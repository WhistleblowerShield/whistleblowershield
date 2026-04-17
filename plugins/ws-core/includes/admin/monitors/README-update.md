# includes/admin/monitors/

Cron-driven monitoring systems.

## Files

- `admin-url-monitor.php`
- `admin-feed-monitor.php`

## URL Monitor

Checks configured URL fields, classifies status outcomes, logs issues, and reports recoveries.

## Feed Monitor

Polls configured feed sources, stages candidates, and supports accept/reject editorial workflow.

## Reliability Notes

- WP-Cron timing is traffic dependent.
- For production reliability, pair with server cron triggering `wp-cron.php`.
- Keep monitor option keys and staging/log paths stable unless migration is intentional.
