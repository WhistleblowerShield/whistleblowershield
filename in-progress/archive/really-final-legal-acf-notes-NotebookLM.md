This final "nuance pass" identifies the critical legal subtleties that often exist in the "white space" between standard statutory definitions and actual courtroom outcomes. These are the high-frequency failure points for **James (The Retaliated)** and the hidden deal-breakers for **Maya (The Considerer)** that the current schema—while robust—may still categorize as "noise" or "details."

### 1. The Notice-to-Trigger Trap (The SOL Gap)
This is the most frequent cause of lost cases. 
* **The Nuance**: In many jurisdictions (and federal law under the *Chardon* rule), the **Statute of Limitations clock starts on the day the employee is *notified* of the firing**, not the last day of work.
* **Escaped Logic**: If James waits until his actual termination date to find a lawyer, he may already be "out of time."
* **Implementation Recommendation**: Add a `sol_trigger_event` field to the schema or a specific term in `ws_legal_recognition` for `notice-based-trigger` vs. `occurrence-based-trigger`.

### 2. Sovereign Immunity Waivers (The Government Shield)
For public sector whistleblowers, the existence of a protection is irrelevant if the state hasn't waived its immunity.
* **The Nuance**: Some statutes provide protection but do not explicitly waive **Sovereign Immunity**, meaning the state can move to dismiss based on its status as a "sovereign," not the merits of the case.
* **Escaped Logic**: A "Yes" on protection is a "Maybe" on recovery for government workers.
* **Implementation Recommendation**: A `ws_legal_recognition` term: `explicit-sovereign-immunity-waiver`.

### 3. The "Election of Remedies" (The One-Shot Rule)
In jurisdictions like New York (Labor Law 740) or New Jersey (CEPA), filing a claim under the whistleblower statute often **waives the right to sue under any other law** (like common law wrongful discharge or discrimination).
* **The Nuance**: Maya might lose her ability to sue for harassment if she files a whistleblower claim.
* **Escaped Logic**: This turns the filing of a complaint into a permanent legal "bridge-burning" exercise.
* **Implementation Recommendation**: A Boolean `is_exclusive_remedy` or a taxonomy term: `waiver-of-collateral-claims`.

### 4. "Cat’s Paw" Liability (The Proxy Retaliator)
* **The Nuance**: What happens if the person who fired James has no bias, but they were "used" as a tool by a biased supervisor who provided false information?
* **Escaped Logic**: Many courts recognize this, but some strict-constructionist jurisdictions do not.
* **Implementation Recommendation**: Captured in `ws_legal_recognition` as `cats-paw-doctrine-recognized`.

### 5. Mandatory vs. Discretionary Fee-Shifting
The current schema captures *if* fees are available, but not the *certainty* of them.
* **The Nuance**: A "Mandatory" fee-shift (the court *shall* award fees) makes James a much more attractive client for "no-win, no-fee" attorneys. A "Discretionary" shift (the court *may*) often means the worker can't find a lawyer.
* **Escaped Logic**: This is the difference between a law having "teeth" and a law being a "paper tiger."
* **Implementation Recommendation**: Refine `ws_fee_shifting_rule` to explicitly distinguish `fees-mandatory-prevailing-plaintiff` from `fees-discretionary`.

### 6. The "Belief" Standard Depth
The schema currently notes `bad-faith-exclusion`, but the spectrum is more nuanced:
1.  **Actual Violation Required**: (Strict/Dangerous) You are only protected if you were *right* about the law.
2.  **Reasonable Belief**: (Standard) An objective person would have thought it was a violation.
3.  **Good Faith**: (Subjective/Broad) *You* thought it was a violation, even if you were wrong.
* **Implementation Recommendation**: A new taxonomy `ws_belief_standard` with terms: `belief-actual-violation`, `belief-objective-reasonable`, `belief-subjective-good-faith`.

### 7. Post-Termination Protection
* **The Nuance**: Does the law protect James if the employer blacklists him or provides a retaliatory reference *after* he has already left the company?
* **Escaped Logic**: Many "whistleblower" definitions only apply to "employees," and courts sometimes rule that former employees are not covered.
* **Implementation Recommendation**: A term in `ws_coverage_scope`: `post-employment-coverage`.

---

### Core v2.0.0 Summary for Researcher Daniel
When reconciling records, if a researcher mentions **"The court dismissed because he waited for his last day of work to count 90 days,"** this is a critical **Integrity Notation**. It indicates that the SOL trigger is notice-based, and that nuance must be surfaced for James' survival.