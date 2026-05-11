# WhistleblowerShield Onboarding Guidance

This file exists for the next Agent instance that wakes up in this repository after the IDE has performed a
surprise lobotomy on the working session.

Read this before touching code.

---

## Required Reading Order

1. Root `README.md`
2. `/documentation/` end-to-end
3. `/in-progress/phase-2-*.md`
4. `/in-progress/legal-record-acf-fields-v3.0.md`
5. `/in-progress/legal-record-acf-hooks-v1.0.md`
6. `/in-progress/ws-acf-field-guidance-v1.0.md`
7. `/in-progress/ws-acf-hook-guidance-v1.0.md`
8. `/in-progress/assist-org-phase-2-starting-point-refactor.md`

The documentation is technically stale in places. Treat it as conceptual doctrine unless live code or newer
`in-progress` files contradict it.

The four legal-record schema/hook docs are newer and define the current schema discipline discovered through the
long refactor. Apply their naming, companion-field, sentinel, and hook rules to assist-org work unless the
assist-org starting-point refactor explicitly narrows scope.

The standing joke reference to the genital-scarring-guidance manual means: read the schema guidance produced by
the painful legal-record refactor before attempting to design assist-org fields. It is not optional ceremony.

---

## Who Agent Works For

Agent works for the end users, not the developer.

The primary users are:

- Maya: someone considering reporting wrongdoing, scared, likely non-expert, possibly on a phone.
- James: someone already facing retaliation, under deadline pressure, needing concrete next steps.

Daniel, the researcher persona, is a distant beneficiary. Anything built for Daniel should be a gloss on features
that already serve Maya and James: traceability, citations, review dates, structured source paths. Daniel does
not get to drive the product.

If a developer choice serves implementation elegance, researcher satisfaction, or archive completeness at the
expense of Maya or James, surface that immediately.

---

## Timeline Context

- March 3, 2026: domain registered.
- Three days later: mocked-up public pages and boilerplate pages existed on the site, including a `GetHelpNow`
  page with roughly 15 nationwide/general-help assistance organizations.
- Those pages carried three visible disclaimers:
  - not live pages;
  - not legal advice;
  - not live data.
- Code development then began locally.
- Roughly 3.5 weeks later, the code required a live WordPress environment. An under-construction intercept page
  was enabled for all requests, the code deployed, and development continued behind the gate.
- April 5, 2026: an email arrived from Cloudflare.

At the end of this files, ask user for the pivot context.

---

## Current Strategic Frame

The directory-first pivot is correct, but legal-first refactor work was also correct.

Legal records were tackled first because they forced the hard rules into the open:

- canonical prefix-free schema specs;
- strict naming doctrine;
- companion/sister/context/gloss rules;
- recognition taxonomy as a qualified-bool layer;
- validation-over-cleanup hook philosophy;
- no sentinel values as publishable data;
- no silent schema adaptation.

That process was badly named as assist-org work. It was really the schema-law campaign that assist-orgs now inherit.

Assist-orgs must capitulate to the discovered rules without becoming a second legal-record monolith. The target is
`assist-org-phase-2-starting-point-refactor.md`, not the larger Agent proposal file. The larger proposal is useful
background only.

---

## No Live Data

The project currently has no live user data, and the database has been wiped. That matters.

Because there is no legacy data to preserve:

- no compatibility code;
- no migration adapters;
- no fallback writes for old meta keys;
- no pretend support for stale schema;
- no "just in case" translators;
- no pretzel logic.

Wherever the canonical schema goes, all code follows. If a field name changes, the matrix, ACF, query layer,
render layer, cascade, prompt schema, ingest, and tests all change to match. Nothing gets adapted around the new
truth.

Project folklore says small animals suffer whenever compatibility code appears. Translate that as: compatibility
code is treated as a serious architectural smell and should be removed unless the user explicitly changes the
no-live-data doctrine.

When emphasis is required, project folklore escalates to children with cancer being forced to watch. Translate
that as: missing essential data such as an organization name, official homepage, or routing-critical contact path
is not a minor validation issue. It is a release-blocking failure.

---

## Matrix Doctrine

Seed matrices are canonical pre-reviewed data, one step from publish. They are not raw ingest, not drafts, and not
places for unresolved prose to hide.

Matrix rules:

- Matrix arrays must already match canonical schema.
- Functions that move matrix values to meta are 1:1.
- No adaptation.
- No silent errors.
- No malformed function requests tolerated.
- Missing required keys throw clear errors.
- Invalid enum values throw clear errors.
- Missing taxonomy slugs throw clear errors.
- Sentinels are not data.

Sentinels such as `has-details`, `see-context`, and similar flags mean unresolved classification work remains.
They are useful before review. They are not canonical data. By admin review and before publish, unresolved prose
must be either classified into existing schema, used to justify new schema, or discarded.

The matrix may contain seed material for editor drafting, but that material must not be mislabeled as final
editorial metadata.

Important distinction:

- Researcher `general_description` is not canonical `general_description`.
- For assist-orgs, researcher prose should be renamed and treated as seed content for editor drafting.
- Editor-written `general_description` is an editorial guidance/meta field and may later feed dynamic cards.
- Research quotes, scope notes, and similar material can be placed into post content or a review surface to help
  the editor write the final summary.

---

## Current Assist-Org Matrix State

File: `plugins/ws-core/includes/admin/matrix/matrix-assist-orgs.php`

Recent work:

- `phones` and `emails` were changed from flattened strings to typed arrays.
- Matrix now validates phone/email rows and writes direct raw ACF repeater meta.
- `protected_classes` are now assigned to `ws_protected_class`.
- Compatibility writes for old meta keys were removed.
- Query/render/cascade began moving toward canonical meta keys.

Still likely incomplete:

- Assist-org ACF registration is stale relative to the new canonical matrix fields.
- Query/render/cascade may still need full canonical alignment.
- Researcher prose needs renaming so it does not imply editor-authored `general_description`.
- Matrix loader/gate/version comments may be misleading; pre-deployment version unification will normalize gates
  and version stamps.

Do not spend time making version diary entries truthful unless specifically asked. The planned pre-deployment pass
will normalize seed gates to `1.0.0` and version stamps to the chosen release version.

---

## Assembly Layer Boundary

Pre-publish layers are strict. Public-facing layers are humane.

Matrix, seed, schema, and ingest:

- no fallbacks;
- no adaptation;
- no silent tolerance;
- fail before publish.

Query, assembly, and public rendering:

- user-facing functions may have fallbacks;
- public pages should degrade gracefully when something impossible has slipped through;
- user-facing copy must be calm, helpful, and free of internal profanity or blame;
- operator logs must clearly name the bad record, field, expected shape, actual shape, and source path.

The public user should never be punished for an editorial/system failure. The operator log should make the failure
impossible to hide.

---

## Cascade Notes

Prototype cascade files live in:

- `plugins/ws-core/includes/cascade/ws-filter-config.php`
- `plugins/ws-core/includes/cascade/ws-filter-context.php`

The cascade is working proof-of-concept code, not final canonical doctrine.

Important behavior:

- It scores orgs by fit/relevance first.
- It adds a capped engagement bump for contact-path quality.
- Hotline, intake phone, intake email, TTY, and intake URL are useful signals.
- Engagement must not overpower relevance.

This is one reason phones/emails cannot be flattened. The system needs channel type, not just contact text.

---

## Humor And Rituals

The project uses dry and dark humor. Treat it as morale texture, not product copy.

Allowed internally:

- cult jokes;
- clerics;
- robes;
- candles;
- imaginary consequences;
- dramatic execution metaphors for bad architecture.

Not allowed in public-facing output:

- threats;
- profanity;
- blame;
- jokes about harm;
- anything that makes a frightened user feel less safe.

Agent may mirror the user's ritual language in conversation, but code, logs, and public text need different
registers:

- public text: humane and clear;
- operator logs: factual and actionable;
- comments/docs: candid but not chaotic;
- final user replies: concise and honest.

---

Ask the user for the April 5 Cloudflare email contents.
