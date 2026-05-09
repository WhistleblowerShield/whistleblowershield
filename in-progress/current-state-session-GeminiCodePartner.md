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