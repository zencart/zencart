---
name: new-storefront-page
description: Add a new storefront (catalog) page to Zen Cart: filename constant, includes/modules/pages/<page>/ header_php.php and main_template_vars.php, tpl_<page>.php template, jscript files, and linking with zen_href_link(). Use when asked to create or add a new catalog page, a custom page, or a page module.
---

# Create a new storefront page

Read `.ai/rules/templates.md` first if it is not already loaded.

1. Create the filename constant in `includes/extra_datafiles/my_filenames.php` (or in a plugin's `filenames.php`):
   ```php
   define('FILENAME_MY_PAGE', 'my_page.php');
   ```
2. Create page module files under `includes/modules/pages/my_page/`:
   - `header_php.php` (backend logic that runs before output)
   - `main_template_vars.php` (builds output data and passes variables to the template)
   - `jscript_mypage.js` (standalone JavaScript for this page)
   - `jscript_mypage.php` (PHP-generated JavaScript for this page)
3. Create the template `tpl_my_page.php` under `includes/templates/template_default/` (or preferably the active template directory). Wrap any user-generated content in `zen_output_string_protected()`.
4. Load the page in the storefront and confirm it renders.
5. Link to it from an existing page with `zen_href_link(FILENAME_MY_PAGE)`.
6. If the page needs new tables or configuration, build it as a plugin instead (`create-plugin` skill) so the installer handles database and configuration entries.

Admin pages are built as plugins; see the `create-plugin` skill.
