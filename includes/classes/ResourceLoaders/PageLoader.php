<?php
/**
 *
 * @copyright Copyright 2003-2024 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: DrByte 2024 Sep 24 Modified in v2.1.0-beta1 $
 */

namespace Zencart\PageLoader;

use Zencart\Traits\Singleton;

class PageLoader
{
    use Singleton;
    
    private
        $installedPlugins,
        $mainPage,
        $fileSystem;
    
    public function init(array $installedPlugins, $mainPage, $fileSystem)
    {
        $this->installedPlugins = $installedPlugins;
        $this->mainPage = $mainPage;
        $this->fileSystem = $fileSystem;
    }

    public function findModulePageDirectory($context = 'catalog')
    {
        if (is_dir(DIR_WS_MODULES . 'pages/' . $this->mainPage)) {
            return DIR_WS_MODULES . 'pages/' . $this->mainPage;
        }
        foreach ($this->installedPlugins as $plugin) {
            $rootDir = DIR_FS_CATALOG . 'zc_plugins/' . $plugin['unique_key'] . '/' . $plugin['version'] . '/' . $context ;
            $checkDir = $rootDir . '/includes/modules/pages/' . $this->mainPage;
            if (is_dir($checkDir)) return $checkDir;
        }
        return false;
    }

    public function getTemplatePart($pageDirectory, $templatePart, $fileExtension = '.php')
    {
        $directoryArray = [];
        $directoryArray = $this->getTemplatePartFromDirectory($directoryArray, $pageDirectory, $templatePart,
                                                              $fileExtension);

        foreach ($this->installedPlugins as $plugin) {
            $checkDir = 'zc_plugins/' . $plugin['unique_key'] . '/' . $plugin['version'] . '/catalog/';
            $checkDir .= $pageDirectory;
            $directoryArray = $this->getTemplatePartFromDirectory($directoryArray, $checkDir, $templatePart,
                                                                  $fileExtension);
        }
        sort($directoryArray);
        return $directoryArray;
    }

    public function getTemplatePartFromDirectory($directoryArray, $pageDirectory, $templatePart, $fileExtension)
    {
        if ($dir = @dir($pageDirectory)) {
            while ($file = $dir->read()) {
                if (!is_dir($pageDirectory . $file)) {
                    if (substr($file, strrpos($file, '.')) == $fileExtension && preg_match($templatePart, $file)) {
                        $directoryArray[] = $file;
                    }
                }
            }
            $dir->close();
        }
        return $directoryArray;
    }

    function getTemplateDirectory($templateCode, $currentTemplate, $currentPage, $templateDir)
    {
        if ($currentTemplate === 'template_default') $currentTemplate = DIR_WS_TEMPLATES . $currentTemplate . '/';

        $path = DIR_WS_TEMPLATES . 'template_default/' . $templateDir;

        if ($this->fileSystem->fileExistsInDirectory($currentTemplate . $currentPage, $templateCode)) {
            return $currentTemplate . $currentPage . '/';
        }
        if ($this->fileSystem->fileExistsInDirectory(
            DIR_WS_TEMPLATES . 'template_default/' . $currentPage, preg_replace('/\//', '', $templateCode))) {
            return DIR_WS_TEMPLATES . 'template_default/' . $currentPage;
        }
        if ($this->fileSystem->fileExistsInDirectory(
            $currentTemplate . $templateDir, preg_replace('/\//', '', $templateCode))) {
            return $currentTemplate . $templateDir;
        }
        if ($tplPluginDir = $this->getTemplatePluginDir($templateCode, $templateDir)) {
            return $tplPluginDir;
        }
        return $path;
    }

    public function getTemplatePluginDir(string $templateCode, string $templateDir, ?string $whichPlugin = '')
    {
        foreach ($this->installedPlugins as $plugin) {
            if (!empty($whichPlugin) && $plugin['unique_key'] !== $whichPlugin) {
                continue;
            }

            $checkDir = 'zc_plugins/' . $plugin['unique_key'] . '/' . $plugin['version'] . '/catalog/includes/templates/default/' . $templateDir . '/';
            if ($this->fileSystem->fileExistsInDirectory($checkDir, preg_replace('/\//', '', $templateCode))) {
                return $checkDir;
            }
        }
        return false;
    }

    public function getBodyCode()
    {
        if (file_exists(DIR_WS_MODULES . 'pages/' . $this->mainPage . '/main_template_vars.php')) {
            $bodyCode = DIR_WS_MODULES . 'pages/' . $this->mainPage . '/main_template_vars.php';
            return $bodyCode;
        }
        $bodyCode = $this->getTemplateDirectory('tpl_' . preg_replace('/.php/', '', $this->mainPage) . '_default.php', DIR_WS_TEMPLATE, $this->mainPage, 'templates') . '/tpl_' . $this->mainPage . '_default.php';
        return $bodyCode;
    }
}
