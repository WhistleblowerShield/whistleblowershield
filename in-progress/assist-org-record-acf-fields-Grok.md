**WhistleblowerShield.org Assist-Org Schema Reinvention Proposal: "ShieldForge v1.0"**  
*Drafted as a complete conceptual overhaul inspired by the legal-record ACF proposal's naming conventions, sentinel patterns, conditional companions, and relational integrity. This throws away the legacy structure from `acf-assist-orgs.php.txt` and the JSON research prompt (`US-0-Assist-org-20260421-0001.txt`), while preserving and expanding the taxonomy tables from `register-taxonomies.php.txt` as the stable core. Fun, flexible, crisis-first, and edge-case-proof (yes, even the hypothetical Utah French-speaking clothing-optional niche support group for whistleblowers in the apparel industry).*

### Core Philosophy & New Rules (Breaking/Bending Legacy)
The legacy schema was functional but rigid: tabs felt like siloed forms, many fields were flat or repeater-heavy without strong conditionals, sentinels were underused, and it didn't deeply capture **psychosocial depth**, **training/grants**, **capacity signals**, **relationship metadata**, or **directory-matching nuance** for users in crisis (Maya pre-decision; James post-retaliation).

**New Guiding Principles** (modeled on legal-record proposal):
- **Prefix-free canonical naming** in the primary group (no `ws_aorg_*` everywhere in the data layer; use clean `snake_case` for fields, `kebab-case` for taxonomy choices). CPT prefix (`ws-assist-org`) stays for storage/relationships.
- **has_*/is_* booleans** as triggers for companions (`_details`, `_context`, `_limits`, `_notes`).
- **Sentinels**: `has-details`, `unclear`, `other`, `see-context`, `has-limits`, `has-phases`, `mixed`. Use liberally with companions. Replace vague "unclear" fallbacks where possible with `has-details` + note.
- **Sister fields**: Fields that inherit conditional logic from a trigger (e.g., `protected_class_details` sisters with `protected_classes` when `has-details` present). **Brother fields**: Parallel siblings in the same tab/group (e.g., `mental_health_details` and `peer_support_details` as brothers under a psychosocial group). **Cousins**: Loosely related across tabs (e.g., `training_programs` cousins with `additional_services` and `whistleblower_scope_details`).
- **Hierarchical & relational taxonomies** where logical (expand existing ones); flat for simple choices.
- **Dynamic tabs/groups** via ACF (or future flexible content) for extensibility.
- **Edge-case capture**: Any org (e.g., Utah-based, French-primary, "clothing-optional safe space" for apparel-industry whistleblowers facing body-image retaliation or sector-specific harassment) maps cleanly via `employment_sectors` + new terms, `languages` + `additional_languages`, `community_scope`, `protected_classes` + `has-details`, and custom notes without breaking filters.
- **Directory-first UX**: Fields drive smart filters/ranking (whistleblower_scope multiplier + service depth + capacity signals). Never leave users at a dead end—fallback to "has-details" + review notes.
- **Compassionate + Auditable**: Plain-English summaries, internal relationship notes, verification stamps, and _review_notes for pipeline handoff.
- **Fun Rule-Bending**: Introduce "vibe" or capacity qualifiers? No—keep professional. Instead, add **capacity & sustainability signals** (volunteer-led? grant-funded? burnout risk?) and **trauma-informed flags** as new taxonomies/services. Make tabs narrative-driven: "Who We Are" → "Who We Help & How" → "How to Reach Us Safely" → "What It Costs & Who Qualifies" → "Our Strength & Credentials" → "Internal Ops (hidden)".
- **New Rules Proposed**:
  - All multi-selects default to allowing `has-details` + companion.
  - Repeater fields for phones/emails/addresses get type taxonomy or select with "other" sentinel.
  - Hooks for derived fields (e.g., auto-set `serves_nationwide` if jurisdictions cover all 57; auto-flag `additional_services` if free-text present).
  - Cross-tab conditionals handled via `acf/save_post` or admin notices (like mixed-motive in legal records).
  - Omit pure invention; prioritize omission + sentinel over guessing.
  - Support Schema.org alignment for future SEO (LegalService + Organization markup hooks).

### Expanded/Updated Taxonomy Tables (Source of Truth Updates)
Keep existing from `register-taxonomies.php.txt`; propose additions/new terms below. Seed via updated `ws_seed_*` functions. Use hierarchical where grouping helps filters (e.g., disclosure_targets).

**ws_aorg_type** (expand for nuance):
- Existing + `peer-led`, `hybrid-nonprofit-legal`, `grant-making-funder`, `training-provider`, `other` (with `has-details`).

**ws_aorg_service** (major expansion for depth; add psychosocial, capacity-building):
- Existing (`legal-rep`, `consultation`, `referral`, `doc-review`, `hotline`, `retaliation`, `financial`, `advocacy`, `media`, `mental-health`, `peer-support`, `secure-drop`, `additional`, `unclear`).
- New: `trauma-informed-counseling`, `crisis-intervention`, `financial-grants`, `emergency-relocation`, `whistleblower-training`, `advocacy-campaigns`, `media-storytelling-support`, `legal-education-toolkit`, `certification-programs`, `research-policy`, `has-details`.

**New Taxonomy: ws_aorg_psychosocial_support** (flat or hierarchical; captures WoA-style depth):
- `peer-support`, `trauma-informed`, `group-circles`, `individual-counseling`, `family-support`, `suicide-prevention`, `certified-peer-specialists`, `has-details`.

**New Taxonomy: ws_aorg_capacity_signal** (for ranking/filtering sustainability):
- `staffed-attorneys`, `volunteer-network`, `grant-funded`, `donor-supported`, `low-capacity`, `high-volume-intake`, `training-provider`, `has-details`.

**ws_disclosure_type**, **ws_protected_class**, **ws_disclosure_target**, **ws_process_type**, **ws_employment_sector**, **ws_case_stage**, **ws_language** — expand with sector-specific or edge terms (e.g., `apparel-industry` under employment_sectors if needed; `french` already covered; add `indigenous-languages` or `asl` as needed via `additional`).

**ws_aorg_cost_model** — add `grant-funded-assistance`, `crowdfunded`, `no-cost-for-whistleblowers`.

**New Taxonomy: ws_aorg_eligibility_class** (for non-income filters):
- `case-type-specific`, `sector-specific`, `retaliation-severity`, `anonymity-required`, `has-details`.

### Proposed Field Structure (ACF Field Group: `group_assist_org_shieldforge`)
**Tabs** (logical narrative flow for editors + directory UX):
1. **Identity & Mission** (core identifiers, description).
2. **Who We Help & Scope** (taxonomies + details; drives filters).
3. **How We Help** (services, additional, psychosocial brothers).
4. **Safe Contact & Intake** (channels, security emphasis for crisis users).
5. **Access & Eligibility** (cost, income, other constraints).
6. **Strength & Credentials** (attorneys, training, legitimacy).
7. **Internal & Relationships** (hidden; for platform ops).
8. **Plain English & Stamps** (shared groups).

**Key Fields** (prefix-free where possible; use `ws_aorg_` only for legacy compatibility during transition):

**Identity & Mission Tab**:
- `official_name` (text, required).
- `common_name` (text).
- `internal_id` (text, hidden or `_ws_aorg_id` for ingest).
- `general_description` (textarea or wysiwyg; 3-5 sentences).
- `mission_quote` (textarea; verbatim from site for whistleblower_scope justification).
- `assistance_type` (taxonomy: ws_aorg_type; allow multi with `mixed` or `has-details`).
- `has_logo` (true_false) → `logo` (image, sister).

**Who We Help & Scope Tab** (filter core):
- `serves_nationwide` (true_false).
- `nationwide_example` (textarea; verbatim quote).
- `jurisdictions` (taxonomy: WS_JURISDICTION_TAXONOMY; conditional on !nationwide).
- `has_limited_scope` (true_false) → `community_scope` (textarea; e.g., "Utah apparel sector safe spaces").
- `whistleblower_scope` (number 0-3) → `whistleblower_scope_details` (textarea; required for 0-1).
- `disclosure_types` (taxonomy).
- `disclosure_targets` (taxonomy) → `disclosure_target_details` (if has-details).
- `protected_classes` (taxonomy) → `protected_class_details` (sister; e.g., "apparel workers facing unique harassment").
- `employment_sectors` (taxonomy; add edge like "clothing-optional communities" via has-details or new term).
- `case_stages` (taxonomy) → `case_stage_details`.
- `process_types` (taxonomy).
- `jurisdiction_exceptions` (textarea; e.g., "nationwide except certain military contexts").

**How We Help Tab** (services depth; brothers for related offerings):
- `services_provided` (taxonomy: ws_aorg_service) → `additional_services` (if additional).
- `psychosocial_support` (new taxonomy) → brothers: `mental_health_details`, `peer_support_details`, `trauma_informed_context`.
- `capacity_signals` (new taxonomy) → `capacity_notes`.
- `training_programs` (true_false) → `training_details` (cousin to services; e.g., "certification for peer supporters").
- `financial_grants_available` (true_false) → `grant_details`.
- `has_toolkit_resources` (true_false) → `resources_url` or `toolkit_details`.

**Safe Contact & Intake Tab** (crisis priority):
- `website_url` (url, required).
- `intake_url` (url; strict: personal assistance only).
- `contact_url` (url).
- `phones` (repeater: type select from schema + number; sentinel "other").
- `emails` (repeater: type + address).
- `mailing_address` (textarea; || separator).
- `has_secure_channel` (true_false) → `secure_contact_url`, `secure_contact_tool` (select), `secure_contact_tool_other` (sister if other).
- `anonymous_pre_consult_possible` (ternary: yes/no/unclear).
- `languages_supported` (taxonomy: ws_language) → `languages_additional` (if additional; e.g., "French primary for Utah clients").
- `has_multilingual_staff` (true_false) → `language_notes` (cousin).

**Access & Eligibility Tab**:
- `cost_models` (taxonomy: ws_aorg_cost_model).
- `has_income_limit` (true_false) → `income_eligibility_details`.
- `eligibility_classes` (new taxonomy) → `eligibility_notes` (non-income, e.g., "apparel sector only" or "clothing-optional affirmed").
- `accepts_anonymous` (true_false).

**Strength & Credentials Tab**:
- `has_attorneys` (true_false; licensed on staff).
- `accreditation` (text).
- `bar_admissions` (text or repeater).
- `legitimacy_url` (url; GuideStar etc.).
- `last_verified` (date_picker).
- `training_certification_offered` (true_false) → details (cousin to capacity).

**Internal & Relationships Tab** (hidden):
- Internal contact repeaters or fields (name, role, email, phone, last_contacted).
- `relationship_notes` (textarea).
- `partnerships` (post object or text; links to other assist-orgs or agencies).
- Shared stamps: last_edited, verification_status, plain_english_wysiwyg, etc.

**Hidden/Derived**:
- `_review_notes` (always; capture anomalies, edge cases, schema gaps).
- Derived: `_serves_all_jurisdictions`, `_disclosure_target_class`, capacity score hints, etc. (hooks).
- `has_major_edit` + description (like legal records).

**Conditional Logic Examples** (ACF + hooks):
- Psychosocial brothers appear when `mental-health` or `peer-support` in services or dedicated taxonomy.
- `has-details` in any taxonomy triggers its companion (and potentially brothers/cousins).
- Cross-tab: If `whistleblower_scope` low + strong psychosocial, surface note for editors.

### Implementation Notes & Pipeline Fit
- **Ingest/JSON Updates**: Extend the research prompt schema to output new fields/sentinels. Use `has-details` generously with breadcrumbs in `_review_notes`.
- **Directory Filters**: Leverage taxonomies + has_* for situation-based matching (pre-report mental health? post-retaliation peer support? French-speaking?).
- **Hooks Needed**: Auto-fill derives, enforce Intake Commitment Rule, flag low whistleblower_scope, cross-tab notices (e.g., "Strong psychosocial—complete details tab").
- **Edge Case Handling**: Utah French clothing-optional apparel group → `employment_sectors: private-sector` + `has-details: "apparel industry, clothing-optional safe spaces"`, `languages: french` + additional, `community_scope: "Utah"`, services via has-details.
- **Fun/Extensibility**: Future flexible content blocks for "Programs" or "Success Stories" (anonymized). Align with Schema.org LegalService/Organization for public markup.
- **Migration Path**: Keep legacy fields temporarily via clone or transition group; phase out `ws_aorg_*` prefixes.

This reinvention makes the directory more compassionate, precise, and future-proof—capturing not just "who can help" but **how safely and deeply** for whistleblowers in unique situations. It bends toward user crisis needs while maintaining rigorous auditability.

**Next Steps Recommendation**: 
1. Update taxonomies/seeds in `register-taxonomies.php.txt`.
2. Draft full `acf-assist-org-shieldforge.php` mirroring legal-record style.
3. Revise research prompt JSON schema + matrix.
4. Test with edge-case orgs and existing matrix entries (GAP, NWC, WoA, etc.).

This proposal discards the old to build something stronger—let's forge a better shield. What part should we prototype first?