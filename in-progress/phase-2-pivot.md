# Phase 2 Pivot - Directory-First Execution Plan

**Last updated:** 2026-04-06
**Status:** Active
**Priority:** Highest (replaces prior sequencing assumptions)

---

## Objective

Ship a useful, low-risk public experience sooner by leading with a dynamic
assist-organization directory and a situation-based filter cascade.

This pivot prioritizes helping users find real support now, while legal
content coverage continues to mature in parallel.

---

## Product Strategy

### Why this pivot

- Demand signal exists (non-trivial traffic despite under-construction gate).
- Directory guidance carries lower legal-construction risk than statute-first outputs.
- Filter telemetry can directly guide future data enrichment and taxonomy tuning.

### Outcome target

A user can answer plain-language questions and receive a narrowed, relevant
list of assist organizations with graceful fallback when matches are sparse.

---

## Scope

### In scope

- Situation-based filter cascade for assist-organization discovery.
- Directory-specific filtered results renderer.
- Taxonomy alignment and validation for organization records.
- Logging for filter usage and zero-result/fallback patterns.
- Admin review loop for taxonomy corrections.

### Out of scope (for this pivot phase)

- Full jurisdiction-page filtered legal cascade.
- New legal construction layers beyond existing curated paths.
- Launch-readiness claims tied to 57-jurisdiction statute completeness.

---

## Success Criteria

1. A first-time user can complete the filter flow in under 60 seconds.
2. At least one useful fallback path is shown for every zero-result query.
3. Directory filter logs identify top unmatched combinations each week.
4. Taxonomy corrections can be applied without code changes.
5. No taxonomy internals are exposed in user-facing language.

---

## Core UX Model

### Question sequence

1. Situation stage (`ws_case_stage`):
- Thinking about reporting
- Already reported and experienced retaliation
- Doing research

2. Concern/event axis:
- Pre-disclosure branch maps to `ws_protected_disclosure`
- Post-retaliation branch maps to `ws_adverse_action_type` (+ optional `ws_protected_disclosure`)

3. Employment context (`ws_employment_sector`):
- Federal / state-local / private / nonprofit / military-defense / not sure

4. Optional target context (`ws_disclosure_targets`, simplified):
- Internal / external / both / not sure

### Design rules

- "Not sure" always broadens; never dead-ends.
- Questions are user-language; taxonomy is implementation detail.
- Ordering hints can be used, but filtering rules must stay deterministic.

---

## Technical Architecture

### New/updated components

1. `includes/render/ws-filter-config.php`
- Canonical GET param names
- Allowed values per param
- Thin-result thresholds
- Neutral/default behavior

2. `ws_resolve_filter_context()` (hub)
- Parse/validate/sanitize GET params
- Convert values -> taxonomy term IDs
- Return normalized filter context object
- Emit one log record per request

3. `ws_render_directory_taxonomy_guide()`
- Query scope: `ws-assist-org` only
- Apply filter context via tax_query
- Render result cards + no-result fallback

4. Directory filter panel renderer
- Stateless GET form
- Server-rendered baseline
- Optional progressive enhancement via JS

5. Logging + admin surface
- Rolling log (bounded)
- Weekly summaries: top combos, top no-match combos, top broadened paths

### Non-goals in this implementation

- No branching monolith renderer for jurisdiction + directory.
- No mixed ownership of filter logic outside the hub.

---

## Data & Taxonomy Readiness

### Minimum readiness checks before enabling public filter flow

1. `ws-assist-org` records have required core fields populated.
2. Core taxonomy assignments are present:
- `ws_case_stage`
- `ws_protected_disclosure` (or `ws_adverse_action_type` where applicable)
- `ws_employment_sector`
- Optional: `ws_disclosure_target`

3. Sentinel/overflow handling is consistent (`additional`, `other`, `has-details` where used).

4. Taxonomy audit tool returns no structural mismatches for directory-relevant sets.

---

## Fallback Behavior

### Zero-result contract

When no organizations match all selected filters:

1. Show supportive, confidence-preserving message (avoid blunt "no match" phrasing).
2. Suggest one-filter relaxation options.
3. Offer direct link to full directory.
4. Preserve selected answers so users can adjust quickly.

Recommended default copy:

"WhistleblowerShield is dedicated to helping you find the right assistance organization.
Based on what you shared, your situation appears broader than our current filter set.
The organizations below are trusted, reputable, and equipped to help across a wide range of needs."

Tone variants (for future A/B or context-based use):

1. Concise:
"We could not find a close match for all selected details.
The organizations below are trusted and can help across a broad range of whistleblower situations."

2. Formal:
"WhistleblowerShield is committed to helping you identify an appropriate assistance organization.
Based on the criteria provided, your matter appears broader than our current filter model.
The organizations below are established, reputable, and capable of supporting a wide spectrum of needs."

3. Compassionate:
"You are not in the wrong place.
Your situation appears broader than our current filters, so we are showing trusted organizations known to help with many kinds of whistleblower concerns.
You can also broaden one filter below to see additional options."

### Thin-result contract

When results are below threshold:

1. Show thin-result notice.
2. Offer broaden controls inline.
3. Keep matched results visible (never hide valid matches).

---

## Logging Spec

### Log payload (no user identity)

- timestamp (UTC)
- request context (`directory_request = true`)
- selected params (normalized)
- resolved taxonomy term IDs/slugs
- result count
- fallback triggered (bool)
- broaden suggestion generated (bool)

### Retention

- Rolling append capped by entry count and file size.
- Keep latest summary snapshot for admin view.

---

## Implementation Milestones

### Milestone 1 - Filter Contract Freeze

Deliverables:
- `ws-filter-config.php`
- Param schema + sanitizer map
- Unit-level validation helpers

Acceptance:
- Unknown params ignored safely
- Invalid values fail closed (broaden, do not crash)

### Milestone 2 - Directory Filter Engine

Deliverables:
- `ws_resolve_filter_context()`
- Directory tax_query builder
- Deterministic result ordering

Acceptance:
- Same input always yields same query
- No direct taxonomy internals exposed in UI

### Milestone 3 - UI + Fallback

Deliverables:
- Server-rendered filter form
- Results panel + empty/thin states
- Clear filters reset path

Acceptance:
- All "not sure" routes produce usable results or actionable fallback

### Milestone 4 - Logging + Admin Review

Deliverables:
- Log writer
- Admin summary panel/tab
- Top mismatch combinations list

Acceptance:
- Team can identify top taxonomy gaps from logs in under 5 minutes

### Milestone 5 - QA + Soft Launch

Deliverables:
- Golden test URL set
- Manual QA checklist
- Soft-launch toggle

Acceptance:
- 0 blocker defects on golden scenarios
- Fallback behavior verified across edge cases

---

## QA Matrix (Initial)

1. Pre-disclosure + private sector + financial concern
2. Pre-disclosure + not sure + not sure
3. Retaliation + fired + state-local
4. Retaliation + harassment + not sure concern
5. Research path neutral directory browse
6. Zero-result forced combo
7. Thin-result combo
8. Clear-filters return to neutral state

---

## Risks & Mitigations

### Risk: taxonomy misclassification causes irrelevant results
Mitigation:
- Weekly audit from logs
- Fast admin correction loop
- Broadening fallback always available

### Risk: overfitting filters to current sparse data
Mitigation:
- Keep filter contract stable, threshold config adjustable
- Prefer broad defaults over strict gating

### Risk: feature creep back to legal-cascade scope
Mitigation:
- Explicit out-of-scope section enforced per milestone
- Directory-only acceptance gate for pivot completion

---

## Operating Cadence

- Build cycle: 1 milestone at a time, each independently demoable.
- Weekly review: filter logs + taxonomy correction pass.
- Quality rule: no acceleration that weakens fallback clarity or result relevance.

---

## Definition of Pivot Complete

The pivot is complete when:

1. Directory filter cascade is live and stable.
2. Users can reliably find relevant organizations from plain-language inputs.
3. Fallback paths are honest, helpful, and never dead-end.
4. Taxonomy tuning loop is operational via logs + admin corrections.
5. Legal-content expansion can continue independently without blocking directory value.
