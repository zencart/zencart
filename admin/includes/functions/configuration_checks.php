<?php
/**
 * @copyright Copyright 2003-2022 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: lat9 2022 Jun 19 Modified in v1.5.8-alpha $
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
function zen_validate_configuration_entry($variable, $check_string, $config_name = '')
{
    global $messageStack;

    $data = json_decode($check_string, true);

    // check inputs - error should be a defined constant in the language files
    if (empty($data['error']) || !isset($data['options']) || !is_array($data['options'])) {
        return;
    }

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
                break;
        }
    } elseif ($config_name !== '') { 
        $error_msg = sprintf(constant($data['error']), $config_name); 
    } else { 
        $error_msg = constant($data['error']);
    }

    if (defined($data['id'])) {
        $id = constant($data['id']);
    } elseif (is_integer($data['id'])) {
        $id = $data['id']; 
    } else { 
        return;
    }

    $options = $data['options']; 

    $result = filter_var($variable, $id, $options);
    if ($result === false) {
        $messageStack->add_session($error_msg, 'error');
        return false;
    }
    return true;
}
