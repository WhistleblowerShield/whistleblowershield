<?php
/**
 * matrix-assist-orgs.php — Seeds nationwide and federal-scope whistleblower support organizations.
 *
 * @package    WhistleblowerShield
 * @since      3.0.0
 * @version    3.18.0
 * @author     Whistleblower Shield
 * @link       https://whistleblowershield.org
 * @copyright  Copyright (c) Whistleblower Shield
 */

defined( 'ABSPATH' ) || exit;

// ════════════════════════════════════════════════════════════════════════════
// Assist-Org Data
// ════════════════════════════════════════════════════════════════════════════

global $_ws_assist_org_matrix;
$_ws_assist_org_matrix = [
    [
        'internal_id'         => 'gov-accountability-proj-us',
        'common_name'         => 'GAP',
        'title'               => 'Government Accountability Project',
        'slug'                => 'government-accountability-project',
        'description'         => "The nation's leading whistleblower protection and advocacy organization. GAP uses legal representation, investigative journalism, and legislative advocacy to protect whistleblowers and promote institutional accountability.",
        'ws_aorg_website_url' => 'https://whistleblower.org',
        'ws_aorg_intake_url'  => 'https://whistleblower.org/intake/',
        'ws_aorg_contact_url' => 'https://whistleblower.org/about/contact/',
        'phones'              => [
            [ 'label' => 'Main Office', 'number' => '202-457-0034' ]
        ],
        'headquarters'        => '1612 K St NW, Suite 808, Washington, DC 20006-2802',
        'cost_model'          => 'Pro Bono / Donation-funded for public interest cases.',
        'case_stages'         => [ 'pre-report', 'post-report', 'retaliation-active', 'litigation' ],
        'services'            => [ 'legal-representation', 'advocacy', 'legislative-reform', 'public-interest-communication' ],
        'sectors'             => [ 'federal-government', 'corporate', 'national-security', 'scientific-integrity' ],
    ],
    [
        'internal_id'         => 'national-whistleblower-center-us',
        'common_name'         => 'NWC',
        'title'               => 'National Whistleblower Center',
        'slug'                => 'national-whistleblower-center',
        'description'         => 'A nonprofit advocacy group focusing on environmental, financial, and government fraud. NWC operates a major Attorney Referral Program to connect whistleblowers with specialized legal counsel for bounty-based programs.',
        'ws_aorg_website_url' => 'https://www.whistleblowers.org',
        'ws_aorg_intake_url'  => 'https://report-fraud-now.info',
        'ws_aorg_contact_url' => 'https://www.whistleblowers.org/contact-us/',
        'phones'              => [
            [ 'label' => 'Main Office', 'number' => '202-342-1903' ]
        ],
        'headquarters'        => 'Washington, D.C. (National Security/Legal Center)',
        'cost_model'          => 'Attorney Referral Program is free; attorneys donate only if case is won.',
        'case_stages'         => [ 'pre-report', 'post-report', 'litigation' ],
        'services'            => [ 'legal-referral', 'education', 'advocacy' ],
        'sectors'             => [ 'financial-fraud', 'environmental', 'government-contracting' ],
    ],
    [
        'internal_id'         => 'whistleblower-aid-us',
        'common_name'         => 'Whistleblower Aid',
        'title'               => 'Whistleblower Aid',
        'slug'                => 'whistleblower-aid',
        'description'         => 'Provides free legal and security services to individuals reporting lawbreaking in government and the private sector. Specializes in high-impact disclosures in the technology and national security fields.',
        'ws_aorg_website_url' => 'https://whistlebloweraid.org',
        'ws_aorg_intake_url'  => 'https://whistlebloweraid.org/contact/',
        'ws_aorg_contact_url' => '',
        'secure_channels'     => [
            'signal' => '+1 201-773-1371',
            'notes'  => 'Instruction to use personal devices only, never employer-owned hardware.'
        ],
        'phones'              => [],
        'headquarters'        => '1250 Connecticut Ave NW, Suite 700, Washington, DC 20036',
        'cost_model'          => 'Free legal representation for whistleblowers; donation-funded.',
        'case_stages'         => [ 'pre-report', 'post-report', 'retaliation-active' ],
        'services'            => [ 'legal-representation', 'digital-security', 'media-support' ],
        'sectors'             => [ 'technology', 'national-security', 'federal-government' ],
    ],
    [
        'internal_id'         => 'project-on-govt-oversight-us',
        'common_name'         => 'POGO',
        'title'               => 'Project On Government Oversight',
        'slug'                => 'project-on-government-oversight',
        'description'         => 'An independent watchdog that investigates corruption and abuse of power. POGO provides extensive resources for potential whistleblowers and trains congressional staff on handling disclosures.',
        'ws_aorg_website_url' => 'https://www.pogo.org',
        'ws_aorg_intake_url'  => 'https://www.pogo.org/report-corruption-and-waste',
        'ws_aorg_contact_url' => '',
        'phones'              => [],
        'headquarters'        => '1100 13th Street NW, Suite 800, Washington, DC 20005',
        'cost_model'          => 'Nonprofit watchdog; provides investigative support and guidance.',
        'case_stages'         => [ 'pre-report', 'post-report' ],
        'services'            => [ 'investigation', 'education', 'policy-advocacy' ],
        'sectors'             => [ 'federal-government', 'defense-procurement', 'national-security' ],
    ],
    [
        'internal_id'         => 'anti-fraud-coalition-us',
        'common_name'         => 'TAF',
        'title'               => 'The Anti-Fraud Coalition',
        'slug'                => 'anti-fraud-coalition',
        'description'         => 'Formerly Taxpayers Against Fraud, this coalition is the premier network for attorneys and whistleblowers involved in False Claims Act (qui tam) and financial fraud litigation.',
        'ws_aorg_website_url' => 'https://taf.org',
        'ws_aorg_intake_url'  => 'https://taf.org/whistleblower-resources/',
        'ws_aorg_contact_url' => '',
        'phones'              => [],
        'headquarters'        => '1220 19th St NW, Ste 501, Washington, DC 20036',
        'cost_model'          => 'Resource hub and legal directory for contingency-based litigation.',
        'case_stages'         => [ 'pre-report', 'litigation' ],
        'services'            => [ 'legal-directory', 'education', 'economic-research' ],
        'sectors'             => [ 'healthcare-fraud', 'financial-markets', 'government-procurement' ],
    ],
    [
        'internal_id'         => 'whistleblowers-of-america-us',
        'common_name'         => 'WoA',
        'title'               => 'Whistleblowers of America',
        'slug'                => 'whistleblowers-of-america',
        'description'         => 'Focuses on the psychosocial and mental health impact of retaliation. Provides peer support, mentorship, and documentation tools to help whistleblowers survive the trauma of workplace harassment.',
        'ws_aorg_website_url' => 'https://whistleblowersofamerica.org',
        'ws_aorg_intake_url'  => 'https://whistleblowersofamerica.org/contact/',
        'ws_aorg_contact_url' => '',
        'phones'              => [],
        'headquarters'        => 'Workplace Promise Institute (National Scope)',
        'cost_model'          => 'Donation/Membership based; peer support focus.',
        'case_stages'         => [ 'retaliation-active', 'post-report' ],
        'services'            => [ 'peer-support', 'mentorship', 'psychosocial-resilience', 'documentation-tools' ],
        'sectors'             => [ 'veterans-affairs', 'defense', 'general-workplace' ],
    ],
];

/**
 * Executes the seeding process for the matrix.
 */
function ws_matrix_seed_assist_orgs() {
    global $_ws_assist_org_matrix;

    $us_term = get_term_by( 'slug', 'us', WS_JURISDICTION_TAXONOMY );
    $us_term_id = $us_term ? $us_term->term_id : 0;

    foreach ( $_ws_assist_org_matrix as $org ) {
        
        $post_data = [
            'post_title'   => $org['title'],
            'post_name'    => $org['slug'],
            'post_content' => $org['description'],
            'post_status'  => 'publish',
            'post_type'    => 'ws_assist_org',
        ];

        // Check if record exists by internal_id to prevent duplicates
        $existing = get_posts([
            'post_type'  => 'ws_assist_org',
            'meta_key'   => 'ws_internal_id',
            'meta_value' => $org['internal_id'],
            'fields'     => 'ids',
        ]);

        if ( ! empty( $existing ) ) {
            $post_id = $existing[0];
            $post_data['ID'] = $post_id;
            wp_update_post( $post_data );
        } else {
            $post_id = wp_insert_post( $post_data );
        }

        if ( is_wp_error( $post_id ) ) continue;

        // ── Standard Meta ───────────────────────────────────────────────────
        update_post_meta( $post_id, 'ws_internal_id', $org['internal_id'] );
        update_post_meta( $post_id, 'ws_common_name', $org['common_name'] );
        update_post_meta( $post_id, 'ws_aorg_website_url', $org['ws_aorg_website_url'] );
        update_post_meta( $post_id, 'ws_aorg_intake_url', $org['ws_aorg_intake_url'] );
        update_post_meta( $post_id, 'ws_aorg_contact_url', $org['ws_aorg_contact_url'] );
        update_post_meta( $post_id, 'ws_headquarters', $org['headquarters'] );
        update_post_meta( $post_id, 'ws_cost_model', $org['cost_model'] );
        update_post_meta( $post_id, 'ws_phones', $org['phones'] );

        // ── Extended Meta (Secure Channels) ─────────────────────────────────
        if ( ! empty( $org['secure_channels'] ) ) {
            update_post_meta( $post_id, 'ws_secure_channels', $org['secure_channels'] );
        }

        // ── Taxonomies ──────────────────────────────────────────────────────
        
        // Case stages
        if ( ! empty( $org['case_stages'] ) ) {
            ws_matrix_assign_terms( $post_id, $org['case_stages'], 'ws_case_stage' );
        }

        // Services offered
        if ( ! empty( $org['services'] ) ) {
            ws_matrix_assign_terms( $post_id, $org['services'], 'ws_aorg_service' );
        }

        // Employment sectors
        if ( ! empty( $org['sectors'] ) ) {
            ws_matrix_assign_terms( $post_id, $org['sectors'], 'ws_employment_sector' );
        }

        // Default Languages
        ws_matrix_assign_terms( $post_id, ['english'], 'ws_language' );

        // Jurisdiction
        if ( $us_term_id ) {
            wp_set_object_terms( $post_id, $us_term_id, WS_JURISDICTION_TAXONOMY );
        }

        update_post_meta( $post_id, 'ws_matrix_source', 'matrix-assist-orgs' );
    }
}