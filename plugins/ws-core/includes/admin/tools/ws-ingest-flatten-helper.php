<?php
/**
 * Flattens a nested assist-org record to a flat array with dot-separated keys.
 * Only flattens the six top-level groups; does not recurse arbitrarily.
 * Example: [ 'contact.phones' => ... ]
 *
 * @param array $record The nested assist-org record.
 * @return array The flattened record.
 */
function ws_ingest_flatten_assist_org_record(array $record): array {
    if (!is_array($record)) return $record;
    $groups = [
        'contact',
        'eligibility',
        'services',
        'secure_contact',
        'meta',
        'other',
    ];
    $flat = [];
    foreach ($record as $key => $val) {
        if (in_array($key, $groups, true) && is_array($val)) {
            foreach ($val as $subkey => $subval) {
                $flat[$subkey] = $subval;
            }
        } else {
            $flat[$key] = $val;
        }
    }
    return $flat;
}