<?php
/**
 * @package admin
 * @copyright Copyright 2015 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version 
 */

  // Function used for configuration checks only
  function check_configuration($variable, $check_string) { 
     $parts = explode(",", $check_string, 2); 
     eval('$id = ' . $parts[0] . ';'); 
     eval('$options = ' . $parts[1] . ';'); 
     $result = filter_var($variable, $id, $options); 
     return $result; 
  }
