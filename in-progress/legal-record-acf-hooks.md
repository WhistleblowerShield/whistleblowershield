# Legal Record ACF Hook Requirements

---

### General
Document required hook use in the inline definition of field where hook is needed.

- Derived fields: auto-fill on load and on save.
- Merged hidden fields (e.g., `_related_agencies`, `_precedent_ids`, `_parent_ids`): auto-fill on save.
- Derived select choices (e.g., `court` filtered by `jurisdiction`): filter on field load.

Always write unified hooks over duplicates. A single hook using `get_post_type()` is preferred over two
near-identical hooks per CPT. Reuse hooks wherever possible.

### Precedent Taxonomy Mapping Choices

`extended_taxonomies` and `suppressed_taxonomies` use the same controlled taxonomy-term picker.

- `taxonomy` choices come from one allowlist of legal-record taxonomies that precedent may extend or suppress.
- `term` choices are filtered by the selected `taxonomy` in the same repeater row.
- Store both values as slugs for readable ingest, export, and review.
- Validate on save that selected `term` exists in selected `taxonomy`.
- Do not allow free-entry taxonomy names or term names in these mapping rows.

### Contradiction Guards
Document when hook is required to guard against invalid combinations of values in table below. Note if cross-field
monitoring is required. When cross-field monitoring also requires cross-tab monitoring add to detailed entry to 
[Cross-Tab Conditional and Monitoring] block.

- `protected_classes` and `excluded_classes` — same class slug must never be present in both taxonomies.
   * When overlap is detected: flag for editor resolution; do not auto-remove because the correct side is legal-context
     dependent.
- `garcetti-exception` — invalid unless `public-sector` is present in `employment_sectors`.
   * When `public-sector` is absent: remove `garcetti-exception`, clear `garcetti_exception_context`, and clear any
     sister fields.
- `mitigation-exception` — invalid without `mitigation-required` in `legal_recognitions`.
   * When `mitigation-required` is absent: remove `mitigation-exception`, clear `mitigation_exception_context`,
     and clear any sister fields.
- `contractual-waiver` — invalid when `civil_action_waiver_scope` is `anti`.
   * When `anti` is set: remove `contractual-waiver` from `legal_recognitions`, clear `contractual_waiver_context`,
     and clear any sister fields.
- `jury-trial` — invalid without `private-right-of-action` in `legal_recognitions`.
   * When `private-right-of-action` is absent: remove `jury-trial`, clear `jury_trial_context`, and clear any sister fields.
- `exhaustion-required` — invalid when `process_pathway_scope` is `direct-court`.
   * When `direct-court` is set: remove `exhaustion-required`, clear `exhaustion_required_context`, and clear any
     sister fields.
- `direct-filing-permitted` — invalid with `exhaustion-required`.
   * When `direct-filing-permitted` is present in `process_types`: remove `exhaustion-required`, clear
     `exhaustion_required_context`, and clear any sister fields.
- `sovereign-immunity-waiver` — invalid when `sovereign_immunity_waiver` is `none` or
  `sovereign_immunity_statuses` includes `not-waived`.
   * When no waiver is indicated: remove `sovereign-immunity-waiver` from `legal_recognitions`.
- Multi-select fallback values — `see-context` / `see-details` must not be combined with specific choices.
   * Applies to the same-field multi-selects listed in [Cross-Tab Conditional and Monitoring].
- `malicious_reporting_sanctions.sanction_penalty` — `felony` and `misdemeanor` must not appear in the same row.
   * Use separate repeater rows for separate criminal tracks.
- `scope` — enforce precedent taxonomy bucket consistency.
   * When `scope` is `favorable`: clear `suppressed_taxonomies`.
   * When `scope` is `adverse`: clear `extended_taxonomies`.
   * When `scope` is `neutral`: clear both `extended_taxonomies` and `suppressed_taxonomies`.
- `burden_shifting_frameworks` — `mixed-motive` is incompatible with `but-for` in most formulations; multi-framework combinations need validity check. Burden of Proof tab.
- `election_of_remedies_rules` — `no-election-required` invalidates all other choices in the same field. Retaliation tab.
- `proper_defendants` — `employer-entity-only` is mutually exclusive with individual-liability choices (`individual-supervisors`, `any-individual`). Waiver & Scope tab.
- `sol_triggers` — evaluate whether `see-context` exclusivity rule extends to this field. Statute of Limitations & Thresholds tab.
- `types` (citation-specific) — citation type choices likely mutually exclusive; needs evaluation. Identity tab.

### Agency Filtering

- `primary_agency` — auto-fill with the first attached `ws-agency` post when empty. Filter choices to
   currently attached posts only. Instructions when empty: `"Attach one ws-agency to local or federal first"`;
   when non-empty: `"Override primary_agency with any currently attached local or federal agency"`.
- `local_agencies` — filter to jx-applicable, non-federal `ws-agency` posts. (Stub: future refinement
   intersecting `ws_process_type`, `ws_disclosure_targets` and `ws_protected_disclosure` taxonomies.)
- `federal_agencies` — filter to federal `ws-agency` posts only. (Stub: future refinement intersecting
  `ws_process_type`, `ws_disclosure_targets` and `ws_protected_disclosure` taxonomies.)

---

## Relationship Direction Contract (For Sync)

- Parent-bearing legal records: `citation`, `construction`.
- Child-bearing  legal records: `statute`, `common_law`.

---

## Cross-Tab Conditional and Monitoring

### Contradiction Guard Cross-Tab Pairs
The following hook guards compare fields that live on different tabs:

- `garcetti-exception` in `legal_recognitions` requires `public-sector` in `employment_sectors`.
- `exhaustion-required` in `legal_recognitions` conflicts with `direct-filing-permitted` in `process_types`.
- `sovereign-immunity-waiver` in `legal_recognitions` conflicts with `sovereign_immunity_waiver` is `none` or
  `not-waived` in `sovereign_immunity_statuses`.
- `protected_classes` and `excluded_classes` must not contain the same class slug.

### Contradiction Guard Cross-Field in Sister Blocks
The following hook guards compare fields in a single block:

- `fee_shifting` block (Processes & Remedies tab), monitor for contradictions and invalid combinations.
  * `fee_shifting_standard` has possible values that makes some values in `fee_shifting_scopes` invalid.
  * `fee_shifting_scopes` is multi-select and can create invalid combos.
  * `fee-shifting-standard` in `legal_recognitions`, means that `none-american-rule` can only be set with phased
     exceptions. `fee_shifting_scopes` must be set to `has-phases`only, or `fee-shifting-standard` removed from
    `legal_recognitions`.

### Contradiction Guard Same-Field Multi-Selects
The following hook guards protect multi-select fields whose choices include umbrella or fallback values:

- `malicious_reporting_sanctions.sanction_penalty` cannot combine `felony` and `misdemeanor` in the same repeater
   row. Add a second row when the same provision creates separate felony and misdemeanor tracks.

### mixed-motive → mixed_motive_remedy_context

When `burden_shifting_frameworks` (Burden Of Proof tab) includes `mixed-motive`,
the field `mixed_motive_remedy_context` (Processes & Remedies tab) becomes relevant.
ACF conditional logic cannot surface this cross-tab dependency natively.

Implementation: register an `acf/save_post` hook (or `admin_notices` hooked to
`current_screen`) that detects `mixed-motive` in `burden_shifting_frameworks` and
emits a dismissible admin notice directing the editor to the Enforcement tab:

> "Mixed-motive framework selected — please complete the 'Mixed Motive Remedy
>  Context' field on the Enforcement tab."

Notice should be informative (not alarmist) and display on the edit screen for all four legal record CPTs.
Dismiss state does not need to persist — the notice should reappear on each save as long as `mixed-motive`
is present and `mixed_motive_remedy_context` is empty.

### blacklisting in adverse_actions → is_blacklisting_extended (Processes & Remedies Tab)

`is_blacklisting_extended` (Processes & Remedies tab) is conditionally revealed
  * When `adverse_actions` (Retaliation tab) includes `blacklisting`.


---

## Slug reveals context.
When slug-x is present in legal_recognitions, slug_x_context is revealed; _context is descriptive and is not required by default.

Each triggered cluster has one primary required sister.
Each slug-triggered cluster must include one structured sister field that matches the doctrinal function of the slug, and that field is marked [R] in the slug map.

Additional sisters are allowed when parallel.
More than one sister is acceptable when the fields are genuinely parallel structured companions that arise from slug presence alone rather than from a downstream field choice.

Prefer direct conditionals over fake siblings.
If a field depends on a specific value of another field in the cluster, it should be declared with an explicit conditional on that value rather than as a sister to _context.

Use has-details only for dedicated *_details companions.
If a field includes has-details, that sentinel triggers a same-stem *_details field; do not use see-details unless an already-active details field exists elsewhere in the same cluster.

Use see-context when overflow belongs in _context.
If a structured choice is directionally correct but incomplete, see-context may appear alone or alongside structured values, and the overflow belongs in the cluster’s _context field.

Requiredness belongs first in field attributes.
Where a sister is required within a triggered cluster, generated ACF should set 'required' => 1 where applicable; hooks should handle the cases conditional logic alone cannot safely enforce.

Critical empty states block save.
If a triggering slug is present and a required sister field is empty, save-blocking validation should reference both the slug and the missing field so the editor must either populate the field or remove the slug.

Absence can define non-applicability.
If the absence of a slug already means the cluster does not apply, do not create redundant “none” choices inside the cluster’s structured fields.

Do not encode ambiguity as a fake enum when narrative already solves it.
Avoid unclear-type choices where has-details or see-context already provides a cleaner review-state path.

### Use test
A field should stay a sister only if this sentence is true:

“This field becomes relevant because the slug is present, not because another field in the cluster took a specific value.”

If that sentence is false, make it a direct conditional instead.

#### Example
For sovereign-immunity-status, the cluster can truthfully be modeled as:

sovereign-immunity-status → sovereign_immunity_context + sovereign_immunity_status[R].

sovereign_immunity_waiver is conditional on sovereign_immunity_status is NOT not-waived.

sovereign_immunity_scope is conditional on sovereign_immunity_status is non-empty.

sovereign_immunity_status_details is conditional on sovereign_immunity_status is has-details.

### Conditional _context field only Rule

If _context is the only field revealed in a triggered cluster, _context becomes the required field for that cluster.

A slightly fuller version, if you want the logic spelled out:

Within a triggered cluster, _context is not required by default. However, if slug presence reveals no required structured sister and _context is the only revealed field, _context becomes required and should carry 'required' => 1 or equivalent save-blocking validation.

Why it matters
Without this rule, a slug could create a “ghost cluster” where the editor selects the recognition term, sees a context box, leaves it blank, and still saves a record with no actual cluster content. That is exactly the sort of silent fall-through you’ve been trying to kill with fire all night.

Interaction with the other rules
So the cluster hierarchy becomes:

If there is a matching structured sister, that sister is the required field and gets [R].

_context is revealed but not required by default.

If no structured sister is revealed, _context becomes required.

Hooks block save whenever the triggered cluster’s required field is empty.

