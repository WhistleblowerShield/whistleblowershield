# Legal Record ACF Canonical Field Draft (v2.1)

Purpose: unified, prefix-free field set for all four legal record types
(`statute`, `common_law`, `citation`, `construction`) incorporating the
taxonomy overhaul, tab consolidation, and legal-nuance expansions identified
during v2.0 review. This is the working spec for the next ingest/render
rewrite cycle.

Notes: Do not update existing files. Rename existing files with `.txt` appended.
Create new files with same names as the originals.
Source note: the true legacy statute ACF source is
`plugins/ws-core-rewritten/includes/acf/acf-jx-statutes.legacy.20260429.php`.

## Naming Rules
**Casing**
Meta names (ACF `name` key): `snake_case` only.
Choice keys (select / multi-select option values): `kebab-case` only.

**CPT infix** — absent from this draft; applied at registration
CPT slot values: `statute` · `comlaw` · `citation` · `construction`
`name` (meta key): `ws_jx_statute_*` · `ws_jx_comlaw_*` · `ws_jx_citation_*` · `ws_jx_construction_*`
field `key`: `field_jx_statute_*` · `field_jx_comlaw_*` · `field_jx_citation_*` · `field_jx_construction_*`
tab field `key`: `field_jx_{cpt}_{tab}_tab` — tab label lowercase, no `_and_`, no symbols.
Approved abbreviations: `sol` = `statute_of_limitations` · `bop` = `burden_of_proof`
group `key`: `group_jx_statute_metadata` · `group_jx_comlaw_metadata` · `group_jx_citation_metadata` · `group_jx_construction_metadata`
group `menu_order`: < 85 — workflow groups occupy 85–99; CPT group must precede them.

**Reserved prefixes**
`ws_auto_` — written exclusively by hook logic (stamp, source, plain-English attribution). Never use on content fields.

**Cardinality**
Single-value fields: singular noun.
Multi-value fields (multi-select, repeater, array): plural noun.

**Booleans**
`has_*` — trigger boolean. True activates a companion or dependent field. May trigger `*_details`, another
field (e.g. `has_effective_date` triggers `effective_date`), or both.
`is_*` or `*_is_*` — state boolean. Describes a condition; does not imply a companion. An `is_*` field
may act as a trigger when documented inline as an approved case.

**Companion suffixes**
`*_details` — freetext (usually) companion. Two valid triggers:
When `has_field_name_details` is true, `field_name_details` is triggered, or
When `has-details` sentinel is present in trigger `field_name`, conditional `field_name_details` is triggered.
`has-*` sentinel or `has_*` bool can be used as a trigger for any conditional fields as `*_companion`, but
trigger and companion fields must share the same name, or condition logic must be well-documented.
Annotation not required when the field naming convention makes the trigger unambiguous. Annotation required
when the trigger `field_name` deviates from conditional `field_name`.

`*_context` — freetext (usually) companion. Triggered by a specific value or any non-empty value in a named
trigger field. Annotation always required.

**Sister fields**
A sister field shares a sibling's conditional but is not itself a `*_companion` field. Sisters inherit the sibling's
visibility — they appear when the sibling appears, hide when it hides — but are not independently conditional on
the sibling.
No naming convention applies to sisters. Use a logical name for the data it holds.
Sisters may appear before or after the sibling. Freetext occurs last usually, but no order is prescribed; use
best editorial logic.
A sister may not appear without a corresponding `*_companion` sibling in the same cluster.
Sister clusters can chain when multiple conditions layer. Chains get messy — use inline notes where they help.

**Avoid**
`*_recognized` — use a `ws_legal_recognition` taxonomy term instead where logical.
`*_type`  — prefer  `*_class` ,  `*_scope` ,  `*_status` ,  `*_rule` ,  `*_framework` ,  `*_weight` , or  `*_standard` .
Use  `*_type`  only when context requires it and no better suffix fits. Pluralize suffix accordingly.
`*_limitations` — use `*_limits` in meta names. No restriction on taxonomy slugs.

**Data-shape suffixes**  (e.g.,  `*_url` ,  `*_date` ,  `*_email` ,  `*_value` (int),  `*_unit` (calendar-unit select:  `days` | `weeks` | `months` | `years` ))
Apply when the field holds that data shape. Never apply otherwise.

**Sentinel Values**
Trigger sentinels: `has-details`, `has-limits`, `has-phases`, `has-channel-requirement`, `ic-channel-required`
Redirect sentinels: `see-details`, `see-context`

## Hook Requirements
**General**
Derived fields: auto-fill on load and on save.
Merged hidden fields (e.g., `_related_agencies`, `_precedent_ids`, `_parent_ids`): auto-fill on save.
Derived select choices (e.g., `court` filtered by `jurisdiction`): filter on field load.
Write unified hooks over duplicates. A single hook using `get_post_type()` is preferred over two
near-identical hooks per CPT. Reuse hooks wherever logical.

**Contradiction guards**
`fee_shifting_rules` — detect and flag contradictory terms.
`sovereign_immunity_limits` — detect and flag contradictory terms.
`causation_application` — enforce mutual exclusivity: `liability`, `damages`, and `both` must never appear
together. `has-details` may accompany any single primary value.
`contractual-waiver` — invalid when `civil_action_waiver_scope` is `anti`. When `anti` is set: remove
`contractual-waiver` from `legal_recognitions`, clear `contractual_waiver_context`, and clear any sisters.
`jury-trial` — invalid without `private-right-of-action` in `legal_recognitions`. When
`private-right-of-action` is absent: remove `jury-trial`, clear `jury_trial_context`, and clear any sisters.
`process_pathway` vs `exhaustion_required_class` — if `process_pathway` is `direct-court`, clear
`exhaustion_required_class` and `exhaustion_required_context`.

**Agency filtering**
`primary_agency` — auto-fill with the first attached `ws-agency` post when empty. Filter choices to
currently attached posts only.
`local_agencies` — filter to jx-applicable, non-federal `ws-agency` posts.
`federal_agencies` — filter to federal `ws-agency` posts only.

## Inline Field Descriptions
Default field types (by naming convention, unless stated otherwise):
`has_*` · `is_*` · `*_is_*` → boolean
`*_class` · `*_scope` · `*_status` · `*_rule` · `*_framework` · `*_weight` · `*_standard` → select
`*_share` — range/string
`*_compare` — select: `gte`|`lte`|`gt`|`lt`|`eq`
`*_formula` — string/calculation
`*_sanctions` — repeater
`*_application` — multi-select
`*_direction` — select
`*_bar` — select/bool
select → signals single-select; multi-select must be specified
all others → freetext

Default taxonomy field settings (unless stated otherwise):
Field type: taxonomy, multi-select, `load_terms`: 1, `save_terms`: 1

Conditional annotation phrasing — four accepted forms:
Taxonomy term present:  `conditional on slug in taxonomy_field`
Any non-empty value:    `conditional on trigger_field is non-empty`
Specific value in select field:        `conditional on trigger_field is trigger_value`
Specific value in multi-select field:  `conditional on trigger_field includes trigger_value`
Compound conditions: AND / OR / NOT (all-caps).

## Attached Workflow Groups
Four shared ACF groups attach to all four legal record types alongside the CPT-specific group.
Defined in `includes/acf/workflow/` — do not duplicate any of these fields in CPT-specific ACF files.
| Group key | menu_order | Tab label | Fields added |
| --- | --- | --- | --- |
| group_plain_english_metadata | 85 | Plain-English | ws_has_plain_english , ws_plain_english_wysiwyg , ws_plain_english_reviewed , 4 ws_auto_ stamps |
| group_auto_stamp_metadata | 90 | Authorship & Review | ws_auto_create_date , ws_auto_create_author , ws_auto_last_edited_date , ws_auto_last_edited_author |
| group_source_verify_metadata | 95 | Source & Verification | ws_auto_source_method , ws_auto_source_name , ws_auto_verified_by , ws_auto_verified_date , ws_verification_status , ws_needs_review |
| group_major_edit_metadata | 99 | Major Edit | ws_is_major_edit , ws_major_edit_description , ws_major_edit_update_type |

## Prompt Schema -> ACF Field Mapping
Maps phase-1 reconciler research JSON into the canonical statute ACF model.
Use the JSON key when `legacy_key` is present; otherwise use the canonical ACF `name`.

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

    classifications. (Plain English Summaries)
        protections_summary                 -> identity.general_description
        protected_disclosures               -> protected_disclosures
        protected_activities                -> protected_actions

integrity.
    has_anomalies                           -> ingest-only
    notations                               -> ingest-only
    notation_count                          -> ingest-only

```


## Common Fields (Apply To All 4 Legal Record Types)
Fields ordered to reflect logical editorial workflow within each tab.
The 12-tab structure has been consolidated into 8 editorial domains for workflow clarity, query-layer alignment, and Phase 2 filter sanity. All conditional logic, sister-field relationships, and taxonomy triggers are preserved.

### Tab 1: Identity & Status
Fields ordered: identification → scope → curated → temporal metadata
`jurisdiction`                   — (single-select taxonomy: `WS_JURISDICTION_TAXONOMY`)
`official_name`
`common_name`
`citation`                       — (statute citation / precedent case / case name (shared slot))
`protection_scope`               — (single-select taxonomy: `ws_protection_scope`)
`general_description`            — (brief; reserve full summary for `plain_english_wysiwyg`)
`has_attach_flag`                — (triggers display_order)
`display_order`                  — (conditional on `has_attach_flag` is true)
`date`                           — (enacted / ruling / decision date (shared slot))
`has_effective_date`             — (only when `effective_date` differs from `date`)
`effective_date`
`effective_year`                 — (derived from `effective_date` if present, `date` if not)
`retro_date`                     — (sister field to `retro_context`)
`retro_context`                  — (conditional on `retroactive-date` in `legal_recognitions`)

### Tab 2: Coverage & Protected Scope
Fields ordered: legal_recognitions → activity standard → disclosure → classes → sectors → targets
`legal_recognitions`             — (taxonomy: `ws_legal_recognition`)
`manager_rule_exclusion_context` — (conditional on `manager-rule-exclusion` in `legal_recognitions`)
`public_concern_context`         — (conditional on `public-concern-required` in `legal_recognitions`)
`bad_faith_exclusion_context`    — (conditional on `bad-faith-exclusion` in `legal_recognitions`)
`anonymity_protection_context`   — (conditional on `anonymity-protection` in `legal_recognitions`)
`malicious_reporting_sanctions`  — (sister field to `malicious_reporting_context`; repeater)
`malicious_reporting_context`    — (conditional on `malicious-reporting-sanctions` in `legal_recognitions`)
`protected_action_standard`      — (sister field to `protected_action_context`; select)
`reasonable_belief_scope`        — (sister field to `reasonable_belief_context`; select)
`reasonable_belief_context`      — (conditional on `protected_action_standard` is `reasonable-belief`)
`protected_action_source`        — (sister field to `protected_action_context`; multi-select)
`protected_actions`              — (sister field to `protected_action_context`; taxonomy: `ws_protected_action`)
`protected_action_context`       — (conditional on `protected-action` in `legal_recognitions`)
`garcetti_exception_context`     — (conditional on `employment_sectors` includes `public-sector` OR `manager-rule-exclusion` in `legal_recognitions`)
`protected_disclosures`          — (taxonomy: `ws_protected_disclosure`)
`protected_classes`              — (taxonomy: `ws_protected_class`)
`former_employee_context`        — (conditional on `former-employee` in `protected_classes`)
`protected_class_details`        — (conditional on `protected_classes` includes `has-details`)
`excluded_classes`               — (sister field to `excluded_class_context`; taxonomy: `ws_excluded_class`)
`excluded_class_context`         — (conditional on `excluded-class` in `legal_recognitions`)
`excluded_class_details`         — (conditional on `excluded_classes` includes `has-details`)
`employment_sectors`             — (taxonomy: `ws_employment_sector`)
`disclosure_targets`             — (taxonomy: `ws_disclosure_target`)
`disclosure_channel_scope`       — (sister field of `disclosure_channel_context`; select)
`disclosure_format`              — (sister field of `disclosure_channel_context`; select)
`disclosure_format_details`      — (conditional on `disclosure_format` is `has-details`)
`disclosure_channel_context`     — (conditional on `has-channel-requirement` in `protected_disclosures`)
`ic_channel_sequence_context`    — (conditional on `ic-channel-required` in `protected_disclosures`)
`disclosure_target_details`      — (conditional on `disclosure_targets` includes `has-details`)

### Tab 3: Adverse Actions & Retaliation
Fields ordered: adverse actions → recognitions → sanctions
`adverse_actions`                — (taxonomy: `ws_adverse_action`)
`adverse_action_details`         — (conditional on `adverse_actions` includes `has-details`)
`adverse_action_scope`           — (select: `termination-only`|`material-adverse`|`broad-any-adverse-action`|`see-context`)
`adverse_action_scope_context`   — (conditional on `adverse_action_scope` is non-empty)
`constructive_discharge_standard`— (sister field to `constructive_discharge_context`; select)
`constructive_discharge_context` — (conditional on `constructive-discharge` in `adverse_actions`)
`is_evidence_collection_protected`
`anticipatory_retaliation_context`— (conditional on `anticipatory-retaliation` in `adverse_actions`)
`cats_paw_liability_context`     — (conditional on `cats-paw-liability` in `legal_recognitions`)
`is_cats_paw_liability_extended` — (sister field to `cats_paw_liability_context` AND conditional on `associates-*` in `protected_classes`)
`third_party_retaliation_context`— (conditional on `third-party-retaliation` in `legal_recognitions`)
`criminal_sanctions`             — (sister field to `criminal_sanctions_context`; repeater)
`criminal_sanctions_context`     — (conditional on `criminal-sanctions` in `legal_recognitions`)
`has_blacklisting_protection`
`blacklisting_protection_details`— (conditional on `has_blacklisting_protection` is true)

### Tab 4: Enforcement & Procedure
Fields ordered: core SOL → modifiers → exhaustion/pathways → thresholds → federal/state interaction → process routing
`sol_value`                      — (integer)
`sol_unit`                       — (select: `days`|`weeks`|`months`|`years`)
`sol_trigger`                    — (select: `filing-of-complaint`|`accrual`|`discovery-actual`|
                                      `discovery-constructive`|`discovery-notice`|`conclusion-of-admin-process`|
                                      `see-context`)
`sol_trigger_context`            — (conditional on `sol_trigger` is non-empty)
`sol_trigger_event`              — (select: `notice-of-action`|`occurrence-of-action`|`discovery-of-harm`|
                                      `constructive-discharge-accrual`; when the clock starts, independent of
                                      how it runs)
`has_sol_details`
`sol_details`                    — (conditional on `has_sol_details` is true)
`sop_value`                      — (sister field to `statute_of_repose_context`; integer)
`sop_unit`                       — (sister field to `statute_of_repose_context`; select)
`is_sop_tolling_available`       — (sister field to `statute_of_repose_context`; bool)
`statute_of_repose_context`      — (conditional on `statute-of-repose` in `legal_recognitions`)
`statutory_tolling_context`      — (conditional on `statutory-tolling` in `legal_recognitions`)
`equitable_tolling_context`      — (conditional on `equitable-tolling` in `legal_recognitions`)
`exhaustion_required_class`      — (sister field to `exhaustion_required_context`; select)
`exhaustion_required_context`    — (conditional on `exhaustion-required` in `legal_recognitions`)
`process_pathway`                — (select: `agency-first-mandatory`|`direct-court`|`hybrid-right-to-sue-on-inaction`|`see-context`)
`agency_inaction_triggers_suit`  — (bool; sister to `process_pathway`)
`suspends_sol_during_admin`      — (bool; true when SOL is expressly tolled during administrative pendency)
`filing_notice_value`            — (sister field to `filing_notice_context`; integer)
`filing_notice_unit`             — (sister field to `filing_notice_context`; select)
`filing_notice_target`           — (sister field to `filing_notice_context`; select)
`filing_notice_context`          — (conditional on `pre-filing-notice` in `legal_recognitions`)
`has_employer_threshold`
`threshold_compare`              — (sister field to `employer_threshold_details`; select)
`threshold_value`                — (sister field to `employer_threshold_details`; integer)
`threshold_unit`                 — (sister field to `employer_threshold_details`; select)
`employer_threshold_details`     — (conditional on `has_employer_threshold` is true)
`has_cure_period`
`cure_period_value`              — (sister field to `cure_period_details`; integer)
`cure_period_unit`               — (sister field to `cure_period_details`; select)
`cure_period_details`            — (conditional on `has_cure_period` is true)
`federal_state_interaction`      — (select: `express-preemption`|`savings-clause-preserves-state`|`concurrent-enforcement`|`field-preemption`|`state-exceeds-federal-floor`|`see-context`)
`savings_clause_context`         — (conditional on `federal_state_interaction` is `savings-clause-preserves-state` OR `savings-clause` in `legal_recognitions`)
`interaction_details`            — (conditional on `federal_state_interaction` is non-empty)
`cba_preemption_context`         — (conditional on `cba-grievance-preemption` in `legal_recognitions`)
`amended_claim_context`          — (conditional on `amended-claim` in `legal_recognitions`)
`process_types`                  — (taxonomy: `ws_process_type`)
`primary_agency`                 — (auto-fill by hook)
`local_agencies`                 — (multi-select: `ws-agency`)
`federal_agencies`               — (multi-select: `ws-agency`)
`enforcement_priority`           — (select)
`enforcement_channel`            — (textarea)
`private_roa_context`            — (conditional on `private-right-of-action` in `legal_recognitions`)
`jury_trial_scope`               — (sister field to `jury_trial_context`; select)
`jury_trial_context`             — (conditional on `private-right-of-action` AND `jury-trial` in `legal_recognitions`)
`mixed_motive_remedy_context`    — (conditional on `burden_shifting_framework` is `mixed-motive`)

### Tab 5: Burden of Proof & Causation
Fields ordered: framework → employee standards → causation → employer defenses → rebuttable presumption → temporal presumption → detail overflow
`has_burden_shifting`
`burden_shifting_framework`      — (sister field to `burden_shifting_details`; select)
`burden_shifting_context`        — (conditional on `burden_shifting_framework` is non-empty)
`burden_shifting_details`
`same_decision_standard`         — (select: `preponderance`|`clear-and-convincing`|`not-available`|`see-context`; conditional on `employer_defenses` includes `same-decision-defense` OR `same-decision-clear-convincing`)
`causal_nexus_statutory_text`    — (conditional on `causation_standards` is non-empty)
`employee_standards`             — (taxonomy: `ws_employee_standard`)
`employee_standard_details`      — (conditional on `employee_standards` includes `has-details`)
`causation_standards`            — (taxonomy: `ws_causation_standard`)
`causation_application`          — (sister field to `causation_standard_context`; multi-select)
`causation_application_details`  — (conditional on `causation_application` includes `has-details`)
`causation_standard_context`     — (conditional on `causation_standards` is non-empty)
`employer_knowledge_scope`       — (sister field to `employer_knowledge_context`; select)
`employer_knowledge_scope_details`— (conditional on `employer_knowledge_scope` is `has-details`)
`employer_knowledge_context`     — (conditional on `employer-knowledge` in `legal_recognitions`)
`employer_defenses`              — (taxonomy: `ws_employer_defense`)
`employer_defense_details`       — (conditional on `employer_defenses` includes `has-details`)
`has_rebuttable_presumption`
`rebuttable_presumption_details` — (conditional on `has_rebuttable_presumption` is true)
`has_temporal_presumption`
`presumption_window_value`       — (sister field to `temporal_presumption_details`)
`presumption_window_unit`        — (sister field to `temporal_presumption_details`; select)
`presumption_effect`             — (sister field to `temporal_presumption_details`; select)
`presumption_effect_details`     — (conditional on `presumption_effect` is `has-details`)
`temporal_presumption_details`   — (conditional on `has_temporal_presumption` is true)
`temporal_proximity_value`       — (sister field to `temporal_proximity_context`)
`temporal_proximity_unit`        — (sister field to `temporal_proximity_context`; select)
`temporal_proximity_context`     — (conditional on `temporal-proximity-sufficient` in `legal_recognitions`)
`has_bop_details`
`bop_details`                    — (conditional on `has_bop_details` is true)

### Tab 6: Remedies & Outcomes
Fields ordered: fee shifting → remedies/caps → mitigation/interest → reinstatement → election → rewards/qui tam
`fee_shifting_rules`             — (taxonomy: `ws_fee_shifting_rule`)
`fee_shifting_rule_phases`       — (conditional on `fee_shifting_rules` includes `has-phases`)
`fee_shifting_rule_details`      — (conditional on `fee_shifting_rules` includes `has-details`)
`fee_shifting_asymmetry`         — (select)
`fee_shifting_asymmetry_details` — (conditional on `fee_shifting_asymmetry` is `has-details`)
`remedies`                       — (taxonomy: `ws_remedy`)
`remedy_limits`                  — (conditional on `remedies` includes `has-limits`)
`remedy_details`                 — (conditional on `remedies` includes `has-details`)
`remedy_liquidated_multiplier`   — (conditional on `liquidated-damages` in `remedies`; select)
`remedy_liquidated_formula`      — (conditional on `remedy_liquidated_multiplier` is `statutory-formula`)
`remedy_liquidated_details`      — (conditional on `remedy_liquidated_multiplier` is `has-details`)
`remedy_caps`                    — (repeater: `cap_type`, `employer_threshold`, `max_amount`, `applies_to`)
`remedy_caps_context`            — (conditional on `remedy_caps` is non-empty OR `remedies` includes `has-limits`)
`mitigation_required`            — (select: `yes-statutory`|`yes-common-law`|`no`|`has-details`)
`mitigation_required_details`    — (conditional on `mitigation_required` is `has-details`)
`mitigation_exception_context`   — (conditional on `mitigation_required` is `no`)
`interest_provision`             — (select: `none`|`pre-judgment-statutory`|`post-judgment`|`discretionary`|`see-context`)
`interest_provision_context`     — (conditional on `interest_provision` is non-empty)
`preliminary_reinstatement_standard`— (sister field to `preliminary_reinstatement_context`; select)
`reinstatement_standard_details` — (conditional on `preliminary_reinstatement_standard` is `has-details`)
`preliminary_reinstatement_scope`— (sister field to `preliminary_reinstatement_context`; select)
`preliminary_reinstatement_context`— (conditional on `preliminary-reinstatement` in `legal_recognitions`)
`election_of_remedies_rules`     — (multi-select)
`election_of_remedies_context`   — (conditional on `election_of_remedies_rules` is non-empty)
`has_reward`
`reward_discretion_standard`     — (sister field to `reward_details`; select)
`reward_discretion_formula`      — (conditional on `reward_discretion_standard` is `formula-based`)
`reward_discretion_details`      — (conditional on `reward_discretion_standard` is `has-details`)
`reward_details`                 — (conditional on `has_reward` is true)
`qui_tam_government_share`       — (sister field to `qui_tam_share_context`)
`qui_tam_relator_share`          — (sister field to `qui_tam_share_context`)
`qui_tam_reduction_context`      — (sister field to `qui_tam_share_context`)
`qui_tam_share_context`          — (conditional on `qui-tam` in `process_types`)
`has_first_to_file_bar`
`first_to_file_context`          — (conditional on `has_first_to_file_bar` is true)
`has_public_disclosure_bar`
`public_disclosure_bar_context`  — (conditional on `has_public_disclosure_bar` is true)

### Tab 7: Limitations, Defenses & Bars
Fields ordered: contractual → recognitions → immunity → defendants → discovery/settlement → successor/extraterritorial
`civil_action_waiver_scope`      — (select)
`civil_action_waiver_context`    — (conditional on `civil_action_waiver_scope` is non-empty)
`contractual_waiver_scope`       — (sister field to `contractual_waiver_context`; select)
`contractual_waiver_context`     — (conditional on `civil_action_waiver_scope` NOT `anti` AND `contractual-waiver` in `legal_recognitions`)
`waiver_of_collateral_claims_context`  — (conditional on `waiver-of-collateral-claims` in `legal_recognitions`)
`class_action_waiver_context`    — (conditional on `class-action-waiver` in `legal_recognitions`)
`proper_defendants`              — (multi-select)
`proper_defendant_details`       — (conditional on `proper_defendants` includes `has-details`)
`joint_employer_context`         — (conditional on `proper_defendants` includes `joint-employer` OR `staffing-agency`)
`individual_liability_scope`     — (sister of `individual_liability_context`; multi-select)
`individual_liability_context`   — (conditional on `individual-liability` in `legal_recognitions`)
`sovereign_immunity_limits`      — (multi-select)
`sovereign_immunity_scope`       — (sister field to `sovereign_immunity_limits_details`; select)
`sovereign_immunity_waiver`      — (sister field to `sovereign_immunity_limits_details`; select)
`sovereign_immunity_limits_details`— (conditional on `sovereign_immunity_limits` is `has-details`)
`nda_limits_context`             — (conditional on `nda-limitations` in `legal_recognitions`)
`anti_gag_provision_context`     — (conditional on `anti-gag-provision` in `legal_recognitions`)
`no_retaliatory_evidence_context`  — (conditional on `no-retaliatory-evidence` in `legal_recognitions`)
`stay_of_discipline_context`     — (conditional on `stay-of-disciplinary-action` in `legal_recognitions`)
`anti_slapp_protection_scope`    — (sister field to `anti_slapp_protection_context`; select)
`anti_slapp_protection_context`  — (conditional on `anti-slapp-protection` in `legal_recognitions`)
`discovery_protection_context`   — (conditional on `discovery-protection` in `legal_recognitions`)
`settlement_restriction_scope`   — (sister field to `settlement_restriction_context`; select)
`settlement_restriction_context` — (conditional on `confidential-settlement-restriction` in `legal_recognitions`)
`successor_liability_context`    — (conditional on `successor-liability` in `legal_recognitions`)
`extraterritorial_context`       — (conditional on `extraterritorial-coverage` in `legal_recognitions`)

### Tab 8: Relationships & Precedent
Fields ordered: reference → related legal records
`ref_materials`                  — (array; post object; `ws-reference`)
`overruled_by_id`                — (post object; legal-record)
`citation_ids`
`construction_ids`

## Specialized Fields By Legal Record Type
**Substantive-Record Common Fields (statute + common_law)**
Identity and Publishing Tab (insert after `citation`)
`precedent_common`               — (common name for precedent case held in field `citation`)
Classification Tab (insert after `excluded_class_details`)
`doctrine_basis`                 — (legal basis for the doctrine)
`public_policy_sources`           — (multi-select)
`policy_source_details`          — (conditional on `public_policy_sources` includes `has-details`)
`recognition_status`             — (select)
`recognition_status_details`     — (conditional on `recognition_status` is `has-details`)
Enforcement & Procedure Tab (insert after `interaction_details`)
`statutory_preclusion_context`   — (conditional on `statutory-preclusion` in `legal_recognitions`)

**Hidden Fields**
`_disclosure_target_class`       — (derived from `disclosure_targets`; auto-fill on save)
`_precedent_ids`                 — (merged array of `citation_ids` and `construction_ids`; auto-fill on save)
`_primary_agency_is_fed`         — (derived from `primary_agency` jx; auto-fill on save)
`_related_agencies`              — (merged array of `local_agencies` and `federal_agencies`; auto-fill on save)

**Precedent-Record Common Fields (citation + construction)**
Identity and Publishing Tab
`status`                         — (select)
`binding_scope`                  — (select)
`court`                          — (select: populate by hook)
`court_details`                  — (conditional on `court` is `has-details`)
`court_jx`                       — (sister field to `court_details`; taxonomy)
`court_is_fed`                   — (derived)
Effective Date Tab (insert after `effective_year`)
`mandate_date`
Classification Tab (insert after `legal_recognitions`)
`scope`                          — (select: `favorable`|`adverse`|`neutral`)
`extended_taxonomies`            — (conditional on `scope` is `favorable`; repeater)
`suppressed_taxonomies`          — (conditional on `scope` is `adverse`; repeater)
`has_affected_jx`                — (derived)
`affected_jx`                     — (conditional on `has_affected_jx`)
Relationships Tab
`statute_ids`
`comlaw_ids`
`parent_weight`                  — (select)
`has_negative_treatment`
`negative_treatment_class`        — (sister field to `negative_treatment_details`; select)
`negative_treatment_class_details`
`negative_treatment_details`
Source / Audit Tab (insert after `authority_reference`)
`authority_source`               — (select)
`authority_source_details`       — (conditional on `authority_source` is `has-details`)
`review_standard`                — (select)
`review_standard_details`        — (conditional on `review_standard` is `has-details`)
Hidden Fields
`_parent_ids`                    — (merged array of `statute_ids` and `comlaw_ids`; auto-fill on save)

**Citation-Specific**
Identity and Publishing Tab (insert after `citation`)
`types`                          — (multi-select)
`type_context`                   — (conditional on `types` is non-empty)

**Construction-Specific**
Identity and Publishing Tab
`type`                           — (select)
`is_en_banc`                     — (bool; trigger)
`panel_composition_class`         — (sister field to `panel_composition_details`; select)
`panel_composition_details`      — (conditional on `is_en_banc` is false)

## Rename Normalization (Current → Canonical)
`fee_shiftings`                            → `fee_shifting_rules`
`ws_fee_shifting`                          → `ws_fee_shifting_rule` (taxonomy table)
`has_limit_ambiguous`                      → `has_sol_details`
`limit_details`                            → `sol_details`
`has_tolling_details`                      → split into `statutory-tolling` and `equitable-tolling` in `legal_recognitions`
`tolling_details`                          → split into `statutory_tolling_context` and `equitable_tolling_context`
`has_exhaustion_required`                  → `exhaustion-required` in `legal_recognitions`
`exhaustion_details`                       → `exhaustion_required_context`
`exhaustion_is_jurisdictional` (bool)      → `exhaustion_required_class` (single select)
`rebuttable_presumption`                   → `rebuttable_presumption_details`
`has_statutory_preclusion`                 → `statutory-preclusion` in `legal_recognitions`
`doctrine_basis_wysiwyg`                   → `doctrine_basis`
`recognition_status_wysiwyg`               → `recognition_status` + `recognition_status_details`
`other_sources`                            → `policy_source_details`
`doctrine_name`                            → `official_name`
`statute_citation` / `precedent_name` / `case_name` / `case_citation` → `citation`
`enacted_date` / `ruling_date` / `decision_date` → `date`
`statute_url` / `precedent_url` / `citation_url` / `construction_url` → `url`
`statute_url_is_pdf` / `precedent_url_is_pdf` / `citation_url_is_pdf` / `construction_url_is_pdf` → `url_is_pdf`
`superseded_by`                            → `overruled_by_id`
`has_constructive_discharge_recognized`    → `constructive-discharge` in `adverse_actions`
`has_anticipatory_retaliation_recognized`  → `anticipatory-retaliation` in `adverse_actions`
`continuing_violation_recognized`          → `continuing-violation-doctrine` in `ws_legal_recognition`
`equitable_tolling_recognized`             → `equitable-tolling` in `ws_legal_recognition`
`has_amended_claim_recognized`             → `amended-claim` in `legal_recognitions`
`arbitration_waiver_enforceability`        → `contractual-waiver` in `legal_recognitions`
`disclosure_target_type`                   → `_disclosure_target_class` (derived, hidden)
`court_name`                               → `court_details`
`is_favorable` (bool)                      → `scope` (single select)
`adverse_action_scope` (textarea)          → `adverse_action_scope` (select) + `adverse_action_scope_context` (freetext)
`doctrine_id`                              → removed
`bop_flag`                                 → removed
`statute_id` + `comlaw_id` — singular      → pluralized
`preemption_direction`                     → `federal_state_interaction`

## Relationship Direction Contract (For Sync)
Parent-bearing legal records: `citation`, `construction`.
Child-bearing legal records: `statute`, `common_law`.

## Cross-Tab Conditional: mixed-motive → mixed_motive_remedy_context
When `burden_shifting_framework` (Tab 5) is `mixed-motive`, the field `mixed_motive_remedy_context` (Tab 4) becomes relevant.
Implementation: register an `acf/save_post` hook or `admin_notices` that detects `mixed-motive` and emits a dismissible admin notice directing the editor to Tab 4.
Notice reappears on each save as long as `mixed-motive` is present and `mixed_motive_remedy_context` is empty.

## Slug-to-Companion Map (ws_legal_recognition taxonomy)
// ── Identity & Status Tab ──────────────────────────────────────────────────
'retroactive-date'                    → 'retro_context' + 'retro_date'
// ── Coverage & Protected Scope Tab ─────────────────────────────────────────
'manager-rule-exclusion'              → 'manager_rule_exclusion_context'
'public-concern-required'             → 'public_concern_context'
'bad-faith-exclusion'                 → 'bad_faith_exclusion_context'
'malicious-reporting-sanctions'       → 'malicious_reporting_context' + 'malicious_reporting_sanctions'
'anonymity-protection'                → 'anonymity_protection_context'
'protected-action'                    → 'protected_action_context' + 'protected_actions' + 'protected_action_standard' + 'protected_action_source'
'excluded-class'                      → 'excluded_class_context' + 'excluded_classes'
'garcetti-exception'                  → 'garcetti_exception_context'
// ── Adverse Actions & Retaliation Tab ──────────────────────────────────────
'cats-paw-liability'                  → 'cats_paw_liability_context' + 'is_cats_paw_liability_extended'
'third-party-retaliation'             → 'third_party_retaliation_context'
'criminal-sanctions'                  → 'criminal_sanctions_context' + 'criminal_sanctions'
// ── Enforcement & Procedure Tab ────────────────────────────────────────────
'statute-of-repose'                   → 'statute_of_repose_context' + 'sop_value' + 'sop_unit' + 'is_sop_tolling_available'
'statutory-tolling'                   → 'statutory_tolling_context'
'equitable-tolling'                   → 'equitable_tolling_context'
'exhaustion-required'                 → 'exhaustion_required_context' + 'exhaustion_required_class'
'pre-filing-notice'                   → 'filing_notice_context' + 'filing_notice_target' + 'filing_notice_value' + 'filing_notice_unit'
'statutory-preclusion'                → 'statutory_preclusion_context'
'private-right-of-action'             → 'private_roa_context'
'jury-trial'                          → 'jury_trial_context' + 'jury_trial_scope'
'preliminary-reinstatement'           → 'preliminary_reinstatement_context' + 'preliminary_reinstatement_standard' + 'reinstatement_standard_details' + 'preliminary_reinstatement_scope'
'cba-grievance-preemption'            → 'cba_preemption_context'
'amended-claim'                       → 'amended_claim_context'
'savings-clause'                      → 'savings_clause_context'
'agency-inaction-triggers-suit'       → (sister to process_pathway, bool)
'federal-concurrent-enforcement'      → (signals interaction_details)
'state-floor-exceeds-federal'         → (signals interaction_details)
// ── Burden of Proof & Causation Tab ────────────────────────────────────────
'employer-knowledge'                  → 'employer_knowledge_context' + 'employer_knowledge_scope'
'same-decision-defense'               → 'same_decision_standard'
'temporal-proximity-sufficient'       → 'temporal_proximity_context' + 'temporal_proximity_value' + 'temporal_proximity_unit'
// ── Limitations, Defenses & Bars Tab ───────────────────────────────────────
'contractual-waiver'                  → 'contractual_waiver_context' + 'contractual_waiver_scope'
'waiver-of-collateral-claims'         → 'waiver_of_collateral_claims_context'
'class-action-waiver'                 → 'class_action_waiver_context'
'nda-limitations'                     → 'nda_limits_context'
'anti-gag-provision'                  → 'anti_gag_provision_context'
'no-retaliatory-evidence'             → 'no_retaliatory_evidence_context'
'stay-of-disciplinary-action'         → 'stay_of_discipline_context'
'anti-slapp-protection'               → 'anti_slapp_protection_context' + 'anti_slapp_protection_scope'
'discovery-protection'                → 'discovery_protection_context'
'confidential-settlement-restriction' → 'settlement_restriction_context' + 'settlement_restriction_scope'
'individual-liability'                → 'individual_liability_context' + 'individual_liability_scope'
'successor-liability'                 → 'successor_liability_context'
'extraterritorial-coverage'           → 'extraterritorial_context'
// ── Without Context (no tab) ───────────────────────────────────────────────
'catch-all-protection'                — (no companion)
'internal-only-disclosure'            — (no companion)
'trade-secret-immunity'               — (no companion)
'continuing-violation-doctrine'       — (no companion)
'prospective-whistleblower-protection'— (no companion)
'sovereign-immunity-waiver'           — (no companion)
'class-action'                        — (no companion)

## Taxonomy Reference
**New Taxonomy Tables**
`ws_sovereign_immunity_status` — flat. Tracks how state/federal sovereign immunity applies. Attached to `jx-statute`, `jx-common-law`, `jx-citation`, `jx-construction`.
`ws_remedy_cap_basis` — flat. Classifies structural basis for damages caps. Attached to `jx-statute`, `jx-common-law`.

**Term Additions to Existing Tables**
`ws_legal_recognition`: `garcetti-exception`, `savings-clause`, `federal-concurrent-enforcement`, `state-floor-exceeds-federal`, `agency-inaction-triggers-suit`, `official-duties-carveout`, `equitable-interest-award`, `mitigation-exception-recognized`
`ws_remedy`: `pre-judgment-interest`, `post-judgment-interest`, `discretionary-interest`, `mitigation-exception`, `non-economic-cap-separate`, `punitive-damages-capped-separately`
`ws_causation_standard`: `dual-standard-applies`, `statutory-nexus-diverges-from-common-law`, `substantial-motivating-factor`, `any-consideration-nexus`
`ws_protected_class`: `victim-domestic-violence-sexual-assault`, `family-member-whistleblower`, `domestic-work-employee`, `contractor-subcontractor-agent`
`ws_process_type`: `agency-inaction-civil-trigger`, `hybrid-admin-civil-path`, `direct-filing-permitted`

## Notes
This draft treats the statute set as baseline for broad legal parity, then adds per-type deltas.
`ws_legal_recognition` is a presence/absence signal table, not a classification table.
Editorial workflow optimization: Tabs 4–7 will carry a persistent `acf/message` field at the top:
`"⚠️ Several fields below appear conditionally based on Legal Recognitions selected in Tab 2. Check the Companion Map before saving."`
All conditional logic uses the four accepted annotation forms. Sister fields inherit visibility but are not independently conditional.
Zero live data implications. This spec is purely structural and ready for PHP field generation or ingest schema mapping.