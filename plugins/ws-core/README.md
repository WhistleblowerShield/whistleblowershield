# ws-core

The core plugin for WhistleblowerShield.org. Implements the complete data
model, editorial workflow, and public-facing output for the platform.

**Stack:** WordPress + ACF Pro
**Requires:** PHP 8.0+, WordPress 6.0+, ACF Pro
**Version:** 3.15.0

Full project documentation is in `/documentation/`. This file covers the
rules a developer needs open while writing code.

---

## Architecture

Six layers, loaded in strict dependency order by `includes/loader.php`:

```
Universal Layer   CPTs, taxonomies, ACF field groups, query functions
                  Loaded on frontend and admin
Matrix Layer      Idempotent seeders — run once on install (admin only)
Admin Layer       ACF hooks, audit trail, monitoring, dashboard (admin only)
Tools Layer       Admin tools in includes/admin/tools/ — prompt generator,
                  ingest tool. Write to wp-content/logs/ws-ingest/.
                  See includes/admin/tools/README.md for operator workflow.
Assembly Layer    Render functions + shortcodes → HTML (frontend only)
Assets            Conditionally loaded CSS + JS
```

**Assembly Layer = render functions + shortcodes only.** The query layer
is the Universal Layer — a prerequisite of the Assembly Layer, not part
of it. Never refer to the query layer as part of the "assembly layer."

**The query layer contract** is the most important rule in the codebase:
shortcodes, render functions, and admin surfaces never call `get_field()`,
`get_post_meta()`, or `WP_Query` directly. All data retrieval goes through
`includes/queries/`. Admin files that must bypass this (columns, hooks,
metaboxes) carry inline comments explaining why.

---

## CPT Registry

11 CPTs registered. One file per CPT in `includes/cpt/`.

| Slug | Purpose | Public |
|---|---|---|
| `jurisdiction` | One post per U.S. jurisdiction (57 total) | Yes |
| `jx-summary` | Plain-language summary for each jurisdiction | No |
| `jx-statute` | Codified statutory whistleblower protections | No |
| `jx-common-law` | Judicially-recognized common law protections | No |
| `jx-citation` | Case law citations supporting statute records | No |
| `jx-interpretation` | Court rulings interpreting specific statutes | No |
| `ws-agency` | Enforcement and oversight agencies | Yes |
| `ws-ag-procedure` | Agency-specific filing procedures | Yes |
| `ws-assist-org` | Legal aid and advocacy organizations | Yes |
| `ws-legal-update` | Legal development notices | No |
| `ws-reference` | External reference materials | Yes |

### jx-common-law

Added in v3.13.0. Stores judicially-recognized common law whistleblower
protection doctrines — public policy exceptions to at-will employment,
implied covenant claims, constitutional protections — for jurisdictions
that lack codified statutory protections or where common law supplements
statutes.

Key differences from `jx-statute`:
- Anchor is a judicial doctrine, not a statute section. `ws_jx_comlaw_doctrine_id`
  (format: `[JX]-CL-[SHORT-SLUG]`) replaces `statute_id` as the stable
  pipeline identifier used in prompt exclusion lists.
- `ws_jx_comlaw_doctrine_basis` and `ws_jx_comlaw_recognition_status` are WYSIWYG fields
  that carry the primary explanatory content.
- `ws_jx_comlaw_statutory_preclusion` boolean flags jurisdictions where the common
  law claim is unavailable when a statutory remedy exists (Wyoming pattern).
- `ws_jx_comlaw_public_policy_sources` checkbox tracks what sources of law the
  jurisdiction accepts as establishing public policy (constitution, statute,
  administrative-rule, case-law, federal-law, other).
- `ws_jx_comlaw_other_sources` freetext companion — visible when `other` is checked.
- `ws_jx_comlaw_precedent_url` links to the leading case on an approved source.
- SOL is almost always `limit_ambiguous: true` — common law claims borrow
  limitations periods from analogous statutes.

Uses the same taxonomy palette as `jx-statute` and participates in the
same query layer pattern via `ws_get_jx_common_law_data()`.
Render stub: `render-common-law.php` — implement when Wyoming data build begins.

---

## Taxonomy Registry

17 taxonomies registered in `includes/taxonomies/register-taxonomies.php`.

### Shared doctrinal taxonomies

These attach to `jx-statute`, `jx-citation`, `jx-interpretation`, and
`jx-common-law`. All support `tax_query` filtering.

| Slug | Type | Notes |
|---|---|---|
| `ws_disclosure_type` | hierarchical | 6 parents, 26 children |
| `ws_protected_class` | hierarchical | 4 parents, 12 children + `has-details` |
| `ws_disclosure_target` | hierarchical | 5 parents, 13 children + `has-details` |
| `ws_adverse_action_types` | flat | 14 terms + `has-details` |
| `ws_process_type` | flat | 9 terms |
| `ws_remedies` | flat | 20 terms + `has-details` |
| `ws_fee_shifting` | flat | 4 terms |
| `ws_employer_defense` | flat | 6 terms + `has-details` |
| `ws_employee_standard` | flat | 6 terms + `has-details` |

### has-details sentinel pattern

Five taxonomies support a `has-details` sentinel slug. When selected,
a companion ACF freetext `_details` field becomes visible on the edit
screen. The sentinel signals that the record contains nuance beyond what
the registered slugs capture. Applies to `jx-statute`, `jx-common-law`,
`jx-citation`, and `jx-interpretation`.

Companion field mapping:
```
protected_class     → *_protected_class_details
disclosure_targets  → *_disclosure_target_details
adverse_action      → *_adverse_action_details
remedies            → *_remedies_details
employer_defense    → *_employer_defense_details
employee_standard   → *_employee_standard_details
```

### Other taxonomies

| Slug | Attaches To | Notes |
|---|---|---|
| `ws_jurisdiction` | All content CPTs | Canonical join key. USPS slug. |
| `ws_languages` | `ws-agency`, `ws-assist-org` | `additional` is a system sentinel |
| `ws_case_stage` | `ws-assist-org` | Phase 2 filter axis |
| `ws_aorg_type` | `ws-assist-org` | Single-value |
| `ws_employment_sector` | `ws-assist-org` | Phase 2 filter axis |
| `ws_aorg_cost_model` | `ws-assist-org` | Single-value |
| `ws_aorg_service` | `ws-assist-org` | `additional` is a system sentinel |
| `ws_procedure_type` | `ws-ag-procedure` | 3 stable terms |

---

## Naming Conventions

### ACF Field Keys

These rules govern ACF `key` values only — not `name` (meta key), `label`,
or any other property.

1. No `ws_` prefix on field keys. `field_` is sufficient namespacing.
2. Group keys end with `_metadata`.
3. Group keys include record context and must mirror the record prefix pattern.
   Examples: `group_agency_metadata`, `group_jx_statute_metadata`,
   `group_jx_comlaw_metadata`, `group_ag_procedure_metadata`.
   Approved abbreviations: `aorg`, `interp`, `comlaw`.
4. Tab field keys end with `_tab`.
5. Tab field keys should use full words, not legacy abbreviations.
   Example: `field_jx_citation_content_tab` preferred over `field_jx_cite_content_tab`.
6. Field key = `field_` + meta name with `ws_` prefix stripped.
   Example: `ws_jx_statute_official_name` → `field_jx_statute_official_name`.
7. Shared utility fields are still record-scoped in meta naming. Do not reuse
   global meta names across record types. Use `ws_[record]_*` forms (for
   example, `ws_agency_languages`, `ws_aorg_languages`,
   `ws_jx_citation_attach_flag`, `ws_jx_statute_ref_materials`), and keep field
   keys aligned to stripped meta names.

### Post Meta Keys

1. All custom meta keys carry a `ws_` prefix. No bare unprefixed keys.
2. Auto-stamp keys carry the `ws_auto_` prefix (written by hook logic only).
3. Private or internal-only non-field keys carry a leading underscore and are
   not exposed as editable ACF fields unless explicitly required.
4. Content CPT meta keys must include record-type infix. Missing infix is invalid.
5. Jurisdiction child records must use `ws_jx_` record prefix families
   (`ws_jx_statute_*`, `ws_jx_comlaw_*`, `ws_jx_citation_*`, `ws_jx_interp_*`).
6. Agency procedures are agency child records and use `ws_procedure_*`.
7. Boolean trigger fields use `_has_` naming when they gate companion fields
   via conditional logic.
   Example: `ws_procedure_has_prerequisites`.
8. Companion explanatory fields use `_details` naming. Prefer `_details` over
   `_note`/`_notes` for consistency.
9. Special case: `ws_aorg_internal_relationship_notes` remains `_notes` by
   design (freeform relationship notes, not structured companion details).
10. `*_details` naming remains plural by convention where already established.
11. Multi-select/meta-array fields must be pluralized.
12. Singular naming still applies for non-array values and taxonomy slugs unless
   the value is explicitly multi-valued.

### Render Function Names

Render functions are named after their **data type**, not the page section
they produce.

```php
ws_render_jx_common_law()   // correct — data type
ws_render_jx_case_law()     // wrong — section name
```

---

## Date Conventions

All date values written to post meta by plugin code use:

```php
current_time( 'Y-m-d' )   // local site date, date-only
```

GMT audit timestamps use `gmdate( 'Y-m-d' )`.
`current_time( 'mysql' )` is reserved for `post_date` arguments only.

---

## Query Layer Return Keys

The query layer strips all `ws_` and `ws_auto_` prefixes from return keys.

```
record  → created_by, created_by_name, created_date,
          edited_by, edited_by_name, edited_date

plain   → has_content, plain_content, written_by, written_by_name,
          written_date, is_reviewed, reviewed_by, reviewed_by_name,
          reviewed_date

verify  → source_method, source_name, verified_by, verified_by_name,
          verified_date, verify_status, needs_review
```

---

## Seeder Gates

All matrix seeders use a `ws_seeded_{slug}` option key with a semver string
value. To re-run a seeder, bump its version string — never delete the option.

---

## Version History

| Version | Summary |
|---|---|
| 1.0.0 | Initial release |
| 2.1.0 | Refactored for ws-core architecture |
| 2.3.1 | Citations, agencies, legal updates added |
| 3.0.0 | Taxonomy join replaces post meta join; attach-flag pattern; federal append; matrix seeders |
| 3.1.0 | ACF key naming rules; meta key naming rules; query layer return key standardization |
| 3.2.0 | `ws_auto_` prefix pass; legal update system overhaul |
| 3.3.0 | Dataset completeness pass; source verify system; query layer split |
| 3.4.0 | Admin layer audit; plain English fields centralized; source verify role gates |
| 3.5.0 | jx-statute ingest alignment; `ws_employer_defense` taxonomy; ACF overhaul |
| 3.6.0 | Query layer split (helpers/shared/jurisdiction/agencies); render naming rules |
| 3.7.0 | `ws_employment_sector` taxonomy; deprecated taxonomy cleanup |
| 3.8.0 | Court matrix split; interpretation system; reference page implementation |
| 3.8.1 | Post-audit pass — PHP 8 fatal fix, output escaping, race conditions |
| 3.9.0 | `ws-ag-procedure` CPT; agency render pipeline; procedure seeder |
| 3.10.0 | `ws_procedure_type` taxonomy; source verify for procedures |
| 3.11.0 | `has-details` sentinel added to 5 taxonomies |
| 3.12.0 | `ws_employee_standard` taxonomy; ACF companion field pattern for has-details |
| 3.13.0 | `jx-common-law` CPT + ACF + query function + render stub; all shared taxonomies updated to include jx-common-law; `ws_jx_comlaw_doctrine_id`, `ws_jx_comlaw_statutory_preclusion`, `ws_jx_comlaw_public_policy_sources`, `ws_jx_comlaw_precedent_url` fields |
| 3.13.1 | `tool-generate-prompt.php` added to `includes/admin/tools/`; reads live taxonomy data via `get_terms()` — no hardcoded arrays |
| 3.14.0 | `tool-ingest.php` added; ACF field names renamed throughout `acf-jx-statutes.php` and `acf-jx-common-law.php` to match JSON schema keys; four ingest log files added to `wp-content/logs/ws-ingest/` |
