import re

file_path = 'c:/Users/dejunai/projects/repo/whistleblowershield/plugins/ws-core/includes/admin/matrix/matrix-assist-orgs.php'
with open(file_path, 'r', encoding='utf-8') as f:
    content = f.read()

# 1. Update ws_matrix_build_assist_org_internal_id
content = content.replace(
    "$homepage = trim( (string) ws_matrix_required( $org, 'ws_aorg_website_url' ) );",
    "$homepage = trim( (string) ws_matrix_required( $org, 'official_homepage_url' ) );"
)

# 2. Update $meta array definition
meta_start = content.find('$meta = [')
meta_end = content.find('];', meta_start) + 2
old_meta = content[meta_start:meta_end]

new_meta = """$meta = [
            '_ws_aorg_id'                  => $internal_id,
            'ws_aorg_official_name'        => $official_name,
            'ws_aorg_common_name'          => ws_matrix_optional( $org, 'common_name' ),
            'ws_aorg_official_homepage_url'=> ws_matrix_required( $org, 'official_homepage_url' ),
            'ws_aorg_intake_url'           => ws_matrix_optional( $org, 'intake_url' ),
            'ws_aorg_contact_url'          => ws_matrix_optional( $org, 'contact_url' ),
            'ws_aorg_organization_model'   => $organization_model,
            'ws_aorg_secure_channel_status' => $secure_channel_status,
            'ws_aorg_secure_contact_tools' => $secure_contact_tools,
            'ws_aorg_mailing_address'      => ws_matrix_optional( $org, 'mailing_address' ),
            'ws_aorg_income_screening'     => $income_screening,
            'ws_aorg_eligibility_status'   => $eligibility_status,
            'ws_aorg_is_nationwide'        => ws_matrix_required( $org, 'is_nationwide' ),
            'ws_aorg_anonymous_pre_consult_status' => $anonymous_pre_consult_status,
            'ws_aorg_has_attorneys'        => $has_attorneys,
            'ws_aorg_whistleblower_fit'    => $whistleblower_fit,
            'ws_aorg_service_depth'        => $service_depth,
            'ws_aorg_intake_commitment_class' => $intake_commitment_class,
            'ws_aorg_whistleblower_scope'  => ws_matrix_required( $org, 'whistleblower_scope' ),
            'ws_aorg_phones'               => ws_matrix_optional( $org, 'phones' ),
            'ws_aorg_emails'               => ws_matrix_optional( $org, 'emails' ),
        ];"""

content = content[:meta_start] + new_meta + content[meta_end:]

# 3. Remove repeater logic for phones and emails
repeater_start = content.find('// Contact repeaters: consume canonical matrix arrays directly.')
repeater_end = content.find('// ── Taxonomies ───────────────────────────────────────────────────────')
content = content[:repeater_start] + content[repeater_end:]

# 4. Update taxonomy assignment for sectors
content = content.replace(
    "ws_matrix_assign_terms_strict( $post_id, ws_matrix_required_array( $org, 'sectors' ), 'ws_employment_sector' );",
    "ws_matrix_assign_terms_strict( $post_id, ws_matrix_required_array( $org, 'employment_sectors' ), 'ws_employment_sector' );"
)

with open(file_path, 'w', encoding='utf-8') as f:
    f.write(content)

print('Seeder updated successfully')
