<?php
/**
 *
 * @copyright Copyright 2003-2022 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: Zcwilt 2020 Jun 02 New in v1.5.8-alpha $
 */
namespace Zencart\LanguageLoader;

use Zencart\LanguageLoader\LanguageLoader;

class LanguageLoaderFactory
{
    public function make(string $context, array $installedPlugins, string $currentPage, string $templateDirectory, string $fallback = 'english'): \Zencart\LanguageLoader\LanguageLoader
    {
        $arraysLoader = $this->makeArraysLoader($context, $installedPlugins, $currentPage, $templateDirectory, $fallback);
        $filesLoader = $this->makeFilesLoader($context, $installedPlugins, $currentPage, $templateDirectory, $fallback);
        $mainLoader = new LanguageLoader($arraysLoader, $filesLoader);
        return $mainLoader;
    }

    protected function makeArraysLoader(string $context, array $installedPlugins, string $currentPage, string $templateDirectory, string $fallback)
    {
        $className = 'Zencart\\LanguageLoader\\' . ucfirst(strtolower($context)) . 'ArraysLanguageLoader';
        $loader = new $className($installedPlugins, $currentPage, $templateDirectory, $fallback);
        return $loader;
    }

    protected function makeFilesLoader(string $context, array $installedPlugins, string $currentPage, string $templateDirectory, string $fallback)
    {
        $className = 'Zencart\\LanguageLoader\\' . ucfirst(strtolower($context)) . 'FilesLanguageLoader';
        $loader = new $className($installedPlugins, $currentPage, $templateDirectory, $fallback);
        return $loader;
    }
}
