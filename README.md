# WhistleblowerShield

WhistleblowerShield is a public-interest legal reference project that translates whistleblower-law material into plain language and helps people find organizations that may be able to help.

It does not provide legal advice. It does not decide whether a reader is protected. It does not tell someone what action to take.

Its job is narrower and more useful:

```text
explain the legal terrain
preserve the source trail
surface relevant deadlines, processes, and concepts
connect people-in-need to support options that appear relevant
```

The project exists because whistleblower law is scattered across statutes, cases, doctrines, agencies, procedures, and jurisdiction-specific rules. People who need this information most are often least able to spend days reconstructing it from primary sources.

WhistleblowerShield tries to make that terrain legible.

## Who It Serves

The project is organized around two primary reader situations.

### Maya

Maya is considering whether to report wrongdoing.

She has not acted yet. She is trying to understand whether the law may protect her, what kinds of disclosures matter, who she might report to, and what risks or deadlines she should know before deciding what to do.

### James

James already reported something and is now facing retaliation or pressure.

He needs to understand what may count as retaliation, what processes or deadlines may matter, what remedies may exist, and which organizations may be able to help.

A third reader exists too: Daniel, the researcher or casual user. Daniel matters, but Daniel does not drive product decisions. Maya and James do.

## What the Project Builds

WhistleblowerShield does not treat a jurisdiction page as one hand-written article.

It models smaller records and assembles them into useful views.

Current or active record types include:

- jurisdictions
- jurisdiction summaries
- statutes
- common-law doctrines
- citations
- constructions
- assist organizations
- controlled vocabularies
- glossary terms

Some future or partial layers also exist in code, such as agencies and procedures. They should not be treated as the current documentation focus unless a specific doc says so.

## The Core Model

The law does not always live in the first statute someone finds.

A current answer may depend on:

```text
statute
    + later citation
    + later construction
    + common-law doctrine
    + jurisdiction-specific procedure
```

The reader should not have to understand that machinery.

The system does.

A statute may be the starting point. A later case or construction may modify the practical meaning. The public page can show the statute first because that is how humans usually read. Internally, the system still preserves the modification chain.

## Current Implementation

The project is implemented primarily through the `ws-core` WordPress plugin.

Current stack:

- WordPress
- ACF Pro
- PHP
- custom post types
- controlled taxonomies
- matrix seeders
- query-layer data contracts
- render and shortcode assembly
- prompt and ingest tooling under active development

This project is still pre-launch in the important sense: there is no precious hand-entered production database that must be protected with compatibility layers or migration shims. If that changes, the documentation must change with it.

Some public-facing or beta-facing material may exist. That does not automatically mean every internal layer is settled.

## Current Doctrine

These are not slogans. They are rules learned from the project’s own mistakes and refactors.

### Read the Code Before Rewriting the Docs

Documentation can drift. Code can also be transitional. When they disagree, inspect the live code first.

A document should not state something as current reality unless the code supports it.

### Keep Retrieval Out of Rendering

Render functions and shortcodes should not call `get_field()`, `get_post_meta()`, or `WP_Query` directly.

Data retrieval belongs in the query layer.

This prevents the presentation layer from quietly learning storage details and producing five versions of the same data shape.

### Fail Loud

Silent failures are dangerous in a legal reference system.

A failed lookup, missing term, invalid enum, skipped write, or swallowed exception should not quietly become plausible output.

A legitimate empty state is fine.

A software failure pretending to be an empty state is not.

### Controlled Vocabularies Matter

Taxonomies in this project are not casual WordPress tags.

They are controlled vocabularies used to make legal concepts, reader situations, and organization metadata queryable.

Free text is necessary for nuance. It should not replace structure when structure is possible.

### Attribution Is Not Audit

Visible credit and hidden chain-of-custody answer different questions.

An administrator fixing a typo should not necessarily become the credited editor of a record.

The audit trail should still know who touched the data.

Both truths matter.

## Documentation Status

The documentation set is being reconciled.

Some files describe current implementation. Some describe design intent. Some preserve historical reasoning. Some are draft notes or archived proposals.

Those categories must not be merged silently.

A useful document should make clear whether it is describing:

```text
current reality
current design intent
historical rationale
```

If it cannot do that, it is not ready to become canonical.

## Contributors

WhistleblowerShield is currently maintained as a very small project, often functionally by one person.

That is a constraint, not a membership policy.

Thoughtful contributors are welcome. Useful help may come from:

- legal researchers
- whistleblower attorneys
- labor and public-accountability advocates
- accessibility-minded writers
- WordPress developers
- data modelers
- taxonomy and ontology people
- public-interest technologists

This project is opinionated. Contributors are not expected to agree with every decision. They are expected to care about accuracy, sources, plain language, and the people the project serves.

This is not a pitch deck. It is not written for investors. If funding ever helps, it does not get to rewrite the mission.

## Where to Start

Recommended first files:

```text
README.md
documentation/README.documentation.md
documentation/-start-here-/architecture-overview.md
documentation/-start-here-/design-principles.md
plugins/ws-core/includes/loader.php
plugins/ws-core/includes/queries/
plugins/ws-core/includes/taxonomies/register-taxonomies.php
```

If an older document disagrees with these files or with live code, do not patch from memory. Verify first.