<?php
/**
 * template_func Class.
 *
 * @package classes
 * @copyright Copyright 2003-2016 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: Author: DrByte  Thu Apr 2 14:27:45 2015 -0400 Modified in v1.5.5 $
 */
if (!defined('IS_ADMIN_FLAG')) {
  die('Illegal Access');
}
/**
 * template_func Class.
 * This class is used to for template-override calculations
 *
 * @package classes
 */
class template_func extends base {

  function __construct($template_dir = 'default') {
    $this->info = array();
  }

  function get_template_part($page_directory, $template_part, $file_extension = '.php') {
      $pageLoader = Zencart\PageLoader\PageLoader::getInstance();
      $directory_array = $pageLoader->getTemplatePart($page_directory, $template_part, $file_extension);
      return $directory_array;
  }

  function get_template_dir($template_code, $current_template, $current_page, $template_dir, $debug=false) {
      $pageLoader = Zencart\PageLoader\PageLoader::getInstance();

      $path = $pageLoader->getTemplateDirectory($template_code, $current_template, $current_page, $template_dir);

      return $path;
  }

  function file_exists($file_dir, $file_pattern, $debug=false) {
    $file_found = false;
    $file_pattern = '/'.str_replace("/", "\/", $file_pattern).'$/';
    if ($mydir = @dir($file_dir)) {
      while ($file = $mydir->read()) {
        if (preg_match($file_pattern, $file)) {
          $file_found = true;
          break;
        }
      }
      $mydir->close();
    }
    return $file_found;
  }
}
