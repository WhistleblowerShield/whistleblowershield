# includes/shortcodes/

Shortcode registration files. Assembly Layer — loaded only on the
frontend (`! is_admin()`).

This directory is the primary contributor entry point. Both files
are fully documented with `@param`, query return shapes, and
plain-language notes on behavior and constraints.

---

## Files

| File | Shortcodes |
|---|---|
| `shortcodes-jurisdiction.php` | `[ws_jx_header]`, `[ws_jx_summary]`, `[ws_jx_statutes]`, `[ws_jx_flag]`, `[ws_jx_citation]`, `[ws_jx_interpretation]`, `[ws_jx_limitations]` |
| `shortcodes-general.php` | `[ws_nla_disclaimer_notice]`, `[ws_footer]`, `[ws_legal_updates]`, `[ws_reference_page]`, `[ws_jurisdiction_index]`, `[ws_assist_org_directory]` |

See each file for complete `@param` documentation, attribute
descriptions, and query return shape glossary.

---

## The Shortcode Contract

Shortcodes are presentation wrappers only.

- Never call `get_field()`, `get_post_meta()`, or `WP_Query` directly
- Call the appropriate query layer function
- Pass the result to a render function
- Return the rendered HTML string

A shortcode that bypasses the query layer is a violation of the
plugin's core architectural rule. If the query layer doesn't return
the data you need, extend the query layer — don't add a direct read
to the shortcode.

---

## Assist-Org Query Glossary

`ws_q_build_assist_org_row()` in `includes/queries/query-directory.php`
returns a complete assist-org data object. Primary contributor-facing keys:

- `id`, `title`, `url`, `status`
- `internal_id`, `official_name`, `common_name`
- `description`, `whistleblower_scope`, `whistleblower_note`
- `type` (slug), `type_label`
- `serves_nationwide`, `federal_only`, `limited_scope`, `community_scope`
- `website_url`, `intake_url`, `contact_url`
- `phones` (`[{type,value}]`), `emails` (`[{type,value}]`)
- `has_secure_channel`, `secure_contact_url`, `secure_contact_tool`
- `income_eligibility_required`, `income_limit_notes`, `eligibility_notes`
- `anonymous_pre_consult_possible`, `has_attorneys`
- `legitimacy_url`, `last_reviewed`
- `jurisdictions`, `jurisdiction_labels`
- `disclosure_types`, `disclosure_type_labels`
- `disclosure_targets`, `disclosure_target_labels`, `disclosure_targets_details`
- `case_stages`, `case_stage_labels`
- `process_types`, `process_type_labels`
- `service_slugs`, `services` (display labels)
- `employment_sectors`, `employment_sector_labels`
- `languages`, `language_labels`, `additional_languages`
- `cost_model`, `cost_model_labels`

Complete payload blocks:

- `meta` — full assist-org meta snapshot (including internal relationship fields)
- `taxonomies` — normalized taxonomy bundles (`ids`, `slugs`, `names`)
- `verify` — source verification workflow block
- `record` — authored/edited stamp block
- `plain` — plain-English workflow block
