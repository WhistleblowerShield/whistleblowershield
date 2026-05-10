That is a brilliant architectural pivot. Shifting the "Source of Truth" from a PHP global array to a **Machine-Readable JSON Schema** creates a clean "Language-Agnostic Contract." This allows your ingest pipeline (whether written in Python, Node, or handled by an LLM) to validate data against a static file without needing to execute the WordPress environment.

In this model, the **JSON is the Spec**, and the **PHP is just the Hydrator**.

### The JSON-to-ACF Workflow

1. **Source of Truth:** `jx-statute-schema.json` contains the prefix-free definitions, JSON mapping keys, and prompt groups.
2. **The Hydrator:** A PHP class reads the JSON on `acf/init`. It applies the **Naming Normalization** (infixes, prefixes, and that specific "underscore-migration" for hidden fields).
3. **Change Detection:** By hashing the JSON file, the system can determine if the schema has evolved, though since we are using `acf_add_local_field_group`, ACF handles the "registration" on every page load with minimal overhead.

---

### 1. The Machine-Readable Schema (`jx-statute-schema.json`)

This refined JSON follows your **Canonical Field Spec** and handles the **Sister-to Doctrine** and **Prompt Schema Mapping**.

```json
{
  "jx-statute": {
    "infix": "jx",
    "menu_order": 10,
    "tabs": {
      "identity": {
        "label": "Identity & Publishing",
        "fields": {
          "jurisdiction": { "type": "taxonomy", "taxonomy": "WS_JURISDICTION_TAXONOMY", "field_type": "radio", "required": true, "group": 1 },
          "official_name": { "required": true, "group": 1 },
          "common_name": { "json_key": "title", "group": 5 },
          "citation": { "label": "Citation", "required": true, "group": 1 },
          "protection_scope": { "type": "select", "choices": ["disclosure", "retaliation", "both"], "group": 2 }
        }
      },
      "classifications": {
        "label": "Classifications",
        "fields": {
          "legal_recognitions": { "type": "taxonomy", "taxonomy": "ws_legal_recognition", "group": 2 },
          "manager_rule_exclusion_context": {
            "type": "textarea",
            "conditional": { "field": "legal_recognitions", "slug": "manager-rule-exclusion" }
          },
          "protected_action_standards": { 
            "type": "select", "multiple": true, "sister": "protected_action_context",
            "choices": ["per-se-protected", "actual-violation", "reasonable-belief", "good-faith"] 
          },
          "protected_action_context": {
            "type": "textarea",
            "conditional": { "field": "legal_recognitions", "slug": "protected-action" }
          }
        }
      }
    },
    "hidden": {
      "_id": { "group": 0 },
      "_effective_year": { "type": "number", "readonly": true, "group": 0 },
      "_precedent_ids": { "type": "relationship", "post_type": ["jx-citation", "jx-construction"], "group": 0 }
    }
  }
}

```

---

### 2. The PHP Hydrator (The "Enforcer")

This script reads the JSON and applies your specific **Solo-Developer Naming Rules**.

```php
/**
 * Reads the JSON schema and registers ACF field groups.
 * Enforces the "Underscore-Migration" for hidden fields.
 */
function ws_hydrate_acf_from_json() {
    $json_path = WS_CORE_PATH . 'schemas/jx-statute-schema.json';
    if ( ! file_exists( $json_path ) ) return;

    $registry = json_decode( file_get_contents( $json_path ), true );

    foreach ( $registry as $cpt => $config ) {
        $infix = $config['infix'];
        $acf_fields = [];
        
        // 1. Process Tabs
        foreach ( $config['tabs'] as $tab_slug => $tab_data ) {
            $acf_fields[] = [
                'key'   => "field_{$infix}_{$cpt}_{$tab_slug}_tab",
                'label' => $tab_data['label'],
                'type'  => 'tab',
            ];

            foreach ( $tab_data['fields'] as $name => $args ) {
                $acf_fields[] = ws_map_json_to_acf( $name, $args, $infix, $cpt, $config );
            }
        }

        // 2. Process Hidden Fields (Applying the Prepended Underscore Rule)
        foreach ( $config['hidden'] as $name => $args ) {
            $acf_fields[] = ws_map_json_to_acf( $name, $args, $infix, $cpt, $config );
        }

        acf_add_local_field_group([
            'key'      => "group_{$infix}_{$cpt}_metadata",
            'title'    => "WS: " . ucwords( str_replace( '-', ' ', $cpt ) ),
            'fields'   => $acf_fields,
            'location' => [[['param' => 'post_type', 'operator' => '==', 'value' => $cpt]]],
            'menu_order' => $config['menu_order'],
        ]);
    }
}

/**
 * Maps a single JSON field definition to an ACF-compliant array.
 */
function ws_map_json_to_acf( $name, $args, $infix, $cpt, $config ) {
    $is_hidden  = str_starts_with( $name, '_' );
    $clean_name = ltrim( $name, '_' );
    
    // Solo Dev Rule: _name becomes _ws_{infix}_{cpt}_{name}
    $field_name = $is_hidden ? "_ws_{$infix}_{$cpt}_{$clean_name}" : "ws_{$infix}_{$cpt}_{$clean_name}";
    $field_key  = "field_{$infix}_{$cpt}_{$clean_name}";

    $field = array_merge( [
        'key'   => $field_key,
        'name'  => $field_name,
        'label' => $args['label'] ?? ucwords( str_replace( '_', ' ', $clean_name ) ),
        'type'  => $args['type'] ?? 'text',
    ], $args );

    // Resolve Sister-to Inheritance
    if ( ! empty( $args['sister'] ) ) {
        $sibling_name = $args['sister'];
        // Search for the sibling in tabs/hidden to find its conditional
        foreach ( $config['tabs'] as $tab ) {
            if ( isset( $tab['fields'][$sibling_name]['conditional'] ) ) {
                $field['conditional_logic'] = ws_parse_json_conditional( $tab['fields'][$sibling_name]['conditional'], $infix, $cpt );
            }
        }
    }

    return $field;
}

```

---

### Where We Stand

* **Taxonomy Registry:** Locked in `register-taxonomies.php`, using version-gated seeding.
* **Schema Source:** Transitioned to `schemas/*.json` to provide a machine-readable contract for your pipeline.
* **Naming Rules:** Hidden fields now follow the `_ws_jx_...` prepend rule. Standard fields follow `ws_jx_...`.
* **Logic Inheritance:** The PHP Hydrator handles **Sister-to** conditional logic automatically, meaning you only have to define a conditional once (on the anchor/context field).
* **Documentation Discipline:** We’ve moved away from "rambling descriptions" to **Inline Annotations** for build logic and **ACF Instructions** for editorial guidance.

### Next Step

Does the `ws_parse_json_conditional` helper need to support the "Pseudo-class" taxonomy checks (e.g., `any retaliation-slug in adverse_actions`)? If so, we should define that logic now before the JSON files get too large.