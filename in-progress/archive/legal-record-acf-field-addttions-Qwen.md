Here are the targeted expansions, drafted to slot directly into `legal-record-acf-fields.md` without disrupting the existing architecture, naming conventions, or conditional syntax. Each block includes exact placement instructions and uses the approved annotation phrasing.

---

### 📍 Insert in: `Classifications` Tab
**Placement:** After `protected_action_context`
```markdown
`garcetti_exception_context`       — (conditional on employment_sectors includes public-sector OR manager-rule-exclusion in legal_recognitions; documents speech-pursuant-to-official-duties carve-out and Lane v. Franks testimony exception)
```

---

### 📍 Replace in: `Statute of Limitations & Thresholds` Tab
**Placement:** Replace the entire `has_preemption` → `preemption_details` block with:
```markdown
`federal_state_interaction`        — (select: `express-preemption`|`savings-clause-preserves-state`|`concurrent-enforcement`|`field-preemption`|`state-exceeds-federal-floor`|`see-context`)
`savings_clause_context`           — (conditional on federal_state_interaction is savings-clause-preserves-state OR savings-clause in legal_recognitions; documents explicit statutory preservation of state-law claims or higher state floors)
`interaction_details`              — (conditional on federal_state_interaction is non-empty; replaces legacy preemption_details; captures preemption carve-outs, savings clause text, or concurrent enforcement mechanics)
```
*Note: Add to `Rename Normalization` section if maintaining backward traceability:*
`` `preemption_direction`                            → `federal_state_interaction` ``

---

### 📍 Insert in: `Process & Remedies` Tab
**Placement:** After `mitigation_required_details`
```markdown
`process_pathway`                  — (select: `agency-first-mandatory`|`direct-court`|`hybrid-right-to-sue-on-inaction`|`see-context`; defines statutory exhaustion sequence vs. immediate civil action)
`agency_inaction_triggers_suit`    — (bool; true when agency inaction explicitly triggers de novo civil right rather than merely permitting suit; sister to process_pathway)
`suspends_sol_during_admin`        — (bool; true when SOL is expressly tolled during administrative pendency; use statutory_tolling_context for partial/jurisdictional variance)
`mitigation_exception_context`     — (conditional on mitigation_required is no; documents statutory or judicial exceptions to mitigation duty, e.g., FCA back pay, equitable tolling carve-outs)
`interest_provision`               — (select: `none`|`pre-judgment-statutory`|`post-judgment`|`discretionary`|`see-context`)
`interest_provision_context`       — (conditional on interest_provision is non-empty; captures rate caps, compounding rules, or sovereign immunity interest limits)
`remedy_caps`                      — (repeater; each row:
├── `cap_type`                        [select: `emotional-distress`|`punitive`|`compensatory`|`aggregate`|`employer-size-tiered`]
├── `employer_threshold`              [string/range or integer; e.g., "15-14", "100+"]
├── `max_amount`                      [string/currency or "uncapped"; e.g., "300000"]
└── `applies_to`                      [select: `single-claim`|`per-plaintiff`|`per-incident`|`aggregate-action`|`see-context`])
`remedy_caps_context`             — (conditional on remedy_caps is non-empty OR remedies includes has-limits; captures cross-cap stacking rules, offset provisions, or taxability interactions)
```

---

### 📍 Insert in: `Burden Of Proof` Tab
**Placement:** After `employer_defense_details`
```markdown
`same_decision_standard`           — (select: `preponderance`|`clear-and-convincing`|`not-available`|`see-context`; conditional on employer_defenses includes same-decision-defense OR same-decision-clear-convincing)
`causal_nexus_statutory_text`      — (conditional on causation_standards is non-empty; captures explicit statutory divergence from circuit common law on causation standard, e.g., but-for vs contributing-factor backstop)
```

---

### 🔗 Update: `Slug-to-Companion Map` (Optional but Recommended)
Add to the `Burden of Proof` section to keep the taxonomy-trigger map in sync:
```markdown
'same-decision-defense'            → 'same_decision_standard'                  // Recognized
```
Add to the `Statute of Limitations` section:
```markdown
'savings-clause'                   → 'savings_clause_context'                 // Recognized
```

---

### 📐 Integration Notes (Why This Doesn't Shred the Spec)
1. **Zero structural drift:** Every addition uses existing tab slots. No new tabs, no reordering of mandatory fields.
2. **Conditional syntax compliance:** All triggers use the four approved phrasing patterns. Compound conditions use explicit `AND / OR / NOT` logic.
3. **Sister/Companion discipline:** `agency_inaction_triggers_suit` and `interest_provision_context` are explicitly marked as sisters or conditional companions. None orphan themselves.
4. **Taxonomy-first alignment:** Where possible (`same_decision_standard`, `federal_state_interaction`, `savings_clause_context`), the spec leans on `ws_legal_recognition` or explicit selects rather than proliferating booleans. This keeps the ACF screen clean and the ingest schema predictable.
5. **Ingest-ready:** The repeater structure for `remedy_caps` matches your existing `criminal_sanctions` pattern. JSON ingest can map cleanly to nested arrays without schema breaks.

Drop these blocks into the spec exactly as positioned. They slot into your canonical draft without touching load order, hook requirements, or the rename normalization table (except the single `preemption_direction` alias note). If you want the JSON ingest mapping lines for these new fields drafted to match your `legal-record-acf-fields.md` schema table, say the word and I'll generate the exact `JSON key -> ACF field` rows.