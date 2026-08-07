# AntiGravity Refactor Notes

This document tracks architectural changes, removed legacy code, and technical debt items that arose during the refactoring process.

## Removed Legacy Features

### Taxonomy Term Meta (`display_order`)
- **Taxonomy:** `ws_jurisdiction`
- **Context:** In the legacy codebase, the `ws_seed_jurisdiction_taxonomy()` seeder applied a custom `display_order` term meta value (from 1 to 57) to each jurisdiction term to enforce a specific sorting order (Federal, DC, States, Territories). 
- **Change:** The rewritten `ws_seed_taxonomy()` logic is completely registry-driven and does not support arbitrary term meta insertion out-of-the-box. The `display_order` generation was dropped.
- **Impact:** Any ACF configurations, frontend templates, or legacy query filters (such as the one previously found in `admin-hooks.php`) that explicitly ordered jurisdiction lists `orderby => 'meta_value_num'` using the `display_order` meta key will no longer function. 
- **Action Required:** Since PHP arrays natively preserve the registry order during initialization, sorting can likely be handled by retrieving terms in the order they were seeded (or ordering by term ID, since they are seeded sequentially) without relying on DB meta. If ACF or a template strictly requires meta-based ordering, we can update the ACF query filters instead of bloating the seeder.
