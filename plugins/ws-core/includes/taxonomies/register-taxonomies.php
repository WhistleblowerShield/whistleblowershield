<?php

/**
 * register-taxonomies.php — WhistleblowerShield Taxonomy Registry
 *
 * Single source of truth for all 21 ws-core taxonomies. $_ws_taxonomy_registry (global
 * array) drives registration, seeding, and LLM prompt generation from one place.
 * 
 * ws_jurisdiction taxonomy table use canonical table array in matrix-jurisdictions.php.
 *
 * Registration: ws_register_all_taxonomies() on init. Capabilities via ws_get_taxonomy_caps().
 * Seeding:      ws_seed_all_taxonomies() on admin_init, version-gated per taxonomy via
 *               ws_seeded_{slug} option key.
 *               ### [AFTER CODE IS DEPLOYED AND LIVE DATA IS SEEDED] ###
 *               Bump seed_version to trigger re-seed.
 *               ### [DO NOT BUMP BEFORE CODE IS DEPLOYED] ###
 * Term format:  'slug' => 'Label'           (flat or child of active parent)
 *               'slug' => ['Label', 1]      (top-level parent; next terms become children)
 * Sentinels:    has-parent / has-details / has-* — signals a companion ACF freetext field
 *               holds details or context not covered by available slugs.
 * Record keys:  'record' => ['legal', 'assist'] — marks which LLM record types use this
 *               taxonomy. Drives ws_get_taxonomies_for_record('legal'|'assist'), which
 *               returns taxonomy slug + labels + configured prompt string per entry.
 *
 * Two-phase registration (taxonomy before CPTs, object_type binding after):
 * See TAXONOMY TWO-PHASE BEHAVIOR in loader.php. Do not collapse.
 *
 * @package       WhistleblowerShield
 * @since         2.1.0
 * @version       3.20.0 [3.20 locked — never been deployed, no live data, bump patch only] — [Do not bump gates]
 * @author        Whistleblower Shield
 * @link          https://whistleblowershield.org
 * @copyright     Copyright (c) 2026 Whistleblower Shield
 *
 */
defined('ABSPATH') || exit;

require_once WS_CORE_PATH . 'includes/admin/matrix/matrix-jurisdictions.php';

$_ws_taxonomy_registry = [

// —— 1. Disclosure Categories ———————————————————————————————————————————————
/**
 * Assigns ws_protected_disclosure with its hierarchical term structure.
 *
 *
 * @todo legal_prompt, assist_prompt — set instruction strings.
 * 
 */
    'ws_protected_disclosure'  => [
        'cpts'                     => ['jx-statute', 'jx-common-law', 'jx-citation', 'jx-construction', 'ws-agency', 'ag-procedure', 'ws-assist-org'],
        'plural'                   => 'Disclosure Categories',
        'singular'                 => 'Disclosure Category',
        'menu_name'                => 'Disclosures',
        'hierarchical'             => true,
        'seed_version'             => '1.0.0',
        'record'                   => ['legal', 'assist'],
        'legal_prompt'             => '',
        'assist_prompt'            => '',
        'terms'                    => [
            'workplace-employment'       => ['Workplace & Employment', 1],
                'wage-hour-violations'           => 'Wage & Hour Violations',
                'occupational-health-safety'     => 'Occupational Health & Safety',
                'collective-bargaining'          => 'Collective Bargaining Rights',
            'financial-corporate'        => ['Financial & Corporate', 1],
                'securities-commodities-fraud'   => 'Securities & Commodities Fraud',
                'consumer-financial-protection'  => 'Consumer Financial Protection',
                'banking-aml-compliance'         => 'Banking & AML Compliance',
                'shareholder-rights'             => 'Shareholder Rights',
                'tax-evasion-fraud'              => 'Tax Evasion & Fraud',
            'government-accountability'  => ['Government Accountability', 1],
                'procurement-spending-fraud'     => 'Procurement & Spending Fraud',
                'public-corruption-ethics'       => 'Public Corruption & Ethics',
                'election-integrity'             => 'Election Integrity',
                'military-defense-reporting'     => 'Military & Defense Reporting',
                'mismanagement-of-funds'         => 'Mismanagement of Public Funds',
            'public-health-safety'       => ['Public Health & Safety', 1],
                'healthcare-medicare-fraud'      => 'Healthcare & Medicare Fraud',
                'environmental-protection'       => 'Environmental Protection',
                'food-drug-safety'               => 'Food & Drug Safety',
                'nuclear-energy-safety'          => 'Nuclear & Energy Safety',
                'transportation-safety'          => 'Transportation & Aviation Safety',
                'child-abuse-reporting'          => 'Child Abuse & Exploitation Reporting',
                'patient-abuse-reporting'        => 'Patient Abuse & Neglect Reporting',
            'privacy-data-integrity'     => ['Privacy & Data Integrity', 1],
                'cybersecurity-disclosure'       => 'Cybersecurity Disclosure',
                'hipaa-patient-privacy'          => 'HIPAA & Patient Privacy',
                'consumer-data-protection'       => 'Consumer Data Protection',
                'education-privacy-ferpa'        => 'Education Privacy (FERPA)',
            'national-security'          => ['National Security', 1],
                'intelligence-community'         => 'Intelligence Community Reporting',
                'classified-information'         => 'Classified Information Disclosures',
                'export-sanctions-compliance'    => 'Export Controls & Sanctions Compliance',
            'general-legal'              => ['General Legal', 1],
                'general-wrongdoing'             => 'General Wrongdoing / Violation of Law',
            'has-parent'                 => ['Has Sentinels', 1],
                'has-channel'                    => 'Has Channel Requirement',
                'has-ic-channel'                 => 'Intelligence Community Channel Required',
                
       ]
    ],

// —— 2. Process Types ———————————————————————————————————————————————————————
/**
 * Assigns ws_process_type with its flat term structure.
 * 
 * @todo legal_prompt, assist_prompt — set instruction strings.
 * 
 */
    'ws_process_type'  => [
        'cpts'             => ['jx-statute', 'jx-common-law', 'jx-citation', 'jx-construction', 'ws-agency', 'ws-assist-org'],
        'plural'           => 'Process Types',
        'singular'         => 'Process Type',
        'menu_name'        => 'Processes',
        'seed_version'     => '1.0.0',
        'record'           => ['legal', 'assist'],
        'legal_prompt'     => '',
        'assist_prompt'    => '',
        'terms'            => [
            'pre-suit-notice'          => 'Pre-Suit Notice',
            'administrative-complaint' => 'Administrative Complaint',
            'civil-lawsuit'            => 'Civil Lawsuit',
            'qui-tam'                  => 'Qui Tam (False Claims)',
            'internal-disclosure'      => 'Internal Disclosure',
            'regulatory-tip'           => 'Regulatory Tip',
            'criminal-referral'        => 'Criminal Referral',
            'state-agency-complaint'   => 'State Agency Complaint',
            'congressional-disclosure' => 'Congressional Disclosure',
            'representative-action'    => 'Representative Action',
            'arbitration-compelled'    => 'Arbitration Compelled',
            'hybrid-admin-civil-path'  => 'Hybrid Admin → Civil Pathway',
            'direct-filing-permitted'  => 'Direct Filing Permitted (No Exhaustion)',
        ]
    ],

// —— 3. Remedies ————————————————————————————————————————————————————————————
/**
 * Assigns ws_remedy with its flat term structure.
 *
 * @todo legal_prompt — set instruction string.
 * 
 */
    'ws_remedy'     => [
        'cpts'          => ['jx-statute', 'jx-common-law', 'jx-citation', 'jx-construction'],
        'plural'        => 'Remedies',
        'singular'      => 'Remedy',
        'seed_version'  => '1.0.0',
        'record'        => ['legal'],
        'legal_prompt'  => '',
        'terms'         => [
            'reinstatement'             => 'Reinstatement',
            'interim-reinstatement'     => 'Interim / Preliminary Reinstatement',
            'back-pay'                  => 'Back Pay',
            'interest-on-backpay'       => 'Interest on Back Pay',
            'front-pay'                 => 'Front Pay',
            'front-pay-in-lieu'         => 'Front Pay in Lieu of Reinstatement',
            'double-back-pay'           => 'Double Back Pay',
            'lost-wages'                => 'Lost Wages',
            'benefits-restoration'      => 'Benefits Restoration',
            'compensatory-damages'      => 'Compensatory Damages',
            'emotional-distress-damages' => 'Emotional Distress Damages',
            'punitive-damages'          => 'Punitive Damages',
            'treble-damages'            => 'Treble Damages',
            'civil-penalty'             => 'Civil Penalty',
            'attorney-fees'             => 'Attorney Fees',
            'attorney-fees-admin'       => 'Attorney Fees (Administrative Phase)',
            'litigation-costs'          => 'Litigation Costs',
            'injunctive-relief'         => 'Injunctive Relief',
            'cease-and-desist'          => 'Cease and Desist Order',
            'expungement-of-personnel-record' => 'Expungement of Personnel Record',
            'bounty-qui-tam-award'      => 'Bounty / Qui Tam Award',
            'whistleblower-fund-award'  => 'Whistleblower Fund Award',
            'non-monetary-relief'       => 'Non-Monetary Relief',
            'neutral-reference'         => 'Neutral / Corrected Reference',
            'wage-differential'         => 'Wage Differential',
            'liquidated-damages'        => 'Liquidated Damages',
            'consequential-damages'     => 'Consequential / Special Damages',
            'declaratory-relief'        => 'Declaratory Relief',
            'tax-gross-up'              => 'Tax Gross-Up',
            'non-economic-cap-separate'           => 'Non-Economic Damages Capped Separately',
            'punitive-damages-capped-separately'  => 'Punitive Damages Capped Separately',
            'has-limits'                => 'Has Limits/Caps/Standards',
            'has-details'               => 'Has Details',
        ]
    ],

// —— 4. Protected Classes ———————————————————————————————————————————————————
/**
 * Assigns ws_protected_class with its hierarchical term structure.
 *
 * @todo - should be sync'd with ws_excluded_class table by hook, with
 *         never_excluded and never_protected gates when adding terms to
 *         the respective table.
 * @todo legal_prompt, assist_prompt — set instruction strings.
 * 
 */
    'ws_protected_class'  => [
        'cpts'                => ['jx-statute', 'jx-common-law', 'jx-citation', 'jx-construction', 'ws-assist-org'],
        'plural'              => 'Protected Classes',
        'singular'            => 'Protected Class',
        'hierarchical'        => true,
        'seed_version'        => '1.0.0',
        'record'              => ['legal', 'assist'],
        'legal_prompt'        => '',
        'assist_prompt'       => '',
        'terms'               => [
            'public-sector'                => ['Public Sector', 1],
                'federal-employee'               => 'Federal Employee',
                'state-employee'                 => 'State Agency Employee',
                'local-gov-staff'                => 'Local / Municipal Employee',
                'k12-education-staff'            => 'K-12 / Higher Ed Staff',
                'military-personnel'             => 'Military Personnel',
            'private-sector'               => ['Private Sector', 1],
                'corporate-staff'                => 'Corporate / Private Employee',
                'contractor-gig'                 => 'Independent Contractor / Gig',
                'non-profit-staff'               => 'Non-Profit Employee',
                'agricultural-worker'            => 'Agricultural Worker',
                'domestic-work-employee'         => 'Domestic Work Employee',
            'healthcare-staff'             => ['Healthcare & Medical', 1],
                'clinical-staff'                 => 'Clinical (Nurse / Physician)',
                'medical-student'                => 'Medical Student / Intern / Resident',
            'special-status'               => ['Special Status', 1],
                'job-applicant'                  => 'Job Applicant',
                'former-employee'                => 'Former Employee',
                'perceived-whistleblower'        => 'Perceived Whistleblower',
                'intern-volunteer'               => 'Intern / Volunteer',
                'qui-tam-relator'                => 'Qui Tam Relator',
                'victim-domestic-violence-sexual-assault' => 'Victim of Domestic/Sexual Violence',
            'associates-of-whistleblower'  => ['Associates of Whistleblower', 1],
                'associates-spouse'              => 'Spouse of Whistleblower',
                'associates-immediate-family'    => 'Immediate Family of Whistleblower',
                'associates-household-family'    => 'Household Family of Whistleblower',
                'associates-close'               => 'Close Associates of Whistleblower',
            'all-sectors'                  => ['All Sectors', 1],
                'all-employees-only'             => 'All Employees Only',
            'has-parent'                   => ['Has Sentinels', 1],
                'has-details'                    => 'Has Details',
        ]
    ],

// —— 5. Excluded Classes ————————————————————————————————————————————————————
/**
 * Assigns ws_excluded_class with its hierarchical term structure.
 *
 * Duplicate table of ws_protected_class with all-sectors (all-employees-only) removed.
 *
 * @todo - should be sync'd with ws_protected_class table by hook, with
 *         never_excluded and never_protected gates when adding terms to
 *         the respective table.
 *
 */
    'ws_excluded_class'  => [
        'cpts'               => ['jx-statute', 'jx-common-law', 'jx-citation', 'jx-construction'],
        'plural'             => 'Excluded Classes',
        'singular'           => 'Excluded Class',
        'hierarchical'       => true,
        'seed_version'       => '1.0.0',
        'record'             => [],
        'terms'              => [
            'public-sector'                => ['Public Sector', 1],
                'federal-employee'               => 'Federal Employee',
                'state-employee'                 => 'State Agency Employee',
                'local-gov-staff'                => 'Local / Municipal Employee',
                'k12-education-staff'            => 'K-12 / Higher Ed Staff',
                'military-personnel'             => 'Military Personnel',
            'private-sector'               => ['Private Sector', 1],
                'corporate-staff'                => 'Corporate / Private Employee',
                'contractor-gig'                 => 'Independent Contractor / Gig',
                'non-profit-staff'               => 'Non-Profit Employee',
                'agricultural-worker'            => 'Agricultural Worker',
            'healthcare-staff'             => ['Healthcare & Medical', 1],
                'clinical-staff'                 => 'Clinical (Nurse / Physician)',
                'medical-student'                => 'Medical Student / Intern / Resident',
            'special-status'               => ['Special Status', 1],
                'job-applicant'                  => 'Job Applicant',
                'former-employee'                => 'Former Employee',
                'perceived-whistleblower'        => 'Perceived Whistleblower',
                'intern-volunteer'               => 'Intern / Volunteer',
                'qui-tam-relator'                => 'Qui Tam Relator',
            'associates-of-whistleblower'  => ['Associates of Whistleblower', 1],
                'associates-spouse'              => 'Spouse of Whistleblower',
                'associates-immediate-family'    => 'Immediate Family of Whistleblower',
                'associates-household-family'    => 'Household Family of Whistleblower',
                'associates-close'               => 'Close Associates of Whistleblower',
            'has-parent'                   => ['Has Sentinels', 1],
                'has-details'                    => 'Has Details',
        ]
    ],

// —— 6. Adverse Actions —————————————————————————————————————————————————————
/**
 * Assigns ws_adverse_action with its flat term structure.
 *
 * @todo legal_prompt — set instruction string.
 * 
 */
    'ws_adverse_action'  => [
        'cpts'               => ['jx-statute', 'jx-common-law', 'jx-citation', 'jx-construction'],
        'plural'             => 'Adverse Action Types',
        'singular'           => 'Adverse Action Type',
        'menu_name'          => 'Adverse Actions',
        'seed_version'       => '1.0.0',
        'record'             => ['legal'],
        'legal_prompt'       => '',
        'terms'              => [
            'termination'                  => 'Termination',
            'constructive-discharge'       => 'Constructive Discharge',
            'demotion'                     => 'Demotion',
            'suspension'                   => 'Suspension',
            'disciplinary-action'          => 'Disciplinary Action',
            'transfer'                     => 'Transfer',
            'schedule-change'              => 'Schedule Change',
            'benefit-denial'               => 'Benefit Denial',
            'benefit-clawback'             => 'Benefit Clawback / Retroactive Revocation',
            'pay-reduction'                => 'Pay / Benefits Reduction',
            'harassment'                   => 'Harassment',
            'hostile-work-environment'     => 'Hostile Work Environment',
            'workplace-isolation'          => 'Workplace Isolation / Ostracism',
            'post-employment-retaliation'  => 'Post-Employment Retaliation',
            'blacklisting'                 => 'Blacklisting',
            'negative-reference'           => 'Negative Reference',
            'security-clearance-action'    => 'Security Clearance Action',
            'contract-non-renewal'         => 'Contract Non-Renewal',
            'professional-license-action'  => 'Professional License Action',
            'privilege-revocation'         => 'Privilege / Access Revocation',
            'immigration-threat'           => 'Immigration-Related Threat',
            'anticipatory-retaliation'     => 'Anticipatory Retaliation',
            'threatened-retaliation'       => 'Threatened Retaliation',
            'retaliatory-investigation'    => 'Retaliatory Investigation / Audit',
            'retaliatory-litigation'       => 'Retaliatory Litigation (SLAPP)',
            'retaliatory-discovery'        => 'Retaliatory Discovery (Litigation Harassment)',
            'has-details'                  => 'Has Details',
        ]
    ],

// —— 7. Languages ———————————————————————————————————————————————————————————
/**
 * Assigns ws_language with its flat term structure.
 *
 * 'additional' is a functional flag — auto-assigned when ws_agency_additional_languages
 * or ws_aorg_additional_languages text fields contain a value.
 *
 * @todo assist_prompt — set instruction string.
 * 
 */
    'ws_language'  => [
        'cpts'           => ['ws-agency', 'ws-assist-org'],
        'plural'         => 'Languages',
        'singular'       => 'Language',
        'seed_version'   => '1.0.0',
        'record'         => ['assist'],
        'assist_prompt'  => '',
        'terms'          => [
            'english'        => 'English',
            'spanish'        => 'Spanish',
            'mandarin'       => 'Mandarin',
            'cantonese'      => 'Cantonese',
            'french'         => 'French',
            'portuguese'     => 'Portuguese',
            'vietnamese'     => 'Vietnamese',
            'tagalog'        => 'Tagalog',
            'korean'         => 'Korean',
            'arabic'         => 'Arabic',
            'hindi'          => 'Hindi',
            'russian'        => 'Russian',
            'haitian-creole' => 'Haitian Creole',
            'polish'         => 'Polish',
            'japanese'       => 'Japanese',
            'additional'     => 'Additional',
        ]
    ],

// —— 8. Case Stages —————————————————————————————————————————————————————————
/**
 * Assigns ws_case_stage with its flat term structure.
 *
 * @todo assist_prompt — set instruction string.
 * 
 */
    'ws_case_stage'  => [
        'cpts'           => ['ws-assist-org'],
        'plural'         => 'Case Stages',
        'singular'       => 'Case Stage',
        'seed_version'   => '1.0.0',
        'record'         => ['assist'],
        'assist_prompt'  => '',
        'terms'          => [
            'pre-report'          => 'Pre-Report',
            'post-report'         => 'Post-Report',
            'retaliation-active'  => 'Retaliation Active',
            'litigation'          => 'Litigation',
            'has-details'         => 'Has Details',
        ]
    ],

// —— 9. Jurisdictions ———————————————————————————————————————————————————————
/**
 * Assigns ws_jurisdiction with its flat term structure.
 *
 * Seed terms are derived from the canonical jurisdiction matrix:
 * matrix key -> taxonomy slug, title -> taxonomy label.
 *
 * @todo legal_prompt, assist_prompt — set instruction strings.
 * 
 */
    'ws_jurisdiction'  => [
        'cpts'             => ['jurisdiction', 'jx-statute', 'jx-summary', 'jx-citation', 'jx-construction', 'jx-common-law', 'ws-agency', 'ag-procedure', 'ws-assist-org'],
        'plural'           => 'Jurisdictions',
        'singular'         => 'Jurisdiction',
        'seed_version'     => '1.0.0',
        'record'           => ['legal', 'assist'],
        'legal_prompt'     => '',
        'assist_prompt'    => '',
        'order'            => 1,
        'terms'            => ws_jurisdiction_matrix_taxonomy_terms(),
    ],

// —— 10. Disclosure Targets —————————————————————————————————————————————————
/**
 * Assigns ws_disclosure_target with its hierarchical term structure.
 *
 * Describes who received the disclosure for protection to apply.
 *
 * @todo legal_prompt, assist_prompt — set instruction strings.
 * 
 */
    'ws_disclosure_target'  => [
        'cpts'                  => ['jx-statute', 'jx-common-law', 'jx-citation', 'jx-construction', 'ws-assist-org'],
        'plural'                => 'Disclosure Targets',
        'singular'              => 'Disclosure Target',
        'hierarchical'          => true,
        'seed_version'          => '1.0.0',
        'record'                => ['legal', 'assist'],
        'legal_prompt'          => '',
        'assist_prompt'         => '',
        'terms'                 => [
            'internal'              => ['Internal', 1],
                'internal-supervisor'      => 'Supervisor / Manager',
                'internal-hr'              => 'Human Resources',
                'internal-compliance'      => 'Compliance / Ethics Hotline',
                'internal-legal'           => 'In-House Legal Counsel',
                'internal-management'      => 'Management (General)',
                'internal-oversight-body'  => 'Internal Oversight Office',
            'external-agency'       => ['External: Government Agency', 1],
                'agency-federal'           => 'Federal Agency',
                'agency-state'             => 'State Agency',
                'agency-local'             => 'Local / Municipal Agency',
                'ig-federal'               => 'Federal Inspector General',
                'ig-state'                 => 'State Inspector General',
                'law-enforcement-fed'      => 'Federal Law Enforcement',
                'law-enforcement-state'    => 'State Law Enforcement',
                'external-oversight-body'  => 'External Oversight Office',
            'legislative'           => ['Legislative Body', 1],
                'legislative-federal'      => 'U.S. Congress',
                'legislative-state'        => 'State Legislature',
            'judicial'              => ['Judicial / Legal', 1],
                'court-filing'             => 'Court Filing',
                'attorney-counsel'         => 'Personal Attorney / Counsel',
            'public'                => ['Public Disclosure', 1],
                'public-media'             => 'Media / Press',
                'public-nonprofit'         => 'Non-Profit / Advocacy Organization',
                'public-social-media'      => 'Social Media',
            'has-parent'            => ['Has Sentinels', 1],
                'has-details'              => 'Has Details',
        ]
    ],

// —— 11. Employer Defenses ——————————————————————————————————————————————————
/**
 * Assigns ws_employer_defense with its flat term structure.
 *
 * @todo legal_prompt — set instruction string.
 * 
 */
    'ws_employer_defense'  => [
        'cpts'                  => ['jx-statute', 'jx-common-law', 'jx-citation', 'jx-construction'],
        'plural'                => 'Employer Defense Standards',
        'singular'              => 'Employer Defense Standard',
        'menu_name'             => 'Employer Defenses',
        'seed_version'          => '1.0.0',
        'record'                => ['legal'],
        'legal_prompt'          => '',
        'terms'                 => [
            'same-decision-defense'              => 'Same-Decision Defense',
            'legitimate-non-retaliatory-reason'  => 'Legitimate Non-Retaliatory Reason',
            'after-acquired-evidence'            => 'After-Acquired Evidence (Specific Non-Retaliatory)',
            'good-faith-compliance'              => 'Good-Faith Compliance',
            'independent-contractor-defense'     => 'Independent Contractor Defense',
            'statutory-exception-claim'          => 'Statutory Exception Claim',
            'no-protected-activity'              => 'Disclosure was not Protected',
            'no-jurisdiction'                    => 'Disclosure out of Scope (No Jurisdiction)',
            'has-details'                        => 'Has Details',
        ]
    ],

// —— 12. Organization Types —————————————————————————————————————————————————
/**
 * Assigns ws_aorg_type with its flat term structure.
 *
 * 'oversight-office' acts as the term for "Government Oversight Office"
 * (more legible to laypeople than 'ombudsman').
 *
 * @todo assist_prompt — set instruction string.
 * 
 */
    'ws_aorg_type'  => [
        'cpts'           => ['ws-assist-org'],
        'plural'         => 'Organization Types',
        'singular'       => 'Organization Type',
        'menu_name'      => 'Org Types',
        'seed_version'   => '1.0.0',
        'record'         => ['assist'],
        'assist_prompt'  => '',
        'terms'          => [
            'nonprofit'         => 'Nonprofit Organization',
            'legal-aid'         => 'Legal Aid Clinic',
            'law-firm'          => 'Law Firm',
            'bar-program'       => 'Bar Association Program',
            'advocacy'          => 'Advocacy Organization',
            'oversight-office'  => 'Government Oversight Office',
            'union'             => 'Labor Union',
            'mixed'             => 'Mixed Organization Type',
        ]
    ],

// —— 13. Employment Sectors —————————————————————————————————————————————————
/**
 * Assigns ws_employment_sector with its flat term structure.
 *
 * 'all-sectors-only' is used for organizations that serve all worker types.
 *
 * @todo legal_prompt, assist_prompt — set instruction strings.
 * 
 */
    'ws_employment_sector'  => [
        'cpts'                  => ['jx-statute', 'jx-common-law', 'jx-citation', 'jx-construction', 'ws-agency', 'ag-procedure', 'ws-assist-org'],
        'plural'                => 'Employment Sectors',
        'singular'              => 'Employment Sector',
        'seed_version'          => '1.0.0',
        'record'                => ['legal', 'assist'],
        'legal_prompt'          => '',
        'assist_prompt'         => '',
        'terms'                 => [
            'healthcare-worker'      => 'Healthcare Worker',
            'federal-employee'       => 'Federal Government Employee',
            'state-local-employee'   => 'State & Local Government Employee',
            'private-sector'         => 'Private Sector Employee',
            'public-sector'          => 'Public Sector Employee',
            'military-defense'       => 'Military & Defense Contractors',
            'government-contractor'  => 'Government Contractor',
            'nonprofit-ngo'          => 'Nonprofit & NGO Employee',
            'all-sectors-only'       => 'All Employment Sectors Only',
        ]
    ],

// —— 14. Cost Models ————————————————————————————————————————————————————————
/**
 * Assigns ws_aorg_cost_model with its flat term structure.
 *
 * @todo assist_prompt — set instruction string.
 * 
 */
    'ws_aorg_cost_model'  => [
        'cpts'                => ['ws-assist-org'],
        'plural'              => 'Cost Structures',
        'singular'            => 'Cost Structure',
        'menu_name'           => 'Cost Models',
        'seed_version'        => '1.0.0',
        'record'              => ['assist'],
        'assist_prompt'       => '',
        'terms'               => [
            'free'                => 'Free of Charge',
            'pro-bono'            => 'Pro Bono',
            'sliding-scale'       => 'Sliding Scale Fee',
            'contingency'         => 'Contingency Fee',
            'fee-for-service'     => 'Fee for Service',
            'unclear'             => 'Model Unclear',
        ]
    ],

// —— 15. Organization Services ——————————————————————————————————————————————
/**
 * Assigns ws_aorg_service with its flat term structure.
 *
 * 'additional' is the sentinel term for free-text overflow.
 *
 * @todo assist_prompt — set instruction string.
 * 
 */
    'ws_aorg_service'   => [
        'cpts'              => ['ws-assist-org'],
        'plural'            => 'Provided Services',
        'singular'          => 'Provided Service',
        'menu_name'         => 'Services',
        'seed_version'      => '1.0.0',
        'record'            => ['assist'],
        'assist_prompt'     => '',
        'terms'             => [
            'legal-rep'         => 'Full Legal Representation',
            'consultation'      => 'Legal Consultation / Advice',
            'referral'          => 'Intake & Referral',
            'doc-review'        => 'Document Review',
            'hotline'           => 'Whistleblower Hotline',
            'retaliation'       => 'Retaliation Defense',
            'financial'         => 'Financial Assistance',
            'advocacy'          => 'Policy Advocacy',
            'media'             => 'Media & Communications Support',
            'mental-health'     => 'Mental Health Support',
            'peer-support'      => 'Peer Support',
            'secure-drop'       => 'SecureDrop Intake',
            'additional'        => 'Additional Services',
            'unclear'           => 'Services Unclear',
        ]
    ],

// —— 16. Procedure Types ————————————————————————————————————————————————————
/**
 * Assigns ws_procedure_type with its flat term structure.
 *
 * These three terms are stable — the set is not expected to grow.
 * - disclosure: procedure for reporting wrongdoing to the agency
 * - retaliation: procedure for filing a complaint after adverse action
 * - both: single procedure that covers both disclosure and retaliation
 *
 */
    'ws_procedure_type'  => [
        'cpts'               => ['ag-procedure'],
        'plural'             => 'Procedure Types',
        'singular'           => 'Procedure Type',
        'menu_name'          => 'Procedures',
        'seed_version'       => '1.0.0',
        'record'             => [],
        'terms'              => [
            'disclosure'         => 'Disclosure',
            'retaliation'        => 'Retaliation',
            'both'               => 'Both',
        ]
    ],

// —— 17. Employee Burden Standards ——————————————————————————————————————————
/**
 * Assigns ws_employee_standard with its flat term structure.
 *
 * Covers evidentiary burden-of-proof standards only — the volume/quality of
 * evidence required. Causation standards (the logical link between disclosure
 * and adverse action) are in ws_causation_standard.
 *
 * @todo legal_prompt — set instruction string.
 * 
 */
    'ws_employee_standard'  => [
        'cpts'                  => ['jx-statute', 'jx-common-law', 'jx-citation', 'jx-construction'],
        'plural'                => 'Employee Burden Standards',
        'singular'              => 'Employee Burden Standard',
        'menu_name'             => 'Employee Standards',
        'seed_version'          => '1.0.0',
        'record'                => ['legal'],
        'legal_prompt'          => '',
        'terms'                 => [
            'contributing-factor'   => 'Contributing Factor',
            'motivating-factor'     => 'Motivating Factor',
            'substantial-factor'    => 'Substantial Factor',
            'but-for'               => 'But-For (Burden of Proof)',
            'preponderance'         => 'Preponderance of the Evidence',
            'clear-and-convincing'  => 'Clear and Convincing Evidence',
            'reasonable-belief'     => 'Reasonable Belief Standard',
            'has-details'           => 'Has Details',
        ]
    ],

// —— 18. Protection Scopes ——————————————————————————————————————————————————
/**
 * Assigns ws_protection_scope with its flat term structure.
 *
 * Editorial classification only; mirrors ws_process_type scope for display purposes.
 * - disclosure:  legal protection for reporting wrongdoing
 * - retaliation: legal protection from adverse actions after reporting
 * - both:        legal protection for both disclosure and retaliation
 * 
 *  No 'record' key is intentional, do not assign to research prompts.
 *  The protection scope of any legal record is an internal editorial call.
 *
 */
    'ws_protection_scope'  => [
        'cpts'                 => ['jx-statute', 'jx-common-law', 'jx-citation', 'jx-construction'],
        'plural'               => 'Protection Scope Types',
        'singular'             => 'Protection Scope Type',
        'menu_name'            => 'Protections',
        'seed_version'         => '1.0.0',
        'terms'                => [
            'disclosure'           => 'Disclosure',
            'retaliation'          => 'Retaliation',
            'both'                 => 'Both',
        ]
    ],

// —— 19. Protected Actions ——————————————————————————————————————————————————
/**
 * Assigns ws_protected_action with its hierarchical term structure.
 *
 * Two parent clauses per Title VII §704(a) and state analogs:
 * - Opposition Clause: opposing, resisting, or refusing; typically requires
 *   good-faith reasonable belief.
 * - Participation Clause: participating in proceedings or investigations;
 *   typically broader protection; good-faith requirement may not apply.
 *
 * @todo legal_prompt — set instruction string.
 * 
 */
    'ws_protected_action'  => [
        'cpts'                 => ['jx-statute', 'jx-common-law', 'jx-citation', 'jx-construction'],
        'plural'               => 'Protected Actions',
        'singular'             => 'Protected Action',
        'hierarchical'         => true,
        'seed_version'         => '1.0.0',
        'record'               => ['legal'],
        'legal_prompt'         => '',
        'terms'                => [
            'opposition-clause'     => ['Opposition Clause', 1],
                'opposing-practice'              => 'Opposing Practice',
                'internal-objection'             => 'Internal Objection',
                'refusal-to-participate'         => 'Refusal to Participate',
                'refusal-to-violate-law'         => 'Refusal to Violate Law',
            'participation-clause'  => ['Participation Clause', 1],
                'filing-complaint'               => 'Filing Complaint',
                'testifying'                     => 'Testifying',
                'assisting-whistleblower'        => 'Assisting Whistleblower',
                'cooperation-with-investigation' => 'Cooperation with Investigation',
                'participation-support'          => 'Participation Support',
            'attempted-parent'      => ['Attempted Parent', 1],
                'attempted-reporting'            => 'Attempted Reporting',
            'concerted-parent'      => ['Concerted Parent', 1],
                'concerted-activity'             => 'Concerted Activity',
        ]
    ],

// —— 20. Legal Recognitions —————————————————————————————————————————————————
/**
 * Assigns ws_legal_recognition with its flat term structure.
 *
 * Presence of a term signals the doctrine/rule is recognized in this jurisdiction.
 * Absence signals the doctrine is not recognized or the statute is silent.
 *
 * Used for bool-state values of Legal Recognitions where true when:
 * - Specified   — statute explicitly names or enumerates something
 * - Recognized  — judicial doctrine courts have affirmatively acknowledged
 * - Required    — mandatory obligation; non-compliance typically defeats the claim
 * - Applies     — statutory condition that operates by force of law when triggered
 * - Available   — mechanism or remedy that may be invoked but is not automatic
 * - Permitted   — right expressly allowed; cannot be waived or procedurally blocked
 * - Barred      — doctrine, action, or evidence explicitly excluded by law or rule
 * - Prohibited  — conduct expressly forbidden; violation triggers statutory liability
 * - Present     — clause or provision exists without implying judicial affirmation
 * - Sufficient  — condition independently meets the threshold for protection to attach
 * - Limited     — legal effect, scope, or enforceability is narrowed by statute or rule
 * - Enforceable — waiver, agreement, or procedural limitation may be given legal effect
 * - Unenforceable — waiver, agreement, or procedural limitation cannot be given legal effect
 * - Waived      — immunity, defense, or objection has been relinquished or abrogated
 *
 */
    'ws_legal_recognition'  => [
        'cpts'                  => ['jx-statute', 'jx-common-law', 'jx-citation', 'jx-construction'],
        'plural'                                    => 'Legal Recognitions',
        'singular'                                  => 'Legal Recognition',
        'seed_version'                              => '1.0.0',
        'record'                => [],
        'terms'                 => [
            // Identity
            'retroactive-date'                          => 'Retroactive Date Specified',
            // Classification
            // = NOTE = 'legal_recognitions' appears at top of Classification Tab
            'manager-rule-exclusion'                    => 'Manager Rule / Duty Speech Exclusion Applies',
            'public-concern-required'                   => 'Public Concern Required',
            'bad-faith-exclusion'                       => 'Bad Faith / Knowingly False Exclusion Applies',
            'anonymity-protection'                      => 'Anonymity / Confidentiality Protection Available',
            'malicious-reporting-sanctions'             => 'Malicious Reporting Sanctions Specified',
            'protected-action'                          => 'Protected Action Specified',
            'excluded-class'                            => 'Excluded Class Specified',
            'garcetti-exception'                        => 'Garcetti / Official-Duties Exclusion Applies',
            'disclosure-channel-defined'                => 'Disclosure Channel Defined',
            // Statute of Limitations & Thresholds
            'statute-of-repose'                         => 'Statute of Repose Specified',
            'statutory-tolling'                         => 'Statutory Tolling Specified',
            'equitable-tolling'                         => 'Equitable Tolling Available',
            'cba-grievance-preemption'                  => 'CBA Grievance Preemption Applies',
            'amended-claim'                             => 'Amended Claim / Relation Back Available',
            'exhaustion-required'                       => 'Exhaustion Required',
            'pre-filing-notice'                         => 'Pre-Filing Notice Required',
            'statutory-preclusion'                      => 'Statutory Preclusion Applies',
            'employer-threshold-specified'              => 'Employer Threshold Specified',
            'cure-period-specified'                     => 'Cure Period Specified',
            // Retaliation
            'evidence-preservation'                     => 'Evidence Preservation Required',
            'constructive-discharge-standard'           => 'Constructive Discharge Standard Specified',
            'remedy-election-required'                  => 'Election of Remedies Required',
            'cats-paw-liability'                        => 'Cat\'s Paw Liability Recognized',
            'third-party-retaliation'                   => 'Third-Party Retaliation Prohibited',
            'criminal-sanctions'                        => 'Criminal Sanctions Specified',
            // Processes and Remedies
            'process-pathway'                           => 'Process Pathway Specified',
            'private-right-of-action'                   => 'Private Right of Action Available',
            'jury-trial'                                => 'Jury Trial Available',
            'fee-shifting-standard'                     => 'Fee Shifting Standard Specified',
            'civil-review-standard'                     => 'Civil Review Standard Specified',
            'equitable-interest-award'                  => 'Equitable Interest Provision Available',
            'mitigation-required'                       => 'Mitigation Required',
            'mitigation-exception'                      => 'Mitigation Exception Available',
            'preliminary-reinstatement'                 => 'Preliminary / Interim Reinstatement Available',
            // Burden of Proof
            'burden-shifting-framework'                 => 'Burden Shifting Framework Specified',
            'same-decision-defense-standard'            => 'Same-Decision Defense Standard Specified',
            'causation-dual-standard'                   => 'Causation Dual Standard Applies',
            'employer-knowledge'                        => 'Employer Knowledge Element Required',
            'temporal-presumption-recognized'           => 'Temporal Presumption Recognized',
            'temporal-proximity-sufficient'             => 'Temporal Proximity Sufficient',
            // Rewards
            'reward-available'                          => 'Reward Available',
            'qui-tam-action'                            => 'Qui Tam Action Available',
            // Waiver & Scope
            'all-waivers-unenforceable'                 => 'All Waivers Unenforceable',
            'civil-action-waiver'                       => 'Civil Action Waiver Enforceable',
            'contractual-waiver'                        => 'Contractual Waiver Enforceable',
            'collateral-claims-waiver'                  => 'Collateral Claims Waiver Enforceable',
            'class-action-waiver'                       => 'Class Action Waiver Enforceable',
            'individual-liability'                      => 'Individual Liability Available',
            'sovereign-immunity-status'                 => 'Sovereign Immunity Status Specified',
            'nda-limitations'                           => 'NDA / Non-Disparagement Clauses Limited',
            'anti-gag-provision-present'                => 'Anti-Gag Provision Present',
            'no-retaliatory-evidence'                   => 'Retaliatory Evidence Barred',
            'stay-of-disciplinary-action'               => 'Stay of Disciplinary Action Available',
            'anti-slapp-protection'                     => 'Anti-SLAPP Protection Available',
            'discovery-protection'                      => 'Discovery Protection / Anti-Harassment Available',
            'confidential-settlement-restriction'       => 'Confidential Settlement Terms Limited',
            'successor-liability'                       => 'Successor Employer Liability Recognized',
            'extraterritorial-coverage'                 => 'Extraterritorial Coverage Applies',
            // Without Context
            'statutory-nexus-diverges-from-common-law'  => 'Statutory Nexus Override Applies',
            'catch-all-protection'                      => 'Catch-All Protection Clause Present',
            'internal-only-disclosure-sufficient'       => 'Internal-Only Disclosure Sufficient',
            'trade-secret-immunity-available'           => 'Trade Secret Immunity Available',
            'continuing-violation-doctrine'             => 'Continuing Violation Doctrine Recognized',
            'prospective-whistleblower-protection'      => 'Prospective Whistleblower Protection Recognized',
            'blanket-sovereign-immunity-waived'         => 'Blanket Sovereign Immunity Waived',
            'class-action-permitted'                    => 'Class / Collective Action Permitted',
            'official-duties-carveout'                  => 'Official Duties Carveout Recognized',
        ]
    ],

// —— 21. Causation Standards ————————————————————————————————————————————————
/**
 * Assigns ws_causation_standard with its flat term structure.
 *
 * Covers the causal link required between the disclosure and the adverse action.
 * Distinct from ws_employee_standard which covers evidentiary burden-of-proof
 * standards (the volume/quality of evidence required).
 * 
 * @todo legal_prompt — set instruction string. 
 *
 */
    'ws_causation_standard'  => [
        'cpts'                   => ['jx-statute', 'jx-common-law', 'jx-citation', 'jx-construction'],
        'plural'                 => 'Causation Standards',
        'singular'               => 'Causation Standard',
        'seed_version'           => '1.0.0',
        'record'                 => ['legal'],
        'legal_prompt'           => '',
        'terms'                  => [
            'causation-but-for'                        => 'But-For Causation Standard',
            'causation-any-consideration'              => 'Any Consideration Causation Standard',
            'causation-contributing-factor'            => 'Contributing Factor Causation Standard',
            'causation-motivating-factor'              => 'Motivating Factor Causation Standard',
            'causation-substantial-factor'             => 'Substantial Factor Causation Standard',
            'causation-proximate-cause'                => 'Proximate Cause Standard',
            'contributing-factor-but-for-backstop'     => 'Contributing Factor (But-For Backstop)',
            'has-details'                              => 'Has Details',
        ]
    ],
];

// ════════════════════════════════════════════════════════════════════════════
// REGISTER TAXONOMIES
// ════════════════════════════════════════════════════════════════════════════

/**
 * Registers all taxonomies defined in _ws_taxonomy_registry.
 *
 * Hooked to 'init'.
 * @return void
 *
 */
function ws_register_all_taxonomies()
{
    global $_ws_taxonomy_registry;

    $default_caps = ws_get_taxonomy_caps();

    foreach ($_ws_taxonomy_registry as $slug => $config) {
        if (taxonomy_exists($slug)) {
            continue;
        }

        $plural       = $config['plural'];
        $singular     = $config['singular'];
        $name         = $config['name'] ?? $plural;
        $menu_name    = $config['menu_name'] ?? $plural;
        $hierarchical = $config['hierarchical'] ?? false;

        $labels = [
            'name'              => $name,
            'singular_name'     => $singular,
            'search_items'      => 'Search ' . $plural,
            'all_items'         => 'All ' . $plural,
            'edit_item'         => 'Edit ' . $singular,
            'update_item'       => 'Update ' . $singular,
            'add_new_item'      => 'Add New ' . $singular,
            'new_item_name'     => 'New ' . $singular . ' Name',
            'menu_name'         => $menu_name,
        ];

        if ($hierarchical) {
            $labels['parent_item']       = 'Parent ' . $singular;
            $labels['parent_item_colon'] = 'Parent ' . $singular . ':';
        }

        $args = [
            'label'               => $name,
            'labels'              => $labels,
            'public'              => $config['public'] ?? false,
            'publicly_queryable'  => $config['publicly_queryable'] ?? false,
            'hierarchical'        => $hierarchical,
            'show_ui'             => $config['show_ui'] ?? true,
            'show_in_rest'        => $config['show_in_rest'] ?? true,
            'show_admin_column'   => $config['show_admin_column'] ?? true,
            'rewrite'             => $config['rewrite'] ?? false,
            'query_var'           => $config['query_var'] ?? false,
            'capabilities'        => $config['capabilities'] ?? $default_caps,
        ];

        register_taxonomy($slug, $config['cpts'], $args);
    }
}
add_action('init', 'ws_register_all_taxonomies');

/**
 * Get/Set taxonomy capabilities.
 *
 * @return array Array of capabilities for taxonomy operations.
 *
 */
function ws_get_taxonomy_caps()
{
    return [
        'manage_terms' => 'manage_options',
        'edit_terms'   => 'manage_options',
        'delete_terms' => 'manage_options',
        'assign_terms' => 'edit_posts',
    ];
}

// ════════════════════════════════════════════════════════════════════════════
// REGISTRY QUERY HELPERS
// ════════════════════════════════════════════════════════════════════════════

/**
 * Returns all taxonomy registry entries that serve the given record type.
 *
 * Scans the global $_ws_taxonomy_registry and collects every taxonomy whose
 * 'record' array contains $record_type. Each result carries the taxonomy slug,
 * human-readable labels, and the pre-configured prompt string for that type —
 * ready for use by admin LLM prompt-generation tooling.
 *
 * Usage:
 *   $legal_tables  = ws_get_taxonomies_for_record( 'legal' );
 *   $assist_tables = ws_get_taxonomies_for_record( 'assist' );
 *
 * Each entry in the returned array:
 *   [
 *     'taxonomy' => 'ws_employment_sector',       // taxonomy slug
 *     'plural'   => 'Employment Sectors',         // human-readable plural
 *     'singular' => 'Employment Sector',          // human-readable singular
 *     'prompt'   => 'Sectors protected by...',   // prompt string (may be '')
 *   ]
 *
 * @param  string  $record_type  Either 'legal' or 'assist'.
 * @return array[] Indexed array of taxonomy descriptor arrays.
 * 
 */
function ws_get_taxonomies_for_record(string $record_type): array
{
    global $_ws_taxonomy_registry;

    if (!in_array($record_type, ['legal', 'assist'], true)) {
        wp_die("This is a FuQ'n Error! ws_get_taxonomies_for_record() called with invalid type: '{$record_type}'. Expected 'legal' or 'assist'.");
    }

    $prompt_key = $record_type . '_prompt'; // resolves to 'legal_prompt' or 'assist_prompt'
    $result     = [];

    foreach ($_ws_taxonomy_registry as $slug => $config) {

        if (! in_array($record_type, $config['record'] ?? [], true)) {
            continue;
        }

        $result[] = [
            'taxonomy' => $slug,
            'plural'   => $config['plural'],
            'singular' => $config['singular'],
            'prompt'   => $config[ $prompt_key ] ?? '',
        ];
    }

    return $result;
}

// ════════════════════════════════════════════════════════════════════════════
// SEEDING
// ════════════════════════════════════════════════════════════════════════════

/**
 * Iterates through the taxonomy registry to seed terms conditionally.
 *
 * - Generates a version gate key (e.g., 'ws_seeded_protected_disclosure').
 * - Checks against the 'seed_version' defined in the registry.
 * - If the version has bumped (or is unseeded), it delegates to ws_seed_taxonomy().
 * - Collects any WP_Error messages and outputs an admin_notice reflecting
 *   either a clean success or directing the admin to the PHP error log.
 *
 * Hooked to 'admin_init'.
 * @return void
 *
 */

function ws_seed_all_taxonomies()
{
    global $_ws_taxonomy_registry;

    $ran_seeder = false;
    $all_errors = [];

    foreach ($_ws_taxonomy_registry as $slug => $config) {
        if (! empty($config['terms'])) {
            // Strict configuration check: Taxonomies must be snake_case
            if (strpos($slug, '-') !== false) {
                trigger_error("WS Core: Taxonomy slug '{$slug}' contains a hyphen — must be snake_case.", E_USER_ERROR);
                continue;
            }

            // Auto-generate gate key: ws_protected_disclosure -> ws_seeded_protected_disclosure
            $gate = 'ws_seeded_' . substr($slug, 3);
            if (empty($config['seed_version'])) {
                wp_die("This is a FuQ'n Error! Taxonomy '{$slug}' is missing 'seed_version' in the registry.");
            }
            $version = $config['seed_version'];

            if (get_option($gate) !== $version) {
                $ran_seeder = true;
                $errors = ws_seed_taxonomy($slug, $config['terms'], $gate, $version, ! empty($config['order']));
                if (! empty($errors)) {
                    array_push($all_errors, ...$errors);
                }
            }
        }
    }

    if ($ran_seeder) {
        if (empty($all_errors)) {
            add_action('admin_notices', function () {
                echo '<div class="notice notice-success is-dismissible"><p>WS Core: Taxonomy tables were updated cleanly. No errors reported.</p></div>';
            });
        } else {
            foreach ($all_errors as $err) {
                error_log('WS Core Error: ' . $err);
            }
            add_action('admin_notices', function () {
                echo '<div class="notice notice-error is-dismissible"><p>WS Core: Taxonomy tables were updated, but errors were reported! See PHP error_log.</p></div>';
            });
        }
    }
}
add_action('admin_init', 'ws_seed_all_taxonomies');

/**
 * Dynamically seeds terms for a single taxonomy using stateful hierarchical parsing.
 *
 * Array $terms format:
 * - 'slug' => 'Term Label' (Child of the current active parent, or top-level if no active parent)
 * - 'slug' => ['Term Label', 1] (Sets this term as top-level, and makes it the active parent for subsequent terms)
 *
 * @param string $taxonomy     The taxonomy slug.
 * @param array  $terms        The terms to seed.
 * @param string $option_label The DB option key for gating.
 * @param string $version      The version string for gating.
 * @param bool   $ordered      When true, sets display_order term meta (1-based, incremental).
 * @return array               Array of error messages, if any.
 *
 */

function ws_seed_taxonomy($taxonomy, $terms, $option_label, $version = '1.0.0', $ordered = false)
{
    if (empty($taxonomy) || !is_string($taxonomy)) {
        wp_die("This is a FuQ'n Error! ws_seed_taxonomy() called with an invalid \$taxonomy argument.");
    }
    if (!is_array($terms) || empty($terms)) {
        wp_die("This is a FuQ'n Error! ws_seed_taxonomy() called with invalid \$terms for '{$taxonomy}'.");
    }
    if (empty($option_label) || !is_string($option_label)) {
        wp_die("This is a FuQ'n Error! ws_seed_taxonomy() called with an invalid \$option_label for '{$taxonomy}'.");
    }

    $errors = [];
    $current_parent_id = 0; // Stateful tracker for the active parent (resets per taxonomy loop)
    $display_order     = 0;

    foreach ($terms as $slug => $data) {
        $is_parent = false;
        $name = $data;

        if (is_array($data)) {
            $name = $data[0];
            if (array_key_exists(1, $data)) {
                if ($data[1] === 1) {
                    $is_parent = true;
                    $current_parent_id = 0; // Ensure the parent itself is inserted at the top-level root
                } else {
                    $invalid_val = var_export($data[1], true);
                    wp_die("Taxonomy seeding error in '{$taxonomy}': Invalid flag {$invalid_val} for term '{$slug}'. Only integer 1 is permitted.");
                }
            }
        }

        // Determine the parent ID for this specific term
        $target_parent_id = (! $is_parent && $current_parent_id > 0) ? $current_parent_id : 0;

        $args     = [ 'slug' => $slug, 'parent' => $target_parent_id ];
        $term_obj = get_term_by('slug', $slug, $taxonomy); // single DB call replaces term_exists() + get_term()
        $term_id  = 0;

        if (! $term_obj) {
            $result = wp_insert_term($name, $taxonomy, $args);
            if (is_wp_error($result)) {
                $errors[] = "Failed to insert term '{$slug}' in '{$taxonomy}': " . $result->get_error_message();
            } else {
                $term_id = $result['term_id'];
            }
        } else {
            $term_id = $term_obj->term_id;

            // Skip DB write and cache invalidation if nothing actually changed
            if ($term_obj->name !== $name || (int) $term_obj->parent !== $target_parent_id) {
                $update_result = wp_update_term($term_id, $taxonomy, [ 'name' => $name, 'parent' => $target_parent_id ]);
                if (is_wp_error($update_result)) {
                    $errors[] = "Failed to update term '{$slug}' in '{$taxonomy}': " . $update_result->get_error_message();
                }
            }
        }

        // Write display_order term meta when the registry entry requests ordered output
        if ($ordered && $term_id > 0) {
            update_term_meta($term_id, 'display_order', ++$display_order);
        }

        // If this term was flagged as a parent, set it as the active parent for subsequent terms
        if ($is_parent && $term_id > 0) {
            $current_parent_id = $term_id;
        }
    }

    update_option($option_label, $version);

    return $errors;
}
