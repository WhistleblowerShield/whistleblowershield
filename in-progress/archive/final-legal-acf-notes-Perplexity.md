This is a substantial, mature schema. The naming conventions are tight, the sentinel pattern is clean, the cross-tab conditional for `mixed-motive` is correctly handled, and the `ws_legal_recognition` presence/absence table is an elegant solution to what used to be a field explosion. The fact that I have to work hard to find gaps is a good sign.

Here's what I found — genuinely missing nuance, nothing fabricated to fill space. 

***

## Remaining Legal Gaps

### 1. The "Contributing Factor" Split Is Correct But Incomplete

The taxonomy note correctly separates `ws_employee_standard` (evidentiary weight) from `ws_causation_standard` (causal logic) and acknowledges "contributing factor" appears in both. Good. But there's a third framing courts use that neither table captures cleanly: **"but-for contributing factor"** — the standard that emerges when a statute uses contributing-factor language but courts gloss it with a but-for backstop. The Eighth Circuit's treatment of AIR21, for example, creates a hybrid that doesn't map cleanly to either `contributing-factor` in causation or `but-for`. 

**Suggestion:** Add a `has_causation_standard_details` bool + `causation_standard_details` to the Burden of Proof tab when `causation_standards` includes any term, so hybrid standards can be described. Currently `causation_standard_details` exists as a standalone field but without a bool gate — fine if it follows the `*_context` pattern (conditional on non-empty). Confirm the conditional behavior is intentional per your naming rules.

***

### 2. "Scope of Protected Activity" — Internal-Only Disclosures Are Not Separately Flagged

`disclosure_channel_scope` captures `approved-channel-only` | `any-channel` | `has-details` — this is the *what channels are valid* question. But there's a distinct prior question: **whether internal reporting alone is protected at all, or whether external/regulatory disclosure is required to trigger protection**. Post-*Digital Realty Trust v. Somers* (2018), internal-only reporters under Dodd-Frank lost federal protection entirely. That's not a channel restriction — it's a threshold question about whether the activity qualifies at all.  

`disclosure_targets` taxonomy covers *who* the disclosure went to. But the schema has no boolean or `legal_recognitions` slug that flags "internal-only disclosure is sufficient for protection." A researcher looking at a state statute that *does* protect internal reporters needs to know that affirmatively.

**Suggestion:** Add `internal-only-sufficient` to `ws_legal_recognition` (no companion needed — presence signals yes, absence signals absent or ambiguous). Alternatively fold it into `disclosure_channel_scope` as a fourth option: `internal-only-sufficient`.

***

### 3. "Motivating Factor" vs. "Substantial Motivating Factor" — The California Split

`burden_shifting_framework` has `motivating-factor` as a term. California *Harris v. City of Santa Monica* (2013) introduced **"substantial motivating factor"** as a distinct and higher standard than plain motivating factor. Several other states use similar language. These are not interchangeable — a researcher in California working under FEHA needs to know which applies, and the current single term collapses a real distinction. 

**Suggestion:** Add `substantial-motivating-factor` as a distinct term in `ws_causation_standard` taxonomy (not in `burden_shifting_framework`, since it's a causal standard, not a framework). The framework is still `motivating-factor`; the causal link standard is `substantial-motivating-factor`.

***

### 4. Preliminary Reinstatement Is a `legal_recognitions` Term — But Has No Context Field

`preliminary-reinstatement` appears in the slug-to-companion map as "(no companion needed)." That's defensible for a simple boolean, but preliminary reinstatement under OSHA and several state analogs has genuinely material nuance: some statutes make it mandatory on a prima facie showing; others make it discretionary; some require a bond; some apply only during the administrative phase. A researcher comparing AIR21 vs. a state analog can't see any of that without a context field. 

**Suggestion:** Promote `preliminary-reinstatement` from no-companion to: `preliminary-reinstatement` → `preliminary_reinstatement_context`. Low overhead, high legal value.

***

### 5. Sovereign Immunity — The State-vs.-State-Entity Distinction Is Missing

`sovereign_immunity_limits` covers the waiver question well. But it doesn't capture a distinct dimension: **whether immunity attaches to the state itself vs. its instrumentalities or political subdivisions**. Under *Alden v. Maine* and state sovereign immunity doctrine, a state may waive immunity for suits against state agencies but retain it for suits against the state itself in federal court. A WB claim against a state university (instrumentality) may be viable while the same claim against the state AG's office is not. 

**Suggestion:** Add `sovereign_immunity_scope` as a sister field to `sovereign_immunity_details`: single-select: `state-only` | `instrumentalities-included` | `political-subdivisions-included` | `all` | `see-details`.

***

### 6. `election_of_remedies_rules` — "Administrative-Bars-Civil" Doesn't Capture Timing

`election_of_remedies_rules` includes `administrative-bars-civil` which is correct as a term. But the *timing* of the election is often the determinative legal question — specifically, whether the election is irrevocable at the moment of filing, or only after a final administrative determination. NLRB preemption cases and several state statutes turn on exactly this. A term alone doesn't capture whether the election crystallizes at filing or at decision. 

**Suggestion:** Add `election_of_remedies_context` is already in the schema — good. Confirm the field instruction language directs researchers to capture election timing specifically, not just which remedy was elected.

***

### 7. `ws_protected_action` Taxonomy — "Opposition Clause" vs. "Participation Clause" Still Absent

From the prior review: the seed terms still don't include the opposition/participation distinction. Looking at the updated `register-taxonomies.php`, `ws_protected_action` is registered as hierarchical but the seeds remain flat.  The opposition clause (opposing an unlawful practice) and participation clause (participating in a proceeding) have different good-faith and causation requirements in Title VII analogs and many state statutes. A researcher can't distinguish them with the current flat terms. 

**Suggestion:** Add `opposition-clause` and `participation-clause` as parent terms, with existing terms (`attempted-reporting`, `testifying`, etc.) nested under the appropriate parent. This is the one structural gap that was flagged in the first pass and still isn't closed.

***

### 8. No Field For "Good Faith Report to Whom" — Scope of Protected Recipients

`protected_action_standard` has `good_faith` as a value, and `protected_action_source` covers the legal basis. But neither field captures a distinct legal dimension: **whether good-faith protection extends to reports made to non-designated recipients** (e.g., a supervisor rather than compliance, or a colleague rather than an agency). Some statutes are strict about who the disclosure must go to for good-faith protection to apply; others are silent. This is distinct from `disclosure_targets` (which covers eligibility) — it's about whether the *good-faith standard itself* has a recipient requirement. 

**Suggestion:** This is a genuinely edge-case field. It could be captured in `protected_action_context` as a narrative note rather than a structured field — the context field is already conditional on `protected_action_standard` non-empty. Add a field instruction directing researchers to note recipient restrictions when `good_faith` is selected. No new field needed if the instruction is explicit.

***

## Minor / Schema Housekeeping

| Item | Issue | Fix |
|---|---|---|
| `causation_standard_details` | Present as standalone — confirm conditional is on `causation_standards` non-empty, not a `has_*` gate | Clarify in field description |
| `criminal_sanction` | Single-select only — a statute can impose both misdemeanor *and* felony sanctions depending on the conduct | Change to multi-select |
| `protected_action_context` is listed twice in Classification Tab | Appears as sister field annotation on both `protected_action_standard` and `protected_action_source`, then again standalone | Confirm it's one field, two references in the draft |
| `retro_context` + `retro_date` — companion order | `retro_date` is listed before `retro_context` in the Effective Date Tab but `retro_context` is the trigger for `retro_date` per the slug-to-companion map | Swap order or clarify in implementation |

The `criminal_sanction` multi-select issue is the only one with real legal consequence — a statute that imposes misdemeanor sanctions for retaliation and felony sanctions for obstruction needs both terms, and the current single-select silently drops one. 

---


# Legal Record ACF — Nuance Gap Implementation Proposal
# Version: 3.16.0-proposal
# Source: Perplexity nuance review, 2026-04-26
# Applies to: legal-record-acf-fields.md + register-taxonomies.php

---

## Naming Convention Reminder

Per established schema rules:
- `*_context` — conditional on parent field non-empty; nullable free text
- `*_details` — conditional on `has_*` bool true or `has-details` sentinel
- `has_*` — boolean gate
- Sentinel `has-details` in select/taxonomy triggers `*_details` companion

---

## 1. Causation Standard — Hybrid "But-For Contributing Factor" Coverage

**Gap:** `ws_causation_standard` has no term for hybrid standards where courts
apply a contributing-factor label but impose a but-for backstop (e.g., some
AIR21 circuit splits). `causation_standard_details` exists as a standalone field
but its conditional behavior is unspecified in the draft.

**Changes:**

### register-taxonomies.php — ws_causation_standard seeds
Add seed term:
```
slug: 'contributing-factor-but-for-backstop'
name: 'Contributing Factor (But-For Backstop)'
description: 'Contributing factor language with judicial but-for limitation applied'
```

### legal-record-acf-fields.md — Burden Of Proof Tab
Clarify `causation_standard_details` conditional:

```
- `causation_standard_details`  — (conditional on `causation_standards` non-empty;
                                   freetext; document hybrid or judicially modified
                                   causation standards, circuit splits, and backstop
                                   limitations not captured by taxonomy terms alone)
```

**No new field required.** Conditional clarification and seed term only.

---

## 2. Internal-Only Disclosure Sufficiency Flag

**Gap:** No structured signal for whether internal-only disclosure qualifies for
protection without external/regulatory report. Post-*Digital Realty Trust v.
Somers* (2018), this is a threshold eligibility question, not a channel
restriction. `disclosure_channel_scope` covers valid channels; this covers
whether the activity qualifies at all.

**Changes:**

### register-taxonomies.php — ws_legal_recognition seeds
Add seed term:
```
slug: 'internal-only-sufficient'
name: 'Internal-Only Disclosure Sufficient'
description: 'Internal reporting alone satisfies protected activity threshold;
              external or regulatory disclosure not required for protection to attach'
```

### legal-record-acf-fields.md — Slug-to-Companion Map
Add entry:
```
'internal-only-sufficient'      (no companion needed)
```

### legal-record-acf-fields.md — Classification Tab
No new field. Term presence in `legal_recognitions` is the signal.
Absence = absent or ambiguous; document in `protected_action_context` if needed.

---

## 3. Causation Standard — "Substantial Motivating Factor"

**Gap:** California *Harris v. City of Santa Monica* (2013) and several state
analogs use "substantial motivating factor" — a distinct and higher standard
than plain motivating factor. Currently collapsed into `motivating-factor`.

**Changes:**

### register-taxonomies.php — ws_causation_standard seeds
Add seed term:
```
slug: 'substantial-motivating-factor'
name: 'Substantial Motivating Factor'
description: 'Motivating factor standard with substantiality threshold; see
              California FEHA and Harris v. City of Santa Monica (2013)'
```

**Note:** This is a `ws_causation_standard` term, not a `burden_shifting_framework`
value. The framework remains `motivating-factor`; the causal link standard is
`substantial-motivating-factor`. Both may be set simultaneously on a record.

---

## 4. Preliminary Reinstatement — Promote to Context-Bearing Slug

**Gap:** `preliminary-reinstatement` in `ws_legal_recognition` is currently
no-companion. Material nuance exists: mandatory vs. discretionary; bond
requirement; administrative-phase-only vs. full pendency. A context field
allows researchers to capture this without a field proliferation.

**Changes:**

### legal-record-acf-fields.md — Slug-to-Companion Map
Update entry:
```
// Before
'preliminary-reinstatement'      (no companion needed)

// After
'preliminary-reinstatement'     → 'preliminary_reinstatement_context'
```

### legal-record-acf-fields.md — Enforcement Tab
Add after `anticipatory_retaliation_context`:
```
- `preliminary_reinstatement_context`  — (conditional on `preliminary-reinstatement`
                                          in `legal_recognitions`; document mandatory
                                          vs. discretionary standard, bond requirement,
                                          and whether reinstatement applies during
                                          administrative phase only or full pendency)
```

---

## 5. Sovereign Immunity — State vs. Instrumentality Scope

**Gap:** `sovereign_immunity_limits` covers the waiver question. No field
captures whether immunity attaches to the state itself vs. its instrumentalities
or political subdivisions — a distinct dimension under *Alden v. Maine* and
state sovereign immunity doctrine.

**Changes:**

### legal-record-acf-fields.md — Enforcement Tab
Add `sovereign_immunity_scope` as sister field to `sovereign_immunity_details`,
immediately before it:

```
- `sovereign_immunity_scope`    — (sister field to `sovereign_immunity_details`;
                                   single-select: `state-only`|`instrumentalities-included`|
                                   `political-subdivisions-included`|`all`|`see-details`)
- `sovereign_immunity_details`  — (conditional on `sovereign_immunity_limits` includes
                                   `has-details` or `sovereign_immunity_scope` is
                                   `see-details`; no change to existing conditional)
```

**Hook note:** Existing hook that catches contradictory `sovereign_immunity_limits`
terms should be extended to validate `sovereign_immunity_scope` is not set to `all`
while `sovereign_immunity_limits` includes `not-waived`.

---

## 6. Election of Remedies — Field Instruction Update Only

**Gap:** `election_of_remedies_context` exists but field instructions do not
direct researchers to capture election timing (irrevocable at filing vs. after
final determination). This is often the determinative legal question.

**Changes:**

### legal-record-acf-fields.md — Enforcement Tab
Update field description for `election_of_remedies_context`:

```
- `election_of_remedies_context`   — (conditional on `election_of_remedies_rules`
                                      non-empty; document: (1) whether election is
                                      irrevocable at moment of filing or only after
                                      final administrative determination; (2) whether
                                      pending agency action tolls the civil SOL;
                                      (3) any statutory exceptions to election rules)
```

**No new field required.** Instruction update only.

---

## 7. ws_protected_action — Opposition / Participation Hierarchy

**Gap:** `ws_protected_action` is registered as hierarchical but seeds are flat.
Opposition clause (opposing an unlawful practice) and participation clause
(participating in a proceeding) have different good-faith and causation
requirements in Title VII analogs and many state statutes. Previously flagged;
still unimplemented.

**Changes:**

### register-taxonomies.php — ws_protected_action seeds
Restructure seeds to add parent terms and re-parent existing terms:

```
// New parent terms
slug: 'opposition-clause'
name: 'Opposition Clause'
description: 'Opposing, complaining about, or resisting an unlawful practice;
              typically requires good-faith reasonable belief; see Title VII
              § 704(a) opposition clause and state analogs'
parent: 0

slug: 'participation-clause'
name: 'Participation Clause'
description: 'Participating in an investigation, proceeding, or hearing;
              typically broader protection than opposition clause; good-faith
              requirement may not apply; see Title VII § 704(a) participation
              clause and state analogs'
parent: 0

// Re-parent existing terms
'attempted-reporting'      → parent: 'opposition-clause'
'refusal-to-participate'   → parent: 'opposition-clause'
'internal-objection'       → parent: 'opposition-clause'  // add if not present
'testifying'               → parent: 'participation-clause'
'filing-complaint'         → parent: 'participation-clause'  // add if not present
'assisting-complainant'    → parent: 'participation-clause'  // add if not present
'participation-support'    → parent: 'participation-clause'
```

**Note:** Add `internal-objection`, `filing-complaint`, and `assisting-complainant`
as new seed terms in the same pass — these are recognized protected activities
missing from the current seed set and align with the previously flagged gap
from the first nuance review.

---

## 8. Good-Faith Standard — Recipient Restriction Field Instruction

**Gap:** No structured field captures whether the good-faith standard has a
recipient restriction (report must go to a designated recipient for protection
to attach). Edge-case but outcome-determinative in strict-channel statutes.

**Changes:**

### legal-record-acf-fields.md — Classification Tab
Update field instruction for `protected_action_context`:

```
- `protected_action_context`   — (conditional on `protected_actions` non-empty;
                                   document: (1) any recipient restriction when
                                   `protected_action_standard` is `good_faith`
                                   (i.e., whether report must go to a designated
                                   recipient for protection to attach); (2) any
                                   activity-specific limitations not captured by
                                   taxonomy terms; (3) interaction between opposition
                                   and participation clause standards where both apply)
```

**No new field required.** Instruction update only.

---

## 9. criminal_sanction — Change to Multi-Select

**Gap:** Current single-select silently drops one sanction when a statute
imposes misdemeanor sanctions for retaliation and felony sanctions for
obstruction simultaneously. Only legal-consequence bug in the review.

**Changes:**

### legal-record-acf-fields.md — Enforcement Tab
Update field definition:

```
// Before
- `criminal_sanction`           — (single-select: `misdemeanor`|`felony`)

// After
- `criminal_sanctions`          — (multi-select: `misdemeanor`|`felony`;
                                   NOTE: renamed to plural per naming convention
                                   for multi-value datapoints)
```

**Hook note:** Any existing hook or query referencing `criminal_sanction`
(singular) must be updated to `criminal_sanctions`.

---

## 10. Housekeeping Fixes

### 10a. protected_action_context — Duplicate Reference Clarification

`protected_action_context` appears as a sister field annotation on both
`protected_action_standard` and `protected_action_source`, then as a standalone
field. This is one field with multiple trigger conditions, not multiple fields.

**Change:** Add clarifying comment in Classification Tab field block:

```
// NOTE: `protected_action_context` is a single field with multiple conditional
// triggers: it surfaces when `protected_actions` is non-empty (primary trigger),
// and its instructions encompass elaboration for both `protected_action_standard`
// and `protected_action_source`. Do not register duplicate fields.
```

### 10b. retro_date / retro_context — Order Correction

`retro_date` is listed before `retro_context` in the Effective Date Tab, but
`retro_context` is the slug-triggered field and `retro_date` is its sister.
Canonical order per naming convention: trigger-companion before sister fields.

**Change:** Reorder Effective Date Tab entries:

```
// Before
- `retro_date`
- `retro_context`

// After
- `retro_context`              — (conditional on `retroactive-date` in `legal_recognitions`)
- `retro_date`                 — (sister field to `retro_context`)
```

---

## Summary Table

| # | Change Type | Location | New Fields | New Seeds | Instruction Only |
|---|---|---|---|---|---|
| 1 | Seed + clarify conditional | `ws_causation_standard` / BOP tab | — | 1 | ✓ |
| 2 | Seed + slug map | `ws_legal_recognition` / Classification tab | — | 1 | — |
| 3 | Seed | `ws_causation_standard` | — | 1 | — |
| 4 | Promote slug + new field | `ws_legal_recognition` / Enforcement tab | 1 | — | — |
| 5 | New sister field | Enforcement tab | 1 | — | — |
| 6 | Instruction update | Enforcement tab | — | — | ✓ |
| 7 | Restructure seeds + new terms | `ws_protected_action` | — | 5 | — |
| 8 | Instruction update | Classification tab | — | — | ✓ |
| 9 | Field type + rename | Enforcement tab | — | — | — |
| 10a | Comment / clarification | Classification tab | — | — | ✓ |
| 10b | Field order correction | Effective Date tab | — | — | ✓ |

**Net additions:** 2 new ACF fields, 8 new taxonomy seed terms, 4 instruction
updates, 2 structural clarifications. No existing fields removed. No existing
taxonomy terms modified.

---

## Version Bump Recommendation

`legal-record-acf-fields.md` → `3.16.0`  
`register-taxonomies.php` → bump seeder gate versions:
- `ws_causation_standard` seeder gate → `'1.1.0'`
- `ws_legal_recognition` seeder gate → `'1.1.0'`
- `ws_protected_action` seeder gate → `'1.1.0'` (hierarchy restructure requires re-seed)

---

*End of proposal.*
