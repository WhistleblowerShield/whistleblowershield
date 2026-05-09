import re

file_path = 'c:/Users/dejunai/projects/repo/whistleblowershield/plugins/ws-core/includes/admin/matrix/matrix-assist-orgs.php'
with open(file_path, 'r', encoding='utf-8') as f:
    content = f.read()

# Replace keys
content = content.replace("'_ws_aorg_id' =>", "'_id' =>")
content = content.replace("'ws_aorg_website_url' =>", "'official_homepage_url' =>")
content = content.replace("'ws_aorg_intake_url' =>", "'intake_url' =>")
content = content.replace("'ws_aorg_contact_url' =>", "'contact_url' =>")
content = content.replace("'sectors' =>", "'employment_sectors' =>")

# Remove unwanted keys
content = re.sub(r"\s*'secure_contact_url' => [^,]+,", "", content)
content = re.sub(r"\s*'is_limited_scope' => [^,]+,", "", content)
content = re.sub(r"\s*'community_scope' => [^,]+,", "", content)

# Flatten phones
def flatten_phones(match):
    block = match.group(0)
    numbers = re.findall(r"'number' => '([^']+)'", block)
    if not numbers:
        return "'phones' => '',"
    return "'phones' => '" + ", ".join(numbers) + "',"
content = re.sub(r"'phones' => \[[^\]]*?\],", flatten_phones, content, flags=re.DOTALL)

# Flatten emails
def flatten_emails(match):
    block = match.group(0)
    addresses = re.findall(r"'address' => '([^']+)'", block)
    if not addresses:
        return "'emails' => '',"
    return "'emails' => '" + ", ".join(addresses) + "',"
content = re.sub(r"'emails' => \[[^\]]*?\],", flatten_emails, content, flags=re.DOTALL)

with open(file_path, 'w', encoding='utf-8') as f:
    f.write(content)

print('File updated successfully')
