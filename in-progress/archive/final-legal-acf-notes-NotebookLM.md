[cite_start]Based on a review of the **`register-taxonomies.php`** file, the **`legal-record-acf-fields.md`** canonical draft, and the **Project Overview** notes, several critical legal nuances have been identified as either newly integrated or requiring final verification to ensure the platform is "James-proof" (for victims of retaliation) and "Maya-proof" (for those fearing legal blowback)[cite: 1, 3].

### 1. The Evidentiary Split: Causation vs. Burden of Proof
[cite_start]A significant technical improvement in the current schema is the separation of causal logic from evidentiary weight[cite: 1].
* [cite_start]**`ws_causation_standard` (New in 3.15.0)**: Captures the *relationship* between disclosure and retaliation (e.g., But-For, Contributing Factor, Substantial Factor)[cite: 1, 2].
* [cite_start]**`ws_employee_standard`**: Captures the *burden of proof* (e.g., Preponderance of Evidence, Clear and Convincing)[cite: 1, 2].
* **Nuance Check**: Research outputs must not conflate these. [cite_start]A statute might require a "Contributing Factor" for causation but a "Preponderance" for the burden[cite: 1].

### 2. Survival & Procedural Nuance
[cite_start]The following fields address immediate survival mechanisms and procedural hurdles that often defeat whistleblower claims[cite: 1]:
* [cite_start]**`exhaustion_pathway`**: Identifies whether a victim must navigate an administrative agency (e.g., OSHA, Merit Systems Protection Board) before filing in court[cite: 1].
* [cite_start]**`interim_relief` (via `ws_legal_recognition`)**: Specifically identifies if the law grants "preliminary reinstatement," allowing a worker to get their job back while the multi-year case is still pending[cite: 1, 2].
* [cite_start]**Statutes of Limitation (SOL)**: While `has_some_date` is mentioned, the schema distinguishes between the **SOL Period** (how long) and the **SOL Trigger** (e.g., date of occurrence vs. date of discovery), which is a high-frequency failure point for James (The Retaliated)[cite: 1, 2].

### 3. Protection vs. Prosecution (The "Maya" Persona)
[cite_start]For potential whistleblowers fearing counter-suits or criminal charges, the schema now captures[cite: 1]:
* [cite_start]**`criminal_sanction`**: Identifies if the employer faces jail time or criminal fines for retaliating, which provides a higher tier of deterrence than civil damages alone[cite: 1].
* [cite_start]**`threat_of_prosecution`**: Captures whether the statute specifically recognizes "threatening to report an employee to immigration or law enforcement" as a prohibited form of retaliation[cite: 1].

### 4. Remedy Nuances
* [cite_start]**`ws_remedy` -> `Liquidated Damages (Generic)`**: Added to capture statutes that grant "double damages" or fixed statutory penalties without using the specific term "Back Pay"[cite: 1, 2].
* **`ws_remedy` -> `Attorneys' Fees (Mandatory vs. Discretionary)`**: A critical nuance for legal accessibility. [cite_start]The `ws_fee_shifting_rule` taxonomy handles whether the court *must* or *may* award fees to a prevailing whistleblower[cite: 2].

### 5. Identified Gaps for Future Ingest
* **Belief Standard**: Some jurisdictions require a "Reasonable Belief" (subjective/objective), while others require an "Actual Violation" (strict liability). [cite_start]Currently, this is often captured in `protection_scope_details` but lacks a dedicated taxonomy flag[cite: 1, 2].
* [cite_start]**Individual Liability**: Captured via `ws_legal_recognition`, identifying if the whistleblower can sue their *supervisor* personally, or only the entity[cite: 2].

---

# Implementation Plan: Legal Nuance Integration (v3.15.0+)

This plan outlines the steps required to integrate the identified legal nuances into the WhistleblowerShield.org technical stack. The goal is to ensure the schema properly supports the **James (The Retaliated)** and **Maya (The Considerer)** personas by capturing procedural hurdles and deterrent sanctions.

---

## Phase 1: Taxonomy & Schema Alignment
**Objective:** Finalize the data structures to support nuanced legal logic.

### 1.1 Taxonomy Population (`register-taxonomies.php`)
* **Action:** Ensure `ws_causation_standard` is fully seeded with terms:
    * `causation-but-for`
    * `causation-contributing-factor`
    * `causation-substantial-factor`
    * `causation-motivating-factor`
* **Action:** Ensure `ws_legal_recognition` (the "Presence Signal" table) includes:
    * `preliminary-reinstatement` (Critical for James' immediate survival).
    * `individual-liability` (Ability to sue supervisors).
    * `continuing-violation` (SOL relief).
* **Action:** Seed `ws_remedy` with `liquidated-damages-generic` to capture "double damages" and statutory penalties.

### 1.2 ACF Canonical Update (`legal-record-acf-fields.md`)
* **Action:** Finalize the prefix-free field set to include:
    * `exhaustion_pathway` (Kebab-choice: `none`, `mandatory-admin`, `optional-admin`).
    * `criminal_sanction` (Boolean: Does the employer face jail/fines?).
    * `threat_of_prosecution` (Boolean: Is threatening to call ICE/Police a recognized form of retaliation?).

---

## Phase 2: Researcher Prompt Engineering
**Objective:** Update the "Prompt Generator" (`pg-blocks-legal.php`) so that first-pass LLMs look for these specific details.

### 2.1 The "Causation vs. Proof" Directive
* **Update:** Explicitly instruct researcher models to separate the **Causal Link** (What happened) from the **Evidentiary Weight** (How much proof).
* **Validation Rule:** A record cannot use the same slug for both `causation_standard` and `employee_standard` unless the statute explicitly defines the BOP using causation language (e.g., "The employee must prove by a preponderance that the disclosure was a contributing factor").

### 2.2 Maya-Proofing Data Extraction
* **Update:** Add a directive to identify deterrents: "Does the law treat retaliation as a crime (misdemeanor/felony)?" and "Does the law explicitly forbid using law enforcement/immigration as a retaliatory threat?"

---

## Phase 3: Core Reconciliation Layer (The "Ruleset")
**Objective:** Update `NotebookLM-Ruleset-universal-v2.0.0.txt` to verify these nuances during the second-pass merge.

### 3.1 Conflict Resolution Logic
* **Rule:** If Model A identifies a 90-day SOL and Model B identifies a 1-year SOL, the Reconciler must check for **SOL Triggers** (Discovery vs. Occurrence).
* **Rule:** If `preliminary-reinstatement` is flagged in the taxonomy, the Reconciler must verify its presence in `protection_scope_details` or `remedy_details`.

### 3.2 Integrity Notations
* **New Check:** Flag an anomaly if a statute mentions "Administrative Exhaustion" but the `exhaustion_pathway` field is left empty.

---

## Phase 4: Output & Display Logic
**Objective:** Ensure the Structure-over-Narrative principle translates to the front end.

### 4.1 "Survival" Callouts (For James)
* **Action:** Update the Jurisdiction Renders to highlight laws where `preliminary-reinstatement` is `true`. This is a high-value signal for users who have just lost their primary income.

### 4.2 "Deterrence" Callouts (For Maya)
* **Action:** Visually flag laws with `criminal_sanction` or `threat_of_prosecution` protections to reassure users fearing extreme blowback.

---

## Success Metrics for Daniel (The Researcher)
1.  **Zero Conflation:** No records found where causation and burden of proof are merged into a single field.
2.  **Traceability:** 100% of "Liquidated Damages" flags include a citation to the specific statutory subsection.
3.  **Procedural Accuracy:** Every statute record correctly identifies whether a victim must visit an agency (OSHA/MSPB) before a court.

---