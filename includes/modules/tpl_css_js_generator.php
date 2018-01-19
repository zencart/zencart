<?php
/**
 * template css_js_generator module
 *
 * @package modules
 * @copyright Copyright 2003-2013 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id$
 */
if (!defined('IS_ADMIN_FLAG')) {
  die('Illegal Access');
}
// init the arrays, if not already set before this script fired or by the above notifier hook.
foreach(array('stylesheets', 'jsfiles', 'jsfilesTop', 'jscripts', 'jscriptsTop', 'printsheets', 'inline_css', 'deduplicate') as $val) {
  if (!isset($$val)) $$val = array();
}
if (isset($template_framework)) $template_framework = '';
$bodyClasses = '';

$zco_notifier->notify('NOTIFY_MODULE_START_CSS_JS_GENERATOR', array(), $css_js_handler, $use_default_handler, $bodyClasses);


$manufacturers_id = (isset($_GET['manufacturers_id'])) ? $_GET['manufacturers_id'] : '';
$tmp_products_id = (isset($_GET['products_id'])) ? (int)$_GET['products_id'] : '';
$tmp_pagename = ($this_is_home_page) ? 'index_home' : $current_page_base;
if ($current_page_base == 'page' && isset($ezpage_id)) $tmp_pagename = $current_page_base . (int)$ezpage_id;

/** Built a list of classes to be assigned to the BODY tag for targeted styling */
$bodyClasses .= ' ' . $_SESSION['language'];
if ($tmp_pagename != '')        $bodyClasses .= ' ' . base::camelize($tmp_pagename);
if ($tmp_products_id != '')     $bodyClasses .= ' p' . $tmp_products_id;
if ($manufacturers_id != '')    $bodyClasses .= ' m' . $manufacturers_id;
if ($current_category_id != '') $bodyClasses .= ' c' . $current_category_id;
if ($cPath != '')               $bodyClasses .= ' cPath' . $cPath;
$zco_notifier->notify('NOTIFY_ADD_BODY_CLASSES', array(), $bodyClasses);




// Now build all the CSS/JS components  -- or skip if overridden by an alternate handler
if (!isset($use_default_handler) || (isset($use_default_handler) && $use_default_handler != FALSE) ) {
  /**
   * load the framework-specific stylesheet files
   *
   * To customize loading for a specific CSS framework, use an observer class watching NOTIFY_MODULE_CSS_JS_GENERATOR_FRAMEWORK_SELECT,
   * and populate $stylesheets with the correct files for your framework.
   *
   * The intention here is to allow for specifying which stylesheets need to be loaded before everything else, ie: setting a dependency order
   *
   * The $template_framework variable is expected to be set in your template's template_info.php file, and is used by observer classes to help them make decisions on whether to load these dependencies.
   */
  $zco_notifier->notify('NOTIFY_MODULE_CSS_JS_GENERATOR_FRAMEWORK_SELECT', $css_js_handler, $template_framework, $stylesheets);

  // prepare array for later deduplicating of stylesheet names
  foreach($stylesheets as $val) {
    if (preg_match('~.*/([^/]*\.css)$~', $val, $matches)) {
      $deduplicate[] = $matches[1];
    }
  }

  /**
   * load all template-specific stylesheets, named as "stylesheet.css" and "stylesheet-responsive.css" and "font.css"
   */
  $sheets_array_primary = array(
          '/' . 'stylesheet',
          '/' . 'stylesheet-responsive',
          '/' . 'font',
  );
  foreach($sheets_array_primary as $key => $value) {
    $perpagefile = $template->get_template_dir(ltrim($value, '/') . '.css', DIR_WS_TEMPLATE, $current_page_base, 'css') . $value . '.css';
    if (file_exists($perpagefile)) {
      $stylesheets[] = $perpagefile;
      $deduplicate[] = ltrim($value . '.css', '/');
    }
  }

  /**
   * load all remaining template-specific stylesheets, alphabetically
   * filter out duplicates
   */
  $directory_array = $template->get_template_part($template->get_template_dir('^[^(inline|print)].*\.css',DIR_WS_TEMPLATE, $current_page_base,'css'), '/^[^(inline|print)]/', '.css');
  sort($directory_array);
  $directory_array  = array_diff($directory_array, $deduplicate);
  foreach($directory_array as $key => $value) {
    $stylesheets[] = $template->get_template_dir('.css',DIR_WS_TEMPLATE, $current_page_base,'css') . '/' . $value;
  }

  /**
   * load 'inline' stylesheet content, named like "inline*.css", alphabetically
   */
  $directory_array = $template->get_template_part($template->get_template_dir('^inline.*\.css',DIR_WS_TEMPLATE, $current_page_base,'css'), '/^inline/', '.css');
  sort($directory_array);
  foreach($directory_array as $key => $value) {
    $inline_css[] = $template->get_template_dir('.css',DIR_WS_TEMPLATE, $current_page_base,'css') . '/' . $value;
  }
  $sheets_array = array('/inline_' . $_SESSION['language'] . '_stylesheet',
          '/inline_' . $tmp_pagename,
          '/inline_' . $_SESSION['language'] . '_' . $tmp_pagename,
          '/inline_c_' . $cPath,
          '/inline_' . $_SESSION['language'] . '_c_' . $cPath,
          '/inline_m_' . $manufacturers_id,
          '/inline_' . $_SESSION['language'] . '_m_' . (int)$manufacturers_id,
          '/inline_p_' . $tmp_products_id,
          '/inline_' . $_SESSION['language'] . '_p_' . $tmp_products_id,
  );

  $sheets_array = array_intersect($sheets_array_primary, $sheets_array);
  foreach($sheets_array as $key => $value) {
    $perpagefile = $template->get_template_dir(ltrim($value, '/') . '.css', DIR_WS_TEMPLATE, $current_page_base, 'css') . $value . '.css';
    if (file_exists($perpagefile)) $inline_css[] = $perpagefile;
  }


  /**
   * load printer-friendly stylesheets -- named like "print*.css", alphabetically
   */
  $directory_array = $template->get_template_part($template->get_template_dir('^print.*\.css',DIR_WS_TEMPLATE, $current_page_base,'css'), '/^print/', '.css');
  sort($directory_array);
  foreach($directory_array as $key => $value) {
    $printsheets[] = $template->get_template_dir('.css',DIR_WS_TEMPLATE, $current_page_base,'css') . '/' . $value;
  }

  /**
   * load all site-wide jscript_*.php files from includes/templates/YOURTEMPLATE/jscript, alphabetically
   */
  $directory_array = $template->get_template_part($template->get_template_dir('^jscript_.*\.php',DIR_WS_TEMPLATE, $current_page_base, 'jscript'), '/^jscript_/', '.php');
  sort($directory_array);
  foreach($directory_array as $key => $value) {
    /**
     * include content from all site-wide jscript_*.php files from includes/templates/YOURTEMPLATE/jscript, alphabetically.
     * These .php files can be manipulated by PHP when they're called, and are copied in-full to the browser page
     */
    if (preg_match('~^.*_top.php$~', $value)) {
      $jscriptsTop[] = $template->get_template_dir('^jscript_.*\.php',DIR_WS_TEMPLATE, $current_page_base,'jscript') . '/' . $value;
    } else {
      $jscripts[] = $template->get_template_dir('^jscript_.*\.php',DIR_WS_TEMPLATE, $current_page_base,'jscript') . '/' . $value;
    }
  }

  /**
   * include content from all page-specific jscript_*.php files from includes/modules/pages/PAGENAME, alphabetically.
   */
  $directory_array = $template->get_template_part($page_directory, '/^jscript_/');
  sort($directory_array);
  foreach($directory_array as $key => $value) {
    /**
     * include content from all page-specific jscript_*.php files from includes/modules/pages/PAGENAME, alphabetically.
     * These .PHP files can be manipulated by PHP when they're called, and are copied in-full to the browser page
     */
    if (preg_match('~.*_top.php$~', $value)) {
      $jscriptsTop[] = $page_directory . '/' . $value;
    } else {
      $jscripts[] = $page_directory . '/' . $value;
    }
  }

  /**
   * load all site-wide jscript_*.js files from includes/templates/YOURTEMPLATE/jscript, alphabetically
   */
  $directory_array = $template->get_template_part($template->get_template_dir('\.js',DIR_WS_TEMPLATE, $current_page_base,'jscript'), '/.*/', '.js');
  sort($directory_array);
  foreach($directory_array as $key => $value) {
    if (preg_match('~^.*_top.js$~', $value)) {
      $jsfilesTop[] = $template->get_template_dir('\.js',DIR_WS_TEMPLATE, $current_page_base,'jscript') . '/' . $value;
    } else {
      $jsfiles[] = $template->get_template_dir('\.js',DIR_WS_TEMPLATE, $current_page_base,'jscript') . '/' . $value;
    }
  }

  /**
   * load all page-specific jscript_*.js files from includes/modules/pages/PAGENAME, alphabetically
   */
  $directory_array = $template->get_template_part($page_directory, '/^.*/', '.js');
  sort($directory_array);
  foreach($directory_array as $key => $value) {
    if (preg_match('~^.*_top.js$~', $value)) {
      $jsfilesTop[] = $page_directory . '/' . $value;
    } else {
      $jsfiles[] = $page_directory . '/' . $value;
    }
  }

// DEBUG
// die('<pre>' . print_r($stylesheets, true) . print_r($inline_css, true). print_r($printsheets, true) . print_r($jsfilesTop, true) . print_r($jsfiles, true) . print_r($jscriptsTop, true) . print_r($jscripts, true));



/**
 * Now assemble stylesheets and linked scripts to prepare the default output HTML
 * NOTE: the arrays are still available in case the template designer wants to do something different in the template
 *       or in case an observer class is preferred and wants to override the generated content of $template_css_output and $template_js_output
 *       ie: someone might want to hook the starting and ending notifiers for this page and use them to periodically cache/recache the generated content
 * For those who want to minify/combine files, see the comments next to the notifier hook at the bottom of this file, below.
 */
$zco_notifier->notify('NOTIFY_MODULE_BUILD_STRINGS_IN_CSS_JS_GENERATOR', $css_js_handler, $stylesheets, $inline_css, $printsheets, $jsfilesTop, $jsfiles, $jscriptsTop, $jscripts);
foreach ($stylesheets as $val) {
  $template_css_output .= '    <link rel="stylesheet" type="text/css" href="' . $val . '" id="' . str_replace('.', '-', basename($val)) . '">'."\n";
}
// process "inline" as the "last loaded" ... in most cases this is just as efficient as actually loading "inline" ... and doing it this way allows for minifier/compiler scripts to override.
foreach ($inline_css as $val) {
  $template_css_output .= '    <link rel="stylesheet" type="text/css" href="' . $val . '" id="' . str_replace('.', '-', basename($val)) . '" />'."\n";
}
foreach ($printsheets as $val) {
  $template_css_output .= '    <link rel="stylesheet" type="text/css" media="print" href="' . $val . '" id="' . str_replace('.', '-', basename($val)) . '">'."\n";
}
//js
foreach ($jsfilesTop as $val) {
  $template_js_output_top .= '    <script src="' . $val . '"></script>' . "\n";
}
foreach ($jsfiles as $val) {
  $template_js_output_bottom .= '    <script src="' . $val . '"></script>' . "\n";
}
// NOTE: the $jscripts and $jscriptsTop arrays can't be processed into a string variable, since they use "require" statements which cause execution of other scripts.
// Therefore the arrays are simply passed back to the template so it can loop thru the arrays to do the needed execution at the appropriate time.


} else {
  require(DIR_WS_MODULES . zen_get_module_directory($css_js_handler));
}

/**
 * To minify or combine stylesheets and/or js files, hook the following notifier using an observer class and use the above arrays to feed your minify/combine script.
 */
// This should be last line of the script:
$zco_notifier->notify('NOTIFY_MODULE_END_CSS_JS_GENERATOR');
