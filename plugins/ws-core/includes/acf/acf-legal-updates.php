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
 *   ws_legal_update_jurisdictions          Affected jurisdictions (taxonomy, optional)
 *   ws_legal_update_multi_jurisdiction     Multi-jurisdiction flag (true_false, optional)
 *   ws_legal_update_hide_public            Public visibility toggle (true_false, optional)
 *   ws_legal_update_date                   Effective/published update date (date_picker, optional)
 *   ws_legal_update_source_url             Primary source URL (url, optional)
 *   ws_legal_update_source_url_is_pdf      Primary source URL is PDF toggle (true_false, optional)
 *   ws_legal_update_type                   Update classification (select, optional)
 *   ws_legal_update_summary_wysiwyg        Update summary body (wysiwyg, required)
 *
 * SHARED WORKFLOW GROUPS
 * ----------------------
 *   - group_stamp_metadata (acf-stamp-fields.php, menu_order 90)
 *
 * PLAIN ENGLISH
 * -------------
 * This record does not participate in a separate plain-English workflow.
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

            // ── Update Date ───────────────────────────────────────────────

            [
                'key'            => 'field_legal_update_date',
                'label'          => 'Update Date',
                'name'           => 'ws_legal_update_date',
                'type'           => 'date_picker',
                'instructions'   => 'Date the legal change took effect or was officially published.',
                'display_format' => 'F j, Y',
                'return_format'  => 'Y-m-d',
                'first_day'      => 1,
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
                    'citation'       => 'Citation Update',
                    'summary'        => 'Summary Update',
                    'interpretation' => 'Interpretation Update',
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

            // ── Law / Statute Name ────────────────────────────────────────

            [
                'key'          => 'field_legal_update_law_name',
                'label'        => 'Law / Statute Name',
                'name'         => 'ws_legal_update_law_name',
                'type'         => 'text',
                'instructions' => 'The name of the law or statute affected by this update.',
            ],

            // ── Summary ───────────────────────────────────────────────────

            [
                'key'          => 'field_legal_update_summary_wysiwyg',
                'label'        => 'Summary',
                'name'         => 'ws_legal_update_summary_wysiwyg',
                'type'         => 'wysiwyg',
                'instructions' => 'Brief summary of the legal change and its significance for whistleblowers.',
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
