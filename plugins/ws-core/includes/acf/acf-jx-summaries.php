<?php
/**
 * acf-jx-summaries.php
 *
 * Registers ACF Pro fields for the `jx-summary` CPT.
 *
 * PURPOSE
 * -------
 * Provides structured metadata for jurisdiction summary records, including
 * primary narrative content, limitations, and internal review controls used
 * in editorial and publication workflows.
 *
 * GROUP: group_jx_summary_metadata
 *
 * FIELD SUMMARY
 * -------------
 * Summary Content tab:
 *   ws_jx_summary_wysiwyg                   Jurisdiction Summary (wysiwyg, required)
 *   ws_jx_summary_limitations               Limitations & Ramifications [label|description] (repeater, optional)
 *   ws_jx_summary_sources                   Sources & Citations (textarea, optional)
 *
 * Summary Review tab:
 *   ws_jx_summary_plain_english_reviewed    Plain English Reviewed (true_false, optional)
 * 
 * Hidden fields:
 *   _ws_jx_summary_internal_notes          Internal Notes (textarea, hidden — for editorial use only, not rendered publicly)
 *
 * 
 * SHARED WORKFLOW GROUPS
 * ----------------------
 *   - group_stamp_metadata (acf-stamp-fields.php, menu_order 90)
 *   - group_plain_english_metadata (acf-plain-english-fields.php, menu_order 85)
 *   - group_source_verify_metadata (acf-source-verify.php)
 *   - group_major_edit_metadata (acf-major-edit.php, menu_order 99)
 * 
 * SHARDED WORKFLOW FIELDS
 * -----------------------
 *  - auto-filled on save by ws_acf_write_stamp_fields()
 *    - ws_auto_last_edited_date            (text, readonly, date of last edit)
 *    - ws_auto_last_edited_author          (text, readonly, user id of last editor)
 *    - ws_auto_create_date                 (text, readonly, date authored)
 *    - ws_auto_create_author               (text, readonly, user id of author)
 *  - auto-checked on save by ws_acf_write_plain_english()
 *    - ws_has_plain_english                (true_false, defaults to false, enable to expose wysiwyg summary field)
 *    - ws_plain_english_wysiwyg            (wysiwyg, summary of legal record, conditional on ws_has_plain_english)
 *    - ws_plain_english_reviewed           (true_false, defaults to false, must be enabled by Admin to enable summary render)
 *  - auto-filled on save by ws_acf_write_plain_english() when ws_plain_english_wysiwyg is non-empty
 *    - ws_auto_plain_english_by            (user, readonly, user id when summary was last edited)
 *    - ws_auto_plain_english_date          (text, readonly, date of last edit to summary)
 *  - auto-filled on save by ws_acf_write_plain_english() when ws_plain_english_reviewed is true
 *    - ws_auto_plain_english_reviewed_by   (user, readonly, user id of Admin who approved summary)
 *    - ws_auto_plain_english_reviewed_date (text, readonly, date summary was approved)
 *  - auto-filled on post creation by ws_acf_write_source_method()
 *    - ws_auto_source_method               (text, readonly, set to method of post creation (e.g. "ai_research", "human_created", "matrix_seeded"))
 *    - ws_auto_source_name                 (text, readonly, "Direct" when human_created or matrix_seeded, auto-set when ingested to tool or feed name (e.g. NoteBookLM or Inoreader ))
 *  - auto-set on post creation by ws_acf_write_verification_status() — (conditional on ws_auto_source_name is non-empty AND is not 'Direct')
 *    - ws_verification_status              (select: unverified, verified, defaults to unverified — set to verified by Authors, Admins required to unverified)
 *    - ws_needs_review                     (true_false, default true — must be disabled by Admin to enable publishing)
 *  - auto-filled on save by ws_acf_write_verification_status() when ws_verification_status is true
 *    - ws_auto_verified_by                (user, readonly, user that verified the post)
 *    - ws_auto_verified_date              (text, readonly, date of verification)
 *  - auto-checked on save by ws_acf_write_major_edit() ws_is_major_edit true triggers legal-update post creation
 *    - ws_is_major_edit                   (true_false, set to true when manual edit warrants legal-update post)
 *    - ws_major_edit_description          (textarea, required when ws_is_major_edit is true, description of the edit for legal-update seed summary)
 *    - ws_major_edit_update_type          (select, required when ws_is_major_edit is true, legal-update type  — auto derives from source; override if necessary)
 * 
 * PLAIN ENGLISH
 * -------------
 * jx-summary has its own "plain-english" summary_wysiwyg, and workflow.
 *
 * @package    WhistleblowerShield
 * @since      2.1.0
 * @version    3.17.0
 * @author     Whistleblower Shield
 * @link       https://whistleblowershield.org
 * @copyright  Copyright (c) Whistleblower Shield
 *
 */

defined( 'ABSPATH' ) || exit;

// ── Field group registration ──────────────────────────────────────────────────

add_action( 'acf/init', 'ws_register_acf_jx_summary' );

function ws_register_acf_jx_summary() {

    if ( ! function_exists( 'acf_add_local_field_group' ) ) {
        return;
    }

    acf_add_local_field_group( [

        'key'                   => 'group_jx_summary_metadata',
        'title'                 => 'Jurisdiction Summary Metadata',
        'menu_order'            => 0,
        'position'              => 'normal',
        'style'                 => 'default',
        'label_placement'       => 'top',
        'instruction_placement' => 'label',
        'active'                => true,

        'location' => [ [ [
            'param'    => 'post_type',
            'operator' => '==',
            'value'    => 'jx-summary',
        ] ] ],

        'fields' => [

            // ── Tab: Content ──────────────────────────────────────────────

            [
                'key'   => 'field_jx_summary_content_tab',
                'label' => 'Summary Content',
                'type'  => 'tab',
            ],
            [
                'key'          => 'field_jx_summary_wysiwyg',
                'label'        => 'Jurisdiction Summary',
                'name'         => 'ws_jx_summary_wysiwyg',
                'type'         => 'wysiwyg',
                'instructions' => '<strong>IMPORTANT:</strong> Use the editor toolbar for all formatting. Do NOT paste raw Markdown (**, ##, ---). Content must be clean HTML. This field is rendered directly on the jurisdiction page.',
                'required'     => 1,
                'tabs'         => 'all',
                'toolbar'      => 'full',
                'media_upload' => 0,
                'delay'        => 0,
            ],
            [
                'key'          => 'field_jx_summary_limitations',
                'label'        => 'Limitations & Ramifications',
                'name'         => 'ws_jx_summary_limitations',
                'type'         => 'repeater',
                'instructions' => 'Each row is one limitation. Label: Short bold heading. Description: Plain-English explanation. Rendered to Jurisdiction pages after Case Law via [ws_jx_limitations].',
                'button_label' => 'Add Limitation',
                'layout'       => 'table',
                'min'          => 0,
                'max'          => 0,
                'sub_fields'   => [
                    [
                        'key'          => 'field_jx_summary_limit_label',
                        'label'        => 'Label',
                        'name'         => 'ws_jx_summary_limit_label',
                        'type'         => 'text',
                        'instructions' => 'Short bold heading (e.g. "Media Reporting", "Personal Grievances").',
                        'required'     => 1,
                        'wrapper'      => [ 'width' => '25' ],
                    ],
                    [
                        'key'          => 'field_jx_summary_limit_text',
                        'label'        => 'Description',
                        'name'         => 'ws_jx_summary_limit_text',
                        'type'         => 'textarea',
                        'instructions' => 'Plain-English explanation. No HTML.',
                        'required'     => 1,
                        'rows'         => 3,
                        'wrapper'      => [ 'width' => '75' ],
                    ],
                ],
            ],
            [
                'key'          => 'field_jx_summary_sources',
                'label'        => 'Source Attribution',
                'name'         => 'ws_jx_summary_sources',
                'type'         => 'textarea',
                'instructions' => 'Proper attribution required. List AI Agents, URL references, etc. One-per-line recommended.',
                'rows'         => 6,
            ],
            [
                'key'          => 'field_jx_summary_internal_notes',
                'label'        => 'Internal Notes',
                'name'         => '_ws_jx_summary_internal_notes',
                'type'         => 'textarea',
                'instructions' => 'Internal editorial notes only. Not displayed publicly.',
                'rows'         => 4,
            ],

            // ── Tab: Authorship & Review ──────────────────────────────────
            //
            // jx-summary is the plain-english document — it does not use
            // the has_plain_english / plain_english_reviewed pathway used
            // by other CPTs. Instead it carries its own reviewed fields here
            // with semantics appropriate to summary review (not translation).
            //
            // Stamp fields (last_edited_author, created_date, last_edited,
            // create_author) are registered centrally in acf-stamp-fields.php
            // and appear via that group's Authorship & Review tab (menu_order 90).

            [
                'key'   => 'field_jx_summary_review_tab',
                'label' => 'Summary Review',
                'type'  => 'tab',
            ],
            [
                'key'           => 'field_jx_summary_plain_english_reviewed',
                'label'         => 'Plain English Reviewed',
                'name'          => 'ws_jx_summary_plain_english_reviewed',
                'type'          => 'true_false',
                'instructions'  => 'Check when a human has reviewed and approved this plain-english summary.',
                'ui'            => 1,
                'ui_on_text'    => 'Reviewed',
                'ui_off_text'   => 'Pending',
                'default_value' => 0,
            ],

        ], // end fields

    ] ); // end acf_add_local_field_group

} // end ws_register_acf_jx_summary


// Field locking, auto-fill today, and stamp fields are handled centrally
// in admin-hooks.php via ws_acf_lock_for_non_admins(), ws_acf_autofill_today(),
// and ws_acf_write_stamp_fields().


