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
class zcObserverCheckoutFlowEmailOnly extends base
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
        if ($this->isEmailOnlyEnabled()) {
            $this->attach($this, array('NOTIFY_CHECKOUTFLOW_GET_INITIAL_STEP'));
            $this->attach($this, array('NOTIFY_CHECKOUTFLOW_DOUSERVALIDATE_START'));
            $this->attach($this, array('NOTIFY_INIT_HEADER_CHECK_COUNTRY'));
            $this->attach($this, array('NOTIFY_CHECKOUTFLOW_CONFIRMATION_CHECK_SHIPPING_START'));
            $this->attach($this, array('NOTIFY_CHECKOUTFLOW_CONFIRMATION_INIT_PAYMENT_START'));
            $this->attach($this, array('NOTIFY_CHECKOUTFLOW_PROCESS_INIT_PAYMENT_START'));
            $this->attach($this, array('NOTIFY_CHECKOUTFLOW_FINISHED_ORDER_SET'));
            $this->attach($this, array('NOTIFY_CHECKOUT_MANAGER_DETERMINE_INITIAL_FLOW'));
        }
    }

    /**
     * @param $class
     * @param $eventID
     * @param array $paramsArray
     * @param $skip
     */
    public function updateNotifyInitHeaderCheckCountry(&$class, $eventID, $paramsArray = array(), &$skip)
    {
        $skip = true;;
    }

    /**
     * @param $class
     * @param $eventID
     * @param array $paramsArray
     * @param $skip
     */
    public function updateNotifyCheckoutflowProcessInitPaymentStart(&$class, $eventID, $paramsArray = array(), &$skip)
    {
        global $credit_covers;
        $this->request->getSession()->set('payment', '');
        $credit_covers = true;
        $skip = true;
    }

    /**
     * @param $class
     * @param $eventID
     * @param array $paramsArray
     * @param $step
     */
    public function updateNotifyCheckoutflowGetInitialStep(&$class, $eventID, $paramsArray = array(), &$step)
    {
        $step = 'emailOnly';
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
        $modeErrorLink = zen_href_link(FILENAME_CHECKOUT_FLOW);
        if ($this->request->readGet('step', 'emailOnly') == 'emailOnly') {
            $this->request->getSession()->set('shipping', null);
            $skip = true;
        }
    }

    /**
     * @param $class
     * @param $eventID
     * @param array $paramsArray
     * @param $skip
     */
    public function updateNotifyCheckoutflowConfirmationInitPaymentStart(&$class, $eventID, $paramsArray = array(), &$skip)
    {
        $skip = true;
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
//        }
    }

    /**
     * @param $class
     * @param $eventID
     * @param array $paramsArray
     * @param $skip
     */
    public function updateNotifyCheckoutflowConfirmationCheckShippingStart(&$class, $eventID, $paramsArray = array(), &$skip)
    {
        $skip = true;
    }

    /**
     * @param $class
     * @param $eventID
     * @param array $paramsArray
     * @param $order
     */
    public function updateNotifyCheckoutflowFinishedOrderSet(&$class, $eventID, $paramsArray = array(), &$order)
    {
        $order->info['shipping_method'] = TEXT_EMAIL_ONLY_SHIPPING_METHOD;
        $order->info['payment_method'] = TEXT_EMAIL_ONLY_PAYMENT_METHOD;
    }

    /**
     * @param $class
     * @param $eventID
     * @param array $paramsArray
     * @param $flow
     */
    public function updateNotifyCheckoutManagerDetermineInitialFlow(&$class, $eventID, $paramsArray = array(), &$flow)
    {
        $customerType = $this->request->getSession()->get('customerType', false);
        if ($this->isEmailOnlyEnabled() || $customerType == 'emailOnly') {
            $flow = 'emailOnly';
        }
    }

    /**
     * @return bool
     */
    protected function isEmailOnlyEnabled()
    {
        $customerType = $this->request->getSession()->get('customerType', false);
        $sessionCart = $this->request->getSession()->get('cart');
        if ($customerType == 'emailOnly' && $this->request->readGet('step') == 'finished') {
            return true;
        }
        if (GUEST_CHECKOUT_ALLOWED == 'false' || GUEST_ALLOW_EMAIL_ONLY == 'false') {
            return false;
        }

        if (!isset($_SESSION['is_guest']) || $_SESSION['is_guest'] === false) {
            return false;
        }

        if ($sessionCart->get_content_type() != 'virtual') {
            return false;
        }
        if ($sessionCart->show_total() != 0) {
            return false;
        }
        return true;
    }
}
