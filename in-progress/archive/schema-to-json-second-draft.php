<?php
/**
 * WS Core: Statute Markdown-to-JSON Compiler
 * Protocol: WS-SPM-4.7.2
 * Version: 4.2.0
 */

defined('ABSPATH') || exit;

class WS_Statute_Compiler {
    private const VERSION = "4.2.0";
    private static $audit_log = [];

    /**
     * Entry point: Generates the "Etched Stone" manifest.
     */
    public static function compile() {
        global $_ws_taxonomy_registry; 

        // 1. Load Source and Check Integrity
        $spec_path = WS_CORE_PATH . 'docs/legal-record-acf-fields-v3.0.md';
        if (!file_exists($spec_path)) {
            wp_die("Critical Protocol 4.7.2 Failure: Spec file not found. Flaying sequence initiated.");
        }

        $content = file_get_contents($spec_path);
        $mtime   = filemtime($spec_path); // Base for idempotency

        $manifest = [
            'meta' => [
                '_warning' => "Per WS-Core Security Protocol 4.7.2, direct modification is prohibited. Hand-editing triggers automated disciplinary flaying. Intestines will be removed via the navel to preserve the audit trail.",
                'protocol_reference' => "Security Protocol Manual (WS-SPM-4.7.2)",
                'status'             => "pending",
                'generator_version'  => self::VERSION,
                'generated_timestamp'=> date('c', $mtime),
                'cpt'                => "jx-statute",
                'infix'              => "jx",
                'checksum'           => hash_file('crc32b', $spec_path) // byte-identical tracking
            ],
            'tabs'   => [],
            'hidden' => []
        ];

        // 2. Parse Markdown Sections (###)
        $sections = preg_split('/^### /m', $content);
        array_shift($sections); // Remove preamble

        foreach ($sections as $section) {
            $lines     = explode("\n", $section);
            $tab_label = trim(array_shift($lines));
            $tab_key   = strtolower(str_replace([' ', '&'], ['_', ''], $tab_label));

            foreach ($lines as $line) {
                $line = trim($line);
                if (!str_starts_with($line, '- ')) continue;

                $field_args = self::parse_field_line($line, $_ws_taxonomy_registry);
                
                if (str_starts_with($field_args['name'], '_')) {
                    $manifest['hidden'][$field_args['name']] = $field_args;
                } else {
                    $manifest['tabs'][$tab_key]['label'] = $tab_label;
                    $manifest['tabs'][$tab_key]['fields'][$field_args['name']] = $field_args;
                }
            }
        }

        // 3. Post-Processing: Expansion and Global Validation
        $manifest = self::expand_sisters($manifest);
        self::validate_term_slugs($manifest, $_ws_taxonomy_registry); 

        // 4. Deterministic Save
        self::finalize($manifest);
        
        return "The stone is etched. Protocol 4.7.2 satisfied.";
    }

    /**
     * Parses field lines and extracts Delta rules.
     */
    private static function parse_field_line($line, $registry) {
        preg_match('/- `?([a-z0-9_]+)`?\s*(?:—\s*\((.*)\))?/', $line, $matches);
        $name = $matches[1] ?? 'unknown';
        $delta_string = $matches[2] ?? '';

        if (preg_match('/approv(al|ed)/i', $delta_string)) {
            self::$audit_log[] = "APPROVED: Field '{$name}' contains editorial approval annotation.";
        }

        $field = [ 
            'name' => $name, 
            'type' => self::infer_type_by_suffix($name) // Guidance v1.0
        ];

        // Split deltas by semicolon, respecting logical phrases
        $deltas = preg_split('/;\s*(?!AND|OR|NOT|absent in)/i', $delta_string);

        foreach ($deltas as $delta) {
            $delta = trim($delta);

            // Taxonomy Table Validation
            if (preg_match('/(?:(single-select)\s+)?taxonomy:\s*([A-Z_a-z0-9]+)/', $delta, $m)) {
                $tax_slug = $m[2];
                if (!isset($registry[$tax_slug]) && $tax_slug !== 'WS_JURISDICTION_TAXONOMY') {
                    wp_die("Direct Protocol 4.7.2 Violation: Taxonomy '{$tax_slug}' missing from registry. FuQ'n Error.");
                }
                $field['type'] = 'taxonomy';
                $field['taxonomy'] = $tax_slug;
                $field['field_type'] = ($m[1] === 'single-select') ? 'select' : 'multi_select';
            }

            // Select Choices and Multi-select Logic
            if (preg_match('/select:\s*([^)]+)/', $delta, $m)) {
                $field['type'] = 'select';
                $field['choices'] = explode('|', $m[1]);
                $field['multiple'] = str_contains($delta, 'multi-select') ? 1 : 0;
            }

            // Sister Marker
            if (preg_match('/Sister to ([a-z0-9_]+)/', $delta, $m)) {
                $field['sister_to'] = $m[1];
                if (preg_match('/;\s*(AND|OR|NOT|absent in)\s+(.+)/i', $delta, $lm)) {
                    $field['local_logic'] = $lm[0];
                }
            }

            // Capture raw logic for the "Bodily-Harm-Fit" validator
            if (str_starts_with($delta, 'conditional on ')) {
                $field['logic_raw'] = substr($delta, 15);
            }

            // Hook Classifications
            if (preg_match('/hook:\s*\[([^\]]+)\]/', $delta, $m)) {
                $field['hook'] = array_map('trim', explode(',', $m[1]));
            }
        }

        return $field;
    }

    /**
     * Suffix-based type inference.
     */
    private static function infer_type_by_suffix($name) {
        if (str_ends_with($name, '_context') || str_ends_with($name, '_details') || str_ends_with($name, '_gloss')) return 'textarea';
        if (str_ends_with($name, '_date')) return 'date_picker';
        if (str_ends_with($name, '_value')) return 'number';
        if (preg_match('/_?(has|is)_|(_is_)/', $name)) return 'true_false'; // Approved Booleans

        self::$audit_log[] = "ESCAPE: Field '{$name}' escaped to default 'text'.";
        return 'text';
    }

    /**
     * Validates every term-slug used in logic against the registry.
     */
    private static function validate_term_slugs($manifest, $registry) {
        $flat = self::flatten($manifest);
        foreach ($flat as $field) {
            if (empty($field['logic_raw'])) continue;
            
            // Matches 'slug' in 'field' or 'slug' absent in 'field'
            if (preg_match_all("/'(.+?)' (?:in|absent in) '(.+?)'/", $field['logic_raw'], $matches, PREG_SET_ORDER)) {
                foreach ($matches as $m) {
                    $slug = $m[1]; $target = $m[2];
                    $tax  = $flat[$target]['taxonomy'] ?? null;
                    if (!$tax || !isset($registry[$tax]['terms'][$slug])) {
                        wp_die("Direct Protocol 4.7.2 Violation: Field '{$field['name']}' references non-existent term-slug '{$slug}' in '{$tax}'. FuQ'n Error.");
                    }
                }
            }
        }
    }

    /**
     * Copies logic from anchor fields to sisters.
     */
    private static function expand_sisters($manifest) {
        $flat = self::flatten($manifest);
        foreach ($manifest['tabs'] as &$tab) {
            foreach ($tab['fields'] as &$field) {
                if (empty($field['sister_to'])) continue;
                $anchor = $flat[$field['sister_to']] ?? null;
                if ($anchor && !empty($anchor['logic_raw'])) {
                    $field['logic'] = empty($field['local_logic']) 
                        ? $anchor['logic_raw'] 
                        : "( {$anchor['logic_raw']} ) {$field['local_logic']}";
                }
            }
        }
        return $manifest;
    }

    private static function flatten($manifest) {
        $flat = [];
        foreach ($manifest['tabs'] as $t) { $flat = array_merge($flat, $t['fields']); }
        return array_merge($flat, $manifest['hidden']);
    }

    private static function finalize($manifest) {
        ksort($manifest);
        file_put_contents(WS_CORE_PATH . 'schemas/jx-statute.json', json_encode($manifest, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
        file_put_contents(WS_CORE_PATH . 'schemas/audit-log.txt', implode("\n", self::$audit_log));
    }

    private static function ksort_recursive(&$array) {
        ksort($array);
        foreach ($array as &$value) { if (is_array($value)) self::ksort_recursive($value); }
    }
}