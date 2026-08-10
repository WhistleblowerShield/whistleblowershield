# System Architecture

## Purpose

This document serves as a technical reference for the current WhistleblowerShield implementation.

Where `architecture-overview.md` explains why major architectural boundaries exist, this document describes how those boundaries are currently implemented.

This is an implementation document.

It records:

- major system layers
- load order
- dependency relationships
- execution flow
- key integration points
- current architectural constraints

When both documents appear to cover the same territory, begin with:

```text
-start-here/architecture-overview.md
```

That document explains why the structure exists.

This document explains where to find it.

---

## Overview

WhistleblowerShield is implemented through a custom WordPress plugin (`ws-core`) that provides the platform's data model, editorial workflow, research tooling, and public-facing content assembly.

GeneratePress Premium provides the site shell and theme framework.

The platform's structure, behavior, and content assembly are governed by the plugin.

Most meaningful application logic lives within `ws-core`.

---

## Current Architectural Layers

The implementation is organized into six major layers.

Layers load in dependency order.

Higher layers may depend upon lower layers.

Lower layers must not depend upon higher layers.

```text
Data
    ↓
Matrix
    ↓
Query
    ↓
Admin
    ↓
Assembly
    ↓
Frontend Assets
```

---

## 1. Data Layer

### Purpose

Defines what records exist and how they are structured.

### Components

- Custom Post Types
- Taxonomies
- ACF field groups
- Shared workflow fields

### Current Content Types

Examples include:

```text
Jurisdiction
Summary
Statute
Citation
Construction
Common Law
Agency
Procedure
Assist Organization
Legal Update
Reference
```

### Current Design

ACF field groups are registered in PHP and stored in source control.

The platform does not rely on database-exported field groups as its source of truth.

Field definitions belong to the codebase.

### Key Relationship Model

Jurisdictional scope is implemented through taxonomy assignment.

The jurisdiction taxonomy acts as the primary join mechanism throughout the system.

Content records are attached to jurisdictions through taxonomy terms rather than dedicated relationship fields.

---

## 2. Matrix Layer

### Purpose

Provides reproducible baseline data.

### Responsibilities

The matrix system creates and maintains:

- jurisdictions
- taxonomy terms
- agencies
- assist organizations
- procedures
- reference datasets
- court lookup structures

### Current Behavior

Matrix seeders are version-gated.

Seed data may be recreated by advancing the relevant gate version.

Seeders are intended to be:

```text
repeatable
predictable
idempotent
```

The goal is to make core reference data reproducible rather than dependent on manual entry.

### Notes

Not all matrix assets become WordPress records.

Certain court and lookup datasets remain in-memory reference structures.

---

## 3. Query Layer

### Purpose

Acts as the platform's internal data API.

The query layer retrieves information from WordPress and returns normalized PHP arrays that can be consumed by renderers, assemblers, dashboards, and administrative tooling.

### Current Structure

```text
query-helpers.php
query-shared.php
query-jurisdiction.php
query-directory.php
query-agencies.php
query-general.php
```

Additional files may exist as the platform expands.

### Architectural Rule

The query layer is the preferred location for:

```text
WP_Query
get_post_meta()
get_field()
taxonomy retrieval
data normalization
```

### Why This Exists

Render files should not need to know how data is stored.

Administrative displays should not need to normalize raw structures repeatedly.

The query layer provides a stable contract between storage and presentation.

### Current Status

The query-layer doctrine is actively enforced and remains one of the most important architectural boundaries in the codebase.

---

## 4. Admin Layer

### Purpose

Supports editorial workflow, governance, monitoring, and data integrity.

### Current Responsibilities

Examples include:

- verification workflow
- source tracking
- plain-English review
- major edit tracking
- audit systems
- dashboard components
- monitoring services
- validation hooks
- administrative tools

### Design Philosophy

The admin layer exists to help maintain content quality and data integrity.

It is not responsible for public presentation.

Whenever possible, public-facing behavior belongs elsewhere.

### Current Scope

The administrative surface remains one of the largest portions of the codebase by file count and functionality.

---

## 5. Assembly Layer

### Purpose

Builds public-facing content from query-layer datasets.

### Components

```text
Render Functions
Shortcodes
Assemblers
```

### Render Functions

Render functions transform normalized arrays into HTML.

They should focus on presentation.

They should not retrieve data directly.

---

### Shortcodes

Shortcodes provide reusable entry points into render functions.

Shortcodes remain presentation-focused and should not bypass the query layer.

---

### Assemblers

Assemblers construct full content experiences automatically.

Current examples include:

```text
Jurisdiction pages
Agency pages
```

Assemblers allow content experiences to be generated from records without requiring manual shortcode placement inside post content.

### Current Status

The jurisdiction assembler remains one of the most important public-facing systems in the platform.

---

## 6. Frontend Assets

### Purpose

Provide styling and client-side behavior.

### Current Responsibilities

Examples include:

- layout styling
- section presentation
- trust indicators
- directory interfaces
- filtering behavior
- page interactions

### Design Philosophy

Frontend assets support understanding.

They should not become the source of legal meaning.

Legal meaning belongs to records, data structures, and assembled content.

---

## Current Content Flow

A typical jurisdiction page follows this path:

```text
WordPress Route
    ↓
Assembler
    ↓
Query Layer
    ↓
Normalized Dataset
    ↓
Render Functions
    ↓
HTML Output
    ↓
Frontend Styling
```

The reader sees the final assembled page.

The underlying records remain separate.

---

## Federal Overlay Model

Federal authority applies across jurisdictions.

Certain federal records are surfaced alongside jurisdiction-specific records where appropriate.

This allows readers to see:

```text
Jurisdiction-Specific Law
    +
Federal Law
```

within a single assembled experience.

The implementation preserves the distinction between federal and local records while presenting them together when useful.

---

## Attach-Flag Pattern

Certain content types support editorial curation.

Examples include:

```text
Statutes
Citations
Constructions
```

Records may be marked for curated display and ordered explicitly.

### Purpose

The curated view highlights the records most likely to matter to most readers.

It is not intended to represent the complete legal record.

Uncurated records still exist within the system and remain available for future filtering, navigation, and discovery features.

---

## Caching

The platform uses transient-based caching where appropriate to reduce repeated database work.

Caching is treated as a performance optimization rather than a source of truth.

When a cache becomes inaccurate, the underlying data remains authoritative.

Cache invalidation is generally tied to save, update, or delete events affecting the relevant record type.

---

## Current Areas of Active Development

The architecture described above is running and in active use.

Several areas remain in active development:

### Jurisdiction Population

The platform is still expanding jurisdiction coverage through the research and ingest workflow.

---

### Common Law Presentation

Common-law records are supported by the data model.

Public-facing presentation continues to evolve.

---

### Situation-Based Navigation

The data structures required for situation-based entry paths largely exist.

Navigation and presentation layers continue to develop.

---

### Citation Expansion

Citation records exist conceptually within the architecture.

Publication, ingestion, and presentation workflows continue to mature.

---

## Architectural Constraints

Several constraints guide implementation decisions.

### Query Before Render

Rendering layers should consume data.

They should not become data-access layers.

---

### Structure Before Presentation

Records, taxonomies, and workflows define meaning.

Presentation helps users understand that meaning.

Presentation should not become the primary source of it.

---

### Controlled Vocabulary Before Free Text

Known concepts should be represented structurally whenever practical.

Free text exists to provide context and nuance, not to replace structure entirely.

---

## Assessment

The current implementation supports the project's primary goals:

```text
Store legal information
Maintain editorial control
Preserve source history
Support structured research
Assemble reader-facing guidance
```

The architecture remains under active development. The major boundaries between data, workflow, retrieval, assembly, and presentation are load-bearing and continue to guide implementation decisions — but none of those boundaries should be treated as closed to revision.