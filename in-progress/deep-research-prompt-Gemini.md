---

## The Research Directive: Whistleblower Assistance Verification & Expansion

### Phase 1: Identity & Accessibility Audit

For each organization listed in the provided `matrix-assist-orgs.php` seeder, perform a live web verification to confirm the following:

* 
**Intake Integrity:** Does the `intake_url` lead to a page where a person can request individual assistance, legal review, or peer support?. If the page only allows for "Submitting a Tip" or "Reporting a Crime," mark the field as empty and search for a true assistance entry point.


* 
**Link Status:** Verify the `official_homepage_url` and `contact_url` return a 200 OK status. Confirm the organization’s name appears prominently on the destination page to avoid "bad handoffs".


* **Contact Accuracy:** Verify the `mailing_address` and `phones` are current. Discard any "dead" numbers or closed offices.



### Phase 2: Security & Privacy Hardening

Examine the technical contact methods for each organization to satisfy the **Maya** persona's need for safety:

* 
**Secure Channels:** Search for specific mention of **Signal**, **ProtonMail**, **SecureDrop**, or **Tutanota**.


* 
**HTTPS Gatekeeping:** Do not mark `has_secure_channel` as "yes" for standard HTTPS web forms.


* 
**Anonymity:** Confirm if a "pre-consult" is possible without the user providing a legal name or employer details.



### Phase 3: Cost Model & Eligibility Transparency

To assist the **James** persona in finding a fast path to help, define the "barrier to entry":

* 
**Cost Clarity:** Distinguish between `free`, `pro-bono`, and `contingency` models. Use `unclear` only if the site provides no fee documentation.


* 
**Income Gates:** Identify if the organization requires proof of low-income status (`income_eligibility_required`).



### Phase 4: Discovery of Missing Nationwide Records

Identify additional **nationwide** or **federal-scope** organizations not found in the current matrix. Focus on niche sectors such as:

* 
**Nuclear and Energy Safety**.


* 
**Intelligence Community Reporting**.


* 
**Scientific Integrity and Food/Drug Safety**.

---