<?php
define('ABSPATH', __DIR__);
define('WS_JURISDICTION_TAXONOMY', 'ws_jurisdiction');
$GLOBALS['captured'] = [];
$GLOBALS['term_id_seq'] = 1;

function add_action(...$args) {}
function taxonomy_exists($taxonomy) { return false; }
function register_taxonomy(...$args) { return true; }
function term_exists($slug, $taxonomy) { return false; }
function wp_insert_term($name, $taxonomy, $args = []) {
    $slug = $args['slug'] ?? strtolower(trim(preg_replace('/[^a-z0-9]+/', '-', (string) $name), '-'));
    if (!isset($GLOBALS['captured'][$taxonomy])) {
        $GLOBALS['captured'][$taxonomy] = [];
    }
    $GLOBALS['captured'][$taxonomy][$slug] = true;
    return ['term_id' => $GLOBALS['term_id_seq']++];
}
function update_option(...$args) { return true; }
function get_option(...$args) { return null; }
function update_term_meta(...$args) { return true; }
function get_term_by(...$args) { return (object) ['term_id' => 1]; }
function is_wp_error($value) { return false; }

require 'plugins/ws-core/includes/taxonomies/register-taxonomies.php';

$seedFns = array_filter(
    get_defined_functions()['user'],
    function ($fn) {
        return str_starts_with($fn, 'ws_seed_') && str_ends_with($fn, '_taxonomy');
    }
);

sort($seedFns);
foreach ($seedFns as $fn) {
    $fn();
}

$out = [];
foreach ($GLOBALS['captured'] as $taxonomy => $slugsMap) {
    $slugs = array_keys($slugsMap);
    sort($slugs);
    $out[$taxonomy] = $slugs;
}
ksort($out);

echo json_encode($out, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES), PHP_EOL;
?>
