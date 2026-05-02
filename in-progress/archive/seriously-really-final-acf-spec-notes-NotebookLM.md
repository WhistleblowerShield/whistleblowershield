As **WS-Core**, I have performed a high-fidelity reconciliation of the `register-taxonomies.php` registry and the `legal-record-acf-fields.md` canonical draft. To ensure the foundation is truly "James-proof" (addressing immediate survival) and "Maya-proof" (addressing fear of blowback), I have identified the final set of legal nuances required to bridge the gap between "statute-on-paper" and "success-in-court."

Below are the four required tables for final integration.

### Table 1: Proposed New Fields

| Priority | Record Group/Type | ACF Tab | Field Name | Description |
| :--- | :--- | :--- | :--- | :--- |
| **1** | Substantive | SOL & Thresholds | `sol_trigger_event` | **The "Chardon Rule" Trap**: Select field (notice-of-action, occurrence-of-action, discovery-of-harm). Identifies if the clock starts when James is *notified* of firing or his last day of work. |
| **2** | Substantive | Waiver & Scope | `sovereign_immunity_waiver` | **The Public Sector Shield**: Select field (explicit-waiver, implied-waiver, none, not-applicable). Critical for government workers; without an explicit waiver, the protection may be unenforceable. |
| **3** | Substantive | Classifications | `confidentiality_mandate` | **Maya's Anonymity**: Select field (govt-must-conceal, employer-must-conceal, both, discretionary). Captures if the law *mandates* identity protection rather than just permitting it. |
| **4** | Precedent | Relationships | `ruling_impact_weight` | **The "Leading Case" Signal**: Select field (landmark, incremental, clarifying, distinguishing). Helps Daniel distinguish between a minor procedural ruling and a sea-change in doctrine. |
| **5** | Substantive | Burden of Proof | `pretext_standard` | **The "Liar" Test**: Select field (pretext-only, pretext-plus, mixed-motive-alt). Captures the specific standard required to defeat an employer’s "legitimate reason" defense. |
| **6** | Substantive | Process & Remedies | `election_of_remedies_waiver` | **The "Bridge-Burner"**: Boolean. Captures if filing under this specific statute automatically waives the right to sue under common law or other acts (e.g., NJ CEPA or NY Labor Law 740). |

---

### Table 2: Changes to Existing Fields

| Priority | Record Group/Type | ACF Tab | Field Affected | Effect on Existing Datapoint |
| :--- | :--- | :--- | :--- | :--- |
| **1** | Substantive | Process & Remedies | `primary_agency` | **Exhaustion Mapping**: Change logic to require an "Agency Type" meta-tag (Admin-Adjudicatory vs. Investigative-Only) to clarify if James can actually get a ruling there. |
| **2** | Substantive | SOL & Thresholds | `sol_trigger` | **Accrual Nuance**: Expand select options to include `constructive-discharge-accrual` to distinguish from standard termination dates. |
| **3** | Precedent | Identity & Publishing | `binding_scope` | **Daniel's Precision**: Add `overruled-in-part` and `questioned-by-subsequent-court` to reflect the often-fractured state of case law. |
| **4** | Substantive | Process & Remedies | `reward_discretion_standard` | **The "Toothless" Check**: Add `statutory-floor` (e.g., "not less than 15%") to distinguish from "up to" discretionary rewards. |
| **5** | Substantive | Burden of Proof | `employer_knowledge_scope` | **The "Cat's Paw" Bridge**: Add `imputed-knowledge` as an option to handle cases where the decision-maker was "used" by a biased supervisor. |

---

### Table 3: Proposed New Taxonomy Tables

| New Taxonomy Table | Example Terms | Concept Covered |
| :--- | :--- | :--- |
| **`ws_legal_authority_source`** | Constitutional, Statutory, Regulatory, Executive Order, Judicial Doctrine | **The Hierarchy of Law**: Allows users to filter by the "weight" and "permanence" of the legal basis for the protection. |
| **`ws_public_policy_exception`** | Refusal to Violate Law, Reporting Illegal Act, Exercising Legal Right, Performance of Public Duty | **Common Law Categorization**: Specifically for `jx-common-law` records to categorize *why* the at-will employment doctrine was pierced. |
| **`ws_administrative_exhaustion_path`** | Mandatory-Internal, Mandatory-Agency, Optional-Agency, No-Exhaustion | **The "James" Road-Map**: Provides high-fidelity filtering for the specific procedural gate James must clear before reaching a court. |

---

### Table 4: Proposed New Terms for Existing Taxonomies

| Table | Proposed New Terms | Gap Covered in Context |
| :--- | :--- | :--- |
| **`ws_adverse_action`** | `retaliatory-litigation` | **The SLAPP Threat**: Covers counter-suits or "Litigation Harassment" meant to exhaust the whistleblower’s resources—a major fear for Maya. |
| **`ws_remedy`** | `tax-gross-up` | **The IRS Buffer**: Compensates the whistleblower for the increased tax liability of receiving a multi-year back-pay award in a single lump sum. |
| **`ws_legal_recognition`** | `prospective-whistleblower-protection` | **The "Pre-Emptive" Strike**: Protects employees who are *preparing* to report but are fired before the disclosure is finalized. |
| **`ws_protected_disclosure`** | `mismanagement-of-funds` | **Government Nuance**: Distinguishes between "Illegal Acts" and "Gross Mismanagement/Waste," which is a primary trigger in many public-sector acts. |
| **`ws_employer_defense`** | `bona-fide-occupational-qualification` | **The "Safety" Shield**: Handles cases where the employer claims the action was based on essential job performance/safety rather than the disclosure. |
| **`ws_legal_recognition`** | `continuing-violation-doctrine` | **SOL Relief**: Allows James to link older retaliatory acts to newer ones to bypass a short (e.g., 30-day) Statute of Limitations. |

---

**Final Reconciler's Note:** These additions finalize the "Data Foundation" by ensuring that the most frequent "hidden" causes of case dismissal (SOL trigger errors, lack of immunity waivers, and exhaustion traps) are captured as structured, queryable data.