## Legal Record ACF Hook Requirements

This document captures hook requirements for the legal-record ACF model. The field spec identifies *where* a hook
is needed; this doc explains the shared behavior, validation rules, and known hook backlog. See the [Hook Rules]
section of `legal-record-acf-fields-v2.5.md` for the high-level "when to write a hook" rules.

---

## Triggered Cluster Guidance

A triggered cluster is defined as more than one field being revealed by a trigger. By convention all clusters are
triggered when a specified recognition slug is present in `legal_recognitions`. Clusters normally consist of one
`*_context` field with at least one sister field. If an existing trigger of one field expands to two fields, it
should migrate as conditional term in `legal_recognitions`.

Not all recognition slugs in `legal_recognitions` trigger clusters. Some trigger the single `*_context` companion.
Others are simply bool states, with no companion fields. See [Slug-to-Companion Map] in spec for slug specifics.

When triggered the first field, `*_context`, carries the narrative glue for the cluster. Its name should relate
directly to the recognized doctrine represented by the slugs presence. The remaining sisters fields carry the
structured data that defines the cluster. They should be named to reflect the aspect of the doctrine they help
define, and their suffix should represent the data they hold (e.g. `*_class`, `*_scope`, `*_status`).

Each cluster will have at least one required field where the represented doctrine should be captured structurally
— mark it `[R]` in the spec's slug map. Additional sister fields may be required where necessary. Sister fields
that have further conditions before they are revealed — mark as `[+]`; they may also become required once revealed,
mark them twice as `[+][R]`.

### Details, Sentinels, and Companions

Avoid using `has-details` (defined in the main spec) when the selected value already lives inside a triggered
cluster rooted by a `*_context` field; use `see-context`. `see-context` is a sentinel used in cases where an
already-active `*_context` field exists, as is the case with most clusters. The sentinel is essentially editorial
guidance to use the companion field for freetext nuance regarding the specified trigger field, or the cluster as a
whole.

### Requiredness

Cluster requiredness hierarchy: a revealed and structured sister field is usually the required field for the
cluster. It will be marked `[R]` in the [Slug-to-Companion Map] of the main spec; Multiple sisters may be
required. In some cases the `*_context` field will be required as well. When `*_context` is revealed without
sisters, it is the `[R]` required field by default; It is also possible the only sister field or fields are
required by general rule; in this case the sister fields are not marked as required, and the `*_context` field
becomes the required field by default. Hooks should monitor all required fields in the cluster. If any are empty,
a validation error should identify both the triggering slug and the empty field or fields, for editorial review.

Requiredness must first be set in field attributes — generated ACF should set `'required' => 1` where applicable.
Hooks enforce requiredness when conditional logic alone cannot block the save or surface a proper flag.

### Non-Applicability and Ambiguity

Absence defines non-applicability — The absence of a recognition slug already means the doctrine does not
apply. Do not use redundant `none` values as choices in the cluster's select fields where their presence would
represent the recognized doctrine does not apply. Using `none`, `no-*` as choices in select fields that add
definition to the doctrine such as `*_scope` or `*_limit` is acceptable; in those cases the field must become
required. Where possible avoid using field negating choices. Prefer empty fields that are not required, where
logical.

Do not include ambiguous choice terms. Always prefer `see-context` (or `has-details` when necessary) where data may
reasonably be 'unclear', 'mixed' or 'varies'. If the possible data can genuinely be classified as 'mixed' or
'varies' and not does require further nuance, 'mixed'or 'varies' may be used; 'unclear' is simply unacceptable.
Annotate use with inline comments.

---

## Precedent Taxonomy Mapping

`extended_taxonomies` and `suppressed_taxonomies` use the same filtered taxonomy-term choices. `taxonomy`
choices come from the allowlist of legal-record taxonomies that precedent may realistically extend or suppress
(see [Eligible Taxonomy Allowlist] in the main spec); `term` choices are filtered by the selected `taxonomy` in
the same repeater row. Available terms for `extended_taxonomies` must not already be present in the parent
legal-record. Similarly terms for `suppressed_taxonomies` must be present. Hooks must monitor both values as slugs
and validate the values on save.

---

## Agency Filtering

`primary_agency` auto-fills with the first attached `ws-agency` post when empty; choices filter to currently
attached posts only. `local_agencies` filters to jx-applicable, non-federal `ws-agency` posts. `federal_agencies`
filters to federal `ws-agency` posts only.

Editor instructions on `primary_agency`: when empty, show `"Attach one ws-agency to local or federal first"`;
when non-empty, show `"Override primary_agency with any currently attached local or federal agency"`.

Future agency filtering may intersect `ws_process_type`, `ws_disclosure_targets`, and `ws_protected_disclosure`
taxonomies.

---

## Relationship Direction Contract

Relationship sync respects legal-record directionality. Parent-bearing records: `citation`, `construction`.
Child-bearing records: `statute`, `common_law`.

---

## Hooks To Come

The following hook requirements are known but not yet fully implemented or finalized. This is the working backlog
for validation, filtering, and editor guidance.

### Symbol Legend

The breadcrumb tables below use these markers:
- `[I]` — values in same-field can create invalid combinations.
- `[X]` — mutually exclusive values.
- `[U]` — umbrella-only value excludes granular and contradictory values.
- `[D]` — value may duplicate a concept represented by another field or value.
- `[R]` — value requires a value in another field.
- `[E]` — value excludes another field or or field value.

### Potential Invalid Multi-Value Combos

`[I]` — List of multi value fields that can have same-field invalid combinations:
`[X]` — Denotes specific conflict
`[U]` — Denotes umbrella-only value

- Reviewed multi-value fields with no currently defined same-field conflict are intentionally omitted.
- `legal_recognitions`
    * `[X]` `all-waivers-unenforceable`         excludes `civil-action-waiver`, `contractual-waiver`,
                                                         `collateral-claims-waiver`, `class-action-waiver`
    * `[X]` `blanket-sovereign-immunity-waived` excludes `sovereign-immunity-status`
    * `[X]` `class-action-permitted`            excludes `class-action-waiver`
- `burden_shifting_frameworks`       — `[X]` `mixed-motive`, `but-for`
- `protected_classes`                — `[U]` `all-employees-only`
- `employment_sectors`               — `[U]` `all-sectors-only`
- `preliminary_reinstatement_scopes` — `[U]` `full-pendency-only`
- `individual_liability_scopes`      — `[U]` `any-individual-only`
- `anti_slapp_protection_scopes`     — `[U]` `full-procedural-only`

### Potential Duplicate Cross-Field Concepts

`[D]` — List of values that may duplicate a concept represented value in another field:
- `legal_recognitions`
    * `pre-filing-notice` → `process_types` — `pre-suit-notice`

### Cross-Field Required Values

`[R]` — List of field values that require a value in another field:
- `process_types`
    * `qui-tam` → `legal_recognitions` — `qui-tam-action`
    * `civil-lawsuit` → `legal_recognitions` — `private-right-of-action`
- `sol_triggers`
    * `constructive-discharge-accrual` → `adverse_actions` — `constructive-discharge`
- `remedies`
    * `bounty-qui-tam-award` → `legal_recognitions` — `qui-tam-action`
- `remedy_caps.remedy_cap`
    * `punitive` → `remedies` — `punitive-damages`
    * `compensatory` → `remedies` — `compensatory-damages`
- `protected_classes`
    * `qui-tam-relator` → `process_types` — `qui-tam`

### Cross-Field Exclusions

`[E]` — List of values that exclude a value in another field:
- `legal_recognitions`
    * `statutory-preclusion` → excludes ordinary process/remedy pathway fields when claim is fully precluded
    * `no-retaliatory-evidence` → excludes evidence-use fields that treat retaliatory evidence as available
- `process_pathway_scope`
    * `direct-court` → excludes `exhaustion-required` in `legal_recognitions`
- `sovereign_immunity_status`
    * `not-waived` → excludes `sovereign_immunity_waiver_class`

### Special Cases

List of special case:
- `protected_classes` and `excluded_classes`: slug-to-slug exclusion.
    * Block save with hook. Requires editor resolution.
- `scope`: enforce precedent consistency.
    * `favorable` conflicts with `suppressed_taxonomies` is non-empty.
    * `adverse` conflicts with `extended_taxonomies` is non-empty.
    * `neutral` conflicts with both `extended_taxonomies` and `suppressed_taxonomies` when non-empty.
- `has_fee_shifting_phases`: auto-set true, when `fee_shifting_standard` is `none-american-rule`. Flag for
   editorial review with note that `none-american-rule` as the `fee_shifting_standard` requires at least one
   `fee_shifting_phases.phase` as an exception to `none-american-rule` or remove the recognition slug.

---

## Global Hook-Writing Guidance

These rules apply across hook types. They are intentionally implementation-facing but not tied to one final
helper API.

**Inputs and ordering.** Normalize values before validating — treat taxonomy, select, and multi-select values as
slug arrays internally even when ACF stores a scalar. Taxonomy-absence conditionals must evaluate before
slug-presence conditionals when both appear in the same cluster (e.g., `all-waivers-unenforceable` absent, then
`civil-action-waiver` present).

**Conflict validation.** Prefer explicit validation over silent conflict resolution. When a controlling field or
taxonomy term makes another value impossible, block save and flag the field that contains the conflict. The error
must name the controlling field/value and the conflicting field/value so the editor can resolve the record.

**Triggered-cluster requiredness.** The slug map is the source of truth — `[R]` fields must be non-empty when
their triggering slug is present. A `*_context` field is always required when it is the sole surfaced field in its
triggered cluster; a structured sister, when present, will usually be required `[R]` instead. Sisters do not
preclude `*_context` from being required as well.

**Umbrella, sentinel, and none values.** Umbrella hooks target values ending in `-only` — when a `-only` value is
present, flag granular or excluded values in the same field. Sentinels (`has-details`, `see-details`,
`see-context`) remain valid choices; it allows the companion to carry nuance regarding the umbrella value when
necessary. Blanket `none`/`no-*` values are valid only in required fields, where an empty field is not allowed.
They act as umbrella values and exclude affirmative choices in the same field when present.

**Exclusion and cross-field hooks.** Exclusion hooks should flag all fields in the excluded cluster — context,
sisters, details, and chained downstream conditionals. Hooks must identify the required-empty field or fields and
the value and field at the root of the exclusion. Hooks also need to monitor for stale values when a controlling
field changes; block save and flag stale values in their respective field for editor review.
Cross-field required hooks should validate both directions where required — when a value in a depended field
requires a value in a specific field, save should be blocked the flag should name both fields and both values.

**Repeaters.** Validate each row independently unless the rule is explicitly cross-row.

**Authorship.** Derived and merged fields are written by hooks only — editors should not be asked to maintain
hidden derived fields manually.

**Organization.** Prefer one hook per behavior family with a small rules table over many one-off hooks.

---

## Hook Examples

These examples are schematic. Project-helper stand-ins are documented once in [Hook Helpers] below; their final
implementations may evolve, but the example shape will continue to read correctly because helpers are referenced
by intent rather than by implementation.

### Hook Helpers

The hook examples in this section reference a small set of project-helper stand-ins. They are documented here as
a single source of truth rather than alongside individual examples. Final implementations may live in `ws-core`
utility files; when a helper signature changes, update this section first.

**ACF value access.**

- `ws_hooked_get(int $post_id, string $field)` — read the current saved value of an ACF field.
- `ws_hooked_set(int $post_id, string $field, mixed $value)` — write a value to an ACF field.

**Taxonomy slug operations.**

- `ws_hooked_has_slug(int $post_id, string $taxonomy_field, string $slug)` — true when the slug is attached to
  the post in the given taxonomy field.
- `ws_hooked_has_child_slug(int $post_id, string $taxonomy_field, string $parent_slug)` — return true when any term
  attached to the post is a descendant of the named parent slug. Centralizes parent/child resolution so callers
  don't enumerate child slugs by hand and don't break when new children are added to the seeder.

**Field value checks.**

- `ws_hooked_has_value(int $post_id, string $field, string $value)` — return true when the field's saved value
  equals or contains the given value (works for scalar and array fields).

**Pattern helpers.**

- `ws_hooked_sentinels(array $values)` — return the subset of values matching `has-*` or `see-*` patterns.
  Centralizing sentinel detection means adding a future sentinel (e.g., `has-limits`, `see-policy`) requires no
  changes to any hook that excludes sentinels from its granular-value comparisons.

**Composition helpers.**

These helpers operate across multiple ACF fields on a single post. Each one captures a recurring shape — merge or
fall-through — so callers can wire field names into the helper rather than re-implement the body each time.

- `ws_hooked_merge(int $post_id, array $source_fields)` — read each named field, treat its value as an
  array, merge them, dedupe, drop empties, and return the merged array. Caller writes the result wherever needed.
- `ws_hooked_first_filled(int $post_id, array $source_fields)` — return the first non-empty value among the
  named fields, in order. Used for derived fields with fallback chains (e.g., prefer `effective_date`, fall back
  to `date`).

**Error reporting.**

- `ws_hooked_error(string $field, string $message)` — emit a validation error for the named field with the
  given message. Causes ACF to block save and surface the message to the editor.

### Merge Arrays

Use this pattern for hidden merged relationship fields such as `_related_agencies`, `_precedent_ids`, and
`_parent_ids`. The same shape works for any merged hidden field — change only the source and target field names.

```php
function ws_merge_related_agencies(int $post_id): void {
    ws_hooked_set($post_id, '_related_agencies',
        ws_hooked_merge($post_id, ['local_agencies', 'federal_agencies']));
}
```

### Derive A Value

Use this pattern when a visible or hidden field is derived from another field and should stay synchronized. The
fall-through across source fields is agnostic; the transform (here, year extraction) is domain-specific and stays
inline.

```php
function ws_derive_effective_year(int $post_id): void {
    $source_date = ws_hooked_first_filled($post_id, ['effective_date', 'date']);

    ws_hooked_set($post_id, 'effective_year', $source_date ? substr($source_date, 0, 4) : '');
}
```

### Block Granular Values When Umbrella Is Present

Use this pattern for `[U]` fields. The hook target is the `-only` value.

```php
function ws_validate_umbrella_only(int $post_id, string $field, string $only_value): void {
    $values = (array) ws_hooked_get($post_id, $field);
    $sentinels = ws_hooked_sentinels($values);

    if (!in_array($only_value, $values, true)) {
        return;
    }

    $granular = array_diff($values, [$only_value], $sentinels);

    if ($granular) {
        ws_hooked_error($field, "$only_value cannot be combined with granular values.");
    }
}
```

### Enforce Triggered-Cluster Requiredness

Use this pattern for slug-map `[R]` fields.

```php
function ws_validate_required_cluster_field(
    int $post_id,
    string $trigger_slug,
    string $required_field
): void {
    if (!ws_hooked_has_slug($post_id, 'legal_recognitions', $trigger_slug)) {
        return;
    }

    if (empty(ws_hooked_get($post_id, $required_field))) {
        ws_hooked_error($required_field, "$required_field is required when $trigger_slug is selected.");
    }
}
```

### Apply Taxonomy-Absence Exclusion First

Current dogma: flag only. Surface the exclusionary value in its field, and surface the values it excludes in
their respective fields. Do not auto-resolve by removing terms or clearing fields.

Use this pattern for clusters such as waiver fields where a blocking slug must be absent before specific clusters
can exist.

```php
function ws_validate_waiver_clusters_when_blocked(int $post_id): void {
    if (!ws_hooked_has_slug($post_id, 'legal_recognitions', 'all-waivers-unenforceable')) {
        return;
    }

    foreach ([
        'civil-action-waiver',
        'contractual-waiver',
        'collateral-claims-waiver',
        'class-action-waiver',
    ] as $excluded_slug) {
        if (ws_hooked_has_slug($post_id, 'legal_recognitions', $excluded_slug)) {
            ws_hooked_error('legal_recognitions',
                "$excluded_slug cannot be combined with all-waivers-unenforceable.");
        }
    }
}
```

### Enforce Required None/No Exclusivity

Use this pattern for `[N]` values (negative value exclusivity is currently unused).

```php
function ws_validate_none_value(string $field, array $values, string $none_value): void {
    if (!in_array($none_value, $values, true)) {
        return;
    }

    $sentinels = ws_hooked_sentinels($values);
    $others = array_diff($values, [$none_value], $sentinels);

    if ($others) {
        ws_hooked_error($field, "$none_value cannot be combined with affirmative values.");
    }
}
```

### Flag Hidden Stale Values

Current dogma: flag only. Surface stale values in their specific field, and name the field/value that made them
stale. Do not auto-resolve by wiping hidden values.

### Enforce Cross-Field Required Values

Use this pattern for `[R]` breadcrumbs outside the slug map.

```php
function ws_validate_cross_field_requirement(
    int $post_id,
    string $source_field,
    string $source_value,
    string $required_field,
    string $required_value
): void {
    if (!ws_hooked_has_value($post_id, $source_field, $source_value)) {
        return;
    }

    if (!ws_hooked_has_value($post_id, $required_field, $required_value)) {
        ws_hooked_error(
            $required_field,
            "$source_field:$source_value requires $required_field:$required_value."
        );
    }
}
```

### Validate Same-Field Multi-Select Exclusion

Use this pattern for `[X]` pairs in multi-select fields where two specific values are mutually exclusive, such as
`mixed-motive` and `but-for` in `burden_shifting_frameworks`. Use `ws_hooked_error` rather than auto-cleanup so
the editor decides which value to keep.

```php
function ws_validate_incompatible_pair(
    int $post_id,
    string $field,
    string $value_a,
    string $value_b
): void {
    $values = (array) ws_hooked_get($post_id, $field);

    if (in_array($value_a, $values, true) && in_array($value_b, $values, true)) {
        ws_hooked_error(
            $field,
            "$value_a and $value_b cannot both be selected in $field; choose one."
        );
    }
}

// Example call:
// ws_validate_incompatible_pair($post_id, 'burden_shifting_frameworks', 'mixed-motive', 'but-for');
```

When a single field carries multiple incompatible pairs, wrap the call in a loop over a `$forbidden_pairs` array
declared near the field definition. Keep one declaration per field so the rule table is greppable.

### Validate Repeater-Row Sub-Field Exclusion

Use this pattern when sub-field values must not co-occur within a single repeater row, such as `felony` and
`misdemeanor` in `malicious_reporting_sanctions.sanction_penalty`. The rule is row-scoped — the same two values
are valid across separate rows. Identify the offending row in the error message so the editor can navigate to it.

```php
function ws_validate_repeater_row_exclusion(
    int $post_id,
    string $repeater_field,
    string $sub_field,
    string $value_a,
    string $value_b
): void {
    $rows = (array) ws_hooked_get($post_id, $repeater_field);

    foreach ($rows as $idx => $row) {
        $row_values = (array) ($row[$sub_field] ?? []);

        if (in_array($value_a, $row_values, true) && in_array($value_b, $row_values, true)) {
            $row_num = $idx + 1;
            ws_hooked_error(
                "$repeater_field.$sub_field",
                "Row $row_num: $value_a and $value_b cannot appear in the same row. Use a separate row for each."
            );
        }
    }
}

// Example call:
// ws_validate_repeater_row_exclusion(
//     $post_id,
//     'malicious_reporting_sanctions',
//     'sanction_penalty',
//     'felony',
//     'misdemeanor'
// );
```

### Apply Cross-Taxonomy AND-Conditional Guard

Use this pattern for sister fields whose validity depends on a triggering slug in `legal_recognitions` AND a
cross-taxonomy condition in another taxonomy, such as `is_cats_paw_liability_extended`. Flag the field when its
prerequisites are absent.

```php
function ws_validate_cats_paw_extension_guard(int $post_id): void {
    if (!ws_hooked_get($post_id, 'is_cats_paw_liability_extended')) {
        return;
    }

    // Both conditions must hold for the extension flag to remain valid:
    //   1. Sibling cluster active: 'cats-paw-liability' present in legal_recognitions
    //   2. Cross-taxonomy:         any child of 'associates-of-whistleblower' in protected_classes
    $sibling_active    = ws_hooked_has_slug($post_id, 'legal_recognitions', 'cats-paw-liability');
    $associate_present = ws_hooked_has_child_slug($post_id, 'protected_classes', 'associates-of-whistleblower');

    if (!$sibling_active || !$associate_present) {
        ws_hooked_error('is_cats_paw_liability_extended',
            'Extended cat\'s paw liability requires cats-paw-liability and an associates-of-whistleblower protected class.');
    }
}
```
