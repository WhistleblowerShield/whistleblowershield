
---

## Codex Prompt — Engagement Scoring + Profile View Logging

### Context
The assist-org directory has a filter scoring system in `ws-filter-context.php` (`ws_filter_score_org()`). We are adding two parallel systems: an engagement scoring layer that appends to relevance scoring without altering it, and a profile view logger triggered when a user clicks the "More about this organization" link from the directory.

---

### `ws-filter-config.php`

Add alongside existing `$ws_filter_score_weights`:

```php
// ── Engagement Score Weights ──────────────────────────────────────────────
// Engagement scoring is additive — appended after relevance scoring, never
// merged into it. A general org with strong engagement cannot outscore a
// targeted org with weak engagement. The cap enforces this guarantee.
// has_secure_channel is intentionally excluded — already surfaced in render.

$ws_filter_engagement_weights = [
    'has_hotline'        => 2,  // most actionable first-contact for Maya/James
    'has_intake_url'     => 1,
    'has_intake_email'   => 1,
    'has_intake_phone'   => 1,
    'has_tty'            => 1,
];

// Hard cap. Max single-axis relevance miss = 3-8pts. Cap must stay below that.
define( 'WS_FILTER_ENGAGEMENT_SCORE_CAP', 4 );
```

Add log file constant alongside existing filter log constants:

```php
define( 'WS_FILTER_PROFILE_LOG', WP_CONTENT_DIR . '/logs/ws-filter/profile-views.log' );
```

---

### `ws-filter-context.php`

Add new function `ws_filter_score_engagement()`:

```php
/**
 * Derives an engagement score from contact-path quality signals.
 *
 * Engagement scoring is additive — it appends to the relevance score
 * returned by ws_filter_score_org() and never replaces or alters it.
 * Weights and cap are defined in ws-filter-config.php.
 *
 * Called in ws_filter_sort_orgs() immediately after ws_filter_score_org().
 *
 * @param array $org Normalized org row from ws_q_build_assist_org_row().
 * @return int Engagement score, capped at WS_FILTER_ENGAGEMENT_SCORE_CAP.
 */
function ws_filter_score_engagement( array $org ): int {
    global $ws_filter_engagement_weights;

    $w     = $ws_filter_engagement_weights ?? [];
    $score = 0;

    $phones      = is_array( $org['phones'] ?? null ) ? $org['phones'] : [];
    $emails      = is_array( $org['emails'] ?? null ) ? $org['emails'] : [];
    $phone_types = array_column( $phones, 'type' );
    $email_types = array_column( $emails, 'type' );

    if ( in_array( 'hotline', $phone_types, true ) ) $score += (int) ( $w['has_hotline']        ?? 0 );
    if ( in_array( 'intake',  $phone_types, true ) ) $score += (int) ( $w['has_intake_phone']   ?? 0 );
    if ( in_array( 'tty',     $phone_types, true ) ) $score += (int) ( $w['has_tty']            ?? 0 );
    if ( in_array( 'intake',  $email_types, true ) ) $score += (int) ( $w['has_intake_email']   ?? 0 );
    if ( ! empty( $org['intake_url'] ) )              $score += (int) ( $w['has_intake_url']     ?? 0 );

    return min( $score, defined( 'WS_FILTER_ENGAGEMENT_SCORE_CAP' ) ? WS_FILTER_ENGAGEMENT_SCORE_CAP : 4 );
}
```

Add new function `ws_filter_log_profile_view()`:

```php
/**
 * Logs a profile view event when a user navigates from the directory
 * to an org's full profile page.
 *
 * No user identity is logged. Filter context is resolved from GET params
 * carried over from the directory — intact when arriving from a filtered
 * directory, has_filters=false otherwise. Both are useful signals.
 *
 * Log file: WS_FILTER_PROFILE_LOG (wp-content/logs/ws-filter/profile-views.log)
 * Pruning and .htaccess protection follow the same pattern as directory-requests.log.
 *
 * @param int   $org_id  Post ID of the ws-assist-org being viewed.
 * @param array $context Normalized filter context from ws_resolve_filter_context().
 * @return void
 */
function ws_filter_log_profile_view( int $org_id, array $context, array $click_meta = [] ): void {

    $rank      = isset( $click_meta['rank'] )      ? (int) $click_meta['rank']      : 0;
    $rel_score = isset( $click_meta['rel_score'] ) ? (int) $click_meta['rel_score'] : 0;
    $eng_score = isset( $click_meta['eng_score'] ) ? (int) $click_meta['eng_score'] : 0;
    $secure    = ! empty( $click_meta['secure'] )  ? 'yes' : 'no';

    $log_dir = WP_CONTENT_DIR . '/logs/ws-filter';
    if ( ! file_exists( $log_dir ) ) {
        wp_mkdir_p( $log_dir );
        file_put_contents( $log_dir . '/.htaccess', "Deny from all\n" );
    }

    $path    = defined( 'WS_FILTER_PROFILE_LOG' ) ? WS_FILTER_PROFILE_LOG : $log_dir . '/profile-views.log';
    $ts      = gmdate( 'Y-m-d H:i:s' );
    $stage   = $context['stage']   ?? '-';
    $concern = $context['concern'] ?? '-';
    $tax     = $context['concern_tax'] ? str_replace( 'ws_', '', $context['concern_tax'] ) : '-';
    $sector  = $context['sector']  ?? '-';
    $target  = $context['target']  ?? '-';
    $filters = $context['has_filters'] ? 'yes' : 'no';

    $line = "[{$ts} UTC]  event:profile_view  org_id:{$org_id}  rank:{$rank}  rel:{$rel_score}  eng:{$eng_score}  secure_rendered:{$secure}  stage:{$stage}  concern:{$concern}({$tax})  sector:{$sector}  target:{$target}  filtered:{$filters}" . PHP_EOL;

    file_put_contents( $path, $line, FILE_APPEND | LOCK_EX );

    $max = defined( 'WS_FILTER_LOG_MAX_LINES' ) ? (int) WS_FILTER_LOG_MAX_LINES : 5000;
    ws_filter_prune_log( $path, $max );
}
```

In `ws_filter_sort_orgs()`, update the scoring line:

```php
// Before:
'score' => ws_filter_score_org( $org, $context, $targeted ),

// After:
$rel_score = ws_filter_score_org( $org, $context, $targeted );
$eng_score = ws_filter_score_engagement( $org );
$scored[] = [
    'org'       => $org,
    'score'     => $rel_score + $eng_score,
    'rel_score' => $rel_score,
    'eng_score' => $eng_score,
];

foreach ( $scored as &$item ) {
    $item['org']['_rel_score'] = $item['rel_score'];
    $item['org']['_eng_score'] = $item['eng_score'];
}
unset( $item );
```

---

### `render-directory.php`

render-directory.php — ws_render_directory_listing()
Currently iterates $items and calls ws_render_directory_card() per org. Add position tracking and pass enriched data to the card:
```php
// Before:
foreach ( $items as $org ) {
    echo ws_render_directory_card( $org );
}

// After:
foreach ( $items as $position => $org ) {
    echo ws_render_directory_card( $org, $position + 1 );
}
```
render-directory.php — ws_render_directory_card()
Update signature to accept position:
```php
// Before:
function ws_render_directory_card( $org ) {

// After:
function ws_render_directory_card( array $org, int $position = 0 ) {
```

Update the "More about this organization" link to carry enriched query args:
```php
// Before:
add_query_arg( 'ws_from_dir', '1', $org['url'] )

// After:
add_query_arg( [
    'ws_from_dir'  => '1',
    'ws_rank'      => $position,
    'ws_rel_score' => (int) ( $org['_rel_score'] ?? 0 ),
    'ws_eng_score' => (int) ( $org['_eng_score'] ?? 0 ),
    'ws_secure'    => ! empty( $org['has_secure_channel'] ) ? '1' : '0',
], $org['url'] )
```

---

### `shortcodes-general.php` (or `render-directory.php` — whichever owns the frontend init hook)

Add a hook that fires on `ws-assist-org` single page loads arriving from the directory:

```php
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
    $context    = ws_resolve_filter_context();
	$click_meta = [
		'rank'      => isset( $_GET['ws_rank'] )      ? (int) $_GET['ws_rank']      : 0,
		'rel_score' => isset( $_GET['ws_rel_score'] ) ? (int) $_GET['ws_rel_score'] : 0,
		'eng_score' => isset( $_GET['ws_eng_score'] ) ? (int) $_GET['ws_eng_score'] : 0,
		'secure'    => ! empty( $_GET['ws_secure'] ) && $_GET['ws_secure'] === '1',
	];
	ws_filter_log_profile_view( get_the_ID(), $context, $click_meta );
} );
```

Note: `ws_resolve_filter_context()` is loaded in the Universal Layer so it is available on the frontend. The hook fires once per page load, no JavaScript, no AJAX, no user identity stored.

---

### Version bumps
- `ws-filter-config.php`  — bump version, add changelog entry
- `ws-filter-context.php` — bump version, add changelog entry
- `render-directory.php`  — bump version, add changelog entry
---

### Do not touch
- `ws_filter_score_org()` — relevance scoring is unchanged
- The filter log format for `directory-requests.log` — profile view events go to a separate file only
- Any prompt template files — separate repair in progress