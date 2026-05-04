## Legal Record ACF Hook Requirements

This document captures hook requirements for the legal-record ACF model. The main field spec should identify where
a hook is needed; this document explains the shared behavior, validation rules, and known hook backlog.

**Changelog (paired with spec v2.5):**

- Mixed-Motive Remedy Notice: corrected target tab reference from "Enforcement tab" to "Processes & Remedies tab".
- `bad-faith-exclusion` cross-field exclusion: corrected `good-faith-only` to `good-faith` (matches actual choice
  in `protected_action_standards`).
- Added three Hook Examples, addressing previously breadcrumb-only patterns:
  - **Validate Same-Field Multi-Select Exclusion** — pairwise `[X]` enforcement (e.g., `mixed-motive` vs `but-for`
    in `burden_shifting_frameworks`).
  - **Validate Repeater-Row Sub-Field Exclusion** — row-scoped enforcement (e.g., `felony` vs `misdemeanor` in
    `malicious_reporting_sanctions.sanction_penalty`).
  - **Apply Cross-Taxonomy AND-Conditional Guard** — slug-AND-cross-taxonomy cleanup pattern (e.g.,
    `is_cats_paw_liability_extended`); introduces `has_any_child_slug()` as a project helper stand-in.

---

## General Hook Guidance

Document hook requirements inline with the field definition that needs them. Use hooks for behavior that ACF field
settings cannot express reliably on their own.

Hooks are expected for:

- Derived fields that must auto-fill on load and on save.
- Merged hidden fields, such as `_related_agencies`, `_precedent_ids`, and `_parent_ids`, that must auto-fill on save.
- Derived select choices, such as `court` filtered by `jurisdiction`, that must filter on field load.
- Select, choice, and taxonomy fields that need anti-contradiction enforcement.
- Conditional clusters where companion and sister fields need cross-field validation.

Prefer unified hooks over duplicate hooks. A single hook that branches by `get_post_type()` is preferred over
near-identical hooks per CPT. Reuse hook logic wherever possible.

---

## Triggered Cluster Guidance

Triggered clusters begin when a slug in `legal_recognitions` reveals one or more related fields. A cluster may
include a `*_context` companion, one or more structured sister fields, and downstream conditionals that depend on
specific values inside the cluster.

### Context Companions

When `slug-x` is present in `legal_recognitions`, the corresponding `slug_x_context` field is revealed. The
`*_context` field is descriptive and is not required by default.

Use `see-context` when a structured choice is directionally correct but incomplete and the overflow belongs in the
cluster's `*_context` field. `see-context` may appear alone or alongside structured values when that combination is
meaningful for review.

### Required Sister Fields

Each slug-triggered cluster should have one primary required structured sister field when the doctrine can be
captured structurally. In the slug map, mark that field with `[R]`.

Additional sister fields are allowed when they are genuinely parallel structured companions that arise directly from
slug presence, rather than from a downstream field choice.

A field should remain a sister only when this sentence is true:

> This field becomes relevant because the slug is present, not because another field in the cluster took a specific
> value.

If that sentence is false, model the field as a direct conditional on the relevant trigger field and value.

### Direct Conditionals

Prefer direct conditionals over fake siblings. If a field depends on a specific value of another field in the
cluster, declare the dependency explicitly with the accepted conditional annotation form.

Example:

```text
sovereign-immunity-status -> sovereign_immunity_context + sovereign_immunity_status[R]

sovereign_immunity_waiver is conditional on sovereign_immunity_status is NOT not-waived.
sovereign_immunity_scope is conditional on sovereign_immunity_status is non-empty.
sovereign_immunity_status_details is conditional on sovereign_immunity_status is has-details.
```

### Details Companions

Use `has-details` only for dedicated `*_details` companions. When a field includes `has-details`, that sentinel
triggers the same-stem `*_details` field. Use `see-details` only when an already-active details field exists
elsewhere in the same cluster.

### Requiredness

Requiredness belongs first in field attributes. When a sister field is required inside a triggered cluster,
generated ACF should set `'required' => 1` where applicable. Hooks should enforce requiredness when conditional
logic alone cannot do so safely.

If a triggering slug is present and the required field for that cluster is empty, validation must block save. The
validation error should identify both the triggering slug and the missing field, so the editor can either complete
the field or remove the slug.

If `*_context` is the only field revealed by a triggered cluster, `*_context` becomes the required field for that
cluster. This prevents a selected recognition slug from creating an empty cluster with no captured substance.

Cluster requiredness hierarchy:

- If a matching structured sister exists, that sister is the required field and gets `[R]`.
- `*_context` is revealed but not required by default.
- If no structured sister is revealed, `*_context` becomes required.
- Hooks block save whenever the triggered cluster's required field is empty.

### Non-Applicability and Ambiguity

Absence can define non-applicability. If the absence of a recognition slug already means the cluster does not apply,
do not add redundant `none` choices inside that cluster's structured fields.

Use `none`, `no-*`, or equivalent non-applicability values only when the field itself must still be answered. In
those cases the field must be required, and the non-applicability value must be monitored as mutually exclusive with
affirmative values in the same field.

Do not encode ambiguity as a fake enum when narrative capture is cleaner. Avoid unclear-type choices where
`has-details` or `see-context` provides a better review-state path.

---

## Precedent Taxonomy Mapping Choices

`extended_taxonomies` and `suppressed_taxonomies` use the same controlled taxonomy-term picker.

- `taxonomy` choices come from one allowlist of legal-record taxonomies that precedent may extend or suppress.
- `term` choices are filtered by the selected `taxonomy` in the same repeater row.
- Store both values as slugs for readable ingest, export, and review.
- Validate on save that the selected `term` exists in the selected `taxonomy`.
- Do not allow free-entry taxonomy names or term names in these mapping rows.

---

## Agency Filtering

- `primary_agency` auto-fills with the first attached `ws-agency` post when empty. Choices should be filtered to
  currently attached posts only.
- When `primary_agency` is empty, show this instruction: `"Attach one ws-agency to local or federal first"`.
- When `primary_agency` is non-empty, show this instruction: `"Override primary_agency with any currently attached local or federal agency"`.
- `local_agencies` filters to jx-applicable, non-federal `ws-agency` posts.
- `federal_agencies` filters to federal `ws-agency` posts only.

Future agency filtering may intersect `ws_process_type`, `ws_disclosure_targets`, and `ws_protected_disclosure`
taxonomies.

---

## Relationship Direction Contract

Relationship sync must respect legal-record directionality.

- Parent-bearing legal records: `citation`, `construction`.
- Child-bearing legal records: `statute`, `common_law`.

---

## Hooks To Come

The following hook requirements are known but not yet fully implemented or finalized. Keep this list as the working
backlog for validation, filtering, and editor guidance.

### Contradiction Guards

Document each contradiction guard where the affected field is defined. Note whether the guard requires cross-field
monitoring. If cross-field monitoring also crosses tabs, list the item in the cross-tab section below.

- `protected_classes` and `excluded_classes`: the same class slug must never be present in both taxonomies. When
  overlap is detected, flag for editor resolution; do not auto-remove because the correct side is legal-context
  dependent.
- `garcetti-exception`: invalid unless `public-sector` is present in `employment_sectors`. When `public-sector` is
  absent, remove `garcetti-exception`, clear `garcetti_exception_context`, and clear any sister fields.
- `mitigation-exception`: invalid without `mitigation-required` in `legal_recognitions`. When `mitigation-required`
  is absent, remove `mitigation-exception`, clear `mitigation_exception_context`, and clear any sister fields.
- `all-waivers-blocked`: excludes specific waiver clusters; see Exclusions.
- `jury-trial`: invalid without `private-right-of-action` in `legal_recognitions`. When `private-right-of-action`
  is absent, remove `jury-trial`, clear `jury_trial_context`, and clear any sister fields.
- `exhaustion-required`: invalid when `process_pathway_scope` is `direct-court`. When `direct-court` is set, remove
  `exhaustion-required`, clear `exhaustion_required_context`, and clear any sister fields.
- `direct-filing-permitted`: invalid with `exhaustion-required`. When `direct-filing-permitted` is present in
  `process_types`, remove `exhaustion-required`, clear `exhaustion_required_context`, and clear any sister fields.
- `sovereign-immunity-waiver`: excludes the fuller `sovereign-immunity-status` cluster; see Exclusions.
- Multi-select redirect values: evaluate whether `see-details` must not be combined with specific choices.
- `malicious_reporting_sanctions.sanction_penalty`: `felony` and `misdemeanor` must not appear in the same repeater
  row. Use separate repeater rows for separate criminal tracks.
- `scope`: enforce precedent taxonomy bucket consistency. When `scope` is `favorable`, clear
  `suppressed_taxonomies`; when `scope` is `adverse`, clear `extended_taxonomies`; when `scope` is `neutral`, clear
  both `extended_taxonomies` and `suppressed_taxonomies`; when `scope` is `dual-effect`, allow both.
- `burden_shifting_frameworks`: `mixed-motive` is incompatible with `but-for` in most formulations; multi-framework
  combinations need a validity check on the Burden of Proof tab.
- `election_of_remedies_rules`: `no-election-required` invalidates all other choices in the same field on the
  Retaliation tab.
- `is_employer_only_defendant`: when true, force `proper_defendants` to exactly `employer-entity` and clear every
  other `proper_defendants` value.
- `types` for citation records: citation type choices are likely mutually exclusive and need evaluation on the
  Identity tab.

### Cross-Tab Guards

The following hook guards compare fields that live on different tabs:

- `garcetti-exception` in `legal_recognitions` requires `public-sector` in `employment_sectors`.
- `exhaustion-required` in `legal_recognitions` conflicts with `direct-filing-permitted` in `process_types`.
- `protected_classes` and `excluded_classes` must not contain the same class slug.

### Sister-Block Guards

The following hook guards compare fields inside a single triggered block:

- `fee_shifting` block on the Processes & Remedies tab: monitor for contradictions and invalid combinations.
- `fee_shifting_standard` may make some values in `fee_shifting_scopes` invalid.
- `fee_shifting_scopes` is multi-select and can create invalid combinations.
- When `fee-shifting-standard` is present in `legal_recognitions`, `none-american-rule` can only be set with phased
  exceptions. `fee_shifting_scopes` must be set to `has-phases` only, or `fee-shifting-standard` must be removed
  from `legal_recognitions`.

### Same-Field Multi-Select Guards

The following hook guards protect multi-select fields whose choices include umbrella or fallback values:

- `malicious_reporting_sanctions.sanction_penalty` cannot combine `felony` and `misdemeanor` in the same repeater
  row. Add a second row when the same provision creates separate felony and misdemeanor tracks.

### Mixed-Motive Remedy Notice

When `burden_shifting_frameworks` on the Burden of Proof tab includes `mixed-motive`,
`mixed_motive_remedy_context` on the Processes & Remedies tab becomes relevant. ACF conditional logic cannot surface
this cross-tab dependency natively.

Register an `acf/save_post` hook, or an `admin_notices` hook attached through `current_screen`, that detects
`mixed-motive` in `burden_shifting_frameworks` and emits a dismissible admin notice directing the editor to the
Processes & Remedies tab:

> "Mixed-motive framework selected — please complete the 'Mixed Motive Remedy Context' field on the Processes & Remedies tab."

The notice should be informative, not alarmist, and should display on the edit screen for all four legal-record
CPTs. Dismiss state does not need to persist; the notice should reappear on each save as long as `mixed-motive` is
present and `mixed_motive_remedy_context` is empty.

### Blacklisting Extension Visibility

`is_blacklisting_extended` on the Processes & Remedies tab is conditionally revealed when `adverse_actions` on the
Retaliation tab includes `blacklisting`.

### Potential Invalid Multi-Value Combos

Breadcrumb list only. These multi-select or multi-taxonomy fields may need same-field invalid-combo review later.

Symbol table:

- `[U]` - umbrella-only value vs. granular value it already covers.
- `[X]` - mutually exclusive values.
- `[N]` - required none/no answer vs. affirmative values.
- `[O]` - overloaded field; too many conflict styles to summarize here.
- `[Q]` - candidate retained for later review; no specific conflict class assigned yet.

Parent slugs are not selectable values and are not conflicts by themselves. If children from two parent families
conflict, note it as `[X] children 'parent-x' v. 'parent-y'`.

- `legal_recognitions` - `[O]`
- `protected_action_standards` - `[X]` `per-se-protected`
- `protected_action_source` - `[Q]`
- `protected_actions` - `[Q]`
- `protected_disclosures` - `[Q]`
- `protected_classes` - `[U]` `all-employees-only`
- `excluded_classes` - `[Q]`
- `employment_sectors` - `[U]` `all-sectors-only`
- `disclosure_targets` - `[Q]`
- `adverse_actions` - `[Q]`
- `sol_triggers` - `[Q]`
- `filing_notice_targets` - `[Q]`
- `preservation_requirement_scopes` - `[Q]`
- `malicious_reporting_sanctions.conduct_sanctioned` - `[Q]`
- `malicious_reporting_sanctions.sanction_penalty` - `[X]` `felony`, `misdemeanor`
- `criminal_sanctions.sanction_conduct` - `[Q]`
- `process_types` - `[Q]`
- `fee_shifting_phases.phase_scope` - `[N]` `none`
- `remedies` - `[Q]`
- `preliminary_reinstatement_scopes` - `[U]` `full-pendency-only`
- `burden_shifting_frameworks` - `[X]` `mixed-motive`, `but-for`
- `employer_knowledge_scopes` - `[Q]`
- `employer_defenses` - `[Q]`
- `individual_liability_scopes` - `[U]` `any-individual-only`
- `anti_slapp_protection_scopes` - `[U]` `full-procedural-only`
- `election_of_remedies_rules` - `[N]` `no-election-required`
- `public_policy_sources` - `[Q]`
- `authority_sources` - `[Q]`
- `types` - `[X]`

### Potential Duplicate Cross-Field Concepts

Breadcrumb list only. `[D]` marks a value that may duplicate a concept represented by another field or value.

- `legal_recognitions` - `[D]`
    * `pre-filing-notice` -> `process_types` - `pre-suit-notice`
    * `criminal-sanctions` -> `process_types` - `criminal-referral`
    * `private-right-of-action` -> `process_types` - `civil-lawsuit`
    * `class-action` -> `process_types` - `representative-action`
    * `civil-action-waiver` -> `civil_action_waiver_scope` - waiver-permission values
    * `contractual-waiver` -> `contractual_waiver_scope` - waiver-enforcement values
    * `class-action-waiver` -> `civil_action_waiver_scope` - `permitted-individual-only`
    * `discovery-protection` -> `adverse_actions` - `retaliatory-discovery`
    * `anti-slapp-protection` -> `adverse_actions` - `retaliatory-litigation`
- `process_types` - `[D]`
    * `direct-filing-permitted` -> `process_pathway_scope` - `direct-court`

### Potential Cross-Field Required Values

Breadcrumb list only. `[R]` marks a value that may require a value in another field.

- `legal_recognitions` - `[R]`
    * `public-concern-required` -> `employment_sectors` - `public-sector`
    * `garcetti-exception` -> `employment_sectors` - `public-sector`
    * `jury-trial` -> `legal_recognitions` - `private-right-of-action`
    * `mitigation-exception` -> `legal_recognitions` - `mitigation-required`
    * `preliminary-reinstatement` -> `remedies` - `reinstatement` or `interim-reinstatement`
    * `fee-shifting-standard` -> `remedies` - `attorney-fees` or `attorney-fees-admin`
    * `equitable-interest-award` -> `remedies` - `interest-on-backpay`
    * `internal-only-disclosure` -> `process_types` - `internal-disclosure`
    * `internal-only-disclosure` -> `disclosure_targets` - at least one child of `internal`
- `adverse_actions` - `[R]`
    * `constructive-discharge` -> `sol_triggers` - `constructive-discharge-accrual`
- `remedies` - `[R]`
    * `bounty-qui-tam-award` -> `process_types` - `qui-tam`
    * `bounty-qui-tam-award` -> `protected_classes` - `qui-tam-relator`
    * `liquidated-damages` -> `remedy_liquidated_multiplier` - non-empty
- `remedy_caps.remedy_cap` - `[R]`
    * `punitive` -> `remedies` - `punitive-damages`
    * `compensatory` -> `remedies` - `compensatory-damages`
- `protected_classes` - `[R]`
    * `qui-tam-relator` -> `process_types` - `qui-tam`
- `burden_shifting_frameworks` - `[R]`
    * `mixed-motive` -> `mixed_motive_remedy_context` - non-empty

### Potential Cross-Field Invalid Combos

Breadcrumb list only. `[C]` marks a value that may conflict with a value or absence in another field.

- `legal_recognitions` - `[C]`
    * `exhaustion-required` -> `process_pathway_scope` - `direct-court`
    * `exhaustion-required` -> `process_types` - `direct-filing-permitted`
- `protected_classes` - `[C]`
    * same class slug -> `excluded_classes` - same class slug
- `excluded_classes` - `[C]`
    * same class slug -> `protected_classes` - same class slug
- `fee_shifting_scopes` - `[C]`
    * `has-phases` only requirement -> `legal_recognitions` - `fee-shifting-standard`
- `fee_shifting_phases.phase_scope` - `[C]`
    * `none` -> affirmative phase-scope values
- `election_of_remedies_rules` - `[C]`
    * `no-election-required` -> affirmative election-rule values
- `is_employer_only_defendant` - `[C]`
    * true -> `proper_defendants` - any value other than `employer-entity`
- `process_types` - `[C]`
    * `direct-filing-permitted` -> `legal_recognitions` - `exhaustion-required`

### Potential Required-Empty Cleanup

Breadcrumb list only. `[W]` marks stale hidden values that must be wiped when the controlling field no longer
permits the dependent field.

- `scope` - `[W]`
    * `favorable` -> `suppressed_taxonomies` must be empty
    * `adverse` -> `extended_taxonomies` must be empty
    * `neutral` -> `extended_taxonomies` must be empty
    * `neutral` -> `suppressed_taxonomies` must be empty
    * `dual-effect` -> `extended_taxonomies` and `suppressed_taxonomies` may both carry values

### Potential Cross-Field Exclusions

Breadcrumb list only. `[E]` marks a value that may exclude another field or cluster from existing.

- `legal_recognitions` - `[E]`
    * `all-waivers-blocked` -> excludes `civil-action-waiver` cluster
    * `all-waivers-blocked` -> excludes `contractual-waiver` cluster
    * `all-waivers-blocked` -> excludes `collateral-claims-waiver` cluster
    * `all-waivers-blocked` -> excludes `class-action-waiver` cluster
    * `sovereign-immunity-waiver` -> excludes `sovereign-immunity-status` cluster
    * `internal-only-disclosure` -> excludes multiple external-disclosure paths; review later
    * `bad-faith-exclusion` -> excludes protected-action good-faith posture
    * `statutory-preclusion` -> excludes ordinary process/remedy pathway fields when claim is fully precluded
    * `no-retaliatory-evidence` -> excludes evidence-use fields that treat retaliatory evidence as available
- `process_pathway_scope` - `[E]`
    * `direct-court` -> excludes `exhaustion-required` cluster
- `fee_shifting_standard` - `[E]`
    * `none-american-rule` -> excludes non-phased `fee_shifting_scopes`
- `sovereign_immunity_status` - `[E]`
    * `not-waived` -> excludes `sovereign_immunity_waiver`

---

## Global Hook-Writing Guidance

These rules apply across hook types. They are intentionally implementation-facing but not tied to one final helper
API.

- Normalize values before validating. Treat taxonomy, select, and multi-select values as slug arrays internally,
  even when ACF stores a scalar.
- Prefer deterministic cleanup over silent invalid state. When a controlling field makes another field impossible,
  clear the stale field on save and validate that it remains empty.
- Use validation errors when cleanup would destroy editorial judgment. For example, overlapping
  `protected_classes` and `excluded_classes` should be flagged for editor review rather than auto-removed.
- Use the slug map as the source of truth for triggered-cluster requiredness. `[R]` fields must be non-empty when
  their triggering slug is present.
- A `*_context` field is required only when it is the only surfaced field in its triggered cluster, unless the map
  explicitly marks a structured sister field `[R]`.
- Taxonomy-absence conditionals must evaluate before slug-presence conditionals when both appear in the same
  cluster. Example: `all-waivers-blocked` absent, then `civil-action-waiver` present.
- Umbrella hooks target values ending in `-only`. When a `-only` value is present, remove or reject granular sibling
  values in the same field.
- Sentinels are not granular values. `has-details`, `see-details`, and `see-context` may remain valid alongside an
  umbrella or exclusion value when the field definition allows the companion to carry nuance.
- Required none/no values are valid only in required fields. When present, they must exclude affirmative sibling
  values in the same field.
- Exclusion hooks should clear all fields in the excluded cluster, including context fields, structured sisters,
  details fields, and chained downstream conditionals.
- Required-empty cleanup hooks should clear hidden stale values when a controlling field changes, then validate that
  hidden disallowed fields remain empty.
- Cross-field required hooks should validate both directions that matter. If value A requires value B, the error
  should name both fields and both values.
- Repeater hooks should validate each row independently unless the rule is explicitly cross-row.
- Derived and merged fields should be written by hooks only. Editors should not be asked to maintain hidden derived
  fields manually.
- Prefer one hook per behavior family with a small rules table over many one-off hooks.

### Hook Examples

These examples are schematic. Function names such as `acf_value()`, `set_acf_value()`, `has_slug()`, and
`validation_error()` stand in for final project helpers.

#### Merge Arrays

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

#### Derive A Value

Use this pattern when a visible or hidden field is derived from another field and should stay synchronized.

```php
function ws_derive_effective_year(int $post_id): void {
    $effective_date = acf_value($post_id, 'effective_date');
    $date = acf_value($post_id, 'date');
    $source_date = $effective_date ?: $date;

    set_acf_value($post_id, 'effective_year', $source_date ? substr($source_date, 0, 4) : '');
}
```

#### Block Granular Values When Umbrella Is Present

Use this pattern for `[U]` fields. The hook target is the `-only` value.

```php
function ws_validate_umbrella_only(int $post_id, string $field, string $only_value): void {
    $values = (array) acf_value($post_id, $field);
    $sentinels = ['has-details', 'see-details', 'see-context'];

    if (!in_array($only_value, $values, true)) {
        return;
    }

    $granular = array_diff($values, [$only_value], $sentinels);

    if ($granular) {
        validation_error($field, "$only_value cannot be combined with granular values.");
    }
}
```

#### Enforce Triggered-Cluster Requiredness

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

#### Apply Taxonomy-Absence Exclusion First

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

#### Enforce Required None/No Exclusivity

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

#### Wipe Hidden Stale Values

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

#### Enforce Cross-Field Required Values

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

#### Enforce Exact-Only Boolean Cleanup

Use this pattern for boolean refinements such as `is_employer_only_defendant`.

```php
function ws_apply_employer_only_defendant(int $post_id): void {
    if (!acf_value($post_id, 'is_employer_only_defendant')) {
        return;
    }

    set_acf_value($post_id, 'proper_defendants', ['employer-entity']);
}
```

#### Validate Same-Field Multi-Select Exclusion

Use this pattern for `[X]` pairs in multi-select fields where two specific values are mutually exclusive, such as
`mixed-motive` and `but-for` in `burden_shifting_frameworks`. Use validation_error rather than auto-cleanup so the
editor decides which value to keep.

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

#### Validate Repeater-Row Sub-Field Exclusion

Use this pattern when sub-field values must not co-occur within a single repeater row, such as `felony` and
`misdemeanor` in `malicious_reporting_sanctions.sanction_penalty`. The rule is row-scoped; the same two values
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

#### Apply Cross-Taxonomy AND-Conditional Guard

Use this pattern for sister fields whose validity depends on a triggering slug in `legal_recognitions` AND a
cross-taxonomy condition in another taxonomy, such as `is_cats_paw_liability_extended`. Cleanup (rather than
validation_error) is correct here because the field is structurally meaningless when its prerequisites are absent.

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

`has_any_child_slug($post_id, $taxonomy_field, $parent_slug)` is a project helper that returns true when any term
assigned to the post is a descendant of the named parent slug in the named taxonomy. The helper centralizes
parent/child resolution so callers don't enumerate child slugs by hand and don't break when new children are added
to the seeder.
