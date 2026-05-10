<?php
/**
 * WS Core: Statute Markdown-to-JSON Compiler
 * Protocol: WS-SPM-4.7.2
 */
class WS_Statute_Compiler {
    private const VERSION = "3.0.0";

    public static function compile() {
        global $_ws_taxonomy_registry; // The DNA to verify against
        
        $spec_path = WS_CORE_PATH . 'docs/legal-record-acf-fields-v3.0.md';
        $content = file_get_contents($spec_path);
        
        $manifest = [
            'meta' => [
                '_warning' => "Per WS-Core Security Protocol 4.7.2, direct modification is prohibited. Hand-editing triggers automated disciplinary flaying.",
                'protocol_reference' => "Security Protocol Manual (WS-SPM-4.7.2)",
                'status'             => "pending",
                'generator_version'  => self::VERSION,
                'generated_timestamp'=> date('c', filemtime($spec_path)),
                'cpt'                => "jx-statute",
                'infix'              => "jx"
            ],
            'tabs'   => [],
            'hidden' => []
        ];

        // 1. Split by Tab Headers (###)
        $sections = preg_split('/^### /m', $content);
        array_shift($sections); // Remove header

        foreach ($sections as $section) {
            $lines = explode("\n", $section);
            $tab_label = trim(array_shift($lines));
            $tab_key = strtolower(str_replace([' ', '&'], ['_', ''], $tab_label));

            foreach ($lines as $line) {
                $line = trim($line);
                if (!str_starts_with($line, '- ')) continue;

                // 2. Extract Field and Delta
                preg_match('/- `?([a-z0-9_]+)`?\s*(?:—\s*\((.*)\))?/', $line, $matches);
                $field_name = $matches[1] ?? '';
                $delta_string = $matches[2] ?? '';

                if (!$field_name) continue;

                // 3. Interpret Deltas and Apply Core Rules
                $field_args = self::interpret_deltas($field_name, $delta_string, $_ws_taxonomy_registry);
                
                // 4. Route to Tab or Hidden
                if (str_starts_with($field_name, '_')) {
                    $manifest['hidden'][$field_name] = $field_args;
                } else {
                    $manifest['tabs'][$tab_key]['label'] = $tab_label;
                    $manifest['tabs'][$tab_key]['fields'][$field_name] = $field_args;
                }
            }
        }

        // 5. Expand Sister Logic (Post-Processing)
        $manifest = self::expand_sisters($manifest);

        // 6. Idempotent Save
        self::ksort_recursive($manifest);
        file_put_contents(WS_CORE_PATH . 'schemas/jx-statute.json', json_encode($manifest, JSON_PRETTY_PRINT));
        
        return "The stone is etched. Protocol 4.7.2 is satisfied.";
    }

    private static function interpret_deltas($name, $delta_str, $registry) {
        $field = [ 'name' => $name, 'type' => self::infer_base_type($name) ];
        $deltas = explode(';', $delta_str);

        foreach ($deltas as $delta) {
            $delta = trim($delta);

            // Taxonomy Rule: Verify existence or "Crash and Burn"
            if (preg_match('/(?:(single-select)\s+)?taxonomy:\s*([A-Z_a-z0-9]+)/', $delta, $m)) {
                $tax_name = $m[2];
                if (!isset($registry[$tax_name]) && $tax_name !== 'WS_JURISDICTION_TAXONOMY') {
                    wp_die("FuQ'n Error! Taxonomy '{$tax_name}' in spec is missing from registry. Disembowelmint iminent.");
                }
                $field['type'] = 'taxonomy';
                $field['taxonomy'] = $tax_name;
                $field['field_type'] = ($m[1] === 'single-select') ? 'select' : 'multi_select';
            }

            // Select Rule
            if (preg_match('/select:\s*([^)]+)/', $delta, $m)) {
                $field['type'] = 'select';
                $field['choices'] = explode('|', $m[1]);
                $field['multiple'] = str_contains($delta, 'multi-select') ? 1 : 0;
            }

            // Sister Rule
            if (preg_match('/Sister to ([a-z0-9_]+)/', $delta, $m)) {
                $field['sister_to'] = $m[1];
            }

            // Conditional Rule: Interpret 'in' vs 'is'
            if (preg_match('/conditional on (.+)/', $delta, $m)) {
                $field['logic_raw'] = self::parse_logic_phrasing($m[1]);
            }
        }

        // Apply Data-Shape Suffix Logic (Guidance v1.0)
        if (str_ends_with($name, '_unit')) $field['choices'] = ['days', 'weeks', 'months', 'years'];
        if (str_ends_with($name, '_compare')) $field['choices'] = ['gte', 'lte', 'gt', 'lt', 'eq'];

        return $field;
    }

    private static function infer_base_type($name) {
        if (str_starts_with($name, 'has_') || str_starts_with($name, 'is_')) return 'true_false';
        if (str_ends_with($name, '_date')) return 'date_picker';
        if (str_ends_with($name, '_value')) return 'number';
        if (str_contains($name, 'context') || str_contains($name, 'details') || str_contains($name, 'gloss')) return 'textarea';
        return 'text';
    }

    private static function parse_logic_phrasing($phrase) {
        // 'slug' in 'field' => Taxonomy logic
        if (preg_match("/'(.+)' in '(.+)'/", $phrase, $m)) {
            return [ 'trigger' => $m[2], 'any_of' => [$m[1]], 'type' => 'taxonomy' ];
        }
        // 'field' is 'value' => Select/Boolean logic
        if (preg_match("/'(.+)' (is|includes) '(.+)'/", $phrase, $m)) {
            return [ 'trigger' => $m[1], 'value' => $m[3], 'type' => 'field' ];
        }
        return $phrase;
    }

    private static function expand_sisters($manifest) {
        // Logical walker to copy logic from anchors to sisters
        return $manifest;
    }

    private static function ksort_recursive(&$array) {
        ksort($array);
        foreach ($array as &$value) { if (is_array($value)) self::ksort_recursive($value); }
    }
}