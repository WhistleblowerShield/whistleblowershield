<?php
/**
 * admin-interpretation-metabox.php
 *
 * Adds a "Federal Court Interpretations" meta box to the jx-statute edit screen.
 *
 * PURPOSE
 * -------
 * Displays all jx-interpretation records linked to the current statute,
 * with a direct "Add New Interpretation" button that opens the creation
 * form in a new tab. Renders on all jx-statute posts — both federal and
 * state statutes can have court interpretation records.
 *
 * WORKFLOW
 * --------
 * 1. Editor saves a jx-statute post.
 * 2. The meta box appears below the ACF field groups.
 * 3. Existing interpretations are listed: case name, court, year, favorable?,
 *    and an Edit link.
 * 4. "Add New Interpretation" opens:
 *    post-new.php?post_type=jx-interpretation&statute_id={ID}
 *    in a new browser tab. The statute's own ws_jurisdiction term is passed
 *    via tax_input so the new interpretation inherits the correct jurisdiction.
 * 5. After saving the new interpretation, the editor closes the tab and
 *    refreshes the statute screen to see the updated list.
 *
 * NOTE: The button is disabled (with tooltip) on auto-draft statutes because
 * the statute_id must reference a saved post to be meaningful.
 *
 * @package    WhistleblowerShield
 * @since      2.4.0
 * @version 3.10.0
 * @author     Dejunai
 *
 * VERSION
 * -------
 * 2.4.0  Initial release.
 * 2.4.1  Bug #7 fix: get_posts() had two 'meta_key' entries in the same
 *         array. PHP silently used the second value ('ws_interp_year'),
 *         discarding the 'meta_value' => $post->ID filter entirely and
 *         returning interpretations across all statutes. Fixed by using
 *         a proper meta_query for the statute filter and a separate
 *         'meta_key' / 'orderby' pair for the year sort.
 * 3.0.0  Phase 12.1: Replaced ws_jx_code meta check with has_term() against
 *         the ws_jurisdiction taxonomy. &ws_jx_code=US removed from add URL.
 * 3.0.1  Added inline comment to direct meta reads in metabox render function
 *        explaining why the query layer is not used in admin metabox context.
 * 3.6.0  Metabox now reads ws_jx_statute_interp_ids (reverse index maintained
 *        by admin-hooks.php) — simple post__in query, no meta_query JOIN.
 * 3.8.0  Removed federal-only guard (has_term 'us' check). Both federal and
 *        state statutes now show the metabox. Court label resolution updated
 *        to use ws_court_lookup() + other branch. Add URL now uses the
 *        statute's own ws_jurisdiction term instead of hardcoded 'us'.
 *        Metabox title renamed from "Federal Court Interpretations".
 */

defined( 'ABSPATH' ) || exit;

add_action( 'add_meta_boxes', 'ws_register_interpretation_metabox' );

/**
 * Registers the meta box on the jx-statute edit screen.
 */
function ws_register_interpretation_metabox() {
    add_meta_box(
        'ws_interpretations',
        'Court Interpretations',
        'ws_render_interpretation_metabox',
        'jx-statute',
        'normal',
        'default'
    );

    add_meta_box(
        'ws_interpretations',
        'Court Interpretations',
        'ws_render_interpretation_metabox',
        'jx-common-law',
        'normal',
        'default'
    );
}


/**
 * Renders the Court Interpretations meta box.
 *
 * Displays all jx-interpretation records linked to the current statute,
 * regardless of jurisdiction. Both federal and state statutes can have
 * court interpretation records.
 *
 * @param WP_Post $post  The current jx-statute post object.
 */
function ws_render_interpretation_metabox( $post ) {

    // ── Auto-draft guard ──────────────────────────────────────────────────

    $is_draft = ( $post->post_status === 'auto-draft' );
    $post_type = get_post_type( $post );
    $is_statute = ( $post_type === 'jx-statute' );

    // ── Build "Add New Interpretation" URL ────────────────────────────────
    //
    // statute_id/common_law_id
    //                    — read by acf/load_value in acf-jx-interpretations.php
    //                      to pre-select ws_jx_interp_statute_id or
    //                      ws_jx_interp_comlaw_id.
    // tax_input[...][]   — WordPress core pre-assigns the ws_jurisdiction taxonomy
    //                      term on the new post screen without any ACF hook.
    //                      Uses the statute's own jurisdiction term so state-level
    //                      interpretations inherit the correct jurisdiction.
    // post_title         — WordPress core pre-fills the title field.

    $parent_terms = get_the_terms( $post->ID, WS_JURISDICTION_TAXONOMY );
    $parent_term  = ( $parent_terms && ! is_wp_error( $parent_terms ) ) ? $parent_terms[0] : null;
    $parent_param = $is_statute ? 'statute_id' : 'comlaw_id';
    $add_url = admin_url( 'post-new.php?post_type=jx-interpretation&' . $parent_param . '=' . $post->ID );
    if ( $parent_term ) {
        $add_url .= '&tax_input[' . WS_JURISDICTION_TAXONOMY . '][]=' . $parent_term->term_id;
    }
    $post_title = get_the_title( $post );
    if ( $post_title ) {
        $add_url .= '&post_title=' . rawurlencode( 'Interpretation — ' . $post_title );
    }

    // ── Fetch linked interpretations ──────────────────────────────────────
    //
    if ( $is_statute ) {
        // ws_jx_statute_interp_ids is the reverse index maintained by
        // ws_rebuild_jx_statute_interp_index() in admin-hooks.php. Reading it
        // here is a single get_post_meta() call; the post__in query that follows
        // is a simple WHERE ID IN (...) sorted by decision year, no meta JOIN.
        $interp_ids = array_filter( array_map( 'intval', (array) get_post_meta( $post->ID, 'ws_jx_statute_interp_ids', true ) ) );
    } else {
        // Common-law links can be stored from either side:
        // - ws_jx_comlaw_interp_ids on jx-common-law
        // - ws_jx_interp_commlaw_id on jx-interpretation
        // Use the union so the metabox remains accurate even if one side lags.
        $from_comlaw = array_filter( array_map( 'intval', (array) get_post_meta( $post->ID, 'ws_jx_comlaw_interp_ids', true ) ) );
        $from_interp = get_posts( [
            'post_type'      => 'jx-interpretation',
            'post_status'    => 'any',
            'posts_per_page' => -1,
            'fields'         => 'ids',
            'no_found_rows'  => true,
            'meta_query'     => [ [
                'key'     => 'ws_jx_interp_comlaw_id',
                'value'   => $post->ID,
                'compare' => '=',
                'type'    => 'NUMERIC',
            ] ],
        ] );
        $interp_ids = array_values( array_unique( array_merge( $from_comlaw, array_map( 'intval', (array) $from_interp ) ) ) );
    }

    $interpretations = empty( $interp_ids ) ? [] : get_posts( [
        'post_type'      => 'jx-interpretation',
        'post_status'    => 'any',
        'posts_per_page' => -1,
        'fields'         => 'ids',
        'post__in'       => $interp_ids,
        'meta_key'       => 'ws_jx_interp_year',
        'orderby'        => 'meta_value_num',
        'order'          => 'DESC',
    ] );

    // ── Render ────────────────────────────────────────────────────────────
    ?>
    <?php if ( empty( $interpretations ) ) : ?>
        <p class="ws-interp-empty">No court interpretation records linked to this record yet.</p>
    <?php else : ?>
        <table class="ws-interp-table">
            <thead>
                <tr>
                    <th>Case Name</th>
                    <th>Court</th>
                    <th>Year</th>
                    <th>Favorable?</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ( $interpretations as $interp_id ) :
                    $case_name   = get_the_title( $interp_id );
                    // Direct meta reads — admin metabox display only; query layer is for front-end shortcode rendering.
                    $court_key   = get_post_meta( $interp_id, 'ws_jx_interp_court', true );
                    $year        = get_post_meta( $interp_id, 'ws_jx_interp_year', true );
                    $favorable   = get_post_meta( $interp_id, 'ws_jx_interp_is_favorable', true );
                    if ( $court_key === 'other' ) {
                        $court_label = esc_html( get_post_meta( $interp_id, 'ws_jx_interp_court_name', true ) ?: 'Other' );
                    } else {
                        $court_entry = ws_court_lookup( $court_key );
                        $court_label = $court_entry ? esc_html( $court_entry['short'] ) : esc_html( $court_key );
                    }
                    $edit_url    = get_edit_post_link( $interp_id );
                ?>
                <tr>
                    <td><?php echo esc_html( $case_name ?: '(untitled)' ); ?></td>
                    <td><?php echo $court_label; ?></td>
                    <td><?php echo esc_html( $year ?: '—' ); ?></td>
                    <td>
                        <?php if ( $favorable ) : ?>
                            <span class="ws-favorable-yes">Yes</span>
                        <?php else : ?>
                            <span class="ws-favorable-no">No</span>
                        <?php endif; ?>
                    </td>
                    <td><a href="<?php echo esc_url( $edit_url ); ?>" target="_blank">Edit</a></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>

    <div class="ws-interp-actions">
        <?php if ( $is_draft ) : ?>
            <a class="button ws-interp-add-btn"
               disabled
               title="Save this record first before adding interpretations.">
                + Add New Interpretation
            </a>
            <span style="color:#666;font-size:12px;">Save this record first to enable this button.</span>
        <?php else : ?>
            <a class="button button-primary ws-interp-add-btn"
               href="<?php echo esc_url( $add_url ); ?>"
               target="_blank">
                + Add New Interpretation
            </a>
            <span style="color:#666;font-size:12px;">Opens in a new tab. Refresh this page after saving to update the list.</span>
        <?php endif; ?>
    </div>
    <?php
}
