import re
import json

php_file = r'c:\Users\dejunai\projects\repo\whistleblowershield\plugins\ws-core\includes\register-taxonomies.php'
with open(php_file, 'r', encoding='utf-8') as f:
    php_content = f.read()

md_file = r'c:\Users\dejunai\projects\repo\whistleblowershield\in-progress\legal-record-acf-hooks-v1.0.md'
with open(md_file, 'r', encoding='utf-8') as f:
    md_content = f.read()

terms = []
in_block = False
for line in php_content.split('\n'):
    if "'ws_legal_recognition'  => [" in line:
        in_block = True
    elif in_block and "'ws_causation_standard'" in line:
        break
    elif in_block:
        match = re.search(r"'([a-z0-9-]+)'\s+=>\s+'", line)
        if match:
            terms.append(match.group(1))

missing = []
for term in terms:
    search_str = f"'{term}'"
    if search_str not in md_content:
        missing.append(term)

print('Missing terms:', json.dumps(missing))
