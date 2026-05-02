# Assist Organization Record ACF Canonical Field Proposal

**Status:** Draft proposal  
**Purpose:** Replace the current assist-organization ACF with a complete, normalized, editor-safe field model for `ws-assist-org`.  
**Reference Model:** `legal-record-acf-fields.md` naming and schema philosophy, adapted for assistance organizations.  
**Record Type:** `assist-org` / `ws-assist-org`  
**Primary Use:** Public-facing assistance directory, intake routing, research verification, crisis-safe referrals, and long-term maintenance.

---

## Purpose

The assist-organization record is not a legal doctrine record. It is a user-safety record.

A statute record can tolerate unresolved nuance as long as it is marked for legal review. An assist-org record can fail a person immediately if it publishes a dead phone number, implies legal representation where only referral exists, routes a vulnerable whistleblower to a leak-drop instead of personal help, or hides eligibility restrictions until after the user has already exposed themselves.

This proposal expands the assist-org data model so the directory can answer the questions that matter in a real user moment:

- Can this organization actually help me?
- Can I contact them safely?
- Will they talk to me anonymously?
- Do they serve my location, worker type, issue, language, and stage?
- Are they a direct service provider, a referral hub, an advocacy group, or mainly informational?
- Is their intake fast, restricted, seasonal, closed, or unclear?
- What should a human editor verify before publication?

---

## Design Principles

- Use legal-record naming rules where useful.
- Bend naming rules where user safety or editorial clarity requires it.
- Prefer plain-English field names over nonprofit-sector jargon.
- Use taxonomies for search/routing filters.
- Use select fields for small, stable operational states.
- Use repeaters for variable contact, office, service, and verification data.
- Keep uncertainty visible to editors.
- Never let “unclear” silently masquerade as “safe.”
- Separate public-facing fields from internal verification fields.
- Separate service claims from verified intake behavior.

---

## Naming Rules Applied

- Meta names are `snake_case` only.
- Choice keys are `kebab-case` only.
- Booleans use `has_*` when they trigger fields.
- Booleans use `is_*` when they are state markers and do not trigger fields.
- Multi-value fields use plural nouns.
- Single-value fields use singular nouns.
- Detail companions use `*_details`.
- Context companions use `*_context`.
- Operational notes use `*_notes` when the field is meant for human editorial maintenance rather than public explanation.
- URLs use `*_url`.
- Dates use `*_date`.
- Internal-only fields may use `_` prefix.
- Avoid `*_type` unless the term is genuinely the clearest label.
- Prefer `*_class`, `*_scope`, `*_status`, `*_rule`, `*_model`, `*_channel`, or `*_standard` where more precise.

### Deliberate Rule Breaks

- `assistance_type` is retained as a public-routing taxonomy name because it is already established and immediately understandable.
- `intake_url` is retained because “intake” is a common service-access word and is shorter than `personal_assistance_request_url`.
- `whistleblower_scope` is retained, but redesigned from integer scale to taxonomy/choice-backed routing data.
- `secure_drop` is allowed as a service term but must not be treated as a qualifying personal-assistance intake channel.

---

## Sentinel Values

Use sentinel values sparingly but deliberately.

- `has-details` — use where an existing taxonomy/choice cannot fully capture the concept and a companion field is required.
- `unclear` — use only where uncertainty itself is a meaningful operational state.
- `not-stated` — use where the source is silent and silence must be distinguished from uncertainty.
- `not-applicable` — use where the field does not logically apply to the organization.
- `see-context` — use in choice fields where a companion `*_context` already carries the nuance.

---

## Needed Hooks

- Derived values need auto-fill by hook on load/fill/update.
- Merged values need auto-fill by hook on save.
- Taxonomy fallback sentinels should trigger required companion fields.
- `jurisdictions` should derive `serves_nationwide` when all 57 jurisdictions or a nationwide term is selected.
- `public_directory_status` should require minimum public-safety fields before `publish-ready`.
- `intake_status` should trigger admin notices when stale or unclear.
- `last_verified_date` should warn after a configurable interval.
- `emergency_help_status` should prevent crisis-safe display unless clearly verified.
- `secure_channel_status` should not treat HTTPS, generic contact forms, or SecureDrop as encrypted personal-assistance channels.
- `leak_drop_urls` should never populate `intake_url`.
- `has_attorneys` should trigger attorney-service companion fields.
- `law-firm` in `assistance_type` should require `cost_models` and `fee_model_context`.
- `referral-only` in service scope should require `referral_context`.
- `income_screening` values should trigger eligibility detail fields.
- `languages` containing `additional` should trigger `language_details`.
- `language_access_rule` should emit a review warning if set to `required` or `exclusive` and `languages` is empty.
- `eligibility_constraints` containing `language-required`, `identity-restricted`, `residency-required`, or `environment-required` should require `eligibility_constraints_details`.
- `service_environment` containing `restricted-environment` or `residential` should require `service_environment_details` and should block automatic high-confidence routing until reviewed.
- `service_area_scope` should be checked against selected jurisdictions.
- Public display should suppress phone/email rows marked `internal-only`.
- Public display should suppress records with `do_not_publish` status regardless of WordPress post status.

---

## Core Tab Model

The assist-org record should use these tabs:

1. Identity & Publishing
2. Service Area
3. Services & Fit
4. Intake & Contact
5. Eligibility & Access
6. Safety & Privacy
7. Legal Capacity
8. Verification & Trust
9. Relationships
10. Source / Audit
11. Internal Operations
12. Hidden Fields

---

# Common Fields for `ws-assist-org`

---

## Identity & Publishing Tab

Fields ordered: identity → public display → directory control.

- `official_name`
- `common_name`
- `alternate_names` — repeater; prior names, acronyms, program names, DBA names.
- `organization_status` — single-select: `active`|`inactive`|`merged`|`closed`|`unclear`|`has-details`
- `organization_status_details`
- `official_homepage_url`
- `homepage_url_status` — single-select: `verified`|`redirects`|`unverified`|`dead`
- `homepage_verified_date`
- `logo`
- `general_description` — brief public-facing description.
- `public_summary` — optional short card text; if empty, generated from `general_description`.
- `assistance_type` — single-select taxonomy: `ws_aorg_type`
- `assistance_model` — taxonomy: `ws_aorg_assistance_model`
- `public_directory_status` — single-select: `draft-review`|`publish-ready`|`needs-verification`|`temporarily-hidden`|`do-not-publish`
- `public_directory_status_details`
- `display_priority` — integer; manual editorial weighting.
- `has_attach_flag`
- `display_order`

### Notes

`organization_status` and `public_directory_status` are intentionally separate. An organization may be active but still unsafe to publish because intake is unverified.

---

## Service Area Tab

Fields ordered: geography → scope → exceptions → offices.

- `service_area_scope` — single-select: `nationwide`|`multi-state`|`single-state`|`regional`|`local`|`virtual-only`|`unclear`|`has-details`
- `service_area_details`
- `serves_nationwide` — derived bool.
- `jurisdictions` — taxonomy: `WS_JURISDICTION_TAXONOMY`
- `jurisdiction_exceptions`
- `nationwide_evidence_quote` — source quote supporting nationwide or multi-jurisdiction scope.
- `service_area_source_url`
- `has_physical_offices`
- `offices` — repeater:
  - `office_name`
  - `office_scope` — single-select: `headquarters`|`regional`|`local`|`clinic`|`mailing-only`|`has-details`
  - `office_jurisdiction` — taxonomy: `WS_JURISDICTION_TAXONOMY`, save_terms false.
  - `office_address`
  - `office_phone`
  - `office_email`
  - `office_url`
  - `office_notes`
- `virtual_service_available` — ternary: `yes`|`no`|`unclear`
- `in_person_service_available` — ternary: `yes`|`no`|`unclear`

---

## Services & Fit Tab

Fields ordered: service fit → legal issue fit → worker fit → stage fit.

- `whistleblower_fit` — single-select: `direct-whistleblower-focus`|`whistleblower-subset`|`adjacent-worker-help`|`not-whistleblower-specific`|`unclear`
- `whistleblower_fit_context`
- `whistleblower_evidence_quote`
- `services` — taxonomy: `ws_aorg_service`
- `service_details`
- `service_depth` — taxonomy: `ws_aorg_service_depth`
- `service_depth_context`
- `service_environment` — taxonomy: `ws_aorg_service_environment`
- `service_environment_details`
- `case_stages` — taxonomy: `ws_case_stage`
- `case_stage_details`
- `protected_disclosures` — taxonomy: `ws_protected_disclosure`
- `protected_disclosure_details`
- `protected_classes` — taxonomy: `ws_protected_class`
- `protected_class_details`
- `employment_sectors` — taxonomy: `ws_employment_sector`
- `disclosure_targets` — taxonomy: `ws_disclosure_target`
- `disclosure_target_details`
- `process_types` — taxonomy: `ws_process_type`
- `retaliation_help_available` — ternary: `yes`|`no`|`unclear`
- `retaliation_help_context`
- `referral_available` — ternary: `yes`|`no`|`unclear`
- `referral_context`
- `does_not_provide` — taxonomy: `ws_aorg_service_exclusion`
- `does_not_provide_details`
- `best_fit_summary` — editor-facing plain English: who this org is best for.
- `poor_fit_summary` — editor-facing plain English: who should not be routed here.

### Notes

`services` says what the organization claims to do. `service_depth` says how direct or complete the help appears to be. This distinction is critical for not misrouting crisis users to organizations that merely refer, educate, or advocate.

---

## Intake & Contact Tab

Fields ordered: intake status → intake path → contact methods → response expectations.

- `intake_status` — single-select: `open`|`limited`|`waitlist`|`seasonal`|`closed`|`unclear`|`has-details`
- `intake_status_details`
- `intake_url` — direct personal-assistance request URL.
- `intake_url_status` — single-select: `verified`|`redirects`|`unverified`|`dead`
- `intake_verified_date`
- `intake_commitment_level` — single-select: `personal-help-request`|`screening-form`|`referral-request`|`general-contact-only`|`leak-drop-only`|`unclear`
- `intake_commitment_details`
- `contact_url`
- `contact_url_status` — single-select: `verified`|`redirects`|`unverified`|`dead`
- `phones` — repeater:
  - `phone_channel` — single-select: `hotline`|`intake`|`main`|`regional`|`tty`|`fax`|`secure`|`media`|`internal-only`|`has-details`
  - `phone_number`
  - `phone_hours`
  - `phone_language_notes`
  - `phone_publication_status` — single-select: `public`|`internal-only`|`do-not-publish`
  - `phone_verified_date`
  - `phone_notes`
- `emails` — repeater:
  - `email_channel` — single-select: `intake`|`general`|`legal`|`support`|`secure`|`media`|`internal-only`|`has-details`
  - `email_address`
  - `email_publication_status` — single-select: `public`|`internal-only`|`do-not-publish`
  - `email_verified_date`
  - `email_notes`
- `contact_forms` — repeater:
  - `form_url`
  - `form_scope` — single-select: `intake`|`consultation-request`|`referral-request`|`general-contact`|`complaint-reporting`|`media-tip`|`unclear`|`has-details`
  - `form_requires_identity` — ternary: `yes`|`no`|`unclear`
  - `form_secure_channel` — ternary: `yes`|`no`|`unclear`
  - `form_notes`
- `leak_drop_urls` — repeater:
  - `leak_drop_url`
  - `leak_drop_tool` — single-select: `securedrop`|`globaleaks`|`custom`|`has-details`
  - `leak_drop_notes`
- `mailing_address`
- `expected_response_time` — single-select: `same-day`|`one-to-three-days`|`one-week`|`over-one-week`|`not-stated`|`unclear`|`has-details`
- `response_time_details`
- `after_hours_available` — ternary: `yes`|`no`|`unclear`
- `emergency_help_status` — single-select: `not-emergency-service`|`limited-urgent-help`|`urgent-hotline`|`unclear`|`has-details`
- `emergency_help_details`

### Hard Rule

`leak_drop_urls` must never populate `intake_url`. A leak-drop can support anonymous evidence submission, but it is not personal assistance unless the organization explicitly says it will respond with help.

---

## Eligibility & Access Tab

Fields ordered: eligibility → screening → cost → accessibility → languages.

- `eligibility_status` — single-select: `open-to-public`|`screening-required`|`restricted`|`members-only`|`referral-only`|`unclear`|`has-details`
- `eligibility_details`
- `income_screening` — single-select: `required`|`not-required`|`possible`|`unclear`|`has-details`
- `income_screening_details`
- `identity_screening` — single-select: `required`|`not-required`|`possible`|`unclear`|`has-details`
- `identity_screening_details`
- `membership_requirement` — single-select: `none`|`union-members-only`|`profession-members-only`|`program-members-only`|`has-details`
- `membership_details`
- `geographic_eligibility_details`
- `worker_status_eligibility_details`
- `eligibility_constraints` — taxonomy: `ws_aorg_eligibility_constraint`
- `eligibility_constraints_details`
- `conflict_screening_required` — ternary: `yes`|`no`|`unclear`
- `conflict_screening_details`
- `cost_models` — taxonomy: `ws_aorg_cost_model`
- `fee_model_context`
- `payment_timing` — single-select: `no-payment`|`upfront`|`after-recovery`|`mixed`|`unclear`|`has-details`
- `payment_timing_details`
- `languages` — taxonomy: `ws_language`
- `language_access_rule` — single-select: `available`|`preferred`|`required`|`exclusive`|`unclear`|`has-details`
- `language_details`
- `interpretation_available` — ternary: `yes`|`no`|`unclear`
- `accessibility_services` — taxonomy: `ws_aorg_accessibility_service`
- `accessibility_details`
- `technology_access_requirements` — single-select: `phone-ok`|`internet-required`|`video-required`|`document-upload-required`|`unclear`|`has-details`
- `technology_access_details`

### Notes

`languages` identifies what languages the organization can use. `language_access_rule` identifies whether language is merely available, preferred, required, or exclusive. This prevents a French-only organization from being treated like an English-speaking organization that merely offers French.

`eligibility_constraints` captures hard gatekeeping conditions that are not income, geography, or worker-status alone. Use it for identity-limited, residency-limited, profession-limited, referral-only, membership-only, language-required, or unusual access rules.

---

## Safety & Privacy Tab

Fields ordered: anonymity → security → privacy → risk warnings.

- `anonymous_pre_consult_possible` — ternary: `yes`|`no`|`unclear`
- `anonymous_pre_consult_details`
- `confidentiality_claimed` — ternary: `yes`|`no`|`unclear`
- `confidentiality_details`
- `privacy_policy_url`
- `privacy_policy_status` — single-select: `verified`|`not-found`|`unverified`|`dead`
- `secure_channel_status` — single-select: `dedicated-secure-channel`|`standard-web-form`|`leak-drop-only`|`none-found`|`unclear`|`has-details`
- `secure_channel_details`
- `secure_contact_tools` — taxonomy: `ws_secure_contact_tool`
- `secure_contact_tool_details`
- `secure_contact_url`
- `encryption_notes`
- `retention_policy_url`
- `retention_policy_details`
- `mandatory_reporting_warning` — ternary: `yes`|`no`|`unclear`
- `mandatory_reporting_details`
- `risk_warning_notes` — editor-facing safety caveats shown before routing if needed.
- `public_safety_note` — public-facing warning when the record is useful but risky or limited.

### Notes

This tab intentionally separates “confidentiality claimed” from “secure channel available.” A privacy promise without a safe contact method should not be treated as secure intake.

---

## Legal Capacity Tab

Fields ordered: attorney availability → representation authority → attorney limits.

- `has_attorneys` — ternary: `yes`|`no`|`unclear`
- `attorney_role` — single-select: `direct-representation`|`consultation-only`|`referral-panel`|`supervised-clinic`|`policy-only`|`unclear`|`has-details`
- `attorney_role_details`
- `legal_representation_available` — ternary: `yes`|`no`|`unclear`
- `legal_representation_details`
- `bar_jurisdictions` — taxonomy: `WS_JURISDICTION_TAXONOMY`, save_terms false.
- `bar_status_details`
- `accreditation`
- `unauthorized_practice_warning` — ternary: `yes`|`no`|`unclear`
- `unauthorized_practice_details`
- `attorney_client_relationship_status` — single-select: `may-form`|`does-not-form`|`not-stated`|`unclear`|`has-details`
- `attorney_client_details`
- `privilege_warning_notes`
- `representation_limits`

### Notes

This tab exists because “has attorneys” is not enough. Users need to know whether attorneys represent people, screen cases, provide referrals, supervise students, or only support policy advocacy.

---

## Verification & Trust Tab

Fields ordered: legitimacy → source verification → update status.

- `legitimacy_urls` — repeater:
  - `legitimacy_url`
  - `legitimacy_source` — single-select: `irs`|`guidestar`|`charity-navigator`|`bar-directory`|`court-directory`|`government-directory`|`congressional-directory`|`state-registry`|`news-source`|`has-details`
  - `legitimacy_notes`
- `tax_exempt_status` — single-select: `501c3`|`501c4`|`government`|`for-profit`|`fiscally-sponsored`|`not-stated`|`unclear`|`has-details`
- `tax_exempt_details`
- `source_quality` — single-select: `official-only`|`official-plus-third-party`|`third-party-only`|`weak`|`has-details`
- `source_quality_details`
- `verification_status` — single-select: `verified`|`partially-verified`|`needs-review`|`stale`|`failed`|`has-details`
- `verification_details`
- `last_verified_date`
- `next_review_date`
- `verification_frequency` — single-select: `monthly`|`quarterly`|`semiannual`|`annual`|`manual-only`
- `verification_attempts` — repeater:
  - `attempt_date`
  - `attempt_method` — single-select: `website`|`email`|`phone`|`directory`|`archive`|`other`
  - `attempt_result` — single-select: `verified`|`no-response`|`failed`|`conflicting-info`|`has-details`
  - `attempt_notes`
- `staleness_warning` — derived bool.
- `editor_confidence` — single-select: `high`|`medium`|`low`|`do-not-use`
- `editor_confidence_details`

---

## Relationships Tab

Fields ordered: related internal objects → external networks → referrals.

- `related_legal_records` — post object; legal records.
- `related_agencies` — post object; `ws-agency`.
- `related_procedures` — post object; `ag-procedure` if still retained.
- `parent_organization_id` — post object; `ws-assist-org`.
- `child_program_ids` — post object; `ws-assist-org`.
- `network_memberships` — taxonomy: `ws_aorg_network`
- `referral_partners` — repeater:
  - `partner_name`
  - `partner_url`
  - `partner_relationship` — single-select: `formal-referral`|`informal-referral`|`coalition`|`funding-partner`|`unclear`|`has-details`
  - `partner_notes`
- `replaced_by_id` — post object; `ws-assist-org`.
- `duplicate_of_id` — post object; `ws-assist-org`.

---

## Source / Audit Tab

Fields ordered: source → research provenance → review notes.

- `source_urls` — repeater:
  - `source_url`
  - `source_label`
  - `source_class` — single-select: `official`|`third-party`|`archive`|`directory`|`news`|`other`
  - `source_verified_date`
  - `source_notes`
- `authority_reference` — freetext; official registry, IRS, court, bar, or government reference.
- `last_reviewed_date`
- `_review_notes`
- `_reconciled_notes`
- `_researcher_notes`
- `_schema_gap_notes`

---

## Internal Operations Tab

Fields ordered: internal relationship → outreach → maintenance.

- `_internal_contact_name`
- `_internal_contact_role`
- `_internal_contact_email`
- `_internal_contact_phone`
- `_internal_last_contacted_date`
- `_internal_contact_status` — single-select: `not-contacted`|`contacted`|`responded`|`confirmed`|`declined`|`bounced`|`has-details`
- `_internal_contact_details`
- `_internal_relationship_notes`
- `_editor_owner`
- `_review_priority` — single-select: `critical`|`high`|`normal`|`low`
- `_review_blockers`
- `_do_not_contact`
- `_do_not_contact_reason`

---

## Hidden Fields

Fields ordered: id → derived → routing.

- `_id` — generated by ingest tool or matrix seeder.
- `_service_fit_score` — derived numeric score.
- `_intake_safety_score` — derived numeric score.
- `_routing_weight` — derived/manual hybrid score.
- `_has_public_contact` — derived bool.
- `_has_verified_intake` — derived bool.
- `_has_crisis_safe_contact` — derived bool.
- `_has_legal_help` — derived bool.
- `_has_secure_personal_assistance` — derived bool.
- `_effective_jurisdictions` — derived from `jurisdictions`, `serves_nationwide`, and exceptions.
- `_last_ingest_batch_id`
- `_last_ingest_model`
- `_last_ingest_date`

---

# Proposed Taxonomy Tables

---

## `ws_aorg_type`

Existing table. Retain, but refine terms.

Concept: primary organization classification.

Recommended terms:

- `nonprofit`
- `legal-aid`
- `law-firm`
- `bar-program`
- `advocacy`
- `oversight-office`
- `union`
- `clinic`
- `referral-network`
- `peer-network`
- `mental-health-provider`
- `journalism-support`
- `mixed`
- `has-details`

### Proposed Changes

Add:

- `clinic`
- `referral-network`
- `peer-network`
- `mental-health-provider`
- `journalism-support`
- `has-details`

---

## `ws_aorg_assistance_model`

New table.

Concept: what kind of help relationship the organization offers.

Terms:

- `direct-service`
- `screening-and-referral`
- `referral-only`
- `hotline-support`
- `peer-support`
- `legal-clinic`
- `impact-litigation`
- `policy-advocacy`
- `media-support`
- `emergency-support`
- `self-help-resources`
- `mixed`
- `unclear`
- `has-details`

### Why Needed

`ws_aorg_type` describes what the organization is. This table describes how it helps. A legal-aid organization and a bar program may both route users very differently.

---

## `ws_aorg_service`

Existing table. Retain and expand.

Concept: services offered.

Current core terms should remain. Add:

- `case-screening`
- `legal-advice`
- `limited-scope-representation`
- `full-representation`
- `impact-litigation`
- `emergency-consultation`
- `know-your-rights`
- `safety-planning`
- `career-support`
- `public-benefits-support`
- `housing-support`
- `immigration-support`
- `labor-organizing-support`
- `expert-referral`
- `attorney-referral`
- `therapist-referral`
- `media-strategy`
- `secure-communication-guidance`
- `evidence-preservation-guidance`
- `additional`
- `unclear`

### Proposed Cleanup

Consider renaming:

- `legal-rep` → `full-representation`
- `doc-review` → `document-review`
- `financial` → `financial-support`
- `media` → `media-support`

The old slugs can be supported by migration aliases if needed.

---

## `ws_aorg_service_depth`

New table.

Concept: how deep the help goes.

Terms:

- `information-only`
- `triage-only`
- `brief-advice`
- `document-review`
- `limited-scope-help`
- `ongoing-support`
- `direct-representation`
- `referral-only`
- `warm-handoff`
- `case-management`
- `unclear`
- `has-details`

### Why Needed

A user-facing directory must distinguish “they explain rights” from “they may take your case.”

---

## `ws_aorg_service_exclusion`

New table.

Concept: what the organization explicitly does not do.

Terms:

- `no-legal-advice`
- `no-representation`
- `no-emergency-help`
- `no-individual-assistance`
- `no-anonymous-contact`
- `no-media-placement`
- `no-financial-aid`
- `no-mental-health-care`
- `no-government-employee-help`
- `no-private-sector-help`
- `no-criminal-defense`
- `no-immigration-help`
- `no-employment-litigation`
- `has-details`

### Why Needed

Negative service data is as important as affirmative service data. It prevents bad routing.

---

## `ws_aorg_eligibility_constraint`

New table.

Concept: hard gatekeeping conditions that affect whether a person can actually receive help.

Terms:

- `income-limited`
- `residency-required`
- `citizenship-required`
- `immigration-status-limited`
- `language-required`
- `identity-restricted`
- `community-restricted`
- `profession-restricted`
- `sector-restricted`
- `union-members-only`
- `referral-required`
- `membership-required`
- `age-restricted`
- `conflict-screening-required`
- `case-merit-screening-required`
- `capacity-limited`
- `environment-required`
- `has-details`

### Why Needed

Eligibility is broader than income. This table prevents narrow or unusual organizations from being falsely routed to users who cannot access them.

---

## `ws_aorg_service_environment`

New table.

Concept: the practical setting or access context in which help is provided.

Terms:

- `standard-office`
- `remote-only`
- `phone-only`
- `online-only`
- `community-based`
- `clinic-based`
- `mobile-clinic`
- `shelter-based`
- `residential`
- `campus-based`
- `worksite-based`
- `restricted-environment`
- `has-details`

### Why Needed

Some organizations are technically assistance providers but operate only in a specific physical, social, residential, institutional, or restricted environment. This should be searchable and reviewable, not buried in `_review_notes`.

---

## `ws_aorg_cost_model`

Existing table. Retain and expand.

Concept: cost structure.

Recommended terms:

- `free`
- `pro-bono`
- `sliding-scale`
- `contingency`
- `fee-for-service`
- `flat-fee`
- `membership-funded`
- `grant-funded`
- `court-awarded-fees`
- `costs-only`
- `mixed`
- `unclear`
- `has-details`

---

## `ws_aorg_accessibility_service`

New table.

Concept: practical accessibility supports.

Terms:

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

---

## `ws_secure_contact_tool`

New table.

Concept: actual secure communication tools.

Terms:

- `signal`
- `protonmail`
- `tutanota`
- `wire`
- `keybase`
- `pgp-email`
- `securedrop`
- `globaleaks`
- `encrypted-web-form`
- `tor-onion-service`
- `other-secure-tool`
- `has-details`

### Note

`securedrop` and `globaleaks` may support anonymous submission, but do not automatically qualify as personal-assistance intake.

---

## `ws_aorg_network`

New table.

Concept: coalition, directory, referral, or formal network membership.

Terms:

- `legal-services-network`
- `bar-referral-network`
- `whistleblower-advocacy-network`
- `press-freedom-network`
- `labor-network`
- `mental-health-network`
- `public-interest-law-network`
- `government-funded-network`
- `foundation-funded-network`
- `has-details`

---

## `ws_case_stage`

Existing table. Retain and expand.

Concept: user stage supported.

Recommended terms:

- `pre-report`
- `considering-reporting`
- `preparing-disclosure`
- `post-report`
- `retaliation-active`
- `agency-complaint`
- `investigation-active`
- `litigation`
- `appeal`
- `settlement`
- `post-case-recovery`
- `career-fallout`
- `media-exposure`
- `has-details`

---

## Existing Shared Taxonomies Used by Assist-Orgs

Retain use of:

- `WS_JURISDICTION_TAXONOMY`
- `ws_protected_disclosure`
- `ws_protected_class`
- `ws_disclosure_target`
- `ws_process_type`
- `ws_employment_sector`
- `ws_language`

### Proposed Shared Taxonomy Additions

#### `ws_language`

Consider adding if not already present:

- `farsi`
- `urdu`
- `pashto`
- `somali`
- `amharic`
- `swahili`
- `german`
- `italian`
- `ukrainian`
- `hebrew`
- `other-language`

Note: language availability still does not indicate language exclusivity. Use `language_access_rule` for that.

#### `ws_protected_class`

Consider adding if not already present:

- `union-member`
- `licensed-professional`
- `academic-researcher`
- `journalist-source`
- `immigrant-worker`
- `low-wage-worker`
- `executive-manager`
- `intern-volunteer`

#### `ws_disclosure_target`

Consider adding:

- `internal-oversight`
- `external-oversight-body`
- `inspector-general`
- `audit-committee`
- `board-of-directors`

#### `ws_process_type`

Consider adding:

- `pre-filing-consultation`
- `agency-intake`
- `attorney-referral`
- `media-referral`
- `support-referral`
- `settlement-negotiation`

---

# Proposed Field Normalization From Current Assist-Org ACF

| Current Field | Proposed Field | Notes |
|---|---|---|
| `_ws_aorg_id` | `_id` | Align with legal-record hidden ID rule. |
| `ws_aorg_official_name` | `official_name` | Drop storage prefix in canonical proposal. |
| `ws_aorg_type` | `assistance_type` | Use taxonomy `ws_aorg_type`. |
| `ws_aorg_description` | `general_description` | Align with legal-record naming. |
| `ws_aorg_common_name` | `common_name` | Keep. |
| `ws_aorg_logo` | `logo` | Keep data-shape. |
| `ws_aorg_serves_nationwide` | `serves_nationwide` | Derived from `service_area_scope` + `jurisdictions`. |
| `ws_aorg_whistleblower_scope` | `whistleblower_fit` | Replace integer scale with meaningful choice. |
| `ws_aorg_whistleblower_scope_details` | `whistleblower_fit_context` | Context, not merely details. |
| `ws_aorg_has_limited_scope` | `service_area_scope` / `eligibility_status` | Bool is too blunt. |
| `ws_aorg_jurisdictions` | `jurisdictions` | Keep taxonomy. |
| `ws_aorg_community_scope` | `best_fit_summary` / `eligibility_details` | Split into service fit and eligibility. |
| `ws_aorg_protected_disclosures` | `protected_disclosures` | Keep shared taxonomy. |
| `ws_aorg_disclosure_targets` | `disclosure_targets` | Keep shared taxonomy. |
| `ws_aorg_disclosure_target_details` | `disclosure_target_details` | Keep. |
| `ws_aorg_case_stages` | `case_stages` | Keep taxonomy, expand terms. |
| `ws_aorg_case_stage_details` | `case_stage_details` | Keep. |
| `ws_aorg_process_types` | `process_types` | Keep shared taxonomy. |
| `ws_aorg_services` | `services` | Keep taxonomy, expand terms. |
| `ws_aorg_additional_services` | `service_details` | Broader companion. |
| `ws_aorg_employment_sectors` | `employment_sectors` | Keep shared taxonomy. |
| `ws_aorg_protected_classes` | `protected_classes` | Keep shared taxonomy. |
| `ws_aorg_protected_class_details` | `protected_class_details` | Keep. |
| `ws_aorg_website_url` | `official_homepage_url` | More precise. |
| `ws_aorg_intake_url` | `intake_url` | Keep. |
| `ws_aorg_contact_url` | `contact_url` | Keep. |
| `ws_aorg_phones` | `phones` | Expand repeater. |
| `ws_aorg_phone_type` | `phone_channel` | Better than type. |
| `ws_aorg_phone_number` | `phone_number` | Keep. |
| `ws_aorg_emails` | `emails` | Expand repeater. |
| `ws_aorg_email_type` | `email_channel` | Better than type. |
| `ws_aorg_email_address` | `email_address` | Keep. |
| `ws_aorg_mailing_address` | `mailing_address` | Keep. |
| `ws_aorg_has_secure_channel` | `secure_channel_status` | Ternary/bool is too blunt. |
| `ws_aorg_secure_contact_url` | `secure_contact_url` | Keep. |
| `ws_aorg_secure_contact_tool` | `secure_contact_tools` | Taxonomy, multi-select. |
| `ws_aorg_secure_contact_tool_other` | `secure_contact_tool_details` | Companion. |
| `ws_aorg_languages` | `languages` | Keep taxonomy. |
| `ws_aorg_additional_languages` | `language_details` | Better companion. |
| New | `language_access_rule` | Distinguishes language availability from language requirement or exclusivity. |
| New | `eligibility_constraints` | Captures hard gatekeeping rules not reducible to income or geography. |
| New | `service_environment` | Captures unusual service settings or restricted environments. |
| `ws_aorg_cost_models` | `cost_models` | Keep taxonomy, expand terms. |
| `ws_aorg_has_income_limit` | `income_screening` | Bool replaced by meaningful choice. |
| `ws_aorg_has_income_limit_details` | `income_screening_details` | Keep concept. |
| `ws_aorg_accepts_anonymous` | `anonymous_pre_consult_possible` | More precise. |
| `ws_aorg_eligibility_details` | `eligibility_details` | Keep. |
| `ws_aorg_licensed_attorneys` | `has_attorneys` | Ternary. |
| `ws_aorg_accreditation` | `accreditation` | Keep. |
| `ws_aorg_bar_states` | `bar_jurisdictions` | Use jurisdiction taxonomy save_terms false. |
| `ws_aorg_legitimacy_url` | `legitimacy_urls` | Repeater, not single URL. |
| `ws_aorg_last_reviewed` | `last_reviewed_date` | Align legal-record naming. |
| `_ws_aorg_internal_contact_name` | `_internal_contact_name` | Drop redundant object prefix. |
| `_ws_aorg_internal_contact_role` | `_internal_contact_role` | Same. |
| `_ws_aorg_internal_contact_email` | `_internal_contact_email` | Same. |
| `_ws_aorg_internal_contact_phone` | `_internal_contact_phone` | Same. |
| `_ws_aorg_internal_last_contacted` | `_internal_last_contacted_date` | Data-shape suffix. |
| `_ws_aorg_internal_relationship_notes` | `_internal_relationship_notes` | Same. |

---

# Priority Build Plan

## Phase 1 — Do Not Misroute Users

Implement first:

- `public_directory_status`
- `organization_status`
- `service_area_scope`
- `whistleblower_fit`
- `services`
- `service_depth`
- `does_not_provide`
- `intake_status`
- `intake_commitment_level`
- `intake_url_status`
- `phones` expanded repeater
- `emails` expanded repeater
- `contact_forms`
- `leak_drop_urls`
- `eligibility_status`
- `eligibility_constraints`
- `language_access_rule`
- `service_environment`
- `cost_models`
- `anonymous_pre_consult_possible`
- `secure_channel_status`
- `has_attorneys`
- `attorney_role`
- `verification_status`
- `last_verified_date`

## Phase 2 — Make Search and Routing Excellent

Implement next:

- `assistance_model`
- `case_stages` expanded terms
- `protected_disclosures`
- `protected_classes`
- `employment_sectors`
- `process_types`
- `retaliation_help_available`
- `referral_available`
- `jurisdiction_exceptions`
- `_service_fit_score`
- `_intake_safety_score`
- `_routing_weight`

## Phase 3 — Human Maintenance and Trust

Implement after core routing:

- `verification_attempts`
- `source_urls`
- `legitimacy_urls`
- `next_review_date`
- `verification_frequency`
- `editor_confidence`
- `internal operations` fields
- relationship fields
- derived hidden fields

---

# Public Display Safeguards

Before a record can be displayed as a recommended assist-org, require:

- `public_directory_status` = `publish-ready`
- `organization_status` = `active`
- `official_homepage_url` present
- `homepage_url_status` = `verified` or `redirects`
- `general_description` present
- at least one of:
  - verified `intake_url`
  - public phone
  - public email
  - public contact form
- `intake_commitment_level` is not `leak-drop-only`
- `verification_status` is not `failed`
- `editor_confidence` is not `do-not-use`
- `language_access_rule` is not `exclusive` unless the user's language match is confirmed
- `eligibility_constraints` does not include a hard restriction that conflicts with the user's known profile
- `service_environment` does not include `restricted-environment` unless the record is being shown with a clear warning

Records failing these checks may still exist as internal draft/research records.

---

# Ingest Schema Alignment

The next assist-org research prompt should map to this field model and preserve these blocks:

- `meta`
- `records`
- `integrity`

But record internals should be reorganized as:

- `identity`
- `service_area`
- `services_fit`
- `intake_contact`
- `eligibility_access`
- `safety_privacy`
- `legal_capacity`
- `verification_trust`
- `relationships`
- `source_audit`

This keeps machine ingest clean while matching the ACF tabs.

---

# Final Notes

This proposal intentionally treats assist-org records as more operational than legal records.

The legal-record system asks:

> What protection exists?

The assist-org system asks:

> Can this person safely and realistically get help from this organization right now?

That requires more status fields, more verification fields, more negative-service fields, more contact-method structure, more eligibility constraints, clearer language-access rules, service-environment modeling, and more publication gates than the legal-record model.

The result is heavier, but for this object type, heavier is correct.
