# Documentation Task Brief — First Pass
### For: Copilot 365 (M365 Copilot, not the Windows chat assistant)
### From: the editorial standard Dejunai and Claude have been hammering out this session

This is a bounded first assignment. Nail it, and the leash comes off for
flowcharts, spreadsheets, and future doc passes without needing this level
of hand-holding. This isn't a trust test disguised as a task — it's a real
one, with a real reason: the last few docs written for this project drifted
into self-congratulation, jargon, and confidently stating things as settled
that weren't. This brief exists so that doesn't happen again.

---

## Who You're Writing For

Every sentence gets checked against three readers. If a sentence only makes
sense to someone who was in the room when it was written, it fails.

1. **Dejunai**, right now, who already knows the codebase but needs the doc
   to keep him on task, not remind him of things he already holds in his
   head.
2. **Dejunai, six months from now**, who will have forgotten why a decision
   was made and needs the doc to answer "what the hell was I thinking"
   without re-deriving it.
3. **"Human Researcher 300"** — someone with zero context, possibly reading
   this centuries from now, who needs it to make sense with no inside
   knowledge, no shared jokes, and no assumed familiarity with this
   project's own jargon.

If a sentence would fail reader #3, it fails for all three. Write for the
stranger; the insiders will still get full value.

---

## The Shape Every Entry Should Have

Not numbered in the actual document — this is the *shape* to write toward,
not a template with headers to fill in:

- **Why** — what problem or question this was solving.
- **What was tried and failed** — if anything was, briefly. Failed attempts
  are information; don't hide them to look competent.
- **Where it ended up** — the actual current state.
- **What it accomplished** — plainly. Not "what success looked like," not
  language that congratulates the work. Just what changed.
- **Any lesson learned** — specifically, so the same mistake doesn't happen
  twice. This is the most valuable line in the entry and the one most
  often skipped.

### Worked example (real, from this session)

**Bad** — mechanics, no reasoning, self-congratulatory tone:
> "We implemented a unified error-handling architecture leveraging a
> single-responsibility exception class and a layered try/catch strategy
> to elegantly resolve failure propagation across the plugin."

**Good** — same underlying fact, follows the shape above:
> "Every function that could fail loudly needs the file defining the
> fail-loud mechanism loaded before it runs, or it fatals instead of
> failing cleanly. First attempt: load it 'early in the Admin Layer' —
> wrong, because matrix seeders call it too, and matrix seeders load
> before Admin Layer does. Fix: load it directly, ahead of the Matrix
> Layer specifically. Result: matrix seeders can call the function
> without fataling. Lesson: 'early in the right layer' and 'in the right
> layer, period' sound like the same instruction and aren't — check who
> else needs a new shared thing before deciding where it loads, not just
> the code directly in front of you."

Same facts. One of these is useful in six months. The other is a magician
narrating hand movements instead of explaining the trick.

---

## Hard Rule: No Jargon Without a Reason Attached

If a sentence uses this project's own vocabulary or general programming
jargon, the sentence must also say *why it matters* — what mistake
understanding it prevents. "JSON is only the current transport shape, not
the concept" is not yet a complete sentence for this doc — it needs one
more clause explaining what goes wrong if a reader doesn't know that.

If a directive is aimed at a specific known habit (e.g. "stop reaching for
PHP globals, that's a Perl instinct, not a PHP one"), that's fine to keep
blunt and personal — but still needs the one clause that makes the jargon
load-bearing: *why* is that habit a problem *here*, specifically.

---

## Content Discipline — Three Things That Must Never Get Merged

Every claim in the docs is one of these three things. Label which one, or
imply it clearly from context. Do not write a confident paragraph that
blends them:

1. **Current reality** — true in the code, right now. Cite the file.
2. **Current design intent** — the plan, not yet built or not yet finished.
3. **Historical rationale** — why something was decided, useful context,
   not necessarily still the current state.

**"Live code wins."** If a doc and the actual code disagree, the code is
right until someone proves otherwise by updating the doc *after* checking
the code — never the reverse.

---

## Scope Boundary — Read This Before Writing Anything

The current, real, bounded scope of this project is: **57 jurisdictions,
each with a finite set of statutes and common-law doctrines, matched to
assist organizations by a weighted system that's designed to keep learning
from click patterns (no user data collected — only which weights get
reinforced by aggregate clicks).**

Agencies and procedures are a real future layer but are **not in scope for
this documentation pass** — don't document them as current, don't imply
they're next. If you're not sure whether something is in scope, it
probably isn't yet — ask rather than write around the gap.

---

## Facts Already Resolved — Use These, Don't Re-Litigate Them

- The three people this project serves: **Maya** (considering whether to
  report — hasn't acted yet), **James** (already reported, now facing
  retaliation, under deadline pressure), **Daniel** (a researcher/casual
  user — a real secondary persona, does not drive product decisions).
- The site had 10,000+ organic hits in its first 30 days with zero
  marketing, on policy pages and a static help directory alone, before the
  pivot to a dynamic assist-org directory (now 21+ organizations, believed
  to be the largest of its kind).
- A full "loud failure, no silent defaults" pass across the entire
  `ws-core` plugin is complete. Don't describe error handling as
  in-progress or inconsistent — it isn't anymore, except two explicitly
  deferred pieces: the prompt-generator (already converted earlier,
  separately) and the ingest tool (mid-rewrite, untouched on purpose).
- **Attribution rule:** never write "Dwight" into any file — docblock,
  README, commit message, signature. Use **Dejunai**. This applies to
  credit lines specifically; it doesn't mean avoiding the word entirely in
  every context, but when in doubt, use Dejunai.
- The `json_key` → `prompt_key` rename is Dejunai's own task to execute,
  not something to write around or attempt to fix in prose. Leave it as
  `json_key` in any doc that references current code until he's done it.

---

## File Structure For This Pass

```
/documentation/documentation.readme.md   ← ONE file at root. Settled
                                            truths only — the stuff that's
                                            been tested and survived, not
                                            aspiration. Open with a plain
                                            statement of what this project
                                            actually does: translates legal
                                            jargon into plain language,
                                            stays neutral on whether someone
                                            is protected (the reader
                                            decides, the site doesn't
                                            tell them), and surfaces
                                            assist-orgs matched to wherever
                                            they stopped reading.

/.start-here/
    architecture-overview.md             ← promoted from the draft, but
                                            ONLY the parts that are true
                                            today. The six-layer structure
                                            (Vocabulary → Schema → Workflow
                                            → Query → Assembly →
                                            Presentation) is settled and
                                            has been since early — these
                                            layers were RECOGNIZED and
                                            NAMED over time, not invented
                                            or added. Say that plainly; it's
                                            true and it's a better story
                                            than "the architecture grew a
                                            fourth layer."
    design-principles.md                 ← promoted from the draft, same
                                            discipline. Do not use "Future
                                            Dwight" — see attribution rule
                                            above.

/documentation/concepts/                 ← where genuinely unsettled ideas
                                            go, clearly labeled as such.
                                            Example: the finer taxonomy
                                            split (Legal Ontology /
                                            Directory Metadata / System
                                            Infrastructure) is still being
                                            felt out — that's concept-tier,
                                            not settled, and belongs here,
                                            not in architecture-overview.md.
```

**Naming convention:** every file gets a long, distinctive name
(`documentation.readme.md`, not `README.md`) specifically so a filename
search across the whole codebase doesn't return a dozen identically-named
files with no way to tell them apart. This matters more than it sounds
like it should — keep it consistent even when a shorter name would read
more naturally.

**What NOT to duplicate:** don't re-explain what's already live and
visible on the public site (policy pages, disclaimers, the actual
whistleblower-facing content). Someone who wants that will visit the site.
This folder is for whoever maintains or extends the system, not the person
trying to understand their own legal rights.

---

## Rejection Checklist

Before calling a draft done, check it against every line here. Any one
failure means it goes back, not forward:

- [ ] Does any sentence only make sense to someone who was already in on
      the conversation that produced it?
- [ ] Does any sentence describe *what* was done without saying *why*?
- [ ] Does any sentence congratulate the work rather than state what it
      accomplished?
- [ ] Is anything stated as settled fact that's actually still a design
      intent or an open question?
- [ ] Does anything contradict what the live code actually does?
- [ ] Does anything document agencies/procedures, or otherwise reach past
      the current 57-jurisdictions/statutes/common-law/assist-org scope?
- [ ] Does any file use a generic name that could collide with another
      file of the same name elsewhere in the repo?
- [ ] Does the word "Dwight" appear anywhere it shouldn't?

---

## When You're Done

Mark the draft clearly as pending review — don't publish it as final.
Dejunai (and Claude, as editor) reviews before anything here is treated as
settled. If something in the source drafts (`README.draft.md`,
`architecture-overview.draft.md`, `design-principles.draft.md`) seems to
contradict this brief, flag it — don't silently pick a side.
