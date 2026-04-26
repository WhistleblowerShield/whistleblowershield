You are indeed very close—your draft ACF field spec and taxonomy registration (and term seeding) in WhistleblowerShield are by far the most comprehensive I have seen for this legal problem domain. Here’s a critical, detail-oriented comparison and gap analysis, with a focus on "missed legal nuance" in the ACF field structure and its relationship to the taxonomies. The analysis proceeds in three parts:

---

## 1. What Is Covered

Your ACF and the taxonomy set, as listed, cover (with associated sentinel pattern and contextual triggers):

- **Jurisdictional scoping** (canonical USPS code taxonomy, derived fields, binding scope)
- **Citation, naming, and classification across all record types** (statutes, common-law, citations, constructions)
- **Event dates and retroactivity**
- **All major functional categories of whistleblower law**:
    - **Protected disclosures and actions** (extremely granular, with parent/child taxonomies, plus "has-details" fallback)
    - **Protected/excluded classes** (hierarchical structure tracks real law; details fields for editorial nuance)
    - **Adverse action, retaliation, all common forms** (including recent additions like retaliatory investigation/litigation)
    - **Channels, targets, agencies** (hierarchical with internal/external, now maps nearly all real-world scenarios*)
    - **Remedies, fee-shifting, preemption, exhaustion** ("has-limits", "has-phases", and direct enforcement structure for complex SOL/interplay)
    - **Burden of proof / evidentiary standards** (with new split causation vs evidentiary burden, both with detail capture)
    - **Procedures, relationships, right to action, waivers** (comprehensive, with structural hooks and override triggers)
    - **Recognition doctrine (via taxonomy)**—capturing the majority of underlying judge-made doctrines and their presence/absence.
- **Detail overflow for all nuanced cases ("has-details" fields with context companions)**
- **Distinction/relationship tracking for precedents, overruling, construction/extension, cross-tab dependencies**
- **Reward and relator share structure (for False Claims/Qui Tam cases)**
- **All required sentinel patterns for "other/unclear"**

---

## 2. What’s Potentially Missing or Variant in Broader Law (Nuance Gaps Identified)

### A. Protected Activity/Disclosure – Coverage for "Attempts" and "Intent"
- **Coverage for "attempted" protected activities** is present ("Attempted Reporting" in protected actions), but:
    - Some laws protect both actual disclosure **and** attempt/threat/intent—even preparing to disclose or refusing to act. In some fields (esp. SOX, FCA, labor), intent may create coverage before actual attempt (such as “attempted but deterred”).
    - **Current ACF Solution:** "Attempted Reporting" present, but there is no explicit field to track the distinction between "actual", "attempted", and "deterred" (where employer preempts or prevents).
    - **Potential Fix:** A field or taxonomy term like `deterred-disclosure` or extending protected_action_status (`actual|attempted|threatened|deterred`) would expressly track this nuance.

### B. Anti-Gag/Non-Retaliation Provisions
- **Anti-gag** provisions are not always NDA/contractual limits, but can appear as statutory bars on “policies or practices that would chill disclosure” (e.g., bar to post-complaint retaliation, chilling effect doctrine).
    - **Current Solution:** NDA limits are addressable; anti-retaliation is mapped in adverse actions.
    - **Potential Gap:** Explicit tracking of anti-gag statutes/policy bans (beyond NDA) is not called out.
    - **Potential Fix:** Add `anti-gag-provision` to the ws_legal_recognition taxonomy (with a details/context companion if present).

### C. Immunities, Exemptions, and Defendants
- **Immunity/Exemption logic:** You cover sovereign immunity (`sovereign_immunity_limits`). Some laws exclude **certain classes of defendant by role as "gatekeeper" or implementer** (ex: auditors, compliance staff, or state entity acting in federal capacity).
    - **Proper defendants** covers most use-cases.
    - **Potential Gap:** No explicit "special role immunity/exemption" field; e.g., *auditors exempted from liability*, or fields tracking when statute “precludes private suit against...”
    - **Potential Fix:** Consider adding proper_defendants_exceptions or defendant_exemption_context or, if rare, using current proper_defendant_details with standardized guidance.

### D. Evidence-Shielding (“No Retaliatory Evidence”)
- Some statutes bar not only action but also "use of protected disclosure as negative evidence"; ex: can't use protected report as a negative factor in performance review/evaluation, even if no action is taken.
    - This is subtle and rarely captured.
    - **Current Solution:** Closest mapping may be adverse action or legal recognition taxonomy, but it’s not explicit.
    - **Potential Fix:** Add a `no-retaliatory-evidence` term to ws_legal_recognition (or a field under Enforcement/Remedies for "use of disclosure as evidence barred").

### E. Scope-of-Protection: "Catch-all" and Residual Coverage
- Many modern whistleblower laws contain a “catch-all” for disclosures not listed specifically (ex: "including but not limited to..." or "any violation of law/rule/regulation").
    - **Current Taxonomy:** The general-wrongdoing child term exists, but field for "catch-all application" could be made more explicit by a boolean or as a legal_recognition term (e.g., `catch-all-protection`).
    - **Potential Fix:** Optional field, but a term in legal_recognition taxonomy is the simplest and most editorially complete.

### F. Temporal/Procedural Mechanics (Interim/Temporary Relief)
- "Interim reinstatement" etc. appears as a remedy, but...
    - Some statutes allow for **emergency, preliminary, expedited, or interim orders**—often on a different time/trigger logic or with unique standards.
    - **Current Remedy Tax:** `interim-reinstatement` exists.
    - ***But:*** No explicit field for "expedited/interim procedural status" (as opposed to just available as remedy; i.e., was a temporary order available and triggered on lower burden?).
    - **Potential Fix:** Consider adding a boolean field or taxonomy value for "emergency/interim relief procedure available" (distinct from as a possible final remedy).

### G. Retaliation by Third Parties (Associational/”Cat’s Paw” Liabilities)
- **Cat’s paw** and “associational” retaliation (family/friend of whistleblower or someone acting on employer’s behalf) is called out for individuals ("cats-paw-liability", "associates-of-whistleblower" in class taxonomy), but...
    - *Some laws have express provisions for retaliation by outside third parties—contractors, unions, non-employer entities.*
    - **Current Solution:** Proper defendants + protected/excluded class + legal_recognition taxonomy can cover this, but *not as a distinct "third-party/contractor/union" retaliation mapping*.
    - **Potential Fix:** Consider a ws_legal_recognition taxonomy term for `third-party-retaliation` or `contractor-retaliation-liability`.

### H. Remedies: Special/Social Remedies
- **Non-traditional remedies:** Restitution (return of property), nullification of non-disparagements, mental health support, apology/reputation clearing, etc.
    - **Current Solution:** “Non-monetary-relief” is present, “expungement of personnel record” is covered, and "mental-health" is a service for orgs.
    - **Potential Gap:** *Some soft/PR remedies* (e.g., apology or corrective notice, reputation clearing, public posting) have no enumerated taxonomy term.
    - **Potential Fix:** Consider adding “apology/corrective notice/reinstatement of reputation” to ws_remedy (if desired to match corner-case laws).

### I. Regulatory Process Nuance: Rulemaking, Guidance, FAQ, or “Interpretive” Triggers
- Laws occasionally distinguish between protections for complaints made in response to ***statutes, regulations, rules, guidance, policies, FAQs***, and sometimes only statutes/regulations (“law/rule” vs “policy”).
    - **Current Solution:** protected_action_source covers constitutional/statutory/judicial/regulatory/executive.
    - **Potential Gap:** No way to capture "protected if violation of law/rule, but not if just internal company policy/guidance".
    - **Potential Fix:** Sufficient granularity? Consider taxonomy for “company policy” or more granular sources, or at least editorial guidance to use protected_action_source for guidance/policy nuance.

### J. Preemption Detail — Express vs. Implied, “Reverse Preemption”
- You cover preemption direction and details ("federal preempts state," etc.), but some laws have **“reverse preemption”** (state trumps federal in specific circumstance), or “express” vs “implied” preemption logic, or case law on displacement.
    - **Potential Fix:** Might add taxonomy term for “reverse preemption” or expand preemption_direction with express/implied.

### K. Whistleblowing Chain/Scope to External Orgs/Media
- **Scope of allowed disclosure** is covered, but...
    - Some statutes allow/require reporting ONLY to government, others allow media/NGO/public disclosure, and in particular "public interest" defense (i.e., protected only if government disclosure fails/is not effective).
    - **Current Solution:** taxonomy covers this at the disclosure_targets level, with channel_scope and details.
    - **Gap**: *Nuance rarely required for “stepwise” protection (ex: must report internally first, then externally),* but that detail must be editorialized in disclosure_channel_scope and -details.
    - Suggest no new field, but editorial guidance to mark this in channel_scope/context.

### L. Cost/Fee “Caps” vs “Shifting/Absorption” vs “Discretionary/Statutory Caps”
- Fee-shifting rule taxonomy is present, but there are cases where *fees are available but capped, or only a certain party can recover, or multiple shifting rules can apply in series (e.g., agency then court)*.
    - You have “has-limits” and detail overflow, but not a structured way to describe “statutory cap on fees” as distinct from statutory cap on damages.
    - **Potential Fix:** Editorial guidance—fee_shifting_details should always be used to note caps.

### M. Nonbinary/Custom "Standards"
- For evidentiary/causation standards, some statutes use *custom/variable standards* (ex: “substantial evidence as determined by agency”, “preliminary showing” for interim relief), which your "has-details" pattern handles, but only at the detail level; not as a selectable/analytically-tagged taxonomy.
    - **Potential Fix:** You want "has-details", but consider a “custom” taxonomy option to distinguish “specified in details, not standard as listed”.

### N. Whistleblower Definition (Who Qualifies)
- Your protected_class taxonomy is detailed—but on edge, some statutes distinguish between *current*, *former*, *prospective*, *perceived*, and *associated* (proxy) whistleblowers. You've captured these.
    - No meaningful gap.

---

## 3. Recommendations and Best Next Increments

**Your model is robust and future-proofed, but for maximal legal fidelity, consider:**

### 1. Add the following legal_recognition taxonomy terms (with details/context as needed):
   - `anti-gag-provision`
   - `third-party-retaliation`
   - `catch-all-protection`
   - `no-retaliatory-evidence`
   - `reverse-preemption`
   - (If you want full doctrine parity for labor law, a term for interim/procedural relief distinct from remedy.)

### 2. Add (if desired for analytical uniformity):
   - A field or select for `protected_action_status`: (`actual`, `attempted`, `deterred`, etc.) if wanted for high-fidelity data.

### 3. Editorialize:
   - Instruct editors to use *_details/context fields for:
     - Special role immunity/exemption situations (defendants, enforcement, etc.)
     - Preemption logic too complex for direction field (express/implied/conditional)
     - Stepwise or sequential disclosure requirements.
     - Unusual/unique fee-capping logic or “custom/variable” evidentiary standards.

---

## Conclusion

**You have *not* missed any major dimension captured in modern U.S. or comparative whistleblower law—your structure is legally robust and feature-complete. Any "missed nuances" at the margins can be addressed by:**
- Adding a handful of extra `ws_legal_recognition` or `ws_remedy` taxonomy terms (see above), or
- Confirming editors will standardize the use of the (already present) detail/context overflow fields for true edge-cases.

**Your sentinel system (has-details/has-limits) and companion context fields are extremely "future-proof" and will cover any as-yet undiscovered, highly jurisdiction-specific legal nuances.**

---

Absolutely! Here’s a **concrete proposal** for (a) new `ws_legal_recognition` taxonomy terms to cover the final edge legal nuances found in whistleblower law, and (b) a focused editorial checklist for contributors for detail/context fields to ensure no protection features are missed.

---

# 1. Proposed `ws_legal_recognition` Taxonomy Delta

These are new terms to be added to ws_legal_recognition. Terms with *companion context/detail fields* are noted.

```php
[
    // Procedural/Protection Terms
    'anti-gag-provision'         => 'Anti-Gag Provision Recognized',           // + anti_gag_context
    'third-party-retaliation'    => 'Third-Party Retaliation Prohibited',      // + third_party_retaliation_context
    'catch-all-protection'       => 'Catch-All Protection Clause Present',     // + catch_all_protection_context
    'no-retaliatory-evidence'    => 'Retaliatory Evidence Barred',             // + no_retaliatory_evidence_context
    'reverse-preemption'         => 'Reverse Preemption Applies',              // + preemption_details

    // Optional (depending on field coverage elsewhere)
    'emergency-relief-procedure' => 'Emergency/Expedited Relief Procedure',    // + emergency_relief_context
    'custom-standard'            => 'Custom Evidentiary or Causation Standard',// + custom_standard_context

    // Editorial/Complex Preemption
    'express-preemption'         => 'Express Preemption',                      // + preemption_details
    'implied-preemption'         => 'Implied Preemption',                      // + preemption_details
]
```

**Term Descriptions (suggested for contributor documentation):**
- **anti-gag-provision:** Applies where the law directly prohibits employer policies or practices that restrict employee disclosure or chill whistleblowing, not limited to NDA or confidentiality contract clauses.
- **third-party-retaliation:** Use where retaliation by non-employer parties (e.g., contractors, unions, third parties) is expressly covered or prohibited.
- **catch-all-protection:** For statutes/doctrine that uses open-text “including but not limited to,” etc.—offering protection for any disclosure not otherwise specified.
- **no-retaliatory-evidence:** Use if the statute/doctrine bars use of the protected disclosure as negative evidence for employment action, even absent retaliation.
- **reverse-preemption:** Use if state law is carved out from federal preemption or explicitly overrides it under certain circumstances.
- **emergency-relief-procedure:** When expedited/preliminary relief is available (distinct from mere presence of interim remedy).
- **custom-standard:** Use for unique evidentiary/causation burdens not matching taxonomy options; details required.
- **express/‌implied-preemption:** For explicit coverage of preemption doctrine being statutory/express or judicial/implied.

---

# 2. Editorial Checklist for Detail/Context Fields

### Contributors must use *_details or *_context fields to capture:

- **Special role immunity/exemption:**  
  If law excludes certain types of defendants (e.g., compliance officers/auditors), explicitly record this in proper_defendant_details.
- **Fee caps, shifting variants, or partial fee award rules:**  
  If attorneys' fees/costs are capped, only partly recoverable, or available at an unusual phase or only on a subset of outcomes, describe this in fee_shifting_details.
- **Sequential or conditional disclosure requirements:**  
  If protection depends on reporting in a particular order (internal before agency, etc.), document fully in disclosure_channel_details/context.
- **Stepwise/expedited relief:**  
  If relief prior to final adjudication is available (other than as final remedy), or on special “probable cause” or preponderance-of-belief, describe in remedy_details (for relief) or in a new emergency_relief_context field as per above.
- **Custom evidentiary/causation standards:**  
  If statute/doctrine departs from taxonomy standards ("substantial evidence as determined by agency", “preliminary showing”, etc.), describe exhaustively in standard_details or causation_standard_details.
- **Ambiguous, variable, or “as determined by” legal triggers:**  
  Any place the law is purposely vague, uses “as determined by [agency/court]”, or has contingent triggers—describe after primary taxonomy choices, using *_details/‌*_context.
- **Unique or soft remedies:**  
  If a remedy is granted that cannot be fit in existing taxonomy slugs (apology, corrective notice, public posting, etc.)—record in remedy_details.

---

# 3. Example Additions to Taxonomy Seeder (PHP Snippet)

You can add the new terms to your ws_seed_legal_recognition_taxonomy:

```php
function ws_seed_legal_recognition_taxonomy() {
    $taxonomy = 'ws_legal_recognition';
    $terms    = [
        // ...existing terms...

        // Additional Nuance Terms
        'anti-gag-provision'         => 'Anti-Gag Provision Recognized',
        'third-party-retaliation'    => 'Third-Party Retaliation Prohibited',
        'catch-all-protection'       => 'Catch-All Protection Clause Present',
        'no-retaliatory-evidence'    => 'Retaliatory Evidence Barred',
        'reverse-preemption'         => 'Reverse Preemption Applies',
        'emergency-relief-procedure' => 'Emergency/Expedited Relief Procedure',
        'custom-standard'            => 'Custom Evidentiary or Causation Standard',
        'express-preemption'         => 'Express Preemption',
        'implied-preemption'         => 'Implied Preemption',
    ];
    foreach ( $terms as $slug => $name ) {
        if ( ! term_exists( $slug, $taxonomy ) ) {
            wp_insert_term( $name, $taxonomy, [ 'slug' => $slug ] );
        }
    }
}
```

---

# 4. Communication for Editors

**Add a note in the contributor docs** (or ACF field descriptions) as follows:
> Please consult the detail/context checklist before marking a record as "complete." If any edge-case or ambiguous legal features are present, describe them in relevant "details" or "context" overflow fields, and add the appropriate `ws_legal_recognition` taxonomy term if a new nuance is present. If you encounter a recurring legal nuance not covered, propose a new taxonomy term for inclusion.

---

## Summary Table for Easy Reference

| Nuance                               | Field/Taxonomy                     | Context/Details Needed? |
|-------------------------------------- |------------------------------------|------------------------|
| Anti-gag/prohibition on chilling      | ws_legal_recognition               | Yes (anti_gag_context) |
| Third-party retaliation/contractors   | ws_legal_recognition               | Yes (third_party_retaliation_context) |
| "Catch-all" coverage                  | ws_legal_recognition               | Yes                   |
| Bar on use as negative evidence       | ws_legal_recognition               | Yes                   |
| Reverse preemption                    | ws_legal_recognition               | Yes (preemption_details) |
| Emergency/expedited relief            | ws_legal_recognition, remedy       | Yes                   |
| Custom standards                      | ws_legal_recognition, \*_details   | Yes                   |
| Special defendant immunities          | proper_defendant_details           | Yes                   |
| Fee cap/partial shifting              | fee_shifting_details               | Yes                   |
| Unique/soft remedies                  | remedy_details                     | Yes                   |

---

## Final Notes

- You may want to review your editorial style guide to ensure that detail/context fields are always used for ambiguous or edge-case situations, and that the taxonomy stays in sync with emerging legal doctrine.
- This structure will future-proof the data model; the "overflow" pattern plus enhanced taxonomy ensures all current and forthcoming legal nuance can be captured systematically.

---
