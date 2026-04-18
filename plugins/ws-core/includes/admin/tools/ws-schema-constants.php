<?php
/**
 * ws-schema-constants.php
 *
 * WhistleblowerShield Core Plugin — Schema Constants
 *
 * PURPOSE
 * -------
 * Single authoritative source for controlled vocabulary sets used across
 * the ingest pipeline, prompt generator, and any future validation layer.
 *
 * These are NOT WordPress taxonomies. They are finite, enumerated values
 * that appear in JSON schema fields and are validated at ingest time.
 * They do not drive tax_query filtering and do not need term registration.
 *
 * WHEN TO ADD A CONSTANT HERE
 * ---------------------------
 * A value set belongs here when ALL of the following are true:
 *   - It is a finite enumerated list (not free text)
 *   - It appears in the JSON ingest schema as a valid value constraint
 *   - It applies to more than one CPT or pipeline stage
 *   - It is not already a registered WordPress taxonomy term set
 *
 * A value set does NOT belong here when:
 *   - It is a registered taxonomy (those live in register-taxonomies.php)
 *   - It is a single-CPT implementation detail with no shared consumers
 *   - It is a UI label rather than a stored value
 *
 * USAGE
 * -----
 * Ingest tool:      ws_schema_allowed( WS_SCHEMA_PHONE_TYPE )
 * Prompt generator: WS_SCHEMA_PHONE_TYPE (reference for template output)
 * Future tools:     same pattern — reference the constant, never hardcode
 *
 * LOAD ORDER
 * ----------
 * Loaded in the Admin Layer by loader.php alongside other tool files.
 * Must load before tool-ingest.php and tool-generate-prompt.php.
 *
 * @package    WhistleblowerShield
 * @since      3.16.0
 * @version    3.16.0
 * @author     Whistleblower Shield
 * @link       https://whistleblowershield.org
 * @copyright  Copyright (c) Whistleblower Shield
 *
 * VERSION LOG
 * -----------
 * 3.16.0  Initial release.
 *         WS_SCHEMA_PHONE_TYPE          — phone scope for ws-assist-org and ws-agency
 *         WS_SCHEMA_EMAIL_TYPE          — email scope for ws-assist-org and ws-agency
 *         WS_SCHEMA_URL_STATUS          — homepage_url_status across all research CPTs
 *         WS_SCHEMA_TRI_STATE           — yes | no | unclear for boolean-ish required fields
 *         WS_SCHEMA_SOURCE_METHOD       — source_method values (mirrors WS_SOURCE_* constants)
 *         WS_SCHEMA_JSON_FORMAT_VERSION — current accepted ingest schema version sentinel
 *         WS_SCHEMA_RECORD_TYPE         — valid record_type values for ingest batches
 *         WS_SCHEMA_WHISTLEBLOWER_SCOPE — 0–3 integer range for assist-org scope field
 */

defined( 'ABSPATH' ) || exit;
define( 'WS_SCHEMA_JSON_FORMAT_VERSION', "2.0" );

// ════════════════════════════════════════════════════════════════════════════
// Phone Type
//
// Controlled vocabulary for the `type` key in the `phones` array on
// ws-assist-org and ws-agency records. Applies to both CPTs — do not
// maintain a separate copy per CPT.
//
// 'other' is a valid fallback but requires a companion note in the
// prompt schema (same pattern as has-details sentinel in taxonomies).
// The ingest tool warns when 'other' is used without a sibling note field.
// ════════════════════════════════════════════════════════════════════════════

define( 'WS_SCHEMA_PHONE_TYPE', [
    'hotline',       // Primary user-facing line; may be after-hours staffed
    'intake',        // Business-hours case intake; may route to a coordinator
    'headquarters',  // Administrative main line; rarely appropriate for Maya/James
    'regional',      // Geographic branch or field office line
    'tty',           // TTY/TDD accessibility line
    'fax',           // Fax; still relevant in legal/administrative contexts
    'other',         // Fallback — requires note in prompt _review_notes
] );


// ════════════════════════════════════════════════════════════════════════════
// Email Type
//
// Controlled vocabulary for the `type` key in the `emails` array on
// ws-assist-org and ws-agency records.
// ════════════════════════════════════════════════════════════════════════════

define( 'WS_SCHEMA_EMAIL_TYPE', [
    'intake',
    'general',
    'legal',
    'media',
    'support',
    'secure', // For secure email addresses that may require special handling
    'other',
] );


// ════════════════════════════════════════════════════════════════════════════
// URL Status
//
// Valid values for homepage_url_status and any other URL status fields
// across all research record types (assist-org, agency, statute, etc.).
// ════════════════════════════════════════════════════════════════════════════

define( 'WS_SCHEMA_URL_STATUS', [
    'verified',    // Researcher confirmed URL resolves and content matches
    'redirects',   // URL resolves but redirects — destination should be noted
    'unverified',  // URL not confirmed; record may still be included with caveat
    'dead',        // URL no longer resolves at all
] );


// ════════════════════════════════════════════════════════════════════════════
// Tri-State
//
// Valid values for required boolean-ish fields that must allow an honest
// "cannot confirm" answer without failing the record:
//   anonymous_pre_consult_possible
//   has_attorneys
//   income_eligibility_required
//   has_secure_channel
//   has_secure_drop
//
// 'unclear' is coerced to 0 (false) at ingest with a warning logged.
// The coercion is intentional — ingest must write something; the warning
// surfaces the ambiguity for the human reviewer.
// ════════════════════════════════════════════════════════════════════════════

define( 'WS_SCHEMA_TRI_STATE', [
    'yes',
    'no',
    'unclear',
] );


// ════════════════════════════════════════════════════════════════════════════
// Source Method
//
// Valid values for meta.source_method in ingest batches.
// Mirrors the WS_SOURCE_* plugin constants defined in ws-core.php.
// Defined here separately so this file has no dependency on ws-core.php
// load order — tool files load after the universal layer but this constant
// is useful for validation without re-importing the plugin constant.
// ════════════════════════════════════════════════════════════════════════════

define( 'WS_SCHEMA_SOURCE_METHOD', [
    'ai_assisted',    // Created or substantially drafted with AI assistance
    'human_created',  // Created directly by a human editor
    'matrix_seed',    // Created by a matrix seeder at install
    'bulk_import',    // Created via a structured import process
    'feed_import',    // Created via the Inoreader legislative feed monitor
] );


// ════════════════════════════════════════════════════════════════════════════
// Record Type
//
// Valid values for meta.record_type in ingest batches.
// The ingest tool detects record type from this field or falls back to
// first-record shape detection. Both paths validate against this set.
// ════════════════════════════════════════════════════════════════════════════

define( 'WS_SCHEMA_RECORD_TYPE', [
    'statute',
    'common-law',
    'citation',
    'interpretation',
    'assist-org',
    'agency',
    'procedure',
    'reference',
] );


// ════════════════════════════════════════════════════════════════════════════
// Whistleblower Scope
//
// Valid integer values for the whistleblower_scope field on assist-org
// records. Represents how directly the org serves whistleblowers:
//   0 — Not applicable / no meaningful whistleblower service
//   1 — Adjacent; may help in specific edge cases
//   2 — Serves whistleblowers among broader constituency
//   3 — Whistleblower-primary or whistleblower-specialist org
//
// Stored as integer. The ingest tool casts incoming values to int and
// validates range — out-of-range values are flagged as warnings.
// ════════════════════════════════════════════════════════════════════════════

define( 'WS_SCHEMA_WHISTLEBLOWER_SCOPE_MIN', 0 );
define( 'WS_SCHEMA_WHISTLEBLOWER_SCOPE_MAX', 3 );


// ════════════════════════════════════════════════════════════════════════════
// Secure Contact Tool
//
// Known secure channel and drop tool identifiers for the secure_contact_tool
// field on assist-org and agency records. This list is not exhaustive —
// 'other' is always valid — but covers the tools researchers are most
// likely to encounter. Used for display normalization in the render layer.
// ════════════════════════════════════════════════════════════════════════════

define( 'WS_SCHEMA_SECURE_TOOL', [
    'SecureDrop',    // Freedom of the Press Foundation SecureDrop
    'Signal',        // Signal encrypted messaging
    'ProtonMail',    // ProtonMail encrypted email
    'Tutanota',      // Tutanota encrypted email
    'Wire',          // Wire encrypted messaging
    'Keybase',       // Keybase encrypted messaging/file sharing
    'other',         // Fallback — describe in _review_notes
] );


// ════════════════════════════════════════════════════════════════════════════
// Helper: ws_schema_allowed()
//
// Returns the allowed values array for a given schema constant.
// Accepts the constant value directly (pass the constant, not its name).
// Returns [] when the constant is not a recognized schema array.
//
// Usage:
//   if ( ! in_array( $value, ws_schema_allowed( WS_SCHEMA_PHONE_TYPE ), true ) ) { ... }
// ════════════════════════════════════════════════════════════════════════════

/**
 * Returns the allowed values array for a schema constant.
 *
 * @param  mixed $constant  The schema constant value (e.g. WS_SCHEMA_PHONE_TYPE).
 * @return array            Allowed values, or empty array if not recognized.
 */
function ws_schema_allowed( $constant ): array {
    if ( ! is_array( $constant ) ) {
        return [];
    }
    return $constant;
}


/**
 * Validates a single value against an allowed schema constant.
 *
 * Returns true when the value is in the allowed set.
 * Case-sensitive — schema values are always lowercase slugs.
 *
 * @param  string $value     Value to validate.
 * @param  array  $constant  Schema constant array (e.g. WS_SCHEMA_PHONE_TYPE).
 * @return bool
 */
function ws_schema_valid( string $value, array $constant ): bool {
    return in_array( $value, $constant, true );
}
