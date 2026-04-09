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
 *   ws_aorg_cost_model             — taxonomy radio (save_terms: 1) — Phase 2 filter axis
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
 * @version 3.15.2
 *
 * VERSION
 * -------
 * 3.15.2  Contact & Intake secure-channel fields added:
 *         - ws_aorg_has_secure_channel (true/false)
 *         - ws_aorg_secure_contact_url (url)
 *         - ws_aorg_secure_contact_tool (text; e.g., Signal, SecureDrop)
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
 *         ws_ao_case_stage field added to Scope of Service tab.
 * 3.9.0   ws_case_stage taxonomy field added.
 * 3.7.0   ws_employment_sector converted from ACF checkbox to taxonomy field.
 *         ws_aorg_cost_model converted from select to taxonomy radio.
 * 3.0.0   ws_jx_code join retired; ws_jurisdiction taxonomy used throughout.
 * 1.0.0   Initial release.
 */

defined( 'ABSPATH' ) || exit;

add_action( 'acf/init', 'ws_register_acf_assist_org' );

function ws_register_acf_assist_org() {

    if ( ! function_exists( 'acf_add_local_field_group' ) ) {
        return;
    }

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
            // The post title serves as the public organization name.
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
                'key'           => 'field_aorg_type',
                'label'         => 'Organization Type',
                'name'          => 'ws_aorg_type',
                'type'          => 'taxonomy',
                'taxonomy'      => 'ws_aorg_type',
                'instructions'  => 'Select the category that best describes this organization.',
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
                'label'        => 'Whistleblower Focus Score',
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
                'key'          => 'field_aorg_whistleblower_note',
                'label'        => 'Scope Justification',
                'name'         => 'ws_aorg_whistleblower_note',
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
                'key'           => 'field_aorg_limited_scope',
                'label'         => 'Community / Local Scope',
                'name'          => 'ws_aorg_limited_scope',
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
                'key'           => 'field_aorg_jurisdiction',
                'label'         => 'Jurisdictions Served',
                'name'          => WS_JURISDICTION_TAXONOMY,
                'type'          => 'taxonomy',
                'taxonomy'      => WS_JURISDICTION_TAXONOMY,
                'field_type'    => 'checkbox',
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
                        'field'    => 'field_aorg_limited_scope',
                        'operator' => '==',
                        'value'    => '1',
                    ],
                ] ],
            ],

            [
                'key'           => 'field_aorg_disclosure_type',
                'label'         => 'Misconduct Categories Handled',
                'name'          => 'ws_aorg_disclosure_type',
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
                'taxonomy'      => 'ws_disclosure_targets',
                'field_type'    => 'checkbox',
                'instructions'  => 'Reporting channels this organization can help a whistleblower navigate or prepare for. Tag all that the org explicitly supports.',
                'add_term'      => 0,
                'save_terms'    => 1,
                'load_terms'    => 1,
                'return_format' => 'id',
            ],

            [
                'key'          => 'field_aorg_disclosure_targets_details',
                'label'        => 'Disclosure Targets Details',
                'name'         => 'ws_aorg_disclosure_targets_details',
                'type'         => 'textarea',
                'rows'         => 3,
                'instructions' => 'Describe any conditions, channel-specific expertise, or nuance in the reporting targets this organization supports.',
                // conditional_logic set dynamically — see ws_aorg_details_conditional()
            ],

            [
                'key'           => 'field_aorg_case_stage',
                'label'         => 'Case Stage',
                'name'          => 'ws_ao_case_stage',
                'type'          => 'taxonomy',
                'taxonomy'      => 'ws_case_stage',
                'field_type'    => 'checkbox',
                'instructions'  => 'Stage of a whistleblower\'s situation where this organization is most useful. Tag all that genuinely apply.',
                'add_term'      => 0,
                'save_terms'    => 1,
                'load_terms'    => 1,
                'return_format' => 'id',
            ],

            [
                'key'           => 'field_aorg_services',
                'label'         => 'Services Offered',
                'name'          => 'ws_aorg_services',
                'type'          => 'taxonomy',
                'taxonomy'      => 'ws_aorg_service',
                'instructions'  => 'Select all services this organization provides to whistleblowers.',
                'required'      => 1,
                'field_type'    => 'checkbox',
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
                'field_type'    => 'checkbox',
                'instructions'  => 'Select the employment sectors this organization serves. Leave blank if all sectors are accepted.',
                'required'      => 0,
                'add_term'      => 0,
                'save_terms'    => 1,
                'load_terms'    => 1,
                'return_format' => 'id',
                'allow_null'    => 1,
            ],

            // ────────────────────────────────────────────────────────────────
            // Tab: Contact & Intake
            //
            // How a whistleblower reaches this organization. All fields
            // except website_url are optional — not all organizations
            // publish every channel.
            // ────────────────────────────────────────────────────────────────

            [
                'key'   => 'field_aorg_contact_tab',
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
                'label'        => 'Intake / Contact Form URL',
                'name'         => 'ws_aorg_intake_url',
                'type'         => 'url',
                'instructions' => 'Direct link to an intake form, referral request, or secure contact page — if different from the main website.',
            ],

            [
                'key'          => 'field_aorg_phone',
                'label'        => 'Phone Number',
                'name'         => 'ws_aorg_phone',
                'type'         => 'text',
                'instructions' => 'Public-facing phone number for whistleblower inquiries.',
                'placeholder'  => '(555) 000-0000',
            ],

            [
                'key'          => 'field_aorg_email',
                'label'        => 'Contact Email',
                'name'         => 'ws_aorg_email',
                'type'         => 'email',
                'instructions' => 'Public contact email address for whistleblower inquiries.',
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
                'instructions' => 'Direct URL to the secure contact method page.',
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
                'type'         => 'text',
                'instructions' => 'Name of secure contact method. Example: "Signal", "SecureDrop", "ProtonMail".',
                'placeholder'  => 'Signal',
                'conditional_logic' => [ [ [
                    'field'    => 'field_aorg_has_secure_channel',
                    'operator' => '==',
                    'value'    => '1',
                ] ] ],
            ],

            [
                'key'           => 'field_aorg_languages',
                'label'         => 'Languages Served',
                'name'          => 'ws_languages',
                'type'          => 'taxonomy',
                'taxonomy'      => 'ws_languages',
                'field_type'    => 'checkbox',
                'instructions'  => 'Select languages this organization can serve. Check "Additional" if other languages are available — then specify them below.',
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
                'key'   => 'field_aorg_eligibility_tab',
                'label' => 'Eligibility & Cost',
                'type'  => 'tab',
            ],

            [
                'key'           => 'field_aorg_cost_model',
                'label'         => 'Cost Structure',
                'name'          => 'ws_aorg_cost_model',
                'type'          => 'taxonomy',
                'taxonomy'      => 'ws_aorg_cost_model',
                'instructions'  => 'Select the primary cost model for whistleblower services at this organization.',
                'required'      => 1,
                'field_type'    => 'radio',
                'add_term'      => 0,
                'save_terms'    => 1,
                'load_terms'    => 1,
                'return_format' => 'id',
                'allow_null'    => 0,
            ],

            [
                'key'           => 'field_aorg_income_limit',
                'label'         => 'Income Eligibility Required?',
                'name'          => 'ws_aorg_income_limit',
                'type'          => 'true_false',
                'instructions'  => 'Enable if this organization requires clients to meet income or financial eligibility criteria.',
                'ui'            => 1,
                'ui_on_text'    => 'Yes',
                'ui_off_text'   => 'No',
                'default_value' => 0,
            ],

            [
                'key'          => 'field_aorg_income_limit_notes',
                'label'        => 'Income Eligibility Details',
                'name'         => 'ws_aorg_income_limit_notes',
                'type'         => 'textarea',
                'instructions' => 'Describe the income thresholds or financial eligibility criteria — e.g., "Income must be below 200% of the federal poverty level."',
                'rows'         => 3,
                'conditional_logic' => [ [ [
                    'field'    => 'field_aorg_income_limit',
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
                'key'          => 'field_aorg_eligibility_notes',
                'label'        => 'Additional Eligibility Requirements',
                'name'         => 'ws_aorg_eligibility_notes',
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
                'label'        => 'Accreditations & Certifications',
                'name'         => 'ws_aorg_accreditation',
                'type'         => 'text',
                'instructions' => 'Any relevant professional accreditations or certifications — e.g., "ABA-accredited", "NQAP member", "DOJ-recognized".',
            ],

            [
                'key'          => 'field_aorg_bar_states',
                'label'        => 'State Bar Memberships',
                'name'         => 'ws_aorg_bar_states',
                'type'         => 'text',
                'instructions' => 'States where attorneys at this organization are bar-admitted — e.g., "CA, NY, DC, Federal".',
            ],

            [
                'key'          => 'field_aorg_verify_url',
                'label'        => 'Verification / Transparency URL',
                'name'         => 'ws_aorg_verify_url',
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
            // Tab: Internal Relationship
            //
            // Private operator metadata for relationship building and
            // outreach continuity. Not surfaced in public output.
            // ────────────────────────────────────────────────────────────────

            [
                'key'   => 'field_aorg_internal_rel_tab',
                'label' => 'Internal Relationship',
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

            // ── Tab: Plain Language ───────────────────────────────────────
            // Removed entirely — ws-assist-org content is plain language
            // by nature; the plain language workflow does not apply.

        ], // end fields

    ] ); // end acf_add_local_field_group

} // end ws_register_acf_assist_org


// Dynamic choice filter removed (Phase 3.2 / 12.1).
// ws_jurisdiction is now a taxonomy field — ACF loads terms natively.


// ── Conditional logic: has-details sentinel ───────────────────────────────────
//
// When the 'has-details' term is selected in ws_aorg_disclosure_targets,
// the companion _details textarea becomes visible.

add_filter( 'acf/load_field', 'ws_aorg_details_conditional' );

function ws_aorg_details_conditional( $field ) {

    static $map = [
        'field_aorg_disclosure_targets_details' => [ 'ws_disclosure_targets', 'field_aorg_disclosure_targets' ],
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
