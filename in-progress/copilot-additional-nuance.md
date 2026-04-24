Perfect catch! Let me re-evaluate the legal nuances **against the actual taxonomies** you've already built:

## Revised Assessment: Legal Gaps NOT Covered By Existing Taxonomies

### ✅ Already Captured (via taxonomies):  
- **Disclosure types** (`ws_disclosure_type`) — hierarchical, well-scoped  
- **Process types** (`ws_process_type`) — administrative, civil, qui tam, etc.  
- **Remedies** (`ws_remedy`) — broad remedies catalog with limits sentinel  
- **Adverse actions** (`ws_adverse_action`) — comprehensive retaliation forms  
- **Employer defenses** (`ws_employer_defense`) — same-decision, LNRR, etc.  
- **Employee burden standards** (`ws_employee_standard`) — contributing-factor to but-for  
- **Fee-shifting types** (`ws_fee_shifting_type`) — bilateral, unilateral, discretionary

---

### ⚠️ Still Missing Legal Nuances:

#### 1. **Retaliation Causation Standard** (DISTINCT from burden of proof)  
Your `ws_employee_standard` covers *burden of proof* (preponderance, clear-and-convincing), but NOT the *causation standard* for retaliation itself:  
- **But-for causation** vs. **"any consideration"** causation  
- **Temporal proximity** rules (how soon after disclosure does retaliation need to occur?)  
- **Presumption of retaliation** (some statutes presume retaliation if adverse action within X days of disclosure)

**Recommendation:** Add a new `ws_retaliation_causation` taxonomy or expand `ws_employee_standard` with causation-specific terms like `presumed-retaliation-90-days`, `temporal-proximity-required`.

---

#### 2. **Private Right of Action** (Binary)  
Your `ws_process_type` lists enforcement *channels*, but doesn't capture whether a **private right of action exists**. Some statutes allow only:  
- Administrative complaints (no private lawsuit)  
- Regulatory tips (agency-only)  
- No individual private enforcement at all

**Recommendation:** Add a boolean ACF field `has_private_right_of_action` to legal records (not taxonomy — this is a gate/constraint, not a multi-select).

---

#### 3. **Discovery Rule vs. Accrual Rule** (SOL mechanics)  
Your SOL fields exist (`sol_value`, `sol_unit`, `sol_trigger`), but `sol_trigger` likely doesn't distinguish:  
- **Discovery rule** — SOL starts when employee discovers (or should have discovered) the retaliation  
- **Accrual rule** — SOL starts when adverse action occurs  
- **Relation-back doctrine** — amended complaints can relate back to original filing

**Recommendation:** Expand `sol_trigger` taxonomy or create `ws_sol_trigger_type` with terms: `accrual`, `discovery`, `mixed`, `relation-back-applicable`.

---

#### 4. **Adverse Action Specificity** (Narrow vs. Broad Definition)  
Your `ws_adverse_action` taxonomy lists *types* (termination, demotion, etc.), but many statutes define "adverse action" **narrowly** in the statute itself:  
- "Termination or suspension only" (excludes demotion, discipline)  
- "Material adverse change" (undefined but narrow)  
- "Any adverse action" (broadest)

**Recommendation:** Add an ACF field `adverse_action_definitional_scope` (single-select): `termination-only` | `material-adverse` | `broad-any-adverse-action` | `has-details`.

---

#### 5. **Class Action / Collective Action Preclusion**  
Many modern statutes ban or restrict class actions / collective bargaining claims. Your taxonomies don't capture this.

**Recommendation:** Add a boolean ACF field `class_action_waiver` or `collective_action_preclusion` to legal records.

---

#### 6. **Exhaustion vs. Administrative Escalation** (Sequential Requirement)  
You have `has_exhaustion_requirement` but may conflate:  
- **Informal exhaustion** — must try to resolve internally first  
- **Administrative prerequisite** — must file with agency before civil court  
- **Mandatory escalation** — must appeal within agency before private suit

**Recommendation:** Rename or add `ws_exhaustion_type` taxonomy: `informal` | `administrative-prerequisite` | `appellate-escalation` | `mixed`.

---

#### 7. **Arbitration Enforceability Nuance** (Already has sentinels, but needs inverse)**  
You have `arbitration_waiver_enforceability` (void/limited/enforceable/mixed). But some statutes:  
- **Require arbitration** (mandate it)  
- **Prohibit arbitration** (void all arbitration clauses)

**Recommendation:** Clarify the taxonomy or add inverse: `arbitration_requirement_status` (if not already covered by `arbitration_waiver_enforceability` logic).

---

#### 8. **Fee Conditionality Beyond Phases** (You have `fee_shifting_phases` — good!)  
But `ws_fee_shifting_type` doesn't distinguish:  
- **Fees only if nominal damages** (prevailing party must get >$X)  
- **Fees capped** (e.g., not to exceed 50% of recovery)  
- **Fees conditional on settlement** (fee-shifting disappears if case settles vs. judgment)

**Recommendation:** Add or leverage `fee_shifting_phases` + `fee_shifting_details` more explicitly with a `ws_fee_shifting_condition` taxonomy or enhance `fee_shifting_details` field guidance.

---

#### 9. **Damages Liability (Individual vs. Corporate; Personal Liability)**  
Your `ws_remedy` lists remedies but doesn't capture:  
- **Individual liability** — can supervisors/officers be personally sued?  
- **Corporate liability only** — shield individuals  
- **Scope of employment** requirement

**Recommendation:** You have `individual_liability_available` (boolean) — good! But ensure it's populated consistently.

---

#### 10. **Whistleblower Definition Circularity** (Temporal/Nested)**  
Some statutes have:  
- **Nested disclosure requirement** — must disclose internally *first* before external protection applies  
- **Good-faith belief** variant — employee protected for *believed* violation (not just actual)  
- **Timing-dependent** — protection only if disclosure made within X months of learning of violation

**Recommendation:** Your `disclosure_types` taxonomy is semantic (what's disclosed). Add a `ws_disclosure_timing_requirement` taxonomy or ACF field to capture: `internal-first` | `good-faith-belief` | `contemporaneous` | `has-details`.

---

#### 11. **Sovereign Immunity Limits** (Policy-Level Constraint)  
You have `sovereign_immunity_limits` as an ACF field. Good! But ensure it captures:  
- **Liability cap** on state/federal employers  
- **Waiver specificity** — limited to specific statutes only  
- **Exception clauses** — immunity waived *only if* certain conditions met

---

#### 12. **Reward / Qui Tam Discretion Nuance**  
You have `reward_discretion_standard` (mandatory/discretionary/presumptive/has-details). But missing:  
- **Reward phase** — does reward apply pre-settlement or only post-judgment?  
- **Reward reduction** — can agency reduce reward if employee had other motives?  
- **Reward threshold** — only if recovery exceeds $X?

**Recommendation:** Expand `reward_discretion_details` guidance or add `ws_reward_trigger_condition` taxonomy.

---

## Priority Additions (Rank by Legal Impact):

| Priority | Field | Type | Rationale |  
|----------|-------|------|-----------|  
| **HIGH** | `has_private_right_of_action` | Boolean ACF | Gate-level: no private suit means entire workflow changes |  
| **HIGH** | `retaliation_causation_standard` | Taxonomy or ACF | Legally distinct from burden of proof; changes pleading standard |  
| **MEDIUM** | `adverse_action_definitional_scope` | Single-select ACF | Narrows what counts as retaliation; critical for case assessment |  
| **MEDIUM** | `discovery_rule_vs_accrual` | Taxonomy | SOL mechanics—high impact on statute expiration |  
| **MEDIUM** | `class_action_waiver` | Boolean ACF | Game-changer for representative claims |  
| **LOW** | `administrative_escalation_required` | Taxonomy (refine exhaustion) | Procedural nuance; filters remedies available |  
| **LOW** | `disclosure_timing_requirement` | Taxonomy | Semantic edge case; most already in disclosure_types |

---

Should I draft field names and seeding logic for any of these?

Got it! You're right — you already have **`has_amended_claim_recognized`** at line 72. That captures the relation-back doctrine concept (the **legal recognizability** of amended claims). Good naming.

So the SOL mechanics are actually **already well-captured**:  
- `sol_trigger` — *when* the clock starts (accrual vs. discovery)  
- `has_amended_claim_recognized` — *whether* amended claims can relate back  
- `amended_claim_details` — specifics of any limitations

---

## Refined Legal Gaps (Re-evaluated):

Given that amended claims are covered, here are the **actual remaining nuances**:

### **Still Missing (High Priority):**

1. **`has_private_right_of_action`** — Boolean  
   - Filters entire enforcement pipeline  
   - Many statutes allow only admin complaints, not civil suits

2. **`retaliation_causation_standard`** — NEW taxonomy  
   - Distinct from `employee_standards` (which is burden-of-proof)  
   - Terms: `but-for` | `any-consideration` | `temporal-proximity-required` | `presumed-retaliation-within-X-days`  
   - **Why separate:** A statute can have "preponderance of evidence" (BOP) but "but-for causation" (causation), or vice versa

3. **`adverse_action_definitional_scope`** — Single-select ACF  
   - `termination-only` | `material-adverse-change` | `broad-any-adverse` | `has-details`  
   - Narrows what triggers protection (not all retaliation statutes cover demotion, discipline, etc.)

4. **`class_action_waiver`** — Boolean  
   - Game-changer for collective/representative claims  
   - Missing from current schema

5. **`sol_trigger_type`** — Expand/clarify taxonomy  
   - Your `sol_trigger` is likely freetext; needs explicit terms:  
   - `accrual` | `discovery` | `discovery-with-due-diligence` | `mixed`

---

### **Lower Priority (Already Partially Covered or Edge Cases):**

- ✅ `amended_claim_recognized` — **You have this**  
- ✅ `exhaustion_is_jurisdictional` — **You have this**  
- ✅ `individual_liability_available` — **You have this**  
- ✅ `arbitration_waiver_enforceability` — **You have this**  
- ⚠️ `fee_shifting_conditionality` — Covered by `fee_shifting_details` + `fee_shifting_phases` (could enhance field guidance)

---

## Suggested Additions to Your Draft:

Add to **Statute of Limitations And Thresholds Tab**:  
```markdown  
- `sol_trigger_type`       — (single-select: `accrual`|`discovery`|`discovery-due-diligence`|`mixed`)  
```

Add to **Classification Tab**:  
```markdown  
- `retaliation_causation_standard`  — (multi-select via new taxonomy; distinct from employee burden standard)  
- `adverse_action_definitional_scope` — (single-select: `termination-only`|`material-adverse`|`broad-any-adverse`|`has-details`)  
```

Add to **Identity And Publishing Tab**:  
```markdown  
- `has_private_right_of_action`  — (boolean; critical gate for enforcement pathway)  
```

Add to **Enforcement Tab**:  
```markdown  
- `class_action_waiver`  — (single-select: `prohibited`|`permitted-individual-only`|`permitted-collective`|`mixed`)  
```

---

**Should I draft the taxonomy seeding functions for `ws_retaliation_causation_standard` and `ws_class_action_scope`?**