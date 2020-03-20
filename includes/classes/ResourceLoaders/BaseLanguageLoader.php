<?php
/**
 *
 * @copyright Copyright 2003-2020 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id:  $
 */

namespace Zencart\LanguageLoader;

use Zencart\FileSystem\FileSystem;

class BaseLanguageLoader
{
    protected $languageDefines = [];

    public function __construct($mainLoader, $pluginList, $currentPage, $templateDir, $fallback = 'english')
    {
        $this->mainLoader = $mainLoader;
        $this->languageDefines = [];
        $this->pluginList = []; // @todo temp
        $this->currentPage = $currentPage;
        $this->fallback = $fallback;
        $this->fileSystem = FileSystem::getInstance();
        $this->templateDir = $templateDir;
    }
}