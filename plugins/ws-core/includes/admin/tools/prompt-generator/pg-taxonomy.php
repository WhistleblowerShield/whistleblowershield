<?php
/**
 * pg-taxonomy.php
 *
 * Prompt Generator — Taxonomy Rendering
 *
 * PURPOSE
 * -------
 * Reads live taxonomy terms and renders them into the two-column slug
 * tables that appear in every research prompt. This is the mechanism
 * that keeps prompt vocabulary synchronized with production taxonomy —
 * if a term is added or renamed in register-taxonomies.php, the next
 * generated prompt reflects it automatically. No dependencies on other
 * pg-* files.
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
 * 3.22.0-rewrite  ws_prompt_taxonomies_for_record_type() now throws for
 *                 an unrecognized record_type instead of returning an
 *                 empty array — a silent empty result here previously
 *                 meant "print a schema with zero taxonomy tables,"
 *                 which is a plausible-looking wrong prompt, not an
 *                 obvious failure.
 * 3.21.0-rewrite  Docblock pass. Annotated the direct get_terms() call
 *                 per the admin-tool query-layer exception rule.
 *                 No logic changes.
 */

defined( 'ABSPATH' ) || exit;

/**
 * Admin-only query-layer exception: reads live taxonomy terms directly
 * via get_terms(). This is the entire point of this tool — prompt
 * accuracy depends on *current* term state, not a cached or rendered
 * view of it. Never do this in render/query-layer code.
 */
function ws_prompt_get_taxonomy_terms_no_parents( string $taxonomy ): array {
    $terms = get_terms( [
        'taxonomy'   => $taxonomy,
        'hide_empty' => false,
        'orderby'    => 'term_id',
        'order'      => 'ASC',
    ] );

    if ( is_wp_error( $terms ) || empty( $terms ) ) {
        return [];
    }

    $parent_ids = [];
    foreach ( $terms as $term ) {
        if ( (int) $term->parent > 0 ) {
            $parent_ids[ (int) $term->parent ] = true;
        }
    }

    $out = [];
    foreach ( $terms as $term ) {
        $tid = (int) $term->term_id;
        if ( isset( $parent_ids[ $tid ] ) ) {
            continue; // Parent terms are intentionally excluded — see prompt text.
        }
        $out[] = [ 'slug' => (string) $term->slug ];
    }

    return $out;
}

/**
 * Prompt render slug columns.
 *
 * @param array $terms Parameter description.
 * @return string Return description.
 */
function ws_prompt_render_slug_columns( array $terms ): string {
    if ( empty( $terms ) ) {
        return "(no terms found)\n\n";
    }

    $pad = 35;
    $lines = '';
    $count = count( $terms );
    for ( $i = 0; $i < $count; $i += 2 ) {
        $left = $terms[ $i ]['slug'] ?? '';
        $right = $terms[ $i + 1 ]['slug'] ?? '';
        if ( $right !== '' ) {
            $lines .= str_pad( $left, $pad ) . $right . "\n";
        } else {
            $lines .= $left . "\n";
        }
    }

    return $lines . "\n";
}

/**
 * Prompt taxonomy descriptions.
 *
 * @return array Return description.
 */
function ws_prompt_taxonomy_descriptions(): array {
    return [
        'ws_protected_disclosure' => 'Subject matter of disclosure',
        'ws_protected_class'      => 'Worker classifications served',
        'ws_disclosure_target'    => 'Who the disclosure may be made to',
        'ws_process_type'         => 'Procedural routes navigated',
        'ws_case_stage'           => 'Legal stages supported',
        'ws_language'             => 'Languages confirmed by source',
        'ws_organization_model'   => 'Primary org classification',
        'ws_employment_sector'    => 'Sectors served',
        'ws_aorg_cost_model'      => 'Cost structure',
        'ws_aorg_service'         => 'Services offered',
        'ws_adverse_action'       => 'Adverse actions addressed',
        'ws_remedy'               => 'Remedies addressed',
        'ws_fee_shifting_rule'    => 'Fee shifting rules',
        'ws_employer_defense'     => 'Employer defenses',
        'ws_employee_standard'    => 'Employee burden standards',
    ];
}

/**
 * Which taxonomy tables get printed for which record type. This is the
 * one place to edit if a record type needs a different taxonomy set —
 * do not special-case this list anywhere else.
 *
 * @throws WS_Loud_Failure if $record_type isn't a known type.
 */
function ws_prompt_taxonomies_for_record_type( string $record_type ): array {
    ws_prompt_assert_valid_record_type( $record_type );

    switch ( $record_type ) {
        case 'assist-org':
            return [ 'ws_protected_disclosure', 'ws_protected_class', 'ws_disclosure_target', 'ws_process_type', 'ws_case_stage', 'ws_language', 'ws_organization_model', 'ws_employment_sector', 'ws_aorg_cost_model', 'ws_aorg_service' ];
        case 'statute':
        case 'common-law':
            return [ 'ws_protected_disclosure', 'ws_protected_class', 'ws_disclosure_target', 'ws_employment_sector', 'ws_adverse_action', 'ws_process_type', 'ws_remedy', 'ws_fee_shifting_rule', 'ws_employer_defense', 'ws_employee_standard' ];
        case 'citation':
        case 'construction':
            return [ 'ws_protected_disclosure', 'ws_protected_class', 'ws_disclosure_target', 'ws_employment_sector', 'ws_adverse_action', 'ws_process_type', 'ws_remedy', 'ws_employer_defense', 'ws_employee_standard' ];
    }

    // Unreachable — ws_prompt_assert_valid_record_type() already threw
    // for anything not covered above. No default case on purpose.
    ws_fail_loud( 'prompt-generator', "Unreachable: '{$record_type}' passed validation but has no taxonomy list. This is a bug in ws_prompt_taxonomies_for_record_type() itself — fix the switch statement.", [ 'record_type' => $record_type ] );
}

/**
 * Prompt dynamic taxonomy tables.
 *
 * @param string $record_type Parameter description.
 * @return string Return description.
 */
function ws_prompt_dynamic_taxonomy_tables( string $record_type ): string {
    $taxonomies = ws_prompt_taxonomies_for_record_type( $record_type );
    $descs = ws_prompt_taxonomy_descriptions();

    $out  = "--------------------------------------------------------------------------------\n";
    $out .= "TAXONOMY TABLES\n\n";
    $out .= "Verify all slugs match exactly. Use slugs only from assigned table.\n";
    $out .= "Use all applicable slugs for multi-select arrays. Parent terms are intentionally excluded.\n\n";

    foreach ( $taxonomies as $taxonomy ) {
        $desc = $descs[ $taxonomy ] ?? 'Allowed terms';
        $out .= "TAXONOMY: {$taxonomy} ({$desc})\n";
        $out .= ws_prompt_render_slug_columns( ws_prompt_get_taxonomy_terms_no_parents( $taxonomy ) );
    }

    return $out;
}