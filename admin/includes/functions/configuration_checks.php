<?php
/**
 * @package admin
 * @copyright Copyright 2003-2018 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: Drbyte Tue Jan 16 14:28:27 2018 -0500 New in v1.5.6 $
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
     if (empty($data['error'])) return; 
     if (!defined($data['error'])) {
        $msg_array = explode('_', $data['error']);

        $str_msg = array();

        foreach ($msg_array as $key => $word) {
          $str_msg[$key] = implode('_', array_slice($msg_array, 0, $key + 1));
/*          if ($key == 0) {
            $str_msg[$key] = $word;
          } else {
            $str_msg[$key] = $str_msg[$key - 1] . '_' . $word;
          }*/
        }

        $str_count = count($str_msg);

        switch (true) {
          case ($str_count >= 3 && $str_msg[2] == 'TEXT_MIN_ADMIN'):
            $error_msg = TEXT_MIN_GENERAL_ADMIN;
            break;
          case ($str_count >= 3 && $str_msg[2] == 'TEXT_MAX_ADMIN');
            $error_msg = TEXT_MAX_GENERAL_ADMIN;
            break;
          default:
            $error_msg = TEXT_DATA_OUT_OF_RANGE;
        }
     } else { 
        $error_msg = constant($data['error']); 
     }
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
        zen_redirect(zen_href_link(FILENAME_CONFIGURATION, 'gID=' . $_GET['gID'] . '&cID=' . (int)$_GET['cID'] . '&action=edit'));
     }
     return; 
  }
