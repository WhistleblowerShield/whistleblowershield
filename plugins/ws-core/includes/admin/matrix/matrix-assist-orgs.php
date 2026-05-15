<?php
/**
 * matrix-assist-orgs.php — Seeds nationwide and federal-scope whistleblower support organizations.
 *
 * @package    WhistleblowerShield
 * @since      3.0.0
 * @version    3.20.0
 * @author     Whistleblower Shield
 * @link       https://whistleblowershield.org
 * @copyright  Copyright (c) Whistleblower Shield
 * 
 */

defined( 'ABSPATH' ) || exit;


// ════════════════════════════════════════════════════════════════════════════
// Assist-Org Data
// ════════════════════════════════════════════════════════════════════════════

global $_ws_assist_org_matrix;
$_ws_assist_org_matrix = [
    [
        'common_name' => 'GAP',
        'official_name' => 'Government Accountability Project',
        'official_homepage_url' => 'https://whistleblower.org',
        'intake_url' => 'https://crm.whistleblower.org/form/online-intake-application',
        'contact_url' => '',
        'phones' => [
            [ 'type' => 'headquarters', 'number' => '(202) 457-0034' ],
        ],
        'emails' => [
            [ 'type' => 'general', 'address' => 'info@whistleblower.org' ],
        ],
        'secure_channel_status' => 'none-found',
        'secure_contact_tools' => [],
        'mailing_address' => 'Government Accountability Project, 1612 K St NW, Suite 808, Washington, DC 20006-2802',
        'income_screening' => 'not-required',
        'organization_model' => 'nonprofit',
        'cost_models' => [ 'pro-bono' ],
        'languages' => [ 'english' ],
        'is_nationwide' => 1,
        'anonymous_pre_consult_status' => 'yes',
        'has_attorneys' => 'yes',
        'whistleblower_scope' => 3,
        'whistleblower_fit' => 'primary-focus',
        'service_depth' => 'direct-representation',
        'intake_commitment_class' => 'screening-form',
        'eligibility_status' => 'screening-required',
        'services' => [
            'legal-rep',
            'consultation',
            'advocacy',
            'media',
        ],
        'employment_sectors' => [
            'federal-employee',
            'private-sector',
            'nonprofit-ngo',
        ],
        'protected_classes' => [ 'federal-employee', 'corporate-staff', 'non-profit-staff' ],
        'protected_disclosures' => [
            'public-corruption-ethics',
            'procurement-spending-fraud',
            'environmental-protection',
            'occupational-health-safety',
            'securities-commodities-fraud',
        ],
        'disclosure_targets' => [
            'internal-compliance',
            'agency-federal',
            'agency-state',
            'legislative-federal',
            'public-media',
        ],
        'case_stages' => [
            'pre-report',
            'post-report',
            'retaliation-active',
            'litigation',
        ],
    ],
    [
        'common_name' => 'NWC',
        'official_name' => 'National Whistleblower Center',
        'official_homepage_url' => 'https://www.whistleblowers.org',
        'intake_url' => 'https://www.report-fraud-now.info/',
        'contact_url' => 'https://www.whistleblowers.org/contact-us/',
        'phones' => [
            [ 'type' => 'headquarters', 'number' => '(202) 342-1903' ],
        ],
        'emails' => [
            [ 'type' => 'general', 'address' => 'contact@whistleblowers.org' ],
        ],
        'secure_channel_status' => 'none-found',
        'secure_contact_tools' => [],
        'mailing_address' => 'National Whistleblower Center, 1800 M Street NW #33888, Washington, DC 20033',
        'income_screening' => 'not-required',
        'organization_model' => 'nonprofit',
        'cost_models' => [ 'unclear' ],
        'languages' => [ 'english' ],
        'is_nationwide' => 1,
        'anonymous_pre_consult_status' => 'yes',
        'has_attorneys' => 'yes',
        'whistleblower_scope' => 3,
        'whistleblower_fit' => 'primary-focus',
        'service_depth' => 'referral-only',
        'intake_commitment_class' => 'tip-submission-only',
        'eligibility_status' => 'open-to-public',
        'services' => [
            'referral',
            'advocacy',
            'consultation',
        ],
        'employment_sectors' => [
            'all-sectors-only',
        ],
        'protected_classes' => [ 'all-employees-only' ],
        'protected_disclosures' => [
            'securities-commodities-fraud',
            'tax-evasion-fraud',
            'public-corruption-ethics',
            'procurement-spending-fraud',
            'healthcare-medicare-fraud',
            'environmental-protection',
            'banking-aml-compliance',
        ],
        'disclosure_targets' => [
            'agency-federal',
            'agency-state',
            'internal-compliance',
        ],
        'case_stages' => [
            'pre-report',
            'post-report',
            'retaliation-active',
            'litigation',
        ],
    ],
    [
        'common_name' => '',
        'official_name' => 'Whistleblower Aid',
        'official_homepage_url' => 'https://whistlebloweraid.org',
        'intake_url' => 'https://whistlebloweraid.org/become-a-whistleblower/',
        'contact_url' => '',
        'phones' => [],
        'emails' => [],
        'secure_channel_status' => 'dedicated-secure-channel',
        'secure_contact_tools' => [ 'signal' ],
        'mailing_address' => 'Whistleblower Aid, 1250 Connecticut Ave NW, Suite 700, Washington, DC 20036',
        'income_screening' => 'not-required',
        'organization_model' => 'legal-aid',
        'cost_models' => [ 'pro-bono' ],
        'languages' => [ 'english' ],
        'is_nationwide' => 1,
        'anonymous_pre_consult_status' => 'yes',
        'has_attorneys' => 'yes',
        'whistleblower_scope' => 3,
        'whistleblower_fit' => 'primary-focus',
        'service_depth' => 'direct-representation',
        'intake_commitment_class' => 'personal-help-request',
        'eligibility_status' => 'screening-required',
        'services' => [
            'legal-rep',
            'consultation',
            'advocacy',
            'media',
            'retaliation',
        ],
        'employment_sectors' => [
            'federal-employee',
            'private-sector',
        ],
        'protected_classes' => [ 'federal-employee', 'corporate-staff' ],
        'protected_disclosures' => [
            'public-corruption-ethics',
            'classified-information',
            'intelligence-community',
            'environmental-protection',
            'cybersecurity-disclosure',
            'general-wrongdoing',
        ],
        'disclosure_targets' => [
            'internal-management',
            'internal-compliance',
            'agency-federal',
            'legislative-federal',
            'public-media',
        ],
        'case_stages' => [
            'pre-report',
            'post-report',
            'retaliation-active',
        ],
    ],
    [
        'common_name' => 'POGO',
        'official_name' => 'Project On Government Oversight',
        'official_homepage_url' => 'https://www.pogo.org',
        'intake_url' => '',
        'contact_url' => 'https://www.pogo.org/contact-us',
        'phones' => [
            [ 'type' => 'headquarters', 'number' => '(202) 347-1122' ],
        ],
        'emails' => [
            [ 'type' => 'general', 'address' => 'info@pogo.org' ],
        ],
        'secure_channel_status' => 'none-found',
        'secure_contact_tools' => [],
        'mailing_address' => 'Project On Government Oversight, 1100 13th Street NW, Suite 800, Washington, DC 20005',
        'income_screening' => 'not-required',
        'organization_model' => 'advocacy',
        'cost_models' => [ 'free' ],
        'languages' => [ 'english' ],
        'is_nationwide' => 1,
        'anonymous_pre_consult_status' => 'yes',
        'has_attorneys' => 'no',
        'whistleblower_scope' => 1,
        'whistleblower_fit' => 'adjacent-help',
        'service_depth' => 'information-only',
        'intake_commitment_class' => 'general-contact-only',
        'eligibility_status' => 'open-to-public',
        'services' => [
            'advocacy',
            'hotline',
            'media',
        ],
        'employment_sectors' => [
            'federal-employee',
            'military-defense',
        ],
        'protected_classes' => [ 'federal-employee', 'military-personnel' ],
        'protected_disclosures' => [
            'public-corruption-ethics',
            'procurement-spending-fraud',
            'military-defense-reporting',
            'general-wrongdoing',
        ],
        'disclosure_targets' => [
            'agency-federal',
            'legislative-federal',
            'public-media',
        ],
        'case_stages' => [
            'pre-report',
        ],
    ],
    [
        'common_name' => 'TAF',
        'official_name' => 'The Anti-Fraud Coalition',
        'official_homepage_url' => 'https://www.taf.org',
        'intake_url' => 'https://www.taf.org/attorneys/find-an-attorney/',
        'contact_url' => '',
        'phones' => [],
        'emails' => [],
        'secure_channel_status' => 'none-found',
        'secure_contact_tools' => [],
        'mailing_address' => 'The Anti-Fraud Coalition, 1220 19th St NW, Ste 501, Washington, DC 20036',
        'income_screening' => 'not-required',
        'organization_model' => 'advocacy',
        'cost_models' => [ 'unclear' ],
        'languages' => [ 'english' ],
        'is_nationwide' => 1,
        'anonymous_pre_consult_status' => 'no',
        'has_attorneys' => 'yes',
        'whistleblower_scope' => 2,
        'whistleblower_fit' => 'significant-program',
        'service_depth' => 'referral-only',
        'intake_commitment_class' => 'referral-request',
        'eligibility_status' => 'referral-only',
        'services' => [
            'referral',
            'advocacy',
        ],
        'employment_sectors' => [
            'all-sectors-only',
        ],
        'protected_classes' => [ 'all-employees-only' ],
        'protected_disclosures' => [
            'securities-commodities-fraud',
            'healthcare-medicare-fraud',
            'procurement-spending-fraud',
            'tax-evasion-fraud',
            'banking-aml-compliance',
        ],
        'disclosure_targets' => [
            'agency-federal',
            'agency-state',
        ],
        'case_stages' => [
            'pre-report',
            'post-report',
            'litigation',
        ],
    ],
    [
        'common_name' => 'WoA',
        'official_name' => 'Whistleblowers of America',
        'official_homepage_url' => 'https://www.whistleblowersofamerica.org',
        'intake_url' => '',
        'contact_url' => 'https://www.whistleblowersofamerica.org/learn-more/peer-support',
        'phones' => [
            [ 'type' => 'headquarters', 'number' => '202-643-1956' ],
        ],
        'emails' => [
            [ 'type' => 'general', 'address' => 'info@whistleblowersofamerica.org' ],
            [ 'type' => 'support', 'address' => 'peers@whistleblowersofamerica.org' ],
        ],
        'secure_channel_status' => 'none-found',
        'secure_contact_tools' => [],
        'mailing_address' => 'Whistleblowers of America, 11130 Lillian Highway, Pensacola, FL 32506',
        'income_screening' => 'not-required',
        'organization_model' => 'advocacy',
        'cost_models' => [ 'unclear' ],
        'languages' => [ 'english' ],
        'is_nationwide' => 1,
        'anonymous_pre_consult_status' => 'yes',
        'has_attorneys' => 'no',
        'whistleblower_scope' => 3,
        'whistleblower_fit' => 'primary-focus',
        'service_depth' => 'peer-support',
        'intake_commitment_class' => 'peer-support-request',
        'eligibility_status' => 'open-to-public',
        'services' => [
            'retaliation',
            'advocacy',
            'referral',
            'consultation',
        ],
        'employment_sectors' => [
            'all-sectors-only',
        ],
        'protected_classes' => [ 'all-employees-only' ],
        'protected_disclosures' => [
            'general-wrongdoing',
            'occupational-health-safety',
        ],
        'disclosure_targets' => [
            'internal-hr',
            'internal-management',
            'public-media',
        ],
        'case_stages' => [
            'pre-report',
            'post-report',
            'retaliation-active',
        ],
    ],
    [
        'common_name' => 'WIN',
        'official_name' => 'Whistleblowing International Network',
        'official_homepage_url' => 'https://whistleblowingnetwork.org/Home',
        'intake_url' => '',
        'contact_url' => 'https://whistleblowingnetwork.org/Contact-Us',
        'phones' => [],
        'emails' => [
            [ 'type' => 'general', 'address' => 'info@whistleblowingnetwork.org' ],
        ],
        'secure_channel_status' => 'none-found',
        'secure_contact_tools' => [],
        'mailing_address' => 'Whistleblowing International Network (WIN) c/o SCVO, Edward House, 199 Sauchiehall Street, Glasgow, G2 3EX',
        'income_screening' => 'not-required',
        'organization_model' => 'advocacy',
        'cost_models' => [ 'free' ],
        'languages' => [ 'english' ],
        'is_nationwide' => 1,
        'anonymous_pre_consult_status' => 'no',
        'has_attorneys' => 'no',
        'whistleblower_scope' => 2,
        'whistleblower_fit' => 'significant-program',
        'service_depth' => 'referral-only',
        'intake_commitment_class' => 'general-contact-only',
        'eligibility_status' => 'restricted',
        'services' => [
            'referral',
            'advocacy',
        ],
        'employment_sectors' => [
            'all-sectors-only',
        ],
        'protected_classes'       => [ 'all-employees-only' ],
        'protected_disclosures' => [
            'public-corruption-ethics',
            'election-integrity',
            'environmental-protection',
            'cybersecurity-disclosure',
            'consumer-data-protection',
        ],
        'disclosure_targets' => [
            'agency-federal',
            'public-nonprofit',
        ],
        'case_stages' => [
            'pre-report',
            'post-report',
        ],
    ],
    [
        'common_name' => 'NELP',
        'official_name' => 'National Employment Law Project',
        'official_homepage_url' => 'https://www.nelp.org',
        'intake_url' => '',
        'contact_url' => 'https://www.nelp.org/about-us/contact-us/',
        'phones' => [
            [ 'type' => 'headquarters', 'number' => '(212) 285-3025' ],
        ],
        'emails' => [
            [ 'type' => 'general', 'address' => 'nelp@nelp.org' ],
        ],
        'secure_channel_status' => 'none-found',
        'secure_contact_tools' => [],
        'mailing_address' => 'National Office: National Employment Law Project, PO Box 1779, New York, NY 10008',
        'income_screening' => 'not-required',
        'organization_model' => 'advocacy',
        'cost_models' => [ 'free' ],
        'languages' => [ 'english' ],
        'is_nationwide' => 1,
        'anonymous_pre_consult_status' => 'no',
        'has_attorneys' => 'no',
        'whistleblower_scope' => 1,
        'whistleblower_fit' => 'adjacent-help',
        'service_depth' => 'information-only',
        'intake_commitment_class' => 'general-contact-only',
        'eligibility_status' => 'open-to-public',
        'services' => [
            'advocacy',
        ],
        'employment_sectors' => [
            'private-sector',
            'nonprofit-ngo',
        ],
        'protected_classes'       => [ 'corporate-staff', 'contractor-gig', 'agricultural-worker' ],
        'protected_disclosures' => [
            'wage-hour-violations',
            'occupational-health-safety',
        ],
        'disclosure_targets' => [
            'agency-federal',
            'agency-state',
        ],
        'case_stages' => [
            'post-report',
            'retaliation-active',
        ],
    ],
    [
        'common_name' => 'NELA',
        'official_name' => 'National Employment Lawyers Association',
        'official_homepage_url' => 'https://www.nela.org',
        'intake_url' => 'https://engagement.nela.org/NELA/findalawyer.aspx',
        'contact_url' => '',
        'phones' => [
            [ 'type' => 'headquarters', 'number' => '(415) 296-7629' ],
        ],
        'emails' => [
            [ 'type' => 'general', 'address' => 'nelahq@nelahq.org' ],
        ],
        'secure_channel_status' => 'none-found',
        'secure_contact_tools' => [],
        'mailing_address' => '1800 Sutter Street, Suite 210, Concord, CA 94520',
        'income_screening' => 'not-required',
        'organization_model' => 'bar-program',
        'cost_models' => [ 'fee-for-service' ],
        'languages' => [ 'english' ],
        'is_nationwide' => 1,
        'anonymous_pre_consult_status' => 'no',
        'has_attorneys' => 'no',
        'whistleblower_scope' => 1,
        'whistleblower_fit' => 'adjacent-help',
        'service_depth' => 'referral-only',
        'intake_commitment_class' => 'referral-request',
        'eligibility_status' => 'referral-only',
        'services' => [
            'referral',
        ],
        'employment_sectors' => [
            'all-sectors-only',
        ],
        'protected_classes'       => [ 'all-employees-only' ],
        'protected_disclosures' => [
            'wage-hour-violations',
            'occupational-health-safety',
            'public-corruption-ethics',
            'general-wrongdoing',
        ],
        'disclosure_targets' => [
            'court-filing',
            
        ],
        'case_stages' => [
            'post-report',
            'retaliation-active',
            'litigation',
        ],
    ],
    [
        'common_name' => 'LSC',
        'official_name' => 'Legal Services Corporation - Find Legal Aid',
        'official_homepage_url' => 'https://www.lsc.gov',
        'intake_url' => 'https://www.lsc.gov/about-lsc/what-legal-aid/get-legal-help',
        'contact_url' => '',
        'phones' => [
            [ 'type' => 'headquarters', 'number' => '(202) 295-1500' ],
        ],
        'emails' => [],
        'secure_channel_status' => 'none-found',
        'secure_contact_tools' => [],
        'mailing_address' => 'Legal Services Corporation, 3333 K Street NW, Washington, DC 20007',
        'income_screening' => 'required',
        'organization_model' => 'legal-aid',
        'cost_models' => [ 'free' ],
        'languages' => [ 'english' ],
        'is_nationwide' => 1,
        'anonymous_pre_consult_status' => 'no',
        'has_attorneys' => 'no',
        'whistleblower_scope' => 1,
        'whistleblower_fit' => 'adjacent-help',
        'service_depth' => 'referral-only',
        'intake_commitment_class' => 'referral-request',
        'eligibility_status' => 'referral-only',
        'services' => [
            'referral',
        ],
        'employment_sectors' => [
            'all-sectors-only',
        ],
        'protected_classes'       => [ 'all-employees-only' ],
        'protected_disclosures' => [
            'wage-hour-violations',
            'occupational-health-safety',
            'public-corruption-ethics',
            'general-wrongdoing',
        ],
        'disclosure_targets' => [
            'agency-federal',
            'agency-state',
            'court-filing',
        ],
        'case_stages' => [
            'pre-report',
            'post-report',
            'retaliation-active',
        ],
    ],
    [
        'common_name' => 'NLADA',
        'official_name' => 'National Legal Aid and Defender Association',
        'official_homepage_url' => 'https://www.nlada.org',
        'intake_url' => '',
        'contact_url' => '',
        'phones' => [
            [ 'type' => 'headquarters', 'number' => '(202) 452-0620' ],
        ],
        'emails' => [],
        'secure_channel_status' => 'none-found',
        'secure_contact_tools' => [],
        'mailing_address' => 'National Legal Aid and Defender Association, 1140 Connecticut Ave NW, Suite 900, Washington, DC 20036',
        'income_screening' => 'not-required',
        'organization_model' => 'advocacy',
        'cost_models' => [ 'free' ],
        'languages' => [ 'english' ],
        'is_nationwide' => 1,
        'anonymous_pre_consult_status' => 'no',
        'has_attorneys' => 'no',
        'whistleblower_scope' => 1,
        'whistleblower_fit' => 'adjacent-help',
        'service_depth' => 'referral-only',
        'intake_commitment_class' => 'information-only',
        'eligibility_status' => 'referral-only',
        'services' => [
            'referral',
            'advocacy',
        ],
        'employment_sectors' => [
            'all-sectors-only',
        ],
        'protected_classes'       => [ 'all-employees-only', 'agricultural-worker' ],
        'protected_disclosures' => [
            'wage-hour-violations',
            'occupational-health-safety',
            'public-corruption-ethics',
            'general-wrongdoing',
        ],
        'disclosure_targets' => [
            'agency-state',
            'court-filing',
        ],
        'case_stages' => [
            'pre-report',
            'post-report',
            'retaliation-active',
        ],
    ],
    [
        'common_name' => 'NWC Referral',
        'official_name' => 'National Whistleblower Center — Attorney Referral Program',
        'official_homepage_url' => 'https://www.whistleblowers.org',
        'intake_url' => 'https://www.whistleblowers.org/find-a-whisteblower-attorney/',
        'contact_url' => '',
        'phones' => [
            [ 'type' => 'headquarters', 'number' => '(202) 342-1900' ],
        ],
        'emails' => [
            [ 'type' => 'general', 'address' => 'info@whistleblowers.org' ],
        ],
        'secure_channel_status' => 'none-found',
        'secure_contact_tools' => [],
        'mailing_address' => 'National Whistleblower Center, 2001 S Street NW, Washington, DC 20009',
        'income_screening' => 'not-required',
        'organization_model' => 'bar-program',
        'cost_models' => [ 'free' ],
        'languages' => [ 'english' ],
        'is_nationwide' => 1,
        'anonymous_pre_consult_status' => 'no',
        'has_attorneys' => 'no',
        'whistleblower_scope' => 2,
        'whistleblower_fit' => 'significant-program',
        'service_depth' => 'referral-only',
        'intake_commitment_class' => 'referral-request',
        'eligibility_status' => 'referral-only',
        'services' => [
            'referral',
        ],
        'employment_sectors' => [
            'all-sectors-only',
        ],
        'protected_classes'       => [ 'federal-employee', 'corporate-staff', 'contractor-gig' ],
        'protected_disclosures' => [
            'securities-commodities-fraud',
            'tax-evasion-fraud',
            'public-corruption-ethics',
            'procurement-spending-fraud',
            'healthcare-medicare-fraud',
        ],
        'disclosure_targets' => [
            'agency-federal',
            'court-filing',
        ],
        'case_stages' => [
            'pre-report',
            'post-report',
            'litigation',
        ],
    ],
    [
        'common_name' => 'ABA',
        'official_name' => 'American Bar Association — Find Legal Help',
        'official_homepage_url' => 'https://www.americanbar.org/groups/legal_services/',
        'intake_url' => 'https://www.americanbar.org/groups/legal_services/flh-home/',
        'contact_url' => '',
        'phones' => [
            [ 'type' => 'headquarters', 'number' => '(800) 285-2221' ],
        ],
        'emails' => [
            [ 'type' => 'general', 'address' => 'info@americanbar.org' ],
        ],
        'secure_channel_status' => 'none-found',
        'secure_contact_tools' => [],
        'mailing_address' => 'American Bar Association, 321 N Clark St, Chicago, IL 60654',
        'income_screening' => 'required',
        'organization_model' => 'bar-program',
        'cost_models' => [ 'fee-for-service' ],
        'languages' => [ 'english' ],
        'is_nationwide' => 1,
        'anonymous_pre_consult_status' => 'no',
        'has_attorneys' => 'no',
        'whistleblower_scope' => 1,
        'whistleblower_fit' => 'adjacent-help',
        'service_depth' => 'referral-only',
        'intake_commitment_class' => 'referral-request',
        'eligibility_status' => 'referral-only',
        'services' => [
            'referral',
        ],
        'employment_sectors' => [
            'all-sectors-only',
        ],
        'protected_classes'       => [ 'all-employees-only' ],
        'protected_disclosures' => [
            'wage-hour-violations',
            'occupational-health-safety',
            'securities-commodities-fraud',
            'healthcare-medicare-fraud',
            'general-wrongdoing',
        ],
        'disclosure_targets' => [
            'court-filing',
            
        ],
        'case_stages' => [
            'pre-report',
            'post-report',
            'litigation',
        ],
    ],
    [
        'common_name' => 'PEER',
        'official_name' => 'Public Employees for Environmental Responsibility',
        'official_homepage_url' => 'https://peer.org',
        'intake_url' => 'https://peer.org/contact-legal-team/',
        'contact_url' => 'https://peer.org/about-us/contact-us/',
        'phones' => [
            [ 'type' => 'headquarters', 'number' => '(202) 265-7337' ],
        ],
        'emails' => [
            [ 'type' => 'general', 'address' => 'info@peer.org' ],
        ],
        'secure_channel_status' => 'none-found',
        'secure_contact_tools' => [],
        'mailing_address' => 'Public Employees for Environmental Responsibility, 962 Wayne Ave, Suite 610, Silver Spring, MD 20910',
        'income_screening' => 'not-required',
        'organization_model' => 'nonprofit',
        'cost_models' => [ 'pro-bono' ],
        'languages' => [ 'english' ],
        'is_nationwide' => 1,
        'anonymous_pre_consult_status' => 'yes',
        'has_attorneys' => 'yes',
        'whistleblower_scope' => 2,
        'whistleblower_fit' => 'significant-program',
        'service_depth' => 'direct-representation',
        'intake_commitment_class' => 'personal-help-request',
        'eligibility_status' => 'restricted',
        'services' => [
            'legal-rep',
            'consultation',
            'retaliation',
        ],
        'employment_sectors' => [
            'federal-employee',
            'state-local-employee',
        ],
        'protected_classes' => [ 'federal-employee', 'state-employee', 'local-gov-staff' ],
        'protected_disclosures' => [
            'environmental-protection',
            'occupational-health-safety',
        ],
        'disclosure_targets' => [
            'agency-federal',
            'agency-state',
            'legislative-state',
        ],
        'case_stages' => [
            'pre-report',
            'retaliation-active',
            'litigation',
        ],
    ],
    [
        'common_name' => 'TSN',
        'official_name' => 'The Signals Network',
        'official_homepage_url' => 'https://thesignalsnetwork.org',
        'intake_url' => 'https://thesignalsnetwork.org/whistleblower-protection-program/',
        'contact_url' => 'https://thesignalsnetwork.org/contact/',
        'phones' => [],
        'emails' => [
            [ 'type' => 'general', 'address' => 'info@thesignalsnetwork.org' ],
            [ 'type' => 'intake', 'address' => 'protect@thesignalsnetwork.org' ],
        ],
        'secure_channel_status' => 'dedicated-secure-channel',
        'secure_contact_tools' => [ 'protonmail' ],
        'mailing_address' => 'The Signals Network, 416 Florida Ave NW #26152, Washington, DC 20001',
        'income_screening' => 'not-required',
        'organization_model' => 'nonprofit',
        'cost_models' => [ 'free' ],
        'languages' => [ 'english' ],
        'is_nationwide' => 1,
        'anonymous_pre_consult_status' => 'yes',
        'has_attorneys' => 'yes',
        'whistleblower_scope' => 3,
        'whistleblower_fit' => 'primary-focus',
        'service_depth' => 'ongoing-support',
        'intake_commitment_class' => 'personal-help-request',
        'eligibility_status' => 'screening-required',
        'services' => [
            'consultation',
            'media',
            'retaliation',
        ],
        'employment_sectors' => [
            'all-sectors-only',
        ],
        'protected_classes' => [ 'all-employees-only' ],
        'protected_disclosures' => [
            'general-wrongdoing',
            'public-corruption-ethics',
            'healthcare-medicare-fraud',
        ],
        'disclosure_targets' => [
            'public-media',
            'agency-federal',
        ],
        'case_stages' => [
            'pre-report',
            'retaliation-active',
        ],
    ],
];

// ════════════════════════════════════════════════════════════════════════════
// Seeder: ws_seed_assist_org_matrix
// ════════════════════════════════════════════════════════════════════════════

function ws_seed_assist_org_matrix() {

    global $_ws_assist_org_matrix;

    // Resolve the US jurisdiction term ID.
    $us_term = ws_jx_term_by_code( 'us' );
    if ( ! $us_term || is_wp_error( $us_term ) ) {
                error_log( sprintf(
                    '[ws-core assist-org matrix] Missing \'us\' SLUG in WS_JURISDICTION_TAXONOMY (expected %s, referenced from %s line %d)',
                    $slug,
                    __FILE__,
                    __LINE__
                ) );
                wp_die("You_Fuq'd_Up! — see the log");
            }

    $us_term_id = (int) $us_term->term_id;

    if ( ! defined( 'WS_MATRIX_SEEDING_IN_PROGRESS' ) ) {
        define( 'WS_MATRIX_SEEDING_IN_PROGRESS', true );
    }

    foreach ( $_ws_assist_org_matrix as $org ) {

        $official_name = trim( (string) $org['official_name'] );

        $existing_posts = get_posts( [
            'post_type'      => 'ws-assist-org',
            'post_status'    => 'any',
            'posts_per_page' => 2,
            'title'          => $official_name,
            'fields'         => 'ids',
            'no_found_rows'  => true,
        ] );

        if ( count( $existing_posts ) > 1 ) {
            wp_die(
                esc_html( "[ws-core assist-org matrix] Multiple ws-assist-org posts found for '{$official_name}'." ),
                esc_html__( 'Assist-org matrix seed failed', 'whistleblowershield' ),
                [ 'response' => 500 ]
            );
        }

        $existing = ! empty( $existing_posts ) ? get_post( (int) $existing_posts[0] ) : null;

        if ( $existing ) {
            $post_id = $existing->ID;
        } else {
            $post_id = wp_insert_post( [
                'post_title'   => $official_name,
                'post_type'    => 'ws-assist-org',
                'post_status'  => 'publish',
                'post_content' => '',
            ] );
        }

        if ( is_wp_error( $post_id ) || ! $post_id ) {
            continue;
        }

        update_post_meta( $post_id, 'ws_aorg_official_name', $official_name );
        update_post_meta( $post_id, 'ws_aorg_common_name', $org['common_name'] );
        update_post_meta( $post_id, 'ws_aorg_official_homepage_url', $org['official_homepage_url'] );
        update_post_meta( $post_id, 'ws_aorg_intake_url', $org['intake_url'] );
        update_post_meta( $post_id, 'ws_aorg_contact_url', $org['contact_url'] );
        update_post_meta( $post_id, 'ws_aorg_secure_channel_status', $org['secure_channel_status'] );
        update_post_meta( $post_id, 'ws_aorg_secure_contact_tools', $org['secure_contact_tools'] );
        update_post_meta( $post_id, 'ws_aorg_mailing_address', $org['mailing_address'] );
        update_post_meta( $post_id, 'ws_aorg_income_screening', $org['income_screening'] );
        update_post_meta( $post_id, 'ws_aorg_eligibility_status', $org['eligibility_status'] );
        update_post_meta( $post_id, 'ws_aorg_is_nationwide', $org['is_nationwide'] );
        update_post_meta( $post_id, 'ws_aorg_anonymous_pre_consult_status', $org['anonymous_pre_consult_status'] );
        update_post_meta( $post_id, 'ws_aorg_has_attorneys', $org['has_attorneys'] );
        update_post_meta( $post_id, 'ws_aorg_whistleblower_fit', $org['whistleblower_fit'] );
        update_post_meta( $post_id, 'ws_aorg_service_depth', $org['service_depth'] );
        update_post_meta( $post_id, 'ws_aorg_intake_commitment_class', $org['intake_commitment_class'] );
        update_post_meta( $post_id, 'ws_aorg_whistleblower_scope', $org['whistleblower_scope'] );

        $post = get_post( (int) $post_id );
        if ( ! $post || $post->post_name === '' ) {
            continue;
        }

        update_post_meta(
            $post_id,
            '_ws_aorg_id',
            ws_matrix_build_assist_org_internal_id( 'us', (string) $post->post_name )
        );

        $phones = $org['phones'];
        update_post_meta( $post_id, 'ws_aorg_phones', count( $phones ) );
        foreach ( $phones as $i => $phone ) {
            update_post_meta( $post_id, "ws_aorg_phones_{$i}_ws_aorg_phone_type", $phone['type'] );
            update_post_meta( $post_id, "ws_aorg_phones_{$i}_ws_aorg_phone_number", $phone['number'] );
        }

        $emails = $org['emails'];
        update_post_meta( $post_id, 'ws_aorg_emails', count( $emails ) );
        foreach ( $emails as $i => $email ) {
            update_post_meta( $post_id, "ws_aorg_emails_{$i}_ws_aorg_email_type", $email['type'] );
            update_post_meta( $post_id, "ws_aorg_emails_{$i}_ws_aorg_email_address", $email['address'] );
        }

        wp_set_object_terms( $post_id, [ $org['organization_model'] ], 'ws_organization_model' );
        wp_set_object_terms( $post_id, $org['cost_models'], 'ws_aorg_cost_model' );
        wp_set_object_terms( $post_id, $org['protected_disclosures'], 'ws_protected_disclosure' );
        wp_set_object_terms( $post_id, $org['disclosure_targets'], 'ws_disclosure_target' );
        wp_set_object_terms( $post_id, $org['case_stages'], 'ws_case_stage' );
        wp_set_object_terms( $post_id, $org['services'], 'ws_aorg_service' );
        wp_set_object_terms( $post_id, $org['employment_sectors'], 'ws_employment_sector' );
        wp_set_object_terms( $post_id, $org['protected_classes'], 'ws_protected_class' );
        wp_set_object_terms( $post_id, $org['languages'], 'ws_language' );
        wp_set_object_terms( $post_id, $us_term_id, WS_JURISDICTION_TAXONOMY );

        update_post_meta( $post_id, 'ws_matrix_source', 'matrix-assist-orgs' );
    }
}


// ── Gate ──────────────────────────────────────────────────────────────────────

add_action( 'admin_init', function() {
    if ( get_option( 'ws_seeded_assist_org_matrix' ) !== '1.0.0' ) {
        ws_seed_assist_org_matrix();
        update_option( 'ws_seeded_assist_org_matrix', '1.0.0' );
    }
} );
