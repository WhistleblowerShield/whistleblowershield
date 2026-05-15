<?php
/**
 * Assist-org JSON ingest.
 *
 * Reads normalized JSON files from tools/normalized/ and creates or updates
 * WP posts of type ws-assist-org with ACF meta fields and taxonomy terms.
 *
 * Deduplication key: ws_aorg_official_homepage_url (URL is stable; names drift).
 *
 * Run from WP root:
 *   wp eval-file wp-content/plugins/ws-core/... [adjust path]
 *   -- OR --
 *   php -r "define('ABSPATH', '/path/to/wp/'); ... require_once ABSPATH.'wp-load.php';"
 *
 * The script is self-contained. It does NOT require the matrix-assist-orgs helpers
 * to be loaded; it defines its own thin wrappers that fail loudly with expletives.
 */

declare(strict_types=1);

// ── Bootstrap guard ───────────────────────────────────────────────────────────

if (!defined('ABSPATH')) {
    fwrite(STDERR, "ERROR: Run this script inside WP context (wp eval-file) or bootstrap WP first.\n");
    exit(1);
}

// ── Configuration ─────────────────────────────────────────────────────────────

const WS_AORG_POST_TYPE  = 'ws-assist-org';
const WS_AORG_PREFIX     = 'ws_aorg_';
const WS_AORG_INGEST_SRC = 'ingest-assist-org.php';

const NORMALIZED_DIR = __DIR__ . '/normalized/';

// ── Error handler ─────────────────────────────────────────────────────────────

function aorg_die(string $msg): never {
    $full = "[INGEST FUQUP] " . $msg . " — ABORTING.";
    if (defined('WP_DEBUG') && WP_DEBUG) {
        error_log($full);
    }
    fwrite(STDERR, $full . "\n");
    exit(1);
}

function aorg_warn(string $msg): void {
    $full = "[INGEST WARN] " . $msg;
    error_log($full);
    echo $full . "\n";
}

// ── Scalar writers ────────────────────────────────────────────────────────────

function aorg_write_meta(int $post_id, string $base_key, mixed $value): void {
    if ($value === '' || $value === null) return;
    update_post_meta($post_id, WS_AORG_PREFIX . $base_key, $value);
}

// ── Repeater writer ───────────────────────────────────────────────────────────

function aorg_write_repeater(int $post_id, string $base_key, array $rows, array $subfield_map): void {
    $full_key = WS_AORG_PREFIX . $base_key;
    update_post_meta($post_id, $full_key, count($rows));
    foreach ($rows as $i => $row) {
        if (!is_array($row)) {
            aorg_die("Repeater '{$full_key}' row {$i} is not an array on post {$post_id}.");
        }
        foreach ($subfield_map as $src => $acf) {
            if (!array_key_exists($src, $row)) {
                aorg_die("Repeater '{$full_key}' row {$i} missing key '{$src}' on post {$post_id}.");
            }
            update_post_meta($post_id, "{$full_key}_{$i}_{$acf}", $row[$src]);
        }
    }
}

// ── Taxonomy writer ───────────────────────────────────────────────────────────

function aorg_assign_terms(int $post_id, array $slugs, string $taxonomy): void {
    if (empty($slugs)) return;

    // Filter editorial sentinels — they must not appear in seeded data.
    $clean = array_values(array_filter($slugs, fn($s) => !in_array($s, ['has-details', 'see-details', 'see-context'], true)));
    $dropped = array_diff($slugs, $clean);
    if (!empty($dropped)) {
        aorg_warn("Editorial sentinel(s) stripped from '{$taxonomy}' on post {$post_id}: " . implode(', ', $dropped));
    }

    if (empty($clean)) return;

    $result = wp_set_object_terms($post_id, $clean, $taxonomy);
    if (is_wp_error($result)) {
        aorg_die("Taxonomy shitstorm assigning '{$taxonomy}' on post {$post_id}: " . $result->get_error_message());
    }
}

// ── Deduplication ─────────────────────────────────────────────────────────────

function aorg_find_existing(string $homepage_url): ?int {
    $posts = get_posts([
        'post_type'      => WS_AORG_POST_TYPE,
        'posts_per_page' => 1,
        'post_status'    => 'any',
        'meta_key'       => 'ws_aorg_official_homepage_url',
        'meta_value'     => $homepage_url,
        'fields'         => 'ids',
    ]);
    return !empty($posts) ? (int) $posts[0] : null;
}

// ── Jurisdiction: US ──────────────────────────────────────────────────────────

function aorg_get_us_term_id(): int {
    static $id = null;
    if ($id !== null) return $id;
    $term = get_term_by('slug', 'us', 'ws_jurisdiction');
    if (!$term || is_wp_error($term)) {
        aorg_die("Couldn't find 'us' term in ws_jurisdiction — taxonomy not seeded. Fix that shitshow first.");
    }
    $id = (int) $term->term_id;
    return $id;
}

// ── Post content builder ──────────────────────────────────────────────────────

function aorg_build_post_content(array $record): string {
    $seed = $record['_post_content_seed'] ?? [];
    $parts = [];

    if (!empty($record['general_description'])) {
        $parts[] = $record['general_description'];
    }

    if (!empty($seed['nationwide_example'])) {
        $parts[] = '<blockquote>' . esc_html($seed['nationwide_example']) . '</blockquote>';
    }

    if (!empty($seed['additional_services'])) {
        $parts[] = '<p><strong>Additional services:</strong> ' . esc_html($seed['additional_services']) . '</p>';
    }

    if (!empty($seed['eligibility_notes'])) {
        $parts[] = '<p><strong>Eligibility:</strong> ' . esc_html($seed['eligibility_notes']) . '</p>';
    }

    return implode("\n\n", array_filter($parts));
}

// ── Core record ingester ──────────────────────────────────────────────────────

function aorg_ingest_record(array $record, int $us_term_id): void {
    $official_name = trim((string)($record['official_name'] ?? ''));
    $homepage_url  = trim((string)($record['official_homepage_url'] ?? ''));

    if ($official_name === '' || $homepage_url === '') {
        aorg_die("Record missing official_name or official_homepage_url — cannot ingest this craptastic entry.");
    }

    $slug        = sanitize_title($official_name);
    $post_content = aorg_build_post_content($record);
    $existing_id  = aorg_find_existing($homepage_url);

    $post_data = [
        'post_title'   => $official_name,
        'post_name'    => $slug,
        'post_type'    => WS_AORG_POST_TYPE,
        'post_status'  => 'draft',
        'post_content' => $post_content,
    ];

    if ($existing_id) {
        $post_data['ID'] = $existing_id;
        $post_id = wp_update_post($post_data, true);
        $action = 'UPDATED';
    } else {
        $post_id = wp_insert_post($post_data, true);
        $action = 'CREATED';
    }

    if (is_wp_error($post_id) || !$post_id) {
        $msg = is_wp_error($post_id) ? $post_id->get_error_message() : 'empty post ID';
        aorg_die("Post shitfail for '{$official_name}': {$msg}");
    }

    // ── Scalar ACF meta ───────────────────────────────────────────────────────

    $scalar_fields = [
        'official_name', 'common_name', 'official_homepage_url', 'homepage_verified_date',
        'organization_model', 'intake_url', 'contact_url', 'mailing_address',
        'is_nationwide', 'whistleblower_scope', 'whistleblower_scope_details',
        'language_details', 'protected_class_details', 'case_stage_details',
        'disclosure_target_details', 'service_limits', 'public_warning_notes',
        'editor_confidence_notes', 'whistleblower_scope_details',
        // Select fields
        'organization_status', 'public_directory_status', 'editor_confidence',
        'whistleblower_fit', 'service_depth', 'intake_commitment_class',
        'intake_status', 'expected_response_time', 'eligibility_status',
        'income_screening', 'membership_requirement', 'anonymous_pre_consult_status',
        'confidentiality_claimed', 'secure_channel_status', 'has_attorneys',
        'attorney_role', 'legal_representation_status', 'attorney_client_relationship_status',
    ];

    foreach ($scalar_fields as $field) {
        if (isset($record[$field]) && $record[$field] !== '') {
            aorg_write_meta($post_id, $field, $record[$field]);
        }
    }

    // secure_contact_tools is a multi-select array stored as serialised meta
    if (!empty($record['secure_contact_tools'])) {
        update_post_meta($post_id, WS_AORG_PREFIX . 'secure_contact_tools', $record['secure_contact_tools']);
    }

    // ── Repeaters ─────────────────────────────────────────────────────────────

    if (!empty($record['phones'])) {
        aorg_write_repeater($post_id, 'phones', $record['phones'], [
            'type'   => 'ws_aorg_phone_type',
            'number' => 'ws_aorg_phone_number',
        ]);
    }

    if (!empty($record['emails'])) {
        aorg_write_repeater($post_id, 'emails', $record['emails'], [
            'type'    => 'ws_aorg_email_type',
            'address' => 'ws_aorg_email_address',
        ]);
    }

    // ── Taxonomies ────────────────────────────────────────────────────────────

    // organization_model → ws_organization_model (single value as array)
    if (!empty($record['organization_model'])) {
        aorg_assign_terms($post_id, [$record['organization_model']], 'ws_organization_model');
    }

    $taxonomy_fields = [
        'protected_disclosures' => 'ws_protected_disclosure',
        'disclosure_targets'    => 'ws_disclosure_target',
        'case_stages'           => 'ws_case_stage',
        'process_types'         => 'ws_process_type',
        'services'              => 'ws_aorg_service',
        'employment_sectors'    => 'ws_employment_sector',
        'protected_classes'     => 'ws_protected_class',
        'cost_models'           => 'ws_aorg_cost_model',
        'languages'             => 'ws_language',
    ];

    foreach ($taxonomy_fields as $field => $taxonomy) {
        if (isset($record[$field]) && is_array($record[$field])) {
            aorg_assign_terms($post_id, $record[$field], $taxonomy);
        }
    }

    // Jurisdiction: US (all batch records are US-nationwide)
    if ((int)($record['is_nationwide'] ?? 0) === 1) {
        $jx_result = wp_set_object_terms($post_id, $us_term_id, 'ws_jurisdiction');
        if (is_wp_error($jx_result)) {
            aorg_die("Jurisdiction sh!tstorm on post {$post_id}: " . $jx_result->get_error_message());
        }
    }

    // ── Audit stamp ───────────────────────────────────────────────────────────

    update_post_meta($post_id, 'ws_matrix_source', WS_AORG_INGEST_SRC);
    update_post_meta($post_id, 'ws_aorg_ingest_date', date('Y-m-d'));

    echo "[{$action}] {$official_name} (post {$post_id})\n";
}

// ── Main ──────────────────────────────────────────────────────────────────────

if (!is_dir(NORMALIZED_DIR)) {
    aorg_die("Normalized dir not found: " . NORMALIZED_DIR . " — run normalize-assist-org.php first, dumbass.");
}

$files = glob(NORMALIZED_DIR . '*-normalized.json');
if (empty($files)) {
    aorg_die("No normalized JSON files in " . NORMALIZED_DIR . " — nothing to ingest, you absolute muppet.");
}

$us_term_id  = aorg_get_us_term_id();
$total_in    = 0;
$total_ok    = 0;

foreach ($files as $path) {
    $raw = json_decode((string) file_get_contents($path), true, 512, JSON_THROW_ON_ERROR);
    if (!isset($raw['records']) || !is_array($raw['records'])) {
        aorg_warn("Skipping {$path} — no 'records' key.");
        continue;
    }

    $batch_id = basename($path, '-normalized.json');
    echo "\n── Batch {$batch_id} (" . count($raw['records']) . " records) ─────────────────────────\n";

    foreach ($raw['records'] as $record) {
        $total_in++;
        aorg_ingest_record($record, $us_term_id);
        $total_ok++;
    }
}

echo "\n── Done ────────────────────────────────────────────────────────────────────\n";
echo "Ingested {$total_ok}/{$total_in} records.\n";
echo "All posts created as draft — editorial review required before publish.\n";
