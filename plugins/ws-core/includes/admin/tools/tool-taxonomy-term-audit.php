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
 * @version 3.14.5
 */

defined( 'ABSPATH' ) || exit;

add_action( 'admin_menu', 'ws_register_taxonomy_term_audit_page' );

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

            <h2>Protected Class Spot Check</h2>
            <p>
                all-sectors:
                <strong><?php echo ! empty( $results['protected_class']['all-sectors'] ) ? 'present' : 'missing'; ?></strong>
                |
                all-employees:
                <strong><?php echo ! empty( $results['protected_class']['all-employees'] ) ? 'present' : 'missing'; ?></strong>
            </p>

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

function ws_tax_audit_render_rows_table( $rows ) {
    echo '<table class="widefat striped" style="max-width: 1200px;">';
    echo '<thead><tr><th style="width:260px;">Taxonomy</th><th>Slugs</th></tr></thead><tbody>';
    foreach ( $rows as $row ) {
        echo '<tr>';
        echo '<td><code>' . esc_html( (string) $row['taxonomy'] ) . '</code></td>';
        echo '<td>' . esc_html( implode( ', ', (array) $row['slugs'] ) ) . '</td>';
        echo '</tr>';
    }
    echo '</tbody></table>';
}

function ws_tax_audit_run() {
    $seed_map    = ws_tax_audit_get_seed_map();
    $live_map    = ws_tax_audit_get_live_map();
    $live_labels = ws_tax_audit_get_live_label_map();

    $all_taxonomies = array_values( array_unique( array_merge( array_keys( $seed_map ), array_keys( $live_map ) ) ) );
    sort( $all_taxonomies );

    $extra_rows   = [];
    $missing_rows = [];

    foreach ( $all_taxonomies as $taxonomy ) {
        $seed_slugs = isset( $seed_map[ $taxonomy ] ) ? array_values( array_unique( $seed_map[ $taxonomy ] ) ) : [];
        $live_slugs = isset( $live_map[ $taxonomy ] ) ? array_values( array_unique( $live_map[ $taxonomy ] ) ) : [];

        sort( $seed_slugs );
        sort( $live_slugs );

        $extra   = array_values( array_diff( $live_slugs, $seed_slugs ) );
        $missing = array_values( array_diff( $seed_slugs, $live_slugs ) );

        if ( ! empty( $extra ) ) {
            $extra_rows[] = [
                'taxonomy' => $taxonomy,
                'slugs'    => $extra,
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
        'live_labels'     => $live_labels,
        'protected_class' => [
            'all-sectors'   => in_array( 'all-sectors', $live_map['ws_protected_class'] ?? [], true ),
            'all-employees' => in_array( 'all-employees', $live_map['ws_protected_class'] ?? [], true ),
        ],
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
    if ( preg_match( "/\\$taxonomy\\s*=\\s*'((?:ws_)[a-z0-9_]+)'/", $body, $m ) ) {
        return (string) $m[1];
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

    if ( preg_match_all( "/'slug'\\s*=>\\s*'([a-z0-9-]+)'/", $body, $matches ) ) {
        $slugs = array_merge( $slugs, $matches[1] );
    }

    if ( preg_match_all( "/'([a-z0-9-]+)'\\s*=>\\s*'/", $body, $matches ) ) {
        foreach ( (array) $matches[1] as $key ) {
            if ( $key === 'name' || $key === 'children' ) {
                continue;
            }
            $slugs[] = $key;
        }
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
        if ( $taxonomy === '' || empty( $slugs ) ) {
            continue;
        }

        $php .= "    '" . ws_tax_audit_php_escape( $taxonomy ) . "' => [\n";
        foreach ( $slugs as $slug ) {
            $slug = (string) $slug;
            $name = (string) ( $labels_map[ $taxonomy ][ $slug ] ?? $slug );
            $php .= "        '" . ws_tax_audit_php_escape( $slug ) . "' => '" . ws_tax_audit_php_escape( $name ) . "',\n";
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

    $stamp    = gmdate( 'Ymd-His' );
    $filename = 'taxonomy-audit-' . $stamp . '.php.txt';
    $path     = trailingslashit( $dir ) . $filename;
    $latest   = trailingslashit( $dir ) . 'taxonomy-audit-latest.php.txt';
    $payload  = ws_tax_audit_build_php_block( $results );

    @file_put_contents( $path, $payload );
    @file_put_contents( $latest, $payload );

    return $path;
}
