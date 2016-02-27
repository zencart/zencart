<?php
/**
 * sagepay form
 *
 * @package paymentMethod
 * @copyright Copyright 2003-2016 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @copyright Portions Copyright Nixak
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: Author: Wilt New in v1.5.5 $
 */
require_once(DIR_FS_CATALOG . DIR_WS_MODULES . 'payment/sagepay_zc/AbstractSagepayAPI.php');

/**
 * Class sagepay_form
 */
class sagepay_zc_payment extends AbstractSagepayAPI
{

    /**
     * @var
     */
    public $code;
    /**
     * @var mixed|null
     */
    public $version;
    /**
     * @var string
     */
    public $title;
    /**
     * @var mixed|null
     */
    public $description;
    /**
     * @var bool
     */
    public $enabled;
    /**
     * @var int|mixed|null
     */
    public $order_status = 0;

    /**
     *
     */
    public function __construct()
    {
        global $order;
        $this->version = $this->getModuleDefineValue('_VNO');
        $this->title = $this->getModuleDefineValue('_CATALOG_TEXT_TITLE');
        $this->description = '';
        if ((defined('IS_ADMIN_FLAG') && IS_ADMIN_FLAG === true) || (!isset($_GET['main_page']) || $_GET['main_page'] == ''))
        {
            $this->title = sprintf($this->getModuleDefineValue('_ADMIN_TEXT_TITLE'), $this->version);
            $this->description = $this->getModuleDefineValue('_ADMIN_TEXT_DESCRIPTION');
            $new_version_details = plugin_version_check_for_updates(2049, '1.00');
            if ($new_version_details !== FALSE) {
                $this->title .= '<span class="alert">' . ' - NOTE: A NEW VERSION OF THIS PLUGIN IS AVAILABLE. <a href="' . $new_version_details['link'] . '" target="_blank">[Details]</a>' . '</span>';
            }
        }
        $this->enabled = (($this->getModuleDefineValue('_STATUS') == 'True') ? true : false);
        $this->sort_order = $this->getModuleDefineValue('_SORT_ORDER');
        if ((int)$this->getModuleDefineValue('_ORDER_STATUS_ID') > 0) {
            $this->order_status = $this->getModuleDefineValue('_ORDER_STATUS_ID');
        }
        if (is_object($order)) {
            $this->update_status();
        }
    }

    /**
     *
     */
    public function update_status()
    {
        global $order, $db;
        if ($this->enabled == false || (int)$this->getModuleDefineValue('_ZONE') == 0) {
            return;
        }
        $check_flag = false;
        $sql = "SELECT zone_id FROM " . TABLE_ZONES_TO_GEO_ZONES . " WHERE geo_zone_id = '" . (int)$this->getModuleDefineValue('_ZONE') . "' AND zone_country_id = '" . (int)$order->billing['country']['id'] . "' ORDER BY zone_id";
        $checks = $db->Execute($sql);
        foreach ($checks as $check) {
            if ($check['zone_id'] < 1) {
                $check_flag = true;
                break;
            } elseif ($check['zone_id'] == $order->billing['zone_id']) {
                $check_flag = true;
                break;
            }
        }
        if ($check_flag == false) {
            $this->enabled = false;
        }
    }


    /**
     * @return bool
     */
    public function javascript_validation()
    {
        return false;
    }

    /**
     * @return array
     */
    public function selection()
    {
        return array(
            'id' => $this->code,
            'module' => $this->title
        );
    }

    /**
     * @return bool
     */
    public function pre_confirmation_check()
    {
        return false;
    }

    /**
     * @return bool
     */
    public function confirmation()
    {
        return false;
    }

    /**
     * @return bool
     */
    public function after_process()
    {
        return false;
    }

    /**
     * @return int
     */
    public function check()
    {
        global $db;
        $apiType = strtoupper($this->code);
        if (!isset($this->_check)) {
            $sql = "SELECT configuration_value FROM " . TABLE_CONFIGURATION . " WHERE configuration_key = 'MODULE_PAYMENT_" . $apiType . "_STATUS'";
            $check_query = $db->execute($sql);
            $this->_check = $check_query->RecordCount();
        }
        return $this->_check;
    }

    /**
     *
     */
    public function install()
    {
        global $db;
        $db->Execute("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) VALUES ('Enable Sagepay Form Module', '" . $this->getModuleDefineName('_STATUS') . "', 'True', 'Do you want to accept Sagepay Form payments?', '6', '0', 'zen_cfg_select_option(array(\'True\', \'False\'), ', now())");
        $db->Execute("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) VALUES ('SagePay Vendor Name', '" . $this->getModuleDefineName('_VENDOR_NAME') . "', 'testvendor', 'Vendor Name to use with the Sagepay Form service.', '6', '1', now())");
        $db->Execute("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) VALUES ('Password', '" . $this->getModuleDefineName('_PASSWORD') . "', 'testvendor', 'Password to use with the Sagepay Form service. Normally your encyption password', '6', '2', now())");
        $db->Execute("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) VALUES ('Vendors email address', '" .  $this->getModuleDefineName('_VENDOR_EMAIL') . "', '', 'Vendors email address to use with the Sagepay Form service. Leave blank to use the Zen Cart store owners email address. If you enter a email address here MAKE SURE it is correct.', '6', '3', now())");
        $db->Execute("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) VALUES ('Transaction Mode', '" .  $this->getModuleDefineName('_TEST_STATUS') . "', 'test', 'Use Test or Live Mode?', '6', '4', 'zen_cfg_select_option(array(\'test\', \'live\'), ', now())");
        $db->Execute("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) VALUES ('Transaction Type', '" .  $this->getModuleDefineName('_TXTYPE') . "', 'Payment', 'Choose which payment type to use for all transactions, Payment is normally the default', '6', '5', 'zen_cfg_select_option(array(\'Payment\', \'Deferred\', \'Authenticate\'), ', now())");
        $db->Execute("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) VALUES ('Transaction Currency', '" .  $this->getModuleDefineName('_CURRENCY') . "', 'Default Currency', 'The currency to use for all card transactions', '6', '6', 'zen_cfg_select_option(array(\'Default Currency\', \'GBP\', \'EUR\', \'USD\'), ', now())");
        $db->Execute("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) VALUES ('Sort order of display.', '" .  $this->getModuleDefineName('_SORT_ORDER') . "', '0', 'Sort order of display. Lowest is displayed first.', '6', '7', now())");
        $db->Execute("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, use_function, set_function, date_added) VALUES ('Payment Zone', '" .  $this->getModuleDefineName('_ZONE') . "', '0', 'If a zone is selected, only enable this payment method for that zone.', '6', '8', 'zen_get_zone_class_title', 'zen_cfg_pull_down_zone_classes(', now())");
        $db->Execute("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, use_function, date_added) VALUES ('Set Order Status', '" .  $this->getModuleDefineName('_ORDER_STATUS_ID') . "', '0', 'Set the status of orders made with this payment module to this value', '6', '9', 'zen_cfg_pull_down_order_statuses(', 'zen_get_order_status_name', now())");
        $db->Execute("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) VALUES ('AVS (Address Verification) Options', '" .  $this->getModuleDefineName('_AVS') . "', 'Default', 'How should the AVS and CV2 rules for the Sage Pay Go account being used be applied? (Consult Sage Pay documentation for an explanation).', '6', '10', 'zen_cfg_select_option(array(\'Default\', \'Force With Rules\', \'Force NO Checks \', \'Force Without Rules\'), ', now())");
        $db->Execute("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) VALUES ('3D-Secure Options', '" .  $this->getModuleDefineName('_3D_SECURE') . "', 'Default', 'How should the 3D-Secure rules for the Sage Pay Go account being used be applied? (Consult Sage Pay documentation for an explanation).', '6', '11', 'zen_cfg_select_option(array(\'Default\', \'Force 3D-Secure and apply rules for authorisation.\', \'No 3D-Secure checks.\', \'Force 3D-Secure but ALWAYS obtain an auth code, irrespective of rule base.\'), ', now())");
        $db->Execute("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) VALUES ('Shopping cart', '" .  $this->getModuleDefineName('_SHOPCART') . "', 'true', 'Send shopping cart details to Sagepay?', '6', '12', 'zen_cfg_select_option(array(\'true\', \'false\'), ', now())");
        $db->Execute("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) VALUES ('Send Email', '" .  $this->getModuleDefineName('_SENDEMAIL') . "', 'both', 'Should Sagepay send order emails to the vendor only, both the vendor and the customer (default) or no email sent to either?', '6', '13', 'zen_cfg_select_option(array(\'none\', \'both\', \'vendor\'), ', now())");
        $db->Execute("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) VALUES ('Email Message', '" .  $this->getModuleDefineName('_EMAILMSG') . "', '', 'A message to the customer which is inserted into the successfull transaction e-mails only. If provided this message is included toward the top of the customer confirmation e-mails. WARNING message can only be alphanumeric and up to a max of 7500 characters long', '6', '14', 'zen_cfg_textarea(', now())");
        $db->Execute("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) VALUES ('Debug Mode', '" .  $this->getModuleDefineName('_DEBUGGING') . "', 'Off', 'Would you like to enable debug mode?', '6', '16', 'zen_cfg_select_option(array(\'Off\', \'Log File\', \'Log and Email\'), ', now())");
        $this->tryCreateTransactionTable();
    }

    /**
     *
     */
    public function remove()
    {
        global $db;
        $db->Execute("DELETE FROM " . TABLE_CONFIGURATION . " WHERE configuration_key IN ('" . implode("', '",  $this->keys()) . "')");
    }

    /**
     * @return array
     */
    public function keys()
    {
        $apiType = strtoupper($this->code);
        $keylist = array(
            'STATUS',
            'VENDOR_NAME',
            'PASSWORD',
            'VENDOR_EMAIL',
            'TEST_STATUS',
            'TXTYPE',
            'CURRENCY',
            'SORT_ORDER',
            'ZONE',
            'ORDER_STATUS_ID',
            'AVS',
            '3D_SECURE',
            'SHOPCART',
            'SENDEMAIL',
            'EMAILMSG',
            'DEBUGGING'
        );

        $keys = array();
        foreach ($keylist as $key) {
            $keyName = 'MODULE_PAYMENT_' . $apiType . '_' . $key;
            $keys[] = $keyName;
        }
        return $keys;
    }

    /**
     * @param $defineTail
     * @return mixed|null
     */
    public function getModuleDefineValue($defineTail)
    {
        $defineName = 'MODULE_PAYMENT_' . strtoupper($this->code) . $defineTail;
        if (!defined($defineName)) {
            return null;
        }
        return constant($defineName);
    }

    /**
     * @param $defineTail
     * @return string
     */
    public function getModuleDefineName($defineTail)
    {
        $defineName = 'MODULE_PAYMENT_' . strtoupper($this->code) . $defineTail;
        return $defineName;
    }

    /**
     * @param $zf_order_id
     * @return mixed
     */
    public function admin_notification($zf_order_id)
    {
        global $db;
        $sql = "SELECT * FROM " . TABLE_SAGEPAY_TRANSACTION . " WHERE zen_order_id = '" . $zf_order_id . "'";
        $sagepay_form_transaction_info = $db->Execute($sql);
        require(DIR_FS_CATALOG . DIR_WS_MODULES . 'payment/sagepay_zc/sagepay_form_admin_notification.php');
        return $output;
    }
}
/**
 * this is ONLY here to offer compatibility with ZC versions prior to v1.5.2
 */
if (!function_exists('plugin_version_check_for_updates')) {
    function plugin_version_check_for_updates($plugin_file_id = 0, $version_string_to_compare = '')
    {
        if ($plugin_file_id == 0) return FALSE;
        $new_version_available = FALSE;
        $lookup_index = 0;
        $url = 'https://plugins.zen-cart.com/versioncheck/'.(int)$plugin_file_id;

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL,$url);
        curl_setopt($ch, CURLOPT_VERBOSE, 0);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Plugin Version Check [' . (int)$plugin_file_id . '] ' . HTTP_SERVER);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);
        $error = curl_error($ch);

        if ($error > 0) {
            curl_setopt($ch, CURLOPT_URL, str_replace('tps:', 'tp:', $url));
            $response = curl_exec($ch);
            $error = curl_error($ch);
        }
        curl_close($ch);
        if ($error > 0 || $response == '') {
            $response = file_get_contents($url);
        }
        if ($response === false) {
            $response = file_get_contents(str_replace('tps:', 'tp:', $url));
        }
        if ($response === false) return false;

        $data = json_decode($response, true);

        if (!$data || !is_array($data)) return false;
        // compare versions
        if (strcmp($data[$lookup_index]['latest_plugin_version'], $version_string_to_compare) > 0) $new_version_available = TRUE;
        // check whether present ZC version is compatible with the latest available plugin version
        if (!in_array('v'. PROJECT_VERSION_MAJOR . '.' . PROJECT_VERSION_MINOR, $data[$lookup_index]['zcversions'])) $new_version_available = FALSE;
        return ($new_version_available) ? $data[$lookup_index] : FALSE;
    }
}
if (!function_exists('issetorArray')) {
    /**
     * function issetorArray
     *
     * returns an array[key] or default value if key does not exist
     *
     * @param array $array
     * @param $key
     * @param null $default
     * @return mixed
     */
    function issetorArray(array $array, $key, $default = null)
    {
        return isset($array[$key]) ? $array[$key] : $default;
    }
}
