# WhistleblowerShield Architecture Overview

**Status:** Draft  
**Audience:** Future maintainer, project contributor, human researcher 300 years from now  
**Purpose:** Explain the architectural shape of WhistleblowerShield without requiring someone to reverse-engineer it from scattered headers, comments, and half-finished refactors.

---

# Core Idea

WhistleblowerShield is not best understood as a WordPress plugin that displays legal pages.

It is better understood as:

```text
a structured legal knowledge system
implemented inside WordPress
```

The public pages are outputs. The real value is the structured legal model underneath them.

A jurisdiction page should eventually answer reader-centered questions:

- Am I protected?
- What happened to me?
- What can I do next?
- Who can help?

To answer those questions honestly, the system models law in smaller pieces and assembles them into reader-facing explanations.

---

# The Assembly Model

Whistleblower law does not live in one clean place.

A current legal answer may come from:

```text
statute
    + citation
    + construction
    + common-law doctrine
    + agency procedure
    + later legal update
```

The reader should not have to understand that machinery.

The system does.

This is why WhistleblowerShield separates legal records instead of treating a jurisdiction page as one authored blob.

A statute may be the starting point. A later citation or construction may be the controlling modification. The assembly layer can show the statute first for human comprehension while the internal model still respects later modifications.

---

# Architectural Layers

The current system is converging around six major layers.

## 1. Vocabulary Layer

Defines the concepts used throughout the system.

Includes:

- core taxonomies
- glossary terms
- aliases
- legal recognition terms
- controlled vocabularies

Questions answered:

```text
What concepts exist?
What terms can records use?
What vocabulary is safe to query, filter, prompt, and render?
```

Important files:

```text
plugins/ws-core/includes/taxonomies/register-taxonomies.php
plugins/ws-core/includes/taxonomies/register-glossary.php
```

### Taxonomies

Taxonomies in this project are controlled vocabularies, not casual tags.

Some represent legal doctrine:

- protected disclosures
- protected actions
- legal recognitions
- remedies
- causation standards

Some represent directory metadata:

- languages
- organization models
- cost models
- services
- case stages

Some represent system axes:

- jurisdictions
- procedure types

Those categories should not be governed the same way.

---

## 2. Schema Layer

Defines what a record can store.

Includes:

- canonical field definitions
- ACF build tables
- prompt field metadata
- future ingest mappings

Questions answered:

```text
What is a legal record?
What fields belong to it?
Which fields are essential, expected, conditional, optional, or internal?
```

Important files and documents:

```text
plugins/ws-core/includes/acf/acf-jx-statutes.php
in-progress/legal-record-acf-fields-v3.0.md
in-progress/legal-record-acf-hooks-v1.0.md
```

The `jx-statute` schema is currently the most advanced implementation of the canonical legal-record pattern.

Citation, construction, and common-law schemas still contain older patterns and should not be documented as fully migrated until code proves they are.

---

## 3. Workflow Layer

Defines how records are created, reviewed, verified, credited, and audited.

Includes:

- authorship stamps
- plain-English review
- source verification
- major edit flags
- hidden audit fields
- admin enforcement hooks

Questions answered:

```text
Who created this?
Who reviewed this?
Who verified this?
Who deserves visible credit?
Who actually touched the data last?
```

Important files:

```text
plugins/ws-core/includes/acf/workflow/acf-stamp-fields.php
plugins/ws-core/includes/acf/workflow/acf-plain-english-fields.php
plugins/ws-core/includes/acf/workflow/acf-source-verify.php
plugins/ws-core/includes/acf/workflow/acf-major-edit.php
plugins/ws-core/includes/admin/admin-hooks.php
```

The workflow layer intentionally separates attribution from audit.

Visible editorial credit and hidden chain-of-custody are different questions.

---

## 4. Query Layer

Acts as the internal data API.

Render functions and shortcodes should not call WordPress data functions directly. They should receive normalized arrays from query-layer functions.

Questions answered:

```text
What data is needed?
How is it normalized?
What shape can renderers rely on?
```

Important files:

```text
plugins/ws-core/includes/queries/query-helpers.php
plugins/ws-core/includes/queries/query-shared.php
plugins/ws-core/includes/queries/query-jurisdiction.php
plugins/ws-core/includes/queries/query-general.php
plugins/ws-core/includes/queries/query-directory.php
plugins/ws-core/includes/queries/query-agencies.php
```

The query layer strips storage prefixes and exposes plain PHP keys downstream.

Example:

```text
ws_auto_create_author
```

becomes:

```text
author.created_by
```

The render layer should not need to know how ACF or post meta stores the value.

---

## 5. Assembly Layer

Turns datasets into structured page output.

Includes:

- render functions
- shortcode files
- page assemblers

Questions answered:

```text
How should the reader encounter this information?
What order makes sense?
What sections appear on which page?
```

Important files:

```text
plugins/ws-core/includes/render/
plugins/ws-core/includes/shortcodes/
```

The query layer is not assembly. It is a prerequisite for assembly.

---

## 6. Presentation Layer

Final output shown to readers.

Includes:

- HTML
- frontend CSS
- frontend JavaScript
- glossary tooltip injection
- citation formatting helpers

Questions answered:

```text
How does this appear to Maya, James, or a researcher?
```

Presentation should not become the source of legal truth.

---

# Matrix Layer

The matrix layer seeds reproducible starting data.

It is admin-only, except for pure in-memory reference arrays that are needed on the frontend.

Important files:

```text
plugins/ws-core/includes/admin/matrix/
```

Matrix order matters.

Jurisdictions create foundational terms.

Federal statutes and agencies depend on those terms.

Procedures depend on agencies and statutes.

If the matrix layer feels picky about load order, that is because it is.

Do not simplify the order unless the dependency graph has actually changed.

---

# Controlled Vocabularies and Sentinels

WhistleblowerShield uses a recurring pattern:

```text
taxonomy term
    +
context/detail field
```

Examples:

```text
has-details
has-channel
additional
```

This avoids two bad outcomes:

1. Exploding the taxonomy with too many tiny terms.
2. Losing queryability by dumping everything into free text.

Known concept goes in the taxonomy.

Nuance goes in the companion field.

---

# Prompt and Ingest Layer

Prompt and ingest tooling is evolving.

The important doctrine:

```text
Canonical schema names are not the same as research-agent names.
```

A field may have:

```text
name        = canonical schema name
prompt_key  = research-agent alias
```

The canonical name remains the truth.

The prompt key is a translation layer for better research extraction.

The current code may still call this `json_key`.

Long-term, `prompt_key` is the clearer name because JSON is only the current transport shape, not the concept.

---

# Documentation Caution

The codebase contains multiple historical layers.

Some documents describe:

- current implementation
- intended future architecture
- superseded plans
- AI-generated drafts
- temporary handoff notes

Do not treat every document as equally current.

When in doubt:

```text
live code wins
```

Then ask whether the code is settled or transitional.

That second question matters.

---

# Future Direction

Several architectural themes are emerging but should not yet be treated as completed:

```text
Legal Vocabulary Layer
    ↓
Canonical Schema Layer
    ↓
Workflow Governance Layer
    ↓
Query/Data API Layer
    ↓
Assembly Layer
    ↓
Presentation Layer
```

Likewise, taxonomies appear to be separating naturally into:

```text
Legal Ontology
Directory Metadata
System Infrastructure
```

This split is currently conceptual rather than formal.

Document reality.

Do not promote aspirations to facts simply because they sound organized.

Future-you will have enough problems already.