# Unified Loud-Failure Rollout — Progress & Handoff

**Purpose of this file:** record of a completed full pass converting silent
fallbacks/defaults/swallowed-errors across `ws-core` to the unified
`ws_fail_loud()` / `ws_render_or_fail_loud()` / `ws_log_loud_failure()`
mechanism defined in `plugins/ws-core/includes/admin/ws-fail-loud.php`. This
file was originally written as a mid-pass handoff (Stages 0–4 done, Stage 5 in
progress) across a token-budget wall, then updated to final state once Stage 5
(cascade, taxonomies, cpt, acf) finished in the following session. Kept as a
reference for the patterns found and the judgment calls made, in case a
similar pass is ever needed on new code.

Read `plugins/ws-core/README.fail-loud.md` first — it's the original staged
rollout plan this work follows. This file is the status update on top of it.

The user's standing instruction for this whole pass: **no silent fails, no
default values, no fallbacks that make bad input quietly become plausible
output.** ("If someone asks my legal code for a tennis match it does not get
to become a statute.") `pg-*.php` (the prompt-generator) is explicitly out of
scope — it was already converted in an earlier pass. `tool-ingest.php` is
explicitly skipped for now — it's due for a complete rewrite and redoing this
work on it now would be wasted effort.

---

## Status: what's done (Stages 0–5, complete — full pass finished)

### Stage 0 — Loader wiring
- `ws-fail-loud.php` is now required directly at the top of the
  `is_admin()` block in `loader.php`, **before** the Matrix Layer (not inside
  `$admin_files`, which loads too late — matrix seeders need it too). Get this
  wrong and every `ws_fail_loud()` call in a matrix seeder fatals with an
  undefined-function error instead of a clean `WS_Loud_Failure`.

### Stage 1 — `admin-health-check.php`, `admin-url-monitor.php`, `admin-feed-monitor.php`
Kept each file's own internal logic (options, notices, email digests, lock/TTL
recovery) intact, but routed their failures through `ws_log_loud_failure()`
too, plus fixed several previously-silent gaps with **no existing signal at
all**:
- `admin-health-check.php`: issues now deduped against a stored option before
  logging (the hook fires on every admin page load — without dedup it would
  flood the 100-entry rolling log).
- `admin-url-monitor.php`: unchecked `wp_schedule_event()` return (silent
  monitor death), a stale-lock-past-TTL case, the caught-exception path not
  reaching the unified log, unchecked `wp_mail()` on all three digest senders.
- `admin-feed-monitor.php`: unchecked `wp_mkdir_p()`/`file_put_contents()`,
  `wp_insert_post()` failure swallowed, and — the real bug — an unresolvable
  `jx_code` silently produced a **statute post with no jurisdiction term at
  all**.

### Stage 2 — Admin tools + admin core + matrix seeders
- `ws-schema-constants.php`: `ws_schema_allowed()` now throws instead of
  silently returning `[]` for a non-array constant.
- `tool-taxonomy-term-audit.php`: fixed `is_wp_error()`-vs-empty conflation in
  three live-map builders; `ws_tax_audit_get_seed_map()` now throws when
  `register-taxonomies.php` is missing/unreadable (was silently producing a
  false "everything is extra" diagnosis); removed `@`-suppressed unchecked
  file writes.
- `tool-prompt-generator.php` verified already fully converted (it's just the
  pg-* file loader, not itself pg-*.php — no changes needed).
- **`admin-hooks.php`**: `ws_sync_additional_languages_term()` /
  `ws_sync_additional_services_term()` no longer silently bail when the
  `additional` sentinel term is missing or `wp_set_object_terms()` fails.
  `ws_set_source_method()` now calls `ws_fail_loud()` instead of silently
  no-op'ing on an unrecognized method value.
- **`admin-navigation.php`**: `ws_get_attached_citation_count()` distinguishes
  a genuine query failure from "no jurisdiction term assigned."
- **`admin-major-edit-hook.php`**: `wp_insert_post()` failure and a missing/
  failed jurisdiction-term assignment on the auto-created legal-update post
  are now logged (previously: a legal-update post could be created with no
  jurisdiction term and nothing recorded).
- **`admin-procedure-watch.php`** — the most serious admin-layer finding:
  `wp_update_post()` demoting a flagged procedure to draft had its return
  value **completely unchecked**. If that write failed, the post stayed
  **live and published** with a detected authority-link mismatch while the
  admin notice claimed it had been demoted. Fixed, plus three
  `is_wp_error()`-vs-empty conflations in the same file.
- **`jurisdiction-dashboard.php`**: a `wp_get_post_terms()` failure resolving
  a jurisdiction's own term previously rendered that entire row as
  empty/missing across all eight dashboard columns, indistinguishable from a
  genuinely incomplete jurisdiction.
- **Matrix seeders** — the standout finding of the whole pass: the **same
  dead-code bug** in four separate files. A bare `return;` sat *before* an
  `error_log()`/`wp_die()` call meant to fire when a prerequisite taxonomy
  term is missing, making that error-handling permanently unreachable. One
  missing term silently aborted seeding for **the entire matrix** (all 57
  jurisdictions, or all federal agencies, or all federal statutes, or all
  procedures) with zero indication anywhere why. Fixed in:
  `matrix-jurisdictions.php`, `matrix-agencies.php`, `matrix-fed-statutes.php`,
  `matrix-ag-procedures.php`. Also: `matrix-helpers.php`'s raw
  `RuntimeException` throws converted to `ws_fail_loud()` for consistency;
  `ws_matrix_assign_terms()` now logs (doesn't throw) skipped/unresolved
  slugs instead of silently dropping them. `matrix-assist-orgs.php` was
  already doing the right thing independently (`wp_die()` on bad canonical
  data via its own `ws_assist_org_matrix_fail()` helper) — converted that
  helper to route through `ws_fail_loud()` so it gets the same persistent
  central log everything else now shares, without losing any strictness.
  `admin-matrix-watch.php`, `matrix-federal-courts.php`,
  `matrix-state-courts.php` reviewed — clean, no changes needed (the latter
  two are pure in-memory data arrays, no logic).

### Stage 3 — Query layer (`includes/queries/`)
Discipline for this stage: **leave "no results" alone** (a jurisdiction with
no jx-summary, zero attach-flagged statutes, etc. is a legitimate empty state
your render layer already handles gracefully — not a bug). Only fix the one
recurring real bug shape: a genuine `WP_Error` from a taxonomy/term lookup
silently treated identically to "this record legitimately has no data."

- `query-helpers.php`, `query-shared.php`: reviewed, clean — no changes.
- `query-jurisdiction.php`: fixed this pattern in three places, in rough order
  of blast radius: `ws_get_jx_term_id()` (called by every shortcode before
  fetching any dataset — an error here previously blanked an entire
  jurisdiction page for a real visitor); `ws_get_us_term_id()` (a failure
  silently drops the federal-law append from **every** jurisdiction page
  sitewide, cached per-request); the jurisdiction-index 24-hour cache-fill
  loop (a failure silently drops one state from the sitewide index for a
  full day).
- `query-agencies.php`: same pattern in `ws_build_agency_procedure_row()`
  (jurisdiction/protected-disclosure terms on procedure cards).
- `query-directory.php`: `ws_q_taxonomy_payload()` — the central taxonomy
  reader for **every** assist-org directory row (called ~10x per org). This
  was the highest-value fix in the query layer: the directory is the live
  Phase 2 feature actually taking real traffic right now, and a failure here
  previously made an org invisible to the filter cascade's scoring
  (`ws_filter_score_org()` reads these same taxonomy assignments) with zero
  record of why. Deduped per-taxonomy-per-request to avoid flooding the log.
- `query-general.php`: reviewed, clean — no changes.

### Stage 4 — Render layer + shortcodes
This is where `ws_render_or_fail_loud()` gets used anywhere for the first
time. Wrapped the three visitor-facing top-level render dispatchers so an
uncaught exception anywhere downstream degrades to the calm "this section is
temporarily unavailable" notice instead of a fatal white screen:
- `render-jurisdiction.php` — `ws_handle_jurisdiction_render()`'s call to
  `ws_render_jx_curated()`.
- `render-agency.php` — both branches of `ws_handle_agency_render()`
  (ws-agency and ag-procedure).
- `shortcodes-general.php` — `ws_shortcode_assist_org_directory()`'s call to
  `ws_render_directory_page()` (the directory shortcode had no equivalent
  wrap until now).

Also: `ws-statute-bold.php` had **three unchecked `preg_*` return values**.
The worst: a `null` from `preg_replace_callback()` on the bare-citation
pattern was concatenated directly into the result string — silently
**deleting that paragraph of text** from visitor-facing output on a rare PCRE
failure (e.g. backtrack-limit exceeded on long content), not just losing the
bold styling. All three now fail open (keep original text/HTML) and log.

`render-general.php`, `render-section.php`, `render-directory.php`,
`shortcodes-jurisdiction.php` reviewed — clean, pure display/templating with
legitimate empty-state handling. No changes needed.

### Stage 5 — Cascade, taxonomies, CPT, ACF (complete)

- `includes/cascade/ws-filter-context.php`: converted six `WP_DEBUG`-gated
  `error_log()` calls (log-dir creation, `.htaccess` write, log-append in both
  `ws_filter_log_profile_view()` and `ws_filter_log_request()`) to
  unconditional `ws_log_loud_failure()`. `ws-filter-config.php` — pure
  constants, no changes.
- `includes/taxonomies/register-taxonomies.php`: verified `ws_seed_all_taxonomies()`
  /`ws_seed_taxonomy()` only run on `admin_init` (confirmed `ws_fail_loud()` is
  loaded by then — same load-order check as the Stage-0 lesson), then
  converted the `trigger_error(E_USER_ERROR)`+`continue` dead-handling and all
  `wp_die("...FuQ'n Error...")` calls to `ws_fail_loud()`; routed the
  accumulated `$all_errors` batch through `ws_log_loud_failure()` too.
  `register-glossary.php`: fixed `ws_get_glossary_lookup()`'s
  is_wp_error-vs-empty conflation, widened `catch (Exception $e)` to
  `catch (\Throwable $e)` in `ws_apply_glossary_tooltips()` (DOM code can
  throw `Error`, not just `Exception`), fixed a silent `wp_insert_term()`
  failure in `ws_seed_glossary_taxonomy()`, removed an `@`-suppressed
  `file_put_contents()` in `ws_glossary_debug_log()`.
- `includes/cpt/` (all 11 files): read in full, all clean — pure
  `register_post_type()` calls, no dynamic logic. Zero edits needed.
  (`cpt-legal-updates.php`'s deletion-lock filters reviewed as
  correct-by-design, not a silent-fail candidate.)
- `includes/acf/` (4 workflow files + 11 CPT-specific files): read in full.
  Nearly all pure field registration — no changes. Four real instances of the
  `is_wp_error()`-vs-empty conflation pattern found and fixed, all in
  relationship-picker/admin-notice scoping helpers (editor-facing, not
  visitor-facing, so lower severity than the query-layer instances in Stage 3
  — but fixed anyway for consistency, since a silent picker-scope failure
  still hides a real problem from whoever would otherwise notice it):
  - `acf-jx-citations.php` — `ws_jx_cite_no_citations_notice()`: a failed
    `wp_get_post_terms()` call silently suppressed the "zero attached
    citations" editor warning on a jx-summary edit screen — same failure mode
    as "no citations yet," but for a different reason worth knowing about.
  - `acf-jx-constructions.php` — `ws_jx_construction_auto_populate_affected_jx()`:
    jurisdiction codes that failed to resolve to a taxonomy term were dropped
    from `affected_jx` with zero record, silently narrowing which
    jurisdiction pages a construction would appear on. Now logs the skipped
    codes.
  - `acf-jx-statutes.php` — `ws_jx_legal_get_post_jurisdiction_term()`: shared
    by three agency-relationship-picker scoping call sites; a failed
    `wp_get_post_terms()` silently fell back to an unscoped picker with no
    signal.
  - `acf-ag-procedures.php` — `ws_ag_procedure_scope_parent_ids()`: same
    shape, `!empty() && !is_wp_error()` ordering on two term lookups meant a
    genuine WP_Error on either produced the identical "show unscoped list"
    result as the documented "no terms assigned yet" fallback.

  Left alone as harmless/dead, not fixed: `get_term_by()` calls guarded with
  `if ( ! $term || is_wp_error( $term ) )` in several `*_details_conditional()`
  functions (`acf-jx-citations.php`, `acf-jx-constructions.php`,
  `acf-jx-common-laws.php`, `acf-jx-statutes.php`) — `get_term_by()` never
  actually returns `WP_Error` (only `WP_Term|false`), so the `is_wp_error()`
  branch there is unreachable dead code, not a silent-fail bug.

---

## Status: what's left

Nothing. Stages 0 through 5 are all complete — every file in `includes/`
except `pg-*.php` (out of scope per standing instruction, already converted
in an earlier pass) and `tool-ingest.php` (explicitly deferred pending its
rewrite) has been read function-by-function and fixed where the recurring
bug shapes below were found.

---

## The pattern to keep hunting for

Three shapes accounted for nearly every real bug found in this pass, in order
of how often each one turned up:

1. **`is_wp_error($x) || empty($x)` treated as one case**, silently producing
   the same "empty/not found" result for both a genuine query failure and a
   legitimate "no data yet" state. Fix: split the branches, log only the
   `is_wp_error()` one, leave the legitimate-empty case untouched. This was
   the single most common finding across the query layer and several admin
   files.
2. **Dead-or-unreachable error handling** — a `return`/`continue` placed
   *before* the `error_log()`/`wp_die()`/throw that was clearly meant to fire.
   Found identically in four matrix seeders. Worth grep'ing for
   `return;\s*\n\s*(error_log|wp_die|throw)` patterns in anything not yet
   reviewed.
3. **Unchecked return values on write operations** —
   `wp_update_post()`, `wp_insert_post()`, `wp_set_object_terms()`,
   `file_put_contents()`, `wp_mkdir_p()`, `wp_mail()`, `wp_schedule_event()`,
   `preg_replace_callback()`/`preg_split()`. Each of these can return
   `false`/`null`/`WP_Error` and every one of them was found unchecked
   somewhere in this codebase before this pass. The `admin-procedure-watch.php`
   `wp_update_post()` case was the most serious instance found overall — a
   post that was supposed to be demoted stayed live and published.

## Judgment calls made throughout (for consistency if continuing)

- **`ws_fail_loud()`** (throws, never returns) is reserved for: code paths a
  caller can/will catch (matrix seeders during install, admin-tool actions
  wrapped in try/catch at the admin-page boundary) — **never** for code that
  runs unconditionally on every single page load (`admin_notices`,
  `admin_init` hooks that fire every request), since an uncaught throw there
  breaks every admin screen, not just one subsystem.
- **`ws_log_loud_failure()`** (logs, never throws) is for exactly those
  every-request hook contexts, and for any place where continuing execution
  after a partial failure is still the right behavior (e.g. one bad slug in
  a batch shouldn't abort the whole batch).
- **`ws_render_or_fail_loud()`** is reserved for the top-level visitor-facing
  render dispatchers only (the three `the_content`/shortcode entry points
  fixed in Stage 4) — not scattered into every individual render helper,
  since the top-level wrap already catches anything thrown deeper in the
  call chain.
- Frequently-called functions (called many times per page load, e.g.
  `ws_get_us_term_id()`, `ws_q_taxonomy_payload()`) got dedup logic (static
  per-request tracking, or reliance on existing request-level caching) before
  adding a log call, specifically to avoid flooding the 100-entry rolling log
  in `ws_loud_failure_log` from a single bad request.
