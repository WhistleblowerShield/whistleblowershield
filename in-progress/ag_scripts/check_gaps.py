import re

fields_file = 'in-progress/legal-record-acf-fields-v3.0.md'
hooks_file = 'in-progress/legal-record-acf-hooks-v1.0.md'

with open(fields_file, 'r', encoding='utf-8') as f:
    fields_content = f.read()

with open(hooks_file, 'r', encoding='utf-8') as f:
    hooks_content = f.read()

# Find all 'conditional on `X` in `legal_recognitions`'
cond_regex = re.compile(r'conditional on `([^`]+)` in `legal_recognitions`')
field_slugs = set(cond_regex.findall(fields_content))

# Find all recognized slugs in the hook map
hook_regex = re.compile(r'^[A-Z][a-z]+:\s+\'([a-z0-9-]+)\'', re.MULTILINE)
hook_slugs = set(hook_regex.findall(hooks_content))

print('Slugs in fields but not in hooks map:')
for slug in field_slugs - hook_slugs:
    print(f'  - {slug}')

print('\nSlugs in hooks map but not in fields as conditionals (might be standalone):')
for slug in hook_slugs - field_slugs:
    print(f'  - {slug}')

# Also check Sister to relationships
sister_regex = re.compile(r'`([^`]+)`\s+—\s+\([^)]*Sister to `([^`]+)`')
sisters = sister_regex.findall(fields_content)

print(f'\nChecking {len(sisters)} sister relationships...')

# For each sister, check if it appears on the same line as its sibling in hooks map
for sister, sibling in sisters:
    # Find the sibling in the hooks file
    escaped_sibling = re.escape(sibling)
    sibling_line_regex = re.compile(r'^.*' + escaped_sibling + r'.*$', re.MULTILINE)
    matches = sibling_line_regex.findall(hooks_content)
    found_sister = False
    if matches:
        for match in matches:
            if sister in match:
                found_sister = True
                break
        if not found_sister:
            print(f"Sister '{sister}' not found on the same line as sibling '{sibling}' in hooks map.")
    else:
        print(f"Sibling '{sibling}' not found in hooks map at all.")
