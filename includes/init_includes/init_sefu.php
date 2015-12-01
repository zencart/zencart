<?php
/**
 * set the HTTP GET parameters manually if search_engine_friendly_urls is enabled
 * see {@link  http://www.zen-cart.com/wiki/index.php/Developers_API_Tutorials#InitSystem wikitutorials} for more details.
 *
 * @package initSystem
 * @copyright Copyright 2003-2005 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: init_sefu.php 2753 2005-12-31 19:17:17Z wilt $
 */ 
if (!defined('IS_ADMIN_FLAG')) {
  die('Illegal Access');
}
if (SEARCH_ENGINE_FRIENDLY_URLS == 'true') {
  if (strlen($_SERVER['REQUEST_URI']) > 1) {
    $GET_array = array();
    $PHP_SELF = $_SERVER['SCRIPT_NAME'];
    $vars = explode('/', substr($_SERVER['REQUEST_URI'], 1));
    for ($i=0, $n=sizeof($vars); $i<$n; $i++) {
      if (strpos($vars[$i], '[]')) {
        $GET_array[substr($vars[$i], 0, -2)][] = $vars[$i+1];
      } else {
        $_GET[$vars[$i]] = $vars[$i+1];
      }
      $i++;
    }
    if (sizeof($GET_array) > 0) {
      while (list($key, $value) = each($GET_array)) {
        $_GET[$key] = $value;
      }
    }
  }
}
?>