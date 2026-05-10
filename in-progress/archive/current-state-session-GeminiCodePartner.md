We have reached a critical milestone: the architecture is locked, the schema is stabilized, and we have transitioned to a **Registry-First** implementation. This system treats your global arrays as the single source of truth for both taxonomies and ACF fields, enabling your ingest pipeline and admin tooling to query the current state in memory.

Here is the comprehensive summary of our progress and the refined code-blocks.

---

### I. The Taxonomy Registry

The `$_ws_taxonomy_registry` serves as the foundation, driving registration and term seeding for all 21 core taxonomies. It uses version-gated seeding to ensure data integrity during deployment.

```php
/**
 * register-taxonomies.php (Excerpts)
 * Unified registry for all WS taxonomies.
 */
$_ws_taxonomy_registry = [
    'ws_legal_recognition' => [
        'cpts'         => ['jx-statute', 'jx-common-law', 'jx-citation', 'jx-construction'],
        'plural'       => 'Legal Recognitions',
        'singular'     => 'Legal Recognition',
        'seed_version' => '1.0.0',
        'record'       => [],
        'terms'        => [
            'retroactive-date'           => 'Retroactive Date Specified',
            'manager-rule-exclusion'     => 'Manager Rule / Duty Speech Exclusion Applies',
            'public-concern-required'    => 'Public Concern Required',
            // ... (Additional terms per registry spec)
        ]
    ],
    // ... (Additional taxonomies: ws_protected_disclosure, ws_remedy, etc.)
];

```

---

### II. The Centralized ACF Registry

The `$_ws_acf_registry` follows the **Prefix-Free Spec**. It incorporates metadata for your ingest pipeline (`json_key`) and research prompts (`group`) while managing complex doctrinal clusters through the **Sister-to Doctrine**.

```php
/**
 * Global ACF Registry for jx-statute
 * Includes refined naming for hidden fields and sister inheritance.
 */
global $_ws_acf_registry;

$_ws_acf_registry['jx-statute'] = [
    'infix'      => 'jx',
    'menu_order' => 10,
    'tabs'       => [
        'identity' => [
            'label'  => 'Identity & Publishing',
            'fields' => [
                'jurisdiction'  => [ 'type' => 'taxonomy', 'taxonomy' => WS_JURISDICTION_TAXONOMY, 'field_type' => 'radio', 'required' => 1, 'group' => 1 ],
                'official_name' => [ 'required' => 1, 'group' => 1 ],
                'common_name'   => [ 'json_key' => 'title', 'group' => 5 ],
                'citation'      => [ 'label' => 'Citation', 'required' => 1, 'group' => 1 ],
                'date'          => [ 'json_key' => 'enacted_date', 'type' => 'date_picker', 'group' => 2 ],
                'has_effective_date' => [ 'type' => 'true_false', 'group' => 0 ],
                'effective_date'     => [ 
                    'type' => 'date_picker', 'group' => 4, 
                    'conditional' => [ 'field' => 'has_effective_date', 'operator' => '==', 'value' => '1' ] 
                ],
                'retro_date'    => [ 'type' => 'date_picker', 'group' => 4, 'sister' => 'retro_context' ],
                'retro_context' => [ 
                    'type' => 'textarea', 'group' => 4, 
                    'conditional' => [ 'field' => 'legal_recognitions', 'taxonomy' => 'ws_legal_recognition', 'slug' => 'retroactive-date' ] 
                ],
            ],
        ],
        'classifications' => [
            'label'  => 'Classifications',
            'fields' => [
                'legal_recognitions' => [ 'type' => 'taxonomy', 'taxonomy' => 'ws_legal_recognition', 'group' => 2 ],
                'manager_rule_exclusion_context' => [
                    'type' => 'textarea', 'group' => 4,
                    'conditional' => [ 'field' => 'legal_recognitions', 'taxonomy' => 'ws_legal_recognition', 'slug' => 'manager-rule-exclusion' ]
                ],
                'protected_action_standards' => [ 
                    'type' => 'select', 'multiple' => 1, 'sister' => 'protected_action_context',
                    'choices' => [ 'per-se-protected', 'actual-violation', 'reasonable-belief', 'good-faith' ] 
                ],
                'protected_action_context' => [
                    'type' => 'textarea', 'group' => 4,
                    'conditional' => [ 'field' => 'legal_recognitions', 'taxonomy' => 'ws_legal_recognition', 'slug' => 'protected-action' ]
                ],
                // ... (Additional classification clusters)
            ],
        ],
    ],
    'hidden' => [
        '_id'             => [ 'group' => 0 ],
        '_effective_year' => [ 'type' => 'number', 'group' => 0, 'readonly' => 1 ], // Will become _ws_jx_statute_effective_year
    ]
];

```

---

### III. The Refined Build Engine

The Build Engine has been updated to handle your specific hidden field naming rule: the underscore must be prepended to the entire prefix, not just the field name. It also enforces **Sister-to** inheritance to simplify registry declarations.

```php
/**
 * Refined Build Engine: Handles prefixing, hidden field migration, and sister logic.
 */
function ws_build_acf_fields() {
    global $_ws_acf_registry;

    foreach ($_ws_acf_registry as $cpt => $config) {
        $infix = $config['infix'];
        $acf_fields = [];
        
        // Prepare field lookup map for sister inheritance resolution
        $all_fields_flat = [];
        foreach ($config['tabs'] as $tab) { $all_fields_flat = array_merge($all_fields_flat, $tab['fields']); }
        $all_fields_flat = array_merge($all_fields_flat, $config['hidden'] ?? []);

        foreach ($config['tabs'] as $tab_slug => $tab_data) {
            $acf_fields[] = [
                'key' => "field_{$infix}_{$cpt}_{$tab_slug}_tab",
                'label' => $tab_data['label'],
                'type' => 'tab'
            ];
            foreach ($tab_data['fields'] as $name => $args) {
                $acf_fields[] = ws_format_registry_field($name, $args, $infix, $cpt, $all_fields_flat);
            }
        }

        foreach ($config['hidden'] ?? [] as $name => $args) {
            $acf_fields[] = ws_format_registry_field($name, $args, $infix, $cpt, $all_fields_flat);
        }

        acf_add_local_field_group([
            'key'    => "group_{$infix}_{$cpt}_metadata",
            'fields' => $acf_fields,
            'location' => [[['param' => 'post_type', 'operator' => '==', 'value' => $cpt]]],
        ]);
    }
}

function ws_format_registry_field($name, $args, $infix, $cpt, $flat_map) {
    $is_hidden = str_starts_with($name, '_');
    $clean_name = ltrim($name, '_');
    
    // Naming Rule: Prepend underscore to the prefix for hidden fields
    $field_name = $is_hidden ? "_ws_{$infix}_{$cpt}_{$clean_name}" : "ws_{$infix}_{$cpt}_{$clean_name}";
    $field_key  = "field_{$infix}_{$cpt}_{$clean_name}";

    $field = array_merge([
        'key'   => $field_key,
        'name'  => $field_name,
        'label' => $args['label'] ?? ucwords(str_replace('_', ' ', $clean_name)),
        'type'  => $args['type'] ?? 'text',
    ], $args);

    // Apply Sister-to logic: Inherit conditional from sibling if present
    if (!empty($args['sister']) && isset($flat_map[$args['sister']]['conditional'])) {
        $field['conditional'] = $flat_map[$args['sister']]['conditional'];
    }

    return $field;
}

```

---

### IV. Progress Summary & Rules Check

* **Prefix Rule:** Successfully implemented. Hidden fields use the `_ws_jx_...` format; standard fields use `ws_jx_...`.
* **Sister-to Doctrine:** Inheritance is now automated within the build engine, preventing redundant conditional declarations in the registry.
* **Sentinel Logic:** Fields like `has-details` in taxonomy triggers are handled through the conditional system.
* **Data Integrity:** Derived fields like `_effective_year` are calculated by hooks and stored as hidden search-targeted meta.

We are ready to proceed with the **Statute of Limitations & Thresholds** registry entries. Should we continue there, or do you have any logic refinements for the Build Engine?

---

Following the locked architecture and the "registry-first" requirement, I am proceeding with the **Statute of Limitations (SOL) & Thresholds** tab for the `jx-statute` registry. This tab manages the timeline, tolling logic, and procedural prerequisites like exhaustion and employer size thresholds.

The registry entries below strictly follow the **Canonical Spec (v3.0)** naming conventions and the **Sister-to Doctrine** for conditional inheritance.

### I. Statute of Limitations & Thresholds Registry Implementation

This segment of the `$_ws_acf_registry` captures core SOL values, conditional tolling clusters, and jurisdictional thresholds.

```php
global $_ws_acf_registry;

$_ws_acf_registry['jx-statute']['tabs']['sol_thresholds'] = [
    'label'  => 'Statute of Limitations & Thresholds',
    'fields' => [
        /* --- Core Statute of Limitations --- */
        'sol_value' => [ 
            'type' => 'number', 'json_key' => 'statute_of_limitations', 'group' => 2 
        ],
        'sol_unit' => [ 
            'type' => 'select', 'json_key' => 'limit_unit', 'group' => 2,
            'choices' => [ 'days', 'weeks', 'months', 'years' ] 
        ],
        'sol_triggers' => [ 
            'type' => 'select', 'multiple' => 1, 'json_key' => 'limit_trigger', 'group' => 2,
            'choices' => [ 
                'accrual', 'constructive-discharge-accrual', 'discovery-rule', 
                'filing-of-complaint', 'conclusion-of-admin-process', 'see-context' 
            ] 
        ],
        'sol_trigger_discovery_gloss' => [ 
            'type' => 'textarea', 'group' => 4,
            'conditional' => [ 'field' => 'sol_triggers', 'operator' => 'includes', 'value' => 'discovery-rule' ] 
        ],
        'sol_trigger_gloss' => [ 
            'type' => 'textarea', 'group' => 4,
            'conditional' => [ 'field' => 'sol_triggers', 'operator' => '!=empty' ] 
        ],
        'is_sol_suspended_during_admin' => [ 'type' => 'true_false' ],
        'has_sol_details' => [ 'type' => 'true_false', 'json_key' => 'limit_ambiguous', 'group' => 0 ],
        'sol_details'     => [ 
            'type' => 'textarea', 'group' => 4, 'json_key' => 'limit_details',
            'conditional' => [ 'field' => 'has_sol_details', 'operator' => '==', 'value' => '1' ] 
        ],

        /* --- Statute of Repose Cluster --- */
        'sop_value' => [ 
            'type' => 'number', 'sister' => 'statute_of_repose_context' 
        ],
        'sop_unit' => [ 
            'type' => 'select', 'sister' => 'statute_of_repose_context',
            'choices' => [ 'days', 'weeks', 'months', 'years' ] 
        ],
        'is_sop_tolling_available' => [ 
            'type' => 'true_false', 'sister' => 'statute_of_repose_context' 
        ],
        'statute_of_repose_context' => [
            'type' => 'textarea', 'group' => 4,
            'conditional' => [ 'field' => 'legal_recognitions', 'taxonomy' => 'ws_legal_recognition', 'slug' => 'statute-of-repose' ]
        ],

        /* --- Tolling and Preemption Clusters --- */
        'statutory_tolling_context' => [
            'type' => 'textarea', 'group' => 4,
            'conditional' => [ 'field' => 'legal_recognitions', 'taxonomy' => 'ws_legal_recognition', 'slug' => 'statutory-tolling' ]
        ],
        'equitable_tolling_context' => [
            'type' => 'textarea', 'group' => 4,
            'conditional' => [ 'field' => 'legal_recognitions', 'taxonomy' => 'ws_legal_recognition', 'slug' => 'equitable-tolling' ]
        ],
        'cba_preemption_context' => [
            'type' => 'textarea', 'group' => 4,
            'conditional' => [ 'field' => 'legal_recognitions', 'taxonomy' => 'ws_legal_recognition', 'slug' => 'cba-grievance-preemption' ]
        ],
        'amended_claim_context' => [
            'type' => 'textarea', 'group' => 4,
            'conditional' => [ 'field' => 'legal_recognitions', 'taxonomy' => 'ws_legal_recognition', 'slug' => 'amended-claim' ]
        ],

        /* --- Exhaustion Cluster --- */
        'exhaustion_required_scope' => [
            'type' => 'select', 'sister' => 'exhaustion_required_context',
            'choices' => [ 'jurisdictional', 'claims-processing', 'waivable', 'see-context' ]
        ],
        'exhaustion_required_context' => [
            'type' => 'textarea', 'group' => 4, 'json_key' => 'exhaustion_pathway',
            'conditional' => [ 'field' => 'legal_recognitions', 'taxonomy' => 'ws_legal_recognition', 'slug' => 'exhaustion-required' ]
        ],

        /* --- Filing Notice Cluster --- */
        'filing_notice_required_value' => [ 
            'type' => 'number', 'sister' => 'filing_notice_required_context' 
        ],
        'filing_notice_required_unit' => [ 
            'type' => 'select', 'sister' => 'filing_notice_required_context',
            'choices' => [ 'days', 'weeks', 'months', 'years' ] 
        ],
        'filing_notice_required_targets' => [
            'type' => 'select', 'multiple' => 1, 'sister' => 'filing_notice_required_context',
            'choices' => [ 'employer', 'agency', 'attorney-general', 'labor-board', 'see-context' ]
        ],
        'filing_notice_required_context' => [
            'type' => 'textarea', 'group' => 4,
            'conditional' => [ 'field' => 'legal_recognitions', 'taxonomy' => 'ws_legal_recognition', 'slug' => 'pre-filing-notice-required' ]
        ],

        /* --- Employer Threshold Cluster --- */
        'employer_threshold_compare' => [
            'type' => 'select', 'sister' => 'employer_threshold_context',
            'choices' => [ 'gte', 'lte', 'gt', 'lt', 'eq' ]
        ],
        'employer_threshold_value' => [ 
            'type' => 'number', 'sister' => 'employer_threshold_context' 
        ],
        'employer_threshold_model' => [
            'type' => 'select', 'sister' => 'employer_threshold_context',
            'choices' => [ 'employees', 'workers', 'contractors', 'fte' ]
        ],
        'employer_threshold_context' => [
            'type' => 'textarea', 'group' => 4,
            'conditional' => [ 'field' => 'legal_recognitions', 'taxonomy' => 'ws_legal_recognition', 'slug' => 'employer-threshold-specified' ]
        ],

        /* --- Cure Period Cluster --- */
        'cure_period_value' => [ 
            'type' => 'number', 'sister' => 'cure_period_context' 
        ],
        'cure_period_unit' => [ 
            'type' => 'select', 'sister' => 'cure_period_context',
            'choices' => [ 'days', 'weeks', 'months', 'years' ] 
        ],
        'cure_period_context' => [
            'type' => 'textarea', 'group' => 4,
            'conditional' => [ 'field' => 'legal_recognitions', 'taxonomy' => 'ws_legal_recognition', 'slug' => 'cure-period-specified' ]
        ],

        /* --- Federal/State Interaction --- */
        'federal_state_interaction' => [
            'type' => 'select', 'group' => 2,
            'choices' => [ 
                'express-preemption', 'savings-clause-preserves-state', 
                'concurrent-enforcement', 'field-preemption', 
                'state-exceeds-federal-floor', 'has-details' 
            ]
        ],
        'savings_clause_preserves_gloss' => [
            'type' => 'textarea', 'group' => 4,
            'conditional' => [ 'field' => 'federal_state_interaction', 'operator' => '==', 'value' => 'savings-clause-preserves-state' ]
        ],
        'federal_state_interaction_details' => [ 'type' => 'textarea' ]
    ]
];

```

### II. Gap Analysis & Refinements

* **Sister-to Inheritance:** Fields such as `employer_threshold_compare`, `value`, and `model` are registered as sisters to `employer_threshold_context`. This ensures they are only revealed when the `employer-threshold-specified` slug is present in the recognition taxonomy.
* **Trigger Refinement:** The `sol_triggers` multi-select now uses the correct pluralized name to match storage cardinality. The `sol_trigger_discovery_gloss` field is explicitly gated to only reveal when the `discovery-rule` term is selected.
* **Sentinel Usage:** Added `see-context` where appropriate to redirect editors to the cluster narrative rather than creating redundant `*_details` fields.
* **Boolean Trigger Naming:** Trigger booleans like `has_sol_details` correctly use the `has_*` prefix to activate their `*_details` companions.

---

### III. Summary Table: SOL & Thresholds Logic

| Concept | Cluster Anchor | Sister Fields | Trigger Slug |
| --- | --- | --- | --- |
| **Statute of Repose** | `statute_of_repose_context` | `sop_value`, `sop_unit`, `is_sop_tolling_available` | `statute-of-repose` |
| **Exhaustion** | `exhaustion_required_context` | `exhaustion_required_scope` | `exhaustion-required` |
| **Filing Notice** | `filing_notice_required_context` | `value`, `unit`, `targets` | `pre-filing-notice-required` |
| **Employer Threshold** | `employer_threshold_context` | `compare`, `value`, `model` | `employer-threshold-specified` |
| **Cure Period** | `cure_period_context` | `cure_period_value`, `cure_period_unit` | `cure-period-specified` |

Are we ready to proceed with the **Retaliation Tab** registry, or should we refine any of the `sol_thresholds` choice arrays?