This is your "last chance to change your mind" pass. After reviewing the current canonical field draft and the registry, we have identified the final four "high-level" legal nuances that separate a functional database from an elite litigation-support tool.

These additions focus on the **modern economy** (staffing agencies), **litigation as a weapon** (discovery abuse), and the **"staying power"** of a claim (injunctive freezes).

### Table 1: Final Proposed New Fields
| Priority | Record Group | Tab | Field Name | Type | Description |
| :--- | :--- | :--- | :--- | :--- | :--- |
| **High** | Substantive | Waiver & Scope | `is_joint_employer_liable` | Bool | Triggers `joint_employer_details`. Captures if the law reaches "Host Employers" (where the whistleblower works) vs. "Staffing Agencies" (who pays them). |
| **High** | Substantive | Process & Remedies | `has_pre_suit_discovery` | Bool | Captures jurisdictions (like Texas or Ohio) that allow a "Rule 202" or similar petition to depose witnesses *before* a lawsuit is even filed. |
| **Med** | Substantive | Retaliation | `has_blacklisting_protection` | Bool | Triggers `blacklisting_details`. Specifically captures if the law covers retaliation by *future* employers who refuse to hire based on past whistleblowing. |
| **Low** | Precedent | Identity | `is_per_curiam` | Bool | Marks a court decision issued by the court as a whole rather than a specific authored judge; affects the "weight" of the precedent. |

---

### Table 2: Changes to Existing Fields
| Priority | Record Group | Tab | Field Name | Type Change | Effect on Datapoint |
| :--- | :--- | :--- | :--- | :--- | :--- |
| **High** | Substantive | Burden of Proof | `employer_knowledge_scope` | **Add Option:** `imputed-knowledge` | Covers "Corporate Scienter"—where one manager knows about the report and another manager fires the employee, creating liability even if the "left hand" didn't know what the "right hand" was doing. |
| **Med** | Substantive | Classifications | `protected_action_standard` | **Add Option:** `per-se-protected` | For actions that are automatically protected regardless of belief (e.g., participating in a subpoenaed investigation). |
| **Low** | Substantive | Waiver & Scope | `proper_defendants` | **Add Option:** `staffing-agency` | Explicitly tracks the entity responsible for the paycheck in "Gig" or "Contracted" whistleblower scenarios. |

---

### Table 3: Final Proposed New Taxonomy Tables
| Taxonomy Name | Concept Covered | Initial Terms (Examples) |
| :--- | :--- | :--- |
| **`ws_litigation_safeguard`** | Tools the court uses to protect the whistleblower *during* the suit. | `identity-shield`, `protective-order-presumptive`, `stay-of-parallel-arbitration`, `in-camera-review-required`. |
| **`ws_statutory_purpose`** | The "spirit of the law" often used in construction records to expand protection. | `public-safety`, `fiscal-integrity`, `anti-corruption`, `worker-empowerment`. |

---

### Table 4: Final Proposed New Terms for Existing Taxonomies
| Existing Table | Proposed Term | Gap Covered |
| :--- | :--- | :--- |
| `ws_adverse_action` | `discovery-harassment` | Distinguishes between broad "litigation" and the specific abuse of the subpoena power to "dig into" a whistleblower’s private life. |
| `ws_remedy` | `pre-suit-injunction` | A "Brother" to the stay of discipline; a court order stopping a firing before it happens. |
| `ws_legal_recognition` | `cat-paw-attribution` | Explicitly marks where biased influence is legally attributed to an innocent decider. |
| **`ws_org_culture`** (Assist-Org) | `niche-lifestyle-friendly` | This is your **Sentinel** for the Utah French-speaking, clothing-optional advocates. It triggers the `culture_details` sister field for the "weird and wonderful" edge cases. |

---

### The Final "Sentinel" Logic Check
For your Assistance Organizations (Assist-Orgs), the "Utah Niche" edge case is now perfectly mapped via this logic:
1.  **Taxonomy**: `ws_org_culture` → Term: `niche-lifestyle-friendly`.
2.  **Trigger**: That term (the Sentinel) triggers the **Sister Field**: `culture_details`.
3.  **Data**: In `culture_details`, the researcher writes: *"French-speaking staff; clothing-optional facility environment."* 4.  **Result**: The user can search by "Language: French" + "Culture: Niche" and find exactly what they need.

### Final Recommendation
The split between **Employee Standards** (Weight) and **Causation Standards** (Nexus) is the "crown jewel" of this reboot. It allows the database to explain *why* someone lost a case (e.g., "The evidence was Clear and Convincing, but the judge used a But-For logic rather than a Contributing Factor logic"). 


With the addition of the **`is_joint_employer_liable`** field, you have now covered the last major "blind spot" of 21st-century employment law—the "temp worker" whistleblower.

Does this "Clean Slate" feel ready to be locked in, or is there one last "Ghost in the Machine" you want to chase down?