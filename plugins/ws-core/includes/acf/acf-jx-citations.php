<?php
/**
 * acf-jx-citations.php
 *
 * Registers ACF Pro fields for the `jx-citation` CPT.
 *
 * PURPOSE
 * -------
 * Provides structured metadata for citation records used to support statutes,
 * common-law doctrines, and construction summaries with linked source
 * authority and structured classification tags.
 *
 * GROUP: group_jx_citation_metadata
 *
 * FIELD SUMMARY
 * -------------
 * Content tab:
 *   ws_jx_citation_types                          Citation Types (select, required)
 *   ws_jx_citation_protected_disclosures               Disclosure Categories (multi_select, optional)
 *   ws_jx_citation_official_name                  Official Name (text, required)
 *   ws_jx_citation_common_name                    Common Name (text, optional)
 *   ws_jx_citation_url                            Source URL (url, optional)
 *   ws_jx_citation_url_is_pdf                     Is Link to PDF? (true_false, optional)
 *   ws_jx_citation_has_attach_flag                Attach to Jurisdiction Page (true_false, optional)
 *   ws_jx_citation_display_order                  Display Order (number, conditional)
 *
 * Classification tab:
 *   ws_jx_citation_protected_classes              Protected Class (multi_select, optional)
 *   ws_jx_citation_protected_class_details        Protected Class Details (textarea, optional)
 *   ws_jx_citation_employment_sectors             Employment Sectors (multi_select, optional)
 *   ws_jx_citation_disclosure_targets             Disclosure Targets (multi_select, optional)
 *   ws_jx_citation_disclosure_target_details      Disclosure Targets Details (textarea, optional)
 *   ws_jx_citation_adverse_actions                Adverse Action (multi_select, optional)
 *   ws_jx_citation_adverse_action_details         Adverse Action Details (textarea, optional)
 *   ws_jx_citation_process_types                  Process Type (multi_select, optional)
 *   ws_jx_citation_remedies                       Remedies (multi_select, optional)
 *   ws_jx_citation_remedy_details                 Remedies Details (textarea, optional)
 *   ws_jx_citation_fee_shifting                  Fee shifting (multi_select, optional)
 *   ws_jx_citation_employer_defenses              Employer Defenses (multi_select, optional)
 *   ws_jx_citation_employer_defense_details       Employer Defense Details (textarea, optional)
 *   ws_jx_citation_employee_standards             Employee Standards (multi_select, optional)
 *   ws_jx_citation_employee_standard_details      Employee Standard Details (textarea, optional)
 *   ws_jx_citation_has_employer_threshold         Has Employer Size Threshold (true_false, optional)
 *   ws_jx_citation_employer_threshold_details     Employer Threshold Details (textarea, optional, conditional)
 *   ws_jx_citation_last_reviewed                  Last Reviewed (text, optional)
 *
 * Relationships tab:
 *   ws_jx_citation_statute_ids                    Related Statutes (post_object, optional)
 *   ws_jx_citation_comlaw_ids                     Related Common Law Doctrines (post_object, optional)
 *
 * Reference Materials tab:
 *   ws_jx_citation_ref_materials                  Reference Materials (relationship, optional)
 * 
 * Hidden fields:
 *   _ws_jx_citation_id                            Ingest Dedupe Code (text, hidden)
 *   _ws_jx_citation_stub
 *   _ws_jx_citation_stub_source
 *   _ws_jx_citation_stub_source_label
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
 *
 * @package    WhistleblowerShield
 * @since      2.3.0
 * @version    3.17.4
 * @author     Whistleblower Shield
 * @link       https://whistleblowershield.org
 * @copyright  Copyright (c) Whistleblower Shield
 *
 */

defined( 'ABSPATH' ) || exit;

// ── Field group registration ──────────────────────────────────────────────────

add_action( 'acf/init', 'ws_register_acf_jx_citations' );

function ws_register_acf_jx_citations() {

    if ( ! function_exists( 'acf_add_local_field_group' ) ) {
        return;
    }

    acf_add_local_field_group( [

        'key'                   => 'group_jx_citation_metadata',
        'title'                 => 'Jurisdiction Citation',
        'menu_order'            => 0,
        'position'              => 'normal',
        'style'                 => 'default',
        'label_placement'       => 'top',
        'instruction_placement' => 'label',
        'active'                => true,

        'location' => [ [ [
            'param'    => 'post_type',
            'operator' => '==',
            'value'    => 'jx-citation',
        ] ] ],

        'fields' => [

            // ── Tab: Content ──────────────────────────────────────────────

            [
                'key'   => 'field_jx_citation_content_tab',
                'label' => 'Content',
                'type'  => 'tab',
            ],
            [
                'key'          => 'field_jx_citation_types',
                'label'        => 'Citation Types',
                'name'         => 'ws_jx_citation_types',
                'type'         => 'select',
                'required'     => 1,
                'instructions' => 'Select one or more source categories this citation references.',
                'choices'      => [
                    'case_law'   => 'Case Law',
                    'statute'    => 'Statute',
                    'regulatory' => 'Regulatory',
                    'secondary'  => 'Secondary Source',
                ],
                'default_value' => [ 'case_law' ],
                'allow_null'    => 0,
                'multiple'      => 1,
                'return_format' => 'value',
                'ui'            => 1,
            ],
			[
				'key'           => 'field_jx_citation_protected_disclosures',
				'label'         => 'Disclosure Categories',
				'name'          => 'ws_jx_citation_protected_disclosures',
				'type'          => 'taxonomy',
				'taxonomy'      => 'ws_protected_disclosure',
				'field_type'    => 'multi_select', // Use multi-select for multiple categories
				'add_term'      => 0,
				'save_terms'    => 1,
				'load_terms'    => 1,
				'return_format' => 'id',
				'multiple'      => 1,
			],
            [
                'key'          => 'field_jx_citation_official_name',
                'label'        => 'Official Name',
                'name'         => 'ws_jx_citation_official_name',
                'type'         => 'text',
                'required'     => 1,
                'instructions' => 'The full citation as it will appear in the footnote — e.g., Lawson v. PPG Architectural Finishes, Inc., 12 Cal. 5th 703 (2022).',
            ],

            [
                'key'          => 'field_jx_citation_common_name',
                'label'        => 'Common Name',
                'name'         => 'ws_jx_citation_common_name',
                'type'         => 'text',
                'instructions' => 'Shortened or colloquial name for this citation if commonly referenced — e.g., "Lawson". Leave blank if no common name applies.',
                'required'     => 0,
            ],
            
            [
                'key'          => 'field_jx_citation_id',
                'label'        => 'Citation ID',
                'name'         => 'ws_jx_citation_id',
                'type'         => 'text',
                'instructions' => 'Unique identifier for this citation.',
                'required'     => 1,
            ],

            [
                'key'          => 'field_jx_citation_url',
                'label'        => 'Source URL',
                'name'         => 'ws_jx_citation_url',
                'type'         => 'url',
                'instructions' => 'Direct link to the source document, case, or statute.',
            ],

            [
                'key'           => 'field_jx_citation_url_is_pdf',
                'label'         => 'Is Link to PDF?',
                'name'          => 'ws_jx_citation_url_is_pdf',
                'type'          => 'true_false',
                'instructions'  => 'Enable if the source URL links directly to a PDF document. Appends "(PDF)" to the rendered link.',
                'ui'            => 1,
                'ui_on_text'    => 'PDF',
                'ui_off_text'   => 'No',
                'default_value' => 0,
            ],
            [
                'key'           => 'field_jx_citation_has_attach_flag',
                'label'         => 'Attach to Jurisdiction Page',
                'name'          => 'ws_jx_citation_has_attach_flag',
                'type'          => 'true_false',
                'instructions'  => 'Enable to include this citation in the rendered case law section on the jurisdiction page. Disable to store for reference only.',
                'ui'            => 1,
                'ui_on_text'    => 'Attached',
                'ui_off_text'   => 'Unattached',
                'default_value' => 0,
            ],
            [
                'key'               => 'field_jx_citation_display_order',
                'label'             => 'Display Order',
                'name'              => 'ws_jx_citation_display_order',
                'type'              => 'number',
                'instructions'      => 'Set the order in which this citation appears in the footnote list. Lower numbers appear first.',
                'min'               => 1,
                'step'              => 1,
                'conditional_logic' => [ [ [
                    'field'    => 'field_jx_citation_has_attach_flag',
                    'operator' => '==',
                    'value'    => '1',
                ] ] ],
            ],

            // ── Tab: Classification ───────────────────────────────────────
            //
            // Doctrinal taxonomy fields mirroring jx-statute. Tag only what
            // the cited source genuinely addresses — do not inherit from the
            // parent statute. has-details sentinel pattern active on all
            // supporting taxonomies — companion _details fields follow each.

            [
                'key'   => 'field_jx_citation_classification_tab',
                'label' => 'Classification',
                'type'  => 'tab',
            ],

            [
                'key'           => 'field_jx_citation_protected_classes',
                'label'         => 'Protected Class',
                'name'          => 'ws_jx_citation_protected_classes',
                'type'          => 'taxonomy',
                'taxonomy'      => 'ws_protected_class',
                'field_type'    => 'multi_select',
                'instructions'  => 'Worker classification at issue in this citation. Tag only where the cited source explicitly addresses or turns on protected class status.',
                'add_term'      => 0,
                'save_terms'    => 1,
                'load_terms'    => 1,
                'return_format' => 'id',
            ],

            [
                'key'          => 'field_jx_citation_protected_class_details',
                'label'        => 'Protected Class Details',
                'name'         => 'ws_jx_citation_protected_class_details',
                'type'         => 'textarea',
                'rows'         => 3,
                'instructions' => 'Describe nuance in protected class coverage as addressed by this citation.',
                // conditional_logic set dynamically — see ws_jx_citation_details_conditional()
            ],

            [
                'key'           => 'field_jx_citation_disclosure_targets',
                'label'         => 'Disclosure Targets',
                'name'          => 'ws_jx_citation_disclosure_targets',
                'type'          => 'taxonomy',
                'taxonomy'      => 'ws_disclosure_target',
                'field_type'    => 'multi_select',
                'instructions'  => 'Reporting target at issue in this citation. Tag only where the cited source explicitly discusses or turns on the reporting channel.',
                'add_term'      => 0,
                'save_terms'    => 1,
                'load_terms'    => 1,
                'return_format' => 'id',
            ],

            [
                'key'           => 'field_jx_citation_employment_sectors',
                'label'         => 'Employment Sectors',
                'name'          => 'ws_jx_citation_employment_sectors',
                'type'          => 'taxonomy',
                'taxonomy'      => 'ws_employment_sector',
                'field_type'    => 'multi_select',
                'instructions'  => 'Employment sectors this ruling affects. May refine, extend, or restrict the parent statute\'s sector coverage.',
                'required'      => 0,
                'add_term'      => 0,
                'save_terms'    => 1,
                'load_terms'    => 1,
                'return_format' => 'id',
            ],

            [
                'key'          => 'field_jx_citation_disclosure_target_details',
                'label'        => 'Disclosure Targets Details',
                'name'         => 'ws_jx_citation_disclosure_target_details',
                'type'         => 'textarea',
                'rows'         => 3,
                'instructions' => 'Describe nuance in the reporting channel as addressed by this citation.',
                // conditional_logic set dynamically — see ws_jx_citation_details_conditional()
            ],

            [
                'key'           => 'field_jx_citation_has_employer_threshold',
                'label'         => 'Employer Size Threshold',
                'name'          => 'ws_jx_citation_has_employer_threshold',
                'type'          => 'true_false',
                'instructions'  => 'Enable when this ruling addresses or modifies employer size threshold coverage.',
                'ui'            => 1,
                'ui_on_text'    => 'Yes',
                'ui_off_text'   => 'No',
                'default_value' => 0,
            ],

            [
                'key'               => 'field_jx_citation_employer_threshold_details',
                'label'             => 'Employer Threshold Details',
                'name'              => 'ws_jx_citation_employer_threshold_details',
                'type'              => 'textarea',
                'instructions'      => 'Describe how this ruling affects employer size threshold coverage.',
                'required'          => 0,
                'rows'              => 3,
                'conditional_logic' => [ [ [
                    'field'    => 'field_jx_citation_has_employer_threshold',
                    'operator' => '==',
                    'value'    => '1',
                ] ] ],
            ],

            [
                'key'           => 'field_jx_citation_adverse_actions',
                'label'         => 'Adverse Action Types',
                'name'          => 'ws_jx_citation_adverse_actions',
                'type'          => 'taxonomy',
                'taxonomy'      => 'ws_adverse_action',
                'field_type'    => 'multi_select',
                'instructions'  => 'Retaliatory action at issue in this citation. Tag only where the cited source explicitly addresses the type of adverse action taken or alleged.',
                'add_term'      => 0,
                'save_terms'    => 1,
                'load_terms'    => 1,
                'return_format' => 'id',
            ],

            [
                'key'          => 'field_jx_citation_adverse_action_details',
                'label'        => 'Adverse Action Details',
                'name'         => 'ws_jx_citation_adverse_action_details',
                'type'         => 'textarea',
                'rows'         => 3,
                'instructions' => 'Describe nuance in the adverse action coverage as addressed by this citation.',
                // conditional_logic set dynamically — see ws_jx_citation_details_conditional()
            ],

            [
                'key'           => 'field_jx_citation_process_types',
                'label'         => 'Process Type',
                'name'          => 'ws_jx_citation_process_types',
                'type'          => 'taxonomy',
                'taxonomy'      => 'ws_process_type',
                'field_type'    => 'multi_select',
                'instructions'  => 'Procedural route at issue or discussed in this citation. Tag only where the cited source explicitly addresses procedure.',
                'add_term'      => 0,
                'save_terms'    => 1,
                'load_terms'    => 1,
                'return_format' => 'id',
            ],

            [
                'key'           => 'field_jx_citation_remedies',
                'label'         => 'Remedies',
                'name'          => 'ws_jx_citation_remedies',
                'type'          => 'taxonomy',
                'taxonomy'      => 'ws_remedy',
                'field_type'    => 'multi_select',
                'instructions'  => 'Remedies discussed, awarded, or denied in this citation. Tag only where the cited source explicitly addresses remedy.',
                'add_term'      => 0,
                'save_terms'    => 1,
                'load_terms'    => 1,
                'return_format' => 'id',
            ],

            [
                'key'          => 'field_jx_citation_remedy_details',
                'label'        => 'Remedies Details',
                'name'         => 'ws_jx_citation_remedy_details',
                'type'         => 'textarea',
                'rows'         => 3,
                'instructions' => 'Describe nuance in remedy availability or scope as addressed by this citation.',
                // conditional_logic set dynamically — see ws_jx_citation_details_conditional()
            ],

            [
                'key'           => 'field_jx_citation_fee_shifting_rules',
                'label'         => 'Fee Shifting Rules',
                'name'          => 'ws_jx_citation_fee_shifting_rules',
                'type'          => 'taxonomy',
                'taxonomy'      => 'ws_fee_shifting_rule',
                'field_type'    => 'multi_select',
                'instructions'  => 'Fee-shifting outcome or discussion in this citation. Tag only where the cited source explicitly addresses fees. Single value.',
                'add_term'      => 0,
                'save_terms'    => 1,
                'load_terms'    => 1,
                'return_format' => 'id',
            ],

            [
                'key'           => 'field_jx_citation_employer_defenses',
                'label'         => 'Employer Defenses',
                'name'          => 'ws_jx_citation_employer_defenses',
                'type'          => 'taxonomy',
                'taxonomy'      => 'ws_employer_defense',
                'field_type'    => 'multi_select',
                'instructions'  => 'Employer defense raised, accepted, or rejected in this citation. Tag only where the cited source explicitly addresses a defense posture.',
                'add_term'      => 0,
                'save_terms'    => 1,
                'load_terms'    => 1,
                'return_format' => 'id',
            ],

            [
                'key'          => 'field_jx_citation_employer_defense_details',
                'label'        => 'Employer Defense Details',
                'name'         => 'ws_jx_citation_employer_defense_details',
                'type'         => 'textarea',
                'rows'         => 3,
                'instructions' => 'Describe nuance in the employer defense posture as addressed by this citation.',
                // conditional_logic set dynamically — see ws_jx_citation_details_conditional()
            ],

            [
                'key'           => 'field_jx_citation_employee_standards',
                'label'         => 'Employee Standards',
                'name'          => 'ws_jx_citation_employee_standards',
                'type'          => 'taxonomy',
                'taxonomy'      => 'ws_employee_standard',
                'field_type'    => 'multi_select',
                'instructions'  => 'Burden-of-proof standard at issue or clarified by this citation. Tag only where the cited source explicitly addresses or turns on the employee burden standard.',
                'add_term'      => 0,
                'save_terms'    => 1,
                'load_terms'    => 1,
                'return_format' => 'id',
            ],

            [
                'key'          => 'field_jx_citation_employee_standard_details',
                'label'        => 'Employee Standard Details',
                'name'         => 'ws_jx_citation_employee_standard_details',
                'type'         => 'textarea',
                'rows'         => 3,
                'instructions' => 'Describe nuance in the burden-of-proof standard as addressed by this citation.',
                // conditional_logic set dynamically — see ws_jx_citation_details_conditional()
            ],

            // ── Tab: Authorship & Review ──────────────────────────────────
            // Removed — registered centrally in acf-stamp-fields.php
            // (group_stamp_metadata, menu_order 90).

            // ── Last Reviewed ─────────────────────────────────────────────
            //
            // Content-owned field — not a stamp. Retained here in the
            // citation's own group.

            [
                'key'          => 'field_jx_citation_last_reviewed',
                'label'        => 'Last Reviewed',
                'name'         => 'ws_jx_citation_last_reviewed',
                'type'         => 'text',
                'instructions' => 'Update this date each time the citation is meaningfully revised.',
            ],

            // ── Tab: Plain-English ───────────────────────────────────────
            // Removed — registered centrally in acf-plain-english-fields.php
            // (group_plain_english_metadata, menu_order 85).

            // ── Tab: Relationships ────────────────────────────────────────
            //
            // Links this citation back to statute and/or common-law doctrine
            // parent records. Optional — a citation may be jurisdiction-wide
            // without a parent link. Multiple — one citation may be relevant
            // to more than one record in the same jurisdiction.

            [
                'key'   => 'field_jx_citation_relationships_tab',
                'label' => 'Relationships',
                'type'  => 'tab',
            ],

            [
                'key'           => 'field_jx_citation_statute_ids',
                'label'         => 'Related Statutes',
                'name'          => 'ws_jx_citation_statute_ids',
                'type'          => 'post_object',
                'post_type'     => [ 'jx-statute' ],
                'instructions'  => 'Link this citation to the statute(s) it interprets or supports. Optional — leave blank for jurisdiction-wide citations not tied to a specific statute.',
                'required'      => 0,
                'multiple'      => 1,
                'allow_null'    => 1,
                'ui'            => 1,
                'return_format' => 'id',
            ],

            [
                'key'           => 'field_jx_citation_comlaw_ids',
                'label'         => 'Related Common Law Doctrines',
                'name'          => 'ws_jx_citation_comlaw_ids',
                'type'          => 'post_object',
                'post_type'     => [ 'jx-common-law' ],
                'instructions'  => 'Link this citation to common-law doctrine record(s) it interprets or supports. Optional.',
                'required'      => 0,
                'multiple'      => 1,
                'allow_null'    => 1,
                'ui'            => 1,
                'return_format' => 'id',
            ],

            // ── Tab: Reference Materials ───────────────────────────────────
            //
            // Links this citation to ws-reference records for researchers and
            // legal professionals. Not rendered on jurisdiction pages.
            // Only approved references display publicly via [ws_reference_page].

            [
                'key'   => 'field_jx_citation_ref_materials_tab',
                'label' => 'Reference Materials',
                'type'  => 'tab',
            ],

            [
                'key'           => 'field_jx_citation_ref_materials',
                'label'         => 'Reference Materials',
                'name'          => 'ws_jx_citation_ref_materials',
                'type'          => 'relationship',
                'post_type'     => [ 'ws-reference' ],
                'filters'       => [ 'search' ],
                'instructions'  => 'Attach external reference materials relevant to this record. Only approved references will display publicly. These are for researchers and legal professionals — not for primary users seeking guidance.',
                'min'           => 0,
                'max'           => 0,
                'return_format' => 'object',
            ],

        ], // end fields

    ] ); // end acf_add_local_field_group

} // end ws_register_acf_jx_citations


// Field locking, auto-fill today, and stamp fields are handled centrally
// in admin-hooks.php via ws_acf_lock_for_non_admins(), ws_acf_autofill_today(),
// and ws_acf_write_stamp_fields().


// ── Conditional logic: taxonomy sentinel-gated details fields ─────────────────
//
// The following detail textareas are shown when the companion trigger field
// includes sentinel slug 'has-details':
// - ws_jx_citation_protected_classes
// - ws_jx_citation_disclosure_targets
// - ws_jx_citation_adverse_actions
// - ws_jx_citation_remedies
// - ws_jx_citation_employee_standards
// - ws_jx_citation_employer_defenses

add_filter( 'acf/load_field', 'ws_jx_citation_details_conditional' );

function ws_jx_citation_details_conditional( $field ) {

    static $map = [
        'field_jx_citation_protected_class_details'    => [ 'ws_protected_class',      'field_jx_citation_protected_classes' ],
        'field_jx_citation_disclosure_target_details'  => [ 'ws_disclosure_target',    'field_jx_citation_disclosure_targets' ],
        'field_jx_citation_adverse_action_details'     => [ 'ws_adverse_action',       'field_jx_citation_adverse_actions' ],
        'field_jx_citation_remedy_details'             => [ 'ws_remedy',               'field_jx_citation_remedies' ],
        'field_jx_citation_employee_standard_details'  => [ 'ws_employee_standard',    'field_jx_citation_employee_standards' ],
        'field_jx_citation_employer_defense_details'   => [ 'ws_employer_defense',     'field_jx_citation_employer_defenses' ],
    ];

    if ( ! isset( $map[ $field['key'] ] ) ) {
        return $field;
    }

    [ $taxonomy, $trigger_key ] = $map[ $field['key'] ];

    $term = get_term_by( 'slug', 'has-details', $taxonomy );
    if ( ! $term || is_wp_error( $term ) ) {
        return $field;
    }

    $field['conditional_logic'] = [ [ [
        'field'    => $trigger_key,
        'operator' => '==',
        'value'    => (string) $term->term_id,
    ] ] ];

    return $field;
}


// ── Pre-populate ws_jx_citation_statute_ids from ?statute_id= URL param ──────
//
// Mirrors ws_construction_prefill_statute_id() in acf-jx-constructions.php.
// When a new citation is opened from the statute's citation metabox,
// statute_id is passed as a URL param. acf/load_value returns it as the
// field's live value so ACF renders the statute pre-selected.
// Returns an array — ws_jx_citation_statute_ids is a multiple post_object field.

add_filter( 'acf/load_value/key=field_jx_citation_statute_ids', 'ws_citation_prefill_statute_ids', 5, 3 );

function ws_citation_prefill_statute_ids( $value, $post_id, $field ) {

    if ( get_post_status( $post_id ) !== 'auto-draft' ) {
        return $value;
    }

    if ( ! isset( $_GET['statute_id'] ) ) {
        return $value;
    }

    $statute_id = absint( $_GET['statute_id'] );

    if ( $statute_id && get_post_type( $statute_id ) === 'jx-statute' ) {
        return [ $statute_id ];
    }

    return $value;
}

// ── Pre-populate ws_jx_citation_comlaw_ids from ?common_law_id= URL param ──
//
// Mirrors ws_citation_prefill_statute_ids() above.
// When a new citation is opened from the common-law citation metabox,
// common_law_id is passed as a URL param. acf/load_value returns it as the
// field's live value so ACF renders the doctrine pre-selected.
// Returns an array — ws_jx_citation_comlaw_ids is a multiple post_object field.

add_filter( 'acf/load_value/key=field_jx_citation_comlaw_ids', 'ws_citation_prefill_comlaw_ids', 5, 3 );

function ws_citation_prefill_comlaw_ids( $value, $post_id, $field ) {

    if ( get_post_status( $post_id ) !== 'auto-draft' ) {
        return $value;
    }

    if ( ! isset( $_GET['common_law_id'] ) ) {
        return $value;
    }

    $comlaw_id = absint( $_GET['common_law_id'] );

    if ( $comlaw_id && get_post_type( $comlaw_id ) === 'jx-common-law' ) {
        return [ $comlaw_id ];
    }

    return $value;
}


// ── Admin notice: zero attached citations ─────────────────────────────────────
//
// Fires on jx-summary edit screens only. Reads the assigned ws_jurisdiction
// taxonomy term on the jx-summary post, then queries for attached jx-citation
// records scoped to that same term.
//
// Displays a warning notice if zero attached citations are found,
// prompting the summary author to act before publishing.

add_action( 'admin_notices', 'ws_jx_cite_no_citations_notice' );
function ws_jx_cite_no_citations_notice() {

    $screen = get_current_screen();
    if ( ! $screen || $screen->post_type !== 'jx-summary' || $screen->base !== 'post' ) {
        return;
    }

    global $post;
    if ( ! $post ) return;

    // Get the ws_jurisdiction taxonomy term assigned to this jx-summary.
    $terms = wp_get_post_terms( $post->ID, WS_JURISDICTION_TAXONOMY );
    if ( empty( $terms ) || is_wp_error( $terms ) ) return;

    $term_id = $terms[0]->term_id;

    // Query for attached citations scoped to this jurisdiction term.
    $attached = get_posts( [
        'post_type'      => 'jx-citation',
        'post_status'    => 'publish',
        'posts_per_page' => 1,
        'fields'         => 'ids',
        'meta_query'     => [
            [
                'key'   => 'ws_jx_citation_attach_flag',
                'value' => '1',
            ],
        ],
        'tax_query' => [ [
            'taxonomy' => WS_JURISDICTION_TAXONOMY,
            'field'    => 'term_id',
            'terms'    => $term_id,
        ] ],
    ] );

    if ( ! empty( $attached ) ) {
        return;
    }

    // Resolve a display name for this jurisdiction from the term or its post.
    $jx_post_id = ws_get_id_by_code( $terms[0]->slug );
    $jx_name    = $jx_post_id ? get_the_title( $jx_post_id ) : strtoupper( $terms[0]->slug );

    echo '<div class="notice notice-warning is-dismissible"><p>';
    echo '<strong>WhistleblowerShield — Citation Warning:</strong> ';
    echo 'No attached citations found for <strong>' . esc_html( $jx_name ) . '</strong>. ';
    echo 'The case law section will not render on the jurisdiction page until at least one ';
    echo '<a href="' . esc_url( admin_url( 'edit.php?post_type=jx-citation' ) ) . '">citation record</a> ';
    echo 'is published with <em>Attach to Jurisdiction Page</em> enabled and the ';
    echo esc_html( strtoupper( $terms[0]->slug ) ) . ' jurisdiction term assigned.';
    echo '</p></div>';
}
