<?php
/**
 * Assist-org batch normalizer.
 *
 * Reads US-3/8/10 research batch files (prompt-schema format), remaps every
 * prompt key to its canonical ACF base name, flattens US-3 nested structure,
 * promotes phones/emails to typed repeater rows, and emits ready-for-ingest
 * JSON files in the normalized/ sub-directory.
 *
 * Run: php normalize-assist-org.php
 */

declare(strict_types=1);

const BATCHES = [
    'US-3'  => __DIR__ . '/ready/US-3-Assist-org-rev2-a-NotebookLM.json',
    'US-8'  => __DIR__ . '/ready/US-8-Assist-org-NotebookLM.json',
    'US-10' => __DIR__ . '/ready/US-10-Assist-org-NotebookLM.json',
];

const OUT_DIR = __DIR__ . '/normalized/';

// ── Key remapping ─────────────────────────────────────────────────────────────
// Prompt key → canonical ACF base name.
// false  = drop entirely (deferred or out of scope for starting-point).
// null   = residual (collected into _post_content_seed).
const KEY_REMAP = [
    'organization_name'              => 'official_name',
    'disclosure_types'               => 'protected_disclosures',
    'languages_supported'            => 'languages',
    'languages_additional'           => 'language_details',
    'assistance_type'                => 'organization_model',
    'services_provided'              => 'services',
    'anonymous_pre_consult_possible' => 'anonymous_pre_consult_status',
    'verified_date_url'              => 'homepage_verified_date',
    'verified_url_date'              => 'homepage_verified_date',
    'disclosure_targets_details'     => 'disclosure_target_details',
    'whistleblower_note'             => 'whistleblower_scope_details',
    'income_eligibility_required'    => 'income_screening',
    'has_secure_channel'             => 'secure_channel_status',
    // Residuals
    'additional_services'            => null,
    'nationwide_example'             => null,
    'eligibility_notes'              => null,
    // Dropped — deferred from starting-point
    'homepage_url_status'            => false,
    'legitimacy_url'                 => false,
];

// Phone type slugs accepted by ws_matrix_phone_channels.
const PHONE_ALLOWED = ['hotline', 'intake', 'headquarters', 'regional', 'tty', 'fax', 'other'];

// Email type slugs accepted by ws_matrix_email_channels.
const EMAIL_ALLOWED = ['intake', 'general', 'legal', 'media', 'support', 'secure', 'other'];

// Nested group keys used by US-3 format.
const NESTED_BLOCKS = ['identity', 'scope_of_service', 'contact', 'eligibility', 'security', 'review'];

// Defaults for new starting-point fields not present in batch data.
// All set conservatively so records require editorial review before publish.
const NEW_FIELD_DEFAULTS = [
    // New starting-point selects — needs editorial review before publish
    'organization_status'                 => 'unclear',
    'public_directory_status'             => 'draft-review',
    'editor_confidence'                   => 'low',
    'whistleblower_fit'                   => 'unclear',
    'service_depth'                       => 'unclear',
    'intake_commitment_class'             => 'unclear',
    'intake_status'                       => 'unclear',
    'expected_response_time'              => 'unclear',
    'eligibility_status'                  => 'unclear',
    'membership_requirement'              => 'none',
    'confidentiality_claimed'             => 'unclear',
    'secure_contact_tools'                => [],
    'attorney_role'                       => 'unclear',
    'legal_representation_status'         => 'unclear',
    'attorney_client_relationship_status' => 'unclear',
    // Select fields not universally present across batches
    'secure_channel_status'               => 'unclear',
    'income_screening'                    => 'unclear',
    'anonymous_pre_consult_status'        => 'unclear',
    'has_attorneys'                       => 'unclear',
    // Taxonomy arrays — some batch records omit these; empty is valid
    'protected_classes'                   => [],
    'employment_sectors'                  => [],
    'services'                            => [],
    'cost_models'                         => [],
    'protected_disclosures'               => [],
    'disclosure_targets'                  => [],
    'case_stages'                         => [],
    'process_types'                       => [],
    'languages'                           => [],
];

// ── Audit log ─────────────────────────────────────────────────────────────────

$audit = [];

function alog(string $org, string $msg): void {
    global $audit;
    $audit[] = "[{$org}] {$msg}";
}

// ── Structure helpers ─────────────────────────────────────────────────────────

function is_nested(array $r): bool {
    foreach (NESTED_BLOCKS as $b) {
        if (isset($r[$b]) && is_array($r[$b])) return true;
    }
    return false;
}

function flatten_record(array $r): array {
    $flat = [];
    foreach ($r as $k => $v) {
        if (in_array($k, NESTED_BLOCKS, true) && is_array($v)) {
            foreach ($v as $fk => $fv) $flat[$fk] = $fv;
        } else {
            $flat[$k] = $v;
        }
    }
    return $flat;
}

// ── Value transforms ──────────────────────────────────────────────────────────

function map_income(string $v): string {
    return match ($v) {
        'no'  => 'not-required',
        'yes' => 'required',
        default => 'unclear',
    };
}

function map_secure_channel(string $v, string $org): string {
    if ($v === 'yes') {
        alog($org, "WARN secure_channel_status: has_secure_channel=yes cannot map to dedicated-secure-channel without tool verification; set to 'unclear'");
        return 'unclear';
    }
    return match ($v) {
        'no'    => 'none-found',
        default => 'unclear',
    };
}

function safe_phone_type(string $t): string {
    return in_array($t, PHONE_ALLOWED, true) ? $t : 'other';
}

function infer_email_type(string $address): string {
    $prefix = strtolower((string) strstr($address, '@', before_needle: true) ?: $address);
    return match (true) {
        str_starts_with($prefix, 'intake')  => 'intake',
        str_starts_with($prefix, 'info')    => 'general',
        str_starts_with($prefix, 'contact') => 'general',
        str_starts_with($prefix, 'help')    => 'general',
        str_starts_with($prefix, 'support') => 'support',
        str_starts_with($prefix, 'legal')   => 'legal',
        str_starts_with($prefix, 'media')   => 'media',
        str_starts_with($prefix, 'secure')  => 'secure',
        default                             => 'other',
    };
}

function safe_email_type(string $t, string $address): string {
    if (in_array($t, EMAIL_ALLOWED, true)) return $t;
    return infer_email_type($address);
}

function normalize_phones(mixed $v, string $org): array {
    if ($v === null || $v === '' || $v === []) return [];

    if (is_array($v) && isset($v[0]) && is_array($v[0])) {
        $out = [];
        foreach ($v as $row) {
            $number = trim((string)($row['number'] ?? $row['phone'] ?? ''));
            if ($number === '') {
                alog($org, "WARN phone row missing number — skipped");
                continue;
            }
            $out[] = [
                'type'   => safe_phone_type((string)($row['type'] ?? 'other')),
                'number' => $number,
            ];
        }
        return $out;
    }

    if (is_string($v) && $v !== '') {
        return [['type' => 'other', 'number' => $v]];
    }

    alog($org, "WARN unrecognised phones shape — stored as empty");
    return [];
}

function normalize_emails(mixed $v, string $org): array {
    if ($v === null || $v === '' || $v === []) return [];

    if (is_array($v) && isset($v[0]) && is_array($v[0])) {
        $out = [];
        foreach ($v as $row) {
            $address = trim((string)($row['address'] ?? $row['email'] ?? ''));
            if ($address === '') {
                alog($org, "WARN email row missing address — skipped");
                continue;
            }
            $out[] = [
                'type'    => safe_email_type((string)($row['type'] ?? ''), $address),
                'address' => $address,
            ];
        }
        return $out;
    }

    if (is_string($v) && $v !== '') {
        return [['type' => infer_email_type($v), 'address' => $v]];
    }

    alog($org, "WARN unrecognised emails shape — stored as empty");
    return [];
}

function normalize_case_stages(array $stages, array $out_so_far, string $org): array {
    if (!in_array('other', $stages, true)) return $stages;

    $stages = array_values(array_filter($stages, fn($s) => $s !== 'other'));

    if (!empty($out_so_far['case_stage_details'])) {
        if (!in_array('has-details', $stages, true)) $stages[] = 'has-details';
        alog($org, "case_stages: 'other' removed; has-details added (case_stage_details present)");
    } else {
        alog($org, "case_stages: 'other' removed; no case_stage_details — no replacement added");
    }

    return $stages;
}

// ── Record normalizer ─────────────────────────────────────────────────────────

function normalize_record(array $record): array {
    if (is_nested($record)) $record = flatten_record($record);

    $org      = $record['official_name'] ?? $record['organization_name'] ?? 'unknown';
    $out      = [];
    $residual = [];

    foreach ($record as $k => $v) {
        // Audit / provenance keys always pass through
        if (str_starts_with($k, '_')) {
            $out[$k] = $v;
            continue;
        }

        // Use array_key_exists — null entries in KEY_REMAP signal residual;
        // ?? would coalesce null to $k and silently pass them through.
        $target = array_key_exists($k, KEY_REMAP) ? KEY_REMAP[$k] : $k;

        if ($target === false) {
            alog($org, "DROPPED: {$k}");
            continue;
        }

        if ($target === null) {
            if ($v !== null && $v !== '' && $v !== []) {
                $residual[$k] = $v;
                alog($org, "RESIDUAL: {$k} → _post_content_seed");
            }
            continue;
        }

        // Value transforms for fields that need more than a key rename
        if ($target === 'income_screening') {
            $out[$target] = map_income((string) $v);
            alog($org, "income_screening: '{$v}' → '{$out[$target]}'");
            continue;
        }

        if ($target === 'secure_channel_status') {
            $out[$target] = map_secure_channel((string) $v, $org);
            continue;
        }

        if ($target === 'phones') {
            $out[$target] = normalize_phones($v, $org);
            continue;
        }

        if ($target === 'emails') {
            $out[$target] = normalize_emails($v, $org);
            continue;
        }

        $out[$target] = $v;
    }

    // case_stages fix after all keys are resolved
    if (isset($out['case_stages']) && is_array($out['case_stages'])) {
        $out['case_stages'] = normalize_case_stages($out['case_stages'], $out, $org);
    }

    // Ensure phones/emails are always present so the ingest never errors on missing key
    if (!isset($out['phones'])) $out['phones'] = [];
    if (!isset($out['emails'])) $out['emails'] = [];

    // Apply defaults for new starting-point fields absent from batch data
    foreach (NEW_FIELD_DEFAULTS as $field => $default) {
        if (!isset($out[$field])) {
            $out[$field] = $default;
            alog($org, "DEFAULT: {$field} = " . (is_array($default) ? '[]' : "'{$default}'"));
        }
    }

    if (!empty($residual)) {
        $out['_post_content_seed'] = $residual;
    }

    return $out;
}

// ── Main ──────────────────────────────────────────────────────────────────────

if (!is_dir(OUT_DIR) && !mkdir(OUT_DIR, 0755, true)) {
    fwrite(STDERR, "ERROR: could not create output directory: " . OUT_DIR . "\n");
    exit(1);
}

$total_records = 0;

foreach (BATCHES as $batch_id => $path) {
    if (!file_exists($path)) {
        fwrite(STDERR, "SKIP  [{$batch_id}]: file not found — {$path}\n");
        continue;
    }

    $raw = json_decode((string) file_get_contents($path), true, 512, JSON_THROW_ON_ERROR);

    if (!isset($raw['records']) || !is_array($raw['records'])) {
        fwrite(STDERR, "ERROR [{$batch_id}]: no 'records' key in {$path}\n");
        continue;
    }

    $is_nationwide_default = ($raw['meta']['nationwide_only'] ?? false) ? 1 : 0;

    $records = array_map(function (array $record) use ($is_nationwide_default): array {
        $normalized = normalize_record($record);
        if (!isset($normalized['is_nationwide'])) {
            $normalized['is_nationwide'] = $is_nationwide_default;
        }
        return $normalized;
    }, $raw['records']);
    $total_records += count($records);

    $out = [
        'meta'    => array_merge($raw['meta'] ?? [], [
            'normalized_by'   => 'normalize-assist-org.php',
            'normalized_date' => date('Y-m-d'),
            'schema_version'  => 'starting-point-refactor',
        ]),
        'records' => $records,
    ];

    if (isset($raw['integrity'])) $out['integrity'] = $raw['integrity'];

    $out_path = OUT_DIR . $batch_id . '-normalized.json';
    file_put_contents($out_path, json_encode($out, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . "\n");

    printf("WROTE [%s]: %s  (%d records)\n", $batch_id, $out_path, count($records));
}

// Audit summary
$note_count = count($audit);
echo "\n" . str_repeat('─', 72) . "\n";
echo "Audit log ({$note_count} notes, {$total_records} records total)\n";
echo str_repeat('─', 72) . "\n";
foreach ($audit as $line) echo $line . "\n";
echo str_repeat('─', 72) . "\n";
