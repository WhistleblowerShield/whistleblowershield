<?php
/**
 * query-general.php
 *
 * General Query Layer
 *
 * PURPOSE
 * -------
 * Holds cross-cutting query functions that are not jurisdiction-dataset
 * specific but are consumed by shared/general shortcodes and renderers.
 *
 * LOAD ORDER
 * ----------
 * Must load after query-jurisdiction.php because legal updates use
 * ws_get_jx_term_id() for jurisdiction scoping.
 *
 * @package    WhistleblowerShield
 * @since      3.10.5
 * @version    3.17.0
 * @author     Whistleblower Shield
 * @link       https://whistleblowershield.org
 * @copyright  Copyright (c) Whistleblower Shield
 *
 */

defined( 'ABSPATH' ) || exit;


// ════════════════════════════════════════════════════════════════════════════
// Legal Updates
// ════════════════════════════════════════════════════════════════════════════

function ws_get_legal_updates_data( $jx_id = 0, $count = 0 ) {

    if ( ! $count ) {
        $count = $jx_id ? 5 : 100;
    }

    // One 100-item transient covers all sitewide requests <= 100.
    if ( ! $jx_id && $count <= 100 ) {
        $cached = get_transient( WS_CACHE_LEGAL_UPDATES_SITEWIDE );
        if ( false !== $cached ) {
            return array_slice( $cached, 0, $count );
        }
    }

    // Always fetch 100 for sitewide cacheable calls, exact count otherwise.
    $fetch_count = ( ! $jx_id && $count <= 100 ) ? 100 : $count;

    $query_args = [
        'post_type'      => 'ws-legal-update',
        'post_status'    => 'publish',
        'posts_per_page' => $fetch_count,
        'orderby'        => 'date',
        'order'          => 'DESC',
        'no_found_rows'  => true,
        // Hidden updates remain in admin but are excluded from public render.
        'meta_query'     => [
            'relation' => 'AND',
            [
                'relation' => 'OR',
                [
                    'key'     => 'ws_legal_update_hide_public',
                    'compare' => 'NOT EXISTS',
                ],
                [
                    'key'     => 'ws_legal_update_hide_public',
                    'value'   => '1',
                    'compare' => '!=',
                ],
            ],
        ],
    ];

    if ( $jx_id ) {
        $term_id = ws_get_jx_term_id( $jx_id );
        if ( $term_id ) {
            $query_args['tax_query'] = [ [
                'taxonomy' => WS_JURISDICTION_TAXONOMY,
                'field'    => 'term_id',
                'terms'    => $term_id,
            ] ];
        }
        // Jurisdiction-scoped calls restrict to summary-safe update types only.
        $query_args['meta_query'][] = [
            'key'     => 'ws_legal_update_type',
            'value'   => WS_LEGAL_UPDATE_SUMMARY_TYPES,
            'compare' => 'IN',
        ];
    }

    $updates = get_posts( $query_args );

    if ( empty( $updates ) ) {
        return [];
    }

    $items = [];
    foreach ( $updates as $update ) {
        $uid     = $update->ID;
        $items[] = [
            'id'                 => $uid,
            'title'              => get_the_title( $uid ),
            'effective_date'     => get_post_meta( $uid, 'ws_legal_update_effective_date',   true ),
            'post_date'          => get_post_field( 'post_date', $uid ),
            'type'               => get_post_meta( $uid, 'ws_legal_update_type',                      true ),
            'multi_jurisdiction' => (bool) get_post_meta( $uid, 'ws_legal_update_multi_jurisdiction', true ),
            'law_name'           => get_post_meta( $uid, 'ws_legal_update_law_name',         true ) ?: '',
            'source_url'         => get_post_meta( $uid, 'ws_legal_update_source_url',       true ) ?: '',
            'source_url_is_pdf'  => (bool) get_post_meta( $uid, 'ws_legal_update_source_url_is_pdf', true ),
            'summary'            => wp_kses_post( get_post_meta( $uid, 'ws_legal_update_summary_wysiwyg', true ) ?: '' ),
            'verify'             => ws_build_source_verify_array( $uid ),
            'author'             => ws_build_author_array( $uid ),
        ];
    }

    if ( ! $jx_id && $count <= 100 ) {
        set_transient( WS_CACHE_LEGAL_UPDATES_SITEWIDE, $items, HOUR_IN_SECONDS );
        return array_slice( $items, 0, $count );
    }

    return $items;
}


// Invalidate sitewide legal updates cache when legal update posts change.
add_action( 'save_post_ws-legal-update', function() {
    delete_transient( WS_CACHE_LEGAL_UPDATES_SITEWIDE );
} );
add_action( 'before_delete_post', function( $post_id ) {
    if ( get_post_type( $post_id ) === 'ws-legal-update' ) {
        delete_transient( WS_CACHE_LEGAL_UPDATES_SITEWIDE );
    }
} );


// ════════════════════════════════════════════════════════════════════════════
// Reference Materials
// ════════════════════════════════════════════════════════════════════════════

function ws_get_ref_materials( $post_id ) {
    $post_id = (int) $post_id;
    if ( ! $post_id ) return [];

    $post_type = get_post_type( $post_id );
    $ref_field_by_type = [
        'jx-statute'        => 'ws_jx_statute_ref_materials',
        'jx-citation'       => 'ws_jx_citation_ref_materials',
        'jx-construction' => 'ws_jx_construction_ref_materials',
        'jx-common-law'     => 'ws_jx_comlaw_ref_materials',
    ];
    $ref_field = $ref_field_by_type[ $post_type ] ?? '';

    $refs = get_field( $ref_field, $post_id );
    if ( ! is_array( $refs ) || empty( $refs ) ) return [];

    $items = [];
    foreach ( $refs as $ref ) {
        $rid = 0;
        if ( $ref instanceof WP_Post ) {
            $rid = (int) $ref->ID;
        } elseif ( is_object( $ref ) && isset( $ref->ID ) ) {
            $rid = (int) $ref->ID;
        } elseif ( is_numeric( $ref ) ) {
            $rid = (int) $ref;
        }
        if ( ! $rid ) continue;

        $title = get_post_meta( $rid, 'ws_ref_title', true );
        if ( empty( $title ) ) {
            $title = get_the_title( $rid );
        }

        $items[] = [
            'title'       => sanitize_text_field( $title ),
            'url'         => esc_url_raw( get_post_meta( $rid, 'ws_ref_url', true ) ),
            'is_pdf'      => (bool) get_post_meta( $rid, 'ws_ref_url_is_pdf', true ),
            'description' => sanitize_textarea_field( get_post_meta( $rid, 'ws_ref_description', true ) ),
            'type'        => sanitize_text_field( get_post_meta( $rid, 'ws_ref_type', true ) ),
            'source_name' => sanitize_text_field( get_post_meta( $rid, 'ws_ref_source_name', true ) ),
        ];
    }

    return $items;
}


function ws_get_reference_page_data( $parent_post_id ) {
    $parent_post_id = (int) $parent_post_id;
    if ( ! $parent_post_id ) return null;

    $allowed_types = WS_REF_PARENT_TYPES;
    if ( ! in_array( get_post_type( $parent_post_id ), $allowed_types, true ) ) return null;

    return [
        'parent_title' => get_the_title( $parent_post_id ),
        'parent_url'   => get_permalink( $parent_post_id ),
        'references'   => ws_get_ref_materials( $parent_post_id ),
    ];
}
