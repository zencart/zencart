<?php
/**
 * @copyright Copyright 2003-2016 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version  $Id: New in v1.6.0 $
 */
namespace ZenCart\CheckoutFlow\flowSteps;

use ZenCart\CheckoutFlow\CheckoutRedirectException;

/**
 * Class Shipping
 * @package ZenCart\CheckoutFlow\flowStates
 */
class Shipping extends AbstractFlowStep
{
    /**
     * @var string
     */
    protected $templateName = 'checkout_shipping';
    /**
     * @var string
     */
    protected $stepName = 'shipping';

    /**
     * @throws CheckoutRedirectException
     */
    public function processStep()
    {
        try {
            $this->notify('NOTIFY_HEADER_START_CHECKOUT_SHIPPING');
            $this->checkValidCart();
            $this->checkValidStock();
            $this->setCartId();
            $this->setSessionSendto();
            $this->initOrder();
            $this->checkVirtual();
            $this->setWeightCount();
            $this->initShipping();
            $this->manageFreeShipping();
            $this->manageFormSubmit();
            $this->manageShippingQuotes();
            $this->setShippingCommonLinks();
            $this->manageMainBreadcrumb();
            $this->notify('NOTIFY_HEADER_END_CHECKOUT_SHIPPING');
        } catch (CheckoutRedirectException $e) {
            $this->notify('NOTIFY_CHECKOUTFLOW_SHIPPING_EXCEPTION', array(), $e);
            throw new CheckoutRedirectException($e->getRedirectDestination());
        }
    }

    /**
     *
     */
    protected function setShippingCommonLinks()
    {
        $formActionLink = zen_href_link(FILENAME_CHECKOUT_FLOW, 'step=' . $this->stepName, 'SSL');
        $displayAddressEdit = (MAX_ADDRESS_BOOK_ENTRIES >= 2);
        $editShippingButtonLink = zen_href_link(FILENAME_CHECKOUT_SHIPPING_ADDRESS, '', 'SSL');
        if ($this->alterShippingEditButtonAllowed()) {
            $editShippingButtonLink = ${$this->session->get('payment')}->alterShippingEditButton();
            $displayAddressEdit = true;
        }
        $this->notify('NOTIFY_CHECKOUTFLOW_SHIPPING_SET_COMMON_LINKS', array(), $formActionLink, $displayAddressEdit,
            $editShippingButtonLink);
        $this->view->getTplVarManager()->set('formActionLink', $formActionLink);
        $this->view->getTplVarManager()->set('displayAddressEdit', $displayAddressEdit);
        $this->view->getTplVarManager()->set('editShippingButtonLink', $editShippingButtonLink);
    }

    /**
     * @return bool
     */
    protected function alterShippingEditButtonAllowed()
    {
        if (!$this->session->get('payment') == null) {
            return false;
        }
        if (!method_exists(${$this->session->get('payment')}, 'alterShippingEditButton')) {
            return false;
        }
        return ${$this->session->get('payment')}->alterShippingEditButton();
    }

    /**
     *
     */
    protected function setSessionSendto()
    {
        $this->notify('NOTIFY_CHECKOUTFLOW_SHIPPING_SET_SESSION_SENDTO_START');
        if ($this->session->get('sendto') === null) {
            $this->session->set('sendto', $this->session->get('customer_default_address_id'));
            $this->session->set('billto', $this->session->get('customer_default_address_id'));
            $this->notify('NOTIFY_CHECKOUTFLOW_SHIPPING_SET_SESSION_SENDTO_END');
            return;
        }
        $this->checkSendToAddressBook();
        $this->notify('NOTIFY_CHECKOUTFLOW_SHIPPING_SET_SESSION_SENDTO_END');
    }

    /**
     *
     */
    protected function checkSendToAddressBook()
    {
        $query = "SELECT count(*) AS total
                            FROM   " . TABLE_ADDRESS_BOOK . "
                            WHERE  customers_id = :customersID
                            AND    address_book_id = :addressBookID";

        $query = $this->dbConn->bindVars($query, ':customersID', $this->session->get('customer_id'),
            'integer');
        $query = $this->dbConn->bindVars($query, ':addressBookID', $this->session->get('sendto'),
            'integer');
        $result = $this->dbConn->Execute($query);

        if ($result->fields['total'] != '1') {
            $this->session->set('sendto', $this->session->get('customer_default_address_id'));
//            $this->session->set('billto', $this->session->get('customer_default_address_id'));
            $this->session->set('shipping', null);
        }
    }


    /**
     * @throws CheckoutRedirectException
     */
    protected function checkVirtual()
    {
        $this->notify('NOTIFY_CHECKOUTFLOW_SHIPPING_CHECK_VIRTUAL_START');
        if ($this->order->content_type != 'virtual') {
            return;
        }
        $sessionShipping = $this->session->get('shipping');
        $sessionShipping['id'] = 'free_free';
        $sessionShipping['title'] = 'free_free';
        $this->session->set('shipping', $sessionShipping);
        $this->session->set('sendto', false);
        throw new CheckoutRedirectException(array(
            'redirect' => zen_href_link(FILENAME_CHECKOUT_FLOW, 'step=' . $this->nextFlowStep)
        ));
        $this->notify('NOTIFY_CHECKOUTFLOW_SHIPPING_CHECK_VIRTUAL_END');
    }


    /**
     *
     */
    protected function manageFreeShipping()
    {
        $this->notify('NOTIFY_CHECKOUTFLOW_SHIPPING_MANAGE_FREE_SHIPPING_START');
        $free_shipping = false;
        if (defined('MODULE_ORDER_TOTAL_SHIPPING_FREE_SHIPPING') && (MODULE_ORDER_TOTAL_SHIPPING_FREE_SHIPPING == 'true')) {
            $free_shipping = $this->testFreeShippingLocation();
        }
        $GLOBALS['free_shipping'] = $free_shipping;
        $this->notify('NOTIFY_CHECKOUTFLOW_SHIPPING_MANAGE_FREE_SHIPPING_END');
    }

    /**
     * @return bool
     */
    protected function testFreeShippingLocation()
    {
        $pass = false;
        $cart = $this->session->get('cart');
        $destinationAllowed = MODULE_ORDER_TOTAL_SHIPPING_DESTINATION;
        $destinationActual = ($this->order->delivery['country_id'] == STORE_COUNTRY);
        if ($destinationAllowed == 'national' && $destinationActual) {
            $pass = true;
        }
        if ($destinationAllowed == 'international' && !$destinationActual) {
            $pass = true;
        }
        if ($destinationAllowed == 'both') {
            $pass = true;
        }
        $free_shipping = false;
        if ($pass && $cart->show_total() >= MODULE_ORDER_TOTAL_SHIPPING_FREE_SHIPPING_OVER) {
            $free_shipping = true;
        }
        return $free_shipping;
    }


    /**
     * @throws CheckoutRedirectException
     */
    protected function manageFormSubmit()
    {
        global $free_shipping;

        $skipManageFormSubmit = false;
        $this->notify('NOTIFY_CHECKOUTFLOW_SHIPPING_MANAGE_FORM_SUBMIT_START', array(), $skipManageFormSubmit);
        if ($skipManageFormSubmit) {
            return;
        }
        if ($this->request->readPost('action') != 'process' || !$this->request->readPost('shipping', false)) {
            return;
        }
        $this->manageComments();
        $this->manageNoShippingModules($free_shipping);
        $alllowFreeShipping = true;
        if (defined('MODULE_ORDER_TOTAL_SHIPPING_FREE_SHIPPING') && (MODULE_ORDER_TOTAL_SHIPPING_FREE_SHIPPING == 'true')) {
            $alllowFreeShipping = true;;
        }
        list($module, $method) = explode('_', $this->request->readPost('shipping'));
        $this->getShippingQuotes($module, $method, $free_shipping, $alllowFreeShipping);
        $this->notify('NOTIFY_CHECKOUTFLOW_SHIPPING_MANAGE_FORM_SUBMIT_END');
    }

    /**
     * @param $free_shipping
     * @throws CheckoutRedirectException
     */
    protected function manageNoShippingModules($free_shipping)
    {
        if ((zen_count_shipping_modules() == 0) && ($free_shipping == true)) {
            $this->session->set('shipping', null);
            throw new CheckoutRedirectException(array(
                'redirect' => zen_href_link(FILENAME_CHECKOUT_FLOW, 'step=' . $this->nextFlowStep)
            ));
        }
    }

    /**
     * @param $shippingModule
     * @param $shippingMethod
     * @param $free_shipping
     * @param $allowFreeShipping
     * @throws CheckoutRedirectException
     */
    protected function getShippingQuotes($shippingModule, $shippingMethod, $free_shipping, $allowFreeShipping)
    {
        global $shipping_modules;

        $quote = array();
        $sessionShipping = $this->session->get('shipping');
        $sessionShipping['id'] = $this->request->readPost('shipping');
        $shippingClass = $GLOBALS[$shippingModule];
        $this->session->set('shipping', $sessionShipping);
        $quote = $this->setShippingQuoteError($quote, $allowFreeShipping);
        if (!is_object($shippingClass) && ($this->session->get('shipping')['id'] != 'free_free')) {
            $this->session->set('shipping', null);
            return;
        }
        $quote = $this->setShippingQuoteFreeFree($quote);
        $quote = $this->setShippingQuoteNotFreeFree($quote, $shipping_modules, $shippingMethod, $shippingModule);
        if (isset($quote['error'])) {
            $this->session->set('shipping', null);
            return;
        }
        if ((isset($quote[0]['methods'][0]['title'])) && (isset($quote[0]['methods'][0]['cost']))) {
            $sessionShipping = array(
                'id' => $this->request->readPost('shipping'),
                'title' => (($free_shipping == true) ? $quote[0]['methods'][0]['title'] : $quote[0]['module'] . ' (' . $quote[0]['methods'][0]['title'] . ')'),
                'cost' => $quote[0]['methods'][0]['cost']
            );
            $this->session->set('shipping', $sessionShipping);
            throw new CheckoutRedirectException(array(
                'redirect' => zen_href_link(FILENAME_CHECKOUT_FLOW, 'step=' . $this->nextFlowStep)
            ));
        }
    }

    /**
     * @param $quote
     * @param $allowFreeShipping
     * @return mixed
     */
    protected function setShippingQuoteError($quote, $allowFreeShipping)
    {
        if ($this->request->readPost('shipping') == 'free_free' && ($this->order->content_type != 'virtual' && !$allowFreeShipping)) {
            $quote['error'] = ERROR_TEXT_INVALID_INPUT_MAKE_ANOTHER_SELECTION;
        }
        return $quote;
    }

    /**
     * @param $quote
     * @return mixed
     */
    protected function setShippingQuoteFreeFree($quote)
    {
        if ($this->session->get('shipping')['id'] == 'free_free') {
            $quote[0]['methods'][0]['title'] = FREE_SHIPPING_TITLE;
            $quote[0]['methods'][0]['cost'] = '0';
        }
        return $quote;
    }

    /**
     * @param $quote
     * @param $shipping_modules
     * @param $shippingMethod
     * @param $shippingModule
     * @return mixed
     */
    protected function setShippingQuoteNotFreeFree($quote, $shipping_modules, $shippingMethod, $shippingModule)
    {
        if ($this->session->get('shipping')['id'] != 'free_free') {
            $quote = $shipping_modules->quote($shippingMethod, $shippingModule);
        }
        return $quote;
    }

    /**
     *
     */
    protected function manageShippingQuotes()
    {
        global $shipping_modules;

        $this->notify('NOTIFY_CHECKOUTFLOW_SHIPPING_MANAGE_SHIPPING_QUOTES_START');
        $quotes = $shipping_modules->quote();
        // check that the currently selected shipping method is still valid (in case a zone restriction has disabled it, etc)
        $sessionShipping = $this->session->get('shipping');
        if (isset($sessionShipping)) {
            $this->checkShippingStillValid($quotes);
        }
        $this->view->getTplVarManager()->set('quotes', $quotes);
        if ((!isset($sessionShipping) || (!isset($sessionShipping['id']) || $sessionShipping['id'] == '') && zen_count_shipping_modules() >= 1)) {
            $this->session->set('shipping', $shipping_modules->cheapest());
        }
        $this->notify('NOTIFY_CHECKOUTFLOW_SHIPPING_MANAGE_SHIPPING_QUOTES_END');
    }

    /**
     * @param $quotes
     */
    protected function checkShippingStillValid($quotes)
    {
        $checklist = array();
        foreach ($quotes as $key => $val) {
            if ($val['methods'] == '') {
                continue;
            }
            foreach ($val['methods'] as $key2 => $method) {
                $checklist[] = $val['id'] . '_' . $method['id'];
            }
        }
        $checkval = $this->session->get('shipping')['id'];
        if (!in_array($checkval, $checklist) && $checkval != 'free_free') {
            $this->view->getMessageStack()->add('checkout_shipping', ERROR_PLEASE_RESELECT_SHIPPING_METHOD, 'error');
        }
    }
}
