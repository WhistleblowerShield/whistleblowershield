<?php
/**
 * ACF fields for ws-assist-org.
 *
 * This group is intentionally plain: it exposes the meta and taxonomy targets
 * used by the assist-org matrix and custom ingest. No legacy aliases, no
 * derived display fields, no rescue layer.
 *
 * @package WhistleblowerShield
 */

defined( 'ABSPATH' ) || exit;

add_action( 'acf/init', 'ws_register_acf_assist_org' );

function ws_aorg_choice_map( array $values ): array {
    return array_combine( $values, array_map( static function( string $value ): string {
        return ucwords( str_replace( '-', ' ', $value ) );
    }, $values ) );
}

function ws_aorg_constant_choices( string $constant, array $fallback ): array {
    return ws_aorg_choice_map( defined( $constant ) && is_array( constant( $constant ) ) ? constant( $constant ) : $fallback );
}

function ws_aorg_text_field( string $key, string $label, string $name, int $required = 0 ): array {
    return [
        'key'      => $key,
        'label'    => $label,
        'name'     => $name,
        'type'     => 'text',
        'required' => $required,
    ];
}

function ws_aorg_url_field( string $key, string $label, string $name, int $required = 0 ): array {
    return [
        'key'      => $key,
        'label'    => $label,
        'name'     => $name,
        'type'     => 'url',
        'required' => $required,
    ];
}

function ws_aorg_true_false_field( string $key, string $label, string $name ): array {
    return [
        'key'     => $key,
        'label'   => $label,
        'name'    => $name,
        'type'    => 'true_false',
        'ui'      => 1,
        'message' => '',
    ];
}

function ws_aorg_select_field( string $key, string $label, string $name, array $choices, int $required = 0 ): array {
    return [
        'key'           => $key,
        'label'         => $label,
        'name'          => $name,
        'type'          => 'select',
        'choices'       => $choices,
        'required'      => $required,
        'allow_null'    => $required ? 0 : 1,
        'ui'            => 1,
        'return_format' => 'value',
    ];
}

function ws_aorg_taxonomy_field( string $key, string $label, string $name, string $taxonomy, string $field_type, int $required = 0 ): array {
    return [
        'key'           => $key,
        'label'         => $label,
        'name'          => $name,
        'type'          => 'taxonomy',
        'taxonomy'      => $taxonomy,
        'field_type'    => $field_type,
        'required'      => $required,
        'add_term'      => 0,
        'save_terms'    => 1,
        'load_terms'    => 1,
        'return_format' => 'id',
    ];
}

function ws_register_acf_assist_org() {
    if ( ! function_exists( 'acf_add_local_field_group' ) ) {
        return;
    }

    $phone_type_choices = ws_aorg_constant_choices( 'WS_SCHEMA_PHONE_TYPE', [
        'hotline',
        'intake',
        'headquarters',
        'regional',
        'tty',
        'fax',
        'secure',
        'other',
    ] );

    $email_type_choices = ws_aorg_constant_choices( 'WS_SCHEMA_EMAIL_TYPE', [
        'intake',
        'general',
        'legal',
        'media',
        'support',
        'secure',
        'other',
    ] );

    $secure_tool_choices = ws_aorg_constant_choices( 'WS_SCHEMA_SECURE_TOOL', [
        'securedrop',
        'globaleaks',
        'signal',
        'protonmail',
        'tutanota',
        'pgp-email',
        'wire',
        'keybase',
        'encrypted-web-form',
        'other',
    ] );

    acf_add_local_field_group( [
        'key'                   => 'group_assist_org_metadata',
        'title'                 => 'Assistance Organization Details',
        'menu_order'            => 0,
        'position'              => 'normal',
        'style'                 => 'default',
        'label_placement'       => 'top',
        'instruction_placement' => 'label',
        'active'                => true,
        'location'              => [ [ [
            'param'    => 'post_type',
            'operator' => '==',
            'value'    => 'ws-assist-org',
        ] ] ],
        'fields'                => [
            [
                'key'   => 'field_aorg_tab_identity',
                'label' => 'Identity',
                'type'  => 'tab',
            ],
            ws_aorg_text_field( 'field_aorg_id', 'Internal ID', '_ws_aorg_id', 1 ),
            ws_aorg_text_field( 'field_aorg_official_name', 'Official Name', 'ws_aorg_official_name', 1 ),
            ws_aorg_text_field( 'field_aorg_common_name', 'Common Name', 'ws_aorg_common_name' ),
            ws_aorg_url_field( 'field_aorg_homepage_url', 'Official Homepage URL', 'ws_aorg_official_homepage_url', 1 ),
            ws_aorg_text_field( 'field_aorg_homepage_verified_date', 'Homepage Verified Date', 'ws_aorg_homepage_verified_date' ),
            ws_aorg_taxonomy_field( 'field_aorg_organization_model', 'Organization Model', 'ws_aorg_organization_model', 'ws_organization_model', 'radio', 1 ),

            [
                'key'   => 'field_aorg_tab_contact',
                'label' => 'Contact',
                'type'  => 'tab',
            ],
            ws_aorg_url_field( 'field_aorg_intake_url', 'Intake URL', 'ws_aorg_intake_url' ),
            ws_aorg_url_field( 'field_aorg_lawyers_url', 'Lawyers URL', 'ws_aorg_lawyers_url' ),
            ws_aorg_url_field( 'field_aorg_contact_url', 'Contact URL', 'ws_aorg_contact_url' ),
            [
                'key'   => 'field_aorg_mailing_address',
                'label' => 'Mailing Address',
                'name'  => 'ws_aorg_mailing_address',
                'type'  => 'textarea',
                'rows'  => 3,
            ],
            ws_aorg_true_false_field( 'field_aorg_social_presence', 'Social Links Found', 'ws_aorg_social_presence' ),
            [
                'key'          => 'field_aorg_social_links',
                'label'        => 'Social Links',
                'name'         => 'ws_aorg_social_links',
                'type'         => 'repeater',
                'layout'       => 'table',
                'button_label' => 'Add Social Link',
                'sub_fields'   => [
                    ws_aorg_text_field( 'field_aorg_social_platform', 'Platform', 'social_platform' ),
                    ws_aorg_url_field( 'field_aorg_social_url', 'URL', 'social_url' ),
                    ws_aorg_true_false_field( 'field_aorg_social_is_contact', 'Contact Path', 'social_is_contact' ),
                ],
            ],
            [
                'key'          => 'field_aorg_phones',
                'label'        => 'Phones',
                'name'         => 'ws_aorg_phones',
                'type'         => 'repeater',
                'layout'       => 'table',
                'button_label' => 'Add Phone',
                'sub_fields'   => [
                    ws_aorg_select_field( 'field_aorg_phone_type', 'Type', 'phone_type', $phone_type_choices, 1 ),
                    ws_aorg_text_field( 'field_aorg_phone_number', 'Number', 'phone_number', 1 ),
                    ws_aorg_url_field( 'field_aorg_phone_url', 'Source URL', 'phone_url', 1 ),
                ],
            ],
            [
                'key'          => 'field_aorg_emails',
                'label'        => 'Emails',
                'name'         => 'ws_aorg_emails',
                'type'         => 'repeater',
                'layout'       => 'table',
                'button_label' => 'Add Email',
                'sub_fields'   => [
                    ws_aorg_select_field( 'field_aorg_email_type', 'Type', 'email_type', $email_type_choices, 1 ),
                    [
                        'key'      => 'field_aorg_email_address',
                        'label'    => 'Address',
                        'name'     => 'email_address',
                        'type'     => 'email',
                        'required' => 1,
                    ],
                    ws_aorg_url_field( 'field_aorg_email_url', 'Source URL', 'email_url', 1 ),
                ],
            ],

            [
                'key'   => 'field_aorg_tab_secure',
                'label' => 'Secure Channels',
                'type'  => 'tab',
            ],
            ws_aorg_true_false_field( 'field_aorg_has_secure_channel', 'Has Secure Channel', 'ws_aorg_has_secure_channel' ),
            [
                'key'          => 'field_aorg_secure_channels',
                'label'        => 'Secure Channels',
                'name'         => 'ws_aorg_secure_channels',
                'type'         => 'repeater',
                'layout'       => 'table',
                'button_label' => 'Add Secure Channel',
                'sub_fields'   => [
                    ws_aorg_select_field( 'field_aorg_secure_channel_tool', 'Tool', 'channel_tool', $secure_tool_choices, 1 ),
                    ws_aorg_url_field( 'field_aorg_secure_channel_url', 'URL', 'channel_url', 1 ),
                    ws_aorg_text_field( 'field_aorg_secure_channel_label', 'Label', 'channel_label', 1 ),
                    ws_aorg_select_field( 'field_aorg_secure_channel_class', 'Class', 'channel_class', [
                        'two-way-support' => 'Two Way Support',
                        'tip-drop'        => 'Tip Drop',
                    ], 1 ),
                ],
            ],

            [
                'key'   => 'field_aorg_tab_service',
                'label' => 'Service',
                'type'  => 'tab',
            ],
            ws_aorg_true_false_field( 'field_aorg_is_nationwide', 'Nationwide', 'ws_aorg_is_nationwide' ),
            [
                'key'      => 'field_aorg_whistleblower_scope',
                'label'    => 'Whistleblower Scope',
                'name'     => 'ws_aorg_whistleblower_scope',
                'type'     => 'number',
                'required' => 1,
                'min'      => 0,
                'max'      => 3,
                'step'     => 1,
            ],
            ws_aorg_select_field( 'field_aorg_income_screening', 'Income Screening', 'ws_aorg_income_screening', [
                'not-required' => 'Not Required',
                'required'     => 'Required',
                'varies'       => 'Varies',
            ] ),
            ws_aorg_select_field( 'field_aorg_eligibility_status', 'Eligibility Status', 'ws_aorg_eligibility_status', [
                'open-to-public' => 'Open To Public',
                'restricted'     => 'Restricted',
            ] ),
            ws_aorg_select_field( 'field_aorg_service_depth', 'Service Depth', 'ws_aorg_service_depth', [
                'direct-representation' => 'Direct Representation',
                'ongoing-support'       => 'Ongoing Support',
                'warm-handoff'          => 'Warm Handoff',
                'referral-only'         => 'Referral Only',
                'triage-only'           => 'Triage Only',
                'peer-support'          => 'Peer Support',
                'information-only'      => 'Information Only',
            ] ),
            ws_aorg_select_field( 'field_aorg_intake_commitment_class', 'Intake Commitment Class', 'ws_aorg_intake_commitment_class', [
                'personal-help-request' => 'Personal Help Request',
                'screening-form'        => 'Screening Form',
                'referral-request'      => 'Referral Request',
                'peer-support-request'  => 'Peer Support Request',
                'general-contact-only'  => 'General Contact Only',
                'information-only'      => 'Information Only',
                'tip-submission-only'   => 'Tip Submission Only',
            ] ),

            [
                'key'   => 'field_aorg_tab_legal',
                'label' => 'Legal',
                'type'  => 'tab',
            ],
            ws_aorg_true_false_field( 'field_aorg_has_attorneys', 'Has Attorneys', 'ws_aorg_has_attorneys' ),
            ws_aorg_true_false_field( 'field_aorg_anonymous_pre_consult_status', 'Anonymous Pre-Consult', 'ws_aorg_anonymous_pre_consult_status' ),
            ws_aorg_select_field( 'field_aorg_attorney_role', 'Attorney Role', 'ws_aorg_attorney_role', [
                'direct-representation' => 'Direct Representation',
                'consultation-only'     => 'Consultation Only',
                'referral-panel'        => 'Referral Panel',
                'policy-only'           => 'Policy Only',
            ] ),
            ws_aorg_select_field( 'field_aorg_legal_representation_status', 'Legal Representation Status', 'ws_aorg_legal_representation_status', [
                'available'      => 'Available',
                'limited'        => 'Limited',
                'referral-only'  => 'Referral Only',
                'not-available'  => 'Not Available',
            ] ),

            [
                'key'   => 'field_aorg_tab_taxonomies',
                'label' => 'Taxonomies',
                'type'  => 'tab',
            ],
            ws_aorg_taxonomy_field( 'field_aorg_cost_models', 'Cost Models', 'ws_aorg_cost_models', 'ws_aorg_cost_model', 'multi_select' ),
            ws_aorg_taxonomy_field( 'field_aorg_services', 'Services', 'ws_aorg_services', 'ws_aorg_service', 'multi_select', 1 ),
            ws_aorg_taxonomy_field( 'field_aorg_employment_sectors', 'Employment Sectors', 'ws_aorg_employment_sectors', 'ws_employment_sector', 'multi_select' ),
            ws_aorg_taxonomy_field( 'field_aorg_protected_classes', 'Protected Classes', 'ws_aorg_protected_classes', 'ws_protected_class', 'multi_select' ),
            ws_aorg_taxonomy_field( 'field_aorg_protected_disclosures', 'Protected Disclosures', 'ws_aorg_protected_disclosures', 'ws_protected_disclosure', 'multi_select' ),
            ws_aorg_taxonomy_field( 'field_aorg_disclosure_targets', 'Disclosure Targets', 'ws_aorg_disclosure_targets', 'ws_disclosure_target', 'multi_select' ),
            ws_aorg_taxonomy_field( 'field_aorg_case_stages', 'Case Stages', 'ws_aorg_case_stages', 'ws_case_stage', 'multi_select' ),
            ws_aorg_taxonomy_field( 'field_aorg_languages', 'Languages', 'ws_aorg_languages', 'ws_language', 'multi_select' ),
            [
                'key'   => 'field_aorg_language_details',
                'label' => 'Language Details',
                'name'  => 'ws_aorg_language_details',
                'type'  => 'textarea',
                'rows'  => 3,
            ],

            [
                'key'   => 'field_aorg_tab_ingest',
                'label' => 'Ingest',
                'type'  => 'tab',
            ],
            ws_aorg_text_field( 'field_aorg_matrix_source', 'Matrix / Ingest Source', 'ws_matrix_source' ),
            ws_aorg_text_field( 'field_aorg_ingest_batch_id', 'Ingest Batch ID', '_ws_ingest_batch_id' ),
            ws_aorg_text_field( 'field_aorg_ingest_source_file', 'Ingest Source File', '_ws_ingest_source_file' ),
            ws_aorg_text_field( 'field_aorg_ingest_date', 'Ingest Date', 'ws_aorg_ingest_date' ),
        ],
    ] );
}
