<?php
/**
 * sagepay form
 *
 * @package paymentMethod
 * @copyright Copyright 2003-2015 Zen Cart Development Team
 * @copyright Portions Copyright Nixak
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: Author: Wilt New in v1.5.5 $
 */

/**
 * Class AbstractSagepayAPI
 */
class AbstractSagepayAPI extends base
{

    /**
     *
     */
    const SP_PROTOCOL_VERSION = '3.00';

    /**
     *
     */
    protected function tryCreateTransactionTable()
    {
        global $db, $sniffer;

        if (!$sniffer->table_exists(TABLE_SAGEPAY_TRANSACTION)) {
            $sql = "CREATE TABLE " . TABLE_SAGEPAY_TRANSACTION . " (
                      id INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
                      vpstxid VARCHAR(38) NOT NULL,
                      api_type VARCHAR(40) NOT NULL,
                      zen_order_id INT(11) NOT NULL,
                      security_key VARCHAR(40) NOT NULL,
                      transaction_status VARCHAR(15) NOT NULL,
                      status_detail VARCHAR(255) DEFAULT NULL,
                      vendor_tx_code VARCHAR(40) NOT NULL,
                      tx_auth_no VARCHAR(20) DEFAULT NULL,
                      avs_cv2 VARCHAR(50) DEFAULT NULL,
                      address_result VARCHAR(20) DEFAULT NULL,
                      postcode_result VARCHAR(20) DEFAULT NULL,
                      cv2_result VARCHAR(20) DEFAULT NULL,
                      three_d_secure_status VARCHAR(20) DEFAULT NULL,
                      cavv_result VARCHAR(32) DEFAULT NULL,
                      card_type VARCHAR(15) DEFAULT NULL,
                      last_4_digits VARCHAR(4) DEFAULT NULL,
                      address_status VARCHAR(20) DEFAULT NULL,
                      payer_status VARCHAR(20) DEFAULT NULL,
                      PRIMARY KEY (`id`))";
            $db->Execute($sql);
        }
        if (!$sniffer->field_exists(TABLE_SAGEPAY_TRANSACTION, 'api_type')) {
            $sql = "ALTER TABLE TABLE_SAGEPAY_TRANSACTION ADD api_type VARCHAR( 40 ) NOT NULL AFTER vpstxid";
            $db->Execute($sql);
        }

    }

    /**
     * @return int
     */
    protected function getAvsSetting()
    {
        $moduleSetting = 'MODULE_PAYMENT_' . strtoupper($this->code) . '_AVS';
        $settingArray = array("Default" => 0, "Force With Rules" => 1, "Force NO Checks." => 2, "Force Without Rules" => 3);
        return $this->getSetting($moduleSetting, $settingArray);
    }

    /**
     * @return int
     */
    protected function get3dSecureSetting()
    {
        $moduleSetting = 'MODULE_PAYMENT_' . strtoupper($this->code) . '_3D_SECURE';
        $settingArray = array(
            "Default" => 0,
            "Force 3D-Secure Rules" => 1,
            "No 3D-Secure checks" => 2,
            "Force 3D-Secure No Rules" => 3
        );
        return $this->getSetting($moduleSetting, $settingArray);
    }

    /**
     * @return int
     */
    protected function getEmailSendSettings()
    {
        $moduleSetting = 'MODULE_PAYMENT_' . strtoupper($this->code) . '_SENDEMAIL';
        $settingArray = array("none" => 0, "both" => 1, "vendor" => 2);
        return $this->getSetting($moduleSetting, $settingArray);
    }

    /**
     * @param $moduleSetting
     * @param $settingArray
     * @return int
     */
    protected function getSetting($moduleSetting, $settingArray)
    {
        if (!array_key_exists(constant($moduleSetting), $settingArray)) {
            return 0;
        }
        return $settingArray[constant($moduleSetting)];

    }

    /**
     * @return mixed
     */
    function getSagepayCurrency()
    {
        $moduleSetting = 'MODULE_PAYMENT_' . strtoupper($this->code) . '_CURRENCY';

        $sagepayCurrency = DEFAULT_CURRENCY;
        if (in_array(constant($moduleSetting), array('GPB', 'EUR', 'USD'))) {
            $sagepayCurrency = constant($moduleSetting);
        }
        return $sagepayCurrency;
    }

    /**
     * @param $status
     * @return string
     */
    function getResponseErrorMessage($status)
    {

        $error_message = constant('MODULE_PAYMENT_' . strtoupper($this->code) . '_TEXT_DECLINED_MESSAGE');
        $tryMessage = constant('MODULE_PAYMENT_' . strtoupper($this->code) . '_TEXT_' . $status . '_MESSAGE');
        if (defined($tryMessage)) {
            $error_message = $tryMessage;
        }
        return $error_message;
    }

    /**
     * @param $errorMessages
     */
    protected function errorLog($errorMessages = array())
    {
        $moduleSetting = 'MODULE_PAYMENT_' . strtoupper($this->code) . '_DEBUGGING';

        if (constant($moduleSetting) == 'Off') {
            return;
        }
        $logDir = defined('DIR_FS_LOGS') ? DIR_FS_LOGS : DIR_FS_SQL_CACHE;
        $message = date('M-d-Y h:i:s') .
            "\n=================================\n\n";
        foreach ($errorMessages as $errorMessage) {
            $message .= $errorMessage['title'] . "\n\n";
            $message .= $errorMessage['content'] . "\n\n";
            $message .= "=================================\n\n";
        }
        $file = $logDir . '/' . 'Sagepay_Debug_' . time() . '_' . zen_create_random_value(4) . '.log';
        if ($fp = @fopen($file, 'a')) {
            fwrite($fp, $message);
            fclose($fp);
        }
        if (constant($moduleSetting) !== 'Log and Email') {
            return;
        }
        zen_mail(STORE_NAME, STORE_OWNER_EMAIL_ADDRESS, 'Sagepay Form Alert ' . date('M-d-Y h:i:s'), $message, STORE_OWNER,
            STORE_OWNER_EMAIL_ADDRESS, array('EMAIL_MESSAGE_HTML' => nl2br($message)), 'debug');
    }

    /**
     * @return array
     */
    protected function buildStandardTransactionDetails()
    {
        global $order, $currencies, $customer_id, $products;

        $sagepayCurrency = $this->getSagepayCurrency();
        $us_state_codes = SagepayUtil::getUsStateCodes();
        $entries = array();
        $entries['VPSProtocol'] = self::SP_PROTOCOL_VERSION;
        $entries['ApplyAVSCV2'] = (int)$this->getAvsSetting();
        $entries['Apply3DSecure'] = (int)$this->get3dSecureSetting();
        $entries['VendorTxCode'] = date('YmdHis') . $customer_id;
        $entries['Amount'] = number_format($order->info['total'] * $currencies->get_value($sagepayCurrency),
            $currencies->get_decimal_places($sagepayCurrency));
        $entries['Currency'] = $sagepayCurrency;
        $entries['Description'] = "Goods from " . STORE_NAME;
        $entries['CustomerName'] = $order->billing['firstname'] . ' ' . $order->billing['lastname'];
        $entries['CustomerEmail'] = $order->customer['email_address'];
        $VendorEmailAddress = $this->getModuleDefineValue('_VENDOR_EMAIL');
        if ($VendorEmailAddress == '') {
            $VendorEmailAddress = STORE_OWNER_EMAIL_ADDRESS;
        }
        $entries['VendorEMail'] = $VendorEmailAddress;
        $entries['SendEMail'] = $this->getEmailSendSettings();
        $entries['eMailMessage'] = $this->getModuleDefineValue('_EMAILMSG');

        $billingEntries = sagepayCustomer::setBillingEntries($order, $us_state_codes);
        if ($this->getModuleDefineValue('_TEST_STATUS') == 'test') {
            $billingEntries['BillingAddress1'] = '88';
            $billingEntries['BillingPostCode'] = '412';
        }
        $deliveryEntries = sagepayCustomer::setDeliveryEntries($order, $us_state_codes);
        $entries = array_merge($entries, $billingEntries, $deliveryEntries);
        if ($this->getModuleDefineValue('_SHOPCART') == 'true') {
            $formEntries['Basket'] = sagepayBasket::getCartContents($order);
        }

        return $entries;
    }
}
