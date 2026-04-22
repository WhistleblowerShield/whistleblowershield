<?php
/**
 * Prompt Generator - Config and Context Helpers
 */

defined( 'ABSPATH' ) || exit;

function ws_prompt_output_dir(): string {
    $dir = WP_CONTENT_DIR . '/logs/ws-prompts';
    if ( ! file_exists( $dir ) ) {
        wp_mkdir_p( $dir );
        file_put_contents( $dir . '/.htaccess', "Deny from all\n" );
    }
    return $dir;
}

function ws_prompt_resolve_jx_context( string $jx_id ): array {
    $jx = strtoupper( sanitize_text_field( $jx_id ) );
    $jx_type = 'state';
    if ( $jx === 'us' ) {
        $jx_type = 'federal';
    } elseif ( $jx === 'dc' ) {
        $jx_type = 'district';
    } elseif ( in_array( $jx, [ 'as', 'gu', 'mp', 'pr', 'vi' ], true ) ) {
        $jx_type = 'territory';
    }

    $context = [
        'jx_id'           => $jx,
        'jx_name'         => $jx,
        'jx_type'         => $jx_type,
        'legislature_url' => '',
    ];

    if ( $jx === '' ) {
        return $context;
    }

    if ( function_exists( 'ws_get_jurisdiction_data' ) ) {
        $data = ws_get_jurisdiction_data( $jx );
        if ( is_array( $data ) ) {
            $context['jx_name'] = (string) ( $data['name'] ?? $context['jx_name'] );
            $class = strtolower( trim( (string) ( $data['class'] ?? '' ) ) );
            if ( $class !== '' ) {
                $context['jx_type'] = $class;
            }
            $context['legislature_url'] = esc_url_raw( (string) ( $data['gov']['legislature_url'] ?? '' ) );
            return $context;
        }
    }

    $term = get_term_by( 'slug', strtolower( $jx ), WS_JURISDICTION_TAXONOMY );
    if ( $term && ! is_wp_error( $term ) ) {
        $context['jx_name'] = (string) $term->name;
    }

    return $context;
}

function ws_prompt_record_type_to_post_type( string $record_type ): string {
    switch ( $record_type ) {
        case 'statute':
            return 'jx-statute';
        case 'common-law':
            return 'jx-common-law';
        case 'citation':
            return 'jx-citation';
        case 'construction':
            return 'jx-construction';
        case 'assist-org':
            return 'ws-assist-org';
        default:
            return '';
    }
}

function ws_prompt_extract_record_identifier( string $record_type, int $post_id ): string {
    if ( $record_type === 'statute' ) {
        return trim( (string) get_post_meta( $post_id, '_ws_jx_statute_id', true ) );
    }

    if ( $record_type === 'common-law' ) {
        $v = trim( (string) get_post_meta( $post_id, '_ws_jx_comlaw_doctrine_id', true ) );
        if ( $v !== '' ) {
            return $v;
        }
    }

    if ( $record_type === 'citation' ) {
        $v = trim( (string) get_post_meta( $post_id, '_ws_jx_citation_id', true ) );
        if ( $v !== '' ) {
            return $v;
        }
    }

    if ( $record_type === 'construction' ) {
        $v = trim( (string) get_post_meta( $post_id, '_ws_jx_construction_id', true ) );
        if ( $v !== '' ) {
            return $v;
        }
    }

    if ( $record_type === 'assist-org' ) {
        $v = trim( (string) get_post_meta( $post_id, '_ws_aorg_internal_id', true ) );
        if ( $v !== '' ) {
            return $v;
        }
    }

    return trim( (string) get_the_title( $post_id ) );
}
