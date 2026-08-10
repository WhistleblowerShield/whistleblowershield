<?php
/**
 * pg-config.php
 *
 * Prompt Generator — Config and Context Helpers
 *
 * PURPOSE
 * -------
 * Resolves jurisdiction context (name, type, legislature URL) for the
 * prompt builders, and maps record types to their post types and meta
 * keys. No dependencies on other pg-* files.
 *
 * NO DEFAULTS, NO FALLBACKS, NO CATCH-ALLS
 * -----------------------------------------
 * Per project rule: if a record_type doesn't match a known type, or a
 * jurisdiction ID doesn't resolve to a real jurisdiction, this file
 * throws rather than silently producing a plausible-looking wrong
 * answer. "Ask for a tennis record, get a statute record" is the
 * failure mode this guards against — a caller bug should halt
 * execution immediately, loudly, not degrade into generic defaults.
 *
 * @package    WhistleblowerShield
 * @since      3.13.0
 * @version    3.22.0-rewrite
 * @author     WhistleblowerShield (Dejunai)
 * @link       https://whistleblowershield.org
 * @copyright  Copyright (c) Whistleblower Shield
 *
 * VERSION LOG
 * -----------
 * 3.22.0-rewrite  Added ws_prompt_valid_record_types() and
 *                 ws_prompt_assert_valid_record_type() as the single
 *                 source of truth for "is this a real record type."
 *                 ws_prompt_resolve_jx_context() now throws when a
 *                 jurisdiction can't be positively confirmed to exist,
 *                 instead of silently returning generic 'state' defaults.
 *                 ws_prompt_record_type_to_post_type() now throws
 *                 instead of returning a text sentinel that behaved as
 *                 a silent no-op when used as a real post_type value.
 * 3.21.0-rewrite  Docblock pass. Annotated the one direct-DB call in this
 *                 file per the admin-tool query-layer exception rule.
 */

defined( 'ABSPATH' ) || exit;

/**
 * The single source of truth for which record types exist. Every other
 * function in this folder that branches on record_type should validate
 * against this list — do not maintain a second copy of it anywhere.
 */
function ws_prompt_valid_record_types(): array {
    return [ 'statute', 'common-law', 'citation', 'construction', 'assist-org' ];
}

/**
 * Loud-fail guard. Throws immediately if $record_type isn't one of the
 * known types — no default, no fallback, no silent pass-through. Call
 * this at the top of any function that makes a decision based on
 * record_type, even if you believe the caller already validated it.
 * Defense in depth: if something somehow calls a function incorrectly,
 * this is what makes it loud instead of quietly wrong.
 *
 * @throws WS_Loud_Failure
 */
function ws_prompt_assert_valid_record_type( string $record_type ): void {
    if ( ! in_array( $record_type, ws_prompt_valid_record_types(), true ) ) {
        ws_fail_loud(
            'prompt-generator',
            "Invalid record_type '{$record_type}'. Valid types are: "
            . implode( ', ', ws_prompt_valid_record_types() ) . '. '
            . 'No default record type exists — an unrecognized record_type '
            . 'must halt execution, not silently produce output for the wrong type.',
            [ 'record_type' => $record_type ]
        );
    }
}

/**
 * Returns the output directory for generated prompts.
 *
 * Creates the directory and writes an .htaccess restriction if not present.
 *
 * @return string Prompt output directory path.
 */
function ws_prompt_output_dir(): string {
    $dir = WP_CONTENT_DIR . '/logs/ws-prompts';
    if ( ! file_exists( $dir ) ) {
        wp_mkdir_p( $dir );
        file_put_contents( $dir . '/.htaccess', "Deny from all\n" );
    }
    return $dir;
}

/**
 * Resolves a two-letter jurisdiction code into name/type/legislature-URL
 * context for use in prompt headers.
 *
 * NO SILENT DEFAULT: previously, if neither ws_get_jurisdiction_data()
 * nor a taxonomy term lookup found the jurisdiction, this function
 * quietly returned jx_type='state' and jx_name=<raw code> — a plausible
 * -looking but potentially wrong context (e.g. treating DC or a
 * territory as a generic state). It now throws instead. A jurisdiction
 * code that doesn't resolve to a real jurisdiction is a caller bug or a
 * data gap, not something to paper over with a generic guess.
 *
 * Admin-only query-layer exception: falls back to get_term_by() only as
 * a secondary *name-lookup* path when the real query-layer function
 * isn't available — not as a way to avoid failing when the jurisdiction
 * genuinely doesn't exist. Never do this in render/query-layer code.
 *
 * @throws WS_Loud_Failure if the format is invalid or the jurisdiction cannot be positively confirmed to exist.
 */
function ws_prompt_resolve_jx_context( string $jx_id ): array {
    $jx = strtoupper( sanitize_text_field( $jx_id ) );

    if ( $jx === '' || ! preg_match( '/^[A-Z]{2}$/', $jx ) ) {
        ws_fail_loud( 'prompt-generator', "ws_prompt_resolve_jx_context() requires a two-letter jurisdiction code, got '{$jx_id}'.", [ 'jx_id' => $jx_id ] );
    }

    $jx_type = 'state';
    if ( $jx === 'US' ) {
        $jx_type = 'federal';
    } elseif ( $jx === 'DC' ) {
        $jx_type = 'district';
    } elseif ( in_array( $jx, [ 'AS', 'GU', 'MP', 'PR', 'VI' ], true ) ) {
        $jx_type = 'territory';
    }

    // Preferred path: real query-layer function, if it's loaded and
    // actually has data for this code.
    if ( function_exists( 'ws_get_jurisdiction_data' ) ) {
        $data = ws_get_jurisdiction_data( $jx );
        if ( is_array( $data ) && ! empty( $data ) ) {
            $class = strtolower( trim( (string) ( $data['class'] ?? '' ) ) );
            return [
                'jx_id'           => $jx,
                'jx_name'         => (string) ( $data['name'] ?? $jx ),
                'jx_type'         => $class !== '' ? $class : $jx_type,
                'legislature_url' => esc_url_raw( (string) ( $data['gov']['legislature_url'] ?? '' ) ),
            ];
        }
    }

    // Admin-only query-layer exception (see docblock above): direct
    // taxonomy read used only as a fallback name lookup.
    $term = get_term_by( 'slug', strtolower( $jx ), WS_JURISDICTION_TAXONOMY );
    if ( $term && ! is_wp_error( $term ) ) {
        return [
            'jx_id'           => $jx,
            'jx_name'         => (string) $term->name,
            'jx_type'         => $jx_type,
            'legislature_url' => '',
        ];
    }

    // Neither path confirmed this jurisdiction exists. Loud fail —
    // no generic default context.
    ws_fail_loud(
        'prompt-generator',
        "Jurisdiction '{$jx}' could not be resolved via ws_get_jurisdiction_data() "
        . "or the " . WS_JURISDICTION_TAXONOMY . " taxonomy. Refusing to generate "
        . "a prompt with a guessed-at jurisdiction name/type.",
        [ 'jx_id' => $jx ]
    );
}

/**
 * Resolves a prompt record type string into the corresponding WordPress custom post type slug.
 *
 * @param string $record_type Prompt record type (e.g. 'statute', 'assist-org').
 * @return string Custom post type slug.
 * @throws WS_Loud_Failure if the record type is invalid.
 */
function ws_prompt_record_type_to_post_type( string $record_type ): string {
    ws_prompt_assert_valid_record_type( $record_type );

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
    }

    // Unreachable — ws_prompt_assert_valid_record_type() already threw
    // for anything not covered above. No default case on purpose.
    ws_fail_loud( 'prompt-generator', "Unreachable: '{$record_type}' passed validation but has no post_type mapping. This is a bug in ws_prompt_record_type_to_post_type() itself, not a caller error — fix the switch statement.", [ 'record_type' => $record_type ] );
}

/**
 * Admin-only query-layer exception: reads post meta directly to build
 * exclusion lists for the prompt generator. Exclusion lists are advisory
 * text shown to a research AI, not rendered to any visitor — a stale or
 * incomplete exclusion list produces redundant research at worst, not
 * incorrect published data. Never do this in render/query-layer code.
 */
function ws_prompt_extract_record_identifier( string $record_type, int $post_id ): string {
    ws_prompt_assert_valid_record_type( $record_type );

    if ( $record_type === 'statute' ) {
        return trim( (string) get_post_meta( $post_id, '_ws_jx_statute_id', true ) );
    }

    if ( $record_type === 'common-law' ) {
        $v = trim( (string) get_post_meta( $post_id, '_ws_jx_comlaw_id', true ) );
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
        $v = trim( (string) get_post_meta( $post_id, '_ws_aorg_id', true ) );
        if ( $v !== '' ) {
            return $v;
        }
    }

    return trim( (string) get_the_title( $post_id ) );
}
