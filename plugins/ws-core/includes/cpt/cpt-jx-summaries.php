<?php
/**
 * cpt-jx-summary.php
 *
 * Registers the Jurisdiction Summary Custom Post Type.
 *
 * PURPOSE
 * -------
 * This CPT stores the plain-english legal summary explaining
 * whistleblower protections within a specific U.S. jurisdiction.
 *
 * Each jurisdiction should have exactly one associated summary record.
 *
 * The summary is not intended to be accessed directly by the public.
 * Instead it is rendered through the Jurisdiction page using
 * shortcodes or query layer functions.
 *
 * ARCHITECTURE
 * ------------
 * jurisdiction (public CPT)
 *      └── jx-summary (private dataset)
 *
 * Example relationship:
 *
 *      California (jurisdiction)
 *            ↳ California Summary (jx-summary)
 *
 * CONTENT GUIDELINES
 * ------------------
 * Summaries should be written in plain-english and designed
 * for readers who may not have legal training.
 *
 * The purpose is to provide:
 *
 *      • an overview of whistleblower protections
 *      • key legal principles
 *      • important warnings or limitations
 *      • guidance toward official resources
 *
 * The summary should not attempt to replicate statutory text.
 *
 * @package    WhistleblowerShield
 * @since      1.0.0
 * @version    3.20.0
 * @author     Whistleblower Shield
 * @link       https://whistleblowershield.org
 * @copyright  Copyright (c) Whistleblower Shield
 *
 */

defined( 'ABSPATH' ) || exit;

add_action( 'init', 'ws_register_cpt_jx_summary' );

function ws_register_cpt_jx_summary() {

    $labels = [
        'name'               => 'Jurisdiction Summaries',
        'singular_name'      => 'Jurisdiction Summary',
        'menu_name'          => 'JX Summaries',
        'name_admin_bar'     => 'JX Summary',
        'add_new'            => 'Add New',
        'add_new_item'       => 'Add New Jurisdiction Summary',
        'edit_item'          => 'Edit Jurisdiction Summary',
        'new_item'           => 'New Jurisdiction Summary',
        'view_item'          => 'View Summary',
        'search_items'       => 'Search Summaries',
        'not_found'          => 'No summaries found',
        'not_found_in_trash' => 'No summaries found in trash',
        'all_items'          => 'All Summaries',
    ];

    $args = [

        'labels'              => $labels,

        // ── Visibility ────────────────────────────────────────────────────
        // Private dataset — rendered through the parent jurisdiction page.
        // Not directly accessible to the public.

        'public'              => false,
        'show_ui'             => true,
        'show_in_menu'        => true,
        'publicly_queryable'  => false,
        'exclude_from_search' => true,
        'has_archive'         => false,
        'query_var'           => true,

        // ── Editor ────────────────────────────────────────────────────────

        'supports'            => [ 'title', 'editor', 'revisions' ],

        // ── REST ──────────────────────────────────────────────────────────

        'show_in_rest'        => true,

        // ── Admin Menu ────────────────────────────────────────────────────

        'menu_icon'       => 'dashicons-media-text',
        'menu_position'   => 28,

        // ── Capabilities ──────────────────────────────────────────────────

        'capability_type' => 'post',
        'rewrite'         => [ 'slug' => 'jx-summary', 'with_front' => false ],

    ];

    // Slug uses hyphen convention — must match ACF location rules
    // and relationship field post_type references throughout ws-core.
    register_post_type( 'jx-summary', $args );
}
