<?php
/**
 *
 * @copyright Copyright 2003-2025 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: DrByte 2025 Sep 18 Modified in v2.2.0 $
 */
namespace Zencart\LanguageLoader;

use Zencart\FileSystem\FileSystem;
use Zencart\ResourceLoaders\TemplateResolver;

/**
 * @since ZC v1.5.8
 */
class BaseLanguageLoader
{
    protected string $fallback;
    protected \Zencart\FileSystem\FileSystem $fileSystem;
    protected array $languageDefines = [];
    protected array $pluginList;
    protected string $templateDir;
    protected string $zcPluginsDir;
    protected TemplateResolver $templateResolver;

    public string $currentPage;

    public function __construct(array $pluginList, string $currentPage, string $templateDir, string $fallback = 'english')
    {
        $this->pluginList = $pluginList;
        $this->currentPage = $currentPage;
        $this->fallback = $fallback;
        $this->fileSystem = new FileSystem();
        $this->templateDir = $templateDir;
        $this->zcPluginsDir = DIR_FS_CATALOG . 'zc_plugins/';
        $this->templateResolver = new TemplateResolver();
    }

    /**
     * @since ZC v2.2.0
     */
    public function getTemplateDir(): string
    {
        return $this->templateDir;
    }

    /**
     * @since ZC v2.2.0
     */
    public function getFallback(): string
    {
        return $this->fallback;
    }

    /**
     * @since ZC v2.2.1
     */
    protected function getTemplateInheritanceChainForLookup(bool $reverse = false): array
    {
        $chain = $this->templateResolver->getTemplateInheritanceChain($this->templateDir);
        if ($chain === []) {
            $chain = [$this->templateDir];
        }

        if ($reverse) {
            $chain = array_reverse($chain);
        }

        return array_values(array_unique($chain));
    }

    /**
     * @since ZC v2.2.1
     */
    protected function findTemplateLanguageOverrideFile(
        string $rootPath,
        string $language,
        string $fileName,
        string $extraPath = ''
    ): ?string {
        $rootPath = rtrim($rootPath, '/') . '/';
        $extraPath = trim($extraPath, '/');
        foreach ($this->getTemplateInheritanceChainForLookup() as $templateKey) {
            foreach ($this->getTemplateLanguageOverrideCandidates($rootPath, $language, $templateKey, $fileName, $extraPath) as $path) {
                if (is_file($path)) {
                    return $path;
                }
            }
        }

        return null;
    }

    /**
     * @since ZC v2.2.1
     */
    protected function findTemplateFirstLanguageFile(string $rootPath, string $fileName): ?string
    {
        $rootPath = rtrim($rootPath, '/') . '/';
        foreach ($this->getTemplateInheritanceChainForLookup() as $templateKey) {
            $path = $rootPath . $templateKey . '/' . $fileName;
            if (is_file($path)) {
                return $path;
            }
        }

        return null;
    }

    /**
     * @since ZC v2.2.1
     */
    protected function getTemplateLanguageOverrideFiles(
        string $rootPath,
        string $language,
        string $fileName,
        string $extraPath = ''
    ): array {
        $rootPath = rtrim($rootPath, '/') . '/';
        $extraPath = trim($extraPath, '/');
        $files = [];

        foreach ($this->getTemplateInheritanceChainForLookup(true) as $templateKey) {
            foreach ($this->getTemplateLanguageOverrideCandidates($rootPath, $language, $templateKey, $fileName, $extraPath) as $path) {
                if (is_file($path)) {
                    $files[] = $path;
                }
            }
        }

        return $files;
    }

    /**
     * @since ZC v2.2.1
     */
    protected function getTemplateFirstLanguageFiles(string $languageDir, string $fileName): array
    {
        $languageDir = rtrim($languageDir, '/') . '/';
        $files = [];
        foreach ($this->getTemplateInheritanceChainForLookup(true) as $templateKey) {
            $path = $this->templateResolver->getTemplateBasePath($templateKey) . $languageDir;
            if ($templateKey !== 'template_default') {
                $path .= $templateKey . '/';
            }
            $path .= $fileName;
            if (is_file($path)) {
                $files[] = $path;
            }
        }

        return $files;
    }

    /**
     * @since ZC v2.2.1
     */
    protected function getTemplateLanguageOverrideCandidates(
        string $rootPath,
        string $language,
        string $templateKey,
        string $fileName,
        string $extraPath = ''
    ): array {
        $paths = [];
        $rootPath = rtrim($rootPath, '/') . '/';
        $extraPath = trim($extraPath, '/');

        $paths[] = $this->buildTemplateLanguageOverridePath($rootPath, $language, $templateKey, $fileName, $extraPath);

        foreach ($this->getPluginTemplateLanguageRoots($templateKey) as $pluginLanguageRoot) {
            $paths[] = $this->buildTemplateLanguageOverridePath($pluginLanguageRoot, $language, $templateKey, $fileName, $extraPath);
        }

        return array_values(array_unique($paths));
    }

    /**
     * @since ZC v3.0.0
     */
    protected function getTemplateLanguageOverrideDirectories(
        string $rootPath,
        string $language,
        string $templateKey,
        string $extraPath = ''
    ): array {
        $directories = [];
        $rootPath = rtrim($rootPath, '/') . '/';
        $extraPath = trim($extraPath, '/');

        $directories[] = $this->buildTemplateLanguageOverrideDirectory($rootPath, $language, $templateKey, $extraPath);

        foreach ($this->getPluginTemplateLanguageRoots($templateKey) as $pluginLanguageRoot) {
            $directories[] = $this->buildTemplateLanguageOverrideDirectory($pluginLanguageRoot, $language, $templateKey, $extraPath);
        }

        return array_values(array_unique($directories));
    }

    /**
     * @since ZC v2.2.1
     */
    protected function getPluginTemplateLanguageRoots(string $templateKey): array
    {
        $roots = [];
        $templateRecord = $this->templateResolver->getTemplateRecord($templateKey);

        // -----
        // If the specified template is a plugin, include its language root so
        // template inheritance can traverse parent/child plugin templates.
        //
        if ($this->templateResolver->isPluginTemplate($templateKey) && $templateRecord !== null) {
            $roots[] = $this->zcPluginsDir . $templateRecord['plugin_key'] . '/' . $templateRecord['plugin_version'] . '/catalog/includes/languages/';
        }

        foreach ($this->pluginList as $plugin) {
            if (
                empty($plugin['unique_key'])
                || empty($plugin['version'])
                || (($plugin['type'] ?? null) === 'template')
            ) {
                continue;
            }
            $roots[] = $this->zcPluginsDir . $plugin['unique_key'] . '/' . $plugin['version'] . '/catalog/includes/languages/';
        }

        return array_values(array_unique($roots));
    }

    /**
     * @since ZC v2.2.1
     */
    protected function buildTemplateLanguageOverridePath(
        string $rootPath,
        string $language,
        string $templateKey,
        string $fileName,
        string $extraPath = ''
    ): string {
        $path = $this->buildTemplateLanguageOverrideDirectory($rootPath, $language, $templateKey, $extraPath);
        return $path . '/' . $fileName;
    }

    /**
     * @since ZC v2.2.1
     */
    protected function buildTemplateLanguageOverrideDirectory(
        string $rootPath,
        string $language,
        string $templateKey,
        string $extraPath = ''
    ): string {
        $path = rtrim($rootPath, '/') . '/' . $language . '/';
        if ($extraPath !== '') {
            $path .= trim($extraPath, '/') . '/';
        }
        return rtrim($path . $templateKey, '/');
    }
}
