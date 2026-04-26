This is the right moment to do this check—because what’s left now isn’t structure, it’s **litigation-grade nuance leakage**. And at your current level, the gaps are subtle but important.

I’m going to stay disciplined to your instruction:

* **No overlap** with what already exists in your ACF 
* **No overlap** with your declared additions
* **No overlap** with existing taxonomy tables
* Only **true legal nuance gaps that affect outcomes**

---

# **FINAL NUANCE GAPS (No Tables — just the signal)**

These are the *last-mile* issues that could cause a user to misunderstand their protection **even if everything else is correct**.

---

## **1. “Protected disclosure timing” is not fully captured**

You capture:

* disclosure types
* causation
* adverse action

**But you do NOT capture:**

> Whether protection exists *before*, *during*, or *after* disclosure

### Why this matters

* Many laws protect:

  * **attempted disclosure**
  * **preparatory steps**
  * **refusal to participate**
* Others protect only **completed disclosures**

### Gap

You have no way to distinguish:

* “I was fired before I reported”
  vs
* “I reported and then was fired”

### What’s missing conceptually

**Temporal scope of protected activity**

---

## **2. “Refusal vs reporting” is under-modeled**

You have:

* `protected_actions`
* `disclosure_types`

But in whistleblower law:

> **Refusal to engage in wrongdoing** is often protected **even without disclosure**

### Why it matters

* Huge real-world scenario:

  > “I refused to falsify records and got fired”

This is NOT always treated the same as reporting.

### Gap

You cannot cleanly distinguish:

* disclosure-based protection
* refusal-based protection
* participation-based protection (internal investigations)

---

## **3. “Knowledge of employer” is missing**

Critical nuance:

> The employer must usually **know or suspect** the protected activity

### Why it matters

* If employer didn’t know → no retaliation claim (in most frameworks)
* Some statutes allow inference
* Some require explicit knowledge

### Gap

No field captures:

* whether employer knowledge is required
* how it can be inferred

---

## **4. “Third-party retaliation” is not captured**

Modern doctrine includes:

> Retaliation against **associates** of the whistleblower

Examples:

* spouse fired
* colleague punished
* references sabotaged

### Why it matters

* Recognized in federal law (e.g., *Thompson v. North American Stainless*)
* Appears in multiple statutes

### Gap

No structural way to represent:

* retaliation not directed at the whistleblower themselves

---

## **5. “Scope of damages causation vs liability causation” is only partially solved**

You correctly split causation standards 👍

But missing nuance:

> Some laws apply different causation standards to:

* **liability**
* **remedies (damages phase)**

### Example

* contributing factor → liability
* but-for → damages

### Gap

You have:

* `ws_causation_standard`
* `ws_causation_application` (conceptually)

But you don’t explicitly tie:

* which standard applies to which phase **within the same record**

---

## **6. “Chilling effect / deterrence retaliation” is missing**

Some statutes protect against:

> Actions that would **deter a reasonable person from reporting**

Even if:

* no formal adverse action occurred

### Why it matters

* This is broader than “material adverse action”
* Often appears in federal frameworks

### Gap

Your model assumes retaliation = discrete action
You don’t capture **deterrence-based standards**

---

## **7. “Good faith vs accuracy” distinction is not fully captured**

You have:

* `reasonable_belief`
* `good_faith`

But missing nuance:

> Some laws protect:

* **good faith but wrong**
  Others require:
* **objectively reasonable AND correct-ish**

### Gap

You don’t capture:

* whether **truth of disclosure matters**
* whether protection survives incorrect reporting

---

## **8. “Parallel claims interaction” (stacking vs exclusivity)**

You partially cover:

* election of remedies
* preclusion

But missing nuance:

> Can a user bring:

* multiple whistleblower claims simultaneously?
* or must they choose one?

### Why it matters

* This is where cases are lost procedurally

### Gap

You don’t explicitly capture:

* **stackable vs exclusive claim structure**

---

## **9. “Interim relief availability” (URGENT real-world gap)**

This one matters a lot for Persona B (retaliation victims):

> Can the worker get **temporary relief while the case is pending?**

Examples:

* preliminary reinstatement
* injunctions

You partially capture:

* remedies

But not:

> **timing of relief**

### Gap

No distinction between:

* final remedies
* interim protections

---

## **10. “Standard of review” on appeal (precedent layer gap)**

For citations/constructions:

> How courts review agency or lower decisions:

* de novo
* substantial evidence
* arbitrary & capricious

### Why it matters

* Defines how strong a prior decision really is

### Gap

You have:

* binding_scope
* parent_weight

But not:

* **review standard**

---

# **What this means (important)**

You are NOT missing structure.

You are missing:

### **“Edge-case legal behavior modeling”**

The kind that only shows up when:

* cases are litigated
* defenses are raised
* procedural traps hit

---

# **If I compress everything into one sentence**

Your system perfectly models:

> “What protections exist”

But still has small gaps in:

> **“How those protections actually behave under stress in real litigation”**

---

# **If you only fix 3 things**

If you want maximum impact with minimal expansion:

1. **Protected activity timing (before / during / after disclosure)**
2. **Employer knowledge requirement**
3. **Interim relief availability**

Those three alone will dramatically increase real-world accuracy for users.

---

Here’s a clean proposal doc aligned to your system, naming conventions, and philosophy.

---

# WhistleblowerShield — Phase X Proposal
## Final Nuance Coverage Expansion

**Status:** Draft  
**Author:** System Architecture  
**Date:** 2026-04-26  
**Scope:** Legal Record ACF + Taxonomy Completeness  
**Applies To:** `statute`, `common_law`, `citation`, `construction`  

---

## Purpose

This proposal addresses **remaining legal nuance gaps** in the WhistleblowerShield data model after completion of:

- Canonical ACF schema
- Existing taxonomy tables
- Declared additions:
  - `has_anonymity_protection`
  - `confidentiality_scope`
  - NDA / contractual waiver expansion

The goal is not expansion for completeness alone, but to ensure:

> The system accurately reflects how whistleblower protections behave under real litigation conditions.

---

## Design Principles

- Maintain **plain-English editorial usability**
- Avoid introducing legal jargon into field names
- Prefer **behavioral modeling** over doctrinal labeling
- Preserve **taxonomy discipline** (no unnecessary proliferation)
- Extend existing structures before introducing new ones

---

## Identified Gap Categories

The remaining gaps fall into four categories:

1. **Temporal Scope of Protection**
2. **Causation & Knowledge Mechanics**
3. **Retaliation Surface Expansion**
4. **Procedural & Litigation Behavior**

---

## PROPOSED ADDITIONS

---

# 1. Protected Activity Timing

## Problem

The system does not distinguish whether protection applies:

- Before disclosure
- During disclosure
- After disclosure

This creates ambiguity in real-world scenarios such as:

> “I was fired before I reported.”

## Proposal

### New Field

**Tab:** Classification  
**Field:** `protected_activity_timing`  
**Type:** multi-select

```text
pre-disclosure
during-disclosure
post-disclosure
continuous
has-details
````

### Companion

```text
protected_activity_timing_details
```

---

# 2. Refusal vs Reporting Distinction

## Problem

Refusal to participate in wrongdoing is not cleanly separated from disclosure.

## Proposal

### New Taxonomy Table

**`ws_protected_activity_mode`**

Concept: Mode of protected conduct

```text
disclosure
refusal-to-participate
internal-participation
investigation-cooperation
preparatory-activity
has-details
```

### New Field

**Tab:** Classification
**Field:** `protected_activity_modes`
**Type:** taxonomy (`ws_protected_activity_mode`)

---

# 3. Employer Knowledge Requirement

## Problem

Most retaliation claims require employer awareness, but this is not modeled.

## Proposal

### New Field

**Tab:** Burden Of Proof
**Field:** `employer_awareness_requirement`
**Type:** select

```text
actual-knowledge
constructive-knowledge
inferred-knowledge
no-requirement
has-details
```

### Companion

```text
employer_awareness_details
```

---

# 4. Third-Party Retaliation

## Problem

Retaliation against associates is not captured.

## Proposal

### New Field

**Tab:** Enforcement
**Field:** `third_party_retaliation`
**Type:** bool

### Companion

```text
third_party_retaliation_details
```

---

# 5. Causation Phase Application

## Problem

Causation standards may apply differently to:

* Liability
* Damages

## Proposal

### New Field

**Tab:** Burden Of Proof
**Field:** `causation_application`
**Type:** multi-select

```text
liability
damages
both
has-details
```

### Companion

```text
causation_application_details
```

---

# 6. Deterrence / Chilling Standard

## Problem

Some laws protect against conduct that would deter reporting, even without formal adverse action.

## Proposal

### New Field

**Tab:** Enforcement
**Field:** `deterrence_standard`
**Type:** bool

### Companion

```text
deterrence_standard_details
```

---

# 7. Truth vs Good Faith Distinction

## Problem

The system does not distinguish whether protection survives incorrect disclosures.

## Proposal

### New Field

**Tab:** Classification
**Field:** `truth_requirement`
**Type:** select

```text
truth-required
reasonable-belief-sufficient
good-faith-sufficient
mixed
has-details
```

### Companion

```text
truth_requirement_details
```

---

# 8. Parallel Claims / Exclusivity

## Problem

Interaction between multiple claims is not fully modeled.

## Proposal

### New Field

**Tab:** Enforcement
**Field:** `claim_interaction_rule`
**Type:** select

```text
stackable
exclusive
election-required
mixed
has-details
```

### Companion

```text
claim_interaction_details
```

---

# 9. Interim Relief Availability

## Problem

Timing of relief is not distinguished from final remedies.

## Proposal

### New Field

**Tab:** Enforcement
**Field:** `interim_relief_available`
**Type:** bool

### Companion

```text
interim_relief_details
```

---

# 10. Standard of Review (Precedent Only)

## Problem

The weight of precedent is incomplete without review standard.

## Proposal

### New Field (Precedent Records Only)

**Tab:** Classification
**Field:** `review_standard`
**Type:** select

```text
de-novo
substantial-evidence
arbitrary-capricious
abuse-of-discretion
mixed
has-details
```

### Companion

```text
review_standard_details
```

---

## TAXONOMY ADDITIONS

### New Table

#### `ws_protected_activity_mode`

**Concept:** Type of protected conduct

---

## SUMMARY OF IMPACT

### What This Fixes

* Clarifies **when protection begins**
* Distinguishes **types of protected conduct**
* Captures **litigation-critical causation mechanics**
* Expands **retaliation beyond termination**
* Models **procedural realities users actually face**

---

## PRIORITY ORDER

### Phase 1 (Critical)

* protected_activity_timing
* employer_awareness_requirement
* interim_relief_available

### Phase 2 (High Value)

* protected_activity_modes
* causation_application
* third_party_retaliation

### Phase 3 (Refinement)

* deterrence_standard
* truth_requirement
* claim_interaction_rule
* review_standard

---

## FINAL NOTE

This proposal does not expand the system for theoretical completeness.

It closes the gap between:

> “What the law says”

and

> **“What actually happens when a whistleblower relies on it.”**

