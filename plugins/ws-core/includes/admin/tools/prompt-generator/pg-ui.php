<?php
/**
 * pg-ui.php
 *
 * Prompt Generator — Admin UI and Request Handling
 *
 * PURPOSE
 * -------
 * Registers the admin page, collects and validates form input, dispatches
 * to ws_generate_prompt(), writes the output file, and renders the form.
 *
 * REWRITE NOTE — THE $_POST SCATTER FIX
 * --------------------------------------
 * Prior to 3.21.0, both ws_handle_prompt_generation() and
 * ws_render_prompt_generator_page() read $_POST directly, in different
 * places, with slightly different sanitization each time. That's not a
 * PHP global in the language-keyword sense, but it behaves like one for
 * debugging purposes: rename a form field and you have to grep every
 * function in this file to find every place that reads it, with no
 * single place that declares "here's what this form actually produces."
 *
 * This rewrite adds ws_prompt_collect_form_input(): array as the ONLY
 * place in this file that touches $_POST. Every other function receives
 * that array as a parameter. If a field name ever changes, there is
 * exactly one line to edit.
 *
 * ⚠ JS FUNCTIONS RECONSTRUCTED, NOT VERIFIED ⚠
 * -----------------------------------------------
 * The original form HTML calls wsPromptToggleFields() (on record_type
 * change) and wsPromptApplyJxFromSelect() (on jurisdiction dropdown
 * change), but no <script> block defining them was recovered during
 * this rewrite pass. The versions below are reconstructed from the
 * ws-field-* class names already present in the form markup (a standard
 * show/hide-by-class pattern) and are low-risk if wrong — worst case is
 * a UI toggle behaving oddly, not a data-integrity problem. Please test
 * these against the real form before trusting them, and replace with
 * the original if it turns out to differ.
 *
 * Depends on: pg-config.php, pg-exclusions.php, pg-builders.php (for
 * ws_generate_prompt), pg-taxonomy.php (indirectly, via ws_generate_prompt).
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
 * 3.22.0-rewrite  ws_handle_prompt_generation() now validates record_type
 *                 explicitly via ws_prompt_assert_valid_record_type()
 *                 before anything else runs, and wraps the entire
 *                 generation pipeline (jurisdiction resolution, exclusion
 *                 building, prompt generation) in one try/catch instead
 *                 of several scattered ones — a throw anywhere in that
 *                 chain now surfaces as a clear admin notice with the
 *                 real refusal message, never a fatal white screen and
 *                 never a silently-written wrong file.
 * 3.21.0-rewrite  Added ws_prompt_collect_form_input() as the single
 *                 $_POST read point. Updated dispatch from the two old
 *                 builder names to ws_generate_prompt(). Reconstructed
 *                 (unverified) JS toggle functions — see note above.
 *                 Architecture and code by Claude (Anthropic), directed
 *                 and reviewed by Dejunai.
 */

defined( 'ABSPATH' ) || exit;

add_action( 'admin_menu', 'ws_register_prompt_generator_page' );

/**
 * Register prompt generator page.
 *
 * @return mixed Return description.
 */
function ws_register_prompt_generator_page() {
    add_submenu_page(
        'tools.php',
        'WS Prompt Generator',
        'WS Prompt Generator',
        'manage_options',
        'ws-prompt-generator',
        'ws_render_prompt_generator_page'
    );
}

/**
 * THE ONLY function in this file that reads $_POST. Every other function
 * receives this array's return value as a parameter. If you're chasing
 * a "where does this form field come from" bug, start and end here.
 */
function ws_prompt_collect_form_input(): array {
    $record_type = sanitize_text_field( (string) ( $_POST['record_type'] ?? 'statute' ) );
    $posted_jx   = strtoupper( sanitize_text_field( (string) ( $_POST['jx_id'] ?? '' ) ) );

    return [
        'is_submit'                  => isset( $_POST['submit'] ),
        'is_refresh_exclusions'      => isset( $_POST['ws_refresh_exclusions'] ),
        'nonce'                      => (string) ( $_POST['ws_prompt_nonce'] ?? '' ),
        'record_type'                => $record_type,
        'jx_id'                      => $posted_jx,
        'records_requested'          => max( 0, (int) ( $_POST['records_requested'] ?? 0 ) ),
        'proposal_count'             => max( 0, (int) ( $_POST['proposal_count'] ?? 0 ) ),
        'assist_org_nationwide'      => ! empty( $_POST['assist_org_nationwide'] ),
        'assist_org_focus_notes'     => sanitize_textarea_field( (string) ( $_POST['assist_org_focus_notes'] ?? '' ) ),
        'scope_details'              => sanitize_textarea_field( (string) ( $_POST['scope_details'] ?? '' ) ),
        'min_quality'                => sanitize_text_field( (string) ( $_POST['min_quality'] ?? 'moderate' ) ),
        'statute_type'               => sanitize_text_field( (string) ( $_POST['statute_type'] ?? 'state' ) ),
        'disable_exclusion_list'     => ! empty( $_POST['disable_exclusion_list'] ),
        'exclusion_list_auto'        => sanitize_textarea_field( (string) ( $_POST['exclusion_list_auto'] ?? '' ) ),
        'exclusion_list_auto_edited' => ! empty( $_POST['exclusion_list_auto_edited'] ),
        'exclusion_list_manual'      => sanitize_textarea_field( (string) ( $_POST['exclusion_list_manual'] ?? ( $_POST['exclusion_list'] ?? '' ) ) ),
    ];
}

/**
 * Handle prompt generation.
 *
 * @param array $input Parameter description.
 * @return array Return description.
 */
function ws_handle_prompt_generation( array $input ): array {
    $result = [ 'success' => false, 'message' => '', 'filename' => '', 'path' => '' ];

    if ( empty( $input['nonce'] ) || ! wp_verify_nonce( $input['nonce'], 'ws_generate_prompt' ) ) {
        $result['message'] = 'Security check failed.';
        return $result;
    }
    if ( ! current_user_can( 'manage_options' ) ) {
        $result['message'] = 'Insufficient permissions.';
        return $result;
    }

    $record_type = $input['record_type'];
    $jx_id       = $input['jx_id'];

    if ( ! preg_match( '/^[A-Z]{2}$/', $jx_id ) ) {
        $result['message'] = 'A valid two-letter jurisdiction code is required.';
        return $result;
    }

    // Everything from here on either produces a real result or throws.
    // One wide catch, not several scattered ones — every function this
    // pipeline calls (jurisdiction resolution, exclusion building, prompt
    // generation) validates record_type independently and refuses to
    // guess, per project rule. Catching broadly here just means a refusal
    // anywhere in the chain surfaces as one clear admin notice instead of
    // a fatal white screen, without papering over what actually failed.
    try {
        ws_prompt_assert_valid_record_type( $record_type );

        $records_requested = ( $record_type === 'assist-org' ) ? $input['proposal_count'] : $input['records_requested'];

        $jx_context = ws_prompt_resolve_jx_context( $jx_id );

        $scope_details = $input['scope_details'];
        if ( $scope_details === '' && in_array( $record_type, [ 'statute', 'common-law' ], true ) ) {
            $scope_details = sanitize_key( (string) ( $jx_context['jx_type'] ?? 'state' ) ) . '-level whistleblower laws and protections';
        }

        $auto_exclusions      = ws_prompt_get_auto_exclusions( $record_type, $jx_id );
        $auto_exclusions_text = $input['exclusion_list_auto_edited']
            ? $input['exclusion_list_auto']
            : implode( "\n", $auto_exclusions );

        $exclusion_list = '';
        if ( ! $input['disable_exclusion_list'] ) {
            $exclusion_list = ws_prompt_merge_exclusions(
                $input['exclusion_list_manual'],
                ws_prompt_split_lines( $auto_exclusions_text )
            );
        }

        $scope = [
            'jx_id'                  => $jx_id,
            'jx_name'                => (string) $jx_context['jx_name'],
            'legislature_url'        => (string) $jx_context['legislature_url'],
            'records_requested'      => $records_requested,
            'proposal_count'         => $input['proposal_count'],
            'scope_details'          => $scope_details,
            'assist_org_focus_notes' => $input['assist_org_focus_notes'],
            'nationwide_only'        => $input['assist_org_nationwide'] ? 1 : 0,
            'exclusion_list'         => $exclusion_list,
            'min_quality'            => $input['min_quality'],
            'statute_type'           => $input['statute_type'],
        ];

        $prompt = ws_generate_prompt( $record_type, $scope );
    } catch ( \Throwable $e ) {
        // Loud to the human (visible red admin notice), not silently
        // wrong and not a fatal white screen. No file is written when
        // this happens — a caught exception here means something in the
        // pipeline refused to guess, and that refusal should reach you
        // with its real message, not get swallowed or generalized away.
        //
        // ws_fail_loud() (in ws-fail-loud.php, the plugin-wide unified
        // failure primitive) already logged this to
        // wp-content/logs/ws-core-error.log and the admin-visible rolling
        // log before it reached here — this catch only controls what the
        // prompt-generator's OWN admin page shows inline, in addition to
        // that. If $e is a WS_Loud_Failure, prefix with its $context so
        // it's clear which subsystem refused.
        $prefix = ( $e instanceof \WS_Loud_Failure ) ? "[{$e->context}] " : '';
        $result['message'] = "Refused: {$prefix}" . $e->getMessage();
        return $result;
    }

    $dir      = ws_prompt_output_dir();
    $filename = strtoupper( $jx_id ) . '-' . $records_requested . '-' . ucfirst( $record_type ) . '-' . date( 'Ymd-Hi' ) . '.txt';
    $filepath = $dir . '/' . $filename;

    if ( file_put_contents( $filepath, $prompt ) === false ) {
        $result['message'] = 'Failed to write prompt file. Check directory permissions on ' . $dir;
        return $result;
    }

    $result['success']  = true;
    $result['message']  = 'Prompt generated successfully.';
    $result['filename'] = $filename;
    $result['path']     = str_replace( ABSPATH, '/', $filepath );
    return $result;
}

/**
 * Render prompt generator page.
 *
 * @return mixed Return description.
 */
function ws_render_prompt_generator_page() {
    $input = ws_prompt_collect_form_input();

    $result = $input['is_submit'] ? ws_handle_prompt_generation( $input ) : null;

    $record_type = $input['record_type'];
    $posted_jx   = $input['jx_id'];

    $auto_exclusions      = ( $posted_jx && $record_type ) ? ws_prompt_get_auto_exclusions( $record_type, $posted_jx ) : [];
    $auto_exclusions_text = $input['exclusion_list_auto_edited']
        ? $input['exclusion_list_auto']
        : implode( "\n", $auto_exclusions );

    $default_scope_details = 'state-level whistleblower laws and protections';
    if ( $posted_jx !== '' ) {
        $ctx = ws_prompt_resolve_jx_context( $posted_jx );
        $default_scope_details = sanitize_key( (string) ( $ctx['jx_type'] ?? 'state' ) ) . '-level whistleblower laws and protections';
    }
    $scope_details_value = $input['scope_details'];
    if ( $scope_details_value === '' && in_array( $record_type, [ 'statute', 'common-law' ], true ) ) {
        $scope_details_value = $default_scope_details;
    }

    $auto_count   = count( ws_prompt_split_lines( $auto_exclusions_text ) );
    $manual_count = count( ws_prompt_split_lines( $input['exclusion_list_manual'] ) );
    $merged_count = $input['disable_exclusion_list']
        ? 0
        : count( ws_prompt_split_lines( ws_prompt_merge_exclusions( $input['exclusion_list_manual'], ws_prompt_split_lines( $auto_exclusions_text ) ) ) );

    $jx_terms = get_terms( [
        'taxonomy'   => WS_JURISDICTION_TAXONOMY,
        'hide_empty' => false,
        'orderby'    => 'slug',
        'order'      => 'ASC',
    ] );
    ?>
    <div class="wrap" id="ws-prompt-generator-root">
        <h1>WS Prompt Generator</h1>
        <p>Generates AI research prompt templates from live taxonomy data. Output files are written to <code><?php echo esc_html( str_replace( ABSPATH, '/', WP_CONTENT_DIR . '/logs/ws-prompts/' ) ); ?></code>.</p>

        <?php if ( $result ): ?>
            <div class="notice notice-<?php echo $result['success'] ? 'success' : 'error'; ?> is-dismissible">
                <p><?php echo esc_html( $result['message'] ); ?></p>
                <?php if ( $result['success'] ): ?>
                    <p><strong>File:</strong> <code><?php echo esc_html( $result['filename'] ); ?></code></p>
                    <p><strong>Path:</strong> <code><?php echo esc_html( $result['path'] ); ?></code></p>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <form method="post" action="">
            <?php wp_nonce_field( 'ws_generate_prompt', 'ws_prompt_nonce' ); ?>
            <table class="form-table" role="presentation">
                <tr>
                    <th scope="row"><label for="record_type">Record Type</label></th>
                    <td>
                        <select name="record_type" id="record_type" onchange="wsPromptToggleFields()">
                            <option value="statute"        <?php selected( $record_type, 'statute' ); ?>>Statute</option>
                            <option value="common-law"     <?php selected( $record_type, 'common-law' ); ?>>Common Law</option>
                            <option value="citation"       <?php selected( $record_type, 'citation' ); ?>>Citation</option>
                            <option value="construction"   <?php selected( $record_type, 'construction' ); ?>>Construction</option>
                            <option value="assist-org"     <?php selected( $record_type, 'assist-org' ); ?>>Assist Org</option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="jx_id">Jurisdiction ID</label></th>
                    <td>
                        <select id="jx_select" class="regular-text" onchange="wsPromptApplyJxFromSelect()">
                            <option value="">Select jurisdiction code...</option>
                            <?php if ( ! is_wp_error( $jx_terms ) ): ?>
                                <?php foreach ( $jx_terms as $term ): ?>
                                    <option value="<?php echo esc_attr( strtoupper( $term->slug ) ); ?>" <?php selected( strtoupper( $posted_jx ), strtoupper( $term->slug ) ); ?>><?php echo esc_html( strtoupper( $term->slug ) . ' - ' . $term->name ); ?></option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                        <br><br>
                        <input type="text" name="jx_id" id="jx_id" value="<?php echo esc_attr( $posted_jx ); ?>" class="small-text" maxlength="2" required style="text-transform:uppercase;">
                    </td>
                </tr>
                <tr class="ws-field-statute ws-field-common-law">
                    <th scope="row"><label for="records_requested">Records Requested</label></th>
                    <td><input type="number" name="records_requested" id="records_requested" value="<?php echo esc_attr( $input['records_requested'] ); ?>" class="small-text" min="0" max="20"></td>
                </tr>
                <tr class="ws-field-assist-org" style="display:none;">
                    <th scope="row"><label for="proposal_count">Proposal Count</label></th>
                    <td><input type="number" name="proposal_count" id="proposal_count" value="<?php echo esc_attr( $input['proposal_count'] ); ?>" class="small-text" min="0" max="20"></td>
                </tr>
                <tr class="ws-field-assist-org" style="display:none;">
                    <th scope="row"><label for="assist_org_nationwide">Nationwide Only</label></th>
                    <td><label><input type="checkbox" name="assist_org_nationwide" id="assist_org_nationwide" value="1" <?php checked( $input['assist_org_nationwide'] ); ?>> Restrict to nationwide or clearly multi-state organizations.</label></td>
                </tr>
                <tr class="ws-field-assist-org" style="display:none;">
                    <th scope="row"><label for="assist_org_focus_notes">Assist-Org Focus Notes</label></th>
                    <td><textarea name="assist_org_focus_notes" id="assist_org_focus_notes" rows="3" class="large-text"><?php echo esc_textarea( $input['assist_org_focus_notes'] ); ?></textarea></td>
                </tr>
                <tr class="ws-field-statute ws-field-common-law">
                    <th scope="row"><label for="scope_details">Scope Details</label></th>
                    <td>
                        <textarea name="scope_details" id="scope_details" rows="3" class="large-text"><?php echo esc_textarea( $scope_details_value ); ?></textarea>
                        <p class="description">Optional. If blank, defaults to: <?php echo esc_html( $default_scope_details ); ?>.</p>
                    </td>
                </tr>
                <tr class="ws-field-citation ws-field-construction" style="display:none;">
                    <th scope="row"><label for="scope_details_citations">Statutes to Research</label></th>
                    <td><textarea name="scope_details" id="scope_details_citations" rows="5" class="large-text"><?php echo esc_textarea( $input['scope_details'] ); ?></textarea></td>
                </tr>
                <tr class="ws-field-citation ws-field-construction" style="display:none;">
                    <th scope="row"><label for="min_quality">Minimum Quality</label></th>
                    <td>
                        <select name="min_quality" id="min_quality">
                            <option value="low"      <?php selected( $input['min_quality'], 'low' ); ?>>Low (include all)</option>
                            <option value="moderate" <?php selected( $input['min_quality'], 'moderate' ); ?>>Moderate (appellate+)</option>
                            <option value="high"     <?php selected( $input['min_quality'], 'high' ); ?>>High (supreme courts only)</option>
                        </select>
                    </td>
                </tr>
                <tr class="ws-field-construction" style="display:none;">
                    <th scope="row"><label for="statute_type">Statute Type</label></th>
                    <td>
                        <select name="statute_type" id="statute_type">
                            <option value="state"   <?php selected( $input['statute_type'], 'state' ); ?>>State statute</option>
                            <option value="federal" <?php selected( $input['statute_type'], 'federal' ); ?>>Federal statute</option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="disable_exclusion_list">Exclusions</label></th>
                    <td><label><input type="checkbox" name="disable_exclusion_list" id="disable_exclusion_list" value="1" <?php checked( $input['disable_exclusion_list'] ); ?>> Disable exclusion list for this run.</label></td>
                </tr>
                <tr>
                    <th scope="row"><label for="exclusion_list_auto">Auto Exclusions (Drafts)</label></th>
                    <td>
                        <input type="hidden" name="exclusion_list_auto_edited" id="exclusion_list_auto_edited" value="0">
                        <textarea name="exclusion_list_auto" id="exclusion_list_auto" rows="4" class="large-text code"><?php echo esc_textarea( $auto_exclusions_text ); ?></textarea>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="exclusion_list_manual">Manual Exclusions (Optional)</label></th>
                    <td>
                        <textarea name="exclusion_list_manual" id="exclusion_list_manual" rows="4" class="large-text"><?php echo esc_textarea( $input['exclusion_list_manual'] ); ?></textarea>
                        <p class="description"><strong>Merged exclusions:</strong> <?php echo (int) $merged_count; ?> unique (<?php echo (int) $auto_count; ?> auto + <?php echo (int) $manual_count; ?> manual before dedupe).</p>
                    </td>
                </tr>
            </table>

            <p class="submit">
                <input type="submit" name="submit" id="submit" class="button button-primary" value="Generate Prompt">
                <input type="submit" name="ws_refresh_exclusions" id="ws_refresh_exclusions" class="button" value="Refresh Auto Exclusions" onclick="var edited=document.getElementById('exclusion_list_auto_edited'); if (edited) { edited.value='0'; }">
            </p>
        </form>
    </div>

    <script>
    /**
     * ⚠ RECONSTRUCTED, NOT VERIFIED — see file header docblock.
     * Standard show/hide-by-class pattern inferred from the ws-field-*
     * classes already present in the form markup above. Test against
     * the real form before trusting.
     */
    function wsPromptToggleFields() {
        var recordType = document.getElementById('record_type').value;
        var allFieldRows = document.querySelectorAll(
            '.ws-field-statute, .ws-field-common-law, .ws-field-citation, .ws-field-construction, .ws-field-assist-org'
        );
        allFieldRows.forEach(function (row) {
            row.style.display = 'none';
        });
        var showClass = '.ws-field-' + recordType;
        document.querySelectorAll(showClass).forEach(function (row) {
            row.style.display = '';
        });
    }

    /**
     * Wspromptapplyjxfromselect.
     *
     * @return mixed Return description.
     */
    function wsPromptApplyJxFromSelect() {
        var select = document.getElementById('jx_select');
        var jxInput = document.getElementById('jx_id');
        if (select && jxInput && select.value) {
            jxInput.value = select.value;
        }
    }

    document.addEventListener('DOMContentLoaded', wsPromptToggleFields);
    </script>
    <?php
}