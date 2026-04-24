This is a strong, mature schema—most core whistleblower doctrine is already covered. What follows is strictly **gap analysis** against real-world litigation friction points and edge-case protections that are **not already captured** in:

* your proposal doc 
* your declared additions (anonymity, confidentiality scope, NDA/waiver scope)
* existing taxonomy tables

No redundancy, no overlap—only missing nuance that will matter in practice.

---

# **TABLE 1 — NEW FIELDS (by priority)**

| Priority | Field Name                      | Record Type / Group | Tab             | Type                                                                                    | Description                                                                                                                               |
| -------- | ------------------------------- | ------------------- | --------------- | --------------------------------------------------------------------------------------- | ----------------------------------------------------------------------------------------------------------------------------------------- |
| **HIGH** | `preemption_risk_flag`          | Substantive         | Classification  | bool                                                                                    | Flags when federal or other law may preempt or limit the protection (critical in multi-law overlap scenarios like OSHA vs. state claims). |
| **HIGH** | `preemption_details`            | Substantive         | Classification  | textarea                                                                                | Explains scope and conditions of preemption risk (e.g., exclusive federal remedy doctrines).                                              |
| **HIGH** | `forum_restrictions`            | Substantive         | Enforcement     | select (`exclusive_forum` | `concurrent` | `restricted`)                                | Whether claims must be brought in a specific forum (agency-only, federal court only, etc.).                                               |
| **HIGH** | `forum_details`                 | Substantive         | Enforcement     | textarea                                                                                | Details forum constraints, including jurisdictional traps.                                                                                |
| **HIGH** | `causation_standard_applies_to` | Substantive         | Burden of Proof | select (`liability` | `remedy_phase` | `both`)                                          | Distinguishes whether causation standard applies to liability vs. damages phase (important in mixed-motive frameworks).                   |
| **HIGH** | `temporal_proximity_recognized` | Precedent           | Burden of Proof | bool                                                                                    | Whether courts recognize timing alone as evidence of causation.                                                                           |
| **HIGH** | `temporal_proximity_details`    | Precedent           | Burden of Proof | textarea                                                                                | Explains how proximity is treated (prima facie, supporting, insufficient alone, etc.).                                                    |
| **HIGH** | `protected_disclosure_format`   | Substantive         | Classification  | multi-select (`oral` | `written` | `anonymous` | `any`)                                 | Some statutes implicitly or explicitly limit format (this matters in litigation).                                                         |
| **MED**  | `duty_to_report_flag`           | Substantive         | Classification  | bool                                                                                    | Flags statutes where employee has a legal duty to report (affects retaliation analysis).                                                  |
| **MED**  | `duty_to_report_details`        | Substantive         | Classification  | textarea                                                                                | Explains scope of mandatory reporting obligation.                                                                                         |
| **MED**  | `good_faith_definition`         | Substantive         | Classification  | textarea                                                                                | Captures statutory or case-defined meaning of “good faith” where it deviates from default assumptions.                                    |
| **MED**  | `internal_reporting_preference` | Substantive         | Classification  | select (`required` | `encouraged` | `not_required`)                                     | Captures statutes that prefer or require internal reporting before external disclosure.                                                   |
| **MED**  | `knowledge_requirement`         | Substantive         | Classification  | select (`actual_knowledge` | `reasonable_suspicion` | `no_requirement` | `has_details`) | Distinguishes thresholds beyond “reasonable belief” (important in fraud statutes).                                                        |
| **MED**  | `blacklisting_prohibited_flag`  | Substantive         | Enforcement     | bool                                                                                    | Explicitly captures whether post-employment retaliation is covered.                                                                       |
| **MED**  | `blacklisting_details`          | Substantive         | Enforcement     | textarea                                                                                | Details scope of post-employment protections.                                                                                             |
| **LOW**  | `remedy_offset_rules`           | Substantive         | Enforcement     | textarea                                                                                | Whether awards are offset by other recoveries (unemployment, settlements, etc.).                                                          |
| **LOW**  | `collateral_estoppel_effect`    | Precedent           | Relationships   | select (`binding` | `persuasive` | `none`)                                              | Whether prior administrative findings affect later litigation.                                                                            |
| **LOW**  | `statutory_conflict_flag`       | Substantive         | Classification  | bool                                                                                    | Flags when multiple statutes conflict or overlap in ways that affect protection.                                                          |
| **LOW**  | `statutory_conflict_details`    | Substantive         | Classification  | textarea                                                                                | Explanation of conflict or overlap.                                                                                                       |

---

# **TABLE 2 — CHANGES TO EXISTING FIELDS**

| Priority | Field                                                            | Record Type / Group | Tab            | Type Change         | Effect on Datapoint                                                                                                                                                   |
| -------- | ---------------------------------------------------------------- | ------------------- | -------------- | ------------------- | --------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| **HIGH** | `protected_activity_standard`                                    | Substantive         | Classification | expand enum         | Add `statutory-duty` and `mixed-standard` → captures statutes where reporting is required OR hybrid standards apply.                                                  |
| **HIGH** | `disclosure_targets`                                             | Substantive         | Classification | taxonomy refinement | Replace “government-oversight” with **two concepts**: `external-oversight-body` and `internal-oversight-body` → resolves your ombudsman issue cleanly without jargon. |
| **HIGH** | `adverse_action_scope`                                           | Substantive         | Enforcement    | expand enum         | Add `post-employment` → captures blacklisting/industry retaliation explicitly.                                                                                        |
| **HIGH** | `arbitration_waiver_enforceability` → `contractual_waiver_scope` | Substantive         | Enforcement    | expand enum         | Add: `void-public-policy`, `void-as-to-whistleblowing`, `enforceable-with-exceptions` → reflects real case law nuance.                                                |
| **MED**  | `employee_standards`                                             | Substantive         | Burden         | clarify usage       | Split conceptually: one for **protected activity threshold**, one for **causation standard** (currently conflated).                                                   |
| **MED**  | `burden_shifting_framework`                                      | Substantive         | Burden         | expand enum         | Add: `statutory-strict-liability`, `prima-facie-presumption` → captures non-traditional frameworks.                                                                   |
| **MED**  | `process_types`                                                  | Substantive         | Enforcement    | expand enum         | Add `hybrid-admin-civil` → for statutes that require admin filing but allow later civil action.                                                                       |
| **LOW**  | `proper_defendants`                                              | Substantive         | Enforcement    | no type change      | Add structured parsing (entity vs individual vs supervisor) rather than freeform text.                                                                                |
| **LOW**  | `reward_discretion_standard`                                     | Substantive         | Reward         | expand enum         | Add `formula-based` → used in some statutory reward schemes.                                                                                                          |

---

# **TABLE 3 — NEW TAXONOMY TABLES**

| Taxonomy Table                  | Concept Covered                                                                  |
| ------------------------------- | -------------------------------------------------------------------------------- |
| `ws_forum_type`                 | Where claims must be filed (agency, state court, federal court, hybrid systems). |
| `ws_preemption_type`            | Types of preemption (federal preemption, exclusive remedy, partial overlap).     |
| `ws_disclosure_format`          | Format of protected disclosures (oral, written, anonymous, etc.).                |
| `ws_reporting_obligation`       | Whether reporting is voluntary, encouraged, or mandatory.                        |
| `ws_post_employment_protection` | Scope of protections after employment ends (blacklisting, references, etc.).     |
| `ws_causation_application`      | Whether causation standard applies to liability, damages, or both.               |

---

# **TABLE 4 — NEW TERMS FOR EXISTING TAXONOMIES**

| Taxonomy                  | New Term                      | Gap It Covers                                                                          |
| ------------------------- | ----------------------------- | -------------------------------------------------------------------------------------- |
| `ws_disclosure_targets`   | `external-oversight-body`     | Covers inspectors general, ethics boards, watchdog entities without using “ombudsman.” |
| `ws_disclosure_targets`   | `internal-oversight-body`     | Covers internal audit committees, ethics officers, internal ombuds-type roles.         |
| `ws_adverse_action_types` | `post-employment-retaliation` | Explicitly captures retaliation after termination (blacklisting, references).          |
| `ws_adverse_action_types` | `investigation-interference`  | Covers obstruction, intimidation, or chilling during investigations.                   |
| `ws_process_type`         | `hybrid-admin-civil`          | Captures systems requiring admin filing before civil litigation.                       |
| `ws_remedies`             | `declaratory-relief`          | Courts declaring rights without monetary damages.                                      |
| `ws_remedies`             | `emotional-distress-damages`  | Often awarded but not always clearly categorized under compensatory.                   |
| `ws_remedies`             | `reputational-harm-damages`   | Distinct from general compensatory in some rulings.                                    |
| `ws_employer_defense`     | `after-acquired-evidence`     | Important defense limiting remedies after misconduct discovered.                       |
| `ws_employer_defense`     | `same-actor-inference`        | Common judicial inference in employment cases.                                         |
| `ws_employee_standard`    | `prima-facie-inference`       | Used where initial showing creates presumption without full burden shift.              |
| `ws_fee_shifting`         | `asymmetric-discretionary`    | Court discretion but functionally favors one side (common in practice).                |

---

# **Key Takeaways (straight talk)**

You’ve already built ~90–95% of what most legal systems miss.

What was missing falls into **four real-world failure zones**:

1. **Forum + procedural traps**
   → where cases die before merits (forum_restrictions, preemption)

2. **Causation nuance (litigation reality)**
   → liability vs damages, temporal proximity, burden splits

3. **Edge-case protections**
   → disclosure format, duty to report, internal preference rules

4. **Post-employment and structural retaliation**
   → blacklisting, investigation interference

---

You’re right to pause here—this is exactly the kind of term that quietly breaks usability if it slips through unexamined.

## What “estoppel” actually means (plain English)

At its core, **estoppel** means:

> *You’re not allowed to argue something now because your earlier actions, statements, or a prior decision already settled that issue.*

In your context (whistleblower law), the relevant version is usually:

* **Collateral estoppel (issue preclusion)**
  → If an issue was already decided (for example, by an agency or court), you **can’t re-litigate that same issue** in a new case.

### `prior_decision_effect`

**Why this works:**

* Plain English
* Covers both directions (binding or not)
* Flexible across admin + court decisions
* No legal jargon leakage

**Example meaning:**

> “Does a prior decision affect this claim?”

---

### Field

```
prior_decision_effect
```

### Type

```
select:
  - binding
  - persuasive
  - none
  - mixed
```

### Companion

```
prior_decision_details (textarea)
```

### What it captures

* Whether earlier findings:

  * Must be followed (binding)
  * Can influence (persuasive)
  * Don’t matter (none)
  * Depend on context (mixed)

---

# Recommended Final Naming Set

If I were locking this system:

| Concept                | Field Name                                                     |
| ---------------------- | -------------------------------------------------------------- |
| Causation standard     | `retaliation_connection_standard`                              |
| Where it applies       | `causation_application` *(or your `ws_causation_application`)* |
| Prior decisions effect | `prior_decision_effect`                                        |

---

# Why this matters (zoomed out)

You’re building something most legal systems get wrong:

* Lawyers optimize for **precision**
* You’re optimizing for **correct use by non-lawyers**

That means:

* 95% accuracy + usability > 100% doctrinal purity

Replacing “estoppel” with something like `prior_decision_effect` is exactly the kind of decision that makes your system *actually usable in the wild*.

---

is_en_banc is fine to keep. It is legal jargon, but it lives in the construction layer, where the editor is already closer to source-law handling. The triggered field does enough work.
I’d just make the ACF instruction very plain:

En banc means the full court, or a larger authorized group of judges, decided the matter instead of a smaller panel. Leave this checked unless the construction was issued by a panel.

One small naming thought: if this field defaults true, is_en_banc is a little odd because most appellate decisions are not en banc. But for construction records, if your object represents a formal court-level construction rather than an ordinary citation, the default may make sense.
A slightly plainer alternative would be:
ruling_body_type
with choices:
en_banc|panel|other
But I would not force that change. is_en_banc + panel_composition_details is compact and acceptable.

