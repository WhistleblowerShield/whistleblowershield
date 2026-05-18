<?php
/**
 * Assist-org custom JSON ingest.
 *
 * Imports the current ready assist-org batch as draft ws-assist-org posts.
 * This is intentionally narrow: the source file is a hand-thinned staging
 * artifact, not a generic "normalized" format.
 *
 * Run from WordPress root with WP-CLI:
 *   wp eval-file in-progress/tools/ingest-assist-org.php
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
    fwrite( STDERR, "ERROR: Run this script inside WordPress context, usually with wp eval-file.\n" );
    exit( 1 );
}

const WS_AORG_POST_TYPE      = 'ws-assist-org';
const WS_AORG_PREFIX         = 'ws_aorg_';
const WS_AORG_INGEST_SOURCE  = 'ingest-assist-org';
const WS_AORG_READY_JSON     = __DIR__ . '/ready/US-6-Assist-org-normalized.json';

function aorg_ingest_fail( string $message, array $context = [] ): never {
    $details = '';

    if ( $context ) {
        $details = ' Context: ' . wp_json_encode( $context, JSON_UNESCAPED_SLASHES );
    }

    $full = "[assist-org ingest] {$message}{$details}";
    error_log( $full );
    fwrite( STDERR, $full . "\n" );
    exit( 1 );
}

function aorg_ingest_required_string( array $record, string $key ): string {
    if ( ! array_key_exists( $key, $record ) ) {
        aorg_ingest_fail( 'Required key is missing from staged record.', [ 'key' => $key ] );
    }

    $value = trim( (string) $record[ $key ] );
    if ( $value === '' ) {
        aorg_ingest_fail( 'Required key is blank in staged record.', [ 'key' => $key ] );
    }

    return $value;
}

function aorg_ingest_optional_int( mixed $value ): int {
    if ( is_bool( $value ) ) {
        return $value ? 1 : 0;
    }

    if ( is_int( $value ) ) {
        return $value;
    }

    if ( is_string( $value ) && preg_match( '/^-?\d+$/', $value ) ) {
        return (int) $value;
    }

    aorg_ingest_fail( 'Integer-like staged value is malformed.', [ 'value' => $value ] );
}

function aorg_ingest_contains_unclear( mixed $value ): bool {
    if ( is_string( $value ) ) {
        return strtolower( trim( $value ) ) === 'unclear';
    }

    if ( is_array( $value ) ) {
        foreach ( $value as $item ) {
            if ( aorg_ingest_contains_unclear( $item ) ) {
                return true;
            }
        }
    }

    return false;
}

function aorg_ingest_write_meta_if_present( int $post_id, array $record, string $key ): void {
    if ( ! array_key_exists( $key, $record ) ) {
        return;
    }

    $value = $record[ $key ];
    if ( $value === null || $value === '' || $value === [] ) {
        return;
    }

    if ( aorg_ingest_contains_unclear( $value ) ) {
        aorg_ingest_fail( 'Staged record contains unresolved unclear value.', [
            'post_id' => $post_id,
            'key'     => $key,
        ] );
    }

    if ( in_array( $key, [ 'has_attorneys', 'is_nationwide', 'anonymous_pre_consult_status', 'social_presence' ], true ) ) {
        $value = aorg_ingest_optional_int( $value );
    }

    update_post_meta( $post_id, WS_AORG_PREFIX . $key, $value );
}

function aorg_ingest_find_existing_post_id( string $official_name ): ?int {
    $posts = get_posts( [
        'post_type'      => WS_AORG_POST_TYPE,
        'post_status'    => 'any',
        'posts_per_page' => 2,
        'title'          => $official_name,
        'fields'         => 'ids',
        'no_found_rows'  => true,
    ] );

    if ( count( $posts ) > 1 ) {
        aorg_ingest_fail( 'Multiple existing assist-org posts match staged official_name.', [
            'official_name' => $official_name,
            'matches'       => count( $posts ),
        ] );
    }

    return $posts ? (int) $posts[0] : null;
}

function aorg_ingest_build_internal_id( string $jx_slug, string $post_slug ): string {
    if ( function_exists( 'ws_matrix_build_assist_org_internal_id' ) ) {
        return ws_matrix_build_assist_org_internal_id( $jx_slug, $post_slug );
    }

    $jx_slug   = sanitize_key( strtolower( trim( $jx_slug ) ) );
    $post_slug = sanitize_title( $post_slug );

    if ( $jx_slug === '' || $post_slug === '' ) {
        aorg_ingest_fail( 'Unable to build assist-org internal ID from blank slug data.', [
            'jx_slug'   => $jx_slug,
            'post_slug' => $post_slug,
        ] );
    }

    if ( str_starts_with( $post_slug, $jx_slug . '-' ) ) {
        return $post_slug;
    }

    return $jx_slug . '-' . $post_slug;
}

function aorg_ingest_build_post_content( array $record ): string {
    if ( empty( $record['_post_content_seed'] ) || ! is_array( $record['_post_content_seed'] ) ) {
        return '';
    }

    $seed  = $record['_post_content_seed'];
    $parts = [];

    foreach ( [
        'general_description'          => '',
        'whistleblower_scope_details'  => 'Whistleblower scope',
        'nationwide_example'           => 'Nationwide note',
        '_review_notes'                => 'Research notes',
    ] as $key => $label ) {
        if ( empty( $seed[ $key ] ) || ! is_string( $seed[ $key ] ) ) {
            continue;
        }

        $text = trim( $seed[ $key ] );
        if ( $text === '' ) {
            continue;
        }

        if ( $label === '' ) {
            $parts[] = '<p>' . esc_html( $text ) . '</p>';
        } else {
            $parts[] = '<p><strong>' . esc_html( $label ) . ':</strong> ' . esc_html( $text ) . '</p>';
        }
    }

    return implode( "\n\n", $parts );
}

function aorg_ingest_write_repeater( int $post_id, array $record, string $key, array $subfield_map ): void {
    if ( ! array_key_exists( $key, $record ) ) {
        return;
    }

    $rows = $record[ $key ];
    if ( ! is_array( $rows ) ) {
        aorg_ingest_fail( 'Repeater value must be an array.', [ 'post_id' => $post_id, 'key' => $key ] );
    }

    $full_key = WS_AORG_PREFIX . $key;
    update_post_meta( $post_id, $full_key, count( $rows ) );

    foreach ( $rows as $i => $row ) {
        if ( ! is_array( $row ) ) {
            aorg_ingest_fail( 'Repeater row must be an object/array.', [
                'post_id' => $post_id,
                'key'     => $key,
                'row'     => $i,
            ] );
        }

        foreach ( $subfield_map as $source_key => $acf_key ) {
            if ( ! array_key_exists( $source_key, $row ) ) {
                aorg_ingest_fail( 'Repeater row is missing required subfield.', [
                    'post_id'  => $post_id,
                    'key'      => $key,
                    'row'      => $i,
                    'subfield' => $source_key,
                ] );
            }

            update_post_meta( $post_id, "{$full_key}_{$i}_{$acf_key}", $row[ $source_key ] );
        }
    }
}

function aorg_ingest_assign_terms( int $post_id, array $slugs, string $taxonomy ): void {
    if ( ! taxonomy_exists( $taxonomy ) ) {
        aorg_ingest_fail( 'Required taxonomy does not exist.', [
            'post_id'  => $post_id,
            'taxonomy' => $taxonomy,
        ] );
    }

    $clean = [];
    foreach ( $slugs as $slug ) {
        $slug = trim( (string) $slug );
        if ( $slug === '' ) {
            continue;
        }

        if ( strtolower( $slug ) === 'unclear' ) {
            aorg_ingest_fail( 'Unresolved unclear taxonomy value reached ingest.', [
                'post_id'  => $post_id,
                'taxonomy' => $taxonomy,
            ] );
        }

        if ( ! term_exists( $slug, $taxonomy ) ) {
            aorg_ingest_fail( 'Required taxonomy term does not exist.', [
                'post_id'  => $post_id,
                'taxonomy' => $taxonomy,
                'slug'     => $slug,
            ] );
        }

        $clean[] = $slug;
    }

    if ( ! $clean ) {
        return;
    }

    $result = wp_set_object_terms( $post_id, $clean, $taxonomy );
    if ( is_wp_error( $result ) ) {
        aorg_ingest_fail( 'Taxonomy assignment failed.', [
            'post_id'  => $post_id,
            'taxonomy' => $taxonomy,
            'reason'   => $result->get_error_message(),
        ] );
    }
}

function aorg_ingest_us_term_id(): int {
    $term = get_term_by( 'slug', 'us', WS_JURISDICTION_TAXONOMY );
    if ( ! $term || is_wp_error( $term ) ) {
        aorg_ingest_fail( 'Required US jurisdiction term is unavailable.', [
            'taxonomy' => 'WS_JURISDICTION_TAXONOMY',
            'slug'     => 'us',
        ] );
    }

    return (int) $term->term_id;
}

function aorg_ingest_record( array $record, string $batch_id, int $us_term_id ): string {
    if ( aorg_ingest_contains_unclear( $record ) ) {
        aorg_ingest_fail( 'Staged record still contains unresolved unclear value.', [
            'official_name' => $record['official_name'] ?? '',
        ] );
    }

    $official_name = aorg_ingest_required_string( $record, 'official_name' );
    aorg_ingest_required_string( $record, 'official_homepage_url' );

    $existing_id   = aorg_ingest_find_existing_post_id( $official_name );
    $post_content  = aorg_ingest_build_post_content( $record );
    $post_data     = [
        'post_title'  => $official_name,
        'post_name'   => sanitize_title( $official_name ),
        'post_type'   => WS_AORG_POST_TYPE,
        'post_status' => 'draft',
    ];

    if ( $post_content !== '' ) {
        $post_data['post_content'] = $post_content;
    }

    if ( $existing_id ) {
        $post_data['ID'] = $existing_id;
        $post_id         = wp_update_post( $post_data, true );
        $action          = 'UPDATED';
    } else {
        $post_id = wp_insert_post( $post_data, true );
        $action  = 'CREATED';
    }

    if ( is_wp_error( $post_id ) || ! $post_id ) {
        aorg_ingest_fail( 'Draft post creation/update failed.', [
            'official_name' => $official_name,
            'reason'        => is_wp_error( $post_id ) ? $post_id->get_error_message() : 'empty post ID',
        ] );
    }

    foreach ( [
        'official_name',
        'common_name',
        'official_homepage_url',
        'homepage_verified_date',
        'intake_url',
        'lawyers_url',
        'contact_url',
        'social_presence',
        'mailing_address',
        'income_screening',
        'eligibility_status',
        'is_nationwide',
        'anonymous_pre_consult_status',
        'has_attorneys',
        'attorney_role',
        'legal_representation_status',
        'service_depth',
        'intake_commitment_class',
        'whistleblower_scope',
        'language_details',
    ] as $key ) {
        aorg_ingest_write_meta_if_present( (int) $post_id, $record, $key );
    }

    if ( array_key_exists( 'secure_channel', $record ) ) {
        if ( aorg_ingest_contains_unclear( $record['secure_channel'] ) ) {
            aorg_ingest_fail( 'Staged record contains unresolved unclear value.', [
                'post_id' => (int) $post_id,
                'key'     => 'secure_channel',
            ] );
        }

        update_post_meta(
            (int) $post_id,
            'ws_aorg_has_secure_channel',
            aorg_ingest_optional_int( $record['secure_channel'] )
        );
    }

    aorg_ingest_write_repeater( (int) $post_id, $record, 'phones', [
        'type'   => 'phone_type',
        'number' => 'phone_number',
        'url'    => 'phone_url',
    ] );

    aorg_ingest_write_repeater( (int) $post_id, $record, 'emails', [
        'type'    => 'email_type',
        'address' => 'email_address',
        'url'     => 'email_url',
    ] );

    aorg_ingest_write_repeater( (int) $post_id, $record, 'social_links', [
        'platform'   => 'social_platform',
        'url'        => 'social_url',
        'is_contact' => 'social_is_contact',
    ] );

    aorg_ingest_write_repeater( (int) $post_id, $record, 'secure_channels', [
        'tool'  => 'channel_tool',
        'url'   => 'channel_url',
        'label' => 'channel_label',
        'class' => 'channel_class',
    ] );

    if ( array_key_exists( 'organization_model', $record ) ) {
        aorg_ingest_assign_terms( (int) $post_id, [ $record['organization_model'] ], 'ws_organization_model' );
    }

    foreach ( [
        'cost_models'           => 'ws_aorg_cost_model',
        'protected_disclosures' => 'ws_protected_disclosure',
        'disclosure_targets'    => 'ws_disclosure_target',
        'case_stages'           => 'ws_case_stage',
        'services'              => 'ws_aorg_service',
        'employment_sectors'    => 'ws_employment_sector',
        'protected_classes'     => 'ws_protected_class',
        'languages'             => 'ws_language',
    ] as $key => $taxonomy ) {
        if ( array_key_exists( $key, $record ) ) {
            if ( ! is_array( $record[ $key ] ) ) {
                aorg_ingest_fail( 'Taxonomy field must be an array.', [
                    'post_id' => (int) $post_id,
                    'key'     => $key,
                ] );
            }

            aorg_ingest_assign_terms( (int) $post_id, $record[ $key ], $taxonomy );
        }
    }

    if ( ! empty( $record['is_nationwide'] ) ) {
        $result = wp_set_object_terms( (int) $post_id, $us_term_id, WS_JURISDICTION_TAXONOMY );
        if ( is_wp_error( $result ) ) {
            aorg_ingest_fail( 'Jurisdiction assignment failed.', [
                'post_id' => (int) $post_id,
                'reason'  => $result->get_error_message(),
            ] );
        }
    }

    $post = get_post( (int) $post_id );
    if ( ! $post || $post->post_name === '' ) {
        $slug_result = wp_update_post( [
            'ID'        => (int) $post_id,
            'post_name' => sanitize_title( $official_name ),
        ], true );

        if ( is_wp_error( $slug_result ) ) {
            aorg_ingest_fail( 'WordPress did not accept a draft post_name for internal ID.', [
                'post_id'       => (int) $post_id,
                'official_name' => $official_name,
                'reason'        => $slug_result->get_error_message(),
            ] );
        }

        $post = get_post( (int) $post_id );
        if ( ! $post || $post->post_name === '' ) {
            aorg_ingest_fail( 'WordPress did not return a usable post_name for internal ID.', [
                'post_id'       => (int) $post_id,
                'official_name' => $official_name,
            ] );
        }
    }

    update_post_meta(
        (int) $post_id,
        '_ws_aorg_id',
        aorg_ingest_build_internal_id( 'us', (string) $post->post_name )
    );

    update_post_meta( (int) $post_id, 'ws_matrix_source', WS_AORG_INGEST_SOURCE );
    update_post_meta( (int) $post_id, '_ws_ingest_batch_id', $batch_id );
    update_post_meta( (int) $post_id, '_ws_ingest_source_file', basename( WS_AORG_READY_JSON ) );
    update_post_meta( (int) $post_id, 'ws_aorg_ingest_date', gmdate( 'Y-m-d' ) );

    return "{$action} {$official_name} (post {$post_id})";
}

if ( ! file_exists( WS_AORG_READY_JSON ) ) {
    aorg_ingest_fail( 'Ready JSON file not found.', [ 'path' => WS_AORG_READY_JSON ] );
}

try {
    $raw = json_decode( (string) file_get_contents( WS_AORG_READY_JSON ), true, 512, JSON_THROW_ON_ERROR );
} catch ( JsonException $e ) {
    aorg_ingest_fail( 'Ready JSON file is malformed.', [
        'path'   => WS_AORG_READY_JSON,
        'reason' => $e->getMessage(),
    ] );
}

if ( ! is_array( $raw ) || empty( $raw['records'] ) || ! is_array( $raw['records'] ) ) {
    aorg_ingest_fail( 'Ready JSON file does not contain records array.', [ 'path' => WS_AORG_READY_JSON ] );
}

$batch_id   = basename( WS_AORG_READY_JSON, '.json' );
$us_term_id = aorg_ingest_us_term_id();
$count      = 0;

echo "\nAssist-org custom ingest: {$batch_id}\n";
echo 'Records: ' . count( $raw['records'] ) . "\n\n";

foreach ( $raw['records'] as $record ) {
    echo aorg_ingest_record( $record, $batch_id, $us_term_id ) . "\n";
    $count++;
}

echo "\nDone. Draft records ingested: {$count}.\n";
