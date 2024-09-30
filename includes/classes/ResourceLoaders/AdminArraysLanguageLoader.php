<?php
/**
 *
 * @copyright Copyright 2003-2024 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: lat9 2024 Sep 04 Modified in v2.1.0-beta1 $
 */
namespace Zencart\LanguageLoader;

class AdminArraysLanguageLoader extends ArraysLanguageLoader
{
    public function loadInitialLanguageDefines($mainLoader): void
    {
        $this->mainLoader = $mainLoader;
        $this->loadBaseLanguageFiles();
        $this->loadLanguageForView();
        $this->loadLanguageExtraDefinitions();
    }

    protected function loadLanguageForView(): void
    {
        $this->loadDefinesFromDirFileWithFallback(DIR_WS_LANGUAGES, $this->currentPage);

        $defineList = $this->pluginLoadDefinesFromArrayFile($this->fallback, $this->currentPage, 'admin', '');
        $this->addLanguageDefines($defineList);

        if ($_SESSION['language'] !== $this->fallback) {
            $defineList = $this->pluginLoadDefinesFromArrayFile($_SESSION['language'], $this->currentPage, 'admin', '');
            $this->addLanguageDefines($defineList);
        }
    }

    protected function loadLanguageExtraDefinitions(): void
    {
        $defineList = $this->loadArraysFromDirectory(DIR_WS_LANGUAGES, $this->fallback, '/extra_definitions');
        $this->addLanguageDefines($defineList);

        if ($_SESSION['language'] !== $this->fallback) {
            $defineList = $this->loadArraysFromDirectory(DIR_WS_LANGUAGES, $_SESSION['language'], '/extra_definitions');
            $this->addLanguageDefines($defineList);
        }

        $defineList = $this->pluginLoadArraysFromDirectory($this->fallback, '/extra_definitions');
        $this->addLanguageDefines($defineList);

        if ($_SESSION['language'] !== $this->fallback) {
            $defineList = $this->pluginLoadArraysFromDirectory($_SESSION['language'], '/extra_definitions');
            $this->addLanguageDefines($defineList);
        }
    }

    protected function loadBaseLanguageFiles()
    {
        // -----
        // First, load the main language file(). The 'lang.english.php' file is always
        // loaded, with its constant values possibly overwritten by a different main
        // language file (e.g. lang.spanish.php).
        //
        // These definitions are added to the to-be-generated constants' list.
        //
        $mainFile = DIR_WS_LANGUAGES . 'lang.' . $_SESSION['language'] . '.php';
        $fallbackFile = DIR_WS_LANGUAGES . 'lang.' . $this->fallback . '.php';
        $defineList = $this->loadDefinesWithFallback($mainFile, $fallbackFile);
        $this->addLanguageDefines($defineList);

        // -----
        // Next, load some other files with multi-page-use constants, adding their
        // definitions to the to-be-generated constants' list.
        //
        // Each file is first loaded from the 'english' sub-directory for the given
        // directory and then, if the session's language is non-english, overwritten
        // by any such file found in that language sub-directory (e.g. 'spanish').
        //
        $this->loadDefinesFromDirFileWithFallback(DIR_WS_LANGUAGES, 'gv_name.php');
        $this->loadDefinesFromDirFileWithFallback(DIR_WS_LANGUAGES, FILENAME_EMAIL_EXTRAS);
        $this->loadDefinesFromDirFileWithFallback(DIR_FS_CATALOG . DIR_WS_LANGUAGES, FILENAME_OTHER_IMAGES_NAMES);

        // -----
        // Finally, if the 'lang.other_images_names.php' has a template-override file **in the
        // current session's language**, load those definitions, adding to the
        // to-be-generated constants' list.
        //
        if ($this->fileSystem->hasTemplateLanguageOverride($this->templateDir, DIR_FS_CATALOG . DIR_WS_LANGUAGES, $_SESSION['language'], FILENAME_OTHER_IMAGES_NAMES)) {
            $defineList = $this->loadDefinesFromArrayFile(DIR_FS_CATALOG . DIR_WS_LANGUAGES, $_SESSION['language'], FILENAME_OTHER_IMAGES_NAMES, '/' . $this->templateDir);
            $this->addLanguageDefines($defineList);
        }
    }
}
