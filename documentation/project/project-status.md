# Project Status

**Last updated:** 2026-04-04
**Plugin version:** 3.14.0
**Prompt template version:** v2.0.14
**NotebookLM Ruleset version:** v1.0.8
**Environment:** Live — behind construction page at whistleblowershield.org

---

## Current State

The `ws-core` plugin is live on the production server behind an
"under construction" page. The install/seed loop is complete — all
57 jurisdiction posts exist, all matrix seeders have reached their
correct gate versions, and the jurisdiction dashboard is green.

The ingest pipeline is operational. NJ (7 statutes) and MA (7 statutes)
have been ingested. CA (22 statutes) is in progress through NotebookLM
remediation. WY statutes and common law doctrine are pending.

---

## What Is Complete

**Plugin architecture** — 11 CPTs, 17 taxonomies, 15 ACF field groups,
9 matrix seeders, 4-file query layer, 6-file render layer, 2-file
shortcode layer, full admin layer including audit trail, editorial
workflow, monitoring systems, jurisdiction dashboard, and two admin
tools (prompt generator + ingest processor).

**jx-common-law CPT** (v3.13.0) — judicially-recognized common law
whistleblower protection doctrines. Full CPT, ACF field group, query
function (`ws_get_jx_common_law_data()`), and render stub. Participates
in all shared taxonomies. Uses `ws_cl_` meta key prefix. SOL almost
always `limit_ambiguous: true` — common law borrows analogous limitations
periods. Wyoming is the first planned common law record
(`WY-CL-PUBLIC-POLICY`).

**ACF field rename pass** (v3.14.0) — all ACF field names in
`acf-jx-statutes.php` and `acf-jx-common-law.php` renamed to match
JSON ingest schema keys exactly. All downstream references in
`query-jurisdiction.php` and `matrix-fed-statutes.php` updated.
New field: `ws_jx_statute_bop_flag` / `ws_cl_bop_flag`.

**Admin tools** (`includes/admin/tools/`):
- `tool-generate-prompt.php` (v3.13.1) — generates AI research prompts
  from live WordPress taxonomy data via `get_terms()`. Four record types:
  statute, common-law, citation, interpretation. Outputs to
  `wp-content/logs/ws-prompts/[JX]-[N]-[Type]-[timestamp].txt`.
- `tool-ingest.php` (v3.14.0) — processes validated JSON batches and
  writes statute records to `jx-statute` CPT. Three-phase pipeline:
  pre-flight validation → admin confirmation → record processing.
  Four ledger/breadcrumb logs in `wp-content/logs/ws-ingest/`; detailed run logs in `wp-content/logs/ws-ingest/ingested/`.

**Ingest pipeline** — fully operational multi-model AI research pipeline
with human review gate. See pipeline section below.

**Documentation** — 16-document set in `/documentation/` plus plugin-level
README and 10 directory-level READMEs in `ws-core/includes/`. All updated
to reflect v3.14.0 state.

---

## Ingest Pipeline — Current State

### Architecture (five layers)

1. **ChatGPT (GPT-5.4 Thinking)** — primary research model. Best
   multi-statute output across all models tested. Produces disciplined
   JSON with strong citation quality and honest integrity blocks.
   Known minor issue: `fee_shifting: []` on records without fee shifting
   data — handled by ingest tool cleanup.

2. **Gemini 3.0 Thinking** — reformat pass specialist. Using a
   dedicated reformat prompt (`ws-prompt-template-v2.0.13-gemini-reformat.txt`)
   produces zero schema violations. Best single-model schema compliance.
   Identity issues on launch day (called itself a Flash preview from
   nine months prior) — timestamp must be manually corrected before
   NotebookLM merge.

3. **Grok (xAI)** — disqualified for multi-statute jurisdictions.
   Anchors on CEPA (or equivalent primary statute) and refuses to
   find others regardless of mode (Auto, Expert, Fast). Retired from
   primary rotation.

4. **NotebookLM** — fact-checker, merge layer, and ruleset-enforced
   reconciliation engine. Operates with a permanent ruleset (v1.0.8)
   in its personality. Verifies factual fields against official statute
   text via web research. Resolves conflicts between model outputs.
   Catches forbidden citation sources, parent slugs, and schema
   violations. Identity and timestamp verification (CHECK 1 + CHECK 2)
   run before any schema review — failed checks abort processing.

5. **Human review** — final approval gate. All records require human
   sign-off before ingest. `jx-summary` records are always
   `human_created` by enforced policy.

### Prompt template — v2.0.14

Key additions since v2.0.8:
- Full omit-when-empty field lists (arrays and strings)
- `fee_shifting` in routinely-empty list
- `_reconciled_notes` added to schema — NotebookLM only, autostripped
- `specific_impact` guidance for citations (10-20 words, action-verb first)
- Court shorthand reference for citations/interpretations
- `bop_flag` field added
- Taxonomy tables generated from live WordPress database via `get_terms()`
  (prompt generator) — no hardcoded arrays, approved terms surface
  automatically

### NotebookLM ruleset — v1.0.8

Key additions since initial ruleset:
- RESEARCH AGENT IDENTITY AND TIMESTAMP VERIFICATION (CHECK 1 + CHECK 2)
- URL VERIFICATION — live web only, no internal database
- EMPTY FIELD CLEANUP — explicit omit-when-empty lists
- `_RECONCILED_NOTES` per-record audit log (autostripped at ingest)
- `verified_via` domain-name-only for agent-supplied URLs
- `has-details` sentinel valid taxonomy list — explicitly excludes
  `ws_disclosure_type` and `ws_process_type`
- Ruleset trimmed to ~7,700 chars — explanatory prose removed,
  rules-only format

### Ingest log files (`wp-content/logs/ws-ingest/`)

| File | Content |
|---|---|
| `preflight-errors.log` | Append-only. Failed preflights with filename and reason |
| `imported.log` | Append-only. Completed batches — filename, JX, counts, has_warnings flag, has_failures flag |
| `citations-breadcrumbs.log` | Append-only. Full citation strings — research trail for future jx-citation records |
| `ingested/[JX]-[timestamp]-ingest.txt` | Full detail run log per batch |

### Taxonomy proposals — pending human review

Four proposals surfaced during NJ and MA research runs:

- `victim-of-domestic-violence-or-sexual-assault` → `ws_protected_class`
  Seen in: NJ-34:11C-1. Employees who are victims (or whose family
  members are victims) of domestic or sexual violence.

- `family-member-of-whistleblower` → `ws_protected_class`
  Seen in: CA-6310, CA-6311. Employees retaliated against because
  a family member engaged in protected activity.

- `domestic-work-employees` → `ws_protected_class`
  Seen in: CA-6310, CA-6311. Domestic workers explicitly covered with
  a stated exclusion for publicly funded household employees.

- `contractors-subcontractors-agents` → `ws_protected_class`
  Seen in: US-1514A. Contractors, subcontractors, and agents explicitly
  covered under Sarbanes-Oxley § 806.

All proposals are in `proposed-terms-log.json` with `status: pending`.

### Data produced to date

**New Jersey (JX-NJ):** 7 statute records — CEPA, False Claims Act,
Wage Theft Act, SAFE Act, Workers' Comp Retaliation, PEOSH, ISRA.
Ingested. Posts #1710–1716.

**Massachusetts (JX-MA):** 7 statute records. Ingested. Posts #1717–1723.
Three records had `has-details` in `disclosure_types` (invalid) — stripped
by ingest tool and logged.

**California (JX-CA):** 22 statute records in NotebookLM remediation.
Parent slug cleanup in progress. Not yet ingested.

**Federal (JX-US):** US-1514A (Sarbanes-Oxley § 806) — clean record
with citations. Pending ingest.

**Wyoming (JX-WY):** 3 statutes + 1 common law doctrine
(`WY-CL-PUBLIC-POLICY`) — verified, pending ingest.

### Models tested — performance summary

| Model | Schema compliance | Multi-statute | Notes |
|---|---|---|---|
| ChatGPT GPT-5.4 Thinking | High | ✅ | Best overall for production runs |
| Gemini 3.0 Thinking (reformat) | Zero violations | ✅ | Reformat prompt required |
| Gemini Deep Research | Research only | N/A | Source material, not JSON output |
| NotebookLM | N/A | N/A | Merge/fact-check layer only |
| Grok (all modes) | Moderate | ❌ | Anchors on primary statute |
| Qwen | Poor | Partial | Invented slugs, identity issues |
| MetaAI | Poor | Partial | Invented slugs, called itself Gemini |
| Gemma 4 | Poor | Partial | Wrong identity, future timestamps |

---

## Pre-Launch Checklist

### Completed ✅

- [x] Plugin live on production server (behind construction page)
- [x] Install/seed loop complete — all 57 jurisdictions seeded
- [x] Health check admin notice silent
- [x] All seeder gates at correct versions
- [x] Jurisdiction dashboard green
- [x] `tool-generate-prompt.php` built and operational
- [x] `tool-ingest.php` built and operational
- [x] NJ (7) and MA (7) statutes ingested
- [x] ACF field names synced to JSON schema keys
- [x] Four ingest log files operational

### In Progress 🔄

- [ ] CA-22 statutes — NotebookLM parent slug remediation, then ingest
- [ ] WY statutes — pending ingest
- [ ] WY-CL-PUBLIC-POLICY — first common law record, pending ingest
- [ ] US-1514A — pending ingest

### Pending — Pipeline

- [ ] `render-common-law.php` — implement stub (prerequisite for Wyoming
      jurisdiction page)
- [ ] `render-assist-org.php` — extract from `render-section.php`
      (prerequisite for Phase 2)
- [ ] `taxonomy-common-law.txt` — create reference file for pipeline
- [ ] Review and action four pending taxonomy proposals

### Pending — Phase 2

- [ ] `ws_resolve_filter_context()` implementation
- [ ] `ws_render_jx_filtered()` implementation
- [ ] `ws_render_directory_taxonomy_guide()` implementation
- [ ] Phase 2 situation-based entry points

### Pending — General

- [ ] CSS pass against GeneratePress Premium — trust badge, layout
- [ ] Configure Inoreader API credentials
- [ ] Server-side crontab for URL and feed monitors
- [ ] Remove development-only notice from `ws-core.php`
- [ ] `taxonomy-common-law.txt` reference file

---

## Known Gaps

**Demo data only at launch** — NJ, MA, CA, WY, and Federal at launch.
Remaining 52 jurisdictions populated post-launch via ingest pipeline.

**Phase 2 filter cascade not built** — platform launches with curated
jurisdiction view only.

**Citation records not yet ingested** — citations from ingest runs are
preserved in `citations-breadcrumbs.log` as a research trail. A
`tool-ingest.php` extension for `jx-citation` records is a post-launch
priority.

**Common law render stub** — `render-common-law.php` returns empty string
until implemented. Wyoming page will not show common law section until
this is filled.

**Auto-stamp hook issue** — `ws_auto_stamp` hooks not firing correctly.
Parked for dedicated debugging session post-ingest.
