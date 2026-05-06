# Legal Record ACF Canonical Field Spec (v2.5)

**Purpose:** Unified, prefix-free field set for all four legal record types
(`statute`, `common_law`, `citation`, `construction`).

**Notes:** Do not update existing files. Rename existing files with `.txt` appended.
Create new files with same names as the originals from scratch.

---

## Naming Rules

### Casing

Casing is strict. ACF field `name` values (meta keys) use `snake_case`. Select choice values and taxonomy term
slugs use `kebab-case`.

### Umbrella Choice Values

Umbrella values in multi-value fields end with `-only`, when the value represents a blanket selection that
excludes granular sibling values in the same field. Do not use `-only` with other values, find alternatives.

Hooks targeting the umbrella`-only` values must flag granular siblings when present. Sentinels such as
`has-details` and `see-context` are not granular values and may coexist with an umbrella value.

Examples: `all-sectors-only`, `all-employees-only`, `full-pendency-only`.

### CPT Prefix and Infix

Canonical field names are prefix-free; CPT-specific prefixes will be applied during ACF rewrite.

Fixed parts: `ws_*` identifies the codebase, `jx_*` identifies legal records that are children of `jurisdiction`
records, and CPT slot values are `statute`, `comlaw`, `citation`, and `construction` — mapping to post types
`jx-statute`, `jx-common-law`, `jx-citation`, and `jx-construction`.

Registered ACF field `name` values use `ws_jx_{cpt}_*`. Registered field `key` values use `field_jx_{cpt}_*` (no
`ws_` codebase indicator). Tab field keys use `field_jx_{cpt}_{tab-label}_tab`. ACF group keys use
`group_jx_{cpt}_metadata`.

Build `tab-label` from the lowercased tab `label`, dropping symbols (`&`, `/`, etc.) and joining adjacent words
with a single underscore. Do not use `_and_`, do not abbreviate, and do not use deprecated abbreviations such as
`sol` for `statute_of_limitations` or `bop` for `burden_of_proof`. Examples: `Statute of Limitations &
Thresholds` → `statute_of_limitations_thresholds_tab`; `Processes & Remedies` → `processes_remedies_tab`.

CPT-specific ACF groups must have `menu_order` below 85; shared workflow groups occupy 85–99.

### Reserved Prefixes

`ws_auto_` is reserved for auto-stamp mechanisms used by workflow ACFs, written only by hook logic.
Never use `ws_auto_` for content fields.

### Cardinality

Field names reflect storage cardinality: singular for single-value, plural for multi-value (multi-selects,
repeaters, arrays). When a suffix declares shape, match the cardinality — `*_scope` vs `*_scopes`, `*_status` vs
`*_statuses`. When a field's base concept changes from plural to companion text, keep the modified-key infix
singular: `disclosure_targets` → `disclosure_target_details` or `disclosure_target_context`.

Exception: `*_details` and `*_context` are lexical labels and do not track cardinality.

### Booleans

Boolean naming is limited to two roles. `has_*` is a trigger boolean — when true, it activates a companion or
dependent field (e.g., `has_effective_date` triggers `effective_date`; `has_field_name` triggers
`field_name_details`). `is_*` and `*_is_*` are state booleans — they do not trigger companions.

Any boolean outside these scopes requires approval and inline documentation.

### Companion Fields

Companion fields are conditional fields whose names usually identify their trigger.

`*_details` is a freetext companion, triggered by either a `has_field_name` boolean or a `has-details` sentinel
in `field_name`. Do not name the boolean `has_field_name_details` when the companion is `field_name_details`.

`*_context` is a freetext companion. Usually used to anchor a cluster triggered by a `legal_recognitions` slug. It
is normally accompanied one or more structured sister fields and explains context for the cluster as a whole.
Any field can conditionally trigger a `*_context` companion provided it is the only field revealed. All
`*_context` conditionals must declare their trigger field and trigger value.

Other companion shapes — `*_limits`, `*_phases`, or any `*_companion` — may be triggered by a `has_*` boolean or
`has-*` sentinel sharing the same base name (e.g., `has_field_name_limits` triggers `field_name_limits`;
`has-phases` in `field_name` triggers `field_name_phases`); and do not require inline annotations of the
conditional. When trigger and companion do not share a base name (e.g. dropped prefix_ or _suffix), document the
conditional inline.

Conditional annotation is optional when the naming convention makes the trigger unambiguous; otherwise it is
required. Conditional `*_context` companion must always document its conditional. See [Conditional Annotation] for
accepted forms.

### Repeater Pluralization — Row Singularization

Repeater fields, by their default nature, signify multiple values will apply. Even if technically untrue and a
repeater field is expressly intended for a single-value (first: you've likely made a mistake; second:) always 
pluralize the repeater fields. Inside repeater fields, avoid multi-value fields unless absolutely required, use
single-value fields only. If multiple values are required, add an additional row for each required value.
Example:
Repeater.
  - Incorrect Form:
    * Row_01 = [(Attribute = Color), (Specifics = Red, Blue)] *is wrong*
  - Correct Form:
    * Row_01 = [(Attribute = Color), (Specific = Red)]  *and*
    * Row_02 = [(Attribute = Color), (Specific = Blue)] *is right*

#### Repeater Context

By convention, the last subfield of a row in a repeater is a freetext `*_context` companion. Even though it is
acknowledged as unnecessary, set the field as "conditional on `first_subfield` is non-empty"; this conforms to
the general rules regarding use of `*_context` companion fields. If for any reason the `first_subfield` could
legitimately be empty, (first: you've likely made a mistake; second:) set as "conditional on `primary_subfield`";
which should never be empty. If for any reason there is legitimately no subfield that will be non-empty in every
row (first: you really have made a mistake; second:) omit the conditional entirely, and annotate the field
inline. Include your `username` and `editor_id` — So that: I can find you —and— educate you about repeaters.

*NOTE:* The following revision is up for debate:
<!-- ### Repeater Pluralization — Row Singularization

Repeater fields, by default, signify that multiple values may apply. Always pluralize repeater field names,
even when a repeater is temporarily expected to hold only one row.

Inside repeater rows, avoid multi-value fields unless absolutely required. Prefer single-value subfields and add
one row per value.

Example:
- Incorrect:
  * Row 01 = [(Attribute = Color), (Specifics = Red, Blue)]
- Correct:
  * Row 01 = [(Attribute = Color), (Specific = Red)]
  * Row 02 = [(Attribute = Color), (Specific = Blue)]

#### Repeater Context

By convention, the final subfield in a repeater row is a freetext `*_context` companion. Set it as conditional on
the row identity field being non-empty, usually the first subfield.

If the first subfield may legitimately be empty, use the primary required subfield instead. If no row subfield is
guaranteed to be non-empty, the repeater is probably mis-modeled; omit the conditional only with an inline
annotation explaining the exception. -->

### Sister Fields

Sister fields inherit a sibling's (usually a `*_context` companion) conditional logic without repeating the
conditional themselves. Name them logically for the data they hold — sisters have no naming convention beyond
casing and cardinality. Annotate each with the inline note "Sister to `sibling_field`"; and append any additional
requirements after using `AND`, `OR`, or `NOT`. Sisters may appear before or after their sibling, with the
freetext sibling typically last in the cluster. Use logical sequencing for editorial flow . A sister cannot appear
without its companion sibling. Conditional clusters must trigger from specified slug in `legal_recognitions`;
document trigger slug and conditional cluster fields in [Slug-to-Companion Map] below. Clusters may chain other
conditional fields — when chained fields become "messy", add inline notes clarity.

Conditional `*_details` and paired `*_unit` fields follow declared rules, and do not require documentation in the
[Slug-to-Companion Map] below.

### Cluster Triggers

Clusters that reveal more than one field beyond `_context` must trigger from `legal_recognitions`. When a
`legal_recognitions` also requires a term from a related taxonomy, add it as a `[P]` 'Prerequisite' for the slug
in the [Slug-to-Companion Map] below. If required slug mutually requires the same slug in `legal_recognitions`, it
is considered `[P+]` "Paired". Add the "Paired" slug and its taxonomy to the [Cross-Field] `[R]` requirements
table in `legal-record-acf-hooks.md`. Hooks must flag for the 'Prerequisite' or 'Paired' terms as appropriate.

Single-field conditionals (`*_context` companion only) may migrate to `legal_recognitions`.
Migration of single-field conditionals is not required.

### Legal Recognition Taxonomy Slugs

Recognition slugs in `ws_legal_recognition` taxonomy and set in `legal_recognitions`, should always be named
relative to the doctrine their presence "recognizes". When the doctrine is clear without the bool-state verb
(e.g. `-recognized`, `-permitted`, `-specified`) suffix, omit the suffix for brevity. Otherwise include the
suffix. Prefer clarity over brevity; don't over-commit to brevity, if later concerns arise due to clarity append
the damn suffix.

### Avoided Names

Discouraged but not forbidden:
- `*_recognized` and other legal-state bools — deemed unnecessary; add as a term to `ws_legal_recognition`
   taxonomy, whenever possible.
- `*_type` — deemed too generic; use a more precise suffix when it fits (prefer `*_class`, `*_scope`, `*_status`,
  `*_rule`, `*_framework`, `*_weight`, `*_strength`, `*_standard`, `*_source`, or other appropriate suffix);
   use of `*_type` when appropriate and no better suffix fits, does not require annotation or approval.
- `*_limitation` — deemed too long; use`*_limit` for brevity.

Pluralize suffixes to match cardinality (e.g. `*_classes`, `*_scopes`, `*_statuses`).
  * No latin-bs where status is both singular and plural;
  * No euro-bs where stati is plural of status;
  * Just english-bs where statuses is both acceptable and appropriate.
The modified-key infix should always be singular (e.g. `protected_actions` → `protected_action_standards` or
`protected_action_sources`).


### Data Shape Suffixes

Use a data-shape suffix only when the field data is the appropriate shape: `*_url`, `*_date`, `*_email`, `*_id`,
`*_value` (integer), `*_unit` (select; (usually) calendar units: `days`, `weeks`, `months`, `years`).

**Duration pair.** When a duration is captured, use a `*_value` + `*_unit`. Where both fields are a required pair,
only `*_value` needs to be annotated; `*_unit` silently inherits all annotations. When both are needed in a
cluster both are `[R]` required by rule; `*_value` does not need a marker; by general rule `*_unit` does not need
to be documented.

---

## Sentinel Rules

Sentinels are reserved select choice keys or taxonomy term slugs with defined system behavior.

**Trigger sentinels** signal that a companion field should appear. `has-details` triggers the relevant
`*_details` companion; prefer it over `other`, `unclear`, or `mixed` when a trigger plus companion captures the
nuance cleanly. New taxonomy-term sentinels must use the `has-*` prefix; in hierarchical taxonomy tables, new
`has-*` terms must nest under `has-parent`.

**Non-standard sentinels** must each be documented. Currently approved:
- `has-limits` in `ws_remedy` triggers `remedy_limits`
- `has-ic-channel` in `ws_protected_disclosure` triggers `ic_channel_sequence_context`.

**Redirect sentinels** — use `see-details` and `see-context` when associated companion field is already triggered.

*NOTE:* Avoid including ambiguous choice values. Always prefer `see-context` (or `has-details` when necessary)
where data may reasonably be 'other', 'unclear', 'mixed' or 'varies'. If the possible data can genuinely be
classified as 'mixed' or 'varies' and not does require further nuance, 'mixed' or 'varies' may be used; 'unclear'
and 'other' are simply unacceptable. Annotate use of begrudgingly-permissible choices with inline comments.

---

## Hook Rules

# New Hook Dogma

Current Thinking is: Hooks should not clear values on conflict: required or exclusion. Hook should block save and
flag errors citing both causal field, and offending field, including specific values where necessary. Validation
error should read as editorial guidance with review required. The editor should then proceed with review of actual
legal-record and correct fields values accordingly, to enable save.

Document required hook behavior inline with the field definition that needs it.

Use hooks for: derived fields that auto-fill on load and on save; merged hidden fields (e.g.,
`_related_agencies`, `_precedent_ids`, `_parent_ids`) that auto-fill on save; derived select choices (e.g.,
`court` filtered by `jurisdiction`) that filter on field load; select, choice, and taxonomy fields needing
anti-contradiction enforcement; conditional clusters where companions and sisters need cross-field
anti-contradiction enforcement.

Prefer unified hooks over duplicates — a single hook branching by `get_post_type()` beats near-identical hooks
per CPT. Reuse hook logic wherever possible. See `legal-record-acf-hooks.md` for details.

**Cluster gate consistency.** When a cluster's authoritative gate lives in `legal_recognitions` and a
contributing sister-taxonomy term implies the same recognition state, register a consistency hook that flags
divergence in either direction. The error must name both fields and both values.

---

## Inline Field Description Rules

### Annotation Discipline

Inline parenthetical annotations exist to clarify the ACF build, not to guide editors. Keep an annotation only
when it does one of the following:
- declares field shape (taxonomy, select choices, repeater structure, derived expression, sister relationship);
- documents conditional logic that the build engine needs (the [Conditional Annotation] forms);
- justifies why the field is necessary, or explaining how it differs from existing fields that cover similar
  concepts.

Drop annotations that read as data-entry guidance for editors (what to type, example values, descriptions of the
underlying legal concept). Editor guidance belongs in ACF instruction text on the field itself, not in this spec.

### Default Field Types

**Default rule:** Any field whose shape is not specified by the conventions below or by an inline definition is a
freetext field. Field shape is never inferred from a name's apparent meaning — only from explicit convention or
inline definition.

These defaults apply by naming convention unless the inline definition says otherwise:
- `has_*`, `is_*`, and `*_is_*` are boolean fields.
- `*_class`, `*_scope`, `*_status`, `*_rule`, `*_framework`, `*_weight`, and `*_standard` are select fields.
- `*_share` describes a specified portion of a reward, e.g., `25-30%`.
- `*_compare` describes a mandated comparison and uses `gte`, `lte`, `gt`, `lt`, or `eq`.
- `*_value` is an integer or number field.
- `*_unit` is a select field — calendar unit unless stated otherwise.
- `*_formula` describes mandated calculations.
- `*_sanctions` describes specified unlawful conduct and associated penalties, usually as a repeater.
- `*_application` describes where or how a legal standard applies and is a select field.
- `*_bar` is used for claim-blocking doctrines or procedural bars and may be select or boolean.
- `select` means single-select unless multi-select is specified.

### Default Taxonomy Field Settings

Taxonomy fields default to: type `taxonomy`, multi-select, `load_terms: 1`, `save_terms: 1`.

### Conditional Annotation

Use only these accepted conditional-annotation forms:
- Taxonomy term present: `conditional on slug in taxonomy_field`
- Taxonomy term absent: `conditional on slug absent in taxonomy_field`
- Any non-empty value: `conditional on trigger_field is non-empty`
- Specific value in select field: `conditional on trigger_field is trigger_value`
- Specific value absent from select field: `conditional on trigger_field is NOT trigger_value`
- Specific value in multi-select field: `conditional on trigger_field includes trigger_value`
- Child taxonomy value present: `conditional on any child-slug of parent-slug in taxonomy_field`
- Boolean true: `conditional on bool_field is true`
- Boolean false: `conditional on bool_field is false`
- Compound values and conditions: combine with all-caps `AND`, `OR`, and `NOT`
- Absent conditionals imply "AND not-empty"; 'absent from select field' form can be used with multi-select.

Annotation is optional for `*_details`, `*_limits`, `*_phases`, and `*_companions` when the naming convention
makes the trigger unambiguous. It is required for all other conditional fields, and `*_context` fields must always
declare their trigger field and trigger value. When using `AND`, `OR`, or `NOT`, omit "conditional on" while using
accepted conditional notation.

Sister fields inherit the cluster gate; their conditional annotation needs only value-specific gates that further
restrict visibility (`is`, `is NOT`, `includes`). A sister never needs `is non-empty` against another field in its
own cluster — that's already guaranteed by the cluster gate.

---

## Attached Workflow Group Rules

Four shared workflow ACF groups attach to all four legal record types alongside the CPT-specific group; they are
defined in `includes/acf/workflow/`. Do not duplicate workflow fields in CPT-specific ACF files.

| Group key | `menu_order` | Tab label | Fields added |
|---|---:|---|---|
| `group_plain_english_metadata` | 85 | Plain-English | `ws_has_plain_english`, `ws_plain_english_wysiwyg`, `ws_plain_english_reviewed`, `ws_auto_plain_english_create_date`, `ws_auto_plain_english_create_author`, `ws_auto_plain_english_last_edited_date`, `ws_auto_plain_english_last_edited_author` |
| `group_auto_stamp_metadata` | 90 | Authorship & Review | `ws_auto_create_date`, `ws_auto_create_author`, `ws_auto_last_edited_date`, `ws_auto_last_edited_author` |
| `group_source_verify_metadata` | 95 | Source & Verification | `ws_auto_source_method`, `ws_auto_source_name`, `ws_auto_verified_by`, `ws_auto_verified_date`, `ws_verification_status`, `ws_needs_review` |
| `group_major_edit_metadata` | 99 | Major Edit | `ws_is_major_edit`, `ws_major_edit_description`, `ws_major_edit_update_type` |

---


## Prompt Schema → ACF Field Mapping

Maps phase-1 reconciler research JSON into the canonical statute ACF model.
Use the JSON key when `legacy_key` is present in `acf-jx-statutes.php`; otherwise use the canonical ACF `name`.

```

JSON key                                    -> ACF field
meta.
    jurisdiction                            -> identity.jurisdiction

records.
    identity. (Identification)
        title                               -> common_name
        official_citation                   -> citation
        statute_url                         -> authority.url

    classifications. (Scope & Priority)
        covered_sectors                     -> employment_sectors
                                                   {public-sector},
                                                   {private-sector},
                                                   {healthcare-worker},
                                                   {government-contractor}
                                                   [2 terms omitted]
        legal_recognitions                  -> legal_recognitions
                                                   {internal-only-disclosure-sufficient},
                                                   {prospective-whistleblower-protection},
                                                   {trade-secret-immunity-available},
                                                   {continuing-violation-doctrine},
                                                   {criminal-sanctions},
                                                   {blanket-sovereign-immunity-waived},
                                                   {qui-tam-action},
                                                   {disclosure-channel-defined},
                                                   {same-decision-defense-standard},
                                                   {constructive-discharge-standard},
                                                   {anti-slapp-protection},
                                                   {catch-all-protection}
                                                   [35 terms omitted]

    statute_of_limitations. (The Timeline (Crucial))
        statute_of_limitations              -> sol_value
        limit_unit                          -> sol_unit
                                                   (days|weeks|months|years)
        limit_trigger                       -> sol_triggers

    burden_of_proof. (The Burden of Proof (Matching Scan))
        employee_standard                  -> employee_standard
                                                   {preponderance},
                                                   {clear-and-convincing}
                                                   [6 terms omitted]
        causation_standard                 -> causation_standard
                                                   {causation-but-for},
                                                   {causation-contributing-factor},
                                                   {causation-motivating-factor},
                                                   {causation-substantial-factor}
                                                   [5 terms omitted]

    process_remedies. (Remediation & Exhaustion)
        available_remedies                  -> remedies
                                                   {back-pay},
                                                   {front-pay},
                                                   {reinstatement},
                                                   {compensatory-damages},
                                                   {punitive-damages},
                                                   {attorney-fees},
                                                   {liquidated-damages},
                                                   {tax-gross-up},
                                                   {injunctive-relief}
                                                   [23 terms omitted]
        exhaustion_required                 -> classifications.legal_recognitions
                                                   {exhaustion-required}
        exhaustion_pathway                  -> statute_of_limitations.exhaustion_required_context

    classifications. (Plain English Summaries)
        protections_summary                 -> identity.general_description
        protected_disclosures               -> protected_disclosures
        protected_activities                -> protected_actions

integrity.
    has_anomalies                           -> ingest-only
    notations                               -> ingest-only
    notation_count                          -> ingest-only

```

---

## Common Fields (Apply To All 4 Legal Record Types)

These normalized canonical fields exist in every legal-record ACF.
Field order reflects logical editorial workflow within each tab.

---

### Identity Tab

Fields ordered: identification → related dates → scope → curated

- `jurisdiction`                   — (single-select taxonomy: `ws_jurisdiction`)
- `official_name`
- `common_name`
- `citation`                       — (statute citation / precedent case / case name; shared slot)
- `date`                           — (enacted / ruling / decision date (shared slot))
- `has_effective_date`             — (only when `effective_date` is specified and differs from `date`)
- `effective_date`
- `effective_year`                 — (derived from `effective_date` if present, `date` if not)
- `retro_date`                     — (Sister to `retro_context`)
- `retro_context`                  — (conditional on `retroactive-date` in `legal_recognitions`)
- `protection_scope`               — (select: `disclosure`|`retaliation`|`both`; internal editorial
                                      classification; replaces former `ws_protection_scope` taxonomy)
- `general_description`            — (brief; reserve full summary for `plain_english_wysiwyg`)
- `has_attach_flag`                — (special-case; approved use of `has_*` bool; triggers `display_order`)
- `display_order`                  — (conditional on `has_attach_flag` is true)
- `last_reviewed_date`             — (must be last in Identity Tab after any insertions)

---

### Classification Tab

Fields ordered: legal_recognitions → activity standard → disclosure →
classes → sectors → targets → recognitions

- `legal_recognitions`             — (taxonomy: `ws_legal_recognition`; replaces all `*_recognized` booleans,
                                      includes other state bools; See [Slug-to-Companion Map] below.)
- `manager_rule_exclusion_context` — (conditional on `manager-rule-exclusion` in `legal_recognitions`)
- `public_concern_required_context`  — (conditional on `public-concern-required` in `legal_recognitions` AND
                                      `public-sector` in `employment_sectors`)
- `bad_faith_exclusion_context`    — (conditional on `bad-faith-exclusion` in `legal_recognitions`)
- `anonymity_protection_context`   — (conditional on `anonymity-protection` in `legal_recognitions`)
- `malicious_reporting_sanctions`  — (Sister to `malicious_reporting_context`; repeater:
      ├── `conduct_sanctioned`           [select: `knowingly-false`|`reckless-disregard`|`bad-faith-motive`|
      │                                   `see-context`],
      ├── `sanction_penalty`             [select: `civil-fine`|`remedy-forfeiture`|`attorney-fee-shift`|`felony`|
      │                                   `misdemeanor`|`see-context`],
      └── `conduct_context`              [conditional on `conduct_sanctioned` is non-empty])
- `malicious_reporting_context`    — (conditional on `malicious-reporting-sanctions` in `legal_recognitions`)
- `protected_action_standards`     — (Sister to `protected_action_context`; multi-select: `per-se-protected`|
                                      `actual-violation`|`reasonable-belief`|`good-faith`)
- `reasonable_belief_scope`        — (Sister to `reasonable_belief_context`; select: `objective-only`|
                                      `subjective-only`|`dual-prong`|`see-context`)
- `reasonable_belief_context`      — (conditional on `protected_action_standards` includes `reasonable-belief`)
- `protected_action_sources`       — (Sister to `protected_action_context`; multi-select: `constitutional`|
                                      `statutory`|`judicial`|`regulatory`|`executive`|`see-context`)
- `protected_actions`              — (Sister to `protected_action_context`; taxonomy: `ws_protected_action`)
- `protected_action_context`       — (conditional on `protected-action` in `legal_recognitions`)
- `protected_disclosures`          — (taxonomy: `ws_protected_disclosure`)
- `protected_classes`              — (taxonomy: `ws_protected_class`)
- `former_employee_context`        — (conditional on `former-employee` in `protected_classes`)
- `protected_class_details`
- `excluded_classes`               — (Sister to `excluded_class_context`; taxonomy: `ws_excluded_class`)
- `excluded_class_context`         — (conditional on `excluded-class` in `legal_recognitions`)
- `excluded_class_details`
- `employment_sectors`             — (taxonomy: `ws_employment_sector`)
- `garcetti_exception_context`     — (conditional on `garcetti-exception` in `legal_recognitions` AND
                                      `public-sector` in `employment_sectors`)
- `disclosure_targets`             — (taxonomy: `ws_disclosure_target`)
- `disclosure_channel_scope`       — (Sister to `disclosure_channel_context`; select: `any-channel`|
                                      `approved-channel-only`|`mandatory-internal-first`|`see-context`)
- `disclosure_format`              — (Sister to `disclosure_channel_context`; select: `written-only`|
                                      `oral-permitted`|`either`|`has-details`)
- `disclosure_format_details`
- `disclosure_channel_context`     — (conditional on `disclosure-channel-defined` in `legal_recognitions`)
- `ic_channel_sequence_context`    — (conditional on `has-ic-channel` in `protected_disclosures`)
- `disclosure_target_details`

---

### Statute of Limitations & Thresholds Tab

Fields ordered: core SOL → modifiers → exhaustion → pathways → thresholds → federal/state interaction

- `sol_value`
- `sol_unit`
- `sol_triggers`                   — (multi-select: `accrual`|`constructive-discharge-accrual`|`discovery-rule`|
                                      `filing-of-complaint`|`conclusion-of-admin-process`|`see-context`)
- `sol_trigger_discovery_context`  — (conditional on `sol_triggers` includes `discovery-rule`)
- `sol_trigger_context`            — (conditional on `sol_triggers` is non-empty)
- `is_sol_suspended_during_admin`
- `has_sol_details`                — (approved use of `has_field_name_details`)
- `sol_details`
- `sop_value`                      — (Sister to `statute_of_repose_context`)
- `sop_unit`
- `is_sop_tolling_available`       — (Sister to `statute_of_repose_context`)
- `statute_of_repose_context`      — (conditional on `statute-of-repose` in `legal_recognitions`)
- `statutory_tolling_context`      — (conditional on `statutory-tolling` in `legal_recognitions`)
- `equitable_tolling_context`      — (conditional on `equitable-tolling` in `legal_recognitions`)
- `cba_preemption_context`         — (conditional on `cba-grievance-preemption` in `legal_recognitions`)
- `amended_claim_context`          — (conditional on `amended-claim` in `legal_recognitions`)
- `exhaustion_required_scope`      — (Sister to `exhaustion_required_context`; select:
                                      `jurisdictional`|`claims-processing`|`waivable`|`see-context`)
- `exhaustion_required_context`    — (conditional on `exhaustion-required` in `legal_recognitions`)
- `filing_notice_required_value`            — (Sister to `filing_notice_required_context`)
- `filing_notice_required_unit`
- `filing_notice_required_targets`          — (Sister to `filing_notice_required_context`; multi-select: `employer`|`agency`|
                                      `attorney-general`|`labor-board`|`see-context`)
- `filing_notice_required_context`          — (conditional on `pre-filing-notice-required` in `legal_recognitions`)
- `employer_threshold_compare`     — (Sister to `employer_threshold_context`; select: `gte`|`lte`|`gt`|`lt`|
                                      `eq`)
- `employer_threshold_value`       — (Sister to `employer_threshold_context`)
- `employer_threshold_model`       — (Sister to `employer_threshold_context`; select: `employees`|`workers`|
                                      `contractors`|`fte`)
- `employer_threshold_context`     — (conditional on `employer-threshold-specified` in `legal_recognitions`)
- `cure_period_value`              — (Sister to `cure_period_context`)
- `cure_period_unit`
- `cure_period_context`            — (conditional on `cure-period-specified` in `legal_recognitions`)
- `federal_state_interaction`      — (select: `express-preemption`|`savings-clause-preserves-state`|
                                      `concurrent-enforcement`|`field-preemption`|`state-exceeds-federal-floor`|
                                      `has-details`)
- `savings_clause_context`         — (conditional on `federal_state_interaction` is
                                      `savings-clause-preserves-state`)
- `federal_state_interaction_context`  — (conditional on `federal_state_interaction` is non-empty)
- `federal_state_interaction_details`

---

### Retaliation Tab

Fields ordered: scope → adverse actions → recognitions → sanctions

- `adverse_action_scope`              — (select: `termination-only`|`material-adverse`|`broad-any-adverse-action`|
                                         `see-context`)
- `adverse_action_scope_context`      — (conditional on `adverse_action_scope` is non-empty)
- `adverse_actions`                   — (taxonomy: `ws_adverse_action`)
- `anticipatory_retaliation_context`  — (conditional on `anticipatory-retaliation` in `adverse_actions`)
- `threatened_retaliation_context`    — (conditional on `threatened-retaliation` in `adverse_actions`)
- `adverse_action_details`
- `is_blacklisting_extended`          — (conditional on `blacklisting` in `adverse_actions`)
- `preservation_deadline_value`       — (Sister to `evidence_preservation_context`)
- `preservation_deadline_unit`
- `preservation_requirement_scopes`    — (Sister to `evidence_preservation_context`; multi-select:
                                         `litigation-hold`|`statutory-hold`|`court-order`|`agency-request`|
                                         `see-context`)
- `evidence_preservation_context`     — (conditional on `evidence-preservation` in `legal_recognitions`)
- `constructive_discharge_standard`   — (Sister to `constructive_discharge_context`; select: `dual-prong`|
                                         `objective-intolerability`|`intent-required`|`see-context`)
- `constructive_discharge_context`    — (conditional on `constructive-discharge-standard` in `legal_recognitions`)
- `is_evidence_collection_protected`
- `cats_paw_liability_context`        — (conditional on `cats-paw-liability` in `legal_recognitions`)
- `is_cats_paw_liability_extended`    — (Sister to `cats_paw_liability_context`; AND conditional on
                                         any child-slug of `associates-of-whistleblower` in `protected_classes`)
- `third_party_retaliation_context`   — (conditional on `third-party-retaliation` in `legal_recognitions` AND
                                         any retaliation-slug in `adverse_actions`)
- `criminal_sanctions`                — (Sister to `criminal_sanctions_context`; repeater:
      ├── `sanction_conduct`                [select: `retaliation`|`disclosure`|`false-report`|`obstruction`|
      │                                      `see-context`],
      ├── `sanction_level`                  [select: `misdemeanor`|`felony`|`see-context`],
      └── `sanction_context`                [conditional on `sanction_conduct` is non-empty])
- `criminal_sanctions_context`        — (conditional on `criminal-sanctions` in `legal_recognitions` AND
                                         `criminal-referral` in `process_types`)

---

### Processes & Remedies Tab

Fields ordered: process → pathway → fee shifting → remedies → reinstatement

- `process_types`                  — (taxonomy: `ws_process_type`)
- `primary_agency`                 — (derived from first attached post_type[`ws-agency`] when empty)
- `local_agencies`                 — (multi-select: post_type[`ws-agency`] filtered by jx)
- `federal_agencies`               — (multi-select: post_type[`ws-agency`] filtered to jx=`us`)
- `process_pathway_scope`          — (Sister to `process_pathway_context`; select: `direct-agency`|
                                      `direct-court`|`either`|`hybrid-right-to-sue-on-inaction`|`see-context`)
- `process_pathway_limit`          — (Sister to `process_pathway_context`; select: `mandatory`|`permitted`|
                                      `conditional`|`unavailable`|`see-context`)
- `is_agency_inaction_trigger`     — (conditional on `process_pathway_scope` is `hybrid-right-to-sue-on-inaction`)
- `process_pathway_context`        — (conditional on `process-pathway` in `legal_recognitions`)
- `enforcement_sequence`           — (freetext; narrative glue tying enforcement agencies, sequence, and any
                                      enforcement requirements together; associated fields include
                                      `process_types`, `primary_agency`, `local_agencies`, `federal_agencies`,
                                      `process_pathway_scope`, and `process_pathway_limit`)
- `private_roa_context`            — (conditional on `private-right-of-action` in `legal_recognitions`)
- `jury_trial_scope`               — (Sister to `jury_trial_context`; select: `all-claims`|`damages`|
                                      `liability`|`see-context`)
- `jury_trial_context`             — (conditional on `private-right-of-action` AND `jury-trial` in
                                      `legal_recognitions`)
- `fee_shifting_standard`          — (Sister to `fee_shifting_standard_context`; select:
                                      `bilateral-loser-pays`|`unilateral-pro-plaintiff`|`none-american-rule`|
                                      `prevailing-defendant-bad-faith`|`see-context`)
- `fee_shifting_scope`             — (Sister to `fee_shifting_standard_context`; select: `mandatory`|
                                      `discretionary`|`asymmetrical`)
- `has_fee_shifting_phases`        — (auto-set true — (conditional on `fee_shifting_standard` is
                                      `none-american-rule`))
- `fee_shifting_phases`            — (repeater:
      ├── `phase`                        [select: `administrative`|`investigative`|`litigation`|`appeal`|
      │                                   `see-context`],
      ├── `phase_standard`               [select: `bilateral-loser-pays`|`unilateral-pro-plaintiff`|
      │                                   `unilateral-pro-defendant`| `prevailing-defendant-bad-faith`|
      │                                   `american-rule`|`see-context`],
      ├── `phase_scope`                  [select: `mandatory`|`discretionary`|`asymmetrical`],
      ├── `phase_asymmetry`              [conditional on `phase_scope` is `asymmetrical`; select: `two-way`|
      │                                   `one-way-plaintiff`|`one-way-defendant-frivolous`|`has-details`],
      ├── `asymmetry_details`            [conditional on `phase_asymmetry` is `has-details`],                   
      └── `phase_context`                [conditional on `phase` is non-empty])
- `fee_shifting_asymmetry`         — (conditional on `fee_shifting_scope` is `asymmetrical`; select: `two-way`|
                                      `one-way-plaintiff`|`one-way-defendant-frivolous`|`has-details`)
- `fee_shifting_asymmetry_details`
- `fee_shifting_standard_context`  — (conditional on `fee-shifting-standard` in `legal_recognitions`)
- `remedies`                       — (taxonomy: `ws_remedy`)
- `remedy_limits`                  — (conditional on `remedies` includes `has-limits`)
- `remedy_caps`                    — (Sister to `remedy_limits`; repeater:
       ├── `remedy_cap`                  [select: `emotional-distress`|`punitive`|`compensatory`|`aggregate`|
       │                                  `employer-size-tiered`|`see-context`],
       ├── `employer_tier`               [conditional on `remedy_cap` is `employer-size-tiered`],
       ├── `cap_amount`,
       ├── `applies_to`                  [select: `single-claim`|`per-plaintiff`|`per-incident`|`aggregate-action`|
       │                                  `see-context`],
       └── `cap_context`                [conditional on `remedy_cap` is non-empty])
- `remedy_details`
- `remedy_liquidated_multiplier`   — (conditional on `liquidated-damages` in `remedies`; select:
                                      `double`|`treble`|`2x-back-pay`|`2x-wages-lost`|`statutory-formula`|
                                      `statutory-daily-fine`|`up-to-double`|`up-to-treble`|`has-details`)
- `remedy_liquidated_formula`      — (conditional on `remedy_liquidated_multiplier` is `statutory-formula`)
- `remedy_liquidated_details`      — (conditional on `remedy_liquidated_multiplier` is `has-details`)
- `mitigation_required_scopes`     — (Sister to `mitigation_required_context`; multi-select: `yes-statutory`|
                                      `yes-common-law`)
- `mitigation_required_context`    — (conditional on `mitigation-required` in `legal_recognitions`)
- `mitigation_exception_context`   — (conditional on `mitigation-required` in `legal_recognitions` AND
                                      `mitigation-exception` in `legal_recognitions`)
- `interest_provision_scope`       — (Sister to `interest_provision_context`; select:
                                      `pre-judgment-statutory`|`post-judgment`|`both`|`discretionary`|
                                      `see-context`)
- `interest_provision_context`     — (conditional on `equitable-interest-award` in `legal_recognitions`)
- `reinstatement_standard`         — (Sister to `preliminary_reinstatement_context`; select: `mandatory`|
                                      `discretionary`|`has-details`)
- `reinstatement_standard_details`
- `preliminary_reinstatement_scope`    — (Sister to `preliminary_reinstatement_context`; select:
                                           `admin-phase`|`full-pendency-only`|`see-context`)
- `preliminary_reinstatement_context`   — (conditional on `preliminary-reinstatement` in `legal_recognitions` AND
                                           `reinstatement` OR `interim-reinstatement` in `remedies`)

---

### Burden Of Proof Tab

Fields ordered: framework → employee standards → causation → employer defenses →
rebuttable presumption → temporal presumption → detail overflow

- `burden_shifting_frameworks`     — (Sister to `burden_shifting_context`; multi-select: `mcdonnell-douglas`|
                                      `motivating-factor`|`but-for`|`mixed-motive`|`has-details`)
- `burden_shifting_details`        — (conditional on `burden_shifting_frameworks` includes `has-details`)
- `mixed_motive_remedy_context`    — (conditional on `burden_shifting_frameworks` includes `mixed-motive`)
- `burden_shifting_context`        — (conditional on `burden-shifting-framework` in `legal_recognitions`)
- `same_decision_standard`         — (Sister to `same_decision_context`; select: `preponderance`|
                                      `clear-and-convincing`|`see-context`)
- `same_decision_context`          — (conditional on `same-decision-defense-standard` in `legal_recognitions`)
- `employee_standard`              — (single-select taxonomy: `ws_employee_standard`)
- `employee_standard_details`
- `has_causal_nexus_statutory_text`  — (conditional on `causation_standard` is non-empty)
- `causal_nexus_statutory_text`    — (captures verbatim statutory text distinct from `causation_standard_context`)
- `causation_standard`             — (single-select taxonomy: `ws_causation_standard`)
- `causation_application`          — (Sister to `causation_standard_context`; select: `liability`|
                                      `damages`|`both`|`see-context`)
- `causation_application_context`  — (conditional on `causation_application` is non-empty)
- `causation_standard_context`     — (conditional on `causation_standard` is non-empty)
- `causation_dual_standard_context`  — (conditional on `causation-dual-standard` in `legal_recognitions` AND
                                        `causation_standard` is non-empty)
- `employer_knowledge_scopes`      — (Sister to `employer_knowledge_context`; multi-select:
                                      `actual-knowledge`|`constructive-knowledge`|`inferred-knowledge`|
                                      `imputed-knowledge`|`has-details`)
- `employer_knowledge_scopes_details`
- `employer_knowledge_context`     — (conditional on `employer-knowledge-required` in `legal_recognitions`)
- `employer_defenses`              — (taxonomy: `ws_employer_defense`)
- `employer_defense_details`
- `has_rebuttable_presumption`
- `rebuttable_presumption_details`
- `presumption_window_value`       — (Sister to `temporal_presumption_context`)
- `presumption_window_unit`
- `presumption_effect`             — (Sister to `temporal_presumption_context`; select: `shifts-burden`|
                                      `creates-inference`|`rebuttable-presumption`|`has-details`)
- `presumption_effect_details`
- `temporal_presumption_context`   — (conditional on `temporal-presumption-recognized` in `legal_recognitions`)
- `temporal_proximity_value`       — (Sister to `temporal_proximity_context`)
- `temporal_proximity_unit`
- `temporal_proximity_context`     — (conditional on `temporal-proximity-sufficient` in `legal_recognitions`)
- `has_bop_details`                — (approved use of `has_field_name_details`)
- `bop_details`

---

### Reward Tab

Fields ordered: rewards → qui tam specifics

- `reward_discretion_standard`     — (Sister to `reward_context`; select: `mandatory`|`discretionary`|
                                      `presumptive`|`formula-based`|`has-details`)
- `reward_discretion_formula`      — (conditional on `reward_discretion_standard` is `formula-based`)
- `reward_discretion_details`      — (conditional on `reward_discretion_standard` is `has-details`)
- `reward_context`                 — (conditional on `reward-available` in `legal_recognitions`)
- `qui_tam_government_share`       — (Sister to `qui_tam_share_context`)
- `qui_tam_relator_share`          — (Sister to `qui_tam_share_context`)
- `qui_tam_reduction_context`      — (Sister to `qui_tam_share_context`)
- `qui_tam_share_context`          — (conditional on `qui-tam-action` in `legal_recognitions`)
- `has_first_to_file_bar`          — (Sister to `qui_tam_share_context`; AND
                                      `bounty-qui-tam-award` in `remedies`)
- `first_to_file_bar_details`
- `has_public_disclosure_bar`      — (Sister to `qui_tam_share_context`; AND
                                      `bounty-qui-tam-award` in `remedies`)
- `public_disclosure_bar_details`

---

### Waiver & Scope Tab

Fields ordered: contractual → recognitions → immunity → defendants.

- `all_waivers_blocked_context`    — (conditional on `all-plaintiff-waivers-void` in `legal_recognitions`)
- `civil_action_waiver_scope`      — (Sister to `civil_action_waiver_context`; select: `prohibited`|
                                      `permitted-individual-only`|`permitted-collective`|`see-context`)
- `civil_action_waiver_context`    — (conditional on `all-plaintiff-waivers-void` absent in `legal_recognitions`
                                      AND `civil-action-waiver` in `legal_recognitions`)
- `contractual_waiver_scope`       — (Sister to `contractual_waiver_context`; select: `void`|
                                      `limited`|`enforceable`|`void-public-policy`|`void-as-to-whistleblowing`|
                                      `enforceable-with-exceptions`|`see-context`)
- `contractual_waiver_context`     — (conditional on `all-plaintiff-waivers-void` absent in `legal_recognitions`
                                      AND `contractual-waiver` in `legal_recognitions`)
- `collateral_claims_waiver_context`  — (conditional on `all-plaintiff-waivers-void` absent in `legal_recognitions`
                                         AND `collateral-claims-waiver` in `legal_recognitions`)
- `class_action_waiver_context`    — (conditional on `all-plaintiff-waivers-void` absent in `legal_recognitions`
                                      AND `class-action-waiver` in `legal_recognitions`)
- `proper_defendant_rules`         — (Sister to `proper_defendants_context`; repeater:
      ├── `defendant_class`              [select: `employer-entity`|`individual-supervisor`|`public-official`|
      │                                   `government-agency`|`contractor`|`successor-employer`|
      │                                   `parent-subsidiary`|`joint-employer`|`staffing-agency`|
      │                                   `labor-organization`|`see-context`],
      ├── `defendant_limit`              [select: `mandatory`|`permitted`|`conditional`|`unavailable`|
      │                                   `exclusive`|`see-context`],                   
      └── `defendant_context`            [conditional on `defendant_class` is non-empty])
- `proper_defendants_context`      — (conditional on `proper-defendants-specified` in `legal_recognitions`)
- `individual_liability_scopes`    — (Sister to `individual_liability_context`; multi-select: `supervisor`|
                                      `coworker`|`officer-director`|`any-individual-only`|`has-details`)
- `individual_liability_details`   — (conditional on `individual_liability_scopes` includes `has-details`)
- `individual_liability_context`   — (conditional on `individual-liability` in `legal_recognitions`)
- `sovereign_immunity_status`      — (Sister to `sovereign_immunity_context`; select: `not-waived`|
                                      `partially-waived`|`fully-waived`|`has-details`)
- `sovereign_immunity_limits`      — (Sister to `sovereign_immunity_context`; multi-select:
                                      `cap-applies`|`conditions-apply`|`tort-claims-act-gate`)
- `sovereign_immunity_limit_context`  — (conditional on `sovereign_immunity_limits` is non-empty)
- `sovereign_immunity_scope`       — (Sister to `sovereign_immunity_context`; select: `all`|
                                      `instrumentalities-included`|`political-subdivisions-included`|
                                      `state-exclusively`|`see-context`)
- `sovereign_immunity_waiver_class`  — (Sister to `sovereign_immunity_context`; AND conditional on
                                        `sovereign_immunity_status` is NOT `not-waived`; select:
                                        `explicit-waiver`|`implied-waiver`)
- `sovereign_immunity_status_details`
- `sovereign_immunity_context`     — (conditional on `sovereign-immunity-status` in `legal_recognitions`)
- `nda_limits_context`             — (conditional on `nda-limitations` in `legal_recognitions`)
- `anti_gag_provision_context`     — (conditional on `anti-gag-provision-present` in `legal_recognitions`)
- `no_retaliatory_evidence_context`  — (conditional on `no-retaliatory-evidence` in `legal_recognitions`)
- `stay_of_discipline_context`     — (conditional on `stay-of-disciplinary-action` in `legal_recognitions`)
- `anti_slapp_protection_scopes`   — (Sister to `anti_slapp_protection_context`; multi-select:
                                      `motion-to-strike`|`discovery-stay`|`fee-shift-on-motion`|
                                      `full-procedural-only`|`see-context`)
- `anti_slapp_protection_context`  — (conditional on `anti-slapp-protection` in `legal_recognitions`)
- `discovery_protection_scopes`    — (Sister to `discovery_protection_context`; multi-select:
                                      `retaliatory-subpoenas`|`abusive-discovery`|`litigation-harassment`|
                                      `see-context`)
- `discovery_protection_context`   — (conditional on `discovery-protection` in `legal_recognitions`; distinct from
                                      anti-SLAPP)
- `settlement_restriction_scope`   — (Sister to `settlement_restriction_context`; select: `amount`|
                                      `facts`|`full-prohibition`|`agency-notification`|`see-context`)
- `settlement_restriction_context` — (conditional on `confidential-settlement-restriction` in `legal_recognitions`)
- `successor_liability_context`    — (conditional on `successor-liability` in `legal_recognitions`)
- `extraterritorial_context`       — (conditional on `extraterritorial-coverage` in `legal_recognitions`)

---

### Relationships Tab

Fields ordered: reference → related legal records

- `ref_materials`                  — (array; post object; `ws-reference`)
- `overruled_by_id`                — (post object; legal-record['post_id'])

---

### Authority Tab

Fields ordered: source url → authority

- `url`
- `url_is_pdf`
- `authority_reference`

---

### Hidden Fields (no tab; prefixed with underscore)

Fields ordered: id → derived

- `_disclosure_target_class`       — (derived from `disclosure_targets`; select: `internal`|`external`|`both`)
- `_primary_agency_is_fed`         — (derived from `primary_agency` jx)
- `_related_agencies`              — (merged array of `local_agencies` and `federal_agencies`)

---

## Specialized Fields By Legal Record Type

---

### Substantive-Record Common Fields (statute + common_law)

Substantive records carry most of the fields defining whistleblower protections.

#### Fields Excluded From Substantive Records

Notable precedent-only fields capture modifications to legal definitions and do not appear on substantive records:
- `court`                 — federal or local court that made the ruling
- `scope`                 — the result of the ruling
- `binding_strength`      — effective strength of the ruling
- `affected_jx`           — affected jurisdictions
- `extended_taxonomies`   — affected taxonomy when ruling is favorable or dual-effect
- `suppressed_taxonomies` — affected taxonomy when ruling is adverse or dual-effect
- `_parent_ids`: `statute_ids`, `comlaw_ids` — legal record or records affected by ruling

#### Substantive Additions

#### Processes & Remedies Tab (insert after `jury_trial_context`)

- `review_standard_scope`          — (Sister to `review_standard_context`; select: `de-novo`|
                                      `substantial-evidence`|`arbitrary-capricious`|`abuse-of-discretion`|
                                      `has-details`)
- `review_standard_scope_details`
- `review_standard_context`        — (conditional on `civil-review-standard` in `legal_recognitions`)

#### Retaliation Tab (insert after `anticipatory_retaliation_context`)
- `election_of_remedies_rules`     — (Sister to `election_of_remedies_context`; multi-select:
                                      `administrative-bars-civil`|`state-bars-federal`|`remedy-exclusivity`|
                                      `first-filed-controls`|`see-context`)
- `election_of_remedies_context`   — (conditional on `remedy-election-required` in `legal_recognitions`)

#### Relationships Tab

- `citation_ids`
- `construction_ids`

#### Hidden Fields

- `_precedent_ids`                 — (merged array of `citation_ids` and `construction_ids`)

---

### Statute-Specific

Statute records have no deltas.
Future statute-only additions, if any, will be documented here.

---

### Common-Law-Specific

#### Identity Tab (insert after `citation`)

- `precedent_common`               — (common name for precedent case held in field `citation`)

#### Classification Tab (insert after `excluded_class_details`)

- `doctrine_basis`                 — (the legal basis for the doctrine; reserve full summary for
                                      `plain_english_wysiwyg`)
- `public_policy_sources`          — (multi-select: `constitution`|`federal-law`|`statute`|`administrative-rule`|
                                      `case-law`|`executive`|`has-details`)
- `policy_source_details`          — (conditional on `public_policy_sources` includes `has-details`)
- `recognition_status`             — (select: `recognized`|`limited`|`uncertain`|`rejected`|`abrogated`|
                                      `has-details`)
- `recognition_status_details`

#### Statute of Limitations & Thresholds Tab (insert after `federal_state_interaction_details`)

- `statutory_preclusion_context`   — (conditional on `statutory-preclusion` in `legal_recognitions`)

#### Burden of Proof Tab (insert after `causal_nexus_statutory_text`)

- `statutory_nexus_context`        — (conditional on `statutory-nexus-controls` in `legal_recognitions`)


---

### Precedent-Record Common Fields (citation + construction)

Precedent records carry most common fields. Some notable exceptions are fields that are definitionally
inapplicable to court decisions:

- `election_of_remedies_rules`  — Legislative/Doctrinal construct; not a court ruling.
- `doctrine_*`, `public_policy_*`, `recognition_*`, etc. — Common-law-specific fields that have no precedent
  equivalent.
- `_precedent_ids`              — Precedent-Records have `_parent_ids`: `statute_ids`, `comlaw_ids` instead.

#### Identity Tab (insert after `citation`)

- `class`                          — (select: `case-law`|`statute`|`regulatory`|`secondary`|`has-details`)
- `class_details`
- `status`                         — (select: `published`|`unpublished`|`memorandum`|`vacated`)
- `binding_strength`               — (select: `binding`|`persuasive`|`mixed`|`distinguished`|`overruled`; approved
                                      use of 'mixed')
- `court`                          — (select; filtered by jx)
- `court_details`
- `court_jx`                       — (Sister to `court_details`; taxonomy: `ws_jurisdiction`)

#### Identity Tab (insert after `effective_year`)

- `mandate_date`                   — (date the appellate court issues its mandate after a ruling becomes final and
                                      operative; distinct from `date`, which captures the decision/ruling date)

#### Classification Tab (insert after `legal_recognitions`)

- `scope`                          — (select: `favorable`|`adverse`|`neutral`|`dual-effect`; `neutral` means no
                                      taxonomy effect; `dual-effect` means the ruling both extends and suppresses
                                      different taxonomy terms)
- `extended_taxonomies`            — (conditional on `scope` is `favorable` OR `scope` is `dual-effect`; repeater:
      ├── `taxonomy`                     [select: taxonomy slug],
      └── `term`                         [select: taxonomy term])
- `suppressed_taxonomies`          — (conditional on `scope` is `adverse` OR `scope` is `dual-effect`; repeater:
      ├── `taxonomy`                     [select: taxonomy slug],
      └── `term`                         [select: taxonomy term])
- `has_affected_jx`                — (derived from `court` `ws_jx_codes`; true when affected jx differs from
                                      precedent `jurisdiction`)
- `affected_jx`                    — (conditional on `has_affected_jx` is true; taxonomy: `ws_jurisdiction`)

##### Eligible Taxonomy Allowlist for `extended_taxonomies` / `suppressed_taxonomies`

The `taxonomy` repeater select choices are restricted to legal-record-attached classificatory taxonomies that a
ruling can meaningfully extend or suppress. The `term` choices are filtered to terms within the selected taxonomy.

Eligible:
- `ws_legal_recognition`
- `ws_protected_disclosure`
- `ws_protected_class`
- `ws_excluded_class`
- `ws_employment_sector`
- `ws_disclosure_target`
- `ws_protected_action`
- `ws_adverse_action`
- `ws_employer_defense`
- `ws_remedy`
- `ws_process_type`
- `ws_employee_standard`
- `ws_causation_standard`

Excluded:
- `ws_jurisdiction`     — geographic, not classificatory.
- `ws_aorg_*`, `ws_language`, `ws_case_stage`, `ws_procedure_type` — not attached to legal-record CPTs.

#### Relationships Tab

- `statute_ids`
- `comlaw_ids`
- `parent_weight`                  — (select: `primary`|`secondary`|`distinguishing-only`)
- `has_negative_treatment`
- `negative_treatment_class`     — (Sister to `negative_treatment_details`; select: `overruled`|
                                    `distinguished`|`limited`|`questioned`|`superseded-by-statute`|
                                    `see-details`)
- `negative_treatment_details`

#### Authority Tab (insert after `authority_reference`)

- `authority_sources`              — (multi-select: `constitutional`|`legislative`|`judicial`|`regulatory`|
                                      `executive`|`has-details`)
- `authority_source_details`
- `review_standard_scope`          — (select: `de-novo`|`substantial-evidence`|`arbitrary-capricious`|
                                      `abuse-of-discretion`|`has-details`)
- `review_standard_scope_details`

#### Hidden Fields

- `_parent_ids`                    — (merged array of `statute_ids` and `comlaw_ids`)
- `_court_is_fed`                  — (derived from `court` `ws_jx_codes`)

---

### Citation-Specific

Citation records have no deltas from the precedent-record common set.
Future citation-only additions, if any, will be documented here.

---

### Construction-Specific

#### Identity Tab

- `is_en_banc`                     — (defaults true; when false, triggers `panel_composition_details`; approved use
                                      of `is_*` bool as trigger)
- `panel_composition_class`        — (Sister to `panel_composition_details`; select: `three-judge`|
                                      `five-judge`|`seven-judge`|`nine-judge`|`expanded-panel`|`judge`|
                                      `see-details`)
- `panel_composition_details`      — (conditional on `is_en_banc` is false; approved use of `is_*` bool as trigger)

---

## Rename Normalization (Current → Canonical)

Only fields that currently violate target naming conventions, are inconsistent
across legal ACFs, or were structurally redesigned during the canonical rewrite.
Fields that are unchanged or new do not appear in this list.

- `fee_shiftings`                            → `fee_shifting_standard`
- `fee_shifting_scopes` (multi-select)       → `fee_shifting_scope`            (select) + `has_fee_shifting_phases` 
- `has_limit_ambiguous`                      → `has_sol_details`
- `limit_details`                            → `sol_details`
- `has_tolling_details`                      →  split into `statutory-tolling` and `equitable-tolling` true when present in `legal_recognitions`
- `tolling_details`                          →  split into `statutory_tolling_context` and `equitable_tolling_context`
- `has_exhaustion_required`                  → `exhaustion-required`            in `legal_recognitions`
- `exhaustion_details`                       → `exhaustion_required_context`
- `exhaustion_is_jurisdictional` (bool)      → `exhaustion_required_scope`     (select)
- `has_employer_threshold`                   → `employer-threshold-specified`  in `legal_recognitions`
- `employer_threshold_details`               → `employer_threshold_context`
- `has_cure_period`                          → `cure-period-specified`         in `legal_recognitions`
- `cure_period_details`                      → `cure_period_context`
- `rebuttable_presumption`                   → `rebuttable_presumption_details`
- `has_statutory_preclusion`                 → `statutory-preclusion`           in `legal_recognitions`
- `doctrine_basis_wysiwyg`                   → `doctrine_basis`                (never was wysiwyg)
- `recognition_status_wysiwyg`               → `recognition_status`            (select) — (never was wysiwyg) + `recognition_status_details` (textarea)
- `other_sources`                            → `policy_source_details`         (uses `has-details` sentinel now)
- `doctrine_name`                            → `official_name`
- `statute_citation` / `precedent_name` / `case_name` / `case_citation`        (shared slot)   → `citation`
- `enacted_date` / `ruling_date` / `decision_date`                             (shared slot)   → `date`
- `statute_url` / `precedent_url` / `citation_url` / `construction_url`        (shared slot)   → `url`
- `statute_url_is_pdf` / `precedent_url_is_pdf` / `citation_url_is_pdf` / `construction_url_is_pdf` (shared slot)   → `url_is_pdf`
- `superseded_by`                            → `overruled_by_id`
- `has_constructive_discharge_recognized`    → `constructive-discharge`         in `adverse_actions`
- `has_anticipatory_retaliation_recognized`  → `anticipatory-retaliation`       in `adverse_actions`
- `continuing_violation_recognized`          → `continuing-violation-doctrine`  in `ws_legal_recognition`
- `equitable_tolling_recognized`             → `equitable-tolling`              in `ws_legal_recognition`
- `has_amended_claim_recognized`             → `amended-claim`                  in `ws_legal_recognition`
- `arbitration_waiver_enforceability`        → `contractual-waiver`             in `legal_recognitions`
- `has_reward`                               → `reward-available`              in `legal_recognitions`
- `reward_details`                           → `reward_context`
- `disclosure_target_type`                   → `_disclosure_target_class`      (derived, hidden)
- `court_name`                               → `court_details`                 (uses `has-details` sentinel now)
- `is_favorable` (bool)                      → `scope`                         (select)
- `adverse_action_scope` (textarea)          → `adverse_action_scope`          (select) + `adverse_action_scope_context` (freetext)
- `doctrine_id`                              →  removed (visible dedupe IDs deemed unnecessary)
- `bop_flag`                                 →  removed (used by researchers only, never meant for ACF meta)
- `statute_id` + `comlaw_id` — singular      →  pluralized to support (rare-but-possible) multi-values
- `disclosure_types`                         → `protected_disclosures`
- `ws_disclosure_type` (taxonomy)            → `ws_protected_disclosure`       (taxonomy)
- `sol_trigger_event`                        →  removed (collapsed in to unified `sol_trigger`; `sol_trigger_context` now must describe legal, factual, and contextual per trigger)
- `has_preemption` + `preemption_details`    →  removed (preemption block replaced with `federal_state_interaction` block)
- `preemption_direction`                     → `federal_state_interaction`
- `sovereign_immunity_statuses`              → `sovereign_immunity_status`     (select)
- `proper_defendants`                        → `proper_defendant_rules.defendant_class`
- `is_employer_only_defendant`               → `proper_defendant_rules.defendant_limit` = `exclusive`
- `proper_defendant_details`                 → `proper_defendants_context`
- `joint_employer_context`                   → `proper_defendant_rules.defendant_context`
- `threshold_compare`                        → `employer_threshold_compare`
- `threshold_value`                          → `employer_threshold_value`
- `threshold_unit`                           → `employer_threshold_model`
- `public_concern_context`                   → `public_concern_required_context`
- `statutory-nexus-diverges-from-common-law` → `statutory-nexus-controls`
- `statutory-nexus-diverges`                 → `statutory-nexus-controls`

---

## Slug-to-Companion Map (ws_legal_recognition taxonomy)

Used for bool-state values of Legal Recognitions where true when:
 - Specified   — statute explicitly names or enumerates something
 - Recognized  — judicial doctrine courts have affirmatively acknowledged
 - Required    — mandatory obligation; non-compliance typically defeats the claim
 - Applies     — statutory condition that operates by force of law when triggered
 - Available   — mechanism or remedy that may be invoked but is not automatic
 - Permitted   — right expressly allowed; cannot be waived or procedurally blocked
 - Barred      — doctrine, action, or evidence explicitly excluded by law or rule
 - Prohibited  — conduct expressly forbidden; violation triggers statutory liability
 - Present     — clause or provision exists without implying judicial affirmation
 - Sufficient  — condition independently meets the threshold for protection to attach
 - Limited     — legal effect, scope, or enforceability is narrowed by statute or rule
 - Enforceable — waiver, agreement, or procedural limitation may be given legal effect
 - Void        — legal effect, scope, or enforceability is no longer relevant
 - Waived      — immunity, defense, or objection has been relinquished or abrogated

Conditional companion fields (preferably) `*_context` and noted with ` → ` are revealed when the corresponding
slug is present in `legal_recognitions`. Sister fields noted with ` + ` silently inherit the conditional behavior
from the triggered sibling and are only revealed with the triggered sibling. Additional sister field requirements
may exist before they are revealed. These must be documented after the sibling is declared with `AND`, `OR`, or
`NOT`. Sister fields may (an usually do) appear before their sibling. The (normally) freetext sibling is usually
last. There is no rule for the order of cluster fields. Use logical ordering for best editorial workflow.

Within triggered, companion and sister fields are required when logically necessary. Generated ACF should set
`'required' => 1` where applicable. Hooks should block save and flag when a triggering slug is present and any
required field in the cluster is empty; a validation error should identify both the triggering slug and the empty
field or fields, for editorial review.

Annotation Legend:
- `[R]`  — Required field for the cluster.
- `[+]`  — Conditional state marker. Field exists in the cluster but is revealed only when further conditions are
           met. Inline definitions document the additional conditions. If also marked with `[R]`, field is
           required once revealed.
`*_details` fields in clusters follow general rules and are not listed.
`*_unit` fields paired to sister `*_value` fields in clusters follow general rules and are not listed.

Marked slugs have exclusions documented in the [Cross-Field]`[E]` Exclusions table in `legal-record-acf-hooks.md`.
- `[E]`  — Slug has cross-field exclusions.

Marker Payloads:
When a marker is present, condition applies to listed slugs. If cross-taxonomy, the specific taxonomy is stated.
When also conditional on non-taxonomy fields, or specific field values see [Cross-Field] `[E]` Exclusions table or
`[R]` Requirements table in `legal-record-acf-hooks.md`.
- `[E+]` — Slug excludes other slugs.
- `[E-]` — Slug is excluded by other slugs.
- `[P]`  — 'Prerequisite' slug required.
- `[P+]` — 'Paired' slug mutually-required.

Related taxonomy and specific slug when `[P+]` 'Paired' are also documented in `[R]` requirements table .

```

// ── Identity Tab ─────────────────────────────────────────────────────────────────────────────────────
Specified:    'retroactive-date'                                      → 'retro_context'                         + 'retro_date'[R]

// ── Classification Tab ───────────────────────────────────────────────────────────────────────────────
Applies:      'manager-rule-exclusion'                                → 'manager_rule_exclusion_context'[R]
Required:     'public-concern-required'[P]                            → 'public_concern_required_context'[R]
                 * 'public-sector' in 'employment_sectors'
Applies:      'bad-faith-exclusion'                                   → 'bad_faith_exclusion_context'[R]
Specified:    'malicious-reporting-sanctions'                         → 'malicious_reporting_context'           + 'malicious_reporting_sanctions'[R]
Available:    'anonymity-protection'                                  → 'anonymity_protection_context'[R]
Specified:    'protected-action'                                      → 'protected_action_context'              + 'protected_actions'[R]                + 'protected_action_standards'
                                                                                                                + 'protected_action_sources'
                                                                                                                + 'reasonable_belief_context'[+][R]     + 'reasonable_belief_scope'
Specified:    'excluded-class'                                        → 'excluded_class_context'                + 'excluded_classes'[R]
Applies:      'garcetti-exception'[P]                                 → 'garcetti_exception_context'[R]
                 * 'public-sector' in 'employment_sectors'
Specified:    'disclosure-channel-defined'                            → 'disclosure_channel_context'            + 'disclosure_channel_scope'[R]         + 'disclosure_format'

// ── Statute of Limitations & Thresholds Tab ──────────────────────────────────────────────────────────
Specified:    'statute-of-repose'                                     → 'statute_of_repose_context'[R]          + 'sop_value' + 'is_sop_tolling_available'
Specified:    'statutory-tolling'                                     → 'statutory_tolling_context'[R]
Available:    'equitable-tolling'                                     → 'equitable_tolling_context'[R]
Applies:      'cba-grievance-preemption'                              → 'cba_preemption_context'[R]
Available:    'amended-claim'                                         → 'amended_claim_context'[R]
Required:     'exhaustion-required'                                   → 'exhaustion_required_context'           + 'exhaustion_required_scope'[R]
Required:     'pre-filing-notice-required'[P]                         → 'filing_notice_required_context'        + 'filing_notice_required_targets'[R]   + 'filing_notice_required_value'
                 * 'pre-filing-notice-process' in 'process_types'
Specified:    'employer-threshold-specified'                          → 'employer_threshold_context'            + 'employer_threshold_compare'[R]       + 'employer_threshold_value'[R]
                                                                                                                + 'employer_threshold_model'[R]
Specified:    'cure-period-specified'                                 → 'cure_period_context'                   + 'cure_period_value'[R]

// ── Statute of Limitations & Thresholds Tab (Common Law Records Only) ────────────────────────────────
Applies:      'statutory-preclusion'[E]                               → 'statutory_preclusion_context'[R]

// ── Retaliation Tab ──────────────────────────────────────────────────────────────────────────────────
Required:     'evidence-preservation'                                 → 'evidence_preservation_context'         + 'preservation_requirement_scopes'[R]  + 'preservation_deadline_value'
Specified:    'constructive-discharge-standard'[P]                    → 'constructive_discharge_context'        + 'constructive_discharge_standard'[R]
                 * 'constructive-discharge' in 'adverse_actions'
Recognized:   'cats-paw-liability'                                    → 'cats_paw_liability_context'[R]         + 'is_cats_paw_liability_extended'[+]
Prohibited:   'third-party-retaliation'[P]                            → 'third_party_retaliation_context'[R]
                 * any retaliation-slug in 'adverse_actions'
Specified:    'criminal-sanctions'[P]                                 → 'criminal_sanctions_context'            + 'criminal_sanctions'[R]
                 * 'criminal-referral' in 'process_types'

// ── Retaliation Tab (Substantive Records Only) ───────────────────────────────────────────────────────
Recognized:   'remedy-election-required'                              → 'election_of_remedies_context'          + 'election_of_remedies_rules'[R]

// ── Processes & Remedies Tab ─────────────────────────────────────────────────────────────────────────
Specified:    'process-pathway'                                       → 'process_pathway_context'               + 'process_pathway_scope'[R]            + 'process_pathway_limit'
Available:    'private-right-of-action'[P+]                           → 'private_roa_context'[R]
                 * 'civil-lawsuit' in 'process_types'
Available:    'jury-trial'[P]                                         → 'jury_trial_context'                    + 'jury_trial_scope'[R]
                 * 'private-right-of-action'
Specified:    'fee-shifting-standard'[P]                              → 'fee_shifting_standard_context'         + 'fee_shifting_standard'[R]            + 'fee_shifting_scope'[R]
                 * 'attorney-fees' OR 'attorney-fees-admin' in 'remedies'                                       + 'fee_shifting_phases'[+][R]           + 'has_fee_shifting_phases'
Available:    'equitable-interest-award'[P]                           → 'interest_provision_context'            + 'interest_provision_scope'[R]
                 * 'interest-on-backpay' in 'remedies'
Required:     'mitigation-required'                                   → 'mitigation_required_context'           + 'mitigation_required_scopes'[R]
Available:    'mitigation-exception'[P]                               → 'mitigation_exception_context'[R]
                 * 'mitigation-required'
Available:    'preliminary-reinstatement'[P]                          → 'preliminary_reinstatement_context'     + 'reinstatement_standard'[R]           + 'preliminary_reinstatement_scope'[R]
                 * 'reinstatement' OR 'interim-reinstatement' in 'remedies' 

// ── Processes & Remedies Tab (Substantive Records Only) ──────────────────────────────────────────────
Specified:    'civil-review-standard'                                 → 'review_standard_context'               + 'review_standard_scope'[R]

// ── Burden of Proof Tab ──────────────────────────────────────────────────────────────────────────────
Specified:    'burden-shifting-framework'                             → 'burden_shifting_context'               + 'burden_shifting_frameworks'[R]
Specified:    'same-decision-defense-standard'[P]                     → 'same_decision_context'                 + 'same_decision_standard'[R]
                 * 'same-decision-defense' in 'employer_defenses'
Applies:      'causation-dual-standard'[P]                            → 'causation_dual_standard_context'[R]
                 *  non-empty in 'causation_standard'
Required:     'employer-knowledge-required'                           → 'employer_knowledge_context'            + 'employer_knowledge_scopes'[R]
Recognized:   'temporal-presumption-recognized'                       → 'temporal_presumption_context'          + 'presumption_window_value'[R]         + 'presumption_effect'[R]
Sufficient:   'temporal-proximity-sufficient'                         → 'temporal_proximity_context'            + 'temporal_proximity_value'

// ── Burden of Proof Tab (Common Law Records Only) ────────────────────────────────────────────────────
Applies:      'statutory-nexus-controls'                              → 'statutory_nexus_context'[R]

// ── Rewards Tab ──────────────────────────────────────────────────────────────────────────────────────
Available:    'reward-available'                                      → 'reward_context'                        + 'reward_discretion_standard'[R]
Available:    'qui-tam-action'[P+]                                    → 'qui_tam_share_context'[R]              + 'qui_tam_government_share'            + 'qui_tam_relator_share'
                 * 'qui-tam-process' in 'process_types'                                                         + 'qui_tam_reduction_context'
                 * 'bounty-qui-tam-award' in 'remedies'                                                         + 'has_first_to_file_bar'               + 'has_public_disclosure_bar'

// ── Waiver & Scope Tab ───────────────────────────────────────────────────────────────────────────────
Void:         'all-plaintiff-waivers-void'[E+]                        → 'all_waivers_blocked_context'[R]
                 * 'civil-action-waiver'
                 * 'contractual-waiver'
                 * 'collateral-claims-waiver'
                 * 'class-action-waiver'
Enforceable:  'civil-action-waiver'[E-]                               → 'civil_action_waiver_context'           + 'civil_action_waiver_scope'[R]
                 * 'all-plaintiff-waivers-void'
Enforceable:  'contractual-waiver'[E-]                                → 'contractual_waiver_context'            + 'contractual_waiver_scope'[R]
                 * 'all-plaintiff-waivers-void'
Enforceable:  'collateral-claims-waiver'[E-]                          → 'collateral_claims_waiver_context'[R]
                 * 'all-plaintiff-waivers-void'
Enforceable:  'class-action-waiver'[E-]                               → 'class_action_waiver_context'[R]
                 * 'all-plaintiff-waivers-void'
                 * 'class-action-permitted'
Specified:    'sovereign-immunity-status'[E-]                         → 'sovereign_immunity_context'            + 'sovereign_immunity_status'[R]        + 'sovereign_immunity_limits'
                 * 'blanket-sovereign-immunity-waived'                                                          + 'sovereign_immunity_scope'            + 'sovereign_immunity_waiver_class'[+][R]
Specified:    'proper-defendants-specified'                           → 'proper_defendants_context'             + 'proper_defendant_rules'[R]
Limited:      'nda-limitations'                                       → 'nda_limits_context'[R]
Present:      'anti-gag-provision-present'                            → 'anti_gag_provision_context'[R]
Barred:       'no-retaliatory-evidence'[E]                            → 'no_retaliatory_evidence_context'[R]
Available:    'stay-of-disciplinary-action'                           → 'stay_of_discipline_context'[R]
Available:    'anti-slapp-protection'                                 → 'anti_slapp_protection_context'         + 'anti_slapp_protection_scopes'[R]
Available:    'discovery-protection'[P]                               → 'discovery_protection_context'[R]
                 * 'retaliatory-discovery' in 'adverse_actions' 
Limited:      'confidential-settlement-restriction'                   → 'settlement_restriction_context'        + 'settlement_restriction_scope'[R]
Available:    'individual-liability'                                  → 'individual_liability_context'          + 'individual_liability_scopes'[R]
Recognized:   'successor-liability'                                   → 'successor_liability_context'[R]
Applies:      'extraterritorial-coverage'                             → 'extraterritorial_context'[R]

// ── Without Context (no tab) ─────────────────────────────────────────────────────────────────────────
Present:      'catch-all-protection'                                  — (no companion needed)
Sufficient:   'internal-only-disclosure-sufficient'[P]                — (no companion needed)
                 * 'internal-disclosure' in 'process_types'
                 *  any child-slug of 'internal' in 'disclosure_targets'
Available:    'trade-secret-immunity-available'                       — (no companion needed)
Recognized:   'continuing-violation-doctrine'                         — (no companion needed)
Recognized:   'prospective-whistleblower-protection'                  — (no companion needed)
Waived:       'blanket-sovereign-immunity-waived'[E+]                 — (no companion needed)
                 * 'sovereign-immunity-status'
Permitted:    'class-action-permitted'[E+]                            — (no companion needed)
                 * 'class-action-waiver'
Recognized:   'official-duties-carveout'                              — (no companion needed)

```

---

## Notes

The ACFs for legal records are designed to capture all nuance for the current record. Much of this data will
never surface outside the summary of the legal record in `plain_english_wysiwyg`. The data primarily serves as both
requirement and guidance for the editor when crafting the summary. Enforcement of data integrity by hook, ensures
the editor is not working from incomplete or contradictory data.

This spec uses the statute field set as the baseline for broad legal-record parity, then adds per-type deltas
where a record type requires different structure. `ws_legal_recognition` is a presence/absence signal table, not a
classification table. Slugs marked `(no companion needed)` in the [Slug-to-Companion Map] are captured only through
the `ws_legal_recognition` taxonomy; no separate ACF field is registered for those slugs.

All conditional logic must use the accepted annotation forms listed in the [Conditional Annotation] section. Sister
fields inherit conditionals from their triggered sibling. Annotations for sister fields declare the sibling, they
do not repeat the sibling's conditionals.

This spec has zero live-data implications. It is the structural source of truth for PHP field generation and
ingest schema mapping.

### Ingest Pipeline Philosophy

The legal-record field set is the *destination* schema, not the *acquisition* schema. Researchers — including LLM
research assistants — are not expected to find or report data in ACF-field shape. The pipeline is staged so that
each layer extracts what it can model cleanly and passes the rest forward as narrative for the next layer:

1. **Researcher (LLM or human).** Reports findings in legal terms, not field terms. Where possible findings should
   map cleanly to a field. Otherwise findings ride forward as a breadcrumb in freetext, or `*_context` companions
   for downstream review.
2. **Reconciler.** May update findings to map to fields — disambiguating cross-jurisdictional terminology,
   normalizing values, splitting compound findings, resolving freetext into structured equivalents when possible.
   Remaining findings stay as breadcrumbs for human review.
3. **Human reviewer.** Final resolution. Validates field-mapped values, reviews un-mapped findings, and
   researches remaining required fields. The final summary is then written to `plain_english_wysiwyg`.

This staging is the operational form of the omission-over-fabrication rule: each layer maps what it can and
avoids invention. Freetext fields and `*_context` companions are not failure modes — they are the designed
channels through which breadcrumbs survive between stages without being lost.
