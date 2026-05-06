# WS ACF Hook Guidance (v1.0)

**Purpose:** Reusable hook patterns, helper definitions, and validation philosophy for ACF field sets across the
WS codebase. This document explains *how* hooks should behave; domain-specific specs (legal records, assist-orgs,
etc.) declare *which* hooks each record type needs and supply the cross-field tables that drive them.

**Scope:** Generic patterns and helpers. Examples may reference fields from existing implementations (most often
legal records) for illustration. The pattern is the contract; the example is the vehicle.

**Companion document.** See `ws-acf-field-guidance-v1.0.md` for naming rules, companion-suffix doctrine,
sentinels, and conditional annotation forms. Hook behavior assumes those rules are in force.

---

## Hook Philosophy

**Validation over cleanup.** When a hook detects a conflict — a required field is empty, an exclusion is
violated, a stale value lingers in a hidden field — the hook **must** block save and surface a validation error
naming both the causal field/value and the affected field/value. The hook **must never** silently clear, rewrite,
or
auto-resolve the conflict. Editors are responsible for resolution because legal-record correctness (and similar
domain correctness) depends on editorial judgment that hooks cannot replicate. Validation errors **must** read as
editorial guidance rather than terse failure messages.

This rule has two consequences. First, hook implementations that previously cleared values on conflict are
deprecated; rewrite them to flag instead. Second, validation errors must always identify *both ends* of the
conflict so the editor **is forced to** make a deliberate choice rather than guessing what the system found
offensive.

**ACF attributes versus hook enforcement.** ACF field attributes (`'required' => 1`) **must** enforce required
fields when the field's visibility is fully controlled by ACF conditional logic and the visibility itself is safe
under all save paths. Hooks must still validate conditional requirements that ACF cannot reliably enforce —
typically cross-field, cross-tab, or taxonomy-driven conditions — and must report both the triggering slug or
value and the empty required field.

**Authorship boundary.** Derived and merged fields are written by hooks only. Editors **must never** be asked to
maintain hidden derived fields manually; if an editor finds themselves doing so, the hook is missing.

**Hook organization.** Prefer one hook per behavior family with a small rules table over many one-off hooks. A
rules table keeps the legitimate variations greppable and the inert ones obvious. Reuse hook logic wherever
possible — a single hook branching by `get_post_type()` beats near-identical hooks per CPT.

---

## Triggered Cluster Guidance

A *triggered companion* is one field revealed by a trigger. A *triggered cluster* is a companion plus one or
more sister fields revealed by the same primary gate.

Most clusters are rooted in the record's recognition taxonomy because those slugs represent doctrine-level or
operational bool states. A recognition slug **is capable of** triggering:

- no companion, where the slug alone captures the state;
- one `*_context` companion;
- a full cluster: one `*_context` companion plus sister fields.

Some clusters **are permitted to be** rooted in a non-recognition field when the trigger is a core classificatory
value rather
than a bool-state about that value. In those cases, the domain spec must document the trigger explicitly and the
hook must follow the same `[R]` requiredness rules used for recognition-rooted clusters.

The first companion field normally carries the narrative context for the cluster. Sister fields carry structured
values or narrower explanations that belong to the same triggered state.

### Required Fields

The slug map (in the domain spec) is the source of truth for conditionally required cluster fields. Fields marked
`[R]` must be non-empty when their triggering slug is present and any additional `[+]` condition has been
satisfied.

If a triggered cluster reveals only one companion field, that companion is required by default. If a cluster has
structured sisters, at least one structured sister is usually marked `[R]`; the context field **must** also be
marked
`[R]` when narrative explanation is mandatory in addition to structured detail. Sisters do not preclude
`*_context` from being required as well.

When the only sister fields in a cluster are universally required by general rule (e.g., `*_value` and `*_unit`
duration
pairs), their individual `[R]` markers **must** be omitted from the slug-map. Instead, the cluster's `*_context`
field **must** carry the `[R]` marker to unconditionally anchor the cluster's required validation state.

ACF field attributes **are authorized to** enforce required fields when visibility conditions make that safe. Hooks
must still
validate conditional requirements that ACF cannot reliably enforce, and must report both the triggering slug or
value and the empty required field.

### Details, Sentinels, and Companions Inside Clusters

Avoid `has-details` inside a cluster rooted on `*_context` when the existing context field **is capable of
carrying** the needed
explanation. Use `see-context` instead — it directs the editor to the already-visible companion without creating
a redundant details field.

Use `has-details` inside a cluster only when the extra explanation belongs to a specific sister value and **must**
not be mixed into the cluster context. Common approved shapes: a sister select where one value requires a narrow
explanation distinct from the cluster narrative; a multi-select sister where `has-details` is one value among
otherwise structured choices; a record-type-specific field where the details field already exists by naming
convention.

Use `see-gloss` when the already-visible companion is a `*_gloss` rather than a `*_context`.

### Non-Applicability and Ambiguity

Absence defines non-applicability. When a recognition slug is absent, the doctrine or state it represents does
not apply; the cluster is hidden and no further validation is needed. Do not add redundant `none` values to
cluster select fields just to express "this cluster doesn't apply" — absence already conveys that.

Using `none` or `no-*` as choices in select fields that further qualify an active doctrine (e.g., `*_scope` or
`*_limit`) is acceptable. In those cases the field is required when the cluster is active, and the negation value
must exclude all affirmative siblings in the same field. Mark the slug `[R]` in the slug map.

Avoid ambiguous values such as `other`, `unclear`, `mixed`, and `varies`. Prefer `see-context` or `has-details`
when a companion field can carry the nuance. `mixed` and `varies` may be used only when the value is itself a
meaningful classification and no additional nuance is expected. `other` and `unclear` are not valid active
choices.

---

## Hook Behavior Patterns

These patterns apply across hook types. They are intentionally implementation-facing but not tied to any specific
helper API.

### Inputs and Ordering

Normalize values before validating. Treat taxonomy, select, and multi-select values as slug arrays internally even
when ACF stores a scalar. When multiple conditions evaluate against the same cluster, taxonomy-absence
conditionals must evaluate before slug-presence conditionals — otherwise an absent blocking slug appears not to
exist when the real reason is that the cluster has been excluded.

### Umbrella, Sentinel, and Negation Values

**Umbrella values.** Values ending in `-only` are umbrella values. When present in a multi-value field, they
exclude granular sibling values in the same field. Sentinels are not granular sibling values and **are permitted
to** coexist with
umbrella values.

**Trigger sentinels.** `has-details` and other `has-*` values reveal a companion field.

**Redirect sentinels.** `see-context` and `see-gloss` do not reveal fields. They point the editor to an
already-visible companion.

**Blanket negation values.** Generic `none`, `none-*`, and `no-*` values are reserved. They are valid only in
required fields where an empty value cannot express the legal or operational result. No generic blanket negation
value is currently active across the codebase; domain specs **are the exclusive authority to** activate one with
explicit documentation.

**Domain-specific exceptions.** Some legal terms read as negations but are not generic sentinels (e.g.,
`none-american-rule` in legal-record fee-shifting fields). These are permitted as actual values, not sentinels,
and must be documented in their domain spec.

### Repeaters

Validate each row independently unless the rule is explicitly cross-row. Cross-row rules (e.g., uniqueness across
rows) must be flagged as such in the domain spec; absent that flag, hooks operate row-scoped.

### Exclusion and Cross-Field Hooks

When a controlling value or taxonomy term excludes another value or cluster, the hook **must** flag every affected
field — context, sisters, details, and any chained downstream conditionals — naming the controlling field/value
at the root of the exclusion.

Hooks must also monitor for stale values when a controlling field changes. Block save and flag stale values in
their respective fields for editor review. Do not auto-clear; the editor must decide whether the value **must** be
removed or whether the controlling field was the field that changed in error.

Cross-field required hooks **must unconditionally** validate both directions where required. When a value in a
depended-on field
requires a value in a specific field, save **must** be blocked and the flag **must** name both fields and both
values.

### Cross-Cluster Consistency

When a cluster's authoritative gate lives in the recognition taxonomy and a contributing sister-taxonomy term
implies the same recognition state, register a consistency hook that flags divergence in either direction. The
error must name both fields and both values. This pattern keeps the recognition taxonomy and any related
taxonomies aligned so the recognition layer remains the single source of truth for state.

---

## Hook Helpers

Hook examples in this document and in domain specs reference a small set of project-helper stand-ins. They are
documented here as a single source of truth. Final implementations **must** live in `ws-core` utility files; when a
helper signature changes, update this section first and then propagate to every doc that references it.

**ACF value access.**

- `ws_hooked_get(int $post_id, string $field)` — read the current saved value of an ACF field.
- `ws_hooked_set(int $post_id, string $field, mixed $value)` — write a value to an ACF field.

**Taxonomy slug operations.**

- `ws_hooked_has_slug(int $post_id, string $taxonomy_field, string $slug)` — true when the slug is attached to
  the post in the given taxonomy field.
- `ws_hooked_has_child_slug(int $post_id, string $taxonomy_field, string $parent_slug)` — true when any term
  attached to the post is a descendant of the named parent slug. Centralizes parent/child resolution so callers
  don't enumerate child slugs by hand and don't break when new children are added to the seeder.

**Field value checks.**

- `ws_hooked_has_value(int $post_id, string $field, string $value)` — true when the field's saved value equals
  or contains the given value (works for scalar and array fields).

**Pattern helpers.**

- `ws_hooked_sentinels(array $values)` — return the subset of values matching `has-*` or `see-*` patterns.
  Centralizing sentinel detection means adding a future sentinel requires no changes to any hook that excludes
  sentinels from its granular-value comparisons.

**Composition helpers.** These helpers operate across multiple ACF fields on a single post. Each captures a
recurring shape — merge or fall-through — so callers **must** wire field names into the helper rather than
re-implement the body each time.

- `ws_hooked_merge(int $post_id, array $source_fields)` — read each named field, treat its value as an array,
  merge them, dedupe, drop empties, and return the merged array. Caller writes the result wherever needed.
- `ws_hooked_first_filled(int $post_id, array $source_fields)` — return the first non-empty value among the named
  fields, in order. Used for derived fields with fallback chains.

**Error reporting.**

- `ws_hooked_error(string $field, string $message)` — emit a validation error for the named field with the given
  message. Causes ACF to block save and surface the message to the editor.

---

## Hook Examples

These examples are schematic. Project-helper stand-ins are documented above; example bodies remain readable as
helpers evolve because helpers are referenced by intent rather than by implementation.

### Merge Arrays

Use this pattern for merged relationship fields. The same shape works for any merged field — change only the
source and target field names. Most merged target fields are hidden (denoted by leading underscore).

```php
function ws_merge_related_agencies(int $post_id): void {
    ws_hooked_set($post_id, '_related_agencies',
        ws_hooked_merge($post_id, ['local_agencies', 'federal_agencies']));
}
```

### Derive a Value

Use this pattern when a visible or hidden field is derived from another field and **must** stay synchronized. The
fall-through across source fields is agnostic; the transform (here, year extraction) is domain-specific and
stays inline.

```php
function ws_derive_effective_year(int $post_id): void {
    $source_date = ws_hooked_first_filled($post_id, ['effective_date', 'date']);

    ws_hooked_set($post_id, 'effective_year', $source_date ? substr($source_date, 0, 4) : '');
}
```

### Block Granular Values When Umbrella Is Present

Use this pattern for umbrella `-only` fields. The hook target is the `-only` value.

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

Use this pattern for slug-map `[R]` fields. The hook fires when the trigger slug is present and the required
field is empty.

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

Use this pattern when a blocking slug must be absent before specific clusters can exist. Current dogma is
flag-only: surface the exclusionary value in its field, surface the values it excludes in their respective
fields, do not auto-resolve.

```php
function ws_validate_waiver_clusters_when_blocked(int $post_id): void {
    if (!ws_hooked_has_slug($post_id, 'legal_recognitions', 'all-plaintiff-waivers-void')) {
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
                "$excluded_slug cannot be combined with all-plaintiff-waivers-void.");
        }
    }
}
```

### Flag Hidden Stale Values

Use this pattern when a controlling field changes and previously-revealed dependent fields **might** still hold
values.
Surface stale values in their specific field, name the field/value that created the stale state, do not
auto-resolve. Implementation depends on which controlling field changed and which dependent fields could be
stale; the pattern is consistent: detect, flag, name both ends.

### Enforce Cross-Field Required Values

Use this pattern for `[R]` requirements outside the slug map (cross-tab and cross-field requirements documented
in the domain spec's hook tables).

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

Use this pattern for `[X]` mutually-exclusive pairs in multi-select fields. Use `ws_hooked_error` rather than
auto-cleanup so the editor decides which value to keep.

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
declared near the field definition. Keep one declaration per field so the rule table stays greppable.

### Apply Cross-Taxonomy AND-Conditional Guard

Use this pattern for sister fields whose validity depends on a triggering slug in the recognition taxonomy AND a
cross-taxonomy condition in another taxonomy. Flag the field when its prerequisites are absent rather than
clearing the field's value.

```php
function ws_validate_dual_condition_guard(int $post_id, string $bool_field,
    string $sibling_slug, string $cross_taxonomy_field, string $parent_slug
): void {
    if (!ws_hooked_get($post_id, $bool_field)) {
        return;
    }

    $sibling_active   = ws_hooked_has_slug($post_id, 'legal_recognitions', $sibling_slug);
    $cross_tax_active = ws_hooked_has_child_slug($post_id, $cross_taxonomy_field, $parent_slug);

    if (!$sibling_active || !$cross_tax_active) {
        ws_hooked_error($bool_field,
            "$bool_field requires $sibling_slug and any $parent_slug descendant in $cross_taxonomy_field.");
    }
}
```

Domain specs apply this pattern to specific fields by passing the relevant slug and taxonomy names.

---

— drafted for Dejunai by Claude (Anthropic), session of 2026-05-06
