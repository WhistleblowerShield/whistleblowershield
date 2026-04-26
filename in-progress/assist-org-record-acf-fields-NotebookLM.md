# Assist Org Record ACF Canonical Field Draft (v3.0.0 Proposal)

**Purpose:** Draft a unified, prefix-free field set for the `assist-org` Custom Post Type (CPT) to support the Phase 2 Directory-First pivot. This schema transitions from a simple "contact list" to a high-fidelity "matchmaking engine" for users like **James** (immediate crisis) and **Maya** (strategic consideration).

**Naming Rules Applied (Adapted from Legal Schema):**
- **Prefix-Free:** No `ws_aorg_` storage prefixes in the canonical draft.
- **Boolean Triggers:** `has_*` (presence of a service/requirement) and `is_*` (status/attribute).
- **Details Companion:** Every complex boolean or choice field has a `*_details` freetext companion.
- **Kebab-Case Slugs:** All taxonomy terms and choice keys use kebab-case.
- **Ranking Logic:** Introduction of "Integrity Signals" to power the front-end ranking/sorting.

---

## Tab 1: Identity & Affiliation
*Establishing who they are and their "vibe" (e.g., ACLU vs. a niche collective).*

| Field Name | Type | Key/Slug | Description |
| :--- | :--- | :--- | :--- |
| `official_name` | Text | — | Full legal name of the organization. |
| `common_name` | Text | — | Acronym or shorter name (e.g., "SPLC", "NWC"). |
| `org_class` | Taxonomy | `ws_org_class` | High-level type: `non-profit`, `law-firm`, `government-agency`, `union`, `media-org`, `mutual-aid`. |
| `org_affiliation` | Taxonomy | `ws_org_affiliation` | The "Vibe": `civil-rights`, `environmental`, `labor-union`, `religious`, `partisan-political`, `niche-special-interest`. |
| `description_technical` | Textarea | — | Formal mission statement. |
| `description_plain` | Textarea | — | **Core Principle:** Accessible summary for James/Maya. |
| `is_politically_active` | True/False | — | Does the org engage in lobbying or partisan work? |
| `affiliation_details` | Textarea | — | Describe specific affiliations (e.g., "French-speaking nudist collective" or "Catholic worker house"). |

---

## Tab 2: Service Model & Costs
*Addressing Maya's fear of costs and James' need for specific help.*

| Field Name | Type | Key/Slug | Description |
| :--- | :--- | :--- | :--- |
| `primary_services` | Taxonomy | `ws_org_service` | See Taxonomy Update below: `litigation`, `secure-drop`, `financial-aid`, `mental-health`. |
| `has_litigation_support` | True/False | — | Do they actually go to court? |
| `fee_structure` | Taxonomy | `ws_org_fees` | `pro-bono`, `sliding-scale`, `contingency`, `flat-fee`, `membership-required`. |
| `has_financial_aid` | True/False | — | Do they give money for living expenses/whistleblower bounties? |
| `service_capacity` | Choice | `waitlist`, `accepting-new`, `crisis-only` | Current availability status. |
| `service_details` | Textarea | — | Specific limitations (e.g., "Only accepts cases involving environmental waste"). |

---

## Tab 3: Eligibility & Coverage
*The "Matchmaking" logic.*

| Field Name | Type | Key/Slug | Description |
| :--- | :--- | :--- | :--- |
| `is_nationwide` | True/False | — | Serves all 57 jurisdictions? |
| `covered_jurisdictions` | Taxonomy | `ws_jurisdiction` | Map to standard JX table. |
| `whistleblower_scope` | Number | 0–10 | How central is whistleblowing to their mission? (10 = NWC; 2 = ACLU). |
| `protected_classes` | Taxonomy | `ws_protected_class` | Map to legal schema: `public-sector`, `healthcare-worker`, etc. |
| `disclosure_targets` | Taxonomy | `ws_disclosure_target` | Map to legal schema: `fraud`, `safety`, `national-security`. |
| `language_support` | Taxonomy | `ws_language` | Terms: `english`, `spanish`, `french`, `asl`, `other-details`. |
| `eligibility_details` | Textarea | — | Unusual requirements (e.g., "Must speak French," "Must reside on-site"). |

---

## Tab 4: Impact & Trust Signals
*Powering the Directory Ranking (Internal/Editorial Data).*

| Field Name | Type | Key/Slug | Description |
| :--- | :--- | :--- | :--- |
| `editorial_rank` | Number | 1–5 | Global weight for directory sorting. |
| `has_secure_intake` | True/False | — | Do they offer PGP, Signal, or SecureDrop? (Crucial for Maya). |
| `is_vetted_partner` | True/False | — | Have we verified their success rate? |
| `notable_victories` | Textarea | — | Cite specific cases or headlines. |
| `internal_audit_notes` | Textarea | — | **Integrity Block:** "They claim to help everyone, but only answer phones on Tuesdays." |

---

## Tab 5: Connectivity
*How James gets help NOW.*

| Field Name | Type | Key/Slug | Description |
| :--- | :--- | :--- | :--- |
| `website_url` | URL | — | Primary site. |
| `intake_url` | URL | — | Specific "Get Help" or "Contact" page. |
| `hotline_phone` | Text | — | Priority phone line. |
| `secure_email` | Text | — | Encrypted contact email. |
| `has_physical_office` | True/False | — | Presence of a walk-in location. |
| `physical_address` | Textarea | — | Street address (if not a nudist colony/hidden location). |

---

## Proposed Taxonomy Tables & Terms (New in v3.0.0)

### 1. `ws_org_service` (Expansion)
- `litigation-direct` (We are your lawyers)
- `litigation-support` (Amicus briefs/Referrals)
- `secure-disclosure` (We host a SecureDrop)
- `financial-support` (We pay your rent while you sue)
- `mental-health` (Counseling for PTSD/Retaliation stress)
- `media-amplification` (We get you on the 6 o'clock news)
- `physical-protection` (Safe houses/Witness-style support)

### 2. `ws_org_affiliation` (New)
- `civil-liberties` (e.g., ACLU)
- `workers-rights` (e.g., Labor Unions)
- `environmental-defense` (e.g., Sierra Club)
- `corporate-accountability` (e.g., NWC)
- `government-oversight` (e.g., POGO)
- `identity-specific` (e.g., LGBTQ+, Veteran, Racial Justice)

### 3. `ws_org_fees` (New)
- `no-cost-ever`
- `contingency-only`
- `sliding-scale`
- `donor-funded`
- `fee-for-service`

---

## Researcher Directive Update (For Daniel)
**Validation Rule:** For every `assist-org` record, if `whistleblower_scope` is < 5 (Indirect Help), the `affiliation_details` field **must** explain why a whistleblower would go here (e.g., "The ACLU helps whistleblowers only when the retaliation involves a First Amendment violation").

**Nuance Check:** If an organization has an "unusual" requirement (e.g., "Must be a nudist," "Only speaks French"), this **must** be flagged in `has-details` within the `ws_language` or `ws_org_affiliation` taxonomies to prevent James from wasting precious crisis time.