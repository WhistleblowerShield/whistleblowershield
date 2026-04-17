<?php
/**
 * query-jurisdiction.php
 *
 * Jurisdiction Query Layer
 *
 * PURPOSE
 * -------
 * Provides centralized functions for retrieving jurisdiction records and
 * their associated datasets.
 *
 * This file acts as the primary data access layer for the WhistleblowerShield
 * plugin. By consolidating queries here we avoid repeating WP_Query logic
 * throughout the plugin and maintain consistent behavior across shortcodes
 * and templates.
 *
 * ARCHITECTURE
 * ------------
 *
 * jurisdiction (public CPT)
 *      ├── jx-summary       (attach via ws_jurisdiction taxonomy)
 *      ├── jx-statute       (attach_flag + order, ws_jurisdiction taxonomy scope)
 *      ├── jx-common-law    (attach_flag + order, ws_jurisdiction taxonomy scope)
 *      ├── jx-citation      (attach_flag + order, ws_jurisdiction taxonomy scope)
 *      └── jx-interpretation (attach_flag + order, ws_jurisdiction taxonomy scope)
 *
 * JURISDICTION IDENTITY
 * ---------------------
 * The canonical two-letter code for each jurisdiction is the slug of its
 * assigned ws_jurisdiction taxonomy term (e.g., 'ca', 'tx', 'us').
 * All lookups use taxonomy queries.
 *
 * ws_jx_term_id post meta is written on each jurisdiction post (by the seeder
 * and the save_post_jurisdiction hook below) as a convenience for direct
 * post->term_id lookups without a get_term_by() call at runtime.
 *
 * CACHING
 * -------
 * Transient caching is used for expensive or repeated queries.
 * All jurisdiction transients are invalidated on save_post for the
 * jurisdiction CPT.
 *
 * Transient keys:
 *      ws_id_for_term_{term_id}    — post ID lookup by taxonomy term ID
 *      WS_CACHE_ALL_JURISDICTIONS  — full post object list
 *      WS_CACHE_JX_INDEX           — index data with counts
 *
 * STAMP META KEYS
 * ---------------
 * All CPTs share identical ws_auto_ prefixed stamp meta keys (see ws-core.php
 * META KEY NAMING RULES). The GMT audit keys are private (_ws_auto_*) and are
 * not exposed through the query layer:
 *
 *      ws_auto_date_created        — local date (Y-m-d), written once
 *      ws_auto_create_author       — WP user ID, written once
 *      ws_auto_last_edited_date    — local date (Y-m-d), written every save
 *      ws_auto_last_edited_author  — WP user ID, written every save (admin-overridable)
 *
 * DATASET RETURN FORMAT
 * ---------------------
 * All dataset functions return a consistent base array. Keys are plain PHP
 * array keys — the ws_ / ws_auto_ meta key prefixes are stripped at this
 * layer and must not reappear downstream.
 *
 *      [
 *          'id'      => int,
 *          'title'   => string,
 *          'url'     => string,
 *          'status'  => string,  // WP post status
 *          'content' => string,  // raw post_content — apply the_content in render layer
 *          'record'  => [
 *              'created_by'      => int,    // WP user ID (ws_auto_create_author)
 *              'created_by_name' => string, // display name resolved from created_by
 *              'created_date'    => string, // Y-m-d local (ws_auto_date_created)
 *              'edited_by'       => int,    // WP user ID (ws_auto_last_edited_author)
 *              'edited_by_name'  => string, // display name resolved from edited_by
 *              'edited_date'     => string, // Y-m-d local (ws_auto_last_edited)
 *          ],
 *          'plain'  => [ ... ],  // CPTs with plain English workflow — see ws_build_plain_english_array()
 *          'verify' => [ ... ],  // all CPTs — see ws_build_source_verify_array()
 *      ]
 *
 * ws_get_jx_statute_data(), ws_get_jx_comlaw_data(), ws_get_jx_citation_data(), and
 * ws_get_jx_interpretation_data() return arrays-of-arrays using the same
 * shape, plus an 'is_fed' boolean key. Each may contain two groups
 * (jurisdiction-scoped + federal append).
 *
 * Expand each dataset array as those CPTs are defined.
 *
 * NOTE: Audit trail data (_ws_last_edited_by, _ws_edit_history) is stored
 * in wp_postmeta as private hidden keys and is NOT retrieved through this
 * query layer. Use ws_get_last_editor( $post_id ) and
 * ws_get_edit_history( $post_id ) defined in admin-audit-trail.php instead.
 *
 * @package    WhistleblowerShield
 * @since      1.0.0
 * @version    3.10.5
 * @author     Whistleblower Shield
 * @link       https://whistleblowershield.org
 * @copyright  Copyright (c) Whistleblower Shield
 *
 * VERSION
 * -------
 * 1.0.0   Initial release.
 * 2.1.0   Refactored for ws-core architecture.
 * 2.3.1   Content keys normalized to raw post_content.
 * 3.0.0   Taxonomy-based lookups throughout; post meta join removed.
 * 3.1.0   record sub-array added; stamp fields unprefixed in return keys.
 * 3.2.0   Legal update system overhaul; tax_query jurisdiction filter.
 * 3.3.2   ws_/ws_auto_ prefixes stripped from all query layer return keys.
 * 3.5.0   ws_get_jx_statute_data() rebuilt for ingest alignment.
 * 3.6.0   Query layer split: helpers → shared → jurisdiction → agencies.
 * 3.7.0   Assist-org directory dataset introduced (later extracted).
 * 3.8.0   Court label resolution via ws_court_lookup(). Reference page anchor support.
 * 3.9.0   Summary gate on index. Frontend repeater fallback. Services via taxonomy.
 * 3.10.0  ws_procedure_type taxonomy reads added.
 * 3.10.3  Query hardening + schema sync pass:
 *         - added normalization helpers for mixed scalar/array/object/meta payloads
 *         - aligned statute/citation/interpretation/common-law datasets with current
 *           non-hidden ACF fields (including relationship/detail fields)
 *         - removed stale statute bop_standard key read; employee_standard taxonomy
 *           is now the canonical burden standard source
 *         - added defensive handling for WP_Error taxonomy lookups and mixed
 *           ws_ref_materials relationship return shapes
 * 3.10.4  Assist-org datasets moved to query-directory.php.
 * 3.10.5  Cross-cutting legal updates/reference functions moved to
 *         query-general.php.
 */

defined( 'ABSPATH' ) || exit;


// ════════════════════════════════════════════════════════════════════════════
// Term ID Lookup by Code
//
// Resolves a two-letter USPS code to the ws_jurisdiction taxonomy term ID.
// This is step one of the code → post ID chain and is exposed as its own
// helper so callers that need only the term ID (e.g. to scope a dataset
// query directly) can stop here without the extra post lookup.
//
// Returns the integer term ID, or 0 if the term cannot be resolved.
// ════════════════════════════════════════════════════════════════════════════

/**
 * Resolves a two-letter USPS jurisdiction code to its ws_jurisdiction term ID.
 *
 * @param  string $jx_code  Two-letter USPS code (case-insensitive).
 * @return int               Term ID, or 0 if not found.
 */
function ws_get_term_id_by_code( $jx_code ) {

    if ( empty( $jx_code ) ) {
        return 0;
    }

    $term = ws_jx_term_by_code( sanitize_text_field( $jx_code ) );

    if ( ! $term || is_wp_error( $term ) ) {
        return 0;
    }

    return (int) $term->term_id;
}


// ════════════════════════════════════════════════════════════════════════════
// Jurisdiction Post ID Lookup by Code
//
// Resolves a two-letter USPS code to the jurisdiction post ID.
// Composes ws_get_term_id_by_code() with a cached tax_query post lookup.
//
// Result is cached in a transient keyed by taxonomy term ID for 24 hours.
// Returns false if the term or jurisdiction post cannot be resolved.
// ════════════════════════════════════════════════════════════════════════════

/**
 * Resolves a two-letter USPS jurisdiction code to its jurisdiction post ID.
 *
 * @param  string    $jx_code  Two-letter USPS code (case-insensitive).
 * @return int|false           Post ID, or false if not found.
 */
function ws_get_id_by_code( $jx_code ) {

    $term_id = ws_get_term_id_by_code( $jx_code );

    if ( ! $term_id ) {
        return false;
    }

    $cache_key = 'ws_id_for_term_' . $term_id;
    $post_id   = get_transient( $cache_key );

    if ( false === $post_id ) {

        $query = new WP_Query( [
            'post_type'      => 'jurisdiction',
            'posts_per_page' => 1,
            'fields'         => 'ids',
            'no_found_rows'  => true,
            'tax_query'      => [ [
                'taxonomy' => WS_JURISDICTION_TAXONOMY,
                'field'    => 'term_id',
                'terms'    => $term_id,
            ] ],
        ] );

        $post_id = ! empty( $query->posts ) ? (int) $query->posts[0] : 0;
        set_transient( $cache_key, $post_id, DAY_IN_SECONDS );
    }

    return $post_id ?: false;
}


// ════════════════════════════════════════════════════════════════════════════
// Input Resolver
//
// Resolves a mixed $input (numeric post ID or two-letter USPS code string)
// to a jurisdiction post ID integer. Used by all dataset retrieval functions
// to eliminate the repeated is_numeric ternary.
//
// For callers that need a term ID rather than a post ID, use
// ws_get_term_id_by_code() directly.
//
// Returns 0 if input is empty or the code cannot be resolved.
// ════════════════════════════════════════════════════════════════════════════

function ws_resolve_jx_id( $input ) {
    if ( ! $input ) return 0;
    return is_numeric( $input ) ? (int) $input : (int) ws_get_id_by_code( (string) $input );
}

/**
 * Normalizes mixed scalar/array/meta payloads to a clean integer ID list.
 *
 * Accepts ACF post_object/taxonomy returns, serialized meta strings,
 * comma-delimited strings, and mixed object arrays.
 *
 * @param mixed $value Raw value from get_field()/get_post_meta().
 * @return array<int>
 */
function ws_q_normalize_id_list( $value ) {
    $value = maybe_unserialize( $value );

    if ( $value instanceof WP_Post ) {
        return [ (int) $value->ID ];
    }

    if ( is_object( $value ) ) {
        if ( isset( $value->ID ) ) {
            return [ (int) $value->ID ];
        }
        if ( isset( $value->term_id ) ) {
            return [ (int) $value->term_id ];
        }
        return [];
    }

    if ( is_string( $value ) ) {
        $value = trim( $value );
        if ( $value === '' ) {
            return [];
        }
        if ( strpos( $value, ',' ) !== false ) {
            $value = array_map( 'trim', explode( ',', $value ) );
        } else {
            $value = [ $value ];
        }
    }

    if ( ! is_array( $value ) ) {
        $value = [ $value ];
    }

    $ids = [];
    foreach ( $value as $item ) {
        if ( $item instanceof WP_Post ) {
            $ids[] = (int) $item->ID;
            continue;
        }
        if ( is_object( $item ) ) {
            if ( isset( $item->ID ) ) {
                $ids[] = (int) $item->ID;
                continue;
            }
            if ( isset( $item->term_id ) ) {
                $ids[] = (int) $item->term_id;
                continue;
            }
            continue;
        }
        if ( is_numeric( $item ) ) {
            $ids[] = (int) $item;
        }
    }

    $ids = array_values( array_filter( array_unique( $ids ) ) );
    return $ids;
}

/**
 * Returns the first normalized ID from a mixed value, else 0.
 *
 * @param mixed $value Raw value from get_field()/get_post_meta().
 * @return int
 */
function ws_q_first_id( $value ) {
    $ids = ws_q_normalize_id_list( $value );
    return ! empty( $ids ) ? (int) $ids[0] : 0;
}

/**
 * Normalizes mixed scalar/array/meta payloads to a clean text list.
 *
 * @param mixed  $value Raw value from get_field()/get_post_meta().
 * @param string $sanitize Sanitizer callback name.
 * @return array<string>
 */
function ws_q_normalize_text_list( $value, $sanitize = 'sanitize_text_field' ) {
    $value = maybe_unserialize( $value );

    if ( is_string( $value ) ) {
        $value = trim( $value );
        if ( $value === '' ) {
            return [];
        }
        if ( strpos( $value, ',' ) !== false ) {
            $value = array_map( 'trim', explode( ',', $value ) );
        } else {
            $value = [ $value ];
        }
    }

    if ( ! is_array( $value ) ) {
        $value = [ $value ];
    }

    $items = [];
    foreach ( $value as $item ) {
        if ( is_scalar( $item ) ) {
            $text = call_user_func( $sanitize, (string) $item );
            if ( $text !== '' ) {
                $items[] = $text;
            }
        }
    }

    return array_values( array_unique( $items ) );
}


// ════════════════════════════════════════════════════════════════════════════
// Master Jurisdiction Data Fetcher
//
// Accepts either a numeric post ID or a two-letter USPS code string.
// Falls back to the global $post if no input is provided.
//
// Returns a structured array of all jurisdiction metadata, or false if the
// post cannot be resolved or is not a jurisdiction CPT record.
//
// Flag data is retrieved as an array from ACF (return_format: array)
// and destructured here for consistent downstream access.
//
// Record management fields use unprefixed stamp meta keys via get_post_meta(),
// consistent with all other dataset functions.
// ════════════════════════════════════════════════════════════════════════════

function ws_get_jurisdiction_data( $input = null ) {

    if ( ! $input ) {
        global $post;
        $post_id = $post->ID ?? 0;
    } else {
        $post_id = ws_resolve_jx_id( $input );
    }

    if ( ! $post_id || get_post_type( $post_id ) !== 'jurisdiction' ) {
        return false;
    }

    $flag_id  = (int) get_post_meta( $post_id, 'ws_jx_flag', true );
    $jx_terms = wp_get_post_terms( $post_id, WS_JURISDICTION_TAXONOMY, [ 'fields' => 'slugs' ] );
    $jx_code  = ( ! is_wp_error( $jx_terms ) && ! empty( $jx_terms ) ) ? strtoupper( $jx_terms[0] ) : '';

    return [

        // ── Identity ─────────────────────────────────────────────────────────
        'id'         => $post_id,
        'name'       => get_the_title( $post_id ),
        'class'      => get_field( 'ws_jurisdiction_class', $post_id ),
        'code'       => $jx_code,
        // ws_jx_term_id is the ws_jurisdiction taxonomy term ID written by
        // the seeder and save_post_jurisdiction hook. Returned here for
        // callers that need the term ID directly without a get_term_by() call.
        'jx_term_id' => (int) get_post_meta( $post_id, 'ws_jurisdiction_jx', true ),

        // ── Flag ─────────────────────────────────────────────────────────────
        // ACF returns the raw attachment ID for image fields in some contexts;
        // bypass get_field() and resolve the URL directly via WP core.
        'flag' => [
            'image_url'   => $flag_id ? wp_get_attachment_image_url( $flag_id, 'full' ) : '',
            'attribution' => get_post_meta( $post_id, 'ws_jx_flag_attribution', true ),
            'source_url'  => get_post_meta( $post_id, 'ws_jx_flag_source_url',  true ),
            'license'     => get_post_meta( $post_id, 'ws_jx_flag_license',     true ),
        ],

        // ── Government Links ─────────────────────────────────────────────────
        'gov' => [
            'portal_url'        => get_field( 'ws_jx_gov_portal_url',     $post_id ),
            'portal_label'      => get_field( 'ws_jx_gov_portal_label',   $post_id ),
            'executive_url'     => get_field( 'ws_jx_executive_url',      $post_id ),
            'executive_label'   => get_field( 'ws_jx_executive_label',    $post_id ),
            'authority_url'     => get_field( 'ws_jx_wb_authority_url',   $post_id ),
            'authority_label'   => get_field( 'ws_jx_wb_authority_label', $post_id ),
            'legislature_url'   => get_field( 'ws_jx_legislature_url',    $post_id ),
            'legislature_label' => get_field( 'ws_jx_legislature_label',  $post_id ),
        ],

        // ── Record Management ─────────────────────────────────────────────────
        // Read via get_post_meta() using unprefixed stamp keys, consistent with
        // all other dataset functions. Stamp values are written by
        // ws_acf_write_stamp_fields() in admin-hooks.php.
        'record' => ws_build_record_array( $post_id ),

    ];
}


// ════════════════════════════════════════════════════════════════════════════
// Dataset: Summary
//
// Retrieves the jx-summary post assigned to the given ws_jurisdiction term
// and returns a fully-hydrated data array.
//
// jx-summary is inherently plain English. It does not use the has_plain_english
// / plain_reviewed workflow and carries no summarized_by / summarized_date
// stamps.
//
// Returns false if no jx-summary is found for the term.
// ════════════════════════════════════════════════════════════════════════════

function ws_get_jx_summary_data( $jx_term_id ) {

    $term_id = (int) $jx_term_id;
    if ( ! $term_id ) {
        return false;
    }

    $ids = get_posts( [
        'post_type'      => 'jx-summary',
        'post_status'    => 'publish',
        'posts_per_page' => 1,
        'fields'         => 'ids',
        'no_found_rows'  => true,
        'tax_query'      => [ [
            'taxonomy' => WS_JURISDICTION_TAXONOMY,
            'field'    => 'term_id',
            'terms'    => $term_id,
        ] ],
    ] );

    if ( empty( $ids ) ) {
        return false;
    }

    $sid = (int) $ids[0];

    return [
        'id'            => $sid,
        'title'         => get_the_title( $sid ),
        'url'           => get_permalink( $sid ),
        'status'        => get_post_status( $sid ),
        // Content fields
        'content'       => get_post_meta( $sid, 'ws_jx_summary_wysiwyg', true ),
        'sources'       => get_post_meta( $sid, 'ws_jx_summary_sources',   true ),
        'limitations'   => (array) get_field( 'ws_jx_summary_limitations', $sid ),
        'plain_english_reviewed' => (bool) get_post_meta( $sid, 'ws_jx_summary_plain_english_reviewed', true ),
        // jx-summary is inherently plain English; ws_has_plain_english is
        // implicitly true and no per-record toggle is stored or returned here.
        // Plain language fields
        'plain'         => ws_build_plain_english_array( $sid ),
        // Source & verification
        'verify'        => ws_build_source_verify_array( $sid ),
        // Record management
        'record'        => ws_build_record_array( $sid ),
    ];
}


// ════════════════════════════════════════════════════════════════════════════
// Dataset: Statutes
//
// Returns the editorially curated jx-statute records for the jurisdiction
// summary page — published records assigned to the given ws_jurisdiction
// taxonomy term that have attach_flag = true, sorted by order ASC.
//
// attach_flag is NOT a publish gate. It marks the 3–5 statutes an editor
// has chosen to highlight on the summary page. All other statutes remain
// fully accessible via taxonomy-driven user queries.
//
// Accepts a taxonomy term ID integer as scope ($jx_term_id).
// Returns an array of statute data arrays, or empty array if none found.
//
// Federal append logic: if the requested jurisdiction is not US, US-scoped
// statutes are appended with is_fed = true.
// ════════════════════════════════════════════════════════════════════════════

function ws_get_jx_statute_data( $jx_term_id ) {

    $term_id    = (int) $jx_term_id;
    $us_term_id = ws_get_us_term_id();
    if ( ! $term_id ) {
        return [];
    }

    $fetch = function( $tid, $is_fed ) {
        $q = new WP_Query( [
            'post_type'      => 'jx-statute',
            'post_status'    => 'publish',
            'posts_per_page' => -1,
            'orderby'        => 'meta_value_num',
            'meta_key'       => 'ws_jx_statute_display_order',
            'order'          => 'ASC',
            'no_found_rows'  => true,
            'meta_query'     => [ [
                'key'     => 'ws_jx_statute_has_attach_flag',
                'value'   => '1',
                'compare' => '=',
            ] ],
            'tax_query'      => [ [
                'taxonomy' => WS_JURISDICTION_TAXONOMY,
                'field'    => 'term_id',
                'terms'    => $tid,
            ] ],
        ] );
        $rows = [];
        foreach ( $q->posts as $statute ) {
            $sid    = $statute->ID;
            $rows[] = [
                'id'      => $sid,
                'title'   => get_the_title( $sid ),
                'url'     => get_permalink( $sid ),
                'status'  => get_post_status( $sid ),
                'content' => get_post_field( 'post_content', $sid ),
                'order'   => (int) get_post_meta( $sid, 'ws_jx_statute_display_order', true ),
                'is_fed'  => $is_fed,

                // ── Legal Basis ───────────────────────────────────────────
                'official_name'        => get_post_meta( $sid, 'ws_jx_statute_official_name',       true ),
                'citation'             => get_post_meta( $sid, 'ws_jx_statute_citation',             true ),
                'common_name'          => get_post_meta( $sid, 'ws_jx_statute_common_name',          true ),
                'disclosure_type'      => ws_q_normalize_id_list( get_field( 'ws_jx_statute_disclosure_types',      $sid ) ),
                'protected_class'      => ws_q_normalize_id_list( get_field( 'ws_jx_statute_protected_classes',      $sid ) ),
                'protected_class_details' => get_post_meta( $sid, 'ws_jx_statute_protected_class_details', true ),
                'disclosure_targets'   => ws_q_normalize_id_list( get_field( 'ws_jx_statute_disclosure_targets',   $sid ) ),
                'disclosure_target_details' => get_post_meta( $sid, 'ws_jx_statute_disclosure_target_details', true ),
                'adverse_action_scope' => get_post_meta( $sid, 'ws_jx_statute_adverse_action_scope', true ),
                'attach_flag'          => (bool) get_post_meta( $sid, 'ws_jx_statute_has_attach_flag',              true ),

                // ── Statute of Limitations ────────────────────────────────
                'sol_value'           => get_post_meta( $sid, 'ws_jx_statute_sol_value',           true ),
                'sol_unit'            => get_post_meta( $sid, 'ws_jx_statute_sol_unit',            true ),
                'sol_trigger'         => get_post_meta( $sid, 'ws_jx_statute_sol_trigger',         true ),
                'has_sol'             => (bool) get_post_meta( $sid, 'ws_jx_statute_has_limit_ambiguous',     true ),
                'sol_details'         => get_post_meta( $sid, 'ws_jx_statute_limit_details',         true ),
                'has_tolling'         => (bool) get_post_meta( $sid, 'ws_jx_statute_has_tolling_details', true ),
                'tolling_details'     => get_post_meta( $sid, 'ws_jx_statute_tolling_details',     true ),
                'has_exhaustion'      => (bool) get_post_meta( $sid, 'ws_jx_statute_has_exhaustion_required',      true ),
                'exhaustion_details'  => get_post_meta( $sid, 'ws_jx_statute_exhaustion_details',  true ),

                // ── Enforcement ───────────────────────────────────────────
                'process_type'         => ws_q_normalize_id_list( get_field( 'ws_jx_statute_process_types',          $sid ) ),
                'adverse_action'       => ws_q_normalize_id_list( get_field( 'ws_jx_statute_adverse_action_types',        $sid ) ),
                'adverse_action_details' => get_post_meta( $sid, 'ws_jx_statute_adverse_action_type_details', true ),
                'fee_shifting'         => ws_q_normalize_id_list( get_field( 'ws_jx_statute_fee_shiftings',          $sid ) ),
                'remedies'             => ws_q_normalize_id_list( get_field( 'ws_jx_statute_remedies',              $sid ) ),
                'remedies_details'     => get_post_meta( $sid, 'ws_jx_statute_remedy_details', true ),
                'local_agencies'       => ws_q_normalize_id_list( get_field( 'ws_jx_statute_local_agencies',        $sid ) ),
                'federal_agencies'     => ws_q_normalize_id_list( get_field( 'ws_jx_statute_federal_agencies',      $sid ) ),
                'enforcement_channel'  => get_post_meta( $sid, 'ws_jx_statute_enforcement_channel', true ),
                'citation_ids'         => ws_q_normalize_id_list( get_post_meta( $sid, 'ws_jx_statute_citation_ids', true ) ),
                'interp_ids'         => ws_q_normalize_id_list( get_post_meta( $sid, 'ws_jx_statute_interp_ids', true ) ),

                // ── Burden of Proof ───────────────────────────────────────
                'employee_standard'        => ws_q_normalize_id_list( get_field( 'ws_jx_statute_employee_standards', $sid ) ),
                'employee_standard_details' => get_post_meta( $sid, 'ws_jx_statute_employee_standard_details', true ),
                'employer_defense'         => ws_q_normalize_id_list( get_field( 'ws_jx_statute_employer_defenses', $sid ) ),
                'employer_defense_details' => get_post_meta( $sid, 'ws_jx_statute_employer_defense_details', true ),
                'has_rebuttable'           => (bool) get_post_meta( $sid, 'ws_jx_statute_has_rebuttable_presumption', true ),
                'rebuttable_details'       => get_post_meta( $sid, 'ws_jx_statute_rebuttable_presumption',       true ),
                'has_bop'                  => (bool) get_post_meta( $sid, 'ws_jx_statute_has_bop_details',   true ),
                'bop_details'              => get_post_meta( $sid, 'ws_jx_statute_bop_details',              true ),
                'bop_flag'                 => get_post_meta( $sid, 'ws_jx_statute_bop_flag', true ),

                // ── Reward ────────────────────────────────────────────────
                'has_reward'     => (bool) get_post_meta( $sid, 'ws_jx_statute_has_reward_available',     true ),
                'reward_details' => get_post_meta( $sid, 'ws_jx_statute_reward_details', true ),

                // ── Links ─────────────────────────────────────────────────
                'statute_url' => get_post_meta( $sid, 'ws_jx_statute_url',        true ),
                'url_is_pdf'  => (bool) get_post_meta( $sid, 'ws_jx_statute_url_is_pdf', true ),

                'last_reviewed' => get_post_meta( $sid, 'ws_jx_statute_last_reviewed', true ),
                'ref_materials' => ws_get_ref_materials( $sid ),

                // Plain language fields
                'plain'  => ws_build_plain_english_array( $sid ),
                // Source & verification
                'verify' => ws_build_source_verify_array( $sid ),
                // Record management
                'record' => ws_build_record_array( $sid ),
            ];
        }
        return $rows;
    };

    $results = [];
    $terms_to_fetch = array_values( array_filter( array_unique( [ $term_id, $us_term_id ] ) ) );
    foreach ( $terms_to_fetch as $tid ) {
        $results = array_merge( $results, $fetch( $tid, $tid === $us_term_id && $term_id !== $us_term_id ) );
    }

    return $results;
}


// ════════════════════════════════════════════════════════════════════════════
// Jurisdiction Term ID Helper
//
// Returns the ws_jurisdiction taxonomy term ID assigned to a jurisdiction
// post. Used by shortcodes to resolve the term_id before calling data
// functions without making taxonomy calls inside the shortcode itself.
//
// Returns 0 if the post has no term assigned.
// ════════════════════════════════════════════════════════════════════════════

function ws_get_jx_term_id( $post_id ) {
    $terms = wp_get_post_terms( $post_id, WS_JURISDICTION_TAXONOMY );
    if ( empty( $terms ) || is_wp_error( $terms ) ) {
        return 0;
    }
    return (int) $terms[0]->term_id;
}


// ════════════════════════════════════════════════════════════════════════════
// US Term ID Helper
//
// Returns the ws_jurisdiction taxonomy term ID for the 'us' term.
// Result is cached in a static variable — one DB read per request.
//
// Always resolved by literal slug/code ('us') to avoid relying on seeded
// option state.
//
// Used by data functions to mark federal rows consistently.
// ════════════════════════════════════════════════════════════════════════════

function ws_get_us_term_id() {
    static $us_term_id = null;
    if ( $us_term_id !== null ) {
        return $us_term_id;
    }

    $term = ws_jx_term_by_code( 'us' );
    $us_term_id = ( $term && ! is_wp_error( $term ) ) ? (int) $term->term_id : 0;
    return $us_term_id;
}


// ════════════════════════════════════════════════════════════════════════════
// Dataset: Citations
//
// Returns the editorially curated jx-citation records for the jurisdiction
// summary page — published records assigned to the given ws_jurisdiction
// taxonomy term that have attach_flag = true, sorted by order ASC.
//
// attach_flag is NOT a publish gate. It marks the 3–5 citations an editor
// has chosen to highlight on the summary page. All other citations remain
// fully accessible via taxonomy-driven user queries.
//
// Accepts a taxonomy term ID integer as scope ($jx_term_id).
// Returns an array of citation arrays, or empty array if none found.
//
// Federal append logic: if the requested jurisdiction is not US, US-scoped
// citations are appended with is_fed = true.
// ════════════════════════════════════════════════════════════════════════════

function ws_get_jx_citation_data( $jx_term_id ) {

    $term_id    = (int) $jx_term_id;
    $us_term_id = ws_get_us_term_id();
    if ( ! $term_id ) {
        return [];
    }

    $fetch = function( $tid, $is_fed ) {
        $q = new WP_Query( [
            'post_type'      => 'jx-citation',
            'post_status'    => 'publish',
            'posts_per_page' => -1,
            'orderby'        => 'meta_value_num',
            'meta_key'       => 'ws_jx_statute_display_order',
            'order'          => 'ASC',
            'no_found_rows'  => true,
            'meta_query'     => [ [
                'key'     => 'ws_jx_statute_has_attach_flag',
                'value'   => '1',
                'compare' => '=',
            ] ],
            'tax_query'      => [ [
                'taxonomy' => WS_JURISDICTION_TAXONOMY,
                'field'    => 'term_id',
                'terms'    => $tid,
            ] ],
        ] );
        $rows = [];
        foreach ( $q->posts as $citation ) {
            $cid    = $citation->ID;
            $type_raw = get_post_meta( $cid, 'ws_jx_citation_types', true );
            $type_list = ws_q_normalize_text_list( $type_raw, 'sanitize_key' );
            $rows[] = [
                'id'      => $cid,
                'title'   => get_the_title( $cid ),
                'url'     => get_permalink( $cid ),
                'status'  => get_post_status( $cid ),
                'content' => get_post_field( 'post_content', $cid ),
                'is_fed'  => $is_fed,
                // Citation-specific fields
                'types'           => $type_list,
                'disclosure_type' => ws_q_normalize_id_list( get_field( 'ws_jx_citation_disclosure_types', $cid ) ),
                'official_name'   => get_post_meta( $cid, 'ws_jx_citation_official_name',           true ),
                'common_name'     => get_post_meta( $cid, 'ws_jx_citation_common_name',             true ),
                'label'           => get_post_meta( $cid, 'ws_jx_citation_common_name',           true )
                                   ?: get_post_meta( $cid, 'ws_jx_citation_official_name',             true )
                                   ?: get_the_title( $cid ),
                'cite_url'        => get_post_meta( $cid, 'ws_jx_citation_url',           true ),
                'summary'         => get_post_meta( $cid, 'ws_jx_citation_summary_wysiwyg', true ),
                'is_pdf'          => (bool) get_post_meta( $cid, 'ws_jx_citation_url_is_pdf', true ),
                'protected_class' => ws_q_normalize_id_list( get_field( 'ws_jx_citation_protected_classes', $cid ) ),
                'protected_class_details' => get_post_meta( $cid, 'ws_jx_citation_protected_class_details', true ),
                'disclosure_targets' => ws_q_normalize_id_list( get_field( 'ws_jx_citation_disclosure_targets', $cid ) ),
                'disclosure_target_details' => get_post_meta( $cid, 'ws_jx_citation_disclosure_target_details', true ),
                'adverse_action' => ws_q_normalize_id_list( get_field( 'ws_jx_citation_adverse_action_types', $cid ) ),
                'adverse_action_details' => get_post_meta( $cid, 'ws_jx_citation_adverse_action_type_details', true ),
                'process_type' => ws_q_normalize_id_list( get_field( 'ws_jx_citation_process_types', $cid ) ),
                'remedies' => ws_q_normalize_id_list( get_field( 'ws_jx_citation_remedies', $cid ) ),
                'remedies_details' => get_post_meta( $cid, 'ws_jx_citation_remedy_details', true ),
                'fee_shifting' => ws_q_normalize_id_list( get_field( 'ws_jx_citation_fee_shiftings', $cid ) ),
                'employer_defense' => ws_q_normalize_id_list( get_field( 'ws_jx_citation_employer_defenses', $cid ) ),
                'employer_defense_details' => get_post_meta( $cid, 'ws_jx_citation_employer_defense_details', true ),
                'employee_standard' => ws_q_normalize_id_list( get_field( 'ws_jx_citation_employee_standards', $cid ) ),
                'employee_standard_details' => get_post_meta( $cid, 'ws_jx_citation_employee_standard_details', true ),
                'statute_ids'     => ws_q_normalize_id_list( get_field( 'ws_jx_citation_statute_ids', $cid ) ),
                'comlaw_ids'  => ws_q_normalize_id_list( get_field( 'ws_jx_citation_comlaw_ids', $cid ) ),
                'attach_flag'     => (bool) get_post_meta( $cid, 'ws_jx_citation_has_attach_flag',        true ),
                'order'           => (int)  get_post_meta( $cid, 'ws_jx_citation_display_order',      true ),
                'last_reviewed'   => get_post_meta( $cid, 'ws_jx_citation_last_reviewed', true ),
                'ref_materials'   => ws_get_ref_materials( $cid ),
                // Plain language fields
                'plain'  => ws_build_plain_english_array( $cid ),
                // Source & verification
                'verify' => ws_build_source_verify_array( $cid ),
                // Record management
                'record' => ws_build_record_array( $cid ),
            ];
        }
        return $rows;
    };

    $results = [];
    $terms_to_fetch = array_values( array_filter( array_unique( [ $term_id, $us_term_id ] ) ) );
    foreach ( $terms_to_fetch as $tid ) {
        $results = array_merge( $results, $fetch( $tid, $tid === $us_term_id && $term_id !== $us_term_id ) );
    }

    return $results;
}


// ════════════════════════════════════════════════════════════════════════════
// Dataset: Interpretations
//
// Returns the editorially curated jx-interpretation records for the
// jurisdiction summary page — published records assigned to the given
// ws_jurisdiction taxonomy term that have attach_flag = true, sorted by
// order ASC.
//
// attach_flag is NOT a publish gate. It marks the 3–5 interpretations an
// editor has chosen to highlight on the summary page. All others remain
// fully accessible via taxonomy-driven user queries.
//
// Accepts a taxonomy term ID integer as scope ($jx_term_id).
// Returns an array of interpretation data arrays, or empty array if none found.
//
// Note: interpretations are US federal court decisions. When querying a
// non-US jurisdiction, local records are returned first and US-scoped records
// are appended with is_fed = true.
// ════════════════════════════════════════════════════════════════════════════

function ws_get_jx_interpretation_data( $jx_term_id ) {

    $term_id    = (int) $jx_term_id;
    $us_term_id = ws_get_us_term_id();
    if ( ! $term_id ) {
        return [];
    }

    $fetch = function( $tid, $is_fed ) {
        $q = new WP_Query( [
            'post_type'      => 'jx-interpretation',
            'post_status'    => 'publish',
            'posts_per_page' => -1,
            'orderby'        => 'meta_value_num',
            'meta_key'       => 'ws_jx_interp_display_order',
            'order'          => 'ASC',
            'no_found_rows'  => true,
            'meta_query'     => [ [
                'key'     => 'ws_jx_interp_has_attach_flag',
                'value'   => '1',
                'compare' => '=',
            ] ],
            'tax_query'      => [ [
                'taxonomy' => WS_JURISDICTION_TAXONOMY,
                'field'    => 'term_id',
                'terms'    => $tid,
            ] ],
        ] );
        $rows = [];
        foreach ( $q->posts as $interp ) {
            $iid    = $interp->ID;
            $rows[] = [
                'id'      => $iid,
                'title'   => get_the_title( $iid ),
                'url'     => get_permalink( $iid ),
                'status'  => get_post_status( $iid ),
                'content' => get_post_field( 'post_content', $iid ),
                'order'   => (int) get_post_meta( $iid, 'ws_jx_interp_display_order', true ),
                'is_fed'  => $is_fed,
                // Interpretation-specific fields
                'official_name' => get_post_meta( $iid, 'ws_jx_interp_official_name',            true ),
                'common_name'   => get_post_meta( $iid, 'ws_jx_interp_common_name',              true ),
                'citation'      => get_post_meta( $iid, 'ws_jx_interp_case_citation',    true ),
                'opinion_url'   => get_post_meta( $iid, 'ws_jx_interp_url',              true ),
                'opinion_url_is_pdf' => (bool) get_post_meta( $iid, 'ws_jx_interp_url_is_pdf', true ),
                'court'         => ( ( $_ck = get_post_meta( $iid, 'ws_jx_interp_court', true ) ) === 'other' )
                                        ? ( get_post_meta( $iid, 'ws_jx_interp_court_name', true ) ?: 'Other' )
                                        : ( ( $crt = ws_court_lookup( $_ck ) ) ? $crt['short'] : $_ck ),
                'year'          => (int) get_post_meta( $iid, 'ws_jx_interp_year',             true ),
                'is_favorable'  => (bool) get_post_meta( $iid, 'ws_jx_interp_is_favorable', true ),
                'summary'       => get_post_meta( $iid, 'ws_jx_interp_summary_wysiwyg',          true ),
                'disclosure_type' => ws_q_normalize_id_list( get_field( 'ws_jx_interp_disclosure_types', $iid ) ),
                'protected_class' => ws_q_normalize_id_list( get_field( 'ws_jx_interp_protected_classes', $iid ) ),
                'protected_class_details' => get_post_meta( $iid, 'ws_jx_interp_protected_class_details', true ),
                'disclosure_targets' => ws_q_normalize_id_list( get_field( 'ws_jx_interp_disclosure_targets', $iid ) ),
                'disclosure_target_details' => get_post_meta( $iid, 'ws_jx_interp_disclosure_target_details', true ),
                'adverse_action' => ws_q_normalize_id_list( get_field( 'ws_jx_interp_adverse_action_types', $iid ) ),
                'adverse_action_details' => get_post_meta( $iid, 'ws_jx_interp_adverse_action_type_details', true ),
                'process_type'  => ws_q_normalize_id_list( get_field( 'ws_jx_interp_process_types', $iid ) ),
                'remedies' => ws_q_normalize_id_list( get_field( 'ws_jx_interp_remedies', $iid ) ),
                'remedies_details' => get_post_meta( $iid, 'ws_jx_interp_remedy_details', true ),
                'fee_shifting' => ws_q_normalize_id_list( get_field( 'ws_jx_interp_fee_shiftings', $iid ) ),
                'employer_defense' => ws_q_normalize_id_list( get_field( 'ws_jx_interp_employer_defenses', $iid ) ),
                'employer_defense_details' => get_post_meta( $iid, 'ws_jx_interp_employer_defense_details', true ),
                'employee_standard' => ws_q_normalize_id_list( get_field( 'ws_jx_interp_employee_standards', $iid ) ),
                'employee_standard_details' => get_post_meta( $iid, 'ws_jx_interp_employee_standard_details', true ),
                'parent_statute_id' => ws_q_first_id( get_post_meta( $iid, 'ws_jx_interp_statute_id', true ) ),
                'parent_comlaw_id'  => ws_q_first_id( get_post_meta( $iid, 'ws_jx_interp_comlaw_id', true ) ),
                'affected_jx' => ws_q_normalize_id_list( get_field( 'ws_jx_interp_affected_jx', $iid ) ),
                'attach_flag'   => (bool) get_post_meta( $iid, 'ws_jx_interp_has_attach_flag',         true ),
                'last_reviewed' => get_post_meta( $iid, 'ws_jx_interp_last_reviewed',    true ),
                'ref_materials' => ws_get_ref_materials( $iid ),
                // Plain language fields
                'plain'  => ws_build_plain_english_array( $iid ),
                // Source & verification
                'verify' => ws_build_source_verify_array( $iid ),
                // Record management
                'record' => ws_build_record_array( $iid ),
            ];
        }
        return $rows;
    };

    $results = [];
    $terms_to_fetch = array_values( array_filter( array_unique( [ $term_id, $us_term_id ] ) ) );
    foreach ( $terms_to_fetch as $tid ) {
        $results = array_merge( $results, $fetch( $tid, $tid === $us_term_id && $term_id !== $us_term_id ) );
    }

    return $results;
}


// ════════════════════════════════════════════════════════════════════════════
// Get All Jurisdictions
//
// Returns a list of all published jurisdiction post objects ordered
// alphabetically by title. Result is cached for 12 hours.
//
// Used for bulk operations and administrative views where full post objects
// are needed. For index display use ws_get_jurisdiction_index_data() which
// includes type counts and structured metadata.
// ════════════════════════════════════════════════════════════════════════════

function ws_get_all_jurisdictions() {

    $cache_key     = WS_CACHE_ALL_JURISDICTIONS;
    $jurisdictions = get_transient( $cache_key );

    if ( false === $jurisdictions ) {

        $query = new WP_Query( [
            'post_type'      => 'jurisdiction',
            'post_status'    => 'publish',
            'posts_per_page' => -1,
            'orderby'        => 'title',
            'order'          => 'ASC',
            'no_found_rows'  => true,
        ] );

        $jurisdictions = $query->posts;

        // Cache for 12 hours — invalidated on jurisdiction save.
        set_transient( $cache_key, $jurisdictions, 12 * HOUR_IN_SECONDS );
    }

    return $jurisdictions;
}


// ════════════════════════════════════════════════════════════════════════════
// Get Jurisdiction Index Data
//
// Returns a structured array containing all jurisdictions as index items
// plus a count breakdown by type. Used to power the jurisdiction index
// shortcode and any type-filtered display views.
//
// SUMMARY GATE
// ------------
// A published jurisdiction post with no linked jx-summary is a stub —
// it has no useful content for end users. Only jurisdictions with a
// linked jx-summary are included in the index. Jurisdictions that have
// not yet been summarized are silently excluded.
//
// Return shape:
//      [
//          'items'  => [ [ 'name', 'code', 'type', 'url' ], ... ],
//          'counts' => [ 'all', 'state', 'territory', 'district', 'federal' ]
//      ]
//
// Result is cached for 24 hours — invalidated on jurisdiction/jx-summary
// saves and jx-summary deletes. The summary check per jurisdiction runs
// only at cache fill time.
// ════════════════════════════════════════════════════════════════════════════

function ws_get_jurisdiction_index_data() {

    $cache_key = WS_CACHE_JX_INDEX;
    $cached    = get_transient( $cache_key );

    if ( false === $cached ) {

        $query = new WP_Query( [
            'post_type'      => 'jurisdiction',
            'post_status'    => 'publish',
            'posts_per_page' => -1,
            'orderby'        => 'title',
            'order'          => 'ASC',
            'no_found_rows'  => true,
        ] );

        $index_items = [];
        $counts      = [
            'all'       => 0,
            'state'     => 0,
            'territory' => 0,
            'district'  => 0,
            'federal'   => 0,
        ];

        if ( $query->have_posts() ) {
            foreach ( $query->posts as $post ) {

                $jx_terms = wp_get_post_terms( $post->ID, WS_JURISDICTION_TAXONOMY );

                if ( is_wp_error( $jx_terms ) || empty( $jx_terms ) ) {
                    continue;
                }

                $jx_term = $jx_terms[0];

                // Gate: exclude stubs — jurisdiction must have a linked jx-summary.
                if ( ! ws_get_jx_summary_data( $jx_term->term_id ) ) {
                    continue;
                }

                $type = get_post_meta( $post->ID, 'ws_jurisdiction_class', true ) ?: 'state';
                $code = strtoupper( $jx_term->slug );

                $index_items[] = [
                    'name' => get_term( $jx_term->term_id )->name ?? '',
                    'code' => $code,
                    'type' => $type,
                    'url'  => get_permalink( $post->ID ),
                ];

                $counts['all']++;
                if ( isset( $counts[ $type ] ) ) {
                    $counts[ $type ]++;
                }
            }
        }

        $cached = [
            'items'  => $index_items,
            'counts' => $counts,
        ];

        // Cache for 24 hours — invalidated on jurisdiction save.
        set_transient( $cache_key, $cached, DAY_IN_SECONDS );
    }

    return $cached;
}


// ════════════════════════════════════════════════════════════════════════════
// Cache Invalidation + ws_jx_term_id Write
//
// Clears jurisdiction/index transients when jurisdiction or jx-summary
// content changes so summary-gated index rows stay accurate.
//
// Also handles per-term ID cache clear + ws_jx_term_id write on
// save_post_jurisdiction.
//
// Also writes ws_jx_term_id post meta, providing a direct post->term_id
// mapping for seeders and admin tooling without a get_term_by() call at
// runtime.
// ════════════════════════════════════════════════════════════════════════════

function ws_invalidate_jurisdiction_list_and_index_caches() {
    delete_transient( WS_CACHE_ALL_JURISDICTIONS );
    delete_transient( WS_CACHE_JX_INDEX );
}


add_action( 'save_post_jurisdiction', function( $post_id ) {

    // Clear list and index caches.
    ws_invalidate_jurisdiction_list_and_index_caches();

    // Resolve the assigned ws_jurisdiction term once for both operations.
    $terms = wp_get_post_terms( $post_id, WS_JURISDICTION_TAXONOMY );

    if ( ! empty( $terms ) && ! is_wp_error( $terms ) ) {
        // Clear the per-term ID cache so ws_get_id_by_code() reflects any
        // taxonomy reassignment immediately.
        delete_transient( 'ws_id_for_term_' . $terms[0]->term_id );

        // Write the direct post->term_id mapping.
        update_post_meta( $post_id, 'ws_jx_term_id', $terms[0]->term_id );
    }

} );

// Index membership is gated by linked jx-summary existence/publication.
// Invalidate jurisdiction list + index whenever a summary is changed or removed.
add_action( 'save_post_jx-summary', 'ws_invalidate_jurisdiction_list_and_index_caches' );
add_action( 'before_delete_post', function( $post_id ) {
    if ( get_post_type( $post_id ) === 'jx-summary' ) {
        ws_invalidate_jurisdiction_list_and_index_caches();
    }
} );


// ════════════════════════════════════════════════════════════════════════════
// Common Law Protection Data
//
// Returns all attached jx-common-law records for a jurisdiction, appending
// federal common law doctrine records the same way ws_get_jx_statute_data()
// appends federal statutes.
//
// @param int $jx_term_id  The ws_jurisdiction term ID for the jurisdiction.
// @return array           Flat array of common law doctrine row arrays.
//                         Empty array if no records exist.
// ════════════════════════════════════════════════════════════════════════════

function ws_get_jx_common_law_data( $jx_term_id ) {

    $term_id    = (int) $jx_term_id;
    $us_term_id = ws_get_us_term_id();
    if ( ! $term_id ) {
        return [];
    }

    $fetch = function( $tid, $is_fed ) {
        $q = new WP_Query( [
            'post_type'      => 'jx-common-law',
            'post_status'    => 'publish',
            'posts_per_page' => -1,
            'orderby'        => 'meta_value_num',
            'meta_key'       => 'ws_jx_comlaw_display_order',
            'order'          => 'ASC',
            'no_found_rows'  => true,
            'meta_query'     => [ [
                'key'     => 'ws_jx_comlaw_has_attach_flag',
                'value'   => '1',
                'compare' => '=',
            ] ],
            'tax_query'      => [ [
                'taxonomy' => WS_JURISDICTION_TAXONOMY,
                'field'    => 'term_id',
                'terms'    => $tid,
            ] ],
        ] );
        $rows = [];
        foreach ( $q->posts as $record ) {
            $rid    = $record->ID;
            $rows[] = [
                'id'      => $rid,
                'title'   => get_the_title( $rid ),
                'url'     => get_permalink( $rid ),
                'status'  => get_post_status( $rid ),
                'content' => get_post_field( 'post_content', $rid ),
                'order'   => (int) get_post_meta( $rid, 'ws_jx_comlaw_display_order', true ),
                //'is_fed'  => $is_fed,

                // ── Legal Basis ───────────────────────────────────────────
                'doctrine_name'          => get_post_meta( $rid, 'ws_jx_comlaw_doctrine_name',          true ),
                'doctrine_id'            => get_post_meta( $rid, 'ws_jx_comlaw_doctrine_id',             true ),
                'common_name'            => get_post_meta( $rid, 'ws_jx_comlaw_common_name',             true ),
                'precedent_url'          => get_post_meta( $rid, 'ws_jx_comlaw_precedent_url',           true ),
                'precedent_url_is_pdf'   => (bool) get_post_meta( $rid, 'ws_jx_comlaw_precedent_url_is_pdf', true ),
                'public_policy_sources'  => ws_q_normalize_text_list( get_post_meta( $rid, 'ws_jx_comlaw_public_policy_sources',  true ), 'sanitize_key' ),
                'other_sources'          => get_post_meta( $rid, 'ws_jx_comlaw_other_sources',           true ),
                'doctrine_basis'         => get_post_meta( $rid, 'ws_jx_comlaw_doctrine_basis_wysiwyg',          true ),
                'recognition_status'     => get_post_meta( $rid, 'ws_jx_comlaw_recognition_status_wysiwyg',      true ),
                'disclosure_type'      => ws_q_normalize_id_list( get_field( 'ws_jx_comlaw_disclosure_types',      $rid ) ),
                'protected_class'      => ws_q_normalize_id_list( get_field( 'ws_jx_comlaw_protected_classes',      $rid ) ),
                'protected_class_details' => get_post_meta( $rid, 'ws_jx_comlaw_protected_class_details', true ),
                'disclosure_targets'   => ws_q_normalize_id_list( get_field( 'ws_jx_comlaw_disclosure_targets',   $rid ) ),
                'disclosure_target_details' => get_post_meta( $rid, 'ws_jx_comlaw_disclosure_target_details', true ),
                'adverse_action_scope' => get_post_meta( $rid, 'ws_jx_comlaw_adverse_action_scope',  true ),
                'attach_flag'          => (bool) get_post_meta( $rid, 'ws_jx_comlaw_has_attach_flag',        true ),

                // ── Statute of Limitations ────────────────────────────────
                'sol_value'           => get_post_meta( $rid, 'ws_jx_comlaw_sol_value',           true ),
                'sol_unit'            => get_post_meta( $rid, 'ws_jx_comlaw_sol_unit',            true ),
                'sol_trigger'         => get_post_meta( $rid, 'ws_jx_comlaw_sol_trigger',         true ),
                'has_sol'             => (bool) get_post_meta( $rid, 'ws_jx_comlaw_has_limit_ambiguous',     true ),
                'sol_details'         => get_post_meta( $rid, 'ws_jx_comlaw_limit_details',         true ),
                'has_tolling'         => (bool) get_post_meta( $rid, 'ws_jx_comlaw_has_tolling_details', true ),
                'tolling_details'     => get_post_meta( $rid, 'ws_jx_comlaw_tolling_details',     true ),
                'has_exhaustion'      => (bool) get_post_meta( $rid, 'ws_jx_comlaw_has_exhaustion_required',      true ),
                'exhaustion_details'  => get_post_meta( $rid, 'ws_jx_comlaw_exhaustion_details',  true ),

                // ── Enforcement ───────────────────────────────────────────
                'process_type'     => ws_q_normalize_id_list( get_field( 'ws_jx_comlaw_process_types',     $rid ) ),
                'adverse_action'   => ws_q_normalize_id_list( get_field( 'ws_jx_comlaw_adverse_action_types',   $rid ) ),
                'adverse_action_details' => get_post_meta( $rid, 'ws_jx_comlaw_adverse_action_type_details', true ),
                'fee_shifting'     => ws_q_normalize_id_list( get_field( 'ws_jx_comlaw_fee_shiftings',     $rid ) ),
                'remedies'         => ws_q_normalize_id_list( get_field( 'ws_jx_comlaw_remedies',         $rid ) ),
                'remedies_details' => get_post_meta( $rid, 'ws_jx_comlaw_remedy_details', true ),
                'related_agencies' => ws_q_normalize_id_list( get_field( 'ws_jx_comlaw_related_agencies', $rid ) ),

                // ── Burden of Proof ───────────────────────────────────────
                'has_preclusion'         => (bool) get_post_meta( $rid, 'ws_jx_comlaw_has_statutory_preclusion',         true ),
                'statutory_preclusion_details' => get_post_meta( $rid, 'ws_jx_comlaw_statutory_preclusion_details', true ),
                'employee_standard'        => ws_q_normalize_id_list( get_field( 'ws_jx_comlaw_employee_standards',        $rid ) ),
                'employee_standard_details' => get_post_meta( $rid, 'ws_jx_comlaw_employee_standard_details', true ),
                'employer_defense'         => ws_q_normalize_id_list( get_field( 'ws_jx_comlaw_employer_defenses',         $rid ) ),
                'employer_defense_details' => get_post_meta( $rid, 'ws_jx_comlaw_employer_defense_details', true ),
                'has_rebuttable'           => (bool) get_post_meta( $rid, 'ws_jx_comlaw_has_rebuttable_presumption', true ),
                'rebuttable_details'       => get_post_meta( $rid, 'ws_jx_comlaw_rebuttable_presumption_details',       true ),
                'has_bop'                  => (bool) get_post_meta( $rid, 'ws_jx_comlaw_has_bop_details',   true ),
                'bop_details'              => get_post_meta( $rid, 'ws_jx_comlaw_bop_details',              true ),
                'bop_flag'                 => get_post_meta( $rid, 'ws_jx_comlaw_bop_flag', true ),

                // ── Reward ────────────────────────────────────────────────
                'has_reward'     => (bool) get_post_meta( $rid, 'ws_jx_comlaw_has_reward_available',     true ),
                'reward_details' => get_post_meta( $rid, 'ws_jx_comlaw_reward_details', true ),
                'citation_ids'   => ws_q_normalize_id_list( get_field( 'ws_jx_comlaw_citation_ids', $rid ) ),
                'interp_ids'     => ws_q_normalize_id_list( get_field( 'ws_jx_comlaw_interp_ids', $rid ) ),
                'ref_materials'  => ws_get_ref_materials( $rid ),

                // Record management
                'plain'  => ws_build_plain_english_array( $rid ),
                'verify' => ws_build_source_verify_array( $rid ),
                'record' => ws_build_record_array( $rid ),
            ];
        }
        return $rows;
    };

    $results = [];
    $terms_to_fetch = array_values( array_unique( [ $term_id ] ) );
    foreach ( $terms_to_fetch as $tid ) {
        $results = array_merge( $results, $fetch( $tid ) );
    }

    return $results;
}

