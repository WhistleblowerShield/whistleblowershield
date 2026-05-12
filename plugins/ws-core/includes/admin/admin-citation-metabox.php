<?php
/**
 * admin-citation-metabox.php
 *
 * Adds a "Case Law & Citations" meta box to the jx-statute edit screen.
 *
 * PURPOSE
 * -------
 * Displays all jx-citation records linked to the current statute via
 * ws_jx_citation_statute_ids, with a direct "Add New Citation" button
 * that opens the creation form pre-populated with the statute relationship,
 * jurisdiction taxonomy, and post title.
 *
 * Unlike the construction metabox, this metabox is not jurisdiction-gated —
 * it appears on all jx-statute records regardless of jurisdiction.
 *
 * WORKFLOW
 * --------
 * 1. Editor saves a jx-statute post.
 * 2. The meta box appears below the ACF field groups.
 * 3. Existing linked citations are listed: official name, type, attached?, Edit.
 * 4. "Add New Citation" opens:
 *    post-new.php?post_type=jx-citation
 *      &statute_id={ID}
 *      &tax_input[WS_JURISDICTION_TAXONOMY][]={term_id}
 *      &post_title=Citation — {statute title}
 *    in a new browser tab.
 * 5. The new citation screen has the statute relationship pre-selected (via
 *    acf/load_value in acf-jx-citations.php) and the WS_JURISDICTION_TAXONOMY taxonomy
 *    pre-assigned (via tax_input URL parameter, handled by WordPress core).
 * 6. After saving, the editor closes the tab and refreshes the statute screen.
 *
 * *NOTE:* The button is disabled (with tooltip) on auto-draft statutes because
 * the statute_id must reference a saved post to be meaningful.
 *
 * @package    WhistleblowerShield
 * @since      3.6.0
 * @version    3.20.0
 * @author     Whistleblower Shield
 *
 * VERSION
 * -------
 * 3.6.0  Initial release. Mirrors admin-construction-metabox.php pattern.
 *        No jurisdiction gate. Pre-assigns WS_JURISDICTION_TAXONOMY via tax_input URL
 *        param (WordPress core); pre-selects statute via acf/load_value hook.
 *        Metabox reads ws_jx_statute_citation_ids (reverse index maintained
 *        by admin-hooks.php) — simple post__in query, no meta_query JOIN.
 */

defined( 'ABSPATH' ) || exit;


add_action( 'add_meta_boxes', 'ws_register_citation_metabox' );

/**
 * Registers the meta box on the jx-statute edit screen.
 */
function ws_register_citation_metabox() {
    add_meta_box(
        'ws_citations',
        'Case Law & Citations',
        'ws_render_citation_metabox',
        'jx-statute',
        'normal',
        'default'
    );

    add_meta_box(
        'ws_citations',
        'Case Law & Citations',
        'ws_render_citation_metabox',
        'jx-common-law',
        'normal',
        'default'
    );
}


/**
 * Renders the Case Law & Citations meta box.
 *
 * Appears on all jx-statute records regardless of jurisdiction.
 *
 * @param WP_Post $post  The current jx-statute post object.
 */
function ws_render_citation_metabox( $post ) {

    $is_draft = ( $post->post_status === 'auto-draft' );
    $post_type = get_post_type( $post );
    $is_statute = ( $post_type === 'jx-statute' );

    // ── Build "Add New Citation" URL ──────────────────────────────────────
    //
    // statute_id/comlaw_id
    //                    — read by acf/load_value in acf-jx-citations.php to
    //                      pre-select ws_jx_citation_statute_ids or
    //                      ws_jx_citation_comlaw_ids.
    // tax_input[...][]   — WordPress core pre-assigns the WS_JURISDICTION_TAXONOMY taxonomy
    //                      term(s) on the new post screen without any ACF hook.
    //                      All terms from the statute are forwarded; a statute
    //                      may carry more than one jurisdiction term.
    // post_title         — WordPress core pre-fills the title field.

    $terms   = wp_get_post_terms( $post->ID, WS_JURISDICTION_TAXONOMY );
    $parent_param = $is_statute ? 'statute_id' : 'comlaw_id';
    $add_url = admin_url( 'post-new.php?post_type=jx-citation&' . $parent_param . '=' . $post->ID );

    if ( ! empty( $terms ) && ! is_wp_error( $terms ) ) {
        foreach ( $terms as $term ) {
            $add_url .= '&tax_input[' . WS_JURISDICTION_TAXONOMY . '][]=' . $term->term_id;
        }
    }

    $statute_title = get_the_title( $post );
    if ( $statute_title ) {
        $add_url .= '&post_title=' . rawurlencode( 'Citation — ' . $statute_title );
    }

    // ── Fetch linked citations ────────────────────────────────────────────
    //
    if ( $is_statute ) {
        // ws_jx_statute_citation_ids is the reverse index maintained by
        // ws_rebuild_jx_statute_citation_index() in admin-hooks.php. Reading it
        // here is a single get_post_meta() call; the post__in query that follows
        // is a simple WHERE ID IN (...) with no meta JOIN.
        $citation_ids = array_filter( array_map( 'intval', (array) get_post_meta( $post->ID, 'ws_jx_statute_citation_ids', true ) ) );
    } else {
        // Common-law links can be stored from either side:
        // - ws_jx_comlaw_citation_ids on jx-common-law
        // - ws_jx_citation_comlaw_ids on jx-citation
        // Use the union so the metabox remains accurate even if one side lags.
        $from_comlaw = array_filter( array_map( 'intval', (array) get_post_meta( $post->ID, 'ws_jx_comlaw_citation_ids', true ) ) );
        $from_citation = get_posts( [
            'post_type'      => 'jx-citation',
            'post_status'    => 'any',
            'posts_per_page' => -1,
            'fields'         => 'ids',
            'no_found_rows'  => true,
            'meta_query'     => [
                'relation' => 'OR',
                [
                    'key'     => 'ws_jx_citation_comlaw_ids',
                    'value'   => '"' . $post->ID . '"',
                    'compare' => 'LIKE',
                ],
                [
                    'key'     => 'ws_jx_citation_comlaw_ids',
                    'value'   => (string) $post->ID,
                    'compare' => '=',
                ],
            ],
        ] );
        $citation_ids = array_values( array_unique( array_merge( $from_comlaw, array_map( 'intval', (array) $from_citation ) ) ) );
    }

    $citations = empty( $citation_ids ) ? [] : get_posts( [
        'post_type'      => 'jx-citation',
        'post_status'    => 'any',
        'posts_per_page' => -1,
        'fields'         => 'ids',
        'post__in'       => $citation_ids,
        'orderby'        => 'title',
        'order'          => 'ASC',
    ] );

    $type_labels = [
        'case_law'   => 'Case Law',
        'statute'    => 'Statute',
        'common_law' => 'Common Law',
        'regulatory' => 'Regulatory',
        'secondary'  => 'Secondary Source',
    ];

    // ── Render ────────────────────────────────────────────────────────────
    ?>
    <?php if ( empty( $citations ) ) : ?>
        <p class="ws-citation-empty">No citation records linked to this record yet.</p>
    <?php else : ?>
        <table class="ws-citation-table">
            <thead>
                <tr>
                    <th>Official Name</th>
                    <th>Type</th>
                    <th>Attached?</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ( $citations as $cite_id ) :
                    // Direct meta reads — admin metabox display only; query layer is for front-end shortcode rendering.
                    $official_name = get_post_meta( $cite_id, 'ws_jx_citation_official_name', true );
                    $type_raw      = get_post_meta( $cite_id, 'ws_jx_citation_types', true );
                    $type_keys     = is_array( $type_raw ) ? $type_raw : ( $type_raw ? [ $type_raw ] : [] );
                    $attached      = get_post_meta( $cite_id, 'ws_jx_citation_has_attach_flag', true );
                    $type_label_parts = [];
                    foreach ( $type_keys as $type_key ) {
                        $type_label_parts[] = $type_labels[ $type_key ] ?? (string) $type_key;
                    }
                    $type_label = ! empty( $type_label_parts ) ? implode( ', ', $type_label_parts ) : '—';
                    $edit_url      = get_edit_post_link( $cite_id );
                ?>
                <tr>
                    <td><?php echo esc_html( $official_name ?: get_the_title( $cite_id ) ?: '(untitled)' ); ?></td>
                    <td><?php echo esc_html( $type_label ); ?></td>
                    <td>
                        <?php if ( $attached ) : ?>
                            <span class="ws-attached-yes">Yes</span>
                        <?php else : ?>
                            <span class="ws-attached-no">No</span>
                        <?php endif; ?>
                    </td>
                    <td><a href="<?php echo esc_url( $edit_url ); ?>" target="_blank">Edit</a></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>

    <div class="ws-citation-actions">
        <?php if ( $is_draft ) : ?>
            <a class="button ws-citation-add-btn"
               disabled
               title="Save this record first before adding citations.">
                + Add New Citation
            </a>
            <span style="color:#666;font-size:12px;">Save this record first to enable this button.</span>
        <?php else : ?>
            <a class="button button-primary ws-citation-add-btn"
               href="<?php echo esc_url( $add_url ); ?>"
               target="_blank">
                + Add New Citation
            </a>
            <span style="color:#666;font-size:12px;">Opens in a new tab. Refresh this page after saving to update the list.</span>
        <?php endif; ?>
    </div>
    <?php
}
