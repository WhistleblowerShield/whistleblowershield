Good — a full read on the updated file. Here's the first-pass review. 

***

## First-Pass Review: `legal-record-acf-fields.md` (v3.15 → current)

Overall: the proposal items landed well. Several are cleanly implemented. A few have drifted, one has a naming inconsistency that will cause a runtime bug, and two housekeeping items from the proposal are still unresolved.

***

### What Landed Correctly

- `preliminary_reinstatement_context` promoted with full instruction text — correct 
- `sovereign_immunity_scope` added as sister field before `sovereign_immunity_details` — correct placement, correct select options 
- `internal-only-sufficient` added to slug-to-companion map as no-companion — correct 
- `criminal_sanctions` restructured as a repeater with `sanction_conduct` + `sanction_level` sub-fields — this is actually *better* than the proposal's multi-select suggestion; a repeater captures conduct-to-severity pairings cleanly 
- `causation_standard_context` conditional is now explicit (`conditional on causation_standards non-empty`) — correct 
- `disclosure_channel_scope` has `mandatory-internal-first` added as a fourth option alongside the existing three — good addition 
- `election_of_remedies_context` instruction language — need to verify (see below)

***

### Issues Found

**1. `retro_date` / `retro_context` — Order Not Fixed**

The proposal flagged this and the Effective Date Tab still reads:

```
- `retro_date`    — (sister field to `retro_context`)
- `retro_context` — (conditional on `retroactive-date` in `legal_recognitions`)
```

The trigger field (`retro_context`) still follows its sister (`retro_date`). Per the naming convention, context fields are the primary conditional and sisters inherit from them — the sister should be listed second. The slug-to-companion map correctly shows `retro_context + retro_date` (trigger first, sister second), but the Effective Date Tab field order contradicts it. This is a documentation inconsistency that will confuse whoever implements the ACF registration — it needs to match the slug map. 

**Fix:** Swap order in Effective Date Tab:
```
- `retro_context`  — (conditional on `retroactive-date` in `legal_recognitions`)
- `retro_date`     — (sister field to `retro_context`)
```

***

**2. `protected_action_context` — Slug Map Key Mismatch (Runtime Bug Risk)**

The Classifications Tab field definition reads:
```
- `protected_action_context`  — (conditional on `protected-action` in `legal_recognitions`)
```

But the slug-to-companion map entry reads:
```
'protected-action' → 'protected_actions_context' + ...
```

The map uses `protected_actions_context` (plural), the tab definition uses `protected_action_context` (singular). These are two different field names. Whichever one gets registered in ACF, the other won't resolve. One of them is wrong. 

**Fix:** Decide canonical name and align both locations. Given that `protected_actions` (plural) is the taxonomy field name and the context is triggered by that field's population, `protected_action_context` (singular) is the more consistent choice — context fields use singular per naming convention (`*_context` not `*s_context`). Update the slug map to `protected_action_context`.

***

**3. `ws_protected_action` Hierarchy — Still Flat**

The proposal recommended adding `opposition-clause` and `participation-clause` as parent terms with existing terms re-parented under them. The taxonomy file needs to be checked, but the ACF field draft has no note acknowledging the hierarchy change or directing implementers to the taxonomy for the correct term structure. If the taxonomy is hierarchical but no note exists here, an implementer registering the ACF field may not know to use child terms. 

**Fix:** Add a parenthetical note on `protected_actions`:
```
- `protected_actions`  — (sister field to `protected_action_context`; taxonomy: `ws_protected_action`;
                          hierarchical — use child terms under `opposition-clause` or `participation-clause`
                          parent terms; parent terms alone are not sufficient)
```

***

**4. `election_of_remedies_context` — Instruction Content Not Verifiable in This Read**

The proposal specified updating the field instruction to direct researchers to capture election timing (irrevocable at filing vs. after final determination). The field appears in the Substantive-Record Common Fields section under Process & Remedies Tab, but the read truncated before reaching it fully. Based on the visible context the field is present but instruction content wasn't confirmed. 

**Verify:** Confirm `election_of_remedies_context` description includes election timing language from the proposal.

***

**5. `substantial-motivating-factor` Seed — Not Reflected in ACF Draft**

The ACF draft has no note in the Burden of Proof Tab or Taxonomy Reference section acknowledging the new `substantial-motivating-factor` term in `ws_causation_standard`. It doesn't need a new field, but the taxonomy reference section should note the addition so implementers know the term exists. 

**Fix:** Add to Taxonomy Reference notes under `ws_causation_standard`:
```
New terms added in this version:
- `contributing-factor-but-for-backstop`
- `substantial-motivating-factor`
```

***

**6. `anti_slapp_context` — Slug Mismatch**

In the Classifications Tab, the field is listed as:
```
- `anti_slapp_protection_context`  — (conditional on `anti-slapp-protection` in `legal_recognitions`)
```

But the slug-to-companion map uses:
```
'anti-slapp' → 'anti_slapp_context'
```

Three-way inconsistency: the taxonomy slug (`anti-slapp` vs. `anti-slapp-protection`), the field name (`anti_slapp_protection_context` vs. `anti_slapp_context`), and presumably the taxonomy seed. At least one of the slug or field name is wrong — they need to match. 

**Fix:** Pick one canonical slug/field name, align all three locations (Classifications Tab, Waiver & Scope Tab where the field lives, and the slug map). Recommend: `anti-slapp` slug, `anti_slapp_context` field — shorter and already in the map.

***

### Summary

| # | Issue | Severity | Fix Required |
|---|---|---|---|
| 1 | `retro_date`/`retro_context` order still wrong | Low | Swap order in Effective Date Tab |
| 2 | `protected_action_context` vs. `protected_actions_context` name collision | **High** | Align slug map to singular form |
| 3 | `ws_protected_action` hierarchy not noted on field | Medium | Add parenthetical note |
| 4 | `election_of_remedies_context` instruction not confirmed | Medium | Verify content |
| 5 | New causation standard seeds not noted in taxonomy reference | Low | Add version note |
| 6 | `anti-slapp` slug/field name mismatch across three locations | **High** | Pick one canonical form, align all |

Issues 2 and 6 are the ones that will cause a runtime ACF conditional registration bug — both are slug/name mismatches between where a field is defined and where it's referenced.

---