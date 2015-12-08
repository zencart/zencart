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
 * Class Payment
 * @package ZenCart\CheckoutFlow\flowStates
 */
class Payment extends AbstractFlowStep
{
    /**
     * @var string
     */
    protected $templateName = 'checkout_payment';
    /**
     * @var string
     */
    protected $stepName = 'payment';

    /**
     * @throws CheckoutRedirectException
     */
    public function processStep()
    {
        try {
            $this->notify('NOTIFY_HEADER_START_CHECKOUT_PAYMENT');
            $this->checkValidCart();
            $this->checkValidStock();
            $this->checkCartId();
            $this->setPaymentCommonLinks();
            $this->checkShippingParameters();
            $this->getCouponCode();
            $this->setSessionBillto();
            $this->initOrder();
            $this->initShipping($this->session->get('shipping'));
            $this->initOrderTotals();
            $this->manageComments();
            $this->setWeightCount();
            $this->initPaymentModules();
            $this->managePaymentError();
            $this->manageMainBreadcrumb();
            $this->notify('NOTIFY_HEADER_END_CHECKOUT_PAYMENT');
        } catch (CheckoutRedirectException $e) {
            $this->notify('NOTIFY_CHECKOUTFLOW_PAYMENT_EXCEPTION', array(), $e);
            throw new CheckoutRedirectException($e->getRedirectDestination());
        }
    }

    /**
     *
     */
    protected function setPaymentCommonLinks()
    {
        /**
         *
         */
        $formActionLink = zen_href_link(FILENAME_CHECKOUT_FLOW, 'step=confirmation', 'SSL');
        $this->notify('NOTIFY_CHECKOUTFLOW_PAYMENT_SET_COMMON_LINKS', array(), $formActionLink);
        $this->view->getTplVarManager()->set('formActionLink', $formActionLink);
    }

    /**
     * @throws CheckoutRedirectException
     */
    protected function checkShippingParameters()
    {
        $this->notify('NOTIFY_CHECKOUTFLOW_PAYMENT_CHECK_SHIPPING_START');
        $sessionShipping = $this->session->get('shipping');
        $sessionCart = $this->session->get('cart');
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
        $this->notify('NOTIFY_CHECKOUTFLOW_PAYMENT_CHECK_SHIPPING_END');
    }

    /**
     *
     */
    protected function getCouponCode()
    {
        $this->notify('NOTIFY_CHECKOUTFLOW_PAYMENT_GET_COUPON_CODE_START');
        if ($this->session->get('cc_id')) {
            $discount_coupon_query = "SELECT coupon_code
                            FROM " . TABLE_COUPONS . "
                            WHERE coupon_id = :couponID";

            $discount_coupon_query = $this->dbConn->bindVars($discount_coupon_query, ':couponID', $this->session->get('cc_id'), 'integer');
            $GLOBALS['discount_coupon'] = $this->dbConn->Execute($discount_coupon_query);
        }
        $this->notify('NOTIFY_CHECKOUTFLOW_PAYMENT_GET_COUPON_CODE_END');
    }

    /**
     *
     */
    protected function setSessionBillto()
    {
        $this->notify('NOTIFY_CHECKOUTFLOW_PAYMENT_GET_SESSION_BILLTO_START');
        if (!$this->session->get('billto')) {
            $$this->session->set('billto', $this->session->get('customer_default_address_id'));
        } else {
            $this->checkBillToAddressBook();

        }
        $this->notify('NOTIFY_CHECKOUTFLOW_PAYMENT_GET_SESSION_BILLTO_END');
    }

    /**
     *
     */
    protected function checkBillToAddressBook()
    {
        $query = "SELECT count(*) AS total FROM " . TABLE_ADDRESS_BOOK . "
                          WHERE customers_id = :customersID
                          AND address_book_id = :addressBookID";

        $query = $this->dbConn->bindVars($query, ':customersID', $this->session->get('customer_id'),
            'integer');
        $query = $this->dbConn->bindVars($query, ':addressBookID', $this->session->get('billto'),
            'integer');
        $result = $this->dbConn->Execute($query);

        if ($result->fields['total'] != '1') {
            $this->session->set('billto', $this->session->get('customer_default_address_id'));
            $this->session->set('payment', '');
        }
    }

    /**
     *
     */
    protected function initPaymentModules()
    {
        $this->notify('NOTIFY_CHECKOUTFLOW_PAYMENT_INIT_PAYMENT_MODULES_START');
        $payment_modules = new \payment;
        $GLOBALS['payment_modules'] = $payment_modules;
        $GLOBALS['flagOnSubmit'] = sizeof($payment_modules->selection());
        $this->notify('NOTIFY_CHECKOUTFLOW_PAYMENT_INIT_PAYMENT_MODULES_END');
    }

    /**
     *
     */
    protected function managePaymentError()
    {
        $this->notify('NOTIFY_CHECKOUTFLOW_PAYMENT_MANAGE_PAYMENT_ERROR_START');
        $paymentError = $this->request->readGet['payment_error'];
        if (isset($paymentError) && is_object($$paymentError) && ($error = $$paymentError->get_error())) {
            $this->view->getMessageStack()->add('checkout_payment', $error['error'], 'error');
        }
        $this->notify('NOTIFY_CHECKOUTFLOW_PAYMENT_MANAGE_PAYMENT_ERROR_END');
    }
}
