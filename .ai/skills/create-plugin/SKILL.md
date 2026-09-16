---
name: create-plugin
description: Scaffold a new encapsulated Zen Cart plugin under zc_plugins (manifest, installer, catalog/admin layout, PSR-4 namespaces, filename constants, enablement for testing). Use when asked to build, scaffold, or add a plugin, an admin page, an observer, or a payment/shipping/order-total module as a plugin.
---

# Create a plugin

Read `.ai/rules/plugins.md` first if it is not already loaded; it holds the layout, manifest,
installer, autoload and gitignore conventions this procedure relies on.

1. Confirm the branch is clean and appropriate. Offer to work on a dedicated branch.
2. Pick a Capitalized `unique_key` and an initial version directory (`v1.0.0` style is common).
3. Create `zc_plugins/<UniqueKey>/<version>/manifest.php` with `pluginVersion`, `pluginName`, `pluginDescription`, `pluginAuthor`, `pluginId` (0 unless published in the plugin library).
4. Add `filenames.php` (and `database_tables.php` if the plugin has tables) in the plugin root.
5. Add `Installer/ScriptedInstaller.php` for schema and configuration setup. Make every step idempotent and self-upgrading. Skip it only for a module-style plugin whose module class manages its own `configuration` rows via `install()` / `remove()` / `keys()`.
6. Place catalog code under `catalog/includes/` (`classes/`, `classes/observers/`, `modules/pages/<page>/`, `languages/english/lang.<page>.php`, `templates/template_default/tpl_<page>.php`) and admin code under `admin/` (`<page>.php`, `includes/languages/english/lang.<page>.php`, `includes/classes/observers/`). Namespaced classes go under `catalog/includes/classes/` or `admin/includes/classes/` and are autoloaded as `Zencart\Plugins\Catalog\<UniqueKey>\...` / `Zencart\Plugins\Admin\<UniqueKey>\...`.
7. Stylesheets: observer on `NOTIFY_HTML_HEAD_END` calling `linkCatalogStylesheet()` after `detectZcPluginDetails(__DIR__)`; CSS under `catalog/includes/templates/default/css/`.
8. Append `!<UniqueKey>/` and `!<UniqueKey>/**` to `zc_plugins/.gitignore`.
9. Enable it through the admin Plugin Manager (or the test-only `plugin_control` insert in the rule file), exercise its pages, and run the relevant PHPUnit suites (`.ai/rules/testing.md`).
10. Wrap any user-supplied output in `zen_output_string_protected()`; use `TABLE_*` constants in SQL; curly-brace syntax only.

Do not stage or commit unless asked.
