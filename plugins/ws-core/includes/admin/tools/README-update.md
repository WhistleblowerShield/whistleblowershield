# includes/admin/tools/

Operator tooling for prompt generation, ingest, and taxonomy audits.

## Files

- `tool-generate-prompt.php`
- `tool-ingest.php`
- `tool-taxonomy-term-audit.php`

## Workflow

1. Generate prompts from current taxonomy/state context.
2. Produce and validate external research output.
3. Run ingest preflight.
4. Confirm ingest and review logs.
5. Resolve proposed/new taxonomy terms through controlled admin flows.

## Guardrails

- Keep ingest preflight strict.
- Do not silently auto-create taxonomy terms from model output.
- Maintain append-only audit-style logs where defined.

## Scope Note

This folder is intentionally operational and may lag broader naming migrations when backwards compatibility in ingest/prompt tooling is required.
