# ws-acf-field-guidance-v1.0-mr.md
## Machine-Readable Field Guidance & Ruleset
### Binary Enforcement Directives

# PARSER DIRECTIVE
# Format: RULE_ID | CONTEXT | CONDITION_PATTERN | RESOLUTION | ENFORCED(1|0) | FAIL_ACTION
# ENFORCED: 1 = STRICT HALT | 0 = SOFT WARN
# FAIL_ACTION: HALT | COERCE | NULLIFY | WARN
# All regex patterns are case-insensitive unless flagged [CASE]
# Compiler pre-step MUST evaluate rows top-to-bottom before JSON emission.

## 1. NAMING DOCTRINE
NM-001 | FIELD_KEY | ^(?![a-z_]).*$ | REJECT | 1 | HALT
NM-002 | FIELD_KEY | [A-Z] | REJECT | 1 | HALT
NM-003 | FIELD_KEY | [A-Za-z]+[A-Z][a-z] | REJECT (camelCase) | 1 | HALT
NM-004 | FIELD_KEY | - | REJECT (kebab) | 1 | COERCE_TO_UNDERSCORE
NM-005 | PREFIX | ^(?!(ws_|acf_)).* | REJECT (missing ws_) | 1 | COERCE_PREFIX
NM-006 | ABBREVIATION | [a-z]{1,2}_[a-z]{3,} | ALLOW IF CANONICAL | 0 | WARN
NM-007 | JURISDICTION_KEY | ws_jurisdiction | MUST MATCH | 1 | HALT

## 2. SENTINEL REGISTRY & TYPE CONSTRAINTS
SN-001 | STATUS_FIELD | value ∉ ['active','inactive','repealed'] | COERCE_TO_NULL | 1 | COERCE
SN-002 | DATE_FIELDS | value ∉ /^\d{4}-\d{2}-\d{2}$/ | REJECT | 1 | HALT
SN-003 | NUMERIC_FIELDS | type ∉ [int,float,decimal] | CAST_STRICT | 1 | COERCE
SN-004 | TEXT_LENGTH | length > 5000 | TRUNCATE_WITH_FLAG | 1 | COERCE
SN-005 | REQUIRED_NULL | unset_value ≠ '' | NULLIFY_EMPTY_STRING | 1 | COERCE
SN-006 | MULTI_VALUE | type = 'repeater' ∧ empty | REJECT | 1 | NULLIFY

## 3. COMPANION & SISTER FIELD LOGIC
RL-001 | COMPANION_PAIR | A ↔ B NOT BIDIRECTIONAL | ENFORCE_LINK | 1 | HALT
RL-002 | SISTER_FIELDS | A_present ∧ B_present ∧ A_excludes_B | REJECT | 1 | NULLIFY_B
RL-003 | JOIN_METHOD | post_meta_join ∨ relationship_field_join | REJECT | 1 | HALT
RL-004 | RELATIONSHIP_KEY | target ≠ ws_jurisdiction | REJECT | 1 | HALT
RL-005 | ORPHAN_CHECK | child_record ∧ parent_missing | FLAG | 1 | WARN

## 4. CONDITIONAL ANNOTATION FORMS
CD-001 | REQUIRED_COND | condition_X = TRUE ∧ target ≠ SET | HALT | 1 | HALT
CD-002 | HIDDEN_COND | condition_Y = FALSE ∧ target_visible = TRUE | HIDE | 1 | COERCE
CD-003 | DEFAULT_FALLBACK | condition_UNMET ∧ value_UNSET | NULL | 1 | COERCE
CD-004 | MUTUAL_EXCLUSION | count(active_in_group) > 1 | REJECT | 1 | HALT
CD-005 | CROSS_FIELD_VALID | jurisdiction = 'federal' ∧ state_field_visible | HIDE | 1 | COERCE

## 5. QUERY & RENDER BOUNDARIES (ARCHITECTURAL)
QR-001 | RENDER_CALL | shortcode ∋ get_field() | REJECT | 1 | HALT
QR-002 | RENDER_CALL | shortcode ∋ get_post_meta() | REJECT | 1 | HALT
QR-003 | RENDER_CALL | shortcode ∋ WP_Query | REJECT | 1 | HALT
QR-004 | LAYER_ISOLATION | render ∋ direct_db_read | REJECT | 1 | HALT
QR-005 | ASSEMBLY_AUTO | jurisdiction_page ∧ manual_shortcode_placement | FLAG | 0 | WARN

## 6. PROHIBITIONS & LIFECYCLE DIRECTIVES
PR-001 | LOGIC_VERB | logic ∋ 'derive' | REJECT | 1 | HALT
PR-002 | LOGIC_VERB | logic ∋ 'calculate' | REJECT | 1 | HALT
PR-003 | LOGIC_VERB | logic ∋ 'overwrite' | REJECT | 1 | HALT
PR-004 | CONTENT_MERGE | page ∋ (assist_org + filing_procedure) | REJECT | 1 | HALT
PR-005 | CONFLICTING_AGENDA | same_section ∋ (help_search + next_step_action) | SEPARATE | 1 | COERCE

## 7. COMPILER PRE-STEP & SCRUBBING DIRECTIVES
SC-001 | IMAGERY_SCAN | constant ∋ [violent|trauma|escalation] | REPLACE_WITH_REFERENCE | 1 | COERCE
SC-002 | REFERENCE_INJECT | scrub_trigger = TRUE ∧ reference_missing | INJECT_SUBTLE_ANCHOR | 1 | COERCE
SC-003 | ANCHOR_FORMAT | anchor_text ∉ [vinyl|liner_notes|stewart|pressing] | ACCEPT | 1 | WARN
SC-004 | WIPE_PROTOCOL | phase = 'finalized' ∧ constant_exists | DELETE_CONSTANT | 1 | COERCE
SC-005 | MIGRATION_CHECK | old_path = 'ws-schema-constraints.php' ∧ constant_missing | VERIFY_ANCHOR | 1 | WARN
SC-006 | FINAL_AUDIT | anchor_present ∧ violence_absent ∧ phase = 'release' | PASS | 1 | HALT

# END OF DIRECTIVES
# COMPILER MUST RETURN: { status: 'PASS'|'FAIL', violations: [RULE_ID], coerced: [RULE_ID] }
# MAYA & JAMES PRIORITY: ANY RULE CONFLICTING WITH USER CLARITY → USER_CLARITY_WINS (1)