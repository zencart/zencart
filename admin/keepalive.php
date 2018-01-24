<?php
/**
 * @package admin
 * @copyright Copyright 2003-2012 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id$
 *
 */
require ('includes/application_top.php');

if (isset($_SESSION['admin_id'])) {
  header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
  header("Cache-Control: no-store, no-cache, must-revalidate");
  header("Cache-Control: post-check=0, pre-check=0", false);
  header("Pragma: no-cache");
  echo 'OK';
} else {
  header("HTTP/1.1 401 Unauthorized");
}

require (DIR_WS_INCLUDES . 'application_bottom.php');