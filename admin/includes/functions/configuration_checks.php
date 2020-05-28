<?php
/**
 * @copyright Copyright 2003-2020 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: Scott C Wilson 2020 May 02 Modified in v1.5.7 $
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
  function zen_validate_configuration_entry($variable, $check_string, $config_name = '') { 
     global $messageStack; 
     $data = json_decode($check_string, true); 
     // check inputs - error should be a defined constant in the language files
     if (empty($data['error'])) return; 
     if (!defined($data['error'])) {

        switch (true) {
          case (strpos($data['error'], 'TEXT_MIN_ADMIN') === 0):
            $error_msg = TEXT_MIN_GENERAL_ADMIN;
            break;
          case (strpos($data['error'], 'TEXT_MAX_ADMIN') === 0);
            $error_msg = TEXT_MAX_GENERAL_ADMIN;
            break;
          default:
            $error_msg = TEXT_DATA_OUT_OF_RANGE;
        }
     } else { 
        if (!empty($config_name)) { 
          $error_msg = sprintf(constant($data['error']), $config_name); 
        } else { 
          $error_msg = constant($data['error']); 
        }
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
        return false;
     }
     return true; 
  }
