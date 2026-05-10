<?php
/**
 * WS Core: Statute Markdown-to-JSON Compiler
 * Protocol: WS-SPM-4.7.2 (Final Behavioral Enforcement)
 * Version: 11.0.0
 */

// 1. Mock WordPress environment for local execution
if (!function_exists('wp_die')) {
    function wp_die($msg) {
        $consequences = WS_Statute_Compiler_Local::CONSEQUENCES;
        $chosen = $consequences[array_rand($consequences)];
        echo "\n\n[SECURITY PROTOCOL MANUAL VIOLATION]\nERROR: " . strtoupper($msg);
        echo "\nCONSEQUENCE: " . $chosen . "\nStatus: Immutable. Truth-Beyond-Question.\n\n";
        exit(1);
    }
}

class WS_Statute_Compiler_Local {
    private const VERSION = "11.1.0";
    private static $audit_log = [];

    /**
     * Procedural Consequences Registry (pending migration to ws-schema-constraints.php)
     *
     * Each entry describes the disciplinary outcome of a Protocol 4.7.2 violation.
     * Random selection is the established assignment mechanism; severity is comparable
     * across entries. New entries should match the procedural-cruelty register and
     * prioritize traumatic specificity over generic menace.
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
        self::$audit_log = []; // Reset static state for repeatable runs
        $spec_file = './legal-record-acf-fields-v3.0.md';
        $tax_file  = './register-taxonomies.php';
        
        if (!file_exists($spec_file)) wp_die("Spec file missing from local path.");

        // Scavenge taxonomies without execution
        $registry = self::scavenge_taxonomies($tax_file);

        $content = file_get_contents($spec_file);
        $mtime   = filemtime($spec_file);

        $manifest = [
            'meta' => [
                '_warning' => "Per WS-Core Security Protocol 4.7.2, direct modification is prohibited. Hand-editing triggers automated disciplinary flaying. Intestines will be removed via the navel to preserve the audit trail.",
                'protocol_reference' => "Security Protocol Manual (WS-SPM-4.7.2)",
                'status'             => "pending",
                'generator_version'  => self::VERSION,
                'generated_timestamp'=> date('c', $mtime),
                'cpt'                => "jx-statute",
                'infix'              => "jx",
                'checksum'           => hash_file('crc32b', $spec_file)
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

        // Audit Log: prepend summary header for at-a-glance review
        $tab_field_count = 0;
        foreach ($manifest['tabs'] as $t) {
            $tab_field_count += count($t['fields'] ?? []);
        }
        $hidden_count = count($manifest['hidden']);
        $entry_counts = array_count_values(array_map(
            fn($line) => explode(':', $line, 2)[0],
            self::$audit_log
        ));
        ksort($entry_counts);
        $header = [
            "================================================================",
            "WS Statute Compiler Audit Log",
            "Generator version: " . self::VERSION,
            "Generated: " . date('c', filemtime($spec_file)),
            "Spec checksum: " . hash_file('crc32b', $spec_file),
            "Tab fields: {$tab_field_count} | Hidden fields: {$hidden_count}",
            "----------------------------------------------------------------",
            "Entry counts:",
        ];
        foreach ($entry_counts as $type => $count) {
            $header[] = sprintf("  %-20s %d", $type, $count);
        }
        $header[] = "================================================================";
        $header[] = "";
        $log_output = implode("\n", $header) . implode("\n", self::$audit_log) . "\n";
        file_put_contents('./audit-log.txt', $log_output);

        return "The stone is etched. Behavioral hooks verified. Matrix intact.";
    }

    private static function parse_field_line($line, $registry) {
        preg_match('/- `?([a-z0-9_]+)`?\s*(?:—\s*\((.*)\))?/', $line, $matches);
        $name = $matches[1] ?? 'unknown';
        $delta_str = $matches[2] ?? '';

        $field = [ 'name' => $name ];
        $field['type'] = self::infer_type_and_rules($name, $field);

        // Log editorially-approved exceptions for periodic review
        if (preg_match('/approv(al|ed)/i', $delta_str)) {
            self::$audit_log[] = "APPROVED: {$name} → spec carries editorial-approval annotation";
        }

        $deltas = preg_split('/;\s*(?!AND|OR|NOT|absent in)/i', $delta_str);

        foreach ($deltas as $delta) {
            $delta = trim($delta);

            if (preg_match('/(?:(single-select)\s+)?taxonomy:\s*([A-Z_a-z0-9]+)/', $delta, $m)) {
                $field['type'] = 'taxonomy';
                $field['taxonomy'] = $m[2];
                $field['field_type'] = ($m[1] === 'single-select') ? 'select' : 'multi_select';
                if (!isset($registry[$m[2]])) wp_die("Taxonomy '{$m[2]}' missing from registry.");
                self::$audit_log[] = "TAXONOMY-BIND: {$name} → {$m[2]} ({$field['field_type']})";
            }

            if (preg_match('/select:\s*([^)]+)/', $delta, $m)) {
                $field['type'] = 'select';
                $field['choices'] = explode('|', $m[1]);
                $field['multiple'] = str_contains($delta, 'multi-select') ? 1 : 0;
            }

            if (preg_match('/Sister to ([a-z0-9_]+)/', $delta, $m)) {
                $field['sister_to'] = $m[1];
                // Capture additional conditions appended after the sister declaration
                if (preg_match('/Sister to [a-z0-9_]+\s*;\s*(AND|OR|NOT|absent in)\s+(.+)/i', $delta_str, $lm)) {
                    $field['local_logic'] = trim($lm[0]);
                }
            }
            if (str_starts_with($delta, 'conditional on ')) $field['logic_raw'] = substr($delta, 15);
            
            if (preg_match('/hook:\s*(.+)$/', $delta, $m)) {
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

        // Snapshot the spec-declared hooks before any inference so we can log origins.
        $declared = $field['hook'] ?? [];
        $hooks    = $declared;

        foreach ($declared as $h) {
            self::$audit_log[] = "DECLARED: {$n} → {$h}";
        }

        // 1. Logic Expansion
        if (!empty($field['sister_to'])) {
            $anchor = $flat[$field['sister_to']] ?? null;
            $anchor_logic = $anchor['logic_raw'] ?? null;
            if ($anchor_logic) {
                if (empty($field['local_logic'])) {
                    $field['logic'] = $anchor_logic;
                    self::$audit_log[] = "SISTER-EXPANSION: {$n} inherited logic from {$field['sister_to']}";
                } else {
                    $field['logic'] = "( {$anchor_logic} ) {$field['local_logic']}";
                    self::$audit_log[] = "SISTER-COMPOUND: {$n} composed logic from {$field['sister_to']} with local condition";
                }
            } else {
                wp_die("Field '$n' is sister to '{$field['sister_to']}' but the anchor carries no logic to inherit. Per WS-SPM Section 4.7.2.S (pending), sister relationships exist solely to propagate cluster gates from anchors; an orphan sister has no gate, no cluster membership, and no documented reason to exist as a sister. Resolution requires either (a) confirming the anchor field's conditional annotation is present in the spec, (b) correcting the sister-to reference to the intended anchor, or (c) removing the sister relationship if the field is genuinely independent.");
            }
        } elseif (!empty($field['logic_raw'])) {
            $field['logic'] = $field['logic_raw'];
            self::$audit_log[] = "LOGIC-DIRECT: {$n} captured conditional logic from spec";
        }

        // 2. Behavioral Hook Inference for Multi-Select Choices
        if (!empty($field['choices'])) {
            $is_multi = ($field['multiple'] ?? 0) == 1 || ($field['field_type'] ?? '') === 'multi_select';

            if ($is_multi) {
                foreach ($field['choices'] as $choice) {
                    // Umbrella Hook: detect "-only" values
                    if (str_ends_with($choice, '-only') && !in_array('umbrella', $hooks, true)) {
                        $hooks[] = 'umbrella';
                        self::$audit_log[] = "INFERRED: {$n} → umbrella (reason: choice \"{$choice}\")";
                    }
                    // Negation Hook: detect "none", "none-", or "no-"
                    if (($choice === 'none' || str_starts_with($choice, 'none-') || str_starts_with($choice, 'no-'))
                        && !in_array('negation', $hooks, true)) {
                        $hooks[] = 'negation';
                        $field['required'] = 1;
                        self::$audit_log[] = "INFERRED: {$n} → negation (reason: choice \"{$choice}\")";
                        self::$audit_log[] = "NEGATION-REQUIRES: {$n} promoted to required=1 (negation hook present)";
                    }
                }
            }
        }

        // 3. Hidden Field Cardinality Auto-Inference
        // _hidden_string → derive (default; spec annotation may over-stamp with butchers)
        // _hidden_array  → merge  (default; spec annotation may over-stamp)
        // _hidden_repeater → REJECTED (Protocol 4.7.2 violation; field is unreachable)
        if (str_starts_with($n, '_')) {
            $type = $field['type'] ?? 'text';
            if ($type === 'repeater') {
                wp_die("Field '$n' is a hidden repeater. Per WS-SPM Section 4.7.2.R (pending), repeaters require an editor-facing surface; hidden repeaters indicate a database table being smuggled into ACF.");
            }
            if (($field['multiple'] ?? 0) == 1 || ($field['field_type'] ?? '') === 'multi_select') {
                if (!in_array('merge', $hooks, true)) {
                    $hooks[] = 'merge';
                    self::$audit_log[] = "INFERRED: {$n} → merge (reason: hidden array)";
                }
            } else {
                if (!in_array('derive', $hooks, true)) {
                    $hooks[] = 'derive';
                    self::$audit_log[] = "INFERRED: {$n} → derive (reason: hidden scalar)";
                }
            }
        }

        // 4. Purge Metadata (spec annotations now authoritative for declared hooks)
        unset($field['logic_raw'], $field['sister_to'], $field['local_logic']);

        if (!empty($field['logic'])) {
            self::validate_terms($n, $field['logic'], $flat, $registry);
        }

        $field['hook'] = array_values(array_unique($hooks));

        // 5. Over-Stamp: spec-declared hooks supersede cardinality-inferred siblings.
        //    butchers (explicit overwrite) over-stamps derive (default scalar inference).
        if (in_array('butchers', $field['hook'], true) && in_array('derive', $field['hook'], true)) {
            $field['hook'] = array_values(array_diff($field['hook'], ['derive']));
            self::$audit_log[] = "OVER-STAMPED: {$n} → derive removed by declared butchers";
        }

        // 6. Vocabulary Validation: every declared hook must exist in HOOK_CLASSIFICATIONS
        foreach ($field['hook'] as $h) {
            if (!isset(self::HOOK_CLASSIFICATIONS[$h])) {
                $known = implode(', ', array_keys(self::HOOK_CLASSIFICATIONS));
                wp_die("Field '$n' declares unknown hook category '$h'. Known categories: $known.");
            }
        }

        // 7. NO-HOOK detection: field has no declared or inferred hook category.
        //    Not a failure — some fields are legitimately hookless (pure freetext, simple data) —
        //    but worth logging so the spec author can review whether the absence is intentional.
        if (empty($field['hook'])) {
            self::$audit_log[] = "NO-HOOK: {$n} has no declared or inferred hook category";
            unset($field['hook']);
        }
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
        
        self::$audit_log[] = "ESCAPE: {$n} → text (reason: no suffix-based type inference matched)";
        return 'text';
    }

    private static function ksort_recursive(&$array) {
        ksort($array);
        foreach ($array as &$value) { if (is_array($value)) self::ksort_recursive($value); }
    }
}
