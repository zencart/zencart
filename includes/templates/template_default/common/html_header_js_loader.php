<?php
/**
 * Common Template
 *
 * Outputs the html header's jscript files.
 *
 * @copyright Copyright 2003-2024 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: lat9 2024 Jul 23 New in v2.1.0-alpha1 $
 */
use Zencart\PageLoader\PageLoader;

if (!defined('IS_ADMIN_FLAG')) {
    die('Illegal Access');
}

$pageLoader = PageLoader::getInstance();

/**
 * load all site-wide jscript_*.js files from includes/templates/YOURTEMPLATE/jscript, alphabetically
 */
$directory_array = $template->get_template_part($template->get_template_dir('^jscript_.*\.js', DIR_WS_TEMPLATE, $current_page_base, 'jscript'), '/^jscript_/', '.js');
foreach ($directory_array as $value) {
    echo '<script src="' .  $template->get_template_dir('^' . $value, DIR_WS_TEMPLATE, $current_page_base, 'jscript') . '/' . $value . '"></script>' . "\n";
}

/**
 * load all page-specific jscript_*.js files from includes/modules/pages/PAGENAME, alphabetically
 */
$directory_array = $pageLoader->listModulePagesFiles('jscript_', '.js');
foreach ($directory_array as $value) {
    echo '<script src="' . $value . '"></script>' . "\n";
}

/**
 * load all site-wide jscript_*.php files from includes/templates/YOURTEMPLATE/jscript, alphabetically
 */
$directory_array = $template->get_template_part($template->get_template_dir('^jscript_.*\.php', DIR_WS_TEMPLATE, $current_page_base, 'jscript'), '/^jscript_/', '.php');
foreach ($directory_array as $value) {
    /**
     * include content from all site-wide jscript_*.php files from includes/templates/YOURTEMPLATE/jscript, alphabetically.
     * These .PHP files can be manipulated by PHP when they're called, and are copied in-full to the browser page
     */
    require $template->get_template_dir('^' . $value, DIR_WS_TEMPLATE, $current_page_base, 'jscript') . '/' . $value;
    echo "\n";
}

/**
 * include content from all page-specific jscript_*.php files from includes/modules/pages/PAGENAME, alphabetically.
 */
$directory_array = $pageLoader->listModulePagesFiles('jscript_', '.php');
foreach ($directory_array as $value) {
    /**
     * include content from all page-specific jscript_*.php files from includes/modules/pages/PAGENAME, alphabetically.
     * These .PHP files can be manipulated by PHP when they're called, and are copied in-full to the browser page
     */
    require $value;
    echo "\n";
}
