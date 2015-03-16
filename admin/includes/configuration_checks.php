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
     // Expected format of $check string is a json encoded array 
     // with the following paramters: 
     // error: defined constant with error message
     // id: id of the filter to apply.   
     // options: per http://php.net/manual/en/function.filter-var.php
     global $messageStack; 
     $data = json_decode($check_string, true); 
     // check inputs - error should be a defined constant
     if (!empty($data['error']) && defined($data['error'])) { 
        $error_msg = constant($data['error']); 
     } else {
        $error_msg = 'Validation error - bad error field'; 
        return; 
     }
     if (is_integer($data['id'])) { 
        $id = $data['id']; 
     } else { 
        $error_msg = 'Validation error - bad id field'; 
        return; 
     }
     if (is_array($data['options'])) { 
        $options = $data['options']; 
     } else { 
        // example: $options = array('options' => array('min_range' => 4));
        $error_msg = 'Validation error - bad options field'; 
        return; 
     }
     $result = filter_var($variable, $id, $options); 
     if ($result === false) { 
        $messageStack->add_session($error_msg, 'error');
        zen_redirect(zen_href_link(FILENAME_CONFIGURATION, 'gID=' . $_GET['gID'] . '&cID=' . (int)$cID));
     }
     return; 
  }
