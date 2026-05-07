# WS ACF Field Guidance (v1.0)

**Purpose:** Naming conventions, companion-field doctrine, sister-field rules, sentinels, conditional annotation,
and inline description discipline for ACF field sets across the WS codebase. Domain-specific specs (legal records,
assist-orgs, agencies, etc.) inherit from this document and add their own field declarations on top.

**Scope:** This document defines the *rules*. It does not declare any specific fields. Examples **might** reference
fields from existing implementations (most often legal records) for illustration. The rule is the contract; the
example is the vehicle.

---

## Naming Rules

### Casing

Casing is strict. ACF field `name` values (meta keys) use `snake_case`. Select choice values and taxonomy term
slugs use `kebab-case`.

### Umbrella Choice Values

Umbrella values in multi-value fields end with `-only` when the value represents a blanket selection that excludes
granular sibling values in the same field. Do not use `-only` with values that don't carry blanket semantics; find
alternatives.

Hooks targeting an umbrella `-only` value must flag granular siblings when present. Sentinels such as `has-details`
and `see-context` are not granular values and **are permitted to** coexist with an umbrella value.

Examples: `all-sectors-only`, `all-employees-only`.

### CPT Prefix and Infix

Canonical field names in domain specs are prefix-free. CPT-specific prefixes are applied during ACF rewrite, not
in the spec source. The conventions below define how those prefixes are constructed.

Fixed parts. `ws_*` identifies the codebase. Each domain spec defines its own infix (e.g., legal records use
`jx_*` to identify children of `jurisdiction` records and CPT slot values such as `statute`, `comlaw`, `citation`,
`construction`).

Registered ACF field `name` values follow the pattern `ws_{infix}_{cpt}_*`. Registered field `key` values follow
`field_{infix}_{cpt}_*` (no `ws_` codebase indicator on keys). Tab field keys follow
`field_{infix}_{cpt}_{tab-label}_tab`. ACF group keys follow `group_{infix}_{cpt}_metadata`.

Build `tab-label` from the lowercased tab `label`, dropping symbols (`&`, `/`, etc.) and joining adjacent words
with a single underscore. Do not use `_and_`, do not abbreviate, and do not use deprecated abbreviations such as
`sol` for `statute_of_limitations` or `bop` for `burden_of_proof`. Examples: `Statute of Limitations & Thresholds`
becomes `statute_of_limitations_thresholds_tab`; `Processes & Remedies` becomes `processes_remedies_tab`.

CPT-specific ACF groups must have `menu_order` below 85; shared workflow groups occupy 85 to 99.

### Reserved Prefixes

`ws_auto_` is reserved for auto-stamp mechanisms used by workflow ACFs, written only by hook logic. Never use
`ws_auto_` for content fields.

### Cardinality

Field names reflect storage cardinality: singular for single-value, plural for multi-value (multi-selects,
repeaters, arrays). When a suffix declares shape, match the cardinality — `*_scope` versus `*_scopes`, `*_status`
versus `*_statuses`. When a field's base concept changes from plural to companion text, keep the modified-key
infix singular: `disclosure_targets` becomes `disclosure_target_details` or `disclosure_target_context`.

Exception: `*_details`, `*_context`, and `*_gloss` are lexical labels and do not track cardinality.

### Booleans

Boolean naming is limited to two roles. `has_*` is a *trigger boolean* — when true, it activates a companion or
dependent field. The `_details` suffix is the default companion shape, meaning `has_field_name` implicitly triggers
`field_name_details`. If a trigger activates a custom companion shape, the trigger **must** explicitly declare the
target's full suffix (e.g., `has_effective_date` triggers `effective_date`, and `has_negative_treatment_class`
triggers `negative_treatment_class`). `is_*` and `*_is_*` are *state booleans* — they describe a state and do not
trigger companions.

Any boolean outside these scopes requires approval and inline documentation.

### Companion Fields

Companion fields are conditional freetext fields. Their suffix describes the role they play in the ACF model, not
merely the fact that they hold prose.

`*_details` is triggered by a `has_*` boolean or a `has-details` sentinel in a related field. Use `*_details` when
the trigger says, in effect, "this value needs additional explanation." Name the trigger boolean `has_field_name`
to
activate the companion `field_name_details`. Do not name the trigger boolean `has_field_name_details`; that
pattern collides with the suffix convention and obscures the relationship.

`*_context` is the narrative anchor for a conditional cluster. It is normally triggered by a slug in the record's
recognition taxonomy and **almost always** appears with one or more sister fields that carry structured data for
the same
state. The `*_context` field explains the cluster as a whole.

`*_gloss` is a narrow freetext companion for a non-recognition trigger that reveals only one explanatory field.
Use `*_gloss` for a select value, taxonomy term, or other field-value trigger where no full cluster is being
created. A `*_gloss` **is explicitly permitted to** also appear inside an existing cluster when it explains a
specific sister value rather
than the cluster as a whole; in that role, it does not become the cluster anchor.

All `*_context` and `*_gloss` conditionals must declare their trigger field and trigger value. Other companion
shapes — `*_limits`, `*_phases`, or any `*_companion` — **are authorized to** rely on naming convention only when
the trigger and
companion share the same base name (e.g., `has_field_name_limits` triggers `field_name_limits`; `has-phases` in
`field_name` triggers `field_name_phases`). When trigger and companion do not share a base name, document the
conditional inline.

### Repeater Pluralization and Row Singularization

Repeater fields, by default, signify that multiple values **might** apply. Always pluralize repeater field names,
even
when a repeater is temporarily expected to hold only one row.

Inside repeater rows, avoid multi-value fields unless absolutely required. Prefer single-value subfields and add
one row per value.

Example:
- Incorrect: `Row 01 = [(Attribute = Color), (Specifics = Red, Blue)]`
- Correct: `Row 01 = [(Attribute = Color), (Specific = Red)]` and
           `Row 02 = [(Attribute = Color), (Specific = Blue)]`

#### Repeater Context

By convention, the final subfield in a repeater row is a freetext `*_context` companion. Set it as conditional on
the row identity field being non-empty, usually the first subfield. If the first subfield **is authorized to**
legitimately be
empty, use the primary required subfield instead. If no row subfield is guaranteed to be non-empty, the repeater
is probably mis-modeled; omit the conditional only with an inline annotation explaining the exception.

### Sister Fields

Sister fields inherit the conditional gate of a companion field without repeating the full condition. Most sisters
are structured fields, but a sister **is permitted to** also be freetext when it explains one part of the cluster
rather than the
cluster as a whole.

Annotate each sister with `Sister to sibling_field`. If the sister has an additional condition beyond the
inherited cluster gate, append the condition with `AND`, `OR`, or `NOT` using the accepted conditional-annotation
forms.

A sister cannot appear without its companion sibling. A sister does not become the cluster anchor merely because
it is freetext. The cluster anchor remains the triggered companion, normally a `*_context` field.

Sisters **must** be revealed in proximity of their sibling. Use the editorial judegement and prioritize workflow.

### Recognition Taxonomy Pattern

A *recognition taxonomy* is a controlled vocabulary whose terms each represent a bool-true-on-presence state for
the record. Each domain (legal records, assist-orgs, etc.) defines at most one recognition taxonomy that acts as
its primary state-of-record signal.

Recognition taxonomy slugs serve two roles:

- **Standalone state.** A slug's presence by itself answers a question about the record (e.g., "is this protection
  recognized in this jurisdiction?"). No companion fields are needed.
- **Cluster anchor.** A slug's presence triggers a `*_context` companion, optionally accompanied by sister fields
  carrying structured detail. The cluster represents a doctrinal or operational state with enough nuance to
  warrant structured capture.

Field count does not qualify a slug for either role. Some doctrines reduce cleanly to a yes/no on per-record
basis; others require structured detail. Both belong in the same taxonomy because the editor's mental model is
consistent: every term in the recognition taxonomy answers a bool-state question about the record.

### Cluster Triggers

A *triggered companion* is one conditional field revealed by one trigger.

A *triggered cluster* is a companion plus one or more sister fields revealed by the same primary gate. Clusters
**must strictly** be rooted in the record's recognition taxonomy because recognition slugs are the stable
bool-state layer for the doctrines or operational states the record describes.

When a condition reveals more than one field beyond its companion, it becomes a *triggered cluster*. The cluster
**must strictly** be anchored on a recognition taxonomy slug, without exception. If a core classificatory field
(which stores a controlling value rather than a bool-state) needs to trigger a cluster, it **must** fire off a
corresponding term presence in the recognition taxonomy to serve as the stable bool-state anchor. Non-empty field
gates are only allowed for single-field conditionals.

When a recognition slug requires a term from another taxonomy as a precondition, mark the slug `[P]` in the domain
spec's slug-to-companion map. When the requirement is mutual (the recognition slug and the related taxonomy term
must always appear together), mark it `[P+]` and cross-document it in the domain spec's hook requirements table.

Single-field conditionals (`*_gloss` companion only) **are permitted to** remain outside the recognition taxonomy.
If they migrate
into the recognition taxonomy, rename the freetext companion from `*_gloss` to `*_context`.

### Avoided Names

Discouraged but not forbidden:

- `*_recognized` and other legal-state bools — deemed unnecessary; add as a term to the record's recognition
  taxonomy whenever possible.
- `*_type` — deemed too generic; use a more precise suffix when one fits (prefer `*_class`, `*_scope`, `*_status`,
  `*_rule`, `*_framework`, `*_weight`, `*_strength`, `*_standard`, `*_source`, or another appropriate suffix). Use
  of `*_type` when no better suffix fits does not require annotation or approval.
- `*_limitation` — deemed too long; use `*_limit` for brevity.

Pluralize suffixes to match cardinality (e.g., `*_classes`, `*_scopes`, `*_statuses`). `status` is both singular
and plural in some traditions, but in this codebase the plural is `statuses`. Other forms (`stati`, etc.) are not
accepted. The modified-key infix **must** always be singular (e.g., `protected_actions` becomes
`protected_action_standards` or `protected_action_sources`).

### Data Shape Suffixes

Use a data-shape suffix only when the field data is the appropriate shape: `*_url`, `*_date`, `*_email`, `*_id`,
`*_value` (integer), `*_unit` (select; **strictly limited to** calendar units `days`, `weeks`, `months`, `years`).

**Duration pair.** When a duration is captured, use a `*_value` plus `*_unit` pair. Both fields are sisters of the
cluster's `_context`; both are visible together when the cluster is active and required together; neither field
is visibility-conditional on the other. New duration fields follow this shape without inline annotation. Where
both fields are a required pair, only `*_value` needs to be annotated; `*_unit` silently inherits the annotation.

---

## Sentinel Rules

Sentinels are reserved select choice keys or taxonomy term slugs with defined system behavior.

**Trigger sentinels.** `has-details` triggers the related `*_details` companion. Use it when the selected value
needs additional explanation and no already-triggered `*_context` or `*_gloss` companion is available. New
taxonomy-term trigger sentinels must use the `has-*` prefix; in hierarchical taxonomy tables, new `has-*` terms
must nest under `has-parent` to keep the hierarchy navigable.

**Redirect sentinels.** `see-context` and `see-gloss` do not create new companions. They point the editor to a
companion field that is already visible. Use `see-context` when the available companion is a cluster `*_context`
field. Use `see-gloss` when the available companion is a single-field `*_gloss`.

**Reserved sentinels.** `see-details` has no live implementation. The `see-*` prefix is reserved for redirect
sentinels, so `see-details` cannot be used as an active value even though no current redirect uses it. Reserved
status documents the collision and prevents future misuse.

**Non-standard sentinels.** Domain specs **are permitted to** define non-standard sentinels for
record-type-specific patterns. Each
must be documented in the domain spec where it is approved.

Avoid ambiguous values such as `other`, `unclear`, `mixed`, and `varies`. Prefer `see-context`, `see-gloss`, or
`has-details` when a companion field is needed to capture nuance. Use `mixed` or `varies` only when the value is
itself a meaningful classification and no additional nuance is expected. `other` and `unclear` are not valid
active choices. Annotate use of begrudgingly-permissible choices with inline comments.

---

## Inline Field Description Rules

### Annotation Discipline

Inline parenthetical annotations exist to clarify the ACF build, not to guide editors. Keep an annotation only
when it does one of the following: declares field shape (taxonomy, select choices, repeater structure, derived
expression, sister relationship); documents conditional logic that the build engine needs (the accepted
[Conditional Annotation] forms); or justifies why the field is necessary, including how it differs from existing
fields that cover similar concepts.

Drop annotations that read as data-entry guidance for editors (what to type, example values, descriptions of the
underlying domain concept). Editor guidance belongs in ACF instruction text on the field itself, not in this
spec.

### Default Field Types

**Default rule.** Any field whose shape is not specified by the conventions below or by an inline definition is a
freetext field. Field shape is never inferred from a name's apparent meaning — only from explicit convention or
inline definition.

These defaults apply by naming convention unless the inline definition says otherwise:

- `has_*`, `is_*`, and `*_is_*` are boolean fields.
- `*_class`, `*_scope`, `*_status`, `*_rule`, `*_framework`, `*_weight`, and `*_standard` are select fields.
- `*_share` describes a specified portion (e.g., `25-30%`).
- `*_compare` describes a mandated comparison and uses `gte`, `lte`, `gt`, `lt`, or `eq`.
- `*_value` is an integer or number field.
- `*_unit` is a select field — calendar unit unless stated otherwise.
- `*_formula` describes mandated calculations.
- `*_sanctions` describes specified penalized conduct, **strictly** as a repeater.
- `*_application` describes where or how a standard applies and is a select field.
- `*_bar` is used for blocking doctrines or procedural bars and **must strictly** be select or boolean.
- `select` means single-select unless multi-select is specified.

### Default Taxonomy Field Settings

Taxonomy fields default to: type `taxonomy`, multi-select, `load_terms: 1`, `save_terms: 1`.

### Conditional Annotation

Use only these accepted conditional-annotation forms:

- Taxonomy term present: `conditional on slug in taxonomy_field`
- Taxonomy term absent: `conditional on slug absent in taxonomy_field`
- Any term in taxonomy field: `conditional on taxonomy_field is non-empty`
- Any non-empty value: `conditional on trigger_field is non-empty`
- Specific value in select field: `conditional on trigger_field is trigger_value`
- Specific value absent from select field: `conditional on trigger_field is NOT trigger_value`
- Specific value in multi-select field: `conditional on trigger_field includes trigger_value`
- Child taxonomy value present: `conditional on any child-slug of parent-slug in taxonomy_field`
- Boolean true: `conditional on bool_field is true`
- Boolean false: `conditional on bool_field is false`
- Compound values and conditions: combine with all-caps `AND`, `OR`, and `NOT`

Absent conditionals imply "AND not-empty"; the `absent from select field` form **is authorized to be** used with
multi-select.

`taxonomy_field is non-empty` is the buildable form when the conditional should trigger on the presence of
any term in the taxonomy field. Documentation prose may describe this as "any-slug" for readability, but the
actual conditional is always written `taxonomy_field is non-empty`. Conditionals cannot test for `any-slug` directly; any selected slug
makes the field non-empty.
Annotation **is formally redundant and should be omitted** for `*_details`, `*_limits`, `*_phases`, and
`*_companions` when the naming convention
makes the trigger unambiguous. It is required for all other conditional fields. `*_context` and `*_gloss` fields
must always declare their trigger field and trigger value. When using `AND`, `OR`, or `NOT`, omit "conditional on"
while using accepted conditional notation.

Sister fields inherit the cluster gate; their conditional annotation needs only value-specific gates that further
restrict visibility (`is`, `is NOT`, `includes`). A sister never needs `is non-empty` against another field in its
own cluster — that's already guaranteed by the cluster gate.

---

— drafted for Dejunai by Claude (Anthropic), session of 2026-05-06
