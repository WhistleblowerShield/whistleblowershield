**Here is a structured gap analysis** of the two legal files (`legal-record-acf-fields.md.txt` and `register-taxonomies.php.txt`) for **U.S. whistleblower legal protections**. I prioritized gaps in core legal nuance that are **not** (or only thinly/implicitly) covered by the existing ACF field proposal or the seeded taxonomy terms/tables.

The analysis focuses on substantive whistleblower retaliation protections, procedural requirements, remedies, burden/causation issues, common-law public policy exceptions, and precedent impacts—drawing from typical structures in federal statutes (SOX, Dodd-Frank, FCA, WPA, OSHA, etc.), state analogs, and common-law wrongful discharge doctrines. I omitted overlaps with existing fields/taxonomies (e.g., `protected_action`, `legal_recognitions` companions, `causation_standard`, `adverse_action`, `remedy`, `process_type`, `disclosure_target`, etc.) and honored the naming/conditional/sentinel conventions in the ACF draft.

Output is in **four markdown tables** as requested.

### Table 1: Proposed New Fields (by priority)

| Priority | Field Name                          | Record Type/Group          | Tab                          | Field Type                  | Description of Datapoint |
|----------|-------------------------------------|----------------------------|------------------------------|-----------------------------|--------------------------|
| 1        | `ws_burden_of_proof_standard`      | substantive (statute + common_law) | Burden of Proof             | taxonomy (multi-select)    | Evidentiary standard the whistleblower must satisfy to prove the claim or shift burden (e.g., preponderance, clear-and-convincing, contributing-factor-as-threshold). Distinct from causation nexus. |
| 1        | `burden_of_proof_context`          | substantive                | Burden of Proof             | freetext (companion)       | Details on how the burden operates, initial prima facie elements, shifting mechanics, or rebuttal requirements not captured by taxonomy. |
| 2        | `prima_facie_elements`             | substantive + precedent (citation/construction) | Classifications or Retaliation | freetext or repeater      | Specific elements required to establish a prima facie retaliation case (temporal proximity, knowledge, etc.). Useful for both statutes and case law impact. |
| 2        | `has_emotional_distress_damages`   | substantive                | Process & Remedies          | boolean (`has_*`)          | Whether non-economic/emotional distress or pain-and-suffering damages are available/recoverable. Triggers `emotional_distress_details`. |
| 3        | `sovereign_immunity_context`       | substantive                | Waiver & Scope              | freetext                   | Details on sovereign immunity waivers, exceptions, or limitations for claims against government entities (federal/state). Companion to potential new `legal_recognition`. |
| 3        | `has_discovery_protection`         | substantive + precedent    | Retaliation                 | boolean                    | Whether the law protects against retaliatory discovery, subpoenas, or litigation harassment. Triggers details. |
| 3        | `public_policy_test_elements`      | common-law                 | Classifications             | freetext or repeater       | Specific multi-part test elements for public policy wrongful discharge (common in state common law). |
| 4        | `has_interest_on_backpay`          | substantive                | Process & Remedies          | boolean                    | Availability of prejudgment/post-judgment interest on back pay or other monetary relief. |
| 4        | `arbitration_enforceability_context` | substantive             | Waiver & Scope              | freetext                   | Nuanced treatment of mandatory arbitration clauses in whistleblower claims (beyond general contractual waiver). |

These fields follow the existing conventions (`has_*` + `_details`/`_context`, taxonomy where choices are enumerable, sister/conditional logic, placement in logical tabs). They address recurring gaps in burden mechanics, non-economic harms, government immunities, and common-law specifics that the current spec handles only implicitly or via broad freetext.

### Table 2: Proposed Changes to Existing Fields (by priority)

| Priority | Field Name (existing)              | Record Type/Group | Tab                  | Change to Field Type / Structure | How the Change Affects the Datapoint |
|----------|------------------------------------|-------------------|----------------------|----------------------------------|-------------------------------------|
| 1        | `burden_shifting_framework` (mentioned in cross-tab note) | substantive | Burden of Proof     | Expand options or convert to taxonomy + `has-details`; add explicit link to `ws_causation_standard` and new `ws_burden_of_proof_standard` | Currently tied only to mixed-motive remedy context. Broadening captures single-motive vs. mixed-motive, burden-shifting triggers, and interaction with evidentiary standards—preventing reliance on ad-hoc freetext. |
| 2        | `ws_employee_standard` (noted as split in draft) | substantive | Burden of Proof     | Reinstate or explicitly add as companion taxonomy (evidentiary) if fully removed | The split to `ws_causation_standard` leaves the distinct evidentiary BOP (quantity/quality of proof) uncovered; restoring a parallel taxonomy or field restores parity for statutes like SOX §806 vs. others. |
| 2        | `protected_action_context` + sisters | substantive | Classifications     | Add optional sister field `protected_activity_standard` (single-select: reasonable-belief-objective, good-faith, actual-violation, etc.) | Strengthens granularity for "reasonable belief" nuances already partially present; reduces pressure on freetext for statute-specific variations. |
| 3        | `contractual_waiver_context`       | substantive | Waiver & Scope      | Add explicit sentinel support or sister for arbitration-specific treatment | Current field is broad; explicit arbitration nuance prevents conflation with general NDAs/waivers. |
| 3        | `remedies` taxonomy usage          | substantive | Process & Remedies  | Add new terms (see Table 4) or expand `has-limits`/`has-details` triggers | Better captures non-economic and ancillary monetary relief without proliferating top-level booleans. |

These changes are conservative—mostly expansions, sentinels, or sisters—to maintain backward compatibility and editorial workflow while closing nuance gaps.

### Table 3: Proposed New Taxonomy Tables

| Taxonomy Slug                  | Concept the New Table Would Cover | High-Level Terms (categories only; not exhaustive definitions) |
|--------------------------------|-----------------------------------|----------------------------------------------------------------|
| `ws_burden_of_proof`          | Evidentiary standards (quantity/quality of proof required from whistleblower or to shift burden); distinct from `ws_causation_standard` (nexus). | preponderance-of-evidence, clear-and-convincing, contributing-factor-threshold, prima-facie-only, has-details |
| `ws_evidence_type`            | Recognized forms of evidence sufficient to support retaliation inference or protected activity (frequently litigated in precedent). | temporal-proximity, comparator-evidence, direct-evidence, circumstantial, retaliatory-motive-inference, has-details |
| `ws_immunity_limit`           | Treatment of immunities (sovereign, qualified, absolute) and waivers in whistleblower retaliation claims. | sovereign-immunity-waived, qualified-immunity-limited, absolute-immunity-barred, has-details |

These are flat (or lightly hierarchical) like most existing tables, attachable to all four legal record types (with `record: ['legal']`), and would drive filters/rendering similar to `ws_legal_recognition` or `ws_causation_standard`. They fill structural holes in burden/evidentiary and immunity areas that are central to whistleblower case viability but currently diffuse across freetext or unaddressed.

### Table 4: Proposed New Terms for Existing Taxonomy Tables

| Term Slug (kebab-case)                  | Taxonomy Table                  | Gap It Would Cover in Context of Existing Table |
|-----------------------------------------|---------------------------------|-------------------------------------------------|
| `emotional-distress-damages`           | `ws_remedy`                    | Captures availability of non-economic damages for mental/emotional harm from retaliation (common in SOX/Dodd-Frank but not explicitly enumerated; complements `compensatory-damages`). |
| `interest-on-backpay`                  | `ws_remedy`                    | Ancillary monetary relief (pre/post-judgment interest); frequently available but missing from current remedy list. |
| `cooperation-with-investigation`       | `ws_protected_action`          | Protects individuals who assist/cooperate with ongoing government investigations (distinct from initial disclosure or opposition/participation clauses). |
| `refusal-to-violate-law`               | `ws_protected_action`          | Explicit protection for refusing to participate in illegal activity (common in state common law and some federal schemes). |
| `office-of-inspector-general`          | `ws_disclosure_target`         | Specific internal/external federal target for protected disclosures (e.g., agency IG hotlines); refines hierarchical structure without over-broadening "government-accountability". |
| `temporal-proximity-sufficient`        | `ws_legal_recognition`         | Recognizes that close timing between protected activity and adverse action can support causation/retaliation inference (key in precedent and many statutes). |
| `prima-facie-case-specified`           | `ws_legal_recognition`         | Statute or doctrine explicitly enumerates elements of a prima facie retaliation case (triggers companion for details). |
| `sovereign-immunity-waiver`            | `ws_legal_recognition`         | Explicit waiver or limitation of sovereign immunity for whistleblower claims against government (critical for federal/state employee protections). |
| `discovery-protection`                 | `ws_legal_recognition`         | Bars retaliatory discovery or litigation tactics against whistleblowers (extends existing `no-retaliatory-evidence` and `anti-slapp`). |
| `qualified-immunity-limit`             | `ws_legal_recognition`         | Limits on qualified immunity for individual supervisors/officials in §1983 or analogous claims. |

**Additional notes on gaps and rationale**:
- **Burden/evidentiary mechanics** are the highest-priority gap. The current split to `ws_causation_standard` and partial `mixed-motive` handling leaves evidentiary BOP and prima facie details underdeveloped relative to real litigation (e.g., McDonnell Douglas framework adaptations, contributing-factor vs. but-for thresholds).
- **Non-economic remedies and immunities** are thinly covered; whistleblower plaintiffs frequently seek emotional distress and must navigate government immunities.
- **Common-law specifics** (public policy tests) and precedent impacts on evidence/burden are underrepresented compared to statutory fields.
- **Protected activity** taxonomy could usefully expand for cooperation/refusal scenarios without bloating `protected_disclosure`.
- No major conflicts with existing naming/sentinel/conditional rules or the assist-org side (these are legal-record focused). New taxonomies would integrate via `ws_get_taxonomies_for_record('legal')` and seeding logic.

These proposals are targeted, minimal, and designed to enhance coverage of recurring U.S. whistleblower legal nuances while preserving the clean, filter-friendly structure of the current system. They prioritize high-impact areas for both Maya (understanding protections pre-decision) and James (navigating post-report retaliation). 

If you provide the full untruncated ACF draft, the exact "US-0-Assist-org" schema, or the matrix PHP for cross-reference, I can refine priorities or map these more granularly.