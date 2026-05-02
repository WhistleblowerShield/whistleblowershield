# Assist-Org Record ACF Canonical Field Draft

Purpose: unified, prefix-free field set for the `ws-assist-org` CPT, replacing
the legacy `acf-assist-orgs.php` field definitions. Drives the directory
matching, filter cascade, ingest pipeline, and public-facing render layer.

---

## Naming Rules Applied

- No CPT infix or storage prefix in this draft (no `ws_aorg_*`).
- `snake_case` only.
- Booleans use `has_*` or `is_*`.
- Single-value datapoints use singular nouns.
- Multi-value arrays use plural nouns.
- `*_details` — freetext companion, conditional on `has_*` is true.
  `*_details` may have sister fields; no naming convention applies to sister fields.
- `*_context` — freetext companion, conditional on trigger field when specified
  value, values, or any non-empty value depending on trigger requirements.
  Never triggered by `has_*` — that pattern uses `*_details`.
- `*_url` — URL field; `*_url_status` is its verification signal companion.
- `*_url_date` — date companion to `*_url_status` when status is `verified`.
- Derived values need hooks on fill.
- Internal-only fields prefixed with underscore in meta key name.

### Sentinel Values

- `has-details` — sentinel in taxonomy arrays; triggers `*_details` companion
  via `has_*` boolean pattern or dynamic conditional.
- `other` — sentinel in select/repeater type fields; triggers `*_context`
  companion or dedicated freetext description.
- `unclear` — ternary fallback; signals research inconclusive, not that field
  was skipped. Always expected, never lazy.
- `additional` — sentinel in `languages` taxonomy; triggers `languages_additional`.

---

## Attached Plain-English

Assist-org records attach to the shared `group_plain_english_metadata` workflow
group. Plain-English summary is the editorial narrative for the record — not
captured in fields below.

---

## Common Fields (Apply To All Assist-Org Records)

Field order reflects logical editorial workflow within each tab.

---

### Identity Tab

Core identifiers and classification. `official_name` is the authoritative
data-layer source; post_title mirrors it at ingest time.

- `official_name`            — (ESSENTIAL; text; full official name as it appears on homepage
                               or governing documents; post_title mirrors this at ingest)
- `common_name`              — (OPTIONAL; text; widely used shorthand or acronym e.g. "GAP", "NWC")
- `org_type`                 — (taxonomy: `ws_aorg_type`; radio single-select; required)
- `general_description`      — (textarea; 3-5 sentence plain-English overview of mission,
                               focus areas, and typical whistleblower support)
- `logo`                     — (image; PNG or SVG preferred; max 1MB)
- `last_reviewed`            — (date; manually updated each time record is verified for accuracy)
- `legitimacy_url`           — (OPTIONAL; URL to third-party verification: GuideStar,
                               Charity Navigator, IRS Form 990, state bar directory, etc.)

---

### Scope Tab

Defines who this organization can help and how. These fields drive the directory
filter cascade. An editor filling this tab determines whether Maya or James
ever sees this record.

#### Geographic Coverage

- `is_nationwide`            — (bool; true when org serves all 57 jurisdictions;
                               drives directory nationwide tier)
- `jurisdictions`            — (taxonomy: jurisdiction; multi-select; conditional on
                               `is_nationwide` is false — specific jurisdictions served)
- `has_limited_scope`        — (bool; conditional on `is_nationwide` is false;
                               true when coverage is sub-jurisdictional: city, county, region)
- `community_scope`          — (freetext sister to `has_limited_scope`; conditional on
                               `has_limited_scope` is true; describe geographic footprint
                               e.g. "San Francisco Bay Area", "Los Angeles County")
- `jurisdiction_exceptions`  — (OPTIONAL; freetext; coverage gaps or exclusions
                               e.g. "nationwide except Texas")

#### Whistleblower Focus

- `whistleblower_scope`      — (integer 0–3; required; base score multiplier for directory ranking:
                               0=unclear/flags record, 1=tangential, 2=significant focus,
                               3=primary mission)
- `whistleblower_note`       — (freetext; verbatim quote from org's site describing whistleblower
                               mission; or reason for inclusion when scope is 0)
- `nationwide_example`       — (OPTIONAL; verbatim quote showing multi-jurisdictional scope;
                               required for nationwide_only ingest batches)

#### Service Taxonomy

- `protected_disclosures`    — (taxonomy: `ws_protected_disclosure`; multi-select; all applicable
                               misconduct categories this org has experience assisting with)
- `protected_actions`        — (OPTIONAL; taxonomy: `ws_protected_action`; multi-select;
                               specific protected activity types this org supports)
- `protected_classes`        — (taxonomy: `ws_protected_class`; multi-select;
                               all worker classifications served; fallback: `has-details`)
- `protected_class_details`  — (conditional on `protected_classes` includes `has-details`;
                               describe classifications served not covered by taxonomy)
- `employment_sectors`       — (OPTIONAL; taxonomy: `ws_employment_sector`; multi-select;
                               sectors served; omit when all sectors accepted)
- `disclosure_targets`       — (taxonomy: `ws_disclosure_target`; multi-select;
                               reporting channels this org helps whistleblowers navigate;
                               fallback: `has-details`)
- `disclosure_target_details` — (conditional on `disclosure_targets` includes `has-details`)
- `case_stages`              — (taxonomy: `ws_case_stage`; multi-select;
                               stages where this org is most useful; fallback: `has-details`)
- `case_stage_details`       — (conditional on `case_stages` includes `has-details`)
- `process_types`            — (OPTIONAL; taxonomy: `ws_process_type`; multi-select;
                               process channels this org helps navigate)

#### Services

- `services`                 — (taxonomy: `ws_aorg_service`; multi-select; required;
                               all services provided to whistleblowers; fallback: `unclear`)
- `additional_services`      — (conditional on `services` includes `additional`;
                               describe services not covered by taxonomy)

---

### Contact & Intake Tab

How a whistleblower reaches this organization. `website_url` is required.
All other fields are optional — not all organizations publish every channel.
Fields ordered: primary URLs → phone/email → address → secure channel → languages.

#### Primary URLs

- `website_url`              — (URL; required; organization's primary public website)
- `website_url_status`       — (single-select: `verified`|`redirects`|`unverified`|`dead`;
                               fallback: `unverified`)
- `website_url_date`         — (OPTIONAL; date YYYY-MM-DD; sister to `website_url_status`;
                               omit unless status is `verified`)
- `intake_url`               — (OPTIONAL; URL; direct link to personal assistance request,
                               case submission, or legal review — NOT tip lines or leak drops)
- `intake_url_status`        — (OPTIONAL; single-select: `verified`|`redirects`|`unverified`|`dead`)
- `intake_url_date`          — (OPTIONAL; date YYYY-MM-DD; sister to `intake_url_status`;
                               omit unless status is `verified`)
- `contact_url`              — (OPTIONAL; URL; general contact page when separate from intake)

#### Phone & Email

- `phones`                   — (OPTIONAL; repeater: `phone_type` + `phone_number`;
                               types: `hotline`|`intake`|`headquarters`|`regional`|`tty`|`fax`|`other`;
                               describe `other` in `contact_context`)
- `emails`                   — (OPTIONAL; repeater: `email_type` + `email_address`;
                               types: `intake`|`general`|`legal`|`media`|`support`|`secure`|`other`;
                               describe `other` in `contact_context`)
- `contact_context`          — (OPTIONAL; freetext; describe any `other` phone or email types,
                               or capture contact nuance not covered by the repeater structure)
- `mailing_address`          — (OPTIONAL; textarea; physical or mailing address;
                               use `||` separator for multiple addresses)

#### Secure Channel

- `has_secure_channel`       — (ternary: `yes`|`no`|`unclear`; `yes` requires a dedicated
                               encrypted contact tool: Signal, ProtonMail, Tutanota, Wire,
                               Keybase; standard HTTPS forms or SecureDrop do not qualify)
- `secure_tool`              — (sister to `secure_details`; conditional on `has_secure_channel`
                               is `yes`; single-select: `Signal`|`ProtonMail`|`Tutanota`|
                               `Wire`|`Keybase`|`other`)
- `secure_tool_other`        — (sister to `secure_details`; conditional on `secure_tool`
                               is `other`; freetext; use `||` separator for multiple tools)
- `secure_url`               — (sister to `secure_details`; conditional on `has_secure_channel`
                               is `yes`; URL to secure contact method or instruction page)
- `secure_details`           — (conditional on `has_secure_channel` is `yes`;
                               describe the secure channel, any setup requirements, or nuance)
- `anonymous_pre_consult`    — (ternary: `yes`|`no`|`unclear`; whether meaningful assistance
                               is possible without identity disclosure; SecureDrop qualifies)

#### Languages

- `languages`                — (taxonomy: `ws_language`; multi-select; languages this org
                               can support; include `english` if site is in English;
                               use `additional` when other languages are available)
- `languages_additional`     — (conditional on `languages` includes `additional`;
                               freetext; list additional languages comma-separated)

---

### Eligibility & Cost Tab

Critical for laypeople assessing whether this org can realistically help them.
Cost model and income limits are top concerns for financially stressed whistleblowers.

- `cost_models`              — (taxonomy: `ws_aorg_cost_model`; multi-select; required;
                               all cost models that apply to whistleblower services here;
                               fallback: `unclear`)
- `has_income_limit`         — (bool; true when income or financial eligibility is required)
- `income_limit_details`     — (conditional on `has_income_limit`; describe thresholds
                               e.g. "Below 200% of federal poverty level")
- `has_attorneys`            — (ternary: `yes`|`no`|`unclear`; `yes` when licensed attorneys
                               available for intake or representation)
- `accepts_anonymous`        — (ternary: `yes`|`no`|`unclear`; whether org can meaningfully
                               assist without client identity disclosure)
- `eligibility_notes`        — (OPTIONAL; freetext; non-income constraints: case type
                               restrictions, employer size thresholds, union membership,
                               geographic limits, or sector-specific requirements)

---

### Credentials Tab

Helps laypeople assess whether this org provides reliable legal guidance vs.
general advocacy support. Informs directory trust signals.

- `has_licensed_attorneys`   — (bool; true when org employs licensed attorneys who can
                               provide formal legal advice and representation)
- `bar_states`               — (OPTIONAL; text; states where org's attorneys are bar-admitted
                               e.g. "CA, NY, DC, Federal")
- `accreditation`            — (OPTIONAL; text; professional accreditation or certifications
                               e.g. "ABA-accredited", "NQAP member", "DOJ-recognized")

---

### Internal Contact Tab

Private operator metadata for relationship building and outreach continuity.
Not surfaced in any public output. All fields prefixed with underscore in meta.

- `_contact_name`            — (text; primary relationship contact at the organization)
- `_contact_role`            — (text; role or title for outreach context)
- `_contact_email`           — (email; direct working contact email)
- `_contact_phone`           — (text; direct phone or extension)
- `_last_contacted`          — (date; most recent direct outreach date)
- `_relationship_notes`      — (textarea; concise factual notes for relationship continuity)
- `_outreach_status`         — (single-select: `not-contacted`|`outreach-sent`|`in-conversation`|
                               `partner`|`declined`|`unresponsive`; tracks org outreach pipeline)
- `_crowdfunding_tier`       — (OPTIONAL; text; Open Collective jurisdiction funding tier
                               this org is associated with, when applicable)

---

### Hidden Fields (no tab; prefixed with underscore in meta key)

- `_id`                      — (generated by ingest tool or matrix seeder; ingest deduplication key)

---

## Rename Normalization (Legacy → Canonical)

Only legacy fields that violate target naming conventions or are redundant.

- `ws_aorg_internal_id`              → `_id`
- `ws_aorg_official_name`            → `official_name`
- `ws_aorg_type`                     → `org_type`
- `ws_aorg_description`              → `general_description`
- `ws_aorg_common_name`              → `common_name`
- `ws_aorg_logo`                     → `logo`
- `ws_aorg_serves_nationwide`        → `is_nationwide`
- `ws_aorg_whistleblower_scope`      → `whistleblower_scope`
- `ws_aorg_whistleblower_scope_details` → `whistleblower_note` (freetext note,
                                          not a `*_details` companion — renamed for clarity)
- `ws_aorg_has_limited_scope`        → `has_limited_scope`
- `ws_aorg_jurisdictions`            → `jurisdictions`
- `ws_aorg_community_scope`          → `community_scope`
- `ws_aorg_protected_disclosures`    → `protected_disclosures`
- `ws_aorg_disclosure_targets`       → `disclosure_targets`
- `ws_aorg_disclosure_target_details`→ `disclosure_target_details`
- `ws_aorg_case_stages`              → `case_stages`
- `ws_aorg_case_stage_details`       → `case_stage_details`
- `ws_aorg_process_types`            → `process_types`
- `ws_aorg_services`                 → `services`
- `ws_aorg_additional_services`      → `additional_services`
- `ws_aorg_employment_sectors`       → `employment_sectors`
- `ws_aorg_protected_classes`        → `protected_classes`
- `ws_aorg_protected_class_details`  → `protected_class_details`
- `ws_aorg_website_url`              → `website_url`
- `ws_aorg_intake_url`               → `intake_url`
- `ws_aorg_contact_url`              → `contact_url`
- `ws_aorg_phones`                   → `phones`
- `ws_aorg_emails`                   → `emails`
- `ws_aorg_mailing_address`          → `mailing_address`
- `ws_aorg_has_secure_channel`       → `has_secure_channel`
- `ws_aorg_secure_contact_url`       → `secure_url`
- `ws_aorg_secure_contact_tool`      → `secure_tool`
- `ws_aorg_secure_contact_tool_other`→ `secure_tool_other`
- `ws_aorg_languages`                → `languages`
- `ws_aorg_additional_languages`     → `languages_additional`
- `ws_aorg_cost_models`              → `cost_models`
- `ws_aorg_has_income_limit`         → `has_income_limit`
- `ws_aorg_has_income_limit_details` → `income_limit_details`
- `ws_aorg_accepts_anonymous`        → `accepts_anonymous`
- `ws_aorg_eligibility_details`      → `eligibility_notes`
- `ws_aorg_licensed_attorneys`       → `has_licensed_attorneys`
- `ws_aorg_accreditation`            → `accreditation`
- `ws_aorg_bar_states`               → `bar_states`
- `ws_aorg_legitimacy_url`           → `legitimacy_url`
- `ws_aorg_last_reviewed`            → `last_reviewed`
- `_ws_aorg_internal_contact_name`   → `_contact_name`
- `_ws_aorg_internal_contact_role`   → `_contact_role`
- `_ws_aorg_internal_contact_email`  → `_contact_email`
- `_ws_aorg_internal_contact_phone`  → `_contact_phone`
- `_ws_aorg_internal_last_contacted` → `_last_contacted`
- `_ws_aorg_internal_relationship_notes` → `_relationship_notes`

---

## New Fields (Not in Legacy ACF)

Fields added in this canonical overhaul that were not in `acf-assist-orgs.php`.

### Identity Tab
- `last_reviewed`             — was in Credentials tab legacy; moved to Identity as
                                a top-level editorial signal
- `legitimacy_url`            — was in Credentials tab legacy; moved to Identity

### Scope Tab
- `protected_actions`         — new; `ws_protected_action` taxonomy; specific protected
                                activity types the org supports (optional, not always
                                applicable to assist-org context)
- `nationwide_example`        — new; verbatim quote for nationwide scope verification;
                                required for nationwide ingest batches

### Contact & Intake Tab
- `website_url_status`        — new; verification signal for `website_url`
- `website_url_date`          — new; date companion to `website_url_status`
- `intake_url_status`         — new; verification signal for `intake_url`
- `intake_url_date`           — new; date companion to `intake_url_status`
- `contact_context`           — new; freetext for `other` phone/email types and
                                contact nuance not captured by repeater structure
- `anonymous_pre_consult`     — was `ws_aorg_accepts_anonymous` in Eligibility tab
                                legacy; moved here as it's a contact-layer concern
- `secure_details`            — new; freetext companion to the secure channel cluster;
                                describes setup requirements and nuance

### Eligibility & Cost Tab
- `has_attorneys`             — was `has_attorneys (ternary)` in prompt schema only;
                                now promoted to a proper ACF field
- `accepts_anonymous`         — new copy of `anonymous_pre_consult` as ternary in
                                Eligibility context; the two fields serve different
                                editorial purposes:
                                `anonymous_pre_consult` (Contact tab) = can secure channel
                                be used without identity?
                                `accepts_anonymous` (Eligibility tab) = can the org
                                provide meaningful assistance without knowing who you are?
                                These are related but distinct questions.

### Internal Contact Tab
- `_outreach_status`          — new; tracks org outreach pipeline for crowdfunding
                                campaign and partner relationships
- `_crowdfunding_tier`        — new; Open Collective jurisdiction funding tier

---

## Prompt Schema → ACF Field Mapping

Maps ingest JSON keys to canonical ACF field names for the ingest rewrite.

```
JSON key                     → ACF field
─────────────────────────────────────────────────────────
identity.official_name       → official_name
identity.common_name         → common_name
identity.official_homepage_url → website_url
identity.homepage_url_status → website_url_status
identity.homepage_url_date   → website_url_date
identity.general_description → general_description

scope.nationwide_example     → nationwide_example
scope.protected_disclosures  → protected_disclosures
scope.protected_classes      → protected_classes
scope.protected_class_details→ protected_class_details
scope.languages_supported    → languages
scope.languages_additional   → languages_additional
scope.assistance_type        → org_type
scope.employment_sectors     → employment_sectors
scope.cost_models            → cost_models
scope.services_provided      → services
scope.additional_services    → additional_services
scope.process_types          → process_types
scope.case_stages            → case_stages
scope.case_stage_details     → case_stage_details
scope.disclosure_targets     → disclosure_targets
scope.disclosure_target_details → disclosure_target_details
scope.jurisdiction_exceptions→ jurisdiction_exceptions
scope.whistleblower_scope    → whistleblower_scope
scope.whistleblower_note     → whistleblower_note

contact.intake_url           → intake_url
contact.contact_url          → contact_url
contact.phones               → phones (repeater)
contact.emails               → emails (repeater)
contact.mailing_address      → mailing_address

eligibility.income_eligibility_required → has_income_limit (bool coerced)
eligibility.income_eligibility_details  → income_limit_details
eligibility.eligibility_notes           → eligibility_notes

security.has_secure_channel  → has_secure_channel
security.secure_contact_url  → secure_url
security.secure_contact_tool → secure_tool
security.secure_contact_tool_other → secure_tool_other
security.anonymous_pre_consult_possible → anonymous_pre_consult
security.has_attorneys       → has_attorneys

review.legitimacy_url        → legitimacy_url
review._review_notes         → (stripped at ingest; not stored in ACF)
```

---

## Shared Workflow Groups

All four shared workflow groups attach to `ws-assist-org`:

| Group | File | menu_order |
|---|---|---|
| `group_stamp_metadata` | `acf-stamp-fields.php` | 90 |
| `group_plain_english_metadata` | `acf-plain-english-fields.php` | 85 |
| `group_source_verify_metadata` | `acf-source-verify.php` | 95 |
| `group_major_edit_metadata` | `acf-major-edit.php` | 99 |

---

## Needed Hooks

- `website_url_date` and `intake_url_date` — auto-populate from ingest timestamp
  when `*_url_status` is `verified`. Same pattern as other URL date fields.
- `is_nationwide` true → clear `jurisdictions` and `has_limited_scope` on save.
- `has_limited_scope` true → require `community_scope` (admin notice, not hard required).
- `whistleblower_scope` = 0 → emit admin notice requiring `whistleblower_note` explanation.
  Ingest enforces: zero scope without a note is a hard reject.
- `secure_tool` = `other` → require `secure_tool_other` (admin notice).
- `services` includes `additional` → require `additional_services` (admin notice).
- `languages` includes `additional` → require `languages_additional` (admin notice).
- `has_income_limit` true → require `income_limit_details` (admin notice).

---

## Notes

- `anonymous_pre_consult` (Contact tab) and `accepts_anonymous` (Eligibility tab)
  are intentionally separate. The first is a security/channel question; the second
  is an eligibility question. Both matter to Maya in different ways.
- `whistleblower_note` in the canonical schema is NOT a `*_details` field — it is
  a freetext editorial note / verbatim quote, not a conditional companion. The name
  was cleaned from `whistleblower_scope_details` to reflect this distinction.
- The `_id` hidden field is written by ingest and the matrix seeder only. Editors
  never interact with it directly. Meta key note: ACF uses `_ws_aorg_id` internally;
  the canonical `_id` field writes to a separate meta key to avoid clobbering ACF's
  own field reference mapping.
- The Internal Contact tab is entirely non-public. No query layer function should
  ever return these fields. The underscore prefix on all meta keys enforces this
  in WordPress's visibility convention.
- `org_type` uses a radio single-select rather than a multi-select. An org that
  genuinely spans multiple types should use `mixed` — the taxonomy has this term.
  Do not tag multiple types; the radio enforces the singular classification.
