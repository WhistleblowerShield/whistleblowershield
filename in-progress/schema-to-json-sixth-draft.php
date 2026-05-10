<?php
/**
 * WS Core: Statute Markdown-to-JSON Compiler
 * Protocol: WS-SPM-4.7.2 (Final Behavioral Enforcement)
 * Version: 11.1.0
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

    // The Immutable Choice Sentinel Registry
    // Sentinels are @-prefixed single-value annotations in select: declarations.
    // They suppress literal choice output; the named source provides choices at runtime.
    private const CHOICE_SENTINELS = [
        '@hook'   => 'Choices provided by a hook at runtime; source is a general hook computation.',
        '@matrix' => 'Choices provided by a seed matrix via hook; source is a structured data matrix.',
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

        // Truncate at the Specialized Fields section — this prototype targets
        // statutes only; the per-CPT delta sections are parsed in a later pass.
        $content = preg_split('/^## Specialized Fields By Legal Record Type/m', $content)[0];

        // Segment by Tab Headers
        $sections = preg_split('/^### /m', $content);
        array_shift($sections);

        foreach ($sections as $section) {
            $lines = explode("\n", $section);
            $tab_label = trim(array_shift($lines));
            $tab_key = preg_replace('/_+/', '_', strtolower(str_replace([' ', '&', '/'], ['_', '', ''], $tab_label)));

            // Join continuation lines: append any non-field line to the preceding field
            // line while its parenthetical annotation is still open (more ( than )).
            $joined = [];
            foreach ($lines as $line) {
                $t = trim($line);
                if (str_starts_with($t, '- ')) {
                    $joined[] = $t;
                } elseif ($t !== '' && !empty($joined)) {
                    $last = $joined[count($joined) - 1];
                    if (substr_count($last, '(') > substr_count($last, ')')) {
                        $joined[count($joined) - 1] .= ' ' . $t;
                    }
                }
            }

            foreach ($joined as $line) {
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

        // Deterministic Write — sort properties alphabetically, preserve spec tab and field order.
        $tab_order = array_keys($manifest['tabs']);
        $field_order = [];
        foreach ($manifest['tabs'] as $tk => $tab) $field_order[$tk] = array_keys($tab['fields']);
        self::ksort_recursive($manifest);
        $ordered_tabs = [];
        foreach ($tab_order as $tk) {
            $ordered_fields = [];
            foreach ($field_order[$tk] as $fk) $ordered_fields[$fk] = $manifest['tabs'][$tk]['fields'][$fk];
            $manifest['tabs'][$tk]['fields'] = $ordered_fields;
            $ordered_tabs[$tk] = $manifest['tabs'][$tk];
        }
        $manifest['tabs'] = $ordered_tabs;
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

        // Post-object field detection (operates on full annotation string before delta loop).
        // Pattern 1: post_type keyword (bare or old bracket form) — relationship field.
        //   "multi-select: post_type" → multiple=1; bare "post_type" → multiple=0.
        if (preg_match('/(?:(multi-select)[:\s]+)?post_type\b/', $delta_str, $m)) {
            $field['type'] = 'post_object';
            $field['post_type'] = true;
            $field['multiple'] = !empty($m[1]) ? 1 : 0;
            self::$audit_log[] = "POST-OBJECT: {$name} → multiple={$field['multiple']}";
        // Pattern 2: legacy "post object" / "post_id" narrative annotation.
        } elseif (str_contains($delta_str, 'post object') || preg_match('/\bpost_id\b/', $delta_str)) {
            $field['type'] = 'post_object';
            $field['post_type'] = true;
            $field['multiple'] = str_contains($delta_str, 'array') ? 1 : 0;
            self::$audit_log[] = "POST-OBJECT: {$name} → legacy annotation, multiple={$field['multiple']}";
        // Pattern 3: legacy hidden merge target annotation.
        } elseif (str_contains($delta_str, 'merged array')) {
            $field['type'] = 'post_object';
            $field['post_type'] = true;
            $field['multiple'] = 1;
            self::$audit_log[] = "POST-OBJECT: {$name} → merged array (multiple=1)";
        }

        foreach ($deltas as $delta) {
            $delta = trim($delta);

            if (preg_match('/(?:(single-select)\s+)?taxonomy:\s*`?([A-Z_a-z0-9]+)`?/', $delta, $m)) {
                $tax_slug = $m[2];
                $field['type'] = 'taxonomy';
                $field['taxonomy'] = $tax_slug;
                $field['field_type'] = ($m[1] === 'single-select') ? 'select' : 'multi_select';
                if (!isset($registry[$tax_slug])) wp_die("Taxonomy '{$tax_slug}' missing from registry.");
                self::$audit_log[] = "TAXONOMY-BIND: {$name} → {$tax_slug} ({$field['field_type']})";
            }

            if (preg_match('/select:\s*([^;)]+)/', $delta, $m) && ($field['type'] ?? '') !== 'post_object') {
                $field['type'] = 'select';
                $raw_choices = explode('|', trim($m[1]));
                $choices = array_map(fn($c) => trim($c, " \t`"), $raw_choices);
                // @-prefixed single value is a runtime sentinel: choices are hook-provided, not declared here.
                // Valid sentinels are registered in CHOICE_SENTINELS; unknown @-values are a protocol violation.
                if (count($choices) === 1 && str_starts_with($choices[0], '@')) {
                    if (!isset(self::CHOICE_SENTINELS[$choices[0]])) {
                        wp_die("Field '{$name}' declares unknown choice sentinel '{$choices[0]}'. Known sentinels: " . implode(', ', array_keys(self::CHOICE_SENTINELS)) . ".");
                    }
                    self::$audit_log[] = "SENTINEL-CHOICES: {$name} → {$choices[0]} (" . self::CHOICE_SENTINELS[$choices[0]] . ")";
                } else {
                    $field['choices'] = $choices;
                }
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

        // Pre-build: taxonomy fields carrying has-details → singular base → field name.
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
        } elseif (str_ends_with($n, '_details') && empty($field['logic_raw'])) {
            $base = substr($n, 0, -8);
            if (isset($has_details_map[$base])) {
                $trigger = $has_details_map[$base];
                $field['logic'] = "`has-details` in `{$trigger}`";
                self::$audit_log[] = "AUTO-LOGIC: {$n} → inferred conditional on has-details in {$trigger}";
            }
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
        unset($field['name'], $field['logic_raw'], $field['sister_to'], $field['local_logic']);

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
        // Logic strings use backtick notation: `slug` in `field`
        // Use [^`]+ to prevent crossing backtick boundaries on compound AND conditions.
        if (preg_match_all('/`([^`]+)` (?:in|absent in) `([^`]+)`/', $logic, $matches, PREG_SET_ORDER)) {
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

        // Find each taxonomy entry by locating 'slug' => [ ... 'terms' => [ ... ] ... ]
        // Use offset scanning to handle nested brackets in hierarchical term arrays.
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
                // No literal terms array (e.g. function-generated). Register with empty terms
                // so existence checks pass; individual slug validation is intentionally skipped.
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

    private static function infer_type_and_rules($n, &$field) {
        static $shape_suffixes = ['url', 'date', 'email', 'id', 'value', 'unit', 'formula', 'year', 'order'];
        if (in_array($n, $shape_suffixes, true)) {
            self::$audit_log[] = "[WARN] BARE-SHAPE: '{$n}' matches a data-shape suffix without underscore context; type inferred by exact-name guard.";
        }

        if (preg_match('/_(context|details|gloss)$/', $n)) return 'textarea';
        if (preg_match('/_?(has|is)_|(_is_)/', $n)) return 'true_false';
        if (str_ends_with($n, '_bar')) return 'true_false';
        if ($n === 'date' || str_ends_with($n, '_date')) return 'date_picker';

        // Guidance-defined select suffixes (ws-acf-field-guidance-v1.0.md §Default Field Types).
        // Choices are domain-specific and must be declared in the annotation.
        if (preg_match('/_(class|scope|status|rule|framework|weight|standard|application)e?s?$/', $n)) {
            return 'select';
        }

        if (str_ends_with($n, '_unit')) {
            $field['choices'] = ['days', 'weeks', 'months', 'years'];
            return 'select';
        }
        if (str_ends_with($n, '_compare')) {
            $field['choices'] = ['gte', 'lte', 'gt', 'lt', 'eq'];
            return 'select';
        }

        if (str_ends_with($n, '_value') || str_ends_with($n, '_year') || str_ends_with($n, '_order')) return 'number';
        if ($n === 'url' || str_ends_with($n, '_url')) return 'url';

        if (str_ends_with($n, '_ids')) {
            $field['multiple'] = 1;
            $field['post_type'] = true;
            return 'post_object';
        }
        if (str_ends_with($n, '_id')) {
            $field['multiple'] = 0;
            $field['post_type'] = true;
            return 'post_object';
        }

        self::$audit_log[] = "ESCAPE: {$n} → text (reason: no suffix-based type inference matched)";
        return 'text';
    }

    private static function ksort_recursive(&$array) {
        ksort($array);
        foreach ($array as &$value) { if (is_array($value)) self::ksort_recursive($value); }
    }
}

echo WS_Statute_Compiler_Local::compile() . "\n";
