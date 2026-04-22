# includes/shortcodes/

Shortcode registration files. Assembly Layer — loaded only on the
frontend (`! is_admin()`).

This directory is the primary contributor entry point. Both files
are fully documented with `@param`, query return shapes, and
plain-english notes on behavior and constraints.

---

## Files

| File | Shortcodes |
|---|---|
| `shortcodes-jurisdiction.php` | `[ws_jx_header]`, `[ws_jx_summary]`, `[ws_jx_statutes]`, `[ws_jx_citation]`, `[ws_jx_construction_]`, `[ws_jx_limitations]` |
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

## Shared Payload Glossary: query-shared.php

`plain`, `verify`, and `author` are payload blocks returned by query-layer
dataset functions.

### Payload: `author`

- `created_by`, `created_by_name`, `created_date`
- `edited_date`, `edited_by`, `edited_by_name`

### Payload: `plain`

- `has_content`, `plain_content`, `written_by`, `written_by_name`, `written_date`
- `is_reviewed`, `reviewed_by`, `reviewed_by_name`, `reviewed_date`

### Payload: `verify`

- `source_method`, `source_name`
- `verified_by`, `verified_by_name`, `verified_date`, `verify_status`
- `needs_review`

---

## Agencies Query Glossary: query-agencies.php

### `ws_get_agency_data( $jx_term_id )`
Returns `array<int,agency>`:

- `id`, `title`, `url`, `status`
- `code`, `name`, `logo`
- `acronym`, `mission`,
- `disclosure_type`, `disclosure_targets`, `process_type`, `jurisdictions`
- `website_url`, `reporting_url`, `phone`
- `confidentiality_details`, `has_anonymous`
- `has_reward`, `reward_details`
- `languages`, `additional_languages`
- `last_reviewed`
- `plain` (payload), `verify` (payload), `author` (payload)

### `ws_get_agency_procedure( $procedure_id )`
Returns one normalized agency procedure row (or `[]` if not found):

- `id`, `title`, `url`
- `agency_id`, `agency_name`, `agency_url`
- `acronym`, `mission`,
- `type`
- `jurisdiction`, `jurisdiction_slugs`
- `disclosure_types`, `disclosure_type_slugs`
- `statute_ids`, `comlaw_ids`
- `entry_point`, `intake_url`, `phone`, `identity_policy`
- `intake_only`, `deadline_days`, `clock_start`
- `has_prereqs`, `prereq_details`
- `walkthrough`, `exclusivity_details`
- `parent_override`, `last_reviewed`
- `author` (payload)

### `ws_get_agency_procedures( $agency_id )`
Returns `array<int,row>` using the exact row shape from `ws_get_agency_procedure()`.

### `ws_get_procedures_for_statute( $statute_id )`
Returns statute-linked procedure rows:

- `id`, `title`, `url`
- `type`
- `agency_id`, `agency_name`, `agency_url`
- `acronym`, `mission`,
- `statute_ids`, `comlaw_ids`
- `deadline_days`, `intake_only`

---

## Directory Query Glossary: query-directory.php

### `ws_get_assist_org_data( $jx_term_id )`
Returns `array<int,row>` using the exact row shape from `ws_q_build_assist_org_row()`.

### `ws_get_nationwide_assist_org_data( $filters = [] )`
Returns `array<int,row>` using the exact row shape from `ws_q_build_assist_org_row()`.

### `ws_q_build_assist_org_row( $oid )`
Returns one complete assist-org row:

- `id`, `title`, `url`, `status`
- `official_name`, `common_name`
- `type`, `type_label`
- `description`
- `whistleblower_scope`, `whistleblower_scope_details`
- `logo`
- `nationwide_flag`, `federal_only`
- `has_limited_scope`, `community_scope`
- `disclosure_types`, `disclosure_type_labels`
- `disclosure_targets`, `disclosure_target_labels`, `disclosure_target_details`
- `protected_class`, `protected_class_labels`, `protected_class_details`
- `case_stages`, `case_stage_labels`, `case_stage_details`
- `process_types`, `process_type_labels`
- `services`, `service_labels`, `additional_services`
- `employment_sectors`, `employment_sector_labels`
- `website_url`, `intake_url`, `contact_url`
- `phones` (`[{type,value}]`), `emails` (`[{type,value}]`)
- `has_secure_channel`, `secure_contact_url`, `secure_contact_tool`, `secure_contact_tool_other`
- `mailing_address`
- `languages`, `language_labels`, `additional_languages`
- `cost_model`, `cost_model_labels`
- `has_income_limit`, `income_eligibility_required`, `income_limit_details`
- `has_anonymous`
- `eligibility_details`
- `has_attorneys`
- `accreditation`, `bar_states`
- `legitimacy_url`, `last_reviewed`
- `jurisdictions`, `jurisdiction_labels`
- `has_extended_profile`
- `taxonomies`
- `plain` (payload), `verify` (payload), `author` (payload)

---

## General Query Glossary: query-general.php

### `ws_get_legal_updates_data( $jx_id = 0, $count = 0 )`
Returns `array<int,update>`:

- `id`, `title`
- `effective_date`, `post_date`
- `type`, `multi_jurisdiction`
- `law_name`
- `source_url`, `source_url_is_pdf`
- `summary`
- `verify` (payload), `author` (payload)

### `ws_get_ref_materials( $post_id )`
Returns `array<int,reference>`:

- `title`
- `url`, `is_pdf`
- `description`
- `type`
- `source_name`

### `ws_get_reference_page_data( $parent_post_id )`
Returns `array|null`:

- `parent_title`
- `parent_url`
- `references` (from `ws_get_ref_materials()`)

---

## Jurisdiction Query Glossary: query-jurisdiction.php

### `ws_get_jurisdiction_data( $input = null )`
Returns `array|false`:

- `id`, `name`, `class`, `code`, `jx_term_id`
- `flag` (`image_url`, `attribution`, `source_url`, `license`)
- `gov` (`portal_url`, `portal_label`, `executive_url`, `executive_label`, `authority_url`, `authority_label`, `legislature_url`, `legislature_label`)
- `author` (payload)

### `ws_get_jurisdiction_index_data()`
Returns:

- `items` (`array<int,{name,code,type,url}>`)
- `counts` (`all`, `state`, `territory`, `district`, `federal`)

### `ws_get_jx_citation_data( $jx_term_id )`
Returns `array<int,row>`:

- `id`, `title`, `url`, `status`, `content`, `is_fed`
- `types`, `disclosure_type`
- `official_name`, `common_name`, `label`
- `cite_url`, `summary`, `is_pdf`
- `protected_class`, `protected_class_details`
- `disclosure_targets`, `disclosure_target_details`
- `adverse_action`, `adverse_action_details`
- `process_type`
- `remedies`, `remedy_details`
- `fee_shifting`
- `employer_defense`, `employer_defense_details`
- `employee_standard`, `employee_standard_details`
- `statute_ids`, `comlaw_ids`
- `attach_flag`, `order`, `last_reviewed`
- `ref_materials`
- `plain` (payload), `verify` (payload), `author` (payload)

### `ws_get_jx_common_law_data( $jx_term_id )`
Returns `array<int,row>`:

- `id`, `title`, `url`, `status`, `content`, `order`
- `doctrine_name`, `doctrine_id`, `common_name`
- `precedent_url`, `precedent_url_is_pdf`
- `public_policy_sources`, `other_sources`
- `doctrine_basis`, `recognition_status`
- `disclosure_type`
- `protected_class`, `protected_class_details`
- `disclosure_targets`, `disclosure_target_details`
- `adverse_action_scope`, `attach_flag`
- `sol_value`, `sol_unit`, `sol_trigger`, `has_sol`, `sol_details`
- `has_tolling`, `tolling_details`
- `has_exhaustion`, `exhaustion_details`
- `process_type`
- `adverse_action`, `adverse_action_details`
- `fee_shifting`
- `remedies`, `remedy_details`
- `related_agencies`
- `has_preclusion`, `statutory_preclusion_details`
- `employee_standard`, `employee_standard_details`
- `employer_defense`, `employer_defense_details`
- `has_rebuttable`, `rebuttable_details`
- `has_bop`, `bop_details`, `bop_flag`
- `has_reward`, `reward_details`
- `citation_ids`, `construction_ids`
- `ref_materials`
- `plain` (payload), `verify` (payload), `author` (payload)

### `ws_get_jx_construction_data( $jx_term_id )`
Returns `array<int,row>`:

- `id`, `title`, `url`, `status`, `content`, `order`, `is_fed`
- `official_name`, `common_name`
- `citation`
- `opinion_url`, `opinion_url_is_pdf`
- `court`, `year`, `is_favorable`
- `summary`
- `disclosure_type`
- `protected_class`, `protected_class_details`
- `disclosure_targets`, `disclosure_target_details`
- `adverse_action`, `adverse_action_details`
- `process_type`
- `remedies`, `remedy_details`
- `fee_shifting`
- `employer_defense`, `employer_defense_details`
- `employee_standard`, `employee_standard_details`
- `parent_statute_id`, `parent_comlaw_id`
- `affected_jx`
- `attach_flag`, `last_reviewed`
- `ref_materials`
- `plain` (payload), `verify` (payload), `author` (payload)

### `ws_get_jx_statute_data( $jx_term_id )`
Returns `array<int,row>`:

- `id`, `title`, `url`, `status`, `content`, `order`, `is_fed`
- `official_name`, `citation`, `common_name`
- `disclosure_type`
- `protected_class`, `protected_class_details`
- `disclosure_targets`, `disclosure_target_details`
- `adverse_action_scope`, `attach_flag`
- `sol_value`, `sol_unit`, `sol_trigger`, `has_sol`, `sol_details`
- `has_tolling`, `tolling_details`
- `has_exhaustion`, `exhaustion_details`
- `process_type`
- `adverse_action`, `adverse_action_details`
- `fee_shifting`
- `remedies`, `remedy_details`
- `local_agencies`, `federal_agencies`
- `enforcement_channel`
- `citation_ids`
- `employee_standard`, `employee_standard_details`
- `employer_defense`, `employer_defense_details`
- `has_rebuttable`, `rebuttable_details`
- `has_bop`, `bop_details`, `bop_flag`
- `has_reward`, `reward_details`
- `statute_url`, `url_is_pdf`
- `last_reviewed`
- `ref_materials`
- `plain` (payload), `verify` (payload), `author` (payload)

### `ws_get_jx_summary_data( $jx_term_id )`
Returns `array|false`:

- `id`, `title`, `url`, `status`
- `content`, `sources`, `limitations`
- `plain_english_reviewed`
- `plain` (payload), `verify` (payload), `author` (payload)
