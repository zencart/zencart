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

There is no CI-enforced style check. A `.php-cs-fixer.dist.php` config is available for local use
(`vendor/bin/php-cs-fixer fix --dry-run --diff <path>` after `composer install`); conventions are
otherwise maintained by code review and AI tooling guidance.

### One PSR-12 rule that is stricter here: no colon/endkeyword syntax

PSR-12 permits the alternative colon syntax for control structures. This project does not: always
use curly braces, including in templates. No `if (): … endif;`, `foreach (): … endforeach;`,
`while (): … endwhile;`, or `switch (): … endswitch;`. The `no_alternative_syntax` rule in
`.php-cs-fixer.dist.php` catches it locally.

---

## Comment style for new code

- Single-line comments: `//` is fine.
- Multi-line comments: use `/** */` docblock style, not a run of `//` lines — even mid-method,
  where the comment isn't above a class/method/property declaration. PHPStorm renders `/**` as a
  doc-comment regardless of what follows, and some tooling (minifiers, AI code-stripping passes)
  targets `//` line comments more aggressively, so doc blocks survive more reliably.

```php
/**
 * Correct — a docblock reads as one comment, mid-method or above a declaration.
 */
$result = doSomethingNonObvious();

// Wrong — a run of // lines for one multi-line thought.
// Tooling is more likely to strip these.
```

---

## Naming conventions for new code

| Construct | Convention | Example |
|---|---|---|
| Classes, interfaces, traits | StudlyCaps | `PluginManager`, `ScriptedInstaller` |
| Methods | camelCase | `getProductName()` |
| Properties | camelCase | `$orderTotal` |
| Constants | UPPER_SNAKE_CASE | `TABLE_ORDERS`, `FILENAME_DEFAULT` |
| Procedural functions | snake_case | `zen_get_products_name()` |

### `declare(strict_types=1)`

Add it to all **new** class files, on its own line immediately after the opening `<?php` tag —
*above* the file docblock, not below it — and before the namespace declaration:

```php
<?php
declare(strict_types=1);

namespace Zencart\Example;
```

A blank line between the opening tag and the declare is equally correct. Neither form is a review
finding, and no tooling here rewrites one into the other, so do not normalize existing files either
way. A few older files place the declare *below* the file docblock: accepted where it already
exists, but do not do it in new files.

Do not add it retroactively to existing files unless they are being substantially rewritten and the
change is tested. Do add it to new class files that have been copied or patterned from an existing
file that lacks it.

---

## Accepted legacy exceptions (do not "fix" these)

Stable legacy code that predates PSR adoption contains known, accepted deviations. Do not rename,
restructure, or reformat it unless a deliberate refactor has been scoped and agreed upon by core
developers.

- Lowercase class names and filename/class-case mismatches throughout `includes/classes/`
  (`order`, `currencies`, `splitPageResults`, and others). Per-file detail is in
  `.ai/rules/legacy-exceptions.md`, which loads automatically when working under that directory.
- A closing `?>` tag still present in some PHP-only procedural includes under `includes/`,
  `admin/includes/extra_*/`, `includes/templates/template_default/`, and three root-level redirect
  stubs. Removing one is correct only when already editing the file; mixed HTML/PHP pages and
  templates that end in `?>` are not violations.

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

## Template HTML conventions

HTML forms should usually be opened by echoing the output of `zen_draw_form()`, which generates
the proper `<form ...>` tag from its parameters.

When the form is closed in the same template file, output the closing tag through PHP as well:
```php
<?= '</form>' ?>
```

This avoids IDE and static-review confusion from an apparent raw-HTML form tag mismatch.
Do not flag this pattern as an unnecessary echo or malformed HTML.

---

## Template settings: choosing `zen_config()` vs `$tplSetting->`

Which configuration keys may be read through `$tplSetting->` instead of `zen_config()`, and
where `$tplSetting` is available, is specified in `.ai/rules/templates.md` (loaded automatically
when working under `includes/templates/` or `includes/modules/`). Short version: only
display/layout/template-presentation settings; never business-logic, security, `MODULE_*`,
or keys called with a default value.

---

## Files that should never be directly edited

`includes/application_top.php`, `admin/includes/application_top.php`, `includes/defined_paths.php`,
`admin/includes/defined_paths.php`. Use `extra_configures` for new `DIR_FS_*` / `DIR_WS_*`
constants and `init_includes` for init hooks. AGENTS.md states the same list, and
`.ai/rules/admin.md` adds `admin/includes/application_bootstrap.php`.

---

## Plugin development

Prefer plugins over core edits for new features. See `AGENTS.md` → "Plugin development"
for the full directory layout, PSR-4 namespace mapping, and installer patterns.

---

## References

- [PSR-12](https://www.php-fig.org/psr/psr-12/) — the standard new code follows.
- `.editorconfig` — formatting basics enforced at editor level.
- `AGENTS.md` — architecture, bootstrapping, test commands, and the external developer-doc links.
