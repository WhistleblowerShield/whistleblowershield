<?php
/**
 * tool-prompt-generator.php
 *
 * WhistleblowerShield Core Plugin - Admin Tool
 *
 * Prompt generator entrypoint.
 */

defined( 'ABSPATH' ) || exit;

$ws_pg_dir = __DIR__ . '/prompt-generator';
$ws_pg_files = [
    'pg-config.php',
    'pg-exclusions.php',
    'pg-taxonomy.php',
    'pg-blocks-shared.php',
    'pg-blocks-assist-org.php',
    'pg-blocks-legal.php',
    'pg-builders.php',
    'pg-ui.php',
];

foreach ( $ws_pg_files as $ws_pg_file ) {
    require_once $ws_pg_dir . '/' . $ws_pg_file;
}

