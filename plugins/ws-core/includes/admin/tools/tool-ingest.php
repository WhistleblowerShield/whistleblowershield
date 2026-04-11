<?php
/**
 * tool-ingest.php
 *
 * WhistleblowerShield Core Plugin — Admin Tool
 *
 * PURPOSE
 * -------
 * Processes validated JSON ingest files and writes supported records to
 * the appropriate CPT (jx-statute, jx-common-law, jx-citation, jx-interpretation,
 * or ws-assist-org) with normalized field
 * mapping, taxonomy assignments, and deterministic linkage to related
 * agency/citation records.
 *
 * This tool also enforces ingest guardrails (schema/version checks,
 * integrity advisory handling, and proposal-term logging) while preserving
 * clean-state data conventions used across ws-core.
 *
 * RECORD TYPES SUPPORTED (this version)
 * --------------------------------------
 * - statute (jx-statute CPT)
 * - common-law (jx-common-law CPT)
 * - citation (jx-citation CPT)
 * - interpretation (jx-interpretation CPT)
 * - assist-org (ws-assist-org CPT)
 *
 * PIPELINE PHASES
 * ---------------
 * Phase 1 — Pre-Flight Validation
 *   IT-1: batch_completed sentinel check
 *   IT-2: record_count integrity check
 *   IT-3: with_errors advisory surface
 *   IT-4: proposed terms merge into log
 *   IT-5: Admin confirmation before Phase 2
 *
 * Phase 2 — Record Processing
 *   Create post → stamp source → map fields → assign taxonomies
 *   → create/link agency stubs → create/link citation stubs
 *   → attach statute citation ID array
 *
 * Phase 3 — Post-Run Report
 *
 * OUTPUT DIRECTORY
 * ----------------
 * Upload JSON files via the WordPress media library or FTP to a
 * staging path. This tool reads from a user-specified path.
 *
 * KEY ARCHITECTURAL RULES
 * -----------------------
 * - verification_status is always set to 'unverified' on ingest
 * - needs_review is always set to false on ingest
 * - _review_notes and _reconciled_notes are autostripped (never written)
 * - Proposed terms are logged, not inserted into the taxonomy
 * - Proposed terms in records are removed before writing
 * - source_chain is written to hidden key _ws_auto_source_chain
 * - enforcement.primary_agency is mapped to enforcement channel and used for agency matching/stubs
 * - attached_citations supports array or free-text rows with structured parsing
 * - citation dedupe uses stable hidden keys to prevent duplicate stub proliferation
 * - statute records receive ws_jx_statute_citation_ids at ingest-time when citation links exist
 * - run logs include batch count + second-level timestamp to avoid filename collisions
 * - The assistant's integrity block is advisory — ingest tool validates independently
 * - Version handlers are never modified after release
 *
 * @package    WhistleblowerShield
 * @since      3.14.0
 * @version    3.15.1
 * @author     Whistleblower Shield
 * @link       https://whistleblowershield.org
 * @copyright  Copyright (c) Whistleblower Shield
 *
 * VERSION
 * -------
 * 3.16.0  Assist-org field map corrections and additions:
 *         - organization_name now maps to both post_title (existing) and
 *           ws_aorg_official_name (new dedicated meta field)
 *         - assistance_types key renamed to assistance_type (single-value)
 *         - coverage_exceptions key renamed to jurisdiction_exceptions
 *           and remapped to ws_aorg_jurisdiction_exceptions (was incorrectly
 *           mapped to ws_aorg_eligibility_notes)
 *         - ws_aorg_official_name added to allowed keys and field map
 *         - jurisdiction_exceptions added to allowed keys and field map
 *         - assistance_type (singular) replaces assistance_types in allowed keys
 *         - Slug abbreviation pass expanded: global→intl, coalition→coal,
 *           institute/institution→inst, education/educational→edu,
 *           employment→emp, employee/employees→emp, protection/s→prot,
 *           advocacy→adv, alliance→all, committee→cmte, council→cncl,
 *           bureau→bur, office→ofc, rights→rts, public→pub, policy→pol,
 *           research→rsch, whistleblowing→wb; small words stripped:
 *           and, the, for, of, in, at, to, a, an
 * 3.15.1  Added assist-org ingest support:
 *         - record_type detection and schema validation for assist-org batches
 *         - dedicated assist-org field map and processor for ws-assist-org CPT
 *         - taxonomy assignment support for ws_aorg_* / ws_languages / ws_process_type / ws_case_stage
 *         - deterministic dedupe via internal ID + website URL + ingest record key
 * 3.15.0  Ingest reliability + UX polish:
 *         - detailed run logs moved to logs/ws-ingest/ingested/
 *         - folder dry-run shows expandable per-file preflight warnings
 *         - agency stub normalization uses jurisdiction ID prefixing rule
 *         - agency code normalization includes AG/ST/COMMR abbreviations
 *         - agency stub create-or-reuse path added to prevent duplicate stubs
 *         - in-pass agency label dedupe by normalized code key
 * 3.14.3  Citation dedupe + common-law relationship linkage:
 *         - citation stub dedupe keyed by normalized case name + parent record
 *         - optional parent_common_law_id linkage for citation and interpretation records
 *         - common-law interpretation back-link support via ws_cl_interpretation_ids
 * 3.14.2  Citation + interpretation ingest and neutral logging:
 *         - dedicated jx-citation field map and processor
 *         - dedicated jx-interpretation field map and processor
 *         - parent statute linkage via parent_statute_id lookup
 *         - neutral record_id logging across record types
 * 3.14.1  Common-law ingest support:
 *         - batch record-type detection when meta.record_type is absent
 *         - dedicated jx-common-law field map and processor
 *         - doctrine-aware preflight and run logging identifiers
 *         - common-law agency linkage to ws_cl_related_agencies
 * 3.14.0  Statute ingest hardening and linkage expansion (json_format_version 2.0):
 *         - local/federal agency routing + fail-open agency stubs
 *         - citation stub creation/linking from citations.attached_citations
 *         - structured citation parsing (CASE || IMPACT || URL || SOURCE || QUALITY)
 *         - citation impact seed mapped to citation summary field
 *         - statute-side citation ID array attachment at create-time
 *         - collision-safe run-log naming (batch count + second precision)
 */

defined( 'ABSPATH' ) || exit;

// ── Constants ─────────────────────────────────────────────────────────────────

define( 'WS_INGEST_VERSION',       '3.16.0' );
define( 'WS_INGEST_SCHEMA_VERSION', '2.0' );
define( 'WS_PROPOSED_TERMS_LOG',   WP_CONTENT_DIR . '/logs/ws-ingest/proposed-terms-log.json' );
define( 'WS_INGEST_LOG_DIR',       WP_CONTENT_DIR . '/logs/ws-ingest/' );
define( 'WS_INGEST_RUN_LOG_DIR',   WP_CONTENT_DIR . '/logs/ws-ingest/ingested/' );
define( 'WS_INGEST_INBOX_DIR',     WP_CONTENT_DIR . '/logs/ws-ingest/inbox/' );
define( 'WS_INGEST_ARCHIVE_DIR',   WP_CONTENT_DIR . '/logs/ws-ingest/archive/' );
define( 'WS_INGEST_CONFIRM_TTL',   30 * MINUTE_IN_SECONDS );


// ── Admin menu registration ───────────────────────────────────────────────────

add_action( 'admin_menu', 'ws_register_ingest_tool_page' );

function ws_register_ingest_tool_page() {
    add_submenu_page(
        'tools.php',
        'WS Ingest Tool',
        'WS Ingest Tool',
        'manage_options',
        'ws-ingest-tool',
        'ws_render_ingest_tool_page'
    );
}


// ── Admin notice flags (ingest tool page) ──────────────────────────────────

/**
 * Queues an admin notice for the ingest tool screen.
 *
 * @param string $message Notice text.
 * @param string $type    Notice class suffix: error|warning|success|info.
 * @return void
 */
function ws_ingest_queue_admin_notice( string $message, string $type = 'warning' ): void {
    global $ws_ingest_admin_notices;

    if ( ! isset( $ws_ingest_admin_notices ) || ! is_array( $ws_ingest_admin_notices ) ) {
        $ws_ingest_admin_notices = [];
    }

    $allowed = [ 'error', 'warning', 'success', 'info' ];
    if ( ! in_array( $type, $allowed, true ) ) {
        $type = 'warning';
    }

    $ws_ingest_admin_notices[] = [
        'message' => $message,
        'type'    => $type,
    ];
}

add_action( 'admin_notices', function() {
    if ( ! is_admin() || ! current_user_can( 'manage_options' ) ) {
        return;
    }

    $screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;
    if ( ! $screen || $screen->id !== 'tools_page_ws-ingest-tool' ) {
        return;
    }

    global $ws_ingest_admin_notices;
    if ( empty( $ws_ingest_admin_notices ) || ! is_array( $ws_ingest_admin_notices ) ) {
        return;
    }

    foreach ( $ws_ingest_admin_notices as $notice ) {
        $message = (string) ( $notice['message'] ?? '' );
        $type    = (string) ( $notice['type'] ?? 'warning' );
        if ( $message === '' ) {
            continue;
        }
        echo '<div class="notice notice-' . esc_attr( $type ) . '"><p>' . esc_html( $message ) . '</p></div>';
    }

    $ws_ingest_admin_notices = [];
} );


// ── Log directory bootstrap ───────────────────────────────────────────────────

function ws_ingest_bootstrap_log_dir(): void {
    if ( ! is_dir( WS_INGEST_LOG_DIR ) ) {
        if ( ! wp_mkdir_p( WS_INGEST_LOG_DIR ) ) {
            ws_ingest_queue_admin_notice( 'WS Ingest Tool: failed to create log directory at ' . WS_INGEST_LOG_DIR . '. Please check filesystem permissions.', 'warning' );
            error_log( '[ws-core] ws_ingest_bootstrap_log_dir(): failed to create log dir ' . WS_INGEST_LOG_DIR );
            return;
        }
        file_put_contents( WS_INGEST_LOG_DIR . '.htaccess', "Deny from all\n", LOCK_EX );
    }
    if ( ! is_dir( WS_INGEST_RUN_LOG_DIR ) ) {
        if ( ! wp_mkdir_p( WS_INGEST_RUN_LOG_DIR ) ) {
            $msg = 'ws_ingest_bootstrap_log_dir(): failed to create run-log dir ' . WS_INGEST_RUN_LOG_DIR;
            ws_ingest_queue_admin_notice( 'WS Ingest Tool: failed to create run-log directory. Check filesystem permissions.', 'warning' );
            ws_ingest_log_preflight_failure( 'bootstrap', [ $msg ] );
            return;
        }
        file_put_contents( trailingslashit( WS_INGEST_RUN_LOG_DIR ) . '.htaccess', "Deny from all\n", LOCK_EX );
    }
    if ( ! is_dir( WS_INGEST_INBOX_DIR ) ) {
        if ( ! wp_mkdir_p( WS_INGEST_INBOX_DIR ) ) {
            $msg = 'ws_ingest_bootstrap_log_dir(): failed to create inbox dir ' . WS_INGEST_INBOX_DIR;
            ws_ingest_queue_admin_notice( 'WS Ingest Tool: failed to create ingest inbox directory. Check filesystem permissions.', 'warning' );
            ws_ingest_log_preflight_failure( 'bootstrap', [ $msg ] );
            return;
        }
        file_put_contents( trailingslashit( WS_INGEST_INBOX_DIR ) . '.htaccess', "Deny from all\n", LOCK_EX );
    }
    if ( ! is_dir( WS_INGEST_ARCHIVE_DIR ) ) {
        if ( ! wp_mkdir_p( WS_INGEST_ARCHIVE_DIR ) ) {
            $msg = 'ws_ingest_bootstrap_log_dir(): failed to create archive dir ' . WS_INGEST_ARCHIVE_DIR;
            ws_ingest_queue_admin_notice( 'WS Ingest Tool: failed to create ingest archive directory. Check filesystem permissions.', 'warning' );
            ws_ingest_log_preflight_failure( 'bootstrap', [ $msg ] );
            return;
        }
        file_put_contents( trailingslashit( WS_INGEST_ARCHIVE_DIR ) . '.htaccess', "Deny from all\n", LOCK_EX );
    }
    if ( ! file_exists( WS_PROPOSED_TERMS_LOG ) ) {
        file_put_contents( WS_PROPOSED_TERMS_LOG, json_encode(
            [ 'proposed_terms' => [] ],
            JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES
        ), LOCK_EX );
    }
}

function ws_ingest_get_inbox_files(): array {
    ws_ingest_bootstrap_log_dir();

    $files = glob( trailingslashit( WS_INGEST_INBOX_DIR ) . '*.json' );
    if ( ! is_array( $files ) ) {
        return [];
    }

    $files = array_values( array_filter( $files, 'is_file' ) );
    sort( $files, SORT_NATURAL | SORT_FLAG_CASE );
    return $files;
}

function ws_ingest_decode_json_payload( string $raw ): array {
    $data = json_decode( $raw, true );
    if ( json_last_error() !== JSON_ERROR_NONE ) {
        return [
            'ok'          => false,
            'data'        => null,
            'json'        => $raw,
            'error'       => json_last_error_msg(),
        ];
    }

    return [
        'ok'          => true,
        'data'        => $data,
        'json'        => $raw,
        'error'       => '',
    ];
}

function ws_ingest_archive_json_file( string $source_path, string $filename, array $data ): array {
    ws_ingest_bootstrap_log_dir();

    $stamp = gmdate( 'Ymd-His' );
    $target_path = trailingslashit( WS_INGEST_ARCHIVE_DIR ) . $stamp . '-' . basename( $filename );
    $encoded = wp_json_encode( $data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE );

    if ( ! is_string( $encoded ) || file_put_contents( $target_path, $encoded ) === false ) {
        return [ 'ok' => false, 'path' => '', 'error' => 'Failed to write archive JSON file.' ];
    }

    if ( file_exists( $source_path ) && ! is_writable( $source_path ) ) {
        return [ 'ok' => false, 'path' => $target_path, 'error' => 'Archived copy written, but source file is not writable for deletion.' ];
    }
    $deleted = false;
    if ( function_exists( 'wp_delete_file' ) ) {
        $deleted = (bool) wp_delete_file( $source_path );
    } else {
        $deleted = unlink( $source_path );
    }
    if ( ! $deleted ) {
        return [ 'ok' => false, 'path' => $target_path, 'error' => 'Archived copy written, but failed to delete source file from inbox.' ];
    }

    return [ 'ok' => true, 'path' => $target_path, 'error' => '' ];
}

function ws_ingest_archive_raw_file( string $source_path, string $filename ): array {
    ws_ingest_bootstrap_log_dir();

    $stamp = gmdate( 'Ymd-His' );
    $target_path = trailingslashit( WS_INGEST_ARCHIVE_DIR ) . $stamp . '-' . basename( $filename );

    if ( ! file_exists( $source_path ) ) {
        return [ 'ok' => false, 'path' => '', 'error' => 'Source file no longer exists in inbox.' ];
    }
    if ( rename( $source_path, $target_path ) ) {
        return [ 'ok' => true, 'path' => $target_path, 'error' => '' ];
    }

    return [ 'ok' => false, 'path' => '', 'error' => 'Failed to move raw file to archive.' ];
}


// ── Confirmation payload helpers ─────────────────────────────────────────────

/**
 * Stores raw ingest JSON for confirmation step and returns the token key.
 */
function ws_ingest_store_confirm_payload( string $json, string $filename ): string {
    $token = strtolower( wp_generate_password( 20, false, false ) );
    $key   = 'ws_ingest_confirm_' . $token;

    set_transient( $key, [
        'user_id'  => get_current_user_id(),
        'json'     => $json,
        'filename' => $filename,
        'created'  => time(),
    ], WS_INGEST_CONFIRM_TTL );

    return $token;
}

/**
 * Loads a previously stored confirmation payload by token.
 */
function ws_ingest_load_confirm_payload( string $token ): ?array {
    $safe = preg_replace( '/[^a-z0-9]/', '', strtolower( $token ) );
    if ( empty( $safe ) ) {
        return null;
    }

    $data = get_transient( 'ws_ingest_confirm_' . $safe );
    if ( ! is_array( $data ) ) {
        return null;
    }

    if ( (int) ( $data['user_id'] ?? 0 ) !== get_current_user_id() ) {
        return null;
    }

    return $data;
}

/**
 * Deletes a stored confirmation payload token.
 */
function ws_ingest_delete_confirm_payload( string $token ): void {
    $safe = preg_replace( '/[^a-z0-9]/', '', strtolower( $token ) );
    if ( ! empty( $safe ) ) {
        delete_transient( 'ws_ingest_confirm_' . $safe );
    }
}


// ── Proposed terms log ────────────────────────────────────────────────────────

function ws_ingest_load_proposed_terms_log(): array {
    if ( ! file_exists( WS_PROPOSED_TERMS_LOG ) ) {
        return [ 'proposed_terms' => [] ];
    }
    $raw = file_get_contents( WS_PROPOSED_TERMS_LOG );
    $log = json_decode( $raw, true );
    return is_array( $log ) ? $log : [ 'proposed_terms' => [] ];
}

function ws_ingest_save_proposed_terms_log( array $log ): bool {
    return file_put_contents(
        WS_PROPOSED_TERMS_LOG,
        json_encode( $log, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE )
    ) !== false;
}

/**
 * Merges new_terms_proposed from a batch into the persistent log.
 * Deduplicates by term_id — appends seen_in values for existing entries.
 *
 * @return array [ 'merged' => int, 'new' => int ]
 */
function ws_ingest_merge_proposed_terms( array &$log, array $new_terms ): array {
    $counts = [ 'merged' => 0, 'new' => 0 ];

    foreach ( $new_terms as $proposal ) {
        $term_id = $proposal['term_id'] ?? '';
        if ( ! $term_id ) continue;

        $found = false;
        foreach ( $log['proposed_terms'] as &$existing ) {
            if ( $existing['term_id'] === $term_id ) {
                // Merge seen_in
                $new_seen = array_diff(
                    $proposal['seen_in'] ?? [],
                    $existing['seen_in'] ?? []
                );
                $existing['seen_in'] = array_values(
                    array_merge( $existing['seen_in'] ?? [], $new_seen )
                );
                $existing['count'] = count( $existing['seen_in'] );
                $counts['merged']++;
                $found = true;
                break;
            }
        }
        unset( $existing );

        if ( ! $found ) {
            $log['proposed_terms'][] = [
                'taxonomy'    => $proposal['taxonomy']   ?? '',
                'term_id'     => $term_id,
                'term_label'  => $proposal['term_label'] ?? '',
                'notes'       => $proposal['notes']      ?? '',
                'seen_in'     => $proposal['seen_in']    ?? [],
                'count'       => count( $proposal['seen_in'] ?? [] ),
                'status'      => 'pending',
                'resolved_on' => null,
                'resolution'  => null,
            ];
            $counts['new']++;
        }
    }

    return $counts;
}

/**
 * Builds a blacklist of pending proposed term slugs.
 * Used to strip proposed terms from taxonomy arrays before writing records.
 *
 * Only blacklists terms that are BOTH pending AND not yet registered
 * in WordPress. A term that has been approved and seeded should never
 * be blacklisted even if its log status has not been updated.
 *
 * @return array [ 'term_id' => 'taxonomy_slug' ]
 */
function ws_ingest_build_blacklist( array $log ): array {
    $blacklist = [];
    foreach ( $log['proposed_terms'] as $term ) {
        $status   = $term['status']   ?? 'pending';
        $term_id  = $term['term_id']  ?? '';
        $taxonomy = $term['taxonomy'] ?? '';

        if ( $status !== 'pending' ) continue;
        if ( ! $term_id || ! $taxonomy ) continue;

        // Do not blacklist if the term is already registered in WordPress.
        // This protects against the case where a term was approved and seeded
        // but the log status was not updated before the next ingest run.
        if ( term_exists( $term_id, $taxonomy ) ) continue;

        $blacklist[ $term_id ] = $taxonomy;
    }
    return $blacklist;
}

/**
 * Recursively strips underscore-prefixed keys from associative arrays.
 *
 * Used to hard-ignore commentary/analysis keys such as _review_notes or
 * model-side diagnostics that should never participate in ingest writes.
 */
function ws_ingest_strip_prefixed_keys( $value ) {
    if ( ! is_array( $value ) ) {
        return $value;
    }

    $clean = [];
    foreach ( $value as $k => $v ) {
        if ( is_string( $k ) && strpos( $k, '_' ) === 0 && $k !== '_review_notes' ) {
            continue;
        }
        $clean[ $k ] = ws_ingest_strip_prefixed_keys( $v );
    }
    return $clean;
}

/**
 * Applies record-level defaults derived from batch meta.
 * Record-level fallback behavior has been intentionally removed.
 * Run-scope/meta values are authoritative.
 */
function ws_ingest_apply_record_defaults( array $record, array $meta, string $record_type ): array {
    return $record;
}


// ── JSON validation ───────────────────────────────────────────────────────────

/**
 * Runs all pre-flight checks on a decoded JSON array.
 *
 * @return array [ 'pass' => bool, 'errors' => string[], 'warnings' => string[] ]
 */
function ws_ingest_preflight( array $data, string $batch_filename = '' ): array {
    $result = [ 'pass' => true, 'errors' => [], 'warnings' => [] ];

    $meta      = isset( $data['meta'] ) ? ws_ingest_strip_prefixed_keys( $data['meta'] ) : null;
    $records   = isset( $data['records'] ) ? ws_ingest_strip_prefixed_keys( $data['records'] ) : null;
    $integrity = $data['integrity'] ?? null;

    // Structure check
    if ( ! is_array( $meta ) || ! is_array( $records ) || ! is_array( $integrity ) ) {
        $result['errors'][] = 'JSON missing required top-level keys: meta, records, integrity.';
        $result['pass'] = false;
        return $result;
    }

    // IT-1: batch_completed sentinel
    if ( empty( $meta['batch_completed'] ) ) {
        $result['errors'][] = 'IT-1 FAILED: batch_completed is missing or empty. Batch may be truncated.';
        $result['pass'] = false;
    }

    // Version check
    $version = $meta['json_format_version'] ?? '';
    if ( $version !== WS_INGEST_SCHEMA_VERSION ) {
        $result['errors'][] = sprintf(
            'Unsupported json_format_version "%s". This tool handles version %s only.',
            esc_html( $version ),
            WS_INGEST_SCHEMA_VERSION
        );
        $result['pass'] = false;
    }

    // IT-2: record_count integrity
    $declared = (int) ( $meta['record_count'] ?? -1 );
    $actual   = count( $records );
    if ( $declared !== $actual ) {
        $result['errors'][] = sprintf(
            'IT-2 FAILED: record_count mismatch — declared %d, found %d.',
            $declared,
            $actual
        );
        $result['pass'] = false;
    }

    // IT-3: with_errors advisory
    if ( ! empty( $integrity['with_errors'] ) ) {
        $result['warnings'][] = 'Assistant reported with_errors: true.';
        foreach ( $integrity['error_details'] ?? [] as $detail ) {
            $result['warnings'][] = '  → ' . $detail;
        }
    }

    $record_type = ws_ingest_detect_record_type( $data, $batch_filename );

    // Record identity checks
    foreach ( $records as $i => $record ) {
        if ( ! is_array( $record ) ) {
            $result['errors'][] = "record[$i]: must be an object.";
            $result['pass'] = false;
            continue;
        }
        $record = ws_ingest_strip_prefixed_keys( $record );
        $record = ws_ingest_apply_record_defaults( $record, is_array( $meta ) ? $meta : [], $record_type );

        $sid = ws_ingest_get_record_identifier( (array) $record, $record_type );
        if ( empty( $sid ) || $sid === 'UNKNOWN' ) {
            if ( $record_type === 'common-law' ) {
                $result['warnings'][] = "record[$i]: missing doctrine_id.";
            } elseif ( $record_type === 'citation' ) {
                $result['warnings'][] = "record[$i]: missing citation_id.";
            } elseif ( $record_type === 'interpretation' ) {
                $result['warnings'][] = "record[$i]: missing interpretation_id.";
            } elseif ( $record_type === 'assist-org' ) {
                $result['warnings'][] = "record[$i]: missing internal_id and organization_name.";
            } else {
                $result['warnings'][] = "record[$i]: missing statute_id.";
            }
        }

        $shape = ws_ingest_validate_record_shape( $record, $record_type, $i );
        if ( ! empty( $shape['errors'] ) ) {
            $result['errors'] = array_merge( $result['errors'], $shape['errors'] );
            $result['pass'] = false;
        }
        if ( ! empty( $shape['warnings'] ) ) {
            $result['warnings'] = array_merge( $result['warnings'], $shape['warnings'] );
        }
    }

    return $result;
}

/**
 * Detects record type for an ingest batch.
 * Prefers meta.record_type; falls back to run-scope filename token.
 */
function ws_ingest_detect_record_type( array $data, string $batch_filename = '' ): string {
    $meta_type = strtolower( trim( (string) ( $data['meta']['record_type'] ?? '' ) ) );
    if ( in_array( $meta_type, [ 'statute', 'common-law', 'citation', 'interpretation', 'assist-org' ], true ) ) {
        return $meta_type;
    }

    $name_type = ws_ingest_detect_record_type_from_filename( $batch_filename );
    if ( $name_type !== '' ) {
        return $name_type;
    }

    return 'statute';
}

/**
 * Extracts record type from run-scope batch filename.
 */
function ws_ingest_detect_record_type_from_filename( string $batch_filename ): string {
    $base = strtolower( basename( $batch_filename ) );
    if ( $base === '' ) {
        return '';
    }
    if ( strpos( $base, 'assist-org' ) !== false || strpos( $base, 'assist_org' ) !== false ) {
        return 'assist-org';
    }
    if ( strpos( $base, 'common-law' ) !== false || strpos( $base, 'common_law' ) !== false ) {
        return 'common-law';
    }
    if ( strpos( $base, 'interpretation' ) !== false ) {
        return 'interpretation';
    }
    if ( strpos( $base, 'citation' ) !== false ) {
        return 'citation';
    }
    if ( strpos( $base, 'statute' ) !== false || strpos( $base, 'statutes' ) !== false ) {
        return 'statute';
    }
    return '';
}

/**
 * Returns canonical record identifier for logs/warnings.
 */
function ws_ingest_get_record_identifier( array $record, string $record_type ): string {
    if ( $record_type === 'common-law' ) {
        return (string) ( $record['doctrine_id'] ?? 'UNKNOWN' );
    }
    if ( $record_type === 'citation' ) {
        return (string) ( $record['citation_id'] ?? 'UNKNOWN' );
    }
    if ( $record_type === 'interpretation' ) {
        return (string) ( $record['interpretation_id'] ?? 'UNKNOWN' );
    }
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
    return (string) ( $record['statute_id'] ?? 'UNKNOWN' );
}

/**
 * Returns allowed top-level keys for each record type.
 */
function ws_ingest_allowed_record_keys( string $record_type ): array {
    $common = [
        'jurisdiction_id',
        'common_name',
        'legal_basis',
        'statute_of_limitations',
        'enforcement',
        'burden_of_proof',
        'reward',
        'links',
        'citations',
        '_review_notes',
        '_reconciled_notes',
    ];

    if ( $record_type === 'common-law' ) {
        return array_merge( $common, [ 'doctrine_id', 'doctrine_name' ] );
    }

    if ( $record_type === 'citation' ) {
        return [
            'jurisdiction_id',
            'citation_id',
            'parent_statute_id',
            'parent_common_law_id',
            'case_name',
            'common_name',
            'court',
            'effective_date',
            'ruling_date',
            'specific_impact',
            'favorable',
            'disclosure_types',
            'protected_class',
            'protected_class_details',
            'disclosure_targets',
            'disclosure_targets_details',
            'adverse_action',
            'adverse_action_details',
            'remedies',
            'remedies_details',
            'process_type',
            'fee_shifting',
            'employer_defense',
            'employer_defense_details',
            'employee_standard',
            'employee_standard_details',
            '_multi_taxonomy_notes',
            'links',
            'quality',
            '_review_notes',
        ];
    }

    if ( $record_type === 'interpretation' ) {
        return [
            'jurisdiction_id',
            'interpretation_id',
            'parent_statute_id',
            'parent_common_law_id',
            'case_name',
            'common_name',
            'court',
            'effective_date',
            'ruling_date',
            'specific_impact',
            'favorable',
            'disclosure_types',
            'protected_class',
            'protected_class_details',
            'disclosure_targets',
            'disclosure_targets_details',
            'adverse_action',
            'adverse_action_details',
            'remedies',
            'remedies_details',
            'process_type',
            'fee_shifting',
            'employer_defense',
            'employer_defense_details',
            'employee_standard',
            'employee_standard_details',
            '_multi_taxonomy_notes',
            'links',
            'quality',
            '_review_notes',
        ];
    }

    if ( $record_type === 'assist-org' ) {
        return [
            'jurisdiction_id',
            'internal_id',
            'organization_name',
            'official_homepage_url',
            'general_description',
            'source_url',
            'common_name',
            'homepage_url_status',
            'verified_date_url',
            'intake_url',
            'contact_url',
            'phones',
            'emails',
            'mailing_address',
            'has_secure_channel',
            'secure_contact_url',
            'secure_contact_tool',
            'secure_contact_tool_other',
            'nationwide_example',
            'official_name',
            'disclosure_types',
            'languages_supported',
            'languages_additional',
            'assistance_type',
            'employment_sectors',
            'cost_models',
            'services_provided',
            'process_types',
            'anonymous_pre_consult_possible',
            'has_attorneys',
            'income_eligibility_required',
            'income_eligibility_details',
            'eligibility_notes',
            'case_stages',
            'case_stage_details',
            'disclosure_targets',
            'disclosure_targets_details',
            'jurisdiction_exceptions',
            'whistleblower_scope',
            'whistleblower_note',
            '_review_notes',
            '_reconciled_notes',
        ];
    }

    // statute
    return array_merge( $common, [ 'statute_id', 'official_name' ] );
}

/**
 * Strict shape validation for one record.
 * Returns [ 'errors' => string[], 'warnings' => string[] ]
 */
function ws_ingest_validate_record_shape( array $record, string $record_type, int $index ): array {
    $errors = [];
    $warnings = [];

    $id_key = 'statute_id';
    if ( $record_type === 'common-law' ) {
        $id_key = 'doctrine_id';
    } elseif ( $record_type === 'citation' ) {
        $id_key = 'citation_id';
    } elseif ( $record_type === 'interpretation' ) {
        $id_key = 'interpretation_id';
    } elseif ( $record_type === 'assist-org' ) {
        $id_key = 'organization_name';
    }

    $sid = ws_ingest_get_record_identifier( $record, $record_type );

    $allowed_keys = ws_ingest_allowed_record_keys( $record_type );
    foreach ( array_keys( $record ) as $key ) {
        if ( ! in_array( (string) $key, $allowed_keys, true ) ) {
            $errors[] = "$sid: unknown top-level key '{$key}' in record[$index].";
        }
    }

    if ( trim( (string) ( $record[ $id_key ] ?? '' ) ) === '' ) {
        if ( $record_type === 'assist-org' ) {
            $warnings[] = "$sid: missing required {$id_key} in record[$index] (non-blocking; requires human review).";
        } else {
            $errors[] = "$sid: missing required {$id_key} in record[$index].";
        }
    }

    if ( $record_type === 'assist-org' ) {
        if ( trim( (string) ( $record['official_homepage_url'] ?? '' ) ) === '' ) {
            $warnings[] = "$sid: missing required official_homepage_url in record[$index] (non-blocking; requires human review).";
        }
        if ( trim( (string) ( $record['general_description'] ?? '' ) ) === '' ) {
            $warnings[] = "$sid: missing required general_description in record[$index] (non-blocking; requires human review).";
        }
        if ( isset( $record['phones'] ) && ! is_array( $record['phones'] ) ) {
            $warnings[] = "$sid: phones should be an array of {type,number} objects.";
        }
        if ( isset( $record['emails'] ) && ! is_array( $record['emails'] ) ) {
            $warnings[] = "$sid: emails should be an array of {type,address} objects.";
        }
    }

    if ( in_array( $record_type, [ 'citation', 'interpretation' ], true ) ) {
        $parent_statute_id    = trim( (string) ( $record['parent_statute_id'] ?? '' ) );
        $parent_common_law_id = trim( (string) ( $record['parent_common_law_id'] ?? '' ) );
        if ( $parent_statute_id === '' && $parent_common_law_id === '' ) {
            $errors[] = "$sid: missing parent_statute_id and parent_common_law_id in record[$index].";
        }
    }

    if ( isset( $record['citations'] ) ) {
        if ( ! is_array( $record['citations'] ) ) {
            $errors[] = "$sid: citations must be an object when present.";
        } else {
            $attached = $record['citations']['attached_citations'] ?? null;
            if ( $attached !== null && ! is_array( $attached ) && ! is_string( $attached ) ) {
                $errors[] = "$sid: citations.attached_citations must be an array or string when present.";
            }
            if ( is_array( $attached ) ) {
                foreach ( $attached as $row_i => $row ) {
                    if ( ! is_string( $row ) ) {
                        $errors[] = "$sid: citations.attached_citations[$row_i] must be a string.";
                    }
                }
            }
        }
    }

    return [ 'errors' => $errors, 'warnings' => $warnings ];
}


// ── Field map — JSON key → ACF meta key ──────────────────────────────────────

/**
 * Returns the complete statute field map for json_format_version 2.0.
 *
 * Format:
 *   'json.path' => [ 'meta_key', 'type' ]
 *
 * Types:
 *   text    — update_post_meta() with sanitize_text_field()
 *   textarea — update_post_meta() with sanitize_textarea_field()
 *   url     — update_post_meta() with esc_url_raw()
 *   bool    — update_post_meta() with (int)(bool) cast
 *   number  — update_post_meta() with (float) cast
 *   tax     — wp_set_object_terms() with taxonomy name in [2]
 *   derived — set by ingest logic, not directly from JSON
 *   omit    — not written to DB
 */
function ws_ingest_statute_field_map_v2(): array {
    return [
        // ── Legal Basis ───────────────────────────────────────────────────
        'official_name'                          => [ 'ws_jx_statute_official_name',            'text'     ],
        'common_name'                            => [ 'ws_jx_statute_common_name',               'text'     ],
        'legal_basis.statute_citation'           => [ 'ws_jx_statute_citation',                 'text'     ],
        'legal_basis.disclosure_types'           => [ 'ws_jx_statute_disclosure_type',          'tax', 'ws_disclosure_type'     ],
        'legal_basis.protected_class'            => [ 'ws_jx_statute_protected_class',          'tax', 'ws_protected_class'     ],
        'legal_basis.protected_class_details'    => [ 'ws_jx_statute_protected_class_details',  'textarea' ],
        'legal_basis.disclosure_targets'         => [ 'ws_jx_statute_disclosure_targets',       'tax', 'ws_disclosure_targets'  ],
        'legal_basis.disclosure_targets_details' => [ 'ws_jx_statute_disclosure_targets_details','textarea'],
        'legal_basis.adverse_action_scope'       => [ 'ws_jx_statute_adverse_action_scope',     'textarea' ],

        // ── SOL ───────────────────────────────────────────────────────────
        'statute_of_limitations.limit_value'       => [ 'ws_jx_statute_sol_value',          'number'  ],
        'statute_of_limitations.limit_unit'        => [ 'ws_jx_statute_sol_unit',           'text'    ],
        'statute_of_limitations.limit_ambiguous'   => [ 'ws_jx_statute_limit_ambiguous',    'bool'    ],
        'statute_of_limitations.limit_details'     => [ 'ws_jx_statute_limit_details',      'textarea' ],
        'statute_of_limitations.trigger'           => [ 'ws_jx_statute_sol_trigger',        'text'    ],
        'statute_of_limitations.exhaustion_required' => [ 'ws_jx_statute_exhaustion_required', 'bool' ],
        'statute_of_limitations.exhaustion_details'  => [ 'ws_jx_statute_exhaustion_details',  'textarea' ],
        'statute_of_limitations.tolling_notes'     => [ 'ws_jx_statute_tolling_notes',      'textarea' ],
        // tolling_has_notes derived: set to 1 when tolling_notes is present

        // ── Enforcement ───────────────────────────────────────────────────
        'enforcement.process_type'           => [ 'ws_jx_statute_process_type',      'tax', 'ws_process_type'         ],
        'enforcement.adverse_action'         => [ 'ws_jx_statute_adverse_action',    'tax', 'ws_adverse_action_types'  ],
        'enforcement.adverse_action_details' => [ 'ws_jx_statute_adverse_action_details', 'textarea' ],
        'enforcement.fee_shifting'           => [ 'ws_jx_statute_fee_shifting',      'tax', 'ws_fee_shifting'          ],
        'enforcement.remedies'               => [ 'ws_jx_statute_remedies',          'tax', 'ws_remedies'              ],
        'enforcement.remedies_details'       => [ 'ws_jx_statute_remedies_details',  'textarea' ],
        'enforcement.primary_agency'         => [ 'ws_jx_statute_enforcement_channel', 'textarea' ],

        // ── Burden of Proof ───────────────────────────────────────────────
        'burden_of_proof.employee_standard'         => [ 'ws_jx_statute_employee_standard',         'tax', 'ws_employee_standard' ],
        'burden_of_proof.employee_standard_details' => [ 'ws_jx_statute_employee_standard_details',  'textarea' ],
        'burden_of_proof.employer_defense'          => [ 'ws_jx_statute_employer_defense',           'tax', 'ws_employer_defense'  ],
        'burden_of_proof.employer_defense_details'  => [ 'ws_jx_statute_employer_defense_details',   'textarea' ],
        'burden_of_proof.rebuttable_presumption'    => [ 'ws_jx_statute_rebuttable_presumption',     'textarea' ],
        // rebuttable_has_presumption derived: set to 1 when rebuttable_presumption present
        'burden_of_proof.burden_of_proof_details'   => [ 'ws_jx_statute_burden_of_proof_details',   'textarea' ],
        // bop_has_details derived: set to 1 when burden_of_proof_details present
        'burden_of_proof.burden_of_proof_flag'      => [ 'ws_jx_statute_bop_flag',                  'text'     ],

        // ── Reward ────────────────────────────────────────────────────────
        'reward.available'      => [ 'ws_jx_statute_reward_available', 'bool'     ],
        'reward.reward_details' => [ 'ws_jx_statute_reward_details',   'textarea' ],

        // ── Links ─────────────────────────────────────────────────────────
        'links.statute_url' => [ 'ws_jx_statute_url',    'url'  ],
        'links.is_pdf'      => [ 'ws_jx_statute_is_pdf', 'bool' ],
        'links.is_official' => [ null, 'omit' ], // advisory
        'links.url_source'  => [ null, 'omit' ], // advisory

        // ── Autostripped ──────────────────────────────────────────────────
        '_review_notes'     => [ null, 'omit' ],
        '_reconciled_notes' => [ null, 'omit' ],

        // ── Citations ─────────────────────────────────────────────────────
        'citations.attached_citations' => [ null, 'omit' ], // handled in stub pass (jx-citation)
        'citations.citation_count'     => [ null, 'omit' ], // advisory
    ];
}

/**
 * Returns the complete common-law field map for json_format_version 2.0.
 */
function ws_ingest_common_law_field_map_v2(): array {
    return [
        // ── Legal Basis ───────────────────────────────────────────────────
        'doctrine_name'                           => [ 'ws_cl_doctrine_name',                'text'     ],
        'doctrine_id'                             => [ 'ws_cl_doctrine_id',                  'text'     ],
        'common_name'                             => [ 'ws_cl_common_name',                  'text'     ],
        'links.precedent_url'                     => [ 'ws_cl_precedent_url',                'url'      ],
        'legal_basis.public_policy_sources'       => [ 'ws_cl_public_policy_sources',        'array'    ],
        'legal_basis.other_sources'               => [ 'ws_cl_other_sources',                'text'     ],
        'legal_basis.doctrine_basis'              => [ 'ws_cl_doctrine_basis',               'textarea' ],
        'legal_basis.recognition_status'          => [ 'ws_cl_recognition_status',           'textarea' ],
        'legal_basis.disclosure_types'            => [ 'ws_cl_disclosure_type',              'tax', 'ws_disclosure_type'    ],
        'legal_basis.protected_class'             => [ 'ws_cl_protected_class',              'tax', 'ws_protected_class'    ],
        'legal_basis.protected_class_details'     => [ 'ws_cl_protected_class_details',      'textarea' ],
        'legal_basis.disclosure_targets'          => [ 'ws_cl_disclosure_targets',           'tax', 'ws_disclosure_targets' ],
        'legal_basis.disclosure_targets_details'  => [ 'ws_cl_disclosure_targets_details',   'textarea' ],
        'legal_basis.adverse_action_scope'        => [ 'ws_cl_adverse_action_scope',         'textarea' ],

        // ── SOL ───────────────────────────────────────────────────────────
        'statute_of_limitations.limit_value'       => [ 'ws_cl_sol_value',               'number'   ],
        'statute_of_limitations.limit_unit'        => [ 'ws_cl_sol_unit',                'text'     ],
        'statute_of_limitations.limit_ambiguous'   => [ 'ws_cl_limit_ambiguous',         'bool'     ],
        'statute_of_limitations.limit_details'     => [ 'ws_cl_limit_details',           'textarea' ],
        'statute_of_limitations.trigger'           => [ 'ws_cl_sol_trigger',             'text'     ],
        'statute_of_limitations.exhaustion_required' => [ 'ws_cl_exhaustion_required',   'bool'     ],
        'statute_of_limitations.exhaustion_details'  => [ 'ws_cl_exhaustion_details',    'textarea' ],
        'statute_of_limitations.tolling_notes'     => [ 'ws_cl_tolling_notes',           'textarea' ],

        // ── Enforcement ───────────────────────────────────────────────────
        'enforcement.process_type'           => [ 'ws_cl_process_type',          'tax', 'ws_process_type'         ],
        'enforcement.adverse_action'         => [ 'ws_cl_adverse_action',        'tax', 'ws_adverse_action_types' ],
        'enforcement.adverse_action_details' => [ 'ws_cl_adverse_action_details', 'textarea' ],
        'enforcement.fee_shifting'           => [ 'ws_cl_fee_shifting',          'tax', 'ws_fee_shifting'         ],
        'enforcement.remedies'               => [ 'ws_cl_remedies',              'tax', 'ws_remedies'             ],
        'enforcement.remedies_details'       => [ 'ws_cl_remedies_details',      'textarea' ],
        'enforcement.primary_agency'         => [ null, 'omit' ],

        // ── Burden of Proof ───────────────────────────────────────────────
        'burden_of_proof.statutory_preclusion'         => [ 'ws_cl_statutory_preclusion',         'bool'     ],
        'burden_of_proof.statutory_preclusion_details' => [ 'ws_cl_statutory_preclusion_details', 'textarea' ],
        'burden_of_proof.employee_standard'            => [ 'ws_cl_employee_standard',             'tax', 'ws_employee_standard' ],
        'burden_of_proof.employee_standard_details'    => [ 'ws_cl_employee_standard_details',     'textarea' ],
        'burden_of_proof.employer_defense'             => [ 'ws_cl_employer_defense',              'tax', 'ws_employer_defense'  ],
        'burden_of_proof.employer_defense_details'     => [ 'ws_cl_employer_defense_details',      'textarea' ],
        'burden_of_proof.rebuttable_presumption'       => [ 'ws_cl_rebuttable_presumption',        'textarea' ],
        'burden_of_proof.burden_of_proof_details'      => [ 'ws_cl_burden_of_proof_details',       'textarea' ],
        'burden_of_proof.burden_of_proof_flag'         => [ 'ws_cl_bop_flag',                      'text'     ],

        // ── Reward ────────────────────────────────────────────────────────
        'reward.available'      => [ 'ws_cl_reward_available', 'bool'     ],
        'reward.reward_details' => [ 'ws_cl_reward_details',   'textarea' ],

        // ── Autostripped / advisory ───────────────────────────────────────
        '_review_notes'              => [ null, 'omit' ],
        '_reconciled_notes'          => [ null, 'omit' ],
        'citations.attached_citations' => [ null, 'omit' ],
        'citations.citation_count'     => [ null, 'omit' ],
    ];
}

/**
 * Returns the complete citation field map for json_format_version 2.0.
 */
function ws_ingest_citation_field_map_v2(): array {
    return [
        // ── Core content ─────────────────────────────────────────────────
        'case_name'                  => [ 'ws_jx_citation_official_name',            'text'     ],
        'common_name'                => [ 'ws_jx_citation_common_name',              'text'     ],
        'links.case_url'             => [ 'ws_jx_citation_url',                      'url'      ],
        'links.is_pdf'               => [ 'ws_jx_citation_is_pdf',                   'bool'     ],
        'specific_impact'            => [ 'ws_jx_citation_summary',                  'textarea' ],

        // ── Taxonomy classification ─────────────────────────────────────
        'disclosure_types'           => [ 'ws_jx_citation_disclosure_type',          'tax', 'ws_disclosure_type'    ],
        'protected_class'            => [ 'ws_jx_citation_protected_class',          'tax', 'ws_protected_class'    ],
        'protected_class_details'    => [ 'ws_jx_citation_protected_class_details',  'textarea' ],
        'disclosure_targets'         => [ 'ws_jx_citation_disclosure_targets',       'tax', 'ws_disclosure_targets' ],
        'disclosure_targets_details' => [ 'ws_jx_citation_disclosure_targets_details','textarea' ],
        'adverse_action'             => [ 'ws_jx_citation_adverse_action',           'tax', 'ws_adverse_action_types' ],
        'adverse_action_details'     => [ 'ws_jx_citation_adverse_action_details',   'textarea' ],
        'process_type'               => [ 'ws_jx_citation_process_type',             'tax', 'ws_process_type'       ],
        'remedies'                   => [ 'ws_jx_citation_remedies',                 'tax', 'ws_remedies'           ],
        'remedies_details'           => [ 'ws_jx_citation_remedies_details',         'textarea' ],
        'fee_shifting'               => [ 'ws_jx_citation_fee_shifting',             'tax', 'ws_fee_shifting'       ],
        'employer_defense'           => [ 'ws_jx_citation_employer_defense',         'tax', 'ws_employer_defense'   ],
        'employer_defense_details'   => [ 'ws_jx_citation_employer_defense_details', 'textarea' ],
        'employee_standard'          => [ 'ws_jx_citation_employee_standard',        'tax', 'ws_employee_standard'  ],
        'employee_standard_details'  => [ 'ws_jx_citation_employee_standard_details','textarea' ],

        // ── Advisory / omitted ───────────────────────────────────────────
        '_review_notes'         => [ null, 'omit' ],
        '_multi_taxonomy_notes' => [ null, 'omit' ],
        'links.is_official'     => [ null, 'omit' ],
        'links.url_source'      => [ null, 'omit' ],
        'quality'               => [ null, 'omit' ],
        'court'                 => [ null, 'omit' ],
        'effective_date'        => [ null, 'omit' ],
        'ruling_date'           => [ null, 'omit' ],
        'favorable'             => [ null, 'omit' ],
    ];
}

/**
 * Returns the complete interpretation field map for json_format_version 2.0.
 */
function ws_ingest_interpretation_field_map_v2(): array {
    return [
        // ── Core content ─────────────────────────────────────────────────
        'case_name'                  => [ 'ws_jx_interp_official_name',             'text'     ],
        'common_name'                => [ 'ws_jx_interp_common_name',               'text'     ],
        'links.case_url'             => [ 'ws_jx_interp_url',                       'url'      ],
        'specific_impact'            => [ 'ws_jx_interp_summary',                   'textarea' ],
        'favorable'                  => [ 'ws_jx_interp_favorable',                 'bool'     ],
        'court'                      => [ 'ws_jx_interp_court',                     'text'     ],

        // ── Taxonomy classification ─────────────────────────────────────
        'disclosure_types'           => [ 'ws_jx_interp_disclosure_type',           'tax', 'ws_disclosure_type'    ],
        'protected_class'            => [ 'ws_jx_interp_protected_class',           'tax', 'ws_protected_class'    ],
        'protected_class_details'    => [ 'ws_jx_interp_protected_class_details',   'textarea' ],
        'disclosure_targets'         => [ 'ws_jx_interp_disclosure_targets',        'tax', 'ws_disclosure_targets' ],
        'disclosure_targets_details' => [ 'ws_jx_interp_disclosure_targets_details','textarea' ],
        'adverse_action'             => [ 'ws_jx_interp_adverse_action',            'tax', 'ws_adverse_action_types' ],
        'adverse_action_details'     => [ 'ws_jx_interp_adverse_action_details',    'textarea' ],
        'process_type'               => [ 'ws_jx_interp_process_type',              'tax', 'ws_process_type'       ],
        'remedies'                   => [ 'ws_jx_interp_remedies',                  'tax', 'ws_remedies'           ],
        'remedies_details'           => [ 'ws_jx_interp_remedies_details',          'textarea' ],
        'fee_shifting'               => [ 'ws_jx_interp_fee_shifting',              'tax', 'ws_fee_shifting'       ],
        'employer_defense'           => [ 'ws_jx_interp_employer_defense',          'tax', 'ws_employer_defense'   ],
        'employer_defense_details'   => [ 'ws_jx_interp_employer_defense_details',  'textarea' ],
        'employee_standard'          => [ 'ws_jx_interp_employee_standard',         'tax', 'ws_employee_standard'  ],
        'employee_standard_details'  => [ 'ws_jx_interp_employee_standard_details', 'textarea' ],

        // ── Advisory / omitted ───────────────────────────────────────────
        '_review_notes'         => [ null, 'omit' ],
        '_multi_taxonomy_notes' => [ null, 'omit' ],
        'links.is_official'     => [ null, 'omit' ],
        'links.url_source'      => [ null, 'omit' ],
        'links.is_pdf'          => [ null, 'omit' ],
        'quality'               => [ null, 'omit' ],
        'effective_date'        => [ null, 'omit' ],
        'ruling_date'           => [ null, 'omit' ],
    ];
}

/**
 * Returns the complete assist-org field map for json_format_version 2.0.
 */
function ws_ingest_assist_org_field_map_v2(): array {
    return [
        // ── Core content ─────────────────────────────────────────────────
        'common_name'               => [ 'ws_aorg_common_name',                 'text'     ],
        'official_name'             => [ 'ws_aorg_official_name',               'text'     ],
        'general_description'       => [ 'ws_aorg_description',                 'textarea' ],
        'official_homepage_url'     => [ 'ws_aorg_website_url',                 'url'      ],
        'intake_url'                => [ 'ws_aorg_intake_url',                  'url'      ],
        'contact_url'               => [ 'ws_aorg_contact_url',                 'url'      ],
        'phones'                    => [ 'ws_aorg_phones',                      'repeater_phone' ],
        'emails'                    => [ 'ws_aorg_emails',                      'repeater_email' ],
        'mailing_address'           => [ 'ws_aorg_mailing_address',             'textarea' ],
        'has_secure_channel'        => [ 'ws_aorg_has_secure_channel',          'bool'     ],
        'secure_contact_url'        => [ 'ws_aorg_secure_contact_url',          'url'      ],
        'secure_contact_tool'       => [ 'ws_aorg_secure_contact_tool',         'text'     ],
        'secure_contact_tool_other' => [ 'ws_aorg_secure_contact_tool_other',   'text'     ],
        'languages_additional'      => [ 'ws_aorg_additional_languages',        'text'     ],
        'verified_date_url'         => [ 'ws_aorg_last_reviewed',               'text'     ],
        'whistleblower_scope'       => [ 'ws_aorg_whistleblower_scope',         'number'   ],
        'whistleblower_note'        => [ 'ws_aorg_whistleblower_note',          'textarea' ],
        'income_eligibility_required' => [ 'ws_aorg_income_limit',              'bool'     ],
        'income_eligibility_details' => [ 'ws_aorg_income_limit_notes',         'textarea' ],
        'eligibility_notes'         => [ 'ws_aorg_eligibility_notes',           'textarea' ],
        'anonymous_pre_consult_possible' => [ 'ws_aorg_accepts_anonymous',      'bool'     ],
        'has_attorneys'             => [ 'ws_aorg_licensed_attorneys',          'bool'     ],
        'jurisdiction_exceptions'   => [ 'ws_aorg_jurisdiction_exceptions',     'textarea' ],
        'case_stage_details'        => [ 'ws_aorg_case_stage_details',          'textarea' ],
        'disclosure_targets_details'=> [ 'ws_aorg_disclosure_targets_details',  'textarea' ],

        // ── Taxonomies ───────────────────────────────────────────────────
        'disclosure_types'          => [ 'ws_aorg_disclosure_types',       'tax', 'ws_disclosure_type'    ],
        'disclosure_targets'        => [ 'ws_aorg_disclosure_targets',     'tax', 'ws_disclosure_targets' ],
        'languages_supported'       => [ 'ws_languages',                   'tax', 'ws_languages'          ],
        'assistance_type'           => [ 'ws_aorg_type',                   'tax', 'ws_aorg_type'          ],
        'employment_sectors'        => [ 'ws_aorg_employment_sectors',     'tax', 'ws_employment_sector'  ],
        'cost_models'               => [ 'ws_aorg_cost_models',            'tax', 'ws_aorg_cost_model'    ],
        'services_provided'         => [ 'ws_aorg_services',               'tax', 'ws_aorg_service'       ],
        'process_types'             => [ 'ws_aorg_process_types',          'tax', 'ws_process_type'       ],
        'case_stages'               => [ 'ws_aorg_case_stages',            'tax', 'ws_case_stage'         ],

        // ── Advisory / omitted ───────────────────────────────────────────
        // organization_name is intentionally omitted from the field map loop.
        // It is written twice manually in ws_ingest_process_assist_org_record():
        //   1. As post_title via wp_insert_post()
        //   2. As ws_aorg_official_name via update_post_meta()
        // The field map loop would only write it once.
        'organization_name'         => [ null, 'omit' ],
        'internal_id'               => [ null, 'omit' ],
        'source_url'                => [ null, 'omit' ],
        'homepage_url_status'       => [ null, 'omit' ],
        'nationwide_example'        => [ null, 'seed' ],
        '_review_notes'             => [ null, 'seed' ],
        '_reconciled_notes'         => [ null, 'omit' ],
    ];
}

/**
 * Normalizes phones array rows for ws_aorg_phones repeater writes.
 *
 * Expected input:
 *   [ { "type": "hotline", "number": "(555) 000-0000" }, ... ]
 *
 * @return array<int,array<string,string>>
 */
function ws_ingest_normalize_phone_rows( $value ): array {
    if ( ! is_array( $value ) ) {
        return [];
    }

    $rows = [];
    $allowed = defined( 'WS_SCHEMA_PHONE_TYPE' ) && is_array( WS_SCHEMA_PHONE_TYPE )
        ? WS_SCHEMA_PHONE_TYPE
        : [ 'hotline', 'intake', 'headquarters', 'regional', 'tty', 'fax', 'other' ];

    foreach ( $value as $row ) {
        if ( ! is_array( $row ) ) {
            continue;
        }
        $type   = strtolower( trim( (string) ( $row['type'] ?? '' ) ) );
        $number = trim( (string) ( $row['number'] ?? '' ) );
        if ( $type === '' || $number === '' ) {
            continue;
        }
        if ( ! in_array( $type, $allowed, true ) ) {
            continue;
        }
        $rows[] = [
            'ws_aorg_phone_type'   => $type,
            'ws_aorg_phone_number' => $number,
        ];
    }

    return $rows;
}

/**
 * Normalizes emails array rows for ws_aorg_emails repeater writes.
 *
 * Expected input:
 *   [ { "type": "intake", "address": "team@example.org" }, ... ]
 *
 * @return array<int,array<string,string>>
 */
function ws_ingest_normalize_email_rows( $value ): array {
    if ( ! is_array( $value ) ) {
        return [];
    }

    $rows = [];
    $allowed = defined( 'WS_SCHEMA_EMAIL_TYPE' ) && is_array( WS_SCHEMA_EMAIL_TYPE )
        ? WS_SCHEMA_EMAIL_TYPE
        : [ 'intake', 'general', 'legal', 'media', 'support', 'other' ];

    foreach ( $value as $row ) {
        if ( ! is_array( $row ) ) {
            continue;
        }
        $type    = strtolower( trim( (string) ( $row['type'] ?? '' ) ) );
        $address = sanitize_email( (string) ( $row['address'] ?? '' ) );
        if ( $type === '' || $address === '' ) {
            continue;
        }
        if ( ! in_array( $type, $allowed, true ) ) {
            continue;
        }
        $rows[] = [
            'ws_aorg_email_type'    => $type,
            'ws_aorg_email_address' => $address,
        ];
    }

    return $rows;
}

/**
 * Builds post_content seed append blocks from assist-org record fields.
 */
function ws_ingest_build_assist_org_seed_append( array $record ): string {
    $nationwide_example = trim( (string) ws_ingest_get_value( $record, 'nationwide_example' ) );
    $case_stage_details = trim( (string) ws_ingest_get_value( $record, 'case_stage_details' ) );
    $jurisdiction_exceptions = trim( (string) ws_ingest_get_value( $record, 'jurisdiction_exceptions' ) );
    $review_notes       = trim( (string) ws_ingest_get_value( $record, '_review_notes' ) );

    $blocks = [];
    if ( $nationwide_example !== '' ) {
        $blocks[] = "Nationwide scope note: {$nationwide_example}";
    }
    if ( $case_stage_details !== '' ) {
        $blocks[] = "Case stage notes: {$case_stage_details}";
    }
    if ( $jurisdiction_exceptions !== '' ) {
        $blocks[] = "Jurisdiction exceptions: {$jurisdiction_exceptions}";
    }
    if ( $review_notes !== '' ) {
        $blocks[] = "Researcher notes: {$review_notes}";
    }

    if ( empty( $blocks ) ) {
        return '';
    }

    return "\n\n---\n" . implode( "\n\n---\n", array_map( 'trim', $blocks ) );
}

function ws_ingest_build_assist_org_internal_id( array $record, string $jx_slug = '' ): string {
    $org_name = trim( (string) ( $record['organization_name'] ?? '' ) );
    $homepage = trim( (string) ( $record['official_homepage_url'] ?? '' ) );
    $jx_slug  = strtolower( trim( (string) $jx_slug ) );

    $host = strtolower( (string) wp_parse_url( $homepage, PHP_URL_HOST ) );
    if ( str_starts_with( $host, 'www.' ) ) {
        $host = substr( $host, 4 );
    }

    // Ingest always generates assist-org internal IDs; never trust batch-supplied IDs.
    $seed = $org_name !== '' ? $org_name : $host;
    if ( $seed === '' ) {
        $seed = 'assist org';
    }

    $normalized = strtolower( $seed );
    // Strip ampersands directly (do not expand to "and").
    $normalized = str_replace( '&', ' ', $normalized );

    // Swap jurisdiction display name to compact jurisdiction ID token.
    if ( $jx_slug !== '' && defined( 'WS_JURISDICTION_TAXONOMY' ) ) {
        $jx_term = get_term_by( 'slug', $jx_slug, WS_JURISDICTION_TAXONOMY );
        if ( $jx_term && ! is_wp_error( $jx_term ) ) {
            $jx_name = strtolower( trim( (string) $jx_term->name ) );
            if ( $jx_name !== '' ) {
                $jx_name_rx = preg_quote( $jx_name, '/' );
                $normalized = preg_replace( '/\b' . $jx_name_rx . '\b/u', ' ' . $jx_slug . ' ', $normalized );
            }
        }
    }

    // Strip small stop words before abbreviation pass.
    $normalized = preg_replace( '/\b(?:and|the|for|of|in|at|to|a|an)\b/u', ' ', $normalized );

    // Human-readable abbreviation pass (no hard length cap).
    // IMPORTANT: Keep this ruleset in sync with
    // ws_matrix_build_assist_org_internal_id() in matrix-assist-orgs.php.
    // If these diverge, seeded/internal IDs will drift over time.
    $abbrev_rules = [
        '/\bwhistle[\s\-]*blow(?:er|ers|ing)\b/u' => 'wb',
        '/\bglobal\b/u'                              => 'intl',
        '/\binternational\b/u'                       => 'intl',
        '/\bnationals?\b/u'                          => 'nat',
        '/\borganizations?\b/u'                      => 'org',
        '/\borganisations?\b/u'                      => 'org',
        '/\bassociations?\b/u'                       => 'assoc',
        '/\bcoalitions?\b/u'                         => 'coal',
        '/\balliances?\b/u'                          => 'all',
        '/\bcommittees?\b/u'                         => 'cmte',
        '/\bcouncils?\b/u'                           => 'cncl',
        '/\binstitutions?\b/u'                       => 'inst',
        '/\binstitutes?\b/u'                         => 'inst',
        '/\bbureaus?\b/u'                            => 'bur',
        '/\boffices?\b/u'                            => 'ofc',
        '/\bemployees?\b/u'                          => 'emp',
        '/\bemployment\b/u'                          => 'emp',
        '/\bprotections?\b/u'                        => 'prot',
        '/\badvocacy\b/u'                            => 'adv',
        '/\brights\b/u'                              => 'rts',
        '/\bpublic\b/u'                              => 'pub',
        '/\bpolicy\b/u'                              => 'pol',
        '/\beducational\b/u'                         => 'edu',
        '/\beducation\b/u'                           => 'edu',
        '/\bresearch\b/u'                            => 'rsch',
        '/\battorneys?\b/u'                          => 'att',
        '/\breferrals?\b/u'                          => 'ref',
        '/\bfederal\b/u'                             => 'fed',
        '/\bgovernmental\b/u'                        => 'gov',
        '/\bgovernments?\b/u'                        => 'gov',
        '/\bdepartments?\b/u'                        => 'dept',
        '/\bcommissions?\b/u'                        => 'comm',
        '/\bcorporations?\b/u'                       => 'corp',
        '/\bfoundations?\b/u'                        => 'fdn',
        '/\bcenters?\b/u'                            => 'ctr',
        '/\bcentres?\b/u'                            => 'ctr',
        '/\bservices?\b/u'                           => 'svc',
        '/\bnetworks?\b/u'                           => 'net',
        '/\bprograms?\b/u'                           => 'prog',
        '/\bprojects?\b/u'                           => 'proj',
        '/\binitiatives?\b/u'                        => 'init',
        '/\bresources?\b/u'                          => 'res',
    ];
    foreach ( $abbrev_rules as $pattern => $replacement ) {
        $normalized = preg_replace( $pattern, ' ' . $replacement . ' ', $normalized );
    }

    $normalized = preg_replace( '/[^a-z0-9]+/u', '-', $normalized );
    $normalized = trim( (string) $normalized, '-' );
    $normalized = preg_replace( '/-+/', '-', (string) $normalized );

    if ( $normalized === '' ) {
        $normalized = $host !== '' ? sanitize_title( $host ) : 'assist-org';
    }

    if ( $jx_slug !== '' && ! preg_match( '/(^|-)' . preg_quote( $jx_slug, '/' ) . '($|-)/', $normalized ) ) {
        $normalized .= '-' . $jx_slug;
    }

    return $normalized;
}

function ws_ingest_find_assist_org_post_id( string $record_key, string $internal_id, string $homepage_url ): int {
    if ( $record_key !== '' ) {
        $existing = get_posts( [
            'post_type'      => 'ws-assist-org',
            'post_status'    => 'any',
            'posts_per_page' => 1,
            'fields'         => 'ids',
            'no_found_rows'  => true,
            'meta_query'     => [ [
                'key'     => '_ws_ingest_record_key',
                'value'   => $record_key,
                'compare' => '=',
            ] ],
        ] );
        if ( ! empty( $existing ) ) {
            return (int) $existing[0];
        }
    }

    if ( $internal_id !== '' ) {
        $existing = get_posts( [
            'post_type'      => 'ws-assist-org',
            'post_status'    => 'any',
            'posts_per_page' => 1,
            'fields'         => 'ids',
            'no_found_rows'  => true,
            'meta_query'     => [ [
                'key'     => 'ws_aorg_internal_id',
                'value'   => $internal_id,
                'compare' => '=',
            ] ],
        ] );
        if ( ! empty( $existing ) ) {
            return (int) $existing[0];
        }
    }

    if ( $homepage_url !== '' ) {
        $existing = get_posts( [
            'post_type'      => 'ws-assist-org',
            'post_status'    => 'any',
            'posts_per_page' => 1,
            'fields'         => 'ids',
            'no_found_rows'  => true,
            'meta_query'     => [ [
                'key'     => 'ws_aorg_website_url',
                'value'   => $homepage_url,
                'compare' => '=',
            ] ],
        ] );
        if ( ! empty( $existing ) ) {
            return (int) $existing[0];
        }
    }

    return 0;
}

/**
 * Finds a statute post by canonical statute ID.
 */
function ws_ingest_find_statute_post_id_by_statute_id( string $statute_id, string $jx_slug ): int {
    $statute_id = strtoupper( trim( $statute_id ) );
    if ( $statute_id === '' ) {
        return 0;
    }

    $existing = get_posts( [
        'post_type'      => 'jx-statute',
        'post_status'    => 'any',
        'posts_per_page' => 1,
        'fields'         => 'ids',
        'no_found_rows'  => true,
        'meta_query'     => [ [
            'key'     => '_ws_jx_statute_id',
            'value'   => $statute_id,
            'compare' => '=',
        ] ],
    ] );

    if ( ! empty( $existing ) ) {
        return (int) $existing[0];
    }

    $record_key = strtolower( trim( $jx_slug ) . '|' . $statute_id );
    if ( $record_key === '' ) {
        return 0;
    }

    $fallback = get_posts( [
        'post_type'      => 'jx-statute',
        'post_status'    => 'any',
        'posts_per_page' => 1,
        'fields'         => 'ids',
        'no_found_rows'  => true,
        'meta_query'     => [ [
            'key'     => '_ws_ingest_record_key',
            'value'   => $record_key,
            'compare' => '=',
        ] ],
    ] );

    return ! empty( $fallback ) ? (int) $fallback[0] : 0;
}

/**
 * Finds a common-law post by canonical doctrine ID.
 */
function ws_ingest_find_common_law_post_id_by_doctrine_id( string $doctrine_id, string $jx_slug ): int {
    $doctrine_id = strtoupper( trim( $doctrine_id ) );
    if ( $doctrine_id === '' ) {
        return 0;
    }

    $existing = get_posts( [
        'post_type'      => 'jx-common-law',
        'post_status'    => 'any',
        'posts_per_page' => 1,
        'fields'         => 'ids',
        'no_found_rows'  => true,
        'meta_query'     => [ [
            'key'     => '_ws_cl_doctrine_id',
            'value'   => $doctrine_id,
            'compare' => '=',
        ] ],
    ] );

    if ( ! empty( $existing ) ) {
        return (int) $existing[0];
    }

    $record_key = strtolower( trim( $jx_slug ) . '|' . $doctrine_id );
    if ( $record_key === '' ) {
        return 0;
    }

    $fallback = get_posts( [
        'post_type'      => 'jx-common-law',
        'post_status'    => 'any',
        'posts_per_page' => 1,
        'fields'         => 'ids',
        'no_found_rows'  => true,
        'meta_query'     => [ [
            'key'     => '_ws_ingest_record_key',
            'value'   => $record_key,
            'compare' => '=',
        ] ],
    ] );

    return ! empty( $fallback ) ? (int) $fallback[0] : 0;
}


// ── Taxonomy validation ───────────────────────────────────────────────────────

/**
 * Returns all registered term slugs for a taxonomy.
 * Used to validate ingest values before writing.
 */
function ws_ingest_get_valid_slugs( string $taxonomy ): array {
    static $cache = [];
    if ( isset( $cache[ $taxonomy ] ) ) return $cache[ $taxonomy ];

    // Bypass object cache to ensure newly seeded terms are visible.
    // Without this, get_terms() may return stale results if the
    // persistent object cache has not been invalidated since seeding.
    clean_taxonomy_cache( $taxonomy );

    $terms = get_terms( [
        'taxonomy'        => $taxonomy,
        'hide_empty'      => false,
        'fields'          => 'slugs',
        'cache_results'   => false,
        'update_term_meta_cache' => false,
    ] );

    $cache[ $taxonomy ] = is_wp_error( $terms ) ? [] : $terms;
    return $cache[ $taxonomy ];
}

/**
 * Validates and filters a taxonomy array.
 * Removes: invalid slugs, parent slugs, blacklisted proposed terms.
 * has-details sentinel is only valid when a companion _details field is non-empty.
 *
 * @return array [ 'valid' => string[], 'removed' => [ slug => reason ] ]
 */
function ws_ingest_validate_taxonomy_array( array $slugs, string $taxonomy, array $blacklist, array $record ): array {
    $valid_slugs  = ws_ingest_get_valid_slugs( $taxonomy );
    $valid        = [];
    $removed      = [];

    // Parent slugs — structural labels only, never valid record values
    $parent_slugs = [
        'workplace-employment', 'financial-corporate', 'government-accountability',
        'public-health-safety', 'privacy-data-integrity', 'national-security',
        'public-sector', 'private-sector', 'healthcare-staff', 'special-status',
        'internal', 'external-agency', 'legislative', 'judicial', 'public',
    ];

    foreach ( $slugs as $slug ) {
        if ( in_array( $slug, $parent_slugs, true ) ) {
            $removed[ $slug ] = 'parent slug';
            continue;
        }
        if ( isset( $blacklist[ $slug ] ) ) {
            $removed[ $slug ] = 'proposed term (pending)';
            continue;
        }
        if ( ! in_array( $slug, $valid_slugs, true ) ) {
            $removed[ $slug ] = 'unregistered slug';
            continue;
        }
        $valid[] = $slug;
    }

    return [ 'valid' => $valid, 'removed' => $removed ];
}


// ── Value extractor ───────────────────────────────────────────────────────────

/**
 * Extracts a value from a nested record array using dot-notation path.
 * Returns null if path not found.
 */
function ws_ingest_get_value( array $record, string $path ) {
    $parts   = explode( '.', $path );
    $current = $record;
    foreach ( $parts as $part ) {
        if ( ! is_array( $current ) || ! array_key_exists( $part, $current ) ) {
            return null;
        }
        $current = $current[ $part ];
    }
    return $current;
}

/**
 * Parses boolean-like payload values with explicit handling for ternary strings.
 *
 * Accepted true values:  true, 1, "1", "true", "yes", "y", "on"
 * Accepted false values: false, 0, "0", "false", "no", "n", "off", ""
 *
 * Special handling:
 * - "unclear" is coerced to 0 and logged as a warning.
 * - Unknown non-empty strings are coerced to 0 and logged as warnings.
 */
function ws_ingest_parse_boolish_value( $value, string $record_id, string $json_path, array &$warnings ): int {
    if ( is_bool( $value ) ) {
        return $value ? 1 : 0;
    }

    if ( is_int( $value ) || is_float( $value ) ) {
        return ( (float) $value ) > 0 ? 1 : 0;
    }

    if ( is_string( $value ) ) {
        $raw = trim( $value );
        $v   = strtolower( $raw );

        if ( $v === '' || in_array( $v, [ '0', 'false', 'no', 'n', 'off' ], true ) ) {
            return 0;
        }
        if ( in_array( $v, [ '1', 'true', 'yes', 'y', 'on' ], true ) ) {
            return 1;
        }

        $tri_keys = [ 'anonymous_pre_consult_possible', 'has_attorneys', 'income_eligibility_required' ];
        $is_tri_key = in_array( $json_path, $tri_keys, true );
        if ( $v === 'unclear' ) {
            $warnings[] = $is_tri_key
                ? "{$record_id}: {$json_path}='unclear' coerced to 0 (no) for ingest; meat-bag review required."
                : "{$record_id}: {$json_path}='unclear' coerced to 0 (false) for ingest.";
            return 0;
        }

        $warnings[] = "{$record_id}: {$json_path} has unsupported boolean value '{$raw}'; coerced to 0 (false).";
        return 0;
    }

    if ( $value === null ) {
        return 0;
    }

    return (int) (bool) $value;
}

/**
 * Normalizes date-like values to YYYY-MM-DD.
 * Used for legacy values like "2026-04-07 14:22 UTC".
 * Returns empty string when no YYYY-MM-DD token is found.
 */
function ws_ingest_normalize_ymd_date( $value ): string {
    $raw = trim( (string) $value );
    if ( $raw === '' ) {
        return '';
    }

    if ( preg_match( '/\b(\d{4}-\d{2}-\d{2})\b/', $raw, $m ) ) {
        return $m[1];
    }

    return '';
}

/**
 * Normalizes a free-text agency label for loose matching.
 */
function ws_ingest_normalize_agency_label( string $value ): string {
    $value = strtolower( trim( $value ) );
    if ( $value === '' ) {
        return '';
    }

    $value = preg_replace( '/\(.*?\)/', ' ', $value );
    $value = preg_replace( '/[^a-z0-9]+/', ' ', (string) $value );
    $value = preg_replace( '/\s+/', ' ', (string) $value );
    return trim( (string) $value );
}

/**
 * Splits a primary_agency string into agency-like labels.
 */
function ws_ingest_extract_agency_labels( string $primary_agency ): array {
    $value = trim( $primary_agency );
    if ( $value === '' ) {
        return [];
    }

    // Keep slash compounds (for example Cal/OSHA) intact; split on semicolon/comma/and/or only.
    $parts = preg_split( '/\s*(?:;|,|\band\b|\bor\b)\s*/i', $value );
    if ( ! is_array( $parts ) ) {
        return [ $value ];
    }

    $parts = array_values( array_filter( array_map( 'trim', $parts ) ) );
    return empty( $parts ) ? [ $value ] : $parts;
}

/**
 * Extracts meaningful agency tokens from a normalized label.
 */
function ws_ingest_agency_meaningful_tokens( string $normalized_label ): array {
    if ( $normalized_label === '' ) {
        return [];
    }

    $raw_tokens = preg_split( '/\s+/', $normalized_label );
    if ( ! is_array( $raw_tokens ) ) {
        return [];
    }

    $stopwords = [
        'the', 'of', 'and', 'for', 'to', 'in', 'at', 'on',
        'department', 'dept', 'division', 'office', 'board', 'commission', 'agency', 'bureau',
        'state', 'commonwealth', 'county', 'city',
        'cal', 'ca', 'ma', 'nj', 'pa', 'us',
    ];

    $tokens = [];
    foreach ( $raw_tokens as $token ) {
        $token = trim( (string) $token );
        if ( $token === '' || strlen( $token ) < 3 ) {
            continue;
        }
        if ( in_array( $token, $stopwords, true ) ) {
            continue;
        }
        $tokens[] = $token;
    }

    return array_values( array_unique( $tokens ) );
}

/**
 * Returns a match reason code when a label/candidate pair is strong enough.
 */
function ws_ingest_agency_match_reason( string $needle, string $candidate ): string {
    if ( $needle === '' || $candidate === '' ) {
        return '';
    }

    if ( $needle === $candidate ) {
        return 'exact';
    }

    if ( strlen( $needle ) < 6 || strlen( $candidate ) < 6 ) {
        return '';
    }

    if ( ! str_contains( $needle, $candidate ) && ! str_contains( $candidate, $needle ) ) {
        return '';
    }

    $needle_tokens    = ws_ingest_agency_meaningful_tokens( $needle );
    $candidate_tokens = ws_ingest_agency_meaningful_tokens( $candidate );
    $shared_tokens    = array_values( array_intersect( $needle_tokens, $candidate_tokens ) );

    if ( count( $shared_tokens ) >= 2 ) {
        return 'containment+token_overlap';
    }

    return '';
}

/**
 * Finds ws-agency IDs assigned to a jurisdiction term that match agency labels.
 * Returns both IDs and reason codes for breadcrumb debugging.
 */
function ws_ingest_match_agencies_for_jx_detailed( array $labels, string $jx_slug ): array {
    $jx_slug = strtolower( trim( $jx_slug ) );
    if ( empty( $labels ) || $jx_slug === '' ) {
        return [ 'matched_ids' => [], 'reasons' => [] ];
    }

    $term = get_term_by( 'slug', $jx_slug, WS_JURISDICTION_TAXONOMY );
    if ( ! $term || is_wp_error( $term ) ) {
        return [ 'matched_ids' => [], 'reasons' => [] ];
    }

    $q = new WP_Query( [
        'post_type'      => 'ws-agency',
        'post_status'    => 'any',
        'posts_per_page' => -1,
        'fields'         => 'ids',
        'no_found_rows'  => true,
        'tax_query'      => [ [
            'taxonomy' => WS_JURISDICTION_TAXONOMY,
            'field'    => 'term_id',
            'terms'    => [ (int) $term->term_id ],
        ] ],
    ] );

    if ( empty( $q->posts ) ) {
        return [ 'matched_ids' => [], 'reasons' => [] ];
    }

    $normalized_needles = [];
    foreach ( $labels as $label ) {
        $normalized = ws_ingest_normalize_agency_label( (string) $label );
        if ( $normalized !== '' ) {
            $normalized_needles[] = $normalized;
        }
    }
    $normalized_needles = array_values( array_unique( $normalized_needles ) );

    if ( empty( $normalized_needles ) ) {
        return [ 'matched_ids' => [], 'reasons' => [] ];
    }

    $matched_ids = [];
    $reasons     = [];

    foreach ( $q->posts as $agency_id ) {
        $candidates = [
            ws_ingest_normalize_agency_label( get_the_title( $agency_id ) ),
            ws_ingest_normalize_agency_label( (string) get_post_meta( $agency_id, 'ws_agency_name', true ) ),
            ws_ingest_normalize_agency_label( (string) get_post_meta( $agency_id, 'ws_agency_code', true ) ),
        ];
        $candidates = array_values( array_unique( array_filter( $candidates ) ) );

        foreach ( $normalized_needles as $needle ) {
            foreach ( $candidates as $candidate ) {
                $reason = ws_ingest_agency_match_reason( $needle, $candidate );
                if ( $reason !== '' ) {
                    $matched_ids[] = (int) $agency_id;
                    $reasons[] = 'agency#' . (int) $agency_id . ' via ' . $reason . " (needle='" . $needle . "', candidate='" . $candidate . "')";
                    continue 3;
                }
            }
        }
    }

    return [
        'matched_ids' => array_values( array_unique( $matched_ids ) ),
        'reasons'     => array_values( array_unique( $reasons ) ),
    ];
}

/**
 * Finds ws-agency IDs assigned to a jurisdiction term that match agency labels.
 */
function ws_ingest_match_agencies_for_jx( array $labels, string $jx_slug ): array {
    $match = ws_ingest_match_agencies_for_jx_detailed( $labels, $jx_slug );
    return (array) ( $match['matched_ids'] ?? [] );
}

/**
 * Replaces jurisdiction names with their IDs/slugs using the taxonomy table.
 */
function ws_ingest_replace_jx_names_with_ids( string $value ): string {
    $value = trim( $value );
    if ( $value === '' ) {
        return '';
    }

    $terms = get_terms( [
        'taxonomy'   => WS_JURISDICTION_TAXONOMY,
        'hide_empty' => false,
    ] );
    if ( is_wp_error( $terms ) || empty( $terms ) ) {
        return $value;
    }

    foreach ( $terms as $term ) {
        if ( ! isset( $term->name, $term->slug ) ) {
            continue;
        }
        $name = trim( (string) $term->name );
        $slug = strtoupper( trim( (string) $term->slug ) );
        if ( $name === '' || $slug === '' ) {
            continue;
        }
        $value = (string) preg_replace( '/\b' . preg_quote( $name, '/' ) . '\b/i', $slug, $value );
    }

    return trim( preg_replace( '/\s+/', ' ', $value ) );
}

/**
 * Prefixes state-level agency labels with jurisdiction ID when missing.
 */
function ws_ingest_prepare_agency_stub_label( string $label, string $jx_slug ): string {
    $label = trim( preg_replace( '/\s+/', ' ', $label ) );
    if ( $label === '' ) {
        return '';
    }

    $jx_slug = strtolower( trim( $jx_slug ) );
    $prefixed = ws_ingest_replace_jx_names_with_ids( $label );
    $term = get_term_by( 'slug', $jx_slug, WS_JURISDICTION_TAXONOMY );
    $jx_id = strtoupper( (string) $jx_slug );
    $jx_name = '';
    if ( $term && ! is_wp_error( $term ) ) {
        $jx_name = trim( (string) ( $term->name ?? '' ) );
        if ( $jx_id === '' ) {
            $jx_id = strtoupper( (string) ( $term->slug ?? '' ) );
        }
    }

    $starts_with_id = ( $jx_id !== '' ) && (bool) preg_match( '/^' . preg_quote( $jx_id, '/' ) . '\b/i', $prefixed );
    $starts_with_name = ( $jx_name !== '' ) && (bool) preg_match( '/^' . preg_quote( $jx_name, '/' ) . '\b/i', $prefixed );

    if ( $jx_id !== '' && ! $starts_with_id && ! $starts_with_name ) {
        $prefixed = $jx_id . ' ' . $prefixed;
    }

    return trim( preg_replace( '/\s+/', ' ', $prefixed ) );
}

/**
 * Builds an abbreviated agency code slug with no hard length cap.
 */
function ws_ingest_build_agency_stub_code( string $label ): string {
    $slug = strtolower( trim( $label ) );
    if ( $slug === '' ) {
        return '';
    }

    $slug = ws_ingest_replace_jx_names_with_ids( $slug );

    $phrase_replacements = [
        'attorney general' => 'ag',
    ];
    foreach ( $phrase_replacements as $from => $to ) {
        $slug = str_ireplace( $from, $to, $slug );
    }

    $word_replacements = [
        'commissioner' => 'commr',
        'department'   => 'dept',
        'division'     => 'div',
        'standards'    => 'stds',
        'enforcement'  => 'enf',
        'personnel'    => 'pers',
        'office'       => 'ofc',
        'board'        => 'bd',
        'commission'   => 'comm',
        'state'        => 'st',
        'public'       => 'pub',
        'health'       => 'hlth',
        'social'       => 'soc',
        'services'     => 'svcs',
    ];
    foreach ( $word_replacements as $from => $to ) {
        $slug = (string) preg_replace( '/\b' . preg_quote( $from, '/' ) . '\b/i', $to, $slug );
    }

    $slug = preg_replace( '/[^a-z0-9]+/', '-', (string) $slug );
    $slug = preg_replace( '/-+/', '-', (string) $slug );
    return trim( (string) $slug, '-' );
}

/**
 * Returns true when post is assigned to the jurisdiction term.
 */
function ws_ingest_post_has_jx_term( int $post_id, int $term_id ): bool {
    if ( $post_id <= 0 || $term_id <= 0 ) {
        return false;
    }
    $terms = wp_get_post_terms( $post_id, WS_JURISDICTION_TAXONOMY, [ 'fields' => 'ids' ] );
    if ( is_wp_error( $terms ) || empty( $terms ) ) {
        return false;
    }
    return in_array( $term_id, array_map( 'intval', $terms ), true );
}

/**
 * Finds an existing agency by normalized code/name within a jurisdiction.
 */
function ws_ingest_find_existing_agency_stub( string $display_label, string $agency_code, int $term_id ): int {
    if ( $term_id <= 0 ) {
        return 0;
    }

    $display_label = trim( $display_label );
    $agency_code   = trim( $agency_code );

    if ( $agency_code !== '' ) {
        $by_path = get_page_by_path( $agency_code, OBJECT, 'ws-agency' );
        if ( $by_path instanceof WP_Post && ws_ingest_post_has_jx_term( (int) $by_path->ID, $term_id ) ) {
            return (int) $by_path->ID;
        }

        $by_code = get_posts( [
            'post_type'      => 'ws-agency',
            'post_status'    => 'any',
            'posts_per_page' => 1,
            'fields'         => 'ids',
            'no_found_rows'  => true,
            'tax_query'      => [ [
                'taxonomy' => WS_JURISDICTION_TAXONOMY,
                'field'    => 'term_id',
                'terms'    => [ $term_id ],
            ] ],
            'meta_query'     => [ [
                'key'   => 'ws_agency_code',
                'value' => $agency_code,
            ] ],
        ] );
        if ( ! empty( $by_code ) ) {
            return (int) $by_code[0];
        }
    }

    if ( $display_label !== '' ) {
        $by_name = get_posts( [
            'post_type'      => 'ws-agency',
            'post_status'    => 'any',
            'posts_per_page' => 1,
            'fields'         => 'ids',
            'no_found_rows'  => true,
            'tax_query'      => [ [
                'taxonomy' => WS_JURISDICTION_TAXONOMY,
                'field'    => 'term_id',
                'terms'    => [ $term_id ],
            ] ],
            'meta_query'     => [ [
                'key'   => 'ws_agency_name',
                'value' => $display_label,
            ] ],
        ] );
        if ( ! empty( $by_name ) ) {
            return (int) $by_name[0];
        }
    }

    return 0;
}

/**
 * Returns true when a parsed label looks like an agency body, not a forum/path.
 */
function ws_ingest_should_create_agency_stub( string $label ): bool {
    $label = trim( $label );
    if ( $label === '' || strlen( $label ) < 2 ) {
        return false;
    }
    return true;
}

/**
 * Creates a draft ws-agency stub for an unmatched primary_agency label.
 */
function ws_ingest_create_agency_stub( string $label, string $jx_slug, ?bool &$was_created = null ) {
    $was_created = false;
    $label = trim( $label );
    if ( ! ws_ingest_should_create_agency_stub( $label ) ) {
        return 0;
    }

    $jx_slug = strtolower( trim( $jx_slug ) );
    $term    = get_term_by( 'slug', $jx_slug, WS_JURISDICTION_TAXONOMY );
    if ( ! $term || is_wp_error( $term ) ) {
        return 0;
    }

    $display_label = ws_ingest_prepare_agency_stub_label( $label, $jx_slug );
    if ( $display_label === '' ) {
        $display_label = $label;
    }
    $agency_code = ws_ingest_build_agency_stub_code( $display_label );
    if ( $agency_code === '' ) {
        $agency_code = sanitize_title( $display_label );
    }

    $existing_id = ws_ingest_find_existing_agency_stub( $display_label, $agency_code, (int) $term->term_id );
    if ( $existing_id > 0 ) {
        return (int) $existing_id;
    }

    $post_id = wp_insert_post( [
        'post_type'   => 'ws-agency',
        'post_status' => 'draft',
        'post_title'  => $display_label,
        'post_name'   => $agency_code,
        'post_author' => get_current_user_id(),
    ] );

    if ( is_wp_error( $post_id ) || ! $post_id ) {
        return 0;
    }

    update_post_meta( $post_id, 'ws_agency_name', $display_label );
    update_post_meta( $post_id, 'ws_agency_code', $agency_code );
    update_post_meta( $post_id, '_ws_agency_stub', 1 );
    update_post_meta( $post_id, '_ws_agency_stub_source', 'ingest.primary_agency' );

    wp_set_object_terms( $post_id, [ (int) $term->term_id ], WS_JURISDICTION_TAXONOMY );

    $was_created = true;

    return (int) $post_id;
}

/**
 * Parses attached citations from array or free-text input into citation strings.
 */
function ws_ingest_parse_attached_citations( $raw ): array {
    $items = [];

    if ( is_array( $raw ) ) {
        foreach ( $raw as $entry ) {
            if ( is_string( $entry ) ) {
                $items[] = $entry;
            }
        }
    } elseif ( is_string( $raw ) ) {
        $items[] = $raw;
    } else {
        return [];
    }

    $parsed = [];
    foreach ( $items as $item ) {
        $lines = preg_split( '/(?:\r\n|\n|\r)+/', $item );
        if ( ! is_array( $lines ) ) {
            $lines = [ $item ];
        }

        $chunks = [];
        foreach ( $lines as $line ) {
            $line = trim( (string) $line );
            if ( $line === '' ) {
                continue;
            }
            $chunks[] = $line;
        }

        foreach ( $chunks as $chunk ) {
            $clean = trim( (string) $chunk );
            $clean = preg_replace( '/^[\-*\x{2022}\d\)\.\s]+/u', '', $clean );
            $clean = trim( (string) $clean );
            if ( $clean !== '' ) {
                $parsed[] = $clean;
            }
        }
    }

    return array_values( array_unique( $parsed ) );
}

/**
 * Parses one citation row into structured values.
 * Expected row shape: CASE || IMPACT || URL || SOURCE || QUALITY
 */
function ws_ingest_parse_citation_entry( string $citation_text ): array {
    $entry = [
        'case_name'       => trim( $citation_text ),
        'specific_impact' => '',
        'url'             => '',
        'source'          => '',
        'quality'         => '',
        'raw'             => trim( $citation_text ),
    ];

    if ( ! str_contains( $citation_text, '||' ) ) {
        return $entry;
    }

    $parts = array_map( 'trim', explode( '||', $citation_text ) );
    if ( ! empty( $parts[0] ) ) {
        $entry['case_name'] = $parts[0];
    }
    if ( ! empty( $parts[1] ) ) {
        $entry['specific_impact'] = $parts[1];
    }
    if ( ! empty( $parts[2] ) ) {
        $entry['url'] = $parts[2];
    }
    if ( ! empty( $parts[3] ) ) {
        $entry['source'] = $parts[3];
    }
    if ( ! empty( $parts[4] ) ) {
        $entry['quality'] = $parts[4];
    }

    return $entry;
}

/**
 * Derives citation type from ingest batch context.
 */
function ws_ingest_citation_type_from_batch( array $meta ): string {
    $record_type = strtolower( trim( (string) ( $meta['record_type'] ?? 'statute' ) ) );

    $map = [
        'statute'        => 'statute',
        'citation'       => 'case_law',
        'interpretation' => 'case_law',
        'common-law'     => 'case_law',
        'regulatory'     => 'regulatory',
        'secondary'      => 'secondary',
    ];

    return $map[ $record_type ] ?? 'statute';
}

/**
 * Builds a stable dedupe key for citation stubs created during ingest.
 */
function ws_ingest_normalize_case_name( string $case_name ): string {
    $normalized = strtolower( trim( preg_replace( '/\s+/', ' ', $case_name ) ) );
    $normalized = preg_replace( '/[^a-z0-9]+/', '', (string) $normalized );
    return (string) $normalized;
}

/**
 * Builds a stable dedupe key for citation stubs created during ingest.
 */
function ws_ingest_build_citation_key( string $jx_slug, string $parent_type, int $parent_post_id, string $case_name ): string {
    $normalized_case = ws_ingest_normalize_case_name( $case_name );
    $payload         = strtolower( $jx_slug ) . '|' . strtolower( $parent_type ) . '|' . (int) $parent_post_id . '|' . $normalized_case;
    return hash( 'sha256', $payload );
}

/**
 * Finds an existing citation linked to the same parent by case name.
 */
function ws_ingest_find_citation_by_parent_and_case( string $parent_meta_key, int $parent_post_id, string $case_name ): int {
    if ( $parent_post_id <= 0 || trim( $case_name ) === '' ) {
        return 0;
    }

    $case_name = sanitize_text_field( $case_name );

    $existing = get_posts( [
        'post_type'      => 'jx-citation',
        'post_status'    => 'any',
        'posts_per_page' => 1,
        'fields'         => 'ids',
        'no_found_rows'  => true,
        'meta_query'     => [
            'relation' => 'AND',
            [
                'key'     => $parent_meta_key,
                'value'   => '"' . (int) $parent_post_id . '"',
                'compare' => 'LIKE',
            ],
            [
                'relation' => 'OR',
                [
                    'key'     => 'ws_jx_citation_official_name',
                    'value'   => $case_name,
                    'compare' => '=',
                ],
                [
                    'key'     => 'ws_jx_citation_common_name',
                    'value'   => $case_name,
                    'compare' => '=',
                ],
            ],
        ],
    ] );

    return ! empty( $existing ) ? (int) $existing[0] : 0;
}

/**
 * Finds an existing jx-citation by hidden ingest key.
 */
function ws_ingest_find_citation_by_key( string $citation_key ): int {
    if ( $citation_key === '' ) {
        return 0;
    }

    $existing = get_posts( [
        'post_type'      => 'jx-citation',
        'post_status'    => 'any',
        'posts_per_page' => 1,
        'fields'         => 'ids',
        'no_found_rows'  => true,
        'meta_query'     => [ [
            'key'     => '_ws_ingest_citation_key',
            'value'   => $citation_key,
            'compare' => '=',
        ] ],
    ] );

    return ! empty( $existing ) ? (int) $existing[0] : 0;
}

/**
 * Creates citation stubs from record.citations.attached_citations.
 */
function ws_ingest_create_citation_stubs_for_statute( int $statute_post_id, array $record, string $jx_slug, array $meta ): array {
    $created  = [];
    $linked   = [];
    $warnings = [];

    $raw       = $record['citations']['attached_citations'] ?? [];
    $citations = ws_ingest_parse_attached_citations( $raw );
    if ( empty( $citations ) ) {
        return [ 'created' => $created, 'linked' => $linked, 'warnings' => $warnings, 'count' => 0, 'unique_case_count' => 0, 'duplicate_case_rows' => 0 ];
    }

    $case_counts = [];
    foreach ( $citations as $citation_text ) {
        $entry = ws_ingest_parse_citation_entry( (string) $citation_text );
        $case_name = trim( (string) ( $entry['case_name'] ?? '' ) );
        if ( $case_name === '' ) {
            $case_name = trim( (string) $citation_text );
        }
        $case_key = ws_ingest_normalize_case_name( $case_name );
        if ( $case_key === '' ) {
            $case_key = strtolower( trim( preg_replace( '/\s+/', ' ', (string) $case_name ) ) );
        }
        if ( $case_key === '' ) {
            continue;
        }
        $case_counts[ $case_key ] = (int) ( $case_counts[ $case_key ] ?? 0 ) + 1;
    }
    $duplicate_case_rows = 0;
    foreach ( $case_counts as $c ) {
        if ( $c > 1 ) {
            $duplicate_case_rows += ( $c - 1 );
        }
    }

    $jx_term = get_term_by( 'slug', strtolower( $jx_slug ), WS_JURISDICTION_TAXONOMY );

    foreach ( $citations as $citation_text ) {
        $entry        = ws_ingest_parse_citation_entry( $citation_text );
        $case_name    = trim( (string) $entry['case_name'] );
        $impact_seed  = trim( (string) $entry['specific_impact'] );
        $citation_url = trim( (string) $entry['url'] );
        $source_label = trim( (string) $entry['source'] );

        if ( $case_name === '' ) {
            $case_name = trim( $citation_text );
        }

        $citation_key = ws_ingest_build_citation_key( $jx_slug, 'statute', $statute_post_id, $case_name );
        $existing_id  = ws_ingest_find_citation_by_key( $citation_key );
        if ( ! $existing_id ) {
            $existing_id = ws_ingest_find_citation_by_parent_and_case( 'ws_jx_citation_statute_ids', $statute_post_id, $case_name );
        }

        if ( $existing_id ) {
            $raw_statute_ids = get_post_meta( $existing_id, 'ws_jx_citation_statute_ids', true );
            $statute_ids     = is_array( $raw_statute_ids ) ? array_map( 'intval', $raw_statute_ids ) : [];
            if ( ! in_array( $statute_post_id, $statute_ids, true ) ) {
                $statute_ids[] = $statute_post_id;
                update_post_meta( $existing_id, 'ws_jx_citation_statute_ids', array_values( array_unique( $statute_ids ) ) );
            }
            if ( get_post_meta( $existing_id, '_ws_ingest_citation_key', true ) === '' ) {
                update_post_meta( $existing_id, '_ws_ingest_citation_key', $citation_key );
            }
            $linked[] = $existing_id;
            continue;
        }

        $post_id = wp_insert_post( [
            'post_type'   => 'jx-citation',
            'post_status' => 'draft',
            'post_title'  => substr( $case_name, 0, 180 ),
            'post_author' => get_current_user_id(),
        ] );

        if ( is_wp_error( $post_id ) || ! $post_id ) {
            $warnings[] = 'Citation stub create failed for: ' . substr( $case_name, 0, 80 );
            continue;
        }

        update_post_meta( $post_id, 'ws_jx_citation_type', [ ws_ingest_citation_type_from_batch( $meta ) ] );
        update_post_meta( $post_id, 'ws_jx_citation_common_name', sanitize_text_field( $case_name ) );
        update_post_meta( $post_id, 'ws_jx_citation_official_name', sanitize_text_field( $case_name ) );

        if ( $citation_url !== '' ) {
            $safe_url = esc_url_raw( $citation_url );
            if ( $safe_url !== '' ) {
                update_post_meta( $post_id, 'ws_jx_citation_url', $safe_url );
            }
        }

        if ( $impact_seed !== '' ) {
            $impact_text = sanitize_text_field( $impact_seed );
            $summary     = '<p><strong>Starter hint:</strong> ' . esc_html( $impact_text ) . '</p>';
            update_post_meta( $post_id, 'ws_jx_citation_summary', wp_kses_post( $summary ) );
        }

        if ( $source_label !== '' ) {
            update_post_meta( $post_id, '_ws_citation_stub_source_label', sanitize_text_field( $source_label ) );
        }

        update_post_meta( $post_id, 'ws_jx_citation_statute_ids', [ $statute_post_id ] );
        update_post_meta( $post_id, 'ws_attach_flag', 0 );
        update_post_meta( $post_id, 'ws_verification_status', 'unverified' );
        update_post_meta( $post_id, 'ws_needs_review', 0 );
        update_post_meta( $post_id, 'ws_auto_source_method', sanitize_text_field( $meta['source_method'] ?? 'ai_assisted' ) );
        update_post_meta( $post_id, 'ws_auto_source_name', sanitize_text_field( $meta['source_name'] ?? '' ) );
        update_post_meta( $post_id, '_ws_ingest_citation_key', $citation_key );
        update_post_meta( $post_id, '_ws_citation_stub', 1 );
        update_post_meta( $post_id, '_ws_citation_stub_source', 'ingest.attached_citations' );

        if ( $jx_term && ! is_wp_error( $jx_term ) ) {
            wp_set_object_terms( $post_id, [ (int) $jx_term->term_id ], WS_JURISDICTION_TAXONOMY );
        }

        $created[] = (int) $post_id;
    }

    return [
        'created'  => array_values( array_unique( $created ) ),
        'linked'   => array_values( array_unique( $linked ) ),
        'warnings' => $warnings,
        'count'    => count( $citations ),
        'unique_case_count' => count( $case_counts ),
        'duplicate_case_rows' => $duplicate_case_rows,
    ];
}

/**
 * Creates citation stubs from record.citations.attached_citations for common-law records.
 */
function ws_ingest_create_citation_stubs_for_common_law( int $common_law_post_id, array $record, string $jx_slug, array $meta ): array {
    $created  = [];
    $linked   = [];
    $warnings = [];

    $raw       = $record['citations']['attached_citations'] ?? [];
    $citations = ws_ingest_parse_attached_citations( $raw );
    if ( empty( $citations ) ) {
        return [ 'created' => $created, 'linked' => $linked, 'warnings' => $warnings, 'count' => 0, 'unique_case_count' => 0, 'duplicate_case_rows' => 0 ];
    }

    $case_counts = [];
    foreach ( $citations as $citation_text ) {
        $entry = ws_ingest_parse_citation_entry( (string) $citation_text );
        $case_name = trim( (string) ( $entry['case_name'] ?? '' ) );
        if ( $case_name === '' ) {
            $case_name = trim( (string) $citation_text );
        }
        $case_key = ws_ingest_normalize_case_name( $case_name );
        if ( $case_key === '' ) {
            $case_key = strtolower( trim( preg_replace( '/\s+/', ' ', (string) $case_name ) ) );
        }
        if ( $case_key === '' ) {
            continue;
        }
        $case_counts[ $case_key ] = (int) ( $case_counts[ $case_key ] ?? 0 ) + 1;
    }
    $duplicate_case_rows = 0;
    foreach ( $case_counts as $c ) {
        if ( $c > 1 ) {
            $duplicate_case_rows += ( $c - 1 );
        }
    }

    $jx_term = get_term_by( 'slug', strtolower( $jx_slug ), WS_JURISDICTION_TAXONOMY );

    foreach ( $citations as $citation_text ) {
        $entry        = ws_ingest_parse_citation_entry( $citation_text );
        $case_name    = trim( (string) $entry['case_name'] );
        $impact_seed  = trim( (string) $entry['specific_impact'] );
        $citation_url = trim( (string) $entry['url'] );
        $source_label = trim( (string) $entry['source'] );

        if ( $case_name === '' ) {
            $case_name = trim( $citation_text );
        }

        $citation_key = ws_ingest_build_citation_key( $jx_slug, 'common-law', $common_law_post_id, $case_name );
        $existing_id  = ws_ingest_find_citation_by_key( $citation_key );
        if ( ! $existing_id ) {
            $existing_id = ws_ingest_find_citation_by_parent_and_case( 'ws_jx_citation_common_law_ids', $common_law_post_id, $case_name );
        }

        if ( $existing_id ) {
            $raw_common_law_ids = get_post_meta( $existing_id, 'ws_jx_citation_common_law_ids', true );
            $common_law_ids     = is_array( $raw_common_law_ids ) ? array_map( 'intval', $raw_common_law_ids ) : [];
            if ( ! in_array( $common_law_post_id, $common_law_ids, true ) ) {
                $common_law_ids[] = $common_law_post_id;
                update_post_meta( $existing_id, 'ws_jx_citation_common_law_ids', array_values( array_unique( $common_law_ids ) ) );
            }
            if ( get_post_meta( $existing_id, '_ws_ingest_citation_key', true ) === '' ) {
                update_post_meta( $existing_id, '_ws_ingest_citation_key', $citation_key );
            }
            $linked[] = $existing_id;
            continue;
        }

        $post_id = wp_insert_post( [
            'post_type'   => 'jx-citation',
            'post_status' => 'draft',
            'post_title'  => substr( $case_name, 0, 180 ),
            'post_author' => get_current_user_id(),
        ] );

        if ( is_wp_error( $post_id ) || ! $post_id ) {
            $warnings[] = 'Citation stub create failed for: ' . substr( $case_name, 0, 80 );
            continue;
        }

        // Common-law attached citations are case law by definition.
        update_post_meta( $post_id, 'ws_jx_citation_type', [ 'case_law' ] );
        update_post_meta( $post_id, 'ws_jx_citation_common_name', sanitize_text_field( $case_name ) );
        update_post_meta( $post_id, 'ws_jx_citation_official_name', sanitize_text_field( $case_name ) );

        if ( $citation_url !== '' ) {
            $safe_url = esc_url_raw( $citation_url );
            if ( $safe_url !== '' ) {
                update_post_meta( $post_id, 'ws_jx_citation_url', $safe_url );
            }
        }

        if ( $impact_seed !== '' ) {
            $impact_text = sanitize_text_field( $impact_seed );
            $summary     = '<p><strong>Starter hint:</strong> ' . esc_html( $impact_text ) . '</p>';
            update_post_meta( $post_id, 'ws_jx_citation_summary', wp_kses_post( $summary ) );
        }

        if ( $source_label !== '' ) {
            update_post_meta( $post_id, '_ws_citation_stub_source_label', sanitize_text_field( $source_label ) );
        }

        update_post_meta( $post_id, 'ws_jx_citation_common_law_ids', [ $common_law_post_id ] );
        update_post_meta( $post_id, 'ws_attach_flag', 0 );
        update_post_meta( $post_id, 'ws_verification_status', 'unverified' );
        update_post_meta( $post_id, 'ws_needs_review', 0 );
        update_post_meta( $post_id, 'ws_auto_source_method', sanitize_text_field( $meta['source_method'] ?? 'ai_assisted' ) );
        update_post_meta( $post_id, 'ws_auto_source_name', sanitize_text_field( $meta['source_name'] ?? '' ) );
        update_post_meta( $post_id, '_ws_ingest_citation_key', $citation_key );
        update_post_meta( $post_id, '_ws_citation_stub', 1 );
        update_post_meta( $post_id, '_ws_citation_stub_source', 'ingest.attached_citations' );

        if ( $jx_term && ! is_wp_error( $jx_term ) ) {
            wp_set_object_terms( $post_id, [ (int) $jx_term->term_id ], WS_JURISDICTION_TAXONOMY );
        }

        $created[] = (int) $post_id;
    }

    return [
        'created'  => array_values( array_unique( $created ) ),
        'linked'   => array_values( array_unique( $linked ) ),
        'warnings' => $warnings,
        'count'    => count( $citations ),
        'unique_case_count' => count( $case_counts ),
        'duplicate_case_rows' => $duplicate_case_rows,
    ];
}


// ── Post title builder ────────────────────────────────────────────────────────

function ws_ingest_build_post_title( array $record, string $jx_slug ): string {
    $jx      = strtoupper( $jx_slug );
    $sid     = $record['statute_id']    ?? '';
    $name    = $record['official_name'] ?? '';
    $common  = $record['common_name']   ?? '';

    if ( $common ) {
        return trim( "$jx — $name ($common)" );
    }
    return trim( "$jx — $name" );
}

function ws_ingest_build_common_law_post_title( array $record, string $jx_slug ): string {
    $jx      = strtoupper( $jx_slug );
    $name    = (string) ( $record['doctrine_name'] ?? '' );
    $common  = (string) ( $record['common_name'] ?? '' );

    if ( $common !== '' ) {
        return trim( "$jx — $name ($common)" );
    }
    return trim( "$jx — $name" );
}


// ── Core record processor ─────────────────────────────────────────────────────

/**
 * Processes a single statute record.
 * Creates a WP post, stamps source fields, maps all JSON fields to ACF meta,
 * assigns taxonomy terms, and derives companion boolean fields.
 *
 * @return array [ 'success' => bool, 'post_id' => int|null, 'log' => string[], 'warnings' => string[] ]
 */
function ws_ingest_process_statute_record( array $record, array $meta, array $blacklist ): array {
    $result = [
        'success'  => false,
        'post_id'  => null,
        'log'      => [],
        'warnings' => [],
        'citation_stub_created' => 0,
        'agency_stub_created'   => 0,
        'agency_breadcrumbs' => [
            'labels'      => [],
            'matched_ids' => [],
            'created_ids' => [],
        ],
    ];

    $sid = $record['statute_id'] ?? 'UNKNOWN';

    // ── Step 1: Check for duplicate ──────────────────────────────────────
    $jx_slug     = strtolower( (string) ( $meta['jurisdiction_id'] ?? '' ) );
    $record_key  = $jx_slug && $sid !== 'UNKNOWN' ? strtolower( $jx_slug . '|' . $sid ) : '';
    $duplicates  = [];

    if ( $record_key ) {
        $duplicates = get_posts( [
            'post_type'      => 'jx-statute',
            'post_status'    => 'any',
            'meta_query'     => [ [
                'key'     => '_ws_ingest_record_key',
                'value'   => $record_key,
                'compare' => '=',
            ] ],
            'posts_per_page' => 1,
            'fields'         => 'ids',
            'no_found_rows'  => true,
        ] );
    }

    if ( ! empty( $duplicates ) ) {
        $result['warnings'][] = "$sid: duplicate detected (post #{$duplicates[0]}) — skipped.";
        return $result;
    }

    // ── Step 2: Create post ──────────────────────────────────────────────
    $post_id = wp_insert_post( [
        'post_type'   => 'jx-statute',
        'post_status' => 'draft',
        'post_title'  => ws_ingest_build_post_title( $record, $jx_slug ),
        'post_author' => get_current_user_id(),
    ] );

    if ( is_wp_error( $post_id ) ) {
        $result['warnings'][] = "$sid: wp_insert_post failed — " . $post_id->get_error_message();
        return $result;
    }

    $result['post_id'] = $post_id;

    // ── Step 3: Source stamps ────────────────────────────────────────────
    update_post_meta( $post_id, 'ws_auto_source_method', sanitize_text_field( $meta['source_method'] ?? 'ai_assisted' ) );
    update_post_meta( $post_id, 'ws_auto_source_name',   sanitize_text_field( $meta['source_name']   ?? '' ) );
    update_post_meta( $post_id, 'ws_verification_status', 'unverified' );
    update_post_meta( $post_id, 'ws_needs_review',        0 );

    // Source chain — full provenance record of all contributing models.
    // Populated by NotebookLM from its input files. Stored as hidden JSON string.
    if ( ! empty( $meta['source_chain'] ) && is_array( $meta['source_chain'] ) ) {
        update_post_meta( $post_id, '_ws_auto_source_chain', wp_json_encode( $meta['source_chain'] ) );
    }

    // ── Step 4: Assign ws_jurisdiction taxonomy term ──────────────────────
    if ( $jx_slug ) {
        $term = get_term_by( 'slug', $jx_slug, WS_JURISDICTION_TAXONOMY );
        if ( $term && ! is_wp_error( $term ) ) {
            wp_set_object_terms( $post_id, $term->term_id, WS_JURISDICTION_TAXONOMY );
            $result['log'][] = "jurisdiction: assigned '{$jx_slug}'";
        } else {
            $result['warnings'][] = "$sid: jurisdiction term '{$jx_slug}' not found in ws_jurisdiction taxonomy.";
        }
    }

    if ( $record_key ) {
        update_post_meta( $post_id, '_ws_ingest_record_key', $record_key );
    }

    if ( $sid !== '' && $sid !== 'UNKNOWN' ) {
        // Canonical hidden key used by prompt exclusions.
        update_post_meta( $post_id, '_ws_jx_statute_id', sanitize_text_field( $sid ) );
        delete_post_meta( $post_id, '_ws_jx_statute_id_missing' );
    }

    // ── Step 5: Field map ────────────────────────────────────────────────
    $field_map      = ws_ingest_statute_field_map_v2();
    $tax_removals   = [];
    $omitted_fields = [];

    foreach ( $field_map as $json_path => $field_def ) {
        $meta_key  = $field_def[0];
        $type      = $field_def[1];
        $taxonomy  = $field_def[2] ?? null;

        if ( $type === 'omit' || $meta_key === null ) {
            // Log omitted fields that have non-empty values (for run report)
            $val = ws_ingest_get_value( $record, $json_path );
            if ( $val !== null && $val !== '' && $val !== [] ) {
                $omitted_fields[ $json_path ] = $val;
            }
            continue;
        }

        $value = ws_ingest_get_value( $record, $json_path );
        if ( $value === null ) continue;

        switch ( $type ) {
            case 'text':
                if ( $value !== '' ) {
                    update_post_meta( $post_id, $meta_key, sanitize_text_field( $value ) );
                }
                break;

            case 'textarea':
                if ( $value !== '' ) {
                    update_post_meta( $post_id, $meta_key, sanitize_textarea_field( $value ) );
                }
                break;

            case 'url':
                if ( $value !== '' ) {
                    update_post_meta( $post_id, $meta_key, esc_url_raw( $value ) );
                }
                break;

            case 'bool':
                update_post_meta( $post_id, $meta_key, ws_ingest_parse_boolish_value( $value, $sid, $json_path, $result['warnings'] ) );
                break;

            case 'number':
                if ( $value !== '' && $value !== null ) {
                    update_post_meta( $post_id, $meta_key, (float) $value );
                }
                break;

            case 'array':
                if ( is_array( $value ) ) {
                    $clean = array_values( array_filter( array_map( 'sanitize_text_field', $value ), fn( $v ) => $v !== '' ) );
                    if ( ! empty( $clean ) ) {
                        update_post_meta( $post_id, $meta_key, $clean );
                    }
                }
                break;

            case 'tax':
                if ( ! is_array( $value ) || empty( $value ) ) break;
                $validated = ws_ingest_validate_taxonomy_array( $value, $taxonomy, $blacklist, $record );
                if ( ! empty( $validated['removed'] ) ) {
                    foreach ( $validated['removed'] as $slug => $reason ) {
                        $tax_removals[] = "$sid [$taxonomy]: removed '$slug' ($reason)";
                    }
                }
                if ( ! empty( $validated['valid'] ) ) {
                    // Convert slugs to term IDs
                    $term_ids = [];
                    foreach ( $validated['valid'] as $slug ) {
                        $term = get_term_by( 'slug', $slug, $taxonomy );
                        if ( $term && ! is_wp_error( $term ) ) {
                            $term_ids[] = $term->term_id;
                        }
                    }
                    if ( $term_ids ) {
                        wp_set_object_terms( $post_id, $term_ids, $taxonomy );
                    }
                }
                break;
        }
    }

    // ── Step 6: Derived boolean companions ───────────────────────────────

    // tolling_has_notes: 1 when tolling_notes is present
    $tolling = ws_ingest_get_value( $record, 'statute_of_limitations.tolling_notes' );
    if ( $tolling ) {
        update_post_meta( $post_id, 'ws_jx_statute_tolling_has_notes', 1 );
    }

    // rebuttable_has_presumption: 1 when rebuttable_presumption is present
    $rebuttable = ws_ingest_get_value( $record, 'burden_of_proof.rebuttable_presumption' );
    if ( $rebuttable ) {
        update_post_meta( $post_id, 'ws_jx_statute_rebuttable_has_presumption', 1 );
    }

    // bop_has_details: 1 when burden_of_proof_details is present
    $bop_details = ws_ingest_get_value( $record, 'burden_of_proof.burden_of_proof_details' );
    if ( $bop_details ) {
        update_post_meta( $post_id, 'ws_jx_statute_bop_has_details', 1 );
    }

    // citations.attached_citations: create draft jx-citation stubs and link to this statute.
    $citation_stub_result = ws_ingest_create_citation_stubs_for_statute( $post_id, $record, $jx_slug, $meta );
    $citation_ids = array_values( array_unique( array_merge(
        $citation_stub_result['created'] ?? [],
        $citation_stub_result['linked'] ?? []
    ) ) );

    if ( ! empty( $citation_ids ) ) {
        update_post_meta( $post_id, 'ws_jx_statute_citation_ids', array_map( 'intval', $citation_ids ) );
        $result['log'][] = "$sid: attached " . count( $citation_ids ) . ' citation ID(s) on statute record';
    }

    if ( ! empty( $citation_stub_result['created'] ) ) {
        $result['citation_stub_created'] = count( array_unique( array_map( 'intval', (array) $citation_stub_result['created'] ) ) );
        $result['log'][] = "$sid: created " . count( $citation_stub_result['created'] ) . ' citation stub record(s) from citations.attached_citations';
    }
    if ( ! empty( $citation_stub_result['linked'] ) ) {
        $result['log'][] = "$sid: linked " . count( $citation_stub_result['linked'] ) . ' existing citation record(s) from citations.attached_citations';
    }
    if ( ! empty( $citation_stub_result['warnings'] ) ) {
        foreach ( $citation_stub_result['warnings'] as $warning ) {
            $result['warnings'][] = "$sid: $warning";
        }
    }

    if ( (int) ( $citation_stub_result['count'] ?? 0 ) > 0 ) {
        $rows = (int) ( $citation_stub_result['count'] ?? 0 );
        $unique_cases = (int) ( $citation_stub_result['unique_case_count'] ?? 0 );
        $dupes = (int) ( $citation_stub_result['duplicate_case_rows'] ?? 0 );
        $result['log'][] = "$sid: citation rows={$rows}, unique_case_keys={$unique_cases}, created=" . count( (array) ( $citation_stub_result['created'] ?? [] ) ) . ', linked=' . count( (array) ( $citation_stub_result['linked'] ?? [] ) );
        if ( $dupes > 0 ) {
            $result['warnings'][] = "$sid: attached_citations contains {$dupes} duplicate CASE row(s); dedupe collapsed rows by CASE identity before/create-link pass.";
        }
    }

    // enforcement.primary_agency: preserve text channel and attempt agency linking.
    $primary_agency = (string) ws_ingest_get_value( $record, 'enforcement.primary_agency' );
    if ( trim( $primary_agency ) !== '' ) {
        $agency_labels = ws_ingest_extract_agency_labels( $primary_agency );
        $target_jx     = ( $jx_slug === 'us' ) ? 'us' : $jx_slug;
        $agency_match  = ws_ingest_match_agencies_for_jx_detailed( $agency_labels, $target_jx );
        $matched_ids   = (array) ( $agency_match['matched_ids'] ?? [] );
        $match_reasons = (array) ( $agency_match['reasons'] ?? [] );
        $created_stub_ids = [];

        if ( empty( $matched_ids ) ) {
            $resolved_ids = [];
            $seen_keys    = [];
            foreach ( $agency_labels as $label ) {
                $prepared_label = ws_ingest_prepare_agency_stub_label( (string) $label, $target_jx );
                if ( $prepared_label === '' ) {
                    $prepared_label = trim( (string) $label );
                }
                $prepared_code = ws_ingest_build_agency_stub_code( $prepared_label );
                $dedupe_key = $target_jx . '|' . $prepared_code;
                if ( $prepared_code !== '' && isset( $seen_keys[ $dedupe_key ] ) ) {
                    continue;
                }
                $seen_keys[ $dedupe_key ] = true;

                $created_now = false;
                $stub_id = ws_ingest_create_agency_stub( (string) $label, $target_jx, $created_now );
                if ( $stub_id ) {
                    $resolved_ids[] = (int) $stub_id;
                    if ( $created_now ) {
                        $created_stub_ids[] = (int) $stub_id;
                    }
                }
            }
            $matched_ids = array_values( array_unique( $resolved_ids ) );
            if ( ! empty( $matched_ids ) ) {
                $created_count = count( array_values( array_unique( $created_stub_ids ) ) );
                $result['agency_stub_created'] = $created_count;
                if ( $created_count > 0 ) {
                    $result['log'][] = "$sid: created " . $created_count . " agency stub record(s) from enforcement.primary_agency";
                }
            }
        }

        if ( ! empty( $matched_ids ) ) {
            $agency_key = ( $jx_slug === 'us' ) ? 'ws_jx_statute_federal_agencies' : 'ws_jx_statute_local_agencies';
            update_post_meta( $post_id, $agency_key, $matched_ids );
            $result['log'][] = "$sid: linked " . count( $matched_ids ) . " agency record(s) from enforcement.primary_agency";
        } else {
            $result['warnings'][] = "$sid: no ws-agency matches found and no stub was created for enforcement.primary_agency text.";
        }

        $result['agency_breadcrumbs'] = [
            'labels'      => array_values( $agency_labels ),
            'matched_ids' => array_values( array_map( 'intval', $matched_ids ) ),
            'created_ids' => array_values( array_map( 'intval', $created_stub_ids ) ),
            'match_reasons' => array_values( array_map( 'strval', $match_reasons ) ),
        ];
    }

    // ── Step 7: Log tax removals ─────────────────────────────────────────
    foreach ( $tax_removals as $removal ) {
        $result['warnings'][] = $removal;
    }

    // ── Step 8: Log omitted fields with values ───────────────────────────
    foreach ( $omitted_fields as $path => $val ) {
        $display = is_array( $val ) ? implode( ', ', $val ) : $val;
        $result['log'][] = "omitted (no ACF field): $path = " . substr( $display, 0, 80 );
    }

    $result['success'] = true;
    $result['log'][]   = "$sid: created as post #$post_id (draft, unverified)";

    return $result;
}

/**
 * Processes a single common-law record.
 */
function ws_ingest_process_common_law_record( array $record, array $meta, array $blacklist ): array {
    $result = [
        'success'  => false,
        'post_id'  => null,
        'log'      => [],
        'warnings' => [],
        'citation_stub_created' => 0,
        'agency_stub_created'   => 0,
        'agency_breadcrumbs' => [
            'labels'      => [],
            'matched_ids' => [],
            'created_ids' => [],
        ],
    ];

    $did = $record['doctrine_id'] ?? 'UNKNOWN';

    $jx_slug     = strtolower( (string) ( $meta['jurisdiction_id'] ?? '' ) );
    $record_key  = $jx_slug && $did !== 'UNKNOWN' ? strtolower( $jx_slug . '|' . $did ) : '';
    $duplicates  = [];

    if ( $record_key ) {
        $duplicates = get_posts( [
            'post_type'      => 'jx-common-law',
            'post_status'    => 'any',
            'meta_query'     => [ [
                'key'     => '_ws_ingest_record_key',
                'value'   => $record_key,
                'compare' => '=',
            ] ],
            'posts_per_page' => 1,
            'fields'         => 'ids',
            'no_found_rows'  => true,
        ] );
    }

    if ( ! empty( $duplicates ) ) {
        $result['warnings'][] = "$did: duplicate detected (post #{$duplicates[0]}) — skipped.";
        return $result;
    }

    $post_id = wp_insert_post( [
        'post_type'   => 'jx-common-law',
        'post_status' => 'draft',
        'post_title'  => ws_ingest_build_common_law_post_title( $record, $jx_slug ),
        'post_author' => get_current_user_id(),
    ] );

    if ( is_wp_error( $post_id ) ) {
        $result['warnings'][] = "$did: wp_insert_post failed — " . $post_id->get_error_message();
        return $result;
    }

    $result['post_id'] = $post_id;

    update_post_meta( $post_id, 'ws_auto_source_method', sanitize_text_field( $meta['source_method'] ?? 'ai_assisted' ) );
    update_post_meta( $post_id, 'ws_auto_source_name',   sanitize_text_field( $meta['source_name']   ?? '' ) );
    update_post_meta( $post_id, 'ws_verification_status', 'unverified' );
    update_post_meta( $post_id, 'ws_needs_review',        0 );

    if ( ! empty( $meta['source_chain'] ) && is_array( $meta['source_chain'] ) ) {
        update_post_meta( $post_id, '_ws_auto_source_chain', wp_json_encode( $meta['source_chain'] ) );
    }

    if ( $jx_slug ) {
        $term = get_term_by( 'slug', $jx_slug, WS_JURISDICTION_TAXONOMY );
        if ( $term && ! is_wp_error( $term ) ) {
            wp_set_object_terms( $post_id, $term->term_id, WS_JURISDICTION_TAXONOMY );
            $result['log'][] = "jurisdiction: assigned '{$jx_slug}'";
        } else {
            $result['warnings'][] = "$did: jurisdiction term '{$jx_slug}' not found in ws_jurisdiction taxonomy.";
        }
    }

    if ( $record_key ) {
        update_post_meta( $post_id, '_ws_ingest_record_key', $record_key );
    }
    if ( $did !== '' && $did !== 'UNKNOWN' ) {
        update_post_meta( $post_id, '_ws_cl_doctrine_id', sanitize_text_field( $did ) );
    }

    $field_map      = ws_ingest_common_law_field_map_v2();
    $tax_removals   = [];
    $omitted_fields = [];

    foreach ( $field_map as $json_path => $field_def ) {
        $meta_key  = $field_def[0];
        $type      = $field_def[1];
        $taxonomy  = $field_def[2] ?? null;

        if ( $type === 'omit' || $meta_key === null ) {
            $val = ws_ingest_get_value( $record, $json_path );
            if ( $val !== null && $val !== '' && $val !== [] ) {
                $omitted_fields[ $json_path ] = $val;
            }
            continue;
        }
        if ( $type === 'seed' ) {
            continue;
        }

        $value = ws_ingest_get_value( $record, $json_path );
        if ( $value === null ) {
            continue;
        }

        // Tri-choice bools are handled in a dedicated enforcement pass below
        // so warnings are emitted once and review state is consistent.
        if ( $type === 'bool' && in_array( $json_path, [ 'has_secure_channel', 'anonymous_pre_consult_possible', 'has_attorneys', 'income_eligibility_required' ], true ) ) {
            continue;
        }

        switch ( $type ) {
            case 'text':
                if ( $meta_key !== null && $value !== '' ) {
                    if ( $json_path === 'verified_date_url' ) {
                        $normalized_date = ws_ingest_normalize_ymd_date( $value );
                        if ( $normalized_date !== '' ) {
                            update_post_meta( $post_id, $meta_key, $normalized_date );
                        }
                        break;
                    }
                    update_post_meta( $post_id, $meta_key, sanitize_text_field( (string) $value ) );
                }
                break;

            case 'textarea':
                if ( $meta_key !== null && $value !== '' ) {
                    update_post_meta( $post_id, $meta_key, sanitize_textarea_field( (string) $value ) );
                }
                break;

            case 'url':
                if ( $meta_key !== null && $value !== '' ) {
                    $safe_url = esc_url_raw( (string) $value );
                    if ( $safe_url !== '' ) {
                        update_post_meta( $post_id, $meta_key, $safe_url );
                    }
                }
                break;

            case 'repeater_phone':
                if ( $meta_key !== null ) {
                    $rows = ws_ingest_normalize_phone_rows( $value );
                    if ( function_exists( 'update_field' ) ) {
                        update_field( $meta_key, $rows, $post_id );
                    } else {
                        update_post_meta( $post_id, $meta_key, $rows );
                    }
                }
                break;

            case 'repeater_email':
                if ( $meta_key !== null ) {
                    $rows = ws_ingest_normalize_email_rows( $value );
                    if ( function_exists( 'update_field' ) ) {
                        update_field( $meta_key, $rows, $post_id );
                    } else {
                        update_post_meta( $post_id, $meta_key, $rows );
                    }
                }
                break;

            case 'bool':
                if ( $meta_key !== null ) {
                    update_post_meta( $post_id, $meta_key, ws_ingest_parse_boolish_value( $value, $did, $json_path, $result['warnings'] ) );
                }
                break;

            case 'number':
                if ( $meta_key !== null && $value !== '' && $value !== null ) {
                    if ( $json_path === 'whistleblower_scope' ) {
                        $scope = max( 0, min( 3, (int) $value ) );
                        if ( (int) $value !== $scope ) {
                            $result['warnings'][] = "{$org_name}: whistleblower_scope '{$value}' out of range; clamped to {$scope}.";
                        }
                        update_post_meta( $post_id, $meta_key, $scope );
                    } else {
                        update_post_meta( $post_id, $meta_key, (float) $value );
                    }
                }
                break;

            case 'tax':
                if ( ! is_array( $value ) || empty( $value ) ) {
                    break;
                }

                $validated = ws_ingest_validate_taxonomy_array( $value, (string) $taxonomy, $blacklist, $record );
                if ( ! empty( $validated['removed'] ) ) {
                    foreach ( $validated['removed'] as $slug => $reason ) {
                        $tax_removals[] = "{$org_name} [{$taxonomy}]: removed '{$slug}' ({$reason})";
                    }
                }
                if ( empty( $validated['valid'] ) ) {
                    break;
                }

                $term_ids = [];
                foreach ( $validated['valid'] as $slug ) {
                    $term = get_term_by( 'slug', $slug, (string) $taxonomy );
                    if ( $term && ! is_wp_error( $term ) ) {
                        $term_ids[] = (int) $term->term_id;
                    }
                }
                if ( empty( $term_ids ) ) {
                    break;
                }

                if ( in_array( (string) $taxonomy, $single_tax_types, true ) && count( $term_ids ) > 1 ) {
                    $result['warnings'][] = "{$org_name} [{$taxonomy}]: multiple slugs provided for single-select taxonomy; first term retained.";
                    $term_ids = [ (int) $term_ids[0] ];
                }

                wp_set_object_terms( $post_id, $term_ids, (string) $taxonomy );
                break;
        }
    }

    // Tri-choice enforcement (non-blocking):
    // Missing or "unclear" values are coerced to "no" (0) and flagged for human review.
    $tri_choice_fields = [
        'has_secure_channel'            => 'ws_aorg_has_secure_channel',
        'anonymous_pre_consult_possible'=> 'ws_aorg_accepts_anonymous',
        'has_attorneys'                 => 'ws_aorg_licensed_attorneys',
        'income_eligibility_required'   => 'ws_aorg_income_limit',
    ];
    $tri_values = [];
    foreach ( $tri_choice_fields as $json_key => $meta_key ) {
        $raw_val = ws_ingest_get_value( $record, $json_key );
        $missing = ( $raw_val === null ) || ( is_string( $raw_val ) && trim( $raw_val ) === '' );

        if ( $missing ) {
            update_post_meta( $post_id, $meta_key, 0 );
            $tri_values[ $json_key ] = 0;
            $needs_review = true;
            $result['warnings'][] = "{$org_name}: {$json_key} missing/empty; coerced to 'no' (0). Meat-bag review required.";
            continue;
        }

        $parsed = ws_ingest_parse_boolish_value( $raw_val, $org_name, $json_key, $result['warnings'] );
        update_post_meta( $post_id, $meta_key, $parsed );
        $tri_values[ $json_key ] = $parsed;

        if ( is_string( $raw_val ) && strtolower( trim( $raw_val ) ) === 'unclear' ) {
            $needs_review = true;
        }
    }

    // Non-blocking quality gates: surface "meat-bag-in-the-loop" failures.
    $required_presence = [
        'organization_name',
        'official_homepage_url',
        'general_description',
        'homepage_url_status',
        'whistleblower_scope',
        'whistleblower_note',
    ];
    foreach ( $required_presence as $required_key ) {
        $raw_val = ws_ingest_get_value( $record, $required_key );
        $is_missing = ( $raw_val === null );
        if ( is_string( $raw_val ) && trim( $raw_val ) === '' ) {
            $is_missing = true;
        }
        if ( $is_missing ) {
            $result['warnings'][] = "{$org_name}: required key {$required_key} missing/empty (non-blocking; human review required).";
        }
    }

    $required_array_keys = [ 'languages_supported', 'case_stages', 'disclosure_targets' ];
    foreach ( $required_array_keys as $arr_key ) {
        $arr_val = ws_ingest_get_value( $record, $arr_key );
        if ( ! is_array( $arr_val ) ) {
            $result['warnings'][] = "{$org_name}: required key {$arr_key} must be an array (non-blocking; human review required).";
            continue;
        }
        if ( $arr_key === 'languages_supported' && count( $arr_val ) === 0 ) {
            $result['warnings'][] = "{$org_name}: languages_supported is empty (non-blocking; human review required).";
        }
        if ( $arr_key === 'case_stages' && count( $arr_val ) === 0 ) {
            $result['warnings'][] = "{$org_name}: case_stages is empty (non-blocking; human review required).";
        }
    }

    $homepage_status = strtolower( trim( (string) ws_ingest_get_value( $record, 'homepage_url_status' ) ) );
    if ( $homepage_status !== '' && ! in_array( $homepage_status, [ 'verified', 'redirects', 'unverified' ], true ) ) {
        $result['warnings'][] = "{$org_name}: homepage_url_status '{$homepage_status}' invalid (expected verified|redirects|unverified).";
    }

    $has_secure_channel = (int) ( $tri_values['has_secure_channel'] ?? 0 );
    $secure_url  = trim( (string) ws_ingest_get_value( $record, 'secure_contact_url' ) );
    $secure_tool = trim( (string) ws_ingest_get_value( $record, 'secure_contact_tool' ) );
    $secure_tool_other = trim( (string) ws_ingest_get_value( $record, 'secure_contact_tool_other' ) );
    $allowed_secure_tools = defined( 'WS_SCHEMA_SECURE_TOOL' ) && is_array( WS_SCHEMA_SECURE_TOOL )
        ? WS_SCHEMA_SECURE_TOOL
        : [ 'SecureDrop', 'Signal', 'ProtonMail', 'Tutanota', 'Wire', 'Keybase', 'other' ];

    if ( $has_secure_channel === 1 ) {
        if ( $secure_url === '' ) {
            $result['warnings'][] = "{$org_name}: has_secure_channel is true but secure_contact_url is missing (non-blocking; human review required).";
        }
        if ( $secure_tool === '' ) {
            $result['warnings'][] = "{$org_name}: has_secure_channel is true but secure_contact_tool is missing (non-blocking; human review required).";
        } elseif ( ! in_array( $secure_tool, $allowed_secure_tools, true ) ) {
            $result['warnings'][] = "{$org_name}: secure_contact_tool '{$secure_tool}' is not an allowed canonical value.";
        }

        if ( $secure_tool === 'other' && $secure_tool_other === '' ) {
            $result['warnings'][] = "{$org_name}: secure_contact_tool is 'other' but secure_contact_tool_other is missing.";
        }

        if ( $secure_tool !== 'other' && $secure_tool_other !== '' ) {
            $result['warnings'][] = "{$org_name}: secure_contact_tool_other provided but secure_contact_tool is not 'other'.";
        }
    } else {
        if ( $secure_url !== '' || $secure_tool !== '' || $secure_tool_other !== '' ) {
            $result['warnings'][] = "{$org_name}: secure_contact_url/secure_contact_tool/secure_contact_tool_other provided while has_secure_channel is false.";
        }
    }

    $income_required = (int) ( $tri_values['income_eligibility_required'] ?? 0 );
    $income_eligibility_details = trim( (string) ws_ingest_get_value( $record, 'income_eligibility_details' ) );
    if ( $income_required === 1 && $income_eligibility_details === '' ) {
        $result['warnings'][] = "{$org_name}: income_eligibility_required is yes but income_eligibility_details is missing.";
    }

    $case_stages_raw = ws_ingest_get_value( $record, 'case_stages' );
    $case_stage_details = trim( (string) ws_ingest_get_value( $record, 'case_stage_details' ) );
    $case_stages_has_other = false;
    if ( is_array( $case_stages_raw ) ) {
        $case_stages_has_other = in_array( 'other', array_map( 'strval', $case_stages_raw ), true );
    }
    if ( $case_stages_has_other && $case_stage_details === '' ) {
        $needs_review = true;
        $result['warnings'][] = "{$org_name}: case_stages includes 'other' but case_stage_details is missing.";
    }
    if ( ! $case_stages_has_other && $case_stage_details !== '' ) {
        $needs_review = true;
        $result['warnings'][] = "{$org_name}: case_stage_details provided but case_stages does not include 'other'.";
    }

    $jurisdiction_exceptions = trim( (string) ws_ingest_get_value( $record, 'jurisdiction_exceptions' ) );
    if ( $jurisdiction_exceptions !== '' ) {
        $needs_review = true;
        $result['warnings'][] = "{$org_name}: jurisdiction_exceptions present; manual jurisdiction scope review required.";
    }

    $meta_nationwide_only = false;
    $meta_nationwide_raw = $meta['nationwide_only'] ?? null;
    if ( is_bool( $meta_nationwide_raw ) ) {
        $meta_nationwide_only = $meta_nationwide_raw;
    } elseif ( is_numeric( $meta_nationwide_raw ) ) {
        $meta_nationwide_only = ( (int) $meta_nationwide_raw ) === 1;
    } elseif ( is_string( $meta_nationwide_raw ) ) {
        $meta_nationwide_only = in_array( strtolower( trim( $meta_nationwide_raw ) ), [ '1', 'true', 'yes', 'y' ], true );
    }

    $nationwide_example = trim( (string) ws_ingest_get_value( $record, 'nationwide_example' ) );
    if ( $meta_nationwide_only && $nationwide_example === '' ) {
        $needs_review = true;
        $result['warnings'][] = "{$org_name}: nationwide_only run but nationwide_example is empty (researcher error; human review required).";
    }

    $scope_value = (int) get_post_meta( $post_id, 'ws_aorg_whistleblower_scope', true );
    $scope_note  = trim( (string) get_post_meta( $post_id, 'ws_aorg_whistleblower_note', true ) );
    if ( $scope_value === 0 && $scope_note === '' ) {
        $result['warnings'][] = "{$org_name}: whistleblower_scope is 0 but whistleblower_note is empty.";
    }

    // Canonical ingest rule: nationwide scope is asserted only when
    // nationwide_example contains evidence text.
    $is_nationwide = ( $nationwide_example !== '' ) ? 1 : 0;
    update_post_meta( $post_id, 'ws_aorg_serves_nationwide', $is_nationwide );
    if ( $is_nationwide === 1 ) {
        update_post_meta( $post_id, 'ws_aorg_limited_scope', 0 );
    }

    foreach ( $tax_removals as $removal ) {
        $result['warnings'][] = $removal;
    }

    foreach ( $omitted_fields as $path => $val ) {
        $display = is_array( $val ) ? implode( ', ', $val ) : (string) $val;
        $result['log'][] = 'omitted (no ACF field): ' . $path . ' = ' . substr( $display, 0, 80 );
    }

    // Seed append blocks (non-meta editorial content) are appended to post_content.
    // Base content remains general_description.
    $seed_append  = ws_ingest_build_assist_org_seed_append( $record );
    $base_content = trim( (string) ( $record['general_description'] ?? '' ) );
    $final_content = $base_content . $seed_append;
    if ( $final_content !== '' ) {
        wp_update_post( [
            'ID'           => (int) $post_id,
            'post_content' => wp_kses_post( $final_content ),
        ] );

        // Assist-org plain English workflow:
        // duplicate assembled seed content into ws_plain_english_wysiwyg.
        update_post_meta( $post_id, 'ws_has_plain_english', 1 );
        update_post_meta( $post_id, 'ws_plain_english_wysiwyg', wp_kses_post( $final_content ) );
    }

    if ( $needs_review ) {
        update_post_meta( $post_id, 'ws_needs_review', 1 );
    }

    $result['success'] = true;
    $result['log'][]   = "{$org_name}: created as post #{$post_id} (draft, unverified)";

    return $result;
}


// ── Run log writer ────────────────────────────────────────────────────────────

/**
 * Writes a persistent run log to wp-content/logs/ws-ingest/ingested/.
 * Filename: [jx]-[record_count]-[record_type]-[YYYYMMDD-HHMMSS]-ingest.txt
 * Collision safety for same-second runs: -01, -02, etc before -ingest.txt.
 * FTP-accessible, .htaccess protected.
 */
function ws_ingest_extract_batch_count_from_filename( string $filename ): int {
    $base = basename( $filename );
    if ( preg_match( '/^[A-Za-z]{2}-(\d+)-/', $base, $m ) ) {
        return (int) $m[1];
    }
    return 0;
}

/**
 * Resolves jurisdiction token for run-log filenames.
 *
 * Priority:
 * 1) summary.jurisdiction
 * 2) summary.jurisdiction_id
 * 3) summary.jx_id
 * 4) filename prefix (e.g., US-8-...)
 */
function ws_ingest_resolve_runlog_jx( array $summary, string $batch_filename = '' ): string {
    $candidates = [
        (string) ( $summary['jurisdiction'] ?? '' ),
        (string) ( $summary['jurisdiction_id'] ?? '' ),
        (string) ( $summary['jx_id'] ?? '' ),
    ];

    foreach ( $candidates as $candidate ) {
        $candidate = strtoupper( trim( $candidate ) );
        if ( preg_match( '/^[A-Z]{2}$/', $candidate ) ) {
            return $candidate;
        }
    }

    $base = basename( $batch_filename );
    if ( preg_match( '/^([A-Za-z]{2})-\d+-/', $base, $m ) ) {
        return strtoupper( $m[1] );
    }

    return 'XX';
}

function ws_ingest_write_run_log( array $result, string $batch_filename = '' ): bool {
    ws_ingest_bootstrap_log_dir();

    $summary = $result['summary'] ?? [];
    $jx      = ws_ingest_resolve_runlog_jx( $summary, $batch_filename );
    $batch_count = ws_ingest_extract_batch_count_from_filename( $batch_filename );
    $record_type = (string) ( $summary['record_type'] ?? 'unknown' );
    $ts      = gmdate( 'Ymd-His' );

    $jx_part = preg_replace( '/[^a-z0-9]+/', '', strtolower( (string) $jx ) );
    if ( $jx_part === '' ) {
        $jx_part = 'xx';
    }

    $count_part = max( 0, (int) $batch_count );
    $type_part  = strtolower( preg_replace( '/[^a-z0-9-]+/', '-', $record_type ) );
    $type_part  = trim( preg_replace( '/-+/', '-', $type_part ), '-' );
    if ( $type_part === '' ) {
        $type_part = 'unknown';
    }

    $base = "{$jx_part}-{$count_part}-{$type_part}-{$ts}";
    $path = WS_INGEST_RUN_LOG_DIR . "{$base}-ingest.txt";
    $n    = 1;
    while ( file_exists( $path ) ) {
        $path = WS_INGEST_RUN_LOG_DIR . "{$base}-" . str_pad( (string) $n, 2, '0', STR_PAD_LEFT ) . "-ingest.txt";
        $n++;
    }

    $lines   = [];
    $lines[] = '================================================';
    $lines[] = 'WS INGEST RUN LOG';
    $lines[] = '================================================';
    $lines[] = 'Run timestamp:    ' . date( 'Y-m-d H:i:s' ) . ' UTC';
    $lines[] = 'Jurisdiction:     ' . $jx;
    $lines[] = 'Source:           ' . ( $summary['source_name']   ?? '' );
    $lines[] = 'Source method:    ' . ( $summary['source_method'] ?? '' );
    $lines[] = 'Batch completed:  ' . ( $summary['batch_completed'] ?? '' );
    $lines[] = '';
    $lines[] = '── SUMMARY ──────────────────────────────────────';
    $lines[] = 'Created:          ' . ( $summary['created']  ?? 0 );
    $lines[] = 'Skipped (dupe):   ' . ( $summary['skipped']  ?? 0 );
    $lines[] = 'Failed:           ' . ( $summary['failed']   ?? 0 );
    $lines[] = 'Citation stubs:   ' . ( $summary['citation_stubs_created'] ?? 0 );
    $lines[] = 'Agency stubs:     ' . ( $summary['agency_stubs_created'] ?? 0 );
    $lines[] = 'Proposed new:     ' . ( $summary['proposed_new']    ?? 0 );
    $lines[] = 'Proposed merged:  ' . ( $summary['proposed_merged'] ?? 0 );
    $lines[] = 'Blacklist size:   ' . ( $summary['blacklist_size']  ?? 0 );
    $lines[] = '';

    // Pre-flight warnings
    $preflight = $result['preflight'] ?? [];
    if ( ! empty( $preflight['warnings'] ) ) {
        $lines[] = '── PREFLIGHT WARNINGS ───────────────────────────';
        foreach ( $preflight['warnings'] as $w ) {
            $lines[] = '  ' . $w;
        }
        $lines[] = '';
    }

    // Per-record detail
    $lines[] = '── RECORD DETAIL ────────────────────────────────';
    foreach ( $result['records'] ?? [] as $rec ) {
        $status  = $rec['success'] ? '✓' : '✗';
        $post    = $rec['post_id'] ? ' [post #' . $rec['post_id'] . ']' : '';
        $lines[] = "{$status} {$rec['record_id']}{$post}";
        foreach ( $rec['warnings'] as $w ) {
            $lines[] = '    ⚠ ' . $w;
        }
        foreach ( $rec['log'] as $l ) {
            $lines[] = '    · ' . $l;
        }
    }

    $lines[] = '';
    $lines[] = '================================================';
    $lines[] = 'END OF LOG';
    $lines[] = '================================================';

    return file_put_contents( $path, implode( "\n", $lines ) . "\n" ) !== false;
}


// ── Append-only ledger logs ───────────────────────────────────────────────────

/**
 * Appends a line to the preflight errors ledger.
 * One entry per failed preflight — filename, timestamp, reasons.
 */
function ws_ingest_log_preflight_failure( string $filename, array $errors ): bool {
    $path   = WS_INGEST_LOG_DIR . 'preflight-errors.log';
    $ts     = date( 'Y-m-d H:i:s' );
    $reason = implode( ' | ', $errors );
    $line   = "[{$ts} UTC]  {$filename}  —  {$reason}" . PHP_EOL;
    return file_put_contents( $path, $line, FILE_APPEND | LOCK_EX ) !== false;
}

/**
 * Appends a line to the imported batches ledger.
 * One entry per successfully processed batch.
 */
function ws_ingest_log_imported_batch( string $filename, array $summary, bool $has_warnings, bool $has_failures ): bool {
    $path    = WS_INGEST_LOG_DIR . 'imported.log';
    $ts      = date( 'Y-m-d H:i:s' );
    $jx      = strtoupper( $summary['jurisdiction'] ?? 'XX' );
    $record_type = strtolower( (string) ( $summary['record_type'] ?? '' ) );
    $created = (int) ( $summary['created']  ?? 0 );
    $skipped = (int) ( $summary['skipped']  ?? 0 );
    $failed  = (int) ( $summary['failed']   ?? 0 );
    $citation_stubs = (int) ( $summary['citation_stubs_created'] ?? 0 );
    $agency_stubs   = (int) ( $summary['agency_stubs_created'] ?? 0 );
    $warnings = $has_warnings ? 'true' : 'false';
    $failures = $has_failures ? 'true' : 'false';

    $line = "[{$ts} UTC]  {$filename}  {$jx}  created:{$created}  skipped:{$skipped}  failed:{$failed}";
    if ( $record_type !== 'assist-org' ) {
        $line .= "  citation_stubs:{$citation_stubs}  agency_stubs:{$agency_stubs}";
    }
    $line .= "  has_warnings:{$warnings}  has_failures:{$failures}" . PHP_EOL;

    return file_put_contents( $path, $line, FILE_APPEND | LOCK_EX ) !== false;
}

/**
 * Appends citation breadcrumbs to citations-breadcrumbs.log.
 * One entry per statute that has attached_citations.
 * Retained as a human review trail even when citation stubs are auto-created.
 */
function ws_ingest_log_citation_breadcrumbs( string $filename, string $jx, string $record_id, array $citations ): bool {
    if ( empty( $citations ) ) return true;

    $path = WS_INGEST_LOG_DIR . 'citations-breadcrumbs.log';
    $ts   = date( 'Y-m-d H:i:s' );

    $lines   = [];
    $lines[] = "[{$ts} UTC]  {$filename}  {$jx}  {$record_id}";
    foreach ( $citations as $cite ) {
        $lines[] = '  ' . $cite;
    }
    $lines[] = '---';
    $lines[] = '';

    return file_put_contents( $path, implode( PHP_EOL, $lines ) . PHP_EOL, FILE_APPEND | LOCK_EX ) !== false;
}

/**
 * Appends agency mapping breadcrumbs to agency-breadcrumbs.log.
 * One entry per record that attempted enforcement.primary_agency mapping.
 */
function ws_ingest_log_agency_breadcrumbs( string $filename, string $jx, string $record_id, array $labels, array $matched_ids, array $created_ids, array $match_reasons = [] ): bool {
    if ( empty( $labels ) ) {
        return true;
    }

    $path = WS_INGEST_LOG_DIR . 'agency-breadcrumbs.log';
    $ts   = date( 'Y-m-d H:i:s' );

    $lines   = [];
    $lines[] = "[{$ts} UTC]  {$filename}  {$jx}  {$record_id}";
    $lines[] = '  labels: ' . implode( ' | ', array_map( 'strval', $labels ) );
    $lines[] = '  matched_ids: ' . ( empty( $matched_ids ) ? 'none' : implode( ', ', array_map( 'intval', $matched_ids ) ) );
    $lines[] = '  match_reasons: ' . ( empty( $match_reasons ) ? 'none' : implode( ' | ', array_map( 'strval', $match_reasons ) ) );
    $lines[] = '  created_stub_ids: ' . ( empty( $created_ids ) ? 'none' : implode( ', ', array_map( 'intval', $created_ids ) ) );
    $lines[] = '---';
    $lines[] = '';

    return file_put_contents( $path, implode( PHP_EOL, $lines ) . PHP_EOL, FILE_APPEND | LOCK_EX ) !== false;
}

function ws_ingest_process_batch_data( array $data, string $batch_filename ): array {
    $result = [
        'phase'            => 'processing',
        'preflight'        => null,
        'records'          => [],
        'summary'          => [],
        'errors'           => [],
        'runtime_warnings' => [],
        'confirm_token'    => '',
    ];

    $log             = ws_ingest_load_proposed_terms_log();
    $new_terms       = $data['meta']['new_terms_proposed'] ?? [];
    $merge_counts    = ws_ingest_merge_proposed_terms( $log, $new_terms );
    if ( ! ws_ingest_save_proposed_terms_log( $log ) ) {
        $result['runtime_warnings'][] = 'Failed to persist proposed-terms log merge. Ingest continues, but review queue may be stale.';
    }

    $blacklist = ws_ingest_build_blacklist( $log );
    $meta      = ws_ingest_strip_prefixed_keys( $data['meta'] ?? [] );
    $records   = ws_ingest_strip_prefixed_keys( $data['records'] ?? [] );
    $record_type = ws_ingest_detect_record_type( $data, $batch_filename );

    $created  = 0;
    $skipped  = 0;
    $failed   = 0;
    $citation_stubs_created = 0;
    $agency_stubs_created   = 0;
    $all_logs = [];

    foreach ( $records as $record ) {
        if ( is_array( $record ) ) {
            $record = ws_ingest_apply_record_defaults( $record, $meta, $record_type );
        }
        if ( $record_type === 'common-law' ) {
            $record_result = ws_ingest_process_common_law_record( $record, $meta, $blacklist );
        } elseif ( $record_type === 'citation' ) {
            $record_result = ws_ingest_process_citation_record( $record, $meta, $blacklist );
        } elseif ( $record_type === 'interpretation' ) {
            $record_result = ws_ingest_process_interpretation_record( $record, $meta, $blacklist );
        } elseif ( $record_type === 'assist-org' ) {
            $record_result = ws_ingest_process_assist_org_record( $record, $meta, $blacklist );
        } else {
            $record_result = ws_ingest_process_statute_record( $record, $meta, $blacklist );
        }
        $sid           = ws_ingest_get_record_identifier( (array) $record, $record_type );

        $raw_citations    = $record['citations']['attached_citations'] ?? [];
        $parsed_citations = ws_ingest_parse_attached_citations( $raw_citations );
        if ( ! empty( $parsed_citations ) ) {
            if ( ! ws_ingest_log_citation_breadcrumbs( $batch_filename, $meta['jurisdiction_id'] ?? '', $sid, $parsed_citations ) ) {
                $result['runtime_warnings'][] = "$sid: failed to append citation breadcrumb log.";
            }
        }

        $agency_breadcrumbs = $record_result['agency_breadcrumbs'] ?? null;
        if ( is_array( $agency_breadcrumbs ) && ! empty( $agency_breadcrumbs['labels'] ) ) {
            if ( ! ws_ingest_log_agency_breadcrumbs(
                $batch_filename,
                (string) ( $meta['jurisdiction_id'] ?? '' ),
                $sid,
                (array) ( $agency_breadcrumbs['labels'] ?? [] ),
                (array) ( $agency_breadcrumbs['matched_ids'] ?? [] ),
                (array) ( $agency_breadcrumbs['created_ids'] ?? [] ),
                (array) ( $agency_breadcrumbs['match_reasons'] ?? [] )
            ) ) {
                $result['runtime_warnings'][] = "$sid: failed to append agency breadcrumb log.";
            }
        }

        $all_logs[] = [
            'record_id' => $sid,
            'success'   => $record_result['success'],
            'post_id'   => $record_result['post_id'],
            'log'       => $record_result['log'],
            'warnings'  => $record_result['warnings'],
        ];

        if ( $record_result['success'] ) {
            $created++;
        } elseif ( ! empty( $record_result['warnings'] ) &&
                   str_contains( implode( ' ', $record_result['warnings'] ), 'duplicate' ) ) {
            $skipped++;
        } else {
            $failed++;
        }

        $citation_stubs_created += (int) ( $record_result['citation_stub_created'] ?? 0 );
        $agency_stubs_created   += (int) ( $record_result['agency_stub_created'] ?? 0 );
    }

    $nationwide_example_miss_count = 0;
    foreach ( $all_logs as $rec_log ) {
        $warnings = (array) ( $rec_log['warnings'] ?? [] );
        foreach ( $warnings as $warning_line ) {
            if ( str_contains( (string) $warning_line, 'nationwide_only run but nationwide_example is empty' ) ) {
                $nationwide_example_miss_count++;
                break;
            }
        }
    }
    if ( $nationwide_example_miss_count > 0 ) {
        $msg = "Nationwide-only run quality check: {$nationwide_example_miss_count} record(s) were missing nationwide_example evidence.";
        $result['runtime_warnings'][] = $msg;
        ws_ingest_queue_admin_notice( $msg, 'warning' );
    }

    $result['records'] = $all_logs;
    $result['summary'] = [
        'created'         => $created,
        'skipped'         => $skipped,
        'failed'          => $failed,
        'citation_stubs_created' => $citation_stubs_created,
        'agency_stubs_created'   => $agency_stubs_created,
        'proposed_new'    => $merge_counts['new'],
        'proposed_merged' => $merge_counts['merged'],
        'blacklist_size'  => count( $blacklist ),
        'source_name'     => $meta['source_name']      ?? '',
        'source_method'   => $meta['source_method']    ?? '',
        'jurisdiction'    => $meta['jurisdiction_id']  ?? ( $meta['jx_id'] ?? '' ),
        'jurisdiction_id' => $meta['jurisdiction_id']  ?? '',
        'jx_id'           => $meta['jx_id']            ?? '',
        'batch_completed' => $meta['batch_completed']  ?? '',
        'record_type'     => $record_type,
    ];

    if ( ! ws_ingest_write_run_log( $result, $batch_filename ) ) {
        $result['runtime_warnings'][] = 'Failed to write detailed run log file.';
    }

    $has_record_warnings = ! empty( array_filter(
        array_column( $result['records'], 'warnings' ),
        fn( $w ) => ! empty( $w )
    ) );
    $has_runtime_warnings = ! empty( $result['runtime_warnings'] );
    $has_warnings = $has_record_warnings || $has_runtime_warnings;
    $has_failures = ( (int) ( $result['summary']['failed'] ?? 0 ) ) > 0;
    if ( ! ws_ingest_log_imported_batch( $batch_filename, $result['summary'], $has_warnings, $has_failures ) ) {
        $result['runtime_warnings'][] = 'Failed to append imported batch ledger log.';
    }

    return $result;
}

function ws_handle_ingest_folder_submission(): array {
    $result = [
        'phase'            => 'folder-processing',
        'preflight'        => null,
        'records'          => [],
        'summary'          => [],
        'errors'           => [],
        'runtime_warnings' => [],
        'confirm_token'    => '',
        'folder'           => [
            'inbox_count'        => 0,
            'processed_files'    => 0,
            'archived_files'     => 0,
            'ready_files'        => 0,
            'blocked_files'      => 0,
            'created_total'      => 0,
            'skipped_total'      => 0,
            'failed_total'       => 0,
            'citation_stubs_total' => 0,
            'agency_stubs_total'   => 0,
            'limit'              => 0,
            'dry_run'            => false,
            'files'              => [],
        ],
    ];

    $limit = max( 1, min( 100, (int) ( $_POST['ws_ingest_folder_limit'] ?? 25 ) ) );
    $dry_run = ! empty( $_POST['ws_ingest_folder_dry_run'] );
    $inbox_files = ws_ingest_get_inbox_files();
    $result['folder']['inbox_count'] = count( $inbox_files );
    $result['folder']['limit'] = $limit;
    $result['folder']['dry_run'] = $dry_run;

    if ( empty( $inbox_files ) ) {
        $result['errors'][] = 'Inbox is empty. Upload JSON files to the ingest inbox folder first.';
        return $result;
    }

    $to_process = array_slice( $inbox_files, 0, $limit );

    foreach ( $to_process as $source_path ) {
        $filename = basename( $source_path );
        $file_report = [
            'filename'    => $filename,
            'status'      => 'unknown',
            'errors'      => [],
            'summary'     => [],
            'archive'     => '',
        ];

        if ( ! is_readable( $source_path ) ) {
            $file_report['status'] = 'read-failed';
            $file_report['errors'][] = 'Unable to read file from inbox.';
            $result['folder']['files'][] = $file_report;
            continue;
        }

        $raw = file_get_contents( $source_path );
        if ( $raw === false ) {
            $file_report['status'] = 'read-failed';
            $file_report['errors'][] = 'Unable to read file from inbox.';
            $result['folder']['files'][] = $file_report;
            continue;
        }

        $decoded = ws_ingest_decode_json_payload( (string) $raw );
        if ( ! $decoded['ok'] ) {
            $file_report['status'] = $dry_run ? 'invalid-json-dry-run' : 'invalid-json';
            $file_report['errors'][] = 'JSON parse error: ' . $decoded['error'];

            if ( ! $dry_run ) {
                $archive_raw = ws_ingest_archive_raw_file( $source_path, $filename );
                if ( $archive_raw['ok'] ) {
                    $file_report['archive'] = $archive_raw['path'];
                    $result['folder']['archived_files']++;
                } else {
                    $file_report['errors'][] = $archive_raw['error'];
                }
            }

            $result['folder']['processed_files']++;
            $result['folder']['blocked_files']++;
            if ( ! $dry_run ) {
                $result['folder']['failed_total']++;
            }
            $result['folder']['files'][] = $file_report;
            continue;
        }

        $data = $decoded['data'];

        $preflight = ws_ingest_preflight( $data, $filename );
        if ( ! $preflight['pass'] ) {
            $file_report['status'] = $dry_run ? 'preflight-failed-dry-run' : 'preflight-failed';
            $file_report['errors'] = array_merge( $file_report['errors'], $preflight['errors'] );
            if ( ! $dry_run ) {
                if ( ! ws_ingest_log_preflight_failure( $filename, $preflight['errors'] ) ) {
                    $result['runtime_warnings'][] = "{$filename}: failed to append preflight failure ledger log.";
                }

                $archive_fail = ws_ingest_archive_json_file( $source_path, $filename, $data );
                if ( $archive_fail['ok'] ) {
                    $file_report['archive'] = $archive_fail['path'];
                    $result['folder']['archived_files']++;
                } else {
                    $file_report['errors'][] = $archive_fail['error'];
                }
            }

            $result['folder']['processed_files']++;
            $result['folder']['blocked_files']++;
            if ( ! $dry_run ) {
                $result['folder']['failed_total']++;
            }
            $result['folder']['files'][] = $file_report;
            continue;
        }

        if ( $dry_run ) {
            $file_report['status'] = 'ready-dry-run';
            $file_report['summary'] = [
                'would_records'       => count( (array) ( $data['records'] ?? [] ) ),
                'preflight_warnings'  => count( (array) ( $preflight['warnings'] ?? [] ) ),
            ];
            $file_report['preflight_warnings'] = array_values( (array) ( $preflight['warnings'] ?? [] ) );

            $result['folder']['processed_files']++;
            $result['folder']['ready_files']++;
            $result['folder']['files'][] = $file_report;
            continue;
        }

        $batch_result = ws_ingest_process_batch_data( $data, $filename );
        $file_report['status'] = 'processed';
        $file_report['summary'] = $batch_result['summary'] ?? [];
        if ( ! empty( $batch_result['runtime_warnings'] ) ) {
            $file_report['errors'] = array_merge( $file_report['errors'], $batch_result['runtime_warnings'] );
        }

        $result['folder']['created_total'] += (int) ( $batch_result['summary']['created'] ?? 0 );
        $result['folder']['skipped_total'] += (int) ( $batch_result['summary']['skipped'] ?? 0 );
        $result['folder']['failed_total']  += (int) ( $batch_result['summary']['failed'] ?? 0 );
        $result['folder']['citation_stubs_total'] += (int) ( $batch_result['summary']['citation_stubs_created'] ?? 0 );
        $result['folder']['agency_stubs_total']   += (int) ( $batch_result['summary']['agency_stubs_created'] ?? 0 );

        $archive_ok = ws_ingest_archive_json_file( $source_path, $filename, $data );
        if ( $archive_ok['ok'] ) {
            $file_report['archive'] = $archive_ok['path'];
            $result['folder']['archived_files']++;
        } else {
            $file_report['errors'][] = $archive_ok['error'];
        }

        $result['folder']['processed_files']++;
        $result['folder']['files'][] = $file_report;
    }

    $result['summary'] = [
        'created'                => $result['folder']['created_total'],
        'skipped'                => $result['folder']['skipped_total'],
        'failed'                 => $result['folder']['failed_total'],
        'citation_stubs_created' => $result['folder']['citation_stubs_total'],
        'agency_stubs_created'   => $result['folder']['agency_stubs_total'],
    ];

    return $result;
}

// ── Main handler ──────────────────────────────────────────────────────────────

function ws_handle_ingest_submission(): array {
    $result = [
        'phase'     => '',
        'preflight' => null,
        'records'   => [],
        'summary'   => [],
        'errors'    => [],
        'runtime_warnings' => [],
        'confirm_token' => '',
    ];

    if ( empty( $_POST['ws_ingest_nonce'] ) || ! wp_verify_nonce( $_POST['ws-ingest_nonce'], 'ws_run_ingest' ) ) {
        $result['errors'][] = 'Security check failed.';
        return $result;
    }

    if ( ! current_user_can( 'manage_options' ) ) {
        $result['errors'][] = 'Insufficient permissions.';
        return $result;
    }

    ws_ingest_bootstrap_log_dir();

    $mode = sanitize_key( wp_unslash( $_POST['ws-ingest_mode'] ?? 'manual' ) );
    if ( $mode === 'folder' ) {
        return ws_handle_ingest_folder_submission();
    }

    $batch_filename = sanitize_text_field( wp_unslash( $_POST['ws-ingest_filename'] ?? 'unknown' ) );

    // ── Read JSON ────────────────────────────────────────────────────────
    $confirmed      = ! empty( $_POST['ws-ingest_confirmed'] );
    $confirm_token  = sanitize_text_field( wp_unslash( $_POST['ws-ingest_confirm_token'] ?? '' ) );
    $json_input     = '';

    if ( $confirmed ) {
        if ( empty( $confirm_token ) ) {
            $result['errors'][] = 'Confirmation token missing. Please run pre-flight again.';
            return $result;
        }

        $payload = ws_ingest_load_confirm_payload( $confirm_token );
        if ( ! $payload ) {
            $result['errors'][] = 'Confirmation payload expired or invalid. Please run pre-flight again.';
            return $result;
        }

        $json_input = (string) ( $payload['json'] ?? '' );
        if ( $batch_filename === 'unknown' && ! empty( $payload['filename'] ) ) {
            $batch_filename = sanitize_text_field( $payload['filename'] );
        }

        // Single-use token.
        ws_ingest_delete_confirm_payload( $confirm_token );
    } else {
        $json_input = (string) wp_unslash( $_POST['ws-ingest_json'] ?? '' );
    }

    if ( trim( $json_input ) === '' ) {
        $result['errors'][] = 'No JSON provided.';
        return $result;
    }

    $data = json_decode( $json_input, true );
    if ( json_last_error() !== JSON_ERROR_NONE ) {
        $result['errors'][] = 'JSON parse error: ' . json_last_error_msg();
        return $result;
    }

    // ── Phase 1: Pre-Flight ──────────────────────────────────────────────
    $result['phase']     = 'preflight';
    $preflight           = ws_ingest_preflight( $data, $batch_filename );
    $result['preflight'] = $preflight;

    if ( ! $preflight['pass'] ) {
        if ( ! ws_ingest_log_preflight_failure( $batch_filename, $preflight['errors'] ) ) {
            $result['runtime_warnings'][] = 'Failed to append preflight failure ledger log. Check filesystem permissions.';
        }
        return $result;
    }

    // Check if user confirmed after seeing preflight
    if ( ! $confirmed ) {
        $result['confirm_token'] = ws_ingest_store_confirm_payload( $json_input, $batch_filename );
        if ( empty( $result['confirm_token'] ) ) {
            $result['errors'][] = 'Failed to store confirmation payload. Please try again; if this persists, check object cache/transient storage.';
        }
        // Return preflight results — show confirmation UI
        return $result;
    }

    return ws_ingest_process_batch_data( $data, $batch_filename );
}


// ── Admin page renderer ───────────────────────────────────────────────────────

function ws_render_ingest_tool_page() {
    if ( ! current_user_can( 'manage_options' ) ) {
        wp_die( 'Access denied.' );
    }

    $run_result = null;
    if ( isset( $_POST['ws-ingest_nonce'] ) ) {
        $run_result = ws_handle_ingest_submission();
    }

    $phase             = $run_result['phase']     ?? '';
    $preflight         = $run_result['preflight'] ?? null;
    $confirmed         = ! empty( $_POST['ws-ingest_confirmed'] );
    $confirm_token     = $run_result['confirm_token'] ?? '';
    $show_confirmation = ( $phase === 'preflight' && $preflight && $preflight['pass'] && ! $confirmed && ! empty( $confirm_token ) );
    $json_input        = (string) wp_unslash( $_POST['ws-ingest_json'] ?? '' );
    $batch_filename    = sanitize_text_field( wp_unslash( $_POST['ws-ingest_filename'] ?? '' ) );
    $inbox_files       = ws_ingest_get_inbox_files();

    ?>
    <div class="wrap">
        <h1>WS Ingest Tool <span style="font-size:13px;color:#666;font-weight:normal;">v<?php echo esc_html( WS_INGEST_VERSION ); ?> — schema <?php echo esc_html( WS_INGEST_SCHEMA_VERSION ); ?></span></h1>
        <p><strong>This version handles:</strong> <code>statute</code>, <code>common-law</code>, <code>citation</code>, <code>interpretation</code>, and <code>assist-org</code> records, <code>json_format_version 2.0</code> only.</p>

        <?php if ( ! empty( $run_result['errors'] ) ): ?>
            <div class="notice notice-error">
                <?php foreach ( $run_result['errors'] as $err ): ?>
                    <p><?php echo esc_html( $err ); ?></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <?php if ( ! empty( $run_result['runtime_warnings'] ) ): ?>
            <div class="notice notice-warning">
                <p><strong>Ingest completed with runtime warnings.</strong></p>
                <?php foreach ( $run_result['runtime_warnings'] as $warning ): ?>
                    <p><?php echo esc_html( $warning ); ?></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <?php if ( $preflight && ! $preflight['pass'] ): ?>
            <div class="notice notice-error">
                <p><strong>Pre-flight failed — ingest aborted.</strong></p>
                <?php foreach ( $preflight['errors'] as $err ): ?>
                    <p>⛔ <?php echo esc_html( $err ); ?></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <?php if ( $phase === 'processing' && ! empty( $run_result['summary'] ) ): ?>
            <?php $s = $run_result['summary']; ?>
            <div class="notice notice-<?php echo $s['failed'] > 0 ? 'warning' : 'success'; ?>">
                <p><strong>Ingest complete.</strong></p>
                <p>
                    ✅ Created: <strong><?php echo (int) $s['created']; ?></strong> &nbsp;|&nbsp;
                    ⏭ Skipped (duplicate): <strong><?php echo (int) $s['skipped']; ?></strong> &nbsp;|&nbsp;
                    ❌ Failed: <strong><?php echo (int) $s['failed']; ?></strong>
                </p>
                <p>
                    🧾 Citation stubs created: <strong><?php echo (int) ( $s['citation_stubs_created'] ?? 0 ); ?></strong>
                    &nbsp;|&nbsp; 🏛 Agency stubs created: <strong><?php echo (int) ( $s['agency_stubs_created'] ?? 0 ); ?></strong>
                </p>
                <?php if ( (int) $s['skipped'] > 0 ): ?>
                    <p>Batch process detected duplicates. Duplicates were skipped.</p>
                <?php endif; ?>
                <p>
                    Source: <strong><?php echo esc_html( $s['source_name'] ); ?></strong>
                    (<?php echo esc_html( $s['source_method'] ); ?>) &nbsp;|&nbsp;
                    Jurisdiction: <strong><?php echo esc_html( strtoupper( $s['jurisdiction'] ) ); ?></strong> &nbsp;|&nbsp;
                    Batch completed: <?php echo esc_html( $s['batch_completed'] ); ?>
                </p>
                <?php if ( $s['proposed_new'] > 0 || $s['proposed_merged'] > 0 ): ?>
                    <p>
                        Proposed terms: <strong><?php echo (int) $s['proposed_new']; ?></strong> new,
                        <strong><?php echo (int) $s['proposed_merged']; ?></strong> merged into existing entries.
                        Blacklist size: <?php echo (int) $s['blacklist_size']; ?> pending terms.
                    </p>
                <?php endif; ?>
            </div>

            <?php foreach ( $run_result['records'] as $rec ): ?>
                <div style="margin:10px 0;padding:10px;border:1px solid <?php echo $rec['success'] ? '#46b450' : '#dc3232'; ?>;border-radius:4px;background:#fff;">
                    <strong><?php echo esc_html( $rec['record_id'] ); ?></strong>
                    <?php if ( $rec['post_id'] ): ?>
                        — <a href="<?php echo get_edit_post_link( $rec['post_id'] ); ?>">post #<?php echo (int) $rec['post_id']; ?></a>
                    <?php endif; ?>
                    <?php if ( ! empty( $rec['warnings'] ) ): ?>
                        <ul style="margin:5px 0 0 15px;color:#c00;">
                            <?php foreach ( $rec['warnings'] as $w ): ?>
                                <li><?php echo esc_html( $w ); ?></li>
                            <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>
                    <?php if ( ! empty( $rec['log'] ) ): ?>
                        <ul style="margin:5px 0 0 15px;color:#555;font-size:12px;">
                            <?php foreach ( $rec['log'] as $l ): ?>
                                <li><?php echo esc_html( $l ); ?></li>
                            <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>

        <?php elseif ( $phase === 'folder-processing' && ! empty( $run_result['folder'] ) ): ?>
            <?php $f = $run_result['folder']; ?>
            <div class="notice notice-<?php echo ( (int) ( $f['failed_total'] ?? 0 ) > 0 ) ? 'warning' : 'success'; ?>">
                <?php if ( ! empty( $f['dry_run'] ) ): ?>
                    <p><strong>Folder ingest dry run complete.</strong></p>
                <?php else: ?>
                    <p><strong>Folder ingest iteration complete.</strong></p>
                <?php endif; ?>
                <p>
                    Files processed: <strong><?php echo (int) ( $f['processed_files'] ?? 0 ); ?></strong>
                    of <?php echo (int) ( $f['limit'] ?? 0 ); ?> requested
                    (inbox had <?php echo (int) ( $f['inbox_count'] ?? 0 ); ?>).
                </p>
                <?php if ( ! empty( $f['dry_run'] ) ): ?>
                    <p>
                        Ready files: <strong><?php echo (int) ( $f['ready_files'] ?? 0 ); ?></strong>
                        &nbsp;|&nbsp; Blocked files: <strong><?php echo (int) ( $f['blocked_files'] ?? 0 ); ?></strong>
                    </p>
                    <p>No records were written and no files were moved in dry run mode.</p>
                    <?php if ( (int) ( $f['ready_files'] ?? 0 ) > 0 ): ?>
                        <form method="post" action="" style="margin:8px 0 0 0;">
                            <?php wp_nonce_field( 'ws_run_ingest', 'ws-ingest_nonce' ); ?>
                            <input type="hidden" name="ws-ingest_mode" value="folder">
                            <input type="hidden" name="ws-ingest_folder_limit" value="<?php echo esc_attr( (string) ( $f['limit'] ?? 25 ) ); ?>">
                            <input type="submit" class="button button-primary" value="Execute Now (Run Without Dry Run)">
                        </form>
                    <?php endif; ?>
                <?php else: ?>
                    <p>
                        Records — ✅ Created: <strong><?php echo (int) ( $f['created_total'] ?? 0 ); ?></strong>
                        &nbsp;|&nbsp; ⏭ Skipped: <strong><?php echo (int) ( $f['skipped_total'] ?? 0 ); ?></strong>
                        &nbsp;|&nbsp; ❌ Failed: <strong><?php echo (int) ( $f['failed_total'] ?? 0 ); ?></strong>
                    </p>
                    <p>
                        Stubs — 🧾 Citation: <strong><?php echo (int) ( $f['citation_stubs_total'] ?? 0 ); ?></strong>
                        &nbsp;|&nbsp; 🏛 Agency: <strong><?php echo (int) ( $f['agency_stubs_total'] ?? 0 ); ?></strong>
                    </p>
                    <?php if ( (int) ( $f['skipped_total'] ?? 0 ) > 0 ): ?>
                        <p>Batch process detected duplicates. Duplicates were skipped.</p>
                    <?php endif; ?>
                    <p>
                        Archived files: <strong><?php echo (int) ( $f['archived_files'] ?? 0 ); ?></strong>
                    </p>
                <?php endif; ?>
            </div>

            <?php foreach ( (array) ( $f['files'] ?? [] ) as $item ): ?>
                <div style="margin:10px 0;padding:10px;border:1px solid #ccd0d4;border-radius:4px;background:#fff;">
                    <p style="margin:0 0 8px 0;"><strong><?php echo esc_html( $item['filename'] ?? '' ); ?></strong> — <?php echo esc_html( $item['status'] ?? 'unknown' ); ?></p>

                    <?php if ( ! empty( $item['summary'] ) ): ?>
                        <p style="margin:0 0 6px 0;color:#555;">
                            <?php if ( ! empty( $f['dry_run'] ) ): ?>
                                would process <?php echo (int) ( $item['summary']['would_records'] ?? 0 ); ?> records,
                                preflight warnings <?php echo (int) ( $item['summary']['preflight_warnings'] ?? 0 ); ?>
                            <?php else: ?>
                                created <?php echo (int) ( $item['summary']['created'] ?? 0 ); ?>,
                                skipped <?php echo (int) ( $item['summary']['skipped'] ?? 0 ); ?>,
                                failed <?php echo (int) ( $item['summary']['failed'] ?? 0 ); ?>,
                                citation stubs <?php echo (int) ( $item['summary']['citation_stubs_created'] ?? 0 ); ?>,
                                agency stubs <?php echo (int) ( $item['summary']['agency_stubs_created'] ?? 0 ); ?>
                            <?php endif; ?>
                        </p>
                    <?php endif; ?>

                    <?php if ( ! empty( $f['dry_run'] ) && ! empty( $item['preflight_warnings'] ) ): ?>
                        <details style="margin:4px 0 8px 0;">
                            <summary>Show pre-flight warnings (<?php echo (int) count( (array) $item['preflight_warnings'] ); ?>)</summary>
                            <ul style="margin:6px 0 0 18px;color:#555;">
                                <?php foreach ( (array) $item['preflight_warnings'] as $warn ): ?>
                                    <li><?php echo esc_html( $warn ); ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </details>
                    <?php endif; ?>

                    <?php if ( ! empty( $item['errors'] ) ): ?>
                        <ul style="margin:4px 0 8px 18px;color:#c00;">
                            <?php foreach ( (array) $item['errors'] as $err ): ?>
                                <li><?php echo esc_html( $err ); ?></li>
                            <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>

                    <?php if ( ! empty( $item['archive'] ) ): ?>
                        <p style="margin:0;color:#555;"><strong>Archived:</strong> <?php echo esc_html( str_replace( ABSPATH, '/', (string) $item['archive'] ) ); ?></p>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>

        <?php elseif ( $show_confirmation ): ?>
            <?php // Pre-flight passed — show results and ask for confirmation ?>
            <div class="notice notice-warning">
                <p><strong>Pre-flight passed. Review and confirm before records are written.</strong></p>
            </div>

            <?php if ( ! empty( $preflight['warnings'] ) ): ?>
                <div class="notice notice-info">
                    <p><strong>Assistant self-report / warnings:</strong></p>
                    <?php foreach ( $preflight['warnings'] as $w ): ?>
                        <p><?php echo esc_html( $w ); ?></p>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <form method="post" action="">
                <?php wp_nonce_field( 'ws_run_ingest', 'ws-ingest_nonce' ); ?>
                <input type="hidden" name="ws-ingest_confirmed" value="1">
                <input type="hidden" name="ws-ingest_confirm_token" value="<?php echo esc_attr( $confirm_token ); ?>">
                <input type="hidden" name="ws-ingest_filename" value="<?php echo esc_attr( $batch_filename ); ?>">
                <p>
                    <input type="submit" class="button button-primary" value="✅ Confirm — Write Records">
                    &nbsp;
                    <a href="<?php echo admin_url( 'tools.php?page=ws-ingest-tool' ); ?>" class="button">Cancel</a>
                </p>
            </form>

        <?php else: ?>
            <?php // Initial form ?>

            <h2>Folder Batch Mode</h2>
            <p>Upload JSON files via FTP to the inbox directory. If the folder is non-empty, you can process files in iterations.</p>
            <p>
                <strong>Inbox:</strong> <code><?php echo esc_html( str_replace( ABSPATH, '/', WS_INGEST_INBOX_DIR ) ); ?></code><br>
                <strong>Archive:</strong> <code><?php echo esc_html( str_replace( ABSPATH, '/', WS_INGEST_ARCHIVE_DIR ) ); ?></code>
            </p>

            <?php if ( empty( $inbox_files ) ): ?>
                <div class="notice notice-info"><p>Inbox is currently empty.</p></div>
            <?php else: ?>
                <div class="notice notice-info">
                    <p><strong>Inbox ready:</strong> <?php echo (int) count( $inbox_files ); ?> file(s) available.</p>
                </div>
                <details style="margin:0 0 12px 0;">
                    <summary>Show inbox files</summary>
                    <ul style="margin:8px 0 0 18px;">
                        <?php foreach ( $inbox_files as $pending ): ?>
                            <li><?php echo esc_html( basename( $pending ) ); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </details>
            <?php endif; ?>

            <form method="post" action="" style="margin-bottom:20px;">
                <?php wp_nonce_field( 'ws_run_ingest', 'ws-ingest_nonce' ); ?>
                <input type="hidden" name="ws-ingest_mode" value="folder">
                <table class="form-table" role="presentation">
                    <tr>
                        <th scope="row"><label for="ws-ingest-folder-limit">Files This Iteration</label></th>
                        <td>
                            <input type="number" name="ws-ingest-folder-limit" id="ws-ingest-folder-limit"
                                   class="small-text" min="1" max="100" value="<?php echo esc_attr( (string) ( $_POST['ws-ingest-folder-limit'] ?? '25' ) ); ?>">
                            <p class="description">Processes the first N JSON files from inbox (alphabetical). Processed files are archived.</p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="ws-ingest-folder-dry-run">Dry Run</label></th>
                        <td>
                            <label>
                                <input type="checkbox" name="ws-ingest-folder-dry-run" id="ws-ingest-folder-dry-run" value="1" <?php checked( ! empty( $_POST['ws-ingest-folder-dry-run'] ) ); ?>>
                                Preflight only (no record writes, no archive moves)
                            </label>
                        </td>
                    </tr>
                </table>
                <p class="submit">
                    <input type="submit" class="button button-primary" value="Run Folder Ingest Iteration" <?php disabled( empty( $inbox_files ) ); ?>>
                </p>
            </form>

            <hr>
            <h2>Single File / Manual Mode</h2>

            <form method="post" action="">
                <?php wp_nonce_field( 'ws_run_ingest', 'ws-ingest_nonce' ); ?>
                <input type="hidden" name="ws-ingest_mode" value="manual">
                <table class="form-table" role="presentation">
                    <tr>
                        <th scope="row"><label for="ws-ingest-filename">Batch Filename</label></th>
                        <td>
                            <input type="text" name="ws-ingest-filename" id="ws-ingest-filename"
                                   class="regular-text"
                                   placeholder="e.g. NJ-7-Statutes-NotebookLM-20260403-0843.json"
                                value="<?php echo esc_attr( $batch_filename ); ?>">
                            <p class="description">Used in the run logs for traceability. Paste the original filename of the JSON file.</p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="ws-ingest-json">JSON Batch</label></th>
                        <td>
                            <textarea name="ws-ingest-json" id="ws-ingest-json"
                                      rows="20" class="large-text code"
                                      placeholder='Paste the complete JSON object here — {"meta":{...},"records":[...],"integrity":{...}}'
                                      required><?php echo esc_textarea( $json_input ); ?></textarea>
                            <p class="description">
                                Paste the complete JSON object from your research model or NotebookLM merge.
                                Pre-flight checks run first. You will be asked to confirm before any records are written.
                            </p>
                        </td>
                    </tr>
                </table>
                <p class="submit">
                    <input type="submit" class="button button-primary" value="Run Pre-Flight Check">
                </p>
            </form>

        <?php endif; ?>

        <hr>
        <p style="color:#999;font-size:12px;">
            Proposed terms log: <code><?php echo esc_html( WS_PROPOSED_TERMS_LOG ); ?></code> &nbsp;|&nbsp;
            All ingested records are created as <strong>drafts</strong> with <strong>verification_status: unverified</strong>.
        </p>
    </div>
    <?php
}
