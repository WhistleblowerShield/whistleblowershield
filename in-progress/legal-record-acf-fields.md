# Legal Record ACF Canonical Field Draft

Purpose: draft a unified, prefix-free field set for all four legal record types
(`statute`, `common_law`, `citation`, `construction`) as the first step toward a
full rewrite of the ingest tool.

Notes: Do not update existing files. Rename existing files with .txt appended.
Create new files with same names as the originals.

Source note: the true legacy statute ACF source is
`plugins/ws-core-rewritten/includes/acf/acf-jx-statutes.legacy.20260429.php`.
`acf-jx-statutes.ag.20260429.php.txt` is an Antigravity first pass, not the
legacy baseline.

---

## Naming Rules

**Casing**
- Meta names (ACF `name` key): `snake_case` only.
- Choice keys (select / multi-select option values): `kebab-case` only.

**CPT infix** — absent from this draft; applied at registration

CPT slot values: `statute` · `comlaw` · `citation` · `construction`

- `name` (meta key): `ws_jx_statute_*` · `ws_jx_comlaw_*` · `ws_jx_citation_*` · `ws_jx_construction_*`
- field `key`: `field_jx_statute_*` · `field_jx_comlaw_*` · `field_jx_citation_*` · `field_jx_construction_*`
- tab field `key`: `field_jx_{cpt}_{tab}_tab` — tab label lowercase, no `_and_`, no symbols.
  Approved abbreviations: `sol` = `statute_of_limitations` · `bop` = `burden_of_proof`
- group `key`: `group_jx_statute_metadata` · `group_jx_comlaw_metadata` · `group_jx_citation_metadata` · `group_jx_construction_metadata`
- group `menu_order`: < 85 — workflow groups occupy 85–99; CPT group must precede them.

**Reserved prefixes**
- `ws_auto_` — written exclusively by hook logic (stamp, source, plain-English attribution). Never use on content fields.

**Cardinality**
- Single-value fields: singular noun.
- Multi-value fields (multi-select, repeater, array): plural noun.

**Booleans**
- `has_*` — trigger boolean. True activates a companion or dependent field. May trigger `*_details`, another
  field (e.g. `has_effective_date` triggers `effective_date`), or both.
- `is_*` or `*_is_*` — state boolean. Describes a condition; does not imply a companion. An `is_*` field
  may act as a trigger when documented inline as an approved case.

**Companion suffixes**

- `*_details` — freetext (usually) companion. Two valid triggers:
    When `has_field_name_details` is true, `field_name_details` is triggered, or
    When `has-details` sentinel is present in trigger `field_name`, conditional `field_name_details` is triggered.
- `has-*` sentinel or `has_*` bool can be used as a trigger for any conditional fields as `*_companion`, but
    trigger and companion fields must share the same name, or condition logic must be well-documented
    (e.g., `has_field_name_limits` and `field_name_limits`, `has-phases` and `*_phases`).

Annotation not required when the field naming convention makes the trigger unambiguous. Annotation required
when the trigger `field_name` deviates from conditional `field_name` (e.g., a suffix or prefix is dropped).

`*_context` — freetext (usually) companion. Triggered by a specific value or any non-empty value in a named
 trigger field. Annotation always required.

**Sister fields**

A sister field shares a sibling's conditional but is not itself a `*_companion` field. Sisters inherit the sibling's 
visibility — they appear when the sibling appears, hide when it hides — but are not independently conditional on
the sibling.

- No naming convention applies to sisters. Use a logical name for the data it holds.
- Sisters may appear before or after the sibling. Freetext occurs last usually, but no order is prescribed; use
  best editorial logic.
- A sister may not appear without a corresponding `*_companion` sibling in the same cluster.
- Sister clusters can chain when multiple conditions layer. Chains get messy — use inline notes where they help.

**Avoid**

- `*_recognized` — use a `ws_legal_recognition` taxonomy term instead where logical.
- `*_type` — prefer `*_class`, `*_scope`, `*_status`, `*_rule`, `*_framework`, `*_weight`, or `*_standard`.
   Use `*_type` only when context requires it and no better suffix fits. Pluralize suffix accordingly.
- `*_limitations` — use `*_limits` in meta names. No restriction on taxonomy slugs.

**Data-shape suffixes** (e.g., `*_url`, `*_date`, `*_email`, `*_value`(int), `*_unit`(calendar-unit select: `days`|`weeks`|`months`|`years`))

Apply when the field holds that data shape. Never apply otherwise.

---

## Sentinel Values

Sentinels are reserved choice keys and taxonomy term slugs with defined system behavior.

**Trigger sentinels** — added to a field's choices or taxonomy to signal a companion should appear:
- `has-details` — triggers the `*_details` companion for the field it appears in. Prefer over `other`,
  `unclear`, or `mixed` when a companion can capture the nuance.
- `has-limits` — in `ws_remedy`: triggers `remedy_limits`.
- `has-phases` — in `ws_fee_shifting_rule`: triggers `fee_shifting_rule_phases`.
- `has-channel-requirement` — in `ws_protected_disclosure`: triggers `disclosure_channel_context`.
- `ic-channel-required` — in `ws_protected_disclosure` under `national-security`: triggers `ic_channel_sequence_context`.
  Marks disclosures subject to the mandatory IC sequential channel chain (IG → ISCPB → congressional
  intelligence committee). Disclosure outside this chain destroys protection entirely.

**Redirect sentinels** — use when a companion is already triggered by another mechanism, making `has-details`
redundant in the current field:
- `see-details` — the `*_details` companion for this context is already active.
- `see-context` — the `*_context` companion for this context is already active.

---

## Hook Requirements

**General**
- Derived fields: auto-fill on load and on save.
- Merged hidden fields (e.g., `_related_agencies`, `_precedent_ids`, `_parent_ids`): auto-fill on save.
- Derived select choices (e.g., `court` filtered by `jurisdiction`): filter on field load.
- Write unified hooks over duplicates. A single hook using `get_post_type()` is preferred over two
  near-identical hooks per CPT. Reuse hooks wherever logical.

**Contradiction guards**
- `fee_shifting_rules` — detect and flag contradictory terms.
- `sovereign_immunity_limits` — detect and flag contradictory terms.
- `causation_application` — enforce mutual exclusivity: `liability`, `damages`, and `both` must never appear
   together. `has-details` may accompany any single primary value.
- `contractual-waiver` — invalid when `civil_action_waiver_scope` is `anti`. When `anti` is set: remove
  `contractual-waiver` from `legal_recognitions`, clear `contractual_waiver_context`, and clear any sisters.
- `jury-trial` — invalid without `private-right-of-action` in `legal_recognitions`. When
  `private-right-of-action` is absent: remove `jury-trial`, clear `jury_trial_context`, and clear any sisters.

**Agency filtering**
- `primary_agency` — auto-fill with the first attached `ws-agency` post when empty. Filter choices to
   currently attached posts only. Instructions when empty: `"Attach one ws-agency to local or federal first"`;
   when non-empty: `"Override primary_agency with any currently attached local or federal agency"`.
- `local_agencies` — filter to jx-applicable, non-federal `ws-agency` posts. (Stub: future refinement
   intersecting `ws_process_type` and `ws_disclosure_*` taxonomies.)
- `federal_agencies` — filter to federal `ws-agency` posts only. (Stub: future refinement intersecting
  `ws_process_type` and `ws_disclosure_*` taxonomies.)

---

## Inline Field Descriptions

**Default field types** (by naming convention, unless stated otherwise):
- `has_*` · `is_*` · `*_is_*` → boolean
- `*_class` · `*_scope` · `*_status` · `*_rule` · `*_framework` · `*_weight` · `*_standard` → select
- `*_share`        — used to describe specified portion of a reward, e.g. "25-30%"
- `*_compare`      — used to describe mandated comparison (select: `gte`|`lte`|`gt`|`lt`|`eq`)
- `*_formula`      — used to describe mandated calculations
- `*_sanctions`    — used to describe specified unlawful conduct and associated penalties (repeater)
- `*_application`  — used to describe where or how a legal standard applies
- `*_direction`    — used to describe directional legal operation, e.g. "Federal Preempts State"
- `*_bar`          — used for claim-blocking doctrines or procedural bars
- select → signals single-select; multi-select must be specified
- all others → freetext

**Default taxonomy field settings** (unless stated otherwise):
- Field type: taxonomy
-  multi-select
- `load_terms`: 1
- `save_terms`: 1

**Conditional annotation phrasing** — four accepted forms:
- Taxonomy term present:  `conditional on slug in taxonomy_field`
- Any non-empty value:    `conditional on trigger_field is non-empty`
- Specific value in select field:        `conditional on trigger_field is trigger_value`
- Specific value in multi-select field:  `conditional on trigger_field includes trigger_value`
Compound conditions: AND / OR / NOT (all-caps).

`*_details`, `*_limits`, `*_phases` and `*_companions` do not require annotation when the naming convention makes
 the trigger unambiguous. All other conditional fields, `*_context` included, must declare their trigger field and
 trigger value.

---

## Attached Workflow Groups

Four shared ACF groups attach to all four legal record types alongside the CPT-specific group.
Defined in `includes/acf/workflow/` — do not duplicate any of these fields in CPT-specific ACF files.

| Group key | `menu_order` | Tab label | Fields added |
|---|---|---|---|
| `group_plain_english_metadata` | 85 | Plain-English | `ws_has_plain_english`, `ws_plain_english_wysiwyg`, `ws_plain_english_reviewed`, 4 `ws_auto_` stamps |
| `group_auto_stamp_metadata` | 90 | Authorship & Review | `ws_auto_create_date`, `ws_auto_create_author`, `ws_auto_last_edited_date`, `ws_auto_last_edited_author` |
| `group_source_verify_metadata` | 95 | Source & Verification | `ws_auto_source_method`, `ws_auto_source_name`, `ws_auto_verified_by`, `ws_auto_verified_date`, `ws_verification_status`, `ws_needs_review` |
| `group_major_edit_metadata` | 99 | Major Edit | `ws_is_major_edit`, `ws_major_edit_description`, `ws_major_edit_update_type` |

---

## Prompt Schema -> ACF Field Mapping

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

    sol. (The Timeline (Crucial))
        statute_of_limitations              -> sol_value
        limit_unit                          -> sol_unit
                                                   (days|months|years|none)
        limit_trigger                       -> sol_trigger

    bop. (The Burden of Proof (Matching Scan))
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
                                                   {interim-relief}
                                                   [23 terms omitted]
        exhaustion_required                 -> classifications.legal_recognitions
                                                   {exhaustion-required}
        exhaustion_pathway                  -> sol.exhaustion_required_context

    source_audit. (Plain English Summaries)
        protections_summary                 -> identity.general_description
        protected_disclosures               -> classifications.protected_disclosures
        protected_activities                -> classifications.protected_actions

integrity.
    has_anomalies                           -> ingest-only
    notations                               -> ingest-only
    notation_count                          -> ingest-only

```

```

JSON key                                    -> ACF field (ws_jx_statute_*)

meta.
    jurisdiction_id                         -> identity.jurisdiction
    source_name                             -> ingest-only
    generated_by                            -> ingest-only
    batch_completed                         -> ingest-only (IT-1 sentinel)
    record_count                            -> ingest-only (IT-2 check)

records.
    identity.
        statute_id                          -> _id
        official_name                       -> official_name
        common_name                         -> common_name
        citation                            -> citation
        url                                 -> url
        url_is_pdf                          -> url_is_pdf
	
	effective_date.
        effective_date                      -> effective_date
        effective_year                      -> effective_year

    classifications.
        employment_sectors                  -> employment_sectors
        legal_recognitions                  -> legal_recognitions
        protected_disclosures               -> protected_disclosures
        protected_classes                   -> protected_classes
        protected_class_details             -> protected_class_details
        excluded_classes                    -> excluded_classes
        excluded_class_details              -> excluded_class_details
        excluded_class_context              -> excluded_class_context
        disclosure_targets                  -> disclosure_targets
        disclosure_target_details           -> disclosure_target_details
        adverse_action_scope                -> adverse_action_scope
        adverse_action_scope_context        -> adverse_action_scope_context
        adverse_actions                     -> adverse_actions
        adverse_action_details              -> adverse_action_details
        protected_actions                   -> protected_actions
        protected_action_context            -> protected_action_context
        protected_action_standard           -> protected_action_standard
        protected_action_source             -> protected_action_source
        manager_rule_exclusion_context      -> manager_rule_exclusion_context
        public_concern_context              -> public_concern_context
        bad_faith_exclusion_context         -> bad_faith_exclusion_context
        malicious_reporting_context         -> malicious_reporting_context
        malicious_reporting_sanctions       -> malicious_reporting_sanctions
        anonymity_protection_context        -> anonymity_protection_context

    sol.
        sol_value                           -> sol_value
        sol_unit                            -> sol_unit
        has_sol_details                     -> has_sol_details
        sol_details                         -> sol_details
        sol_trigger                         -> sol_trigger
        sol_trigger_event                   -> sol_trigger_event
        sol_trigger_context                 -> sol_trigger_context
        statutory_tolling_context           -> statutory_tolling_context
        equitable_tolling_context           -> equitable_tolling_context
        exhaustion_required_context         -> exhaustion_required_context
        exhaustion_required_class           -> exhaustion_required_class
        filing_notice_value                 -> filing_notice_value
        filing_notice_unit                  -> filing_notice_unit
        filing_notice_target                -> filing_notice_target
        filing_notice_context               -> filing_notice_context
        sop_value                           -> sop_value
        sop_unit                            -> sol_unit
        is_sop_tolling_available            -> is_sop_tolling_available
        statute_of_repose_context           -> statute_of_repose_context
        preemption_scope                    -> preemption_scope
        preemption_details                  -> preemption_details
        cba_preemption_context              -> cba_preemption_context
        amended_claim_context               -> amended_claim_context
        statutory_preclusion_context        -> statutory_preclusion_context
        employer_threshold_value            -> employer_threshold_value
        employer_threshold_unit             -> employer_threshold_unit
        employer_threshold_context          -> employer_threshold_context
        cure_period_value                   -> cure_period_value
        cure_period_unit                    -> cure_period_unit
        cure_period_context                 -> cure_period_context

    retaliation.
        constructive_discharge_standard     -> constructive_discharge_standard
        constructive_discharge_context      -> constructive_discharge_context
        anticipatory_retaliation_context    -> anticipatory_retaliation_context
        is_evidence_collection_protected    -> is_evidence_collection_protected
        cats_paw_liability_context          -> cats_paw_liability_context
        is_cats_paw_liability_extended      -> is_cats_paw_liability_extended
        third_party_retaliation_context     -> third_party_retaliation_context
        criminal_sanctions_context          -> criminal_sanctions_context
        criminal_sanctions                  -> criminal_sanctions
        has_blacklisting_protection         -> has_blacklisting_protection
        blacklisting_details                -> blacklisting_details

    process_remedies.
        primary_agency                      -> primary_agency
        local_agencies                      -> local_agencies
        federal_agencies                    -> federal_agencies
        enforcement_priority                -> enforcement_priority
        enforcement_channel                 -> enforcement_channel
        process_types                       -> process_types
        fee_shifting_rules                  -> fee_shifting_rules
        fee_shifting_rule_phases            -> fee_shifting_rule_phases
        remedies                            -> remedies
        remedy_details                      -> remedy_details
        remedy_limits                       -> remedy_limits
        remedy_liquidated_multiplier        -> remedy_liquidated_multiplier
        remedy_liquidated_context           -> remedy_liquidated_context
        mitigation_details                  -> mitigation_details
        mixed_motive_remedy_context         -> mixed_motive_remedy_context
        preliminary_reinstatement_context   -> preliminary_reinstatement_context
        preliminary_reinstatement_standard  -> preliminary_reinstatement_standard
        reinstatement_standard_details      -> reinstatement_standard_details
        preliminary_reinstatement_scope     -> preliminary_reinstatement_scope
        private_roa_context                 -> private_roa_context
        jury_trial_context                  -> jury_trial_context
        jury_trial_scope                    -> jury_trial_scope
        election_of_remedies_rules          -> election_of_remedies_rules
        election_of_remedies_context        -> election_of_remedies_context

    reward.
        has_reward                          -> has_reward
        reward_discretion_standard          -> reward_discretion_standard
        reward_discretion_formula           -> reward_discretion_formula
        reward_discretion_details           -> reward_discretion_details
        reward_details                      -> reward_details
        qui_tam_government_share            -> qui_tam_government_share
        qui_tam_relator_share               -> qui_tam_relator_share
        qui_tam_reduction_context           -> qui_tam_reduction_context
        qui_tam_share_context               -> qui_tam_share_context
        has_first_to_file_bar               -> has_first_to_file_bar
        first_to_file_context               -> first_to_file_context
        has_public_disclosure_bar           -> has_public_disclosure_bar
        public_disclosure_bar_context       -> public_disclosure_bar_context

    waiver_scope.
        proper_defendants                   -> proper_defendants
        proper_defendant_details            -> proper_defendant_details
        joint_employer_context              -> joint_employer_context
        sovereign_immunity_waiver           -> sovereign_immunity_waiver
        sovereign_immunity_limits           -> sovereign_immunity_limits
        sovereign_immunity_scope            -> sovereign_immunity_scope
        sovereign_immunity_details          -> sovereign_immunity_details
        contractual_waiver_context          -> contractual_waiver_context
        contractual_waiver_scope            -> contractual_waiver_scope
        waiver_of_collateral_claims_context -> waiver_of_collateral_claims_context
        nda_limits_context                  -> nda_limits_context
        anti_gag_provision_context          -> anti_gag_provision_context
        no_retaliatory_evidence_context     -> no_retaliatory_evidence_context
        stay_of_discipline_context          -> stay_of_discipline_context
        anti_slapp_protection_context       -> anti_slapp_protection_context
        anti_slapp_protection_scope         -> anti_slapp_protection_scope
        discovery_protection_context        -> discovery_protection_context
        settlement_restriction_context      -> settlement_restriction_context
        settlement_restriction_scope        -> settlement_restriction_scope
        individual_liability_context        -> individual_liability_context
        individual_liability_scope          -> individual_liability_scope
        successor_liability_context         -> successor_liability_context
        extraterritorial_context            -> extraterritorial_context
        civil_action_waiver_scope           -> civil_action_waiver_scope
        civil_action_waiver_context         -> civil_action_waiver_context
        class_action_waiver_context         -> class_action_waiver_context

    bop.
        employee_standards                  -> employee_standards
        employee_standard_details           -> employee_standard_details
        causation_standards                 -> causation_standards
        causation_standard_context          -> causation_standard_context
        causation_application               -> causation_application
        employer_knowledge_context          -> employer_knowledge_context
        employer_knowledge_scope            -> employer_knowledge_scope
        burden_shifting_framework           -> burden_shifting_framework
        burden_shifting_context             -> burden_shifting_context
        burden_shifting_details             -> burden_shifting_details
        employer_defenses                   -> employer_defenses
        employer_defense_details            -> employer_defense_details
        rebuttable_presumption_details      -> rebuttable_presumption_details
        presumption_window_value            -> presumption_window_value
        presumption_window_unit             -> presumption_window_unit
        presumption_window_context          -> presumption_window_context
        has_bop_details                     -> has_bop_details
        bop_details                         -> bop_details

    source_audit.
        last_reviewed_date                  -> last_reviewed_date
        url                                 -> url
        url_is_pdf                          -> url_is_pdf
        authority_reference                 -> authority_reference
        _review_notes                       -> _review_notes
        _reconciled_notes                   -> _reconciled_notes

integrity.
    has_anomalies                       -> ingest-only
    notations                           -> ingest-only
    notation_count                      -> ingest-only

```

---

## Common Fields (Apply To All 4 Legal Record Types)

These normalized canonical fields exist in every legal-record ACF.
Field order reflects logical editorial workflow within each tab.

---

### Identity And Publishing Tab

Fields ordered: identification → scope → curated

- `jurisdiction`                   — (single-select taxonomy: `WS_JURISDICTION_TAXONOMY`)
- `official_name`
- `common_name`
- `citation`                       — (statute citation / precedent case / case name (shared slot); yes: there will
                                      be a `ws_jx_citation_citation`)
- `protection_scope`               — (single-select taxonomy: `ws_protection_scope`)
- `general_description`            — (brief; reserve full summary for `plain_english_wysiwyg`)
- `has_attach_flag`                — (special-case; approved use of `has_*` bool; triggers display_order. Used
                                      together for attaching curated records to jx-summary at render)
- `display_order`                  — (conditional on `has_attach_flag` is true)

---

### Effective Date Tab

Fields ordered: related dates → retroactive

- `date`                           — (enacted / ruling / decision date (shared slot))
- `has_effective_date`             — (only when `effective_date` is specified and differs from `date`)
- `effective_date`
- `effective_year`                 — (derived from `effective_date` if present, `date` if not)
- `retro_date`                     — (sister field to `retro_context`)
- `retro_context`                  — (conditional on `retroactive-date` in `legal_recognitions`)

---

### Tab

Fields ordered: legal_recognitions → activity standard → disclosure →
classes → sectors → targets → recognitions

- `legal_recognitions`             — (taxonomy: `ws_legal_recognition`; replaces all `*_recognized` booleans,
                                      and other bools; See [Slug-to-Companion Map] below.)
- `manager_rule_exclusion_context` — (conditional on `manager-rule-exclusion` in `legal_recognitions`)
- `public_concern_context`         — (conditional on `public-concern-required` in `legal_recognitions`)
- `bad_faith_exclusion_context`    — (conditional on `bad-faith-exclusion` in `legal_recognitions`)
- `anonymity_protection_context`   — (conditional on `anonymity-protection` in `legal_recognitions`)
- `malicious_reporting_sanctions`  — (sister field to `malicious_reporting_context`; repeater: 
      ├── `sanction_conduct`             [select: `knowingly-false`|`reckless-disregard`|`bad-faith-motive`|
      │                                   `see-context`,
      └── `sanction_penalty`              select: `civil-fine`|`remedy-forfeiture`|`attorney-fee-shift`|
                                          `misdemeanor`|`felony`|`see-context`])
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
- `disclosure_targets`             — (taxonomy: `ws_disclosure_target`)
- `disclosure_channel_scope`       — (sister field of `disclosure_channel_context`; select: `any-channel`|
                                      `approved-channel-only`|`mandatory-internal-first`|`see-context`)
- `disclosure_format`              — (sister field of `disclosure_channel_context`; select: `written-only`|
                                      `oral-permitted`|`either`|`has-details`)
- `disclosure_format_details`
- `disclosure_channel_context`     — (conditional on `has-channel-requirement` in `protected_disclosures`)
- `ic_channel_sequence_context`    — (conditional on `ic-channel-required` in `protected_disclosures`; documents
                                      mandatory IC/national-security sequential channel requirement;
                                      going outside the chain destroys protection)
- `disclosure_target_details`

---

### Statute of Limitations & Thresholds Tab

Fields ordered: core SOL  → modifiers  → exhaustion  → thresholds  → preemption.

- `sol_value`                      — (integer)
- `sol_unit`                       — (select: `days`|`weeks`|`months`|`years`)
- `sol_trigger`                    — (select: `filing-of-complaint`|`accrual`|`discovery-actual`|
                                      `discovery-constructive`|`discovery-notice`|`conclusion-of-admin-process`|
                                      `see-context`)
- `sol_trigger_context`            — (conditional on `sol_trigger` is non-empty)
- `sol_trigger_event`              — (select: `notice-of-action`|`occurrence-of-action`|`discovery-of-harm`|
                                      `constructive-discharge-accrual`; when the clock starts, independent of
                                      how it runs)
- `has_sol_details`
- `sol_details`
- `sop_value`                      — (sister field to `statute_of_repose_context`; integer)
- `sop_unit`                       — (sister field to `statute_of_repose_context`; select: `days`|`weeks`|
                                      `months`|`years`)
- `is_sop_tolling_available`       — (sister field to `statute_of_repose_context`; very unlikely; true only when
                                      explicitly stated; use `statute_of_repose_context` to describe context)
- `statute_of_repose_context`      — (conditional on `statute-of-repose` in `legal_recognitions`)
- `statutory_tolling_context`      — (conditional on `statutory-tolling` in `legal_recognitions`)
- `equitable_tolling_context`      — (conditional on `equitable-tolling` in `legal_recognitions`)
- `cba_preemption_context`         — (conditional on `cba-grievance-preemption` in `legal_recognitions`)
- `amended_claim_context`          — (conditional on `amended-claim` in `legal_recognitions`)
- `exhaustion_required_class`      — (sister field to `exhaustion_required_context`; select:
                                      `jurisdictional`|`claims-processing`|`waivable`|`see-context`)
- `exhaustion_required_context`    — (conditional on `exhaustion-required` in `legal_recognitions`)
- `filing_notice_value`            — (sister field to `filing_notice_context`; integer)
- `filing_notice_unit`             — (sister field to `filing_notice_context`; select: `days`|`weeks`|`months`|
                                      `years`)
- `filing_notice_target`           — (sister field to `filing_notice_context`; select: `employer`|`agency`|
                                      `attorney-general`|`labor-board`|`see-context`)
- `filing_notice_context`          — (conditional on `pre-filing-notice` in `legal_recognitions`)
- `has_employer_threshold`
- `threshold_compare`              — (sister field to `employer_threshold_details`; select: `gte`|`lte`|
                                      `gt`|`lt`|`eq`)
- `threshold_value`                — (sister field to `employer_threshold_details`; integer)
- `threshold_unit`                 — (sister field to `employer_threshold_details`; select: `employees`|
                                      `contractors`|`workers`|`fte`)
- `employer_threshold_details`
- `has_cure_period`
- `cure_period_value`              — (sister field to `cure_period_details`; integer)
- `cure_period_unit`               — (sister field to `cure_period_details`; select: `days`|`weeks`|`months`|
                                      `years`)
- `cure_period_details`
- `has_preemption`
- `preemption_direction`           — (sister field to `preemption_details`; select:
                                      `federal-preempts-state`|`state-not-preempted`|`partial`|`see-details`)
- `preemption_details`

---

### Retaliation Tab

Fields ordered: adverse actions → recognitions → sanctions

- `adverse_actions`                   — (taxonomy: `ws_adverse_action`)
- `adverse_action_details`
- `adverse_action_scope`              — (select: `termination-only`|`material-adverse`|
                                         `broad-any-adverse-action`|`see-context`)
- `adverse_action_scope_context`      — (conditional on `adverse_action_scope` is non-empty)
- `constructive_discharge_standard`   — (sister field to `constructive_discharge_context`; select:
                                         `objective-intolerability`|`intent-required`|`dual-prong`|`see-context`)
- `constructive_discharge_context`    — (conditional on `constructive-discharge` in `adverse_actions`)
- `is_evidence_collection_protected`
- `anticipatory_retaliation_context`  — (conditional on `anticipatory-retaliation` in `adverse_actions`)
- `cats_paw_liability_context`        — (conditional on `cats-paw-liability` in `legal_recognitions`)
- `is_cats_paw_liability_extended`    — (sister field to `cats_paw_liability_context` AND conditional on `associates-*`
                                         in `protected_classes`; true only when explicitly extended to
                                         associates-of-whistleblower)
- `third_party_retaliation_context`   — (conditional on `third-party-retaliation` in `legal_recognitions`)
- `criminal_sanctions`                — (sister field to `criminal_sanctions_context`; repeater: 
      ├── `sanction_conduct`                [select: `retaliation`|`disclosure`|`false-report`|`obstruction`|
      │                                      `see-context`,
      └── `sanction_level`                   select: `misdemeanor`|`felony`|`see-context`])
- `criminal_sanctions_context`        — (conditional on `criminal-sanctions` in `legal_recognitions`)
- `has_blacklisting_protection`       — (true when statute explicitly extends protection against post-employment
                                         blacklisting by future employers; distinct from `blacklisting` in
                                         `adverse_actions` which captures the harm)
- `blacklisting_protection_details`

### Process & Remedies Tab

Fields ordered: process → fee shifting → remedies → enforcement

- `process_types`                  — (taxonomy: `ws_process_type`)
- `primary_agency`                 — (auto-fill by hook when first `ws-agency` added and value is empty)
- `local_agencies`                 — (multi-select: `ws-agency` posts filtered by jx, common process and
                                      common disclosure taxonomies)
- `enforcement_priority`           — (select: `agency-first`|`court-first`|`either`|`sequential`)
- `enforcement_channel`            — (priority of enforcement agencies, with any enforcement requirements)
- `private_roa_context`            — (conditional on `private-right-of-action` in `legal_recognitions`)
- `jury_trial_scope`               — (sister field to `jury_trial_context`; select: `all-claims`|
                                     `damages-only`|`liability-only`|`see-context`)
- `jury_trial_context`             — (conditional on `private-right-of-action` AND `jury-trial` in
                                      `legal_recognitions`)
- `fee_shifting_rules`             — (taxonomy: `ws_fee_shifting_rule`)
- `fee_shifting_rule_phases`       — (conditional on `fee_shifting_rules` includes `has-phases`)
- `fee_shifting_rule_details`
- `fee_shifting_asymmetry`         — (select: `one-way-plaintiff`|`one-way-defendant-frivolous`|
                                      `two-way`|`american-rule`|`has-details`)
- `fee_shifting_asymmetry_details`
- `remedies`                       — (taxonomy: `ws_remedy`)
- `remedy_limits`                  — (conditional on `remedies` includes `has-limits`)
- `remedy_details`
- `remedy_liquidated_multiplier`   — (conditional on `liquidated-damages` in `remedies`; select:
                                      `double`|`treble`|`2x-back-pay`|`2x-wages-lost`|`statutory-formula`|
                                      `statutory-daily-fine`|`up-to-double`|`up-to-treble`|`has-details`)
- `remedy_liquidated_formula`      — (conditional on `remedy_liquidated_multiplier` is `statutory-formula`)
- `remedy_liquidated_details`      — (conditional on `remedy_liquidated_multiplier` is `has-details`)
- `mitigation_required`            — (select: `yes-statutory`|`yes-common-law`|`no`|`has-details`)
- `mitigation_required_details`
- `preliminary_reinstatement_standard`  — (sister field to `preliminary_reinstatement_context`; select: `mandatory`|
                                           `discretionary`|`has-details`)
- `reinstatement_standard_details`      — (conditional on `preliminary_reinstatement_standard` is `has-details`)
- `preliminary_reinstatement_scope`     — (sister field to `preliminary_reinstatement_context`; select: `admin-phase`|
                                           `full-pendency`|`both`)
- `preliminary_reinstatement_context`   — (conditional on `preliminary-reinstatement` in `legal_recognitions`)
- `mixed_motive_remedy_context`    — (conditional on `burden_shifting_framework` is `mixed-motive`;
                                      see [Cross-Tab Conditional below])

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
- `sovereign_immunity_limits`      — (multi-select: `not-waived`|`partially-waived`|`fully-waived`|`cap-applies`|
                                      `conditions-apply`|`has-details`)
- `sovereign_immunity_scope`       — (sister field to `sovereign_immunity_limits_details`; select:
                                      `state-only`|`instrumentalities-included`|`political-subdivisions-included`|
                                      `all`|`see-details`)
- `sovereign_immunity_waiver`      — (sister field to `sovereign_immunity_limits_details`; select:
                                      `explicit-waiver`|`implied-waiver`|`none`|`not-applicable`)
- `sovereign_immunity_limits_details`
- `nda_limits_context`             — (conditional on `nda-limitations` in `legal_recognitions`)
- `anti_gag_provision_context`     — (conditional on `anti-gag-provision` in `legal_recognitions`)
- `no_retaliatory_evidence_context`      — (conditional on `no-retaliatory-evidence` in `legal_recognitions`)
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

### Burden Of Proof Tab

Fields ordered: framework → employee standards → causation → employer defenses  →
rebuttable presumption  → temporal presumption → detail overflow

- `has_burden_shifting`
- `burden_shifting_framework`      — (sister field to `burden_shifting_details`; select: `mcdonnell-douglas`|
                                      `motivating-factor`|`but-for`|`mixed-motive`|`see-context`;
                                      see [Cross-Tab Conditional] below)
- `burden_shifting_context`        — (conditional on `burden_shifting_framework` is non-empty)
- `burden_shifting_details`
- `employee_standards`             — (taxonomy: `ws_employee_standard`; evidentiary burden only)
- `employee_standard_details`
- `causation_standards`            — (taxonomy: `ws_causation_standard`; causal link standard)
- `causation_application`          — (sister field to `causation_standard_context`; multi-select: `liability`|
                                      `damages`|`both`|`has-details`; combo limited to `has-details` plus one other)
- `causation_application_details`
- `causation_standard_context`     — (conditional on `causation_standards` is non-empty)
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
- `presumption_effect`             — (sister field to `temporal_presumption_details`; select:
                                      `shifts-burden`|`creates-inference`|`rebuttable-presumption`|`has-details`)
- `presumption_effect_details`
- `temporal_presumption_details`
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
- `has_first_to_file_bar`          — (qui-tam only. True when a prior filing by another relator may
                                      bar this claim under the first-to-file rule)
- `first_to_file_context`          — (conditional on `has_first_to_file_bar` is true)
- `has_public_disclosure_bar`      — (qui-tam only. True when prior public disclosure may independently
                                      bar this claim; flag whenever `bounty-qui-tam-award` is in `remedies`
                                      to prevent false reads on claim availability)
- `public_disclosure_bar_context`  — (conditional on `has_public_disclosure_bar` is true)

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

#### Process & Remedies Tab (insert after `jury_trial_context`)

- `election_of_remedies_rules`     — (multi-select: `administrative-bars-civil`|`state-bars-federal`|
                                      `remedy-exclusivity`|`first-filed-controls`|`no-election-required`|
                                      `see-context`)
- `election_of_remedies_context`   — (conditional on `election_of_remedies_rules` is non-empty)

#### Relationships Tab

- `citation_ids`
- `construction_ids`

#### Hidden Fields

- `_precedent_ids`                 — (merged array of `citation_ids` and `construction_ids`; auto-fill by hook on save)

---

### Statute-Specific

#### Enforcement Tab

- `federal_agencies`               — (insert after `local_agencies`; multi-select: `ws-agency` posts filtered by jx,
                                      common process and common disclosure taxonomies)

#### Hidden Fields

- `_primary_agency_is_fed`         — (derived from `primary_agency` jx; auto-fill by hook on save)
- `_related_agencies`              — (merged array of `local_agencies` and `federal_agencies`; auto-fill by hook
                                      on save)

---

### Common-Law-Specific

#### Identity and Publishing Tab (insert after `citation`)

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

#### Statute of Limitations & Thresholds Tab (insert after `preemption_details`)

- `statutory_preclusion_context`   — (conditional on `statutory-preclusion` in `legal_recognitions`)

---

### Precedent-Record Common Fields (citation + construction)

Precedent records carry most common fields. Some notable exceptions are fields that are definitionally
inapplicable to court decisions:
- `election_of_remedies_rules`  — Legislative/Doctrinal construct; not a court ruling.
- `doctrine_*`, `public_policy_*`, `recognition_*`, etc. — Common-law-specific fields that have no precedent equivalent.
- `_precedent_ids`              — Precedent-Records have `_parent_ids`: `statute_ids`, `comlaw_ids` instead.
These fields do not appear on precedent-records.

#### Identity and Publishing Tab

- `status`                         — (select: `published`|`unpublished`|`memorandum`|`vacated`)
- `binding_scope`                  — (select: `binding`|`persuasive`|`mixed`|`distinguished`|`overruled`)
- `court`                          — (select: populate choices by hook on load/fill filter by jx)
- `court_details`                  — (conditional on `court` is `has-details`)
- `court_jx`                       — (sister field to `court_details`; taxonomy: `WS_JURISDICTION_TAXONOMY`,
                                      'load_terms' => 1, 'save_terms' => 0)
- `court_is_fed`                   — (derived from `court` `ws_jx_codes`; manually set when `court`
                                      is `has-details`)

#### Effective Date Tab (insert after `effective_year`)

- `mandate_date`

#### Classification Tab (insert after `legal_recognitions`)

- `scope`                          — (select: `favorable`|`adverse`|`neutral`)
- `extended_taxonomies`            — (conditional on `scope` is `favorable`; repeater;
                                      each row: taxonomy slug + term slug being added to parent's coverage)
- `suppressed_taxonomies`          — (conditional on `scope` is `adverse`; repeater;
                                      each row: taxonomy slug + term slug being removed from parent's coverage)
- `has_affected_jx`                — (derived from `court` `ws_jx_codes`; manually set false when single jx
                                      is same as precedent `jurisdiction`; manually set if true when
                                      `court`-`has-details` and covers multiple jx)
- `affected_jx`                    — (conditional on `has_affected_jx`; derived from `court` `ws_jx_codes`;
                                      manually set taxonomy: `WS_JURISDICTION_TAXONOMY`, 'load_terms' => 1,
                                      'save_terms' => 0, once `has_affected_jx` is true to apply affected jx
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

#### Enforcement Tab

- `federal_agencies`               — (insert after `local_agencies`; multi-select: `ws-agency` posts filtered by jx,
                                      common process and common disclosure taxonomies)

#### Source / Audit Tab (insert after `authority_reference`)

- `authority_source`               — (select: `constitutional`|`legislative`|`judicial`|`regulatory`|
                                      `executive`|`has-details`)
- `authority_source_details`
- `review_standard`                — (select: `de-novo`|`substantial-evidence`|`arbitrary-capricious`|
                                      `abuse-of-discretion`|`has-details`)
- `review_standard_details`

#### Hidden Fields

- `_primary_agency_is_fed`         — (derived from `primary_agency` jx; auto-fill by hook on save)
- `_related_agencies`              — (merged array of `local_agencies` and `federal_agencies`; auto-fill by hook
                                      on save)
- `_parent_ids`                    — (merged array of `statute_ids` and `comlaw_ids`; auto-fill by hook on save)

---

### Citation-Specific

#### Identity and Publishing Tab (insert after `citation`)
- `types`                          — (multi-select: `case-law`|`statute`|`regulatory`|`secondary`)
- `type_context`                   — (conditional on `types` is non-empty; provide context for `types` chosen)

---

### Construction-Specific

#### Identity and Publishing Tab
- `type`                           — (select: `case-law`|`statute`|`regulatory`|`secondary`)
- `is_en_banc`                     — (defaults true; when false, triggers `panel_composition_details`; approved use
                                      of `is_*` bool as trigger)
- `panel_composition_class`        — (sister field to `panel_composition_details`; select: `three-judge`|
                                      `five-judge`|`seven-judge`|`nine-judge`|`expanded-panel`|`judge`|
                                      `see-details`)
- `panel_composition_details`      — (conditional on `is_en_banc` is false; approved use of `is_*` bool as trigger)

---

## Rename Normalization (Current  → Canonical)

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
- `exhaustion_is_jurisdictional` (bool)      → `exhaustion_required_class`     (single select)
- `rebuttable_presumption`                   → `rebuttable_presumption_details`
- `has_statutory_preclusion`                 → `statutory-preclusion`           in `legal_recognitions`
- `doctrine_basis_wysiwyg`                   → `doctrine_basis`                (never was wysiwyg)
- `recognition_status_wysiwyg`               → `recognition_status`            (select) — (never was wysiwyg) + `recognition_status_details`(textarea)
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
- `continuing_violation_recognized`          → `continuing-violation-doctrine`           in `ws_legal_recognition`
- `equitable_tolling_recognized`             → `equitable-tolling`              in `ws_legal_recognition`
- `has_amended_claim_recognized`             → `amended-claim`                  in `ws_legal_recognition`
- `arbitration_waiver_enforceability`        → `contractual-waiver`             in `legal_recognitions`
- `disclosure_target_type`                   → `_disclosure_target_class`      (derived, hidden)
- `court_name`                               → `court_details`                 (uses `has-details` sentinel now) 
- `is_favorable` (bool)                      → `scope`                         (single select)
- `adverse_action_scope` (textarea)          → `adverse_action_scope`          (select) + `adverse_action_scope_context` (freetext)
- `doctrine_id`                              →  removed (visible dedupe IDs deemed unnecessary)
- `bop_flag`                                 →  removed (used by researchers only, never meant for ACF meta)
- `statute_id` + `comlaw_id` — singular      →  pluralized to support (rare-but-possible) multi-values

---

## Relationship Direction Contract (For Sync)

- Parent-bearing legal records: `citation`, `construction`.
- Child-bearing  legal records: `statute`, `common_law`.

---

### Cross-Tab Conditional: mixed-motive  → mixed_motive_remedy_context

When `burden_shifting_framework` (Burden Of Proof tab) is `mixed-motive`,
the field `mixed_motive_remedy_context` (Process & Remedies tab) becomes relevant.
ACF conditional logic cannot surface this cross-tab dependency natively.

Implementation: register an `acf/save_post` hook (or `admin_notices` hooked to
`current_screen`) that detects `mixed-motive` in `burden_shifting_framework` and
emits a dismissible admin notice directing the editor to the Process & Remedies tab:

> "Mixed-motive framework selected — please complete the 'Mixed Motive Remedy
>  Context' field on the Process & Remedies tab."

Notice should be 'informative' not 'pants-on-fire' and display on the edit screen
for all four legal record CPTs. Dismiss state does not need to persist — the notice
should reappear on each save as long as `mixed-motive` is present and
`mixed_motive_remedy_context` is empty.

---

## Slug-to-Companion Map (ws_legal_recognition taxonomy)

  * Used for bool-state values of Legal Recognitions where true when:
  * - Specified   — statute explicitly names or enumerates something
  * - Recognized  — judicial doctrine courts have affirmatively acknowledged
  * - Required    — mandatory obligation; non-compliance typically defeats the claim
  * - Applies     — statutory condition that operates by force of law when triggered
  * - Available   — mechanism or remedy that may be invoked but is not automatic
  * - Permitted   — right expressly allowed; cannot be waived or procedurally blocked
  * - Barred      — doctrine, action, or evidence explicitly excluded by law or rule
  * - Prohibited  — conduct expressly forbidden; violation triggers statutory liability
  * - Present     — clause or provision exists without implying judicial affirmation
  * - Sufficient  — condition independently meets the threshold for protection to attach

Conditional-Companion fields `*_context` noted with ' → ' are triggered by slug presence in `legal_recognitions`.
Sister fields noted by ' + ' inherit the conditional behavior, but are defined by the sibling.
Sister fields cannot appear without the triggered sibling being revealed.
Sister fields can (and usually do) appear before sibling.

```
// ── Effective Date Tab ──────────────────────────────────────────────────────
'retroactive-date'                    → 'retro_context'                     + 'retro_date'                       // Specified
// ── Tab ─────────────────────────────────────────────────────
'manager-rule-exclusion'              → 'manager_rule_exclusion_context'                                         // Applies
'public-concern-required'             → 'public_concern_context'                                                 // Applies
'bad-faith-exclusion'                 → 'bad_faith_exclusion_context'                                            // Applies
'malicious-reporting-sanctions'       → 'malicious_reporting_context'       + 'malicious_reporting_sanctions'    // Applies
'anonymity-protection'                → 'anonymity_protection_context'                                           // Recognized
'protected-action'                    → 'protected_action_context'          + 'protected_actions' + 'protected_action_standard'
                                                                            + 'protected_action_source'          // Specified
'excluded-class'                      → 'excluded_class_context'            + 'excluded_classes'                 // Specified
// ── Statute of Limitations Tab ──────────────────────────────────────────────
'statute-of-repose'                   → 'statute_of_repose_context'         + 'sop_value' + 'sop_unit'
                                                                            + 'is_sop_tolling_available'         // Specified
'statutory-tolling'                   → 'statutory_tolling_context'                                              // Specified
'equitable-tolling'                   → 'equitable_tolling_context'                                              // Recognized
'cba-grievance-preemption'            → 'cba_preemption_context'                                                 // Applies
'amended-claim'                       → 'amended_claim_context'                                                  // Recognized
'exhaustion-required'                 → 'exhaustion_required_context'       + 'exhaustion_required_class'        // Required
'pre-filing-notice'                   → 'filing_notice_context'             + 'filing_notice_target' + 'filing_notice_value'
                                                                            + 'filing_notice_unit'               // Required
'statutory-preclusion'                → 'statutory_preclusion_context'                                           // Applies
// ── Retaliation Tab ─────────────────────────────────────────────────────────
'cats-paw-liability'                  → 'cats_paw_liability_context'        + 'is_cats_paw_liability_extended'   // Recognized
'third-party-retaliation'             → 'third_party_retaliation_context'                                        // Prohibited
'criminal-sanctions'                  → 'criminal_sanctions_context'        + 'criminal_sanctions'               // Specified
// ── Process & Remedies Tab ──────────────────────────────────────────────────
'private-right-of-action'             → 'private_roa_context'                                                    // Available
'jury-trial'                          → 'jury_trial_context'                + 'jury_trial_scope'                 // Available
'preliminary-reinstatement'           → 'preliminary_reinstatement_context' + 'preliminary_reinstatement_standard' + 'reinstatement_standard_details'
                                                                            + 'preliminary_reinstatement_scope'  // Available
// ── Waiver & Scope Tab ──────────────────────────────────────────────────────                                  
'contractual-waiver'                  → 'contractual_waiver_context'        + 'contractual_waiver_scope'         // Recognized
'waiver-of-collateral-claims'         → 'waiver_of_collateral_claims_context'                                    // Applies
'nda-limitations'                     → 'nda_limits_context'                                                     // Recognized
'anti-gag-provision'                  → 'anti_gag_provision_context'                                             // Recognized
'no-retaliatory-evidence'             → 'no_retaliatory_evidence_context'                                        // Barred
'stay-of-disciplinary-action'         → 'stay_of_discipline_context'                                             // Available
'anti-slapp-protection'               → 'anti_slapp_protection_context'     + 'anti_slapp_protection_scope'      // Applies
'discovery-protection'                → 'discovery_protection_context'                                           // Applies
'confidential-settlement-restriction' → 'settlement_restriction_context'    + 'settlement_restriction_scope'     // Applies
'individual-liability'                → 'individual_liability_context'      + 'individual_liability_scope'       // Available
'successor-liability'                 → 'successor_liability_context'                                            // Recognized
'extraterritorial-coverage'           → 'extraterritorial_context'                                               // Recognized
// ── Burden of Proof Tab ─────────────────────────────────────────────────────
'employer-knowledge'                  → 'employer_knowledge_context'        + 'employer_knowledge_scope'         // Required
// ── Without Context (no tab) ────────────────────────────────────────────────
'catch-all-protection'                  — (no companion needed)   // Present
'internal-only-disclosure'              — (no companion needed)   // Sufficient
'trade-secret-immunity'                 — (no companion needed)   // Recognized
'continuing-violation-doctrine'                  — (no companion needed)   // Recognized
'prospective-whistleblower-protection'  — (no companion needed)   // Available
'temporal-proximity-sufficient'         — (no companion needed)   // Recognized
'sovereign-immunity-waiver'             — (no companion needed)   // Recognized
'class-action'                          — (no companion needed)   // Permitted

```

---

## Taxonomy Reference

### New Taxonomy Tables
- `ws_legal_recognition` — presence/absence signal table for recognized legal
  doctrines and procedural rules. Flat. Attached to all 4 legal CPTs. (built and assigned)
- `ws_causation_standard` — causation link standards, split from `ws_employee_standard`.
  Flat. Attached to all 4 legal CPTs. (built and assigned)
- `ws_protection_scope` — duplicate of `ws_procedure_type`; mostly for editorial use, not intended to render;
   but not forbidden to render.

Split taxonomy note: `ws_employee_standard` split to `ws_causation_standard`;
   They are sibling taxonomies covering distinct legal concepts.
- `ws_employee_standard` — evidentiary weight: how much proof, what quality.
  (preponderance, clear-and-convincing, contributing factor as BOP threshold)
- `ws_causation_standard` — causal logic: the relationship between disclosure
   and adverse action. (but-for, any-consideration, contributing factor as nexus)
The same underlying concept (e.g. contributing factor) may appear in both tables
   under different framing. This is intentional and legally correct.

---

## Notes

- This draft treats the statute set as baseline for broad legal parity, then adds per-type deltas.
- `ws_legal_recognition` is a presence/absence signal table, not a classification table.
- Fields marked (no companion needed) in the [Slug-to-Companion Map]
  ('catch-all-protection', 'internal-only-disclosure', 'trade-secret-immunity', 'continuing-violation-doctrine',
   'prospective-whistleblower-protection', 'temporal-proximity-sufficient', 'sovereign-immunity-waiver',
   'class-action') are captured exclusively via `ws_legal_recognition` taxonomy.
    No separate ACF field is registered for them. They are simply bool true if present in the array.
