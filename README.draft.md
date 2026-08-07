# WhistleblowerShield

WhistleblowerShield is a public-interest legal reference project for people trying to understand whistleblower protections in the United States.

The project exists for two people first:

- **Maya**, who is thinking about coming forward and needs to know whether the law may protect her before she takes the next step.
- **James**, who already reported something and is now facing retaliation, uncertainty, deadlines, and the practical question of what to do next.

Researchers, lawyers, advocates, developers, and unusually patient taxonomy people are welcome here too. But Maya and James are the reason the system exists.

This is not a lead-generation site. It is not a pitch deck. It is not trying to impress investors. It is a structured legal knowledge system being built, for now, mostly by one person who would still prefer the thing be understandable if someone else finds the hard drive in 300 years.

## What the Project Is

WhistleblowerShield models whistleblower law as structured records, not as one-off pages.

A jurisdiction page is not treated as the source of truth. It is an assembled view created from smaller pieces:

- jurisdiction records
- summaries
- statutes
- common-law doctrines
- citations
- constructions
- agencies
- filing procedures
- assist organizations
- glossary concepts
- controlled vocabularies

The public page is an output. The record model underneath is the actual asset.

The core idea is simple enough:

> The statute is not always the final truth.
>
> The assembled legal meaning is the truth the reader needs.

A later citation or construction may narrow, expand, or clarify a statute. The system should preserve that chain and still present the reader with a coherent answer.

## Current Implementation

The main implementation lives in the `ws-core` WordPress plugin.

Current stack:

- WordPress
- ACF Pro
- PHP
- structured custom post types
- controlled taxonomies
- matrix seeders
- query-layer data contracts
- render/shortcode assembly
- prompt and ingest tooling under active development

This is still pre-launch in the important sense: there is no precious, hand-entered production database that must be preserved through migration compatibility layers. Some live or beta-facing pages may exist, but the operating doctrine remains that destructive architectural cleanup is allowed until the project formally says otherwise.

If that ever stops being true, this README should be updated immediately. Future-you will otherwise lie to yourself by accident, which is rude.

## The People This Serves

### Maya

Maya is considering whether to report wrongdoing.

She does not want a law review article. She wants to understand:

- Am I protected?
- What kind of report counts?
- Who can I report to?
- What should I avoid doing?
- What deadlines or traps matter before I act?

### James

James has already reported something and something has happened to him.

He needs to understand:

- Was this retaliation?
- What forum or agency might matter?
- What remedies might exist?
- What deadlines are easy to miss?
- Who can help?

If a feature does not eventually help Maya or James, it needs a reason to exist.

## How the System Thinks

WhistleblowerShield is built around several principles.

### Model the smallest useful legal unit

A statute, citation, construction, or common-law doctrine may each contribute one piece of the current legal answer. The system should model those pieces separately so they can be assembled honestly.

### Use controlled vocabularies

Taxonomies are not casual WordPress tags here. They are controlled vocabularies for legal concepts, directory metadata, and system axes.

### Keep retrieval out of rendering

Render functions and shortcodes do not call `get_field()`, `get_post_meta()`, or `WP_Query` directly. Data retrieval goes through the query layer.

If a render file starts learning how the database works, something is probably drifting.

### Fail loud

Silent failures are dangerous in a legal reference system. A missing term, failed query, bad enum, skipped write, or swallowed error should not quietly become plausible output.

If someone asks the legal code for a tennis match, it does not get to become a statute.

### Separate attribution from audit

Authorship, review, verification, and hidden audit history answer different questions.

Credit and chain-of-custody are intentionally not the same thing.

## Major Layers

A simplified view of the architecture:

```text
Vocabulary Layer
    taxonomies, glossary concepts, controlled terms

Schema Layer
    canonical field definitions and ACF generation

Workflow Layer
    authorship, review, verification, audit, major edits

Query Layer
    normalized data retrieval and dataset contracts

Assembly Layer
    render functions and shortcodes

Presentation Layer
    public HTML, tooltips, formatting helpers, CSS/JS
```

See `documentation/architecture-overview.md` for the expanded version once that file is promoted into the formal documentation set.

## Project Status

The project is in active development.

Some systems are stable enough to document. Others are knowingly transitional.

Known current truths:

- The query-layer doctrine is real and implemented in code.
- The taxonomy registry is the source of truth for core taxonomies.
- The legal-record schema rewrite is underway, with `jx-statute` leading the transition.
- Citation, construction, and common-law records still contain older schema patterns in places.
- Glossary tooling exists as a proof of concept, not as a settled concept layer.
- Prompt and ingest tooling are evolving and should not be treated as final until reviewed against live code.

Before rewriting documentation, read the code.

Before trusting old documentation, read the code.

Before trusting memory, read the code and then maybe drink water.

## Contributors

WhistleblowerShield is currently maintained by a very small project team, often functionally one person.

That is a constraint, not a philosophy.

Thoughtful contributors are welcome to apply their patience, expertise, and skepticism. Useful help may come from:

- whistleblower attorneys
- legal researchers
- labor and public-accountability advocates
- accessibility-minded writers
- WordPress developers
- data modelers
- taxonomy and ontology people
- public-interest technologists

The project is opinionated. Accuracy matters. Sources matter. Plain-English clarity matters. Maya and James matter more than contributor ego, including the maintainer's.

## Documentation Doctrine

Documentation is part of the system, not decoration.

The project distinguishes:

```text
Current reality
Current design intent
Historical rationale
```

Those three things must not be quietly merged into one confident paragraph.

If something is a proposal, call it a proposal.

If something is current code truth, cite the file that proves it.

If something is old but useful, archive it instead of pretending it is current.

## Where to Start

Recommended reading order for a new human or future maintainer:

1. This README.
2. `documentation/architecture-overview.md` once promoted.
3. `documentation/design-principles.md` once promoted.
4. `plugins/ws-core/README.ws-core.md`.
5. `plugins/ws-core/includes/loader.php`.
6. `plugins/ws-core/includes/queries/`.
7. `plugins/ws-core/includes/taxonomies/register-taxonomies.php`.
8. The current legal-record schema documents under `in-progress/`, with caution.

If a document disagrees with live code, live code wins until someone proves otherwise.