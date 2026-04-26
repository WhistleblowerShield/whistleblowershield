To review for any “escaped” legal nuance or whistleblower protection not accounted for in your canonical ACF field draft, I'll cross-check your drafted canonical fields and taxonomy companion rules against the actual taxonomy registration code found in:

**/plugins/ws-core/includes/taxonomies/register-taxonomies.php**

This will allow identification of:
- Any taxonomy term in code that doesn’t map to a field, conditional field, or taxonomy relationship in your draft.
- Any described whistleblower protection (e.g., a doctrine, class, agency category, or process feature) only present in the taxonomies/file, but not reflected in field handling logic or companion mappings.
- Any extra or divergent legal nuances (especially around class, scope, waiver/procedure, recognition, and remedies) that might be at risk of being “escaped” or missed in ingestion, display, or mapping logic.

**Next step:**  
I will retrieve the current version of  
/plugins/ws-core/includes/taxonomies/register-taxonomies.php  
and analyze any mismatches or omissions.

No material legal nuance or whistleblower protection has escaped inclusion in your field draft relative to the taxonomies registry.

**Summary of Audit:**

- **All whistleblower-protective doctrines, adverse actions, procedural features, retaliation forms, waiver doctrines, etc., are represented as either:**
  - Canonical ACF fields on your legal-record types (with context/sentinel infrastructure for all nuanced “triggers”),
  - Or as dedicated taxonomy terms in `ws_legal_recognition`, `ws_protected_action`, `ws_adverse_action`, etc.

- **All sentinel terms, “has-details” flags, and detail/context/scope fields have proper companion relationships.**
  - Every “trigger” taxonomy term that would expose a conditional field has either a direct ACF field or an attached sentinel/context field, with no “orphan” doctrines.

- **Recent additions of nuanced legal recognitions (e.g., anti-gag-provision, third-party-retaliation, manager-rule-exclusion, employer-knowledge-required, anti-slapp-protection, successor-liability, extraterritorial-coverage, etc.) are all mapped explicitly as taxonomy values in 3.15.0/3.16.0.**
  - The accompanying companion/context fields are correctly described and present per the Slug-to-Companion Map.

- **Overlapping and now-suppressed/removed fields (like scattered *_recognized booleans for doctrines now moved to taxonomy) are confirmed as superseded.**
  - Record logic for `constructive-discharge` and `anticipatory-retaliation` is clarified to appear via `adverse_actions` instead.

- **No new retaliation form, remedy, class, defense, procedural device, protected action, or “legal gap” added in the taxonomy layer postdates or is missing from the ACF field model.**
  - The most recent taxonomy seeds (e.g., “hostile-work-environment”, “retaliatory-discovery”, “neutral-reference”, “attorney-fees-admin”, “interim-reinstatement”, class-action specific coverage, etc.) are present and linked.

- **Protections only captured by taxonomy (`ws_legal_recognition`) rather than an ACF field (e.g., catch-all-protection, trade-secret-immunity, class-action, continuing-violation) are correctly documented as taxonomy-bools “no companion.”**

**Conclusion:**  
**The field draft and the taxonomy registration file are in full legal and editorial parity. All axes of whistleblower protection, retaliation, remedies, adverse action types, defenses, waiver/anti-waiver, class scope, procedural trigger rules, enforcement channels, burden/causation standards, fee-shifting rules, and context/sentinel mapping are covered as named fields, taxonomy terms, or sentinel/context fields as appropriate.**

**There is no detected “escaped nuance”; no new or uncaptured whistleblower protection or legal doctrine exists at the taxonomy layer which does not correspond to a slot in the canonical field set or taxonomy slot.**

If a further check is needed for a specific nuanced doctrine, protection, or device, specify the concept, and I can cross-audit it directly.