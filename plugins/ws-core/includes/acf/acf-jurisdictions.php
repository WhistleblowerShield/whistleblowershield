<?php
/**
 * acf-jurisdictions.php
 *
 * Registers ACF Pro fields for the `jurisdiction` CPT.
 *
 * PURPOSE
 * -------
 * Provides structured metadata for jurisdiction records, including canonical
 * taxonomy identity, government link targets, and flag assets rendered on
 * jurisdiction pages and related navigation components.
 *
 * GROUP: group_jurisdiction_metadata
 *
 * FIELD SUMMARY
 * -------------
 * Identity tab:
 *   ws_jurisdiction_jx            Jurisdiction taxonomy (select, required)
 *   ws_jurisdiction_class         Jurisdiction Class (text, required)
 *   ws_jurisdiction_name          Jurisdiction Name (text, required)
 *
 * Government Leadership URLs tab:
 *   ws_jx_gov_portal_url          Government Portal URL (url, optional)
 *   ws_jx_gov_portal_label        Government Portal Label (text, optional)
 *   ws_jx_executive_url           Executive Office URL (url, optional)
 *   ws_jx_executive_label         Executive Office Title (text, optional)
 *   ws_jx_wb_authority_url        Whistleblower Authority URL (url, optional)
 *   ws_jx_wb_authority_label      Whistleblower Authority Office (text, optional)
 *   ws_jx_legislature_url         Legislature URL (url, optional)
 *   ws_jx_legislature_label       Legislature Name (text, optional)
 *
 * Flag tab:
 *   ws_jx_flag_image              Flag Image (image, optional)
 *   ws_jx_flag_attribution        Flag Attribution (text, optional)
 *   ws_jx_flag_source_url         Flag Source URL (url, optional)
 *   ws_jx_flag_license            Flag License (text, optional)
 *
 * //Record Management tab:
 * //  ws_auto_last_edited_author    Last Editor (user, auto)
 * //  ws_auto_last_edited_date      Date Last Edited (text, auto)
 *
 * SHARED WORKFLOW GROUPS
 * ----------------------
 *   - group_stamp_metadata (acf-stamp-fields.php, menu_order 90)
 *   - group_source_verify_metadata (acf-source-verify.php)
 * 
 * SHARDED WORKFLOW FIELDS
 * -----------------------
 *  - auto-filled on save by ws_acf_write_stamp_fields()
 *    - ws_auto_last_edited_date            (text, readonly, date of last edit)
 *    - ws_auto_last_edited_author          (text, readonly, user id of last editor)
 *    - ws_auto_create_date                 (text, readonly, date authored)
 *    - ws_auto_create_author               (text, readonly, user id of author)
 *  - auto-filled on post creation by ws_acf_write_source_method()
 *    - ws_auto_source_method               (text, readonly, set to method of post creation (e.g. "ai_research", "human_created", "matrix_seeded"))
 *    - ws_auto_source_name                 (text, readonly, "Direct" when human_created or matrix_seeded, auto-set when ingested to tool or feed name (e.g. NoteBookLM or Inoreader ))
 *  - auto-set on post creation by ws_acf_write_verification_status() — (conditional on ws_auto_source_name is non-empty AND is not 'Direct')
 *    - ws_verification_status              (select: unverified, verified, defaults to unverified — set to verified by Authors, Admins required to unverified)
 *    - ws_needs_review                     (true_false, default true — must be disabled by Admin to enable publishing)
 *  - auto-filled on save by ws_acf_write_verification_status() when ws_verification_status is true
 *    - ws_auto_verified_by                (user, readonly, user that verified the post)
 *    - ws_auto_verified_date              (text, readonly, date of verification)
 * 
 * AUTO-SELECTION NOTES
 * --------------------
 * Authority and legislature labels are auto-selected on first save from
 * jurisdiction class and post slug; both are manually overridable.
 * Territory post slugs used by auto-selection logic:
 *   guam, puerto-rico, us-virgin-islands, american-samoa, northern-mariana-islands
 *
 * @package    WhistleblowerShield
 * @since      1.0.0
 * @version    3.20.1
 * @author     Whistleblower Shield
 * @link       https://whistleblowershield.org
 * @copyright  Copyright (c) Whistleblower Shield
 * 
 */

defined( 'ABSPATH' ) || exit;

add_action( 'acf/init', 'ws_register_acf_jurisdiction_fields' );

function ws_register_acf_jurisdiction_fields() {

    if ( ! function_exists( 'acf_add_local_field_group' ) ) {
        return;
    }

    acf_add_local_field_group( [

        'key'                   => 'group_jurisdiction_metadata',
        'title'                 => 'Jurisdiction Metadata',
        'menu_order'            => 0,
        'position'              => 'normal',
        'style'                 => 'default',
        'label_placement'       => 'top',
        'instruction_placement' => 'label',
        'active'                => true,

        'location' => [ [ [
            'param'    => 'post_type',
            'operator' => '==',
            'value'    => 'jurisdiction',
        ] ] ],

        'fields' => [

			// ────────────────────────────────────────────────────────────────
			// Tab: Identity
			//
			// Core identifiers for each jurisdiction record. ws_jx_code is a
			// legacy display field retained for visual reference only — the
			// canonical jurisdiction identifier is the slug of the assigned
			// WS_JURISDICTION_TAXONOMY taxonomy term. All fields are seeder-populated
			// and locked against manual editing.
			// ────────────────────────────────────────────────────────────────

            [
                'key'   => 'field_jx_identity_tab',
                'label' => 'Identity',
                'type'  => 'tab',
            ],

            [
                'key'           => 'field_jurisdiction_jx',
                'label'         => 'Jurisdiction Taxonomy aka USPS Code',
                'name'          => 'ws_jurisdiction_jx',
                'type'          => 'taxonomy',
                'taxonomy'      => WS_JURISDICTION_TAXONOMY,
                'field_type'    => 'select',
                'instructions'  => 'Internal taxonomy field. Seeded by Matrix at Plugin Install. Drives the WS_JURISDICTION_TAXONOMY term assignment for this jurisdiction record.',
				'required'      => 1,
				'add_term'      => 0,
                'save_terms'    => 0,
				'load_terms'    => 0,
                'return_format' => 'id',
                'wrapper'      => [ 'class' => 'hidden' ],
            ],


            [
                'key'     => 'field_jx_code_display',
                'label'   => 'USPS Code',
                'name'    => '',
                'type'    => 'message',
                'message' => '',
                'wrapper' => [ 'width' => '20' ],
            ],

            [
                'key'          => 'field_jurisdiction_class',
                'label'        => 'Jurisdiction Class',
                'name'         => 'ws_jurisdiction_class',
                'type'         => 'text',
                'instructions' => 'Seeder-set. Values: federal, state, territory, district.',
                'required'     => 1,
                'wrapper'      => [ 'width' => '30' ],
            ],

            [
                'key'          => 'field_jurisdiction_name',
                'label'        => 'Jurisdiction Name',
                'name'         => 'ws_jurisdiction_name',
                'type'         => 'text',
                'instructions' => 'Official name displayed to users (e.g., California, District of Columbia, Puerto Rico). Do not include "State of" prefix.',
                'required'     => 1,
                'wrapper'      => [ 'width' => '50' ],
            ],

            // ────────────────────────────────────────────────────────────────
            // Tab: Government Leadership URLs
            //
            // External links rendered in the jurisdiction header. Labels are
            // selectable to accommodate naming differences across jurisdictions.
            // Whistleblower Authority and Legislature Name are auto-selected on
            // first save and can be manually corrected afterward.
            // ────────────────────────────────────────────────────────────────

            [
                'key'   => 'field_jx_government_urls_tab',
                'label' => 'Government Leadership URLs',
                'type'  => 'tab',
            ],

            // Government Portal

            [
                'key'          => 'field_jx_gov_portal_url',
                'label'        => 'Government Portal URL',
                'name'         => 'ws_jx_gov_portal_url',
                'type'         => 'url',
                'instructions' => 'Link to the jurisdiction\'s main government website (e.g., ca.gov, dc.gov).',
                'placeholder'  => 'https://',
				'wrapper'      => ['width' => '70']
            ],

            [
                'key'           => 'field_jx_gov_portal_label',
                'label'         => 'Government Portal Label',
                'name'          => 'ws_jx_gov_portal_label',
                'type'          => 'text',
                'instructions'  => 'Text shown to users for this link. Include jurisdiction name (e.g., "California State Portal").',
                'default_value' => 'Official Government Portal',
				'wrapper'      => ['width' => '30']
            ],

            // Executive Office

            [
                'key'          => 'field_jx_executive_url',
                'label'        => 'Executive Office URL',
                'name'         => 'ws_jx_executive_url',
                'type'         => 'url',
                'instructions' => 'Official website for this jurisdiction\'s executive office. Leave blank for Federal.',
                'placeholder'  => 'https://',
            	'wrapper'      => ['width' => '70']
            ],

            [
                'key'           => 'field_jx_executive_label',
                'label'         => 'Executive Office Title',
                'name'          => 'ws_jx_executive_label',
                'type'          => 'text',
                'instructions'  => 'Office title of the chief executive. Governor for states and most territories; Mayor for D.C.',
                'default_value' => 'Office of the Governor',
            	'wrapper'      => ['width' => '30']
            ],

            // Whistleblower Authority

            [
                'key'          => 'field_jx_wb_authority_url',
                'label'        => 'Whistleblower Authority URL',
                'name'         => 'ws_jx_wb_authority_url',
                'type'         => 'url',
                'instructions' => 'Website for the office handling whistleblower matters in this jurisdiction.',
                'placeholder'  => 'https://',
            	'wrapper'      => ['width' => '70']
            ],

            [
                'key'           => 'field_jx_wb_authority_label',
                'label'         => 'Whistleblower Authority Office',
                'name'          => 'ws_jx_wb_authority_label',
                'type'          => 'text',
                'instructions'  => 'Primary office for whistleblower protection and enforcement.',
                'default_value' => 'Office of the Attorney General',
            	'wrapper'      => ['width' => '30']
            ],

            // Legislature

            [
                'key'          => 'field_jx_legislature_url',
                'label'        => 'Legislature URL',
                'name'         => 'ws_jx_legislature_url',
                'type'         => 'url',
                'instructions' => 'Official website for the state legislature, territorial assembly, or Congress.',
                'placeholder'  => 'https://',
            	'wrapper'      => ['width' => '70']
            ],

            [
                'key'           => 'field_jx_legislature_label',
                'label'         => 'Legislature Name',
                'name'          => 'ws_jx_legislature_label',
                'type'          => 'text',
                'instructions'  => 'Name of the Jurisdiction\'s legislative body.',
                'default_value' => 'State Legislature',
            	'wrapper'      => ['width' => '30']
            ],

            // ────────────────────────────────────────────────────────────────
            // Tab: Flag
            //
            // Flag images are sourced from Wikimedia Commons. Attribution,
            // source URL, and license are required for proper crediting.
            // ────────────────────────────────────────────────────────────────

            [
                'key'   => 'field_jx_flag_tab',
                'label' => 'Flag',
                'type'  => 'tab',
            ],

            [
                'key'           => 'field_jx_flag_image',
                'label'         => 'Flag Image',
                'name'          => 'ws_jx_flag_image',
                'type'          => 'image',
                'instructions'  => 'Upload the official flag image. Source from Wikimedia Commons to ensure proper licensing.',
                'return_format' => 'array',
                'preview_size'  => 'thumbnail',
                'library'       => 'uploadedTo',
				'wrapper'       => ['width' => '30' ]
			],

            [
                'key'          => 'field_jx_flag_attribution',
                'label'        => 'Flag Attribution',
                'name'         => 'ws_jx_flag_attribution',
                'type'         => 'text',
                'instructions' => 'Credit line from Wikimedia Commons. Copy from the file\'s attribution section (e.g., "Devin Cook / Public domain").',
            	'wrapper'      => ['width' => '70' ]
			],

            [
                'key'          => 'field_jx_flag_source_url',
                'label'        => 'Flag Source URL',
                'name'         => 'ws_jx_flag_source_url',
                'type'         => 'url',
                'instructions' => 'Link to the Wikimedia Commons page for this flag image. Used for attribution and license verification.',
            	'wrapper'      => ['width' => '70' ]
			],

            [
                'key'           => 'field_jx_flag_license',
                'label'         => 'Flag License',
                'name'          => 'ws_jx_flag_license',
                'type'          => 'text',
                'instructions'  => 'License from Wikimedia Commons. Most U.S. flags are Public Domain. Check file page if unsure.',
                'default_value' => 'Public Domain',
            	'wrapper'       => ['width' => '30' ]
			],

/*             // ────────────────────────────────────────────────────────────────
            // Tab: Record Management
            //
            // Jurisdiction records are seeder-generated — create_author and
            // created_date are not meaningful and are not tracked here.
            // Matrix-watch handles the audit trail for post-install edits.
            //
            // last_edited and last_edited_author are stamped automatically
            // on every ACF save by admin-hooks.php. Last Editor is editable
            // by administrators to credit a different contributor when needed.
            //
            // GMT date is hidden from the UI but written to the database.
            // ────────────────────────────────────────────────────────────────

            [
                'key'   => 'field_jx_record_management_tab',
                'label' => 'Record Management',
                'type'  => 'tab',
            ],

            [
                'key'          => 'field_auto_last_edited_date_gmt',
                'label'        => 'Date Last Edited (GMT)',
                'name'         => '_ws_auto_last_edited_date_gmt',
                'type'         => 'text',
                'readonly'     => 1,
                'disabled'     => 1,
                'wrapper'      => [ 'class' => 'hidden' ],
            ],
			[
                'key'           => 'field_auto_last_edited_author',
                'label'         => 'Last Editor',
                'name'          => 'ws_auto_last_edited_author',
                'type'          => 'user',
                'instructions'  => 'User who last updated this record. Updated automatically. Admins can change to credit a different contributor.',
                'role'          => [ 'author', 'editor', 'administrator' ],
                'return_format' => 'array',
            ],
			
            [
                'key'          => 'field_auto_last_edited_date',
                'label'        => 'Date Last Edited',
                'name'         => 'ws_auto_last_edited_date',
                'type'         => 'text',
                'readonly'     => 1,
                'disabled'     => 1,
            ], */

        ], // end fields

    ] ); // end acf_add_local_field_group

} // end ws_register_acf_jurisdiction_fields



