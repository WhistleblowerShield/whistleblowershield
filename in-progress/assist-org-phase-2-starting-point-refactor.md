# Assist-Org Phase 2 Starting Point Refactor

Purpose: define the smallest assist-org schema adjustment needed to resume the Phase 2 directory pivot without
turning the assist-org record into a second legal-record monolith.

This document does not replace `assist-org-record-acf-fields-Codex.md`. That file remains a larger proposal. This
file is the bounded implementation target for the directory-first path.

---

## Governing Test

Every field in this refactor must answer at least one of these questions:

- Can Maya or James realistically contact this organization now?
- Is the contact path safe enough to show without a warning?
- Is the organization a strong, partial, or poor fit for the selected situation?
- Does the cascade need this value to sort or warn correctly?
- Would leaving this fact in overflow materially weaken user guidance?

If the answer is no, defer it.

---

## Current Capability

The current cascade can already:

- read centralized GET params;
- validate values against allowed slugs;
- treat absent answers as broadening, not failure;
- route concern values to `ws_protected_disclosure` or `ws_adverse_action`;
- score by whistleblower focus, taxonomy matches, sector, target, and attorney signal;
- surface targeted organizations before nationwide/general organizations;
- log filter choices without user identity, cookies, sessions, IP, or freeform user text.

The missing piece is not the cascade core. The missing piece is structured assist-org data for routing-critical
facts currently landing in overflow or review notes.

---

## Refactor Boundary

This pass may add fields, update prompt mapping, update ingest validation, and update cascade scoring.

This pass must not:

- redesign the full assist-org admin workflow;
- add relationship management;
- add internal outreach operations;
- add broad verification-attempt repeaters;
- migrate legal-record doctrine into assist-org records;
- require a new recognition taxonomy unless a later batch proves one is necessary.

Assist-org records describe service reality, not legal doctrine.

---

## Field Additions

Field names are prefix-free here. ACF registration applies the `ws_aorg_` prefix.

### Identity and Publishing

- `organization_status` - select: `active`|`inactive`|`merged`|`closed`|`unclear`|`has-details`
- `organization_status_details` - conditional on `organization_status` is `has-details`
- `public_directory_status` - select: `draft-review`|`publish-ready`|`needs-verification`|
  `temporarily-hidden`|`do-not-publish`|`has-details`
- `public_directory_status_details` - conditional on `public_directory_status` is `has-details`
- `editor_confidence` - select: `high`|`medium`|`low`|`do-not-use`
- `editor_confidence_notes`

Why now: these fields prevent research records from silently becoming recommendations before a human is ready to
stand behind the routing.

### Service Fit

- `whistleblower_fit` - select: `primary-focus`|`significant-program`|`adjacent-help`|`not-specific`|
  `none`|`unclear`|`has-details`
- `whistleblower_fit_details` - conditional on `whistleblower_fit` is `has-details`
- `service_depth` - select: `information-only`|`triage-only`|`brief-advice`|`document-review`|
  `limited-scope-help`|`direct-representation`|`referral-only`|`warm-handoff`|`ongoing-support`|
  `unclear`|`has-details`
- `service_depth_details` - conditional on `service_depth` is `has-details`
- `intake_commitment_class` - select: `personal-help-request`|`screening-form`|`referral-request`|
  `general-contact-only`|`leak-drop-only`|`information-only`|`unclear`|`has-details`
- `intake_commitment_class_details` - conditional on `intake_commitment_class` is `has-details`
- `service_limits` - textarea

Why now: current overflow repeatedly distinguishes direct help, referral, screening, general information, peer
support, and legal representation. These are not decoration; they change who should be shown first.

`service_limits` is intentionally freetext for this pass. It catches known user-facing limits such as "no phone
legal advice," "referral only," "subject-matter limited," and "no guaranteed representation" without forcing a
second modeling campaign.

### Intake and Contact

- `intake_status` - select: `open`|`limited`|`waitlist`|`closed`|`unclear`|`has-details`
- `intake_status_details` - conditional on `intake_status` is `has-details`
- `expected_response_time` - select: `same-day`|`one-to-three-days`|`one-week`|`over-one-week`|
  `not-stated`|`unclear`|`has-details`
- `expected_response_time_details` - conditional on `expected_response_time` is `has-details`

Why now: Maya and James need to know whether a path is likely to produce help, not just whether a URL exists.

### Eligibility and Cost

- `eligibility_status` - select: `open-to-public`|`screening-required`|`restricted`|`members-only`|
  `referral-only`|`unclear`|`has-details`
- `eligibility_status_details` - conditional on `eligibility_status` is `has-details`
- `income_screening` - select: `required`|`not-required`|`possible`|`unclear`|`has-details`
- `income_screening_details` - conditional on `income_screening` is `has-details`
- `membership_requirement` - select: `none`|`union-members-only`|`profession-members-only`|
  `program-members-only`|`has-details`
- `membership_requirement_details` - conditional on `membership_requirement` is `has-details`

Why now: research repeatedly found "case review," "no income cutoff," "membership," and "qualifying case" facts.
Those facts affect whether the result is useful now or only theoretically relevant.

### Safety and Privacy

- `anonymous_pre_consult_status` - select: `yes`|`no`|`unclear`|`has-details`
- `anonymous_pre_consult_details` - conditional on `anonymous_pre_consult_status` is `has-details`
- `confidentiality_claimed` - select: `yes`|`no`|`unclear`|`has-details`
- `confidentiality_claimed_details` - conditional on `confidentiality_claimed` is `has-details`
- `secure_channel_status` - select: `dedicated-secure-channel`|`standard-web-form`|`leak-drop-only`|
  `none-found`|`unclear`|`has-details`
- `secure_channel_status_details` - conditional on `secure_channel_status` is `has-details`
- `secure_contact_tools` - multi-select: `signal`|`protonmail`|`tutanota`|`wire`|`keybase`|
  `pgp-email`|`securedrop`|`globaleaks`|`encrypted-web-form`|`other`
- `secure_contact_tool_details` - conditional on `secure_contact_tools` includes `other`
- `risk_warning_notes`

Why now: HTTPS intake, pseudonym use, SecureDrop, Signal, and privilege/confidentiality claims are different facts.
Collapsing them makes the directory less safe.

### Legal Capacity

- `has_attorneys` - select: `yes`|`no`|`unclear`
- `attorney_role` - select: `direct-representation`|`consultation-only`|`referral-panel`|
  `supervised-clinic`|`policy-only`|`unclear`|`has-details`
- `attorney_role_details` - conditional on `attorney_role` is `has-details`
- `legal_representation_status` - select: `available`|`limited`|`referral-only`|`not-available`|
  `unclear`|`has-details`
- `legal_representation_status_details` - conditional on `legal_representation_status` is `has-details`
- `attorney_client_relationship_status` - select: `may-form`|`does-not-form`|`not-stated`|
  `unclear`|`has-details`
- `attorney_client_relationship_status_details` - conditional on `attorney_client_relationship_status` is
  `has-details`

Why now: "has attorneys" is too blunt. The directory needs to distinguish direct representation from referral
panels, policy shops, and standard forms that expressly do not create an attorney-client relationship.

---

## Existing Fields to Keep

These existing fields remain useful and should not be renamed in the Phase 2 starting-point pass unless an ACF
rewrite is already happening for other reasons:

- `ws_aorg_official_name`
- `ws_aorg_common_name`
- `ws_aorg_description`
- `ws_aorg_type`
- `ws_aorg_website_url`
- `ws_aorg_intake_url`
- `ws_aorg_contact_url`
- `ws_aorg_phones`
- `ws_aorg_emails`
- `ws_aorg_mailing_address`
- `ws_aorg_serves_nationwide`
- `ws_aorg_jurisdictions`
- `ws_aorg_protected_disclosures`
- `ws_aorg_disclosure_targets`
- `ws_aorg_case_stages`
- `ws_aorg_process_types`
- `ws_aorg_services`
- `ws_aorg_employment_sectors`
- `ws_aorg_protected_classes`
- `ws_aorg_cost_models`
- `ws_aorg_languages`
- `ws_aorg_legitimacy_url`
- `ws_aorg_last_reviewed`

Rename normalization can wait. Maya and James do not benefit from clean meta names if the directory stalls.

---

## Field Shape Notes

Do not use `*_context` for this starting-point assist-org pass unless a true cluster is created. These are mostly
single-field conditionals from select values, so `*_details` is sufficient.

Do not use `see-details` or `see-context` in the new local selects for this pass. If the value needs explanation,
use `has-details` and its matching `*_details` field.

Do not convert every recurring service fact into a new taxonomy term. Use existing `ws_aorg_service` where the term
exists. Use the new local select fields for routing-critical distinctions. Leave non-routing nuance in
`service_limits`, `risk_warning_notes`, or `_review_notes`.

---

## Overflow Promotion Rule

Overflow promotes only when the fact is both recurring and routing-relevant.

Promote now:

- secure contact quality;
- personal assistance versus leak drop or information-only contact;
- direct representation versus referral or policy-only role;
- eligibility gates;
- intake status;
- service depth;
- directory publication confidence.

Keep in notes for now:

- exact program descriptions;
- one-off certifications;
- relationship-building details;
- broad mission phrasing;
- unverified claims about future expansion;
- source disagreements that do not affect routing.

---

## Cascade Scoring Updates

Keep the existing scoring structure. Add only small, explainable modifiers.

Positive signals:

- `whistleblower_fit = primary-focus` strong bonus.
- `whistleblower_fit = significant-program` moderate bonus.
- `service_depth = direct-representation`, `warm-handoff`, or `ongoing-support` bonus.
- `intake_commitment_class = personal-help-request` bonus.
- `intake_status = open` bonus.
- `secure_channel_status = dedicated-secure-channel` small safety bonus.
- `anonymous_pre_consult_status = yes` small safety bonus for pre-report users.
- `attorney_role = direct-representation` or `consultation-only` bonus when legal help is sought.

Warning or downgrade signals:

- `organization_status` not `active` blocks public recommendation.
- `public_directory_status` not `publish-ready` blocks public recommendation.
- `editor_confidence = do-not-use` blocks public recommendation.
- `whistleblower_fit = none` blocks public recommendation.
- `whistleblower_fit = unclear` keeps record visible only as low-confidence fallback.
- `intake_commitment_class = leak-drop-only` blocks ordinary help routing.
- `intake_commitment_class = information-only` downgrades for James.
- `secure_channel_status = leak-drop-only` requires public warning if shown.
- `legal_representation_status = referral-only` should not be labeled as direct legal help.

Do not let engagement scoring overpower fit. A hotline is useful only when the organization is otherwise relevant.

---

## Prompt Update Target

The prompt should ask researchers to distinguish these facts explicitly:

- What kind of help does the organization provide?
- How deep is the help?
- Does the intake path request personal assistance, screen for possible help, refer out, or only accept tips?
- Can a user begin without giving their legal name?
- Is there a dedicated secure channel, or only a normal HTTPS form?
- Does the organization provide direct legal representation, consultation, referral, or policy advocacy only?
- Are there eligibility gates, income gates, membership gates, or subject-matter limits?
- Is there any user-facing warning that should appear before recommending the org?

The prompt should not require researchers to invent a structured value when the source does not support one.
Use `unclear` when the question matters but the answer is not stated.

---

## Ingest Update Target

Ingest must validate the new local select values and reject invalid values.

Ingest may map old tri-state fields forward:

- `anonymous_pre_consult_possible` -> `anonymous_pre_consult_status`
- `has_secure_channel` -> `secure_channel_status`
- `has_attorneys` -> `has_attorneys`
- `income_eligibility_required` -> `income_screening`

Ingest must not map `has_secure_channel = yes` to `dedicated-secure-channel` unless a secure tool or secure URL is
also present. A normal HTTPS intake form maps to `standard-web-form`.

Ingest should preserve `_review_notes`, `_reconciled_notes`, and `_schema_gap_notes` for audit. Those fields are
not routing inputs.

---

## Deferred From Other Proposal

Defer these until after the directory path works with real records:

- `alternate_names`
- `offices`
- `contact_forms`
- `leak_drop_urls`
- detailed verification attempts;
- referral partners;
- related legal records;
- related agencies;
- parent and child program records;
- internal contact operations;
- derived hidden scores;
- full rename normalization.

These are not rejected. They are simply not part of the Phase 2 starting point.

---

## Exit Criteria

This refactor is sufficient when:

1. A reconciled assist-org batch no longer parks routing-critical facts only in overflow fields.
2. The cascade can rank direct help, referral help, peer support, and information-only resources differently.
3. Unsafe or misleading contact paths are blocked, downgraded, or shown with warnings.
4. Maya can find pre-report help without being pushed toward leak drops or litigation-only orgs.
5. James can find retaliation/legal-help paths without mistaking referral-only orgs for direct representation.
6. The next batch can be ingested without forcing another ACF overhaul.
