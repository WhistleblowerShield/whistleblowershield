# Legal Record ACF Rules Rewrite Draft

This draft rewrites only the rules section from `legal-record-acf-fields-v2.3.md`.
It does not rewrite the field mapping or any downstream spec content.

---

## Naming Rules

### Casing

Casing is strictly enforced.

- ACF field `name` values, which become meta keys, must use `snake_case`.
- Select choice values must use `kebab-case`.
- Taxonomy term slugs must use `kebab-case`.

### CPT Prefix and Infix

Canonical field names in this draft are prefix-free. CPT-specific prefixes are applied during registration.

Use these fixed parts:

- `ws_*` identifies the Whistleblower Shield codebase.
- `jx_*` identifies legal records that are children of `jurisdiction` records.
- CPT slot values are `statute`, `comlaw`, `citation`, and `construction`.

Registered ACF field `name` values must use:

- `ws_jx_statute_*`
- `ws_jx_comlaw_*`
- `ws_jx_citation_*`
- `ws_jx_construction_*`

Registered ACF field `key` values must use:

- `field_jx_statute_*`
- `field_jx_comlaw_*`
- `field_jx_citation_*`
- `field_jx_construction_*`

Field keys do not include the `ws_*` codebase indicator.

Tab field keys must use:

```text
field_jx_{cpt}_{tab-label}_tab
```

For tab labels:

- Build `tab-label` from the tab field `label`, lowercased.
- Do not use `_and_`.
- Do not use symbols.
- Omit words or characters that do not belong in the key.
- Do not abbreviate tab labels.
- Do not use deprecated abbreviations such as `sol` for `statute_of_limitations` or `bop` for `burden_of_proof`.

ACF group keys must use:

```text
group_jx_{cpt}_metadata
```

CPT-specific ACF groups must have `menu_order` lower than `85`. Shared workflow groups occupy `85` through `99`, so every CPT-specific group must appear before them.

### Reserved Prefixes

Reserved prefixes are strictly enforced.

- `ws_auto_` is written only by hook logic, including stamp, source, and plain-English attribution fields.
- Do not use `ws_auto_` for content fields.

### Cardinality

Field names must reflect storage cardinality.

- Single-value fields use singular nouns.
- Multi-value fields, including multi-selects, repeaters, and arrays, use plural nouns.

When the field's base concept changes from plural to companion text, keep the modified key infix singular:

- `disclosure_targets` -> `disclosure_target_details`
- `disclosure_targets` -> `disclosure_target_context`

When the suffix declares the field shape, match the actual field cardinality:

- Use `*_scope` or `*_status` for single-value fields.
- Use `*_scopes` or `*_statuses` for multi-value fields.

Exception: `*_details` and `*_context` are lexical field labels. They do not behave as count nouns and do not track storage cardinality.

### Booleans

Boolean naming is limited to trigger booleans and state booleans.

- `has_*` names a trigger boolean. When true, it activates a companion field, a dependent field, or both.
- `is_*` names a state boolean. It describes a condition and does not imply a companion field.
- `*_is_*` also names a state boolean. It describes a condition and does not imply a companion field.

Examples:

- `has_effective_date` may trigger `effective_date`.
- `has_field_name` may trigger `field_name_details`.

Any boolean outside these scopes requires approval and inline documentation.

### Companion Fields

Companion fields are conditional fields whose naming usually identifies their trigger.

#### `*_details`

Use `*_details` for freetext companion fields.

`*_details` can be triggered in two standard ways:

- A boolean named `has_field_name` triggers `field_name_details`.
- A `has-details` sentinel present in `field_name` triggers `field_name_details`.

Do not name the boolean `has_field_name_details` when the companion is `field_name_details`.

#### `*_context`

Use `*_context` for freetext companion fields that explain context for a selected value, taxonomy term, or non-empty field.

`*_context` can be triggered in two standard ways:

- A trigger field contains a specified trigger value.
- A trigger field is non-empty.

All `*_context` conditionals must document their trigger field and trigger value.

#### Other Companion Fields

A `has_*` boolean or `has-*` sentinel may trigger other companion fields, such as `*_limits`, `*_phases`, or another `*_companion` shape.

The trigger and companion should share the same base name. If they do not, document the conditional logic inline.

Examples:

- `has_field_name_limits` triggers `field_name_limits`.
- `has-phases` in `field_name` triggers `field_name_phases`.

Conditional annotation is not required when the naming convention makes the trigger unambiguous. Annotation is required when the trigger field name differs from the conditional field name, including cases where a suffix or prefix is dropped.

### Sister Fields

Sister fields inherit a companion field's conditional logic but are not themselves companion fields.

Use this inline note for sister fields:

```text
Sister field to `sibling_field`;
```

Rules for sister fields:

- Use a logical, contextual name for the data the field holds.
- Do not apply a required naming convention beyond normal casing and cardinality rules.
- Do not repeat the sibling's conditional documentation.
- Document extra requirements after the sister-field note, using `AND`, `OR`, or `NOT`.
- Sister fields may appear before or after their sibling.
- Freetext sibling fields usually appear last, but order should follow editorial logic.
- A sister field may not appear without a corresponding companion sibling in the same cluster.
- Sister clusters may chain. Use inline notes when chained conditions become hard to read.

### Avoided Names

These names are discouraged, not forbidden.

- Avoid `*_recognized` when a `ws_legal_recognition` taxonomy term can logically represent the state.
- Avoid `*_type` when a more precise suffix fits.
- Avoid `*_limitations` in field names; prefer `*_limits`.

Preferred alternatives to `*_type` include:

- `*_class`
- `*_scope`
- `*_status`
- `*_rule`
- `*_framework`
- `*_weight`
- `*_standard`

The list is not exhaustive. Use `*_type` when the context requires it and no better suffix fits. Pluralize the suffix according to field cardinality.

### Data Shape Suffixes

Use a data-shape suffix only when the field stores that shape of data.

Common data-shape suffixes include:

- `*_url`
- `*_date`
- `*_email`
- `*_value`
- `*_unit`

Use `*_value` for integer or number fields.

Use `*_unit` for unit-select fields. Calendar-unit selects use:

- `days`
- `weeks`
- `months`
- `years`

---

## Sentinel Rules

Sentinels are reserved select choice keys or taxonomy term slugs with defined system behavior.

### Trigger Sentinels

Use trigger sentinels in field choices or taxonomy terms to signal that a companion field should appear.

- `has-details` triggers the relevant `*_details` companion.
- Prefer `has-details` over `other`, `unclear`, or `mixed` when a trigger plus companion field captures the nuance better.
- New taxonomy-term triggers must use the `has-*` prefix.
- In hierarchical taxonomy tables, new `has-*` terms must nest under `has-parent`.

### Non-Standard Sentinels

Document every non-standard sentinel.

Currently approved non-standard sentinels:

- `has-limits` in `ws_remedy` triggers `remedy_limits`.
- `has-channel` in `ws_protected_disclosure` triggers `disclosure_channel_context`.
- `has-ic-channel` in `ws_protected_disclosure` triggers `ic_channel_sequence_context`.

### Redirect Sentinels

Use redirect sentinels when a companion field is already active through another trigger and `has-details` or a parallel context trigger would be redundant.

- `see-details` means the relevant `*_details` companion is already active.
- `see-context` means the relevant `*_context` companion is already active.

---

## Hook Rules

Document required hook behavior inline with the field definition that needs it.

Use hooks for:

- Derived fields that must auto-fill on load and on save.
- Merged hidden fields, such as `_related_agencies`, `_precedent_ids`, and `_parent_ids`, that must auto-fill on save.
- Derived select choices, such as `court` filtered by `jurisdiction`, that must filter on field load.
- Select, choice, and taxonomy fields that need anti-contradiction enforcement.
- Conditional clusters where companion fields and sister fields need cross-field anti-contradiction enforcement.

Prefer unified hooks over duplicate hooks. A single hook that branches by `get_post_type()` is preferred over near-identical hooks per CPT. Reuse hook logic wherever possible.

See `legal-record-acf-hooks.md` for hook details.

---

## Inline Field Description Rules

### Default Field Types

These defaults apply by naming convention unless the inline definition says otherwise.

- `has_*`, `is_*`, and `*_is_*` are boolean fields.
- `*_class`, `*_scope`, `*_status`, `*_rule`, `*_framework`, `*_weight`, and `*_standard` are select fields.
- `*_share` describes a specified portion of a reward, such as `25-30%`.
- `*_compare` describes a mandated comparison and uses `gte`, `lte`, `gt`, `lt`, or `eq`.
- `*_value` is an integer or number field.
- `*_unit` is a select field and means calendar unit unless stated otherwise.
- `*_formula` describes mandated calculations.
- `*_sanctions` describes specified unlawful conduct and associated penalties, usually as a repeater.
- `*_application` describes where or how a legal standard applies and is a select field.
- `*_direction` describes directional legal operation, such as federal preemption of state law, and is a select field.
- `*_bar` is used for claim-blocking doctrines or procedural bars and may be select or boolean.
- `select` means single-select unless multi-select is specified.
- All other fields default to freetext.

### Default Taxonomy Field Settings

Taxonomy fields use these defaults unless stated otherwise:

- Field type: taxonomy
- Multi-select
- `load_terms`: `1`
- `save_terms`: `1`

### Conditional Annotation

Use only these accepted conditional-annotation forms.

- Taxonomy term present: `conditional on slug in taxonomy_field`
- Any non-empty value: `conditional on trigger_field is non-empty`
- Specific value in select field: `conditional on trigger_field is trigger_value`
- Specific value in multi-select field: `conditional on trigger_field includes trigger_value`
- Compound conditions: use all-caps `AND`, `OR`, and `NOT`

Annotation is optional for `*_details`, `*_limits`, `*_phases`, and `*_companions` when the naming convention makes the trigger unambiguous.

Annotation is required for all other conditional fields. `*_context` fields must always declare their trigger field and trigger value.

---

## Attached Workflow Group Rules

Four shared workflow ACF groups attach to all four legal record types alongside the CPT-specific group.

Do not duplicate workflow fields in CPT-specific ACF files. The shared workflow groups are defined in `includes/acf/workflow/`.

| Group key | `menu_order` | Tab label | Fields added |
|---|---:|---|---|
| `group_plain_english_metadata` | 85 | Plain-English | `ws_has_plain_english`, `ws_plain_english_wysiwyg`, `ws_plain_english_reviewed`, 4 `ws_auto_` stamps |
| `group_auto_stamp_metadata` | 90 | Authorship & Review | `ws_auto_create_date`, `ws_auto_create_author`, `ws_auto_last_edited_date`, `ws_auto_last_edited_author` |
| `group_source_verify_metadata` | 95 | Source & Verification | `ws_auto_source_method`, `ws_auto_source_name`, `ws_auto_verified_by`, `ws_auto_verified_date`, `ws_verification_status`, `ws_needs_review` |
| `group_major_edit_metadata` | 99 | Major Edit | `ws_is_major_edit`, `ws_major_edit_description`, `ws_major_edit_update_type` |

---