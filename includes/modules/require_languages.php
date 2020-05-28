<?php
/**
 * loads template specific language override files
 *
 * @copyright Copyright 2003-2020 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: Scott C Wilson 2019 Jul 23 Modified in v1.5.7 $
 */
if (!defined('IS_ADMIN_FLAG')) {
  die('Illegal Access');
}
// determine language or template language file
if (file_exists($language_page_directory . $template_dir . '/' . $current_page_base . '.php')) {
  $template_dir_select = $template_dir . '/';
} else {
  $template_dir_select = '';
}

// set language or template language file
$directory_array = $template->get_template_part($language_page_directory . $template_dir_select, '/^'.$current_page_base . '/');
foreach($directory_array as $key => $value) {
  require_once($language_page_directory . $template_dir_select . $value);
}

// load master language file(s) if lang files loaded previously were "overrides" and not masters.
if ($template_dir_select != '') {
  $directory_array = $template->get_template_part($language_page_directory, '/^'.$current_page_base . '/');
  foreach($directory_array as $key => $value) {
    require_once($language_page_directory . $value);
  }
}
