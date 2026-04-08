<?php
$inc = WP_PLUGIN_DIR . '/ws-core/includes';
if ( ! function_exists('ws_find_related_jx_record_id') ) require_once $inc . '/admin/admin-navigation.php';
if ( ! function_exists('ws_acf_log_major_edit') ) require_once $inc . '/admin/admin-major-edit-hook.php';
if ( ! function_exists('ws_matrix_assign_terms') ) require_once $inc . '/admin/matrix/matrix-helpers.php';
if ( ! function_exists('ws_feed_monitor_read_staged') ) require_once $inc . '/admin/monitors/admin-feed-monitor.php';

$results = [];
$cleanup_posts = [];
$created_term_id = 0;

// cleanup any prior interrupted Codex test posts
$old = get_posts([
  'post_type' => ['jx-summary','jx-statute'],
  'post_status' => 'any',
  'posts_per_page' => 50,
  's' => 'Codex ',
  'fields' => 'ids',
]);
foreach ( (array) $old as $oid ) { wp_delete_post((int)$oid, true); }

$term = get_term_by('slug','us',WS_JURISDICTION_TAXONOMY);
if ( ! $term || is_wp_error($term) ) {
    $created = wp_insert_term('Codex Test Jurisdiction', WS_JURISDICTION_TAXONOMY, ['slug' => 'codex-test-jx']);
    if ( ! is_wp_error($created) ) {
        $created_term_id = (int) $created['term_id'];
        $term = get_term($created_term_id, WS_JURISDICTION_TAXONOMY);
    }
}
$term_id = ( $term && ! is_wp_error($term) ) ? (int) $term->term_id : 0;
$results['term_id'] = $term_id;

if ( $term_id ) {
    $pub = wp_insert_post(['post_type'=>'jx-summary','post_status'=>'publish','post_title'=>'Codex Pub Summary '.time()]);
    $drf = wp_insert_post(['post_type'=>'jx-summary','post_status'=>'draft','post_title'=>'Codex Draft Summary '.time()]);
    if ( $pub ) { wp_set_object_terms($pub, [$term_id], WS_JURISDICTION_TAXONOMY); $cleanup_posts[] = (int)$pub; }
    if ( $drf ) { wp_set_object_terms($drf, [$term_id], WS_JURISDICTION_TAXONOMY); $cleanup_posts[] = (int)$drf; }

    if ( $pub ) {
        wp_update_post(['ID'=>$pub,'post_modified'=>'2024-01-01 00:00:00','post_modified_gmt'=>'2024-01-01 00:00:00']);
    }

    $picked = function_exists('ws_find_related_jx_record_id') ? (int) ws_find_related_jx_record_id('jx-summary', $term_id) : 0;
    $results['helper_publish_preferred'] = [
        'publish_id' => (int)$pub,
        'draft_id'   => (int)$drf,
        'picked_id'  => (int)$picked,
        'pass'       => ( (int)$picked === (int)$pub ),
    ];
}

$src = wp_insert_post(['post_type'=>'jx-statute','post_status'=>'draft','post_title'=>'Codex Major Source '.time()]);
if ( $src && ! is_wp_error($src) ) {
    $src = (int)$src;
    $cleanup_posts[] = $src;
    update_post_meta($src,'ws_is_major_edit',1);
    update_post_meta($src,'ws_major_edit_description','Codex failure-path description');

    $fail_filter = function($maybe_empty, $postarr){
        if ( ($postarr['post_type'] ?? '') === 'ws-legal-update' ) {
            return true;
        }
        return $maybe_empty;
    };
    add_filter('wp_insert_post_empty_content', $fail_filter, 10, 2);
    ws_acf_log_major_edit($src);
    remove_filter('wp_insert_post_empty_content', $fail_filter, 10);

    $results['major_edit_failure_preserves_input'] = [
        'is_major'     => (int) get_post_meta($src,'ws_is_major_edit',true),
        'description'  => (string) get_post_meta($src,'ws_major_edit_description',true),
        'pass'         => ((int)get_post_meta($src,'ws_is_major_edit',true) === 1)
                          && ((string)get_post_meta($src,'ws_major_edit_description',true) === 'Codex failure-path description'),
    ];
}

$src2 = wp_insert_post(['post_type'=>'jx-statute','post_status'=>'draft','post_title'=>'Codex Major Empty '.time()]);
if ( $src2 && ! is_wp_error($src2) ) {
    $src2 = (int)$src2;
    $cleanup_posts[] = $src2;
    update_post_meta($src2,'ws_is_major_edit',1);
    update_post_meta($src2,'ws_major_edit_description','    ');
    ws_acf_log_major_edit($src2);

    $results['major_edit_empty_clears_fields'] = [
        'is_major'    => (int) get_post_meta($src2,'ws_is_major_edit',true),
        'description' => (string) get_post_meta($src2,'ws_major_edit_description',true),
        'pass'        => ((int)get_post_meta($src2,'ws_is_major_edit',true) === 0)
                         && ((string)get_post_meta($src2,'ws_major_edit_description',true) === ''),
    ];
}

if ( function_exists('ws_feed_monitor_read_staged') && function_exists('ws_feed_monitor_write_staged') ) {
    $orig = ws_feed_monitor_read_staged();
    $probe = [[
        'guid' => 'codex-test-guid-'.time(),
        'title' => 'Codex Probe',
        'status' => 'pending',
        'staged_at' => current_time('Y-m-d H:i:s'),
    ]];
    $w1 = ws_feed_monitor_write_staged($probe);
    $r1 = ws_feed_monitor_read_staged();
    $w2 = ws_feed_monitor_write_staged(is_array($orig) ? $orig : []);
    $results['feed_staged_roundtrip'] = [
        'write_probe' => (bool)$w1,
        'read_is_array' => is_array($r1),
        'guid_match' => (is_array($r1) && !empty($r1[0]['guid']) && $r1[0]['guid'] === $probe[0]['guid']),
        'restore_ok' => (bool)$w2,
        'pass' => (bool)$w1 && is_array($r1) && !empty($r1[0]['guid']) && ($r1[0]['guid'] === $probe[0]['guid']) && (bool)$w2,
    ];
}

if ( function_exists('ws_matrix_assign_terms') && ! empty($cleanup_posts) ) {
    ws_matrix_assign_terms((int)$cleanup_posts[0], ['nonexistent-term-slug'], 'ws_nonexistent_taxonomy');
    $results['matrix_helper_smoke'] = ['pass' => true];
}

foreach ( $cleanup_posts as $pid ) {
    wp_delete_post((int)$pid, true);
}
if ( $created_term_id ) {
    wp_delete_term($created_term_id, WS_JURISDICTION_TAXONOMY);
}

echo wp_json_encode($results, JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES), "\n";
