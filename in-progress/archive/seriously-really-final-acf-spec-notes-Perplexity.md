Good — I've read the full spec deeply. The other AI notes from the research files didn't surface more content (likely permission/format issues), but I have everything I need from the primary spec and the Copilot analysis file. Let me go to work. Red team hat on. No mercy.

***

## Red-Team: Legal Nuance Gaps the ACF Spec Can't Cover

The spec is genuinely impressive — but impressive things have impressive blind spots. Here's where it cracks under real-world legal pressure, organized by severity.

***

## The Carnage, By Category

### 1. The "Deterred Disclosure" Problem
The spec tracks `attempted-reporting` in `ws_protected_action`, but no field distinguishes **actual → attempted → threatened → deterred** as a spectrum. This matters because under SOX and FCA case law, protection can attach *before* any disclosure occurs if the employer preemptively retaliates to prevent it. Your schema would correctly mark a record as having `protected-action`, but an LLM prompt or researcher has no structured signal that the protection attaches at *pre-disclosure intent* rather than post-disclosure act. That is a qualitatively different legal posture and it's currently buried in freetext or lost entirely. [drive.google](https://drive.google.com/file/d/1nKsA-8vqn4dqXmWX4hRooujv3clwSPDz/view?usp=drivesdk)

### 2. Classified/National Security Disclosure Has No Channel-Enforcement Teeth
You have `intelligence-community` and `classified-information` under `ws_protected_disclosure`, and `congressional-disclosure` as a process type. What you **don't have** is any structured field for the **Inspector General Act → ICWPA → ISCPB review chain** — the mandatory sequential channel requirement specific to IC/national security disclosures where going *outside* that chain destroys protection entirely. `disclosure_channel_scope` with `mandatory-internal-first` is a blunt instrument here. A cleared employee who went to the IG, was ignored, and then went to congressional intelligence committees may have full protection. One who went to the press first has none. The schema can't distinguish these at ingest without a bespoke sentinel. 

### 3. Qui Tam Relator Share Has No Concurrency or Priority Field
`qui-tam-relator` exists as a protected class and `qui-tam` as a process type, but the **relator share percentage range** (15–30% government-intervened, 25–30% non-intervened under FCA) has no structured field. More critically, there's no field for **first-to-file bar status** or **public disclosure bar** — both of which can eliminate a claim entirely, not just reduce a remedy. A record showing `bounty-qui-tam-award` as a remedy creates a false impression of availability if the underlying claim is time-barred by a prior public disclosure. This is a data integrity hazard, not just a nuance gap. 

### 4. The Causation-Standard / Burden-Allocation Split Has a Hidden Seam
`ws_causation_standard` is well-structured, and `contributing-factor-but-for-backstop` correctly captures the AIR21/WPA dual-phase standard. But there's no structured field for **who bears the burden at each phase** after a prima facie case. Under the WPA/WPEA, once the employee shows contributing factor, the *burden shifts* to the employer to prove clear-and-convincing it would have taken the same action anyway. Under Title VII McDonnell Douglas, it's preponderance with a different allocation. Your schema captures *what* the standard is but not *when the burden shifts and to whom*, which is the actual litigation-critical information. The `causation_application` field with `liability`/`damages`/`both` is a contradiction guard, not a burden-allocation tracker. 

### 5. Anti-SLAPP Is Mapped But Its Procedural Teeth Aren't
`anti-slapp-protection` is in `ws_legal_recognition`  — good. But Anti-SLAPP statutes operate procedurally: they create a **special motion to strike**, an **automatic stay of discovery**, and **fee-shifting on the motion itself**, often on a different burden (probability of prevailing, not merits). None of those procedural mechanics are capturable as structured data in the current schema. An org or researcher seeing `anti-slapp-protection: true` has no idea if this is a robust California § 425.16 with mandatory fee-shifting and discovery freeze, or a weak state analog with no real teeth. The difference is enormous for a whistleblower deciding whether to fight a SLAPP suit. 

### 6. Constructive Discharge Has a Trigger But No Standard Field
`constructive-discharge` is in `ws_adverse_action` with a companion context field. But constructive discharge has a **legally contested standard**: some circuits require *objective* intolerability (reasonable person), others require employer *intent*, others use a dual-prong. This is exactly the kind of split that your `reasonable_belief_context` handles elegantly for protected action — but there's no paraparallel `constructive_discharge_standard` select (objective-intolerability | intent-required | dual-prong | has-details). The context freetext will capture it if an editor is meticulous, but there's no structured analytical signal for the LLM or the reconciler.

### 7. Election-of-Remedies Has No "First Filed" Tracking Field
`election_of_remedies_rules` includes `first-filed-controls` as a choice. But **which forum was filed in first** — the critical operative fact when this rule applies — is not tracked. If a whistleblower filed administratively first, that may bar the civil action. The schema records the *rule* but not the *state*, which means the assist-org record and the legal record have no structured handshake on whether this restriction is currently triggered or merely theoretically applicable. 

### 8. Confidential Settlement Restriction Has No Scope Field
`confidential-settlement-restriction` is in `ws_legal_recognition` with a context companion. But the actual *scope* varies wildly: some laws prohibit confidentiality clauses as to the *amount* only, others as to the *facts* of the retaliation, others prohibit any settlement confidentiality related to safety violations. Without a structured scope field (am (amount-only | facts | full-prohibition | agency-notification-required | has-details), editors will dump everything into `settlement_restriction_context` and the data becomes analytically opaque.

### 9. Individual Liability Has No Role Classifier
`individual-liability` is a no-companion term in `ws_legal_recognition`. In practice, the critical question is *which individuals*: supervisors personally, co-workers, HR personnel, officers and directors under SOX § 1107? Wit Without a `ws_individual_liability_scope` field (supervisor | coworker | officer-director | any-individual | has-details), this is a binary flag on a very non-binary legal reality.

### 10. Mixed-Motive Is Missing as a Distinct Causation Concept
`causation-motivating-factor` and `substantial-motivating-factor` are in `ws_causation_standard`. But **mixed-motive** is a distinct *defense* doctrine — even if protected activity was *a* motivating factor, employer wins if it proves the same action would have been taken for legitimate reasons. This is analytically separate from the plaintiff's causation burden. There's no taxonomy term or field for whether a **same-decision defense is available**, which is a meaningful protection gap for Title VII analogs applied in whistleblower context. 

***

## The Fix Plan

| # | Gap | Severity | Fix Type | Proposed Implementation | Companion Needed? |
|---|-----|----------|----------|------------------------|-------------------|
| 1 | Deterred disclosure pre-disclosure protection | High | New taxonomy term | Add `deterred-disclosure` to `ws_protected_action`; OR add `protected_action_status` select (`actual\|attempted\|threatened\|deterred`) to Classifications Tab | No (select) / Yes if taxonomy |
| 2 | IC/national security mandatory channel chain | High | New sentinel + field | Add `ic-channel-required` sentinel to `ws_protected_disclosure` under `national-security`; add `ic_channel_sequence_context` companion field triggered by it | Yes — `ic_channel_sequence_context` |
| 3 | Qui tam first-to-file / public disclosure bar | High | New fields | Add `has_first_to_file_bar` boolean + `first_to_file_context`; add `has_public_disclosure_bar` boolean + `public_disclosure_bar_context` to SOL/Thresholds Tab | Yes — context companions |
| 4 | Burden-shift allocation (who/when/standard) | High | New field cluster | Add `burden_shift_trigger` select (`prima-facie\|contributing-factor\|preponderance\|see-context`) + `burden_shift_standard` select (`clear-convincing\|preponderance\|see-context`) + `burden_shift_details` to Burden of Proof Tab | Yes — `burden_shift_details` |
| 5 | Anti-SLAPP procedural mechanics | Medium | New field | Add `anti_slapp_scope` select (`motion-to-strike\|discovery-stay\|fee-shift-on-motion\|full-procedural\|see-context`) conditional on `anti-slapp-protection` in `legal_recognitions` | Yes — sister to existing context |
| 6 | Constructive discharge standard | Medium | New select field | Add `constructive_discharge_standard` select (`objective-intolerability\|intent-required\|dual-prong\|has-details`) as sister to `constructive_discharge_context` in Retaliation Tab | Via `has-details` |
| 7 | Election-of-remedies first-filed state tracking | Medium | New field | Add `election_first_filed_forum` select (`administrative\|civil\|arbitration\|not-yet-filed\|see-context`) conditional on `first-filed-controls` in `election_of_remedies_rules` | Yes — context companion |
| 8 | Confidential settlement restriction scope | Medium | New select field | Add `settlement_restriction_scope` select (`amount-only\|facts\|full-prohibition\|agency-notification\|has-details`) conditional on `confidential-settlement-restriction` in `legal_recognitions` | Via `has-details` |
| 9 | Individual liability role classifier | Medium | New select field | Add `individual_liability_scope` multi-select (`supervisor\|coworker\|officer-director\|any-individual\|has-details`) conditional on `individual-liability` in `legal_recognitions` | Yes — context |
| 10 | Mixed-motive / same-decision defense | Medium | New taxonomy term + field | Add `same-decision-defense` to `ws_legal_recognition`; add `same_decision_defense_context` companion + `same_decision_standard` select (`preponderance\|clear-convincing\|see-context`) | Yes — context companion |

***

## Honorable Mention (Didn't Make the Main Hit List)

The `ws_process_type` taxonomy has `arbitration-compelled` but no `arbitration-waived` term  — some statutes explicitly *bar* compelled arbitration for whistleblower claims (Dodd-Frank, CFPB rules), which is the mirror-image of compelled arbitration and currently has no structured home except as an `election_of_remedies_rules` value or freetext. It's edge-case enough to land in editorial guidance rather than a new field, but it'll bite someone eventually. 

The `ws_adverse_action` taxonomy also has no term for **benefit clawback** — some employers respond to whistleblowing by retroactively revoking vested benefits, unvested equity, or severance, which is legally distinct from `benefit-denial` (prospective) and `pay-reduction`. Rare but vicious, and increasingly litigated under ERISA anti-retaliation provisions. 