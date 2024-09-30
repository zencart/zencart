<?php
/**
 *
 * @copyright Copyright 2003-2024 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: lat9 2024 Sep 04 Modified in v2.1.0-beta1 $
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
        // -----
        // First, load all the array files for the current page, creating the
        // constants for the 'base' current-page's file.
        //
        $this->loadCurrentPageBaseFile();

        // -----
        // Next, build up the constant-definition array for additional 'base' per-page
        // language files, i.e. files that are 'similar' to the current page's name
        // but not specifically the 'base' current-page file.
        //
        // Start with any such files in the 'english' language directory.  If the current
        // session language is different than 'english', load those files, overwriting any
        // similarly-named definitions present in 'english'.
        //
        $definesList = $this->loadCurrentPageExtraFilesFromDir(DIR_WS_LANGUAGES . $this->fallback);
        if ($_SESSION['language'] !== $this->fallback) {
            $definesList = array_merge($definesList, $this->loadCurrentPageExtraFilesFromDir(DIR_WS_LANGUAGES . $_SESSION['language']));
        }

        // -----
        // Bring in any additional per-page files from enabled zc_plugins.
        //
        // Any definitions found in these directories overwrite any of the 'base' per-page
        // definitions.
        //
        foreach ($this->pluginList as $plugin) {
            $pluginDir = $this->zcPluginsDir . $plugin['unique_key'] . '/' . $plugin['version'] . '/catalog/includes/languages/';

            $definesListPlugin = $this->loadCurrentPageExtraFilesFromDir($pluginDir . $this->fallback);
            if ($_SESSION['language'] !== $this->fallback) {
                $definesListPlugin = array_merge($definesListPlugin, $this->loadCurrentPageExtraFilesFromDir($pluginDir . $_SESSION['language']));
            }

            $definesList = array_merge($definesList, $definesListPlugin);

            $definesListPlugin = $this->loadCurrentPageExtraFilesFromDir($pluginDir . $this->fallback . '/default');
            if ($_SESSION['language'] !== $this->fallback) {
                $definesListPlugin = array_merge($definesListPlugin, $this->loadCurrentPageExtraFilesFromDir($pluginDir . $_SESSION['language'] . '/default'));
            }

            $definesList = array_merge($definesList, $definesListPlugin);
        }

        // -----
        // Finally, if there are additional per-page files in the current language's active template's
        // directory, those overwrite any definitions previously loaded.
        //
        $definesListTemplate = $this->loadCurrentPageExtraFilesFromDir(DIR_WS_LANGUAGES . $_SESSION['language'] . '/' . $this->templateDir);
        $definesList = array_merge($definesList, $definesListTemplate);

        // -----
        // Create language constants from the definitions loaded here.
        //
        $this->makeConstants($definesList);
    }

    protected function loadCurrentPageBaseFile(): void
    {
        // -----
        // First, load the main language file(s) for the current page . The 'english/lang.{page-name}.php'
        // file is always loaded, with its constant values possibly overwritten by a different page-specific
        // language file (e.g. 'spanish/lang.{page-name}.php').
        //
        // These definitions are added to the to-be-generated constants' list.
        //
        $currentPageBaseFile = '/lang.' . $this->currentPage . '.php';

        $mainFile = DIR_WS_LANGUAGES . $_SESSION['language'] . $currentPageBaseFile;
        $fallbackFile = DIR_WS_LANGUAGES . $this->fallback . $currentPageBaseFile;
        $defineList = $this->loadDefinesWithFallback($mainFile, $fallbackFile);

        // -----
        // Next, check each enabled zc_plugin to see if any page-specific language file
        // is present.
        //
        foreach ($this->pluginList as $plugin) {
            $pluginDir = $this->zcPluginsDir . $plugin['unique_key'] . '/' . $plugin['version'] . '/catalog/includes/languages/';

            $mainFile = $pluginDir . $_SESSION['language'] . $currentPageBaseFile;
            $fallbackFile = $pluginDir . $this->fallback . $currentPageBaseFile;
            $defineList = array_merge($defineList, $this->loadDefinesWithFallback($mainFile, $fallbackFile));

            $mainFile = $pluginDir . $_SESSION['language'] . '/default' . $currentPageBaseFile;
            $fallbackFile = $pluginDir . $this->fallback . '/default' . $currentPageBaseFile;
            $defineList = array_merge($defineList, $this->loadDefinesWithFallback($mainFile, $fallbackFile));
        }

        // -----
        // Finally, if there is a template-override file **in the current session's language**,
        // load those definitions, adding to the to-be-generated constants' list.
        //
        // Any definitions found in this file overwrite all previously-loaded definitions for
        // the page-specific base language file.
        //
        $template_dir = '/' . $this->templateDir;
        $templateMainFile = DIR_WS_LANGUAGES . $_SESSION['language'] . $template_dir . $currentPageBaseFile;
        $defineList = array_merge($defineList, $this->loadArrayDefineFile($templateMainFile));

        // -----
        // Make constants from the list of array-based language definitions for the
        // current page.
        //
        $this->makeConstants($defineList);
    }

    protected function loadCurrentPageExtraFilesFromDir(string $directory): array
    {
        // -----
        // The specified directory is searched for 'lang.' files (alphabetically sorted) that
        // apply to the current page (i.e. $current_page_base) that have at least 1 character
        // difference with the 'base' file for the page.  For example, lang.account_information.php
        // but not lang.account.php for the 'account' page.
        //
        $files_regex = '~^lang.' . $this->currentPage  . '(.+)\.php$~i';

        $defines = [];
        $files = $this->fileSystem->listFilesFromDirectoryAlphaSorted($directory, $files_regex);
        foreach ($files as $file) {
            $defines = array_merge($defines, $this->loadArrayDefineFile($directory . '/' . $file));
        }

        return $defines;
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
        // Next, if there is a template-override file **for the current session's language**,
        // load those definitions, adding to the to-be-generated constants' list.
        //
        // Any definitions found in this file overwrite the 'base' main language files.
        //
        $templateMainFile = DIR_WS_LANGUAGES . $this->templateDir . '/lang.' . $_SESSION['language'] . '.php';
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
        // Note: These files are not checked for presence in zc_plugins!
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

            $defineList = $this->loadArrayDefineFile(DIR_WS_LANGUAGES . $_SESSION['language'] . '/' . $this->templateDir . '/lang.' . $file);
            $this->addLanguageDefines($defineList);
        }
    }
}
