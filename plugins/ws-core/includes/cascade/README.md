# includes/cascade/

This folder contains the WhistleblowerShield filter cascade implementation for the Phase 2 assist-organization directory.

Files here define the shared filter contract and normalize incoming request context so renderers and query builders do not read `$_GET` directly.

## Files

- `ws-filter-config.php`
  - Defines all allowed directory filter GET parameters.
  - Maps valid param values to taxonomy slugs.
  - Houses scoring weights and engagement weight config used by directory sorting.
  - Serves as the single source of truth for filter parameter names and allowed values.

- `ws-filter-context.php`
  - Registers the filter GET params with WordPress query vars.
  - Reads and sanitizes request input via `get_query_var()` or `$_GET`.
  - Validates values against the config map.
  - Routes `ws_concern` to the correct taxonomy based on stage.
  - Returns a normalized filter context array consumed by directory renderers and query layers.

## Design Principles

- No renderer or shortcode reads `$_GET` directly.
- Invalid or unknown filter values are treated as absent, not as errors.
- Concern values are resolved to `ws_protected_disclosure` or `ws_adverse_action` depending on the stage.
- This layer is intentionally narrow: it standardizes filter input and normalization, leaving actual query building and rendering to downstream modules.
