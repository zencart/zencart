# Zen Cart Coding Conventions

This is the authoritative reference for coding standards in this codebase.
All AI tools, automated agents, and human contributors should follow these conventions.
See also `AGENTS.md` for architecture, bootstrapping, and workflow details.

---

## Guiding principle: stability over purity

This is a mature, actively-used codebase. **Do not refactor stable code solely to satisfy a convention.**
Conventions apply to *new* code and to code that is already being modified for another reason.
When in doubt: if it works and you weren't asked to touch it, leave it alone.

---

## PHP standard: PSR-12

New code follows [PSR-12](https://www.php-fig.org/psr/psr-12/).
Formatting basics are already enforced by `.editorconfig` (4 spaces, LF line endings, UTF-8,
final newline, no trailing whitespace) — configure your editor to respect it.
Beyond `.editorconfig`, follow PSR-12 for brace placement, spacing, and structure.

There is currently no automated PHPCS enforcement. Conventions are maintained by code review and AI tooling guidance.

### One PSR-12 rule that is stricter here: no colon/endkeyword syntax

PSR-12 does not prohibit the alternative colon syntax for control structures. This project does.

**Always use curly-brace syntax:**
```php
// Correct
if ($condition) {
    // ...
}

foreach ($items as $item) {
    // ...
}
```

**Never use colon/endkeyword syntax — not even in templates:**
```php
// Wrong — do not use
if ($condition):
    // ...
endif;

foreach ($items as $item):
    // ...
endforeach;
```

---

## Comment style for new code

- Single-line comments: `//` is fine.
- Multi-line comments: use `/** */` docblock style, not a run of `//` lines — even when the
  comment isn't above a class/method/property declaration (e.g. explaining a block of logic
  mid-method). PHPStorm renders `/**` as a doc-comment regardless of what follows, and some
  tooling (minifiers, AI code-stripping passes) targets `//` line comments more aggressively,
  so doc-style blocks are more reliably preserved across multiple lines.

```php
// Correct — single line
$total = $price * $qty; // apply quantity

/**
 * Correct — multi-line, documents the code immediately below.
 */
class Foo
{
}

/**
 * Correct — also fine mid-method, documenting the next statement.
 */
$result = doSomethingNonObvious();

// Wrong — multi-line using //
// Explain the non-obvious reason here.
// across multiple lines like this.
```

---

## Naming conventions for new code

| Construct | Convention | Example |
|---|---|---|
| Classes, interfaces, traits | StudlyCaps | `PluginManager`, `ScriptedInstaller` |
| Methods | camelCase | `getProductName()` |
| Properties | camelCase | `$orderTotal` |
| Constants | UPPER_SNAKE_CASE | `TABLE_ORDERS`, `FILENAME_INDEX` |
| Procedural functions | snake_case | `zen_get_products_name()` |

### `declare(strict_types=1)`

Add `declare(strict_types=1)` to all **new** class files, placed after the opening `<?php` tag
and the file's docblock comment, before the namespace declaration.
Do not add it retroactively to existing files unless they are being substantially rewritten.
Do add it to new class files that have been copied or patterned from an existing file that lacks it.

---

## Accepted legacy exceptions (do not "fix" these)

The following patterns exist in stable legacy code and predate PSR adoption.
They are known, accepted deviations. Do not rename, restructure, or reformat
these files unless a deliberate refactor has been scoped and agreed upon by core developers.

| File / Pattern | Deviation | Notes |
|---|---|---|
| `includes/classes/order.php` | lowercase class name `order` | Core class; renaming is a major BC break |
| `includes/classes/category_tree.php` | lowercase + snake `category_tree` | Same |
| `includes/classes/class.notifier.php` | lowercase `notifier` | Same |
| `includes/classes/template_func.php` | lowercase + snake `template_func` | Same |
| `includes/classes/products.php` | lowercase `products` | This class is deprecated; should not be used anyway. |
| `includes/classes/breadcrumb.php` | lowercase `breadcrumb` | Same |
| `includes/classes/language.php` | lowercase `language` | Same |
| `includes/classes/currencies.php` | lowercase `currencies` | Same |
| `includes/classes/http_client.php` | no method visibility, no strict_types | Known tech debt; stable; do not modify; mostly deprecated anyway |
| `includes/classes/split_page_results.php` | lowercase + splitCase hybrid | Legacy; stable |
| Various legacy observers in `includes/classes/observers/` | lowercase class names | Legacy pattern for observer auto-loading |
| `includes/modules/`, `includes/init_includes/`, `includes/extra_configures/` | closing `?>` tag present in ~71 files | Procedural include files; correct only when already editing the file |

---

## Database: always use table name constants

Table names are defined as constants in `includes/database_tables.php`.
Always use these constants in PHP code — never raw or prefixed table names.

```php
// Correct
$db->Execute("SELECT * FROM " . TABLE_ORDERS . " WHERE ...");

// Wrong
$db->Execute("SELECT * FROM zen_orders WHERE ...");
$db->Execute("SELECT * FROM orders WHERE ...");
```

---

## Security conventions

- Call `zen_output_string_protected()` on any user-supplied value before outputting it to HTML.
- New code should avoid direct `$_GET`/`$_POST`/`$_REQUEST` access where sanitized request helpers are available. 
  Direct access exists in some legacy class files and is acceptable there; do not propagate the pattern into new code.
- Admin input sanitization has its own whitelisting system — see [Admin Sanitization docs](https://docs.zen-cart.com/dev/code/admin_sanitization/) 
  before relaxing sanitization rules for any admin field.
- Do not modify the early request-sanitizing logic in `application_top.php`.

---

## Template settings: choosing `zen_config()` vs `$tplSetting->`

See `AGENTS.md` → "Configuration: `zen_config()` vs `$tplSetting` (TemplateSettings)" for how the
two mechanisms work and where `$tplSetting` is (and isn't) available. This section covers which
keys are appropriate to convert from `zen_config()` to `$tplSetting->`.

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

---

## Files that should never be directly edited

| File | Reason |
|---|---|
| `includes/application_top.php` | Bootstrap core; use `extra_configures` and `init_includes` hooks instead |
| `admin/includes/application_top.php` | Same |
| `includes/defined_paths.php` | Use `extra_configures` to define new `DIR_FS_*` / `DIR_WS_*` constants |
| `admin/includes/defined_paths.php` | Same |

More are listed in AGENTS.md

---

## Plugin development

Prefer plugins over core edits for new features. See `AGENTS.md` → "Plugin development"
for the full directory layout, PSR-4 namespace mapping, and installer patterns.

---

## References

- [PSR-12 Extended Coding Style](https://www.php-fig.org/psr/psr-12/)
- [Zen Cart Developer Docs](https://docs.zen-cart.com/dev/)
- `AGENTS.md` — architecture, bootstrapping, test commands, workflow
- `.editorconfig` — formatting rules enforced at editor level
