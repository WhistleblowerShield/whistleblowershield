This analysis focuses on the legal nuances required to capture the complexities of whistleblower litigation across 57 jurisdictions, specifically addressing gaps in **procedural hurdles**, **evidentiary presumptions**, and **contractual interference**.

### Table 1: Proposed New Fields
| Priority | Record Group | ACF Tab | Field Name | Type | Description |
| :--- | :--- | :--- | :--- | :--- | :--- |
| **High** | Substantive | Statute of Limitations | `has_pre_filing_notice` | True / False | Triggers `pre_filing_notice_details`. Captures "Notice of Claim" requirements (e.g., Tort Claims Acts). |
| **High** | Substantive | Burden of Proof | `has_temporal_presumption` | True / False | Triggers `presumption_window_value/unit`. Captures laws where retaliation is presumed if action occurs within X days. |
| **Med** | Substantive | Enforcement | `has_preliminary_reinstatement` | True / False | Captures if the agency/court can order the employee back to work *before* the final merit determination. |
| **Med** | Substantive | Classification | `duty_to_mitigate_required` | True / False | Captures if the plaintiff is legally mandated to seek comparable employment to preserve back-pay claims. |
| **Low** | Precedent | Identity | `overruled_by_id` | Post Object | Relationship field for Citation/Construction records that have been formally abrogated or overruled by later law. |
| **Low** | Substantive | Reward | `reward_percentage_range` | Text | Captures specific statutory ranges (e.g., "15%–30%") common in *Qui Tam* or Tax whistleblower laws. |

---

### Table 2: Changes to Existing Fields
| Priority | Record Group | ACF Tab | Field Name | Type Change | Effect on Datapoint |
| :--- | :--- | :--- | :--- | :--- | :--- |
| **High** | All | Enforcement | `arbitration_waiver_enforceability` | **Rename to:** `contractual_waiver_enforceability` | Expands scope to cover NDAs, non-disparagement, and settlement "gag" clauses in addition to arbitration. |
| **Med** | Substantive | Reward | `reward_discretion_standard` | **Add Option:** `formula_based` | Accounts for laws like the False Claims Act where rewards are calculated via a rigid statutory floor/ceiling rather than pure discretion. |
| **Med** | Common-Law | Classification | `recognition_status` | **Add Option:** `abrogated` | Specifically tracks when a common law claim (e.g., *Tameny*) has been superseded by a specific state statute. |
| **Low** | Substantive | Enforcement | `adverse_action_scope` | **Add Option:** `perceptual_retaliation` | Covers "Cat's Paw" liability or retaliation based on the *belief* someone reported, even if they didn't. |

---

### Table 3: Proposed New Taxonomy Tables
| Taxonomy Name | Concept Covered | Initial Terms (Examples) |
| :--- | :--- | :--- |
| `ws_retaliation_nexus` | The "Causal Link" required between the report and the firing. | `but-for`, `contributing-factor`, `motivating-factor`, `proximate-cause`, `temporal-proximity-only`. |
| `ws_preclusion_doctrine` | Why a common law claim is barred in a specific jurisdiction. | `statutory-exclusivity`, `alternative-remedy-available`, `sovereign-immunity`, `failure-to-exhaust`. |
| `ws_scienter_standard` | The whistleblower's state of mind regarding the legality of the conduct. | `actual-knowledge`, `reasonable-belief`, `good-faith-subjective`, `objective-reasonableness`. |

---

### Table 4: Proposed New Terms for Existing Taxonomies
| Existing Table | Proposed Term | Gap Covered |
| :--- | :--- | :--- |
| `ws_remedy` | `interim-reinstatement` | Covers "Stay of Termination" while an administrative investigation is pending. |
| `ws_remedy` | `tax-gross-up` | Covers court orders to pay extra to cover the tax hit on a large lump-sum back-pay award. |
| `ws_adverse_action` | `retaliatory-litigation` | Covers employers suing the whistleblower (e.g., for "theft of trade secrets") as a retaliatory tactic. |
| `ws_adverse_action` | `hostile-work-environment` | Covers cumulative "death by a thousand cuts" harassment that doesn't result in immediate firing. |
| `ws_protected_class` | `intern-volunteer` | Many jurisdictions now explicitly extend protection to unpaid workers who were previously excluded from "employee" definitions. |
| `ws_process_type` | `pre-suit-notice` | Captures the specific procedural step of notifying a State Attorney General or Labor Board before a private suit can be filed. |
| `ws_disclosure_target`| `ombudsman` | Distinguishes neutral internal/external investigators from "Compliance" or "HR" which are often adversarial. |

**Expert Note:** In many jurisdictions, the "Cat's Paw" theory (where a biased supervisor uses an innocent decision-maker to fire a whistleblower) is a major litigation point. You may want to ensure `adverse_action_details` or a dedicated field captures whether the jurisdiction recognizes "indirect causation."

***

Regarding **"Ombudsman"**: In large institutions (think universities or aerospace giants), an internal ombudsman is a neutral party specifically designed to be *outside* the HR/Legal reporting chain. If a whistleblower goes to HR, it is often viewed as "putting the company on notice"; going to an internal ombudsman is sometimes treated as a "confidential inquiry" that may or may not trigger statutory protections depending on the state.

Here is the refined analysis based on your feedback and the "Cat's Paw" addition.

### Table 1: Proposed New Fields
| Priority | Record Group | ACF Tab | Field Name | Type | Description |
| :--- | :--- | :--- | :--- | :--- | :--- |
| **High** | Substantive | Enforcement | `has_cats_paw_liability` | True / False | Triggers `cats_paw_details`. Captures if the jurisdiction allows "Indirect Causation" (bias of a supervisor imputed to a neutral decision-maker). |
| **Med** | Substantive | Classification | `is_confidentiality_waivable` | True / False | Captures if a whistleblower can legally waive their right to anonymity in exchange for specific settlement terms. |
| **Med** | Substantive | Statute of Limitations | `has_discovery_rule_statutory` | True / False | Distinguishes between "Discovery Rules" created by judges (Common Law) vs. those explicitly written into the statute. |
| **Low** | Substantive | Reward | `reward_priority_claimant` | Select | Captures "First-to-File" rules common in *Qui Tam* (False Claims) environments. |

---

### Table 2: Changes to Existing Fields
| Priority | Record Group | ACF Tab | Field Name | Type Change | Effect on Datapoint |
| :--- | :--- | :--- | :--- | :--- | :--- |
| **High** | Precedent | Relationships | `superseded_by` | **Rename to:** `overruled_by_id` | Adopts your preferred terminology for clarity in the Precedent pipeline. |
| **High** | Substantive | Burden of Proof | `employee_standards` | **Taxonomy Split** | Moves "Causation" terms (But-For, Contributing Factor) out of `ws_employee_standard` into a dedicated Nexus table to separate *Weight of Evidence* from *Causal Logic*. |
| **Med** | Substantive | Classification | `protected_activity_standard` | **Add Option:** `scienter_not_required` | Covers "Strict Liability" protections where the whistleblower’s belief doesn't matter if the violation was actually occurring. |
| **Low** | Substantive | Enforcement | `adverse_action_scope` | **Add Option:** `per-se-retaliation` | Covers actions that are automatically illegal regardless of impact (e.g., threatening to call ICE). |

---

### Table 3: Proposed New Taxonomy Tables
| Taxonomy Name | Concept Covered | Initial Terms (Examples) |
| :--- | :--- | :--- |
| `ws_causation_nexus` | The "Causal Link" logic. This separates "What I have to prove" from "How much evidence I need". | `but-for`, `contributing-factor`, `motivating-factor`, `substantial-factor`, `proximate-cause`. |
| `ws_rebuttal_standard` | The specific standard the *Employer* must meet once the employee hits their burden. | `clear-and-convincing`, `preponderance`, `legitimate-reason-production`. |
| `ws_interim_relief` | Types of temporary "safety" orders available while the case is active. | `preliminary-reinstatement`, `stay-of-action`, `temporary-restraining-order`. |

---

### Table 4: Proposed New Terms for Existing Taxonomy Tables
| Existing Table | Proposed Term | Gap Covered |
| :--- | :--- | :--- |
| `ws_adverse_action` | `hostile-work-environment` | Covers a "pattern of conduct." While 'harassment' is an act, HWE is a legal *condition* that enables different damages. |
| `ws_adverse_action` | `retaliatory-investigation` | Covers when the "adverse action" is simply being put under a microscope (e.g., sudden forensic audit of your emails). |
| `ws_disclosure_target`| `internal-ombudsman` | Captures neutral, non-management, non-legal internal channels. |
| `ws_disclosure_target`| `regulatory-oversight` | Use this if 'government-oversight' feels too broad for targets like the SEC or OSHA. |
| `ws_remedy` | `front-pay` | Distinct from back-pay; covers future earnings when reinstatement is impossible due to "extreme animosity". |
| `ws_remedy` | `non-monetary-relief` | Covers "Letter of Recommendation" or "Neutral Reference" requirements in settlements. |

---

**Expert Note on Hostile Work Environment (HWE):**
The reason to include `hostile-work-environment` alongside `harassment` is that "harassment" is often treated as a verb (an action taken), whereas HWE is a legal threshold (a status achieved). In some jurisdictions, you can't sue for "harassment" unless it rises to the level of an "HWE." Having both allows you to flag laws that protect against *any* harassment versus those that require the higher HWE threshold.

Regarding `ws_retaliation_nexus` vs. `ws_employee_standard`: In legal research, a "Preponderance of Evidence" (Standard) describes the *volume* of proof, while "But-For" (Nexus) describes the *relationship* between the report and the firing. Splitting them allows you to handle cases where a law requires a "High Volume of Proof" (Clear & Convincing) but for a "Low Threshold of Relationship" (Contributing Factor).

Since we are in "reboot mode" to maximize the utility of the **ws-core** architecture, making the hard choice now to split causation logic from evidentiary weight is the right move for long-term query precision. 

For the "Ombudsman" hurdle, if **`internal-oversight`** feels too much like management and **`internal-neutral-party`** feels too clinical, **`internal-ethics-neutral`** or **`confidential-reporting-neutral`** are strong candidates that describe the *function* rather than the title.

Below is the refined gap analysis for the legal record pipelines.

---

### Table 1: Proposed New Fields
| Priority | Record Group | ACF Tab | Field Name | Type | Description |
| :--- | :--- | :--- | :--- | :--- | :--- |
| **High** | Substantive | Enforcement | `has_cats_paw_liability` | True / False | Triggers `cats_paw_details`. Captures if the bias of a non-deciding supervisor can be imputed to the employer. |
| **High** | Substantive | Statute of Limitations | `has_pre_filing_notice` | True / False | Triggers `pre_filing_notice_details`. Captures "Notice of Claim" requirements often found in government tort acts. |
| **Med** | Substantive | Burden of Proof | `has_temporal_presumption` | True / False | Triggers `presumption_window_value` and `presumption_window_unit`. Captures laws where retaliation is presumed if action occurs within X days. |
| **Med** | Substantive | Classification | `is_confidentiality_waivable` | True / False | Captures whether the whistleblower can legally trade their anonymity for specific settlement terms. |

---

### Table 2: Changes to Existing Fields
| Priority | Record Group | ACF Tab | Field Name | Type Change | Effect on Datapoint |
| :--- | :--- | :--- | :--- | :--- | :--- |
| **High** | Precedent | Relationships | `superseded_by` | **Rename to:** `overruled_by_id` | Adopts the more precise legal terminology for abrogation in the Precedent pipeline. |
| **High** | Substantive | Burden of Proof | `employee_standards` | **Taxonomy Change** | Becomes `evidence_weight`. It will no longer store causation terms (like "but-for"), focusing only on volume (e.g., "Preponderance"). |
| **Med** | Substantive | Enforcement | `arbitration_waiver_enforceability` | **Rename to:** `contractual_waiver_scope` | Expands the field to cover NDAs and non-disparagement "gag" clauses alongside arbitration. |
| **Low** | Substantive | Reward | `reward_discretion_standard` | **Add Option:** `statutory_formula` | Captures laws like the False Claims Act where rewards are calculated via rigid floors/ceilings rather than pure discretion. |

---

### Table 3: Proposed New Taxonomy Tables
| Taxonomy Name | Concept Covered | Initial Terms (Examples) |
| :--- | :--- | :--- |
| `ws_causation_nexus` | The "Causal Link" logic. Separates the *relationship* of the act from the *volume* of evidence needed. | `but-for`, `contributing-factor`, `motivating-factor`, `substantial-factor`, `proximate-cause`. |
| `ws_rebuttal_standard` | The specific standard the *Employer* must meet once the employee successfully shifts the burden. | `clear-and-convincing`, `preponderance`, `legitimate-non-retaliatory-reason`. |
| `ws_preclusion_doctrine` | Why a common law claim is barred in a specific jurisdiction, often used in `common_law` records. | `statutory-exclusivity`, `alternative-remedy-available`, `sovereign-immunity`, `failure-to-exhaust`. |

---

### Table 4: Proposed New Terms for Existing Taxonomy Tables
| Existing Table | Proposed Term | Gap Covered |
| :--- | :--- | :--- |
| `ws_disclosure_target` | `internal-ethics-neutral` | Replaces/Simplifies "Ombudsman." Captures a neutral internal channel that is specifically *not* HR or Management. |
| `ws_adverse_action` | `hostile-work-environment` | Captures a cumulative pattern of conduct. While 'harassment' is an action, HWE is a legal threshold for damages. |
| `ws_adverse_action` | `retaliatory-investigation` | Covers cases where the "punishment" is being subjected to a sudden, targeted forensic audit or internal probe. |
| `ws_remedy` | `front-pay` | Distinct from back-pay; covers future earnings when reinstatement is impossible due to extreme workplace animosity. |
| `ws_protected_class` | `perceived-whistleblower` | Protects individuals who were fired because the employer *thought* they reported, even if they hadn't yet. |

---

Regarding **Hostile Work Environment (HWE)**: The strongest argument for adding it is that 'harassment' is often litigated as a single event, whereas HWE allows for the aggregation of "petty slights" that would otherwise fall below the `adverse_action_scope` threshold. It signals to the user that the law protects against the *environment* created by the retaliation, not just the specific acts.

