# Assist-Org ACF Canonical Field Draft

**CPT:** `ws-assist-org`
**ACF Group:** `group_assist_org_metadata`
**Version:** 1.0.0-draft
**Based On:** `legal-record-acf-fields.md` conventions; `acf-assist-orgs.php` v3.17.0; `register-taxonomies.php` v3.16.0

Purpose: Define a unified, fully-expanded field set for `ws-assist-org` records. The goal is
complete edge-case coverage — a Utah org serving only Arabic-speaking whistleblowers with a
clothing-optional policy in their mission statement should map cleanly. When in doubt, add the
field and accept that it will be empty 99% of the time. Empty is fine. Missing is not.

---

## Naming Rules Applied

All rules inherit from `legal-record-acf-fields.md` naming conventions. Assist-org extensions:

- Prefix: `ws_aorg_*` for public/content fields; `_ws_aorg_*` for internal/private fields.
- No CPT infix clash; `ws_aorg_` is the canonical namespace.
- `snake_case` meta names; `kebab-case` choice/taxonomy slugs.
- `has_*` — trigger booleans (may reveal companion fields).
- `is_*` — state booleans (do not trigger companions).
- `*_details` — freetext companion; conditional on `has_*` bool true or `has-details` sentinel.
- `*_context` — freetext companion; conditional on specific trigger value being present.
- `*_notes` — internal/editorial freetext; never surfaced publicly.
- `*_url`, `*_date`, `*_email` — data-shape suffixes; use only with matching data shapes.
- Taxonomy fields are `multi_select` unless noted as `single-select`.
- Taxonomy fields use `load_terms => 1, save_terms => 1` unless noted otherwise.

### Sentinel Values (Inherited)

- `has-details` — triggers `*_details` companion in taxonomy/choice fields.
- `unclear` — ternary fallback; signals ambiguity; expected for many orgs.
- `additional` — in `ws_language`; auto-assigned when `ws_aorg_additional_languages` is non-empty.
- `additional` — in `ws_aorg_service`; auto-assigned when `ws_aorg_additional_services` is non-empty.

### Needed Hooks

- `ws_aorg_additional_languages` non-empty → auto-assign `additional` term in `ws_language`.
- `ws_aorg_additional_services` non-empty → auto-assign `additional` term in `ws_aorg_service`.
- `ws_aorg_serves_nationwide` true → clear `ws_aorg_jurisdictions` on save with admin notice.
- `ws_aorg_whistleblower_scope` = 0 → enforce `ws_aorg_whistleblower_scope_details` non-empty
  (hard reject at ingest; admin notice on save).
- `ws_aorg_has_secure_channel` true → validate `ws_aorg_secure_contact_url` non-empty on save.
- `ws_aorg_secure_contact_tool` = `other` → enforce `ws_aorg_secure_contact_tool_other` non-empty.
- `ws_aorg_has_income_limit` true → enforce `ws_aorg_income_limit_details` non-empty.
- `ws_aorg_accepts_anonymous` true AND `ws_aorg_has_secure_channel` false → emit advisory notice
  (anonymous intake without secure channel is worth flagging for editorial review).
- `ws_aorg_cultural_community_scope` non-empty OR `ws_aorg_cultural_focus` non-empty
  → auto-assign `has-details` in `ws_protected_class` if not already present.
- `ws_aorg_policy_flags` includes `clothing-optional` or `clothing-encouraged-nude`
  → emit admin advisory notice (editorial heads-up, not a blocker).
- `ws_aorg_faith_based` true → auto-assign `has-details` to `ws_protected_class` if not present.
- `ws_aorg_primary_language_only` true → validate `ws_aorg_languages` has exactly one term selected
  and it is NOT `english` only (advisory; some legitimate orgs are non-English primary).
- `ws_aorg_bar_state_ids` non-empty → derive and store `_ws_aorg_bar_state_ids` hidden field.
- `ws_aorg_last_reviewed` → bump `ws_auto_last_edited_date` stamp on save.

---

## Inline Field Descriptions

All fields freetext unless defined by naming convention or explicitly specified.
Taxonomy fields are multi_select unless noted. All taxonomy fields load_terms/save_terms = 1.
All `has_*` fields are `true_false` with `ui => 1`.

---

## Tab: Identity

Fields ordered: identification → classification → presentation

- `_ws_aorg_id`                     — (text, required, slug-safe; lowercase-hyphen; ingest dedupe code;
                                       stored with underscore prefix per META KEY NOTE in acf-assist-orgs.php;
                                       examples: `aclu-national`, `gp-ca`, `nwc-dc`)
- `ws_aorg_official_name`           — (text, required; full official name exactly as on homepage/governing docs;
                                       post_title mirrors this value at ingest time)
- `ws_aorg_common_name`             — (text, optional; shorthand/acronym used in citations; e.g. `GAP`, `NWC`)
- `ws_aorg_type`                    — (single-select taxonomy: `ws_aorg_type`, radio, required;
                                       use `mixed` when org genuinely spans multiple categories)
- `ws_aorg_description`             — (textarea, optional; plain-English mission/focus overview;
                                       reserve full narrative for `ws_plain_english_wysiwyg`)
- `ws_aorg_logo`                    — (image, optional; PNG or SVG preferred; max 1MB)
- `ws_aorg_ein`                     — (text, optional; IRS Employer Identification Number; format `XX-XXXXXXX`;
                                       enables GuideStar/ProPublica cross-reference at ingest)
- `ws_aorg_founding_year`           — (number, optional; four-digit year; used for credibility scoring)
- `ws_aorg_parent_org`              — (text, optional; parent organization name when this is a chapter or program;
                                       e.g. `ACLU National` for a state affiliate)
- `has_parent_org`                  — (bool trigger; reveals `ws_aorg_parent_org` and
                                       `ws_aorg_parent_org_url`)
- `ws_aorg_parent_org_url`          — (url, conditional on `has_parent_org`; parent org homepage)
- `ws_aorg_is_chapter`              — (is_* bool; true when this record is a chapter/affiliate of a
                                       separately-documented parent org record; used to suppress duplicate
                                       nationwide indexing)
- `ws_aorg_faith_based`             — (is_* bool; true when org has religious affiliation or faith-based
                                       mission; does NOT disqualify; surfaces to editorial for `protected_class`
                                       review and public disclosure tagging)
- `ws_aorg_faith_tradition`         — (text, conditional on `ws_aorg_faith_based`; e.g. `Quaker`,
                                       `Catholic`, `Evangelical`; optional; empty = interfaith or unspecified)

---

## Tab: Scope of Service

Fields ordered: geographic → whistleblower focus → disclosure → classes → sectors →
cultural focus → case stages → process → services

- `ws_aorg_serves_nationwide`       — (true_false; nationwide trigger; all 57 jurisdictions when true;
                                       clears `ws_aorg_jurisdictions` on save)
- `ws_aorg_whistleblower_scope`     — (number, required; 0–3; 0=off-topic/flag, 1=tangential,
                                       2=significant program, 3=primary mission; base score multiplier)
- `ws_aorg_whistleblower_scope_details` — (textarea; scope justification; required when score = 0;
                                       paste verbatim quote from org's website; editorial review only)
- `ws_aorg_has_limited_scope`       — (bool; true when coverage is sub-jurisdictional; city/county/region;
                                       conditional on `ws_aorg_serves_nationwide` = false)
- `ws_aorg_jurisdictions`           — (taxonomy: `ws_jurisdiction`, multi_select; leave blank if nationwide)
- `ws_aorg_jurisdiction_exceptions` — (textarea, optional; free text coverage gaps; e.g.
                                       `nationwide except Texas`; maps from `jurisdiction_exceptions` in
                                       research prompt)
- `ws_aorg_community_scope`         — (textarea, conditional on `ws_aorg_has_limited_scope`;
                                       sub-jurisdictional service footprint; e.g. `San Francisco`,
                                       `Navajo Nation`, `Los Angeles County`)

### Cultural & Community Focus (new section)

These fields capture specialized community targeting not representable by `ws_protected_class`
taxonomy alone. A Utah org serving only Arabic-speaking whistleblowers is the canonical use case.
These are edge-case fields — most records will be empty. Include them anyway.

- `has_cultural_focus`              — (bool trigger; true when org explicitly targets a named
                                       ethnic, national-origin, immigrant, diaspora, or cultural community)
- `ws_aorg_cultural_community_scope` — (textarea, conditional on `has_cultural_focus`;
                                       describe the community served; e.g. `Arabic-speaking whistleblowers`,
                                       `Somali immigrant workers`, `Hmong agricultural workers`)
- `ws_aorg_cultural_focus`          — (taxonomy: `ws_aorg_cultural_focus` [NEW — see Taxonomy Extension below];
                                       conditional on `has_cultural_focus`;
                                       multi_select; describes cultural/ethnic/diaspora focus categories)
- `ws_aorg_nationality_focus`       — (text, conditional on `has_cultural_focus`; ISO 3166-1 alpha-2 codes
                                       comma-separated; e.g. `SA, EG, JO, LB` for Arab countries;
                                       optional, ingest-friendly cross-reference)
- `ws_aorg_immigration_status_scope` — (multi-select: `undocumented`|`daca`|`visa-holder`|`asylum-seeker`|
                                       `refugee`|`naturalized`|`all-status`|`has-details`; optional;
                                       captures immigration status eligibility constraints or focus)
- `ws_aorg_immigration_status_details` — (textarea, conditional on `ws_aorg_immigration_status_scope`
                                       includes `has-details`)

### Disclosure Scope

- `ws_aorg_disclosure_types`        — (taxonomy: `ws_disclosure_type`, multi_select, required;
                                       all misconduct categories this org has experience with)
- `ws_aorg_disclosure_targets`      — (taxonomy: `ws_disclosure_target`, multi_select, optional;
                                       reporting channels the org can help navigate)
- `ws_aorg_disclosure_target_details` — (textarea, conditional on `ws_aorg_disclosure_targets`
                                       includes `has-details`)

### Protected & Served Classes

- `ws_aorg_protected_classes`       — (taxonomy: `ws_protected_class`, multi_select, optional;
                                       all protected classes this org serves; use `has-details` for
                                       classes not in taxonomy)
- `ws_aorg_protected_class_details` — (textarea, conditional on `ws_aorg_protected_classes`
                                       includes `has-details`; use for edge cases including cultural
                                       community constraints captured in `ws_aorg_cultural_community_scope`)
- `ws_aorg_excluded_classes`        — (taxonomy: `ws_excluded_class`, multi_select, optional;
                                       classes this org explicitly CANNOT serve; rare but important for
                                       matching logic; e.g. org that excludes federal employees from
                                       its state-specific mandate)
- `ws_aorg_excluded_class_details`  — (textarea, conditional on `ws_aorg_excluded_classes` non-empty)

### Employment & Sector

- `ws_aorg_employment_sectors`      — (taxonomy: `ws_employment_sector`, multi_select, optional;
                                       sectors served; leave blank if all sectors accepted)
- `ws_aorg_employer_size_scope`     — (multi-select: `any-size`|`small-employer`|`mid-size`|`large-employer`|
                                       `government-employer`|`has-details`; optional; for orgs with
                                       employer-size constraints — e.g. NLRA 1+ employees vs. ADEA 20+)
- `ws_aorg_employer_size_details`   — (textarea, conditional on `ws_aorg_employer_size_scope`
                                       includes `has-details`)

### Case Stages & Process

- `ws_aorg_case_stages`             — (taxonomy: `ws_case_stage`, multi_select, optional;
                                       stages where org is most useful)
- `ws_aorg_case_stage_details`      — (textarea, conditional on `ws_aorg_case_stages` includes `other`)
- `ws_aorg_process_types`           — (taxonomy: `ws_process_type`, multi_select, optional;
                                       process channels org can help navigate)
- `ws_aorg_geographic_reach_class`  — (single-select: `hyperlocal`|`regional`|`statewide`|
                                       `multi-state`|`nationwide`|`international`; optional;
                                       editorial summary of reach; derived from jurisdictions
                                       but useful for display logic)

### Services

- `ws_aorg_services`                — (taxonomy: `ws_aorg_service`, multi_select, required;
                                       all services provided)
- `ws_aorg_additional_services`     — (textarea, optional; services not in taxonomy;
                                       non-empty auto-assigns `additional` service term)
- `ws_aorg_service_limitations`     — (textarea, optional; describes any limitations on services;
                                       e.g. `consultation only, no representation`, `referral only after intake screening`)

### Mission Statement & Policy Flags (new — edge case coverage)

- `has_mission_statement_url`       — (bool trigger; true when a standalone mission statement page exists)
- `ws_aorg_mission_statement_url`   — (url, conditional on `has_mission_statement_url`; direct link to
                                       mission/about/values page)
- `ws_aorg_mission_verbatim`        — (textarea, optional; verbatim quote from mission statement;
                                       used for editorial context and `whistleblower_scope` justification)
- `ws_aorg_policy_flags`            — (multi-select: `trauma-informed`|`survivor-led`|`peer-led`|
                                       `harm-reduction`|`clothing-optional`|`clothing-encouraged-nude`|
                                       `clothing-required`|`members-only`|`invitation-only`|
                                       `referral-required`|`appointment-required`|`by-application`|
                                       `waitlist-active`|`has-details`;
                                       optional; surface unusual operational or access policies from
                                       mission statements or intake pages; yes, clothing-optional is here,
                                       and it will be empty on 99.9% of records — that's fine)
- `ws_aorg_policy_flag_details`     — (textarea, conditional on `ws_aorg_policy_flags` includes `has-details`)

---

## Tab: Contact & Intake

Fields ordered: web → intake → phones → emails → address → secure → languages

- `ws_aorg_website_url`             — (url, required; official primary website)
- `ws_aorg_intake_url`              — (url, optional; direct intake/case-request path;
                                       must be a path to personal assistance, NOT a tip-drop URL)
- `ws_aorg_contact_url`             — (url, optional; general contact page)
- `ws_aorg_intake_url_status`       — (single-select: `verified`|`redirects`|`unverified`|`dead`;
                                       optional; ingest provenance flag for intake URL)
- `ws_aorg_homepage_url_status`     — (single-select: `verified`|`redirects`|`unverified`|`dead`;
                                       optional; ingest provenance flag for homepage)
- `ws_aorg_homepage_url_date`       — (date_picker, optional; date homepage URL was verified;
                                       omit if status is not `verified`)
- `ws_aorg_phones`                  — (repeater, optional; layout: table;
                                       sub-fields: `ws_aorg_phone_type` [select: hotline|intake|headquarters|
                                       regional|tty|fax|other] + `ws_aorg_phone_number` [text])
- `ws_aorg_emails`                  — (repeater, optional; layout: table;
                                       sub-fields: `ws_aorg_email_type` [select: intake|general|legal|media|
                                       support|secure|other] + `ws_aorg_email_address` [email])
- `ws_aorg_mailing_address`         — (textarea, optional; physical/mailing address; use `||` separator
                                       for multiple addresses)
- `ws_aorg_has_physical_office`     — (is_* bool; true when org has a public-facing walk-in or
                                       appointment office; distinct from mailing address)
- `ws_aorg_physical_office_details` — (textarea, conditional on `ws_aorg_has_physical_office`;
                                       describe access, hours, ADA compliance if known)
- `ws_aorg_has_virtual_intake`      — (is_* bool; true when org offers video or async virtual intake;
                                       useful for rural/geographically-isolated whistleblowers)

### Secure Contact

- `ws_aorg_has_secure_channel`      — (true_false; true = dedicated encrypted first-contact tool;
                                       Standard HTTPS alone = false; SecureDrop qualifies as true)
- `ws_aorg_secure_contact_url`      — (url, conditional on `ws_aorg_has_secure_channel`; link to
                                       tool or instructions)
- `ws_aorg_secure_contact_tool`     — (select, conditional on `ws_aorg_has_secure_channel`;
                                       choices from `WS_SCHEMA_SECURE_TOOL`: SecureDrop|Signal|
                                       ProtonMail|Tutanota|Wire|Keybase|other)
- `ws_aorg_secure_contact_tool_other` — (text, conditional on tool = `other`)
- `ws_aorg_accepts_anonymous`       — (true_false; true when org can assist without client revealing
                                       identity; SecureDrop qualifies)
- `ws_aorg_anonymous_intake_notes`  — (textarea, optional; describe anonymous intake path, limitations,
                                       or tool used; useful when `ws_aorg_accepts_anonymous` is true
                                       but nuance is required)

### Languages

- `ws_aorg_languages`               — (taxonomy: `ws_language`, multi_select; languages org can support;
                                       include `english` if site is in English; add `additional` if
                                       other languages exist)
- `ws_aorg_additional_languages`    — (text; additional languages not in taxonomy, comma-separated;
                                       non-empty auto-assigns `additional` term)
- `ws_aorg_primary_language_only`   — (is_* bool; true when org operates exclusively in a non-English
                                       primary language; e.g. French-only org; triggers advisory hook)
- `ws_aorg_language_access_notes`   — (textarea, optional; describe interpreter services, translation
                                       availability, or language access limitations; covers the edge case
                                       of a French-only org in Utah)
- `ws_aorg_asl_available`           — (is_* bool; true when American Sign Language interpretation
                                       is available for intake or consultation)
- `ws_aorg_tty_available`           — (is_* bool; true when TTY/TDD telephone service is available;
                                       derives from `ws_aorg_phones` type `tty` but surfaced here
                                       for accessibility filtering)

---

## Tab: Eligibility & Cost

Fields ordered: cost → income → anonymous → additional eligibility → intake commitment

- `ws_aorg_cost_models`             — (taxonomy: `ws_aorg_cost_model`, multi_select, required;
                                       one or more cost structures)
- `ws_aorg_cost_model_notes`        — (textarea, optional; describe fee arrangements not captured by
                                       taxonomy; e.g. `free for retaliation claims, fee-for-service
                                       for consultation only`)
- `ws_aorg_has_income_limit`        — (true_false; true = income or financial eligibility required)
- `ws_aorg_income_limit_details`    — (textarea, conditional on `ws_aorg_has_income_limit`;
                                       describe thresholds; e.g. `below 200% federal poverty level`)
- `ws_aorg_accepts_anonymous`       — (true_false; duplicate surface here for editorial workflow;
                                       canonical value lives in Contact tab; sync by hook)
- `ws_aorg_eligibility_details`     — (textarea, optional; non-income eligibility constraints;
                                       employer size thresholds, case type restrictions, union membership,
                                       geographic limits, etc.)
- `ws_aorg_has_waitlist`            — (is_* bool; true when org has a known active waitlist;
                                       surfaced in directory to manage user expectations)
- `ws_aorg_waitlist_details`        — (textarea, conditional on `ws_aorg_has_waitlist`;
                                       describe scope, estimated wait time if known)
- `ws_aorg_intake_commitment_class` — (single-select: `direct-assistance`|`fast-referral`|`general-referral`|
                                       `information-only`|`unclear`; optional; editorial classification of
                                       intake commitment level; maps to research prompt INCLUDE criteria)
- `ws_aorg_intake_commitment_details` — (textarea, conditional on `ws_aorg_intake_commitment_class`
                                       is `unclear` or when nuance needed)

### Accessibility (new — edge case coverage)

- `ws_aorg_ada_accessible`          — (is_* bool; true when physical office is ADA compliant;
                                       omit/false when no physical office)
- `ws_aorg_accessibility_details`   — (textarea, optional; describe physical, cognitive, or
                                       communication accessibility features or limitations)
- `ws_aorg_online_only`             — (is_* bool; true when org has no physical presence;
                                       important for rural and international whistleblowers)

---

## Tab: Credentials

Fields ordered: legal → accreditation → bar → legitimacy → review date

- `ws_aorg_licensed_attorneys`      — (true_false; true = licensed attorneys available for
                                       intake or representation)
- `ws_aorg_attorney_scope`          — (single-select: `staff-attorneys`|`volunteer-attorneys`|
                                       `contracted-attorneys`|`referral-network-only`|`mixed`|`unclear`;
                                       conditional on `ws_aorg_licensed_attorneys` true;
                                       clarifies the nature of attorney access)
- `ws_aorg_accreditation`           — (text, optional; relevant accreditations;
                                       e.g. `ABA-accredited`, `NQAP member`, `DOJ-recognized`,
                                       `IRS 501(c)(3)`)
- `ws_aorg_bar_states`              — (text, optional; bar-admitted states in plain text;
                                       e.g. `CA, NY, DC, Federal`)
- `ws_aorg_bar_state_ids`           — (taxonomy: `ws_jurisdiction`, multi_select, optional;
                                       machine-readable bar admission jurisdictions; load_terms => 1,
                                       save_terms => 0 — display only, not saved as post terms;
                                       ingest-populated from `ws_aorg_bar_states`;
                                       enables tax_query filtering for state-specific legal help)
- `ws_aorg_non_legal_credentials`   — (text, optional; relevant non-legal credentials;
                                       e.g. `NASW-accredited social workers`, `ICF-certified coaches`,
                                       `SAMHSA-trained counselors`; covers peer support and
                                       mental health credentials)
- `ws_aorg_legitimacy_url`          — (url, optional; 3rd-party verification;
                                       IRS 990, Charity Navigator, GuideStar, state bar directory,
                                       congressional directory, court listing)
- `ws_aorg_legitimacy_class`        — (multi-select: `irs-990`|`charity-navigator`|`guidestar`|
                                       `state-bar-directory`|`congressional-directory`|`court-listing`|
                                       `news-coverage`|`government-contract`|`has-details`; optional;
                                       classifies the type of legitimacy evidence linked above)
- `ws_aorg_legitimacy_details`      — (textarea, conditional on `ws_aorg_legitimacy_class`
                                       includes `has-details`)
- `ws_aorg_last_reviewed`           — (date_picker, optional; date record last verified for accuracy;
                                       content-owned, not a stamp field)

### Awards & Recognition (new — edge case/scoring)

- `has_notable_recognition`         — (bool trigger; true when org has received notable external
                                       recognition, awards, or congressional citations)
- `ws_aorg_recognition_details`     — (textarea, conditional on `has_notable_recognition`;
                                       describe recognition; used in credibility scoring)

---

## Tab: Organizational Profile (new tab)

This tab captures structural, operational, and affiliation data not covered by other tabs.
Most fields will be empty. All exist for edge-case coverage and scoring accuracy.

- `ws_aorg_org_size_class`          — (single-select: `solo-practitioner`|`small-org-2-10`|
                                       `mid-org-11-50`|`large-org-51-plus`|`coalition`|`unclear`;
                                       optional; affects whistleblower_scope scoring weight)
- `ws_aorg_is_coalition`            — (is_* bool; true when org is a formal coalition of multiple
                                       independent member orgs; changes display logic)
- `ws_aorg_coalition_members`       — (textarea, conditional on `ws_aorg_is_coalition`;
                                       list member organizations)
- `ws_aorg_national_affiliations`   — (text, optional; national umbrella orgs, networks, or
                                       federations this org belongs to; comma-separated;
                                       e.g. `NELA, NWC, Alliance for Justice`)
- `ws_aorg_government_funded`       — (is_* bool; true when org receives primary or significant
                                       government funding; editorial disclosure flag; does not
                                       disqualify but surfaces for conflict-of-interest review)
- `ws_aorg_government_funded_details` — (textarea, conditional on `ws_aorg_government_funded`;
                                       describe funding source and scope)
- `ws_aorg_has_active_litigation`   — (is_* bool; true when org is itself party to active
                                       significant litigation; editorial heads-up flag)
- `ws_aorg_active_litigation_details` — (textarea, conditional on `ws_aorg_has_active_litigation`)
- `ws_aorg_social_media`            — (repeater, optional; layout: table;
                                       sub-fields: `ws_aorg_social_platform`
                                       [select: twitter-x|bluesky|mastodon|linkedin|facebook|
                                       instagram|youtube|other] + `ws_aorg_social_url` [url];
                                       for credibility cross-reference and public presence signal)

---

## Tab: Review & Source

Fields ordered: review notes → source → legitimacy audit

- `ws_aorg_review_notes`            — (textarea, optional; public-facing: editorial note about
                                       record quality, coverage gaps, or pending verification;
                                       distinct from internal relationship notes)
- `ws_aorg_source_quote`            — (textarea, optional; verbatim quote from org's site that
                                       best characterizes their whistleblower scope and services;
                                       maps from `nationwide_example` and `whistleblower_note`
                                       in research prompt)
- `ws_aorg_source_url`              — (url, optional; source URL for `ws_aorg_source_quote`;
                                       provenance link for editorial audit)
- `ws_aorg_ingest_source_method`    — (single-select: `ai_research`|`human_created`|`matrix_seeded`|
                                       `partner_submission`|`user_submission`; optional; tracks
                                       how record entered pipeline; maps from `source_method` in
                                       research prompt meta)
- `ws_aorg_ingest_source_name`      — (text, optional; tool or feed name when ingest_source is
                                       `ai_research`; e.g. `NotebookLM`, `Perplexity`, `Grok`)
- `ws_aorg_prompt_version`          — (text, optional; research prompt version used; e.g. `3.0.0`;
                                       provenance for reconciler)

---

## Tab: Internal Contact & Relationship Notes

*Not surfaced publicly. For pipeline continuity only.*

- `_ws_aorg_internal_contact_name`  — (text, optional; primary relationship contact at org)
- `_ws_aorg_internal_contact_role`  — (text, optional; role/title for outreach context)
- `_ws_aorg_internal_contact_email` — (email, optional; direct working email)
- `_ws_aorg_internal_contact_phone` — (text, optional; direct phone/extension)
- `_ws_aorg_internal_last_contacted` — (date_picker, optional; most recent direct outreach date)
- `_ws_aorg_internal_relationship_notes` — (textarea, optional; concise factual notes for
                                       relationship continuity; rows: 4)
- `_ws_aorg_research_anomalies`     — (textarea, optional; maps from `_review_notes` in research
                                       prompt; captures fallback use, schema gaps, ingest flags,
                                       dead URLs, or reviewer breadcrumbs; NOT the same as
                                       `ws_aorg_review_notes` — this is internal-only)

---

## Hidden Fields (no tab; underscore-prefixed)

- `_ws_aorg_id`                     — (text; ingest dedupe code; see META KEY NOTE)
- `_ws_aorg_bar_state_ids`          — (derived; auto-populated from `ws_aorg_bar_states` text
                                       on save; comma-separated jurisdiction slugs; ingest-use)
- `_ws_aorg_jurisdiction_count`     — (number; derived; count of terms in `ws_aorg_jurisdictions`;
                                       auto-fill on save; used for geographic reach scoring)
- `_ws_aorg_is_nationwide`          — (bool; derived from `ws_aorg_serves_nationwide`; redundant
                                       but useful for fast ingest flag without ACF dependency)
- `_ws_aorg_has_attorney_access`    — (bool; derived from `ws_aorg_licensed_attorneys` AND
                                       `ws_aorg_attorney_scope` != `referral-network-only`;
                                       auto-fill on save; enables attorney-access filter in directory)
- `_ws_aorg_language_count`         — (number; derived; count of terms in `ws_aorg_languages` plus
                                       presence in `ws_aorg_additional_languages`; scoring signal)
- `_ws_aorg_primary_language`       — (text; derived; first non-`additional` term in `ws_aorg_languages`;
                                       used when `ws_aorg_primary_language_only` is true)

---

## Shared Workflow Groups (Inherited)

These field groups are registered separately and appended to `ws-assist-org` records.
They are not re-registered here; they are referenced for editorial completeness.

| Group | File | menu_order |
|---|---|---|
| `group_stamp_metadata` | `acf-stamp-fields.php` | 90 |
| `group_plain_english_metadata` | `acf-plain-english-fields.php` | 85 |
| `group_source_verify_metadata` | `acf-source-verify.php` | — |
| `group_major_edit_metadata` | `acf-major-edit.php` | 99 |

---

## Taxonomy Extensions

New taxonomies proposed for `ws-assist-org`. These do not exist in `register-taxonomies.php` v3.16.0.
Each requires a new `register_taxonomy()` entry, a seeder, and a gate.

---

### ws_aorg_cultural_focus (new)

Flat taxonomy. Describes ethnic, national-origin, immigrant, diaspora, or cultural community
targeting for an assist-org. The `ws_protected_class` taxonomy covers worker *type*; this
covers cultural *identity* — a meaningfully distinct axis.

**Object types:** `ws-assist-org`

**Proposed seed terms:**

| Slug | Label |
|---|---|
| `arab-middle-eastern` | Arab / Middle Eastern |
| `south-asian` | South Asian |
| `east-asian` | East Asian |
| `southeast-asian` | Southeast Asian |
| `latin-hispanic` | Latin / Hispanic |
| `african-diaspora` | African Diaspora |
| `indigenous-native` | Indigenous / Native American |
| `pacific-islander` | Pacific Islander |
| `eastern-european` | Eastern European |
| `lgbtq` | LGBTQ+ |
| `women-focused` | Women-Focused |
| `immigrant-general` | Immigrant Workers (General) |
| `undocumented-workers` | Undocumented Workers |
| `refugee-asylum` | Refugee / Asylum Seeker |
| `faith-community` | Faith Community |
| `disability-focused` | Disability-Focused |
| `veteran-focused` | Veteran / Military Family |
| `multilingual-general` | Multilingual (General) |
| `additional` | Additional |

**Gate key:** `ws_seeded_aorg_cultural_focus`
**Gate version:** `1.0.0`

---

### ws_aorg_intake_commitment (new — optional; consider replacing `ws_aorg_intake_commitment_class` freetext)

Flat taxonomy. Classifies the org's commitment level to direct personal assistance.
Enables `tax_query` filtering for directory matching.

**Object types:** `ws-assist-org`

**Proposed seed terms:**

| Slug | Label |
|---|---|
| `direct-assistance` | Direct Assistance |
| `fast-referral` | Fast Referral Pathway |
| `general-referral` | General Referral |
| `information-only` | Information / Education Only |
| `unclear` | Unclear |

**Gate key:** `ws_seeded_aorg_intake_commitment`
**Gate version:** `1.0.0`

---

### Extend Existing Taxonomy: ws_aorg_service

Add the following terms to `ws_seed_aorg_service_taxonomy()`:

| Slug | Label | Rationale |
|---|---|---|
| `immigration-support` | Immigration Support | Serves undocumented/visa-holder whistleblowers |
| `mental-health-crisis` | Mental Health Crisis Support | Distinct from `mental-health`; crisis-specific |
| `safety-planning` | Safety Planning | Physical safety advice for high-risk reporters |
| `translation-interpretation` | Translation / Interpretation | Language access as a service |
| `court-accompaniment` | Court Accompaniment | Non-legal in-person support at hearings |
| `benefits-navigation` | Benefits / COBRA Navigation | Post-termination benefits help |
| `reintegration` | Reintegration Support | Post-retaliation career reintegration |
| `training-education` | Whistleblower Training / Education | Org serves primarily via workshops |
| `policy-advocacy` | Policy / Legislative Advocacy | Systemic advocacy distinct from case advocacy |
| `research` | Research / Documentation | Primary output is research, not direct help |

---

### Extend Existing Taxonomy: ws_aorg_cost_model

Add the following terms:

| Slug | Label | Rationale |
|---|---|---|
| `grant-funded` | Grant-Funded (No Cost) | Distinct from `free`; funding source disclosed |
| `membership-based` | Membership-Based | Access tied to org membership (e.g. union) |
| `donation-requested` | Donation Requested | Nominally free but donation asked |
| `court-awarded-fees` | Court-Awarded Fees Only | Attorneys only paid if case wins via fee-shifting |

---

### Extend Existing Taxonomy: ws_case_stage

Add the following terms to `ws_seed_case_stage_taxonomy()`:

| Slug | Label | Rationale |
|---|---|---|
| `decision-point` | Decision Point (Pre-Report) | Before deciding whether to report; counseling stage |
| `post-settlement` | Post-Settlement / Aftermath | After case resolution; reintegration focus |
| `appeal` | Appeal | Active appeals support |

---

### Extend Existing Taxonomy: ws_language

Add the following terms to `ws_seed_language_taxonomy()`:

| Slug | Label | Rationale |
|---|---|---|
| `amharic` | Amharic | Ethiopian diaspora — significant in major metro areas |
| `farsi` | Farsi / Persian | Iranian diaspora |
| `urdu` | Urdu | South Asian workers |
| `turkish` | Turkish | Growing US diaspora |
| `somali` | Somali | East African diaspora |
| `hmong` | Hmong | Agricultural and manufacturing workers |
| `khmer` | Khmer | Southeast Asian workers |
| `tigrinya` | Tigrinya | Eritrean/Ethiopian diaspora |
| `asl` | American Sign Language | Accessibility; already handled by `ws_aorg_asl_available` bool but useful for taxonomy filtering |
| `sign-language-other` | Other Sign Language | Non-ASL sign languages |

---

## Rename Normalization (Current acf-assist-orgs.php → Canonical)

Only fields that currently violate target naming conventions or require alignment with this draft:

| Current | Canonical |
|---|---|
| `ws_aorg_has_income_limit_details` | `ws_aorg_income_limit_details` |
| `ws_aorg_cost_models` | `ws_aorg_cost_models` (unchanged; multi confirmed correct) |
| `ws_aorg_phones` sub-field `ws_aorg_phone_type` | unchanged; schema-constant driven |
| `ws_aorg_emails` sub-field `ws_aorg_email_type` | `secure` type added to `WS_SCHEMA_EMAIL_TYPE` (see note) |

**WS_SCHEMA_EMAIL_TYPE note:** Add `secure` to the constant in `ws-schema-constants.php` to match
the research prompt's email type of `secure`. Currently missing from the constant; present in
the research prompt. This is a schema gap.

---

## Field Summary (Full)

Organized by tab for easy implementation reference.

### Identity Tab
| Field Name | Type | Required | Notes |
|---|---|---|---|
| `_ws_aorg_id` | text | yes | ingest dedupe key |
| `ws_aorg_official_name` | text | yes | mirrors post_title |
| `ws_aorg_common_name` | text | no | acronym/shorthand |
| `ws_aorg_type` | taxonomy (radio) | yes | `ws_aorg_type` |
| `ws_aorg_description` | textarea | no | plain-English overview |
| `ws_aorg_logo` | image | no | PNG/SVG |
| `ws_aorg_ein` | text | no | `XX-XXXXXXX` |
| `ws_aorg_founding_year` | number | no | 4-digit year |
| `has_parent_org` | true_false | no | trigger |
| `ws_aorg_parent_org` | text | conditional | parent org name |
| `ws_aorg_parent_org_url` | url | conditional | parent homepage |
| `ws_aorg_is_chapter` | true_false | no | is_* state flag |
| `ws_aorg_faith_based` | true_false | no | is_* state flag |
| `ws_aorg_faith_tradition` | text | conditional | faith tradition |

### Scope of Service Tab
| Field Name | Type | Required | Notes |
|---|---|---|---|
| `ws_aorg_serves_nationwide` | true_false | no | nationwide trigger |
| `ws_aorg_whistleblower_scope` | number 0–3 | yes | scoring |
| `ws_aorg_whistleblower_scope_details` | textarea | required when score=0 | |
| `ws_aorg_has_limited_scope` | true_false | conditional | sub-jurisdictional |
| `ws_aorg_jurisdictions` | taxonomy | no | `ws_jurisdiction` |
| `ws_aorg_jurisdiction_exceptions` | textarea | no | coverage gaps |
| `ws_aorg_community_scope` | textarea | conditional | sub-jx footprint |
| `has_cultural_focus` | true_false | no | trigger |
| `ws_aorg_cultural_community_scope` | textarea | conditional | e.g. Arabic-speaking |
| `ws_aorg_cultural_focus` | taxonomy | conditional | `ws_aorg_cultural_focus` [NEW] |
| `ws_aorg_nationality_focus` | text | conditional | ISO codes |
| `ws_aorg_immigration_status_scope` | multi-select | no | status eligibility |
| `ws_aorg_immigration_status_details` | textarea | conditional | |
| `ws_aorg_disclosure_types` | taxonomy | yes | `ws_disclosure_type` |
| `ws_aorg_disclosure_targets` | taxonomy | no | `ws_disclosure_target` |
| `ws_aorg_disclosure_target_details` | textarea | conditional | |
| `ws_aorg_protected_classes` | taxonomy | no | `ws_protected_class` |
| `ws_aorg_protected_class_details` | textarea | conditional | |
| `ws_aorg_excluded_classes` | taxonomy | no | `ws_excluded_class` |
| `ws_aorg_excluded_class_details` | textarea | conditional | |
| `ws_aorg_employment_sectors` | taxonomy | no | `ws_employment_sector` |
| `ws_aorg_employer_size_scope` | multi-select | no | employer size |
| `ws_aorg_employer_size_details` | textarea | conditional | |
| `ws_aorg_case_stages` | taxonomy | no | `ws_case_stage` |
| `ws_aorg_case_stage_details` | textarea | conditional | `other` trigger |
| `ws_aorg_process_types` | taxonomy | no | `ws_process_type` |
| `ws_aorg_geographic_reach_class` | single-select | no | editorial summary |
| `ws_aorg_services` | taxonomy | yes | `ws_aorg_service` |
| `ws_aorg_additional_services` | textarea | no | auto-assigns `additional` |
| `ws_aorg_service_limitations` | textarea | no | service constraints |
| `has_mission_statement_url` | true_false | no | trigger |
| `ws_aorg_mission_statement_url` | url | conditional | |
| `ws_aorg_mission_verbatim` | textarea | no | verbatim quote |
| `ws_aorg_policy_flags` | multi-select | no | operational policies |
| `ws_aorg_policy_flag_details` | textarea | conditional | |

### Contact & Intake Tab
| Field Name | Type | Required | Notes |
|---|---|---|---|
| `ws_aorg_website_url` | url | yes | official homepage |
| `ws_aorg_intake_url` | url | no | direct intake path |
| `ws_aorg_contact_url` | url | no | general contact |
| `ws_aorg_intake_url_status` | single-select | no | provenance flag |
| `ws_aorg_homepage_url_status` | single-select | no | provenance flag |
| `ws_aorg_homepage_url_date` | date_picker | no | verification date |
| `ws_aorg_phones` | repeater | no | type + number |
| `ws_aorg_emails` | repeater | no | type + address |
| `ws_aorg_mailing_address` | textarea | no | `||` separator |
| `ws_aorg_has_physical_office` | true_false | no | is_* state flag |
| `ws_aorg_physical_office_details` | textarea | conditional | access/hours |
| `ws_aorg_has_virtual_intake` | true_false | no | is_* state flag |
| `ws_aorg_has_secure_channel` | true_false | no | trigger |
| `ws_aorg_secure_contact_url` | url | conditional | |
| `ws_aorg_secure_contact_tool` | select | conditional | schema constant |
| `ws_aorg_secure_contact_tool_other` | text | conditional | |
| `ws_aorg_accepts_anonymous` | true_false | no | |
| `ws_aorg_anonymous_intake_notes` | textarea | no | anonymous path detail |
| `ws_aorg_languages` | taxonomy | no | `ws_language` |
| `ws_aorg_additional_languages` | text | no | auto-assigns `additional` |
| `ws_aorg_primary_language_only` | true_false | no | is_* state flag |
| `ws_aorg_language_access_notes` | textarea | no | interpreter/translation |
| `ws_aorg_asl_available` | true_false | no | is_* accessibility flag |
| `ws_aorg_tty_available` | true_false | no | is_* accessibility flag |

### Eligibility & Cost Tab
| Field Name | Type | Required | Notes |
|---|---|---|---|
| `ws_aorg_cost_models` | taxonomy | yes | `ws_aorg_cost_model` |
| `ws_aorg_cost_model_notes` | textarea | no | fee nuance |
| `ws_aorg_has_income_limit` | true_false | no | trigger |
| `ws_aorg_income_limit_details` | textarea | conditional | |
| `ws_aorg_accepts_anonymous` | true_false | no | synced from Contact tab |
| `ws_aorg_eligibility_details` | textarea | no | non-income constraints |
| `ws_aorg_has_waitlist` | true_false | no | is_* state flag |
| `ws_aorg_waitlist_details` | textarea | conditional | |
| `ws_aorg_intake_commitment_class` | single-select | no | or use taxonomy below |
| `ws_aorg_intake_commitment_details` | textarea | conditional | |
| `ws_aorg_ada_accessible` | true_false | no | is_* accessibility flag |
| `ws_aorg_accessibility_details` | textarea | no | accessibility description |
| `ws_aorg_online_only` | true_false | no | is_* state flag |

### Credentials Tab
| Field Name | Type | Required | Notes |
|---|---|---|---|
| `ws_aorg_licensed_attorneys` | true_false | no | |
| `ws_aorg_attorney_scope` | single-select | conditional | attorney type |
| `ws_aorg_accreditation` | text | no | accreditations |
| `ws_aorg_bar_states` | text | no | plain text |
| `ws_aorg_bar_state_ids` | taxonomy | no | `ws_jurisdiction`; save_terms=0 |
| `ws_aorg_non_legal_credentials` | text | no | non-legal creds |
| `ws_aorg_legitimacy_url` | url | no | 3rd-party verify |
| `ws_aorg_legitimacy_class` | multi-select | no | legitimacy type |
| `ws_aorg_legitimacy_details` | textarea | conditional | |
| `ws_aorg_last_reviewed` | date_picker | no | |
| `has_notable_recognition` | true_false | no | trigger |
| `ws_aorg_recognition_details` | textarea | conditional | awards/citations |

### Organizational Profile Tab (new)
| Field Name | Type | Required | Notes |
|---|---|---|---|
| `ws_aorg_org_size_class` | single-select | no | org size |
| `ws_aorg_is_coalition` | true_false | no | is_* state flag |
| `ws_aorg_coalition_members` | textarea | conditional | member list |
| `ws_aorg_national_affiliations` | text | no | networks/federations |
| `ws_aorg_government_funded` | true_false | no | is_* disclosure flag |
| `ws_aorg_government_funded_details` | textarea | conditional | |
| `ws_aorg_has_active_litigation` | true_false | no | is_* editorial flag |
| `ws_aorg_active_litigation_details` | textarea | conditional | |
| `ws_aorg_social_media` | repeater | no | platform + url |

### Review & Source Tab (new)
| Field Name | Type | Required | Notes |
|---|---|---|---|
| `ws_aorg_review_notes` | textarea | no | public editorial note |
| `ws_aorg_source_quote` | textarea | no | verbatim scope quote |
| `ws_aorg_source_url` | url | no | provenance link |
| `ws_aorg_ingest_source_method` | single-select | no | pipeline provenance |
| `ws_aorg_ingest_source_name` | text | no | tool/feed name |
| `ws_aorg_prompt_version` | text | no | research prompt version |

### Internal Contact Tab
| Field Name | Type | Required | Notes |
|---|---|---|---|
| `_ws_aorg_internal_contact_name` | text | no | private |
| `_ws_aorg_internal_contact_role` | text | no | private |
| `_ws_aorg_internal_contact_email` | email | no | private |
| `_ws_aorg_internal_contact_phone` | text | no | private |
| `_ws_aorg_internal_last_contacted` | date_picker | no | private |
| `_ws_aorg_internal_relationship_notes` | textarea | no | private |
| `_ws_aorg_research_anomalies` | textarea | no | ingest breadcrumbs |

### Hidden Fields
| Field Name | Type | Notes |
|---|---|---|
| `_ws_aorg_id` | text | ingest dedupe |
| `_ws_aorg_bar_state_ids` | text | derived from bar_states |
| `_ws_aorg_jurisdiction_count` | number | derived |
| `_ws_aorg_is_nationwide` | bool | derived |
| `_ws_aorg_has_attorney_access` | bool | derived |
| `_ws_aorg_language_count` | number | derived |
| `_ws_aorg_primary_language` | text | derived |

---

## Schema Gap Notes

1. **`secure` email type missing from `WS_SCHEMA_EMAIL_TYPE`** — The research prompt v3.0.0 uses
   `secure` as a valid email type. The ACF schema constant does not include it. Add `secure` to
   `WS_SCHEMA_EMAIL_TYPE` in `ws-schema-constants.php`. This is a live schema gap between
   the ingest prompt and the ACF definition.

2. **`ws_aorg_accepts_anonymous` appears in two tabs** — Currently exists in Contact & Intake
   and Eligibility & Cost in this draft. The ACF fields for these tabs are the same post meta key.
   The duplication is intentional for editorial workflow (the field matters in both contexts),
   but only one ACF field definition should be registered. Recommend registering in Contact tab,
   surfacing read-only derived display in Eligibility tab via custom column or admin notice hook.
   Alternatively, register once in Eligibility tab and reference it conditionally in Contact tab.

3. **`ws_aorg_intake_commitment_class` vs `ws_aorg_intake_commitment` taxonomy** — Two parallel
   approaches are proposed. Recommend implementing the taxonomy (`ws_aorg_intake_commitment`) for
   `tax_query` filtering, with `ws_aorg_intake_commitment_details` as the freetext companion.
   Deprecate the ACF select field approach.

4. **`ws_aorg_bar_state_ids` `save_terms => 0`** — This field is taxonomy-based for display/filtering
   but saving terms would pollute the `ws_jurisdiction` taxonomy with attorney-bar data that doesn't
   represent the org's service jurisdiction. Use `save_terms => 0` and rely on the `_ws_aorg_bar_state_ids`
   hidden field for ingest-side cross-reference.

5. **`ws_excluded_class` taxonomy not attached to `ws-assist-org`** — The taxonomy is registered on
   `jx-statute, jx-common-law, jx-citation, jx-construction` in v3.16.0. Extend object_types to
   include `ws-assist-org` to support `ws_aorg_excluded_classes` field above. This requires a
   bump in `register-taxonomies.php`.

6. **`ws_aorg_cultural_focus` taxonomy is new** — Requires full implementation: `register_taxonomy()`,
   seed function, gate. See Taxonomy Extensions section above.

---

## Notes

- Edge-case coverage is the explicit design goal. A field that is empty 99% of the time still
  has a 1% hit rate across hundreds of organizations — and that 1% is exactly the kind of nuance
  this directory exists to capture.
- The Utah-Arabic-French-clothing-optional org maps cleanly: `ws_aorg_jurisdictions` → [ut],
  `ws_aorg_cultural_community_scope` → "Arabic-speaking whistleblowers", `ws_aorg_cultural_focus`
  → [arab-middle-eastern], `ws_aorg_languages` → [arabic, french], `ws_aorg_primary_language_only`
  → true (or false if English is also available), `ws_aorg_language_access_notes` → "French is
  the primary operational language; English materials available on request",
  `ws_aorg_policy_flags` → [clothing-optional], `ws_aorg_mission_verbatim` → [verbatim quote].
- Ambiguity is always a `has-details` sentinel plus a `*_details` companion. Treat ambiguity
  as a review-state, not a null state.
- This draft does not modify `acf-assist-orgs.php` directly. New fields require implementation
  passes against the existing file.
