<?php
/**
 * acf-jx-constructions.php
 *
 * Registers ACF Pro fields for the `jx-construction` CPT.
 *
 * PURPOSE
 * -------
 * Provides structured metadata for construction records, including case
 * identity, classification tags, parent-record linkage, and jurisdiction
 * impact signals used by legal analysis and summary workflows.
 *
 * GROUP: group_jx_construction_metadata
 *
 * FIELD SUMMARY
 * -------------
 * Case Identity tab:
 *   ws_jx_construction_court                          Court (select, required)
 *   ws_jx_construction_court_name                     Court Name (text, conditional)
 *   ws_jx_construction_year                           Decision Year (number, required)
 *   ws_jx_construction_is_favorable                   Favorable to Whistleblower? (true_false, optional)
 *   ws_jx_construction_official_name                  Official Name (text, required)
 *   ws_jx_construction_common_name                    Common Name (text, optional)
 *   ws_jx_construction_case_citation                  Citation (text, required)
 *   ws_jx_construction_url                            Opinion URL (url, optional)
 *   ws_jx_construction_url_is_pdf                     Link is PDF? (true_false, optional)
 *
 * Summary tab:
 *   ws_jx_construction_summary_wysiwyg                Summary (wysiwyg, required)
 *   ws_jx_construction_has_attach_flag                Attach to Jurisdiction Page (true_false, optional)
 *   ws_jx_construction_display_order                  Display Order (number, conditional)
 *
 * Classification tab:
 *   ws_jx_construction_protected_disclosures               Disclosure Category (multi_select, optional)
 *   ws_jx_construction_protected_classes              Protected Class (multi_select, optional)
 *   ws_jx_construction_protected_class_details        Protected Class Details (textarea, optional)
 *   ws_jx_construction_disclosure_targets             Disclosure Targets (multi_select, optional)
 *   ws_jx_construction_disclosure_target_details      Disclosure Targets Details (textarea, optional)
 *   ws_jx_construction_adverse_actions           Adverse Action Types (multi_select, optional)
 *   ws_jx_construction_adverse_action_details    Adverse Action Details (textarea, optional)
 *   ws_jx_construction_process_types                  Process Type (multi_select, optional)
 *   ws_jx_construction_remedies                       Remedies (multi_select, optional)
 *   ws_jx_construction_remedy_details                 Remedies Details (textarea, optional)
 *   ws_jx_construction_fee_shifting                  Fee Shifting (multi_select, optional)
 *   ws_jx_construction_employer_defenses              Employer Defense (multi_select, optional)
 *   ws_jx_construction_employer_defense_details       Employer Defense Details (textarea, optional)
 *   ws_jx_construction_employee_standard             Employee Standard (multi_select, optional)
 *   ws_jx_construction_employee_standard_details      Employee Standard Details (textarea, optional)
 *
 * Relationships tab:
 *   ws_jx_construction_statute_id                     Parent Statute (post_object, optional)
 *   ws_jx_construction_comlaw_id                      Parent Common Law Doctrine (post_object, optional)
 *   ws_jx_construction_affected_jx                    Affected Jurisdictions (multi_select, optional)
 *   ws_jx_construction_last_reviewed                  Last Verified Date (text, optional)
 *
 * Reference Materials tab:
 *   ws_jx_construction_ref_materials                  Reference Materials (relationship, optional)
 * 
 * Hidden fields:
 *   _ws_jx_construction_id                            Ingest Dedupe Code (text, hidden)
 *   _ws_auto_source_chain                             Source Chain [role|tool] (array, hidden, created by ingest) — Research Provenance
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
 * @since      2.4.0
 * @version    3.17.0
 * @author     Whistleblower Shield
 * @link       https://whistleblowershield.org
 * @copyright  Copyright (c) Whistleblower Shield
 *
 */

defined( 'ABSPATH' ) || exit;

add_action( 'acf/init', 'ws_register_acf_jx_construction_s' );

function ws_register_acf_jx_construction_s() {

    if ( ! function_exists( 'acf_add_local_field_group' ) ) {
        return;
    }

    acf_add_local_field_group( [
        'key'                   => 'group_jx_construction_metadata',
        'title'                 => 'construction Details',
        'menu_order'            => 0,
        'position'              => 'normal',
        'style'                 => 'default',
        'label_placement'       => 'top',
        'instruction_placement' => 'label',
        'active'                => true,

        'location' => [ [ [
            'param'    => 'post_type',
            'operator' => '==',
            'value'    => 'jx-construction',
        ] ] ],

        'fields' => [

            // ────────────────────────────────────────────────────────────────
            // Tab: Case Identity
            //
            // Core bibliographic data — the court, case name, citation,
            // year, and a link to the full opinion.
            // ────────────────────────────────────────────────────────────────

            [
                'key'   => 'field_jx_construction_case_identity_tab',
                'label' => 'Case Identity',
                'type'  => 'tab',
            ],

            [
                'key'           => 'field_jx_construction_court',
                'label'         => 'Court',
                'name'          => 'ws_jx_construction_court',
                'type'          => 'select',
                'instructions'  => 'Select the court that issued this decision. For state court decisions, select "State Level" and enter the court name in the field below.',
                'choices'       => [],  // populated by ws_construction_load_court_choices()
                'allow_null'    => 0,
                'required'      => 1,
                'ui'            => 1,
                'return_format' => 'value',
                'wrapper'       => [ 'width' => '50' ],
            ],

            [
                'key'               => 'field_jx_construction_court_name',
                'label'             => 'Court Name',
                'name'              => 'ws_jx_construction_court_name',
                'type'              => 'text',
                'instructions'      => 'Enter the court name. Include jurisdiction and level where relevant, e.g., "Superior Court of California, Sacramento County". This field only appears when "Other" is selected above.',
                'required'          => 1,
                'conditional_logic' => [ [ [
                    'field'    => 'field_jx_construction_court',
                    'operator' => '==',
                    'value'    => 'other',
                ] ] ],
                'wrapper'           => [ 'width' => '50' ],
            ],

            [
                'key'          => 'field_jx_construction_year',
                'label'        => 'Decision Year',
                'name'         => 'ws_jx_construction_year',
                'type'         => 'number',
                'instructions' => 'Four-digit year the decision was issued.',
                'required'     => 1,
                'min'          => 1900,
                'max'          => 2099,
                'step'         => 1,
                'wrapper'      => [ 'width' => '25' ],
            ],

            [
                'key'           => 'field_jx_construction_is_favorable',
                'label'         => 'Favorable to Whistleblower?',
                'name'          => 'ws_jx_construction_is_favorable',
                'type'          => 'true_false',
                'instructions'  => 'Does this ruling support the whistleblower\'s position?',
                'ui'            => 1,
                'ui_on_text'    => 'Yes',
                'ui_off_text'   => 'No',
                'default_value' => 0,
                'wrapper'       => [ 'width' => '25' ],
            ],

            [
                'key'          => 'field_jx_construction_official_name',
                'label'        => 'Official Name',
                'name'         => 'ws_jx_construction_official_name',
                'type'         => 'text',
                'instructions' => 'Full case name, e.g., "Bechtel v. Administrative Review Board".',
                'required'     => 1,
                'wrapper'      => [ 'width' => '70' ],
            ],

            [
                'key'          => 'field_jx_construction_common_name',
                'label'        => 'Common Name',
                'name'         => 'ws_jx_construction_common_name',
                'type'         => 'text',
                'instructions' => 'Shortened or colloquial name if this case is commonly cited by a shorter title — e.g., "Bechtel". Leave blank if no common name applies.',
                'required'     => 0,
                'wrapper'      => [ 'width' => '30' ],
            ],

            [
                'key'          => 'field_jx_construction_case_citation',
                'label'        => 'Citation',
                'name'         => 'ws_jx_construction_case_citation',
                'type'         => 'text',
                'instructions' => 'Standard legal citation, e.g., "710 F.3d 443 (1st Cir. 2013)".',
                'required'     => 1,
                'wrapper'      => [ 'width' => '30' ],
            ],

            [
                'key'          => 'field_jx_construction_id',
                'label'        => 'Construction ID',
                'name'         => 'ws_jx_construction_id',
                'type'         => 'text',
                'instructions' => 'Unique identifier for this construction.',
                'required'     => 1,
            ],

            [
                'key'          => 'field_jx_construction_url',
                'label'        => 'Opinion URL',
                'name'         => 'ws_jx_construction_url',
                'type'         => 'url',
                'instructions' => 'Link to the full opinion (CourtListener, PACER, etc.).',
            ],

            [
                'key'           => 'field_jx_construction_url_is_pdf',
                'label'         => 'Link is PDF?',
                'name'          => 'ws_jx_construction_url_is_pdf',
                'type'          => 'true_false',
                'instructions'  => 'Enable if the opinion URL links directly to a PDF document.',
                'ui'            => 1,
                'ui_on_text'    => 'PDF',
                'ui_off_text'   => 'No',
                'default_value' => 0,
            ],

            // ────────────────────────────────────────────────────────────────
            // Tab: Summary
            //
            // The substance of the ruling — what legal question the court
            // decided and how it relates to process type.
            // ────────────────────────────────────────────────────────────────

            [
                'key'   => 'field_jx_construction_summary_tab',
                'label' => 'Summary',
                'type'  => 'tab',
            ],

            [
                'key'           => 'field_jx_construction_has_attach_flag',
                'label'         => 'Attach to Jurisdiction Page',
                'name'          => 'ws_jx_construction_has_attach_flag',
                'type'          => 'true_false',
                'instructions'  => 'Enable to include this construction in the rendered section on the jurisdiction page. Disable to store for reference only.',
                'ui'            => 1,
                'ui_on_text'    => 'Attached',
                'ui_off_text'   => 'Unattached',
                'default_value' => 0,
            ],

            [
                'key'               => 'field_jx_construction_display_order',
                'label'             => 'Display Order',
                'name'              => 'ws_jx_construction_display_order',
                'type'              => 'number',
                'instructions'      => 'Set the order in which this construction appears on the jurisdiction page. Lower numbers appear first.',
                'min'               => 1,
                'step'              => 1,
                'conditional_logic' => [ [ [
                    'field'    => 'field_jx_construction_has_attach_flag',
                    'operator' => '==',
                    'value'    => '1',
                ] ] ],
            ],

            // ────────────────────────────────────────────────────────────────
            // Tab: Classification
            //
            // Doctrinal taxonomy fields mirroring jx-statute. Tag only what
            // the construction genuinely addresses or clarifies — do not
            // inherit from the parent statute. has-details sentinel pattern
            // active on all supporting taxonomies — companion _details fields
            // follow each.
            // ────────────────────────────────────────────────────────────────

            [
                'key'   => 'field_jx_construction_classification_tab',
                'label' => 'Classification',
                'type'  => 'tab',
            ],

            [
                'key'           => 'field_jx_construction_protected_disclosures',
                'label'         => 'Disclosure Category',
                'name'          => 'ws_jx_construction_protected_disclosures',
                'type'          => 'taxonomy',
                'taxonomy'      => 'ws_protected_disclosure',
                'field_type'    => 'multi_select',
                'instructions'  => 'Subject matter addressed or clarified by this construction. Tag only what the construction genuinely explains or narrows.',
                'add_term'      => 0,
                'save_terms'    => 1,
                'load_terms'    => 1,
                'return_format' => 'id',
            ],

            [
                'key'           => 'field_jx_construction_protected_classes',
                'label'         => 'Protected Class',
                'name'          => 'ws_jx_construction_protected_classes',
                'type'          => 'taxonomy',
                'taxonomy'      => 'ws_protected_class',
                'field_type'    => 'multi_select',
                'instructions'  => 'Worker classification addressed or clarified by this construction. Tag only where the construction explicitly turns on or explains protected class applicability.',
                'add_term'      => 0,
                'save_terms'    => 1,
                'load_terms'    => 1,
                'return_format' => 'id',
            ],

            [
                'key'          => 'field_jx_construction_protected_class_details',
                'label'        => 'Protected Class Details',
                'name'         => 'ws_jx_construction_protected_class_details',
                'type'         => 'textarea',
                'rows'         => 3,
                'instructions' => 'Describe nuance in protected class coverage as addressed by this construction.',
                // conditional_logic set dynamically — see ws_jx_construction_details_conditional()
            ],

            [
                'key'           => 'field_jx_construction_disclosure_targets',
                'label'         => 'Disclosure Targets',
                'name'          => 'ws_jx_construction_disclosure_targets',
                'type'          => 'taxonomy',
                'taxonomy'      => 'ws_disclosure_target',
                'field_type'    => 'multi_select',
                'instructions'  => 'Reporting target addressed or clarified by this construction. Tag only where the construction explicitly discusses or turns on the reporting channel.',
                'add_term'      => 0,
                'save_terms'    => 1,
                'load_terms'    => 1,
                'return_format' => 'id',
            ],

            [
                'key'           => 'field_jx_construction_employment_sectors',
                'label'         => 'Employment Sectors',
                'name'          => 'ws_jx_construction_employment_sectors',
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
                'key'          => 'field_jx_construction_disclosure_target_details',
                'label'        => 'Disclosure Targets Details',
                'name'         => 'ws_jx_construction_disclosure_target_details',
                'type'         => 'textarea',
                'rows'         => 3,
                'instructions' => 'Describe nuance in the reporting channel as addressed by this construction.',
                // conditional_logic set dynamically — see ws_jx_construction_details_conditional()
            ],

            [
                'key'           => 'field_jx_construction_has_employer_threshold',
                'label'         => 'Employer Size Threshold',
                'name'          => 'ws_jx_construction_has_employer_threshold',
                'type'          => 'true_false',
                'instructions'  => 'Enable when this ruling addresses or modifies employer size threshold coverage.',
                'ui'            => 1,
                'ui_on_text'    => 'Yes',
                'ui_off_text'   => 'No',
                'default_value' => 0,
            ],

            [
                'key'               => 'field_jx_construction_employer_threshold_details',
                'label'             => 'Employer Threshold Details',
                'name'              => 'ws_jx_construction_employer_threshold_details',
                'type'              => 'textarea',
                'instructions'      => 'Describe how this ruling affects employer size threshold coverage.',
                'required'          => 0,
                'rows'              => 3,
                'conditional_logic' => [ [ [
                    'field'    => 'field_jx_construction_has_employer_threshold',
                    'operator' => '==',
                    'value'    => '1',
                ] ] ],
            ],

            [
                'key'           => 'field_jx_construction_adverse_actions',
                'label'         => 'Adverse Action Types',
                'name'          => 'ws_jx_construction_adverse_actions',
                'type'          => 'taxonomy',
                'taxonomy'      => 'ws_adverse_action',
                'field_type'    => 'multi_select',
                'instructions'  => 'Retaliatory action addressed or clarified by this construction. Tag only where the construction explicitly explains or narrows the type of adverse action covered.',
                'add_term'      => 0,
                'save_terms'    => 1,
                'load_terms'    => 1,
                'return_format' => 'id',
            ],

            [
                'key'          => 'field_jx_construction_adverse_action_details',
                'label'        => 'Adverse Action Details',
                'name'         => 'ws_jx_construction_adverse_action_details',
                'type'         => 'textarea',
                'rows'         => 3,
                'instructions' => 'Describe nuance in adverse action coverage as addressed by this construction.',
                // conditional_logic set dynamically — see ws_jx_construction_details_conditional()
            ],

            [
                'key'           => 'field_jx_construction_process_types',
                'label'         => 'Process Type',
                'name'          => 'ws_jx_construction_process_types',
                'type'          => 'taxonomy',
                'taxonomy'      => 'ws_process_type',
                'field_type'    => 'multi_select',
                'instructions'  => 'Procedural route addressed or clarified by this construction. Tag only where the construction explicitly explains or narrows procedural requirements or options.',
                'add_term'      => 0,
                'save_terms'    => 1,
                'load_terms'    => 1,
                'return_format' => 'id',
            ],

            [
                'key'           => 'field_jx_construction_remedies',
                'label'         => 'Remedies',
                'name'          => 'ws_jx_construction_remedies',
                'type'          => 'taxonomy',
                'taxonomy'      => 'ws_remedy',
                'field_type'    => 'multi_select',
                'instructions'  => 'Remedies addressed, clarified, or limited by this construction. Tag only where the construction explicitly explains remedy availability or scope.',
                'add_term'      => 0,
                'save_terms'    => 1,
                'load_terms'    => 1,
                'return_format' => 'id',
            ],

            [
                'key'          => 'field_jx_construction_remedy_details',
                'label'        => 'Remedies Details',
                'name'         => 'ws_jx_construction_remedy_details',
                'type'         => 'textarea',
                'rows'         => 3,
                'instructions' => 'Describe nuance in remedy availability or scope as addressed by this construction.',
                // conditional_logic set dynamically — see ws_jx_construction_details_conditional()
            ],

            [
                'key'           => 'field_jx_construction_fee_shifting_rules',
                'label'         => 'Fee Shifting Rules',
                'name'          => 'ws_jx_construction_fee_shifting_rules',
                'type'          => 'taxonomy',
                'taxonomy'      => 'ws_fee_shifting_rule',
                'field_type'    => 'multi_select',
                'instructions'  => 'Fee-shifting rule addressed or clarified by this construction. Tag only where the construction explicitly explains fee-shifting applicability or limits. Single value.',
                'add_term'      => 0,
                'save_terms'    => 1,
                'load_terms'    => 1,
                'return_format' => 'id',
            ],

            [
                'key'           => 'field_jx_construction_employer_defenses',
                'label'         => 'Employer Defense',
                'name'          => 'ws_jx_construction_employer_defenses',
                'type'          => 'taxonomy',
                'taxonomy'      => 'ws_employer_defense',
                'field_type'    => 'multi_select',
                'instructions'  => 'Employer defense addressed, validated, or rejected by this construction. Tag only where the construction explicitly explains or limits a defense posture.',
                'add_term'      => 0,
                'save_terms'    => 1,
                'load_terms'    => 1,
                'return_format' => 'id',
            ],

            [
                'key'          => 'field_jx_construction_employer_defense_details',
                'label'        => 'Employer Defense Details',
                'name'         => 'ws_jx_construction_employer_defense_details',
                'type'         => 'textarea',
                'rows'         => 3,
                'instructions' => 'Describe nuance in the employer defense posture as addressed by this construction.',
                // conditional_logic set dynamically — see ws_jx_construction_details_conditional()
            ],

            [
                'key'           => 'field_jx_construction_employee_standard',
                'label'         => 'Employee Standard',
                'name'          => 'ws_jx_construction_employee_standard',
                'type'          => 'taxonomy',
                'taxonomy'      => 'ws_employee_standard',
                'field_type'    => 'multi_select',
                'instructions'  => 'Burden-of-proof standard addressed or clarified by this construction. Tag only where the construction explicitly explains or narrows the employee burden standard.',
                'add_term'      => 0,
                'save_terms'    => 1,
                'load_terms'    => 1,
                'return_format' => 'id',
            ],

            [
                'key'          => 'field_jx_construction_employee_standard_details',
                'label'        => 'Employee Standard Details',
                'name'         => 'ws_jx_construction_employee_standard_details',
                'type'         => 'textarea',
                'rows'         => 3,
                'instructions' => 'Describe nuance in the burden-of-proof standard as addressed by this construction.',
                // conditional_logic set dynamically — see ws_jx_construction_details_conditional()
            ],

            // ────────────────────────────────────────────────────────────────
            // Tab: Relationships
            //
            // Links this construction back to statute and/or common-law parent.
            // Jurisdiction scope is provided by ws_jurisdiction taxonomy.
            // ────────────────────────────────────────────────────────────────

            [
                'key'   => 'field_jx_construction_relationships_tab',
                'label' => 'Relationships',
                'type'  => 'tab',
            ],

            [
                'key'           => 'field_jx_construction_statute_id',
                'label'         => 'Parent Statute',
                'name'          => 'ws_jx_construction_statute_id',
                'type'          => 'post_object',
                'post_type'     => [ 'jx-statute' ],
                'instructions'  => 'Parent statute this case interprets, when statute-linked.',
                'required'      => 0,
                'multiple'      => 0,
                'allow_null'    => 1,
                'ui'            => 1,
                'return_format' => 'id',
            ],

            [
                'key'           => 'field_jx_construction_comlaw_id',
                'label'         => 'Parent Common Law Doctrine',
                'name'          => 'ws_jx_construction_comlaw_id',
                'type'          => 'post_object',
                'post_type'     => [ 'jx-common-law' ],
                'instructions'  => 'Parent common-law doctrine this case interprets, when doctrine-linked.',
                'required'      => 0,
                'multiple'      => 0,
                'allow_null'    => 1,
                'ui'            => 1,
                'return_format' => 'id',
            ],

            [
                'key'           => 'field_jx_construction_affected_jx',
                'label'         => 'Affected Jurisdictions',
                'name'          => 'ws_jx_construction_affected_jx',
                'type'          => 'taxonomy',
                'taxonomy'      => WS_JURISDICTION_TAXONOMY,
                'field_type'    => 'multi_select',
                'instructions'  => 'Jurisdictions bound by this ruling. Auto-computed on save from the selected court\'s geographic scope (federal or state court matrix). Empty = SCOTUS (all jurisdictions). Override manually only when the court\'s scope does not reflect the ruling\'s actual reach.',
                'required'      => 0,
                'add_term'      => 0,
                'save_terms'    => 0,
                'load_terms'    => 1,
                'return_format' => 'id',
            ],

            // ── Tab: Authorship & Review ──────────────────────────────────
            // Removed — registered centrally in acf-stamp-fields.php
            // (group_stamp_metadata, menu_order 90).

            // ── Last Verified Date ────────────────────────────────────────
            //
            // Content-owned field — not a stamp. Retained here in the
            // construction's own group.

            [
                'key'          => 'field_jx_construction_last_reviewed',
                'label'        => 'Last Verified Date',
                'name'         => 'ws_jx_construction_last_reviewed',
                'type'         => 'text',
                'instructions' => 'Update this date each time the record is meaningfully revised.',
            ],

            // ── Tab: Plain-English ───────────────────────────────────────
            // Removed — registered centrally in acf-plain-english-fields.php
            // (group_plain_english_metadata, menu_order 85).

            // ── Tab: Reference Materials ───────────────────────────────────
            //
            // Links this construction to ws-reference records for researchers
            // and legal professionals. Not rendered on jurisdiction pages.
            // Only approved references display publicly via [ws_reference_page].

            [
                'key'   => 'field_jx_construction_ref_materials_tab',
                'label' => 'Reference Materials',
                'type'  => 'tab',
            ],

            [
                'key'           => 'field_jx_construction_ref_materials',
                'label'         => 'Reference Materials',
                'name'          => 'ws_jx_construction_ref_materials',
                'type'          => 'relationship',
                'post_type'     => [ 'ws-reference' ],
                'filters'       => [ 'search' ],
                'instructions'  => 'Attach external reference materials relevant to this record. Only approved references will display publicly. These are for researchers and legal professionals — not for primary users seeking guidance.',
                'min'           => 0,
                'max'           => 0,
                'return_format' => 'object',
            ],

        ],
    ] );

} // end ws_register_acf_jx_construction_s


// ── Conditional logic: taxonomy sentinel-gated details fields ─────────────────
//
// The following detail textareas are shown when the companion trigger field
// includes sentinel slug 'has-details':
// - ws_jx_construction_protected_classes
// - ws_jx_construction_disclosure_targets
// - ws_jx_construction_adverse_actions
// - ws_jx_construction_remedies
// - ws_jx_construction_employee_standard
// - ws_jx_construction_employer_defenses

add_filter( 'acf/load_field', 'ws_jx_construction_details_conditional' );

function ws_jx_construction_details_conditional( $field ) {

    static $map = [
        'field_jx_construction_protected_class_details'     => [ 'ws_protected_class',      'field_jx_construction_protected_classes' ],
        'field_jx_construction_disclosure_target_details'   => [ 'ws_disclosure_target',    'field_jx_construction_disclosure_targets' ],
        'field_jx_construction_adverse_action_details'      => [ 'ws_adverse_action',       'field_jx_construction_adverse_actions' ],
        'field_jx_construction_remedy_details'              => [ 'ws_remedy',               'field_jx_construction_remedies' ],
        'field_jx_construction_employee_standard_details'   => [ 'ws_employee_standard',    'field_jx_construction_employee_standard' ],
        'field_jx_construction_employer_defense_details'    => [ 'ws_employer_defense',     'field_jx_construction_employer_defenses' ],
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


// ── Court choices: context-aware select population ────────────────────────────
//
// Builds the court select list based on parent record scope:
//
//   Federal statute (has 'us' ws_jurisdiction term):
//     All federal courts (SCOTUS + circuits + districts) merged with all
//     state courts. $_ws_federal_court_matrix and $_ws_state_court_matrix
//     are merged.
//
//   State statute (does not have 'us' term):
//     State courts only ($_ws_state_court_matrix). Federal courts do not
//     interpret state statutes.
//
//   Unknown (no parent resolved yet):
//     Defaults to showing all courts (federal + state) as a safe fallback.
//
// Parent context is resolved from saved meta (existing records) or URL
// parameters (new records): statute_id, then comlaw_id.
//
// The 'other' entry (level=99) sorts last and reveals the free-text
// ws_jx_construction_court_name field for courts not in either matrix.
//
// Sorted by level ascending so SCOTUS / state supreme courts appear before
// appellate courts, which appear before district / trial courts.

add_filter( 'acf/load_field/key=field_jx_construction_court', 'ws_jx_construction_load_court_choices' );

function ws_jx_construction_load_court_choices( $field ) {
    global $_ws_federal_court_matrix, $_ws_state_court_matrix, $post;

    if ( empty( $_ws_federal_court_matrix ) ) {
        return $field;
    }

    // Resolve parent context — saved meta first, URL param fallback.
    $parent_id = 0;
    if ( $post && get_post_type( $post->ID ) === 'jx-construction' && get_post_status( $post->ID ) !== 'auto-draft' ) {
        $statute_id = (int) get_post_meta( $post->ID, 'ws_jx_construction_statute_id', true );
        $comlaw_id = (int) get_post_meta( $post->ID, 'ws_jx_construction_comlaw_id', true );
        $parent_id = $statute_id > 0 ? $statute_id : $comlaw_id;
    }
    if ( ! $parent_id && isset( $_GET['statute_id'] ) ) {
        $parent_id = absint( $_GET['statute_id'] );
    }
    if ( ! $parent_id && isset( $_GET['comlaw_id'] ) ) {
        $parent_id = absint( $_GET['comlaw_id'] );
    }

    // Determine parent scope. Unknown parent defaults to showing all courts.
    $is_federal = ! $parent_id || has_term( 'us', WS_JURISDICTION_TAXONOMY, $parent_id );

    $candidates = $is_federal
        ? array_merge( $_ws_federal_court_matrix, $_ws_state_court_matrix ?: [] )
        : ( $_ws_state_court_matrix ?: [] );

    uasort( $candidates, function( $a, $b ) {
        return $a['level'] <=> $b['level'];
    } );

    $choices = [];
    foreach ( $candidates as $key => $court ) {
        $choices[ $key ] = $court['short'];
    }

    $field['choices'] = $choices;
    return $field;
}


// ── Pre-populate ws_statute_id from ?statute_id= URL parameter ────────────────
//
// Bug #5 fix: acf/load_field cannot pre-select a post ID on post_object fields
// because ACF ignores the default_value property when rendering a relationship
// selector — it only shows a pre-selected item when the value is already stored
// in post meta.
//
// The correct hook is acf/load_value, which returns the live value ACF will
// render as the field's current selection. When the post is an auto-draft and
// statute_id is present in the URL, we return that ID as the field value so
// ACF renders the statute pre-selected. On saved posts, or when no URL
// parameter is present, we return $value unchanged.

add_filter( 'acf/load_value/key=field_jx_construction_statute_id', 'ws_jx_construction_prefill_statute_id', 5, 3 );

function ws_construction_prefill_statute_id( $value, $post_id, $field ) {

    // Only pre-fill on brand-new auto-draft posts.
    if ( get_post_status( $post_id ) !== 'auto-draft' ) {
        return $value;
    }

    // Only act when the URL carries a valid statute_id.
    if ( ! isset( $_GET['statute_id'] ) ) {
        return $value;
    }

    $statute_id = absint( $_GET['statute_id'] );

    if ( $statute_id && get_post_type( $statute_id ) === 'jx-statute' ) {
        return $statute_id;
    }

    return $value;
}

// ── Pre-populate ws_jx_construction_comlaw_id from ?comlaw_id= URL param ──
//
// Mirrors ws_construction_prefill_statute_id() above.
// When a new construction is opened from the common-law construction metabox,
// comlaw_id is passed as a URL param. acf/load_value returns it as the
// field's live value so ACF renders the doctrine pre-selected.

add_filter( 'acf/load_value/key=field_jx_construction_comlaw_id', 'ws_jx_construction_prefill_comlaw_id', 5, 3 );

function ws_construction_prefill_comlaw_id( $value, $post_id, $field ) {

    // Only pre-fill on brand-new auto-draft posts.
    if ( get_post_status( $post_id ) !== 'auto-draft' ) {
        return $value;
    }

    // Only act when the URL carries a valid comlaw_id.
    if ( ! isset( $_GET['comlaw_id'] ) ) {
        return $value;
    }

    $comlaw_id = absint( $_GET['comlaw_id'] );

    if ( $comlaw_id && get_post_type( $comlaw_id ) === 'jx-common-law' ) {
        return $comlaw_id;
    }

    return $value;
}


// ── Auto-populate ws_jx_construction_affected_jx ─────────────────────────────────
// ── from court matrix on every save ────────────────────────────────────────
//
// Runs at priority 20 (after ACF saves its fields at 10). Reads the court key
// saved to ws_jx_construction_court, looks it up via ws_court_lookup() (checks both
// $_ws_federal_court_matrix and $_ws_state_court_matrix), and resolves
// ws_jx_codes to ws_jurisdiction taxonomy term IDs.
//
// SCOTUS (ws_jx_codes = us): writes an empty array. The query/render layer
// treats empty affected_jx + SCOTUS court as "us" — avoids
// storing all 60+ term IDs in meta unnecessarily.
//
// Recomputes on every save so the value stays in sync if the court is changed.

add_action( 'acf/save_post', 'ws_jx_construction_auto_populate_affected_jx', 20 );

function ws_jx_construction_auto_populate_affected_jx( $post_id ) {

    if ( get_post_type( $post_id ) !== 'jx-construction' ) {
        return;
    }

    $court_key  = get_post_meta( $post_id, 'ws_jx_construction_court', true );
    $court_data = ws_court_lookup( $court_key );

    if ( ! $court_data ) {
        return;
    }

    $jx_codes = $court_data['ws_jx_codes'];

    // Other: geographic scope is unknown — leave affected_jx for manual entry.
    if ( $jx_codes === '__manual__' ) {
        return;
    }

    
    //     // SCOTUS: null = all jurisdictions. Store empty to signal bind-all.
    // if ( $jx_codes === 'us' ) {
    //     update_post_meta( $post_id, 'ws_jx_construction_affected_jx', [] );
    //     return;
    // }

    // Resolve each USPS code to a ws_jurisdiction term ID.
    $term_ids = [];
    foreach ( $jx_codes as $code ) {
        $term = ws_jx_term_by_code( $code );
        if ( $term && ! is_wp_error( $term ) ) {
            $term_ids[] = $term->term_id;
        }
    }
    if( count($term_ids) > 1 ) {
        update_post_meta( $post_id, 'ws_jx_construction_has_affected_jx', true );
        update_post_meta( $post_id, 'ws_jx_construction_affected_jx', array_map( 'intval', $term_ids ) );
    } else { update_post_meta( $post_id, 'ws_jx_construction_has_affected_jx', false );
             delete_post_meta( $post_id, 'ws_jx_construction_affected_jx' );
    }
}
