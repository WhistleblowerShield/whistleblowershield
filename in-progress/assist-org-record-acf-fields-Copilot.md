# Universal Whistleblower Assistance Organization Schema (UWAOS)

**Purpose**:  
Capture every meaningful datapoint for any organization providing direct or indirect support to whistleblowers, whether through legal counsel, advocacy, mental health, systems reform, or “nontraditional” aid routes. Express edge cases without forcing false choices; allow full parity with legal directory logic. Schema supports everything from global public service bar associations to a self-help hotline run by two anonymous ex-feds operating "off the grid" in the Utah desert, fully in French, and clothing-optional.

---

## Table of Contents

1. [Naming and Schema Philosophy](#naming-and-schema-philosophy)
2. [Tabs & Editorial Order](#tabs--editorial-order)
3. [Master Field List](#master-field-list)
4. [Conditional, Sentinel, & Sibling Patterns](#conditional-sentinel--sibling-patterns)
5. [New Taxonomies & Proposal Table](#new-taxonomies--proposal-table)
6. [Recommended Rule Innovations & Rule-Bending](#recommended-rule-innovations--rule-bending)
7. [Edge Case Handling (“Utah, Naked, and Francophone” Problem)](#edge-case-handling)
8. [How To Use This Schema](#how-to-use-this-schema)

---

## Naming and Schema Philosophy

- **No prefixes** for ACF keys. Use lower_snake_case for fields. Taxonomies use kebab-case for slugs, consistent with your rules.
- "Has", "is" for sentinel and booleans; "_details" for companion free text.  
- Everything multi-value is plural. Everything trigger/conditional is matched with sentinel or logical companion.
- **Taxonomies** for any mutable or classifiable enumerated data. All terms can be extended (see “new_terms_proposed” evolutionary policy).
- **Unclassifiable** = sentinel value (`has-details`, `unclear`, `see-notes`) and forced companion field (“I genuinely can’t fit this in the taxonomy!”).
- **Fields can be repeatable** (arrays) when sensible; e.g., many intake_url, or many internal_contacts.
- **Schema must support machine audit, human narrative, and narrative overflow**; any field value can be `see-notes` to trigger non-schematic expansion.

---

## Tabs & Editorial Order

1. **Identity & Public Profile**
2. **Scope of Service & Coverage**
3. **Contact & Intake**
4. **Eligibility, Screening & Cost**
5. **Services & Methods**
6. **Security & Privacy**
7. **Credentials & Legitimacy**
8. **Access & Barriers**
9. **Internal Relationship & Ops Notes**
10. **Narrative & Editorial Overflow**
11. **System & Audit Hidden Fields**

---

## Master Field List

### 1. Identity & Public Profile

- `official_name` (text, required)
- `common_name` (text, optional)
- `internal_id` (slug, required, slug-safe)
- `profile_motto` (text, optional)
- `homepage_url` (url, required)
- `logo_image` (image, optional)
- `organization_type` (taxonomy: ws_aorg_type, required)
- `established_year` (year, optional)
- `status` (select: active | suspended | dissolved | see-details)
- `status_details` (text, companion if “see-details” is set)

### 2. Scope of Service & Coverage

- `jurisdictions_covered` (taxonomy: ws_jurisdiction, multi)
- `nationwide` (boolean, triggers companion “nationwide_rationale”)
- `nationwide_rationale` (text, companion, required if nationwide is true, fallback: “see-notes”)
- `community_scope_labels` (taxonomy: ws_community_scope, multi)  
    (Ex – `utah`, `francophone`, `rural-only`, `prison-population`, `lgbtqia`, `clothing-optional`)
- `community_scope_details` (text, companion to above)
- `service_audience_note` (text, optional: e.g. “Focuses on rural, French-speaking unionized workers reluctant to wear pants.”)

### 3. Contact & Intake

- `intake_urls` (array of url, at least one required)
- `contact_urls` (array of url)
- `contact_phones` (array of objects: `{"type": taxonomy: ws_phone_type, "number": text}`)
- `contact_emails` (array of objects: `{"type": taxonomy: ws_email_type, "address": email}`)
- `messaging_tools` (array of: `{"type": taxonomy: ws_messaging_tool, "id": text, "public": boolean}`)
- `mailing_addresses` (array of text, allow “||” separation for multi if needed)
- `in_person_availability` (boolean, trigger companion “in_person_policy_details”)
- `in_person_policy_details` (text)

### 4. Eligibility, Screening & Cost

- `income_eligibility_required` (ternary: yes | no | unclear)
- `income_eligibility_policy` (text, required if yes)
- `eligibility_criteria_summary` (text)
- `eligibility_checklist` (array of select keys: e.g. “union-membership”, “us-citizenship”, “pending-case”, “see-details”)
- `cost_models` (taxonomy: ws_aorg_cost_model, multi, required)
- `cost_policy_note` (text, companion for “see-details” cost model)
- `services_free_to_client` (boolean)
- `pro_bono_capacity` (ternary: yes | no | unclear)
- `insurance_accepted` (select: yes | no | unclear | see-details)
- `insurance_note` (text)
- `sliding_scale_note` (text)

### 5. Services & Methods

- `services_provided` (taxonomy: ws_aorg_service, multi, required)
- `services_additional_details` (text, required if “additional” or “unclear” is selected)
- `service_modalities` (taxonomy: ws_service_modality, multi: e.g., direct-rep, peer-support, legal-aid, mental-health, advocacy, investigation, training, technology, research, financial-assistance, housing-assistance, surveillance-mitigation, etc.)
- `methods` (text, optional, freeform explanation)
- `service_exceptions` (text, e.g. “No retaliation-only or post-conviction assistance.”)
- `service_languages` (taxonomy: ws_language, multi)
- `service_languages_additional` (text, required if “additional” is present)

### 6. Security & Privacy

- `has_secure_channel` (ternary: yes | no | unclear)
- `secure_contact_url` (url, conditional on yes)
- `secure_contact_tool` (taxonomy: ws_secure_tool, conditional)
- `secure_tool_details` (text, “other” companion)
- `anonymous_pre_consult_possible` (ternary: yes | no | unclear)
- `privacy_policy_url` (url, optional)
- `privacy_policy_quality_rating` (select: ideal | sufficient | weak | bad | none | see-details)
- `privacy_policy_details` (text)
- `accessibility_rating` (select: “fully-accessible”, “partially-accessible”, “not-accessible”, “unclear”, “see-details”)
- `accessibility_details` (text)
- `retaliation_safe_space` (boolean, true if org explicitly signals “no retaliation zone” or equivalent pledge)
- `clothing_policy` (taxonomy: ws_clothing_policy, single, for our “edge of edge” cases)
- `clothing_policy_details` (text, required if not “default-policy”)
- `opsec_rating` (select: ideal | partial | weak | none | see-details)
- `opsec_policy_details` (text)

### 7. Credentials & Legitimacy

- `licensed_attorneys_on_staff` (ternary: yes | no | unclear)
- `attorney_info` (array of objects: bar_state, name, contact, years_experience)
- `accreditation` (text)
- `external_verification_urls` (array of urls, e.g. GuideStar, Charity Navigator)
- `legitimacy_url` (url)
- `major_awards` (array of text)
- `major_partnerships` (array of text)
- `last_reviewed_date` (date)
- `credentials_details` (text, freeform)

### 8. Access & Barriers

- `physical_accessibility` (select: “fully-accessible”, “partial”, “none”, “unclear”, “see-details”)
- `barriers_summary` (text)
- `discrimination_policy_url` (url)
- `discrimination_policy_rating` (select: ideal | sufficient | weak | none | see-details)
- `discrimination_details` (text, “see-details” companion)
- `cultural_competency` (select: ideal | partial | weak | none | see-details)
- `cultural_competency_details` (text)

### 9. Internal Relationship & Ops Notes

- `internal_contact_name` (text)
- `internal_contact_role` (text)
- `internal_contact_email` (email)
- `internal_contact_phone` (text)
- `internal_last_contacted` (date)
- `internal_relationship_notes` (text)
- `internal_priority_flag` (select: high | med | low)
- `internal_review_status` (select: ready | needs-confirm | do-not-contact | see-details)
- `internal_status_details` (text)

### 10. Narrative & Editorial Overflow

- `editorial_oversight_notes` (text)
- `story_of_help` (text, optional, "Tell the story of an actual or archetypal client" — used for directory “in practice” cases)
- `editorial_classification_notes` (text, for ambiguous or edge orgs)
- `source_confidence_rating` (select: ideal | high | medium | low | see-details)
- `researcher_review_notes` (text)
- `schema_gaps_found` (boolean)
- `schema_gaps_details` (text, required if boolean is true)

### 11. System & Audit Hidden Fields

- `_id` (internal ingest dedupe)
- `_ingest_method` (e.g. “human”, “ai”, “partner-provided”, “org-self-edit”)
- `_last_edit` (datetime)
- `_last_editor` (user)
- `_source_name`
- `_run_notes`
- `_major_error_flag`
- `_major_error_notes`

---

## Conditional, Sentinel & Sibling Patterns

- Any field with `_details` suffix is either required by a sentinel value ("has-details", "see-details", "unclear" as appropriate), or appears as a narrative companion if taxonomy values fail to capture reality.
- Sentinel taxonomy values: see-details, has-details, unclear. Always result in a human-readable breadcrumb.
- Sibling fields: e.g., if `has_secure_channel` is yes, secure_contact_tool, secure_contact_url, secure_tool_details must be filled.
- “See-notes” in any select/tax field always triggers freeform or editorial overflow next to the field.
- Any multi-value taxonomy permits “other” or “additional”, which always triggers companion freetext.
- Arrays are always permitted on repeatable and combinatory attributes (contact, methods, addresses, partnerships, etc).

---

## New Taxonomies & Proposal Table

#### New/Expanded Taxonomies Proposed

- **ws_community_scope**
    - utah
    - francophone
    - lgbtqia
    - indigenous
    - disability
    - rural
    - urban
    - union
    - government-only
    - faith-based
    - clothing-optional
    - substance-use
    - formerly-incarcerated
    - immigrant
    - professional-discipline
    - digital-only
    - home-visit
    - see-details

- **ws_messaging_tool**
    - signal
    - protonmail
    - tutanota
    - wire
    - keybase
    - whatsapp
    - telegram
    - facebook-messenger
    - wechat
    - slack
    - teams
    - traditional-mail
    - ansi-bbs
    - see-details

- **ws_phone_type** and **ws_email_type** (add "secure", "alias", "burner", "restricted", "see-details")

- **ws_clothing_policy**
    - default-policy
    - clothing-optional
    - uniforms-required
    - costumes-expected
    - “zoom-camera-on”
    - “zoom-camera-off”
    - see-details

- **ws_service_modality**
    - direct-representation
    - legal-advice
    - peer-support
    - mental-health
    - advocacy
    - financial-assistance
    - housing-assistance
    - surveillance-mitigation
    - training
    - campaign
    - research
    - technology
    - resource-library
    - organizing
    - referral-only
    - see-details

---

## Recommended Rule Innovations & Rule-Bending

- Any taxonomy in this schema can include “see-details” or “has-details” or “unclear” to permit real-world overflow.
- “Edge case” coverage is not a bug but a feature—taxonomy expansion should be driven by real-world, narrative-driven evidence. “Clothing-optional” should not only be possible, it should be welcomed wherever exhibited.
- For any field that can be multi-typed (e.g. secure_contact_tool), allow array of type and detail (some orgs genuinely want to be Contacted via both Signal and ProtonMail).
- Any “conditional” field becomes mandatory if the “see-details” or “has-details” value is present, or if more narrative is required to avoid user confusion.
- “Access and Barriers” tab must exist so that “inaccessible” or “discriminatory” orgs can be truthfully indexed without “shadow banning.”
- Editorial overflow is always available in every tab as `_notes`/`_details`/`_narrative`.
- Allow for exploratory/experimental fields, audited by "schema_gaps_found" and "schema_gaps_details".

---

## Edge Case Handling

### Case: Utah, Clothing-Optional, Francophone Peer-Only Hotline

- `jurisdictions_covered`: ["ut"]
- `nationwide`: false
- `community_scope_labels`: ["utah", "francophone", "clothing-optional"]
- `community_scope_details`: "Organization only accepts peer-support clients with a demonstrated willingness to participate sans clothing. Hotline may switch to English if absolutely necessary. French is preferred."
- `clothing_policy`: "clothing-optional"
- `service_languages`: ["french", "english"]
- `service_modalities`: ["peer-support"]
- `in_person_availability`: false

Everything is mappable, nothing is discarded. All "absurd-outlier" orgs can be documented and surfaced for the right audience.

---

## How To Use This Schema

1. **Don’t leave the world out** for lack of a slot. If the org can help a whistleblower anywhere, anywhen, anyhow — it fits.
2. When your schema can’t express the “weirdness” — use "has-details," “see-details,” or propose a new taxonomy term.
3. “Tab” and editorial order is for humans; the slots and taxonomy infill is for machines.
4. When in doubt, let narrative, not a
