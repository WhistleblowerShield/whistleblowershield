<?php
/**
 * cpt-ag-procedures.php — Registers the ag-procedure CPT.
 *
 * @package WhistleblowerShield
 * @since   3.9.0
 * @version 3.10.0
 */

defined( 'ABSPATH' ) || exit;

add_action( 'init', 'ws_register_cpt_ag_procedures' );

function ws_register_cpt_ag_procedures() {

    $labels = [
        'name'               => 'Procedures',
        'singular_name'      => 'Procedure',
        'menu_name'          => 'AG Procedures',
        'name_admin_bar'     => 'AG Procedure',
        'add_new'            => 'Add New',
        'add_new_item'       => 'Add New Procedure',
        'edit_item'          => 'Edit Procedure',
        'new_item'           => 'New Procedure',
        'view_item'          => 'View Procedure',
        'search_items'       => 'Search Procedures',
        'not_found'          => 'No procedures found',
        'not_found_in_trash' => 'No procedures found in trash',
        'all_items'          => 'All Procedures',
    ];

    $args = [
        'labels'             => $labels,

        // -- Visibility ----------------------------------------------------

        'public'              => true,
        'show_ui'             => true,
        'show_in_menu'        => true,
        'publicly_queryable'  => true,
        'exclude_from_search' => false,
        'has_archive'         => 'procedures',
        'query_var'           => true,

        // -- Editor --------------------------------------------------------
        // No editor — all content is stored in ACF fields.

        'supports'            => [ 'title', 'revisions' ],
        'rewrite'             => [ 'slug' => 'ag-procedure', 'with_front' => false ],
        'capability_type'     => 'post',

        // -- REST ----------------------------------------------------------
        // Enabled for Block Editor and ACF AJAX support.

        'show_in_rest'        => true,

        // -- Admin Menu ----------------------------------------------------

        'menu_icon'           => 'dashicons-clipboard',
        'menu_position'       => 41,

        // -- Taxonomies ----------------------------------------------------

        'taxonomies'          => [ 'ws_protected_disclosure' ],
    ];

    register_post_type( 'ag-procedure', $args );
}

// Admin columns for ag-procedure are registered in admin-columns.php. Do not register
// manage_ag-procedure_posts_columns or manage_ag-procedure_posts_custom_column hooks
// here — duplicate registrations cause conflicting column sets. All CPT column logic lives
// in admin-columns.php.
