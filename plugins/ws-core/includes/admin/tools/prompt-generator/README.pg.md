# includes/admin/tools/prompt-generator/

⚠ **See `README.fail-loud.md` (two directories up, at `includes/ws-core/`)
first if you're reading this after the unified error-handling pass.**
Every `throw new \InvalidArgumentException(...)` / `\RuntimeException(...)`
/ `\LogicException(...)` described below as the loud-fail mechanism has
since been converted to `ws_fail_loud('prompt-generator', $message, $data)`,
calling into `WS_Loud_Failure` / `ws_fail_loud()` from
`includes/admin/ws-fail-loud.php`. The *behavior* described below (throws
instead of defaulting) is still accurate — only the specific exception
class changed, plugin-wide. This prompt-generator folder is the reference
implementation for that migration; read `pg-config.php`'s
`ws_prompt_assert_valid_record_type()` for the pattern.

The prompt generator, split into eight files. This README exists because
`README.admin.tools.md` in the parent folder still describes this as a
single file (`tool-prompt-generator.php`) — that description predates the
split and was never updated. Point future readers here instead.

---

## File Map (load order — see `tool-prompt-generator.php`)

| File | Role | Depends on |
|---|---|---|
| `pg-config.php` | Jurisdiction context resolution, output dir, record-type validation and post-type mapping | nothing |
| `pg-exclusions.php` | Auto/manual exclusion list building and merging | `pg-config.php` |
| `pg-taxonomy.php` | Live taxonomy term reads, slug table rendering | `pg-config.php` (validator) |
| `pg-blocks-shared.php` | Blocks used by every record type: intro, policy, meta, new-terms, integrity, final contract | nothing |
| `pg-blocks-assist-org.php` | assist-org schema block | nothing — **content unverified, see file header** |
| `pg-blocks-legal.php` | statute/common-law/citation/construction schema blocks | `pg-config.php` (validator) — **statute is live-generated from ACF; the other three throw, see file header** |
| `pg-builders.php` | Assembly layer — one function (`ws_generate_prompt`) builds the complete prompt from the blocks above | all of the above except `pg-ui.php` |
| `pg-ui.php` | Admin page, form handling, single `$_POST` read point, single try/catch boundary | all of the above |

---

## The Two Rules This Folder Now Enforces

**1. Change a phrase once, in the one function that produces it.**

Before the 3.21.0 rewrite, `pg-builders.php` had two parallel functions
doing the same nine-step assembly by hand. `ws_generate_prompt()` fixed
that — one function, driven by an ordered block list
(`ws_prompt_block_sequence()`) and a data-driven RUN SCOPE footer
(`ws_prompt_run_scope_fields()`).

**2. No defaults, no fallbacks, no catch-alls — an invalid input halts
execution loudly, it never produces a plausible-looking wrong answer.**

Added in the 3.22.0 pass. `ws_prompt_assert_valid_record_type()` in
`pg-config.php` is the single gate every function that branches on
`record_type` calls before doing anything else. Before this pass,
`ws_prompt_block_sequence()` and `ws_prompt_run_scope_fields()` both
silently fell through to the legal-type shape for *any* unrecognized
record_type — ask for something that doesn't exist, quietly get a
statute-shaped answer back. That's fixed everywhere in this folder now:
every branch either succeeds with real, validated data, or throws.
`pg-ui.php` catches at one boundary (the whole generation pipeline, one
try/catch) so a refusal becomes a visible admin notice, not a fatal
white screen — but nothing between that boundary and the actual data
ever silently guesses.

If you're adding a new record type, a new shared block, or a new
RUN SCOPE field and you find yourself writing an `if ($record_type === ...)`
chain with a bare `else` or `default` that returns something generic —
stop. That's exactly the pattern both rules exist to prevent.

---

## Statute Schema Is Now Live, Not Hand-Typed

`ws_prompt_legal_schema_block('statute')` no longer contains a JSON
schema string. It calls `ws_get_jx_statute_prompt_package()` in
`acf-jx-statutes.php` — a function that already existed, built for
exactly this purpose — and renders the schema block from the live ACF
field table. Add, remove, or rename a field in `acf-jx-statutes.php`,
and the next generated prompt reflects it automatically. This is the
same pattern `pg-taxonomy.php` already used for taxonomy tables; the
schema block was the one piece still hand-maintained, and it had
visibly drifted (a genuine brace-matching bug in the source, found and
fixed during the 3.21.0 pass, before this deeper fix in 3.22.0).

## Known Gap: common-law / citation / construction

No `ws_get_jx_comlaw_prompt_package()`, `ws_get_jx_citation_prompt_package()`,
or `ws_get_jx_construction_prompt_package()` exists. What exists instead is
a set of JSON field-spec files in `in-progress/compiler/`
(`jx-comlaw.json`, `jx-citation.json`, `jx-construction.json`) — a
different, hook/logic-based schema format from the PHP args-table shape
`acf-jx-statutes.php` uses. The path from those files to a callable
prompt-package function wasn't guessed at — that's an architecture
decision, not a schema-shape one. `ws_prompt_legal_schema_block()` throws
for these three record types with the specific missing function name and
a pointer to what exists. Common-law, citation, and construction prompts
cannot be generated until this is resolved.

---

## Known Gap: `pg-blocks-assist-org.php`

The retrieved content is short (six lines) relative to the assist-org
schema's actual size (identity, scope_of_service, contact, eligibility,
security, review). Unclear if that's the whole function or a fragment.
Confirm against the live file.

---

## The Admin-Tool Query-Layer Exception

Per project rule: the query layer is non-negotiable for user-facing
output, but admin-only tools may call `get_posts()`, `get_post_meta()`,
`get_terms()`, `get_term_by()`, and `WP_Query` directly — **provided each
call site is annotated with why**. Every direct-DB call in this folder
carries that annotation as of the 3.21.0 rewrite. If you add a new one,
annotate it the same way, at the call site, not just in this README.

---

## Signature

Architecture and code for the 3.21.0 and 3.22.0 rewrite passes — the
builder consolidation, the data-driven RUN SCOPE footer, the
query-layer-exception annotations, the `$_POST` collection fix, the
live ACF-driven statute schema, and the loud-fail validation pass — by
**Claude (Anthropic)**, directed and reviewed by **Dejunai**, for
WhistleblowerShield.org.

Final decisions, as always on this project, are human-directed. This
README and the accompanying rewrite are a proposal for review, not a
merge — treat every file in this pass as a diff to read, not a patch to
trust. No PHP interpreter was available during either pass to run a real
`php -l` — every file was hand-traced for control-structure balance, but
that is not a substitute for actually linting before this touches
anything real.
