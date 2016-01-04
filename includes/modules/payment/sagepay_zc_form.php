<?php
/**
 * sagepay form
 *
 * @package paymentMethod
 * @copyright Copyright 2003-2015 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @copyright Portions Copyright Nixak
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: Author: Wilt New in v1.5.5 $
 */
require_once(DIR_FS_CATALOG . DIR_WS_MODULES . 'payment/sagepay_zc/SagepayBasket.php');
require_once(DIR_FS_CATALOG . DIR_WS_MODULES . 'payment/sagepay_zc/SagepayCustomer.php');
require_once(DIR_FS_CATALOG . DIR_WS_MODULES . 'payment/sagepay_zc/SagepayUtil.php');
require_once(DIR_FS_CATALOG . DIR_WS_MODULES . 'payment/sagepay_zc/sagepay_zc_payment.php');

/**
 * Class sagepay_form
 */
class sagepay_zc_form extends sagepay_zc_payment
{

    /**
     * @var array
     */
    protected $sagepayResponse;

    /**
     *
     */
    public function __construct()
    {
        $this->code = 'sagepay_zc_form';
        parent::__construct();
        $this->form_action_url = 'https://live.sagepay.com/gateway/service/vspform-register.vsp';
        if (MODULE_PAYMENT_SAGEPAY_ZC_FORM_TEST_STATUS == 'test') {
            $this->form_action_url = 'https://test.sagepay.com/gateway/service/vspform-register.vsp';
        }
        if (!function_exists('mcrypt_encrypt')) $this->enabled = false;
    }

    /**
     * @return string
     */
    public function process_button()
    {
        $sid = zen_session_name() . '=' . zen_session_id();
        $formEntries = $this->buildStandardTransactionDetails();
        $formEntries['SuccessURL'] = str_replace('&amp;', '&', zen_href_link(FILENAME_CHECKOUT_PROCESS, $sid, 'SSL', false));
        $formEntries['FailureURL'] = str_replace('&amp;', '&', zen_href_link(FILENAME_CHECKOUT_PROCESS, $sid, 'SSL', false));
        $processButtonString = SagepayUtil::processCryptEntries($formEntries);

        $crypt = SagepayUtil::encryptAndEncode($processButtonString, MODULE_PAYMENT_SAGEPAY_ZC_FORM_PASSWORD);

        $transaction_type = strtoupper(MODULE_PAYMENT_SAGEPAY_ZC_FORM_TXTYPE);
        $this->errorLog(array(
            array('title' => 'Transaction Type', 'content' => $transaction_type),
            array('title' => 'Submit Data', 'content' => $processButtonString)
        ));

        $process_button_string = zen_draw_hidden_field('VPSProtocol', self::SP_PROTOCOL_VERSION) .
            zen_draw_hidden_field('TxType', $transaction_type) .
            zen_draw_hidden_field('Vendor', MODULE_PAYMENT_SAGEPAY_ZC_FORM_VENDOR_NAME) .
            zen_draw_hidden_field('ReferrerID', 'BB5F9F0D-8982-4203-AFD4-AF78017E4B92') .
            zen_draw_hidden_field('Crypt', $crypt);
        return $process_button_string;
    }

    /**
     *
     */
    public function before_process()
    {
        global $messageStack;
        $sagepay_return_data = SagepayUtil::decodeAndDecrypt($_GET['crypt'], MODULE_PAYMENT_SAGEPAY_ZC_FORM_PASSWORD);
        $this->errorLog(array(
            array('title' => 'Response Data', 'content' => $sagepay_return_data)
        ));
        $this->sagepayResponse = SagepayUtil::getResponseTokens($sagepay_return_data);

        $status = $this->sagepayResponse['Status'];
        if (in_array($status, array('OK', 'REGISTERED', 'AUTHENTICATED'))) {
            return;
        }
        $error_message = $this->getResponseErrorMessage($this->sagepayResponse['Status']);
        $payment_error_return = 'ERROR ' . sprintf($error_message, $this->sagepayResponse['StatusDetail']);
        $this->errorLog(array(
            array('title' => 'Response Values', 'content' => implode("\n", $this->sagepayResponse))
        ));
        $messageStack->add_session('checkout_payment', $payment_error_return, 'error');
        zen_redirect(zen_href_link(FILENAME_CHECKOUT_PAYMENT, '', 'SSL', true, false));
    }

    /**
     * @param $zf_order_id
     */
    public function after_order_create($zf_order_id)
    {
        global $db;
        $transactionData = $this->sagepayResponse;
        $sagepayTransaction = array();
        $sagepayTransaction[] = array('fieldName' => 'vpstxid', 'value' => $transactionData['VPSTxId'], 'type' => 'string');
        $sagepayTransaction[] = array('fieldName' => 'zen_order_id', 'value' => $zf_order_id, 'type' => 'integer');
        $sagepayTransaction[] = array('fieldName' => 'api_type', 'value' => $this->code, 'type' => 'string');
        $sagepayTransaction[] = array('fieldName' => 'vendor_tx_code', 'value' => $transactionData['VendorTxCode'], 'type' => 'string');
        $sagepayTransaction[] = array('fieldName' => 'transaction_status', 'value' => $transactionData['Status'], 'type' => 'string');
        $sagepayTransaction[] = array('fieldName' => 'status_detail', 'value' => issetorArray($transactionData, 'StatusDetail', ''), 'type' => 'string');
        $sagepayTransaction[] = array('fieldName' => 'tx_auth_no', 'value' => issetorArray($transactionData, 'TxAuthNo', ''), 'type' => 'string');
        $sagepayTransaction[] = array('fieldName' => 'avs_cv2', 'value' => issetorArray($transactionData, 'AVSCV2', ''), 'type' => 'string');
        $sagepayTransaction[] = array('fieldName' => 'address_result', 'value' => issetorArray($transactionData, 'AddressResult', ''), 'type' => 'string');
        $sagepayTransaction[] = array('fieldName' => 'postcode_result', 'value' => issetorArray($transactionData, 'PostCodeResult', ''), 'type' => 'string');
        $sagepayTransaction[] = array('fieldName' => 'cv2_result', 'value' => issetorArray($transactionData, 'CV2Result', ''), 'type' => 'string');
        $sagepayTransaction[] = array('fieldName' => 'three_d_secure_status', 'value' => issetorArray($transactionData, '3DSecureStatus', ''), 'type' => 'string');
        $sagepayTransaction[] = array('fieldName' => 'cavv_result', 'value' => issetorArray($transactionData, 'CAVV', ''), 'type' => 'string');
        $sagepayTransaction[] = array('fieldName' => 'card_type', 'value' => issetorArray($transactionData, 'CardType', ''), 'type' => 'string');
        $sagepayTransaction[] = array('fieldName' => 'last_4_digits', 'value' => issetorArray($transactionData, 'Last4Digits', ''), 'type' => 'string');
        $sagepayTransaction[] = array('fieldName' => 'address_status', 'value' => issetorArray($transactionData, 'AddressStatus', ''), 'type' => 'string');
        $sagepayTransaction[] = array('fieldName' => 'payer_status', 'value' => issetorArray($transactionData, 'PayerStatus', ''), 'type' => 'string');
        $db->perform(TABLE_SAGEPAY_TRANSACTION, $sagepayTransaction);
    }
}
