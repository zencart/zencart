<?php
/**
 * @package admin
 * @copyright Copyright 2003-2013 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version GIT: $Id: Author: DrByte  Sat Jun 1 13:30:37 2013 -0400 Modified in v1.5.2 $
 */
require('includes/application_top.php');
?>
<!doctype html>
<html <?php echo HTML_PARAMS; ?>>
<head>
    <?php require DIR_WS_INCLUDES . 'admin_html_head.php'; ?>
</head>
<body>
<!-- header //-->
<?php require(DIR_WS_INCLUDES . 'header.php'); ?>
<!-- header_eof //-->
<div id="alt_nav">
<h1><?php echo HEADING_TITLE ?></h1>

<?php
 foreach (zen_get_admin_menu_for_user() as $menuKey => $pages)
 {
   $pageList = array();
   foreach ($pages as $page)
   {
      $pageList[] = '<a href="' . zen_href_link($page['file'], $page['params']) . '">' . $page['name'] . '</a>';
   }
?>
  <div>
    <h2><?php echo $menuTitles[$menuKey] ?></h2>
    <p><?php echo implode(', ', $pageList) ?>.</p>
  </div>
<?php
 }
?>
</div>

<!-- body_eof //-->
<!-- footer //-->
<?php require(DIR_WS_INCLUDES . 'footer.php'); ?>
<!-- footer_eof //-->
<br>
</body>
</html>
<?php require(DIR_WS_INCLUDES . 'application_bottom.php'); ?>
