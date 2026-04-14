***

# Assist-Org Prompt Refactor Plan

Goal: reduce prompt sprawl, collapse conflicting rules, and make schema/behavior rules legible for both humans and LLMs, while preserving existing ingest expectations.

***

## Stage 0 — Ground Truth Snapshot

**Objective:** Lock in the current behavior so refactors don’t accidentally change ingest semantics.

- [ ] Save the current prompt as `in-progress/archive/research/assist-org-prompt-legacy-2026-04-11.txt`.
- [ ] Save the three model outputs (Gemini, Grok, ChatGPT) alongside it as a known “failure batch”.
- [ ] Document current ingest assumptions (even if implicit) in a short note:
  - Which fields are truly required for ingest vs. “required in prompt prose”.
  - Which enum sets are canonical in `ws-schema-constants.php`.
  - How ingest treats `""` vs `[]` vs omitted for key fields.

Deliverable: `in-progress/archive/research/assist-org-prompt-legacy-notes.md`.

***

## Stage 1 — Authority Separation

**Objective:** Make it unambiguous which section owns what.

1. **Single schema authority**
   - [ ] Treat `RECORD SCHEMA` as the only canonical shape description in the prompt (keys, nesting, order).
   - [ ] Remove any structural restatement from:
     - REQUIRED KEYS section.
     - PERMISSIBLE TO OMIT list.
     - Inline definitions that repeat the exact same info.

2. **Separate behavior vs. structure**
   - [ ] Keep inline definitions **only** for: one-sentence field meaning, type, and “scalar vs array vs object”.
   - [ ] Move all conditional rules (e.g. “required when X…”, “omit when empty…”) to a separate “Behavioral Rules” section.

3. **Move taxonomy tables out of the blast radius**
   - [ ] Keep full taxonomy tables, but explicitly mark them as **reference only** (“do not invent new slugs; do not use values not listed here”).
   - [ ] Add a short per-field whitelist reminder before tables:
     - e.g., `disclosure_types → ws_disclosure_type`, `employment_sectors → ws_employment_sector`, etc.

Deliverable: updated prompt where sections are clearly labeled:

```text
RECORD SCHEMA  — structure only
INLINE DEFINITIONS  — meaning + type only
BEHAVIORAL RULES  — requiredness, omit vs blank, conditional fields
TAXONOMY TABLES  — reference only
```

***

## Stage 2 — Collapse Redundant Rules

**Objective:** Remove overlapping statements that compete for model attention.

1. **Omission rules**
   - [ ] Replace the long PERMISSIBLE TO OMIT table + scattered “omit if…” bullets with:
     - One global rule: “Omit any optional field when empty or uncertain.”
     - One short list of **exceptions**: fields that must be present even when blank (`required-even-if-empty`).
   - [ ] In BEHAVIORAL RULES, define:
     - `required-always` (must appear with a non-empty value).
     - `required-even-if-empty` (must appear, but `""` or `[]` is allowed).
     - `optional` (omit when you don’t have confident data).

2. **Required keys vs. inline definitions**
   - [ ] Remove the standalone REQUIRED KEYS block.
   - [ ] Instead, add per-field flags in a single “Field Requirements” table, e.g.:

     | Field path | Requirement | Notes |
     |-----------|------------|-------|
     | identity.official_name | required-always | Omit record if missing |
     | identity.source_url | optional | Only when non-official source used |
     | scope_of_service.nationwide_example | required-even-if-empty | Evidence if available; empty string allowed |

   - [ ] Keep that table short and authoritative; do not restate full prose there.

3. **_review_notes rules**
   - [ ] Centralize all `_review_notes` guidance into a single dedicated subsection:
     - What belongs there, what does not.
     - A few curated examples, not a sprawling list.

Deliverable: prompt sections where each rule appears **once** and only once.

***

## Stage 3 — Final “Write Contract” Block

**Objective:** Give the model a small, loud checklist right before generation.

Add a new section immediately before “Produce the complete JSON object now…”:

```text
FINAL WRITE CONTRACT

Before you write the JSON:

1. Use ONLY keys and nesting exactly as shown in RECORD SCHEMA.
2. For each field that takes taxonomy slugs, use ONLY slugs from that field’s table.
   - Never use parent slugs, only children (and has-details where allowed).
3. Requiredness:
   - required-always → field must exist with a non-empty value.
   - required-even-if-empty → field must exist, may be "" or [].
   - optional → omit the field entirely when data is unknown or uncertain.
4. Do NOT invent new slugs. If a concept does not fit any existing slug:
   - Leave the field empty/omitted.
   - Explain the gap in record._review_notes.
5. Do NOT reorder top-level keys or record keys.
6. If you must break any rule:
   - Set integrity.with_errors: true.
   - Explain exactly what and why in integrity.error_details.
```

This should be the last instruction the model sees before output.

Deliverable: updated prompt with a concise FINAL WRITE CONTRACT section.

***

## Stage 4 — Field-Level Tightening

**Objective:** Slim inline definitions and behavioral rules to the minimum that is still persona-safe.

1. **Inline definitions**
   - [ ] Rewrite inline definitions to match schema order (identity → scope_of_service → contact → eligibility → security → review).
   - [ ] Keep each entry to: field path, short meaning, type, and any non-obvious constraint (e.g., “array of ws_languages slugs”).

2. **Behavioral rules**
   - [ ] Move all “required when X” and “omit otherwise” rules here:
     - `protected_class_details` when `protected_class` includes `has-details`.
     - `additional_services` when `services_provided` includes `additional`.
     - `secure_contact_*` when `has_secure_channel` is `yes`.
     - `case_stage_details` when `case_stages` includes `other`.
     - `disclosure_targets_details` when `disclosure_targets` includes `has-details`.
   - [ ] Explicitly state the few exceptions where empty arrays are allowed and required.

3. **Parent slug handling**
   - [ ] Keep the PARENT SLUGS section but trim it to:
     - A clear rule that parent slugs are forbidden values.
     - A short, updated example list of parent slugs.
     - A succinct 3–4 step self-check.

Deliverable: a prompt where:
- Inline definitions = “what is this field”.
- Behavioral rules = “when/how do we populate it”.

***

## Stage 5 — Test Batch and Diff Analysis

**Objective:** Validate that the refactor improves behavior without breaking ingest.

1. **Run a new 3-org batch** with each model:
   - Same three orgs as in the current test (WoA, GAP, NWC) for comparability.
2. **Violation diff**:
   - Compare old vs. new outputs:
     - Parent slug usage.
     - Enum invention.
     - Omit vs. blank vs. present correctness.
     - `_review_notes` quality.

3. **Persona check**
   - Quick hand review: does anything in the new guidance make it easier to miss a critical intake path for Maya / James?

Deliverable: `in-progress/archive/research/assist-org-prompt-refactor-test-notes.md` capturing before/after failure patterns.

***

## Stage 6 — Code-Level Support (Optional but Recommended)

**Objective:** Backstop the prompt with light post-processing or future reconciler rules.

- [ ] Add a simple **parent-slug scrubber** in the reconciler or ingest that:
  - Drops known parent slugs from taxonomy arrays.
  - Logs them to `_review_notes` or integrity for human review.
- [ ] (If needed) Add a **whitelist checker** for each taxonomy field using `ws-schema-constants.php` enums, to catch invented slugs before ingest.
- [ ] Consider a reconciler rule that:
  - Normalizes empty/omitted fields according to the “required-even-if-empty” contract.

Deliverable: small, targeted changes in `ws-schema-constants.php` (if needed) and/or reconciler rules, not in the prompt generator itself.

***
