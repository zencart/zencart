<?php
/**
 * @copyright Copyright 2003-2024 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: lat9 2023 Oct 25 Modified in v2.0.0-alpha1 $
 */
if (!defined('IS_ADMIN_FLAG')) {
    die('Illegal Access');
}

use Zencart\ResourceLoaders\TemplateResolver;
use Zencart\Templates\TemplateSelect;

// Set theme related directories
if (empty($template_dir)) {
    $templateSelect = new TemplateSelect();
    $template_dir = $templateSelect->getActiveTemplateDir();
}
$templateResolver = new TemplateResolver();
$templateRecord = $templateResolver->getTemplateRecord($template_dir) ?? $templateResolver->getTemplateRecord('template_default');
if ($templateRecord === null) {
    die('Fatal error: template_default could not be resolved.');
}
$template_dir = $templateRecord['template_key'];

zen_define_default('DIR_WS_TEMPLATE', $templateRecord['template_catalog_path']);
zen_define_default('DIR_WS_TEMPLATE_IMAGES', $templateRecord['template_web_path'] . 'images/');
zen_define_default('DIR_WS_TEMPLATE_ICONS', DIR_WS_TEMPLATE_IMAGES . 'icons/');

require DIR_FS_CATALOG . DIR_WS_CLASSES . 'template_func.php';
$template = new template_func(DIR_WS_TEMPLATE);

/**
 * send the content charset "now" so that all content is impacted by it - this is important for non-english sites
 */
header("Content-Type: text/html; charset=" . CHARSET);

/**
 * set HTML <title> tag for admin pages
 */
$pagename = preg_replace('/\.php$/', '', basename($PHP_SELF));
if ($pagename === 'configuration') {
    $pagename .= " ". zen_get_configuration_group_value($_GET['gID']);
}

$pagename = str_replace('_', ' ', $pagename);
if ($pagename === 'index') {
    $pagename = HEADER_TITLE_TOP; // Admin home page/dashboard
}

$pagename = ucwords($pagename);
if ($pagename === '') {
  $pagename = STORE_NAME;
}
$title = TEXT_ADMIN_TAB_PREFIX . ' ' . $pagename;
zen_define_default('TITLE', $title);
