<?php
/**
 * matrix-assist-orgs.php — Seeds nationwide and federal-scope whistleblower support organizations.
 *
 * @package    WhistleblowerShield
 * @since      3.0.0
 * @version    3.19.0
 *
 * VERSION
 * -------
 * 3.19.0  Gap-fill for remaining 7 orgs (WIN, NELP, NELA, LSC, NLADA,
 *         NWC Referral, ABA) — nationwide_example and protected_classes
 *         added to all 7; protected_class_details added to all 7.
 *         NWC Referral: whistleblower_scope corrected 3→2 (referral
 *         program, not full-service org); cost_models corrected
 *         fee-for-service→free (ARP is explicitly free to whistleblowers).
 *         Gate bumped to 1.2.0.
 *
 * VERSION
 * -------
 * 3.18.1  Factual conflict resolution after live-site verification:
 *         - NWC mailing_address corrected to 1800 M Street NW #33888, DC 20033
 *           (whistleblowers.org contact page confirmed; old 1140 Connecticut Ave was stale)
 *         - PEER mailing_address: expanded 'PEER' abbreviation to full org name
 *         - GAP zip +4 (20006-2802): kept — USPS extended zip, not incorrect
 *         - WbAid secure contact tools: Signal-specific page confirmed live
 *         - POGO secure_channel_status: kept none-found — no live public secure channel confirmed
 *         - TAF has_attorneys: kept 1 — staff page confirms 3 attorneys on staff
 *         - NWC/PEER contact_url from JSON was blank — kept existing matrix values
 *
 * 3.18.0  Gap-fill from Grok research batches (US-0-Assist-org-Grok-4-matrix-updates*.json).
 *         Filled ws_aorg_contact_url where empty: NWC, POGO.
 *         (GAP, WbAid, TAF had no Grok contact_url to fill from.)
 *         Inserted nationwide_example after whistleblower_scope_details for all 8 orgs.
 *         Inserted protected_classes and protected_class_details after sectors
 *         for all 8 researched orgs. No existing values overwritten.
 *
 * 3.17.1  Data corrections from deep research pass:
 *         - GAP: corrected mailing address to 1612 K St NW Suite 808;
 *           updated intake_url to /how-to-request-assistance/
 *         - Whistleblower Aid: corrected mailing address to
 *           1250 Connecticut Ave NW Suite 700 (Charity Navigator confirmed)
 *         - TAF: re-branded from Taxpayers Against Fraud Education Fund
 *           to The Anti-Fraud Coalition; slug, internal_id, description,
 *           mailing address, and intake_url updated; gate bumped to 1.1.0
 *         - The Signals Network: secure_channel_status set to dedicated-secure-channel (ProtonMail
 *           confirmed); secure fields populated; protect@ email added to
 *           emails repeater; ProtonMail note removed from description;
 *           intake_url added
 *         - WIN: removed invalid public-general slug from disclosure_targets
 *         - POGO: whistleblower_scope corrected from 3 to 1 (investigative
 *           watchdog, not direct help org)
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
        '_id' => 'us-gov-act-proj',
        'common_name' => 'GAP',
        'official_name' => 'Government Accountability Project',
        'slug' => 'government-accountability-project',
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
        '_id' => 'nat-wb-ctr-us',
        'common_name' => 'NWC',
        'official_name' => 'National Whistleblower Center',
        'slug' => 'national-whistleblower-center',
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
        'protected_classes' => [ 'all-employees' ],
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
        '_id' => 'wb-aid-us',
        'common_name' => '',
        'official_name' => 'Whistleblower Aid',
        'slug' => 'whistleblower-aid',
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
        '_id' => 'proj-on-gov-oversight-us',
        'common_name' => 'POGO',
        'official_name' => 'Project On Government Oversight',
        'slug' => 'project-on-government-oversight',
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
        '_id' => 'anti-fraud-coal-us',
        'common_name' => 'TAF',
        'official_name' => 'The Anti-Fraud Coalition',
        'slug' => 'the-anti-fraud-coalition',
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
        'protected_classes' => [ 'all-employees' ],
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
        '_id' => 'wb-america-us',
        'common_name' => 'WoA',
        'official_name' => 'Whistleblowers of America',
        'slug' => 'whistleblowers-of-america',
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
        'protected_classes' => [ 'all-employees' ],
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
        '_id' => 'wb-intl-net-us',
        'common_name' => 'WIN',
        'official_name' => 'Whistleblowing International Network',
        'slug' => 'whistleblowing-international-network',
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
        'protected_classes'       => [ 'all-employees' ],
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
        '_id' => 'nat-emp-law-proj-us',
        'common_name' => 'NELP',
        'official_name' => 'National Employment Law Project',
        'slug' => 'national-employment-law-project',
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
        '_id' => 'nat-emp-lawyers-assoc-us',
        'common_name' => 'NELA',
        'official_name' => 'National Employment Lawyers Association',
        'slug' => 'national-employment-lawyers-association',
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
        'protected_classes'       => [ 'all-employees' ],
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
        '_id' => 'legal-svc-corp-find-legal-aid-us',
        'common_name' => 'LSC',
        'official_name' => 'Legal Services Corporation - Find Legal Aid',
        'slug' => 'legal-services-corporation-find-legal-aid',
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
        'protected_classes'       => [ 'all-employees' ],
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
        '_id' => 'nat-legal-aid-defender-assoc-us',
        'common_name' => 'NLADA',
        'official_name' => 'National Legal Aid and Defender Association',
        'slug' => 'national-legal-aid-and-defender-association',
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
        'protected_classes'       => [ 'all-employees', 'agricultural-worker' ],
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
        '_id' => 'nat-wb-ctr-att-ref-prog-us',
        'common_name' => 'NWC Referral',
        'official_name' => 'National Whistleblower Center — Attorney Referral Program',
        'slug' => 'national-whistleblower-center-attorney-referral',
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
        '_id' => 'american-bar-assoc-find-legal-help-us',
        'common_name' => 'ABA',
        'official_name' => 'American Bar Association — Find Legal Help',
        'slug' => 'american-bar-association-find-legal-help',
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
        'protected_classes'       => [ 'all-employees' ],
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
        '_id' => 'pub-emp-environmental-responsibility-us',
        'common_name' => 'PEER',
        'official_name' => 'Public Employees for Environmental Responsibility',
        'slug' => 'public-employees-for-environmental-responsibility',
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
        '_id' => 'signals-net-us',
        'common_name' => 'TSN',
        'official_name' => 'The Signals Network',
        'slug' => 'the-signals-network',
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
        'protected_classes' => [ 'all-employees' ],
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

/**
 * Builds a normalized assist-org internal ID using the ingest ruleset.
 *
 * This keeps matrix-seeded IDs consistent with ingest-generated IDs.
 *
 * @param array  $org     Matrix org row.
 * @param string $jx_slug Jurisdiction slug suffix (typically 'us').
 * @return string
 */
function ws_matrix_build_assist_org_internal_id( array $org, string $jx_slug = '' ): string {
    $org_name = trim( (string) ws_matrix_required( $org, 'official_name' ) );
    $homepage = trim( (string) ws_matrix_required( $org, 'official_homepage_url' ) );
    $jx_slug  = strtolower( trim( (string) $jx_slug ) );

    $host = strtolower( (string) wp_parse_url( $homepage, PHP_URL_HOST ) );
    if ( str_starts_with( $host, 'www.' ) ) {
        $host = substr( $host, 4 );
    }

    $seed = $org_name !== '' ? $org_name : $host;
    if ( $seed === '' ) {
        $seed = 'assist-org';
    }

    $normalized = strtolower( $seed );
    $normalized = str_replace( '&', ' ', $normalized );

    if ( $jx_slug !== '' && defined( 'WS_JURISDICTION_TAXONOMY' ) ) {
        $jx_term = get_term_by( 'slug', $jx_slug, WS_JURISDICTION_TAXONOMY );
        if ( $jx_term && ! is_wp_error( $jx_term ) ) {
            $jx_name = strtolower( trim( (string) $jx_term->name ) );
            if ( $jx_name !== '' ) {
                $jx_name_rx = preg_quote( $jx_name, '/' );
                $normalized = preg_replace( '/\b' . $jx_name_rx . '\b/u', ' ' . $jx_slug . ' ', $normalized );
            }
        }
    }

    $normalized = preg_replace( '/\b(?:and|the|for|of|in|at|to|a|an)\b/u', ' ', $normalized );

    // IMPORTANT: Keep this ruleset in sync with
    // ws_ingest_build_aorg_internal_id() in tool-ingest.php.
    // If these diverge, seeded/internal IDs will drift over time.
    $abbrev_rules = [
        '/\bwhistle[\s\-]*blow(?:er|ers|ing)\b/u' => 'wb',
        '/\bglobal\b/u'                              => 'intl',
        '/\binternational\b/u'                       => 'intl',
        '/\bnationals?\b/u'                          => 'nat',
        '/\borganizations?\b/u'                      => 'org',
        '/\borganisations?\b/u'                      => 'org',
        '/\bassociations?\b/u'                       => 'assoc',
        '/\bcoalitions?\b/u'                         => 'coal',
        '/\balliances?\b/u'                          => 'all',
        '/\bcommittees?\b/u'                         => 'cmte',
        '/\bcouncils?\b/u'                           => 'cncl',
        '/\binstitutions?\b/u'                       => 'inst',
        '/\binstitutes?\b/u'                         => 'inst',
        '/\bbureaus?\b/u'                            => 'bur',
        '/\boffices?\b/u'                            => 'ofc',
        '/\bemployees?\b/u'                          => 'emp',
        '/\bemployment\b/u'                          => 'emp',
        '/\bprotections?\b/u'                        => 'prot',
        '/\badvocacy\b/u'                            => 'adv',
        '/\brights\b/u'                              => 'rts',
        '/\bpublic\b/u'                              => 'pub',
        '/\bpolicy\b/u'                              => 'pol',
        '/\beducational\b/u'                         => 'edu',
        '/\beducation\b/u'                           => 'edu',
        '/\bresearch\b/u'                            => 'rsch',
        '/\battorneys?\b/u'                          => 'att',
        '/\breferrals?\b/u'                          => 'ref',
        '/\bfederal\b/u'                             => 'fed',
        '/\bgovernmental\b/u'                        => 'gov',
        '/\bgovernments?\b/u'                        => 'gov',
        '/\bdepartments?\b/u'                        => 'dept',
        '/\bcommissions?\b/u'                        => 'comm',
        '/\bcorporations?\b/u'                       => 'corp',
        '/\bfoundations?\b/u'                        => 'fdn',
        '/\bcenters?\b/u'                            => 'ctr',
        '/\bcentres?\b/u'                            => 'ctr',
        '/\bservices?\b/u'                           => 'svc',
        '/\bnetworks?\b/u'                           => 'net',
        '/\bprograms?\b/u'                           => 'prog',
        '/\bprojects?\b/u'                           => 'proj',
        '/\binitiatives?\b/u'                        => 'init',
        '/\bresources?\b/u'                          => 'res',
        '/\baccountability\b/u'                      => 'acct',
    ];

    foreach ( $abbrev_rules as $pattern => $replacement ) {
        $normalized = preg_replace( $pattern, ' ' . $replacement . ' ', $normalized );
    }

    $normalized = preg_replace( '/[^a-z0-9]+/u', '-', $normalized );
    $normalized = trim( (string) $normalized, '-' );
    $normalized = preg_replace( '/-+/', '-', (string) $normalized );

    if ( $normalized === '' ) {
        $normalized = $host !== '' ? sanitize_title( $host ) : 'assist-org';
    }

    if ( $jx_slug !== '' && ! preg_match( '/(^|-)' . preg_quote( $jx_slug, '/' ) . '($|-)/', $normalized ) ) {
        $normalized .= '-' . $jx_slug;
    }

    return $normalized;
}

/**
 * Throws a clear matrix-shape error instead of silently tolerating bad seed data.
 *
 * @param string $message Error detail.
 * @return void
 */
function ws_matrix_error( string $message ): void {
    throw new RuntimeException( $message . ' This canonical matrix row is bullsh!t.' );
}

/**
 * Reads a required canonical matrix key.
 *
 * @param array  $org Matrix org row.
 * @param string $key Canonical key name.
 * @return mixed
 */
function ws_matrix_required( array $org, string $key ) {
    if ( ! array_key_exists( $key, $org ) ) {
        $label = (string) ( $org['slug'] ?? $org['official_name'] ?? 'unknown assist org' );
        ws_matrix_error( "Missing required matrix key '{$key}' for {$label}." );
    }

    return $org[ $key ];
}

/**
 * Reads an explicitly optional canonical matrix key.
 *
 * @param array  $org     Matrix org row.
 * @param string $key     Canonical key name.
 * @param mixed  $default Default value.
 * @return mixed
 */
function ws_matrix_optional( array $org, string $key, $default = '' ) {
    return array_key_exists( $key, $org ) ? $org[ $key ] : $default;
}

/**
 * Requires a value from an allowed set.
 *
 * @param array  $org     Matrix org row.
 * @param string $key     Canonical key name.
 * @param array  $allowed Allowed values.
 * @return string
 */
function ws_matrix_required_enum( array $org, string $key, array $allowed ): string {
    $value = sanitize_key( (string) ws_matrix_required( $org, $key ) );

    if ( $value === 'has-details' ) {
        $label = (string) ( $org['slug'] ?? $org['official_name'] ?? 'unknown assist org' );
        ws_matrix_error( "Editorial flag 'has-details' is not seedable data for '{$key}' in {$label}." );
    }

    if ( ! in_array( $value, $allowed, true ) ) {
        $label = (string) ( $org['slug'] ?? $org['official_name'] ?? 'unknown assist org' );
        ws_matrix_error( "Invalid value '{$value}' for matrix key '{$key}' in {$label}." );
    }

    return $value;
}

/**
 * Reads a required canonical array key.
 *
 * @param array  $org Matrix org row.
 * @param string $key Canonical key name.
 * @return array
 */
function ws_matrix_required_array( array $org, string $key ): array {
    $value = ws_matrix_required( $org, $key );

    if ( ! is_array( $value ) ) {
        $label = (string) ( $org['slug'] ?? $org['official_name'] ?? 'unknown assist org' );
        ws_matrix_error( "Matrix key '{$key}' must be an array for {$label}." );
    }

    return $value;
}

/**
 * Reads and validates typed phone channel rows for ACF repeater storage.
 *
 * @param array $org Matrix org row.
 * @return array<int,array{type:string,number:string}>
 */
function ws_matrix_phone_channels( array $org ): array {
    $rows = ws_matrix_required_array( $org, 'phones' );
    $allowed = [ 'hotline', 'intake', 'headquarters', 'regional', 'tty', 'fax', 'other' ];
    $label = (string) ( $org['slug'] ?? $org['official_name'] ?? 'unknown assist org' );
    $out = [];

    foreach ( $rows as $i => $row ) {
        if ( ! is_array( $row ) ) {
            ws_matrix_error( "Phone row {$i} must be an array for {$label}." );
        }

        $type = sanitize_key( (string) ( $row['type'] ?? '' ) );
        $number = trim( (string) ( $row['number'] ?? '' ) );

        if ( $type === '' || $number === '' ) {
            ws_matrix_error( "Phone row {$i} requires type and number for {$label}." );
        }

        if ( ! in_array( $type, $allowed, true ) ) {
            ws_matrix_error( "Invalid phone type '{$type}' in row {$i} for {$label}." );
        }

        $out[] = [
            'type'   => $type,
            'number' => $number,
        ];
    }

    return $out;
}

/**
 * Reads and validates typed email channel rows for ACF repeater storage.
 *
 * @param array $org Matrix org row.
 * @return array<int,array{type:string,address:string}>
 */
function ws_matrix_email_channels( array $org ): array {
    $rows = ws_matrix_required_array( $org, 'emails' );
    $allowed = [ 'intake', 'general', 'legal', 'media', 'support', 'secure', 'other' ];
    $label = (string) ( $org['slug'] ?? $org['official_name'] ?? 'unknown assist org' );
    $out = [];

    foreach ( $rows as $i => $row ) {
        if ( ! is_array( $row ) ) {
            ws_matrix_error( "Email row {$i} must be an array for {$label}." );
        }

        $type = sanitize_key( (string) ( $row['type'] ?? '' ) );
        $address = sanitize_email( (string) ( $row['address'] ?? '' ) );

        if ( $type === '' || $address === '' ) {
            ws_matrix_error( "Email row {$i} requires type and a valid address for {$label}." );
        }

        if ( ! in_array( $type, $allowed, true ) ) {
            ws_matrix_error( "Invalid email type '{$type}' in row {$i} for {$label}." );
        }

        $out[] = [
            'type'    => $type,
            'address' => $address,
        ];
    }

    return $out;
}

/**
 * Writes an ACF repeater directly from canonical matrix rows.
 *
 * The matrix is already canonical; this function only maps row keys to ACF's
 * raw repeater meta keys and fails loudly if the row shape is malformed.
 *
 * @param int    $post_id       Post ID.
 * @param string $field_name    ACF repeater field name.
 * @param array  $rows          Canonical rows.
 * @param array  $subfield_map  Canonical key => ACF subfield name.
 * @return void
 */
function ws_matrix_write_repeater( int $post_id, string $field_name, array $rows, array $subfield_map ): void {
    update_post_meta( $post_id, $field_name, count( $rows ) );

    foreach ( $rows as $i => $row ) {
        if ( ! is_array( $row ) ) {
            ws_matrix_error( "Repeater '{$field_name}' row {$i} must be an array on post {$post_id}." );
        }

        foreach ( $subfield_map as $source_key => $acf_key ) {
            if ( ! array_key_exists( $source_key, $row ) ) {
                ws_matrix_error(
                    "Repeater '{$field_name}' row {$i} missing required key '{$source_key}' on post {$post_id}."
                );
            }

            update_post_meta( $post_id, "{$field_name}_{$i}_{$acf_key}", $row[ $source_key ] );
        }
    }
}

function ws_matrix_income_screening( array $org ): string {
    return ws_matrix_required_enum(
        $org,
        'income_screening',
        [ 'required', 'not-required', 'possible', 'unclear' ]
    );
}

function ws_matrix_secure_channel_status( array $org ): string {
    return ws_matrix_required_enum(
        $org,
        'secure_channel_status',
        [
            'dedicated-secure-channel',
            'standard-web-form',
            'secure-tip-only',
            'leak-drop-only',
            'none-found',
            'unclear',
        ]
    );
}

/**
 * Normalizes secure contact tools to current multi-select slugs.
 *
 * @param array $org Matrix org row.
 * @return array
 */
function ws_matrix_secure_contact_tools( array $org ): array {
    $tools = ws_matrix_required_array( $org, 'secure_contact_tools' );

    $map = [
        'signal'       => 'signal',
        'protonmail'   => 'protonmail',
        'proton-mail'  => 'protonmail',
        'tutanota'     => 'tutanota',
        'wire'         => 'wire',
        'keybase'      => 'keybase',
        'pgp'          => 'pgp-email',
        'pgp-email'    => 'pgp-email',
        'securedrop'   => 'securedrop',
        'secure-drop'  => 'securedrop',
        'globaleaks'   => 'globaleaks',
        'global-leaks' => 'globaleaks',
        'encrypted-web-form' => 'encrypted-web-form',
        'other'        => 'other',
    ];

    $normalized = [];
    foreach ( $tools as $tool ) {
        $slug = sanitize_key( (string) $tool );
        if ( $slug === 'has-details' ) {
            $label = (string) ( $org['slug'] ?? $org['official_name'] ?? 'unknown assist org' );
            ws_matrix_error( "Editorial flag 'has-details' is not seedable data for {$label}." );
        }

        if ( ! isset( $map[ $slug ] ) ) {
            $label = (string) ( $org['slug'] ?? $org['official_name'] ?? 'unknown assist org' );
            ws_matrix_error( "Invalid secure contact tool '{$slug}' for {$label}." );
        }

        $normalized[] = $map[ $slug ];
    }

    return array_values( array_unique( $normalized ) );
}

/**
 * Assigns terms for the assist-org matrix and fails loudly on bad slugs.
 *
 * @param int    $post_id  Post ID.
 * @param array  $slugs    Term slugs.
 * @param string $taxonomy Taxonomy slug.
 * @return void
 */
function ws_matrix_assign_terms_strict( int $post_id, array $slugs, string $taxonomy ): void {
    if ( ! taxonomy_exists( $taxonomy ) ) {
        ws_matrix_error( "Missing taxonomy '{$taxonomy}' while seeding post {$post_id}." );
    }

    if ( empty( $slugs ) ) {
        ws_matrix_error( "No term slugs provided for taxonomy '{$taxonomy}' on post {$post_id}." );
    }

    $term_ids = [];
    foreach ( $slugs as $slug ) {
        $slug = sanitize_key( (string) $slug );
        if ( $slug === '' ) {
            ws_matrix_error( "Blank term slug passed for taxonomy '{$taxonomy}' on post {$post_id}." );
        }

        if ( $slug === 'has-details' ) {
            ws_matrix_error(
                "Editorial flag 'has-details' is not seedable data for taxonomy '{$taxonomy}' on post {$post_id}."
            );
        }

        $term = get_term_by( 'slug', $slug, $taxonomy );
        if ( ! $term || is_wp_error( $term ) ) {
            ws_matrix_error( "Missing term slug '{$slug}' in taxonomy '{$taxonomy}' on post {$post_id}." );
        }

        $term_ids[] = (int) $term->term_id;
    }

    $result = wp_set_object_terms( $post_id, $term_ids, $taxonomy );
    if ( is_wp_error( $result ) ) {
        ws_matrix_error(
            "Failed assigning taxonomy '{$taxonomy}' on post {$post_id}: " . $result->get_error_message()
        );
    }
}

function ws_seed_assist_org_matrix() {

    global $_ws_assist_org_matrix;

    // Resolve the US jurisdiction term ID.
    $us_term = ws_jx_term_by_code( 'us' );
    if ( ! $us_term || is_wp_error( $us_term ) ) {
        ws_matrix_error( 'US jurisdiction term is missing; assist-org matrix cannot seed.' );
    }
    $us_term_id = (int) $us_term->term_id;

    if ( ! defined( 'WS_MATRIX_SEEDING_IN_PROGRESS' ) ) {
        define( 'WS_MATRIX_SEEDING_IN_PROGRESS', true );
    }

    foreach ( $_ws_assist_org_matrix as $org ) {

        $internal_id = ws_matrix_build_assist_org_internal_id( $org, 'us' );
        if ( $internal_id === '' ) {
            ws_matrix_error( "Failed building internal ID for '{$org['slug']}'." );
        }

        $slug = sanitize_title( (string) ws_matrix_required( $org, 'slug' ) );
        $official_name = trim( (string) ws_matrix_required( $org, 'official_name' ) );
        $description = '';

        $existing = get_page_by_path( $slug, OBJECT, 'ws-assist-org' );
        $content  = '';

        $post_data = [
            'post_title'   => $official_name,
            'post_name'    => $slug,
            'post_type'    => 'ws-assist-org',
            'post_status'  => 'publish',
            'post_content' => $content,
        ];

        if ( $existing ) {
            $post_data['ID'] = $existing->ID;
            $post_id = wp_update_post( $post_data );
        } else {
            $post_id = wp_insert_post( $post_data );
        }

        if ( is_wp_error( $post_id ) || ! $post_id ) {
            $message = is_wp_error( $post_id ) ? $post_id->get_error_message() : 'empty post ID';
            ws_matrix_error( "Failed creating/updating assist-org post '{$slug}': {$message}." );
        }

        // ── ACF meta fields ──────────────────────────────────────────────────
        //
        // String/URL fields: skipped if empty (no point storing blank strings).
        // Boolean fields (0/1) and array fields always write — 0 is meaningful.

        $organization_model = ws_matrix_required_enum(
            $org,
            'organization_model',
            [
                'nonprofit',
                'legal-aid',
                'law-firm',
                'bar-program',
                'advocacy',
                'oversight-office',
                'union',
                'government-office',
                'coalition',
                'program',
                'mixed',
            ]
        );
        $secure_channel_status = ws_matrix_secure_channel_status( $org );
        $secure_contact_tools = ws_matrix_secure_contact_tools( $org );
        $phone_channels = ws_matrix_phone_channels( $org );
        $email_channels = ws_matrix_email_channels( $org );
        $income_screening = ws_matrix_income_screening( $org );
        $anonymous_pre_consult_status = ws_matrix_required_enum(
            $org,
            'anonymous_pre_consult_status',
            [ 'yes', 'no', 'unclear' ]
        );
        $has_attorneys = ws_matrix_required_enum(
            $org,
            'has_attorneys',
            [ 'yes', 'no', 'unclear' ]
        );
        $whistleblower_fit = ws_matrix_required_enum(
            $org,
            'whistleblower_fit',
            [ 'primary-focus', 'significant-program', 'adjacent-help', 'not-specific', 'none', 'unclear' ]
        );
        $service_depth = ws_matrix_required_enum(
            $org,
            'service_depth',
            [
                'information-only',
                'triage-only',
                'brief-advice',
                'document-review',
                'limited-scope-help',
                'direct-representation',
                'referral-only',
                'warm-handoff',
                'peer-support',
                'ongoing-support',
                'unclear',
            ]
        );
        $intake_commitment_class = ws_matrix_required_enum(
            $org,
            'intake_commitment_class',
            [
                'personal-help-request',
                'screening-form',
                'referral-request',
                'peer-support-request',
                'general-contact-only',
                'tip-submission-only',
                'leak-drop-only',
                'information-only',
                'unclear',
            ]
        );
        $eligibility_status = ws_matrix_required_enum(
            $org,
            'eligibility_status',
            [ 'open-to-public', 'screening-required', 'restricted', 'members-only', 'referral-only', 'unclear' ]
        );

        $meta = [
            '_ws_aorg_id'                  => $internal_id,
            'ws_aorg_official_name'        => $official_name,
            'ws_aorg_common_name'          => ws_matrix_optional( $org, 'common_name' ),
            'ws_aorg_official_homepage_url'=> ws_matrix_required( $org, 'official_homepage_url' ),
            'ws_aorg_intake_url'           => ws_matrix_optional( $org, 'intake_url' ),
            'ws_aorg_contact_url'          => ws_matrix_optional( $org, 'contact_url' ),
            'ws_aorg_organization_model'   => $organization_model,
            'ws_aorg_secure_channel_status' => $secure_channel_status,
            'ws_aorg_secure_contact_tools' => $secure_contact_tools,
            'ws_aorg_mailing_address'      => ws_matrix_optional( $org, 'mailing_address' ),
            'ws_aorg_income_screening'     => $income_screening,
            'ws_aorg_eligibility_status'   => $eligibility_status,
            'ws_aorg_is_nationwide'        => ws_matrix_required( $org, 'is_nationwide' ),
            'ws_aorg_anonymous_pre_consult_status' => $anonymous_pre_consult_status,
            'ws_aorg_has_attorneys'        => $has_attorneys,
            'ws_aorg_whistleblower_fit'    => $whistleblower_fit,
            'ws_aorg_service_depth'        => $service_depth,
            'ws_aorg_intake_commitment_class' => $intake_commitment_class,
            'ws_aorg_whistleblower_scope'  => ws_matrix_required( $org, 'whistleblower_scope' ),
        ];

        foreach ( $meta as $key => $value ) {
            if ( $value !== '' ) {
                update_post_meta( $post_id, $key, $value );
            }
        }

        ws_matrix_write_repeater(
            $post_id,
            'ws_aorg_phones',
            $phone_channels,
            [
                'type'   => 'ws_aorg_phone_type',
                'number' => 'ws_aorg_phone_number',
            ]
        );

        ws_matrix_write_repeater(
            $post_id,
            'ws_aorg_emails',
            $email_channels,
            [
                'type'    => 'ws_aorg_email_type',
                'address' => 'ws_aorg_email_address',
            ]
        );

        // ── Taxonomies ───────────────────────────────────────────────────────

        // Organization model (single slug; stored in ws_aorg_type taxonomy).
        if ( $organization_model !== '' ) {
            ws_matrix_assign_terms_strict( $post_id, [ $organization_model ], 'ws_aorg_type' );
        }

        // Cost models (array of slugs — must match ws_aorg_cost_model seeder).
        ws_matrix_assign_terms_strict( $post_id, ws_matrix_required_array( $org, 'cost_models' ), 'ws_aorg_cost_model' );

        // Protected disclosures (array of slugs — must match ws_protected_disclosure seeder).
        ws_matrix_assign_terms_strict(
            $post_id,
            ws_matrix_required_array( $org, 'protected_disclosures' ),
            'ws_protected_disclosure'
        );

        // Optional disclosure targets (array of slugs).
        ws_matrix_assign_terms_strict(
            $post_id,
            ws_matrix_required_array( $org, 'disclosure_targets' ),
            'ws_disclosure_target'
        );

        // Case stages (array of slugs).
        ws_matrix_assign_terms_strict( $post_id, ws_matrix_required_array( $org, 'case_stages' ), 'ws_case_stage' );

        // Services offered (array of ws_aorg_service slugs — must match seeder).
        ws_matrix_assign_terms_strict( $post_id, ws_matrix_required_array( $org, 'services' ), 'ws_aorg_service' );

        // Employment sectors (array of ws_employment_sector slugs).
        ws_matrix_assign_terms_strict( $post_id, ws_matrix_required_array( $org, 'employment_sectors' ), 'ws_employment_sector' );

        // Protected classes (array of ws_protected_class slugs).
        ws_matrix_assign_terms_strict( $post_id, ws_matrix_required_array( $org, 'protected_classes' ), 'ws_protected_class' );

        // Language: English (all seeded national orgs operate in English).
        $default_languages = [ 'english' ];
        if ( ! empty( $default_languages ) ) {
            ws_matrix_assign_terms_strict( $post_id, $default_languages, 'ws_language' );
        }

        // Jurisdiction: US.
        $jx_result = wp_set_object_terms( $post_id, $us_term_id, 'ws_jurisdiction' );
        if ( is_wp_error( $jx_result ) ) {
            ws_matrix_error(
                "Failed assigning US jurisdiction on post {$post_id}: " . $jx_result->get_error_message()
            );
        }

        // ── Seeder stamp ─────────────────────────────────────────────────────
        update_post_meta( $post_id, 'ws_matrix_source', 'matrix-assist-orgs' );
    }
}


// ── Gate ──────────────────────────────────────────────────────────────────────

add_action( 'admin_init', function() {
    if ( get_option( 'ws_seeded_assist_org_matrix' ) !== '1.0.0' ) {
        try {
            ws_seed_assist_org_matrix();
        } catch ( RuntimeException $e ) {
            wp_die(
                esc_html( '[ws-core assist-org matrix] ' . $e->getMessage() ),
                esc_html__( 'Assist-org matrix seed failed', 'whistleblowershield' ),
                [ 'response' => 500 ]
            );
        }

        update_option( 'ws_seeded_assist_org_matrix', '1.0.0' );
    }
} );
