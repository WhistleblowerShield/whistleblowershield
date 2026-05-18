<?php
/**
 * ws-filter-context.php
 *
 * WhistleblowerShield Core Plugin — Filter Context Resolver
 *
 * PURPOSE
 * -------
 * Resolves incoming GET parameters into a normalized filter context object
 * consumed by all directory and cascade renderers. Single entry point for
 * all filter logic — no renderer or shortcode reads $_GET directly.
 *
 * ARCHITECTURE
 * ------------
 * - Reads only params defined in ws-filter-config.php. Unknown params ignored.
 * - Invalid values fail closed: treated as absent (broadest match).
 * - Routes ws_concern to the correct taxonomy based on ws_stage.
 * - Emits one log record per directory request (no user identity logged).
 * - Returns a normalized context array consumed by query and render layers.
 *
 * CONTEXT ARRAY SHAPE
 * -------------------
 * [
 *   'stage'          => string|null   // validated ws_case_stage slug or null
 *   'concern'        => string|null   // validated concern slug or null
 *   'concern_tax'    => string|null   // 'ws_protected_disclosure' | 'ws_adverse_action' | null
 *   'sector'         => string|null   // validated ws_employment_sector slug or null
 *   'target'         => string|null   // validated ws_disclosure_target slug or null
 *   'has_filters'    => bool          // true if any axis has a value
 *   'is_research'    => bool          // true if stage = research (neutral browse)
 *   'raw'            => array         // original sanitized GET values before validation
 * ]
 *
 * @package    WhistleblowerShield
 * @since      3.15.0
 * @version    3.20.0
 *
 * VERSION LOG
 * -----------
 * 3.15.1  Added engagement scoring helper and profile-view logging helper.
 * 3.15.0  Initial release. GET param resolution, concern taxonomy routing,
 *         validation, logging, and scoring helper.
 */

defined( 'ABSPATH' ) || exit;

// Register filter GET params with WordPress so they survive pretty permalinks.
// Without this, $_GET may not contain these params on pages using
// post_name-based permalinks.
add_filter( 'query_vars', function( $vars ) {
    $vars[] = WS_FILTER_PARAM_STAGE;
    $vars[] = WS_FILTER_PARAM_CONCERN;
    $vars[] = WS_FILTER_PARAM_SECTOR;
    $vars[] = WS_FILTER_PARAM_TARGET;
    $vars[] = WS_FILTER_PARAM_BADGE;
    return $vars;
} );


// ════════════════════════════════════════════════════════════════════════════
// ws_resolve_filter_context()
// ════════════════════════════════════════════════════════════════════════════

/**
 * Reads and validates GET params, returns a normalized filter context array.
 *
 * All downstream renderers and query builders consume this array.
 * Never read $_GET directly outside this function.
 *
 * @param bool $log_request Whether to write a directory-request log entry.
 * @return array Normalized filter context. See file header for shape.
 */
function ws_resolve_filter_context( bool $log_request = true ): array {
    // ── 1. Sanitize raw input ─────────────────────────────────────────────
    // Use get_query_var() first (registered params on pretty permalink pages),
    // fall back to $_GET for non-pretty permalink setups or direct access.
    $raw = [
        WS_FILTER_PARAM_STAGE   => sanitize_key( (string) ( get_query_var( WS_FILTER_PARAM_STAGE,   '' ) ?: wp_unslash( $_GET[ WS_FILTER_PARAM_STAGE   ] ?? '' ) ) ),
        WS_FILTER_PARAM_CONCERN => sanitize_key( (string) ( get_query_var( WS_FILTER_PARAM_CONCERN, '' ) ?: wp_unslash( $_GET[ WS_FILTER_PARAM_CONCERN ] ?? '' ) ) ),
        WS_FILTER_PARAM_SECTOR  => sanitize_key( (string) ( get_query_var( WS_FILTER_PARAM_SECTOR,  '' ) ?: wp_unslash( $_GET[ WS_FILTER_PARAM_SECTOR  ] ?? '' ) ) ),
        WS_FILTER_PARAM_TARGET  => sanitize_key( (string) ( get_query_var( WS_FILTER_PARAM_TARGET,  '' ) ?: wp_unslash( $_GET[ WS_FILTER_PARAM_TARGET  ] ?? '' ) ) ),
        WS_FILTER_PARAM_BADGE   => sanitize_key( (string) ( get_query_var( WS_FILTER_PARAM_BADGE,   '' ) ?: wp_unslash( $_GET[ WS_FILTER_PARAM_BADGE   ] ?? '' ) ) ),
    ];

    // ── 2. Validate against allowed values ───────────────────────────────
    // Invalid values → null (treated as absent / broadest match).
    $stage   = ws_filter_validate( $raw[ WS_FILTER_PARAM_STAGE   ], WS_FILTER_PARAM_STAGE   );
    $concern = ws_filter_validate( $raw[ WS_FILTER_PARAM_CONCERN ], WS_FILTER_PARAM_CONCERN );
    $sector  = ws_filter_validate( $raw[ WS_FILTER_PARAM_SECTOR  ], WS_FILTER_PARAM_SECTOR  );
    $target  = ws_filter_validate( $raw[ WS_FILTER_PARAM_TARGET  ], WS_FILTER_PARAM_TARGET  );
    $badge   = ws_filter_validate_badge_priority( $raw[ WS_FILTER_PARAM_BADGE ] );

    // ── 3. Route concern to correct taxonomy ─────────────────────────────
    // Pre-report / research / null → ws_protected_disclosure
    // Retaliation-active / litigation → ws_adverse_action
    // Post-report → ws_protected_disclosure (could be either; default to disclosure)
    $concern_tax = null;
    if ( $concern !== null ) {
        $concern_tax = ws_filter_resolve_concern_taxonomy( $concern, $stage );
    }

    // ── 4. Research mode flag ─────────────────────────────────────────────
    $is_research = ( $stage === 'research' );

    // ── 5. Has-filters flag ───────────────────────────────────────────────
    $has_filters = ( $stage !== null || $concern !== null || $sector !== null || $target !== null );

    // ── 6. Build context ─────────────────────────────────────────────────
    $context = [
        'stage'       => $stage,
        'concern'     => $concern,
        'concern_tax' => $concern_tax,
        'sector'      => $sector,
        'target'      => $target,
        'badge'       => $badge,
        'has_filters' => $has_filters,
        'is_research' => $is_research,
        'raw'         => $raw,
    ];

    // ── 7. Log the request ───────────────────────────────────────────────
    if ( $log_request ) {
        ws_filter_log_request( $context );
    }

    return $context;
}

/**
 * Validates the soft badge-priority sort parameter.
 *
 * @param string $value Raw sanitized value.
 * @return string|null
 */
function ws_filter_validate_badge_priority( string $value ): ?string {
    if ( $value === '' ) {
        return null;
    }

    if ( in_array( $value, [ 'secure', 'attorneys', 'anonymous' ], true ) ) {
        return $value;
    }

    if ( preg_match( '/^(model|cost)-[a-z0-9-]+$/', $value ) ) {
        return $value;
    }

    return null;
}


// ════════════════════════════════════════════════════════════════════════════
// ws_filter_validate()
// ════════════════════════════════════════════════════════════════════════════

/**
 * Validates a single param value against its allowed list.
 *
 * Returns the validated slug string, or null if absent or invalid.
 * Fails closed — invalid input is treated as absent, never crashes.
 *
 * @param string $value Raw sanitized value.
 * @param string $param Param name constant (e.g. WS_FILTER_PARAM_STAGE).
 * @return string|null
 */
function ws_filter_validate( string $value, string $param ): ?string {
    $allowed_map = ws_filter_allowed_map();

    if ( $value === '' ) {
        return null;
    }

    $allowed = $allowed_map[ $param ] ?? [];

    if ( empty( $allowed ) || ! isset( $allowed[ $value ] ) ) {
        return null;
    }

    return (string) $allowed[ $value ];
}


// ════════════════════════════════════════════════════════════════════════════
// ws_filter_resolve_concern_taxonomy()
// ════════════════════════════════════════════════════════════════════════════

/**
 * Routes a concern slug to the correct taxonomy based on stage.
 *
 * ws_adverse_action slugs are only meaningful in retaliation/litigation
 * stages. All other stages use ws_protected_disclosure.
 *
 * @param string      $concern Validated concern slug.
 * @param string|null $stage   Validated stage slug or null.
 * @return string Taxonomy slug.
 */
function ws_filter_resolve_concern_taxonomy( string $concern, ?string $stage ): string {
    $adverse_action_stages = [ 'retaliation-active', 'litigation' ];

    if ( $stage !== null && in_array( $stage, $adverse_action_stages, true ) ) {
        // Check if this concern slug belongs to ws_adverse_action
        if ( ws_filter_is_adverse_action_slug( $concern ) ) {
            return 'ws_adverse_action';
        }
    }

    return 'ws_protected_disclosure';
}


// ════════════════════════════════════════════════════════════════════════════
// ws_filter_is_adverse_action_slug()
// ════════════════════════════════════════════════════════════════════════════

/**
 * Returns true if the given slug belongs to ws_adverse_action.
 *
 * Used by the concern taxonomy router to distinguish protected disclosure slugs
 * from adverse action type slugs in the shared concern param.
 *
 * @param string $slug Concern slug to check.
 * @return bool
 */
function ws_filter_is_adverse_action_slug( string $slug ): bool {
    static $adverse_slugs = [
        'termination', 'constructive-discharge', 'demotion', 'suspension',
        'disciplinary-action', 'transfer', 'schedule-change', 'pay-reduction',
        'harassment', 'blacklisting', 'security-clearance-action',
        'contract-non-renewal', 'privilege-revocation', 'immigration-threat',
    ];

    return in_array( $slug, $adverse_slugs, true );
}


// ════════════════════════════════════════════════════════════════════════════
// ws_filter_score_org()
// ════════════════════════════════════════════════════════════════════════════

/**
 * Scores a single assist-org row against the active filter context.
 *
 * Higher score = more relevant. Used to sort results within each tier
 * (targeted orgs sorted by score desc, then nationwide orgs by score desc).
 *
 * Weights are defined in ws_filter_score_weights() in ws-filter-config.php.
 * Adjust weights there; do not hardcode numbers here.
 *
 * @param array $org     Normalized org row from ws_q_build_assist_org_row().
 * @param array $context Normalized filter context from ws_resolve_filter_context().
 * @param bool  $targeted Whether this org is jurisdiction-scoped (vs nationwide).
 * @return int Score.
 */
function ws_filter_score_org( array $org, array $context, bool $targeted = false ): int {
    $w     = ws_filter_score_weights();
    $score = 0;

    // @todo: batch-fetch all org terms before the scoring loop using
    // wp_get_object_terms() with an array of post IDs + fields='all_with_object_id'
    // to replace the N×4 per-org term lookups with a single query.
    // Not urgent at current dataset size; required before any caching pass.

    // Base score from whistleblower scope (1-3)
    $scope  = (int) get_post_meta( $org['id'], 'ws_aorg_whistleblower_scope', true );
    $score += max( 0, $scope ) * (int) ( $w['scope_per_level'] ?? 10 );

    // Concern / taxonomy match
    if ( $context['concern'] !== null && $context['concern_tax'] !== null ) {
        $org_terms = wp_get_object_terms(
            $org['id'],
            $context['concern_tax'],
            [ 'fields' => 'slugs' ]
        );
        if ( ! is_wp_error( $org_terms ) && in_array( $context['concern'], (array) $org_terms, true ) ) {
            $score += (int) ( $w['protected_disclosure_match'] ?? 8 );
        }
    }

    // Case stage match
    if ( $context['stage'] !== null ) {
        $org_stages = wp_get_object_terms( $org['id'], 'ws_case_stage', [ 'fields' => 'slugs' ] );
        if ( ! is_wp_error( $org_stages ) && in_array( $context['stage'], (array) $org_stages, true ) ) {
            $score += (int) ( $w['case_stage_match'] ?? 8 );
        }
    }

    // Employment sector match
    if ( $context['sector'] !== null ) {
        $org_sectors = wp_get_object_terms( $org['id'], 'ws_employment_sector', [ 'fields' => 'slugs' ] );
        if ( ! is_wp_error( $org_sectors ) ) {
            // all-sectors-only orgs match any sector filter but get half weight
            if ( in_array( 'all-sectors-only', (array) $org_sectors, true ) ) {
                $score += (int) floor( ( $w['sector_match'] ?? 5 ) / 2 );
            } elseif ( in_array( $context['sector'], (array) $org_sectors, true ) ) {
                $score += (int) ( $w['sector_match'] ?? 5 );
            }
        }
    }

    // Disclosure target match (optional axis)
    if ( $context['target'] !== null ) {
        $org_targets = wp_get_object_terms( $org['id'], 'ws_disclosure_target', [ 'fields' => 'slugs' ] );
        if ( ! is_wp_error( $org_targets ) && in_array( $context['target'], (array) $org_targets, true ) ) {
            $score += (int) ( $w['target_match'] ?? 3 );
        }
    }

    // Attorney bonus — applies when stage signals legal need
    $attorney_stages = ws_filter_attorney_stages();
    if ( $context['stage'] !== null && in_array( $context['stage'], $attorney_stages, true ) ) {
        if ( get_post_meta( $org['id'], 'ws_aorg_has_attorneys', true ) === 'yes' ) {
            $score += (int) ( $w['has_attorneys_bonus'] ?? 4 );
        }
    }

    // Targeted org tiebreaker
    if ( $targeted ) {
        $score += (int) ( $w['targeted_org_bonus'] ?? 2 );
    }

    return $score;
}


// ════════════════════════════════════════════════════════════════════════════
// ws_filter_score_engagement()
// ════════════════════════════════════════════════════════════════════════════

/**
 * Derives an engagement score from contact-path quality signals.
 *
 * Engagement scoring is additive — it appends to the relevance score and
 * never replaces or alters it. Weights/cap are defined in ws-filter-config.php.
 *
 * @param array $org Normalized org row from ws_q_build_assist_org_row().
 * @return int Engagement score, capped at WS_FILTER_ENGAGEMENT_SCORE_CAP.
 */
function ws_filter_score_engagement( array $org ): int {
    $weights = ws_filter_engagement_weights();
    $score   = 0;

    $phones      = is_array( $org['phones'] ?? null ) ? $org['phones'] : [];
    $emails      = is_array( $org['emails'] ?? null ) ? $org['emails'] : [];
    $phone_types = array_column( $phones, 'type' );
    $email_types = array_column( $emails, 'type' );

    if ( in_array( 'hotline', $phone_types, true ) ) {
        $score += (int) ( $weights['has_hotline'] ?? 0 );
    }
    if ( in_array( 'intake', $phone_types, true ) ) {
        $score += (int) ( $weights['has_intake_phone'] ?? 0 );
    }
    if ( in_array( 'tty', $phone_types, true ) ) {
        $score += (int) ( $weights['has_tty'] ?? 0 );
    }
    if ( in_array( 'intake', $email_types, true ) ) {
        $score += (int) ( $weights['has_intake_email'] ?? 0 );
    }
    if ( ! empty( $org['intake_url'] ) ) {
        $score += (int) ( $weights['has_intake_url'] ?? 0 );
    }

    $cap = defined( 'WS_FILTER_ENGAGEMENT_SCORE_CAP' )
        ? (int) WS_FILTER_ENGAGEMENT_SCORE_CAP
        : 4;

    return min( $score, $cap );
}


// ════════════════════════════════════════════════════════════════════════════
// ws_filter_log_profile_view()
// ════════════════════════════════════════════════════════════════════════════

/**
 * Logs a profile-view event when a user navigates from directory to org page.
 *
 * No user identity is logged.
 *
 * @param int   $org_id      Post ID of the ws-assist-org being viewed.
 * @param array $context     Normalized filter context from ws_resolve_filter_context().
 * @param array $click_meta  Extra context from directory click query args.
 * @return void
 */
function ws_filter_log_profile_view( int $org_id, array $context, array $click_meta = [] ): void {
    $rank          = isset( $click_meta['rank'] ) ? (int) $click_meta['rank'] : 0;
    $rel_score     = isset( $click_meta['rel_score'] ) ? (int) $click_meta['rel_score'] : 0;
    $eng_score     = isset( $click_meta['eng_score'] ) ? (int) $click_meta['eng_score'] : 0;
    $results_total = isset( $click_meta['results_total'] ) ? (int) $click_meta['results_total'] : 0;
    $secure        = ! empty( $click_meta['secure'] ) ? 'yes' : 'no';

    $log_dir = WP_CONTENT_DIR . '/logs/ws-filter';
    if ( ! is_dir( $log_dir ) && ! wp_mkdir_p( $log_dir ) ) {
        if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
            error_log( '[ws-core] ws_filter_log_profile_view(): failed to create log directory: ' . $log_dir );
        }
        return;
    }
    if ( is_dir( $log_dir ) && ! file_exists( $log_dir . '/.htaccess' ) ) {
        $htaccess_written = file_put_contents( $log_dir . '/.htaccess', "Deny from all\n", LOCK_EX );
        if ( false === $htaccess_written && defined( 'WP_DEBUG' ) && WP_DEBUG ) {
            error_log( '[ws-core] ws_filter_log_profile_view(): failed to write .htaccess in ' . $log_dir );
        }
    }

    $path = defined( 'WS_FILTER_PROFILE_LOG' )
        ? WS_FILTER_PROFILE_LOG
        : $log_dir . '/profile-views.log';

    $ts      = gmdate( 'Y-m-d H:i:s' );
    $stage   = ws_filter_log_slug( $context['stage'] ?? null );
    $concern = ws_filter_log_slug( $context['concern'] ?? null );
    $tax     = ws_filter_log_slug( str_replace( 'ws_', '', (string) ( $context['concern_tax'] ?? '' ) ) );
    $sector  = ws_filter_log_slug( $context['sector'] ?? null );
    $target  = ws_filter_log_slug( $context['target'] ?? null );
    $filters = ! empty( $context['has_filters'] ) ? 'yes' : 'no';

    $line = "[{$ts} UTC]  event:profile_view  org_id:{$org_id}  rank:{$rank}  results_total:{$results_total}  rel:{$rel_score}  eng:{$eng_score}  secure_badge_rendered:{$secure}  stage:{$stage}  concern:{$concern}({$tax})  sector:{$sector}  target:{$target}  filtered:{$filters}" . PHP_EOL;

    $write_ok = file_put_contents( $path, $line, FILE_APPEND | LOCK_EX );
    if ( false === $write_ok ) {
        if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
            error_log( '[ws-core] ws_filter_log_profile_view(): failed to append log line to ' . $path );
        }
        return;
    }

    $max = defined( 'WS_FILTER_LOG_MAX_LINES' ) ? (int) WS_FILTER_LOG_MAX_LINES : 5000;
    ws_filter_prune_log( $path, $max );
}


// ════════════════════════════════════════════════════════════════════════════
// ws_filter_log_request()
// ════════════════════════════════════════════════════════════════════════════

/**
 * Appends one log entry per directory filter request.
 *
 * No user identity is logged. Payload: timestamp, selected params,
 * resolved taxonomies, has_filters flag.
 *
 * Log file: wp-content/logs/ws-filter/directory-requests.log
 * Bounded by WS_FILTER_LOG_MAX_LINES — oldest entries pruned when exceeded.
 *
 * @param array $context Normalized filter context.
 * @return void
 */
function ws_filter_log_request( array $context ): void {
    static $log_dir = null;

    if ( $log_dir === null ) {
        $log_dir = WP_CONTENT_DIR . '/logs/ws-filter';
        if ( ! is_dir( $log_dir ) && ! wp_mkdir_p( $log_dir ) ) {
            if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
                error_log( '[ws-core] ws_filter_log_request(): failed to create log directory: ' . $log_dir );
            }
            return;
        }
        if ( is_dir( $log_dir ) && ! file_exists( $log_dir . '/.htaccess' ) ) {
            $htaccess_written = file_put_contents( $log_dir . '/.htaccess', "Deny from all\n", LOCK_EX );
            if ( false === $htaccess_written && defined( 'WP_DEBUG' ) && WP_DEBUG ) {
                error_log( '[ws-core] ws_filter_log_request(): failed to write .htaccess in ' . $log_dir );
            }
        }
    }

    $path    = $log_dir . '/directory-requests.log';
    $ts      = gmdate( 'Y-m-d H:i:s' );
    $stage   = ws_filter_log_slug( $context['stage'] ?? null );
    $concern = ws_filter_log_slug( $context['concern'] ?? null );
    $concern_tax = $context['concern_tax'] ?? null;
    $tax     = ws_filter_log_slug( $concern_tax ? str_replace( 'ws_', '', $concern_tax ) : '-' );
    $sector  = ws_filter_log_slug( $context['sector'] ?? null );
    $target  = ws_filter_log_slug( $context['target'] ?? null );
    $filters = $context['has_filters'] ? 'yes' : 'no';

    $line = "[{$ts} UTC]  stage:{$stage}  concern:{$concern}({$tax})  sector:{$sector}  target:{$target}  filtered:{$filters}" . PHP_EOL;

    $write_ok = file_put_contents( $path, $line, FILE_APPEND | LOCK_EX );
    if ( false === $write_ok ) {
        if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
            error_log( '[ws-core] ws_filter_log_request(): failed to append log line to ' . $path );
        }
        return;
    }

    // Prune only when not in development mode.
    // During development the full log history is preserved to surface
    // usage patterns needed for weight tuning. Set WP_DEBUG to false
    // or define WS_FILTER_PRUNE_LOG true to enable pruning.
    $pruning_enabled = ( ! defined( 'WP_DEBUG' ) || ! WP_DEBUG )
        || ( defined( 'WS_FILTER_PRUNE_LOG' ) && WS_FILTER_PRUNE_LOG );

    if ( $pruning_enabled ) {
        $max = defined( 'WS_FILTER_LOG_MAX_LINES' ) ? (int) WS_FILTER_LOG_MAX_LINES : 5000;
        ws_filter_prune_log( $path, $max );
    }
}


// ════════════════════════════════════════════════════════════════════════════
// ws_filter_log_slug()
// ════════════════════════════════════════════════════════════════════════════

/**
 * Normalizes a value into a safe single-token log value.
 *
 * @param mixed $value Value to normalize.
 * @return string Lowercase token or '-'.
 */
function ws_filter_log_slug( $value ): string {
    $token = sanitize_key( (string) ( $value ?? '' ) );
    return $token !== '' ? $token : '-';
}


// ════════════════════════════════════════════════════════════════════════════
// ws_filter_prune_log()
// ════════════════════════════════════════════════════════════════════════════

/**
 * Trims a log file to the most recent $max lines.
 *
 * Only reads and rewrites the file when it actually exceeds $max lines.
 * Uses a 10% buffer to avoid rewriting on every request near the limit.
 *
 * @param string $path Log file path.
 * @param int    $max  Maximum lines to retain.
 * @return void
 */
function ws_filter_prune_log( string $path, int $max ): void {
    if ( ! file_exists( $path ) ) {
        return;
    }

    $lines = file( $path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES );
    if ( ! is_array( $lines ) || count( $lines ) <= (int) floor( $max * 1.1 ) ) {
        return;
    }

    $trimmed = array_slice( $lines, -$max );
    file_put_contents( $path, implode( PHP_EOL, $trimmed ) . PHP_EOL, LOCK_EX );
}
