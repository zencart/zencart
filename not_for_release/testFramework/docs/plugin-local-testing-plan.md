# Plugin-Local Testing Plan

## Goal

Support tests that live with the plugin they validate under `zc_plugins/<PluginName>/<version>/tests`, while reusing Zen Cart's central `not_for_release/testFramework` base classes, helpers, runners, and CI grouping.

Plugin tests should be versioned with the plugin. The framework should remain centralized; plugins should own only their test files, fixtures, seeders, and optional test metadata.

## Authoring Convention

For a plugin stored at:

- `zc_plugins/<PluginName>/<version>/`

use this test layout:

- `tests/FeatureAdmin/*Test.php` for admin/in-process feature tests
- `tests/FeatureStore/*Test.php` for storefront/in-process feature tests
- `tests/Unit/*Test.php` for isolated unit tests
- `tests/Fixtures/` for optional plugin-local fixtures
- `tests/Seeders/` for optional plugin-local seeders
- `tests/bootstrap.php` for optional plugin-local bootstrap code
- `tests/plugin-test.php` for optional plugin test metadata

Plugin-local tests should extend the normal framework base classes:

- `Tests\Support\zcInProcessFeatureTestCaseAdmin`
- `Tests\Support\zcInProcessFeatureTestCaseStore`
- `Tests\Support\zcUnitTestCase`

Plugin-local tests that install, uninstall, enable, disable, or mutate plugin filesystem state should use:

```php
/**
 * @group serial
 * @group plugin-filesystem
 */
```

Read-only plugin-local tests can use:

```php
/**
 * @group parallel-candidate
 */
```

## MVP Implemented

The initial plugin-local testing MVP is now in place.

Implemented:

- plugin-local discovery through `not_for_release/testFramework/run-plugin-tests.sh`
- Composer script `tests-plugin`, with suite and group filters for unit, storefront, admin, and filesystem-mutating plugin-local tests
- path-based filtering with `--plugin`, `--suite`, `--require-group`, and normal PHPUnit `--filter`
- plugin-local admin feature tests included in the aggregate feature runner's serial `plugin-filesystem` bucket
- group reporting support for plugin-local admin and storefront feature tests
- `Tests\Support\Traits\PluginLocalTestConcerns`
- plugin root detection from a test file path
- plugin-local metadata loading from `tests/plugin-test.php`
- plugin-local bootstrap loading from `tests/bootstrap.php`
- helper support for installing the current plugin source into the test filesystem
- helper support for installing the current plugin through the plugin installer stack
- `zc_plugins/.gitignore` keeps external plugin source trees out of the main Zen Cart repository; plugin-local tests should be committed in the plugin repository that owns them
- GDPR / DSAR plugin-local admin install/uninstall reference coverage
- GDPR / DSAR plugin-local storefront reference coverage

Current reference plugin:

- `zc_plugins/gdpr-dsar/v1.0.0`

Current reference tests:

- `zc_plugins/gdpr-dsar/v1.0.0/tests/FeatureAdmin/GdprDsarPluginInstallTest.php`
- `zc_plugins/gdpr-dsar/v1.0.0/tests/FeatureStore/GdprDsarStorefrontTest.php`

## Commands

Run all plugin-local tests:

```bash
composer tests-plugin
```

Dry-run plugin-local discovery:

```bash
composer tests-plugin -- --dry-run
```

Run only one plugin:

```bash
composer tests-plugin -- --plugin gdpr-dsar
```

Run one plugin and suite:

```bash
composer tests-plugin -- --plugin gdpr-dsar --suite FeatureAdmin
composer tests-plugin -- --plugin gdpr-dsar --suite FeatureStore
composer tests-plugin -- --plugin gdpr-dsar --suite Unit
```

Run only plugin filesystem mutation tests:

```bash
composer tests-plugin -- --plugin gdpr-dsar --require-group plugin-filesystem --group plugin-filesystem
```

Run one class or method:

```bash
composer tests-plugin -- --plugin gdpr-dsar --filter GdprDsarPluginInstallTest
```

## Plugin Repository GitHub Actions

Plugin repositories should be able to run their plugin-local tests without copying Zen Cart's central test framework into the plugin repository.

The expected CI contract is:

- the plugin repository owns `tests/`, fixtures, seeders, optional `tests/bootstrap.php`, and optional `tests/plugin-test.php`
- the Zen Cart repository owns `not_for_release/testFramework`, Composer scripts, PHPUnit configuration, and the database/web bootstrap
- the CI job checks out Zen Cart, places the plugin under `zc_plugins/<PluginName>/<version>`, then runs the central plugin-local test runner

A plugin repository CI workspace should be arranged like:

```text
workspace/
  plugin/
  zencart/
    not_for_release/testFramework/
    zc_plugins/
      gdpr-dsar/
        v1.0.2/
          manifest.php
          catalog/
          admin/
          tests/
```

Start with workflow environment variables rather than hard-coding plugin paths in multiple places:

```yaml
env:
  ZC_PLUGIN_NAME: gdpr-dsar
  ZC_PLUGIN_VERSION: v1.0.2
  ZC_CORE_REF: master
```

The preferred GitHub Actions workflow should use the published Zen Cart test-runner container rather than DDEV. DDEV can remain useful for local development, but plugin CI should use the container so every plugin repository gets the same PHP extensions, Composer version, MySQL client tooling, and shell environment.

An initial GitHub Actions workflow can:

1. Check out the plugin repository into `plugin/`.
2. Check out Zen Cart into `zencart/` at `ZC_CORE_REF`.
3. Copy or sync `plugin/` into `zencart/zc_plugins/${ZC_PLUGIN_NAME}/${ZC_PLUGIN_VERSION}/`.
4. Run inside `ghcr.io/zencart/zencart-test-runner:<php-tag>` with MySQL as a service container.
5. Install Composer dependencies inside the Zen Cart checkout.
6. Run `composer tests-plugin -- --plugin "${ZC_PLUGIN_NAME}"`.

The container-based command sequence is:

```bash
composer install
composer tests-plugin -- --plugin "${ZC_PLUGIN_NAME}"
```

The container itself is documented in:

```text
not_for_release/testFramework/test-runner-container-ghcr.md
```

Longer term, Zen Cart can still provide a reusable GitHub Action around the container to reduce repeated workflow boilerplate in plugin repositories.

A reusable Action could look like:

```yaml
- uses: zencart/plugin-test-action@v1
  with:
    plugin-name: gdpr-dsar
    plugin-version: v1.0.2
    zencart-ref: master
```

The reusable Action would be responsible for checking out Zen Cart, installing the plugin under `zc_plugins/<PluginName>/<version>`, preparing the database/web runtime, running `composer tests-plugin` inside the test-runner container, and uploading useful logs or artifacts on failure.

A future matrix can test plugins against multiple Zen Cart and PHP versions:

```yaml
strategy:
  matrix:
    zencart-ref:
      - master
      - v2.1.0
    php:
      - "8.2"
      - "8.3"
      - "8.4"
```

Do not start with a broad matrix. Prove one known-good Zen Cart reference first, then expand once plugin-local CI is stable.

## Next Milestone: Real Plugin Coverage

The framework plumbing is usable. The next phase should focus on meaningful plugin behavior coverage.

Recommended order:

1. Expand GDPR / DSAR storefront tests around policy acceptance gating.
2. Add GDPR / DSAR request submission coverage and verify the request/audit rows.
3. Add GDPR / DSAR admin queue tests that use seeded request rows.
4. Add SLA due and overdue coverage.
5. Add export expiry coverage.
6. Add anonymization and forced logout/session invalidation coverage.
7. Add a small plugin-local unit test to prove the `tests/Unit` path with a real plugin.

## Next Milestone: Plugin-Local Data Support

The MVP supports explicit bootstrap loading, but seeders and fixtures are still manual.

Recommended order:

1. Add a plugin-local seeder convention under `tests/Seeders`.
2. Let `tests/bootstrap.php` explicitly register or require plugin-local seeders first.
3. Add helper methods for resolving plugin-local fixture paths.
4. Only add automatic seeder/fixture discovery after at least two plugins need the same behavior.

Seeder class names should avoid collisions with central seeders. Prefer plugin-specific namespaces.

## Later: Discovery and Metadata

Current filtering is path-based and intentionally simple.

Later improvements:

- use `tests/plugin-test.php` metadata for richer filtering
- support metadata fields such as `filesystem_mutation`, `serial`, `bootstrap`, `seeders`, and `fixtures`
- optionally expose plugin-local tests as PHPUnit XML suites if IDE or CI tooling needs that
- introduce a PHPUnit extension/listener or runner-generated bootstrap map if explicit helper bootstrapping becomes noisy

Do not add PHPUnit XML suites until there is a concrete need. Passing discovered file paths to PHPUnit is simpler and already works.

## Later: Isolation

Plugin filesystem mutation tests currently run serially. That is safe, but it limits parallelism.

Later improvements:

- install plugin source into worker-local plugin directories for isolated mutation tests
- make filesystem isolation metadata-driven
- allow read-only plugin tests to stay parallel while install/uninstall tests run isolated or serial

## Open Risks

- Host execution currently depends on the local PHP environment having the required PDO driver.
- Container execution depends on the published `ghcr.io/zencart/zencart-test-runner` image and a reachable MySQL service.
- DDEV can still be used for local development, but it is no longer the preferred plugin CI runtime.
- Storefront plugin-local tests need the plugin installed through the real installer stack before plugin-provided pages, language files, and tables are available.
- The original central plugin install tests still use framework-owned fixtures from `not_for_release/testFramework/Support/plugins`; do not move them until plugin-local coverage is proven with real plugins.

## What Not To Do

- Do not force each plugin to invent its own test runner.
- Do not duplicate the central test framework inside each plugin.
- Do not make `not_for_release/testFramework/Support/plugins` the long-term source of truth for real plugin tests.
- Do not migrate every existing plugin-related test at once.
