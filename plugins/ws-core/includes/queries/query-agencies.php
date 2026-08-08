<?php
/**
 * query-agencies.php
 *
 * Query Layer — Agency Dataset Functions
 *
 * PURPOSE
 * -------
 * Holds dataset functions for the ws-agency CPT and its child CPTs.
 * Loaded in the Universal Layer so procedure data is available in
 * both admin and frontend contexts.
 *
 * FUNCTIONS
 * ---------
 *   ws_get_agency_procedures( $agency_id )
 *       Returns all published ag-procedure records belonging to a given
 *       agency, ordered alphabetically by title. Result is cached in a
 *       per-agency transient (24 hours). Invalidated on procedure save.
 *
 * LOAD ORDER
 * ----------
 * Must be loaded after query-shared.php (depends on ws_build_author_array).
 * Loaded fourth in the query file array: helpers → shared → jurisdiction → agencies.
 *
 * DATA CONTRACT
 * -------------
 * Procedures are linked to their parent agency via the ws_ag_procedure_agency_id
 * post meta key (ACF post_object field, stores integer post ID).
 *
 * Taxonomy fields (jurisdiction, protected_disclosures, procedure_type) use
 * save_terms=1 in ACF, so their values are read via wp_get_post_terms() /
 * wp_get_object_terms(), not get_post_meta(). Simple scalar fields use
 * get_post_meta() directly. ws_procedure_type is single-value — the query
 * layer returns its slug as a plain string (first term slug, or '').
 *
 * CACHING
 * -------
 * Cache key:   ws_agency_procedures_{$agency_id}_
 * TTL:         DAY_IN_SECONDS (24 hours)
 * Invalidated: save_post_ag-procedure hook (clears the parent agency's key)
 *
 * @package    WhistleblowerShield
 * @since      3.9.0
 * @version    3.20.2
 * @author     Whistleblower Shield
 * @link       https://whistleblowershield.org
 * @copyright  Copyright (c) Whistleblower Shield
 *
 * VERSION HISTORY
 * ---------------
 * 3.20.1 Loud-failure pass. ws_build_agency_procedure_row() previously
 *        conflated a genuine wp_get_post_terms()/wp_get_object_terms()
 *        failure with "this procedure legitimately has no jurisdiction or
 *        protected-disclosure terms" — both produced the same silent empty
 *        array on a visitor-facing procedure card. Now logged separately.
 * 3.20.2 Same conflation missed on ws_procedure_type in both
 *        ws_build_agency_procedure_row() and ws_get_procedures_for_record()
 *        — a genuine failure resolving the single-value procedure-type term
 *        (disclosure/retaliation/both) previously produced the same '' as
 *        a legitimately unset type, silently mis-sorting or dropping the
 *        procedure card with no record of why. Found during a fine-tooth-
 *        comb pass across the query layer siblings of query-jurisdiction.php.
 * 3.9.0  Initial. ws_get_agency_procedures() + per-agency transient cache.
 *        Phase 2 of ag-procedure feature build.
 * 3.10.0 ws_proc_type get_post_meta() reads replaced with wp_get_object_terms()
 *        on ws_procedure_type in both ws_build_agency_procedure_row() and
 *        ws_get_procedures_for_record(). Returns first term slug as plain
 *        string; empty string when no term assigned.
 * 3.10.1 Query hardening + field coverage sync:
 *        - procedure rows now expose parent_ids (normalized)
 *        - procedure rows now expose parent_override
 *        - relationship ID normalization aligned with query-jurisdiction helpers
 */

defined( 'ABSPATH' ) || exit;

/**
 * True when the ID points to a ws-agency post.
 *
 * @param  int $post_id
 * @return bool
 */
function ws_is_agency_id( $post_id ) {
    return ( $post_id > 0 && get_post_type( $post_id ) === 'ws-agency' );
}

/**
 * True when the ID points to a legal parent post that can own procedures.
 *
 * @param  int $post_id
 * @return bool
 */
function ws_is_legal_parent_id( $post_id ) {
    if ( $post_id <= 0 ) {
        return false;
    }

    $type = get_post_type( $post_id );
    return in_array( $type, [ 'jx-statute', 'jx-common-law' ], true );
}


// ════════════════════════════════════════════════════════════════════════════
// ws_get_agency_procedures( $agency_id )
//
// Returns all published ag-procedure records for a given agency,
// ordered alphabetically by title. Grouped by type (disclosure / retaliation
// / both) in the render layer — this function returns the flat list.
//
// The caller (ws_render_agency_procedures in render-agency.php) groups
// the result before rendering. Returning the flat list here keeps the
// query layer free of display logic.
//
// Return shape per row:
//   id                     int     Procedure post ID.
//   title                  string  Procedure post title.
//   url                    string  Permalink to the individual procedure post.
//   type                   string  'disclosure' | 'retaliation' | 'both'
//   jurisdictions          array   WP_Term[] from WS_JURISDICTION_TAXONOMY taxonomy.
//   protected_disclosures  array   WP_Term[] from ws_protected_disclosure taxonomy.
//   entry_point            string  How the filer initiates: online/mail/phone/in_person/multi
//   intake_url             string  Direct link to the intake form/portal for this procedure.
//   phone                  string  Specific phone number for this procedure (may differ from agency).
//   identity_policy        string  'anonymous' | 'confidential' | 'identified' | 'varies'
//   intake_only            bool    True if agency receives and refers only — does not investigate.
//   deadline_days          int     Statutory deadline in calendar days. 0 = none/unknown.
//   clock_start            string  'adverse_action' | 'knowledge' | 'last_act' | 'varies' | ''
//   has_prereqs            bool    True if prerequisites must be satisfied before filing.
//   prereq_details         string  Description of prerequisite conditions (when has_prereqs).
//   walkthrough            string  Raw HTML from WYSIWYG field. Sanitize with wp_kses_post() before output.
//   exclusivity_details    string  Mutual exclusivity warning text. Plain text.
//   last_reviewed          string  Y-m-d date. Empty if not yet verified.
//   record                 array   ws_build_author_array() sub-array (authorship stamps).
//
/**
 * Returns all published procedures for a single agency.
 *
 * @param  int $agency_id Post ID of the parent ws-agency.
 * @return array<int,array<string,mixed>> Flat procedure rows (empty when none).
 */
function ws_get_agency_procedures( $agency_id ) {

    $agency_id = (int) $agency_id;
    if ( ! ws_is_agency_id( $agency_id ) ) {
        return [];
    }

    $cache_key = 'ws_agency_procedures_' . $agency_id . '_';
    $cached    = get_transient( $cache_key );

    if ( false !== $cached ) {
        return $cached;
    }

    $q = new WP_Query( [
        'post_type'      => 'ag-procedure',
        'post_status'    => 'publish',
        'posts_per_page' => -1,
        'orderby'        => 'title',
        'order'          => 'ASC',
        'no_found_rows'  => true,
        // Link: procedure → parent agency via ws_ag_procedure_agency_id post meta.
        // ACF post_object fields store the referenced post's integer ID.
        'meta_query'     => [ [
            'key'   => 'ws_ag_procedure_agency_id',
            'value' => $agency_id,
            'type'  => 'NUMERIC',
        ] ],
    ] );

    $rows = [];

    foreach ( $q->posts as $post ) {
        $row = ws_build_agency_procedure_row( (int) $post->ID );
        if ( $row ) {
            $rows[] = $row;
        }
    }

    set_transient( $cache_key, $rows, DAY_IN_SECONDS );

    return $rows;
}

/**
 * Returns one published ag-procedure row for single-procedure rendering.
 *
 * @param  int   $procedure_id Procedure post ID.
 * @return array               Procedure row or [] when not found/not published.
 */
function ws_get_agency_procedure( $procedure_id ) {
    return ws_build_agency_procedure_row( (int) $procedure_id );
}

/**
 * Builds one normalized procedure row used by list and single-procedure views.
 *
 * @param  int   $pid Procedure post ID.
 * @return array      Procedure row, or [] when invalid/not published.
 */
function ws_build_agency_procedure_row( $pid ) {
    if ( ! $pid || get_post_type( $pid ) !== 'ag-procedure' || get_post_status( $pid ) !== 'publish' ) {
        return [];
    }

    $agency_id      = (int) get_post_meta( $pid, 'ws_ag_procedure_agency_id', true );
    $agency_url     = $agency_id ? get_permalink( $agency_id ) : '';
    $agency_name    = $agency_id ? (string) get_post_meta( $agency_id, 'ws_agency_official_name', true ) : '';
    $agency_common  = $agency_id ? (string) get_post_meta( $agency_id, 'ws_agency_common_name', true ) : '';
    $agency_mission = $agency_id ? (string) get_post_meta( $agency_id, 'ws_agency_mission', true ) : '';
    $agency_title   = $agency_id ? get_the_title( $agency_id ) : '';

    // Taxonomy fields use save_terms=1 in ACF — read via WP term functions,
    // not get_post_meta(). is_wp_error() guard handles unregistered taxonomies
    // or posts with no terms assigned.
    $jx_terms    = wp_get_post_terms( $pid, WS_JURISDICTION_TAXONOMY );
    $disc_types  = wp_get_object_terms( $pid, 'ws_protected_disclosure' );

    // A genuine query error here is not the same as "this procedure legitimately
    // has no jurisdiction/disclosure terms yet" — both previously produced the
    // same empty array silently. This row feeds visitor-facing procedure cards
    // on agency pages; an error-caused empty scope reads identically to a
    // real one, with nothing recorded about which it was.
    if ( is_wp_error( $jx_terms ) ) {
        ws_log_loud_failure( new WS_Loud_Failure( 'query-agencies', "wp_get_post_terms() failed reading jurisdiction terms for procedure {$pid}.", [
            'procedure_id' => $pid,
            'error'        => $jx_terms->get_error_message(),
        ] ) );
    }
    if ( is_wp_error( $disc_types ) ) {
        ws_log_loud_failure( new WS_Loud_Failure( 'query-agencies', "wp_get_object_terms() failed reading protected-disclosure terms for procedure {$pid}.", [
            'procedure_id' => $pid,
            'error'        => $disc_types->get_error_message(),
        ] ) );
    }

    $jx_slugs    = ( ! is_wp_error( $jx_terms ) && is_array( $jx_terms ) ) ? array_values( array_unique( array_map( function( $t ) { return (string) $t->slug; }, $jx_terms ) ) ) : [];
    $disc_slugs  = ( ! is_wp_error( $disc_types ) && is_array( $disc_types ) ) ? array_values( array_unique( array_map( function( $t ) { return (string) $t->slug; }, $disc_types ) ) ) : [];

    // ws_procedure_type is a single-value taxonomy. Return the slug string
    // so render-agency.php can use it as an array key without further
    // processing. Empty string when no term is assigned (draft/incomplete).
    $proc_type_terms = wp_get_object_terms( $pid, 'ws_procedure_type', [ 'fields' => 'slugs' ] );

    // Same conflation risk as jx_terms/disc_types above: ws_procedure_type
    // decides which group (disclosure/retaliation/both) this procedure card
    // renders under. A genuine query failure here previously produced the
    // same '' as "type genuinely not set yet," silently mis-sorting or
    // dropping the card with no record of why.
    if ( is_wp_error( $proc_type_terms ) ) {
        ws_log_loud_failure( new WS_Loud_Failure( 'query-agencies', "wp_get_object_terms() failed reading ws_procedure_type for procedure {$pid}.", [
            'procedure_id' => $pid,
            'error'        => $proc_type_terms->get_error_message(),
        ] ) );
    }

    $proc_type = ( ! is_wp_error( $proc_type_terms ) && ! empty( $proc_type_terms ) )
                       ? $proc_type_terms[0]
                       : '';

    return [
        'id'               => $pid,
        'title'            => get_the_title( $pid ),
        'url'              => get_permalink( $pid ),
        'agency_id'        => $agency_id,
        'agency_name'      => $agency_name !== '' ? $agency_name : $agency_title,
        'agency_common'    => $agency_common,
        'agency_mission'   => $agency_mission,
        'agency_url'       => $agency_url ? (string) $agency_url : '',
        'type'             => $proc_type,
        'jurisdictions'    => ( $jx_terms   && ! is_wp_error( $jx_terms   ) ) ? $jx_terms   : [],
        'jurisdiction_slugs' => $jx_slugs,
        'protected_disclosures' => ( $disc_types && ! is_wp_error( $disc_types ) ) ? $disc_types : [],
        'protected_disclosure_slugs' => $disc_slugs,
        'employment_sectors' => ws_q_normalize_id_list( get_field( 'ws_ag_procedure_employment_sectors', $pid ) ),
        'statute_ids'      => ws_q_normalize_id_list( get_post_meta( $pid, 'ws_ag_procedure_statute_ids', true ) ),
        'comlaw_ids'       => ws_q_normalize_id_list( get_post_meta( $pid, 'ws_ag_procedure_comlaw_ids', true ) ),
        'parent_ids'       => ws_q_normalize_id_list( get_post_meta( $pid, '_ws_ag_procedure_parent_ids', true ) ),
        'entry_point'      => get_post_meta( $pid, 'ws_ag_procedure_entry_point',           true ),
        'intake_url'       => get_post_meta( $pid, 'ws_ag_procedure_intake_url',            true ),
        'phone'            => get_post_meta( $pid, 'ws_ag_procedure_phone',                 true ),
        'identity_policy'  => get_post_meta( $pid, 'ws_ag_procedure_identity_policy',       true ),
        'intake_only'      => (bool) get_post_meta( $pid, 'ws_ag_procedure_intake_only',    true ),
        'deadline_days'    => (int)  get_post_meta( $pid, 'ws_ag_procedure_deadline_days',  true ),
        'clock_start'      => get_post_meta( $pid, 'ws_ag_procedure_deadline_clock_start',  true ),
        'has_prereqs'      => (bool) get_post_meta( $pid, 'ws_ag_procedure_has_prerequisites',  true ),
        'prereq_details'      => get_post_meta( $pid, 'ws_ag_procedure_prerequisites_details',    true ),
        // walkthrough is a WYSIWYG field — stored as raw HTML.
        // Sanitize with wp_kses_post() before output; never echo raw.
        'walkthrough'      => get_post_meta( $pid, 'ws_ag_procedure_walkthrough_wysiwyg',           true ),
        'exclusivity_details' => get_post_meta( $pid, 'ws_ag_procedure_exclusivity_details',      true ),
        'parent_override'  => (bool) get_post_meta( $pid, 'ws_ag_procedure_parent_override',  true ),
        'last_reviewed'    => get_post_meta( $pid, 'ws_ag_procedure_last_reviewed',         true ),
        // Standard authorship stamp sub-array (created_by, edited_by, dates).
        // Standard source-verified stamp sub-array (source_*, verified_*, needs_review).
        'author'           => ws_build_author_array( $pid ),
        'verify'           => ws_build_source_verify_array( $pid ),
    ];
}


// ════════════════════════════════════════════════════════════════════════════
// ws_get_procedures_for_record( $record_id )
//
// Returns all published ag-procedure records that explicitly link to the
// given jx-statute or jx-common-law post. Used by the statute (common law)
// section renderer to surface "Filing Procedures Under This Statute"
// (or Common Law) on jurisdiction pages.
//
// Relationship fields (ws_ag_procedure_statute_ids) or (ws_ag_procedure_comlaw_ids) 
// are stored by ACF as a serialized array of post IDs. Depending on save path
// these may be serialized as strings (common ACF UI save) or integers
// (programmatic/meta writes). Query both shapes to avoid false negatives:
//   — string shape:  ...s:3:"123";...
//   — integer shape: ...i:123;...
//
// Return shape per row:
//   id             int     Procedure post ID.
//   title          string  Procedure post title.
//   url            string  Permalink.
//   type           string  'disclosure' | 'retaliation' | 'both'
//   agency_id      int     Parent agency post ID.
//   agency_name    string  Parent agency official name, fallback to post title.
//   agency_url     string  Parent agency permalink (empty string if not found).
//   agency_common  string  Parent agency common name (empty string if not found).
//   agency_mission string  Parent agency mission statement (empty string if not found).
//   deadline_days  int     Statutory deadline in calendar days. 0 = none/unknown.
//   intake_only    bool    True if agency receives and refers only.
//
// Result cached per record (ws_agency_procedures_{id}_, 24h).
// Invalidated by the acf/save_post stash hooks below.
//
/* Returns all published procedures linked to a record.
 *
 * @param  int $record_id Post ID of the jx-statute or jx-common-law.
 * @return array<int,array<string,mixed>> Flat procedure rows (empty when none).
 * 
 */
function ws_get_procedures_for_record( $record_id ) {

    $record_id = (int) $record_id;
    if ( ! ws_is_legal_parent_id( $record_id ) ) {
        return [];
    }

    $cache_key = 'ws_agency_procedures_' . $record_id . '_';
    $cached    = get_transient( $cache_key );

    if ( false !== $cached ) {
        return $cached;
    }

    $q = new WP_Query( [
        'post_type'      => 'ag-procedure',
        'post_status'    => 'publish',
        'posts_per_page' => -1,
        'orderby'        => 'title',
        'order'          => 'ASC',
        'no_found_rows'  => true,
        // Match both possible serialized value shapes used by ACF/meta writes.
        'meta_query'     => [
            'relation' => 'OR',
            [
                'key'     => 'ws_ag_procedure_statute_ids',
                'value'   => '"' . $record_id . '"',
                'compare' => 'LIKE',
            ],
            [
                'key'     => 'ws_ag_procedure_statute_ids',
                'value'   => ';i:' . $record_id . ';',
                'compare' => 'LIKE',
            ],
            [
                'key'     => 'ws_ag_procedure_comlaw_ids',
                'value'   => '"' . $record_id . '"',
                'compare' => 'LIKE',
            ],
            [
                'key'     => 'ws_ag_procedure_comlaw_ids',
                'value'   => ';i:' . $record_id . ';',
                'compare' => 'LIKE',
            ],
        ],
    ] );

    $rows = [];

    foreach ( $q->posts as $post ) {

        $pid       = $post->ID;
        $agency_id    = (int) get_post_meta( $pid, 'ws_ag_procedure_agency_id', true );
        $agency_name     = $agency_id ? (string) get_post_meta( $agency_id, 'ws_agency_official_name', true ) : '';
        $agency_common  = $agency_id ? (string) get_post_meta( $agency_id, 'ws_agency_common_name', true ) : '';
        $agency_mission  = $agency_id ? (string) get_post_meta( $agency_id, 'ws_agency_mission', true ) : '';
        $agency_title = $agency_id ? get_the_title( $agency_id ) : '';

        // ws_procedure_type is single-value — take first slug, empty string if unset.
        $pt_terms = wp_get_object_terms( $pid, 'ws_procedure_type', [ 'fields' => 'slugs' ] );

        if ( is_wp_error( $pt_terms ) ) {
            ws_log_loud_failure( new WS_Loud_Failure( 'query-agencies', "wp_get_object_terms() failed reading ws_procedure_type for procedure {$pid} while building the 'Filing Procedures Under This Statute' panel.", [
                'procedure_id' => $pid,
                'error'        => $pt_terms->get_error_message(),
            ] ) );
        }

        $pt_slug  = ( ! is_wp_error( $pt_terms ) && ! empty( $pt_terms ) ) ? $pt_terms[0] : '';

        $rows[] = [
            'id'            => $pid,
            'title'         => get_the_title( $pid ),
            'url'           => get_permalink( $pid ),
            'type'          => $pt_slug,
            'agency_id'     => $agency_id,
            'agency_name'   => $agency_name !== '' ? $agency_name : $agency_title,
            'agency_common' => $agency_common,
            'agency_mission' => $agency_mission,
            'agency_url'    => $agency_id ? (string) get_permalink( $agency_id ) : '',
            'employment_sectors' => ws_q_normalize_id_list( get_field( 'ws_ag_procedure_employment_sectors', $pid ) ),
            'statute_ids'   => ws_q_normalize_id_list( get_post_meta( $pid, 'ws_ag_procedure_statute_ids', true ) ),
            'comlaw_ids'    => ws_q_normalize_id_list( get_post_meta( $pid, 'ws_ag_procedure_comlaw_ids', true ) ),
            'parent_ids'    => ws_q_normalize_id_list( get_post_meta( $pid, '_ws_ag_procedure_parent_ids', true ) ),
            'deadline_days' => (int)  get_post_meta( $pid, 'ws_ag_procedure_deadline_days', true ),
            'intake_only'   => (bool) get_post_meta( $pid, 'ws_ag_procedure_intake_only',   true ),
        ];
    }

    set_transient( $cache_key, $rows, DAY_IN_SECONDS );

    return $rows;
}


// ════════════════════════════════════════════════════════════════════════════
// ws_get_agency_data( $jx_term_id )
//
// Returns all published ws-agency records assigned to the given
// WS_JURISDICTION_TAXONOMY taxonomy term, ordered alphabetically.
//
// Used by jurisdiction-scoped render paths that need the agency dataset
// without procedural children.
// ════════════════════════════════════════════════════════════════════════════

/**
 * Returns all published agencies linked to one jurisdiction term.
 *
 * @param  int $jx_term_id WS_JURISDICTION_TAXONOMY term ID.
 * @return array<int,array<string,mixed>> Flat agency rows.
 */
function ws_get_agency_data( $jx_term_id ) {

    $term_id = (int) $jx_term_id;
    if ( ! $term_id ) {
        return [];
    }

    $q = new WP_Query( [
        'post_type'      => 'ws-agency',
        'post_status'    => 'publish',
        'posts_per_page' => -1,
        'orderby'        => 'title',
        'order'          => 'ASC',
        'no_found_rows'  => true,
        'tax_query'      => [ [
            'taxonomy' => WS_JURISDICTION_TAXONOMY,
            'field'    => 'term_id',
            'terms'    => $term_id,
        ] ],
    ] );

    $rows = [];
    foreach ( $q->posts as $agency ) {
        $aid    = $agency->ID;
        $rows[] = [
            'id'                    => $aid,
            'title'                 => get_the_title( $aid ),
            'url'                   => get_permalink( $aid ),
            'status'                => get_post_status( $aid ),
            'code'                  => get_post_meta( $aid, '_ws_agency_id', true ),
            'name'                  => get_post_meta( $aid, 'ws_agency_official_name', true ),
            'logo'                  => get_field( 'ws_agency_logo', $aid ),
            'common'                => get_post_meta( $aid, 'ws_agency_common_name',           true ),
            'mission'               => get_post_meta( $aid, 'ws_agency_mission',           true ),
            'protected_disclosure'  => ws_q_normalize_id_list( get_field( 'ws_agency_protected_disclosures', $aid ) ),
            'disclosure_targets'    => ws_q_normalize_id_list( get_field( 'ws_agency_disclosure_targets', $aid ) ),
            'employment_sectors'    => ws_q_normalize_id_list( get_field( 'ws_agency_employment_sectors', $aid ) ),
            'process_type'          => ws_q_normalize_id_list( get_field( 'ws_agency_process_types', $aid ) ),
            'jurisdictions'         => ws_q_normalize_id_list( get_field( 'ws_agency_jurisdictions', $aid ) ),
            'website_url'           => get_post_meta( $aid, 'ws_agency_url', true ),
            'reporting_url'         => get_post_meta( $aid, 'ws_agency_reporting_url', true ),
            'phone'                 => get_post_meta( $aid, 'ws_agency_phone', true ),
            'confidentiality_details'  => get_post_meta( $aid, 'ws_agency_confidentiality_details', true ),
            'has_anonymous'         => (bool) get_post_meta( $aid, 'ws_agency_accepts_anonymous', true ),
            'has_reward'            => (bool) get_post_meta( $aid, 'ws_agency_has_reward', true ),
            'reward_details'        => get_post_meta( $aid, 'ws_agency_reward_details', true ),
            'languages'             => get_field( 'ws_agency_languages', $aid ),
            'additional_languages'  => get_post_meta( $aid, 'ws_agency_additional_languages', true ),
            'last_reviewed'         => get_post_meta( $aid, 'ws_agency_last_reviewed', true ),
            'plain'                 => ws_build_plain_english_array( $aid ),
            'verify'                => ws_build_source_verify_array( $aid ),
            'author'                => ws_build_author_array( $aid ),
        ];
    }

    return $rows;
}


// ════════════════════════════════════════════════════════════════════════════
// Cache Invalidation — Agency Procedures
//
// Fires on save_post_ag-procedure. Reads ws_ag_procedure_agency_id from the saved
// procedure and deletes the per-agency transient so the next page load
// reflects updated procedure data.
//
// Uses save_post_ag-procedure (not acf/save_post) to cover programmatic
// saves as well as ACF edit-screen saves.
// ════════════════════════════════════════════════════════════════════════════

/**
 * Request-level stash for prior ws_ag_procedure_agency_id during updates/deletes.
 *
 * @param  int      $post_id
 * @param  int|null $agency_id  Pass an int to write; null to read.
 * @return int
 */
function ws_ag_procedure_agency_stash( $post_id, $agency_id = null ) {
    static $stash = [];
    if ( null !== $agency_id ) {
        $stash[ $post_id ] = (int) $agency_id;
    }
    return (int) ( $stash[ $post_id ] ?? 0 );
}

// Capture old agency linkage before post/meta updates are written.
add_action( 'pre_post_update', function( $post_id ) {
    if ( get_post_type( $post_id ) !== 'ag-procedure' ) {
        return;
    }
    ws_ag_procedure_agency_stash( $post_id, (int) get_post_meta( $post_id, 'ws_ag_procedure_agency_id', true ) );
} );

add_action( 'save_post_ag-procedure', function( $post_id ) {

    // Ignore autosaves/revisions and new inserts with no previous state.
    if ( wp_is_post_revision( $post_id ) || wp_is_post_autosave( $post_id ) ) {
        return;
    }

    $old_agency_id = ws_ag_procedure_agency_stash( $post_id );

    $agency_id = (int) get_post_meta( $post_id, 'ws_ag_procedure_agency_id', true );

    if ( $agency_id ) {
        delete_transient( 'ws_agency_procedures_' . $agency_id . '_' );
    }

    if ( $old_agency_id && $old_agency_id !== $agency_id ) {
        delete_transient( 'ws_agency_procedures_' . $old_agency_id . '_' );
    }

}, 10, 1 );


// ════════════════════════════════════════════════════════════════════════════
// Cache Invalidation — Parent Procedures (stash + diff pattern)
//
// When a procedure is saved via the ACF edit screen, the set of linked
// parents may change. Both the old parents (removed links) and the new
// parents (added links) need their transients cleared.
//
// Requires a two-priority hook pattern because ACF writes field values at
// acf/save_post priority 10 — the stash captures pre-write values at
// priority 5, and the diff runs at priority 20 after the new values are
// written.
//
// STASH:  priority 5  — read _ws_ag_procedure_parent_ids from DB (old values).
// DIFF:   priority 20 — read _ws_ag_procedure_parent_ids from DB (new values),
//                       compute union of old+new, delete all affected keys.
//
// On delete: before_delete_post captures parent IDs while the post still
// exists; deleted_post reads the stash and clears those transients.
// ════════════════════════════════════════════════════════════════════════════


/**
 * Stash helper for _ws_ag_procedure_parent_ids before/after an ACF edit-screen save.
 *
 * Pass $ids to write; omit to read. Static storage persists for the
 * current PHP request only — values do not survive the request boundary.
 *
 * @param  int        $post_id  Procedure post ID.
 * @param  array|null $ids      Array of parent IDs to stash, or null to read.
 * @return array                The stashed IDs (empty array if nothing stashed).
 */
function ws_ag_procedure_parent_save_stash( $post_id, $ids = null ) {
    static $stash = [];
    if ( $ids !== null ) {
        $stash[ $post_id ] = $ids;
    }
    return $stash[ $post_id ] ?? [];
}

/**
 * Stash helper for parent IDs captured before a procedure post is deleted.
 *
 * @param  int        $post_id  Procedure post ID.
 * @param  array|null $ids      Array of parent IDs to stash, or null to read.
 * @return array                The stashed IDs (empty array if nothing stashed).
 */
function ws_ag_procedure_parent_delete_stash( $post_id, $ids = null ) {
    static $stash = [];
    if ( $ids !== null ) {
        $stash[ $post_id ] = $ids;
    }
    return $stash[ $post_id ] ?? [];
}


// Priority 5: capture parent IDs currently in the DB before ACF overwrites them.
add_action( 'acf/save_post', function( $post_id ) {

    if ( get_post_type( $post_id ) !== 'ag-procedure' ) {
        return;
    }

    $raw = get_post_meta( $post_id, '_ws_ag_procedure_parent_ids', true );
    $ids = is_array( $raw ) ? array_map( 'intval', $raw ) : [];

    ws_ag_procedure_parent_save_stash( $post_id, $ids );

}, 5 );


// Priority 20: diff old vs new parent IDs and delete affected transients.
add_action( 'acf/save_post', function( $post_id ) {

    if ( get_post_type( $post_id ) !== 'ag-procedure' ) {
        return;
    }

    $old_ids = ws_ag_procedure_parent_save_stash( $post_id );

    $raw     = get_post_meta( $post_id, '_ws_ag_procedure_parent_ids', true );
    $new_ids = is_array( $raw ) ? array_map( 'intval', $raw ) : [];

    // Clear transients for every parent that was linked before OR after this save.
    // Covers removed links (old \ new) and added links (new \ old) in one pass.
    $affected = array_unique( array_merge( $old_ids, $new_ids ) );

    foreach ( $affected as $parent_id ) {
        if ( $parent_id ) {
            delete_transient( 'ws_agency_procedures_' . $parent_id . '_');
        }
    }

}, 20 );


// Before delete: stash parent IDs while the post still exists.
add_action( 'before_delete_post', function( $post_id ) {

    if ( get_post_type( $post_id ) !== 'ag-procedure' ) {
        return;
    }

    $raw = get_post_meta( $post_id, '_ws_ag_procedure_parent_ids', true );
    $ids = is_array( $raw ) ? array_map( 'intval', $raw ) : [];

    ws_ag_procedure_parent_delete_stash( $post_id, $ids );
    ws_ag_procedure_agency_stash( $post_id, (int) get_post_meta( $post_id, 'ws_ag_procedure_agency_id', true ) );

} );


// After delete: clear parent transients and agency transient.
add_action( 'deleted_post', function( $post_id ) {

    // get_post_type() is unreliable after deletion — gate on stash having data.
    // If neither stash has data this post was not a ag-procedure.
    $parent_ids = ws_ag_procedure_parent_delete_stash( $post_id );

    foreach ( $parent_ids as $parent_id ) {
        if ( $parent_id ) {
            delete_transient( 'ws_agency_procedures_' . $parent_id . '_');
        }
    }

    $agency_id = ws_ag_procedure_agency_stash( $post_id );
    if ( $agency_id ) {
        delete_transient( 'ws_agency_procedures_' . $agency_id . '_');
    }

} );

