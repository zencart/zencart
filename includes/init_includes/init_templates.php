<?php
/**
 * initialise template system variables
 * see  {@link  https://docs.zen-cart.com/dev/code/init_system/} for more details.
 * Determines current template name for current language, from database
 * Then loads template-specific language file, followed by master/default language file
 * ie: includes/languages/classic/english.php followed by includes/languages/english.php
 *
 * @copyright Copyright 2003-2024 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: DrByte 2024 Mar 07 Modified in v2.0.0-rc1 $
 */

use Zencart\LanguageLoader\LanguageLoaderFactory;
use Zencart\ResourceLoaders\TemplateResolver;
use Zencart\Templates\TemplateSelect;

if (!defined('IS_ADMIN_FLAG')) {
    die('Illegal Access');
}

/*
 * Lookup the template for the current language
 * The 'choice' aliases help with weighting for fallback to default selection
 */
$templateResolver = new TemplateResolver();
$templateSelect = new TemplateSelect();
$template_dir = $templateSelect->getActiveTemplateDir();

/**
 * Allow admins to switch templates using &t= URL parameter
 */
if (zen_is_whitelisted_admin_ip()) {
    // check if a template override was requested and that the template is available
    if (isset($_GET['t']) && $templateResolver->getTemplateRecord($_GET['t']) !== null) {
        $_SESSION['tpl_override'] = $_GET['t'];
    }
    if (isset($_GET['t']) && $_GET['t'] === 'off') {
        unset($_SESSION['tpl_override']);
    }
    if (isset($_SESSION['tpl_override'])) {
        $template_dir = $_SESSION['tpl_override'];
    }
}

$templateRecord = $templateResolver->getTemplateRecord($template_dir) ?? $templateResolver->getTemplateRecord('template_default');
if ($templateRecord === null) {
    die('Fatal error: template_default could not be resolved.');
}
$template_dir = $templateRecord['template_key'];

/**
 * Now that we've established which template to use, initialize all its components
 */

/**
 * The actual template directory to use
 */
zen_define_default('DIR_WS_TEMPLATE', $templateRecord['template_catalog_path']);

/**
 * The actual template images directory to use
 */
zen_define_default('DIR_WS_TEMPLATE_IMAGES', DIR_WS_TEMPLATE . 'images/');

/**
 * The actual template icons directory to use
 */
zen_define_default('DIR_WS_TEMPLATE_ICONS', DIR_WS_TEMPLATE_IMAGES . 'icons/');

if (empty($tpl_settings) || !is_array($tpl_settings)) {
    $tpl_settings = [];
}

/**
 * Instantiate TemplateSettings object, before loading template's template_settings.php file.
 */
$tplSetting = new TemplateSettings($tpl_settings);

/**
 * Load template-specific configuration settings, if they exist.
 */
if (file_exists($templateRecord['template_settings_path'])) {
    require_once $templateRecord['template_settings_path'];
}

// check again in case overrides went wrong
if (empty($tpl_settings) || !is_array($tpl_settings)) {
    $tpl_settings = [];
}

/**
 * Load any template override settings from db
 */
if (!empty($templateSelect->getActiveTemplateSettings())) {
    $tmp = json_decode($templateSelect->getActiveTemplateSettings(), true);
    if (is_array($tmp)) {
        $tpl_settings = array_merge($tmp, $tpl_settings);
    }
}
$tpl_settings['template_dir'] = $template_dir;

/**
 * Load the appropriate Language files, based on the currently-selected template
 */
$languageLoaderFactory = new LanguageLoaderFactory();
$languageLoader = $languageLoaderFactory->make('catalog', $installedPlugins, $current_page, $template_dir);
$languageLoader->loadInitialLanguageDefines();
$languageLoader->finalizeLanguageDefines();

/**
 * Process any overrides from the $tpl_settings array, inserting them into the $tplSetting class object
 */
$tplSetting->setFromArray($tpl_settings);

/**
 * send the content charset "now" so that all content is impacted by it
 */
header("Content-Type: text/html; charset=" . CHARSET);
