# Template Context DTO and Repository Proposal

## Status

Last updated: 2026-04-23

- `TemplateResolver` is already in place and now owns merged core/plugin template discovery, inheritance lookup, and path metadata.
- `TemplateDto` already exists, but today it acts as a request-local singleton cache of discovered template records, not as a selected-template context object.
- Catalog and admin bootstrap now both resolve the active template through `TemplateResolver`, but they still duplicate the database selection lookup and pass loose arguments into loaders.
- `BaseLanguageLoader`, `PageLoader`, `SideboxFinder`, `html_output.php`, and `functions_templates.php` already consume resolver-derived template records and inheritance chains.
- The main remaining simplification is not "introduce template records" but "introduce one request-level selected-template context object and stop reassembling it in multiple places."

## Purpose

Template state is currently assembled in several places during bootstrap. This note captures a possible simplification using:

- a readonly `TemplateContext` DTO
- a `TemplateSelectionRepository` for database selection
- a `TemplateContextFactory` for request/bootstrap assembly
- the existing `TemplateResolver` for filesystem and plugin-template discovery

The goal is to make template selection, inheritance, plugin template metadata, and language-loader setup use one consistent request-level model.

## Current Shape

Template state is currently spread across bootstrap scripts, loaders, helper functions, and globals.

### Catalog Bootstrap

`includes/init_includes/init_templates.php`:

- queries `TABLE_TEMPLATE_SELECT`
- applies the whitelisted admin `&t=` template override
- creates a `TemplateResolver`
- resolves the selected template record
- assigns `$template_dir`
- defines `DIR_WS_TEMPLATE`, `DIR_WS_TEMPLATE_IMAGES`, and `DIR_WS_TEMPLATE_ICONS`
- loads template settings
- creates the language loader using loose arguments:

```php
$languageLoaderFactory->make('catalog', $installedPlugins, $current_page, $template_dir);
```

### Admin Bootstrap

`admin/includes/init_includes/init_languages.php`:

- queries `TABLE_TEMPLATE_SELECT` before admin template setup
- resolves `$template_dir` through `zen_resolve_template_key()` so storefront template language overrides can apply to admin module pages
- creates the language loader using loose arguments:

```php
$languageLoaderFactory->make('admin', $installedPlugins, $current_page, $template_dir);
```

`admin/includes/init_includes/init_templates.php`:

- performs another template lookup/resolution
- defines admin-side template constants
- creates the legacy `template_func` instance

### Resolver and Loaders

`includes/classes/ResourceLoaders/TemplateResolver.php` currently discovers and resolves available template records from:

- core `includes/templates/*/template_info.php`
- plugin manifests with selectable template metadata
- plugin-provided template paths
- base-template inheritance
- resolver-owned metadata such as `template_path`, `template_catalog_path`, `template_web_path`, and `template_settings_path`

It does not own the selected-template database lookup.

`includes/classes/ResourceLoaders/BaseLanguageLoader.php` receives:

```php
array $pluginList,
string $currentPage,
string $templateDir,
string $fallback = 'english'
```

It then creates its own `TemplateResolver`.

`includes/classes/ResourceLoaders/PageLoader.php` receives installed plugins and current page, then lazily creates its own `TemplateResolver`.

`includes/functions/functions_templates.php` also creates new `TemplateResolver` instances unless one is passed in.

`TemplateDto` already caches the discovered template records used by `TemplateResolver`, so the open gap is request-context assembly rather than template-record storage.

## Main Problem

The repeated constructor arguments are a symptom of a larger issue: template state is not represented as one object.

Today, related state travels separately as:

- `$template_dir`
- `DIR_WS_TEMPLATE`
- `DIR_WS_TEMPLATE_IMAGES`
- `DIR_WS_TEMPLATE_ICONS`
- `$installedPlugins`
- current page
- selected language id/directory
- template DB row/settings
- resolver-derived template record
- inheritance chain

That makes it easy for catalog, admin, language loading, template loading, and helper functions to drift slightly apart.

## Proposed Objects

Important distinction:

- keep `TemplateDto` as the cache of available template records discovered by `TemplateResolver`
- add a separate `TemplateContext` object for the selected template and other request-specific state

Trying to make one object serve both roles would blur two different concerns:

- available template metadata is global to the request and mostly stable across callers
- selected template, language, preview override, and installed-plugin state are request/bootstrap concerns

### TemplateContext

`TemplateContext` should be a readonly request-level snapshot.

Example shape:

```php
namespace Zencart\ResourceLoaders;

final readonly class TemplateContext
{
    public function __construct(
        public string $context, // catalog/admin
        public string $selectedTemplateKey,
        public array $selectedTemplateRecord,
        public array $inheritanceChain,
        public array $installedPlugins,
        public string $currentPage,
        public int $languageId,
        public string $languageDirectory,
        public ?array $templateSelectRow = null,
    ) {
    }

    public function catalogPath(): string
    {
        return $this->selectedTemplateRecord['template_catalog_path'] ?? 'includes/templates/template_default/';
    }

    public function webPath(): string
    {
        return $this->selectedTemplateRecord['template_web_path'] ?? '/includes/templates/template_default/';
    }

    public function settingsPath(): ?string
    {
        return $this->selectedTemplateRecord['template_settings_path'] ?? null;
    }

    public function imagesPath(): string
    {
        return rtrim($this->webPath(), '/') . '/images/';
    }
}
```

Readonly is useful because template state should be stable once the resolver/loaders are created. If the selected template changes mid-request, a new context should be built deliberately.

This DTO should represent the active request only. It should not replace the resolver cache currently held in `TemplateDto`.

### TemplateSelectionRepository

This class should own `TABLE_TEMPLATE_SELECT` lookup behavior.

Example shape:

```php
namespace Zencart\ResourceLoaders;

final class TemplateSelectionRepository
{
    public function __construct(private \queryFactory $db)
    {
    }

    public function findForLanguageId(int $languageId): array
    {
        $sql = "SELECT *, template_language = " . $languageId . " AS choice1, template_language = 0 AS choice2
                FROM " . TABLE_TEMPLATE_SELECT . "
                ORDER BY choice1 DESC, choice2 DESC, template_language";

        $result = $this->db->Execute($sql);

        return $result->fields ?? [];
    }
}
```

This removes duplicate template-selection SQL from catalog and admin bootstrap.

### TemplateContextFactory

The factory should gather runtime state and produce the readonly DTO.

Example shape:

```php
namespace Zencart\ResourceLoaders;

final class TemplateContextFactory
{
    public function __construct(
        private TemplateSelectionRepository $selectionRepository,
        private TemplateResolver $templateResolver,
    ) {
    }

    public function forCatalog(array $installedPlugins, string $currentPage): TemplateContext
    {
        $row = $this->selectionRepository->findForLanguageId((int)$_SESSION['languages_id']);
        $templateKey = $row['template_dir'] ?? 'template_default';

        if (zen_is_whitelisted_admin_ip()) {
            if (isset($_GET['t']) && $this->templateResolver->getTemplateRecord($_GET['t']) !== null) {
                $_SESSION['tpl_override'] = $_GET['t'];
            }
            if (isset($_GET['t']) && $_GET['t'] === 'off') {
                unset($_SESSION['tpl_override']);
            }
            if (isset($_SESSION['tpl_override'])) {
                $templateKey = $_SESSION['tpl_override'];
            }
        }

        return $this->build('catalog', $templateKey, $installedPlugins, $currentPage, $row);
    }

    public function forAdmin(array $installedPlugins, string $currentPage): TemplateContext
    {
        $row = $this->selectionRepository->findForLanguageId((int)$_SESSION['languages_id']);
        return $this->build('admin', $row['template_dir'] ?? 'template_default', $installedPlugins, $currentPage, $row);
    }

    private function build(string $context, string $templateKey, array $installedPlugins, string $currentPage, array $row): TemplateContext
    {
        $record = $this->templateResolver->getTemplateRecord($templateKey)
            ?? $this->templateResolver->getTemplateRecord('template_default');

        if ($record === null) {
            die('Fatal error: template_default could not be resolved.');
        }

        $selectedTemplateKey = $record['template_key'] ?? 'template_default';

        return new TemplateContext(
            context: $context,
            selectedTemplateKey: $selectedTemplateKey,
            selectedTemplateRecord: $record,
            inheritanceChain: $this->templateResolver->getTemplateInheritanceChain($selectedTemplateKey),
            installedPlugins: $installedPlugins,
            currentPage: $currentPage,
            languageId: (int)($_SESSION['languages_id'] ?? 1),
            languageDirectory: (string)($_SESSION['language'] ?? 'english'),
            templateSelectRow: $row,
        );
    }
}
```

## Responsibility Boundaries

### Keep `TemplateResolver` Focused

`TemplateResolver` should continue to answer:

- what template records exist?
- what is a template's filesystem path?
- what is a template's catalog path?
- what is a template's web path?
- is the template plugin-provided?
- what is the inheritance chain?

It should not query `TABLE_TEMPLATE_SELECT`.

It should also continue to own the template-record cache through `TemplateDto`, since that cache is about discovered template availability, not selected-template bootstrap state.

### Move DB Selection Out of Init Scripts

`TemplateSelectionRepository` should answer:

- which template is selected for the current language?
- what is the fallback default row?
- what template settings JSON came from the DB row?

### Let the Factory Bridge Runtime State

`TemplateContextFactory` should answer:

- which context are we in, catalog or admin?
- should catalog apply the whitelisted admin `&t=` preview override?
- what resolved template record should the request use?
- what immutable context should loaders receive?

## Bootstrap Usage

Catalog `includes/init_includes/init_templates.php` could become roughly:

```php
$templateResolver = new TemplateResolver();
$templateContextFactory = new TemplateContextFactory(
    new TemplateSelectionRepository($db),
    $templateResolver,
);

$templateContext = $templateContextFactory->forCatalog($installedPlugins, $current_page);
$template_dir = $templateContext->selectedTemplateKey;

define('DIR_WS_TEMPLATE', $templateContext->catalogPath());
define('DIR_WS_TEMPLATE_IMAGES', $templateContext->imagesPath());
define('DIR_WS_TEMPLATE_ICONS', DIR_WS_TEMPLATE_IMAGES . 'icons/');
```

Admin `admin/includes/init_includes/init_languages.php` could use:

```php
$templateContext = $templateContextFactory->forAdmin($installedPlugins, $current_page);
$template_dir = $templateContext->selectedTemplateKey;

$languageLoader = $languageLoaderFactory->make('admin', $templateContext, $current_page);
```

Admin `admin/includes/init_includes/init_templates.php` could then reuse the existing `$templateContext` instead of querying the DB again:

```php
$template_dir = $templateContext->selectedTemplateKey;

define('DIR_WS_TEMPLATE', $templateContext->catalogPath());
define('DIR_WS_TEMPLATE_IMAGES', $templateContext->imagesPath());
define('DIR_WS_TEMPLATE_ICONS', DIR_WS_TEMPLATE_IMAGES . 'icons/');
```

## Loader Usage

`LanguageLoaderFactory` could move from:

```php
make(string $context, array $installedPlugins, string $currentPage, string $templateDirectory, string $fallback = 'english')
```

to:

```php
make(string $context, TemplateContext $templateContext, string $currentPage, string $fallback = 'english')
```

`BaseLanguageLoader` could store:

```php
protected TemplateContext $templateContext;
```

instead of separate `$pluginList` and `$templateDir` fields.

`PageLoader::init()` could move from:

```php
public function init(array $installedPlugins, string $mainPage, FileSystem $fileSystem): void
```

to:

```php
public function init(TemplateContext $templateContext, string $mainPage, FileSystem $fileSystem): void
```

That lets page and template lookup use the same template key, inheritance chain, current page, and installed plugin list as language loading.

## Compatibility Surface

For an incremental migration, keep existing globals/constants:

- `$template_dir`
- `DIR_WS_TEMPLATE`
- `DIR_WS_TEMPLATE_IMAGES`
- `DIR_WS_TEMPLATE_ICONS`
- `$template`
- `$languageLoader`

But derive them from `TemplateContext`.

That means older templates and plugins continue working while newer internals use one coherent model.

## Migration Plan

1. Keep `TemplateDto` as the resolver cache of discovered template records; do not repurpose it into request context.
2. Add `TemplateContext`.
3. Add `TemplateSelectionRepository`.
4. Add `TemplateContextFactory`.
5. Update catalog `init_templates.php` to create `$templateContext` and derive existing globals/constants from it.
6. Update admin `init_languages.php` to create `$templateContext` using the same factory.
7. Update admin `init_templates.php` to reuse `$templateContext` instead of performing another DB/template resolver lookup.
8. Update `LanguageLoaderFactory` and `BaseLanguageLoader` to accept `TemplateContext`, while retaining backwards-compatible overload/wrapper behavior if needed.
9. Update `PageLoader::init()` to accept `TemplateContext`, again preserving compatibility where needed.
10. Gradually update helper functions in `functions_templates.php` and `html_output.php` to accept optional `TemplateContext` or reuse a shared resolver/context.
11. Add focused tests around:
    - default template fallback
    - language-specific template selection
    - admin language loading using storefront template overrides
    - catalog `&t=` preview override
    - plugin-provided selectable templates
    - inheritance chain resolution
    - plugin overlay lookup

## Expected Benefits

- One place for selected-template DB lookup.
- Clear separation between discovered template records and the request-selected template.
- Fewer repeated `TemplateResolver` constructions at call sites that currently re-create one only to fetch already-cached data.
- Fewer loose constructor arguments.
- Less duplication between catalog/admin bootstrap.
- Cleaner distinction between selected template state and available template records.
- Easier tests for template selection and inheritance.
- Better future path for incorporating more of `template_func` and `PageLoader` resolution behavior into resource-loader classes.

## Current Gaps This Proposal Targets

Even after the resolver work already landed, these gaps remain:

- catalog and admin each still perform their own `TABLE_TEMPLATE_SELECT` lookup logic
- admin language bootstrap and admin template bootstrap do not yet share one resolved request object
- loaders still receive loose constructor/init arguments instead of a single selected-template context
- helper functions still instantiate resolver objects ad hoc instead of receiving shared request state where that would clarify behavior

## Caution

This should be an incremental refactor. Template bootstrap is old, central plumbing. The safer path is to introduce the DTO/repository/factory first, keep the old globals and constants as compatibility outputs, and then migrate loaders/helper functions one at a time.
