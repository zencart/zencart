<?php
/**
 * index.php represents the hub of the Zen Cart MVC system
 *
 * Overview of flow
 * <ul>
 * <li>Load application_top.php - see {@tutorial initsystem}</li>
 * <li>Set main language directory based on $_SESSION['language']</li>
 * <li>Load all *header_php.php files from includes/modules/pages/PAGE_NAME/</li>
 * <li>Load html_header.php (this is a common template file)</li>
 * <li>Load main_template_vars.php (this is a common template file)</li>
 * <li>Load tpl_main_page.php (this is a common template file)</li>
 * <li>Load application_bottom.php</li>
 * </ul>
 *
 * @package general
 * @copyright Copyright 2003-2014 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: index.php 2942 2006-02-02 04:41:23Z drbyte $
 */
/**
 * Load common library stuff
 */
  require('includes/application_top.php');

  $language_page_directory = DIR_WS_LANGUAGES . $_SESSION['language'] . '/';
  $directory_array = $template->get_template_part($code_page_directory, '/^header_php/');
  foreach ($directory_array as $value) {
/**
 * We now load header code for a given page. This is the handler for page-specific logic processing.
 * Page code is stored in includes/modules/pages/PAGE_NAME/directory
 * 'header_php****.php' files in that directory are loaded now.
 */
    require($code_page_directory . '/' . $value);
  }
/**
 * Load any modules which depend on header_php results before beginning template output:
 */
  require(DIR_WS_MODULES . zen_get_module_directory('template_dependency_modules.php'));
/**
 * We now load the html_header.php file. This file contains code that would appear within the HTML <head></head> code
 * It is overridable on a template and page basis in that a custom template can define its own common/html_header.php file, but this is rare in practice.
 */
  require($template->get_template_dir('html_header.php',DIR_WS_TEMPLATE, $current_page_base,'common'). '/html_header.php');
/**
 * Define Template Variables picked up from includes/main_template_vars.php unless a file exists in the
 * includes/pages/{page_name}/directory to overide, allowing different pages to have different overall
 * templates.
 */
  require($template->get_template_dir('main_template_vars.php',DIR_WS_TEMPLATE, $current_page_base,'common'). '/main_template_vars.php');

/**
 * Define the template that will govern the overall page layout, can be done on a page by page basis
 * or using a default template. The template also loads the page body code based on the variable $body_code.
 */
  require($template->get_template_dir('tpl_main_page.php',DIR_WS_TEMPLATE, $current_page_base,'common'). '/tpl_main_page.php');
?>
<?php
/**
 * Load general code to run before page closes, such as shutting down sessions etc
 */
?>
<?php require(DIR_WS_INCLUDES . 'application_bottom.php'); ?>
