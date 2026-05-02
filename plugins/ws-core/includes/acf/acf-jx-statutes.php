<?php
defined( 'ABSPATH' ) || exit;

/**
 * acf-jx-statutes.php
 *
 * Canonical ACF field registry for the `jx-statute` CPT.
 *
 * This file intentionally treats field definitions as the source of truth.
 * ACF registration and future prompt generation are both built from the same
 * compact args table.
 *
 * Legacy baseline: acf-jx-statutes.legacy.20260429.php. The Antigravity
 * first-pass file is retained only as a comparison artifact.
 *
 * Prompt group values:
 *   0 = do not attach to prompt
 *   1 = essential
 *   2 = expected
 *   3 = expected-if-found
 *   4 = conditional
 *   5 = optional
 *
 * @package    WhistleblowerShield
 * @since      2.0.0
 * @version    4.0.0-draft
 * 
 */

add_action( 'acf/init', 'ws_register_acf_jx_statutes' );
add_action( 'admin_init', 'ws_init_jx_statute_acf_fields' );

/**
 * Build and expose the canonical statute field table for admin tooling.
 * 
 */
function ws_init_jx_statute_acf_fields() {
    global $_ws_jx_statute_acf_fields;

    $_ws_jx_statute_acf_fields = ws_get_jx_statute_acf_fields();
}

/**
 * Return canonical, prefix-free statute ACF args.
 *
 * Field `name` values are stored without the `ws_jx_statute_` CPT infix.
 * `ws_build_acf_field()` applies storage names and field keys at registration.
 * 
 */
function ws_get_jx_statute_acf_tabs(): array {
    return [
        'identity' => [
            'label'  => 'Identity & Publishing',
            'fields' => [
                [ 'name' => 'jurisdiction', 'label' => 'Jurisdiction', 'type' => 'taxonomy', 'taxonomy' => WS_JURISDICTION_TAXONOMY, 'field_type' => 'radio', 'required' => 1, 'group' => 1 ],
                [ 'name' => 'official_name', 'required' => 1, 'group' => 1 ],
                [ 'name' => 'common_name', 'json_key' => 'title', 'group' => 5 ],
                [ 'name' => 'citation', 'json_key' => 'official_citation', 'label' => 'Citation', 'required' => 1, 'group' => 1 ],
                [ 'name' => 'protection_scope', 'type' => 'taxonomy', 'taxonomy' => 'ws_protection_scope', 'field_type' => 'select', 'group' => 2 ],
                [ 'name' => 'general_description', 'type' => 'textarea', 'rows' => 4, 'group' => 2 ],
                [ 'name' => 'has_attach_flag', 'group' => 0 ],
                [ 'name' => 'display_order', 'type' => 'number', 'group' => 0, 'conditional' => [ 'field' => 'has_attach_flag', 'operator' => '==', 'value' => '1' ] ],
            ],
        ],

        'effective_date' => [
            'label'  => 'Effective Date',
            'fields' => [
                [ 'name' => 'date', 'json_key' => 'enacted_date', 'type' => 'date_picker', 'group' => 2 ],
                [ 'name' => 'has_effective_date', 'group' => 0 ],
                [ 'name' => 'effective_date', 'type' => 'date_picker', 'group' => 4, 'conditional' => [ 'field' => 'has_effective_date', 'operator' => '==', 'value' => '1' ] ],
                [ 'name' => 'effective_year', 'type' => 'number', 'group' => 0, 'readonly' => 1 ],
                [ 'name' => 'retro_date', 'type' => 'date_picker', 'group' => 4, 'sister' => 'retro_context' ],
                [ 'name' => 'retro_context', 'type' => 'textarea', 'rows' => 3, 'group' => 4, 'conditional' => [ 'field' => 'legal_recognitions', 'taxonomy' => 'ws_legal_recognition', 'slug' => 'retroactive-date' ] ],
            ],
        ],

        'classifications' => [
            'label'  => 'Classifications',
            'fields' => [
                [ 'name' => 'legal_recognitions', 'type' => 'taxonomy', 'taxonomy' => 'ws_legal_recognition', 'group' => 2 ],
                [ 'name' => 'manager_rule_exclusion_context', 'type' => 'textarea', 'rows' => 3, 'group' => 4, 'conditional' => [ 'field' => 'legal_recognitions', 'taxonomy' => 'ws_legal_recognition', 'slug' => 'manager-rule-exclusion' ] ],
                [ 'name' => 'public_concern_context', 'type' => 'textarea', 'rows' => 3, 'group' => 4, 'conditional' => [ 'field' => 'legal_recognitions', 'taxonomy' => 'ws_legal_recognition', 'slug' => 'public-concern-required' ] ],
                [ 'name' => 'bad_faith_exclusion_context', 'type' => 'textarea', 'rows' => 3, 'group' => 4, 'conditional' => [ 'field' => 'legal_recognitions', 'taxonomy' => 'ws_legal_recognition', 'slug' => 'bad-faith-exclusion' ] ],
                [ 'name' => 'anonymity_protection_context', 'type' => 'textarea', 'rows' => 3, 'group' => 4, 'conditional' => [ 'field' => 'legal_recognitions', 'taxonomy' => 'ws_legal_recognition', 'slug' => 'anonymity-protection' ] ],
                [ 'name' => 'malicious_reporting_sanctions', 'type' => 'repeater', 'group' => 4, 'sub_fields' => [
                    [ 'name' => 'sanction_conduct', 'type' => 'select', 'multiple' => 1, 'choices' => [ 'knowingly-false', 'reckless-disregard', 'bad-faith-motive', 'see-context' ] ],
                    [ 'name' => 'sanction_penalty', 'type' => 'select', 'multiple' => 1, 'choices' => [ 'civil-fine', 'remedy-forfeiture', 'attorney-fee-shift', 'misdemeanor', 'felony', 'see-context' ] ],
                ], 'sister' => 'malicious_reporting_context' ],
                [ 'name' => 'malicious_reporting_context', 'type' => 'textarea', 'rows' => 3, 'group' => 4, 'conditional' => [ 'field' => 'legal_recognitions', 'taxonomy' => 'ws_legal_recognition', 'slug' => 'malicious-reporting-sanctions' ] ],
                [ 'name' => 'protected_action_standard', 'type' => 'select', 'choices' => [ 'per-se-protected', 'actual-violation', 'reasonable-belief', 'good-faith' ], 'group' => 4, 'sister' => 'protected_action_context' ],
                [ 'name' => 'reasonable_belief_scope', 'type' => 'select', 'choices' => [ 'objective-only', 'subjective-only', 'dual-prong', 'see-context' ], 'group' => 4, 'sister' => 'reasonable_belief_context' ],
                [ 'name' => 'reasonable_belief_context', 'type' => 'textarea', 'rows' => 3, 'group' => 4, 'conditional' => [ 'field' => 'protected_action_standard', 'operator' => '==', 'value' => 'reasonable-belief' ] ],
                [ 'name' => 'protected_action_source', 'type' => 'select', 'multiple' => 1, 'choices' => [ 'constitutional', 'statutory', 'judicial', 'regulatory', 'executive', 'see-context' ], 'group' => 4, 'sister' => 'protected_action_context' ],
                [ 'name' => 'protected_actions', 'json_key' => 'protected_activities', 'type' => 'taxonomy', 'taxonomy' => 'ws_protected_action', 'group' => 4, 'sister' => 'protected_action_context' ],
                [ 'name' => 'protected_action_context', 'type' => 'textarea', 'rows' => 3, 'group' => 4, 'conditional' => [ 'field' => 'legal_recognitions', 'taxonomy' => 'ws_legal_recognition', 'slug' => 'protected-action' ] ],
                [ 'name' => 'protected_disclosures', 'type' => 'taxonomy', 'taxonomy' => 'ws_protected_disclosure', 'group' => 2 ],
                [ 'name' => 'protected_classes', 'type' => 'taxonomy', 'taxonomy' => 'ws_protected_class', 'group' => 2 ],
                [ 'name' => 'former_employee_context', 'type' => 'textarea', 'rows' => 3, 'group' => 4, 'conditional' => [ 'field' => 'protected_classes', 'taxonomy' => 'ws_protected_class', 'slug' => 'former-employee' ] ],
                [ 'name' => 'protected_class_details', 'type' => 'textarea', 'rows' => 3, 'group' => 4, 'conditional' => [ 'field' => 'protected_classes', 'taxonomy' => 'ws_protected_class', 'slug' => 'has-details' ] ],
                [ 'name' => 'excluded_classes', 'type' => 'taxonomy', 'taxonomy' => 'ws_excluded_class', 'group' => 4, 'sister' => 'excluded_class_context' ],
                [ 'name' => 'excluded_class_context', 'type' => 'textarea', 'rows' => 3, 'group' => 4, 'conditional' => [ 'field' => 'legal_recognitions', 'taxonomy' => 'ws_legal_recognition', 'slug' => 'excluded-class' ] ],
                [ 'name' => 'excluded_class_details', 'type' => 'textarea', 'rows' => 3, 'group' => 4, 'conditional' => [ 'field' => 'excluded_classes', 'taxonomy' => 'ws_excluded_class', 'slug' => 'has-details' ] ],
                [ 'name' => 'employment_sectors', 'json_key' => 'covered_sector', 'type' => 'taxonomy', 'taxonomy' => 'ws_employment_sector', 'group' => 2 ],
                [ 'name' => 'disclosure_targets', 'type' => 'taxonomy', 'taxonomy' => 'ws_disclosure_target', 'group' => 2 ],
                [ 'name' => 'disclosure_channel_scope', 'type' => 'select', 'choices' => [ 'any-channel', 'approved-channel-only', 'mandatory-internal-first', 'see-context' ], 'group' => 4, 'sister' => 'disclosure_channel_context' ],
                [ 'name' => 'disclosure_format', 'type' => 'select', 'choices' => [ 'written-only', 'oral-permitted', 'either', 'has-details' ], 'group' => 4, 'sister' => 'disclosure_channel_context' ],
                [ 'name' => 'disclosure_format_details', 'type' => 'textarea', 'rows' => 3, 'group' => 4, 'conditional' => [ 'field' => 'disclosure_format', 'operator' => '==', 'value' => 'has-details' ] ],
                [ 'name' => 'disclosure_channel_context', 'type' => 'textarea', 'rows' => 3, 'group' => 4, 'conditional' => [ 'field' => 'protected_disclosures', 'taxonomy' => 'ws_protected_disclosure', 'slug' => 'has-channel-requirement' ] ],
                [ 'name' => 'ic_channel_sequence_context', 'type' => 'textarea', 'rows' => 3, 'group' => 4, 'conditional' => [ 'field' => 'protected_disclosures', 'taxonomy' => 'ws_protected_disclosure', 'slug' => 'ic-channel-required' ] ],
                [ 'name' => 'disclosure_target_details', 'type' => 'textarea', 'rows' => 3, 'group' => 4, 'conditional' => [ 'field' => 'disclosure_targets', 'taxonomy' => 'ws_disclosure_target', 'slug' => 'has-details' ] ],
            ],
        ],

        'sol' => [
            'label'  => 'Statute of Limitations & Thresholds',
            'fields' => [
                [ 'name' => 'sol_value', 'json_key' => 'statute_of_limitations', 'type' => 'number', 'group' => 2 ],
                [ 'name' => 'sol_unit', 'json_key' => 'limit_unit', 'type' => 'select', 'choices' => [ 'days', 'weeks', 'months', 'years' ], 'group' => 2 ],
                [ 'name' => 'sol_trigger', 'json_key' => 'limit_trigger', 'type' => 'select', 'choices' => [ 'filing-of-complaint', 'accrual', 'discovery-actual', 'discovery-constructive', 'discovery-notice', 'conclusion-of-admin-process', 'see-context' ], 'group' => 2 ],
                [ 'name' => 'sol_trigger_context', 'type' => 'textarea', 'rows' => 3, 'group' => 4, 'conditional' => [ 'field' => 'sol_trigger', 'operator' => '!=empty' ] ],
                [ 'name' => 'sol_trigger_event', 'type' => 'select', 'choices' => [ 'notice-of-action', 'occurrence-of-action', 'discovery-of-harm', 'constructive-discharge-accrual' ], 'group' => 3 ],
                [ 'name' => 'has_sol_details', 'json_key' => 'limit_ambiguous', 'group' => 0 ],
                [ 'name' => 'sol_details', 'json_key' => 'limit_details', 'type' => 'textarea', 'rows' => 3, 'group' => 4, 'conditional' => [ 'field' => 'has_sol_details', 'operator' => '==', 'value' => '1' ] ],
                [ 'name' => 'sop_value', 'type' => 'number', 'group' => 4, 'sister' => 'statute_of_repose_context' ],
                [ 'name' => 'sop_unit', 'type' => 'select', 'choices' => [ 'days', 'weeks', 'months', 'years' ], 'group' => 4, 'sister' => 'statute_of_repose_context' ],
                [ 'name' => 'is_sop_tolling_available', 'group' => 4, 'sister' => 'statute_of_repose_context' ],
                [ 'name' => 'statute_of_repose_context', 'type' => 'textarea', 'rows' => 3, 'group' => 4, 'conditional' => [ 'field' => 'legal_recognitions', 'taxonomy' => 'ws_legal_recognition', 'slug' => 'statute-of-repose' ] ],
                [ 'name' => 'statutory_tolling_context', 'type' => 'textarea', 'rows' => 3, 'group' => 4, 'conditional' => [ 'field' => 'legal_recognitions', 'taxonomy' => 'ws_legal_recognition', 'slug' => 'statutory-tolling' ] ],
                [ 'name' => 'equitable_tolling_context', 'type' => 'textarea', 'rows' => 3, 'group' => 4, 'conditional' => [ 'field' => 'legal_recognitions', 'taxonomy' => 'ws_legal_recognition', 'slug' => 'equitable-tolling' ] ],
                [ 'name' => 'cba_preemption_context', 'type' => 'textarea', 'rows' => 3, 'group' => 4, 'conditional' => [ 'field' => 'legal_recognitions', 'taxonomy' => 'ws_legal_recognition', 'slug' => 'cba-grievance-preemption' ] ],
                [ 'name' => 'amended_claim_context', 'type' => 'textarea', 'rows' => 3, 'group' => 4, 'conditional' => [ 'field' => 'legal_recognitions', 'taxonomy' => 'ws_legal_recognition', 'slug' => 'amended-claim' ] ],
                [ 'name' => 'exhaustion_required_class', 'type' => 'select', 'choices' => [ 'jurisdictional', 'claims-processing', 'waivable', 'see-context' ], 'group' => 4, 'sister' => 'exhaustion_required_context' ],
                [ 'name' => 'exhaustion_required_context', 'json_key' => 'exhaustion_pathway', 'type' => 'textarea', 'rows' => 3, 'group' => 4, 'conditional' => [ 'field' => 'legal_recognitions', 'taxonomy' => 'ws_legal_recognition', 'slug' => 'exhaustion-required' ] ],
                [ 'name' => 'filing_notice_value', 'type' => 'number', 'group' => 4, 'sister' => 'filing_notice_context' ],
                [ 'name' => 'filing_notice_unit', 'type' => 'select', 'choices' => [ 'days', 'weeks', 'months', 'years' ], 'group' => 4, 'sister' => 'filing_notice_context' ],
                [ 'name' => 'filing_notice_target', 'type' => 'select', 'choices' => [ 'employer', 'agency', 'attorney-general', 'labor-board', 'see-context' ], 'group' => 4, 'sister' => 'filing_notice_context' ],
                [ 'name' => 'filing_notice_context', 'type' => 'textarea', 'rows' => 3, 'group' => 4, 'conditional' => [ 'field' => 'legal_recognitions', 'taxonomy' => 'ws_legal_recognition', 'slug' => 'pre-filing-notice' ] ],
                [ 'name' => 'has_employer_threshold', 'group' => 0 ],
                [ 'name' => 'threshold_compare', 'type' => 'select', 'choices' => [ 'gte', 'lte', 'gt', 'lt', 'eq' ], 'group' => 4, 'sister' => 'employer_threshold_details' ],
                [ 'name' => 'threshold_value', 'type' => 'number', 'group' => 4, 'sister' => 'employer_threshold_details' ],
                [ 'name' => 'threshold_unit', 'type' => 'select', 'choices' => [ 'employees', 'contractors', 'workers', 'fte' ], 'group' => 4, 'sister' => 'employer_threshold_details' ],
                [ 'name' => 'employer_threshold_details', 'type' => 'textarea', 'rows' => 3, 'group' => 4, 'conditional' => [ 'field' => 'has_employer_threshold', 'operator' => '==', 'value' => '1' ] ],
                [ 'name' => 'has_cure_period', 'group' => 0 ],
                [ 'name' => 'cure_period_value', 'type' => 'number', 'group' => 4, 'sister' => 'cure_period_details' ],
                [ 'name' => 'cure_period_unit', 'type' => 'select', 'choices' => [ 'days', 'weeks', 'months', 'years' ], 'group' => 4, 'sister' => 'cure_period_details' ],
                [ 'name' => 'cure_period_details', 'type' => 'textarea', 'rows' => 3, 'group' => 4, 'conditional' => [ 'field' => 'has_cure_period', 'operator' => '==', 'value' => '1' ] ],
                [ 'name' => 'has_preemption', 'group' => 0 ],
                [ 'name' => 'preemption_direction', 'type' => 'select', 'choices' => [ 'federal-preempts-state', 'state-not-preempted', 'partial', 'see-details' ], 'group' => 4, 'sister' => 'preemption_details' ],
                [ 'name' => 'preemption_details', 'type' => 'textarea', 'rows' => 3, 'group' => 4, 'conditional' => [ 'field' => 'has_preemption', 'operator' => '==', 'value' => '1' ] ],
            ],
        ],

        'retaliation' => [
            'label'  => 'Retaliation',
            'fields' => [
                [ 'name' => 'adverse_actions', 'type' => 'taxonomy', 'taxonomy' => 'ws_adverse_action', 'group' => 2 ],
                [ 'name' => 'adverse_action_details', 'type' => 'textarea', 'rows' => 3, 'group' => 4, 'conditional' => [ 'field' => 'adverse_actions', 'taxonomy' => 'ws_adverse_action', 'slug' => 'has-details' ] ],
                [ 'name' => 'adverse_action_scope', 'type' => 'select', 'choices' => [ 'termination-only', 'material-adverse', 'broad-any-adverse-action', 'see-context' ], 'group' => 2 ],
                [ 'name' => 'adverse_action_scope_context', 'type' => 'textarea', 'rows' => 3, 'group' => 4, 'conditional' => [ 'field' => 'adverse_action_scope', 'operator' => '!=empty' ] ],
                [ 'name' => 'constructive_discharge_standard', 'type' => 'select', 'choices' => [ 'objective-intolerability', 'intent-required', 'dual-prong', 'see-context' ], 'group' => 4, 'sister' => 'constructive_discharge_context' ],
                [ 'name' => 'constructive_discharge_context', 'type' => 'textarea', 'rows' => 3, 'group' => 4, 'conditional' => [ 'field' => 'adverse_actions', 'taxonomy' => 'ws_adverse_action', 'slug' => 'constructive-discharge' ] ],
                [ 'name' => 'is_evidence_collection_protected', 'group' => 3 ],
                [ 'name' => 'anticipatory_retaliation_context', 'type' => 'textarea', 'rows' => 3, 'group' => 4, 'conditional' => [ 'field' => 'adverse_actions', 'taxonomy' => 'ws_adverse_action', 'slug' => 'anticipatory-retaliation' ] ],
                [ 'name' => 'cats_paw_liability_context', 'type' => 'textarea', 'rows' => 3, 'group' => 4, 'conditional' => [ 'field' => 'legal_recognitions', 'taxonomy' => 'ws_legal_recognition', 'slug' => 'cats-paw-liability' ] ],
                [ 'name' => 'is_cats_paw_liability_extended', 'group' => 4, 'sister' => 'cats_paw_liability_context' ],
                [ 'name' => 'third_party_retaliation_context', 'type' => 'textarea', 'rows' => 3, 'group' => 4, 'conditional' => [ 'field' => 'legal_recognitions', 'taxonomy' => 'ws_legal_recognition', 'slug' => 'third-party-retaliation' ] ],
                [ 'name' => 'criminal_sanctions', 'type' => 'repeater', 'group' => 4, 'sub_fields' => [
                    [ 'name' => 'sanction_conduct', 'type' => 'select', 'multiple' => 1, 'choices' => [ 'retaliation', 'disclosure', 'false-report', 'obstruction', 'see-context' ] ],
                    [ 'name' => 'sanction_level', 'type' => 'select', 'choices' => [ 'misdemeanor', 'felony', 'see-context' ] ],
                ], 'sister' => 'criminal_sanctions_context' ],
                [ 'name' => 'criminal_sanctions_context', 'type' => 'textarea', 'rows' => 3, 'group' => 4, 'conditional' => [ 'field' => 'legal_recognitions', 'taxonomy' => 'ws_legal_recognition', 'slug' => 'criminal-sanctions' ] ],
                [ 'name' => 'has_blacklisting_protection', 'group' => 0 ],
                [ 'name' => 'blacklisting_protection_details', 'type' => 'textarea', 'rows' => 3, 'group' => 4, 'conditional' => [ 'field' => 'has_blacklisting_protection', 'operator' => '==', 'value' => '1' ] ],
            ],
        ],

        'process_remedies' => [
            'label'  => 'Process & Remedies',
            'fields' => [
                [ 'name' => 'process_types', 'type' => 'taxonomy', 'taxonomy' => 'ws_process_type', 'group' => 2 ],
                [ 'name' => 'primary_agency', 'type' => 'post_object', 'post_type' => [ 'ws-agency' ], 'multiple' => 0, 'group' => 3 ],
                [ 'name' => 'local_agencies', 'type' => 'post_object', 'post_type' => [ 'ws-agency' ], 'multiple' => 1, 'group' => 3 ],
                [ 'name' => 'federal_agencies', 'type' => 'post_object', 'post_type' => [ 'ws-agency' ], 'multiple' => 1, 'group' => 3 ],
                [ 'name' => 'enforcement_priority', 'type' => 'select', 'choices' => [ 'agency-first', 'court-first', 'either', 'sequential' ], 'group' => 2 ],
                [ 'name' => 'enforcement_channel', 'type' => 'textarea', 'rows' => 3, 'group' => 2 ],
                [ 'name' => 'private_roa_context', 'type' => 'textarea', 'rows' => 3, 'group' => 4, 'conditional' => [ 'field' => 'legal_recognitions', 'taxonomy' => 'ws_legal_recognition', 'slug' => 'private-right-of-action' ] ],
                [ 'name' => 'jury_trial_scope', 'type' => 'select', 'choices' => [ 'all-claims', 'damages-only', 'liability-only', 'see-context' ], 'group' => 4, 'sister' => 'jury_trial_context' ],
                [ 'name' => 'jury_trial_context', 'type' => 'textarea', 'rows' => 3, 'group' => 4, 'conditional' => [ 'field' => 'legal_recognitions', 'taxonomy' => 'ws_legal_recognition', 'slug' => 'jury-trial' ] ],
                [ 'name' => 'fee_shifting_standard', 'type' => 'select', 'choices' => [ 'bilateral-loser-pays', 'unilateral-pro-plaintiff', 'unilateral-pro-defendant', 'prevailing-defendant-bad-faith', 'see-context' ], 'group' => 2 ],
                [ 'name' => 'fee_shifting_scope', 'type' => 'select', 'choices' => [ 'mandatory', 'discretionary', 'none' ], 'group' => 4, 'sister' => 'fee_shifting_standard_context' ],
                [ 'name' => 'has_fee_shifting_phases', 'group' => 0 ],
                [ 'name' => 'fee_shifting_phases', 'type' => 'textarea', 'rows' => 3, 'group' => 4, 'conditional' => [ 'field' => 'has_fee_shifting_phases', 'operator' => '==', 'value' => '1' ] ],
                [ 'name' => 'fee_shifting_asymmetry', 'type' => 'select', 'choices' => [ 'none', 'two-way', 'one-way-plaintiff', 'one-way-defendant-frivolous', 'see-context' ], 'group' => 2, 'sister' => 'fee_shifting_standard_context' ],
                [ 'name' => 'fee_shifting_standard_context', 'type' => 'textarea', 'rows' => 3, 'group' => 4, 'conditional' => [ 'field' => 'fee_shifting_standard', 'operator' => '!=empty' ] ],
                [ 'name' => 'remedies', 'json_key' => 'available_remedies', 'type' => 'taxonomy', 'taxonomy' => 'ws_remedy', 'group' => 2 ],
                [ 'name' => 'remedy_limits', 'type' => 'textarea', 'rows' => 3, 'group' => 4, 'conditional' => [ 'field' => 'remedies', 'taxonomy' => 'ws_remedy', 'slug' => 'has-limits' ] ],
                [ 'name' => 'remedy_details', 'type' => 'textarea', 'rows' => 3, 'group' => 4, 'conditional' => [ 'field' => 'remedies', 'taxonomy' => 'ws_remedy', 'slug' => 'has-details' ] ],
                [ 'name' => 'remedy_liquidated_multiplier', 'type' => 'select', 'choices' => [ 'double', 'treble', '2x-back-pay', '2x-wages-lost', 'statutory-formula', 'statutory-daily-fine', 'up-to-double', 'up-to-treble', 'has-details' ], 'group' => 4, 'conditional' => [ 'field' => 'remedies', 'taxonomy' => 'ws_remedy', 'slug' => 'liquidated-damages' ] ],
                [ 'name' => 'remedy_liquidated_formula', 'type' => 'textarea', 'rows' => 3, 'group' => 4, 'conditional' => [ 'field' => 'remedy_liquidated_multiplier', 'operator' => '==', 'value' => 'statutory-formula' ] ],
                [ 'name' => 'remedy_liquidated_details', 'type' => 'textarea', 'rows' => 3, 'group' => 4, 'conditional' => [ 'field' => 'remedy_liquidated_multiplier', 'operator' => '==', 'value' => 'has-details' ] ],
                [ 'name' => 'mitigation_required', 'type' => 'select', 'choices' => [ 'yes-statutory', 'yes-common-law', 'no', 'has-details' ], 'group' => 2 ],
                [ 'name' => 'mitigation_required_details', 'type' => 'textarea', 'rows' => 3, 'group' => 4, 'conditional' => [ 'field' => 'mitigation_required', 'operator' => '==', 'value' => 'has-details' ] ],
                [ 'name' => 'preliminary_reinstatement_standard', 'type' => 'select', 'choices' => [ 'mandatory', 'discretionary', 'has-details' ], 'group' => 4, 'sister' => 'preliminary_reinstatement_context' ],
                [ 'name' => 'reinstatement_standard_details', 'type' => 'textarea', 'rows' => 3, 'group' => 4, 'conditional' => [ 'field' => 'preliminary_reinstatement_standard', 'operator' => '==', 'value' => 'has-details' ] ],
                [ 'name' => 'preliminary_reinstatement_scope', 'type' => 'select', 'choices' => [ 'admin-phase', 'full-pendency', 'both' ], 'group' => 4, 'sister' => 'preliminary_reinstatement_context' ],
                [ 'name' => 'preliminary_reinstatement_context', 'type' => 'textarea', 'rows' => 3, 'group' => 4, 'conditional' => [ 'field' => 'legal_recognitions', 'taxonomy' => 'ws_legal_recognition', 'slug' => 'preliminary-reinstatement' ] ],
                [ 'name' => 'mixed_motive_remedy_context', 'type' => 'textarea', 'rows' => 3, 'group' => 4, 'conditional' => [ 'field' => 'burden_shifting_framework', 'operator' => '==', 'value' => 'mixed-motive' ] ],
                [ 'name' => 'election_of_remedies_rules', 'type' => 'select', 'multiple' => 1, 'choices' => [ 'administrative-bars-civil', 'state-bars-federal', 'remedy-exclusivity', 'first-filed-controls', 'no-election-required', 'see-context' ], 'group' => 2 ],
                [ 'name' => 'election_of_remedies_context', 'type' => 'textarea', 'rows' => 3, 'group' => 4, 'conditional' => [ 'field' => 'election_of_remedies_rules', 'operator' => '!=empty' ] ],
            ],
        ],

        'waiver_scope' => [
            'label'  => 'Waiver & Scope',
            'fields' => [
                [ 'name' => 'civil_action_waiver_scope', 'type' => 'select', 'choices' => [ 'prohibited', 'permitted-individual-only', 'permitted-collective', 'anti', 'see-context' ], 'group' => 2 ],
                [ 'name' => 'civil_action_waiver_context', 'type' => 'textarea', 'rows' => 3, 'group' => 4, 'conditional' => [ 'field' => 'civil_action_waiver_scope', 'operator' => '!=empty' ] ],
                [ 'name' => 'contractual_waiver_scope', 'type' => 'select', 'choices' => [ 'void', 'limited', 'enforceable', 'void-public-policy', 'void-as-to-whistleblowing', 'enforceable-with-exceptions', 'see-context' ], 'group' => 4, 'sister' => 'contractual_waiver_context' ],
                [ 'name' => 'contractual_waiver_context', 'type' => 'textarea', 'rows' => 3, 'group' => 4, 'conditional' => [ 'field' => 'legal_recognitions', 'taxonomy' => 'ws_legal_recognition', 'slug' => 'contractual-waiver' ] ],
                [ 'name' => 'waiver_of_collateral_claims_context', 'type' => 'textarea', 'rows' => 3, 'group' => 4, 'conditional' => [ 'field' => 'legal_recognitions', 'taxonomy' => 'ws_legal_recognition', 'slug' => 'waiver-of-collateral-claims' ] ],
                [ 'name' => 'proper_defendants', 'type' => 'select', 'multiple' => 1, 'choices' => [ 'employer-entity-only', 'individual-supervisors', 'government-agency-only', 'contractors-included', 'successor-employer', 'joint-employer', 'staffing-agency', 'scope-of-employment-required', 'has-details' ], 'group' => 2 ],
                [ 'name' => 'proper_defendant_details', 'type' => 'textarea', 'rows' => 3, 'group' => 4, 'conditional' => [ 'field' => 'proper_defendants', 'operator' => '==', 'value' => 'has-details' ] ],
                [ 'name' => 'joint_employer_context', 'type' => 'textarea', 'rows' => 3, 'group' => 4, 'conditional' => [ 'relation' => 'OR', 'rules' => [
                    [ 'field' => 'proper_defendants', 'operator' => '==', 'value' => 'joint-employer' ],
                    [ 'field' => 'proper_defendants', 'operator' => '==', 'value' => 'staffing-agency' ],
                ] ] ],
                [ 'name' => 'individual_liability_scope', 'type' => 'select', 'multiple' => 1, 'choices' => [ 'supervisor', 'coworker', 'officer-director', 'any-individual', 'has-details' ], 'group' => 4, 'sister' => 'individual_liability_context' ],
                [ 'name' => 'individual_liability_context', 'type' => 'textarea', 'rows' => 3, 'group' => 4, 'conditional' => [ 'field' => 'legal_recognitions', 'taxonomy' => 'ws_legal_recognition', 'slug' => 'individual-liability' ] ],
                [ 'name' => 'sovereign_immunity_limits', 'type' => 'select', 'multiple' => 1, 'choices' => [ 'not-waived', 'partially-waived', 'fully-waived', 'cap-applies', 'conditions-apply', 'has-details' ], 'group' => 2 ],
                [ 'name' => 'sovereign_immunity_scope', 'type' => 'select', 'multiple' => 1, 'choices' => [ 'state-only', 'instrumentalities-included', 'political-subdivisions-included', 'all', 'see-details' ], 'group' => 4, 'sister' => 'sovereign_immunity_limits_details' ],
                [ 'name' => 'sovereign_immunity_waiver', 'type' => 'select', 'choices' => [ 'explicit-waiver', 'implied-waiver', 'none', 'not-applicable' ], 'group' => 4, 'sister' => 'sovereign_immunity_limits_details' ],
                [ 'name' => 'sovereign_immunity_limits_details', 'type' => 'textarea', 'rows' => 3, 'group' => 4, 'conditional' => [ 'field' => 'sovereign_immunity_limits', 'operator' => '==', 'value' => 'has-details' ] ],
                [ 'name' => 'nda_limits_context', 'type' => 'textarea', 'rows' => 3, 'group' => 4, 'conditional' => [ 'field' => 'legal_recognitions', 'taxonomy' => 'ws_legal_recognition', 'slug' => 'nda-limitations' ] ],
                [ 'name' => 'anti_gag_provision_context', 'type' => 'textarea', 'rows' => 3, 'group' => 4, 'conditional' => [ 'field' => 'legal_recognitions', 'taxonomy' => 'ws_legal_recognition', 'slug' => 'anti-gag-provision' ] ],
                [ 'name' => 'no_retaliatory_evidence_context', 'type' => 'textarea', 'rows' => 3, 'group' => 4, 'conditional' => [ 'field' => 'legal_recognitions', 'taxonomy' => 'ws_legal_recognition', 'slug' => 'no-retaliatory-evidence' ] ],
                [ 'name' => 'stay_of_discipline_context', 'type' => 'textarea', 'rows' => 3, 'group' => 4, 'conditional' => [ 'field' => 'legal_recognitions', 'taxonomy' => 'ws_legal_recognition', 'slug' => 'stay-of-disciplinary-action' ] ],
                [ 'name' => 'anti_slapp_protection_scope', 'type' => 'select', 'multiple' => 0, 'choices' => [ 'motion-to-strike', 'discovery-stay', 'fee-shift-on-motion', 'full-procedural', 'see-context' ], 'group' => 4, 'sister' => 'anti_slapp_protection_context' ],
                [ 'name' => 'anti_slapp_protection_context', 'type' => 'textarea', 'rows' => 3, 'group' => 4, 'conditional' => [ 'field' => 'legal_recognitions', 'taxonomy' => 'ws_legal_recognition', 'slug' => 'anti-slapp-protection' ] ],
                [ 'name' => 'discovery_protection_context', 'type' => 'textarea', 'rows' => 3, 'group' => 4, 'conditional' => [ 'field' => 'legal_recognitions', 'taxonomy' => 'ws_legal_recognition', 'slug' => 'discovery-protection' ] ],
                [ 'name' => 'settlement_restriction_scope', 'type' => 'select', 'multiple' => 0, 'choices' => [ 'amount-only', 'facts', 'full-prohibition', 'agency-notification', 'see-context' ], 'group' => 4, 'sister' => 'settlement_restriction_context' ],
                [ 'name' => 'settlement_restriction_context', 'type' => 'textarea', 'rows' => 3, 'group' => 4, 'conditional' => [ 'field' => 'legal_recognitions', 'taxonomy' => 'ws_legal_recognition', 'slug' => 'confidential-settlement-restriction' ] ],
                [ 'name' => 'successor_liability_context', 'type' => 'textarea', 'rows' => 3, 'group' => 4, 'conditional' => [ 'field' => 'legal_recognitions', 'taxonomy' => 'ws_legal_recognition', 'slug' => 'successor-liability' ] ],
                [ 'name' => 'extraterritorial_context', 'type' => 'textarea', 'rows' => 3, 'group' => 4, 'conditional' => [ 'field' => 'legal_recognitions', 'taxonomy' => 'ws_legal_recognition', 'slug' => 'extraterritorial-coverage' ] ],
            ],
        ],

        'bop' => [
            'label'  => 'Burden Of Proof',
            'fields' => [
                [ 'name' => 'has_burden_shifting', 'group' => 0 ],
                [ 'name' => 'burden_shifting_framework', 'type' => 'select', 'choices' => [ 'mcdonnell-douglas', 'motivating-factor', 'but-for', 'mixed-motive', 'see-context' ], 'choice_labels' => [ 'mcdonnell-douglas' => 'McDonnell Douglas' ], 'group' => 2, 'sister' => 'burden_shifting_details' ],
                [ 'name' => 'burden_shifting_context', 'type' => 'textarea', 'rows' => 3, 'group' => 4, 'conditional' => [ 'field' => 'burden_shifting_framework', 'operator' => '!=empty' ] ],
                [ 'name' => 'burden_shifting_details', 'type' => 'textarea', 'rows' => 3, 'group' => 4, 'conditional' => [ 'field' => 'burden_shifting_framework', 'operator' => '!=empty' ] ],
                [ 'name' => 'employee_standard', 'type' => 'taxonomy', 'taxonomy' => 'ws_employee_standard', 'field_type' => 'radio', 'group' => 2 ],
                [ 'name' => 'employee_standard_details', 'type' => 'textarea', 'rows' => 3, 'group' => 4, 'conditional' => [ 'field' => 'employee_standard', 'taxonomy' => 'ws_employee_standard', 'slug' => 'has-details' ] ],
                [ 'name' => 'causation_standard', 'type' => 'taxonomy', 'taxonomy' => 'ws_causation_standard', 'field_type' => 'radio', 'group' => 2 ],
                [ 'name' => 'causation_application', 'type' => 'select', 'choices' => [ 'liability', 'damages', 'both', 'see-context' ], 'group' => 4, 'sister' => 'causation_standard_context' ],
                [ 'name' => 'causation_application_context', 'type' => 'textarea', 'rows' => 3, 'group' => 4, 'conditional' => [ 'field' => 'causation_application', 'operator' => '!=empty' ] ],
                [ 'name' => 'causation_standard_context', 'type' => 'textarea', 'rows' => 3, 'group' => 4, 'conditional' => [ 'field' => 'causation_standard', 'operator' => '!=empty' ] ],
                [ 'name' => 'employer_knowledge_scope', 'type' => 'select', 'choices' => [ 'actual-knowledge', 'constructive-knowledge', 'inferred-knowledge', 'imputed-knowledge', 'has-details' ], 'group' => 4, 'sister' => 'employer_knowledge_context' ],
                [ 'name' => 'employer_knowledge_scope_details', 'type' => 'textarea', 'rows' => 3, 'group' => 4, 'conditional' => [ 'field' => 'employer_knowledge_scope', 'operator' => '==', 'value' => 'has-details' ] ],
                [ 'name' => 'employer_knowledge_context', 'type' => 'textarea', 'rows' => 3, 'group' => 4, 'conditional' => [ 'field' => 'legal_recognitions', 'taxonomy' => 'ws_legal_recognition', 'slug' => 'employer-knowledge' ] ],
                [ 'name' => 'employer_defenses', 'type' => 'taxonomy', 'taxonomy' => 'ws_employer_defense', 'group' => 2 ],
                [ 'name' => 'employer_defense_details', 'type' => 'textarea', 'rows' => 3, 'group' => 4, 'conditional' => [ 'field' => 'employer_defenses', 'taxonomy' => 'ws_employer_defense', 'slug' => 'has-details' ] ],
                [ 'name' => 'has_rebuttable_presumption', 'group' => 0 ],
                [ 'name' => 'rebuttable_presumption_details', 'json_key' => 'rebuttable_presumption', 'type' => 'textarea', 'rows' => 3, 'group' => 4, 'conditional' => [ 'field' => 'has_rebuttable_presumption', 'operator' => '==', 'value' => '1' ] ],
                [ 'name' => 'has_temporal_presumption', 'group' => 0 ],
                [ 'name' => 'presumption_window_value', 'type' => 'number', 'group' => 4, 'sister' => 'temporal_presumption_details' ],
                [ 'name' => 'presumption_window_unit', 'type' => 'select', 'choices' => [ 'days', 'weeks', 'months', 'years' ], 'group' => 4, 'sister' => 'temporal_presumption_details' ],
                [ 'name' => 'presumption_effect', 'type' => 'select', 'choices' => [ 'shifts-burden', 'creates-inference', 'rebuttable-presumption', 'has-details' ], 'group' => 4, 'sister' => 'temporal_presumption_details' ],
                [ 'name' => 'presumption_effect_details', 'type' => 'textarea', 'rows' => 3, 'group' => 4, 'conditional' => [ 'field' => 'presumption_effect', 'operator' => '==', 'value' => 'has-details' ] ],
                [ 'name' => 'temporal_presumption_details', 'type' => 'textarea', 'rows' => 3, 'group' => 4, 'conditional' => [ 'field' => 'has_temporal_presumption', 'operator' => '==', 'value' => '1' ] ],
                [ 'name' => 'has_bop_details', 'group' => 0 ],
                [ 'name' => 'bop_details', 'json_key' => 'burden_of_proof_details', 'type' => 'textarea', 'rows' => 3, 'group' => 4, 'conditional' => [ 'field' => 'has_bop_details', 'operator' => '==', 'value' => '1' ] ],
            ],
        ],

        'reward' => [
            'label'  => 'Reward',
            'fields' => [
                [ 'name' => 'has_reward', 'json_key' => 'has_reward_available', 'group' => 0 ],
                [ 'name' => 'reward_discretion_standard', 'type' => 'select', 'choices' => [ 'mandatory', 'discretionary', 'presumptive', 'formula-based', 'has-details' ], 'group' => 4, 'sister' => 'reward_details' ],
                [ 'name' => 'reward_discretion_formula', 'type' => 'textarea', 'rows' => 3, 'group' => 4, 'conditional' => [ 'field' => 'reward_discretion_standard', 'operator' => '==', 'value' => 'formula-based' ] ],
                [ 'name' => 'reward_discretion_details', 'type' => 'textarea', 'rows' => 3, 'group' => 4, 'conditional' => [ 'field' => 'reward_discretion_standard', 'operator' => '==', 'value' => 'has-details' ] ],
                [ 'name' => 'reward_details', 'type' => 'textarea', 'rows' => 3, 'group' => 4, 'conditional' => [ 'field' => 'has_reward', 'operator' => '==', 'value' => '1' ] ],
                [ 'name' => 'qui_tam_government_share', 'group' => 4, 'sister' => 'qui_tam_share_context' ],
                [ 'name' => 'qui_tam_relator_share', 'group' => 4, 'sister' => 'qui_tam_share_context' ],
                [ 'name' => 'qui_tam_reduction_context', 'type' => 'textarea', 'rows' => 3, 'group' => 4, 'sister' => 'qui_tam_share_context' ],
                [ 'name' => 'qui_tam_share_context', 'type' => 'textarea', 'rows' => 3, 'group' => 4, 'conditional' => [ 'field' => 'process_types', 'taxonomy' => 'ws_process_type', 'slug' => 'qui-tam' ] ],
                [ 'name' => 'has_first_to_file_bar', 'group' => 0 ],
                [ 'name' => 'first_to_file_context', 'type' => 'textarea', 'rows' => 3, 'group' => 4, 'conditional' => [ 'field' => 'has_first_to_file_bar', 'operator' => '==', 'value' => '1' ] ],
                [ 'name' => 'has_public_disclosure_bar', 'group' => 0 ],
                [ 'name' => 'public_disclosure_bar_context', 'type' => 'textarea', 'rows' => 3, 'group' => 4, 'conditional' => [ 'field' => 'has_public_disclosure_bar', 'operator' => '==', 'value' => '1' ] ],
            ],
        ],

        'relationships' => [
            'label'  => 'Relationships',
            'fields' => [
                [ 'name' => 'ref_materials', 'type' => 'post_object', 'post_type' => [ 'ws-reference' ], 'multiple' => 1, 'return_format' => 'object', 'group' => 5 ],
                [ 'name' => 'citation_ids', 'type' => 'relationship', 'post_type' => [ 'jx-citation' ], 'group' => 0 ],
                [ 'name' => 'construction_ids', 'type' => 'relationship', 'post_type' => [ 'jx-construction' ], 'group' => 0 ],
                [ 'name' => 'overruled_by_id', 'type' => 'post_object', 'post_type' => [ 'jx-statute', 'jx-common-law', 'jx-citation', 'jx-construction' ], 'multiple' => 0, 'group' => 0 ],
            ],
        ],

        'source_audit' => [
            'label'  => 'Source / Audit',
            'fields' => [
                [ 'name' => 'last_reviewed_date', 'type' => 'date_picker', 'group' => 0 ],
                [ 'name' => 'url', 'json_key' => 'statute_url', 'type' => 'url', 'group' => 2 ],
                [ 'name' => 'url_is_pdf', 'group' => 3 ],
                [ 'name' => 'authority_reference', 'type' => 'textarea', 'rows' => 3, 'group' => 3 ],
            ],
        ],

        'hidden' => [
            'label'  => 'Hidden',
            'fields' => [
                [ 'name' => '_id', 'group' => 0 ],
                [ 'name' => '_disclosure_target_class', 'type' => 'select', 'choices' => [ 'internal', 'external', 'both' ], 'group' => 0 ],
                [ 'name' => '_precedent_ids', 'type' => 'relationship', 'post_type' => [ 'jx-citation', 'jx-construction' ], 'group' => 0 ],
                [ 'name' => '_primary_agency_is_fed', 'group' => 0 ],
                [ 'name' => '_related_agencies', 'type' => 'post_object', 'post_type' => [ 'ws-agency' ], 'multiple' => 1, 'group' => 0 ],
            ],
        ],
    ];
}

/**
 * Return the statute ACF table with field-level metadata normalized.
 *
 * The raw table is grouped by tab for human readability. This pass stores the
 * tab key on each field so the same field args can drive ACF registration,
 * ingest mapping, and prompt JSON grouping without recomputing context later.
 * 
 */
function ws_get_jx_statute_acf_fields(): array {
    $tabs = ws_get_jx_statute_acf_tabs();

    foreach ( $tabs as $tab_key => &$tab ) {
        foreach ( $tab['fields'] as &$field ) {
            $field['tab']          = $field['tab'] ?? $tab_key;
            $field['json_key']     = $field['name'];
            $field['json_path']    = $field['json_path'] ?? $field['tab'] . '.' . $field['json_key'];
            $field['prompt_group'] = (int) ( $field['group'] ?? 0 );
        }
        unset( $field );
    }
    unset( $tab );

    return $tabs;
}

/**
 * Register the statute ACF group from the canonical field table.
 * 
 */
function ws_register_acf_jx_statutes() {
    if ( ! function_exists( 'acf_add_local_field_group' ) ) {
        return;
    }

    $fields = [];
    $tabs   = ws_get_jx_statute_acf_fields();

    foreach ( $tabs as $tab_key => $tab ) {
        if ( 'hidden' !== $tab_key ) {
            $fields[] = [
                'key'       => "field_jx_statute_{$tab_key}_tab",
                'label'     => $tab['label'],
                'type'      => 'tab',
                'placement' => 'top',
            ];
        }

        foreach ( $tab['fields'] as $field_args ) {
            $fields[] = ws_build_acf_field( $field_args, $tab_key, 'statute' );
        }
    }

    acf_add_local_field_group( [
        'key'                   => 'group_jx_statute_metadata',
        'title'                 => 'Statute Details',
        'menu_order'            => 0,
        'position'              => 'normal',
        'style'                 => 'default',
        'label_placement'       => 'top',
        'instruction_placement' => 'label',
        'active'                => true,
        'location'              => [ [ [
            'param'    => 'post_type',
            'operator' => '==',
            'value'    => 'jx-statute',
        ] ] ],
        'fields'                => $fields,
    ] );
}

/**
 * Build one ACF local field array from compact canonical args.
 * 
 */
function ws_build_acf_field( array $args, string $tab, string $group ): array {
    $name       = $args['name'];
    $type       = $args['type'] ?? ws_infer_acf_field_type( $name );
    $field_name = str_starts_with( $name, '_' ) ? "_ws_jx_{$group}" . $name : "ws_jx_{$group}_{$name}";
    $field_key  = str_starts_with( $name, '_' ) ? "field_jx_{$group}" . $name : "field_jx_{$group}_{$name}";

    $field = [
        'key'      => $field_key,
        'label'    => $args['label'] ?? ws_label_from_field_name( $name ),
        'name'     => $field_name,
        'type'     => $type,
        'required' => (int) ( $args['required'] ?? 0 ),
    ];

    if ( ! empty( $args['instructions'] ) ) {
        $field['instructions'] = $args['instructions'];
    }

    if ( isset( $args['readonly'] ) ) {
        $field['readonly'] = (int) $args['readonly'];
    }

    if ( 'textarea' === $type ) {
        $field['rows'] = (int) ( $args['rows'] ?? 3 );
    }

    if ( 'number' === $type ) {
        $field['step'] = $args['step'] ?? 1;
    }

    if ( 'true_false' === $type ) {
        $field['ui']            = 1;
        $field['ui_on_text']    = $args['ui_on_text'] ?? 'Yes';
        $field['ui_off_text']   = $args['ui_off_text'] ?? 'No';
        $field['default_value'] = (int) ( $args['default_value'] ?? 0 );
    }

    if ( 'select' === $type ) {
        $field['choices']       = ws_acf_choices( $args['choices'] ?? [], $args['choice_labels'] ?? [] );
        $field['allow_null']    = $args['allow_null'] ?? 1;
        $field['ui']            = 1;
        $field['multiple']      = (int) ( $args['multiple'] ?? 0 );
        $field['return_format'] = 'value';
    }

    if ( 'taxonomy' === $type ) {
        $field['taxonomy']      = $args['taxonomy'];
        $field['field_type']    = $args['field_type'] ?? 'multi_select';
        $field['add_term']      = 0;
        $field['save_terms']    = $args['save_terms'] ?? 1;
        $field['load_terms']    = $args['load_terms'] ?? 1;
        $field['return_format'] = $args['return_format'] ?? 'id';
    }

    if ( in_array( $type, [ 'post_object', 'relationship' ], true ) ) {
        $field['post_type']     = $args['post_type'] ?? [];
        $field['allow_null']    = $args['allow_null'] ?? 1;
        $field['multiple']      = (int) ( $args['multiple'] ?? 1 );
        $field['return_format'] = $args['return_format'] ?? 'id';
        $field['ui']            = 1;
        if ( 'relationship' === $type ) {
            $field['filters']  = $args['filters'] ?? [ 'search' ];
            $field['elements'] = $args['elements'] ?? [];
        }
    }

    if ( 'repeater' === $type ) {
        $field['layout']     = $args['layout'] ?? 'row';
        $field['button_label'] = $args['button_label'] ?? 'Add Row';
        $field['sub_fields'] = [];
        foreach ( $args['sub_fields'] ?? [] as $sub_args ) {
            $field['sub_fields'][] = ws_build_acf_sub_field( $sub_args, $field_key );
        }
    }

    if ( ! empty( $args['conditional'] ) && empty( $args['conditional']['taxonomy'] ) ) {
        $field['conditional_logic'] = ws_build_acf_conditional_logic( $args['conditional'], $group );
    }

    return $field;
}

/**
 * Build a repeater sub-field. Sub-fields do not get CPT storage prefixes.
 * 
 */
function ws_build_acf_sub_field( array $args, string $parent_key ): array {
    $name = $args['name'];
    $type = $args['type'] ?? ws_infer_acf_field_type( $name );

    $field = [
        'key'      => "{$parent_key}_{$name}",
        'label'    => $args['label'] ?? ws_label_from_field_name( $name ),
        'name'     => $name,
        'type'     => $type,
        'required' => (int) ( $args['required'] ?? 0 ),
    ];

    if ( 'select' === $type ) {
        $field['choices']       = ws_acf_choices( $args['choices'] ?? [], $args['choice_labels'] ?? [] );
        $field['allow_null']    = 1;
        $field['ui']            = 1;
        $field['return_format'] = 'value';
    }

    return $field;
}

/**
 * Infer field type from naming rules.
 * 
 */
function ws_infer_acf_field_type( string $name ): string {
    if ( str_starts_with( $name, 'has_' ) || str_starts_with( $name, 'is_' ) || str_contains( $name, '_is_' ) ) {
        return 'true_false';
    }

    if ( str_ends_with( $name, '_url' ) ) {
        return 'url';
    }

    if ( str_ends_with( $name, '_date' ) ) {
        return 'date_picker';
    }

    if ( str_ends_with( $name, '_value' ) || str_ends_with( $name, '_year' ) || str_ends_with( $name, '_order' ) ) {
        return 'number';
    }

    if ( str_ends_with( $name, '_compare' ) || str_ends_with( $name, '_direction' ) ) {
        return 'select';
    }

    if ( str_ends_with( $name, '_details' ) || str_ends_with( $name, '_context' ) || str_contains( $name, 'description' ) || str_contains( $name, 'reference' ) ) {
        return 'textarea';
    }

    return 'text';
}

/**
 * Convert compact choice lists into ACF choice arrays.
 * 
 */
function ws_acf_choices( array $choices, array $choice_labels = [] ): array {
    $acf_choices = [];

    foreach ( $choices as $key => $label ) {
        if ( is_int( $key ) ) {
            $key   = $label;
            $label = $choice_labels[ $key ] ?? ucwords( str_replace( '-', ' ', (string) $label ) );
        } elseif ( isset( $choice_labels[ $key ] ) ) {
            $label = $choice_labels[ $key ];
        }
        $acf_choices[ (string) $key ] = (string) $label;
    }

    return $acf_choices;
}

/**
 * Human label from canonical field name.
 * 
 */
function ws_label_from_field_name( string $name ): string {
    $name = ltrim( $name, '_' );
    $name = str_replace( [ '_', 'bop', 'sol', 'sop', 'roa', 'cba', 'nda', 'slapp', 'ic' ], [ ' ', 'BOP', 'SOL', 'SOP', 'ROA', 'CBA', 'NDA', 'SLAPP', 'IC' ], $name );

    return ucwords( $name );
}

/**
 * Build ACF conditional logic for non-taxonomy conditions.
 * 
 */
function ws_build_acf_conditional_logic( array $conditional, string $group ): array {
    if ( ! empty( $conditional['rules'] ) ) {
        $logic = [];
        foreach ( $conditional['rules'] as $rule ) {
            $logic[] = [
                ws_build_acf_conditional_rule( $rule, $group ),
            ];
        }

        return 'AND' === strtoupper( $conditional['relation'] ?? 'OR' )
            ? [ array_column( $logic, 0 ) ]
            : $logic;
    }

    return [ [ ws_build_acf_conditional_rule( $conditional, $group ) ] ];
}

function ws_build_acf_conditional_rule( array $conditional, string $group ): array {
    $field = $conditional['field'];
    $key   = str_starts_with( $field, '_' ) ? "field_jx_{$group}{$field}" : "field_jx_{$group}_{$field}";

    return [
        'field'    => $key,
        'operator' => $conditional['operator'] ?? '==',
        'value'    => (string) ( $conditional['value'] ?? '' ),
    ];
}

/**
 * Return compact prompt-ready package for statute research generation.
 * 
 */
function ws_get_jx_statute_prompt_package(): array {
    $tabs          = ws_get_jx_statute_acf_fields();
    $fields        = [];
    $fields_by_tab = [];

    foreach ( $tabs as $tab_key => $tab ) {
        foreach ( $tab['fields'] as $field ) {
            $prompt_group = (int) ( $field['prompt_group'] ?? 0 );
            if ( 0 === $prompt_group ) {
                continue;
            }

            $prompt_field = [
                'name'          => $field['name'],
                'json_path'     => $field['json_path'],
                'json_key'      => $field['json_key'] ?? null,
                'tab'           => $field['tab'],
                'tab_label'     => $tab['label'],
                'group'         => $prompt_group,
                'type'          => $field['type'] ?? ws_infer_acf_field_type( $field['name'] ),
                'taxonomy'      => $field['taxonomy'] ?? null,
                'choices'       => $field['choices'] ?? null,
                'required'      => (int) ( $field['required'] ?? 0 ),
                'conditional'   => $field['conditional'] ?? null,
                'legal_prompt'  => $field['legal_prompt'] ?? ws_build_jx_statute_prompt_instruction( $field ),
            ];

            $fields[] = $prompt_field;
            $fields_by_tab[ $field['tab'] ][] = $prompt_field;
        }
    }

    return [
        'package'       => 'jx-statute',
        'post_type'     => 'jx-statute',
        'field_prefix'  => 'ws_jx_statute_',
        'prompt_groups' => [
            1 => 'essential',
            2 => 'expected',
            3 => 'expected-if-found',
            4 => 'conditional',
            5 => 'optional',
        ],
        'fields'        => $fields,
        'fields_by_tab' => $fields_by_tab,
    ];
}

/**
 * Build a simple default prompt instruction from field args.
 * 
 */
function ws_build_jx_statute_prompt_instruction( array $field ): string {
    $name = $field['json_key'] ?? $field['name'];
    $type = $field['type'] ?? ws_infer_acf_field_type( $name );

    if ( 'taxonomy' === $type ) {
        return "{$name} - array of {$field['taxonomy']} term slugs.";
    }

    if ( ! empty( $field['choices'] ) ) {
        return "{$name} - " . ( ! empty( $field['multiple'] ) ? 'array' : 'select' ) . ' of: ' . implode( ', ', $field['choices'] ) . '.';
    }

    if ( ! empty( $field['conditional'] ) ) {
        return "{$name} - conditional field; include only when triggered.";
    }

    return "{$name} - {$type}.";
}

/**
 * Apply taxonomy-sentinel conditional logic once term IDs exist.
 * 
 */
add_filter( 'acf/load_field', 'ws_jx_statute_apply_taxonomy_conditionals' );

function ws_jx_statute_apply_taxonomy_conditionals( $field ) {
    static $map = null;

    if ( null === $map ) {
        $map = [];
        foreach ( ws_get_jx_statute_acf_fields() as $tab ) {
            foreach ( $tab['fields'] as $args ) {
                if ( empty( $args['conditional']['taxonomy'] ) ) {
                    continue;
                }

                $target_key = str_starts_with( $args['name'], '_' )
                    ? 'field_jx_statute' . $args['name']
                    : 'field_jx_statute_' . $args['name'];

                $trigger = $args['conditional']['field'];
                $map[ $target_key ] = [
                    'taxonomy'    => $args['conditional']['taxonomy'],
                    'slug'        => $args['conditional']['slug'],
                    'trigger_key' => str_starts_with( $trigger, '_' )
                        ? 'field_jx_statute' . $trigger
                        : 'field_jx_statute_' . $trigger,
                ];
            }
        }
    }

    if ( empty( $map[ $field['key'] ] ) ) {
        return $field;
    }

    $rule = $map[ $field['key'] ];
    $term = get_term_by( 'slug', $rule['slug'], $rule['taxonomy'] );
    if ( ! $term || is_wp_error( $term ) ) {
        return $field;
    }

    $field['conditional_logic'] = [ [ [
        'field'    => $rule['trigger_key'],
        'operator' => '==',
        'value'    => (string) $term->term_id,
    ] ] ];

    return $field;
}

add_filter( 'acf/prepare_field/name=ws_jx_statute_local_agencies', 'ws_jx_statute_prepare_local_agencies_field' );
add_filter( 'acf/fields/post_object/query/name=ws_jx_statute_local_agencies', 'ws_jx_statute_local_agencies_query', 10, 3 );
add_filter( 'acf/fields/post_object/query/name=ws_jx_statute_federal_agencies', 'ws_jx_statute_federal_agencies_query', 10, 3 );

/**
 * Resolve the first jurisdiction term for a legal record post.
 *
 * Local helper for now; shape is intentionally generic enough to lift into a
 * shared helper file for all legal ACFs later.
 * 
 */
function ws_jx_legal_get_post_jurisdiction_term( $post_id ) {
    static $cache = [];

    $post_id = (int) $post_id;
    if ( ! $post_id ) {
        return null;
    }

    if ( array_key_exists( $post_id, $cache ) ) {
        return $cache[ $post_id ];
    }

    $terms = wp_get_post_terms( $post_id, WS_JURISDICTION_TAXONOMY );
    if ( is_wp_error( $terms ) || empty( $terms ) ) {
        $cache[ $post_id ] = null;
        return $cache[ $post_id ];
    }

    $cache[ $post_id ] = $terms[0];
    return $cache[ $post_id ];
}

function ws_jx_statute_get_term_for_post( $post_id ) {
    return ws_jx_legal_get_post_jurisdiction_term( $post_id );
}

function ws_jx_statute_prepare_local_agencies_field( $field ) {
    $post_id = (int) ( $_GET['post'] ?? 0 );
    $term    = ws_jx_legal_get_post_jurisdiction_term( $post_id );

    if ( $term && strtolower( (string) $term->slug ) === 'us' ) {
        return false;
    }

    return $field;
}

function ws_jx_legal_agency_query_for_jurisdiction( array $args, $jurisdiction ): array {
    $args['post_type'] = [ 'ws-agency' ];

    if ( $jurisdiction instanceof WP_Term ) {
        $args['tax_query'] = [ [
            'taxonomy' => WS_JURISDICTION_TAXONOMY,
            'field'    => 'term_id',
            'terms'    => [ (int) $jurisdiction->term_id ],
        ] ];
    }

    return $args;
}

function ws_jx_statute_local_agencies_query( $args, $field, $post_id ) {
    $term = ws_jx_legal_get_post_jurisdiction_term( $post_id );

    if ( ! $term || strtolower( (string) $term->slug ) === 'us' ) {
        return $args;
    }

    return ws_jx_legal_agency_query_for_jurisdiction( $args, $term );
}

function ws_jx_statute_federal_agencies_query( $args, $field, $post_id ) {
    static $us_term = null;

    if ( null === $us_term ) {
        $us_term = get_term_by( 'slug', 'us', WS_JURISDICTION_TAXONOMY ) ?: false;
    }

    return ws_jx_legal_agency_query_for_jurisdiction( $args, $us_term instanceof WP_Term ? $us_term : null );
}
