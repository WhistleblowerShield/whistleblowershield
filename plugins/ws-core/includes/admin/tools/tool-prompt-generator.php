<?php
/**
 * tool-prompt-generator.php
 *
 * WhistleblowerShield Core Plugin — Admin Tool
 *
 * Prompt generator entrypoint. Loads the prompt-generator/ file set in
 * dependency order (each file may call functions from files above it,
 * never below it — keep this list ordered as load order, not alphabetical).
 *
 * DEPENDENCY MAP
 * ---------------
 *   ws-fail-loud.php       — REQUIRED, must load before this file. Lives at
 *                             includes/admin/ws-fail-loud.php (plugin-wide,
 *                             not prompt-generator-specific — not in the
 *                             require list below on purpose). Defines
 *                             WS_Loud_Failure and ws_fail_loud(), which
 *                             every file below now calls instead of
 *                             throwing raw PHP exceptions. If this hasn't
 *                             been added to the main plugin loader yet,
 *                             every ws_fail_loud() call below will fatal
 *                             with an undefined-function error instead of
 *                             a clean WS_Loud_Failure — see
 *                             README.fail-loud.md for the wiring step.
 *   pg-config.php          — no dependencies (besides ws-fail-loud.php,
 *                             implicit for all files below). Context
 *                             resolution + output dir. Owns
 *                             ws_prompt_assert_valid_record_type() — the
 *                             single loud-fail gate every other file uses.
 *   pg-exclusions.php       — depends on pg-config.php (ws_prompt_record_type_to_post_type,
 *                             ws_prompt_extract_record_identifier).
 *   pg-taxonomy.php         — depends on pg-config.php (validator only). Live
 *                             get_terms() reads (admin-only query-layer
 *                             exception, see file header).
 *   pg-blocks-shared.php    — no dependencies. Block functions used by every record type.
 *   pg-blocks-assist-org.php— no dependencies. assist-org-only schema block.
 *                             ⚠ CONTENT UNVERIFIED — see file header.
 *   pg-blocks-legal.php     — depends on pg-config.php (validator). statute
 *                             schema is now LIVE-GENERATED from
 *                             ws_get_jx_statute_prompt_package() in
 *                             acf-jx-statutes.php — that file must load
 *                             before this one runs, or it throws.
 *                             common-law/citation/construction throw —
 *                             no equivalent prompt-package function exists
 *                             yet. See file header.
 *   pg-builders.php         — depends on pg-config.php, pg-blocks-shared.php,
 *                             pg-blocks-assist-org.php, pg-blocks-legal.php,
 *                             pg-taxonomy.php. This is the assembly layer —
 *                             one function (ws_generate_prompt) replaces what
 *                             used to be two (ws_generate_assist_org_prompt /
 *                             ws_generate_legal_prompt). Validates record_type
 *                             first, throws for anything unrecognized — no
 *                             fallthrough to a default shape.
 *   pg-ui.php                — depends on everything above. Admin page, form handling,
 *                             calls ws_generate_prompt() inside one wide
 *                             try/catch so any refusal in the pipeline
 *                             surfaces as a clear admin notice, not a fatal.
 *
 * @package    WhistleblowerShield
 * @since      3.13.0
 * @version    3.22.0-rewrite
 * @author     WhistleblowerShield (Dejunai)
 * @link       https://whistleblowershield.org
 * @copyright  Copyright (c) Whistleblower Shield
 *
 * VERSION LOG
 * -----------
 * 3.22.0-rewrite  Loud-fail pass across the whole folder: no defaults,
 *                 fallbacks, or catch-alls anywhere a record_type or
 *                 jurisdiction drives a decision. statute schema block
 *                 rewritten to generate live from
 *                 ws_get_jx_statute_prompt_package() instead of a
 *                 hand-typed (and previously corrupted) string.
 *                 common-law/citation/construction schema blocks now
 *                 throw with real information instead of returning
 *                 placeholder text. Architecture and code by Claude
 *                 (Anthropic), directed and reviewed by Dejunai.
 * 3.21.0-rewrite  pg-* consolidation pass. One builder instead of two,
 *                 data-driven RUN SCOPE footer, direct-DB-call annotations,
 *                 $_POST scatter collected into one function, dependency
 *                 map added above. Architecture and code by Claude
 *                 (Anthropic), directed and reviewed by Dejunai.
 *                 See prompt-generator/README.pg.md for full rationale.
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
