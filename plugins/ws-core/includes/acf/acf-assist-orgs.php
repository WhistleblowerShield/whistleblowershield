<?php
/**
 * acf-assist-orgs.php — ACF Pro fields for the ws-assist-org CPT.
 *
 * Group key: group_assist_org_metadata
 * Stamp fields: group_stamp_metadata (acf-stamp-fields.php, menu_order 90)
 * Source verify: group_source_verify_metadata (acf-source-verify.php)
 *
 * Tabs: Identity | Scope of Service | Contact | Eligibility & Cost | Credentials
 *
 * Key fields:
 *   ws_aorg_serves_nationwide      — true = surfaces in nationwide directory query
 *   ws_aorg_whistleblower_scope    — integer 1-3; drives base score in ws_filter_score_org()
 *   ws_jurisdiction                — taxonomy field (save_terms: 1)
 *   ws_languages                   — taxonomy field; additional_languages text triggers
 *                                    'additional' term auto-assign via admin-hooks.php
 *   ws_aorg_cost_models            — taxonomy multi-select (save_terms: 1) — Phase 2 filter axis
 *   ws_employment_sector           — taxonomy (save_terms: 1) — Phase 2 filter axis
 *   ws_case_stage                  — taxonomy (save_terms: 1) — Phase 2 filter axis
 *
 * META KEY NOTE
 * -------------
 * ws_aorg_internal_id is stored WITHOUT a leading underscore. ACF uses the
 * _ws_aorg_internal_id key (underscore prefix) for its own internal field
 * reference — writing a value there clobbers ACF's mapping. Ingest must write
 * to ws_aorg_internal_id. The leading underscore in prompt JSON schema output
 * is a naming convention only; ingest strips it during mapping.
 *
 * @package WhistleblowerShield
 * @since   1.0.0
 * @version 3.16.6
 *
 * VERSION
 * -------
 * 3.16.6  Added ws_aorg_case_stage_details textarea field.
 *         Conditional display: appears when ws_aorg_case_stages includes
 *         the ws_case_stage term slug `other`.
 * 3.16.5  Secure contact tool canonicalization:
 *         - ws_aorg_secure_contact_tool now uses WS_SCHEMA_SECURE_TOOL select
 *         - ws_aorg_secure_contact_tool_other added (conditional when tool = other)
 * 3.16.4  Eligibility & cost schema correction:
 *         - ws_aorg_cost_models remains plural and now uses taxonomy checkbox
 *           (multi-select) to support multiple cost structures per organization
 *         - ws_aorg_income_limit_notes retained for income-specific criteria
 *         - ws_aorg_eligibility_notes retained for non-income eligibility constraints
 * 3.16.3  Meta naming rule alignment pass (ACF schema only):
 *         - Multi-value jurisdiction field name pluralized:
 *           WS_JURISDICTION_TAXONOMY (ws_jurisdiction) -> ws_jurisdictions
 *         - URL field naming in this group already compliant (_url suffix only on URL fields)
 * 3.16.2  Naming normalization:
 *         - ws_ao_case_stage fully replaced by ws_aorg_case_stages
 *         - ws_aorg_disclosure_type fully replaced by ws_aorg_disclosure_types
 *         - ws_aorg_process_types retained as multi-select taxonomy field
 * 3.16.1  Process Types field added to Scope of Service tab:
 *         - ws_process_type taxonomy (checkbox, save_terms/load_terms)
 *         Query layer now returns process_types in assist-org payloads.
 * 3.16.0  ws_aorg_official_name (text) added to Identity tab immediately after
 *         ws_aorg_internal_id. Stores the full official organization name as a
 *         dedicated meta field. post_title mirrors this value at ingest time but
 *         ws_aorg_official_name is the authoritative data-layer source, consistent
 *         with how all other CPTs store their official name in a dedicated meta key.
 * 3.15.2  Contact & Intake secure-channel fields added:
 *         - ws_aorg_has_secure_channel (true/false)
 *         - ws_aorg_secure_contact_url (url)
 *         - ws_aorg_secure_contact_tool (text; e.g., Signal, SecureDrop)
 * 3.16.0  Contact model refactor:
 *         - ws_aorg_intake_url and ws_aorg_contact_url are separate fields
 *           (intake and contact are distinct concepts in render logic)
 *         - ws_aorg_phone replaced by ws_aorg_phones repeater:
 *             ws_aorg_phone_type, ws_aorg_phone_number
 *         - ws_aorg_email replaced by ws_aorg_emails repeater:
 *             ws_aorg_email_type, ws_aorg_email_address
 * 3.15.1  ws_aorg_whistleblower_scope (number 1-3) added to Scope of Service tab.
 *         ws_aorg_whistleblower_note (textarea) added — editorial justification for scope.
 *         ws_aorg_common_name (text) added to Identity tab.
 * 3.12.4  Removed explicit ws_aorg_federal_only field.
 *         Federal-only status is derived from scope rules:
 *         serves_nationwide = 0 and jurisdiction = ['us'].
 * 3.12.3  Scope controls expanded:
 *         - ws_aorg_serves_nationwide explicitly treated as 57-jurisdiction flag.
 *         - ws_aorg_limited_scope flag added; ws_aorg_community_scope now
 *           appears only for limited, non-nationwide orgs.
 * 3.12.2  ws_aorg_community_scope field added to Scope of Service tab.
 * 3.12.1  Internal Relationship tab added for non-public org contact fields.
 * 3.12.0  ws_aorg_disclosure_targets field added to Scope of Service tab.
 *         ws_aorg_case_stages (legacy key: ws_ao_case_stage) added to Scope of Service tab.
 * 3.9.0   ws_case_stage taxonomy field added.
 * 3.7.0   ws_employment_sector converted from ACF checkbox to taxonomy field.
 *         ws_aorg_cost_models converted from select to taxonomy multi-select.
 * 3.0.0   ws_jx_code join retired; ws_jurisdiction taxonomy used throughout.
 * 1.0.0   Initial release.
 */

defined( 'ABSPATH' ) || exit;

add_action( 'acf/init', 'ws_register_acf_assist_org' );

function ws_register_acf_assist_org() {

    if ( ! function_exists( 'acf_add_local_field_group' ) ) {
        return;
    }

    $phone_type_choices  = array_combine( WS_SCHEMA_PHONE_TYPE, WS_SCHEMA_PHONE_TYPE );
    $email_type_choices  = array_combine( WS_SCHEMA_EMAIL_TYPE, WS_SCHEMA_EMAIL_TYPE );
    $secure_tool_choices = array_combine( WS_SCHEMA_SECURE_TOOL, WS_SCHEMA_SECURE_TOOL );

    acf_add_local_field_group( [

        'key'                   => 'group_aorg_metadata',
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
                'name'         => 'ws_aorg_internal_id',
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
                'instructions' => 'Plain-language overview of this organization\'s mission, focus areas, and typical whistleblower support.',
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
                'key'           => 'field_aorg_disclosure_types',
                'label'         => 'Misconduct Categories Handled',
                'name'          => 'ws_aorg_disclosure_types',
                'type'          => 'taxonomy',
                'taxonomy'      => 'ws_disclosure_type',
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
                'taxonomy'      => 'ws_aorg_protected_classes',
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
                'key'          => 'field_aorg_income_limit_details',
                'label'        => 'Income Eligibility Details',
                'name'         => 'ws_aorg_income_limit_details',
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
                'name'         => 'ws_aorg_internal_contact_name',
                'type'         => 'text',
                'instructions' => 'Internal-use only. Primary relationship contact at the organization. This field is never shown publicly.',
                'required'     => 0,
                'placeholder'  => 'Jane Doe',
            ],

            [
                'key'          => 'field_aorg_internal_contact_role',
                'label'        => 'Internal Contact Role/Title',
                'name'         => 'ws_aorg_internal_contact_role',
                'type'         => 'text',
                'instructions' => 'Internal-use only. Role/title for context during outreach.',
                'required'     => 0,
                'placeholder'  => 'Director of Intake',
            ],

            [
                'key'          => 'field_aorg_internal_contact_email',
                'label'        => 'Internal Contact Email',
                'name'         => 'ws_aorg_internal_contact_email',
                'type'         => 'email',
                'instructions' => 'Internal-use only. Direct working contact email (if available).',
                'required'     => 0,
            ],

            [
                'key'          => 'field_aorg_internal_contact_phone',
                'label'        => 'Internal Contact Phone',
                'name'         => 'ws_aorg_internal_contact_phone',
                'type'         => 'text',
                'instructions' => 'Internal-use only. Direct phone/extension for relationship follow-up.',
                'required'     => 0,
                'placeholder'  => '(555) 000-0000 x123',
            ],

            [
                'key'            => 'field_aorg_internal_last_contacted',
                'label'          => 'Internal Last Contacted',
                'name'           => 'ws_aorg_internal_last_contacted',
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
                'name'         => 'ws_aorg_internal_relationship_notes',
                'type'         => 'textarea',
                'instructions' => 'Internal-use only. Keep concise, factual notes for relationship continuity and follow-up context.',
                'required'     => 0,
                'rows'         => 4,
            ],

            // Plain Language fields are now provided by the shared
            // group_plain_english_metadata workflow group.

        ], // end fields

    ] ); // end acf_add_local_field_group

} // end ws_register_acf_assist_org


// Dynamic choice filter removed (Phase 3.2 / 12.1).
// ws_jurisdiction is now a taxonomy field — ACF loads terms natively.


// ── Conditional logic: taxonomy term-gated details fields ─────────────────────
//
// - disclosure_targets_details appears when ws_disclosure_target has term
//   slug 'has-details'.
// - protected_class_details appears when ws_protected_classes has term
//   slug 'has-details'.
// - case_stage_details appears when ws_case_stage has term slug 'other'.

add_filter( 'acf/load_field', 'ws_aorg_details_conditional' );

function ws_aorg_details_conditional( $field ) {

    static $map = [
        'field_aorg_disclosure_target_details'  => [ 'ws_disclosure_target', 'field_aorg_disclosure_targets', 'has-details' ],
        'field_aorg_protected_class_details'    => [ 'ws_protected_classes', 'field_aorg_protected_classes',  'has-details' ],
        'field_aorg_case_stage_details'         => [ 'ws_case_stage',        'field_aorg_case_stages',        'other' ],
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
