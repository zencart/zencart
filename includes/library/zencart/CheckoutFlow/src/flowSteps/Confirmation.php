<?php
/**
 * @copyright Copyright 2003-2015 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version  $Id: New in v1.6.0 $
 */

namespace ZenCart\CheckoutFlow\flowSteps;

use ZenCart\CheckoutFlow\CheckoutRedirectException;

/**
 * Class Confirmation
 * @package ZenCart\CheckoutFlow\flowStates
 */
class Confirmation extends AbstractFlowStep
{
    /**
     * @var string
     */
    protected $templateName = 'checkout_confirmation';
    /**
     * @var string
     */
    protected $stepName = 'confirmation';

    /**
     * @throws CheckoutRedirectException
     */
    public function processStep()
    {
        try {
            $this->notify('NOTIFY_HEADER_START_CHECKOUT_CONFIRMATION');
            $this->checkValidCart();
            $this->checkValidStock();
            $this->checkCartId();
            $this->checkShippingParameters();
            $this->managePostSessionPayment();
            $this->manageComments();
            $this->manageDisplayConditions();
            $this->initOrder();
            $this->initShipping($this->session->get('shipping'));
            $this->initOrderTotals();
            $this->initPayment();
            $this->manageCouponReferral();
            $this->setConfirmationCommonLinks();
            $this->manageMainBreadcrumb();
            $this->notify('NOTIFY_HEADER_END_CHECKOUT_CONFIRMATION');
        } catch (CheckoutRedirectException $e) {
            $this->notify('NOTIFY_CHECKOUTFLOW_CONFIRMATION_EXCEPTION', array(), $e);
            throw new CheckoutRedirectException($e->getRedirectDestination());
        }
    }

    /**
     *
     */
    protected function setConfirmationCommonLinks()
    {
        $sessionPayment = $this->session->get('payment');
        $actualPayment = $GLOBALS[$$sessionPayment];

        $formActionLink = zen_href_link(FILENAME_CHECKOUT_FLOW, 'step=process', 'SSL');
        if (isset($actualPayment) && isset($actualPayment->form_action_url)) {
            $formActionLink = $actualPayment->form_action_url;
        }
        $editShippingButtonLink = zen_href_link(FILENAME_CHECKOUT_FLOW, '', 'SSL');
        if (method_exists($actualPayment, 'alterShippingEditButton')) {
            $theLink = $actualPayment->alterShippingEditButton();
            if ($theLink) {
                $editShippingButtonLink = $theLink;
            }
        }
        $flagDisablePaymentAddressChange = false;
        if (isset($actualPayment->flagDisablePaymentAddressChange)) {
            $flagDisablePaymentAddressChange = $actualPayment->flagDisablePaymentAddressChange;
        }
        $this->notify('NOTIFY_CHECKOUTFLOW_CONFIRMATION_SET_COMMON_LINKS', array(), $formActionLink);
        $this->view->getTplVarManager()->set('formActionLink', $formActionLink);
        $this->view->getTplVarManager()->set('editShippingButtonLink', $editShippingButtonLink);
        $this->view->getTplVarManager()->set('flagDisablePaymentAddressChange', $flagDisablePaymentAddressChange);
    }

    /**
     * @throws CheckoutRedirectException
     */
    protected function checkShippingParameters()
    {
        $skip = false;
        $sessionShipping = $this->session->get('shipping');
        $sessionCart = $this->session->get('cart');
        $this->notify('NOTIFY_CHECKOUTFLOW_CONFIRMATION_CHECK_SHIPPING_START', array(), $skip);
        if ($skip) {
            $this->notify('NOTIFY_CHECKOUTFLOW_CONFIRMATION_CHECK_SHIPPING_END', array(), $skip);

            return;
        }
        if (!$sessionShipping) {
            throw new CheckoutRedirectException(array(
                'redirect' => zen_href_link(FILENAME_CHECKOUT_FLOW, 'step=shipping', 'SSL')
            ));
        }
        if (isset($sessionShipping['id']) && $sessionShipping['id'] == 'free_free' && $sessionCart->get_content_type() != 'virtual' && defined('MODULE_ORDER_TOTAL_SHIPPING_FREE_SHIPPING_OVER') && $sessionCart->show_total() < MODULE_ORDER_TOTAL_SHIPPING_FREE_SHIPPING_OVER) {
            throw new CheckoutRedirectException(array(
                'redirect' => zen_href_link(FILENAME_CHECKOUT_FLOW, 'step=shipping', 'SSL')
            ));
        }
        $this->notify('NOTIFY_CHECKOUTFLOW_CONFIRMATION_CHECK_SHIPPING_END', array(), $skip);
    }

    /**
     *
     */
    protected function managePostSessionPayment()
    {
        $this->notify('NOTIFY_CHECKOUTFLOW_CONFIRMATION_MANAGE_POST_SESSION_PAYMENT_START');
        if ($this->request->readPost('payment')) {
            $this->session->set('payment', $this->request->readPost('payment'));
        }
        $this->notify('NOTIFY_CHECKOUTFLOW_CONFIRMATION_MANAGE_POST_SESSION_PAYMENT_END');
    }

    /**
     *
     */
    protected function manageDisplayConditions()
    {
        if (DISPLAY_CONDITIONS_ON_CHECKOUT == 'true' && $this->request->readPost('conditions') != '1') {
            $this->view->getMessageStack()->add_session('checkout_payment', ERROR_CONDITIONS_NOT_ACCEPTED, 'error');
        }
        $this->notify('NOTIFY_CHECKOUTFLOW_CONFIRMATION_MANAGE_DISPLAY_CONDITIONS_END');
    }

    /**
     * @throws CheckoutRedirectException
     */
    protected function initPayment()
    {
        $skip = false;
        $this->notify('NOTIFY_CHECKOUTFLOW_CONFIRMATION_INIT_PAYMENT_START', array(), $skip);
        global $credit_covers;
        if ($skip) {
            return;
        }
        if (!isset($credit_covers)) {
            $credit_covers = false;
        }
        if ($credit_covers) {
            $this->session->set('payment', '');
        }
        $payment_modules = new \payment($this->session->get('payment'));
        $payment_modules->update_status();
        $actualPayment = $payment_modules->paymentClass;

        if (($this->session->get('payment') == '' || !is_object($actualPayment)) && $credit_covers === false) {
            $this->view->getMessageStack()->add_session('checkout_payment', ERROR_NO_PAYMENT_MODULE_SELECTED, 'error');
        }
        if (is_array($payment_modules->modules)) {
            $payment_modules->pre_confirmation_check();
        }
        if ($this->view->getMessageStack()->size('checkout_payment') > 0) {
            throw new CheckoutRedirectException(array(
                'redirect' => zen_href_link(FILENAME_CHECKOUT_FLOW, 'step=payment', 'SSL')
            ));
        }
        $this->view->getTplVarManager()->set('payment_modules', $payment_modules);
        $this->notify('NOTIFY_CHECKOUTFLOW_CONFIRMATION_INIT_PAYMENT_END');
    }

    /**
     *
     */
    protected function manageCouponReferral()
    {
        $this->notify('NOTIFY_CHECKOUTFLOW_CONFIRMATION_COUPON_REFERRAL_START');
        if ($this->session->get('cc_id') === null) {
            $this->notify('NOTIFY_CHECKOUTFLOW_CONFIRMATION_NO_COUPON_REFERRAL');
            return;
        }

        $discount_coupon_query = "SELECT coupon_code
                            FROM " . TABLE_COUPONS . "
                            WHERE coupon_id = :couponID";

        $discount_coupon_query = $this->dbConn->bindVars($discount_coupon_query, ':couponID', $this->session->get('cc_id'), 'integer');
        $discount_coupon = $this->dbConn->Execute($discount_coupon_query);

        $customers_referral_query = "SELECT customers_referral
                               FROM " . TABLE_CUSTOMERS . "
                               WHERE customers_id = :customersID";

        $customers_referral_query = $this->dbConn->bindVars($customers_referral_query, ':customersID', $this->session->get('customer_id'),
            'integer');
        $customers_referral = $this->dbConn->Execute($customers_referral_query);

        // only use discount coupon if set by coupon
        if ($customers_referral->fields['customers_referral'] == '' && CUSTOMERS_REFERRAL_STATUS == 1) {
            $sql = "UPDATE " . TABLE_CUSTOMERS . "
            SET customers_referral = :customersReferral
            WHERE customers_id = :customersID";

            $sql = $this->dbConn->bindVars($sql, ':customersID', $this->session->get('customer_id'), 'integer');
            $sql = $this->dbConn->bindVars($sql, ':customersReferral', $discount_coupon->fields['coupon_code'], 'string');
            $this->dbConn->Execute($sql);
        }
        $this->notify('NOTIFY_CHECKOUTFLOW_CONFIRMATION_COUPON_REFERRAL_END');
    }
}
