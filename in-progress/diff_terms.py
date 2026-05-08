import re

php_file = r'c:\Users\dejunai\projects\repo\whistleblowershield\plugins\ws-core\includes\register-taxonomies.php'
with open(php_file, 'r', encoding='utf-8') as f:
    php_content = f.read()

md_file = r'c:\Users\dejunai\projects\repo\whistleblowershield\in-progress\legal-record-acf-hooks-v1.0.md'
with open(md_file, 'r', encoding='utf-8') as f:
    md_content = f.read()

# Extract from PHP
php_terms = set()
in_block = False
for line in php_content.split('\n'):
    if "'ws_legal_recognition'  => [" in line:
        in_block = True
    elif in_block and "'ws_causation_standard'" in line:
        break
    elif in_block:
        match = re.search(r"'([a-z0-9-]+)'\s+=>\s+'", line)
        if match:
            if match.group(1) not in ['plural', 'singular']:
                php_terms.add(match.group(1))

# Extract from Markdown
md_terms = set()
for line in md_content.split('\n'):
    match = re.search(r"'([a-z0-9-]+)'(?:\[[A-Z+-]+\])?\s*[→—+]", line)
    if match:
        md_terms.add(match.group(1))

print("In PHP but NOT in MD (Missing from slug-map):")
print(sorted(php_terms - md_terms))

print("In MD but NOT in PHP (Stale in slug-map):")
print(sorted(md_terms - php_terms))
