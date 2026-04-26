# Final Pass — Legal Nuances Still Escaping the 3.16.0 Draft

I've read the canonical field draft and the 3.15.0 taxonomy seed again line by line. Even with the worker-status, authorized-recipient, and legal-recognition additions from the implementation proposal, there are still about a dozen whistleblower-specific doctrines that have no clean slot in the schema. They are not academic — they show up in motions to dismiss.

These are the ones that will force you into `*_details` freetext unless you add them now.

---

## 1. Professional responsibility carve-outs

**What is missing:** no field for attorney-client privilege, physician confidentiality, or auditor duty of confidentiality.

- In-house counsel whistleblowers lose protection under most state laws if the disclosure breaches Rule 1.6. SOX has a narrow exception. Your schema has `protected_action_source = judicial` and `anonymity-protection`, but nothing that records "privileged information excluded" or "DTSA immunity applies."
- **Impact:** you cannot filter for "states where lawyers can blow the whistle on client fraud without losing license."

**Minimal fix:** add to `ws_legal_recognition`:
- `attorney-privilege-carveout` → `attorney_privilege_context`
- `trade-secret-immunity` → `trade_secret_context` (for 18 U.S.C. §1833 DTSA immunity)

## 2. Participant in wrongdoing exclusion

**What is missing:** many statutes deny protection if the whistleblower planned or participated in the violation (e.g., FCA retaliation, some state false claims acts).

You have `bad-faith-exclusion` for knowingly false reports, but not for complicity. That's a different element — good faith belief can be true even if the reporter helped commit the fraud.

**Minimal fix:** add `participant-exclusion` to `ws_legal_recognition` with `participant_exclusion_context`. Do not conflate with bad faith.

## 3. Classified national security channeling

**What is missing:** `ws_disclosure_type` has a "National Security" parent, but no way to record that protection is lost if disclosure is not made through cleared channels (ICWPA, PPD-19).

Your `authorized_recipients` taxonomy helps, but it does not capture the criminal exposure for improper disclosure.

**Minimal fix:** add to `ws_legal_recognition`:
- `classified-channel-required` → `classified_channel_context`

## 4. Written vs oral reporting requirement

**What is missing:** several state retaliation statutes require a written complaint to trigger protection. Others protect oral reports. You have `disclosure_channel_scope`, but not format.

**Impact:** determines whether Maya's verbal report to HR is protected.

**Minimal fix:** add field to Classification tab:
- `reporting_format_required` — single-select: `written-only`, `oral-permitted`, `either`, `has-details`
- `reporting_format_details`

## 5. Internal-first sequencing

**What is missing:** `authorized_recipients` lists who, but not the order. Some laws (healthcare whistleblower statutes) require internal report first, then external after X days.

You have `exhaustion-required`, but that's for administrative filing, not for the disclosure itself.

**Minimal fix:** add to Classification tab:
- `has_internal_first_requirement`
- `internal_first_wait_days` (integer, sister field)
- `internal_first_details`

## 6. Mitigation of damages duty

**What is missing:** almost every retaliation remedy requires the plaintiff to mitigate lost wages. Failure to mitigate reduces back pay. No field captures whether the jurisdiction imposes a statutory duty to mitigate or a specific standard.

**Minimal fix:** add to Enforcement tab, after `remedy_limits`:
- `mitigation_required` — single-select: `yes-statutory`, `yes-common-law`, `no`, `has-details`
- `mitigation_details`

## 7. Emotional distress and non-economic damages architecture

**What is missing:** `ws_remedy` has `punitive-damages`, but does not distinguish compensatory emotional distress, reputational harm, or statutory caps on non-economic damages. You currently rely on `remedy_limits`.

**Impact:** you cannot answer James' question "can I get damages for anxiety" without reading freetext.

**Minimal fix:** add to `ws_remedy`:
- `emotional-distress-damages`
- `reputational-harm-damages`
Keep caps in `remedy_limits` context.

## 8. Collective bargaining preemption

**What is missing:** no way to record that a whistleblower claim is preempted by a CBA grievance procedure (common in public sector). You have `preemption_direction` for federal/state, but not for labor contracts.

**Minimal fix:** add to `ws_legal_recognition`:
- `cba-grievance-preemption` → `cba_preemption_context`

## 9. Same-actor inference and temporal presumption rebuttal

**What is missing:** you capture `has_temporal_presumption` with window, but not the evidentiary effect. Some courts apply a "same actor" inference (if same manager hired and fired, no retaliation). Others shift burden back to employer.

**Minimal fix:** add to Burden of Proof tab, after `presumption_window_details`:
- `presumption_effect` — single-select: `shifts-burden`, `creates-inference`, `rebuttable-presumption`, `has-details`

## 10. Foreign authority disclosures

**What is missing:** `extraterritorial-coverage` covers geographic reach, but not whether disclosure to a foreign regulator is protected. FCPA and export-control whistleblowers often report to non-U.S. authorities.

**Minimal fix:** expand `ws_authorized_recipient` government branch to include `foreign-regulator` child term. No new field needed.

## 11. Attorney fees for pro se and frivolous defense

**What is missing:** your `fee_shifting_asymmetry` adds one-way/two-way, but does not capture the specific "prevailing defendant for frivolous claim" standard that appears in SOX and many state laws.

**Minimal fix:** add option to `fee_shifting_asymmetry`: `defendant-frivolous-only`

## 12. Expungement and make-whole equitable relief

**What is missing:** reinstatement is covered, but not expungement of personnel file, neutral reference, or posting of notice — common make-whole remedies.

**Minimal fix:** add to `ws_remedy`:
- `expungement`
- `neutral-reference`
- `posting-notice`

---

## Why these matter for your two personas

Maya needs to know if her oral report to a supervisor counts, and whether her prior participation in the billing scheme disqualifies her. James needs to know if he must mitigate, whether his union grievance blocks his lawsuit, and if he can get his personnel file cleaned.

Without these fields, both answers fall back to "see details" — which defeats the structured-data promise.

## Recommendation

Do not expand the 3.16.0 scope further — you are already at risk of field bloat. Instead, create a `v3.16.1-legal-nuance` patch that adds only the taxonomy terms and the seven new fields listed above (reporting_format, internal_first, mitigation, presumption_effect, plus the three legal_recognition terms with highest litigation frequency: `attorney-privilege-carveout`, `participant-exclusion`, `classified-channel-required`).

That keeps the reboot deliverable shippable while ensuring the next ingest run for CA and WY does not have to be re-done when these doctrines surface in the case law.

---

