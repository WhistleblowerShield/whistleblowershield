<?php
/**
 * acf-assist-orgs.php
 *
 * Registers ACF Pro fields for the `ws-assist-org` CPT.
 *
 * PURPOSE
 * -------
 * Provides structured metadata for whistleblower assistance organizations,
 * including service scope, jurisdiction coverage, intake channels, and
 * eligibility signals used by directory matching and ranking logic.
 *
 * GROUP: group_assist_org_metadata
 *
 * FIELD SUMMARY
 * -------------
 * Identity tab:
 *   ws_aorg_internal_id                    Internal ID Code (text, required, slug-safe, lowercase-hyphen, abbreviated-by-table)
 *   ws_aorg_official_name                  Official Organization Name (text, required)
 *   ws_aorg_type                           Organization Type (radio, required)
 *   ws_aorg_description                    Organization Description (textarea, optional)
 *   ws_aorg_common_name                    Common Name / Acronym (text, optional)
 *   ws_aorg_logo                           Organization Logo (image, optional)
 *
 * Scope of Service tab:
 *   ws_aorg_serves_nationwide              Nationwide (All 57 Jurisdictions) (true_false, optional)
 *   ws_aorg_whistleblower_scope            Whistleblower Focus Scope (number, required)
 *   ws_aorg_whistleblower_scope_details    Scope Justification — Details (textarea, optional)
 *   ws_aorg_has_limited_scope              Community / Local Scope (true_false, conditional)
 *   ws_aorg_jurisdictions                  Jurisdictions Served (multi_select, optional)
 *   ws_aorg_community_scope                Community Scope (textarea, conditional)
 *   ws_aorg_protected_disclosures               Misconduct Categories Handled (multi_select, required)
 *   ws_aorg_disclosure_targets             Disclosure Targets Supported (multi_select, optional)
 *   ws_aorg_disclosure_target_details      Disclosure Targets Details (textarea, optional)
 *   ws_aorg_case_stages                    Case Stage (multi_select, optional)
 *   ws_aorg_case_stage_details             Case Stage Details (textarea, optional)
 *   ws_aorg_process_types                  Process Types (multi_select, optional)
 *   ws_aorg_services                       Services Offered (multi_select, required)
 *   ws_aorg_additional_services            Additional Services (textarea, optional)
 *   ws_aorg_employment_sectors             Employment Sectors Served (multi_select, optional)
 *   ws_aorg_protected_classes              Protected Classes Served (multi_select, optional)
 *   ws_aorg_protected_class_details        Protected Class Details (textarea, optional)
 *
 * Contact & Intake tab:
 *   ws_aorg_website_url                    Official Website (url, required)
 *   ws_aorg_intake_url                     Intake URL (url, optional)
 *   ws_aorg_contact_url                    Contact URL (url, optional)
 *   ws_aorg_phones                         Phone Numbers [type|number] (repeater, optional)
 *   ws_aorg_emails                         Email Addresses [type|address] (repeater, optional)
 *   ws_aorg_mailing_address                Mailing Address (textarea, optional, use || to separate multiple)
 *   ws_aorg_has_secure_channel             Secure Contact Channel Available? (true_false, optional)
 *   ws_aorg_secure_contact_url             Secure Contact URL (url, conditional)
 *   ws_aorg_secure_contact_tool            Secure Contact Tool (select, conditional)
 *   ws_aorg_secure_contact_tool_other      Secure Contact Tool (Other) (text, conditional)
 *   ws_aorg_languages                      Languages Supported (multi_select, optional)
 *   ws_aorg_additional_languages           Additional Languages (text, optional)
 *
 * Eligibility & Cost tab:
 *   ws_aorg_cost_models                    Cost Structure (multi_select, required)
 *   ws_aorg_has_income_limit               Income Eligibility Required? (true_false, optional)
 *   ws_aorg_income_limit_details           Income Eligibility Details (textarea, conditional)
 *   ws_aorg_accepts_anonymous              Can Assist Anonymous Clients? (true_false, optional)
 *   ws_aorg_eligibility_details            Non-Income Eligibility Requirements (textarea, optional)
 *
 * Credentials tab:
 *   ws_aorg_licensed_attorneys             Licensed Attorneys on Staff? (true_false, optional)
 *   ws_aorg_accreditation                  Accreditation & Certifications (text, optional)
 *   ws_aorg_bar_states                     State Bar Memberships (text, optional)
 *   ws_aorg_legitimacy_url                 Legitimacy / Transparency URL (url, optional)
 *   ws_aorg_last_reviewed                  Last Verified Date (date_picker, optional)
 *
 * Internal Contact & Relationship Notes tab (no rendering on front end, for internal use only):
 *   _ws_aorg_internal_contact_name          Internal Contact Name (text, optional)
 *   _ws_aorg_internal_contact_role          Internal Contact Role/Title (text, optional)
 *   _ws_aorg_internal_contact_email         Internal Contact Email (email, optional)
 *   _ws_aorg_internal_contact_phone         Internal Contact Phone (text, optional)
 *   _ws_aorg_internal_last_contacted        Internal Last Contacted (date_picker, optional)
 *   _ws_aorg_internal_relationship_notes    Internal Relationship Notes (textarea, optional)
 * 
 * Hidden Fields (used by Admin Tool Ingest, not visible to public):
 *   _ws_aorg_id                            Ingest Dedupe Code (text, hidden)
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
 * META KEY NOTE
 * -------------
 * ws_aorg_internal_id is stored WITHOUT a leading underscore. ACF uses the
 * _ws_aorg_id key (underscore prefix) for its own internal field
 * reference -- writing a value there clobbers ACF's mapping. Ingest must write
 * to _ws_aorg_id. The leading underscore in prompt JSON schema output
 * is a naming convention only; ingest strips it during mapping.
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

add_action( 'acf/init', 'ws_register_acf_assist_org' );

function ws_register_acf_assist_org() {

    if ( ! function_exists( 'acf_add_local_field_group' ) ) {
        return;
    }

    $phone_type_choices  = array_combine( WS_SCHEMA_PHONE_TYPE,  WS_SCHEMA_PHONE_TYPE );
    $email_type_choices  = array_combine( WS_SCHEMA_EMAIL_TYPE,  WS_SCHEMA_EMAIL_TYPE );
    $secure_tool_choices = array_combine( WS_SCHEMA_SECURE_TOOL, WS_SCHEMA_SECURE_TOOL );

    acf_add_local_field_group( [

        'key'                   => 'group_assist_org_metadata',
        'title'                 => 'Assistance Organization Details',
        'menu_order'            => 0,
        'position'              => 'normal',
        'style'                 => 'default',
        'label_placement'       => 'top',
        'instruction_placement' => 'label',
        'active'                => true,

        'location' => [ [ [
            'param'    => 'post_type',
            'operator' => '==',
            'value'    => 'ws-assist-org',
        ] ] ],

        'fields' => [

            // ────────────────────────────────────────────────────────────────
            // Tab: Identity
            //
            // Core identifiers and classification for each organization.
            // post_title mirrors ws_aorg_official_name at ingest time.
            // ws_aorg_official_name is the authoritative data-layer source.
            // ────────────────────────────────────────────────────────────────

            [
                'key'   => 'field_aorg_identity_tab',
                'label' => 'Identity',
                'type'  => 'tab',
            ],

            [
                'key'          => 'field_aorg_internal_id',
                'label'        => 'Internal Reference Code',
                'name'         => '_ws_aorg_id',
                'type'         => 'text',
                'instructions' => 'Slug-safe internal identifier — lowercase, hyphens only. Examples: "aclu-national", "nwc-dc", "gp-ca". Used for programmatic lookups and deduplication.',
                'required'     => 1,
                'placeholder'  => 'aclu-national',
            ],

            [
                'key'          => 'field_aorg_official_name',
                'label'        => 'Official Organization Name',
                'name'         => 'ws_aorg_official_name',
                'type'         => 'text',
                'instructions' => 'Full official name of the organization exactly as it appears on its homepage or governing documents. This is the authoritative data-layer field — post_title mirrors this value at ingest time.',
                'required'     => 1,
                'placeholder'  => 'Government Accountability Project',
            ],


            [
                'key'           => 'field_aorg_type',
                'label'         => 'Organization Type',
                'name'          => 'ws_aorg_type',
                'type'          => 'taxonomy',
                'taxonomy'      => 'ws_aorg_type',
                'instructions'  => 'Select the category that best describes this organization. Use "Mixed Organization Type" when an organization genuinely fits multiple categories and cannot be reasonably classified under a single type.',
                'required'      => 1,
                'field_type'    => 'radio',
                'add_term'      => 0,
                'save_terms'    => 1,
                'load_terms'    => 1,
                'return_format' => 'id',
                'allow_null'    => 0,
            ],

            [
                'key'          => 'field_aorg_description',
                'label'        => 'Organization Description',
                'name'         => 'ws_aorg_description',
                'type'         => 'textarea',
                'instructions' => 'Plain-English overview of this organization\'s mission, focus areas, and typical whistleblower support.',
                'required'     => 0,
                'rows'         => 4,
                'new_lines'    => 'wpautop',
            ],


            [
                'key'          => 'field_aorg_common_name',
                'label'        => 'Common Name / Abbreviation',
                'name'         => 'ws_aorg_common_name',
                'type'         => 'text',
                'instructions' => 'Short name or abbreviation used in citations and exclusion lists — e.g., "GAP", "NWC", "PEER". Leave blank if the organization does not use a common abbreviation.',
                'required'     => 0,
                'placeholder'  => 'GAP',
            ],

            [
                'key'           => 'field_aorg_logo',
                'label'         => 'Organization Logo',
                'name'          => 'ws_aorg_logo',
                'type'          => 'image',
                'instructions'  => 'Upload the organization\'s logo (PNG or SVG preferred).',
                'return_format' => 'array',
                'preview_size'  => 'medium',
                'library'       => 'all',
                'max_size'      => '1',
                'mime_types'    => 'png,svg,jpg,jpeg',
            ],

            // ────────────────────────────────────────────────────────────────
            // Tab: Scope of Service
            //
            // Defines who this organization can help and how. These fields
            // drive the directory filtering logic so laypeople can quickly
            // surface organizations relevant to their specific situation.
            // ────────────────────────────────────────────────────────────────

            [
                'key'   => 'field_aorg_scope_tab',
                'label' => 'Scope of Service',
                'type'  => 'tab',
            ],

            [
                'key'           => 'field_aorg_serves_nationwide',
                'label'         => 'Nationwide (All 57 Jurisdictions)',
                'name'          => 'ws_aorg_serves_nationwide',
                'type'          => 'true_false',
                'instructions'  => 'Enable to mark this organization as nationwide across all 57 jurisdictions (states, territories, and federal). This is the core nationwide trigger for directory logic.',
                'ui'            => 1,
                'ui_on_text'    => 'Nationwide',
                'ui_off_text'   => 'Limited',
                'default_value' => 0,
            ],

            [
                'key'          => 'field_aorg_whistleblower_scope',
                'label'        => 'Whistleblower Focus Scope',
                'name'         => 'ws_aorg_whistleblower_scope',
                'type'         => 'number',
                'instructions' => 'How dedicated is this organization to whistleblower assistance specifically? 0 = not applicable (org does not serve whistleblowers — ingest will reject; requires justification in note field); 1 = tangential (general legal aid that can assist); 2 = significant focus (one program among several); 3 = primary mission (whistleblowers are the core constituency). Used as a base score multiplier in directory sorting. A score of 0 must be explained in the Scope Justification field below — it exists only to flag LLM-sourced records that slipped through topic screening.',
                'required'     => 1,
                'default_value'=> 1,
                'min'          => 0,
                'max'          => 3,
                'step'         => 1,
                'prepend'      => '',
                'append'       => '/ 3',
            ],

            [
                'key'          => 'field_aorg_whistleblower_scope_details',
                'label'        => 'Scope Justification — Details',
                'name'         => 'ws_aorg_whistleblower_scope_details',
                'type'         => 'textarea',
                'instructions' => 'Supporting quote or editorial note that justifies the scope score above. Paste a direct quote from the organization\'s own website. Required when score is 0 — explain why the record exists and what should happen to it. Used for editorial review, not surfaced publicly.',
                'required'     => 0,
                'rows'         => 3,
                'new_lines'    => '',
                'conditional_logic' => 0,
                // Score = 0 makes this functionally required by editorial policy,
                // not enforced by ACF conditional required (ACF can't do required-if).
                // Ingest enforces: zero scope without a note is a hard reject.
            ],

            [
                'key'           => 'field_aorg_has_limited_scope',
                'label'         => 'Community / Local Scope',
                'name'          => 'ws_aorg_has_limited_scope',
                'type'          => 'true_false',
                'instructions'  => 'Enable when coverage is limited to specific cities/regions within a jurisdiction (for example, San Francisco or Los Angeles County).',
                'ui'            => 1,
                'ui_on_text'    => 'Local/Community',
                'ui_off_text'   => 'Jurisdiction-wide',
                'default_value' => 0,
                'conditional_logic' => [ [ [
                    'field'    => 'field_aorg_serves_nationwide',
                    'operator' => '==',
                    'value'    => '0',
                ] ] ],
            ],

            [
                'key'           => 'field_aorg_jurisdictions',
                'label'         => 'Jurisdictions Served',
                'name'          => 'ws_aorg_jurisdictions',
                'type'          => 'taxonomy',
                'taxonomy'      => WS_JURISDICTION_TAXONOMY,
                'field_type'    => 'multi_select',
                'instructions'  => 'Select every jurisdiction where this organization can provide assistance. If nationwide, enable the toggle above and leave this blank.',
                'required'      => 0,
                'add_term'      => 0,
                'save_terms'    => 1,
                'load_terms'    => 1,
                'return_format' => 'id',
                'allow_null'    => 1,
            ],

            [
                'key'          => 'field_aorg_community_scope',
                'label'        => 'Community Scope',
                'name'         => 'ws_aorg_community_scope',
                'type'         => 'textarea',
                'instructions' => 'Optional local-service footprint for community-driven organizations. Examples: "San Francisco", "Los Angeles County", "Inland Empire", "Bay Area".',
                'required'     => 0,
                'rows'         => 2,
                'conditional_logic' => [ [
                    [
                        'field'    => 'field_aorg_serves_nationwide',
                        'operator' => '==',
                        'value'    => '0',
                    ],
                    [
                        'field'    => 'field_aorg_has_limited_scope',
                        'operator' => '==',
                        'value'    => '1',
                    ],
                ] ],
            ],

            [
                'key'           => 'field_aorg_protected_disclosures',
                'label'         => 'Misconduct Categories Handled',
                'name'          => 'ws_aorg_protected_disclosures',
                'type'          => 'taxonomy',
                'taxonomy'      => 'ws_protected_disclosure',
                'instructions'  => 'Select all types of misconduct this organization has experience assisting with.',
                'required'      => 1,
                'field_type'    => 'multi_select',
                'add_term'      => 0,
                'save_terms'    => 1,
                'load_terms'    => 1,
                'return_format' => 'id',
            ],

            [
                'key'           => 'field_aorg_disclosure_targets',
                'label'         => 'Disclosure Targets Supported',
                'name'          => 'ws_aorg_disclosure_targets',
                'type'          => 'taxonomy',
                'taxonomy'      => 'ws_disclosure_target',
                'field_type'    => 'multi_select',
                'instructions'  => 'Reporting channels this organization can help a whistleblower navigate or prepare for. Tag all that the org explicitly supports.',
                'add_term'      => 0,
                'save_terms'    => 1,
                'load_terms'    => 1,
                'return_format' => 'id',
            ],

            [
                'key'          => 'field_aorg_disclosure_target_details',
                'label'        => 'Disclosure Targets Details',
                'name'         => 'ws_aorg_disclosure_target_details',
                'type'         => 'textarea',
                'rows'         => 3,
                'instructions' => 'Describe any conditions, channel-specific expertise, or nuance in the reporting targets this organization supports.',
                // conditional_logic set dynamically — see ws_aorg_details_conditional()
            ],

            [
                'key'           => 'field_aorg_case_stages',
                'label'         => 'Case Stage',
                'name'          => 'ws_aorg_case_stages',
                'type'          => 'taxonomy',
                'taxonomy'      => 'ws_case_stage',
                'field_type'    => 'multi_select',
                'instructions'  => 'Stage of a whistleblower\'s situation where this organization is most useful. Tag all that genuinely apply.',
                'add_term'      => 0,
                'save_terms'    => 1,
                'load_terms'    => 1,
                'return_format' => 'id',
            ],

            [
                'key'          => 'field_aorg_case_stage_details',
                'label'        => 'Case Stage Details',
                'name'         => 'ws_aorg_case_stage_details',
                'type'         => 'textarea',
                'rows'         => 3,
                'instructions' => 'Provide details when Case Stage includes "other".',
                // conditional_logic set dynamically — see ws_aorg_details_conditional()
            ],

            [
                'key'           => 'field_aorg_process_types',
                'label'         => 'Process Types',
                'name'          => 'ws_aorg_process_types',
                'type'          => 'taxonomy',
                'taxonomy'      => 'ws_process_type',
                'field_type'    => 'multi_select',
                'instructions'  => 'Process channels this organization can help users navigate (for example, agency complaint, internal complaint, court filing).',
                'add_term'      => 0,
                'save_terms'    => 1,
                'load_terms'    => 1,
                'return_format' => 'id',
                'allow_null'    => 1,
            ],

            [
                'key'           => 'field_aorg_services',
                'label'         => 'Services Offered',
                'name'          => 'ws_aorg_services',
                'type'          => 'taxonomy',
                'taxonomy'      => 'ws_aorg_service',
                'instructions'  => 'Select all services this organization provides to whistleblowers.',
                'required'      => 1,
                'field_type'    => 'multi_select',
                'add_term'      => 0,
                'save_terms'    => 1,
                'load_terms'    => 1,
                'return_format' => 'id',
                'allow_null'    => 0,
            ],

            [
                'key'               => 'field_aorg_additional_services',
                'label'             => 'Additional Services',
                'name'              => 'ws_aorg_additional_services',
                'type'              => 'textarea',
                'instructions'      => 'Describe any services not covered by the list above. The "Additional Services" checkbox will be auto-assigned when this field is non-empty.',
                'required'          => 0,
                'rows'              => 3,
                'conditional_logic' => 0,
            ],
                       
            [
                'key'           => 'field_aorg_employment_sectors',
                'label'         => 'Employment Sectors Served',
                'name'          => 'ws_aorg_employment_sectors',
                'type'          => 'taxonomy',
                'taxonomy'      => 'ws_employment_sector',
                'field_type'    => 'multi_select',
                'instructions'  => 'Select the employment sectors this organization serves. Leave blank if all sectors are accepted.',
                'required'      => 0,
                'add_term'      => 0,
                'save_terms'    => 1,
                'load_terms'    => 1,
                'return_format' => 'id',
                'allow_null'    => 1,
            ],

            [
                'key'           => 'field_aorg_protected_classes',
                'label'         => 'Protected Classes Served',
                'name'          => 'ws_aorg_protected_classes',
                'type'          => 'taxonomy',
                'taxonomy'      => 'ws_protected_class',
                'field_type'    => 'multi_select',
                'instructions'  => 'Select all protected classes this organization serves. If "has-details" is selected, provide details below.',
                'add_term'      => 0,
                'save_terms'    => 1,
                'load_terms'    => 1,
                'return_format' => 'id',
                'required'      => 0,
            ],

            [
                'key'          => 'field_aorg_protected_class_details',
                'label'        => 'Protected Class Details',
                'name'         => 'ws_aorg_protected_class_details',
                'type'         => 'textarea',
                'instructions' => 'If "has-details" is selected above, provide details here.',
                'rows'         => 3,
            ], 
            // conditional_logic set dynamically — see ws_aorg_details_conditional()
            
            // ────────────────────────────────────────────────────────────────
            // Tab: Contact & Intake
            //
            // How a whistleblower reaches this organization. All fields
            // except website_url are optional — not all organizations
            // publish every channel.
            // ────────────────────────────────────────────────────────────────

            [
                'key'   => 'field_aorg_contact_intake_tab',
                'label' => 'Contact & Intake',
                'type'  => 'tab',
            ],

            [
                'key'          => 'field_aorg_website_url',
                'label'        => 'Official Website',
                'name'         => 'ws_aorg_website_url',
                'type'         => 'url',
                'instructions' => 'The organization\'s primary public website.',
                'required'     => 1,
            ],

            [
                'key'          => 'field_aorg_intake_url',
                'label'        => 'Intake URL',
                'name'         => 'ws_aorg_intake_url',
                'type'         => 'url',
                'instructions' => 'Direct link to intake or case-submission workflow. Find-A-Lawyer or similar pages qualify if no dedicated intake form exists.',
            ],

            [
                'key'          => 'field_aorg_contact_url',
                'label'        => 'Contact URL',
                'name'         => 'ws_aorg_contact_url',
                'type'         => 'url',
                'instructions' => 'General contact page URL when separate from intake.',
            ],

            [
                'key'               => 'field_aorg_phones',
                'label'             => 'Phone Numbers',
                'name'              => 'ws_aorg_phones',
                'type'              => 'repeater',
                'instructions'      => 'Public phone lines. Type values come from WS_SCHEMA_PHONE_TYPE at includes/admin/tools/ws-schema-constants.php.',
                'required'          => 0,
                'layout'            => 'table',
                'button_label'      => 'Add Phone',
                'sub_fields'        => [
                    [
                        'key'          => 'field_aorg_phone_type',
                        'label'        => 'Type',
                        'name'         => 'ws_aorg_phone_type',
                        'type'         => 'select',
                        'instructions' => 'Select a canonical value: hotline | intake | headquarters | regional | tty | fax | other.',
                        'required'     => 1,
                        'choices'      => $phone_type_choices,
                        'allow_null'   => 0,
                        'ui'           => 0,
                        'wrapper'      => [ 'width' => '30' ],
                    ],
                    [
                        'key'          => 'field_aorg_phone_number',
                        'label'        => 'Number',
                        'name'         => 'ws_aorg_phone_number',
                        'type'         => 'text',
                        'instructions' => 'Phone number in public format.',
                        'required'     => 1,
                        'placeholder'  => '(555) 000-0000',
                        'wrapper'      => [ 'width' => '70' ],
                    ],
                ],
            ],

            [
                'key'               => 'field_aorg_emails',
                'label'             => 'Email Addresses',
                'name'              => 'ws_aorg_emails',
                'type'              => 'repeater',
                'instructions'      => 'Public email channels. Type values come from WS_SCHEMA_EMAIL_TYPE at includes/admin/tools/ws-schema-constants.php.',
                'required'          => 0,
                'layout'            => 'table',
                'button_label'      => 'Add Email',
                'sub_fields'        => [
                    [
                        'key'          => 'field_aorg_email_type',
                        'label'        => 'Type',
                        'name'         => 'ws_aorg_email_type',
                        'type'         => 'select',
                        'instructions' => 'Select a canonical value: intake | general | legal | media | support | other.',
                        'required'     => 1,
                        'choices'      => $email_type_choices,
                        'allow_null'   => 0,
                        'ui'           => 0,
                        'wrapper'      => [ 'width' => '30' ],
                    ],
                    [
                        'key'          => 'field_aorg_email_address',
                        'label'        => 'Email',
                        'name'         => 'ws_aorg_email_address',
                        'type'         => 'email',
                        'instructions' => 'Public email address.',
                        'required'     => 1,
                        'wrapper'      => [ 'width' => '70' ],
                    ],
                ],
            ],

            [
                'key'          => 'field_aorg_mailing_address',
                'label'        => 'Mailing Address',
                'name'         => 'ws_aorg_mailing_address',
                'type'         => 'textarea',
                'instructions' => 'Physical or mailing address, if publicly available.',
                'rows'         => 3,
            ],

            [
                'key'           => 'field_aorg_has_secure_channel',
                'label'         => 'Secure Contact Channel Available?',
                'name'          => 'ws_aorg_has_secure_channel',
                'type'          => 'true_false',
                'instructions'  => 'Enable when the organization publishes a dedicated secure first-contact channel (for example, Signal or SecureDrop).',
                'ui'            => 1,
                'ui_on_text'    => 'Yes',
                'ui_off_text'   => 'No',
                'default_value' => 0,
            ],

            [
                'key'          => 'field_aorg_secure_contact_url',
                'label'        => 'Secure Contact URL',
                'name'         => 'ws_aorg_secure_contact_url',
                'type'         => 'url',
                'instructions' => 'Direct URL to the secure contact method, tool or instruction page.',
                'conditional_logic' => [ [ [
                    'field'    => 'field_aorg_has_secure_channel',
                    'operator' => '==',
                    'value'    => '1',
                ] ] ],
            ],

            [
                'key'          => 'field_aorg_secure_contact_tool',
                'label'        => 'Secure Contact Tool',
                'name'         => 'ws_aorg_secure_contact_tool',
                'type'         => 'select',
                'instructions' => 'Select a canonical value from SecureDrop | Signal | ProtonMail | Tutanota | Wire | Keybase | other',
                'choices'      => $secure_tool_choices,
                'allow_null'   => 1,
                'ui'           => 0,
                'conditional_logic' => [ [ [
                    'field'    => 'field_aorg_has_secure_channel',
                    'operator' => '==',
                    'value'    => '1',
                ] ] ],
            ],

            [
                'key'          => 'field_aorg_secure_contact_tool_other',
                'label'        => 'Secure Contact Tool (Other)',
                'name'         => 'ws_aorg_secure_contact_tool_other',
                'type'         => 'text',
                'instructions' => 'Required detail when Secure Contact Tool is set to "other".',
                'placeholder'  => 'Name the secure contact tool',
                'conditional_logic' => [ [
                    [
                        'field'    => 'field_aorg_has_secure_channel',
                        'operator' => '==',
                        'value'    => '1',
                    ],
                    [
                        'field'    => 'field_aorg_secure_contact_tool',
                        'operator' => '==',
                        'value'    => 'other',
                    ],
                ] ],
            ],

            [
                'key'           => 'field_aorg_languages',
                'label'         => 'Languages Supported',
                'name'          => 'ws_aorg_languages',
                'type'          => 'taxonomy',
                'taxonomy'      => 'ws_language',
                'field_type'    => 'multi_select',
                'instructions'  => 'Select languages this organization can support. Check "Additional" if other languages are available — then specify them below.',
                'add_term'      => 0,
                'save_terms'    => 1,
                'load_terms'    => 1,
                'return_format' => 'id',
            ],

            [
                'key'          => 'field_aorg_additional_languages',
                'label'        => 'Additional Languages',
                'name'         => 'ws_aorg_additional_languages',
                'type'         => 'text',
                'instructions' => 'List additional languages not in the checkbox list above (comma-separated). Saving a non-empty value here automatically assigns the "Additional" language term.',
            ],

            // ────────────────────────────────────────────────────────────────
            // Tab: Eligibility & Cost
            //
            // Critical information for a layperson evaluating whether this
            // organization can realistically help them. Cost model and
            // income limits are top concerns for financially stressed
            // whistleblowers considering retaliation.
            // ────────────────────────────────────────────────────────────────

            [
                'key'   => 'field_aorg_eligibility_cost_tab',
                'label' => 'Eligibility & Cost',
                'type'  => 'tab',
            ],

            [
                'key'           => 'field_aorg_cost_models',
                'label'         => 'Cost Structure',
                'name'          => 'ws_aorg_cost_models',
                'type'          => 'taxonomy',
                'taxonomy'      => 'ws_aorg_cost_model',
                'instructions'  => 'Select one or more cost models that apply to whistleblower services at this organization.',
                'required'      => 1,
                'field_type'    => 'multi_select',
                'add_term'      => 0,
                'save_terms'    => 1,
                'load_terms'    => 1,
                'return_format' => 'id',
                'allow_null'    => 0,
            ],

            [
                'key'           => 'field_aorg_has_income_limit',
                'label'         => 'Income Eligibility Required?',
                'name'          => 'ws_aorg_has_income_limit',
                'type'          => 'true_false',
                'instructions'  => 'Enable if this organization requires clients to meet income or financial eligibility criteria.',
                'ui'            => 1,
                'ui_on_text'    => 'Yes',
                'ui_off_text'   => 'No',
                'default_value' => 0,
            ],

            [
                'key'          => 'field_aorg_has_income_limit_details',
                'label'        => 'Income Eligibility Details',
                'name'         => 'ws_aorg_has_income_limit_details',
                'type'         => 'textarea',
                'instructions' => 'Describe the income thresholds or financial eligibility criteria — e.g., "Income must be below 200% of the federal poverty level."',
                'rows'         => 3,
                'conditional_logic' => [ [ [
                    'field'    => 'field_aorg_has_income_limit',
                    'operator' => '==',
                    'value'    => '1',
                ] ] ],
            ],

            [
                'key'           => 'field_aorg_accepts_anonymous',
                'label'         => 'Can Assist Anonymous Clients?',
                'name'          => 'ws_aorg_accepts_anonymous',
                'type'          => 'true_false',
                'instructions'  => 'Enable if this organization can provide meaningful assistance without requiring the client to disclose their identity.',
                'ui'            => 1,
                'ui_on_text'    => 'Yes',
                'ui_off_text'   => 'No',
                'default_value' => 0,
            ],

            [
                'key'          => 'field_aorg_eligibility_details',
                'label'        => 'Additional Eligibility Requirements',
                'name'         => 'ws_aorg_eligibility_details',
                'type'         => 'textarea',
                'instructions' => 'Describe any eligibility requirements not covered above — e.g., case type restrictions, geographic limits, employer size thresholds, or union membership requirements.',
                'rows'         => 4,
            ],

            // ────────────────────────────────────────────────────────────────
            // Tab: Credentials
            //
            // Helps laypeople assess whether this organization can provide
            // reliable legal guidance vs. general advocacy support.
            // ────────────────────────────────────────────────────────────────

            [
                'key'   => 'field_aorg_credentials_tab',
                'label' => 'Credentials',
                'type'  => 'tab',
            ],

            [
                'key'           => 'field_aorg_licensed_attorneys',
                'label'         => 'Licensed Attorneys on Staff?',
                'name'          => 'ws_aorg_licensed_attorneys',
                'type'          => 'true_false',
                'instructions'  => 'Enable if this organization employs licensed attorneys who can provide formal legal advice and representation.',
                'ui'            => 1,
                'ui_on_text'    => 'Yes',
                'ui_off_text'   => 'No',
                'default_value' => 0,
            ],

            [
                'key'          => 'field_aorg_accreditation',
                'label'        => 'Accreditation & Certifications',
                'name'         => 'ws_aorg_accreditation',
                'type'         => 'text',
                'instructions' => 'Any relevant professional accreditation or certifications — e.g., "ABA-accredited", "NQAP member", "DOJ-recognized".',
            ],

            [
                'key'          => 'field_aorg_bar_states',
                'label'        => 'State Bar Memberships',
                'name'         => 'ws_aorg_bar_states',
                'type'         => 'text',
                'instructions' => 'States where attorneys at this organization are bar-admitted — e.g., "CA, NY, DC, Federal".',
            ],

            [
                'key'          => 'field_aorg_legitimacy_url',
                'label'        => 'Legitimacy / Transparency URL',
                'name'         => 'ws_aorg_legitimacy_url',
                'type'         => 'url',
                'instructions' => 'Link to a page that verifies the organization\'s legitimacy — e.g., IRS Form 990, state bar directory, Charity Navigator, GuideStar.',
            ],

            // ── Tab: Authorship & Review ──────────────────────────────────
            // Removed — registered centrally in acf-stamp-fields.php
            // (group_stamp_metadata, menu_order 90).

            // ── Last Verified Date ────────────────────────────────────────
            //
            // Content-owned field — not a stamp. Retained here in the
            // assist-org's own group.

            [
                'key'            => 'field_aorg_last_reviewed',
                'label'          => 'Last Verified Date',
                'name'           => 'ws_aorg_last_reviewed',
                'type'           => 'date_picker',
                'instructions'   => 'Update this date each time the organization record is verified for accuracy.',
                'display_format' => 'F j, Y',
                'return_format'  => 'Y-m-d',
                'first_day'      => 1,
            ],

            // ────────────────────────────────────────────────────────────────
            // Tab: Internal Contact & Relationship Notes
            //
            // Private operator metadata for relationship building and
            // outreach continuity. Not surfaced in public output.
            // ────────────────────────────────────────────────────────────────

            [
                'key'   => 'field_aorg_internal_contact_tab',
                'label' => 'Internal Contact & Relationship Notes',
                'type'  => 'tab',
            ],

            [
                'key'          => 'field_aorg_internal_contact_name',
                'label'        => 'Internal Contact Name',
                'name'         => '_ws_aorg_internal_contact_name',
                'type'         => 'text',
                'instructions' => 'Internal-use only. Primary relationship contact at the organization. This field is never shown publicly.',
                'required'     => 0,
                'placeholder'  => 'Jane Doe',
            ],

            [
                'key'          => 'field_aorg_internal_contact_role',
                'label'        => 'Internal Contact Role/Title',
                'name'         => '_ws_aorg_internal_contact_role',
                'type'         => 'text',
                'instructions' => 'Internal-use only. Role/title for context during outreach.',
                'required'     => 0,
                'placeholder'  => 'Director of Intake',
            ],

            [
                'key'          => 'field_aorg_internal_contact_email',
                'label'        => 'Internal Contact Email',
                'name'         => '_ws_aorg_internal_contact_email',
                'type'         => 'email',
                'instructions' => 'Internal-use only. Direct working contact email (if available).',
                'required'     => 0,
            ],

            [
                'key'          => 'field_aorg_internal_contact_phone',
                'label'        => 'Internal Contact Phone',
                'name'         => '_ws_aorg_internal_contact_phone',
                'type'         => 'text',
                'instructions' => 'Internal-use only. Direct phone/extension for relationship follow-up.',
                'required'     => 0,
                'placeholder'  => '(555) 000-0000 x123',
            ],

            [
                'key'            => 'field_aorg_internal_last_contacted',
                'label'          => 'Internal Last Contacted',
                'name'           => '_ws_aorg_internal_last_contacted',
                'type'           => 'date_picker',
                'instructions'   => 'Internal-use only. Most recent direct outreach date.',
                'required'       => 0,
                'display_format' => 'F j, Y',
                'return_format'  => 'Y-m-d',
                'first_day'      => 1,
            ],

            [
                'key'          => 'field_aorg_internal_relationship_notes',
                'label'        => 'Internal Relationship Notes',
                'name'         => '_ws_aorg_internal_relationship_notes',
                'type'         => 'textarea',
                'instructions' => 'Internal-use only. Keep concise, factual notes for relationship continuity and follow-up context.',
                'required'     => 0,
                'rows'         => 4,
            ],

            // Plain-English fields are now provided by the shared
            // group_plain_english_metadata workflow group.

        ], // end fields

    ] ); // end acf_add_local_field_group

} // end ws_register_acf_assist_org


// Dynamic choice filter removed (Phase 3.2 / 12.1).
// ws_aorg_jurisdictions is now a taxonomy field — ACF loads terms natively.


// ── Conditional logic: taxonomy term-gated details fields ─────────────────────
//
// - disclosure_target_details appears when ws_aorg_disclosure_targets includes
//   term slug 'has-details'.
// - protected_class_details appears when ws_aorg_protected_classes includes
//   term slug 'has-details'.
// - case_stage_details appears when ws_aorg_case_stages includes term slug
//   'other'.


add_filter( 'acf/load_field', 'ws_aorg_details_conditional' );

function ws_aorg_details_conditional( $field ) {

    static $map = [
        'field_aorg_disclosure_target_details'  => [ 'ws_disclosure_target', 'field_aorg_disclosure_targets', 'has-details' ],
        'field_aorg_protected_class_details'    => [ 'ws_protected_class',   'field_aorg_protected_classes',  'has-details' ],
        'field_aorg_case_stage_details'         => [ 'ws_case_stage',        'field_aorg_case_stages',        'other'       ],
    ];

    if ( ! isset( $map[ $field['key'] ] ) ) {
        return $field;
    }

    [ $taxonomy, $trigger_key, $trigger_slug ] = $map[ $field['key'] ];

    $term = get_term_by( 'slug', $trigger_slug, $taxonomy );
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
