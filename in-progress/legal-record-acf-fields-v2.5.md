# Legal Record ACF Canonical Field Spec (v2.5)

**Purpose:** Unified, prefix-free field set for all four legal record types
(`statute`, `common_law`, `citation`, `construction`) as the working spec for the
next ingest/render rewrite cycle.

**Notes:** Do not update existing files. Rename existing files with `.txt` appended.
Create new files with same names as the originals.

---

## Naming Rules

### Casing

Casing is strict. ACF field `name` values (meta keys) use `snake_case`. Select choice values and taxonomy term
slugs use `kebab-case`.

### Umbrella Choice Values

Umbrella values in multi-select fields and selectable multi-taxonomy fields end with `-only`, but only when the
value represents a blanket selection that excludes granular sibling values in the same field. Do not add `-only`
to ordinary granular values.

Hooks targeting the `-only` value must clear or reject granular siblings. Sentinels such as `has-details` and
`see-context` are not granular values and may coexist with an umbrella value when the field's companion logic
permits.

Examples: `all-sectors-only`, `all-employees-only`, `full-pendency-only`.

### CPT Prefix and Infix

Canonical field names are prefix-free; CPT-specific prefixes are applied during registration.

Fixed parts: `ws_*` identifies the codebase, `jx_*` identifies legal records that are children of `jurisdiction`
records, and CPT slot values are `statute`, `comlaw`, `citation`, and `construction` — mapping to post types
`jx-statute`, `jx-common-law`, `jx-citation`, and `jx-construction`.

Registered ACF field `name` values use `ws_jx_{cpt}_*`. Registered field `key` values use `field_jx_{cpt}_*` (no
`ws_` codebase indicator on keys). Tab field keys use `field_jx_{cpt}_{tab-label}_tab`. ACF group keys use
`group_jx_{cpt}_metadata`.

Build `tab-label` from the lowercased tab `label`, dropping symbols (`&`, `/`, etc.) and joining adjacent words
with a single underscore. Do not use `_and_`, do not abbreviate, and do not use deprecated abbreviations such as
`sol` for `statute_of_limitations` or `bop` for `burden_of_proof`. Examples: `Statute of Limitations &
Thresholds` → `statute_of_limitations_thresholds_tab`; `Processes & Remedies` → `processes_remedies_tab`.

CPT-specific ACF groups must have `menu_order` below 85; shared workflow groups occupy 85–99.

### Reserved Prefixes

`ws_auto_` is reserved for auto-stamp mechanisms used by workflow ACFs, written only by hook logic. Never use
`ws_auto_` for content fields.

### Cardinality

Field names reflect storage cardinality: singular for single-value, plural for multi-value (multi-selects,
repeaters, arrays). When a suffix declares shape, match the cardinality — `*_scope` vs `*_scopes`, `*_status` vs
`*_statuses`. When a field's base concept changes from plural to companion text, keep the modified-key infix
singular: `disclosure_targets` → `disclosure_target_details` or `disclosure_target_context`.

Exception: `*_details` and `*_context` are lexical labels and do not track cardinality.

### Booleans

Boolean naming is limited to two roles. `has_*` is a trigger boolean — when true, it activates a companion or
dependent field (e.g., `has_effective_date` triggers `effective_date`; `has_field_name` triggers
`field_name_details`). `is_*` and `*_is_*` are state booleans — they describe a condition without implying a
companion.

Any boolean outside these scopes requires approval and inline documentation.

### Companion Fields

Companion fields are conditional fields whose names usually identify their trigger.

`*_details` is a freetext companion, triggered by either a `has_field_name` boolean or a `has-details` sentinel
in `field_name`. Do not name the boolean `has_field_name_details` when the companion is `field_name_details`.

`*_context` is a freetext companion explaining context for a selected value, taxonomy term, or non-empty field.
Triggered by either a specific value in a trigger field, or any non-empty value. All `*_context` conditionals
must declare both their trigger field and trigger value.

Other companion shapes — `*_limits`, `*_phases`, or any `*_companion` — may be triggered by a `has_*` boolean or
`has-*` sentinel sharing the same base name (e.g., `has_field_name_limits` triggers `field_name_limits`;
`has-phases` in `field_name` triggers `field_name_phases`). When trigger and companion do not share a base name,
document the conditional inline.

Conditional annotation is optional when the naming convention makes the trigger unambiguous; otherwise it is
required. See [Conditional Annotation] for accepted forms.

### Sister Fields

Sister fields inherit a companion's conditional logic without being conditional themselves. Name them logically
for the data they hold — no naming convention beyond casing and cardinality. Annotate each with the inline note
`Sister field to \`sibling_field\`;` and append any extra requirements after using `AND`, `OR`, or `NOT`; do not
repeat the sibling's conditional. Sisters may appear before or after their sibling, with freetext siblings
typically last but final order following editorial logic. A sister cannot appear without its companion sibling in
the same cluster. Sister clusters may chain — when chained conditions become hard to read, add inline notes.

### Avoided Names

Discouraged but not forbidden: `*_recognized` when a `ws_legal_recognition` taxonomy term fits the state;
`*_type` when a more precise suffix fits (prefer `*_class`, `*_scope`, `*_status`, `*_rule`, `*_framework`,
`*_weight`, or `*_standard`); `*_limitations` (prefer `*_limits`). Pluralize the suffix to match cardinality. Use
`*_type` only when no better suffix fits.

### Data Shape Suffixes

Use a data-shape suffix only when the field stores that shape: `*_url`, `*_date`, `*_email`, `*_value` (integer
or number), `*_unit` (unit-select; calendar units are `days`, `weeks`, `months`, `years`).

---

## Sentinel Rules

Sentinels are reserved select choice keys or taxonomy term slugs with defined system behavior.

**Trigger sentinels** signal that a companion field should appear. `has-details` triggers the relevant
`*_details` companion; prefer it over `other`, `unclear`, or `mixed` when a trigger plus companion captures the
nuance better. New taxonomy-term triggers must use the `has-*` prefix; in hierarchical taxonomy tables, new
`has-*` terms must nest under `has-parent`.

**Non-standard sentinels** must each be documented. Currently approved: `has-limits` in `ws_remedy` triggers
`remedy_limits`; `has-channel` in `ws_protected_disclosure` triggers `disclosure_channel_context`;
`has-ic-channel` in `ws_protected_disclosure` triggers `ic_channel_sequence_context`.

**Redirect sentinels** mark companion fields already active through another trigger: `see-details` (`*_details`
companion already active) and `see-context` (`*_context` companion already active).

---

## Hook Rules

Document required hook behavior inline with the field definition that needs it.

Use hooks for: derived fields that auto-fill on load and on save; merged hidden fields (e.g.,
`_related_agencies`, `_precedent_ids`, `_parent_ids`) that auto-fill on save; derived select choices (e.g.,
`court` filtered by `jurisdiction`) that filter on field load; select, choice, and taxonomy fields needing
anti-contradiction enforcement; conditional clusters where companions and sisters need cross-field
anti-contradiction enforcement.

Prefer unified hooks over duplicates — a single hook branching by `get_post_type()` beats near-identical hooks
per CPT. Reuse hook logic wherever possible. See `legal-record-acf-hooks.md` for details.

---

## Inline Field Description Rules

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
- `*_direction` describes directional legal operation (e.g., federal preemption of state law) and is a select
  field.
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
- Compound conditions: combine with all-caps `AND`, `OR`, and `NOT`

Annotation is optional for `*_details`, `*_limits`, `*_phases`, and `*_companions` when the naming convention
makes the trigger unambiguous. It is required for all other conditional fields, and `*_context` fields must
always declare their trigger field and trigger value. When using `AND` or `OR`, repeat the full condition on both
sides rather than relying on implied trigger fields.

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
                                                   {internal-only-disclosure},
                                                   {prospective-whistleblower-protection},
                                                   {trade-secret-immunity},
                                                   {continuing-violation-doctrine},
                                                   {criminal-sanctions},
                                                   {blanket-sovereign-immunity-waiver},
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

- `jurisdiction`                   — (single-select taxonomy: `WS_JURISDICTION_TAXONOMY`)
- `official_name`
- `common_name`
- `citation`                       — (statute citation / precedent case / case name; shared slot)
- `date`                           — (enacted / ruling / decision date (shared slot))
- `has_effective_date`             — (only when `effective_date` is specified and differs from `date`)
- `effective_date`
- `effective_year`                 — (derived from `effective_date` if present, `date` if not)
- `retro_date`                     — (sister field to `retro_context`)
- `retro_context`                  — (conditional on `retroactive-date` in `legal_recognitions`)
- `protection_scope`               — (single-select taxonomy: `ws_protection_scope`; internal use only)
- `general_description`            — (brief; reserve full summary for `plain_english_wysiwyg`)
- `has_attach_flag`                — (special-case; approved use of `has_*` bool; triggers `display_order`)
- `display_order`                  — (conditional on `has_attach_flag` is true)
- `last_reviewed_date`             — (manually updated when record reviewed for accuracy; must be last in Identity
                                      Tab after any insertions)

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
- `malicious_reporting_sanctions`  — (sister field to `malicious_reporting_context`; repeater:
      ├── `conduct_sanctioned`           [multi-select: `knowingly-false`|`reckless-disregard`|`bad-faith-motive`|
      │                                   `see-context`],
      ├── `sanction_penalty`             [multi-select: `civil-fine`|`remedy-forfeiture`|`attorney-fee-shift`|
      │                                    `felony`|`misdemeanor`|`see-context`],
      └── `conduct_context`              [conditional on `conduct_sanctioned` is non-empty])
- `malicious_reporting_context`    — (conditional on `malicious-reporting-sanctions` in `legal_recognitions`)
- `protected_action_standards`     — (sister field to `protected_action_context`; multi-select: `per-se-protected`|
                                      `actual-violation`|`reasonable-belief`|`good-faith`)
- `reasonable_belief_scope`        — (sister field to `reasonable_belief_context`; select: `objective-only`|
                                      `subjective-only`|`dual-prong`|`see-context`)
- `reasonable_belief_context`      — (conditional on `protected_action_standards` includes `reasonable-belief`)
- `protected_action_source`        — (sister field to `protected_action_context`; multi-select: `constitutional`|
                                      `statutory`|`judicial`|`regulatory`|`executive`|`see-context`)
- `protected_actions`              — (sister field to `protected_action_context`; taxonomy: `ws_protected_action`)
- `protected_action_context`       — (conditional on `protected-action` in `legal_recognitions`)
- `protected_disclosures`          — (taxonomy: `ws_protected_disclosure`)
- `protected_classes`              — (taxonomy: `ws_protected_class`)
- `former_employee_context`        — (conditional on `former-employee` in `protected_classes`)
- `protected_class_details`
- `excluded_classes`               — (sister field to `excluded_class_context`; taxonomy: `ws_excluded_class`)
- `excluded_class_context`         — (conditional on `excluded-class` in `legal_recognitions`)
- `excluded_class_details`
- `employment_sectors`             — (taxonomy: `ws_employment_sector`)
- `garcetti_exception_context`     — (conditional on `garcetti-exception` in `legal_recognitions` AND
                                      `public-sector` in `employment_sectors`)
- `disclosure_targets`             — (taxonomy: `ws_disclosure_target`)
- `disclosure_channel_scope`       — (sister field to `disclosure_channel_context`; select: `any-channel`|
                                      `approved-channel-only`|`mandatory-internal-first`|`see-context`)
- `disclosure_format`              — (sister field to `disclosure_channel_context`; select: `written-only`|
                                      `oral-permitted`|`either`|`has-details`)
- `disclosure_format_details`
- `disclosure_channel_context`     — (conditional on `has-channel` in `protected_disclosures`)
- `ic_channel_sequence_context`    — (conditional on `has-ic-channel` in `protected_disclosures`)
- `disclosure_target_details`

---

### Statute of Limitations & Thresholds Tab

Fields ordered: core SOL → modifiers → exhaustion → pathways → thresholds → federal/state interaction

- `sol_value`
- `sol_unit`                       — (select: `days`|`weeks`|`months`|`years`)
- `sol_triggers`                   — (multi-select: `accrual`|`constructive-discharge-accrual`|`discovery-rule`|
                                      `filing-of-complaint`|`conclusion-of-admin-process`|`see-context`)
- `sol_trigger_discovery_context`  — (conditional on `sol_triggers` includes `discovery-rule`)
- `sol_trigger_context`            — (conditional on `sol_triggers` is non-empty)
- `is_sol_suspended_during_admin`  — (only true when SOL is explicitly tolled while pending administrative action)
- `has_sol_details`
- `sol_details`
- `sop_value`                      — (sister field to `statute_of_repose_context`)
- `sop_unit`                       — (sister field to `statute_of_repose_context`; required when `sop_value` is
                                      non-empty; select: `days`|`weeks`|
                                      `months`|`years`)
- `is_sop_tolling_available`       — (sister field to `statute_of_repose_context`; true only when explicitly
                                     stated)
- `statute_of_repose_context`      — (conditional on `statute-of-repose` in `legal_recognitions`)
- `statutory_tolling_context`      — (conditional on `statutory-tolling` in `legal_recognitions`)
- `equitable_tolling_context`      — (conditional on `equitable-tolling` in `legal_recognitions`)
- `cba_preemption_context`         — (conditional on `cba-grievance-preemption` in `legal_recognitions`)
- `amended_claim_context`          — (conditional on `amended-claim` in `legal_recognitions`)
- `exhaustion_required_scope`      — (sister field to `exhaustion_required_context`; select:
                                      `jurisdictional`|`claims-processing`|`waivable`|`see-context`)
- `exhaustion_required_context`    — (conditional on `exhaustion-required` in `legal_recognitions`)
- `filing_notice_value`            — (sister field to `filing_notice_context`)
- `filing_notice_unit`             — (sister field to `filing_notice_context`; required when `filing_notice_value`
                                      is non-empty; select: `days`|`weeks`|`months`|
                                      `years`)
- `filing_notice_targets`          — (sister field to `filing_notice_context`; multi-select: `employer`|`agency`|
                                      `attorney-general`|`labor-board`|`see-context`)
- `filing_notice_context`          — (conditional on `pre-filing-notice` in `legal_recognitions`)
- `has_employer_threshold`
- `employer_threshold_compare`     — (sister field to `employer_threshold_details`; select: `gte`|`lte`|`gt`|`lt`|
                                      `eq`)
- `employer_threshold_value`       — (sister field to `employer_threshold_details`)
- `employer_threshold_model`       — (sister field to `employer_threshold_details`; select: `employees`|
                                      `contractors`|`workers`|`fte`)
- `employer_threshold_details`
- `has_cure_period`
- `cure_period_value`              — (sister field to `cure_period_details`)
- `cure_period_unit`               — (sister field to `cure_period_details`; select: `days`|`weeks`|`months`|
                                      `years`)
- `cure_period_details`
- `federal_state_interaction`      — (select: `express-preemption`|`savings-clause-preserves-state`|
                                      `concurrent-enforcement`|`field-preemption`|`state-exceeds-federal-floor`|
                                      `has-details`)
- `savings_clause_context`         — (conditional on `federal_state_interaction` is
                                      `savings-clause-preserves-state`)
- `federal_state_interaction_context`  — (conditional on `federal_state_interaction` is non-empty)
- `federal_state_interaction_details`

---

### Retaliation Tab

Fields ordered: adverse actions → recognitions → sanctions

- `adverse_actions`                   — (taxonomy: `ws_adverse_action`)
- `adverse_action_details`
- `adverse_action_scope`              — (select: `termination-only`|`material-adverse`|
                                         `broad-any-adverse-action`|`see-context`)
- `adverse_action_scope_context`      — (conditional on `adverse_action_scope` is non-empty)
- `preservation_deadline_value`       — (sister field to `evidence_preservation_context`)
- `preservation_deadline_unit`        — (sister field to `evidence_preservation_context`; required when
                                         `preservation_deadline_value` is non-empty; select: `days`|`weeks`|
                                         `months`|`years`)
- `preservation_requirement_scopes`    — (sister field to `evidence_preservation_context`; multi-select:
                                         `litigation-hold`|`statutory-hold`|`court-order`|
                                         `agency-request`|`see-context`)
- `evidence_preservation_context`     — (conditional on `evidence-preservation` in `legal_recognitions`)
- `constructive_discharge_standard`   — (sister field to `constructive_discharge_context`; select:
                                         `objective-intolerability`|`intent-required`|`dual-prong`|`see-context`)
- `constructive_discharge_context`    — (conditional on `constructive-discharge` in `adverse_actions`)
- `is_evidence_collection_protected`
- `anticipatory_retaliation_context`  — (conditional on `anticipatory-retaliation` in `adverse_actions`)
- `cats_paw_liability_context`        — (conditional on `cats-paw-liability` in `legal_recognitions`)
- `is_cats_paw_liability_extended`    — (sister field to `cats_paw_liability_context`; AND conditional on
                                         any child-slug of `associates-of-whistleblower` in `protected_classes`)
- `third_party_retaliation_context`   — (conditional on `third-party-retaliation` in `legal_recognitions`)
- `criminal_sanctions`                — (sister field to `criminal_sanctions_context`; repeater:
      ├── `sanction_conduct`                [multi-select: `retaliation`|`disclosure`|`false-report`|`obstruction`|
      │                                      `see-context`],
      ├── `sanction_level`                  [select: `misdemeanor`|`felony`|`see-context`],
      └── `sanction_context`                [conditional on `sanction_conduct` is non-empty])
- `criminal_sanctions_context`        — (conditional on `criminal-sanctions` in `legal_recognitions`)

---

### Processes & Remedies Tab

Fields ordered: process → pathway → fee shifting → remedies → reinstatement

- `process_types`                  — (taxonomy: `ws_process_type`)
- `primary_agency`                 — (derived from first attached post_type[`ws-agency`] when empty)
- `local_agencies`                 — (multi-select: post_type[`ws-agency`] filtered by jx, common `*disclosure*`
                                      and `process_types`)
- `federal_agencies`               — (multi-select: post_type[`ws-agency`] filtered to jx='us', common
                                      `*disclosure*` and `process_types`)
- `process_pathway_scope`          — (sister field to `process_pathway_context`; select: `agency-first-mandatory`|
                                      `direct-court`|`either`|`hybrid-right-to-sue-on-inaction`|`see-context`)
- `is_agency_inaction_trigger`     — (conditional on `process_pathway_scope` is
                                      `hybrid-right-to-sue-on-inaction`)
- `process_pathway_context`        — (conditional on `process-pathway` in `legal_recognitions`)
- `enforcement_sequence`           — (freetext; narrative glue tying enforcement agencies, sequence, and any
                                      enforcement requirements together; structured siblings include
                                      `process_types`, `primary_agency`, `local_agencies`, `federal_agencies`,
                                      and `process_pathway_scope`)
- `private_roa_context`            — (conditional on `private-right-of-action` in `legal_recognitions`)
- `jury_trial_scope`               — (sister field to `jury_trial_context`; select: `all-claims`|
                                      `damages-only`|`liability-only`|`see-context`)
- `jury_trial_context`             — (conditional on `private-right-of-action` in `legal_recognitions` AND
                                      `jury-trial` in `legal_recognitions`)
- `fee_shifting_standard`          — (sister field to `fee_shifting_standard_context`; select:
                                      `bilateral-loser-pays`|`unilateral-pro-plaintiff`|`none-american-rule`|
                                      `prevailing-defendant-bad-faith`|`see-context`)
- `fee_shifting_scopes`            — (sister field to `fee_shifting_standard_context`; multi-select: `mandatory`|
                                      `discretionary`|`asymmetrical`|`has-phases`; hook for contradictions)
- `fee_shifting_phases`            — (conditional on `fee_shifting_scopes` includes `has-phases`; repeater:
      ├── `phase`                        [select: `administrative`|`investigative`|`litigation`|`appeal`|
      │                                   `see-context`],
      ├── `phase_standard`               [select: `bilateral-loser-pays`|`unilateral-pro-plaintiff`|
      │                                   `unilateral-pro-defendant`| `prevailing-defendant-bad-faith`|
      │                                   `none-american-rule`|`see-context`],
      ├── `phase_scope`                  [required; multi-select: `mandatory`|`discretionary`|`asymmetrical`|`none`],
      ├── `phase_asymmetry`              [conditional on `phase_scope` includes `asymmetrical`; select: `two-way`|
      │                                   `one-way-plaintiff`|`one-way-defendant-frivolous`|`has-details`],
      ├── `asymmetry_details`            [conditional on `phase_asymmetry` is `has-details`],                   
      └── `phase_context`                [conditional on `phase` is non-empty])
- `fee_shifting_asymmetry`         — (conditional on `fee_shifting_scopes` includes `asymmetrical`; select:
                                     `two-way`|
                                      `one-way-plaintiff`|`one-way-defendant-frivolous`|`has-details`)
- `fee_shifting_asymmetry_details` — (conditional on `fee_shifting_asymmetry` is `has-details`)
- `fee_shifting_standard_context`  — (conditional on `fee-shifting-standard` in `legal_recognitions`)
- `remedies`                       — (taxonomy: `ws_remedy`)
- `remedy_limits`                  — (conditional on `remedies` includes `has-limits`)
- `remedy_caps`                    — (sister field to `remedy_limits`; repeater:
       ├── `remedy_cap`                  [select: `emotional-distress`|`punitive`|`compensatory`|`aggregate`|
       │                                  `employer-size-tiered`|`see-context`],
       ├── `employer_tier`               [conditional on `remedy_cap` is `employer-size-tiered`,
       │                                  e.g. "15-24", "100+"],
       ├── `cap_amount`                  [e.g., "300000", "uncapped"],
       ├── `applies_to`                  [select: `single-claim`|`per-plaintiff`|`per-incident`|`aggregate-action`|
       │                                  `see-context`],
       └── `cap_context`                [conditional on `remedy_cap` is non-empty])
- `is_blacklisting_extended`      — (conditional on `blacklisting` in `adverse_actions`; true when
                                      future-employers are also specified)
- `remedy_details`
- `remedy_liquidated_multiplier`   — (conditional on `liquidated-damages` in `remedies`; select:
                                      `double`|`treble`|`2x-back-pay`|`2x-wages-lost`|`statutory-formula`|
                                      `statutory-daily-fine`|`up-to-double`|`up-to-treble`|`has-details`)
- `remedy_liquidated_formula`      — (conditional on `remedy_liquidated_multiplier` is `statutory-formula`)
- `remedy_liquidated_details`      — (conditional on `remedy_liquidated_multiplier` is `has-details`)
- `mitigation_required_scopes`     — (sister field to `mitigation_required_context`; multi-select: `yes-statutory`|
                                      `yes-common-law`)
- `mitigation_required_context`    — (conditional on `mitigation-required` in `legal_recognitions`)
- `mitigation_exception_context`   — (conditional on `mitigation-required` in `legal_recognitions` AND
                                      `mitigation-exception` in `legal_recognitions`)
- `interest_provision_scope`       — (sister field to `interest_provision_context`; select:
                                     `pre-judgment-statutory`|
                                      `post-judgment`|`both`|`discretionary`|`see-context`)
- `interest_provision_context`     — (conditional on `equitable-interest-award` in `legal_recognitions`)
- `reinstatement_standard`         — (sister field to `preliminary_reinstatement_context`; select: `mandatory`|
                                      `discretionary`|`has-details`)
- `reinstatement_standard_details` — (conditional on `reinstatement_standard` is `has-details`)
- `preliminary_reinstatement_scopes`    — (sister field to `preliminary_reinstatement_context`; multi-select:
                                           `admin-phase`|`full-pendency-only`)
- `preliminary_reinstatement_context`   — (conditional on `preliminary-reinstatement` in `legal_recognitions`)
- `mixed_motive_remedy_context`    — (conditional on `burden_shifting_frameworks` includes `mixed-motive`;
                                      see [Cross-Tab Conditional and Monitoring] below)

---

### Burden Of Proof Tab

Fields ordered: framework → employee standards → causation → employer defenses →
rebuttable presumption → temporal presumption → detail overflow

- `burden_shifting_frameworks`     — (sister field to `burden_shifting_context`; multi-select: `mcdonnell-douglas`|
                                      `motivating-factor`|`but-for`|`mixed-motive`|`has-details`;
                                       see [Cross-Tab Conditional and Monitoring] below)
- `burden_shifting_details`        — (conditional on `burden_shifting_frameworks` includes `has-details`)
- `burden_shifting_context`        — (conditional on `burden-shifting-framework` in `legal_recognitions`)
- `same_decision_standard`         — (sister field to `same_decision_context`; select: `preponderance`|
                                      `clear-and-convincing`|`see-context`)
- `same_decision_context`          — (conditional on `employer_defenses` includes `same-decision-defense`)
- `causal_nexus_statutory_text`    — (conditional on `causation_standard` is non-empty; verbatim or near-verbatim
                                      statutory language describing the causal link standard)
- `employee_standard`              — (single-select taxonomy: `ws_employee_standard`; evidentiary burden only)
- `employee_standard_details`
- `causation_standard`             — single-select (taxonomy: `ws_causation_standard`; causal link standard)
- `causation_application`          — (sister field to `causation_standard_context`; select: `liability`|
                                      `damages`|`both`|`see-context`)
- `causation_application_context`  — (conditional on `causation_application` is non-empty)
- `causation_standard_context`     — (conditional on `causation_standard` is non-empty)
- `causation_dual_standard_context`  — (conditional on `causation-dual-standard` in `legal_recognitions`)
- `employer_knowledge_scopes`      — (sister field to `employer_knowledge_context`; multi-select:
                                      `actual-knowledge`|`constructive-knowledge`|`inferred-knowledge`|
                                      `imputed-knowledge`|`has-details`)
- `employer_knowledge_scopes_details`
- `employer_knowledge_context`     — (conditional on `employer-knowledge` in `legal_recognitions`)
- `employer_defenses`              — (taxonomy: `ws_employer_defense`)
- `employer_defense_details`
- `has_rebuttable_presumption`
- `rebuttable_presumption_details`
- `has_temporal_presumption`
- `presumption_window_value`       — (sister field to `temporal_presumption_details`)
- `presumption_window_unit`        — (sister field to `temporal_presumption_details`; select: `days`|`weeks`|
                                      `months`|`years`)
- `presumption_effect`             — (sister field to `temporal_presumption_details`; select: `shifts-burden`|
                                      `creates-inference`|`rebuttable-presumption`|`has-details`)
- `presumption_effect_details`
- `temporal_presumption_details`
- `temporal_proximity_value`       — (sister field to `temporal_proximity_context`)
- `temporal_proximity_unit`        — (sister field to `temporal_proximity_context`; required when
                                      `temporal_proximity_value` is non-empty; select: `days`|`weeks`|
                                      `months`|`years`)
- `temporal_proximity_context`     — (conditional on `temporal-proximity-sufficient` in `legal_recognitions`)
- `has_bop_details`
- `bop_details`

---

### Reward Tab

Fields ordered: rewards → qui tam specifics

- `has_reward`
- `reward_discretion_standard`     — (sister field to `reward_details`; select: `mandatory`|`discretionary`|
                                      `presumptive`|`formula-based`|`has-details`)
- `reward_discretion_formula`      — (conditional on `reward_discretion_standard` is `formula-based`)
- `reward_discretion_details`      — (conditional on `reward_discretion_standard` is `has-details`)
- `reward_details`
- `qui_tam_government_share`       — (sister field to `qui_tam_share_context`; range when government intervenes;
                                      e.g. "15%–25%")
- `qui_tam_relator_share`          — (sister field to `qui_tam_share_context`; range when government declines;
                                      e.g. "25%–30%")
- `qui_tam_reduction_context`      — (sister field to `qui_tam_share_context`; conditions under which the court may
                                      reduce share below statutory floor)
- `qui_tam_share_context`          — (conditional on `qui-tam` in `process_types`)
- `has_first_to_file_bar`          — (sister field to `qui_tam_share_context`; AND
                                      `bounty-qui-tam-award` in `remedies`)
- `first_to_file_bar_details`
- `has_public_disclosure_bar`      — (sister field to `qui_tam_share_context`; AND
                                      `bounty-qui-tam-award` in `remedies`)
- `public_disclosure_bar_details`

---

### Waiver & Scope Tab

Fields ordered: contractual → recognitions → immunity → defendants.

- `all_waivers_blocked_context`    — (conditional on `all-waivers-blocked` in `legal_recognitions`)
- `civil_action_waiver_scope`      — (sister field to `civil_action_waiver_context`; select: `prohibited`|
                                      `permitted-individual-only`|`permitted-collective`|`see-context`)
- `civil_action_waiver_context`    — (conditional on `all-waivers-blocked` absent in `legal_recognitions` AND
                                      `civil-action-waiver` in `legal_recognitions`)
- `contractual_waiver_scope`       — (sister field to `contractual_waiver_context`; select: `void`|
                                      `limited`|`enforceable`|`void-public-policy`|`void-as-to-whistleblowing`|
                                      `enforceable-with-exceptions`|`see-context`)
- `contractual_waiver_context`     — (conditional on `all-waivers-blocked` absent in `legal_recognitions` AND
                                      `contractual-waiver` in `legal_recognitions`)
- `collateral_claims_waiver_context`  — (conditional on `all-waivers-blocked` absent in `legal_recognitions` AND
                                      `collateral-claims-waiver` in `legal_recognitions`)
- `class_action_waiver_context`    — (conditional on `all-waivers-blocked` absent in `legal_recognitions` AND
                                      `class-action-waiver` in `legal_recognitions`)
- `proper_defendants`              — (multi-select: `employer-entity`|`individual-supervisors`|
                                      `government-agency`|`contractors-included`|`successor-employer`|
                                      `joint-employer`|`staffing-agency`|`scope-of-employment-required`|
                                      `has-details`)
- `is_employer_only_defendant`     — (true only when the law expressly limits proper defendants to the employer
                                      entity; hook forces `proper_defendants` to `employer-entity` only)
- `proper_defendant_details`
- `joint_employer_context`         — (conditional on `proper_defendants` includes `joint-employer` OR
                                      `proper_defendants` includes `staffing-agency`)
- `individual_liability_scopes`    — (sister field to `individual_liability_context`; multi-select: `supervisor`|
                                      `coworker`|`officer-director`|`any-individual-only`|`has-details`)
- `individual_liability_context`   — (conditional on `individual-liability` in `legal_recognitions`)
- `sovereign_immunity_status`      — (sister field to `sovereign_immunity_context`; select: `not-waived`|
                                      `partially-waived`|`fully-waived`|`has-details`)
- `sovereign_immunity_limits`      — (conditional on `sovereign_immunity_status` is non-empty; multi-select:
                                      `cap-applies`|`conditions-apply`|`tort-claims-act-gate`)
- `sovereign_immunity_scope`       — (conditional on `sovereign_immunity_status` is non-empty; select: `all`|
                                      `instrumentalities-included`|`political-subdivisions-included`|
                                      `state-only`|`see-context`)
- `sovereign_immunity_waiver_class` — (conditional on `sovereign_immunity_status` is NOT `not-waived`; select:
                                      `explicit-waiver`|`implied-waiver`)
- `sovereign_immunity_status_details`
- `sovereign_immunity_context`     — (conditional on `sovereign-immunity-status` in `legal_recognitions`)
- `nda_limits_context`             — (conditional on `nda-limitations` in `legal_recognitions`)
- `anti_gag_provision_context`     — (conditional on `anti-gag-provision` in `legal_recognitions`)
- `no_retaliatory_evidence_context`  — (conditional on `no-retaliatory-evidence` in `legal_recognitions`)
- `stay_of_discipline_context`     — (conditional on `stay-of-disciplinary-action` in `legal_recognitions`)
- `anti_slapp_protection_scopes`   — (sister field to `anti_slapp_protection_context`; multi-select:
                                      `motion-to-strike`|`discovery-stay`|`fee-shift-on-motion`|
                                      `full-procedural-only`|`see-context`)
- `anti_slapp_protection_context`  — (conditional on `anti-slapp-protection` in `legal_recognitions`)
- `discovery_protection_context`   — (conditional on `discovery-protection` in `legal_recognitions`; documents
                                      specific protections against retaliatory subpoenas, abusive discovery,
                                      or litigation harassment distinct from anti-SLAPP)
- `settlement_restriction_scope`   — (sister field to `settlement_restriction_context`; select: `amount-only`|
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

- `url`                            — (url field; statute / precedent / case law URL (shared slot))
- `url_is_pdf`                     — (true when source URL points to a PDF)
- `authority_reference`            — (holds the official legislative history citation or regulatory citation
                                      (CFR, Federal Register, etc.))

---

### Hidden Fields (no tab; prefixed with underscore)

Fields ordered: id → derived

- `_id`                            — (generated by ingest tool or matrix seeder)
- `_disclosure_target_class`       — (derived from `disclosure_targets`; select:
                                      `internal`|`external`|`both`)
- `_primary_agency_is_fed`         — (derived from `primary_agency` jx)
- `_related_agencies`              — (merged array of `local_agencies` and `federal_agencies`)

---

## Specialized Fields By Legal Record Type

---

### Substantive-Record Common Fields (statute + common_law)

Substantive records carry most of the fields defining whistleblower protections.

#### Fields Excluded From Substantive Records

The following precedent-only fields capture modifications to legal definitions and do not appear on substantive
records:

- `court`                 — federal or local court that made the ruling
- `scope`                 — the result of the ruling (`favorable`|`adverse`|`neutral`|`dual-effect`) on
                            whistleblower protections; use `dual-effect` when the same ruling both extends and
                            suppresses different taxonomy terms
- `binding_scope`         — effective strength of the ruling
- `affected_jx`           — affected jurisdictions (when more than the single preceding court's jx is affected;
                            e.g. California Supreme Court (jx=['ca']) ruling only affects California (jx=['ca']),
                            U.S. Court of Appeals for the Seventh Circuit (jx=['us']) affects jx =>
                            ['il','in','wi'])
- `extended_taxonomies`   — affected taxonomy when ruling is favorable or dual-effect
- `suppressed_taxonomies` — affected taxonomy when ruling is adverse or dual-effect
- `_parent_ids`: `statute_ids`, `comlaw_ids` — legal record or records affected by ruling

#### Substantive Additions

#### Processes & Remedies Tab (insert after `jury_trial_context`)

- `review_standard_scope`          — (sister field to `review_standard_context`; select: `de-novo`|
                                      `substantial-evidence`|`arbitrary-capricious`|`abuse-of-discretion`|
                                      `has-details`)
- `review_standard_scope_details`  — (conditional on `review_standard_scope` is `has-details`)
- `review_standard_context`        — (conditional on `civil-review-standard` in `legal_recognitions`)

#### Retaliation Tab (insert after `anticipatory_retaliation_context`)
- `election_of_remedies_rules`     — (required; multi-select: `administrative-bars-civil`|`state-bars-federal`|
                                      `remedy-exclusivity`|`first-filed-controls`|`no-election-required`|
                                      `see-context`)
- `election_of_remedies_context`   — (conditional on `election_of_remedies_rules` is non-empty)

#### Relationships Tab

- `citation_ids`
- `construction_ids`

#### Hidden Fields

- `_precedent_ids`                 — (merged array of `citation_ids` and `construction_ids`)

---

### Statute-Specific

This section is preserved as a structural slot for the four legal-record types. Statute records have no deltas
from the common record set; all statute fields are inherited from [Common Fields] and [Substantive-Record Common
Fields]. Future statute-only additions, if any, will be documented here.

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

---

### Precedent-Record Common Fields (citation + construction)

Precedent records carry most common fields. Some notable exceptions are fields that are definitionally
inapplicable to court decisions:
- `election_of_remedies_rules`  — Legislative/Doctrinal construct; not a court ruling.
- `doctrine_*`, `public_policy_*`, `recognition_*`, etc. — Common-law-specific fields that have no precedent
  equivalent.
- `_precedent_ids`              — Precedent-Records have `_parent_ids`: `statute_ids`, `comlaw_ids` instead.
These fields do not appear on precedent-records.

#### Identity Tab (insert after `citation`)

- `class`                          — (select: `case-law`|`statute`|`regulatory`|`secondary`|`has-details`)
- `class_details`                  — (conditional on `class` is `has-details`)
- `status`                         — (select: `published`|`unpublished`|`memorandum`|`vacated`)
- `binding_scope`                  — (select: `binding`|`persuasive`|`mixed`|`distinguished`|`overruled`)
- `court`                          — (select; filtered by jx)
- `court_details`                  — (conditional on `court` is `has-details`)
- `court_jx`                       — (sister field to `court_details`; taxonomy: `WS_JURISDICTION_TAXONOMY`)

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
- `affected_jx`                    — (conditional on `has_affected_jx`; taxonomy: `WS_JURISDICTION_TAXONOMY`)

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
- `ws_protection_scope` — internal editorial classification.
- `ws_aorg_*`, `ws_language`, `ws_case_stage`, `ws_procedure_type` — not attached to legal-record CPTs.

#### Relationships Tab

- `statute_ids`
- `comlaw_ids`
- `parent_weight`                  — (select: `primary`|`secondary`|`distinguishing-only`)
- `has_negative_treatment`
- `negative_treatment_class`     — (sister field to `negative_treatment_details`; select: `overruled`|
                                    `distinguished`|`limited`|`questioned`|`superseded-by-statute`|
                                    `see-details`)
- `negative_treatment_details`

#### Authority Tab (insert after `authority_reference`)

- `authority_sources`              — (multi-select: `constitutional`|`legislative`|`judicial`|`regulatory`|
                                      `executive`|`has-details`)
- `authority_source_details`
- `review_standard_scope`          — (select: `de-novo`|`substantial-evidence`|`arbitrary-capricious`|
                                      `abuse-of-discretion`|`has-details`)
- `review_standard_scope_details`   — (conditional on `review_standard_scope` is `has-details`)

#### Hidden Fields

- `_parent_ids`                    — (merged array of `statute_ids` and `comlaw_ids`)
- `_court_is_fed`                  — (derived from `court` `ws_jx_codes`)

---

### Citation-Specific

This section is preserved as a structural slot for the four legal-record types. Citation records have no deltas
from the precedent-record common set; all citation fields are inherited from [Common Fields] and
[Precedent-Record Common Fields]. Future citation-only additions, if any, will be documented here.

---

### Construction-Specific

#### Identity Tab

- `is_en_banc`                     — (defaults true; when false, triggers `panel_composition_details`; approved use
                                      of `is_*` bool as trigger)
- `panel_composition_class`        — (sister field to `panel_composition_details`; select: `three-judge`|
                                      `five-judge`|`seven-judge`|`nine-judge`|`expanded-panel`|`judge`|
                                      `see-details`)
- `panel_composition_details`      — (conditional on `is_en_banc` is false; approved use of `is_*` bool as trigger)

---

## Rename Normalization (Current → Canonical)

Only fields that currently violate target naming conventions, are inconsistent
across legal ACFs, or were structurally redesigned during the canonical rewrite.
Fields that are unchanged or new do not appear in this list.

- `fee_shiftings`                            → `fee_shifting_standard`
- `has_limit_ambiguous`                      → `has_sol_details`
- `limit_details`                            → `sol_details`
- `has_tolling_details`                      →  split into `statutory-tolling` and `equitable-tolling` true when
  present in `legal_recognitions`
- `tolling_details`                          →  split into `statutory_tolling_context` and
  `equitable_tolling_context`
- `has_exhaustion_required`                  → `exhaustion-required`            in `legal_recognitions`
- `exhaustion_details`                       → `exhaustion_required_context`
- `exhaustion_is_jurisdictional` (bool)      → `exhaustion_required_scope`     (single select)
- `rebuttable_presumption`                   → `rebuttable_presumption_details`
- `has_statutory_preclusion`                 → `statutory-preclusion`           in `legal_recognitions`
- `doctrine_basis_wysiwyg`                   → `doctrine_basis`                (never was wysiwyg)
- `recognition_status_wysiwyg`               → `recognition_status`            (select) — (never was wysiwyg) +
  `recognition_status_details` (textarea)
- `other_sources`                            → `policy_source_details`         (uses `has-details` sentinel now)
- `doctrine_name`                            → `official_name`
- `statute_citation` / `precedent_name` / `case_name` / `case_citation`        (shared slot)   → `citation`
- `enacted_date` / `ruling_date` / `decision_date`                             (shared slot)   → `date`
- `statute_url` / `precedent_url` / `citation_url` / `construction_url`        (shared slot)   → `url`
- `statute_url_is_pdf` / `precedent_url_is_pdf` / `citation_url_is_pdf` /
  `construction_url_is_pdf`                                                    (shared slot)   → `url_is_pdf`
- `superseded_by`                            → `overruled_by_id`
- `has_constructive_discharge_recognized`    → `constructive-discharge`         in `adverse_actions`
- `has_anticipatory_retaliation_recognized`  → `anticipatory-retaliation`       in `adverse_actions`
- `continuing_violation_recognized`          → `continuing-violation-doctrine`  in `ws_legal_recognition`
- `equitable_tolling_recognized`             → `equitable-tolling`              in `ws_legal_recognition`
- `has_amended_claim_recognized`             → `amended-claim`                  in `ws_legal_recognition`
- `arbitration_waiver_enforceability`        → `contractual-waiver`             in `legal_recognitions`
- `disclosure_target_type`                   → `_disclosure_target_class`       (derived, hidden)
- `court_name`                               → `court_details`                  (uses `has-details` sentinel now)
- `is_favorable` (bool)                      → `scope`                          (single select)
- `adverse_action_scope` (textarea)          → `adverse_action_scope`           (select) +
  `adverse_action_scope_context` (freetext)
- `doctrine_id`                              →  removed (visible dedupe IDs deemed unnecessary)
- `bop_flag`                                 →  removed (used by researchers only, never meant for ACF meta)
- `statute_id` + `comlaw_id` — singular      →  pluralized to support (rare-but-possible) multi-values
- `disclosure_types`                         → `protected_disclosures`
- `ws_disclosure_type` (taxonomy)            → `ws_protected_disclosure`        (taxonomy)
- `sol_trigger_event`                        →  removed (collapsed in to unified `sol_trigger`;
  `sol_trigger_context` now must describe legal, factual, and contextual per trigger)
- `has_preemption` + `preemption_details`    →  removed (preemption block replaced with
  `federal_state_interaction` block)
- `preemption_direction`                     → `federal_state_interaction`
- `sovereign_immunity_statuses`              → `sovereign_immunity_status`      (select)
- `threshold_compare`                        → `employer_threshold_compare`
- `threshold_value`                          → `employer_threshold_value`
- `threshold_unit`                           → `employer_threshold_unit`
- `public_concern_context`                   → `public_concern_required_context`

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

Conditional-companion fields named `*_context` and noted with ` → ` are revealed when the corresponding slug is
present in `legal_recognitions`. Sister fields noted with ` + ` inherit that conditional behavior from the
triggered sibling and cannot appear unless the triggered sibling is also revealed. Sister fields may appear before
or after the sibling that defines their visibility, and any additional sister-field requirements must be documented
after the sibling declaration with `AND`, `OR`, or `NOT`.

Within triggered clusters, companion and sister fields are required when logically necessary. Generated ACF should
set `'required' => 1` where applicable. For critical requirements, hooks should detect when a triggering slug is
present but a required field in that cluster is empty, then block save with a validation error that identifies both
the triggering slug and the missing field.

Annotation legend:

- `[R]` — Required field for the triggered cluster. Hook must block save when slug is present and field is empty.
- `[+]` — Conditional state marker. The field exists in the cluster but is set or revealed only when its own
  documented condition is met (e.g., an explicit affirmative state, or a child-slug presence in another taxonomy).
  `[+]` fields are not required by default; the field's inline definition documents the additional condition.

```

// ── Identity Tab ─────────────────────────────────────────────────────────────
'retroactive-date'                    → 'retro_context'                         + 'retro_date' [R]                   // Specified

// ── Classification Tab ───────────────────────────────────────────────────────
'manager-rule-exclusion'              → 'manager_rule_exclusion_context' [R]                                         // Applies
'public-concern-required'             → 'public_concern_required_context' [R]                                        // Applies
'bad-faith-exclusion'                 → 'bad_faith_exclusion_context' [R]                                            // Applies
'malicious-reporting-sanctions'       → 'malicious_reporting_context'           + 'malicious_reporting_sanctions' [R] // Specified
'anonymity-protection'                → 'anonymity_protection_context' [R]                                           // Recognized
'protected-action'                    → 'protected_action_context'              + 'protected_actions' [R] + 'protected_action_standards'
                                                                                + 'protected_action_source'          // Specified
                                                                                // NESTED: 'reasonable-belief' in 'protected_action_standards' triggers
                                                                                //         'reasonable_belief_context' + 'reasonable_belief_scope' (sister)
'excluded-class'                      → 'excluded_class_context'                + 'excluded_classes' [R]             // Specified
'garcetti-exception'                  → 'garcetti_exception_context' [R]                                             // Applies

// ── Statute of Limitations & Thresholds Tab ──────────────────────────────────
'statute-of-repose'                   → 'statute_of_repose_context'             + 'sop_value' [R] + 'sop_unit' [R]
                                                                                + 'is_sop_tolling_available' [+]     // Specified
'statutory-tolling'                   → 'statutory_tolling_context' [R]                                              // Specified
'equitable-tolling'                   → 'equitable_tolling_context' [R]                                              // Recognized
'cba-grievance-preemption'            → 'cba_preemption_context' [R]                                                 // Applies
'amended-claim'                       → 'amended_claim_context' [R]                                                  // Recognized
'exhaustion-required'                 → 'exhaustion_required_context'           + 'exhaustion_required_scope' [R]    // Required
'pre-filing-notice'                   → 'filing_notice_context'                 + 'filing_notice_targets' [R] + 'filing_notice_value' [R]
                                                                                + 'filing_notice_unit' [R]           // Required
'statutory-preclusion'                → 'statutory_preclusion_context' [R]                                           // Applies

// ── Retaliation Tab ───────────────────────────────────────────────────────────────────
'evidence-preservation'               → 'evidence_preservation_context'         + 'preservation_deadline_value' + 'preservation_deadline_unit'
                                                                                + 'preservation_requirement_scopes' [R] // Required
'cats-paw-liability'                  → 'cats_paw_liability_context' [R]        + 'is_cats_paw_liability_extended' [+]                                     // Recognized
'third-party-retaliation'             → 'third_party_retaliation_context' [R]                                        // Prohibited
'criminal-sanctions'                  → 'criminal_sanctions_context'            + 'criminal_sanctions' [R]           // Specified

// ── Processes & Remedies Tab ──────────────────────────────────────────────────────────
'process-pathway'                     → 'process_pathway_context'               + 'process_pathway_scope' [R]       // Specified
'private-right-of-action'             → 'private_roa_context' [R]                                                    // Available
'jury-trial'                          → 'jury_trial_context'                    + 'jury_trial_scope' [R]             // Available
'fee-shifting-standard'               → 'fee_shifting_standard_context'         + 'fee_shifting_standard' [R]
                                                                                + 'fee_shifting_scopes' [R]          // Specified
'civil-review-standard'               → 'review_standard_context'               + 'review_standard_scope' [R]
'equitable-interest-award'            → 'interest_provision_context'            + 'interest_provision_scope' [R]     // Available
'mitigation-required'                 → 'mitigation_required_context'           + 'mitigation_required_scopes' [R]   // Specified
'mitigation-exception'                → 'mitigation_exception_context' [R]                                           // Recognized
'preliminary-reinstatement'           → 'preliminary_reinstatement_context'     + 'reinstatement_standard' [R]
                                                                                + 'preliminary_reinstatement_scopes' [R] // Available

// ── Burden of Proof Tab ───────────────────────────────────────────────────────────────
'burden-shifting-framework'           → 'burden_shifting_context'               + 'burden_shifting_frameworks' [R]   // Specified
'causation-dual-standard'             → 'causation_dual_standard_context' [R]                                        // Applies
'employer-knowledge'                  → 'employer_knowledge_context'            + 'employer_knowledge_scopes' [R]    // Required
'temporal-proximity-sufficient'       → 'temporal_proximity_context'            + 'temporal_proximity_value' [R]
                                                                                + 'temporal_proximity_unit' [R]      // Recognized

// ── Waiver & Scope Tab ────────────────────────────────────────────────────────────────
'all-waivers-blocked'                → 'all_waivers_blocked_context' [R]                                          // Prohibited
'civil-action-waiver'                → 'civil_action_waiver_context'         + 'civil_action_waiver_scope' [R]    // Recognized
'contractual-waiver'                  → 'contractual_waiver_context'            + 'contractual_waiver_scope' [R]     // Recognized
'collateral-claims-waiver'            → 'collateral_claims_waiver_context' [R]                                      // Applies
'class-action-waiver'                 → 'class_action_waiver_context' [R]                                            // Recognized
'sovereign-immunity-status'           → 'sovereign_immunity_context'            + 'sovereign_immunity_status' [R]    // Specified
'nda-limitations'                     → 'nda_limits_context' [R]                                                     // Recognized
'anti-gag-provision'                  → 'anti_gag_provision_context' [R]                                             // Recognized
'no-retaliatory-evidence'             → 'no_retaliatory_evidence_context' [R]                                        // Barred
'stay-of-disciplinary-action'         → 'stay_of_discipline_context' [R]                                             // Available
'anti-slapp-protection'               → 'anti_slapp_protection_context'         + 'anti_slapp_protection_scopes' [R] // Applies
'discovery-protection'                → 'discovery_protection_context' [R]                                           // Applies
'confidential-settlement-restriction' → 'settlement_restriction_context'        + 'settlement_restriction_scope' [R] // Applies
'individual-liability'                → 'individual_liability_context'          + 'individual_liability_scopes' [R]  // Available
'successor-liability'                 → 'successor_liability_context' [R]                                            // Recognized
'extraterritorial-coverage'           → 'extraterritorial_context' [R]                                               // Recognized

// ── Without Context (no tab) ─────────────────────────────────────────────────────────
'statutory-nexus-diverges-from-common-law'  — (no companion needed)   // Applies
'catch-all-protection'                      — (no companion needed)   // Present
'internal-only-disclosure'                  — (no companion needed)   // Sufficient
'trade-secret-immunity'                     — (no companion needed)   // Recognized
'continuing-violation-doctrine'             — (no companion needed)   // Recognized
'prospective-whistleblower-protection'      — (no companion needed)   // Available
'blanket-sovereign-immunity-waiver'         — (no companion needed)   // Recognized
                                            // EXCLUDES 'sovereign-immunity-status' slug — when this slug is present,
                                            //          the sovereign_immunity_* status cluster is logically void
                                            //          and 'sovereign-immunity-status' must not also be in legal_recognitions.
                                            //          Hook should validation_error if both are present.
'class-action'                              — (no companion needed)   // Permitted
'official-duties-carveout'                  — (no companion needed)   // Applies

```

---

## Cross-Taxonomy Slug-to-Companion Map

Some `*_context` companions are triggered by terms in taxonomies other than `ws_legal_recognition`. They follow the
same trigger → companion + sister structure but live outside the primary recognition map. The legend (`[R]`, `[+]`,
` → `, ` + `) carries the same meaning as in the primary map.

```

// ── Triggered by terms in `ws_employer_defense` ──────────────────────────────────────
'same-decision-defense'           → 'same_decision_context'                + 'same_decision_standard'

// ── Triggered by terms in `ws_protected_class` ───────────────────────────────────────
'former-employee'                 → 'former_employee_context'

// ── Triggered by terms in `ws_protected_disclosure` ──────────────────────────────────
'has-channel'                     → 'disclosure_channel_context'           + 'disclosure_channel_scope'
                                                                           + 'disclosure_format'
                                                                           + 'disclosure_format_details'
'has-ic-channel'                  → 'ic_channel_sequence_context'

// ── Triggered by terms in `ws_adverse_action` ────────────────────────────────────────
'constructive-discharge'          → 'constructive_discharge_context'       + 'constructive_discharge_standard'
'anticipatory-retaliation'        → 'anticipatory_retaliation_context'

// ── Triggered by terms in `ws_process_type` ──────────────────────────────────────────
'qui-tam'                         → 'qui_tam_share_context'                + 'qui_tam_government_share'
                                                                           + 'qui_tam_relator_share'
                                                                           + 'qui_tam_reduction_context'
                                                                           + 'has_first_to_file_bar'
                                                                           + 'has_public_disclosure_bar'

```

---

## Notes

This spec uses the statute field set as the baseline for broad legal-record parity, then adds per-type deltas
where a record type requires different structure. `ws_legal_recognition` is a presence/absence signal table, not a
classification table. Slugs marked `(no companion needed)` in the [Slug-to-Companion Map] are captured only through
the `ws_legal_recognition` taxonomy; no separate ACF field is registered for those slugs.

All conditional logic must use the accepted annotation forms listed in the [Conditional Annotation] section. Sister
fields inherit visibility from their triggered sibling but are not independently conditional.

This spec has zero live-data implications. It is the structural source of truth for PHP field generation and
ingest schema mapping.

### Ingest Pipeline Philosophy

The legal-record field set is the *destination* schema, not the *acquisition* schema. Researchers — including LLM
research assistants — are not expected to find or report data in ACF-field shape. The pipeline is staged so that
each layer extracts what it can model cleanly and passes the rest forward as narrative for the next layer:

1. **Researcher (LLM or human).** Reports findings in legal terms, not field terms. When findings happen to map
   cleanly to a field, that's a win — the value lands in the field. When they don't, they ride forward as a
   breadcrumb in narrative, freetext, or `*_context` companions for downstream review.
2. **Reconciler.** Performs middle-ground work that would stress researchers — disambiguating cross-jurisdictional
   terminology, normalizing values, splitting compound findings, resolving freetext into structured equivalents
   when possible. Some reconciler output now maps cleanly to a field: another win. The remainder stays as
   breadcrumb for human review.
3. **Human reviewer.** Final reconciliation. Validates field-mapped values, reads breadcrumbs, and either promotes
   them to fields, expands them in `_review_notes`, or accepts them as narrative-only (`*_context`,
   `general_description`, `plain_english_wysiwyg`).

This staging is the operational form of the omission-over-fabrication rule: each layer maps what it can and
declines to invent what it can't. Freetext fields and `*_context` companions are not failure modes — they are
the designed channels through which breadcrumbs survive between stages without being lost or invented over.
