You've got the right problem now — it's not "what's missing," it's "what do we etch before the seeder gates lock." The 48-hour ferment worked. All three final passes stopped proposing new architectures and started circling the same six litigation killers.

Here's the synthesis that survives the overlap test: if 2+ of the seriously-really-final notes hit it *and* 5+ of the original 8 hit it, it goes in. Everything else waits for filter logs.

### Etch these now — 7 fields, 1 option add, no new tables

**SOL & Thresholds**
- `sol_trigger_event` — select: `notice-of-action` | `occurrence-of-action` | `discovery-of-harm` | `constructive-discharge-accrual`  
  This is the Chardon trap. Your existing `sol_trigger` says *how long*. This says *when the clock starts*. James loses on day 31 without it.

**Enforcement**
- `sovereign_immunity_waiver` — select: `explicit-waiver` | `implied-waiver` | `none` | `not-applicable`  
  Sister to your existing `sovereign_immunity_limits`. Pair with `sovereign_immunity_waiver_details`. Public-sector workers need to know if the waiver is real.
- `confidentiality_mandate` — select: `govt-must-conceal` | `employer-must-conceal` | `both` | `discretionary` | `none`  
  Maya doesn't ask "can I be anonymous." She asks "do they *have* to keep my name secret." This answers it.
- `is_joint_employer_liable` — bool → `joint_employer_details`  
  Temp, staffing agency, gig host. Add `staffing-agency` and `joint-employer` to `proper_defendants` at the same time. This closes the modern-economy blind spot.
- `has_blacklisting_protection` — bool → `blacklisting_details`  
  You have `blacklisting` in `ws_adverse_action`. This is the *protection* signal James needs after termination.
- `has_discovery_protection` — bool → `discovery_protection_details`  
  Covers SLAPP, abusive subpoenas, Rule 202. Pair with the terms below.

**Burden of Proof**
- Add option `imputed-knowledge` to existing `employer_knowledge_scope`  
  This is your cat's paw bridge. Manager A knows, manager B fires, law imputes. No new field, and it answers the associate-parent extension you flagged: put "extends to spouse per Thompson" in `cats_paw_context`.

### Seed these terms now — bump gates, no migration

**ws_adverse_action** → gate to 1.3.0  
`retaliatory-litigation`, `discovery-harassment`, `blacklisting`, `post-employment-retaliation`

**ws_legal_recognition** → gate to 1.2.0  
`cat-paw-attribution`, `discovery-protection`, `prospective-whistleblower-protection`, `sovereign-immunity-waiver`, `continuing-violation-doctrine`, `temporal-proximity-sufficient`

**ws_proper_defendant** → gate to 1.1.0  
`staffing-agency`, `joint-employer`

**ws_remedy** → gate to 1.2.0  
`emotional-distress-damages`, `interest-on-backpay`, `tax-gross-up`

**ws_protected_action** → gate to 1.2.0  
`cooperation-with-investigation`, `refusal-to-violate-law`

**ws_disclosure_target** → gate to 1.1.0  
`office-of-inspector-general`

**ws_protected_disclosure** → gate to 1.1.0  
`mismanagement-of-funds`

### Park for 3.17 — correct, but they need new tables or heavy hooks

- All new taxonomy tables: `ws_litigation_safeguard`, `ws_statutory_purpose`, `ws_burden_of_proof`, `ws_evidence_type`, `ws_immunity_limit`, `ws_legal_authority_source`, `ws_public_policy_exception`, `ws_administrative_exhaustion_path`
- `has_pre_suit_discovery`, `is_per_curiam`, `pretext_standard`, `election_of_remedies_waiver`, `prima_facie_elements`, `ruling_impact_weight`  
  These live fine in `*_context` until your logs show researchers hitting them repeatedly.

### Why this list wins

It adds 6 new fields and 1 option, all using your `has_*` → `*_details` and presence-taxonomy + context pattern. No new ACF groups, no re-seeding of empty tables, and it closes the six gaps that actually get cases dismissed: SOL start date, sovereign waiver, confidentiality mandate, joint employer, blacklisting, and discovery harassment.

Tag `register-taxonomies.php` at 3.16.0, bump the seven seeder gates above, and ship the directory. The Cloudflare logs will tell you which of the deferred terms deserve promotion in 3.16.1.

The masons can start carving.