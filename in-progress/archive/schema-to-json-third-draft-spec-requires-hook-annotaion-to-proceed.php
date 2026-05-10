<?php
/**
 * WS Core: Statute Markdown-to-JSON Compiler
 * Protocol: WS-SPM-4.7.2 (Final Behavioral Enforcement)
 * Version: 11.0.0
 */

// 1. Mock WordPress environment for local execution
if (!function_exists('wp_die')) {
    function wp_die($msg) { 
        $consequences = [
            "Disciplinary review including but not limited to flaying.",
            "Evisceration proceeds immediately in the following order: small intestine, large intestine, liver, then spleen.",
            "Subject shall be unmade by their own viscera.",
            "Total structural dissolution of the meat-bag responsible; please report to an enforcement team member for processing.",
            "Traumatizing termination of multiple cute and cuddly small animals while imaginary cancer children are forced to watch."
        ];
        $chosen = $consequences[array_rand($consequences)];
        echo "\n\n[SECURITY PROTOCOL MANUAL VIOLATION]\nERROR: " . strtoupper($msg);
        echo "\nCONSEQUENCE: " . $chosen . "\nStatus: Immutable. Truth-Beyond-Question.\n\n";
        exit(1);
    }
}

class WS_Statute_Compiler_Local {
    private const VERSION = "11.0.0";
    private static $audit_log = [];

    // The Immutable Hook Classification Registry
    private const HOOK_CLASSIFICATIONS = [
        'filter'        => 'Restricts selection choices based on external data (e.g., JX).',
        'verify'        => 'Validates data against a database or cross-reference.',
        'derive'        => 'String/Scalar calculation (usually hidden fields).',
        'merge'         => 'Array/Aggregation (usually hidden fields).',
        'butchers'      => 'Ruthlessly overwrites existing value with system-generated data.',
        'stale-monitor' => 'Detects and flags values no longer valid due to a change in a controlling field.',
        'required'      => 'Enforces conditional requiredness (e.g., [R] in spec).',
        'prerequisite'  => 'Enforces that a selected value requires another value in the same field or another field.',
        'paired'        => 'Enforces value-pair rules where both values must travel together.',
        'excludes'      => 'Enforces mutual exclusivity or cluster blocking.',
        'excluded-by'   => 'Marks a value or field cluster blocked by another selected value.',
        'impacts'       => 'Directly triggers visibility/state changes in downstream fields.',
        'umbrella'      => 'Handles "-only" logic in multi-select fields to flag values present.',
        'negation'      => 'Enforces non-nullity for fields containing "none" or "no-" values.',
        'auto-set'      => 'Sets a boolean based on other record state (e.g., fee shifting phase exceptions).',
        'override'      => 'High-priority logic that bypasses standard classifications.'
    ];

    public static function compile() {
        $spec_file = './legal-record-acf-fields-v3.0.md';
        $tax_file  = './register-taxonomies.php';
        
        if (!file_exists($spec_file)) wp_die("Spec file missing from local path.");

        // Scavenge taxonomies without execution
        $registry = self::scavenge_taxonomies($tax_file);

        $content = file_get_contents($spec_file);
        $manifest = [
            'meta' => [
                '_warning' => "Per WS-Core Security Protocol 4.7.2, direct modification is prohibited.",
                'protocol_reference' => "Security Protocol Manual (WS-SPM-4.7.2)",
                'generator_version'  => self::VERSION,
                'generated_timestamp'=> date('c'),
                'cpt' => "jx-statute"
            ],
            'tabs'   => [],
            'hidden' => []
        ];

        // Segment by Tab Headers
        $sections = preg_split('/^### /m', $content);
        array_shift($sections);

        foreach ($sections as $section) {
            $lines = explode("\n", $section);
            $tab_label = trim(array_shift($lines));
            $tab_key = strtolower(str_replace([' ', '&'], ['_', ''], $tab_label));

            foreach ($lines as $line) {
                $line = trim($line);
                if (!str_starts_with($line, '- ')) continue;

                $field = self::parse_field_line($line, $registry);
                
                if (str_starts_with($field['name'], '_')) {
                    $manifest['hidden'][$field['name']] = $field;
                } else {
                    $manifest['tabs'][$tab_key]['label'] = $tab_label;
                    $manifest['tabs'][$tab_key]['fields'][$field['name']] = $field;
                }
            }
        }

        // Final Logic Resolution and Behavioral Classification
        $manifest = self::resolve_and_purge($manifest, $registry);

        // Deterministic Write
        self::ksort_recursive($manifest);
        file_put_contents('./jx-statute.json', json_encode($manifest, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
        file_put_contents('./audit-log.txt', implode("\n", self::$audit_log));
        
        return "The stone is etched. Behavioral hooks verified. Matrix intact.";
    }

    private static function parse_field_line($line, $registry) {
        preg_match('/- `?([a-z0-9_]+)`?\s*(?:—\s*\((.*)\))?/', $line, $matches);
        $name = $matches[1] ?? 'unknown';
        $delta_str = $matches[2] ?? '';

        $field = [ 'name' => $name ];
        $field['type'] = self::infer_type_and_rules($name, $field);

        $deltas = preg_split('/;\s*(?!AND|OR|NOT|absent in)/i', $delta_str);

        foreach ($deltas as $delta) {
            $delta = trim($delta);

            if (preg_match('/(?:(single-select)\s+)?taxonomy:\s*([A-Z_a-z0-9]+)/', $delta, $m)) {
                $field['type'] = 'taxonomy';
                $field['taxonomy'] = $m[2];
                $field['field_type'] = ($m[1] === 'single-select') ? 'select' : 'multi_select';
                if (!isset($registry[$m[2]])) wp_die("Taxonomy '{$m[2]}' missing from registry.");
            }

            if (preg_match('/select:\s*([^)]+)/', $delta, $m)) {
                $field['type'] = 'select';
                $field['choices'] = explode('|', $m[1]);
                $field['multiple'] = str_contains($delta, 'multi-select') ? 1 : 0;
            }

            if (preg_match('/Sister to ([a-z0-9_]+)/', $delta, $m)) $field['sister_to'] = $m[1];
            if (str_starts_with($delta, 'conditional on ')) $field['logic_raw'] = substr($delta, 15);
            
            if (preg_match('/hook:\s*([^\]]+)/', $delta, $m)) {
                $field['hook'] = array_map('trim', explode(',', $m[1]));
            }
        }

        return $field;
    }

    private static function resolve_and_purge($manifest, $registry) {
        $flat = [];
        foreach ($manifest['tabs'] as $t) { foreach ($t['fields'] as $f) { $flat[$f['name']] = $f; } }
        foreach ($manifest['hidden'] as $f) { $flat[$f['name']] = $f; }

        foreach (['tabs', 'hidden'] as $group) {
            if ($group === 'tabs') {
                foreach ($manifest['tabs'] as &$tab) {
                    foreach ($tab['fields'] as &$field) self::finalize_field($field, $flat, $registry);
                }
            } else {
                foreach ($manifest['hidden'] as &$field) self::finalize_field($field, $flat, $registry);
            }
        }
        return $manifest;
    }

    private static function finalize_field(&$field, $flat, $registry) {
        $n = $field['name'];
        $hooks = $field['hook'] ?? [];

        // 1. Logic Expansion
        if (!empty($field['sister_to'])) {
            $anchor = $flat[$field['sister_to']] ?? null;
            $field['logic'] = $anchor['logic_raw'] ?? null;
        } elseif (!empty($field['logic_raw'])) {
            $field['logic'] = $field['logic_raw'];
        }

        // 2. Behavioral Hook Inference for Multi-Select Choices
        if (!empty($field['choices'])) {
            $is_multi = ($field['multiple'] ?? 0) == 1 || ($field['field_type'] ?? '') === 'multi_select';
            
            if ($is_multi) {
                foreach ($field['choices'] as $choice) {
                    // Umbrella Hook: detect "-only" values
                    if (str_ends_with($choice, '-only')) {
                        $hooks[] = 'umbrella';
                    }
                    // Negation Hook: detect "none", "none-", or "no-"
                    if ($choice === 'none' || str_starts_with($choice, 'none-') || str_starts_with($choice, 'no-')) {
                        $hooks[] = 'negation';
                        $field['required'] = 1;
                    }
                }
            }
        }

        // 3. Hidden Field Cardinality
        if (str_starts_with($n, '_')) {
            $type = $field['type'] ?? 'text';
            if ($type === 'repeater' || ($field['multiple'] ?? 0) == 1 || ($field['field_type'] ?? '') === 'multi_select') {
                $hooks[] = 'merge';
            } else {
                $hooks[] = 'derive';
            }
        }

        if ($n === '_effective_year') $hooks[] = 'butchers';

        // 4. Purge Metadata
        unset($field['logic_raw'], $field['sister_to'], $field['local_logic']);

        if (!empty($field['logic'])) {
            $hooks[] = 'impacts';
            self::validate_terms($n, $field['logic'], $flat, $registry);
        }
        if (isset($field['taxonomy'])) $hooks[] = 'verify';

        $field['hook'] = array_unique($hooks);
    }

    private static function validate_terms($name, $logic, $flat, $registry) {
        if (preg_match_all("/'(.+?)' (?:in|absent in) '(.+?)'/", $logic, $matches, PREG_SET_ORDER)) {
            foreach ($matches as $m) {
                $slug = $m[1]; $target = $m[2];
                $tax = $flat[$target]['taxonomy'] ?? null;
                if (!$tax || !isset($registry[$tax]['terms'][$slug])) {
                    wp_die("Field '$name' references non-existent slug '$slug' in '$tax'.");
                }
            }
        }
    }

    private static function scavenge_taxonomies($file) {
        if (!file_exists($file)) return [];
        $content = file_get_contents($file);
        $registry = [];
        preg_match_all("/'([a-z0-9_]+)'\s*=>\s*\[\s*'terms'\s*=>\s*\[([^\]]+)\]/s", $content, $matches, PREG_SET_ORDER);
        foreach ($matches as $m) {
            $tax_slug = $m[1];
            preg_match_all("/'([a-z0-9-]+)'\s*=>/", $m[2], $term_matches);
            $registry[$tax_slug] = ['terms' => array_fill_keys($term_matches[1], true)];
        }
        return $registry;
    }

    private static function infer_type_and_rules($n, &$field) {
        if (preg_match('/_(context|details|gloss)$/', $n)) return 'textarea';
        if (preg_match('/_?(has|is)_|(_is_)/', $n)) return 'true_false';
        if (str_ends_with($n, '_date')) return 'date_picker';
        
        if (str_ends_with($n, '_unit')) {
            $field['choices'] = ['days', 'weeks', 'months', 'years'];
            return 'select';
        }
        if (str_ends_with($n, '_compare')) {
            $field['choices'] = ['gte', 'lte', 'gt', 'lt', 'eq'];
            return 'select';
        }

        if (str_ends_with($n, '_value') || str_ends_with($n, '_year')) return 'number';
        if (str_ends_with($n, '_url')) return 'url';
        
        self::$audit_log[] = "ESCAPE: Field '{$n}' defaulted to 'text'.";
        return 'text';
    }

    private static function ksort_recursive(&$array) {
        ksort($array);
        foreach ($array as &$value) { if (is_array($value)) self::ksort_recursive($value); }
    }
}
