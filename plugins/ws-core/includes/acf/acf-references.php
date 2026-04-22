<?php
/**
 * acf-references.php
 *
 * Registers ACF Pro fields for the `ws-reference` CPT.
 *
 * PURPOSE
 * -------
 * Provides structured metadata for external reference materials attached
 * to individual statute, common-law, citation, or construction records.
 *
 * GROUP: group_reference_metadata
 *
 * FIELD SUMMARY
 * -------------
 * Content tab:
 *   ws_ref_title          Resource Title (text, optional)
 *   ws_ref_url            Resource URL (url, required)
 *   ws_ref_url_is_pdf     Link is PDF? (true_false, optional)
 *   ws_ref_description    Description (textarea, optional)
 *   ws_ref_type           Resource Type (select, optional)
 *   ws_ref_source_name    Source / Author (text, optional)
 *
 * SHARED WORKFLOW GROUPS
 * ----------------------
 *   - group_stamp_metadata (acf-stamp-fields.php, menu_order 90)
 *   - group_source_verify_metadata (acf-source-verify.php)
 *
 * PLAIN ENGLISH
 * -------------
 * ws-reference does not participate in the has_plain_english / plain_reviewed
 * workflow. References are outbound links with metadata — no plain-english
 * companion use case exists.
 *
 * @package    WhistleblowerShield
 * @since      3.3.0
 * @version    3.17.0
 * @author     Whistleblower Shield
 * @link       https://whistleblowershield.org
 * @copyright  Copyright (c) Whistleblower Shield
 *
 */

defined( 'ABSPATH' ) || exit;

add_action( 'acf/init', 'ws_register_acf_ws_reference' );

function ws_register_acf_ws_reference() {

    if ( ! function_exists( 'acf_add_local_field_group' ) ) {
        return;
    }

    acf_add_local_field_group( [
        'key'                   => 'group_reference_metadata',
        'title'                 => 'Reference Details',
        'menu_order'            => 0,
        'position'              => 'normal',
        'style'                 => 'default',
        'label_placement'       => 'top',
        'instruction_placement' => 'label',
        'active'                => true,

        'location' => [ [ [
            'param'    => 'post_type',
            'operator' => '==',
            'value'    => 'ws-reference',
        ] ] ],

        'fields' => [

            // ────────────────────────────────────────────────────────────────
            // Tab: Content
            //
            // The substance of the reference: what it is, where it lives,
            // and why it's relevant to the parent record.
            // ────────────────────────────────────────────────────────────────

            [
                'key'   => 'field_ref_content_tab',
                'label' => 'Content',
                'type'  => 'tab',
            ],

            [
                'key'          => 'field_ref_title',
                'label'        => 'Resource Title',
                'name'         => 'ws_ref_title',
                'type'         => 'text',
                'instructions' => 'The title of the referenced resource as it should appear publicly.',
                'required'     => 0,
            ],

            [
                'key'          => 'field_ref_url',
                'label'        => 'Resource URL',
                'name'         => 'ws_ref_url',
                'type'         => 'url',
                'instructions' => 'Direct link to the external resource. Required. All outbound links open in a new tab.',
                'required'     => 1,
            ],

            [
                'key'           => 'field_ref_url_is_pdf',
                'label'         => 'Link is PDF?',
                'name'          => 'ws_ref_url_is_pdf',
                'type'          => 'true_false',
                'instructions'  => 'Enable if the resource URL links directly to a PDF document.',
                'ui'            => 1,
                'ui_on_text'    => 'PDF',
                'ui_off_text'   => 'No',
                'default_value' => 0,
            ],

            [
                'key'          => 'field_ref_description',
                'label'        => 'Description',
                'name'         => 'ws_ref_description',
                'type'         => 'textarea',
                'instructions' => 'Brief description of the resource and its relevance to the parent record. One to three sentences.',
                'rows'         => 4,
            ],

            [
                'key'           => 'field_ref_type',
                'label'         => 'Resource Type',
                'name'          => 'ws_ref_type',
                'type'          => 'select',
                'instructions'  => 'Classify the type of resource.',
                'choices'       => [
                    'case_law'        => 'Case Law',
                    'academic_paper'  => 'Academic Paper',
                    'gov_report'      => 'Government Report',
                    'journalism'      => 'Journalism',
                    'policy_analysis' => 'Policy Analysis',
                    'other'           => 'Other',
                ],
                'allow_null'    => 1,
                'ui'            => 1,
                'return_format' => 'value',
            ],

            [
                'key'          => 'field_ref_source_name',
                'label'        => 'Source / Author',
                'name'         => 'ws_ref_source_name',
                'type'         => 'text',
                'instructions' => 'Name of the publishing organization or primary author.',
            ],

            // ── Tab: Approval ─────────────────────────────────────────────
            // Removed entirely — ws_ref_approved retired. ws-reference does
            // not warrant an approval gate; the parent record's review
            // workflow is the appropriate quality gate. See version log.

            // ── Tab: Authorship & Review ──────────────────────────────────
            // Removed — registered centrally in acf-stamp-fields.php
            // (group_stamp_metadata, menu_order 90).
            // Unique field keys (field_ws_ref_last_edited_author, etc.)
            // retired. ws-reference now uses shared field keys consistent
            // with all other CPTs.

        ],
    ] );

} // end ws_register_acf_ws_reference


// Field locking and stamp fields are handled centrally in admin-hooks.php.
// ws_ref_approved has been retired — Approval tab removed entirely.
// ws-reference is NOT enrolled in the plain_english guards or stamp functions.
