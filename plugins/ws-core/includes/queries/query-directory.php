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
 * @version    3.20.1
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
    if ( is_wp_error( $terms ) ) {
        // This is the central taxonomy reader for every assist-org directory
        // row — called ~10x per org (jurisdiction, organization_model,
        // protected_disclosure, disclosure_target, protected_class,
        // case_stage, services, employment, languages, cost_model). A
        // genuine failure here previously produced the exact same empty
        // payload as "this org legitimately has no terms," which makes the
        // org invisible to the live Phase 2 filter cascade's scoring
        // (ws_filter_score_org() reads these same taxonomy assignments) with
        // no way to tell a real data gap from a query error. Deduped per
        // taxonomy per request — a failure here is almost always systemic
        // (e.g. a deregistered taxonomy), so it would otherwise repeat once
        // per org per taxonomy and could fill the 100-entry rolling log in
        // a single bad directory page load.
        static $logged = [];
        if ( empty( $logged[ $taxonomy ] ) ) {
            $logged[ $taxonomy ] = true;
            ws_log_loud_failure( new WS_Loud_Failure( 'query-directory', "wp_get_object_terms() failed for taxonomy '{$taxonomy}' — assist-org rows will show this taxonomy as empty for the rest of this request.", [
                'taxonomy' => $taxonomy,
                'post_id'  => $post_id,
                'error'    => $terms->get_error_message(),
            ] ) );
        }
        return [ 'ids' => [], 'slugs' => [], 'names' => [] ];
    }
    if ( ! is_array( $terms ) ) {
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
 * Reads assist-org channel repeaters from canonical raw ACF repeater meta.
 *
 * Matrix and ingest write this shape directly; query reads that shape directly.
 *
 * @param int    $post_id    Assist-org post ID.
 * @param string $field_name Repeater field name.
 * @param string $type_key   Row key for channel type.
 * @param string $value_key  Row key for channel value.
 * @return array<int,array{type:string,value:string}>
 */
function ws_q_get_channel_rows( int $post_id, string $field_name, string $type_key, string $value_key ): array {
    $count = (int) get_post_meta( $post_id, $field_name, true );
    if ( $count <= 0 ) {
        return [];
    }

    $raw_rows = [];
    for ( $i = 0; $i < $count; $i++ ) {
        $raw_rows[] = [
            $type_key  => get_post_meta( $post_id, "{$field_name}_{$i}_{$type_key}", true ),
            $value_key => get_post_meta( $post_id, "{$field_name}_{$i}_{$value_key}", true ),
        ];
    }

    return ws_q_normalize_channel_rows( $raw_rows, $type_key, $value_key );
}

/**
 * Returns yes/no from canonical boolean-ish meta.
 *
 * @param int    $post_id Post ID.
 * @param string $key     Meta key.
 * @return string
 */
function ws_q_bool_meta_yes_no( int $post_id, string $key ): string {
    return (bool) get_post_meta( $post_id, $key, true ) ? 'yes' : 'no';
}

/**
 * Reads assist-org social link repeater rows from canonical raw ACF meta.
 *
 * @param int $post_id Assist-org post ID.
 * @return array<int,array{platform:string,url:string,is_contact:bool}>
 */
function ws_q_get_social_link_rows( int $post_id ): array {
    $field_name = 'ws_aorg_social_links';
    $count      = (int) get_post_meta( $post_id, $field_name, true );
    if ( $count <= 0 ) {
        return [];
    }

    $out = [];
    for ( $i = 0; $i < $count; $i++ ) {
        $platform = strtolower( trim( (string) get_post_meta( $post_id, "{$field_name}_{$i}_social_platform", true ) ) );
        $url      = trim( (string) get_post_meta( $post_id, "{$field_name}_{$i}_social_url", true ) );
        if ( $platform === '' || $url === '' ) {
            continue;
        }

        $out[] = [
            'platform'   => $platform,
            'url'        => $url,
            'is_contact' => (bool) get_post_meta( $post_id, "{$field_name}_{$i}_social_is_contact", true ),
        ];
    }

    return $out;
}

/**
 * Reads assist-org secure channel repeater rows from canonical raw ACF meta.
 *
 * @param int $post_id Assist-org post ID.
 * @return array<int,array{tool:string,url:string,label:string,class:string}>
 */
function ws_q_get_secure_channel_rows( int $post_id ): array {
    $field_name = 'ws_aorg_secure_channels';
    $count      = (int) get_post_meta( $post_id, $field_name, true );
    if ( $count <= 0 ) {
        return [];
    }

    $out = [];
    for ( $i = 0; $i < $count; $i++ ) {
        $tool = strtolower( trim( (string) get_post_meta( $post_id, "{$field_name}_{$i}_channel_tool", true ) ) );
        $url  = trim( (string) get_post_meta( $post_id, "{$field_name}_{$i}_channel_url", true ) );
        if ( $tool === '' && $url === '' ) {
            continue;
        }

        $out[] = [
            'tool'  => $tool,
            'url'   => $url,
            'label' => trim( (string) get_post_meta( $post_id, "{$field_name}_{$i}_channel_label", true ) ),
            'class' => trim( (string) get_post_meta( $post_id, "{$field_name}_{$i}_channel_class", true ) ),
        ];
    }

    return $out;
}

/**
 * Sorts assist-org rows for the no-cascade default directory.
 *
 * @param array<int,array<string,mixed>> $rows Assist-org query rows.
 * @return array<int,array<string,mixed>>
 */
function ws_q_sort_assist_org_rows( array $rows ): array {
    $rank = [
        'us-wb-aid'            => 10,
        'us-psst'              => 20,
        'us-gap'               => 30,
        'us-peer'              => 40,
        'us-nsc'               => 50,
        'us-tsn'               => 60,
        'us-lasst'             => 70,
        'us-nwc'               => 80,
        'us-aiwi'              => 90,
        'us-wttf'              => 100,
        'us-woa'               => 110,
        'us-taf'               => 120,
        'us-whisper'           => 130,
        'us-empowr'            => 140,
        'us-nlada'             => 150,
        'us-nelp'              => 160,
        'us-lsc-find-law-aid'  => 170,
        'us-nela'              => 180,
        'us-aba-find-law-help' => 190,
    ];

    usort( $rows, static function( array $a, array $b ) use ( $rank ): int {
        $a_id = (string) ( $a['internal_id'] ?? '' );
        $b_id = (string) ( $b['internal_id'] ?? '' );
        $a_rank = $rank[ $a_id ] ?? 999;
        $b_rank = $rank[ $b_id ] ?? 999;

        if ( $a_rank !== $b_rank ) {
            return $a_rank <=> $b_rank;
        }

        return strcasecmp( (string) ( $a['title'] ?? '' ), (string) ( $b['title'] ?? '' ) );
    } );

    return $rows;
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
    $tax_organization_model = ws_q_taxonomy_payload( $oid, 'ws_organization_model' );
    $tax_protected_disclosure   = ws_q_taxonomy_payload( $oid, 'ws_protected_disclosure' );
    $tax_disclosure_target = ws_q_taxonomy_payload( $oid, 'ws_disclosure_target' );
    $tax_protected_class   = ws_q_taxonomy_payload( $oid, 'ws_protected_class' );
    $tax_case_stage        = ws_q_taxonomy_payload( $oid, 'ws_case_stage' );
    $tax_services          = ws_q_taxonomy_payload( $oid, 'ws_aorg_service' );
    $tax_employment        = ws_q_taxonomy_payload( $oid, 'ws_employment_sector' );
    $tax_languages         = ws_q_taxonomy_payload( $oid, 'ws_language' );
    $tax_cost_model        = ws_q_taxonomy_payload( $oid, 'ws_aorg_cost_model' );

    $_nw  = (bool) get_post_meta( $oid, 'ws_aorg_is_nationwide', true );
    $_jx  = $tax_jx['slugs'];
    $_fed = ( ! $_nw && count( $_jx ) === 1 && strtolower( (string) $_jx[0] ) === 'us' );
    $plain = ws_build_plain_english_array( $oid );
    $legitimacy_url = (string) get_post_meta( $oid, 'ws_aorg_legitimacy_url', true );
    $secure_channels = ws_q_get_secure_channel_rows( $oid );
    $has_secure_channel = (bool) get_post_meta( $oid, 'ws_aorg_has_secure_channel', true );
    $secure_tools = array_values( array_unique( array_filter( array_column( $secure_channels, 'tool' ) ) ) );

    return [
        'id'            => $oid,
        'internal_id'   => (string) get_post_meta( $oid, '_ws_aorg_id', true ),
        'title'         => get_the_title( $oid ),
        'url'           => get_permalink( $oid ),
        'status'        => get_post_status( $oid ),
        'official_name'        => (string) get_post_meta( $oid, 'ws_aorg_official_name',             true ),
        'common_name'          => (string) get_post_meta( $oid, 'ws_aorg_common_name',               true ),
        'model'                 => (string) ( $tax_organization_model['slugs'][0] ?? '' ),
        'model_label'           => (string) ( $tax_organization_model['names'][0] ?? '' ),
        'type'                  => (string) ( $tax_organization_model['slugs'][0] ?? '' ),
        'type_label'            => (string) ( $tax_organization_model['names'][0] ?? '' ),
        'description'          => (string) get_post_meta( $oid, 'ws_aorg_description',               true ),
        'whistleblower_scope'  => (int) get_post_meta( $oid, 'ws_aorg_whistleblower_scope', true ),
        'whistleblower_scope_details' => (string) get_post_meta( $oid, 'ws_aorg_whistleblower_scope_details', true ),
        'logo'                 => get_field( 'ws_aorg_logo', $oid ),
        'nationwide_flag'      => $_nw,
        'federal_only'         => $_fed,
        'has_limited_scope'    => (bool) get_post_meta( $oid, 'ws_aorg_has_limited_scope',       true ),
        'community_scope'      => (string) get_post_meta( $oid, 'ws_aorg_community_scope',            true ),
        // Forward-facing taxonomy values (slugs), with labels alongside.
        'protected_disclosures'        => $tax_protected_disclosure['slugs'],
        'protected_disclosure_labels'  => $tax_protected_disclosure['names'],
        'disclosure_targets'           => $tax_disclosure_target['slugs'],
        'disclosure_target_labels'     => $tax_disclosure_target['names'],
        'disclosure_target_details'    => (string) get_post_meta( $oid, 'ws_aorg_disclosure_target_details', true ),
        'protected_classes'        => $tax_protected_class['slugs'],
        'protected_class_labels'   => $tax_protected_class['names'],
        'protected_class_details'  => (string) get_post_meta( $oid, 'ws_aorg_protected_class_details', true ),
        'case_stages'          => $tax_case_stage['slugs'],
        'case_stage_labels'    => $tax_case_stage['names'],
        'case_stage_details'   => (string) get_post_meta( $oid, 'ws_aorg_case_stage_details', true ),
        'service_labels'       => $tax_services['names'], // render-facing labels
        'services'             => $tax_services['slugs'],
        'additional_services'  => (string) get_post_meta( $oid, 'ws_aorg_additional_services',        true ),
        'employment_sectors'   => $tax_employment['slugs'],
        'employment_sector_labels' => $tax_employment['names'],
        'official_homepage_url'=> (string) get_post_meta( $oid, 'ws_aorg_official_homepage_url',      true ),
        'intake_url'           => (string) get_post_meta( $oid, 'ws_aorg_intake_url',                 true ),
        'lawyers_url'          => (string) get_post_meta( $oid, 'ws_aorg_lawyers_url',                true ),
        'contact_url'          => (string) get_post_meta( $oid, 'ws_aorg_contact_url',                true ),
        'social_presence'      => (bool) get_post_meta( $oid, 'ws_aorg_social_presence', true ),
        'social_links'         => ws_q_get_social_link_rows( $oid ),
        'phones'               => ws_q_get_channel_rows( $oid, 'ws_aorg_phones', 'phone_type', 'phone_number' ),
        'emails'               => ws_q_get_channel_rows( $oid, 'ws_aorg_emails', 'email_type', 'email_address' ),
        'has_secure_channel'   => $has_secure_channel,
        'secure_channel_status'=> $has_secure_channel ? 'available' : 'none-found',
        'secure_channels'      => $secure_channels,
        'secure_contact_tools' => $secure_tools,
        'mailing_address'      => (string) get_post_meta( $oid, 'ws_aorg_mailing_address',            true ),
        'languages'            => $tax_languages['slugs'],
        'language_labels'      => $tax_languages['names'],
        'language_details'     => (string) get_post_meta( $oid, 'ws_aorg_language_details',           true ),
        'additional_languages' => (string) get_post_meta( $oid, 'ws_aorg_language_details',           true ),
        'cost_model'           => $tax_cost_model['slugs'],
        'cost_model_labels'    => $tax_cost_model['names'],
        'income_screening'     => (string) get_post_meta( $oid, 'ws_aorg_income_screening', true ),
        'eligibility_status'   => (string) get_post_meta( $oid, 'ws_aorg_eligibility_status', true ),
        'anonymous_pre_consult_status' => ws_q_bool_meta_yes_no( $oid, 'ws_aorg_anonymous_pre_consult_status' ),
        'has_attorneys'        => ws_q_bool_meta_yes_no( $oid, 'ws_aorg_has_attorneys' ),
        'attorney_role'        => (string) get_post_meta( $oid, 'ws_aorg_attorney_role', true ),
        'legal_representation_status' => (string) get_post_meta( $oid, 'ws_aorg_legal_representation_status', true ),
        'accreditation'        => (string) get_post_meta( $oid, 'ws_aorg_accreditation',              true ),
        'bar_states'           => (string) get_post_meta( $oid, 'ws_aorg_bar_states',                 true ),
        'legitimacy_url'       => $legitimacy_url,
        //'last_reviewed'        => (string) get_post_meta( $oid, 'ws_aorg_last_reviewed',              true ),
        'jurisdictions'        => $tax_jx['slugs'],
        'jurisdiction_labels'  => $tax_jx['names'],
        'has_extended_profile' => ! empty( $plain['is_reviewed'] ),
        'taxonomies' => [
            'jurisdiction'           => $tax_jx,
            'organization_model'     => $tax_organization_model,
            'protected_disclosures'  => $tax_protected_disclosure,
            'disclosure_targets'     => $tax_disclosure_target,
            'protected_classes'      => $tax_protected_class,
            'case_stages'            => $tax_case_stage,
            'aorg_services'          => $tax_services,
            'employment_sectors'     => $tax_employment,
            'languages'              => $tax_languages,
            'cost_models'            => $tax_cost_model,
        ],
        'plain'   => $plain,
        'verify'  => ws_build_source_verify_array( $oid ),
        'author'  => ws_build_author_array( $oid ),
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

    return ws_q_sort_assist_org_rows( $rows );
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
                'key'     => 'ws_aorg_is_nationwide',
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
    // $filters['concern_tax'] is either 'ws_protected_disclosure' or
    // 'ws_adverse_action' depending on the user's stage selection.
    if ( ! empty( $filters['concern'] ) && ! empty( $filters['concern_tax'] ) ) {
        $allowed_concern_taxonomies = [ 'ws_protected_disclosure', 'ws_adverse_action' ];
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

    return ws_q_sort_assist_org_rows( $rows );
}

