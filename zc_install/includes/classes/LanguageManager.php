<?php
/**
 * @copyright Copyright 2003-2020 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: Steve 2020 May 01 New in v1.5.7 $
 */

class LanguageManager
{
    public function __construct($langPath = 'includes/languages/')
    {
        $this->langPath = $langPath;
    }

    public function getLanguagesInstalled()
    {
        $infoFiles = $this->listFilesFromDirectory(DIR_FS_INSTALL . $this->langPath, '~^lng_info.*\.php$~i');
        $this->languagesInstalled = [];
        foreach ($infoFiles as $infoFile) {
            $infoData = require(DIR_FS_INSTALL . $this->langPath . $infoFile);
            $this->languagesInstalled = array_merge($this->languagesInstalled, $infoData);
        }
	return $this->languagesInstalled;
    }

    public function loadLanguageDefines($lng, $currentPage, $fallback = 'en_us')
    {
        $defineListFallback = [];
        if ($lng != $fallback) {
            $defineListFallback = $this->loadDefineFile($fallback, 'main');
        }
        $defineListMain = $this->loadDefineFile($lng, 'main');
        $defineList = array_merge($defineListFallback, $defineListMain);
        $this->makeConstants($defineList);
    }

    public function loadDefineFile($lng, $file)
    {
        $defineList = [];
        $fp = DIR_FS_INSTALL . $this->langPath . $lng . '/' . $file . '.php';
        if (file_exists($fp)) {
            $defineList = require($fp);
        }
        return $defineList;
    }

    public function makeConstants($defines)
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
    protected function listFilesFromDirectory($rootDir, $fileRegx)
    {
        if (!$dir = @dir($rootDir)) return [];
        $fileList = [];
        while ($file = $dir->read()) {
            if (preg_match($fileRegx, $file) > 0) {
                $fileList[] = basename($rootDir . '/' . $file);
            }
        }
        $dir->close();
        return $fileList;
    }
}