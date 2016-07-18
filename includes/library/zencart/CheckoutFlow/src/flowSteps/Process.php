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
 * Class Process
 * @package ZenCart\CheckoutFlow\flowStates
 */
class Process extends AbstractFlowStep
{

    /**
     * @var string
     */
    protected $templateName = 'checkout_process';
    /**
     * @var string
     */
    protected $stepName = 'process';

    /**
     * @throws CheckoutRedirectException
     */
    public function processStep()
    {
        try {
            $this->notify('NOTIFY_HEADER_START_CHECKOUT_PROCESS');
            $this->manageCreditCardSlamming();
            $this->initPayment();
            $this->initShipping($this->session->get('shipping'));
            $this->initOrder();
            $this->initOrderTotals();
            $this->managePaymentPreConfirmationCheck();
            $this->manageOrderTotalProcess();
            $this->checkCreditCovers();
            $this->managePaymentBeforeProcess();
            $this->manageOrderInsert();
            $this->managePaymentAfterOrderCreate();
            $this->manageOrderCreateAddProducts();
            $this->manageOrderSendEmails();
            $this->manageClearSlamming();
            $this->manageOrderSummary();
            $this->managePaymentAfterProcess();
            $this->manageSessionReset();
            $this->doFinishedRedirect();
            $this->notify('NOTIFY_HEADER_END_CHECKOUT_PROCESS');
        } catch (CheckoutRedirectException $e) {
            throw new CheckoutRedirectException($e->getRedirectDestination());
        }
    }

    /**
     * @throws CheckoutRedirectException
     */
    protected function manageCreditCardSlamming()
    {
        $this->notify('NOTIFY_CHECKOUTFLOW_PROCESS_MANAGE_CREDIT_CARD_SLAMMING_START');
        $paymentAttempt = $this->session->get('payment_attempt');
        if ($paymentAttempt === null) {
            $this->session->set('payment_attempt', 0);
        }
        $this->session->set('payment_attempt', $paymentAttempt+1);
        $this->notify('NOTIFY_CHECKOUT_SLAMMING_ALERT');
        if ($this->session->get('payment_attempt') > 3) {
            $this->notify('NOTIFY_CHECKOUT_SLAMMING_LOCKOUT');
            $this->session->get('cart')->reset(true);
            zen_session_destroy();
            throw new CheckoutRedirectException(array('redirect' => zen_href_link(FILENAME_TIME_OUT)));
        }
        $this->notify('NOTIFY_CHECKOUTFLOW_PROCESS_MANAGE_CREDIT_CARD_SLAMMING_END');
    }

    /**
     *
     */
    protected function initPayment()
    {
        global $credit_covers;
        $this->notify('NOTIFY_CHECKOUTFLOW_PROCESS_INIT_PAYMENT_START');
        if (!isset($credit_covers)) {
            $credit_covers = false;
        }
        // load selected payment module
        $GLOBALS['payment_modules'] = new \payment($this->session->get('payment'));
        $this->notify('NOTIFY_CHECKOUTFLOW_PROCESS_INIT_PAYMENT_END');
    }

    /**
     * @throws CheckoutRedirectException
     */
    protected function initOrder()
    {
        $this->notify('NOTIFY_CHECKOUTFLOW_PROCESS_INIT_ORDER_START');
        $GLOBALS['order'] = new \order;
        if (sizeof($GLOBALS['order']->products) < 1) {
            throw new CheckoutRedirectException(array('redirect' => zen_href_link(FILENAME_SHOPPING_CART)));
        }
        $this->notify('NOTIFY_CHECKOUTFLOW_PROCESS_INIT_ORDER_END');
    }

    /**
     *
     */
    protected function initOrderTotals()
    {
        $this->notify('NOTIFY_CHECKOUTFLOW_PROCESS_INIT_ORDER_TOTALS_START');
        $GLOBALS['order_total_modules'] = new \order_total;
        $this->notify('NOTIFY_CHECKOUTFLOW_PROCESS_INIT_ORDER_TOTALS_END');
    }

    /**
     *
     */
    protected function managePaymentPreConfirmationCheck()
    {
        global $credit_covers;
        $this->notify('NOTIFY_CHECKOUTFLOW_PROCESS_MANAGE_PAYMENT_PRECONFIRMATION_CHECK_START');
        $this->notify('NOTIFY_CHECKOUT_PROCESS_BEFORE_ORDER_TOTALS_PRE_CONFIRMATION_CHECK');
        if (strpos($GLOBALS[$this->session->get('payment')]->code, 'paypal') !== 0) {
            $GLOBALS['order_totals'] = $GLOBALS['order_total_modules']->pre_confirmation_check();
        }
        if ($credit_covers === true) {
            $GLOBALS['order']->info['payment_method'] = $GLOBALS['order']->info['payment_module_code'] = '';
        }
        $this->notify('NOTIFY_CHECKOUTFLOW_PROCESS_MANAGE_PAYMENT_PRECONFIRMATION_CHECK_END');
    }

    /**
     *
     */
    protected function manageOrderTotalProcess()
    {
        $this->notify('NOTIFY_CHECKOUTFLOW_PROCESS_MANAGE_ORDER_TOTAL_PROCESS_START');
        $this->notify('NOTIFY_CHECKOUT_PROCESS_BEFORE_ORDER_TOTALS_PROCESS');
        $GLOBALS['order_totals'] = $GLOBALS['order_total_modules']->process();
        $this->notify('NOTIFY_CHECKOUT_PROCESS_AFTER_ORDER_TOTALS_PROCESS');
        $this->notify('NOTIFY_CHECKOUTFLOW_PROCESS_MANAGE_ORDER_TOTAL_PROCESS_END');
    }

    /**
     * @throws CheckoutRedirectException
     */
    protected function checkCreditCovers()
    {
        global $credit_covers;
        $this->notify('NOTIFY_CHECKOUTFLOW_PROCESS_MANAGE_CHECK_CREDIT_COVERS_START');
        if ($this->session->get('payment') === null && $credit_covers === false) {
            throw new CheckoutRedirectException(array('redirect' => zen_href_link(FILENAME_DEFAULT)));
        }
        $this->notify('NOTIFY_CHECKOUTFLOW_PROCESS_MANAGE_CHECK_CREDIT_COVERS_END');
    }

    /**
     *
     */
    protected function managePaymentBeforeProcess()
    {
        $GLOBALS['payment_modules']->before_process();
        $this->notify('NOTIFY_CHECKOUT_PROCESS_AFTER_PAYMENT_MODULES_BEFOREPROCESS');

    }

    /**
     *
     */
    protected function manageOrderInsert()
    {
        $GLOBALS['insert_id'] = $GLOBALS['order']->create($GLOBALS['order_totals'], 2);
    }

    /**
     *
     */
    protected function managePaymentAfterOrderCreate()
    {
        $this->notify('NOTIFY_CHECKOUT_PROCESS_AFTER_ORDER_CREATE');
        $GLOBALS['payment_modules']->after_order_create($GLOBALS['insert_id']);
        $this->notify('NOTIFY_CHECKOUT_PROCESS_AFTER_PAYMENT_MODULES_AFTER_ORDER_CREATE');

    }

    /**
     *
     */
    protected function manageOrderCreateAddProducts()
    {
        $this->notify('NOTIFY_CHECKOUTFLOW_PROCESS_MANAGE_ORDER_CREATE_ADD_PRODUCTS_START');
        $GLOBALS['order']->create_add_products($GLOBALS['insert_id']);
        $this->session->set('order_number_created', $GLOBALS['insert_id']);
        $this->notify('NOTIFY_CHECKOUT_PROCESS_AFTER_ORDER_CREATE_ADD_PRODUCTS');
        $this->notify('NOTIFY_CHECKOUTFLOW_PROCESS_MANAGE_ORDER_CREATE_ADD_PRODUCTS_END');
    }

    /**
     *
     */
    protected function manageOrderSendEmails()
    {
        $this->notify('NOTIFY_CHECKOUTFLOW_PROCESS_MANAGE_ORDER_SEND_EMAILS_START');
        $GLOBALS['order']->send_order_email($GLOBALS['insert_id']);
        $this->notify('NOTIFY_CHECKOUT_PROCESS_AFTER_SEND_ORDER_EMAIL');
        $this->notify('NOTIFY_CHECKOUTFLOW_PROCESS_MANAGE_ORDER_SEND_EMAILS_END');
    }

    /**
     *
     */
    protected function manageClearSlamming()
    {
        $this->notify('NOTIFY_CHECKOUTFLOW_PROCESS_MANAGE_CLEAR_SLAMMING_START');
        if ($this->session->get('payment_attempt')) {
            $this->session->set('payment_attempt', null);
        }
        $this->notify('NOTIFY_CHECKOUTFLOW_PROCESS_MANAGE_CLEAR_SLAMMING_END');
    }

    /**
     *
     */
    protected function manageOrderSummary()
    {
        global $insert_id, $order_totals, $currencies, $order;
        $this->notify('NOTIFY_CHECKOUTFLOW_PROCESS_MANAGE_ORDER_SUMMARY_START');
        $ototal = $order_subtotal = $credits_applied = 0;
        for ($i = 0, $n = sizeof($order_totals); $i < $n; $i++) {
            if ($order_totals[$i]['code'] == 'ot_subtotal') {
                $order_subtotal = $order_totals[$i]['value'];
            }
            if ($$order_totals[$i]['code']->credit_class == true) {
                $credits_applied += $order_totals[$i]['value'];
            }
            if ($order_totals[$i]['code'] == 'ot_total') {
                $ototal = $order_totals[$i]['value'];
            }
            if ($order_totals[$i]['code'] == 'ot_tax') {
                $otax = $order_totals[$i]['value'];
            }
            if ($order_totals[$i]['code'] == 'ot_shipping') {
                $oshipping = $order_totals[$i]['value'];
            }
        }
        $commissionable_order = ($order_subtotal - $credits_applied);
        $commissionable_order_formatted = $currencies->format($commissionable_order);

        $this->session->set('order_number', $insert_id);
        $orderSummary = [];

        $orderSummary['order_number'] = $insert_id;
        $orderSummary['order_subtotal'] = $order_subtotal;
        $orderSummary['credits_applied'] = $credits_applied;
        $orderSummary['order_total'] = $ototal;
        $orderSummary['commissionable_order'] = $commissionable_order;
        $orderSummary['commissionable_order_formatted'] = $commissionable_order_formatted;
        $orderSummary['coupon_code'] = $order->info['coupon_code'];
        $orderSummary['currency_code'] = $order->info['currency'];
        $orderSummary['currency_value'] = $order->info['currency_value'];
        $orderSummary['payment_module_code'] = $order->info['payment_module_code'];
        $orderSummary['shipping_method'] = $order->info['shipping_method'];
        $orderSummary['orders_status'] = $order->info['orders_status'];
        $orderSummary['orders_status_name'] = $order->info['orders_status_name'];
        $orderSummary['tax'] = $otax;
        $orderSummary['shipping'] = $oshipping;
        $this->session->set('order_summary', $orderSummary);
        $this->notify('NOTIFY_CHECKOUT_PROCESS_HANDLE_AFFILIATES');
        $this->notify('NOTIFY_CHECKOUTFLOW_PROCESS_MANAGE_ORDER_SUMMARY_END');
    }

    /**
     *
     */
    protected function managePaymentAfterProcess()
    {
        $this->notify('NOTIFY_CHECKOUTFLOW_PROCESS_MANAGE_PAYMENT_AFTER_PROCESS_START');
        $GLOBALS['payment_modules']->after_process();
        $this->notify('NOTIFY_CHECKOUTFLOW_PROCESS_MANAGE_PAYMENT_AFTER_PROCESS_END');
    }

    /**
     *
     */
    protected function manageSessionReset()
    {
        $this->notify('NOTIFY_CHECKOUTFLOW_PROCESS_MANAGE_SESSION_RESET_START');
        $this->session->get('cart')->reset(true);
        // unregister session variables used during checkout
        $this->session->set('sendto', null);
        $this->session->set('billto', null);
        $this->session->set('shipping', null);
        $this->session->set('payment', null);
        $this->session->set('comments', null);

        $GLOBALS['order_total_modules']->clear_posts();//ICW ADDED FOR CREDIT CLASS SYSTEM
        // This should be before the zen_redirect:
        $this->notify('NOTIFY_HEADER_END_CHECKOUT_PROCESS');
        $this->notify('NOTIFY_CHECKOUTFLOW_PROCESS_MANAGE_SESSION_RESET_END');
    }

    /**
     * @throws CheckoutRedirectException
     */
    protected function doFinishedRedirect()
    {
        throw new CheckoutRedirectException(array(
            'redirect' => zen_href_link(FILENAME_CHECKOUT_FLOW,
                ($this->request->readGet('action') == 'confirm' ? 'action=confirm&step=finished' : 'step=finished'),
                'SSL')
        ));
    }
}
