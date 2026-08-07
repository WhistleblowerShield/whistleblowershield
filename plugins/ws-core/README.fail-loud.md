# Unified Loud Failure System — Rollout Plan

`includes/admin/ws-fail-loud.php` is the new single mechanism. This
document is the honest, staged plan for wiring the rest of the plugin
into it. It is a plan, not a completed migration — see "What's actually
done" below for the real current state.

---

## What's Actually Done

- `ws-fail-loud.php` exists: `WS_Loud_Failure` exception class,
  `ws_fail_loud()`, `ws_render_or_fail_loud()`, `ws_log_loud_failure()`,
  one admin notice.
- The **entire prompt generator** (`pg-config.php`, `pg-builders.php`,
  `pg-taxonomy.php`, `pg-blocks-legal.php`) is wired into it — every raw
  `throw new \InvalidArgumentException(...)` / `\RuntimeException(...)` /
  `\LogicException(...)` from the loud-fail pass has been converted to
  `ws_fail_loud('prompt-generator', $message, $data)`. This is the
  reference implementation — read `pg-config.php`'s
  `ws_prompt_assert_valid_record_type()` for the pattern to copy.
- **Stage 0 — loader wired.** `ws-fail-loud.php` is now the first file in
  `loader.php`'s Admin Layer file list, loading before `admin-navigation`
  and every other admin file. It is no longer a file that exists but does
  nothing.
- **Stage 1 — the three existing admin-tool patterns folded in.**
  `admin-health-check.php`, `admin-url-monitor.php`, and
  `admin-feed-monitor.php` keep their own internal logic (options,
  notices, email digests, lock/TTL recovery) unchanged, but now also call
  `ws_log_loud_failure()` so their failures surface in the ONE
  consolidated ws-fail-loud notice too. Along the way, several
  previously-silent failure points with *no* existing signal at all were
  fixed in the same pass (not just re-routed):
  - `admin-health-check.php`: issues are deduplicated against a stored
    option before logging, so the every-page-load `admin_notices` hook
    doesn't flood the log with the same issue on every view — only a
    newly-appeared issue gets logged.
  - `admin-url-monitor.php`: `wp_schedule_event()`'s return value was
    never checked (a failed schedule meant the monitor silently never
    ran again); a lock held past its own TTL window fell through to the
    same silent skip as a normal in-window overlap; `wp_mail()`'s return
    value was never checked in any of the three digest senders (a
    misconfigured mail transport meant the one mechanism built to notify
    admins of a problem could itself fail without anyone knowing).
  - `admin-feed-monitor.php`: `wp_mkdir_p()` / `file_put_contents()`
    return values were unchecked in the data-dir bootstrap;
    `wp_insert_post()` failure and jurisdiction-term resolution failure
    in `ws_feed_ingest_item()` were both silently swallowed — a bad
    `jx_code` typed into the review UI produced a statute post with no
    jurisdiction term at all, and nothing recorded why.

## What's NOT Done

- `tool-ingest.php` (4,962 lines) — uses structured `$result['errors']`
  arrays. Does NOT yet call `ws_fail_loud()`. In progress.
- The query layer (`includes/queries/`, 6 files as currently loaded).
- The render/output layer (6 files) and shortcode layer (2 files).
- Every CPT/ACF/taxonomy registration file and the matrix seeders.
- The cascade layer (`includes/cascade/`).

---

## Why Not "Touch Every File" In One Pass

Two reasons, both real:

1. **I've only read fragments of most of these files** — search-result
   snippets, not full contents. Editing code I haven't fully read, in a
   codebase whose whole editorial philosophy is "omission over
   fabrication," would be the code equivalent of inventing a legal
   citation. The prompt generator got the full treatment because I'd
   already read all eight files in full for the two prior rewrite passes.
2. **This is exactly the shape of task you described handing to
   ClaudeCode/Codex** — large, mechanical, touches many files, doesn't
   need architectural judgment once the pattern is proven. The value I
   can add is the pattern itself (done) and reviewing the mechanical
   rollout for anything that doesn't fit the pattern (below) — not
   grinding through every file blind.

---

## Staged Rollout, By Layer

Same shape as the pg-* framework — each stage is independently
reviewable, doesn't require the next stage to exist yet.

**Stage 0 — Wire the loader.** Add `ws-fail-loud.php` to the Admin Layer
file list in `loader.php`, positioned before `tool-prompt-generator.php`
and any other file that will call `ws_fail_loud()`. Confirm the prompt
generator still works after this — it's the only subsystem currently
calling a function that doesn't exist until this step happens.

**Stage 1 — Fold in the two existing admin-tool patterns.**
`admin-health-check.php` and `admin-url-monitor.php` already do
"collect issues, show one notice" — the right move is probably NOT
"replace with ws_fail_loud() everywhere," since their existing patterns
(especially the URL monitor's try/catch/finally with email digests) do
things `ws_fail_loud()` doesn't (email admins, track recovery vs. new
failure). More likely: keep their existing internal logic, but have them
ALSO log through `ws_log_loud_failure()` so their failures show up in
the ONE consolidated notice, and eventually collapse to one `admin_notices`
hook instead of three. This needs their full content read first, not a
guess from fragments.

**Stage 2 — `tool-ingest.php` and any other admin tool with a structured
`$result['errors']` pattern.** Lower risk than Stage 1 — these are
admin-only, single-purpose tools. Convert genuine failure conditions
(not routine validation the user is expected to fix and retry, like a
malformed upload) to `ws_fail_loud('ingest', ...)`.

**Stage 3 — Query layer.** Highest scrutiny. Per the query-layer
contract, this code has no fallback path today by design — if it's
already failing loud in its own way (returning `null`/`false` on a bad
query, or already throwing), confirm that's consistent with
`ws_fail_loud()` rather than converting blindly. A query-layer function
that returns `null` for "no matching post" is NOT the same as one that
returns `null` for "the query itself was malformed" — only the second
is a loud-fail candidate. Do not convert "no results" into an exception.

**Stage 4 — Render/output and shortcode layers.** Use
`ws_render_or_fail_loud()`, not `ws_fail_loud()` directly — these are
visitor-facing. A render function that currently returns empty string or
skips a section silently on bad data is a candidate; wrap its body in
the callable passed to `ws_render_or_fail_loud()`.

**Stage 5 — Everything else** (CPT/ACF/taxonomy registration, matrix
seeders). Lowest priority — these mostly run once at `init`/`acf/init`
and already have the health-check system watching for the CPTs/taxonomies
existing. Worth a pass eventually, not urgent.

---

## Handing This to ClaudeCode

Stages 2–5 are genuinely mechanical once Stage 0 and 1 are settled by a
human (you) making the judgment calls this document flags. A reasonable
prompt for ClaudeCode once you're ready: point it at one file at a time,
give it `ws-fail-loud.php` and the prompt-generator's `pg-config.php` as
the reference pattern, and ask it to identify (not silently convert)
every silent-default/fallback/catch-all in that file, listed for your
review before any edit — same discipline this document is trying to
model, not a license to convert everything it finds without a human
checking each one is a real failure and not a legitimate "no results"
case.
