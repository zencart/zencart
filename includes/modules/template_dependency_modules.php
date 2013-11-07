<?php
/**
 * Load common dependencies required by templates
 *
 * @package initSystem
 * @copyright Copyright 2003-2013 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version GIT: $Id: Author: DrByte  Fri Jul 6 11:57:44 2012 -0400 Modified in v1.5.1 $
 */
// must be called appropriately
if (!defined('IS_ADMIN_FLAG')) {
  die('Illegal Access');
}
$zco_notifier->notify('NOTIFY_MODULE_TEMPLATE_DEPENDENCY_MODULES', $current_page_base);

require(DIR_WS_MODULES . zen_get_module_directory('meta_tags.php'));

require(DIR_WS_TEMPLATE . 'template_info.php');

// dynamically discover and prepare proper markup for relevant stylesheets and javascripts
if (NULL == $css_js_handler || $css_js_handler == '') $css_js_handler = 'tpl_css_js_generator.php';
require(DIR_WS_MODULES . zen_get_module_directory($css_js_handler));

