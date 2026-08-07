# WhistleblowerShield Design Principles

**Status:** Draft  
**Purpose:** Capture the project doctrines that keep appearing in code, comments, architecture notes, and refactor decisions.

This is not a style guide.

It is a memory aid for Future Dwight, future contributors, and whoever discovers the project long after everyone involved should have documented more things and trusted their memory less.

---

# 1. Maya and James Come First

WhistleblowerShield exists for people trying to answer dangerous, practical questions.

Maya asks:

```text
Am I protected if I come forward?
```

James asks:

```text
I reported something and now something happened to me.
What can I do?
```

Every system choice should eventually serve one or both questions.

If a feature only makes the architecture more impressive, it needs to justify itself.

---

# 2. Model the Law, Not the Page

A jurisdiction page is not the source of truth.

It is an assembled view.

The system models smaller legal records:

- statutes
- common-law doctrines
- citations
- constructions
- agencies
- procedures
- assist organizations

Those records are assembled into reader-facing pages.

This matters because a later citation or construction may change the practical meaning of a statute.

The page can show the statute first for readability.

The model must still preserve the legal chain.

---

# 3. Canonical Schema Wins

Canonical field names define the internal model.

They should be:

- stable
- precise
- reasonably boring
- understandable to the maintainer

Prompt names, UI labels, aliases, and transport formats may change.

They do not define the schema.

Recommended doctrine:

```text
name        = canonical schema name
prompt_key  = research-agent alias
label       = editor-facing text
```

If the current code still says:

```text
json_key
```

read it as an implementation-era name, not the concept itself.

JSON is a transport format.

Prompt language is the actual purpose.

---

# 4. Define Once, Generate Many Outputs

Prefer one source of truth that can produce multiple outputs.

Examples:

- taxonomy registry drives registration, seeding, and prompt vocabulary
- schema tables drive ACF generation and prompt generation
- workflow field definitions drive review and query payloads

Avoid parallel hand-maintained systems whenever possible.

Parallel systems become drift factories.

Drift factories eventually produce plausible lies.

---

# 5. Taxonomies Are Controlled Vocabularies

Taxonomies in WhistleblowerShield are not ordinary WordPress tags.

They are controlled vocabularies.

Some represent legal ontology:

- protected disclosures
- protected actions
- legal recognitions
- causation standards
- remedies

Some represent directory metadata:

- services
- languages
- organization models
- cost models

Some represent system infrastructure:

- jurisdictions
- procedure types

These categories may all use WordPress taxonomies.

They are not the same thing.

A new language term is not the same class of decision as a new legal-recognition doctrine.

Treat them accordingly.

---

# 6. Use Sentinels for Nuance

The project frequently uses terms like:

```text
has-details
has-channel
additional
```

These are not junk terms.

They are sentinel terms.

Pattern:

```text
controlled vocabulary term
    +
companion context field
```

This allows the system to remain queryable while still expressing real-world complexity.

Avoid two common failures:

### Vocabulary Explosion

Creating endless increasingly-specific taxonomy terms.

### Text Blob Syndrome

Giving up and putting everything in free text.

The preferred compromise is:

```text
known concept
    +
structured detail field
```

---

# 7. Fail Loud

Silent failures are dangerous in a legal reference system.

A missing term, failed query, invalid enum, skipped write, swallowed exception, or malformed configuration should not quietly become acceptable-looking output.

Distinguish between:

```text
legitimate empty state
```

and:

```text
unknown state caused by failure
```

Those are not the same thing.

An empty summary is a content problem.

A failed query pretending the summary is empty is a software problem.

The system should surface the difference.

If someone asks the legal code for a tennis match, it does not get to become a statute.

---

# 8. Attribution Is Not Audit

The system intentionally separates visible credit from hidden chain-of-custody.

Attribution answers:

```text
Who deserves editorial credit?
```

Audit answers:

```text
Who touched the data?
When?
What changed?
```

These are different questions.

An administrator fixing punctuation should not necessarily become the public editor of record.

The audit trail should still know what happened.

Both truths matter.

---

# 9. Plain English Is a Workflow

Plain-English content is not decoration.

It is not the paragraph added at the end because somebody remembered accessibility.

It has lifecycle:

- created
- edited
- reviewed
- approved
- re-reviewed when meaning changes

Plain-English review exists because:

```text
Readable
```

is not the same thing as:

```text
Accurate
```

A document should strive to be both.

---

# 10. Documentation Must Declare Its Status

Every significant document should make clear whether it describes:

```text
Current Reality
```

```text
Current Design Intent
```

```text
Historical Rationale
```

These are different categories.

Do not silently convert proposals into facts.

Do not quietly leave obsolete plans in active documentation.

Do not delete useful history merely because it is no longer current.

Archive when appropriate.

Delete only when genuinely valueless.

---

# 11. Contributors Are Welcome

The project is often maintained as if only one person exists.

That is a survival strategy.

It is not a membership policy.

Thoughtful contributors are welcome.

Helpful contributors may come from:

- legal research
- whistleblower advocacy
- software development
- accessibility
- technical writing
- labor rights
- public accountability
- structured data and taxonomy design

The project is opinionated.

People are not expected to agree with every decision.

They are expected to care whether the result is accurate and useful.

---

# 12. Investors Are Not the Audience

The documentation should not read like a startup pitch.

This project is not built around growth metrics, valuation language, enterprise positioning, disruption narratives, or carefully optimized investor decks.

The primary goals are:

- accuracy
- transparency
- maintainability
- usefulness

The intended audience is:

```text
People seeking answers.
Researchers seeking facts.
Maintainers seeking truth.
```

Not venture capital.

---

# 13. Code Truth Comes Before Documentation Comfort

If documentation and code disagree:

```text
Investigate.
```

Do not simply update whichever one is easier.

If code and intent disagree:

```text
Document the disagreement.
```

If a migration is incomplete:

```text
Say so.
```

The goal is not confident documentation.

The goal is trustworthy documentation.

Trustworthy documentation is allowed to say:

```text
This is not settled yet.
```

That is better than a polished lie.

---

# 14. Build for the Person Who Finds the Hard Drive

Future Dwight is a user.

Future contributors are users.

Human Researcher 300 is a user.

The project should be understandable by someone who was not present for the conversation that produced it.

Leave breadcrumbs.

Write down assumptions.

Explain surprising decisions.

Document dependency chains.

Document doctrine.

Future-you is smart.

Future-you is also busy and will have forgotten half of this.

Plan accordingly.