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
     global $messageStack; 
     $parts = explode(",", $check_string, 3); 
     eval('$error_msg = ' . $parts[0] . ';'); 
     eval('$id = ' . $parts[1] . ';'); 
     eval('$options = ' . $parts[2] . ';'); 
     $result = filter_var($variable, $id, $options); 
     if ($result === false) { 
        $messageStack->add_session($error_msg, 'error');
        zen_redirect(zen_href_link(FILENAME_CONFIGURATION, 'gID=' . $_GET['gID'] . '&cID=' . (int)$cID));
     }
     return; 
  }
