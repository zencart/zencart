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
 * Class AbstractFlowState
 * @package ZenCart\CheckoutFlow\flowStates
 */
abstract class AbstractFlowStep extends \base
{
    /**
     * @var
     */
    protected $view;
    /**
     * @var
     */
    protected $dbConn;
    /**
     * @var
     */
    protected $manager;
    /**
     * @var
     */
    protected $request;
    /**
     * @var
     */
    protected $nextFlowStep;
    /**
     * @var
     */
    protected $session;

    /**
     * @param $manager
     */
    public function __construct($manager, $request, $dbConn, $view)
    {
        $this->view = $view;
        $this->dbConn = $dbConn;
        $this->manager = $manager;
        $this->request = $request;
        $this->session = $request->getSession();
        $this->nextFlowStep = $this->manager->getNextFlowStep($this->stepName);
    }

    /**
     * @throws CheckoutRedirectException
     */
    protected function checkValidCart()
    {
        $this->notify('NOTIFY_CHECKOUTFLOWSTATE_CHECK_VALID_CART_START');
        if ($this->session->get('cart')->count_contents() <= 0) {
            throw new CheckoutRedirectException(array('redirect' => zen_href_link(FILENAME_TIME_OUT)));
        }
        $this->session->set('valid_to_checkout', true);
        $this->session->get('cart')->get_products(true);
        if ($this->session->get('valid_to_checkout') == false) {
            $this->view->getMessageStack->add('header', ERROR_CART_UPDATE, 'error');
            throw new CheckoutRedirectException(array('redirect' => zen_href_link(FILENAME_SHOPPING_CART)));
        }
        $this->notify('NOTIFY_CHECKOUTFLOWSTATE_CHECK_VALID_CART_END');
    }

    /**
     * @throws CheckoutRedirectException
     */
    protected function checkValidStock()
    {
        $this->notify('NOTIFY_CHECKOUTFLOWSTATE_CHECK_VALID_STOCK_START');
        if ((STOCK_CHECK == 'true') && (STOCK_ALLOW_CHECKOUT != 'true')) {
            $products = $this->session->get('cart')->get_products();
            for ($i = 0, $n = sizeof($products); $i < $n; $i++) {
                if (zen_check_stock($products[$i]['id'], $products[$i]['quantity'])) {
                    throw new CheckoutRedirectException(array('redirect' => zen_href_link(FILENAME_SHOPPING_CART)));
                    break;
                }
            }
        }
        $this->notify('NOTIFY_CHECKOUTFLOWSTATE_CHECK_VALID_STOCK_END');
    }

    /**
     *
     */
    protected function initOrder()
    {
        $this->notify('NOTIFY_CHECKOUTFLOWSTATE_INIT_ORDER_START');
        $GLOBALS['order'] = $this->order = new \order; //@todo remove globals
        $this->notify('NOTIFY_CHECKOUTFLOWSTATE_INIT_ORDER_END');
    }

    /**
     *
     */
    protected function manageComments()
    {
        $this->notify('NOTIFY_CHECKOUTFLOWSTATE_MANAGE_COMMENTS_START');
        $this->session->set('comments', zen_db_prepare_input($this->request->readPost('comments')));
        if ($this->session->get('comments', '') != '') {
            $GLOBALS['comments'] = $this->session->get('comments');
        }
        $this->notify('NOTIFY_CHECKOUTFLOWSTATE_MANAGE_COMMENTS_END');
    }

    /**
     *
     */
    protected function setWeightCount()
    {
        $this->notify('NOTIFY_CHECKOUTFLOWSTATE_SET_WEIGHT_COUNT_START');
        $GLOBALS['total_weight'] = $this->session->get('cart')->show_weight();
        $GLOBALS['total_count'] = $this->session->get('cart')->count_contents();
        $this->notify('NOTIFY_HECKOUTFLOWSTATE_SET_WEIGHT_COUNT_END');
    }

    /**
     * @throws CheckoutRedirectException
     */
    protected function setCartId()
    {
        $this->notify('NOTIFY_CHECKOUTFLOW_SHIPPING_SET_CART_ID_START');
        if ($this->session->get('cart', false)->cartID === false) {
            throw new CheckoutRedirectException(array('redirect' => zen_href_link(FILENAME_TIME_OUT)));
        }

        if ($this->session->get('cartID',
                false) === false || $this->session->get('cart')->cartID != $this->session->get('cartID')
        ) {
            $this->session->set('cartID', $this->session->get('cart')->cartID);
        }
        $this->notify('NOTIFY_CHECKOUTFLOW_SHIPPING_SET_CART_ID_END');
    }

    /**
     * @throws CheckoutRedirectException
     */
    protected function checkCartId()
    {
        $this->notify('NOTIFY_CHECKOUTFLOWSTATE_CHECK_CART_ID_START');
        if ($this->session->get('cart', false)->cartID === false) {
            return;
        }
        if ($this->session->get('cart')->cartID != $this->session->get('cartID')) {
            throw new CheckoutRedirectException(array('redirect' => zen_href_link(FILENAME_CHECKOUT_FLOW, '', 'SSL')));
        }
        $this->notify('NOTIFY_CHECKOUTFLOWSTATE_CHECK_CART_ID_END');
    }

    /**
     *
     */
    protected function initOrderTotals()
    {
        $this->notify('NOTIFY_CHECKOUTFLOWSTATE_INIT_ORDER_TOTALS_START');
        require(DIR_WS_CLASSES . 'order_total.php');
        $order_total_modules = new \order_total;
        $order_total_modules->collect_posts();
        $order_total_modules->pre_confirmation_check();
        $this->view->getTplVarManager()->set('order_total_modules', $order_total_modules);
        $this->notify('NOTIFY_CHECKOUTFLOWSTATE_INIT_ORDER_TOTALS_END');
    }

    /**
     *
     */
    protected function initShipping($module = null)
    {
        $this->notify('NOTIFY_CHECKOUTFLOW_PAYMENT_INIT_SHIPPING_START');
        $GLOBALS['shipping_modules'] = new \shipping($module);
        $GLOBALS['comments'] = $this->session->get('comments');
        $this->notify('NOTIFY_CHECKOUTFLOW_PAYMENT_INIT_SHIPPING_END');
    }

    /**
     * @return mixed
     */
    public function getTemplateName()
    {
        $this->notify('NOTIFY_CHECKOUTFLOWSTATE_GET_TEMPLATE_NAME');
        return $this->templateName;
    }

    /**
     *
     */
    protected function manageMainBreadcrumb()
    {
        $this->view->getBreadcrumb()->add(NAVBAR_TITLE_1, zen_href_link(FILENAME_CHECKOUT_FLOW, '', 'SSL'));
        $this->view->getBreadcrumb()->add(NAVBAR_TITLE_2);
    }

    /**
     * @return mixed
     */
    public function getManager()
    {
        return $this->manager;
    }

    public function getStateName()
    {
        return $this->stateName;
    }

    public function getViewStep()
    {
        if (isset($this->viewStepName)) {
            return $this->viewStepName;
        }
        return $this->stepName;
    }

    public function getView()
    {
        return $this->view;
    }
}
