# Proposal: Derived Engagement Layer for Assist-Org Directory

## Purpose

The current assist-org schema already captures many first-contact and accessibility signals:

- `phones[]` with typed values (`hotline`, `intake`, `headquarters`, `regional`, `tty`, `fax`, `other`)
- `emails[]` with typed values (`intake`, `general`, `legal`, `media`, `support`, `other`)
- `intake_url`
- `contact_url`
- `has_secure_channel`
- `secure_contact_url`
- `secure_contact_tool`
- `_review_notes`

This is enough raw material to derive a more useful **engagement layer** for scoring and rendering, without creating a large new research taxonomy.

The key distinction is:

- `ws_process_type` = what legal / administrative pathways an org can help with
- **engagement layer** = how a user can realistically begin and sustain contact with the org

These are related, but not the same.

---

## Why this matters

For high-priority end users (especially Maya, then James), engagement mechanics directly affect:

- urgency
- anonymity
- friction
- accessibility
- emotional burden at first contact

A directory card should privilege actionable information:
- Is there a hotline?
- Is there an intake path?
- Is there a secure path?
- Is there only a headquarters number or general contact page?
- Is there any accessibility support such as TTY?

This is distinct from the deeper profile page, where `ws_process_type` is highly useful for researchers, journalists, lawyers, and advanced users.

---

## Recommendation

Do **not** create a new upstream taxonomy yet.

Instead, derive one or both of the following in code:

### Option A: `engagement_profile`
A derived set of flags / labels used by render and sort logic.

Example derived flags:
- `has_hotline`
- `has_intake_phone`
- `has_intake_email`
- `has_intake_url`
- `has_general_contact_url`
- `has_secure_channel`
- `has_tty`
- `contact_is_hq_only`
- `contact_is_media_only`
- `contact_is_fallback_only`

### Option B: `engagement_score_inputs`
A normalized structure used only by the scoring system.

Example:
```php
[
  'phone_hotline'        => true,
  'phone_intake'         => false,
  'email_intake'         => true,
  'url_intake'           => true,
  'url_general_contact'  => false,
  'secure_channel'       => true,
  'tty_available'        => false,
  'hq_only'              => false,
  'media_only'           => false,
]
```

Option A is better for readability in render.
Option B is cleaner for scoring internals.
Both can coexist if one is derived from the other.

---

## Suggested derivation rules

### Positive engagement signals
These support first-contact usability and may contribute to sort order:

- `hotline` phone type present
- `intake` phone type present
- `intake` email present
- `support` email present
- `intake_url` present
- `has_secure_channel = yes`
- `tty` phone type present

### Neutral / weak signals
These may be useful fallback contact paths, but are not ideal front-door routes:

- `general` email present
- `contact_url` present
- `regional` phone present

### Low-value / caution signals
These should not disqualify an org, but may reduce front-door usability:

- `headquarters` is the only phone
- `media` is the only email
- `fax` is the only contact method
- no phone, no email, no intake URL, no secure channel

### Review-sensitive signals
These should come from `_review_notes` or future structured fields, not inferred too aggressively:

- account required before intake
- long callback delays / backlog
- third-party scheduler required
- partner referral only
- intake temporarily closed

These may later support a friction or caution layer, but should not be over-automated yet.

---

## Scoring use

The engagement layer should influence sort order lightly but meaningfully.

### Maya-first weighting
Maya benefits most from:
- secure first contact
- hotline or clear intake path
- low-friction contact options
- accessibility support
- no need to decode legal process language first

Suggested boosts:
- secure channel
- hotline
- intake URL
- intake email / intake phone
- TTY support

Suggested non-boosts or mild penalties:
- only headquarters line
- only media email
- no actionable contact path

### James weighting
James may still benefit from the above, but process and representation may matter more once retaliation is active.

For James, engagement signals should still help, but they should work alongside:
- case stage fit
- disclosure type fit
- attorney presence
- process-type fit

### Daniel weighting
Daniel likely cares less about engagement score and more about completeness, scope, legitimacy, and detailed profile depth.

This supports the current product idea:
- directory card = actionable and calm
- detailed internal profile = richer, more technical, more archival

---

## Rendering use

The derived engagement layer can feed calm, non-influential card UI:

Examples:
- `Secure intake available`
- `Hotline available`
- `TTY line available`
- `Online intake form`
- `General contact only`

Avoid language that implies recommendation, urgency, or endorsement.

This fits the existing plan for the optional internal detail page:
- card = actionable minimum
- `[more information]` text-link = deeper internal profile
- detailed profile = context for secondary users and tie-break thinking, not a “must-click” warning

---

## Why not use `ws_process_type` for this

`ws_process_type` models the legal or procedural pathways an org can help navigate:
- administrative complaint
- civil lawsuit
- qui tam
- internal disclosure
- regulatory tip
- criminal referral
- state agency complaint
- congressional disclosure
- representative action

That taxonomy is valuable, but it answers a different question:
**“What kinds of formal processes can this org help with?”**

The engagement layer answers:
**“How can the user actually reach this org, and how usable is that first contact path?”**

Both matter.
They should remain separate.

---

## Recommendation summary

- Keep `ws_process_type` as-is.
- Keep current phone/email type enums as-is for now.
- Derive a separate engagement layer from existing contact fields.
- Use that engagement layer for:
  - small scoring adjustments
  - actionable card badges / labels
  - future friction-aware UX improvements
- Do not over-automate review-note nuance yet.
- Revisit the enums only if repeated real-world patterns cannot be expressed cleanly.