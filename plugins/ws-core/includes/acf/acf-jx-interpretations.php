<?php
/**
 * acf-jx-interpretations.php
 *
 * Registers ACF Pro fields for the `jx-interpretation` CPT.
 *
 * PURPOSE
 * -------
 * Provides structured metadata for interpretation records, including case
 * identity, classification tags, parent-record linkage, and jurisdiction
 * impact signals used by legal analysis and summary workflows.
 *
 * GROUP: group_jx_interpretation_metadata
 *
 * FIELD SUMMARY
 * -------------
 * Case Identity tab:
 *   ws_jx_interp_official_name         Case full name (text, required)
 *   ws_jx_interp_case_citation         Reporter citation (text, optional)
 *   ws_jx_interp_court                 Court selector (select, required)
 *   ws_jx_interp_year                  Decision year (number, required)
 *   ws_jx_interp_url                   Opinion source URL (url, optional)
 *   ws_jx_interp_url_is_pdf            Opinion URL is PDF toggle (true_false, optional)
 *
 * Summary tab:
 *   ws_jx_interp_summary_wysiwyg       Holding summary content (wysiwyg, optional)
 *   ws_jx_interp_has_attach_flag       Summary-page attach toggle (true_false, optional)
 *   ws_jx_interp_display_order         Summary-page render order (number, optional)
 *   ws_jx_interp_last_reviewed         Last reviewed date value (text, optional)
 *
 * Classification tab:
 *   ws_jx_interp_disclosure_types       Disclosure categories (taxonomy, optional)
 *   ws_jx_interp_protected_classes      Protected classes (taxonomy, optional)
 *   ws_jx_interp_disclosure_targets     Disclosure targets (taxonomy, optional)
 *   ws_jx_interp_adverse_action_types   Adverse action types (taxonomy, optional)
 *   ws_jx_interp_remedies               Remedies (taxonomy, optional)
 *
 * Relationships tab:
 *   ws_jx_interp_statute_id            Parent statute link (post_object, optional)
 *   ws_jx_interp_common_law_id         Parent common-law link (post_object, optional)
 *   ws_jx_interp_affected_jurisdictions Affected jurisdictions (taxonomy, optional)
 *
 * Reference Materials tab:
 *   ws_jx_interp_ref_materials         Linked references (relationship, optional)
 *
 * SHARED WORKFLOW GROUPS
 * ----------------------
 *   - group_stamp_metadata (acf-stamp-fields.php, menu_order 90)
 *   - group_plain_english_metadata (acf-plain-english-fields.php, menu_order 85)
 *   - group_source_verify_metadata (acf-source-verify.php)
 *   - group_major_edit_metadata (acf-major-edit.php, menu_order 99)
 *
 * IMPLEMENTATION NOTES
 * --------------------
 * Court choices are populated dynamically by ws_interp_load_court_choices().
 * ws_jx_interp_affected_jurisdictions is auto-computed on save from the court
 * matrix mapping and uses save_terms: 0 to avoid taxonomy query pollution.
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

add_action( 'acf/init', 'ws_register_acf_jx_interpretations' );

function ws_register_acf_jx_interpretations() {

    if ( ! function_exists( 'acf_add_local_field_group' ) ) {
        return;
    }

    acf_add_local_field_group( [
        'key'                   => 'group_jx_interpretation_metadata',
        'title'                 => 'Interpretation Details',
        'menu_order'            => 0,
        'position'              => 'normal',
        'style'                 => 'default',
        'label_placement'       => 'top',
        'instruction_placement' => 'label',
        'active'                => true,

        'location' => [ [ [
            'param'    => 'post_type',
            'operator' => '==',
            'value'    => 'jx-interpretation',
        ] ] ],

        'fields' => [

            // ────────────────────────────────────────────────────────────────
            // Tab: Case Identity
            //
            // Core bibliographic data — the court, case name, citation,
            // year, and a link to the full opinion.
            // ────────────────────────────────────────────────────────────────

            [
                'key'   => 'field_jx_interp_case_identity_tab',
                'label' => 'Case Identity',
                'type'  => 'tab',
            ],

            [
                'key'           => 'field_jx_interp_court',
                'label'         => 'Court',
                'name'          => 'ws_jx_interp_court',
                'type'          => 'select',
                'instructions'  => 'Select the court that issued this decision. For state court decisions, select "State Level" and enter the court name in the field below.',
                'choices'       => [],  // populated by ws_interp_load_court_choices()
                'allow_null'    => 0,
                'required'      => 1,
                'ui'            => 1,
                'return_format' => 'value',
                'wrapper'       => [ 'width' => '50' ],
            ],

            [
                'key'               => 'field_jx_interp_court_name',
                'label'             => 'Court Name',
                'name'              => 'ws_jx_interp_court_name',
                'type'              => 'text',
                'instructions'      => 'Enter the court name. Include jurisdiction and level where relevant, e.g., "Superior Court of California, Sacramento County". This field only appears when "Other" is selected above.',
                'required'          => 1,
                'conditional_logic' => [ [ [
                    'field'    => 'field_jx_interp_court',
                    'operator' => '==',
                    'value'    => 'other',
                ] ] ],
                'wrapper'           => [ 'width' => '50' ],
            ],

            [
                'key'          => 'field_jx_interp_year',
                'label'        => 'Decision Year',
                'name'         => 'ws_jx_interp_year',
                'type'         => 'number',
                'instructions' => 'Four-digit year the decision was issued.',
                'required'     => 1,
                'min'          => 1900,
                'max'          => 2099,
                'step'         => 1,
                'wrapper'      => [ 'width' => '25' ],
            ],

            [
                'key'           => 'field_jx_interp_favorable',
                'label'         => 'Favorable to Whistleblower?',
                'name'          => 'ws_jx_interp_favorable',
                'type'          => 'true_false',
                'instructions'  => 'Does this ruling support the whistleblower\'s position?',
                'ui'            => 1,
                'ui_on_text'    => 'Yes',
                'ui_off_text'   => 'No',
                'default_value' => 0,
                'wrapper'       => [ 'width' => '25' ],
            ],

            [
                'key'          => 'field_jx_interp_official_name',
                'label'        => 'Official Name',
                'name'         => 'ws_jx_interp_official_name',
                'type'         => 'text',
                'instructions' => 'Full case name, e.g., "Bechtel v. Administrative Review Board".',
                'required'     => 1,
                'wrapper'      => [ 'width' => '70' ],
            ],

            [
                'key'          => 'field_jx_interp_common_name',
                'label'        => 'Common Name',
                'name'         => 'ws_jx_interp_common_name',
                'type'         => 'text',
                'instructions' => 'Shortened or colloquial name if this case is commonly cited by a shorter title — e.g., "Bechtel". Leave blank if no common name applies.',
                'required'     => 0,
                'wrapper'      => [ 'width' => '30' ],
            ],

            [
                'key'          => 'field_jx_interp_case_citation',
                'label'        => 'Citation',
                'name'         => 'ws_jx_interp_case_citation',
                'type'         => 'text',
                'instructions' => 'Standard legal citation, e.g., "710 F.3d 443 (1st Cir. 2013)".',
                'required'     => 1,
                'wrapper'      => [ 'width' => '30' ],
            ],

            [
                'key'          => 'field_jx_interp_url',
                'label'        => 'Opinion URL',
                'name'         => 'ws_jx_interp_url',
                'type'         => 'url',
                'instructions' => 'Link to the full opinion (CourtListener, Google Scholar, PACER, etc.).',
            ],

            [
                'key'           => 'field_jx_interp_url_is_pdf',
                'label'         => 'Link is PDF?',
                'name'          => 'ws_jx_interp_url_is_pdf',
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
                'key'   => 'field_jx_interp_summary_tab',
                'label' => 'Summary',
                'type'  => 'tab',
            ],

            [
                'key'          => 'field_jx_interp_summary_wysiwyg',
                'label'        => 'Summary',
                'name'         => 'ws_jx_interp_summary_wysiwyg',
                'type'         => 'wysiwyg',
                'instructions' => 'Summarize what the court decided in plain language. Focus on what this ruling means for whistleblowers — not legal procedure. Citation is captured above.',
                'required'     => 1,
                'tabs'         => 'visual',
                'toolbar'      => 'basic',
                'media_upload' => 0,
                'delay'        => 0,
            ],

            [
                'key'           => 'field_jx_interp_has_attach_flag',
                'label'         => 'Attach to Jurisdiction Page',
                'name'          => 'ws_jx_interp_has_attach_flag',
                'type'          => 'true_false',
                'instructions'  => 'Enable to include this interpretation in the rendered section on the jurisdiction page. Disable to store for reference only.',
                'ui'            => 1,
                'ui_on_text'    => 'Attached',
                'ui_off_text'   => 'Unattached',
                'default_value' => 0,
            ],

            [
                'key'               => 'field_jx_interp_display_order',
                'label'             => 'Display Order',
                'name'              => 'ws_jx_interp_display_order',
                'type'              => 'number',
                'instructions'      => 'Set the order in which this interpretation appears on the jurisdiction page. Lower numbers appear first.',
                'min'               => 1,
                'step'              => 1,
                'conditional_logic' => [ [ [
                    'field'    => 'field_jx_interp_has_attach_flag',
                    'operator' => '==',
                    'value'    => '1',
                ] ] ],
            ],

            // ────────────────────────────────────────────────────────────────
            // Tab: Classification
            //
            // Doctrinal taxonomy fields mirroring jx-statute. Tag only what
            // the interpretation genuinely addresses or clarifies — do not
            // inherit from the parent statute. has-details sentinel pattern
            // active on all supporting taxonomies — companion _details fields
            // follow each.
            // ────────────────────────────────────────────────────────────────

            [
                'key'   => 'field_jx_interp_classification_tab',
                'label' => 'Classification',
                'type'  => 'tab',
            ],

            [
                'key'           => 'field_jx_interp_disclosure_types',
                'label'         => 'Disclosure Category',
                'name'          => 'ws_jx_interp_disclosure_types',
                'type'          => 'taxonomy',
                'taxonomy'      => 'ws_disclosure_type',
                'field_type'    => 'multi_select',
                'instructions'  => 'Subject matter addressed or clarified by this interpretation. Tag only what the interpretation genuinely explains or narrows.',
                'add_term'      => 0,
                'save_terms'    => 1,
                'load_terms'    => 1,
                'return_format' => 'id',
            ],

            [
                'key'           => 'field_jx_interp_protected_classes',
                'label'         => 'Protected Class',
                'name'          => 'ws_jx_interp_protected_classes',
                'type'          => 'taxonomy',
                'taxonomy'      => 'ws_protected_class',
                'field_type'    => 'multi_select',
                'instructions'  => 'Worker classification addressed or clarified by this interpretation. Tag only where the interpretation explicitly turns on or explains protected class applicability.',
                'add_term'      => 0,
                'save_terms'    => 1,
                'load_terms'    => 1,
                'return_format' => 'id',
            ],

            [
                'key'          => 'field_jx_interp_protected_class_details',
                'label'        => 'Protected Class Details',
                'name'         => 'ws_jx_interp_protected_class_details',
                'type'         => 'textarea',
                'rows'         => 3,
                'instructions' => 'Describe nuance in protected class coverage as addressed by this interpretation.',
                // conditional_logic set dynamically — see ws_jx_interp_details_conditional()
            ],

            [
                'key'           => 'field_jx_interp_disclosure_targets',
                'label'         => 'Disclosure Targets',
                'name'          => 'ws_jx_interp_disclosure_targets',
                'type'          => 'taxonomy',
                'taxonomy'      => 'ws_disclosure_target',
                'field_type'    => 'multi_select',
                'instructions'  => 'Reporting target addressed or clarified by this interpretation. Tag only where the interpretation explicitly discusses or turns on the reporting channel.',
                'add_term'      => 0,
                'save_terms'    => 1,
                'load_terms'    => 1,
                'return_format' => 'id',
            ],

            [
                'key'          => 'field_jx_interp_disclosure_target_details',
                'label'        => 'Disclosure Targets Details',
                'name'         => 'ws_jx_interp_disclosure_target_details',
                'type'         => 'textarea',
                'rows'         => 3,
                'instructions' => 'Describe nuance in the reporting channel as addressed by this interpretation.',
                // conditional_logic set dynamically — see ws_jx_interp_details_conditional()
            ],

            [
                'key'           => 'field_jx_interp_adverse_action_types',
                'label'         => 'Adverse Action Types',
                'name'          => 'ws_jx_interp_adverse_action_types',
                'type'          => 'taxonomy',
                'taxonomy'      => 'ws_adverse_action_type',
                'field_type'    => 'multi_select',
                'instructions'  => 'Retaliatory action addressed or clarified by this interpretation. Tag only where the interpretation explicitly explains or narrows the type of adverse action covered.',
                'add_term'      => 0,
                'save_terms'    => 1,
                'load_terms'    => 1,
                'return_format' => 'id',
            ],

            [
                'key'          => 'field_jx_interp_adverse_action_type_details',
                'label'        => 'Adverse Action Details',
                'name'         => 'ws_jx_interp_adverse_action_type_details',
                'type'         => 'textarea',
                'rows'         => 3,
                'instructions' => 'Describe nuance in adverse action coverage as addressed by this interpretation.',
                // conditional_logic set dynamically — see ws_jx_interp_details_conditional()
            ],

            [
                'key'           => 'field_jx_interp_process_types',
                'label'         => 'Process Type',
                'name'          => 'ws_jx_interp_process_types',
                'type'          => 'taxonomy',
                'taxonomy'      => 'ws_process_type',
                'field_type'    => 'multi_select',
                'instructions'  => 'Procedural route addressed or clarified by this interpretation. Tag only where the interpretation explicitly explains or narrows procedural requirements or options.',
                'add_term'      => 0,
                'save_terms'    => 1,
                'load_terms'    => 1,
                'return_format' => 'id',
            ],

            [
                'key'           => 'field_jx_interp_remedies',
                'label'         => 'Remedies',
                'name'          => 'ws_jx_interp_remedies',
                'type'          => 'taxonomy',
                'taxonomy'      => 'ws_remedy',
                'field_type'    => 'multi_select',
                'instructions'  => 'Remedies addressed, clarified, or limited by this interpretation. Tag only where the interpretation explicitly explains remedy availability or scope.',
                'add_term'      => 0,
                'save_terms'    => 1,
                'load_terms'    => 1,
                'return_format' => 'id',
            ],

            [
                'key'          => 'field_jx_interp_remedy_details',
                'label'        => 'Remedies Details',
                'name'         => 'ws_jx_interp_remedy_details',
                'type'         => 'textarea',
                'rows'         => 3,
                'instructions' => 'Describe nuance in remedy availability or scope as addressed by this interpretation.',
                // conditional_logic set dynamically — see ws_jx_interp_details_conditional()
            ],

            [
                'key'           => 'field_jx_interp_fee_shiftings',
                'label'         => 'Fee Shifting',
                'name'          => 'ws_jx_interp_fee_shiftings',
                'type'          => 'taxonomy',
                'taxonomy'      => 'ws_fee_shifting',
                'field_type'    => 'multi_select',
                'instructions'  => 'Fee-shifting rule addressed or clarified by this interpretation. Tag only where the interpretation explicitly explains fee-shifting applicability or limits. Single value.',
                'add_term'      => 0,
                'save_terms'    => 1,
                'load_terms'    => 1,
                'return_format' => 'id',
            ],

            [
                'key'           => 'field_jx_interp_employer_defenses',
                'label'         => 'Employer Defense',
                'name'          => 'ws_jx_interp_employer_defenses',
                'type'          => 'taxonomy',
                'taxonomy'      => 'ws_employer_defense',
                'field_type'    => 'multi_select',
                'instructions'  => 'Employer defense addressed, validated, or rejected by this interpretation. Tag only where the interpretation explicitly explains or limits a defense posture.',
                'add_term'      => 0,
                'save_terms'    => 1,
                'load_terms'    => 1,
                'return_format' => 'id',
            ],

            [
                'key'          => 'field_jx_interp_employer_defense_details',
                'label'        => 'Employer Defense Details',
                'name'         => 'ws_jx_interp_employer_defense_details',
                'type'         => 'textarea',
                'rows'         => 3,
                'instructions' => 'Describe nuance in the employer defense posture as addressed by this interpretation.',
                // conditional_logic set dynamically — see ws_jx_interp_details_conditional()
            ],

            [
                'key'           => 'field_jx_interp_employee_standards',
                'label'         => 'Employee Standard',
                'name'          => 'ws_jx_interp_employee_standards',
                'type'          => 'taxonomy',
                'taxonomy'      => 'ws_employee_standard',
                'field_type'    => 'multi_select',
                'instructions'  => 'Burden-of-proof standard addressed or clarified by this interpretation. Tag only where the interpretation explicitly explains or narrows the employee burden standard.',
                'add_term'      => 0,
                'save_terms'    => 1,
                'load_terms'    => 1,
                'return_format' => 'id',
            ],

            [
                'key'          => 'field_jx_interp_employee_standard_details',
                'label'        => 'Employee Standard Details',
                'name'         => 'ws_jx_interp_employee_standard_details',
                'type'         => 'textarea',
                'rows'         => 3,
                'instructions' => 'Describe nuance in the burden-of-proof standard as addressed by this interpretation.',
                // conditional_logic set dynamically — see ws_jx_interp_details_conditional()
            ],

            // ────────────────────────────────────────────────────────────────
            // Tab: Relationships
            //
            // Links this interpretation back to statute and/or common-law parent.
            // Jurisdiction scope is provided by ws_jurisdiction taxonomy.
            // ────────────────────────────────────────────────────────────────

            [
                'key'   => 'field_jx_interp_relationships_tab',
                'label' => 'Relationships',
                'type'  => 'tab',
            ],

            [
                'key'           => 'field_jx_interp_statute_id',
                'label'         => 'Parent Statute',
                'name'          => 'ws_jx_interp_statute_id',
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
                'key'           => 'field_jx_interp_common_law_id',
                'label'         => 'Parent Common Law Doctrine',
                'name'          => 'ws_jx_interp_common_law_id',
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
                'key'           => 'field_jx_interp_affected_jurisdictions',
                'label'         => 'Affected Jurisdictions',
                'name'          => 'ws_jx_interp_affected_jurisdictions',
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
            // interpretation's own group.

            [
                'key'          => 'field_jx_interp_last_reviewed',
                'label'        => 'Last Verified Date',
                'name'         => 'ws_jx_interp_last_reviewed',
                'type'         => 'text',
                'instructions' => 'Update this date each time the record is meaningfully revised.',
            ],

            // ── Tab: Plain Language ───────────────────────────────────────
            // Removed — registered centrally in acf-plain-english-fields.php
            // (group_plain_english_metadata, menu_order 85).

            // ── Tab: Reference Materials ───────────────────────────────────
            //
            // Links this interpretation to ws-reference records for researchers
            // and legal professionals. Not rendered on jurisdiction pages.
            // Only approved references display publicly via [ws_reference_page].

            [
                'key'   => 'field_jx_interp_ref_materials_tab',
                'label' => 'Reference Materials',
                'type'  => 'tab',
            ],

            [
                'key'           => 'field_jx_interp_ref_materials',
                'label'         => 'Reference Materials',
                'name'          => 'ws_jx_interp_ref_materials',
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

} // end ws_register_acf_jx_interpretations


// ── Conditional logic: taxonomy sentinel-gated details fields ─────────────────
//
// The following detail textareas are shown when the companion trigger field
// includes sentinel slug 'has-details':
// - ws_jx_interp_protected_classes
// - ws_jx_interp_disclosure_targets
// - ws_jx_interp_adverse_action_types
// - ws_jx_interp_remedies
// - ws_jx_interp_employee_standards
// - ws_jx_interp_employer_defenses

add_filter( 'acf/load_field', 'ws_jx_interp_details_conditional' );

function ws_jx_interp_details_conditional( $field ) {

    static $map = [
        'field_jx_interp_protected_class_details'    => [ 'ws_protected_class',      'field_jx_interp_protected_classes' ],
        'field_jx_interp_disclosure_target_details'  => [ 'ws_disclosure_target',    'field_jx_interp_disclosure_targets' ],
        'field_jx_interp_adverse_action_type_details' => [ 'ws_adverse_action_type',  'field_jx_interp_adverse_action_types' ],
        'field_jx_interp_remedy_details'             => [ 'ws_remedy',               'field_jx_interp_remedies' ],
        'field_jx_interp_employee_standard_details'  => [ 'ws_employee_standard',    'field_jx_interp_employee_standards' ],
        'field_jx_interp_employer_defense_details'   => [ 'ws_employer_defense',     'field_jx_interp_employer_defenses' ],
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
//     state courts. $ws_court_matrix and $ws_state_court_matrix are merged.
//
//   State statute (does not have 'us' term):
//     State courts only ($ws_state_court_matrix). Federal courts do not
//     interpret state statutes.
//
//   Unknown (no parent resolved yet):
//     Defaults to showing all courts (federal + state) as a safe fallback.
//
// Parent context is resolved from saved meta (existing records) or URL
// parameters (new records): statute_id, then common_law_id.
//
// The 'other' entry (level=99) sorts last and reveals the free-text
// ws_jx_interp_court_name field for courts not in either matrix.
//
// Sorted by level ascending so SCOTUS / state supreme courts appear before
// appellate courts, which appear before district / trial courts.

add_filter( 'acf/load_field/key=field_jx_interp_court', 'ws_interp_load_court_choices' );

function ws_interp_load_court_choices( $field ) {
    global $ws_court_matrix, $ws_state_court_matrix, $post;

    if ( empty( $ws_court_matrix ) ) {
        return $field;
    }

    // Resolve parent context — saved meta first, URL param fallback.
    $parent_id = 0;
    if ( $post && get_post_type( $post->ID ) === 'jx-interpretation' && get_post_status( $post->ID ) !== 'auto-draft' ) {
        $statute_id = (int) get_post_meta( $post->ID, 'ws_jx_interp_statute_id', true );
        $common_law_id = (int) get_post_meta( $post->ID, 'ws_jx_interp_common_law_id', true );
        $parent_id = $statute_id > 0 ? $statute_id : $common_law_id;
    }
    if ( ! $parent_id && isset( $_GET['statute_id'] ) ) {
        $parent_id = absint( $_GET['statute_id'] );
    }
    if ( ! $parent_id && isset( $_GET['common_law_id'] ) ) {
        $parent_id = absint( $_GET['common_law_id'] );
    }

    // Determine parent scope. Unknown parent defaults to showing all courts.
    $is_federal = ! $parent_id || has_term( 'us', WS_JURISDICTION_TAXONOMY, $parent_id );

    $candidates = $is_federal
        ? array_merge( $ws_court_matrix, $ws_state_court_matrix ?: [] )
        : ( $ws_state_court_matrix ?: [] );

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

add_filter( 'acf/load_value/key=field_jx_interp_statute_id', 'ws_interp_prefill_statute_id', 5, 3 );
add_filter( 'acf/load_value/key=field_jx_interp_common_law_id', 'ws_interp_prefill_common_law_id', 5, 3 );

function ws_interp_prefill_statute_id( $value, $post_id, $field ) {

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

/**
 * Pre-populate ws_jx_interp_common_law_id from ?common_law_id= URL parameter.
 */
function ws_interp_prefill_common_law_id( $value, $post_id, $field ) {

    // Only pre-fill on brand-new auto-draft posts.
    if ( get_post_status( $post_id ) !== 'auto-draft' ) {
        return $value;
    }

    // Only act when the URL carries a valid common_law_id.
    if ( ! isset( $_GET['common_law_id'] ) ) {
        return $value;
    }

    $common_law_id = absint( $_GET['common_law_id'] );

    if ( $common_law_id && get_post_type( $common_law_id ) === 'jx-common-law' ) {
        return $common_law_id;
    }

    return $value;
}


// ── Auto-populate ws_jx_interp_affected_jurisdictions from court matrix on every save ────
//
// Runs at priority 20 (after ACF saves its fields at 10). Reads the court key
// saved to ws_jx_interp_court, looks it up via ws_court_lookup() (checks both
// $ws_court_matrix and $ws_state_court_matrix), and resolves ws_jx_codes to
// ws_jurisdiction taxonomy term IDs.
//
// SCOTUS (ws_jx_codes = null): writes an empty array. The query/render layer
// treats empty affected_jx + SCOTUS court as "all jurisdictions" — avoids
// storing all 60+ term IDs in meta unnecessarily.
//
// Recomputes on every save so the value stays in sync if the court is changed.

add_action( 'acf/save_post', 'ws_interp_auto_populate_affected_jx', 20 );

function ws_interp_auto_populate_affected_jx( $post_id ) {

    if ( get_post_type( $post_id ) !== 'jx-interpretation' ) {
        return;
    }

    $court_key  = get_post_meta( $post_id, 'ws_jx_interp_court', true );
    $court_data = ws_court_lookup( $court_key );

    if ( ! $court_data ) {
        return;
    }

    $jx_codes = $court_data['ws_jx_codes'];

    // Other: geographic scope is unknown — leave affected_jx for manual entry.
    if ( $jx_codes === '__manual__' ) {
        return;
    }

    // SCOTUS: null = all jurisdictions. Store empty to signal bind-all.
    if ( $jx_codes === null ) {
        update_post_meta( $post_id, 'ws_jx_interp_affected_jurisdictions', [] );
        return;
    }

    // Resolve each USPS code to a ws_jurisdiction term ID.
    $term_ids = [];
    foreach ( $jx_codes as $code ) {
        $term = ws_jx_term_by_code( $code );
        if ( $term && ! is_wp_error( $term ) ) {
            $term_ids[] = $term->term_id;
        }
    }

    update_post_meta( $post_id, 'ws_jx_interp_affected_jurisdictions', $term_ids );
}
