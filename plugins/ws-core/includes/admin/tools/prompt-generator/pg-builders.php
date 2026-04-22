<?php
/**
 * Prompt Generator - Prompt Builders
 */

defined( 'ABSPATH' ) || exit;

function ws_generate_assist_org_prompt( array $scope ): string {
    $jx       = strtoupper( sanitize_text_field( $scope['jx_id'] ) );
    $jx_name  = sanitize_text_field( $scope['jx_name'] );
    $records  = (int) ( $scope['records_requested'] ?? 0 );
    $notes    = trim( (string) ( $scope['assist_org_focus_notes'] ?? '' ) );
    $excludes = (string) ( $scope['exclusion_list'] ?? '' );
    $nationwide_only = ! empty( $scope['nationwide_only'] );

    $out  = ws_prompt_shared_intro_block( 'assist-org' );
    $out .= "--------------------------------------------------------------------------------\nTARGET CRITERIA\n\n";
    $out .= "- Include organizations with direct help or fast referral pathways.\n";
    $out .= "- Exclude pure reporting channels and media tip lines without support pathways.\n";
    $out .= "- Law firm exception: contingency, pro bono, and legal-aid models are permitted.\n\n";
    $out .= ws_prompt_shared_policy_block();
    $out .= ws_prompt_meta_block( 'assist-org', [ 'nationwide_only' => $nationwide_only ] );
    $out .= ws_prompt_new_terms_guidance_block();
    $out .= ws_prompt_assist_org_schema_block();
    $out .= ws_prompt_dynamic_taxonomy_tables( 'assist-org' );
    $out .= ws_prompt_integrity_block();

    $out .= "--------------------------------------------------------------------------------\nRUN SCOPE\n\n";
    $out .= "Record type:          assist-org\n";
    $out .= "Jurisdiction:         {$jx_name}\n";
    $out .= "Jurisdiction ID:      {$jx}\n";
    $out .= $records > 0
        ? "Requested Records:    {$records}\n"
        : "Requested Records:    dynamic based on research quality and confidence\n";
    $out .= 'meta.nationwide_only: ' . ( $nationwide_only ? 'true' : 'false' ) . "\n\n";

    if ( $notes !== '' ) {
        $out .= "Focus Notes: {$notes}\n\n";
    }

    $out .= ws_prompt_render_exclusion_list( $excludes, 'Do not return records matching these exclusions:' );
    $out .= ws_prompt_final_contract();
    return $out;
}

function ws_generate_legal_prompt( string $record_type, array $scope ): string {
    $jx       = strtoupper( sanitize_text_field( $scope['jx_id'] ) );
    $jx_name  = sanitize_text_field( $scope['jx_name'] );
    $leg_url  = esc_url_raw( (string) ( $scope['legislature_url'] ?? '' ) );
    $records  = (int) ( $scope['records_requested'] ?? 0 );
    $notes    = trim( (string) ( $scope['scope_details'] ?? '' ) );
    $excludes = (string) ( $scope['exclusion_list'] ?? '' );

    $out  = ws_prompt_shared_intro_block( $record_type );
    $out .= ws_prompt_shared_policy_block();
    $out .= ws_prompt_meta_block( $record_type );
    $out .= ws_prompt_new_terms_guidance_block();
    $out .= ws_prompt_legal_schema_block( $record_type );
    $out .= ws_prompt_dynamic_taxonomy_tables( $record_type );
    $out .= ws_prompt_integrity_block();

    $out .= "--------------------------------------------------------------------------------\nRUN SCOPE\n\n";
    $out .= "Record type:        {$record_type}\n";
    $out .= "Jurisdiction:       {$jx_name}\n";
    $out .= "Jurisdiction ID:    {$jx}\n";
    $out .= "Legislature URL:    {$leg_url}\n";
    $out .= $records > 0
        ? "Records Requested:  {$records}\n"
        : "Records Requested:  dynamic based on research quality and confidence\n";

    if ( $record_type === 'citation' || $record_type === 'construction' ) {
        $out .= 'Minimum quality:    ' . sanitize_text_field( (string) ( $scope['min_quality'] ?? 'moderate' ) ) . "\n";
    }
    if ( $record_type === 'construction' ) {
        $out .= 'Statute type:       ' . sanitize_text_field( (string) ( $scope['statute_type'] ?? 'state' ) ) . "\n";
    }
    if ( $notes !== '' ) {
        $out .= "Scope Notes:\n{$notes}\n";
    }

    $out .= "\n";
    $out .= ws_prompt_render_exclusion_list( $excludes, 'Do not return records matching these exclusions:' );
    $out .= ws_prompt_final_contract();
    return $out;
}

