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
/**
 * generate hreflang for multilingual sites (ignored if only 1 language configured)
 */
require DIR_WS_MODULES . zen_get_module_directory('hreflang.php');

$zco_notifier->notify('NOTIFY_HTML_HEAD_CSS_BEGIN', $current_page_base);
/**
 * Load all template-specific stylesheets, via the common CSS loader.
 */
require $template->get_template_dir('html_header_css_loader.php', DIR_WS_TEMPLATE, $current_page_base, 'common') . '/html_header_css_loader.php';

/** CDN for jQuery core **/
?>
<script src="https://code.jquery.com/jquery-3.7.1.min.js" integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>
<script>window.jQuery || document.write(unescape('%3Cscript src="<?php echo $template->get_template_dir('.js',DIR_WS_TEMPLATE, $current_page_base,'jscript'); ?>/jquery.min.js"%3E%3C/script%3E'));</script>

<?php
$zco_notifier->notify('NOTIFY_HTML_HEAD_JS_BEGIN', $current_page_base);

/**
 * Load all template-specific jscript files, via the common jscript loader.
 */
require $template->get_template_dir('html_header_js_loader.php', DIR_WS_TEMPLATE, $current_page_base, 'common') . '/html_header_js_loader.php';

$zco_notifier->notify('NOTIFY_HTML_HEAD_END', $current_page_base);
?>

</head>
<?php // NOTE: Blank line following is intended: ?>

