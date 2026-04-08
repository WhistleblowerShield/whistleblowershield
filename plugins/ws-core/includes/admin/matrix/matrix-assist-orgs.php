<?php
/**
 * matrix-assist-orgs.php — Seeds nationwide and federal-scope whistleblower support organizations.
 *
 * @package WhistleblowerShield
 * @since   3.0.0
 * @version 3.15.1
 */

defined( 'ABSPATH' ) || exit;


// ════════════════════════════════════════════════════════════════════════════
// Assist-Org Data
// ════════════════════════════════════════════════════════════════════════════

global $_ws_assist_org_matrix;
$_ws_assist_org_matrix = [

    // ── Dedicated whistleblower nonprofits / legal aid ────────────────────

    [
        'internal_id'          => 'gap-national',
        'common_name'          => 'GAP',
        'title'                => 'Government Accountability Project',
        'slug'                 => 'government-accountability-project',
        'description'          => 'Promotes government and corporate accountability by advancing a culture of transparency, providing legal representation to whistleblowers, and advocating for strong whistleblower laws.',
        'post_content'         => 'Promotes government and corporate accountability by advancing a culture of transparency, providing legal representation to whistleblowers, and advocating for strong whistleblower laws.',
        'ws_aorg_website_url'  => 'https://whistleblower.org',
        'ws_aorg_intake_url'   => 'https://whistleblower.org/intake/',
        'ws_aorg_phone'        => '(202) 457-0034',
        'ws_aorg_email'        => 'info@whistleblower.org',
        'aorg_type'            => 'nonprofit',
        'cost_model'           => 'pro-bono',
        'is_nationwide'        => 1,
        'accepts_anon'         => 1,
        'has_attorneys'        => 1,
        'whistleblower_scope'  => 3,
        'whistleblower_note'   => 'Government Accountability Project has empowered over 8,000 whistleblowers with legal protection and advocacy expertise.',
        'services'             => [ 'legal-rep', 'consultation', 'advocacy', 'media' ],
        'sectors'              => [ 'federal-employee', 'private-sector', 'nonprofit-ngo' ],
        'disclosure_types'     => [ 'public-corruption-ethics', 'procurement-spending-fraud', 'environmental-protection', 'occupational-health-safety', 'securities-commodities-fraud' ],
        'disclosure_targets'   => [ 'internal-compliance', 'agency-federal', 'agency-state', 'legislative-federal', 'public-media' ],
        'case_stages'          => [ 'pre-report', 'post-report', 'retaliation-active', 'litigation' ],
    ],

    [
        'internal_id'          => 'nwc-dc',
        'common_name'          => 'NWC',
        'title'                => 'National Whistleblower Center',
        'slug'                 => 'national-whistleblower-center',
        'description'          => 'Advocates for the rights of whistleblowers, promotes whistleblower protections, and educates the public and policymakers about the importance of whistleblowing.',
        'post_content'         => 'Advocates for the rights of whistleblowers, promotes whistleblower protections, and educates the public and policymakers about the importance of whistleblowing.',
        'ws_aorg_website_url'  => 'https://www.whistleblowers.org',
        'ws_aorg_intake_url'   => 'https://www.report-fraud-now.info/',
        'ws_aorg_phone'        => '(202) 342-1903',
        'ws_aorg_email'        => 'contact@whistleblowers.org',
        'aorg_type'            => 'nonprofit',
        'cost_model'           => 'mixed',
        'is_nationwide'        => 1,
        'accepts_anon'         => 1,
        'has_attorneys'        => 1,
        'whistleblower_scope'  => 3,
        'whistleblower_note'   => 'The National Whistleblower Center (NWC) is the leading nonprofit dedicated to protecting and rewarding whistleblowers around the world. We assist whistleblowers in finding legal aid, advocate for stronger whistleblower protection laws, and educate the public about whistleblowers\' critical role in protecting democracy and the rule of law.',
        'services'             => [ 'referral', 'advocacy', 'consultation' ],
        'sectors'              => [ 'all-sectors' ],
        'disclosure_types'     => [ 'securities-commodities-fraud', 'tax-evasion-fraud', 'public-corruption-ethics', 'procurement-spending-fraud', 'healthcare-medicare-fraud', 'environmental-protection', 'banking-aml-compliance' ],
        'disclosure_targets'   => [ 'agency-federal', 'agency-state', 'internal-compliance' ],
        'case_stages'          => [ 'pre-report', 'post-report', 'retaliation-active', 'litigation' ],
    ],

    [
        'internal_id'          => 'whistleblower-aid-dc',
        'title'                => 'Whistleblower Aid',
        'slug'                 => 'whistleblower-aid',
        'description'          => 'Provides free legal assistance and support to clients who want to safely and legally disclose wrongdoing in the public interest.',
        'post_content'         => 'Provides free legal assistance and support to clients who want to safely and legally disclose wrongdoing in the public interest.',
        'ws_aorg_website_url'  => 'https://whistlebloweraid.org',
        'ws_aorg_intake_url'   => 'https://whistlebloweraid.org/become-a-whistleblower/',
        'ws_aorg_phone'        => '',
        'ws_aorg_email'        => '',
        'aorg_type'            => 'legal-aid',
        'cost_model'           => 'pro-bono',
        'is_nationwide'        => 1,
        'accepts_anon'         => 1,
        'has_attorneys'        => 1,
        'whistleblower_scope'  => 3,
        'whistleblower_note'   => 'Whistleblower Aid is a pioneering non-profit legal organization that helps public and private sector workers report and expose wrongdoing — safely, lawfully, and responsibly.',
        'services'             => [ 'legal-rep', 'consultation', 'advocacy', 'media', 'retaliation' ],
        'sectors'              => [ 'federal-employee', 'private-sector' ],
        'disclosure_types'     => [ 'public-corruption-ethics', 'classified-information', 'intelligence-community', 'environmental-protection', 'cybersecurity-disclosure', 'general-wrongdoing' ],
        'disclosure_targets'   => [ 'internal-management', 'internal-compliance', 'agency-federal', 'legislative-federal', 'public-media' ],
        'case_stages'          => [ 'pre-report', 'post-report', 'retaliation-active' ],
    ],

    [
        'internal_id'          => 'pogo-dc',
        'common_name'          => 'POGO',
        'title'                => 'Project On Government Oversight',
        'slug'                 => 'project-on-government-oversight',
        'description'          => 'Investigates and exposes waste, corruption, abuse of power, and when the government fails to serve the public interest, including supporting federal whistleblowers.',
        'post_content'         => 'Investigates and exposes waste, corruption, abuse of power, and when the government fails to serve the public interest, including supporting federal whistleblowers.',
        'ws_aorg_website_url'  => 'https://www.pogo.org',
        'ws_aorg_intake_url'   => 'https://www.pogo.org/submit-a-tip/',
        'ws_aorg_phone'        => '(202) 347-1122',
        'ws_aorg_email'        => 'info@pogo.org',
        'aorg_type'            => 'advocacy',
        'cost_model'           => 'free',
        'is_nationwide'        => 1,
        'accepts_anon'         => 1,
        'has_attorneys'        => 0,
        'whistleblower_scope'  => 3,
        'whistleblower_note'   => 'POGO was founded to help bring attention to disclosures from Pentagon whistleblowers. Since then, we have worked with whistleblowers on countless investigative projects.',
        'services'             => [ 'advocacy', 'hotline', 'media' ],
        'sectors'              => [ 'federal-employee', 'military-defense' ],
        'disclosure_types'     => [ 'public-corruption-ethics', 'procurement-spending-fraud', 'military-defense-reporting', 'general-wrongdoing' ],
        'disclosure_targets'   => [ 'agency-federal', 'legislative-federal', 'public-media' ],
        'case_stages'          => [ 'pre-report' ],
    ],

    [
        'internal_id'          => 'tafc-national',
        'common_name'          => 'TAF Coalition',
        'title'                => 'Taxpayers Against Fraud Education Fund',
        'slug'                 => 'taxpayers-against-fraud-education-fund',
        'description'          => 'Supports whistleblowers and their counsel in False Claims Act and related anti-fraud cases, and maintains a network of qui tam attorneys.',
        'post_content'         => 'Supports whistleblowers and their counsel in False Claims Act and related anti-fraud cases, and maintains a network of qui tam attorneys.',
        'ws_aorg_website_url'  => 'https://taf.org',
        'ws_aorg_intake_url'   => 'https://www.taf.org/whistleblower-attorneys/',
        'ws_aorg_phone'        => '',
        'ws_aorg_email'        => '',
        'aorg_type'            => 'advocacy',
        'cost_model'           => 'mixed',
        'is_nationwide'        => 1,
        'accepts_anon'         => 0,
        'has_attorneys'        => 1,
        'whistleblower_scope'  => 2,
        'whistleblower_note'   => 'Through our membership directory of top whistleblower attorneys, we help prospective whistleblowers find the best representation to win these tough cases.',
        'services'             => [ 'referral', 'advocacy' ],
        'sectors'              => [ 'all-sectors' ],
        'disclosure_types'     => [ 'securities-commodities-fraud', 'healthcare-medicare-fraud', 'procurement-spending-fraud', 'tax-evasion-fraud', 'banking-aml-compliance' ],
        'disclosure_targets'   => [ 'agency-federal', 'agency-state' ],
        'case_stages'          => [ 'pre-report', 'post-report', 'litigation' ],
    ],

    [
        'internal_id'          => 'woa-national',
        'common_name'          => 'WoA',
        'title'                => 'Whistleblowers of America',
        'slug'                 => 'whistleblowers-of-america',
        'description'          => 'Provides peer support, advocacy, and guidance to whistleblowers, with a focus on retaliation response and trauma-informed support.',
        'post_content'         => 'Provides peer support, advocacy, and guidance to whistleblowers, with a focus on retaliation response and trauma-informed support.',
        'ws_aorg_website_url'  => 'https://www.whistleblowersofamerica.org',
        'ws_aorg_intake_url'   => 'https://www.whistleblowersofamerica.org/learn-more/peer-support',
        'ws_aorg_phone'        => '',
        'ws_aorg_email'        => '',
        'aorg_type'            => 'advocacy',
        'cost_model'           => 'mixed',
        'is_nationwide'        => 1,
        'accepts_anon'         => 1,
        'has_attorneys'        => 0,
        'whistleblower_scope'  => 3,
        'whistleblower_note'   => 'We help whistleblowers overcome the traumatic stress caused by retaliation and help with problem-solving — assisting whistleblowers who have suffered retaliation after having identified harm to individuals or the public.',
        'services'             => [ 'retaliation', 'advocacy', 'referral', 'consultation' ],
        'sectors'              => [ 'all-sectors' ],
        'disclosure_types'     => [ 'general-wrongdoing', 'occupational-health-safety' ],
        'disclosure_targets'   => [ 'internal-hr', 'internal-management', 'public-media' ],
        'case_stages'          => [ 'pre-report', 'post-report', 'retaliation-active' ],
    ],

    [
        'internal_id'          => 'win-global',
        'common_name'          => 'WIN',
        'title'                => 'Whistleblowing International Network',
        'slug'                 => 'whistleblowing-international-network',
        'description'          => 'Global network of civil society organizations supporting whistleblowing, transparency, and accountability, including member groups operating in the United States.',
        'post_content'         => 'Global network of civil society organizations supporting whistleblowing, transparency, and accountability, including member groups operating in the United States.',
        'ws_aorg_website_url'  => 'https://whistleblowingnetwork.org',
        'ws_aorg_intake_url'   => 'https://whistleblowingnetwork.org/Contact',
        'ws_aorg_phone'        => '',
        'ws_aorg_email'        => '',
        'aorg_type'            => 'advocacy',
        'cost_model'           => 'free',
        'is_nationwide'        => 1,
        'accepts_anon'         => 0,
        'has_attorneys'        => 0,
        'services'             => [ 'referral', 'advocacy' ],
        'sectors'              => [ 'all-sectors' ],
        'whistleblower_scope'  => 2,
        'whistleblower_note'   => 'WIN supports whistleblowing organizations and individuals worldwide through its member network, advocacy, and capacity-building for civil society groups.',
        'disclosure_types'     => [ 'public-corruption-ethics', 'election-integrity', 'environmental-protection', 'cybersecurity-disclosure', 'consumer-data-protection' ],
        'disclosure_targets'   => [ 'agency-federal', 'public-general' ],
        'case_stages'          => [ 'pre-report', 'post-report' ],
    ],

    // ── National worker / employment focus ────────────────────────────────

    [
        'internal_id'          => 'nelp-national',
        'common_name'          => 'NELP',
        'title'                => 'National Employment Law Project',
        'slug'                 => 'national-employment-law-project',
        'description'          => 'Champions the rights of low-wage and unemployed workers through research and advocacy, including for workers who face retaliation for reporting violations.',
        'post_content'         => 'Champions the rights of low-wage and unemployed workers through research and advocacy, including for workers who face retaliation for reporting violations.',
        'ws_aorg_website_url'  => 'https://www.nelp.org',
        'ws_aorg_intake_url'   => 'https://www.nelp.org/contact-us/',
        'ws_aorg_phone'        => '(212) 285-3025',
        'ws_aorg_email'        => '',
        'aorg_type'            => 'advocacy',
        'cost_model'           => 'free',
        'is_nationwide'        => 1,
        'accepts_anon'         => 0,
        'has_attorneys'        => 0,
        'services'             => [ 'advocacy' ],
        'sectors'              => [ 'private-sector', 'nonprofit-ngo' ],
        'whistleblower_scope'  => 1,
        'whistleblower_note'   => 'NELP champions the rights of low-wage workers through research and advocacy, including protection for those who report violations.',
        'disclosure_types'     => [ 'wage-hour-violations', 'occupational-health-safety' ],
        'disclosure_targets'   => [ 'agency-federal', 'agency-state' ],
        'case_stages'          => [ 'post-report', 'retaliation-active' ],
    ],

    [
        'internal_id'          => 'nela-national',
        'common_name'          => 'NELA',
        'title'                => 'National Employment Lawyers Association',
        'slug'                 => 'national-employment-lawyers-association',
        'description'          => 'National professional association of lawyers representing employees in labor, employment, and civil rights disputes. Provides a lawyer finder to connect workers with plaintiff-side employment counsel.',
        'post_content'         => 'National professional association of lawyers representing employees in labor, employment, and civil rights disputes. Provides a lawyer finder to connect workers with plaintiff-side employment counsel.',
        'ws_aorg_website_url'  => 'https://www.nela.org',
        'ws_aorg_intake_url'   => 'https://exchange.nela.org/memberdirectory/findalawyer',
        'ws_aorg_phone'        => '(202) 420-0007',
        'ws_aorg_email'        => '',
        'aorg_type'            => 'bar-program',
        'cost_model'           => 'fee-for-service',
        'is_nationwide'        => 1,
        'accepts_anon'         => 0,
        'has_attorneys'        => 0,
        'services'             => [ 'referral' ],
        'sectors'              => [ 'all-sectors' ],
        'whistleblower_scope'  => 1,
        'whistleblower_note'   => 'NELA members exclusively represent employees — plaintiffs — in labor, employment, and civil rights matters, providing a national referral network for workers seeking counsel.',
        'disclosure_types'     => [ 'wage-hour-violations', 'occupational-health-safety', 'public-corruption-ethics', 'general-wrongdoing' ],
        'disclosure_targets'   => [ 'judicial-federal', 'judicial-state' ],
        'case_stages'          => [ 'post-report', 'retaliation-active', 'litigation' ],
    ],

    [
        'internal_id'          => 'lsc-national',
        'common_name'          => 'LSC',
        'title'                => 'Legal Services Corporation - Find Legal Aid',
        'slug'                 => 'legal-services-corporation-find-legal-aid',
        'description'          => 'National legal aid locator supported by the Legal Services Corporation, helping users find local nonprofit legal aid providers for civil legal issues, including workplace retaliation and employment-related matters.',
        'post_content'         => 'National legal aid locator supported by the Legal Services Corporation, helping users find local nonprofit legal aid providers for civil legal issues, including workplace retaliation and employment-related matters.',
        'ws_aorg_website_url'  => 'https://www.lsc.gov',
        'ws_aorg_intake_url'   => 'https://www.lsc.gov/about-lsc/what-legal-aid/get-legal-help',
        'ws_aorg_phone'        => '(202) 295-1500',
        'ws_aorg_email'        => '',
        'aorg_type'            => 'legal-aid',
        'cost_model'           => 'free',
        'is_nationwide'        => 1,
        'accepts_anon'         => 0,
        'has_attorneys'        => 0,
        'services'             => [ 'referral' ],
        'sectors'              => [ 'all-sectors' ],
        'whistleblower_scope'  => 1,
        'whistleblower_note'   => 'LSC funds civil legal aid providers across all 50 states and territories; employment and retaliation matters are within scope for many member organizations.',
        'disclosure_types'     => [ 'wage-hour-violations', 'occupational-health-safety', 'public-corruption-ethics', 'general-wrongdoing' ],
        'disclosure_targets'   => [ 'agency-federal', 'agency-state', 'judicial-state' ],
        'case_stages'          => [ 'pre-report', 'post-report', 'retaliation-active' ],
    ],

    [
        'internal_id'          => 'nlada-national',
        'common_name'          => 'NLADA',
        'title'                => 'National Legal Aid and Defender Association',
        'slug'                 => 'national-legal-aid-and-defender-association',
        'description'          => 'National association supporting civil legal aid and public defense providers, with resources and member pathways that help users locate legal-aid support channels.',
        'post_content'         => 'National association supporting civil legal aid and public defense providers, with resources and member pathways that help users locate legal-aid support channels.',
        'ws_aorg_website_url'  => 'https://www.nlada.org',
        'ws_aorg_intake_url'   => 'https://www.nlada.org',
        'ws_aorg_phone'        => '(202) 452-0620',
        'ws_aorg_email'        => '',
        'aorg_type'            => 'advocacy',
        'cost_model'           => 'free',
        'is_nationwide'        => 1,
        'accepts_anon'         => 0,
        'has_attorneys'        => 0,
        'services'             => [ 'referral', 'advocacy' ],
        'sectors'              => [ 'all-sectors' ],
        'whistleblower_scope'  => 1,
        'whistleblower_note'   => 'NLADA supports the nation\'s civil legal aid and public defense providers, offering pathways for users to locate income-eligible legal help across all jurisdictions.',
        'disclosure_types'     => [ 'wage-hour-violations', 'occupational-health-safety', 'public-corruption-ethics', 'general-wrongdoing' ],
        'disclosure_targets'   => [ 'agency-state', 'judicial-state' ],
        'case_stages'          => [ 'pre-report', 'post-report', 'retaliation-active' ],
    ],

    // ── Bar / attorney referral programs ──────────────────────────────────

    [
        'internal_id'          => 'nwc-attorney-referral',
        'common_name'          => 'NWC Referral',
        'title'                => 'National Whistleblower Center — Attorney Referral Program',
        'slug'                 => 'national-whistleblower-center-attorney-referral',
        'description'          => 'Referral program connecting whistleblowers with experienced attorneys in False Claims Act, SEC, IRS, and other whistleblower law areas.',
        'post_content'         => 'Referral program connecting whistleblowers with experienced attorneys in False Claims Act, SEC, IRS, and other whistleblower law areas.',
        'ws_aorg_website_url'  => 'https://www.whistleblowers.org',
        'ws_aorg_intake_url'   => 'https://www.whistleblowers.org/find-a-whisteblower-attorney/',
        'ws_aorg_phone'        => '',
        'ws_aorg_email'        => '',
        'aorg_type'            => 'bar-program',
        'cost_model'           => 'fee-for-service',
        'is_nationwide'        => 1,
        'accepts_anon'         => 0,
        'has_attorneys'        => 0,
        'services'             => [ 'referral' ],
        'sectors'              => [ 'all-sectors' ],
        'whistleblower_scope'  => 3,
        'whistleblower_note'   => 'NWC\'s attorney referral program connects whistleblowers with experienced counsel in False Claims Act, SEC, IRS, and other whistleblower law areas.',
        'disclosure_types'     => [ 'securities-commodities-fraud', 'tax-evasion-fraud', 'public-corruption-ethics', 'procurement-spending-fraud', 'healthcare-medicare-fraud' ],
        'disclosure_targets'   => [ 'agency-federal', 'judicial-federal' ],
        'case_stages'          => [ 'pre-report', 'post-report', 'litigation' ],
    ],

    [
        'internal_id'          => 'aba-find-legal-help',
        'common_name'          => 'ABA',
        'title'                => 'American Bar Association — Find Legal Help',
        'slug'                 => 'american-bar-association-find-legal-help',
        'description'          => 'ABA information portal that directs the public to state and local lawyer referral services and bar-sponsored legal aid programs across the United States.',
        'post_content'         => 'ABA information portal that directs the public to state and local lawyer referral services and bar-sponsored legal aid programs across the United States.',
        'ws_aorg_website_url'  => 'https://www.americanbar.org/groups/legal_services/flh-home/',
        'ws_aorg_intake_url'   => 'https://www.americanbar.org/groups/legal_services/flh-home/',
        'ws_aorg_phone'        => '',
        'ws_aorg_email'        => '',
        'aorg_type'            => 'bar-program',
        'cost_model'           => 'fee-for-service',
        'is_nationwide'        => 1,
        'accepts_anon'         => 0,
        'has_attorneys'        => 0,
        'services'             => [ 'referral' ],
        'sectors'              => [ 'all-sectors' ],
        'whistleblower_scope'  => 1,
        'whistleblower_note'   => 'The ABA Find Legal Help portal directs the public to state and local lawyer referral services and bar-sponsored legal aid programs across the United States.',
        'disclosure_types'     => [ 'wage-hour-violations', 'occupational-health-safety', 'securities-commodities-fraud', 'healthcare-medicare-fraud', 'general-wrongdoing' ],
        'disclosure_targets'   => [ 'judicial-federal', 'judicial-state' ],
        'case_stages'          => [ 'pre-report', 'post-report', 'litigation' ],
    ],

    // ── From batch US-8 — new entries ─────────────────────────────────────

    [
        'internal_id'          => 'peer-national',
        'common_name'          => 'PEER',
        'title'                => 'Public Employees for Environmental Responsibility',
        'slug'                 => 'public-employees-for-environmental-responsibility',
        'description'          => 'PEER provides free legal and strategic assistance to federal, state, and local government employees who blow the whistle on environmental harm, public health threats, and scientific integrity violations.',
        'post_content'         => 'PEER provides free legal and strategic assistance to federal, state, and local government employees who blow the whistle on environmental harm, public health threats, and scientific integrity violations.',
        'ws_aorg_website_url'  => 'https://peer.org',
        'ws_aorg_intake_url'   => 'https://peer.org/about-us/contact-us/',
        'ws_aorg_phone'        => '',
        'ws_aorg_email'        => '',
        'aorg_type'            => 'nonprofit',
        'cost_model'           => 'pro-bono',
        'is_nationwide'        => 1,
        'accepts_anon'         => 1,
        'has_attorneys'        => 1,
        'whistleblower_scope'  => 2,
        'whistleblower_note'   => 'At PEER, our dedicated attorneys represent federal, state, and local government whistleblowers who wish to expose their agency\'s wrongdoings on environmental, public health, and scientific issues.',
        'services'             => [ 'legal-rep', 'consultation', 'retaliation' ],
        'sectors'              => [ 'federal-employee', 'state-local-employee' ],
        'disclosure_types'     => [ 'environmental-protection', 'occupational-health-safety' ],
        'disclosure_targets'   => [ 'agency-federal', 'agency-state', 'legislative-state' ],
        'case_stages'          => [ 'pre-report', 'retaliation-active', 'litigation' ],
    ],

    [
        'internal_id'          => 'tsn-national',
        'common_name'          => 'TSN',
        'title'                => 'The Signals Network',
        'slug'                 => 'the-signals-network',
        'description'          => 'The Signals Network is a nonprofit that provides holistic support to whistleblowers who share public interest information — including legal assistance, psychological counseling, physical security, and media coordination.',
        'post_content'         => 'The Signals Network is a nonprofit that provides holistic support to whistleblowers who share public interest information — including legal assistance, psychological counseling, physical security, and media coordination.',
        'ws_aorg_website_url'  => 'https://thesignalsnetwork.org',
        'ws_aorg_intake_url'   => 'https://thesignalsnetwork.org/whistleblowers/',
        'ws_aorg_phone'        => '',
        'ws_aorg_email'        => '',
        'aorg_type'            => 'nonprofit',
        'cost_model'           => 'free',
        'is_nationwide'        => 1,
        'accepts_anon'         => 1,
        'has_attorneys'        => 1,
        'whistleblower_scope'  => 3,
        'whistleblower_note'   => 'TSN\'s Whistleblower Protection Program aims to help whistleblowers navigate the legal, physical, psychological and economic consequences of speaking out.',
        'services'             => [ 'consultation', 'media', 'retaliation' ],
        'sectors'              => [ 'all-sectors' ],
        'disclosure_types'     => [ 'general-wrongdoing', 'public-corruption-ethics', 'healthcare-medicare-fraud' ],
        'disclosure_targets'   => [ 'public-media', 'agency-federal' ],
        'case_stages'          => [ 'pre-report', 'retaliation-active' ],
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
        return; // Jurisdiction terms not yet seeded — bail.
    }
    $us_term_id = (int) $us_term->term_id;

    if ( ! defined( 'WS_MATRIX_SEEDING_IN_PROGRESS' ) ) {
        define( 'WS_MATRIX_SEEDING_IN_PROGRESS', true );
    }

    foreach ( $_ws_assist_org_matrix as $org ) {

        $existing = get_page_by_path( $org['slug'], OBJECT, 'ws-assist-org' );
        $content  = (string) ( $org['post_content'] ?? '' );

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
            'ws_aorg_internal_id'         => $org['internal_id']          ?? '',
            'ws_aorg_common_name'         => $org['common_name']          ?? '',
            'ws_aorg_description'         => $org['description']          ?? '',
            'ws_aorg_website_url'         => $org['ws_aorg_website_url']  ?? '',
            'ws_aorg_intake_url'          => $org['ws_aorg_intake_url']   ?? '',
            'ws_aorg_phone'               => $org['ws_aorg_phone']        ?? '',
            'ws_aorg_email'               => $org['ws_aorg_email']        ?? '',
            'ws_aorg_serves_nationwide'   => $org['is_nationwide']        ?? 0,
            'ws_aorg_limited_scope'       => $org['is_limited_scope']     ?? 0,
            'ws_aorg_community_scope'     => $org['community_scope']      ?? '',
            'ws_aorg_accepts_anonymous'   => $org['accepts_anon']         ?? 0,
            'ws_aorg_licensed_attorneys'  => $org['has_attorneys']        ?? 0,
            // Whistleblower focus score (1-3) — drives base score in ws_filter_score_org().
            // Always write even if 0; 1 is the minimum meaningful value and 0 signals
            // the matrix entry is missing this field (ingest should warn).
            'ws_aorg_whistleblower_scope' => $org['whistleblower_scope']  ?? 1,
            'ws_aorg_whistleblower_note'  => $org['whistleblower_note']   ?? '',
        ];

        foreach ( $meta as $key => $value ) {
            if ( $value !== '' ) {
                update_post_meta( $post_id, $key, $value );
            }
        }

        // ── Taxonomies ───────────────────────────────────────────────────────

        // Organization type (single slug).
        if ( ! empty( $org['aorg_type'] ) ) {
            ws_matrix_assign_terms( $post_id, [ $org['aorg_type'] ], 'ws_aorg_type' );
        }

        // Cost model (single slug — must match ws_aorg_cost_model seeder).
        if ( ! empty( $org['cost_model'] ) ) {
            ws_matrix_assign_terms( $post_id, [ $org['cost_model'] ], 'ws_aorg_cost_model' );
        }

        // Disclosure types (array of slugs — must match ws_disclosure_type seeder).
        if ( ! empty( $org['disclosure_types'] ) ) {
            ws_matrix_assign_terms( $post_id, $org['disclosure_types'], 'ws_disclosure_type' );
        }

        // Optional disclosure targets (array of slugs).
        if ( ! empty( $org['disclosure_targets'] ) ) {
            ws_matrix_assign_terms( $post_id, $org['disclosure_targets'], 'ws_disclosure_targets' );
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
        ws_matrix_assign_terms( $post_id, [ 'english' ], 'ws_languages' );

        // Jurisdiction: US.
        wp_set_object_terms( $post_id, $us_term_id, WS_JURISDICTION_TAXONOMY );

        // ── Seeder stamp ─────────────────────────────────────────────────────
        update_post_meta( $post_id, 'ws_matrix_source', 'assist-org-matrix' );
    }
}


// ── Gate ──────────────────────────────────────────────────────────────────────

add_action( 'admin_init', function() {
    if ( get_option( 'ws_seeded_assist_org_matrix' ) !== '1.0.7' ) {
        ws_seed_assist_org_matrix();
        update_option( 'ws_seeded_assist_org_matrix', '1.0.7' );
    }
} );
