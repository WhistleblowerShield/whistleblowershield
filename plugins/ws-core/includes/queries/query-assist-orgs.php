<?php
/**
 * query-assist-orgs.php
 *
 * Assist Organization Query Layer
 *
 * PURPOSE
 * -------
 * Dedicated data access functions for ws-assist-org records.
 *
 * This file was split from query-jurisdiction.php to keep jurisdiction
 * datasets focused and prevent the jurisdiction module from becoming a
 * catch-all for unrelated query concerns.
 *
 * LOAD ORDER
 * ----------
 * Must load after query-helpers.php and query-shared.php.
 *
 * @package WhistleblowerShield
 * @since   3.10.4
 * @version 3.10.4
 */

defined( 'ABSPATH' ) || exit;


/**
 * Builds the normalized return payload for a single assist-org post.
 *
 * @param int $oid Assist-org post ID.
 * @return array<string,mixed>
 */
function ws_q_build_assist_org_row( $oid ) {

    $oid  = (int) $oid;
    $_nw  = (bool) get_post_meta( $oid, 'ws_aorg_serves_nationwide', true );
    $_jx  = wp_get_object_terms( $oid, WS_JURISDICTION_TAXONOMY, [ 'fields' => 'slugs' ] );
    $_jx  = ( ! is_wp_error( $_jx ) && is_array( $_jx ) ) ? array_values( array_unique( array_map( 'strval', $_jx ) ) ) : [];
    $_fed = ( ! $_nw && count( $_jx ) === 1 && strtolower( (string) $_jx[0] ) === 'us' );

    return [
        'id'            => $oid,
        'title'         => get_the_title( $oid ),
        'url'           => get_permalink( $oid ),
        'status'        => get_post_status( $oid ),
        'internal_id'          => get_post_meta( $oid, 'ws_aorg_internal_id',               true ),
        'type'                 => ( ( $_aorg_type = get_the_terms( $oid, 'ws_aorg_type' ) ) && ! is_wp_error( $_aorg_type ) ) ? $_aorg_type[0] : null,
        'description'          => get_post_meta( $oid, 'ws_aorg_description',                true ),
        'logo'                 => get_field( 'ws_aorg_logo', $oid ),
        'serves_nationwide'    => $_nw,
        'nationwide_flag'      => $_nw,
        'federal_only'         => $_fed,
        'limited_scope'        => (bool) get_post_meta( $oid, 'ws_aorg_limited_scope',       true ),
        'community_scope'      => get_post_meta( $oid, 'ws_aorg_community_scope',            true ),
        'disclosure_type'      => ws_q_normalize_id_list( get_field( 'ws_aorg_disclosure_type', $oid ) ),
        'disclosure_targets'   => ws_q_normalize_id_list( get_field( 'ws_aorg_disclosure_targets', $oid ) ),
        'disclosure_targets_details' => get_post_meta( $oid, 'ws_aorg_disclosure_targets_details', true ),
        'case_stages'          => ws_q_normalize_id_list( get_field( 'ws_ao_case_stage', $oid ) ),
        'services'             => ( ( $_sv = wp_get_object_terms( $oid, 'ws_aorg_service', [ 'fields' => 'names' ] ) ) && ! is_wp_error( $_sv ) ) ? $_sv : [],
        'additional_services'  => get_post_meta( $oid, 'ws_aorg_additional_services',        true ),
        'employment_sectors'   => ( ( $_es = wp_get_object_terms( $oid, 'ws_employment_sector', [ 'fields' => 'names' ] ) ) && ! is_wp_error( $_es ) ) ? $_es : [],
        'website_url'          => get_post_meta( $oid, 'ws_aorg_website_url',                true ),
        'intake_url'           => get_post_meta( $oid, 'ws_aorg_intake_url',                 true ),
        'phone'                => get_post_meta( $oid, 'ws_aorg_phone',                      true ),
        'email'                => get_post_meta( $oid, 'ws_aorg_email',                      true ),
        'mailing_address'      => get_post_meta( $oid, 'ws_aorg_mailing_address',            true ),
        'languages'            => get_field( 'ws_languages', $oid ),
        'additional_languages' => get_post_meta( $oid, 'ws_aorg_additional_languages',       true ),
        'cost_model'           => ( ( $_cm = wp_get_object_terms( $oid, 'ws_aorg_cost_model', [ 'fields' => 'names' ] ) ) && ! is_wp_error( $_cm ) ) ? $_cm : [],
        'income_limit'         => get_post_meta( $oid, 'ws_aorg_income_limit',               true ),
        'income_limit_notes'   => get_post_meta( $oid, 'ws_aorg_income_limit_notes',         true ),
        'anonymous'            => (bool) get_post_meta( $oid, 'ws_aorg_accepts_anonymous',   true ),
        'eligibility_notes'    => get_post_meta( $oid, 'ws_aorg_eligibility_notes',          true ),
        'licensed_attorneys'   => (bool) get_post_meta( $oid, 'ws_aorg_licensed_attorneys',  true ),
        'accreditation'        => get_post_meta( $oid, 'ws_aorg_accreditation',              true ),
        'bar_states'           => get_post_meta( $oid, 'ws_aorg_bar_states',                 true ),
        'verify_url'           => get_post_meta( $oid, 'ws_aorg_verify_url',                 true ),
        'last_reviewed'        => get_post_meta( $oid, 'ws_aorg_last_reviewed',              true ),
        'plain'  => ws_build_plain_english_array( $oid ),
        'verify' => ws_build_source_verify_array( $oid ),
        'record' => ws_build_record_array( $oid ),
    ];
}


// ════════════════════════════════════════════════════════════════════════════
// Dataset: Assist Organizations
// ════════════════════════════════════════════════════════════════════════════

function ws_get_assist_org_data( $jx_term_id ) {

    $term_id = (int) $jx_term_id;
    if ( ! $term_id ) {
        return [];
    }

    $q = new WP_Query( [
        'post_type'      => 'ws-assist-org',
        'post_status'    => 'publish',
        'posts_per_page' => -1,
        'orderby'        => 'title',
        'order'          => 'ASC',
        'no_found_rows'  => true,
        'tax_query'      => [ [
            'taxonomy' => WS_JURISDICTION_TAXONOMY,
            'field'    => 'term_id',
            'terms'    => $term_id,
        ] ],
    ] );

    $rows = [];
    foreach ( $q->posts as $org ) {
        $rows[] = ws_q_build_assist_org_row( $org->ID );
    }

    return $rows;
}


// ════════════════════════════════════════════════════════════════════════════
// Dataset: Nationwide Assist Organizations (Directory)
// ════════════════════════════════════════════════════════════════════════════

function ws_get_nationwide_assist_org_data( $filters = [] ) {

    $query_args = [
        'post_type'      => 'ws-assist-org',
        'post_status'    => 'publish',
        'posts_per_page' => -1,
        'orderby'        => 'title',
        'order'          => 'ASC',
        'no_found_rows'  => true,
        'meta_query'     => [
            'relation' => 'AND',
            [
                'key'     => 'ws_aorg_serves_nationwide',
                'value'   => '1',
                'compare' => '=',
            ],
        ],
    ];

    if ( ! empty( $filters['type'] ) ) {
        $query_args['tax_query'][] = [
            'taxonomy' => 'ws_aorg_type',
            'field'    => 'slug',
            'terms'    => sanitize_key( $filters['type'] ),
        ];
    }

    if ( ! empty( $filters['sector'] ) ) {
        $query_args['tax_query'][] = [
            'taxonomy' => 'ws_employment_sector',
            'field'    => 'slug',
            'terms'    => sanitize_key( $filters['sector'] ),
        ];
    }

    if ( ! empty( $filters['stage'] ) ) {
        $query_args['tax_query'][] = [
            'taxonomy' => 'ws_case_stage',
            'field'    => 'slug',
            'terms'    => sanitize_key( $filters['stage'] ),
        ];
    }

    if ( ! empty( $filters['cost_model'] ) ) {
        $query_args['tax_query'][] = [
            'taxonomy' => 'ws_aorg_cost_model',
            'field'    => 'slug',
            'terms'    => sanitize_key( $filters['cost_model'] ),
        ];
    }

    $q    = new WP_Query( $query_args );
    $rows = [];

    foreach ( $q->posts as $org ) {
        $rows[] = ws_q_build_assist_org_row( $org->ID );
    }

    return $rows;
}
