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

- `organization_status` - select: `active`|`inactive`|`merged`|`closed`
- `public_directory_status` - select: `draft-review`|`publish-ready`|`needs-verification`|
  `temporarily-hidden`|`do-not-publish`
- `editor_confidence` - select: `high`|`medium`|`low`|`do-not-use`
- `editor_confidence_notes`
- `organization_model` - single term: `nonprofit`|`legal-aid`|`law-firm`|`bar-program`|`advocacy`|
  `oversight-office`|`union`|`government-office`|`coalition`|`program`|`mixed`

Why now: these fields prevent research records from silently becoming recommendations before a human is ready to
stand behind the routing. `organization_model` replaces the working concept behind `aorg_type`; it describes the
kind of organization, not whether the organization is a good whistleblower match.

### Service Fit

- `whistleblower_scope` - integer: `0`|`1`|`2`|`3`
- `whistleblower_scope_details`
- `service_depth` - select: `information-only`|`triage-only`|`brief-advice`|`document-review`|
  `limited-scope-help`|`direct-representation`|`referral-only`|`warm-handoff`|`peer-support`|
  `ongoing-support`
- `intake_commitment_class` - select: `personal-help-request`|`screening-form`|`referral-request`|
  `peer-support-request`|`general-contact-only`|`tip-submission-only`|`leak-drop-only`|`information-only`
- `service_limits` - textarea

Why now: current overflow repeatedly distinguishes direct help, referral, screening, general information, peer
support, and legal representation. These are not decoration; they change who should be shown first.

`whistleblower_scope` is the single field for organization-level whistleblower relevance. Do not duplicate it
with a label field; labels can be derived from the integer when needed:

- `0` - no meaningful whistleblower-specific service;
- `1` - adjacent or weak whistleblower relevance;
- `2` - significant whistleblower program or subject-matter subset;
- `3` - primary or broad whistleblower support.

`service_limits` is intentionally freetext for this pass. It catches known user-facing limits such as "no phone
legal advice," "referral only," "subject-matter limited," and "no guaranteed representation" without forcing a
second modeling campaign.

### Intake and Contact

- `intake_status` - select: `open`|`limited`|`waitlist`|`closed`
- `expected_response_time` - select: `same-day`|`one-to-three-days`|`one-week`|`over-one-week`|
  `not-stated`

Why now: Maya and James need to know whether a path is likely to produce help, not just whether a URL exists.

### Eligibility and Cost

- `eligibility_status` - select: `open-to-public`|`screening-required`|`restricted`|`members-only`|
  `referral-only`
- `income_screening` - select: `required`|`not-required`|`possible`
- `membership_requirement` - select: `none`|`union-members-only`|`profession-members-only`|
  `program-members-only`

Why now: research repeatedly found "case review," "no income cutoff," "membership," and "qualifying case" facts.
Those facts affect whether the result is useful now or only theoretically relevant.

### Safety and Privacy

- `anonymous_pre_consult_status` - bool
- `confidentiality_claimed` - bool
- `secure_channel_status` - bool
- `secure_contact_tools` - multi-select: `signal`|`protonmail`|`tutanota`|`wire`|`keybase`|
  `pgp-email`|`securedrop`|`globaleaks`|`encrypted-web-form`|`other`
- `secure_contact_tool_details` - conditional on `secure_contact_tools` includes `other`
- `public_warning_notes`

Why now: HTTPS intake, pseudonym use, SecureDrop, Signal, and privilege/confidentiality claims are different facts.
Collapsing them makes the directory less safe.

### Legal Capacity

- `has_attorneys` - bool
- `attorney_role` - select: `direct-representation`|`consultation-only`|`referral-panel`|
  `supervised-clinic`|`policy-only`
- `legal_representation_status` - select: `available`|`limited`|`referral-only`|`not-available`
- `attorney_client_relationship_status` - select: `may-form`|`does-not-form`|`not-stated`

Why now: "has attorneys" is too blunt. The directory needs to distinguish direct representation from referral
panels, policy shops, and standard forms that expressly do not create an attorney-client relationship.

---

## Existing Fields to Keep

These existing fields remain useful. ACF registration will apply the `ws_aorg_` prefix automatically. The actual meta keys in the database will still have the prefix, but the JSON pipeline should target the canonical base names.

Actual base-name changes:
- `official_homepage_url` (was `website_url`)
- `general_description` (was `description`)
- `is_nationwide` (was `serves_nationwide`)
- `last_reviewed_date` (was `last_reviewed`)

Fields keeping their base names (with prefix applied at registration):
- `official_name`
- `common_name`
- `whistleblower_scope`
- `whistleblower_scope_details`
- `intake_url`
- `contact_url`
- `phones` (kept as scalar/text for starting-point to avoid repeater churn)
- `emails` (kept as scalar/text for starting-point to avoid repeater churn)
- `mailing_address`
- `jurisdictions`
- `protected_disclosures`
- `disclosure_targets`
- `case_stages`
- `process_types`
- `services`
- `employment_sectors`
- `protected_classes`
- `cost_models`
- `languages`

*Note: `aorg_type` is retired as a field/concept name and replaced by `organization_model`; the registered taxonomy is now `ws_organization_model`.*
*Note: `legitimacy_url` is deferred from the starting point as it was modeled as a repeater in Codex.*

---

## Field Shape Notes

Do not use `*_context` for this starting-point assist-org pass unless a true cluster is created. These are mostly
single-field conditionals from select values, so `*_details` is sufficient.

Do not use `see-details`, `see-context`, or `has-details` in canonical research values. `has-details` is an
editorial approval blocker only, not seedable data.

Do not convert every recurring service fact into a new taxonomy term. Use existing `ws_aorg_service` where the term
exists. Use the new local select fields for routing-critical distinctions. Leave non-routing nuance in
`service_limits`, `public_warning_notes`, or `_review_notes`.

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

- `whistleblower_scope` remains the base score.
- `whistleblower_scope = 3` strong bonus.
- `whistleblower_scope = 2` moderate bonus.
- `service_depth = direct-representation`, `warm-handoff`, `peer-support`, or `ongoing-support` bonus.
- `intake_commitment_class = personal-help-request` bonus.
- `intake_commitment_class = peer-support-request` bonus for retaliation-active or post-report users.
- `intake_status = open` bonus.
- `secure_channel_status = dedicated-secure-channel` small safety bonus.
- `anonymous_pre_consult_status = true` small safety bonus for pre-report users.
- `attorney_role = direct-representation` or `consultation-only` bonus when legal help is sought.

Warning or downgrade signals:

- `organization_status` not `active` blocks public recommendation.
- `public_directory_status` not `publish-ready` blocks public recommendation.
- `editor_confidence = do-not-use` blocks public recommendation.
- `whistleblower_scope = 0` blocks public recommendation.
- `intake_commitment_class = leak-drop-only` blocks ordinary help routing.
- `intake_commitment_class = tip-submission-only` blocks ordinary help routing.
- `intake_commitment_class = information-only` downgrades for James.
- `secure_channel_status = secure-tip-only` requires public warning if shown.
- `secure_channel_status = leak-drop-only` requires public warning if shown.
- `legal_representation_status = referral-only` should not be labeled as direct legal help.

Do not let engagement scoring overpower fit. A hotline is useful only when the organization is otherwise relevant.

---

## Prompt Update Target

The prompt should ask researchers to distinguish these facts explicitly:

- What kind of help does the organization provide?
- What is the 0-3 `whistleblower_scope` score, and what evidence supports it?
- How deep is the help?
- Does the intake path request personal assistance, screen for possible help, refer out, or only accept tips?
- Can a user begin without giving their legal name?
- Is there a dedicated secure channel, or only a normal HTTPS form?
- Does the organization provide direct legal representation, consultation, referral, or policy advocacy only?
- Are there eligibility gates, income gates, membership gates, or subject-matter limits?
- Is there any user-facing warning that should appear before recommending the org?

The prompt should not require researchers to invent a structured value when the source does not support one.
Leave fields empty when the source does not support a structured value. Use `none` only where an explicit
negative value is materially different from an empty value.

---

## Ingest Update Target

Since JSONs will be modified and ingest rebuilt, ingest expects the new canonical keys natively. Ingest must validate the new local select values and reject invalid ones.

For the old tri-state or legacy fields that require conceptual mapping during JSON modification:

- `assistance_type` -> `organization_model` (JSON must translate legacy types to new models)
- `anonymous_pre_consult_possible` -> `anonymous_pre_consult_status`
- `has_secure_channel` -> `secure_channel_status` (JSON must not map `has_secure_channel = true` to `dedicated-secure-channel` without verifying a secure tool or URL exists)
- `has_attorneys` -> `has_attorneys`
- `income_eligibility_required` -> `income_screening`

Ingest must not map a normal HTTPS intake form to `dedicated-secure-channel`; it maps to `standard-web-form`.

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
