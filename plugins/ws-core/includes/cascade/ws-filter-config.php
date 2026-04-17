<?php
/**
 * ws-filter-config.php
 *
 * WhistleblowerShield Core Plugin — Filter Configuration
 *
 * Canonical configuration for the situation-based filter cascade.
 * Covers the assist-organization directory (Phase 2) and will extend
 * to the jurisdiction statute cascade in a later phase.
 *
 * ARCHITECTURE NOTES
 * ------------------
 * - All GET param names are defined here. Never hardcode param strings
 *   in resolvers, renderers, or shortcodes — always reference these constants.
 * - Scoring weights live here intentionally. If they prove stable and a
 *   contributor wants to tune them, promote the weight logic to the resolver
 *   at that point. Do not move weights prematurely.
 * - Near-zero threshold (WS_FILTER_JX_THRESHOLD_*) applies to the
 *   jurisdiction statute cascade only. The directory has no suppression
 *   threshold — nationwide orgs are always shown, targeted orgs sort first.
 * - URL readability of GET params is intentionally deferred. The ws_ prefix
 *   is locked in; specific param names are provisional and will be revisited
 *   before public launch.
 *
 * @package    WhistleblowerShield
 * @since      3.15.0
 * @version    3.15.1
 *
 * @uses       Included by ws-core.php at core_init() — loaded before any
 *             renderer, query builder, or shortcode that needs the constants
 *             or config accessors. Do not include this file directly; rely on
 *             the core loader so shared helpers are available in sequence.
 *
 * VERSION LOG
 * -----------
 * 3.15.1  Added engagement-score config and profile-view log constant.
 * 3.15.0  Initial release. Directory filter cascade config.
 *         GET params, allowed values, scoring weights, ordering rules,
 *         fallback copy variants, JX threshold defaults.
 */

defined( 'ABSPATH' ) || exit;

// ── GET Parameter Names ───────────────────────────────────────────────────────
// Provisional names — do not couple external URLs to these until reviewed
// for readability at pre-launch stage.

define( 'WS_FILTER_PARAM_STAGE',   'ws_stage'   ); // ws_case_stage
define( 'WS_FILTER_PARAM_CONCERN', 'ws_concern' ); // ws_disclosure_type or ws_adverse_action_type
define( 'WS_FILTER_PARAM_SECTOR',  'ws_sector'  ); // ws_employment_sector
define( 'WS_FILTER_PARAM_TARGET',  'ws_target'  ); // ws_disclosure_target (optional)

// Profile view telemetry log (directory -> single org profile).
define( 'WS_FILTER_PROFILE_LOG', WP_CONTENT_DIR . '/logs/ws-filter/profile-views.log' );

// ── Allowed Values Per Param ──────────────────────────────────────────────────
// Maps param values to taxonomy slugs. An absent param = "not sure" = no filter
// applied on that axis. Do not use a sentinel slug for "not sure" — absence is
// the signal.
//
// Values listed here are the only accepted inputs. Anything else is ignored
// and the axis is treated as absent (broadest match).

function ws_filter_allowed_map(): array {
    static $allowed = null;
    if ( $allowed !== null ) {
        return $allowed;
    }

    $allowed = [
        WS_FILTER_PARAM_STAGE => [
            'pre-report'         => 'pre-report',
            'post-report'        => 'post-report',
            'retaliation-active' => 'retaliation-active',
            'litigation'         => 'litigation',
            'research'           => 'research',
        ],

        // Concern maps to different taxonomies depending on stage.
        // pre-report / research → ws_disclosure_type
        // retaliation-active / litigation → ws_adverse_action_type
        // post-report → either (resolver picks based on stage)
        // The resolver handles the taxonomy routing; config just validates values.
        WS_FILTER_PARAM_CONCERN => [
            // ws_disclosure_type children (pre-report branch)
            'wage-hour-violations'          => 'wage-hour-violations',
            'occupational-health-safety'    => 'occupational-health-safety',
            'collective-bargaining'         => 'collective-bargaining',
            'securities-commodities-fraud'  => 'securities-commodities-fraud',
            'consumer-financial-protection' => 'consumer-financial-protection',
            'banking-aml-compliance'        => 'banking-aml-compliance',
            'shareholder-rights'            => 'shareholder-rights',
            'tax-evasion-fraud'             => 'tax-evasion-fraud',
            'healthcare-medicare-fraud'     => 'healthcare-medicare-fraud',
            'food-drug-safety'              => 'food-drug-safety',
            'environmental-protection'      => 'environmental-protection',
            'nuclear-energy-safety'         => 'nuclear-energy-safety',
            'transportation-safety'         => 'transportation-safety',
            'public-corruption-ethics'      => 'public-corruption-ethics',
            'procurement-spending-fraud'    => 'procurement-spending-fraud',
            'election-integrity'            => 'election-integrity',
            'consumer-data-protection'      => 'consumer-data-protection',
            'cybersecurity-disclosure'      => 'cybersecurity-disclosure',
            'intelligence-community'        => 'intelligence-community',
            'classified-information'        => 'classified-information',
            'export-sanctions-compliance'   => 'export-sanctions-compliance',
            'general-wrongdoing'            => 'general-wrongdoing',
            'military-defense-reporting'    => 'military-defense-reporting',
            // ws_adverse_action_type (retaliation branch)
            'termination'                   => 'termination',
            'constructive-discharge'        => 'constructive-discharge',
            'demotion'                      => 'demotion',
            'suspension'                    => 'suspension',
            'disciplinary-action'           => 'disciplinary-action',
            'transfer'                      => 'transfer',
            'schedule-change'               => 'schedule-change',
            'pay-reduction'                 => 'pay-reduction',
            'harassment'                    => 'harassment',
            'blacklisting'                  => 'blacklisting',
            'security-clearance-action'     => 'security-clearance-action',
            'contract-non-renewal'          => 'contract-non-renewal',
            'privilege-revocation'          => 'privilege-revocation',
            'immigration-threat'            => 'immigration-threat',
        ],

        WS_FILTER_PARAM_SECTOR => [
            'federal-employee'     => 'federal-employee',
            'state-local-employee' => 'state-local-employee',
            'private-sector'       => 'private-sector',
            'nonprofit-ngo'        => 'nonprofit-ngo',
            'military-defense'     => 'military-defense',
            'all-sectors'          => 'all-sectors',
        ],

        WS_FILTER_PARAM_TARGET => [
            'internal-supervisor' => 'internal-supervisor',
            'internal-hr'         => 'internal-hr',
            'internal-compliance' => 'internal-compliance',
            'internal-legal'      => 'internal-legal',
            'internal-management' => 'internal-management',
            'agency-federal'      => 'agency-federal',
            'agency-state'        => 'agency-state',
            'agency-local'        => 'agency-local',
            'legislative-federal' => 'legislative-federal',
            'legislative-state'   => 'legislative-state',
            'judicial-federal'    => 'judicial-federal',
            'judicial-state'      => 'judicial-state',
            'public-media'        => 'public-media',
            'public-general'      => 'public-general',
            'law-enforcement'     => 'law-enforcement',
        ],
    ];

    return $allowed;
}

// ── Scoring Weights — Directory Sort ─────────────────────────────────────────
// Controls result ordering in ws_render_directory_taxonomy_guide().
// Targeted orgs (jurisdiction-scoped) always outscore nationwide orgs
// with identical taxonomy matches via the targeted_org_bonus.
//
// Adjust here when filter log data suggests reweighting. Promote to
// resolver constants when weights prove stable across multiple review cycles.

function ws_filter_score_weights(): array {
    static $weights = null;
    if ( $weights !== null ) {
        return $weights;
    }

    $weights = [

    // Base weight per scope level (1, 2, or 3)
    // Applied as: scope_score = scope_level * scope_per_level
    'scope_per_level'       => 10,

    // Taxonomy match bonuses — applied per matched term
    'disclosure_type_match' =>  8,   // ws_disclosure_type or ws_adverse_action_type
    'case_stage_match'      =>  8,   // ws_case_stage
    'sector_match'          =>  5,   // ws_employment_sector
    'target_match'          =>  3,   // ws_disclosure_target (optional axis)

    // Contextual bonus: has_attorneys when stage signals legal need
    // Applies when ws_stage is retaliation-active, litigation, or pre-report
    'has_attorneys_bonus'   =>  4,

    // Tiebreaker: jurisdiction-scoped org vs nationwide
    'targeted_org_bonus'    =>  2,
    ];

    return $weights;
}

// Engagement scoring appends to relevance scoring and is capped so it cannot
// overturn strong relevance mismatches.
function ws_filter_engagement_weights(): array {
    static $weights = null;
    if ( $weights !== null ) {
        return $weights;
    }

    $weights = [
        'has_hotline'      => 2,
        'has_intake_url'   => 1,
        'has_intake_email' => 1,
        'has_intake_phone' => 1,
        'has_tty'          => 1,
    ];

    return $weights;
}

// Hard cap for engagement scoring.
define( 'WS_FILTER_ENGAGEMENT_SCORE_CAP', 4 );

// Stages where has_attorneys_bonus applies.
// pre-report intentionally excluded: that stage signals Maya (early/uncertain),
// who needs a consult more than a referral. Skewing referral orgs with attorney
// bonuses at that stage was a misstep — she benefits from broader options first.
function ws_filter_attorney_stages(): array {
    return [ 'retaliation-active', 'litigation' ];
}

// ── Directory Ordering Rules ──────────────────────────────────────────────────
// The directory never suppresses results. Nationwide/general orgs are always
// shown — they sort to the bottom when targeted orgs exist.
//
// Sort order:
//   1. Targeted orgs (jurisdiction-scoped), sorted by score desc
//   2. Nationwide/general orgs with taxonomy matches, sorted by score desc
//   3. Nationwide/general orgs with no taxonomy matches, sorted by scope desc
//
// Zero-result state: triggered only when targeted_count === 0.
// Nationwide orgs are still shown below the fallback message.
// Even in fallback, known filter axes are applied to the nationwide set
// to suppress obviously irrelevant orgs (e.g. securities fraud orgs
// when the user indicated a labor concern).

define( 'WS_FILTER_DIR_ZERO_RESULT_THRESHOLD', 0 ); // targeted orgs only

// ── Fallback Message Copy ─────────────────────────────────────────────────────
// Used when targeted_count === 0. Renderer picks the variant; default is
// 'compassionate'. A/B testing or context-based selection is a future concern.

function ws_filter_fallback_copy(): array {
    static $copy = null;
    if ( $copy !== null ) {
        return $copy;
    }

    $copy = [

    'default' => 'compassionate',

    'compassionate' => "You are not in the wrong place. Your situation appears broader than our current filters, so we are showing trusted organizations known to help with many kinds of whistleblower concerns. You can also broaden one filter below to see additional options.",

    'concise' => "We could not find a close match for all selected details. The organizations below are trusted and can help across a broad range of whistleblower situations.",

    'formal' => "WhistleblowerShield is committed to helping you identify an appropriate assistance organization. Based on the criteria provided, your matter appears broader than our current filter model. The organizations below are established, reputable, and capable of supporting a wide spectrum of needs.",
    ];

    return $copy;
}

// ── Jurisdiction Statute Cascade Thresholds ───────────────────────────────────
// Near-zero threshold for the JX statute cascade (Phase 2 proper).
// Not used by the directory.
//
// Threshold = max( WS_FILTER_JX_THRESHOLD_MIN, floor( published_count * WS_FILTER_JX_THRESHOLD_PCT ) )
// If result count <= threshold → thin result state → broaden controls shown.
//
// Per-JX overrides for thin jurisdictions. Editor-assigned values (future)
// stored as post meta on the jurisdiction post take precedence over both.

define( 'WS_FILTER_JX_THRESHOLD_MIN', 3    );
define( 'WS_FILTER_JX_THRESHOLD_PCT', 0.10 );

function ws_filter_jx_threshold_overrides(): array {
    return [
        'wy' => 2,
        'as' => 1,
        'gu' => 1,
        'mp' => 1,
        'vi' => 1,
        'pr' => 2,
    ];
}

// ── Thin Result Copy ──────────────────────────────────────────────────────────
// Used when result count <= threshold in the JX statute cascade.
// Always shows matched results alongside the message — never hides valid matches.

function ws_filter_thin_result_copy(): string {
    return "WhistleblowerShield is dedicated to helping you find the right assistance organization. Based on what you shared, your situation appears broader than our current filter set. The organizations below are trusted, reputable, and equipped to help across a wide range of needs.";
}
