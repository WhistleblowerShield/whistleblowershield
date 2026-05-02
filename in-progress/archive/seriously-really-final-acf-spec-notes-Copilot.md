Here’s a direct legal-nuance gap analysis between the **ACF canonical field spec** and the **taxonomy registry** (plus the implied/expressed domain model), focused on whistleblower law edge cases. I am not repeating “add more fields” for abstract concepts, but surfacing where the **field model** may be missing material legal or editorial distinctions in U.S. whistleblower/retaliation/qui tam doctrine.

---
## Legal Nuance Gaps & Model Tensions

### 1. Protected Activity & Scope Gaps

**a. Internal vs. External Channels**  
- There is a taxonomy (`ws_disclosure_target`) for "internal" vs. "external" but the field model (see `disclosure_channel_scope`) only allows three scopes and lacks fields for conditional logic:
  - Was disclosure only protected if made “internally first” and then “externally if not resolved” (typical in Dodd-Frank, SOX, etc)?  
  - No apparent place to represent multi-step escalation (e.g., internal → regulator → media) and how scope changes.

**b. Good-Faith Standard Specification**  
- The field `protected_action_standard` attempts to collapse what’s often treated distinctly:
  - "Objective" (reasonable belief), "Subjective" (good-faith), or "Dual-prong" is a valuable axis, but sometimes the true test *differs for different kinds of disclosure* (e.g., opposition vs participation).
  - No way to express that "internal objections" get a different test than "external regulatory complaints" (Title VII split).

**c. “Participatory” Actions (e.g., Testimony Reprisal)**  
- `ws_protected_action` includes “participation,” but the effect of, for example, retaliation for “assisting another’s claim” is sometimes doctrinally different or has a different BOP/causation standard. The model does not distinguish if *protection is broader for participatory acts*.

### 2. Statute of Limitations & “Continuing Violation” Doctrine

- There’s an enumeration for “continuing-violation” in the legal recognitions, but **no dedicated fields for “tolling trigger” variants**, e.g.:
  - “Last adverse act” vs. “first act” vs. “discovery of pattern” as trigger.
  - Some anti-retaliation laws treat wage claims as separately accruing per paycheck, whistleblowing as single cause—here, only one `sol_trigger` field.
- **Lack of fields to model tolling for equitable estoppel, fraudulent concealment, or disability tolling**, which are *sometimes provided for* in employment/False Claims/retaliation.

### 3. Retaliation Recognition: “Constructive” and “Anticipatory” Subtleties

- There's a field for constructive discharge, but not for the *degree* of adversity required:
  - Some standards demand “materially adverse” in “reasonable employee’s eyes,” others any negative change; the model only has three broad choices.
- **No way to indicate if protected against “threatened” retaliatory acts** (e.g., retaliation for *attempted* disclosure).
- The "anticipatory retaliation" field is present but doesn't capture *when* anticipation triggers protection (before, during, or after actual whistleblowing).

### 4. Enforcement & Judicial Review Gaps

**a. Scope of Judicial Review**  
- Under “review_standard” only the most generic choices exist, but in some domains (MSPB, federal sector), the scope of record creation and factfinding, and the standard (substantial evidence, arbitrary & capricious, etc.) is a crucial distinction, and sometimes the court’s review is limited to questions of law.

**b. Relationship to Arbitration**  
- There is a field for “arbitration compelled” in process type, but no companion to reflect:
  - If statutes void waivers of public law rights in arbitration, or if compulsory arbitration is permitted for some claims but not others. Current model can’t surface partial-voiding/rules.

### 5. Defense & Burden of Proof Interplay

**a. Same-Decision Defense & Remedies Modulation**  
- There is a field for “same-decision defense,” but not for *remedy-limiting effect* — e.g., mixed-motive finding often strips remedies to “back pay only” or “declaratory/injunctive only.”  
- No explicit field for “remedy restriction on partial defense” (cf. 42 USC 2000e-5(g)(2)(B)).

**b. Presumptions and Inference**  
- There are fields for “rebuttable presumption” and “presumption window,” but in doctrine (especially anti-retaliation), there are:
  - Shifting presumptions (e.g., timing within X days → presumption for plaintiff, but can be rebutted by employer for “legitimate reason”), *or* loss of presumption if not raised within a time window.
  - “Temporal proximity” as a separate field or doctrinal axis, *distinct from formal burden shifting*.

### 6. Class Actions, Collective Actions, Aggregation

- There’s a legal recognition for “class-action” but not for:
  - Whether opt-in or opt-out procedures apply (FLSA vs. Title VII),
  - If anti-retaliation provision provides collective relief or only individual (especially important post-Epic Systems Corp. v. Lewis).

### 7. Relief & Damages Gaps

- The “remedies” taxonomy is flat but cannot handle cases where punitive damages are **capped by statute**, or emotional distress is noncognizable.
- There is a `remedy_limits` field, but no explicit link to “cap applies” or companion for “statutory maximum,” “statutory floor,” “prohibits make-whole,” etc.

### 8. Scope of Covered Employer/Joint Employer Liability

- There is a companion for “proper defendants,” but the taxonomy structure doesn't allow for:
  - Modulating “joint employer” liability versus “contractors included” — in some statutes, contractors are covered as direct employers, elsewhere only when acting in concert.
  - No field to indicate “successor employer” liability doctrine [can be an issue for “piercing the corporate veil” or “acquisition/merger liable for old acts”].

### 9. Waivers, Releases & Foreclosure

- “Civil action waiver”/“contractual waiver” does not distinguish between waivers before the fact (ex ante) and after-the-fact (ex post, in settlement). Laws differ on whether post-dispute waivers are effective.

### 10. Interstitial Preemption & State Law Overlay

- “Preemption” is modeled, but there is no concept of:
  - Areas where *state* provides only broader remedy, or where the federal law is a floor (anti-preemption), or where “conflict preemption” attaches only in particular procedural postures.

---

## Editorial Nuance Gaps

- **International scope:** (e.g., does “jurisdiction” allow for EU/UK/Canada, or only U.S.? If the future model is international, the registry is U.S.-centric.)
- **Hyperlocal/municipal overlay:** Some jurisdictions (city ordinances/state “mini-OSHA” protections) have unique filing logic not representable here.

---

## Gaps in Cross-field Relationships/Validation

- Many validation rules are described (e.g., "jury-trial" term disallowed without private right of action), but no field (e.g. "validation_status" or "editorial_conflict_flag") captures *failures* or warnings triggered by logic guardrails.
- There is no fields for **editor rationale**, administrative override, or note for exception-handled records.

---

## Synthesis/Recommendations

| Gap | Proposal |
|-----|----------|
| Internal/External multi-step, escalation logic | Add field set for “escalation scheme,” with multi-valued path and conditional field for each “step”'s protection scope. |
| Differentiate subjective/objective for each protected activity type | Allow `protected_action_*` fields to support multi-valued logic and a mapping to “test” (objective, subjective, dual). |
| Tolling doctrines not just as yes/no | Add more granular “tolling type” select/repeater with triggers (estoppel, concealment, continuing violation, disability). |
| Retaliation for threatened/attempted activity | Add field for “threatened retaliation protection” (is-there/isn’t-there, details). |
| Arbitration & waiver scope/partials | Add explicit field for “partial arbitration voiding,” “scope of waiver,” and “whistleblower-specific arbitration restriction.” |
| Limited remedy effect for partial/same-decision BOP | Add “remedy limits/penalty reduction for partial defense” with toggles for back-pay, front-pay, punitive exclusion etc. |
| Aggregation/class action type | Add fields for “collective action mechanism,” “opt-in/out allowed,” “non-individual relief?” etc. |
| Cap/floor/ceiling for each remedy | Add integer/decimal/unit fields for “statutory cap,” “floor,” “per claim/max aggregate,” structured under each remedy. |
| Successor/joint liability nuance | Add mapping field for “entity-relationship coverage,” tie “contractors-included,” “successor liability,” “joint employer” to different doctrinal triggers. |
| Ex ante vs. ex post waiver | Add select for “when is waiver permitted,” or “temporal scope of waiver bans.” |
| State/Federal layer logic | A field for “preemption floor/ceiling,” “gap-filler only,” or “no-preemption zone” with companion context. |
| Pre-validation warnings | Add internal-use field for “validation warnings” or “editor override notes.” |

---

## Final Note

Many of these edge cases matter most to practitioners, not to general concept mapping. If your use case is “unified field set for all current whistleblower doctrines” for machine ingestion, the above gaps will limit full semantic equivalence to black-letter doctrine.

If you want “ACF in stone,” these are the chiseling gaps you'd regret not carving.  
**If you want targeted field/term proposals for any gap above, ask and I will generate them.**