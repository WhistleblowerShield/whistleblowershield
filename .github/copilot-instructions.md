# Project Guidelines

## Architecture
- Treat the query layer as the only data-read layer. Route retrieval through `plugins/ws-core/includes/queries/` and do not add direct `get_field()`, `get_post_meta()`, or `WP_Query` reads in render/shortcode code.
- Preserve strict load/dependency ordering described in `plugins/ws-core/includes/loader.php` and `plugins/ws-core/includes/queries/README.md` when adding modules.
- Use `ws_jurisdiction` taxonomy slugs (two-letter USPS-style codes such as `ca`, `tx`, `us`) as the canonical cross-content join key.
- Keep assembly responsibilities separate: query functions return normalized arrays, render/shortcode code assembles output.
- Keep the established six-layer split intact (data/query, matrix, admin, assembly, assets) and add logic to the right layer rather than creating cross-layer shortcuts.

## Code Ownership Boundary
- Treat `plugins/ws-core/vendor/` as vendored third-party code (localized Sentry stack), not first-party project code.
- Do not treat Composer metadata in `plugins/ws-core/composer.json` and `plugins/ws-core/composer.lock` as the source of truth for project architecture or contribution workflow.
- First-party Sentry integration points are limited to `plugins/ws-core/includes/sentry_init.php`, Sentry bootstrap lines in `plugins/ws-core/ws-core.php` (`ws_core_init()`), and the `sentry_init` load/invocation path in `plugins/ws-core/includes/loader.php`.
- Prefer changes in first-party integration code over edits inside vendored libraries unless explicitly requested.
- At first-party/vendor integration boundaries (especially loader/bootstrap includes), add brief inline comments that clarify ownership and privacy intent.

## Build And Test
- Primary codebase is the WordPress plugin in `plugins/ws-core/`.
- There is no repository-standard automated test suite yet; validate changes with focused manual checks in the staging WordPress environment and relevant admin tools.
- Do not default to Composer install/update workflows for normal feature work in this repo.
- Validate behavior in the relevant WordPress admin surfaces (matrix tools, ingest/prompt tools, dashboards) when touching those systems.
- A dummy WordPress testing environment is available at `C:/Users/dejunai/projects/_entire_site/` for end-to-end plugin validation.
- User environment convention: `C:/Users/dejunai/projects/` is expected to be available from `PATH`; prefer portable commands that work from that root without hardcoded machine-specific detours.

## Conventions
- Follow existing naming patterns documented in `plugins/ws-core/README.md` (`ws_` meta prefixes, CPT-specific infixes, query-return key conventions).
- Keep ACF and data-layer changes aligned with documentation and query mappings; update docs when behavior changes.
- When changing matrix seeders or admin tools, preserve idempotent behavior and existing option/version gate patterns.
- Prefer minimal, targeted edits that preserve established architecture over compatibility layers or broad refactors.
- Prefer canonical replacement over adaptation: when direction changes, update the codebase to the new canonical path instead of layering migration/mitigation/legacy shims.
- Do not add one-off fallback branches, backward-compat guards, or transitional data-preservation code unless explicitly requested.
- If existing stored data conflicts with the canonical model, prefer reset/reseed workflows over in-code adaptation logic.
- Decision accountability: if canonical-only changes cause breakage, treat that as an accepted tradeoff from this strategy rather than a reason to silently add adaptive code.
- It is acceptable (and expected) to warn when adaptive/compatibility code could reduce short-term risk, but do not implement it unless explicitly approved after a direct discussion.
- Preserve the canonical rule that query-layer output is normalized and prefix-stripped; do not leak raw meta naming conventions into render/shortcode output APIs.

## Workspace Focus
- Default code reasoning scope to `plugins/ws-core/`, `documentation/`, and `in-progress/`.
- Treat `legacy/` and `ignore/` as non-canonical historical/experimental areas unless explicitly requested.
- Treat `.zip` files as non-source artifacts by default, except `in-progress/logs/logs.zip` which is an intentional log archive.

## Documentation Hub
- Start at `documentation/README.md` for documentation map and key concepts.
- System architecture: `documentation/architecture/system-architecture.md`
- Development references:
  - `documentation/development/ws-core-system.md`
  - `documentation/development/ws-core-data-layer.md`
  - `documentation/development/ws-core-query-layer.md`
  - `documentation/development/ws-core-output-layer.md`
  - `documentation/development/ws-core-audit-and-integrity.md`
- Product/editorial context:
  - `documentation/product/guidance-system.md`
  - `documentation/editorial/editorial-system.md`
  - `documentation/editorial/research-and-transparency.md`
- Roadmap/status: `documentation/project/project-status.md`, `documentation/proposals/current-proposals.md`

## Common Pitfalls
- Do not bypass the query layer in frontend output code.
- Do not duplicate long-form rules from documentation; link to the appropriate docs and keep instructions concise.
- Ensure WordPress can write logs used by admin tools under `wp-content/logs/`.
- Do not misclassify vendored Sentry files as first-party feature code when estimating scope, reviewing architecture, or proposing edits.