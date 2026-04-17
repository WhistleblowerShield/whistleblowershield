<?php
defined( 'ABSPATH' ) || exit;

/**
 * acf-jx-common-law.php
 *
 * Registers ACF Pro fields for the `jx-common-law` CPT.
 *
 * PURPOSE
 * -------
 * Provides structured metadata for common law whistleblower protections.
 * Mirrors jx-statute field structure with two differences:
 *   1. The Legal Basis tab replaces statute citation/URL fields with
 *      two WYSIWYG fields: Doctrine Basis and Recognition Status.
 *   2. The Links tab is replaced by a Cases tab for leading case
 *      references rather than statute URLs.
 *
 * FIELD SUMMARY
 * -------------
 * Legal Basis tab:
 *   ws_jx_comlaw_doctrine_name                Doctrine name (text, required)
 *   ws_jx_comlaw_doctrine_id                  Unique doctrine identifier (text, required)
 *   ws_jx_comlaw_common_name                  Common/informal name (text, optional)
 *   ws_jx_comlaw_precedent_url                Leading case URL (url, optional)
 *   ws_jx_comlaw_precedent_url_is_pdf         PDF link toggle (true_false)
 *   ws_jx_comlaw_public_policy_sources        Public policy source categories (checkbox)
 *   ws_jx_comlaw_doctrine_basis_wysiwyg       Doctrine basis narrative (wysiwyg)
 *   ws_jx_comlaw_recognition_status_wysiwyg   Recognition status narrative (wysiwyg)
 *   ws_jx_comlaw_disclosure_types             Disclosure Categories taxonomy (multi_select)
 *   ws_jx_comlaw_protected_classes            Protected Class taxonomy (multi_select)
 *   ws_jx_comlaw_protected_class_details      Protected Class detail (textarea, conditional)
 *   ws_jx_comlaw_disclosure_targets           Disclosure Targets taxonomy (multi_select)
 *   ws_jx_comlaw_disclosure_target_details    Disclosure Targets detail (textarea, conditional)
 *   ws_jx_comlaw_adverse_action_scope         Free-text scope of covered adverse actions
 *   ws_jx_comlaw_has_attach_flag              Editorial curation flag (true_false)
 *   ws_jx_comlaw_display_order                Render order among flagged items (number, conditional)
 *
 * Statute of Limitations tab:
 *   ws_jx_comlaw_sol_value                    Filing Window Value (number)
 *   ws_jx_comlaw_sol_unit                     Time Unit (select)
 *   ws_jx_comlaw_sol_trigger                  Deadline Trigger (select)
 *   ws_jx_comlaw_has_limit_ambiguous          SOL supplementary detail toggle (true_false)
 *   ws_jx_comlaw_limit_details                SOL detail (textarea, conditional)
 *   ws_jx_comlaw_has_tolling_details          Tolling provisions toggle (true_false)
 *   ws_jx_comlaw_tolling_details              Tolling & extension details (textarea, conditional)
 *   ws_jx_comlaw_has_exhaustion_required      Exhaustion required toggle (true_false)
 *   ws_jx_comlaw_exhaustion_details           Exhaustion procedure & deadline (textarea, conditional)
 *
 * Enforcement tab:
 *   ws_jx_comlaw_process_types                Process Types taxonomy (multi_select)
 *   ws_jx_comlaw_adverse_action_types         Adverse Action Types taxonomy (multi_select)
 *   ws_jx_comlaw_adverse_action_type_details  Adverse Action detail (textarea, conditional)
 *   ws_jx_comlaw_fee_shiftings                Fee Shifting taxonomy (multi_select)
 *   ws_jx_comlaw_remedies                     Available Remedies taxonomy (multi_select)
 *   ws_jx_comlaw_remedy_details               Remedy detail (textarea, conditional)
 *   ws_jx_comlaw_related_agencies             Primary oversight agencies (post_object)
 *
 * Burden of Proof tab:
 *   ws_jx_comlaw_has_statutory_preclusion     Statutory preclusion toggle (true_false)
 *   ws_jx_comlaw_statutory_preclusion_details Statutory preclusion detail (textarea, conditional)
 *   ws_jx_comlaw_employee_standards           Employee Standard taxonomy (multi_select)
 *   ws_jx_comlaw_employee_standard_details    Employee Standard detail (textarea, conditional)
 *   ws_jx_comlaw_employer_defenses            Employer Defense taxonomy (multi_select)
 *   ws_jx_comlaw_employer_defense_details     Employer Defense detail (textarea, conditional)
 *   ws_jx_comlaw_has_rebuttable_presumption   Rebuttable presumption toggle (true_false)
 *   ws_jx_comlaw_rebuttable_presumption_details Rebuttable presumption detail (textarea, conditional)
 *   ws_jx_comlaw_has_bop_details              BOP supplementary detail toggle (true_false)
 *   ws_jx_comlaw_bop_details                  BOP detail (textarea, conditional)
 *   ws_jx_comlaw_bop_flag                     BOP signal phrase (text)
 *
 * Reward tab:
 *   ws_jx_comlaw_has_reward_available         Reward available toggle (true_false)
 *   ws_jx_comlaw_reward_details               Reward details (textarea, conditional)
 *
 * Relationships tab:
 *   ws_jx_comlaw_citation_ids                 Related citations (post_object, multiple)
 *   ws_jx_comlaw_interpretation_ids           Related interpretations (post_object, multiple)
 *
 * @package    WhistleblowerShield
 * @since      3.13.0
 * @version    3.13.0
 * @author     Whistleblower Shield
 * @link       https://whistleblowershield.org
 * @copyright  Copyright (c) Whistleblower Shield
 *
 * VERSION
 * -------
 * 3.13.0  Initial release. Mirrors acf-jx-statutes.php with doctrine-
 *         anchored Legal Basis tab replacing statute citation/URL fields.
 *         ws_jx_comlaw_doctrine_id, ws_jx_comlaw_precedent_url, ws_jx_comlaw_public_policy_sources,
 *         and ws_jx_comlaw_statutory_preclusion added based on architectural analysis.
 */

add_action( 'acf/init', 'ws_register_acf_jx_common_law' );

function ws_register_acf_jx_common_law() {

    if ( ! function_exists( 'acf_add_local_field_group' ) ) {
        return;
    }

    acf_add_local_field_group( [
        'key'                   => 'group_jx_comlaw_metadata',
        'title'                 => 'Common Law Protection Details',
        'menu_order'            => 0,
        'position'              => 'normal',
        'style'                 => 'default',
        'label_placement'       => 'top',
        'instruction_placement' => 'label',
        'active'                => true,

        'location' => [ [ [
            'param'    => 'post_type',
            'operator' => '==',
            'value'    => 'jx-common-law',
        ] ] ],

        'fields' => [

            // ────────────────────────────────────────────────────────────────
            // Tab: Legal Basis
            // ────────────────────────────────────────────────────────────────

            [
                'key'   => 'field_jx_comlaw_legal_basis_tab',
                'label' => 'Legal Basis',
                'type'  => 'tab',
            ],

            [
                'key'          => 'field_jx_comlaw_doctrine_name',
                'label'        => 'Doctrine Name',
                'name'         => 'ws_jx_comlaw_doctrine_name',
                'type'         => 'text',
                'instructions' => 'The formal or widely-recognized name of this common law doctrine — e.g., "Public Policy Exception to At-Will Employment" or "Implied Covenant of Good Faith and Fair Dealing".',
                'required'     => 1,
            ],

            [
                'key'          => 'field_jx_comlaw_doctrine_id',
                'label'        => 'Doctrine ID',
                'name'         => 'ws_jx_comlaw_doctrine_id',
                'type'         => 'text',
                'instructions' => 'Unique identifier for this doctrine record. Format: [JX]-CL-[SHORT-SLUG] in kebab-case, max 4–5 words after CL. Examples: WY-CL-PUBLIC-POLICY, WY-CL-IMPLIED-COVENANT, CA-CL-TAMENY. Used in prompt exclusion lists to prevent duplicate records across pipeline runs.',
                'required'     => 1,
            ],

            [
                'key'          => 'field_jx_comlaw_common_name',
                'label'        => 'Common Name',
                'name'         => 'ws_jx_comlaw_common_name',
                'type'         => 'text',
                'instructions' => 'Informal or shorthand name for this doctrine, if one is widely used. Leave blank if none applies.',
                'required'     => 0,
            ],

            [
                'key'          => 'field_jx_comlaw_precedent_url',
                'label'        => 'Leading Case URL',
                'name'         => 'ws_jx_comlaw_precedent_url',
                'type'         => 'url',
                'instructions' => 'Link to the binding court opinion that established this doctrine. Use an approved source only: supremecourt.gov, [state].uscourts.gov, courts.[state].gov, courtlistener.com, casetext.com, or law.justia.com. Leave blank if no verified URL is available.',
                'required'     => 0,
            ],

            [
                'key'           => 'field_jx_comlaw_precedent_url_is_pdf',
                'label'         => 'Link is PDF?',
                'name'          => 'ws_jx_comlaw_precedent_url_is_pdf',
                'type'          => 'true_false',
                'instructions'  => 'Enable if the leading case URL links directly to a PDF document.',
                'ui'            => 1,
                'ui_on_text'    => 'PDF',
                'ui_off_text'   => 'No',
                'default_value' => 0,
            ],

            [
                'key'          => 'field_jx_comlaw_public_policy_sources',
                'label'        => 'Public Policy Sources',
                'name'         => 'ws_jx_comlaw_public_policy_sources',
                'type'         => 'checkbox',
                'instructions' => 'Which sources of law does this jurisdiction accept as establishing a "public policy" for purposes of this doctrine? Check all that apply.',
                'choices'      => [
                    'constitution'           => 'State Constitution',
                    'statute'                => 'Statute',
                    'administrative-rule'    => 'Administrative Rule / Regulation',
                    'case-law'               => 'Case Law / Judicial Decision',
                    'federal-law'            => 'Federal Law',
                    'other'                  => 'Other',
                ],
                'layout'       => 'vertical',
                'required'     => 0,
            ],

            [
                'key'               => 'field_jx_comlaw_other_sources',
                'label'             => 'Other Sources Detail',
                'name'              => 'ws_jx_comlaw_other_sources',
                'type'              => 'text',
                'instructions'      => 'Describe the other source of law accepted as establishing public policy in this jurisdiction.',
                'conditional_logic' => [ [ [
                    'field'    => 'field_jx_comlaw_public_policy_sources',
                    'operator' => '==',
                    'value'    => 'other',
                ] ] ],
            ],

            [
                'key'          => 'field_jx_comlaw_doctrine_basis_wysiwyg',
                'label'        => 'Doctrine Basis',
                'name'         => 'ws_jx_comlaw_doctrine_basis_wysiwyg',
                'type'         => 'wysiwyg',
                'tabs'         => 'all',
                'toolbar'      => 'full',
                'media_upload' => 0,
                'instructions' => 'Describe the legal principle this protection rests on — e.g., the public policy tort theory, the constitutional provision, or the common law rule. Cite the leading case(s) that established the doctrine. This field is the primary explanatory content for this record.',
                'required'     => 1,
            ],

            [
                'key'          => 'field_jx_comlaw_recognition_status_wysiwyg',
                'label'        => 'Recognition Status',
                'name'         => 'ws_jx_comlaw_recognition_status_wysiwyg',
                'type'         => 'wysiwyg',
                'tabs'         => 'all',
                'toolbar'      => 'full',
                'media_upload' => 0,
                'instructions' => 'Describe the current state of this doctrine in this jurisdiction — e.g., well-established and frequently applied, contested or narrowly construed, limited to specific fact patterns, or recognized but rarely litigated. Note any recent developments, circuit splits, or limitations that affect practical applicability.',
                'required'     => 1,
            ],

            [
                'key'           => 'field_jx_comlaw_disclosure_types',
                'label'         => 'Disclosure Categories',
                'name'          => 'ws_jx_comlaw_disclosure_types',
                'type'          => 'taxonomy',
                'taxonomy'      => 'ws_disclosure_type',
                'field_type'    => 'multi_select',
                'instructions'  => 'Classify the types of misconduct this doctrine protects disclosures of.',
                'add_term'      => 0,
                'save_terms'    => 1,
                'load_terms'    => 1,
                'return_format' => 'id',
            ],

            [
                'key'           => 'field_jx_comlaw_protected_classes',
                'label'         => 'Protected Class',
                'name'          => 'ws_jx_comlaw_protected_classes',
                'type'          => 'taxonomy',
                'taxonomy'      => 'ws_protected_class',
                'field_type'    => 'multi_select',
                'instructions'  => 'Select the worker classifications covered by this doctrine.',
                'add_term'      => 0,
                'save_terms'    => 1,
                'load_terms'    => 1,
                'return_format' => 'id',
            ],

            [
                'key'          => 'field_jx_comlaw_protected_class_details',
                'label'        => 'Protected Class Details',
                'name'         => 'ws_jx_comlaw_protected_class_details',
                'type'         => 'textarea',
                'rows'         => 3,
                'instructions' => 'Describe nuance in coverage — e.g., exclusions, eligibility thresholds, or judicial language distinguishing which workers qualify.',
                // conditional_logic set dynamically — see ws_jx_cl_details_conditional()
            ],

            [
                'key'           => 'field_jx_comlaw_disclosure_targets',
                'label'         => 'Disclosure Targets',
                'name'          => 'ws_jx_comlaw_disclosure_targets',
                'type'          => 'taxonomy',
                'taxonomy'      => 'ws_disclosure_target',
                'field_type'    => 'multi_select',
                'instructions'  => 'Who must the disclosure be made to for this doctrine to apply?',
                'add_term'      => 0,
                'save_terms'    => 1,
                'load_terms'    => 1,
                'return_format' => 'id',
            ],

            [
                'key'          => 'field_jx_comlaw_disclosure_target_details',
                'label'        => 'Disclosure Targets Details',
                'name'         => 'ws_jx_comlaw_disclosure_target_details',
                'type'         => 'textarea',
                'rows'         => 3,
                'instructions' => 'Describe any conditions or judicial requirements affecting which reporting channels qualify for protection under this doctrine.',
                // conditional_logic set dynamically — see ws_jx_cl_details_conditional()
            ],

            [
                'key'          => 'field_jx_comlaw_adverse_action_scope',
                'label'        => 'Adverse Action Scope',
                'name'         => 'ws_jx_comlaw_adverse_action_scope',
                'type'         => 'textarea',
                'rows'         => 3,
                'instructions' => 'Describe the workplace actions this doctrine considers adverse, where the taxonomy terms do not fully capture the judicial scope.',
                'required'     => 0,
            ],

            [
                'key'           => 'field_jx_comlaw_has_attach_flag',
                'label'         => 'Attach to Jurisdiction Page',
                'name'          => 'ws_jx_comlaw_has_attach_flag',
                'type'          => 'true_false',
                'instructions'  => 'Enable to include this record in the rendered common law section on the jurisdiction page.',
                'ui'            => 1,
                'ui_on_text'    => 'Attached',
                'ui_off_text'   => 'Unattached',
                'default_value' => 0,
            ],

            [
                'key'               => 'field_jx_comlaw_display_order',
                'label'             => 'Display Order',
                'name'              => 'ws_jx_comlaw_display_order',
                'type'              => 'number',
                'instructions'      => 'Order among attached common law records on the jurisdiction page. Lower numbers appear first.',
                'min'               => 1,
                'step'              => 1,
                'conditional_logic' => [ [ [
                    'field'    => 'field_jx_comlaw_has_attach_flag',
                    'operator' => '==',
                    'value'    => '1',
                ] ] ],
            ],

            // ────────────────────────────────────────────────────────────────
            // Tab: Statute of Limitations
            // ────────────────────────────────────────────────────────────────

            [
                'key'   => 'field_jx_comlaw_sol_deadlines_tab',
                'label' => 'Statute of Limitations & Deadlines',
                'type'  => 'tab',
            ],

            [
                'key'          => 'field_jx_comlaw_sol_value',
                'label'        => 'Filing Window Value',
                'name'         => 'ws_jx_comlaw_sol_value',
                'type'         => 'number',
                'instructions' => 'The numeric count for the deadline. Common law SOLs are almost always borrowed from an analogous statute — document the source in SOL Details.',
                'min'          => 1,
                'step'         => 1,
                'wrapper'      => [ 'width' => '30' ],
            ],

            [
                'key'           => 'field_jx_comlaw_sol_unit',
                'label'         => 'Time Unit',
                'name'          => 'ws_jx_comlaw_sol_unit',
                'type'          => 'select',
                'choices'       => [
                    'days'   => 'Days',
                    'months' => 'Months',
                    'years'  => 'Years',
                ],
                'default_value' => 'years',
                'allow_null'    => 0,
                'ui'            => 1,
                'return_format' => 'value',
                'wrapper'       => [ 'width' => '30' ],
            ],

            [
                'key'           => 'field_jx_comlaw_sol_trigger',
                'label'         => 'Deadline Trigger',
                'name'          => 'ws_jx_comlaw_sol_trigger',
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
                'key'           => 'field_jx_comlaw_has_limit_ambiguous',
                'label'         => 'SOL Has Supplementary Detail',
                'name'          => 'ws_jx_comlaw_has_limit_ambiguous',
                'type'          => 'true_false',
                'instructions'  => 'Enable to document the analogous statute the limitations period is borrowed from. Almost always required for common law claims.',
                'ui'            => 1,
                'ui_on_text'    => 'Yes',
                'ui_off_text'   => 'No',
                'default_value' => 0,
            ],

            [
                'key'               => 'field_jx_comlaw_limit_details',
                'label'             => 'SOL Details',
                'name'              => 'ws_jx_comlaw_limit_details',
                'type'              => 'textarea',
                'rows'              => 3,
                'instructions'      => 'Identify the analogous statute the limitations period is borrowed from and any judicial authority for that borrowing.',
                'conditional_logic' => [ [ [
                    'field'    => 'field_jx_comlaw_has_limit_ambiguous',
                    'operator' => '==',
                    'value'    => '1',
                ] ] ],
            ],

            [
                'key'           => 'field_jx_comlaw_has_tolling_details',
                'label'         => 'Tolling Provisions Exist',
                'name'          => 'ws_jx_comlaw_has_tolling_details',
                'type'          => 'true_false',
                'instructions'  => 'Enable if identified tolling or extension conditions apply to this doctrine.',
                'ui'            => 1,
                'ui_on_text'    => 'Yes',
                'ui_off_text'   => 'No',
                'default_value' => 0,
            ],

            [
                'key'               => 'field_jx_comlaw_tolling_details',
                'label'             => 'Tolling & Extension Details',
                'name'              => 'ws_jx_comlaw_tolling_details',
                'type'              => 'textarea',
                'rows'              => 3,
                'instructions'      => 'Describe specific conditions that pause or extend the limitations period.',
                'conditional_logic' => [ [ [
                    'field'    => 'field_jx_comlaw_has_tolling_details',
                    'operator' => '==',
                    'value'    => '1',
                ] ] ],
            ],

            [
                'key'           => 'field_jx_comlaw_has_exhaustion_required',
                'label'         => 'Exhaustion Required?',
                'name'          => 'ws_jx_comlaw_has_exhaustion_required',
                'type'          => 'true_false',
                'instructions'  => 'Must the claimant file with an agency before going to court under this doctrine?',
                'ui'            => 1,
                'ui_on_text'    => 'Yes',
                'ui_off_text'   => 'No',
                'default_value' => 0,
                'wrapper'       => [ 'width' => '30' ],
            ],

            [
                'key'               => 'field_jx_comlaw_exhaustion_details',
                'label'             => 'Exhaustion Procedure & Deadline',
                'name'              => 'ws_jx_comlaw_exhaustion_details',
                'type'              => 'textarea',
                'rows'              => 3,
                'instructions'      => 'Describe the required administrative filing step and its deadline.',
                'required'          => 1,
                'conditional_logic' => [ [ [
                    'field'    => 'field_jx_comlaw_has_exhaustion_required',
                    'operator' => '==',
                    'value'    => '1',
                ] ] ],
                'wrapper'           => [ 'width' => '70' ],
            ],

            // ────────────────────────────────────────────────────────────────
            // Tab: Enforcement
            // ────────────────────────────────────────────────────────────────

            [
                'key'   => 'field_jx_comlaw_enforcement_tab',
                'label' => 'Enforcement',
                'type'  => 'tab',
            ],

            [
                'key'           => 'field_jx_comlaw_process_types',
                'label'         => 'Process Types',
                'name'          => 'ws_jx_comlaw_process_types',
                'type'          => 'taxonomy',
                'taxonomy'      => 'ws_process_type',
                'field_type'    => 'multi_select',
                'instructions'  => 'Which procedural routes are available under this doctrine?',
                'add_term'      => 0,
                'save_terms'    => 1,
                'load_terms'    => 1,
                'return_format' => 'id',
            ],

            [
                'key'           => 'field_jx_comlaw_adverse_action_types',
                'label'         => 'Adverse Action Types',
                'name'          => 'ws_jx_comlaw_adverse_action_types',
                'type'          => 'taxonomy',
                'taxonomy'      => 'ws_adverse_action_type',
                'field_type'    => 'multi_select',
                'instructions'  => 'Select the adverse actions covered by this doctrine.',
                'add_term'      => 0,
                'save_terms'    => 1,
                'load_terms'    => 1,
                'return_format' => 'id',
            ],

            [
                'key'          => 'field_jx_comlaw_adverse_action_type_details',
                'label'        => 'Adverse Action Details',
                'name'         => 'ws_jx_comlaw_adverse_action_type_details',
                'type'         => 'textarea',
                'rows'         => 3,
                'instructions' => 'Describe any judicial language, broad catch-all provisions, or nuance that the taxonomy terms do not fully capture.',
                // conditional_logic set dynamically — see ws_jx_cl_details_conditional()
            ],

            [
                'key'           => 'field_jx_comlaw_fee_shiftings',
                'label'         => 'Fee Shifting',
                'name'          => 'ws_jx_comlaw_fee_shiftings',
                'type'          => 'taxonomy',
                'taxonomy'      => 'ws_fee_shifting',
                'field_type'    => 'multi_select',
                'instructions'  => 'Select the fee shifting rule that applies under this doctrine.',
                'add_term'      => 0,
                'save_terms'    => 1,
                'load_terms'    => 1,
                'return_format' => 'id',
            ],

            [
                'key'           => 'field_jx_comlaw_remedies',
                'label'         => 'Available Remedies',
                'name'          => 'ws_jx_comlaw_remedies',
                'type'          => 'taxonomy',
                'taxonomy'      => 'ws_remedy',
                'field_type'    => 'multi_select',
                'instructions'  => 'What can a claimant recover under this doctrine?',
                'add_term'      => 0,
                'save_terms'    => 1,
                'load_terms'    => 1,
                'return_format' => 'id',
            ],

            [
                'key'          => 'field_jx_comlaw_remedy_details',
                'label'        => 'Remedies Details',
                'name'         => 'ws_jx_comlaw_remedy_details',
                'type'         => 'textarea',
                'rows'         => 3,
                'instructions' => 'Describe caps, eligibility conditions, or other nuance affecting available remedies under this doctrine.',
                // conditional_logic set dynamically — see ws_jx_cl_details_conditional()
            ],

            [
                'key'           => 'field_jx_comlaw_related_agencies',
                'label'         => 'Primary Oversight Agencies',
                'name'          => 'ws_jx_comlaw_related_agencies',
                'type'          => 'post_object',
                'post_type'     => [ 'ws-agency' ],
                'instructions'  => 'Select agencies relevant to enforcement or intake under this doctrine, if any.',
                'multiple'      => 1,
                'allow_null'    => 1,
                'ui'            => 1,
                'return_format' => 'id',
            ],

            // ────────────────────────────────────────────────────────────────
            // Tab: Burden of Proof
            // ────────────────────────────────────────────────────────────────

            [
                'key'   => 'field_jx_comlaw_bop_tab',
                'label' => 'Burden of Proof',
                'type'  => 'tab',
            ],

            [
                'key'           => 'field_jx_comlaw_has_statutory_preclusion',
                'label'         => 'Statutory Preclusion',
                'name'          => 'ws_jx_comlaw_has_statutory_preclusion',
                'type'          => 'true_false',
                'instructions'  => 'Enable if this jurisdiction bars the common law claim when a statutory remedy for the same conduct exists. This is a critical user-facing signal — when true, a worker with a statutory remedy cannot rely on this doctrine.',
                'ui'            => 1,
                'ui_on_text'    => 'Precluded by statute',
                'ui_off_text'   => 'No preclusion',
                'default_value' => 0,
            ],

            [
                'key'               => 'field_jx_comlaw_statutory_preclusion_details',
                'label'             => 'Statutory Preclusion Details',
                'name'              => 'ws_jx_comlaw_statutory_preclusion_details',
                'type'              => 'textarea',
                'rows'              => 3,
                'instructions'      => 'Describe which statutes trigger preclusion, the judicial authority for the rule, and any exceptions or edge cases — e.g., whether preclusion applies only when the statutory remedy is adequate.',
                'conditional_logic' => [ [ [
                    'field'    => 'field_jx_comlaw_has_statutory_preclusion',
                    'operator' => '==',
                    'value'    => '1',
                ] ] ],
            ],

            [
                'key'           => 'field_jx_comlaw_employee_standards',
                'label'         => 'Employee Standard',
                'name'          => 'ws_jx_comlaw_employee_standards',
                'type'          => 'taxonomy',
                'taxonomy'      => 'ws_employee_standard',
                'field_type'    => 'multi_select',
                'instructions'  => 'What standard must the claimant meet? Tag all that explicitly apply. Omit if no standard is named in the leading case — do not infer.',
                'add_term'      => 0,
                'save_terms'    => 1,
                'load_terms'    => 1,
                'return_format' => 'id',
            ],

            [
                'key'          => 'field_jx_comlaw_employee_standard_details',
                'label'        => 'Employee Standard Details',
                'name'         => 'ws_jx_comlaw_employee_standard_details',
                'type'         => 'textarea',
                'rows'         => 3,
                'instructions' => 'Describe any split standard, burden shift, or other nuance in the employee burden under this doctrine.',
                // conditional_logic set dynamically — see ws_jx_cl_details_conditional()
            ],

            [
                'key'           => 'field_jx_comlaw_employer_defenses',
                'label'         => 'Employer Defense',
                'name'          => 'ws_jx_comlaw_employer_defenses',
                'type'          => 'taxonomy',
                'taxonomy'      => 'ws_employer_defense',
                'field_type'    => 'multi_select',
                'instructions'  => 'Select the defense standard(s) available to the employer under this doctrine.',
                'add_term'      => 0,
                'save_terms'    => 1,
                'load_terms'    => 1,
                'return_format' => 'id',
            ],

            [
                'key'          => 'field_jx_comlaw_employer_defense_details',
                'label'        => 'Employer Defense Details',
                'name'         => 'ws_jx_comlaw_employer_defense_details',
                'type'         => 'textarea',
                'rows'         => 3,
                'instructions' => 'Describe the specific defense standard — evidentiary burden, judicial language, or procedural conditions.',
                // conditional_logic set dynamically — see ws_jx_cl_details_conditional()
            ],

            [
                'key'           => 'field_jx_comlaw_has_rebuttable_presumption',
                'label'         => 'Rebuttable Presumption Exists',
                'name'          => 'ws_jx_comlaw_has_rebuttable_presumption',
                'type'          => 'true_false',
                'instructions'  => 'Enable if this doctrine creates a rebuttable presumption in favour of the claimant.',
                'ui'            => 1,
                'ui_on_text'    => 'Yes',
                'ui_off_text'   => 'No',
                'default_value' => 0,
            ],

            [
                'key'               => 'field_jx_comlaw_rebuttable_presumption_details',
                'label'             => 'Rebuttable Presumption Details',
                'name'              => 'ws_jx_comlaw_rebuttable_presumption_details',
                'type'              => 'textarea',
                'rows'              => 3,
                'instructions'      => 'Describe the presumption and what the employer must do to rebut it.',
                'conditional_logic' => [ [ [
                    'field'    => 'field_jx_comlaw_has_rebuttable_presumption',
                    'operator' => '==',
                    'value'    => '1',
                ] ] ],
            ],

            [
                'key'           => 'field_jx_comlaw_has_bop_details',
                'label'         => 'BOP Has Supplementary Detail',
                'name'          => 'ws_jx_comlaw_has_bop_details',
                'type'          => 'true_false',
                'instructions'  => 'Enable to add a note about a non-standard or notable burden of proof situation under this doctrine.',
                'ui'            => 1,
                'ui_on_text'    => 'Yes',
                'ui_off_text'   => 'No',
                'default_value' => 0,
            ],

            [
                'key'               => 'field_jx_comlaw_bop_details',
                'label'             => 'BOP Details',
                'name'              => 'ws_jx_comlaw_bop_details',
                'type'              => 'textarea',
                'rows'              => 3,
                'instructions'      => 'Describe the notable burden of proof situation under this doctrine.',
                'conditional_logic' => [ [ [
                    'field'    => 'field_jx_comlaw_has_bop_details',
                    'operator' => '==',
                    'value'    => '1',
                ] ] ],
            ],

            // ────────────────────────────────────────────────────────────────

            [
                'key'          => 'field_jx_comlaw_bop_flag',
                'label'        => 'BOP Flag',
                'name'         => 'ws_jx_comlaw_bop_flag',
                'type'         => 'text',
                'instructions' => 'Short signal phrase identifying a non-standard burden shift. Use a compact hyphenated phrase, e.g. "contributing-factor-shift", "90-day rebuttable presumption". Not a full sentence.',
                'maxlength'    => 120,
            ],

            // Tab: Reward
            // ────────────────────────────────────────────────────────────────

            [
                'key'   => 'field_jx_comlaw_reward_tab',
                'label' => 'Reward',
                'type'  => 'tab',
            ],

            [
                'key'           => 'field_jx_comlaw_has_reward_available',
                'label'         => 'Reward Available',
                'name'          => 'ws_jx_comlaw_has_reward_available',
                'type'          => 'true_false',
                'instructions'  => 'Enable if this doctrine provides a monetary reward or bounty (distinct from compensatory remedies).',
                'ui'            => 1,
                'ui_on_text'    => 'Yes',
                'ui_off_text'   => 'No',
                'default_value' => 0,
            ],

            [
                'key'               => 'field_jx_comlaw_reward_details',
                'label'             => 'Reward Details',
                'name'              => 'ws_jx_comlaw_reward_details',
                'type'              => 'textarea',
                'rows'              => 3,
                'instructions'      => 'Describe the reward structure.',
                'conditional_logic' => [ [ [
                    'field'    => 'field_jx_comlaw_has_reward_available',
                    'operator' => '==',
                    'value'    => '1',
                ] ] ],
            ],

            // ── Relationships ────────────────────────────────────────────

            [
                'key'   => 'field_jx_comlaw_relationships_tab',
                'label' => 'Relationships',
                'type'  => 'tab',
            ],

            [
                'key'           => 'field_jx_comlaw_citation_ids',
                'label'         => 'Related Citations',
                'name'          => 'ws_jx_comlaw_citation_ids',
                'type'          => 'post_object',
                'post_type'     => [ 'jx-citation' ],
                'instructions'  => 'Link citations that directly support this doctrine. Optional.',
                'required'      => 0,
                'multiple'      => 1,
                'allow_null'    => 1,
                'ui'            => 1,
                'return_format' => 'id',
            ],

            [
                'key'           => 'field_jx_comlaw_interpretation_ids',
                'label'         => 'Related Interpretations',
                'name'          => 'ws_jx_comlaw_interpretation_ids',
                'type'          => 'post_object',
                'post_type'     => [ 'jx-interpretation' ],
                'instructions'  => 'Link interpretations that directly analyze this doctrine. Optional.',
                'required'      => 0,
                'multiple'      => 1,
                'allow_null'    => 1,
                'ui'            => 1,
                'return_format' => 'id',
            ],

            // ── Reference Materials ───────────────────────────────────────

            [
                'key'   => 'field_jx_comlaw_ref_materials_tab',
                'label' => 'Reference Materials',
                'type'  => 'tab',
            ],

            [
                'key'           => 'field_jx_comlaw_ref_materials',
                'label'         => 'Reference Materials',
                'name'          => 'ws_jx_comlaw_ref_materials',
                'type'          => 'relationship',
                'post_type'     => [ 'ws-reference' ],
                'filters'       => [ 'search' ],
                'instructions'  => 'Attach external reference materials relevant to this record.',
                'min'           => 0,
                'max'           => 0,
                'return_format' => 'object',
            ],

        ],
    ] );

} // end ws_register_acf_jx_common_law


// ── Conditional logic: taxonomy sentinel-gated details fields ─────────────────
//
// The following detail textareas are shown when the companion trigger field
// includes sentinel slug 'has-details':
// - ws_jx_comlaw_protected_classes
// - ws_jx_comlaw_disclosure_targets
// - ws_jx_comlaw_adverse_action_types
// - ws_jx_comlaw_remedies
// - ws_jx_comlaw_employee_standards
// - ws_jx_comlaw_employer_defenses

add_filter( 'acf/load_field', 'ws_jx_comlaw_details_conditional' );

function ws_jx_comlaw_details_conditional( $field ) {

    static $map = [
        'field_jx_comlaw_protected_class_details'    => [ 'ws_protected_class',      'field_jx_comlaw_protected_classes' ],
        'field_jx_comlaw_disclosure_target_details'  => [ 'ws_disclosure_target',    'field_jx_comlaw_disclosure_targets' ],
        'field_jx_comlaw_adverse_action_type_details' => [ 'ws_adverse_action_type',  'field_jx_comlaw_adverse_action_types' ],
        'field_jx_comlaw_remedy_details'             => [ 'ws_remedy',               'field_jx_comlaw_remedies' ],
        'field_jx_comlaw_employee_standard_details'  => [ 'ws_employee_standard',    'field_jx_comlaw_employee_standards' ],
        'field_jx_comlaw_employer_defense_details'   => [ 'ws_employer_defense',     'field_jx_comlaw_employer_defenses' ],
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
