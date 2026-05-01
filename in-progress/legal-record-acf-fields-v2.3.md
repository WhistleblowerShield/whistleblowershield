# Legal Record ACF Canonical Field Draft (v2.3)

**Purpose:** Unified, prefix-free field set for all four legal record types
(`statute`, `common_law`, `citation`, `construction`) as the working spec for the
next ingest/render rewrite cycle.

**Notes:** Do not update existing files. Rename existing files with `.txt` appended.
Create new files with same names as the originals.

---

## Naming Rules

### Casing
Strictly enforced. No exceptions.

- Meta names (ACF field `name` key): `snake_case` only.
- Choice keys (select / multi-select option values): `kebab-case` only.
- Taxonomy terms: `kebab-case` only.

### CPT Infix
Absent from this draft; applied at registration. Strictly enforced. No exceptions.

- Codebase indicator: `ws_*`
- Parent record indicator: `jx_*`, indicates legal records are all children of `jurisdiction` records
- CPT slot values: `statute` · `comlaw` · `citation` · `construction`

- field `name` (meta key): `ws_jx_statute_*` · `ws_jx_comlaw_*` · `ws_jx_citation_*` · `ws_jx_construction_*`
- field `key`: `field_jx_statute_*` · `field_jx_comlaw_*` · `field_jx_citation_*` · `field_jx_construction_*`
  * There is no `ws_*` codebase indicator.
- tab field `key`: `field_jx_{cpt}_{tab-label}_tab`
  * `tab-label` is tab field `label` lowercase; no `_and_`, no symbols, omit.
  * Do not use abbreviations in tabs.
  * No longer approved: `sol` = `statute_of_limitations` · `bop` = `burden_of_proof`
- group `key`: `group_jx_{cpt}_metadata`
- group `menu_order`: must be < 85 — workflow groups occupy 85–99; CPT group must precede them.

### Reserved Prefixes
Strictly enforced. No exceptions.

- `ws_auto_` — written exclusively by hook logic (stamp, source, plain-English attribution).
   Never use on content fields.

### Cardinality
Strictly enforced.

- Single-value fields: singular noun.
- Multi-value fields (multi-select, repeater, array): plural noun.

### Booleans

- `has_*` — trigger boolean. True activates a companion or dependent field. May trigger `*_details`, another
   field (e.g. `has_effective_date` triggers `effective_date`), or both.
- `is_*` or `*_is_*` — state boolean. Describes a condition; does not imply a companion.

Use of either boolean outside this scope requires approval and inline documentation.

### Companion Suffixes

- `*_details` — freetext companion. Uses 2 types of `has*` triggers (`*_details` always implied):
    * When `has_field_name` is true, `field_name_details` is triggered. `has_field_name_details` is not correct.
    * When `has-details` sentinel is present in trigger `field_name`, conditional `field_name_details` is triggered.
- `*_context` — freetext companion. Uses 2 types of triggers:
    * When `trigger_field` contains specified `trigger-value`.
    * When `trigger_field` is non-empty.
- `has-*` sentinel or `has_*` bool can be used as a trigger for any conditional companion field as `*_companion`,
   but trigger and companion fields must share the same name or conditional logic must be well-documented.
    * e.g., `has_field_name_limits` and `field_name_limits`, `has-phases` in `field_name` and `field_name_phases`.

Conditional annotation not required when the field naming convention makes the trigger unambiguous. Annotation
is required when the trigger `field_name` deviates from conditional `field_name` (e.g., a suffix or prefix is
dropped). For `*_context` conditionals, annotation is always required.

### Sister Fields
Sister fields inherits their sibling's conditional but are not considered a `*_companion` field. Sisters use
the identical conditionals of their siblings. Do not re-document conditionals. Instead note inline as "Sister field
to `sibling_field`;"

- No naming convention applies to sister fields. Use a logical/contextual name for the data it holds or concept
  it covers.
- Sisters may appear before or after their sibling. Freetext sibling fields normally appear last, but no order is
  prescribed; use best editorial logic.
- Sisters may not appear without a corresponding `*_companion` sibling in the same cluster.
- Sister clusters can chain. When multiple conditions layer: chains get messy — use inline notes where they help.

### Avoid
Avoidance is preferred not required.

- `*_recognized`  — use a `ws_legal_recognition` taxonomy term to signal bool state where possible/logical.
- `*_type`        — prefer `*_class`, `*_scope`, `*_status`, `*_rule`, `*_framework`, `*_weight`, or `*_standard`.
    * List is not exhaustive.
    * Use `*_type` when context requires it and no better suffix fits.
    * Pluralize suffix accordingly.
- `*_limitations` — prefer `*_limits` in field names. No preference where used otherwise.

### Data Shape Suffixes
Use a data shape suffix when the field holds corresponding data shape. Never use otherwise.

- `*_url`, `*_date`, `*_email`, etc.
- `*_value` (int), `*_unit` (calendar-unit select: `days`|`weeks`|`months`|`years`)

---

## Sentinel Values: Naming Rules/Currently in Use Values
Sentinels are reserved choice keys and taxonomy term slugs with defined system behavior.

### Trigger Sentinels
Added to a field's choices or taxonomy to signal a companion should appear.

- `has-details` — triggers the `*_details` companion. Companion field must be relevant to trigger field.
   * Prefer over `other`, `unclear`, or `mixed` when a trigger + companion can capture the nuance.
- When conditional requires new taxonomy term trigger use `has-*` prefix only.
- When new taxonomy term is added to hierarchical taxonomy table, new term must nest under `has-parent`.

### Non-Standard Sentinels
Document any use of non-standard sentinels in table below.

- `has-limits`     — in `ws_remedy`               : triggers `remedy_limits`.
- `has-phases`     — in `ws_fee_shifting_rule`    : triggers `fee_shifting_rule_phases`.
- `has-channel`    — in `ws_protected_disclosure` : triggers `disclosure_channel_context`.
- `has-ic-channel` — in `ws_protected_disclosure` : triggers `ic_channel_sequence_context`.

### Redirect Sentinels
Use when a companion field is already triggered by another mechanism, making `has-details` redundant.

- `see-details` — the `*_details` companion for this trigger is already active.
- `see-context` — the `*_context` companion for this trigger is already active.

---

## Hook Requirements

### General
Document required hook use in the inline definition of field where hook is needed.

- Derived fields: auto-fill on load and on save.
- Merged hidden fields (e.g., `_related_agencies`, `_precedent_ids`, `_parent_ids`): auto-fill on save.
- Derived select choices (e.g., `court` filtered by `jurisdiction`): filter on field load.

Always write unified hooks over duplicates. A single hook using `get_post_type()` is preferred over two
near-identical hooks per CPT. Reuse hooks wherever possible.

### Contradiction Guards
Document when hook is required to guard against invalid combinations of values in table below. Note if cross-field
monitoring is required. When cross-field monitoring also requires cross-tab monitoring add to detailed entry to 
[Cross-Tab Conditional and Monitoring] block.

- `fee_shifting_rules` — detect and flag contradictory terms.
- `sovereign_immunity_limits` — detect and flag contradictory terms.
- `causation_application` — enforce mutual exclusivity: `liability`, `damages`, and `both` must never appear
   together. `has-details` may accompany any single primary value.
- `mitigation-exception` — invalid without `mitigation-required` in `legal_recognitions`.
   * When `mitigation-required` is absent: remove `mitigation-exception`, clear `mitigation_exception_context`,
     and clear any sister fields.
- `contractual-waiver` — invalid when `civil_action_waiver_scope` is `anti`.
   * When `anti` is set: remove `contractual-waiver` from `legal_recognitions`, clear `contractual_waiver_context`,
     and clear any sister fields.
- `jury-trial` — invalid without `private-right-of-action` in `legal_recognitions`.
   * When `private-right-of-action` is absent: remove `jury-trial`, clear `jury_trial_context`, and clear any
     sister fields.
- `exhaustion-required` — invalid when `process_pathway_scope` is `direct-court`.
   * When `direct-court` is set: remove `exhaustion-required`, clear `exhaustion_required_context`, and clear any
     sister fields.
- `garcetti-exception` — invalid unless one `public-sector` child-slug is present in `employment_sectors`.
   * When no child-slug is present: remove `garcetti-exception` from `legal_recognitions`, clear
     `garcetti_exception_context`, and clear any sister fields.
- `has-phases` — invalid when selecting taxonomy term for repeater child [`fee_shifting_rule`].
   * When `fee_shifting_rule_phases` has already been conditionally revealed as repeater.

### Agency Filtering

- `primary_agency` — auto-fill with the first attached `ws-agency` post when empty. Filter choices to
   currently attached posts only. Instructions when empty: `"Attach one ws-agency to local or federal first"`;
   when non-empty: `"Override primary_agency with any currently attached local or federal agency"`.
- `local_agencies` — filter to jx-applicable, non-federal `ws-agency` posts. (Stub: future refinement
   intersecting `ws_process_type`, `ws_disclosure_targets` and `ws_protected_disclosure` taxonomies.)
- `federal_agencies` — filter to federal `ws-agency` posts only. (Stub: future refinement intersecting
  `ws_process_type`, `ws_disclosure_targets` and `ws_protected_disclosure` taxonomies.)

---

## Inline Field Descriptions

**Default field types** (by naming convention, unless stated otherwise):

- `has_*` · `is_*` · `*_is_*` → boolean
- `*_class` · `*_scope` · `*_status` · `*_rule` · `*_framework` · `*_weight` · `*_standard` → select
- `*_share`        — used to describe specified portion of a reward, e.g. "25-30%"
- `*_compare`      — used to describe mandated comparison (select: `gte`|`lte`|`gt`|`lt`|`eq`)
- `*_formula`      — used to describe mandated calculations
- `*_sanctions`    — used to describe specified unlawful conduct and associated penalties (repeater)
- `*_application`  — used to describe where or how a legal standard applies (multi-select)
- `*_direction`    — used to describe directional legal operation, e.g. "Federal Preempts State" (select)
- `*_bar`          — used for claim-blocking doctrines or procedural bars (select/bool)
- select → signals single-select; multi-select must be specified
- all others → freetext

**Default taxonomy field settings** (unless stated otherwise):

- Field type: taxonomy
- multi-select
- `load_terms`: 1
- `save_terms`: 1

**Conditional annotation phrasing** — four accepted forms:

- Taxonomy term present:  `conditional on slug in taxonomy_field`
- Any non-empty value:    `conditional on trigger_field is non-empty`
- Specific value in select field:        `conditional on trigger_field is trigger_value`
- Specific value in multi-select field:  `conditional on trigger_field includes trigger_value`
- Compound conditions: AND / OR / NOT (all-caps).

`*_details`, `*_limits`, `*_phases` and `*_companions` do not require annotation when the naming convention
makes the trigger unambiguous. All other conditional fields, `*_context` included, must declare their trigger
field and trigger value.

---

## Attached Workflow Groups

Four shared ACF groups attach to all four legal record types alongside the CPT-specific group.
Defined in `includes/acf/workflow/` — do not duplicate any of these fields in CPT-specific ACF files.

| Group key                      | `menu_order` | Tab label             | Fields added |
|---|---|---|---|
| `group_plain_english_metadata` | 85           | Plain-English         | `ws_has_plain_english`, `ws_plain_english_wysiwyg`, `ws_plain_english_reviewed`, 4 `ws_auto_` stamps |
| `group_auto_stamp_metadata`    | 90           | Authorship & Review   | `ws_auto_create_date`, `ws_auto_create_author`, `ws_auto_last_edited_date`, `ws_auto_last_edited_author` |
| `group_source_verify_metadata` | 95           | Source & Verification | `ws_auto_source_method`, `ws_auto_source_name`, `ws_auto_verified_by`, `ws_auto_verified_date`, `ws_verification_status`, `ws_needs_review` |
| `group_major_edit_metadata`    | 99           | Major Edit            | `ws_is_major_edit`, `ws_major_edit_description`, `ws_major_edit_update_type` |

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
        statute_url                         -> source_audit.url

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
                                                   {sovereign-immunity-waiver},
                                                   {anti-slapp-protection},
                                                   {catch-all-protection}
                                                   [35 terms omitted]

    statute_of_limitations. (The Timeline (Crucial))
        statute_of_limitations              -> sol_value
        limit_unit                          -> sol_unit
                                                   (days|months|years|none)
        limit_trigger                       -> sol_trigger

    burden_of_proof. (The Burden of Proof (Matching Scan))
        employee_standards                  -> employee_standards
                                                   {preponderance},
                                                   {clear-and-convincing}
                                                   [6 terms omitted]
        causation_standards                 -> causation_standards
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
- `citation`                       — (statute citation / precedent case / case name (shared slot); yes: there will
                                      be a `ws_jx_citation_citation`)
- `date`                           — (enacted / ruling / decision date (shared slot))
- `has_effective_date`             — (only when `effective_date` is specified and differs from `date`)
- `effective_date`
- `effective_year`                 — (derived from `effective_date` if present, `date` if not)
- `retro_date`                     — (sister field to `retro_context`)
- `retro_context`                  — (conditional on `retroactive-date` in `legal_recognitions`)
- `protection_scope`               — (single-select taxonomy: `ws_protection_scope`; internal use only)
- `general_description`            — (brief; reserve full summary for `plain_english_wysiwyg`)
- `has_attach_flag`                — (special-case; approved use of `has_*` bool; triggers display_order. Used
                                      together for attaching curated records to jx-summary at render)
- `display_order`                  — (conditional on `has_attach_flag` is true)

---

### Classification Tab

Fields ordered: legal_recognitions → activity standard → disclosure →
classes → sectors → targets → recognitions

- `legal_recognitions`             — (taxonomy: `ws_legal_recognition`; replaces all `*_recognized` booleans,
                                      includes other state bools; See [Slug-to-Companion Map] below.)
- `manager_rule_exclusion_context` — (conditional on `manager-rule-exclusion` in `legal_recognitions`)
- `public_concern_required_context`  — (conditional on `public-concern-required` in `legal_recognitions`)
- `bad_faith_exclusion_context`    — (conditional on `bad-faith-exclusion` in `legal_recognitions`)
- `anonymity_protection_context`   — (conditional on `anonymity-protection` in `legal_recognitions`)
- `malicious_reporting_sanctions`  — (sister field to `malicious_reporting_context`; repeater:
      ├── `sanction_conduct`             [select: `knowingly-false`|`reckless-disregard`|`bad-faith-motive`|
      │                                   `see-context`],
      └── `sanction_penalty`             [select: `civil-fine`|`remedy-forfeiture`|`attorney-fee-shift`|`felony`|
                                          `misdemeanor`|`see-context`])
- `malicious_reporting_context`    — (conditional on `malicious-reporting-sanctions` in `legal_recognitions`)
- `protected_action_standard`      — (sister field to `protected_action_context`; select: `per-se-protected`|
                                      `actual-violation`|`reasonable-belief`|`good-faith`)
- `reasonable_belief_scope`        — (sister field to `reasonable_belief_context`; select: `objective-only`|
                                      `subjective-only`|`dual-prong`|`see-context`)
- `reasonable_belief_context`      — (conditional on `protected_action_standard` is `reasonable-belief`)
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
- `garcetti_exception_context`     — (conditional on `garcetti-exception` in `legal_recognitions` AND any 
                                      `public-sector` child-slug in `employment_sectors`)
- `disclosure_targets`             — (taxonomy: `ws_disclosure_target`)
- `disclosure_channel_scope`       — (sister field of `disclosure_channel_context`; select: `any-channel`|
                                      `approved-channel-only`|`mandatory-internal-first`|`see-context`)
- `disclosure_format`              — (sister field of `disclosure_channel_context`; select: `written-only`|
                                      `oral-permitted`|`either`|`has-details`)
- `disclosure_format_details`
- `disclosure_channel_context`     — (conditional on `has-channel` in `protected_disclosures`)
- `ic_channel_sequence_context`    — (conditional on `has-ic-channel` in `protected_disclosures`; document
                                      mandatory Intel Community/National Security disclosure channel sequence)
- `disclosure_target_details`

---

### Statute of Limitations & Thresholds Tab

Fields ordered: core SOL → modifiers → exhaustion → pathways → thresholds → federal/state interaction

- `sol_value`
- `sol_unit`                       — (select: `days`|`weeks`|`months`|`years`)
- `sol_trigger`                    — (select: `accrual`|`constructive-discharge-accrual`|`discovery-rule`|
                                      `filing-of-complaint`|`conclusion-of-admin-process`|`see-context`)
- `sol_trigger_discovery_context`  — (conditional on `sol_trigger` is `discovery-rule`; detail discovery specifics)
- `sol_trigger_context`            — (conditional on `sol_trigger` is non-empty; detail trigger as legal, factual,
                                      or contextual as necessary)
- `is_sol_suspended_during_admin`  — (only true when SOL is explicitly tolled while pending administrative action)
- `has_sol_details`
- `sol_details`
- `sop_value`                      — (sister field to `statute_of_repose_context`)
- `sop_unit`                       — (sister field to `statute_of_repose_context`; select: `days`|`weeks`|
                                      `months`|`years`)
- `is_sop_tolling_available`       — (sister field to `statute_of_repose_context`; very unlikely; true only when
                                      explicitly stated; use `statute_of_repose_context` to describe context)
- `statute_of_repose_context`      — (conditional on `statute-of-repose` in `legal_recognitions`)
- `statutory_tolling_context`      — (conditional on `statutory-tolling` in `legal_recognitions`)
- `equitable_tolling_context`      — (conditional on `equitable-tolling` in `legal_recognitions`)
- `cba_preemption_context`         — (conditional on `cba-grievance-preemption` in `legal_recognitions`)
- `amended_claim_context`          — (conditional on `amended-claim` in `legal_recognitions`)
- `exhaustion_required_scope`      — (sister field to `exhaustion_required_context`; select:
                                      `jurisdictional`|`claims-processing`|`waivable`|`see-context`)
- `exhaustion_required_context`    — (conditional on `exhaustion-required` in `legal_recognitions`)
- `filing_notice_value`            — (sister field to `filing_notice_context`)
- `filing_notice_unit`             — (sister field to `filing_notice_context`; select: `days`|`weeks`|`months`|
                                      `years`)
- `filing_notice_target`           — (sister field to `filing_notice_context`; select: `employer`|`agency`|
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
- `cure_period_value`              — (sister field to `cure_period_details`; integer)
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

- `adverse_actions`                — (taxonomy: `ws_adverse_action`)
- `adverse_action_details`
- `adverse_action_scope`           — (select: `termination-only`|`material-adverse`|
                                      `broad-any-adverse-action`|`see-context`)
- `adverse_action_scope_context`   — (conditional on `adverse_action_scope` is non-empty)
- `preservation_deadline_value`    — (sister field to `evidence_preservation_context`)
- `preservation_deadline_unit`     — (sister field to `evidence_preservation_context`; select:
                                      `days`|`weeks`|`months`|`years`)
- `preservation_requirement_scope` — (sister field to `evidence_preservation_context`; select:
                                      `litigation-hold`|`statutory-hold`|`court-order`|
                                      `agency-request`|`see-context`)
- `evidence_preservation_context`  — (conditional on `evidence-preservation` in `legal_recognitions`)
- `constructive_discharge_standard`   — (sister field to `constructive_discharge_context`; select:
                                         `objective-intolerability`|`intent-required`|`dual-prong`|`see-context`)
- `constructive_discharge_context`    — (conditional on `constructive-discharge` in `adverse_actions`)
- `is_evidence_collection_protected`
- `anticipatory_retaliation_context`  — (conditional on `anticipatory-retaliation` in `adverse_actions`)
- `cats_paw_liability_context`        — (conditional on `cats-paw-liability` in `legal_recognitions`)
- `is_cats_paw_liability_extended`    — (conditional on any `associates-of-whistleblower`child-slug in
                                         `protected_classes` AND `cats-paw-liability` in `legal_recognitions`)
- `third_party_retaliation_context`   — (conditional on `third-party-retaliation` in `legal_recognitions`)
- `criminal_sanctions`                — (sister field to `criminal_sanctions_context`; repeater:
      ├── `sanction_conduct`                [select: `retaliation`|`disclosure`|`false-report`|`obstruction`|
      │                                      `see-context`],
      └── `sanction_level`                  [select: `misdemeanor`|`felony`|`see-context`])
- `criminal_sanctions_context`        — (conditional on `criminal-sanctions` in `legal_recognitions`)

---

### Processes & Remedies Tab

Fields ordered: process → pathway → fee shifting → remedies → reinstatement

- `process_types`                  — (taxonomy: `ws_process_type`)
- `primary_agency`                 — (auto-fill by hook when first post_type[`ws-agency`] added to local or federal)
- `local_agencies`                 — (multi-select: post_type[`ws-agency`] filtered by jx, common `*disclosure*`
                                      and `process_types`)
- `process_pathway_scope`          — (sister field to `process_pathway_context`; select: `agency-first-mandatory`|
                                      `direct-court`|`either`|`hybrid-right-to-sue-on-inaction`|`see-context`)
- `is_agency_inaction_trigger`     — (conditional on `process_pathway_scope` is `hybrid-right-to-sue-on-inaction`)
- `process_pathway_context`        — (conditional on `process-pathway` in `legal_recognitions`)
- `enforcement_sequence`           — (priority of enforcement agencies, with any enforcement requirements)
- `private_roa_context`            — (conditional on `private-right-of-action` in `legal_recognitions`)
- `jury_trial_scope`               — (sister field to `jury_trial_context`; select: `all-claims`|
                                      `damages-only`|`liability-only`|`see-context`)
- `jury_trial_context`             — (conditional on `private-right-of-action` AND `jury-trial` in
                                      `legal_recognitions`)
- `fee_shifting_rules`             — (taxonomy: `ws_fee_shifting_rule`)
- `fee_shifting_rule_phases`       — (conditional on `fee_shifting_rules` includes `has-phases`; repeater:
      ├── `phase`                        [select: `administrative`|`investigative`|`litigation`|`appeal`|
      │                                   `has-details`],
      ├── `fee_shifting_rule`            [single-select taxonomy: `ws_fee_shifting_rule`; hook excludes 
      │                                   `has-phases` sentinel],
      └── `phase_details`                [conditional on `phase` is `has-details`])
- `fee_shifting_asymmetry`         — (conditional on `fee_shifting_rules` includes `has-asymmetry`; select:
                                      `one-way-plaintiff`|`one-way-defendant-frivolous`|`two-way`|`american-rule`|
                                      `has-details`)
- `fee_shifting_asymmetry_details`
- `remedies`                       — (taxonomy: `ws_remedy`)
- `remedy_limits`                  — (conditional on `remedies` includes `has-limits`)
- `remedy_caps`                    — (sister field to `remedy_limits`; repeater:
       ├── `cap_remedy`                  [select: `emotional-distress`|`punitive`|`compensatory`|`aggregate`|
       │                                  `employer-size-tiered`],
       ├── `employer_tier`               [conditional on `cap_remedy` is `employer-size-tiered`, e.g. "15-14", "100+"],
       ├── `max_amount`                  [e.g., "300000", "uncapped"],
       ├── `applies_to`                  [select: `single-claim`|`per-plaintiff`|`per-incident`|`aggregate-action`|
       │                                  `has-details`],
       └── `application_details`         [conditional on `applies_to` is `has-details`])
- `remedy_caps_context`            — (sister field to `remedy_limits`)
- `has_blacklisting_extended`      — (conditional on`blacklisting` in `adverse_actions`; true when future-employers
                                      are also specified)
- `blacklisting_extended_details`
- `remedy_details`
- `remedy_liquidated_multiplier`   — (conditional on `liquidated-damages` in `remedies`; select:
                                      `double`|`treble`|`2x-back-pay`|`2x-wages-lost`|`statutory-formula`|
                                      `statutory-daily-fine`|`up-to-double`|`up-to-treble`|`has-details`)
- `remedy_liquidated_formula`      — (conditional on `remedy_liquidated_multiplier` is `statutory-formula`)
- `remedy_liquidated_details`      — (conditional on `remedy_liquidated_multiplier` is `has-details`)
- `mitigation_required_scope`      — (sister field to `mitigation_required_context`;select: `yes-statutory`|
                                      `yes-common-law`|`has-details`, hook for invalid combos)
- `mitigation_required_context`    — (conditional on `mitigation-required` is `legal_recognitions`)
- `mitigation_exception_context`   — (conditional on `mitigation-required` is `legal_recognitions` AND
                                      `mitigation-exception` is `legal_recognitions`)
- `interest_provision_scope`       — (sister field to`interest_provision_context`; select: `pre-judgment-statutory`|
                                      `post-judgment`|`both`|`discretionary`|`see-context`)
- `interest_provision_context`     — (conditional on `equitable-interest-award` in `legal_recognitions`)
- `reinstatement_standard`         — (sister field to `preliminary_reinstatement_context`; select: `mandatory`|
                                      `discretionary`|`has-details`)
- `reinstatement_standard_details`
- `preliminary_reinstatement_scope`     — (sister field to `preliminary_reinstatement_context`; select:
                                           `admin-phase`|`full-pendency`|`both`)
- `preliminary_reinstatement_context`   — (conditional on `preliminary-reinstatement` in `legal_recognitions`)
- `mixed_motive_remedy_context`    — (conditional on `burden_shifting_framework` is `mixed-motive`;
                                      see [Cross-Tab Conditional and Monitoring] below)

---

### Burden Of Proof Tab

Fields ordered: framework → employee standards → causation → employer defenses →
rebuttable presumption → temporal presumption → detail overflow

- `has_burden_shifting`
- `burden_shifting_framework`      — (sister field to `burden_shifting_details`; select: `mcdonnell-douglas`|
                                      `motivating-factor`|`but-for`|`mixed-motive`|`see-context`;
                                      see [Cross-Tab Conditional and Monitoring] below)
- `burden_shifting_context`        — (conditional on `burden_shifting_framework` is non-empty)
- `burden_shifting_details`
- `same_decision_standard`         — (sister field to `same_decision_context`; select: `preponderance`|
                                      `clear-and-convincing`|`not-available`|`see-context`)
- `same_decision_context`          — (conditional on `employer_defenses` includes `same-decision-defense`)
- `causal_nexus_statutory_text`    — (conditional on `causation_standards` is non-empty; verbatim or near-verbatim
                                      statutory language describing the causal link standard)
- `employee_standards`             — (taxonomy: `ws_employee_standard`; evidentiary burden only)
- `employee_standard_details`
- `causation_standards`            — (taxonomy: `ws_causation_standard`; causal link standard)
- `causation_application`          — (sister field to `causation_standard_context`; multi-select: `liability`|
                                      `damages`|`both`|`has-details`; combo limited to `has-details` plus one other)
- `causation_application_details`
- `causation_standard_context`     — (conditional on `causation_standards` is non-empty)
- `causation_dual_standard_context`  — (conditional on `causation-dual-standard` in `legal_recognitions`)
- `employer_knowledge_scope`       — (sister field to `employer_knowledge_context`; select:
                                      `actual-knowledge`|`constructive-knowledge`|`inferred-knowledge`|
                                      `imputed-knowledge`|`has-details`)
- `employer_knowledge_scope_details`
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
- `temporal_proximity_unit`        — (sister field to `temporal_proximity_context`; select: `days`|`weeks`|
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
- `has_first_to_file_bar`          — (sister field to `qui_tam_share_context`)
- `first_to_file_details`
- `has_public_disclosure_bar`      — (sister field to `qui_tam_share_context` AND conditional on
                                      `bounty-qui-tam-award` in `remedies`)
- `public_disclosure_bar_details`

---

### Waiver & Scope Tab

Fields ordered: contractual → recognitions → immunity → defendants.

- `civil_action_waiver_scope`      — (select: `prohibited`|`permitted-individual-only`|
                                      `permitted-collective`|`anti`|`see-context`)
- `civil_action_waiver_context`    — (conditional on `civil_action_waiver_scope` is non-empty)
- `contractual_waiver_scope`       — (sister field to `contractual_waiver_context`; select: `void`|
                                      `limited`|`enforceable`|`void-public-policy`|`void-as-to-whistleblowing`|
                                      `enforceable-with-exceptions`|`see-context`)
- `contractual_waiver_context`     — (conditional on `civil_action_waiver_scope` NOT `anti` AND
                                      `contractual-waiver` in `legal_recognitions`)
- `waiver_of_collateral_claims_context`  — (conditional on `waiver-of-collateral-claims` in `legal_recognitions`)
- `class_action_waiver_context`    — (conditional on `class-action-waiver` in `legal_recognitions`)
- `proper_defendants`              — (multi-select: `employer-entity-only`|`individual-supervisors`|
                                      `government-agency-only`|`contractors-included`|`successor-employer`|
                                      `joint-employer`|`staffing-agency`|`scope-of-employment-required`|
                                      `has-details`)
- `proper_defendant_details`
- `joint_employer_context`         — (conditional on `proper_defendants` includes `joint-employer` OR
                                      `staffing-agency`)
- `individual_liability_scope`     — (sister of `individual_liability_context`; multi-select: `supervisor`|
                                      `coworker`|`officer-director`|`any-individual`|`has-details`)
- `individual_liability_context`   — (conditional on `individual-liability` in `legal_recognitions`)
- `sovereign_immunity_statuses`    — (sister field of `sovereign_immunity_context`; taxonomy:
                                      `ws_sovereign_immunity_status`)
- `sovereign_immunity_scope`       — (sister field to `sovereign_immunity_status_details`; select:
                                      `state-only`|`instrumentalities-included`|`political-subdivisions-included`|
                                      `all`|`see-details`)
- `sovereign_immunity_waiver`      — (sister field to `sovereign_immunity_status_details`; select:
                                      `explicit-waiver`|`implied-waiver`|`none`|`not-applicable`)
- `sovereign_immunity_status_details`
- `sovereign_immunity_context`     — (conditional on `sovereign-immunity-status` in `legal_recognitions`)
- `nda_limits_context`             — (conditional on `nda-limitations` in `legal_recognitions`)
- `anti_gag_provision_context`     — (conditional on `anti-gag-provision` in `legal_recognitions`)
- `no_retaliatory_evidence_context`  — (conditional on `no-retaliatory-evidence` in `legal_recognitions`)
- `stay_of_discipline_context`     — (conditional on `stay-of-disciplinary-action` in `legal_recognitions`)
- `anti_slapp_protection_scope`    — (sister field to `anti_slapp_protection_context`; select:
                                      `motion-to-strike`|`discovery-stay`|`fee-shift-on-motion`|
                                      `full-procedural`|`see-context`)
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

### Source / Audit Tab

Fields ordered: reviewed → source url → authority

- `last_reviewed_date`             — (manually updated when record reviewed for accuracy)
- `url`                            — (url field; statute / precedent / case law URL (shared slot))
- `url_is_pdf`                     — (when true, renderer adds a "(PDF)" text after the url)
- `authority_reference`            — (holds the official legislative history citation or regulatory citation
                                      (CFR, Federal Register, etc.))

---

### Hidden Fields (no tab; prefixed with underscore)

Fields ordered: id → derived

- `_id`                            — (generated by ingest tool or matrix seeder)
- `_disclosure_target_class`       — (derived from `disclosure_targets`; auto-fill by hook on save; select:
                                      `internal`|`external`|`both`)

---

## Specialized Fields By Legal Record Type

---

### Substantive-Record Common Fields (statute + common_law)

Substantive records carry most of the fields defining whistleblower protections.
The notable precedent fields capture modifications to the definitions:
- `court`                 — federal or local court that made the ruling
- `scope`                 — the result of the ruling (`favorable`|`adverse`|`neutral`) on whistleblower protections
- `binding_scope`         — effective strength of the ruling
- `affected_jx`           — affected jurisdictions (when more than the single preceding court's jx is affected;
                            e.g. California Supreme Court (jx=['ca']) ruling only affects California (jx=['ca']),
                            U.S. Court of Appeals for the Seventh Circuit (jx=['us']) affects jx => ['il','in','wi'])
- `extended_taxonomies`   — affected taxonomy when ruling is favorable
- `suppressed_taxonomies` — affected taxonomy when ruling is adverse
- `_parent_ids`: `statute_ids`, `comlaw_ids` — legal record or records affected by ruling
These fields do not appear on substantive records.

#### Processes & Remedies Tab (insert after `jury_trial_context`)

- `review_standard_scope`          — (sister field to `review_standard_context`; select: `de-novo`|
                                      `substantial-evidence`|`arbitrary-capricious`|`abuse-of-discretion`|
                                      `has-details`)
- `review_standard_details`
- `review_standard_context`        — (conditional on `civil-review-standard` in `legal_recognitions`)

#### Processes & Remedies Tab (insert after `anticipatory_retaliation_context`)
- `election_of_remedies_rules`     — (multi-select: `administrative-bars-civil`|`state-bars-federal`|
                                      `remedy-exclusivity`|`first-filed-controls`|`no-election-required`|
                                      `see-context`)
- `election_of_remedies_context`   — (conditional on `election_of_remedies_rules` is non-empty)

#### Relationships Tab

- `citation_ids`
- `construction_ids`

#### Hidden Fields

- `_precedent_ids`                 — (merged array of `citation_ids` and `construction_ids`; auto-fill by hook
                                      on save)

---

### Statute-Specific

#### Processes & Remedies Tab (insert after `local_agencies`)

- `federal_agencies`               — (multi-select: post_type[`ws-agency`] filtered to jx='us', common
                                      `*disclosure*` and `process_types`)

#### Hidden Fields

- `_primary_agency_is_fed`         — (derived from `primary_agency` jx; auto-fill by hook on save)
- `_related_agencies`              — (merged array of `local_agencies` and `federal_agencies`; auto-fill by hook
                                      on save)

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
- `doctrine_*`, `public_policy_*`, `recognition_*`, etc. — Common-law-specific fields that have no precedent equivalent.
- `_precedent_ids`              — Precedent-Records have `_parent_ids`: `statute_ids`, `comlaw_ids` instead.
These fields do not appear on precedent-records.

#### Identity Tab

- `status`                         — (select: `published`|`unpublished`|`memorandum`|`vacated`)
- `binding_scope`                  — (select: `binding`|`persuasive`|`mixed`|`distinguished`|`overruled`)
- `court`                          — (select: populate choices by hook on load/fill filter by jx)
- `court_details`                  — (conditional on `court` is `has-details`)
- `court_jx`                       — (sister field to `court_details`; taxonomy: `WS_JURISDICTION_TAXONOMY`,
                                      `load_terms` => 1, `save_terms` => 0)
- `court_is_fed`                   — (derived from `court` `ws_jx_codes`; manually set when `court`
                                      is `has-details`)

#### Effective Date Tab (insert after `effective_year`)

- `mandate_date`

#### Classification Tab (insert after `legal_recognitions`)

- `scope`                          — (select: `favorable`|`adverse`|`neutral`)
- `extended_taxonomies`            — (conditional on `scope` is `favorable`; repeater:
      ├── `taxonomy`                     [select: taxonomy slug],
      └── `term`                         [select: taxonomy term])
- `suppressed_taxonomies`          — (conditional on `scope` is `adverse`; repeater:
      ├── `taxonomy`                     [select: taxonomy slug],
      └── `term`                         [select: taxonomy term])
- `has_affected_jx`                — (derived from `court` `ws_jx_codes`; manually set false when single jx
                                      is same as precedent `jurisdiction`; manually set if true when
                                      `court`-`has-details` and covers multiple jx)
- `affected_jx`                    — (conditional on `has_affected_jx`; derived from `court` `ws_jx_codes`;
                                      manually set taxonomy: `WS_JURISDICTION_TAXONOMY`, `load_terms` => 1,
                                      `save_terms` => 0, once `has_affected_jx` is true to apply affected jx
                                       by `court`-`has-details` `court_jx`)

#### Relationships Tab

- `statute_ids`
- `comlaw_ids`
- `parent_weight`                  — (select: `primary`|`secondary`|`distinguishing-only`)
- `has_negative_treatment`
- `negative_treatment_class`       — (sister field to `negative_treatment_details`; select: `overruled`|
                                      `distinguished`|`limited`|`questioned`|`superseded-by-statute`|`has-details`)
- `negative_treatment_class_details`
- `negative_treatment_details`

#### Source / Audit Tab (insert after `authority_reference`)

- `authority_source`               — (select: `constitutional`|`legislative`|`judicial`|`regulatory`|
                                      `executive`|`has-details`)
- `authority_source_details`
- `review_standard_scope`          — (select: `de-novo`|`substantial-evidence`|`arbitrary-capricious`|
                                      `abuse-of-discretion`|`has-details`)
- `review_standard_scope_details`
- `review_standard_details`

#### Processes & Remedies Tab (insert after `local_agencies`)

- `federal_agencies`               — (multi-select: post_type[`ws-agency`] filtered to jx='us', common
                                      `*disclosure*` and `process_types`)

#### Hidden Fields

- `_primary_agency_is_fed`         — (derived from `primary_agency` jx; auto-fill by hook on save)
- `_related_agencies`              — (merged array of `local_agencies` and `federal_agencies`; auto-fill by hook
                                      on save)
- `_parent_ids`                    — (merged array of `statute_ids` and `comlaw_ids`; auto-fill by hook on save)

---

### Citation-Specific

#### Identity Tab (insert after `citation`)

- `types`                          — (multi-select: `case-law`|`statute`|`regulatory`|`secondary`)
- `type_context`                   — (conditional on `types` is non-empty; provide context for `types` chosen)

---

### Construction-Specific

#### Identity Tab

- `type`                           — (select: `case-law`|`statute`|`regulatory`|`secondary`)
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

- `fee_shiftings`                            → `fee_shifting_rules`
- `ws_fee_shifting`                          → `ws_fee_shifting_rule`          (taxonomy table)
- `has_limit_ambiguous`                      → `has_sol_details`
- `limit_details`                            → `sol_details`
- `has_tolling_details`                      →  split into `statutory-tolling` and `equitable-tolling` true when present in `legal_recognitions`
- `tolling_details`                          →  split into `statutory_tolling_context` and `equitable_tolling_context`
- `has_exhaustion_required`                  → `exhaustion-required`            in `legal_recognitions`
- `exhaustion_details`                       → `exhaustion_required_context`
- `exhaustion_is_jurisdictional` (bool)      → `exhaustion_required_scope`     (single select)
- `rebuttable_presumption`                   → `rebuttable_presumption_details`
- `has_statutory_preclusion`                 → `statutory-preclusion`           in `legal_recognitions`
- `doctrine_basis_wysiwyg`                   → `doctrine_basis`                (never was wysiwyg)
- `recognition_status_wysiwyg`               → `recognition_status`            (select) — (never was wysiwyg) + `recognition_status_details` (textarea)
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
- `adverse_action_scope` (textarea)          → `adverse_action_scope`           (select) + `adverse_action_scope_context` (freetext)
- `doctrine_id`                              →  removed (visible dedupe IDs deemed unnecessary)
- `bop_flag`                                 →  removed (used by researchers only, never meant for ACF meta)
- `statute_id` + `comlaw_id` — singular      →  pluralized to support (rare-but-possible) multi-values
- `disclosure_types`                         → `protected_disclosures`
- `ws_disclosure_type` (taxonomy)            → `ws_protected_disclosure`        (taxonomy) 
- `has_preemption` + `preemption_details`    →  removed (preemption block replaced with `federal_state_interaction` block)
- `preemption_direction`                     → `federal_state_interaction`
- `sovereign_immunity_limits`                → `sovereign_immunity_statuses`    (taxonomy)
---

## Relationship Direction Contract (For Sync)

- Parent-bearing legal records: `citation`, `construction`.
- Child-bearing  legal records: `statute`, `common_law`.

---

## Cross-Tab Conditional and Monitoring

### mixed-motive → mixed_motive_remedy_context

When `burden_shifting_framework` (Burden Of Proof tab) is `mixed-motive`,
the field `mixed_motive_remedy_context` (Enforcement tab) becomes relevant.
ACF conditional logic cannot surface this cross-tab dependency natively.

Implementation: register an `acf/save_post` hook (or `admin_notices` hooked to
`current_screen`) that detects `mixed-motive` in `burden_shifting_framework` and
emits a dismissible admin notice directing the editor to the Enforcement tab:

> "Mixed-motive framework selected — please complete the 'Mixed Motive Remedy
>  Context' field on the Enforcement tab."

Notice should be informative (not alarmist) and display on the edit screen for all four legal record CPTs.
Dismiss state does not need to persist — the notice should reappear on each save as long as `mixed-motive`
is present and `mixed_motive_remedy_context` is empty.

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

Conditional-Companion fields `*_context` noted with ` → ` are triggered by slug presence in `legal_recognitions`.
Sister fields noted by ` + ` inherit the conditional behavior, but are defined by the sibling.
Sister fields cannot appear without the triggered sibling being revealed.
Sister fields can (and usually do) appear before sibling.
Sister fields can have additional conditionals AND / OR / NOT.

```
// ── Identity Tab ─────────────────────────────────────────────────────────────
'retroactive-date'                    → 'retro_context'                         + 'retro_date'                       // Specified

// ── Classification Tab ───────────────────────────────────────────────────────
'manager-rule-exclusion'              → 'manager_rule_exclusion_context'                                             // Applies
'public-concern-required'             → 'public_concern_required_context'                                                     // Applies
'bad-faith-exclusion'                 → 'bad_faith_exclusion_context'                                                // Applies
'malicious-reporting-sanctions'       → 'malicious_reporting_context'           + 'malicious_reporting_sanctions'    // Applies
'anonymity-protection'                → 'anonymity_protection_context'                                               // Recognized
'protected-action'                    → 'protected_action_context'              + 'protected_actions'
                                                                                + 'protected_action_standard'
                                                                                + 'protected_action_source'          // Specified
'excluded-class'                      → 'excluded_class_context'                + 'excluded_classes'                 // Specified
'garcetti-exception'                  → 'garcetti_exception_context'                                                 // Applies

// ── Statute of Limitations & Thresholds Tab ──────────────────────────────────
'statute-of-repose'                   → 'statute_of_repose_context'             + 'sop_value' + 'sop_unit'
                                                                                + 'is_sop_tolling_available'         // Specified
'statutory-tolling'                   → 'statutory_tolling_context'                                                  // Specified
'equitable-tolling'                   → 'equitable_tolling_context'                                                  // Recognized
'cba-grievance-preemption'            → 'cba_preemption_context'                                                     // Applies
'amended-claim'                       → 'amended_claim_context'                                                      // Recognized
'exhaustion-required'                 → 'exhaustion_required_context'           + 'exhaustion_required_class'        // Required
'pre-filing-notice'                   → 'filing_notice_context'                 + 'filing_notice_target' + 'filing_notice_value'
                                                                                + 'filing_notice_unit'               // Required
'statutory-preclusion'                → 'statutory_preclusion_context'                                               // Applies

// ── Retaliation Tab ──────────────────────────────────────────────────────────
'cats-paw-liability'                  → 'cats_paw_liability_context'            + 'is_cats_paw_liability_extended'   // Recognized
'third-party-retaliation'             → 'third_party_retaliation_context'                                            // Prohibited
'criminal-sanctions'                  → 'criminal_sanctions_context'            + 'criminal_sanctions'               // Specified

// ── Processes & Remedies Tab ──────────────────────────────────────────────────────────
'private-right-of-action'             → 'private_roa_context'                                                        // Available
'jury-trial'                          → 'jury_trial_context'                    + 'jury_trial_scope'                 // Available
'preliminary-reinstatement'           → 'preliminary_reinstatement_context'     + 'preliminary_reinstatement_standard' + 'reinstatement_standard_details'
                                                                                + 'preliminary_reinstatement_scope'  // Available

// ── Burden of Proof Tab ──────────────────────────────────────────────────────
'employer-knowledge'                  → 'employer_knowledge_context'            + 'employer_knowledge_scope'         // Required
'temporal-proximity-sufficient'       → 'temporal_proximity_context'            + 'temporal_proximity_value'
                                                                                + 'temporal_proximity_unit'          // Recognized

// ── Waiver & Scope Tab ───────────────────────────────────────────────────────
'contractual-waiver'                  → 'contractual_waiver_context'            + 'contractual_waiver_scope'         // Recognized
'waiver-of-collateral-claims'         → 'waiver_of_collateral_claims_context'                                        // Applies
'class-action-waiver'                 → 'class_action_waiver_context'                                                // Recognized
'nda-limitations'                     → 'nda_limits_context'                                                         // Recognized
'anti-gag-provision'                  → 'anti_gag_provision_context'                                                 // Recognized
'no-retaliatory-evidence'             → 'no_retaliatory_evidence_context'                                            // Barred
'stay-of-disciplinary-action'         → 'stay_of_discipline_context'                                                 // Available
'anti-slapp-protection'               → 'anti_slapp_protection_context'         + 'anti_slapp_protection_scope'      // Applies
'discovery-protection'                → 'discovery_protection_context'                                               // Applies
'confidential-settlement-restriction' → 'settlement_restriction_context'        + 'settlement_restriction_scope'     // Applies
'individual-liability'                → 'individual_liability_context'          + 'individual_liability_scope'       // Available
'successor-liability'                 → 'successor_liability_context'                                                // Recognized
'extraterritorial-coverage'           → 'extraterritorial_context'                                                   // Recognized

// ── Without Context (no tab) ────────────────────────────────────────────────
'statutory-nexus-diverges-from-common-law'  — (no companion needed)   // Applies
'catch-all-protection'                      — (no companion needed)   // Present
'internal-only-disclosure'                  — (no companion needed)   // Sufficient
'trade-secret-immunity'                     — (no companion needed)   // Recognized
'continuing-violation-doctrine'             — (no companion needed)   // Recognized
'prospective-whistleblower-protection'      — (no companion needed)   // Available
'sovereign-immunity-waiver'                 — (no companion needed)   // Recognized
'class-action'                              — (no companion needed)   // Permitted
'official-duties-carveout'                  — (no companion needed)   // Applies

```

---

## Taxonomy Reference

### New Tables (v2.3)

- `ws_sovereign_immunity_status` — flat. [DONE]

### Existing Tables: Split Taxonomy Note  [DONE]

`ws_employee_standard` was split to create `ws_causation_standard`; sibling taxonomies covering distinct concepts.

- `ws_employee_standard` — evidentiary weight: how much proof, what quality.
- `ws_causation_standard` — causal logic: the relationship between disclosure and adverse action.

The same underlying concept (e.g. contributing factor) may appear in both tables under different framing —
intentional and legally correct.

### Existing Tables: Term Additions (v2.3) [DONE]

### NEVER ###
### DO THIS AND I WILL SHOOT YOUR DOG ### All additions to existing tables require a `seed_version` bump on the affected taxonomy in `register-taxonomies.php`.
### NEVER ###

**`ws_legal_recognition`** — append in logical tab order, before the Without Context block: [DONE]

| Slug | Label | Companion | Note |
|---|---|---|---|
| `garcetti-exception` | Garcetti / Official-Duties Exclusion Applies | → `garcetti_exception_context` | Applies |
| `official-duties-carveout` | Official Duties Carveout (Lane v. Franks Exception) | (no companion) | Applies |
| `equitable-interest-award` | Equitable Interest Provision Available | → `interest_provision_context` | Available |
| `mitigation-exception` | Mitigation Exception Recognized | → `mitigation_exception_context` | Recognized |
| `causation-dual-standard` | Causation Dual Standard (Liability vs Damages Differ) | → `causation_dual_standard_context` | Applies
| `statutory-nexus-diverges-from-common-law` | Statutory Nexus Overrides Circuit Common Law | (no companion) | Applies |

**`ws_remedy`** — append before `has-limits` sentinel: [DONE]

| Slug | Label |
|---|---|
| `non-economic-cap-separate` | Non-Economic Damages Capped Separately |
| `punitive-damages-capped-separately` | Punitive Damages Capped Separately |

***`ws_process_type`** — append after existing terms. [DONE]

| Slug | Label |
|---|---|
| `hybrid-admin-civil-path` | Hybrid Admin → Civil Pathway |
| `direct-filing-permitted` | Direct Filing Permitted (No Exhaustion) |

**`ws_protected_class`** [DONE]

| Slug | Label | Parent |
|---|---|---|
| `victim-domestic-violence-sexual-assault` | Victim of Domestic/Sexual Violence | `special-status` |
| `domestic-work-employee` | Domestic Work Employee | `private-sector` |

---

## Notes

- This draft treats the statute set as baseline for broad legal parity, then adds per-type deltas.
- `ws_legal_recognition` is a presence/absence signal table, not a classification table.
- Fields marked (no companion needed) in the [Slug-to-Companion Map] are captured exclusively via
  `ws_legal_recognition` taxonomy. No separate ACF field is registered for them.
- All conditional logic uses the four accepted annotation forms. Sister fields inherit visibility but
  are not independently conditional.
- Zero live data implications. This spec is purely structural and ready for PHP field generation or
  ingest schema mapping.

