## Assist-Org Ingest Nested Schema Migration

### Context
The assist-org JSON schema has been reorganized from flat top-level keys into six nested groups (`identity`, `scope_of_service`, `contact`, `eligibility`, `security`, `review`) to be consistent with how statute, common-law, citation, and interpretation records are already structured. The ingest tool currently expects flat keys on assist-org records. This pass updates the ingest tool to handle the nested structure.

No changes to any other files. No changes to the statute, common-law, citation, or interpretation processors.

---

### `tool-ingest.php`

**Version bump + changelog entry:**
```
3.16.x  Assist-org ingest migrated to nested schema structure matching
        statute/common-law/citation/interpretation consistency:
        - ws_ingest_allowed_record_keys() updated: flat key list replaced
          with six top-level group names
        - ws_ingest_flatten_assist_org_record() helper added
        - ws_ingest_validate_record_shape() updated to flatten before
          checking assist-org required fields
        - ws_ingest_get_record_identifier() updated to read from
          identity group
        - ws_ingest_process_assist_org_record() updated to flatten
          before field map loop
```

---

**`ws_ingest_allowed_record_keys()` — assist-org branch:**

Replace the existing flat key list with the six group names:

```php
if ( $record_type === 'assist-org' ) {
    return [
        'identity',
        'scope_of_service',
        'contact',
        'eligibility',
        'security',
        'review',
        '_reconciled_notes',  // NotebookLM meta — autostripped before processing
    ];
}
```

---

**New helper `ws_ingest_flatten_assist_org_record()`:**

Add immediately before `ws_ingest_validate_record_shape()`:

```php
/**
 * Flattens a nested assist-org record into a single working array.
 *
 * The assist-org JSON schema groups fields under six top-level keys:
 * identity, scope_of_service, contact, eligibility, security, review.
 * The field map loop and validators work against flat keys, so this
 * helper merges all groups into one array before processing.
 *
 * Later groups win on key collision — review._review_notes will not
 * collide with anything, but the merge order is documented here for
 * future reference.
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

**`ws_ingest_get_record_identifier()` — assist-org branch:**

```php
// Before:
if ( $record_type === 'assist-org' ) {
    $internal_id = trim( (string) ( $record['internal_id'] ?? '' ) );
    if ( $internal_id !== '' ) {
        return $internal_id;
    }
    $org_name = trim( (string) ( $record['official_name'] ?? '' ) );
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

**`ws_ingest_validate_record_shape()` — assist-org required field checks:**

The allowed-key loop runs against `$record` top-level keys (the group names) — that stays unchanged and is correct. Only the required-field checks need to read from the flattened array:

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

Note: the `$id_key = 'official_name'` assignment for assist-org and the `$sid` derivation line above remain unchanged — `ws_ingest_get_record_identifier()` already handles the flatten internally after this pass.

---

**`ws_ingest_process_assist_org_record()` — flatten before field map loop:**

At the top of the function, immediately after the `$org_name` and `$record_type` assignments and before any field reads, add:

```php
// Flatten nested schema groups into a working array.
// The assist-org schema groups fields under identity, scope_of_service,
// contact, eligibility, security, and review for consistency with other
// record types. The field map loop expects flat keys.
$flat = ws_ingest_flatten_assist_org_record( $record );
```

Then replace every instance of `$record['key']` in the processor body with `$flat['key']`, with these specific exceptions that must continue to read from `$record` directly:

- `$record['_reconciled_notes']` — autostrip reads from top level
- Any loop over `array_keys( $record )` for unknown-key detection — already handled in validator, not processor
- The `wp_insert_post()` call that uses `$org_name` — `$org_name` is already assigned from `ws_ingest_get_record_identifier()` before the flatten

The seed accumulator block (`nationwide_example`, `case_stage_details`, `_review_notes`, `jurisdiction_exceptions`) reads via `$flat` since those fields now live inside `scope_of_service` and `review` groups.

---

### Do not touch
- Statute, common-law, citation, interpretation processors — unchanged
- `ws_ingest_assist_org_field_map_v2()` — field map keys are flat and remain flat; the flatten step is what bridges the nested input to the flat map
- `ws_ingest_detect_record_type()` — record type detection reads `meta`, not `records[]`
- NotebookLM ruleset — schema change is prompt/ingest side only; ruleset update is a separate pass