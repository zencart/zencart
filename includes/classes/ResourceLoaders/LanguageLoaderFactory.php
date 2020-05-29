<?php
/**
 *
 * @copyright Copyright 2003-2020 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id:  $
 */


namespace Zencart\LanguageLoader;


class LanguageLoaderFactory
{

    public function make($context, $installedPlugins, $currentPage, $templateDirectory, $fallback = 'english')
    {
        $arraysLoader = $this->makeArraysLoader($context, $installedPlugins, $currentPage, $templateDirectory, $fallback);
        $filesLoader = $this->makeFilesLoader($context, $installedPlugins, $currentPage, $templateDirectory, $fallback);
        $mainLoader = $this->makeMainLoader($arraysLoader, $filesLoader, $context);

        return $mainLoader;
    }

    protected function makeArraysLoader($context, $installedPlugins, $currentPage, $templateDirectory, $fallback)
    {
        $className = 'Zencart\\LanguageLoader\\' . ucfirst(strtolower($context)) . 'ArraysLanguageLoader';
        $loader = new $className($installedPlugins, $currentPage, $templateDirectory, $fallback);
        return $loader;
    }

    protected function makeFilesLoader($context, $installedPlugins, $currentPage, $templateDirectory, $fallback)
    {
        $className = 'Zencart\\LanguageLoader\\' . ucfirst(strtolower($context)) . 'FilesLanguageLoader';
        $loader = new $className($installedPlugins, $currentPage, $templateDirectory, $fallback);
        return $loader;
    }

    protected function makeMainLoader($arraysLoader, $filesLoader, $context)
    {
        $className = 'Zencart\\LanguageLoader\\' . ucfirst(strtolower($context)) . 'LanguageLoader';
        $loader = new $className($arraysLoader, $filesLoader);
        return $loader;
    }
}