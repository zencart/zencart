# Encapsulated Template Plugins Implementation Plan

## Progress

Last updated: 2026-04-23

- Phase 1 complete: `TemplateResolver` now merges core and plugin-backed selectable template discovery, caches template records via `TemplateDto`, and is covered by unit tests.
- Phase 2 largely complete: `PageLoader` resolves selected-template, base-template, named-overlay, default-overlay, and `template_default` precedence for template parts and asset enumeration.
- Bootstrap compatibility landed: catalog and admin template init now resolve the selected template through `TemplateResolver` instead of assuming `includes/templates/<template>/`.
- Admin discovery landed: `zen_get_catalog_template_directories()` now returns resolver-backed selectable templates instead of scanning only core template folders.
- Admin selection compatibility landed: `template_select.php` filters plugin-backed templates to installed plugins, validates template keys before save/insert, and resolves `template_init.php`, screenshots, and `template_settings.php` through resolver metadata.
- Language-override compatibility landed: `BaseLanguageLoader` now walks the selected template inheritance chain for template-specific language files and extra definitions, including plugin-backed template language roots.
- Sidebox compatibility landed: `SideboxFinder` now resolves inherited and plugin-backed template sidebox directories through the resolver chain.
- Legacy file-resolution compatibility landed: `functions_files.php` and `functions_templates.php` now resolve template-scoped modules, sideboxes, index filters, html-includes, screenshots, and template init/settings paths through resolver-backed inheritance helpers.
- Template asset fallback landed: `html_output.php` now falls back through the selected template inheritance chain for template and language assets instead of hardcoded `template_default` substitution.
- Dogfooding started: `responsive_classic_dogfood` is now packaged under `zc_plugins/ResponsiveClassic/v1.0.0/` as the first distinct selectable template package candidate, keeping it separate from the built-in `responsive_classic` during migration testing.
- Remaining major work: broaden feature-level coverage, finish compatibility sweeps in admin tooling such as layout/developer workflows, and prove full end-to-end storefront/admin rendering with a selected plugin-backed template.

## Goal

Make Zen Cart templates work as first-class encapsulated plugins, using the same general discovery model as `zc_plugins/<Plugin>/<version>/manifest.php`, while preserving today's template override behavior and keeping migration risk manageable.

This document separates two related capabilities:

- plugin-provided template overlays for an existing selected template
- fully selectable template packages that live inside `zc_plugins`

The recommended delivery path is to ship those in phases rather than trying to replace all template assumptions in one pass.

## Current State

Encapsulated plugins are already discovered from filesystem structure plus `manifest.php`, primarily via:

- `includes/classes/PluginManager.php`
- `includes/application_top.php`
- `admin/includes/application_bootstrap.php`

Storefront runtime already has partial plugin-template support:

- `includes/classes/ResourceLoaders/PageLoader.php` can locate template parts from `zc_plugins/.../catalog/includes/templates/default/...`
- `includes/classes/template_func.php` delegates template resolution to `PageLoader`

That means plugin authors can already ship some storefront template assets such as:

- page templates
- css
- javascript
- shared template partials

However, selectable templates are still treated as a separate system:

- template discovery scans only `includes/templates/*`
- admin template selection assumes template folders live in core paths
- bootstrap defines `DIR_WS_TEMPLATE*` constants using `includes/templates/<template>/`
- many callers still assume template assets live in core template directories

In short:

- plugin overlays are partially supported
- plugin-backed selectable templates are not yet a first-class concept

## Desired End State

Zen Cart should support both of these models:

### 1. Template Overlay Plugin

A normal encapsulated plugin can contribute assets that overlay:

- `default`
- a named template such as `responsive_classic`
- both

These overlays should participate in normal template resolution without requiring file copies into core directories.

### 2. Selectable Template Package

A plugin can optionally provide a complete theme package that:

- is discoverable from admin template selection
- exposes `template_info.php` style metadata
- can provide `template_settings.php`
- can be selected in `template_select`
- resolves storefront assets from the plugin directory instead of a core template directory

## Core Template Positioning

`includes/templates/template_default` should remain in core as the built-in fallback template and compatibility base.

That means:

- `template_default` stays in core code
- encapsulated plugins can override or extend it
- plugin-backed templates can inherit from it directly or indirectly

This gives Zen Cart one stable fallback for:

- bootstrap safety
- legacy compatibility
- template fallback behavior
- progressive migration of existing templates

By contrast, `includes/templates/responsive_classic` should be treated as the first realistic migration target for encapsulation rather than a permanent core requirement.

Recommended direction:

- keep `template_default` in core
- add resolver and overlay support so plugins can override `template_default`
- support fully selectable plugin-backed templates
- migrate `responsive_classic` into `zc_plugins` as the first dogfooded encapsulated template

This keeps the safety net in core while moving the more feature-complete storefront template into the new plugin architecture once the underlying path resolution is ready.

## Child Template Compatibility

This plan should also satisfy the child-template use case described in GitHub discussion #6428:

- avoid copying all of `responsive_classic` just to customize a few files
- allow a derived template to override only the files it needs
- keep inherited behavior coming from the base template
- reduce the long-term maintenance burden of merging upstream template changes back into custom templates

In this model, a child template is simply a plugin-backed template with a declared base template.

Examples:

- `responsive_classic` can declare `template_default` as its base
- a custom storefront theme can declare `responsive_classic` as its base
- a minimal brand theme can override only a small number of files while inheriting everything else

This means the encapsulated-template architecture should not only support standalone plugin-backed templates, but also one-level child templates as a first-class use case.

### Initial Scope for Child Templates

The first supported child-template behavior should focus on code and template-file inheritance, since that was the main use case described in the discussion.

Initial support should cover:

- template files under `includes/templates/...`
- common partials
- sideboxes
- template-level images
- template-scoped css and javascript lookup using the same fallback chain

Initial support should avoid introducing unlimited inheritance depth.

Recommended rule:

- support one explicit `baseTemplate`
- allow `responsive_classic -> template_default`
- allow one custom child to inherit from `responsive_classic`
- do not design for arbitrary deep inheritance chains in the first release

### Open Questions from the Child Template Discussion

The discussion surfaces a few design questions that this plan needs to keep in scope:

- how css and javascript precedence should work when both child and base provide similar assets
- how a child template intentionally suppresses a base asset
- how plugin installation instructions should target parent vs child templates
- how overrides outside `includes/templates`, such as modules and language files, should participate in base-template inheritance

The current plan addresses those by:

- centralizing precedence in the resolver and `PageLoader`
- keeping inheritance shallow
- treating non-template override directories as part of the compatibility sweep
- using `responsive_classic` migration as the real-world proving ground

## Recommended Delivery Strategy

Implement this in two milestones.

### Milestone A: Formalize Plugin Template Overlays

Scope:

- keep `template_default` as the core fallback template
- support plugin assets for `default` and named templates
- improve resolver behavior and precedence
- no major admin template-selection redesign yet

Outcome:

- plugins can cleanly ship template-specific assets inside `zc_plugins`
- this extends the existing partial support without destabilizing template selection

### Milestone B: Add Selectable Encapsulated Templates

Scope:

- template plugin metadata in `manifest.php`
- admin discovery for plugin-backed templates
- path abstraction for selected template roots
- bootstrap updates for plugin-backed template assets
- prepare `responsive_classic` to become the first migrated encapsulated template

Outcome:

- complete templates can live in `zc_plugins` and be selected alongside core templates

## Proposed Directory Conventions

### Overlay Plugin

```text
zc_plugins/<Plugin>/<version>/
  manifest.php
  catalog/
    includes/
      templates/
        default/
          css/
          jscript/
          common/
          templates/
          sideboxes/
        responsive_classic/
          css/
          jscript/
          common/
          templates/
          sideboxes/
```

### Selectable Template Package

```text
zc_plugins/<Plugin>/<version>/
  manifest.php
  catalog/
    includes/
      templates/
        <templateKey>/
          template_info.php
          template_settings.php
          common/
          css/
          images/
          jscript/
          sideboxes/
          templates/
```

A template plugin may also ship:

- `default/` for shared fallback assets
- `<templateKey>/` for the actual selectable template root

## Manifest Changes

Extend plugin manifests with explicit template metadata.

Recommended shape:

```php
return [
    'pluginVersion' => 'v1.0.0',
    'pluginName' => 'Example Theme',
    'pluginDescription' => 'Selectable theme packaged as an encapsulated plugin.',
    'pluginAuthor' => 'Zen Cart Team',
    'pluginId' => 0,
    'zcVersions' => [],
    'template' => [
        'key' => 'example_theme',
        'baseTemplate' => 'responsive_classic',
    ],
];
```

The presence of `template` with a `key` declares a selectable template plugin.

For the common case, the resolver should infer metadata paths from `template.key`:

- `catalog/includes/templates/example_theme/template_info.php`
- `catalog/includes/templates/example_theme/template_settings.php`

The base convention should remain:

```text
zc_plugins/<Plugin>/<version>/
  catalog/
    includes/
      templates/
        <templateKey>/
          template_info.php
          template_settings.php
```

`infoFile` and `settingsFile` should be optional escape hatches only for non-standard package layouts:

```php
'template' => [
    'key' => 'example_theme',
    'baseTemplate' => 'responsive_classic',
    'infoFile' => 'custom/path/template_info.php',
    'settingsFile' => 'custom/path/template_settings.php',
],
```

This keeps normal template plugins convention-based while still allowing rare advanced packages to override metadata paths.

Overlay-only plugins do not need manifest hints. PageLoader discovers overlay directories from installed plugin files:

```text
zc_plugins/<Plugin>/<version>/catalog/includes/templates/default/
zc_plugins/<Plugin>/<version>/catalog/includes/templates/responsive_classic/
```

Notes:

- `baseTemplate` is important for fallback behavior
- metadata should be optional so existing plugins remain valid
- `baseTemplate` also doubles as the child-template inheritance declaration
- `infoFile` and `settingsFile` should not be required when the files live in the conventional template root

## Architecture Changes

### 1. Introduce a Template Resolver

Add a small service responsible for turning a template key into a resolved location and metadata record.

Suggested responsibilities:

- determine whether a template is core-backed or plugin-backed
- return template root filesystem path
- return template root web path
- return metadata from `template_info.php`
- return base template for fallback
- return whether a template is acting as a child of another template
- return selectable template records declared by plugin manifests

Suggested file:

- `includes/classes/ResourceLoaders/TemplateResolver.php`

Suggested methods:

- `getSelectableTemplates(): array`
- `getTemplateRecord(string $templateKey): ?array`
- `getTemplateFilesystemPath(string $templateKey): ?string`
- `getTemplateCatalogPath(string $templateKey): ?string`
- `getTemplateWebPath(string $templateKey): ?string`
- `getBaseTemplate(string $templateKey): string`
- `getTemplateInheritanceChain(string $templateKey): array`
- `isPluginTemplate(string $templateKey): bool`

This service becomes the seam between old template assumptions and new plugin-backed locations.

Current implementation notes:

- `TemplateResolver` stores the discovered records in `TemplateDto`, so callers reuse a single merged template map during a request.
- each template record now carries resolver-owned location fields such as `template_path`, `template_catalog_path`, `template_web_path`, and `template_settings_path`
- plugin-backed records also carry `plugin_key`, `plugin_version`, `template_source`, `manifest`, and `has_template_settings`
- base-template inheritance is normalized onto `base_template`, with `template_default` appended as the final fallback when available

### 2. Separate Template Identity from Template Filesystem Root

Today `template_dir` effectively means both:

- the template identifier stored in the database
- the folder name under `includes/templates`

That should change to:

- `template_dir` remains the public identifier
- resolver determines the actual filesystem location

This preserves DB compatibility while allowing plugin-backed template roots where appropriate.

Important constraint:

- `template_default` should continue to resolve to the core template directory
- not every template key needs to map to a plugin-backed location
- the resolver must support a mixed world of core-backed and plugin-backed templates

### 3. Generalize PageLoader Template Resolution

`PageLoader` already resolves some template assets from plugin paths, but it assumes plugin assets live under `catalog/includes/templates/default/...`.

It should instead resolve with a defined precedence model that can account for:

- the selected template root
- the selected template base template
- plugin overlays targeting the selected template
- plugin overlays targeting `default`
- `template_default`

Recommended precedence:

1. selected template page-specific asset
2. selected template shared asset
3. enabled plugin overlay targeting selected template
4. enabled plugin overlay targeting selected template's base template
5. enabled plugin overlay targeting `default`
6. `template_default`

This precedence should be centralized in one place and reused by:

- `getTemplateDirectory`
- `getTemplatePart`
- page-body resolution
- css/js discovery

For child-template behavior, this is the core rule set that determines how a derived theme inherits from its base without copying all files.

### 4. Generalize Template Discovery in Admin

`zen_get_catalog_template_directories` currently scans only `DIR_FS_CATALOG_TEMPLATES`.

It should merge:

- core templates from `includes/templates`
- plugin-backed selectable templates discovered from manifests

Suggested behavior:

- overlay-only plugins are not listed in `template_select`
- plugin-backed templates appear alongside core templates
- plugin-backed templates are only selectable in admin when their owning plugin package is installed
- missing plugin-backed templates should still show a useful warning if selected but unavailable

Primary touchpoints:

- `includes/functions/functions_templates.php`
- `admin/template_select.php`
- `admin/layout_controller.php`

### 5. Rework Template Bootstrap Constants

Current bootstrap defines:

- `DIR_WS_TEMPLATE`
- `DIR_WS_TEMPLATE_IMAGES`
- `DIR_WS_TEMPLATE_ICONS`

using hard-coded core template paths.

For plugin-backed selected templates, those need to resolve through the new resolver, while core-backed templates must continue to work unchanged.

This is a high-risk area because many files use the constants directly rather than going through `$template->get_template_dir(...)`.

Recommended approach:

- keep the constant names for backward compatibility
- source their values from the resolved template record
- add helper methods for web-path generation where constants are not sufficient

Primary touchpoints:

- `includes/init_includes/init_templates.php`
- `admin/includes/init_includes/init_templates.php`
- `includes/functions/html_output.php`

### 6. Decide Template Plugin Enablement Rules

Template selection should not feel coupled to generic plugin enable/disable state in confusing ways.

Recommended rules:

- selectable template packages are
  - discoverable if installed via the admin's `Plugin Manager`
  - **not** enabled or disabled via the `Plugin Manager`; the `Template Selection` tool provides that function.

- template overlays only apply when their plugin is enabled
- selecting a plugin-backed template does not require the template plugin to behave like a normal runtime feature plugin unless it also ships behavioral code
- `template_default` remains available regardless of plugin state

This may require a distinction between:

- installed plugin package
- enabled overlay behavior
- selected active template

Primary touchpoints:

- `includes/classes/ViewBuilders/PluginManagerController.php`

## Implementation Phases

## Phase 0: Document and Lock Conventions

Tasks:

- agree on overlay vs selectable-template terminology
- finalize manifest keys
- finalize directory conventions
- document precedence rules
- document the initial child-template scope and inheritance-depth limit

Deliverables:

- this doc
- a brief contributor-facing convention note after implementation starts

## Phase 1: Template Resolver Foundation

Status: complete

Tasks:

- add `TemplateResolver`
- support current core templates without behavior changes
- expose merged template records for core templates
- expose base-template relationships for child-template resolution

Deliverables:

- merged core/plugin selectable-template discovery
- resolver-backed metadata and path records for template consumers
- tests for resolver output on existing core and plugin-backed templates

## Phase 2: Formalize Overlay Plugins

Status: mostly complete

Tasks:

- extend `PageLoader` to look for named-template overlays in plugins, not just `default`
- define overlay precedence across selected template, overlays, and fallback
- update asset enumeration for css/js/common/template parts
- prove that a child template can inherit from a base template without full duplication

Deliverables:

- plugin authors can target `default` or named templates cleanly
- existing plugin-provided template assets continue to work
- inheritance-aware sidebox, helper-path, and HTML asset fallback behavior now uses the same resolver model

Suggested first targets:

- `includes/classes/ResourceLoaders/PageLoader.php`
- `includes/classes/template_func.php`

## Phase 3: Admin Discovery of Plugin Templates

Status: in progress

Tasks:

- extend `zen_get_catalog_template_directories`
- allow plugin-backed `template_info.php`
- display plugin template records in template admin pages

Deliverables:

- plugin-backed selectable templates are visible in admin alongside core templates
- `template_select.php` now validates selectable template keys and uses resolver-backed init/settings/screenshot paths

Suggested first targets:

- `includes/functions/functions_templates.php`
- `admin/template_select.php`
- `admin/layout_controller.php`

## Phase 4: Bootstrap Plugin-Backed Selected Templates

Status: in progress

Tasks:

- resolve selected template path through `TemplateResolver`
- update `DIR_WS_TEMPLATE*` constants
- load plugin-backed `template_settings.php`
- ensure runtime web paths work for images/icons/css/js

Deliverables:

- a plugin-backed template can actually be selected and rendered without changing core-template behavior
- catalog and admin bootstrap now source `DIR_WS_TEMPLATE*` values from the resolved template record

Suggested first targets:

- `includes/init_includes/init_templates.php`
- `admin/includes/init_includes/init_templates.php`
- `includes/functions/html_output.php`

## Phase 5: Compatibility Sweep

Status: in progress

Tasks:

- audit direct `DIR_WS_TEMPLATE*` consumers
- audit file existence checks that assume core paths
- audit language/template override helpers
- audit admin tools that search template directories

Deliverables:

- reduced hard-coded path assumptions
- clear follow-up list for any deferred compatibility gaps
- current sweep includes base language loading, sidebox lookup, helper-directory resolution, and template/language asset fallback

## Test Strategy

Add tests in small layers.

### Unit Tests

Cover:

- manifest parsing for template metadata
- resolver output for core templates
- resolver output for plugin-backed templates
- inheritance-chain resolution for child templates
- precedence rules for overlay lookup

### Integration / Feature Tests

Cover:

- admin template list includes plugin-backed templates alongside core templates
- selecting a plugin-backed template persists correctly
- storefront resolves main template file from a plugin-backed template
- storefront resolves inherited template assets from a child template's base template
- plugin overlay asset is found for selected named template
- fallback to `template_default` still works

### Regression Cases

Must verify:

- classic core templates still behave unchanged
- existing overlay-style plugins such as GDPR DSAR and POSM continue to load their assets
- direct asset helpers such as `zen_image` do not generate broken URLs

## Current Test Coverage Status

The current test suite covers a meaningful subset of resolver behavior, but it does not yet prove that every legacy template override surface works end-to-end with encapsulated templates.

Currently covered:

- selectable plugin-backed template discovery and metadata
- plugin-backed template filesystem and web path records
- base-template inheritance chains
- plugin-backed template overriding a core template key
- custom `template_settings.php` path support
- template search directories for admin/developer-tool style lookups
- template language override directories
- template-first language directories
- template init file path resolution
- template screenshot web path resolution
- `PageLoader::getTemplateDirectory()` fallback from child template to base template
- named overlay lookup before default fallback
- CSS/template-part merge behavior across child, base, and overlay sources
- sidebox discovery across inherited and plugin template paths
- sidebox path resolution for inherited and plugin template paths
- template-aware `zen_get_module_directory()`
- template-aware `zen_get_module_sidebox_directory()`
- template-aware `zen_get_index_filters_directory()`
- `zen_get_file_directory()` fallback through inherited template directories
- template image and language asset fallback through HTML output helpers

Relevant unit test files:

- `not_for_release/testFramework/Unit/testsTemplateResolver/TemplateResolverTest.php`
- `not_for_release/testFramework/Unit/testsTemplateResolver/PageLoaderTemplateResolutionTest.php`
- `not_for_release/testFramework/Unit/testsTemplateResolver/FunctionsFilesTemplateResolutionTest.php`
- `not_for_release/testFramework/Unit/testsTemplateResolver/SideboxFinderTemplateResolutionTest.php`
- `not_for_release/testFramework/Unit/testsTemplateResolver/BaseLanguageLoaderTemplateChainTest.php`
- `not_for_release/testFramework/Unit/testsTemplateResolver/HtmlOutputTemplateAssetFallbackTest.php`
- `not_for_release/testFramework/Unit/testsTemplateResolver/FunctionsTemplatesTest.php`

Still not fully covered:

- full storefront request rendering using a selected plugin-backed template
- `template_select.php` selecting a plugin-backed template and persisting it in a feature-level admin flow
- runtime bootstrap constants such as `DIR_WS_TEMPLATE`, `DIR_WS_TEMPLATE_IMAGES`, and `DIR_WS_TEMPLATE_ICONS` across plugin-backed templates in full request rendering
- full CSS/JS loader behavior in real page rendering, including ordering and duplicate handling
- `template_settings.php` behavior in admin/layout controller beyond path resolution
- `template_init.php` execution behavior beyond resolver-backed path resolution
- page-specific overrides under `templates/`, `common/`, `sideboxes/`, `modalboxes/`, and `menu_templates` in a full request
- module overrides beyond targeted helper-path tests
- language override behavior in a full storefront/admin request lifecycle
- admin developer-tool search behavior as an end-to-end feature
- define pages / `html_includes` editing workflow in admin
- layout controller sidebox editing for plugin-backed selected templates
- behavior when a plugin-backed selected template is disabled, missing, or uninstalled
- precedence collision tests where child, base, selected-template overlay, default overlay, and `template_default` all provide the same file
- suppression/removal semantics for inherited CSS/JS assets, if that becomes a supported feature

The next required test layer should be feature-level coverage that selects `responsive_classic_dogfood` and renders real storefront/admin pages while asserting that specific files resolve from plugin, base, overlay, and `template_default` locations.

## Suggested First Reference Implementations

### Overlay Reference

Use an existing plugin with storefront template assets, such as:

- GDPR DSAR
- POSM

Goal:

- prove named-template overlay support without changing template selection

### Selectable Template Reference

Create a minimal proof-of-concept template plugin that:

- declares template capability in `manifest.php`
- ships `template_info.php`
- extends `responsive_classic`
- overrides one template file, one stylesheet, and one image asset

Keep it intentionally small so debugging path resolution is easier.

This proof of concept should double as the first child-template test case:

- a thin custom template inheriting from `responsive_classic`
- only a few overridden files
- everything else inherited through the base-template chain

### First Core Migration Target

After the proof of concept works, migrate `responsive_classic` out of `includes/templates/responsive_classic` and into `zc_plugins` as the first real encapsulated template package.

Rationale:

- it is a realistic production template
- it exercises more of the system than a tiny demo theme
- it lets core dogfood the plugin-template architecture
- it avoids destabilizing `template_default`, which should remain the permanent safety net

## Risks

### Direct Constant Usage

Many callers use `DIR_WS_TEMPLATE_IMAGES` and related constants directly. Those usages may bypass newer resolver logic and expose incorrect web paths for plugin-backed selected templates.

### Mixed Filesystem and Web Paths

Some code treats template paths as filesystem paths, some as web paths. Plugin-backed templates will make that distinction more visible, so the resolver API should keep those separate.

### Admin Tooling Assumptions

Developer and layout tools often assume templates live only under `includes/templates`. Those areas need a compatibility sweep after the primary runtime works.

### Enablement Semantics

If template packages and behavior plugins share too much state, users may end up with confusing interactions between plugin enable/disable and template selection.

## Out of Scope for Initial Delivery

- redesigning the template database schema unless resolver indirection proves insufficient
- changing all legacy template APIs at once
- forcing all existing plugins to add template metadata immediately
- removing `template_default` from core

## Recommended First Work Items

1. Add feature-level tests that select `responsive_classic_dogfood` and render real storefront/admin flows.
2. Finish the compatibility sweep for remaining admin tooling, especially layout/developer workflows.
3. Verify full CSS/JS ordering and duplicate-handling behavior in page rendering.
4. Create or refine a tiny proof-of-concept selectable child template plugin for inheritance-focused testing.
5. Migrate `responsive_classic` into `zc_plugins` as the first full reference implementation once feature coverage is in place.

## Success Criteria

The feature is successful when all of the following are true:

- a plugin can provide template overlays for `default` and named templates
- a plugin can provide a selectable template package when needed
- admin template selection can see and choose plugin-backed templates alongside core templates
- storefront rendering resolves template assets from plugin-backed roots correctly when a plugin-backed template is selected
- fallback to `template_default` still works
- existing core templates and current plugins remain compatible
