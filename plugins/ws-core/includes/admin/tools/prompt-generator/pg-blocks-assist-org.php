<?php
/**
 * pg-blocks-assist-org.php
 *
 * Prompt Generator — Assist Org Blocks
 *
 * ⚠ CONTENT NOT FULLY VERIFIED ⚠
 * -------------------------------
 * Everything below the divider is what was retrievable during this
 * rewrite pass. It is short — six lines — compared to the detail level
 * of ws_prompt_legal_schema_block()'s statute branch (which runs to
 * ~10 lines of nested field definitions). Two possibilities:
 *
 *   1. This genuinely is the whole function, and assist-org intentionally
 *      points the researcher at "the current schema contract" rather than
 *      re-typing the full field dictionary in the prompt itself (the
 *      assist-org schema is large — identity, scope_of_service, contact,
 *      eligibility, security, review — and may live in ws-schema-constants.php
 *      or a dedicated reference doc instead of being inlined here).
 *   2. This is a truncated fragment and there's more field detail that
 *      didn't surface.
 *
 * I have NOT filled in a guessed field dictionary here. Please confirm
 * against the actual file which of the two is true — if (2), paste the
 * rest and I'll fold it in without touching anything else in this file.
 *
 * @package    WhistleblowerShield
 * @since      3.13.0
 * @version    3.21.0-rewrite
 * @author     WhistleblowerShield (Dwight)
 * @link       https://whistleblowershield.org
 * @copyright  Copyright (c) Whistleblower Shield
 *
 * VERSION LOG
 * -----------
 * 3.21.0-rewrite  Docblock pass. Content preserved exactly as retrieved —
 *                 flagged as unverified rather than expanded. See note
 *                 above.
 */

defined( 'ABSPATH' ) || exit;

function ws_prompt_assist_org_schema_block(): string {
    return "--------------------------------------------------------------------------------\nFIELD DICTIONARY & SCHEMA REQUIREMENTS\n\n"
        . "Use the current assist-org schema contract and required fallbacks.\n"
        . "Use ternary fields with yes|no|unclear as applicable.\n"
        . "Use companion detail fields when sentinel terms trigger them.\n\n"
        . "JSON RECORD SHAPE\n"
        . "{\n  \"identity\": {...},\n  \"scope_of_service\": {...},\n  \"contact\": {...},\n  \"eligibility\": {...},\n  \"security\": {...},\n  \"review\": {...}\n}\n\n";
}
