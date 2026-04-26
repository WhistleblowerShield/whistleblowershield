The attached files represent a **highly mature, editorially thoughtful evolution** of the legal-record ACF canonical field set (version reflecting 3.16.0 taxonomy updates). The structure achieves strong unification across the four CPTs (`jx-statute`, `jx-common-law`, `jx-citation`, `jx-construction`), with logical tab ordering, conditional companions (`*_context`, `*_details`), sentinels (`has-details`, `has-limits`, `has-phases`, `see-context`), derived/hidden fields, and reusable hooks. 

Key improvements over prior drafts include:
- Expanded **Classifications Tab** with nuanced triggers like `manager_rule_context`, `public_concern_context`, `bad_faith_context`, `is_evidence_collection_protected`, and `has_malicious_reporting_sanctions`.
- Dedicated **Retaliation Tab** and **Waiver & Scope Tab** for better workflow separation.
- Enhanced **Burden Of Proof Tab** with `causation_application` (liability vs. damages phase distinctions), `causation_standard_context` (explicitly for hybrids/circuit splits/backstops), and `employer_awareness_requirement`.
- Qui tam depth in **Reward Tab** (`qui_tam_government_share`, `qui_tam_relator_share`, `qui_tam_reduction_grounds`).
- New **legal_recognition** terms (e.g., `trade-secret-immunity`, `anti-gag-provision`, `anti-slapp-protection`, `no-retaliatory-evidence`, `stay-of-disciplinary-action`, `successor-liability`, `extraterritorial-coverage`, `manager-rule-exclusion`, `public-concern-required`, `bad-faith-exclusion`, `internal-only-sufficient`, `catch-all-protection`).
- Taxonomy updates (e.g., `ws_adverse_action` with `retaliatory-discovery`; `ws_protected_action` hierarchy for opposition/participation clauses; causation refinements like `contributing-factor-but-for-backstop`).

Hooks for contradictions (e.g., `civil_action_waiver_scope` `anti` invalidating `contractual-waiver`; `jury-trial` requiring `private-right-of-action`) and cross-tab notices (mixed-motive) are well-specified. The **Slug-to-Companion Map** and **Rename Normalization** are clean and comprehensive.

### Escaped or Under-Captured Legal Nuances in Whistleblower Protections
After cross-referencing the files against core U.S. whistleblower frameworks (SOX §806, Dodd-Frank, FCA/qui tam, WPA/WPEA, OSHA-enforced laws, state analogs, DTSA §1833(b), common-law public policy exceptions, and recent developments as of 2026), the model captures **the vast majority** of recurring nuances through flexible mechanisms (`legal_recognitions` + companions, `protected_action_standard`/`context`/`source`, `adverse_actions` + scope/context, `causation_standards` + `context`/`application`, `proper_defendants`, `remedies`, `preemption_details`, etc.). 

**No critical structural gaps** exist that would break parity or editorial workflow. Edge cases are best handled in record-specific freetext companions or precedent `extend_taxonomy`/`suppress_taxonomy`/`negative_treatment` rather than proliferating rigid fields. However, a few **subtle or emerging nuances** are only partially surfaced or rely heavily on overflow fields—flagged here for potential taxonomy/hook refinement or editorial guidance:

1. **Duty Speech / Job Duties Exception ("Manager Rule") and Routine vs. Extraordinary Disclosures**:
   - **Coverage**: Excellent — `manager-rule-exclusion` in `legal_recognitions` → `manager_rule_context`; `protected_action_standard` (reasonable_belief/good_faith); `public_concern_context` (for common-law or certain statutes); `protected_actions` hierarchy (opposition/participation clauses, internal-objection, etc.); `is_evidence_collection_protected`.
   - **Nuance**: Garcetti v. Ceballos (and analogs) often narrows First Amendment/common-law protections for public employees when disclosures fall within official job duties ("duty speech"). Some statutes (e.g., WPA for certain federal roles) impose higher burdens or exclusions for routine compliance/oversight work. Private-sector analogs appear in SOX/Dodd-Frank interpretations. Recent bills (e.g., 2025-2026 proposals) seek to close gaps for investigators in normal duties.
   - **Status**: Well-handled via existing fields. `manager_rule_context` can document whether the exclusion applies narrowly (e.g., only to policy-making roles) or broadly. No change needed, but editors should use `protected_action_context` + `doctrine_basis` (common-law) to note circuit/state variations.

2. **Trade Secret Immunity (DTSA §1833(b) and State Analogs)**:
   - **Coverage**: Explicitly present as `trade-secret-immunity` in `legal_recognitions` (no companion needed; "Recognized" category). This is a strong, targeted addition.
   - **Nuance**: Immunity shields disclosures made in confidence to government officials/attorneys (or under seal in filings) solely to report/investigate suspected law violations. Employers must provide notice in confidentiality agreements (non-compliance limits DTSA remedies). Applies to both federal and state trade secret claims; interplay with NDAs/anti-gag rules is common. Some states have parallel or broader immunities.
   - **Status**: Captured cleanly. Pair with `nda_limits_context` or `anti_gag_context` for full picture. If patterns of "notice requirement" or "sealed filing" nuances emerge frequently, a lightweight sister field could be added, but the current setup suffices.

3. **Anti-SLAPP / Retaliatory Litigation Protections**:
   - **Coverage**: `anti-slapp-protection` in `legal_recognitions` → `anti_slapp_protection_context`; `ws_adverse_action` includes `retaliatory-litigation` (SLAPP) and `retaliatory-discovery`.
   - **Nuance**: State anti-SLAPP statutes (strong in CA, NY, etc.; patchwork nationally) provide early dismissal and fee-shifting for suits chilling public participation, including whistleblower disclosures. No comprehensive federal anti-SLAPP exists (proposals pending). Qui tam relators sometimes invoke these defensively. Overlap with `retaliatory-litigation` as adverse action.
   - **Status**: Adequately covered. `anti_slapp_protection_context` can detail applicability (e.g., to specific claims, fee recovery, or interaction with federal preemption). Consider linking to `fee_shifting_rules` or `remedies` where SLAPP-specific fees apply.

4. **Successor / Joint / Contractor Liability**:
   - **Coverage**: `successor-liability` in `legal_recognitions` → `successor_liability_context`; `proper_defendants` multi-select includes `successor-employer`, `joint-employer`, `contractors-included`, `scope-of-employment-required`.
   - **Nuance**: FCA and other statutes often apply "substantial continuity" or notice-based successor liability (easier for whistleblowers than strict common-law rules). Joint-employer doctrines (e.g., under FLSA analogs or specific whistleblower laws) and contractor liability vary. Post-merger/acquisition retaliation claims can survive if the successor had notice.
   - **Status**: Strong coverage. `proper_defendant_details` and context fields handle variations well.

5. **Extraterritorial Application**:
   - **Coverage**: `extraterritorial-coverage` in `legal_recognitions` → `extraterritorial_context`.
   - **Nuance**: Most U.S. whistleblower provisions (SOX §806, FCA, etc.) have limited or no extraterritorial reach (presumption against it; confirmed in D.C. Circuit for SOX). Exceptions or domestic conduct hooks exist in some cases (e.g., U.S.-based decisionmaking affecting foreign employees). Dodd-Frank/SEC rules sometimes differ. Emerging global compliance issues (multinationals) add complexity.
   - **Status**: Explicitly addressed. Context field can note "domestic application via U.S. conduct" distinctions or circuit splits.

6. **Other Minor/Overflow Nuances**:
   - **Bad Faith / Knowingly False Exclusions and Malicious Reporting Sanctions**: Covered via `bad_faith_context` and `has_malicious_reporting_sanctions` + `malicious_reporting_details`. Handles "reasonable belief" carve-outs and sanctions for false reports.
   - **Public Concern Requirement**: Common in some common-law exceptions and older statutes; captured via `public_concern_context`.
   - **Employer Awareness / Knowledge Element**: `employer_awareness_requirement` + `details` addresses actual/constructive/inferred knowledge thresholds (key in some retaliation proofs).
   - **Evidence Collection / No-Retaliatory-Evidence Rules**: `is_evidence_collection_protected` and `no_retaliatory_evidence_context` cover protections for gathering proof.
   - **Cure Periods, Administrative Backstops, and Hybrid Standards**: `has_cure_period`, `causation_application` (phased application), and `causation_standard_context` (explicitly calls out hybrids, circuit splits, backstops) are thoughtful additions.
   - **Emerging/2025-2026 Developments** (e.g., FinCEN AML whistleblower program with 10-30% awards and protections; bills extending federal employee coverage; DOJ recoveries): These fit into existing `process_types`, `reward_discretion_standard`, `federal_agencies`, `disclosure_types`, etc. No new fields required for ingest—use `general_description`/`plain_english_wysiwyg` and specific records.

### Overall Assessment for the Ingest Rewrite
- **Captured**: 95%+ of core and recurring whistleblower nuances (protected activity definitions, causation/burden frameworks, adverse actions/retaliation forms, remedies/fee shifting, procedural hurdles, waivers/immunity, common-law doctrines, precedent effects).
- **Escaped/Thin Coverage**: None that are systemic. The heaviest reliance on freetext companions (`*_context`/`*_details`) occurs for jurisdiction-specific or circuit-split details (intentional and appropriate for a reference platform). `ws_legal_recognition` expansions in 3.16.0 directly address prior feedback (e.g., trade secrets, anti-SLAPP/gag, successor, extraterritorial, manager/public concern/bad faith rules).
- **Recommendations** (minimal):
  - **Taxonomy/Hooks**: Ensure `ws_legal_recognition` seeding includes clear editor notes on hook behaviors (e.g., `contractual-waiver` invalidation, `jury-trial` dependency). Consider adding a sentinel term like `has-duty-speech-nuance` if volume warrants, but current `manager_rule_context` suffices.
  - **Editorial Guidance**: In plain-English or ingest matrix, flag that `*_context` fields should explicitly address "reasonable belief" thresholds, DTSA notice requirements, SLAPP fee recovery, and extraterritorial limits where relevant.
  - **Precedent Handling**: Leverage `scope`/`extend_taxonomy`/`suppress_taxonomy`/`negative_treatment` aggressively for judicial refinements of these nuances.
  - **No Major Additions Needed**: The design prioritizes flexibility without bloat—ideal for the 2-pipeline ingest rewrite. Derived fields and hooks (e.g., agency filtering, merged IDs) will maintain data integrity.

This is a solid, production-capable canonical draft. It balances comprehensiveness with usability for editors and plain-language rendering for users (Maya/James personas). Remaining nuances are inherently fact-specific and well-suited to the context/detail overflow mechanisms provided.

---

