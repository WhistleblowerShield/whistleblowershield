<?php
/**
 * Prompt Generator - Assist Org Blocks
 */

defined( 'ABSPATH' ) || exit;

function ws_prompt_assist_org_schema_block(): string {
    return "--------------------------------------------------------------------------------\nFIELD DICTIONARY & SCHEMA REQUIREMENTS\n\n"
        . "Use the current assist-org schema contract and required fallbacks.\n"
        . "Use ternary fields with yes|no|unclear as applicable.\n"
        . "Use companion detail fields when sentinel terms trigger them.\n\n"
        . "JSON RECORD SHAPE\n"
        . "{\n  \"identity\": {...},\n  \"scope_of_service\": {...},\n  \"contact\": {...},\n  \"eligibility\": {...},\n  \"security\": {...},\n  \"review\": {...}\n}\n\n";
}

