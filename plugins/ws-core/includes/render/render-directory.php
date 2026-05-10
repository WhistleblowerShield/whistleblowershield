<?php
/**
 * Assist-organization directory render functions.
 *
 * Renders [ws_assist_org_directory] output in the Assembly Layer.
 * For broader architecture notes, see includes/render/README.md.
 *
 * @package WhistleblowerShield
 * @since   3.6.0
 * @version 3.15.1
 */

defined( 'ABSPATH' ) || exit;

add_action( 'wp', function() {
    if ( ! is_singular( 'ws-assist-org' ) ) {
        return;
    }
    if ( empty( $_GET['ws_from_dir'] ) ) {
        return;
    }
    if ( ! function_exists( 'ws_resolve_filter_context' ) || ! function_exists( 'ws_filter_log_profile_view' ) ) {
        return;
    }

    $org_id = get_queried_object_id();
    if ( ! $org_id ) {
        return;
    }

    $context = ws_resolve_filter_context( false );
    $click_meta = [
        'rank'          => isset( $_GET['ws_rank'] ) ? (int) $_GET['ws_rank'] : 0,
        'rel_score'     => isset( $_GET['ws_rel_score'] ) ? (int) $_GET['ws_rel_score'] : 0,
        'eng_score'     => isset( $_GET['ws_eng_score'] ) ? (int) $_GET['ws_eng_score'] : 0,
        'results_total' => isset( $_GET['ws_results_total'] ) ? (int) $_GET['ws_results_total'] : 0,
        'secure'        => ! empty( $_GET['ws_secure_badge'] ) && $_GET['ws_secure_badge'] === '1',
    ];

    ws_filter_log_profile_view( (int) $org_id, $context, $click_meta );
} );


// ════════════════════════════════════════════════════════════════════════════
// ws_render_directory_page()
// ════════════════════════════════════════════════════════════════════════════

/**
 * Top-level render for the Directory page.
 *
 * Accepts a normalized filter context from ws_resolve_filter_context() and
 * a pre-fetched set of org rows. Splits rows into tiers, scores and sorts
 * each tier, then renders the filter panel + results.
 *
 * Called by [ws_assist_org_directory] shortcode.
 *
 * @param array $targeted   Jurisdiction-scoped org rows (may be empty).
 * @param array $nationwide All published nationwide org rows.
 * @param array $context    Normalized filter context from ws_resolve_filter_context().
 * @return string HTML output.
 */
function ws_render_directory_page( array $targeted, array $nationwide, array $context ): string {
    ob_start();
    ?>
    <div class="ws-directory">

        <?php // ── Filter panel (left or top depending on layout) ─────────── ?>
        <?php echo ws_render_directory_taxonomy_guide( $context ); // phpcs:ignore ?>

        <div class="ws-directory__results">

            <?php // ── Intro ────────────────────────────────────────────────── ?>
            <div class="ws-directory__intro">
                <p>The organizations listed below provide free or low-cost legal
                support, confidential guidance, and advocacy services to
                whistleblowers across the United States. All listings are
                reviewed by our editorial team.</p>
            </div>

            <?php
            // ── Score and sort each tier ──────────────────────────────────────
            $targeted   = ws_filter_sort_orgs( $targeted,   $context, true  );
            $nationwide = ws_filter_sort_orgs( $nationwide, $context, false );

            $targeted_count = count( $targeted );
            $rendered_total = $targeted_count + count( $nationwide );
            ?>

            <?php // ── Targeted results ─────────────────────────────────────── ?>
            <?php if ( ! empty( $targeted ) ) : ?>
                <div class="ws-directory__section ws-directory__section--targeted">
                    <h2 class="ws-directory__section-heading">
                        Organizations in Your Area
                    </h2>
                    <?php echo ws_render_directory_listing( $targeted, $rendered_total ); // phpcs:ignore ?>
                </div>
            <?php endif; ?>

            <?php // ── Zero targeted fallback message ───────────────────────── ?>
            <?php if ( $targeted_count === 0 && $context['has_filters'] ) : ?>
                <?php echo ws_render_directory_fallback_message( $context ); // phpcs:ignore ?>
            <?php endif; ?>

            <?php // ── Nationwide results ───────────────────────────────────── ?>
            <?php if ( ! empty( $nationwide ) ) : ?>
                <div class="ws-directory__section ws-directory__section--nationwide">
                    <?php if ( ! empty( $targeted ) ) : ?>
                        <h2 class="ws-directory__section-heading">
                            Nationwide Organizations
                        </h2>
                    <?php endif; ?>
                    <?php echo ws_render_directory_listing( $nationwide, $rendered_total ); // phpcs:ignore ?>
                </div>
            <?php endif; ?>

            <?php // ── Truly empty (no targeted, no nationwide) ─────────────── ?>
            <?php if ( empty( $targeted ) && empty( $nationwide ) ) : ?>
                <?php echo ws_render_directory_empty(); // phpcs:ignore ?>
            <?php endif; ?>

        </div><?php // .ws-directory__results ?>

    </div><?php // .ws-directory ?>
    <?php
    return ob_get_clean();
}


// ════════════════════════════════════════════════════════════════════════════
// ws_render_directory_taxonomy_guide()
// ════════════════════════════════════════════════════════════════════════════

/**
 * Renders the situation-based filter panel for the directory.
 *
 * Server-rendered GET form. No AJAX required for core functionality.
 * JS may be layered on for progressive enhancement later.
 *
 * Filter axes rendered (all optional — absence = "not sure" = broadest match):
 *   1. Situation stage (ws_case_stage)
 *   2. Concern (ws_protected_disclosure or ws_adverse_action, routed by stage)
 *   3. Employment sector (ws_employment_sector)
 *   4. Disclosure target — optional, shown as "optional refinement"
 *
 * Wide/narrow toggle is always shown when any filter is active.
 *
 * @param array $context Normalized filter context from ws_resolve_filter_context().
 * @return string HTML output.
 */
function ws_render_directory_taxonomy_guide( array $context ): string {
    $request_uri = wp_unslash( $_SERVER['REQUEST_URI'] ?? '/' );
    $path_only   = strtok( (string) $request_uri, '?' );
    if ( ! is_string( $path_only ) || $path_only === '' ) {
        $path_only = '/';
    }
    $current_url = esc_url( $path_only );

    ob_start();
    ?>
    <div class="ws-directory__filter-panel" role="search" aria-label="Filter organizations by your situation">

        <form method="get" action="<?php echo $current_url; ?>" class="ws-filter-form">

            <div class="ws-filter-form__header">
                <h2 class="ws-filter-form__heading">Find Help For Your Situation</h2>
                <p class="ws-filter-form__hint">Answer what applies — leave anything blank if you're not sure.</p>
            </div>

            <?php // ── Stage ────────────────────────────────────────────────── ?>
            <fieldset class="ws-filter-group" id="ws-filter-stage">
                <legend class="ws-filter-group__label">
                    Where are you right now?
                    <span class="ws-filter-group__hint">Choose the closest description.</span>
                </legend>
                <div class="ws-filter-group__options">
                    <?php
                    $stage_options = [
                        'pre-report'         => 'I\'m thinking about reporting something',
                        'post-report'        => 'I\'ve already reported',
                        'retaliation-active' => 'I\'m facing retaliation right now',
                        'litigation'         => 'I\'m in or considering legal action',
                        'research'           => 'I\'m researching, not ready to act',
                    ];
                    foreach ( $stage_options as $slug => $label ) :
                        $checked = ( $context['stage'] === $slug ) ? 'checked' : '';
                        ?>
                        <label class="ws-filter-option">
                            <input type="radio"
                                   name="<?php echo esc_attr( WS_FILTER_PARAM_STAGE ); ?>"
                                   value="<?php echo esc_attr( $slug ); ?>"
                                   <?php echo $checked; ?>>
                            <?php echo esc_html( $label ); ?>
                        </label>
                    <?php endforeach; ?>
                </div>
            </fieldset>

            <?php // ── Concern ──────────────────────────────────────────────── ?>
            <?php
            // Show concern options appropriate to the selected stage.
            // Pre-report / research / null → protected disclosures
            // Retaliation / litigation → adverse action types
            $show_adverse = in_array( $context['stage'], [ 'retaliation-active', 'litigation' ], true );

            $concern_label = $show_adverse
                ? 'What happened to you?'
                : 'What are you concerned about?';

            $concern_hint = $show_adverse
                ? 'Select the action taken against you.'
                : 'Select the type of wrongdoing you witnessed or reported.';

            $concern_options = $show_adverse
                ? ws_filter_get_adverse_action_options()
                : ws_filter_get_protected_disclosure_options();
            ?>
            <fieldset class="ws-filter-group" id="ws-filter-concern">
                <legend class="ws-filter-group__label">
                    <?php echo esc_html( $concern_label ); ?>
                    <span class="ws-filter-group__hint"><?php echo esc_html( $concern_hint ); ?></span>
                </legend>
                <div class="ws-filter-group__options ws-filter-group__options--grid">
                    <?php foreach ( $concern_options as $slug => $label ) :
                        $checked = ( $context['concern'] === $slug ) ? 'checked' : '';
                        ?>
                        <label class="ws-filter-option">
                            <input type="radio"
                                   name="<?php echo esc_attr( WS_FILTER_PARAM_CONCERN ); ?>"
                                   value="<?php echo esc_attr( $slug ); ?>"
                                   <?php echo $checked; ?>>
                            <?php echo esc_html( $label ); ?>
                        </label>
                    <?php endforeach; ?>
                </div>
            </fieldset>

            <?php // ── Sector ───────────────────────────────────────────────── ?>
            <fieldset class="ws-filter-group" id="ws-filter-sector">
                <legend class="ws-filter-group__label">
                    What kind of employer do you work for?
                    <span class="ws-filter-group__hint">This helps match organizations licensed to help in your sector.</span>
                </legend>
                <div class="ws-filter-group__options">
                    <?php
                    $sector_options = [
                        'federal-employee'     => 'Federal government',
                        'state-local-employee' => 'State or local government',
                        'private-sector'       => 'Private company',
                        'nonprofit-ngo'        => 'Nonprofit or NGO',
                        'military-defense'     => 'Military or defense',
                        'all-sectors'          => 'Not sure or other sector',
                                    ];
                    foreach ( $sector_options as $slug => $label ) :
                        $checked = ( $context['sector'] === $slug ) ? 'checked' : '';
                        ?>
                        <label class="ws-filter-option">
                            <input type="radio"
                                   name="<?php echo esc_attr( WS_FILTER_PARAM_SECTOR ); ?>"
                                   value="<?php echo esc_attr( $slug ); ?>"
                                   <?php echo $checked; ?>>
                            <?php echo esc_html( $label ); ?>
                        </label>
                    <?php endforeach; ?>
                </div>
            </fieldset>

            <?php // ── Target (optional refinement) ─────────────────────────── ?>
            <fieldset class="ws-filter-group ws-filter-group--optional" id="ws-filter-target">
                <legend class="ws-filter-group__label">
                    Who did you report to, or plan to report to?
                    <span class="ws-filter-group__hint">Optional — skip if you're not sure yet.</span>
                </legend>
                <div class="ws-filter-group__options ws-filter-group__options--grid">
                    <?php
                    $target_options = [
                        'internal-supervisor' => 'My supervisor or manager',
                        'internal-hr'         => 'Human Resources',
                        'internal-compliance' => 'Compliance or ethics hotline',
                        'internal-management' => 'General management',
                        'agency-federal'      => 'A federal agency',
                        'agency-state'        => 'A state agency',
                        'public-media'        => 'News media or press',
                        'law-enforcement'     => 'Law enforcement',
                    ];
                    foreach ( $target_options as $slug => $label ) :
                        $checked = ( $context['target'] === $slug ) ? 'checked' : '';
                        ?>
                        <label class="ws-filter-option">
                            <input type="radio"
                                   name="<?php echo esc_attr( WS_FILTER_PARAM_TARGET ); ?>"
                                   value="<?php echo esc_attr( $slug ); ?>"
                                   <?php echo $checked; ?>>
                            <?php echo esc_html( $label ); ?>
                        </label>
                    <?php endforeach; ?>
                </div>
            </fieldset>

            <?php // ── Submit + Clear ────────────────────────────────────────── ?>
            <div class="ws-filter-form__actions">
                <button type="submit" class="ws-btn ws-btn--primary">
                    Find Organizations
                </button>
                <?php if ( $context['has_filters'] ) : ?>
                    <a href="<?php echo $current_url; ?>"
                       class="ws-btn ws-btn--ghost ws-filter-form__clear"
                       aria-label="Clear all filters and show all organizations">
                        Show All
                    </a>
                <?php endif; ?>
            </div>

            <?php // ── Wide/Narrow toggle ───────────────────────────────────── ?>
            <?php if ( $context['has_filters'] ) : ?>
                <div class="ws-filter-form__breadth-controls" role="group" aria-label="Adjust search breadth">
                    <p class="ws-filter-form__breadth-hint">
                        Not finding what you need?
                    </p>
                    <a href="<?php echo esc_url( ws_filter_broaden_url( $context ) ); ?>"
                       class="ws-btn ws-btn--outline ws-btn--sm">
                        Widen My Search
                        <span class="ws-filter-form__breadth-tip">Remove one filter to see more options</span>
                    </a>
                    <?php if ( ws_filter_can_narrow( $context ) ) : ?>
                        <span class="ws-filter-form__breadth-tip">
                            Add more details above to narrow results
                        </span>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

        </form>

    </div><?php // .ws-directory__filter-panel ?>
    <?php
    return ob_get_clean();
}


// ════════════════════════════════════════════════════════════════════════════
// ws_filter_sort_orgs()
// ════════════════════════════════════════════════════════════════════════════

/**
 * Scores and sorts an array of org rows against the active filter context.
 *
 * @param array $orgs     Org rows from query layer.
 * @param array $context  Normalized filter context.
 * @param bool  $targeted Whether these are jurisdiction-scoped orgs.
 * @return array Sorted org rows, highest score first.
 */
function ws_filter_sort_orgs( array $orgs, array $context, bool $targeted ): array {
    if ( empty( $orgs ) ) {
        return [];
    }

    // Score each org
    $scored = [];
    foreach ( $orgs as $org ) {
        $rel_score = ws_filter_score_org( $org, $context, $targeted );
        $eng_score = ws_filter_score_engagement( $org );

        $org['_rel_score'] = $rel_score;
        $org['_eng_score'] = $eng_score;

        $scored[] = [
            'org'   => $org,
            'score' => $rel_score + $eng_score,
        ];
    }

    // Sort by score descending, then by org title ascending as tiebreaker
    usort( $scored, function( $a, $b ) {
        if ( $b['score'] !== $a['score'] ) {
            return $b['score'] - $a['score'];
        }
        return strcmp(
            $a['org']['title'] ?? '',
            $b['org']['title'] ?? ''
        );
    } );

    return array_column( $scored, 'org' );
}


// ════════════════════════════════════════════════════════════════════════════
// ws_render_directory_fallback_message()
// ════════════════════════════════════════════════════════════════════════════

/**
 * Renders the zero-targeted-result fallback message.
 *
 * Only shown when targeted_count === 0 and filters are active.
 * Nationwide orgs still render below this message.
 *
 * @param array $context Normalized filter context.
 * @return string HTML output.
 */
function ws_render_directory_fallback_message( array $context ): string {
    $copy    = ws_filter_fallback_copy();
    $variant = $copy['default'] ?? 'compassionate';
    $message = $copy[ $variant ] ?? ( $copy['compassionate'] ?? '' );

    ob_start();
    ?>
    <div class="ws-directory__fallback" role="status" aria-live="polite">
        <p class="ws-directory__fallback-message"><?php echo esc_html( $message ); ?></p>
    </div>
    <?php
    return ob_get_clean();
}


// ════════════════════════════════════════════════════════════════════════════
// ws_filter_broaden_url()
// ════════════════════════════════════════════════════════════════════════════

/**
 * Returns a URL with the least-significant active filter removed.
 *
 * Removal priority (remove least specific first):
 *   target → concern → sector → stage
 *
 * @param array $context Normalized filter context.
 * @return string URL with one filter removed.
 */
function ws_filter_broaden_url( array $context ): string {
    $remove_order = [
        WS_FILTER_PARAM_TARGET,
        WS_FILTER_PARAM_CONCERN,
        WS_FILTER_PARAM_SECTOR,
        WS_FILTER_PARAM_STAGE,
    ];

    foreach ( $remove_order as $param ) {
        $context_key = str_replace( 'ws_', '', $param );
        if ( array_key_exists( $context_key, $context ) && $context[ $context_key ] !== null ) {
            return esc_url( remove_query_arg( $param ) );
        }
    }

    // All filters already absent — return clean URL
    $request_uri = wp_unslash( $_SERVER['REQUEST_URI'] ?? '/' );
    $path_only   = strtok( (string) $request_uri, '?' );
    if ( ! is_string( $path_only ) || $path_only === '' ) {
        $path_only = '/';
    }
    return esc_url( $path_only );
}


// ════════════════════════════════════════════════════════════════════════════
// ws_filter_can_narrow()
// ════════════════════════════════════════════════════════════════════════════

/**
 * Returns true if there are filter axes the user hasn't set yet.
 *
 * Used to decide whether to show the "add more details to narrow" hint.
 *
 * @param array $context Normalized filter context.
 * @return bool
 */
function ws_filter_can_narrow( array $context ): bool {
    // Only show the "add more details" hint when at least one filter is
    // already active AND at least one axis is still unset. On a fresh
    // unfiltered page there is nothing to narrow.
    if ( ! $context['has_filters'] ) {
        return false;
    }
    return ( $context['stage']   === null
          || $context['concern'] === null
          || $context['sector']  === null );
}


// ════════════════════════════════════════════════════════════════════════════
// Concern option helpers
// ════════════════════════════════════════════════════════════════════════════

/**
 * Returns plain-english protected disclosure options for the concern fieldset.
 *
 * @return array slug => label
 */
function ws_filter_get_protected_disclosure_options(): array {
    return [
        'general-wrongdoing'            => 'General wrongdoing or law violation',
        'public-corruption-ethics'      => 'Government corruption or ethics violation',
        'procurement-spending-fraud'    => 'Government spending or contracting fraud',
        'healthcare-medicare-fraud'     => 'Healthcare or Medicare fraud',
        'securities-commodities-fraud'  => 'Securities or financial fraud',
        'environmental-protection'      => 'Environmental harm',
        'occupational-health-safety'    => 'Workplace safety violation',
        'wage-hour-violations'          => 'Wage theft or unpaid overtime',
        'tax-evasion-fraud'             => 'Tax fraud or evasion',
        'consumer-data-protection'      => 'Data privacy or consumer protection',
        'cybersecurity-disclosure'      => 'Cybersecurity threat',
        'food-drug-safety'              => 'Food or drug safety',
        'military-defense-reporting'    => 'Military or defense misconduct',
        'election-integrity'            => 'Election integrity',
        'classified-information'        => 'Classified information misuse',
    ];
}

/**
 * Returns plain-english adverse action options for the concern fieldset.
 *
 * @return array slug => label
 */
function ws_filter_get_adverse_action_options(): array {
    return [
        'termination'            => 'Fired or laid off',
        'constructive-discharge' => 'Forced to resign',
        'demotion'               => 'Demoted',
        'suspension'             => 'Suspended',
        'harassment'             => 'Harassment or hostile environment',
        'pay-reduction'          => 'Pay cut or benefits reduced',
        'disciplinary-action'    => 'Written up or disciplined',
        'transfer'               => 'Transferred against my will',
        'blacklisting'           => 'Blacklisted or reference sabotage',
        'contract-non-renewal'   => 'Contract not renewed',
        'security-clearance-action' => 'Security clearance threatened',
        'immigration-threat'     => 'Immigration status threatened',
        'privilege-revocation'   => 'Access or privileges revoked',
    ];
}


// ════════════════════════════════════════════════════════════════════════════
// ws_render_directory_listing()
// ════════════════════════════════════════════════════════════════════════════

/**
 * Renders a directory grid from normalized org rows.
 *
 * @param array<int,array<string,mixed>> $items         Org rows from query layer.
 * @param int                            $results_total Total rendered results count across the page.
 * @return string HTML output.
 */
function ws_render_directory_listing( $items, int $results_total = 0 ) {
    ob_start();
    $results_total = $results_total > 0 ? $results_total : count( $items );
    ?>
    <div class="ws-directory__grid" role="list">
        <?php foreach ( $items as $position => $org ) : ?>
            <?php echo ws_render_directory_card( $org, (int) $position + 1, $results_total ); // phpcs:ignore ?>
        <?php endforeach; ?>
    </div>
    <?php
    return ob_get_clean();
}


// ════════════════════════════════════════════════════════════════════════════
// ws_render_directory_card()  — unchanged from v3.7.1
// ════════════════════════════════════════════════════════════════════════════

/**
 * Renders a single assist-organization card.
 *
 * @param array<string,mixed> $org Normalized org row.
 * @param int                 $position      1-based rank position in rendered list.
 * @param int                 $results_total Total rendered results count across the page.
 * @return string             HTML output.
 */
function ws_render_directory_card( array $org, int $position = 0, int $results_total = 0 ) {

    $cost_labels = [
        'free'            => 'Free',
        'pro-bono'        => 'Pro Bono',
        'sliding-scale'   => 'Sliding Scale',
        'contingency'     => 'Contingency Fee',
        'fee-for-service' => 'Fee for Service',
        'unclear'         => 'Unclear',
    ];

    $type_slug = (string) ( $org['type'] ?? '' );
    $type_name = (string) ( $org['type_label'] ?? '' );

    $cost_slug  = is_array( $org['cost_model'] ) ? ( $org['cost_model'][0] ?? '' ) : '';
    $cost_label = ( $cost_slug === 'unclear' ) ? '' : ( $cost_labels[ $cost_slug ] ?? '' );
    $secure_channel_status = (string) ( $org['secure_channel_status'] ?? '' );
    $has_secure_channel = ! in_array( $secure_channel_status, [ '', 'none-found', 'unclear' ], true );
    $secure_contact_tools = is_array( $org['secure_contact_tools'] ?? null ) ? $org['secure_contact_tools'] : [];
    $phones = is_array( $org['phones'] ?? null ) ? $org['phones'] : [];
    $emails = is_array( $org['emails'] ?? null ) ? $org['emails'] : [];

    $card_phone = '';
    foreach ( $phones as $row ) {
        $t = strtolower( trim( (string) ( $row['type'] ?? '' ) ) );
        $v = trim( (string) ( $row['value'] ?? '' ) );
        if ( $v === '' ) {
            continue;
        }
        if ( in_array( $t, [ 'hotline', 'intake' ], true ) ) {
            $card_phone = $v;
            break;
        }
    }

    $email_priority = [ 'intake', 'legal' ];
    $card_emails = [];
    foreach ( $emails as $row ) {
        $t = strtolower( trim( (string) ( $row['type'] ?? '' ) ) );
        $v = sanitize_email( (string) ( $row['value'] ?? '' ) );
        if ( $v === '' || ! in_array( $t, $email_priority, true ) ) {
            continue;
        }
        if ( ! in_array( $v, $card_emails, true ) ) {
            $card_emails[] = $v;
        }
    }

    $services = ( is_array( $org['services'] ) && ! is_wp_error( $org['services'] ) )
        ? array_values( array_filter( $org['services'] ) )
        : [];

    $anchor_id = ! empty( $org['internal_id'] )
        ? 'aorg-' . sanitize_html_class( $org['internal_id'] )
        : 'aorg-' . absint( $org['id'] );

    $services_html = '';
    if ( ! empty( $services ) ) {
        $count = count( $services );
        if ( $count === 1 ) {
            $services_html = esc_html( $services[0] );
        } elseif ( $count === 2 ) {
            $services_html = esc_html( $services[0] ) . ' &amp; ' . esc_html( $services[1] );
        } else {
            $parts = array_map( 'esc_html', array_slice( $services, 0, -1 ) );
            $last  = esc_html( end( $services ) );
            $services_html = implode( ', ', $parts ) . ' &amp; ' . $last;
        }
    }

    ob_start();
    ?>
    <div class="ws-aorg-card" id="<?php echo esc_attr( $anchor_id ); ?>"
         role="listitem"
         data-type="<?php echo esc_attr( $type_slug ); ?>"
         data-cost="<?php echo esc_attr( $cost_slug ); ?>">

        <div class="ws-aorg-card__header">
            <h2 class="ws-aorg-card__name"><?php echo esc_html( $org['title'] ); ?></h2>
            <div class="ws-aorg-card__badges" aria-label="<?php echo esc_attr( $org['title'] ); ?> details">
                <?php if ( $type_name ) : ?>
                    <span class="ws-aorg-card__badge ws-aorg-card__badge--type"
                          data-slug="<?php echo esc_attr( $type_slug ); ?>">
                        <?php echo esc_html( $type_name ); ?>
                    </span>
                <?php endif; ?>
                <?php if ( $cost_label ) : ?>
                    <span class="ws-aorg-card__badge ws-aorg-card__badge--cost">
                        <?php echo esc_html( $cost_label ); ?>
                    </span>
                <?php endif; ?>
                <?php if ( ( $org['has_attorneys'] ?? '' ) === 'yes' ) : ?>
                    <span class="ws-aorg-card__badge ws-aorg-card__badge--attorneys">
                        Licensed Attorneys
                    </span>
                <?php endif; ?>
                <?php if ( ( $org['anonymous_pre_consult_status'] ?? '' ) === 'yes' ) : ?>
                    <span class="ws-aorg-card__badge ws-aorg-card__badge--anon">
                        Accepts Anonymous
                    </span>
                <?php endif; ?>
                <?php if ( $has_secure_channel ) : ?>
                    <span class="ws-aorg-card__badge ws-aorg-card__badge--secure">
                        Secure Channel
                    </span>
                <?php endif; ?>
            </div>
        </div>

        <?php if ( ! empty( $org['description'] ) ) : ?>
            <div class="ws-aorg-card__description">
                <?php echo wp_kses_post( wpautop( $org['description'] ) ); ?>
            </div>
        <?php endif; ?>

        <?php if ( $services_html ) : ?>
            <div class="ws-aorg-card__services">
                <strong>Services Provided:</strong> <?php echo $services_html; ?>
            </div>
        <?php endif; ?>

        <?php if ( $card_phone !== '' || ! empty( $card_emails ) ) : ?>
            <div class="ws-aorg-card__contact">
                <?php if ( $card_phone !== '' ) : ?>
                    <span class="ws-aorg-card__phone">
                        <a href="tel:<?php echo esc_attr( preg_replace( '/[^0-9+]/', '', $card_phone ) ); ?>"
                           aria-label="Call <?php echo esc_attr( $org['title'] ); ?>: <?php echo esc_attr( $card_phone ); ?>">
                            <?php echo esc_html( $card_phone ); ?>
                        </a>
                    </span>
                <?php endif; ?>
                <?php foreach ( $card_emails as $card_email ) : ?>
                    <span class="ws-aorg-card__email">
                        <a href="mailto:<?php echo esc_attr( $card_email ); ?>"
                           aria-label="Email <?php echo esc_attr( $org['title'] ); ?>">
                            <?php echo esc_html( $card_email ); ?>
                        </a>
                    </span>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <div class="ws-aorg-card__actions">
            <?php if ( ! empty( $org['official_homepage_url'] ) ) : ?>
                <a href="<?php echo esc_url( $org['official_homepage_url'] ); ?>"
                   class="ws-btn ws-btn--secondary ws-btn--sm"
                   target="_blank"
                   rel="noopener noreferrer"
                   aria-label="Visit the <?php echo esc_attr( $org['title'] ); ?> website (opens in new tab)">
                    Visit Website
                    <span class="screen-reader-text">(opens in new tab)</span>
                </a>
            <?php endif; ?>
            <?php if ( ! empty( $org['contact_url'] ) ) : ?>
                <a href="<?php echo esc_url( $org['contact_url'] ); ?>"
                   class="ws-btn ws-btn--contact ws-btn--sm"
                   target="_blank"
                   rel="noopener noreferrer"
                   aria-label="Open contact page for <?php echo esc_attr( $org['title'] ); ?> (opens in new tab)">
                    Contact
                    <span class="screen-reader-text">(opens in new tab)</span>
                </a>
            <?php endif; ?>
            <?php if ( ! empty( $org['intake_url'] ) ) : ?>
                <a href="<?php echo esc_url( $org['intake_url'] ); ?>"
                   class="ws-btn ws-btn--primary ws-btn--sm"
                   target="_blank"
                   rel="noopener noreferrer"
                   aria-label="Start intake with <?php echo esc_attr( $org['title'] ); ?> (opens in new tab)">
                    Start Intake
                    <span class="screen-reader-text">(opens in new tab)</span>
                </a>
            <?php endif; ?>
            <?php if ( ! empty( $org['has_extended_profile'] ) && ! empty( $org['url'] ) ) : ?>
                <a href="<?php echo esc_url( add_query_arg( [
                    'ws_from_dir'  => '1',
                    'ws_rank'      => $position,
                    'ws_rel_score' => (int) ( $org['_rel_score'] ?? 0 ),
                    'ws_eng_score' => (int) ( $org['_eng_score'] ?? 0 ),
                    'ws_results_total' => max( 0, $results_total ),
                    'ws_secure_badge'  => $has_secure_channel ? '1' : '0',
                ], $org['url'] ) ); ?>"
                   class="ws-aorg-card__more-link"
                   aria-label="More about <?php echo esc_attr( $org['title'] ); ?>">
                    More about this organization
                </a>
            <?php endif; ?>
        </div>

    </div>
    <?php
    return ob_get_clean();
}


// ════════════════════════════════════════════════════════════════════════════
// ws_render_directory_empty()
// ════════════════════════════════════════════════════════════════════════════

/**
 * Renders the directory empty-state message.
 *
 * @return string HTML output.
 */
function ws_render_directory_empty() {
    ob_start();
    ?>
    <div class="ws-directory__empty" role="status" aria-live="polite">
        <p>No organizations are available right now. Please check back soon.</p>
    </div>
    <?php
    return ob_get_clean();
}
