<?php
/**
 * @copyright Copyright 2003-2025 Zen Cart Development Team
 * @license https://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: DrByte 2025 Sep 18 Modified in v2.2.0 $
 */
if (!defined('IS_ADMIN_FLAG')) {
    die('Illegal Access');
}

/**
 * @since ZC v3.0.0
 */
function zen_get_template_resolver_with_installed_plugins(
    ?\Zencart\ResourceLoaders\TemplateResolver $resolver = null
): \Zencart\ResourceLoaders\TemplateResolver {
    global $installedPlugins;

    if ($resolver !== null) {
        return $resolver;
    }

    return new \Zencart\ResourceLoaders\TemplateResolver(
        null,
        null,
        null,
        $installedPlugins ?? null
    );
}

/**
 * Get all template directories found in catalog folder structure
 *
 * @since ZC v1.5.8
 */
function zen_get_catalog_template_directories(bool $include_template_default = false): array
{
    $resolver = zen_get_template_resolver_with_installed_plugins();
    return $resolver->getSelectableTemplates((bool)$include_template_default);
}

/**
 * @since ZC v3.0.0
 */
function zen_get_template_search_directories(
    string $templateKey,
    array $subdirectories = [],
    bool $includeTemplateDefault = true,
    ?\Zencart\ResourceLoaders\TemplateResolver $resolver = null
): array
{
    $resolver = zen_get_template_resolver_with_installed_plugins($resolver);
    $chain = $resolver->getTemplateInheritanceChain($templateKey);
    if ($includeTemplateDefault !== true) {
        $chain = array_values(array_filter($chain, static fn(string $item): bool => $item !== 'template_default'));
    }

    $directories = [];
    foreach ($chain as $chainTemplateKey) {
        $templatePath = $resolver->getTemplateFilesystemPath($chainTemplateKey);
        if ($templatePath === null) {
            continue;
        }

        if ($subdirectories === []) {
            $directories[] = rtrim($templatePath, '/') . '/';
            continue;
        }

        foreach ($subdirectories as $subdirectory) {
            $directories[] = rtrim($templatePath, '/') . '/' . trim($subdirectory, '/') . '/';
        }
    }

    return array_values(array_unique($directories));
}

/**
 * @since ZC v3.0.0
 */
function zen_get_template_inheritance_chain(
    string $templateKey,
    bool $includeTemplateDefault = true,
    ?\Zencart\ResourceLoaders\TemplateResolver $resolver = null
): array {
    $resolver = zen_get_template_resolver_with_installed_plugins($resolver);
    $chain = $resolver->getTemplateInheritanceChain($templateKey);
    if ($includeTemplateDefault !== true) {
        $chain = array_values(array_filter($chain, static fn(string $item): bool => $item !== 'template_default'));
    }

    return array_values(array_unique($chain));
}

/**
 * @since ZC v3.0.0
 */
function zen_get_template_catalog_override_directories(
    string $templateKey,
    string $catalogBasePath,
    bool $includeTemplateDefault = true,
    ?\Zencart\ResourceLoaders\TemplateResolver $resolver = null
): array {
    global $installedPlugins;

    $resolver = zen_get_template_resolver_with_installed_plugins($resolver);
    $catalogBasePath = trim($catalogBasePath, '/');
    $directories = [];

    foreach (zen_get_template_inheritance_chain($templateKey, $includeTemplateDefault, $resolver) as $chainTemplateKey) {
        $record = $resolver->getTemplateRecord($chainTemplateKey);
        if ($record !== null && !empty($record['is_plugin_template']) && !empty($record['plugin_key']) && !empty($record['plugin_version'])) {
            $directories[] = 'zc_plugins/' . $record['plugin_key'] . '/' . $record['plugin_version'] . '/catalog/' . $catalogBasePath . '/' . $chainTemplateKey . '/';
            continue;
        }

        $directories[] = $catalogBasePath . '/' . $chainTemplateKey . '/';

        foreach (($installedPlugins ?? []) as $plugin) {
            if (empty($plugin['unique_key']) || empty($plugin['version'])) {
                continue;
            }
            $directories[] = 'zc_plugins/' . $plugin['unique_key'] . '/' . $plugin['version'] . '/catalog/' . $catalogBasePath . '/' . $chainTemplateKey . '/';
        }
    }

    return array_values(array_unique($directories));
}

/**
 * @since ZC v3.0.0
 */
function zen_get_template_language_override_directories(
    string $templateKey,
    string $languageRootPath,
    string $language,
    string $extraPath = '',
    bool $includeTemplateDefault = true,
    ?\Zencart\ResourceLoaders\TemplateResolver $resolver = null
): array {
    $resolver = zen_get_template_resolver_with_installed_plugins($resolver);
    $languageRootPath = rtrim($languageRootPath, '/') . '/';
    $extraPath = trim($extraPath, '/');
    $directories = [];

    foreach (zen_get_template_inheritance_chain($templateKey, $includeTemplateDefault, $resolver) as $chainTemplateKey) {
        $directory = $languageRootPath . $language . '/';
        if ($extraPath !== '') {
            $directory .= $extraPath . '/';
        }
        $directory .= $chainTemplateKey . '/';
        $directories[] = $directory;
    }

    return array_values(array_unique($directories));
}

/**
 * @since ZC v3.0.0
 */
function zen_get_template_first_language_directories(
    string $templateKey,
    string $languageRootPath,
    bool $includeTemplateDefault = true,
    ?\Zencart\ResourceLoaders\TemplateResolver $resolver = null
): array {
    $resolver = zen_get_template_resolver_with_installed_plugins($resolver);
    $languageRootPath = rtrim($languageRootPath, '/') . '/';
    $directories = [];

    foreach (zen_get_template_inheritance_chain($templateKey, $includeTemplateDefault, $resolver) as $chainTemplateKey) {
        $directories[] = $languageRootPath . $chainTemplateKey . '/';
    }

    return array_values(array_unique($directories));
}

/**
 * @since ZC v3.0.0
 */
function zen_get_template_init_file_path(
    string $templateKey,
    ?\Zencart\ResourceLoaders\TemplateResolver $resolver = null
): ?string {
    $resolver = zen_get_template_resolver_with_installed_plugins($resolver);
    $templatePath = $resolver->getTemplateFilesystemPath($templateKey);
    if ($templatePath === null) {
        return null;
    }

    return rtrim($templatePath, '/') . '/template_init.php';
}

/**
 * @since ZC v3.0.0
 */
function zen_get_template_screenshot_web_path(
    string $templateKey,
    ?\Zencart\ResourceLoaders\TemplateResolver $resolver = null
): ?string {
    $resolver = zen_get_template_resolver_with_installed_plugins($resolver);
    $record = $resolver->getTemplateRecord($templateKey);
    if ($record === null || empty($record['screenshot']) || empty($record['template_web_path'])) {
        return null;
    }

    return rtrim($record['template_web_path'], '/') . '/images/' . ltrim($record['screenshot'], '/');
}

/**
 * @since ZC v3.0.0
 */
function zen_resolve_template_key(?\Zencart\ResourceLoaders\TemplateResolver $resolver = null): string
{
    $templateSelect = new \Zencart\Templates\TemplateSelect();
    $templateKey = $templateSelect->getActiveTemplateDir() ?? '';

    $resolver = zen_get_template_resolver_with_installed_plugins($resolver);
    $record = $resolver->getTemplateRecord($templateKey);
    if ($record === null) {
        return 'template_default';
    }
    return $record['template_key'] ?? 'template_default';
}

/**
 * @since ZC v1.5.8
 */
function zen_register_new_template(string $template_dir, int|string $language_id): false|int|string
{
    $templateSelect = new \Zencart\Templates\TemplateSelect();
    return $templateSelect->registerNewTemplate($template_dir, (int)$language_id);
}

/**
 * @return array of language_name and language_id entries
 * @since ZC v1.5.8
 */
function zen_get_template_languages_not_registered(): array
{
    $templateSelect = new \Zencart\Templates\TemplateSelect();
    return $templateSelect->getUnregisteredTemplateLanguages();
}

/**
 * @param numeric $id
 * @param string $template_dir
 * @since ZC v1.5.8
 */
function zen_update_template_name_for_id(int|string $id, string $template_dir): void
{
    $templateSelect = new \Zencart\Templates\TemplateSelect();
    $templateSelect->updateTemplateNameForId((int)$id, $template_dir);
}

/**
 * @param numeric $id
 * @return bool whether template existed before delete
 * @since ZC v1.5.8
 */
function zen_deregister_template_id(int|string $id): bool
{
    $templateSelect = new \Zencart\Templates\TemplateSelect();
    return $templateSelect->deregisterTemplateId((int)$id);
}
