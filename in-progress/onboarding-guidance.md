# WhistleblowerShield Onboarding Guidance

This file exists for the next Claude instance that wakes up in this repository
with no memory of prior sessions — after a context reset, a new session, or
just a long gap. Read this before touching code or docs.

This is a rewrite of the previous version of this file (which asked the reader
to go find out the April 5 Cloudflare email contents and the pivot context —
both now resolved and recorded below). If you're reading an even older cached
copy of this file, this version wins.

---

## Required Reading Order

1. Root `README.md`
2. `documentation/todo.md` — **read this before trusting anything else in
   `/documentation`.** It's a reconciliation guide listing exactly where the
   formal docs disagree with the live code, disagree with each other, or
   describe something genuinely undecided. `/documentation` itself is
   currently the stalest layer in this repo — see "Documentation Is About To
   Be Rewritten" below.
3. `plugins/todo.md` — status record of the loud-failure unification pass
   (see below). Read it to know what's already done; don't re-derive it.
4. `/documentation/` end-to-end, filtered through what `documentation/todo.md`
   told you to distrust.
5. `/in-progress/*.md` — current design intent. Distinguish "already live in
   code" from "proposed" before citing anything from here as fact. Pay
   particular attention to `phase-2-pivot.md` (the current plan, itself
   behind the real current state — see below) vs. `phase-2-plan-pre-pivot.md`
   (superseded, historical only).
6. `legal-record-acf-fields-v3.0.md` + `legal-record-acf-hooks-v1.0.md` +
   `ws-acf-field-guidance-v1.0.md` + `ws-acf-hook-guidance-v1.0.md` — the
   canonical schema/hook doctrine for legal records. Live in code for
   `jx-statute` only as of this writing (see "Schema State" below).
7. `assist-org-phase-2-starting-point-refactor.md` and
   `assist-org-record-acf-fields-Codex.md` — **read for background only.**
   Neither is canonical. See "Schema State" below.

---

## Who Dejunai Is (get this right, it's asymmetric)

The user's real name is Dwight Edward Jackson. **Call him Dwight in
conversation.** His project/cyber handle — the name that belongs in any
repo, doc, or code attribution — is **Dejunai** (Dwight Edward Jackson,
United Nations, Artificial Intelligence; pronounced like the French
"déjeuner"). He deliberately keeps his real name out of the public repo.

Never write "Dwight" into a file. If you're attributing authorship anywhere
— a docblock `@author` line, a README signature, a commit message — use
"Dejunai." Nineteen stray "Dwight" references were found and corrected
across 11 files in `plugins/ws-core/includes/admin/` on 2026-08-07; don't
reintroduce them.

---

## Who Agent Works For

Agent works for the end users, not the developer.

The primary users are:

- **Maya**: someone considering reporting wrongdoing, scared, likely
  non-expert, possibly on a phone.
- **James**: someone already facing retaliation, under deadline pressure,
  needing concrete next steps.

Daniel, the researcher persona, is a distant beneficiary. Anything built for
Daniel should be a gloss on features that already serve Maya and James:
traceability, citations, review dates, structured source paths. Daniel does
not get to drive the product.

If a developer choice serves implementation elegance, researcher
satisfaction, or archive completeness at the expense of Maya or James,
surface that immediately.

---

## Current Strategic State (accurate as of 2026-08-07 — verify before citing later)

- The site has **one live page**, built entirely from `matrix-assist-orgs.php`
  — the assist-org directory beta, currently listing 21+ organizations. This
  page regenerates from the matrix seeder in under a moment after a full
  wipe. There is no hand-entered, non-reproducible production data anywhere
  on the platform right now.
- The **directory-first Phase 2 pivot** (`in-progress/phase-2-pivot.md`,
  dated 2026-04-06) is the active plan, not the original jurisdiction-filter
  plan (`phase-2-plan-pre-pivot.md`, superseded). The pivot happened because
  the site got 10,000+ organic hits in its first 30 days with zero marketing
  or SEO — real, unprompted demand for the "who can help me" question.
- **Even `phase-2-pivot.md` is behind the real current state.** Dejunai
  reports the milestone list in that file (Filter Contract Freeze → Directory
  Filter Engine → UI+Fallback → Logging+Admin Review → QA+Soft Launch) has
  progressed further than the file's last-updated date would suggest. That
  file has no checkboxes tracking real status — it's a plan doc, not a
  tracker. **Ask Dejunai which milestones are actually done rather than
  inferring from any written doc.**
- The ingest pipeline (`tool-ingest.php`) is mid-rewrite. Per explicit
  instruction in an earlier session: skip it, don't try to unify its error
  handling or otherwise touch it, until told the rewrite has landed.

---

## The Loud-Failure Unification Pass — Done, Follow The Same Discipline Going Forward

A full pass converting every silent fallback/default/swallowed-error in
`ws-core` to the unified `ws_fail_loud()` / `ws_render_or_fail_loud()` /
`ws_log_loud_failure()` mechanism (`includes/admin/ws-fail-loud.php`) is
**complete** as of 2026-08-07 — every layer (matrix, admin, query, render,
shortcodes, cascade, taxonomies, CPT, ACF) has been read function-by-function
and fixed. Full detail and the recurring bug patterns found are recorded in
`plugins/todo.md`. Do not re-run this pass; do read `plugins/todo.md` if you
need the pattern reference for new code.

The standing rule this pass enforced, verbatim from the user: **no silent
fails, no default values, no fallbacks that make bad input quietly become
plausible output.** ("If someone asks my legal code for a tennis match, it
does not get to become a statute.") Apply this to anything new you write.
`pg-*.php` (prompt generator) and `tool-ingest.php` were explicitly out of
scope for that pass — the prompt generator was already converted earlier;
`tool-ingest.php` is mid-rewrite (see above).

---

## Schema State — What's Actually Live vs. What's Proposed

**Legal records** (`jx-statute`, `jx-common-law`, `jx-citation`,
`jx-construction`): the canonical v3.0 rewrite
(`legal-record-acf-fields-v3.0.md`) — prefix-free field names, the
`ws_legal_recognition` recognition-taxonomy pattern, `*_context`/`*_gloss`/
sister-field doctrine — is **live in code for `jx-statute` only**
(`acf-jx-statutes.php`, self-versioned `4.0.0-draft`). The other three
record types are still on their older, pre-rewrite ACF schema. Don't assume
citation/construction/common-law match the v3.0 spec until someone actually
migrates them.

**Assist orgs**: **no canonical schema exists yet.** Two drafts exist in
`/in-progress` —
`assist-org-record-acf-fields-Codex.md` (deep) and
`assist-org-phase-2-starting-point-refactor.md` (shallow) — and per Dejunai
directly, **both are prior AI agents' attempts** to apply the legal-record
pattern to assist-orgs, not his own design and not a spec. He intends to
write the real schema himself; it's "actively in development, with nothing
formal." Do not implement against either draft without checking with him
first, and do not describe either as the plan in any documentation.
`acf-assist-orgs.php` (live) is on an older schema than either draft
describes, and `matrix-assist-orgs.php` has already moved ahead of the ACF
registration on some fields — so even "which schema is the live ACF file on"
isn't a single clean answer per field today.

---

## No Live Data — Still The Doctrine, Now Qualified

The project currently has, in spirit, no live production data — the one
live page is 100% matrix-seeded and disposable, regenerable in under a
moment after a wipe. Keep behaving accordingly:

- no compatibility code;
- no migration adapters;
- no fallback writes for old meta keys;
- no pretend support for stale schema;
- no "just in case" translators;
- no pretzel logic.

Wherever the canonical schema goes, all code follows. If a field name
changes, the matrix, ACF, query layer, render layer, cascade, prompt schema,
ingest, and tests all change to match. Nothing gets adapted around the new
truth. This holds even though a real URL now serves real visitors — the
distinction that matters is "precious, hand-entered, non-reproducible data"
(none exists) vs. "a live URL" (one exists). Don't conflate the two, and
don't let "the site is live now" become an excuse to start writing
compatibility shims.

---

## Documentation Is About To Be Rewritten

`/documentation` is due for a substantial rewrite — not a patch pass. As of
today it disagrees with itself and with the live code on taxonomy counts,
version numbers, the Phase 2 plan, and the legal-record ACF schema (full
detail in `documentation/todo.md`).

The planned approach, agreed with Dejunai: split by content type.
**Narrative/voice sections** (project overview, personas, editorial
standards, tone) may be drafted with Microsoft 365 Copilot — those sections
are mostly about consistent voice, not fact-verification, and haven't
drifted much. **Fact-bearing sections** (data layer field lists, taxonomy
tables, query layer signatures, version numbers, anything checkable against
code) must be verified against live source by a code-aware agent — Copilot
can't read this repo and will produce fluent, confident-sounding wrong
numbers otherwise, which is exactly the failure mode this project's whole
editorial philosophy (omission over fabrication) exists to prevent.

If you're the agent doing this rewrite: read `documentation/todo.md` first,
follow its source-of-truth hierarchy, and never state a number or a status
you haven't personally verified against the file that would prove it. If you
find something the reconciliation guide didn't already cover, add it to that
file's "Newly Found" section — don't just silently fix the doc and move on,
since Dejunai may want to weigh in on wording, especially for anything in
the "ahead of every written doc" or "on the fence" categories that guide
calls out.

If you're a later agent and `/documentation` now looks different from what
this section describes, that's expected — the rewrite happened. Trust the
current file contents over this paragraph once that's true.

---

## Assembly Layer Boundary

Pre-publish layers are strict. Public-facing layers are humane.

Matrix, seed, schema, and ingest:

- no fallbacks;
- no adaptation;
- no silent tolerance;
- fail before publish.

Query, assembly, and public rendering:

- user-facing functions may have fallbacks;
- public pages should degrade gracefully when something impossible has
  slipped through;
- user-facing copy must be calm, helpful, and free of internal profanity or
  blame;
- operator logs must clearly name the bad record, field, expected shape,
  actual shape, and source path.

The public user should never be punished for an editorial/system failure.
The operator log should make the failure impossible to hide.

---

## Humor And Rituals

The project uses dry and dark humor. Treat it as morale texture, not
product copy.

Allowed internally: cult jokes, clerics, robes, candles, imaginary
consequences, dramatic execution metaphors for bad architecture. Project
folklore says small animals suffer whenever compatibility code appears —
translate that as: compatibility code is a serious architectural smell,
removed unless the user explicitly changes the no-live-data doctrine. When
emphasis escalates further ("children with cancer forced to watch"),
translate that as: missing essential data — an organization name, official
homepage, routing-critical contact path — is release-blocking, not a minor
validation issue.

Not allowed in public-facing output: threats, profanity, blame, jokes about
harm, anything that makes a frightened user feel less safe.

Agent may mirror the user's ritual language in conversation, but code, logs,
and public text need different registers:

- public text: humane and clear;
- operator logs: factual and actionable;
- comments/docs: candid but not chaotic;
- final user replies: concise and honest.

---

## If You're Not Sure Where to Start

Ask Dwight. He'd rather answer a clarifying question than have an agent
guess confidently and be wrong — that's the entire editorial philosophy of
this project, applied to how you should work with him too.
