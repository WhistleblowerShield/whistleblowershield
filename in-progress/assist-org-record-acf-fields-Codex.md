# Assist-Org Record ACF Canonical Field Draft

Purpose: draft a unified, prefix-free field set for `ws-assist-org` records,
using the legal-record ACF rules as the naming and conditional logic baseline.

Assist-org records answer a different question than legal records:

> Can this person safely and realistically get help from this organization now?

That makes contact accuracy, intake safety, service fit, eligibility, and
verification first-class data. The schema below keeps the current taxonomy
registry as source of truth where possible and proposes extensions only where
the current tables cannot express routing-critical facts.

---

## Naming Rules

Use the legal-record naming rules unless this draft says otherwise.

**Casing**
- Meta names: `snake_case` only.
- Choice keys: `kebab-case` only.

**CPT infix** - absent from this draft; applied at registration.

CPT slot value: `aorg`

- `name` (meta key): `ws_aorg_*`
- field `key`: `field_aorg_*`
- tab field `key`: `field_aorg_{tab}_tab`
- group `key`: `group_aorg_metadata`
- group `menu_order`: < 85 - workflow groups occupy 85-99.

**Reserved prefixes**
- `ws_auto_` - written exclusively by hook logic. Never use on content fields.
- `_` - hidden/internal field. Use only for ingest IDs, derived fields, and internal operations data.

**Cardinality**
- Single-value fields: singular noun.
- Multi-value fields: plural noun.

**Booleans**
- `has_*` - trigger boolean. True activates a companion or dependent field.
- `is_*` or `*_is_*` - state boolean. Describes a condition; does not imply a companion unless documented inline.

**Default field types**
- `has_*` / `is_*` / `*_is_*` -> boolean unless a ternary is explicitly declared.
- `*_class`, `*_scope`, `*_status`, `*_rule`, `*_framework`, `*_weight`, `*_standard` -> select.
- `*_share` -> portion of a reward or fee share.
- `*_formula` -> mandated or stated calculation.
- `*_bar` -> claim-blocking doctrine, access blocker, or procedural blocker.
- `*_channel` -> contact, intake, or disclosure path.
- `*_model` -> cost, service, or organizational model.
- `*_url`, `*_date`, `*_email`, `*_phone`, `*_year` -> matching data shape.
- select -> single-select; multi-select must be stated.
- all others -> freetext.

**Companions**
- `*_details` - freetext companion triggered by `has_*` boolean or `has-details` sentinel.
- `*_context` - freetext companion triggered by a named field value, any non-empty value, or a taxonomy term.
- Annotation is required when trigger and companion names do not make the relationship obvious.
- `*_context` fields must declare their trigger field and value.

**Sister fields**

A sister field shares another field's conditional visibility but is not itself a
companion field.

- No naming convention applies to sisters.
- Sister fields should name the data they hold, not the trigger.
- A sister may not appear without a corresponding companion or context anchor in the same cluster.

**Avoid**
- `*_type` - prefer `*_class`, `*_scope`, `*_status`, `*_model`, `*_channel`, `*_service`, or `*_category` when they fit. `type` is allowed when it is truly the least-bad name.
- `*_limitations` - use `*_limits`.
- `*_recognized` - not meaningful for assist-org records.

---

## Sentinel Values

**Trigger sentinels**
- `has-details` - triggers the `*_details` companion for the field it appears in.
- `additional` - in `ws_language` and `ws_aorg_service`; triggers the annotated additional-detail companion.
- `unclear` - valid taxonomy/choice value only where the registered taxonomy or field explicitly defines it.

**Redirect sentinels**
- `see-details` - detail field for this context is already active.
- `see-context` - context field for this context is already active.

**Assist-org operational sentinels**
- `not-stated` - source does not state the answer.
- `not-applicable` - field does not logically apply.

Use `not-stated` only in local select fields, not in registry taxonomies unless the taxonomy defines it.

---

## Attached Workflow Groups

Shared workflow groups attach alongside the assist-org group. Do not duplicate
these fields in the CPT-specific ACF file.

| Group key | `menu_order` | Tab label | Fields added |
|---|---:|---|---|
| `group_plain_english_metadata` | 85 | Plain-English | `ws_has_plain_english`, `ws_plain_english_wysiwyg`, `ws_plain_english_reviewed`, 4 `ws_auto_` stamps |
| `group_auto_stamp_metadata` | 90 | Authorship & Review | `ws_auto_create_date`, `ws_auto_create_author`, `ws_auto_last_edited_date`, `ws_auto_last_edited_author` |
| `group_source_verify_metadata` | 95 | Source & Verification | `ws_auto_source_method`, `ws_auto_source_name`, `ws_auto_verified_by`, `ws_auto_verified_date`, `ws_verification_status`, `ws_needs_review` |
| `group_major_edit_metadata` | 99 | Major Edit | `ws_is_major_edit`, `ws_major_edit_description`, `ws_major_edit_update_type` |

---

## Default Taxonomy Field Settings

Unless stated otherwise:

- Field type: taxonomy
- Field UI: multi-select
- `load_terms`: 1
- `save_terms`: 1

Single-select taxonomy fields must be stated explicitly.

---

## Conditional Annotation Phrasing

Accepted forms:

- Taxonomy term present: `conditional on slug in taxonomy_field`
- Any non-empty value: `conditional on trigger_field is non-empty`
- Specific value in select field: `conditional on trigger_field is trigger_value`
- Specific value in multi-select field: `conditional on trigger_field includes trigger_value`

Compound conditions: AND / OR / NOT.

---

## Hook Requirements

**General**
- Derived fields auto-fill on load and save.
- Merged hidden fields auto-fill on save.
- URL status date fields fill from ingest/verification date when status is `verified`.
- Registered taxonomy slugs are source of truth; ingest rejects invalid taxonomy slugs.
- Public render suppresses internal fields and any contact row marked `do-not-publish`.

**Safety guards**
- `intake_url` must not be populated from leak-drop/tip-submission URLs.
- `secure_channel_status` must not treat normal HTTPS forms as secure personal-assistance channels.
- SecureDrop/GlobaLeaks can support anonymous submission, but do not automatically prove personal assistance.
- `public_directory_status` cannot become `publish-ready` unless identity, homepage, service fit, at least one usable public contact path, and verification status are complete.
- `organization_status` not `active` blocks public recommendation.
- `intake_commitment_class` of `leak-drop-only`, `information-only`, or `unclear` blocks crisis-routing recommendation unless a public warning is shown.
- `whistleblower_fit` of `none` or `unclear` blocks normal routing and requires `whistleblower_fit_details`.
- `services` includes `unclear` requires review notice.

**Auto-assignment**
- `language_details` non-empty may auto-assign `additional` in `languages`.
- `service_details` non-empty may auto-assign `additional` in `services` when no better term fits.
- `is_nationwide` true may derive from `jurisdictions` containing `us` or all jurisdictions, depending on implementation policy.
- `_effective_jurisdictions` derives from `jurisdictions`, `is_nationwide`, and `jurisdiction_exceptions`.
- `_has_public_contact` derives from public phone/email/contact form/intake URL rows.
- `_has_verified_intake` derives from verified `intake_url` or verified intake-class contact row.
- `_has_secure_personal_assistance` derives from `secure_channel_status` and `intake_commitment_class`.

---

## Field Tabs

Field order follows the editorial workflow.

1. Identity & Publishing
2. Service Area
3. Service Fit
4. Intake & Contact
5. Eligibility & Cost
6. Safety & Privacy
7. Legal Capacity
8. Trust & Verification
9. Relationships
10. Source / Audit
11. Internal Operations
12. Hidden Fields

---

## Identity & Publishing Tab

Fields ordered: identity -> classification -> public display -> directory status.

- `official_name`
- `common_name`
- `alternate_names`              - repeater:
    - `alternate_name`
    - `alternate_name_notes`
- `official_homepage_url`         - URL; official domain.
- `homepage_url_status`           - select: `verified`|`redirects`|`unverified`|`dead`
- `homepage_verified_date`        - conditional on `homepage_url_status` is `verified`
- `organization_status`           - select: `active`|`inactive`|`merged`|`closed`|`unclear`|`has-details`
- `organization_status_details`
- `logo`
- `general_description`
- `assistance_class`              - single-select taxonomy: `ws_aorg_type`
- `assistance_class_details`      - conditional on `assistance_class` is `mixed`
- `public_directory_status`       - select: `draft-review`|`publish-ready`|`needs-verification`|`temporarily-hidden`|`do-not-publish`|`has-details`
- `public_directory_status_details`
- `display_priority`              - integer
- `has_attach_flag`
- `display_order`                 - conditional on `has_attach_flag` is true

---

## Service Area Tab

Fields ordered: geography -> evidence -> access mode -> offices.

- `is_nationwide`                 - bool; true when organization serves the United States broadly.
- `jurisdictions`                 - taxonomy: `WS_JURISDICTION_TAXONOMY`
- `jurisdiction_exceptions`
- `service_area_scope`            - select: `nationwide`|`multi-state`|`single-state`|`regional`|`local`|`virtual-only`|`unclear`|`has-details`
- `service_area_scope_details`
- `nationwide_evidence_quote`
- `service_area_source_url`
- `virtual_service_status`        - select: `available`|`not-available`|`unclear`|`has-details`
- `virtual_service_status_details`
- `in_person_service_status`      - select: `available`|`appointment-only`|`not-available`|`unclear`|`has-details`
- `in_person_service_status_details`
- `has_physical_offices`
- `offices`                       - conditional on `has_physical_offices` is true; repeater:
    - `office_name`
    - `office_scope`              - select: `headquarters`|`regional`|`local`|`clinic`|`mailing-only`|`has-details`
    - `office_jurisdiction`       - taxonomy: `WS_JURISDICTION_TAXONOMY`; `save_terms`: 0
    - `office_address`
    - `office_phone`
    - `office_email`
    - `office_url`
    - `office_scope_details`

---

## Service Fit Tab

Fields ordered: whistleblower fit -> services -> subject matter -> people served -> process/stage.

- `whistleblower_fit`             - select: `primary-focus`|`significant-program`|`adjacent-help`|`not-specific`|`none`|`unclear`|`has-details`
- `whistleblower_fit_details`
- `whistleblower_evidence_quote`
- `services`                      - taxonomy: `ws_aorg_service`
- `service_details`               - conditional on `services` includes `additional`
- `service_limit_context`         - conditional on `services` includes `unclear`
- `service_depth`                 - select: `information-only`|`triage-only`|`brief-advice`|`document-review`|`limited-scope-help`|`direct-representation`|`referral-only`|`warm-handoff`|`ongoing-support`|`unclear`|`has-details`
- `service_depth_details`
- `intake_commitment_class`       - select: `personal-help-request`|`screening-form`|`referral-request`|`general-contact-only`|`leak-drop-only`|`information-only`|`unclear`|`has-details`
- `intake_commitment_class_details`
- `protected_disclosures`         - taxonomy: `ws_protected_disclosure`
- `protected_classes`             - taxonomy: `ws_protected_class`
- `protected_class_details`
- `employment_sectors`            - taxonomy: `ws_employment_sector`
- `case_stages`                   - taxonomy: `ws_case_stage`
- `case_stage_details`
- `process_types`                 - taxonomy: `ws_process_type`
- `disclosure_targets`            - taxonomy: `ws_disclosure_target`
- `disclosure_target_details`
- `best_fit_notes`
- `poor_fit_notes`

---

## Intake & Contact Tab

Fields ordered: intake status -> URLs -> contact rows -> leak drops -> response expectations.

- `intake_status`                 - select: `open`|`limited`|`waitlist`|`seasonal`|`closed`|`unclear`|`has-details`
- `intake_status_details`
- `intake_url`                    - URL; direct personal-assistance request path only.
- `intake_url_status`             - select: `verified`|`redirects`|`unverified`|`dead`
- `intake_verified_date`          - conditional on `intake_url_status` is `verified`
- `contact_url`
- `contact_url_status`            - select: `verified`|`redirects`|`unverified`|`dead`
- `contact_verified_date`         - conditional on `contact_url_status` is `verified`
- `phones`                        - repeater:
    - `phone_channel`             - select: `hotline`|`intake`|`main`|`regional`|`tty`|`fax`|`secure`|`media`|`internal-only`|`has-details`
    - `phone_number`
    - `phone_hours`
    - `phone_language_notes`
    - `phone_publication_status`  - select: `public`|`internal-only`|`do-not-publish`
    - `phone_verified_date`
    - `phone_channel_details`
- `emails`                        - repeater:
    - `email_channel`             - select: `intake`|`general`|`legal`|`support`|`secure`|`media`|`internal-only`|`has-details`
    - `email_address`
    - `email_publication_status`  - select: `public`|`internal-only`|`do-not-publish`
    - `email_verified_date`
    - `email_channel_details`
- `contact_forms`                 - repeater:
    - `form_url`
    - `form_scope`                - select: `intake`|`consultation-request`|`referral-request`|`general-contact`|`complaint-reporting`|`media-tip`|`unclear`|`has-details`
    - `form_requires_identity`    - select: `yes`|`no`|`unclear`
    - `form_verified_date`
    - `form_scope_details`
- `leak_drop_urls`                - repeater:
    - `leak_drop_url`
    - `leak_drop_tool`            - select: `securedrop`|`globaleaks`|`custom`|`has-details`
    - `leak_drop_tool_details`
- `mailing_address`
- `expected_response_time`        - select: `same-day`|`one-to-three-days`|`one-week`|`over-one-week`|`not-stated`|`unclear`|`has-details`
- `expected_response_time_details`
- `after_hours_status`            - select: `available`|`not-available`|`unclear`|`has-details`
- `after_hours_status_details`
- `emergency_help_status`         - select: `not-emergency-service`|`limited-urgent-help`|`urgent-hotline`|`unclear`|`has-details`
- `emergency_help_status_details`

---

## Eligibility & Cost Tab

Fields ordered: eligibility -> screening -> costs -> languages/access.

- `eligibility_status`            - select: `open-to-public`|`screening-required`|`restricted`|`members-only`|`referral-only`|`unclear`|`has-details`
- `eligibility_status_details`
- `income_screening`              - select: `required`|`not-required`|`possible`|`unclear`|`has-details`
- `income_screening_details`
- `identity_screening`            - select: `required`|`not-required`|`possible`|`unclear`|`has-details`
- `identity_screening_details`
- `membership_requirement`        - select: `none`|`union-members-only`|`profession-members-only`|`program-members-only`|`has-details`
- `membership_requirement_details`
- `geographic_eligibility_notes`
- `worker_status_eligibility_notes`
- `conflict_screening_status`     - select: `required`|`not-required`|`possible`|`unclear`|`has-details`
- `conflict_screening_status_details`
- `cost_models`                   - taxonomy: `ws_aorg_cost_model`
- `cost_model_context`            - conditional on `cost_models` includes `unclear`
- `payment_timing`                - select: `no-payment`|`upfront`|`after-recovery`|`mixed`|`unclear`|`has-details`
- `payment_timing_details`
- `languages`                     - taxonomy: `ws_language`
- `language_details`              - conditional on `languages` includes `additional`
- `language_access_rule`          - select: `available`|`preferred`|`required`|`exclusive`|`unclear`|`has-details`
- `language_access_rule_details`
- `interpretation_status`         - select: `available`|`not-available`|`unclear`|`has-details`
- `interpretation_status_details`
- `technology_access_requirements` - multi-select: `phone-ok`|`internet-required`|`video-required`|`document-upload-required`|`unclear`|`has-details`
- `technology_access_requirement_details`

---

## Safety & Privacy Tab

Fields ordered: anonymity -> secure channels -> privacy -> risk warnings.

- `anonymous_pre_consult_status`  - select: `yes`|`no`|`unclear`|`has-details`
- `anonymous_pre_consult_details`
- `confidentiality_claimed`       - select: `yes`|`no`|`unclear`|`has-details`
- `confidentiality_claimed_details`
- `privacy_policy_url`
- `privacy_policy_status`         - select: `verified`|`not-found`|`unverified`|`dead`
- `secure_channel_status`         - select: `dedicated-secure-channel`|`standard-web-form`|`leak-drop-only`|`none-found`|`unclear`|`has-details`
- `secure_channel_status_details`
- `secure_contact_tools`          - multi-select: `signal`|`protonmail`|`tutanota`|`wire`|`keybase`|`pgp-email`|`securedrop`|`globaleaks`|`encrypted-web-form`|`other`
- `secure_contact_tool_details`   - conditional on `secure_contact_tools` includes `other`
- `secure_contact_url`
- `retention_policy_url`
- `retention_policy_notes`
- `mandatory_reporting_status`    - select: `yes`|`no`|`unclear`|`has-details`
- `mandatory_reporting_status_details`
- `risk_warning_notes`
- `public_safety_note`

---

## Legal Capacity Tab

Fields ordered: attorney availability -> representation -> bar/accreditation -> warnings.

- `has_attorneys`                 - select: `yes`|`no`|`unclear`
- `attorney_role`                 - select: `direct-representation`|`consultation-only`|`referral-panel`|`supervised-clinic`|`policy-only`|`unclear`|`has-details`
- `attorney_role_details`
- `legal_representation_status`   - select: `available`|`limited`|`referral-only`|`not-available`|`unclear`|`has-details`
- `legal_representation_status_details`
- `bar_jurisdictions`             - taxonomy: `WS_JURISDICTION_TAXONOMY`; `save_terms`: 0
- `bar_jurisdiction_notes`
- `accreditation`
- `unauthorized_practice_warning` - select: `yes`|`no`|`unclear`|`has-details`
- `unauthorized_practice_warning_details`
- `attorney_client_relationship_status` - select: `may-form`|`does-not-form`|`not-stated`|`unclear`|`has-details`
- `attorney_client_relationship_status_details`
- `privilege_warning_notes`
- `representation_limits`

---

## Trust & Verification Tab

Fields ordered: legitimacy -> verification status -> attempts -> confidence.

- `legitimacy_urls`               - repeater:
    - `legitimacy_url`
    - `legitimacy_source`         - select: `irs`|`guidestar`|`charity-navigator`|`bar-directory`|`court-directory`|`government-directory`|`state-registry`|`news-source`|`has-details`
    - `legitimacy_source_details`
- `tax_exempt_status`             - select: `501c3`|`501c4`|`government`|`for-profit`|`fiscally-sponsored`|`not-stated`|`unclear`|`has-details`
- `tax_exempt_status_details`
- `source_quality`                - select: `official-only`|`official-plus-third-party`|`third-party-only`|`weak`|`has-details`
- `source_quality_details`
- `verification_status`           - select: `verified`|`partially-verified`|`needs-review`|`stale`|`failed`|`has-details`
- `verification_status_details`
- `last_verified_date`
- `next_review_date`
- `verification_frequency`        - select: `monthly`|`quarterly`|`semiannual`|`annual`|`manual-only`
- `verification_attempts`         - repeater:
    - `attempt_date`
    - `attempt_method`            - select: `website`|`email`|`phone`|`directory`|`archive`|`other`
    - `attempt_result`            - select: `verified`|`no-response`|`failed`|`conflicting-info`|`has-details`
    - `attempt_result_details`
- `staleness_warning`             - derived bool
- `editor_confidence`             - select: `high`|`medium`|`low`|`do-not-use`
- `editor_confidence_notes`

---

## Relationships Tab

Fields ordered: internal records -> external networks -> referrals.

- `related_legal_records`         - post object; legal record CPTs; multi-select
- `related_agencies`              - post object; `ws-agency`; multi-select
- `parent_organization_id`        - post object; `ws-assist-org`
- `child_program_ids`             - post object; `ws-assist-org`; multi-select
- `referral_partners`             - repeater:
    - `partner_name`
    - `partner_url`
    - `partner_relationship`      - select: `formal-referral`|`informal-referral`|`coalition`|`funding-partner`|`unclear`|`has-details`
    - `partner_relationship_details`
- `replaced_by_id`                - post object; `ws-assist-org`
- `duplicate_of_id`               - post object; `ws-assist-org`

---

## Source / Audit Tab

Fields ordered: source material -> ingest provenance -> review notes.

- `source_urls`                   - repeater:
    - `source_url`
    - `source_label`
    - `source_class`              - select: `official`|`third-party`|`archive`|`directory`|`news`|`other`
    - `source_verified_date`
    - `source_notes`
- `authority_reference`
- `last_reviewed_date`
- `_review_notes`
- `_reconciled_notes`
- `_researcher_notes`
- `_schema_gap_notes`

---

## Internal Operations Tab

Private operator metadata. Never surface publicly.

- `_internal_contact_name`
- `_internal_contact_role`
- `_internal_contact_email`
- `_internal_contact_phone`
- `_internal_last_contacted_date`
- `_internal_contact_status`      - select: `not-contacted`|`contacted`|`responded`|`confirmed`|`declined`|`bounced`|`has-details`
- `_internal_contact_status_details`
- `_internal_relationship_notes`
- `_editor_owner`
- `_review_priority`              - select: `critical`|`high`|`normal`|`low`
- `_review_blockers`
- `_do_not_contact`
- `_do_not_contact_reason`

---

## Hidden Fields

Fields ordered: id -> derived -> ingest.

- `_id`                           - generated by ingest tool or matrix seeder
- `_effective_jurisdictions`      - derived from `jurisdictions`, `is_nationwide`, and `jurisdiction_exceptions`
- `_has_public_contact`           - derived bool
- `_has_verified_intake`          - derived bool
- `_has_secure_personal_assistance` - derived bool
- `_has_legal_help`               - derived bool
- `_service_fit_score`            - derived numeric score
- `_intake_safety_score`          - derived numeric score
- `_routing_weight`               - derived/manual hybrid score
- `_last_ingest_batch_id`
- `_last_ingest_model`
- `_last_ingest_date`

---

## Proposed Taxonomy Extensions

Use existing registry tables first:

- `WS_JURISDICTION_TAXONOMY`
- `ws_protected_disclosure`
- `ws_protected_class`
- `ws_disclosure_target`
- `ws_process_type`
- `ws_case_stage`
- `ws_language`
- `ws_aorg_type`
- `ws_employment_sector`
- `ws_aorg_cost_model`
- `ws_aorg_service`

Propose the following only if implementation needs stronger filtering than local selects can provide.

### `ws_aorg_accessibility_service`

Practical access supports.

- `tty-relay`
- `video-relay`
- `asl-interpretation`
- `language-interpretation`
- `screen-reader-accessible`
- `mobility-accessible-office`
- `remote-intake`
- `low-tech-access`
- `after-hours-contact`
- `has-details`

### `ws_aorg_service_depth`

How deep the help goes.

- `information-only`
- `triage-only`
- `brief-advice`
- `document-review`
- `limited-scope-help`
- `direct-representation`
- `referral-only`
- `warm-handoff`
- `ongoing-support`
- `unclear`
- `has-details`

Note: this draft currently models service depth as a select to avoid adding a taxonomy before the field proves useful.

### `ws_aorg_service_exclusion`

What the organization explicitly does not provide.

- `no-legal-advice`
- `no-representation`
- `no-emergency-help`
- `no-individual-assistance`
- `no-anonymous-contact`
- `no-financial-aid`
- `no-mental-health-care`
- `no-government-employee-help`
- `no-private-sector-help`
- `has-details`

### Existing Taxonomy Term Gaps

Consider future additions when enough records justify them:

- `ws_language`: `asl`, `farsi`, `urdu`, `somali`, `amharic`, `hmong`, `khmer`
- `ws_case_stage`: `decision-point`, `appeal`, `post-settlement`
- `ws_aorg_service`: `safety-planning`, `translation-interpretation`, `court-accompaniment`, `benefits-navigation`, `training-education`, `research`
- `ws_aorg_cost_model`: `grant-funded`, `membership-based`, `donation-requested`, `court-awarded-fees`

---

## Rename Normalization (Legacy -> Canonical)

Only fields that currently violate target naming conventions, are inconsistent
with the canonical draft, or were structurally redesigned.

- `_ws_agency_id` / `_ws_aorg_id`       -> `_id`
- `ws_agency_official_name` / `ws_aorg_official_name` -> `official_name`
- `ws_agency_common_name` / `ws_aorg_common_name` -> `common_name`
- `ws_agency_logo` / `ws_aorg_logo`     -> `logo`
- `ws_agency_mission` / `ws_aorg_description` -> `general_description`
- `ws_aorg_type`                       -> `assistance_class`
- `ws_agency_url` / `ws_aorg_website_url` -> `official_homepage_url`
- `ws_agency_reporting_url` / `ws_aorg_intake_url` -> `intake_url`
- `ws_aorg_contact_url`                -> `contact_url`
- `ws_agency_phone`                    -> `phones`
- `ws_aorg_phones`                     -> `phones`
- `ws_aorg_emails`                     -> `emails`
- `ws_aorg_mailing_address`            -> `mailing_address`
- `ws_agency_jurisdictions` / `ws_aorg_jurisdictions` -> `jurisdictions`
- `ws_aorg_serves_nationwide`          -> `is_nationwide`
- `ws_aorg_jurisdiction_exceptions`    -> `jurisdiction_exceptions`
- `ws_agency_protected_disclosures` / `ws_aorg_protected_disclosures` -> `protected_disclosures`
- `ws_agency_disclosure_targets` / `ws_aorg_disclosure_targets` -> `disclosure_targets`
- `ws_aorg_disclosure_target_details`  -> `disclosure_target_details`
- `ws_agency_employment_sectors` / `ws_aorg_employment_sectors` -> `employment_sectors`
- `ws_agency_process_types` / `ws_aorg_process_types` -> `process_types`
- `ws_aorg_protected_classes`          -> `protected_classes`
- `ws_aorg_protected_class_details`    -> `protected_class_details`
- `ws_aorg_case_stages`                -> `case_stages`
- `ws_aorg_case_stage_details`         -> `case_stage_details`
- `ws_aorg_services`                   -> `services`
- `ws_aorg_additional_services`        -> `service_details`
- `ws_aorg_cost_models`                -> `cost_models`
- `ws_aorg_has_income_limit`           -> `income_screening`
- `ws_aorg_has_income_limit_details`   -> `income_screening_details`
- `ws_agency_accepts_anonymous` / `ws_aorg_accepts_anonymous` -> `anonymous_pre_consult_status`
- `ws_agency_languages` / `ws_aorg_languages` -> `languages`
- `ws_agency_additional_languages` / `ws_aorg_additional_languages` -> `language_details`
- `ws_aorg_has_secure_channel`         -> `secure_channel_status`
- `ws_aorg_secure_contact_url`         -> `secure_contact_url`
- `ws_aorg_secure_contact_tool`        -> `secure_contact_tools`
- `ws_aorg_secure_contact_tool_other`  -> `secure_contact_tool_details`
- `ws_aorg_licensed_attorneys`         -> `has_attorneys`
- `ws_aorg_accreditation`              -> `accreditation`
- `ws_aorg_bar_states`                 -> `bar_jurisdictions`
- `ws_aorg_legitimacy_url`             -> `legitimacy_urls`
- `ws_agency_last_reviewed` / `ws_aorg_last_reviewed` -> `last_reviewed_date`
- `_ws_aorg_internal_contact_name`     -> `_internal_contact_name`
- `_ws_aorg_internal_contact_role`     -> `_internal_contact_role`
- `_ws_aorg_internal_contact_email`    -> `_internal_contact_email`
- `_ws_aorg_internal_contact_phone`    -> `_internal_contact_phone`
- `_ws_aorg_internal_last_contacted`   -> `_internal_last_contacted_date`
- `_ws_aorg_internal_relationship_notes` -> `_internal_relationship_notes`

---

## Prompt Schema -> ACF Field Mapping

Maps current research JSON into this canonical field model.

```
JSON key                                -> ACF field
identity.
    official_name                       -> official_name
    common_name                         -> common_name
    official_homepage_url               -> official_homepage_url
    homepage_url_status                 -> homepage_url_status
    homepage_url_date                   -> homepage_verified_date
    general_description                 -> general_description

scope.nationwide_example                     -> nationwide_evidence_quote
scope.protected_disclosures                       -> protected_disclosures
scope.protected_classes                      -> protected_classes
scope.protected_class_details                -> protected_class_details
scope.languages_supported                    -> languages
scope.languages_additional                   -> language_details
scope.assistance_type                        -> assistance_class
scope.employment_sectors                     -> employment_sectors
scope.cost_models                            -> cost_models
scope.services_provided                      -> services
scope.additional_services                    -> service_details
scope.process_types                          -> process_types
scope.case_stages                            -> case_stages
scope.case_stage_details                     -> case_stage_details
scope.disclosure_targets                     -> disclosure_targets
scope.disclosure_target_details              -> disclosure_target_details
scope.jurisdiction_exceptions                -> jurisdiction_exceptions
scope.whistleblower_scope                    -> whistleblower_fit
scope.whistleblower_note                     -> whistleblower_evidence_quote

contact.intake_url                           -> intake_url
contact.contact_url                          -> contact_url
contact.phones                               -> phones
contact.emails                               -> emails
contact.mailing_address                      -> mailing_address

eligibility.income_eligibility_required      -> income_screening
eligibility.income_eligibility_details       -> income_screening_details
eligibility.eligibility_notes                -> eligibility_status_details

security.has_secure_channel                  -> secure_channel_status
security.secure_contact_url                  -> secure_contact_url
security.secure_contact_tool                 -> secure_contact_tools
security.secure_contact_tool_other           -> secure_contact_tool_details
security.anonymous_pre_consult_possible      -> anonymous_pre_consult_status
security.has_attorneys                       -> has_attorneys

review.legitimacy_url                        -> legitimacy_urls
review._review_notes                         -> _review_notes
```

---

## Public Display Safeguards

Recommended assist-org display requires:

- `public_directory_status` is `publish-ready`
- `organization_status` is `active`
- `official_homepage_url` present
- `homepage_url_status` is `verified` or `redirects`
- `general_description` present
- At least one public contact path exists
- `intake_commitment_class` is not `leak-drop-only`
- `verification_status` is not `failed`
- `editor_confidence` is not `do-not-use`

Records failing these checks may remain as internal draft/research records.

---

## Notes

- `services` says what the organization claims to provide.
- `service_depth` says how complete or direct that help appears to be.
- `intake_commitment_class` says whether the contact path is personal assistance, referral, general contact, information-only, or leak-drop-only.
- `anonymous_pre_consult_status` says whether a person can begin without disclosing identity.
- `secure_channel_status` says whether the communication path is meaningfully secure.
- Those five concepts must not collapse into each other.

The schema is intentionally heavier than the legacy agency ACF. That weight is
mostly in operational safety fields, where mistakes are user-facing and
time-sensitive.
