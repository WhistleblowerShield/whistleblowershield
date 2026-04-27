<?php
/**
 * register-taxonomies.php — Registers all ws-core taxonomies and seeds initial terms.
 *
 * @package WhistleblowerShield
 * @since   2.1.0
 * @version 3.16.0
 *
 * VERSION
 * -------
 * 3.16.0  Final nuance pass — multi-agent review:
 *         ws_legal_recognition: 15 new terms added (anti-gag-provision,
 *         third-party-retaliation, catch-all-protection, no-retaliatory-evidence,
 *         trade-secret-immunity, stay-of-disciplinary-action, manager-rule-exclusion,
 *         public-concern-required, employer-knowledge-required, bad-faith-exclusion,
 *         anti-slapp-protection, successor-liability-recognized, extraterritorial-coverage,
 *         confidential-settlement-restriction, internal-only-sufficient).
 *         Gate bumped to 1.0.0.
 *         ws_causation_standard: contributing-factor-but-for-backstop and
 *         substantial-motivating-factor added. Gate bumped to 1.0.0.
 *         ws_adverse_action: retaliatory-discovery added. Gate bumped to 1.2.0.
 *         ws_remedy: neutral-reference, attorney-fees-admin added. Gate bumped to 1.2.0.
 *         ws_protected_action: opposition-clause and participation-clause added as
 *         hierarchical parents; internal-objection, filing-complaint,
 *         assisting-complainant added as child terms. Gate bumped to 1.0.0.
 *
 * VERSION
 * -------
 * 2.1.0   Initial: ws_disclosure_type.
 * 2.3.1   ws_process_type added.
 * 2.4.0   ws_coverage_scope, ws_retaliation_forms, ws_language, ws_case_stage added.
 * 3.0.0   ws_jurisdiction registered; all gates migrated to Unified Option-Gate Method.
 * 3.1.0   Taxonomy rename pass: ws_protected_class, ws_adverse_action, ws_remedy,
 *         ws_disclosure_target, ws_fee_shifting_rule. ws_bulk_insert_hierarchical() added.
 * 3.2.0   ws_employer_defense added (jx-statute).
 * 3.3.0   ws_aorg_type added (ws-assist-org).
 * 3.6.0   National Security parent + 3 children added to ws_disclosure_type.
 * 3.7.0   ws_employment_sector added. Deprecated taxonomies removed.
 * 3.8.1   ws_seed_disclosure_taxonomy() refactored to ws_bulk_insert_hierarchical().
 * 3.9.0   ag-procedure added to ws_jurisdiction and ws_disclosure_type object_types.
 * 3.10.0  ws_procedure_type added (ag-procedure). Replaces ws_proc_type ACF select.
 * 3.11.0  has-details sentinel term added to ws_adverse_action, ws_remedy,
 *         ws_disclosure_target, ws_protected_class, ws_employer_defense. Signals
 *         that a companion ACF freetext field holds detail beyond available slugs.
 *         Gate versions bumped to 1.0.0 for affected seeders.
 * 3.12.0  ws_employee_standard added
 * 3.14.1  all-sectors added to ws_protected_class as parent; all-employees added as child.
 *          internal-management added to ws_disclosure_target under internal parent.
 *          general-legal parent + general-wrongdoing child added to ws_disclosure_type.
 * 3.14.2  retaliation-protection and wrongful-termination removed from ws_disclosure_type
 *          (workplace-employment children). These are adverse action types, not disclosure
 *          types. Disclosure gate bumped to 1.2.0. (jx-statute). Flat taxonomy replacing freetext
 *         employee_standard field. Seven terms including has-details sentinel.
 * 3.13.0  jx-common-law added to object_types for all shared doctrinal taxonomies:
 *         ws_disclosure_type, ws_protected_class, ws_disclosure_target,
 *         ws_adverse_action, ws_process_type, ws_remedy, ws_fee_shifting_rule,
 *         ws_employer_defense, ws_employee_standard, ws_jurisdiction.
 *         jx-citation and jx-construction also added to taxonomies where missing.
 * 3.15.0  ws_legal_recognition added — flat taxonomy replacing *_recognized booleans.
 *         ws_causation_standard added — flat taxonomy for causation standards,
 *         split from ws_employee_standard. Causation terms removed from
 *         ws_employee_standard. ws_adverse_action: retaliatory-litigation,
 *         hostile-work-environment, retaliatory-investigation added.
 *         ws_protected_class: intern-volunteer added under special-status.
 *         qui-tam-relator confirmed in ws_excluded_class (already present).
 *         ws_remedy: interim-reinstatement, tax-gross-up added.
 *         ws_employee_standard gate bumped to 1.0.0 (causation terms removed).
 * 3.14.2  ws_disclosure_type and ws_process_type set to non-public.
 *         Both remain visible in wp-admin and available to internal tooling.
 */

defined( 'ABSPATH' ) || exit;


// ════════════════════════════════════════════════════════════════════════════
// TAXONOMY REGISTRATION
// ════════════════════════════════════════════════════════════════════════════

/**
 * Register all taxonomies for the WhistleblowerShield Core.
 */
function ws_register_taxonomies() {

    // ── 1. Disclosure Categories ──────────────────────────────────────────
    //

    if ( ! taxonomy_exists( 'ws_disclosure_type' ) ) {
        register_taxonomy(
            'ws_disclosure_type',
            [ 'jx-statute', 'jx-common-law', 'jx-citation', 'jx-construction', 'ws-agency', 'ag-procedure', 'ws-assist-org' ],
            [
                'label'             => 'Disclosure Categories',
                'labels'            => [
                    'name'              => 'Disclosure Categories',
                    'singular_name'     => 'Disclosure Category',
                    'search_items'      => 'Search Categories',
                    'all_items'         => 'All Categories',
                    'parent_item'       => 'Parent Category',
                    'parent_item_colon' => 'Parent Category:',
                    'edit_item'         => 'Edit Category',
                    'update_item'       => 'Update Category',
                    'add_new_item'      => 'Add New Category',
                    'new_item_name'     => 'New Disclosure Category Name',
                    'menu_name'         => 'Disclosures',
                ],
                'public'            => false,
                'publicly_queryable'=> false,
                'hierarchical'      => true,
                'show_ui'           => true,
                'show_in_rest'      => true,
                'show_admin_column' => true,
                'rewrite'           => false,
                'query_var'         => false,
                'capabilities'      => ws_get_taxonomy_caps(),
            ]
        );
    }

    // ── 2. Process Types ──────────────────────────────────────────────────

    if ( ! taxonomy_exists( 'ws_process_type' ) ) {
        register_taxonomy(
            'ws_process_type',
            [ 'jx-statute', 'jx-common-law', 'jx-citation', 'jx-construction', 'ws-agency', 'ws-assist-org' ],
            [
                'label'             => 'Process Types',
                'labels'            => [
                    'name'              => 'Process Types',
                    'singular_name'     => 'Process Type',
                    'search_items'      => 'Search Process Types',
                    'all_items'         => 'All Process Types',
                    'edit_item'         => 'Edit Process Type',
                    'update_item'       => 'Update Process Type',
                    'add_new_item'      => 'Add New Process Type',
                    'new_item_name'     => 'New Process Type Name',
                    'menu_name'         => 'Processes',
                ],
                'public'            => false,
                'publicly_queryable'=> false,
                'hierarchical'      => false,
                'show_ui'           => true,
                'show_in_rest'      => true,
                'show_admin_column' => true,
                'rewrite'           => false,
                'query_var'         => false,
                'capabilities'      => ws_get_taxonomy_caps(),
            ]
        );
    }

    // ── 3. Remedies ───────────────────────────────────────────────────────
    //
    // Renamed from ws_remedy_type → ws_remedy (3.1.0).

    if ( ! taxonomy_exists( 'ws_remedy' ) ) {
        register_taxonomy(
            'ws_remedy',
            [ 'jx-statute', 'jx-common-law', 'jx-citation', 'jx-construction' ],
            [
                'label'             => 'Remedies',
                'labels'            => [
                    'name'              => 'Remedies',
                    'singular_name'     => 'Remedy',
                    'search_items'      => 'Search Remedies',
                    'all_items'         => 'All Remedies',
                    'edit_item'         => 'Edit Remedy',
                    'update_item'       => 'Update Remedy',
                    'add_new_item'      => 'Add New Remedy',
                    'new_item_name'     => 'New Remedy Name',
                    'menu_name'         => 'Remedies',
                ],
                'public'            => false,
                'publicly_queryable'=> false,
                'hierarchical'      => false,
                'show_ui'           => true,
                'show_in_rest'      => true,
                'show_admin_column' => true,
                'rewrite'           => false,
                'query_var'         => false,
                'capabilities'      => ws_get_taxonomy_caps(),
            ]
        );
    }

    // ── 4. Protected Classes ────────────────────────────────────────────────
    //
    // Renamed from ws_coverage_scope → ws_protected_class (3.1.0).
    // Converted to hierarchical to support employee type groupings.

    if ( ! taxonomy_exists( 'ws_protected_class' ) ) {
        register_taxonomy(
            'ws_protected_class',
            [ 'jx-statute', 'jx-common-law', 'jx-citation', 'jx-construction', 'ws-assist-org' ],
            [
                'label'             => 'Protected Classes',
                'labels'            => [
                    'name'              => 'Protected Classes',
                    'singular_name'     => 'Protected Class',
                    'search_items'      => 'Search Protected Classes',
                    'all_items'         => 'All Protected Classes',
                    'parent_item'       => 'Parent Class',
                    'parent_item_colon' => 'Parent Class:',
                    'edit_item'         => 'Edit Protected Class',
                    'update_item'       => 'Update Protected Class',
                    'add_new_item'      => 'Add New Protected Class',
                    'new_item_name'     => 'New Protected Class Name',
                    'menu_name'         => 'Protected Classes',
                ],
                    'public'            => false,
                    'publicly_queryable'=> false,
                    'hierarchical'      => true,
                    'show_ui'           => true,
                    'show_in_rest'      => true,
                    'show_admin_column' => true,
                    'rewrite'           => false,
                    'query_var'         => false,
                    'capabilities'      => ws_get_taxonomy_caps(),
            ]
        );
    }

     // ── 5. Excluded Classes ────────────────────────────────────────────────
    //
    // Duplicate of Protected Classes.

    if ( ! taxonomy_exists( 'ws_excluded_class' ) ) {
        register_taxonomy(
            'ws_excluded_class',
            [ 'jx-statute', 'jx-common-law', 'jx-citation', 'jx-construction', ],
            [
                'label'             => 'Excluded Classes',
                'labels'            => [
                    'name'              => 'Excluded Classes',
                    'singular_name'     => 'Excluded Class',
                    'search_items'      => 'Search Excluded Classes',
                    'all_items'         => 'All Excluded Classes',
                    'parent_item'       => 'Parent Class',
                    'parent_item_colon' => 'Parent Class:',
                    'edit_item'         => 'Edit Excluded Class',
                    'update_item'       => 'Update Excluded Class',
                    'add_new_item'      => 'Add New Excluded Class',
                    'new_item_name'     => 'New Excluded Class Name',
                    'menu_name'         => 'Excluded Classes',
                ],
                    'public'            => false,
                    'publicly_queryable'=> false,
                    'hierarchical'      => true,
                    'show_ui'           => true,
                    'show_in_rest'      => true,
                    'show_admin_column' => true,
                    'rewrite'           => false,
                    'query_var'         => false,
                    'capabilities'      => ws_get_taxonomy_caps(),
            ]
        );
    }

    // ── 6. Adverse Actions ───────────────────────────────────────────
    //
    // Renamed from ws_retaliation_forms → ws_adverse_action (3.1.0).
    // Aligns with JSON field name adverse_action; cleaner legal terminology.

    if ( ! taxonomy_exists( 'ws_adverse_action' ) ) {
        register_taxonomy(
            'ws_adverse_action',
            [ 'jx-statute', 'jx-common-law', 'jx-citation', 'jx-construction' ],
            [
                'label'             => 'Adverse Action Types',
                'labels'            => [
                    'name'              => 'Adverse Action Types',
                    'singular_name'     => 'Adverse Action Type',
                    'search_items'      => 'Search Adverse Action Types',
                    'all_items'         => 'All Adverse Action Types',
                    'edit_item'         => 'Edit Adverse Action Type',
                    'update_item'       => 'Update Adverse Action Type',
                    'add_new_item'      => 'Add New Adverse Action Type',
                    'new_item_name'     => 'New Adverse Action Type Name',
                    'menu_name'         => 'Adverse Actions',
                ],
                'public'            => false,
                'publicly_queryable'=> false,
                'hierarchical'      => false,
                'show_ui'           => true,
                'show_in_rest'      => true,
                'show_admin_column' => true,
                'rewrite'           => false,
                'query_var'         => false,
                'capabilities'      => ws_get_taxonomy_caps(),
            ]
        );
    }

    // ── 7. Languages ──────────────────────────────────────────────────────


    if ( ! taxonomy_exists( 'ws_language' ) ) {
        register_taxonomy(
            'ws_language',
            [ 'ws-agency', 'ws-assist-org' ],
            [
                'label'             => 'Languages',
                'labels'            => [
                    'name'              => 'Languages',
                    'singular_name'     => 'Language',
                    'search_items'      => 'Search Languages',
                    'all_items'         => 'All Languages',
                    'edit_item'         => 'Edit Language',
                    'update_item'       => 'Update Language',
                    'add_new_item'      => 'Add New Language',
                    'new_item_name'     => 'New Language Name',
                    'menu_name'         => 'Languages',
                ],
                'public'            => false,
                'publicly_queryable'=> false,
                'hierarchical'      => false,
                'show_ui'           => true,
                'show_in_rest'      => true,
                'show_admin_column' => true,
                'rewrite'           => false,
                'query_var'         => false,
                'capabilities'      => ws_get_taxonomy_caps(),
            ]
        );
    }

    // ── 8. Case Stages ─────────────────────────────────────────────────────


    if ( ! taxonomy_exists( 'ws_case_stage' ) ) {
        register_taxonomy(
            'ws_case_stage',
            [ 'ws-assist-org' ],
            [
                'label'             => 'Case Stages',
                'labels'            => [
                    'name'              => 'Case Stages',
                    'singular_name'     => 'Case Stage',
                    'search_items'      => 'Search Case Stages',
                    'all_items'         => 'All Case Stages',
                    'edit_item'         => 'Edit Case Stage',
                    'update_item'       => 'Update Case Stage',
                    'add_new_item'      => 'Add New Case Stage',
                    'new_item_name'     => 'New Case Stage Name',
                    'menu_name'         => 'Case Stages',
                ],
                'public'            => false,
                'publicly_queryable'=> false,
                'hierarchical'      => false,
                'show_ui'           => true,
                'show_in_rest'      => true,
                'show_admin_column' => true,
                'rewrite'           => false,
                'query_var'         => false,
                'capabilities'      => ws_get_taxonomy_caps(),
            ]
        );
    }

    // ── 9. Jurisdictions ───────────────────────────────────────────────────
    //
    // Replaces ws_jx_code post meta as the jurisdiction join mechanism.
    // Private taxonomy — terms are canonical USPS-code slugs (e.g. 'us', 'ca', 'tx').
    // Terms are seeded by matrix-jurisdiction.php via ws_seeded_jurisdiction_taxonomy gate.

    if ( ! taxonomy_exists( WS_JURISDICTION_TAXONOMY ) ) {
        register_taxonomy(
            WS_JURISDICTION_TAXONOMY,
            [ 'jurisdiction', 'jx-statute', 'jx-summary', 'jx-citation', 'jx-construction', 'jx-common-law', 'ws-agency', 'ag-procedure', 'ws-assist-org' ],
            [
                'label'             => 'Jurisdictions',
                'labels'            => [
                    'name'              => 'Jurisdictions',
                    'singular_name'     => 'Jurisdiction',
                    'search_items'      => 'Search Jurisdictions',
                    'all_items'         => 'All Jurisdictions',
                    'edit_item'         => 'Edit Jurisdiction',
                    'update_item'       => 'Update Jurisdiction',
                    'add_new_item'      => 'Add New Jurisdiction',
                    'new_item_name'     => 'New Jurisdiction Name',
                    'menu_name'         => 'Jurisdictions',
                ],
                'public'            => false,
                'publicly_queryable'=> false,
                'hierarchical'      => false,
                'show_ui'           => true,
                'show_in_rest'      => true,
                'show_admin_column' => true,
                'rewrite'           => false,
                'query_var'         => false,
                'capabilities'      => ws_get_taxonomy_caps(),
            ]
        );
    }

    // ── 10. Disclosure Targets ─────────────────────────────────────────────
    //
    // New in 3.1.0. Describes who the disclosure was made to in order for
    // protection to apply. Hierarchical — grouped by reporting channel type.

    if ( ! taxonomy_exists( 'ws_disclosure_target' ) ) {
        register_taxonomy(
            'ws_disclosure_target',
            [ 'jx-statute', 'jx-common-law', 'jx-citation', 'jx-construction', 'ws-assist-org' ],
            [
                'label'             => 'Disclosure Targets',
                'labels'            => [
                    'name'              => 'Disclosure Targets',
                    'singular_name'     => 'Disclosure Target',
                    'search_items'      => 'Search Disclosure Targets',
                    'all_items'         => 'All Disclosure Targets',
                    'parent_item'       => 'Parent Target',
                    'parent_item_colon' => 'Parent Target:',
                    'edit_item'         => 'Edit Disclosure Target',
                    'update_item'       => 'Update Disclosure Target',
                    'add_new_item'      => 'Add New Disclosure Target',
                    'new_item_name'     => 'New Disclosure Target Name',
                    'menu_name'         => 'Disclosure Targets',
                ],
                'public'            => false,
                'publicly_queryable'=> false,
                'hierarchical'      => true,
                'show_ui'           => true,
                'show_in_rest'      => true,
                'show_admin_column' => true,
                'rewrite'           => false,
                'query_var'         => false,
                'capabilities'      => ws_get_taxonomy_caps(),
            ]
        );
    }

    // ── 11. Fee Shifting Rules ──────────────────────────────────────────────────
    //
    // New in 3.1.0. Flat taxonomy describing the fee shifting rule that
    // applies to enforcement of a law.

    if ( ! taxonomy_exists( 'ws_fee_shifting_rule' ) ) {
        register_taxonomy(
            'ws_fee_shifting_rule',
            [ 'jx-statute', 'jx-common-law', 'jx-citation', 'jx-construction' ],
            [
                'label'             => 'Fee Shifting Rules',
                'labels'            => [
                    'name'              => 'Fee Shifting Rules',
                    'singular_name'     => 'Fee Shifting Rule',
                    'search_items'      => 'Search Fee Shifting Rules',
                    'all_items'         => 'All Fee Shifting Rules',
                    'edit_item'         => 'Edit Fee Shifting Rule',
                    'update_item'       => 'Update Fee Shifting Rule',
                    'add_new_item'      => 'Add New Fee Shifting Rule',
                    'new_item_name'     => 'New Fee Shifting Rule Name',
                    'menu_name'         => 'Fee Shifting',
                ],
                'public'            => false,
                'publicly_queryable'=> false,
                'hierarchical'      => false,
                'show_ui'           => true,
                'show_in_rest'      => true,
                'show_admin_column' => true,
                'rewrite'           => false,
                'query_var'         => false,
                'capabilities'      => ws_get_taxonomy_caps(),
            ]
        );
    }

    // ── 12. Employer Defenses ──────────────────────────────────────────────
    //
    // New in 3.2.0. Flat taxonomy describing the defense standard(s) available
    // to the employer under a law.

    if ( ! taxonomy_exists( 'ws_employer_defense' ) ) {
        register_taxonomy(
            'ws_employer_defense',
            [ 'jx-statute', 'jx-common-law', 'jx-citation', 'jx-construction' ],
            [
                'label'             => 'Employer Defense Standards',
                'labels'            => [
                    'name'          => 'Employer Defense Standards',
                    'singular_name' => 'Employer Defense Standard',
                    'search_items'  => 'Search Employer Defense Standards',
                    'all_items'     => 'All Employer Defense Standards',
                    'edit_item'     => 'Edit Employer Defense Standard',
                    'update_item'   => 'Update Employer Defense Standard',
                    'add_new_item'  => 'Add New Employer Defense Standard',
                    'new_item_name' => 'New Employer Defense Standard Name',
                    'menu_name'     => 'Employer Defenses',
                ],
                'public'            => false,
                'publicly_queryable'=> false,
                'hierarchical'      => false,
                'show_ui'           => true,
                'show_in_rest'      => true,
                'show_admin_column' => true,
                'rewrite'           => false,
                'query_var'         => false,
                'capabilities'      => ws_get_taxonomy_caps(),
            ]
        );
    }

    // ── 13. Assist-Org Type ───────────────────────────────────────────────
    //
    // New in 3.3.0. Single-value classification for ws-assist-org records.
    // Drives the public "Get Help" directory filter. Replaces the ws_aorg_type
    // ACF select field. Terms are seeded via ws_seed_aorg_type_taxonomy().

    if ( ! taxonomy_exists( 'ws_aorg_type' ) ) {
        register_taxonomy(
            'ws_aorg_type',
            [ 'ws-assist-org' ],
            [
                'label'             => 'Organization Types',
                'labels'            => [
                    'name'              => 'Organization Types',
                    'singular_name'     => 'Organization Type',
                    'search_items'      => 'Search Organization Types',
                    'all_items'         => 'All Organization Types',
                    'edit_item'         => 'Edit Organization Type',
                    'update_item'       => 'Update Organization Type',
                    'add_new_item'      => 'Add New Organization Type',
                    'new_item_name'     => 'New Organization Type',
                    'menu_name'         => 'Org Types',
                ],
                'public'            => false,
                'publicly_queryable'=> false,
                'hierarchical'      => false,
                'show_ui'           => true,
                'show_in_rest'      => true,
                'show_admin_column' => true,
                'rewrite'           => false,
                'query_var'         => false,
                'capabilities'      => ws_get_taxonomy_caps(),
            ]
        );
    }

    // ── 14. Employment Sectors ─────────────────────────────────────────────
    //
    // New in 3.7.0. Flat taxonomy classifying the employment sectors served
    // by a ws-assist-org record. Applied to ws-assist-org only.
    // Replaces ws_aorg_employment_sectors ACF checkbox field — enables
    // tax_query filtering for Phase 2 filter panel.

    if ( ! taxonomy_exists( 'ws_employment_sector' ) ) {
        register_taxonomy(
            'ws_employment_sector',
            [ 'jx-statute', 'jx-common-law', 'jx-citation', 'jx-construction', 'ws-agency', 'ag-procedure', 'ws-assist-org' ],
            [
                'label'             => 'Employment Sectors',
                'labels'            => [
                    'name'              => 'Employment Sectors',
                    'singular_name'     => 'Employment Sector',
                    'search_items'      => 'Search Employment Sectors',
                    'all_items'         => 'All Employment Sectors',
                    'edit_item'         => 'Edit Employment Sector',
                    'update_item'       => 'Update Employment Sector',
                    'add_new_item'      => 'Add New Employment Sector',
                    'new_item_name'     => 'New Employment Sector Name',
                    'menu_name'         => 'Employment Sectors',
                ],
                'public'            => false,
                'publicly_queryable'=> false,
                'hierarchical'      => false,
                'show_ui'           => true,
                'show_in_rest'      => true,
                'show_admin_column' => true,
                'rewrite'           => false,
                'query_var'         => false,
                'capabilities'      => ws_get_taxonomy_caps(),
            ]
        );
    }

    // ── 15. Assist-Org Cost Models ─────────────────────────────────────────
    //
    // New in 3.9.0. Flat taxonomy classifying the cost structure of a
    // ws-assist-org record. Applied to ws-assist-org only. Single-value
    // (equivalent to the former select field).
    // Replaces ws_aorg_cost_model ACF select field — enables tax_query
    // filtering for Phase 2 filter panel.

    if ( ! taxonomy_exists( 'ws_aorg_cost_model' ) ) {
        register_taxonomy(
            'ws_aorg_cost_model',
            [ 'ws-assist-org' ],
            [
                'label'             => 'Cost Structures',
                'labels'            => [
                    'name'              => 'Cost Structures',
                    'singular_name'     => 'Cost Structure',
                    'search_items'      => 'Search Cost Structures',
                    'all_items'         => 'All Cost Structures',
                    'edit_item'         => 'Edit Cost Structure',
                    'update_item'       => 'Update Cost Structure',
                    'add_new_item'      => 'Add New Cost Structure',
                    'new_item_name'     => 'New Cost Structure Name',
                    'menu_name'         => 'Cost Models',
                ],
                'public'            => false,
                'publicly_queryable'=> false,
                'hierarchical'      => false,
                'show_ui'           => true,
                'show_in_rest'      => true,
                'show_admin_column' => true,
                'rewrite'           => false,
                'query_var'         => false,
                'capabilities'      => ws_get_taxonomy_caps(),
            ]
        );
    }

    // ── 16. Assist-Org Services ────────────────────────────────────────────
    //
    // New in 3.9.0. Flat taxonomy classifying the services offered by a
    // ws-assist-org record. Applied to ws-assist-org only.
    // Replaces ws_aorg_services ACF checkbox field — enables tax_query
    // filtering for Phase 2 filter panel.
    // 'additional' sentinel term auto-assigned when ws_aorg_additional_services
    // companion field is non-empty (mirrors ws_language pattern).

    if ( ! taxonomy_exists( 'ws_aorg_service' ) ) {
        register_taxonomy(
            'ws_aorg_service',
            [ 'ws-assist-org' ],
            [
                'label'             => 'Provided Services',
                'labels'            => [
                    'name'              => 'Provided Services',
                    'singular_name'     => 'Provided Service',
                    'search_items'      => 'Search Services',
                    'all_items'         => 'All Services',
                    'edit_item'         => 'Edit Service',
                    'update_item'       => 'Update Service',
                    'add_new_item'      => 'Add New Service',
                    'new_item_name'     => 'New Service Name',
                    'menu_name'         => 'Services',
                ],
                'public'            => false,
                'publicly_queryable'=> false,
                'hierarchical'      => false,
                'show_ui'           => true,
                'show_in_rest'      => true,
                'show_admin_column' => true,
                'rewrite'           => false,
                'query_var'         => false,
                'capabilities'      => ws_get_taxonomy_caps(),
            ]
        );
    }

    // ── 17. Procedure Types ────────────────────────────────────────────────
    //
    // New in 3.10.0. Flat taxonomy classifying the purpose of a
    // ag-procedure record. Applied to ag-procedure only.
    // Three stable terms: disclosure, retaliation, both.
    // Replaces ws_proc_type ACF select field — enables tax_query filtering
    // in the Phase 2 filter cascade. Single-value per record (radio UI).
    // Terms are seeded via ws_seed_proc_type_taxonomy().

    if ( ! taxonomy_exists( 'ws_procedure_type' ) ) {
        register_taxonomy(
            'ws_procedure_type',
            [ 'ag-procedure' ],
            [
                'label'             => 'Procedure Types',
                'labels'            => [
                    'name'              => 'Procedure Types',
                    'singular_name'     => 'Procedure Type',
                    'search_items'      => 'Search Procedure Types',
                    'all_items'         => 'All Procedure Types',
                    'edit_item'         => 'Edit Procedure Type',
                    'update_item'       => 'Update Procedure Type',
                    'add_new_item'      => 'Add New Procedure Type',
                    'new_item_name'     => 'New Procedure Type Name',
                    'menu_name'         => 'Procedures',
                ],
                'public'            => false,
                'publicly_queryable'=> false,
                'hierarchical'      => false,
                'show_ui'           => true,
                'show_in_rest'      => true,
                'show_admin_column' => true,
                'rewrite'           => false,
                'query_var'         => false,
                'capabilities'      => ws_get_taxonomy_caps(),
            ]
        );
    }

    // ── 18. Employee Standards ─────────────────────────────────────────────
    //
    // New in 3.12.0. Flat taxonomy for the burden-of-proof standard an employee
    // must meet under a statute. Replaces the freetext employee_standard field.
    // Multiple values permitted per record.
    // Terms seeded via ws_seed_employee_standard_taxonomy().

    if ( ! taxonomy_exists( 'ws_employee_standard' ) ) {
        register_taxonomy(
            'ws_employee_standard',
            [ 'jx-statute', 'jx-common-law', 'jx-citation', 'jx-construction' ],
            [
                'label'             => 'Employee Burden Standards',
                'labels'            => [
                    'name'              => 'Employee Burden Standards',
                    'singular_name'     => 'Employee Burden Standard',
                    'search_items'      => 'Search Employee Standards',
                    'all_items'         => 'All Employee Standards',
                    'edit_item'         => 'Edit Employee Standard',
                    'update_item'       => 'Update Employee Standard',
                    'add_new_item'      => 'Add New Employee Standard',
                    'new_item_name'     => 'New Employee Standard Name',
                    'menu_name'         => 'Employee Standards',
                ],
                'public'            => false,
                'publicly_queryable'=> false,
                'hierarchical'      => false,
                'show_ui'           => true,
                'show_in_rest'      => true,
                'show_admin_column' => true,
                'rewrite'           => false,
                'query_var'         => false,
                'capabilities'      => ws_get_taxonomy_caps(),
            ]
        );
    }

    // ── 19. Protection Scopes ────────────────────────────────────────────────
    //
    // Duplicated from Procedure Type

    if ( ! taxonomy_exists( 'ws_protection_scope' ) ) {
        register_taxonomy(
            'ws_protection_scope',
            [ 'jx-statute', 'jx-common-law', 'jx-citation', 'jx-construction' ],
            [
                'label'             => 'Protection Scope Types',
                'labels'            => [
                    'name'              => 'Protection Scope Types',
                    'singular_name'     => 'Protection Scope Type',
                    'search_items'      => 'Search Protection Scope Types',
                    'all_items'         => 'All Protection Scope Types',
                    'edit_item'         => 'Edit Protection Scope Type',
                    'update_item'       => 'Update Protection Scope Type',
                    'add_new_item'      => 'Add New Protection Scope Type',
                    'new_item_name'     => 'New Protection Scope Type Name',
                    'menu_name'         => 'Protections',
                ],
                'public'            => false,
                'publicly_queryable'=> false,
                'hierarchical'      => false,
                'show_ui'           => true,
                'show_in_rest'      => true,
                'show_admin_column' => true,
                'rewrite'           => false,
                'query_var'         => false,
                'capabilities'      => ws_get_taxonomy_caps(),
            ]
        );
    }

    // ── 20. Protected Actions ────────────────────────────────────────────────
    //
    // 

    if ( ! taxonomy_exists( 'ws_protected_action' ) ) {
        register_taxonomy(
            'ws_protected_action',
            [ 'jx-statute', 'jx-common-law', 'jx-citation', 'jx-construction', ],
            [
                'label'             => 'Protected Actions',
                'labels'            => [
                    'name'              => 'Protected Actions',
                    'singular_name'     => 'Protected Action',
                    'search_items'      => 'Search Protected Actions',
                    'all_items'         => 'All Protected Actions',
                    'parent_item'       => 'Parent Action',
                    'parent_item_colon' => 'Parent Action:',
                    'edit_item'         => 'Edit Protected Action',
                    'update_item'       => 'Update Protected Action',
                    'add_new_item'      => 'Add New Protected Action',
                    'new_item_name'     => 'New Protected Action Name',
                    'menu_name'         => 'Protected Actions',
                ],
                    'public'            => false,
                    'publicly_queryable'=> false,
                    'hierarchical'      => true,
                    'show_ui'           => true,
                    'show_in_rest'      => true,
                    'show_admin_column' => true,
                    'rewrite'           => false,
                    'query_var'         => false,
                    'capabilities'      => ws_get_taxonomy_caps(),
            ]
        );
    }

     
}
add_action( 'init', 'ws_register_taxonomies' );


// ── 21. Legal Recognitions ────────────────────────────────────────────────────
//
// New in 3.15.0. Flat taxonomy for judicially-recognized legal doctrines and
// procedural rules. Presence of a term signals recognition; absence signals
// the doctrine is not recognized or is silent. Replaces scattered *_recognized
// ACF boolean fields. Applied to all four legal record CPTs.
// Terms are seeded via ws_seed_legal_recognition_taxonomy().

add_action( 'init', function() {
    if ( ! taxonomy_exists( 'ws_legal_recognition' ) ) {
        register_taxonomy(
            'ws_legal_recognition',
            [ 'jx-statute', 'jx-common-law', 'jx-citation', 'jx-construction' ],
            [
                'label'             => 'Legal Recognitions',
                'labels'            => [
                    'name'              => 'Legal Recognitions',
                    'singular_name'     => 'Legal Recognition',
                    'search_items'      => 'Search Legal Recognitions',
                    'all_items'         => 'All Legal Recognitions',
                    'edit_item'         => 'Edit Legal Recognition',
                    'update_item'       => 'Update Legal Recognition',
                    'add_new_item'      => 'Add New Legal Recognition',
                    'new_item_name'     => 'New Legal Recognition Name',
                    'menu_name'         => 'Legal Recognitions',
                ],
                'public'            => false,
                'publicly_queryable'=> false,
                'hierarchical'      => false,
                'show_ui'           => true,
                'show_in_rest'      => true,
                'show_admin_column' => true,
                'rewrite'           => false,
                'query_var'         => false,
                'capabilities'      => ws_get_taxonomy_caps(),
            ]
        );
    }
} );

// ── 22. Causation Standards ───────────────────────────────────────────────────
//
// New in 3.15.0. Flat taxonomy for retaliation causation standards. Distinct
// from ws_employee_standard (which covers burden-of-proof/evidentiary weight).
// Causation = the logical relationship between the disclosure and the adverse
// action. Burden = the volume/quality of evidence required.
// Split from ws_employee_standard in 3.15.0.
// Terms are seeded via ws_seed_causation_standard_taxonomy().

add_action( 'init', function() {
    if ( ! taxonomy_exists( 'ws_causation_standard' ) ) {
        register_taxonomy(
            'ws_causation_standard',
            [ 'jx-statute', 'jx-common-law', 'jx-citation', 'jx-construction' ],
            [
                'label'             => 'Causation Standards',
                'labels'            => [
                    'name'              => 'Causation Standards',
                    'singular_name'     => 'Causation Standard',
                    'search_items'      => 'Search Causation Standards',
                    'all_items'         => 'All Causation Standards',
                    'edit_item'         => 'Edit Causation Standard',
                    'update_item'       => 'Update Causation Standard',
                    'add_new_item'      => 'Add New Causation Standard',
                    'new_item_name'     => 'New Causation Standard Name',
                    'menu_name'         => 'Causation Standards',
                ],
                'public'            => false,
                'publicly_queryable'=> false,
                'hierarchical'      => false,
                'show_ui'           => true,
                'show_in_rest'      => true,
                'show_admin_column' => true,
                'rewrite'           => false,
                'query_var'         => false,
                'capabilities'      => ws_get_taxonomy_caps(),
            ]
        );
    }
} );


// ════════════════════════════════════════════════════════════════════════════
// SHARED HELPERS
// ════════════════════════════════════════════════════════════════════════════

/**
 * Helper: Taxonomy Capability Mapping
 *
 * Restricts management to Administrators; allows assignment for other roles.
 */
function ws_get_taxonomy_caps() {
    return [
        'manage_terms' => 'manage_options',
        'edit_terms'   => 'manage_options',
        'delete_terms' => 'manage_options',
        'assign_terms' => 'edit_posts',
    ];
}

/**
 * Helper: Hierarchical Term Seeder
 *
 * Inserts a parent/child term structure into a taxonomy. Skips terms
 * that already exist. Used by seeding functions for hierarchical taxonomies.
 *
 * @param array  $hierarchy  Associative array: parent_slug => [ 'name' => '', 'children' => [] ]
 * @param string $taxonomy   Taxonomy slug.
 */
function ws_bulk_insert_hierarchical( array $hierarchy, string $taxonomy ) {
    foreach ( $hierarchy as $parent_slug => $data ) {
        $existing_parent = term_exists( $parent_slug, $taxonomy );
        if ( ! $existing_parent ) {
            $parent = wp_insert_term( $data['name'], $taxonomy, [ 'slug' => $parent_slug ] );
        } else {
            $parent = is_array( $existing_parent )
                ? $existing_parent
                : [ 'term_id' => $existing_parent ];
        }
        if ( is_wp_error( $parent ) || empty( $data['children'] ) ) {
            continue;
        }
        $parent_id = (int) $parent['term_id'];
        foreach ( $data['children'] as $child_slug => $child_name ) {
            if ( ! term_exists( $child_slug, $taxonomy ) ) {
                wp_insert_term( $child_name, $taxonomy, [
                    'slug'   => $child_slug,
                    'parent' => $parent_id,
                ] );
            }
        }
    }
}


// ════════════════════════════════════════════════════════════════════════════
// SEEDING EXECUTION GATES
//
// Each seeder is individually gated using the Unified Option-Gate Method.
// Key format: ws_seeded_{seeder_slug} / version string: '1.0.0'
// No grouped gates — each taxonomy has its own independent gate.
//
// Gate version bump pattern: to re-seed a taxonomy after a term change,
// increment the version string (e.g. '1.0.0' → '1.0.0') in both the
// gate check and the update_option() call below.
// ════════════════════════════════════════════════════════════════════════════

add_action( 'admin_init', function() {

    if ( get_option( 'ws_seeded_disclosure_type' ) !== '1.0.0' ) {
        ws_seed_disclosure_type_taxonomy();
        update_option( 'ws_seeded_disclosure_type', '1.0.0' );
    }
    if ( get_option( 'ws_seeded_process_type' ) !== '1.0.0' ) {
        ws_seed_process_type_taxonomy();
        update_option( 'ws_seeded_process_type', '1.0.0' );
    }
    if ( get_option( 'ws_seeded_remedy' ) !== '1.0.0' ) {
        ws_seed_remedy_taxonomy();
        update_option( 'ws_seeded_remedy', '1.0.0' );
    }
    if ( get_option( 'ws_seeded_protected_class' ) !== '1.0.0' ) {
        ws_seed_protected_class_taxonomy();
        update_option( 'ws_seeded_protected_class', '1.0.0' );
    }
    if ( get_option( 'ws_seeded_excluded_class' ) !== '1.0.0' ) {
        ws_seed_excluded_class_taxonomy();
        update_option( 'ws_seeded_excluded_class', '1.0.0' );
    }
    if ( get_option( 'ws_seeded_adverse_action' ) !== '1.0.0' ) {
        ws_seed_adverse_action_taxonomy();
        update_option( 'ws_seeded_adverse_action', '1.0.0' );
    }
    if ( get_option( 'ws_seeded_language' ) !== '1.0.0' ) {
        ws_seed_language_taxonomy();
        update_option( 'ws_seeded_language', '1.0.0' );
    }
    if ( get_option( 'ws_seeded_case_stage' ) !== '1.0.0' ) {
        ws_seed_case_stage_taxonomy();
        update_option( 'ws_seeded_case_stage', '1.0.0' );
    }
    if ( get_option( 'ws_seeded_jurisdiction' ) !== '1.0.0' ) {
        ws_seed_jurisdiction_taxonomy();
        update_option( 'ws_seeded_jurisdiction', '1.0.0' );
    }
    if ( get_option( 'ws_seeded_disclosure_target' ) !== '1.0.0' ) {
        ws_seed_disclosure_target_taxonomy();
        update_option( 'ws_seeded_disclosure_target', '1.0.0' );
    }
    if ( get_option( 'ws_seeded_fee_shifting_rule' ) !== '1.0.0' ) {
        ws_seed_fee_shifting_rule_taxonomy();
        update_option( 'ws_seeded_fee_shifting_rule', '1.0.0' );
    }
    if ( get_option( 'ws_seeded_employer_defense' ) !== '1.0.0' ) {
        ws_seed_employer_defense_taxonomy();
        update_option( 'ws_seeded_employer_defense', '1.0.0' );
    }
    if ( get_option( 'ws_seeded_aorg_type' ) !== '1.0.0' ) {
        ws_seed_aorg_type_taxonomy();
        update_option( 'ws_seeded_aorg_type', '1.0.0' );
    }
    if ( get_option( 'ws_seeded_employment_sector' ) !== '1.0.0' ) {
        ws_seed_employment_sector_taxonomy();
        update_option( 'ws_seeded_employment_sector', '1.0.0' );
    }
    if ( get_option( 'ws_seeded_aorg_service' ) !== '1.0.0' ) {
        ws_seed_aorg_service_taxonomy();
        update_option( 'ws_seeded_aorg_service', '1.0.0' );
    }
    if ( get_option( 'ws_seeded_aorg_cost_model' ) !== '1.0.0' ) {
        ws_seed_aorg_cost_model_taxonomy();
        update_option( 'ws_seeded_aorg_cost_model', '1.0.0' );
    }
    if ( get_option( 'ws_seeded_procedure_type' ) !== '1.0.0' ) {
        ws_seed_procedure_type_taxonomy();
        update_option( 'ws_seeded_procedure_type', '1.0.0' );
    }
    if ( get_option( 'ws_seeded_protection_scope' ) !== '1.0.0' ) {
        ws_seed_protection_scope_taxonomy();
        update_option( 'ws_seeded_protection_scope', '1.0.0' );
    }
    if ( get_option( 'ws_seeded_employee_standard' ) !== '1.0.0' ) {
        ws_seed_employee_standard_taxonomy();
        update_option( 'ws_seeded_employee_standard', '1.0.0' );
    }
    if ( get_option( 'ws_seeded_protected_action' ) !== '1.0.0' ) {
        ws_seed_protected_action_taxonomy();
        update_option( 'ws_seeded_protected_action', '1.0.0' );
    }
    if ( get_option( 'ws_seeded_legal_recognition' ) !== '1.0.0' ) {
        ws_seed_legal_recognition_taxonomy();
        update_option( 'ws_seeded_legal_recognition', '1.0.0' );
    }
    if ( get_option( 'ws_seeded_causation_standard' ) !== '1.0.0' ) {
        ws_seed_causation_standard_taxonomy();
        update_option( 'ws_seeded_causation_standard', '1.0.0' );
    }
    if ( get_option( 'ws_seeded_adverse_action' ) !== '1.0.0' ) {
        ws_seed_adverse_action_taxonomy();
        update_option( 'ws_seeded_adverse_action', '1.0.0' );
    }

} );


// ════════════════════════════════════════════════════════════════════════════
// SEEDING FUNCTIONS
// ════════════════════════════════════════════════════════════════════════════

/**
 * Seeds ws_disclosure_type with its hierarchical structure.
 */
function ws_seed_disclosure_type_taxonomy() {
    $hierarchy = [
        'workplace-employment' => [
            'name'     => 'Workplace & Employment',
            'children' => [
                'wage-hour-violations'       => 'Wage & Hour Violations',
                'occupational-health-safety' => 'Occupational Health & Safety',
                'collective-bargaining'      => 'Collective Bargaining Rights',
            ],
        ],
        'financial-corporate' => [
            'name'     => 'Financial & Corporate',
            'children' => [
                'securities-commodities-fraud'  => 'Securities & Commodities Fraud',
                'consumer-financial-protection' => 'Consumer Financial Protection',
                'banking-aml-compliance'        => 'Banking & AML Compliance',
                'shareholder-rights'            => 'Shareholder Rights',
                'tax-evasion-fraud'             => 'Tax Evasion & Fraud',
            ],
        ],
        'government-accountability' => [
            'name'     => 'Government Accountability',
            'children' => [
                'procurement-spending-fraud' => 'Procurement & Spending Fraud',
                'public-corruption-ethics'   => 'Public Corruption & Ethics',
                'election-integrity'         => 'Election Integrity',
                'military-defense-reporting' => 'Military & Defense Reporting',
            ],
        ],
        'public-health-safety' => [
            'name'     => 'Public Health & Safety',
            'children' => [
                'healthcare-medicare-fraud' => 'Healthcare & Medicare Fraud',
                'environmental-protection'  => 'Environmental Protection',
                'food-drug-safety'          => 'Food & Drug Safety',
                'nuclear-energy-safety'     => 'Nuclear & Energy Safety',
                'transportation-safety'     => 'Transportation & Aviation Safety',
                'child-abuse-reporting'     => 'Child Abuse & Exploitation Reporting',
                'patient-abuse-reporting'   => 'Patient Abuse & Neglect Reporting',
            ],
        ],
        'privacy-data-integrity' => [
            'name'     => 'Privacy & Data Integrity',
            'children' => [
                'cybersecurity-disclosure'  => 'Cybersecurity Disclosure',
                'hipaa-patient-privacy'     => 'HIPAA & Patient Privacy',
                'consumer-data-protection'  => 'Consumer Data Protection',
                'education-privacy-ferpa'   => 'Education Privacy (FERPA)',
            ],
        ],
        'national-security' => [
            'name'     => 'National Security',
            'children' => [
                'intelligence-community'       => 'Intelligence Community Reporting',
                'classified-information'       => 'Classified Information Disclosures',
                'export-sanctions-compliance'  => 'Export Controls & Sanctions Compliance',
            ],
        ],
        'general-legal' => [
            'name'     => 'General Legal',
            'children' => [
                'general-wrongdoing'    => 'General Wrongdoing / Violation of Law',
            ],
        ],
    ];
    ws_bulk_insert_hierarchical( $hierarchy, 'ws_disclosure_type' );
}

/**
 * Seeds ws_process_type with its flat term list.
 * 
 */
function ws_seed_process_type_taxonomy() {
    $taxonomy = 'ws_process_type';
    $terms    = [
        'pre-suit-notice'          => 'Pre-Suit Notice',
        'administrative-complaint' => 'Administrative Complaint',
        'civil-lawsuit'            => 'Civil Lawsuit',
        'qui-tam'                  => 'Qui Tam (False Claims)',
        'internal-disclosure'      => 'Internal Disclosure',
        'regulatory-tip'           => 'Regulatory Tip',
        'criminal-referral'        => 'Criminal Referral',
        'state-agency-complaint'   => 'State Agency Complaint',
        'congressional-disclosure' => 'Congressional Disclosure',
        'representative-action'    => 'Representative Action',
        'de-novo-civil'            => 'De Novo Civil Action',
        'arbitration-compelled'    => 'Arbitration Compelled',
    ];
    foreach ( $terms as $slug => $name ) {
        if ( ! term_exists( $slug, $taxonomy ) ) {
            wp_insert_term( $name, $taxonomy, [ 'slug' => $slug ] );
        }
    }
}

/**
 * Seeds ws_remedy with its flat term list.
 * Replaces ws_seed_remedy_taxonomy() for ws_remedy_type (deprecated).
 * 3.11.0: has-details sentinel added.
 * 3.15.0: interim-reinstatement, tax-gross-up added.
 */
function ws_seed_remedy_taxonomy() {
    $taxonomy = 'ws_remedy';
    $terms    = [
        'reinstatement'                   => 'Reinstatement',
        'interim-reinstatement'           => 'Interim / Preliminary Reinstatement',
        'back-pay'                        => 'Back Pay',
        'front-pay'                       => 'Front Pay',
        'front-pay-in-lieu'               => 'Front Pay in Lieu of Reinstatement',
        'double-back-pay'                 => 'Double Back Pay',
        'lost-wages'                      => 'Lost Wages',
        'benefits-restoration'            => 'Benefits Restoration',
        'compensatory-damages'            => 'Compensatory Damages',
        'punitive-damages'                => 'Punitive Damages',
        'treble-damages'                  => 'Treble Damages',
        'civil-penalty'                   => 'Civil Penalty',
        'civil-penalties'                 => 'Civil Penalties (Aggregate)',
        'attorney-fees'                   => 'Attorney Fees',
        'litigation-costs'                => 'Litigation Costs',
        'injunctive-relief'               => 'Injunctive Relief',
        'cease-and-desist'                => 'Cease and Desist Order',
        'expungement-of-personnel-record' => 'Expungement of Personnel Record',
        'bounty-qui-tam-award'            => 'Bounty / Qui Tam Award',
        'whistleblower-fund-award'        => 'Whistleblower Fund Award',
        'non-monetary-relief'             => 'Non-Monetary Relief',
        'neutral-reference'               => 'Neutral / Corrected Reference',
        'attorney-fees-admin'             => 'Attorney Fees (Administrative Phase)',
        'wage-differential'               => 'Wage Differential',
        'liquidated-damages'              => 'Liquidated Damages',
        'consequential-damages'           => 'Consequential / Special Damages',
        'declaratory-relief'              => 'Declaratory Relief',
        'tax-gross-up'                    => 'Tax Gross-Up',
        'has-limits'                      => 'Has Limits/Caps/Standards',
        'has-details'                     => 'Has Details',
    ];
    foreach ( $terms as $slug => $name ) {
        if ( ! term_exists( $slug, $taxonomy ) ) {
            wp_insert_term( $name, $taxonomy, [ 'slug' => $slug ] );
        }
    }
}

/**
 * Seeds ws_protected_class with its hierarchical employee type structure.
 * Replaces ws_seed_coverage_scope_taxonomy() for ws_coverage_scope (deprecated).
 * 3.11.0: has-details sentinel added as flat top-level term.
 * 
 * @todo - should be sync'd with ws_excluded_class table by hook, with
 *         never_excluded and never_protected gates when adding terms to
 *         the respective table.
 */
function ws_seed_protected_class_taxonomy() {
    $hierarchy = [
        'public-sector' => [
            'name'     => 'Public Sector',
            'children' => [
                'federal-employee'     => 'Federal Employee',
                'state-employee'       => 'State Agency Employee',
                'local-gov-staff'      => 'Local / Municipal Employee',
                'k12-education-staff'  => 'K-12 / Higher Ed Staff',
                'military-personnel'   => 'Military Personnel',
            ],
        ],
        'private-sector' => [
            'name'     => 'Private Sector',
            'children' => [
                'corporate-staff'      => 'Corporate / Private Employee',
                'contractor-gig'       => 'Independent Contractor / Gig',
                'non-profit-staff'     => 'Non-Profit Employee',
                'agricultural-worker'  => 'Agricultural Worker',
            ],
        ],
        'healthcare-staff' => [
            'name'     => 'Healthcare & Medical',
            'children' => [
                'clinical-staff'       => 'Clinical (Nurse / Physician)',
                'medical-student'      => 'Medical Student / Intern / Resident',
            ],
        ],
        'special-status' => [
            'name'     => 'Special Status',
            'children' => [
                'job-applicant'           => 'Job Applicant',
                'former-employee'         => 'Former Employee',
                'perceived-whistleblower' => 'Perceived Whistleblower',
                'intern-volunteer'        => 'Intern / Volunteer',
                'qui-tam-relator'         => 'Qui Tam Relator',
            ],
        ],
        'associates-of-whistleblower' => [
            'name'     => 'Associates of Whistleblower',
            'children' => [
                'associates-spouse'           => 'Spouse of Whistleblower',
                'associates-immediate-family' => 'Immediate Family of Whistleblower',
                'associates-household-family' => 'Household Family of Whistleblower',
                'associates-close'            => 'Close Associates of Whistleblower',
            ],
        ],
        'all-sectors' => [
            'name'     => 'All Sectors',
            'children' => [
                'all-employees'        => 'All Employees',
            ],
        ],
        'has-details' => [
            'name'     => 'Has Details',
            'children' => [],
        ],
    ];
    ws_bulk_insert_hierarchical( $hierarchy, 'ws_protected_class' );

}
/**
 * Seeds ws_excluded_class with its hierarchical employee type structure.
 * Duplicate table of ws_protected_class will all-sectors (all-employees)
 * removed.
 * 
 * @todo - should be sync'd with ws_protected_class table by hook, with
 *         never_excluded and never_protected gates when adding terms to
 *         the respective table.
 * 
 */
function ws_seed_excluded_class_taxonomy() {
    $hierarchy = [
        'public-sector' => [
            'name'     => 'Public Sector',
            'children' => [
                'federal-employee'     => 'Federal Employee',
                'state-employee'       => 'State Agency Employee',
                'local-gov-staff'      => 'Local / Municipal Employee',
                'k12-education-staff'  => 'K-12 / Higher Ed Staff',
                'military-personnel'   => 'Military Personnel',
            ],
        ],
        'private-sector' => [
            'name'     => 'Private Sector',
            'children' => [
                'corporate-staff'      => 'Corporate / Private Employee',
                'contractor-gig'       => 'Independent Contractor / Gig',
                'non-profit-staff'     => 'Non-Profit Employee',
                'agricultural-worker'  => 'Agricultural Worker',
            ],
        ],
        'healthcare-staff' => [
            'name'     => 'Healthcare & Medical',
            'children' => [
                'clinical-staff'       => 'Clinical (Nurse / Physician)',
                'medical-student'      => 'Medical Student / Intern / Resident',
            ],
        ],
        'special-status' => [
            'name'     => 'Special Status',
            'children' => [
                'job-applicant'           => 'Job Applicant',
                'former-employee'         => 'Former Employee',
                'perceived-whistleblower' => 'Perceived Whistleblower',
                'intern-volunteer'        => 'Intern / Volunteer',
                'qui-tam-relator'         => 'Qui Tam Relator',
            ],
        ],
        'associates-of-whistleblower' => [
            'name'     => 'Associates of Whistleblower',
            'children' => [
                'associates-spouse'           => 'Spouse of Whistleblower',
                'associates-immediate-family' => 'Immediate Family of Whistleblower',
                'associates-household-family' => 'Household Family of Whistleblower',
                'associates-close'            => 'Close Associates of Whistleblower',
            ],
        ],
        
        'has-details' => [
            'name'     => 'Has Details',
            'children' => [],
        ],
    ];
    ws_bulk_insert_hierarchical( $hierarchy, 'ws_excluded_class' );

}

/**
 * Seeds ws_adverse_action with its flat term list.
 * Replaces ws_seed_retaliation_forms_taxonomy() for ws_retaliation_forms (deprecated).
 * 3.11.0: has-details sentinel added.
 * 3.15.0: retaliatory-litigation, hostile-work-environment, retaliatory-investigation added.
 */
function ws_seed_adverse_action_taxonomy() {
    $taxonomy = 'ws_adverse_action';
    $terms    = [
        'termination'                 => 'Termination',
        'constructive-discharge'      => 'Constructive Discharge',
        'demotion'                    => 'Demotion',
        'suspension'                  => 'Suspension',
        'disciplinary-action'         => 'Disciplinary Action',
        'transfer'                    => 'Transfer',
        'schedule-change'             => 'Schedule Change',
        'benefit-denial'              => 'Benefit Denial',
        'pay-reduction'               => 'Pay / Benefits Reduction',
        'harassment'                  => 'Harassment',
        'hostile-work-environment'    => 'Hostile Work Environment',
        'workplace-isolation'         => 'Workplace Isolation / Ostracism',
        'post-employment-retaliation' => 'Post-Employment Retaliation',
        'blacklisting'                => 'Blacklisting',
        'negative-reference'          => 'Negative Reference',
        'security-clearance-action'   => 'Security Clearance Action',
        'contract-non-renewal'        => 'Contract Non-Renewal',
        'professional-license-action' => 'Professional License Action',
        'privilege-revocation'        => 'Privilege / Access Revocation',
        'immigration-threat'          => 'Immigration-Related Threat',
        'threatened-retaliation'      => 'Threatened Retaliation',
        'retaliatory-investigation'   => 'Retaliatory Investigation / Audit',
        'retaliatory-litigation'      => 'Retaliatory Litigation (SLAPP)',
        'retaliatory-discovery'       => 'Retaliatory Discovery (Litigation Harassment)',
        'has-details'                 => 'Has Details',
    ];
    foreach ( $terms as $slug => $name ) {
        if ( ! term_exists( $slug, $taxonomy ) ) {
            wp_insert_term( $name, $taxonomy, [ 'slug' => $slug ] );
        }
    }
}

/**
 * Seeds ws_language terms.
 * 'additional' is a functional flag — auto-assigned when ws_agency_additional_languages
 * or ws_aorg_additional_languages text fields contain a value.
 */
function ws_seed_language_taxonomy() {
    $taxonomy = 'ws_language';
    $terms    = [
        'english'        => 'English',
        'spanish'        => 'Spanish',
        'mandarin'       => 'Mandarin',
        'cantonese'      => 'Cantonese',
        'french'         => 'French',
        'portuguese'     => 'Portuguese',
        'vietnamese'     => 'Vietnamese',
        'tagalog'        => 'Tagalog',
        'korean'         => 'Korean',
        'arabic'         => 'Arabic',
        'hindi'          => 'Hindi',
        'russian'        => 'Russian',
        'haitian-creole' => 'Haitian Creole',
        'polish'         => 'Polish',
        'japanese'       => 'Japanese',
        'additional'     => 'Additional',
    ];
    foreach ( $terms as $slug => $name ) {
        if ( ! term_exists( $slug, $taxonomy ) ) {
            wp_insert_term( $name, $taxonomy, [ 'slug' => $slug ] );
        }
    }
}

/**
 * Seeds ws_case_stage terms.
 */
function ws_seed_case_stage_taxonomy() {
    $taxonomy = 'ws_case_stage';
    $terms    = [
        'pre-report'         => 'Pre-Report',
        'post-report'        => 'Post-Report',
        'retaliation-active' => 'Retaliation Active',
        'litigation'         => 'Litigation',
        'has-details'        => 'Has Details',
    ];
    foreach ( $terms as $slug => $name ) {
        if ( ! term_exists( $slug, $taxonomy ) ) {
            wp_insert_term( $name, $taxonomy, [ 'slug' => $slug ] );
        }
    }
}

/**
 * Seeds ws_jurisdiction taxonomy with canonical USPS codes.
 * Special case: 'us' => 'Federal' (not 'Us' or 'United States').
 * Includes DC and the five U.S. territories.
 * Display order: Federal first, DC second, states alphabetical, territories alphabetical.
 */
function ws_seed_jurisdiction_taxonomy() {
    $taxonomy = WS_JURISDICTION_TAXONOMY;
    $terms    = [
        'us' => 'Federal', 'dc' => 'District of Columbia', 'al' => 'Alabama', 'ak' => 'Alaska',
        'az' => 'Arizona', 'ar' => 'Arkansas', 'ca' => 'California', 'co' => 'Colorado',
        'ct' => 'Connecticut', 'de' => 'Delaware', 'fl' => 'Florida', 'ga' => 'Georgia',
        'hi' => 'Hawaii', 'id' => 'Idaho', 'il' => 'Illinois', 'in' => 'Indiana', 'ia' => 'Iowa',
        'ks' => 'Kansas', 'ky' => 'Kentucky', 'la' => 'Louisiana' , 'me' => 'Maine' , 'md' => 'Maryland',
        'ma' => 'Massachusetts', 'mi' => 'Michigan', 'mn' => 'Minnesota', 'ms' => 'Mississippi',
        'mo' => 'Missouri', 'mt' => 'Montana', 'ne' => 'Nebraska', 'nv' => 'Nevada', 'nh' => 'New Hampshire',
        'nj' => 'New Jersey', 'nm' => 'New Mexico', 'ny' => 'New York', 'nc' => 'North Carolina',
        'nd' => 'North Dakota', 'oh' => 'Ohio', 'ok' => 'Oklahoma', 'or' => 'Oregon', 'pa' => 'Pennsylvania',
        'ri' => 'Rhode Island', 'sc' => 'South Carolina', 'sd' => 'South Dakota', 'tn' => 'Tennessee',
        'tx' => 'Texas', 'ut' => 'Utah', 'vt' => 'Vermont', 'va' => 'Virginia', 'wa' => 'Washington',
        'wv' => 'West Virginia', 'wi' => 'Wisconsin', 'wy' => 'Wyoming', 'as' => 'American Samoa',
        'gu' => 'Guam', 'mp' => 'Northern Mariana Islands', 'pr' => 'Puerto Rico', 'vi' => 'U.S. Virgin Islands',
    ];
    $order = 1;
    foreach ( $terms as $slug => $name ) {
        if ( ! term_exists( $slug, $taxonomy ) ) {
            $result = wp_insert_term( $name, $taxonomy, [ 'slug' => $slug ] );
            if ( ! is_wp_error( $result ) ) {
                update_term_meta( $result['term_id'], 'display_order', $order );
            }
        } else {
            $existing = get_term_by( 'slug', $slug, $taxonomy );
            if ( $existing ) {
                update_term_meta( $existing->term_id, 'display_order', $order );
            }
        }
        $order++;
    }
}

/**
 * Seeds ws_disclosure_target with its hierarchical recipient structure.
 * New in 3.1.0. Describes who received the disclosure for protection to apply.
 * 3.11.0: has-details sentinel added as flat top-level term.
 */
function ws_seed_disclosure_target_taxonomy() {
    $hierarchy = [
        'internal' => [
            'name'     => 'Internal',
            'children' => [
                'internal-supervisor'     => 'Supervisor / Manager',
                'internal-hr'             => 'Human Resources',
                'internal-compliance'     => 'Compliance / Ethics Hotline',
                'internal-legal'          => 'In-House Legal Counsel',
                'internal-management'     => 'Management (General)',
                'internal-oversight-body' => 'Internal Oversight Office',
            ],
        ],
        'external-agency' => [
            'name'     => 'External: Government Agency',
            'children' => [
                'agency-federal'          => 'Federal Agency',
                'agency-state'            => 'State Agency',
                'agency-local'            => 'Local / Municipal Agency',
                'ig-federal'              => 'Federal Inspector General',
                'ig-state'                => 'State Inspector General',
                'law-enforcement-fed'     => 'Federal Law Enforcement',
                'law-enforcement-state'   => 'State Law Enforcement',
                'external-oversight-body' => 'External Oversight Office',
            ],
        ],
        'legislative' => [
            'name'     => 'Legislative Body',
            'children' => [
                'legislative-federal'   => 'U.S. Congress',
                'legislative-state'     => 'State Legislature',
            ],
        ],
        'judicial' => [
            'name'     => 'Judicial / Legal',
            'children' => [
                'court-filing'          => 'Court Filing',
                'attorney-counsel'      => 'Personal Attorney / Counsel',
            ],
        ],
        'public' => [
            'name'     => 'Public Disclosure',
            'children' => [
                'public-media'          => 'Media / Press',
                'public-nonprofit'      => 'Non-Profit / Advocacy Organization',
                'public-social-media'   => 'Social Media',
            ],
        ],
        'has-details' => [
            'name'     => 'Has Details',
            'children' => [],
        ],
    ];
    ws_bulk_insert_hierarchical( $hierarchy, 'ws_disclosure_target' );

}

/**
 * Seeds ws_fee_shifting_rule with its flat term list.
 * New in 3.1.0.
 */
function ws_seed_fee_shifting_rule_taxonomy() {
    $taxonomy = 'ws_fee_shifting_rule';
    $terms    = [
        'none-american-rule'             => 'None (American Rule)',
        'bilateral-loser-pays'           => 'Bilateral (Loser Pays)',
        'unilateral-pro-plaintiff'       => 'Unilateral (Pro-Plaintiff)',
        'unilateral-pro-defendant'       => 'Unilateral (Pro-Defendant)',
        'prevailing-defendant-bad-faith' => 'Defendant Fees on Bad Faith',
        'discretionary'                  => 'Discretionary',
        'mandatory'                      => 'Mandatory',
        'has-phases'                     => 'Has Phase Specifics',
        'has-details'                    => 'Has Details',
    ];
    foreach ( $terms as $slug => $name ) {
        if ( ! term_exists( $slug, $taxonomy ) ) {
            wp_insert_term( $name, $taxonomy, [ 'slug' => $slug ] );
        }
    }
}


/**
 * Seeds ws_aorg_type with organization type terms.
 *
 * New in 3.3.0. Replaces the ws_aorg_type ACF select field.
 * 'oversight-office' replaces the opaque 'ombudsman' label used in the
 * prior select — "Government Oversight Office" is legible to laypeople.
 */
function ws_seed_aorg_type_taxonomy() {
    $taxonomy = 'ws_aorg_type';
    $terms    = [
        'nonprofit'        => 'Nonprofit Organization',
        'legal-aid'        => 'Legal Aid Clinic',
        'law-firm'         => 'Law Firm',
        'bar-program'      => 'Bar Association Program',
        'advocacy'         => 'Advocacy Organization',
        'oversight-office' => 'Government Oversight Office',
        'union'            => 'Labor Union',
        'mixed'            => 'Mixed Organization Type',
    ];
    foreach ( $terms as $slug => $name ) {
        if ( ! term_exists( $slug, $taxonomy ) ) {
            wp_insert_term( $name, $taxonomy, [ 'slug' => $slug ] );
        }
    }
}

/**
 * Seeds ws_employment_sector with flat sector terms.
 *
 * New in 3.7.0. Replaces ws_aorg_employment_sectors ACF checkbox.
 * 'all-sectors' is used for organizations that serve all worker types.
 */
function ws_seed_employment_sector_taxonomy() {
    $taxonomy = 'ws_employment_sector';
    $terms    = [
        'federal-employee'     => 'Federal Government Employee',
        'state-local-employee' => 'State & Local Government Employee',
        'private-sector'       => 'Private Sector Employee',
        'military-defense'     => 'Military & Defense Contractors',
        'nonprofit-ngo'        => 'Nonprofit & NGO Employee',
        'all-sectors'          => 'All Employment Sectors',
    ];
    foreach ( $terms as $slug => $name ) {
        if ( ! term_exists( $slug, $taxonomy ) ) {
            wp_insert_term( $name, $taxonomy, [ 'slug' => $slug ] );
        }
    }
}

/**
 * Seeds ws_aorg_cost_model with flat cost structure terms.
 * New in 3.9.0. Replaces ws_aorg_cost_model ACF select field.
 */
function ws_seed_aorg_cost_model_taxonomy() {
    $taxonomy = 'ws_aorg_cost_model';
    $terms    = [
        'free'            => 'Free of Charge',
        'pro-bono'        => 'Pro Bono',
        'sliding-scale'   => 'Sliding Scale Fee',
        'contingency'     => 'Contingency Fee',
        'fee-for-service' => 'Fee for Service',
        'unclear'         => 'Model Unclear',
    ];
    foreach ( $terms as $slug => $name ) {
        if ( ! term_exists( $slug, $taxonomy ) ) {
            wp_insert_term( $name, $taxonomy, [ 'slug' => $slug ] );
        }
    }
}

/**
 * Seeds ws_aorg_service with flat service terms.
 * New in 3.9.0. Replaces ws_aorg_services ACF checkbox.
 * 'additional' is the sentinel term for free-text overflow.
 */
function ws_seed_aorg_service_taxonomy() {
    $taxonomy = 'ws_aorg_service';
    $terms    = [
        'legal-rep'     => 'Full Legal Representation',
        'consultation'  => 'Legal Consultation / Advice',
        'referral'      => 'Intake & Referral',
        'doc-review'    => 'Document Review',
        'hotline'       => 'Whistleblower Hotline',
        'retaliation'   => 'Retaliation Defense',
        'financial'     => 'Financial Assistance',
        'advocacy'      => 'Policy Advocacy',
        'media'         => 'Media & Communications Support',
        'mental-health' => 'Mental Health Support',
        'peer-support'  => 'Peer Support',
        'secure-drop'   => 'SecureDrop Intake',
        'additional'    => 'Additional Services',
        'unclear'       => 'Services Unclear',
    ];
    foreach ( $terms as $slug => $name ) {
        if ( ! term_exists( $slug, $taxonomy ) ) {
            wp_insert_term( $name, $taxonomy, [ 'slug' => $slug ] );
        }
    }
}

/**
 * Seeds ws_employer_defense with its flat term structure.
 * New in 3.2.0.
 * 3.11.0: has-details sentinel added.
 */
function ws_seed_employer_defense_taxonomy() {
    $taxonomy = 'ws_employer_defense';
    $terms    = [
        'mixed-motive-defense'              => 'Mixed Motive Defense',
        'same-decision-defense'             => 'Same-Decision Defense',
        'same-decision-clear-convincing'    => 'Same-Decision (Clear and Convincing)',
        'legitimate-non-retaliatory-reason' => 'Legitimate Non-Retaliatory Reason',
        'after-acquired-evidence'           => 'After-Acquired Evidence (Specific Non-Retaliatory)',
        'good-faith-compliance'             => 'Good-Faith Compliance',
        'independent-contractor-defense'    => 'Independent Contractor Defense',
        'statutory-exception-claim'         => 'Statutory Exception Claim',
        'no-protected-activity'             => 'Disclosure was not Protected',
        'no-jurisdiction'                   => 'Disclosure out of Scope (No Jurisdiction)',
        'has-details'                       => 'Has Details',
    ];
    foreach ( $terms as $slug => $name ) {
        if ( ! term_exists( $slug, $taxonomy ) ) {
            wp_insert_term( $name, $taxonomy, [ 'slug' => $slug ] );
        }
    }
}

/**
 * Seeds ws_procedure_type with its three flat terms.
 *
 * New in 3.10.0. Replaces the ws_proc_type ACF select field on ag-procedure.
 * These three terms are stable — the set is not expected to grow.
 *
 *   disclosure  — procedure for reporting wrongdoing to the agency
 *   retaliation — procedure for filing a complaint after adverse action
 *   both        — single procedure that covers both disclosure and retaliation
 */
function ws_seed_procedure_type_taxonomy() {
    $taxonomy = 'ws_procedure_type';
    $terms    = [
        'disclosure'  => 'Disclosure',
        'retaliation' => 'Retaliation',
        'both'        => 'Both',
    ];
    foreach ( $terms as $slug => $name ) {
        if ( ! term_exists( $slug, $taxonomy ) ) {
            wp_insert_term( $name, $taxonomy, [ 'slug' => $slug ] );
        }
    }

}

/**
 * Seeds ws_protection_scope with its three flat terms.
 * Duplicate of ws_procedure_type
 *
 *   disclosure  — legal protection for reporting wrongdoing
 *   retaliation — legal protection from adverse actions after reporting
 *   both        — legal protection for both disclosure and retaliation
 */
function ws_seed_protection_scope_taxonomy() {
    $taxonomy = 'ws_protection_scope';
    $terms    = [
        'disclosure'  => 'Disclosure',
        'retaliation' => 'Retaliation',
        'both'        => 'Both',
    ];
    foreach ( $terms as $slug => $name ) {
        if ( ! term_exists( $slug, $taxonomy ) ) {
            wp_insert_term( $name, $taxonomy, [ 'slug' => $slug ] );
        }
    }
}

/**
 * Seeds ws_employee_standard with its flat term list.
 *
 * New in 3.12.0. Replaces the freetext employee_standard fields in ACFs.
 * Covers evidentiary burden-of-proof standards only — the volume/quality of
 * evidence required. Causation standards (the logical link between disclosure
 * and adverse action) moved to ws_causation_standard in 3.15.0.
 * has-details sentinel signals a companion ACF freetext field holds a standard
 * not covered by the registered slugs.
 * 3.15.0: causation-but-for, causation-any-consideration, causation-contributing-factor
 *         removed and moved to ws_causation_standard.
 */
function ws_seed_employee_standard_taxonomy() {
    $taxonomy = 'ws_employee_standard';
    $terms    = [
        'contributing-factor'  => 'Contributing Factor',
        'motivating-factor'    => 'Motivating Factor',
        'substantial-factor'   => 'Substantial Factor',
        'but-for'              => 'But-For (Burden of Proof)',
        'preponderance'        => 'Preponderance of the Evidence',
        'clear-and-convincing' => 'Clear and Convincing Evidence',
        'reasonable-belief'    => 'Reasonable Belief Standard',
        'has-details'          => 'Has Details',
    ];
    foreach ( $terms as $slug => $name ) {
        if ( ! term_exists( $slug, $taxonomy ) ) {
            wp_insert_term( $name, $taxonomy, [ 'slug' => $slug ] );
        }
    }
}

/**
 * Seeds ws_protected_action with its hierarchical term structure.
 *
 * Two parent clauses per Title VII §704(a) and state analogs:
 * - Opposition Clause: opposing, resisting, or refusing; typically requires
 *   good-faith reasonable belief.
 * - Participation Clause: participating in proceedings or investigations;
 *   typically broader protection; good-faith requirement may not apply.
 *
 * 3.16.0: Hierarchy confirmed. opposition-clause, participation-clause, and
 *         child terms (opposing-practice, internal-objection, refusal-to-participate,
 *         filing-complaint, testifying, assisting-whistleblower, participation-support)
 *         already present. Gate bumped to 1.0.0 — re-seeding confirms structure.
 */
function ws_seed_protected_action_taxonomy() {
   $hierarchy = [
        'opposition-clause' => [
            'name'     => 'Opposition Clause',
            'children' => [
                'opposing-practice'       => 'Opposing Practice',
                'internal-objection'      => 'Internal Objection',
                'refusal-to-participate'  => 'Refusal to Participate',
            ],
        ],
        'participation-clause' => [
            'name'     => 'Participation Clause',
            'children' => [
                'filing-complaint'        => 'Filing Complaint',
                'testifying'              => 'Testifying',
                'assisting-whistleblower' => 'Assisting Whistleblower',
                'participation-support'   => 'Participation Support',
            ],
        ],
        'attempted-reporting' => [
            'name'     => 'Attempted Reporting',
            'children' => [],
        ],
        'concerted-activity'  => [
            'name'     => 'Concerted Activity',
            'children' => [],
        ],

    ];
    ws_bulk_insert_hierarchical( $hierarchy, 'ws_protected_action' );

}


/**
 * Seeds ws_legal_recognition with its flat term list.
 *
 * New in 3.15.0. Replaces scattered *_recognized ACF boolean fields.
 * Presence of a term signals the doctrine/rule is recognized in this jurisdiction.
 * Absence signals the doctrine is not recognized or the statute is silent.
 * Terms that have companion *_context fields are noted below.
 * 
* Used for bool-state values of Legal Recognitions where true when:
 *  - Specified   — statute explicitly names or enumerates something
 *  - Recognized  — judicial doctrine courts have affirmatively acknowledged
 *  - Required    — mandatory obligation; non-compliance typically defeats the claim
 *  - Applies     — statutory condition that operates by force of law when triggered
 *  - Available   — mechanism or remedy that may be invoked but is not automatic
 *  - Permitted   — right expressly allowed; cannot be waived or procedurally blocked
 *  - Barred      — doctrine, action, or evidence explicitly excluded by law or rule
 *  - Prohibited  — conduct expressly forbidden; violation triggers statutory liability
 *  - Present     — clause or provision exists without implying judicial affirmation
 *  - Sufficient  — condition independently meets the threshold for protection to attach
 *  
 * Recognized Retaliation Doctrines — REMOVED:
 *  - 'constructive-discharge'   — "Recognized" is true when present in adverse_actions
 *  - 'anticipatory-retaliation' — "Recognized" is true when present in adverse_actions
 * 
 */
function ws_seed_legal_recognition_taxonomy() {
    $taxonomy = 'ws_legal_recognition';
    $terms    = [
        // Effective Date                                                                               // ───── # Effective Date Tab ────────────────────────────────────────────────
        'retroactive-date'                    => 'Retroactive Date Specified',                          // + retro_context + retro_date
        // Classifications                                                                              // ───── # Classifications Tab ───────────────────────────────────────────────
        // = NOTE = `legal_recognitions` appears at top of Classifications Tab                          // =>>> NOTE =>>> `legal_recognitions` appears at top of Classifications Tab
        'protected-action'                    => 'Protected Action Specified',                          // + protected_action_context  + protected_actions + protected_action_standard + protected_action_source
        'excluded-class'                      => 'Excluded Class Specified',                            // + excluded_class_context    + excluded_classes
        'manager-rule-exclusion'              => 'Manager Rule / Duty Speech Exclusion Applies',        // + manager_rule_context
        'public-concern-required'             => 'Public Concern Requirement Applies',                  // + public_concern_context
        'bad-faith-exclusion'                 => 'Bad Faith / Knowingly False Exclusion Applies',       // + bad_faith_context
        'anonymity-protection'                => 'Anonymity / Confidentiality Protection Recognized',   // + anonymity_context
        // Statute of Limitations & Procedural                                                          // ───── # Statute of Limitations And Thresholds Tab ─────────────────────────
        'statutory-tolling'                   => 'Statutory Tolling Specified',                         // + statutory_tolling_context
        'equitable-tolling'                   => 'Equitable Tolling Recognized',                        // + equitable_tolling_context
        'amended-claim'                       => 'Amended Claim / Relation Back Recognized',            // + amended_claim_context
        'exhaustion-required'                 => 'Exhaustion Required',                                 // + exhaustion_context
        'pre-filing-notice'                   => 'Pre-Filing Notice Required',                          // + filing_notice_context
        'statutory-preclusion'                => 'Statutory Preclusion Applies',                        // + preclusion_context
        // Retaliation                                                                                  // ───── # Retaliation Tab ───────────────────────────────────────────────────
        'cats-paw-liability'                  => 'Cat\'s Paw Liability Recognized',                     // + cats_paw_context
        'third-party-retaliation'             => 'Third-Party Retaliation Prohibited',                  // + third_party_retaliation_context
        // Process & Remedies                                                                           // ───── # Process & Remedies Tab ────────────────────────────────────────────
        'private-right-of-action'             => 'Private Right of Action Available',                   // + private_roa_context
        'jury-trial'                          => 'Jury Trial Available',                                // =>>> NOTE =>>> invalid term without 'private-right-of-action' also present.  // + jury_trial_context + jury_trial_scope
        'preliminary-reinstatement'           => 'Preliminary / Interim Reinstatement Available',       // + preliminary_reinstatement_context
        // Waiver & Scope                                                                               // ───── # Waiver & Scope Tab ────────────────────────────────────────────────
        'contractual-waiver'                  => 'Contractual Waiver Recognized',                       // =>>> NOTE =>>> invalid term if 'civil_action_waiver_scope' is set to 'anti'. // + contractual_waiver_context + contractual_waiver_scope
        'nda-limitations'                      => 'NDA / Non-Disparagement Limitations Recognized',     // + nda_limits_context
        'anti-gag-provision'                  => 'Anti-Gag Provision Recognized',                       // + anti_gag_context
        'no-retaliatory-evidence'             => 'Retaliatory Evidence Barred',                         // + no_retaliatory_evidence_context
        'stay-of-disciplinary-action'         => 'Stay of Disciplinary Action Available',               // + stay_context
        'anti-slapp-protection'               => 'Anti-SLAPP Protection Applies',                       // + anti_slapp_protection_context
        'confidential-settlement-restriction' => 'Confidential Settlement Restriction Applies',         // + settlement_restriction_context
        'successor-liability'                 => 'Successor Employer Liability Recognized',             // + successor_liability_context
        'extraterritorial-coverage'           => 'Extraterritorial Coverage Recognized',                // + extraterritorial_context
        'employer-knowledge'                  => 'Employer Knowledge Element Required',                 // + employer_knowledge_context
        // Without Context                                                                              // ───── # Without Context (no Tab) ──────────────────────────────────────────
        'catch-all-protection'                => 'Catch-All Protection Clause Present',                 // (no companion)
        'internal-only-sufficient'            => 'Internal-Only Disclosure Sufficient',                 // (no companion)
        'trade-secret-immunity'               => 'Trade Secret Immunity Recognized',                    // (no companion)
        'continuing-violation'                => 'Continuing Violation Doctrine Recognized',            // (no companion)
        'individual-liability'                => 'Individual Liability Available',                      // (no companion)
        'class-action'                        => 'Class / Collective Action Permitted',                 // (no companion)

    ];
    foreach ( $terms as $slug => $name ) {
        if ( ! term_exists( $slug, $taxonomy ) ) {
            wp_insert_term( $name, $taxonomy, [ 'slug' => $slug ] );
        }
    }
}

/**
 * Seeds ws_causation_standard with its flat term list.
 *
 * New in 3.15.0. Split from ws_employee_standard.
 * Covers the causal link required between the disclosure and the adverse action.
 * Distinct from ws_employee_standard which covers evidentiary burden-of-proof
 * standards (the volume/quality of evidence required).
 * has-details sentinel signals a companion ACF freetext field holds a standard
 * not covered by the registered slugs.
 *
 * 3.16.0: contributing-factor-but-for-backstop and substantial-motivating-factor added.
 */
function ws_seed_causation_standard_taxonomy() {
    $taxonomy = 'ws_causation_standard';
    $terms    = [
        'causation-but-for'                     => 'But-For Causation Standard',
        'causation-any-consideration'           => 'Any Consideration Causation Standard',
        'causation-contributing-factor'         => 'Contributing Factor Causation Standard',
        'contributing-factor-but-for-backstop'  => 'Contributing Factor (But-For Backstop)',
        'causation-motivating-factor'           => 'Motivating Factor Causation Standard',
        'substantial-motivating-factor'         => 'Substantial Motivating Factor Standard',
        'causation-substantial-factor'          => 'Substantial Factor Causation Standard',
        'causation-proximate-cause'             => 'Proximate Cause Standard',
        'has-details'                           => 'Has Details',
    ];
    foreach ( $terms as $slug => $name ) {
        if ( ! term_exists( $slug, $taxonomy ) ) {
            wp_insert_term( $name, $taxonomy, [ 'slug' => $slug ] );
        }
    }
}
