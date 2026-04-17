# ws-core

Core plugin for WhistleblowerShield.org.

This plugin uses a layered architecture with strict boundaries:

1. Universal layer (`includes/cpt`, `includes/taxonomies`, `includes/acf`, `includes/queries`)
2. Admin layer (`includes/admin`, plus `includes/admin/matrix` and `includes/admin/monitors`)
3. Frontend assembly layer (`includes/render`, `includes/shortcodes`)

## Core Rules

- Query functions are the only place that should read content data from the database for frontend output.
- Render functions return HTML strings; they do not perform direct content queries.
- Shortcodes assemble data and call render functions.
- ACF `name` keys remain `ws_*`; query-return keys are unprefixed, contributor-friendly arrays.

## CPTs (Current)

- `jurisdiction`
- `jx-summary`
- `jx-statute`
- `jx-common-law`
- `jx-citation`
- `jx-interpretation`
- `ws-agency`
- `ws-ag-procedure`
- `ws-assist-org`
- `ws-legal-update`
- `ws-reference`

## Taxonomy Notes

Canonical taxonomies are registered in `includes/taxonomies/register-taxonomies.php`.
Current naming uses singular forms where migrated (for example `ws_adverse_action_type`, `ws_remedy`, `ws_language`).

## Where To Read Next

- `includes/acf/README.md`
- `includes/queries/README.md`
- `includes/render/README.md`
- `includes/shortcodes/README.md`
- `includes/admin/README.md`
