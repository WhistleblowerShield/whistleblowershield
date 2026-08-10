# includes/taxonomies/

Taxonomy registration, seeding, and query helpers for all ws-core taxonomies.
All 22 taxonomies are defined and managed entirely within `register-taxonomies.php`.

---

## Files

| File | Purpose |
|---|---|
| `register-taxonomies.php` | Central registry, registration, seeding, and query helpers for all 22 taxonomies |
| `register-glossary.php` | Glossary taxonomy registration, term seeding, transient cache, and DOM scanner |
| `taxonomy-statutes.txt` | Reference schema for jx-statute, jx-citation, jx-construction, jx-common-law CPTs |
| `taxonomy-agencies.txt` | Reference schema for ws-agency CPT |
| `taxonomy-aorgs.txt` | Reference schema for ws-assist-org CPT |
| `taxonomy-tables.txt` | Human-readable flat reference of all taxonomy terms and slugs |

> `taxonomy-common-law.txt` — pending creation for jx-common-law pipeline reference.

**Rule:** The PHP registry is the authoritative source of truth for all taxonomy slugs,
labels, and term structures. If any reference `.txt` file diverges from the registry,
update the text file in the same pass.

---

## Architecture: Centralized Registry

All taxonomy definitions live in a single global array, `$_ws_taxonomy_registry`, declared
at the top of `register-taxonomies.php`. Each entry is keyed by taxonomy slug and contains
all configuration needed for registration, seeding, and LLM prompt generation:

```php
'ws_employment_sector' => [
    'cpts'          => ['jx-statute', 'jx-common-law', 'ws-assist-org', ...],
    'plural'        => 'Employment Sectors',
    'singular'      => 'Employment Sector',
    'hierarchical'  => false,
    'seed_version'  => '1.0.0',
    'record'        => ['legal', 'assist'],
    'legal_prompt'  => '',
    'assist_prompt' => '',
    'terms'         => [
        'federal-employee'     => 'Federal Government Employee',
        'state-local-employee' => 'State & Local Government Employee',
        // ...
    ],
],
```

### Registry Key Reference

| Key | Type | Required | Notes |
|---|---|---|---|
| `cpts` | `string[]` | ✓ | CPT slugs this taxonomy is attached to |
| `plural` | `string` | ✓ | Human-readable plural label |
| `singular` | `string` | ✓ | Human-readable singular label |
| `menu_name` | `string` | — | Admin menu label; defaults to `plural` |
| `name` | `string` | — | Taxonomy `name` label; defaults to `plural` |
| `hierarchical` | `bool` | — | Defaults to `false` |
| `seed_version` | `string` | ✓ | Semver gate; bump to trigger re-seed |
| `record` | `string[]` | ✓ | `'legal'`, `'assist'`, both, or `[]` |
| `legal_prompt` | `string` | ✓ if `'legal'` in record | LLM prompt string for legal record queries |
| `assist_prompt` | `string` | ✓ if `'assist'` in record | LLM prompt string for assist-org queries |
| `order` | `int` (truthy) | — | When set, writes `display_order` term meta |
| `terms` | `array` | ✓ | See **Term Array Format** below |
| `public`, `show_ui`, etc. | `bool` | — | WordPress args; all defaulted in registration loop |

---

## Term Array Format

Terms are defined as a flat associative array. Hierarchy is expressed inline using a
stateful parent flag — no nesting required:

```php
'terms' => [
    // Flat term (top-level in flat taxonomy, or child of the active parent):
    'some-slug' => 'Term Label',

    // Parent term (top-level; subsequent terms become its children):
    'parent-slug' => ['Parent Label', 1],
        'child-slug-a' => 'Child A',
        'child-slug-b' => 'Child B',

    // Next parent resets the active parent:
    'next-parent-slug' => ['Next Parent', 1],
        'child-slug-c' => 'Child C',
],
```

- A term defined as `['Label', 1]` is inserted at root level (parent = 0) and becomes
  the **active parent** for all subsequent terms in the array.
- The next `['Label', 1]` entry resets the active parent.
- Indentation in the source is visual convention only — the parser is index-driven.

---

## Seeder Gate

Seeding is handled by `ws_seed_all_taxonomies()`, hooked to `admin_init`. It iterates
the registry and runs `ws_seed_taxonomy()` only when a taxonomy's stored gate version
does not match its `seed_version`.

Gate keys are auto-generated from the taxonomy slug:

```
ws_protected_disclosure  →  option key: ws_seeded_protected_disclosure
ws_employment_sector →  option key: ws_seeded_employment_sector
```

**To add or change terms:** bump `seed_version` in the registry entry. The seeder
will run once on the next admin load, then gate itself again.

**Never delete a gate option directly** — doing so causes the seeder to re-run against
already-seeded terms. Always use the version bump workflow.

The seeder uses `get_term_by('slug', ...)` to check for existing terms in a single DB
call. It skips `wp_update_term()` entirely if the name and parent are unchanged,
avoiding unnecessary DB writes and cache invalidation.

---

## `has-details` / `has-details-parent` Sentinel Pattern

Hierarchical taxonomies that support free-text overflow use a two-term sentinel at
the end of their term list:

```php
'has-details-parent' => ['Has Details', 1],
    'has-details'        => 'Has Details',
```

- `has-details-parent` is the parent group header (root level).
- `has-details` is its child — the actual assignable sentinel term.
- Both share the label "Has Details"; WordPress enforces slug uniqueness, not label uniqueness.

When an editor assigns `has-details` on a record, a companion ACF freetext `_details`
field becomes visible via conditional logic (injected at field load time, not at
registration time, because term IDs are only available at runtime).

Taxonomies using this pattern: `ws_protected_class`, `ws_excluded_class`, `ws_disclosure_target`.

Flat taxonomies (`ws_employer_defense`, `ws_employee_standard`, `ws_fee_shifting_rule`,
`ws_case_stage`, `ws_causation_standard`) use a plain `'has-details' => 'Has Details'`
top-level term — no parent group needed.

---

## LLM Prompt Query Helper

`ws_get_taxonomies_for_record( $record_type )` returns all registry entries whose
`record` array contains the given type (`'legal'` or `'assist'`), along with each
taxonomy's configured prompt string. Used by the admin prompt-generation tool to
dynamically build LLM context from live taxonomy tables.

```php
$legal_tables  = ws_get_taxonomies_for_record( 'legal' );
$assist_tables = ws_get_taxonomies_for_record( 'assist' );

// Each entry:
// [
//   'taxonomy' => 'ws_employment_sector',
//   'plural'   => 'Employment Sectors',
//   'singular' => 'Employment Sector',
//   'prompt'   => '',   ← fill in per taxonomy
// ]
```

A taxonomy present in both `'legal'` and `'assist'` record arrays will appear in
both result sets with its respective prompt string.

---

## Two-Phase Registration

WordPress requires taxonomies to be registered before CPTs that use them. ACF
`save_terms` / `load_terms` requires CPTs to be registered before a taxonomy's
`object_type` array is finalized.

This is resolved in `loader.php` via two-phase binding — initial registration on
`init` before CPTs, then `object_type` binding after CPTs are registered. See the
`TAXONOMY TWO-PHASE BEHAVIOUR` section of `loader.php`. Do not collapse into a
single pass.

---

## Glossary (`register-glossary.php`)

### Overview

`ws_glossary` is a private, admin-only taxonomy unattached to any CPT. It stores
legal term definitions and aliases used to inject inline tooltips into shortcode
rendered HTML. It is self-contained — all registration, seeding, caching, and
scanning logic lives in `register-glossary.php`.

Shared dependency: `ws_get_taxonomy_caps()` from `register-taxonomies.php` must
be loaded before this file.

---

### Glossary Term Structure

Terms are seeded via `ws_seed_glossary_taxonomy()`. Each entry carries:

```php
'slug' => [
    'name'    => 'Display Name',
    'desc'    => 'Plain-text definition shown in the tooltip.',
    'aliases' => 'alternate phrase|another alias|short form',
],
```

- `name` — the canonical term label stored as the WP term name.
- `desc` — stored as the WP term description; rendered as tooltip text.
- `aliases` — pipe-delimited string stored in the `ws_glossary_aliases` term meta.
  Both the canonical name and all aliases trigger the same tooltip.
  Empty string `''` means no aliases.

**Seeder gate:** `ws_seeded_glossary` option key, version `'1.0.0'`. Bump the
version string in the gate check and the `update_option` call to re-seed.

---

### Transient Cache

`ws_get_glossary_lookup()` builds and caches a flat `[ term_string => definition ]`
lookup array in the `ws_glossary_cache_` transient (TTL: `DAY_IN_SECONDS`).

- Both canonical names and aliases are keys pointing to the same definition.
- Sorted longest-string-first so multi-word phrases match before substrings.
- Cached empty for `5 * MINUTE_IN_SECONDS` when no terms exist, to prevent
  hammering `get_terms()` on every page load during setup.

The cache is invalidated automatically on any term create, edit, or delete via
the `created_ws_glossary`, `edited_ws_glossary`, and `delete_ws_glossary` hooks.

**To force a cache rebuild:** delete the `ws_glossary_cache_` transient from the
WP options table or call `delete_transient( 'ws_glossary_cache_' )` directly.

---

### Scanner Pipeline (`ws_glossary_scan` filter)

Shortcodes opt in to glossary tooltip injection by applying the filter to their
rendered HTML output:

```php
$html = apply_filters( 'ws_glossary_scan', $html );
```

`ws_apply_glossary_tooltips()` receives the HTML string and:

1. Fetches the transient-cached lookup.
2. Precomputes escaped term strings, tooltip attributes, and boundary regex
   patterns once per scan pass — not per text node.
3. Parses the HTML with `DOMDocument`, wrapped in a UTF-8 charset hint.
4. Recursively walks text nodes, skipping descendants of:
   `a`, `span`, `abbr`, `button`, `script`, `style`, `code`, `pre`, `h1`, `h2`, `h3`
5. For each non-empty text node, tests pending (unmatched) terms against the
   original plain text only — never against already-injected markup.
6. Applies all matches for a node in a single `preg_replace_callback` pass,
   longest term first.
7. First match wins globally — each term is injected at most once per scan pass.
8. Injects matched terms as:
   ```html
   <span class="ws-term-highlight" data-tooltip="escaped definition">matched text</span>
   ```
9. Returns the modified HTML body; returns original `$html` on any parse failure.

---

### Debug Controls

Two constants control scanner diagnostics. Both are defined with `if (!defined(...))`
guards so they can be overridden in `wp-config.php`:

| Constant | Default | Effect |
|---|---|---|
| `WS_GLOSSARY_SCAN_DEBUG` | `true` | Writes a scan summary line to the log after each filter pass |
| `WS_GLOSSARY_SCAN_LOG_ROTATE` | `false` | Enables log rotation at 5 MB threshold |

**Log file:** `wp-content/logs/glossary-scan.log`

Each debug line format:
```
2026-04-28 19:00:00 UTC [ws-core][glossary-debug] scan_summary nodes_total=N nodes_non_empty=N nodes_with_pending=N callback_hits=N replacements=N unique_terms=N terms=[term1, term2]
```

Set `WS_GLOSSARY_SCAN_DEBUG` to `false` in production once the scanner is
validated. The constant check is inlined directly — no wrapper function.

---

## Taxonomy Seeding and Registry Version History

### `register-taxonomies.php`
* **3.20.1:** Loud-failure pass. Migrated `wp_die()` calls in `ws_seed_all_taxonomies()` and `ws_seed_taxonomy()` to `ws_fail_loud()`.

### `register-glossary.php`
* **3.20.1:** Loud-failure pass. Fixed conflation in `ws_get_glossary_lookup()` where term failures disabled scanning; widened try/catch block to `\Throwable` to catch DOMDocument errors; logged wp_insert_term failures.
