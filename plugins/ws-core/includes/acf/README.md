# includes/acf/

ACF Pro field group registration for all `ws-core` CPTs.
Each file in this directory registers one primary field group; shared workflow groups live in `workflow/`.

---

## Files

| File | CPT | Group Key |
|---|---|---|
| `acf-jurisdictions.php` | `jurisdiction` | `group_jurisdiction_metadata` |
| `acf-jx-summaries.php` | `jx-summary` | `group_jx_summary_metadata` |
| `acf-jx-statutes.php` | `jx-statute` | `group_jx_statute_metadata` |
| `acf-jx-common-law.php` | `jx-common-law` | `group_jx_common_law_metadata` |
| `acf-jx-citations.php` | `jx-citation` | `group_jx_citation_metadata` |
| `acf-jx-interpretations.php` | `jx-interpretation` | `group_jx_interpretation_metadata` |
| `acf-agencies.php` | `ws-agency` | `group_agency_metadata` |
| `acf-ag-procedures.php` | `ws-ag-procedure` | `group_ag_procedure_metadata` |
| `acf-assist-orgs.php` | `ws-assist-org` | `group_assist_org_metadata` |
| `acf-legal-updates.php` | `ws-legal-update` | `group_legal_update_metadata` |
| `acf-references.php` | `ws-reference` | `group_reference_metadata` |

---

## v3.17.0 Dev Diary (Condensed)

### Core ACF Groups

- `acf-jurisdictions.php`: Jurisdiction remains a special-case metadata container with taxonomy-backed identity and slim record-management fields; it does not follow the legal-record `ws_jx_*` naming model used by statute/citation/common-law/interpretation content records.
- `acf-jx-summaries.php`: Summary field/tab/key naming is normalized to the full `summary` token (no legacy `sum` abbreviations), and summary-specific plain-language review fields remain local to this group.
- `acf-jx-statutes.php`: Legal-record naming is normalized (`name`, `key`, and conditional `field` coherence), taxonomy references use singular table names, and toggle/conditional semantics use consistent `_has_` placement.
- `acf-jx-common-law.php`: Common-law now mirrors the same naming and conditional conventions as statutes, including normalized `_wysiwyg` naming where field type is WYSIWYG and aligned `details` trigger behavior.
- `acf-jx-citations.php`: Citation fields were aligned to singular record tokening (`citation`), taxonomy-meta naming now reflects full taxonomy table stems, and `_details` companion fields follow current trigger/key conventions.
- `acf-jx-interpretations.php`: Interpretation fields were normalized to current naming conventions, including URL PDF-toggle semantics (`*_url_is_pdf`) and WYSIWYG suffixing where applicable.
- `acf-agencies.php`: Agency metadata was kept aligned with the global key/name conventions and taxonomy singular-table references while preserving agency-specific behavior and workflow attachments.
- `acf-ag-procedures.php`: Procedure taxonomy usage preserves single-select behavior for `ws_procedure_type` while maintaining the same naming/conditional normalization standards applied across ACF groups.
- `acf-assist-orgs.php`: Assist-org taxonomy detail hooks were updated to singular taxonomy tables, plural trigger fields, and correct sentinel slug behavior (`has-details`, plus special-case slugs where intended).
- `acf-legal-updates.php`: Legal update source metadata includes URL PDF-toggle pairing and consistent key/name mapping with current field-type suffix conventions.
- `acf-references.php`: Reference URL metadata now includes `is_pdf` follow-up behavior and follows the same normalized field key/name conventions used in the broader refactor.

### Shared Workflow Groups

- `workflow/acf-stamp-fields.php`: Shared authorship/review stamp fields remain centralized and attached across supported CPTs with consistent `ws_auto_*` naming and stable group placement.
- `workflow/acf-plain-english-fields.php`: Shared plain-language workflow stays centralized, including role/validation guard alignment and explicit assist-org inclusion behavior.
- `workflow/acf-source-verify.php`: Source/verification workflow remains centralized with immutable source-method stamping rules and controlled verification transitions.
- `workflow/acf-major-edit.php`: Major-edit workflow remains intentionally isolated and unchanged in behavior except for header/version normalization.

---

## Naming/Schema Rules in Force

- `name` uses `ws_[record]_[meta_distinct]`; legal records keep canonical record tokens (`jx_statute`, `jx_comlaw`, `jx_citation`, `jx_interp`, `jx_summary`).
- `key` mirrors `name` with `ws_` replaced by `field_`.
- Group keys mirror with `group_..._metadata`; tab keys end in `_tab`.
- Taxonomy fields use singular taxonomy table names (for example `ws_adverse_action_type`, `ws_remedy`).
- Taxonomy multi-select fields are pluralized in meta `name`; approved single-select exceptions are `ws_aorg_type` and `ws_procedure_type` (`field_type => radio`).
- Toggle fields used for conditional reveal follow `_has_` placement before the distinct descriptor, and conditional `field` references are updated to the renamed toggle `key`.
- Sentinel `has-details` logic is wired via `*_details_conditional` hooks for applicable taxonomy-backed detail textareas.
