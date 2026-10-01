---
paths:
  - "not_for_release/testFramework/**"
  - "phpunit.xml"
  - "composer.json"
---

# Test suite

Developer documentation: https://docs.zen-cart.com/dev/testframework/testing/

## Layout and bootstrap

- `phpunit.xml` uses `vendor/autoload.php`, sets `APP_ENV=testing` and reduced bcrypt rounds. Composer is used only for the test suite; production code uses Zen Cart's own autoloading.
- Tests live in `not_for_release/testFramework/`, grouped into `Unit`, `FeatureStore`, `FeatureAdmin`. Test autoloading is configured in `composer.json` under `autoload-dev`.
- `not_for_release/testFramework/Support/application_testing.php` is loaded by `application_top.php` when present.
- Unit tests are grouped into topic subdirectories under `not_for_release/testFramework/Unit/` (e.g. `testsTemplateResolver/`, `testsCategories/`, `testsHtmlOutput/`). Put a new test in the subdirectory matching its subject; use `testsSundry/` only when nothing else fits.
- Test classes extend `Tests\Support\zcUnitTestCase`, whose `setUp()` calls `UnitTestBootstrap::initialize()`.

## Commands

```
composer install
composer run-script tests-unit
composer run-script tests-feature
composer run-script tests-feature-parallel
composer run-script tests-feature-store
composer run-script tests-feature-store-parallel
composer run-script tests-feature-admin
composer run-script tests-feature-admin-parallel
composer run-script phpstan:admin
composer run-script phpstan:catalog
```

Prefer the `-parallel` variants when failures come from clashing `require` statements or duplicate function declarations.

## Traps

- If a test calls `define()` on a global constant (common when stubbing config), add the `#[\PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses]` class attribute (or `#[RunInSeparateProcess]` on a method). PHP constants cannot be redefined, so without process isolation a later test can fail or silently reuse the earlier value. The legacy `protected $runTestInSeparateProcess = true;` / `protected $preserveGlobalState = false;` properties seen in older tests do **not** work on the PHPUnit version in use (`TestCase::$runTestInSeparateProcess` is now `private`, so a same-named subclass property is an inert shadow). Use the attribute form; treat tests still using the old properties as suspect for order-dependent flakiness.
- Even though `includes/classes` and `includes/modules` are classmap-autoloaded for tests, existing tests still `require_once DIR_FS_CATALOG . 'includes/classes/Whatever.php';` in `setUp()`. Follow that pattern rather than relying solely on autoloading.
- Never declare a bare global function (e.g. `namespace { function zen_something() {...} }`) to stub a production function in a test file, even to match an existing test that does. PHPUnit executes a test file's top-level code at collection time, before any test runs and regardless of `RunTestsInSeparateProcesses`, so a later real `require_once` of the file defining that function fatals with "Cannot redeclare function", and only when the full suite runs together. Prefer requiring the real function's file with realistic fixture data; if you must stub, guard with `if (!function_exists(...))` and expect it may still collide.
- When mocking `queryFactory` for a write path (`INSERT`/`UPDATE`/`DELETE`), mocking `Execute()` alone is not enough if the code calls `bindVars()`: the real `bindVars()`/`prepare_input()` call `mysqli_real_escape_string($this->link, ...)`, which throws against a mock with no live connection. Mock `bindVars()` too (a placeholder-substitution callback is enough).
- If a new or changed test fails only when the full Unit suite runs together, don't assume your change caused it: this suite has pre-existing, environment-influenced, order-dependent flakiness (e.g. tests in `testsDiscountCoupon/` and `testsSundry/AttributeLookupsTest.php`). Re-run the failing file in isolation first.

## Review expectations by changed area

- Unit-level logic should have focused PHPUnit coverage where practical.
- Storefront/admin behavior changes should use the relevant feature test suite.
- Plugin filesystem/bootstrap changes should exercise plugin enablement/loading paths.
- Prefer the composer scripts above; say when a useful test was not run or cannot be run.
