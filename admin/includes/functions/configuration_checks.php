<?php
/**
 * @package admin
 * @copyright Copyright 2015 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version 
 */
 
  /**
  *   Function used for configuration checks only.
  *   @param $variable - variable to be checked
  *   @param $check_string - a json encoded array containing: 
  *     error: defined constant containing error message
  *     id: id of the filter to apply. (May be mnemonic value of int.)
  *     options: per http://php.net/manual/en/function.filter-var.php
  *   @return - NULL; failure results in redirection inline.
  */ 
  function zen_validate_configuration_entry($variable, $check_string) { 
     global $messageStack; 
     $data = json_decode($check_string, true); 
     // check inputs - error should be a defined constant in the language files
     if (empty($data['error']) || !defined($data['error'])) return;
     $error_msg = constant($data['error']); 

     if (defined($data['id'])) { 
        $id = constant($data['id']); 
     } else if (is_integer($data['id'])) { 
        $id = $data['id']; 
     } else { 
        return; 
     }

     // example: $options = array('options' => array('min_range' => 4));
     if (!is_array($data['options'])) return;
     $options = $data['options']; 
 
     $result = filter_var($variable, $id, $options); 
     if ($result === false) { 
        $messageStack->add_session($error_msg, 'error');
        zen_redirect(zen_href_link(FILENAME_CONFIGURATION, 'gID=' . $_GET['gID'] . '&cID=' . (int)$cID));
     }
     return; 
  }
