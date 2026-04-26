# Legal Record ACF Canonical Field Draft

Purpose: draft a unified, prefix-free field set for all four legal record types
(`statute`, `common_law`, `citation`, `construction`) as the first step toward a
2-pipeline ingest rewrite (`legal-records`, `assist-org-records`).

Notes: Do not update existing files. Rename existing files with .txt appended.
Create new files with same names as the originals.

---

## Naming Rules Applied

- No CPT infix or storage prefix in this draft (no `ws_jx_*`).
- CPT infix use relevant prefixes: `ws_jx_statute_*`, `ws_jx_comlaw_*`, `ws_jx_citation_*`, `ws_jx_construction_*`.
- Meta names are `snake_case` only.
- Choice keys are `kebab-case` only.
- Booleans use `has_*` (usually when field is a trigger) or `is_*` (used when field is not a trigger).
- `has_*` is true can trigger `*_details`(freetext(usually)), but can also trigger other fields
   (e.g. `has_some_date` triggers `some_date`).
- Single-value datapoints use singular nouns.
- Multi-value datapoints use plural nouns.
- `*_recognized` — (too long) avoid where possible; use `ws_legal_recognition` taxonomy slug if logical.
- `*_type` — (too generic) avoid where possible; use `*_class`, `*_scope`, `*_status`, `*_rule`,
  `*_framework`, `*_weight`, or `*_standard` depending on context. `*_type` is acceptable where logical.
  `*_scope` and is commonly used to hold multiple values and is special-case to pluralization rule.
- `*_details` — freetext(usually) companion, conditional on `has_*` bool true, or `has-details` sentinel
   present in trigger choice/taxonomy field. Any sister field inherits the conditional behavior from `*_details`.
  `*_details` may have a sister field; no naming convention applies to sister fields, apply logical name
   using context. Sister fields may not appear without a sibling `*_details` field. 
- `*_context` — freetext(usually) companion, conditional on trigger field when specified
   value, values(not used), or any non-empty value is present, defined by trigger requirements. `*_context` may
   have sister fields similar to `*_details`.
- `*_limits` — preferred over `*_limitations`.
- Some suffixes define data-shape (e.g. `*_url`, `*_date`, `*_email`, etc.). Do not use data-shape suffixes
  otherwise.

### Needed Hooks

- Derived values need auto-fill by hook on fill or update.
- Merged values (usually hidden) need auto-fill by hook on save.
- Derived choices for select fields need filter hook on load and fill. e.g. `courts` on precedent records
  filter/fill when `jurisdiction` is populated or updated.
- `fee_shifting_rules` needs hook to catch contradictory terms.
- `sovereign_immunity_limits` needs hook to catch contradictory terms.
- `primary_agency` needs hook as auto-fill on first added `ws-agency`, if empty. Manual override select needs
   hook to filter choices to attached `ws-agency` posts.
   Hook should set 'instructions' key to: "Attach one `ws-agency` to local or federal first", if empty.
   Hook should set 'instructions' key to: "Override `primary_agency` with any currently attached local or
   federal agency", if non-empty.
- `local_agencies` needs hook to filter to only jx applicable `ws-agency` choices, and exclude federal agencies;
   stub: future_filter to use `ws_disclosure_type` and `ws_disclosure_target` taxonomies.
- `federal_agencies` needs hook to filter to only federal agencies; stub: future_filter to use `disclosure_type`
   and `disclosure_target` taxonomies.
- `civil_action_waiver_scope` override of `contractual_waiver_scope`: if `civil_action_waiver_scope` is set to
  `anti`, the value represents a statutory preclusion of all waivers. A hook monitors both values and terms in
  `legal_recognitions`. If `contractual-waiver` is present in `legal_recognitions` with a value set in
  `contractual_waiver_scope`, both should be cleared and `contractual-waiver` removed from `legal_recognitions`
  — `contractual-waiver` is invalid while `civil_action_waiver_scope` is `anti`.
- Some hooks exist in legacy files, use for reference only. Write new/optimized hooks where logical/possible.
  Reuse new hooks where ever logical/possible. e.g. Don't write a hook that only applies to statute_ids, and then
  write a second hook with near-identical code for comlaw_ids, when one hook with get_post_type() logic will suffice.

### Sentinel Values

- `has-details` —  use sentinel where logical in choice/taxonomy fields to trigger `*_details` companion,
  `has_*` boolean not required.
- `has-details` — should be used to replace `other`, `unclear` or `mixed` when `*_details` can capture nuance.
- `has-limits`  — sentinel in `ws_remedy` triggers `remedy_limits` companion.
- `has-phases`  — sentinel in `ws_fee_shifting_rule` triggers `fee_shifting_phases` companion.
- `see-details` — used in place of `has-details` value in choice fields where `*_details` has already been triggered.
- `see-context` — used in place of `has-details` value in choice fields where `*_context` has already been triggered.

## Inline Field Descriptions

- All fields are 'freetext' or defined by naming convention:
  `has_*`, `is_*` are bool, `*_type`, `*_class`, etc. are select, and so on. Unless specified otherwise or obvious.
- All taxonomy fields are 'multi_select' unless specified otherwise, and use specified to taxonomy.
- All taxonomy fields are "'load_terms' => 1", "'save_terms' => 1", unless specified otherwise.

---

## Attached Plain-English

All four legal record types use the plain-english summary system.

---

## Common Fields (Apply To All 4 Legal Record Types)

These normalized canonical fields exist in every legal-record ACF.
Field order reflects logical editorial workflow within each tab.

---

### Identity And Publishing Tab

- `jurisdiction`               — (single-select taxonomy: `WS_JURISDICTION_TAXONOMY`)
- `official_name`
- `common_name`
- `citation`                   — (statute citation / precedent case / case name (shared slot))
- `protection_scope`           — (single-select taxonomy: `ws_protection_scope`)
- `general_description`        — (brief; reserve full summary for `plain_english_wysiwyg`)
- `has_attach_flag`
- `display_order`

---

### Effective Date Tab

- `date`                       — (enacted / ruling / decision date (shared slot))
- `has_effective_date`         — (only when `effective_date` differs from `date`)
- `effective_date`
- `effective_year`             — (derived from `effective_date` if present, `date` if not)
- `retro_date`                 — (sister field to `retro_context`)
- `retro_context`              — (conditional on `retroactive-date` in `legal_recognitions`)

---

### Classification Tab

Fields ordered: activity standard → disclosure → classes → sectors → targets →
legal recognitions → derived flags.

- `legal_recognitions`             — (taxonomy: `ws_legal_recognition`; replaces all `*_recognized` booleans,
                                      and others; See [Slug-to-Companion Map] below.)
- `protected_action_standard`      — (sister field to `protected_action_context`; single-select: `actual_violation`|
                                      `reasonable_belief`|`good_faith`)
- `reasonable_belief_context`      — (conditional on `protected_activity_standard` is `reasonable_belief`;
                                      single-select: `objective_only`|`subjective_only`|`dual_prong`|`has-details`)
- `reasonable_belief_details`
- `protected_action_source`        — (sister field to `protected_action_context`; multi-select: `constitutional`|
                                      `statutory`|`judicial`|`regulatory`|`executive`|`see-context`)
- `protected_actions`              — (sister field to `protected_action_context`; taxonomy: `ws_protected_action`)
- `protected_action_context`
- `disclosure_types`               — (taxonomy: `ws_disclosure_type`)
- `protected_classes`              — (taxonomy: `ws_protected_class`)
- `protected_class_details`
- `excluded_classes`               — (sister field to `excluded_class_context`; taxonomy: `ws_excluded_class`)
- `excluded_class_context`         — (conditional on `excluded-class` in `legal_recognitions`)
- `excluded_class_details`
- `employment_sectors`             — (taxonomy: `ws_employment_sector`)
- `disclosure_targets`             — (taxonomy: `ws_disclosure_target`)
- `disclosure_target_details`      
- `disclosure_channel_scope`       — (single-select: `approved-channel-only`|`any-channel`|`has-details`)
- `disclosure_channel_details`
- `adverse_action_scope`           — (single-select: `termination-only`|`material-adverse`|
                                      `broad-any-adverse-action`|`see-context`)
- `adverse_action_scope_context`   — (conditional on `adverse_action_scope` non-empty)
- `anonymity_context`              — (conditional on `anonymity-protection` in `legal_recognitions`)

---

### Statute of Limitations And Thresholds Tab

Fields ordered: core SOL → modifiers → exhaustion → thresholds → preemption.

- `sol_value`                  — (integer)
- `sol_unit`                   — (single-select: `days`|`weeks`|`months`|`years`)
- `sol_trigger`                — (single-select: `filing-of-complaint`|`accrual`|`discovery-actual`|
                                  `discovery-constructive`|`discovery-notice`|`see-context`)
- `sol_trigger_context`        — (conditional on `sol_trigger` non-empty)
- `has_sol_details`
- `sol_details`
- `has_statute_of_repose`
- `sop_value`                  — (sister field to `statute_of_repose_details`; integer)
- `sop_unit`                   — (sister field to `statute_of_repose_details`; single-select: `days`|`weeks`|
                                  `months`|`years`)
- `sop_trigger`                — (sister field to `statute_of_repose_details`; single-select: `filing-of-complaint`|
                                  `accrual`|`discovery-actual`|`discovery-constructive`|`discovery-notice`|
                                  `see-details`)
- `statute_of_repose_details`
- `statutory_tolling_context`  — (conditional on `statutory-tolling` in `legal_recognitions`)
- `equitable_tolling_context`  — (conditional on `equitable-tolling` in `legal_recognitions`)
- `amended_claim_context`      — (conditional on `amended-claim` in `legal_recognitions`)
- `exhaustion_class`           — (sister field to `exhaustion_context`; single-select: `jurisdictional`|
                                  `claims-processing`|`waivable`|`see-context`)
- `exhaustion_context`         — (conditional on `exhaustion-required` in `legal_recognitions`)
- `filing_notice_target`       — (sister field to `filing_notice_context`; single-select: `employer`|`agency`|
                                  `attorney-general`|`labor-board`|`see-context`)
- `filing_notice_context`      — (conditional on `pre-filing-notice` in `legal_recognitions`)
- `has_employer_threshold`
- `threshold_compare`          — (sister field to `threshold_details`; single-select: `gte`|`lte`|`gt`|`lt`|`eq`)
- `threshold_value`            — (sister field to `threshold_details`; integer)
- `threshold_unit`             — (sister field to `threshold_details`; single-select: `employees`|`contractors`|
                                  `workers`|`fte`)
- `threshold_details`
- `has_preemption`
- `preemption_direction`       — (sister field to `preemption_details`; single-select: `federal_preempts_state`|
                                  `state_not_preempted`|`partial`|`see-details`)
- `preemption_details`
- `preclusion_context`         — (conditional on `statutory-preclusion` in `legal_recognitions`)

---

### Enforcement Tab

Fields ordered: process → adverse actions → fee shifting → remedies →
liability → contractual → agencies → immunity/defendants.

- `process_types`               — (taxonomy: `ws_process_type`)
- `adverse_actions`             — (taxonomy: `ws_adverse_action`)
- `adverse_action_details`      — (conditional on `adverse_actions` includes `has-details`)
- `constructive_discharge_context`   — (conditional on `adverse_actions` includes `constructive-discharge`)
- `anticipatory_retaliation_context` — (conditional on `adverse_actions` includes `threatened-retaliation`)
- `fee_shifting_rules`          — (taxonomy: `ws_fee_shifting_rule`)
- `fee_shifting_phases`         — (conditional on `fee_shifting_rules` includes `has-phases`)
- `fee_shifting_details`        — (conditional on `fee_shifting_rules` includes `has-details`)
- `remedies`                    — (taxonomy: `ws_remedy`)
- `remedy_limits`               — (conditional on `remedies` includes `has-limits`)
- `remedy_details`              — (conditional on `remedies` includes `has-details`)
- `remedy_liquidated_context`   — (conditional on `remedies` includes `liquidated-damages`; single-select: `double`|
                                  `treble`|`2x-back-pay`|`2x-wages-lost`|`statutory-formula`|`up-to-double`|
                                  `up-to-treble`|`has-details`(approved case of `*_context` used for select field))
- `remedy_liquidated_formula`   — (conditional on `remedy_liquidated_context` includes `statutory-formula`)
- `remedy_liquidated_details`   — (conditional on `remedy_liquidated_context` includes `has-details`)
- `mixed_motive_remedy_context` — (conditional on `burden_shifting_framework` includes `mixed-motive`;
                                   see [Cross-Tab Conditional below])
- `private_roa_context`         — (conditional on `private-right-of-action` in `legal_recognitions`)
- `cats_paw_context`            — (conditional on `cats-paw-liability` in `legal_recognitions`)
- `criminal_sanction`           — (single-select: `misdemeanor`|`felony`)
- `civil_action_waiver_scope`   — (single-select: `prohibited`|`permitted-individual-only`|
                                  `permitted-collective`|`anti`|`see-context`)
- `civil_action_waiver_context` — (conditional on `class_action_waiver_scope` non-empty)
- `contractual_waiver_scope`    — (sister field to `contractual_waiver_context`; single-select: `void`|`limited`|
                                   `enforceable`|`void-public-policy`|`void-as-to-whistleblowing`|
                                   `enforceable-with-exceptions`|`see-context`)
- `contractual_waiver_context`  — (conditional on `contractual_waiver_scope` non-empty)
- `nda_limits_context`          — (conditional on `nda-limits` in `legal_recognitions`)
- `jury_trial_scope`            — (sister field to `jury_trial_context`; single-select: `all-claims`|`damages-only`|
                                   `liability-only`|`see-details`)
- `jury_trial_context`          — (conditional on `jury-trial` in `legal_recognitions`)
- `primary_agency`              — (auto-fill by hook when first `ws-agency` added and value is empty)
- `local_agencies`              — (multi-select: `ws-agency` filtered by jx and disclosure taxonomies)
- `enforcement_priority`        — (single-select: `agency-first`|`court-first`|`either`|`sequential`)
- `enforcement_channel`         — (priority of enforcement agencies, with any enforcement details)
- `sovereign_immunity_limits`   — (multi-select: `not-waived`|`partially-waived`|`fully-waived`|`cap-applies`|
                                  `conditions-apply`|`has-details`)
- `sovereign_immunity_details`
- `proper_defendants`           — (multi-select: `employer-entity-only`|`individual-supervisors`|
                                  `government-agency-only`|`contractors-included`|`scope-of-employment-required`|
                                  `has-details`)
- `proper_defendant_details`

---

### Burden Of Proof Tab

Fields ordered: framework → employee standards → causation → temporal presumption →
employer defenses → rebuttable presumption → detail overflow.

- `has_burden_shifting`
- `burden_shifting_framework`  — (sister field to `burden_shifting_details`; single-select: `McDonnell-Douglas`|
                                  `motivating-factor`|`but-for`|`mixed-motive`|`see-context`;
                                  see [Cross-Tab Conditional] below)
- `burden_shifting_details`
- `burden_shifting_context`    — (conditional on `burden_shifting_framework` non-empty)
- `employee_standards`         — (taxonomy: `ws_employee_standard`; evidentiary burden only)
- `standard_details`
- `causation_standards`        — (taxonomy: `ws_causation_standard`; causal link standard)
- `causation_standard_details`
- `has_temporal_presumption`
- `presumption_window_value`   — (sister field to `presumption_window_details`)
- `presumption_window_unit`    — (sister field to `presumption_window_details`; single-select: `days`|`weeks`|
                                  `months`|`years`)
- `presumption_window_details`
- `employer_defenses`          — (taxonomy: `ws_employer_defense`)
- `defense_details`
- `has_rebuttable_presumption`
- `rebuttable_details`
- `has_bop_details`
- `bop_details`

---

### Reward Tab

- `has_reward_available`
- `reward_details`
- `reward_discretion_standard` — (sister field to `reward_details`; single-select: `mandatory`|`discretionary`|
                                  `presumptive`|`formula-based`|`has-details`)
- `reward_discretion_formula`  — (conditional on `reward_discretion_standard` is `formula-based`)
- `reward_discretion_details`  — (conditional on `reward_discretion_standard` is `has-details`)
- `relator_share_context`      — (conditional on `process_types` includes `qui-tam`)

---

### Relationships Tab

- `ref_materials`
- `overruled_by_id`            — (post object; replaces `superseded_by`)

---

### Source / Audit Tab

- `last_reviewed`              — (manually updated when record reviewed for accuracy)
- `url`                        — statute / precedent URL (shared slot)
- `url_is_pdf`
- `authority_reference`        — (freetext; holds the official legislative history citation or regulatory
                                  citation (CFR, Federal Register,etc.))

---

### Hidden Fields (no tab; prefixed with underscore)

- `_id`                        — (generated by ingest tool or matrix seeder)
- `_disclosure_target_class`   — (derived from `disclosure_targets`; auto-fill by hook an save; single-select:
                                  `internal`|`external`|`both`)

---

## Specialized Fields By Legal Record Type

---

### Substantive-Record Common Fields (statute + common_law)

Reciprocal rule: any substantive field that a precedent can extend, suppress, or
construe is covered on precedent records via `extend_taxonomy` and
`suppress_taxonomy` — precedents do not duplicate individual substantive fields.

#### Enforcement Tab (insert after `anticipatory_retaliation_context`)
- `election_of_remedies_rules`     — (multi-select: `administrative-bars-civil`|`state-bars-federal`|
                                      `remedy-exclusivity`|`first-filed-controls`|`no-election-required`|
                                      `see-context`)
- `election_of_remedies_context`   — (conditional on `election_of_remedies_rules` non-empty)

#### Relationships Tab
- `citation_ids`
- `construction_ids`

#### Hidden Fields
- `_precedent_ids`             — (merged array of `citation_ids` and `construction_ids`; auto-fill by hook on save)

---

### Statute-Specific

#### Enforcement Tab
- `federal_agencies`           — (insert after `local_agencies`; multi-select: `ws-agency` filtered by jx and
                                  disclosure taxonomies)

#### Hidden Fields
- `_primary_agency_is_fed`     — (derived from `primary_agency` jx; auto-fill by hook on save)
- `_related_agencies`          — (merged array of `local_agencies` and `federal_agencies`; auto-fill by hook on save)

---

### Common-Law-Specific

#### Identity and Publishing Tab (insert after `citation`)
- `precedent_common`           — (common name for precedent case held in `citation`)

#### Classification Tab (insert after `excluded_class_details`)
- `doctrine_basis`             — (the legal basis for the doctrine; reserve full summary for `plain_english_wysiwyg`)
- `public_policy_sources`      — (multi-select: `constitution`|`statute`|`administrative-rule`|`case-law`|
                                  `federal-law`|`has-details`)
- `source_details`
- `recognition_status`         — (single-select: `recognized`|`limited`|`uncertain`|
                                  `rejected`|`abrogated`|`has-details`)
- `recognition_details`        — (conditional on `recognition_status` is `has-details`)

---

### Precedent-Record Common Fields (citation + construction)

#### Identity and Publishing Tab
- `types`                      — (citation `types`: multi-select: `case_law`|`statute`|`regulatory`|`secondary`;
                                  construction `type`: single-select: `case_law`|`statute`|`regulatory`|`secondary`;
                                  NOTE: field_key and field_name differ by singular/plural)
- `status`                     — (single-select: `published`|`unpublished`|`memorandum`|`vacated`)
- `binding_scope`              — (single-select: `binding`|`persuasive`|`mixed`|`distinguished`|`overruled`)
- `court`                      — (single-select: populate choices by hook on load/fill filter by jx)
- `court_details`              — (conditional on `court` is `has-details`)
- `court_jx`                   — (sister field to `court_details`; taxonomy: `WS_JURISDICTION_TAXONOMY`,
                                  'load_terms' => 1, 'save_terms' => 0)
- `court_is_fed`               — (derived from `court` `ws_jx_codes`; manually set when `court` is `has-details`)

#### Effective Date Tab (insert after `effective_year`)
- `mandate_date`

#### Classification Tab
- `scope`                      — (single-select: `favorable`|`adverse`|`neutral`)
- `extend_taxonomy`            — (when `scope` is `favorable`; extends parent taxonomy)
- `suppress_taxonomy`          — (when `scope` is `adverse`; removes parent taxonomy term)
- `has_affected_jx`            — (derived from `court` `ws_jx_codes`; manually set false when single jx is same as
                                  precedent `jurisdiction`; manually set true when `court` is `has-details`)
- `affected_jx`                — (conditional on `has_affected_jx`; derived from `court` `ws_jx_codes`; manually
                                  set taxonomy: `WS_JURISDICTION_TAXONOMY`, 'load_terms' => 1, 'save_terms' => 0
                                  when `court` is `has-details`)

#### Relationships Tab
- `statute_ids`
- `comlaw_ids`
- `parent_weight`              — (single-select: `primary`|`secondary`|`distinguishing_only`)
- `has_negative_treatment`
- `negative_treatment_class`   — (sister field to `negative_treatment_details`; single-select: `overruled`|
                                  `distinguished`|`limited`|`questioned`|`superseded-by-statute`|`has-details`)
- `negative_treatment_class_details`
- `negative_treatment_details`

#### Relationships Tab (insert after `authority_reference`)
- `authority_source`           — (single-select: `constitutional`|`legislative`|`judicial`|`regulatory`|`executive`|
                                  `has-details`)
- `authority_source_details`

#### Hidden Fields
- `_parent_ids`                — (merged array of `statute_ids` and `comlaw_ids`; auto-fill by hook on save)

---

### Citation-Specific

#### Identity and Publishing Tab (insert after `types`)
- `citation_type_rationale`    — (freetext; explains `types` assigned)

---

### Construction-Specific

#### Identity and Publishing Tab
- `is_en_banc`                 — (defaults true; when false triggers `panel_composition_details`)
- `panel_composition_class`    — (sister field to `panel_composition_details`; single-select: `three-judge`|
                                  `five-judge`|`seven-judge`|`nine-judge`|`expanded-panel`|`single-judge`|
                                  `see-details`)
- `panel_composition_details`  — (conditional on `is_en_banc` is false)

---

## Rename Normalization (Current → Canonical)

Only fields that currently violate target naming conventions or are inconsistent
across legal ACFs.

- `fee_shiftings`                  → `fee_shifting_rules` → (taxonomy table `ws_fee_shifting` → `ws_fee_shifting_rule`)
- `has_limit_ambiguous`            → `has_sol_details`
- `limit_details`                  → `sol_details`
- `has_tolling_details`            → `has_tolling`
- `has_exhaustion_required`        → `has_exhaustion_requirement`
- `exhaustion_required_details`    → `exhaustion_details`
- `exhaustion_is_jurisdictional`   → `exhaustion_class` (now sister field to `exhaustion_details`)
- `rebuttable_presumption_details` → `rebuttable_details`
- `statutory_preclusion_details`   → `preclusion_details`
- `employee_standard_details`      → `standard_details`
- `employer_defense_details`       → `defense_details`
- `employer_threshold_details`     → `threshold_details`
- `doctrine_basis_wysiwyg`         → `doctrine_basis`
- `recognition_status_wysiwyg`     → `recognition_status`
- `doctrine_name`                  → `official_name`
- `statute_citation` / `precedent_name` / `case_name` (shared slot) → `citation`
- `enacted_date` / `ruling_date` / `decision_date` (shared slot) → `date`
- `statute_url` / `precedent_url` / `citation_url` / `construction_url` (shared slot) → `url`
- `statute_url_is_pdf` / `precedent_url_is_pdf` / `citation_url_is_pdf` /
  `construction_url_is_pdf` (shared slot) → `url_is_pdf`
- `superseded_by`                  → `overruled_by_id` (post object)
- `has_constructive_discharge_recognized` → dropped; slug `constructive-discharge`
   in `ws_legal_recognition` + `constructive_discharge_context` replaces it
- `has_anticipatory_retaliation_recognized` → dropped; slug `anticipatory-retaliation`
   in `ws_legal_recognition` + `anticipatory_retaliation_context` replaces it
- `continuing_violation_recognized` → dropped; slug `continuing-violation` in
  `ws_legal_recognition` replaces it (no companion needed)
- `equitable_tolling_recognized`   → dropped; slug `equitable-tolling` in
  `ws_legal_recognition` replaces it (no companion needed)
- `has_amended_claim_recognized`   → slug `amended-claim` in `ws_legal_recognition`
   + `amended_claim_context` companion display
- `arbitration_waiver_enforceability` → `contractual_waiver_scope`
   (expanded to cover NDAs and non-disparagement agreements)
- `contractual-waiver` in `legal_recognitions` + `contractual_waiver_context` companion display + `contractual_waiver_scope` + sister 
- `disclosure_target_type`         → `_disclosure_target_class` (derived, hidden)
- `exhaustion_type`                → `exhaustion_class`

---

## Relationship Direction Contract (For Sync)

- Parent-bearing legal records: `citation`, `construction`.
- Child-bearing legal records: `statute`, `common_law`.

---

### Cross-Tab Conditional: mixed-motive → mixed_motive_remedy_context

When `burden_shifting_framework` (Burden Of Proof tab) includes `mixed-motive`,
the field `mixed_motive_remedy_context` (Enforcement tab) becomes relevant.
ACF conditional logic cannot surface this cross-tab dependency natively.

Implementation: register an `acf/save_post` hook (or `admin_notices` hooked to
`current_screen`) that detects `mixed-motive` in `burden_shifting_framework` and
emits a dismissible admin notice directing the editor to the Enforcement tab:

> "Mixed-motive framework selected — please complete the 'Mixed Motive Remedy
> Context' field on the Enforcement tab."

Notice should fire on the edit screen for all four legal record CPTs.
Dismiss state does not need to persist — the notice should reappear on each
save as long as `mixed-motive` is present and `mixed_motive_remedy_context`
is empty.

---

## Slug-to-Companion Map (ws_legal_recognition taxonomy)

Presence = true
 * Used for bool-state values of Legal Recognitions where true when:
 *  - Recognized  — judicial doctrines courts have acknowledged
 *  - Available   — procedural/remedy mechanisms
 *  - Permitted   — rights that can be exercised
 *  - Required    — mandatory procedural obligations
 *  - Specified   — explicit editorial enumeration
 *  - Applies     — statutory conditions that operate by force of law

Companion fields noted in parentheses are triggered by slug presence in `legal_recognitions`.

```
// Effective Dates Tab
'retroactive-date'              → 'retro_context' + 'retro_date'
// Classification Tab
'excluded-class'                → 'excluded_class_context' + 'excluded_classes'
'protected-action'              → 'protected_actions_context' + 'protected_actions' + 'protected_action_standard' + 'protected_action_source'
'anonymity-protection'          → 'anonymity_context'
// Statute of Limitations Tab
'statutory-tolling'             → 'statutory_tolling_context'
'equitable-tolling'             → 'equitable_tolling_context'
'amended-claim'                 → 'amended_claim_context'
'exhaustion-required'           → 'exhaustion_context'
'pre-filing-notice'             → 'filing_notice_context'
'statutory-preclusion'          → 'preclusion_context'
// Enforcement Tab
'private-right-of-action'       → 'private_roa_context'
'cats-paw-liability'            → 'cats_paw_context'
'contractual-waiver'            → 'contractual_waiver_context' → 'contractual_waiver_scope'
'nda-limits'                    → 'nda_limits_context'
'jury-trial'                    → 'jury_trial_context' → 'jury_trial_scope'
// Without Context
'continuing-violation'           (no companion needed)
'individual-liability'           (no companion needed)
'class-action'                   (no companion needed)
'preliminary-reinstatement'      (no companion needed)

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

- This draft treats the statute set as baseline for broad legal parity, then adds
  per-type deltas.
- `ws_legal_recognition` is a presence/absence signal table, not a classification
  table.
- Fields marked (no companion needed) in the [Slug-to-Companion Map]
  ('continuing-violation', 'individual-liability', 'class-action',
   'preliminary-reinstatement') are captured exclusively via `ws_legal_recognition`
   taxonomy. No separate ACF field is registered for them.
