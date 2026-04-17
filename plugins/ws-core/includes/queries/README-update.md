# includes/queries/

The query layer.

This layer returns normalized PHP arrays and is the authoritative frontend data API for renderers and shortcodes.

## Load Order

1. `query-helpers.php`
2. `query-shared.php`
3. `query-jurisdiction.php`
4. `query-general.php`
5. `query-directory.php`
6. `query-agencies.php`

## Contracts

- Do not return raw `ws_*` keys to callers unless intentionally nested payloads require that shape.
- Shared payload blocks:
  - `record`
  - `plain`
  - `verify`
- Curated jurisdiction datasets use CPT-specific attach flags:
  - `ws_jx_statute_has_attach_flag`
  - `ws_jx_citation_has_attach_flag`
  - `ws_jx_interp_has_attach_flag`
  - `ws_jx_comlaw_has_attach_flag`

## Key Data APIs

- Jurisdiction datasets: `query-jurisdiction.php`
- Cross-cutting/general datasets: `query-general.php`
- Directory datasets: `query-directory.php`
- Agency/procedure datasets: `query-agencies.php`

## Caching

Transient usage and invalidation are defined inline in each query file. Keep cache key names aligned with current schema names (for example `ws_agency_procedures_*`, `ws_statute_procedures_*`).
