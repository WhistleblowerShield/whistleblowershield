<?php
/**
 * query-directory.php
 *
 * Directory Query Layer
 *
 * PURPOSE
 * -------
 * Dedicated data access functions for the public assist-org directory.
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
 * @version 3.16.1
 */

defined( 'ABSPATH' ) || exit;

/**
 * Returns normalized term payload (ids/slugs/names) for one taxonomy.
 *
 * @param int    $post_id   Post ID.
 * @param string $taxonomy  Taxonomy slug.
 * @return array{ids:array<int>,slugs:array<string>,names:array<string>}
 */
function ws_q_taxonomy_payload( $post_id, $taxonomy ) {
    $terms = wp_get_object_terms( (int) $post_id, (string) $taxonomy );
    if ( is_wp_error( $terms ) || ! is_array( $terms ) ) {
        return [ 'ids' => [], 'slugs' => [], 'names' => [] ];
    }

    $ids = [];
    $slugs = [];
    $names = [];
    foreach ( $terms as $term ) {
        if ( ! ( $term instanceof WP_Term ) ) {
            continue;
        }
        $ids[]   = (int) $term->term_id;
        $slugs[] = (string) $term->slug;
        $names[] = (string) $term->name;
    }

    return [
        'ids'   => array_values( array_unique( $ids ) ),
        'slugs' => array_values( array_unique( $slugs ) ),
        'names' => array_values( array_unique( $names ) ),
    ];
}

/**
 * Normalizes assist-org repeater rows into [type, value] pairs.
 *
 * @param mixed  $rows         Raw repeater rows from ACF.
 * @param string $type_key     Row key for channel type.
 * @param string $value_key    Row key for channel value.
 * @return array<int,array{type:string,value:string}>
 */
function ws_q_normalize_channel_rows( $rows, $type_key, $value_key ) {
    if ( ! is_array( $rows ) ) {
        return [];
    }

    $out = [];
    foreach ( $rows as $row ) {
        if ( ! is_array( $row ) ) {
            continue;
        }
        $type  = strtolower( trim( (string) ( $row[ $type_key ] ?? '' ) ) );
        $value = trim( (string) ( $row[ $value_key ] ?? '' ) );
        if ( $type === '' || $value === '' ) {
            continue;
        }
        $out[] = [
            'type'  => $type,
            'value' => $value,
        ];
    }
    return $out;
}


/**
 * Builds the normalized return payload for a single assist-org post.
 *
 * @param int $oid Assist-org post ID.
 * @return array<string,mixed>
 */
function ws_q_build_assist_org_row( $oid ) {

    $oid  = (int) $oid;
    $tax_jx                = ws_q_taxonomy_payload( $oid, WS_JURISDICTION_TAXONOMY );
    $tax_aorg_type         = ws_q_taxonomy_payload( $oid, 'ws_aorg_type' );
    $tax_disclosure_type   = ws_q_taxonomy_payload( $oid, 'ws_disclosure_type' );
    $tax_disclosure_target = ws_q_taxonomy_payload( $oid, 'ws_disclosure_target' );
    $tax_protected_class   = ws_q_taxonomy_payload( $oid, 'ws_protected_class' );
    $tax_case_stage        = ws_q_taxonomy_payload( $oid, 'ws_case_stage' );
    $tax_process_type      = ws_q_taxonomy_payload( $oid, 'ws_process_type' );
    $tax_services          = ws_q_taxonomy_payload( $oid, 'ws_aorg_service' );
    $tax_employment        = ws_q_taxonomy_payload( $oid, 'ws_employment_sector' );
    $tax_languages         = ws_q_taxonomy_payload( $oid, 'ws_languages' );
    $tax_cost_model        = ws_q_taxonomy_payload( $oid, 'ws_aorg_cost_model' );

    $_nw  = (bool) get_post_meta( $oid, 'ws_aorg_serves_nationwide', true );
    $_jx  = $tax_jx['slugs'];
    $_fed = ( ! $_nw && count( $_jx ) === 1 && strtolower( (string) $_jx[0] ) === 'us' );
    $plain = ws_build_plain_english_array( $oid );
    $legitimacy_url = (string) get_post_meta( $oid, 'ws_aorg_legitimacy_url', true );

    return [
        'id'            => $oid,
        'title'         => get_the_title( $oid ),
        'url'           => get_permalink( $oid ),
        'status'        => get_post_status( $oid ),
        'internal_id'          => (string) get_post_meta( $oid, 'ws_aorg_internal_id',               true ),
        'official_name'        => (string) get_post_meta( $oid, 'ws_aorg_official_name',             true ),
        'common_name'          => (string) get_post_meta( $oid, 'ws_aorg_common_name',               true ),
        'type'                 => (string) ( $tax_aorg_type['slugs'][0] ?? '' ),
        'type_label'           => (string) ( $tax_aorg_type['names'][0] ?? '' ),
        'description'          => (string) get_post_meta( $oid, 'ws_aorg_description',               true ),
        'whistleblower_scope'  => (int) get_post_meta( $oid, 'ws_aorg_whistleblower_scope', true ),
        'whistleblower_note'   => (string) get_post_meta( $oid, 'ws_aorg_whistleblower_note', true ),
        'logo'                 => get_field( 'ws_aorg_logo', $oid ),
        'serves_nationwide'    => $_nw,
        'nationwide_flag'      => $_nw,
        'federal_only'         => $_fed,
        'limited_scope'        => (bool) get_post_meta( $oid, 'ws_aorg_limited_scope',       true ),
        'community_scope'      => (string) get_post_meta( $oid, 'ws_aorg_community_scope',            true ),
        // Forward-facing taxonomy values (slugs), with labels alongside.
        'disclosure_types'     => $tax_disclosure_type['slugs'],
        'disclosure_type_labels' => $tax_disclosure_type['names'],
        'disclosure_type'      => $tax_disclosure_type['slugs'], // legacy alias
        'disclosure_targets'   => $tax_disclosure_target['slugs'],
        'disclosure_target_labels' => $tax_disclosure_target['names'],
        'disclosure_targets_details' => (string) get_post_meta( $oid, 'ws_aorg_disclosure_target_details', true ),
        'protected_class'      => $tax_protected_class['slugs'],
        'protected_class_labels' => $tax_protected_class['names'],
        'protected_class_details' => (string) get_post_meta( $oid, 'ws_aorg_protected_class_details', true ),
        'case_stages'          => $tax_case_stage['slugs'],
        'case_stage_labels'    => $tax_case_stage['names'],
        'process_types'        => $tax_process_type['slugs'],
        'process_type_labels'  => $tax_process_type['names'],
        'services'             => $tax_services['names'], // render-facing labels
        'service_slugs'        => $tax_services['slugs'],
        'additional_services'  => (string) get_post_meta( $oid, 'ws_aorg_additional_services',        true ),
        'employment_sectors'   => $tax_employment['slugs'],
        'employment_sector_labels' => $tax_employment['names'],
        'website_url'          => (string) get_post_meta( $oid, 'ws_aorg_website_url',                true ),
        'intake_url'           => (string) get_post_meta( $oid, 'ws_aorg_intake_url',                 true ),
        'contact_url'          => (string) get_post_meta( $oid, 'ws_aorg_contact_url',                true ),
        'phones'               => ws_q_normalize_channel_rows( get_field( 'ws_aorg_phones', $oid ), 'ws_aorg_phone_type', 'ws_aorg_phone_number' ),
        'emails'               => ws_q_normalize_channel_rows( get_field( 'ws_aorg_emails', $oid ), 'ws_aorg_email_type', 'ws_aorg_email_address' ),
        'has_secure_channel'   => (bool) get_post_meta( $oid, 'ws_aorg_has_secure_channel', true ),
        'secure_contact_url'   => (string) get_post_meta( $oid, 'ws_aorg_secure_contact_url',        true ),
        'secure_contact_tool'  => (string) get_post_meta( $oid, 'ws_aorg_secure_contact_tool',       true ),
        'secure_contact_tool_other' => (string) get_post_meta( $oid, 'ws_aorg_secure_contact_tool_other', true ),
        'mailing_address'      => (string) get_post_meta( $oid, 'ws_aorg_mailing_address',            true ),
        'languages'            => $tax_languages['slugs'],
        'language_labels'      => $tax_languages['names'],
        'additional_languages' => (string) get_post_meta( $oid, 'ws_aorg_additional_languages',       true ),
        'cost_model'           => $tax_cost_model['slugs'],
        'cost_model_labels'    => $tax_cost_model['names'],
        'income_limit'         => (bool) get_post_meta( $oid, 'ws_aorg_income_limit',               true ),
        'income_eligibility_required' => (bool) get_post_meta( $oid, 'ws_aorg_income_limit', true ),
        'income_limit_notes'   => (string) get_post_meta( $oid, 'ws_aorg_income_limit_notes',         true ),
        'anonymous'            => (bool) get_post_meta( $oid, 'ws_aorg_accepts_anonymous',   true ),
        'anonymous_pre_consult_possible' => (bool) get_post_meta( $oid, 'ws_aorg_accepts_anonymous', true ),
        'eligibility_notes'    => (string) get_post_meta( $oid, 'ws_aorg_eligibility_notes',          true ),
        'licensed_attorneys'   => (bool) get_post_meta( $oid, 'ws_aorg_licensed_attorneys',  true ),
        'has_attorneys'        => (bool) get_post_meta( $oid, 'ws_aorg_licensed_attorneys', true ),
        'accreditation'        => (string) get_post_meta( $oid, 'ws_aorg_accreditation',              true ),
        'bar_states'           => (string) get_post_meta( $oid, 'ws_aorg_bar_states',                 true ),
        'legitimacy_url'       => $legitimacy_url,
        'last_reviewed'        => (string) get_post_meta( $oid, 'ws_aorg_last_reviewed',              true ),
        'jurisdictions'        => $tax_jx['slugs'],
        'jurisdiction_labels'  => $tax_jx['names'],
        // Full data object for future shortcode contributors.
        'meta' => [
            'internal_id'                  => (string) get_post_meta( $oid, 'ws_aorg_internal_id', true ),
            'official_name'                => (string) get_post_meta( $oid, 'ws_aorg_official_name', true ),
            'common_name'                  => (string) get_post_meta( $oid, 'ws_aorg_common_name', true ),
            'description'                  => (string) get_post_meta( $oid, 'ws_aorg_description', true ),
            'website_url'                  => (string) get_post_meta( $oid, 'ws_aorg_website_url', true ),
            'intake_url'                   => (string) get_post_meta( $oid, 'ws_aorg_intake_url', true ),
            'contact_url'                  => (string) get_post_meta( $oid, 'ws_aorg_contact_url', true ),
            'has_secure_channel'           => (bool) get_post_meta( $oid, 'ws_aorg_has_secure_channel', true ),
            'secure_contact_url'           => (string) get_post_meta( $oid, 'ws_aorg_secure_contact_url', true ),
            'secure_contact_tool'          => (string) get_post_meta( $oid, 'ws_aorg_secure_contact_tool', true ),
            'secure_contact_tool_other'    => (string) get_post_meta( $oid, 'ws_aorg_secure_contact_tool_other', true ),
            'mailing_address'              => (string) get_post_meta( $oid, 'ws_aorg_mailing_address', true ),
            'additional_languages'         => (string) get_post_meta( $oid, 'ws_aorg_additional_languages', true ),
            'income_limit'                 => (bool) get_post_meta( $oid, 'ws_aorg_income_limit', true ),
            'income_limit_notes'           => (string) get_post_meta( $oid, 'ws_aorg_income_limit_notes', true ),
            'accepts_anonymous'            => (bool) get_post_meta( $oid, 'ws_aorg_accepts_anonymous', true ),
            'eligibility_notes'            => (string) get_post_meta( $oid, 'ws_aorg_eligibility_notes', true ),
            'licensed_attorneys'           => (bool) get_post_meta( $oid, 'ws_aorg_licensed_attorneys', true ),
            'accreditation'                => (string) get_post_meta( $oid, 'ws_aorg_accreditation', true ),
            'bar_states'                   => (string) get_post_meta( $oid, 'ws_aorg_bar_states', true ),
            'legitimacy_url'               => $legitimacy_url,
            'last_reviewed'                => (string) get_post_meta( $oid, 'ws_aorg_last_reviewed', true ),
            'serves_nationwide'            => (bool) get_post_meta( $oid, 'ws_aorg_serves_nationwide', true ),
            'limited_scope'                => (bool) get_post_meta( $oid, 'ws_aorg_limited_scope', true ),
            'community_scope'              => (string) get_post_meta( $oid, 'ws_aorg_community_scope', true ),
            'whistleblower_scope'          => (int) get_post_meta( $oid, 'ws_aorg_whistleblower_scope', true ),
            'whistleblower_note'           => (string) get_post_meta( $oid, 'ws_aorg_whistleblower_note', true ),
            'disclosure_targets_details'   => (string) get_post_meta( $oid, 'ws_aorg_disclosure_target_details', true ),
            'additional_services'          => (string) get_post_meta( $oid, 'ws_aorg_additional_services', true ),
            'internal_contact_name'        => (string) get_post_meta( $oid, 'ws_aorg_internal_contact_name', true ),
            'internal_contact_role'        => (string) get_post_meta( $oid, 'ws_aorg_internal_contact_role', true ),
            'internal_contact_email'       => (string) get_post_meta( $oid, 'ws_aorg_internal_contact_email', true ),
            'internal_contact_phone'       => (string) get_post_meta( $oid, 'ws_aorg_internal_contact_phone', true ),
            'internal_last_contacted'      => (string) get_post_meta( $oid, 'ws_aorg_internal_last_contacted', true ),
            'internal_relationship_notes'  => (string) get_post_meta( $oid, 'ws_aorg_internal_relationship_notes', true ),
        ],
        'taxonomies' => [
            'jurisdiction'       => $tax_jx,
            'aorg_type'          => $tax_aorg_type,
            'disclosure_type'    => $tax_disclosure_type,
            'disclosure_targets' => $tax_disclosure_target,
            'protected_class'    => $tax_protected_class,
            'case_stage'         => $tax_case_stage,
            'process_type'       => $tax_process_type,
            'aorg_service'       => $tax_services,
            'employment_sector'  => $tax_employment,
            'languages'          => $tax_languages,
            'cost_model'         => $tax_cost_model,
        ],
        'plain'  => $plain,
        'has_extended_profile' => ! empty( $plain['is_reviewed'] ),
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
    wp_reset_postdata();

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

    // Phase 2: concern filter — routed to correct taxonomy by context resolver.
    // $filters['concern_tax'] is either 'ws_disclosure_type' or
    // 'ws_adverse_action_types' depending on the user's stage selection.
    if ( ! empty( $filters['concern'] ) && ! empty( $filters['concern_tax'] ) ) {
        $allowed_concern_taxonomies = [ 'ws_disclosure_type', 'ws_adverse_action_types' ];
        $concern_tax = sanitize_key( $filters['concern_tax'] );
        if ( in_array( $concern_tax, $allowed_concern_taxonomies, true ) ) {
            $query_args['tax_query'][] = [
                'taxonomy' => $concern_tax,
                'field'    => 'slug',
                'terms'    => sanitize_key( $filters['concern'] ),
            ];
        }
    }

    $q    = new WP_Query( $query_args );
    $rows = [];

    foreach ( $q->posts as $org ) {
        $rows[] = ws_q_build_assist_org_row( $org->ID );
    }
    wp_reset_postdata();

    return $rows;
}
