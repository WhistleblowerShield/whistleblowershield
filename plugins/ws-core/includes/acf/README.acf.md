# includes/acf/

ACF Pro field group registration for all ws-core CPTs.

Each file in this directory registers one field group for one CPT.
Shared workflow field groups (stamp, plain-english, source verify,
major edit) live in `workflow/` — see `workflow/README.md`.

---

## Files

| File | CPT | Group Key |
|---|---|---|
| `acf-jurisdictions.php` | `jurisdiction` | `group_jurisdiction_metadata` |
| `acf-jx-summaries.php` | `jx-summary` | `group_jx_summary_metadata` |
| `acf-jx-statutes.php` | `jx-statute` | `group_jx_statute_metadata` |
| `acf-jx-common-law.php` | `jx-common-law` | `group_jx_comlaw_metadata` |
| `acf-jx-citations.php` | `jx-citation` | `group_jx_citation_metadata` |
| `acf-jx-constructions.php` | `jx-construction` | `group_jx_construction_metadata` |
| `acf-agencies.php` | `ws-agency` | `group_agency_metadata` |
| `acf-ag-procedures.php` | `ag-procedure` | `group_ag_procedure_metadata` |
| `acf-assist-orgs.php` | `ws-assist-org` | `group_assist_org_metadata` |
| `acf-legal-updates.php` | `ws-legal-update` | `group_legal_update_metadata` |
| `acf-references.php` | `ws-reference` | `group_reference_metadata` |

---

## Naming Conventions

- Meta names: `ws_[record]_[field]`
- Field keys: `field_[record]_[field]` (mirror meta name without `ws_`)
- Group keys: `group_[record]_metadata`
- Tab keys: `field_[record]_[tab-label]_tab`

---

## Field Behavior Rules

- Taxonomy slugs in field config must match current registered taxonomy
  slug names — verify against `register-taxonomies.php` as source of truth.
- Multi-select taxonomy and meta fields should be pluralized in meta naming.
- Toggle fields used for conditional logic should use `has_` semantics
  consistently (e.g. `ws_jx_statute_has_exhaustion`).
- Companion explanatory fields use `*_details` suffix unless the field
  is intentionally an internal note rather than a structured companion.

For shared workflow groups and their contracts, see `includes/acf/workflow/README.md`.

---

## v3.14.0 Field Rename Pass

All field names in `acf-jx-statutes.php` and `acf-jx-common-law.php`
were renamed to match the JSON ingest schema keys exactly. All downstream
references in `query-jurisdiction.php` and `matrix-fed-statutes.php`
were updated in the same pass.

| Old name | New name | Applies to |
|---|---|---|
| `*_has_sol_details` | `*_limit_ambiguous` | statute, common-law |
| `*_sol_details` | `*_limit_details` | statute, common-law |
| `*_has_tolling_details` | `*_tolling_has_notes` | statute, common-law |
| `*_tolling_details` | `*_tolling_details` | statute, common-law |
| `*_has_exhaustion` | `*_exhaustion_required` | statute, common-law |
| `*_has_rebuttable_details` | `*_rebuttable_has_presumption` | statute, common-law |
| `*_rebuttable_details` | `*_rebuttable_presumption` | statute, common-law |
| `*_bop_details` | `*_burden_of_proof_details` | statute, common-law |
| `*_has_reward` | `*_reward_available` | statute, common-law |
| `*_url_is_pdf` | `*_url_is_pdf` | statute only |

New field added: `ws_jx_statute_bop_flag` / `ws_comlaw_bop_flag` — short
signal phrase for non-standard burden shifts (text, 120 char max).
