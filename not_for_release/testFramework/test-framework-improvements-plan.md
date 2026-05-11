# Test Framework Improvements Plan

## Framework Structure

The test framework currently has three main layers:

- `not_for_release/testFramework/Unit`
  - focused unit coverage for support classes, bootstrap helpers, runners, and isolated business logic
  - these tests normally boot through Composer autoload and lightweight helper setup rather than a full storefront/admin request

- `not_for_release/testFramework/FeatureStore`
  - storefront-oriented feature coverage
  - tests use `zcInProcessFeatureTestCaseStore` and issue in-process requests against `index.php`
  - request helpers such as `getMainPage`, `visitLogin`, `submitCreateAccountForm`, `visitCheckoutShipping`, and `followRedirect` exist to keep tests expressive and close to user intent

- `not_for_release/testFramework/FeatureAdmin`
  - admin-oriented feature coverage
  - tests use `zcInProcessFeatureTestCaseAdmin` and issue in-process requests against `admin/index.php`
  - helpers such as `visitAdminHome`, `submitAdminLogin`, `submitAdminSetupWizard`, `visitAdminCommand`, `submitAdminForm`, and `followAdminRedirect` cover the common admin flows

Shared support code lives under `not_for_release/testFramework/Support` and `not_for_release/testFramework/Services`.

Important pieces in that support layer:

- `Support/zcInProcessFeatureTestCase.php`
  - shared base for in-process feature tests
  - defines common constants, config loading, and database bootstrap behavior

- `Support/zcInProcessFeatureTestCaseStore.php`
  - storefront-specific helpers, cookies, SSL request helpers, redirect-following, form submission helpers, and higher-level checkout/account helpers

- `Support/zcInProcessFeatureTestCaseAdmin.php`
  - admin-specific helpers for login, setup wizard submission, endpoint navigation, form submission, redirect-following, and cookie persistence

- `Support/InProcess/*`
  - request/response runner classes and the two child request executors
  - `StorefrontFeatureRunner` and `AdminFeatureRunner` prepare payloads, launch child requests, and turn the results into `FeatureResponse` objects
  - `execute_storefront_request.php` and `execute_admin_request.php` create the request globals, define required bootstrap shims, invoke the real entrypoints, and serialize the response back to PHPUnit

- `Support/TestConfigResolver.php`
  - central config selection for `ddev`, `runner`, and user-specific configurations

- `Support/configs/*.configure.php`
  - test runtime configuration for store/admin/main contexts
  - these drive DB connection details, filesystem roots, and HTTP/HTTPS settings for feature execution

- `Services/DatabaseBootstrapper.php` and `Support/InProcess/InProcessDatabaseSnapshot.php`
  - build and restore the test database baseline used by in-process feature classes
  - this is what keeps feature tests fast enough to run repeatedly without replaying the full install SQL from scratch on every class bootstrap

## How New Tests Should Be Added

### Adding a unit test

Use a unit test when the target behavior does not require a full storefront or admin page request.

Good candidates:

- config resolution
- request/response helper parsing
- bootstrap utilities
- filesystem helpers
- runner error handling
- isolated service classes

Recommended approach:

1. Put the test under `not_for_release/testFramework/Unit/testsSundry` or another existing focused unit subdirectory if one already matches the subject area.
2. Prefer testing one helper/service directly rather than booting a full page.
3. Use small fixtures from `not_for_release/testFramework/Unit/fixtures` where helpful.
4. Keep assertions targeted and deterministic.

Examples:

- `TestConfigResolverTest.php`
- `InProcessFeatureRunnerTest.php`
- `RuntimeConfigTest.php`

### Adding a storefront feature test

Use a storefront feature test when the behavior depends on page bootstrap, sessions, redirects, forms, checkout state, customer login state, or storefront templates.

Recommended approach:

1. Add the test class under the closest existing domain folder in `not_for_release/testFramework/FeatureStore`.
2. Extend `zcInProcessFeatureTestCaseStore`.
3. Use the highest-level helper that matches the intent of the scenario.
4. Prefer asserting user-visible outcomes:
   - status codes
   - redirects
   - key page text
   - form behavior
   - order total sections
   - database side effects only when user-visible assertions are not enough
5. Seed only the state needed for the scenario.

Common helpers to reuse:

- `getMainPage`
- `getSslMainPage`
- `visitLogin`
- `visitCreateAccount`
- `submitLoginForm`
- `submitCreateAccountForm`
- `visitCart`
- `addProductToCart`
- `continueCheckoutShipping`
- `continueCheckoutPayment`
- `submitGiftVoucherSendForm`
- `followRedirect`

Patterns to prefer:

- start from the smallest page flow that proves the behavior
- use dedicated seeders or focused configuration tweaks rather than broad setup whenever possible
- keep one test method centered on one scenario, even when multiple steps are required

### Adding an admin feature test

Use an admin feature test when the behavior depends on admin bootstrap, login, setup wizard state, plugin install state, command routing, or admin forms/tables.

Recommended approach:

1. Add the test class under the closest existing domain folder in `not_for_release/testFramework/FeatureAdmin`.
2. Extend `zcInProcessFeatureTestCaseAdmin`.
3. Use helper methods instead of crafting raw request arrays unless the scenario truly requires it.
4. Assert user-visible admin outcomes first, then database changes if the page behavior alone is insufficient.

Common helpers to reuse:

- `visitAdminHome`
- `visitAdminCommand`
- `submitAdminLogin`
- `submitAdminSetupWizard`
- `submitAdminForm`
- `followAdminRedirect`
- `runCustomSeeder`

Patterns to prefer:

- get the session into the correct admin state first
- keep setup wizard flows explicit if the scenario depends on them
- when testing plugins, install/remove fixtures through the existing filesystem helpers instead of open-coded file operations

## Choosing Between Unit And Feature Coverage

Prefer unit coverage when:

- the subject is a helper/service class
- the behavior can be validated without loading `index.php` or `admin/index.php`
- the failure mode is about parsing, branching, or exception behavior

Prefer feature coverage when:

- redirects matter
- sessions matter
- page bootstrap order matters
- templates or page modules matter
- the scenario depends on real request globals or request routing

When in doubt:

- start with a unit test for helper behavior
- add a feature test only if the risk is really in the application bootstrap or request lifecycle

## Practical Notes For New Tests

- Reuse existing helpers before adding new ones. The framework already has a fair amount of request intent encoded in the base classes.
- If you do add a helper, prefer adding it to the relevant base test case or concern trait so later tests can reuse it.
- Keep feature setup narrow. Seed only what the scenario needs.
- Avoid assertions that depend on incidental markup structure when a stable user-facing string or redirect target will do.
- For tests that care about warnings or debug logs, use `LogFileConcerns` deliberately and verify why a log exists before normalizing the test around it.
- If a feature test starts needing many low-level shims, consider whether the behavior should instead be covered by a focused unit test around the underlying helper or service.

## Remaining Test Backlog

The first large expansion pass is done. The backlog below is now focused on
high-value gaps that are still lightly covered.

### Admin feature gaps

1. Catalog-management CRUD
   - `categories`, `product`, `manufacturers`, `featured`, and `specials`
2. Attribute and option management
   - `attributes_controller`, `option_name`, `option_values`, `options_name_manager`, and `options_values_manager`
3. Communications and export flows
   - `mail`, `newsletters`, and `coupon_admin_export`
4. Session and security edges
   - `mfa`, `logoff`, `denied`, `keepalive`, and expired-session redirects
5. Read-only diagnostic pages
   - `server_info`, `admin_activity`, `whos_online`, `developers_tool_kit`, and `sqlpatch`

### Storefront feature gaps

1. Login, logoff, and auth-guard flows
2. Checkout payment and confirmation variants
3. Product-detail adjacent pages
   - `product_reviews`, `product_reviews_info`, `reviews`, `popup_image`, and `popup_image_additional`
4. Static/content page sweep
   - `about_us`, `conditions`, `privacy`, `cookie_usage`, `shippinginfo`, `site_map`, and `accessibility`
5. Download and recovery-adjacent flows
   - `download`, `download_time_out`, and `unsubscribe`

### Harness and unit gaps

1. Redirect helper behavior
   - `followRedirect`, `followAdminRedirect`, and URI normalization/server derivation
2. Runner failure/path branches
   - malformed child-response payloads, unsupported-path exceptions, and more direct-entrypoint edge cases
3. Cookie capture and propagation
   - multiple cookies, non-session cookie persistence, and carry-forward across requests
4. `FeatureResponse` parsing edges
   - single-quoted attributes, missing form IDs, repeated field names, and whitespace edge cases
5. Database bootstrap lifecycle
   - baseline rebuild triggers, missing snapshot recovery, and restore failure behavior

## Current State

The first coverage-expansion pass is largely complete.

What is now in place:

- admin coverage now extends beyond login/setup into orders, customers, coupons/gift vouchers, and tax/geo/currency CRUD
- storefront coverage now includes account maintenance, address-book CRUD, password recovery/reset, order-history routing, checkout success, address selection, catalog landing pages, and review/question submission
- unit coverage now locks down the newer runner/runtime helpers, including:
  - worker-aware runtime config
  - runner store/admin configure outputs for worker-scoped DB and log paths
  - database runtime constant setup for worker-scoped log directories
  - parallel unit runner CLI behavior
  - worker-database preparation helper behavior
  - worker-runtime description output
  - artifact-routing behavior
  - worker-scoped plugin filesystem behavior

Open harness caveats that still matter:

- admin warnings around `messageStack` compatibility can still appear during header rendering
- feature tests still share mutable infrastructure unless a worker-specific DB/filesystem path is configured
- plugin install tests remain the clearest serial-only bucket

## Current Coverage Priorities

The remaining work is no longer basic reachability.
The biggest gains now come from workflows that stress different request/bootstrap
paths than the first wave.

Use the remaining backlog above as the source of truth, and prefer this order:

1. Admin catalog management and attribute/option flows
2. Storefront login/logoff plus checkout payment/confirmation variants
3. Harness/unit coverage for redirect, cookie, runner, and bootstrap edge behavior
4. Admin communications/export plus session/security pages

## Parallel Test Execution Plan

Parallel execution looks achievable in stages, but the current framework is not ready for full feature-suite parallelism yet.
The biggest constraint is shared mutable state:

- all in-process feature classes currently target the same configured test database
- `InProcessDatabaseSnapshot` restores the baseline by dropping and recreating the live working tables in place
- plugin tests modify the shared `zc_plugins` filesystem tree
- feature bootstrap currently deletes shared debug-log files and `progress.json`

Because of that, a safe rollout should happen in phases rather than trying to parallelize everything at once.

### Phase 1: Parallel unit tests

This phase is already in place.

Current implementation:

- `composer tests-unit` runs the unit suite through `not_for_release/testFramework/run-parallel-unit-tests.sh`
- the runner prints live `START`, `PASS`, and `FAIL` output, supports `--help`, and forwards extra PHPUnit arguments
- targeted runs such as `composer tests-unit -- --filter RuntimeConfigTest` now narrow the file list instead of fanning out across the full suite

This remains the safest parallel lane because the unit suite is the least coupled to the shared storefront/admin runtime.

### Phase 2: Split feature tests by isolation profile

This phase is complete. The feature suite is now explicitly classified so the
parallel/serial boundary is visible and enforced.

Current implementation notes:

- `composer tests-report-feature-groups` now summarizes explicit `serial`, `plugin-filesystem`, and `parallel-candidate` tags alongside heuristic shared-state buckets
- classification is currently complete for the existing feature suite: 68 total feature files, 2 tagged `serial`, 66 tagged `parallel-candidate`, and 0 untagged
- the grouping report now also shows a store-vs-admin breakdown so it is easier to see where the remaining serial burden lives before attempting feature parallelism
- `composer tests-report-feature-groups-strict` now fails if any future feature test file is added without one of the explicit grouping tags or if an explicit tag combination is contradictory
- that strict report now runs in a summary-only mode in CI so the logs stay readable while still blocking classification drift

### Phase 3: Introduce worker-scoped feature databases

This is the main prerequisite for parallel feature execution.

Implementation notes:

- the runtime config layer can now derive worker-specific DB names from `ZC_TEST_WORKER` or `TEST_TOKEN`
- `ZC_TEST_DB_DATABASE` can also override the resolved database name directly for ad hoc local runs
- worker identifier normalization is now centralized in the runtime config layer so DB names, progress files, and artifact directories stay aligned
- the runtime config layer can now derive worker-specific progress-file paths from `ZC_TEST_WORKER` or `TEST_TOKEN`
- the runtime config layer can now derive worker-specific artifact directories for captured console logs from `ZC_TEST_WORKER` or `TEST_TOKEN`
- the runtime config layer can now derive worker-specific log directories from `ZC_TEST_WORKER` or `TEST_TOKEN`
- `composer tests-runtime-describe` now prints the derived worker-scoped DB, progress, log, artifact, and plugin paths for a given environment
- that runtime-description command is now covered by focused unit tests for both default and worker-scoped output
- `composer tests-db-prepare-workers` now creates the base and worker-suffixed databases expected by that naming scheme
- `composer tests-db-prepare-workers -- --dry-run` can be used to verify the planned database names without mutating MySQL
- the worker-database helper now has unit coverage for dry-run output, `--skip-base`, env overrides, help text, invalid worker counts, and unknown options

Remaining work:

- continue proving that bootstrap and snapshot restore behave correctly against worker-local databases during real parallel feature execution
- keep the worker DB naming/config path aligned between local wrappers and CI jobs

### Phase 4: Introduce worker-scoped writable filesystem areas

Even with separate databases, some feature tests would still collide on disk.

Recommended follow-up:

1. Make plugin-install tests write into a worker-specific plugin root, or keep them in a serial bucket.
   - baseline worker-scoped plugin-directory support is now in place in the filesystem helper layer
2. Give debug logs and artifact capture worker-specific directories.
   - captured feature-test console artifacts now have baseline worker-scoped directory support
   - artifact routing into those directories is now covered by a focused unit test around `zcFeatureTestCase`, including `TEST_TOKEN` fallback
3. Replace the shared `progress.json` path with a worker-scoped temp/progress file.
   - baseline runtime/config support for worker-scoped progress filenames is now in place
4. Audit any remaining writes under the catalog root for shared filenames.

Current implementation notes:

- progress-file paths can now resolve per worker
- debug-log writes now resolve through worker-specific `DIR_FS_LOGS` paths in the runner configs
- captured console artifacts can now resolve per worker
- plugin install/remove helpers can now resolve a worker-specific plugin directory
- worker-path derivation is visible through `composer tests-runtime-describe`

Remaining work:

- keep validating real feature execution against those worker-specific paths end-to-end
- keep plugin tests serial until the worker-specific plugin path is proven in full feature flows

### Phase 5: Trial real feature-parallel execution

This phase is now broader than the original storefront-only trial. The current
goal is to keep proving the live parallel runners in CI, not to add more
parallel-candidate classification.

Current implementation notes:

- `composer tests-feature-store-parallel` now runs `not_for_release/testFramework/run-parallel-storefront-feature-tests.sh`
- `composer tests-feature-admin-parallel` now runs `not_for_release/testFramework/run-parallel-admin-feature-tests.sh`
- the runner assigns `ZC_TEST_WORKER` per active worker process and launches one PHPUnit process per candidate test file
- storefront now runs entirely through the parallel-candidate lane, while admin runs a parallel-candidate lane plus the 2 remaining serial plugin/filesystem tests
- worker-count configuration now falls back cleanly between `ZC_FEATURE_PARALLEL_PROCESSES` and `ZC_TEST_DB_WORKERS`, so DB prep and runner scheduling can stay aligned from one setting
- file selection now prefers exact basename matches for `--filter` and env-based targeting before falling back to substring matching
- the runner now fails fast with a clearer message if the expected worker databases are missing or cannot be verified
- `--prepare-databases` can now auto-create the worker DBs before execution, using the same `tests-feature-store-parallel` or `tests-feature-admin-parallel` entrypoint with environment overrides when needed
- `--dry-run` support exists so worker assignment and file selection can be validated without requiring a live DB-backed feature run
- CI-style wrapper scripts now exist for:
  - aggregate feature flow
  - storefront feature flow
  - admin feature flow
  - top-level unit+feature flow

Remaining work:

- validate the runners under repeated green CI runs and watch for cross-worker flakiness rather than classification errors
- decide whether the current split store/admin workflows remain the preferred long-term GitHub Actions shape or whether they should eventually collapse into a single aggregate feature workflow

### Current Operating Model

Core commands:

- `tests-unit`
  - default unit runner for local and CI use
- `tests-feature`
  - aggregate feature CI/local-repro entrypoint driven by `run-feature-tests-ci.sh`
  - performs worker-runtime description, strict classification check, worker-DB preparation, then the aggregate feature runner while forwarding filters to the final runner
- `tests-ci`
  - aggregate top-level CI entrypoint driven by `run-tests-ci.sh`
  - routes filters across the unit and feature CI wrappers, skipping whichever side has no matches for the requested filter

Dry-run and local helpers:

- `tests-feature -- --dry-run`
  - aggregate feature dry-run entrypoint using the same wrapper, with worker DB prep preview instead of real creation
- `tests-feature-local`
  - local convenience wrapper for `tests-feature` that defaults the worker DB base/count and skips the shared base DB
- `tests-feature-local -- --dry-run`
  - local convenience dry-run wrapper using the same script and flags
- `tests-ci -- --dry-run`
  - aggregate top-level dry-run entrypoint using the same wrapper, with feature dry-run behavior and filter-aware unit skipping
  - strips `--dry-run` before invoking the unit runner, so unit-targeted filters still execute cleanly
- `tests-ci-local`
  - local convenience wrapper for `tests-ci` that defaults the worker DB base/count and skips the shared base DB
- `tests-ci-local -- --dry-run`
  - local convenience dry-run wrapper using the same script and flags

Local environment configuration:

- the parallel DB helpers and feature runners now load optional env files before resolving DB defaults
- supported files:
  - `not_for_release/testFramework/Support/configs/test-runner.env`
  - `not_for_release/testFramework/Support/configs/test-runner.local.env`
- legacy fallback files are still loaded if present:
  - `not_for_release/testFramework/test-framework.env`
  - `not_for_release/testFramework/test-framework.local.env`
- `ZC_TEST_ENV_FILE=/path/to/file` can be used to point the runners at a different env file explicitly
- [test-runner.env.example](/home/wilt/Projects/zencart/not_for_release/testFramework/Support/configs/test-runner.env.example) shows the supported DB settings:
  - `ZC_TEST_DB_HOST`
  - `ZC_TEST_DB_PORT`
  - `ZC_TEST_DB_USER`
  - `ZC_TEST_DB_PASSWORD`
- this is the preferred way to make local `tests-feature-local` and `tests-ci-local` work with non-default container database hostnames without repeating CLI overrides on every run

Container runtime:

- `not_for_release/testFramework/docker/test-runner/Dockerfile` defines the preferred Zen Cart test-runner image for CI and plugin testing
- `not_for_release/testFramework/test-runner-container-ghcr.md` documents building and publishing the image to GHCR
- DDEV can still be used for interactive local development, but the container image is the preferred repeatable runtime for plugin CI

Lower-level runners:

- `tests-feature-parallel`
  - aggregate feature-only entrypoint that dispatches to the storefront/admin parallel runners and the plugin-filesystem serial bucket based on the requested filter
- `tests-feature-store-parallel`
  - worker-token-aware storefront parallel runner for the current `parallel-candidate` bucket
- `tests-feature-admin-parallel`
  - worker-token-aware admin parallel runner for the current `parallel-candidate` bucket
- `tests-feature-admin-plugin-filesystem`
  - admin-only bucket for the 2 true serial plugin/filesystem tests
- `tests-report-feature-groups-strict`
  - fails when any feature test lacks an explicit isolation tag or uses an invalid explicit combination such as `serial` plus `parallel-candidate`
- `tests-runtime-describe`
  - prints the derived worker-scoped runtime paths for the current environment
- `tests-db-prepare-workers`
  - creates the base and worker-suffixed databases
- `tests-db-prepare-workers -- --dry-run`
  - preview-only version of worker DB creation

Legacy/raw entrypoints were intentionally removed rather than kept as compatibility aliases.
For one-off local runs against specific buckets, prefer calling `phpunit` directly with `--testsuite` and `--group` instead of adding a permanent Composer script for each combination.

### Wrap-up Checklist

To close out this parallelization push cleanly, the remaining work should be:

1. Keep `tests-unit` as the default unit CI path.
2. Keep explicit feature-test grouping enforced through the strict report.
3. Keep validating worker-DB provisioning and snapshot restore under repeated green CI runs.
4. Keep validating worker-scoped log, progress, artifact, and plugin paths under real feature execution.
5. Decide whether the long-term GitHub Actions shape should stay split by store/admin or consolidate around the aggregate feature wrapper.
6. Add only the remaining backlog tests that materially improve confidence; avoid re-expanding the command surface unless a new workflow genuinely needs it.

### Success criteria

Parallelization is probably worth keeping only if it achieves all of the following:

- unit-test wall-clock time drops materially without increased flake rate
- feature-test parallel runs do not show cross-worker DB corruption
- reruns of the same commit produce stable results
- local developer setup remains understandable and does not require fragile manual DB prep

## Non-Framework Integration Changes

Most of the work lives under `not_for_release/testFramework`, but a small number of files outside that tree were changed intentionally to let the test framework run in-process, run in parallel, or expose consistent CI entrypoints.

### CI and command wiring

- `.github/workflows/zc_unit_test_suite.yml`
- `.github/workflows/zc_feature_test_store_suite.yml`
- `.github/workflows/zc_feature_test_admin_suite.yml`
- `composer.json`
- `.gitignore`

These were changed to:

- move GitHub Actions from direct `phpunit` calls to the named CI wrapper commands
- pass worker-database environment settings into the feature CI lanes
- expose the local/CI parallel runner entrypoints through Composer
- ignore local test-runner env files used by the shell wrappers

### Core runtime hooks for the in-process harness

- `includes/functions/functions_urls.php`

This was changed so `zen_redirect()` can throw an in-process redirect exception when the test harness is explicitly capturing redirects, instead of terminating the PHP process.

### Core compatibility shims needed by unit and in-process tests

- `includes/classes/message_stack.php`
- `includes/classes/split_page_results.php`
- `includes/functions/html_output.php`
- `admin/includes/functions/html_output.php`

These were changed to:

- let the harness exercise legacy `messageStack` behavior such as session import and the old `size` property access pattern
- make `messageStack` work safely in reduced unit-test/in-process environments where full template constants are not bootstrapped
- let `splitPageResults` tolerate legacy call signatures still used by admin/store code under test
- make `splitPageResults` handle more complex counting cases used by the feature coverage
- guard `zen_image()` redefinition so mixed admin/store bootstrap paths can coexist in one PHP process during tests

### Admin-page compatibility fixes surfaced by feature coverage

- `admin/countries.php`
- `admin/coupon_admin.php`
- `admin/currencies.php`
- `admin/customer_groups.php`
- `admin/customers.php`
- `admin/orders.php`
- `admin/tax_rates.php`

These changes are small defensive initializations of `*_query_numrows` variables before passing them to `splitPageResults`.

They were needed because the newer in-process coverage exercises these pages more directly and exposed undefined-variable assumptions that were previously easy to miss in the older test setup.

## Current Compatibility Shims

The in-process feature harness now carries a small number of explicit compatibility shims.
These are worth keeping documented because future cleanup will need to distinguish “temporary but required” from “stale and removable”.

### Harness-side shims

- `not_for_release/testFramework/Support/InProcess/execute_admin_request.php`
  - normalizes the process locale for CI environments where `LC_TIME` comes through as `C.UTF-8`
  - defines early test-runner state such as `ROOTCWD`, `TESTCWD`, and `ZENCART_TESTFRAMEWORK_RUNNING`
  - defines `ZENCART_INPROCESS_REDIRECT_CAPTURE` so redirects can be surfaced back to PHPUnit instead of terminating the child request
  - provides an early `zen_image()` stub and minimal template context because admin bootstrap can construct `messageStack` before the normal admin image helper is available

- `not_for_release/testFramework/Support/InProcess/execute_storefront_request.php`
  - normalizes the process locale for CI environments where `LC_TIME` comes through as `C.UTF-8`
  - defines early test-runner state such as `ROOTCWD`, `TESTCWD`, `ZENCART_TESTFRAMEWORK_RUNNING`, and `ZENCART_INPROCESS_REDIRECT_CAPTURE`
  - provides an early `zen_image()` stub and minimal template/icon context because storefront bootstrap can construct `messageStack` before the normal storefront image helper is available
  - provides a minimal `$detect` stub for responsive template code that expects `isMobile()` and `isTablet()` during rendering

### Application-side shims still required by the current in-process design

- `admin/includes/functions/html_output.php`
  - keeps `zen_image()` wrapped in `function_exists(...)`
  - needed because the admin child runner predefines a minimal `zen_image()` before core admin HTML helpers load
  - without this guard, admin requests fatally redeclare `zen_image()`

- `includes/functions/html_output.php`
  - keeps storefront `zen_image()` wrapped in `function_exists(...)`
  - needed for the same reason as the admin side: the storefront child runner predefines a minimal `zen_image()` before core storefront HTML helpers load
  - without this guard, storefront requests fatally redeclare `zen_image()`

- `includes/classes/message_stack.php`
  - adds `add_from_session()` because admin bootstrap calls that method during autoload and the catalog-side `messageStack` implementation did not previously expose it
  - keeps a safe fallback path in `getDefaultFormats()` for early bootstrap phases where the full template/icon environment is not initialized yet
  - without these adjustments, in-process admin/store requests can fail before normal page rendering begins

- `includes/functions/functions_urls.php`
  - throws `Tests\Support\InProcess\InProcessRedirectException` when `ZENCART_INPROCESS_REDIRECT_CAPTURE` is enabled
  - needed because login/setup/checkout flows rely on real redirects, and the in-process runner must convert those redirects into followable PHPUnit responses instead of letting the child request terminate normally
  - removing this hook causes admin and storefront helpers to surface raw `302` responses instead of the expected followed `200` responses

### Cleanup note

An attempted reduction pass showed that some earlier spillover could be moved back into
`not_for_release/testFramework`, but the shims listed above are currently tied to real bootstrap-order
and redirect-capture behavior in the in-process harness. Future removal would require a deeper redesign
of request bootstrapping rather than a simple revert.
