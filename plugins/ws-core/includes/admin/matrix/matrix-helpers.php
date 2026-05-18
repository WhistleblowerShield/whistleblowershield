<?php
/**
 * matrix-helpers.php — Shared utility functions used by matrix seeder files.
 *
 * @package    WhistleblowerShield
 * @since      3.4.0
 * @version    3.20.0
 * @author     Whistleblower Shield
 * @link       https://whistleblowershield.org
 * @copyright  Copyright (c) Whistleblower Shield
 * 
 */

if ( ! defined( 'ABSPATH' ) ) exit;


// ════════════════════════════════════════════════════════════════════════════
// ws_matrix_build_assist_org_internal_id()
//
// Builds the canonical assist-org internal ID from jurisdiction + post slug.
// The ID is derived data; seed rows and ingest batches must not carry their
// own independent internal-ID value. Known organizations get exact stable
// abbreviations first; all other slugs pass through the shared word table.
//
// @param string $jx_slug  Jurisdiction slug, such as "us".
// @param string $org_slug Canonical assist-org post slug.
// @return string
// ════════════════════════════════════════════════════════════════════════════

function ws_matrix_build_assist_org_internal_id( string $jx_slug, string $org_slug ): string {
    $jx_slug  = sanitize_key( strtolower( trim( $jx_slug ) ) );
    $org_slug = sanitize_title( $org_slug );

    if ( $jx_slug === '' ) {
        throw new RuntimeException( 'Cannot build assist-org internal ID without jurisdiction slug.' );
    }

    if ( $org_slug === '' ) {
        throw new RuntimeException( 'Cannot build assist-org internal ID without assist-org slug.' );
    }

    if ( str_starts_with( $org_slug, $jx_slug . '-' ) ) {
        $org_slug = substr( $org_slug, strlen( $jx_slug ) + 1 );
    }

    return $jx_slug . '-' . ws_matrix_abbreviate_assist_org_slug( $org_slug );
}


// ════════════════════════════════════════════════════════════════════════════
// ws_matrix_abbreviate_assist_org_slug()
//
// Applies canonical abbreviation rules to an assist-org slug.
//
// @param string $org_slug Canonical assist-org post slug.
// @return string
// ════════════════════════════════════════════════════════════════════════════

function ws_matrix_abbreviate_assist_org_slug( string $org_slug ): string {
    $org_slug = sanitize_title( $org_slug );

    if ( $org_slug === '' ) {
        throw new RuntimeException( 'Cannot abbreviate blank assist-org slug.' );
    }

    $known = [
        'government-accountability-project'                  => 'gap',
        'national-security-counselors'                       => 'nsc',
        'national-whistleblower-center'                      => 'nwc',
        'the-anti-fraud-coalition'                           => 'taf',
        'whistleblowers-of-america'                          => 'woa',
        'national-employment-law-project'                    => 'nelp',
        'national-employment-lawyers-association'            => 'nela',
        'national-legal-aid-and-defender-association'        => 'nlada',
        'public-employees-for-environmental-responsibility'  => 'peer',
        'the-signals-network'                                => 'tsn',
        'walk-the-talk-foundation'                           => 'wttf',
        'ai-whistleblower-initiative'                        => 'aiwi',
        'legal-advocates-for-safe-science-and-technology'    => 'lasst',
        'whistleblower-and-source-protection-program'        => 'whisper',
        'empower-oversight-whistleblowers-research'          => 'empowr',
        'reporters-committee-for-freedom-of-the-press'       => 'rcfp',
        'times-up-legal-defense-fund'                        => 'times-up-law-def-fdn',
        'legal-services-corporation-find-legal-aid'          => 'lsc-find-law-aid',
        'american-bar-association-find-legal-help'           => 'aba-find-law-help',
    ];

    if ( isset( $known[ $org_slug ] ) ) {
        return $known[ $org_slug ];
    }

    $drop_words = [
        'and',
        'the',
        'for',
        'of',
        'in',
        'at',
        'to',
        'a',
        'an',
    ];

    $word_map = [
        'advocacy'        => 'adv',
        'aid'             => 'aid',
        'alliance'        => 'all',
        'alliances'       => 'all',
        'association'     => 'assoc',
        'associations'    => 'assoc',
        'attorney'        => 'att',
        'attorneys'       => 'att',
        'bureau'          => 'bur',
        'bureaus'         => 'bur',
        'center'          => 'ctr',
        'centers'         => 'ctr',
        'centre'          => 'ctr',
        'centres'         => 'ctr',
        'coalition'       => 'coal',
        'coalitions'      => 'coal',
        'commission'      => 'comm',
        'commissions'     => 'comm',
        'committee'       => 'cmte',
        'committees'      => 'cmte',
        'corporation'     => 'corp',
        'corporations'    => 'corp',
        'council'         => 'cncl',
        'councils'        => 'cncl',
        'defender'        => 'def',
        'defenders'       => 'def',
        'defense'         => 'def',
        'democracy'       => 'dem',
        'department'      => 'dept',
        'departments'     => 'dept',
        'education'       => 'edu',
        'educational'     => 'edu',
        'employee'        => 'emp',
        'employees'       => 'emp',
        'employment'      => 'emp',
        'environmental'   => 'env',
        'federal'         => 'fed',
        'foundation'      => 'fdn',
        'foundations'     => 'fdn',
        'global'          => 'intl',
        'government'      => 'gov',
        'governmental'    => 'gov',
        'governments'     => 'gov',
        'initiative'      => 'init',
        'initiatives'     => 'init',
        'institution'     => 'inst',
        'institutions'    => 'inst',
        'institute'       => 'inst',
        'institutes'      => 'inst',
        'international'   => 'intl',
        'lawyer'          => 'law',
        'lawyers'         => 'law',
        'legal'           => 'law',
        'national'        => 'nat',
        'nationals'       => 'nat',
        'network'         => 'net',
        'networks'        => 'net',
        'office'          => 'ofc',
        'offices'         => 'ofc',
        'organization'    => 'org',
        'organizations'   => 'org',
        'organisation'    => 'org',
        'organisations'   => 'org',
        'oversight'       => 'ovrs',
        'policy'          => 'pol',
        'program'         => 'prog',
        'programs'        => 'prog',
        'project'         => 'proj',
        'projects'        => 'proj',
        'protect'         => 'prot',
        'protection'      => 'prot',
        'protections'     => 'prot',
        'public'          => 'pub',
        'referral'        => 'ref',
        'referrals'       => 'ref',
        'research'        => 'rsch',
        'resource'        => 'res',
        'resources'       => 'res',
        'responsibility'  => 'resp',
        'rights'          => 'rts',
        'science'         => 'sci',
        'security'        => 'sec',
        'service'         => 'svc',
        'services'        => 'svc',
        'source'          => 'src',
        'technology'      => 'tech',
        'whistleblower'   => 'wb',
        'whistleblowers'  => 'wb',
        'whistleblowing'  => 'wb',
    ];

    $parts = [];
    foreach ( explode( '-', $org_slug ) as $part ) {
        if ( $part === '' || in_array( $part, $drop_words, true ) ) {
            continue;
        }

        $parts[] = $word_map[ $part ] ?? $part;
    }

    $slug = implode( '-', $parts );
    $slug = preg_replace( '/[^a-z0-9]+/', '-', $slug );
    $slug = preg_replace( '/-+/', '-', (string) $slug );
    $slug = trim( (string) $slug, '-' );

    if ( $slug === '' ) {
        throw new RuntimeException( 'Assist-org abbreviation pass produced a blank slug.' );
    }

    return $slug;
}


// ════════════════════════════════════════════════════════════════════════════
// ws_matrix_assign_terms()
//
// Resolves an array of term slugs to term IDs and assigns them to a post
// via wp_set_object_terms(). Silently skips any slug that does not exist
// in the given taxonomy — seeders will not fatal if a term is missing.
//
// @param int    $post_id   Post to assign terms to.
// @param array  $slugs     Term slugs to resolve and assign.
// @param string $taxonomy  Taxonomy slug.
// ════════════════════════════════════════════════════════════════════════════

function ws_matrix_assign_terms( $post_id, array $slugs, $taxonomy ) {
    $term_ids = [];
    foreach ( $slugs as $slug ) {
        $term = get_term_by( 'slug', $slug, $taxonomy );
        if ( $term && ! is_wp_error( $term ) ) {
            $term_ids[] = (int) $term->term_id;
        }
    }
    if ( ! empty( $term_ids ) ) {
        $result = wp_set_object_terms( $post_id, $term_ids, $taxonomy );
        if ( is_wp_error( $result ) ) {
            error_log( sprintf(
                '[ws-core] ws_matrix_assign_terms(): wp_set_object_terms failed for post %d taxonomy %s — %s',
                (int) $post_id,
                (string) $taxonomy,
                $result->get_error_message()
            ) );
        }
    }
}
