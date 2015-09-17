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
 * Class Guest
 * @package ZenCart\CheckoutFlow\flowSteps
 */
class EmailOnly extends Guest
{
    /**
     * @var string
     */
    protected $templateName = 'checkout_guest_emailonly_account_collect';
    /**
     * @var string
     */
    protected $stepName = 'emailOnly';

    protected $viewStepName = 'guest';

    /**
     *
     */
    protected function  buildFormDetailsFromDefault()
    {
        parent::buildFormDetailsFromDefault();
        $addressFields = array('company', 'firstname', 'lastname', 'street-address', 'suburb', 'postcode', 'city', 'state');
        foreach ($this->addressEntries as $key => $addressEntry) {
            $this->addressEntries[$key]['show'] = false;
        }
        $this->addressEntries['email-address'] = array('value' => '', 'show' => true, 'error' => false);
        $this->addressEntries['privacy_conditions'] = array(
            'value' => '',
            'show' => (DISPLAY_PRIVACY_CONDITIONS == 'true'),
            'error' => false
        );
        $this->addressEntries['term_conditions'] = array(
            'value' => '',
            'show' => (DISPLAY_CONDITIONS_ON_CHECKOUT == 'true'),
            'error' => false
        );

        foreach ($addressFields as $fieldName) {
            $this->addressEntries[$fieldName]['value'] = 'EMAILONLY';
        }
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
    protected function processFormSubmit()
    {
        $sqlArray = array();
        $fromRequest = array(
            array(
                'field' => 'email-address',
                'sqlMap' => 'customers_email_address',
                'type' => 'string'
            ),
            array(
                'field' => 'newsletter',
                'sqlMap' => 'customers_newsletter',
                'type' => 'integer'
            ),
            array(
                'field' => 'email_format',
                'sqlMap' => 'customers_email_format',
                'type' => 'string'
            )
        );
        foreach ($fromRequest as $entry) {
            $sqlArray[] = array(
                'value' => $this->request->readPost($entry['field']),
                'type' => $entry['type'],
                'fieldName' => $entry['sqlMap']
            );

        }
        $fromAddressEntries = array(
            array(
                'field' => 'firstname',
                'sqlMap' => 'customers_firstname',
                'type' => 'string'
            ),
            array(
                'field' => 'gender',
                'sqlMap' => 'customers_gender',
                'type' => 'string'
            ),
            array(
                'field' => 'lastname',
                'sqlMap' => 'customers_lastname',
                'type' => 'string'
            ),
            array(
                'field' => 'telephone',
                'sqlMap' => 'customers_telephone',
                'type' => 'string'
            ),
            array(
                'field' => 'fax',
                'sqlMap' => 'customers_fax',
                'type' => 'string'
            )
        );
        foreach ($fromAddressEntries as $entry) {
            $sqlArray[] = array(
                'value' => $this->addressEntries[$entry['field']]['value'],
                'type' => $entry['type'],
                'fieldName' => $entry['sqlMap']
            );
        }

        $sqlArray[] = array('value' => 0, 'type' => 'integer', 'fieldName' => 'customers_default_address_id');
        $sqlArray[] = array(
            'value' => zen_encrypt_password(zen_create_random_value(15, 'mixed')),
            'type' => 'string',
            'fieldName' => 'customers_password'
        );
        $sqlArray[] = array('value' => 1, 'type' => 'integer', 'fieldName' => 'COWOA_account');
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
            'value' => $this->addressEntries['firstname']['value'],
            'type' => 'string',
            'fieldName' => 'entry_firstname'
        );
        $sqlArray[] = array(
            'value' => $this->addressEntries['lastname']['value'],
            'type' => 'string',
            'fieldName' => 'entry_lastname'
        );
        $sqlArray[] = array(
            'value' => $this->addressEntries['street-address']['value'],
            'type' => 'string',
            'fieldName' => 'entry_street_address'
        );
        $sqlArray[] = array(
            'value' => $this->addressEntries['postcode']['value'],
            'type' => 'string',
            'fieldName' => 'entry_postcode'
        );
        $sqlArray[] = array('value' => $this->addressEntries['city']['value'], 'type' => 'string', 'fieldName' => 'entry_city');
        $sqlArray[] = array(
            'value' => $this->addressEntries['zone_country_id']['value'],
            'type' => 'integer',
            'fieldName' => 'entry_country_id'
        );
        $sqlArray[] = array(
            'value' => $this->addressEntries['gender']['value'],
            'type' => 'string',
            'fieldName' => 'entry_gender'
        );
        $sqlArray[] = array(
            'value' => $this->addressEntries['company']['value'],
            'type' => 'string',
            'fieldName' => 'entry_company'
        );
        $sqlArray[] = array(
            'value' => $this->addressEntries['suburb']['value'],
            'type' => 'string',
            'fieldName' => 'entry_suburb'
        );
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
        $this->session->set('customerType', 'emailOnly');
        $this->session->set('customer_first_name', $this->addressEntries['firstname']['value']);
        $this->session->set('customer_default_address_id', $address_id);
        $this->session->set('customer_country_id', $this->addressEntries['zone_country_id']['value']);
        $this->session->set('customer_zone_id', $this->addressEntries['state']['zone_id']);
    }

    /**
     * @return bool
     */
    protected function validateTermConditions()
    {
        if ($this->request->readPost('term_conditions') != 'on') {
            $this->view->getMessageStack()->add('no_account', ERROR_CONDITIONS_NOT_ACCEPTED, 'error');
            return true;
        }
        return false;
    }
}
