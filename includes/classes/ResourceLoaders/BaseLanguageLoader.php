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

    public function __construct($pluginList, $currentPage, $templateDir, $fallback = 'english')
    {
        $this->pluginList = $pluginList;
        $this->languageDefines = [];
        $this->currentPage = $currentPage;
        $this->fallback = $fallback;
        $this->fileSystem = new FileSystem;
        $this->templateDir = $templateDir;
    }
}
