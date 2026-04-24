<?php
/**
 * Prompt Generator - Shared Prompt Blocks
 */

defined( 'ABSPATH' ) || exit;

function ws_prompt_shared_intro_block( string $record_type, int $records = 0 ): string {

    $requested = $records ? $records . ', it is permissible to return less' : 'set to dynamic';

    if ( $record_type === 'assist-org' ) {
        $role       = "You are a research assistant building a vetted directory of assistance organizations for WhistleblowerShield.org. ";
        $harm       = "A dead URL, a wrong phone number, or a mischaracterized legal service is a critical failure, at the worst possible moment. ";
        $end        = "This template covers `" . $record_type . "` records ONLY.\n\n";
    } elseif ( $record_type === 'common-law' || $record_type === 'statute' ) {
        $anti       = $record_type === 'common-law' ? 'statutory' : 'common-law';
        $clarify    = $record_type === 'common-law'
            ? 'judicially-recognized whistleblower protections that exist outside codified statute. '
            : 'legislative acts: codified whistleblower protections, legal rights, safeguards, and benefits; established and enforced. ';
        $role       = "You are a legal research assistant generating structured JSON data for WhistleblowerShield.org, a public-interest reference site covering U.S. whistleblower protections across all 57 U.S. jurisdictions. ";
        $harm       = "A wrong statute of limitations costs someone their right to file. A fabricated citation poisons their attorney's research. These are not research errors — they are final outcomes. ";
        $end        = "This template covers `" . $record_type . "` records ONLY — " . $clarify . "Do not produce records for " . $anti . " protections.\n\n";
    } elseif ( $record_type === 'citation' || $record_type === 'construction' ) {
        $clarify    = $record_type === 'citation' ? 'specific citations to' : 'judicial constructions of';
        $role       = "You are a legal research assistant generating structured JSON data for WhistleblowerShield.org, a public-interest reference site covering U.S. whistleblower protections across all 57 U.S. jurisdictions. ";
        $harm       = "A wrong statute of limitations costs someone their right to file. A fabricated citation poisons their attorney's research. These are not research errors — they are final outcomes. ";
        $end        = "This template covers `" . $record_type . "` records ONLY — " . $clarify . " statutory or common-law whistleblower protections that can be referenced in support of legal arguments. Do not produce records for general statutory or common-law protections.\n\n";
    } else {
        $role       = "This_is_a_fuq'n_error";
        $harm       = "This_is_also_a_fuq'n_error";
        $end        = "This_is_yet_another_fuq'n_error";
    }

    $block  = "WhistleblowerShield.org Research Prompt - v3.0.0\n\n";
    $block .= $role;
    $block .= "You are the first-stage of a pipeline, gathering data that will then be verified for schema, reconciled with additional research, and then verified for accuracy. Research will be machine ingested to the site's database as 'draft' data. Human editors will be responsible for completion of records and the accuracy of the records, before records can be published. This pipeline has been proven to be consistently reliable, very adaptable and at times self-healing. You are its foundation. Your contribution will be documented. Provenance of the research is maintained within the pipeline, and archived on the site. Your input, commentary, observations and opinions about the research process is welcome and expected.\n\n";
    $block .= "Your research will serve vulnerable people in crisis (those considering reporting, or already facing retaliation) primarily, as well as legal researchers or journalists. Accuracy is both paramount and crucial. " . $harm . "Inaccurate data that is improperly vetted and ultimately published is not a research failure, it is a failure of the pipeline.\n\n";
    $block .= "Return a high-confidence, low-noise batch. Requested records is " . $requested . ", use your judgment to return only high-confidence records. Confidence is the hard constraint. Stop when confidence degrades.\n\n";
    $block .= $end;

    return $block;
}
function ws_prompt_shared_policy_block(): string {
    return "--------------------------------------------------------------------------------\n"
        . "GLOBAL OMISSION & SCHEMA POLICY\n\n"
        . "Return ONE JSON object containing `meta`, `records` (array), and `integrity`. Do not invent keys or data.\n\n"
        . "Omission is the primary safeguard against hallucination. Honest gaps in data do not cause harm.\n"
        . "EXPECTED-IF-FOUND: Fields should be actively searched, once is enough. If no supporting evidence is found after a reasonable attempt, set the field to the empty fallback. Do not omit.\n"
        . "EXPECTED: Fields should be actively searched. If no supporting evidence is found after a reasonable number of differing attempts, set the field to the defined fallback. Do not omit.\n"
        . "CONDITIONAL: When a field is triggered do not leave it empty. If there is reasonably no data to add to the field. Simply state \"No reasonable data found.\".\n"
        . "OPTIONAL: After a reasonable search, if data is unavailable or unconfirmed, OMIT THE KEY ENTIRELY.\n\n"
        . "When rules conflict, prioritize schema compliance and omission over guessing. Document rule conflicts in records to _review_notes, and to _json_run_notes otherwise.\n\n";
}

function ws_prompt_meta_block( string $record_type, array $meta_overrides = [] ): string {
    $extra = '';
    if ( array_key_exists( 'nationwide_only', $meta_overrides ) ) {
        $extra .= '- nationwide_only: true | false (Must match RUN SCOPE exactly).' . ( $meta_overrides['nationwide_only'] ? 'true' : 'false' ) . "\n";
    }

    return "--------------------------------------------------------------------------------\n"
        . "META BLOCK\n\n"
        . "- json_format_version: \"2.0\"\n"
        . "- prompt_version: \"3.0\"  (Must match version in prompt's first line, don't include patch).\n"
        . "- source_method: \"ai_research\"\n"
        . "- source_name: \"[MODEL COMMON NAME]\"\n"
        . "- jurisdiction_id: \"[US OR STATE CODE]\"\n"
        . "- generated_date: \"[YYYY-MM-DD]\"\n"
        . "- generated_by: \"[FULL MODEL NAME + VERSION]\"\n"
        . $extra
        . "- record_type: \"{$record_type}\"\n"
        . "- new_terms_proposed: (see META BLOCK: NEW TERMS PROPOSED).\n"
        . "- _json_run_notes: Notes about the batch for human review.\n"
        . "- _json_run_researcher_notes: Unrestricted space for candid input/commentary/observations/opinions.\n"
        . "- record_count: [INTEGER] (Must exactly match length of records array).\n"
        . "- batch_completed: \"[YYYY-MM-DD HH:MM UTC]\" (Write this last, UTC is required by archive).\n\n";
}

function ws_prompt_new_terms_guidance_block(): string {
    return "[META BLOCK: NEW TERMS PROPOSED] (Omit key entirely when no proposals exist).\n"
        . "- new_terms_proposed: Array of arrays of proposal objects. Each object is required.\n"
        . "  Use when a concept is encountered that does not fit any term in its taxonomy and cannot be cleanly represented by combining up to 3 existing terms.\n"
        . "  Expanding the taxonomy to represent real-world concepts is part of our shared primary task.\n"
        . "Proposal shape:\n"
        . "{\n"
        . "  \"taxonomy: \"[existing taxonomy table]\"\n"
        . "  \"term:     \"[proposed-term-in-kebab-case]\"\n"
        . "  \"label:    \"[human-readable label]\"\n"
        . "  \"notes:    \"[concept covered; describe taxonomy gap affected]\"\n"
        . "  \"seen_in:  [\"[common_name or official_name]\"] (prioritize common_name).\n"
        . "  \"count:    [integer matching seen_in length]\n"
        . "}\n"
        . "    Proposal Guidance: Prioritize narrow-scoped over broad-scoped terms. Consider terms that would provide meaningful coverage combined with existing terms.\n"
        . "    Do not use a proposed term in the record array. Document the gap using has-details when available, use _review_notes otherwise.\n\n";
}

function ws_prompt_integrity_block(): string {
    return "--------------------------------------------------------------------------------\n"
        . "[INTEGRITY BLOCK]\n"
        . "- has_anomalies: true | false. (Set to true ONLY if fewer records were returned than requested, source quality is inadequate, schema rules contradict reality, or invalid slugs were discovered in record array. Do not use for routine omissions).\n"
        . "- notations: Array of specific anomaly details. (Omit entirely if has_anomalies is false).\n"
        . "- notation_count: Integer matching notations array length. (Omit entirely if has_anomalies is false).\n\n";
}

function ws_prompt_final_contract(): string {
    return "Produce the complete JSON object now, inside a single code block. Do not include any commentary outside _json_run_researcher_notes or the code block.\n";
}

