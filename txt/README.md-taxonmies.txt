# includes/taxonomies/

Taxonomy registration and seeding for all ws-core taxonomies.

---

## Files

| File | Purpose |
|---|---|
| `register-taxonomies.php` | Registers all 17 taxonomies and runs seeders on first admin load |
| `register-glossary.php` | Registers the `ws_glossary` taxonomy and seeds terms |
| `taxonomy-statutes.txt` | Taxonomy reference for jx-statute, jx-citation, jx-interpretation, jx-common-law |
| `taxonomy-citations.txt` | Taxonomy reference for jx-citation (mirrors statutes) |
| `taxonomy-interpretations.txt` | Taxonomy reference for jx-interpretation (mirrors statutes) |
| `taxonomy-agencies.txt` | Taxonomy reference for ws-agency |
| `taxonomy-aorgs.txt` | Taxonomy reference for ws-assist-org |
| `taxonomy-tables.txt` | Human-readable flat reference of all taxonomy terms and slugs |

`taxonomy-common-law.txt` — pending creation for jx-common-law pipeline reference. Create when Wyoming data build begins.

---

## Current Naming Direction

Canonical taxonomy slugs use singularized names. Migrated slugs include:

- `ws_adverse_action_type`
- `ws_remedy`
- `ws_language`
- `ws_disclosure_target`

All core shared doctrinal taxonomies and assist-org filter taxonomies
are defined in `register-taxonomies.php`.

**Rule:** If the PHP taxonomy slug and any reference text file ever
diverge, update the text files in the same pass. The PHP file is the
authoritative source of truth.

---

## Two-Phase Registration Behaviour

WordPress requires taxonomies to be registered before CPTs that use
them, but ACF `save_terms` / `load_terms` requires CPTs to be
registered before the taxonomy's `object_type` array is finalized.

This is resolved in `loader.php` by registering taxonomies in two
passes — initial registration on `init` before CPTs, then
`object_type` binding after CPTs are registered. This is documented
in the `TAXONOMY TWO-PHASE BEHAVIOUR` section of `loader.php` and
must not be collapsed into a single pass.

---

## Seeder Gate Standard

All taxonomy seeders use the `ws_seeded_{slug}` option key with a
semver string value:

```php
if ( get_option( 'ws_seeded_disclosure_taxonomy' ) !== '1.0.0' ) {
    ws_seed_disclosure_taxonomy();
    update_option( 'ws_seeded_disclosure_taxonomy', '1.0.0' );
}
```

**To re-run a seeder:** bump the version string in the seeder gate
comparison. Never delete the option — deleting it causes the seeder
to re-run against existing terms and produce duplicates or errors.

**To add new terms to an existing seeder:** bump the gate version,
add the new terms to the seeder function, and ensure
`term_exists()` guards prevent duplicate insertion.

---

## `ws_bulk_insert_hierarchical()` Helper

Hierarchical taxonomies use the shared helper defined in
`register-taxonomies.php`. It handles parent-child term creation
in the correct order and prevents duplicate insertion.

Flat taxonomies use `wp_insert_term()` directly with a
`term_exists()` guard.

---

## has-details Sentinel Pattern

Six taxonomies include a `has-details` sentinel term:
`ws_protected_class`, `ws_disclosure_targets`, `ws_adverse_action_types`,
`ws_remedies`, `ws_employer_defense`, `ws_employee_standard`.

When an editor selects `has-details` in a taxonomy multi-select field,
a companion ACF freetext `_details` textarea becomes visible via dynamic
conditional logic (injected at field load time — not at registration time,
because term IDs are only available at runtime).
