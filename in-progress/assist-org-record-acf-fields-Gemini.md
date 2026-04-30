This proposal outlines a complete, ground-up reboot of the **Assistance Organization** (`ws-assist-org`) data architecture. It abandons the legacy `ws_aorg_` prefixes in favor of the clean, logic-driven schema established in the Legal Record reboot.

This schema is designed to capture everything from multi-national legal giants to hyper-niche grassroots groups (e.g., your "French-speaking, clothing-optional Utah advocates") with surgical precision.

---

# Proposal: Assist-Org "Clean Slate" Schema (v4.0.0)

## 1. Naming & Relationship Conventions
* **Prefix-Free**: All meta keys are stripped of `ws_aorg_`.
* **Brother Fields**: Field pairs that work in tandem to define a single concept (e.g., `value` + `unit`).
* **Sister Fields**: Conditional companions that inherit visibility from a `has_*` boolean or a `has-details` sentinel.
* **Cousin Fields**: Relational fields that link to other Post Types (e.g., linking an org to the specific `jx-statute` it litigates).
* **Sentinel Values**: `has-details`, `see-context`, and `unclear`.

---

## 2. Updated Taxonomy Strategy
*Update to `register-taxonomies.php`.*

| Taxonomy | New/Updated Terms / Purpose |
| :--- | :--- |
| **`ws_org_model`** | *Rename from `ws_aorg_type`*. Adds: `mutual-aid`, `academic-clinic`, `professional-association`. |
| **`ws_org_culture`** | **NEW.** Captures niche edge cases. Terms: `trauma-informed`, `secular`, `faith-based`, `anonymous-first`, `niche-lifestyle` (Triggers "Cousin" details). |
| **`ws_service_tier`** | **NEW.** Defines depth. Terms: `full-representation`, `limited-advice`, `referral-only`, `technical-assistance`. |
| **`ws_resource_type`** | **NEW.** Terms: `financial-grant`, `emergency-housing`, `witness-protection-private`, `legal-defense`. |

---

## 3. ACF Field Architecture

### Tab 1: Identity & Mission
*Focus: Who are they and how obsessed are they with whistleblowers?*

* **`internal_id`** (Brother: `_id` hidden) —
* **`official_name`** / **`common_name`**
* **`mission_statement`** (Sister: `mission_source_url`)
* **`focus_scope`** (Brother: `focus_justification`) — (Integer 0–3)
* **`is_active`** (Bool)

---

### Tab 2: Service Matrix (The "Utah French" Tab)
*Focus: Geographic and cultural mapping.*

* **`is_nationwide`** (Bool: Trigger)
* **`jurisdictions`** (Taxonomy: `WS_JURISDICTION_TAXONOMY`)
* **`has_community_footprint`** (Bool)
    * **`community_footprint_details`** (Sister: e.g., "Utah")
* **`languages`** (Taxonomy: `ws_language`)
    * **`language_details`** (Sister: e.g., "Dialect specifics/French Proficiency")
* **`org_culture`** (Taxonomy: `ws_org_culture`)
    * **`culture_details`** (Sister: Captures "Clothing Optional" or other environmental nuances).
* **`employment_sectors`** / **`protected_classes`** (Taxonomies)

---

### Tab 3: Procedural Pipeline
*Focus: Where in the legal "meat-grinder" do they help?*

* **`protected_disclosures`** / **`disclosure_targets`** (Taxonomies)
* **`process_types`** (Taxonomy: `ws_process_type`)
* **`case_stages`** (Taxonomy: `ws_case_stage`)
* **`service_tiers`** (Taxonomy: `ws_service_tier`)
* **`services_provided`** (Taxonomy: `ws_aorg_service`)
    * **`service_details`** (Sister)

---

### Tab 4: Intake & Security Protocols
*Focus: How to reach them without getting caught.*

* **`website_url`** / **`intake_url`** / **`contact_url`**
* **`has_secure_channel`** (Bool: Trigger)
    * **`secure_tool`** (Choice: `Signal`, `Proton`, `SecureDrop`, `has-details`)
    * **`secure_protocol_instructions`** (Sister: "Don't use work WiFi")
* **`communication_methods`** (Repeater: `method_type` + `method_value`)

---

### Tab 5: Eligibility & Cost
*Focus: Can the user afford them / Do they qualify?*

* **`cost_models`** (Taxonomy: `ws_aorg_cost_model`)
* **`is_income_restricted`** (Bool: Trigger)
    * **`income_threshold_details`** (Sister: "Below 200% Poverty Line")
* **`is_anonymous_friendly`** (Bool)
* **`eligibility_context`** (Freetext)

---

### Tab 6: Credentials & Audit
*Focus: Trust but verify.*

* **`has_attorneys`** (Bool)
* **`bar_jurisdictions`** (Taxonomy: `WS_JURISDICTION_TAXONOMY`)
* **`legitimacy_url`** (Sister: `verification_source`)
* **`verification_status`** (Choice: `unverified`, `verified`)
* **`last_reviewed_date`** / **`audit_notes`**

---

## 4. The Relationship Matrix (Cousins & Brothers)

### Brother Fields (Paired Logic)
* **`staff_size_value`** + **`staff_size_class`**: (e.g., `5` + `attorneys`).
* **`waiting_period_value`** + **`waiting_period_unit`**: (e.g., `2` + `weeks`).

### Cousin Fields (Cross-CPT Links)
* **`related_statutes`**: (Post Object: `jx-statute`). Links an org directly to the laws they specialize in.
* **`related_agencies`**: (Post Object: `ws-agency`). Links a non-profit to the specific government agency they oversee or "watchdog."
* **`precedent_victories`**: (Post Object: `jx-citation`). Links an org to the court cases they won.

### Sentinel-Sister Logic
* **`has_niche_culture`** (Bool) → **`niche_details`** (Sister).
    * *Application*: If `ws_org_culture` contains `niche-lifestyle`, the researcher populates the **`niche_details`** sister field with "Clothing-optional facility rules".