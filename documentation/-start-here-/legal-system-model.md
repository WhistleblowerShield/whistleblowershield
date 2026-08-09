# Legal System Model

## Purpose

This document describes how legal information is conceptually structured within the platform, what the entities are, how they relate to each other, and the modeling decisions that shaped the legal layer of the system.

It is a reference for understanding the legal model that underpins WhistleblowerShield.

It is not a technical specification of the database schema. That belongs to the development and reference documentation.

---
**A note on scope:** this document describes the complete intended legal-system model — the whole picture, as planned from the start. Not every entity below is built yet. Statute, Citation, Construction, Assist Organization, and Summary are current, live record types. Agency, Filing Procedure, Legal Update, and Reference describe where the model is headed; their presence here is design intent, not a claim that they exist in code today.
---

## The Core Problem

Whistleblower law in the United States is not a single body of law.

It is a patchwork of federal statutes, state statutes, agency-specific regulations, and case law constructions that vary by:

- **Jurisdiction** — federal law applies everywhere; state law varies dramatically; territorial law is its own category
- **Employment sector** — federal employees, private-sector employees, military contractors, and healthcare workers are often covered by different laws
- **Protected disclosure** — reporting securities fraud is governed by different law than reporting workplace safety violations
- **Timing** — a person considering coming forward has different legal needs than a person already facing retaliation

The platform's data model exists to make this complexity navigable without flattening it.

The answer to:

```text
Am I protected?
```

is genuinely different depending on who you are, where you work, what you reported, and when.

---

## Primary Entities

### Jurisdiction

The organizing unit of the entire system.

Every other entity belongs to one or more jurisdictions.

A jurisdiction is not merely a geographic boundary. It is the legal authority that governs a particular set of laws.

The 57 jurisdictions currently represented by the system are:

- Federal (US)
- District of Columbia
- 50 U.S. states
- American Samoa
- Guam
- Northern Mariana Islands
- Puerto Rico
- U.S. Virgin Islands

Each jurisdiction assembles the records relevant to that legal authority.

---

### Statute

A specific law or regulation that provides whistleblower protection.

Statutes are the foundational legal records of the platform.

A statute record may capture:

- Official name and citation
- Protected classes
- Protected disclosures
- Prohibited adverse actions
- Available process types
- Remedies
- Burden-of-proof standards
- Limitations periods
- Exhaustion requirements
- Employer defenses
- Fee-shifting rules
- Reward provisions

Federal statutes are automatically surfaced alongside applicable state records because federal protections may apply across jurisdictions.

The query layer distinguishes federal records from jurisdiction-specific records during assembly.

---

### Citation

A published court decision or administrative ruling that interprets, applies, expands, limits, or clarifies a statute.

Citations form part of the case-law layer.

A citation is linked to one or more statutes.

Citations help answer:

```text
How does this law work in practice?
```

rather than:

```text
What does the statute say on paper?
```

---

### Construction

A structured record of how a specific court or tribunal interpreted a specific legal protection.

Constructions are more tightly scoped than general citations.

The same statute may be interpreted differently by different courts.

Construction records preserve that distinction.

Court identifiers resolve to court-reference structures maintained by the platform.

When no structured court mapping exists, an alternate text path may be used.

---

### Agency
*(design intent — not yet in code)*

A governmental body that receives disclosures, investigates retaliation, enforces protections, or otherwise participates in whistleblower processes.

An agency record may include:

- Mission
- Reporting channels
- Contact information
- Jurisdictional authority
- Languages supported
- Relevant disclosure categories

Agency records answer:

```text
Who receives the report?
```

---

### Filing Procedure
*(design intent — not yet in code)*

A specific intake or enforcement path associated with an agency.

Procedures answer:

```text
What do I do next?
```

A procedure may record:

- Disclosure intake
- Retaliation intake
- Filing deadlines
- Filing methods
- Identity policies
- Prerequisites
- Walkthrough instructions
- Exclusivity considerations
- Related legal authorities

Procedures are linked to agencies and may be associated with specific statutes or common-law protections.

---

### Assist Organization

A non-government organization that helps whistleblowers.

Examples include:

- Legal aid organizations
- Advocacy groups
- Law firms
- Professional organizations
- Labor organizations
- Oversight organizations

Assist organizations answer:

```text
Who can help me?
```

This is intentionally distinct from:

```text
What do I do next?
```

One question is about support.

The other is about process.

---

### Summary

A plain-English overview of whistleblower protections within a jurisdiction.

The summary is usually the first substantive content a reader encounters.

Unlike statutes, citations, and constructions, the summary is itself the plain-English layer.

It is written primarily for Maya: someone attempting to understand the terrain rather than conduct legal research.

---

### Legal Update
*(design intent — not yet in code)*

A timestamped record of a significant development affecting whistleblower law or procedure.

Examples include:

- Statutory changes
- Regulatory changes
- Major court decisions
- Significant agency developments

Legal updates can appear both at the jurisdiction level and in broader update views.

---

### Reference
*(design intent — not yet in code)*

A source document associated with a statute, citation, or construction.

References provide direct access to supporting material and help preserve traceability.

---

## Key Relationships

```text
Jurisdiction (57)
    │
    ├── Summary
    │
    ├── Statute
    │       ├── Citation
    │       ├── Construction
    │       └── Reference
    │
    ├── Common Law
    │       ├── Citation
    │       ├── Construction
    │       └── Reference
    │
    ├── Agency
    │       └── Filing Procedure
    │
    ├── Assist Organization
    │
    └── Legal Update
```

Most relationships are implemented through jurisdiction taxonomy membership.

The primary exceptions are direct procedure-to-agency relationships and procedure-to-parent cross-references.

---

## The Concept vs. Law Distinction

One of the most important modeling decisions in the platform is the distinction between legal concepts and legal implementations.

A concept exists independently of any single law.

Examples:

```text
Retaliation protection
Protected disclosure
Burden of proof
```

A statute is one implementation of that concept.

For example:

```text
Retaliation Protection
        ↓
Sarbanes-Oxley § 806
        ↓
False Claims Act § 3730(h)
        ↓
State-specific statutes
```

The concepts remain stable even when jurisdictions implement them differently.

Taxonomies allow users to work from concepts.

Legal records describe how those concepts are implemented.

This enables readers to find relevant protections without already knowing which statute they need.

---

## Plain English as a Parallel Layer

Legal accuracy and plain-English communication are related but distinct concerns.

The platform attempts to preserve both.

For statutes, citations, and constructions:

```text
Technical Record
        +
Plain-English Overlay
```

The technical record preserves legal detail.

The plain-English layer explains practical meaning.

Either may exist without eliminating the other.

For summaries, the summary itself is the plain-English layer.

For filing procedures, the walkthrough functions as the plain-English explanation because there is no meaningful technical-only version of:

```text
How do I file this?
```

---

## Source Integrity

Every content record carries source-verification information.

The platform attempts to preserve:

- how information entered the system
- whether it has been reviewed
- whether additional review may be needed

Source attribution and verification are separate concepts.

A record may originate from:

- matrix seeding
- human creation
- AI-assisted research
- structured imports
- monitored feeds

The origin of a record does not determine whether it is correct.

Verification exists to evaluate the information itself.

The source-verification workflow and matrix-divergence monitoring exist to surface records that may require renewed review, validation, or editorial attention.

---

## Assessment

The legal model exists to preserve complexity without forcing readers to experience that complexity directly.

The platform models:

```text
Law
    ↓
Interpretation
    ↓
Procedure
    ↓
Guidance
```

as separate but connected layers.

This allows readers to move from:

```text
Am I protected?
```

to:

```text
Why?
```

to:

```text
What do I do next?
```

without requiring the underlying legal record to be simplified beyond recognition.