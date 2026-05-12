# WS ACF Field Guidance MR (v1.0)
## Machine-Readable Field Guidance
### Expansion Rules For Pre-Compiler Normalization

---
artifact:
  id: ws_acf_field_guidance_mr
  version: "1.0"
  status: draft
  source_guidance: ws-acf-field-guidance-v1.0.md
  scope:
    include:
      - field_name_validation
      - acf_registration_name_generation
      - tab_key_generation
      - type_default_expansion
      - cardinality_expansion
      - companion_expansion
      - sister_expansion
      - repeater_expansion
      - recognition_taxonomy_expansion
      - record_type_double_role_expansion
      - sentinel_expansion
      - conditional_annotation_parsing
      - hook_annotation_parsing
      - hook_class_validation
      - inline_annotation_order_validation
    exclude:
      - query_layer_boundaries
      - render_layer_boundaries
      - public_copy_rules
      - source_verification_workflow
      - ingest_research_prompt_rules
      - runtime_php_implementation
      - editor_instruction_copy

precompiler_contract:
  purpose: expand human-efficient field specs into fully explicit field records
  output_must:
    - preserve_source_order
    - preserve_record_type_delta_origin
    - expand_all_default_types
    - expand_all_default_cardinality
    - expand_all_conditional_logic_to_ast
    - expand_all_sister_inherited_logic
    - expand_all_repeater_subfields
    - expand_all_hook_classes
    - retain_source_provenance_for_each_expansion
    - emit_no_shorthand
  output_must_not:
    - rely_on_field_name_as_only_identifier
    - leave_conditionals_as_unparsed_strings
    - drop_repeater_subfields
    - infer_domain_hooks_not_declared_or_rule_derived
    - adapt_old_field_names
    - add_compatibility_aliases
    - emit_editor_guidance_as_schema
  normalized_field_required_keys:
    - field_name
    - source_name
    - tab
    - order
    - field_type
    - storage_cardinality
    - acf_cardinality
    - conditional_logic
    - companion
    - sisters
    - repeater
    - taxonomy
    - choices
    - hooks
    - validation
    - source

agent_review_guidance:
  hook_spec_completeness:
    description: "When generating the precompiler artifact, check whether hook classes referenced in field specs have corresponding hook spec coverage. Missing coverage should be logged without making the compiler infer behavior."
    severity: warning
    agent_only: true
    missing_action: log
    applicable_to:
      - hook_annotation_rules
      - hook_class_validation
  recognition_taxonomy_definition:
    description: "Verify referenced recognition taxonomy names are explicitly defined in the domain spec. Missing definitions should be flagged for review, not inferred by the compiler."
    severity: warning
    agent_only: true
    missing_action: log

name_rules:
  canonical_spec_field_name:
    case: snake_case
    prefix_policy: prefix_free
    allowed_pattern: "^[a-z][a-z0-9_]*$|^_[a-z][a-z0-9_]*$"
    forbidden_patterns:
      - id: uppercase
        pattern: "[A-Z]"
        severity: error
      - id: kebab_case
        pattern: "-"
        severity: error
      - id: whitespace
        pattern: "\\s"
        severity: error
  choice_value:
    case: kebab_case
    allowed_pattern: "^[a-z0-9]+(?:-[a-z0-9]+)*$"
  taxonomy_term_slug:
    case: kebab_case
    allowed_pattern: "^[a-z0-9]+(?:-[a-z0-9]+)*$"
  registered_acf_name:
    generation:
      pattern: "ws_{infix}_{cpt}_{canonical_field_name_without_leading_underscore}"
      hidden_pattern: "_ws_{infix}_{cpt}_{canonical_field_name_without_leading_underscore}"
      applies_after: canonical_spec_expansion
  registered_acf_key:
    generation:
      pattern: "field_{infix}_{cpt}_{canonical_field_name_without_leading_underscore}"
      hidden_pattern: "field_{infix}_{cpt}_{canonical_field_name_without_leading_underscore}"
  acf_group_key:
    generation:
      pattern: "group_{infix}_{cpt}_metadata"
  tab_key:
    generation:
      lowercase: true
      remove_characters:
        - "&"
        - "/"
      word_joiner: "_"
      forbidden_joiners:
        - "_and_"
      suffix: "_tab"
  reserved_prefixes:
    ws_auto_:
      reserved_for: auto_stamp_workflow_fields
      written_by: hook_logic_only
      forbidden_for: content_fields

cardinality_rules:
  default_storage_cardinality: single
  default_acf_cardinality: single
  taxonomy_default:
    field_type: taxonomy
    storage_cardinality: multi
    acf_cardinality: multi
    load_terms: true
    save_terms: true
  select_default:
    field_type: select
    storage_cardinality: single
    acf_cardinality: single
  multi_select_annotation:
    storage_cardinality: multi
    acf_cardinality: multi
  repeater_default:
    storage_cardinality: multi
    acf_cardinality: repeater
    field_name_must_be_plural: true
  lexical_non_cardinality_suffixes:
    - details
    - context
    - gloss
  pluralization_to_cardinality:
    policy: explicit_declaration_required
    note: |
      field names ending in s do not automatically imply multi-cardinality;
      multi-cardinality must come from an explicit type rule, taxonomy binding,
      multi-select annotation, or repeater declaration. exceptions for specific
      plural suffixes (_ids, _sanctions, etc.) are listed in default_type_rules.
  duration_pair:
    value_suffix: "_value"
    unit_suffix: "_unit"
    unit_choices: [days, weeks, months, years]
    both_visible_together: true
    both_required_together: true
    unit_inherits_value_annotation: true

default_type_rules:
  default:
    field_type: text
    source: default_untyped_field
    audit_required: true
    audit_event: type_fell_through_to_default
  ordered_rules:
    - id: suffix_context
      match_suffix: "_context"
      field_type: textarea
      companion_role: context
    - id: suffix_details
      match_suffix: "_details"
      field_type: textarea
      companion_role: details
    - id: suffix_gloss
      match_suffix: "_gloss"
      field_type: textarea
      companion_role: gloss
    - id: prefix_has
      match_regex: "^has_"
      field_type: true_false
      boolean_role: trigger
    - id: prefix_is
      match_regex: "^is_"
      field_type: true_false
      boolean_role: state
    - id: infix_is
      match_regex: "_is_"
      field_type: true_false
      boolean_role: state
    - id: suffix_class
      match_suffix: "_class"
      field_type: select
    - id: suffix_scope
      match_suffix: "_scope"
      field_type: select
    - id: suffix_status
      match_suffix: "_status"
      field_type: select
    - id: suffix_rule
      match_suffix: "_rule"
      field_type: select
    - id: suffix_framework
      match_suffix: "_framework"
      field_type: select
    - id: suffix_weight
      match_suffix: "_weight"
      field_type: select
    - id: suffix_standard
      match_suffix: "_standard"
      field_type: select
    - id: suffix_share
      match_suffix: "_share"
      field_type: text
      value_shape: specified_portion
    - id: suffix_compare
      match_suffix: "_compare"
      field_type: select
      choices: [gte, lte, gt, lt, eq]
    - id: suffix_value
      match_suffix: "_value"
      field_type: number
    - id: suffix_unit
      match_suffix: "_unit"
      field_type: select
      choices: [days, weeks, months, years]
      enforce_choices_strictly: true
    - id: suffix_formula
      match_suffix: "_formula"
      field_type: text
      value_shape: calculation_description
    - id: suffix_sanctions
      match_suffix: "_sanctions"
      field_type: repeater
      note: convention_inferable_may_be_overridden_by_inline_declaration
    - id: suffix_application
      match_suffix: "_application"
      field_type: select
    - id: suffix_bar
      match_suffix: "_bar"
      allowed_field_types: [select, true_false]
      requires_explicit_type_when_ambiguous: true
    - id: suffix_date
      match_suffix: "_date"
      field_type: date_picker
    - id: suffix_url
      match_suffix: "_url"
      field_type: url
    - id: suffix_email
      match_suffix: "_email"
      field_type: email
    - id: suffix_id_singular
      match_suffix: "_id"
      field_type: post_object

companion_rules:
  details:
    field_suffix: "_details"
    companion_role: details
    valid_triggers:
      - trigger_boolean
      - has_details_sentinel
    trigger_boolean_name_pattern: "has_{base_field_name}"
    invalid_trigger_boolean_name_pattern: "has_{base_field_name}_details"
    conditional_required_when_not_convention_inferable: true
  context:
    field_suffix: "_context"
    companion_role: context
    cluster_anchor: true
    trigger_must_be_declared_inline: true
    preferred_trigger_field_role: recognition_taxonomy
  gloss:
    field_suffix: "_gloss"
    companion_role: gloss
    cluster_anchor: false
    trigger_must_be_declared_inline: true
    allowed_inside_cluster_for_specific_sister_value: true
  convention_inferable_shapes:
    - suffix: "_details"
      infer_when_base_trigger_exists: true
    - suffix: "_limits"
      infer_when_base_trigger_exists: true
    - suffix: "_phases"
      infer_when_base_trigger_exists: true
    - suffix: "_companions"
      infer_when_base_trigger_exists: true

sister_rules:
  annotation_form:
    literal_prefix: "Sister to "
    target_pattern: "^[a-z][a-z0-9_]*$"
  inherits_anchor_logic: true
  may_add_value_specific_gate: true
  may_add_compound_gate: true
  must_have_companion_sibling: true
  does_not_become_cluster_anchor: true
  reveal_in_proximity_required: true
  forbidden_redundant_gate:
    pattern: "conditional on sister_or_anchor_field is non-empty"
    reason: sister already inherits cluster gate

repeater_rules:
  field_name:
    must_be_plural: true
  parent_trigger_rule:
    triggered_repeater_counts_as_one_parent_field: true
    root_context_not_required: true
  row_subfields:
    prefer_single_value_subfields: true
    multi_value_subfields_require_explicit_exception: true
    required_in_precompiler_output: true
  row_context:
    default_position: last
    field_suffix: "_context"
    default_trigger: first_non_empty_row_identity_field
    fallback_trigger: primary_required_row_subfield
    no_trigger_requires_inline_exception: true
  precompiler_expansion:
    repeater_must_emit_subfields: true
    subfield_required_keys:
      - field_name
      - field_type
      - storage_cardinality
      - acf_cardinality
      - choices
      - conditional_logic
      - hooks
      - source

recognition_taxonomy_rules:
  max_per_domain: 1
  role: bool_true_on_presence_state
  slug_modes:
    standalone_state:
      companion_required: false
    cluster_anchor:
      companion_required: true
      preferred_companion_suffix: "_context"
  cluster_trigger_requirement:
    multi_field_cluster_must_anchor_on_recognition_slug: true
    core_classificatory_field_must_not_directly_anchor_doctrinal_cluster: true
    core_classificatory_field_may_require_corresponding_recognition_slug: true
  non_recognition_anchor_exception:
    allowed: true
    must_be_audited_when_multi_field_cluster: true
    omit_context_when_structured_data_is_sufficient: true
    note: |
      record-status fields (record self-description) may anchor multi-field
      clusters without migrating to the recognition taxonomy. doctrinal-state
      fields cannot.
  prerequisite_markers:
    P:
      meaning: prerequisite_slug_required
    P_plus:
      meaning: paired_or_mutual_requirement
      must_cross_document_in_hook_spec: true

record_type_double_roles:
  status: explicitly_approved
  pattern: |
    the same canonical meta key may carry record-type-specific behavior when
    the underlying concept is identical but the structural requirements of
    each cpt differ. example: review_standard acts as a sister field in
    substantive records and as a standalone field in precedent records.
  precompiler_handling:
    emit_per_cpt: true
    record_type_origin_must_be_preserved_in_source_provenance: true
    sister_relationship_may_vary_by_cpt: true
    do_not_force_uniform_role_across_cpts: true
  validation:
    same_key_different_type_is_error: true
    same_key_different_role_is_allowed: true

sentinel_rules:
  trigger_sentinels:
    has-details:
      reveals_companion_suffix: "_details"
      use_when_no_visible_context_or_gloss_can_carry_nuance: true
    has_prefix:
      pattern: "^has-"
      taxonomy_terms_must_nest_under: "has-parent"
  redirect_sentinels:
    see-context:
      reveals_field: false
      points_to_companion_suffix: "_context"
    see-gloss:
      reveals_field: false
      points_to_companion_suffix: "_gloss"
  ambiguous_values:
    forbidden_active_choices:
      - other
      - unclear
    discouraged_choices:
      mixed:
        allowed_only_when_value_is_meaningful_classification: true
      varies:
        allowed_only_when_value_is_meaningful_classification: true
  umbrella_values:
    suffix: "-only"
    applies_to_multi_value_fields: true
    excludes_granular_siblings: true
    sentinels_are_not_granular_siblings: true

conditional_grammar:
  output_ast_required: true
  accepted_forms:
    - id: taxonomy_term_present
      text_pattern: "conditional on {slug} in {taxonomy_field}"
      ast:
        op: term_present
        field: "{taxonomy_field}"
        value: "{slug}"
    - id: taxonomy_term_absent
      text_pattern: "conditional on {slug} absent in {taxonomy_field}"
      ast:
        op: term_absent
        field: "{taxonomy_field}"
        value: "{slug}"
    - id: taxonomy_field_non_empty
      text_pattern: "conditional on {taxonomy_field} is non-empty"
      ast:
        op: field_non_empty
        field: "{taxonomy_field}"
    - id: field_non_empty
      text_pattern: "conditional on {trigger_field} is non-empty"
      ast:
        op: field_non_empty
        field: "{trigger_field}"
    - id: select_value_is
      text_pattern: "conditional on {trigger_field} is {trigger_value}"
      ast:
        op: value_is
        field: "{trigger_field}"
        value: "{trigger_value}"
    - id: select_value_is_not
      text_pattern: "conditional on {trigger_field} is NOT {trigger_value}"
      ast:
        op: value_is_not
        field: "{trigger_field}"
        value: "{trigger_value}"
    - id: multi_select_includes
      text_pattern: "conditional on {trigger_field} includes {trigger_value}"
      ast:
        op: value_includes
        field: "{trigger_field}"
        value: "{trigger_value}"
    - id: child_taxonomy_value_present
      text_pattern: "conditional on any child-slug of {parent_slug} in {taxonomy_field}"
      ast:
        op: child_term_present
        field: "{taxonomy_field}"
        parent: "{parent_slug}"
    - id: pseudo_class_child_taxonomy_value_present
      text_pattern: "conditional on any {pseudo_class} in {taxonomy_field}"
      ast:
        op: pseudo_class_present
        field: "{taxonomy_field}"
        pseudo_class: "{pseudo_class}"
      note: |
        pseudo_class is a literal label preserved by the pre-compiler; the
        actual member-slug resolution is performed by the hook implementation
        layer, not the pre-compiler. a registry of accepted pseudo_class labels
        belongs in the domain hook spec.
    - id: boolean_true
      text_pattern: "conditional on {bool_field} is true"
      ast:
        op: bool_is
        field: "{bool_field}"
        value: true
    - id: boolean_false
      text_pattern: "conditional on {bool_field} is false"
      ast:
        op: bool_is
        field: "{bool_field}"
        value: false
  compound_operators:
    allowed: [AND, OR, NOT]
    ast_required: true
  shorthand_policy:
    any_slug_word_allowed_in_prose_only: true
    emitted_condition_must_use_field_non_empty: true
  absent_conditionals:
    imply_and_not_empty: true
  validation:
    referenced_field_must_exist: true
    referenced_taxonomy_slug_must_exist_when_taxonomy_known: true
    conditionals_must_not_remain_as_strings_in_precompiler_output: true

hook_annotation_rules:
  inline_form:
    prefix: "hook:"
    separator: ","
    classes_only: true
    must_be_last_delta: true
    prose_disallowed: true
    purpose: structural_labels_for_compiler_and_downstream_implementers
    not_editor_facing: true
  allowed_classes:
    filter:
      meaning: restricts_selection_choices_based_on_external_data_or_record_context
    verify:
      meaning: validates_data_against_database_taxonomy_relationship_or_cross_reference
    derive:
      meaning: computes_or_synchronizes_scalar_value_usually_for_hidden_fields_or_derived_public_helpers
    merge:
      meaning: computes_or_synchronizes_array_or_aggregation_usually_for_hidden_relationship_fields
    butchers:
      meaning: ruthlessly_overwrites_existing_value_with_system_generated_data
      stronger_than: derive
      supersedes_in_override_pass: derive
    temporal:
      meaning: validates_date_or_time_relationships_including_impossible_chronology_and_future_date_review
    stale-monitor:
      meaning: detects_and_flags_values_no_longer_valid_after_controlling_field_changes
    required:
      meaning: enforces_conditional_requiredness_including_R_slug_map_requirements
    prerequisite:
      meaning: enforces_selected_value_requires_another_value_in_same_field_or_another_field
    paired:
      meaning: enforces_value_pair_rules_where_two_values_or_fields_must_travel_together
    excludes:
      meaning: enforces_mutual_exclusivity_or_cluster_blocking_caused_by_this_field_or_value
    excluded-by:
      meaning: marks_value_or_field_cluster_blocked_by_another_selected_value
    impacts:
      meaning: directly_triggers_visibility_state_or_downstream_validation_changes
    umbrella:
      meaning: handles_only_suffix_excluding_granular_sibling_values_in_same_multi_value_field
      infer_from_choice_suffix: "-only"
      inference_requires:
        storage_cardinality: multi
    negation:
      meaning: enforces_rules_for_explicit_negation_values_where_empty_cannot_express_result
      infer_from_values:
        - none
        - "none-*"
        - "no-*"
      inference_requires:
        storage_cardinality: multi
      side_effect_when_inferred:
        set_field_required: true
    auto-set:
      meaning: sets_field_based_on_explicit_editorial_intent_expressed_elsewhere_in_record
    override:
      meaning: allows_documented_exception_logic_to_bypass_standard_classification_or_exclusion_rules
      requires_spec_side_case_statement: true
      formal_description_intentionally_absent: true
  validation:
    unknown_hook_class:
      severity: error
      reason: hook_classes_must_appear_in_allowed_classes_registry
    hook_before_other_delta:
      severity: error
      reason: hook_annotation_must_be_last_delta_in_inline_definition
    hook_with_prose:
      severity: error
      reason: hook_annotations_must_not_explain_behavior_in_prose
    butchers_and_derive_both_present:
      action: remove_derive
      reason: butchers_is_stronger_form_of_derive

inline_annotation_rules:
  delimiter: ";"
  allowed_delta_kinds:
    - field_shape
    - select_choices
    - taxonomy_binding
    - repeater_structure
    - sister_relationship
    - conditional_logic
    - explicit_approval_or_exception
    - hook_classes
  forbidden_delta_kinds:
    - editor_data_entry_guidance
    - domain_tutorial
    - public_copy
  ordering:
    hook_classes_must_be_last: true
    conditional_before_hook: true
    sister_before_hook: true
    repeater_structure_before_hook: true
    shape_before_hook: true
    approval_before_hook: true
  examples:
    correct:
      - "field_name — (Sister to field_context; select: a|b; hook: required)"
      - "hidden_field — (select: @hook; hook: filter, derive)"
    incorrect:
      - "field_name — (hook: required; Sister to field_context; select: a|b)"
      - "field_name — (hook: required because editors must fill this when the doctrine is active)"

...
