<?php
$out = [];
foreach ( get_taxonomies([], 'names') as $tax ) {
    if ( strpos( $tax, 'ws_' ) !== 0 ) {
        continue;
    }
    $terms = get_terms([
        'taxonomy'   => $tax,
        'hide_empty' => false,
        'fields'     => 'slugs',
    ]);
    if ( is_wp_error( $terms ) ) {
        continue;
    }
    sort( $terms );
    $out[$tax] = array_values( array_unique( $terms ) );
}
ksort( $out );
echo json_encode( $out, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES );
