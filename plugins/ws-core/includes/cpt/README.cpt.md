# includes/cpt/

CPT registration files for all ws-core post types.

Each file registers one CPT and nothing else. No hooks, no ACF
fields, no query logic. One file, one CPT, one job.

---

## CPT Registry

| Slug | File | `menu_position` | `publicly_queryable` | `has_archive` |
|---|---|---|---|---|
| `jurisdiction` | `cpt-jurisdictions.php` | 25 | true | false |
| `jx-summary` | `cpt-jx-summaries.php` | 26 | false | false |
| `jx-statute` | `cpt-jx-statutes.php` | 32 | false | false |
| `jx-common-law` | `cpt-jx-common-law.php` | 33 | false | false |
| `jx-citation` | `cpt-jx-citations.php` | 27 | false | false |
| `jx-construction` | `cpt-jx-constructions.php` | 29 | false | false |
| `ws-agency` | `cpt-agencies.php` | 28 | true | — |
| `ag-procedure` | `cpt-ag-procedures.php` | 29 | true | false |
| `ws-assist-org` | `cpt-assist-orgs.php` | 30 | true | — |
| `ws-legal-update` | `cpt-legal-updates.php` | 25 | false | false |
| `ws-reference` | `cpt-references.php` | 32 | true | false |

---

## jx-common-law

Added v3.13.0. Stores judicially-recognized common law whistleblower
protection doctrines. Not publicly queryable — rendered through the
jurisdiction page assembler the same way `jx-statute` records are.

Uses dashicons-hammer to distinguish from `jx-statute` (dashicons-media-document)
in the admin sidebar.

See `acf-jx-common-law.php` for field documentation and
`ws_get_jx_common_law_data()` in `query-jurisdiction.php` for the
query layer interface.

---

## Why `has_archive: false` Everywhere

Archive pages are not part of the platform's information architecture.
Content is surfaced via jurisdiction pages (assembled by the render
layer) and the directory shortcode — not via WordPress archive URLs.
Enabling archives would produce unstyled, uncontrolled listing pages.

---

## Why Some CPTs Are Not `publicly_queryable`

`jx-summary`, `jx-statute`, `jx-common-law`, `jx-citation`,
`jx-construction`, and `ws-legal-update` are not publicly queryable.
They are never accessed directly by URL — their content is assembled
and rendered on jurisdiction pages by the Assembly Layer.

`jurisdiction`, `ws-agency`, `ag-procedure`, `ws-assist-org`, and
`ws-reference` are publicly queryable because they have dedicated
render handlers that produce a styled page.

---

## `menu_position` Allocation

Admin sidebar positions are allocated to keep related CPTs adjacent:

```
25  jurisdiction, ws-legal-update
26  jx-summary
27  jx-citation
28  ws-agency
29  jx-construction, ag-procedure
30  ws-assist-org
32  jx-statute, ws-reference
33  jx-common-law
```

If adding a new CPT, check this table before assigning a position
to avoid collision with WordPress core menu items (80, 85, 90, 99)
and existing CPTs.

---

## CPT Component Version History

### `cpt-jurisdictions.php`
* **1.0.0:** Initial release.
* **2.1.0:** ws-core architecture refactor.

### `cpt-jx-common-laws.php`
* **3.13.0:** Initial release.

### `cpt-assist-orgs.php`
* **1.0.0:** Initial release.
* **1.0.1:** corrected menu_position from 31 to 30.
* **3.7.0:** added `ws_employment_sector` taxonomy.
* **3.11.0:** expanded local taxonomy declarations to include process type, case stage, languages, disclosure targets, org type, employment sector, cost model, services, and jurisdiction.

### `cpt-jx-citations.php`
* **2.3.0:** Initial release.
* **3.0.0:** retired `ws_jx_code` join; migrated to `WS_JURISDICTION_TAXONOMY` taxonomy queries.

### `cpt-jx-constructions.php`
* **2.4.0:** Initial release.
* **2.4.1:** corrected menu_position from 28 to 29.

### `cpt-jx-statutes.php`
* **1.0.0:** Initial release.
* **2.1.0:** ws-core architecture refactor. CPT slug standardized to hyphenated convention `jx-statute`.
* **3.2.0:** added `ws_employer_defense` taxonomy.
* **3.7.0:** corrected taxonomies array, removing deprecated slugs (`ws_remedy_type`, `ws_coverage_scope`, `ws_retaliation_forms`).

### `cpt-legal-updates.php`
* **1.0.0:** Initial release.
* **1.9.0:** renamed CPT from `legal-update` to `ws-legal-update`.
* **2.1.0:** ws-core architecture refactor; standardized slug to `ws-legal-update`, renamed file from `cpt-legal-update.php` to `cpt-ws-legal-update.php`.
