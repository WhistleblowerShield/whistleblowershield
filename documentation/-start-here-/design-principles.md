# Design Principles

**Purpose:** Record the design rules that have survived implementation, review, and refactoring.


These principles are not branding language. They are working rules.

Each principle exists because the project has already encountered the kind of mistake the rule prevents.

## 1. Maya and James Come First

The project serves people in two primary situations.

Maya is considering whether to report wrongdoing.

James already reported something and is now facing retaliation or pressure.

The system should help them understand legal terrain and find possible support.

It should not pretend to be their lawyer.

### Why This Matters

Technical features can easily drift toward serving the architecture instead of the reader.

The test is not:

```text
Is this clever?
```

The test is:

```text
Does this eventually help Maya or James understand what may matter?
```

## 2. The Site Does Not Make Legal Determinations

WhistleblowerShield does not tell a reader:

```text
You are protected.
You are not protected.
You should report.
You should sue.
```

It explains potentially relevant laws, concepts, deadlines, processes, sources, and support organizations.

### Why This Matters

The project is a legal reference and connection system, not legal counsel.

If the site starts making determinations, it changes the ethical, legal, and practical nature of the project.

## 3. Model the Law, Not the Page

A page is an output.

The system models smaller legal records:

- statutes
- common-law doctrines
- citations
- constructions
- summaries
- assist organizations

Those records can then be assembled into reader-facing views.

### Why This Matters

A jurisdiction page can become obsolete if it is the source of truth.

A small record can be updated, linked, modified, or superseded without rewriting the entire page by hand.

## 4. Preserve the Chain

A later case, citation, or construction may change the practical meaning of an earlier statute.

The system should preserve relationships between records instead of flattening everything into prose.

### Why This Matters

Readers need coherent explanations, but maintainers need the chain.

If the chain disappears, future updates become guesswork.

## 5. Canonical Schema Wins

Canonical field names define the internal model.

Prompt names, labels, aliases, and transport formats may differ.

Recommended distinction:

```text
name        = canonical schema name
prompt_key  = research-agent alias (implemented; used when canonical name is ambiguous to an LLM)
label       = editor-facing display text
```

`prompt_key` is the current field name throughout the codebase. `json_key` is retired.

### Why This Matters

Research-agent language changes faster than storage contracts.

If prompt wording becomes the schema, the model drifts every time a prompt gets tuned.

## 6. Define Once, Generate Many Outputs

Prefer one source of truth that drives several outputs.

Examples:

- taxonomy registry drives registration, seeding, and prompt vocabulary;
- statute field table drives ACF registration and prompt packages;
- shared workflow fields drive ACF UI, hooks, and query payloads.

### Why This Matters

Parallel systems drift.

Drift creates output that looks plausible but is wrong.

## 7. Taxonomies Are Controlled Vocabularies

Taxonomies in this project are not casual WordPress tags.

They are controlled vocabularies used to make legal concepts and support metadata queryable.

### Why This Matters

Without controlled vocabulary, the system cannot reliably compare records across jurisdictions.

But not all taxonomies are the same kind of thing. A legal-recognition term and a language term may both be WordPress taxonomies, but they do not require the same governance.

## 8. Use Sentinels for Nuance

The project uses sentinel terms such as:

```text
has-details
has-channel
additional
```

Pattern:

```text
controlled term
    +
companion context field
```

### Why This Matters

This prevents two failures:

```text
taxonomy explosion
```

and

```text
free-text blob syndrome
```

The known concept remains queryable. The nuance remains expressible.

## 9. Fail Loud

Silent failures are dangerous.

A missing term, failed write, invalid enum, swallowed exception, or failed lookup should not quietly become acceptable-looking output.

### Why This Matters

A legitimate empty state and a system failure can look identical if the code treats them the same.

A jurisdiction with no summary is an empty state.

A failed term lookup that makes the jurisdiction appear empty is a bug.

The system must preserve that difference.

## 10. Retrieval Does Not Belong in Rendering

Render files and shortcode files should not call storage functions directly.

Use the query layer.

### Why This Matters

If every render function retrieves its own data, there is no reliable contract for data shape, failure handling, or schema changes.

The query layer exists so rendering can stay focused on presentation.

## 11. Attribution Is Not Audit

Visible credit and hidden audit answer different questions.

Attribution asks:

```text
Who should receive editorial credit?
```

Audit asks:

```text
Who touched the data, when, and under what chain?
```

### Why This Matters

An administrator fixing a typo should not necessarily replace the credited editor.

But the system still needs an unquestionable record of who touched the data.

## 12. Plain English Is a Workflow

Plain-English content is not decoration.

It has lifecycle:

- content exists;
- content was written;
- content was reviewed;
- substantial changes may require review again.

### Why This Matters

Readable is not the same as reviewed.

A plain-English explanation can be clear and still wrong. The workflow exists to keep clarity and trust connected.

## 13. Documentation Must Declare Status

Every major claim should be clearly one of:

```text
current reality
current design intent
historical rationale
```

### Why This Matters

A proposal written confidently can become fake history.

A historical note left unlabeled can become fake current reality.

A current implementation described vaguely can become impossible to verify.

## 14. Contributors Are Welcome, but the Mission Is Not for Sale

The project may be maintained mostly by one person today.

That does not mean help is unwelcome.

Useful contributors may include researchers, advocates, attorneys, developers, writers, accessibility people, and data-structure people.

### Why This Matters

The documentation should be welcoming without sounding like a fundraising deck.

Funding may help the project. Funding does not get to redefine the purpose.

## 15. Court Report Before Flourish

Some documents need polish.

These documents need the record first.

Write down:

- what problem existed;
- what was tried;
- what failed;
- where it ended up;
- what lesson should not be forgotten.

### Why This Matters

Future maintainers cannot learn from a mistake that was cleaned out of the record.

A polished summary may look professional.

A preserved rationale prevents repeated failure.

## 16. Code Truth Comes Before Documentation Comfort

If code and documentation disagree, inspect the code.

If code and intent disagree, document the disagreement.

If the code is transitional, say so.

### Why This Matters

The point of documentation is not confidence.

The point is trust.

Sometimes the most trustworthy sentence is:

```text
This is not settled yet.
```