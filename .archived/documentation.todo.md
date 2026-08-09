# Documentation Reconciliation Guide

**Purpose of this file:** a briefing for whoever (human or agent) does the
next pass on `/documentation`. It exists so that pass doesn't have to
re-derive "is this actually true right now" from scratch, and — more
important — doesn't quietly overwrite something that's genuinely unsettled
with a confident guess. This project's own standing rule (see
`plugins/todo.md` and `in-progress/onboarding-guidance.md`) is **no silent
guessing, no invented status**. This file extends that rule to documentation
work.

This is not itself a rewrite of the docs. It's a map of where the docs
disagree with the live code, disagree with each other, or describe
something that's genuinely still undecided.

---

## Source-of-Truth Hierarchy

When two sources disagree, trust in this order:

1. **Live code** in `plugins/ws-core/` — verify by actually reading the
   file, not by trusting any README's summary of it. READMEs drift.
2. **Direct statements from Dejunai** (in conversation, or already captured
   in memory at `.claude/projects/.../memory/`) — this is the fastest-moving
   layer and is routinely ahead of everything written down, including
   `/in-progress`.
3. **`/in-progress/*.md`** — current design intent and schema doctrine, but
   "written" ≠ "built." Some of it is already live (verify first); some is
   pure proposal.
4. **Plugin-level `README*.md` files** (`plugins/ws-core/**/README*.md`) —
   generally solid on architecture/contracts, less reliable on exact field
   lists and version numbers (see confirmed gaps below).
5. **`/documentation/**`** — the formal set. Currently the stalest layer.
   Treat every concrete number or status claim here as unverified until
   cross-checked.

---

## Confirmed Contradictions (verified against live code, 2026-08-07)

These aren't guesses — each was checked by actually reading the file named.

### 1. The plugin's own bootstrap file says the site isn't live — mostly still true, needs qualifying, not correcting

`plugins/ws-core/ws-core.php.txt`, lines 3–6:

> `// !! DEVELOPMENT ONLY — NOT LIVE` ... "This plugin is NOT deployed to a
> live site. There is NO production database. NO user data exists."

Per Dejunai directly (2026-08-07): there is one live page, built entirely
from the `matrix-assist-orgs.php` seeder — which regenerates in under a
moment after a full wipe. So the "no production database / no user data"
spirit of the notice is **still accurate**: there is no precious,
hand-entered, non-reproducible production data anywhere. The "no live
data" doctrine in `in-progress/onboarding-guidance.md` (no compatibility
code, no migration adapters, no fallback writes for old meta keys) should
be kept as the operating philosophy wholeheartedly — but the notice itself
should be **qualified**, not deleted outright, since a real URL does serve
real visitors now. Something like: "no production *data* exists — the one
live page is 100% matrix-seeded and disposable" would be honest in both
directions. Ask Dejunai for the exact wording he wants before editing the
notice; don't just soften it unilaterally.

Also worth asking, not deciding unilaterally: the file is named
`ws-core.php.txt`, not `ws-core.php`. Is that deliberate (so this
checked-out copy can't be accidentally activated), or should it be renamed?
Don't rename it as part of a docs pass.

### 2. `render-common-law.php` does not exist as a file

`documentation/proposals/current-proposals.md` and
`plugins/ws-core/includes/render/README.render.md` both describe it as an
existing stub file (`ws_render_jx_common_law()` "returns empty string and
logs a debug notice"). A directory listing of `includes/render/` this
session shows no such file. Docs should say "not started," not "stub
exists."

### 3. The legal-record ACF canonical rewrite is live for `jx-statute` only

`in-progress/legal-record-acf-fields-v3.0.md` and
`in-progress/legal-record-acf-hooks-v1.0.md` describe a large schema
rewrite (prefix-free canonical field names, the `ws_legal_recognition`
recognition-taxonomy pattern, `*_context`/`*_gloss`/sister-field doctrine)
meant to apply uniformly across all four legal-record types (`statute`,
`common_law`, `citation`, `construction`).

Verified this session:
- `plugins/ws-core/includes/acf/acf-jx-statutes.php` **already uses the new
  pattern** — a compact table-builder (`ws_get_jx_statute_acf_tabs()`),
  canonical field names (`legal_recognitions`, `official_name`, etc.), and
  the live `ws_legal_recognition` taxonomy. Its own docblock self-reports
  `@version 4.0.0-draft` — even the live file doesn't consider itself
  finished.
- `acf-jx-citations.php`, `acf-jx-constructions.php`, and
  `acf-jx-common-laws.php` are **still on the old schema**
  (`ws_jx_citation_types`, `ws_jx_construction_court`, etc.). None of the
  v3.0 rewrite has reached them.

`documentation/development/ws-core-data-layer.md` describes the **old**
schema for all four types. It is now wrong for statute specifically, and
merely unrewritten (still technically accurate) for the other three.

### 4. Taxonomy count disagrees across every doc that states one

Verified live count: `plugins/ws-core/includes/taxonomies/register-taxonomies.php`'s
`$_ws_taxonomy_registry` currently registers **21** taxonomies (counted
directly from the array this session, includes `ws_legal_recognition`).

Docs currently say:
- `documentation/development/ws-core-system.md` — "Sixteen taxonomies."
- `plugins/ws-core/README.ws-core.md` — "17 taxonomies."
- `plugins/ws-core/includes/taxonomies/README.taxonomies.md` — "22
  taxonomies" (off by one from the verified live count — worth a recount
  in case one was added/removed since, rather than assuming my count is
  wrong).

**Action:** re-grep the live registry for the authoritative number and
sync every doc above to match it, in the same pass.

### 5. Plugin version number is inconsistent everywhere

Self-reported version numbers found this session, by file:
`documentation/development/ws-core-system.md` says both 3.14.0 (top) and
cites the constant as 3.19.0 (further down, same doc) — internally
inconsistent. `plugins/ws-core/README.ws-core.md` says 3.18.0.
`documentation/project/project-status.md` says 3.15.0. Live
`WS_CORE_VERSION` constant in `ws-core.php.txt` = `'3.20.0'`.
`includes/loader.php`'s own docblock was bumped to 3.20.1 during a separate
session's edits — one patch version ahead of the main constant.

**Do not cite a version number in prose without grep'ing the live constant
first.** This file's numbers are only accurate as of 2026-08-07.

---

## Superseded — do not present as the current plan

- `in-progress/phase-2-plan-pre-pivot.md` (2026-04-03) — the **original**
  Phase 2 plan: jurisdiction-page filter cascade first. Superseded by the
  pivot below. Keep for historical rationale if Dejunai wants it, but
  nothing in it is the current plan.
- `documentation/product/guidance-system.md`'s "Phase 2: Situation-Based
  Entry" section and `documentation/proposals/current-proposals.md`'s
  "Phase 2: The Situation-Based Filter Cascade" section **both still
  describe the pre-pivot plan**, with no mention that a pivot happened.
  These need a full rewrite, not a patch — the current plan isn't a variant
  of what they describe, it's a different sequencing entirely (directory
  first, jurisdiction cascade second).
- The correct current plan is `in-progress/phase-2-pivot.md` (2026-04-06,
  status "Active," "Highest Priority") — **and that file is itself now
  behind reality** (see next section).

---

## Ahead of every written doc — ask Dejunai, don't infer

- Per Dejunai directly (2026-08-05, not written anywhere in the repo): the
  directory-first pivot isn't just "active," it has a **live beta**
  covering **21+ assist organizations**, and the main site itself is live
  (not the under-construction gate `documentation/project/project-status.md`
  describes). `in-progress/phase-2-pivot.md`'s milestone list (Filter
  Contract Freeze → Directory Filter Engine → UI+Fallback →
  Logging+Admin Review → QA+Soft Launch) has presumably progressed past
  where the file's last-updated date would suggest — but that file has no
  checkboxes reflecting real status, it's a plan doc, not a tracker.
  **Ask Dejunai which milestones are actually done** rather than inferring.
- Ingest pipeline stats in `documentation/project/project-status.md` (NJ 7
  + MA 7 statutes ingested, CA 22 in progress, WY + Federal pending) predate
  the legal-record ACF rewrite (see contradiction #3). If field names
  changed since those records were ingested, **ask whether that data needs
  re-verification under the new schema** — don't assume it was migrated,
  and don't assume it wasn't.
- `tool-ingest.php` itself is mid-rewrite per explicit instruction this
  session ("skip it, it's due for a complete rewrite") — anything the docs
  say about the ingest tool's current behavior should be treated as
  describing the *pre-rewrite* tool until told otherwise.

---

## Deferred / on ice — real proposals, not rejected, but not current work

State these as "deferred," not as upcoming or in-progress:

- County/municipal jurisdiction coverage — `current-proposals.md`,
  "deferred indefinitely."
- Legal-professional/attorney research backend — explicitly out of scope
  for initial launch; would be a separate layer if ever built.
- Block-based (Gutenberg) rendering — "deferred indefinitely — shortcodes
  are sufficient."
- Jurisdiction cross-comparison view — deferred but would directly serve
  the Daniel persona; data model already supports it, no code exists.
- Private law-firm directory layer (`in-progress/future-law-firms.md`,
  `in-progress/aorg-exclusions-short-list.md`) — deliberately excluded from
  the assist-org matrix; a real future-layer proposal, not a rejection.

### Explicitly on the fence — the source doc itself hasn't decided, don't decide for it

- `ws_legal_update_multi_jurisdiction` field — stored, has no query/render
  logic anywhere. `current-proposals.md` itself says "consider removing if
  not planned." That's a question, not a decision already made. **Ask
  Dejunai**, then update the doc to reflect whatever he says — don't
  silently pick "keep" or "remove."

---

## Two assist-org schema drafts — NEITHER is canonical, both are agent output

Correction from Dejunai (2026-08-07), overriding what `in-progress/onboarding-guidance.md`
implied: both existing drafts are prior AI agents' attempts to apply the
legal-record schema pattern to assist-orgs — one at full depth, one
shallow. Neither is Dejunai's own design, and neither should be treated as
a spec.

- `in-progress/assist-org-record-acf-fields-Codex.md` — the **deep**
  attempt (12 tabs, internal-ops fields, verification-attempt repeaters,
  derived scoring fields).
- `in-progress/assist-org-phase-2-starting-point-refactor.md` — the
  **shallow** attempt, bounded to "the smallest adjustment needed to
  resume the Phase 2 directory pivot."
- **The real assist-org schema does not exist yet.** It's Dejunai's stated
  intent to write it himself; as of now it's "actively in development,
  with nothing formal." Do not present either draft as the plan in any
  doc — at most, note that both exist as prior exploratory attempts. Any
  documentation section describing the assist-org data model should say
  "schema not yet finalized" rather than picking one draft to summarize.
- `acf-assist-orgs.php` (read in full this session) is still on an older
  schema than either draft describes, and `matrix-assist-orgs.php` has
  already moved ahead of the ACF registration on some fields (typed
  phone/email repeaters, `ws_protected_class` assignment) — so "which
  schema is the ACF file on" isn't even a single clean answer per field
  today, independent of which draft eventually wins.

---

## How To Use This File

1. Before touching a documentation file, check whether it's named above.
2. If a fact needs correcting and this guide confirms the correction via a
   live-code check (the "Confirmed Contradictions" section), fix it — no
   need to re-ask.
3. If the fact falls under "ahead of every written doc" or "on the fence,"
   **ask Dejunai first.** Don't silently resolve it either direction.
4. If you find a discrepancy not covered here, don't guess and don't
   silently overwrite — add it to the list below with the same evidence
   standard used above (cite the specific file/line you checked, not an
   impression).

## Newly Found (append here, do not edit the sections above without re-verifying)

*(empty — nothing added yet)*
