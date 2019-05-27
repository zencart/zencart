<?php
/**
 * Common Template
 *
 * outputs the html header. i,e, everything that comes before the \</head\> tag <br />
 *
 * @package templateSystem
 * @copyright Copyright 2003-2019 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: Zen4All 2019 Apr 25 Modified in v1.5.6b $
 */

$zco_notifier->notify('NOTIFY_HTML_HEAD_START', $current_page_base, $template_dir);

// Prevent clickjacking risks by setting X-Frame-Options:SAMEORIGIN
header('X-Frame-Options:SAMEORIGIN');

/**
 * load the module for generating page meta-tags
 */
require(DIR_WS_MODULES . zen_get_module_directory('meta_tags.php'));
/**
 * output main page HEAD tag and related headers/meta-tags, etc
 */
?>
<?php

// ZCAdditions.com, ZCA Responsive Template Default (BOF-addition 1 of 2)
if (!class_exists('Mobile_Detect')) {
  include_once(DIR_WS_CLASSES . 'Mobile_Detect.php');
}
  $detect = new Mobile_Detect;
  $isMobile = $detect->isMobile();
  $isTablet = $detect->isTablet();
  if (!isset($layoutType)) $layoutType = ($isMobile ? ($isTablet ? 'tablet' : 'mobile') : 'default');
// ZCAdditions.com, ZCA Responsive Template Default (BOF-addition 1 of 2)

  $paginateAsUL = true;

?>
<!DOCTYPE html>
<html <?php echo HTML_PARAMS; ?>>
  <head>
  <meta charset="<?php echo CHARSET; ?>">
  <title><?php echo META_TAG_TITLE; ?></title>
  <meta name="keywords" content="<?php echo META_TAG_KEYWORDS; ?>" />
  <meta name="description" content="<?php echo META_TAG_DESCRIPTION; ?>" />
  <meta name="author" content="<?php echo STORE_NAME ?>" />
  <meta name="generator" content="shopping cart program by Zen Cart&reg;, http://www.zen-cart.com eCommerce" />
<?php if (defined('ROBOTS_PAGES_TO_SKIP') && in_array($current_page_base,explode(",",constant('ROBOTS_PAGES_TO_SKIP'))) || $current_page_base=='down_for_maintenance' || $robotsNoIndex === true) { ?>
  <meta name="robots" content="noindex, nofollow" />
<?php } ?>

  <meta name="viewport" content="width=device-width, initial-scale=1, user-scalable=yes"/>

<?php if (defined('FAVICON')) { ?>
  <link rel="icon" href="<?php echo FAVICON; ?>" type="image/x-icon" />
  <link rel="shortcut icon" href="<?php echo FAVICON; ?>" type="image/x-icon" />
<?php } //endif FAVICON ?>

  <base href="<?php echo (($request_type == 'SSL') ? HTTPS_SERVER . DIR_WS_HTTPS_CATALOG : HTTP_SERVER . DIR_WS_CATALOG ); ?>" />
<?php if (isset($canonicalLink) && $canonicalLink != '') { ?>
  <link rel="canonical" href="<?php echo $canonicalLink; ?>" />
<?php } ?>
<?php
  // BOF hreflang for multilingual sites
  if (!isset($lng) || (isset($lng) && !is_object($lng))) {
    $lng = new language;
  }
if (count($lng->catalog_languages) > 1) {
  foreach($lng->catalog_languages as $key => $value) {
    echo '<link rel="alternate" href="' . ($this_is_home_page ? zen_href_link(FILENAME_DEFAULT, 'language=' . $key, $request_type, false) : $canonicalLink . (strpos($canonicalLink, '?') ? '&amp;' : '?') . 'language=' . $key) . '" hreflang="' . $key . '" />' . "\n";
  }
  }
  // EOF hreflang for multilingual sites
?>

<?php
/**
 * load all template-specific stylesheets, named like "style*.css", alphabetically
 */
  $directory_array = $template->get_template_part($template->get_template_dir('.css',DIR_WS_TEMPLATE, $current_page_base,'css'), '/^style/', '.css');
  foreach($directory_array as $key => $value) {
    echo '<link rel="stylesheet" type="text/css" href="' . $template->get_template_dir('.css',DIR_WS_TEMPLATE, $current_page_base,'css') . '/' . $value . '" />'."\n";
  }
/**
 * load stylesheets on a per-page/per-language/per-product/per-manufacturer/per-category basis. Concept by Juxi Zoza.
 */
  $manufacturers_id = (isset($_GET['manufacturers_id'])) ? $_GET['manufacturers_id'] : '';
  $tmp_products_id = (isset($_GET['products_id'])) ? (int)$_GET['products_id'] : '';
  $tmp_pagename = ($this_is_home_page) ? 'index_home' : $current_page_base;
  if ($current_page_base == 'page' && isset($ezpage_id)) $tmp_pagename = $current_page_base . (int)$ezpage_id;
  $sheets_array = array('/' . $_SESSION['language'] . '_stylesheet',
                        '/' . $tmp_pagename,
                        '/' . $_SESSION['language'] . '_' . $tmp_pagename,
                        '/c_' . $cPath,
                        '/' . $_SESSION['language'] . '_c_' . $cPath,
                        '/m_' . $manufacturers_id,
                        '/' . $_SESSION['language'] . '_m_' . (int)$manufacturers_id,
                        '/p_' . $tmp_products_id,
                        '/' . $_SESSION['language'] . '_p_' . $tmp_products_id
                        );
  foreach($sheets_array as $key => $value) {
    //echo "<!--looking for: $value-->\n";
    $perpagefile = $template->get_template_dir('.css', DIR_WS_TEMPLATE, $current_page_base, 'css') . $value . '.css';
    if (file_exists($perpagefile)) echo '<link rel="stylesheet" type="text/css" href="' . $perpagefile .'" />'."\n";
  }

/**
 *  custom category handling for a parent and all its children ... works for any c_XX_XX_children.css  where XX_XX is any parent category
 */
  $tmp_cats = explode('_', $cPath);
  $value = '';
  foreach($tmp_cats as $val) {
    $value .= $val;
    $perpagefile = $template->get_template_dir('.css', DIR_WS_TEMPLATE, $current_page_base, 'css') . '/c_' . $value . '_children.css';
    if (file_exists($perpagefile)) echo '<link rel="stylesheet" type="text/css" href="' . $perpagefile .'" />'."\n";
    $perpagefile = $template->get_template_dir('.css', DIR_WS_TEMPLATE, $current_page_base, 'css') . '/' . $_SESSION['language'] . '_c_' . $value . '_children.css';
    if (file_exists($perpagefile)) echo '<link rel="stylesheet" type="text/css" href="' . $perpagefile .'" />'."\n";
    $value .= '_';
  }

/**
 * load printer-friendly stylesheets -- named like "print*.css", alphabetically
 */
  $directory_array = $template->get_template_part($template->get_template_dir('.css',DIR_WS_TEMPLATE, $current_page_base,'css'), '/^print/', '.css');
  sort($directory_array);
  foreach($directory_array as $key => $value) {
    echo '<link rel="stylesheet" type="text/css" media="print" href="' . $template->get_template_dir('.css',DIR_WS_TEMPLATE, $current_page_base,'css') . '/' . $value . '" />'."\n";
  }

/** CDN for jQuery core **/
?>

<script type="text/javascript">window.jQuery || document.write(unescape('%3Cscript type="text/javascript" src="https://code.jquery.com/jquery-3.4.0.min.js" integrity="sha256-BJeo0qm959uMBGb65z40ejJYGSgR7REI4+CW1fNKwOg=" crossorigin="anonymous"%3E%3C/script%3E'));</script>
<?php if (file_exists(DIR_WS_TEMPLATE . "jscript/jquery.min.js")) { ?>
<script type="text/javascript">window.jQuery || document.write(unescape('%3Cscript type="text/javascript" src="<?php echo $template->get_template_dir('.js',DIR_WS_TEMPLATE, $current_page_base,'jscript'); ?>/jquery.min.js"%3E%3C/script%3E'));</script>
<?php } ?>
<script type="text/javascript">window.jQuery || document.write(unescape('%3Cscript type="text/javascript" src="<?php echo $template->get_template_dir('.js','template_default', $current_page_base,'jscript'); ?>/jquery.min.js"%3E%3C/script%3E'));</script>

<?php
/**
 * load all site-wide jscript_*.js files from includes/templates/YOURTEMPLATE/jscript, alphabetically
 */
  $directory_array = $template->get_template_part($template->get_template_dir('.js',DIR_WS_TEMPLATE, $current_page_base,'jscript'), '/^jscript_/', '.js');
  foreach($directory_array as $key => $value) {
    echo '<script type="text/javascript" src="' .  $template->get_template_dir('.js',DIR_WS_TEMPLATE, $current_page_base,'jscript') . '/' . $value . '"></script>'."\n";
  }

/**
 * load all page-specific jscript_*.js files from includes/modules/pages/PAGENAME, alphabetically
 */
  $directory_array = $template->get_template_part($page_directory, '/^jscript_/', '.js');
  foreach($directory_array as $key => $value) {
    echo '<script type="text/javascript" src="' . $page_directory . '/' . $value . '"></script>' . "\n";
  }

/**
 * load all site-wide jscript_*.php files from includes/templates/YOURTEMPLATE/jscript, alphabetically
 */
  $directory_array = $template->get_template_part($template->get_template_dir('.php',DIR_WS_TEMPLATE, $current_page_base,'jscript'), '/^jscript_/', '.php');
  foreach($directory_array as $key => $value) {
/**
 * include content from all site-wide jscript_*.php files from includes/templates/YOURTEMPLATE/jscript, alphabetically.
 * These .PHP files can be manipulated by PHP when they're called, and are copied in-full to the browser page
 */
    require($template->get_template_dir('.php',DIR_WS_TEMPLATE, $current_page_base,'jscript') . '/' . $value); echo "\n";
  }
/**
 * include content from all page-specific jscript_*.php files from includes/modules/pages/PAGENAME, alphabetically.
 */
  $directory_array = $template->get_template_part($page_directory, '/^jscript_/');
  foreach($directory_array as $key => $value) {
/**
 * include content from all page-specific jscript_*.php files from includes/modules/pages/PAGENAME, alphabetically.
 * These .PHP files can be manipulated by PHP when they're called, and are copied in-full to the browser page
 */
    require($page_directory . '/' . $value); echo "\n";
  }
?>

<?php // ZCAdditions.com, ZCA Responsive Template Default (BOF-addition 2 of 2)
$responsive_mobile = '<link rel="stylesheet" type="text/css" href="' . $template->get_template_dir('.css',DIR_WS_TEMPLATE, $current_page_base,'css') . '/' . 'responsive_mobile.css' . '" /><link rel="stylesheet" type="text/css" href="' . $template->get_template_dir('.css',DIR_WS_TEMPLATE, $current_page_base,'css') . '/' . 'jquery.mmenu.all.css' . '" />';
$responsive_tablet = '<link rel="stylesheet" type="text/css" href="' . $template->get_template_dir('.css',DIR_WS_TEMPLATE, $current_page_base,'css') . '/' . 'responsive_tablet.css' . '" /><link rel="stylesheet" type="text/css" href="' . $template->get_template_dir('.css',DIR_WS_TEMPLATE, $current_page_base,'css') . '/' . 'jquery.mmenu.all.css' . '" />';
$responsive_default = '<link rel="stylesheet" type="text/css" href="' . $template->get_template_dir('.css',DIR_WS_TEMPLATE, $current_page_base,'css') . '/' . 'responsive_default.css' . '" />';

if (!isset($_SESSION['layoutType'])) {
  $_SESSION['layoutType'] = 'legacy';
}

if (in_array($current_page_base,explode(",",'popup_image,popup_image_additional')) ) {
  echo '';
} else {
  echo '<link rel="stylesheet" type="text/css" href="' . $template->get_template_dir('.css',DIR_WS_TEMPLATE, $current_page_base,'css') . '/' . 'responsive.css' . '" />';
  if ( $detect->isMobile() && !$detect->isTablet() || $_SESSION['layoutType'] == 'mobile' ) {
    echo $responsive_mobile;
  } else if ( $detect->isTablet() || $_SESSION['layoutType'] == 'tablet' ){
    echo $responsive_tablet;
  } else if ( $_SESSION['layoutType'] == 'full' ) {
    echo '';
  } else {
    echo $responsive_default;
  }
}
?>
  <script type="text/javascript">document.documentElement.className = 'no-fouc';</script>
  <link rel="stylesheet" type="text/css" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css" />
<?php // ZCAdditions.com, ZCA Responsive Template Default (EOF-addition 2 of 2) ?>
<?php
  $zco_notifier->notify('NOTIFY_HTML_HEAD_END', $current_page_base);
?>
</head>

<?php // NOTE: Blank line following is intended: ?>

