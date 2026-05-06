# Legal Record ACF Rule Revision Proposals

Draft proposals for sections in `legal-record-acf-fields-v2.6.md` and
`legal-record-acf-hooks.md` that need more than typo repair. These are not
canonical until copied into the relevant spec.

---

## Main Spec: Companion Fields

### Problem

The current rule correctly separates `*_details`, `*_context`, and `*_gloss`,
but the boundaries are still easy to misread:

- `*_details` is a trigger-companion tied to `has_*` booleans or `has-details`.
- `*_context` is the cluster narrative field, usually rooted in
  `legal_recognitions`.
- `*_gloss` is a single-field explanatory companion, but can also appear as a
  narrowly-triggered field inside a larger cluster.

The last case is the dangerous one. A field such as
`causation_application_context` can be a sister in a large cluster even though
it is freetext. A field such as `mixed_motive_remedy_gloss` can be a
field-value-triggered explanation inside a cluster without becoming the cluster
anchor.

### Proposed Replacement

```md
### Companion Fields

Companion fields are conditional freetext fields. Their suffix describes the
role they play in the ACF model, not merely the fact that they hold prose.

`*_details` is triggered by a `has_*` boolean or a `has-details` sentinel in a
related field. Use `*_details` when the trigger says, in effect: "this value
needs additional explanation." Do not name the trigger boolean
`has_field_name_details` when the companion is `field_name_details`, unless the
exception is explicitly approved inline.

`*_context` is the narrative anchor for a conditional cluster. It is normally
triggered by a slug in `legal_recognitions`, and usually appears with one or
more sister fields that carry structured data for the same doctrine. The
`*_context` field explains the cluster as a whole.

`*_gloss` is a narrow freetext companion for a non-`legal_recognitions` trigger
that reveals only one explanatory field. Use `*_gloss` for a select value,
taxonomy term, or other field-value trigger where no full cluster is being
created. A `*_gloss` may appear inside an existing cluster when it explains a
specific sister value; it does not become the cluster anchor.

All `*_context` and `*_gloss` conditionals must declare their trigger field and
trigger value. Other companion shapes, such as `*_limits`, `*_phases`, or
`*_companion`, may rely on naming convention only when the trigger and
companion share the same base name.
```

---

## Main Spec: Sister Fields

### Problem

The current sister-field prose is true, but not quite broad enough. It implies
that sisters are usually structured fields, while the current model has
legitimate freetext sisters such as `causation_application_context`. The issue
is not whether the sister is prose or structured data; the issue is whether the
field depends on the same cluster gate.

### Proposed Replacement

```md
### Sister Fields

Sister fields inherit the conditional gate of a companion field without
repeating the full condition. Most sisters are structured fields, but a sister
may also be freetext when it explains one part of the cluster rather than the
cluster as a whole.

Annotate each sister with `Sister to sibling_field`. If the sister has an
additional condition beyond the inherited cluster gate, append the condition
with `AND`, `OR`, or `NOT` using the accepted conditional-annotation forms.

A sister cannot appear without its companion sibling. A sister does not become
the cluster anchor merely because it is freetext. The cluster anchor remains
the triggered companion, normally a `*_context` field.

Sisters may appear before or after the sibling they inherit from. Use the order
that gives editors the clearest workflow.
```

---

## Main Spec: Cluster Triggers

### Problem

The current rule says clusters with more than one field beyond `_context` must
trigger from `legal_recognitions`. That is too narrow and creates confusion
around giant clusters rooted to a non-`legal_recognitions` field, such as the
causation standard cluster. It also leaves the difference between a
single-field companion and a cluster slightly muddy.

### Proposed Replacement

```md
### Cluster Triggers

A triggered companion is one conditional field revealed by one trigger.

A triggered cluster is a companion plus one or more sister fields revealed by
the same primary gate. Clusters should normally be rooted in
`legal_recognitions`, because recognition slugs are the stable bool-state
layer for legal doctrines.

When a cluster reveals more than one field beyond its companion and the trigger
is a legal doctrine state, migrate the trigger to `legal_recognitions`. When
the trigger is instead a core classificatory field that already stores the
controlling legal value, such as `causation_standard`, a non-empty field gate is
allowed if any selected taxonomy term should activate the cluster.

If a `legal_recognitions` slug requires a term from another taxonomy, mark the
slug `[P]` in the [Slug-to-Companion Map]. If the requirement is mutual, mark it
`[P+]` and cross-document it in the hook requirements table.

Single-field conditionals may remain outside `legal_recognitions`. If they are
migrated into `legal_recognitions`, rename the freetext companion from
`*_gloss` to `*_context`.
```

---

## Main Spec: Sentinel Rules

### Problem

`see-context`, `see-gloss`, `has-details`, and reserved `see-details` now need
their own sorted doctrine. The current prose still makes it too easy to treat
all sentinel values as interchangeable escape hatches.

### Proposed Replacement

```md
## Sentinel Rules

Sentinels are reserved select choice keys or taxonomy term slugs with defined
system behavior.

**Trigger sentinels.** `has-details` triggers the related `*_details`
companion. Use it when the selected field needs additional explanation and no
already-triggered `*_context` or `*_gloss` companion is available. New taxonomy
term trigger sentinels must use the `has-*` prefix.

**Redirect sentinels.** `see-context` and `see-gloss` do not create new
companions. They point the editor to a companion field that is already visible.
Use `see-context` when the available companion is a cluster `*_context` field.
Use `see-gloss` when the available companion is a single-field `*_gloss` field.

**Reserved sentinels.** `see-details` has no live implementation. It remains
reserved for possible future use and should not appear in active choice lists.

Avoid ambiguous values such as `other`, `unclear`, `mixed`, and `varies`.
Prefer `see-context`, `see-gloss`, or `has-details` when a companion field is
needed. Use `mixed` or `varies` only when the value is itself a meaningful
classification and no additional nuance is expected. `other` and `unclear` are
not valid active choices.

Non-standard sentinels must be documented where approved.
```

---

## Main Spec: Conditional Annotation

### Problem

The new `any-slug` doctrine for `causation_standard` is sound, but the accepted
conditional forms need a place for it. Otherwise the spec says "use only these
forms" and then immediately blesses a non-form in the slug map.

### Proposed Addition

Add this bullet under accepted conditional forms:

```md
- Any term in taxonomy field: `conditional on taxonomy_field is non-empty`
```

Then add a short rule after the list:

```md
Use `taxonomy_field is non-empty` only when any selected term in the taxonomy
field should satisfy the condition. Do not write `any-slug` as an ACF
conditional; `any-slug` is documentation shorthand only. The actual conditional
is `taxonomy_field is non-empty`.
```

### Proposed Slug-Map Note

```md
Related taxonomy where any slug will do is documented as
`non-empty in taxonomy_field`. This is the buildable form of the rule:
conditionals cannot test for `any-slug`, but any selected slug makes the
taxonomy field non-empty. Slug rule bent; approved.
```

---

## Hook Spec: Triggered Cluster Guidance

### Problem

The hook spec currently defines clusters as more than one field and then says
not all `legal_recognitions` slugs trigger clusters. It also assumes clusters
are always rooted in `legal_recognitions`, which is mostly true but not
absolute.

### Proposed Replacement

```md
## Triggered Cluster Guidance

A triggered companion is one field revealed by a trigger. A triggered cluster
is a companion plus one or more sister fields revealed by the same primary
gate.

Most legal-record clusters are rooted in `legal_recognitions`, because those
slugs represent doctrine-level bool states. A recognition slug may trigger:

- no companion, where the slug alone captures the state;
- one `*_context` companion;
- a full cluster: one `*_context` companion plus sister fields.

Some clusters may be rooted in a non-`legal_recognitions` field when the
trigger is a core classificatory value rather than a doctrine bool. In those
cases, the main spec must document the trigger explicitly.

The first companion field normally carries the narrative context for the
cluster. Sister fields carry structured values or narrower explanations that
belong to the same triggered state.
```

---

## Hook Spec: Details, Sentinels, and Companions

### Problem

The section now says "Except where reasonably needed," but does not say what
counts as reasonably needed. That makes future disputes inevitable and gives
agents a shiny place to invent doctrine.

### Proposed Replacement

```md
### Details, Sentinels, and Companions

Avoid `has-details` inside a cluster rooted to `*_context` when the existing
context field can carry the needed explanation. Use `see-context` instead.

Use `has-details` inside a cluster only when the extra explanation belongs to a
specific sister value and should not be mixed into the cluster context. Common
approved shapes:

- a sister select where one value requires a narrow explanation;
- a multi-select sister where `has-details` is one value among otherwise
  structured choices;
- a record-type-specific field where the details field already exists by
  naming convention.

Use `see-gloss` when the already-visible companion is `*_gloss`, not
`*_context`.
```

---

## Hook Spec: Required Fields

### Problem

The word "requiredness" is doing too much work and sounds like it wandered in
from a validation framework manual. The rule also needs to separate ACF field
attributes from hook-blocking behavior.

### Proposed Replacement

```md
### Required Fields

The slug map is the source of truth for conditionally required cluster fields.
Fields marked `[R]` must be non-empty when their triggering slug is present and
any additional `[+]` condition has been satisfied.

If a triggered cluster reveals only one companion field, that companion is
required by default. If a cluster has structured sisters, at least one
structured sister is usually marked `[R]`; the context field may also be marked
`[R]` when narrative explanation is mandatory.

ACF field attributes may enforce required fields when visibility conditions
make that safe. Hooks must still validate conditional requirements that ACF
cannot reliably enforce, and must report both the triggering slug/value and the
empty required field.
```

---

## Hook Spec: Umbrella, Sentinel, and Negation Values

### Problem

Umbrella values, redirect sentinels, trigger sentinels, and reserved negation
values are packed into one paragraph. That is compact, but it hides important
differences.

### Proposed Replacement

```md
**Umbrella values.** Values ending in `-only` are umbrella values. When present
in a multi-value field, they exclude granular sibling values in the same field.
Sentinels are not granular sibling values.

**Trigger sentinels.** `has-details` and other `has-*` values reveal a
companion field.

**Redirect sentinels.** `see-context` and `see-gloss` do not reveal fields.
They point to an already-visible companion field.

**Blanket negation values.** Generic `none`, `none-*`, and `no-*` values are
reserved. They are valid only in required fields where an empty value cannot
express the legal result. No generic blanket negation value is currently active.

**Exception.** `none-american-rule` is valid in `fee_shifting_standard`. It is
a legal term, not a generic negation sentinel.
```

---

## Hook Spec: Special Cases To Recheck Later

These are not proposed rewrites yet. They are deferred notes from the current
review pass.

- `federal_state_interaction` still looks like a rule cluster trying to happen.
  It currently has a base select, a targeted savings-clause gloss, a general
  interaction gloss, and details.
- `superseded-by-statute` now has `superseded_by_statute_id`; later hooks may
  need to decide whether that field is required or merely recommended when the
  class is selected.
- `has_fee_shifting_phases` is an auto-set field inside a recognition cluster.
  It should probably stay special-case documented in the hook spec until real
  data proves the shape stable.
- `see-gloss` should be added to any hook helper that currently detects only
  `has-*` and `see-*` sentinels by prefix. If the helper already matches
  `see-*`, no code change is needed.
- Inactive or non-existing fields should not trigger stale-value hook logic.
  Where a taxonomy slug suppresses an entire cluster, hook validation should
  flag the controlling taxonomy conflict first and avoid pretending hidden
  downstream sisters are independently meaningful.
