# GitHub Copilot Instructions — Zen Cart

Read `CONVENTIONS.md` for the full coding standards reference.
Read `AGENTS.md` for architecture, bootstrapping, and codebase orientation.
The following is a summary of the most important points for code review.

---

## Core principle

This is a mature codebase. Flag violations in **new or modified code only**.
Do not flag legacy patterns in files that were not part of the change under review.
See the "Accepted legacy exceptions" section below.

---

## What to flag in reviews

- Colon/endkeyword control structure syntax (`if (): ... endif;`, `foreach (): ... endforeach;`).
  This project always uses curly-brace syntax, including in templates.
- Raw or prefixed table names in SQL (e.g. `zen_orders`, `orders`).
  Always use the `TABLE_*` constants defined in `includes/database_tables.php` (or defined in a plugin).
- User-supplied output not wrapped in `zen_output_string_protected()`.
- Direct `$_GET`/`$_POST`/`$_REQUEST` access in new code where sanitized helpers exist.
- New class files missing `declare(strict_types=1)`.
- New classes, interfaces, or traits not using StudlyCaps naming.
- Direct edits to `includes/application_top.php`, `admin/includes/application_top.php`,
  `includes/defined_paths.php`, or `admin/includes/defined_paths.php`.
  Changes to these files should use hooks (`extra_configures`, `init_includes`) instead.
- New production dependencies added to `composer.json`.
  Composer is for the test suite only; production code uses its own autoloading.

---

## What NOT to flag (accepted legacy exceptions)

Do not flag the following in files that already contain them and were not introduced by this PR:

- Lowercase or snake_case class names in legacy files:
  `order`, `category_tree`, `notifier`, `template_func`, `breadcrumb`, `language`, `currencies`
- Missing method visibility in `http_client.php` (known tech debt, stable, do not modify)
- Missing `declare(strict_types=1)` in existing files
- Closing `?>` tags in procedural include files under
  `includes/modules/`, `includes/init_includes/`, `includes/extra_configures/`
- Legacy observer class naming under `includes/classes/observers/`

---

## Plugin work

New features should be implemented as plugins under `zc_plugins/<unique_key>/<version>/`
rather than as core edits. Flag PRs that add feature code directly to core when a plugin
would be the appropriate pattern.

---

## Security

- Flag any output of user-supplied data that bypasses `zen_output_string_protected()`.
- Flag admin fields that relax input sanitization without a reference to the
  [Admin Sanitization docs](https://docs.zen-cart.com/dev/code/admin_sanitization/).

