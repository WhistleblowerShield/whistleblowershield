<?php
/**
 * ws-fail-loud.php
 *
 * WhistleblowerShield Core Plugin — Unified Loud Failure System
 *
 * PURPOSE
 * -------
 * ONE mechanism, plugin-wide, for "something is wrong and a human needs
 * to know." Before this file, the plugin had at least four different
 * shapes of failure signal, none of which talked to each other:
 *
 *   1. admin-health-check.php — collects an $issues array, renders its
 *      own admin_notices banner.
 *   2. admin-url-monitor.php — try/catch/finally, error_log() on
 *      WP_DEBUG, its OWN separate admin_notices banner, its own email
 *      digest.
 *   3. tool-ingest.php (and likely other admin tools) — structured
 *      $result['errors'] arrays returned to the caller, checked
 *      case-by-case at each call site.
 *   4. The prompt generator (pg-*, 3.22.0-rewrite) — thrown PHP
 *      exceptions, caught at the pg-ui.php boundary.
 *
 * This file does not replace any of those outright — I haven't read
 * admin-health-check.php or admin-url-monitor.php in full, and rewriting
 * them without having read them would be exactly the kind of guess this
 * project's editorial principles exist to prevent, just applied to code.
 * What this file does is give every one of those four patterns — and
 * every future one — a single thing to route through, so the *next*
 * subsystem that needs to fail loudly doesn't invent a fifth shape.
 *
 * TWO PRIMITIVES
 * ---------------
 * ws_fail_loud( $context, $message, $data = [] )
 *   For internal / admin / data-layer code. ALWAYS throws WS_Loud_Failure.
 *   No return value, because it never returns. Use this anywhere a
 *   function currently does — or should do — one of:
 *     - `return false;` / `return null;` / `return [];` on a real error
 *     - a silent `default:` / bare `else` that produces a generic value
 *     - an inline error string embedded in otherwise-valid output
 *   All of those are "quietly wrong." ws_fail_loud() is loudly wrong,
 *   which is the point.
 *
 * ws_render_or_fail_loud( callable $render_fn, string $context )
 *   For PUBLIC-FACING render code — jurisdiction pages, shortcodes,
 *   anything a visitor (including someone in crisis) might see. A raw
 *   uncaught exception here would produce a fatal white screen for that
 *   visitor, which is unacceptable on a public-interest safety site no
 *   matter how philosophically correct "loud failure" is. This wrapper
 *   catches WS_Loud_Failure (and any other Throwable) from the render
 *   callable, logs it exactly as loudly as ws_fail_loud() would, flags
 *   it for the admin, and returns a calm, minimal "this section is
 *   temporarily unavailable" string instead of crashing the page. Loud
 *   to you. Quiet and honest to the visitor — never blank, never wrong
 *   content, never a stack trace.
 *
 * WHERE FAILURES GO
 * -------------------
 * 1. wp-content/logs/ws-core-error.log — the log path already referenced
 *    in the pre-launch checklist and expected to exist. This file does
 *    not invent a new log location; it writes to the one already
 *    designated for this purpose.
 * 2. A rolling option (`ws_loud_failure_log`, capped at 100 entries) —
 *    same storage pattern admin-url-monitor.php already uses for its own
 *    log, so this isn't introducing a new persistence idiom either.
 * 3. One admin_notices banner, added by THIS file. It does not replace
 *    admin-health-check.php's or admin-url-monitor.php's existing
 *    banners — folding those in is a follow-up pass against their real
 *    content, not a guess made here. Until that pass happens, there are
 *    three banners instead of one. That's an honest intermediate state,
 *    not a finished one.
 *
 * @package    WhistleblowerShield
 * @since      3.23.0
 * @version    3.23.1
 * @author     WhistleblowerShield (Dejunai)
 * @link       https://whistleblowershield.org
 * @copyright  Copyright (c) Whistleblower Shield
 *
 * VERSION LOG
 * -----------
 * 3.23.1  Stage 0 (wired into loader.php, first file in the Admin Layer)
 *         and Stage 1 (admin-health-check.php, admin-url-monitor.php,
 *         admin-feed-monitor.php now also route their existing failure
 *         patterns through ws_log_loud_failure()) complete. tool-ingest.php
 *         and every query/render-layer file remain unconverted — see the
 *         rollout plan in README.fail-loud.md for current state.
 * 3.23.0  Initial release. Architecture and code by Claude (Anthropic),
 *         directed and reviewed by Dejunai. NOT yet wired into
 *         admin-health-check.php, admin-url-monitor.php, tool-ingest.php,
 *         or any query/render-layer file — see the rollout plan in
 *         README.fail-loud.md for the honest next-steps list.
 */

defined( 'ABSPATH' ) || exit;

/**
 * The single exception class every loud failure in this plugin should
 * throw. Carries a $context string (which subsystem, e.g.
 * "prompt-generator", "query-jurisdiction", "ingest") and an optional
 * $data array for structured debugging information that doesn't belong
 * in a human-readable message.
 */
class WS_Loud_Failure extends \RuntimeException {

    /** @var string Which subsystem raised this — short, stable, greppable. */
    public string $context;

    /** @var array<string,mixed> Structured debugging data, logged but not shown to the message. */
    public array $data;

    public function __construct( string $context, string $message, array $data = [] ) {
        $this->context = $context;
        $this->data    = $data;
        parent::__construct( $message );
    }
}

/**
 * Throw a loud failure. This function never returns — it always throws.
 * No default, no fallback value, no way to call this and get anything
 * back except an exception.
 *
 * @param string              $context Short, stable subsystem identifier — e.g. 'prompt-generator', 'ingest', 'query-jurisdiction'. Used for filtering the log; keep it consistent per subsystem, don't invent a new string per call site.
 * @param string              $message Human-readable explanation. Should say what was expected, what was found, and why no default was used — the same standard the prompt-generator rewrite's exception messages already follow.
 * @param array<string,mixed> $data    Optional structured data for the log (post ID, field name, raw value, etc.) — not included in $message, so the log entry can carry detail without cluttering an admin-notice display.
 *
 * @throws WS_Loud_Failure Always.
 */
function ws_fail_loud( string $context, string $message, array $data = [] ) {
    $e = new WS_Loud_Failure( $context, $message, $data );
    ws_log_loud_failure( $e );
    throw $e;
}

/**
 * Runs $render_fn. If it completes normally, returns its return value
 * unchanged. If it throws anything — a WS_Loud_Failure from ws_fail_loud(),
 * or any other Throwable that escaped an unrelated bug — logs it exactly
 * as loudly as ws_fail_loud() would, then returns a calm fallback string
 * instead of letting the exception reach the visitor.
 *
 * Use this at render/shortcode/template boundaries — anywhere a visitor,
 * not an admin, is the audience. Do NOT use this to wrap admin-tool or
 * data-layer code; those should let ws_fail_loud() propagate and be
 * caught at the admin UI boundary instead (see pg-ui.php's
 * ws_handle_prompt_generation() for the reference pattern).
 *
 * @param callable $render_fn Zero-argument callable that returns a string.
 * @param string   $context   Same meaning as ws_fail_loud()'s $context param.
 * @return string
 */
function ws_render_or_fail_loud( callable $render_fn, string $context ): string {
    try {
        return (string) call_user_func( $render_fn );
    } catch ( \Throwable $e ) {
        if ( ! ( $e instanceof WS_Loud_Failure ) ) {
            // Something threw that wasn't already routed through
            // ws_fail_loud() — still log it the same way, so nothing
            // that reaches this wrapper goes unrecorded, but don't
            // pretend it came with the structured $context/$data a real
            // WS_Loud_Failure would carry.
            ws_log_loud_failure( new WS_Loud_Failure( $context, $e->getMessage(), [
                'original_exception_class' => get_class( $e ),
            ] ) );
        }

        return ws_render_visitor_safe_failure_notice( $context );
    }
}

/**
 * The calm, non-alarming string a VISITOR sees when a render call
 * failed. Deliberately generic — it never echoes $message or $data,
 * since those are diagnostic detail for you, not something to show a
 * stranger reading about their own retaliation case. Styling is
 * intentionally minimal inline CSS so this renders sanely even if
 * enqueued stylesheets didn't load for whatever unrelated reason
 * triggered the failure.
 */
function ws_render_visitor_safe_failure_notice( string $context ): string {
    return '<div class="ws-section-unavailable" style="padding:1rem;border:1px solid #ccc;border-radius:4px;color:#555;font-style:italic;">'
        . 'This section is temporarily unavailable. It has been logged and flagged for review.'
        . '</div>';
}

/**
 * Writes one loud failure to the error log and the rolling admin-visible
 * log. This is the ONE place a loud failure gets recorded — both
 * ws_fail_loud() and ws_render_or_fail_loud() route through it, so
 * there's exactly one log format to read, not one per subsystem.
 */
function ws_log_loud_failure( WS_Loud_Failure $e ): void {
    $entry = [
        'time'    => current_time( 'Y-m-d H:i:s' ),
        'context' => $e->context,
        'message' => $e->getMessage(),
        'data'    => $e->data,
        'file'    => $e->getFile(),
        'line'    => $e->getLine(),
    ];

    // 1. wp-content/logs/ws-core-error.log — the log path already
    // designated for this purpose per the pre-launch checklist. This
    // function does not create the directory or file; per that same
    // checklist, PHP will not create it — it must already exist and be
    // writable. If the write fails, it fails silently at this layer
    // (there is nowhere louder to escalate a logging failure to without
    // risking an infinite loop), but the rolling option below is a
    // second, independent record of the same failure.
    $log_line = sprintf(
        "[%s] [%s] %s | data=%s | %s:%d\n",
        $entry['time'],
        $entry['context'],
        $entry['message'],
        wp_json_encode( $entry['data'] ),
        $entry['file'],
        $entry['line']
    );
    @error_log( $log_line, 3, WP_CONTENT_DIR . '/logs/ws-core-error.log' );

    // 2. Rolling admin-visible log, same option-based pattern
    // admin-url-monitor.php already uses. Capped at 100 entries so this
    // can't grow unbounded on a bad day.
    $log = get_option( 'ws_loud_failure_log', [] );
    if ( ! is_array( $log ) ) {
        $log = [];
    }
    $log[] = $entry;
    if ( count( $log ) > 100 ) {
        $log = array_slice( $log, -100 );
    }
    update_option( 'ws_loud_failure_log', $log );
}

// ════════════════════════════════════════════════════════════════════════════
// Admin Notice
//
// One consolidated banner for everything routed through ws_fail_loud()
// or ws_render_or_fail_loud(). Deliberately separate from
// admin-health-check.php's and admin-url-monitor.php's existing banners
// for now — see the file header and README.fail-loud.md for why those
// aren't folded in yet.
// ════════════════════════════════════════════════════════════════════════════

add_action( 'admin_notices', 'ws_loud_failure_admin_notice' );

function ws_loud_failure_admin_notice(): void {
    if ( ! current_user_can( 'manage_options' ) ) {
        return;
    }

    $log = get_option( 'ws_loud_failure_log', [] );
    if ( empty( $log ) || ! is_array( $log ) ) {
        return;
    }

    $recent = array_slice( $log, -5 );
    $count  = count( $log );

    echo '<div class="notice notice-error is-dismissible"><p><strong>WhistleblowerShield — Loud Failures:</strong> ';
    echo esc_html( $count === 1 ? '1 recorded failure.' : "{$count} recorded failures." );
    echo ' Most recent:</p><ul style="margin:0 0 .5em 1.5em;list-style:disc;">';
    foreach ( array_reverse( $recent ) as $entry ) {
        printf(
            '<li><code>%s</code> — %s <span style="color:#999;">(%s)</span></li>',
            esc_html( $entry['context'] ?? 'unknown' ),
            esc_html( $entry['message'] ?? '' ),
            esc_html( $entry['time'] ?? '' )
        );
    }
    echo '</ul><p><a href="' . esc_url( admin_url( 'admin.php?page=ws-loud-failures' ) ) . '">View full log &rarr;</a> — clearing requires visiting that page (not built in this pass; the option can be cleared manually via <code>delete_option(\'ws_loud_failure_log\')</code> until then).</p></div>';
}
