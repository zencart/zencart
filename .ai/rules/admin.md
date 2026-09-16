---
paths:
  - "admin/**"
---

# Admin (`admin/`)

## Bootstrap and configuration

- Entry: `admin/index.php` uses `admin/includes/application_top.php` and `application_bootstrap.php`, which load admin pages or plugin admin pages.
- There is no admin-specific configure.php: admin reads `includes/configure.php`. In dev, `admin/includes/local/configure.php` or `includes/local/configure.php`, if present, override it. A leftover `admin/includes/configure.php` from an older install is still honored but should be deleted.
- Never edit directly: `admin/includes/application_top.php`, `admin/includes/application_bootstrap.php`, `admin/includes/defined_paths.php`. Use `admin/includes/extra_configures/` to define new `DIR_FS_*` / `DIR_WS_*` constants and `admin/includes/init_includes/` for init hooks.
- New admin pages should be built as plugins (`zc_plugins/<unique_key>/<version>/admin/`), not dropped into `admin/`.
- Per-page JavaScript: `admin/includes/javascript_loader.php` loads `admin/includes/javascript/<page>.js`, `<page>.php`, and `<page>_*.js` / `<page>_*.php` for the current `admin/<page>.php`.

## What is not available in admin

- `$tplSetting` (TemplateSettings) is initialized only in the catalog bootstrap (`includes/init_includes/init_templates.php`). Never use it in code reachable from `admin/*`; use `zen_config('KEY', $default)` instead.
- Ajax classes (`includes/classes/ajax/*`, dispatched via `ajax.php`) sit under catalog directories even when only ever invoked from admin; the handler checks `IS_ADMIN_FLAG`, not file location. Trace actual callers before assuming the execution context.
- Some functions are reachable from both admin and catalog (for example several in `includes/functions/functions_products.php`). Trace callers before adapting a shared function to avoid fatal/undefined-property errors from the other context.

## Security model (read before reporting a finding)

- Input sanitization: admin request handling uses more than one sanitization stage (`admin/includes/classes/AdminRequestSanitizer.php` and its whitelist). Do not judge a field unprotected from a single helper; review the whole request pipeline. Before relaxing sanitization for any admin field, follow the [Admin Sanitization docs](https://docs.zen-cart.com/dev/code/admin_sanitization/).
- Authorization is enforced centrally during bootstrap, not inside each page. A page without an explicit permission check is not automatically a finding. When adding admin pages or reports, decide whether existing permissions suffice.
- CSRF validation may be enforced globally; verify request initialization before reporting missing CSRF protection on a page.
- Some administrator-managed content areas intentionally allow richer markup. Treat that as a design decision unless untrusted users can influence the content.
- Data exports: several export features share directories. Review generated-file security, storage location, and cleanup.
- Installation and setup: admin lockout mechanisms and installation safeguards may be implemented separately; review both initialization and installation paths before concluding.
