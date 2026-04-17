# includes/admin/matrix/

Matrix seeders and divergence watch.

## Purpose

- Seed baseline records and registries on gated runs.
- Mark matrix-origin records for divergence tracking.

## Files

- `matrix-helpers.php`
- `matrix-jurisdictions.php`
- `matrix-federal-courts.php`
- `matrix-state-courts.php`
- `matrix-fed-statutes.php`
- `matrix-agencies.php`
- `matrix-assist-orgs.php`
- `matrix-ag-procedures.php`
- `admin-matrix-watch.php`

## Operational Rules

- Seeder gates are option-version based (`ws_seeded_*`).
- Keep seed runs idempotent.
- `ws_matrix_source` should identify the matrix source consistently with current file/source naming.
- Divergence watch should only track post-seed human edits, not seed-time writes.
