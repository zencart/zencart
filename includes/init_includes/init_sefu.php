<?php
/**
 * set the HTTP GET parameters manually if search_engine_friendly_urls is enabled
 * see  {@link  https://docs.zen-cart.com/dev/code/init_system/} for more details.
 * @copyright Copyright 2003-2022 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: Scott C Wilson 2020 Aug 01 Modified in v1.5.8-alpha $
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
      foreach($GET_array as $key => $value) {
        $_GET[$key] = $value;
      }
    }
  }
}
