# Documentation README

**Status:** Published. 
**Purpose:** Explain how the WhistleblowerShield documentation set is organized, what counts as current, and how to avoid converting old notes into false certainty.

This file is the entry point for project documentation. It is not a product overview and not a public-facing explanation of whistleblower law.

WhistleblowerShield documentation exists to preserve:

```text
what the system does
why it does it that way
what was tried
what failed
what survived
what is still unsettled
```

If the documentation cannot answer those questions, it is not doing its job.

## Documentation Rule One

Live code wins over stale documentation.

That does not mean code is always conceptually correct. It means code is the current implementation until changed.

When a document and the code disagree:

1. inspect the code;
2. determine whether the code is settled or transitional;
3. update the documentation only after the disagreement is understood.

Do not resolve a contradiction by choosing the paragraph that sounds better.

## Three Kinds of Truth

Every major claim in the documentation should belong to one of these categories.

### Current Reality

True in the code now.

A current-reality claim should be backed by a file, function, or visible behavior.

Example:

```text
The query layer is loaded in the Universal Layer.
```

This is a current-reality claim if `loader.php` does it.

### Current Design Intent

A plan or direction that may not be fully built.

Example:

```text
The legal-record schema is moving toward a shared canonical field model.
```

This may be true as design intent even if only one record type fully implements it today.

### Historical Rationale

Why a decision was made, including failed attempts.

Example:

```text
Fail-loud handling moved earlier in the loader because the first placement was not available to every file that needed it.
```

Historical rationale should be preserved when it prevents the same mistake from being repeated.

## What This Folder Is Not

This documentation folder is not the public site.

Do not duplicate public-facing legal explanations here unless they are needed to explain the system.

This folder is for maintaining and extending WhistleblowerShield, not for advising readers about their rights.

## Current Documentation Priorities

The current priority is reconciliation.

That means:

```text
read code
identify stale docs
separate current reality from design intent
preserve useful history
delete or archive misleading duplicates
```

Do not rewrite everything at once.

A bad rewrite can make old confusion look official.

## First-Pass Canonical Set

The first documentation pass should establish a small set of files that other documents can point to.

Suggested starting structure:

```text
documentation/
    README.documentation.md

documentation/-start-here-/
    architecture-overview.md
    design-principles.md
```

The `-start-here-` files should contain settled or carefully qualified truths.

## File Naming

Use distinctive filenames.

Prefer:

```text
README.documentation.md
architecture-overview.md
design-principles.md
```

Avoid creating a dozen generic files named:

```text
README.md
todo.md
notes.md
overview.md
```

Generic names are convenient for five minutes and annoying for years.

## Drafts and Staging Notes

Drafts, chat captures, and temporary review notes should stay outside the project tree until promoted deliberately.

Reason:

Another agent or future reviewer may treat any file inside the project folder as canonical or actionable.

A staging note is evidence.

A canonical doc is instruction.

Do not confuse them.

## Archive Policy

Archive files that preserve useful historical reasoning.

Delete files only when they are:

- duplicated elsewhere,
- no longer accurate,
- and contain no unique reasoning worth preserving.

Old documents are not automatically bad. Silent stale documents are.

## Rejection Checklist

Before promoting a documentation draft, check:

- Does it state a proposal as fact?
- Does it describe code that has not been read?
- Does it blur current reality, design intent, and historical rationale?
- Does it explain what was done without explaining why it mattered?
- Does it sound like marketing?
- Does it reach beyond the current scope without labeling that move?
- Does it use project jargon without explaining what mistake the jargon prevents?
- Does it duplicate public-facing site content unnecessarily?
- Does it use a generic filename likely to collide with other files?

If yes, revise before promoting.

## Current Scope for This Documentation Pass

The current documentation pass should focus on:

```text
57 jurisdictions
legal records
controlled vocabularies
query and assembly structure
plain-English and verification workflows
assist organizations
documentation governance
```

Agencies and procedures exist in the codebase but should not become the center of this pass unless a specific review confirms their current role.

Prompt and ingest tooling should be documented carefully because parts are active, parts are transitional, and parts are intentionally deferred.

## What Good Documentation Should Preserve

A good WhistleblowerShield document should preserve the record.

That includes:

- why a design exists;
- what problem it solved;
- what failed before it worked;
- where the current implementation lives;
- what remains unsettled;
- what future maintainers should not accidentally undo.

If the document only describes the final shape and hides the reasoning, it is incomplete.