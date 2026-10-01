---
paths:
  - "includes/templates/**"
  - "includes/modules/pages/**"
---

# Storefront templates, pages and page modules

## Page flow

- `index.php` includes `application_top.php`, loops over `header_php` files from `PageLoader->listModulePagesFiles('header_php', '.php')`, then loads `html_header.php`, `main_template_vars.php`, `tpl_main_page.php`.
- A page lives in `includes/modules/pages/<page_name>/`: `header_php.php` (logic before output), optional `main_template_vars.php` (output data for the template), optional `jscript_*.js` / `jscript_*.php` (page-specific JavaScript, static or PHP-generated). Its template is `tpl_<page_name>.php` under `includes/templates/template_default/templates/` or the active template's directory. Register the page's filename constant via the `filenames.php` pattern (`includes/extra_datafiles/` for core, a plugin's `filenames.php` for plugins).
- Template overrides: with active template `my_template`, files are looked up in `includes/templates/my_template/` before falling back to `template_default`. Customize there rather than editing core files.
- Language files: `lang.foo.php` returns an array of `'CONSTANT_NAME' => 'value'` pairs, merged across load layers (core → plugin, English → active language) and converted to constants via `define()`. Values may reference other keys in the same array with `%%OTHER_KEY%%` placeholders.

## Output rules

- Call `zen_output_string_protected()` on any output that includes user input.
- Curly-brace control structures only, even in templates. Never `if (): ... endif;` or `foreach (): ... endforeach;` (see `CONVENTIONS.md`).
- Forms are opened by echoing `zen_draw_form()`. When the form closes in the same file, close it through PHP too: `<?= '</form>' ?>`. This is intentional; do not flag it as an unnecessary echo or malformed HTML.

## Configuration: `zen_config()` vs `$tplSetting`

Two distinct mechanisms read configuration values; pick based on what is being read and where the code runs.

- `zen_config('KEY', $default = null)` (`includes/functions/zen_config.php`) reads the DB-backed `configuration` / `product_type_layout` repositories, falling back to a same-named `defined()` constant, then to `$default`. Available everywhere (catalog and admin). Use it for core, site-wide, security-sensitive, or business-logic settings, and for any key called with a meaningful default.
- `$tplSetting->KEY` (class `TemplateSettings extends Settings`, `includes/classes/TemplateSettings.php` / `includes/classes/Settings.php`) is a per-template settings store layering an explicit override (a template's `template_settings.php`, or a DB-stored per-template override) on top of the same global-constant fallback. Use it only for display/layout/template-presentation settings a template should be able to override. It has no default-parameter support, so it is not a drop-in replacement for `zen_config('KEY', $default)`.

`$tplSetting` availability, verify before converting a `zen_config()` call:

- Initialized only in `includes/init_includes/init_templates.php`, during the catalog bootstrap. Never in admin: do not convert code reachable from `admin/*`.
- It is a true PHP global: visible in top-level procedural include files (page modules, templates), but inside a function or class method body it needs `global $tplSetting;`.
- `Settings::setFromArray()` treats an explicit override and a same-named global constant as separate concepts: a key already explicitly set (e.g. from `template_settings.php`) always wins over a constant of the same name, regardless of resolution order. If extending `Settings`, do not reintroduce a check that conflates "has a constant" with "has an explicit value"; that previously discarded overrides silently whenever a same-named config constant existed.

### Which keys may be converted to `$tplSetting->`

`$tplSetting->KEY` is appropriate only for **display/layout/template-presentation** settings —
things a template should reasonably be able to override (box widths, separators, show/hide a
sidebox section, image-size defaults, and similar).

Keep using `zen_config()` for:
- Core/site-wide settings not specific to template presentation: `STORE_NAME`, `STORE_OWNER_*`,
  `CONTACT_US*`, `DEFAULT_LANGUAGE`, `DEFAULT_CURRENCY`.
- Security/session-sensitive and registration-field settings: `SESSION_*`, `ENTRY_*_LENGTH`,
  `ACCOUNT_*` fields governing required registration fields.
- Business-logic/checkout-flow settings: `STOCK_CHECK`, `STOCK_ALLOW_CHECKOUT`,
  `DISABLED_PRODUCTS*`, `CUSTOMERS_APPROVAL*`, `CUSTOMERS_REFERRAL_STATUS`,
  `CUSTOMERS_PRODUCTS_NOTIFICATION_STATUS`, `CUSTOMERS_ACTIVATION_REQUIRED`.
- Any `MODULE_*` setting (payment/shipping/order-total module configuration).
- Any key currently called with a meaningful default-value second argument
  (`zen_config('KEY', $default)`) — `$tplSetting->KEY` has no equivalent default-parameter
  support, so converting would silently drop that fallback behavior.
- Settings actually backed by a different repository/table than `configuration`
  (e.g. `SHOW_*_ATTRIBUTES`, sourced from `product_type_layout`) — these require
  `zen_config()`'s repository-aware lookup.
- `*_FILENAME` constants and other non-display identifiers.

When in doubt, check the setting's `configuration_group_id`/title/description in
`zc_install/sql/install/mysql_zencart.sql` to judge whether it reads as a template/display
concern or a core/business one.
