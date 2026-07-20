<?php
/**
 *
 * @copyright Copyright 2003-2025 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: DrByte 2025 Sep 18 Modified in v2.2.0 $
 */
namespace Zencart\LanguageLoader;

use Zencart\FileSystem\FileSystem;

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
     * A leading UTF-8 byte-order-mark (BOM) in a language define file is output as literal text
     * the moment the file is require()'d/include()'d, since it precedes the opening '<?php' tag.
     * That silently corrupts any output already in progress (e.g. an AJAX endpoint's JSON response).
     * Logs a warning identifying the offending file so it can be re-saved without a BOM.
     *
     * @since ZC v2.3.0
     */
    protected static function warnIfFileHasBom(string $file): void
    {
        $handle = @fopen($file, 'rb');
        if ($handle === false) {
            return;
        }
        $firstBytes = fread($handle, 3);
        fclose($handle);
        if ($firstBytes === "\xEF\xBB\xBF") {
            error_log('Language file has a UTF-8 byte-order-mark (BOM), which will corrupt output such as AJAX/JSON responses. Re-save it as UTF-8 without BOM: ' . $file);
        }
    }
}
