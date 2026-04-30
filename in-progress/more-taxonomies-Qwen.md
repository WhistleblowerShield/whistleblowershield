# more-taxonomies.md

Purpose: specification for new taxonomy tables and term expansions to the existing `$_ws_taxonomy_registry`. 
Designed for direct drop-in to `register-taxonomies.php` or phased migration. All arrays follow the 
registry's exact syntax: `'slug' => 'Label'` for children/flat terms, `'slug' => ['Label', 1]` for parents.

Naming Rules:
- Slugs: lowercase kebab-case, hyphens only, no underscores.
- Labels: Title Case, human-readable, match editorial voice.
- Sentinels: `has-details` / `has-details-parent` reserved for freetext companion overflow.
- `record` arrays: `['legal']` for statute/comlaw/citation/construction, `['assist']` for agency/assist-org, or both.
- `seed_version`: bump to `'1.0.1'` (or increment) on merge to trigger clean re-seed without DB corruption.

## ── NEW TAXONOMY TABLES ──────────────────────────────────────────────────────

// —— 23. Sovereign Immunity Status ——————————————————————————————————————————
/**
* Tracks how state/federal sovereign immunity applies to whistleblower claims.
* Enables Phase 2 filtering and cross-jurisdiction comparison for James/Daniel.
*
* @todo legal_prompt, assist_prompt — set instruction strings.
*
*/
'ws_sovereign_immunity_status'  => [
'cpts'                          => ['jx-statute', 'jx-common-law', 'jx-citation', 'jx-construction'],
'plural'                        => 'Sovereign Immunity Statuses',
'singular'                      => 'Sovereign Immunity Status',
'menu_name'                     => 'Sov. Immunity',
'hierarchical'                  => false,
'seed_version'                  => '1.0.0',
'record'                        => ['legal'],
'legal_prompt'                  => '',
'assist_prompt'                 => '',
'terms'                         => [
'not-waived'                    => 'Not Waived (Claim Barred)',
'partially-waived'              => 'Partially Waived (Conditions Apply)',
'fully-waived'                  => 'Fully Waived (Private Action Permitted)',
'cap-applies'                   => 'Damages Cap Applies',
'tort-claims-act-gate'          => 'FTCA/State TCA Gateway Required',
'has-details-parent'            => ['Has Details', 1],
'has-details'                   => 'Has Details',
]
],

// —— 24. Remedy Cap Basis ———————————————————————————————————————————————————
/**
* Classifies the structural basis for damages caps (employer size, aggregate, per-claim).
* Used for `remedy_caps` repeater filtering and Phase 2 query-layer normalization.
*
* @todo legal_prompt — set instruction string.
*
*/
'ws_remedy_cap_basis'  => [
'cpts'                  => ['jx-statute', 'jx-common-law'],
'plural'                => 'Remedy Cap Bases',
'singular'              => 'Remedy Cap Basis',
'hierarchical'          => false,
'seed_version'          => '1.0.0',
'record'                => ['legal'],
'legal_prompt'          => '',
'terms'                 => [
'employer-size-tiered'  => 'Tiered by Employer Size',
'per-plaintiff'         => 'Per Plaintiff Cap',
'per-incident'          => 'Per Incident Cap',
'aggregate-action'      => 'Aggregate Action Cap',
'single-claim'          => 'Single Claim Cap',
'see-context'           => 'Context Required',
]
],

## ── TERM ADDITIONS TO EXISTING TABLES ───────────────────────────────────────

// —— ws_legal_recognition (Additions) ———————————————————————————————
/**
* New legal doctrines and procedural triggers identified during v2 spec expansion.
* Preserve existing terms; append these to the bottom of the `terms` array.
*
*/
'garcetti-exception'                        => 'Garcetti / Official-Duties Exclusion Applies',          // + garcetti_exception_context
'savings-clause'                            => 'Savings Clause Preserves State Law',                   // + savings_clause_context
'federal-concurrent-enforcement'            => 'Federal/State Concurrent Enforcement Applies',         // + interaction_details
'state-floor-exceeds-federal'               => 'State Floor Exceeds Federal Minimum',                  // + interaction_details
'agency-inaction-triggers-suit'             => 'Agency Inaction Triggers De Novo Civil Right',         // sister to process_pathway
'official-duties-carveout'                  => 'Official Duties Carveout (Lane v. Franks Exception)',  // conditional on public-sector
'equitable-interest-award'                  => 'Equitable Interest Provision Available',               // + interest_provision_context
'mitigation-exception-recognized'           => 'Mitigation Exception Recognized (e.g. FCA Back Pay)',  // + mitigation_exception_context

// —— ws_remedy (Additions) ——————————————————————————————————————————
/**
* Interest, mitigation, and modern damage categories missing from initial seed.
*
*/
'pre-judgment-interest'                     => 'Pre-Judgment Interest',
'post-judgment-interest'                    => 'Post-Judgment Interest',
'discretionary-interest'                    => 'Discretionary Interest Award',
'mitigation-exception'                      => 'Mitigation Exception / Exemption',
'non-economic-cap-separate'                 => 'Non-Economic Damages Capped Separately',
'punitive-damages-capped-separately'        => 'Punitive Damages Capped Separately',

// —— ws_causation_standard (Additions) ——————————————————————————————
/**
* Doctrinal divergences and statutory nexus standards.
*
*/
'dual-standard-applies'                     => 'Dual Standard (Liability vs Damages Differ)',
'statutory-nexus-diverges-from-common-law'  => 'Statutory Nexus Overrides Circuit Common Law',
'substantial-motivating-factor'             => 'Substantial Motivating Factor Nexus',
'any-consideration-nexus'                   => 'Any Consideration Nexus',

// —— ws_protected_class (Pending Proposals from project-status.md) —————
/**
* Four proposals surfaced during NJ/MA ingest. Append to existing hierarchy.
*
*/
'victim-domestic-violence-sexual-assault'   => 'Victim of Domestic/Sexual Violence',
'family-member-whistleblower'               => 'Family Member of Whistleblower',
'domestic-work-employee'                    => 'Domestic Work Employee',
'contractor-subcontractor-agent'            => 'Contractor / Subcontractor / Agent',

// —— ws_process_type (Additions) ————————————————————————————————————
/**
* Pathway variations for enforcement and disclosure routing.
*
*/
'agency-inaction-civil-trigger'             => 'Civil Trigger on Agency Inaction',
'hybrid-admin-civil-path'                   => 'Hybrid Admin → Civil Pathway',
'direct-filing-permitted'                   => 'Direct Filing Permitted (No Exhaustion)',

## ── REGISTRY INTEGRATION PROTOCOL ───────────────────────────────────────────

1. **Merge Order**: New tables append after `ws_causation_standard`. Term additions are inserted into 
   existing taxonomy arrays at logical hierarchy positions (parents before children).
2. **Version Gating**: 
   - New tables: `'seed_version' => '1.0.0'` (triggers initial seed on next admin load)
   - Existing tables: bump `'seed_version'` to `'1.0.1'` (or next integer) to trigger `wp_insert_term()` for new slugs only.
3. **Parent/Child Syntax**: 
   - Top-level: `'slug' => ['Label', 1]`
   - Child: `'child-slug' => 'Child Label',`
   - The seeder's stateful parser automatically attaches subsequent terms to the active parent until a new `[1]` flag appears.
4. **Prompt Strings**: Leave `legal_prompt` and `assist_prompt` empty (`''`) until Phase 2 ingest pipeline is active. 
   The prompt generator (`tool-generate-prompt.php`) will dynamically pull `get_terms()` and build instructions.
5. **Conflict Guard**: Run `ws_seed_all_taxonomies()` in staging first. The seeder's `get_term_by('slug')` check prevents 
   duplicate term creation or orphaned children. Verify `ws_seeded_{taxonomy_slug}` option bumps correctly in `wp_options`.
6. **Hidden/Sentinel Terms**: `has-details` and `see-context` terms must remain at the bottom of their respective arrays. 
   Do not reorder them. They act as UI overflow triggers, not legal classifications.

## ── QUERY LAYER IMPACT NOTES ────────────────────────────────────────────────

- `ws_legal_recognition` expansions will surface new conditional fields in `acf/jx-statutes.php` without breaking 
  existing companion maps. The `acf/load_field` filter resolves term IDs dynamically.
- `ws_sovereign_immunity_status` enables `tax_query` filtering for state-level claims where sovereign immunity 
  blocks or limits relief (critical for James persona in public-sector retaliation cases).
- `ws_remedy_cap_basis` pairs with the `remedy_caps` repeater structure. Query layer should extract `cap_basis` 
  slugs for Phase 2 filtered render logic when `remedy_limits` is active.
- No CPT schema changes required. All additions are taxonomy-only. Query layer continues to strip `ws_` prefixes 
  on return; taxonomy slugs remain query-safe.