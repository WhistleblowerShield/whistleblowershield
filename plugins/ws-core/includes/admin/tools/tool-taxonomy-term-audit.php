<?php
/**
 * tool-taxonomy-term-audit.php
 *
 * WhistleblowerShield Core Plugin - Admin Tool
 *
 * PURPOSE
 * -------
 * Audits live ws_* taxonomy terms against terms declared in
 * includes/taxonomies/register-taxonomies.php seed functions.
 *
 * This helps operators detect:
 * - extra terms in DB not present in seed declarations ("new" terms)
 * - seed-declared terms missing from DB
 *
 * ACCESS
 * ------
 * Admin only. Registered under Tools.
 *
 * @package WhistleblowerShield
 * @since   3.14.2
 * @version    3.20.0
 */

defined( 'ABSPATH' ) || exit;

add_action( 'admin_menu', 'ws_register_taxonomy_term_audit_page' );

/**
 * Registers the taxonomy audit page under Tools.
 */
function ws_register_taxonomy_term_audit_page() {
    add_submenu_page(
        'tools.php',
        'WS Taxonomy Term Audit',
        'WS Taxonomy Audit',
        'manage_options',
        'ws-taxonomy-term-audit',
        'ws_render_taxonomy_term_audit_page'
    );
}

/**
 * Renders the taxonomy audit admin page and optionally runs a scan.
 */
function ws_render_taxonomy_term_audit_page() {
    if ( ! current_user_can( 'manage_options' ) ) {
        wp_die( esc_html__( 'You do not have permission to access this page.', 'ws-core' ) );
    }

    $scan_requested = isset( $_POST['ws_tax_audit_run'] );
    $results        = null;
    $log_file       = '';

    if ( $scan_requested ) {
        check_admin_referer( 'ws_tax_audit_nonce', 'ws_tax_audit_nonce' );
        $results  = ws_tax_audit_run();
        $log_file = ws_tax_audit_write_php_log( $results );
    }

    ?>
    <div class="wrap">
        <h1>WS Taxonomy Term Audit</h1>
        <p>
            Compares live <code>ws_*</code> taxonomy terms against slugs declared in
            <code>register-taxonomies.php</code> seed functions.
        </p>

        <form method="post">
            <?php wp_nonce_field( 'ws_tax_audit_nonce', 'ws_tax_audit_nonce' ); ?>
            <input type="hidden" name="ws_tax_audit_run" value="1" />
            <?php submit_button( 'Run Taxonomy Audit', 'primary', 'submit', false ); ?>
        </form>

        <?php if ( is_array( $results ) ) : ?>
            <?php
            $has_extra   = ! empty( $results['extra_rows'] );
            $has_missing = ! empty( $results['missing_rows'] );
            ?>

            <hr />
            <h2>Summary</h2>
            <ul>
                <li><strong>Seeded taxonomies parsed:</strong> <?php echo (int) $results['seed_taxonomies']; ?></li>
                <li><strong>Live taxonomies scanned:</strong> <?php echo (int) $results['live_taxonomies']; ?></li>
                <li><strong>Taxonomies with extra terms:</strong> <?php echo (int) count( $results['extra_rows'] ); ?></li>
                <li><strong>Taxonomies with missing seeded terms:</strong> <?php echo (int) count( $results['missing_rows'] ); ?></li>
            </ul>

            <?php if ( ! empty( $results['parse_warnings'] ) ) : ?>
                <div class="notice notice-warning inline">
                    <p><strong>Parser Warnings</strong></p>
                    <ul style="margin: 0 0 0 1.2rem;">
                        <?php foreach ( (array) $results['parse_warnings'] as $warn ) : ?>
                            <li><code><?php echo esc_html( (string) $warn ); ?></code></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <?php if ( ! $has_extra && ! $has_missing ) : ?>
                <div class="notice notice-success inline"><p><strong>MATCH_FOR_REGISTER_TAXONOMIES</strong></p></div>
            <?php endif; ?>

            <h2>Extra Terms (Live Not In Seed)</h2>
            <?php if ( $has_extra ) : ?>
                <?php ws_tax_audit_render_rows_table( $results['extra_rows'] ); ?>
            <?php else : ?>
                <p>None.</p>
            <?php endif; ?>

            <h2>Missing Terms (Seed Not In Live)</h2>
            <?php if ( $has_missing ) : ?>
                <?php ws_tax_audit_render_rows_table( $results['missing_rows'] ); ?>
            <?php else : ?>
                <p>None.</p>
            <?php endif; ?>

            <?php if ( $log_file !== '' ) : ?>
                <h2>Output Log</h2>
                <p>
                    Saved PHP output to:
                    <code><?php echo esc_html( $log_file ); ?></code>
                </p>
            <?php endif; ?>
        <?php endif; ?>
    </div>
    <?php
}

/**
 * Renders taxonomy/slugs rows as an admin table.
 *
 * @param array<int,array<string,mixed>> $rows Rows with keys: taxonomy, slugs.
 */
function ws_tax_audit_render_rows_table( $rows ) {
    echo '<table class="widefat striped" style="max-width: 1200px;">';
    echo '<thead><tr><th style="width:260px;">Taxonomy</th><th>Slugs</th></tr></thead><tbody>';
    foreach ( $rows as $row ) {
        $slugs = (array) ( $row['slugs'] ?? [] );
        $parents = (array) ( $row['parents'] ?? [] );
        $display = [];
        foreach ( $slugs as $slug ) {
            $slug = (string) $slug;
            $parent_slug = (string) ( $parents[ $slug ] ?? '' );
            if ( $parent_slug !== '' ) {
                $display[] = $slug . ' (parent: ' . $parent_slug . ')';
            } else {
                $display[] = $slug;
            }
        }
        echo '<tr>';
        echo '<td><code>' . esc_html( (string) $row['taxonomy'] ) . '</code></td>';
        echo '<td>' . esc_html( implode( ', ', $display ) ) . '</td>';
        echo '</tr>';
    }
    echo '</tbody></table>';
}

function ws_tax_audit_run() {
    $seed_map    = ws_tax_audit_get_seed_map();
    $live_map    = ws_tax_audit_get_live_map();
    $live_labels = ws_tax_audit_get_live_label_map();
    $live_parent_map = ws_tax_audit_get_live_parent_map();

    $all_taxonomies = array_values( array_unique( array_merge( array_keys( $seed_map ), array_keys( $live_map ) ) ) );
    sort( $all_taxonomies );

    $extra_rows   = [];
    $missing_rows = [];
    $parse_warnings = [];

    foreach ( $all_taxonomies as $taxonomy ) {
        $seed_slugs = isset( $seed_map[ $taxonomy ] ) ? array_values( array_unique( $seed_map[ $taxonomy ] ) ) : [];
        $live_slugs = isset( $live_map[ $taxonomy ] ) ? array_values( array_unique( $live_map[ $taxonomy ] ) ) : [];

        sort( $seed_slugs );
        sort( $live_slugs );

        // If seed extraction produced no slugs for a taxonomy, skip diffing it.
        // This prevents false positives where all live terms appear as "extra".
        if ( empty( $seed_slugs ) ) {
            $parse_warnings[] = sprintf(
                '%s: seed slug extraction returned empty set; skipped from extra/missing diff.',
                $taxonomy
            );
            continue;
        }

        $extra   = array_values( array_diff( $live_slugs, $seed_slugs ) );
        $missing = array_values( array_diff( $seed_slugs, $live_slugs ) );

        if ( ! empty( $extra ) ) {
            $parents = [];
            foreach ( $extra as $slug ) {
                $parents[ $slug ] = (string) ( $live_parent_map[ $taxonomy ][ $slug ] ?? '' );
            }
            $extra_rows[] = [
                'taxonomy' => $taxonomy,
                'slugs'    => $extra,
                'parents'  => $parents,
            ];
        }

        if ( ! empty( $missing ) ) {
            $missing_rows[] = [
                'taxonomy' => $taxonomy,
                'slugs'    => $missing,
            ];
        }
    }

    return [
        'seed_taxonomies' => count( $seed_map ),
        'live_taxonomies' => count( $live_map ),
        'extra_rows'      => $extra_rows,
        'missing_rows'    => $missing_rows,
        'parse_warnings'  => $parse_warnings,
        'live_labels'     => $live_labels,
    ];
}

function ws_tax_audit_get_live_map() {
    $map = [];
    $taxonomies = get_taxonomies( [], 'names' );

    foreach ( (array) $taxonomies as $taxonomy ) {
        if ( strpos( $taxonomy, 'ws_' ) !== 0 ) {
            continue;
        }
        if ( $taxonomy === 'ws_glossary' ) {
            continue;
        }

        $terms = get_terms( [
            'taxonomy'   => $taxonomy,
            'hide_empty' => false,
            'number'     => 0,
        ] );

        if ( is_wp_error( $terms ) ) {
            continue;
        }

        $map[ $taxonomy ] = [];
        foreach ( (array) $terms as $term ) {
            if ( ! empty( $term->slug ) ) {
                $map[ $taxonomy ][] = (string) $term->slug;
            }
        }
    }

    foreach ( $map as $taxonomy => $slugs ) {
        $slugs = array_values( array_unique( $slugs ) );
        sort( $slugs );
        $map[ $taxonomy ] = $slugs;
    }

    ksort( $map );
    return $map;
}

function ws_tax_audit_get_live_label_map() {
    $map = [];
    $taxonomies = get_taxonomies( [], 'names' );

    foreach ( (array) $taxonomies as $taxonomy ) {
        if ( strpos( $taxonomy, 'ws_' ) !== 0 ) {
            continue;
        }
        if ( $taxonomy === 'ws_glossary' ) {
            continue;
        }

        $terms = get_terms( [
            'taxonomy'   => $taxonomy,
            'hide_empty' => false,
            'number'     => 0,
        ] );

        if ( is_wp_error( $terms ) ) {
            continue;
        }

        $map[ $taxonomy ] = [];
        foreach ( (array) $terms as $term ) {
            if ( ! empty( $term->slug ) ) {
                $map[ $taxonomy ][ (string) $term->slug ] = (string) $term->name;
            }
        }

        ksort( $map[ $taxonomy ] );
    }

    ksort( $map );
    return $map;
}

function ws_tax_audit_get_live_parent_map() {
    $map = [];
    $taxonomies = get_taxonomies( [], 'names' );

    foreach ( (array) $taxonomies as $taxonomy ) {
        if ( strpos( $taxonomy, 'ws_' ) !== 0 ) {
            continue;
        }
        if ( $taxonomy === 'ws_glossary' ) {
            continue;
        }

        $terms = get_terms( [
            'taxonomy'   => $taxonomy,
            'hide_empty' => false,
            'number'     => 0,
        ] );

        if ( is_wp_error( $terms ) ) {
            continue;
        }

        $by_id = [];
        foreach ( (array) $terms as $term ) {
            $by_id[ (int) $term->term_id ] = $term;
        }

        $map[ $taxonomy ] = [];
        foreach ( (array) $terms as $term ) {
            $slug = (string) ( $term->slug ?? '' );
            if ( $slug === '' ) {
                continue;
            }
            $parent_slug = '';
            $parent_id = (int) ( $term->parent ?? 0 );
            if ( $parent_id > 0 && isset( $by_id[ $parent_id ] ) ) {
                $parent_slug = (string) ( $by_id[ $parent_id ]->slug ?? '' );
            }
            $map[ $taxonomy ][ $slug ] = $parent_slug;
        }
    }

    return $map;
}

function ws_tax_audit_get_seed_map() {
    $file = WS_CORE_PATH . 'includes/taxonomies/register-taxonomies.php';
    if ( ! file_exists( $file ) ) {
        return [];
    }

    $source = file_get_contents( $file );
    if ( ! is_string( $source ) || $source === '' ) {
        return [];
    }

    $functions = ws_tax_audit_extract_seed_function_bodies( $source );
    $map       = [];

    foreach ( $functions as $body ) {
        $taxonomy = ws_tax_audit_extract_seed_taxonomy( $body );
        if ( $taxonomy === '' ) {
            continue;
        }

        if ( ! isset( $map[ $taxonomy ] ) ) {
            $map[ $taxonomy ] = [];
        }

        $slugs = ws_tax_audit_extract_slugs_from_seed_body( $body );
        $map[ $taxonomy ] = array_merge( $map[ $taxonomy ], $slugs );
    }

    foreach ( $map as $taxonomy => $slugs ) {
        $slugs = array_values( array_unique( $slugs ) );
        sort( $slugs );
        $map[ $taxonomy ] = $slugs;
    }

    ksort( $map );
    return $map;
}

function ws_tax_audit_extract_seed_function_bodies( $source ) {
    $tokens    = token_get_all( $source );
    $functions = [];

    $count = count( $tokens );
    for ( $i = 0; $i < $count; $i++ ) {
        $token = $tokens[ $i ];
        if ( ! is_array( $token ) || $token[0] !== T_FUNCTION ) {
            continue;
        }

        $name = '';
        for ( $j = $i + 1; $j < $count; $j++ ) {
            if ( is_array( $tokens[ $j ] ) && $tokens[ $j ][0] === T_STRING ) {
                $name = (string) $tokens[ $j ][1];
                break;
            }
        }

        if ( $name === '' || strpos( $name, 'ws_seed_' ) !== 0 || substr( $name, -9 ) !== '_taxonomy' ) {
            continue;
        }

        $open = null;
        for ( $k = $j; $k < $count; $k++ ) {
            $chunk = $tokens[ $k ];
            if ( ! is_array( $chunk ) && $chunk === '{' ) {
                $open = $k;
                break;
            }
        }

        if ( $open === null ) {
            continue;
        }

        $depth = 0;
        $body  = '';
        for ( $m = $open; $m < $count; $m++ ) {
            $chunk = $tokens[ $m ];
            $text  = is_array( $chunk ) ? $chunk[1] : $chunk;

            if ( ! is_array( $chunk ) && $chunk === '{' ) {
                $depth++;
            }
            if ( ! is_array( $chunk ) && $chunk === '}' ) {
                $depth--;
            }

            $body .= $text;

            if ( $depth === 0 ) {
                $i = $m;
                break;
            }
        }

        $functions[ $name ] = $body;
    }

    return $functions;
}

function ws_tax_audit_extract_seed_taxonomy( $body ) {
    if ( preg_match( '/\$taxonomy\s*=\s*\'((?:ws_)[a-z0-9_]+)\'/', $body, $m ) ) {
        return (string) $m[1];
    }

    // Supports seeders that assign taxonomy via constant (e.g. WS_JURISDICTION_TAXONOMY).
    if ( preg_match( '/\\$taxonomy\\s*=\\s*([A-Z_][A-Z0-9_]*)\\s*;/', $body, $m ) ) {
        $constant_name = (string) $m[1];
        if ( defined( $constant_name ) ) {
            $resolved = (string) constant( $constant_name );
            if ( strpos( $resolved, 'ws_' ) === 0 ) {
                return $resolved;
            }
        }
        if ( $constant_name === 'WS_JURISDICTION_TAXONOMY' ) {
            return 'WS_JURISDICTION_TAXONOMY';
        }
    }

    if ( preg_match( "/ws_bulk_insert_hierarchical\\s*\\([^,]+,\\s*'((?:ws_)[a-z0-9_]+)'\\s*\\)/", $body, $m ) ) {
        return (string) $m[1];
    }

    if ( preg_match( "/term_exists\\s*\\(\\s*'[^']+'\\s*,\\s*'((?:ws_)[a-z0-9_]+)'\\s*\\)/", $body, $m ) ) {
        return (string) $m[1];
    }

    return '';
}

function ws_tax_audit_extract_slugs_from_seed_body( $body ) {
    $slugs = [];
    $skip_keys = [ 'name', 'children', 'slug', 'taxonomy', 'terms', 'parent' ];

    // Flat seed arrays: $terms = [ 'slug' => 'Label', ... ].
    if ( preg_match_all( '/\\$terms\\s*=\\s*\\[(.*?)\\];/s', $body, $blocks ) ) {
        foreach ( (array) $blocks[1] as $block ) {
            if ( preg_match_all( "/'([a-z0-9-]+)'\\s*=>/", (string) $block, $m ) ) {
                foreach ( (array) $m[1] as $key ) {
                    if ( in_array( $key, $skip_keys, true ) ) {
                        continue;
                    }
                    $slugs[] = (string) $key;
                }
            }
        }
    }

    // Hierarchical seed arrays: $hierarchy = [ parent => [ 'children' => [ child => label ] ] ].
    if ( preg_match_all( '/\\$hierarchy\\s*=\\s*\\[(.*?)\\];/s', $body, $blocks ) ) {
        foreach ( (array) $blocks[1] as $block ) {
            if ( preg_match_all( "/'([a-z0-9-]+)'\\s*=>/", (string) $block, $m ) ) {
                foreach ( (array) $m[1] as $key ) {
                    if ( in_array( $key, $skip_keys, true ) ) {
                        continue;
                    }
                    $slugs[] = (string) $key;
                }
            }
        }
    }

    // Fallback broad key capture for parser resilience (flat seed functions).
    if ( preg_match_all( "/'([a-z0-9-]+)'\\s*=>/", (string) $body, $all_keys ) ) {
        foreach ( (array) $all_keys[1] as $key ) {
            if ( in_array( $key, $skip_keys, true ) ) {
                continue;
            }
            $slugs[] = (string) $key;
        }
    }

    // Explicit term_exists guards often include sentinels/special terms.
    if ( preg_match_all( "/term_exists\\s*\\(\\s*'([a-z0-9-]+)'\\s*,\\s*'(?:ws_)[a-z0-9_]+'\\s*\\)/", $body, $matches ) ) {
        $slugs = array_merge( $slugs, array_map( 'strval', (array) $matches[1] ) );
    }

    // Direct slug insertions: wp_insert_term(..., [ 'slug' => '...' ]).
    if ( preg_match_all( "/'slug'\\s*=>\\s*'([a-z0-9-]+)'/", $body, $matches ) ) {
        $slugs = array_merge( $slugs, array_map( 'strval', (array) $matches[1] ) );
    }

    $slugs = array_values( array_unique( array_map( 'strval', $slugs ) ) );
    sort( $slugs );

    return $slugs;
}

function ws_tax_audit_build_php_block( $results ) {
    $php = "<?php\n";
    $php .= '// WS Taxonomy Audit (paste-ready slug => label rows)' . "\n";
    $php .= '// Generated: ' . gmdate( 'Y-m-d H:i:s' ) . ' UTC' . "\n\n";

    $extra_rows   = (array) ( $results['extra_rows'] ?? [] );
    $missing_rows = (array) ( $results['missing_rows'] ?? [] );
    $labels_map   = (array) ( $results['live_labels'] ?? [] );

    if ( empty( $extra_rows ) && empty( $missing_rows ) ) {
        $php .= '// MATCH_FOR_REGISTER_TAXONOMIES' . "\n";
        return $php;
    }

    $php .= '$ws_taxonomy_new_terms = [' . "\n";
    foreach ( $extra_rows as $row ) {
        $taxonomy = (string) ( $row['taxonomy'] ?? '' );
        $slugs    = (array) ( $row['slugs'] ?? [] );
        $parents  = (array) ( $row['parents'] ?? [] );
        if ( $taxonomy === '' || empty( $slugs ) ) {
            continue;
        }

        $php .= "    '" . ws_tax_audit_php_escape( $taxonomy ) . "' => [\n";
        foreach ( $slugs as $slug ) {
            $slug = (string) $slug;
            $name = (string) ( $labels_map[ $taxonomy ][ $slug ] ?? $slug );
            $line = "        '" . ws_tax_audit_php_escape( $slug ) . "' => '" . ws_tax_audit_php_escape( $name ) . "',";
            $parent_slug = (string) ( $parents[ $slug ] ?? '' );
            if ( $parent_slug !== '' ) {
                $line .= ' // parent: ' . ws_tax_audit_php_escape( $parent_slug );
            }
            $php .= $line . "\n";
        }
        $php .= "    ],\n";
    }
    $php .= "];\n\n";

    if ( ! empty( $missing_rows ) ) {
        $php .= '$ws_taxonomy_missing_seed_terms = [' . "\n";
        foreach ( $missing_rows as $row ) {
            $taxonomy = (string) ( $row['taxonomy'] ?? '' );
            $slugs    = (array) ( $row['slugs'] ?? [] );
            if ( $taxonomy === '' || empty( $slugs ) ) {
                continue;
            }

            $php .= "    '" . ws_tax_audit_php_escape( $taxonomy ) . "' => [\n";
            foreach ( $slugs as $slug ) {
                $php .= "        '" . ws_tax_audit_php_escape( (string) $slug ) . "',\n";
            }
            $php .= "    ],\n";
        }
        $php .= "];\n";
    }

    return $php;
}

function ws_tax_audit_php_escape( $value ) {
    return str_replace( [ '\\', "'" ], [ '\\\\', "\\'" ], (string) $value );
}

function ws_tax_audit_log_dir() {
    $dir = WP_CONTENT_DIR . '/logs/ws-taxonomy-audit';
    if ( ! file_exists( $dir ) ) {
        wp_mkdir_p( $dir );
        file_put_contents( $dir . '/.htaccess', "Deny from all\n" );
    }

    return $dir;
}

function ws_tax_audit_write_php_log( $results ) {
    $dir = ws_tax_audit_log_dir();
    if ( ! file_exists( $dir ) || ! is_dir( $dir ) ) {
        return '';
    }

    // Immutable per-run log filename:
    // human-readable date-hour-minute, with a small numeric suffix only on same-minute collisions.
    $stamp = gmdate( 'Ymd-H-i' );
    $filename = 'taxonomy-audit-' . $stamp . '.php.txt';
    $path     = trailingslashit( $dir ) . $filename;
    $latest   = trailingslashit( $dir ) . 'taxonomy-audit-latest.php.txt';
    $payload  = ws_tax_audit_build_php_block( $results );

    $suffix = 1;
    while ( file_exists( $path ) ) {
        $filename = 'taxonomy-audit-' . $stamp . '-' . str_pad( (string) $suffix, 2, '0', STR_PAD_LEFT ) . '.php.txt';
        $path     = trailingslashit( $dir ) . $filename;
        $suffix++;
    }

    // Write the immutable run file once; never rewrite historical files.
    if ( @file_put_contents( $path, $payload, LOCK_EX ) === false ) {
        return '';
    }

    // Update rolling latest pointer copy for convenience.
    @file_put_contents( $latest, $payload, LOCK_EX );

    return $path;
}
