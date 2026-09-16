---
paths:
  - "zc_plugins/**"
---

# Plugins (`zc_plugins/`)

Encapsulated plugins are the preferred way to add features; core edits are the exception.
Official developer docs: https://docs.zen-cart.com/dev/plugins/

## Layout and discovery

- Directory layout: `zc_plugins/<unique_key>/<version>/catalog/...` and `zc_plugins/<unique_key>/<version>/admin/...`.
- Minimal files: `manifest.php` at the plugin root (unique_key/version and human metadata) and the plugin-provided `catalog/includes/` or `admin/includes/` folders for `classes`, `extra_configures`, `extra_datafiles`, and `modules/pages/`.
- Discovery: `includes/application_top.php` uses `PluginManager` + `PluginControlRepository` to produce `$installedPlugins`; `FileSystem->loadFilesFromPluginsDirectory()` pulls in the plugin's files. Plugins are discovered only once registered ("Installed"): records in the `plugin_control` (and optional `plugin_control_versions`) tables.
- PSR-4: namespace prefixes are added at runtime in `application_top.php` via `$psr4Autoloader->addPrefix()` for `Zencart\Plugins\Catalog\<UniqueKey>` and `Zencart\Plugins\Admin\<UniqueKey>`. A plugin with `unique_key` = `myplugin` and `version` = `1.0.0` maps `Zencart\Plugins\Catalog\Myplugin` to `zc_plugins/myplugin/1.0.0/catalog/includes/classes/`, so `Zencart\Plugins\Catalog\Myplugin\Utils\Helper` lives in `.../catalog/includes/classes/Utils/Helper.php`.
- Additional autoloading that is not auto-detected can be provided by a `psr4Autoload.php` file in the plugin root that registers namespaces or includes the plugin's composer autoloader.
- `unique_key` should be Capitalized for new plugins.
- Filename constants go in a `filenames.php` file in the plugin root (older plugins used `catalog/includes/`); database table constants go in a `database_tables.php` file in the plugin root. The `FileSystem` loader includes both during bootstrap.
- Constants that must be defined early go in `includes/extra_configures/some_filename.php` under `catalog/` or `admin/`.
- Admin pages: name a page's JavaScript file after the PHP file and it loads automatically. For example, `admin/rewards.php` loads `admin/includes/javascript/rewards.js` and any `admin/includes/javascript/rewards_*.js` (see `admin/includes/javascript_loader.php`).
- Stylesheets on storefront pages: an observer attaches to `NOTIFY_HTML_HEAD_END` and calls `linkCatalogStylesheet()` from the `InteractsWithPlugins` trait; its constructor must call `$this->detectZcPluginDetails(__DIR__)` first. The CSS file goes in `catalog/includes/templates/default/css/` (`PageLoader::getTemplatePluginDir()` searches every directory under the plugin's `catalog/includes/templates/`; `default` is the convention, a storefront template's name also works).

Minimal file structure:

```
- zc_plugins/myplugin/1.0.0/manifest.php
- zc_plugins/myplugin/1.0.0/filenames.php
- zc_plugins/myplugin/1.0.0/Installer/ScriptedInstaller.php
- zc_plugins/myplugin/1.0.0/Installer/languages/english/main.php (optional, skip if no strings added)
# catalog-side pages:
- zc_plugins/myplugin/1.0.0/catalog/includes/classes/observers/auto_MyClass.php
- zc_plugins/myplugin/1.0.0/catalog/includes/languages/english/lang.my_page.php
- zc_plugins/myplugin/1.0.0/catalog/includes/modules/pages/my_page/header_php.php
- zc_plugins/myplugin/1.0.0/catalog/includes/templates/template_default/tpl_my_page.php
# admin pages:
- zc_plugins/myplugin/1.0.0/admin/admin_page_name.php
- zc_plugins/myplugin/1.0.0/admin/includes/languages/english/lang.admin_page_name.php
- zc_plugins/myplugin/1.0.0/admin/includes/classes/observers/auto_MyAdminClass.php
```

Minimal `manifest.php`:

```php
return [
    'pluginVersion' => 'v1.0.0',
    'pluginName' => "My Plugin",
    'pluginDescription' => 'Short description',
    'pluginAuthor' => 'You',
    'pluginId' => 0, // ID from Zen Cart forum, if published in plugin library, in order to check for updates via Plugin Manager.
];
```

## Installers

- Installation scripts live in `zc_plugins/<unique_key>/<version>/Installer/`. They must be idempotent, i.e. self-upgrading across missing updates from prior versions.
- `Installer/ScriptedInstaller.php` is optional for some payment/shipping/order-total module-style plugins. Without it, `BasePluginInstaller` simply registers/deregisters the `plugin_control` entry and the install is a no-op success.
- A payment/shipping/order-total plugin may keep `install()`, `remove()`, and `keys()` methods on its module class to manage its own `configuration`-table records (invoked from the admin Modules pages independently of Plugin Manager). Those methods handle configuration entries only, never schema changes; schema changes go in `ScriptedInstaller` install/upgrade/remove methods.
- When converting a previously-unencapsulated module, set `'removesUnencapsulatedVersion' => true` in `manifest.php` and implement `executeInstall()` in `ScriptedInstaller` to purge the old dropped-in files with the inherited `removeFiles($files_to_remove, $context)` helper before calling `parent::executeInstall()`.
- If the module class's own `remove()` deletes its `configuration` records, call it from `ScriptedInstaller::executeUninstall()` (guarded by `defined('MODULE_..._STATUS')`) so a full Plugin Manager uninstall also cleans up.
- `PayPalRestful`'s `Installer/ScriptedInstaller.php` is a working example of both patterns.
- Direct `plugin_control` database inserts are acceptable only in tests/CI setup, never in application code.

## Composer packages inside a plugin

- Manage them inside the plugin: a `composer.json` in the plugin root and `composer install` there to create a plugin-local `vendor/`. Put an `.htaccess` in that `vendor/` to block web access.
- The main application's composer autoloader will not load classes from the plugin's `vendor/`. Register them at runtime with a `psr4Autoload.php` in the plugin root:

```php
// psr4Autoload.php in your plugin folder:
<?php
// Load composer autoloader for this plugin's dependencies
require __DIR__ . '/vendor/autoload.php';

// Alternatively, register specific PSR-4 namespaces for classes not following the prescribed pattern. (Should rarely be needed.)
/** @var \Aura\Autoload\Loader $psr4Autoloader */
//$psr4Autoloader->addPrefix('Foo', __DIR__ . '/vendor/foo/foobar/src');
```

## Enabling a plugin for development and tests

1. Admin UI (preferred, and the best end-to-end test): install/enable via the admin `Plugin Manager`.
2. Direct DB insert, only for tests or CI:

```sql
INSERT INTO plugin_control
  (unique_key, name, description, type, managed, status, author, version, zc_versions, infs)
VALUES
  ('myplugin', 'My Plugin', 'Short description', 'Custom Module', 0, 1, 'You', '1.0.0', '*', 0);

INSERT INTO plugin_control_versions
  (unique_key, version, author, zc_versions, infs)
VALUES
  ('myplugin', '1.0.0', 'You', '*', 0);
```

## Git and search traps

- `zc_plugins/.gitignore` is a blanket deny-all (`*`) with an explicit allowlist. When adding a plugin, append `!PluginName/` and `!PluginName/**` to that file or its files are invisible to git.
- That pattern also fools gitignore-aware search tools (`ripgrep`/`ugrep` with ignore-files enabled, which many `grep` wrappers turn on). Searching from the `zc_plugins/` parent can silently skip an allowlisted plugin's subtree. Target the plugin directory directly (`zc_plugins/PluginName`) or use a flag that ignores `.gitignore`.

## Reviewing plugin changes

- Verify the layout above, `manifest.php`, `filenames.php`, `database_tables.php`, PSR-4 mappings, and installer patterns.
- Installer scripts must be idempotent and use Plugin Manager conventions.
- A new plugin must be allowlisted in `zc_plugins/.gitignore`.
