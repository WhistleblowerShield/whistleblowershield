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
// own independent internal-ID value.
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
        return $org_slug;
    }

    return $jx_slug . '-' . $org_slug;
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
