<?php
/**
 * ws-statute-bold.php — Statute citation bold scanner.
 *
 * PURPOSE
 * -------
 * Registers the ws_statute_bold_scan filter. Wraps statute citations in
 * <strong> tags to give scanning readers a visual anchor. Two patterns:
 *
 *   Named cite:  jx_name + up to 40 chars + §{1,2} + section number
 *                e.g. "California Labor Code § 1102.5"
 *
 *   Bare cite:   §{1,2} + section number, no preceding code name
 *                e.g. "§ 1102.5" or "§§ 1981-1983"
 *
 * Runs as a straight preg_replace on the HTML string — no DOM walking.
 * Statute citations are unlikely to appear in href or data-tooltip
 * attributes, making DOM traversal unnecessary overhead here.
 *
 * INTEGRATION
 * -----------
 * Shortcodes opt in by applying the filter to their rendered HTML:
 *
 *   $html = apply_filters( 'ws_statute_bold_scan', $html, $jx_name );
 *
 * $jx_name should be the full jurisdiction name (e.g. "California").
 * Pass an empty string for contexts where no jurisdiction name is available
 * — bare cite pattern still runs.
 *
 * MATCHING RULES
 * --------------
 * - Named cites: first occurrence per unique full citation string only.
 *   Subsequent identical citations are not re-bolded.
 * - Bare cites: all occurrences are bolded — context-free by definition.
 *   Bare pattern runs only on HTML segments outside existing <strong> blocks
 *   to prevent double-wrapping citations already caught by the named pass.
 * - Trailing periods excluded from matches (sentence punctuation).
 * - Headings (h1-h3): not explicitly skipped. Revisit if bold in headings
 *   looks heavy in practice.
 *
 * SECTION NUMBER FORMAT
 * ---------------------
 * Matches digits followed by any combination of: digits, periods, hyphens,
 * parentheses, and word characters. Covers:
 *   § 1102.5       § 1102.5(b)     § 1102.5(b)(1)
 *   §§ 1981-1983   § 12940         § 2611
 * Trailing period excluded via negative lookbehind.
 *
 * @package    WhistleblowerShield
 * @since      3.10.1
 * @version    3.20.1
 *
 * VERSION LOG
 * -----------
 * 3.20.1  Loud-failure pass. All three preg_* calls (preg_replace_callback
 *         x2, preg_split) had unchecked failure returns. The worst of the
 *         three: a null from the bare-citation preg_replace_callback() was
 *         concatenated directly into $result, silently deleting that text
 *         segment from visitor-facing output — not a cosmetic issue, actual
 *         content loss. All three now fail open (keep original text/HTML)
 *         and log via ws_log_loud_failure() instead.
 */

defined( 'ABSPATH' ) || exit;


add_filter( 'ws_statute_bold_scan', 'ws_apply_statute_bold', 10, 2 );

/**
 * Wraps statute citations in <strong> tags.
 *
 * @param  string $html     HTML string from a shortcode render.
 * @param  string $jx_name  Full jurisdiction name (e.g. "California").
 * @return string           HTML with statute citations bolded.
 */
function ws_apply_statute_bold( $html, $jx_name = '' ) {

    if ( empty( $html ) || ! is_string( $html ) ) {
        return $html;
    }

    // ── Pattern 1: Named cite ─────────────────────────────────────────────
    //
    // Anchors on jx_name, allows up to 40 characters of code name text
    // between the jurisdiction name and the § symbol, then captures the
    // section number. First occurrence of each unique citation string only
    // — $matched_named guards against re-bolding the same cite twice when
    // e.g. "California Labor Code § 1102.5" appears more than once.

    if ( ! empty( $jx_name ) ) {

        $pattern_named = '/('
            . preg_quote( $jx_name, '/' )
            . '[^§]{1,40}'
            . '§{1,2}\s*'
            . '[\d]+[\d.\-()\w]*'
            . '(?<![.]))/u';

        $matched_named = [];

        $named_result = preg_replace_callback(
            $pattern_named,
            function( $m ) use ( &$matched_named ) {
                $cite = $m[1];
                $key  = strtolower( $cite );
                if ( isset( $matched_named[ $key ] ) ) {
                    return $cite;
                }
                $matched_named[ $key ] = true;
                return '<strong>' . $cite . '</strong>';
            },
            $html
        );

        // preg_replace_callback() returns null on a PCRE engine failure
        // (e.g. pcre.backtrack_limit exceeded on unusually long content).
        // Previously assigned straight back to $html — a null there would
        // have silently wiped the entire shortcode's rendered output for a
        // real visitor. Fail open to the original, unbolded HTML instead;
        // losing the bold styling on statute citations is a cosmetic
        // degrade, not a broken page.
        if ( $named_result === null ) {
            ws_log_loud_failure( new WS_Loud_Failure( 'statute-bold', 'preg_replace_callback() failed on the named-citation pattern — returning unbolded HTML for this render.', [
                'jx_name'     => $jx_name,
                'pcre_error'  => preg_last_error_msg(),
            ] ) );
        } else {
            $html = $named_result;
        }
    }

    // ── Pattern 2: Bare cite ──────────────────────────────────────────────
    //
    // Matches § or §§ followed by a section number. No code name required.
    // All occurrences bolded — bare cites are unambiguous.
    //
    // To prevent double-wrapping citations already caught by the named pass,
    // the HTML is split on existing <strong>...</strong> blocks. The bare
    // pattern runs only on the segments between them (even indices after
    // PREG_SPLIT_DELIM_CAPTURE). Captured <strong> blocks (odd indices)
    // are passed through unchanged.

    $pattern_bare = '/(§{1,2}\s*[\d]+[\d.\-()\w]*(?<![.]))/u';

    $parts = preg_split( '/(<strong>.*?<\/strong>)/us', $html, -1, PREG_SPLIT_DELIM_CAPTURE );

    if ( $parts === false ) {
        // preg_split() failure — previously unchecked. Falling through with
        // $parts = false would throw when the foreach below runs. Fail open
        // to the HTML as it stood after the named-citation pass rather than
        // losing the whole section.
        ws_log_loud_failure( new WS_Loud_Failure( 'statute-bold', 'preg_split() failed splitting on <strong> blocks — skipping the bare-citation pass for this render.', [
            'jx_name'    => $jx_name,
            'pcre_error' => preg_last_error_msg(),
        ] ) );
        return $html;
    }

    $result = '';

    foreach ( $parts as $i => $part ) {
        if ( $i % 2 === 1 ) {
            // Odd index — captured <strong> block, pass through unchanged.
            $result .= $part;
        } else {
            // Even index — plain text segment, apply bare cite pattern.
            $bare_result = preg_replace_callback(
                $pattern_bare,
                function( $m ) {
                    return '<strong>' . $m[1] . '</strong>';
                },
                $part
            );

            // Same null-on-failure risk as the named pass above, but worse
            // here: $result .= null silently drops this entire text segment
            // from the visitor-facing output — actual content loss, not just
            // a cosmetic un-bolding. Fail open to the original segment text.
            if ( $bare_result === null ) {
                ws_log_loud_failure( new WS_Loud_Failure( 'statute-bold', 'preg_replace_callback() failed on the bare-citation pattern for one text segment — using the unbolded original instead of dropping it.', [
                    'jx_name'    => $jx_name,
                    'pcre_error' => preg_last_error_msg(),
                ] ) );
                $result .= $part;
            } else {
                $result .= $bare_result;
            }
        }
    }

    return $result;
}
