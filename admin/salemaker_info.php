<?php
/**
 * @package admin
 * @copyright Copyright 2003-2015 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: salemaker_info.php  Modified in v1.6.0 $
 */

  require("includes/application_top.php");

  require(DIR_WS_LANGUAGES . $_SESSION['language'] . '/' . FILENAME_SALEMAKER_INFO . '.php');
?>
<!doctype html public "-//W3C//DTD HTML 4.01 Transitional//EN">
<html <?php echo HTML_PARAMS; ?>>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=<?php echo CHARSET; ?>">
<title><?php echo ADMIN_TITLE; ?></title>
<link rel="stylesheet" type="text/css" href="includes/stylesheet.css">
</head>
<body>
<p class="main"><center><h1><?php echo HEADING_TITLE; ?><?php echo zen_draw_separator(); ?></h1></center></p>
<table width="90%" align="center">
<p class="main"><h3><?php echo SUBHEADING_TITLE; ?></h3></p>
<div class="main">
<?php echo INFO_TEXT; ?>
</div>
<p align="center" class="main"><a href="javascript:window.close();"><?php echo TEXT_CLOSE_WINDOW; ?></a></p>
</body>
</html>
<?php
  require(DIR_WS_INCLUDES . 'application_bottom.php');
