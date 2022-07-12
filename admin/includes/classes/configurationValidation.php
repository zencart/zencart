<?php
/**
 * configurationValidation.php
 * @copyright Copyright 2003-2022 Zen Cart Development Team
 * @copyright Copyright 2019 mc12345678 @ mc12345678.com
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: lat9 2022 Jun 19 Modified in v1.5.8-alpha $
 *
 */

if (!defined('IS_ADMIN_FLAG')) {
    die('Illegal Access');
}

class configurationValidation extends base
{
    /**
     * Validate a configuration value that is an optional collection of email addresses.  If the value
     * input is other than an empty string, it's validated as a collection (possibly single) of email
     * address(es).
     *
     * @param string $val
     * @return bool
     */
    static public function sanitizeEmailNullOK(string $val)
    {
        if ($val === '') {
            return true;
        }
        return configurationValidation::sanitizeEmail($val, false); 
    }

    /**
     * Validate a configuration value that 'should' be an email-address representation.
     *
     * If the $single_email_only input is set to (bool)false (as it is when called from the
     * sanitizeEmailNullOK method, above), then multiple addresses can be supplied.  In this case,
     * the value is expected to be a comma-separated list of email addresses, each either in the format
     * 'email@address', e.g. joe@example.com, or 'Email Name <email@address>', e.g.
     * 'Joe Example <joe@example.com>'.
     *
     * Otherwise, the configured email address *must* be supplied as a single email address, e.g.
     * joe@example.com.
     *
     * Side-effect: Sets the global $configuration_value variable to contain the sanitized result.
     *
     * @param string $val
     * @param bool $single_email_only
     * @return bool
     */
    static public function sanitizeEmail(string $val, bool $single_email_only = true)
    {
        $final_result = '';
        $options = [
            'options' => [
                'default' => false,
            ],
        ];

        if ($val !== '') {
            if ($single_email_only === true) {
                $send_to_array = [$val];
            } else {
                $send_to_array = explode(',', $val);
            }

            $email_error = false;
            foreach ($send_to_array as $address) {
                $send_to_name = '';
                $send_to_email = trim($address);

                // -----
                // If multiple emails are allowed for the configuration ...
                //
                $email_has_parts = false;
                if ($single_email_only === false) {
                    // ----
                    // Gather any email address that's presented within a <> pair, e.g. if
                    // the value was like 'Name 1 <joe@example.com>'; the value includes the leading <
                    // and trailing >.
                    //
                    preg_match('/\<[^>]+\>/', $send_to_email, $send_email_array);

                    // If there are parts to the above, then set/collect them.
                    if (!empty($send_email_array)) {
                        $email_has_parts = true;
                        $send_to_email = trim($send_email_array[0], " <>\t\n\r\0");
                        $send_to_name  = trim(str_replace($send_email_array[0], '', $address));
                    }
                }

                // Collect the individual name/email as part of an array.
                $send_to_email = filter_var($send_to_email, FILTER_VALIDATE_EMAIL, $options);

                // If this round of review identified no record, then move to the next record.
                if ($send_to_email === false) {
                    $email_error = true;
                    continue;
                }

                // Restore the inner email address back to its state for capture.
                if ($email_has_parts === true) {
                    $send_to_email = '<' . $send_to_email . '>';
                }

                // Collect the filtered email information into a single record.
                $final_result .= $send_to_name . ' ' . $send_to_email . ', ';
            }
        }

        // If one or more of the email addresses are not valid, then identify that failed validation.
        if ($final_result === '' || $email_error === true) {
            return false;
        }

        // Provide the filtered value back as the global configuration value.
        $GLOBALS['configuration_value'] = trim($final_result, ' ,');

        return true;
    }

    /**
     *  Usage setting val_function  for the configuration key to something similar
     *    to the below will call on this code to support storage of the boolean related value.
     *    val_function = '{"error":"TEXT_BOOLEAN_VALIDATE","id":"FILTER_CALLBACK","options":{"options":["configurationValidation","sanitizeBoolean"]}}'
     **/
    static public function sanitizeBoolean(string $val)
    {
        $options = [
            'options' => [
                'default' => null,
            ],
            'flags' => FILTER_NULL_ON_FAILURE,
        ];

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
} //end of class
