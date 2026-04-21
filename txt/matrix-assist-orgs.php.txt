<?php
/**
 * matrix-assist-orgs.php — Seeds nationwide and federal-scope whistleblower support organizations.
 *
 * @package    WhistleblowerShield
 * @since      3.0.0
 * @version    3.17.1
 *
 * VERSION
 * -------
 * 3.17.1  Data corrections from deep research pass:
 *         - GAP: corrected mailing address to 1612 K St NW Suite 808;
 *           updated intake_url to /how-to-request-assistance/
 *         - Whistleblower Aid: corrected mailing address to
 *           1250 Connecticut Ave NW Suite 700 (Charity Navigator confirmed)
 *         - TAF: re-branded from Taxpayers Against Fraud Education Fund
 *           to The Anti-Fraud Coalition; slug, internal_id, description,
 *           mailing address, and intake_url updated; gate bumped to 1.1.0
 *         - The Signals Network: has_secure_channel set to 1 (ProtonMail
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
        'internal_id' => 'gov-accountability-proj-us',
        'common_name' => 'GAP',
        'title' => 'Government Accountability Project',
        'slug' => 'government-accountability-project',
        'description' => 'Promotes government and corporate accountability by advancing a culture of transparency, providing legal representation to whistleblowers, and advocating for strong whistleblower laws.',
        'ws_aorg_website_url' => 'https://whistleblower.org',
        'ws_aorg_intake_url' => 'https://crm.whistleblower.org/form/online-intake-application',
        'ws_aorg_contact_url' => '',
        'phones' => [
            [
                'type' => 'other',
                'number' => '(202) 457-0034',
            ],
        ],
        'emails' => [
            [
                'type' => 'general',
                'address' => 'info@whistleblower.org',
            ],
        ],
        'has_secure_channel' => 0,
        'secure_contact_url' => '',
        'secure_contact_tool' => '',
        'mailing_address' => 'Government Accountability Project, 1612 K St NW, Suite 808, Washington, DC 20006-2802',
        'income_limit' => 0,
        'income_limit_notes' => '',
        'eligibility_notes' => '',
        'aorg_type' => 'nonprofit',
        'cost_models' => [ 'pro-bono' ],
        'is_nationwide' => 1,
        'is_limited_scope' => 0,
        'community_scope' => '',
        'accepts_anon' => 1,
        'has_attorneys' => 1,
        'whistleblower_scope' => 3,
        'whistleblower_note' => 'Government Accountability Project has empowered over 8,000 whistleblowers with legal protection and advocacy expertise.',
        'services' => [
            'legal-rep',
            'consultation',
            'advocacy',
            'media',
        ],
        'sectors' => [
            'federal-employee',
            'private-sector',
            'nonprofit-ngo',
        ],
        'disclosure_types' => [
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
        'internal_id' => 'nat-wb-ctr-us',
        'common_name' => 'NWC',
        'title' => 'National Whistleblower Center',
        'slug' => 'national-whistleblower-center',
        'description' => 'Advocates for the rights of whistleblowers, promotes whistleblower protections, and educates the public and policymakers about the importance of whistleblowing.',
        'ws_aorg_website_url' => 'https://www.whistleblowers.org',
        'ws_aorg_intake_url' => 'https://www.report-fraud-now.info/',
        'ws_aorg_contact_url' => '',
        'phones' => [
            [
                'type' => 'other',
                'number' => '(202) 342-1903',
            ],
        ],
        'emails' => [
            [
                'type' => 'general',
                'address' => 'contact@whistleblowers.org',
            ],
        ],
        'has_secure_channel' => 0,
        'secure_contact_url' => '',
        'secure_contact_tool' => '',
        'mailing_address' => 'National Whistleblower Center, 1140 Connecticut Ave NW, Suite 900, Washington, DC 20036',
        'income_limit' => 0,
        'income_limit_notes' => '',
        'eligibility_notes' => '',
        'aorg_type' => 'nonprofit',
        'cost_models' => [ 'unclear' ],
        'is_nationwide' => 1,
        'is_limited_scope' => 0,
        'community_scope' => '',
        'accepts_anon' => 1,
        'has_attorneys' => 1,
        'whistleblower_scope' => 3,
        'whistleblower_note' => 'The National Whistleblower Center (NWC) is the leading nonprofit dedicated to protecting and rewarding whistleblowers around the world. We assist whistleblowers in finding legal aid, advocate for stronger whistleblower protection laws, and educate the public about whistleblowers\' critical role in protecting democracy and the rule of law.',
        'services' => [
            'referral',
            'advocacy',
            'consultation',
        ],
        'sectors' => [
            'all-sectors',
        ],
        'disclosure_types' => [
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
        'internal_id' => 'wb-aid-us',
        'common_name' => '',
        'title' => 'Whistleblower Aid',
        'slug' => 'whistleblower-aid',
        'description' => 'Provides free legal assistance and support to clients who want to safely and legally disclose wrongdoing in the public interest.',
        'ws_aorg_website_url' => 'https://whistlebloweraid.org',
        'ws_aorg_intake_url' => 'https://whistlebloweraid.org/become-a-whistleblower/',
        'ws_aorg_contact_url' => '',
        'phones' => [],
        'emails' => [],
        'has_secure_channel' => 1,
        'secure_contact_url' => 'https://whistlebloweraid.org/become-a-whistleblower/signal/',
        'secure_contact_tool' => 'Signal',
        'mailing_address' => 'Whistleblower Aid, 1250 Connecticut Ave NW, Suite 700, Washington, DC 20036',
        'income_limit' => 0,
        'income_limit_notes' => '',
        'eligibility_notes' => '',
        'aorg_type' => 'legal-aid',
        'cost_models' => [ 'pro-bono' ],
        'is_nationwide' => 1,
        'is_limited_scope' => 0,
        'community_scope' => '',
        'accepts_anon' => 1,
        'has_attorneys' => 1,
        'whistleblower_scope' => 3,
        'whistleblower_note' => 'Whistleblower Aid is a pioneering non-profit legal organization that helps public and private sector workers report and expose wrongdoing — safely, lawfully, and responsibly.',
        'services' => [
            'legal-rep',
            'consultation',
            'advocacy',
            'media',
            'retaliation',
        ],
        'sectors' => [
            'federal-employee',
            'private-sector',
        ],
        'disclosure_types' => [
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
        'internal_id' => 'proj-on-gov-oversight-us',
        'common_name' => 'POGO',
        'title' => 'Project On Government Oversight',
        'slug' => 'project-on-government-oversight',
        'description' => 'Investigates and exposes waste, corruption, abuse of power, and when the government fails to serve the public interest, including supporting federal whistleblowers.',
        'ws_aorg_website_url' => 'https://www.pogo.org',
        'ws_aorg_intake_url' => '',
        'ws_aorg_contact_url' => '',
        'phones' => [
            [
                'type' => 'other',
                'number' => '(202) 347-1122',
            ],
        ],
        'emails' => [
            [
                'type' => 'general',
                'address' => 'info@pogo.org',
            ],
        ],
        'has_secure_channel' => 0,
        'secure_contact_url' => '',
        'secure_contact_tool' => '',
        'mailing_address' => 'Project On Government Oversight, 1100 13th Street NW, Suite 800, Washington, DC 20005',
        'income_limit' => 0,
        'income_limit_notes' => '',
        'eligibility_notes' => '',
        'aorg_type' => 'advocacy',
        'cost_models' => [ 'free' ],
        'is_nationwide' => 1,
        'is_limited_scope' => 0,
        'community_scope' => '',
        'accepts_anon' => 1,
        'has_attorneys' => 0,
        'whistleblower_scope' => 1,
        'whistleblower_note' => 'POGO was founded to help bring attention to disclosures from Pentagon whistleblowers. Since then, we have worked with whistleblowers on countless investigative projects.',
        'services' => [
            'advocacy',
            'hotline',
            'media',
        ],
        'sectors' => [
            'federal-employee',
            'military-defense',
        ],
        'disclosure_types' => [
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
        'internal_id' => 'anti-fraud-coal-us',
        'common_name' => 'TAF',
        'title' => 'The Anti-Fraud Coalition',
        'slug' => 'the-anti-fraud-coalition',
        'description' => 'Formerly Taxpayers Against Fraud, the Anti-Fraud Coalition is the leading professional network for attorneys and whistleblowers in False Claims Act and financial fraud cases. Maintains a directory of 400+ qui tam specialists and publishes economic research on whistleblower program effectiveness.',
        'ws_aorg_website_url' => 'https://www.taf.org',
        'ws_aorg_intake_url' => 'https://www.taf.org/attorneys/find-an-attorney/',
        'ws_aorg_contact_url' => '',
        'phones' => [],
        'emails' => [],
        'has_secure_channel' => 0,
        'secure_contact_url' => '',
        'secure_contact_tool' => '',
        'mailing_address' => 'The Anti-Fraud Coalition, 1220 19th St NW, Ste 501, Washington, DC 20036',
        'income_limit' => 0,
        'income_limit_notes' => '',
        'eligibility_notes' => '',
        'aorg_type' => 'advocacy',
        'cost_models' => [ 'unclear' ],
        'is_nationwide' => 1,
        'is_limited_scope' => 0,
        'community_scope' => '',
        'accepts_anon' => 0,
        'has_attorneys' => 1,
        'whistleblower_scope' => 2,
        'whistleblower_note' => 'Through our membership directory of top whistleblower attorneys, we help prospective whistleblowers find the best representation to win these tough cases.',
        'services' => [
            'referral',
            'advocacy',
        ],
        'sectors' => [
            'all-sectors',
        ],
        'disclosure_types' => [
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
        'internal_id' => 'wb-america-us',
        'common_name' => 'WoA',
        'title' => 'Whistleblowers of America',
        'slug' => 'whistleblowers-of-america',
        'description' => 'Provides peer support, advocacy, and guidance to whistleblowers, with a focus on retaliation response and trauma-informed support.',
        'ws_aorg_website_url' => 'https://www.whistleblowersofamerica.org',
        'ws_aorg_intake_url' => '',
        'ws_aorg_contact_url' => 'https://www.whistleblowersofamerica.org/learn-more/peer-support',
        'phones' => [
            [
                'type' => 'other',
                'number' => '202-643-1956',
            ],
        ],
        'emails' => [
            [
                'type' => 'general',
                'address' => 'info@whistleblowersofamerica.org',
            ],
        ],
        'has_secure_channel' => 0,
        'secure_contact_url' => '',
        'secure_contact_tool' => '',
        'mailing_address' => 'Whistleblowers of America, 11130 Lillian Highway, Pensacola, FL 32506',
        'income_limit' => 0,
        'income_limit_notes' => '',
        'eligibility_notes' => '',
        'aorg_type' => 'advocacy',
        'cost_models' => [ 'unclear' ],
        'is_nationwide' => 1,
        'is_limited_scope' => 0,
        'community_scope' => '',
        'accepts_anon' => 1,
        'has_attorneys' => 0,
        'whistleblower_scope' => 3,
        'whistleblower_note' => 'We help whistleblowers overcome the traumatic stress caused by retaliation and help with problem-solving — assisting whistleblowers who have suffered retaliation after having identified harm to individuals or the public.',
        'services' => [
            'retaliation',
            'advocacy',
            'referral',
            'consultation',
        ],
        'sectors' => [
            'all-sectors',
        ],
        'disclosure_types' => [
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
        'internal_id' => 'wb-intl-net-us',
        'common_name' => 'WIN',
        'title' => 'Whistleblowing International Network',
        'slug' => 'whistleblowing-international-network',
        'description' => 'Global network of civil society organizations supporting whistleblowing, transparency, and accountability, including member groups operating in the United States.',
        'ws_aorg_website_url' => 'https://whistleblowingnetwork.org/Home',
        'ws_aorg_intake_url' => '',
        'ws_aorg_contact_url' => 'https://whistleblowingnetwork.org/Contact-Us',
        'phones' => [],
        'emails' => [
            [
                'type' => 'general',
                'address' => 'info@whistleblowingnetwork.org',
            ],
        ],
        'has_secure_channel' => 0,
        'secure_contact_url' => '',
        'secure_contact_tool' => '',
        'mailing_address' => 'Whistleblowing International Network (WIN) c/o SCVO, Edward House, 199 Sauchiehall Street, Glasgow, G2 3EX',
        'income_limit' => 0,
        'income_limit_notes' => '',
        'eligibility_notes' => '',
        'aorg_type' => 'advocacy',
        'cost_models' => [ 'free' ],
        'is_nationwide' => 1,
        'is_limited_scope' => 0,
        'community_scope' => '',
        'accepts_anon' => 0,
        'has_attorneys' => 0,
        'whistleblower_scope' => 2,
        'whistleblower_note' => 'WIN supports whistleblowing organizations and individuals worldwide through its member network, advocacy, and capacity-building for civil society groups.',
        'services' => [
            'referral',
            'advocacy',
        ],
        'sectors' => [
            'all-sectors',
        ],
        'disclosure_types' => [
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
        'internal_id' => 'nat-emp-law-proj-us',
        'common_name' => 'NELP',
        'title' => 'National Employment Law Project',
        'slug' => 'national-employment-law-project',
        'description' => 'Champions the rights of low-wage and unemployed workers through research and advocacy, including for workers who face retaliation for reporting violations.',
        'ws_aorg_website_url' => 'https://www.nelp.org',
        'ws_aorg_intake_url' => '',
        'ws_aorg_contact_url' => 'https://www.nelp.org/about-us/contact-us/',
        'phones' => [
            [
                'type' => 'other',
                'number' => '(212) 285-3025',
            ],
        ],
        'emails' => [
            [
                'type' => 'general',
                'address' => 'nelp@nelp.org',
            ],
        ],
        'has_secure_channel' => 0,
        'secure_contact_url' => '',
        'secure_contact_tool' => '',
        'mailing_address' => 'National Office: National Employment Law Project, PO Box 1779, New York, NY 10008',
        'income_limit' => 0,
        'income_limit_notes' => '',
        'eligibility_notes' => '',
        'aorg_type' => 'advocacy',
        'cost_models' => [ 'free' ],
        'is_nationwide' => 1,
        'is_limited_scope' => 0,
        'community_scope' => '',
        'accepts_anon' => 0,
        'has_attorneys' => 0,
        'whistleblower_scope' => 1,
        'whistleblower_note' => 'NELP champions the rights of low-wage workers through research and advocacy, including protection for those who report violations.',
        'services' => [
            'advocacy',
        ],
        'sectors' => [
            'private-sector',
            'nonprofit-ngo',
        ],
        'disclosure_types' => [
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
        'internal_id' => 'nat-emp-lawyers-assoc-us',
        'common_name' => 'NELA',
        'title' => 'National Employment Lawyers Association',
        'slug' => 'national-employment-lawyers-association',
        'description' => 'National professional association of lawyers representing employees in labor, employment, and civil rights disputes. Provides a lawyer finder to connect workers with plaintiff-side employment counsel.',
        'ws_aorg_website_url' => 'https://www.nela.org',
        'ws_aorg_intake_url' => 'https://engagement.nela.org/NELA/findalawyer.aspx',
        'ws_aorg_contact_url' => '',
        'phones' => [
            [
                'type' => 'other',
                'number' => '(415) 296-7629',
            ],
        ],
        'emails' => [
            [
                'type' => 'general',
                'address' => 'nelahq@nelahq.org',
            ],
        ],
        'has_secure_channel' => 0,
        'secure_contact_url' => '',
        'secure_contact_tool' => '',
        'mailing_address' => '1800 Sutter Street, Suite 210, Concord, CA 94520',
        'income_limit' => 0,
        'income_limit_notes' => '',
        'eligibility_notes' => '',
        'aorg_type' => 'bar-program',
        'cost_models' => [ 'fee-for-service' ],
        'is_nationwide' => 1,
        'is_limited_scope' => 0,
        'community_scope' => '',
        'accepts_anon' => 0,
        'has_attorneys' => 0,
        'whistleblower_scope' => 1,
        'whistleblower_note' => 'NELA members exclusively represent employees — plaintiffs — in labor, employment, and civil rights matters, providing a national referral network for workers seeking counsel.',
        'services' => [
            'referral',
        ],
        'sectors' => [
            'all-sectors',
        ],
        'disclosure_types' => [
            'wage-hour-violations',
            'occupational-health-safety',
            'public-corruption-ethics',
            'general-wrongdoing',
        ],
        'disclosure_targets' => [
            'judicial-federal',
            'judicial-state',
        ],
        'case_stages' => [
            'post-report',
            'retaliation-active',
            'litigation',
        ],
    ],
    [
        'internal_id' => 'legal-svc-corp-find-legal-aid-us',
        'common_name' => 'LSC',
        'title' => 'Legal Services Corporation - Find Legal Aid',
        'slug' => 'legal-services-corporation-find-legal-aid',
        'description' => 'National legal aid locator supported by the Legal Services Corporation, helping users find local nonprofit legal aid providers for civil legal issues, including workplace retaliation and employment-related matters.',
        'ws_aorg_website_url' => 'https://www.lsc.gov',
        'ws_aorg_intake_url' => 'https://www.lsc.gov/about-lsc/what-legal-aid/get-legal-help',
        'ws_aorg_contact_url' => '',
        'phones' => [
            [
                'type' => 'other',
                'number' => '(202) 295-1500',
            ],
        ],
        'emails' => [],
        'has_secure_channel' => 0,
        'secure_contact_url' => '',
        'secure_contact_tool' => '',
        'mailing_address' => 'Legal Services Corporation, 3333 K Street NW, Washington, DC 20007',
        'income_limit' => 1,
        'income_limit_notes' => 'LSC-funded grantees are legally required to serve clients at or below 125% of Federal Poverty Guidelines per 45 CFR Part 1611; updated annually by HHS',
        'eligibility_notes' => '',
        'aorg_type' => 'legal-aid',
        'cost_models' => [ 'free' ],
        'is_nationwide' => 1,
        'is_limited_scope' => 0,
        'community_scope' => '',
        'accepts_anon' => 0,
        'has_attorneys' => 0,
        'whistleblower_scope' => 1,
        'whistleblower_note' => 'LSC funds civil legal aid providers across all 50 states and territories; employment and retaliation matters are within scope for many member organizations.',
        'services' => [
            'referral',
        ],
        'sectors' => [
            'all-sectors',
        ],
        'disclosure_types' => [
            'wage-hour-violations',
            'occupational-health-safety',
            'public-corruption-ethics',
            'general-wrongdoing',
        ],
        'disclosure_targets' => [
            'agency-federal',
            'agency-state',
            'judicial-state',
        ],
        'case_stages' => [
            'pre-report',
            'post-report',
            'retaliation-active',
        ],
    ],
    [
        'internal_id' => 'nat-legal-aid-defender-assoc-us',
        'common_name' => 'NLADA',
        'title' => 'National Legal Aid and Defender Association',
        'slug' => 'national-legal-aid-and-defender-association',
        'description' => 'National association supporting civil legal aid and public defense providers, with resources and member pathways that help users locate legal-aid support channels.',
        'ws_aorg_website_url' => 'https://www.nlada.org',
        'ws_aorg_intake_url' => '',
        'ws_aorg_contact_url' => '',
        'phones' => [
            [
                'type' => 'other',
                'number' => '(202) 452-0620',
            ],
        ],
        'emails' => [],
        'has_secure_channel' => 0,
        'secure_contact_url' => '',
        'secure_contact_tool' => '',
        'mailing_address' => 'National Legal Aid and Defender Association, 1140 Connecticut Ave NW, Suite 900, Washington, DC 20036',
        'income_limit' => 0,
        'income_limit_notes' => '',
        'eligibility_notes' => '',
        'aorg_type' => 'advocacy',
        'cost_models' => [ 'free' ],
        'is_nationwide' => 1,
        'is_limited_scope' => 0,
        'community_scope' => '',
        'accepts_anon' => 0,
        'has_attorneys' => 0,
        'whistleblower_scope' => 1,
        'whistleblower_note' => 'NLADA supports the nation\'s civil legal aid and public defense providers, offering pathways for users to locate income-eligible legal help across all jurisdictions.',
        'services' => [
            'referral',
            'advocacy',
        ],
        'sectors' => [
            'all-sectors',
        ],
        'disclosure_types' => [
            'wage-hour-violations',
            'occupational-health-safety',
            'public-corruption-ethics',
            'general-wrongdoing',
        ],
        'disclosure_targets' => [
            'agency-state',
            'judicial-state',
        ],
        'case_stages' => [
            'pre-report',
            'post-report',
            'retaliation-active',
        ],
    ],
    [
        'internal_id' => 'nat-wb-ctr-att-ref-prog-us',
        'common_name' => 'NWC Referral',
        'title' => 'National Whistleblower Center — Attorney Referral Program',
        'slug' => 'national-whistleblower-center-attorney-referral',
        'description' => 'Referral program connecting whistleblowers with experienced attorneys in False Claims Act, SEC, IRS, and other whistleblower law areas.',
        'ws_aorg_website_url' => 'https://www.whistleblowers.org',
        'ws_aorg_intake_url' => 'https://www.whistleblowers.org/find-a-whisteblower-attorney/',
        'ws_aorg_contact_url' => '',
        'phones' => [
            [
                'type' => 'other',
                'number' => '(202) 342-1900',
            ],
        ],
        'emails' => [
            [
                'type' => 'general',
                'address' => 'info@whistleblowers.org',
            ],
        ],
        'has_secure_channel' => 0,
        'secure_contact_url' => '',
        'secure_contact_tool' => '',
        'mailing_address' => 'National Whistleblower Center, 2001 S Street NW, Washington, DC 20009',
        'income_limit' => 0,
        'income_limit_notes' => '',
        'eligibility_notes' => '',
        'aorg_type' => 'bar-program',
        'cost_models' => [ 'fee-for-service' ],
        'is_nationwide' => 1,
        'is_limited_scope' => 0,
        'community_scope' => '',
        'accepts_anon' => 0,
        'has_attorneys' => 0,
        'whistleblower_scope' => 3,
        'whistleblower_note' => 'NWC\'s attorney referral program connects whistleblowers with experienced counsel in False Claims Act, SEC, IRS, and other whistleblower law areas.',
        'services' => [
            'referral',
        ],
        'sectors' => [
            'all-sectors',
        ],
        'disclosure_types' => [
            'securities-commodities-fraud',
            'tax-evasion-fraud',
            'public-corruption-ethics',
            'procurement-spending-fraud',
            'healthcare-medicare-fraud',
        ],
        'disclosure_targets' => [
            'agency-federal',
            'judicial-federal',
        ],
        'case_stages' => [
            'pre-report',
            'post-report',
            'litigation',
        ],
    ],
    [
        'internal_id' => 'american-bar-assoc-find-legal-help-us',
        'common_name' => 'ABA',
        'title' => 'American Bar Association — Find Legal Help',
        'slug' => 'american-bar-association-find-legal-help',
        'description' => 'ABA information portal that directs the public to state and local lawyer referral services and bar-sponsored legal aid programs across the United States.',
        'ws_aorg_website_url' => 'https://www.americanbar.org/groups/legal_services/',
        'ws_aorg_intake_url' => 'https://www.americanbar.org/groups/legal_services/flh-home/',
        'ws_aorg_contact_url' => '',
        'phones' => [
            [
                'type' => 'other',
                'number' => '(800) 285-2221',
            ],
        ],
        'emails' => [
            [
                'type' => 'general',
                'address' => 'info@americanbar.org',
            ],
        ],
        'has_secure_channel' => 0,
        'secure_contact_url' => '',
        'secure_contact_tool' => '',
        'mailing_address' => 'American Bar Association, 321 N Clark St, Chicago, IL 60654',
        'income_limit' => 1,
        'income_limit_notes' => 'Routes to LSC-funded and pro bono programs that apply income thresholds (typically ≤125-200% FPG); the referral tool itself does not screen income but downstream resources do',
        'eligibility_notes' => '',
        'aorg_type' => 'bar-program',
        'cost_models' => [ 'fee-for-service' ],
        'is_nationwide' => 1,
        'is_limited_scope' => 0,
        'community_scope' => '',
        'accepts_anon' => 0,
        'has_attorneys' => 0,
        'whistleblower_scope' => 1,
        'whistleblower_note' => 'The ABA Find Legal Help portal directs the public to state and local lawyer referral services and bar-sponsored legal aid programs across the United States.',
        'services' => [
            'referral',
        ],
        'sectors' => [
            'all-sectors',
        ],
        'disclosure_types' => [
            'wage-hour-violations',
            'occupational-health-safety',
            'securities-commodities-fraud',
            'healthcare-medicare-fraud',
            'general-wrongdoing',
        ],
        'disclosure_targets' => [
            'judicial-federal',
            'judicial-state',
        ],
        'case_stages' => [
            'pre-report',
            'post-report',
            'litigation',
        ],
    ],
    [
        'internal_id' => 'pub-emp-environmental-responsibility-us',
        'common_name' => 'PEER',
        'title' => 'Public Employees for Environmental Responsibility',
        'slug' => 'public-employees-for-environmental-responsibility',
        'description' => 'PEER provides free legal and strategic assistance to federal, state, and local government employees who blow the whistle on environmental harm, public health threats, and scientific integrity violations.',
        'ws_aorg_website_url' => 'https://peer.org',
        'ws_aorg_intake_url' => 'https://peer.org/contact-legal-team/',
        'ws_aorg_contact_url' => 'https://peer.org/about-us/contact-us/',
        'phones' => [
            [
                'type' => 'other',
                'number' => '(202) 265-7337',
            ],
        ],
        'emails' => [
            [
                'type' => 'general',
                'address' => 'info@peer.org',
            ],
        ],
        'has_secure_channel' => 0,
        'secure_contact_url' => '',
        'secure_contact_tool' => '',
        'mailing_address' => 'PEER, 962 Wayne Ave, Suite 610, Silver Spring, MD 20910',
        'income_limit' => 0,
        'income_limit_notes' => '',
        'eligibility_notes' => '',
        'aorg_type' => 'nonprofit',
        'cost_models' => [ 'pro-bono' ],
        'is_nationwide' => 1,
        'is_limited_scope' => 0,
        'community_scope' => '',
        'accepts_anon' => 1,
        'has_attorneys' => 1,
        'whistleblower_scope' => 2,
        'whistleblower_note' => 'At PEER, our dedicated attorneys represent federal, state, and local government whistleblowers who wish to expose their agency\'s wrongdoings on environmental, public health, and scientific issues.',
        'services' => [
            'legal-rep',
            'consultation',
            'retaliation',
        ],
        'sectors' => [
            'federal-employee',
            'state-local-employee',
        ],
        'disclosure_types' => [
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
        'internal_id' => 'signals-net-us',
        'common_name' => 'TSN',
        'title' => 'The Signals Network',
        'slug' => 'the-signals-network',
        'description' => 'The Signals Network is a nonprofit that provides holistic support to whistleblowers who share public interest information — including legal assistance, psychological counseling, physical security, and media coordination.',
        'ws_aorg_website_url' => 'https://thesignalsnetwork.org',
        'ws_aorg_intake_url' => 'https://thesignalsnetwork.org/whistleblower-protection-program/',
        'ws_aorg_contact_url' => 'https://thesignalsnetwork.org/contact/',
        'phones' => [],
        'emails' => [
            [
                'type' => 'general',
                'address' => 'info@thesignalsnetwork.org',
            ],
            [
                'type' => 'intake',
                'address' => 'protect@thesignalsnetwork.org',
            ],
        ],
        'has_secure_channel' => 1,
        'secure_contact_url' => 'https://thesignalsnetwork.org/contact/',
        'secure_contact_tool' => 'ProtonMail',
        'mailing_address' => 'The Signals Network, 416 Florida Ave NW #26152, Washington, DC 20001',
        'income_limit' => 0,
        'income_limit_notes' => '',
        'eligibility_notes' => '',
        'aorg_type' => 'nonprofit',
        'cost_models' => [ 'free' ],
        'is_nationwide' => 1,
        'is_limited_scope' => 0,
        'community_scope' => '',
        'accepts_anon' => 1,
        'has_attorneys' => 1,
        'whistleblower_scope' => 3,
        'whistleblower_note' => 'TSN\'s Whistleblower Protection Program aims to help whistleblowers navigate the legal, physical, psychological and economic consequences of speaking out.',
        'services' => [
            'consultation',
            'media',
            'retaliation',
        ],
        'sectors' => [
            'all-sectors',
        ],
        'disclosure_types' => [
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
    $org_name = trim( (string) ( $org['title'] ?? '' ) );
    $homepage = trim( (string) ( $org['ws_aorg_website_url'] ?? '' ) );
    $jx_slug  = strtolower( trim( (string) $jx_slug ) );

    $host = strtolower( (string) wp_parse_url( $homepage, PHP_URL_HOST ) );
    if ( str_starts_with( $host, 'www.' ) ) {
        $host = substr( $host, 4 );
    }

    $seed = $org_name !== '' ? $org_name : $host;
    if ( $seed === '' ) {
        $seed = 'assist org';
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
    // ws_ingest_build_assist_org_internal_id() in tool-ingest.php.
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

function ws_seed_assist_org_matrix() {

    global $_ws_assist_org_matrix;

    // Resolve the US jurisdiction term ID.
    $us_term = ws_jx_term_by_code( 'us' );
    if ( ! $us_term || is_wp_error( $us_term ) ) {
        return; // Jurisdiction terms not yet seeded — bail.
    }
    $us_term_id = (int) $us_term->term_id;

    if ( ! defined( 'WS_MATRIX_SEEDING_IN_PROGRESS' ) ) {
        define( 'WS_MATRIX_SEEDING_IN_PROGRESS', true );
    }

    foreach ( $_ws_assist_org_matrix as $org ) {

        $internal_id = ws_matrix_build_assist_org_internal_id( $org, 'us' );
        if ( $internal_id === '' ) {
            $internal_id = (string) ( $org['internal_id'] ?? '' );
        }

        $existing = get_page_by_path( $org['slug'], OBJECT, 'ws-assist-org' );
        // Matrix no longer requires duplicated description + post_content
        // values. Seeder derives post_content from description unless an
        // explicit post_content override exists.
        $content  = (string) ( $org['post_content'] ?? ( $org['description'] ?? '' ) );

        // Optional matrix-level HTML comment block for this org.
        if ( ! empty( $org['html_comment'] ) ) {
            $content = rtrim( $content ) . "\n\n" . trim( (string) $org['html_comment'] );
        }

        $post_data = [
            'post_title'   => $org['title'],
            'post_name'    => $org['slug'],
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
            continue;
        }

        // ── ACF meta fields ──────────────────────────────────────────────────
        //
        // String/URL fields: skipped if empty (no point storing blank strings).
        // Boolean fields (0/1) and array fields always write — 0 is meaningful.

        $meta = [
            'ws_aorg_internal_id'          => $internal_id,
            'ws_aorg_official_name'        => $org['title']                ?? '',
            'ws_aorg_common_name'          => $org['common_name']          ?? '',
            'ws_aorg_description'          => $org['description']          ?? '',
            'ws_aorg_website_url'          => $org['ws_aorg_website_url']  ?? '',
            'ws_aorg_intake_url'           => $org['ws_aorg_intake_url']   ?? '',
            'ws_aorg_contact_url'          => $org['ws_aorg_contact_url']  ?? '',
            'ws_aorg_has_secure_channel'   => $org['has_secure_channel']   ?? 0,
            'ws_aorg_secure_contact_url'   => $org['secure_contact_url']   ?? '',
            'ws_aorg_secure_contact_tool'  => $org['secure_contact_tool']  ?? '',
            'ws_aorg_mailing_address'      => $org['mailing_address']      ?? '',
            'ws_aorg_has_income_limit'     => $org['income_limit']         ?? '',
            'ws_aorg_has_income_limit_details' => $org['income_limit_notes']   ?? '',
            'ws_aorg_eligibility_details'  => $org['eligibility_notes']    ?? '',
            'ws_aorg_serves_nationwide'    => $org['is_nationwide']        ?? 0,
            'ws_aorg_has_limited_scope'    => $org['is_limited_scope']     ?? 0,
            'ws_aorg_community_scope'      => $org['community_scope']      ?? '',
            'ws_aorg_accepts_anonymous'    => $org['accepts_anon']         ?? 0,
            'ws_aorg_licensed_attorneys'   => $org['has_attorneys']        ?? 0,
            // Whistleblower focus score (1-3) — drives base score in ws_filter_score_org().
            // Always write even if 0; 1 is the minimum meaningful value and 0 signals
            // the matrix entry is missing this field (ingest should warn).
            'ws_aorg_whistleblower_scope'         => $org['whistleblower_scope']  ?? 1,
            'ws_aorg_whistleblower_scope_details' => $org['whistleblower_note']   ?? '',
        ];

        foreach ( $meta as $key => $value ) {
            if ( $value !== '' ) {
                update_post_meta( $post_id, $key, $value );
            }
        }

        // Contact repeaters: consume canonical matrix arrays directly.
        $phone_rows = [];
        if ( ! empty( $org['phones'] ) && is_array( $org['phones'] ) ) {
            foreach ( $org['phones'] as $row ) {
                if ( ! is_array( $row ) ) {
                    continue;
                }
                $number = trim( (string) ( $row['number'] ?? '' ) );
                if ( $number === '' ) {
                    continue;
                }
                $type = sanitize_key( (string) ( $row['type'] ?? 'other' ) );
                if ( $type === '' ) {
                    $type = 'other';
                }
                $phone_rows[] = [
                    'ws_aorg_phone_type'   => $type,
                    'ws_aorg_phone_number' => $number,
                ];
            }
        }

        $email_rows = [];
        if ( ! empty( $org['emails'] ) && is_array( $org['emails'] ) ) {
            foreach ( $org['emails'] as $row ) {
                if ( ! is_array( $row ) ) {
                    continue;
                }
                $address = sanitize_email( (string) ( $row['address'] ?? '' ) );
                if ( $address === '' ) {
                    continue;
                }
                $type = sanitize_key( (string) ( $row['type'] ?? 'other' ) );
                if ( $type === '' ) {
                    $type = 'other';
                }
                $email_rows[] = [
                    'ws_aorg_email_type'    => $type,
                    'ws_aorg_email_address' => $address,
                ];
            }
        }

        if ( function_exists( 'update_field' ) ) {
            update_field( 'ws_aorg_phones', $phone_rows, $post_id );
            update_field( 'ws_aorg_emails', $email_rows, $post_id );
        } else {
            update_post_meta( $post_id, 'ws_aorg_phones', $phone_rows );
            update_post_meta( $post_id, 'ws_aorg_emails', $email_rows );
        }

        // ── Taxonomies ───────────────────────────────────────────────────────

        // Organization type (single slug).
        if ( ! empty( $org['aorg_type'] ) ) {
            ws_matrix_assign_terms( $post_id, [ $org['aorg_type'] ], 'ws_aorg_type' );
        }

        // Cost models (array of slugs — must match ws_aorg_cost_model seeder).
        if ( ! empty( $org['cost_models'] ) && is_array( $org['cost_models'] ) ) {
            ws_matrix_assign_terms( $post_id, $org['cost_models'], 'ws_aorg_cost_model' );
        }

        // Disclosure types (array of slugs — must match ws_disclosure_type seeder).
        if ( ! empty( $org['disclosure_types'] ) ) {
            ws_matrix_assign_terms( $post_id, $org['disclosure_types'], 'ws_disclosure_type' );
        }

        // Optional disclosure targets (array of slugs).
        if ( ! empty( $org['disclosure_targets'] ) ) {
            ws_matrix_assign_terms( $post_id, $org['disclosure_targets'], 'ws_disclosure_target' );
        }

        // Case stages (array of slugs).
        if ( ! empty( $org['case_stages'] ) ) {
            ws_matrix_assign_terms( $post_id, $org['case_stages'], 'ws_case_stage' );
        }

        // Services offered (array of ws_aorg_service slugs — must match seeder).
        if ( ! empty( $org['services'] ) ) {
            ws_matrix_assign_terms( $post_id, $org['services'], 'ws_aorg_service' );
        }

        // Employment sectors (array of ws_employment_sector slugs).
        if ( ! empty( $org['sectors'] ) ) {
            ws_matrix_assign_terms( $post_id, $org['sectors'], 'ws_employment_sector' );
        }

        // Language: English (all seeded national orgs operate in English).
        $default_languages = [ 'english' ];
        if ( ! empty( $default_languages ) ) {
            ws_matrix_assign_terms( $post_id, $default_languages, 'ws_language' );
        }

        // Jurisdiction: US.
        wp_set_object_terms( $post_id, $us_term_id, WS_JURISDICTION_TAXONOMY );

        // ── Seeder stamp ─────────────────────────────────────────────────────
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

