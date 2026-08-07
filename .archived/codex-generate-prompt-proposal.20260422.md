# Codex Generate Prompt Rewrite Proposal

## Objective
Rewrite the prompt generator from scratch while preserving the existing admin interface and core operator workflow, and adopting the new static prompt format (`in-progress/tools/US-0-Assist-org-20260421-0001.txt`) as the canonical style baseline.

This proposal covers:
- Full architectural rewrite for `tool-generate-prompt`.
- Reusable block strategy across record types with minimal variable interpolation.
- New schema blocks for legal record types (`statute`, `common-law`, `citation`, `interpretation`) rebuilt from ACF definitions.
- Migration and verification plan.

## Current State Audit

### 1. Generator status
- Existing generator has been intentionally disabled by renaming:
  - `plugins/ws-core/includes/admin/tools/tool-generate-prompt.php`
  - -> `plugins/ws-core/includes/admin/tools/tool-generate-prompt.php.txt`
- Loader has been updated to stop loading it (`plugins/ws-core/includes/loader.php` tool list).
- Effect: old tool no longer executes in admin.

### 2. UI/workflow contract to preserve
Keep the existing operator-facing behavior and controls:
- Record type selector.
- Jurisdiction selector + manual code input.
- Records Requested / Proposal Count controls by record type.
- Assist-org nationwide toggle and focus notes.
- Scope notes fields.
- Citation/interpretation quality + statute type controls.
- Exclusion controls:
  - Auto exclusions
  - Manual exclusions
  - Disable exclusions
  - Refresh exclusions
- Output file generation to `wp-content/logs/ws-prompts/`.

### 3. Core functional concerns in old file (to avoid reusing)
- Very large monolithic file with mixed concerns (UI rendering, exclusion logic, schema text, taxonomy rendering, assembly).
- High maintenance overhead from interleaved static prose and procedural logic.
- Legacy fragmentation: unresolved references to `ws_prompt_statute_schema()` and `ws_prompt_common_law_schema()` in the disabled file indicate drift between architecture and implementation.

## Canonical Source Inputs For Rewrite

### A. Prompt format baseline
- `in-progress/tools/US-0-Assist-org-20260421-0001.txt`
- Use as style/structure baseline for:
  - narrative framing
  - omission policy
  - interpretation policy
  - field dictionary rigor
  - integrity/meta behavior
  - run scope and output contract

### B. Legal schema sources (authoritative field maps)
- `plugins/ws-core/includes/acf/acf-jx-statutes.php`
- `plugins/ws-core/includes/acf/acf-jx-common-law.php`
- `plugins/ws-core/includes/acf/acf-jx-citations.php`
- `plugins/ws-core/includes/acf/acf-jx-interpretations.php`

### C. Shared enum/constant source
- `plugins/ws-core/includes/admin/tools/ws-schema-constants.php`

## Rewrite Design

## 1. File layout and boundaries
Create a new executable prompt generator with strict separation of concerns:

### Proposed files
- `plugins/ws-core/includes/admin/tools/tool-generate-prompt.php`
  - Thin entrypoint only:
    - menu registration
    - request handler dispatch
    - page render invocation
- `plugins/ws-core/includes/admin/tools/prompt-generator/pg-config.php`
  - Record type config map, file naming rules, scope defaults.
- `plugins/ws-core/includes/admin/tools/prompt-generator/pg-exclusions.php`
  - Auto/manual exclusion resolution + merge behavior.
- `plugins/ws-core/includes/admin/tools/prompt-generator/pg-taxonomy.php`
  - Taxonomy table rendering + parent/sentinel policy.
- `plugins/ws-core/includes/admin/tools/prompt-generator/pg-ui.php`
  - Form HTML + JS preserving existing UX behavior.
- `plugins/ws-core/includes/admin/tools/prompt-generator/pg-blocks-shared.php`
  - Shared static blocks (mission, omission, interpretation, integrity, output contract).
- `plugins/ws-core/includes/admin/tools/prompt-generator/pg-blocks-assist-org.php`
  - Assist-org specific blocks derived from new static template.
- `plugins/ws-core/includes/admin/tools/prompt-generator/pg-blocks-legal.php`
  - Legal record schema blocks rebuilt from ACF, plus legal-specific rules.
- `plugins/ws-core/includes/admin/tools/prompt-generator/pg-builders.php`
  - Final assembler functions per record type.

## 2. Reusable block strategy (minimal variable use)

### Shared blocks for all record types
- Identity/mission framing (tailored line injection only where required).
- Global omission + schema policy.
- Interpretation precedence rule.
- Integrity block contract and anomaly gating.
- Final output contract (single JSON code block).
- Run-scope section formatter.

### Legal-only shared blocks
- Legal taxonomy usage discipline.
- Source hierarchy guidance.
- Record truncation permission language.
- Meta block skeleton (with record-type-specific keys only where needed).

### Assist-org block set
- Use new static template as canonical and split into deterministic blocks:
  - target criteria
  - field dictionary
  - taxonomy tables
  - run scope notes

### Variable interpolation policy
Allow variables only for:
- jurisdiction name/code
- legislature URL
- record counts and modes
- selected record type
- stat-type/min-quality toggles
- exclusion list payload
- operator notes

Everything else should be static block text to reduce drift.

## 3. Legal schema reconstruction approach (ACF-driven)

Rebuild legal schema text from actual ACF fields (not historical prose).

### Statute schema coverage (from `acf-jx-statutes.php`)
- Legal basis
- SOL/deadline/exhaustion
- Enforcement
- Burden of proof
- Reward
- Links
- Relationship and metadata expectations where applicable to generation

### Common-law schema coverage (from `acf-jx-common-law.php`)
- Doctrine identity and legal basis WYSIWYG fields
- Public-policy source model
- SOL/deadline/exhaustion
- Enforcement
- Burden and statutory preclusion
- Reward
- Cases/relationships

### Citation schema coverage (from `acf-jx-citations.php`)
- Case identity/source metadata
- Classification taxonomy axes + sentinel details
- Parent linking to statute/common-law
- Summary/review/link fields

### Interpretation schema coverage (from `acf-jx-interpretations.php`)
- Case identity + court context
- Summary and display semantics
- Classification taxonomy axes + sentinel details
- Parent linkage and affected jurisdiction scope

## 4. Taxonomy handling
- Keep dynamic taxonomy read from WP (single source of truth at runtime) for all record types, including assist-org.
- Do not output parent terms at all (parent exclusion by construction in table output).
- Do not emit hierarchy-vs-flat instructional distinctions; they are unnecessary once parents are hidden.
- Ensure taxonomy applicability matrices include all current registered object type use.
- Standardize table output format across record types to match the manually built style in `in-progress/tools/US-0-Assist-org-20260421-0001.txt`.

## 5. Exclusion mechanics
Preserve behavior from existing workflow:
- Auto exclusions scoped by record type + jurisdiction.
- Merge manual + auto with dedupe and stable sort.
- Respect disable-exclusions flag.
- Preserve operator edit override model for auto exclusions.

## 6. Output naming/path behavior
Preserve:
- Output directory creation and hardening.
- Filename convention:
  - `[JX]-[count]-[RecordType]-[YYYYMMDD-HHmm].txt`

## Implementation Plan

## Phase 1: Scaffold and reinstate tool entry
1. Create new `tool-generate-prompt.php` thin loader.
2. Create `prompt-generator/` module files listed above.
3. Re-enable loader entry for `tool-generate-prompt` after base scaffold passes lint.

## Phase 2: Migrate stable behavior (UI + exclusions + output)
1. Port UI/JS behavior exactly (field toggles, required flags, default scope sync).
2. Port exclusion logic and refresh semantics.
3. Port output write flow and nonce/permission checks.

## Phase 3: Block rewrite using new format
1. Implement shared block catalog from new static prompt style.
2. Implement assist-org builder using canonical static blocks + runtime scope injection.
3. Implement legal shared blocks with concise static contracts.
4. Implement unified dynamic taxonomy-table renderer that matches the manual prompt format and never exposes parent slugs.

## Phase 4: Legal schema reinvention from ACF
1. Build statute/common-law/citation/interpretation schema blocks from ACF maps.
2. Encode conditional rules by field families (toggle -> details, sentinel -> details).
3. Normalize naming so record schemas match ingest expectations.

## Phase 5: Verification and hardening
1. PHP lint all new files.
2. Validate admin page renders and submits for each record type.
3. Confirm prompt output shape and scope sections per record type.
4. Validate exclusion behavior (auto/manual/disabled/refresh).
5. Confirm no loader notices/errors.

## Phase 6: Cleanup
1. Keep archived disabled file (`tool-generate-prompt.php.txt`) until acceptance.
2. After acceptance, remove/archive stale disabled artifact if desired.

## Verification Checklist

### Functional
- Tool page loads from admin tools menu.
- All existing controls appear and behave as before.
- Prompt generation works for all 5 record types.
- Output files are written and retrievable.

### Content integrity
- Assist-org prompt output reflects v3.0 static format style.
- Legal prompts use new block format and rebuilt schemas.
- Shared blocks are consistent across record types with minimal variable substitutions.

### Safety/quality
- No unresolved function references.
- No missing include/load warnings in admin notices.
- No schema block drift from ACF source fields for legal record types.

## Notes for execution
- Preserve admin UX exactly unless explicitly requested otherwise.
- Prioritize deterministic static block composition over ad hoc string building.
- Treat ACF files as authoritative for legal schema content.
- Keep rewrite self-contained and maintainable for future prompt revisions.
