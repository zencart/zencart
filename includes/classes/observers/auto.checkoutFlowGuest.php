<?php
/**
 * zcCheckoutFlowGuestObserver Class.
 *
 * @package classes
 * @copyright Copyright 2003-2015 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: New in v1.6.0 $
 *
 */

/**
 * Class zcObserverCheckoutFlowGuest
 */
class zcObserverCheckoutFlowGuest extends base
{
    /**
     * @var
     */
    protected $request;

    /**
     *
     */
    public function __construct()
    {
        global $zcRequest;
        $this->request = $zcRequest;
        if (GUEST_CHECKOUT_ALLOWED == 'true') {
            $this->attach($this, array('NOTIFY_LOGIN_SUCCESS'));
            $this->attach($this, array('NOTIFY_PASSWORD_FORGOTTEN_CHECK_CUSTOMER_QUERY'));
            $this->setGuestStatus();
        }
        if (GUEST_CHECKOUT_ALLOWED == 'true' && $this->request->getSession()->get('is_guest') === true) {
            $this->attach($this, array('NOTIFY_CHECKOUTFLOW_SET_INITIAL_STEPSLIST'));
            $this->attach($this, array('NOTIFY_CHECKOUTFLOW_GET_INITIAL_STEP'));
            $this->attach($this, array('NOTIFY_CHECKOUTFLOW_DOUSERVALIDATE_START'));
            $this->attach($this, array('NOTIFY_CHECKOUTFLOW_FINISHED_MANAGE_LOGOFF_TEXT_END'));
            $this->attach($this, array('NOTIFY_CHECKOUTFLOW_MANAGE_GLOBAL_NOTIFICATIONS_END'));
            $this->attach($this, array('NOTIFY_ORDER_CREATE_SET_SQL_DATA_ARRAY'));
            $this->attach($this, array('NOTIFY_ORDER_SEND_EMAIL_SET_ORDER_LINK'));
        }
    }

    /**
     * @param $class
     * @param $eventID
     * @param array $paramsArray
     */
    public function updateNotifyLoginSuccess(&$class, $eventID, $paramsArray = array())
    {
        $this->request->getSession()->set('is_guest', false);
        $this->request->getSession()->set('customerType', null);
    }

    /**
     * @param $class
     * @param $eventID
     * @param $params
     * @param $sqlQuery
     */
    public function updateNotifyPasswordForgottenCheckCustomerQuery(&$class, $eventID, $params, &$sqlQuery)
    {
        $sqlQuery .= ' AND is_guest_account != 1';
    }

    /**
     * @param $class
     * @param $eventID
     * @param array $paramsArray
     */
    public function updateNotifyCheckoutFlowSetInitialStepsList(&$class, $eventID, $paramsArray = array())
    {
        if (!isset($_SESSION['customer_id']) || $this->request->getSession()->get('is_guest') === true) {
            $stepsList = $class->getStepsList();
            array(array_unshift($stepsList, 'guest'));
            $class->setStepsList($stepsList);
        }
    }

    /**
     * @param $class
     * @param $eventID
     * @param array $paramsArray
     * @param $step
     */
    public function updateNotifyCheckoutflowGetInitialStep(&$class, $eventID, $paramsArray = array(), &$step)
    {
        if ($step == 'shipping' && $this->request->getSession()->get('customer_id') === null) {
            $step = 'guest';
        }
    }

    /**
     * @param $class
     * @param $eventID
     * @param array $paramsArray
     * @param $skip
     * @param $modeErrorLink
     */
    public function updateNotifyCheckoutflowDouservalidateStart(
        &$class,
        $eventID,
        $paramsArray = array(),
        &$skip,
        &$modeErrorLink
    ) {
        $modeErrorLink = zen_href_link(FILENAME_CHECKOUT_FLOW, 'step=guest');
        if (!$this->validateCustomerTypeForGuest()) {
            unset($_SESSION['customer_id']);
            $skip = true;
            return;
        }
        if (($this->request->readGet('step', 'guest') == 'guest')) {
            $skip = true;
        }
    }

    /**
     * @param $class
     * @param $eventID
     * @param array $paramsArray
     * @param $emailTextInvoiceText
     * @param $emailTextInvoiceUrl
     * @param $insertId
     */
    public function updateNotifyOrderSendEmailSetOrderLink(
        &$class,
        $eventID,
        $paramsArray = array(),
        &$emailTextInvoiceText,
        &$emailTextInvoiceUrl,
        $insertId
    ) {
        $emailTextInvoiceText = EMAIL_TEXT_INVOICE_URL_CLICK;;
        $emailTextInvoiceUrl = zen_href_link(FILENAME_ORDER_STATUS, 'order_id=' . $insertId, 'SSL', false);
    }

    /**
     * @param $class
     * @param $eventID
     * @param array $paramsArray
     * @param $sqlData
     */
    public function updateNotifyOrderCreateSetSqlDataArray(&$class, $eventID, $paramsArray = array(), &$sqlData)
    {
        $sqlData['is_guest_order'] = 1;
    }

    /**
     * @param $class
     * @param $eventID
     * @param array $paramsArray
     */
    public function updateNotifyCheckoutflowFinishedManageDownloadsTemplateEnd(&$class, $eventID, $paramsArray = array())
    {
        $class->getView()->getTplVarManager()->set('flag_show_downloads_template', false);
    }


    /**
     * @param $class
     * @param $eventID
     * @param array $paramsArray
     */
    public function updateNotifyCheckoutflowManageGlobalNotificationsEnd(&$class, $eventID, $paramsArray = array())
    {
        $class->getView()->getTplVarManager()->set('flag_show_products_notification', false);
    }

    /**
     * @param $class
     * @param $eventID
     * @param array $paramsArray
     */
    public function updateNotifyCheckoutflowFinishedManageLogoffTextEnd(&$class, $eventID, $paramsArray = array())
    {
        zen_session_destroy();
        $logoff_text = TEXT_CHECKOUT_LOGOFF_GUEST;
        $view_orders_text = TEXT_SEE_ORDERS_GUEST;
        $logoff_template = null;
        $flag_show_logoff_button = false;
        $class->getView()->getTplVarManager()->set('view_orders_text', $view_orders_text);
        $class->getView()->getTplVarManager()->set('logoff_text', $logoff_text);
        $class->getView()->getTplVarManager()->set('logoff_template', $logoff_template);
        $class->getView()->getTplVarManager()->set('flag_show_logoff_button', $flag_show_logoff_button);
    }

    /**
     * @param $class
     * @param $eventID
     * @param array $paramsArray
     */
    public function updateNotifyCheckoutflowFinishedManageSuccessOrderLinkEnd(&$class, $eventID, $paramsArray = array())
    {
        $class->getView()->getTplVarManager()->se('flag_show_order_link', false);
    }

    /**
     *
     */
    protected function setGuestStatus()
    {
        if ($this->request->getSession()->get('is_guest') === null) {
            $this->request->getSession()->set('is_guest', true);
        }
    }

    /**
     * @return bool
     */
    protected function validateCustomerTypeForGuest()
    {
        if ($this->request->getSession()->get('customerType') === null) {
            return true;
        }
        if ($this->request->getSession()->get('customerType') == 'standard') {
            return true;
        }
        if ($this->request->getSession()->get('customerType') == 'guest') {
            return true;
        }
        return false;
    }
}
