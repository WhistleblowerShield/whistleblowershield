<?php
/**
 * Prompt Generator - Shared Prompt Blocks
 */

defined( 'ABSPATH' ) || exit;

function ws_prompt_shared_intro_block( string $record_type ): string {
    if ( $record_type === 'assist-org' ) {
        return "WhistleblowerShield.org Research Prompt - v3.0.0\n\n"
            . "You are a research assistant building a vetted directory of assistance organizations for WhistleblowerShield.org. "
            . "You are the first stage of a pipeline with machine and human verification.\n\n"
            . "Return a high-confidence, low-noise batch. Confidence is the hard constraint.\n\n";
    }

    return "WhistleblowerShield.org Research Prompt - v3.0.0\n\n"
        . "You are a legal research assistant producing structured JSON records for WhistleblowerShield.org.\n"
        . "You are the first stage of a multi-check pipeline with machine and human verification.\n\n"
        . "Return high-confidence records only. Omit uncertain fields rather than guessing.\n\n";
}

function ws_prompt_shared_policy_block(): string {
    return "--------------------------------------------------------------------------------\n"
        . "GLOBAL OMISSION & SCHEMA POLICY\n\n"
        . "Return ONE JSON object containing `meta`, `records`, and `integrity`.\n"
        . "Do not invent keys or values. Omission is preferred over hallucination.\n\n"
        . "EXPECTED fields may use explicit fallback values where defined.\n"
        . "OPTIONAL fields should be omitted when unverified.\n"
        . "CONDITIONAL fields must be populated when triggered.\n\n"
        . "When rules conflict, prioritize schema compliance and omission over guessing.\n\n";
}

function ws_prompt_meta_block( string $record_type, array $meta_overrides = [] ): string {
    $extra = '';
    if ( array_key_exists( 'nationwide_only', $meta_overrides ) ) {
        $extra .= '    "nationwide_only": ' . ( $meta_overrides['nationwide_only'] ? 'true' : 'false' ) . ",\n";
    }

    return "--------------------------------------------------------------------------------\n"
        . "META BLOCK\n\n"
        . "{\n"
        . "  \"meta\": {\n"
        . "    \"json_format_version\": \"2.0\",\n"
        . "    \"source_method\": \"ai_research\",\n"
        . "    \"source_name\": \"[MODEL COMMON NAME]\",\n"
        . "    \"jurisdiction_id\": \"[US OR STATE CODE]\",\n"
        . "    \"generated_date\": \"[YYYY-MM-DD]\",\n"
        . "    \"generated_by\": \"[FULL MODEL NAME + VERSION]\",\n"
        . $extra
        . "    \"record_type\": \"{$record_type}\",\n"
        . "    \"new_terms_proposed\": [],\n"
        . "    \"record_count\": 0,\n"
        . "    \"batch_completed\": \"[YYYY-MM-DD HH:MM UTC]\"\n"
        . "  }\n"
        . "}\n\n";
}

function ws_prompt_new_terms_guidance_block(): string {
    return "META BLOCK: NEW TERMS PROPOSED\n"
        . "- Keep `meta.new_terms_proposed` as an array.\n"
        . "- Add proposal objects when a concept does not fit existing valid taxonomy slugs.\n"
        . "- Do not place proposed slugs into record taxonomy arrays.\n"
        . "- If no proposals are needed, leave `meta.new_terms_proposed` as []\n\n"
        . "Proposal object shape:\n"
        . "{\n"
        . "  \"taxonomy\": \"[existing taxonomy slug]\",\n"
        . "  \"term\": \"[proposed-term-in-kebab-case]\",\n"
        . "  \"label\": \"[human label]\",\n"
        . "  \"notes\": \"[why existing slugs are insufficient]\",\n"
        . "  \"seen_in\": [\"[record identifier]\"] ,\n"
        . "  \"count\": 1\n"
        . "}\n\n";
}

function ws_prompt_integrity_block(): string {
    return "--------------------------------------------------------------------------------\n"
        . "INTEGRITY BLOCK\n\n"
        . "{\n"
        . "  \"integrity\": {\n"
        . "    \"has_anomalies\": false,\n"
        . "    \"notations\": [],\n"
        . "    \"notation_count\": 0\n"
        . "  }\n"
        . "}\n\n"
        . "Use has_anomalies only for real batch-level anomalies. Routine omissions are not anomalies.\n\n";
}

function ws_prompt_final_contract(): string {
    return "Produce the complete JSON object now, inside a single code block.\n"
        . "Do not include commentary outside the code block.\n";
}

