import re

fields_file = 'in-progress/legal-record-acf-fields-v3.0.md'
hooks_file = 'in-progress/legal-record-acf-hooks-v1.0.md'

with open(fields_file, 'r', encoding='utf-8') as f:
    fields_content = f.read()

with open(hooks_file, 'r', encoding='utf-8') as f:
    hooks_content = f.read()

# All fields defined in fields document
field_def_regex = re.compile(r'^\s*-\s*`([^`]+)`', re.MULTILINE)
all_fields = set(field_def_regex.findall(fields_content))

# Extract everything mapped in the hooks document on lines with an arrow
hook_line_regex = re.compile(r'^([A-Z][a-z]+):\s+\'([a-z0-9-]+)\'(?:\[[^\]]*\])?\s*→\s*(.*)$', re.MULTILINE)
hook_mappings = hook_line_regex.findall(hooks_content)

hook_mapped_fields = set()
for status, slug, rest in hook_mappings:
    fields_on_line = re.findall(r'\'([a-z0-9_]+)\'', rest)
    hook_mapped_fields.update(fields_on_line)

# Let's find fields that are conditional on legal_recognitions or sister to a field that is in hook_mapped_fields
# And check if they are in the hook map.

missing_from_hooks = []
sisters = re.findall(r'`([^`]+)`\s+—\s+\([^)]*Sister to `([^`]+)`', fields_content)
for sister, sibling in sisters:
    if sibling in hook_mapped_fields and sister not in hook_mapped_fields:
        missing_from_hooks.append((sister, sibling))

print("Sister fields missing from hooks map (their sibling is mapped):")
for sister, sibling in missing_from_hooks:
    print(f"  - {sister} (sibling: {sibling})")

# Also find context fields triggered by legal_recognitions but not in hooks map
conds = re.findall(r'`([^`]+)`\s+—\s+\([^)]*conditional on `([^`]+)` in `legal_recognitions`', fields_content)
for field, slug in conds:
    if field not in hook_mapped_fields:
        print(f"  - {field} triggered by {slug}")

