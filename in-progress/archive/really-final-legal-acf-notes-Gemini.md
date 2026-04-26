This final nuance pass targets the "litigation-phase" landmines—the subtle legal mechanisms that often defeat a whistleblower's claim even after they have proven retaliation occurred.

### Table 1: Proposed New Fields
| Priority | Record Group | ACF Tab | Field Name | Type | Description |
| :--- | :--- | :--- | :--- | :--- | :--- |
| **High** | Substantive | Waiver & Scope | `has_personal_indemnity` | Bool | Triggers `personal_indemnity_details`. Captures if the employer is legally prohibited from seeking "contribution" or suing the whistleblower for damages caused by the disclosure. |
| **High** | Substantive | Enforcement | `has_discretionary_immunity` | Bool | Triggers `discretionary_immunity_details`. Specifically for government employers who argue that a firing was a "discretionary policy decision" immune from judicial review. |
| **Med** | Precedent | Identity | `is_pre_emptive_suit` | Bool | Tracks cases where the *employer* sued the whistleblower first (e.g., for a declaratory judgment of non-violation) to anchor the venue or chill the report. |
| **Med** | Substantive | Reward | `reward_priority_claimant` | Select | Options: `first-to-file` \| `first-to-report` \| `pro-rata`. Essential for Qui Tam/Reward records to determine who gets paid in multi-whistleblower cases. |
| **Low** | Substantive | Process & Remedies | `has_expert_witness_fees` | Bool | Captures if "litigation costs" explicitly includes the expensive fees for forensic accountants or industry experts. |

---

### Table 2: Changes to Existing Fields
| Priority | Record Group | Tab | Field Name | Type Change | Effect on Datapoint |
| :--- | :--- | :--- | :--- | :--- | :--- |
| **High** | Substantive | Classifications | `protected_action_source` | **Add Option:** `professional-ethics-code` | Covers "refusal to participate" based on a nurse or lawyer's professional licensing requirements rather than a statute. |
| **High** | Substantive | Burden of Proof | `employer_awareness_requirement` | **Add Option:** `cat-paw-attribution` | Links the specific "Cat's Paw" theory to the awareness standard—where the decider's ignorance is irrelevant if the influencer was biased. |
| **Med** | Substantive | Retaliation | `adverse_action_scope` | **Add Option:** `per-se-retaliation` | Covers actions that are illegal *regardless* of impact on employment, such as threatening to contact immigration authorities. |
| **Low** | All | Identity | `protection_scope` | **Add Option:** `interference-protection` | Distinguishes between protection from *retaliation* and protection from *interference* (e.g., employer blocking access to the hotline). |

---

### Table 3: Proposed New Taxonomy Tables
| Taxonomy Name | Concept Covered | Initial Terms (Examples) |
| :--- | :--- | :--- |
| `ws_evidentiary_privilege` | How "Protected Activity" interacts with corporate/legal secrets during litigation. | `attorney-client-override`, `trade-secret-safe-harbor`, `sealed-filing-requirement`, `privilege-waived-by-retaliation`. |
| `ws_litigation_shield` | Protections for the whistleblower's current status *during* the lawsuit. | `identity-shield-john-doe`, `protective-order-presumptive`, `stay-of-parallel-arbitration`, `anti-blacklisting-injunction`. |
| `ws_standard_of_review` | The level of deference a court gives to an agency's initial whistleblower determination. | `de-novo`, `substantial-evidence`, `arbitrary-capricious`, `abuse-of-discretion`. |

---

### Table 4: Proposed New Terms for Existing Taxonomy Tables
| Term | Table | Gap Covered |
| :--- | :--- | :--- |
| `advice-of-counsel-defense` | `ws_employer_defense` | When an employer claims they aren't liable because they relied on a lawyer's advice to fire the whistleblower. |
| `reassignment-to-lower-prestige` | `ws_adverse_action` | Distinct from "demotion"; covers moving a high-level whistleblower to a "broom closet" role with the same pay but no authority. |
| `pre-suit-discovery` | `ws_process_type` | Captures jurisdictions where a whistleblower can legally "interview" company witnesses before even filing a lawsuit. |
| `internal-ethics-neutral` | `ws_disclosure_target` | Finalized term to replace the confusing "Ombudsman" for laypeople, as discussed in the reboot strategy. |
| `tax-gross-up` | `ws_remedy` | Covers court-ordered payments to offset the "tax spike" caused by receiving years of back-pay in a single lump sum. |

**Expert Insight:** The **`has_discretionary_immunity`** field is the "hidden killer" of government whistleblower suits. If a state classifies the termination as a "policy-level discretionary act," the whistleblower can lose even if the retaliation is obvious. Adding this boolean ensures you can flag jurisdictions where statutory protection is essentially an empty promise for public employees..

How do you want to handle the **`is_pre_emptive_suit`** logic—should it trigger a dedicated context field to describe the "race to the courthouse"?