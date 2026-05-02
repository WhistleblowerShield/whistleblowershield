Let me read the glossary file before synthesizing.Good. The glossary is current and not relevant to the red team synthesis. Now let me work through all five reports.

---

The red team did its job. Five models, no shared context, and they converged on the same six or seven things. That convergence is the signal — if three independent models hit the same gap, it's real.

Here's my synthesis, filtered through what the spec is actually trying to do:

---

## Accept — genuine gaps, fit the existing pattern cleanly

**`sol_trigger_event`** — this is the sharpest find, and Perplexity, MetaAI and NotebookLM all hit it independently. Your `sol_trigger` captures *how the clock runs*. This captures *when it starts*. `notice-of-action` vs `occurrence-of-action` vs `discovery-of-harm` vs `constructive-discharge-accrual`. Single select, sits after `sol_trigger`, sister to `sol_trigger_context`. This is the Chardon trap. Accept.

**`imputed-knowledge` option on `employer_knowledge_scope`** — four models flagged this. One option add, no new field, closes the cat's paw bridge. Accept.

**`constructive_discharge_standard`** — Perplexity nailed the parallel to `reasonable_belief_context`. Sister to `constructive_discharge_context`, same pattern: `objective-intolerability`|`intent-required`|`dual-prong`|`has-details`. Accept.

**`settlement_restriction_scope`** — Perplexity only but it's correct. `confidential-settlement-restriction` fires a context companion but the scope varies enormously: amount-only vs. facts vs. full prohibition vs. agency notification required. A select sister to `settlement_restriction_context`. Accept.

**`individual_liability_scope`** — Perplexity only but again correct. `individual-liability` is currently a no-companion term in `legal_recognitions`. Adding a conditional select (`supervisor`|`coworker`|`officer-director`|`any-individual`|`has-details`) closes a real gap without adding much weight. Accept.

**`has_blacklisting_protection` → `blacklisting_details`** — MetaAI and Gemini both flagged it. `blacklisting` exists in `ws_adverse_action` as the *harm*. This captures the *protection signal* — whether the statute specifically covers post-employment blacklisting by future employers. Retaliation tab, `has_*` pattern. Accept.

**`anti_slapp_scope`** — Perplexity. Sister to `anti_slapp_protection_context`, select: `motion-to-strike`|`discovery-stay`|`fee-shift-on-motion`|`full-procedural`|`see-context`. The difference between California § 425.16 and a weak analog is enormous. Accept.

**New terms for existing taxonomies — the clear ones:**
- `ws_remedy`: `emotional-distress-damages`, `interest-on-backpay`, `tax-gross-up` — three models each, all correct
- `ws_protected_action`: `refusal-to-violate-law`, `cooperation-with-investigation` — solid
- `ws_legal_recognition`: `prospective-whistleblower-protection`, `temporal-proximity-sufficient`, `continuing-violation-doctrine` (already in taxonomy as a term, confirm), `discovery-protection`
- `ws_protected_disclosure`: `mismanagement-of-funds` — public sector gap, correct
- `ws_adverse_action`: `discovery-harassment`, `benefit-clawback` (Perplexity honorable mention — vicious and real)
- `ws_employer_defense`: `same-decision-defense` is already seeded — confirm. `bona-fide-occupational-qualification` — borderline, probably too employment-law-generic for this schema

---

## Accept with modification

**`has_first_to_file_bar` + `has_public_disclosure_bar`** — Perplexity. These belong on the SOL tab as `has_*` booleans with context companions. They're qui tam specific, so they could be gated as substantive-record fields only. The data integrity point is sound — `bounty-qui-tam-award` in remedies creates a false impression if the claim is barred by prior public disclosure. Accept, but scope to substantive records only, note them as qui-tam-relevant in the spec.

**`sovereign_immunity_waiver`** — MetaAI and NotebookLM. You already have `sovereign_immunity_limits` (multi-select) and `sovereign_immunity_scope` + `sovereign_immunity_details` in Waiver & Scope. This is a select sister to that cluster: `explicit-waiver`|`implied-waiver`|`none`|`not-applicable`. It's additive not redundant. Accept as a sister field to the existing sovereign immunity cluster.

**`is_joint_employer_liable` → `joint_employer_details`** — Gemini and MetaAI. `joint-employer` already exists in `proper_defendants` as a value. The `has_*` bool is redundant with that — the select already captures it. But the *details* companion is missing. Accept as: add `joint_employer_details` conditional on `proper_defendants` includes `joint-employer`, drop the bool.

**IC channel sequence** — Perplexity's most interesting find. The `intelligence-community` and `classified-information` protected disclosures have mandatory sequential channel requirements that destroy protection if violated. `disclosure_channel_scope` with `mandatory-internal-first` doesn't capture this. A sentinel `ic-channel-required` under `national-security` in `ws_protected_disclosure` triggering `ic_channel_sequence_context` is the right fit. Accept, narrow scope.

---

## Defer — correct but premature

**`confidentiality_mandate`** — MetaAI. Maya's anonymity question. Whether the law *mandates* identity protection rather than permitting it. Real gap but `anonymity_protection_context` (already triggered by `anonymity-protection` in `legal_recognitions`) is the right home for this nuance for now. Add to context field instructions, promote to select field at 3.17 when data shows it needs structure.

**`burden_shift_trigger` / `burden_shift_standard` cluster** — Perplexity and Grok. Correct that the schema captures *what* the standard is but not *when burden shifts and to whom*. But this is deep litigation mechanics that will mostly live in `bop_details` freetext until you have enough records to know what structure emerges. Defer.

**`pretext_standard`** — NotebookLM. Pretext-only vs pretext-plus vs mixed-motive-alt. Correct distinction. Defer — `burden_shifting_framework` plus `burden_shifting_context` handles this adequately for now.

**`ruling_impact_weight`** — NotebookLM. Landmark vs incremental vs clarifying. Interesting for Daniel but subjective and editorial. Defer.

**`election_first_filed_forum`** — Perplexity. Which forum was filed first when `first-filed-controls` applies. Correct gap but this is state-at-filing, not state-of-law. It's a record-keeping field for case management, not a legal definition field. Defer.

**All proposed new taxonomy tables** — `ws_litigation_safeguard`, `ws_statutory_purpose`, `ws_burden_of_proof`, `ws_evidence_type`, `ws_immunity_limit`, `ws_legal_authority_source`, `ws_public_policy_exception`, `ws_administrative_exhaustion_path`. Some interesting concepts but none are ready. The data doesn't yet exist to populate them, and they'd be mostly empty tables at launch. Defer all.

---

## Reject

**`ws_org_culture` with `niche-lifestyle-friendly`** — Gemini's Utah joke. No.

**`is_per_curiam`** — Gemini. Per curiam affects citation weight but that's already handled by `binding_scope`. Clutter.

**`has_pre_suit_discovery`** — Gemini. Rule 202 petitions are Texas/Ohio procedure, not a whistleblower protection concept. Wrong layer.

**`ws_burden_of_proof` as a new taxonomy** — Grok. `ws_employee_standard` already does this. Redundant.

**`primary_agency` type meta-tag** — NotebookLM. Admin-adjudicatory vs investigative-only is an agency attribute, not a legal record attribute. That belongs on the `ws-agency` CPT.

**Escalation scheme multi-step field set** — Copilot. Too complex, too speculative, freetext handles it adequately.

---

## Net result

About 8 field additions, 1 option add, and roughly 12-15 new taxonomy terms across existing tables. All fit the existing pattern. None require new ACF groups or new taxonomy tables. The spec absorbs them cleanly.

Do you want to work through the exact field placements and option lists before the final spec edit, or are the accept/defer/reject calls above enough to go make the edits?