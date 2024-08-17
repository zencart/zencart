<?php
/**
 *
 * @copyright Copyright 2003-2022 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: brittainmark 2022 Aug 23 Modified in v1.5.8-alpha2 $
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

    public string $currentPage;

    public function __construct(array $pluginList, string $currentPage, string $templateDir, string $fallback = 'english')
    {
        $this->pluginList = $pluginList;
        $this->currentPage = $currentPage;
        $this->fallback = $fallback;
        $this->fileSystem = new FileSystem();
        $this->templateDir = $templateDir;
    }
}
