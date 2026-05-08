import re

file_path = 'c:/Users/dejunai/projects/repo/whistleblowershield/plugins/ws-core/includes/admin/matrix/matrix-assist-orgs.php'
with open(file_path, 'r', encoding='utf-8') as f:
    content = f.read()

# Fix dangling brackets from phones and emails
content = re.sub(r"('phones' => '[^']*',\s*)\],", r"\1", content)
content = re.sub(r"('emails' => '[^']*',\s*)\],", r"\1", content)

with open(file_path, 'w', encoding='utf-8') as f:
    f.write(content)

print('Syntax fixed')
