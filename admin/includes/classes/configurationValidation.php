<?php
/**
 * configurationValidation.php
 * @copyright Copyright 2003-2020 Zen Cart Development Team
 * @copyright Copyright 2019 mc12345678 @ mc12345678.com
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: DrByte 2020 Apr 14 New in v1.5.7 $
 *
 */

if (!defined('IS_ADMIN_FLAG')) {
    die('Illegal Access');
}


class configurationValidation extends base
{
    public function __construct() {
        if (!empty($_SESSION['language'])) {
            require_once zen_get_file_directory(DIR_WS_LANGUAGES . $_SESSION['language'] . '/', 'configuration_validation.php', 'false');
        }
    }

    static public function sanitizeEmail(&$val) {
        $results = array();
        $send_email_array = array();
        $send_to_array = array();
        $send_to_email = '';
        $send_to_name = '';
        $response = array();
        $final_result = '';
        $options = array(
                         'options' => array(
                                      'default' => false,
                                      ),
                         'flags' => '',
                         );
        
        if (isset($val)) {
            $send_to_array = explode(",", $val);
            // If count($send_to_array) > 1 then there are multiple addresses to be parsed.
            foreach ($send_to_array as $key => $address) {
                $send_to_name = '';
                $send_to_email = trim($address);
                // Collect the portion within <> symbols
                preg_match('/\<[^>]+\>/', $address, $send_email_array);
                // If there are parts to the above, then set/collect them.
                if (!empty($send_email_array)) {
                    $send_to_email = preg_replace ("/>/", "", $send_email_array[0]);
                    $send_to_email = trim(preg_replace("/</", "", $send_to_email));
                    $send_to_name  = trim(preg_replace('/\<[^*]*/', '', $address));
                }
                
                // Collect the individual name/email as part of an array.
                $results[$key]['send_to_name'] = filter_var($send_to_name, FILTER_SANITIZE_STRING, $options);
                $results[$key]['send_to_email'] = filter_var($send_to_email, FILTER_VALIDATE_EMAIL, $options);
                
                // Restore the inner email address back to its state for capture.
                if (!empty($send_email_array) && $results[$key]['send_to_email'] !== false) {
                    $results[$key]['send_to_email'] = '<' . $results[$key]['send_to_email'] . '>';
                }
                
                // If the email address is not assigned, but there is content in the name, validate that the name is a correct email address.
                if ($results[$key]['send_to_email'] === false && !empty($results[$key]['send_to_name'])) {
                    $results[$key]['send_to_name'] = filter_var($results[$key]['send_to_name'], FILTER_VALIDATE_EMAIL, $options);
                }
                
                // Remove array parameters that have failed validation.
                foreach ($results[$key] as $key2 => $value2) {
                    if (empty($value2)) {
                        unset($results[$key][$key2]);
                    }
                }
                unset($key2, $value2);
                
                // If this round of review identified no record, then move to the next record.
                if (empty($results[$key])) {
                    continue;
                }
                
                // Collect the filtered email information into a single record.
                $response[$key] = implode(" ", $results[$key]);
            }
            
            // Collect email addresses entered as a string.
            $final_result = implode(", ", $response);
        }
        
        // If there are no email addresses that are valid, then identify that failed validation.
        if (empty($final_result)) {
            return false;
        }
        
        // Provide the filtered value back as $val.
        $val = $final_result;
        
        // Set $configuration_value that is to be stored as the filtered email address.
        return $GLOBALS['configuration_value'] = $final_result;
    }
    
    
    /**
     *  Usage setting val_function  for the configuration key to something similar
     *    to the below will call on this code to support storage of the boolean related value.
     *    val_function = '{"error":"TEXT_BOOLEAN_VALIDATE","id":"FILTER_CALLBACK","options":{"options":["configurationValidation","sanitizeBoolean"]}}'
     **/
    static public function sanitizeBoolean(&$val) {
        $options = array(
                         'options' => array(
                                      'default' => null,
                                      ),
                         'flags' => FILTER_NULL_ON_FAILURE,
                         );
        
        // If the value is truly a boolean response then need to not return false, but need to update
        //   the value as false and allow the change.  If the value is not a boolean, then need
        //   to return false so that it is not permitted.
        $result = filter_var($val, FILTER_VALIDATE_BOOLEAN, $options);

        // $val is not identified as a valid Boolean type result.
        if (null === $result) {
            return false;
        }
        
        $GLOBALS['configuration_value'] = $val;
        
        // Based on processing of filter_var on FILTER_VALIDATE_BOOLEAN that result
        //   in a return of true/false for the boolean value with
        //   null if it is not boolean.
        return is_bool($result);
    }
}//end of class
