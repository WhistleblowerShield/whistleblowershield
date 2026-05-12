<?php
/**
 * matrix-agencies.php — Seeds nationwide federal agencies relevant to whistleblower protection.
 *
 * @package    WhistleblowerShield
 * @since      3.0.0
 * @version    3.20.0
 * @author     Whistleblower Shield
 * @link       https://whistleblowershield.org
 * @copyright  Copyright (c) Whistleblower Shield
 * 
 */

defined( 'ABSPATH' ) || exit;


// ════════════════════════════════════════════════════════════════════════════
// Agency Data
// ════════════════════════════════════════════════════════════════════════════

global $_ws_agency_matrix;
$_ws_agency_matrix = [

    [
        'title'              => 'U.S. Securities and Exchange Commission',
        'slug'               => 'sec-whistleblower-program',
        'official_name'     => 'U.S. Securities and Exchange Commission',
        'common_name'  => 'SEC',
        'url'      => 'https://www.sec.gov/whistleblower',
        'mission'  => 'Administers the SEC Whistleblower Program under Dodd-Frank, awarding 10–30% of sanctions over $1 million to eligible whistleblowers.',
    ],

    [
        'title'              => 'Occupational Safety and Health Administration',
        'slug'               => 'osha-whistleblower-protection-program',
        'official_name'     => 'Occupational Safety and Health Administration',
        'common_name'  => 'OSHA',
        'url'      => 'https://www.whistleblowers.gov',
        'mission'  => 'Investigates retaliation complaints under 25+ federal statutes including Sarbanes-Oxley, Clean Air Act, and STAA.',
    ],

    [
        'title'              => 'U.S. Office of Special Counsel',
        'slug'               => 'office-of-special-counsel',
        'official_name'     => 'U.S. Office of Special Counsel',
        'common_name'  => 'OSC',
        'url'      => 'https://osc.gov',
        'mission'  => 'Receives disclosures from federal employees, investigates prohibited personnel practices, and enforces the Whistleblower Protection Act.',
    ],

    [
        'title'              => 'Merit Systems Protection Board',
        'slug'               => 'merit-systems-protection-board',
        'official_name'     => 'Merit Systems Protection Board',
        'common_name'  => 'MSPB',
        'url'      => 'https://www.mspb.gov',
        'mission'  => 'Adjudicates federal employee appeals including individual right of action (IRA) cases under the Whistleblower Protection Act.',
    ],

    [
        'title'              => 'National Labor Relations Board',
        'slug'               => 'national-labor-relations-board',
        'official_name'     => 'National Labor Relations Board',
        'common_name'  => 'NLRB',
        'url'      => 'https://www.nlrb.gov',
        'mission'  => 'Protects the right of private-sector employees to act collectively, which may include whistleblowing in concerted protected activity.',
    ],

    [
        'title'              => 'Commodity Futures Trading Commission',
        'slug'               => 'cftc-whistleblower-program',
        'official_name'     => 'Commodity Futures Trading Commission',
        'common_name'  => 'CFTC',
        'url'      => 'https://www.whistleblower.gov',
        'mission'  => 'Administers the CFTC Whistleblower Program, providing awards to eligible whistleblowers reporting violations of the Commodity Exchange Act.',
    ],

    [
        'title'              => 'Internal Revenue Service Whistleblower Office',
        'slug'               => 'irs-whistleblower-office',
        'official_name'     => 'Internal Revenue Service — Whistleblower Office',
        'common_name'  => 'IRS WO',
        'url'      => 'https://www.irs.gov/compliance/whistleblower-informant-award',
        'mission'  => 'Awards 15–30% of collected proceeds to informants who report federal tax underpayments above $2 million (corporate) or $200,000 income threshold (individual).',
    ],

    [
        'title'              => 'U.S. Environmental Protection Agency',
        'slug'               => 'epa-whistleblower-protection',
        'official_name'     => 'U.S. Environmental Protection Agency',
        'common_name'  => 'EPA',
        'url'      => 'https://www.epa.gov/ocr/whistleblower-protection',
        'mission'  => 'Receives retaliation complaints under environmental whistleblower statutes including Clean Air Act, Clean Water Act, and Safe Drinking Water Act.',
    ],

    [
        'title'              => 'Department of Justice — False Claims Act Unit',
        'slug'               => 'doj-false-claims-act',
        'official_name'      => 'U.S. Department of Justice — Civil Division',
        'common_name'        => 'DOJ',
        'url'      => 'https://www.justice.gov/civil/false-claims-act',
        'mission'  => 'Pursues False Claims Act qui tam actions. Relators (whistleblowers) may receive 15–30% of government recoveries under 31 U.S.C. § 3730.',
    ],

];


// ════════════════════════════════════════════════════════════════════════════
// Seeder: ws_seed_agency_matrix
// ════════════════════════════════════════════════════════════════════════════

function ws_seed_agency_matrix() {

    global $_ws_agency_matrix;

    // Resolve the US jurisdiction term ID.
    $us_term = ws_jx_term_by_code( 'us' );
    if ( ! $us_term || is_wp_error( $us_term ) ) {
        return; // Taxonomy terms not yet seeded — bail.
    }
    $us_term_id = (int) $us_term->term_id;

    if ( ! defined( 'WS_MATRIX_SEEDING_IN_PROGRESS' ) ) {
        define( 'WS_MATRIX_SEEDING_IN_PROGRESS', true );
    }

    foreach ( $_ws_agency_matrix as $agency ) {

        $existing = get_page_by_path( $agency['slug'], OBJECT, 'ws-agency' );

        if ( $existing ) {
            $post_id = $existing->ID;
            wp_update_post( [
                'ID'         => $post_id,
                'post_title' => $agency['title'],
                'post_name'  => $agency['slug'],
            ] );
        } else {
            $post_id = wp_insert_post( [
                'post_title'  => $agency['title'],
                'post_name'   => $agency['slug'],
                'post_type'   => 'ws-agency',
                'post_status' => 'publish',
            ] );
        }

        if ( is_wp_error( $post_id ) || ! $post_id ) {
            continue;
        }

        // Write agency meta fields.
        $meta_fields = [
            'ws_agency_official_name' => $agency['official_name']    ?? '',
            'ws_agency_common_name'   => $agency['common_name'] ?? '',
            'ws_agency_url'           => $agency['url']     ?? '',
            'ws_agency_mission'       => $agency['mission'] ?? '',
        ];

        foreach ( $meta_fields as $key => $value ) {
            if ( $value !== '' ) {
                update_post_meta( $post_id, $key, $value );
            }
        }

        // Assign US jurisdiction term.
        wp_set_object_terms( $post_id, $us_term_id, 'ws_jurisdiction' );

        // Assign ws_language: English (all seeded federal agencies operate in English).
        $english_term = get_term_by( 'slug', 'english', 'ws_language' );
        if ( $english_term && ! is_wp_error( $english_term ) ) {
            wp_set_object_terms( $post_id, (int) $english_term->term_id, 'ws_language' );
        }

        // Mark as seeded.
        update_post_meta( $post_id, 'ws_matrix_source', 'matrix-agencies' );
    }
}


// ── Gate ──────────────────────────────────────────────────────────────────────

add_action( 'admin_init', function() {
    if ( get_option( 'ws_seeded_agency_matrix' ) !== '1.0.0' ) {
        ws_seed_agency_matrix();
        update_option( 'ws_seeded_agency_matrix', '1.0.0' );
    }
} );
