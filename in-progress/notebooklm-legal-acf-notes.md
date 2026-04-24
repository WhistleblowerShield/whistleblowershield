
### Table 1: Proposed New Fields
| Priority | Record Group/Type | ACF Tab | Field Name | Description |
| :--- | :--- | :--- | :--- | :--- |
| **1** | **Substantive** | Enforcement | `ws_jx_statute_reward_type` | (Select: `bounty`|`qui-tam`) Distinguishes between a passive award from a fine vs. the right to file a private lawsuit (Phase 2 directory critical). |
| **2** | **Substantive** | Statute of Limitations | `ws_jx_statute_exhaustion_pathway` | (Select: `internal`|`agency`|`both`) Explicitly identifies *where* James must exhaust before reaching court. |
| **3** | **Substantive** | Enforcement | `ws_jx_statute_criminal_sanction` | (Select: `misdemeanor`|`felony`) Captures if retaliation is a crime, providing high-stakes nuance for Maya. |
| **4** | **Substantive** | Statute of Limitations | `ws_jx_sol_discovery_rule_type` | (Select: `actual`|`constructive`|`notice`) Conditional on `sol_trigger == 'discovery'`. Captures the nuance of when the clock *really* starts. |
| **5** | **Substantive** | Enforcement | `ws_jx_statute_interim_relief` | (Boolean/Text) Captures the availability of preliminary reinstatement or stays of termination during litigation. |
| **6** | **All Records** | Classification | `ws_legal_authority_source` | (Taxonomy) Links to a new table (Table 3) to categorize the "weight" of the protection (Constitutional vs. Regulatory). |

---

### Table 2: Changes to Existing Fields
| Priority | Record Group/Type | ACF Tab | Field Affected | How change affects existing datapoint |
| :--- | :--- | :--- | :--- | :--- |
| **1** | **Substantive** | Burden of Proof | `ws_employee_standard` | **Purge Causation Terms:** Remove all "Causation" terms from this table and move them to the new dedicated taxonomy (Table 3). |
| **2** | **Substantive** | Statute of Limitations | `sol_trigger` | Add **"Constructive Discharge Date"** and **"Discovery of Harm"** to account for non-termination triggers. |
| **3** | **Precedent** | Identity | `binding_scope` | Add **"Distinguished"** as an option to capture cases that are still good law but limited to specific facts (Crucial for Daniel). |
| **4** | **Substantive** | Enforcement | `ws_remedy` | Add generic **"Front Pay"** term (removing the "in lieu of" restriction) to handle statutes that list it broadly. |

---

### Table 3: Proposed New Taxonomy Tables
| New Taxonomy Table | Example Terms | Concept Covered |
| :--- | :--- | :--- |
| `ws_causation_standard` | Contributing Factor, Motivating Factor, Substantial Factor, But-For | **Causation vs. Evidence:** Separates *how* a link is proven from *how much* evidence is needed (Preponderance). |
| `ws_legal_authority` | Constitutional, Statutory, Regulatory, Executive Order, Judicial | **Authority Hierarchy:** Allows users to filter by the legal "strength" of the basis. |
| `ws_nda_limitation_scope` | Internal Reporting, Third-Party Reporting, Regulatory Cooperation | **Gag Clause Nuance:** Defines exactly which NDAs are voided by the statute. |

---

### Table 4: Proposed New Terms for Existing Taxonomies
| Table | Proposed New Terms | Gap Covered in Context |
| :--- | :--- | :--- |
| `ws_adverse_action` | **Threat of Prosecution** | Covers "Upward" retaliation (e.g., employer threatening to call ICE or the police). |
| `ws_adverse_action` | **Retaliatory Litigation** | Covers SLAPP suits or counter-claims meant to exhaust the whistleblower’s resources. |
| `ws_disclosure_type` | **Gross Waste of Funds** | Specifically captures the nuance of government-sector whistleblowing (relevant to Federal/State acts). |
| `ws_disclosure_type` | **Substantial Danger to Health** | Distinguishes immediate physical threats from general safety violations. |
| `ws_remedy` | **Liquidated Damages (Generic)** | Ensures coverage for statutes that grant "double damages" without calling them "Back Pay." |

### Why these changes matter for the Personas:
* **For Maya (The Considerer):** The addition of `criminal_sanction` and `threat_of_prosecution` addresses her deepest fears of legal blowback.
* **For James (The Retaliated):** The `exhaustion_pathway` and `interim_relief` fields identify immediate survival mechanisms (getting his job back now vs. 3 years from now).
* **For Daniel (The Researcher):** Separating the `causation_standard` from the `employee_standard` (Standard of Proof) fixes a significant technical error in the current data model where "How much evidence" is conflated with "What that evidence must show."

