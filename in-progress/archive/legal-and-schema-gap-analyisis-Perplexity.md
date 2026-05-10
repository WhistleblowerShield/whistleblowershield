Good enough — I have the full taxonomy file and enough context from the project docs. Let me do the full analysis now.

***

Here's the gap report. Flea fart gaps are schema completeness issues (missing terms, coverage holes). VW Beetle gaps are legal nuance issues that could produce materially wrong guidance if not addressed.

***

## Gap Report: 21-Taxonomy Schema

*Audit date: May 8, 2026 | Against the 3-purpose test: search vector · refinement vector · ACF overflow dam*

***

## The Three-Purpose Test Results

Every taxonomy passed the test — all 21 serve at least one of the three valid purposes. No prune candidates identified at this time.

***

## 🦟 Flea Fart Gaps (Schema Completeness)

These are missing terms or term coverage holes — nothing architectural.

**`ws_protected_disclosure` (Taxonomy 1)**
- `ai-algorithmic-harm` is absent — AI/algorithmic discrimination and automated decision-system reporting is a fast-growing disclosure category, now covered by EU AI Act equivalents and emerging state laws (Colorado, California). Worth a child under a new `emerging-tech` parent or under `privacy-data-integrity`. 
- `climate-esg-reporting` is absent — ESG/climate disclosure obligations under SEC Rule S-K and state mandates have become their own disclosure category distinct from `environmental-protection`. 

**`ws_adverse_action` (Taxonomy 6)**
- `benefit-clawback` already exists but `equity-clawback` (stock option cancellation, RSU forfeiture as retaliation) is absent — this is a distinct adverse action pattern in tech/finance whistleblower cases. 
- `publication-suppression` is absent — suppression of a book, academic paper, or research finding as retaliation is documented in healthcare and government contractor cases and doesn't map cleanly to any existing term. 

**`ws_protected_action` (Taxonomy 19)**
- `internal-disclosure-only` under `opposition-clause` is present in spirit via `internal-objection` but there's no term for `whistleblower-hotline-report` as a distinct protected action, which several statutes (SOX, Dodd-Frank) enumerate explicitly. 
- `environmental-sample-collection` — statutes like TSCA and CERCLA protect employees who collect or submit environmental samples; this falls in a gap between `opposing-practice` and `cooperation-with-investigation`. 

**`ws_remedy` (Taxonomy 3)**
- `equity-restoration` (canceled stock grants, forfeited RSUs restored as part of make-whole relief) is absent. Distinct from `benefits-restoration` which reads as health/retirement benefits. 
- `security-clearance-restoration` is absent — appears in federal employee cases and is a distinct non-monetary remedy. 

**`ws_disclosure_target` (Taxonomy 10)**
- `audit-committee` is absent — Sarbanes-Oxley §301 makes audit committee disclosure a protected channel, and it's distinct from `internal-oversight-body` or `internal-compliance`. This is a material omission for corporate whistleblower cases. *(Borderline beetle — see below.)* 
- `self-regulatory-organization` (FINRA, MSRB) is absent under `external-agency` — these are not federal agencies but are legally recognized disclosure channels under Dodd-Frank. 

**`ws_aorg_service` (Taxonomy 15)**
- `translation-interpretation` is absent — distinct from language support; some orgs specifically provide certified legal interpreters rather than bilingual staff. 
- `safety-planning` is absent — specific to domestic violence/sexual assault whistleblower adjacent cases already covered by `victim-domestic-violence-sexual-assault` in protected classes. 

**`ws_employment_sector` (Taxonomy 13)**
- `financial-services-worker` is absent — Dodd-Frank, SOX, and CFPA cover this sector distinctly from generic `private-sector`. It's a common search vector and would get used in refinement constantly. 

***

## 🚗 VW Beetle Gaps (Legal Nuance — Material)

These are gaps that could produce materially wrong output in an LLM research or assist context. No buses. No trucks. Just Beetles.

**`ws_employee_standard` vs. `ws_causation_standard` — Conflation Risk**
The split between Taxonomy 17 (burden of proof) and Taxonomy 21 (causation standard) is architecturally correct and well-documented in the code comments.  However, `reasonable-belief` sits in `ws_employee_standard` (Taxonomy 17) as a burden standard when it is actually a *threshold qualification standard* — whether the disclosure was protected at all — not a quantity-of-evidence standard. It belongs in its own taxonomy or in `ws_legal_recognition` (`reasonable-belief-required` already exists conceptually in the codebase). Leaving it in the burden taxonomy risks the LLM being told a case has a "reasonable belief burden of proof" which is a legally distinct and potentially wrong framing. **This is the biggest single beetle.** 

**`ws_protected_disclosure` — `general-wrongdoing` Is a Black Hole**
`general-wrongdoing / violation-of-law` under `general-legal` is so broad that any record tagged only with it provides essentially zero search or refinement signal. There's no `has-details` sentinel on this term, no companion child terms to narrow it, and no mechanism preventing it from being applied as a lazy catch-all. Consider adding a `has-general-basis-only` sentinel or requiring at least one sibling tag from another parent category before this term is usable on a legal record. 

**`ws_disclosure_target` — `audit-committee` Absence (Elevated from Flea)**
SOX §301 and SEC Rule 10A-3 mandate independent audit committee whistleblower procedures for all listed companies. Disclosures to the audit committee trigger Dodd-Frank protections distinct from `internal-compliance` or `internal-oversight-body`. A record incorrectly tagged as `internal-compliance` instead of audit committee could suppress it from searches by corporate whistleblowers in securities contexts. 

**`ws_employer_defense` — Missing `mixed-motive` as a Distinct Defense**
`same-decision-defense` is present but `mixed-motive` (where both retaliatory and legitimate motives exist) is a distinct doctrine with different burden-allocation rules depending on jurisdiction and statute. Under Title VII price waterhouse standards vs. Mt. Healthy vs. contributing-factor statutes, the outcome differs materially. The current set conflates this into `same-decision-defense` which is technically the *remedy* phase of a mixed-motive analysis, not the defense itself. 

**`ws_legal_recognition` — `good-faith-reasonable-belief` Conflation**
The taxonomy has `public-concern-required` and `bad-faith-exclusion` but no term for `good-faith-reasonable-belief-required` as a distinct recognition toggle. Some statutes require objective reasonable belief (what a reasonable person would believe), others require subjective good faith (what *this* person believed), and a subset require both. These three standards produce different case outcomes. Currently all of them would likely get tagged `reasonable-belief-standard` in `ws_employee_standard` (Taxonomy 17), which is the wrong table and the wrong framing. This compounds the beetle above. 

**`ws_process_type` — `government-shutdown-tolling` / Administrative Backlog Pathways**
`hybrid-admin-civil-path` exists but there's no term for the edge case where administrative exhaustion becomes futile or impossible (agency shutdown, OSHA backlog triggering right-to-sue). Several statutes (AIR21, STAA, SOX) have "kick-out" provisions letting a claimant pull their case to federal court after a delay period. This isn't covered by any existing process type and is a real search vector for frustrated claimants stuck in OSHA purgatory. 

***

## Quick Summary Table

| Gap | Size | Taxonomy | Action |
|---|---|---|---|
| `reasonable-belief` in wrong taxonomy | 🚗 Beetle | #17 / #20 | Move or re-classify |
| `general-wrongdoing` black hole | 🚗 Beetle | #1 | Add sentinel or guard |
| `audit-committee` disclosure target | 🚗 Beetle | #10 | Add term |
| `mixed-motive` defense absent | 🚗 Beetle | #11 | Add term |
| `good-faith-reasonable-belief` conflation | 🚗 Beetle | #20 | Add recognition terms |
| OSHA kick-out / futile exhaustion process | 🚗 Beetle | #2 | Add term |
| `ai-algorithmic-harm` disclosure | 🦟 Flea | #1 | Add term |
| `climate-esg-reporting` disclosure | 🦟 Flea | #1 | Add term |
| `equity-clawback` adverse action | 🦟 Flea | #6 | Add term |
| `publication-suppression` adverse action | 🦟 Flea | #6 | Add term |
| `whistleblower-hotline-report` protected action | 🦟 Flea | #19 | Add term |
| `equity-restoration` remedy | 🦟 Flea | #3 | Add term |
| `security-clearance-restoration` remedy | 🦟 Flea | #3 | Add term |
| `self-regulatory-organization` disclosure target | 🦟 Flea | #10 | Add term |
| `financial-services-worker` sector | 🦟 Flea | #13 | Add term |
| `translation-interpretation` org service | 🦟 Flea | #15 | Add term |

No buses. No trucks. The architecture is solid — these are fill-ins and two structural nuance corrections, not redesigns.