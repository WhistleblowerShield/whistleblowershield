<?php
/**
 * pg-builders.php
 *
 * Prompt Generator — Prompt Builders (assembly layer)
 *
 * PURPOSE
 * -------
 * Assembles a complete prompt from the block functions in pg-blocks-shared.php,
 * pg-blocks-assist-org.php, and pg-blocks-legal.php, plus the taxonomy tables
 * from pg-taxonomy.php.
 *
 * REWRITE NOTE
 * ------------
 * Prior to 3.21.0 this file had two separate hand-written functions —
 * ws_generate_assist_org_prompt() and ws_generate_legal_prompt() — that
 * did the same nine-step assembly sequence in parallel. Every phrase
 * change to shared prompt language meant editing both. This rewrite
 * replaces both with a single ws_generate_prompt(), driven by an ordered
 * block list (ws_prompt_block_sequence()) and a data-driven RUN SCOPE
 * footer (ws_prompt_run_scope_fields() / ws_prompt_render_run_scope()).
 * Change a phrase once, in the one block function that produces it.
 *
 * The old function names are kept as thin wrappers below for backward
 * compatibility with any external call site — they just forward to
 * ws_generate_prompt(). Safe to delete once pg-ui.php is confirmed to
 * call ws_generate_prompt() directly (it does, in this rewrite — see
 * pg-ui.php). Per the NO LIVE DATA rule this project runs on, there's no
 * stored data depending on these names existing — they're kept only in
 * case something outside this folder calls them directly. If nothing
 * does, delete them; don't carry them forward "just in case" forever.
 *
 * Depends on: pg-blocks-shared.php, pg-blocks-assist-org.php,
 * pg-blocks-legal.php, pg-taxonomy.php.
 *
 * @package    WhistleblowerShield
 * @since      3.13.0
 * @version    3.22.0-rewrite
 * @author     WhistleblowerShield (Dwight)
 * @link       https://whistleblowershield.org
 * @copyright  Copyright (c) Whistleblower Shield
 *
 * VERSION LOG
 * -----------
 * 3.22.0-rewrite  Loud-fail pass: ws_generate_prompt() validates
 *                 record_type first, before anything runs.
 *                 ws_prompt_block_sequence() and ws_prompt_run_scope_fields()
 *                 no longer fall through to the legal-type shape for an
 *                 unrecognized record_type — they throw. The missing-
 *                 block-function case now throws instead of embedding an
 *                 error string in generated output. No default/else/
 *                 catch-all branches remain in this file.
 * 3.21.0-rewrite  Consolidated two parallel builders into one
 *                 (ws_generate_prompt), driven by a block-sequence list.
 *                 Replaced the two hand-typed RUN SCOPE footers with one
 *                 data-driven table (ws_prompt_run_scope_fields /
 *                 ws_prompt_render_run_scope). Architecture and code by
 *                 Claude (Anthropic), directed and reviewed by Dwight.
 */

defined( 'ABSPATH' ) || exit;

/**
 * Returns the ordered list of block-generator function names for a given
 * record type. Every entry must be a real, zero-argument-callable
 * function name (ws_prompt_meta_block is the one exception — it's
 * special-cased in ws_generate_prompt() below because it needs
 * $record_type and per-type overrides).
 *
 * This is the one place to edit if a record type's block order or
 * composition needs to change. Do not special-case block order anywhere
 * else in this file.
 *
 * NO SILENT FALLTHROUGH: this previously had no validation at all — any
 * $record_type that wasn't 'assist-org' fell through to the legal block
 * sequence by default, meaning a typo'd or garbage record_type silently
 * produced a legal-shaped prompt instead of failing. That's the "ask for
 * a tennis record, get a statute record" bug, concretely. Fixed.
 *
 * @throws WS_Loud_Failure if $record_type isn't a known type.
 */
function ws_prompt_block_sequence( string $record_type ): array {
    ws_prompt_assert_valid_record_type( $record_type );

    $shared_head = [
        'ws_prompt_shared_policy_block',
        'ws_prompt_meta_block',           // special-cased below — needs $record_type
        'ws_prompt_new_terms_guidance_block',
    ];
    $shared_tail = [ 'ws_prompt_integrity_block' ];

    if ( $record_type === 'assist-org' ) {
        return array_merge( $shared_head, [ 'ws_prompt_assist_org_schema_block' ], $shared_tail );
    }

    if ( in_array( $record_type, [ 'statute', 'common-law', 'citation', 'construction' ], true ) ) {
        // These four use the same shape today — ws_prompt_legal_schema_block()
        // and ws_prompt_dynamic_taxonomy_tables() both branch internally on
        // $record_type already, so no per-type list is needed here. If a
        // future record type needs a genuinely different block order, add
        // an explicit branch above — do not add a bare else/default that
        // silently captures it.
        return array_merge( $shared_head, [ 'ws_prompt_legal_schema_block' ], $shared_tail );
    }

    // Unreachable — ws_prompt_assert_valid_record_type() already threw
    // for anything not covered above. No default/else on purpose.
    ws_fail_loud( 'prompt-generator', "Unreachable: '{$record_type}' passed validation but has no block sequence. This is a bug in ws_prompt_block_sequence() itself — add an explicit branch, not a default.", [ 'record_type' => $record_type ] );
}

/**
 * Which RUN SCOPE footer rows print for a given record type, in order.
 * Each row is [ label, value, applies_to ] where applies_to is either
 * null (prints for every record type using this footer shape) or an
 * array of record types it's restricted to.
 *
 * This is the one place to edit if the RUN SCOPE footer needs a new
 * field — no if-chains anywhere else in this file need to know about it.
 *
 * NO SILENT FALLTHROUGH — same fix as ws_prompt_block_sequence() above.
 *
 * @throws WS_Loud_Failure if $record_type isn't a known type.
 */
function ws_prompt_run_scope_fields( string $record_type, array $scope ): array {
    ws_prompt_assert_valid_record_type( $record_type );

    $jx      = strtoupper( sanitize_text_field( (string) ( $scope['jx_id'] ?? '' ) ) );
    $jx_name = sanitize_text_field( (string) ( $scope['jx_name'] ?? '' ) );
    $records = (int) ( $scope['records_requested'] ?? 0 );
    $records_line = $records > 0 ? (string) $records : 'dynamic based on research quality and confidence';

    if ( $record_type === 'assist-org' ) {
        return [
            [ 'Record type',          'assist-org',    null ],
            [ 'Jurisdiction',         $jx_name,        null ],
            [ 'Jurisdiction ID',      $jx,             null ],
            [ 'Requested Records',    $records_line,   null ],
            [ 'meta.nationwide_only', ! empty( $scope['nationwide_only'] ) ? 'true' : 'false', null ],
        ];
    }

    if ( in_array( $record_type, [ 'statute', 'common-law', 'citation', 'construction' ], true ) ) {
        $leg_url = esc_url_raw( (string) ( $scope['legislature_url'] ?? '' ) );

        return [
            [ 'Record type',       $record_type,  null ],
            [ 'Jurisdiction',      $jx_name,      null ],
            [ 'Jurisdiction ID',   $jx,           null ],
            [ 'Legislature URL',   $leg_url,      null ],
            [ 'Records Requested', $records_line, null ],
            [ 'Minimum quality',   sanitize_text_field( (string) ( $scope['min_quality'] ?? 'moderate' ) ), [ 'citation', 'construction' ] ],
            [ 'Statute type',      sanitize_text_field( (string) ( $scope['statute_type'] ?? 'state' ) ),    [ 'construction' ] ],
        ];
    }

    // Unreachable — same guard as above.
    ws_fail_loud( 'prompt-generator', "Unreachable: '{$record_type}' passed validation but has no RUN SCOPE field list. This is a bug in ws_prompt_run_scope_fields() itself — add an explicit branch, not a default.", [ 'record_type' => $record_type ] );
}

/**
 * Renders the RUN SCOPE footer for any record type from
 * ws_prompt_run_scope_fields(), plus the one hand-written exception:
 * the "notes" field. It's kept as a manual exception rather than folded
 * into the generic row list because assist-org's "Focus Notes" and the
 * legal types' "Scope Notes" differ in more than just label — legal
 * notes render as a multi-line block under the label, assist-org notes
 * render inline. Forcing that into the generic single-line row format
 * would either break legal's multi-line notes or add a format flag to
 * every row just to handle one field. Not worth it for one exception.
 */
function ws_prompt_render_run_scope( string $record_type, array $scope ): string {
    $out = "--------------------------------------------------------------------------------\nRUN SCOPE\n\n";

    foreach ( ws_prompt_run_scope_fields( $record_type, $scope ) as $row ) {
        [ $label, $value, $applies_to ] = $row;
        if ( $applies_to !== null && ! in_array( $record_type, $applies_to, true ) ) {
            continue;
        }
        $out .= str_pad( "{$label}:", 21 ) . "{$value}\n";
    }
    $out .= "\n";

    if ( $record_type === 'assist-org' ) {
        $notes = trim( (string) ( $scope['assist_org_focus_notes'] ?? '' ) );
        if ( $notes !== '' ) {
            $out .= "Focus Notes: {$notes}\n\n";
        }
    } else {
        $notes = trim( (string) ( $scope['scope_details'] ?? '' ) );
        if ( $notes !== '' ) {
            $out .= "Scope Notes:\n{$notes}\n";
        }
    }

    return $out;
}

/**
 * The single builder. Replaces ws_generate_assist_org_prompt() and
 * ws_generate_legal_prompt() — see REWRITE NOTE above.
 *
 * Validates record_type FIRST, before any block runs — this is the
 * primary gate. Every downstream function also validates independently
 * (defense in depth per project rule), but this is where a bad call
 * fails fastest, before any output has been assembled.
 *
 * @throws WS_Loud_Failure if $record_type isn't a known type.
 */
function ws_generate_prompt( string $record_type, array $scope ): string {
    ws_prompt_assert_valid_record_type( $record_type );

    $records = (int) ( $scope['records_requested'] ?? 0 );
    $excludes = (string) ( $scope['exclusion_list'] ?? '' );

    $out = ws_prompt_shared_intro_block( $record_type, $records );

    foreach ( ws_prompt_block_sequence( $record_type ) as $block_fn ) {
        if ( $block_fn === 'ws_prompt_meta_block' ) {
            $meta_overrides = ( $record_type === 'assist-org' )
                ? [ 'nationwide_only' => ! empty( $scope['nationwide_only'] ) ]
                : [];
            $out .= ws_prompt_meta_block( $record_type, $meta_overrides );
            continue;
        }

        if ( $block_fn === 'ws_prompt_legal_schema_block' ) {
            $out .= ws_prompt_legal_schema_block( $record_type );
            continue;
        }

        if ( ! function_exists( $block_fn ) ) {
            // Loud fail, not a loud STRING. A missing block function is a
            // code bug (typo'd name in ws_prompt_block_sequence(), or a
            // file that failed to load) — it should halt generation, not
            // get embedded as text in an otherwise-complete-looking prompt
            // that a researcher could miss.
            ws_fail_loud( 'prompt-generator', "Block function '{$block_fn}' does not exist. Check ws_prompt_block_sequence() for a typo, or confirm the file defining it loaded.", [ 'block_fn' => $block_fn, 'record_type' => $record_type ] );
        }

        $out .= call_user_func( $block_fn );
    }

    $out .= ws_prompt_dynamic_taxonomy_tables( $record_type );
    $out .= ws_prompt_render_run_scope( $record_type, $scope );
    $out .= ws_prompt_render_exclusion_list( $excludes, 'Do not return records matching these exclusions:' );
    $out .= ws_prompt_final_contract();

    return $out;
}

/**
 * @deprecated 3.21.0-rewrite Use ws_generate_prompt('assist-org', $scope).
 * Kept as a thin wrapper only in case something outside this folder
 * calls it directly. Safe to delete once confirmed unused.
 */
function ws_generate_assist_org_prompt( array $scope ): string {
    return ws_generate_prompt( 'assist-org', $scope );
}

/**
 * @deprecated 3.21.0-rewrite Use ws_generate_prompt( $record_type, $scope ).
 * Kept as a thin wrapper only in case something outside this folder
 * calls it directly. Safe to delete once confirmed unused.
 */
function ws_generate_legal_prompt( string $record_type, array $scope ): string {
    return ws_generate_prompt( $record_type, $scope );
}
