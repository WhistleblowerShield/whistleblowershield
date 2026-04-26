# Assist Org Schema Reboot — v4.0 "Burn the Boats"

**Guiding idea:** the current `ws-assist-org` ACF group was built for law firms with a phone number. Whistleblower assistance in the real world includes a Utah peer-support circle that only speaks French, meets clothing-optional, and will only intake via Signal on Tuesdays. If the schema cannot describe that without lying, the schema is wrong.

This proposal throws away every field name, tab, and meta key from `acf-assist-orgs.php`. It keeps the taxonomy *pattern* from the legal-record draft, steals its naming discipline, then bends it on purpose for assist orgs.

We keep: `ws_jurisdiction`, `ws_disclosure_type`, `ws_process_type`, `ws_case_stage`, `ws_language`, `ws_employment_sector`. Everything else gets rebuilt.

---

## 1. New taxonomy family

We add six new flat/hierarchical tables. All use kebab-case slugs, all get `has-details` sentinel.

### 1.1 `ws_intake_method` (how you first reach them)
- web-form, phone-hotline, phone-intake, email-intake, signal, whatsapp, telegram, protonmail, postal-mail, walk-in, referral-only, carrier-pigeon, smoke-signal, has-details

### 1.2 `ws_support_service` (what they actually do beyond "legal")
- legal-representation, legal-consultation, document-review, court-accompaniment
- peer-support, mental-health-counseling, crisis-counseling, trauma-informed-care
- financial-assistance, housing-assistance, relocation-support, food-assistance
- career-counseling, job-placement, resume-help
- media-training, advocacy-coaching, policy-advocacy
- digital-security-help, translation-interpretation, childcare, transportation-assistance, clothing-assistance
- secure-drop-hosting, has-details

### 1.3 `ws_accessibility_feature`
- wheelchair-accessible, asl-interpreter, video-relay, tty, braille-materials
- sensory-friendly, low-stimulation, service-animals-allowed
- has-details

### 1.4 `ws_environmental_sensitivity` (the fun one)
- fragrance-free, sober-space, smoke-free, allergen-cats, allergen-dogs
- low-light, quiet-space
- clothing-optional, nudist-friendly, clothing-required
- has-details

### 1.5 `ws_identity_affirming`
- lgbtq-affirming, trans-affirming, bipoc-centered, indigenous-centered
- veteran-centered, disability-centered, faith-based, secular
- women-centered, immigrant-centered, has-details

### 1.6 `ws_operational_constraint`
- business-hours-only, after-hours-emergency, weekends-only, 24-7
- appointment-only, walk-in-only, remote-only, in-person-only
- lunar-cycle, seasonal, has-details

**Additions to existing tables:**
- `ws_language`: add `american-sign-language`, keep `additional` sentinel
- `ws_aorg_service`: deprecate — migrate to `ws_support_service`
- `ws_protected_class`: migrate to new `ws_worker_status` from legal-record proposal (current-employee, former-employee, applicant, family-member, associate, etc.)

---

## 2. Field architecture — all new names

We follow the legal-record draft rules, then break two:
- meta names are snake_case, no `ws_aorg_` prefix in the draft
- booleans are `has_*` or `is_*`
- multi-value = plural noun
- companion fields = `*_details` or `*_context`
- **New rule (break):** "brother fields" share a stem but live in different tabs and must stay in sync via hook. "Sister fields" are conditional companions. "Cousins" are cross-CPT references that inherit validation.

### Tab: Identity
- `official_name` (required)
- `common_name`
- `alternate_names` (freetext, pipe-separated)
- `parent_organization_id` (post object, ws-assist-org)
- `founding_year` (integer)
- `legal_status` — single-select: nonprofit-501c3 | nonprofit-other | fiscally-sponsored | for-profit | government | tribal | unincorporated | has-details
- `legal_status_details` (sister, conditional)
- `ein_tax_id`
- `mission_summary` (wysiwyg, 3-5 sentences)

### Tab: Whistleblower Fit
- `whistleblower_focus_level` — integer 0-3 (0=unclear, 3=broad)
- `whistleblower_focus_context`
- `disclosure_types` — taxonomy ws_disclosure_type, multi
- `worker_statuses` — taxonomy ws_worker_status, multi
- `employment_sectors` — taxonomy ws_employment_sector, multi
- `case_stages` — taxonomy ws_case_stage, multi
- `process_types` — taxonomy ws_process_type, multi
- `has_whistleblower_details`
- `whistleblower_details` (sister)

### Tab: Services
- `support_services` — taxonomy ws_support_service, multi
- `has_support_details`
- `support_details` (sister)
- `has_legal_services`
- `legal_services_context` (brother to `has_attorneys` in Security tab — hook keeps them in sync)

### Tab: Eligibility & Culture
- `has_income_limits`
- `income_limit_details` (sister)
- `identity_affirming_features` — taxonomy ws_identity_affirming, multi
- `identity_affirming_details`
- `languages_supported` — taxonomy ws_language, multi
- `has_additional_languages`
- `languages_additional_details` (sister)

### Tab: Access & Environment (this is where Utah lives)
- `accessibility_features` — taxonomy ws_accessibility_feature, multi
- `accessibility_details`
- `environmental_sensitivities` — taxonomy ws_environmental_sensitivity, multi
- `environmental_details`
- `clothing_policy` — single-select: clothing-required | clothing-optional | nudist-friendly | has-details (brother to environmental_sensitivities)
- `clothing_policy_details` (sister)
- `has_trauma_informed_space`
- `trauma_informed_details`

### Tab: Intake
- `intake_methods` — taxonomy ws_intake_method, multi
- `has_intake_details`
- `intake_details` (sister)
- `intake_url`
- `intake_phone` (repeater simplified to freetext with type prefix)
- `intake_email`
- `has_anonymous_pre_consult` — ternary: yes | no | unclear
- `anonymous_details`
- `has_appointment_required`
- `appointment_wait_days` (integer, brother to operational constraints)
- `operational_constraints` — taxonomy ws_operational_constraint, multi
- `operational_details`

### Tab: Security & Capacity
- `has_secure_channel`
- `secure_tools` — multi-select: signal | protonmail | tutanota | wire | keybase | secure-drop | other
- `secure_tools_other_details` (sister)
- `has_attorneys` — ternary: yes | no | unclear (cousin to legal_services_context)
- `attorney_details`
- `data_retention_policy` — freetext
- `confidentiality_level` — single-select: standard | attorney-client | medical-hipaa | journalist-shield | has-details
- `confidentiality_details`

### Tab: Cost
- `cost_models` — taxonomy ws_aorg_cost_model, multi (free, pro-bono, sliding-scale, contingency, fee-for-service, unclear)
- `has_sliding_scale`
- `sliding_scale_details` (sister)
- `has_financial_assistance`
- `financial_assistance_details`

### Tab: Contact & Proof
- `official_homepage_url`
- `homepage_url_status` — verified | redirects | unverified | dead
- `mailing_addresses` (freetext, || separator)
- `legitimacy_urls` (repeater of url + source type)
- `last_reviewed_date`
- `review_notes`

---

## 3. Sentinel, companion, and family rules

Borrowed from legal-record draft, then extended:

- **Sentinels:** `has-details`, `has-limits`, `has-phases`, `see-details`, `see-context`, `unclear`
- **Sisters:** `*_details` appears only when parent boolean or sentinel is true. Always freetext.
- **Brothers:** fields that must mirror each other across tabs. Example: `has_legal_services` (Services tab) and `has_attorneys` (Security tab). A `save_post` hook copies yes→yes, no→unclear.
- **Cousins:** fields that reference another CPT. Example: `parent_organization_id` must be a ws-assist-org with `has_nationwide_service = true`. Validation hook blocks save otherwise.
- **New rule — break the prefix rule:** for assist orgs, we drop the `ws_aorg_` infix in the database. Store as `official_name`, not `ws_aorg_official_name`. The query layer will add the prefix back on output. This matches the legal-record canonical draft and cuts migration pain.

---

## 4. Edge case mapping — Utah French clothing-optional

| Datapoint | Field |
|---|---|
| Located in Utah only | `jurisdictions` = |
| Speaks only French | `languages_supported` = (no english) |
| Strongly clothing optional | `environmental_sensitivities` = [clothing-optional], `clothing_policy` = clothing-optional, `clothing_policy_details` = "Meetings held clothing-optional by organizational charter; intake via Signal only" |
| Intake via Signal Tuesdays | `intake_methods` =, `operational_constraints` = [appointment-only], `operational_details` = "Signal intake Tuesdays 18:00-20:00 MT" |
| Peer support only, no lawyers | `support_services` = [peer-support], `has_legal_services` = false, `has_attorneys` = no |
[ut][french][signal]

No field is forced. No "other" bucket abuse. Every quirk has a home.

---

## 5. What we deliberately break

1. **We stop using `ws_aorg_service`.** Migrate to `ws_support_service`. Old data maps 1:1 with a hook on first edit.
2. **We allow fun taxonomy terms.** `carrier-pigeon`, `smoke-signal`, `lunar-cycle`, `nudist-friendly` are real accessibility and operational constraints for marginalized communities. They stay.
3. **We permit empty required fields in draft.** The ingest pipeline will set `whistleblower_focus_level = 0` and flag for human review instead of rejecting the record. Omission beats hallucination.

---

## 6. Migration path

1. Rename current `acf-assist-orgs.php` to `.txt`. Create new file with v4.0 field group using names above.
2. Register six new taxonomies in `register-taxonomies.php`, bump version to 3.16.0, seed with terms listed.
3. Run one-time backfill script: copy `ws_aorg_official_name` → `official_name`, `ws_aorg_common_name` → `common_name`, etc. Keep old meta for 30 days.
4. Update `query-agencies.php` (actually query-assist-org) to strip prefixes on read — already standard in query layer.

This schema can describe a national legal aid clinic, a Discord-based peer support group for game-industry whistleblowers, and that French-speaking, clothing-optional collective in Moab, without special casing any of them.

Want the actual PHP arrays for the six new taxonomies and the ACF field group stub next?