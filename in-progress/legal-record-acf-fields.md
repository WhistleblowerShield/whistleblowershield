# Legal Record ACF Canonical Field Draft

Purpose: draft a unified, prefix-free field set for all four legal record types
(`statute`, `common_law`, `citation`, `construction`) as the first step toward a
2-pipeline ingest rewrite (`legal-records`, `assist-org-records`).

---

## Naming Rules Applied

- No CPT infix or storage prefix in this draft (no `ws_jx_*`).
- `snake_case` only.
- Booleans use `has_*` or `is_*`.
- Single-value datapoints use singular nouns.
- Multi-value arrays use plural nouns.
- `*_recognized` — avoid entirely; use `ws_legal_recognition` taxonomy slug instead.
- `*_type` — avoid; use `*_class`, `*_scope`, `*_status`, `*_rule`, `*_framework`,
  `*_weight`, or `*_standard` depending on context.
- `*_details` — freetext companion, conditional on `has_*` is true.
  `*_details` may have a sister field; no naming convention applies to sister fields.
- `*_framework` — select companion (sister field), triggered by `has_*` boolean.
- `*_context` — freetext companion, conditional on trigger field when specified
  value, values, or any non-empty value depending on trigger requirements.
  Never triggered by `has_*` — that pattern uses `*_details`.
- `*_class` — preferred over `*_type` for derived select companions.
- `*_limits` — preferred over `*_limitations`.
- Derived values need hooks on fill.
- Merged arrays need hooks on save.
- `fee_shifting_rules` needs hook to catch contradictory terms.

### Sentinel Values

- `has-details` — sentinel in taxonomy arrays; triggers `*_details` companion
   via `has_*` boolean pattern.
- `has-limits` — sentinel in `ws_remedy`; triggers `remedy_limits` companion field.
- `has-phase-specifics` — sentinel in `ws_fee_shifting_rule`; triggers
  `fee_shifting_phases` companion field.
- `see-details` — used in place of `has-details` value in ACF select/multi-select fields
   where `*_details` has already been triggered.
- `see-context` — used in place of `has-details` value in ACF select/multi-select fields
   where `*_context` has already been triggered.

---

## Attached Plain-English

All four legal record types use the plain-english summary system.

---

## Common Fields (Apply To All 4 Legal Record Types)

These normalized canonical fields exist in every legal-record ACF.
Field order reflects logical editorial workflow within each tab.

---

### Identity And Publishing Tab

- `jurisdiction`
- `official_name`
- `common_name`
- `citation`                   — statute citation / case name (shared slot)
- `protection_scope`           — (taxonomy: `ws_protection_scope`, dupe of `ws_procedure_type`)
- `general_description`        — (brief; reserve full summary for `plain_english_wysiwyg`)
- `has_attach_flag`
- `display_order`

---

### Effective Date Tab

- `date`                       — enacted / ruling / decision date (shared slot)
- `effective_year`             — (derived from `date`; hook fills when `effective_date` absent)
- `has_effective_date`         — (only when `effective_date` differs from `date`)
- `effective_date`             — (conditional on `has_effective_date`)
- `has_retro_date`             — (only when legal record defines a retroactive start date)
- `retro_date`                 — (conditional on `has_retro_date`)
- `retro_details`              — (conditional on `has_retro_date`)

---

### Classification Tab

Fields ordered: activity standard → disclosure → classes → sectors → targets →
legal recognitions → derived flags.

- `protected_activity_standard`    — (single-select: `actual_violation`|`reasonable_belief`|`good_faith`)
- `reasonable_belief_context`      — (conditional on `protected_activity_standard` is `reasonable_belief`;
                                      single-select: `objective_only`|`subjective_only`|`dual_prong`|`has-details`)
- `reasonable_belief_details`      — (sister field; conditional on `reasonable_belief_context` is `has-details`)
- `disclosure_types`               — (taxonomy: `ws_disclosure_type`)
- `protected_actions`              — (taxonomy: `ws_protected_action`)
- `protected_classes`              — (taxonomy: `ws_protected_class`)
- `protected_class_details`        — (conditional on `protected_classes` includes `has-details`)
- `excluded_classes`               — (taxonomy: `ws_excluded_class`; when legal record specifies an exclusion)
- `excluded_class_details`         — (conditional on `excluded_classes` includes `has-details`)
- `employment_sectors`             — (taxonomy: `ws_employment_sector`)
- `disclosure_targets`             — (taxonomy: `ws_disclosure_target`)
- `disclosure_target_details`      — (conditional on `disclosure_targets` includes `has-details`)
- `legal_recognitions`             — (taxonomy: `ws_legal_recognition`; replaces all *_recognized booleans.
                                      See `ws_legal_recognition` slug-to-companion map below.)
- `adverse_action_scope`           — (single-select: `termination-only`|`material-adverse`|
                                      `broad-any-adverse-action`|`see-context`)
- `adverse_action_scope_context`   — (conditional on `adverse_action_scope` non-empty)
- `anonymity_context`              — (conditional on `anonymity-protection` in `legal_recognitions`)

---

### Statute of Limitations And Thresholds Tab

Fields ordered: core SOL → modifiers → exhaustion → thresholds → preemption.

- `sol_value`
- `sol_unit`
- `sol_trigger`                — (single-select: `accrual`|`discovery-actual`|
                                  `discovery-constructive`|`discovery-notice`|`mixed`)
- `sol_trigger_context`        — (conditional on `sol_trigger` non-empty)
- `has_sol_details`
- `sol_details`
- `has_tolling`
- `tolling_details`
- `amended_claim_context`      — (conditional on `amended-claim` in `legal_recognitions`)
- `has_exhaustion_requirement`
- `exhaustion_details`
- `exhaustion_class`           — (sister field; conditional on `has_exhaustion_requirement`;
                                  single-select: `jurisdictional`|`claims-processing`|`waivable`|`mixed`)
- `has_pre_filing_notice`
- `pre_filing_notice_details`
- `has_employer_threshold`
- `threshold_details`
- `has_preemption`
- `preemption_direction`       — (sister field; conditional on `has_preemption`;
                                  single-select: `federal_preempts_state`|`state_not_preempted`|
                                  `partial`|`unclear`)
- `preemption_details`

---

### Enforcement Tab

Fields ordered: process → adverse actions → fee shifting → remedies →
liability → contractual → agencies → immunity/defendants.

- `process_types`              — (taxonomy: `ws_process_type`)
- `adverse_actions`            — (taxonomy: `ws_adverse_action`)
- `adverse_action_details`     — (conditional on `adverse_actions` includes `has-details`)
- `constructive_discharge_context`   — (conditional on `adverse_actions` includes `constructive-discharge`)
- `anticipatory_retaliation_context` — (conditional on `adverse_actions` includes `threatened-retaliation`)
- `fee_shifting_rules`         — (taxonomy: `ws_fee_shifting_rule`)
- `fee_shifting_details`       — (conditional on `fee_shifting_rules` includes `has-details`)
- `fee_shifting_phases`        — (conditional on `fee_shifting_rules` includes `has-phase-specifics`)
- `remedies`                   — (taxonomy: `ws_remedy`)
- `remedy_limits`              — (conditional on `remedies` includes `has-limits`)
- `remedy_details`             — (conditional on `remedies` includes `has-details`)
- `mixed_motive_remedy_context` — (conditional on `burden_shifting_framework` includes `mixed-motive`)
- `cats_paw_context`           — (conditional on `cats-paw-liability` in `legal_recognitions`)
- `criminal_sanction`          — (single-select: `misdemeanor`|`felony`)
- `class_action_waiver`        — (single-select: `prohibited`|`permitted-individual-only`|
                                  `permitted-collective`|`mixed`)
- `contractual_waiver_scope`   — (conditional on `contractual-waiver` in `legal_recognitions`;
                                  single-select: `void`|`limited`|`enforceable`|`mixed`|
                                  `void-public-policy`|`void-as-to-whistleblowing`|
                                  `enforceable-with-exceptions`)
- `contractual_waiver_context` — (conditional on `contractual_waiver_scope` non-empty)
- `nda_limits_context`         — (conditional on `nda-limits` in `legal_recognitions`)
- `primary_agency`
- `local_agencies`
- `enforcement_channel`        — (priority of enforcement agencies, with any enforcement nuance)
- `sovereign_immunity_limits`
- `proper_defendants`

---

### Burden Of Proof Tab

Fields ordered: framework → employee standards → causation → temporal presumption →
employer defenses → rebuttable presumption → detail overflow.

- `has_burden_shifting`
- `burden_shifting_framework`  — (sister field; conditional on `has_burden_shifting`;
                                  single-select: `McDonnell-Douglas`|`motivating-factor`|
                                  `but-for`|`mixed-motive`|`see-context`)
- `burden_shifting_details`    — (conditional on `has_burden_shifting`)
- `burden_shifting_context`    — (conditional on `burden_shifting_framework` non-empty)
- `employee_standards`         — (taxonomy: `ws_employee_standard`; evidentiary burden only)
- `standard_details`           — (conditional on `employee_standards` includes `has-details`)
- `causation_standards`        — (taxonomy: `ws_causation_standard`; causal link standard)
- `causation_standard_details` — (conditional on `causation_standards` includes `has-details`)
- `has_temporal_presumption`
- `presumption_window_value`   — (sister field; conditional on `has_temporal_presumption`)
- `presumption_window_unit`    — (sister field; conditional on `has_temporal_presumption`;
                                  single-select: `days`|`weeks`|`months`)
- `employer_defenses`          — (taxonomy: `ws_employer_defense`)
- `defense_details`            — (conditional on `employer_defenses` includes `has-details`)
- `has_rebuttable_presumption`
- `rebuttable_details`
- `has_bop_details`
- `bop_details`

---

### Reward Tab

- `has_reward_available`
- `reward_details`             — (conditional on `has_reward_available`)
- `reward_discretion_standard` — (sister field; conditional on `has_reward_available`;
                                  single-select: `mandatory`|`discretionary`|`presumptive`|
                                  `formula-based`|`has-details`)
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
- `authority_reference`

---

### Hidden Fields (no tab; prefixed with underscore)

- `_id`                        — (generated by ingest tool or matrix seeder)
- `_disclosure_target_class`   — (derived from `disclosure_targets`; hook fills;
                                  single-select: `internal`|`external`|`both`)

---

## Specialized Fields By Legal Record Type

---

### Substantive-Record Common Fields (statute + common_law)

Reciprocal rule: any substantive field that a precedent can extend, suppress, or
construe is covered on precedent records via `extend_taxonomy` and
`suppress_taxonomy` — precedents do not duplicate individual substantive fields.

#### Enforcement Tab (insert after `anticipatory_retaliation_context`)
- `election_of_remedies_rule`      — (multi-select ACF: `administrative-bars-civil`|
                                      `state-bars-federal`|`remedy-exclusivity`|
                                      `first-filed-controls`|`no-election-required`|`see-context`)
- `election_of_remedies_context`   — (conditional on `election_of_remedies_rule` non-empty)

#### Relationships Tab
- `citation_ids`
- `construction_ids`

#### Hidden Fields
- `_precedent_ids`             — (merged array of `citation_ids` and `construction_ids`; hook fills)

---

### Statute-Specific

#### Enforcement Tab
- `federal_agencies`           — (insert after `local_agencies`)

#### Hidden Fields
- `_primary_agency_is_fed`     — (derived from `primary_agency` jx; hook fills)
- `_related_agencies`          — (merged array of `local_agencies` and `federal_agencies`; hook fills)

---

### Common-Law-Specific

#### Identity and Publishing Tab (insert after `citation`)
- `precedent_common`           — (common name for precedent case held in `citation`)

#### Classification Tab (insert after `excluded_class_details`)
- `doctrine_basis`             — (the legal basis for the doctrine; reserve full summary for `plain_english_wysiwyg`)
- `public_policy_sources`      — (multi-select; sources of public policy)
- `source_context`             — (conditional on `public_policy_sources` includes `other`)
- `recognition_status`         — (single-select: `recognized`|`limited`|`uncertain`|
                                  `rejected`|`abrogated`|`has-details`)
- `recognition_details`        — (conditional on `recognition_status` is `has-details`)

#### Statute of Limitations And Thresholds Tab
- `has_statutory_preclusion`
- `preclusion_details`

---

### Precedent-Record Common Fields (citation + construction)

#### Identity and Publishing Tab
- `types`                      — (citation: multi-select; construction: single-select;
                                  field_key and field_name differ by singular/plural)
- `status`                     — (single-select: `published`|`unpublished`|`memorandum`|`vacated`)
- `binding_scope`              — (single-select: `binding`|`persuasive`|`mixed`|`distinguished`)
- `court`
- `court_name`                 — (conditional on `court` is `other`)
- `court_is_fed`               — (derived from `court` jx; manually set when `court` is `other`)

#### Effective Date Tab (insert after `effective_date`)
- `mandate_date`

#### Classification Tab
- `scope`                      — (single-select: `favorable`|`adverse`|`neutral`)
- `extend_taxonomy`            — (when `scope` is `favorable`; extends parent taxonomy)
- `suppress_taxonomy`          — (when `scope` is `adverse`; removes parent taxonomy term)
- `has_affected_jx`            — (derived true if `court_is_fed`; manually set false when single jx)
- `affected_jx`                — (conditional on `has_affected_jx`)

#### Relationships Tab
- `statute_ids`
- `comlaw_ids`
- `parent_weight`              — (single-select: `primary`|`secondary`|`distinguishing_only`)
- `negative_treatment_flag`

#### Hidden Fields
- `_parent_ids`                — (merged array of `statute_ids` and `comlaw_ids`; hook fills)

---

### Citation-Specific

#### Identity and Publishing Tab (insert after `types`)
- `citation_type_rationale`    — (freetext; explains `types` assigned)

---

### Construction-Specific

#### Identity and Publishing Tab
- `is_en_banc`                 — (boolean; defaults true; when false triggers `panel_composition_details`)
- `panel_composition_details`  — (conditional on `is_en_banc` is false)

---

## Rename Normalization (Current → Canonical)

Only fields that currently violate target naming conventions or are inconsistent
across legal ACFs.

- `fee_shiftings`                  → `fee_shifting_rules` →
 (taxonomy table `ws_fee_shifting` → `ws_fee_shifting_rule`)
- `has_limit_ambiguous`            → `has_sol_details`
- `limit_details`                  → `sol_details`
- `has_tolling_details`            → `has_tolling`
- `has_exhaustion_required`        → `has_exhaustion_requirement`
- `exhaustion_required_details`    → `exhaustion_details`
- `exhaustion_is_jurisdictional`   → `exhaustion_class` (sister field to `exhaustion_details`)
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
- `has_amended_claim_recognized`   → slug `amended-claim` in `ws_legal_recognition` + `amended_claim_context` companion display
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

## ws_legal_recognition — Slug-to-Companion Map

Presence = recognized. Absence = not recognized or statute silent.
Companion fields noted in parentheses are triggered by slug presence in `legal_recognitions`.

```
continuing-violation           (no companion needed)
equitable-tolling              (no companion needed)
amended-claim                 → amended_claim_context
private-right-of-action        (no companion needed)
individual-liability           (no companion needed)
preliminary-reinstatement      (no companion needed)
class-action-permitted         (no companion needed)
constructive-discharge        → constructive_discharge_context
anticipatory-retaliation      → anticipatory_retaliation_context
cats-paw-liability            → cats_paw_context
contractual-waiver            → contractual_waiver_context → contractual_waiver_scope
anonymity-protection          → anonymity_context
nda-limits                    → nda_limits_context

```

---

## Taxonomy Reference

New taxonomies added in 3.15.0:

- `ws_legal_recognition` — presence/absence signal table for recognized legal
  doctrines and procedural rules. Flat. Attached to all 4 legal CPTs.
- `ws_causation_standard` — causation link standards, split from `ws_employee_standard`.
  Flat. Attached to all 4 legal CPTs.

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
  table. Do not add terms that require a companion value — use `has_*` boolean
  fields for those.
- Fields marked `(no ACF field)` in the SOL tab (continuing-violation,
  equitable-tolling) are captured exclusively via `ws_legal_recognition` taxonomy.
  No separate ACF field is registered for them.
