# includes/acf/

ACF field-group registration for ws-core CPTs.

Each main file in this directory owns one primary CPT group. Shared cross-CPT workflow groups live in `workflow/`.

## Primary Group Files

- `acf-jurisdictions.php`
- `acf-jx-summaries.php`
- `acf-jx-statutes.php`
- `acf-jx-common-law.php`
- `acf-jx-citations.php`
- `acf-jx-interpretations.php`
- `acf-agencies.php`
- `acf-ag-procedures.php`
- `acf-assist-orgs.php`
- `acf-legal-updates.php`
- `acf-references.php`

## Naming Conventions (Current)

- Meta names: `ws_[record]_[field]`
- Field keys: `field_[record]_[field]` (mirror meta name without `ws_`)
- Group keys: `group_[record]_metadata`
- Tab keys:   `field_[record]_[tab-label]_tab`

## Field Behavior Rules

- Taxonomy slugs in field config should match current singular taxonomy table names.
- Multi-select taxonomy/meta fields should be pluralized in meta naming.
- Toggle fields used for conditional logic should use `has` semantics consistently.
- Companion explanatory fields use `*_details` unless intentionally internal-note semantics apply.

For shared workflow groups and their contracts, see `includes/acf/workflow/README.md`.
