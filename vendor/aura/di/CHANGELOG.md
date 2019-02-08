# CHANGELOG

## 3.4.0

- (CHG) LazyArray now extends ArrayObject. PR #151.

## 3.3.0

- (DOC) Update documentation. PR #140.

- (CHG) Rearranging code to achieve full test coverage with the existing test suite. PR #141.

- (ADD) ResolutionHelper. PR #143. Fixes #133.

- (Add) ConfigCollection. PR #146.

- (CHG) Update Reflector.php for PHP 7.2 compatibility. PR #148.

- (CHG) Travis CI changes. PR #152.

- (CHG) Removed CHANGES.md. Added CHANGELOG.md

## 3.2.0

This release adds three new features.

- (ADD) LazyInclude and LazyRequire can now recieve a LazyValue as a filename, so that the filename is not resolved until the include/require is invoked.

- (ADD) Allow direct use of lazies in Lazy; cf PR #128.

- (ADD) Add a new LazyCallable type for injecting callable services; cf. PR #129.

- (CHG) LazyValue now resolves lazies itself; cf. PR #137.

- (ADD) Add a new LazyArray type for injecting arrays of lazy-resolved values; cf PR #138.

There are also carious documentation improvements, and the package now provides (via Composer) the virtual package `container-interop-implementation`.

## 3.1.0

This release has one documentation addition, and one feature addition.

- Added documentation for upgrading/migrating from 2.x to 3.x

- In ContainerBuilder::newConfiguredInstance(), added the ability to pass a ContainerConfig object instance as a config specification.

## 3.0.0

This is the second beta release of this library, and likely the last before a stable release (barring unexpected feature changes and bugfixes).

- (BRK) _Container_ methods `newInstance()` and `get()` now lock the _Container_ automatically. (See note below.)

- (CHG) `$di->params` now allows `null` as a parameter value.

- (ADD) ContainerConfigInterface

- (ADD) Better exception messages.

- (DOC) Add and update documentation.

* * *

Regarding auto-locking of the _Container_ after `newInstance()` and `get()`:

This prevents errors from premature unification of params/setters/values/etc. in the _Resolver_. As a result, do not use _Container_ `newInstance()` or `get()` before you are finished calling `$params`, `$setters`, `$values`, `set()`, or other methods that modify the _Container_. Use the `lazy*()` equivalents to avoid auto-locking the _Container_.

## 3.0.0-beta2

- BREAK: Rename `Aura\Di\_Config\AbstractContinerTest` to `Aura\Di\AbstractContainerConfigTest`.

- BREAK: The ContainerBuilder no longer accepts pre-built services, only config class names.

- BREAK: Remove the `Aura\Di\Exception\ReflectionFailure` exception, throw the native `\ReflectionException` instead.

- BREAK: Previously, the AutoResolver would supply an empty array for array typehints, and null for non-typehinted parameters. It no longer does so; it only attempts to auto-resolve class/interface typehints.

- CHANGE: Add .gitattributes file for export-ignore values.

- CHANGE: Allow PHP 5.5 as the minimum version.

- ADD: Allow constructor params to be specified using position number; this is in addition to specifying by $param name. Positional params take precendence over named params, to be consistent pre-existing behavior regarding merged parameters.

- DOCS: Update documentation, add bookdown files.

## 3.0.0-beta1

- BREAK: Rename `Aura\Di\_Config\AbstractContinerTest` to `Aura\Di\AbstractContainerConfigTest`.

- BREAK: The ContainerBuilder no longer accepts pre-built services, only config class names.

- BREAK: Remove the `Aura\Di\Exception\ReflectionFailure` exception, throw the native `\ReflectionException` instead.

- BREAK: Previously, the AutoResolver would supply an empty array for array typehints, and null for non-typehinted parameters. It no longer does so; it only attempts to auto-resolve class/interface typehints.

- CHANGE: Add .gitattributes file for export-ignore values.

- CHANGE: Allow PHP 5.5 as the minimum version.

- ADD: Allow constructor params to be specified using position number; this is in addition to specifying by $param name. Positional params take precendence over named params, to be consistent pre-existing behavior regarding merged parameters.

- DOCS: Update documentation, add bookdown files.

## 3.0.0-alpha1

This is releases moves the AbstractContainerTest to is proper location. Sorry for making two releases in a row so quickly.

## 2.2.4

* Fixes #91 property-read designation causes PHPStorm to have syntax error. Changed @property-read to @property so they will still be auto-completed by IDE. Thank you David Stockton, Brandon Savage.
* Fix the doc comments.

## 2.2.3

This release provides a better message for _Exception\ReflectionFailure_, via issue #73.

## 2.2.2

This is releases moves the AbstractContainerTest to is proper location. Sorry for making two releases in a row so quickly.

## 2.2.1

This release restructures the testing and support files, particularly Composer. Note the changes in how tests are run in the new README.md.

## 2.2.0

This release has a couple of feature improvements: traits in ancestor classes and in ancestor traits are now honored, and the DI container can now be serialized and unserialized (unless it contains closures).

- ADD: The Factory now gets all traits of ancestor classes & ancestor traits.

- NEW: Class `Aura\Di\Reflection` decorates `ReflectionClass` to permit serialization of the DI Container for caching.

- FIX: The ContainerBuilder now call setAutoResolve() early, rather than late.

- FIX: If the class being factories has no __construct() method, instantiate without constructor.

- DOC: Update documentation and support files.

## 2.1.0

This release incorporates functionality to optionally disable auto-resolution.
By default it remains enabled, but this default may change in a future version.

- Add Container::setAutoResolve(), Factory::setAutoResolve(), etc. to allow
  disabling of auto-resolution

- When auto-resolution is disabled, Factory::newInstance() now throws
  Exception\MissingParam when a constructor param has not been defined

- ContainerBuilder::newInstance() now takes a third param to enable/disable
  auto-resolution

- AbstractContainerTest now allows you to enable/disable auto-resolve for the
  tests via a new getAutoResolve() method

## 2.0.0

- DOC: In README, note that magic-call setters will not work.

- BRK: Related to testing under PHP 5.3, remove the ContainerAssertionsTrait.
  The trait is not 5.3 compatible, so it has to go. Instead, you can extend the
  Aura\Di\_Config\AbstractContainerTest in tests/container/src/ and override the
  provideGet() and provideNewInstance() methods. Sorry for the hassle.

## 2.0.0-beta2

Second beta release.

- REF: Extract object creation from Container into Factory

- DOC: Complete README rewrite, update docblocks

- ADD: The Factory now supports setters from traits.

- ADD: LazyValue functionality.

- ADD: Auto-resolution of typehinted constructor parameters, and of array typehints with no default value, along with directed auto-resolution.

- ADD: ContainerAssertionsTrait so that outehr packages can more easily test their container config classes.

## 2.0.0-beta1

Initial 2.0 beta release.

- _Container_ v1 configurations should still work, with one exception: the `lazyCall()` method has been removed in favor of just `lazy()`. Replace `lazyCall()` with `lazy()` and all should be well.

- Now compatible with PHP 5.3.

- Uses PSR-4 autoloading instead of PSR-0.

- The package now has a series of _Lazy_ classes to represent different types of lazy behaviors, instead of using anonymous functions.

- No more cloning of _Container_ objects; that was a holdover from when we had sub-containers very early in v1 and never really used.

- Removed _Forge_ and placed functionality into _Container_.

- Removed the old _Config_ object; `$params` and `$setter` are now properties on the Container.

- No more top-level '*' config element.

- Renamed _Container_ `getServices()` to `getInstances()`.

- Renamed _Container_ `getDefs()` to `getServices()`.

- Added _ContainerBuilder_ and new _Config_ object for two-stage configuration.

- Now honors $setter values on interface configuration; that is, you can configure a setter on an interface, and classes implementing that interface will honor that value unless overridden by a class parent.

Thanks to HariKT, Damien Patou, Jesse Donat, jvb, and Grummfy for their contributions leading to this release!

## 1.1.2

Hygeine release.

- Add test for #38 -- looks OK
- Merge pull request #32 from harikt/patch-dev
- Add a benching script

## 1.1.1

- [CHG] Config now throws Exception\SetterMethodNotFound when a setter method
  is configured but does not exist on the class

- [CHG] Container::set() now throws Exception\ServiceNotObject when the
  service being set is not an object.

- [CHG] Config now throws Exception\ReflectionFailure when reflection fails.

- [DOC] Typo fixes and clarifications; add PHP 5.5 to Travis build

- [DEL] Remove unused exceptions ContainerExists and ContainerNotFound

## 1.1.0

- [ADD] Container::lazyInclude() and lazyRequire() to lazily include or
  require files

- [ADD] Container::lazyCall() to lazily make a call to any callable, with
  sequential (not named) parameters, just like with call_user_func()

- [ADD] Container::newFactory() method

- [NEW] Factory class, to create new class instances using the Forge

- [REF] Refactor Forge::newInstance() extract method mergeParams(), and make
  allowance for positional override params (these take precedence over named
  override params)

## 1.0.1

(This change log includes notes from in release 1.0.0, which failed to specify
them.)

- [ADD] Config now honors setters on trait methods.

- [ADD] Method Container::lazy() for general-purpose lazy params (i.e.,
  any param can now be lazy, not just services and class instantiations.)

Special thanks to Yuya Takeyama for working on the documentation, along with
Akihito Koriyama and Michal Amerek.

## 1.0.0

- The Forge::newInstance() method now has a third param, $setters, to allow
  passing of setter injection values.
