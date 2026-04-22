<?php
/**
 * Prompt Generator - Legal Blocks
 */

defined( 'ABSPATH' ) || exit;

function ws_prompt_legal_schema_block( string $record_type ): string {
    if ( $record_type === 'statute' ) {
        return "--------------------------------------------------------------------------------\nRECORD SCHEMA (STATUTE)\n\n"
            . "{\n\"identity\": {\"statute_id\": \"\",\n  \"official_name\": \"\",\n  \"citation\": \"\",\n  \"common_name\": \"\",\n  \"adverse_action_scope\": \"\",\n},\n"
            . "   \"legal_basis\": {\"disclosure_types\": [], \"protected_classes\": [], \"protected_class_details\": \"\", \"disclosure_targets\": [], \"disclosure_target_details\": \"\", \"employment_sectors\": []},\n"
            . "   \"deadlines\": {\"sol_value\": 0, \"sol_unit\": \"days\", \"sol_trigger\": \"\", \"has_limit_ambiguous\": false, \"limit_details\": \"\", \"has_tolling_details\": false, \"tolling_details\": \"\", \"has_exhaustion\": false, \"exhaustion_details\": \"\", \"has_employer_threshold\": false, \"employer_threshold_details\": \"\"},\n"
            . "   \"enforcement\": {\"process_types\": [], \"adverse_actions\": [], \"adverse_action_details\": \"\", \"fee_shiftings\": [], \"remedies\": [], \"remedy_details\": \"\", \"local_agencies\": [], \"federal_agencies\": [], \"enforcement_channel\": \"\"},\n"
            . "   \"burden_of_proof\": {\"employee_standards\": [], \"" . "employee_standard_details\": \"\", \"employer_defenses\": [], \"employer_defense_details\": \"\", \"{\"has_rebuttable_presumption\": false, \"rebuttable_presumption\": \", \"{\"has_bop_details\": false, \"{\"bop_details\": \", \"{\"bop_flag\": \"},\n},\n"
            . "   \"reward\": {\"has_reward_available\": false, \"reward_details\": \"\",},\n}"
            . "   \"links\": {\"statute_url\": \"\", \"url_is_pdf\": false,},\n}\n\n";
    }

    if ( $record_type === 'common-law' ) {
        return "--------------------------------------------------------------------------------\nRECORD SCHEMA (COMMON-LAW)\n\n"
            . "{\n\"identity\": {\"doctrine_id\": \"\",\n  \"doctrine_name\": \"\",\n  \"common_name\": \"\",\n  \"precedent_url\": \"\",\n  \"precedent_url_is_pdf\": false,\n  \"public_policy_sources\": [],\n  \"other_sources\": \"\",\n  \"doctrine_basis\": \"\",\n  \"recognition_status\": \"\",\n  \"adverse_action_scope\": \"\",\n},\n"
            . "   \"legal_basis\": {\"disclosure_types\": [], \"protected_classes\": [], \"protected_class_details\": \"\", \"disclosure_targets\": [], \"disclosure_target_details\": \"\", \"employment_sectors\": []},\n"
            . "   \"deadlines\": {\"sol_value\": 0, \"sol_unit\": \"\", \"sol_trigger\": \"\", \"has_limit_ambiguous\": false, \"limit_details\": \"\", \"has_tolling_details\": false, \"tolling_details\": \"\", \"has_exhaustion\": false, \"exhaustion_details\": \"\", \"has_employer_threshold\": false, \"employer_threshold_details\": \"\"},\n"
            . "   \"enforcement\": {\"process_types\": [], \"adverse_actions\": [], \"adverse_action_details\": \"\", \"fee_shiftings\": [], \"remedies\": [], \"remedy_details\": \"\", \"related_agencies\": []},\n"
            . "   \"burden_of_proof\": {\"has_statutory_preclusion\": false, \"statutory_preclusion_details\": \"\", \"employee_standards\": [], \"employee_standard_details\": \"\", \"employer_defenses\": [], \"employer_defense_details\": \"\", \"has_rebuttable_presumption\": false, \"rebuttable_presumption_details\": \"\", \"has_bop_details\": false, \"bop_details\": \"\", \"bop_flag\": \"\"},\n"
            . "   \"reward\": {\"has_reward_available\": false, \"reward_details\": \"\",},\n}\n\n";
    }

    if ( $record_type === 'citation' ) {
        return "--------------------------------------------------------------------------------\nRECORD SCHEMA (CITATION)\n\n"
            . "{\n\"identity\": {\"citation_id\": \"\",\n  \"citation_types\": [],\n  \"official_name\": \"\",\n  \"common_name\": \"\",\n  \"case_name\": \"\",\n  \"court\": \"\",\n  \"effective_date\": \"\",\n  \"specific_impact\": \"\",\n  \"summary\": \"\",\n  \"favorable\": true,\n},\n"
            . "   \"disclosure_types\": [], \"protected_classes\": [], \"protected_class_details\": \"\", \"disclosure_targets\": [], \"disclosure_target_details\": \"\", \"employment_sectors\": [], \"has_employer_threshold\": false, \"employer_threshold_details\": \"\",\n"
            . "   \"adverse_actions\": [], \"adverse_action_details\": \"\", \"process_types\": [], \"remedies\": [], \"remedy_details\": \"\", \"employer_defenses\": [], \"employer_defense_details\": \"\", \"employee_standards\": [], \"employee_standard_details\": \"\",\n}\n\n";
    }

    if ( $record_type === 'construction' ) {
        return "--------------------------------------------------------------------------------\nRECORD SCHEMA (CONSTRUCTION)\n\n"
            . "{\n\"identity\": {\"construction_id\": \"\",\n  \"official_name\": \"\",\n  \"common_name\": \"\",\n  \"case_name\": \"\",\n  \"case_citation\": \"\",\n  \"court\": \"\",\n  \"court_name\": \"\",\n  \"year\": 0,\n  \"effective_date\": \"\",\n  \"specific_impact\": \"\",\n  \"summary\": \"\",\n  \"favorable\": true,\n},\n"
            . "   \"disclosure_types\": [], \"protected_classes\": [], \"protected_class_details\": \"\", \"disclosure_targets\": [], \"disclosure_target_details\": \"\", \"employment_sectors\": [], \"has_employer_threshold\": false, \"employer_threshold_details\": \"\",\n"
            . "   \"adverse_actions\": [], \"adverse_action_details\": \"\", \"process_types\": [], \"remedies\": [], \"remedy_details\": \"\", \"employer_defenses\": [], \"employer_defense_details\": [], \"employee_standards\": [], \"employee_standard_details\": \",\n"
            . "   \"affected_jurisdictions\": [],\n}\n\n";
    }

    return '';
}

