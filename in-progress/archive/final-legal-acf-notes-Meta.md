Reading both files together, the reboot proposal is a big step up from the old scattered booleans. You're moving from 4 separate ACF groups to one canonical field set, and from `*_recognized` checkboxes to the `ws_legal_recognition` presence taxonomy. The `register-taxonomies.php` 3.15.0 seed already implements the split of causation from burden, adds `ws_protected_action` with opposition/participation clauses, and expands adverse actions and remedies.

That structure now captures most of the doctrinal spine of U.S. whistleblower law. But there are still a handful of legally material distinctions that don't have a clean home in the draft.

## What the new schema nails

- **Causation vs burden, properly separated.** `ws_causation_standard` (but-for, contributing-factor, motivating-factor) is now distinct from `ws_employee_standard` (preponderance, clear-and-convincing). That's legally correct and was conflated before.
- **Protected activity granularity.** `protected_action_standard` with `reasonable_belief` → `objective_only`/`subjective_only`/`dual_prong` captures the difference between New Jersey CEPA's objective test and SOX's dual-prong test.
- **Channel control.** `disclosure_channel_scope` (approved-channel-only vs any-channel) plus `disclosure_targets` taxonomy handles the gap between statutes that require internal reporting first and those that protect external media disclosures.
- **Procedural tolling doctrines.** Moving `continuing-violation`, `equitable-tolling`, `statutory-tolling`, `amended-claim` into `ws_legal_recognition` with context companions means you can record not just presence but the court's reasoning.
- **Waiver architecture.** The hook between `civil_action_waiver_scope = anti` and clearing `contractual-waiver` from legal_recognitions correctly models statutes like the Speak Out Act that void NDAs for harassment claims.
- **Remedy specificity.** `remedy_liquidated_multiplier`, `tax-gross-up`, `interim-reinstatement` in the remedy taxonomy, plus `remedy_limits`, gives you room for the weird state formulas.

## Legal nuances that still lack a field

These aren't edge cases — they show up in complaints and determine outcomes.

**1. Associational retaliation**
Your earlier status doc flagged `family-member-of-whistleblower` as pending. The current `ws_protected_class` seed adds `intern-volunteer` but not family members, associates, or perceived whistleblowers. Many state laws (CA 6310, NJ CEPA) explicitly protect relatives. Right now you'd have to jam this into `protected_class_details` freetext, which breaks filtering.

**2. Employer knowledge element**
Most retaliation claims require the decision-maker knew or suspected the protected activity. There's no `employer_knowledge_required` boolean or `knowledge_standard` (actual vs constructive). You capture causation, but not the antecedent knowledge prong.

**3. Manager rule / job-duties carve-out**
The proposal has `refusal-to-participate` in `ws_protected_action`, but nothing for the common-law exception where compliance officers, auditors, or in-house counsel are unprotected when reporting within job duties unless they step outside channels (the "manager rule"). This is a major SOX and state-law distinction. You'd need something like `duty-speech-exclusion` in legal_recognitions with a context field.

**4. Public employee First Amendment overlay**
For government workers, Garcetti/Pickering creates a separate test: speech pursuant to official duties is unprotected, speech on matter of public concern gets balancing. Your `protected_action_source` includes `constitutional`, but there's no field for `public-concern-required` or `official-duties-exclusion`. That's a different doctrine than private-sector whistleblowing.

**5. Successor and joint employer liability**
`proper_defendants` includes `contractors-included` and `employer-entity-only`, but not `successor-employer` or `joint-employer`. Several retaliation statutes impose liability on purchasers of assets or staffing agencies. Right now you'd lose that in `proper_defendant_details`.

**6. Authorized recipient specificity**
`disclosure_targets` is broad. Federal contractor protections (41 U.S.C. §4712) require disclosure to specific recipients — IG, Congress, GAO, etc. — to trigger protection. `disclosure_channel_scope = approved-channel-only` is too blunt. You need a taxonomy or multi-select for authorized recipients, not just internal vs external.

**7. Temporal status of worker**
`ws_protected_class` covers who, but not when. Applicants, former employees, and post-employment retaliation (blacklisting) are protected in many jurisdictions. The schema has `employment_sectors` but no `worker_temporal_status` (current/former/applicant). You'd currently misclassify a blacklisting claim.

**8. Bad faith / knowingly false reports**
`protected_action_standard` has `good_faith`, but no explicit exclusion for knowingly false or malicious reports. Some statutes strip protection for bad faith; others don't. This is distinct from reasonable belief. You need a `bad-faith-exclusion` flag in legal_recognitions.

**9. Anti-SLAPP interplay**
Whistleblowers sued for defamation often raise anti-SLAPP defenses. No field captures whether the jurisdiction recognizes anti-SLAPP protection for whistleblowing disclosures. That's a procedural shield that affects filing strategy.

**10. Geographic and extraterritorial reach**
No field for `extraterritorial_application` or `out-of-state-employee-covered`. California and New York have case law on remote workers; federal contractor statutes have specific geographic limits. Right now it's invisible.

**11. Punitive damages architecture**
You have `remedies` and `remedy_limits`, but punitive damages often have separate statutory caps, mental-state requirements (malice/reckless), and availability by claim type. Lumping into generic limits loses the ability to filter for "states with uncapped punitives for whistleblower claims."

**12. Fee-shifting asymmetry**
`fee_shifting_rules` exists, but the proposal doesn't distinguish one-way (plaintiff only) vs two-way vs offer-of-judgment shifting vs frivolous-claim fee awards to defendants. The `has-phases` sentinel helps, but you still can't query "defendant can recover fees for frivolous SOX claims."

**13. Administrative exhaustion jurisdictionality**
You have `exhaustion_class` with `jurisdictional`/`claims-processing`/`waivable` — that's good. What's missing is the consequence: does failure to exhaust deprive court of subject-matter jurisdiction (SOX pre-2010) or is it a waivable defense? That's outcome-determinative and deserves its own boolean, not just context text.

## Small taxonomy gaps in the PHP

From `register-taxonomies.php` 3.15.0:
- `ws_adverse_action` now includes `retaliatory-litigation`, `hostile-work-environment`, `retaliatory-investigation` — good, but missing `blacklisting` and `post-employment-retaliation` as distinct slugs (currently you'd use `has-details`).
- `ws_protected_action` has opposition and participation clauses, but not `providing-information-to-government` as distinct from `filing-complaint` — some statutes treat them differently for SOL purposes.
- `ws_legal_recognition` includes `nda-limits` but not `confidential-settlement-restriction` — newer state laws bar NDAs in settlement agreements specifically.

## Recommendation for the reboot

Before you freeze the new ACF files, add three lightweight structures rather than stuffing into details fields:

1. A new flat taxonomy `ws_worker_status` (current, former, applicant, perceived-whistleblower, associate-family-member) to replace the missing associational protection.

2. Add to `ws_legal_recognition`: `manager-rule-exclusion`, `public-concern-required`, `employer-knowledge-required`, `bad-faith-exclusion`, `anti-slapp-applies`, `successor-liability`, `extraterritorial-coverage`.

3. Split `proper_defendants` to include `successor-employer` and `joint-employer`, and add `authorized_recipients` as a multi-select taxonomy linked to disclosure_targets.

Those changes keep your presence/absence pattern intact, avoid new booleans, and let the ingest pipeline capture the distinctions that actually win or lose cases.

Want me to draft the exact term lists for those three additions so you can paste them into `register-taxonomies.php` before you regenerate the ACF files?

---

# Implementation Proposal: Legal Record Schema v3.16.0

**Purpose:** close the legal-nuance gaps identified in the `legal-record-acf-fields.md` draft and `register-taxonomies.php` 3.15.0, while preserving the presence-taxonomy pattern you adopted for the reboot.

**Scope:** changes to taxonomies, ACF canonical fields, and three hooks. No changes to CPT registration, query layer contracts, or render layer.

**Target version:** 3.16.0 — legal-records pipeline only. Assist-org pipeline unchanged.

---

## 1. Gaps identified in current draft

The 3.15.0 schema captures causation, burden-shifting, fee-shifting, and most tolling doctrines. It does not capture:

1. Associational retaliation (family members, associates, perceived whistleblowers)
2. Employer knowledge as an element
3. Manager rule / duty-speech exclusion
4. Public employee First Amendment overlay (Garcetti/Pickering)
5. Successor and joint employer liability
6. Authorized recipient requirements for federal contractors
7. Temporal worker status (applicant, former employee, blacklisting)
8. Bad faith / knowingly false report exclusions
9. Anti-SLAPP applicability
10. Extraterritorial coverage
11. Punitive damages caps as distinct from general remedy limits
12. Fee-shifting asymmetry (defendant recovery for frivolous claims)

All of these determine whether a claim survives a motion to dismiss. They should be filterable, not buried in `*_details` freetext.

---

## 2. Proposed taxonomy changes

### 2.1 New taxonomy: `ws_worker_status`

Flat, non-hierarchical. Replaces ad-hoc use of `ws_protected_class` for temporal and associational status.

**Attach to:** all 4 legal CPTs

**Seeder function:** `ws_seed_worker_status_taxonomy()` — gate `ws_seeded_worker_status` = `1.0.0`

| Slug | Name |
|---|---|
| current-employee | Current Employee |
| former-employee | Former Employee |
| applicant | Job Applicant |
| intern-volunteer | Intern or Volunteer |
| family-member | Family Member of Whistleblower |
| associate | Associate of Whistleblower |
| perceived-whistleblower | Perceived Whistleblower |
| has-details | Has Details |

**Migration note:** move `intern-volunteer` from `ws_protected_class` to this taxonomy on ingest. Keep old term for backward compatibility until 3.17.0.

### 2.2 Additions to `ws_legal_recognition`

Add eight terms with context companions. Update `ws_seed_legal_recognition_taxonomy()` and bump gate to `1.1.0`.

| Slug | Name | Companion fields |
|---|---|---|
| manager-rule-exclusion | Manager Rule / Duty Speech Exclusion Applies | `manager_rule_context` |
| public-concern-required | Public Concern Requirement Applies | `public_concern_context` |
| employer-knowledge-required | Employer Knowledge Element Required | `employer_knowledge_context` |
| bad-faith-exclusion | Bad Faith / Knowingly False Exclusion Applies | `bad_faith_context` |
| anti-slapp-applies | Anti-SLAPP Protection Applies | `anti_slapp_context` |
| successor-liability-recognized | Successor Employer Liability Recognized | `successor_liability_context` |
| extraterritorial-coverage | Extraterritorial Coverage Recognized | `extraterritorial_context` |
| confidential-settlement-restriction | Confidential Settlement Restriction Applies | `settlement_restriction_context` |

### 2.3 Additions to `ws_adverse_action`

Bump gate to `1.2.0`.

| Slug | Name |
|---|---|
| blacklisting | Blacklisting |
| post-employment-retaliation | Post-Employment Retaliation |

These replace using `has-details` for blacklisting cases.

### 2.4 New taxonomy: `ws_authorized_recipient`

Hierarchical. Captures statutes that limit protection to specific channels.

**Attach to:** all 4 legal CPTs

**Seeder:** `ws_seed_authorized_recipient_taxonomy()` — gate `1.0.0`

| Parent | Children |
|---|---|
| internal | supervisor, compliance-hotline, legal-department, board-of-directors |
| government | inspector-general, congress, gao, dol-osha, sec, law-enforcement, state-agency |
| external | media, public-disclosure, union |

Use this instead of overloading `disclosure_channel_scope`.

### 2.5 Updates to `ws_remedy`

Bump gate to `1.1.0`. Add term for filtering punitive caps.

| Slug | Name |
|---|---|
| punitive-damages | Punitive Damages |

Existing `has-limits` sentinel will trigger `remedy_limits` where caps apply.

---

## 3. ACF field changes to canonical draft

All fields follow your naming rules: snake_case meta, no prefix in draft, `has_*` triggers `*_details`, context companions conditional on taxonomy presence.

### 3.1 Classification Tab — add after `anonymity_context`

- `worker_statuses` — taxonomy: `ws_worker_status`, multi-select
- `worker_status_details` — conditional on `worker_statuses` includes `has-details`
- `authorized_recipients` — taxonomy: `ws_authorized_recipient`, multi-select
- `authorized_recipient_details` — conditional on `authorized_recipients` includes `has-details`

### 3.2 Classification Tab — new context companions (triggered by `legal_recognitions`)

Add these to the slug-to-companion map:

- `manager_rule_context` — freetext, conditional on `manager-rule-exclusion`
- `public_concern_context` — freetext, conditional on `public-concern-required`
- `employer_knowledge_context` — freetext, conditional on `employer-knowledge-required`
- `bad_faith_context` — freetext, conditional on `bad-faith-exclusion`

### 3.3 Enforcement Tab — expand existing fields

Update `proper_defendants` options to include:
- `successor-employer`
- `joint-employer`

Add after `sovereign_immunity_details`:

- `anti_slapp_context` — conditional on `anti-slapp-applies`
- `successor_liability_context` — conditional on `successor-liability-recognized`
- `extraterritorial_context` — conditional on `extraterritorial-coverage`
- `settlement_restriction_context` — conditional on `confidential-settlement-restriction`

### 3.4 Enforcement Tab — fee shifting asymmetry

Add after `fee_shifting_details`:

- `fee_shifting_asymmetry` — single-select: `one-way-plaintiff`, `one-way-defendant-frivolous`, `two-way`, `american-rule`, `has-details`
- `fee_shifting_asymmetry_details` — conditional on `has-details`

### 3.5 Burden of Proof Tab — no new fields

No change. `employer_knowledge_required` is handled via legal_recognition context, not as separate burden element.

---

## 4. Hook updates required

Keep your pattern of derived values and filtered choices.

1. **Worker status auto-suggest**
   - Hook: `acf/load_field` for `worker_statuses`
   - If `protected_classes` includes `family-member-of-whistleblower` (legacy), auto-select `family-member` in `worker_statuses` and flag for review.

2. **Authorized recipient filter**
   - Hook: `acf/prepare_field` for `authorized_recipients`
   - If `jurisdiction` = `us` and `disclosure_types` includes `federal-contractor`, limit choices to `government` parent terms. Set field instructions dynamically.

3. **Legal recognition contradiction check**
   - Hook: `acf/save_post` priority 20
   - If `bad-faith-exclusion` present and `protected_action_standard` = `good_faith`, emit admin notice: "Bad faith exclusion selected but standard is good faith — confirm intent."

4. **Manager rule context enforcement**
   - Hook: `acf/save_post`
   - If `manager-rule-exclusion` present and `worker_statuses` empty, set `worker_statuses` to include `current-employee` by default (most common application).

---

## 5. Data migration and versioning

**File actions:**
- Rename existing `register-taxonomies.php` to `register-taxonomies.php.txt`
- Create new `register-taxonomies.php` with version header `3.16.0`
- Rename `legal-record-acf-fields.md` to `.txt`, create new canonical draft incorporating fields above

**Seeder gates to bump:**
- `ws_seeded_legal_recognition` → `1.1.0`
- `ws_seeded_adverse_action` → `1.2.0`
- `ws_seeded_remedy` → `1.1.0`
- New: `ws_seeded_worker_status` → `1.0.0`
- New: `ws_seeded_authorized_recipient` → `1.0.0`

**Backfill strategy:**
- On first admin load after update, seeders run automatically.
- For existing NJ, MA, CA records: run `tool-ingest.php` in dry-run mode to flag records where `protected_classes` contains `family-member-of-whistleblower` or where `adverse_actions` would map to new blacklisting term. Do not auto-migrate — queue for human review.

---

## 6. Testing plan

1. **Taxonomy registration test:** verify all 5 new/updated taxonomies appear in legal CPT edit screens, with correct hierarchical structure for authorized recipients.
2. **Context companion test:** select each new legal_recognition term, confirm corresponding `*_context` field appears and saves.
3. **Filter test:** create test statute with `worker_statuses` = `family-member`, `former-employee`; verify `ws_get_jx_statute_data()` returns both terms in query layer array.
4. **Hook test:** set jurisdiction to US, disclosure type to federal-contractor, verify authorized recipient choices filter to government branch only.
5. **Ingest test:** run prompt generator for Wyoming common law record, confirm new fields appear in generated JSON schema stub.

---

## 7. Out of scope for 3.16.0

- Punitive damages caps as separate numeric field (defer to 3.17.0 — use `remedy_limits` for now)
- Geographic coordinates for extraterritorial coverage (keep as context text)
- Full anti-SLAPP 50-state matrix (capture presence only)

This proposal keeps your presence-taxonomy architecture intact, adds 2 new taxonomies and 8 new legal_recognition terms, and introduces 9 ACF fields with context companions. It closes the associational, managerial, public-employee, and successor-liability gaps without creating a parallel boolean system.

---

