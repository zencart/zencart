<?php
/**
 * @copyright Copyright 2003-2026 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 */

namespace Zencart\ResourceLoaders;

use Zencart\DbRepositories\PluginControlRepository;
use Zencart\DbRepositories\PluginControlVersionRepository;
use Zencart\PluginManager\PluginManager;
use Zencart\Templates\TemplateDto;

/**
 * @since ZC v3.0.0
 */
class TemplateResolver
{
    private string $catalogRoot;
    private string $coreTemplatesPath;
    private string $pluginsRoot;
    private ?PluginManager $pluginManager = null;
    private ?array $installedPlugins = null;

    private static array $templateRecords = [];
    private static ?string $loadedContext = null;

    /**
     * @since ZC v3.0.0
     */
    public function __construct(
        ?string $catalogRoot = null,
        ?string $coreTemplatesPath = null,
        ?string $pluginsRoot = null,
        ?array $installedPlugins = null,
        ?PluginManager $pluginManager = null
    )
    {
        $this->catalogRoot = $this->normalizeDirectory($catalogRoot ?? (defined('DIR_FS_CATALOG') ? DIR_FS_CATALOG : dirname(__DIR__, 2)));
        $this->coreTemplatesPath = $this->normalizeDirectory($coreTemplatesPath ?? $this->catalogRoot . '/includes/templates');
        $this->pluginsRoot = $this->normalizeDirectory($pluginsRoot ?? $this->catalogRoot . '/zc_plugins');
        $this->installedPlugins = $installedPlugins;

        if ($pluginManager !== null) {
            $this->pluginManager = $pluginManager;
            return;
        }

        global $db;
        if ($this->installedPlugins === null && is_object($db) && method_exists($db, 'Execute')) {
            $this->pluginManager = new PluginManager(new PluginControlRepository($db), new PluginControlVersionRepository($db));
        }
    }

    /**
     * @since ZC v3.0.0
     */
    public function getSelectableTemplates(bool $includeTemplateDefault = false): array
    {
        $templates = $this->getTemplateRecords();
        if ($includeTemplateDefault) {
            return $templates;
        }

        unset($templates['template_default']);
        return $templates;
    }

    /**
     * @since ZC v3.0.0
     */
    public function getTemplateRecord(string $templateKey): ?array
    {
        $this->getTemplateRecords();

        return TemplateDto::getInstance()->getTemplate($templateKey);
    }

    /**
     * @since ZC v3.0.0
     */
    public function getTemplateFilesystemPath(string $templateKey): ?string
    {
        $record = $this->getTemplateRecord($templateKey);
        if ($record === null) {
            return null;
        }
        return $record['template_path'];
    }

    /**
     * @since ZC v3.0.0
     */
    public function getTemplateCatalogPath(string $templateKey): ?string
    {
        $record = $this->getTemplateRecord($templateKey);
        if ($record === null) {
            return null;
        }
        return $record['template_catalog_path'];
    }

    /**
     * @since ZC v3.0.0
     */
    public function getTemplateWebPath(string $templateKey): ?string
    {
        $record = $this->getTemplateRecord($templateKey);
        if ($record === null) {
            return null;
        }
        return $record['template_web_path'];
    }

    /**
     * @since ZC v3.0.0
     */
    public function getBaseTemplate(string $templateKey): string
    {
        $record = $this->getTemplateRecord($templateKey);
        if ($record === null) {
            return 'template_default';
        }

        return $record['base_template'] ?? 'template_default';
    }

    /**
     * @since ZC v3.0.0
     */
    public function getTemplateInheritanceChain(string $templateKey): array
    {
        $chain = [];
        $seen = [];
        $currentTemplate = $templateKey;

        while (!empty($currentTemplate) && !isset($seen[$currentTemplate])) {
            $record = $this->getTemplateRecord($currentTemplate);
            if ($record === null) {
                break;
            }

            $chain[] = $currentTemplate;
            $seen[$currentTemplate] = true;

            $baseTemplate = $record['base_template'] ?? null;
            if (empty($baseTemplate) || $baseTemplate === $currentTemplate) {
                break;
            }

            $currentTemplate = $baseTemplate;
        }

        if (!in_array('template_default', $chain, true) && $this->getTemplateRecord('template_default') !== null) {
            $chain[] = 'template_default';
        }

        return $chain;
    }

    /**
     * @since ZC v3.0.0
     */
    public function isPluginTemplate(string $templateKey): bool
    {
        $record = $this->getTemplateRecord($templateKey);
        if ($record === null) {
            return false;
        }
        return !empty($record['is_plugin_template']);
    }

    /**
     * @since ZC v3.0.0
     */
    public function isActiveTemplate(string $templateKey): bool
    {
        $record = $this->getTemplateRecord($templateKey);
        if ($record === null) {
            return false;
        }
        return !empty($record['is_active']);
    }

    /**
     * @since ZC v3.0.0
     */
    public function getTemplateBasePath(string $templateKey): string
    {
        $record = $this->getTemplateRecord($templateKey);
        if ($record === null) {
            return '';
        }
        return $record['template_base_fs'];
    }

    /**
     * @since ZC v3.0.0
     */
    private function getTemplateRecords(): array
    {
        $templateDto = TemplateDto::getInstance();
        $contextKey = $this->getContextCacheKey();

        if (self::$loadedContext !== $contextKey) {
            foreach (array_keys($templateDto->getAllTemplates()) as $templateKey) {
                $templateDto->removeTemplate($templateKey);
            }

            if (!isset(self::$templateRecords[$contextKey])) {
                self::$templateRecords[$contextKey] = array_merge(
                $this->loadCoreTemplates(),
                $this->loadPluginTemplates()
            );
            }

            foreach (self::$templateRecords[$contextKey] as $templateKey => $templateProperties) {
                $templateDto->updateTemplate($templateKey, $templateProperties);
            }

            self::$loadedContext = $contextKey;
        }

        return $templateDto->getAllTemplates();
    }

    /**
     * @since ZC v3.0.0
     */
    private function loadCoreTemplates(): array
    {
        $templates = [];
        if (!is_dir($this->coreTemplatesPath)) {
            return $templates;
        }

        $dir = new \DirectoryIterator($this->coreTemplatesPath);
        foreach ($dir as $fileInfo) {
            if ($fileInfo->isDot() || !$fileInfo->isDir()) {
                continue;
            }

            $templateKey = $fileInfo->getFilename();
            $templatePath = $this->normalizeDirectory($fileInfo->getPathname());
            $templateInfo = $this->loadTemplateInfo($templatePath . '/template_info.php');
            if ($templateInfo === null) {
                continue;
            }

            $templates[$templateKey] = array_merge($templateInfo, [
                'template_base_fs' => $this->normalizeDirectory($this->catalogRoot) . '/',
                'template_key' => $templateKey,
                'template_path' => $templatePath . '/',
                'template_catalog_path' => 'includes/templates/' . $templateKey . '/',
                'template_web_path' => $this->buildCoreWebPath($templateKey),
                'template_settings_path' => $templatePath . '/template_settings.php',
                'base_template' => $this->normalizeBaseTemplate($templateInfo['base_template'] ?? null, $templateKey),
                'is_plugin_template' => false,
                'template_source' => 'core',
            ]);
        }

        return $templates;
    }

    /**
     * @since ZC v3.0.0
     */
    private function loadPluginTemplates(): array
    {
        $templates = [];
        if (!is_dir($this->pluginsRoot)) {
            return $templates;
        }

        $installedPlugins = $this->getInstalledPlugins();
        foreach ($installedPlugins as $unique_key => $plugin_info) {
            $version = $plugin_info['version'];
            $versionPath = $this->pluginsRoot . '/' . $unique_key . '/' . $version;
            $manifestFile = $versionPath . '/manifest.php';
            if (!is_file($manifestFile)) {
                continue;
            }
            $manifest = require $manifestFile;
            if (!$this->isSelectableTemplateManifest($manifest)) {
                continue;
            }

            $templateRecord = $this->buildPluginTemplateRecord($unique_key, $version, $versionPath, $manifest);
            if ($templateRecord === null) {
                continue;
            }

            $templates[$templateRecord['template_key']] = $templateRecord;
        }
        return $templates;
    }

    /**
     * @since ZC v3.0.0
     */
    private function getInstalledPlugins(): array
    {
        if ($this->installedPlugins !== null) {
            return $this->normalizeInstalledPlugins($this->installedPlugins);
        }

        if ($this->pluginManager !== null) {
            return $this->pluginManager->getInstalledPlugins();
        }

        return [];
    }

    /**
     * @since ZC v3.0.0
     */
    private function isSelectableTemplateManifest(mixed $manifest): bool
    {
        if (!is_array($manifest) || empty($manifest['template']) || !is_array($manifest['template'])) {
            return false;
        }

        return !empty($manifest['template']['key']);
    }

    /**
     * @since ZC v3.0.0
     */
    private function buildPluginTemplateRecord(string $pluginKey, string $pluginVersion, string $versionPath, array $manifest): ?array
    {
        $template = $manifest['template'];
        $templateKey = $template['key'];
        $defaultTemplatePath = $this->normalizeDirectory($versionPath . '/catalog/includes/templates/' . $templateKey);
        $templateInfoFile = !empty($template['infoFile'])
            ? $versionPath . '/' . ltrim($template['infoFile'], '/')
            : $defaultTemplatePath . '/template_info.php';
        $templateInfo = $this->loadTemplateInfo($templateInfoFile);
        if ($templateInfo === null) {
            return null;
        }

        $templatePath = $this->normalizeDirectory(dirname($templateInfoFile)) . '/';
        $templateCatalogPath = ltrim(str_replace($this->normalizeDirectory($this->catalogRoot) . '/', '', $this->normalizeDirectory($templatePath)), '/') . '/';
        $settingsFile = !empty($template['settingsFile'])
            ? $versionPath . '/' . ltrim($template['settingsFile'], '/')
            : $templatePath . 'template_settings.php';

        return array_merge($templateInfo, [
            'template_base_fs' => $this->normalizeDirectory($versionPath . '/catalog/') . '/',
            'template_key' => $templateKey,
            'template_path' => $templatePath,
            'template_catalog_path' => $templateCatalogPath,
            'template_web_path' => $this->buildPluginWebPath($templateCatalogPath),
            'template_settings_path' => $settingsFile,
            'base_template' => $this->normalizeBaseTemplate($template['baseTemplate'] ?? null, $templateKey),
            'is_plugin_template' => true,
            'template_source' => 'plugin',
            'plugin_key' => $pluginKey,
            'plugin_version' => $pluginVersion,
            'manifest' => $manifest,
            'has_template_settings' => file_exists($settingsFile),
        ]);
    }

    /**
     * @since ZC v3.0.0
     */
    private function loadTemplateInfo(string $templateInfoFile): ?array
    {
        if (!file_exists($templateInfoFile)) {
            return null;
        }

        $template_name = null;
        $template_version = null;
        $template_author = null;
        $template_description = null;
        $template_screenshot = null;
        $template_base = null;
        $base_template = null;
        $uses_single_column_layout_settings = false;
        $uses_mobile_sidebox_settings = true;

        require $templateInfoFile;

        return [
            'name' => $template_name,
            'version' => $template_version,
            'author' => $template_author,
            'description' => $template_description,
            'screenshot' => $template_screenshot,
            'base_template' => $template_base ?: ($base_template ?: null),
            'uses_single_column_layout_settings' => !empty($uses_single_column_layout_settings),
            'uses_mobile_sidebox_settings' => !isset($uses_mobile_sidebox_settings) || !empty($uses_mobile_sidebox_settings),
            'has_template_settings' => file_exists(dirname($templateInfoFile) . '/template_settings.php'),
        ];
    }

    /**
     * @since ZC v3.0.0
     */
    private function buildCoreWebPath(string $templateKey): string
    {
        $catalogWebRoot = defined('DIR_WS_CATALOG') ? DIR_WS_CATALOG : '/';
        return rtrim($catalogWebRoot, '/') . '/includes/templates/' . $templateKey . '/';
    }

    /**
     * @since ZC v3.0.0
     */
    private function buildPluginWebPath(string $templateCatalogPath): string
    {
        $catalogWebRoot = defined('DIR_WS_CATALOG') ? DIR_WS_CATALOG : '/';
        return rtrim($catalogWebRoot, '/') . '/' . trim($templateCatalogPath, '/') . '/';
    }

    /**
     * @since ZC v3.0.0
     */
    private function normalizeBaseTemplate(?string $baseTemplate, string $templateKey): ?string
    {
        if (empty($baseTemplate)) {
            return $templateKey === 'template_default' ? null : 'template_default';
        }

        return $baseTemplate === $templateKey ? null : $baseTemplate;
    }

    private function normalizeDirectory(string $path): string
    {
        return rtrim(str_replace('\\', '/', $path), '/');
    }

    /**
     * @since ZC v3.0.0
     */
    private function getContextCacheKey(): string
    {
        return implode('|', [
            $this->catalogRoot,
            $this->coreTemplatesPath,
            $this->pluginsRoot,
            md5(json_encode($this->normalizeInstalledPlugins($this->installedPlugins ?? [])) ?: '[]'),
        ]);
    }

    /**
     * @since ZC v3.0.0
     */
    private function normalizeInstalledPlugins(array $installedPlugins): array
    {
        $normalized = [];
        foreach ($installedPlugins as $key => $plugin) {
            if (!is_array($plugin)) {
                continue;
            }

            $uniqueKey = $plugin['unique_key'] ?? (is_string($key) ? $key : null);
            $version = $plugin['version'] ?? null;
            if (empty($uniqueKey) || empty($version)) {
                continue;
            }

            $plugin['unique_key'] = $uniqueKey;
            $plugin['version'] = $version;
            $normalized[$uniqueKey] = $plugin;
        }

        return $normalized;
    }
}
