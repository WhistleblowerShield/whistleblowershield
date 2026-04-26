# Legal Record ACF Canonical Field Draft

Purpose: draft a unified, prefix-free field set for all four legal record types
(`statute`, `common_law`, `citation`, `construction`) as the first step toward a
full rewrite of the  ingest tool.

Notes: Do not update existing files. Rename existing files with .txt appended.
Create new files with same names as the originals.

---

## Naming Rules Applied

- No CPT infix or storage prefix in this draft (no `ws_jx_*`).
- CPT infix use relevant prefixes: `ws_jx_statute_*`, `ws_jx_comlaw_*`, `ws_jx_citation_*`, `ws_jx_construction_*`.
- Meta names are `snake_case` only.
- Choice keys are `kebab-case` only.
- Booleans use `has_*` (when the field is a trigger) or `is_*` (when field is not a trigger).
- `has_*` is true can trigger `*_details` companion, but can also trigger other fields (e.g. `has_some_date`
   triggers `some_date`).
- Single-value datapoints use singular nouns.
- Multi-value datapoints use plural nouns.
- `*_recognized` — (too long) avoid where possible; use `ws_legal_recognition` taxonomy slug if logical.
- `*_type` — (too generic) avoid where possible; use `*_class`, `*_scope`, `*_status`, `*_rule`,
  `*_framework`, `*_weight`, or `*_standard` depending on context. `*_type` is acceptable where context logically
   requires it.
- `*_details` — freetext(usually) companion, conditional on `has_*` bool true, or `has-details` sentinel
   present in trigger choice/taxonomy field. Any sister field inherits the conditional behavior from `*_details`.
  `*_details` may have a sister field; no naming convention applies to sister fields, apply logical name
   using context. Sister fields may not appear without a sibling `*_details` field. 
- `*_context` — freetext(usually) companion, conditional on trigger field when specified
   value, values(not used), or any non-empty value is present, defined by trigger requirements. `*_context` may
   have sister fields similar to `*_details`.
- `*_limitations` — (too long) use `*_limits` unless context requires otherwise.
- Some suffixes define data-shape (e.g. `*_url`, `*_date`, `*_email`, etc.).
    Always use data-shape suffixes with logical data-shapes.
    Do not use data-shape suffixes otherwise.

### Needed Hooks

- Derived values need auto-fill by hook on load/fill/update.
- Merged values (usually hidden) need auto-fill by hook on save.
- Derived choices for select fields need filter hook on load/fill/update. e.g. `courts` on precedent records
  filter/fill when `jurisdiction` is populated or updated.
- `fee_shifting_rules` needs hook to catch contradictory terms.
- `sovereign_immunity_limits` needs hook to catch contradictory terms.
- `primary_agency` needs hook as auto-fill on first added `ws-agency`, if empty. Manual override select needs
   hook to filter choices to attached `ws-agency` posts only.
   Hook should set 'instructions' key to: "Attach one `ws-agency` to local or federal first", if empty.
   Hook should set 'instructions' key to: "Override `primary_agency` with any currently attached local or
   federal agency", if non-empty.
- `local_agencies` needs hook to filter to only jx applicable `ws-agency` choices, and exclude federal agencies;
   stub: future refining-filter to use common `ws_process_type` and both common `ws_disclosure_*` taxonomies.
- `federal_agencies` needs hook to filter to only federal agencies; stub: future_filter to use `disclosure_type`
   and `disclosure_target` taxonomies.
- `contractual-waiver` is only a valid term if `civil_action_waiver_scope` is not set to `anti`; the value
   represents a statutory preclusion of ALL waivers. If `contractual_waiver_context` is present with a value,
   the value must be cleared, and `contractual-waiver` removed from `legal_recognitions`. The same is true for any
   sister field. — `contractual-waiver` is invalid while `civil_action_waiver_scope` is `anti`. A hook needs to
   monitor both values and the terms in `legal_recognitions`.
- `jury-trial` is only a valid term if `private-right-of-action` is in `legal_recognitions`. If `jury_trial_context`
   is present with a value, the value must be cleared and `jury-trial` removed from `legal_recognitions`. The same
   is true for any sister field. — `jury-trial` requires `private-right-of-action` AND `jury-trial` is invalid while
   `private-right-of-action` is not present in `legal_recognitions`. A hook needs to monitor for both terms in
   `legal_recognitions`.
- Some hooks exist in legacy files, use for reference only. Write new/optimized hooks where logical/possible.
  Reuse new hooks where ever logical/possible. e.g. Don't write a hook that only applies to statute_ids, and then
  write a second hook with near-identical code for comlaw_ids, when one hook with get_post_type() logic will suffice.

### Sentinel Values

- `has-details` —  use sentinel where logical in choice/taxonomy fields to trigger `*_details` companion,
  `has_*` boolean — not required.
- `has-details` — should be used to replace `other`, `unclear` or `mixed` when `*_details` can capture nuance.
- `has-limits`  — sentinel in `ws_remedy` triggers `remedy_limits` companion.
- `has-phases`  — sentinel in `ws_fee_shifting_rule` triggers `fee_shifting_phases` companion.
- `see-details` — used in place of `has-details` value in choice fields where `*_details` has already been triggered.
- `see-context` — used in place of `has-details` value in choice fields where `*_context` has already been triggered.

## Inline Field Descriptions

- All fields are 'freetext' or defined by naming convention:
  `has_*`, `is_*` are bool, `*_type`, `*_class`, etc. are select, and so on. Unless specified otherwise
   or obvious by context.
- All taxonomy fields are 'multi_select' unless specified otherwise, and use specified to taxonomy.
- All taxonomy fields are 'load_terms' => 1, 'save_terms' => 1, unless specified otherwise.

---

## Attached Plain-English

All four legal record types use the plain-english summary system.

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
- `citation`                       — (freetext; statute citation / precedent case / case name (shared slot))
- `protection_scope`               — (single-select taxonomy: `ws_protection_scope`)
- `general_description`            — (brief; reserve full summary for `plain_english_wysiwyg`)
- `has_attach_flag`
- `display_order`

---

### Effective Date Tab

Fields ordered: related dates → retroactive

- `date`                           — (enacted / ruling / decision date (shared slot))
- `has_effective_date`             — (only when `effective_date` differs from `date`)
- `effective_date`
- `effective_year`                 — (derived from `effective_date` if present, `date` if not)
- `retro_date`                     — (sister field to `retro_context`)
- `retro_context`                  — (conditional on `retroactive-date` in `legal_recognitions`)

---

### Classifications Tab

Fields ordered: legal_recognitions → activity standard → disclosure →
classes → sectors → targets → recognitions

- `legal_recognitions`             — (taxonomy: `ws_legal_recognition`; replaces all `*_recognized` booleans,
                                      and other bools; See [Slug-to-Companion Map] below.)
- `protected_action_standard`      — (sister field to `protected_action_context`; single-select:
                                      `actual_violation`|`reasonable_belief`|`good_faith`)
- `reasonable_belief_context`      — (conditional on `protected_activity_standard` is `reasonable_belief`;
                                      single-select: `objective_only`|`subjective_only`|`dual_prong`|`has-details`)
- `reasonable_belief_details`
- `protected_action_source`        — (sister field to `protected_action_context`; multi-select: `constitutional`|
                                      `statutory`|`judicial`|`regulatory`|`executive`|`see-context`)
- `protected_actions`              — (sister field to `protected_action_context`; taxonomy: `ws_protected_action`)
- `protected_action_context`       — (conditional on `protected-action` in `legal_recognitions`)
- `disclosure_types`               — (taxonomy: `ws_disclosure_type`)
- `protected_classes`              — (taxonomy: `ws_protected_class`)
- `protected_class_details`
- `excluded_classes`               — (sister field to `excluded_class_context`; taxonomy: `ws_excluded_class`)
- `excluded_class_context`         — (conditional on `excluded-class` in `legal_recognitions`)
- `excluded_class_details`
- `employment_sectors`             — (taxonomy: `ws_employment_sector`)
- `disclosure_targets`             — (taxonomy: `ws_disclosure_target`)
- `disclosure_target_details`      
- `disclosure_channel_scope`       — (single-select: `approved-channel-only`|`mandatory-internal-first`|
                                      `any-channel`|`has-details`)
- `disclosure_channel_details`
- `manager_rule_context`           — (conditional on `manager-rule-exclusion` in `legal_recognitions`)
- `public_concern_context`         — (conditional on `public-concern-required` in `legal_recognitions`)
- `bad_faith_context`              — (conditional on `bad-faith-exclusion` in `legal_recognitions`)
- `anonymity_context`              — (conditional on `anonymity-protection` in `legal_recognitions`)
- `has_malicious_reporting_sanctions`
- `malicious_reporting_details`
- `is_evidence_collection_protected`

---

### Statute of Limitations And Thresholds Tab

Fields ordered: core SOL  → modifiers  → exhaustion  → thresholds  → preemption.

- `sol_value`                      — (integer)
- `sol_unit`                       — (single-select: `days`|`weeks`|`months`|`years`)
- `sol_trigger`                    — (single-select: `filing-of-complaint`|`accrual`|`discovery-actual`|
                                      `discovery-constructive`|`discovery-notice`|`conclusion-of-admin-process`|
                                      `see-context`)
- `sol_trigger_context`            — (conditional on `sol_trigger` non-empty)
- `has_sol_details`
- `sol_details`
- `has_statute_of_repose`
- `sop_value`                      — (sister field to `statute_of_repose_details`; integer)
- `sop_unit`                       — (sister field to `statute_of_repose_details`; single-select: `days`|`weeks`|
                                      `months`|`years`)
- `sop_trigger`                    — (sister field to `statute_of_repose_details`; single-select:
                                      `filing-of-complaint`|`accrual`|`discovery-actual`|`discovery-constructive`|
                                      `discovery-notice`|`see-details`)
- `statute_of_repose_details`
- `statutory_tolling_context`      — (conditional on `statutory-tolling` in `legal_recognitions`)
- `equitable_tolling_context`      — (conditional on `equitable-tolling` in `legal_recognitions`)
- `amended_claim_context`          — (conditional on `amended-claim` in `legal_recognitions`)
- `exhaustion_class`               — (sister field to `exhaustion_context`; single-select: `jurisdictional`|
                                      `claims-processing`|`waivable`|`see-context`)
- `exhaustion_context`             — (conditional on `exhaustion-required` in `legal_recognitions`)
- `filing_notice_target`           — (sister field to `filing_notice_context`; single-select: `employer`|`agency`|
                                      `attorney-general`|`labor-board`|`see-context`)
- `filing_notice_context`          — (conditional on `pre-filing-notice` in `legal_recognitions`)
- `has_employer_threshold`
- `threshold_compare`              — (sister field to `threshold_details`; single-select: `gte`|`lte`|`gt`|`lt`|
                                      `eq`)
- `threshold_value`                — (sister field to `threshold_details`; integer)
- `threshold_unit`                 — (sister field to `threshold_details`; single-select: `employees`|
                                      `contractors`|`workers`|`fte`)
- `threshold_details`
- `has_cure_period`
- `cure_period_value`              — (sister to `cure_period_details`; integer)
- `cure_period_unit`               — (sister to `cure_period_details`; single-select: `days`|`weeks`|`months`|
                                      `years`)
- `cure_period_details`
- `has_preemption`
- `preemption_direction`           — (sister field to `preemption_details`; single-select:
                                      `federal_preempts_state`|`state_not_preempted`|`partial`|`see-details`)
- `preemption_details`
- `preclusion_context`             — (conditional on `statutory-preclusion` in `legal_recognitions`)

---

### Retaliation Tab

Fields ordered: adverse actions → recognitions → sanctions

- `adverse_actions`                — (taxonomy: `ws_adverse_action`)
- `adverse_action_details`         — (conditional on `adverse_actions` includes `has-details`)
- `adverse_action_scope`           — (single-select: `termination-only`|`material-adverse`|
                                      `broad-any-adverse-action`|`see-context`)
- `adverse_action_scope_context`   — (conditional on `adverse_action_scope` non-empty)
- `constructive_discharge_context` — (conditional on `adverse_actions` includes `constructive-discharge`)
- `anticipatory_retaliation_context`  — (conditional on `adverse_actions` includes `threatened-retaliation`)
- `cats_paw_context`               — (conditional on `cats-paw-liability` in `legal_recognitions`)
- `third_party_retaliation_context`   — (conditional on `third-party-retaliation` in `legal_recognitions`)
- `criminal_sanctions`             — (repeater: [`sanction_conduct` — (single-select: `retaliation`|`disclosure`|
      ├── `sanction_conduct`          `false-report`|`obstruction`|`other`)], [`sanction_level` — (single-select:
      └── `sanction_level`            `misdemeanor`|`felony`)])

### Process & Remedies Tab

Fields ordered: process → fee shifting → remedies → enforcement

- `process_types`                  — (taxonomy: `ws_process_type`)
- `private_roa_context`            — (conditional on `private-right-of-action` in `legal_recognitions`)
- `jury_trial_scope`               — (sister field to `jury_trial_context`; single-select: `all-claims`|`damages-only`|
                                     `liability-only`|`see-details`)
- `jury_trial_context`             — (conditional on `private-right-of-action` AND `jury-trial` in
                                      `legal_recognitions`)
- `fee_shifting_rules`             — (taxonomy: `ws_fee_shifting_rule`)
- `fee_shifting_phases`            — (conditional on `fee_shifting_rules` includes `has-phases`)
- `fee_shifting_details`           — (conditional on `fee_shifting_rules` includes `has-details`)
- `fee_shifting_asymmetry`         — (single-select: `one-way-plaintiff`|`one-way-defendant-frivolous`|
                                      `two-way`|`american-rule`|`has-details`)
- `fee_shifting_asymmetry_details`
- `remedies`                       — (taxonomy: `ws_remedy`)
- `remedy_limits`                  — (conditional on `remedies` includes `has-limits`)
- `remedy_details`                 — (conditional on `remedies` includes `has-details`)
- `remedy_liquidated_multiplier`   — (conditional on `remedies` includes `liquidated-damages`; single-select:
                                      `double`|`treble`|`2x-back-pay`|`2x-wages-lost`|`statutory-formula`|
                                      `statutory-daily-fine`|`up-to-double`|`up-to-treble`|`has-details`)
- `remedy_liquidated_formula`      — (conditional on `remedy_liquidated_context` includes `statutory-formula`)
- `remedy_liquidated_details`      — (conditional on `remedy_liquidated_context` includes `has-details`)
- `mixed_motive_remedy_context`    — (conditional on `burden_shifting_framework` includes `mixed-motive`;
                                      see [Cross-Tab Conditional below])
- `preliminary_reinstatement_context`  — (conditional on `preliminary-reinstatement` in `legal_recognitions`;
                                          document mandatory vs. discretionary standard, bond requirement,
                                          and whether reinstatement applies during administrative phase only
                                          or full pendency of case)
- `primary_agency`                 — (auto-fill by hook when first `ws-agency` added and value is empty)
- `local_agencies`                 — (multi-select: `ws-agency` posts filtered by jx, common process and
                                      common disclosure taxonomies)
- `enforcement_priority`           — (single-select: `agency-first`|`court-first`|`either`|`sequential`)
- `enforcement_channel`            — (freetext; priority of enforcement agencies, with any enforcement requirements)

### Waiver & Scope Tab

Fields ordered: contractual → recognitions → immunity → defendants.

- `civil_action_waiver_scope`      — (single-select: `prohibited`|`permitted-individual-only`|
                                      `permitted-collective`|`anti`|`see-context`)
- `civil_action_waiver_context`    — (conditional on `class_action_waiver_scope` non-empty)
- `contractual_waiver_scope`       — (sister field to `contractual_waiver_context`; single-select: `void`|
                                      `limited`|`enforceable`|`void-public-policy`|`void-as-to-whistleblowing`|
                                      `enforceable-with-exceptions`|`see-context`)
- `contractual_waiver_context`     — (conditional on `civil_action_waiver_scope` != `anti` AND
                                      `contractual-waiver` in `legal_recognitions`)
- `nda_limits_context`             — (conditional on `nda-limitations` in `legal_recognitions`)
- `anti_gag_context`               — (conditional on `anti-gag-provision` in `legal_recognitions`)
- `no_retaliatory_evidence_context`  — (conditional on `no-retaliatory-evidence` in `legal_recognitions`)
- `stay_context`                   — (conditional on `stay-of-disciplinary-action` in `legal_recognitions`)
- `anti_slapp_protection_context`  — (conditional on `anti-slapp-protection` in `legal_recognitions`)
- `settlement_restriction_context`   — (conditional on `confidential-settlement-restriction` in `legal_recognitions`)
- `successor_liability_context`    — (conditional on `successor-liability-recognized` in `legal_recognitions`)
- `extraterritorial_context`       — (conditional on `extraterritorial-coverage` in `legal_recognitions`)
- `employer_knowledge_context`     — (conditional on `employer-knowledge-required` in `legal_recognitions`)
- `sovereign_immunity_limits`      — (multi-select: `not-waived`|`partially-waived`|`fully-waived`|`cap-applies`|
                                      `conditions-apply`|`has-details`)
- `sovereign_immunity_scope`       — (sister field to `sovereign_immunity_details`; single-select:
                                      `state-only`|`instrumentalities-included`|`political-subdivisions-included`|
                                      `all`|`see-details`)
- `sovereign_immunity_details`
- `proper_defendants`              — (multi-select: `employer-entity-only`|`individual-supervisors`|
                                      `government-agency-only`|`contractors-included`|`successor-employer`|
                                      `joint-employer`|`scope-of-employment-required`|`has-details`)
- `proper_defendant_details`

---

### Burden Of Proof Tab

Fields ordered: framework → employee standards → causation → employer defenses  →
rebuttable presumption  → temporal presumption → detail overflow

- `has_burden_shifting`
- `burden_shifting_framework`      — (sister field to `burden_shifting_details`; single-select: `McDonnell-Douglas`|
                                      `motivating-factor`|`but-for`|`mixed-motive`|`see-context`;
                                      see [Cross-Tab Conditional] below)
- `burden_shifting_context`        — (conditional on `burden_shifting_framework` non-empty)
- `burden_shifting_details`
- `employee_standards`             — (taxonomy: `ws_employee_standard`; evidentiary burden only)
- `standard_details`
- `causation_standards`            — (taxonomy: `ws_causation_standard`; causal link standard)
- `causation_standard_context`     — (conditional on `causation_standards` non-empty; document hybrid
                                      or judicially modified standards, circuit splits, and backstop
                                      limitations not captured by taxonomy terms alone)
- `causation_application`          — (multi-select: `liability`|`damages`|`both`|`has-details`;
                                      use when causation standard applies differently to liability
                                      phase vs. damages phase within the same record)
- `causation_application_details`
- `employer_awareness_requirement` — (single-select: `actual-knowledge`|`constructive-knowledge`|
                                      `inferred-knowledge`|`no-requirement`|`has-details`)
- `employer_awareness_details`
- `employer_defenses`              — (taxonomy: `ws_employer_defense`)
- `defense_details`
- `has_rebuttable_presumption`
- `rebuttable_details`
- `has_temporal_presumption`
- `presumption_window_value`       — (sister field to `presumption_window_details`)
- `presumption_window_unit`        — (sister field to `presumption_window_details`; single-select: `days`|`weeks`|
                                      `months`|`years`)
- `presumption_window_details`
- `has_bop_details`
- `bop_details`

---

### Reward Tab

Fields ordered: rewards → qui tam specifics

- `has_reward_available`
- `reward_discretion_standard`     — (sister field to `reward_details`; single-select: `mandatory`|`discretionary`|
                                      `presumptive`|`formula-based`|`has-details`)
- `reward_discretion_formula`      — (conditional on `reward_discretion_standard` is `formula-based`)
- `reward_discretion_details`      — (conditional on `reward_discretion_standard` is `has-details`)
- `reward_details`
- `qui_tam_government_share`       — (sister to `qui_tam_share_context`; freetext; range when government intervenes;
                                      e.g. "15%–25%")
- `qui_tam_relator_share`          — (sister to `qui_tam_share_context`; freetext; range when government declines;
                                      e.g. "25%–30%")
- `qui_tam_reduction_grounds`      — (sister to `qui_tam_share_context`; freetext; conditions under which court may
                                      reduce below statutory floor)
- `qui_tam_share_context`          — (conditional on `process_types` includes `qui-tam`)

---

### Relationships Tab

Fields ordered: reference → related legal records

- `ref_materials`                  — (post object; `ws-reference`)
- `overruled_by_id`                — (post object; legal-record['post_id']; replaces `superseded_by`)

---

### Source / Audit Tab

Fields ordered: reviewed → source url → authority

- `last_reviewed_date`             — (manually updated when record reviewed for accuracy)
- `url`                            — (url field; statute / precedent / case law URL (shared slot))
- `url_is_pdf`
- `authority_reference`            — (freetext; holds the official legislative history citation or regulatory
                                      citation (CFR, Federal Register, etc.))

---

### Hidden Fields (no tab; prefixed with underscore)

Fields ordered: id → derived

- `_id`                            — (freetext; generated by ingest tool or matrix seeder)
- `_disclosure_target_class`       — (derived from `disclosure_targets`; auto-fill by hook an save; single-select:
                                      `internal`|`external`|`both`)

---

## Specialized Fields By Legal Record Type

---

### Substantive-Record Common Fields (statute + common_law)

Substantive records carry most of the fields defining whistleblower protections.
The notable precedent fields capture modifications to the definitions:
- `court` — federal or local courts that made the ruling
- `scope` — the result of the ruling (`favorable`|`adverse`|`neutral`) on whistleblower protections
- `binding_scope`     — effective strength of the ruling
- `affected_jx`       — affected jurisdictions (when more than the one ruling jx)
- `extend_taxonomy`   — affected taxonomy when ruling is favorable
- `suppress_taxonomy` — affected taxonomy when ruling is adverse
- `parent_ids`: `statute_ids`, `comlaw_ids` — legal record or records affected by ruling
These fields do not appear on substantive records.

#### Process & Remedies Tab (insert after `jury_trial_context`)

- `election_of_remedies_rules`     — (multi-select: `administrative-bars-civil`|`state-bars-federal`|
                                      `remedy-exclusivity`|`first-filed-controls`|`no-election-required`|
                                      `see-context`)
- `election_of_remedies_context`   — (conditional on `election_of_remedies_rules` non-empty)

#### Relationships Tab

- `citation_ids`
- `construction_ids`

#### Hidden Fields

- `_precedent_ids`           — (merged array of `citation_ids` and `construction_ids`; auto-fill by hook on save)

---

### Statute-Specific

#### Enforcement Tab

- `federal_agencies`         — (insert after `local_agencies`; multi-select: `ws-agency` posts filtered by jx,
                                common process and common disclosure taxonomies)

#### Hidden Fields

- `_primary_agency_is_fed`         — (derived from `primary_agency` jx; auto-fill by hook on save)
- `_related_agencies`              — (merged array of `local_agencies` and `federal_agencies`; auto-fill by hook
                                      on save)

---

### Common-Law-Specific

#### Identity and Publishing Tab (insert after `citation`)

- `precedent_common`               — (freetext; common name for precedent case held in `citation`)

#### Classification Tab (insert after `excluded_class_details`)

- `doctrine_basis`                 — (freetext; the legal basis for the doctrine; reserve full summary for
                                      `plain_english_wysiwyg`)
- `public_policy_sources`          — (multi-select: `constitution`|`statute`|`administrative-rule`|`case-law`|
                                      `federal-law`|`has-details`)
- `source_details`
- `recognition_status`             — (single-select: `recognized`|`limited`|`uncertain`|
                                      `rejected`|`abrogated`|`has-details`)
- `recognition_details`            — (conditional on `recognition_status` is `has-details`)

---

### Precedent-Record Common Fields (citation + construction)

Precedent records carry most common fields. The narrow exceptions are fields
that are definitionally inapplicable to court decisions:
- `election_of_remedies_rule` — legislative/doctrinal construct; not a court ruling
-  Common-law-specific fields — doctrine and recognition fields have no precedent equivalent
-  `precedent_ids` — they have `parent_ids`: `statute_ids`, `comlaw_ids` — instead

#### Identity and Publishing Tab

- `types`                          — (citation `types`: multi-select: `case_law`|`statute`|`regulatory`|`secondary`;
                                      construction `type`: single-select: `case_law`|`statute`|`regulatory`|
                                      `secondary`; NOTE: field_key and field_name differ by singular/plural)
- `status`                         — (single-select: `published`|`unpublished`|`memorandum`|`vacated`)
- `binding_scope`                  — (single-select: `binding`|`persuasive`|`mixed`|`distinguished`|`overruled`)
- `court`                          — (single-select: populate choices by hook on load/fill filter by jx)
- `court_details`                  — (conditional on `court` is `has-details`)
- `court_jx`                       — (sister field to `court_details`; taxonomy: `WS_JURISDICTION_TAXONOMY`,
                                      'load_terms' => 1, 'save_terms' => 0)
- `court_is_fed`                   — (derived from `court` `ws_jx_codes`; manually set when `court`
                                      is `has-details`)

#### Effective Date Tab (insert after `effective_year`)

- `mandate_date`

#### Classification Tab (insert after `legal_recognitions`)

- `scope`                          — (single-select: `favorable`|`adverse`|`neutral`)
- `extend_taxonomy`                — (conditional on `scope` is `favorable`; repeater;
                                      each row: taxonomy slug + term slug being added to parent's coverage)
- `suppress_taxonomy`              — (conditional on `scope` is `adverse`; repeater;
                                      each row: taxonomy slug + term slug being removed from parent's coverage)
- `has_affected_jx`                — (derived from `court` `ws_jx_codes`; manually set false when single jx
                                      is same as precedent `jurisdiction`; manually set if true when
                                      `court`-`has-details` and covers multiple jx)
- `affected_jx`                    — (conditional on `has_affected_jx`; derived from `court` `ws_jx_codes`;
                                      manually set taxonomy: `WS_JURISDICTION_TAXONOMY`, 'load_terms' => 1,
                                      'save_terms' => 0, once `has_affected_jx` is true to apply affected jx
                                       by `court`-`has-details` known jx)

#### Relationships Tab

- `statute_ids`
- `comlaw_ids`
- `parent_weight`                  — (single-select: `primary`|`secondary`|`distinguishing_only`)
- `has_negative_treatment`
- `negative_treatment_class`       — (sister field to `negative_treatment_details`; single-select: `overruled`|
                                      `distinguished`|`limited`|`questioned`|`superseded-by-statute`|`has-details`)
- `negative_treatment_class_details`
- `negative_treatment_details`

#### Source / Audit Tab (insert after `authority_reference`)

- `authority_source`               — (single-select: `constitutional`|`legislative`|`judicial`|`regulatory`|
                                      `executive`|`has-details`)
- `authority_source_details`
- `review_standard`                — (single-select: `de-novo`|`substantial-evidence`|`arbitrary-capricious`|
                                      `abuse-of-discretion`|`has-details`)
- `review_standard_details`

#### Hidden Fields

- `_parent_ids`                    — (merged array of `statute_ids` and `comlaw_ids`; auto-fill by hook on save)

---

### Citation-Specific

#### Identity and Publishing Tab (insert after `types`)

- `citation_type_rationale`        — (freetext; explains `types` assigned)

---

### Construction-Specific

#### Identity and Publishing Tab

- `is_en_banc`                     — (defaults true; when false triggers `panel_composition_details`)
- `panel_composition_class`        — (sister field to `panel_composition_details`; single-select: `three-judge`|
                                      `five-judge`|`seven-judge`|`nine-judge`|`expanded-panel`|`single-judge`|
                                      `see-details`)
- `panel_composition_details`      — (conditional on `is_en_banc` is false)

---

## Rename Normalization (Current  → Canonical)

Only fields that currently violate target naming conventions or are inconsistent
across legal ACFs.

- `fee_shiftings`                   → `fee_shifting_rules`
- `ws_fee_shifting`                 → `ws_fee_shifting_rule` (taxonomy table) 
- `has_limit_ambiguous`             → `has_sol_details`
- `limit_details`                   → `sol_details`
- `has_tolling_details`             → `has_tolling`
- `has_exhaustion_required`         → `has_exhaustion_requirement`  → `exhaustion-required` in `legal_recognitions`
- `exhaustion_required_details`     → `exhaustion_details`
- `exhaustion_is_jurisdictional`    → `exhaustion_class`  → (sister field to `exhaustion_details`)
- `rebuttable_presumption_details`  → `rebuttable_details`
- `statutory_preclusion_details`    → `preclusion_details`
- `employee_standard_details`       → `standard_details`
- `employer_defense_details`        → `defense_details`
- `employer_threshold_details`      → `threshold_details`
- `doctrine_basis_wysiwyg`          → `doctrine_basis`
- `recognition_status_wysiwyg`      → `recognition_status`
- `doctrine_name`                   → `official_name`
- `statute_citation` / `precedent_name` / `case_name`   (shared slot)  → `citation`
- `enacted_date` / `ruling_date` / `decision_date`      (shared slot)  → `date`
- `statute_url` / `precedent_url` / `citation_url` / `construction_url` (shared slot)  → `url`
- `statute_url_is_pdf` / `precedent_url_is_pdf` / `citation_url_is_pdf` /
  `construction_url_is_pdf`                             (shared slot)  → `url_is_pdf`
- `superseded_by`                   → `overruled_by_id` (post object)
- `has_constructive_discharge_recognized`    → `constructive-discharge` in `legal_recognitions`
- `has_anticipatory_retaliation_recognized`  → `anticipatory-retaliation` in `legal_recognitions`
- `continuing_violation_recognized`          → `continuing-violation` in `ws_legal_recognition`
- `equitable_tolling_recognized`             → `equitable-tolling` in `ws_legal_recognition`
- `has_amended_claim_recognized`             → `amended-claim` in `ws_legal_recognition`
- `arbitration_waiver_enforceability`        → `contractual-waiver` in `legal_recognitions`
- `disclosure_target_type`          → `_disclosure_target_class` (derived, hidden)
- `exhaustion_type`                 → `exhaustion_class`

If a field is unchanged or new, it does not appear in the above list.

---

## Relationship Direction Contract (For Sync)

- Parent-bearing legal records: `citation`, `construction`.
- Child-bearing  legal records: `statute`, `common_law`.

---

### Cross-Tab Conditional: mixed-motive  → mixed_motive_remedy_context

When `burden_shifting_framework` (Burden Of Proof tab) includes `mixed-motive`,
the field `mixed_motive_remedy_context` (Process & Remedies tab) becomes relevant.
ACF conditional logic cannot surface this cross-tab dependency natively.

Implementation: register an `acf/save_post` hook (or `admin_notices` hooked to
`current_screen`) that detects `mixed-motive` in `burden_shifting_framework` and
emits a dismissible admin notice directing the editor to the Process & Remedies tab:

> "Mixed-motive framework selected — please complete the 'Mixed Motive Remedy
> Context' field on the Process & Remedies tab."

Notice should be 'informative' not 'pants-on-fire' and display on the edit screen
for all four legal record CPTs. Dismiss state does not need to persist — the notice
should reappear on each save as long as `mixed-motive` is present and
`mixed_motive_remedy_context` is empty.

---

## Slug-to-Companion Map (ws_legal_recognition taxonomy)

 * Used for bool-state values of Legal Recognitions. Presence = true:
 *  - Recognized  — judicial doctrines courts have acknowledged
 *  - Available   — procedural/remedy mechanisms
 *  - Permitted   — rights that can be exercised
 *  - Required    — mandatory procedural obligations
 *  - Specified   — explicit editorial enumeration
 *  - Applies     — statutory conditions that operate by force of law

Conditional-Companion fields `*_context` noted with ' → ' are triggered by slug presence in `legal_recognitions`.
Sister fields noted by ' + ' inherit the conditional behavior, but are defined by the sibling.
Sister fields cannot appear without the triggered sibling being revealed.

```
// ── Effective Date Tab ──────────────────────────────────────────────────────
'retroactive-date'                    → 'retro_context' + 'retro_date'
// ── Classifications Tab ─────────────────────────────────────────────────────
'protected-action'                    → 'protected_actions_context' + 'protected_actions' + 'protected_action_standard'
                                                                    + 'protected_action_source'
'excluded-class'                      → 'excluded_class_context'    + 'excluded_classes'
'manager-rule-exclusion'              → 'manager_rule_context'
'public-concern-required'             → 'public_concern_context'
'bad-faith-exclusion'                 → 'bad_faith_context'
'anonymity-protection'                → 'anonymity_context'
// ── Statute of Limitations Tab ──────────────────────────────────────────────
'statutory-tolling'                   → 'statutory_tolling_context'
'equitable-tolling'                   → 'equitable_tolling_context'
'amended-claim'                       → 'amended_claim_context'
'exhaustion-required'                 → 'exhaustion_context'
'pre-filing-notice'                   → 'filing_notice_context'
'statutory-preclusion'                → 'preclusion_context'
// ── Retaliation Tab ─────────────────────────────────────────────────────────
'cats-paw-liability'                  → 'cats_paw_context'
'third-party-retaliation'             → 'third_party_retaliation_context'
// ── Process & Remedies Tab ──────────────────────────────────────────────────
'private-right-of-action'             → 'private_roa_context'
'jury-trial'                          → 'jury_trial_context'         + 'jury_trial_scope'
'preliminary-reinstatement'           → 'preliminary_reinstatement_context'
// ── Waiver & Scope Tab ──────────────────────────────────────────────────────
'contractual-waiver'                  → 'contractual_waiver_context' + 'contractual_waiver_scope'
'nda-limitations'                     → 'nda_limits_context'
'anti-gag-provision'                  → 'anti_gag_context'
'no-retaliatory-evidence'             → 'no_retaliatory_evidence_context'
'stay-of-disciplinary-action'         → 'stay_context'
'anti-slapp'                          → 'anti_slapp_context'
'confidential-settlement-restriction' → 'settlement_restriction_context'
'successor-liability'                 → 'successor_liability_context'
'extraterritorial-coverage'           → 'extraterritorial_context'
'employer-knowledge'                  → 'employer_knowledge_context'
// ── Without Context (no tab) ────────────────────────────────────────────────
'catch-all-protection'      — (no companion needed)
'internal-only-sufficient'  — (no companion needed)
'trade-secret-immunity'     — (no companion needed)
'continuing-violation'      — (no companion needed)
'individual-liability'      — (no companion needed)
'class-action'              — (no companion needed)

```

---

## Taxonomy Reference

New taxonomies added in 3.15.0:

- `ws_legal_recognition` — presence/absence signal table for recognized legal
  doctrines and procedural rules. Flat. Attached to all 4 legal CPTs. (built and assigned)
- `ws_causation_standard` — causation link standards, split from `ws_employee_standard`.
  Flat. Attached to all 4 legal CPTs. (built and assigned)

Split taxonomy note: `ws_causation_standard` and `ws_employee_standard` are
   sibling taxonomies covering distinct legal concepts.
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
  ('catch-all-protection', 'internal-only-sufficient', 'trade-secret-immunity', 'continuing-violation',
   'individual-liability', 'class-action') are captured exclusively via `ws_legal_recognition` taxonomy.
    No separate ACF field is registered for them. They are simply bool true if present in the array.
