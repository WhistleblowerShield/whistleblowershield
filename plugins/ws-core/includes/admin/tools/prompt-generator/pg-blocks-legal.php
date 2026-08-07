<?php
/**
 * pg-blocks-legal.php
 *
 * Prompt Generator — Legal Blocks
 *
 * MAJOR CHANGE — STATUTE SCHEMA IS NOW LIVE-GENERATED
 * ------------------------------------------------------
 * The previous version of this file hand-typed a JSON schema string for
 * each record type. That's a second, disconnected copy of information
 * that `acf-jx-statutes.php` already owns — and it drifted: the source
 * had a genuine syntax bug (mismatched braces, an unclosed string, the
 * object closing twice) in the burden_of_proof/reward/links tail, which
 * is exactly what happens when the same field list is maintained by hand
 * in two places.
 *
 * `acf-jx-statutes.php` already contains `ws_get_jx_statute_prompt_package()`
 * — a function built for exactly this purpose. It returns the live field
 * table (name, json_key, json_path, type, taxonomy, choices, prompt_group,
 * and an auto-generated instruction string) filtered to only the fields
 * flagged for prompt inclusion. The statute branch below now renders
 * FROM that function, live, every time. If a field is added, removed, or
 * renamed in the ACF table, the next generated prompt reflects it
 * automatically — the same way pg-taxonomy.php already keeps taxonomy
 * tables in sync with live `get_terms()` instead of hardcoding term lists.
 *
 * ⚠ COMMON-LAW / CITATION / CONSTRUCTION — NO EQUIVALENT FUNCTION EXISTS ⚠
 * ---------------------------------------------------------------------------
 * I looked for `ws_get_jx_comlaw_prompt_package()`,
 * `ws_get_jx_citation_prompt_package()`, and
 * `ws_get_jx_construction_prompt_package()` — none exist. What DOES exist
 * for these three is a set of JSON field-spec files in
 * `in-progress/compiler/` (`jx-comlaw.json`, `jx-citation.json`,
 * `jx-construction.json`) — a different, hook/logic-based schema format,
 * not the same PHP args-table shape `acf-jx-statutes.php` uses. I do not
 * know the intended path from those compiler JSON files to a callable
 * prompt-package function, and guessing at that integration is a real
 * architecture decision, not a schema-shape guess — I'm not making it
 * for you. These three branches throw with the specific missing-function
 * name and a pointer to what exists, rather than falling back to a
 * hardcoded (and possibly stale) schema string.
 *
 * NO DEFAULTS, NO FALLBACKS, NO CATCH-ALLS
 * -------------------------------------------
 * Every branch below either succeeds with live data or throws. There is
 * no hardcoded schema fallback for any record type, including statute —
 * if ws_get_jx_statute_prompt_package() isn't available, this throws
 * rather than silently reverting to the old hand-typed string.
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
 * 3.22.0-rewrite  statute branch: replaced hand-typed schema string with
 *                 a live renderer over ws_get_jx_statute_prompt_package().
 *                 common-law/citation/construction: replaced placeholder
 *                 text with a thrown exception naming the specific
 *                 missing prompt-package function and pointing at the
 *                 in-progress/compiler/*.json files as the likely (but
 *                 unconfirmed) source of truth for whoever builds it.
 *                 Architecture and code by Claude (Anthropic), directed
 *                 and reviewed by Dwight.
 * 3.21.0-rewrite  statute branch: fixed brace-matching bug, field names
 *                 unchanged (superseded by 3.22.0-rewrite above — kept
 *                 in changelog for the record, not because it's still
 *                 the current approach).
 */

defined( 'ABSPATH' ) || exit;

/**
 * Renders a RECORD SCHEMA block from a prompt-package array shaped like
 * ws_get_jx_statute_prompt_package()'s return value: keys 'package',
 * 'fields' (flat list), 'fields_by_tab' (grouped), 'prompt_groups'
 * (group-code => label map).
 *
 * This function doesn't know or care which CPT the package came from —
 * it's generic over the shape, so the day a
 * ws_get_jx_comlaw_prompt_package() (etc.) exists, this same renderer
 * should work for it without modification. Don't write a second renderer
 * per record type; fix this one if the shape needs to change.
 */
function ws_prompt_render_acf_prompt_package( array $package, string $record_type ): string {
    if ( empty( $package['fields_by_tab'] ) ) {
        ws_fail_loud(
            'prompt-generator',
            "{$package['package']} prompt package returned zero prompt-flagged fields. "
            . "Either the ACF table has no fields with prompt_group > 0, or the package "
            . "function is broken. Refusing to generate an empty schema block.",
            [ 'package' => $package['package'] ?? 'unknown', 'record_type' => $record_type ]
        );
    }

    $out  = "--------------------------------------------------------------------------------\n";
    $out .= 'RECORD SCHEMA (' . strtoupper( $record_type ) . ') — generated live from ' . $package['package'] . "'s ACF field table\n\n";

    $out .= "{\n";
    $tab_lines = [];
    foreach ( $package['fields_by_tab'] as $tab_key => $fields ) {
        $pairs = [];
        foreach ( $fields as $field ) {
            $key = $field['json_key'] ?? $field['name'];
            $pairs[] = '"' . $key . '": ' . ws_prompt_acf_field_json_placeholder( $field );
        }
        $tab_lines[] = '  "' . $tab_key . '": {' . implode( ', ', $pairs ) . '}';
    }
    $out .= implode( ",\n", $tab_lines ) . "\n}\n\n";

    $group_labels = $package['prompt_groups'] ?? [];
    $out .= "FIELD NOTES — prompt_group code meanings: ";
    $out .= implode( ', ', array_map( fn( $code, $label ) => "{$code}={$label}", array_keys( $group_labels ), $group_labels ) );
    $out .= "\n\n";

    foreach ( $package['fields'] as $field ) {
        $out .= "- {$field['json_path']} [group {$field['group']}]: {$field['legal_prompt']}\n";
    }
    $out .= "\n";

    return $out;
}

/**
 * Default JSON placeholder for a field, based on its ACF type. Used only
 * to render the schema SHAPE shown to the research AI — not real data.
 */
function ws_prompt_acf_field_json_placeholder( array $field ): string {
    $type = $field['type'] ?? 'text';

    if ( $type === 'taxonomy' ) {
        return '[]';
    }
    if ( $type === 'select' && ! empty( $field['choices'] ) && ! empty( $field['multiple'] ) ) {
        return '[]';
    }
    if ( $type === 'true_false' ) {
        return 'false';
    }
    if ( $type === 'number' ) {
        return '0';
    }
    if ( in_array( $type, [ 'post_object', 'relationship', 'repeater' ], true ) ) {
        return '[]';
    }

    return '""';
}

/**
 * @throws WS_Loud_Failure if the record type's prompt-package source doesn't exist yet.
 */
function ws_prompt_legal_schema_block( string $record_type ): string {
    ws_prompt_assert_valid_record_type( $record_type );

    if ( $record_type === 'statute' ) {
        if ( ! function_exists( 'ws_get_jx_statute_prompt_package' ) ) {
            ws_fail_loud(
                'prompt-generator',
                "ws_get_jx_statute_prompt_package() is not available — acf-jx-statutes.php "
                . "must be loaded before the prompt generator can build a statute schema "
                . "block. Refusing to fall back to a hardcoded schema string.",
                [ 'record_type' => $record_type ]
            );
        }
        return ws_prompt_render_acf_prompt_package( ws_get_jx_statute_prompt_package(), 'statute' );
    }

    if ( $record_type === 'common-law' ) {
        ws_fail_loud(
            'prompt-generator',
            "No ws_get_jx_comlaw_prompt_package() function exists yet. "
            . "in-progress/compiler/jx-comlaw.json exists and may be the intended source "
            . "of truth, but its integration into a callable prompt package has not been "
            . "built — this is an architecture decision, not something to guess at. "
            . "Common-law prompts cannot be generated until this is resolved.",
            [ 'record_type' => $record_type ]
        );
    }

    if ( $record_type === 'citation' ) {
        ws_fail_loud(
            'prompt-generator',
            "No ws_get_jx_citation_prompt_package() function exists yet. "
            . "in-progress/compiler/jx-citation.json exists and may be the intended source "
            . "of truth, but its integration into a callable prompt package has not been "
            . "built. Citation prompts cannot be generated until this is resolved.",
            [ 'record_type' => $record_type ]
        );
    }

    if ( $record_type === 'construction' ) {
        ws_fail_loud(
            'prompt-generator',
            "No ws_get_jx_construction_prompt_package() function exists yet. "
            . "in-progress/compiler/jx-construction.json exists and may be the intended "
            . "source of truth, but its integration into a callable prompt package has not "
            . "been built. Construction prompts cannot be generated until this is resolved.",
            [ 'record_type' => $record_type ]
        );
    }

    // Unreachable — ws_prompt_assert_valid_record_type() already threw
    // for anything not covered above (including 'assist-org', which
    // never reaches this function — see ws_prompt_block_sequence()).
    ws_fail_loud( 'prompt-generator', "Unreachable: '{$record_type}' passed validation but has no branch in ws_prompt_legal_schema_block(). Fix the function, don't add a default case.", [ 'record_type' => $record_type ] );
}
