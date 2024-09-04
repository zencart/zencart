<?php
/**
 *
 * @copyright Copyright 2003-2024 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: lat9 2024 Aug 30 Modified in v2.1.0-alpha2 $
 */
namespace Zencart\LanguageLoader;

use Zencart\FileSystem\FileSystem;

class CatalogArraysLanguageLoader extends ArraysLanguageLoader
{
    public function loadInitialLanguageDefines($mainLoader): void
    {
        $this->mainLoader = $mainLoader;
        $this->loadMainLanguageFiles();
        $this->loadLanguageExtraDefinitions();
    }

    public function loadLanguageForView(): void
    {
        $languages = [
            $_SESSION['language'],
        ];
        if ($_SESSION['language'] !== $this->fallback) {
            $languages[] = $this->fallback;
        }

        $this->loadCurrentPageBaseFile($languages);

        foreach ($languages as $next_lang) {
            $baseDir = DIR_WS_LANGUAGES . $next_lang;

            $this->loadCurrentPageExtraFilesFromDir($baseDir . '/' . $this->templateDir);

            foreach ($this->pluginList as $plugin) {
                $pluginDir = DIR_FS_CATALOG . 'zc_plugins/' . $plugin['unique_key'] . '/' . $plugin['version'] . '/catalog/includes/languages/' . $next_lang;

                $this->loadCurrentPageExtraFilesFromDir($pluginDir . '/default');
                $this->loadCurrentPageExtraFilesFromDir($pluginDir);
            }

            $this->loadCurrentPageExtraFilesFromDir($baseDir);
        }
    }

    protected function loadCurrentPageBaseFile(array $languages): void
    {
        $filename = 'lang.' . $this->currentPage . '.php';
        foreach ($languages as $next_lang) {
            $baseDir = DIR_WS_LANGUAGES . $next_lang . '/';

            $defines = $this->loadArrayDefineFile($baseDir . $this->templateDir . '/' . $filename);
            $this->makeConstants($defines);

            foreach ($this->pluginList as $plugin) {
                $pluginDir = DIR_FS_CATALOG . 'zc_plugins/' . $plugin['unique_key'] . '/' . $plugin['version'] . '/catalog/includes/languages/' . $next_lang;

                $defines = $this->loadArrayDefineFile($pluginDir . '/default/' . $filename);
                $this->makeConstants($defines);

                $defines = $this->loadArrayDefineFile($pluginDir . '/' . $filename);
                $this->makeConstants($defines);
            }

            $defines = $this->loadArrayDefineFile($baseDir . $filename);
            $this->makeConstants($defines);
        }
    }

    protected function loadCurrentPageExtraFilesFromDir(string $directory): void
    {
        $files_regex = '~^' . 'lang.' . $this->currentPage  . '(.+)\.php$~i';

        $files = $this->fileSystem->listFilesFromDirectoryAlphaSorted($directory, $files_regex);
        foreach ($files as $file) {
            $defines = $this->loadArrayDefineFile($directory . '/' . $file);
            $this->makeConstants($defines);
        }
    }

    protected function loadLanguageExtraDefinitions(): void
    {
        // -----
        // First, load the fallback (i.e. 'english') extra language definitions. If the current
        // session language is different than 'english', load that language's files; they'll
        // overwrite any like-named definitions in the 'english' fallback.
        //
        // Any definitions found here will overwrite any definitions in the 'main' language files.
        //
        $defineList = $this->loadArraysFromDirectory(DIR_WS_LANGUAGES, $this->fallback, '/extra_definitions');

        if ($_SESSION['language'] !== $this->fallback) {
            $defineListLang = $this->loadArraysFromDirectory(DIR_WS_LANGUAGES, $_SESSION['language'], '/extra_definitions');
            $defineList = array_merge($defineList, $defineListLang);
        }

        // -----
        // Next, load the fallback (i.e. 'english') extra language definitions from any enabled zc_plugins. If the current
        // session language is different than 'english', load that language's files too; they'll
        // overwrite any like-named definitions in the 'english' fallback.
        //
        // Any definitions found here will overwrite any non-plugin extra definitions as well as any definitions
        // in the 'main' language files.
        //
        $defineListPlugin = $this->pluginLoadArraysFromDirectory($this->fallback, '/extra_definitions', 'catalog');
        if ($_SESSION['language'] !== $this->fallback) {
            $defineListLang = $this->pluginLoadArraysFromDirectory($_SESSION['language'], '/extra_definitions', 'catalog');
            $defineListPlugin = array_merge($defineListPlugin, $defineListLang);
        }
        $defineList = array_merge($defineList, $defineListPlugin);

        // -----
        // Next, load the fallback (i.e. 'english') extra language definitions from any enabled zc_plugins' 'default' directory.
        // If the current session language is different than 'english', load that language's files too; they'll
        // overwrite any like-named definitions in the 'english' fallback.
        //
        // Any definitions found here will overwrite any non-'default' plugins' extra definitions, non-plugin extra definitions
        // as well as any definitions in the 'main' language files.
        //
        $defineListPlugin = $this->pluginLoadArraysFromDirectory($this->fallback, '/extra_definitions/default', 'catalog');
        if ($_SESSION['language'] !== $this->fallback) {
            $defineListLang = $this->pluginLoadArraysFromDirectory($_SESSION['language'], '/extra_definitions/default', 'catalog');
            $defineListPlugin = array_merge($defineListPlugin, $defineListLang);
        }
        $defineList = array_merge($defineList, $defineListPlugin);

        // -----
        // Finally, load any extra definitions in the current template's override directory, **for the current session language*.
        //
        // Any definitions found here overwrite **all** previous-found definitions.
        //
        $defineListTemplate = $this->loadArraysFromDirectory(DIR_WS_LANGUAGES, $_SESSION['language'], '/extra_definitions/' . $this->templateDir);

        // -----
        // Add these extra definitions to the array of definitions to be created, if not further overridden
        // by any 'legacy' language files to be loaded.
        //
        $this->addLanguageDefines(array_merge($defineList, $defineListTemplate));
    }

    protected function loadMainLanguageFiles(): void
    {
        // -----
        // First, load the main language file(s). The 'lang.english.php' file is always
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
        // Next, if there is a template-override file **in the current session's language**,
        // load those definitions, adding to the to-be-generated constants' list.
        //
        // Any definitions found in this file overwrite the 'base' main language files.
        //
        $template_dir = '/' . $this->templateDir;
        $templateMainFile = DIR_WS_LANGUAGES . $_SESSION['language'] . $template_dir . '/' . $mainFile;
        $defineList = $this->loadArrayDefineFile($templateMainFile);
        $this->addLanguageDefines($defineList);

        // -----
        // Finally, load the various 'other' language files that have definitions used
        // on multiple pages.
        //
        // Each of these files is first loaded from the 'fallback' (i.e. 'english') subdirectory,
        // followed by the current session language directory and finally (if present) in the current
        // language's template-override directory.
        //
        $extraFiles = [
            FILENAME_EMAIL_EXTRAS,
            FILENAME_HEADER,
            FILENAME_BUTTON_NAMES,
            FILENAME_ICON_NAMES,
            FILENAME_OTHER_IMAGES_NAMES,
            FILENAME_CREDIT_CARDS,
            FILENAME_WHOS_ONLINE,
            FILENAME_META_TAGS,
        ];
        foreach ($extraFiles as $file) {
            $file = basename($file, '.php') . '.php';
            $this->loadDefinesFromDirFileWithFallback(DIR_WS_LANGUAGES, $file);

            $defineList = $this->loadArrayDefineFile(DIR_WS_LANGUAGES . $_SESSION['language'] . $template_dir . '/lang.' . $file);
            $this->addLanguageDefines($defineList);
        }
    }
}
