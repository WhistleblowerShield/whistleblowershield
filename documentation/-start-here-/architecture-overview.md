# Architecture Overview

**Purpose:** Record the current architectural structure of WhistleblowerShield and the reasons the major boundaries exist.


WhistleblowerShield is implemented as a WordPress plugin, but the architecture is not organized around WordPress as the primary idea.

The project is organized around structured legal information:

```text
controlled vocabulary
    → legal record
    → workflow state
    → query contract
    → assembled page
    → reader-facing explanation
```

The public page is an output. The source records and the assembly rules are the system.

## Architecture Was Recognized Over Time

The architecture was not invented in one meeting and then implemented cleanly from top to bottom.

The structure existed in practice first. It was recognized, named, and tightened over time as repeated problems appeared:

- rendering code was learning too much about storage;
- ACF field definitions were being duplicated;
- taxonomy terms were becoming both content labels and legal vocabulary;
- silent failures were producing plausible empty output;
- prompt needs were beginning to diverge from canonical schema names.

The current documents should describe what has survived that process, not pretend it was obvious from the beginning.

## Current Major Layers

The project is currently best understood through six layers:

```text
Vocabulary
Schema
Workflow
Query
Assembly
Presentation
```

These layer names are useful only if they prevent mistakes. They are not decoration.

## 1. Vocabulary Layer

The vocabulary layer defines controlled terms the rest of the system relies on.

Current examples:

```text
plugins/ws-core/includes/taxonomies/register-taxonomies.php
plugins/ws-core/includes/taxonomies/register-glossary.php
```

This layer includes:

- taxonomies;
- glossary terms;
- aliases;
- legal recognition terms;
- directory and assist-organization terms.

### Why This Layer Exists

The system needs consistent terms to compare, filter, prompt, and render records.

Without controlled vocabulary, the same concept becomes several strings:

```text
retaliation
retaliatory action
employment reprisal
adverse employment response
```

Some variation belongs in aliases or text. The core concept still needs one stable form.

### What Mistake This Prevents

It prevents every record from inventing its own vocabulary.

That matters because WhistleblowerShield is not trying to publish isolated articles. It is trying to compare, assemble, and explain related legal information across jurisdictions.

## 2. Schema Layer

The schema layer defines what records can store.

Current examples:

```text
plugins/ws-core/includes/acf/
plugins/ws-core/includes/acf/acf-jx-statutes.php
in-progress/legal-record-acf-fields-v3.0.md
```

The statute ACF file is the clearest current example of the newer pattern: one canonical field table drives ACF registration and prompt-package output.

### Why This Layer Exists

The system needs stable field definitions before it can reliably query, render, ingest, or prompt against records.

### What Mistake This Prevents

It prevents the project from maintaining separate versions of the same schema in:

```text
ACF fields
prompt instructions
ingest mappings
query functions
documentation
```

Separate hand-maintained schemas drift.

Drift eventually becomes false output.

## 3. Workflow Layer

The workflow layer records authorship, review, verification, and audit state.

Current examples:

```text
plugins/ws-core/includes/acf/workflow/acf-stamp-fields.php
plugins/ws-core/includes/acf/workflow/acf-plain-english-fields.php
plugins/ws-core/includes/acf/workflow/acf-source-verify.php
plugins/ws-core/includes/acf/workflow/acf-major-edit.php
plugins/ws-core/includes/admin/admin-hooks.php
```

This layer includes:

- created-by stamps;
- last-edited stamps;
- plain-English review state;
- source verification state;
- major-edit flags;
- hidden audit values.

### Why This Layer Exists

Legal reference material needs a chain.

The system needs to know who created a record, who reviewed it, who verified it, whether plain-English content is approved, and what was touched later.

### What Mistake This Prevents

It prevents visible credit from being confused with audit truth.

An administrator can fix a typo without becoming the credited editor of the record. The hidden audit trail can still preserve that the administrator touched the data.

## 4. Query Layer

The query layer retrieves and normalizes data.

Current examples:

```text
plugins/ws-core/includes/queries/query-helpers.php
plugins/ws-core/includes/queries/query-shared.php
plugins/ws-core/includes/queries/query-jurisdiction.php
plugins/ws-core/includes/queries/query-general.php
plugins/ws-core/includes/queries/query-directory.php
plugins/ws-core/includes/queries/query-agencies.php
```

Render files and shortcode files should not call `get_field()`, `get_post_meta()`, or `WP_Query` directly.

### Why This Layer Exists

The system needs one place where storage details become stable data contracts.

Example:

```text
ws_auto_create_author
```

becomes:

```text
author.created_by
```

The render layer should not care how that value was stored.

### What Mistake This Prevents

It prevents rendering code from learning database details.

Once rendering code starts querying directly, every template becomes its own data API. That makes failures harder to catch and schema changes harder to survive.

## 5. Assembly Layer

The assembly layer turns query datasets into page sections.

Current examples:

```text
plugins/ws-core/includes/render/
plugins/ws-core/includes/shortcodes/
```

The query layer is not assembly. It is a prerequisite for assembly.

### Why This Layer Exists

The reader needs information in a coherent order.

The internal model may know that a later citation modifies a statute. The page may still show the statute first because that is how the reader understands the story.

### What Mistake This Prevents

It prevents source order from being confused with reader order.

Internal legal priority and public reading order are related, but not identical.

## 6. Presentation Layer

The presentation layer is the final reader-facing output.

Current examples:

```text
frontend CSS
frontend JavaScript
HTML render output
glossary tooltip injection
statute citation formatting
```

### Why This Layer Exists

People need readable pages, not record dumps.

### What Mistake This Prevents

It prevents presentation from becoming the source of truth.

A tooltip, bold statute citation, or page section may help comprehension. It should not become the place where legal meaning is stored.

## Matrix Layer

The matrix layer seeds reproducible baseline data.

Current examples:

```text
plugins/ws-core/includes/admin/matrix/
```

The matrix layer includes jurisdiction seeders, federal statute seeders, assist-organization seeders, agency seeders, procedure seeders, and matrix-watch tooling.

Some files live in the matrix directory but are not admin-only seeders. Court matrix files are pure in-memory reference data and must be available wherever their lookup functions are used.

### Why This Layer Exists

The project needs reproducible baseline records.

If the database is wiped during pre-launch development, matrix seeders should recreate the known foundation.

### What Mistake This Prevents

It prevents the project from treating disposable seed data as precious production data.

It also prevents hand-created baseline records from becoming impossible to reconstruct.

## Current Known Transitional Areas

Some areas are real but not fully settled.

### Legal-Record Schema

The `jx-statute` schema is ahead of the remaining legal-record types.

Citation, construction, and common-law records still contain older patterns in places.

Do not document all four legal-record types as fully migrated unless the code proves it.

### Prompt and Ingest Tooling

Prompt and ingest tooling is active, but the prompt-generator files are pending a
ground-up rewrite.

`prompt_key` is the current field name throughout the codebase. `json_key` is retired —
the rename is complete in the schema, the output array, the renderer, and the ingest
tooling. There are no surviving `json_key` references.


### Glossary

The glossary exists as a proof of concept.

It already shows useful behavior: canonical terms, aliases, cached lookup, and opt-in tooltip scanning.

It should not yet be documented as a complete concept layer.

## Final Rule

Architecture documentation should describe load-bearing structure.

If an idea is useful but not yet implemented, put it in a concept document.

If an idea is implemented but transitional, say so.

If an idea is historical, preserve the reason but do not present it as current reality.