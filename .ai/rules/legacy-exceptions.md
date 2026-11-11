---
paths:
  - "includes/classes/**"
---

# Legacy exceptions in `includes/classes/`

Several core classes here predate PSR adoption. The deviations below are known and accepted.
Do not rename them, restructure them, reformat them to PSR-12, or retrofit
`declare(strict_types=1)` into them unless a deliberate refactor has been scoped and agreed upon
by core developers. Plugins, template overrides, and third-party code instantiate these classes by
name, so renaming one is a major backwards-compatibility break.

This is the per-file detail behind the "Accepted legacy exceptions" section of `CONVENTIONS.md`.

## Lowercase class names (do not rename)

| File | Class as declared |
|---|---|
| `order.php` | `order` |
| `category_tree.php` | `category_tree` |
| `class.notifier.php` | `notifier` |
| `template_func.php` | `template_func` |
| `breadcrumb.php` | `breadcrumb` |
| `language.php` | `language` |
| `currencies.php` | `currencies` |
| `products.php` | `products` — deprecated; do not use it in new code |

## snake_case filename, camelCase class

Accepted mismatches between filename and class name. Leave both as they are.

| File | Class as declared |
|---|---|
| `split_page_results.php` | `splitPageResults` |
| `http_client.php` | `httpClient` |

`http_client.php` is stable legacy code that is little used in new work — leave it alone unless a
task specifically targets it. It has already been modernized (it carries `declare(strict_types=1)`
and explicit visibility on every method), so it is no longer an exception to those two rules.

## Observers

`observers/` is sometimes described as uniformly lowercase. It is not: only
`products_viewed_counter` uses the old lowercase auto-loading name. The others
(`zcObserverDownloadsViaAws`, `zcObserverDownloadsViaRedirect`,
`zcObserverDownloadsViaStreaming`, `zcObserverDownloadsViaUrl`,
`zcObserverNonCaptchaObserver`) already use StudlyCaps and are not exceptions. Accept the one
lowercase name where it is; name new observers in StudlyCaps.

## What is not excused here

These exceptions are per-file. New files added under `includes/classes/` follow `CONVENTIONS.md`
in full: StudlyCaps class names, `declare(strict_types=1)`, PSR-12, and curly-brace control
structures. Being adjacent to a legacy class does not make a new one exempt.
