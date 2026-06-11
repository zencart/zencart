<?php
/**
 * @copyright Copyright 2003-2025 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: DrByte 2025 Oct 03 Modified in v2.2.0 $
 */

  /**
  *   Function used for configuration checks only.
  *   @param $variable - variable to be checked
  *   @param $check_string - a json encoded array containing:
  *     error: defined constant containing error message
  *     id: id of the filter to apply. (May be mnemonic value of int.)
  *     options: per http://php.net/manual/en/function.filter-var.php
  *   @returns null for malformed requests
  *
  * @since ZC v1.5.6
  */
function zen_validate_configuration_entry(string $variable, string $check_string, string $config_name = ''): ?bool
{
    global $messageStack;

    $data = json_decode($check_string, true);

    // check inputs - error should be a defined constant in the language files
    if (empty($data['error']) || !isset($data['options']) || !is_array($data['options'])) {
        return;
    }

    $options = $data['options'];
    $config_name = zen_output_string_protected($config_name);

    if (!defined($data['error'])) {
        switch (true) {
            case str_starts_with($data['error'], 'TEXT_MIN_ADMIN'):
                $error_msg = sprintf(TEXT_MIN_GENERAL_ADMIN, $config_name, $options['options']['min_range'] ?? '0', zen_output_string_protected($variable));
                break;
            case str_starts_with($data['error'], 'TEXT_MAX_ADMIN'):
                $error_msg = sprintf(TEXT_MAX_GENERAL_ADMIN, $config_name, $options['options']['min_range'] ?? '0', zen_output_string_protected($variable));
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

    if (is_string($data['id']) && defined($data['id'])) {
        $id = (int)constant($data['id']);
    } elseif (is_int($data['id'])) {
        $id = $data['id'];
    } else {
        return;
    }

    $result = filter_var($variable, $id, $options);
    if ($result === false) {
        $messageStack->add_session($error_msg, 'error');
        return false;
    }
    return true;
}
