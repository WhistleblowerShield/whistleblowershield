<?php
/**
 * acf-ag-procedures.php
 *
 * Registers ACF Pro fields for the `ag-procedure` CPT.
 *
 * PURPOSE
 * -------
 * Provides structured metadata for agency procedure records used to describe
 * reporting and retaliation workflows, routing details, and submission methods.
 * This group is procedure-specific and focused on procedure intake/use details.
 *
 * GROUP: group_ag_procedure_metadata
 *
 * FIELD SUMMARY
 * -------------
 * Procedure Identity tab:
 *   ws_ag_procedure_agency_id                Parent Agency (post_object, required)
 *   ws_ag_procedure_name                     Procedure Name (text, required)
 *   ws_ag_procedure_type                     Procedure Type (radio, required)
 *   ws_ag_procedure_jurisdictions            Jurisdiction(s) (multi_select, optional)
 *   ws_ag_procedure_disclosure_types         Disclosure Types Covered (multi_select, optional)
 *   ws_ag_procedure_statute_ids              Related Statutes (relationship, optional)
 *   ws_ag_procedure_comlaw_ids               Related Common Laws (relationship, optional)
 *
 * Filing Details tab:
 *   ws_ag_procedure_entry_point              Entry Point (select, optional)
 *   ws_ag_procedure_intake_url               Intake / Form URL (url, optional)
 *   ws_ag_procedure_phone                    Direct Phone Number (text, optional)
 *   ws_ag_procedure_identity_policy          Identity Policy (select, required)
 *   ws_ag_procedure_intake_only              Intake Only — Does Not Investigate (true_false, optional)
 *   ws_ag_procedure_deadline_days            Filing Deadline (Days) (number, optional)
 *   ws_ag_procedure_deadline_clock_start     Deadline Clock Start (select, conditional)
 *   ws_ag_procedure_has_prerequisites        Prerequisites Required Before Filing (true_false, optional)
 *   ws_ag_procedure_prerequisites_details    Prerequisites — Details (textarea, conditional)
 *
 * Plain English tab:
 *   ws_ag_procedure_walkthrough_wysiwyg      Step-by-Step Walkthrough (wysiwyg, optional)
 *   ws_ag_procedure_exclusivity_details      Mutual Exclusivity Details (textarea, optional)
 *
 * Last Verified tab:
 *   ws_ag_procedure_last_reviewed            Last Verified Date (date_picker, optional)
 *
 * Admin Review tab:
 *   ws_ag_procedure_parent_override          Parent (statute/common law) Link Override (true_false, optional)
 * 
 * Hidden fields:
 *   _ws_ag_procedure_parent_ids              Merged Related Statutes and Common Laws (relationship, hidden)
 *
 * SHARED WORKFLOW GROUPS
 * ----------------------
 *   - group_stamp_metadata (acf-stamp-fields.php, menu_order 90)
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
 * PLAIN ENGLISH
 * -------------
 * Procedures use ws_ag_procedure_walkthrough_wysiwyg (registered in this file)
 * as their plain-english content. The central acf-plain-english-fields.php group
 * is NOT applied to this CPT -- the walkthrough IS the plain-english layer.
 *
 * PARENT AGENCY PRE-FILL
 * ----------------------
 * When a new procedure is created from the agency navigation box, the URL
 * carries ?agency_id={post_id}. The acf/load_value hook below pre-fills
 * ws_ag_procedure_agency_id on auto-draft posts, matching the pattern used by
 * ws_jx_construction_prefill_statute_id() in acf-jx-constructions.php.
 *
 * 
 * @package    WhistleblowerShield
 * @since      3.9.0
 * @version    3.17.0
 * @author     Whistleblower Shield
 * @link       https://whistleblowershield.org
 * @copyright  Copyright (c) Whistleblower Shield
 *
 */

defined( 'ABSPATH' ) || exit;


// ── Field group registration ──────────────────────────────────────────────────

add_action( 'acf/init', 'ws_register_acf_ag_procedures' );

function ws_register_acf_ag_procedures() {

    if ( ! function_exists( 'acf_add_local_field_group' ) ) {
        return;
    }

    acf_add_local_field_group( [

        'key'                   => 'group_ag_procedure_metadata',
        'title'                 => 'Procedure Details',
        'menu_order'            => 0,
        'position'              => 'normal',
        'style'                 => 'default',
        'label_placement'       => 'top',
        'instruction_placement' => 'label',
        'active'                => true,

        'location' => [ [ [
            'param'    => 'post_type',
            'operator' => '==',
            'value'    => 'ag-procedure',
        ] ] ],

        'fields' => [

            // ── Tab: Procedure Identity ───────────────────────────────────

            [
                'key'   => 'field_ag_procedure_identity_tab',
                'label' => 'Procedure Identity',
                'type'  => 'tab',
            ],

            // ── Parent Agency ─────────────────────────────────────────────
            //
            // Links this procedure to its owning agency. Pre-filled from
            // the ?agency_id= URL parameter when created via the agency
            // navigation box — see ws_ag_procedure_prefill_agency_id() below.

            [
                'key'           => 'field_ag_procedure_agency_id',
                'label'         => 'Parent Agency',
                'name'          => 'ws_ag_procedure_agency_id',
                'type'          => 'post_object',
                'instructions'  => 'The agency this procedure belongs to.',
                'required'      => 1,
                'post_type'     => [ 'ws-agency' ],
                'return_format' => 'id',
                'multiple'      => 0,
                'allow_null'    => 0,
                'ui'            => 1,
            ],
            [
                'key'           => 'filed_ag_procedure_name',
                'label'         => 'Procedure Name',
                'name'          => 'ws_ag_procedure_name',
                'type'          => 'text',
                'instructions'  => 'The name of this procedure (e.g. "SEC Fraud Claim Intake").',
                'required'      => 1,
            ],
            [
                'key'           => 'field_ag_procedure_type',
                'label'         => 'Procedure Type',
                'name'          => 'ws_ag_procedure_type',
                'type'          => 'taxonomy',
                'taxonomy'      => 'ws_procedure_type',
                'field_type'    => 'radio',
                'instructions'  => 'Disclosure = reporting wrongdoing. Retaliation = filing a complaint after adverse action. Both = single procedure covers both.',
                'required'      => 1,
                'add_term'      => 0,
                'save_terms'    => 1,
                'load_terms'    => 1,
                'return_format' => 'id',
                'allow_null'    => 0,
            ],

            // ── Jurisdiction(s) ───────────────────────────────────────────
            //
            // Defaults to the parent agency's jurisdictions. Override only
            // when this procedure covers a narrower geographic scope.
            // save_terms=1 writes term assignments directly; load_terms=1
            // reflects current taxonomy state in the admin UI.

            [
                'key'           => 'field_ag_procedure_jurisdictions',
                'label'         => 'Jurisdiction(s)',
                'name'          => 'ws_ag_procedure_jurisdictions',
                'type'          => 'taxonomy',
                'taxonomy'      => WS_JURISDICTION_TAXONOMY,
                'field_type'    => 'multi_select',
                'instructions'  => 'Jurisdictions this procedure applies to. Defaults to the parent agency jurisdictions — override only when a procedure covers a narrower scope.',
                'add_term'      => 0,
                'save_terms'    => 1,
                'load_terms'    => 1,
                'return_format' => 'id',
                'allow_null'    => 1,
            ],
            [
                'key'           => 'field_ag_procedure_disclosure_types',
                'label'         => 'Disclosure Types Covered',
                'name'          => 'ws_ag_procedure_disclosure_types',
                'type'          => 'taxonomy',
                'taxonomy'      => 'ws_disclosure_type',
                'field_type'    => 'multi_select',
                'instructions'  => 'Which disclosure categories this specific procedure accepts. May be narrower than the parent agency\'s overall disclosure categories.',
                'add_term'      => 0,
                'save_terms'    => 1,
                'load_terms'    => 1,
                'return_format' => 'id',
                'allow_null'    => 1,
            ],

            // ── Related Statutes ──────────────────────────────────────────
            //
            // Authoritative list of jx-statute posts this procedure operates
            // under. The relationship picker is auto-scoped via
            // ws_ag_procedure_scope_parent_picker() (below) — it pre-filters to
            // statutes matching this procedure's jurisdiction and disclosure
            // types so the editor sees a relevant subset. Manual taxonomy
            // filter UI (jurisdiction + disclosure type) is also available
            // in the picker for edge cases.
            //
            // Statute links are validated on save by admin-procedure-watch.php:
            // a hard mismatch (zero disclosure-type intersection) demotes the
            // procedure to draft and sets ws_ag_procedure_parent_flagged. The Admin
            // Review tab's override field allows admins to publish despite a
            // known mismatch when the link is intentionally unconventional.

            [
                'key'          => 'field_ag_procedure_statute_ids',
                'label'        => 'Related Statutes',
                'name'         => 'ws_ag_procedure_statute_ids', // merges on post_save with common law IDs in _ws_ag_procedure_parent_ids
                'type'         => 'relationship',
                'instructions' => 'Statutes this procedure specifically operates under. The picker is pre-filtered by this procedure\'s jurisdiction and disclosure types. Use the taxonomy dropdowns to refine further if needed.',
                'post_type'    => [ 'jx-statute' ],
                // 'search' provides a text box; 'taxonomy' adds dropdown filters
                // for ws_jurisdiction and ws_disclosure_type so editors can
                // narrow the list before selecting. Auto-scoping (see hook below)
                // applies the procedure\'s own taxonomy scope automatically.
                'filters'      => [ 'search', 'taxonomy' ],
                'taxonomy'     => [ WS_JURISDICTION_TAXONOMY, 'ws_disclosure_type' ],
                'min'          => 0,
                'max'          => 0,
                'return_format'=> 'id',
                'allow_null'   => 1,
                'multiple'     => 1,
                'elements'     => [],
            ],

            // ── Related Common Law ──────────────────────────────────────────
            //
            // Authoritative list of jx-common-law posts this procedure operates
            // under. The relationship picker is auto-scoped via
            // ws_ag_procedure_scope_parent_picker() (below) — it pre-filters to
            // common law entries matching this procedure's jurisdiction and disclosure
            // types so the editor sees a relevant subset. Manual taxonomy
            // filter UI (jurisdiction + disclosure type) is also available
            // in the picker for edge cases.
            //
            // Common Law links are validated on save by admin-procedure-watch.php:
            // a hard mismatch (zero disclosure-type intersection) demotes the
            // procedure to draft and sets ws_ag_procedure_parent_flagged. The Admin
            // Review tab's override field allows admins to publish despite a
            // known mismatch when the link is intentionally unconventional.

            [
                'key'          => 'field_ag_procedure_comlaw_ids',
                'label'        => 'Related Common Law',
                'name'         => 'ws_ag_procedure_comlaw_ids', // merges on post_save with statute IDs in _ws_ag_procedure_parent_ids
                'type'         => 'relationship',
                'instructions' => 'Common Law entries this procedure specifically operates under. The picker is pre-filtered by this procedure\'s jurisdiction and disclosure types. Use the taxonomy dropdowns to refine further if needed.',
                'post_type'    => [ 'jx-common-law' ],
                // 'search' provides a text box; 'taxonomy' adds dropdown filters
                // for ws_jurisdiction and ws_disclosure_type so editors can
                // narrow the list before selecting. Auto-scoping (see hook below)
                // applies the procedure\'s own taxonomy scope automatically.
                'filters'      => [ 'search', 'taxonomy' ],
                'taxonomy'     => [ WS_JURISDICTION_TAXONOMY, 'ws_disclosure_type' ],
                'min'          => 0,
                'max'          => 0,
                'return_format'=> 'id',
                'allow_null'   => 1,
                'multiple'     => 1,
                'elements'     => [],
            ],

            // ── Tab: Filing Details ───────────────────────────────────────

            [
                'key'   => 'field_ag_procedure_filing_details_tab',
                'label' => 'Filing Details',
                'type'  => 'tab',
            ],
            [
                'key'           => 'field_ag_procedure_employment_sectors',
                'label'         => 'Employment Sectors',
                'name'          => 'ws_ag_procedure_employment_sectors',
                'type'          => 'taxonomy',
                'taxonomy'      => 'ws_employment_sector',
                'field_type'    => 'multi_select',
                'instructions'  => 'Employment sectors this specific procedure applies to.',
                'required'      => 0,
                'add_term'      => 0,
                'save_terms'    => 1,
                'load_terms'    => 1,
                'return_format' => 'id',
            ],
            [
                'key'          => 'field_ag_procedure_entry_point',
                'label'        => 'Entry Point',
                'name'         => 'ws_ag_procedure_entry_point',
                'type'         => 'select',
                'instructions' => 'How the whistleblower initiates this procedure.',
                'choices'      => [
                    'online'    => 'Online — Web Form or Portal',
                    'mail'      => 'Mail — Written Submission',
                    'phone'     => 'Phone — Hotline or Direct Call',
                    'in_person' => 'In Person — Regional Office',
                    'multi'     => 'Multiple — More Than One Option',
                ],
                'allow_null'    => 1,
                'ui'            => 1,
                'return_format' => 'value',
            ],
            [
                'key'          => 'field_ag_procedure_intake_url',
                'label'        => 'Intake / Form URL',
                'name'         => 'ws_ag_procedure_intake_url',
                'type'         => 'url',
                'instructions' => 'Direct link to the intake form or portal specific to this procedure. Overrides the parent agency\'s general reporting URL for this procedure.',
            ],
            [
                'key'          => 'field_ag_procedure_phone',
                'label'        => 'Direct Phone Number',
                'name'         => 'ws_ag_procedure_phone',
                'type'         => 'text',
                'instructions' => 'Specific hotline or office number for this procedure, if different from the parent agency\'s main hotline.',
            ],
            [
                'key'           => 'field_ag_procedure_identity_policy',
                'label'         => 'Identity Policy',
                'name'          => 'ws_ag_procedure_identity_policy',
                'type'          => 'select',
                'instructions'  => 'Anonymous = agency never learns your identity. Confidential = agency knows but will not disclose. Identified = your identity is required to proceed.',
                'required'      => 1,
                'choices'       => [
                    'anonymous'    => 'Anonymous — Identity Never Disclosed',
                    'confidential' => 'Confidential — Identity Protected, Known to Agency',
                    'identified'   => 'Identified — Identity Required',
                    'varies'       => 'Varies — Depends on Circumstances',
                ],
                'allow_null'    => 0,
                'ui'            => 1,
                'default_value' => 'confidential',
                'return_format' => 'value',
            ],
            [
                'key'           => 'field_ag_procedure_intake_only',
                'label'         => 'Intake Only — Does Not Investigate',
                'name'          => 'ws_ag_procedure_intake_only',
                'type'          => 'true_false',
                'instructions'  => 'Enable if this agency only receives and refers — it does not investigate or adjudicate complaints filed under this procedure. Displayed prominently to prevent users from filing here expecting enforcement action.',
                'ui'            => 1,
                'ui_on_text'    => 'Yes',
                'ui_off_text'   => 'No',
                'default_value' => 0,
            ],
            [
                'key'           => 'field_ag_procedure_deadline_days',
                'label'         => 'Filing Deadline (Days)',
                'name'          => 'ws_ag_procedure_deadline_days',
                'type'          => 'number',
                'instructions'  => 'Statutory filing deadline in calendar days. Enter 0 if no deadline applies or deadline is unknown.',
                'default_value' => 0,
                'min'           => 0,
                'step'          => 1,
            ],

            // ── Deadline Clock Start ──────────────────────────────────────
            //
            // Only relevant when a deadline is set. Conditional on
            // filed_ag_procedure_deadline_days being greater than 0.

            [
                'key'          => 'field_ag_procedure_deadline_clock_start',
                'label'        => 'Deadline Clock Start',
                'name'         => 'ws_ag_procedure_deadline_clock_start',
                'type'         => 'select',
                'instructions' => 'The event that starts the filing deadline clock.',
                'choices'      => [
                    'adverse_action' => 'Date of Adverse Action',
                    'knowledge'      => 'Date Complainant Learned of Action',
                    'last_act'       => 'Date of Last Act in a Pattern',
                    'varies'         => 'Varies — See Plain English Walkthrough',
                ],
                'allow_null'    => 1,
                'ui'            => 1,
                'return_format' => 'value',
                'conditional_logic' => [ [ [
                    'field'    => 'field_ag_procedure_deadline_days',
                    'operator' => '>',
                    'value'    => '0',
                ] ] ],
            ],
            [
                'key'           => 'field_ag_procedure_has_prerequisites',
                'label'         => 'Prerequisites Required Before Filing',
                'name'          => 'ws_ag_procedure_has_prerequisites',
                'type'          => 'true_false',
                'instructions'  => 'Enable if the filer must exhaust internal remedies or satisfy other conditions before using this procedure.',
                'ui'            => 1,
                'ui_on_text'    => 'Yes',
                'ui_off_text'   => 'No',
                'default_value' => 0,
            ],
            [
                'key'          => 'field_ag_procedure_prerequisites_details',
                'label'        => 'Prerequisites — Details',
                'name'         => 'ws_ag_procedure_prerequisites_details',
                'type'         => 'textarea',
                'rows'         => 3,
                'instructions' => 'Briefly describe what prerequisites must be satisfied before filing.',
                'conditional_logic' => [ [ [
                    'field'    => 'field_ag_procedure_has_prerequisites',
                    'operator' => '==',
                    'value'    => '1',
                ] ] ],
            ],

            // ── Tab: Plain English ────────────────────────────────────────

            [
                'key'   => 'field_ag_procedure_plain_english_tab',
                'label' => 'Plain English',
                'type'  => 'tab',
            ],
            [
                'key'          => 'field_ag_procedure_walkthrough_wysiwyg',
                'label'        => 'Step-by-Step Walkthrough',
                'name'         => 'ws_ag_procedure_walkthrough_wysiwyg',
                'type'         => 'wysiwyg',
                'instructions' => 'Plain-English guidance for a whistleblower using this procedure. Cover: what to prepare, how to submit, what happens after filing, and realistic timeline expectations. This is the core "what do I do next?" answer.',
                'tabs'         => 'all',
                'toolbar'      => 'full',
                'media_upload' => 0,
            ],
            [
                'key'          => 'field_ag_procedure_exclusivity_details',
                'label'        => 'Mutual Exclusivity Details',
                'name'         => 'ws_ag_procedure_exclusivity_details',
                'type'         => 'textarea',
                'rows'         => 4,
                'instructions' => 'Describe any remedies or procedures the filer may waive or foreclose by using this procedure. Critical for user safety — leave blank only if there are no known exclusivity implications.',
            ],

            // ── Tab: Last Verified ────────────────────────────────────────

            [
                'key'   => 'field_ag_procedure_review_tab',
                'label' => 'Last Verified',
                'type'  => 'tab',
            ],
            [
                'key'            => 'field_ag_procedure_last_reviewed',
                'label'          => 'Last Verified Date',
                'name'           => 'ws_ag_procedure_last_reviewed',
                'type'           => 'date_picker',
                'instructions'   => 'Update each time this procedure record is meaningfully verified against the source agency.',
                'display_format' => 'F j, Y',
                'return_format'  => 'Y-m-d',
                'first_day'      => 1,
            ],

            // ── Tab: Admin Review ─────────────────────────────────────────
            //
            // Visible to administrators only — hidden from all other roles
            // via ws_hide_source_fields_for_non_admins() registered in
            // admin-hooks.php.
            //
            // When admin-procedure-watch.php detects a disclosure-type
            // mismatch between a linked statute and this procedure, it:
            //   1. Sets ws_ag_procedure_parent_flagged = 1 in post meta.
            //   2. Forces post_status back to 'draft'.
            //   3. Records mismatch detail in ws_ag_procedure_parent_flag_detail.
            //
            // The admin reviews the notice on this screen and either:
            //   A. Fixes the underlying data (resolves mismatches) — the flag
            //      clears automatically on the next clean save.
            //   B. Checks ws_ag_procedure_parent_override and saves — the flag is cleared
            //      and the override is logged. The procedure can then be published
            //      normally. The override resets to 0 after each save.

            [
                'key'   => 'field_ag_procedure_admin_review_tab',
                'label' => 'Admin Review',
                'type'  => 'tab',
            ],
            [
                'key'           => 'field_ag_procedure_parent_override',
                'label'         => 'Parent Record (Statute or Common Law) Link Override',
                'name'          => 'ws_ag_procedure_parent_override',
                'type'          => 'true_false',
                'instructions'  => 'Enable to acknowledge parent link warnings and allow publishing despite mismatches. Use only when the link is intentionally unconventional. Resets automatically after each save. Overrides are logged for audit.',
                'ui'            => 1,
                'ui_on_text'    => 'Override',
                'ui_off_text'   => 'No',
                'default_value' => 0,
            ],

        ], // end fields

    ] ); // end acf_add_local_field_group

} // end ws_register_acf_ag_procedures


// ── Auto-scope the statute and common-law relationship picker ────────────────────────────────
//
// Before the field_ag_procedure_statute_ids and field_ag_procedure_comlaw_ids
// relationship pickers render their results, this hook narrows the query to
// statutes and common-law entries that share the procedure's own
// jurisdiction AND disclosure type scope. The editor sees only relevant
// statutes or common-law entries without needing to manually apply filters first.
//
// Falls back to the full list (plus the manual filter UI) when:
//   — The procedure is a new auto-draft (no taxonomy terms saved yet).
//   — The procedure has no jurisdiction or disclosure types assigned.
//
// Note: the hook fires via AJAX when the editor interacts with the picker,
// so $post_id is the procedure post ID with its current saved taxonomy state.

add_filter(
    'acf/fields/relationship/query/key=field_ag_procedure_statute_ids',
    'ws_ag_procedure_scope_parent_ids',
    10, 3
);

add_filter(
    'acf/fields/relationship/query/key=field_ag_procedure_comlaw_ids',
    'ws_ag_procedure_scope_parent_ids',
    10, 3
);

function ws_ag_procedure_scope_parent_ids( $args, $field, $post_id ) {

    // Skip auto-draft — taxonomy terms not saved yet, nothing to scope by.
    if ( ! $post_id || 'auto-draft' === get_post_status( $post_id ) ) {
        return $args;
    }

    $jx_terms   = wp_get_post_terms( $post_id, WS_JURISDICTION_TAXONOMY,  [ 'fields' => 'ids' ] );
    $disc_types = wp_get_object_terms( $post_id, 'ws_disclosure_type',    [ 'fields' => 'ids' ] );

    $tax_query  = [ 'relation' => 'AND' ];
    $has_filter = false;

    if ( ! empty( $jx_terms ) && ! is_wp_error( $jx_terms ) ) {
        $tax_query[] = [
            'taxonomy' => WS_JURISDICTION_TAXONOMY,
            'field'    => 'term_id',
            'terms'    => $jx_terms,
        ];
        $has_filter = true;
    }

    if ( ! empty( $disc_types ) && ! is_wp_error( $disc_types ) ) {
        $tax_query[] = [
            'taxonomy' => 'ws_disclosure_type',
            'field'    => 'term_id',
            'terms'    => $disc_types,
        ];
        $has_filter = true;
    }

    if ( $has_filter ) {
        $args['tax_query'] = $tax_query;
    }

    return $args;
}

// ── Pre-populate ws_ag_procedure_agency_id from ?agency_id= URL parameter ─────────────
//
// When "Add Procedure" is clicked from the agency navigation box, the URL
// carries ?agency_id={post_id}. On auto-draft posts, this hook returns the
// agency ID as the field value so ACF renders the parent agency pre-selected.
// Mirrors ws_construction_prefill_statute_id() in acf-jx-constructions.php.

add_filter( 'acf/load_value/key=field_ag_procedure_agency_id', 'ws_ag_procedure_prefill_agency_id', 5, 3 );

function ws_ag_procedure_prefill_agency_id( $value, $post_id, $field ) {
    if ( get_post_status( $post_id ) !== 'auto-draft' ) {
        return $value;
    }
    if ( ! isset( $_GET['agency_id'] ) ) {
        return $value;
    }
    $agency_id = absint( $_GET['agency_id'] );
    if ( $agency_id && get_post_type( $agency_id ) === 'ws-agency' ) {
        return $agency_id;
    }
    return $value;
}
// ── Merge statute/common-law parent links into unified parent_ids ───────────
//
// ACF writes relationship values at priority 10. This hook runs at 15 so the
// merged parent list is in place before downstream cache-diff hooks (priority 20)
// read _ws_ag_procedure_parent_ids.
add_action( 'acf/save_post', 'ws_ag_procedure_sync_parent_ids', 15 );

/**
 * Merge ws_ag_procedure_statute_ids + ws_ag_procedure_comlaw_ids into
 * _ws_ag_procedure_parent_ids on save.
 *
 * @param int|string $post_id Post ID from acf/save_post.
 * @return void
 */
function ws_ag_procedure_sync_parent_ids( $post_id ) {
    $post_id = (int) $post_id;
    if ( $post_id <= 0 || get_post_type( $post_id ) !== 'ag-procedure' ) {
        return;
    }

    $statute_ids = ws_ag_procedure_normalize_id_array( get_post_meta( $post_id, 'ws_ag_procedure_statute_ids', true ) );
    $comlaw_ids  = ws_ag_procedure_normalize_id_array( get_post_meta( $post_id, 'ws_ag_procedure_comlaw_ids', true ) );

    // Preserve editor order (statutes first, then common-law), remove duplicates.
    $merged = array_values( array_unique( array_merge( $statute_ids, $comlaw_ids ) ) );

    update_post_meta( $post_id, '_ws_ag_procedure_parent_ids', $merged );
}

/**
 * Normalize relationship/meta values into a clean integer ID list.
 *
 * @param mixed $raw Raw post meta value.
 * @return int[]
 */
function ws_ag_procedure_normalize_id_array( $raw ): array {
    if ( ! is_array( $raw ) ) {
        $raw = ( $raw === '' || $raw === null ) ? [] : [ $raw ];
    }

    $ids = array_map( 'intval', $raw );
    $ids = array_filter( $ids, static function( $id ) {
        return $id > 0;
    } );

    return array_values( $ids );
}