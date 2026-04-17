# includes/admin/

Admin-only layer for ws-core.

Covers editorial hooks, diagnostics, dashboards, matrix seeding watch, and monitoring systems.

## Main Files

- `admin-hooks.php`
- `admin-columns.php`
- `admin-navigation.php`
- `admin-audit-trail.php`
- `admin-major-edit-hook.php`
- `admin-citation-metabox.php`
- `admin-interpretation-metabox.php`
- `admin-procedure-watch.php`
- `jurisdiction-dashboard.php`
- `admin-health-check.php`

## Subdirectories

- `matrix/` (seeders + divergence watch)
- `monitors/` (cron-driven monitors)
- `tools/` (operator tooling)

## Boundaries

Admin files may perform direct reads when hook context requires raw values.
Frontend output code (shortcodes/render) should not copy this pattern.

## Priority Discipline

`acf/save_post` and `save_post` hook priorities are load-bearing. Maintain explicit sequencing when modifying stamp/plain/verify/major-edit/audit behavior.
