<?php
/**
 * cpt-jx-citations.php — Registers the jx-citation CPT.
 *
 * Stores case law and regulatory citations for a jurisdiction.
 * Scoped via WS_JURISDICTION_TAXONOMY taxonomy term. Not publicly queryable —
 * content surfaces on jurisdiction pages via the Assembly Layer only.
 * attach_flag + ws_display_order control what appears on curated summary views.
 *
 * @package WhistleblowerShield
 * @since   2.3.0
 * @version 3.10.0
 *
 * VERSION
 * -------
 * 2.3.0   Initial release.
 * 3.0.0   ws_jx_code join retired; WS_JURISDICTION_TAXONOMY taxonomy used throughout.
 */

defined( 'ABSPATH' ) || exit;

add_action( 'init', 'ws_register_cpt_jx_citation' );

function ws_register_cpt_jx_citation() {

    $labels = [
        'name'               => 'Jurisdiction Citations',
        'singular_name'      => 'Jurisdiction Citation',
        'menu_name'          => 'JX Citations',
        'name_admin_bar'     => 'JX Citation',
        'add_new'            => 'Add New',
        'add_new_item'       => 'Add New Jurisdiction Citation',
        'edit_item'          => 'Edit Jurisdiction Citation',
        'new_item'           => 'New Jurisdiction Citation',
        'view_item'          => 'View Citation',
        'search_items'       => 'Search Citations',
        'not_found'          => 'No citations found',
        'not_found_in_trash' => 'No citations found in trash',
        'all_items'          => 'All Citations',
    ];

    $args = [

        'labels'             => $labels,

        // ── Visibility ────────────────────────────────────────────────────
        // Private dataset — rendered through the jurisdiction page assembler.
        // Not directly accessible to the public.

        'public'              => false,
        'show_ui'             => true,
        'show_in_menu'        => true,
        'publicly_queryable'  => false,
        'exclude_from_search' => true,
        'has_archive'         => false,
        'query_var'           => true,
        
        // ── Editor ────────────────────────────────────────────────────────
        // Title used as the citation display label in the admin list.
        // No post editor — all content managed via ACF fields.

        'supports'            => [ 'title', 'revisions' ],
        'taxonomies' => [
            'ws_protection_scope',
            'ws_protected_disclosure',
            'ws_process_type',
            'ws_remedy',
            'ws_protected_class',
            'ws_excluded_class',
            'ws_adverse_action',
            'ws_disclosure_target',
            'ws_fee_shifting_rule',
            'ws_employee_standard',
            'ws_employer_defense',
            'ws_protected_action',
            'ws_causation_standard',
            'ws_legal_recognition',
             WS_JURISDICTION_TAXONOMY,
        ],

        // ── REST ──────────────────────────────────────────────────────────

        'show_in_rest'        => true,

        // ── Admin Menu ────────────────────────────────────────────────────

        'menu_icon'       => 'dashicons-book-alt',
        'menu_position'   => 33,

        // ── Capabilities ──────────────────────────────────────────────────

        'capability_type' => 'post',
        'rewrite'         => [ 'slug' => 'jx-citation', 'with_front' => false ],

    ];

    register_post_type( 'jx-citation', $args );
}
