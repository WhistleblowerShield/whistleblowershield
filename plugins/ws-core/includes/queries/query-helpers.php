<?php
/**
 * query-helpers.php
 *
 * Query Layer — Pure Utility Helpers
 *
 * PURPOSE
 * -------
 * Holds stateless utility functions used throughout the query layer.
 * Functions in this file must be pure utilities: no WP_Query, no
 * get_post_meta(), no get_field(). If a function reads WordPress data,
 * it belongs in query-shared.php instead.
 *
 * LOAD ORDER
 * ----------
 * Must be loaded before query-shared.php and query-jurisdiction.php.
 * Both files depend on functions defined here.
 *
 * FUNCTIONS
 * ---------
 *   ws_resolve_display_name()  Resolves a WP user ID to a display name string.
 *   ws_jx_term_by_code()       Resolves a USPS jurisdiction code to a WP_Term object.
 *   ws_court_lookup()          Looks up a court entry from the federal/state court matrices.
 *
 * NOTE ON FILE SCOPE
 * ------------------
 * This file avoids WP_Query, get_post_meta(), and get_field() calls. Lightweight
 * WP lookups (get_userdata, get_term_by) and global array reads are acceptable.
 *
 * @package    WhistleblowerShield
 * @since      3.6.0
 * @version    3.10.0
 * @author     Whistleblower Shield
 * @link       https://whistleblowershield.org
 * @copyright  Copyright (c) Whistleblower Shield
 *
 * VERSION HISTORY
 * ---------------
 * 3.6.0  Extracted from query-jurisdiction.php as part of query-layer split.
 *        ws_resolve_display_name() previously defined at top of that file.
 * 3.9.0  ws_jx_term_by_code() and ws_court_lookup() moved here from
 *        matrix-helpers.php. Both are called from the Universal Layer
 *        (query-jurisdiction.php) and must be available on the frontend.
 */

defined( 'ABSPATH' ) || exit;


// ════════════════════════════════════════════════════════════════════════════
// Resolve Display Name
//
// Resolves a WordPress user ID to the user's display name.
// Returns an empty string if the user ID is falsy or the user does not exist.
// Used by all dataset functions so the render layer never calls get_userdata().
// ════════════════════════════════════════════════════════════════════════════

/**
 * Returns the display name for a given WP user ID.
 *
 * @param  int    $user_id  WordPress user ID.
 * @return string           Display name, or empty string if not resolvable.
 */
function ws_resolve_display_name( $user_id ) {
    $user_id = (int) $user_id;
    if ( ! $user_id ) {
        return '';
    }
    $user = get_userdata( $user_id );
    return $user ? $user->display_name : '';
}


// ════════════════════════════════════════════════════════════════════════════
// ws_jx_term_by_code()
//
// Single chokepoint for all WS_JURISDICTION_TAXONOMY term lookups by USPS code.
// Accepts any case and normalizes to lowercase internally so callers can pass
// human-entered values without failing term resolution.
//
// Use this everywhere a USPS code needs to resolve to a WS_JURISDICTION_TAXONOMY term.
// For callers that only need the term ID, use ws_get_term_id_by_code() in
// query-jurisdiction.php, which delegates here.
//
// ════════════════════════════════════════════════════════════════════════════

/**
 * Resolves a USPS jurisdiction code to a WS_JURISDICTION_TAXONOMY WP_Term object.
 *
 * @param  string       $code  USPS jurisdiction code (e.g. 'ca', 'us', 'tx').
 * @return WP_Term|false       Term object, or false if not found.
 */
function ws_jx_term_by_code( $code ) {
    // Normalize to lowercase — USPS codes are stored as lowercase slugs.
    // Silently corrects uppercase input rather than warning; callers should
    // pass lowercase but the function handles either form gracefully.
    return get_term_by( 'slug', strtolower( (string) $code ), WS_JURISDICTION_TAXONOMY );
}


// ════════════════════════════════════════════════════════════════════════════
// ws_court_lookup()
//
// Returns the court entry array for a given court key, checking both
// $_ws_federal_court_matrix (federal) and $_ws_state_court_matrix (state) in order.
// Returns null if the key is not found in either matrix.
//
// The court matrices are populated by matrix-federal-courts.php and
// matrix-state-courts.php, which are admin-only. On the frontend these
// globals are empty and this function always returns null — call sites
// must handle null gracefully (the query layer falls back to the raw key).
//
// ws_jx_codes CONTRACT
// --------------------
// ws_jx_codes values MUST be lowercase in the matrix source files
// (e.g. 'ca', 'tx', 'us') to match WS_JURISDICTION_TAXONOMY taxonomy term slugs.
// This function does not normalize matrix payloads; it expects exact keys.
//
// ════════════════════════════════════════════════════════════════════════════

/**
 * Returns the court entry array for a given court key.
 *
 * @param  string     $court_key  The stored ws_jx_construction_court meta value.
 * @return array|null             Court entry (typically includes 'short',
 *                                'name', and optional 'ws_jx_codes'), or
 *                                null if not found.
 */
function ws_court_lookup( $court_key ) {
    global $_ws_federal_court_matrix, $_ws_state_court_matrix;
    if ( ! $court_key ) {
        return null;
    }
    $court = null;
    if ( ! empty( $_ws_federal_court_matrix[ $court_key ] ) ) {
        $court = $_ws_federal_court_matrix[ $court_key ];
    } elseif ( ! empty( $_ws_state_court_matrix[ $court_key ] ) ) {
        $court = $_ws_state_court_matrix[ $court_key ];
    }
    return $court;
}
