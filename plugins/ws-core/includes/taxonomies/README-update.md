# includes/taxonomies/

Taxonomy registration and seeding for ws-core.

Authoritative source: `register-taxonomies.php`.

## Files

- `register-taxonomies.php`
- `register-glossary.php`
- taxonomy reference text files used by research/prompt workflows

## Current Naming Direction

Canonical taxonomy slugs use current singularized names where migrated, including:

- `ws_adverse_action_type`
- `ws_remedy`
- `ws_language`

Core shared doctrinal taxonomies and assist-org filter taxonomies are all defined in `register-taxonomies.php`.

## Seeding

Taxonomy seeders are gate-versioned in options (`ws_seeded_*`).
To rerun a seed path, bump gate versions intentionally and preserve duplicate guards.

## has-details Sentinel Pattern

`has-details` sentinel behavior is used by selected taxonomies and paired ACF `*_details` fields. Keep taxonomy seed terms and ACF conditional logic synchronized.

## Rule

If PHP and reference text files ever diverge, update the text files in the same pass.
