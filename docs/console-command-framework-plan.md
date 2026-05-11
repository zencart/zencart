# Console Command Framework Status

## Status

Last updated: 2026-04-28

- Core console runtime is implemented and reachable through both `zc_cli.php` and `bin/zencart`.
- Core framework classes now exist under `includes/classes/Console/`.
- Built-in `list`, `help`, `plugin:list`, `version:show`, and `config:get` commands are implemented and executable.
- Convention-based plugin discovery from `zc_plugins/<Plugin>/<Version>/Console/commands.php` is implemented.
- A proof plugin command exists in the test framework (`zen-test:demo`) and is exercised through the kernel in unit tests.
- Security hardening is now partially implemented: plugin discovery is restricted to enabled plugins from plugin-control state, command exceptions are wrapped in a top-level kernel boundary, and discovery warnings use plugin-relative paths.
- Coverage now includes input parsing, kernel routing, built-in help/list behavior, plugin discovery policy, duplicate-name handling, broken plugin definitions, safe exception handling, external-process invocation of `zc_cli.php` and `bin/zencart`, and degraded bootstrap coverage for missing DB config, missing MySQL extension, and DB connection failure.
- Console methods introduced by this framework are documented with `@since ZC v3.0.0` docblocks.
- Remaining work is mainly follow-up refinement: richer command metadata/schema, broader built-in command coverage, and final documentation cleanup.

## Goal

This framework provides a core console-command runtime for Zen Cart that:

- provides a stable CLI runtime and command-discovery system
- supports both core commands and plugin-provided commands
- keeps command execution decoupled from admin-page and web-request flows
- makes future command authoring predictable and testable
- does not rely on any third-party console framework or command library

The framework itself should live in core.

Plugin commands should extend the framework, not provide it.

## Non-Goals

This framework does not depend on third-party console tooling.

That means:

- no Symfony Console dependency
- no Laravel Artisan-style dependency
- no external CLI parsing package as the foundation

The runtime, command registry, discovery layer, input parsing, and output helpers are implemented in Zen Cart core.

## Why Core, Not Plugin

The base command system is infrastructure. It exists in core before any plugin can participate.

Reasons to keep the framework in core:

- plugin commands need a stable bootstrap path
- command discovery needs a trusted central registry
- input/output handling and exit codes should be consistent across all commands
- one plugin should not define the runtime that every other plugin depends on

Plugins register commands into the core runtime through a documented convention.

## Current Architecture

Core pieces:

- `bin/zencart` or `zc_cli.php`
  - CLI entrypoint
- `includes/classes/Console/ConsoleKernel.php`
  - bootstraps the console runtime
- `includes/classes/Console/ConsoleCommand.php`
  - base command contract
- `includes/classes/Console/CommandRegistry.php`
  - holds registered commands
- `includes/classes/Console/CommandResolver.php`
  - resolves a command name or alias
- `includes/classes/Console/ConsoleInput.php`
  - parsed args/options accessor
- `includes/classes/Console/ConsoleOutput.php`
  - stdout/stderr helpers
- `includes/classes/Console/PluginCommandDiscovery.php`
  - discovers commands from installed plugins
- `includes/classes/Console/TrustedPluginVersionResolver.php`
  - resolves enabled plugin/version pairs allowed to expose commands

Implementation notes:

- the actual entrypoint is `zc_cli.php`, while `bin/zencart` is a thin wrapper that requires it
- CLI bootstrap is isolated in `includes/application_cli_bootstrap.php`
- plugin discovery is now gated by a trusted allowlist of enabled plugins resolved from plugin-control state in CLI bootstrap
- plugin discovery still scans version directories on disk for those trusted plugin keys and registers plugin console namespaces into the Aura PSR-4 loader before loading `Console/commands.php`
- command definitions currently support `ConsoleCommand` instances or `ConsoleCommand` class names returned from `commands.php`

## Command Contract

Each command should define:

- a unique name, like `cache:clear`
- a short description
- optional aliases
- optional argument and option definitions
- a `handle(ConsoleInput $input, ConsoleOutput $output): int` method

The return value should be the shell exit code.

Argument parsing and output formatting remain lightweight and first-party.

Implementation notes:

- `ConsoleCommand` currently requires `getName()`, `getDescription()`, and `handle(...)`
- optional aliases are supported through `getAliases(): array`
- usage/help text is currently supported through `getUsageLines(): array`
- explicit argument and option definition metadata has not been added yet; commands read parsed arguments/options from `ConsoleInput`

## Command Discovery

The framework supports two sources of commands:

- core commands
- plugin commands

### Core Commands

Core commands live in:

- `includes/classes/Console/Commands/`

These are registered directly by the kernel.

### Plugin Commands

Plugin command discovery uses this convention:

- `zc_plugins/<Plugin>/<Version>/Console/commands.php`

That file returns an array of command classes or command-definition objects.

Implementation notes:

- discovery currently requires `manifest.php` to be present for the plugin version directory before it will attempt to load `Console/commands.php`
- discovery is now filtered to the enabled plugin/version pairs returned by `TrustedPluginVersionResolver`
- one broken plugin definition does not abort discovery; discovery collects errors and the kernel surfaces them as warnings on stderr during boot

## Plugin Integration

- core loads installed plugins
- discovery checks for `Console/commands.php`
- any commands found there are registered

Possible future metadata additions, if needed:

- `consoleCommands`
- `consoleNamespace`
- `consoleBootstrap`

## Runtime Flow

Execution path:

1. CLI entrypoint boots Zen Cart in command mode.
2. Kernel registers built-in commands.
3. Kernel discovers and registers plugin commands.
4. Resolver finds the requested command.
5. Input and output objects are passed to the command.
6. Command returns an exit code.
7. The runtime exits with that code.

## Implemented Commands

- `list`
  - show available commands
- `help <command>`
  - show command usage/details
- `plugin:list`
  - list plugin-manager state available to the CLI runtime
- `version:show`
  - show application and database version information
- `config:get`
  - show a single configuration value by key
- `zen-test:demo`
  - proof plugin command in the test framework used to validate plugin command discovery

Implementation notes:

- `list`
- `help <command>`
- `plugin:list`
- `version:show`
- `config:get`
- plugin proof command: `zen-test:demo` in `not_for_release/testFramework/Support/plugins/zenTestPlugin/v1.0.0/Console/`

## Cron And Automation Use

The CLI runtime is intended for shell execution, including cron jobs and maintenance automation.

Typical invocation patterns:

- `php /path/to/zc_cli.php list`
- `php /path/to/zc_cli.php config:get STORE_NAME`
- `/path/to/bin/zencart version:show`

Operational notes:

- cron should invoke the CLI entrypoint directly, not a web URL
- DB-backed commands require store configuration and a working DB connection
- command exit codes are suitable for shell scripting and scheduled jobs

## Error Handling

The runtime is defensive.

Requirements and current behavior:

- one broken plugin command should not break all console usage
- duplicate command names should be detected clearly
- registration failures should be visible in error output
- command exceptions should produce a non-zero exit code
- the `list` command should still work even if some plugin commands fail to load, while surfacing those failures clearly

Implementation notes:

- the kernel now wraps command execution in a top-level exception boundary and returns exit code `1` for uncaught command failures
- default exception output is intentionally terse and prompts the operator to re-run with `--verbose`
- verbose mode currently exposes the exception class and message, but not a stack trace
- broken plugin definitions and duplicate command-name collisions are surfaced as warnings while leaving core commands available

## Security Review

Originally identified concerns:

- plugin command discovery trusted any plugin directory on disk that contained `manifest.php`, even if the plugin was not installed or enabled through plugin-manager state
- plugin `Console/commands.php` files were executed during discovery, so dropped or leftover plugin code under `zc_plugins` could become executable in CLI mode
- command execution was not wrapped in a top-level exception boundary, so uncaught exceptions could leak stack traces, absolute paths, and internal runtime details to stderr
- plugin discovery warnings included full filesystem paths to failing command definition files, which could leak path details in shared CI logs

Current status:

- addressed: discovery now resolves trusted plugin versions from enabled plugin-control state via `TrustedPluginVersionResolver`
- addressed: `Console/commands.php` loading is now limited to the enabled plugin/version allowlist instead of every plugin directory on disk
- addressed: command execution is now wrapped in a top-level kernel `try/catch`
- addressed: discovery warnings now use plugin-relative identifiers instead of absolute filesystem paths
- further addressed: process-level CLI coverage now exists for normal `zc_cli.php` and `bin/zencart` invocation, plus degraded paths for missing DB config and for running PHP without the MySQL extension
- addressed: DB connection failure coverage is now exercised in the intended DDEV runtime where the MySQL connector is present

Ongoing constraints:

- keep plugin command discovery restricted to trusted plugin-control state and treat enabled-only discovery as the current policy
- keep command failures behind a controlled top-level exception boundary with safe default output
- keep detailed runtime diagnostics behind explicit verbose/debug behavior
- treat console command loading as a privileged runtime and avoid assuming that "on disk" is equivalent to "trusted and active"

### Security Remediation Checklist

1. Completed: replace raw filesystem-wide plugin discovery with plugin-manager-aware discovery.
2. Completed: disabled plugins do not expose console commands; enabled-only is the current policy.
3. Completed: resolve plugin versions for discovery from trusted plugin-control state instead of every `zc_plugins/*/*` directory on disk.
4. Completed: keep `Console/commands.php` loading behind that filtered plugin list so uninstalled or stray plugin code is not executed.
5. Completed: add a top-level `try/catch` around command `handle()` execution in the kernel.
6. Completed: return a controlled non-zero exit code for uncaught command failures.
7. Completed: emit a safe default error message for command exceptions without stack traces or sensitive internals.
8. Completed in minimal form: support `--verbose` for more detail without printing a stack trace.
9. Completed: replace absolute-path discovery warnings with safer plugin-relative identifiers.
10. Completed: add unit tests for uninstalled-plugin directories being ignored.
11. Completed: add unit tests for the chosen disabled-plugin policy.

## Follow-Up Work

- add richer command metadata for arguments and options if command surface area grows
- decide whether command help should eventually expose structured option definitions instead of usage strings alone
- expand built-in command coverage only where there is a clear operational need
- fold any remaining implementation notes into permanent developer documentation once the framework shape stabilizes

## Command Authoring Principles

Console commands should:

- use services and repositories directly where practical
- avoid depending on web-request globals
- avoid assuming admin-page rendering context
- be safe for unattended execution
- produce explicit exit codes and readable output

Commands should be small wrappers over domain logic where possible, not giant procedural scripts.

## Testing Strategy

Coverage priorities for the framework are:

- command registration
- command resolution by name and alias
- duplicate-name conflicts
- plugin command discovery
- command execution and exit codes
- graceful handling of broken plugin command definitions

Feature-level coverage now exists for the first read-only business commands, and broader command-level integration coverage can expand from there.

Current implementation notes:

- `not_for_release/testFramework/Unit/testsSundry/ConsoleInputTest.php`
- `not_for_release/testFramework/Unit/testsSundry/ConsoleKernelTest.php`
- `not_for_release/testFramework/Unit/testsSundry/PluginCommandDiscoveryTest.php`
- `not_for_release/testFramework/Unit/testsSundry/TrustedPluginVersionResolverTest.php`
- `not_for_release/testFramework/Unit/testsSundry/TestFrameworkRunnersTest.php` now covers external-process runs of `zc_cli.php`, `bin/zencart`, the `php -n` degraded path without the MySQL extension, the missing-DB-config degraded path, and the DB-connection-failure degraded path in DDEV
- `not_for_release/testFramework/FeatureAdmin/PluginTests/PluginListCommandTest.php` covers `plugin:list` against real plugin-manager state after installing a plugin through the admin plugin manager flow
- `not_for_release/testFramework/Unit/testsSundry/ConfigGetCommandTest.php` covers `config:get`
- `not_for_release/testFramework/FeatureAdmin/PluginTests/ConfigGetCommandTest.php` covers `config:get` against seeded configuration rows in the test database
- `not_for_release/testFramework/Unit/testsSundry/VersionShowCommandTest.php` covers `version:show`
- `not_for_release/testFramework/FeatureAdmin/PluginTests/VersionShowCommandTest.php` covers `version:show` against real project-version rows in the seeded test database

Still missing:

- broader feature-level coverage for additional core commands beyond `plugin:list`, `version:show`, and `config:get`

## Remaining Considerations

- keep plugin discovery filtered through trusted plugin-manager state rather than treating any on-disk plugin as active
- decide whether command metadata should grow beyond `getUsageLines()` into richer argument and option definitions
- prefer adding new built-in commands only where they cover a clear operational or maintenance need
