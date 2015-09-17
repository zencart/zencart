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
 * Class Finished
 * @package ZenCart\CheckoutFlow\flowStates
 */
class Finished extends AbstractFlowStep
{
    /**
     * @var string
     */
    protected $templateName = 'checkout_success';
    /**
     * @var string
     */
    protected $stepName = 'finished';

    /**
     * @throws CheckoutRedirectException
     */
    public function processStep()
    {
        try {
            $this->notify('NOTIFY_HEADER_START_CHECKOUT_SUCCESS');
            $this->managePaymentMessages();
            $this->manageProductNotify();
            $this->getLastOrder();
            $this->manageOrderSummary();
            $this->manageGlobalNotifications();
            $this->manageGiftVouchers();
            $this->manageDefinePage();
            $this->manageLogoffText();
            $this->manageDownloadsTemplate();
            $this->manageSuccessOrderLink();
            $this->manageMainBreadcrumb();
            $this->notify('NOTIFY_HEADER_END_CHECKOUT_SUCCESS');
        } catch (CheckoutRedirectException $e) {
            $this->notify('NOTIFY_CHECKOUTFLOW_FINISHED_EXCEPTION', array(), $e);
            throw new CheckoutRedirectException($e->getRedirectDestination());
        }
    }

    protected function managePaymentMessages()
    {
        $this->view->getTplVarManager()->set('hasPaymentMessages', false);
        if ($this->session->get('payment_method_messages') !== null) {
            $this->view->getTplVarManager()->set('additional_payment_messages', $this->session->get('payment_method_messages'));
            $this->view->getTplVarManager()->set('hasPaymentMessages', true);
        }
    }

    /**
     * @throws CheckoutRedirectException
     */
    protected function manageProductNotify()
    {
        $this->notify('NOTIFY_CHECKOUTFLOW_FINISHED_MANAGE_PRODUCT_NOTIFY_START');
        if ($this->request->readGet['action'] == 'update') {
            $notify_string = 'action=notify&';
            $notify = $_POST['notify'];

            if (is_array($notify)) {
                for ($i = 0, $n = sizeof($notify); $i < $n; $i++) {
                    $notify_string .= 'notify[]=' . $notify[$i] . '&';
                }
                if (strlen($notify_string) > 0) {
                    $notify_string = substr($notify_string, 0, -1);
                }
            }
            if ($notify_string == 'action=notify&') {
                throw new CheckoutRedirectException(array(
                    'redirect' => zen_href_link(FILENAME_CHECKOUT_FLOW, 'step=finished', 'SSL')
                ));
            } else {
                throw new CheckoutRedirectException(array('redirect' => zen_href_link(FILENAME_DEFAULT, $notify_string)));
            }
        }
        $this->notify('NOTIFY_CHECKOUTFLOW_FINISHED_MANAGE_PRODUCT_NOTIFY_END');
    }

    /**
     *
     */
    protected function getLastOrder()
    {
        $this->notify('NOTIFY_CHECKOUTFLOW_FINSIHED_GET_LAST_ORDER_START');
        $orders_query = "SELECT * FROM " . TABLE_ORDERS . "
                 WHERE customers_id = :customersID
                 ORDER BY date_purchased DESC LIMIT 1";
        $orders_query = $this->dbConn->bindVars($orders_query, ':customersID', $this->session->get('customer_id'), 'integer');
        $orders = $this->dbConn->Execute($orders_query);
        $orders_id = $orders->fields['orders_id'];
        $order = new \order($orders_id);
        $this->notify('NOTIFY_CHECKOUTFLOW_FINISHED_ORDER_SET', array(), $order);
        $orderNumCreated = $this->session->get('order_number_created');
        $this->view->getTplVarManager()->set('order', $order);
        $this->orderId = (isset($orderNumCreated) && $orderNumCreated >= 1) ? $orderNumCreated : $orders_id;
        $this->view->getTplVarManager()->set('orderId', $this->orderId);
        $this->notify('NOTIFY_CHECKOUTFLOW_FINSIHED_GET_LAST_ORDER_END');
    }

    /**
     *
     */
    protected function manageOrderSummary()
    {
        $this->notify('NOTIFY_CHECKOUTFLOW_FINISHED_MANAGE_ORDER_SUMMARY_START');
        $order_summary = $this->session->get('order_summary');
        $this->view->getTplVarManager()->set('order_summary', $order_summary);
        $this->session->set('order_summary', null);
        $this->session->set('order_number_created', null);
        $this->notify('NOTIFY_CHECKOUTFLOW_FINISHED_MANAGE_ORDER_SUMMARY_END');
    }

    /**
     *
     */
    protected function manageGlobalNotifications()
    {
        $this->notify('NOTIFY_CHECKOUTFLOW_MANAGE_GLOBAL_NOTIFICATIONS_START');
        $global_query = "SELECT global_product_notifications
                 FROM " . TABLE_CUSTOMERS_INFO . "
                 WHERE customers_info_id = :customersID";

        $global_query = $this->dbConn->bindVars($global_query, ':customersID', $this->session->get('customer_id'), 'integer');
        $global = $this->dbConn->Execute($global_query);
        $flag_global_notifications = $global->fields['global_product_notifications'];
        if ($flag_global_notifications != '1') {
            $notificationsArray = array();
            $counter = 0;
            $products_query = "SELECT products_id, products_name
                     FROM " . TABLE_ORDERS_PRODUCTS . "
                     WHERE orders_id = :ordersID
                     ORDER BY products_name";

            $products_query = $this->dbConn->bindVars($products_query, ':ordersID', $this->orderId,
                'integer');
            $products = $this->dbConn->Execute($products_query);
            while (!$products->EOF) {
                $notificationsArray[] = array(
                    'counter' => $counter,
                    'products_id' => $products->fields['products_id'],
                    'products_name' => $products->fields['products_name']
                );
                $counter++;
                $products->MoveNext();
            }
        }
        $flag_show_products_notification = (CUSTOMERS_PRODUCTS_NOTIFICATION_STATUS == '1' && sizeof($notificationsArray) > 0 and $flag_global_notifications != '1') ? true : false;
        $this->view->getTplVarManager()->set('flag_show_products_notification', $flag_show_products_notification);
        $this->view->getTplVarManager()->set('notificationsArray', $notificationsArray);
        $this->notify('NOTIFY_CHECKOUTFLOW_MANAGE_GLOBAL_NOTIFICATIONS_END');
    }

    /**
     *
     */
    protected function manageGiftVouchers()
    {
        global $currencies;
        $this->notify('NOTIFY_CHECKOUTFLOW_FINISHED_MANAGE_GIFT_VOUCHERS_START');
        $customer_gv_balance = false;
        $gv_query = "SELECT amount
               FROM " . TABLE_COUPON_GV_CUSTOMER . "
               WHERE customer_id = :customersID ";

        $gv_query = $this->dbConn->bindVars($gv_query, ':customersID', $this->session->get('customer_id'), 'integer');
        $gv_result = $this->dbConn->Execute($gv_query);
        if ($gv_result->fields['amount'] > 0) {
            $customer_has_gv_balance = true;
            $customer_gv_balance = $currencies->format($gv_result->fields['amount']);
        }
        $this->view->getTplVarManager()->set('customer_gv_balance', $customer_gv_balance);
        $this->notify('NOTIFY_CHECKOUTFLOW_FINISHED_MANAGE_GIFT_VOUCHERS_END');
    }

    /**
     *
     */
    protected function manageDefinePage()
    {
        $this->notify('NOTIFY_CHECKOUTFLOW_SHIPPING_CHECK_VALID_STOCK_START');
        $define_page = zen_get_file_directory(DIR_WS_LANGUAGES . $this->session->get('language') . '/html_includes/',
            FILENAME_DEFINE_CHECKOUT_SUCCESS, 'false');
        $this->view->getTplVarManager()->set('define_page', $define_page);
        $this->notify('NOTIFY_CHECKOUTFLOW_SHIPPING_CHECK_VALID_STOCK_END');
    }

    /**
     *
     */
    protected function manageLogoffText()
    {
        $this->notify('NOTIFY_CHECKOUTFLOW_FINISHED_MANAGE_LOGOFF_TEXT_START');
        $logoff_text = TEXT_CHECKOUT_LOGOFF_CUSTOMER;
        $view_orders_text = TEXT_CHECKOUT_LOGOFF_CUSTOMER;
        $logoff_template = null;
        $flag_show_logoff_button = true;
        $this->view->getTplVarManager()->set('logoff_text', $logoff_text);
        $this->view->getTplVarManager()->set('logoff_template', $logoff_template);
        $this->view->getTplVarManager()->set('flag_show_logoff_button', $flag_show_logoff_button);
        $this->notify('NOTIFY_CHECKOUTFLOW_FINISHED_MANAGE_LOGOFF_TEXT_END');
    }

    /**
     *
     */
    protected function manageDownloadsTemplate()
    {
        $this->notify('NOTIFY_CHECKOUTFLOW_FINISHED_MANAGE_DOWNLOADS_TEMPLATE_START');
        $flag_show_downloads_template = (DOWNLOAD_ENABLED == 'true') ? true : false;
        $this->view->getTplVarManager()->set('flag_show_downloads_template', $flag_show_downloads_template);
        $this->notify('NOTIFY_CHECKOUTFLOW_FINISHED_MANAGE_DOWNLOADS_TEMPLATE_END');
    }

    /**
     *
     */
    protected function manageSuccessOrderLink()
    {
        global $flag_show_order_link;
        $this->notify('NOTIFY_CHECKOUTFLOW_FINISHED_MANAGE_SUCCESS_ORDER_LINK_START');
        $flag_show_order_link = true;
        $this->view->getTplVarManager()->set('flag_show_order_link', $flag_show_order_link);
        $this->notify('NOTIFY_CHECKOUTFLOW_FINISHED_MANAGE_SUCCESS_ORDER_LINK_END');
    }
}
