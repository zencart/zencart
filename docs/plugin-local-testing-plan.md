# Plugin-Local Testing Plan

## Goal

Support plugin tests that live inside the plugin itself under `zc_plugins`, while still reusing Zen Cart's existing `not_for_release/testFramework` support code, runners, and base test cases.

## Core Direction

Plugin tests should be versioned with the plugin they validate.

Instead of treating `not_for_release/testFramework/Support/plugins/...` as the primary home for plugin tests, the framework should discover and run tests that live directly inside plugin directories.

## Proposed Plugin Test Layout

For a plugin stored at:

- `zc_plugins/<PluginName>/<version>/`

use a test structure like:

- `zc_plugins/<PluginName>/<version>/tests/FeatureAdmin`
- `zc_plugins/<PluginName>/<version>/tests/FeatureStore`
- `zc_plugins/<PluginName>/<version>/tests/Unit`
- `zc_plugins/<PluginName>/<version>/tests/Fixtures` (optional)
- `zc_plugins/<PluginName>/<version>/tests/Seeders` (optional)
- `zc_plugins/<PluginName>/<version>/tests/bootstrap.php` (optional)

This keeps the tests tied to the plugin version they belong to.

## Framework Changes Needed

### 1. Test Discovery

Update the framework runners so they can discover plugin-local tests under paths such as:

- `zc_plugins/*/*/tests/FeatureAdmin/*Test.php`
- `zc_plugins/*/*/tests/FeatureStore/*Test.php`
- `zc_plugins/*/*/tests/Unit/*Test.php`

The runners should continue supporting the existing core test directories under `not_for_release/testFramework`.

### 2. Shared Base Classes

Plugin-local tests should reuse the core framework base classes, for example:

- `Tests\Support\zcInProcessFeatureTestCaseAdmin`
- `Tests\Support\zcInProcessFeatureTestCaseStore`
- `Tests\Support\zcUnitTestCase`

That keeps the framework centralized while allowing test files to live with the plugin.

### 3. Plugin-Local Bootstrap Support

Allow a plugin to provide optional test-local assets such as:

- `tests/bootstrap.php`
- `tests/Fixtures/`
- `tests/Seeders/`

These should only be loaded when that plugin's tests are running.

### 4. Installation Strategy

When testing a plugin, the framework should use the plugin's real source from its `zc_plugins/<PluginName>/<version>` directory.

Possible strategies:

- run against the plugin already present in the catalog under test
- copy/install the plugin into a worker-local plugin directory when filesystem isolation is required

The framework should prefer worker-local plugin paths for tests that mutate plugin files or require isolated install/uninstall behavior.

### 5. Plugin Test Metadata

Each plugin may optionally declare testing metadata, either in:

- `tests/plugin-test.php`
- or a small extension to `manifest.php`

Useful metadata could include:

- plugin name and version under test
- whether the tests mutate plugin filesystem state
- whether tests must run serially
- custom bootstrap path
- custom seeder path

## Runner Behavior

### Filtering

The runners should support filters for:

- all plugin-local tests
- one plugin only
- one plugin plus one test layer, such as admin/store/unit

Examples of intended use:

- run all plugin tests
- run only GDPR plugin tests
- run only plugin unit tests

### Parallelism and Isolation

Plugin tests that install, uninstall, enable, disable, or otherwise modify plugin files should be tagged so they run serially or in isolated worker-local plugin directories.

Tests that only exercise read-only behavior should remain eligible for parallel execution.

## Authoring Conventions

Document a plugin-testing convention covering:

- where plugin tests live
- which base classes to extend
- how to provide fixtures or seeders
- when to mark tests as serial
- how to classify tests as FeatureAdmin, FeatureStore, or Unit

## Suggested First Reference Implementation

Use the GDPR / DSAR plugin as the first plugin-local example.

### Initial GDPR test coverage

- plugin install and uninstall
- storefront DSAR page access
- privacy-policy acceptance gating
- request submission
- admin queue rendering
- SLA due and overdue behavior
- anonymization and forced logout/session invalidation
- export expiry handling

## Recommended Implementation Order

1. Define and document the plugin-local `tests/` directory convention.
2. Update the test runners to discover tests under `zc_plugins/*/*/tests`.
3. Ensure plugin-local tests can extend the existing framework base classes cleanly.
4. Add support for plugin-local bootstrap, fixtures, and seeders.
5. Implement GDPR plugin tests as the first end-to-end example.
6. Add filtering and runner options for plugin-local test execution.
7. Add serial or isolation tagging guidance for plugin filesystem mutation tests.

## What Not To Do

- Do not force each plugin to invent its own separate test runner.
- Do not duplicate the whole framework inside each plugin.
- Do not make `Support/plugins/...` the primary source of truth for plugin tests if the goal is plugin-local ownership.

## Summary

The best direction is:

- keep the framework support centralized under `not_for_release/testFramework`
- keep plugin test files local to the plugin under `zc_plugins/<PluginName>/<version>/tests`
- teach the framework runners to discover and execute those plugin-local tests

This gives plugin authors ownership of their own tests without fragmenting the underlying test infrastructure.
