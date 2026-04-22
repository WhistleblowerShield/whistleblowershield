<?php
/**
 * cpt-jx-constructions.php — Registers the jx-construction CPT.
 *
 * Stores court constructions of whistleblower statutes. Each record
 * captures one case — citation, court, holding, favorable flag.
 * Linked to parent statute via ws_jx_construction_statute_id (post_object).
 * Scoped via ws_jurisdiction taxonomy term.
 *
 * Created via "Add New construction" button in admin-construction-metabox.php
 * on the jx-statute edit screen.
 *
 * @package WhistleblowerShield
 * @since   2.4.0
 * @version 3.10.0
 *
 * VERSION
 * -------
 * 2.4.0   Initial release.
 * 2.4.1   menu_position corrected from 28 to 29.
 */

defined( 'ABSPATH' ) || exit;

add_action( 'init', 'ws_register_cpt_jx_construction' );

function ws_register_cpt_jx_construction() {

    $labels = [
        'name'               => 'Legal constructions',
        'singular_name'      => 'Legal construction',
        'menu_name'          => 'JX constructions',
        'name_admin_bar'     => 'JX construction',
        'add_new'            => 'Add New',
        'add_new_item'       => 'Add New Legal construction',
        'edit_item'          => 'Edit Legal construction',
        'new_item'           => 'New Legal construction',
        'view_item'          => 'View construction',
        'search_items'       => 'Search constructions',
        'not_found'          => 'No constructions found',
        'not_found_in_trash' => 'No constructions found in trash',
        'all_items'          => 'All constructions',
    ];

    $args = [

        'labels'             => $labels,

        // ── Visibility ────────────────────────────────────────────────────
        // Private dataset — surfaced via the statute edit screen meta box
        // and the public jurisdiction page render layer.

        'public'              => false,
        'show_ui'             => true,
        'show_in_menu'        => true,
        'publicly_queryable'  => false,
        'exclude_from_search' => true,
        'has_archive'         => false,
        'query_var'           => true,

        // ── Editor ────────────────────────────────────────────────────────
        // Title used as the case name in the admin list.
        // No post editor — all content managed via ACF fields.

        'supports'            => [ 'title', 'revisions' ],
        'taxonomies' => [
            'ws_disclosure_type',
            'ws_process_type',
            'ws_remedy',
            'ws_protected_class',
            'ws_adverse_action_type',
            'ws_disclosure_target',
            'ws_fee_shifting',
            'ws_employer_defense',
            'ws_employee_standard',
            WS_JURISDICTION_TAXONOMY,
        ],

        // ── REST ──────────────────────────────────────────────────────────

        'show_in_rest'        => true,

        // ── Admin Menu ────────────────────────────────────────────────────
        // Citations 27 → Agencies 28 → constructions 29 → Assist Orgs 30

        'menu_icon'       => 'dashicons-hammer',
        'menu_position'   => 34,

        // ── Capabilities ──────────────────────────────────────────────────

        'capability_type' => 'post',
        'rewrite'         => false,

    ];

    register_post_type( 'jx-construction', $args );
}
