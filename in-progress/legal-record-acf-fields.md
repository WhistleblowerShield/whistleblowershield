# Legal Record ACF Canonical Field Draft

Purpose: draft a unified, prefix-free field set for all four legal record types (`statute`, `common_law`, `citation`, `construction`) as the first step toward a 2-pipeline ingest rewrite (`legal-records`, `assist-org-records`).

## Naming Rules Applied
- No CPT infix or storage prefix in this draft (no `ws_jx_*`).
- `snake_case` only.
- Booleans use `has_*` or `is_*`.
- Single-value datapoints use singular nouns.
- Multi-value arrays use plural nouns.
- Detail companions use `*_details`, conditional of trigger bool `has_*` is true.
- Context companions use `*_context`, conditional of trigger field non-empty.
- Derived values need hooks on fill.
- Merged arrays need hooks on save.
- `fee_shifting_rules` needs hook to catch contradictory terms.

## Attached Plain-English
- All four legal record types use plain-english summary system.

## Common Fields (Apply To All 4 Legal Record Types)
These are the normalized canonical fields that should exist in every legal-record ACF.

### Identity And Publishing Tab
- `jurisdiction`
- `official_name`
- `common_name`
- `citation`
- `protection_scope`       — (new taxonomy, dupe of `ws_procedure_type`)
- `general_description`    — (brief, reserve full summary for `plain_english_wysiwyg`)
- `has_attach_flag`
- `display_order`

### Effective Date Tab
- `date`
- `effective_year`         — (derived from `date` when `effective_date` is not present)
- `has_effective_date`     — (only use when `effective_date` differs from `date`)
- `effective_date`
- `has_retro_date`         — (only use when legal record defines a retroactive start date)
- `retro_date`
- `retro_details`          — (required when `retro_date` present)

### Classification Tab
- `protected_activity_standard`    — (single-select: `actual_violation`|`reasonable_belief`|`good_faith`)
- `reasonable_belief_test`         — (conditional on `protected_activity_standard` is `reasonable_belief`; single-select: `objective_only`|`subjective_only`|`dual_prong`|`has_details`)
- `reasonable_belief_details`
- `disclosure_types`
- `protected_actions`
- `protected_classes`
- `protected_class_details`
- `excluded_classes`       — (new taxonomy, dupe of `ws_protected_class` use when legal record specifies an exclusion; does not include all-sectors (all-employees))
- `excluded_class_details`
- `employment_sectors`
- `disclosure_targets`
- `disclosure_target_details`
- `disclosure_target_type` — (derived from `disclosure_targets`, single-select: `internal`|`external`|`both`)

### Statute of Limitations And Thresholds Tab
- `sol_value`
- `sol_unit`
- `sol_trigger`            — (single-select: `accrual`|`discovery`|`discovery-due-diligence`|`mixed`)
- `sol_trigger_context`    — (conditional on `sol_trigger` is non-empty)
- `has_sol_details`
- `sol_details`
- `continuing_violation_recognized`
- `has_tolling`
- `tolling_details`
- `equitable_tolling_recognized`
- `has_exhaustion_requirement`
- `exhaustion_details`
- `exhaustion_rule_type`   — (single-select: `jurisdictional`|`claims-processing`|`waivable`|`mixed`)
- `has_employer_threshold`
- `threshold_details`
- `has_amended_claim_recognized`
- `amended_claim_details`

### Enforcement Tab
- `process_types`
- `adverse_actions`
- `adverse_action_details`
- `adverse_action_scope`   — (single-select: `termination-only`|`material-adverse`|`broad-any-adverse-action`|`has-details`)
- `adverse_action_scope_details`
- `fee_shifting_rules`
- `fee_shifting_details`
- `fee_shifting_phases`
- `remedies`
- `remedy_limits`
- `remedy_details`
- `private_right_of_action_available`
- `individual_liability_available`
- `arbitration_waiver_enforceability` — (single-select: `void`|`limited`|`enforceable`|`mixed`)
- `class_action_waiver`               — (single-select: `prohibited`|`permitted-individual-only`|`permitted-collective`|`mixed`)
- `primary_agency`
- `primary_agency_is_fed`  — (derived from `primary_agency` jx)
- `local_agencies`
- `federal_agencies`
- `related_agencies`       — (merged array of `local_agencies` and `federal_agencies`)
- `enforcement_channel`    — (priority of enforcement agencies, with any enforcement nuance)
- `sovereign_immunity_limits`
- `proper_defendants`

### Burden Of Proof Tab
- `burden_shifting_framework`  — (single-select: `McDonnell-Douglas`|`motivating-factor`|`but-for`|`mixed-motive`|`has-details`)
- `burden_shifting_details`
- `employee_standards`
- `standard_details`
- `employer_defenses`
- `defense_details`
- `has_rebuttable_presumption`
- `rebuttable_details`
- `has_bop_details`
- `bop_details`

### Reward Tab
- `has_reward_available`
- `reward_details`
- `reward_discretion_standard` — (single-select: `mandatory`|`discretionary`|`presumptive`|`has-details`)
- `reward_discretion_details`

### Relationships Tab
- `ref_materials`
- `superseded_by`

### Source / Audit Tab
- `last_reviewed`              — (manually updated when information is reviewed for accuracy)
- `url`
- `url_is_pdf`
- `authority_reference`

### Hidden Fields (no tab; prefixed with underscore)
- `_id`                        — (generated by ingest tool or matrix seeder)

## Specialized Fields By Legal Record Type
These fields are additive (or relationship-direction specific) and are not shared by all four.

### Substantive-Record Common Fields
# Enforcement Tab
- `election_of_remedies_rule`  — (insert after `adverse_action_details`)
# Relationships Tab
- `citation_ids`
- `construction_ids`
# Hidden Fields
- `_precedent_ids`          — (insert after `_id`; merged array of `citation_ids` and `construction_ids`)

### Statute-Specific
- none

### Common-Law-Specific
# Identity and Publication Tab
- `precedent_common`       — (insert after `citation`; common name for precedent case held in `citation`)
# Classification Tab
- `doctrine_basis`
- `public_policy_sources`
- `other_sources`
- `recognition_status`     — (single-select: `recognized`|`limited`|`uncertain`|`rejected`|`has-details`)
- `recognition_details`
# Statute of Limitations and Thresholds Tab
- `has_statutory_preclusion`
- `preclusion_details`

### Precedent-Record Common Fields
# Identity and Publication Tab
- `type(s)`                 — (multi-select for `citation`, single-select for `construction`, `field_key` and `field_name` affected by singular/plural)
- `status`                  — (single-select: `published`|`unpublished`|`memorandum`|`vacated`)
- `binding_scope`           — (single-select: `binding`|`persuasive`|`mixed`)
- `court`
- `court_name`              — (when `court` selected is `other`)
- `court_is_fed`            — (derived from `court` jx, manually set if `court` selected is `other` and is federal)
# Effective Date Tab
- `mandate_date`            — (insert after `effective_date`)
# Classification Tab
- `scope`                   — (single-select: `favorable`|`adverse`|`neutral`)
- `extend_taxonomy`         — (when `precedent` scope `favorable` extends `parent` taxonomy, e.g. adds a `protected_class`)
- `suppress_taxonomy`       — (when `precedent` scope `adverse` removes `parent` taxonomy, e.g. removes a `protected_class`)
- `has_affected_jx`         — (derived true if `court_is_fed`, manually set to false when single jx affected)
- `affected_jx`
# Relationships Tab
- `statute_ids`
- `comlaw_ids`
- `parent_weight`           — (single-select: `primary`|`secondary`|`distinguishing_only`)
- `negative_treatment_flag`
# Hidden Fields 
- `_parent_ids`             — (insert after `_id`; merged array of `statute_ids` and `comlaw_ids`)


### Citation-Specific
# Identity and Publication Tab
- `citation_type_rationale` — (insert after `types`)

### Construction-Specific
# Identity and Publication Tab
- `is_en_banc`              — ([unique] defaults true; when false triggers `panel_composition_details`)
- `panel_composition_details`

## Rename Normalization (Current -> Canonical)
Only listing fields that currently violate the target naming conventions or are inconsistent across legal ACFs.

- `fee_shiftings`              -> `fee_shifting_rules` — (taxonomy table `ws_fee_shifting` -> `ws_fee_shifting_rule`)
- `has_limit_ambiguous`        -> `has_sol_details`    — (allowed use of `_details` on `has_` conditional name)
- `limit_details`              -> `sol_details`
- `has_tolling_details`        -> `has_tolling`
- `has_exhaustion_required`    -> `has_exhaustion_requirement`
- `exhaustion_required_details`    -> `exhaustion_details`
- `rebuttable_presumption_details` -> `rebuttable_details`
- `statutory_preclusion_details`   -> `preclusion_details`
- `employee_standard_details`  -> `standard_details`
- `employer_defense_details`   -> `defense_details`
- `employer_threshold_details` -> `threshold_details`
- `doctrine_basis_wysiwyg`     -> `doctrine_basis`
- `recognition_status_wysiwyg` -> `recognition_status`
- `doctrine_name`              -> `official_name`
- `statute_citation` / `precedent_name` / `case_name` (shared slot) -> `citation`
- `enacted_date` / `ruling_date` / `decision_date`    (shared slot) -> `date`
- `statute_url` / `precedent_url` / `citation_url` / `construction_url` (shared slot) -> `url`
- `statute_url_is_pdf` / `precedent_url_is_pdf` / `citation_url_is_pdf` / `construction_url_is_pdf` (shared slot) -> `url_is_pdf`

## Relationship Direction Contract (For Sync)
- Parent-bearing legal records: `citation`, `construction`.
- Child-bearing legal records: `statute`, `common_law`.

## Notes
- This draft intentionally treats the statute set as baseline for broad legal parity, then adds per-type deltas.
- Fields like common-law: federal-agency handling can be policy-constrained at validation/query layers while
  the field exists for structural parity, but is not used.



