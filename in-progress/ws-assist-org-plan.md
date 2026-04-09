## ws-core Codex Session — Assist Org Schema Pass

### `ws-schema-constants.php` — new file, `includes/admin/tools/`
- Full file drafted this session — use that as the base
- Add `WS_SCHEMA_PHONE_TYPE`: `hotline`, `intake`, `headquarters`, `regional`, `tty`, `fax`, `other`
- Add `WS_SCHEMA_EMAIL_TYPE`: `intake`, `general`, `legal`, `media`, `support`, `other`
- Both constants noted as shared across `ws-assist-org` and `ws-agency`
- Add to loader.php tools block before `tool-ingest` and `tool-generate-prompt`

---

### `acf-assist-orgs.php`
- Version → 3.16.0
- `ws_aorg_official_name` text field added to Identity tab immediately after `ws_aorg_internal_id` — full official name, required, authoritative data-layer source, post_title mirrors it at ingest *(scaffolded this session, verify and complete)*
- `ws_aorg_phone` flat field → `ws_aorg_phones` ACF repeater, sub-fields: `ws_aorg_phone_type` (text, from `WS_SCHEMA_PHONE_TYPE`) and `ws_aorg_phone_number` (text)
- `ws_aorg_email` flat field → `ws_aorg_emails` ACF repeater, sub-fields: `ws_aorg_email_type` (text, from `WS_SCHEMA_EMAIL_TYPE`) and `ws_aorg_email_address` (email)
- Mailing address stays flat
- `has_more_info` bool — do not add; replaced by plain English reviewed workflow

---

### `acf-plain-english-fields.php`
- Add `ws-assist-org` to group location rules
- Docblock note: all four plain English fields are meaningful for this CPT. `ws_plain_english_wysiwyg` serves as the public-facing enriched description — vehicle for glossary tooltips, inline links, contextual notes beyond structured fields. `ws_plain_english_reviewed` gates the directory card "More about this organization" link. `ws_has_plain_english` should default to on for assist orgs. Semantic reuse is intentional and documented here.

---

### `register-taxonomies.php`
- `ws_aorg_cost_model`: remove `mixed`, add `unclear` — "Cannot confirm cost structure"
- `ws_aorg_service`: add `secure-drop`, `mental-health`, `peer-support` *(confirm these landed from earlier session)*
- `ws_aorg_type`: `mixed` already added this session — verify present
- Bump gate versions for any modified seeders

---

### `tool-ingest.php`
- Version → 3.16.0
- `organization_name` double-write: `post_title` via `wp_insert_post()` AND `ws_aorg_official_name` via `update_post_meta()` *(scaffolded this session, verify)*
- `assistance_types` → `assistance_type` in allowed keys and field map *(done this session, verify)*
- `coverage_exceptions` → `jurisdiction_exceptions` in allowed keys; remapped to `ws_aorg_jurisdiction_exceptions` (was incorrectly mapped to `ws_aorg_eligibility_notes`) *(done this session, verify)*
- Flat `phone` and `contact_email` field map entries → repeater writers for `ws_aorg_phones` and `ws_aorg_emails`; ingest loops JSON arrays and writes ACF repeater rows
- `nationwide_example`, `case_stage_details`, `_review_notes` moved from `omit` to new `seed` type in field map
- `seed` type: accumulated post-loop, assembled into final `post_content` via `wp_update_post()` after field map loop completes. Format:
```
[general_description]

---
Nationwide scope note: [nationwide_example]

---
Case stage notes: [case_stage_details]

---
Researcher notes: [_review_notes]
```
Each block conditional on non-empty. `general_description` written at `wp_insert_post()` time as before; seed blocks appended via `wp_update_post()` after loop.
- Abbreviation pass expansion in `ws_ingest_build_assist_org_internal_id()`:
  - `&` → stripped directly (not expanded to `and`)
  - Stop words stripped before abbrev pass: `and`, `the`, `for`, `of`, `in`, `at`, `to`, `a`, `an`
  - New abbrev rules: `global` → `intl`, `whistleblowing` → `wb`, `coalition/s` → `coal`, `alliance/s` → `all`, `committee/s` → `cmte`, `council/s` → `cncl`, `institution/s` → `inst`, `institute/s` → `inst`, `bureau/s` → `bur`, `office/s` → `ofc`, `employee/s` → `emp`, `employment` → `emp`, `protection/s` → `prot`, `advocacy` → `adv`, `rights` → `rts`, `public` → `pub`, `policy` → `pol`, `educational` → `edu`, `education` → `edu`, `research` → `rsch`, `network/s` → `net` *(already present, verify plural form)*
  - All existing rules: verify plural variants are covered with `s?` or `s` patterns

---

### `tool-generate-prompt.php`
- `assistance_types` → `assistance_type` throughout — single-value, maps to `ws_aorg_type` radio
- `coverage_exceptions` → `jurisdiction_exceptions` throughout
- `ws_aorg_cost_model` taxonomy table description: remove `mixed` slug, add `unclear` slug, add note "field is multi-select — tag all cost models that apply"
- `has_secure_drop` — do not add as a field; derived from `secure-drop` presence in `services_provided` taxonomy array; document this in the taxonomy table description for `ws_aorg_service`
- Duplicate field description block: remove top "PER RECORD.KEY DETAILS" bullets entirely
- Replace with clean three-part structure:
  1. **Permissible Omit table** — key names only, no explanations
  2. **Required table** — key name + controlled vocabulary or constraint on one line where short enough (`yes | no | unclear`, `0 to 3`, `verified | redirects | unverified`, `true | false`); anything longer lives only in inline definitions
  3. **Inline field definitions** — full authoritative list, unchanged detail level
- `phone` flat field → `phones` array schema: `[{"type": "hotline", "number": ""}]`; type vocabulary from `WS_SCHEMA_PHONE_TYPE`; document that `hotline` is the priority type for Maya/James routing
- `contact_email` flat field → `emails` array schema: `[{"type": "intake", "address": ""}]`; type vocabulary from `WS_SCHEMA_EMAIL_TYPE`; document that `media` type is suppressed on directory card, surfaced on full profile only
- `nationwide_example`, `case_stage_details`, `_review_notes` — add note to permissible-omit list that these fields are appended to the editorial seed (`post_content`) at ingest; they are not written to meta but are not discarded

---

### `query-directory.php`
- `ws_q_build_assist_org_row()`:
  - Flat `ws_aorg_phone` → ACF repeater read for `ws_aorg_phones`, returns structured array of `[type, number]` rows
  - Flat `ws_aorg_email` → ACF repeater read for `ws_aorg_emails`, returns structured array of `[type, address]` rows
  - Add `has_extended_profile` key: `(bool) $org['plain']['is_reviewed']` — true when plain English reviewed toggle is on; gates the full profile link in render layer

---

### `render-directory.php`
- Directory card phone display: prioritize `hotline` type; show first matching `hotline` or `intake` entry; suppress `headquarters` and `fax` on card
- Directory card email display: show `intake` and `legal` types; suppress `media` and `general` on card
- Conditional quiet text link gated on `$org['has_extended_profile'] === true`: "More about this organization" — small, de-emphasized, not a button, links to `ws-assist-org` single permalink
- Note in docblock: full profile link requires assist org assembler to exist; link is safe to add now as the assembler is a known pending item

---

That's everything. Good session.