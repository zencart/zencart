<?php
/**
 *
 * @copyright Copyright 2003-2024 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: lat9 2024 Sep 04 Modified in v2.1.0-beta1 $
 */
namespace Zencart\LanguageLoader;

use Zencart\FileSystem\FileSystem;

class BaseLanguageLoader
{
    protected string $fallback;
    protected \Zencart\FileSystem\FileSystem $fileSystem;
    protected array $languageDefines = [];
    protected array $pluginList;
    protected string $templateDir;
    protected string $zcPluginsDir;

    public string $currentPage;

    public function __construct(array $pluginList, string $currentPage, string $templateDir, string $fallback = 'english')
    {
        $this->pluginList = $pluginList;
        $this->currentPage = $currentPage;
        $this->fallback = $fallback;
        $this->fileSystem = new FileSystem();
        $this->templateDir = $templateDir;
        $this->zcPluginsDir = DIR_FS_CATALOG . 'zc_plugins/';
    }
}
