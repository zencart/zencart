<?php
/**
 * @copyright Copyright 2003-2020 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id:$
 */

class LanguageManager
{
    public function __construct($langPath = 'includes/languages/')
    {
        $this->langPath = $langPath;
    }

    public function getLanguagesInstalled()
    {
        $this->languagesInstalled = require(DIR_FS_INSTALL . $this->langPath . 'languages_installed.php');
        return $this->languagesInstalled;
    }

    public function loadLanguageDefines($lng, $currentPage, $fallback = 'en_us')
    {
        $dff = [];
        if ($lng != $fallback) {
            $dff = $this->loadDefineFile($fallback, 'main');
        }
        $dfl = $this->loadDefineFile($lng, 'main');
        $df = array_merge($dff, $dfl);
        $this->makeConstants($df);
    }

    public function loadDefineFile($lng, $file)
    {
        $df = [];
        $fp = DIR_FS_INSTALL . $this->langPath . $lng . '/' . $file . '.php';
        if (file_exists($fp)) {
            $df = require($fp);
        }
        return $df;
    }

    public function makeConstants($defines)
    {
        foreach ($defines as $defineKey => $defineValue) {
            preg_match('/([A-Z]{4,}|[_]{1,})+/', $defineValue, $matches);
            if (count($matches)) {
                foreach ($matches as $match) {
                    if (isset($defines[$match])) {
                        $defineValue = str_replace($match, $defines[$match], $defineValue);
                    }
                }
            }
            define($defineKey, $defineValue);
        }
    }
}