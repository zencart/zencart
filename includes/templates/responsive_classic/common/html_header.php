<?php
/**
 * Common Template
 *
 * outputs the html header. i,e, everything that comes before the \</head\> tag
 *
 * @copyright Copyright 2003-2024 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: DrByte 2024 Sep 25 Modified in v2.1.0-beta1 $
 */

if (!defined('IS_ADMIN_FLAG')) {
    die('Illegal Access');
}

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
if (!class_exists('MobileDetect')) {
  include_once(DIR_WS_CLASSES . 'Mobile_Detect.php');
}
  $detect = new Detection\MobileDetect;
  $isMobile = $detect->isMobile();
  $isTablet = $detect->isTablet();
  if (!isset($layoutType)) $layoutType = ($isMobile ? ($isTablet ? 'tablet' : 'mobile') : 'default');
// ZCAdditions.com, ZCA Responsive Template Default (BOF-addition 1 of 2)

  $paginateAsUL = true;

?>
<!DOCTYPE html>
<html <?php echo HTML_PARAMS; ?>>
  <head>
<?php
// -----
// Provide a notification that the <head> tag has been rendered for the current page; some scripts need to be
// inserted just after that tag's rendered.
//
$zco_notifier->notify('NOTIFY_HTML_HEAD_TAG_START', $current_page_base);
?>
  <meta charset="<?php echo CHARSET; ?>">
  <link rel="dns-prefetch" href="https://cdnjs.cloudflare.com">
  <link rel="dns-prefetch" href="https://code.jquery.com">
  <title><?php echo META_TAG_TITLE; ?></title>
  <meta name="keywords" content="<?php echo META_TAG_KEYWORDS; ?>">
  <meta name="description" content="<?php echo META_TAG_DESCRIPTION; ?>">
  <meta name="author" content="<?php echo STORE_NAME ?>">
  <meta name="generator" content="shopping cart program by Zen Cart&reg;, https://www.zen-cart.com eCommerce">
<?php if (defined('ROBOTS_PAGES_TO_SKIP') && in_array($current_page_base,explode(",",constant('ROBOTS_PAGES_TO_SKIP'))) || $current_page_base=='down_for_maintenance' || $robotsNoIndex === true) { ?>
  <meta name="robots" content="noindex, nofollow">
<?php } ?>

  <meta name="viewport" content="width=device-width, initial-scale=1, user-scalable=yes">

<?php if (defined('FAVICON')) { ?>
  <link rel="icon" href="<?php echo FAVICON; ?>" type="image/x-icon">
  <link rel="shortcut icon" href="<?php echo FAVICON; ?>" type="image/x-icon">
<?php } //endif FAVICON ?>

  <base href="<?php echo (($request_type == 'SSL') ? HTTPS_SERVER . DIR_WS_HTTPS_CATALOG : HTTP_SERVER . DIR_WS_CATALOG ); ?>">
<?php if (isset($canonicalLink) && $canonicalLink != '') { ?>
  <link rel="canonical" href="<?php echo $canonicalLink; ?>">
<?php } ?>
<?php
/**
 * generate hreflang for multilingual sites (ignored if only 1 language configured)
 */
require DIR_WS_MODULES . zen_get_module_directory('hreflang.php');
?>

<?php
$zco_notifier->notify('NOTIFY_HTML_HEAD_CSS_BEGIN', $current_page_base);

/**
 * Load all template-specific stylesheets, via the common CSS loader.
 */
require $template->get_template_dir('html_header_css_loader.php', DIR_WS_TEMPLATE, $current_page_base, 'common') . '/html_header_css_loader.php';

/** CDN for jQuery core **/
?>
<script src="https://code.jquery.com/jquery-3.7.1.min.js" integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>
<?php if (file_exists(DIR_WS_TEMPLATE . "jscript/jquery.min.js")) { ?>
<script>window.jQuery || document.write(unescape('%3Cscript src="<?php echo $template->get_template_dir('.js',DIR_WS_TEMPLATE, $current_page_base,'jscript'); ?>/jquery.min.js"%3E%3C/script%3E'));</script>
<?php } ?>
<script>window.jQuery || document.write(unescape('%3Cscript src="<?php echo $template->get_template_dir('.js','template_default', $current_page_base,'jscript'); ?>/jquery.min.js"%3E%3C/script%3E'));</script>

<?php
$zco_notifier->notify('NOTIFY_HTML_HEAD_JS_BEGIN', $current_page_base);

/**
 * Load all template-specific jscript files, via the common jscript loader.
 */
require $template->get_template_dir('html_header_js_loader.php', DIR_WS_TEMPLATE, $current_page_base, 'common') . '/html_header_js_loader.php';
?>

<?php // ZCAdditions.com, ZCA Responsive Template Default (BOF-addition 2 of 2)
$responsive_mobile = '<link rel="stylesheet" href="' . $template->get_template_dir('.css',DIR_WS_TEMPLATE, $current_page_base,'css') . '/' . 'responsive_mobile.css' . '"><link rel="stylesheet" href="' . $template->get_template_dir('.css',DIR_WS_TEMPLATE, $current_page_base,'css') . '/' . 'jquery.mmenu.all.css' . '">';
$responsive_tablet = '<link rel="stylesheet" href="' . $template->get_template_dir('.css',DIR_WS_TEMPLATE, $current_page_base,'css') . '/' . 'responsive_tablet.css' . '"><link rel="stylesheet" href="' . $template->get_template_dir('.css',DIR_WS_TEMPLATE, $current_page_base,'css') . '/' . 'jquery.mmenu.all.css' . '">';
$responsive_default = '<link rel="stylesheet" href="' . $template->get_template_dir('.css',DIR_WS_TEMPLATE, $current_page_base,'css') . '/' . 'responsive_default.css' . '">';

if (!isset($_SESSION['layoutType'])) {
  $_SESSION['layoutType'] = 'legacy';
}

if (in_array($current_page_base,explode(",",'popup_image,popup_image_additional')) ) {
  echo '';
} else {
  echo '<link rel="stylesheet" href="' . $template->get_template_dir('.css',DIR_WS_TEMPLATE, $current_page_base,'css') . '/' . 'responsive.css' . '">';
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
  <script>document.documentElement.className = 'no-fouc';</script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/fontawesome.min.css" integrity="sha256-PchpyCpyLZ/Xx9iBpFPuPSadRhkXx6J5Aa01fZ3Lv8Q= sha384-bGIKHDMAvn+yR8S/yTRi+6S++WqBdA+TaJ1nOZf079H6r492oh7V6uAqq739oSZC sha512-SgaqKKxJDQ/tAUAAXzvxZz33rmn7leYDYfBP+YoMRSENhf3zJyx3SBASt/OfeQwBHA1nxMis7mM3EV/oYT6Fdw==" crossorigin="anonymous"/>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/solid.min.css" integrity="sha256-tD3MiV7I+neAR7aQYvGSBykka5Rvugw0zd0V5VioAeM= sha384-o96F2rFLAgwGpsvjLInkYtEFanaHuHeDtH47SxRhOsBCB2GOvUZke4yVjULPMFnv sha512-yDUXOUWwbHH4ggxueDnC5vJv4tmfySpVdIcN1LksGZi8W8EVZv4uKGrQc0pVf66zS7LDhFJM7Zdeow1sw1/8Jw==" crossorigin="anonymous"/>
  <?php if (empty($disableFontAwesomeV4Compatibility)) { ?>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/v4-shims.css" integrity="sha256-CB2v9WYYUz97XoXZ4htbPxCe33AezlF5MY8ufd1eyQ8= sha384-JfB3EVqS5xkU+PfLClXRAMlOqJdNIb2TNb98chdDBiv5yD7wkdhdjCi6I2RIZ+mL sha512-tqGH6Vq3kFB19sE6vx9P6Fm/f9jWoajQ05sFTf0hr3gwpfSGRXJe4D7BdzSGCEj7J1IB1MvkUf3V/xWR25+zvw==" crossorigin="anonymous">
  <?php } ?>
<?php // ZCAdditions.com, ZCA Responsive Template Default (EOF-addition 2 of 2) ?>
<?php
  $zco_notifier->notify('NOTIFY_HTML_HEAD_END', $current_page_base);
?>
</head>

<?php // NOTE: Blank line following is intended: ?>

