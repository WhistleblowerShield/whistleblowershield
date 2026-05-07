# Deferred Until Sanity Returns

Triage list for schema issues found during the v3.0 split. This file is a holding pen, not doctrine.

## Already Milled

- Hook-guide loophole removed: non-recognition fields cannot anchor triggered clusters.
- Forced promotion mistake removed: non-doctrinal state must not be pushed into `legal_recognitions` merely to
  satisfy cluster mechanics.
- Stale insertion anchor fixed: `causal_nexus_statutory_text` → `causation_standard_statutory_text`.

## Mechanical Fixes

### Legal Recognition Seed Drift

These slugs are referenced as `legal_recognitions` triggers but are not present in the `ws_legal_recognition`
seed list:

- `manner-of-opposition-standard`
- `facially-retaliatory-policy`
- `heightened-pleading-standard`
- `employment-classification-test`
- `causation-standard-recognized`

Likely fix: add seed terms to `plugins/ws-core/includes/register-taxonomies.php`, then verify labels/status words.

### `remedy_limits` Repeater Shape

Current issue: `remedy_caps` is modeled as a sister to `remedy_limits`, which makes `remedy_limits` look like a
fake root context/anchor.

Intended doctrine:

- `remedies` may include a value such as `has-limits`.
- That value may reveal one parent-level repeater field: `remedy_limits`.
- A triggered repeater counts as one parent-level field for non-recognition trigger doctrine.
- No root/global `*_context` is needed.
- Row-level `limit_context` carries per-row nuance.

Likely edits:

- rename/reshape `remedy_caps` to `remedy_limits`
- rename `remedy_cap` to `remedy_limit`
- rename `cap_amount` to `limit_amount`
- rename `cap_context` to `limit_context`
- update hook reference `remedy_caps.remedy_cap` to `remedy_limits.remedy_limit`
- document the "one triggered repeater counts as one field" rule in guidance

## Doctrine Decisions

### Broad `*_gloss is non-empty`

Guidance says all `*_gloss` conditionals must declare trigger field and trigger value. Current field spec uses
whole-field non-empty gloss triggers:

- `sol_trigger_gloss` — conditional on `sol_triggers` is non-empty
- `adverse_action_scope_gloss` — conditional on `adverse_action_scope` is non-empty
- `sovereign_immunity_limit_gloss` — conditional on `sovereign_immunity_limits` is non-empty

Decision: formally approve whole-field glosses triggered by non-empty fields, or convert these to value-specific
glosses.

### Auto-Set Hook Exception

General hook doctrine favors validation over cleanup. Current spec allows:

- `has_fee_shifting_phases` auto-set true when `fee_shifting_standard` is `none-american-rule`

Decision: document as an approved derived/guard-field exception, or replace with validation-only behavior.

### `[P+]` Meaning Drift

Legend says `[P+]` is a paired slug relationship. Current map uses:

- `causation-standard-recognized` paired with `causation_standard is non-empty`

Decision: expand `[P+]` to allow pairing with non-empty taxonomy fields, or create a different marker/form for
field-presence pairs.

### Undefined Pseudo-Condition

Current specs use:

- `any retaliation-slug in adverse_actions`

Accepted notation supports `any child-slug of parent-slug in taxonomy_field`, but does not define free-floating
pseudo-classes such as `retaliation-slug`.

Decision: define `retaliation-slug` as an accepted pseudo-class, or replace it with a real parent slug.

### `review_standard` Double Role

`review_standard` appears in two record-type roles:

- substantive records: sister to `review_standard_context`
- precedent records: standalone field

Decision: explicitly approve same meta key with record-type-specific behavior, or split names.

## Needs Field Lists Before Hooks Can Exist

### Vague Special-Case Exclusions

The hook spec contains exclusions that are conceptually right but not enforceable without exact fields:

- `statutory-preclusion` excludes "ordinary process and remedy pathway fields"
- `no-retaliatory-evidence` excludes "evidence-use fields"

Decision: list exact fields/clusters excluded, or remove the special-case language until enforceable.

## Non-Recognition Bool Pressure

Standing rule reminder:

- Do not promote non-doctrinal booleans or record states into `legal_recognitions` merely because they are
  inconvenient.
- If a non-recognition trigger starts trying to reveal multiple fields, reduce to one essential target field first.
- Fold detail into that field when the field is a repeater or structured object.
- Use two fields only when irreducible, and annotate explicit approval inline.
- Keep approved exceptions outside `legal_recognitions` when the state still does not belong in recognition
  taxonomy.

Example: `has_negative_treatment_class` is a valid non-recognition trigger because negative treatment is a record
state/classification, not a legal doctrine recognized by the record.
