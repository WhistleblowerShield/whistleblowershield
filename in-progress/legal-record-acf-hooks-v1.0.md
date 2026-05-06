# Legal Record ACF Hooks (v1.0)

**Purpose:** Legal-record-specific hook requirements. The slug-to-companion map for `ws_legal_recognition`, the
cross-field and cross-tab hook tables that drive validation, and worked examples of legal-record-specific hook
patterns.

**Scope:** Legal records only (`statute`, `common_law`, `citation`, `construction`). Companion documents are
authoritative for everything else this doc references.

**Companion documents.**

- `ws-acf-field-guidance-v1.0.md` — naming rules, companion-suffix doctrine, sentinels, conditional annotation.
- `ws-acf-hook-guidance-v1.0.md` — hook philosophy, validation patterns, helper definitions, generic hook
  examples.
- `legal-record-acf-fields-v3.0.md` — field declarations, tab structure, prompt-schema mapping for legal records.

---

## Precedent Taxonomy Mapping

`extended_taxonomies` and `suppressed_taxonomies` use filtered taxonomy-term choices. `taxonomy` choices come
from the allowlist of legal-record taxonomies that precedent records **are capable of** realistically extending or
suppress (see
the eligible-taxonomy allowlist in the field spec). `term` choices are filtered by the selected `taxonomy` in the
same repeater row.

Available terms for `extended_taxonomies` must exclude terms already present in the parent legal record (you
cannot extend a doctrine that already applies). Available terms for `suppressed_taxonomies` must be limited to
terms present in the parent record (you cannot suppress a doctrine that does not apply). Hooks must monitor both
values as slugs and validate on save.

---

## Agency Filtering

`primary_agency` auto-fills with the first attached `ws-agency` post when empty; choices filter to currently
attached posts only. `local_agencies` filters to jurisdiction-applicable, non-federal `ws-agency` posts.
`federal_agencies` filters to federal `ws-agency` posts only.

Editor instructions on `primary_agency`: when empty, show "Attach one ws-agency to local or federal first"; when
non-empty, show "Override primary_agency with any currently attached local or federal agency."

Future agency filtering **are expected to** intersect `ws_process_type`, `ws_disclosure_target`, and
`ws_protected_disclosure`
taxonomies.

---

## Relationship Direction Contract

Relationship sync respects legal-record directionality. Parent-bearing record types are `citation` and
`construction`. Child-bearing record types are `statute` and `common_law`. Relationship hooks must respect these
directions when populating `_parent_ids` and `_precedent_ids`.

---

## Slug-to-Companion Map

The `ws_legal_recognition` taxonomy is the recognition taxonomy for legal records. Slugs in this taxonomy are
surfaced through the `legal_recognitions` field on every legal-record CPT and serve as bool-state values for
doctrines or operational states. Each slug presence answers a yes/no question about the record. Slugs **are
authorized to** stand
alone (no companion needed) or anchor a cluster of structured fields.

### Recognition Status Vocabulary

Slugs in this map carry a single-word status prefix that reads as "the doctrine is X when this slug is present":

- **Specified** — statute explicitly names or enumerates something
- **Recognized** — judicial doctrine courts have affirmatively acknowledged
- **Required** — mandatory obligation; non-compliance typically defeats the claim
- **Applies** — statutory condition that operates by force of law when triggered
- **Available** — mechanism or remedy that **is available to** be invoked but is not automatic
- **Permitted** — right expressly allowed; cannot be waived or procedurally blocked
- **Barred** — doctrine, action, or evidence explicitly excluded by law or rule
- **Prohibited** — conduct expressly forbidden; violation triggers statutory liability
- **Present** — clause or provision exists without implying judicial affirmation
- **Sufficient** — condition independently meets the threshold for protection to attach
- **Limited** — legal effect, scope, or enforceability is narrowed by statute or rule
- **Enforceable** — waiver, agreement, or procedural limitation **is capable of being** given legal effect
- **Void** — legal effect, scope, or enforceability is no longer relevant
- **Waived** — immunity, defense, or objection has been relinquished or abrogated

### Reading the Map

Conditional companion fields (preferably `*_context`) noted with ` → ` are revealed when the corresponding slug
is present in `legal_recognitions`. Sister fields noted with ` + ` silently inherit the conditional from the
triggered sibling and are revealed only with the sibling. Sister fields **are permitted to** have additional
conditions before
they are revealed; those conditions are documented after the sibling using `AND`, `OR`, or `NOT`. Sister fields
**are permitted to** appear before or after their sibling. The freetext sibling **is almost always** last.

Cluster requiredness rules, sentinel rules, and validation philosophy live in `ws-acf-hook-guidance-v1.0.md`.
This map declares only the trigger-to-cluster relationships and the markers that drive cross-field hooks.

### Annotation Legend

- `[R]`   — Required field for the cluster. Hook blocks save when slug is present and field is empty.
- `[+]`   — Field exists in the cluster but is revealed only when an additional documented condition is met. Inline
            field definitions document the condition. **Is permitted to** combine with `[R]` as `[+][R]`.
- `[E]`   — Slug has cross-field exclusions documented in the cross-field exclusions table below.
- `[E+]`  — Slug excludes other slugs in `legal_recognitions` (specific exclusions listed under the slug).
- `[E-]`  — Slug is excluded by another slug in `legal_recognitions` (specific blocking slug listed under the
            slug).
- `[P]`   — Prerequisite slug required (specific prerequisite listed under the slug as `* slug` or
            `* slug in taxonomy_field`).
- `[P+]`  — Paired slug mutually-required (cross-documented in the cross-field required table below).

`*_details` fields and `*_unit` fields paired to `*_value` sisters follow general rules and are not listed.

`*_context` fields are listed alongside their cluster anchor; they are required by default when revealed and
            explicitly marked `[R]` only when both context and structured sister are required.

```

// ── Identity Tab ─────────────────────────────────────────────────────────────────────────────────────
Specified:    'retroactive-date'                                      → 'retro_context'                         + 'retro_date'[R]

// ── Classification Tab ───────────────────────────────────────────────────────────────────────────────
Applies:      'manager-rule-exclusion'                                → 'manager_rule_exclusion_context'[R]
Required:     'public-concern-required'[P]                            → 'public_concern_required_context'[R]
                 * 'public-sector' in 'employment_sectors'
Applies:      'bad-faith-exclusion'                                   → 'bad_faith_exclusion_context'[R]
Specified:    'malicious-reporting-sanctions'                         → 'malicious_reporting_context'           + 'malicious_reporting_sanctions'[R]
Available:    'anonymity-protection'                                  → 'anonymity_protection_context'[R]
Specified:    'protected-action'                                      → 'protected_action_context'              + 'protected_actions'[R]                + 'protected_action_standards'          + 'protected_action_sources'            + 'reasonable_belief_context'[+][R]     + 'reasonable_belief_scope'
Specified:    'excluded-class'                                        → 'excluded_class_context'                + 'excluded_classes'[R]
Applies:      'garcetti-exception'[P]                                 → 'garcetti_exception_context'[R]
                 * 'public-sector' in 'employment_sectors'
Specified:    'disclosure-channel-defined'                            → 'disclosure_channel_context'            + 'disclosure_channel_scope'[R]         + 'disclosure_format'

// ── Statute of Limitations & Thresholds Tab ──────────────────────────────────────────────────────────
Specified:    'statute-of-repose'                                     → 'statute_of_repose_context'[R]          + 'sop_value' + 'is_sop_tolling_available'
Specified:    'statutory-tolling'                                     → 'statutory_tolling_context'[R]
Available:    'equitable-tolling'                                     → 'equitable_tolling_context'[R]
Applies:      'cba-grievance-preemption'                              → 'cba_preemption_context'[R]
Available:    'amended-claim'                                         → 'amended_claim_context'[R]
Required:     'exhaustion-required'                                   → 'exhaustion_required_context'           + 'exhaustion_required_scope'[R]
Required:     'pre-filing-notice-required'[P]                         → 'filing_notice_required_context'        + 'filing_notice_required_targets'[R]   + 'filing_notice_required_value'
                 * 'pre-filing-notice-process' in 'process_types'
Specified:    'employer-threshold-specified'                          → 'employer_threshold_context'            + 'employer_threshold_compare'[R]       + 'employer_threshold_value'            + 'employer_threshold_model'[R]
Specified:    'cure-period-specified'                                 → 'cure_period_context'[R]                + 'cure_period_value'

// ── Statute of Limitations & Thresholds Tab (Common Law Records Only) ────────────────────────────────
Applies:      'statutory-preclusion'[E]                               → 'statutory_preclusion_context'[R]

// ── Retaliation Tab ──────────────────────────────────────────────────────────────────────────────────
Required:     'evidence-preservation'                                 → 'evidence_preservation_context'         + 'preservation_requirement_scopes'[R]  + 'preservation_deadline_value'
Specified:    'constructive-discharge-standard'[P]                    → 'constructive_discharge_context'        + 'constructive_discharge_standard'[R]
                 * 'constructive-discharge' in 'adverse_actions'
Recognized:   'cats-paw-liability'                                    → 'cats_paw_liability_context'[R]         + 'is_cats_paw_liability_extended'[+]
Prohibited:   'third-party-retaliation'[P]                            → 'third_party_retaliation_context'[R]
                 * any retaliation-slug in 'adverse_actions'
Specified:    'criminal-sanctions'[P]                                 → 'criminal_sanctions_context'            + 'criminal_sanctions'[R]
                 * 'criminal-referral' in 'process_types'

// ── Retaliation Tab (Substantive Records Only) ───────────────────────────────────────────────────────
Recognized:   'remedy-election-required'                              → 'election_of_remedies_context'          + 'election_of_remedies_rules'[R]

// ── Processes & Remedies Tab ─────────────────────────────────────────────────────────────────────────
Specified:    'process-pathway'                                       → 'process_pathway_context'               + 'process_pathway_scope'[R]            + 'process_pathway_limit'
Available:    'private-right-of-action'[P+]                           → 'private_roa_context'[R]
                 * 'civil-lawsuit' in 'process_types'
Available:    'jury-trial'[P]                                         → 'jury_trial_context'                    + 'jury_trial_scope'[R]
                 * 'private-right-of-action'
Specified:    'fee-shifting-standard'[P]                              → 'fee_shifting_standard_context'         + 'fee_shifting_standard'[R]            + 'fee_shifting_scope'[R]               + 'fee_shifting_phases'[+][R]           + 'has_fee_shifting_phases'
                 * 'attorney-fees' OR 'attorney-fees-admin' in 'remedies'
Available:    'equitable-interest-award'[P]                           → 'interest_provision_context'            + 'interest_provision_scope'[R]
                 * 'interest-on-backpay' in 'remedies'
Required:     'mitigation-required'                                   → 'mitigation_required_context'           + 'mitigation_required_sources'[R]
Available:    'mitigation-exception'[P]                               → 'mitigation_exception_context'[R]
                 * 'mitigation-required'
Available:    'preliminary-reinstatement'[P]                          → 'preliminary_reinstatement_context'     + 'preliminary_reinstatement_rule'[R]           + 'preliminary_reinstatement_scope'[R]
                 * 'reinstatement' OR 'interim-reinstatement' in 'remedies'

// ── Processes & Remedies Tab (Substantive Records Only) ──────────────────────────────────────────────
Specified:    'civil-review-standard'                                 → 'review_standard_context'               + 'review_standard'[R]

// ── Burden of Proof Tab ──────────────────────────────────────────────────────────────────────────────
Specified:    'burden-shifting-framework'                             → 'burden_shifting_context'               + 'burden_shifting_frameworks'[R]
Specified:    'same-decision-defense-standard'[P]                     → 'same_decision_context'                 + 'same_decision_standard'[R]
                 * 'same-decision-defense' in 'employer_defenses'
Applies:      'causation-dual-standard'[P]                            → 'causation_dual_standard_context'[R]
                 * 'causation_standard' is non-empty
Required:     'employer-knowledge-required'                           → 'employer_knowledge_context'            + 'employer_knowledge_scopes'[R]
Recognized:   'temporal-presumption-recognized'                       → 'temporal_presumption_context'          + 'presumption_window_value'            + 'presumption_effect'[R]
Sufficient:   'temporal-proximity-sufficient'                         → 'temporal_proximity_context'[R]         + 'temporal_proximity_value'

// ── Burden of Proof Tab (Common Law Records Only) ────────────────────────────────────────────────────
Applies:      'statutory-nexus-controls'                              → 'statutory_nexus_context'[R]

// ── Reward Tab ───────────────────────────────────────────────────────────────────────────────────────
Available:    'reward-available'                                      → 'reward_context'                        + 'reward_discretion_scope'[R]
Available:    'qui-tam-action'[P+]                                    → 'qui_tam_share_context'[R]              + 'qui_tam_government_share'            + 'qui_tam_relator_share'               + 'qui_tam_reduction_context'           + 'has_first_to_file_bar'               + 'has_public_disclosure_bar'
                 * 'qui-tam-process' in 'process_types'
                 * 'bounty-qui-tam-award' in 'remedies'

// ── Waiver & Scope Tab ───────────────────────────────────────────────────────────────────────────────
Void:         'all-plaintiff-waivers-void'[E+]                        → 'all_waivers_blocked_context'[R]
                 * 'civil-action-waiver'
                 * 'contractual-waiver'
                 * 'collateral-claims-waiver'
                 * 'class-action-waiver'
Enforceable:  'civil-action-waiver'[E-]                               → 'civil_action_waiver_context'           + 'civil_action_waiver_status'[R]
                 * 'all-plaintiff-waivers-void'
Enforceable:  'contractual-waiver'[E-]                                → 'contractual_waiver_context'            + 'contractual_waiver_status'[R]
                 * 'all-plaintiff-waivers-void'
Enforceable:  'collateral-claims-waiver'[E-]                          → 'collateral_claims_waiver_context'[R]
                 * 'all-plaintiff-waivers-void'
Enforceable:  'class-action-waiver'[E-]                               → 'class_action_waiver_context'[R]
                 * 'all-plaintiff-waivers-void'
                 * 'class-action-permitted'
Specified:    'sovereign-immunity-status'[E-]                         → 'sovereign_immunity_context'            + 'sovereign_immunity_status'[R]        + 'sovereign_immunity_limits'           + 'sovereign_immunity_scope'            + 'sovereign_immunity_waiver_class'[+][R]
                 * 'blanket-sovereign-immunity-waived'
Specified:    'proper-defendants-specified'                           → 'proper_defendants_context'             + 'proper_defendant_rules'[R]
Limited:      'nda-limitations'                                       → 'nda_limits_context'[R]
Present:      'anti-gag-provision-present'                            → 'anti_gag_provision_context'[R]
Barred:       'no-retaliatory-evidence'[E]                            → 'no_retaliatory_evidence_context'[R]
Available:    'stay-of-disciplinary-action'                           → 'stay_of_discipline_context'[R]
Available:    'anti-slapp-protection'                                 → 'anti_slapp_protection_context'[R]      + 'anti_slapp_protection_scopes'[R]
Available:    'discovery-protection'[P]                               → 'discovery_protection_context'[R]       + 'discovery_protection_scopes'[R]
                 * 'retaliatory-discovery' in 'adverse_actions'
Limited:      'confidential-settlement-restriction'                   → 'settlement_restriction_context'        + 'settlement_restriction_scope'[R]
Available:    'individual-liability'                                  → 'individual_liability_context'          + 'individual_liability_scopes'[R]
Recognized:   'successor-liability'                                   → 'successor_liability_context'[R]
Applies:      'extraterritorial-coverage'                             → 'extraterritorial_context'[R]

// ── Without Context (no tab) ─────────────────────────────────────────────────────────────────────────
Present:      'catch-all-protection'                                  — (no companion needed)
Sufficient:   'internal-only-disclosure-sufficient'[P]                — (no companion needed)
                 * 'internal-disclosure' in 'process_types'
                 * any child-slug of 'internal' in 'disclosure_targets'
Available:    'trade-secret-immunity-available'                       — (no companion needed)
Recognized:   'continuing-violation-doctrine'                         — (no companion needed)
Recognized:   'prospective-whistleblower-protection'                  — (no companion needed)
Waived:       'blanket-sovereign-immunity-waived'[E+]                 — (no companion needed)
                 * 'sovereign-immunity-status'
Permitted:    'class-action-permitted'[E+]                            — (no companion needed)
                 * 'class-action-waiver'
Recognized:   'official-duties-carveout'                              — (no companion needed)

```

---

## Cross-Field Hook Tables

The slug-to-companion map declares trigger-to-cluster relationships. The tables below declare cross-field rules
that hooks enforce in addition to the slug-map relationships. Hook patterns and helper definitions live in
`ws-acf-hook-guidance-v1.0.md`.

### Symbol Legend

For Same-Field validation:
- `[I]` — multi-value field where some value combinations are invalid.
  - `[X]` — mutually exclusive values within the same field.
  - `[U]` — umbrella `-only` value excludes granular and contradictory values in the same field.
  - `[N]` — *reserved for* negation value excluding all affirmative values (none currently active).

For Cross-Field validation:
- `[D]` — *reserved for* values that **might** duplicate a concept represented in another field.
- `[R]` — value requires a value in another field.
- `[E]` — value excludes a value in another field.

For Cross-Tab Cross-Field validation:
- `[C]` — value imposes cross-tab cross-field conditions.

### Invalid Multi-Value Combos

`[I]` — Multi-value fields where values create invalid combinations. `[X]` denotes mutually exclusive values;
`[U]` denotes umbrella `-only` value; `[N]` denotes negation value (currently none).

- `burden_shifting_frameworks`       — `[X]` `mixed-motive`, `but-for`
- `protected_classes`                — `[U]` `all-employees-only`
- `employment_sectors`               — `[U]` `all-sectors-only`
- `individual_liability_scopes`      — `[U]` `any-individual-only`
- `anti_slapp_protection_scopes`     — `[U]` `full-procedural-only`

Reviewed multi-value fields with no currently-defined same-field conflict are intentionally omitted.

### Cross-Field Required Values

`[R]` — Field values that require a value in another field.

- (Processes & Remedies Tab)
- `remedy_caps.remedy_cap`
    * `punitive`     → `remedies` — `punitive-damages`
    * `compensatory` → `remedies` — `compensatory-damages`

### Cross-Tab Required Values

`[R][C]` — Field values that require a value in another field on a different tab.

- (Classifications Tab) → (Processes & Remedies Tab)
- `protected_classes`
    * `qui-tam-relator` → `process_types` — `qui-tam-process`
- (Processes & Remedies Tab) → (Classifications Tab)
- `process_types`
    * `qui-tam-process` → `legal_recognitions` — `qui-tam-action`
    * `civil-lawsuit` → `legal_recognitions` — `private-right-of-action`
- `remedies`
    * `bounty-qui-tam-award` → `legal_recognitions` — `qui-tam-action`
- (Statute of Limitations & Thresholds Tab) → (Retaliation Tab)
- `sol_triggers`
    * `constructive-discharge-accrual` → `adverse_actions` — `constructive-discharge`

### Cross-Field Exclusions

`[E]` — Field values that exclude a value in another field.

- (Retaliation Tab)
- `adverse_action_scope`
    * `termination-only` → excludes `demotion` AND `suspension` AND `disciplinary-action` AND `transfer` AND
      `schedule-change` AND `benefit-denial` AND `benefit-clawback` AND `pay-reduction` AND `harassment` AND
      `hostile-work-environment` AND `workplace-isolation` AND `post-employment-retaliation` AND `blacklisting`
      AND `negative-reference` AND `security-clearance-action` AND `contract-non-renewal` AND
      `professional-license-action` AND `privilege-revocation` AND `immigration-threat` AND
      `anticipatory-retaliation` AND `threatened-retaliation` AND `retaliatory-investigation` AND
      `retaliatory-litigation` AND `retaliatory-discovery` in `adverse_actions`
    * `termination-only` → allows `termination` AND `constructive-discharge` AND `has-details`
      in `adverse_actions`
- (Waiver & Scope Tab)
- `sovereign_immunity_status`
    * `not-waived` → excludes `sovereign_immunity_waiver_class`

### Cross-Tab Excluded Values

`[E][C]` — Field values that exclude a value in another field on a different tab.

- (Processes & Remedies Tab) → (Classifications Tab)
- `process_pathway_scope`
    * `direct-court` → excludes `exhaustion-required` in `legal_recognitions`

---

## Special Cases

### Cluster-Level Exclusions

`legal_recognitions` slugs that exclude entire clusters or substantively shape the validity of other field
groups:

- `statutory-preclusion` → excludes ordinary process and remedy pathway fields when the claim is fully precluded.
- `no-retaliatory-evidence` → excludes evidence-use fields that treat retaliatory evidence as available.

### Class Slug Conflict

`protected_classes` and `excluded_classes` cannot share the same class slug. Block save with hook; require
editor resolution since the correct side is legal-context-dependent.

### Precedent Scope Consistency

(Classifications Tab — Precedent Records Only)

- `scope` enforces precedent record consistency:
    * `favorable` conflicts with `suppressed_taxonomies` non-empty.
    * `adverse` conflicts with `extended_taxonomies` non-empty.
    * `neutral` conflicts with both `extended_taxonomies` and `suppressed_taxonomies` when non-empty.

### Material Adverse Override

(Retaliation Tab)

- `adverse_action_scope`
    * `material-adverse` → excludes `anticipatory-retaliation` AND `threatened-retaliation` in `adverse_actions`.
    * **Override:** the matching `*_gloss` field is non-empty with specific context explaining how the threatened
      or anticipatory act qualifies as `material-adverse`.

When the override applies:
- `material-adverse` plus `anticipatory_retaliation_gloss` (detailing how the anticipatory act qualifies) →
  allows `anticipatory-retaliation`.
- `material-adverse` plus `threatened_retaliation_gloss` (detailing how the threatened act qualifies) → allows
  `threatened-retaliation`.

The hook **is only capable of confirming** the gloss field is non-empty. Editors are responsible for verifying that
the gloss
content actually justifies the override.

### Auto-Set Boolean

(Processes & Remedies Tab)

- `has_fee_shifting_phases`: auto-set true when `fee_shifting_standard` is `none-american-rule`. Flag for
  editorial review with the note that `none-american-rule` as the `fee_shifting_standard` requires at least one
  `fee_shifting_phases.phase` as an exception, or the recognition slug must be removed.

---

## Legal-Record-Specific Hook Examples

Generic hook examples (merge arrays, derive value, umbrella validation, cross-field requirement, etc.) live in
`ws-acf-hook-guidance-v1.0.md`. The examples below illustrate hook patterns specific to legal-record fields.

### Cats-Paw Extension Guard

`is_cats_paw_liability_extended` is valid only when both the `cats-paw-liability` slug is present in
`legal_recognitions` AND any descendant of `associates-of-whistleblower` is present in `protected_classes`. Apply
the dual-condition guard pattern from `ws-acf-hook-guidance-v1.0.md`:

```php
function ws_validate_cats_paw_extension_guard(int $post_id): void {
    if (!ws_hooked_get($post_id, 'is_cats_paw_liability_extended')) {
        return;
    }

    $sibling_active    = ws_hooked_has_slug($post_id, 'legal_recognitions', 'cats-paw-liability');
    $associate_present = ws_hooked_has_child_slug($post_id, 'protected_classes',
        'associates-of-whistleblower');

    if (!$sibling_active || !$associate_present) {
        ws_hooked_error('is_cats_paw_liability_extended',
            'Extended cat\'s paw liability requires cats-paw-liability and an '
            . 'associates-of-whistleblower protected class.');
    }
}
```

### Material Adverse Override Validation

The `adverse_action_scope` value `material-adverse` excludes anticipatory and threatened retaliation by default;
the exclusion **is explicitly authorized to** be overridden when a `*_gloss` field carries justification. Two
glosses participate:
`anticipatory_retaliation_gloss` and `threatened_retaliation_gloss`. The hook checks that the relevant gloss is
non-empty when the corresponding adverse-action slug is present alongside `material-adverse`.

```php
function ws_validate_material_adverse_override(int $post_id): void {
    $scope = ws_hooked_get($post_id, 'adverse_action_scope');
    if ($scope !== 'material-adverse') {
        return;
    }

    $checks = [
        'anticipatory-retaliation' => 'anticipatory_retaliation_gloss',
        'threatened-retaliation'   => 'threatened_retaliation_gloss',
    ];

    foreach ($checks as $action_slug => $gloss_field) {
        if (!ws_hooked_has_slug($post_id, 'adverse_actions', $action_slug)) {
            continue;
        }
        if (empty(ws_hooked_get($post_id, $gloss_field))) {
            ws_hooked_error($gloss_field,
                "$action_slug under material-adverse scope requires $gloss_field "
                . "to explain why it qualifies as material-adverse.");
        }
    }
}
```

### Fee-Shifting Phases Auto-Set

When `fee_shifting_standard` is `none-american-rule`, the boolean `has_fee_shifting_phases` is auto-set true and
the editor is flagged: `fee_shifting_phases` must contain at least one phase as an exception, or the
`fee-shifting-standard` recognition slug must be removed.

```php
function ws_apply_fee_shifting_phases_autoset(int $post_id): void {
    $standard = ws_hooked_get($post_id, 'fee_shifting_standard');
    if ($standard !== 'none-american-rule') {
        return;
    }

    ws_hooked_set($post_id, 'has_fee_shifting_phases', true);

    $phases = (array) ws_hooked_get($post_id, 'fee_shifting_phases');
    if (empty($phases)) {
        ws_hooked_error('fee_shifting_phases',
            'none-american-rule requires at least one fee_shifting_phases.phase as an exception, '
            . 'or remove the fee-shifting-standard recognition slug.');
    }
}
```

---

## Backlog and Recheck Later

Items deferred from the current review pass:

- `federal_state_interaction` field group still looks like a rule cluster trying to happen. Currently a base
  select, a targeted savings-clause gloss, a general interaction gloss, and details. Revisit when more data
  accumulates.
- `superseded-by-statute` recognition slug now has `superseded_by_statute_id`. Future hooks need to decide
  whether the ID field is required or merely recommended when the class is selected.
- Inactive or non-existing fields **must never** trigger stale-value hook logic. Where a taxonomy slug suppresses
  an
  entire cluster, hook validation **must unconditionally** flag the controlling taxonomy conflict first and avoid
pretending
  hidden downstream sisters are independently meaningful.

---

— drafted for Dejunai by Claude (Anthropic), session of 2026-05-06
