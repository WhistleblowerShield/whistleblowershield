<?php
/**
 * Site-wide shortcodes (not jurisdiction-assembler sections).
 *
 * For broader contracts and architecture notes, see:
 * - includes/shortcodes/README.md
 * - documentation/development/ws-core-output-layer.md
 *
 * @package WhistleblowerShield
 * @since   2.1.3
 * @version    3.20.1
 */

if ( ! defined( 'ABSPATH' ) ) exit;


// ── [ws_not_legal_advice_disclaimer_notice] ────────────────────────────────────────────────
//
// To update the notice text site-wide: edit $notice_text below.
// The change propagates to all jurisdiction pages automatically.
// Styling is handled by .ws-nla-disclaimer-notice in ws-core-front-general.css.

add_shortcode( 'ws_not_legal_advice_disclaimer_notice', function() {

    $notice_text = 'This page is provided for informational purposes only '
        . 'and does not constitute legal advice. The "Whistleblower Shield" '
        . 'is a database of legal information, not a law firm. Users should '
        . 'consult with a qualified legal professional regarding the specifics '
        . 'of their situation before initiating any formal disclosure or legal action.';

    return ws_render_not_legal_advice_disclaimer( $notice_text );

} );


// ── [ws_footer] ───────────────────────────────────────────────────────────────

add_shortcode( 'ws_footer', function() {

    return ws_render_footer( [
        'year'         => wp_date( 'Y' ),
        'policy_links' => [
            'Privacy Policy'     => '/privacy-policy/',
            'Disclaimer'         => '/disclaimer/',
            'Corrections Policy' => '/corrections-policy/',
            'Editorial Policy'   => '/editorial-policy/',
        ],
    ] );

} );


// ── [ws_legal_updates] ────────────────────────────────────────────────────────
//
// Renders recent legal updates for a specified jurisdiction, or site-wide
// if no jurisdiction parameter is given. Has defaults per scope when no count parameter is given
//
// Usage:
//   [ws_legal_updates jx="california"]           <- scoped to jurisdiction, summary types only, default count 5
//   [ws_legal_updates]                           <- site-wide, all types, default count 100
//
// Summary types are defined by WS_LEGAL_UPDATE_SUMMARY_TYPES in ws-core.php.
//
// DEPLOYMENT
// Use 1 -- Jurisdiction page (assembled by render-jurisdiction.php):
//   [ws_legal_updates jx="CA" count="5"]
//   Shows the last 5 summary-type updates scoped to the current jurisdiction.
//
// Use 2 -- Site-wide changelog page (standalone WP page, manually placed):
//   [ws_legal_updates]
//   Shows the last 100 updates of all types across all jurisdictions.


/**
 * Shortcode callback to render legal updates.
 *
 * Supported attributes: 'jx' (USPS code or jurisdiction post ID), 'count' (number of updates).
 *
 * @param array $atts Shortcode attributes.
 * @return string HTML output of the legal updates list.
 */
add_shortcode( 'ws_legal_updates', 'ws_shortcode_legal_updates' );
function ws_shortcode_legal_updates( $atts ) {

    $atts = shortcode_atts( [
        'jx'     => '',
        'count'  => 0,   // 0 = auto-resolve: 5 per-jurisdiction, 100 sitewide.
    ], $atts, 'ws_legal_updates' );

    // ── Resolve jurisdiction parameter to a post ID ───────────────────────
    //
    // Accepts: numeric jurisdiction post ID or USPS code ("CA").
    // All data reads are delegated to ws_get_legal_updates_data().

    $jx_id = 0;
    if ( ! empty( $atts['jx'] ) ) {
        if ( is_numeric( $atts['jx'] ) ) {
            $candidate = (int) $atts['jx'];
            $jx_id     = ( $candidate > 0 && get_post_type( $candidate ) === 'jurisdiction' ) ? $candidate : 0;
        } else {
            $jx_id = ws_get_id_by_code( strtoupper( trim( (string) $atts['jx'] ) ) );
        }

        // Fail closed when jx is provided but cannot be resolved.
        if ( ! $jx_id ) {
            return '';
        }
    }

    $items = ws_get_legal_updates_data( $jx_id, (int) $atts['count']);

    if ( empty( $items ) ) {
        return '';
    }

    return ws_render_legal_updates( $items );
}


// ── [ws_reference_page] + URL helper ───────────────────────────────────────

/**
 * Helper to build the reference page URL.
 *
 * Appends query args for the post_id and target section to the reference-materials page URL.
 *
 * @param int $post_id The post ID of the legal record.
 * @param string $section Optional section selector.
 * @return string Resolved reference page URL.
 */
function ws_get_reference_page_url( $post_id, $section = '' ) {
    $slug = apply_filters( 'ws_reference_page_slug', 'reference-materials' );
    $page = get_page_by_path( $slug );
    if ( ! $page ) return '';
    $args = [ 'post_id' => (int) $post_id ];
    if ( $section ) {
        $args['section'] = sanitize_key( $section );
    }
    return add_query_arg( $args, get_permalink( $page->ID ) );
}


/**
 * Shortcode callback to render the reference materials page.
 *
 * A single page with [ws_reference_page] serves all records using URL query parameters.
 *
 * @param array $atts Shortcode attributes.
 * @return string HTML output of the reference materials page.
 */
add_shortcode( 'ws_reference_page', 'ws_shortcode_reference_page' );
function ws_shortcode_reference_page( $atts ) {

    $atts    = shortcode_atts( [ 'post_id' => 0 ], $atts, 'ws_reference_page' );
    $post_id = (int) $atts['post_id'];

    // Accept post_id and section from URL query params.
    // A single page with [ws_reference_page] serves all records.
    if ( ! $post_id && isset( $_GET['post_id'] ) ) {
        $post_id = (int) $_GET['post_id'];
    }

    if ( ! $post_id ) {
        return '';
    }

    $data = ws_get_reference_page_data( $post_id );

    if ( null === $data ) {
        return '';
    }

    // Build back URL — anchor to the originating section when section param is present.
    // section is set by ws_get_reference_page_url() at each call site (statutes,
    // citations, constructions). Matches id="ws-{section}" wrappers in the assembler.
    $section  = isset( $_GET['section'] ) ? sanitize_key( $_GET['section'] ) : '';
    $back_url = $data['parent_url'];
    if ( $section ) {
        $back_url .= '#ws-' . $section;
    }

    // UI copy stays in shortcode layer so content edits do not require render changes.
    $ui_text = [
        'redirect_notice' => 'If you arrived here looking for help with your situation, you are likely in the wrong place. Use the link above to return to <a href="'
            . esc_url( $back_url ) . '">'
            . esc_html( $data['parent_title'] )
            . '</a>.',
        'empty_notice'    => 'No external references are currently available for this record.',
        'accuracy_notice' => 'External references are provided as additional sources of information. They have not been verified by WhistleblowerShield for accuracy or currency.',
    ];

    return ws_render_reference_page( $data, $back_url, $ui_text );
}


// ── [ws_jurisdiction_index] ───────────────────────────────────────────────────
//
// Renders the full filterable jurisdiction index. Intended for placement on a
// standalone WP page (e.g. the site home or a "Find a Jurisdiction" page).
// Not called by the assembler — editors place this manually.

add_shortcode( 'ws_jurisdiction_index', function() {
    $data = ws_get_jurisdiction_index_data();
    return ws_render_jurisdiction_index( $data );
} );


// ── [ws_assist_org_directory] ─────────────────────────────────────────────────
//
// Renders the nationwide assist-org directory. All shortcode attributes are
// optional — omitting them shows all organizations.
//
// Usage:
//   [ws_assist_org_directory]                              — all organizations
//   [ws_assist_org_directory model="nonprofit"]            — filtered by model
//   [ws_assist_org_directory sector="federal" stage="retaliation"]
//   [ws_assist_org_directory cost_model="pro_bono"]
//
// URL query params override shortcode attributes, enabling deep-links from
// jurisdiction pages to arrive with pre-applied filter state:
//
//   ?aorg_model=legal-aid&aorg_stage=retaliation
//
// URL param names:
//   aorg_model    — ws_organization_model slug
//   aorg_sector   — ws_employment_sector slug
//   aorg_stage    — ws_case_stage slug
//   aorg_cost     — ws_aorg_cost_model slug

add_shortcode( 'ws_assist_org_directory', 'ws_shortcode_assist_org_directory' );
/**
 * Directory shortcode entrypoint.
 *
 * Filter state is sourced from GET params via ws_resolve_filter_context()
 * so URLs remain shareable/bookmarkable.
 *
 * @param array<string,mixed> $atts Shortcode attributes (accepted for BC, ignored).
 * @return string HTML output.
 */
function ws_shortcode_assist_org_directory( $atts ) {

    // Phase 2: all filter resolution goes through ws_resolve_filter_context().
    // Shortcode atts are intentionally unused — filter state lives in GET params
    // so that filtered URLs are bookmarkable and shareable.
    //
    // Legacy atts (type, sector, stage, cost_model) are accepted but ignored.
    // Remove them from shortcode usage when convenient — they have no effect.

    // ── Resolve filter context ────────────────────────────────────────────
    $context = ws_resolve_filter_context();

    // ── Fetch results ─────────────────────────────────────────────────────
    // Targeted: jurisdiction-scoped orgs (empty for directory — directory
    // is nationwide by definition; targeted tier reserved for JX pages).
    //
    // For the directory, fetch ALL published nationwide orgs unfiltered.
    // Taxonomy filtering is handled by ws_filter_score_org() in the render
    // layer — scoring ranks relevant orgs to the top rather than excluding
    // non-matches. A strict tax_query AND across four axes would return
    // almost nothing and hide valid orgs from users.
    $targeted   = [];
    $nationwide = ws_get_nationwide_assist_org_data( [] );

    // ── Render ───────────────────────────────────────────────────────────
    // Wrapped in ws_render_or_fail_loud() — this is the live Phase 2
    // directory's entry point, the same treatment given the jurisdiction
    // and agency page assemblers in render-jurisdiction.php/render-agency.php.
    // An uncaught exception here must never reach a real visitor as a fatal
    // white screen, especially on the page currently taking the most direct
    // "who can help me" traffic on the site.
    return ws_render_or_fail_loud(
        function() use ( $targeted, $nationwide, $context ) {
            return ws_render_directory_page( $targeted, $nationwide, $context );
        },
        'shortcodes-general'
    );
}


// Query return contracts:
// - includes/shortcodes/README.md
// - includes/queries/README.md
// - documentation/development/ws-core-query-layer.md
