# includes/queries/

The query layer — the only layer in ws-core that reads from the
database. Returns normalized PHP arrays. Never produces HTML.

---

## The Query Layer Contract

**Shortcodes, render functions, and admin surfaces never call
`get_field()`, `get_post_meta()`, or `WP_Query` directly.**

All data retrieval goes through this directory. This is the most
important architectural rule in the codebase. Violations produce
fragile output code that breaks silently when field names change.

Admin files that must read meta directly (columns, hooks, metaboxes)
carry inline comments explaining why the query layer is not used.

---

## Files and Load Order

Load order is non-negotiable. Each file depends on the one before it.

```
query-helpers.php       Pure utilities — no DB reads, no dependencies
query-shared.php        Sub-array builders — depend on helpers
query-jurisdiction.php  Primary dataset API — depends on shared
query-general.php       Cross-cutting datasets — depends on shared/jurisdiction
query-directory.php     Assist-org directory dataset API — depends on shared
query-agencies.php      Agency/procedure API — depends on shared
```

All six files are in the Universal Layer — loaded on both frontend
and admin via `loader.php`.

---

## Return Key Convention

The query layer strips all `ws_` and `ws_auto_` prefixes from PHP
array return keys. The prefix prevents collisions in `wp_postmeta`;
inside a return array there is no collision risk and the prefix adds
noise.

Every dataset function returns these standard sub-arrays:

```
author  → created_by, created_by_name, created_date,
          edited_by, edited_by_name, edited_date

plain   → has_content, plain_content, written_by, written_by_name,
          written_date, is_reviewed, reviewed_by, reviewed_by_name,
          reviewed_date

verify  → source_method, source_name, verified_by, verified_by_name,
          verified_date, verify_status, needs_review
```

Top-level keys are unprefixed and context-scoped. See each function's
`@return` docblock for the complete key reference.

Multi-value taxonomy and relationship fields are normalized to stable array
shapes in the query layer. Callers should not assume scalar fallbacks for
fields that are modeled as multi-value in ACF.

---

## Caching

| Transient | TTL | Invalidated By |
|---|---|---|
| `ws_id_for_term_{term_id}` | 24h | `save_post_jurisdiction` |
| `WS_CACHE_ALL_JURISDICTIONS` | 12h | `save_post_jurisdiction`, `delete_post` |
| `WS_CACHE_JX_INDEX` | 24h | `save_post_jurisdiction`, `delete_post` |
| `WS_CACHE_LEGAL_UPDATES_SITEWIDE` | 1h | `save_post_ws-legal-update` |
| `ws_agency_procedures_{agency_id}_` | 24h | procedure save/delete |
| `ws_agency_procedures_{statute_id}_` | 24h | `acf/save_post` stash+diff, procedure delete |

Sitewide legal updates cache stores up to 100 items. Requests ≤ 100
served via `array_slice()`. Requests > 100 bypass the cache.
Per-jurisdiction calls are never cached.

---

## Recent Hardening Notes

- Query pass includes defensive normalization for mixed scalar/array/object
    payloads from ACF/meta reads.
- Dataset mappings were synced to current non-hidden ACF fields for statute,
    citation, construction, common-law, agency, assist-org, and procedures.
- Retired/stale key reads were removed where schema ownership changed.

---

## Federal Append Pattern

`ws_get_jx_statute_data()`, `ws_get_jx_citation_data()`, and
`ws_get_jx_construction_data()` automatically append US-scoped
federal records when the requested jurisdiction is not Federal.
Each appended record carries `is_fed: true`. The render layer uses
this flag to split local and federal results into separate sections.
Does not apply to `ws_get_jx_comlaw_data()`, common laws have no
federal counterpart.

---

## The `attach_flag` Gate

The query layer functions for statutes, citations, and constructions
only return records where `ws_[record]_has_attach_flag = true`. This is the
curated summary view — editorially selected records for the
jurisdiction page.

The current attach flag meta keys are:

- `ws_jx_statute_has_attach_flag`
- `ws_jx_citation_has_attach_flag`
- `ws_jx_construction_has_attach_flag`
- `ws_jx_comlaw_has_attach_flag`

**This gate applies to the curated path only.** The Phase 2 filtered
path (`ws_render_jx_filtered()`) bypasses `attach_flag` entirely and
queries all published records. `attach_flag` is an editorial curation
tool, not a visibility gate.

---

## Query Layer Version History

### `query-jurisdiction.php`
* **1.0.0:** Initial release.
* **2.1.0:** ws-core architecture refactor.
* **2.3.1:** Content keys normalized to raw `post_content`.
* **3.0.0:** Replaced post meta join with `WS_JURISDICTION_TAXONOMY` taxonomy lookups.
* **3.1.0:** Added stamp-field unprefixing and `record` sub-array.
* **3.2.0:** Overhauled legal update system and added jurisdiction filters.
* **3.3.2:** Stripped all `ws_` and `ws_auto_` prefixes from return keys.
* **3.5.0:** Statute query rebuilt for schema ingest alignment.
* **3.6.0:** Split query layer into helpers, shared, jurisdiction, and agencies.
* **3.7.0:** Introduced assist-org directory query functions (later extracted).
* **3.8.0:** Added `ws_court_lookup()` label resolution and reference anchor support.
* **3.9.0:** Added index summary gate, repeater fallback, and taxonomy services.
* **3.10.0:** Added `ws_procedure_type` taxonomy reads.
* **3.10.3:** Added normalization helpers and defensively handled taxonomy lookups/mixed relationships.
* **3.10.4:** Moved assist-org directory queries to `query-directory.php`.
* **3.10.5:** Moved legal updates and reference queries to `query-general.php`.
* **3.20.1:** Added Sentry logging to `ws_get_jx_term_id()`, `ws_get_us_term_id()`, and index cache-fill.
* **3.20.2:** Restored `is_fed` signature logic for common law queries; corrected court label resolving for frontend; aligned jurisdiction taxonomy key reading.

### `query-directory.php`
* **3.20.1:** Added Sentry logging to `ws_q_taxonomy_payload()` for term retrieval failures.

### `query-helpers.php`
* **3.6.0:** Extracted display name resolution helper from `query-jurisdiction.php`.
* **3.9.0:** Relocated `ws_jx_term_by_code()` and `ws_court_lookup()` from matrix helpers.
* **3.20.2:** Updated court matrices loader dependency notes.

### `query-shared.php`
* **3.6.0:** Extracted `ws_build_author_array()`, `ws_build_plain_english_array()`, and `ws_build_source_verify_array()` from `query-jurisdiction.php`.

### `query-agencies.php`
* **3.9.0:** Initial release of agency procedures query function and transient cache.
* **3.10.0:** Migrated `ws_procedure_type` to `wp_get_object_terms()`.
* **3.10.1:** Hardened relationships query normalization; exposed parent details.
* **3.20.1:** Separated real `wp_get_object_terms()` query errors from empty assignments.
* **3.20.2:** Added error checking to single-value procedure type lookups.
