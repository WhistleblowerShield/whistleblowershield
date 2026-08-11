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
 * @version    3.20.1
 * @author     Whistleblower Shield
 * @link       https://whistleblowershield.org
 * @copyright  Copyright (c) Whistleblower Shield
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
// matrix-state-courts.php, both loaded in the Universal Layer (loader.php)
// so these globals are populated on both frontend and admin. Prior to
// 2026-08-07 those two files loaded admin-only, so this function always
// returned null on the frontend and every construction record's court
// silently fell back to its raw internal key instead of the resolved
// label — fixed in loader.php. Call sites must still handle null
// gracefully for a genuinely unrecognized court key (the query layer
// falls back to the raw key in that case).
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

