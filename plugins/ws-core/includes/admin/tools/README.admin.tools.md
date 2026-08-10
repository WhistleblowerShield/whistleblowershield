# includes/admin/tools/

Operator-facing admin tools for research intake and controlled ingest.

These tools are loaded only in WordPress admin and are designed for a
human-reviewed pipeline:
- generate prompts from live taxonomy/state context
- ingest validated JSON into structured CPT records

This folder currently contains:
- `tool-prompt-generator.php`
- `tool-ingest.php`
- `tool-taxonomy-term-audit.php`
- `ws-schema-constants.php`
- `prompt-generator/` — prompt generation implementation files

---

## Mental Model

Think of this directory as a two-stage launch system:

1. Prompt Generator (upstream)
   Produces high-discipline research prompts that reflect the current
   WordPress taxonomy universe and jurisdiction context.

2. Ingest Tool (downstream)
   Enforces schema and integrity guardrails, then maps approved JSON into
   canonical WordPress records.

The split is intentional. Prompt quality improves data quality before ingest,
which reduces remediation load and keeps taxonomy/data drift low.

---

## Tool Map

| File | Role | Writes To |
|---|---|---|
| `tool-prompt-generator.php` | Builds prompt templates for statute/common-law/citation/construction runs | `wp-content/logs/ws-prompts/` |
| `tool-ingest.php` | Validates and ingests JSON records into ws-core CPTs | `wp-content/logs/ws-ingest/` and `wp-content/logs/ws-ingest/ingested/` |
| `tool-taxonomy-term-audit.php` | Diffs live `ws_*` taxonomy terms against `register-taxonomies.php` seed declarations | In-page report only |

---

## Prompt Generator

### What it does

- Reads live terms with `get_terms()` so prompt taxonomies stay current.
- Supports all record types: `statute`, `common-law`, `citation`,
  `construction`.
- Merges auto-exclusions with operator-provided exclusions to reduce duplicate
  research output.
- Emits prompt files with deterministic naming:
  `[JX]-[records_requested]-[RecordType]-[YYYYMMDD-HHmm].txt`

### Why it matters

This tool keeps prompt vocabulary synchronized with production taxonomy terms.
That dramatically lowers invalid term proposals and cleanup work downstream.

---

## Ingest Tool

### What it does

- Runs strict pre-flight checks before any writes.
- Requires explicit admin confirmation after pre-flight.
- Maps structured JSON to CPT/meta/taxonomies for:
  - `jx-statute`
  - `jx-common-law`
  - `jx-citation`
  - `jx-construction`
- Handles deterministic linkage to related agencies/citations.
- Logs proposed taxonomy terms without auto-inserting them.

### Core guardrails

- `verification_status` is forced to `unverified` on ingest.
- `needs_review` is forced to false on ingest.
- `_review_notes` and `_reconciled_notes` are stripped (never persisted).
- Proposed terms are removed from record payloads before write.
- Proposed terms are appended to proposal logs for human review.
- Detailed run logs are written under `ingested/` to isolate high-volume detail.

### Current log layout

`wp-content/logs/ws-ingest/`
- `preflight-errors.log`
- `imported.log` (with `has_warnings` and `has_failures` flags)
- `citations-breadcrumbs.log`
- `proposed-terms-log.json`
- `ingested/[JX]-[record_count]-[timestamp]-ingest.txt`

---

## Taxonomy Term Audit Tool

### What it does

- Scans all live `ws_*` taxonomies (excluding `ws_glossary`).
- Parses `register-taxonomies.php` seed functions to derive expected slugs.
- Reports:
  - extra terms (live but not seed-declared)
  - missing terms (seed-declared but not live)

### Why it matters

This gives operators a fast admin-side drift check without running WP-CLI,
and it catches taxonomy-term divergence early during ingest-heavy phases.

---

## Operating Sequence (Recommended)

1. Generate prompt in admin.
2. Run external research + reconciliation workflow.
3. Validate JSON package quality.
4. Run ingest pre-flight and review warnings.
5. Confirm ingest.
6. Review `imported.log` and run detail log in `ingested/`.
7. Resolve pending taxonomy proposals manually.

This sequence minimizes bad writes and keeps auditability high.

---

## Data Integrity Rules

- Do not bypass pre-flight.
- Do not silently auto-create taxonomy terms from AI output.
- Treat `with_errors` advisory blocks as signals, not authority.
- Keep source attribution consistent so audit/review tooling remains reliable.
- Preserve deterministic naming/normalization rules; avoid heuristic drift.

---

## Troubleshooting Quick Notes

### Pre-flight fails immediately

Check:
- `batch_completed` sentinel
- `record_count` mismatch
- malformed JSON or missing top-level keys
- wrong `json_format_version`

Primary log: `preflight-errors.log`

### Ingest says warnings but no failures

Expected in some runs. Check `imported.log` semantics:
- `has_warnings: true` can coexist with `has_failures: false`

### Missing linkage (agencies/citations)

Inspect latest run file in `ingested/` for per-record linkage notes.
These logs are the authoritative trace for what was created, reused,
linked, or skipped.

---

## Extension Guidelines

When adding a new admin tool in this folder:

- Register under Tools menu with `manage_options` capability.
- Keep writes append-only where practical.
- Use explicit version/changelog docblocks at file top.
- Prefer deterministic IDs/slugs over fuzzy matching.
- Add a short section to this README with role, outputs, and guardrails.

---

## Security Posture

- All tool pages are admin-only.
- Log directories are bootstrapped with `.htaccess` deny rules.
- No direct frontend execution paths.

If server config ignores `.htaccess`, enforce equivalent deny rules at the
web server layer.
