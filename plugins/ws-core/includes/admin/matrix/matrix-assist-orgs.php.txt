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
        'lawyers_url' => '',
        'contact_url' => 'https://whistleblower.org/contact-us/',
        'social_presence' => 1,
        'social_links' => [
            [ 'platform' => 'facebook', 'url' => 'https://www.facebook.com/GovernmentAccountabilityProject', 'is_contact' => 0 ],
            [ 'platform' => 'twitter', 'url' => 'https://twitter.com/GovAcctProj', 'is_contact' => 0 ],
            [ 'platform' => 'linked-in', 'url' => 'https://www.linkedin.com/company/government-accountability-project/', 'is_contact' => 0 ],
            [ 'platform' => 'bluesky', 'url' => 'https://bsky.app/profile/govacctproj.bsky.social', 'is_contact' => 0 ],
            [ 'platform' => 'instagram', 'url' => 'https://www.instagram.com/govacctproj/', 'is_contact' => 0 ],
        ],
        'phones' => [
            [ 'type' => 'headquarters', 'number' => '(202) 457-0034', 'url' => 'https://whistleblower.org/contact-us/' ],
        ],
        'emails' => [
            [ 'type' => 'general', 'address' => 'info@whistleblower.org', 'url' => 'https://whistleblower.org/contact-us/' ],
        ],
        'secure_channel' => 0,
        'secure_channels' => [],
        'mailing_address' => 'Government Accountability Project, 1612 K St NW, Suite 808, Washington, DC 20006-2802',
        'income_screening' => 'not-required',
        'organization_model' => 'nonprofit',
        'cost_models' => [ 'pro-bono' ],
        'languages' => [ 'english' ],
        'is_nationwide' => 1,
        'anonymous_pre_consult_status' => 1,
        'has_attorneys' => 1,
        'attorney_role' => 'direct-representation',
        'legal_representation_status' => 'available',
        'whistleblower_scope' => 3,
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
        'common_name' => 'NSC',
        'official_name' => 'National Security Counselors',
        'official_homepage_url' => 'https://www.nationalsecuritylaw.org',
        'intake_url' => 'https://www.nationalsecuritylaw.org/get-help',
        'lawyers_url' => '',
        'contact_url' => 'https://www.nationalsecuritylaw.org/get-help',
        'social_presence' => 0,
        'social_links' => [],
        'phones' => [
            [ 'type' => 'general', 'number' => '501-301-4672', 'url' => 'https://www.nationalsecuritylaw.org/get-help' ],
        ],
        'emails' => [
            [ 'type' => 'general', 'address' => 'Kel@nationalsecuritylaw.org', 'url' => 'https://www.nationalsecuritylaw.org/get-help' ],
            [ 'type' => 'media', 'address' => 'Media@nationalsecuritylaw.org', 'url' => 'https://www.nationalsecuritylaw.org/get-help' ],
        ],
        'secure_channel' => 1,
        'secure_channels' => [
            [
                'tool' => 'signal',
                'url' => 'https://www.nationalsecuritylaw.org/get-help',
                'label' => 'Signal handle instructions',
                'class' => 'two-way-support',
            ],
        ],
        'mailing_address' => '',
        'income_screening' => 'not-required',
        'organization_model' => 'legal-aid',
        'cost_models' => [ 'sliding-scale' ],
        'languages' => [ 'english' ],
        'is_nationwide' => 1,
        'anonymous_pre_consult_status' => 0,
        'has_attorneys' => 1,
        'attorney_role' => 'direct-representation',
        'legal_representation_status' => 'limited',
        'whistleblower_scope' => 2,
        'service_depth' => 'direct-representation',
        'intake_commitment_class' => 'personal-help-request',
        'eligibility_status' => 'restricted',
        'services' => [
            'legal-rep',
            'consultation',
            'doc-review',
            'advocacy',
        ],
        'employment_sectors' => [
            'federal-employee',
            'government-contractor',
            'military-defense',
        ],
        'protected_classes' => [
            'federal-employee',
            'contractor-gig',
            'military-personnel',
        ],
        'protected_disclosures' => [
            'national-security',
            'military-defense-reporting',
            'privacy-data-integrity',
            'public-corruption-ethics',
        ],
        'disclosure_targets' => [
            'agency-federal',
            'legislative-federal',
            'court-filing',
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
        'intake_url' => 'https://report-fraud-now.info/intake/',
        'lawyers_url' => 'https://www.whistleblowers.org/find-a-whisteblower-attorney/',
        'contact_url' => 'https://www.whistleblowers.org/contact-us/',
        'social_presence' => 1,
        'social_links' => [
            [ 'platform' => 'linked-in', 'url' => 'https://www.linkedin.com/company/national-whistleblowers-center/', 'is_contact' => 0 ],
            [ 'platform' => 'twitter', 'url' => 'https://x.com/StopFraud', 'is_contact' => 0 ],
            [ 'platform' => 'youtube', 'url' => 'https://www.youtube.com/@NationalWhistleblowerCenterDC', 'is_contact' => 0 ],
            [ 'platform' => 'facebook', 'url' => 'https://www.facebook.com/NationalWhistleblowerCenter', 'is_contact' => 0 ],
            [ 'platform' => 'instagram', 'url' => 'https://www.instagram.com/nationalwhistleblowercenter/', 'is_contact' => 0 ],
        ],
        'phones' => [
            [ 'type' => 'headquarters', 'number' => '(202) 342-1903', 'url' => 'https://www.whistleblowers.org/attorney-referral-program/' ],
        ],
        'emails' => [
            [ 'type' => 'general', 'address' => 'info@whistleblowers.org', 'url' => 'https://www.whistleblowers.org/contact-us/' ],
        ],
        'secure_channel' => 0,
        'secure_channels' => [],
        'mailing_address' => 'National Whistleblower Center, 1800 M Street NW #33888, Washington, DC 20033',
        'income_screening' => 'not-required',
        'organization_model' => 'nonprofit',
        'cost_models' => [ 'free' ],
        'languages' => [ 'english' ],
        'is_nationwide' => 1,
        'anonymous_pre_consult_status' => 1,
        'has_attorneys' => 1,
        'attorney_role' => 'referral-panel',
        'legal_representation_status' => 'referral-only',
        'whistleblower_scope' => 3,
        'service_depth' => 'referral-only',
        'intake_commitment_class' => 'screening-form',
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
        'intake_url' => 'https://whistlebloweraid.org/become-a-whistleblower/signal/',
        'lawyers_url' => '',
        'contact_url' => 'https://whistlebloweraid.org/become-a-whistleblower/',
        'social_presence' => 1,
        'social_links' => [
            [ 'platform' => 'linked-in', 'url' => 'https://www.linkedin.com/company/whistleblower-aid', 'is_contact' => 0 ],
        ],
        'phones' => [ [ 'type' => 'secure', 'number' => '201-773-1371', 'url' => 'https://whistlebloweraid.org/become-a-whistleblower/signal/' ] ],
        'emails' => [ [ 'type' => 'media', 'address' => 'press@whistlebloweraid.org', 'url' => 'https://whistlebloweraid.org' ] ],
        'secure_channel' => 1,
        'secure_channels' => [
            [
                'tool' => 'signal',
                'url' => 'https://whistlebloweraid.org/become-a-whistleblower/signal/',
                'label' => 'Signal contact instructions',
                'class' => 'two-way-support',
            ],
            [
                'tool' => 'securedrop',
                'url' => 'https://whistlebloweraid.org/become-a-whistleblower/securedrop/',
                'label' => 'SecureDrop submission',
                'class' => 'tip-drop',
            ],
        ],
        'mailing_address' => 'Whistleblower Aid, 1250 Connecticut Ave NW, Suite 700, Washington, DC 20036',
        'income_screening' => 'not-required',
        'organization_model' => 'legal-aid',
        'cost_models' => [ 'pro-bono' ],
        'languages' => [ 'english' ],
        'is_nationwide' => 1,
        'anonymous_pre_consult_status' => 1,
        'has_attorneys' => 1,
        'attorney_role' => 'direct-representation',
        'legal_representation_status' => 'available',
        'whistleblower_scope' => 3,
        'service_depth' => 'direct-representation',
        'intake_commitment_class' => 'personal-help-request',
        'eligibility_status' => 'screening-required',
        'services' => [
            'legal-rep',
            'consultation',
            'advocacy',
            'media',
            'retaliation',
            'secure-drop',
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
        'common_name' => 'TAF',
        'official_name' => 'The Anti-Fraud Coalition',
        'official_homepage_url' => 'https://www.taf.org',
        'intake_url' => 'https://www.taf.org/member-directory/',
        'lawyers_url' => 'https://www.taf.org/member-directory/',
        'contact_url' => 'https://member.taf.org/',
        'social_presence' => 1,
        'social_links' => [
            [ 'platform' => 'linked-in', 'url' => 'https://www.linkedin.com/company/tafcoalition', 'is_contact' => 0 ],
            [ 'platform' => 'twitter', 'url' => 'https://twitter.com/TAFCoalition', 'is_contact' => 0 ],
        ],
        'phones' => [
            [ 'type' => 'headquarters', 'number' => '(202) 296-4826', 'url' => 'https://member.taf.org/' ],
        ],
        'emails' => [
            [ 'type' => 'general', 'address' => 'digital@taf.org', 'url' => 'https://member.taf.org/' ],
        ],
        'secure_channel' => 0,
        'secure_channels' => [],
        'mailing_address' => 'The Anti-Fraud Coalition, 1220 19th St NW, Ste 501, Washington, DC 20036',
        'income_screening' => 'not-required',
        'organization_model' => 'advocacy',
        'cost_models' => [ 'free' ],
        'languages' => [ 'english' ],
        'is_nationwide' => 1,
        'anonymous_pre_consult_status' => 0,
        'has_attorneys' => 1,
        'attorney_role' => 'referral-panel',
        'legal_representation_status' => 'referral-only',
        'whistleblower_scope' => 2,
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
        'intake_url' => 'https://www.whistleblowersofamerica.org/learn-more/peer-support',
        'lawyers_url' => '',
        'contact_url' => 'https://www.whistleblowersofamerica.org/contact-us',
        'social_presence' => 1,
        'social_links' => [
            [ 'platform' => 'facebook', 'url' => 'https://www.facebook.com/whistleblowersofamerica', 'is_contact' => 0 ],
            [ 'platform' => 'linked-in', 'url' => 'https://www.linkedin.com/company/whistleblowersofamerica/', 'is_contact' => 0 ],
            [ 'platform' => 'instagram', 'url' => 'https://www.instagram.com/whistleblowersofamerica', 'is_contact' => 0 ],
            [ 'platform' => 'twitter', 'url' => 'https://twitter.com/WhistleP2P', 'is_contact' => 0 ],
            [ 'platform' => 'youtube', 'url' => 'https://youtube.com/@whistleblowersofamerica', 'is_contact' => 0 ],
            [ 'platform' => 'bluesky', 'url' => 'https://bsky.app/profile/whistlep2p.bsky.social', 'is_contact' => 0 ],
        ],
        'phones' => [
            [ 'type' => 'headquarters', 'number' => '202-643-1956', 'url' => 'https://www.whistleblowersofamerica.org/contact-us' ],
        ],
        'emails' => [
            [ 'type' => 'general', 'address' => 'info@whistleblowersofamerica.org', 'url' => 'https://www.whistleblowersofamerica.org/contact-us' ],
            [ 'type' => 'support', 'address' => 'peers@whistleblowersofamerica.org', 'url' => 'https://www.whistleblowersofamerica.org/contact-us' ],
        ],
        'secure_channel' => 0,
        'secure_channels' => [],
        'mailing_address' => 'Whistleblowers of America, 11130 Lillian Highway, Pensacola, FL 32506',
        'income_screening' => 'not-required',
        'organization_model' => 'advocacy',
        'cost_models' => [ 'free', 'fee-for-service' ],
        'languages' => [ 'english' ],
        'is_nationwide' => 1,
        'anonymous_pre_consult_status' => 1,
        'has_attorneys' => 0,
        'attorney_role' => '',
        'legal_representation_status' => 'not-available',
        'whistleblower_scope' => 3,
        'service_depth' => 'peer-support',
        'intake_commitment_class' => 'peer-support-request',
        'eligibility_status' => 'open-to-public',
        'services' => [
            'retaliation',
            'advocacy',
            'education-training',
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
        'common_name' => 'NELP',
        'official_name' => 'National Employment Law Project',
        'official_homepage_url' => 'https://www.nelp.org',
        'intake_url' => '',
        'lawyers_url' => '',
        'contact_url' => 'https://www.nelp.org/about-us/contact-us/',
        'social_presence' => 1,
        'social_links' => [
            [ 'platform' => 'facebook', 'url' => 'https://www.facebook.com/NationalEmploymentLawProject', 'is_contact' => 0 ],
            [ 'platform' => 'instagram', 'url' => 'https://www.instagram.com/nationalemploymentlawproject/', 'is_contact' => 0 ],
            [ 'platform' => 'bluesky', 'url' => 'https://bsky.app/profile/nelp.org', 'is_contact' => 0 ],
            [ 'platform' => 'linked-in', 'url' => 'https://www.linkedin.com/company/national-employment-law-project', 'is_contact' => 0 ],
        ],
        'phones' => [
            [ 'type' => 'headquarters', 'number' => '(212) 285-3025', 'url' => 'https://www.nelp.org/about-us/contact-us/' ],
        ],
        'emails' => [
            [ 'type' => 'general', 'address' => 'nelp@nelp.org', 'url' => 'https://www.nelp.org/about-us/contact-us/' ],
            [ 'type' => 'media', 'address' => 'press@nelp.org', 'url' => 'https://www.nelp.org/about-us/contact-us/' ],
            [ 'type' => 'other', 'address' => 'development@nelp.org', 'url' => 'https://www.nelp.org/about-us/contact-us/' ],
        ],
        'secure_channel' => 0,
        'secure_channels' => [],
        'mailing_address' => 'National Office: National Employment Law Project, PO Box 1779, New York, NY 10008',
        'income_screening' => 'not-required',
        'organization_model' => 'advocacy',
        'cost_models' => [ 'free' ],
        'languages' => [ 'english' ],
        'is_nationwide' => 1,
        'anonymous_pre_consult_status' => 0,
        'has_attorneys' => 0,
        'attorney_role' => 'policy-only',
        'legal_representation_status' => 'not-available',
        'whistleblower_scope' => 1,
        'service_depth' => 'information-only',
        'intake_commitment_class' => 'general-contact-only',
        'eligibility_status' => 'open-to-public',
        'services' => [
            'advocacy',
            'education-training',
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
        'intake_url' => '',
        'lawyers_url' => 'https://engagement.nela.org/NELA/findalawyer.aspx',
        'contact_url' => 'https://www.nela.org/about/staff/',
        'social_presence' => 1,
        'social_links' => [
            [ 'platform' => 'linked-in', 'url' => 'https://www.linkedin.com/company/nela_hq', 'is_contact' => 0 ],
            [ 'platform' => 'facebook', 'url' => 'https://www.facebook.com/NELAHQ/', 'is_contact' => 0 ],
            [ 'platform' => 'twitter', 'url' => 'https://twitter.com/NELA_HQ', 'is_contact' => 0 ],
        ],
        'phones' => [
            [ 'type' => 'headquarters', 'number' => '(415) 296-7629', 'url' => 'https://www.nela.org/about/staff/' ],
            [ 'type' => 'regional', 'number' => '(202) 898-2880', 'url' => 'https://www.nela.org/about/staff/' ],
        ],
        'emails' => [
            [ 'type' => 'general', 'address' => 'nelahq@nelahq.org', 'url' => 'https://www.nela.org/about/staff/' ],
        ],
        'secure_channel' => 0,
        'secure_channels' => [],
        'mailing_address' => '1800 Sutter Street, Suite 210, Concord, CA 94520',
        'income_screening' => 'not-required',
        'organization_model' => 'bar-program',
        'cost_models' => [ 'fee-for-service' ],
        'languages' => [ 'english' ],
        'is_nationwide' => 1,
        'anonymous_pre_consult_status' => 0,
        'has_attorneys' => 0,
        'attorney_role' => 'referral-panel',
        'legal_representation_status' => 'referral-only',
        'whistleblower_scope' => 1,
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
        'intake_url' => '',
        'lawyers_url' => 'https://www.lsc.gov/about-lsc/what-legal-aid/get-legal-help',
        'contact_url' => 'https://www.lsc.gov/contact-us',
        'social_presence' => 1,
        'social_links' => [
            [ 'platform' => 'facebook', 'url' => 'https://www.facebook.com/LegalServicesCorporation', 'is_contact' => 0 ],
            [ 'platform' => 'twitter', 'url' => 'https://twitter.com/lsctweets', 'is_contact' => 0 ],
            [ 'platform' => 'linked-in', 'url' => 'https://www.linkedin.com/company/legal-services-corporation', 'is_contact' => 0 ],
            [ 'platform' => 'youtube', 'url' => 'https://www.youtube.com/user/LegalServicesCorp', 'is_contact' => 0 ],
            [ 'platform' => 'bluesky', 'url' => 'https://bsky.app/profile/legalservicescorp.bsky.social', 'is_contact' => 0 ],
            [ 'platform' => 'instagram', 'url' => 'https://www.instagram.com/legalservicescorp/', 'is_contact' => 0 ],
        ],
        'phones' => [
            [ 'type' => 'headquarters', 'number' => '(202) 295-1500', 'url' => 'https://www.lsc.gov/contact-us' ],
        ],
        'emails' => [],
        'secure_channel' => 0,
        'secure_channels' => [],
        'mailing_address' => 'Legal Services Corporation, 3333 K Street NW, Washington, DC 20007',
        'income_screening' => 'required',
        'organization_model' => 'legal-aid',
        'cost_models' => [ 'free' ],
        'languages' => [ 'english' ],
        'is_nationwide' => 1,
        'anonymous_pre_consult_status' => 0,
        'has_attorneys' => 0,
        'attorney_role' => 'referral-panel',
        'legal_representation_status' => 'referral-only',
        'whistleblower_scope' => 1,
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
        'lawyers_url' => '',
        'contact_url' => 'https://www.nlada.org/contact-us',
        'social_presence' => 1,
        'social_links' => [
            [ 'platform' => 'twitter', 'url' => 'https://twitter.com/nlada', 'is_contact' => 0 ],
            [ 'platform' => 'facebook', 'url' => 'https://www.facebook.com/nlada.org/timeline/', 'is_contact' => 0 ],
            [ 'platform' => 'youtube', 'url' => 'https://www.youtube.com/channel/UCCq0o9Ieh2pJ_jcDA2Z3wag', 'is_contact' => 0 ],
            [ 'platform' => 'instagram', 'url' => 'https://www.instagram.com/nlada_dc', 'is_contact' => 0 ],
            [ 'platform' => 'bluesky', 'url' => 'https://bsky.app/profile/nlada.bsky.social', 'is_contact' => 0 ],
        ],
        'phones' => [
            [ 'type' => 'headquarters', 'number' => '(202) 452-0620', 'url' => 'https://www.nlada.org/contact-us' ],
        ],
        'emails' => [],
        'secure_channel' => 0,
        'secure_channels' => [],
        'mailing_address' => 'National Legal Aid and Defender Association, 1901 Pennsylvania Avenue NW, Suite 500, Washington, DC 20006',
        'income_screening' => 'not-required',
        'organization_model' => 'advocacy',
        'cost_models' => [ 'free' ],
        'languages' => [ 'english' ],
        'is_nationwide' => 1,
        'anonymous_pre_consult_status' => 0,
        'has_attorneys' => 0,
        'attorney_role' => 'policy-only',
        'legal_representation_status' => 'not-available',
        'whistleblower_scope' => 1,
        'service_depth' => 'referral-only',
        'intake_commitment_class' => 'information-only',
        'eligibility_status' => 'referral-only',
        'services' => [
            'referral',
            'advocacy',
            'education-training',
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
        'common_name' => 'ABA',
        'official_name' => 'American Bar Association — Find Legal Help',
        'official_homepage_url' => 'https://www.americanbar.org/groups/legal_services/',
        'intake_url' => '',
        'lawyers_url' => 'https://www.americanbar.org/groups/legal_services/flh-home/',
        'contact_url' => 'https://www.americanbar.org/about_the_aba/contact/',
        'social_presence' => 1,
        'social_links' => [
            [ 'platform' => 'linked-in', 'url' => 'https://www.linkedin.com/company/american-bar-association', 'is_contact' => 0 ],
            [ 'platform' => 'facebook', 'url' => 'https://facebook.com/AmericanBarAssociation/', 'is_contact' => 0 ],
        ],
        'phones' => [
            [ 'type' => 'headquarters', 'number' => '(800) 285-2221', 'url' => 'https://www.americanbar.org/about_the_aba/contact/' ],
            [ 'type' => 'headquarters', 'number' => '(312) 988-5000', 'url' => 'https://www.americanbar.org/about_the_aba/contact/' ],
            [ 'type' => 'regional', 'number' => '(202) 662-1000', 'url' => 'https://www.americanbar.org/about_the_aba/contact/' ],
        ],
        'emails' => [
            [ 'type' => 'general', 'address' => 'service@americanbar.org', 'url' => 'https://www.americanbar.org/about_the_aba/contact/' ],
            [ 'type' => 'media', 'address' => 'abanews@americanbar.org', 'url' => 'https://www.americanbar.org/about_the_aba/contact/' ],
        ],
        'secure_channel' => 0,
        'secure_channels' => [],
        'mailing_address' => 'American Bar Association, 321 N Clark St, Chicago, IL 60654',
        'income_screening' => 'required',
        'organization_model' => 'bar-program',
        'cost_models' => [ 'fee-for-service' ],
        'languages' => [ 'english' ],
        'is_nationwide' => 1,
        'anonymous_pre_consult_status' => 0,
        'has_attorneys' => 0,
        'attorney_role' => 'referral-panel',
        'legal_representation_status' => 'referral-only',
        'whistleblower_scope' => 1,
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
        'lawyers_url' => '',
        'contact_url' => 'https://peer.org/about-us/contact-us/',
        'social_presence' => 1,
        'social_links' => [
            [ 'platform' => 'facebook', 'url' => 'https://www.facebook.com/peerorg', 'is_contact' => 0 ],
            [ 'platform' => 'instagram', 'url' => 'https://www.instagram.com/follow_peerorg/', 'is_contact' => 0 ],
            [ 'platform' => 'linked-in', 'url' => 'https://www.linkedin.com/company/public-employees-for-environmental-responsibility/', 'is_contact' => 0 ],
            [ 'platform' => 'youtube', 'url' => 'https://www.youtube.com/c/publicemployeesforenvironmentalresponsibility', 'is_contact' => 0 ],
            [ 'platform' => 'bluesky', 'url' => 'https://bsky.app/profile/peer.org', 'is_contact' => 0 ],
        ],
        'phones' => [
            [ 'type' => 'headquarters', 'number' => '(202) 265-7337', 'url' => 'https://peer.org/contact-us/' ],
        ],
        'emails' => [
            [ 'type' => 'general', 'address' => 'info@peer.org', 'url' => 'https://peer.org/contact-us/' ],
        ],
        'secure_channel' => 0,
        'secure_channels' => [],
        'mailing_address' => 'Public Employees for Environmental Responsibility, 962 Wayne Ave, Suite 610, Silver Spring, MD 20910',
        'income_screening' => 'not-required',
        'organization_model' => 'nonprofit',
        'cost_models' => [ 'pro-bono' ],
        'languages' => [ 'english' ],
        'is_nationwide' => 1,
        'anonymous_pre_consult_status' => 1,
        'has_attorneys' => 1,
        'attorney_role' => 'direct-representation',
        'legal_representation_status' => 'limited',
        'whistleblower_scope' => 2,
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
        'lawyers_url' => '',
        'contact_url' => 'https://thesignalsnetwork.org/contact/',
        'social_presence' => 1,
        'social_links' => [
            [ 'platform' => 'facebook', 'url' => 'https://www.facebook.com/TheSignalsNetwork/', 'is_contact' => 0 ],
            [ 'platform' => 'youtube', 'url' => 'https://www.youtube.com/@thesignalsnetwork', 'is_contact' => 0 ],
            [ 'platform' => 'linked-in', 'url' => 'https://www.linkedin.com/company/the-signals-network', 'is_contact' => 0 ],
        ],
        'phones' => [],
        'emails' => [
            [ 'type' => 'general', 'address' => 'info@thesignalsnetwork.org', 'url' => 'https://thesignalsnetwork.org/contact/' ],
            [ 'type' => 'secure', 'address' => 'protect@thesignalsnetwork.org', 'url' => 'https://thesignalsnetwork.org/contact/' ],
        ],
        'secure_channel' => 1,
        'secure_channels' => [
            [
                'tool' => 'protonmail',
                'url' => 'https://thesignalsnetwork.org/contact/',
                'label' => 'Proton Mail secure support email',
                'class' => 'two-way-support',
            ],
            [
                'tool' => 'pgp-email',
                'url' => 'https://thesignalsnetwork.org/contact/',
                'label' => 'PGP key for secure support email',
                'class' => 'two-way-support',
            ],
            [
                'tool' => 'signal',
                'url' => 'https://thesignalsnetwork.org/contact/',
                'label' => 'Signal handle instructions',
                'class' => 'two-way-support',
            ],
        ],
        'mailing_address' => 'The Signals Network, 416 Florida Ave NW #26152, Washington, DC 20001',
        'income_screening' => 'not-required',
        'organization_model' => 'nonprofit',
        'cost_models' => [ 'free' ],
        'languages' => [ 'english' ],
        'is_nationwide' => 1,
        'anonymous_pre_consult_status' => 1,
        'has_attorneys' => 1,
        'attorney_role' => 'consultation-only',
        'legal_representation_status' => 'limited',
        'whistleblower_scope' => 3,
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
    [
        'common_name' => 'WtTF',
        'official_name' => 'Walk the Talk Foundation',
        'official_homepage_url' => 'https://walkthetalkfoundation.org',
        'intake_url' => 'https://walkthetalkfoundation.org/contact-us/',
        'lawyers_url' => '',
        'contact_url' => 'https://walkthetalkfoundation.org/contact-us/',
        'social_presence' => 0,
        'social_links' => [],
        'phones' => [],
        'emails' => [
            [ 'type' => 'general', 'address' => 'info@walkthetalkfoundation.org', 'url' => 'https://walkthetalkfoundation.org/contact-us/' ],
        ],
        'secure_channel' => 0,
        'secure_channels' => [],
        'mailing_address' => 'Walk the Talk Foundation, Wilmington, DE',
        'income_screening' => 'not-required',
        'organization_model' => 'nonprofit',
        'cost_models' => [ 'free' ],
        'languages' => [ 'english' ],
        'is_nationwide' => 1,
        'anonymous_pre_consult_status' => 0,
        'has_attorneys' => 1,
        'attorney_role' => 'consultation-only',
        'legal_representation_status' => 'limited',
        'whistleblower_scope' => 3,
        'service_depth' => 'ongoing-support',
        'intake_commitment_class' => 'personal-help-request',
        'eligibility_status' => 'open-to-public',
        'services' => [
            'consultation',
            'advocacy',
            'media',
            'retaliation',
            'peer-support',
        ],
        'employment_sectors' => [
            'federal-employee',
            'military-defense',
        ],
        'protected_classes' => [
            'military-personnel',
            'federal-employee',
        ],
        'protected_disclosures' => [
            'military-defense-reporting',
            'public-corruption-ethics',
            'general-wrongdoing',
        ],
        'disclosure_targets' => [
            'internal-management',
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
        'common_name' => 'Psst',
        'official_name' => 'Psst',
        'official_homepage_url' => 'https://psst.org',
        'intake_url' => 'https://psst.org/safe',
        'lawyers_url' => '',
        'contact_url' => 'https://psst.org/contact',
        'social_presence' => 1,
        'social_links' => [
            [ 'platform' => 'instagram', 'url' => 'https://www.instagram.com/psst_org', 'is_contact' => 0 ],
            [ 'platform' => 'threads', 'url' => 'https://www.threads.net/@psst_org?xmt=AQGzV2-tp8TVwRyMH2FSy8s9TZFFL0RGsDRZgkV0BVW6_EM', 'is_contact' => 0 ],
            [ 'platform' => 'bluesky', 'url' => 'https://bsky.app/profile/psst-org.bsky.social', 'is_contact' => 0 ],
            [ 'platform' => 'twitter', 'url' => 'https://x.com/psst_org', 'is_contact' => 0 ],
        ],
        'phones' => [],
        'emails' => [
            [ 'type' => 'support', 'address' => 'psst@psst.org', 'url' => 'https://psst.org/contact' ],
        ],
        'secure_channel' => 1,
        'secure_channels' => [
            [
                'tool' => 'encrypted-web-form',
                'url' => 'https://safe.psst.org',
                'label' => 'Psst Safe encrypted intake',
                'class' => 'two-way-support',
            ],
            [
                'tool' => 'signal',
                'url' => 'https://psst.org/faqs',
                'label' => 'Urgent Psst Legal Signal contact instructions',
                'class' => 'two-way-support',
            ],
        ],
        'mailing_address' => '',
        'income_screening' => 'not-required',
        'organization_model' => 'nonprofit',
        'cost_models' => [ 'pro-bono' ],
        'languages' => [ 'english' ],
        'is_nationwide' => 1,
        'anonymous_pre_consult_status' => 1,
        'has_attorneys' => 1,
        'attorney_role' => 'consultation-only',
        'legal_representation_status' => 'limited',
        'whistleblower_scope' => 3,
        'service_depth' => 'ongoing-support',
        'intake_commitment_class' => 'personal-help-request',
        'eligibility_status' => 'restricted',
        'services' => [
            'legal-rep',
            'consultation',
            'advocacy',
            'media',
            'mental-health',
            'peer-support',
            'secure-drop',
        ],
        'employment_sectors' => [
            'private-sector',
            'federal-employee',
        ],
        'protected_classes' => [
            'corporate-staff',
            'federal-employee',
        ],
        'protected_disclosures' => [
            'general-wrongdoing',
            'public-corruption-ethics',
            'cybersecurity-disclosure',
        ],
        'disclosure_targets' => [
            'internal-management',
            'attorney-counsel',
            'public-media',
            'agency-federal',
            'legislative-federal',
        ],
        'case_stages' => [
            'pre-report',
            'post-report',
            'retaliation-active',
        ],
    ],
    [
        'common_name' => 'AIWI',
        'official_name' => 'AI Whistleblower Initiative',
        'official_homepage_url' => 'https://aiwi.org',
        'intake_url' => 'https://aiwi.org/third-opinion/',
        'lawyers_url' => 'https://aiwi.org/ai-whistleblower-defense-fund/',
        'contact_url' => 'https://aiwi.org/contact/',
        'social_presence' => 1,
        'social_links' => [
            [ 'platform' => 'twitter', 'url' => 'https://x.com/aiwi_official', 'is_contact' => 0 ],
            [ 'platform' => 'bluesky', 'url' => 'https://bsky.app/profile/aiwi-official.bsky.social', 'is_contact' => 0 ],
            [ 'platform' => 'linked-in', 'url' => 'https://www.linkedin.com/company/aiwi', 'is_contact' => 0 ],
            [ 'platform' => 'substack', 'url' => 'https://aiwhistleblowerinitiative.substack.com/', 'is_contact' => 0 ],
        ],
        'phones' => [],
        'emails' => [
            [ 'type' => 'general', 'address' => 'hello@aiwi.org', 'url' => 'https://aiwi.org/contact/' ],
        ],
        'secure_channel' => 1,
        'secure_channels' => [
            [
                'tool' => 'globaleaks',
                'url' => 'http://ltinurakxfcvpfucikccapckjcro4vuymzsyzosw6tt4tf7epzfcx3id.onion/',
                'label' => 'Tor-based anonymous contact form',
                'class' => 'two-way-support',
            ],
            [
                'tool' => 'pgp-email',
                'url' => 'https://aiwi.org/contact/',
                'label' => 'Encrypted email using published PGP key',
                'class' => 'two-way-support',
            ],
        ],
        'mailing_address' => '',
        'income_screening' => 'not-required',
        'organization_model' => 'nonprofit',
        'cost_models' => [ 'pro-bono' ],
        'languages' => [ 'english' ],
        'is_nationwide' => 1,
        'anonymous_pre_consult_status' => 1,
        'has_attorneys' => 0,
        'attorney_role' => 'referral-panel',
        'legal_representation_status' => 'referral-only',
        'whistleblower_scope' => 2,
        'service_depth' => 'warm-handoff',
        'intake_commitment_class' => 'personal-help-request',
        'eligibility_status' => 'restricted',
        'services' => [
            'consultation',
            'referral',
            'advocacy',
            'financial',
            'education-training',
        ],
        'employment_sectors' => [
            'technology-sector',
        ],
        'protected_classes' => [
            'corporate-staff',
        ],
        'protected_disclosures' => [
            'cybersecurity-disclosure',
            'general-wrongdoing',
        ],
        'disclosure_targets' => [
            'internal-management',
            'attorney-counsel',
            'agency-federal',
            'public-nonprofit',
        ],
        'case_stages' => [
            'pre-report',
            'post-report',
            'retaliation-active',
        ],
    ],
    [
        'common_name' => 'LASST',
        'official_name' => 'Legal Advocates for Safe Science and Technology',
        'official_homepage_url' => 'https://lasst.org',
        'intake_url' => 'https://lasst.org/ai-safety-whistleblower-legal-defense-fund/',
        'lawyers_url' => 'https://lasst.org/ai-safety-whistleblower-legal-defense-fund/',
        'contact_url' => 'https://lasst.org/ai-safety-whistleblower-legal-defense-fund/',
        'social_presence' => 0,
        'social_links' => [],
        'phones' => [],
        'emails' => [
            [ 'type' => 'secure', 'address' => 'lasst.ldf@protonmail.com', 'url' => 'https://lasst.org/ai-safety-whistleblower-legal-defense-fund/' ],
        ],
        'secure_channel' => 1,
        'secure_channels' => [
            [
                'tool' => 'protonmail',
                'url' => 'https://lasst.org/ai-safety-whistleblower-legal-defense-fund/',
                'label' => 'ProtonMail legal defense fund contact instructions',
                'class' => 'two-way-support',
            ],
        ],
        'mailing_address' => '',
        'income_screening' => 'not-required',
        'organization_model' => 'legal-aid',
        'cost_models' => [ 'pro-bono' ],
        'languages' => [ 'english' ],
        'is_nationwide' => 1,
        'anonymous_pre_consult_status' => 1,
        'has_attorneys' => 1,
        'attorney_role' => 'direct-representation',
        'legal_representation_status' => 'limited',
        'whistleblower_scope' => 2,
        'service_depth' => 'direct-representation',
        'intake_commitment_class' => 'personal-help-request',
        'eligibility_status' => 'restricted',
        'services' => [
            'legal-rep',
            'consultation',
            'referral',
            'financial',
            'advocacy',
        ],
        'employment_sectors' => [
            'technology-sector',
        ],
        'protected_classes' => [
            'corporate-staff',
            'contractor-gig',
            'federal-employee',
            'associates-of-whistleblower',
        ],
        'protected_disclosures' => [
            'cybersecurity-disclosure',
            'privacy-data-integrity',
            'securities-commodities-fraud',
            'public-corruption-ethics',
            'general-wrongdoing',
        ],
        'disclosure_targets' => [
            'internal-management',
            'attorney-counsel',
            'agency-federal',
            'public-media',
            'court-filing',
        ],
        'case_stages' => [
            'pre-report',
            'post-report',
            'retaliation-active',
            'litigation',
        ],
    ],
    [
        'common_name' => 'WHISPeR',
        'official_name' => 'Whistleblower and Source Protection Program',
        'official_homepage_url' => 'https://whisper.exposefacts.org',
        'intake_url' => '',
        'lawyers_url' => '',
        'contact_url' => 'https://whisper.exposefacts.org',
        'social_presence' => 0,
        'social_links' => [],
        'phones' => [],
        'emails' => [
            [ 'type' => 'general', 'address' => 'whisper@exposefacts.org', 'url' => 'https://whisper.exposefacts.org' ],
        ],
        'secure_channel' => 0,
        'secure_channels' => [],
        'mailing_address' => 'Whistleblower and Source Protection Program, ExposeFacts, 1717 K Street NW, Suite 900, Washington, DC 20006',
        'income_screening' => 'not-required',
        'organization_model' => 'legal-aid',
        'cost_models' => [ 'pro-bono' ],
        'languages' => [ 'english' ],
        'is_nationwide' => 1,
        'anonymous_pre_consult_status' => 0,
        'has_attorneys' => 1,
        'attorney_role' => 'direct-representation',
        'legal_representation_status' => 'available',
        'whistleblower_scope' => 2,
        'service_depth' => 'direct-representation',
        'intake_commitment_class' => 'general-contact-only',
        'eligibility_status' => 'restricted',
        'services' => [
            'legal-rep',
            'advocacy',
            'media',
            'consultation',
        ],
        'employment_sectors' => [
            'federal-employee',
            'military-defense',
        ],
        'protected_classes' => [
            'federal-employee',
            'military-personnel',
        ],
        'protected_disclosures' => [
            'intelligence-community',
            'classified-information',
            'public-corruption-ethics',
            'military-defense-reporting',
        ],
        'disclosure_targets' => [
            'public-media',
            'agency-federal',
            'legislative-federal',
        ],
        'case_stages' => [
            'pre-report',
            'post-report',
            'retaliation-active',
            'litigation',
        ],
    ],
    [
        'common_name' => 'EMPOWR',
        'official_name' => 'Empower Oversight Whistleblowers & Research',
        'official_homepage_url' => 'https://empowr.us',
        'intake_url' => '',
        'lawyers_url' => '',
        'contact_url' => 'https://empowr.us/contact/',
        'social_presence' => 1,
        'social_links' => [
            [ 'platform' => 'facebook', 'url' => 'https://www.facebook.com/Empower-Oversight-102206272081047', 'is_contact' => 0 ],
            [ 'platform' => 'twitter', 'url' => 'https://twitter.com/EMPOWR_us', 'is_contact' => 0 ],
            [ 'platform' => 'rumble', 'url' => 'https://rumble.com/c/c-937005', 'is_contact' => 0 ],
            [ 'platform' => 'parler', 'url' => 'https://parler.com/#/user/empowr_oversight', 'is_contact' => 0 ],
        ],
        'phones' => [],
        'emails' => [
            [ 'type' => 'secure', 'address' => 'empowr.us@pm.me', 'url' => 'https://empowr.us/contact/' ],
        ],
        'secure_channel' => 1,
        'secure_channels' => [
            [
                'tool' => 'protonmail',
                'url' => 'https://empowr.us/contact/',
                'label' => 'Proton Mail secure contact address',
                'class' => 'tip-drop',
            ],
            [
                'tool' => 'pgp-email',
                'url' => 'https://empowr.us/contact/',
                'label' => 'Published PGP key for secure email',
                'class' => 'tip-drop',
            ],
        ],
        'mailing_address' => '',
        'income_screening' => 'not-required',
        'organization_model' => 'advocacy',
        'cost_models' => [ 'free' ],
        'languages' => [ 'english' ],
        'is_nationwide' => 1,
        'anonymous_pre_consult_status' => 1,
        'has_attorneys' => 0,
        'attorney_role' => '',
        'legal_representation_status' => 'not-available',
        'whistleblower_scope' => 2,
        'service_depth' => 'triage-only',
        'intake_commitment_class' => 'tip-submission-only',
        'eligibility_status' => 'open-to-public',
        'services' => [
            'advocacy',
            'education-training',
            'consultation',
            'referral',
        ],
        'employment_sectors' => [
            'federal-employee',
            'private-sector',
            'government-contractor',
        ],
        'protected_classes' => [
            'federal-employee',
            'corporate-staff',
            'contractor-gig',
        ],
        'protected_disclosures' => [
            'public-corruption-ethics',
            'procurement-spending-fraud',
            'securities-commodities-fraud',
            'general-wrongdoing',
        ],
        'disclosure_targets' => [
            'agency-federal',
            'legislative-federal',
            'law-enforcement-fed',
            'public-media',
        ],
        'case_stages' => [
            'pre-report',
            'post-report',
            'retaliation-active',
        ],
    ],
];

// ════════════════════════════════════════════════════════════════════════════
// Seeder: ws_seed_assist_org_matrix
// ════════════════════════════════════════════════════════════════════════════

function ws_assist_org_matrix_fail( string $message, array $context = [] ): void {
    $details = '';

    foreach ( $context as $key => $value ) {
        if ( $value === '' || $value === null ) {
            continue;
        }
        $details .= sprintf( ' %s=%s;', $key, (string) $value );
    }

    wp_die(
        esc_html( "[ws-core assist-org matrix] Gawd-damm-it! Seed operation terminated. Canonical data condition was not satisfied. {$message}{$details}" ),
        esc_html__( 'Assist-org matrix seed failed', 'whistleblowershield' ),
        [ 'response' => 500 ]
    );
}

function ws_seed_assist_org_matrix() {

    global $_ws_assist_org_matrix;

    // Resolve the US jurisdiction term ID.
    $us_term = ws_jx_term_by_code( 'us' );
    if ( ! $us_term || is_wp_error( $us_term ) ) {
        ws_assist_org_matrix_fail(
            'Required jurisdiction term is unavailable.',
            [
                'taxonomy' => 'WS_JURISDICTION_TAXONOMY',
                'slug'     => 'us',
            ]
        );
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
            ws_assist_org_matrix_fail(
                'Multiple existing ws-assist-org posts match the canonical official name.',
                [
                    'official_name' => $official_name,
                    'matches'       => count( $existing_posts ),
                ]
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
            $message = is_wp_error( $post_id ) ? $post_id->get_error_message() : 'empty post ID';
            ws_assist_org_matrix_fail(
                'Canonical post creation failed.',
                [
                    'official_name' => $official_name,
                    'reason'        => $message,
                ]
            );
        }

        update_post_meta( $post_id, 'ws_aorg_official_name', $official_name );
        update_post_meta( $post_id, 'ws_aorg_common_name', $org['common_name'] );
        update_post_meta( $post_id, 'ws_aorg_official_homepage_url', $org['official_homepage_url'] );
        update_post_meta( $post_id, 'ws_aorg_intake_url', $org['intake_url'] );
        update_post_meta( $post_id, 'ws_aorg_lawyers_url', $org['lawyers_url'] );
        update_post_meta( $post_id, 'ws_aorg_contact_url', $org['contact_url'] );
        update_post_meta( $post_id, 'ws_aorg_social_presence', $org['social_presence'] );
        update_post_meta( $post_id, 'ws_aorg_has_secure_channel', $org['secure_channel'] );
        update_post_meta( $post_id, 'ws_aorg_mailing_address', $org['mailing_address'] );
        update_post_meta( $post_id, 'ws_aorg_income_screening', $org['income_screening'] );
        update_post_meta( $post_id, 'ws_aorg_eligibility_status', $org['eligibility_status'] );
        update_post_meta( $post_id, 'ws_aorg_is_nationwide', $org['is_nationwide'] );
        update_post_meta( $post_id, 'ws_aorg_anonymous_pre_consult_status', $org['anonymous_pre_consult_status'] );
        update_post_meta( $post_id, 'ws_aorg_has_attorneys', $org['has_attorneys'] );
        update_post_meta( $post_id, 'ws_aorg_attorney_role', $org['attorney_role'] );
        update_post_meta( $post_id, 'ws_aorg_legal_representation_status', $org['legal_representation_status'] );
        update_post_meta( $post_id, 'ws_aorg_service_depth', $org['service_depth'] );
        update_post_meta( $post_id, 'ws_aorg_intake_commitment_class', $org['intake_commitment_class'] );
        update_post_meta( $post_id, 'ws_aorg_whistleblower_scope', $org['whistleblower_scope'] );

        $post = get_post( (int) $post_id );
        if ( ! $post || $post->post_name === '' ) {
            ws_assist_org_matrix_fail(
                'WordPress did not return a canonical post_name after post resolution.',
                [
                    'official_name' => $official_name,
                    'post_id'       => (int) $post_id,
                ]
            );
        }

        update_post_meta(
            $post_id,
            '_ws_aorg_id',
            ws_matrix_build_assist_org_internal_id( 'us', (string) $post->post_name )
        );

        $secure_channels = $org['secure_channels'];
        update_post_meta( $post_id, 'ws_aorg_secure_channels', count( $secure_channels ) );
        foreach ( $secure_channels as $i => $channel ) {
            update_post_meta( $post_id, "ws_aorg_secure_channels_{$i}_channel_tool", $channel['tool'] );
            update_post_meta( $post_id, "ws_aorg_secure_channels_{$i}_channel_url", $channel['url'] );
            update_post_meta( $post_id, "ws_aorg_secure_channels_{$i}_channel_label", $channel['label'] );
            update_post_meta( $post_id, "ws_aorg_secure_channels_{$i}_channel_class", $channel['class'] );
        }

        $phones = $org['phones'];
        update_post_meta( $post_id, 'ws_aorg_phones', count( $phones ) );
        foreach ( $phones as $i => $phone ) {
            update_post_meta( $post_id, "ws_aorg_phones_{$i}_phone_type", $phone['type'] );
            update_post_meta( $post_id, "ws_aorg_phones_{$i}_phone_number", $phone['number'] );
            update_post_meta( $post_id, "ws_aorg_phones_{$i}_phone_url", $phone['url'] );
        }

        $emails = $org['emails'];
        update_post_meta( $post_id, 'ws_aorg_emails', count( $emails ) );
        foreach ( $emails as $i => $email ) {
            update_post_meta( $post_id, "ws_aorg_emails_{$i}_email_type", $email['type'] );
            update_post_meta( $post_id, "ws_aorg_emails_{$i}_email_address", $email['address'] );
            update_post_meta( $post_id, "ws_aorg_emails_{$i}_email_url", $email['url'] );
        }

        $social_links = $org['social_links'];
        update_post_meta( $post_id, 'ws_aorg_social_links', count( $social_links ) );
        foreach ( $social_links as $i => $link ) {
            update_post_meta( $post_id, "ws_aorg_social_links_{$i}_social_platform", $link['platform'] );
            update_post_meta( $post_id, "ws_aorg_social_links_{$i}_social_url", $link['url'] );
            update_post_meta( $post_id, "ws_aorg_social_links_{$i}_social_is_contact", $link['is_contact'] );
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
