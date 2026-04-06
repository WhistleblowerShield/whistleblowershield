# Researcher Guidance - First-Pass LLMs (Gemini, Grok, ChatGPT)

## Purpose
This guide is for first-pass research models only.
They generate draft JSON for later NotebookLM reconciliation.

These models are not legal authorities. Their job is to produce structured,
honest, verifiable drafts with omissions where evidence is weak.

## What First-Pass Models Must Do
1. Follow schema exactly.
2. Use only approved taxonomy slugs from the prompt tables.
3. Omit uncertain values instead of guessing.
4. Use approved citation/source domains only.
5. Report gaps in integrity and json_run_notes.

## What First-Pass Models Must Not Do
1. Do not invent slugs.
2. Do not invent URLs or citations.
3. Do not add non-schema keys.
4. Do not include NotebookLM-only fields.

NotebookLM-only fields are reserved for reconciliation output.
Do not ask first-pass models to output them.

## Field Discipline
- Structure is strict, completeness is not.
- Empty or omitted fields are acceptable when evidence is insufficient.
- A partial but honest record is better than a complete but unreliable record.

## Run Method
1. Keep batches small (1-3 statutes) for reviewability.
2. Generate JSON.
3. Validate URLs and citations quickly before ingest.
4. Send approved drafts to NotebookLM for merge/fact-check.

## Review Checklist Before Reconciliation
1. Schema shape valid.
2. Jurisdiction and record identifiers look correct.
3. No invented taxonomy slugs.
4. No forbidden source domains.
5. Integrity block reflects known gaps honestly.

## Escalation Rule
If a model repeatedly fails a rule, do not keep patching prompts ad hoc.
Add the issue to insights, then update the canonical researcher prompt once.

## Success Definition
A successful first-pass batch is:
- schema-valid,
- mostly accurate,
- explicit about uncertainty,
- safe for reconciliation.

Do not optimize for perfect first-pass completeness.
Optimize for trustworthy drafts.
