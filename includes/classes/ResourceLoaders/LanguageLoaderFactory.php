<?php
/**
 *
 * @copyright Copyright 2003-2024 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: lat9 2024 Aug 18 Modified in v2.1.0-alpha2 $
 */
namespace Zencart\LanguageLoader;

use Zencart\LanguageLoader\LanguageLoader;

/**
 * @since ZC v1.5.8
 */
class LanguageLoaderFactory
{
    /**
     * @since ZC v1.5.8
     */
    public function make(string $context, array $installedPlugins, string $currentPage, string $templateDirectory, string $fallback = 'english'): \Zencart\LanguageLoader\LanguageLoader
    {
        $arraysLoader = $this->makeArraysLoader($context, $installedPlugins, $currentPage, $templateDirectory, $fallback);
        $filesLoader = $this->makeFilesLoader($context, $installedPlugins, $currentPage, $templateDirectory, $fallback);
        $mainLoader = new LanguageLoader($arraysLoader, $filesLoader);
        return $mainLoader;
    }

    /**
     * @since ZC v1.5.8
     */
    protected function makeArraysLoader(string $context, array $installedPlugins, string $currentPage, string $templateDirectory, string $fallback)
    {
        $className = 'Zencart\\LanguageLoader\\' . ucfirst(strtolower($context)) . 'ArraysLanguageLoader';
        $loader = new $className($installedPlugins, $currentPage, $templateDirectory, $fallback);
        return $loader;
    }

    /**
     * @since ZC v1.5.8
     */
    protected function makeFilesLoader(string $context, array $installedPlugins, string $currentPage, string $templateDirectory, string $fallback)
    {
        $className = 'Zencart\\LanguageLoader\\' . ucfirst(strtolower($context)) . 'FilesLanguageLoader';
        $loader = new $className($installedPlugins, $currentPage, $templateDirectory, $fallback);
        return $loader;
    }
}
