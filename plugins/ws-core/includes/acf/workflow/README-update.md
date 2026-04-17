# includes/acf/workflow/

Shared ACF workflow field groups used across multiple CPTs.

## Files

- `acf-stamp-fields.php` (`group_stamp_metadata`)
- `acf-plain-english-fields.php` (`group_plain_english_metadata`)
- `acf-source-verify.php` (`group_source_verify_metadata`)
- `acf-major-edit.php` (`group_major_edit_metadata`)

## Purpose

- `stamp`: authorship/edit tracking fields
- `plain_english`: plain-language content and review workflow fields
- `source_verify`: provenance and verification workflow fields
- `major_edit`: legal-update trigger metadata

## Operational Note

These groups are shared contracts. If a field is changed here, confirm downstream query payloads (`plain`, `verify`, `record`) and admin hooks remain aligned.
