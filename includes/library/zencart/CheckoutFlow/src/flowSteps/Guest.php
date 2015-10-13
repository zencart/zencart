<?php
/**
 * @copyright Copyright 2003-2015 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version  $Id: New in v1.6.0 $
 */
namespace ZenCart\CheckoutFlow\flowSteps;

use ZenCart\CheckoutFlow\CheckoutRedirectException;
use ZenCart\CheckoutFlow\AccountFormValidator;

/**
 * Class Guest
 * @package ZenCart\CheckoutFlow\flowSteps
 */
class Guest extends AbstractFlowStep
{
    use AccountFormValidator;

    /**
     * @var string
     */
    protected $templateName = 'checkout_guest_account_collect';
    /**
     * @var string
     */
    protected $stepName = 'guest';

    /**
     * @var
     */
    protected $addressEntries;

    /**
     * @throws CheckoutRedirectException
     */
    public function processStep()
    {
        try {
            $this->notify('NOTIFY_CHECKOUTFLOW_GUEST_PROCESS_START');
            $this->checkValidCart();
            $this->checkValidStock();
            $this->setCartId();
            $this->buildFormDetails();
            $this->manageFormSubmit();
            $this->manageMainBreadcrumb();
            $this->view->getTplVarManager()->set('addressEntries', $this->addressEntries);
            $this->notify('NOTIFY_CHECKOUTFLOW_GUEST_PROCESS_END');
        } catch (CheckoutRedirectException $e) {
            $this->notify('NOTIFY_CHECKOUTFLOW_GUEST_EXCEPTION', array(), $e);
            throw new CheckoutRedirectException($e->getRedirectDestination());
        }
    }

    /**
     *
     */
    protected function  buildFormDetails()
    {
        $this->addressEntries = array();
        $this->buildFormDetailsFromDefault();
        if ($this->session->get('customer_id') !== null) {
            $this->buildFormDetailsFromCustomer();
        }
        $this->notify('NOTIFY_FLOWSTATE_GUEST_BUIDFORMDETAILS_END', array());
    }

    /**
     *
     */
    protected function  buildFormDetailsFromDefault()
    {
        $this->addressEntries['privacy_conditions'] = array(
            'value' => '',
            'show' => (DISPLAY_PRIVACY_CONDITIONS == 'true'),
            'error' => false
        );
        $this->addressEntries['zone_country_id'] = array('value' => STORE_COUNTRY, 'show' => true, 'error' => false);
        $this->addressEntries['gender'] = array('value' => '', 'show' => (ACCOUNT_GENDER == 'true'), 'error' => false);
        $this->addressEntries['company'] = array('value' => '', 'show' => (ACCOUNT_COMPANY == 'true'), 'error' => false);
        $this->addressEntries['firstname'] = array('value' => '', 'show' => true, 'error' => false);
        $this->addressEntries['lastname'] = array('value' => '', 'show' => true, 'error' => false);
        $this->addressEntries['dob'] = array('value' => '', 'show' => (ACCOUNT_DOB == 'true'), 'error' => false);
        $this->addressEntries['street-address'] = array('value' => '', 'show' => true, 'error' => false);
        $this->addressEntries['suburb'] = array('value' => '', 'show' => (ACCOUNT_SUBURB == 'true'), 'error' => false);
        $this->addressEntries['postcode'] = array('value' => '', 'show' => true, 'error' => false);
        $this->addressEntries['city'] = array('value' => '', 'show' => true, 'error' => false);
        $this->addressEntries['state'] = array(
            'value' => '',
            'show' => (ACCOUNT_STATE == 'true'),
            'showPullDown' => (ACCOUNT_STATE_DRAW_INITIAL_DROPDOWN == 'true'),
            'error' => false,
            'stateHasZones' => false,
            'stateInputError' => false,
            'zone_id' => null,
            'zone_name' => null,
            'label' => ENTRY_STATE
        );
        $this->addressEntries['telephone'] = array('value' => '', 'show' => true, 'error' => false);
        $this->addressEntries['fax'] = array('value' => '', 'show' => (ACCOUNT_FAX_NUMBER == 'true'), 'error' => false);
        $this->addressEntries['email-address'] = array('value' => '', 'show' => true, 'error' => false);
        $this->addressEntries['customers_referral'] = array(
            'value' => '',
            'show' => (CUSTOMERS_REFERRAL_STATUS == 2),
            'error' => false
        );
        $this->addressEntries['email_format'] = array(
            'value' => (ACCOUNT_EMAIL_PREFERENCE == '1' ? 'HTML' : 'TEXT'),
            'show' => true,
            'error' => false
        );
        $this->addressEntries['newsletter'] = array('value' => '', 'show' => true, 'error' => false);
    }

    /**
     *
     */
    protected function  buildFormDetailsFromCustomer()
    {
        $sql = "SELECT * FROM " . TABLE_ADDRESS_BOOK . " WHERE customers_id = :customerId: AND address_book_id = :addressBookId:";
        $sql = $this->dbConn->bindVars($sql, ':customerId:', $this->session->get('customer_id'), 'integer');
        $sql = $this->dbConn->bindVars($sql, ':addressBookId:', $this->session->get('sendto'), 'integer');
        $result = $this->dbConn->execute($sql);
        $mapping = array(
            'zone_country_id' => 'entry_country_id',
            'gender' => 'entry_gender',
            'company' => 'entry_company',
            'firstname' => 'entry_firstname',
            'lastname' => 'entry_lastname',
            'street-address' => 'entry_street_address',
            'suburb' => 'entry_suburb',
            'postcode' => 'entry_postcode',
            'city' => 'entry_city',
            'state' => 'entry_state',
        );
        foreach ($mapping as $addressEntry => $dbEntry) {
            $this->addressEntries[$addressEntry]['value'] = $result->fields[$dbEntry];
        }
        $this->addressEntries['state']['zone_id'] = $result->fields['entry_zone_id'];
        if (is_numeric($result->fields['entry_zone_id']) && $result->fields['entry_zone_id'] > 0) {
            $this->addressEntries['state']['stateHasZones'] = true;
            $this->addressEntries['state']['showPullDown'] = true;
        }
        $sql = "SELECT * FROM " . TABLE_CUSTOMERS . " WHERE customers_id = :customerId:";
        $sql = $this->dbConn->bindVars($sql, ':customerId:', $this->session->get('customer_id'), 'integer');
        $result = $this->dbConn->execute($sql);
        $mapping = array(
            'telephone' => 'customers_telephone',
            'fax' => 'customers_fax',
            'email-address' => 'customers_email_address',
            'customers_referral' => 'customers_referral',
            'email-address' => 'customers_email_address',
            'email_format' => 'customers_email_format',
            'newsletter' => 'customers_newsletter',
        );
        foreach ($mapping as $addressEntry => $dbEntry) {
            $this->addressEntries[$addressEntry]['value'] = $result->fields[$dbEntry];
        }
    }

    /**
     * @throws CheckoutRedirectException
     */
    protected function manageFormSubmit()
    {
        $inProcess = $this->request->readPost('action');
        if (is_null($inProcess) || ($inProcess != 'process')) {
            return;
        }
        $error = $this->errorProcessing();
        if ($error) {
            $this->processMessageStackErrors('no_account');
            return;
        }
        $this->processFormSubmit();
        throw new CheckoutRedirectException(array(
            'redirect' => zen_href_link(FILENAME_CHECKOUT_FLOW, 'step=' . $this->nextFlowStep, 'SSL')
        ));
    }


    /**
     *
     */
    protected function processFormSubmit()
    {
        $sqlArray = array();
        $sqlArray[] = array(
            'value' => $this->request->readPost('firstname'),
            'type' => 'string',
            'fieldName' => 'customers_firstname'
        );
        $sqlArray[] = array('value' => $this->request->readPost('gender'), 'type' => 'string', 'fieldName' => 'customers_gender');
        $sqlArray[] = array(
            'value' => $this->request->readPost('lastname'),
            'type' => 'string',
            'fieldName' => 'customers_lastname'
        );
        $sqlArray[] = array(
            'value' => $this->request->readPost('email-address'),
            'type' => 'string',
            'fieldName' => 'customers_email_address'
        );
        $sqlArray[] = array(
            'value' => $this->request->readPost('telephone'),
            'type' => 'string',
            'fieldName' => 'customers_telephone'
        );
        $sqlArray[] = array('value' => $this->request->readPost('fax'), 'type' => 'string', 'fieldName' => 'customers_fax');
        $sqlArray[] = array(
            'value' => $this->request->readPost('newsletter'),
            'type' => 'integer',
            'fieldName' => 'customers_newsletter'
        );
        $sqlArray[] = array(
            'value' => $this->request->readPost('email_format'),
            'type' => 'string',
            'fieldName' => 'customers_email_format'
        );
        $sqlArray[] = array('value' => 0, 'type' => 'integer', 'fieldName' => 'customers_default_address_id');
        $sqlArray[] = array(
            'value' => zen_encrypt_password(zen_create_random_value(15, 'mixed')),
            'type' => 'string',
            'fieldName' => 'customers_password'
        );
        $sqlArray[] = array('value' => 1, 'type' => 'integer', 'fieldName' => 'is_guest_account');
        $sqlArray[] = array(
            'value' => CUSTOMERS_APPROVAL_AUTHORIZATION,
            'type' => 'integer',
            'fieldName' => 'customers_authorization'
        );
        $this->dbConn->perform(TABLE_CUSTOMERS, $sqlArray);
        $this->session->set('customer_id', $this->dbConn->Insert_ID());
        $sqlArray = array();
        $sqlArray[] = array('value' => $this->session->get('customer_id'), 'type' => 'integer', 'fieldName' => 'customers_id');
        $sqlArray[] = array(
            'value' => $this->request->readPost('firstname'),
            'type' => 'string',
            'fieldName' => 'entry_firstname'
        );
        $sqlArray[] = array('value' => $this->request->readPost('lastname'), 'type' => 'string', 'fieldName' => 'entry_lastname');
        $sqlArray[] = array(
            'value' => $this->request->readPost('street-address'),
            'type' => 'string',
            'fieldName' => 'entry_street_address'
        );
        $sqlArray[] = array('value' => $this->request->readPost('postcode'), 'type' => 'string', 'fieldName' => 'entry_postcode');
        $sqlArray[] = array('value' => $this->request->readPost('city'), 'type' => 'string', 'fieldName' => 'entry_city');
        $sqlArray[] = array(
            'value' => $this->request->readPost('zone_country_id'),
            'type' => 'integer',
            'fieldName' => 'entry_country_id'
        );
        $sqlArray[] = array('value' => $this->request->readPost('gender'), 'type' => 'string', 'fieldName' => 'entry_gender');
        $sqlArray[] = array('value' => $this->request->readPost('company'), 'type' => 'string', 'fieldName' => 'entry_company');
        $sqlArray[] = array('value' => $this->request->readPost('suburb'), 'type' => 'string', 'fieldName' => 'entry_suburb');
        if (ACCOUNT_STATE == 'true') {
            if (isset($this->addressEntries['state']['zone_id'])) {
                $sqlArray[] = array(
                    'value' => $this->addressEntries['state']['zone_id'],
                    'type' => 'integer',
                    'fieldName' => 'entry_zone_id'
                );
                $sqlArray[] = array('value' => '', 'type' => 'string', 'fieldName' => 'entry_state');
            } else {
                $sqlArray[] = array('value' => 0, 'type' => 'integer', 'fieldName' => 'entry_zone_id');
                $sqlArray[] = array(
                    'value' => $this->request->readPost('state'),
                    'type' => 'string',
                    'fieldName' => 'entry_state'
                );
            }
        }
        $this->dbConn->perform(TABLE_ADDRESS_BOOK, $sqlArray);
        $address_id = $this->dbConn->Insert_ID();
        $sql = "update " . TABLE_CUSTOMERS . "
              set customers_default_address_id = '" . (int)$address_id . "'
              where customers_id = '" . (int)$this->session->get('customer_id') . "'";

        $this->dbConn->Execute($sql);
        $sql = "INSERT INTO " . TABLE_CUSTOMERS_INFO . "
                          (customers_info_id, customers_info_number_of_logons,
                           customers_info_date_account_created, customers_info_date_of_last_logon)
              VALUES ('" . (int)$this->session->get('customer_id') . "', '1', now(), now())";

        $this->dbConn->Execute($sql);
        if (SESSION_RECREATE == 'True') {
            zen_session_recreate();
        }
        $this->session->set('customerType', 'guest');
        $this->session->set('customer_first_name', $this->addressEntries['firstname']['value']);
        $this->session->set('customer_default_address_id', $address_id);
        $this->session->set('customer_country_id', $this->addressEntries['zone_country_id']['value']);
        $this->session->set('customer_zone_id', $this->addressEntries['state']['zone_id']);
    }

    /**
     * @param $fieldName
     * @return mixed
     */
    protected function getAddressFieldValue($fieldName)
    {
        if ($this->addressEntries[$fieldName]['show'] == false) {
            return $this->addressEntries[$fieldName]['value'];
        }
        return $this->request->readPost($fieldName);
    }
}
