<?php
/**
 * tool-generate-prompt.php
 *
 * WhistleblowerShield Core Plugin — Admin Tool
 *
 * PURPOSE
 * -------
 * Generates AI research prompt templates for the WhistleblowerShield
 * ingest pipeline across statute, common-law, citation, and interpretation
 * record types.
 *
 * The generator reads taxonomy terms live from WordPress so approved terms
 * are reflected immediately in prompt output. It also computes and merges
 * auto-exclusions for the selected jurisdiction/record type to reduce
 * duplicate generation, while preserving operator overrides.
 *
 * Shared prompt boilerplate is record-neutral and includes record-specific
 * omission guidance for URL fields:
 *   - statute records: statute_url
 *   - common-law records: precedent_url
 *   - citation/interpretation records: case_url
 *
 * The admin form defaults Records Requested to 0 (no hard cap), allowing
 * broad discovery runs unless the operator specifies a limit.
 *
 * RECORD TYPES SUPPORTED
 * ----------------------
 * - statute       Full taxonomy palette, SOL/exhaustion/BOP rules
 * - common-law    Doctrine-anchored, ws_cl_* fields, statutory preclusion
 * - citation      Case law enrichment, court shorthand, sparse taxonomy
 * - interpretation Court ruling on statute, court matrix context
 *
 * OUTPUT
 * ------
 * Files written to: WP_CONTENT_DIR/logs/ws-prompts/
 * Filename format:  [JX_ID]-[records_requested]-[RecordType]-[YYYYMMDD-HHmm].txt
 *
 * ACCESS
 * ------
 * Admin only. Registered under the WhistleblowerShield tools menu.
 *
 * @package    WhistleblowerShield
 * @since      3.14.0
 * @version    3.15.4
 * @author     Whistleblower Shield
 * @link       https://whistleblowershield.org
 * @copyright  Copyright (c) Whistleblower Shield
 *
 * VERSION
 * -------
 * 3.15.4  assist-org prompt integration and corrections:
 *         - replaced preliminary JSON array output with full meta/records/integrity schema
 *         - integrated hand-edited prompt language (personas, per-field rules, note examples)
 *         - added ws_disclosure_type and ws_disclosure_targets to assist-org hierarchical tables
 *         - corrected _ws_aorg_internal_id meta key (missing leading underscore)
 *         - version diary order corrected to descending
 * 3.15.3  Omission/integrity language hardened across prompt types:
 *         - softened OMIT rule to guidance framing (avoid LLM overcorrection)
 *         - restored attached_citations: [] and citation_count: 0 defaults
 *           (omitted = failure to find; empty = no confident findings)
 *         - removed _reconciled_notes from all prompt schemas (reconciler adds it)
 * 3.15.2  Prompt taxonomy tables now include registered object-type annotations,
 *         and assist-org runs include assist-org taxonomies (including ws_languages).
 * 3.15.1  Taxonomy table output is now record-type scoped so prompts include
 *         only the taxonomy tables needed for the selected record type.
 * 3.15.0  Added flexible assist-org sourcing mode to prompt generator:
 *         - new record type: assist-org
 *         - proposal_count control (operator-defined batch size)
 *         - nationwide_only toggle and jurisdiction-aware run scope
 *         - assist-org auto-exclusions via ws-assist-org post type
 * 3.14.4  Added optional phased assist-org sourcing form block (6-7 candidates)
 *         to statute/common-law prompt templates for fallback-layer expansion runs.
 * 3.14.3  Non-statute exclusion hardening:
 *         - citation exclusions now prefer _ws_jx_citation_id
 *         - interpretation exclusions now prefer _ws_jx_interpretation_id
 *         - removed redundant empty-check branch in auto-exclusion query path
 * 3.14.2  Strict canonical scoping in auto-exclusions:
 *         - removed legacy/fallback post scans
 *         - relies on canonical taxonomy-scoped records only
 * 3.14.1  Header documentation sync.
 * 3.14.0  Prompt quality hardening:
 *         - record-neutral shared boilerplate text
 *         - clarified omission guidance for statute_url / precedent_url / case_url
 *         - default Records Requested form value set to 0
 * 3.13.0  Initial release. Generates statute, common-law, citation,
 *         and interpretation prompts from live taxonomy data.
 */

defined( 'ABSPATH' ) || exit;

// ── Admin menu registration ───────────────────────────────────────────────

add_action( 'admin_menu', 'ws_register_prompt_generator_page' );

function ws_register_prompt_generator_page() {
    add_submenu_page(
        'tools.php',
        'WS Prompt Generator',
        'WS Prompt Generator',
        'manage_options',
        'ws-prompt-generator',
        'ws_render_prompt_generator_page'
    );
}


// ── Output directory helper ───────────────────────────────────────────────

function ws_prompt_output_dir(): string {
    $dir = WP_CONTENT_DIR . '/logs/ws-prompts';
    if ( ! file_exists( $dir ) ) {
        wp_mkdir_p( $dir );
        file_put_contents( $dir . '/.htaccess', "Deny from all\n" );
    }
    return $dir;
}

// ── Jurisdiction and exclusion helpers ─────────────────────────────────────

function ws_prompt_resolve_jx_context( string $jx_id ): array {
    $jx = strtoupper( sanitize_text_field( $jx_id ) );
    $context = [
        'jx_id'           => $jx,
        'jx_name'         => $jx,
        'jx_type'         => ( $jx === 'US' ) ? 'federal' : 'state',
        'legislature_url' => '',
    ];

    if ( $jx === '' ) {
        return $context;
    }

    if ( function_exists( 'ws_get_jurisdiction_data' ) ) {
        $data = ws_get_jurisdiction_data( $jx );
        if ( is_array( $data ) ) {
            $context['jx_name'] = (string) ( $data['name'] ?? $context['jx_name'] );
            $class = strtolower( trim( (string) ( $data['class'] ?? '' ) ) );
            if ( $class !== '' ) {
                $context['jx_type'] = $class;
            }
            $context['legislature_url'] = esc_url_raw( (string) ( $data['gov']['legislature_url'] ?? '' ) );
            return $context;
        }
    }

    $term = get_term_by( 'slug', strtolower( $jx ), WS_JURISDICTION_TAXONOMY );
    if ( $term && ! is_wp_error( $term ) ) {
        $context['jx_name'] = (string) $term->name;
    }

    return $context;
}

function ws_prompt_record_type_to_post_type( string $record_type ): string {
    switch ( $record_type ) {
        case 'statute':
            return 'jx-statute';
        case 'common-law':
            return 'jx-common-law';
        case 'citation':
            return 'jx-citation';
        case 'interpretation':
            return 'jx-interpretation';
        case 'assist-org':
            return 'ws-assist-org';
        default:
            return '';
    }
}

function ws_prompt_extract_record_identifier( string $record_type, int $post_id ): string {
    // @todo cite exclusion ids not yet implemented.
    if ( $record_type === 'statute' ) {
        return trim( (string) get_post_meta( $post_id, '_ws_jx_statute_id', true ) );
    }

    if ( $record_type === 'common-law' ) {
        $doctrine_id = trim( (string) get_post_meta( $post_id, '_ws_cl_doctrine_id', true ) );
        if ( $doctrine_id !== '' ) {
            return $doctrine_id;
        }
    }

    if ( $record_type === 'citation' ) {
        $citation_id = trim( (string) get_post_meta( $post_id, '_ws_jx_citation_id', true ) );
        if ( $citation_id !== '' ) {
            return $citation_id;
        }
    }

    if ( $record_type === 'interpretation' ) {
        $interpretation_id = trim( (string) get_post_meta( $post_id, '_ws_jx_interpretation_id', true ) );
        if ( $interpretation_id !== '' ) {
            return $interpretation_id;
        }
    }

    if ( $record_type === 'assist-org' ) {
        $assist_org_id = trim( (string) get_post_meta( $post_id, '_ws_aorg_internal_id', true ) );
        if ( $assist_org_id !== '' ) {
            return $assist_org_id;
        }
    }

    return trim( (string) get_the_title( $post_id ) );
}

function ws_prompt_get_auto_exclusions( string $record_type, string $jx_id ): array {
    $post_type = ws_prompt_record_type_to_post_type( $record_type );
    if ( $post_type === '' || $jx_id === '' ) {
        return [];
    }

    $allowed_statuses = [ 'publish', 'private', 'draft', 'auto-draft', 'pending', 'future' ];
    $jx_slug = strtolower( $jx_id );

    if ( $record_type === 'statute' ) {
        $posts = get_posts( [
            'post_type'              => $post_type,
            'post_status'            => $allowed_statuses,
            'posts_per_page'         => -1,
            'fields'                 => 'ids',
            'no_found_rows'          => true,
            'update_post_meta_cache' => false,
            'update_post_term_cache' => false,
            'tax_query'              => [
                [
                    'taxonomy' => WS_JURISDICTION_TAXONOMY,
                    'field'    => 'slug',
                    'terms'    => [ $jx_slug ],
                ],
            ],
            'meta_query'             => [
                [
                    'key'     => '_ws_jx_statute_id',
                    'value'   => '',
                    'compare' => '!=',
                ],
            ],
        ] );

        if ( empty( $posts ) ) {
            return [];
        }

        $ids = [];
        foreach ( $posts as $pid ) {
            $sid = trim( (string) get_post_meta( (int) $pid, '_ws_jx_statute_id', true ) );
            if ( $sid !== '' ) {
                $ids[] = $sid;
            }
        }

        $ids = array_values( array_unique( $ids ) );
        sort( $ids, SORT_NATURAL | SORT_FLAG_CASE );
        return $ids;
    }

    $posts = get_posts( [
        'post_type'              => $post_type,
        'post_status'            => $allowed_statuses,
        'posts_per_page'         => -1,
        'fields'                 => 'ids',
        'no_found_rows'          => true,
        'update_post_meta_cache' => false,
        'update_post_term_cache' => false,
        'tax_query'              => [
            [
                'taxonomy' => WS_JURISDICTION_TAXONOMY,
                'field'    => 'slug',
                'terms'    => [ $jx_slug ],
            ],
        ],
    ] );

    if ( empty( $posts ) ) {
        return [];
    }

    $ids = [];
    foreach ( $posts as $pid ) {
        $value = ws_prompt_extract_record_identifier( $record_type, (int) $pid );
        if ( $value !== '' ) {
            $ids[] = $value;
        }
    }

    $ids = array_values( array_unique( $ids ) );
    sort( $ids, SORT_NATURAL | SORT_FLAG_CASE );
    return $ids;
}

function ws_prompt_get_statute_posts_missing_hidden_id( string $jx_id ): array {
    $jx_slug = strtolower( trim( $jx_id ) );
    if ( $jx_slug === '' ) {
        return [];
    }

    $allowed_statuses = [ 'publish', 'private', 'draft', 'auto-draft', 'pending', 'future' ];
    $posts = get_posts( [
        'post_type'              => 'jx-statute',
        'post_status'            => $allowed_statuses,
        'posts_per_page'         => -1,
        'fields'                 => 'ids',
        'no_found_rows'          => true,
        'update_post_meta_cache' => false,
        'update_post_term_cache' => false,
        'tax_query'              => [
            [
                'taxonomy' => WS_JURISDICTION_TAXONOMY,
                'field'    => 'slug',
                'terms'    => [ $jx_slug ],
            ],
        ],
    ] );

    if ( empty( $posts ) ) {
        return [];
    }

    $missing = [];
    foreach ( $posts as $pid ) {
        $sid = trim( (string) get_post_meta( (int) $pid, '_ws_jx_statute_id', true ) );
        if ( $sid === '' ) {
            $missing[] = get_the_title( (int) $pid ) ?: ('post #' . (int) $pid);
        }
    }

    return $missing;
}

function ws_prompt_merge_exclusions( string $manual_exclusions, array $auto_exclusions ): string {
    $merged = [];

    foreach ( explode( "\n", (string) $manual_exclusions ) as $line ) {
        $line = trim( $line );
        if ( $line !== '' ) {
            $merged[] = $line;
        }
    }

    foreach ( $auto_exclusions as $line ) {
        $line = trim( (string) $line );
        if ( $line !== '' ) {
            $merged[] = $line;
        }
    }

    $merged = array_values( array_unique( $merged ) );
    sort( $merged, SORT_NATURAL | SORT_FLAG_CASE );

    return implode( "\n", $merged );
}

function ws_prompt_split_lines( string $text ): array {
    $lines = [];
    foreach ( explode( "\n", $text ) as $line ) {
        $line = trim( $line );
        if ( $line !== '' ) {
            $lines[] = $line;
        }
    }

    $lines = array_values( array_unique( $lines ) );
    sort( $lines, SORT_NATURAL | SORT_FLAG_CASE );
    return $lines;
}

function ws_prompt_resolve_auto_exclusions_text( array $post, array $computed_auto_exclusions ): string {
    $posted = isset( $post['exclusion_list_auto'] )
        ? sanitize_textarea_field( (string) $post['exclusion_list_auto'] )
        : '';
    $edited = ! empty( $post['exclusion_list_auto_edited'] );

    // Keep operator edits, but never let an untouched empty textarea
    // suppress computed exclusions on first submit.
    if ( $edited ) {
        return $posted;
    }

    return implode( "\n", $computed_auto_exclusions );
}


// ── Taxonomy data — read from WordPress database ─────────────────────────
//
// All taxonomy helpers read live from WordPress via get_terms().
// This ensures approved proposed terms surface automatically without
// requiring a PHP sync pass. The database is the source of truth at
// runtime — register-taxonomies.php seeds it, but these functions
// read whatever is actually registered and approved.

/**
 * Reads a hierarchical taxonomy from WordPress and returns a nested array
 * suitable for ws_prompt_render_hierarchical_table().
 *
 * Structure: [ parent_slug => [ 'label' => 'Parent Label (parent)', 'children' => [ slug => label ] ] ]
 * The has-details sentinel is excluded from the hierarchy and returned
 * separately so the table renderer can append it at the end.
 *
 * @param string $taxonomy  The taxonomy slug to read.
 * @return array            [ 'hierarchy' => [...], 'has_sentinel' => bool ]
 */
function ws_prompt_read_hierarchical_taxonomy( string $taxonomy ): array {
    $result = [ 'hierarchy' => [], 'has_sentinel' => false ];

    // Get all top-level terms
    $parents = get_terms( [
        'taxonomy'   => $taxonomy,
        'hide_empty' => false,
        'parent'     => 0,
        'orderby'    => 'term_id',
        'order'      => 'ASC',
    ] );

    if ( is_wp_error( $parents ) || empty( $parents ) ) {
        return $result;
    }

    foreach ( $parents as $parent ) {
        if ( $parent->slug === 'has-details' ) {
            $result['has_sentinel'] = true;
            continue;
        }

        $children_terms = get_terms( [
            'taxonomy'   => $taxonomy,
            'hide_empty' => false,
            'parent'     => $parent->term_id,
            'orderby'    => 'term_id',
            'order'      => 'ASC',
        ] );

        $children = [];
        if ( ! is_wp_error( $children_terms ) ) {
            foreach ( $children_terms as $child ) {
                $children[ $child->slug ] = html_entity_decode( $child->name, ENT_QUOTES | ENT_HTML5, 'UTF-8' );
            }
        }

        $result['hierarchy'][ $parent->slug ] = [
            'label'    => html_entity_decode( $parent->name, ENT_QUOTES | ENT_HTML5, 'UTF-8' ) . ' (parent)',
            'children' => $children,
        ];
    }

    return $result;
}

/**
 * Reads a flat taxonomy from WordPress and returns a slug => name array.
 * has-details sentinel is kept in place at the end of the list.
 *
 * @param string $taxonomy  The taxonomy slug to read.
 * @return array            [ slug => label ]
 */
function ws_prompt_read_flat_taxonomy( string $taxonomy ): array {
    $terms = get_terms( [
        'taxonomy'   => $taxonomy,
        'hide_empty' => false,
        'orderby'    => 'term_id',
        'order'      => 'ASC',
    ] );

    if ( is_wp_error( $terms ) || empty( $terms ) ) {
        return [];
    }

    $result = [];
    $sentinel = null;
    foreach ( $terms as $term ) {
        if ( $term->slug === 'has-details' ) {
            $sentinel = $term;
            continue;
        }
        $result[ $term->slug ] = html_entity_decode( $term->name, ENT_QUOTES | ENT_HTML5, 'UTF-8' );
    }
    // Append sentinel last
    if ( $sentinel ) {
        $result['has-details'] = 'Has Details (sentinel — use with {taxonomy}_details)';
    }

    return $result;
}


// ── Taxonomy table renderers ──────────────────────────────────────────────

function ws_prompt_render_hierarchical_table( string $slug, string $label, string $applies_to, string $description, array $hierarchy, bool $has_sentinel = false, array $object_types = [] ): string {
    $pad = 35;
    $out  = str_repeat( '─', 76 ) . "\n";
    $out .= "TAXONOMY: {$slug}\n";
    $out .= "Applies to: {$applies_to}\n";
    $out .= "Hierarchical: YES — use child slugs only\n";
    $out .= "Description: {$description}\n";
    $out .= str_repeat( '─', 76 ) . "\n\n";

    foreach ( $hierarchy as $parent_slug => $data ) {
        $out .= "--- {$parent_slug} ---\n";
        $out .= str_pad( $parent_slug, $pad ) . $data['label'] . "\n";
        foreach ( $data['children'] as $child_slug => $child_label ) {
            $out .= str_pad( $child_slug, $pad ) . $child_label . "\n";
        }
        $out .= "\n";
    }

    if ( $has_sentinel ) {
        $out .= str_pad( 'has-details', $pad ) . "Has Details (sentinel — use with {$slug}_details)\n";
        $out .= "\n";
    }

    return $out;
}

function ws_prompt_render_flat_table( string $slug, string $label, string $applies_to, string $description, array $terms, array $object_types = [] ): string {
    $pad = 35;
    $out  = str_repeat( '─', 76 ) . "\n";
    $out .= "TAXONOMY: {$slug}\n";
    $out .= "Applies to: {$applies_to}\n";
    $out .= "Hierarchical: NO — flat list\n";
    $out .= "Description: {$description}\n";
    $out .= str_repeat( '─', 76 ) . "\n\n";

    foreach ( $terms as $term_slug => $term_label ) {
        $out .= str_pad( $term_slug, $pad ) . $term_label . "\n";
    }
    $out .= "\n";

    return $out;
}


// ── Parent slug self-check list (generated from live taxonomy data) ──────

function ws_prompt_get_parent_slugs(): array {
    $parents = [];
    foreach ( [ 'ws_disclosure_type', 'ws_protected_class', 'ws_disclosure_targets' ] as $taxonomy ) {
        $top_level = get_terms( [
            'taxonomy'   => $taxonomy,
            'hide_empty' => false,
            'parent'     => 0,
            'fields'     => 'slugs',
        ] );
        if ( ! is_wp_error( $top_level ) ) {
            foreach ( $top_level as $slug ) {
                if ( $slug !== 'has-details' ) {
                    $parents[] = $slug;
                }
            }
        }
    }
    return $parents;
}


// ── Court shorthand reference ─────────────────────────────────────────────

function ws_prompt_get_court_shorthand(): string {
    return <<<TEXT
COURT SHORTHAND — use these identifiers exactly. Do not invent abbreviations.

  SCOTUS              U.S. Supreme Court
  CA-[#]              Federal Circuit Court of Appeals (e.g. CA-3, CA-9)
  USDC-[DIST]         Federal District Court (e.g. USDC-DNJ, USDC-CDCA)
  [STATE]-SUP         State Supreme Court (e.g. NJ-SUP, CA-SUP)
  [STATE]-APP         State Appellate Court — general (e.g. NJ-APP, CA-APP)
  [STATE]-APP-[DIV]   State Appellate Division (e.g. NJ-APP-2, CA-APP-4)
  [STATE]-TAX         State Tax Court
  OTHER               Court not in this list — describe in _review_notes

TEXT;
}


// ── Static rule blocks ────────────────────────────────────────────────────

function ws_prompt_statute_rules(): string {
    return <<<'RULES'
---

STATUTE OF LIMITATIONS

Many whistleblower statutes do not specify a filing deadline in their own
text. The applicable SOL is often derived from a general civil procedure
statute rather than the whistleblower law itself. This distinction matters.

Before setting limit_ambiguous to false, verify the SOL value directly
against the statute text at the provided legislature URL. If the value is
stated explicitly in the statute, set limit_ambiguous to false. If you are
deriving it from a general civil procedure statute or secondary source,
set limit_ambiguous to true regardless of your confidence in the derived
value.

Any record with limit_ambiguous: true requires a corresponding entry in
error_details in the integrity block. This is mandatory.

---

EXHAUSTION RULE

exhaustion_required must be true whenever a mandatory administrative filing
step is required before civil court access, even if de novo review becomes
available after a waiting period. Populate exhaustion_details with the
specific procedural requirement and deadline.

Written notice requirements that do not block court access are NOT
exhaustion — do not set exhaustion_required true for these. Note them
in _review_notes instead.

---

BURDEN OF PROOF

burden_of_proof_flag is a short signal phrase identifying the non-standard
burden shift — not a full sentence, not a boolean. Examples:
  "AIR21 burden-shifting framework"
  "90-day rebuttable presumption"
  "contributing-factor-shift"

Use burden_of_proof_details for narrative explanation. Omit entirely
unless a meaningful or non-standard burden shift is identified.

employee_standard (reasonable-belief): describes the threshold for what
qualifies as a protected disclosure — it is not a causation standard. Use
it only when the statute explicitly names reasonable belief as a separate
element. Do not use as a substitute for contributing-factor.

---
RULES;
}

function ws_prompt_citation_rules(): string {
    return <<<'RULES'
---

APPROVED SOURCES

Always attempt sources in this order. Use the first source that yields a
trustworthy URL and stop.

STATUTE SOURCES:
  1. The official legislature URL for this jurisdiction (provided in RUN SCOPE)
  2. uscode.house.gov — federal statutes
  3. congress.gov — federal statutes
  4. legiscan.com — acceptable secondary
  5. law.justia.com — acceptable secondary

CASE LAW SOURCES:
  1. Official court websites (supremecourt.gov, ca9.uscourts.gov, etc.)
  2. courtlistener.com — PACER-sourced, highly reliable
  3. casetext.com — strong coverage, stable URLs
  4. law.justia.com — broad coverage

FORBIDDEN: scholar.google.com (unstable URLs), law firm websites, any
non-institutional aggregator not listed above.

If no verifiable URL exists from the approved list, omit the citation
entirely. Do not substitute a different URL.

---

CITATIONS

Citation format:
  "CASE NAME v. CASE NAME || SPECIFIC_IMPACT || URL || SOURCE || QUALITY"

SPECIFIC_IMPACT: 3-8 words, action-verb first, describing the functional
legal impact of this ruling. Use one of these patterns:
  "defines [legal concept]"       "clarifies [legal standard]"
  "establishes [rule/test]"       "applies [statute/standard]"
  "limits [scope/protection]"     "expands [scope/protection]"
  "interprets [term/phrase]"      "confirms [legal principle]"
  "rejects [legal argument]"      "resolves [conflict/ambiguity]"

QUALITY values:
  high     — appellate or supreme court; frequently cited
  moderate — appellate but narrower scope or less cited
  low      — trial-level or limited precedential value

Prioritize appellate and supreme court decisions.

---

RULES;
}

function ws_prompt_omission_rules(): string {
    return <<<'RULES'
---

OMISSION

When you cannot find a value with reasonable confidence, omit the key
entirely — do not substitute null, "N/A", "unknown", or a placeholder.

Field-level schema rules take precedence: use empty "" or [] only when the
schema explicitly marks a field as required-but-empty.

An incomplete record can be enriched on a future run.
A wrong record causes real harm: a guessed deadline or fabricated citation
may cause a user to miss a legal deadline or act on law that does not exist.

If confidence is insufficient for a field, omit it and note the gap in
integrity.error_details. If you cannot research a record with confidence,
it is acceptable to skip it — set with_errors: true and explain why.

The following fields are routinely empty in a well-produced run and their
absence is never penalized:
  - tolling_notes
  - rebuttable_presumption
  - burden_of_proof_details
  - statute_url    → when record type is statute
  - precedent_url  → when record type is common-law
  - case_url       → when record type is citation or interpretation
  - _review_notes  — omit only when you have nothing to add. When used,
                     note anything a person relying on this record for a
                     real legal decision would need to know: edge cases,
                     coverage gaps, unusual procedural requirements,
                     conflicts with other records, or caveats the schema
                     fields cannot capture.

The following fields must be omitted entirely when empty — do not include
them as empty arrays [] or empty strings "":
  - enforcement.fee_shifting
  - enforcement.process_type
  - enforcement.adverse_action
  - enforcement.remedies
  - legal_basis.disclosure_types
  - legal_basis.protected_class
  - legal_basis.disclosure_targets
  - burden_of_proof.employee_standard
  - burden_of_proof.employer_defense
  - common_name
  - enforcement.primary_agency
  - burden_of_proof.burden_of_proof_flag
  - statute_of_limitations.trigger
  - statute_of_limitations.limit_details
  - statute_of_limitations.exhaustion_details
  - statute_of_limitations.tolling_notes
  - reward.reward_details
  - links.url_source

---

RULES;
}


// ── Taxonomy tables block (shared across record types) ────────────────────

function ws_prompt_taxonomy_tables( string $applies_to ): string {
    $record_type = strtolower( trim( $applies_to ) );

    $out  = str_repeat( '=', 80 ) . "\n";
    $out .= "TAXONOMY TABLES\n";
    $out .= "Notes: Record-type scoped palette. Use child slugs only for hierarchical\n";
    $out .= "       taxonomies. Tag only what is genuinely supported by the source\n";
    $out .= "       material. Many axes will be sparsely used on any single record.\n";
    $out .= "       Never use parent slugs. Never use slugs from the wrong table.\n";
    $out .= str_repeat( '=', 80 ) . "\n\n\n";

    // Hierarchical taxonomies
    $hierarchical = [
        'ws_disclosure_type' => [
            'label'       => 'Disclosure Categories',
            'description' => "Subject matter of the protected disclosure. Use all that apply.",
            'sentinel'    => false,
        ],
        'ws_protected_class' => [
            'label'       => 'Protected Class',
            'description' => "Employment or worker classification protected. Tag all explicitly covered.\n             Do not infer coverage.",
            'sentinel'    => true,
        ],
        'ws_disclosure_targets' => [
            'label'       => 'Disclosure Targets',
            'description' => "Who the protected disclosure may be made to. Tag all valid targets\n             explicitly named or clearly implied.",
            'sentinel'    => true,
        ],
    ];

    $hierarchical_by_record_type = [
        'jx-statute'        => [ 'ws_disclosure_type', 'ws_protected_class', 'ws_disclosure_targets' ],
        'jx-common-law'     => [ 'ws_disclosure_type', 'ws_protected_class', 'ws_disclosure_targets' ],
        'jx-citation'       => [ 'ws_disclosure_type', 'ws_protected_class', 'ws_disclosure_targets' ],
        'jx-interpretation' => [ 'ws_disclosure_type', 'ws_protected_class', 'ws_disclosure_targets' ],
        'ws-assist-org'     => [ 'ws_disclosure_type', 'ws_protected_class', 'ws_disclosure_targets' ],
    ];

    $selected_hierarchical = $hierarchical_by_record_type[ $record_type ] ?? [];

    foreach ( $selected_hierarchical as $slug ) {
        if ( ! isset( $hierarchical[ $slug ] ) ) {
            continue;
        }

        $config = $hierarchical[ $slug ];
        $data = ws_prompt_read_hierarchical_taxonomy( $slug );
        $object_types = ws_prompt_registered_object_types( $slug );
        $out .= ws_prompt_render_hierarchical_table(
            $slug, $config['label'], $applies_to,
            $config['description'],
            $data['hierarchy'],
            $data['has_sentinel'],
            $object_types
        );
    }

    // Flat taxonomies
    $flat = [
        'ws_adverse_action_types' => [
            'label'       => 'Adverse Action Types',
            'description' => "Retaliatory actions explicitly or broadly prohibited. Tag all covered;\n             do not tag actions merely implied.",
        ],
        'ws_process_type' => [
            'label'       => 'Process Types',
            'description' => "Procedural route available. Tag all that apply.",
        ],
        'ws_remedies' => [
            'label'       => 'Available Remedies',
            'description' => "Remedies available to a prevailing claimant. Tag all explicitly\n             available; do not infer from general principles.",
        ],
        'ws_fee_shifting' => [
            'label'       => 'Fee Shifting',
            'description' => "Fee-shifting posture. Single-value taxonomy; use the most accurate term.\n             prevailing-party = either side recovers.\n             unilateral-pro-plaintiff = only a successful plaintiff recovers.",
        ],
        'ws_employer_defense' => [
            'label'       => 'Employer Defense',
            'description' => "Affirmative defenses available to an employer. Tag all explicitly\n             recognized under governing law.",
        ],
        'ws_employee_standard' => [
            'label'       => 'Employee Standard',
            'description' => "Burden-of-proof standard the employee must meet. Tag all that explicitly\n             apply. Omit entirely if no standard is named — do not infer.",
        ],
        'ws_case_stage' => [
            'label'       => 'Case Stages',
            'description' => "Lifecycle stage where help is needed. Tag all that explicitly\n             apply (for example pre-report, retaliation-active, litigation).",
        ],
        'ws_languages' => [
            'label'       => 'Languages',
            'description' => "Languages the organization can handle for intake or support. Tag all\n             available language options.",
        ],
        'ws_aorg_type' => [
            'label'       => 'Assist-Org Type',
            'description' => "Primary organization classification for directory filters.",
        ],
        'ws_employment_sector' => [
            'label'       => 'Employment Sectors',
            'description' => "Employment sectors the organization serves. Tag all supported sectors.",
        ],
        'ws_aorg_cost_model' => [
            'label'       => 'Assist-Org Cost Models',
            'description' => "Available cost models for the organization. Tag all that explicitly apply.",
        ],
        'ws_aorg_service' => [
            'label'       => 'Assist-Org Services',
            'description' => "Services offered by the organization. Tag all service types supported.",
        ],
    ];

    $flat_by_record_type = [
        'jx-statute'        => [ 'ws_adverse_action_types', 'ws_process_type', 'ws_remedies', 'ws_fee_shifting', 'ws_employer_defense', 'ws_employee_standard' ],
        'jx-common-law'     => [ 'ws_adverse_action_types', 'ws_process_type', 'ws_remedies', 'ws_fee_shifting', 'ws_employer_defense', 'ws_employee_standard' ],
        'jx-citation'       => [ 'ws_adverse_action_types', 'ws_process_type', 'ws_remedies', 'ws_employer_defense', 'ws_employee_standard' ],
        'jx-interpretation' => [ 'ws_adverse_action_types', 'ws_process_type', 'ws_remedies', 'ws_employer_defense', 'ws_employee_standard' ],
        'ws-assist-org'     => [ 'ws_process_type', 'ws_case_stage', 'ws_languages', 'ws_aorg_type', 'ws_employment_sector', 'ws_aorg_cost_model', 'ws_aorg_service' ],
    ];

    $selected_flat = $flat_by_record_type[ $record_type ] ?? [];

    foreach ( $selected_flat as $slug ) {
        if ( ! isset( $flat[ $slug ] ) ) {
            continue;
        }

        $config = $flat[ $slug ];
        $terms = ws_prompt_read_flat_taxonomy( $slug );
        $object_types = ws_prompt_registered_object_types( $slug );
        // Fix sentinel label to reference correct companion field
        if ( isset( $terms['has-details'] ) ) {
            $field_map = [
                'ws_adverse_action_types' => 'adverse_action_details',
                'ws_remedies'             => 'remedies_details',
                'ws_employer_defense'     => 'employer_defense_details',
                'ws_employee_standard'    => 'employee_standard_details',
                'ws_protected_class'      => 'protected_class_details',
            ];
            $companion = $field_map[ $slug ] ?? "{$slug}_details";
            $terms['has-details'] = "Has Details (sentinel — use with {$companion})";
        }
        $out .= ws_prompt_render_flat_table(
            $slug, $config['label'], $applies_to,
            $config['description'],
            $terms,
            $object_types
        );
    }

    return $out;
}

function ws_prompt_registered_object_types( string $taxonomy ): array {
    $map = [
        'ws_disclosure_type'   => [ 'jx-statute', 'jx-citation', 'jx-interpretation', 'jx-common-law', 'ws-agency', 'ws-ag-procedure', 'ws-assist-org' ],
        'ws_process_type'      => [ 'jx-statute', 'jx-citation', 'jx-interpretation', 'jx-common-law', 'ws-agency', 'ws-assist-org' ],
        'ws_remedies'          => [ 'jx-statute', 'jx-citation', 'jx-interpretation', 'jx-common-law' ],
        'ws_protected_class'   => [ 'jx-statute', 'jx-citation', 'jx-interpretation', 'jx-common-law', 'ws-assist-org' ],
        'ws_adverse_action_types' => [ 'jx-statute', 'jx-citation', 'jx-interpretation', 'jx-common-law' ],
        'ws_languages'         => [ 'ws-agency', 'ws-assist-org' ],
        'ws_case_stage'        => [ 'ws-assist-org' ],
        'ws_disclosure_targets'=> [ 'jx-statute', 'jx-citation', 'jx-interpretation', 'jx-common-law', 'ws-assist-org' ],
        'ws_fee_shifting'      => [ 'jx-statute', 'jx-citation', 'jx-interpretation', 'jx-common-law' ],
        'ws_employer_defense'  => [ 'jx-statute', 'jx-citation', 'jx-interpretation', 'jx-common-law' ],
        'ws_aorg_type'         => [ 'ws-assist-org' ],
        'ws_employment_sector' => [ 'ws-assist-org' ],
        'ws_aorg_cost_model'   => [ 'ws-assist-org' ],
        'ws_aorg_service'      => [ 'ws-assist-org' ],
        'ws_employee_standard' => [ 'jx-statute', 'jx-citation', 'jx-interpretation', 'jx-common-law' ],
    ];

    return $map[ $taxonomy ] ?? [];
}


// ── Parent slug self-check block ──────────────────────────────────────────

function ws_prompt_parent_slug_block(): string {
    $parents = ws_prompt_get_parent_slugs();
    $list    = implode( ', ', array_map( fn($s) => "\"{$s}\"", $parents ) );

    return <<<BLOCK
---

PARENT SLUGS — CRITICAL RULE

Parent slugs are structural labels only. They are never valid record values
and must never appear in any record array.

SELF-CHECK REQUIRED: Before writing your final JSON, scan every taxonomy
array in every record. If you find any parent slug — including but not
limited to {$list} — you must:

  1. Delete it from the array immediately
  2. Leave the array empty if no valid child slug applies
  3. Note the removed slug in json_run_notes
  4. Set with_errors: true in the integrity block
  5. Add to error_details: "[RECORD_ID]: Removed parent slug [SLUG] from
     [FIELD] — no matching child slug found"

A parent slug in a record array corrupts the database. This self-check
is mandatory on every batch.

---

BLOCK;
}


// ── Taxonomy proposal block ───────────────────────────────────────────────

function ws_prompt_proposal_block(): string {
    return <<<'BLOCK'
---
PROPOSING NEW TAXONOMY TERMS

When you encounter a concept that does not fit any slug in the known taxonomy,
propose it. Proposals are expected and valued at every stage of this pipeline.

A proposal that does not become a registered taxonomy term is not discarded.
It enters a human review queue where it may serve as an edge-case signal —
a last-resort reference for a user whose situation does not fit any existing
term. The person using this site may have nowhere else to turn. A concept
you surface here, even once, even in a single record, could be the most
useful thing in this entire batch for that person.

Propose it.

Before proposing, consider two things — not as gates, but as guidance:

  1. Is this concept likely to appear in other records across other
     jurisdictions? If yes, it is a strong candidate. If it feels entirely
     specific to one record in one jurisdiction, note it in json_run_notes
     as well so the human reviewer has full context.

  2. Can this concept be accurately represented by combining three or fewer
     existing child slugs across the relevant taxonomy fields? If yes,
     use that combination in the record — and also propose the term.
     A workaround combination and a clean proposal are not mutually exclusive.

Do not propose new taxonomy tables or new parent terms. Use json_run_notes
to recommend them.

  {
    "taxonomy":   "[REGISTERED TAXONOMY TABLE SLUG]",
    "term_id":    "[YOUR PROPOSED SLUG IN kebab-case]",
    "term_label": "[HUMAN-READABLE LABEL]",
    "notes":      "[WHY THIS TERM IS NEEDED AND WHY EXISTING TERMS DO NOT COVER IT]",
    "seen_in":    ["[RECORD_ID]"],
    "count":      [INTEGER — must equal length of seen_in]
  }

Do not insert a proposed term_id into any record array. Proposals live in
new_terms_proposed only. If no new terms are needed, new_terms_proposed
must be an empty array [].

---

BLOCK;
}


// ── Integrity block ───────────────────────────────────────────────────────

function ws_prompt_integrity_block(): string {
    return <<<'BLOCK'
---

INTEGRITY BLOCK

The integrity block is your honest self-report on the state of this batch.
Reporting errors here is not a failure — it is the most valuable contribution
you can make to the reliability of this platform.

{
  "integrity": {
        "with_errors":   [true | false],
        "error_details": ["[SPECIFIC ERROR WITH DETAILS]"],
        "error_count":   [INTEGER — must equal length of error_details]
    }
}

with_errors must be true if ANY of the following occurred:
    - record_count is less than requested (fewer records returned than requested)
    - A required record could not be researched with sufficient confidence
    - Any schema rule was knowingly violated
    - A parent slug was detected and removed during self-check

with_errors should not include items like:  
    - Anything a human reviewer should know about this batch
    - use record._review_notes for these — they are expected and do not indicate an error in the batch

Fail-safe policy:
    - It is acceptable to fail one or more records instead of guessing.
    - It is acceptable to fail the full batch if source quality is insufficient.
    - In either case, set with_errors: true and explain exactly why.

OMISSION RULE: If with_errors is false, omit error_details and error_count
entirely. The ingest tool treats a missing key differently from an empty array.

---

BLOCK;
}

function ws_prompt_assist_org_research_block(): string {
    return <<<'BLOCK'
You are a research assistant building a vetted shortlist of assist organizations
for WhistleblowerShield.org fallback routing. When our database, you helped build,
doesn't surface a specific shortlist of targeted assistance organizations, the
fallback list must be ready to help the end users in need.

Persona(1) Maya  - considering coming forward to expose wrongdoing.
Persona(2) James - has already come forward and is under direct retaliation.

Objective: return a high-confidence, low-noise batch where the user needs
direct help or a fast path to qualified help.

Keep the batch tight and practical. Do not return more than requested records.
Do not return records where you are not confident about included data.

BLOCK;
}

function ws_prompt_assist_org_field_rules_block(): string {
    return <<<'BLOCK'
FIELD RULES

---

OMISSION POLICY

Omit any optional field when empty or uncertain.
Omitting unverified data is correct.
Fabricating data is wrong, and has real-world impact on the end user. Contacting an assistance
organization that was not properly vetted wastes time and creates stress for a person in crisis.

---

FIELD REQUIREMENTS

Five tiers govern every field in the schema:

  essential           — omit the entire record if missing; no fallback exists
  expected            — always include; use the stated fallback when data is unavailable
  ternary             — yes when present; no when not present; unclear when uncertain
  expected-if-found   — include when present even if empty; omit only when genuinely not found
  conditional         — required when its parent condition is met; omit otherwise
  optional            — omit entirely when uncertain or unavailable

ESSENTIAL — omit the record if any of these cannot be confidently sourced:

  identity.official_name
  identity.official_homepage_url
  identity.general_description

EXPECTED — always include; use the fallback when data is unavailable. Fallback values here are
not “real” data; they are not considered failures, they are explicit signals for human
review and must be paired with *_details where applicable. Use these fallbacks instead
of descriptions in _review_notes so that _review_notes can stay focused on the key
clarifications you make during your research:

  Field                                       Fallback
  ─────────────────────────────────────────   ────────────────────────────────────────────────
  identity.homepage_url_status                unverified
  scope_of_service.assistance_type            mixed
  scope_of_service.cost_models                ["unclear"]
  scope_of_service.case_stages                ["other"]        → populate case_stage_details
  scope_of_service.disclosure_targets         ["has-details"]  → populate disclosure_targets_details
  scope_of_service.protected_class            ["has-details"]  → populate protected_class_details
  scope_of_service.whistleblower_scope        0  (scope unclear)
  scope_of_service.whistleblower_note         state reason for inclusion
  review._review_notes                        "researcher had no notes on current record"

TERNARY — use yes when the source confirms the presence of this feature, no when the source
confirms its absence, and unclear when the source is ambiguous. unclear is not a failure — 
it is a signal for human review and should be used whenever the source material does not
confidently confirm or deny the presence of the feature.

  security.has_secure_channel                 unclear
  security.anonymous_pre_consult_possible     unclear
  security.has_attorneys                      unclear
  eligibility.income_eligibility_required     unclear

EXPECTED-IF-FOUND — include even when blank; omit only when genuinely not found and note the
absence in _review_notes. This is important for capturing “I looked for this and it's not there”
signals that are critical for human reviewers:

  scope_of_service.nationwide_example         "" when no qualifying quote is found
  scope_of_service.disclosure_types           [] when none can be confirmed
  scope_of_service.languages_supported        [] when language support is ambiguous

CONDITIONAL — required when the parent condition is met; omit otherwise. In most cases these
fields are the structured breadcrumbs for human reviewers: use them to capture “what I found
doesn't fit the schema”; keep _review_notes for any key clarifications you want to make during
your research:

  Field                                       Condition
  ─────────────────────────────────────────   ────────────────────────────────────────────────
  scope_of_service.protected_class_details    protected_class includes has-details
  scope_of_service.additional_services        services_provided includes additional
  scope_of_service.case_stage_details         case_stages includes other
  scope_of_service.disclosure_targets_details disclosure_targets includes has-details
  eligibility.income_eligibility_details      income_eligibility_required is yes
  security.secure_contact_url                 has_secure_channel is yes
  security.secure_contact_tool                has_secure_channel is yes
  security.secure_contact_tool_other          secure_contact_tool is other

OPTIONAL — omit entirely when uncertain or unavailable. It is not required, but use _review_notes
if something was found that didn't comply with instructions, but you think may still be helpful:

  identity.common_name
  identity.verified_url_date
  contact.intake_url
  contact.contact_url
  contact.phones
  contact.emails
  contact.mailing_address
  scope_of_service.languages_additional
  scope_of_service.employment_sectors
  scope_of_service.services_provided
  scope_of_service.process_types
  scope_of_service.jurisdiction_exceptions
  eligibility.eligibility_notes
  review.legitimacy_url

---

INLINE DEFINITIONS

identity:
  official_name               full official name exactly as it appears on the org's homepage.
  official_homepage_url       official domain URL — the org's own homepage, not a directory listing.
  general_description         3 to 5 sentences: what the org does, who it serves, what help it provides.
  common_name                 widely used shorthand name or acronym; omit if none exists.
  homepage_url_status         verified | redirects | unverified
  verified_url_date           YYYY-MM-DD; omit if the URL was not confirmed during this run.

scope_of_service:
  nationwide_example          verbatim quote (up to 3 sentences) from the org's own site or
                              mission statement showing nationwide scope; use "" if none is found.
                              Omit when the org is clearly not nationwide, and note the absence in _review_notes.
  disclosure_types            ws_disclosure_type slugs; [] when none can be confirmed.
  protected_classes           ws_protected_class slugs; use has-details slug when coverage
                              exists but no slug fits cleanly, or coverage is ambiguous.
  protected_class_details     free text when protected_classes includes has-details slug; describe the
                              org's claim of coverage or note ambiguity of coverage.
  languages_supported         ws_languages slugs; list all languages the org claims to support.
                              Do not assume "english" if the site appears to be in another language.
                              Leave languages_supported as [] when language support is ambiguous
                              and note the ambiguity in _review_notes.
  languages_additional        free text listing languages not in taxonomy (e.g. TTY relay,
                              interpreter services); omit when additional slug is not included
							  in languages_supported array.                              
  assistance_type             single ws_aorg_type slug; use mixed slug when no single slug
                              fits cleanly.
  employment_sectors          ws_employment_sector slugs; omit when coverage is unclear.
  cost_models                 ws_aorg_cost_model slugs; include all described cost models.
                              Use unclear slug when other slugs do not fit described cost model cleanly,
                              or no cost model is not described at all.
  services_provided           ws_aorg_service slugs. Include secure-drop when the org runs a dedicated
                              anonymous evidence drop (e.g. SecureDrop). Include additional slug when a
							  service described doesn't fit existing slugs cleanly. Omit services_provided
							  entirely when the site is unclear about what services it provides and strongly
							  note the absence in _review_notes.
  additional_services         free text when services_provided includes additional slug; describe services
                              that did not match existing slugs; omit otherwise.
  process_types               ws_process_type slugs; omit when unclear
  case_stages                 ws_case_stage slugs; use other slug when coverage is described that slugs don't
                              fit cleanly, or coverage is entirely unclear.
  case_stage_details          free text when case_stages includes other slug; describe the org's claim of
                              coverage or note the absence of coverage.
  disclosure_targets          ws_disclosure_targets slugs; use has-details slug when
                              disclosure target coverage exists but no slug fits cleanly, or
							  coverage is entirely unclear.
  disclosure_targets_details  free text when disclosure_targets includes has-details slug; describe the
                              org's claim of coverage or note the absence of coverage.
  jurisdiction_exceptions     free text listing self-reported coverage gaps
                              (e.g. "nationwide except Texas"); omit if none are stated.
  whistleblower_scope         integer 0-3:
                                0 = scope unclear or org is too general to rate
                                1 = not whistleblower-specific (e.g. general ABA referral)
                                2 = subset of whistleblower concerns (e.g. securities fraud only)
                                3 = all or broad whistleblower concerns
  whistleblower_note          verbatim quote (up to 3 sentences) from the org's own site
                              describing its whistleblower mission; when scope is 0, state
                              the reason for inclusion instead.

contact:
  intake_url                  direct URL to the org's intake entry point or start-here page;
                              "find a lawyer" type pages are acceptable. omit when unavailable
							  or unverified.
  contact_url                 general contact page or form URL; distinct from intake_url;
                              omit when unavailable or unverified.
  phones                      array of { "type": "...", "number": "..." } objects;
                              type must be one of: hotline | intake | headquarters |
                              regional | tty | fax | other; if type is other, describe
							  in _review_notes; omit the entire field when no phone is found.
  emails                      array of { "type": "...", "address": "..." } objects;
                              type must be one of: intake | general | legal | media |
                              support | other; if type is other, describe in _review_notes;
                              omit the entire field when no email is found.
  mailing_address             physical mailing address as plain text; omit if unavailable.

eligibility:
  income_eligibility_required yes | no | unclear
  income_eligibility_details  specific income thresholds or criteria; required when
                              income_eligibility_required is yes; omit otherwise.
  eligibility_notes           non-income eligibility constraints (e.g. employer size thresholds
                              or union membership requirements); omit when none are stated.

security:
  has_secure_channel          yes | no | unclear
                              A secure channel means a dedicated encrypted tool:
                              SecureDrop, Signal, ProtonMail, Tutanota, Wire, Keybase.
                              A standard HTTPS web form does not qualify.
  secure_contact_url          URL to the secure channel or page providing instructions; required
                              when has_secure_channel is yes.
  secure_contact_tool         tool name; required when has_secure_channel is yes;
                              choose one: SecureDrop | Signal | ProtonMail | Tutanota |
                              Wire | Keybase | other
  secure_contact_tool_other   describe the tool when secure_contact_tool is other.
  anonymous_pre_consult_possible  yes | no | unclear
                              Can a user make initial contact without identifying themselves?
  has_attorneys               yes | no | unclear
                              Are licensed attorneys available for intake or representation
							  through this org?

review:
  legitimacy_url              URL to a secondary source confirming legitimacy (GuideStar,
                              Charity Navigator, court listing, congressional directory);
                              This is a nice-to-have-but-not-required datapoint; omit when not found.
  _review_notes               required — free text for anything a human reviewer might need
                              to know that the schema did not capture; use the default
                              fallback only when you truly have nothing to add.

                              Good examples:
                              — "intake requires account creation before service details
                                 are visible"
                              — "org is well-regarded but intake page states it is not
                                 currently accepting new clients"
                              — "'nationwide' claim is qualified in FAQ to licensed states
                                 only"
                              — "site recommends Signal and Tor but actual intake is
                                 through a standard HTTPS form; has_secure_channel was set
                                 to no"
                              — "org appears to offer help but does not clearly describe
                                 its services; services_provided left empty intentionally"
                              — "phone type marked other: ilisted as 'after-hours crisis line
							     for existing clients only'; included for human review"
                              — "email type marked other: listed as 'whistleblower secure
                                 tips inbox' without clear fit to slugs; included for
                                 human review"
                              — "site language/support ambiguous; languages_supported
                                 left empty intentionally"

                              You may use _review_notes to briefly explain:
                              — why you left a taxonomy array empty even though the org seems
                                 important (e.g. "org clearly serves federal workers but
                                 employment_sectors mapping is ambiguous")
                              — why existing slugs do not fully capture the real-world pattern
                                 you found

                              Keep notes concise; a few focused sentences can be very useful.

---

ORGANIZATION INCLUSION RULES
  Must provide direct help or a fast referral pathway.
  Prioritize orgs with actionable intake paths over informational pages.

ORGANIZATION EXCLUSION RULES
  Exclude pure government reporting channels.
  Exclude media tip lines without a user support pathway.
  Exclude private law firms with billable-hour primary intake.
  Contingency-fee, pro bono, and legal-aid models are not excluded.

---


BLOCK;
}

function ws_prompt_assist_org_record_schema_block(): string {
        return <<<'BLOCK'
RECORD SCHEMA

{
    "identity": {
        "official_name": "",
        "official_homepage_url": "",
        "general_description": "",
        "common_name": "",
        "homepage_url_status": "",
        "verified_url_date": ""
    },
    "scope_of_service": {
        "nationwide_example": "",
        "disclosure_types": [],
        "protected_classes": [],
        "protected_class_details": "",
        "languages_supported": [],
        "languages_additional": "",
        "assistance_type": "",
        "employment_sectors": [],
        "cost_models": [],
        "services_provided": [],
        "additional_services": "",
        "process_types": [],
        "case_stages": [],
        "case_stage_details": "",
        "disclosure_targets": [],
        "disclosure_targets_details": "",
        "jurisdiction_exceptions": "",
        "whistleblower_scope": 0,
        "whistleblower_note": ""
    },
    "contact": {
        "intake_url": "",
        "contact_url": "",
        "phones": [],
        "emails": [],
        "mailing_address": ""
    },
    "eligibility": {
        "income_eligibility_required": "",
        "income_eligibility_details": "",
        "eligibility_notes": ""
    },
    "security": {
        "has_secure_channel": "",
        "secure_contact_url": "",
        "secure_contact_tool": "",
        "secure_contact_tool_other": "",
        "anonymous_pre_consult_possible": "",
        "has_attorneys": ""
    },
    "review": {
        "legitimacy_url": "",
        "_review_notes": ""
    }
}


BLOCK;
}

function ws_generate_assist_org_prompt( array $scope ): string {
    $jx              = strtoupper( sanitize_text_field( $scope['jx_id'] ) );
    $jx_name         = sanitize_text_field( $scope['jx_name'] );
    $proposal_count  = max( 0, (int) ( $scope['proposal_count'] ?? 0 ) );
    $nationwide_only = ! empty( $scope['nationwide_only'] );
    $focus_notes     = sanitize_textarea_field( (string) ( $scope['assist_org_focus_notes'] ?? '' ) );
    $focus_notes     = trim( preg_replace( '/\R+/', "\n", $focus_notes ) );
    $excludes        = sanitize_textarea_field( (string) ( $scope['exclusion_list'] ?? '' ) );

    $out  = ws_prompt_assist_org_research_block();
    $out .= ws_prompt_what_you_are_producing( 'assistance organization records' );
    $out .= ws_prompt_assist_org_meta_schema();
    $out .= ws_prompt_assist_org_field_rules_block();
    $out .= ws_prompt_assist_org_record_schema_block();
    //$out .= ws_prompt_parent_slug_block(); // Parent slug blocking language has been reworked and moved directly to taxonomy tables
    $out .= ws_prompt_taxonomy_tables( 'ws-assist-org' );
    $out .= ws_prompt_integrity_block();

    $out .= "RUN SCOPE\n\n";
    $out .= "Record type:        assist-org\n";
    $out .= "Jurisdiction:       {$jx_name}\n";
    $out .= "Jurisdiction ID:    {$jx}\n";
    $out .= "Requested Records:  {$proposal_count}\n";
    $out .= 'meta.nationwide_only: ' . ( $nationwide_only ? 'true' : 'false' ) . "\n";
    if ( $focus_notes !== '' ) {
        $out .= "Focus notes:\n---\n{$focus_notes}\n---\n";
    }
    $out .= ws_prompt_render_exclusion_list( $excludes, 'Do not return organizations already known in this list:' );

    if ( $nationwide_only ) {
        $out .= "\nWhen \"meta.nationwide_only\" is true, return only nationwide or large multi-jurisdictional scoped organizations. ";
        $out .= "Do not include single jurisdiction organizations, or organizations that only help federal employees.\n";
    } else {
        $out .= "\nWhen \"meta.nationwide_only\" is false, include strong {$jx} fits and optional broader coverage.\n";
    }

    $out .= ws_prompt_truncation_permission_assist_org( $proposal_count );
    $out .= ws_prompt_final_json_block();

    return $out;
}


// ── Record schemas ────────────────────────────────────────────────────────

function ws_prompt_statute_schema(): string {
    return <<<'ENDSCHEMA'

RECORD SCHEMA

{
  "jurisdiction_id":  "[TWO-LETTER CODE]",
  "statute_id":       "[JURISDICTION_ID-SECTION e.g. CA-1102.5]",
  "official_name":    "[FULL OFFICIAL STATUTE NAME]",
  "common_name":      "[PLAIN LANGUAGE COMMON NAME — omit if none exists]",

  "legal_basis": {
    "statute_citation":           "[FORMAL CITATION e.g. Cal. Lab. Code § 1102.5]",
    "disclosure_types":           [],
    "protected_class":            [],
    "protected_class_details":    "[FREE TEXT — omit unless protected_class uses has-details]",
    "disclosure_targets":         [],
    "disclosure_targets_details": "[FREE TEXT — omit unless disclosure_targets uses has-details]",
    "adverse_action_scope":       "[FREE TEXT — scope of covered adverse actions]"
  },

  "statute_of_limitations": {
    "limit_ambiguous":     false,
    "limit_value":         0,
    "limit_unit":          "[days | months | years]",
    "limit_details":       "[omit if limit_ambiguous is false]",
    "trigger":             "[omit if unknown]",
    "exhaustion_required": false,
    "exhaustion_details":  "[omit if exhaustion_required is false]",
    "tolling_notes":       "[omit if none identified]"
  },

  "enforcement": {
    "primary_agency": "[omit if unknown]",
    "process_type":   [],
    "adverse_action": [],
    "adverse_action_details": "[FREE TEXT — omit unless adverse_action uses has-details]",
    "remedies":       [],
    "remedies_details": "[FREE TEXT — omit unless remedies uses has-details]",
    "fee_shifting":   "[omit if empty]"
  },

  "burden_of_proof": {
    "employee_standard":         [],
    "employee_standard_details": "[FREE TEXT — omit unless employee_standard uses has-details]",
    "employer_defense":          [],
    "employer_defense_details":  "[FREE TEXT — omit unless employer_defense uses has-details]",
    "rebuttable_presumption":    "[omit if none identified]",
    "burden_of_proof_details":   "[omit if none]",
    "burden_of_proof_flag":      "[omit unless a meaningful burden shift is identified]"
  },

  "reward": {
    "available":      false,
    "reward_details": "[omit if available is false]"
  },

  "links": {
    "statute_url": "[omit if no approved source identified]",
    "is_official": false,
    "url_source":  "[domain name — omit if is_official is true or no URL]",
    "is_pdf":      "[omit if false]"
  },

  "citations": {
    "attached_citations": [],
    "citation_count":     0
  },

  "_review_notes":      ""
}

---

SCHEMA NOTES

statute_id: Use [JURISDICTION_ID-SECTION] only. Use the chapter entry-point
section, not mid-chapter provisions. Do not include code prefixes (LAB, GOV).
Do not invent cluster IDs.

limit_ambiguous: Set to true whenever the SOL is derived from a general civil
procedure statute, secondary source, or case law — regardless of confidence.
A zero limit_value with limit_ambiguous false implies the deadline is
verifiably zero; use this only when literally correct.

remedies: If a statute refers to "actual damages" map to compensatory-damages
and note in _review_notes. If "special damages" map to consequential-damages
and note in _review_notes.

CALCULATED FIELDS — compute last, after all records are finalized:
  meta.record_count         — must equal length of records array
  meta.proposed_count       — must equal length of new_terms_proposed
  citations.citation_count  — must equal length of attached_citations
  integrity.error_count     — must equal length of error_details

ENDSCHEMA;
}

function ws_prompt_common_law_schema(): string {
    return <<<'ENDSCHEMA'

RECORD SCHEMA

{
  "jurisdiction_id": "[TWO-LETTER CODE]",
  "doctrine_id":     "[JX]-CL-[SHORT-SLUG e.g. WY-CL-PUBLIC-POLICY]",
  "doctrine_name":   "[FORMAL DOCTRINE NAME]",
  "common_name":     "[SHORTHAND NAME — omit if none widely used]",

  "legal_basis": {
    "doctrine_basis":             "[legal principle, leading cases, how protection works]",
    "recognition_status":         "[current judicial status, well-established vs contested]",
    "public_policy_sources":      ["[constitution | statute | administrative-rule | case-law | federal-law | other]"],
    "other_sources":              "[omit unless 'other' is in public_policy_sources]",
    "disclosure_types":           [],
    "protected_class":            [],
    "protected_class_details":    "[FREE TEXT — omit unless protected_class uses has-details]",
    "disclosure_targets":         [],
    "disclosure_targets_details": "[FREE TEXT — omit unless disclosure_targets uses has-details]",
    "adverse_action_scope":       "[FREE TEXT — scope of covered adverse actions]"
  },

  "statute_of_limitations": {
    "limit_ambiguous":     true,
    "limit_value":         0,
    "limit_unit":          "[days | months | years]",
    "limit_details":       "[required — identify the analogous statute the period is borrowed from]",
    "trigger":             "[omit if unknown]",
    "exhaustion_required": false,
    "exhaustion_details":  "[omit if exhaustion_required is false]",
    "tolling_notes":       "[omit if none identified]"
  },

  "enforcement": {
    "primary_agency": "[omit if unknown]",
    "process_type":   [],
    "adverse_action": [],
    "adverse_action_details": "[FREE TEXT — omit unless adverse_action uses has-details]",
    "remedies":       [],
    "remedies_details": "[FREE TEXT — omit unless remedies uses has-details]",
    "fee_shifting":   "[omit if empty]"
  },

  "burden_of_proof": {
    "statutory_preclusion":         false,
    "statutory_preclusion_details": "[omit if statutory_preclusion is false]",
    "employee_standard":            [],
    "employee_standard_details":    "[FREE TEXT — omit unless employee_standard uses has-details]",
    "employer_defense":             [],
    "employer_defense_details":     "[FREE TEXT — omit unless employer_defense uses has-details]",
    "rebuttable_presumption":       "[omit if none identified]",
    "burden_of_proof_details":      "[omit if none]",
    "burden_of_proof_flag":         "[omit unless a meaningful burden shift is identified]"
  },

  "reward": {
    "available":      false,
    "reward_details": "[omit if available is false]"
  },

  "links": {
    "precedent_url": "[omit if no approved source identified]",
    "is_official":   false,
    "url_source":    "[domain name — omit if is_official is true or no URL]",
    "is_pdf":      "[omit if false]"
  },

  "citations": {
    "attached_citations": [],
    "citation_count":     0
  },

  "_review_notes":     ""
}

---

SCHEMA NOTES

doctrine_id: Format [JX]-CL-[SHORT-SLUG] in kebab-case, max 4-5 words after
CL. Used in prompt exclusion lists to prevent duplicate records.

limit_ambiguous: Almost always true for common law — SOL is borrowed from the
nearest analogous statute. Document the source statute in limit_details.
If limit_details cannot be identified, set with_errors true and explain in
integrity.error_details. Do not silently omit limit_details for common law.

statutory_preclusion: Set to true when this jurisdiction's courts hold that
the common law claim is unavailable when a statutory remedy for the same
conduct exists. Document the controlling cases in statutory_preclusion_details.

CALCULATED FIELDS — compute last:
  meta.record_count        — must equal length of records array
  meta.proposed_count      — must equal length of new_terms_proposed
  citations.citation_count — must equal length of attached_citations
  integrity.error_count    — must equal length of error_details

ENDSCHEMA;
}

function ws_prompt_citation_schema(): string {
    $out = <<<'ENDSCHEMA'

RECORD SCHEMA

{
  "jurisdiction_id":   "[TWO-LETTER CODE]",
  "citation_id":       "[JX]-CIT-[YEAR]-[SHORT-SLUG e.g. NJ-CIT-2003-DZWONAR]",
  "parent_statute_id": "[STATUTE_ID this citation directly supports e.g. NJ-34:19-1]",
  "parent_common_law_id": "[OPTIONAL DOCTRINE_ID when citation supports common-law e.g. PA-CL-WRONGFUL-DISCHARGE]",
  "case_name":         "[FULL CASE NAME e.g. Dzwonar v. McDevitt]",
  "court":             "[COURT SHORTHAND from list above e.g. NJ-SUP]",
  "effective_date":    "[YYYY-MM-DD — operative date of ruling]",
  "ruling_date":       "[YYYY-MM-DD — decision date, omit if same as effective_date]",
  "specific_impact":   "[10-20 words, action-verb first — describes the legal holding]",
  "favorable":         true,

  "disclosure_types":    [],
  "protected_class":     [],
  "disclosure_targets":  [],
  "adverse_action":      [],
  "remedies":            [],
  "process_type":        [],
  "employer_defense":    [],
  "employee_standard":   [],

  "_multi_taxonomy_notes": "[Omit unless multiple taxonomy arrays are tagged — explain the intersection]",

  "links": {
    "case_url":    "[URL to case on approved source — omit if unverifiable]",
    "is_official": false,
    "url_source":  "[domain name — omit if is_official is true]",
    "is_pdf":      "[omit if false]"
  },

  "quality": "[high | moderate | low]",
  "_review_notes": ""
}

---

SCHEMA NOTES

citation_id: Format [JX]-CIT-[YEAR]-[SHORT-SLUG]. Year is ruling year.
Short slug is first meaningful word of the case name.

ENDSCHEMA;
    return $out . ws_prompt_case_law_schema_notes();
}

function ws_prompt_interpretation_schema( string $statute_type ): string {
    $court_note = $statute_type === 'federal'
        ? 'Federal statutes may be interpreted by federal or state courts.'
        : 'State statutes: include only state court interpretations unless a federal court ruling directly interprets this state statute.';

    $out = <<<ENDSCHEMA

RECORD SCHEMA

{$court_note}

{
  "jurisdiction_id":   "[TWO-LETTER CODE]",
  "interpretation_id": "[JX]-INTERP-[YEAR]-[SHORT-SLUG]",
  "parent_statute_id": "[STATUTE_ID this interpretation directly addresses]",
  "parent_common_law_id": "[OPTIONAL DOCTRINE_ID when interpretation addresses common-law doctrine]",
  "case_name":         "[FULL CASE NAME]",
  "court":             "[COURT SHORTHAND from list above]",
  "effective_date":    "[YYYY-MM-DD — operative date of ruling]",
  "ruling_date":       "[YYYY-MM-DD — omit if same as effective_date]",
  "specific_impact":   "[10-20 words, action-verb first — describes the legal holding]",
  "favorable":         true,

  "disclosure_types":    [],
  "protected_class":     [],
  "disclosure_targets":  [],
  "adverse_action":      [],
  "remedies":            [],
  "process_type":        [],
  "employer_defense":    [],
  "employee_standard":   [],

  "_multi_taxonomy_notes": "[Omit unless multiple taxonomy arrays are tagged — explain the intersection]",

  "links": {
    "case_url":    "[URL on approved source — omit if unverifiable]",
    "is_official": false,
    "url_source":  "[domain name — omit if is_official is true]",
    "is_pdf":      "[omit if false]"
  },

  "quality": "[high | moderate | low]",

  "_review_notes":     ""
}

---

SCHEMA NOTES

interpretation_id: Format [JX]-INTERP-[YEAR]-[SHORT-SLUG]. Year is ruling year.
Short slug is first meaningful word of the case name.

ENDSCHEMA;
    return $out . ws_prompt_case_law_schema_notes();
}


// ── Meta block schema (shared) ────────────────────────────────────────────

function ws_prompt_meta_schema(): string {
    return <<<ENDSCHEMA

META BLOCK SCHEMA

{
  "meta": {
    "json_format_version": "2.0",
    "source_method":       "ai_assisted",
    "source_name":         "[YOUR COMMON NAME e.g. Gemini]",
    "jurisdiction_id":     "[TWO-LETTER JURISDICTION CODE]",
    "generated_date":      "[YYYY-MM-DD]",
    "generated_by":        "[YOUR FULL MODEL NAME AND VERSION]",
    "record_count":        0,
    "proposed_count":      0,
    "new_terms_proposed":  [],
    "json_run_notes":      "",
    "batch_completed":     "[YYYY-MM-DD HH:MM UTC — written last]"
  }
}

batch_completed: Always use UTC. Format: YYYY-MM-DD HH:MM UTC.
Written last, after all records, proposals, and calculated fields are final.

ENDSCHEMA;
}

function ws_prompt_legal_research_intro(): string {
    $out  = "You are a legal research assistant generating structured JSON data for\n";
    $out .= "WhistleblowerShield.org, a public-interest reference site covering U.S.\n";
    $out .= "whistleblower protections across all 57 U.S. jurisdictions. Please read\n";
    $out .= "the entire prompt before execution.\n\n";
    return $out;
}

function ws_prompt_case_law_source_block(): string {
    return ws_prompt_citation_rules()
        . ws_prompt_get_court_shorthand();
}

function ws_prompt_case_taxonomy_discipline_block( string $label ): string {
    $mode = strtoupper( trim( $label ) );
    if ( $mode === 'INTERPRETATION' ) {
        return "TAXONOMY TAGGING — INTERPRETATION DISCIPLINE\n\n"
            . "Tag only what this specific ruling directly interprets or clarifies.\n"
            . "In almost all cases a court ruling addresses exactly one taxonomy axis.\n"
            . "Tag one term with confidence. If the ruling genuinely addresses multiple\n"
            . "axes, tag all and populate _multi_taxonomy_notes.\n\n";
    }

    return "TAXONOMY TAGGING — CITATION DISCIPLINE\n\n"
        . "Tag only what this specific ruling directly addresses. In almost all cases\n"
        . "a citation addresses exactly one taxonomy axis — tag one term with confidence\n"
        . "rather than several terms with uncertainty.\n\n"
        . "If and only if the ruling explicitly and materially addresses multiple\n"
        . "taxonomy axes in a single holding, tag all that apply AND populate\n"
        . "_multi_taxonomy_notes with a prose explanation of how the ruling touched\n"
        . "each axis. Multiple tags without _multi_taxonomy_notes is an error.\n\n";
}

function ws_prompt_what_you_are_producing( string $records_label ): string {
    return <<<BLOCK

---

WHAT YOU ARE PRODUCING

A single JSON object with three top-level keys:

  - "meta"      — one block describing this batch
  - "records"   — an array of {$records_label}
  - "integrity" — your honest self-report on the state of this batch

The ingest tool maps your JSON directly to internal data fields. Do not
add keys that are not in the schema. Do not reorder fields within a record.

---

BLOCK;
}

function ws_prompt_final_json_block(): string {
    return "---\n\nProduce the complete JSON object now, inside a single code block.\n"
         . "Do not include any commentary, explanation, or markdown outside the code block.\n";
}

function ws_prompt_render_exclusion_list( string $excludes, string $label ): string {
    $out = '';
    foreach ( explode( "\n", $excludes ) as $line ) {
        $line = trim( $line );
        if ( $line !== '' ) {
            $out .= "  {$line}\n";
        }
    }
    if ( $out === '' ) {
        return '';
    }
    return "Exclusion list: {$label}\n" . $out;
}

function ws_prompt_legal_taxonomy_fields(): string {
    return <<<'BLOCK'
TAXONOMY FIELDS

The following fields accept only the registered term slugs listed in the
taxonomy tables below. Use the slug that best fits. If no slug fits, leave
the array empty — do not invent a slug and insert it into the record.

  legal_basis.disclosure_types     → ws_disclosure_type
  legal_basis.protected_class      → ws_protected_class      → can use has-details
  legal_basis.disclosure_targets   → ws_disclosure_targets   → can use has-details
  enforcement.process_type         → ws_process_type
  enforcement.adverse_action       → ws_adverse_action_types → can use has-details
  enforcement.remedies             → ws_remedies             → can use has-details
  enforcement.fee_shifting         → ws_fee_shifting
  burden_of_proof.employee_standard → ws_employee_standard   → can use has-details
  burden_of_proof.employer_defense  → ws_employer_defense    → can use has-details

Any taxonomy field set to has-details requires the details in an associated
freetext field *_details following the taxonomy field. Omit the *_details
key entirely when the taxonomy field has a proper slug.

BLOCK;
}

function ws_prompt_case_law_schema_notes(): string {
    return <<<'BLOCK'

parent linkage: provide at least one of parent_statute_id or
parent_common_law_id. Provide both only when the record directly supports both.

taxonomy arrays: Tag sparingly. In almost all cases a ruling addresses exactly
one taxonomy axis — tag one term with confidence rather than several with
uncertainty. If and only if the ruling explicitly and materially addresses
multiple taxonomy axes in a single holding, tag all that apply and populate
_multi_taxonomy_notes. Multiple tags without _multi_taxonomy_notes is an error.
_multi_taxonomy_notes without multiple tags is an error.

favorable: Set to true only when the ruling materially expands, strengthens,
or clarifies protection in a way that changes what the statute covers or how
it applies. Procedural wins that do not move the legal line — omit. It must
be undeniably and overwhelmingly pro-whistleblower in scope.

effective_date: The date the ruling became operative law. Omit ruling_date
if the same as effective_date.

_review_notes: Omit only when you have nothing to add. When used, note
anything a person relying on this record for a real legal decision would
need to know: limited holdings, procedural context that affects scope,
circuit splits, or caveats the schema fields cannot capture.

CALCULATED FIELDS — compute last:
  meta.record_count     — must equal length of records array
  meta.proposed_count   — must equal length of new_terms_proposed
  integrity.error_count — must equal length of error_details

BLOCK;
}

function ws_prompt_truncation_permission(): string {
    return <<<'BLOCK'

As many as you can confidently verify (fewer is correct).
Attempt to find the requested number of records, but confidence is the hard
constraint.

Permission to fail and omit is explicit:
  - omit uncertain fields instead of guessing
  - omit uncertain records instead of padding the batch
  - set with_errors: true and explain gaps in integrity.error_details

Why this matters: fabricated or guessed legal information can cause real harm
(for example, missed filing deadlines, invalid venue assumptions, or reliance
on nonexistent precedent).

BLOCK;
}

function ws_prompt_truncation_permission_assist_org( int $requested_records = 0 ): string {
    $header = $requested_records > 0
        ? "Target up to {$requested_records} records, but return fewer when confidence is limited."
        : 'As many as you can confidently verify (fewer is correct).';

    return "\n{$header}\n"
        . "Attempt to find the requested number of records, but confidence is the hard\n"
        . "constraint.\n\n"
        . "Permission to fail and omit is explicit:\n"
        . "  - omit uncertain fields instead of guessing\n"
        . "  - omit uncertain records instead of padding the batch\n"
        . "  - set with_errors: true and explain gaps in integrity.error_details\n\n"
        . "Why this matters: fabricated or guessed assistance-organization details can\n"
        . "send people in crisis to dead intake channels, incorrect eligibility/cost\n"
        . "expectations, or insecure disclosure paths.\n\n";
}

function ws_prompt_assist_org_meta_schema(): string {
    return <<<'ENDSCHEMA'

TOP-LEVEL OUTPUT SCHEMA

{
  "meta": {
    "json_format_version": "2.0",
    "source_method": "ai_assisted",
    "source_name": "[MODEL COMMON NAME]",
    "jurisdiction_id": "[US OR STATE CODE]",
    "generated_date": "[YYYY-MM-DD]",
    "generated_by": "[FULL MODEL NAME + VERSION]",
    "nationwide_only": false,
    "record_count": 0,
    "json_run_notes": "",
    "_json_run_researcher_notes": "[OPTIONAL — any contextual researcher note that does not fit other fields. Example: domain appears unusual but resolves to a legitimate nonprofit homepage.]",
    "batch_completed": "[YYYY-MM-DD HH:MM UTC]"
  },
  "records": [],
  "integrity": {
    "with_errors": false
  }
}

meta.json_run_notes: include any notes about the entire run that you
feel a human reviewer would want to know.
meta.nationwide_only: must match RUN SCOPE Nationwide only exactly (true/false).
If true, each returned record should include nationwide_example evidence.
meta._json_run_researcher_notes: anything that isn't specifically
task related, things that don't quite fit in *_notes. Stripped at ingest,
but maintained in the archival records.

meta.batch_completed: Always use UTC. Format: YYYY-MM-DD HH:MM UTC.
Must exist, must be UTC, used for archival purposes.
Written last, after all records and calculated fields are final.

CALCULATED FIELDS - write last
  - meta.record_count     must equal length of records array, not requested record count.
  - integrity.error_count must equal length of error_details (when with_errors is true)

---

ENDSCHEMA;
}


// ── Full prompt assemblers ────────────────────────────────────────────────

function ws_generate_statute_prompt( array $scope ): string {
    $jx       = strtoupper( sanitize_text_field( $scope['jx_id'] ) );
    $jx_name  = sanitize_text_field( $scope['jx_name'] );
    $leg_url  = esc_url_raw( $scope['legislature_url'] );
    $records  = (int) $scope['records_requested'];
    $notes    = sanitize_textarea_field( $scope['scope_notes'] );
    $excludes = sanitize_textarea_field( $scope['exclusion_list'] );

    $out  = ws_prompt_legal_research_intro();
    $out .= "This data will enter a human review queue before anything is published.\n";
    $out .= "You are not the final authority — you are the first pass. Your job is to\n";
    $out .= "produce the most accurate draft you can, and to be honest about what you\n";
    $out .= "could not find. A wrong statute of limitations or a fabricated citation\n";
    $out .= "could cause real harm to a worker relying on this information.\n";
    $out .= "Honest gaps do not. When in doubt, always choose omission.\n\n";
    $out .= ws_prompt_what_you_are_producing( 'statute records' );
    $out .= ws_prompt_omission_rules();
    $out .= "\n";
    $out .= ws_prompt_legal_taxonomy_fields();
    $out .= ws_prompt_parent_slug_block();
    $out .= ws_prompt_taxonomy_tables( 'jx-statute' );
    $out .= ws_prompt_proposal_block();
    $out .= ws_prompt_statute_rules();
    $out .= ws_prompt_citation_rules();
    $out .= ws_prompt_meta_schema();
    $out .= ws_prompt_statute_schema();
    $out .= ws_prompt_integrity_block();

    // RUN SCOPE
    $out .= "RUN SCOPE\n\n";
    $out .= "Jurisdiction:       {$jx_name}\n";
    $out .= "Jurisdiction ID:    {$jx}\n";
    $out .= "Legislature URL:    {$leg_url}\n";
    $out .= "Record type:        statute\n";
    if ( $records > 0 ) {
        $out .= "Records Requested:  {$records}\n";
    } else {
        $out .= "Records Requested:  as many as you can confidently verify (fewer is correct)\n";
    }
    if ( $notes ) {
        $out .= "Scope:              {$notes}\n";
    }
    $out .= ws_prompt_render_exclusion_list( $excludes, 'Do not produce records for any statute in this list:' );
    $out .= "\n\nThis template covers `statute` records only.\n";
    $out .= "Other record types use separate templates.\n\n";
    $out .= "If any field in the RUN SCOPE is missing, vague, or still holding a\n";
    $out .= "placeholder value, the directive is malformed. Abort immediately.\n\n";
    $out .= "If you cannot confirm a statute exists or locate it at any approved source,\n";
    $out .= "do not fabricate a record.\n";
    $out .= ws_prompt_truncation_permission();
    $out .= ws_prompt_final_json_block();

    return $out;
}

function ws_generate_common_law_prompt( array $scope ): string {
    $jx       = strtoupper( sanitize_text_field( $scope['jx_id'] ) );
    $jx_name  = sanitize_text_field( $scope['jx_name'] );
    $leg_url  = esc_url_raw( $scope['legislature_url'] );
    $records  = (int) $scope['records_requested'];
    $notes    = sanitize_textarea_field( $scope['scope_notes'] );
    $excludes = sanitize_textarea_field( $scope['exclusion_list'] );

    $out  = ws_prompt_legal_research_intro();
    $out .= "This template covers `common-law` records ONLY — judicially-recognized\n";
    $out .= "whistleblower protections that exist outside codified statute. Do not\n";
    $out .= "produce records for statutory protections — those use a separate template.\n\n";
    $out .= "A wrong SOL or fabricated case citation could cause real harm.\n";
    $out .= "Honest gaps do not. When in doubt, always choose omission.\n\n";
    $out .= ws_prompt_what_you_are_producing( 'common law doctrine records' );
    $out .= ws_prompt_omission_rules();
    $out .= "\n";
    $out .= ws_prompt_legal_taxonomy_fields();
    $out .= ws_prompt_parent_slug_block();
    $out .= ws_prompt_taxonomy_tables( 'jx-common-law' );
    $out .= ws_prompt_proposal_block();
    $out .= ws_prompt_citation_rules();
    $out .= ws_prompt_meta_schema();
    $out .= ws_prompt_common_law_schema();
    $out .= ws_prompt_integrity_block();

    $out .= "RUN SCOPE\n\n";
    $out .= "Jurisdiction:       {$jx_name}\n";
    $out .= "Jurisdiction ID:    {$jx}\n";
    $out .= "Legislature URL:    {$leg_url}\n";
    $out .= "Record type:        common-law\n";
    if ( $records > 0 ) {
        $out .= "Records Requested:  {$records}\n";
    } else {
        $out .= "Records Requested:  as many as you can confidently verify (fewer is correct)\n";
    }
    if ( $notes ) {
        $out .= "Scope:              {$notes}\n";
    }
    $out .= ws_prompt_render_exclusion_list( $excludes, 'Do not produce records for any doctrine in this list:' );
    $out .= "\n\nIf you cannot confirm a common law doctrine exists with reasonable\n";
    $out .= "confidence, do not fabricate a record.\n";
    $out .= ws_prompt_truncation_permission();
    $out .= ws_prompt_final_json_block();

    return $out;
}

function ws_generate_citation_prompt( array $scope ): string {
    $jx         = strtoupper( sanitize_text_field( $scope['jx_id'] ) );
    $jx_name    = sanitize_text_field( $scope['jx_name'] );
    $leg_url    = esc_url_raw( $scope['legislature_url'] );
    $statutes   = sanitize_textarea_field( $scope['scope_notes'] );
    $min_q      = sanitize_text_field( $scope['min_quality'] ?? 'moderate' );
    $excludes   = sanitize_textarea_field( $scope['exclusion_list'] );

    $out  = "You are a legal research assistant generating structured JSON citation data\n";
    $out .= "for WhistleblowerShield.org. Please read the entire prompt before execution.\n\n";
    $out .= "Your task is to find case law citations that directly support, interpret,\n";
    $out .= "or materially expand the protections of the statutes listed in the RUN SCOPE.\n";
    $out .= "You are not researching new statutes — you are finding case law that anchors\n";
    $out .= "to existing statute records.\n\n";
    $out .= "A fabricated citation could cause real harm. If you cannot supply both a\n";
    $out .= "real case with reasonable confidence AND a verifiable URL from an approved\n";
    $out .= "source, omit the citation entirely.\n\n";
    $out .= ws_prompt_what_you_are_producing( 'citation records' );
    $out .= ws_prompt_omission_rules();
    $out .= ws_prompt_parent_slug_block();
    $out .= ws_prompt_case_law_source_block();
    $out .= ws_prompt_case_taxonomy_discipline_block( 'citation' );
    $out .= ws_prompt_taxonomy_tables( 'jx-citation' );
    $out .= ws_prompt_meta_schema();
    $out .= ws_prompt_citation_schema();
    $out .= ws_prompt_integrity_block();

    $out .= "RUN SCOPE\n\n";
    $out .= "Jurisdiction:       {$jx_name}\n";
    $out .= "Jurisdiction ID:    {$jx}\n";
    $out .= "Legislature URL:    {$leg_url}\n";
    $out .= "Record type:        citation\n";
    $out .= "Minimum quality:    {$min_q}\n";
    if ( $statutes ) {
        $out .= "Find citations for these statutes:\n";
        foreach ( explode( "\n", $statutes ) as $line ) {
            $line = trim( $line );
            if ( $line ) $out .= "  {$line}\n";
        }
    }
    $out .= ws_prompt_render_exclusion_list( $excludes, 'Do not produce records for these cases:' );
    $out .= ws_prompt_truncation_permission();
    $out .= ws_prompt_final_json_block();

    return $out;
}

function ws_generate_interpretation_prompt( array $scope ): string {
    $jx           = strtoupper( sanitize_text_field( $scope['jx_id'] ) );
    $jx_name      = sanitize_text_field( $scope['jx_name'] );
    $leg_url      = esc_url_raw( $scope['legislature_url'] );
    $statutes     = sanitize_textarea_field( $scope['scope_notes'] );
    $statute_type = sanitize_text_field( $scope['statute_type'] ?? 'state' );
    $min_q        = sanitize_text_field( $scope['min_quality'] ?? 'moderate' );
    $excludes     = sanitize_textarea_field( $scope['exclusion_list'] );

    $out  = "You are a legal research assistant generating structured JSON interpretation\n";
    $out .= "data for WhistleblowerShield.org. Please read the entire prompt before execution.\n\n";
    $out .= "Your task is to find court rulings that directly interpret the statutes listed\n";
    $out .= "in the RUN SCOPE — rulings that clarify what the statute means, who it covers,\n";
    $out .= "how it is applied, or where its limits lie.\n\n";
    $out .= "A fabricated citation could cause real harm. If you cannot supply both a real\n";
    $out .= "case with reasonable confidence AND a verifiable URL, omit it entirely.\n\n";
    $out .= ws_prompt_what_you_are_producing( 'interpretation records' );
    $out .= ws_prompt_omission_rules();
    $out .= ws_prompt_parent_slug_block();
    $out .= ws_prompt_case_law_source_block();
    $out .= ws_prompt_case_taxonomy_discipline_block( 'interpretation' );
    $out .= ws_prompt_taxonomy_tables( 'jx-interpretation' );
    $out .= ws_prompt_meta_schema();
    $out .= ws_prompt_interpretation_schema( $statute_type );
    $out .= ws_prompt_integrity_block();

    $out .= "RUN SCOPE\n\n";
    $out .= "Jurisdiction:       {$jx_name}\n";
    $out .= "Jurisdiction ID:    {$jx}\n";
    $out .= "Legislature URL:    {$leg_url}\n";
    $out .= "Record type:        interpretation\n";
    $out .= "Statute type:       {$statute_type}\n";
    $out .= "Minimum quality:    {$min_q}\n";
    if ( $statutes ) {
        $out .= "Find interpretations for these statutes:\n";
        foreach ( explode( "\n", $statutes ) as $line ) {
            $line = trim( $line );
            if ( $line ) $out .= "  {$line}\n";
        }
    }
    $out .= ws_prompt_render_exclusion_list( $excludes, 'Do not produce records for these cases:' );
    $out .= ws_prompt_truncation_permission();
    $out .= ws_prompt_final_json_block();

    return $out;
}


// ── Form handler ──────────────────────────────────────────────────────────

function ws_handle_prompt_generation(): array {
    $result = [ 'success' => false, 'message' => '', 'filename' => '', 'path' => '' ];

    if ( empty( $_POST['ws_prompt_nonce'] ) || ! wp_verify_nonce( $_POST['ws_prompt_nonce'], 'ws_generate_prompt' ) ) {
        $result['message'] = 'Security check failed.';
        return $result;
    }

    if ( ! current_user_can( 'manage_options' ) ) {
        $result['message'] = 'Insufficient permissions.';
        return $result;
    }

    $record_type = sanitize_text_field( $_POST['record_type'] ?? '' );
    $jx_id       = strtoupper( sanitize_text_field( $_POST['jx_id'] ?? '' ) );
    $records_requested = max( 0, (int) ( $_POST['records_requested'] ?? 0 ) );
    $proposal_count = max( 0, (int) ( $_POST['proposal_count'] ?? 0 ) );

    if ( ! $record_type || ! preg_match( '/^[A-Z]{2}$/', $jx_id ) ) {
        $result['message'] = 'Record type and a valid two-letter jurisdiction code are required.';
        return $result;
    }

    if ( $record_type === 'assist-org' ) {
        $records_requested = $proposal_count;
    }

    $jx_context = ws_prompt_resolve_jx_context( $jx_id );
    $jx_name    = $jx_context['jx_name'];
    $leg_url    = $jx_context['legislature_url'];

    $scope_notes = sanitize_textarea_field( $_POST['scope_notes'] ?? '' );
    if ( $scope_notes === '' && in_array( $record_type, [ 'statute', 'common-law' ], true ) ) {
        $jx_type     = sanitize_key( $jx_context['jx_type'] ?: 'state' );
        $scope_notes = $jx_type . '-level whistleblower laws and protections';
    }

    $disable_exclusions = ! empty( $_POST['disable_exclusion_list'] );
    $auto_exclusions = ws_prompt_get_auto_exclusions( $record_type, $jx_id );
    $auto_exclusions_input = ws_prompt_resolve_auto_exclusions_text( $_POST, $auto_exclusions );
    $exclusion_list = '';
    if ( ! $disable_exclusions ) {
        $exclusion_list  = ws_prompt_merge_exclusions(
            (string) ( $_POST['exclusion_list_manual'] ?? ( $_POST['exclusion_list'] ?? '' ) ),
            ws_prompt_split_lines( $auto_exclusions_input )
        );
    }

    $scope = [
        'jx_id'           => $jx_id,
        'jx_name'         => $jx_name,
        'legislature_url' => $leg_url,
        'records_requested' => $records_requested,
        'proposal_count'  => $proposal_count,
        'scope_notes'     => $scope_notes,
        'assist_org_focus_notes' => sanitize_textarea_field( $_POST['assist_org_focus_notes'] ?? '' ),
        'nationwide_only' => ! empty( $_POST['assist_org_nationwide'] ) ? 1 : 0,
        'exclusion_list'  => $exclusion_list,
        'min_quality'     => sanitize_text_field( $_POST['min_quality'] ?? 'moderate' ),
        'statute_type'    => sanitize_text_field( $_POST['statute_type'] ?? 'state' ),
    ];

    switch ( $record_type ) {
        case 'statute':
            $prompt = ws_generate_statute_prompt( $scope );
            break;
        case 'common-law':
            $prompt = ws_generate_common_law_prompt( $scope );
            break;
        case 'citation':
            $prompt = ws_generate_citation_prompt( $scope );
            break;
        case 'interpretation':
            $prompt = ws_generate_interpretation_prompt( $scope );
            break;
        case 'assist-org':
            $prompt = ws_generate_assist_org_prompt( $scope );
            break;
        default:
            $result['message'] = 'Unknown record type.';
            return $result;
    }

    $dir      = ws_prompt_output_dir();
    $filename = strtoupper( $jx_id ) . '-' . $records_requested . '-' . ucfirst( $record_type ) . '-' . date( 'Ymd-Hi' ) . '.txt';
    $filepath = $dir . '/' . $filename;

    if ( file_put_contents( $filepath, $prompt ) === false ) {
        $result['message'] = 'Failed to write prompt file. Check directory permissions on ' . $dir;
        return $result;
    }

    $result['success']  = true;
    $result['message']  = 'Prompt generated successfully.';
    $result['filename'] = $filename;
    $result['path']     = str_replace( ABSPATH, '/', $filepath );

    return $result;
}


// ── Admin page renderer ───────────────────────────────────────────────────

function ws_render_prompt_generator_page() {

    if ( ! current_user_can( 'manage_options' ) ) {
        wp_die( 'Access denied.' );
    }

    $result = null;
    $is_refresh_only = isset( $_POST['ws_refresh_exclusions'] );
    if ( isset( $_POST['ws_prompt_nonce'] ) && ! $is_refresh_only ) {
        $result = ws_handle_prompt_generation();
    }
    if ( isset( $_POST['ws_prompt_nonce'] ) && $is_refresh_only ) {
        $result = [
            'success'  => true,
            'message'  => 'Auto exclusions refreshed from current records for the selected jurisdiction and record type.',
            'filename' => '',
            'path'     => '',
        ];
    }

    $record_type = sanitize_text_field( $_POST['record_type'] ?? 'statute' );
    $proposal_count_value = max( 0, (int) ( $_POST['proposal_count'] ?? 0 ) );
    $assist_org_nationwide = ! empty( $_POST['assist_org_nationwide'] );
    $disable_exclusions = ! empty( $_POST['disable_exclusion_list'] );
    $assist_org_focus_notes = sanitize_textarea_field( $_POST['assist_org_focus_notes'] ?? '' );
    $posted_jx   = strtoupper( sanitize_text_field( $_POST['jx_id'] ?? '' ) );
    $auto_exclusions = ( $posted_jx && $record_type ) ? ws_prompt_get_auto_exclusions( $record_type, $posted_jx ) : [];
    $missing_statute_hidden_ids = ( $record_type === 'statute' && $posted_jx )
        ? ws_prompt_get_statute_posts_missing_hidden_id( $posted_jx )
        : [];
    $default_scope_note = 'state-level whistleblower laws and protections';
    if ( $posted_jx ) {
        $ctx = ws_prompt_resolve_jx_context( $posted_jx );
        $default_scope_note = sanitize_key( $ctx['jx_type'] ?: 'state' ) . '-level whistleblower laws and protections';
    }
    $scope_note_value = sanitize_textarea_field( $_POST['scope_notes'] ?? '' );
    if ( $scope_note_value === '' && in_array( $record_type, [ 'statute', 'common-law' ], true ) ) {
        $scope_note_value = $default_scope_note;
    }
    $manual_exclusions = sanitize_textarea_field( $_POST['exclusion_list_manual'] ?? ( $_POST['exclusion_list'] ?? '' ) );
    $auto_exclusions_text = ws_prompt_resolve_auto_exclusions_text( $_POST, $auto_exclusions );
    $auto_count = count( ws_prompt_split_lines( $auto_exclusions_text ) );
    $manual_count = count( ws_prompt_split_lines( $manual_exclusions ) );
    $merged_count = $disable_exclusions
        ? 0
        : count( ws_prompt_split_lines( ws_prompt_merge_exclusions( $manual_exclusions, ws_prompt_split_lines( $auto_exclusions_text ) ) ) );
    $auto_exclusion_key_label = 'canonical record identifier (when available)';
    if ( $record_type === 'statute' ) {
        $auto_exclusion_key_label = '_ws_jx_statute_id';
    } elseif ( $record_type === 'common-law' ) {
        $auto_exclusion_key_label = '_ws_cl_doctrine_id';
    } elseif ( $record_type === 'citation' ) {
        $auto_exclusion_key_label = '_ws_jx_citation_id (fallback: case title)';
    } elseif ( $record_type === 'interpretation' ) {
        $auto_exclusion_key_label = '_ws_jx_interpretation_id (fallback: case title)';
    } elseif ( $record_type === 'assist-org' ) {
        $auto_exclusion_key_label = '_ws_aorg_internal_id (fallback: organization title)';
    }
    $jx_terms = get_terms( [
        'taxonomy'   => WS_JURISDICTION_TAXONOMY,
        'hide_empty' => false,
        'orderby'    => 'slug',
        'order'      => 'ASC',
    ] );

    ?>
    <div class="wrap">
        <h1>WS Prompt Generator</h1>
        <p>Generates AI research prompt templates from live taxonomy data. Output files are written to
           <code><?php echo esc_html( str_replace( ABSPATH, '/', WP_CONTENT_DIR . '/logs/ws-prompts/' ) ); ?></code>
           for FTP retrieval.</p>

        <?php if ( $result ): ?>
            <div class="notice notice-<?php echo $result['success'] ? 'success' : 'error'; ?> is-dismissible">
                <p><?php echo esc_html( $result['message'] ); ?></p>
                <?php if ( $result['success'] ): ?>
                    <p><strong>File:</strong> <code><?php echo esc_html( $result['filename'] ); ?></code></p>
                    <p><strong>Path:</strong> <code><?php echo esc_html( $result['path'] ); ?></code></p>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <form method="post" action="">
            <?php wp_nonce_field( 'ws_generate_prompt', 'ws_prompt_nonce' ); ?>

            <table class="form-table" role="presentation">

                <tr>
                    <th scope="row"><label for="record_type">Record Type</label></th>
                    <td>
                        <select name="record_type" id="record_type" onchange="wsPromptToggleFields()">
                            <option value="statute"        <?php selected( $record_type, 'statute' ); ?>>Statute</option>
                            <option value="common-law"     <?php selected( $record_type, 'common-law' ); ?>>Common Law</option>
                            <option value="citation"       <?php selected( $record_type, 'citation' ); ?>>Citation</option>
                            <option value="interpretation" <?php selected( $record_type, 'interpretation' ); ?>>Interpretation</option>
                            <option value="assist-org"     <?php selected( $record_type, 'assist-org' ); ?>>Assist Org</option>
                        </select>
                        <p class="description">Statute and Common Law produce full research prompts.
                           Citation and Interpretation produce enrichment prompts anchored to existing records.
                           Assist Org produces phased sourcing prompts for fallback layer expansion.</p>
                    </td>
                </tr>

                <tr>
                    <th scope="row"><label for="jx_id">Jurisdiction ID</label></th>
                    <td>
                        <select id="jx_select" class="regular-text" onchange="wsPromptApplyJxFromSelect()">
                            <option value="">Select jurisdiction code...</option>
                            <?php if ( ! is_wp_error( $jx_terms ) ): ?>
                                <?php foreach ( $jx_terms as $term ): ?>
                                    <option value="<?php echo esc_attr( strtoupper( $term->slug ) ); ?>" <?php selected( strtoupper( $posted_jx ), strtoupper( $term->slug ) ); ?>>
                                        <?php echo esc_html( strtoupper( $term->slug ) . ' — ' . $term->name ); ?>
                                    </option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                        <br><br>
                        <input type="text" name="jx_id" id="jx_id"
                               value="<?php echo esc_attr( $posted_jx ); ?>"
                               class="small-text" placeholder="e.g. NJ" maxlength="2" required
                               style="text-transform:uppercase;">
                        <p class="description">Required: two-letter USPS code (or US). Use dropdown or type manually.</p>
                    </td>
                </tr>

                <tr class="ws-field-statute ws-field-common-law">
                    <th scope="row"><label for="records_requested">Records Requested</label></th>
                    <td>
                        <input type="number" name="records_requested" id="records_requested"
                               value="<?php echo esc_attr( $_POST['records_requested'] ?? 0 ); ?>"
                               class="small-text" min="0" max="20" placeholder="0 = no limit">
                        <p class="description">Required. Set to 0 to tell the model: as many as you can confidently verify (fewer is correct).</p>
                    </td>
                </tr>

                <tr class="ws-field-assist-org" style="display:none;">
                    <th scope="row"><label for="proposal_count">Proposal Count</label></th>
                    <td>
                        <input type="number" name="proposal_count" id="proposal_count"
                               value="<?php echo esc_attr( $proposal_count_value ); ?>"
                               class="small-text" min="0" max="20" placeholder="0 = no limit">
                        <p class="description">Set to 0 to tell the model: as many as you can confidently verify (fewer is correct). Recommended focused batch: 6 to 7.</p>
                    </td>
                </tr>

                <tr class="ws-field-assist-org" style="display:none;">
                    <th scope="row"><label for="assist_org_nationwide">Nationwide Only</label></th>
                    <td>
                        <label>
                            <input type="checkbox" name="assist_org_nationwide" id="assist_org_nationwide" value="1" <?php checked( $assist_org_nationwide ); ?>>
                            Restrict to nationwide or clearly multi-state organizations.
                        </label>
                    </td>
                </tr>

                <tr class="ws-field-assist-org" style="display:none;">
                    <th scope="row"><label for="assist_org_focus_notes">Assist-Org Focus Notes</label></th>
                    <td>
                        <textarea name="assist_org_focus_notes" id="assist_org_focus_notes" rows="3" class="large-text"
                                  placeholder="Optional guidance, for example: prioritize worker legal referral pathways over general advocacy pages."><?php echo esc_textarea( $assist_org_focus_notes ); ?></textarea>
                        <p class="description">Optional. Add operator-specific priorities for this sourcing batch.</p>
                    </td>
                </tr>

                <tr class="ws-field-statute ws-field-common-law">
                    <th scope="row"><label for="scope_notes">Scope Notes</label></th>
                    <td>
                        <textarea name="scope_notes" id="scope_notes" rows="3" class="large-text"
                                  placeholder="e.g. Please include CEPA, with citations"><?php echo esc_textarea( $scope_note_value ); ?></textarea>
                        <p class="description">Optional. If blank, defaults to: <?php echo esc_html( $default_scope_note ); ?>.</p>
                    </td>
                </tr>

                <tr class="ws-field-citation ws-field-interpretation" style="display:none;">
                    <th scope="row"><label for="scope_notes_citations">Statutes to Research</label></th>
                    <td>
                        <textarea name="scope_notes" id="scope_notes_citations" rows="5" class="large-text"
                                  placeholder="NJ-34:19-1&#10;NJ-2A:32C-10&#10;NJ-34:11-4.10"><?php echo esc_textarea( $_POST['scope_notes'] ?? '' ); ?></textarea>
                        <p class="description">One statute ID per line. Find citations/interpretations for these records.</p>
                    </td>
                </tr>

                <tr class="ws-field-citation ws-field-interpretation" style="display:none;">
                    <th scope="row"><label for="min_quality">Minimum Quality</label></th>
                    <td>
                        <select name="min_quality" id="min_quality">
                            <option value="low"      <?php selected( $_POST['min_quality'] ?? 'moderate', 'low' ); ?>>Low (include all)</option>
                            <option value="moderate" <?php selected( $_POST['min_quality'] ?? 'moderate', 'moderate' ); ?>>Moderate (appellate+)</option>
                            <option value="high"     <?php selected( $_POST['min_quality'] ?? 'moderate', 'high' ); ?>>High (supreme courts only)</option>
                        </select>
                    </td>
                </tr>

                <tr class="ws-field-interpretation" style="display:none;">
                    <th scope="row"><label for="statute_type">Statute Type</label></th>
                    <td>
                        <select name="statute_type" id="statute_type">
                            <option value="state"   <?php selected( $_POST['statute_type'] ?? 'state', 'state' ); ?>>State statute</option>
                            <option value="federal" <?php selected( $_POST['statute_type'] ?? 'state', 'federal' ); ?>>Federal statute</option>
                        </select>
                        <p class="description">State statutes: state courts only. Federal statutes: federal and state courts.</p>
                    </td>
                </tr>

                <tr>
                    <th scope="row"><label for="disable_exclusion_list">Exclusions</label></th>
                    <td>
                        <label>
                            <input type="checkbox" name="disable_exclusion_list" id="disable_exclusion_list" value="1" <?php checked( $disable_exclusions ); ?>>
                            Disable exclusion list for this prompt generation.
                        </label>
                        <p class="description">When enabled, auto/manual exclusion lists are ignored and no exclusion block is added to the prompt.</p>
                    </td>
                </tr>

                <tr>
                    <th scope="row"><label for="exclusion_list_auto">Auto Exclusions (Drafts)</label></th>
                    <td>
                        <input type="hidden" name="exclusion_list_auto_edited" id="exclusion_list_auto_edited" value="0">
                        <textarea name="exclusion_list_auto" id="exclusion_list_auto" rows="4" class="large-text code"
                                  placeholder="No existing draft exclusions found for this jurisdiction/CPT."><?php echo esc_textarea( $auto_exclusions_text ); ?></textarea>
                        <p class="description">Prefilled from existing records for this jurisdiction + CPT using <code><?php echo esc_html( $auto_exclusion_key_label ); ?></code>. Editable if you want to intentionally regenerate an existing record.</p>
                        <?php if ( ! empty( $missing_statute_hidden_ids ) ): ?>
                            <p class="description" style="color:#c00;">
                                Flag: <?php echo (int) count( $missing_statute_hidden_ids ); ?> statute post(s) are missing <code>_ws_jx_statute_id</code> and are not auto-excluded.
                            </p>
                        <?php endif; ?>
                    </td>
                </tr>

                <tr>
                    <th scope="row"><label for="exclusion_list_manual">Manual Exclusions (Optional)</label></th>
                    <td>
                        <textarea name="exclusion_list_manual" id="exclusion_list_manual" rows="4" class="large-text"
                                  placeholder="One ID or title per line"><?php echo esc_textarea( $manual_exclusions ); ?></textarea>
                        <p class="description">Add any extra IDs. Manual and auto exclusions are merged into the prompt.</p>
                        <p class="description"><strong>Merged exclusions:</strong> <?php echo (int) $merged_count; ?> unique (<?php echo (int) $auto_count; ?> auto + <?php echo (int) $manual_count; ?> manual before dedupe).</p>
                        <?php if ( $disable_exclusions ): ?>
                            <p class="description" style="color:#996800;"><strong>Exclusions are currently disabled for this run.</strong></p>
                        <?php endif; ?>
                    </td>
                </tr>

            </table>

            <p class="submit">
                <input type="submit" name="submit" id="submit" class="button button-primary"
                       value="Generate Prompt">
                  <input type="submit" name="ws_refresh_exclusions" id="ws_refresh_exclusions" class="button"
                      value="Refresh Auto Exclusions"
                      onclick="var edited=document.getElementById('exclusion_list_auto_edited'); if (edited) { edited.value='0'; }">
            </p>

        </form>
    </div>

    <script>
    var wsPromptLastAutoScope = '';

    function wsPromptGetDefaultScopeForJx(jxCode) {
        var jx = (jxCode || '').toUpperCase().trim();
        var jxType = (jx === 'US') ? 'federal' : 'state';
        return jxType + '-level whistleblower laws and protections';
    }

    function wsPromptSyncScopeFromJx() {
        var recordType = document.getElementById('record_type');
        var jxInput = document.getElementById('jx_id');
        var scopeNotes = document.getElementById('scope_notes');

        if (!recordType || !jxInput || !scopeNotes) {
            return;
        }

        var type = (recordType.value || '').toLowerCase();
        if (type !== 'statute' && type !== 'common-law') {
            return;
        }

        var current = (scopeNotes.value || '').trim();
        var nextDefault = wsPromptGetDefaultScopeForJx(jxInput.value);
        var looksLikeDefault = /^(state|federal)-level whistleblower laws and protections$/i.test(current);

        // Only rewrite when field is blank or still on an auto/default value.
        if (current === '' || current === wsPromptLastAutoScope || looksLikeDefault) {
            scopeNotes.value = nextDefault;
            wsPromptLastAutoScope = nextDefault;
        }
    }

    function wsPromptApplyJxFromSelect() {
        var select = document.getElementById('jx_select');
        var input = document.getElementById('jx_id');
        if (!select || !input) {
            return;
        }
        if (select.value) {
            input.value = select.value.toUpperCase();
        }
        wsPromptSyncScopeFromJx();
    }

    function wsPromptToggleFields() {
        var type = document.getElementById('record_type').value;
        var groups = {
            'statute':        ['ws-field-statute'],
            'common-law':     ['ws-field-statute', 'ws-field-common-law'],
            'citation':       ['ws-field-citation'],
            'interpretation': ['ws-field-citation', 'ws-field-interpretation'],
            'assist-org':     ['ws-field-assist-org'],
        };
        var allClasses = ['ws-field-statute', 'ws-field-common-law', 'ws-field-citation', 'ws-field-interpretation', 'ws-field-assist-org'];
        allClasses.forEach(function(cls) {
            document.querySelectorAll('.' + cls).forEach(function(el) {
                el.style.display = 'none';
            });
        });
        (groups[type] || []).forEach(function(cls) {
            document.querySelectorAll('.' + cls).forEach(function(el) {
                el.style.display = '';
            });
        });

        var countInput = document.getElementById('records_requested');
        if (countInput) {
            countInput.required = (type === 'statute' || type === 'common-law');
        }

        var proposalInput = document.getElementById('proposal_count');
        if (proposalInput) {
            proposalInput.required = (type === 'assist-org');
        }

        wsPromptToggleExclusions();
    }

    function wsPromptToggleExclusions() {
        var disable = document.getElementById('disable_exclusion_list');
        var autoField = document.getElementById('exclusion_list_auto');
        var manualField = document.getElementById('exclusion_list_manual');
        var refreshButton = document.getElementById('ws_refresh_exclusions');

        if (!disable) {
            return;
        }

        var blocked = !!disable.checked;
        if (autoField) {
            autoField.disabled = blocked;
        }
        if (manualField) {
            manualField.disabled = blocked;
        }
        if (refreshButton) {
            refreshButton.disabled = blocked;
        }
    }

    document.addEventListener('DOMContentLoaded', wsPromptToggleFields);
    document.addEventListener('DOMContentLoaded', function() {
        var jxInput = document.getElementById('jx_id');
        var scopeNotes = document.getElementById('scope_notes');

        if (scopeNotes) {
            var initial = (scopeNotes.value || '').trim();
            if (/^(state|federal)-level whistleblower laws and protections$/i.test(initial)) {
                wsPromptLastAutoScope = initial;
            }
        }
        if (jxInput) {
            jxInput.addEventListener('change', wsPromptSyncScopeFromJx);
            jxInput.addEventListener('blur', wsPromptSyncScopeFromJx);
        }
        wsPromptSyncScopeFromJx();

        var autoTextarea = document.getElementById('exclusion_list_auto');
        var autoEdited = document.getElementById('exclusion_list_auto_edited');
        var disableExclusions = document.getElementById('disable_exclusion_list');
        if (!autoTextarea || !autoEdited) {
            return;
        }
        autoTextarea.addEventListener('input', function() {
            autoEdited.value = '1';
        });
        if (disableExclusions) {
            disableExclusions.addEventListener('change', wsPromptToggleExclusions);
        }
        wsPromptToggleExclusions();
    });
    </script>
    <?php
}
