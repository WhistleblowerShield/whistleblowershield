## Codex Prompt — Assist-Org Ingest: Nested Schema Migration + Field Map Updates

### Context

This file has been restored from a known-good backup. The assist-org ingest section needs two things applied in one pass:

1. **Nested schema migration** — the JSON schema now groups fields under six top-level keys (`identity`, `scope_of_service`, `contact`, `eligibility`, `security`, `review`). The ingest tool currently expects flat keys. Add a flatten helper and update all assist-org functions to use it.

2. **Field map updates** — several fields were added, renamed, or corrected since the backup was made.

All changes are confined to the assist-org section only. Do not touch statute, common-law, citation, or interpretation processors, field maps, or allowed key lists.

---

### Version bump

Bump `WS_INGEST_VERSION` to `3.17.0` and add changelog entry:

```
* 3.17.0  Assist-org ingest: nested schema migration and field map updates:
*         - ws_ingest_allowed_record_keys() updated: flat key list replaced
*           with six top-level group names plus _reconciled_notes
*         - ws_ingest_flatten_assist_org_record() helper added
*         - ws_ingest_get_record_identifier() updated to read from flattened record
*         - ws_ingest_validate_record_shape() updated to flatten before
*           checking required fields
*         - ws_ingest_process_assist_org_record() updated to flatten at top;
*           all field reads use $flat
*         - Field map: cost_model → cost_models (plural, multi-select);
*           legitimacy_url, protected_classes, protected_class_details,
*           secure_contact_tool_other, income_eligibility_details added
*         - Allowed keys updated to match new schema fields
```

---

### 1. `ws_ingest_allowed_record_keys()` — assist-org branch

Replace the existing flat key list with:

```php
if ( $record_type === 'assist-org' ) {
    return [
        'identity',
        'scope_of_service',
        'contact',
        'eligibility',
        'security',
        'review',
        '_reconciled_notes',
    ];
}
```

---

### 2. New helper — add immediately before `ws_ingest_validate_record_shape()`

```php
/**
 * Flattens a nested assist-org record into a single working array.
 *
 * The assist-org JSON schema groups fields under six top-level keys:
 * identity, scope_of_service, contact, eligibility, security, review.
 * The field map loop and validators work against flat keys, so this
 * helper merges all groups into one array before processing.
 *
 * Later groups win on key collision — this is intentional and documented
 * here for future reference. No current fields collide across groups.
 *
 * @param  array $record Raw assist-org record from JSON batch.
 * @return array         Flat key-value array ready for field map loop.
 */
function ws_ingest_flatten_assist_org_record( array $record ): array {
    return array_merge(
        (array) ( $record['identity']         ?? [] ),
        (array) ( $record['scope_of_service'] ?? [] ),
        (array) ( $record['contact']          ?? [] ),
        (array) ( $record['eligibility']      ?? [] ),
        (array) ( $record['security']         ?? [] ),
        (array) ( $record['review']           ?? [] ),
    );
}
```

---

### 3. `ws_ingest_get_record_identifier()` — assist-org branch

```php
// Before:
if ( $record_type === 'assist-org' ) {
    $internal_id = trim( (string) ( $record['internal_id'] ?? '' ) );
    if ( $internal_id !== '' ) {
        return $internal_id;
    }
    $org_name = trim( (string) ( $record['organization_name'] ?? '' ) );
    if ( $org_name !== '' ) {
        return $org_name;
    }
    return 'UNKNOWN';
}

// After:
if ( $record_type === 'assist-org' ) {
    $flat        = ws_ingest_flatten_assist_org_record( $record );
    $internal_id = trim( (string) ( $flat['internal_id'] ?? '' ) );
    if ( $internal_id !== '' ) {
        return $internal_id;
    }
    $org_name = trim( (string) ( $flat['official_name'] ?? '' ) );
    if ( $org_name !== '' ) {
        return $org_name;
    }
    return 'UNKNOWN';
}
```

---

### 4. `ws_ingest_validate_record_shape()` — assist-org required field checks

The allowed-key loop runs against `$record` top-level keys (the six group names) — that check stays unchanged and is correct. Only the required-field checks read from the flattened array:

```php
// Before:
if ( $record_type === 'assist-org' ) {
    if ( trim( (string) ( $record['official_homepage_url'] ?? '' ) ) === '' ) {
        $warnings[] = ...
    }
    if ( trim( (string) ( $record['general_description'] ?? '' ) ) === '' ) {
        $warnings[] = ...
    }
    if ( isset( $record['phones'] ) && ! is_array( $record['phones'] ) ) {
        $warnings[] = ...
    }
    if ( isset( $record['emails'] ) && ! is_array( $record['emails'] ) ) {
        $warnings[] = ...
    }
}

// After:
if ( $record_type === 'assist-org' ) {
    $flat = ws_ingest_flatten_assist_org_record( $record );
    if ( trim( (string) ( $flat['official_homepage_url'] ?? '' ) ) === '' ) {
        $warnings[] = "$sid: missing required official_homepage_url in record[$index] (non-blocking; requires human review).";
    }
    if ( trim( (string) ( $flat['general_description'] ?? '' ) ) === '' ) {
        $warnings[] = "$sid: missing required general_description in record[$index] (non-blocking; requires human review).";
    }
    if ( isset( $flat['phones'] ) && ! is_array( $flat['phones'] ) ) {
        $warnings[] = "$sid: phones should be an array of {type,number} objects.";
    }
    if ( isset( $flat['emails'] ) && ! is_array( $flat['emails'] ) ) {
        $warnings[] = "$sid: emails should be an array of {type,address} objects.";
    }
}
```

---

### 5. `ws_ingest_process_assist_org_record()` — flatten at top

Immediately after the opening `$result` array assignment and before any field reads, add:

```php
// Flatten nested schema groups into a working array.
// The assist-org schema groups fields under identity, scope_of_service,
// contact, eligibility, security, and review for consistency with other
// record types. The field map loop expects flat keys.
$flat = ws_ingest_flatten_assist_org_record( $record );
```

Then replace every read of `$record['key']` in the processor body with `$flat['key']`, with these specific exceptions that must continue reading from `$record` directly:

- `$record['_reconciled_notes']` — autostrip reads from top level
- The `ws_ingest_build_assist_org_seed_append()` call — pass `$flat` not `$record` since seed fields live inside the groups
- The `ws_ingest_build_assist_org_internal_id()` call — pass `$flat` not `$record`

---

### 6. `ws_ingest_assist_org_field_map_v2()` — field map corrections

Apply these changes to the existing field map:

```php
// Change: cost_model → cost_models (plural, multi-select)
// Before:
'cost_model'                => [ 'ws_aorg_cost_model',             'tax', 'ws_aorg_cost_model'    ],
// After:
'cost_models'               => [ 'ws_aorg_cost_model',             'tax', 'ws_aorg_cost_model'    ],
```

Add the following missing entries in the appropriate sections:

```php
// ── Core content additions ────────────────────────────────────────────
'legitimacy_url'            => [ 'ws_aorg_legitimacy_url',              'url'      ],
'income_eligibility_details'=> [ 'ws_aorg_income_limit_notes',          'textarea' ],
'secure_contact_tool_other' => [ 'ws_aorg_secure_contact_tool_other',   'text'     ],
'protected_class_details'   => [ 'ws_aorg_protected_class_details',     'textarea' ],

// ── Taxonomy additions ────────────────────────────────────────────────
'protected_classes'         => [ 'ws_aorg_protected_classes',      'tax', 'ws_protected_class'    ],
```

Also update the seed block — `jurisdiction_exceptions` is already present as seed; verify `case_stage_details` is also present as seed:

```php
'nationwide_example'        => [ null, 'seed' ],
'case_stage_details'        => [ null, 'seed' ],
'jurisdiction_exceptions'   => [ null, 'seed' ],
'_review_notes'             => [ null, 'seed' ],
```

Remove `source_url` from the field map entirely — it is no longer in the schema.

---

### 7. `ws_ingest_build_assist_org_internal_id()` and `ws_ingest_build_assist_org_seed_append()`

Both functions currently receive `$record` and read flat keys from it. After this pass they will receive `$flat` from the processor. Update the call sites in `ws_ingest_process_assist_org_record()` to pass `$flat`. The function signatures themselves do not change.

---

### Do not touch

- Statute, common-law, citation, interpretation processors — unchanged
- `ws_ingest_allowed_record_keys()` for non-assist-org record types — unchanged
- `ws_ingest_normalize_phone_rows()` and `ws_ingest_normalize_email_rows()` — unchanged
- The admin UI rendering functions at the bottom of the file — unchanged
- Version handlers — never modified after release