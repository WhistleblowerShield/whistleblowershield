***

## Overall Assessment

The schema is impressively thorough for a solo-dev rebuild. The field design is clean, the naming conventions are consistent, and the taxonomy architecture is structurally sound. But whistleblower protection law has some genuinely tricky corners that this draft doesn't yet model well. Here's what's missing or underspecified, organized by severity. 

***

## Critical Gaps

### 1. The "Protected Activity Standard" Three-Way Is Too Thin

The `protected_activity_standard` single-select (`actual_violation` | `reasonable_belief` | `good_faith`) collapses a legally critical distinction. **"Reasonable belief" itself has two separate sub-tests** in federal law: an *objective* prong (would a reasonable person believe a violation occurred?) and a *subjective* prong (did *this person* actually believe it?). Some statutes require both; some only one. *Leshinsky v. Telvent GIT* and the SEC whistleblower program post-*Dodd-Frank* diverge on this exact point.  

**Suggestion:** Add `reasonable_belief_test` as a subordinate field: `objective_only` | `subjective_only` | `dual_prong` | `has_details`. Without it, the analyst-side prompt generator will produce dangerously oversimplified output.

***

### 2. "Anonymous / Confidentiality" Protections Are Absent

There is no field capturing whether a statute *protects the identity of the disclosing party* — covering anonymity rights, confidentiality of investigative files, or non-retaliation for anonymous tips. This matters enormously: the SEC and CFTC programs have explicit anonymity pipelines through counsel; some state statutes protect identity in investigations but not in litigation. This is a first-pass issue on any WB protection schema. 

**Suggestion:** Add to Classification Tab: `has_anonymity_protection` (bool) + `anonymity_details`. Also consider `confidentiality_scope` taxonomy or field: `tip_only` | `investigation_and_tip` | `full` | `has_details`.

***

### 3. Constructive Discharge Is Listed as an `adverse_action` Term, But Has Its Own Legal Test 

`constructive-discharge` sits in `ws_adverse_action` as a flat term — but constructive discharge is not simply a type of adverse action. It has a distinct legal standard (*Pennsylvania State Police v. Suders*): the working conditions must be "so intolerable that a reasonable person would have felt compelled to resign." Some WB statutes recognize it; some explicitly don't; some are silent and courts split. Treating it as a peer term to "demotion" erases that complexity. 

**Suggestion:** Either make `ws_adverse_action` hierarchical (with `constructive-discharge` as a child requiring `adverse_action_scope` = `has_details`), or add a dedicated ACF flag `has_constructive_discharge_recognized` (bool) + `constructive_discharge_details` in the Enforcement Tab.

***

## Significant Missing Nuances

### 4. Pre-Filing Procedural Exhaustion vs. Jurisdictional vs. Claims-Processing

The schema has `has_exhaustion_requirement` + `exhaustion_is_jurisdictional` — good start, but it misses a third category that *SCOTUS* has been actively carving out: **claims-processing rules** (*Fort Bend County v. Davis*, 2019). A claims-processing rule can be waived by the defendant; a jurisdictional bar cannot. For WB statutes that require filing with an agency before suit (OSHA, EEOC, state equivalents), this distinction is outcome-determinative. 

**Suggestion:** Expand `exhaustion_is_jurisdictional` to a three-option field or add `exhaustion_rule_type`: `jurisdictional` | `claims-processing` | `waivable` | `mixed` | `has_details`.

***

### 5. Preemption / Field Preemption Field Is Missing

No field tracks whether a federal statute **preempts** a corresponding state WB claim, or whether state claims are *not* preempted and may be pled concurrently. *Geier v. American Honda*, the NLRA's preemption scope, ERISA Section 514 — these are common traps. A user might believe they have a state retaliatory discharge claim when ERISA (or the NLRA) has displaced it entirely. 

**Suggestion:** Add to Enforcement Tab or Relationships Tab: `has_preemption_flag` (bool) + `preemption_details`, and ideally `preemption_direction`: `federal_preempts_state` | `state_not_preempted` | `partial` | `unclear`.

***

### 6. "Mixed Motive" Is on the Burden Framework But Not the Remedy Side

The `burden_shifting_framework` select includes `mixed-motive`, but the remedy consequence of mixed-motive findings isn't modeled. Under *Price Waterhouse* and statutes that follow it, if the employer proves it would have taken the same action anyway, **remedies may be limited to declaratory relief and injunctions — no reinstatement, no back pay**. This is not captured in `remedy_limits` as-is. 

**Suggestion:** Add `mixed_motive_remedy_limit` (bool) + `mixed_motive_remedy_details` to the Enforcement Tab, or document it as a required fill target in `remedy_limits` when `burden_shifting_framework` = `mixed-motive`.

***

### 7. Qui Tam / Relator Status Is Modeled in `process_types` But Not in Protected Class

`qui-tam` appears as a process type term in `ws_process_type`, but **qui tam relators have a distinct legal identity** — they're not just "employees." They may be former employees, competitors, contractors, or even outsiders with knowledge. Under FCA, relator status itself confers standing and partial ownership of the action. The `ws_protected_class` taxonomy has no term for `relator` or `qui-tam-relator`. 

**Suggestion:** Add `qui-tam-relator` to `ws_protected_class` under `special-status`, and consider a `relator_share_range` field on the Reward Tab when `process_types` includes `qui-tam`.

***

### 8. The `ws_protected_action` Seed Terms Are Dangerously Sparse 

The `ws_protected_action` taxonomy seeds only four terms: `attempted-reporting`, `participation-support`, `refusal-to-participate`, `testifying`. This misses a substantial number of legally recognized protected actions: 

- **Filing or initiating a complaint** (distinct from reporting)
- **Assisting another whistleblower** (protected under Title VII and many state statutes)
- **Opposing a practice** (the "opposition clause" distinct from the "participation clause" in Title VII-analog statutes)
- **Requesting an accommodation** (protected under ADA, FMLA retaliation)
- **Engaging in concerted activity** (NLRA Section 7 protection, legally distinct from whistleblowing)
- **Objecting internally** (protected under some statutes even without external disclosure)

The opposition/participation distinction is particularly important — courts treat the two clauses differently on causation standards and good-faith requirements.

**Suggestion:** Expand the seed terms, and make `ws_protected_action` hierarchical with `opposition` and `participation` as parent terms.

***

### 9. The `ws_protection_scope` (already corrected)

***

### 10. No Field for "Confidential Settlement / NDA Enforceability"

Post-*#MeToo*, several state statutes (California SB 331, New York, Illinois) and the federal SPEAK OUT Act explicitly void or limit NDAs that cover WB disclosures. There is no field tracking whether an NDA or confidential settlement agreement can bar a future disclosure or retaliation claim. This is increasingly litigated. 

**Suggestion:** Add to Enforcement Tab: `has_nda_limitation` (bool) + `nda_limitation_details`, or extend `arbitration_waiver_enforceability` to cover settlement agreements (and rename accordingly).

***

### 11. "Chilling Effect" Doctrine / Anticipated Retaliation Not Modeled

Several statutes and significant case law recognize that **anticipated or threatened retaliation** — even before an adverse action occurs — may support a claim (sometimes called "chilling effect" standing). `threatened-retaliation` exists as an adverse action term, but there's no field flagging whether a statute explicitly protects against anticipatory or pre-emptive retaliation (i.e., a claim without a completed adverse action). 

**Suggestion:** Add `has_anticipatory_retaliation_recognized` (bool) + `anticipatory_details` to the Enforcement Tab.

***
