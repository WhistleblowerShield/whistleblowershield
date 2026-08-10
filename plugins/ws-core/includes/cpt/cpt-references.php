<?php
/**
 * cpt-ws-reference.php
 *
 * Registers the ws-reference Custom Post Type.
 *
 * PURPOSE
 * -------
 * Stores external reference materials — case law, academic papers, government
 * reports, journalism, and policy analysis — attached to individual statute,
 * citation, or construction records via a relationship field on the parent.
 *
 * AUDIENCE
 * --------
 * Secondary users only: legal researchers, journalists, attorneys.
 * Primary users (whistleblowers in crisis) should never need this CPT.
 * The reference page disclaimer redirects them back to operational content.
 *
 * ARCHITECTURE
 * ------------
 * ws-reference is NOT part of the jurisdiction assembly pipeline.
 * Records are attached to individual jx-statute, jx-citation, or
 * jx-construction posts via the ws_ref_materials relationship field
 * defined on each parent CPT. They are never rendered directly on
 * jurisdiction pages.
 *
 * Access via: /references/?source=[parent_post_id]
 * Rendered by: [ws_reference_page] shortcode in shortcodes-general.php
 * Static page: must be created manually in WP admin with the slug 'references'
 * and [ws_reference_page] as its only content.
 *
 * @package    WhistleblowerShield
 * @since      3.3.0
 * @version    3.20.0
 * @author     Whistleblower Shield
 * @link       https://whistleblowershield.org
 * @copyright  Copyright (c) Whistleblower Shield
 *
 */

defined( 'ABSPATH' ) || exit;

add_action( 'init', 'ws_register_cpt_ws_reference' );

/**
 * Registers the 'ws-reference' custom post type.
 *
 * Hooked to 'init'.
 *
 * @return void
 */
function ws_register_cpt_ws_reference() {

    $labels = [
        'name'               => 'References',
        'singular_name'      => 'Reference',
        'menu_name'          => 'References',
        'name_admin_bar'     => 'Reference',
        'add_new'            => 'Add New',
        'add_new_item'       => 'Add New Reference',
        'new_item'           => 'New Reference',
        'edit_item'          => 'Edit Reference',
        'view_item'          => 'View Reference',
        'all_items'          => 'All References',
        'search_items'       => 'Search References',
        'not_found'          => 'No references found',
        'not_found_in_trash' => 'No references found in trash',
    ];

    $args = [

        'labels' => $labels,

        // ── Visibility ────────────────────────────────────────────────────
        // Public with permalink so the reference page is reachable via URL.
        // No archive — records are accessed only via the parent post relationship.

        'public'              => false,
        'show_ui'             => true,
        'show_in_menu'        => true,
        'publicly_queryable'  => false,
        'exclude_from_search' => true,
        'has_archive'         => false,
        'query_var'           => true,
        'rewrite'             => [ 'slug' => 'ws-reference', 'with_front' => false ],

        // ── Editor ────────────────────────────────────────────────────────

        'supports'     => [ 'title', 'editor', 'thumbnail' ],
        'show_in_rest' => true,

        // ── Capabilities ──────────────────────────────────────────────────

        'capability_type' => 'post',

        // ── Admin Menu ────────────────────────────────────────────────────

        'menu_icon'     => 'dashicons-book-alt',
        'menu_position' => 52,

    ];

    register_post_type( 'ws-reference', $args );
}
