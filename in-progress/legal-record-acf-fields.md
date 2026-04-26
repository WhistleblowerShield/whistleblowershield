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
- `*_details` — freetext(usually) companion, conditional on `has_*` bool true, or `has-details` sentinel
   present in trigger choice/taxonomy field.
  `*_details` may have a sister field; no naming convention applies to sister fields, apply logical name
   using context.
- `*_context` — freetext(usually) companion, conditional on trigger field when specified
   value, values, or any non-empty value is present, defined by trigger requirements.
- `*_limits` — preferred over `*_limitations`.
- Derived values need hooks on fill or update.
- Merged values (usually hidden) need hooks on save.
- `fee_shifting_rules` needs hook to catch contradictory terms.
- `sovereign_immunity_limits` needs hook to catch contradictory terms.
- `primary_agency` needs hook to auto-fill on first added `ws-agency`, if non-empty. Manual override select needs
   hook to filter choices to attached `ws-agency` posts.
   Hook should set 'default_value' key to: "Attach one 'ws-agency' first".
- `primary_agency_is_fed` needs hook to auto-fill true if `primary_agency` jx is 'us'.
- `local_agencies` needs hook to filter to only jx applicable `ws-agency` choices, and exclude federal agencies.
- `federal_agencies` needs hook to filter to only federal agencies.
- Some suffixes define data-shape (e.g. `*_url`, `*_date`, `*_email`, etc.). Do not use data-shape suffixes
  otherwise.

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
- `effective_date`             — (conditional on `has_effective_date`)
- `effective_year`             — (derived from `effective_date` if present, `date` if not)
- `retro_date`                 — (sister field to `retro_context`)
- `retro_context`              — (conditional on `retroactive-date-defined` in `legal_recognitions`)

---

### Classification Tab

Fields ordered: activity standard → disclosure → classes → sectors → targets →
legal recognitions → derived flags.

- `legal_recognitions`             — (taxonomy: `ws_legal_recognition`; replaces all `*_recognized` booleans,
                                      and others; See [Slug-to-Companion Map] below.)
- `protected_action_standard`      — (conditional on `protected-action-specified` in `legal_recognitions`;
                                      single-select: `actual_violation`|`reasonable_belief`|`good_faith`)
- `reasonable_belief_context`      — (conditional on `protected_activity_standard` is `reasonable_belief`;
                                      single-select: `objective_only`|`subjective_only`|`dual_prong`|`has-details`)
- `reasonable_belief_details`
- `protected_actions`              — (conditional on `protected-action-specified` in `legal_recognitions`;
                                      taxonomy: `ws_protected_action`)
- `disclosure_types`               — (taxonomy: `ws_disclosure_type`)
- `protected_classes`              — (taxonomy: `ws_protected_class`)
- `protected_class_details`
- `excluded_classes`               — (conditional on `excluded-class-specified` in `legal_recognitions`;
                                      taxonomy: `ws_excluded_class`)
- `excluded_class_details`
- `employment_sectors`             — (taxonomy: `ws_employment_sector`)
- `disclosure_targets`             — (taxonomy: `ws_disclosure_target`)
- `disclosure_target_details`
- `adverse_action_scope`           — (single-select: `termination-only`|`material-adverse`|
                                      `broad-any-adverse-action`|`see-context`)
- `adverse_action_scope_context`   — (conditional on `adverse_action_scope` non-empty)
- `anonymity_context`              — (conditional on `anonymity-protection` in `legal_recognitions`)

---

### Statute of Limitations And Thresholds Tab

Fields ordered: core SOL → modifiers → exhaustion → thresholds → preemption.

- `sol_value`                  — (integer)
- `sol_unit`                   — (single-select: `days`|`weeks`|`months`|`years`)
- `sol_trigger`                — (single-select: `accrual`|`discovery-actual`|
                                  `discovery-constructive`|`discovery-notice`|`see-context`)
- `sol_trigger_context`        — (conditional on `sol_trigger` non-empty)
- `has_sol_details`
- `sol_details`
- `statutory_tolling_context`  — (conditional on `statutory-tolling` in `legal_recognitions`)
- `equitable_tolling_context`  — (conditional on `equitable-tolling` in `legal_recognitions`)
- `amended_claim_context`      — (conditional on `amended-claim` in `legal_recognitions`)
- `exhaustion_class`           — (sister field to `exhaustion_context`; single-select: `jurisdictional`|
                                  `claims-processing`|`waivable`|`see-context`)
- `exhaustion_context`         — (conditional on `exhaustion-required` in `legal_recognitions`)
- `pre_filing_notice_context`  — (conditional on `pre-filing-notice-required` in `legal_recognitions`)
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
- `fee_shifting_phases`        — (conditional on `fee_shifting_rules` includes `has-phases`)
- `fee_shifting_details`       — (conditional on `fee_shifting_rules` includes `has-details`)
- `remedies`                   — (taxonomy: `ws_remedy`)
- `remedy_limits`              — (conditional on `remedies` includes `has-limits`)
- `remedy_details`             — (conditional on `remedies` includes `has-details`)
- `mixed_motive_remedy_context` — (conditional on `burden_shifting_framework` includes `mixed-motive`;
                                   see [Cross-Tab Conditional below])
- `cats_paw_context`           — (conditional on `cats-paw-liability` in `legal_recognitions`)
- `criminal_sanction`          — (single-select: `misdemeanor`|`felony`)
- `class_action_waiver_scope`  — (single-select: `prohibited`|`permitted-individual-only`|
                                  `permitted-collective`|`see-context`)
- `contractual_waiver_context` — (conditional on `contractual_waiver_scope` non-empty)
- `contractual_waiver_scope`   — (conditional on `contractual-waiver` in `legal_recognitions`;
                                  single-select: `void`|`limited`|`enforceable`|`mixed`|
                                  `void-public-policy`|`void-as-to-whistleblowing`|
                                  `enforceable-with-exceptions`)
- `contractual_waiver_context` — (conditional on `contractual_waiver_scope` non-empty)
- `nda_limits_context`         — (conditional on `nda-limits` in `legal_recognitions`)
- `primary_agency`
- `local_agencies`
- `enforcement_channel`        — (priority of enforcement agencies, with any enforcement nuance)
- `sovereign_immunity_limits`  — (multi-select: `not-waived`|`partially-waived`|`fully-waived`|`cap-applies`|`conditions-apply`|`has-details`)
- `sovereign_immunity_details`
- `proper_defendants`          — (multi-select: `employer-entity-only`|`individual-supervisors`|`government-agency-only`|`contractors-included`|`scope-of-employment-required`|`has-details`)
- `proper_defendant_details`

---

### Burden Of Proof Tab

Fields ordered: framework → employee standards → causation → temporal presumption →
employer defenses → rebuttable presumption → detail overflow.

- `has_burden_shifting`
- `burden_shifting_framework`  — (sister field; conditional on `has_burden_shifting`;
                                  single-select: `McDonnell-Douglas`|`motivating-factor`|
                                  `but-for`|`mixed-motive`|`see-context`; see [Cross-Tab Conditional] below)
- `burden_shifting_details`
- `burden_shifting_context`    — (conditional on `burden_shifting_framework` non-empty)
- `employee_standards`         — (taxonomy: `ws_employee_standard`; evidentiary burden only)
- `standard_details`
- `causation_standards`        — (taxonomy: `ws_causation_standard`; causal link standard)
- `causation_standard_details`
- `has_temporal_presumption`
- `presumption_window_value`   — (sister field; conditional on `has_temporal_presumption`)
- `presumption_window_unit`    — (sister field; conditional on `has_temporal_presumption`;
                                  single-select: `days`|`weeks`|`months`|`years`)
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

## Slug-to-Companion Map (ws_legal_recognition taxonomy)

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
