<?php
/**
 * acf-plain-english-fields.php
 *
 * Centralized Plain-English ACF field group shared across content CPTs.
 * Group key: group_plain_english_metadata  (menu_order 85)
 *
 * ATTACHED CPTs
 * -------------
 * jx-statute, jx-common-law, jx-citation, jx-construction, ws-agency, ag-procedure, ws-assist-org
 *
 * EXCLUDED CPTs (and why)
 * -----------------------
 * jx-summary      — IS the plain-english document; carries its own review fields.
 * ws-legal-update — Changelog entries; no plain-english companion use case.
 * ws-reference    — Outbound links with metadata; no prose to simplify.
 * jurisdiction    — Structured metadata container; not explanatory prose.
 *
 * FIELDS
 * ------
 * ws_has_plain_english                 Toggle — enables plain-english content field.
 * ws_plain_english_wysiwyg             The plain-english content (conditional on toggle).
 * ws_plain_english_reviewed            Toggle — marks content as human-reviewed.
 * ws_auto_plain_english_reviewed_by    User ID of reviewer. Stamped once on toggle-on; cleared on toggle-off.
 * ws_auto_plain_english_reviewed_date  Local Y-m-d of first review. Same lifecycle.
 * ws_auto_plain_english_by             User ID of summarizer. Stamped once on first plain-english save.
 * ws_auto_plain_english_date           Local Y-m-d of first plain-english save.
 *
 * INTEGRITY GUARDS (admin-hooks.php, priority 5)
 * -----------------------------------------------
 * Rule 1 — has_plain_english requires non-empty plain_english_wysiwyg.
 * Rule 2 — plain_english_reviewed requires editor rank or above.
 * Rule 3 — has_plain_english toggle-off clears all reviewed fields and stamps.
 *
 * @package    WhistleblowerShield
 * @since      3.4.0
 * @version    3.20.1
 * @author     Whistleblower Shield
 * @link       https://whistleblowershield.org
 * @copyright  Copyright (c) Whistleblower Shield
 */

defined( 'ABSPATH' ) || exit;

add_action( 'acf/init', 'ws_register_acf_plain_english_fields' );

/**
 * Registers the shared Plain-English field group for all supported CPTs.
 */
function ws_register_acf_plain_english_fields() {

    if ( ! function_exists( 'acf_add_local_field_group' ) ) {
        return;
    }

    acf_add_local_field_group( [

        'key'                   => 'group_plain_english_metadata',
        'title'                 => 'Plain-English',
        'menu_order'            => 85,
        'position'              => 'normal',
        'style'                 => 'default',
        'label_placement'       => 'top',
        'instruction_placement' => 'label',
        'active'                => true,

        // Attaches to All CPTs.
        // See file header for rationale.
        'location' => [
           // [ [ 'param' => 'post_type', 'operator' => '==', 'value' => 'jx-summary'        ] ],
            [ [ 'param' => 'post_type', 'operator' => '==', 'value' => 'jx-statute'        ] ],
            [ [ 'param' => 'post_type', 'operator' => '==', 'value' => 'jx-citation'       ] ],
            [ [ 'param' => 'post_type', 'operator' => '==', 'value' => 'jx-construction' ] ],
            [ [ 'param' => 'post_type', 'operator' => '==', 'value' => 'jx-common-law'     ] ],
            [ [ 'param' => 'post_type', 'operator' => '==', 'value' => 'ws-agency'         ] ],
            [ [ 'param' => 'post_type', 'operator' => '==', 'value' => 'ag-procedure'   ] ],
            [ [ 'param' => 'post_type', 'operator' => '==', 'value' => 'ws-assist-org'     ] ],
           // [ [ 'param' => 'post_type', 'operator' => '==', 'value' => 'ws-reference'      ] ],
            
        ],

        'fields' => [

            // ── Tab: Plain-English ───────────────────────────────────────
            //
            // menu_order 85 positions this group after each CPT's content
            // tabs and before Authorship & Review (menu_order 90) and
            // Major Edit (menu_order 99).

            [
                'key'   => 'field_plain_english_tab',
                'label' => 'Plain-English',
                'type'  => 'tab',
            ],

            // ── Has Plain-English Summary ────────────────────────────────
            //
            // Master toggle. Guards display of the wysiwyg editor below.
            // ws_acf_plain_english_guards() (priority 5) forces this to 0
            // if plain_english_wysiwyg is empty on save.

            [
                'key'           => 'field_has_plain_english',
                'label'         => 'Requires Plain-English Summary',
                'name'          => 'ws_has_plain_english',
                'type'          => 'true_false',
                'instructions'  => 'Enable when a Plain-English summary of this record is required.',
                'ui'            => 1,
                'ui_on_text'    => 'Yes',
                'ui_off_text'   => 'No',
                'default_value' => 0,
            ],

            // ── Plain-English Content ────────────────────────────────────
            //
            // Conditional on has_plain_english = 1. Content written here
            // is stamped via ws_acf_stamp_summarized_fields() on first save.

            [
                'key'               => 'field_plain_english_wysiwyg',
                'label'             => 'Plain-English Summary',
                'name'              => 'ws_plain_english_wysiwyg',
                'type'              => 'wysiwyg',
                'instructions'      => 'Plain-English summary of this record in non-legalese.',
                'tabs'              => 'all',
                'toolbar'           => 'full',
                'media_upload'      => 0,
                'conditional_logic' => [ [ [
                    'field'    => 'field_has_plain_english',
                    'operator' => '==',
                    'value'    => '1',
                ] ] ],
            ],
            
            // ── Summarized By ─────────────────────────────────────────────
            //
            // Stamped once by ws_acf_stamp_summarized_fields() on first save
            // after has_plain_english is enabled and content exists.
            // Cleared on has_plain_english toggle-off.

            [
                'key'           => 'field_plain_english_by',
                'label'         => 'Summarized By',
                'name'          => 'ws_auto_plain_english_by',
                'type'          => 'user',
                'instructions'  => 'User Auto-stamped on first save when Plain-English summary is created. Read only.',
                'role'          => [ 'author', 'editor', 'administrator' ],
                'return_format' => 'id',
                'readonly'      => 1,
                'disabled'      => 1,
            ],

            // ── Summarized Date ───────────────────────────────────────────
            //
            // Stamped once by ws_acf_stamp_summarized_fields() alongside
            // plain_english_by. Cleared on has_plain_english toggle-off.

            [
                'key'          => 'field_plain_english_date',
                'label'        => 'Summarized Date',
                'name'         => 'ws_auto_plain_english_date',
                'type'         => 'text',
                'instructions' => 'Date Auto-stamped on first save after Plain-English summary is created. Read only.',
                'readonly'     => 1,
                'disabled'     => 1,
            ],
            
            // ── Plain-English Reviewed ───────────────────────────────────
            //
            // Requires editor rank or above — enforced server-side by
            // ws_acf_plain_english_guards() at priority 5. Cleared on
            // has_plain_english toggle-off by the same guard.

            [
                'key'           => 'field_plain_english_reviewed',
                'label'         => 'Plain-English Summary Approved',
                'name'          => 'ws_plain_english_reviewed',
                'type'          => 'true_false',
                'instructions'  => 'Manually toggle when an Editor or Administrator has approved the Plain-English summary.',
                'ui'            => 1,
                'ui_on_text'    => 'Reviewed',
                'ui_off_text'   => 'Pending',
                'role'          => [ 'editor', 'administrator' ],
                'default_value' => 0,
            ],

            // ── Reviewed By ───────────────────────────────────────────────
            //
            // Stamped once by ws_acf_stamp_plain_reviewed_by() when
            // plain_english_reviewed is first enabled. Cleared on toggle-off.
            // Locked for users below editor via ws_acf_lock_for_non_editors().

            [
                'key'           => 'field_plain_english_reviewed_by',
                'label'         => 'Summary Reviewed By',
                'name'          => 'ws_auto_plain_english_reviewed_by',
                'type'          => 'user',
                'instructions'  => 'Auto-toggled when Plain-English-Reviewed is manually enabled or disabled. Read only.',
                'role'          => [ 'author', 'editor', 'administrator' ],
                'return_format' => 'id',
                'readonly'      => 1,
                'disabled'      => 1,
            ],

            // ── Reviewed Date ─────────────────────────────────────────────
            //
            // Stamped once by ws_acf_stamp_plain_reviewed_by() alongside
            // plain_english_reviewed_by. Cleared on has_plain_english toggle-off.
            // Locked for users below editor via ws_acf_lock_for_non_editors().

            [
                'key'          => 'field_plain_english_reviewed_date',
                'label'        => 'Summary Reviewed Date',
                'name'         => 'ws_auto_plain_english_reviewed_date',
                'type'         => 'text',
                'instructions' => 'Auto-toggled when Plain-English-Reviewed is manually enabled or disabled. Read only.',
                'readonly'     => 1,
                'disabled'     => 1,
            ],


        ], // end fields

    ] ); // end acf_add_local_field_group

} // end ws_register_acf_plain_english_fields


// All integrity guards, stamp writes, and field locking for plain-english
// fields are handled centrally in admin-hooks.php:
//   - ws_acf_plain_english_guards()       — acf/save_post priority 5
//   - ws_acf_stamp_plain_reviewed_by()    — acf/save_post priority 25
//      stamps: ws_auto_plain_english_reviewed_by, ws_auto_plain_english_reviewed_date
//   - ws_acf_stamp_summarized_fields()    — acf/save_post priority 25
//   - ws_acf_lock_for_non_editors()       — acf/load_field by field name

