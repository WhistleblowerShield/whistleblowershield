# Legal Record ACF Canonical Field Spec (v3.0)

**Purpose:** Unified, prefix-free field set for all four legal record types (`statute`, `common_law`, `citation`,
`construction`). This document declares the fields. Naming rules, hook patterns, and the slug-to-companion map
live in companion documents.

**Companion documents.**

- `ws-acf-field-guidance-v1.0.md` — naming rules, companion-suffix doctrine, sentinels, conditional annotation,
  inline description discipline, default field types.
- `ws-acf-hook-guidance-v1.0.md` — hook philosophy, validation patterns, helper definitions, generic hook
  examples.
- `legal-record-acf-hooks-v1.0.md` — slug-to-companion map for `ws_legal_recognition`, cross-field hook tables,
  legal-record-specific hook examples.

**Reading guide.** Field declarations below assume the companion documents are in force. Inline annotations
clarify ACF build behavior (taxonomy, select choices, conditional logic, sister relationships) per the
[Annotation Discipline] rule in the field-guidance doc. Annotations do not provide editor data-entry guidance —
that lives in ACF instruction text on the field itself.

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

Maps phase-1 reconciler research JSON into the canonical statute ACF model. Use the JSON key when `legacy_key` is
present in `acf-jx-statutes.php`; otherwise use the canonical ACF `name`.

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
        employee_standard                   -> employee_standard
                                                   {preponderance},
                                                   {clear-and-convincing}
                                                   [6 terms omitted]
        causation_standard                  -> causation_standard
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

These normalized canonical fields exist in every legal-record ACF. Field order reflects logical editorial
workflow within each tab.

---

### Identity Tab

Fields ordered: identification → related dates → scope → curated.

- `jurisdiction`                   — (single-select taxonomy: `ws_jurisdiction`)
- `official_name`
- `common_name`
- `citation`
- `date`
- `has_effective_date`
- `effective_date`
- `effective_year`                 — (hook: butchers)
- `retro_date`                     — (Sister to `retro_context`; hook: required)
- `retro_context`                  — (conditional on `retroactive-date` in `legal_recognitions`)
- `protection_scope`               — (select: `disclosure`|`retaliation`|`both`; replaces former `ws_protection_scope` taxonomy)
- `general_description`
- `has_attach_flag`                — (special-case approved `has_*` bool; triggers `display_order`)
- `display_order`                  — (conditional on `has_attach_flag` is true)
- `last_reviewed_date`             — (must be last in Identity Tab after any insertions)

---

### Classification Tab

Fields ordered: legal_recognitions → activity standard → disclosure → classes → sectors → targets → recognitions.

- `legal_recognitions`             — (taxonomy: `ws_legal_recognition`; hook: impacts, excludes)
- `manager_rule_exclusion_context` — (conditional on `manager-rule-exclusion` in `legal_recognitions`; hook: required)
- `public_concern_required_context` — (conditional on `public-concern-required` in `legal_recognitions` AND
                                       `public-sector` in `employment_sectors`; hook: required)
- `manner_of_opposition_context`   — (conditional on `manner-of-opposition-standard` in `legal_recognitions`; hook: required)
- `bad_faith_exclusion_context`    — (conditional on `bad-faith-exclusion` in `legal_recognitions`; hook: required)
- `anonymity_protection_context`   — (conditional on `anonymity-protection` in `legal_recognitions`; hook: required)
- `malicious_reporting_sanctions`  — (Sister to `malicious_reporting_context`; repeater:
      ├── `conduct_sanctioned`           [select: `knowingly-false`|`reckless-disregard`|`bad-faith-motive`|
      │                                   `see-context`],
      ├── `sanction_penalty`             [select: `civil-fine`|`remedy-forfeiture`|`attorney-fee-shift`|
      │                                   `felony`|`misdemeanor`|`see-context`],
      └── `conduct_context`              [conditional on `conduct_sanctioned` is non-empty]; hook: required)
- `malicious_reporting_context`    — (conditional on `malicious-reporting-sanctions` in `legal_recognitions`)
- `protected_action_standards`     — (Sister to `protected_action_context`; multi-select: `per-se-protected`|
                                      `actual-violation`|`reasonable-belief`|`good-faith`)
- `reasonable_belief_scope`        — (Sister to `protected_action_context`; AND conditional on
                                      `protected_action_standards` includes `reasonable-belief`; select:
                                      `objective-only`|`subjective-only`|`dual-prong`|`see-context`)
- `protected_action_sources`       — (Sister to `protected_action_context`; multi-select: `constitutional`|
                                      `statutory`|`judicial`|`regulatory`|`executive`|`see-context`)
- `protected_actions`              — (Sister to `protected_action_context`; taxonomy: `ws_protected_action`; hook: required)
- `protected_action_context`       — (conditional on `protected-action` in `legal_recognitions`)
- `protected_disclosures`          — (taxonomy: `ws_protected_disclosure`)
- `protected_classes`              — (taxonomy: `ws_protected_class`; hook: impacts, prerequisite, excludes)
- `former_employee_gloss`          — (conditional on `former-employee` in `protected_classes`)
- `protected_class_details`
- `excluded_classes`               — (Sister to `excluded_class_context`; taxonomy: `ws_excluded_class`; hook: required)
- `excluded_class_context`         — (conditional on `excluded-class` in `legal_recognitions`; hook: required)
- `excluded_class_details`
- `employment_sectors`             — (taxonomy: `ws_employment_sector`; hook: impacts)
- `garcetti_exception_context`     — (conditional on `garcetti-exception` in `legal_recognitions` AND
                                      `public-sector` in `employment_sectors`; hook: required)
- `disclosure_targets`             — (taxonomy: `ws_disclosure_target`)
- `disclosure_target_details`
- `disclosure_channel_scope`       — (Sister to `disclosure_channel_context`; select: `any-channel`|
                                      `approved-channel-only`|`mandatory-internal-first`|`see-context`; hook: required)
- `disclosure_format`              — (Sister to `disclosure_channel_context`; select: `written-only`|
                                      `oral-permitted`|`either`|`has-details`)
- `disclosure_format_details`
- `disclosure_channel_context`     — (conditional on `disclosure-channel-defined` in `legal_recognitions`)
- `ic_channel_sequence_gloss`      — (conditional on `has-ic-channel` in `protected_disclosures`)

---

### Statute of Limitations & Thresholds Tab

Fields ordered: core SOL → modifiers → exhaustion → pathways → thresholds → federal/state interaction.

- `sol_value`
- `sol_unit`
- `sol_triggers`                   — (multi-select: `accrual`|`constructive-discharge-accrual`|`discovery-rule`|
                                      `filing-of-complaint`|`conclusion-of-admin-process`|`see-context`; hook: impacts, prerequisite)
- `sol_trigger_discovery_gloss`    — (conditional on `sol_triggers` includes `discovery-rule`)
- `sol_trigger_gloss`              — (conditional on `sol_triggers` is non-empty)
- `is_sol_suspended_during_admin`
- `has_sol_details`                — (approved use of `*_details` on trigger)
- `sol_details`
- `sop_value`                      — (Sister to `statute_of_repose_context`)
- `sop_unit`
- `is_sop_tolling_available`       — (Sister to `statute_of_repose_context`)
- `statute_of_repose_context`      — (conditional on `statute-of-repose` in `legal_recognitions`; hook: required)
- `statutory_tolling_context`      — (conditional on `statutory-tolling` in `legal_recognitions`; hook: required)
- `equitable_tolling_context`      — (conditional on `equitable-tolling` in `legal_recognitions`; hook: required)
- `cba_preemption_context`         — (conditional on `cba-grievance-preemption` in `legal_recognitions`; hook: required)
- `amended_claim_context`          — (conditional on `amended-claim` in `legal_recognitions`; hook: required)
- `exhaustion_required_scope`      — (Sister to `exhaustion_required_context`; select: `jurisdictional`|
                                      `claims-processing`|`waivable`|`see-context`; hook: required, excluded-by)
- `exhaustion_required_context`    — (conditional on `exhaustion-required` in `legal_recognitions`; hook: excluded-by)
- `filing_notice_required_value`   — (Sister to `filing_notice_required_context`)
- `filing_notice_required_unit`
- `filing_notice_required_targets` — (Sister to `filing_notice_required_context`; multi-select: `employer`|
                                      `agency`|`attorney-general`|`labor-board`|`see-context`; hook: required)
- `filing_notice_required_context` — (conditional on `pre-filing-notice-required` in `legal_recognitions`)
- `employer_threshold_compare`     — (Sister to `employer_threshold_context`; select: `gte`|`lte`|`gt`|`lt`|
                                      `eq`; hook: required)
- `employer_threshold_value`       — (Sister to `employer_threshold_context`)
- `employer_threshold_model`       — (Sister to `employer_threshold_context`; select: `employees`|`workers`|
                                      `contractors`|`fte`; hook: required)
- `employer_threshold_context`     — (conditional on `employer-threshold-specified` in `legal_recognitions`)
- `cure_period_value`              — (Sister to `cure_period_context`)
- `cure_period_unit`
- `cure_period_context`            — (conditional on `cure-period-specified` in `legal_recognitions`; hook: required)
- `federal_state_interaction`      — (select: `express-preemption`|`savings-clause-preserves-state`|
                                      `concurrent-enforcement`|`field-preemption`|`state-exceeds-federal-floor`|
                                      `has-details`)
- `savings_clause_preserves_gloss` — (conditional on `federal_state_interaction` is
                                      `savings-clause-preserves-state`)
- `federal_state_interaction_details`

---

### Retaliation Tab

Fields ordered: scope → adverse actions → recognitions → sanctions.

- `adverse_action_scope`              — (select: `termination-only`|`material-adverse`|
                                         `broad-any-adverse-action`|`see-gloss`; hook: excludes)
- `adverse_action_scope_gloss`        — (conditional on `adverse_action_scope` is non-empty; hook: override)
- `adverse_actions`                   — (taxonomy: `ws_adverse_action`; hook: impacts)
- `anticipatory_retaliation_gloss`    — (conditional on `anticipatory-retaliation` in `adverse_actions`; hook: override)
- `threatened_retaliation_gloss`      — (conditional on `threatened-retaliation` in `adverse_actions`; hook: override)
- `adverse_action_details`
- `facially_retaliatory_policy_context` — (conditional on `facially-retaliatory-policy` in `legal_recognitions`; hook: required)
- `is_blacklisting_extended`          — (conditional on `blacklisting` in `adverse_actions`)
- `preservation_deadline_value`       — (Sister to `evidence_preservation_context`)
- `preservation_deadline_unit`
- `preservation_requirement_scopes`   — (Sister to `evidence_preservation_context`; multi-select:
                                         `litigation-hold`|`statutory-hold`|`court-order`|`agency-request`|
                                         `see-context`; hook: required)
- `evidence_preservation_context`     — (conditional on `evidence-preservation` in `legal_recognitions`)
- `constructive_discharge_standard`   — (Sister to `constructive_discharge_context`; select: `dual-prong`|
                                         `objective-intolerability`|`intent-required`|`see-context`; hook: required)
- `constructive_discharge_context`    — (conditional on `constructive-discharge-standard`
                                         in `legal_recognitions`)
- `is_evidence_collection_protected`
- `cats_paw_liability_context`        — (conditional on `cats-paw-liability` in `legal_recognitions`; hook: required)
- `is_cats_paw_liability_extended`    — (Sister to `cats_paw_liability_context`; AND conditional on
                                         any child-slug of `associates-of-whistleblower` in `protected_classes`; hook: verify)
- `third_party_retaliation_context`   — (conditional on `third-party-retaliation` in `legal_recognitions` AND
                                         any retaliation-slug in `adverse_actions`; hook: required)
- `criminal_sanctions`                — (Sister to `criminal_sanctions_context`; repeater:
      ├── `sanction_conduct`                [select: `retaliation`|`disclosure`|`false-report`|`obstruction`|
      │                                      `see-context`],
      ├── `sanction_level`                  [select: `misdemeanor`|`felony`|`see-context`],
      └── `sanction_context`                [conditional on `sanction_conduct` is non-empty]; hook: required)
- `criminal_sanctions_context`        — (conditional on `criminal-sanctions` in `legal_recognitions` AND
                                         `criminal-referral` in `process_types`)

---

### Processes & Remedies Tab

Fields ordered: process → pathway → fee shifting → remedies → reinstatement.

- `process_types`                  — (taxonomy: `ws_process_type`; hook: impacts, prerequisite)
- `primary_agency`                 — (select: post_type; hook: filter, derive)
- `local_agencies`                 — (multi-select: post_type; hook: filter)
- `federal_agencies`               — (multi-select: post_type; hook: filter)
- `process_pathway_scope`          — (Sister to `process_pathway_context`; select: `direct-agency`|
                                      `direct-court`|`either`|`hybrid-right-to-sue-on-inaction`|`see-context`; hook: impacts, required, excludes)
- `process_pathway_limit`          — (Sister to `process_pathway_context`; select: `mandatory`|`permitted`|
                                      `conditional`|`unavailable`|`see-context`)
- `is_agency_inaction_included`    — (conditional on `process_pathway_scope` is
                                      `hybrid-right-to-sue-on-inaction`)
- `process_pathway_context`        — (conditional on `process-pathway` in `legal_recognitions`)
- `enforcement_sequence`
- `private_roa_context`            — (conditional on `private-right-of-action` in `legal_recognitions`; hook: required)
- `pleading_standard_rule`         — (Sister to `pleading_standard_context`; select: `rule-8-notice`|
                                      `rule-9b-particularity`|`state-equivalent-notice`|
                                      `state-equivalent-particularity`|`has-details`; hook: required)
- `pleading_standard_details`
- `pleading_standard_context`      — (conditional on `heightened-pleading-standard` in `legal_recognitions`)
- `jury_trial_scope`               — (Sister to `jury_trial_context`; select: `all-claims`|`damages`|
                                      `liability`|`see-context`; hook: required)
- `jury_trial_context`             — (conditional on `private-right-of-action` AND `jury-trial` in
                                      `legal_recognitions`)
- `fee_shifting_standard`          — (Sister to `fee_shifting_standard_context`; select:
                                      `bilateral-loser-pays`|`unilateral-pro-plaintiff`|`none-american-rule`|
                                      `prevailing-defendant-bad-faith`|`see-context`; hook: required)
- `fee_shifting_scope`             — (Sister to `fee_shifting_standard_context`; select: `mandatory`|
                                      `discretionary`|`asymmetrical`; hook: required)
- `has_fee_shifting_phases`        — (auto-set true; conditional on `fee_shifting_standard` is
                                      `none-american-rule`; approved auto-set with editorial flag; hook: auto-set)
- `fee_shifting_phases`            — (repeater:
      ├── `phase`                        [select: `administrative`|`investigative`|`litigation`|`appeal`|
      │                                   `see-context`],
      ├── `phase_standard`               [select: `bilateral-loser-pays`|`unilateral-pro-plaintiff`|
      │                                   `unilateral-pro-defendant`|`prevailing-defendant-bad-faith`|
      │                                   `american-rule`|`see-context`],
      ├── `phase_scope`                  [select: `mandatory`|`discretionary`|`asymmetrical`],
      ├── `phase_asymmetry`              [conditional on `phase_scope` is `asymmetrical`; select: `two-way`|
      │                                   `one-way-plaintiff`|`one-way-defendant-frivolous`|`has-details`],
      ├── `asymmetry_details`            [conditional on `phase_asymmetry` is `has-details`],
      └── `phase_context`                [conditional on `phase` is non-empty]; hook: required)
- `fee_shifting_asymmetry`         — (conditional on `fee_shifting_scope` is `asymmetrical`; select: `two-way`|
                                      `one-way-plaintiff`|`one-way-defendant-frivolous`|`has-details`)
- `fee_shifting_asymmetry_details`
- `fee_shifting_standard_context`  — (conditional on `fee-shifting-standard` in `legal_recognitions`)
- `remedies`                       — (taxonomy: `ws_remedy`; hook: impacts, prerequisite)
- `remedy_limits`                  — (repeater; conditional on `has-limits` in `remedies`:
       ├── `remedy_limit`                [select: `emotional-distress`|`punitive`|`compensatory`|`aggregate`|
       │                                  `employer-size-tiered`|`see-context`],
       ├── `employer_tier`               [conditional on `remedy_limit` is `employer-size-tiered`],
       ├── `limit_amount`                [number],
       ├── `applies_to`                  [select: `single-claim`|`per-plaintiff`|`per-incident`|
       │                                  `aggregate-action`|`see-context`],
       └── `limit_context`               [conditional on `remedy_limit` is non-empty])
- `remedy_details`
- `remedy_liquidated_multiplier`   — (conditional on `liquidated-damages` in `remedies`; select:
                                      `double`|`treble`|`2x-back-pay`|`2x-wages-lost`|`statutory-formula`|
                                      `statutory-daily-fine`|`up-to-double`|`up-to-treble`|`has-details`)
- `remedy_liquidated_formula`      — (conditional on `remedy_liquidated_multiplier` is `statutory-formula`)
- `remedy_liquidated_details`      — (conditional on `remedy_liquidated_multiplier` is `has-details`)
- `mitigation_required_sources`     — (Sister to `mitigation_required_context`; multi-select: `yes-statutory`|
                                      `yes-common-law`; hook: required)
- `mitigation_required_context`    — (conditional on `mitigation-required` in `legal_recognitions`)
- `mitigation_exception_context`   — (conditional on `mitigation-required` in `legal_recognitions` AND
                                      `mitigation-exception` in `legal_recognitions`; hook: required)
- `after_acquired_evidence_effect` — (Sister to `after_acquired_evidence_context`; select: `bars-front-pay`|
                                      `bars-reinstatement`|`bars-damages`|`bars-all`|`see-context`; hook: required, excluded-by)
- `after_acquired_evidence_context` — (conditional on `after-acquired-evidence` in `legal_recognitions`; hook: excluded-by)
- `interest_provision_scope`       — (Sister to `interest_provision_context`; select:
                                      `pre-judgment-statutory`|`post-judgment`|`both`|`discretionary`|
                                      `see-context`; hook: required)
- `interest_provision_context`     — (conditional on `equitable-interest-award` in `legal_recognitions`)
- `preliminary_reinstatement_rule`         — (Sister to `preliminary_reinstatement_context`; select: `mandatory`|
                                      `discretionary`|`has-details`; hook: required)
- `preliminary_reinstatement_rule_details`
- `preliminary_reinstatement_scope` — (Sister to `preliminary_reinstatement_context`; select: `admin-phase`|
                                      `full-pendency`|`see-context`; hook: required)
- `preliminary_reinstatement_context` — (conditional on `preliminary-reinstatement` in `legal_recognitions` AND
                                      `reinstatement` OR `interim-reinstatement` in `remedies`)

---

### Burden Of Proof Tab

Fields ordered: framework → employee standards → causation → employer defenses → rebuttable presumption →
temporal presumption → detail overflow.

- `burden_shifting_frameworks`      — (Sister to `burden_shifting_context`; multi-select: `mcdonnell-douglas`|
                                       `motivating-factor`|`but-for`|`mixed-motive`|`has-details`; hook: required)
- `burden_shifting_details`         — (conditional on `burden_shifting_frameworks` includes `has-details`)
- `burden_shifting_context`         — (conditional on `burden-shifting-framework` in `legal_recognitions`)
- `mixed_motive_remedy_gloss`       — (conditional on `burden_shifting_frameworks` includes `mixed-motive`)
- `same_decision_standard`          — (Sister to `same_decision_context`; select: `preponderance`|
                                       `clear-and-convincing`|`see-context`; hook: required)
- `same_decision_context`           — (conditional on `same-decision-defense-standard` in `legal_recognitions`)
- `employee_standard`               — (single-select taxonomy: `ws_employee_standard`)
- `employee_standard_details`
- `employment_classification_test`  — (Sister to `employment_classification_context`; select: `economic-realities`|
                                       `common-law-darden`|`abc-test`|`right-to-control`|`hybrid`|`see-context`; hook: required)
- `employment_classification_context`  — (conditional on `employment-classification-test` in `legal_recognitions`; hook: required)
- `has_causation_standard_statutory_text`  — (Sister to `causation_standard_context`)
- `causation_standard_statutory_text`  — (distinct from `causation_standard_context`)
- `causation_standard`              — (Sister to `causation_standard_context`; single-select taxonomy:
                                       `ws_causation_standard`; hook: required)
- `causation_scope`                 — (Sister to `causation_standard_context`; select: `liability`|
                                       `damages`|`both`|`see-context`)
- `causation_standard_context`      — (conditional on `causation-standard-recognized` in `legal_recognitions`; hook: required)
- `causation_dual_standard_context`  — (conditional on `causation-dual-standard` in `legal_recognitions` AND
                                        `causation-standard-recognized` in `legal_recognitions`; hook: required)
- `employer_knowledge_scopes`       — (Sister to `employer_knowledge_context`; multi-select:
                                       `actual-knowledge`|`constructive-knowledge`|`inferred-knowledge`|
                                       `imputed-knowledge`|`has-details`; hook: required)
- `employer_knowledge_scopes_details`
- `employer_knowledge_context`      — (conditional on `employer-knowledge-required` in `legal_recognitions`)
- `employer_defenses`               — (taxonomy: `ws_employer_defense`)
- `employer_defense_details`
- `has_rebuttable_presumption`
- `rebuttable_presumption_details`  — (approved use of as `*_details` suffix)
- `presumption_window_value`        — (Sister to `temporal_presumption_context`)
- `presumption_window_unit`
- `presumption_effect`              — (Sister to `temporal_presumption_context`; select: `shifts-burden`|
                                      `creates-inference`|`rebuttable-presumption`|`has-details`; hook: required)
- `presumption_effect_details`
- `temporal_presumption_context`    — (conditional on `temporal-presumption-recognized` in `legal_recognitions`)
- `temporal_proximity_value`        — (Sister to `temporal_proximity_context`)
- `temporal_proximity_unit`
- `temporal_proximity_context`      — (conditional on `temporal-proximity-sufficient` in `legal_recognitions`; hook: required)
- `has_bop_details`                 — (approved use of `*_details` on trigger)
- `bop_details`

---

### Reward Tab

Fields ordered: rewards → qui tam specifics.

- `reward_discretion_scope`     — (Sister to `reward_context`; select: `mandatory`|`discretionary`|`presumptive`|
                                   `formula-based`|`has-details`; hook: required)
- `reward_discretion_formula`   — (conditional on `reward_discretion_scope` is `formula-based`)
- `reward_discretion_details`   — (conditional on `reward_discretion_scope` is `has-details`)
- `reward_context`              — (conditional on `reward-available` in `legal_recognitions`)
- `qui_tam_government_share`    — (Sister to `qui_tam_share_context`)
- `qui_tam_relator_share`       — (Sister to `qui_tam_share_context`)
- `qui_tam_reduction_context`   — (Sister to `qui_tam_share_context`)
- `qui_tam_share_context`       — (conditional on `qui-tam-action` in `legal_recognitions`; hook: required)
- `first_to_file_bar`           — (Sister to `qui_tam_share_context`; AND `bounty-qui-tam-award` in `remedies`)
- `public_disclosure_bar`       — (Sister to `qui_tam_share_context`; AND `bounty-qui-tam-award` in `remedies`) 

---

### Waiver & Scope Tab

Fields ordered: contractual → recognitions → immunity → defendants.

- `all_waivers_blocked_context`    — (conditional on `all-plaintiff-waivers-void` in `legal_recognitions`; hook: required)
- `civil_action_waiver_status`      — (Sister to `civil_action_waiver_context`; select: `prohibited`|
                                      `permitted-individual-only`|`permitted-collective`|`see-context`; hook: required, excluded-by)
- `civil_action_waiver_context`    — (conditional on `all-plaintiff-waivers-void` absent in
                                      `legal_recognitions` AND `civil-action-waiver` in `legal_recognitions`; hook: excluded-by)
- `contractual_waiver_status`       — (Sister to `contractual_waiver_context`; select: `void`|`limited`|
                                      `enforceable`|`void-public-policy`|`void-as-to-whistleblowing`|
                                      `enforceable-with-exceptions`|`see-context`; hook: required, excluded-by)
- `contractual_waiver_context`     — (conditional on `all-plaintiff-waivers-void` absent in
                                      `legal_recognitions` AND `contractual-waiver` in `legal_recognitions`; hook: excluded-by)
- `collateral_claims_waiver_context` — (conditional on `all-plaintiff-waivers-void` absent in
                                      `legal_recognitions` AND `collateral-claims-waiver` in
                                      `legal_recognitions`; hook: required, excluded-by)
- `class_action_waiver_context`    — (conditional on `all-plaintiff-waivers-void` absent in
                                      `legal_recognitions` AND `class-action-permitted` absent in
                                      `legal_recognitions` AND `class-action-waiver` in `legal_recognitions`; hook: required, excluded-by)
- `proper_defendant_rules`         — (Sister to `proper_defendants_context`; repeater:
      ├── `defendant_class`              [select: `employer-entity`|`individual-supervisor`|`public-official`|
      │                                   `government-agency`|`contractor`|`successor-employer`|
      │                                   `parent-subsidiary`|`joint-employer`|`staffing-agency`|
      │                                   `labor-organization`|`see-context`],
      ├── `defendant_limit`              [select: `mandatory`|`permitted`|`conditional`|`unavailable`|
      │                                   `exclusive`|`see-context`],
      └── `defendant_context`            [conditional on `defendant_class` is non-empty]; hook: required)
- `proper_defendants_context`      — (conditional on `proper-defendants-specified` in `legal_recognitions`)
- `individual_liability_scopes`    — (Sister to `individual_liability_context`; multi-select: `supervisor`|
                                      `coworker`|`officer-director`|`any-individual-only`|`has-details`; hook: required)
- `individual_liability_details`   — (conditional on `individual_liability_scopes` includes `has-details`)
- `individual_liability_context`   — (conditional on `individual-liability` in `legal_recognitions`)
- `sovereign_immunity_status`      — (Sister to `sovereign_immunity_context`; select: `not-waived`|
                                      `partially-waived`|`fully-waived`|`has-details`; hook: required, excluded-by)
- `sovereign_immunity_limits`      — (Sister to `sovereign_immunity_context`; multi-select: `cap-applies`|
                                      `conditions-apply`|`tort-claims-act-gate`; hook: excluded-by)
- `sovereign_immunity_limit_gloss` — (conditional on `sovereign_immunity_limits` is non-empty)
- `sovereign_immunity_scope`       — (Sister to `sovereign_immunity_context`; select: `all`|
                                      `instrumentalities-included`|`political-subdivisions-included`|
                                      `state-exclusively`|`see-context`; hook: excluded-by)
- `sovereign_immunity_waiver_class` — (Sister to `sovereign_immunity_context`; AND conditional on
                                      `sovereign_immunity_status` is non-empty AND is NOT `not-waived`;
                                      select: `explicit-waiver`|`implied-waiver`; hook: required, excluded-by)
- `sovereign_immunity_status_details`
- `sovereign_immunity_context`     — (conditional on `sovereign-immunity-status` in `legal_recognitions`; hook: excluded-by)
- `nda_limits_context`             — (conditional on `nda-limitations` in `legal_recognitions`; hook: required)
- `anti_gag_provision_context`     — (conditional on `anti-gag-provision-present` in `legal_recognitions`; hook: required)
- `no_retaliatory_evidence_context` — (conditional on `no-retaliatory-evidence` in `legal_recognitions`; hook: required)
- `stay_of_discipline_context`     — (conditional on `stay-of-disciplinary-action` in `legal_recognitions`; hook: required)
- `anti_slapp_protection_scopes`   — (Sister to `anti_slapp_protection_context`; multi-select:
                                      `motion-to-strike`|`discovery-stay`|`fee-shift-on-motion`|
                                      `full-procedural-only`|`see-context`; hook: required)
- `anti_slapp_protection_context`  — (conditional on `anti-slapp-protection` in `legal_recognitions`)
- `discovery_protection_scopes`    — (Sister to `discovery_protection_context`; multi-select:
                                      `retaliatory-subpoenas`|`abusive-discovery`|`litigation-harassment`|
                                      `see-context`; hook: required)
- `discovery_protection_context`   — (conditional on `discovery-protection` in `legal_recognitions`; distinct
                                      from anti-SLAPP)
- `settlement_restriction_scope`   — (Sister to `settlement_restriction_context`; select: `amount`|`facts`|
                                      `full-prohibition`|`agency-notification`|`see-context`; hook: required)
- `settlement_restriction_context` — (conditional on `confidential-settlement-restriction` in
                                      `legal_recognitions`)
- `successor_liability_context`    — (conditional on `successor-liability` in `legal_recognitions`; hook: required)
- `extraterritorial_context`       — (conditional on `extraterritorial-coverage` in `legal_recognitions`; hook: required)

---

### Relationships Tab

Fields ordered: reference → related legal records.

- `ref_materials`                  — (multi-select: post_type)
- `overruled_by_id`

---

### Authority Tab

Fields ordered: source url → authority.

- `url`
- `url_is_pdf`
- `authority_reference`

---

### Hidden Fields

Hidden fields have no tab and are prefixed with underscore. Fields ordered: id → derived.

- `_disclosure_target_class`       — (select: `internal`|`external`|`both`; hook: derive)
- `_primary_agency_is_fed`
- `_related_agencies`              — (multi-select: post_type)

---

## Specialized Fields By Legal Record Type

---

### Substantive-Record Common Fields (statute + common_law)

Substantive records carry most of the fields defining whistleblower protections.

#### Fields Excluded From Substantive Records

Notable precedent-only fields capture modifications to legal definitions and do not appear on substantive
records:

- `court` — federal or local court that made the ruling
- `scope` — the result of the ruling
- `binding_strength` — effective strength of the ruling
- `affected_jx` — affected jurisdictions
- `extended_taxonomies` — affected taxonomy when ruling is favorable or dual-effect
- `suppressed_taxonomies` — affected taxonomy when ruling is adverse or dual-effect
- `_parent_ids`: `statute_ids`, `comlaw_ids` — legal records affected by ruling

#### Substantive Additions

##### Processes & Remedies Tab (insert after `jury_trial_context`)

- `review_standard`          — (Sister to `review_standard_context`; select: `de-novo`|
                                      `substantial-evidence`|`arbitrary-capricious`|`abuse-of-discretion`|
                                      `has-details`; hook: required)
- `review_standard_details`
- `review_standard_context`        — (conditional on `civil-review-standard` in `legal_recognitions`)

##### Retaliation Tab (insert after `anticipatory_retaliation_gloss`)

- `election_of_remedies_rules`     — (Sister to `election_of_remedies_context`; multi-select:
                                      `administrative-bars-civil`|`state-bars-federal`|`remedy-exclusivity`|
                                      `first-filed-controls`|`see-context`; hook: required)
- `election_of_remedies_context`   — (conditional on `remedy-election-required` in `legal_recognitions`)

##### Relationships Tab

- `citation_ids`
- `construction_ids`

##### Hidden Fields

- `_precedent_ids`

---

### Statute-Specific

Statute records have no deltas. Future statute-only additions, if any, will be documented here.

---

### Common-Law-Specific

#### Identity Tab (insert after `citation`)

- `precedent_common`

#### Classification Tab (insert after `excluded_class_details`)

- `doctrine_basis`
- `public_policy_sources`          — (multi-select: `constitution`|`federal-law`|`statute`|`administrative-rule`|
                                      `case-law`|`executive`|`has-details`)
- `policy_source_details`          — (conditional on `public_policy_sources` includes `has-details`)
- `recognition_status`             — (select: `recognized`|`limited`|`uncertain`|`rejected`|`abrogated`|
                                      `has-details`)
- `recognition_status_details`

#### Statute of Limitations & Thresholds Tab (insert after `federal_state_interaction_details`)

- `statutory_preclusion_context`   — (conditional on `statutory-preclusion` in `legal_recognitions`; hook: required)

#### Burden of Proof Tab (insert after `causation_standard_statutory_text`)

- `statutory_nexus_context`        — (conditional on `statutory-nexus-controls` in `legal_recognitions`)

---

### Precedent-Record Common Fields (citation + construction)

Precedent records carry most common fields. Notable exceptions are fields definitionally inapplicable to court
decisions:

- `election_of_remedies_rules` — legislative/doctrinal construct; not a court ruling.
- `doctrine_*`, `public_policy_*`, `recognition_*`, etc. — common-law-specific fields with no precedent
  equivalent.
- `_precedent_ids` — Precedent records have `_parent_ids` (`statute_ids`, `comlaw_ids`) instead.

#### Identity Tab (insert after `citation`)

- `class`                          — (select: `case-law`|`statute`|`regulatory`|`secondary`|`has-details`)
- `class_details`
- `status`                         — (select: `published`|`unpublished`|`memorandum`|`vacated`)
- `binding_strength`               — (select: `binding`|`persuasive`|`mixed`|`distinguished`|`overruled`;
                                      approved use of `mixed` where binding strength **is capable of** truly varying)
- `court`                          — (select: @matrix; hook: filter)
- `court_details`
- `court_jx`                       — (Sister to `court_details`; taxonomy: `ws_jurisdiction`)

#### Identity Tab (insert after `effective_year`)

- `mandate_date`                   — (distinct from `date`)

#### Classification Tab (insert after `legal_recognitions`)

- `scope`                          — (select: `favorable`|`adverse`|`neutral`|`dual-effect`; hook: verify)
- `extended_taxonomies`            — (conditional on `scope` is `favorable` OR `scope` is `dual-effect`;
                                      repeater:
      ├── `taxonomy`                     [select: taxonomy slug],
      └── `term`                         [select: taxonomy term]; hook: filter)
- `suppressed_taxonomies`          — (conditional on `scope` is `adverse` OR `scope` is `dual-effect`;
                                      repeater:
      ├── `taxonomy`                     [select: taxonomy slug],
      └── `term`                         [select: taxonomy term]; hook: filter)
- `has_affected_jx`                — (hook: derive)
- `affected_jx`                    — (conditional on `has_affected_jx` is true; taxonomy: `ws_jurisdiction`; hook: filter)

##### Eligible Taxonomy Allowlist for `extended_taxonomies` / `suppressed_taxonomies`

The `taxonomy` repeater select choices are restricted to legal-record-attached classificatory taxonomies that a
ruling can meaningfully extend or suppress. The `term` choices are filtered to terms within the selected
taxonomy.

Eligible: `ws_legal_recognition`, `ws_protected_disclosure`, `ws_protected_class`, `ws_excluded_class`,
`ws_employment_sector`, `ws_disclosure_target`, `ws_protected_action`, `ws_adverse_action`, `ws_employer_defense`,
`ws_remedy`, `ws_process_type`, `ws_employee_standard`, `ws_causation_standard`.

Excluded: `ws_jurisdiction` (geographic, not classificatory); `ws_aorg_*`, `ws_language`, `ws_case_stage`,
`ws_procedure_type` (not attached to legal-record CPTs).

#### Relationships Tab

- `statute_ids`
- `comlaw_ids`
- `parent_weight`                  — (select: `primary`|`secondary`|`distinguishing-only`)
- `has_negative_treatment_class`
- `negative_treatment_class`       — (conditional on `has_negative_treatment_class` is true; select: `overruled`|
                                      `distinguished`|`limited`|`questioned`|`superseded-by-statute`)
- `superseded_by_statute_id`       — (conditional on `negative_treatment_class` is
                                      `superseded-by-statute`)

#### Authority Tab (insert after `authority_reference`)

- `authority_sources`              — (multi-select: `constitutional`|`legislative`|`judicial`|`regulatory`|
                                      `executive`|`has-details`)
- `authority_source_details`
- `review_standard`          — (select: `de-novo`|`substantial-evidence`|`arbitrary-capricious`|
                                      `abuse-of-discretion`|`has-details`)
- `review_standard_details`

#### Hidden Fields

- `_parent_ids`
- `_court_is_fed`

---

### Citation-Specific

Citation records have no deltas from the precedent-record common set. Future citation-only additions, if any,
will be documented here.

---

### Construction-Specific

#### Identity Tab

- `is_en_banc`                     — (default true; false triggers `panel_composition_class`; approved is_* used as trigger)
- `panel_composition_class`        — (conditional on `is_en_banc` is false; select: `three-judge`|`five-judge`|
                                      `seven-judge`|`nine-judge`|`expanded-panel`|`judge`)

---

## Rename Normalization (Current → Canonical)

Only fields that currently violate target naming conventions, are inconsistent across legal ACFs, or were
structurally redesigned during the canonical rewrite. Fields that are unchanged or new do not appear in this
list.

- `fee_shifting_rules`                       → `fee_shifting_standard` + `fee_shifting_scope`
- `has_limit_ambiguous`                      → `has_sol_details`
- `limit_details`                            → `sol_details`
- `has_tolling_details`                      → split into `statutory-tolling` and `equitable-tolling`, true
                                                when present in `legal_recognitions`
- `tolling_details`                          → split into `statutory_tolling_context` and
                                                `equitable_tolling_context`
- `has_exhaustion_required`                  → `exhaustion-required` in `legal_recognitions`
- `exhaustion_details`                       → `exhaustion_required_context`
- `has_employer_threshold`                   → `employer-threshold-specified` in `legal_recognitions`
- `employer_threshold_details`               → `employer_threshold_context`
- `rebuttable_presumption`                   → `rebuttable_presumption_details`
- `has_statutory_preclusion`                 → `statutory-preclusion` in `legal_recognitions`
- `statutory_preclusion_details`             → `statutory_preclusion_context`
- `doctrine_basis_wysiwyg`                   → `doctrine_basis` (never was wysiwyg)
- `recognition_status_wysiwyg`               → `recognition_status` (select; never was wysiwyg) +
                                                `recognition_status_details` (textarea)
- `other_sources`                            → `policy_source_details` (uses `has-details` sentinel now)
- `doctrine_name`                            → `official_name`
- `case_citation`                            → `citation`
- `precedent_url`                            → `url`
- `precedent_url_is_pdf`                     → `url_is_pdf`
- `has_reward_available`                     → `reward-available` in `legal_recognitions`
- `reward_details`                           → `reward_context`
- `court_name`                               → `court_details` (uses `has-details` sentinel now)
- `is_favorable` (bool)                      → `scope` (select)
- `adverse_action_scope` (textarea)          → `adverse_action_scope` (select) + `adverse_action_scope_gloss`
                                                (freetext)
- `doctrine_id`                              → removed (visible dedupe IDs deemed unnecessary)
- `bop_flag`                                 → removed (used by researchers only, never meant for ACF meta)
- `statute_id` + `comlaw_id` (singular)      → pluralized to support rare-but-possible multi-values

---

## Notes

The ACFs for legal records are designed to capture all nuance for the current record. Much of this data will
never surface outside the summary in `plain_english_wysiwyg`. The data primarily serves as both requirement and
guidance for the editor when crafting that summary. Hook-enforced data integrity ensures the editor is not
working from incomplete or contradictory data.

This spec uses the statute field set as the baseline for broad legal-record parity, then adds per-type deltas
where a record type requires different structure. `ws_legal_recognition` is a presence/absence signal taxonomy,
not a classification table. Slugs marked `(no companion needed)` in the slug-to-companion map are captured only
through `ws_legal_recognition`; no separate ACF field is registered for those slugs.

This spec has zero live-data implications. It is the structural source of truth for PHP field generation and
ingest schema mapping.

### Ingest Pipeline Philosophy

The legal-record field set is the *destination* schema, not the *acquisition* schema. Researchers — including LLM
research assistants — are not expected to find or report data in ACF-field shape. The pipeline is staged so each
layer extracts what it **is capable of modeling** cleanly and passes the rest forward as narrative for the next
layer:

1. **Researcher (LLM or human).** Reports findings in legal terms, not field terms. Where possible, findings
   **must strictly** map cleanly to a field. Otherwise, findings ride forward as a breadcrumb in freetext or
`*_context`
   companions for downstream review.
2. **Reconciler.** **Is authorized to** update findings to map to fields — disambiguating cross-jurisdictional
terminology,
   normalizing values, splitting compound findings, resolving freetext into structured equivalents when
   possible. Remaining findings stay as breadcrumbs for human review.
3. **Human reviewer.** Final resolution. Validates field-mapped values, reviews un-mapped findings, and
   researches remaining required fields. The final summary is then written to `plain_english_wysiwyg`.

This staging is the operational form of the omission-over-fabrication rule: each layer maps what it **is capable
of** and
avoids invention. Freetext fields and `*_context` companions are not failure modes — they are the designed
channels through which breadcrumbs survive between stages without being lost.

---

*Geo-centric and historical context regarding the pluralization of status appears in the liner notes. LP edition
only. More info available by handwritten request with self-addressed stamped envelope; sender ZIP and return ZIP
must be at least 175 nautical miles apart, USPS-verified. The limited vinyl edition, read by Patrick Stewart,
includes the liner notes, disputed pluralizations, and the extended commentary track on the `status` incident,
including its violations of at least three rules.*

— drafted for Dejunai by Claude (Anthropic), session of 2026-05-06
