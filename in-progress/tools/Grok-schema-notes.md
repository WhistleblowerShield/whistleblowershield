Yes, there are several datapoints that the current schema doesn't fully capture cleanly, even though they are relevant and potentially valuable for users (Maya or James) in crisis. These often end up in `_review_notes`, `additional_services`, or as sentinels like `has-details` — which is exactly what the prompt intends as a flag for human review.

Here are the most notable ones I observed while researching and updating the 8 orgs so far (with fresh checks on the latest 4):

### 1. **Peer Support & Trauma-Informed/Mental Health Services** (Biggest Gap)
- **Whistleblowers of America (WoA)** stands out strongly here.
  - They offer structured **evidence-based peer mentoring** (Peer 2 Peer Support, Fellowship Circle, Associate Membership), a "Whistleblower Mental Health Advisory Consortium", trauma-informed approaches to retaliation stress, and even a "Whistleblower Protection Advocate certification".
  - They developed their own **taxonomy for toxic retaliation tactics** (to help clinicians/attorneys document impact).
  - Schema only has `peer-support` and `mental-health` under `services_provided`. This doesn't fully convey the depth (structured matching, strategic plans, community programs, training/certification).
  - Worth capturing: In `_review_notes` or expanding `additional_services` with something like "structured trauma-informed peer mentoring program with mental health consortium and retaliation taxonomy".

- Signals Network (from previous batch) also has psychological counseling + financial grants, but it's partially covered under `additional_services`.

### 2. **Educational/Resources + Training Components**
- **The Anti-Fraud Coalition (TAF)**: Maintains extensive resources (fact sheets on FCA/SEC programs, ROI reports, "Fraud By The Numbers", podcast with whistleblowers, amicus briefs). This goes beyond pure "referral" or "advocacy".
- POGO and PEER also produce investigative reports, toolkits, and whistleblower resources that help users understand protections — more than just "media" or "advocacy".
- Schema doesn't have a clean slug for general education, toolkits, or training programs for whistleblowers/advocates.

### 3. **Secure/Anonymous Tip Lines vs. Personal Assistance Distinction**
- **POGO**: Has a prominent "Send Us a Tip" page with Signal + secure email. This is excellent for anonymous evidence drop but **not** personal case consultation/intake (per your Intake Commitment Rule). We correctly left `intake_url` empty and noted it.
- Many orgs blur "report a tip to us" with "get help for your situation". The schema handles this via the rule + `_review_notes`, but it's a recurring friction point worth tracking.

### 4. **Eligibility Nuances Beyond Income**
- **PEER**: Explicitly limited to **current or former public/government employees** with environmental/public health/scientific integrity issues. This is stronger than generic "eligibility_notes".
- Some orgs imply sector or issue-specific filters (e.g., environmental only for PEER, fraud/qui tam for TAF) that don't fit neatly into `protected_classes` or `employment_sectors` without `has-details`.

### 5. **Other Minor/Recurring Items**
- **Languages**: Almost all are English-only with no explicit claims for others (even Spanish). Rarely any interpreter mentions.
- **Financial Assistance/Grants**: Signals Network has a Whistleblower Protection Fund (grants). Schema has `financial` under services, but depth varies.
- **Certification/Training Programs**: WoA's advocate certification and workplace training aren't well captured.
- **Community/Membership Models**: WoA's Associate Membership and Fellowship Circle create ongoing support communities.
- **Podcast/Media as Support Tool**: TAF and WoA use podcasts for education and community building.

### Recommendation
These gaps are worth capturing systematically in `_review_notes` (for human reviewers) or a lightweight expansion of `additional_services`. For example:
- Add a short phrase in `additional_services` for standout features like "trauma-informed peer mentoring program with mental health consortium".
- Or maintain a running "schema enhancement suggestions" list based on batches (e.g., propose new optional fields like `peer_mental_health_details` or `educational_resources` in a future schema version).

The current schema is already quite robust for legal/advocacy pathways, but whistleblower support often involves **psychosocial, community, and educational** elements that feel secondary in the taxonomy.

Would you like me to:
- Update the next 4 orgs (e.g., NELP, NELA, LSC Find Legal Aid, NLADA)?
- Or first revise any of the previous 8 records to better highlight these uncaptured elements in `_review_notes`/`additional_services`?

Just say the word.