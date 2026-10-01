---
name: convert-plugin
description: Convert a legacy drop-in Zen Cart plugin (files scattered into admin/ and includes/, or a zip in zc_plugins/) into the encapsulated zc_plugins structure with a manifest, ScriptedInstaller, and removal of the old unencapsulated files. Use when asked to convert, encapsulate, or modernize an old plugin.
argument-hint: <plugin name or zip>
---

# Convert a legacy plugin

Convert the legacy plugin named in the request (`$ARGUMENTS` when invoked as a slash command) to the encapsulated plugin structure. Read `.ai/rules/plugins.md` first if it is not already loaded.

This version of Zen Cart requires PHP 8.3+ (confirm via `composer.json` and flag any CLI conflicts).

The plugin source is expected to be in `zc_plugins/`, either as a zip archive (`zc_plugins/<plugin-name>.zip`) or an already-extracted directory. If neither is found, ask before proceeding. For anything more complex (partial conversions, files scattered across the project), the developer will describe the situation at invocation time.

1. Confirm the current branch is safe and clean. Offer to work in a dedicated branch. Do not stage or commit without confirmation.
2. Plan, then build the directory structure, then the installer code, then upgrade any embedded Composer packages to a newer version.
3. In `manifest.php`, bump to a new major version for the conversion. Confirm `pluginVersion`, `pluginAuthor` and `pluginId` (the forum File ID) with the developer. Set `'removesUnencapsulatedVersion' => true` and implement `executeInstall()` to purge the old dropped-in files with `removeFiles()` before `parent::executeInstall()`.
4. Move filename constants from old `extra_datafiles` into the plugin-root `filenames.php`, and table constants into `database_tables.php`.
5. If the module class's own `remove()` deletes its configuration rows, call it from `ScriptedInstaller::executeUninstall()` guarded by `defined('MODULE_..._STATUS')`.
6. Allowlist the plugin in `zc_plugins/.gitignore`.

There are no test-suite elements for the plugin and none need to be created; the developer tests manually.
