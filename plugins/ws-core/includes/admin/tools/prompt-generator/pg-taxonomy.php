<?php
/**
 * Prompt Generator - Taxonomy Rendering
 */

defined( 'ABSPATH' ) || exit;

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
            continue;
        }
        $out[] = [ 'slug' => (string) $term->slug ];
    }

    return $out;
}

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

function ws_prompt_taxonomy_descriptions(): array {
    return [
        'ws_protected_disclosure' => 'Subject matter of disclosure',
        'ws_protected_class'      => 'Worker classifications served',
        'ws_disclosure_target'    => 'Who the disclosure may be made to',
        'ws_process_type'         => 'Procedural routes navigated',
        'ws_case_stage'           => 'Legal stages supported',
        'ws_language'             => 'Languages confirmed by source',
        'ws_aorg_type'            => 'Primary org classification',
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

function ws_prompt_taxonomies_for_record_type( string $record_type ): array {
    switch ( $record_type ) {
        case 'assist-org':
            return [ 'ws_protected_disclosure', 'ws_protected_class', 'ws_disclosure_target', 'ws_process_type', 'ws_case_stage', 'ws_language', 'ws_aorg_type', 'ws_employment_sector', 'ws_aorg_cost_model', 'ws_aorg_service' ];
        case 'statute':
        case 'common-law':
            return [ 'ws_protected_disclosure', 'ws_protected_class', 'ws_disclosure_target', 'ws_employment_sector', 'ws_adverse_action', 'ws_process_type', 'ws_remedy', 'ws_fee_shifting_rule', 'ws_employer_defense', 'ws_employee_standard' ];
        case 'citation':
        case 'construction':
            return [ 'ws_protected_disclosure', 'ws_protected_class', 'ws_disclosure_target', 'ws_employment_sector', 'ws_adverse_action', 'ws_process_type', 'ws_remedy', 'ws_employer_defense', 'ws_employee_standard' ];
        default:
            return [];
    }
}

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

