<?php
/**
 * Load common dependencies required by templates
 *
 * @package initSystem
 * @copyright Copyright 2003-2014 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version GIT: $Id: Author: DrByte  Jul 9 2014 New in v1.6.0 $
 */
// must be called appropriately
if (!defined('IS_ADMIN_FLAG')) {
  die('Illegal Access');
}
$zco_notifier->notify('NOTIFY_MODULE_TEMPLATE_DEPENDENCY_MODULES', $current_page_base);

require(DIR_WS_MODULES . zen_get_module_directory('meta_tags.php'));

include(DIR_WS_TEMPLATE . 'template_info.php');

// dynamically discover and prepare proper markup for relevant stylesheets and javascripts
if (NULL == $css_js_handler || $css_js_handler == '') $css_js_handler = 'tpl_css_js_generator.php';
$val = DIR_WS_MODULES . zen_get_module_directory($css_js_handler);
if (file_exists($val)) {
  require($val);
} else {
  $css_js_handler = 'tpl_css_js_generator';
  require(DIR_WS_MODULES . zen_get_module_directory($css_js_handler));
}
