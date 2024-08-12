<?php
/**
 * @copyright Copyright 2003-2024 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: DrByte 2024 Jan 11 Modified in v2.0.0-alpha1 $
 */

class LanguageManager
{
    /**
     * $languagesInstalled is an array of the languages installed
     */
    protected array $languagesInstalled = [];

    /**
     * $langPath is the directory path to languages files
     */
    public function __construct(protected string $langPath = 'includes/languages/')
    {
    }

    public function getLanguagesInstalled(): array
    {
        $infoFiles = $this->listFilesFromDirectory(DIR_FS_INSTALL . $this->langPath, '~^lng_info.*\.php$~i');
        $this->languagesInstalled = [];
        foreach ($infoFiles as $infoFile) {
            $infoData = require DIR_FS_INSTALL . $this->langPath . $infoFile;
            $this->languagesInstalled = array_merge($this->languagesInstalled, $infoData);
        }
        return $this->languagesInstalled;
    }

    protected function listFilesFromDirectory(string $rootDir, string $fileRegx): array
    {
        if (!$dir = @dir($rootDir)) {
            return [];
        }
        $fileList = [];
        while ($file = $dir->read()) {
            if (preg_match($fileRegx, $file) > 0) {
                $fileList[] = basename($rootDir . '/' . $file);
            }
        }
        $dir->close();
        return $fileList;
    }

    public function loadLanguageDefines(string $lng, string $currentPage, string $fallback = 'en_us'): void
    {
        $defineListFallback = [];
        if ($lng !== $fallback) {
            $defineListFallback = $this->loadDefineFile($fallback, 'main');
        }
        $defineListMain = $this->loadDefineFile($lng, 'main');
        $defineList = array_merge($defineListFallback, $defineListMain);
        $this->makeConstants($defineList);
    }

    public function loadDefineFile($lng, $file): mixed
    {
        $defineList = [];
        $fp = DIR_FS_INSTALL . $this->langPath . $lng . '/' . $file . '.php';
        if (file_exists($fp)) {
            $defineList = require $fp;
        }
        return $defineList;
    }

    public function makeConstants($defines): void
    {
        foreach ($defines as $defineKey => $defineValue) {
            preg_match_all('/%{2}([^%]+)%{2}/', $defineValue, $matches, PREG_PATTERN_ORDER);
            if (count($matches[1])) {
                foreach ($matches[1] as $index => $match) {
                    if (isset($defines[$match])) {
                        $defineValue = str_replace($matches[0][$index], $defines[$match], $defineValue);
                    }
                }
            }
            define($defineKey, $defineValue);
        }
    }
}
