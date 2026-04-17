<?php
defined( 'ABSPATH' ) || exit;

/**
 * acf-jx-statutes.php
 *
 * Registers ACF Pro fields for the `jx-statute` CPT.
 *
 * PURPOSE
 * -------
 * Provides structured metadata for individual statutes, enabling granular
 * queries for deadlines, enforcement agencies, burden of proof standards,
 * and misconduct categories.
 *
 * GROUP: group_jx_statute_metadata
 *
 * FIELD SUMMARY
 * -------------
 * Legal Basis tab:
 *   ws_jx_statute_official_name      Official name (text, required)
 *   ws_jx_statute_citation           Official statute citation (text, optional)
 *   ws_jx_statute_common_name        Common/informal name (text, optional)
 *   ws_jx_statute_disclosure_types    Disclosure Categories taxonomy (multi_select)
 *   ws_jx_statute_protected_classes    Protected Class taxonomy (multi_select)
 *   ws_jx_statute_protected_class_details Protected Class Detail (textarea, conditional on has-details term)
 *   ws_jx_statute_disclosure_targets Disclosure Targets taxonomy (multi_select)
 *   ws_jx_statute_disclosure_target_details Disclosure Targets Detail (textarea, conditional on has-details term)
 *   ws_jx_statute_adverse_action_scope Free-text scope of covered adverse actions
 *   ws_jx_statute_has_attach_flag    Editorial curation flag (true_false). Marks this
 *                                    record as one of the ~3–5 highlighted statutes shown
 *                                    on the jurisdiction summary page. NOT a visibility gate —
 *                                    unflagged statutes are accessible via taxonomy queries.
 *   ws_jx_statute_display_order      Render order among flagged items (number, conditional on attach_flag)
 *
 * Jurisdiction scope is provided by the ws_jurisdiction taxonomy — the
 * taxonomy term is assigned via the WordPress taxonomy UI, not via an ACF field.
 *
 * Statute of Limitations tab:
 *   ws_jx_statute_sol_value          Filing Window Value (number)
 *   ws_jx_statute_sol_unit           Time Unit (select)
 *   ws_jx_statute_sol_trigger        Deadline Trigger (select)
 *   ws_jx_statute_has_limit_ambiguous SOL has supplementary detail (true_false)
 *   ws_jx_statute_limit_details      SOL detail (textarea, conditional)
 *   ws_jx_statute_has_tolling_details Tolling provisions exist (true_false)
 *   ws_jx_statute_tolling_details    Tolling & Extension Details (textarea, conditional)
 *   ws_jx_statute_has_exhaustion_required Exhaustion Required? (true_false)
 *   ws_jx_statute_exhaustion_details Exhaustion Procedure & Deadline (textarea, conditional)
 *
 * Enforcement tab:
 *   ws_jx_statute_process_types       Process Types taxonomy (multi_select)
 *   ws_jx_statute_adverse_action_types Adverse Action Types taxonomy (multi_select)
 *   ws_jx_statute_adverse_action_type_details Adverse Action Detail (textarea, conditional on has-details term)
 *   ws_jx_statute_fee_shiftings       Fee Shifting taxonomy (multi_select)
 *   ws_jx_statute_remedies            Available remedies taxonomy (multi_select)
 *   ws_jx_statute_remedy_details      Remedy detail (textarea, conditional on has-details term)
 *   ws_jx_statute_local_agencies     Local Agencies (post_object)
 *   ws_jx_statute_federal_agencies   Federal Agencies (post_object)
 *   ws_jx_statute_enforcement_channel Enforcement Channel Notes (textarea)
 *
 * Burden of Proof tab:
 *   ws_jx_statute_employee_standards  Employee Standard taxonomy (multi_select)
 *   ws_jx_statute_employee_standard_details Employee Standard Detail (textarea, conditional on has-details term)
 *   ws_jx_statute_employer_defenses   Employer Defense taxonomy (multi_select)
 *   ws_jx_statute_employer_defense_details Employer Defense Details (textarea, conditional on has-details term)
 *   ws_jx_statute_has_rebuttable_presumption Rebuttable presumption exists (true_false)
 *   ws_jx_statute_rebuttable_presumption Rebuttable Presumption Details (textarea, conditional)
 *   ws_jx_statute_has_bop_details     BOP has supplementary detail (true_false)
 *   ws_jx_statute_bop_details  BOP detail (textarea, conditional)
 *   ws_jx_statute_bop_flag                BOP signal phrase (text, optional)
 *
 * Reward tab:
 *   ws_jx_statute_has_reward_available Reward available (true_false)
 *   ws_jx_statute_reward_details     Reward Details (textarea, conditional)
 *
 * Links tab:
 *   ws_jx_statute_url                Statute URL (url)
 *   ws_jx_statute_url_is_pdf     PDF link toggle (true_false)
 *
 * SHARED WORKFLOW GROUPS
 * ----------------------
 *   - group_plain_english_metadata (acf-plain-english-fields.php, menu_order 85)
 *   - group_stamp_metadata (acf-stamp-fields.php, menu_order 90)
 *   - group_source_verify_metadata (acf-source-verify.php)
 *   - group_major_edit_metadata (acf-major-edit.php, menu_order 99)
 *
 * @package    WhistleblowerShield
 * @since      2.0.0
 * @version    3.17.0
 * @author     Whistleblower Shield
 * @link       https://whistleblowershield.org
 * @copyright  Copyright (c) Whistleblower Shield
 *
 */

add_action( 'acf/init', 'ws_register_acf_jx_statutes' );

function ws_register_acf_jx_statutes() {

    if ( ! function_exists( 'acf_add_local_field_group' ) ) {
        return;
    }

    acf_add_local_field_group( [
        'key'                   => 'group_jx_statute_metadata',
        'title'                 => 'Statute Details',
        'menu_order'            => 0,
        'position'              => 'normal',
        'style'                 => 'default',
        'label_placement'       => 'top',
        'instruction_placement' => 'label',
        'active'                => true,

        'location' => [ [ [
            'param'    => 'post_type',
            'operator' => '==',
            'value'    => 'jx-statute',
        ] ] ],

        'fields' => [

            // ────────────────────────────────────────────────────────────────
            // Tab: Legal Basis
            // ────────────────────────────────────────────────────────────────

            [
                'key'   => 'field_jx_statute_legal_basis_tab',
                'label' => 'Legal Basis',
                'type'  => 'tab',
            ],

            [
                'key'          => 'field_jx_statute_official_name',
                'label'        => 'Official Name',
                'name'         => 'ws_jx_statute_official_name',
                'type'         => 'text',
                'instructions' => 'Use standard legal notation, e.g., "California Labor Code § 1102.5" or "5 U.S.C. § 2302".',
                'required'     => 1,
            ],

            [
                'key'          => 'field_jx_statute_citation',
                'label'        => 'Official Statute Citation',
                'name'         => 'ws_jx_statute_citation',
                'type'         => 'text',
                'instructions' => 'Short-form legal citation, e.g., "Cal. Lab. Code § 1102.5" or "42 U.S.C. § 5851".',
                'required'     => 0,
            ],

            [
                'key'          => 'field_jx_statute_common_name',
                'label'        => 'Common Name',
                'name'         => 'ws_jx_statute_common_name',
                'type'         => 'text',
                'instructions' => 'Informal or widely-used name for this statute, if one exists — e.g., "Sarbanes-Oxley" or "False Claims Act". Leave blank if no common name applies.',
                'required'     => 0,
            ],

            [
                'key'           => 'field_jx_statute_disclosure_types',
                'label'         => 'Disclosure Categories',
                'name'          => 'ws_jx_statute_disclosure_types',
                'type'          => 'taxonomy',
                'taxonomy'      => 'ws_disclosure_type',
                'field_type'    => 'multi_select',
                'instructions'  => 'Classify the types of misconduct this law protects.',
                'add_term'      => 0,
                'save_terms'    => 1,
                'load_terms'    => 1,
                'return_format' => 'id',
            ],

            [
                'key'           => 'field_jx_statute_protected_classes',
                'label'         => 'Protected Class',
                'name'          => 'ws_jx_statute_protected_classes',
                'type'          => 'taxonomy',
                'taxonomy'      => 'ws_protected_class',
                'field_type'    => 'multi_select',
                'instructions'  => 'Select the employee types or worker classifications protected by this statute.',
                'add_term'      => 0,
                'save_terms'    => 1,
                'load_terms'    => 1,
                'return_format' => 'id',
            ],

            [
                'key'          => 'field_jx_statute_protected_class_details',
                'label'        => 'Protected Class Details',
                'name'         => 'ws_jx_statute_protected_class_details',
                'type'         => 'textarea',
                'rows'         => 3,
                'instructions' => 'Describe nuance in the covered worker classifications — e.g., eligibility thresholds, exclusions, or statutory language distinguishing coverage.',
                // conditional_logic set dynamically — see ws_jx_statute_details_conditional()
            ],

            [
                'key'           => 'field_jx_statute_disclosure_targets',
                'label'         => 'Disclosure Targets',
                'name'          => 'ws_jx_statute_disclosure_targets',
                'type'          => 'taxonomy',
                'taxonomy'      => 'ws_disclosure_target',
                'field_type'    => 'multi_select',
                'instructions'  => 'Who must the disclosure be made to for protection to apply under this statute?',
                'add_term'      => 0,
                'save_terms'    => 1,
                'load_terms'    => 1,
                'return_format' => 'id',
            ],

            [
                'key'          => 'field_jx_statute_disclosure_target_details',
                'label'        => 'Disclosure Targets Details',
                'name'         => 'ws_jx_statute_disclosure_target_details',
                'type'         => 'textarea',
                'rows'         => 3,
                'instructions' => 'Describe any conditions, ordering requirements, or statutory language that affects which reporting channels qualify for protection.',
                // conditional_logic set dynamically — see ws_jx_statute_details_conditional()
            ],

            [
                'key'          => 'field_jx_statute_adverse_action_scope',
                'label'        => 'Adverse Action Scope',
                'name'         => 'ws_jx_statute_adverse_action_scope',
                'type'         => 'textarea',
                'rows'         => 3,
                'instructions' => 'Describe the specific workplace actions this statute considers adverse, where the taxonomy terms do not fully capture the statutory scope or nuance.',
                'required'     => 0,
            ],

            [
                'key'           => 'field_jx_statute_has_attach_flag',
                'label'         => 'Attach to Jurisdiction Page',
                'name'          => 'ws_jx_statute_has_attach_flag',
                'type'          => 'true_false',
                'instructions'  => 'Enable to include this statute in the rendered statutes section on the jurisdiction page. Disable to store for reference only.',
                'ui'            => 1,
                'ui_on_text'    => 'Attached',
                'ui_off_text'   => 'Unattached',
                'default_value' => 0,
            ],

            [
                'key'               => 'field_jx_statute_display_order',
                'label'             => 'Display Order',
                'name'              => 'ws_jx_statute_display_order',
                'type'              => 'number',
                'instructions'      => 'Set the order in which this statute appears on the jurisdiction page. Lower numbers appear first.',
                'min'               => 1,
                'step'              => 1,
                'conditional_logic' => [ [ [
                    'field'    => 'field_jx_statute_has_attach_flag',
                    'operator' => '==',
                    'value'    => '1',
                ] ] ],
            ],

            // ────────────────────────────────────────────────────────────────
            // Tab: Statute of Limitations
            // ────────────────────────────────────────────────────────────────

            [
                'key'   => 'field_jx_statute_sol_deadlines_tab',
                'label' => 'Statute of Limitations & Deadlines',
                'type'  => 'tab',
            ],

            [
                'key'          => 'field_jx_statute_sol_value',
                'label'        => 'Filing Window Value',
                'name'         => 'ws_jx_statute_sol_value',
                'type'         => 'number',
                'instructions' => 'The numeric count for the deadline.',
                'min'          => 1,
                'step'         => 1,
                'wrapper'      => [ 'width' => '30' ],
            ],

            [
                'key'           => 'field_jx_statute_sol_unit',
                'label'         => 'Time Unit',
                'name'          => 'ws_jx_statute_sol_unit',
                'type'          => 'select',
                'choices'       => [
                    'days'   => 'Days',
                    'months' => 'Months',
                    'years'  => 'Years',
                ],
                'default_value' => 'days',
                'allow_null'    => 0,
                'ui'            => 1,
                'return_format' => 'value',
                'wrapper'       => [ 'width' => '30' ],
            ],

            [
                'key'           => 'field_jx_statute_sol_trigger',
                'label'         => 'Deadline Trigger',
                'name'          => 'ws_jx_statute_sol_trigger',
                'type'          => 'select',
                'instructions'  => 'When does the clock start ticking?',
                'choices'       => [
                    'adverse_action' => 'Date of Adverse Action',
                    'discovery'      => 'Date of Discovery',
                    'violation'      => 'Date of Violation',
                ],
                'allow_null'    => 1,
                'ui'            => 1,
                'return_format' => 'value',
                'wrapper'       => [ 'width' => '40' ],
            ],

            [
                'key'           => 'field_jx_statute_has_limit_ambiguous',
                'label'         => 'SOL Has Supplementary Detail',
                'name'          => 'ws_jx_statute_has_limit_ambiguous',
                'type'          => 'true_false',
                'instructions'  => 'Enable to add a detail note — e.g., the deadline is derived from a general civil procedure statute rather than stated in this law.',
                'ui'            => 1,
                'ui_on_text'    => 'Yes',
                'ui_off_text'   => 'No',
                'default_value' => 0,
            ],

            [
                'key'               => 'field_jx_statute_limit_details',
                'label'             => 'SOL Details',
                'name'              => 'ws_jx_statute_limit_details',
                'type'              => 'textarea',
                'rows'              => 3,
                'instructions'      => 'Describe anything a reviewer should know about this deadline — derivation source, dual-period situations, or other nuance.',
                'conditional_logic' => [ [ [
                    'field'    => 'field_jx_statute_has_limit_ambiguous',
                    'operator' => '==',
                    'value'    => '1',
                ] ] ],
            ],

            [
                'key'           => 'field_jx_statute_has_tolling_details',
                'label'         => 'Tolling Provisions Exist',
                'name'          => 'ws_jx_statute_has_tolling_details',
                'type'          => 'true_false',
                'instructions'  => 'Enable if this statute has identified tolling or extension conditions.',
                'ui'            => 1,
                'ui_on_text'    => 'Yes',
                'ui_off_text'   => 'No',
                'default_value' => 0,
            ],

            [
                'key'               => 'field_jx_statute_tolling_details',
                'label'             => 'Tolling & Extension Details',
                'name'              => 'ws_jx_statute_tolling_details',
                'type'              => 'textarea',
                'rows'              => 3,
                'instructions'      => 'Describe specific conditions that pause or extend the statutory clock.',
                'conditional_logic' => [ [ [
                    'field'    => 'field_jx_statute_has_tolling_details',
                    'operator' => '==',
                    'value'    => '1',
                ] ] ],
            ],

            [
                'key'           => 'field_jx_statute_has_exhaustion_required',
                'label'         => 'Exhaustion Required?',
                'name'          => 'ws_jx_statute_has_exhaustion_required',
                'type'          => 'true_false',
                'instructions'  => 'Must the whistleblower file with an agency before going to court?',
                'ui'            => 1,
                'ui_on_text'    => 'Yes',
                'ui_off_text'   => 'No',
                'default_value' => 0,
                'wrapper'       => [ 'width' => '30' ],
            ],

            [
                'key'               => 'field_jx_statute_exhaustion_details',
                'label'             => 'Exhaustion Procedure & Deadline',
                'name'              => 'ws_jx_statute_exhaustion_details',
                'type'              => 'textarea',
                'rows'              => 3,
                'instructions'      => 'Describe the agency filing deadline (e.g., 90 days to OSHA).',
                'required'          => 1,
                'conditional_logic' => [ [ [
                    'field'    => 'field_jx_statute_has_exhaustion_required',
                    'operator' => '==',
                    'value'    => '1',
                ] ] ],
                'wrapper'           => [ 'width' => '70' ],
            ],

            // ────────────────────────────────────────────────────────────────
            // Tab: Enforcement
            // ────────────────────────────────────────────────────────────────

            [
                'key'   => 'field_jx_statute_enforcement_tab',
                'label' => 'Enforcement',
                'type'  => 'tab',
            ],

            [
                'key'           => 'field_jx_statute_process_types',
                'label'         => 'Process Types',
                'name'          => 'ws_jx_statute_process_types',
                'type'          => 'taxonomy',
                'taxonomy'      => 'ws_process_type',
                'field_type'    => 'multi_select',
                'instructions'  => 'Which whistleblower process areas does this statute address?',
                'add_term'      => 0,
                'save_terms'    => 1,
                'load_terms'    => 1,
                'return_format' => 'id',
            ],

            [
                'key'           => 'field_jx_statute_adverse_action_types',
                'label'         => 'Adverse Action Types',
                'name'          => 'ws_jx_statute_adverse_action_types',
                'type'          => 'taxonomy',
                'taxonomy'      => 'ws_adverse_action_type',
                'field_type'    => 'multi_select',
                'instructions'  => 'Select the adverse actions covered by this statute.',
                'add_term'      => 0,
                'save_terms'    => 1,
                'load_terms'    => 1,
                'return_format' => 'id',
            ],

            [
                'key'          => 'field_jx_statute_adverse_action_type_details',
                'label'        => 'Adverse Action Details',
                'name'         => 'ws_jx_statute_adverse_action_type_details',
                'type'         => 'textarea',
                'rows'         => 3,
                'instructions' => 'Describe any statutory language, broad catch-all provisions, or nuance that the taxonomy terms do not fully capture.',
                // conditional_logic set dynamically — see ws_jx_statute_details_conditional()
            ],

            [
                'key'           => 'field_jx_statute_fee_shiftings',
                'label'         => 'Fee Shifting',
                'name'          => 'ws_jx_statute_fee_shiftings',
                'type'          => 'taxonomy',
                'taxonomy'      => 'ws_fee_shifting',
                'field_type'    => 'multi_select',
                'instructions'  => 'Select the fee shifting rule that applies to this statute.',
                'add_term'      => 0,
                'save_terms'    => 1,
                'load_terms'    => 1,
                'return_format' => 'id',
            ],

            [
                'key'           => 'field_jx_statute_remedies',
                'label'         => 'Available remedy',
                'name'          => 'ws_jx_statute_remedies',
                'type'          => 'taxonomy',
                'taxonomy'      => 'ws_remedy',
                'field_type'    => 'multi_select',
                'instructions'  => 'What can a whistleblower recover under this specific law?',
                'add_term'      => 0,
                'save_terms'    => 1,
                'load_terms'    => 1,
                'return_format' => 'id',
            ],

            [
                'key'          => 'field_jx_statute_remedy_details',
                'label'        => 'remedy Details',
                'name'         => 'ws_jx_statute_remedy_details',
                'type'         => 'textarea',
                'rows'         => 3,
                'instructions' => 'Describe caps, eligibility conditions, aggregation rules, or other nuance affecting available remedy.',
                // conditional_logic set dynamically — see ws_jx_statute_details_conditional()
            ],

            [
                'key'           => 'field_jx_statute_local_agencies',
                'label'         => 'Local Agencies',
                'name'          => 'ws_jx_statute_local_agencies',
                'type'          => 'post_object',
                'post_type'     => [ 'ws-agency' ],
                'instructions'  => 'Select non-federal agencies that enforce or provide intake for this statute (state, territory, district, tribal, or local bodies).',
                'multiple'      => 1,
                'allow_null'    => 1,
                'ui'            => 1,
                'return_format' => 'id',
            ],

            [
                'key'           => 'field_jx_statute_federal_agencies',
                'label'         => 'Federal Agencies',
                'name'          => 'ws_jx_statute_federal_agencies',
                'type'          => 'post_object',
                'post_type'     => [ 'ws-agency' ],
                'instructions'  => 'Select federal agencies that enforce or provide intake for this statute.',
                'multiple'      => 1,
                'allow_null'    => 1,
                'ui'            => 1,
                'return_format' => 'id',
            ],

            [
                'key'          => 'field_jx_statute_enforcement_channel',
                'label'        => 'Enforcement Channel Notes',
                'name'         => 'ws_jx_statute_enforcement_channel',
                'type'         => 'textarea',
                'rows'         => 3,
                'instructions' => 'Capture agency/channel nuance not represented by linked agency records (for example, split intake paths, courts, or board-specific routing).',
                'required'     => 0,
            ],

            // ────────────────────────────────────────────────────────────────
            // Tab: Burden of Proof
            // ────────────────────────────────────────────────────────────────

            [
                'key'   => 'field_jx_statute_bop_tab',
                'label' => 'Burden of Proof',
                'type'  => 'tab',
            ],

            [
                'key'           => 'field_jx_statute_employee_standards',
                'label'         => 'Employee Standard',
                'name'          => 'ws_jx_statute_employee_standards',
                'type'          => 'taxonomy',
                'taxonomy'      => 'ws_employee_standard',
                'field_type'    => 'multi_select',
                'instructions'  => 'What standard must the whistleblower meet to succeed? Tag all that explicitly apply. Omit if no standard is named in the statute — do not infer.',
                'add_term'      => 0,
                'save_terms'    => 1,
                'load_terms'    => 1,
                'return_format' => 'id',
            ],

            [
                'key'          => 'field_jx_statute_employee_standard_details',
                'label'        => 'Employee Standard Details',
                'name'         => 'ws_jx_statute_employee_standard_details',
                'type'         => 'textarea',
                'rows'         => 3,
                'instructions' => 'Describe the split standard, burden shift, or other nuance — e.g., different standards applying to different claim types under this statute.',
                // conditional_logic set dynamically — see ws_jx_statute_details_conditional()
            ],

            [
                'key'           => 'field_jx_statute_employer_defenses',
                'label'         => 'Employer Defense',
                'name'          => 'ws_jx_statute_employer_defenses',
                'type'          => 'taxonomy',
                'taxonomy'      => 'ws_employer_defense',
                'field_type'    => 'multi_select',
                'instructions'  => 'Select the defense standard(s) available to the employer under this statute.',
                'add_term'      => 0,
                'save_terms'    => 1,
                'load_terms'    => 1,
                'return_format' => 'id',
            ],

            [
                'key'          => 'field_jx_statute_employer_defense_details',
                'label'        => 'Employer Defense Details',
                'name'         => 'ws_jx_statute_employer_defense_details',
                'type'         => 'textarea',
                'rows'         => 3,
                'instructions' => 'Describe the specific defense standard — e.g., the evidentiary burden required, statutory language, or any procedural conditions attached to the defense.',
                // conditional_logic set dynamically — see ws_jx_statute_details_conditional()
            ],

            [
                'key'           => 'field_jx_statute_has_rebuttable_presumption',
                'label'         => 'Rebuttable Presumption Exists',
                'name'          => 'ws_jx_statute_has_rebuttable_presumption',
                'type'          => 'true_false',
                'instructions'  => 'Enable if this statute creates a rebuttable presumption in favour of the whistleblower.',
                'ui'            => 1,
                'ui_on_text'    => 'Yes',
                'ui_off_text'   => 'No',
                'default_value' => 0,
            ],

            [
                'key'               => 'field_jx_statute_rebuttable_presumption',
                'label'             => 'Rebuttable Presumption Details',
                'name'              => 'ws_jx_statute_rebuttable_presumption',
                'type'              => 'textarea',
                'rows'              => 3,
                'instructions'      => 'Describe the presumption and what the employer must do to rebut it.',
                'conditional_logic' => [ [ [
                    'field'    => 'field_jx_statute_has_rebuttable_presumption',
                    'operator' => '==',
                    'value'    => '1',
                ] ] ],
            ],

            [
                'key'           => 'field_jx_statute_has_bop_details',
                'label'         => 'BOP Has Supplementary Detail',
                'name'          => 'ws_jx_statute_has_bop_details',
                'type'          => 'true_false',
                'instructions'  => 'Enable to add a note about a non-standard or otherwise notable burden of proof situation for this statute.',
                'ui'            => 1,
                'ui_on_text'    => 'Yes',
                'ui_off_text'   => 'No',
                'default_value' => 0,
            ],

            [
                'key'               => 'field_jx_statute_bop_details',
                'label'             => 'BOP Details',
                'name'              => 'ws_jx_statute_bop_details',
                'type'              => 'textarea',
                'rows'              => 3,
                'instructions'      => 'Describe the notable burden of proof situation — e.g., a burden shift, a split standard, or statutory language that modifies the general standard.',
                'conditional_logic' => [ [ [
                    'field'    => 'field_jx_statute_has_bop_details',
                    'operator' => '==',
                    'value'    => '1',
                ] ] ],
            ],


            [
                'key'          => 'field_jx_statute_bop_flag',
                'label'        => 'BOP Flag',
                'name'         => 'ws_jx_statute_bop_flag',
                'type'         => 'text',
                'instructions' => 'Short signal phrase identifying a non-standard burden shift. Use a compact hyphenated phrase, e.g. "contributing-factor-shift", "90-day rebuttable presumption", "AIR21 burden-shifting framework". Not a full sentence.',
                'maxlength'    => 120,
            ],

            // ────────────────────────────────────────────────────────────────
            // Tab: Reward
            // ────────────────────────────────────────────────────────────────

            [
                'key'   => 'field_jx_statute_reward_tab',
                'label' => 'Reward',
                'type'  => 'tab',
            ],

            [
                'key'           => 'field_jx_statute_has_reward_available',
                'label'         => 'Reward Available',
                'name'          => 'ws_jx_statute_has_reward_available',
                'type'          => 'true_false',
                'instructions'  => 'Enable if this statute provides a monetary reward or bounty to the whistleblower (distinct from compensatory remedy).',
                'ui'            => 1,
                'ui_on_text'    => 'Yes',
                'ui_off_text'   => 'No',
                'default_value' => 0,
            ],

            [
                'key'               => 'field_jx_statute_reward_details',
                'label'             => 'Reward Details',
                'name'              => 'ws_jx_statute_reward_details',
                'type'              => 'textarea',
                'rows'              => 3,
                'instructions'      => 'Describe the reward structure — e.g., percentage of collected sanctions, eligibility conditions, administering agency.',
                'conditional_logic' => [ [ [
                    'field'    => 'field_jx_statute_has_reward_available',
                    'operator' => '==',
                    'value'    => '1',
                ] ] ],
            ],

            // ────────────────────────────────────────────────────────────────
            // Tab: Links
            // ────────────────────────────────────────────────────────────────

            [
                'key'   => 'field_jx_statute_links_tab',
                'label' => 'Links',
                'type'  => 'tab',
            ],

            [
                'key'          => 'field_jx_statute_url',
                'label'        => 'Statute URL',
                'name'         => 'ws_jx_statute_url',
                'type'         => 'url',
                'instructions' => 'Link to the official legislature source or best available approved source for this statute.',
            ],

            [
                'key'           => 'field_jx_statute_url_is_pdf',
                'label'         => 'Link is PDF?',
                'name'          => 'ws_jx_statute_url_is_pdf',
                'type'          => 'true_false',
                'instructions'  => 'Enable if the statute URL links directly to a PDF document.',
                'ui'            => 1,
                'ui_on_text'    => 'PDF',
                'ui_off_text'   => 'No',
                'default_value' => 0,
            ],

            // ── Last Verified Date ────────────────────────────────────────
            //
            // Content-owned field — not a stamp. Editable by editors to
            // signal when the statute record was last meaningfully reviewed
            // for accuracy. Rendered inside the Links tab.

            [
                'key'          => 'field_jx_statute_last_reviewed',
                'label'        => 'Last Verified Date',
                'name'         => 'ws_jx_statute_last_reviewed',
                'type'         => 'text',
                'instructions' => 'Update this date each time the statute record is meaningfully revised.',
            ],

            // Authorship & Review tab removed — registered centrally in
            // acf-stamp-fields.php (group_stamp_metadata, menu_order 90).

            // Plain Language tab removed — registered centrally in
            // acf-plain-english-fields.php (group_plain_english_metadata, menu_order 85).

            // ── Tab: Reference Materials ───────────────────────────────────
            //
            // Links this statute to ws-reference records for researchers and
            // legal professionals. Not rendered on jurisdiction pages.
            // Only approved references display publicly via [ws_reference_page].

            [
                'key'   => 'field_jx_statute_ref_materials_tab',
                'label' => 'Reference Materials',
                'type'  => 'tab',
            ],

            [
                'key'           => 'field_jx_statute_ref_materials',
                'label'         => 'Reference Materials',
                'name'          => 'ws_jx_statute_ref_materials',
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

} // end ws_register_acf_jx_statutes


// Field locking, auto-fill today, and stamp fields are handled centrally
// in admin-hooks.php via ws_acf_lock_for_non_admins(), ws_acf_autofill_today(),
// and ws_acf_write_stamp_fields().


// ── Conditional logic: taxonomy sentinel-gated details fields ─────────────────
//
// ACF conditional logic cannot reference taxonomy term IDs at registration time
// because term IDs are assigned at seed runtime, not at code registration time.
//
// The following detail textareas are shown when the companion trigger field
// includes sentinel slug 'has-details':
// - ws_jx_statute_protected_classes
// - ws_jx_statute_disclosure_targets
// - ws_jx_statute_adverse_action_types
// - ws_jx_statute_remedies
// - ws_jx_statute_employee_standards
// - ws_jx_statute_employer_defenses

add_filter( 'acf/load_field', 'ws_jx_statute_details_conditional' );

function ws_jx_statute_details_conditional( $field ) {

    // Map: details field key => [ taxonomy slug, trigger field key ]
    static $map = [
        'field_jx_statute_protected_class_details'    => [ 'ws_protected_class',      'field_jx_statute_protected_classes' ],
        'field_jx_statute_disclosure_target_details'  => [ 'ws_disclosure_target',    'field_jx_statute_disclosure_targets' ],
        'field_jx_statute_adverse_action_type_details' => [ 'ws_adverse_action_type',  'field_jx_statute_adverse_action_types' ],
        'field_jx_statute_remedy_details'             => [ 'ws_remedy',               'field_jx_statute_remedies' ],
        'field_jx_statute_employee_standard_details'  => [ 'ws_employee_standard',    'field_jx_statute_employee_standards' ],
        'field_jx_statute_employer_defense_details'   => [ 'ws_employer_defense',     'field_jx_statute_employer_defenses' ],
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


add_filter( 'acf/prepare_field/name=ws_jx_statute_local_agencies', 'ws_jx_statute_prepare_local_agencies_field' );
add_filter( 'acf/fields/post_object/query/name=ws_jx_statute_local_agencies', 'ws_jx_statute_local_agencies_query', 10, 3 );
add_filter( 'acf/fields/post_object/query/name=ws_jx_statute_federal_agencies', 'ws_jx_statute_federal_agencies_query', 10, 3 );

/**
 * Resolve the first ws_jurisdiction term assigned to the given statute post.
 */
function ws_jx_statute_get_term_for_post( $post_id ) {
    $post_id = (int) $post_id;
    if ( ! $post_id ) {
        return null;
    }

    $terms = wp_get_post_terms( $post_id, WS_JURISDICTION_TAXONOMY );
    if ( is_wp_error( $terms ) || empty( $terms ) ) {
        return null;
    }

    return $terms[0];
}

/**
 * Hide local agencies when the statute jurisdiction is US/federal scope.
 */
function ws_jx_statute_prepare_local_agencies_field( $field ) {
    $post_id = (int) ( $_GET['post'] ?? 0 );
    $term    = ws_jx_statute_get_term_for_post( $post_id );

    if ( $term && strtolower( (string) $term->slug ) === 'us' ) {
        return false;
    }

    return $field;
}

/**
 * Scope state agency chooser to the statute's assigned jurisdiction term.
 */
function ws_jx_statute_local_agencies_query( $args, $field, $post_id ) {
    $term = ws_jx_statute_get_term_for_post( $post_id );

    if ( $term && strtolower( (string) $term->slug ) !== 'us' ) {
        $args['tax_query'] = [ [
            'taxonomy' => WS_JURISDICTION_TAXONOMY,
            'field'    => 'term_id',
            'terms'    => [ (int) $term->term_id ],
        ] ];
    }

    return $args;
}

/**
 * Scope federal agency chooser to the US jurisdiction term.
 */
function ws_jx_statute_federal_agencies_query( $args, $field, $post_id ) {
    $us_term = get_term_by( 'slug', 'us', WS_JURISDICTION_TAXONOMY );

    if ( $us_term && ! is_wp_error( $us_term ) ) {
        $args['tax_query'] = [ [
            'taxonomy' => WS_JURISDICTION_TAXONOMY,
            'field'    => 'term_id',
            'terms'    => [ (int) $us_term->term_id ],
        ] ];
    }

    return $args;
}
