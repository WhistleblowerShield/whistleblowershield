<?php
/**
 * acf-agencies.php
 *
 * Registers ACF Pro fields for the `ws-agency` CPT.
 *
 * PURPOSE
 * -------
 * Provides structured metadata for oversight agency records, including
 * jurisdiction scope, intake channels, and reporting constraints used by
 * directory/filter logic and downstream guidance workflows.
 *
 * GROUP: group_agency_metadata
 *
 * FIELD SUMMARY
 * -------------
 * Agency Identity tab:
 *   _ws_agency_id                       Agency Reference Code (text, required)
 *   ws_agency_official_name              Full Agency Official Name (text, required)
 *   ws_agency_logo                       Agency Logo (image, optional)
 *   ws_agency_jurisdictions              Jurisdiction(s) (multi_select, optional)
 *   ws_agency_disclosure_types           Disclosure Categories (multi_select, optional)
 *   ws_agency_disclosure_targets         Reporting Target Classifications (multi_select, optional)
 *   ws_agency_process_types              Process Types Handled (multi_select, optional)
 *
 * Contact & Reporting tab:
 *   ws_agency_url                        Official Website URL (url, optional)
 *   ws_agency_reporting_url              Secure Reporting Portal (url, optional)
 *   ws_agency_phone                      Whistleblower Hotline (text, optional)
 *   ws_agency_confidentiality_details    Confidentiality & Privacy Details (textarea, optional)
 *   ws_agency_accepts_anonymous          Anonymous Reporting Allowed? (true_false, optional)
 *   ws_agency_has_reward                 Reward/Bounty Program Available? (true_false, optional)
 *   ws_agency_reward_details             Reward/Bounty Program Details (textarea, conditional)
 *   ws_agency_languages                  Languages Supported (multi_select, optional)
 *   ws_agency_additional_languages       Additional Languages (text, optional)
 *   ws_agency_last_reviewed              Last Verified Date (date_picker, optional)
 * 
 * Hidden fields:
 *   __ws_agency_id                       Reserved: Ingest Dedupe Code (text, hidden)
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
 * JURISDICTION FIELD
 * ------------------
 * Scoped via the ws_jurisdiction taxonomy (field_agency_jurisdictions).
 * ACF saves/loads terms natively -- no dynamic choice filter needed.
 * Replaces the retired ws_jx_code meta select.
 *
 *
 * @package    WhistleblowerShield
 * @since      2.3.1
 * @version    3.17.0
 * @author     Whistleblower Shield
 * @link       https://whistleblowershield.org
 * @copyright  Copyright (c) Whistleblower Shield
 * 
 */

defined( 'ABSPATH' ) || exit;


// ── Field group registration ──────────────────────────────────────────────────

add_action( 'acf/init', 'ws_register_acf_agencies' );

function ws_register_acf_agencies() {

    if ( ! function_exists( 'acf_add_local_field_group' ) ) {
        return;
    }

    acf_add_local_field_group( [

        'key'                   => 'group_agency_metadata',
        'title'                 => 'Agency Details & Reporting Protocols',
        'menu_order'            => 0,
        'position'              => 'normal',
        'style'                 => 'default',
        'label_placement'       => 'top',
        'instruction_placement' => 'label',
        'active'                => true,

        'location' => [ [ [
            'param'    => 'post_type',
            'operator' => '==',
            'value'    => 'ws-agency',
        ] ] ],

        'fields' => [

            // ── Tab: Agency Identity ──────────────────────────────────────

            [
                'key'   => 'field_agency_identity_tab',
                'label' => 'Agency Identity',
                'type'  => 'tab',
            ],
            [
                'key'          => 'field_agency_id',
                'label'        => 'Agency Reference Code',
                'name'         => '_ws_agency_id',
                'type'         => 'text',
                'instructions' => 'Internal slug-safe unique ID — e.g., "osc", "sec-owb", "ny-ag-wb".',
                'required'     => 1,
            ],
            [
                'key'          => 'field_agency_official_name',
                'label'        => 'Agency Official Name',
                'name'         => 'ws_agency_official_name',
                'type'         => 'text',
                'required'     => 1,
                'instructions' => 'Example: U.S. Office of Special Counsel',
            ],
            [
                'key'          => 'field_agency_common_name',
                'label'        => 'Agency Common Name / Acronym',
                'name'         => 'ws_agency_common_name',
                'type'         => 'text',
                'required'     => 1,
                'instructions' => 'Example: OSC',
            ],
            [
                'key'           => 'field_agency_logo',
                'label'         => 'Agency Logo',
                'name'          => 'ws_agency_logo',
                'type'          => 'image',
                'instructions'  => 'Upload a high-resolution logo (PNG or SVG preferred).',
                'required'      => 0,
                'return_format' => 'array',
                'preview_size'  => 'medium',
                'library'       => 'all',
                'max_size'      => '1',  // 1MB
                'mime_types'    => 'png,svg,jpg,jpeg',
            ],
            [
                'key'          => 'field_agency_mission',
                'label'        => 'Agency Mission',
                'name'         => 'ws_agency_mission',
                'type'         => 'textarea',
                'required'     => 1,
                'instructions' => 'A brief description of the agency\'s purpose and responsibilities.',
            ],
            
            // ── Jurisdiction(s) ───────────────────────────────────────────
            //
            // Agencies may have authority over multiple jurisdictions.
            // Scoped via the ws_jurisdiction taxonomy — assign terms to
            // control which jurisdiction pages surface this agency.
            // save_terms=1 writes term assignments directly; load_terms=1
            // reflects current taxonomy state in the admin UI.

            [
                'key'           => 'field_agency_jurisdictions',
                'label'         => 'Jurisdiction(s)',
                'name'          => 'ws_agency_jurisdictions',
                'type'          => 'taxonomy',
                'taxonomy'      => WS_JURISDICTION_TAXONOMY,
                'field_type'    => 'multi_select',
                'instructions'  => 'Assign all jurisdictions this agency has authority over. Use US for federal/nationwide agencies.',
                'add_term'      => 0,
                'save_terms'    => 1,
                'load_terms'    => 1,
                'return_format' => 'id',
                'allow_null'    => 1,
            ],
            [
                'key'           => 'field_agency_disclosure_types',
                'label'         => 'Disclosure Categories',
                'name'          => 'ws_agency_disclosure_types',
                'type'          => 'taxonomy',
                'taxonomy'      => 'ws_disclosure_type',
                'field_type'    => 'multi_select',
                'instructions'  => 'Subject matter areas this agency accepts or oversees. Use all that apply.',
                'add_term'      => 0,
                'save_terms'    => 1,
                'load_terms'    => 1,
                'return_format' => 'id',
            ],

            [
                'key'           => 'field_agency_disclosure_targets',
                'label'         => 'Reporting Target Classifications',
                'name'          => 'ws_agency_disclosure_targets',
                'type'          => 'taxonomy',
                'taxonomy'      => 'ws_disclosure_target',
                'field_type'    => 'multi_select',
                'instructions'  => 'What kind of reporting body is this agency? Use the most accurate single term in most cases; multi-tag only where genuinely dual.',
                'add_term'      => 0,
                'save_terms'    => 1,
                'load_terms'    => 1,
                'return_format' => 'id',
            ],

            [
                'key'           => 'field_agency_employment_sectors',
                'label'         => 'Employment Sectors',
                'name'          => 'ws_agency_employment_sectors',
                'type'          => 'taxonomy',
                'taxonomy'      => 'ws_employment_sector',
                'field_type'    => 'multi_select',
                'instructions'  => 'All employment sectors this agency has jurisdiction over.',
                'required'      => 0,
                'add_term'      => 0,
                'save_terms'    => 1,
                'load_terms'    => 1,
                'return_format' => 'id',
            ],

            // ── Process Types ─────────────────────────────────────────────
            //
            // What types of legal action does this agency handle?
            // This is descriptive — the statute is the authoritative
            // source for which process types a whistleblower can use.
            // Tag the agency here so editors and users can filter
            // agencies by how they handle reports (e.g., "show me
            // agencies that accept anonymous administrative complaints").

            [
                'key'           => 'field_agency_process_types',
                'label'         => 'Process Types Handled',
                'name'          => 'ws_agency_process_types',
                'type'          => 'taxonomy',
                'taxonomy'      => 'ws_process_type',
                'instructions'  => 'Select all process types this agency handles. Refer to the relevant statute(s) as the authoritative source.',
                'field_type'    => 'multi_select',
                'add_term'      => 0,
                'save_terms'    => 1,
                'load_terms'    => 1,
                'return_format' => 'id',
                'allow_null'    => 1,
            ],

            // ── Tab: Contact & Reporting ──────────────────────────────────

            [
                'key'   => 'field_agency_contact_tab',
                'label' => 'Contact & Reporting',
                'type'  => 'tab',
            ],
            [
                'key'   => 'field_agency_url',
                'label' => 'Official Website URL',
                'name'  => 'ws_agency_url',
                'type'  => 'url',
            ],
            [
                'key'          => 'field_agency_reporting_url',
                'label'        => 'Secure Reporting Portal',
                'name'         => 'ws_agency_reporting_url',
                'type'         => 'url',
                'instructions' => 'Direct link to the intake form or hotline page.',
            ],
            [
                'key'   => 'field_agency_phone',
                'label' => 'Whistleblower Hotline',
                'name'  => 'ws_agency_phone',
                'type'  => 'text',
            ],
            [
                'key'          => 'field_agency_confidentiality_details',
                'label'        => 'Confidentiality & Privacy Details',
                'name'         => 'ws_agency_confidentiality_details',
                'type'         => 'textarea',
                'rows'         => 4,
                'instructions' => 'Briefly describe how this agency handles identity protection.',
            ],
            [
                'key'           => 'field_agency_accepts_anonymous',
                'label'         => 'Anonymous Reporting Allowed?',
                'name'          => 'ws_agency_accepts_anonymous',
                'type'          => 'true_false',
                'instructions'  => 'Enable if this agency accepts reports without requiring the reporter to identify themselves.',
                'ui'            => 1,
                'ui_on_text'    => 'Yes',
                'ui_off_text'   => 'No',
                'default_value' => 0,
            ],
            [
                'key'           => 'field_agency_has_reward',
                'label'         => 'Reward/Bounty Program Available?',
                'name'          => 'ws_agency_has_reward',
                'type'          => 'true_false',
                'instructions'  => 'Enable if this agency offers financial rewards or bounties to whistleblowers.',
                'ui'            => 1,
                'ui_on_text'    => 'Yes',
                'ui_off_text'   => 'No',
                'default_value' => 0,
            ],
                    [
                        'key'          => 'field_agency_reward_details',
                        'label'        => 'Reward/Bounty Program Details',
                        'name'         => 'ws_agency_reward_details',
                        'type'         => 'textarea',
                        'rows'         => 3,
                        'instructions' => 'Describe what the rewards or bounty program entails.',
                        'conditional_logic' => [ [ [
                            'field'    => 'field_agency_has_reward',
                            'operator' => '==',
                            'value'    => '1',
                        ] ] ],
                    ],
            // ── Languages ─────────────────────────────────────────────

            [
                'key'           => 'field_agency_languages',
                'label'         => 'Languages Supported',
                'name'          => 'ws_agency_languages',
                'type'          => 'taxonomy',
                'taxonomy'      => 'ws_language',
                'field_type'    => 'multi_select',
                'instructions'  => 'Select languages this agency can support. Check "Additional" if other languages are available — then specify them below.',
                'add_term'      => 0,
                'save_terms'    => 1,
                'load_terms'    => 1,
                'return_format' => 'id',
            ],

            [
                'key'          => 'field_agency_additional_languages',
                'label'        => 'Additional Languages',
                'name'         => 'ws_agency_additional_languages',
                'type'         => 'text',
                'instructions' => 'List additional languages not in the checkbox list above (comma-separated). Saving a non-empty value here automatically assigns the "Additional" language term.',
            ],

            // ── Tab: Authorship & Review ──────────────────────────────────
            // Removed — registered centrally in acf-stamp-fields.php
            // (group_stamp_metadata, menu_order 90).

            // ── Last Verified Date ────────────────────────────────────────
            //
            // Content-owned field — not a stamp. Retained here in the
            // agency's own group.

            [
                'key'            => 'field_agency_last_reviewed',
                'label'          => 'Last Verified Date',
                'name'           => 'ws_agency_last_reviewed',
                'type'           => 'date_picker',
                'instructions'   => 'Update this date each time the agency record is meaningfully revised.',
                'display_format' => 'F j, Y',
                'return_format'  => 'Y-m-d',
                'first_day'      => 1,
            ],

            // ── Tab: Plain-English ───────────────────────────────────────
            // Removed — registered centrally in acf-plain-english-fields.php
            // (group_plain_english_metadata, menu_order 85).

        ], // end fields

    ] ); // end acf_add_local_field_group

} // end ws_register_acf_agencies


// Dynamic choice filter removed (Phase 3.2 / 12.1).
// ws_jurisdiction is now a taxonomy field — ACF loads terms natively.
