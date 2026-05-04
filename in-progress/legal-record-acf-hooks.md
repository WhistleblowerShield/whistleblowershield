## Legal Record ACF Hook Requirements

This document captures hook requirements for the legal-record ACF model. The field spec identifies *where* a hook
is needed; this doc explains the shared behavior, validation rules, and known hook backlog. See the [Hook Rules]
section of `legal-record-acf-fields.md` for the high-level "when to write a hook" rules.

---

## Triggered Cluster Guidance

A triggered cluster begins when a slug in `legal_recognitions` reveals one or more related fields. A cluster may
include a `*_context` companion, one or more structured sister fields, and downstream conditionals that depend on
specific values *inside* the cluster.

### Cluster Anatomy

When `slug-x` is present in `legal_recognitions`, the corresponding `slug_x_context` field is revealed. The
`*_context` field is descriptive and not required by default. Use `see-context` when a structured choice is
directionally correct but incomplete and the overflow belongs in the cluster's `*_context`; `see-context` may
appear alone or alongside structured values when that combination is meaningful for review.

Each slug-triggered cluster should have one primary required structured sister field when the doctrine can be
captured structurally — mark it `[R]` in the slug map. Additional sister fields are allowed when they are
genuinely parallel structured companions arising directly from slug presence (rather than from a downstream field
choice).

A field should remain a sister only when this sentence is true:

> This field becomes relevant because the slug is present, not because another field in the cluster took a
> specific value.

If the sentence is false, model the field as a direct conditional on the relevant trigger field and value rather
than as a fake sibling. Example:

```text
sovereign-immunity-status -> sovereign_immunity_context + sovereign_immunity_status[R]

sovereign_immunity_waiver_class is conditional on sovereign_immunity_status is NOT not-waived.
sovereign_immunity_scope is conditional on sovereign_immunity_status is non-empty.
sovereign_immunity_status_details is conditional on sovereign_immunity_status is has-details.
```

### Details Companions

`has-details` is reserved for dedicated `*_details` companions — when present in a field, it triggers the
same-stem `*_details` field. `see-details` is reserved for cases where an already-active details field exists
elsewhere in the same cluster.

### Requiredness

Requiredness belongs first in field attributes — generated ACF should set `'required' => 1` where applicable.
Hooks enforce requiredness when conditional logic alone cannot do so safely; when a triggering slug is present
and the cluster's required field is empty, validation must block save and identify both the triggering slug and
the missing field so the editor can either complete the field or remove the slug.

Cluster requiredness hierarchy: a matching structured sister (when one exists) is the required field and gets
`[R]`; `*_context` is revealed but not required by default; if no structured sister is revealed, `*_context`
becomes the required field; hooks block save whenever the cluster's required field is empty. This prevents a
selected recognition slug from creating an empty cluster with no captured substance.

### Non-Applicability and Ambiguity

Absence can define non-applicability — if the absence of a recognition slug already means the cluster does not
apply, do not add redundant `none` choices inside that cluster's structured fields. Use `none`, `no-*`, or
equivalent non-applicability values only when the field itself must still be answered; in those cases the field
must be required, and the non-applicability value must be monitored as mutually exclusive with affirmative values
in the same field.

Do not encode ambiguity as a fake enum when narrative capture is cleaner. Avoid unclear-type choices where
`has-details` or `see-context` provides a better review-state path.

---

## Precedent Taxonomy Mapping

`extended_taxonomies` and `suppressed_taxonomies` use the same controlled taxonomy-term picker. `taxonomy`
choices come from one allowlist of legal-record taxonomies that precedent may extend or suppress (see [Eligible
Taxonomy Allowlist] in the spec); `term` choices are filtered by the selected `taxonomy` in the same repeater
row. Store both values as slugs for readable ingest, export, and review. Validate on save that the selected
`term` exists in the selected `taxonomy`. Free-entry taxonomy or term names are not allowed.

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

- `[R]` — value may require a value in another field.
- `[C]` — value may conflict with a value or absence in another field.
- `[D]` — value may duplicate a concept represented by another field or value.
- `[E]` — value may exclude another field or cluster from existing.
- `[W]` — stale hidden value that must be wiped when the controlling field no longer permits the dependent field.
- `[U]` — umbrella-only value vs. granular value it already covers.
- `[X]` — mutually exclusive values.
- `[N]` — required none/no answer vs. affirmative values.
- `[O]` — overloaded field; too many conflict styles to summarize here.
- `[Q]` — candidate retained for later review; no specific conflict class assigned yet.

Parent slugs are not selectable values and are not conflicts by themselves. If children from two parent families
conflict, note as `[X] children 'parent-x' v. 'parent-y'`.

### Contradiction Guards

Document each contradiction guard at the affected field's definition. Note whether the guard requires cross-field
monitoring; if it also crosses tabs, list it under [Cross-Tab Guards] below.

- `protected_classes` and `excluded_classes`: the same class slug must never appear in both. When overlap is
  detected, flag for editor resolution rather than auto-removing — the correct side is legal-context dependent.
- `garcetti-exception`: invalid unless `public-sector` is in `employment_sectors`. When `public-sector` is
  absent, remove `garcetti-exception` and clear `garcetti_exception_context` and any sister fields.
- `mitigation-exception`: invalid without `mitigation-required` in `legal_recognitions`. When
  `mitigation-required` is absent, remove `mitigation-exception` and clear `mitigation_exception_context` and any
  sister fields.
- `all-waivers-blocked`: excludes specific waiver clusters; see [Cross-Field Exclusions].
- `jury-trial`: invalid without `private-right-of-action` in `legal_recognitions`. When `private-right-of-action`
  is absent, remove `jury-trial` and clear `jury_trial_context` and any sister fields.
- `exhaustion-required`: invalid when `process_pathway_scope` is `direct-court`. When `direct-court` is set,
  remove `exhaustion-required` and clear `exhaustion_required_context` and any sister fields.
- `direct-filing-permitted`: invalid with `exhaustion-required`. When `direct-filing-permitted` is in
  `process_types`, remove `exhaustion-required` and clear `exhaustion_required_context` and any sister fields.
- `blanket-sovereign-immunity-waiver`: excludes the `sovereign-immunity-status` slug and its full status cluster;
  see [Cross-Field Exclusions].
- `malicious_reporting_sanctions.sanction_penalty`: `felony` and `misdemeanor` must not appear in the same
  repeater row — use separate rows for separate criminal tracks.
- `scope`: enforce precedent taxonomy bucket consistency. `favorable` clears `suppressed_taxonomies`; `adverse`
  clears `extended_taxonomies`; `neutral` clears both; `dual-effect` allows both.
- `burden_shifting_frameworks`: `mixed-motive` is incompatible with `but-for` in most formulations;
  multi-framework combinations need a validity check on the Burden of Proof tab.
- `election_of_remedies_rules`: `no-election-required` invalidates all other choices in the same field on the
  Retaliation tab.
- `is_employer_only_defendant`: when true, force `proper_defendants` to exactly `employer-entity` and clear every
  other value.
- Multi-select redirect values: evaluate whether `see-details` must not be combined with specific choices.
- `class` for precedent records: choices are likely mutually exclusive and need evaluation on the Identity tab.

### Cross-Tab Guards

Guards comparing fields on different tabs:

- `garcetti-exception` in `legal_recognitions` requires `public-sector` in `employment_sectors`.
- `exhaustion-required` in `legal_recognitions` conflicts with `direct-filing-permitted` in `process_types`.
- `protected_classes` and `excluded_classes` must not contain the same class slug.

### Sister-Block Guards

Guards comparing fields inside a single triggered block:

- **`fee_shifting` block (Processes & Remedies tab):** when `fee-shifting-standard` is in `legal_recognitions`,
  `none-american-rule` in `fee_shifting_standard` can only be set with phased exceptions — `fee_shifting_scopes`
  must be `has-phases` only, or `fee-shifting-standard` must be removed from `legal_recognitions`. Multi-select
  `fee_shifting_scopes` may also produce invalid combinations; review later.

### Mixed-Motive Remedy Notice

When `mixed-motive` is in `burden_shifting_frameworks` (Burden of Proof tab) and `mixed_motive_remedy_context`
(Processes & Remedies tab) is empty, emit a dismissible admin notice via `acf/save_post` or `admin_notices`
(attached through `current_screen`):

> "Mixed-motive framework selected — please complete the 'Mixed Motive Remedy Context' field on the Processes &
> Remedies tab."

Display on edit screens for all four legal-record CPTs. Tone: informative, not alarmist. Dismiss state need not
persist — the notice should re-emit each save while the condition holds.

### Blacklisting Extension Visibility

`is_blacklisting_extended` (Processes & Remedies tab) is conditionally revealed when `adverse_actions`
(Retaliation tab) includes `blacklisting`.

### Potential Invalid Multi-Value Combos

Breadcrumb list. Multi-select or multi-taxonomy fields that may need same-field invalid-combo review later.

- `legal_recognitions` — `[O]`
- `protected_action_standards` — `[X]` `per-se-protected`
- `protected_action_source` — `[Q]`
- `protected_actions` — `[Q]`
- `protected_disclosures` — `[Q]`
- `protected_classes` — `[U]` `all-employees-only`
- `excluded_classes` — `[Q]`
- `employment_sectors` — `[U]` `all-sectors-only`
- `disclosure_targets` — `[Q]`
- `adverse_actions` — `[Q]`
- `sol_triggers` — `[Q]`
- `filing_notice_targets` — `[Q]`
- `preservation_requirement_scopes` — `[Q]`
- `malicious_reporting_sanctions.conduct_sanctioned` — `[Q]`
- `malicious_reporting_sanctions.sanction_penalty` — `[X]` `felony`, `misdemeanor`
- `criminal_sanctions.sanction_conduct` — `[Q]`
- `process_types` — `[Q]`
- `fee_shifting_phases.phase_scope` — `[N]` `none`
- `remedies` — `[Q]`
- `preliminary_reinstatement_scopes` — `[U]` `full-pendency-only`
- `burden_shifting_frameworks` — `[X]` `mixed-motive`, `but-for`
- `employer_knowledge_scopes` — `[Q]`
- `employer_defenses` — `[Q]`
- `individual_liability_scopes` — `[U]` `any-individual-only`
- `anti_slapp_protection_scopes` — `[U]` `full-procedural-only`
- `election_of_remedies_rules` — `[N]` `no-election-required`
- `public_policy_sources` — `[Q]`
- `authority_sources` — `[Q]`
- `class` — `[X]`

### Potential Duplicate Cross-Field Concepts

Breadcrumb list. `[D]` values may duplicate a concept represented elsewhere.

- `legal_recognitions` — `[D]`
    * `pre-filing-notice` → `process_types` — `pre-suit-notice`
    * `criminal-sanctions` → `process_types` — `criminal-referral`
    * `private-right-of-action` → `process_types` — `civil-lawsuit`
    * `class-action` → `process_types` — `representative-action`
    * `civil-action-waiver` → `civil_action_waiver_scope` — waiver-permission values
    * `contractual-waiver` → `contractual_waiver_scope` — waiver-enforcement values
    * `class-action-waiver` → `civil_action_waiver_scope` — `permitted-individual-only`
    * `discovery-protection` → `adverse_actions` — `retaliatory-discovery`
    * `anti-slapp-protection` → `adverse_actions` — `retaliatory-litigation`
- `process_types` — `[D]`
    * `direct-filing-permitted` → `process_pathway_scope` — `direct-court`

### Potential Cross-Field Required Values

Breadcrumb list. `[R]` values may require a value in another field.

- `legal_recognitions` — `[R]`
    * `public-concern-required` → `employment_sectors` — `public-sector`
    * `garcetti-exception` → `employment_sectors` — `public-sector`
    * `jury-trial` → `legal_recognitions` — `private-right-of-action`
    * `mitigation-exception` → `legal_recognitions` — `mitigation-required`
    * `preliminary-reinstatement` → `remedies` — `reinstatement` or `interim-reinstatement`
    * `fee-shifting-standard` → `remedies` — `attorney-fees` or `attorney-fees-admin`
    * `equitable-interest-award` → `remedies` — `interest-on-backpay`
    * `internal-only-disclosure` → `process_types` — `internal-disclosure`
    * `internal-only-disclosure` → `disclosure_targets` — at least one child of `internal`
- `adverse_actions` — `[R]`
    * `constructive-discharge` → `sol_triggers` — `constructive-discharge-accrual`
- `remedies` — `[R]`
    * `bounty-qui-tam-award` → `process_types` — `qui-tam`
    * `bounty-qui-tam-award` → `protected_classes` — `qui-tam-relator`
    * `liquidated-damages` → `remedy_liquidated_multiplier` — non-empty
- `remedy_caps.remedy_cap` — `[R]`
    * `punitive` → `remedies` — `punitive-damages`
    * `compensatory` → `remedies` — `compensatory-damages`
- `protected_classes` — `[R]`
    * `qui-tam-relator` → `process_types` — `qui-tam`
- `burden_shifting_frameworks` — `[R]`
    * `mixed-motive` → `mixed_motive_remedy_context` — non-empty

### Potential Cross-Field Invalid Combos

Breadcrumb list. `[C]` values may conflict with a value or absence in another field.

- `legal_recognitions` — `[C]`
    * `exhaustion-required` → `process_pathway_scope` — `direct-court`
    * `exhaustion-required` → `process_types` — `direct-filing-permitted`
- `protected_classes` — `[C]`
    * same class slug → `excluded_classes` — same class slug
- `excluded_classes` — `[C]`
    * same class slug → `protected_classes` — same class slug
- `fee_shifting_scopes` — `[C]`
    * `has-phases` only requirement → `legal_recognitions` — `fee-shifting-standard`
- `fee_shifting_phases.phase_scope` — `[C]`
    * `none` → affirmative phase-scope values
- `election_of_remedies_rules` — `[C]`
    * `no-election-required` → affirmative election-rule values
- `is_employer_only_defendant` — `[C]`
    * true → `proper_defendants` — any value other than `employer-entity`
- `process_types` — `[C]`
    * `direct-filing-permitted` → `legal_recognitions` — `exhaustion-required`

### Potential Required-Empty Cleanup

Breadcrumb list. `[W]` marks stale hidden values that must be wiped when the controlling field no longer permits
the dependent field.

- `scope` — `[W]`
    * `favorable` → `suppressed_taxonomies` must be empty
    * `adverse` → `extended_taxonomies` must be empty
    * `neutral` → `extended_taxonomies` must be empty
    * `neutral` → `suppressed_taxonomies` must be empty
    * `dual-effect` → `extended_taxonomies` and `suppressed_taxonomies` may both carry values

### Potential Cross-Field Exclusions

Breadcrumb list. `[E]` values may exclude another field or cluster from existing.

- `legal_recognitions` — `[E]`
    * `all-waivers-blocked` → excludes `civil-action-waiver` cluster
    * `all-waivers-blocked` → excludes `contractual-waiver` cluster
    * `all-waivers-blocked` → excludes `collateral-claims-waiver` cluster
    * `all-waivers-blocked` → excludes `class-action-waiver` cluster
    * `blanket-sovereign-immunity-waiver` → excludes `sovereign-immunity-status` slug and its status cluster
    * `internal-only-disclosure` → excludes multiple external-disclosure paths; review later
    * `bad-faith-exclusion` → excludes protected-action good-faith posture
    * `statutory-preclusion` → excludes ordinary process/remedy pathway fields when claim is fully precluded
    * `no-retaliatory-evidence` → excludes evidence-use fields that treat retaliatory evidence as available
- `process_pathway_scope` — `[E]`
    * `direct-court` → excludes `exhaustion-required` cluster
- `fee_shifting_standard` — `[E]`
    * `none-american-rule` → excludes non-phased `fee_shifting_scopes`
- `sovereign_immunity_status` — `[E]`
    * `not-waived` → excludes `sovereign_immunity_waiver_class`

---

## Global Hook-Writing Guidance

These rules apply across hook types. They are intentionally implementation-facing but not tied to one final
helper API.

**Inputs and ordering.** Normalize values before validating — treat taxonomy, select, and multi-select values as
slug arrays internally even when ACF stores a scalar. Taxonomy-absence conditionals must evaluate before
slug-presence conditionals when both appear in the same cluster (e.g., `all-waivers-blocked` absent, then
`civil-action-waiver` present).

**Cleanup vs. validation.** Prefer deterministic cleanup over silent invalid state — when a controlling field
makes another field impossible, clear the stale field on save and validate that it remains empty. Use
`validation_error` instead when cleanup would destroy editorial judgment (e.g., overlapping `protected_classes`
and `excluded_classes` should flag for editor review, not auto-resolve).

**Triggered-cluster requiredness.** The slug map is the source of truth — `[R]` fields must be non-empty when
their triggering slug is present. A `*_context` field is required only when it is the sole surfaced field in its
triggered cluster, unless the map explicitly marks a structured sister `[R]`.

**Umbrella, sentinel, and none values.** Umbrella hooks target values ending in `-only` — when a `-only` value is
present, remove or reject granular sibling values in the same field. Sentinels (`has-details`, `see-details`,
`see-context`) are not granular values and may remain valid alongside an umbrella or exclusion value when the
field definition allows the companion to carry nuance. Required none/no values are valid only in required fields
and must exclude affirmative siblings in the same field when present.

**Exclusion, cleanup, and cross-field hooks.** Exclusion hooks should clear all fields in the excluded cluster —
context, sisters, details, and chained downstream conditionals. Required-empty cleanup hooks should clear hidden
stale values when a controlling field changes, then validate that hidden disallowed fields remain empty.
Cross-field required hooks should validate both directions that matter — if value A requires value B, the error
should name both fields and both values.

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

- `acf_value(int $post_id, string $field)` — read the current saved value of an ACF field.
- `set_acf_value(int $post_id, string $field, mixed $value)` — write a value to an ACF field.
- `clear_acf_values(int $post_id, array $fields)` — clear multiple ACF fields in one call.

**Taxonomy slug operations.**

- `has_slug(int $post_id, string $taxonomy_field, string $slug)` — return true when the named slug is currently
  attached to the post in the given taxonomy field.
- `has_any_child_slug(int $post_id, string $taxonomy_field, string $parent_slug)` — return true when any term
  attached to the post is a descendant of the named parent slug. Centralizes parent/child resolution so callers
  don't enumerate child slugs by hand and don't break when new children are added to the seeder.
- `remove_slugs(int $post_id, string $taxonomy_field, array $slugs)` — remove the listed slugs from a taxonomy
  field on the post.

**Field value checks.**

- `field_has_value(int $post_id, string $field, string $value)` — return true when the field's saved value
  equals or contains the given value (works for scalar and array fields).

**Pattern helpers.**

- `ws_filter_sentinels(array $values)` — return the subset of values matching `has-*` or `see-*` patterns.
  Centralizing sentinel detection means adding a future sentinel (e.g., `has-limits`, `see-policy`) requires no
  changes to any hook that excludes sentinels from its granular-value comparisons.

**Error reporting.**

- `validation_error(string $field, string $message)` — emit a validation error for the named field with the
  given message. Causes ACF to block save and surface the message to the editor.

### Merge Arrays

Use this pattern for hidden merged relationship fields such as `_related_agencies`, `_precedent_ids`, and
`_parent_ids`.

```php
function ws_merge_related_agencies(int $post_id): void {
    $local = (array) acf_value($post_id, 'local_agencies');
    $federal = (array) acf_value($post_id, 'federal_agencies');

    $merged = array_values(array_unique(array_filter(array_merge($local, $federal))));

    set_acf_value($post_id, '_related_agencies', $merged);
}
```

### Derive A Value

Use this pattern when a visible or hidden field is derived from another field and should stay synchronized.

```php
function ws_derive_effective_year(int $post_id): void {
    $effective_date = acf_value($post_id, 'effective_date');
    $date = acf_value($post_id, 'date');
    $source_date = $effective_date ?: $date;

    set_acf_value($post_id, 'effective_year', $source_date ? substr($source_date, 0, 4) : '');
}
```

### Block Granular Values When Umbrella Is Present

Use this pattern for `[U]` fields. The hook target is the `-only` value.

```php
function ws_validate_umbrella_only(int $post_id, string $field, string $only_value): void {
    $values = (array) acf_value($post_id, $field);
    $sentinels = ws_filter_sentinels($values);

    if (!in_array($only_value, $values, true)) {
        return;
    }

    $granular = array_diff($values, [$only_value], $sentinels);

    if ($granular) {
        validation_error($field, "$only_value cannot be combined with granular values.");
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
    if (!has_slug($post_id, 'legal_recognitions', $trigger_slug)) {
        return;
    }

    if (empty(acf_value($post_id, $required_field))) {
        validation_error($required_field, "$required_field is required when $trigger_slug is selected.");
    }
}
```

### Apply Taxonomy-Absence Exclusion First

Use this pattern for clusters such as waiver fields where a blocking slug must be absent before specific clusters
can exist.

```php
function ws_clear_waiver_clusters_when_blocked(int $post_id): void {
    if (!has_slug($post_id, 'legal_recognitions', 'all-waivers-blocked')) {
        return;
    }

    remove_slugs($post_id, 'legal_recognitions', [
        'civil-action-waiver',
        'contractual-waiver',
        'collateral-claims-waiver',
        'class-action-waiver',
    ]);

    clear_acf_values($post_id, [
        'civil_action_waiver_context',
        'civil_action_waiver_scope',
        'contractual_waiver_context',
        'contractual_waiver_scope',
        'collateral_claims_waiver_context',
        'class_action_waiver_context',
    ]);
}
```

### Enforce Required None/No Exclusivity

Use this pattern for `[N]` values such as `fee_shifting_phases.phase_scope = none` and
`election_of_remedies_rules = no-election-required`.

```php
function ws_validate_none_value(string $field, array $values, string $none_value): void {
    if (!in_array($none_value, $values, true)) {
        return;
    }

    $others = array_diff($values, [$none_value], ['see-context', 'has-details']);

    if ($others) {
        validation_error($field, "$none_value cannot be combined with affirmative values.");
    }
}
```

### Wipe Hidden Stale Values

Use this pattern for `[W]` cleanup rules where ACF conditionals hide fields but old values may remain saved.

```php
function ws_cleanup_precedent_scope(int $post_id): void {
    $scope = acf_value($post_id, 'scope');

    if ($scope === 'favorable') {
        clear_acf_values($post_id, ['suppressed_taxonomies']);
    }

    if ($scope === 'adverse') {
        clear_acf_values($post_id, ['extended_taxonomies']);
    }

    if ($scope === 'neutral') {
        clear_acf_values($post_id, ['extended_taxonomies', 'suppressed_taxonomies']);
    }
}
```

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
    if (!field_has_value($post_id, $source_field, $source_value)) {
        return;
    }

    if (!field_has_value($post_id, $required_field, $required_value)) {
        validation_error(
            $required_field,
            "$source_field:$source_value requires $required_field:$required_value."
        );
    }
}
```

### Enforce Exact-Only Boolean Cleanup

Use this pattern for boolean refinements such as `is_employer_only_defendant`.

```php
function ws_apply_employer_only_defendant(int $post_id): void {
    if (!acf_value($post_id, 'is_employer_only_defendant')) {
        return;
    }

    set_acf_value($post_id, 'proper_defendants', ['employer-entity']);
}
```

### Validate Same-Field Multi-Select Exclusion

Use this pattern for `[X]` pairs in multi-select fields where two specific values are mutually exclusive, such as
`mixed-motive` and `but-for` in `burden_shifting_frameworks`. Use `validation_error` rather than auto-cleanup so
the editor decides which value to keep.

```php
function ws_validate_incompatible_pair(
    int $post_id,
    string $field,
    string $value_a,
    string $value_b
): void {
    $values = (array) acf_value($post_id, $field);

    if (in_array($value_a, $values, true) && in_array($value_b, $values, true)) {
        validation_error(
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
    $rows = (array) acf_value($post_id, $repeater_field);

    foreach ($rows as $idx => $row) {
        $row_values = (array) ($row[$sub_field] ?? []);

        if (in_array($value_a, $row_values, true) && in_array($value_b, $row_values, true)) {
            $row_num = $idx + 1;
            validation_error(
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
cross-taxonomy condition in another taxonomy, such as `is_cats_paw_liability_extended`. Cleanup (rather than
`validation_error`) is correct here because the field is structurally meaningless when its prerequisites are
absent.

```php
function ws_apply_cats_paw_extension_guard(int $post_id): void {
    if (!acf_value($post_id, 'is_cats_paw_liability_extended')) {
        return;
    }

    // Both conditions must hold for the extension flag to remain valid:
    //   1. Sibling cluster active: 'cats-paw-liability' present in legal_recognitions
    //   2. Cross-taxonomy:         any child of 'associates-of-whistleblower' in protected_classes
    $sibling_active    = has_slug($post_id, 'legal_recognitions', 'cats-paw-liability');
    $associate_present = has_any_child_slug($post_id, 'protected_classes', 'associates-of-whistleblower');

    if (!$sibling_active || !$associate_present) {
        set_acf_value($post_id, 'is_cats_paw_liability_extended', false);
    }
}
```