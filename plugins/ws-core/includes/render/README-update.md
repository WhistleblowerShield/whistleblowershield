# includes/render/

Frontend render layer.

Render functions transform normalized query arrays into HTML strings.

## Files

- `render-jurisdiction.php` (jurisdiction page assembler)
- `render-section.php` (jurisdiction section renderers)
- `render-agency.php` (agency page renderers)
- `render-directory.php` (assist-org directory renderers)
- `render-general.php` (sitewide/general renderers, including reference page renderer)
- `render-common-law.php` (common-law renderer stub)
- `ws-statute-bold.php` (content formatting helper)

## Rules

- No direct query/meta reads in render functions.
- Renderers accept arrays and return strings.
- UI copy can be injected from shortcode layer when operator-editable copy is desired.

## Common-law Status

`render-common-law.php` is currently a stub. Keep its behavior synchronized with the curated dataset shape in `ws_get_jx_common_law_data()` when implementation begins.
