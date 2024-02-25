<?php
/**
 * Common Template
 *
 * outputs the html header. i,e, everything that comes before the \</head\> tag
 *
 * @copyright Copyright 2003-2024 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: DrByte 2024 Feb 11 Modified in v2.0.0-beta1 $
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
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" <?php echo HTML_PARAMS; ?>>
<head>
<?php
// -----
// Provide a notification that the <head> tag has been rendered for the current page; some scripts need to be
// inserted just after that tag's rendered.
//
$zco_notifier->notify('NOTIFY_HTML_HEAD_TAG_START', $current_page_base);
?>
<meta charset="<?php echo CHARSET; ?>"/>
<link rel="dns-prefetch" href="https://cdnjs.cloudflare.com">
<link rel="dns-prefetch" href="https://code.jquery.com">
<title><?php echo META_TAG_TITLE; ?></title>
<meta name="keywords" content="<?php echo META_TAG_KEYWORDS; ?>"/>
<meta name="description" content="<?php echo META_TAG_DESCRIPTION; ?>"/>
<meta http-equiv="imagetoolbar" content="no"/>
<meta name="author" content="<?php echo STORE_NAME ?>"/>
<meta name="generator" content="shopping cart program by Zen Cart&reg;, https://www.zen-cart.com eCommerce"/>
<?php if (defined('ROBOTS_PAGES_TO_SKIP') && in_array($current_page_base,explode(",",constant('ROBOTS_PAGES_TO_SKIP'))) || $current_page_base=='down_for_maintenance' || $robotsNoIndex === true) { ?>
<meta name="robots" content="noindex, nofollow"/>
<?php } ?>

<meta name="viewport" content="width=device-width, initial-scale=1, user-scalable=yes"/>

<?php if (defined('FAVICON')) { ?>
<link rel="icon" href="<?php echo FAVICON; ?>" type="image/x-icon"/>
<link rel="shortcut icon" href="<?php echo FAVICON; ?>" type="image/x-icon"/>
<?php } //endif FAVICON ?>

<base href="<?php echo (($request_type == 'SSL') ? HTTPS_SERVER . DIR_WS_HTTPS_CATALOG : HTTP_SERVER . DIR_WS_CATALOG ); ?>"/>
<?php if (isset($canonicalLink) && $canonicalLink != '') { ?>
<link rel="canonical" href="<?php echo $canonicalLink; ?>"/>
<?php } ?>
<?php
// BOF hreflang for multilingual sites
if (!isset($lng) || (isset($lng) && !is_object($lng))) {
  $lng = new language;
}
if (count($languages = $lng->get_language_list()) > 1) {
  foreach($languages as $key) {
    echo '<link rel="alternate" href="' . ($this_is_home_page ? zen_href_link(FILENAME_DEFAULT, 'language=' . $key, $request_type, false) : $canonicalLink . (strpos($canonicalLink, '?') ? '&amp;' : '?') . 'language=' . $key) . '" hreflang="' . $key . '"/>' . "\n";
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
    echo '<link rel="stylesheet" href="' . $template->get_template_dir('.css',DIR_WS_TEMPLATE, $current_page_base,'css') . '/' . $value . '"/>'."\n";
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
    if (file_exists($perpagefile)) echo '<link rel="stylesheet" href="' . $perpagefile .'"/>'."\n";
  }

/**
 *  custom category handling for a parent and all its children ... works for any c_XX_XX_children.css  where XX_XX is any parent category
 */
  $tmp_cats = explode('_', $cPath);
  $value = '';
  foreach($tmp_cats as $val) {
    $value .= $val;
    $perpagefile = $template->get_template_dir('.css', DIR_WS_TEMPLATE, $current_page_base, 'css') . '/c_' . $value . '_children.css';
    if (file_exists($perpagefile)) echo '<link rel="stylesheet" href="' . $perpagefile .'"/>'."\n";
    $perpagefile = $template->get_template_dir('.css', DIR_WS_TEMPLATE, $current_page_base, 'css') . '/' . $_SESSION['language'] . '_c_' . $value . '_children.css';
    if (file_exists($perpagefile)) echo '<link rel="stylesheet" href="' . $perpagefile .'"/>'."\n";
    $value .= '_';
  }

/**
 * load printer-friendly stylesheets -- named like "print*.css", alphabetically
 */
  $directory_array = $template->get_template_part($template->get_template_dir('.css',DIR_WS_TEMPLATE, $current_page_base,'css'), '/^print/', '.css');
  sort($directory_array);
  foreach($directory_array as $key => $value) {
    echo '<link rel="stylesheet" media="print" href="' . $template->get_template_dir('.css',DIR_WS_TEMPLATE, $current_page_base,'css') . '/' . $value . '"/>'."\n";
  }


/**
 * load all DYNAMIC template-specific stylesheets, named like "style*.php", alphabetically
 */
$directory_array = $template->get_template_part($template->get_template_dir('.php',DIR_WS_TEMPLATE, $current_page_base,'css'), '/^style/', '.php');
foreach($directory_array as $key => $value) {
    require $template->get_template_dir('.php',DIR_WS_TEMPLATE, $current_page_base,'css') . '/' . $value;
}

// User defined styles come last
$user_styles = DIR_WS_TEMPLATE . 'css/site_specific_styles.php';
if (file_exists($user_styles)) {
    require $user_styles;
}

/** CDN for jQuery core **/
?>
<script src="https://code.jquery.com/jquery-3.6.1.min.js" integrity="sha256-o88AwQnZB+VDvE9tvIXrMQaPlFFSUTR+nldQm1LuPXQ=" crossorigin="anonymous"></script>
<script>window.jQuery || document.write(unescape('%3Cscript src="<?php echo $template->get_template_dir('.js',DIR_WS_TEMPLATE, $current_page_base,'jscript'); ?>/jquery.min.js"%3E%3C/script%3E'));</script>

<?php
/**
 * load all site-wide jscript_*.js files from includes/templates/YOURTEMPLATE/jscript, alphabetically
 */
  $directory_array = $template->get_template_part($template->get_template_dir('.js',DIR_WS_TEMPLATE, $current_page_base,'jscript'), '/^jscript_/', '.js');
  foreach($directory_array as $key => $value) {
    echo '<script src="' .  $template->get_template_dir('.js',DIR_WS_TEMPLATE, $current_page_base,'jscript') . '/' . $value . '"></script>'."\n";
  }

/**
 * load all page-specific jscript_*.js files from includes/modules/pages/PAGENAME, alphabetically
 */
  $directory_array = $template->get_template_part($page_directory, '/^jscript_/', '.js');
  foreach($directory_array as $key => $value) {
    echo '<script src="' . $page_directory . '/' . $value . '"></script>' . "\n";
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

  $zco_notifier->notify('NOTIFY_HTML_HEAD_END', $current_page_base);
?>

</head>
<?php // NOTE: Blank line following is intended: ?>

