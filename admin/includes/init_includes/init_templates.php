<?php
/**
 * @package admin
 * @copyright Copyright 2003-2010 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: init_templates.php 15995 2010-04-19 17:41:54Z drbyte $
 */
if (!defined('IS_ADMIN_FLAG')) {
  die('Illegal Access');
}
// Set theme related directories
if (!isset($template_dir) || $template_dir == '') {
  $template_query = $db->Execute("select template_dir from " . TABLE_TEMPLATE_SELECT . " where template_language in (" . (int)$_SESSION['languages_id'] . ', 0' . ") order by template_language DESC");
  $template_dir = $template_query->fields['template_dir'];
}
  define('DIR_WS_TEMPLATE', DIR_WS_TEMPLATES . $template_dir . '/');
  //  define('DIR_WS_TEMPLATE_IMAGES', DIR_WS_CATALOG_TEMPLATE . $template_dir . '/images/');
  define('DIR_WS_TEMPLATE_IMAGES', DIR_WS_CATALOG_TEMPLATE . 'template_default' . '/images/');
  define('DIR_WS_TEMPLATE_ICONS', DIR_WS_TEMPLATE_IMAGES . 'icons/');

  require(DIR_FS_CATALOG . DIR_WS_CLASSES . 'template_func.php');
  $template = new template_func(DIR_WS_TEMPLATE);

/**
 * send the content charset "now" so that all content is impacted by it - this is important for non-english sites
 */
  header("Content-Type: text/html; charset=" . CHARSET);

