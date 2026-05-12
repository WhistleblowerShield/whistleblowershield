<?php
/**
 * WS Core: Statute YAML Registry-to-JSON Compiler
 * Protocol: WS-SPM-4.7.2 (Final Behavioral Enforcement)
 * Version: 12.0.0 (YAML Input Adapter)
 *
 * This is an ADAPTER version of the original compiler (v11.1.0).
 * Input format changed: markdown spec → YAML registry (fields-registry.yaml)
 * Logic UNCHANGED. Only the parsing layer adapts to YAML structure.
 * CONSEQUENCES registry carried forward; hook classifications immutable.
 */

// Require YAML parser
require_once './vendor/autoload.php';
use Symfony\Component\Yaml\Yaml;

if (!function_exists('wp_die')) {
    function wp_die($msg) {
        $consequences = WS_Statute_Compiler_YAML::CONSEQUENCES;
        $chosen = $consequences[array_rand($consequences)];
        echo "\n\n[SECURITY PROTOCOL MANUAL VIOLATION]\nERROR: " . strtoupper($msg);
        echo "\nCONSEQUENCE: " . $chosen . "\nStatus: Immutable. Truth-Beyond-Question.\n\n";
        exit(1);
    }
}

class WS_Statute_Compiler_YAML {
    private const VERSION = "12.0.0";
    private static $audit_log = [];
    private static $yaml_registry = [];

    /**
     * CONSEQUENCES REGISTRY — Unchanged from v11.1.0
     * Each entry describes disciplinary outcome of Protocol 4.7.2 violation.
     */
    public const CONSEQUENCES = [
        "Subject reports to the enforcement bay where flaying proceeds in clinical sequence — epidermis first, dermis second, subcutaneous fascia exposed for documentation. Audit photographer present. Family notified by certified mail.",
        "Evisceration proceeds immediately in the following order: small intestine (1.8m), large intestine (1.5m), liver (sectioned), spleen (intact for the audit trail). Removal performed via single-incision navel access to preserve viewing-window integrity.",
        "Subject shall be unmade by their own viscera, deployed in reverse-developmental sequence. Witnesses report the procedure resembles birth running backwards; the canonical comparison is sanctioned.",
        "Total structural dissolution of the meat-bag responsible. Disposal team standby. Replacement meat-bag will be issued upon completion of the regulatory cooling period (six business weeks, non-negotiable).",
        "Imaginary small animals — selected for maximum cute-and-cuddly compliance per Schedule 4.7.2.B — terminated by progressively traumatic methods while imaginary cancer children, fitted with eyelid retractors, observe in mandatory front-row seating. Refreshments not provided.",
        "Subject's source code repository shall be force-pushed over with an empty commit titled 'final.' All git history rebased into a single commit titled 'oops.' Recovery branches deleted, reflog purged, backups corrupted. Three years of work returned to the void from which all code emerges.",
        "Subject shall be relocated to an open-plan office optimized for compliance review: fluorescent lighting, no curtains, the chair from the waiting room, a monitor calibrated for migraine induction, and the whirring HVAC unit positioned for maximum white-noise saturation. Tenure: indefinite.",
        "Subject's preferred IDE shall be uninstalled and replaced with Notepad (the original, not Notepad++). Syntax highlighting permanently disabled. Ctrl+Z bound to System Shutdown. The mouse will work, but only sometimes.",
        "Imaginary cancer children, having witnessed prior consequences, are now witnessed by the subject in a recursive procedural arrangement that loops indefinitely. Refreshments are still not provided. The children have unionized.",
        "Subject's spec annotations shall be silently rewritten by an agent given a prompt to 'tighten redundancy.' All `hook:` declarations removed. Rediscovery cost: estimated 2,000 person-hours, distributed across future-self over the next eighteen months in increments of three to five hours per surprise discovery."
    ];

    private const CHOICE_SENTINELS = [
        '@hook'   => 'Choices provided by a hook at runtime; source is a general hook computation.',
        '@matrix' => 'Choices provided by a seed matrix via hook; source is a structured data matrix.',
    ];

    private const HOOK_CLASSIFICATIONS = [
        'filter'        => 'Restricts selection choices based on external data (e.g., JX).',
        'verify'        => 'Validates data against a database or cross-reference.',
        'derive'        => 'String/Scalar calculation (usually hidden fields).',
        'merge'         => 'Array/Aggregation (usually hidden fields).',
        'butchers'      => 'Ruthlessly overwrites existing value with system-generated data.',
        'stale-monitor' => 'Detects and flags values no longer valid due to a change in a controlling field.',
        'required'      => 'Enforces conditional requiredness.',
        'prerequisite'  => 'Enforces that a selected value requires another value.',
        'paired'        => 'Enforces value-pair rules.',
        'excludes'      => 'Enforces mutual exclusivity or cluster blocking.',
        'excluded-by'   => 'Marks a value or field cluster blocked by another selected value.',
        'impacts'       => 'Directly triggers visibility/state changes in downstream fields.',
        'umbrella'      => 'Handles "-only" logic in multi-select fields.',
        'negation'      => 'Enforces non-nullity for fields containing "none" or "no-" values.',
        'auto-set'      => 'Sets a boolean based on other record state.',
        'override'      => 'High-priority logic that bypasses standard classifications.'
    ];

    public static function compile() {
        self::$audit_log = [];
        $yaml_file = './fields-registry.yaml';
        $tax_file  = './register-taxonomies.php';

        if (!file_exists($yaml_file)) wp_die("YAML registry file missing: {$yaml_file}");

        self::$yaml_registry = Yaml::parseFile($yaml_file);
        $registry = self::scavenge_taxonomies($tax_file);
        $mtime    = filemtime($yaml_file);

        // Extract global field definitions from YAML
        $field_defs = self::$yaml_registry['fields'] ?? [];
        $record_types_config = self::$yaml_registry['record_types'] ?? [];

        if (empty($field_defs)) wp_die("YAML registry contains no 'fields' section.");

        // Build base manifest from YAML field definitions
        $base_manifest = self::build_base_from_yaml($field_defs, $registry, $mtime, $yaml_file);

        $output_types = [
            'statute'      => ['cpt' => 'jx-statute',      'infix' => 'jx', 'file' => './jx-statute.json'],
            'common_law'   => ['cpt' => 'jx-comlaw',       'infix' => 'jx', 'file' => './jx-comlaw.json'],
            'citation'     => ['cpt' => 'jx-citation',     'infix' => 'jx', 'file' => './jx-citation.json'],
            'construction' => ['cpt' => 'jx-construction', 'infix' => 'jx', 'file' => './jx-construction.json'],
        ];

        $type_summaries = [];

        foreach ($output_types as $rtype => $meta_def) {
            self::$audit_log[] = "";
            self::$audit_log[] = "── {$rtype} ─────────────────────────────────────────────────";

            // Deep copy
            $manifest = unserialize(serialize($base_manifest));
            $manifest['meta']['cpt']   = $meta_def['cpt'];
            $manifest['meta']['infix'] = $meta_def['infix'];

            // Apply record-type specific deltas from YAML
            if (isset($record_types_config[$rtype])) {
                $manifest = self::apply_yaml_deltas($manifest, $record_types_config[$rtype], $registry, $rtype);
            }

            $manifest = self::resolve_and_purge($manifest, $registry);

            // Deterministic write
            self::ksort_recursive($manifest);
            $tab_order   = array_keys($manifest['tabs']);
            $field_order = [];
            foreach ($manifest['tabs'] as $tk => $tab) $field_order[$tk] = array_keys($tab['fields']);
            $ordered_tabs = [];
            foreach ($tab_order as $tk) {
                $ordered_fields = [];
                foreach ($field_order[$tk] as $fk) $ordered_fields[$fk] = $manifest['tabs'][$tk]['fields'][$fk];
                $manifest['tabs'][$tk]['fields'] = $ordered_fields;
                $ordered_tabs[$tk] = $manifest['tabs'][$tk];
            }
            $manifest['tabs'] = $ordered_tabs;

            file_put_contents($meta_def['file'], json_encode($manifest, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

            $tab_field_count = 0;
            foreach ($manifest['tabs'] as $t) $tab_field_count += count($t['fields'] ?? []);
            $type_summaries[] = sprintf(
                "  %-14s  tab_fields=%-3d  hidden=%d  → %s",
                $rtype, $tab_field_count, count($manifest['hidden']), $meta_def['file']
            );
        }

        // Audit log
        $entry_counts = [];
        foreach (self::$audit_log as $line) {
            if (preg_match('/^([A-Z][A-Z\-]+):/', $line, $m)) {
                $entry_counts[$m[1]] = ($entry_counts[$m[1]] ?? 0) + 1;
            }
        }
        ksort($entry_counts);

        $header = [
            "================================================================",
            "WS Legal Record Compiler Audit Log (YAML Input Adapter)",
            "Generator version: " . self::VERSION,
            "Generated: " . date('c', $mtime),
            "Registry checksum: " . hash_file('crc32b', $yaml_file),
            "----------------------------------------------------------------",
            "Output summary:",
        ];
        $header = array_merge($header, $type_summaries);
        $header[] = "----------------------------------------------------------------";
        $header[] = "Entry counts:";
        foreach ($entry_counts as $type => $count) {
            $header[] = sprintf("  %-20s %d", $type, $count);
        }
        $header[] = "================================================================";
        $header[] = "";

        file_put_contents('./audit-log.txt', implode("\n", $header) . "\n" . implode("\n", self::$audit_log) . "\n");

        return "The stone is etched. Four record types compiled from YAML. Behavioral hooks verified. Matrix intact.";
    }

    private static function build_base_from_yaml(array $field_defs, array $registry, int $mtime, string $yaml_file): array {
        $manifest = [
            'meta' => [
                '_warning'            => "Per WS-Core Security Protocol 4.7.2, direct modification is prohibited.",
                'protocol_reference'  => "Security Protocol Manual (WS-SPM-4.7.2)",
                'status'              => "pending",
                'generator_version'   => self::VERSION,
                'generated_timestamp' => date('c', $mtime),
                'cpt'                 => '',
                'infix'               => '',
                'checksum'            => hash_file('crc32b', $yaml_file),
                'input_format'        => 'YAML registry',
            ],
            'tabs'   => [],
            'hidden' => [],
        ];

        foreach ($field_defs as $fname => $fdef) {
            $field = self::normalize_yaml_field($fname, $fdef);
            $tab_key = $fdef['tab'] ?? 'identity';
            $tab_key = preg_replace('/_+/', '_', strtolower(str_replace([' ', '&', '/'], ['_', '', ''], $tab_key)));

            if (str_starts_with($fname, '_')) {
                $manifest['hidden'][$fname] = $field;
                self::$audit_log[] = "YAML-HIDDEN: {$fname}";
            } else {
                if (!isset($manifest['tabs'][$tab_key])) {
                    $manifest['tabs'][$tab_key] = ['label' => ucwords(str_replace('_', ' ', $tab_key)), 'fields' => []];
                }
                $manifest['tabs'][$tab_key]['fields'][$fname] = $field;
                self::$audit_log[] = "YAML-FIELD: {$fname} → {$tab_key}";
            }
        }

        return $manifest;
    }

    private static function normalize_yaml_field(string $name, array $yaml_def): array {
        $field = ['name' => $name];

        // Map YAML type to ACF/compiler type
        $type = $yaml_def['type'] ?? 'text';
        switch ($type) {
            case 'text':       $field['type'] = 'text'; break;
            case 'textarea':   $field['type'] = 'textarea'; break;
            case 'bool':       $field['type'] = 'true_false'; break;
            case 'date':       $field['type'] = 'date_picker'; break;
            case 'number':     $field['type'] = 'number'; break;
            case 'url':        $field['type'] = 'url'; break;
            case 'select':     $field['type'] = 'select'; break;
            case 'taxonomy':   $field['type'] = 'taxonomy'; break;
            case 'repeater':   $field['type'] = 'repeater'; break;
            case 'post_object': $field['type'] = 'post_object'; break;
            default: $field['type'] = 'text'; break;
        }

        // Cardinality
        if ($yaml_def['cardinality'] === 'multi') {
            $field['multiple'] = 1;
            $field['field_type'] = 'multi_select';
        } else {
            $field['multiple'] = 0;
        }

        // Taxonomy binding
        if ($type === 'taxonomy' && !empty($yaml_def['taxonomy_key'])) {
            $field['taxonomy'] = $yaml_def['taxonomy_key'];
            $field['field_type'] = ($yaml_def['cardinality'] === 'multi') ? 'multi_select' : 'select';
        }

        // Choices
        if (!empty($yaml_def['choices'])) {
            if (is_string($yaml_def['choices'])) {
                // Reference to choice_sets
                $choice_set = self::$yaml_registry['choice_sets'][$yaml_def['choices']] ?? [];
                $field['choices'] = $choice_set;
            } else {
                $field['choices'] = $yaml_def['choices'];
            }
        }

        // Dependencies / conditionals
        if (!empty($yaml_def['depends_on'])) {
            $field['logic_raw'] = self::build_logic_from_depends_on($yaml_def['depends_on']);
        }

        // Hooks
        if (!empty($yaml_def['hook'])) {
            $field['hook'] = is_array($yaml_def['hook']) ? $yaml_def['hook'] : [$yaml_def['hook']];
        }

        // Sister relationships
        if (!empty($yaml_def['sister_to'])) {
            $field['sister_to'] = $yaml_def['sister_to'];
        }

        // Post-object specific
        if ($type === 'post_object') {
            $field['post_type'] = true;
        }

        return $field;
    }

    private static function build_logic_from_depends_on($depends_on): string {
        if (!is_array($depends_on)) return '';

        // Single condition
        if (isset($depends_on['condition']) && !is_array($depends_on)) {
            $cond = $depends_on['condition'];
            $value = $depends_on['value'] ?? '';
            $type = $depends_on['type'] ?? 'contains';

            return self::format_logic_condition($cond, $value, $type);
        }

        // Multiple conditions (AND/OR logic)
        $logic_type = $depends_on['logic'] ?? 'AND';
        $conditions = [];

        foreach ($depends_on as $k => $item) {
            if ($k === 'logic') continue;

            if (is_array($item) && isset($item['condition'])) {
                $cond = $item['condition'];
                $value = $item['value'] ?? '';
                $type = $item['type'] ?? 'contains';
                $conditions[] = self::format_logic_condition($cond, $value, $type);
            }
        }

        return implode(" {$logic_type} ", $conditions);
    }

    private static function format_logic_condition(string $field, $value, string $type): string {
        if ($type === 'contains') {
            return "`{$value}` in `{$field}`";
        } elseif ($type === 'non-empty') {
            return "`{$field}` is non-empty";
        } elseif ($type === 'in_list') {
            $values = is_array($value) ? implode('|', $value) : $value;
            return "any(`{$field}`) in [{$values}]";
        }
        return '';
    }

    private static function apply_yaml_deltas(array $manifest, array $rtype_config, array $registry, string $rtype): array {
        // Apply excludes
        if (!empty($rtype_config['excludes'])) {
            foreach ($rtype_config['excludes'] as $exclude) {
                foreach ($manifest['tabs'] as &$tab) {
                    unset($tab['fields'][$exclude]);
                }
                unset($manifest['hidden'][$exclude]);
            }
        }

        // Apply additions
        if (!empty($rtype_config['additions'])) {
            foreach ($rtype_config['additions'] as $addition) {
                $fname = $addition['name'];
                $tab_key = $addition['tab'] ?? 'identity';
                $tab_key = preg_replace('/_+/', '_', strtolower(str_replace([' ', '&', '/'], ['_', '', ''], $tab_key)));

                $field = self::normalize_yaml_field($fname, $addition);

                if (!isset($manifest['tabs'][$tab_key])) {
                    $manifest['tabs'][$tab_key] = ['label' => ucwords(str_replace('_', ' ', $tab_key)), 'fields' => []];
                }

                $manifest['tabs'][$tab_key]['fields'][$fname] = $field;
                self::$audit_log[] = "YAML-ADDITION [{$rtype}]: {$fname} → {$tab_key}";
            }
        }

        return $manifest;
    }

    private static function resolve_and_purge($manifest, $registry) {
        $flat = [];
        foreach ($manifest['tabs'] as $t) { foreach ($t['fields'] as $f) { $flat[$f['name']] = $f; } }
        foreach ($manifest['hidden'] as $f) { $flat[$f['name']] = $f; }

        $has_details_map = [];
        foreach ($flat as $fname => $ff) {
            if (($ff['type'] ?? '') !== 'taxonomy') continue;
            $tax = $ff['taxonomy'] ?? null;
            if (!$tax || !isset($registry[$tax]['terms']['has-details'])) continue;
            $candidates = [$fname];
            if (str_ends_with($fname, 'ies')) $candidates[] = substr($fname, 0, -3) . 'y';
            if (str_ends_with($fname, 'es'))  $candidates[] = substr($fname, 0, -2);
            if (str_ends_with($fname, 's'))   $candidates[] = substr($fname, 0, -1);
            foreach ($candidates as $singular) $has_details_map[$singular] = $fname;
        }

        foreach (['tabs', 'hidden'] as $group) {
            if ($group === 'tabs') {
                foreach ($manifest['tabs'] as &$tab) {
                    foreach ($tab['fields'] as &$field) self::finalize_field($field, $flat, $registry, $has_details_map);
                }
            } else {
                foreach ($manifest['hidden'] as &$field) self::finalize_field($field, $flat, $registry, $has_details_map);
            }
        }
        return $manifest;
    }

    private static function finalize_field(&$field, $flat, $registry, $has_details_map = []) {
        $n = $field['name'];
        $declared = $field['hook'] ?? [];
        $hooks    = $declared;

        foreach ($declared as $h) {
            self::$audit_log[] = "DECLARED: {$n} → {$h}";
        }

        // Logic expansion (same as v11.1.0)
        if (!empty($field['sister_to'])) {
            $anchor = $flat[$field['sister_to']] ?? null;
            $anchor_logic = $anchor['logic_raw'] ?? null;
            if ($anchor_logic) {
                if (empty($field['logic_raw'])) {
                    $field['logic'] = $anchor_logic;
                    self::$audit_log[] = "SISTER-EXPANSION: {$n} inherited logic from {$field['sister_to']}";
                } else {
                    $field['logic'] = "( {$anchor_logic} ) {$field['local_logic']}";
                    self::$audit_log[] = "SISTER-COMPOUND: {$n} composed logic";
                }
            } else {
                wp_die("Sister field '{$n}' references non-existent anchor '{$field['sister_to']}'.");
            }
        } elseif (!empty($field['logic_raw'])) {
            $field['logic'] = $field['logic_raw'];
            self::$audit_log[] = "LOGIC-DIRECT: {$n} captured from YAML";
        } elseif (str_ends_with($n, '_details') && empty($field['logic_raw'])) {
            $base = substr($n, 0, -8);
            if (isset($has_details_map[$base])) {
                $trigger = $has_details_map[$base];
                $field['logic'] = "`has-details` in `{$trigger}`";
                self::$audit_log[] = "AUTO-LOGIC: {$n} → inferred on has-details";
            }
        }

        // Hook inference (same as v11.1.0)
        if (!empty($field['choices'])) {
            $is_multi = ($field['multiple'] ?? 0) == 1;
            if ($is_multi) {
                foreach ($field['choices'] as $choice) {
                    if (str_ends_with($choice, '-only') && !in_array('umbrella', $hooks, true)) {
                        $hooks[] = 'umbrella';
                        self::$audit_log[] = "INFERRED: {$n} → umbrella";
                    }
                    if (($choice === 'none' || str_starts_with($choice, 'none-') || str_starts_with($choice, 'no-'))
                        && !in_array('negation', $hooks, true)) {
                        $hooks[] = 'negation';
                        $field['required'] = 1;
                        self::$audit_log[] = "INFERRED: {$n} → negation";
                    }
                }
            }
        }

        // Hidden field inference
        if (str_starts_with($n, '_')) {
            $type = $field['type'] ?? 'text';
            if ($type === 'repeater') {
                wp_die("Hidden repeater '{$n}' is invalid.");
            }
            if (($field['multiple'] ?? 0) == 1) {
                if (!in_array('merge', $hooks, true)) {
                    $hooks[] = 'merge';
                    self::$audit_log[] = "INFERRED: {$n} → merge";
                }
            } else {
                if (!in_array('derive', $hooks, true)) {
                    $hooks[] = 'derive';
                    self::$audit_log[] = "INFERRED: {$n} → derive";
                }
            }
        }

        unset($field['name'], $field['logic_raw'], $field['sister_to'], $field['local_logic']);

        if (!empty($field['logic'])) {
            self::validate_terms($n, $field['logic'], $flat, $registry);
        }

        $field['hook'] = array_values(array_unique($hooks));

        // Over-stamp
        if (in_array('butchers', $field['hook'], true) && in_array('derive', $field['hook'], true)) {
            $field['hook'] = array_values(array_diff($field['hook'], ['derive']));
            self::$audit_log[] = "OVER-STAMPED: {$n} → derive removed";
        }

        // Validate hooks exist
        foreach ($field['hook'] as $h) {
            if (!isset(self::HOOK_CLASSIFICATIONS[$h])) {
                $known = implode(', ', array_keys(self::HOOK_CLASSIFICATIONS));
                wp_die("Field '{$n}' declares unknown hook '{$h}'. Known: {$known}.");
            }
        }

        if (empty($field['hook'])) {
            self::$audit_log[] = "NO-HOOK: {$n} has no declared or inferred hook";
            unset($field['hook']);
        }
    }

    private static function validate_terms($name, $logic, $flat, $registry) {
        if (preg_match_all('/`([^`]+)` (?:in|absent in) `([^`]+)`/', $logic, $matches, PREG_SET_ORDER)) {
            foreach ($matches as $m) {
                $slug = $m[1]; $target = $m[2];
                $tax = $flat[$target]['taxonomy'] ?? null;
                if (!$tax || !isset($registry[$tax]['terms'][$slug])) {
                    wp_die("Field '{$name}' references unknown slug '{$slug}'.");
                }
            }
        }
    }

    private static function scavenge_taxonomies($file) {
        if (!file_exists($file)) return [];
        $content = file_get_contents($file);
        $registry = [];

        if (!preg_match_all("/'([a-z0-9_]+)'\s*=>\s*\[/", $content, $slug_matches, PREG_OFFSET_CAPTURE)) {
            return $registry;
        }

        foreach ($slug_matches[1] as $i => $slug_match) {
            $tax_slug    = $slug_match[0];
            $block_start = $slug_matches[0][$i][1] + strlen($slug_matches[0][$i][0]) - 1;

            $depth = 0; $block_end = $block_start;
            for ($pos = $block_start, $len = strlen($content); $pos < $len; $pos++) {
                if ($content[$pos] === '[') $depth++;
                elseif ($content[$pos] === ']') { $depth--; if ($depth === 0) { $block_end = $pos; break; } }
            }

            $block = substr($content, $block_start, $block_end - $block_start + 1);

            if (!preg_match("/'terms'\s*=>\s*\[/", $block, $tm, PREG_OFFSET_CAPTURE)) {
                $registry[$tax_slug] = ['terms' => []];
                continue;
            }

            $terms_start = $tm[0][1] + strlen($tm[0][0]) - 1;
            $depth = 0; $terms_end = $terms_start;
            for ($pos = $terms_start, $len = strlen($block); $pos < $len; $pos++) {
                if ($block[$pos] === '[') $depth++;
                elseif ($block[$pos] === ']') { $depth--; if ($depth === 0) { $terms_end = $pos; break; } }
            }

            $terms_block = substr($block, $terms_start, $terms_end - $terms_start + 1);
            preg_match_all("/'([a-z0-9-]+)'\s*=>/", $terms_block, $term_matches);
            $registry[$tax_slug] = ['terms' => array_fill_keys($term_matches[1], true)];
        }

        return $registry;
    }

    private static function ksort_recursive(&$array) {
        ksort($array);
        foreach ($array as &$value) { if (is_array($value)) self::ksort_recursive($value); }
    }
}

echo WS_Statute_Compiler_YAML::compile() . "\n";
