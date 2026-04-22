<?php
/**
 * acf-legal-updates.php
 *
 * Registers ACF Pro fields for the `ws-legal-update` CPT.
 *
 * PURPOSE
 * -------
 * Provides structured metadata for Legal Update records, capturing
 * the nature, source, date, and affected jurisdictions of each
 * significant development in whistleblower law.
 *
 * GROUP: group_legal_update_metadata
 *
 * FIELD SUMMARY
 * -------------
 * Content tab:
 *   ws_legal_update_jurisdictions         Affected Jurisdiction(s) (multi_select, optional)
 *   ws_legal_update_multi_jurisdiction    Multi-Jurisdiction (true_false, optional)
 *   ws_legal_update_hide_public           Hide from Public Change Log (true_false, optional)
 *   ws_legal_update_source_url            Primary Source URL (url, optional)
 *   ws_legal_update_source_url_is_pdf     Link is PDF? (true_false, optional)
 *   ws_legal_update_type                  Update Type (select, optional)
 *   ws_legal_update_law_name              Law / Statute Name (text, optional)
 *   ws_legal_update_summary_wysiwyg       Summary (wysiwyg, optional)
 *   ws_legal_update_effective_date        Effective Date (date_picker, optional)
 *   ws_legal_update_parent_post_id        Parent Post ID (post meta, int, required) — links to the source post that triggered this update record; set programmatically on save.
 *   ws_legal_update_parent_post_type      Parent Post Type (post meta, string, required) — the CPT of the source post; set programmatically on save.
 * 
 *
 * SHARED WORKFLOW GROUPS
 * ----------------------
 *   - group_stamp_metadata (acf-stamp-fields.php, menu_order 90)
 *
 * PLAIN ENGLISH
 * -------------
 * This record does not participate in a separate plain-english workflow.
 *
 * USE NOTES
 * ---------
 * Legal updates are linked to one or more jurisdiction records through
 * ws_legal_update_jurisdictions (ws_jurisdiction taxonomy, save_terms: 1).
 * These records support internal legal tracking, journalist research, and
 * future public update feeds and timelines.
 * 
 * @package    WhistleblowerShield
 * @since      1.0.0
 * @version    3.17.0
 * @author     Whistleblower Shield
 * @link       https://whistleblowershield.org
 * @copyright  Copyright (c) Whistleblower Shield
 *
 */

defined( 'ABSPATH' ) || exit;

add_action( 'acf/init', 'ws_register_acf_legal_update' );

function ws_register_acf_legal_update() {

    if ( ! function_exists( 'acf_add_local_field_group' ) ) {
        return;
    }

    acf_add_local_field_group( [

        'key'                   => 'group_legal_update_metadata',
        'title'                 => 'Legal Update Metadata',
        'menu_order'            => 0,
        'position'              => 'normal',
        'style'                 => 'default',
        'label_placement'       => 'top',
        'instruction_placement' => 'label',
        'active'                => true,

        // Location: ws-legal-update CPT only (hyphenated slug)
        'location' => [ [ [
            'param'    => 'post_type',
            'operator' => '==',
            'value'    => 'ws-legal-update',
        ] ] ],

        'fields' => [

            // ── Tab: Content ──────────────────────────────────────────────

            [
                'key'   => 'field_legal_update_content_tab',
                'label' => 'Content',
                'type'  => 'tab',
            ],

            // ── Affected Jurisdictions ────────────────────────────────────
			// Taxonomy multi-select — one update may affect many
			// jurisdictions. Scoped via ws_jurisdiction taxonomy terms.
			[
				'key'           => 'field_legal_update_jurisdictions',
				'label'         => 'Affected Jurisdiction(s)',
				'name'          => 'ws_legal_update_jurisdictions',
				'type'          => 'taxonomy',
				'instructions'  => 'Select the jurisdictions affected by this legal update.',
				'taxonomy'      => WS_JURISDICTION_TAXONOMY,
				'field_type'    => 'multi_select',
				'return_format' => 'id',
				'save_terms'    => 1,
				'load_terms'    => 1,
				'add_term'      => 0,
			],

            // ── Multi-Jurisdiction Flag ───────────────────────────────────

            [
                'key'           => 'field_legal_update_multi_jurisdiction',
                'label'         => 'Multi-Jurisdiction',
                'name'          => 'ws_legal_update_multi_jurisdiction',
                'type'          => 'true_false',
                'instructions'  => 'Check if this update affects jurisdictions beyond the one listed above. No additional processing occurs — this flag is reserved for future use.',
                'default_value' => 0,
                'ui'            => 1,
                'ui_on_text'    => 'Multi-Jurisdiction',
                'ui_off_text'   => '',
            ],

            // ── Public Visibility ────────────────────────────────────────

            [
                'key'           => 'field_legal_update_hide_public',
                'label'         => 'Hide from Public Change Log',
                'name'          => 'ws_legal_update_hide_public',
                'type'          => 'true_false',
                'instructions'  => 'Enable only when this update must be excluded from frontend/public change-log rendering.',
                'default_value' => 0,
                'ui'            => 1,
                'ui_on_text'    => 'Hidden',
                'ui_off_text'   => 'Visible',
            ],

            // ── Primary Source ────────────────────────────────────────────

            [
                'key'          => 'field_legal_update_source_url',
                'label'        => 'Primary Source URL',
                'name'         => 'ws_legal_update_source_url',
                'type'         => 'url',
                'instructions' => 'Official source for the legal change — e.g., court decision, statute, regulation, or agency policy document.',
            ],

            [
                'key'           => 'field_legal_update_source_url_is_pdf',
                'label'         => 'Link is PDF?',
                'name'          => 'ws_legal_update_source_url_is_pdf',
                'type'          => 'true_false',
                'instructions'  => 'Enable if the primary source URL links directly to a PDF document.',
                'default_value' => 0,
                'ui'            => 1,
                'ui_on_text'    => 'PDF',
                'ui_off_text'   => 'No',
            ],

            // ── Update Type ───────────────────────────────────────────────

            [
                'key'          => 'field_legal_update_type',
                'label'        => 'Update Type',
                'name'         => 'ws_legal_update_type',
                'type'         => 'select',
                'instructions' => 'Select the category that best describes this legal development.',
                'choices'      => [
                    'statute'        => 'Statutory Change',
                    'common_law'     => 'Common Law Development',
                    'citation'       => 'Citation Update',
                    'summary'        => 'Summary Update',
                    'construction' => 'construction Update',
                    'regulation'     => 'Regulatory Change',
                    'policy'         => 'Agency Policy',
                    'procedure'      => 'Agency Procedure',
                    'internal'       => 'WhistleblowerShield.org Internal Adjustment',
                    'other'          => 'Other',
                ],
                'default_value' => 'statute',
                'allow_null'    => 0,
                'ui'            => 1,
                'return_format' => 'value',
            ],

            // ── Parent Post ID ────────────────────────────────────────

            [
                'key'          => 'field_legal_update_parent_post_id',
                'label'        => 'Parent Post ID',
                'name'         => 'ws_legal_update_parent_post_id',
                'type'         => 'text',
                'instructions' => 'The ID of the parent post to which this update relates.',
            ],

            // ── Law / Statute Name ────────────────────────────────────────

            [
                'key'          => 'field_legal_update_parent_post_type',
                'label'        => 'Parent Post Type',
                'name'         => 'ws_legal_update_parent_post_type',
                'type'         => 'text',
                'instructions' => 'The type of the parent post to which this update relates.',
            ],

            // ── Law / Statute Name ────────────────────────────────────────

            [
                'key'          => 'field_legal_update_law_name',
                'label'        => 'Law Name Updated',
                'name'         => 'ws_legal_update_law_name',
                'type'         => 'text',
                'instructions' => 'The name of the law affected by this update.',
            ],

            // ── Summary ───────────────────────────────────────────────────

            [
                'key'          => 'field_legal_update_summary_wysiwyg',
                'label'        => 'Plain-English Summary',
                'name'         => 'ws_legal_update_summary_wysiwyg',
                'type'         => 'wysiwyg',
                'instructions' => 'Brief plain-english summary of the legal change and its significance for whistleblowers.',
                'tabs'         => 'all',
                'toolbar'      => 'basic',
                'media_upload' => 0,
            ],

            // ── Effective Date ────────────────────────────────────────────

            [
                'key'            => 'field_legal_update_effective_date',
                'label'          => 'Effective Date',
                'name'           => 'ws_legal_update_effective_date',
                'type'           => 'date_picker',
                'instructions'   => 'When does this change take effect? Leave blank if not yet determined.',
                'display_format' => 'F j, Y',
                'return_format'  => 'Y-m-d',
                'first_day'      => 1,
            ],

            // ── Tab: Authorship & Review ──────────────────────────────────
            // Removed — registered centrally in acf-stamp-fields.php
            // (group_stamp_metadata, menu_order 90).

        ], // end fields

    ] ); // end acf_add_local_field_group

} // end ws_register_acf_legal_update
