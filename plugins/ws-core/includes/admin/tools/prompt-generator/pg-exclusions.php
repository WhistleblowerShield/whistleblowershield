<?php
/**
 * pg-exclusions.php
 *
 * Prompt Generator — Exclusion Helpers
 *
 * PURPOSE
 * -------
 * Builds and merges the "already have this, don't re-research it"
 * exclusion list shown to research AI models. Auto-exclusions are pulled
 * from live post data; manual exclusions come from the operator's textarea
 * on the admin form.
 *
 * Depends on: pg-config.php (ws_prompt_record_type_to_post_type,
 * ws_prompt_extract_record_identifier).
 *
 * @package    WhistleblowerShield
 * @since      3.13.0
 * @version    3.21.0-rewrite
 * @author     WhistleblowerShield (Dwight)
 * @link       https://whistleblowershield.org
 * @copyright  Copyright (c) Whistleblower Shield
 *
 * VERSION LOG
 * -----------
 * 3.21.0-rewrite  Docblock pass. Annotated the direct-DB calls in this
 *                 file per the admin-tool query-layer exception rule.
 *                 No logic changes.
 */

defined( 'ABSPATH' ) || exit;

/**
 * Admin-only query-layer exception: uses get_posts()/get_post_meta()
 * directly to compute which records already exist for a jurisdiction, so
 * the research prompt can tell the AI not to duplicate them. This never
 * renders to a visitor — worst case on a stale read is redundant AI
 * research output, not a wrong published fact. Never do this in
 * render/query-layer code.
 */
function ws_prompt_get_auto_exclusions( string $record_type, string $jx_id ): array {
    $post_type = ws_prompt_record_type_to_post_type( $record_type );
    if ( $post_type === '' || $jx_id === '' ) {
        return [];
    }

    $allowed_statuses = [ 'publish', 'private', 'draft', 'auto-draft', 'pending', 'future' ];
    $jx_slug = strtolower( $jx_id );

    if ( $record_type === 'statute' ) {
        $posts = get_posts( [
            'post_type'              => $post_type,
            'post_status'            => $allowed_statuses,
            'posts_per_page'         => -1,
            'fields'                 => 'ids',
            'no_found_rows'          => true,
            'update_post_meta_cache' => false,
            'update_post_term_cache' => false,
            'tax_query'              => [ [
                'taxonomy' => WS_JURISDICTION_TAXONOMY,
                'field'    => 'slug',
                'terms'    => [ $jx_slug ],
            ] ],
            'meta_query'             => [ [
                'key'     => '_ws_jx_statute_id',
                'value'   => '',
                'compare' => '!=',
            ] ],
        ] );

        if ( empty( $posts ) ) {
            return [];
        }

        $ids = [];
        foreach ( $posts as $pid ) {
            $sid = trim( (string) get_post_meta( (int) $pid, '_ws_jx_statute_id', true ) );
            if ( $sid !== '' ) {
                $ids[] = $sid;
            }
        }

        $ids = array_values( array_unique( $ids ) );
        sort( $ids, SORT_NATURAL | SORT_FLAG_CASE );
        return $ids;
    }

    $posts = get_posts( [
        'post_type'              => $post_type,
        'post_status'            => $allowed_statuses,
        'posts_per_page'         => -1,
        'fields'                 => 'ids',
        'no_found_rows'          => true,
        'update_post_meta_cache' => false,
        'update_post_term_cache' => false,
        'tax_query'              => [ [
            'taxonomy' => WS_JURISDICTION_TAXONOMY,
            'field'    => 'slug',
            'terms'    => [ $jx_slug ],
        ] ],
    ] );

    if ( empty( $posts ) ) {
        return [];
    }

    $ids = [];
    foreach ( $posts as $pid ) {
        $value = ws_prompt_extract_record_identifier( $record_type, (int) $pid );
        if ( $value !== '' ) {
            $ids[] = $value;
        }
    }

    $ids = array_values( array_unique( $ids ) );
    sort( $ids, SORT_NATURAL | SORT_FLAG_CASE );
    return $ids;
}

function ws_prompt_split_lines( string $text ): array {
    $lines = [];
    foreach ( explode( "\n", $text ) as $line ) {
        $line = trim( $line );
        if ( $line !== '' ) {
            $lines[] = $line;
        }
    }

    $lines = array_values( array_unique( $lines ) );
    sort( $lines, SORT_NATURAL | SORT_FLAG_CASE );
    return $lines;
}

function ws_prompt_merge_exclusions( string $manual_exclusions, array $auto_exclusions ): string {
    $merged = [];

    foreach ( ws_prompt_split_lines( $manual_exclusions ) as $line ) {
        $merged[] = $line;
    }
    foreach ( $auto_exclusions as $line ) {
        $line = trim( (string) $line );
        if ( $line !== '' ) {
            $merged[] = $line;
        }
    }

    $merged = array_values( array_unique( $merged ) );
    sort( $merged, SORT_NATURAL | SORT_FLAG_CASE );
    return implode( "\n", $merged );
}

function ws_prompt_resolve_auto_exclusions_text( array $post, array $computed_auto_exclusions ): string {
    $posted = isset( $post['exclusion_list_auto'] )
        ? sanitize_textarea_field( (string) $post['exclusion_list_auto'] )
        : '';
    $edited = ! empty( $post['exclusion_list_auto_edited'] );

    return $edited ? $posted : implode( "\n", $computed_auto_exclusions );
}

function ws_prompt_render_exclusion_list( string $excludes, string $label ): string {
    $lines = ws_prompt_split_lines( $excludes );
    if ( empty( $lines ) ) {
        return '';
    }

    $out = "{$label}\n";
    foreach ( $lines as $line ) {
        $out .= "  {$line}\n";
    }
    return $out . "\n";
}
