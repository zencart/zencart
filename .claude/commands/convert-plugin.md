Convert the legacy plugin `$ARGUMENTS` to the Encapsulated Plugin structure.

This is a PHP project for Zen Cart, the open source ecommerce application.
Consult AGENTS.md and associated docs files for architecture and conventions.
This version of Zen Cart requires minimum PHP 8.3+ (confirm via composer.json and flag any CLI conflicts).

The plugin source is expected to be in `zc_plugins/` — either as a zip archive (`zc_plugins/<plugin-name>.zip`) or an already-extracted directory. If neither is found, ask before proceeding. For anything more complex (partial conversions, files scattered across the project), the developer will describe the situation explicitly at invocation time.

First confirm that the current branch is safe and clean to work in. Offer to work in a dedicated branch. Don't stage/commit any files without getting confirmation first.

Proceed in steps: plan, then build the directory structure, then build installer code, then upgrade any embedded Composer packages to a newer version.

There are no test suite elements for this plugin, and none need to be created — manual testing will be done by the developer.

In manifest.php, bump to a new major version for this conversion. Prompt to confirm the pluginVersion, pluginAuthor and pluginID (pluginID is the File ID number).

