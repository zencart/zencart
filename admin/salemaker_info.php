<?php
//
// +----------------------------------------------------------------------+
// |zen-cart Open Source E-commerce                                       |
// +----------------------------------------------------------------------+
// | Copyright (c) 2003 The zen-cart developers                           |
// |                                                                      |
// | http://www.zen-cart.com/index.php                                    |
// |                                                                      |
// | Portions Copyright (c) 2003 osCommerce                               |
// +----------------------------------------------------------------------+
// | This source file is subject to version 2.0 of the GPL license,       |
// | that is bundled with this package in the file LICENSE, and is        |
// | available through the world-wide-web at the following url:           |
// | http://www.zen-cart.com/license/2_0.txt.                             |
// | If you did not receive a copy of the zen-cart license and are unable |
// | to obtain it through the world-wide-web, please send a note to       |
// | license@zen-cart.com so we can mail you a copy immediately.          |
// +----------------------------------------------------------------------+
//  $Id: salemaker_info.php 1969 2005-09-13 06:57:21Z drbyte $
//
require("includes/application_top.php");

require(DIR_WS_LANGUAGES . $_SESSION['language'] . '/' . FILENAME_SALEMAKER_INFO . '.php');
?>
<!doctype html>
<html <?php echo HTML_PARAMS; ?>>
  <head>
    <meta charset="<?php echo CHARSET; ?>">
    <title><?php echo TITLE; ?></title>
    <link rel="stylesheet" href="includes/stylesheet.css">
  </head>
  <body>
    <h1 class="text-center"><?php echo HEADING_TITLE; ?></h1>
    <?php echo zen_draw_separator(); ?>
    <h3><?php echo SUBHEADING_TITLE; ?></h3>
    <div class="main"><?php echo INFO_TEXT; ?></div>
    <p align="center" class="main"><a href="javascript:window.close();"><?php echo TEXT_CLOSE_WINDOW; ?></a></p>
  </body>
</html>
<?php
require(DIR_WS_INCLUDES . 'application_bottom.php');
?>
