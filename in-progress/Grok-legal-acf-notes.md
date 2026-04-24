Here is a structured analysis of gaps in legal nuance for whistleblower protections across the four record types (`statute`, `common_law`, `citation`, `construction`). I focused exclusively on areas **not** addressed in the provided canonical field draft, **not** in your declared additions (`has_anonymity_protection` + details; `confidentiality_scope` taxonomy/choice + details; `has_nda_limitation` + details or expansion of `arbitration_waiver_enforceability`/`contractual_waiver_scope`; `superseded_by` → `overruled_by_id`; disclosure target rename/expansion for oversight bodies; or the planned split of causation terms from `ws_employee_standard` into a new table feeding `retaliation_connection_standard`).

Gaps were derived from core whistleblower legal elements (protected activity nuances, causation/burden mechanics, retaliation spectrum, waiver/anti-waiver rules, confidentiality/anonymity interplay, common-law public policy exceptions, precedent scope effects, and enforcement barriers) that vary meaningfully across statutes, doctrines, and case constructions but lack dedicated capture in the current proposal or taxonomies.

### 1. Proposed New Fields (by priority)

These fill structural gaps in capturing variable legal mechanics that affect usability for users (e.g., "Does this protection survive a confidentiality agreement?" or "Can I still be investigated as retaliation?").

**Priority 1 (High – core to claim viability/enforceability, affects plain-English summaries and user decision-making):**

- **Record group: Substantive (statute & common_law)**  
  **Tab: Enforcement**  
  **Field type: bool + conditional freetext (`has_anti_waiver_provision`, `anti_waiver_details`)**  
  **Description:** Captures whether the law explicitly prohibits waiver of protections via contract, policy, NDA, settlement, or employment condition (beyond your planned NDA/settlement expansion). Includes anti-gag, non-disparagement, or confidentiality-waiver overrides common in best-practice standards and some federal statutes.

- **Record group: Precedent (citation & construction)**  
  **Tab: Classification**  
  **Field type: single-select (`retaliation_investigation_protected`: `yes`|`no`|`limited`|`has-details`)** + conditional `investigation_protection_details`  
  **Description:** Indicates if precedent recognizes retaliatory investigations (or criminal/civil referrals with false pretexts) as actionable adverse action. Addresses a noted disparity where most laws do not cover this, but select precedents (e.g., military or specific circuits) do.

- **Record group: Substantive (statute & common_law)**  
  **Tab: Burden Of Proof**  
  **Field type: single-select (`causation_standard_application`: `contributing_factor`|`motivating_factor`|`but_for`|`mixed`|`has-details`)** + `causation_details` (separate from your planned new table)  
  **Description:** Specifies how the statute or doctrine applies causation in the retaliation element (distinct from employee burden or employer defenses). Captures variations like "contributing factor" with clear-and-convincing rebuttal, which is plaintiff-friendly in many federal whistleblower schemes.

**Priority 2 (Medium – important for procedural and common-law parity):**

- **Record group: All (or substantive baseline)**  
  **Tab: Statute of Limitations And Thresholds**  
  **Field type: bool + conditional freetext (`has_procedural_compliance_requirement`, `procedural_compliance_details`)**  
  **Description:** Flags whether strict adherence to internal/external reporting channels, timelines, or "normal channels" exceptions is required for protection to attach (common in statutes and some common-law public policy claims; non-compliance often defeats claims).

- **Record group: Common-law**  
  **Tab: Classification**  
  **Field type: bool + `public_policy_exception_scope` (multi-select or details)**  
  **Description:** Captures the breadth of the public policy exception to at-will employment (e.g., refusal to commit illegal acts, exercising statutory rights, or reporting violations). Complements `doctrine_basis` but focuses on the exception's triggers and limits.

- **Record group: Precedent (citation & construction)**  
  **Tab: Classification**  
  **Field type: bool (`extends_protected_activity`)** + conditional `protected_activity_extension_details` (or link to taxonomy extension)  
  **Description:** Indicates if the precedent broadens "protected activity" beyond the parent statute/common-law (e.g., participation in investigations, refusal to participate in wrongdoing, or lawyer/client disclosures).

**Priority 3 (Lower – niche but useful for completeness):**

- **Record group: Substantive**  
  **Tab: Enforcement**  
  **Field type: bool (`has_criminal_referral_protection`)** + `criminal_referral_details`  
  **Description:** Covers protection against employer-initiated criminal/civil liability referrals as retaliation (beyond general adverse actions).

- **Record group: All**  
  **Tab: Relationships**  
  **Field type: array or relationship field (`related_protections`)**  
  **Description:** Links to overlapping or complementary protections (e.g., First Amendment, trade secret exceptions, or parallel remedies).

### 2. Proposed Changes to Existing Fields (by priority)

These refine or expand fields for better nuance without breaking the prefix-free, singular/plural, has_/is_ conventions.

**Priority 1:**

- **Field: `protected_activity_standard`** (all records, Classification Tab)  
  **Change:** Expand single-select options to explicitly include `reasonable_belief` variants tied to specific misconduct types (or add a companion `protected_disclosure_type` multi-select).  
  **Impact:** Currently limited to `actual_violation`|`reasonable_belief`|`good_faith`; this would better capture that "reasonable belief" must often tie to enumerated categories (violation of law, gross mismanagement, waste, abuse, danger to health/safety) and exclude pure policy disagreements, improving accuracy for plain-English explanations.

- **Field: `adverse_action_scope`** (all, Enforcement Tab)  
  **Change:** Add `investigatory` or `referral-based` to the single-select (or expand to include your new investigation field).  
  **Impact:** Broadens beyond termination/material/broad-adverse to explicitly flag evolving retaliation tactics not covered in standard adverse_action taxonomy.

**Priority 2:**

- **Field: `burden_shifting_framework`** (all, Burden Of Proof Tab)  
  **Change:** Ensure options explicitly distinguish employee prima facie (e.g., contributing factor) from employer rebuttal (clear-and-convincing "same decision" defense). Add `contributing_factor_with_rebuttal` if not derivable.  
  **Impact:** Strengthens differentiation from general `employee_standards` or `employer_defenses`; aligns with Supreme Court clarifications (e.g., Murray v. UBS on contributing factor).

- **Field: `recognition_status`** (common-law, Classification Tab)  
  **Change:** Add `limited_by_contract` or `subject_to_waiver` nuance (via details or new companion).  
  **Impact:** Better reflects common-law public policy exceptions that can be narrowed by judicial doctrines (e.g., manager rule, undivided loyalty for compliance officers).

### 3. Proposed New Taxonomy Tables

These cover concepts with meaningful variation across records that taxonomies already handle well for other elements (disclosure types, adverse actions, etc.).

- **New table: `ws_causation_standard`** (or `ws_retaliation_causation` – name flexible per your note)  
  **Concept covered:** Dedicated flat or simple hierarchy for how causation is applied in the retaliation element (distinct from employee burden standards or employer defenses). Would feed `retaliation_connection_standard` or similar. Terms could include variations like contributing-factor, motivating-factor with same-decision defense, but-for, knowledge/timing test, etc. Addresses the doctrinal confusion and statute-by-statute variation in causation that often defeats claims.

- **New table: `ws_waiver_prohibition`** (or `ws_anti_waiver_type`)  
  **Concept covered:** Specific types of contractual or policy waivers that are prohibited or limited (e.g., pre-dispute arbitration, NDAs/gag orders, settlement confidentiality, internal confidentiality agreements). Complements your planned NDA field and existing `fee_shifting_rules`/`arbitration_waiver_enforceability` by providing granular, filterable taxonomy for enforcement nuances.

- **New table: `ws_protected_activity_exception`** (flat)  
  **Concept covered:** Exceptions or limitations to protected activity (e.g., "in course of duties," "normal channels," policy disagreement carve-outs, compliance officer "manager rule" or "undivided loyalty" limits). Would pair with `protected_activity_standard` and precedent extensions.

### 4. Proposed New Terms for Existing Taxonomy Tables

These plug specific gaps using existing structures (e.g., "has-details" sentinel pattern, hierarchical where appropriate).

- **Table: `ws_adverse_action`** (all records)  
  **New terms:** `retaliatory-investigation`, `false-criminal-referral`, `threat-of-liability` (or similar).  
  **Gap covered:** Captures evolving/non-employment retaliation tactics (retaliatory probes, pretextual referrals) that most statutes/precedents do not expressly protect against, except in narrow cases. Allows "has-details" fallback while enabling taxonomy-driven filters.

- **Table: `ws_disclosure_type`** (all)  
  **New terms:** `internal-compliance-officer` (or under existing internal), `lawyer-client-disclosure`, `participation-in-investigation`.  
  **Gap covered:** Better granularity for disclosures by compliance/lawyer roles or participation (vs. pure reporting), where common-law and some precedents create exceptions or narrower protections (e.g., manager rule, ethical duties).

- **Table: `ws_protected_class`** (and duplicate `ws_excluded_class`)  
  **New terms (under special-status or new parent):** `compliance-officer`, `in-house-counsel`, `union-representative`.  
  **Gap covered:** Highlights sectors/roles with documented narrower protections or judicial limits (e.g., compliance officers facing "undivided loyalty" or manager-rule barriers in EEO/securities contexts).

- **Table: `ws_employee_standard`** (pending your split) or the new causation table  
  **New terms:** `knowledge-timing-test`, `circumstantial-evidence-causation`, `clear-and-convincing-rebuttal`.  
  **Gap covered:** Supports precise burden/causation mechanics (e.g., timing + knowledge for prima facie, or employer rebuttal standards) that vary and frequently determine outcomes.

- **Table: `ws_disclosure_target`** (all)  
  **New terms:** `internal-oversight-body` (as you mentioned; pairs with your `external-oversight-body` rename).  
  **Gap covered:** Explicitly distinguishes internal ombudsman/ethics structures (which may not trigger external protections) from external ones, improving filter accuracy without using the layperson-unfriendly "ombudsman" term.

These proposals maintain the draft's design principles (snake_case, has_/is_ booleans, _details companions, taxonomy parity, substantive baseline + precedent deltas). They prioritize high-impact user-facing nuances (e.g., "Will an NDA block this?") while keeping the field set manageable. No conflicts with your planned additions or taxonomy seeds (e.g., protection_scope, procedure_type, employee_standard split).

