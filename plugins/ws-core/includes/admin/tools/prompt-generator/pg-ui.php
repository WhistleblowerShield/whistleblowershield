<?php
/**
 * Prompt Generator - Admin UI and Request Handling
 */

defined( 'ABSPATH' ) || exit;

add_action( 'admin_menu', 'ws_register_prompt_generator_page' );

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

function ws_handle_prompt_generation(): array {
    $result = [ 'success' => false, 'message' => '', 'filename' => '', 'path' => '' ];

    if ( empty( $_POST['ws_prompt_nonce'] ) || ! wp_verify_nonce( $_POST['ws_prompt_nonce'], 'ws_generate_prompt' ) ) {
        $result['message'] = 'Security check failed.';
        return $result;
    }
    if ( ! current_user_can( 'manage_options' ) ) {
        $result['message'] = 'Insufficient permissions.';
        return $result;
    }

    $record_type = sanitize_text_field( $_POST['record_type'] ?? '' );
    $jx_id       = strtoupper( sanitize_text_field( $_POST['jx_id'] ?? '' ) );
    $records_requested = max( 0, (int) ( $_POST['records_requested'] ?? 0 ) );
    $proposal_count    = max( 0, (int) ( $_POST['proposal_count'] ?? 0 ) );

    if ( ! $record_type || ! preg_match( '/^[A-Z]{2}$/', $jx_id ) ) {
        $result['message'] = 'Record type and a valid two-letter jurisdiction code are required.';
        return $result;
    }

    if ( $record_type === 'assist-org' ) {
        $records_requested = $proposal_count;
    }

    $jx_context = ws_prompt_resolve_jx_context( $jx_id );

    $scope_details = sanitize_textarea_field( (string) ( $_POST['scope_details'] ?? '' ) );
    if ( $scope_details === '' && in_array( $record_type, [ 'statute', 'common-law' ], true ) ) {
        $scope_details = sanitize_key( (string) ( $jx_context['jx_type'] ?? 'state' ) ) . '-level whistleblower laws and protections';
    }

    $disable_exclusions = ! empty( $_POST['disable_exclusion_list'] );
    $auto_exclusions = ws_prompt_get_auto_exclusions( $record_type, $jx_id );
    $auto_exclusions_text = ws_prompt_resolve_auto_exclusions_text( $_POST, $auto_exclusions );

    $exclusion_list = '';
    if ( ! $disable_exclusions ) {
        $exclusion_list = ws_prompt_merge_exclusions(
            (string) ( $_POST['exclusion_list_manual'] ?? ( $_POST['exclusion_list'] ?? '' ) ),
            ws_prompt_split_lines( $auto_exclusions_text )
        );
    }

    $scope = [
        'jx_id'                  => $jx_id,
        'jx_name'                => (string) $jx_context['jx_name'],
        'legislature_url'        => (string) $jx_context['legislature_url'],
        'records_requested'      => $records_requested,
        'proposal_count'         => $proposal_count,
        'scope_details'          => $scope_details,
        'assist_org_focus_notes' => sanitize_textarea_field( (string) ( $_POST['assist_org_focus_notes'] ?? '' ) ),
        'nationwide_only'        => ! empty( $_POST['assist_org_nationwide'] ) ? 1 : 0,
        'exclusion_list'         => $exclusion_list,
        'min_quality'            => sanitize_text_field( (string) ( $_POST['min_quality'] ?? 'moderate' ) ),
        'statute_type'           => sanitize_text_field( (string) ( $_POST['statute_type'] ?? 'state' ) ),
    ];

    switch ( $record_type ) {
        case 'assist-org':
            $prompt = ws_generate_assist_org_prompt( $scope );
            break;
        case 'statute':
        case 'common-law':
        case 'citation':
        case 'construction':
            $prompt = ws_generate_legal_prompt( $record_type, $scope );
            break;
        default:
            $result['message'] = 'Unknown record type.';
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

function ws_render_prompt_generator_page() {
    $result = null;
    if ( isset( $_POST['submit'] ) ) {
        $result = ws_handle_prompt_generation();
    }

    $record_type = sanitize_text_field( $_POST['record_type'] ?? 'statute' );
    $proposal_count_value = max( 0, (int) ( $_POST['proposal_count'] ?? 0 ) );
    $assist_org_nationwide = ! empty( $_POST['assist_org_nationwide'] );
    $disable_exclusions = ! empty( $_POST['disable_exclusion_list'] );
    $posted_jx = strtoupper( sanitize_text_field( $_POST['jx_id'] ?? '' ) );
    $assist_org_focus_notes = sanitize_textarea_field( (string) ( $_POST['assist_org_focus_notes'] ?? '' ) );

    $auto_exclusions = ( $posted_jx && $record_type ) ? ws_prompt_get_auto_exclusions( $record_type, $posted_jx ) : [];
    $auto_exclusions_text = ws_prompt_resolve_auto_exclusions_text( $_POST, $auto_exclusions );

    $default_scope_details = 'state-level whistleblower laws and protections';
    if ( $posted_jx !== '' ) {
        $ctx = ws_prompt_resolve_jx_context( $posted_jx );
        $default_scope_details = sanitize_key( (string) ( $ctx['jx_type'] ?? 'state' ) ) . '-level whistleblower laws and protections';
    }
    $scope_details_value = sanitize_textarea_field( (string) ( $_POST['scope_details'] ?? '' ) );
    if ( $scope_details_value === '' && in_array( $record_type, [ 'statute', 'common-law' ], true ) ) {
        $scope_details_value = $default_scope_details;
    }

    $manual_exclusions = sanitize_textarea_field( (string) ( $_POST['exclusion_list_manual'] ?? ( $_POST['exclusion_list'] ?? '' ) ) );
    $auto_count = count( ws_prompt_split_lines( $auto_exclusions_text ) );
    $manual_count = count( ws_prompt_split_lines( $manual_exclusions ) );
    $merged_count = $disable_exclusions
        ? 0
        : count( ws_prompt_split_lines( ws_prompt_merge_exclusions( $manual_exclusions, ws_prompt_split_lines( $auto_exclusions_text ) ) ) );

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
                            <option value="construction" <?php selected( $record_type, 'construction' ); ?>>construction</option>
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
                    <td><input type="number" name="records_requested" id="records_requested" value="<?php echo esc_attr( $_POST['records_requested'] ?? 0 ); ?>" class="small-text" min="0" max="20"></td>
                </tr>
                <tr class="ws-field-assist-org" style="display:none;">
                    <th scope="row"><label for="proposal_count">Proposal Count</label></th>
                    <td><input type="number" name="proposal_count" id="proposal_count" value="<?php echo esc_attr( $proposal_count_value ); ?>" class="small-text" min="0" max="20"></td>
                </tr>
                <tr class="ws-field-assist-org" style="display:none;">
                    <th scope="row"><label for="assist_org_nationwide">Nationwide Only</label></th>
                    <td><label><input type="checkbox" name="assist_org_nationwide" id="assist_org_nationwide" value="1" <?php checked( $assist_org_nationwide ); ?>> Restrict to nationwide or clearly multi-state organizations.</label></td>
                </tr>
                <tr class="ws-field-assist-org" style="display:none;">
                    <th scope="row"><label for="assist_org_focus_notes">Assist-Org Focus Notes</label></th>
                    <td><textarea name="assist_org_focus_notes" id="assist_org_focus_notes" rows="3" class="large-text"><?php echo esc_textarea( $assist_org_focus_notes ); ?></textarea></td>
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
                    <td><textarea name="scope_details" id="scope_details_citations" rows="5" class="large-text"><?php echo esc_textarea( $_POST['scope_details'] ?? '' ); ?></textarea></td>
                </tr>
                <tr class="ws-field-citation ws-field-construction" style="display:none;">
                    <th scope="row"><label for="min_quality">Minimum Quality</label></th>
                    <td>
                        <select name="min_quality" id="min_quality">
                            <option value="low"      <?php selected( $_POST['min_quality'] ?? 'moderate', 'low' ); ?>>Low (include all)</option>
                            <option value="moderate" <?php selected( $_POST['min_quality'] ?? 'moderate', 'moderate' ); ?>>Moderate (appellate+)</option>
                            <option value="high"     <?php selected( $_POST['min_quality'] ?? 'moderate', 'high' ); ?>>High (supreme courts only)</option>
                        </select>
                    </td>
                </tr>
                <tr class="ws-field-construction" style="display:none;">
                    <th scope="row"><label for="statute_type">Statute Type</label></th>
                    <td>
                        <select name="statute_type" id="statute_type">
                            <option value="state"   <?php selected( $_POST['statute_type'] ?? 'state', 'state' ); ?>>State statute</option>
                            <option value="federal" <?php selected( $_POST['statute_type'] ?? 'state', 'federal' ); ?>>Federal statute</option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="disable_exclusion_list">Exclusions</label></th>
                    <td><label><input type="checkbox" name="disable_exclusion_list" id="disable_exclusion_list" value="1" <?php checked( $disable_exclusions ); ?>> Disable exclusion list for this run.</label></td>
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
                        <textarea name="exclusion_list_manual" id="exclusion_list_manual" rows="4" class="large-text"><?php echo esc_textarea( $manual_exclusions ); ?></textarea>
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

    <?php
}
