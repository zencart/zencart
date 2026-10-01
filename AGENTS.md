# AGENTS.md — Zen Cart

Concise guidance for AI coding agents (and humans) working in this Zen Cart v3.0 PHP codebase:
where to look, how the app boots, test and dev commands, conventions, and integration points.
Every tool loads this file. Topic-specific guidance is split into `.ai/` and indexed at the
end of this file so it loads only when relevant.

## Related files

- `CONVENTIONS.md` — coding standards, PSR-12 rules, naming conventions, legacy exceptions. Read it before writing code.
- `.ai/README.md` — how the topic guides and skills under `.ai/` are organized and synced to each tool.
- `CLAUDE.md` — Claude Code layer (imports this file).
- `dev-REVIEW-CALIBRATION.md` — optional gitignored private review-calibration notes. If present, agents doing code review should read it but must not quote or commit it.

## Scratch / working files

Audit reports, one-off scripts, and other working artifacts produced during a task
(e.g. `audit_*.md`, `phase*_*.php`, `*_audit.stats`) belong in `tmp/` at the project root,
not the repo root. `tmp/` is gitignored. Create it if it doesn't exist.

## Quick orientation (high-value entry points)

- Root storefront entry: `index.php` (loads `includes/application_top.php`, uses `PageLoader` to pull page modules and requisite plugins).
- Admin entry: `admin/index.php` (uses `admin/includes/application_top.php` and `application_bootstrap.php`). Admin reads the same `includes/configure.php` as the catalog; there is no admin-specific configure.php.
- Central bootstrap/config: `includes/configure.php`, `includes/application_top.php`, `includes/application_bottom.php`.
- Core classes: `includes/classes/`; per-page or per-feature modules: `includes/modules/`.
- Plugins: `zc_plugins/` (versioned directories with catalog/admin subfolders).
- Autoload: `includes/psr4Autoload.php` handles most classes; `includes/classes/vendors/` holds bundled 3rd-party libraries loaded only when needed. An autoloader system handles feature initialization during bootstrap.
- Tests and test bootstrap: `phpunit.xml` and `not_for_release/testFramework/`.
- Composer is only used for the test suite: `composer.json`, `vendor/`. No production PHP libraries are required aside from PHP extensions; the app uses its own autoloading and plugins manage their own dependencies.

## Big-picture architecture

- Procedural, page-per-file entrypoints. Each visible page is a thin entry that includes `application_top.php`, then pulls per-page header PHP modules from `includes/modules/pages/PAGE_NAME/` and template fragments.
- Bootstrapping: `includes/application_top.php` constructs an `InitSystem` that reads configurations from `includes/auto_loaders/` and runs them from `includes/init_includes/` and plugin-provided loaders.
- Plugin namespaces are mapped into PSR-4 prefixes during bootstrap. Plugin model: `zc_plugins/<unique_key>/<version>/catalog|admin/...`. `PluginManager` registers installed plugins and the `FileSystem` helper loads files from plugin directories.
- Templates: template selection is handled by the `$template` object and `includes/templates/*` paths; template-specific overrides live under `includes/templates/TEMPLATE/`.

## Key developer workflows

- Install test-suite dependencies: `composer install`.
- Tests (composer scripts in `composer.json`): `composer run-script tests-unit`, `composer run-script tests-feature`, plus `-parallel`, `-store` and `-admin` variants. `phpunit.xml` uses `vendor/autoload.php` and sets APP_ENV=testing.
- Static analysis: `composer run-script phpstan:admin`, `composer run-script phpstan:catalog`. Lint: `php -l`.
- Docs vs code consistency: `composer docs-check` (runs in CI on every push). After changing, moving, or deleting anything that this file, `CONVENTIONS.md`, `README.md` or `.ai/` mentions, re-run it and fix what it reports. After editing anything under `.ai/`, run `composer docs-sync` to regenerate the tool copies and the index below.
- Quick local smoke test: `php -S 127.0.0.1:8000 -t .` from the project root. The app expects `includes/configure.php` to exist; copy `includes/dist-configure.php`, adjust `DIR_FS_CATALOG` and the DB constants, make it writable, and make `cache/` and `logs/` writable.
- The app has no intended CLI entrypoints. For ad-hoc scripts that need the bootstrap:
  ```php
  <?php
  require 'includes/application_top.php';
  // ... logic that depends on DB and bootstrapped services
  require DIR_WS_INCLUDES . 'application_bottom.php'; // close session and cleanup
  ```

## Project-specific conventions and patterns

- Entrypoints are procedural files that require `application_top.php` and later `application_bottom.php`.
- File/constant mapping: filenames are registered via `includes/init_includes/init_file_db_names.php` (loading `filenames.php`) and plugin `filenames.php`; search for the `FILENAME_` constant convention. Table names are `TABLE_*` constants from `includes/database_tables.php`; never use raw or prefixed table names in SQL.
- Autoloading: PSR-4 for core and plugins at runtime (`Aura\Autoload` plus `includes/psr4Autoload.php`, registered in `application_top.php`). For the test suite, `composer.json` classmaps `includes/classes` and `includes/modules`.
- Security and input sanitation: `application_top.php` includes early request-sanitizing logic (rejects suspicious query strings, parameter pollution, crawler `buy_now` attempts). Changes to routing/inputs must preserve these checks. Call `zen_output_string_protected()` on any output that includes user input. Admin sanitization has its own whitelist system; see the admin guide.
- Files that must never be edited directly: `includes/application_top.php`, `admin/includes/application_top.php`, `includes/defined_paths.php`, `admin/includes/defined_paths.php`. Use `extra_configures` and `init_includes` hooks instead.
- Configuration values: `zen_config('KEY', $default)` works everywhere and is the default choice. `$tplSetting->KEY` (TemplateSettings) exists only in the catalog bootstrap and only for display/template-presentation settings; the templates guide has the full rules.
- Ajax classes (`includes/classes/ajax/*`, dispatched via `ajax.php`) sit under catalog directories even when only invoked from admin; the handler checks `IS_ADMIN_FLAG`, not file location. Some functions (e.g. in `includes/functions/functions_products.php`) are reachable from both sides; trace callers before changing shared code.
- Payment/webhook listeners at the repo root are PayPal-specific: `ipn_main_handler.php`, `ppr_listener.php`, `ppr_webhook.php`.

## Troubleshooting

- Enable `STRICT_ERROR_REPORTING` to turn `display_errors` on. Logs are in `logs/`.
- To trace autoload config load order, set `DEBUG_AUTOLOAD=true` in a local `includes/local/configure.php`.

## Files to inspect next

```
- includes/configure.php (or in dev, includes/local/configure.php if it exists, which overrides settings for local development)
- includes/application_top.php (never edit)
- includes/extra_configures/
- includes/defined_paths.php (never edit; use extra_configures for new DIR_FS_* / DIR_WS_* constants)
- includes/init_includes/
- includes/modules/pages/
- zc_plugins/
- composer.json and phpunit.xml
- not_for_release/testFramework/Support/application_testing.php
```
Admin follows the same pattern under `admin/`; see the admin guide below.

## Topic guides and skills

<!-- ai-index:start -->
Topic guides and skills live under `.ai/` (see `.ai/README.md`). Tools that auto-load path-scoped rules (Claude Code from `.claude/rules/`, Copilot from `.github/instructions/`) or skills (Claude Code, Copilot, Codex) receive these automatically. If yours does not, or the task does not touch a listed path, read the file yourself before planning or editing.

| Read when working on | Guide |
|---|---|
| `admin/**` | `.ai/rules/admin.md` (Admin (`admin/`)) |
| `zc_plugins/**` | `.ai/rules/plugins.md` (Plugins (`zc_plugins/`)) |
| `includes/templates/**`, `includes/modules/pages/**` | `.ai/rules/templates.md` (Storefront templates, pages and page modules) |
| `not_for_release/testFramework/**`, `phpunit.xml`, `composer.json` | `.ai/rules/testing.md` (Test suite) |

| Skill | Use when | File |
|---|---|---|
| `checkout` | Use for any task touching the shopping cart, checkout pages, order creation, payment or shipping modules, order totals, discounts, coupons, gift vouchers. | `.ai/skills/checkout/SKILL.md` |
| `convert-plugin` | Use when asked to convert, encapsulate, or modernize an old plugin. | `.ai/skills/convert-plugin/SKILL.md` |
| `create-plugin` | Use when asked to build, scaffold, or add a plugin, an admin page, an observer, or a payment/shipping/order-total module as a plugin. | `.ai/skills/create-plugin/SKILL.md` |
| `doc-drift` | Use when asked to check, audit, or refresh the docs, after renaming or deleting files the docs mention, or when CI's docs-check job fails. | `.ai/skills/doc-drift/SKILL.md` |
| `new-storefront-page` | Use when asked to create or add a new catalog page, a custom page, or a page module. | `.ai/skills/new-storefront-page/SKILL.md` |
| `product-listing` | Use for any task touching product listings, category pages, product info pages, search results, prices, attributes, product images, or sorting and pagination of products. | `.ai/skills/product-listing/SKILL.md` |
| `zc-code-review` | Use when asked to review, audit, critique, or assess a PR, patch, diff, branch, or commit in this repository. | `.ai/skills/zc-code-review/SKILL.md` |
<!-- ai-index:end -->

## References

- Developer docs: https://docs.zen-cart.com/dev/
- Plugin developer docs: https://docs.zen-cart.com/dev/plugins/
- Project README: `README.md`
