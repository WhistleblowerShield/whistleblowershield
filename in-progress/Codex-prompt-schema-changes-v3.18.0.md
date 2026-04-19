---

## Codex Prompt — Employment Sector + Employer Threshold: Schema Additions

### Context

Two new data points need to be added across multiple CPTs. This is a mechanical field addition pass — no logic changes, no new hooks, no architectural changes. Add fields, update query returns, update prompt template. Nothing else.

---

### Version bump

Bump `WS_CORE_VERSION` to `3.18.0`. Add changelog entry:

```
* 3.18.0  Schema additions — employment sector and employer threshold:
*         - ws_employment_sectors added to jx-statute, jx-common-law,
*           jx-citation, jx-interpretation ACF groups
*         - ws_employment_sectors added to ws-agency and ws-ag-procedure
*           ACF groups
*         - ws_jx_statute_has_employer_threshold + _details added to
*           jx-statute ACF group
*         - ws_jx_comlaw_has_employer_threshold + _details added to
*           jx-common-law ACF group
*         - ws_jx_citation_has_employer_threshold + _details added to
*           jx-citation ACF group
*         - ws_jx_interp_has_employer_threshold + _details added to
*           jx-interpretation ACF group
*         - Query layer updated: all four functions return sector and
*           threshold fields
*         - tool-generate-prompt.php updated: sector and threshold
*           fields added to all four record type schemas and field rules
```

---

### 1. ACF — `acf-jx-statutes.php`

Add to the **Legal Basis** tab, after the existing `ws_disclosure_target` taxonomy field:

```php
[
    'key'           => 'field_jx_statute_employment_sectors',
    'label'         => 'Employment Sectors',
    'name'          => 'ws_jx_statute_employment_sectors',
    'type'          => 'taxonomy',
    'taxonomy'      => 'ws_employment_sector',
    'field_type'    => 'multi_select',
    'instructions'  => 'Employment sectors this statute explicitly covers. Tag only what the statute text supports.',
    'required'      => 0,
    'add_term'      => 0,
    'save_terms'    => 1,
    'load_terms'    => 1,
    'return_format' => 'id',
],
```

Add to the **Enforcement** tab, after `ws_jx_statute_has_exhaustion` / `exhaustion_details` block, following the exact same toggle + conditional pattern:

```php
[
    'key'           => 'field_jx_statute_has_employer_threshold',
    'label'         => 'Employer Size Threshold',
    'name'          => 'ws_jx_statute_has_employer_threshold',
    'type'          => 'true_false',
    'instructions'  => 'Enable when the statute restricts coverage based on employer size (e.g. "employers with 15 or more employees").',
    'ui'            => 1,
    'ui_on_text'    => 'Yes',
    'ui_off_text'   => 'No',
    'default_value' => 0,
],
[
    'key'               => 'field_jx_statute_employer_threshold_details',
    'label'             => 'Employer Threshold Details',
    'name'              => 'ws_jx_statute_employer_threshold_details',
    'type'              => 'textarea',
    'instructions'      => 'Describe the employer size requirement as stated in the statute. Include the threshold number and whether it is a minimum or maximum.',
    'required'          => 0,
    'rows'              => 3,
    'conditional_logic' => [ [ [
        'field'    => 'field_jx_statute_has_employer_threshold',
        'operator' => '==',
        'value'    => '1',
    ] ] ],
],
```

---

### 2. ACF — `acf-jx-common-law.php`

Same two additions:

**Legal Basis tab** — after disclosure_target taxonomy field:
```php
[
    'key'           => 'field_jx_comlaw_employment_sectors',
    'label'         => 'Employment Sectors',
    'name'          => 'ws_jx_comlaw_employment_sectors',
    'type'          => 'taxonomy',
    'taxonomy'      => 'ws_employment_sector',
    'field_type'    => 'multi_select',
    'instructions'  => 'Employment sectors this doctrine explicitly covers.',
    'required'      => 0,
    'add_term'      => 0,
    'save_terms'    => 1,
    'load_terms'    => 1,
    'return_format' => 'id',
],
```

**Enforcement tab** — after exhaustion block:
```php
[
    'key'           => 'field_jx_comlaw_has_employer_threshold',
    'label'         => 'Employer Size Threshold',
    'name'          => 'ws_jx_comlaw_has_employer_threshold',
    'type'          => 'true_false',
    'instructions'  => 'Enable when the doctrine restricts coverage based on employer size.',
    'ui'            => 1,
    'ui_on_text'    => 'Yes',
    'ui_off_text'   => 'No',
    'default_value' => 0,
],
[
    'key'               => 'field_jx_comlaw_employer_threshold_details',
    'label'             => 'Employer Threshold Details',
    'name'              => 'ws_jx_comlaw_employer_threshold_details',
    'type'              => 'textarea',
    'instructions'      => 'Describe the employer size requirement as stated in the doctrine.',
    'required'          => 0,
    'rows'              => 3,
    'conditional_logic' => [ [ [
        'field'    => 'field_jx_comlaw_has_employer_threshold',
        'operator' => '==',
        'value'    => '1',
    ] ] ],
],
```

---

### 3. ACF — `acf-jx-citations.php`

Sector only — citations refine statute sector coverage, same field pattern:

```php
[
    'key'           => 'field_jx_citation_employment_sectors',
    'label'         => 'Employment Sectors',
    'name'          => 'ws_jx_citation_employment_sectors',
    'type'          => 'taxonomy',
    'taxonomy'      => 'ws_employment_sector',
    'field_type'    => 'multi_select',
    'instructions'  => 'Employment sectors this ruling affects. May refine, extend, or restrict the parent statute\'s sector coverage.',
    'required'      => 0,
    'add_term'      => 0,
    'save_terms'    => 1,
    'load_terms'    => 1,
    'return_format' => 'id',
],
```

Threshold — same toggle + conditional pattern, prefix `ws_jx_citation_*`:

```php
[
    'key'           => 'field_jx_citation_has_employer_threshold',
    'label'         => 'Employer Size Threshold',
    'name'          => 'ws_jx_citation_has_employer_threshold',
    'type'          => 'true_false',
    'instructions'  => 'Enable when this ruling addresses or modifies employer size threshold coverage.',
    'ui'            => 1,
    'ui_on_text'    => 'Yes',
    'ui_off_text'   => 'No',
    'default_value' => 0,
],
[
    'key'               => 'field_jx_citation_employer_threshold_details',
    'label'             => 'Employer Threshold Details',
    'name'              => 'ws_jx_citation_employer_threshold_details',
    'type'              => 'textarea',
    'instructions'      => 'Describe how this ruling affects employer size threshold coverage.',
    'required'          => 0,
    'rows'              => 3,
    'conditional_logic' => [ [ [
        'field'    => 'field_jx_citation_has_employer_threshold',
        'operator' => '==',
        'value'    => '1',
    ] ] ],
],
```

---

### 4. ACF — `acf-jx-interpretations.php`

Same as citations. Field key prefix `field_jx_interp_*`, meta key prefix `ws_jx_interp_*`:

Sector field — after disclosure_target taxonomy field in the Summary tab.

Threshold — toggle + conditional, same pattern as above.

---

### 5. ACF — `acf-agencies.php`

Sector only — agency carries all sectors it operates across. Add to the **Agency Identity** tab:

```php
[
    'key'           => 'field_agency_employment_sectors',
    'label'         => 'Employment Sectors',
    'name'          => 'ws_agency_employment_sectors',
    'type'          => 'taxonomy',
    'taxonomy'      => 'ws_employment_sector',
    'field_type'    => 'multi_select',
    'instructions'  => 'All employment sectors this agency has jurisdiction over.',
    'required'      => 0,
    'add_term'      => 0,
    'save_terms'    => 1,
    'load_terms'    => 1,
    'return_format' => 'id',
],
```

---

### 6. ACF — `acf-ag-procedures.php`

Sector only — procedure carries the specific sector this pathway applies to. Add to the **Filing Details** tab:

```php
[
    'key'           => 'field_ag_procedure_employment_sectors',
    'label'         => 'Employment Sectors',
    'name'          => 'ws_ag_procedure_employment_sectors',
    'type'          => 'taxonomy',
    'taxonomy'      => 'ws_employment_sector',
    'field_type'    => 'multi_select',
    'instructions'  => 'Employment sectors this specific procedure applies to.',
    'required'      => 0,
    'add_term'      => 0,
    'save_terms'    => 1,
    'load_terms'    => 1,
    'return_format' => 'id',
],
```

---

### 7. Query layer — `query-jurisdiction.php`

**`ws_get_jx_statute_data()`** — add to the row array:

```php
'employment_sectors'          => ws_q_normalize_id_list( get_field( 'ws_jx_statute_employment_sectors', $sid ) ),
'has_employer_threshold'     => (bool) get_post_meta( $sid, 'ws_jx_statute_has_employer_threshold', true ),
'employer_threshold_details' => get_post_meta( $sid, 'ws_jx_statute_employer_threshold_details', true ),
```

**`ws_get_jx_common_law_data()`** — add to the row array:

```php
'employment_sectors'          => ws_q_normalize_id_list( get_field( 'ws_jx_comlaw_employment_sectors', $rid ) ),
'has_employer_threshold'     => (bool) get_post_meta( $rid, 'ws_jx_comlaw_has_employer_threshold', true ),
'employer_threshold_details' => get_post_meta( $rid, 'ws_jx_comlaw_employer_threshold_details', true ),
```

**`ws_get_jx_citation_data()`** — add to the row array:

```php
'employment_sectors'          => ws_q_normalize_id_list( get_field( 'ws_jx_citation_employment_sectors', $cid ) ),
'has_employer_threshold'     => (bool) get_post_meta( $cid, 'ws_jx_citation_has_employer_threshold', true ),
'employer_threshold_details' => get_post_meta( $cid, 'ws_jx_citation_employer_threshold_details', true ),
```

**`ws_get_jx_interpretation_data()`** — add to the row array:

```php
'employment_sectors'          => ws_q_normalize_id_list( get_field( 'ws_jx_interp_employment_sectors', $iid ) ),
'has_employer_threshold'     => (bool) get_post_meta( $iid, 'ws_jx_interp_has_employer_threshold', true ),
'employer_threshold_details' => get_post_meta( $iid, 'ws_jx_interp_employer_threshold_details', true ),
```

---

### 8. Query layer — `query-agencies.php`

**`ws_get_agency_data()`** — add to the row array:

```php
'employment_sectors' => ws_q_normalize_id_list( get_field( 'ws_agency_employment_sectors', $aid ) ),
```

**`ws_get_jx_procedure_data()`** or equivalent — add to the row array:

```php
'employment_sectors' => ws_q_normalize_id_list( get_field( 'ws_ag_procedure_employment_sectors', $pid ) ),
```

---

### 9. `tool-generate-prompt.php`

**Statute and common-law schemas** — add to the `legal_basis` block:

```
"employment_sectors":          ["ws_employment_sectors slugs — tag all that apply; omit when coverage is not sector-specific"],
```

Add to the `enforcement` block:

```
"has_employer_threshold":     false,
"employer_threshold_details": "",
```

**Citation and interpretation schemas** — add to the record schema:

```
"employment_sectors":          ["ws_employment_sectors slugs — tag only when this ruling refines sector coverage from the parent statute"],
"has_employer_threshold":     false,
"employer_threshold_details": "",
```

**Field rules** — add to the statute/common-law omission guidance:

```
has_employer_threshold        true when the statute restricts coverage by employer size.
                              When true, employer_threshold_details is required.
employer_threshold_details    Describe the threshold as stated in the statute text
                              (e.g. "Employers with 15 or more employees").
                              Omit when has_employer_threshold is false.
employment_sectors            Tag all sectors the statute explicitly covers.
                              Omit when coverage is not sector-specific or unclear.
```

---

### Do not touch

- `register-taxonomies.php` — `ws_employment_sector` is already registered and seeded
- `ws-assist-org` ACF — already has employment sector
- All other ACF files not listed above
- All render files
- All shortcode files
- All admin files except the ACF files listed above
- The ingest tool — field map additions are a separate pass

---

### Verification

After changes, confirm:
- All six ACF files load without PHP errors
- Taxonomy field saves and loads correctly on a test statute post
- Toggle fires conditional textarea on citation and interpretation edit screens
- Query layer returns `employment_sectors`, `has_employer_threshold`, `employer_threshold_details` on statute records