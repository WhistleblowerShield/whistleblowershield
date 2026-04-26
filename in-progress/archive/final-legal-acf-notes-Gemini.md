This analysis identifies the remaining "blind spots" in legal nuance within your rebooted architecture, specifically targeting the procedural hurdles that often defeat whistleblower claims before they reach trial and the subtle ways employers retaliate through the legal process itself.

### Table 1: Proposed New Fields
| Priority | Record Group | ACF Tab | Field Name | Type | Description |
| :--- | :--- | :--- | :--- | :--- | :--- |
| **High** | Substantive | SOL & Thresholds | `has_cure_period` | Bool | Triggers `cure_period_details`. Captures if the whistleblower must give the employer/agency a set time (e.g., 30 days) to "fix" the violation before they are legally protected or can sue. |
| **High** | Substantive | Classification | `has_malicious_reporting_sanctions` | Bool | Triggers `malicious_reporting_details`. Captures explicit statutory penalties (fines/loss of protection) for reports made in "bad faith" or with "reckless disregard for truth". |
| **Med** | Substantive | Enforcement | `has_mandatory_mediation` | Bool | Triggers `mediation_details`. Captures if the jurisdiction requires a non-binding settlement attempt as a jurisdictional prerequisite to filing. |
| **Med** | Substantive | Classification | `is_evidence_collection_protected` | Bool | Captures if the law protects the employee from "theft of trade secrets" or "NDA breach" claims when they take documents for the purpose of a protected disclosure. |
| **Low** | Substantive | Enforcement | `has_pre_judgment_interest` | Bool | Captures if the court *must* or *may* add interest to back-pay awards from the date of the firing rather than the date of the verdict. |

---

### Table 2: Changes to Existing Fields
| Priority | Record Group | ACF Tab | Field Name | Type Change | Effect on Datapoint |
| :--- | :--- | :--- | :--- | :--- | :--- |
| **High** | All | SOL & Thresholds | `sol_trigger` | **Add Option:** `conclusion-of-admin-process` | Essential for "exhaustion" jurisdictions where the clock shouldn't start until the agency (e.g., DOL/OSHA) finishes its probe. |
| **High** | Substantive | Enforcement | `remedy_liquidated_multiplier` | **Add Option:** `statutory-daily-fine` | Captures laws (often environmental) that award a fixed dollar amount per day of retaliation rather than a multiplier of wages. |
| **Med** | Substantive | Classification | `disclosure_channel_scope` | **Add Option:** `mandatory-internal-first` | Explicitly flags laws that strip protection if the whistleblower goes to the press/govt without trying the internal chain first. |
| **Low** | Substantive | Classification | `protected_action_source` | **Add Option:** `professional-ethics-code` | Covers "refusal to participate" based on a nurse or lawyer's professional licensing code rather than a specific law. |

---

### Table 3: Proposed New Taxonomy Tables
| Taxonomy Name | Concept Covered | Initial Terms (Examples) |
| :--- | :--- | :--- |
| `ws_pre_suit_requirement` | Specific procedural "landmines" required before a valid claim exists. | `written-notice-to-employer`, `opportunity-to-cure`, `affidavit-of-merit`, `mandatory-consultation`. |
| `ws_venue_rule` | Where the suit can legally be filed (jurisdiction-within-jurisdiction). | `county-of-occurrence`, `employer-residence`, `capitol-county-only`, `plaintiff-residence`. |
| `ws_discovery_protection` | Protections during the litigation process itself. | `shield-from-depositions`, `protective-order-presumptive`, `confidential-document-exchange`. |

---

### Table 4: Proposed New Terms for Existing Taxonomy Tables
| Existing Table | Proposed Term | Gap Covered |
| :--- | :--- | :--- |
| `ws_remedy` | `neutral-reference` | Captures court-ordered "cleansing" of the employment record to prevent "blacklisting" by future employers. |
| `ws_remedy` | `attorney-fees-admin` | Distinguishes laws that pay for the lawyer during the *agency* phase vs. only the *court* phase. |
| `ws_adverse_action` | `retaliatory-discovery` | Covers when an employer uses the lawsuit to harass the whistleblower (e.g., subpoenaing their personal text history or family). |
| `ws_adverse_action` | `reprimand-undocumented` | Covers "verbal warnings" or "counseling memos" that don't hit the file but are used to chill future reporting. |
| `ws_legal_recognition` | `identity-of-accused-protected` | Captures laws that protect the *subject* of the whistleblower's report, which often restricts the whistleblower's ability to speak publicly. |
| `ws_legal_recognition` | `stay-of-disciplinary-action` | Specifically covers the legal power to freeze an ongoing firing process while a whistleblower claim is investigated. |

**Final Nuance Note:** The **"Opportunity to Cure"** (`has_cure_period`) is the single largest legal hurdle missing. In several jurisdictions, if a whistleblower reports a safety violation to the government without first giving the employer a "reasonable opportunity to correct" the condition, they lose *all* statutory protection. This is a vital distinction for a layperson to see immediately.

---

This proposal outlines the technical implementation plan for the **v3.15.0** update of the WhistleblowerShield core architecture. The primary objective is to transition from the legacy "Universal Consistency" rules to the new canonical field set, while integrating deep legal nuances regarding procedural hurdles and employer-side liability theories.

---

# Implementation Proposal: Legal Architecture Reboot (v3.15.0)

## 1. Executive Summary
This update executes a "Reboot Mode" strategy to decouple evidentiary volume from causal logic and standardize procedural "landmines" across all 57 jurisdictions. By replacing scattered boolean fields with the `ws_legal_recognition` signal taxonomy and introducing dedicated causation nexus tables, the database will support higher-precision queries for complex litigation scenarios such as "Cat's Paw" liability and "Opportunity to Cure" requirements.

## 2. ACF Field Architecture Updates
The following fields will be added or modified within the canonical `legal-record-acf-fields.md` framework.

### A. Classification Tab (Identity & Protection)
* **`is_confidentiality_waivable` (Bool):** Captures if a whistleblower can legally trade anonymity for specific settlement terms.
* **`is_evidence_collection_protected` (Bool):** Tracks if the law protects an employee from "theft of trade secrets" claims when gathering documents for a disclosure.
* **`has_malicious_reporting_sanctions` (Bool):** Triggers `malicious_reporting_details`; captures statutory penalties for "bad faith" reports.
* **`disclosure_channel_scope` (Update):** Add option `mandatory-internal-first` to flag laws that strip protection if external channels are used prematurely.

### B. Statute of Limitations & Thresholds Tab
* **`has_cure_period` (Bool):** Triggers `cure_period_details`. This tracks the mandatory timeframe an employee must give an employer to "fix" a violation before filing suit.
* **`sol_trigger` (Update):** Add option `conclusion-of-admin-process` to account for jurisdictions where the clock is stayed during agency investigation.
* **`filing_notice_target` (Update):** Ensure `pre-filing-notice` in `ws_legal_recognition` triggers the existing `filing_notice_context`.

### C. Enforcement Tab (Process & Remedies)
* **`has_cats_paw_liability` (Bool):** Triggers `cats_paw_context` when `cats-paw-liability` is present in `ws_legal_recognition`.
* **`has_mandatory_mediation` (Bool):** Triggers `mediation_details`.
* **`has_pre_judgment_interest` (Bool):** Tracks interest accrual on back-pay from the date of termination.
* **`remedy_liquidated_multiplier` (Update):** Add option `statutory-daily-fine` for environmental and specific safety statutes.

### D. Burden of Proof Tab
* **`has_temporal_presumption` (Bool):** Triggers `presumption_window_value`, `presumption_window_unit`, and `presumption_window_details`.
* **`employee_standards` (Migration):** Shift focus strictly to "Evidentiary Weight" (e.g., Preponderance); all causation terms are migrated to the new `ws_causation_standard` taxonomy.

## 3. Taxonomy & Seeding Updates
Updates to `register-taxonomies.php` to include new relational logic and expanded term sets.

### A. New Taxonomy Tables
| Taxonomy Name | Concept | Example Terms |
| :--- | :--- | :--- |
| `ws_pre_suit_requirement` | Jurisdictional Prerequisites | `written-notice`, `affidavit-of-merit`, `opportunity-to-cure` |
| `ws_venue_rule` | Filing Location Rules | `county-of-occurrence`, `employer-residence` |
| `ws_discovery_protection` | Litigation-phase safeguards | `shield-from-depositions`, `protective-order-presumptive` |
| `ws_rebuttal_standard` | Employer's shifting burden | `clear-and-convincing`, `legitimate-reason-production` |

### B. New Terms for Existing Tables
* **`ws_disclosure_target`:** Add `internal-ethics-neutral` (Simplified "Ombudsman").
* **`ws_adverse_action`:** Add `hostile-work-environment`, `retaliatory-investigation`, `retaliatory-discovery`.
* **`ws_remedy`:** Add `neutral-reference`, `attorney-fees-admin`, `front-pay`.
* **`ws_legal_recognition`:** Add `identity-of-accused-protected` and `stay-of-disciplinary-action`.

## 4. Logic & Hook Requirements
* **Overrule Sync:** The `superseded_by` field is formally renamed to `overruled_by_id` in the Precedent pipeline.
* **Nexus-Standard Split:** Register a save hook to ensure that if a term like `contributing-factor` is selected in `ws_employee_standard`, the system prompts the editor to define the logic in `ws_causation_standard`.
* **Admin Notices:** Implement cross-tab triggers for `has_cure_period` and `cats_paw_liability` to ensure detail fields are not left empty when the signal bool is true.

## 5. Implementation Roadmap
1.  **Phase 1:** Update `register-taxonomies.php` and bump gate versions to `1.2.0` for modified seeders.
2.  **Phase 2:** Register new ACF field groups using the prefix-free `ws_` naming convention.
3.  **Phase 3:** Deploy Python-based multi-agent pipeline to sanitize existing data into the new "Nexus vs. Weight" split.
4.  **Phase 4:** Manual editorial review pass focusing on the newly identified "Cure Period" and "Malicious Reporting" fields.