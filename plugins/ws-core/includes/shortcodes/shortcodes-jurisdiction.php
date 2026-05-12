<?php
/**
 * Jurisdiction page shortcodes.
 *
 * These shortcodes are assembler-facing (render-jurisdiction.php) and route
 * all reads through the query layer (no direct meta calls in shortcode logic).
 *
 * For deep return-shape references, use:
 * - includes/queries/README.md
 * - documentation/development/ws-core-query-layer.md
 *
 * @package WhistleblowerShield
 * @since   2.1.0
 * @version    3.20.0
 */

if ( ! defined( 'ABSPATH' ) ) exit;


// ── [ws_jx_header] ────────────────────────────────────────────────────────────

add_shortcode( 'ws_jx_header', function( $atts ) {

    $atts    = shortcode_atts( [ 'jx' => '' ], $atts );
    $jx_data = ws_get_jurisdiction_data( $atts['jx'] ?: null );

    if ( ! $jx_data ) return '';

    $labels = [
        'state'     => 'State Leadership Offices',
        'territory' => 'Territory Leadership Offices',
        'district'  => 'District Leadership Offices',
        'federal'   => 'Federal Offices',
    ];
    $box_label = $labels[ $jx_data['class'] ] ?? 'Official Leadership Offices';

    $head_label  = $jx_data['gov']['executive_label']   ?? 'Office of the Governor';
    $legal_label = $jx_data['gov']['authority_label']     ?? 'Office of the Attorney General';

    $render_data = [
        'jx_name'   => $jx_data['name'],
        'flag_data' => [
            'image'      => $jx_data['flag']['image_url'],
            'source_url' => $jx_data['flag']['source_url'],
            'attr_str'   => $jx_data['flag']['attribution'], 
            'license'    => $jx_data['flag']['license'],
        ],
        'gov_data' => [
            'box_label' => $box_label,
            'links'     => [
                [ 'url' => $jx_data['gov']['portal_url'],       'label' => $jx_data['gov']['portal_label']       ?: 'Official Government Portal' ],
                [ 'url' => $jx_data['gov']['executive_url'],    'label' => $head_label ],
                [ 'url' => $jx_data['gov']['authority_url'],    'label' => $legal_label ],
                [ 'url' => $jx_data['gov']['legislature_url'],  'label' => $jx_data['gov']['legislature_label']  ?: 'State Legislature' ],
            ],
        ],
    ];

    return ws_render_jx_header( $render_data );

} );


// ── [ws_jx_summary] ───────────────────────────────────────────────────────────
//
// All field reads delegated to ws_get_jx_summary_data() in the query layer.
// Phase 9.1 refactor: no direct get_field() / get_post_meta() calls here.

add_shortcode( 'ws_jx_summary', function() {

    global $post;
    if ( ! $post ) return '';

    $term_id = ws_get_jx_term_id( $post->ID );
    if ( ! $term_id ) return '';

    $data = ws_get_jx_summary_data( $term_id );
    if ( ! $data || empty( $data['content'] ) ) return '';

    // wp_kses_post() is correct here — do not replace with apply_filters('the_content', ...).
    // Summary content comes from an ACF WYSIWYG meta field (ws_jx_summary_wysiwyg),
    // not from post_content. The HTML is already fully formed by the ACF editor. Running
    // the_content filters would double-wrap paragraphs via wpautop, expand any shortcodes
    // embedded in the legal text, and trigger block rendering — none of which is appropriate
    // for a meta-stored WYSIWYG field. wp_kses_post() sanitizes without over-processing.
    // Statute content uses apply_filters('the_content', ...) because it reads post_content
    // directly, which requires block rendering and wpautop.
    //
    // Footer (attribution, review badge, sources) is rendered last by the assembler
    // via ws_render_jx_curated() so it follows limitations and legal updates.
    $jx_data  = ws_get_jurisdiction_data( $post->ID );
    $jx_name  = $jx_data ? $jx_data['name'] : '';
    $html = apply_filters( 'ws_glossary_scan', wp_kses_post( $data['content'] ) );
    $html = apply_filters( 'ws_statute_bold_scan', $html, $jx_name );
    return ws_render_jx_summary_section( $html );

} );


// ── [ws_jx_statutes] ─────────────────────────────────────────────────────────
//
// Fetches attached jx-statute records via the query layer and renders them
// using the two-group pattern. Local (state/territory) records are rendered
// first in .ws-section--local; federal (US) records follow in
// .ws-section--federal. When all records share the same scope (US jurisdiction
// or no federal records), a single flat section is rendered.

add_shortcode( 'ws_jx_statutes', 'ws_shortcode_jx_statutes' );
/**
 * Renders jurisdiction statutes section (local + optional federal append).
 *
 * @return string
 */
function ws_shortcode_jx_statutes() {

    global $post;
    if ( ! $post ) return '';

    $term_id = ws_get_jx_term_id( $post->ID );
    if ( ! $term_id ) return '';

    $statutes = ws_get_jx_statute_data( $term_id );
    if ( empty( $statutes ) ) return '';

    // Check whether any federal records exist.
    $has_fed = false;
    foreach ( $statutes as $s ) {
        if ( $s['is_fed'] ) { $has_fed = true; break; }
    }

    // Helper: build HTML for one statute block including the optional
    // "→ External References" button. The button is omitted when no
    // approved ws-reference items are linked to this statute record.
    $build_statute_chunk = function( $statute ) {
        $html = apply_filters( 'the_content', $statute['content'] );

        $refs     = ws_get_ref_materials( $statute['id'] );
        $ref_url  = ! empty( $refs ) ? ws_get_reference_page_url( $statute['id'], 'statutes' ) : '';

        if ( $ref_url ) {
            $html .= '<div class="ws-ref-materials-link">'
                   . '<a href="' . esc_url( $ref_url ) . '" class="ws-ref-materials-btn" target="_blank">'
                   . '&rarr; External References'
                   . '</a>'
                   . '</div>';
        }

        // Authoritative cross-reference: procedures that operate under this statute.
        // Queries ag-procedure posts linking to this statute via _ws_ag_procedure_parent_ids.
        // Returns '' when none exist — no section rendered for statutes with no procedures.
        $procs = ws_get_procedures_for_record( $statute['id'] );
        if ( ! empty( $procs ) ) {
            $html .= ws_render_record_procedures( $procs, 'statute' );
        }

        return $html;
    };

    if ( ! $has_fed ) {
        // Single-group render: no federal append.
        $content = '';
        foreach ( $statutes as $statute ) {
            $content .= $build_statute_chunk( $statute );
        }
        return ws_render_section( 'Relevant Statutes', $content, 'ws-statutes--local' );
    }

    // Two-group render: split local vs federal.
    $local_html = '';
    $fed_html   = '';
    foreach ( $statutes as $statute ) {
        $chunk = $build_statute_chunk( $statute );
        if ( $statute['is_fed'] ) {
            $fed_html   .= $chunk;
        } else {
            $local_html .= $chunk;
        }
    }

    return ws_render_section_two_group( 'Relevant Statutes', $local_html, 'Federal Statutes', $fed_html );
}


// ── [ws_jx_common_law] ─────────────────────────────────────────────────────────
// Common law section is currently a placeholder, copied from the original
// shortcode in the jx-statute template. Not yet implemented, pending further
// research and design. The shortcode is registered here in anticipation of that
// work, but currently returns an empty string.
//
// Fetches attached jx-common-law records via the query layer and renders them.
// Unlike jx-statute, a single flat section is rendered. No federal append is needed
// because common law is inherently jurisdiction-specific and no US-scoped records
// are expected.

add_shortcode( 'ws_jx_common_law', 'ws_shortcode_jx_common_law' );
/**
 * Renders jurisdiction common law section.
 *
 * @return string
 */
function ws_shortcode_jx_common_law() {

    global $post;
    if ( ! $post ) return '';

    $term_id = ws_get_jx_term_id( $post->ID );
    if ( ! $term_id ) return '';

    $comlaws = ws_get_jx_common_law_data( $term_id );
    if ( empty( $comlaws ) ) return '';

    // Check whether any federal records exist.
    //$has_fed = false;
    //foreach ( $comlaws as $s ) {
    //    if ( $s['is_fed'] ) { $has_fed = true; break; }
    //}

    // Helper: build HTML for one statute block including the optional
    // "→ External References" button. The button is omitted when no
    // approved ws-reference items are linked to this statute record.
    $build_comlaw_chunk = function( $comlaw ) {
        $html = apply_filters( 'the_content', $comlaw['content'] );

        $refs     = ws_get_ref_materials( $comlaw['id'] );
        $ref_url  = ! empty( $refs ) ? ws_get_reference_page_url( $comlaw['id'], 'ws-section--local' ) : '';

        if ( $ref_url ) {
            $html .= '<div class="ws-ref-materials-link">'
                   . '<a href="' . esc_url( $ref_url ) . '" class="ws-ref-materials-btn" target="_blank">'
                   . '&rarr; External References'
                   . '</a>'
                   . '</div>';
        }

        // Authoritative cross-reference: procedures that operate under this statute.
        // Queries ag-procedure posts linking to this statute via _ws_ag_procedure_parent_ids.
        // Returns '' when none exist — no section rendered for statutes with no procedures.
        $procs = ws_get_procedures_for_record( $comlaw['id'] );
        if ( ! empty( $procs ) ) {
            $html .= ws_render_record_procedures( $procs, 'common_law' );
        }

        return $html;
    };

    $content = '';
    foreach ( $comlaws as $comlaw ) {
            $content .= $build_comlaw_chunk( $comlaw );
        }
    return ws_render_section( 'Relevant Common Laws', $content, 'ws-common-laws--local' );

}

// ── [ws_jx_citation] ─────────────────────────────────────────────────────────
//
// Queries published jx-citation records for the current jurisdiction
// where ws_jx_citation_has_attach_flag is true, ordered by ws_jx_citation_display_order.
// Renders the full ws-case-law section: footnote anchors in the body
// and a footnote list with Unicode return links (&#x21a9;) at the foot.
//
// Returns empty string silently if no attached citations exist.
// A warning notice on the jx-summary edit screen covers that gap
// — see ws_jx_cite_no_citations_notice() in acf-jx-citations.php.
//
// Unicode return character: ↩ (U+21A9) replaces the PNG workaround.
// Controlled via .ws-footnote-return in ws-core-front.css.

add_shortcode( 'ws_jx_citation', 'ws_shortcode_jx_citation' );
/**
 * Renders jurisdiction citation section.
 *
 * @return string
 */
function ws_shortcode_jx_citation() {

    global $post;
    if ( ! $post ) return '';

    // Resolve WS_JURISDICTION_TAXONOMY taxonomy term for the current jurisdiction post.
    $term_id = ws_get_jx_term_id( $post->ID );
    if ( ! $term_id ) return '';

    // Fetch attached citations via query layer (ordered by 'order' ASC).
    // Returns mixed local + federal records with is_fed flag on each.
    $citations = ws_get_jx_citation_data( $term_id );

    if ( empty( $citations ) ) return '';

    // Helper: build an array of footnote item HTML strings from a citation slice.
    $build_items = function( $slice, $fn_start, $id_prefix ) {
        $items    = [];
        $fn_index = $fn_start;
        foreach ( $slice as $citation ) {
            $label  = $citation['label'];
            $url    = $citation['cite_url'];
            $is_pdf = $citation['is_pdf'];

            $pdf_suffix = $is_pdf ? ' (PDF)' : '';
            $fn_id = $id_prefix . '-fn-' . $fn_index;

            if ( $url ) {
                $linked_label = '<a href="' . esc_url( $url ) . '" target="_blank" rel="noopener noreferrer">'
                              . esc_html( $label ) . esc_html( $pdf_suffix ) . '</a>';
            } else {
                $linked_label = esc_html( $label ) . esc_html( $pdf_suffix );
            }

            // Unicode return character: ↩ (U+21A9), styled via .ws-footnote-return.
            // Links back to the in-text superscript anchor using the convention:
            //   id="{prefix}-fn-ref-{n}"  e.g. id="all-fn-ref-1"
            // Editors writing HTML blobs reference citations as:
            //   <sup><a href="#all-fn-1" id="all-fn-ref-1">1</a></sup>
            // Prefix is 'all' (single group), 'local', or 'fed' (two-group).
            $ref_target  = '#' . $id_prefix . '-fn-ref-' . $fn_index;
            $return_link = '<a href="' . esc_attr( $ref_target ) . '" class="ws-footnote-return" aria-label="Return to in-text reference">&#x21a9;</a>';

            // "→ External References" button — only when approved references exist.
            $ref_btn = '';
            $refs    = ws_get_ref_materials( $citation['id'] );
            if ( ! empty( $refs ) ) {
                $ref_url = ws_get_reference_page_url( $citation['id'], 'citations' );
                if ( $ref_url ) {
                    $ref_btn = ' <a href="' . esc_url( $ref_url ) . '" '
                             . 'class="ws-ref-materials-btn ws-ref-materials-btn--inline" target="_blank">'
                             . '&rarr; External References'
                             . '</a>';
                }
            }

            $items[] = '<small id="' . esc_attr( $fn_id ) . '">'
                     . $return_link . ' '
                     . $fn_index . '. '
                     . $linked_label
                     . $ref_btn
                     . '</small>';

            $fn_index++;
        }
        return $items;
    };

    // Check whether any federal records exist.
    $local = array_values( array_filter( $citations, fn( $c ) => ! $c['is_fed'] ) );
    $fed   = array_values( array_filter( $citations, fn( $c ) =>   $c['is_fed'] ) );

    if ( empty( $fed ) ) {
        // Single-group: no federal append.
        return ws_render_jx_citations( $build_items( $citations, 1, 'all' ) );
    }

    // Two-group: local and federal citations keep independent visible numbering
    // but use distinct DOM ID prefixes so anchor targets remain unique.
    $out  = ws_render_jx_citations( $build_items( $local, 1, 'local' ), 'ws-section--local' );
    $out .= ws_render_jx_citations( $build_items( $fed,   1, 'fed' ), 'ws-section--federal' );
    return $out;
}


// ── [ws_jx_construction_] ───────────────────────────────────────────────────
//
// Queries published jx-construction records for the current jurisdiction
// where ws_jx_construction_has_attach_flag is true, ordered by ws_jx_construction_display_order ASC.
// Appends US-scoped records (federal court decisions) to state pages via
// the same is_fed pattern used by statutes and citations.
//
// Rendering is delegated to ws_render_jx_construction_s() in render-section.php.
// Returns empty string silently if no attached constructions exist.

add_shortcode( 'ws_jx_construction_', 'ws_shortcode_jx_construction_' );
/**
 * Renders jurisdiction construction section.
 *
 * @return string
 */
function ws_shortcode_jx_construction_() {

    global $post;
    if ( ! $post ) return '';

    $term_id = ws_get_jx_term_id( $post->ID );
    if ( ! $term_id ) return '';

    $constructs = ws_get_jx_construction_data( $term_id );
    if ( empty( $constructs ) ) return '';

    return ws_render_jx_construction_s( $constructs );
}


// ── [ws_jx_limitations] ──────────────────────────────────────────────────────
//
// Reads the ws_jx_summary_limitations repeater from the linked jx-summary post
// and renders the Limitations and Ramifications section.
// Returns empty string silently if no rows are saved or no summary
// is linked to the current jurisdiction.

add_shortcode( 'ws_jx_limitations', 'ws_shortcode_jx_limitations' );
/**
 * Renders summary limitations section.
 *
 * @return string
 */
function ws_shortcode_jx_limitations() {

    global $post;
    if ( ! $post ) return '';

    $term_id = ws_get_jx_term_id( $post->ID );
    if ( ! $term_id ) return '';

    $data = ws_get_jx_summary_data( $term_id );
    if ( ! $data || empty( $data['limitations'] ) ) return '';

    return ws_render_jx_limitations( $data['limitations'] );
}


// Query return contracts are documented in:
// - includes/shortcodes/README.md
// - includes/queries/README.md
// - documentation/development/ws-core-query-layer.md
