<?php
/**
 * @copyright Copyright 2003-2015 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version  $Id: New in v1.6.0 $
 */

namespace ZenCart\CheckoutFlow;

/**
 * Class AccountFormValidator
 * @package ZenCart\CheckoutFlow
 */
trait AccountFormValidator
{

    abstract protected function getAddressFieldValue($fieldName);

    /**
     * @return bool|int
     */
    protected function errorProcessing()
    {
        $error = false;
        foreach ($this->addressEntries as $fieldName => $fieldDetails) {
            $this->addressEntries[$fieldName]['value'] = $this->getAddressFieldValue($fieldName);
            $fieldError = $this->processFieldValidator($fieldName, $fieldDetails);
            $this->addressEntries[$fieldName]['error'] = $fieldError;
            $error = $error | $fieldError;
        }
        return $error;
    }

    /**
     * @param $fieldName
     * @param $fieldDetails
     * @return bool
     */
    protected function processFieldValidator($fieldName, $fieldDetails)
    {
        if ($fieldDetails['show'] == false) {
            return false;
        }
        if (method_exists($this, self::camelize('validate' . ucfirst($fieldName)))) {
            $error = $this->{self::camelize('validate' . ucfirst($fieldName))}($fieldName);
        }
        return $error;
    }

    /**
     * @param $fieldName
     * @return bool
     */
    protected function validatePrivacyConditions($fieldName)
    {
        if ($this->request->readPost('privacy_conditions') != 'on') {
            $this->addressEntries[$fieldName]['errorMsg'] = ERROR_PRIVACY_STATEMENT_NOT_ACCEPTED;

            return true;
        }
        return false;
    }

    /**
     * @return bool
     */
    protected function validateGender($fieldName)
    {
        if (($this->request->readPost('gender') != 'm') && ($this->request->readPost('gender') != 'f')) {
            $this->addressEntries[$fieldName]['errorMsg'] = ENTRY_GENDER_ERROR;
            return true;
        }
        return false;
    }

    /**
     * @return bool
     */
    protected function validateCompany($fieldName)
    {
        return $this->testEntryMinLength($fieldName, ENTRY_COMPANY_MIN_LENGTH, ENTRY_COMPANY_ERROR);
    }

    /**
     * @return bool
     */
    protected function validateFirstName($fieldName)
    {
        return $this->testEntryMinLength($fieldName, ENTRY_FIRST_NAME_MIN_LENGTH, ENTRY_FIRST_NAME_ERROR);
    }

    /**
     * @param $fieldName
     * @return bool
     */
    protected function validateLastName($fieldName)
    {
        return $this->testEntryMinLength($fieldName, ENTRY_LAST_NAME_MIN_LENGTH, ENTRY_LAST_NAME_ERROR);
    }

    /**
     * @return bool
     */
    protected function validateDob($fieldName)
    {
        return false;
    }

    /**
     * @return bool
     */
    protected function validateStreetAddress($fieldName)
    {
        if (strlen($this->request->readPost('street-address')) < ENTRY_STREET_ADDRESS_MIN_LENGTH) {
            $this->addressEntries[$fieldName]['errorMsg'] = ENTRY_STREET_ADDRESS_ERROR;
            return true;
        }
        return false;
    }

    /**
     * @param $fieldName
     * @return bool
     */
    protected function validateSuburb($fieldName)
    {
        return false;
    }

    /**
     * @return bool
     */
    protected function validatePostCode($fieldName)
    {
        if (strlen($this->request->readPost('postcode')) < ENTRY_POSTCODE_MIN_LENGTH) {
            $this->addressEntries[$fieldName]['errorMsg'] = ENTRY_POST_CODE_ERROR;

            return true;
        }
        return false;
    }

    /**
     * @param $fieldName
     * @return bool
     */
    protected function validateCity($fieldName)
    {
        if (strlen($this->request->readPost('city')) < ENTRY_CITY_MIN_LENGTH) {
            $this->addressEntries[$fieldName]['errorMsg'] = ENTRY_CITY_ERROR;
            return true;
        }
        return false;
    }

    /**
     * @param $fieldName
     * @return bool
     */
    protected function validateState($fieldName)
    {
        $this->addressEntries['state']['stateInputError'] = false;
        $this->addressEntries['state']['stateHasZones'] = false;
        $this->addressEntries['state']['zone_id'] = $this->request->readPost('zone_id');
        $this->addressEntries['state']['value'] = $this->request->readPost('state');
        $sql = "SELECT count(*) AS total
                    FROM " . TABLE_ZONES . "
                    WHERE zone_country_id = :zoneCountryID";
        $sql = $this->dbConn->bindVars($sql, ':zoneCountryID', $this->request->readPost('zone_country_id'), 'integer');
        $result = $this->dbConn->Execute($sql);
        $stateHasZones = ($result->fields['total'] > 0);
        $this->addressEntries['state']['stateHasZones'] = $stateHasZones;
        if ($stateHasZones) {
            return $this->checkStateZones($fieldName);
        }
        if (!$stateHasZones && strlen($this->request->readPost('state')) < ENTRY_STATE_MIN_LENGTH) {
            $this->addressEntries['state']['stateInputError'] = true;
            $this->addressEntries[$fieldName]['errorMsg'] = ENTRY_STATE_ERROR;
            return true;
        }
        return false;
    }

    /**
     * @param $fieldName
     * @return bool
     */
    protected function checkStateZones($fieldName)
    {
        $this->addressEntries['state']['showPullDown'] = true;
        $zone = $this->getZoneFromDatabase();
        $foundExactIsoMatch = ($zone->RecordCount() == 1);
        if ($zone->RecordCount() > 1) {
            $foundExactIsoMatch = tryFindExactZoneMatch($zone, $foundExactIsoMatch);
        }
        if ($foundExactIsoMatch) {
            $this->addressEntries['state']['zone_id'] = $zone->fields['zone_id'];
            $this->addressEntries['state']['zone_name'] = $zone->fields['zone_name'];

            return false;
        }
        if (!$foundExactIsoMatch) {
            $this->addressEntries['state']['stateInputError'] = true;
            $this->addressEntries[$fieldName]['errorMsg'] = ENTRY_STATE_ERROR_SELECT;
            return true;
        }
    }

    /**
     * @return mixed
     */
    protected function getZoneFromDatabase()
    {
        $sql = "SELECT DISTINCT zone_id, zone_name, zone_code
                     FROM " . TABLE_ZONES . "
                     WHERE zone_country_id = :zoneCountryID
                     AND " .
            ((trim($this->request->readPost('state')) != '' && $this->request->readPost('zone_id') == 0) ? "(upper(zone_name) like ':zoneState%' OR upper(zone_code) like '%:zoneState%') OR " : "") .
            "zone_id = :zoneID
                     ORDER BY zone_code ASC, zone_name";

        $sql = $this->dbConn->bindVars($sql, ':zoneCountryID', $this->request->readPost('zone_country_id'), 'integer');
        $sql = $this->dbConn->bindVars($sql, ':zoneState', strtoupper($this->request->readPost('state')), 'noquotestring');
        $sql = $this->dbConn->bindVars($sql, ':zoneID', $this->request->readPost('zone_id'), 'integer');
        $zone = $this->dbConn->Execute($sql);
        return $zone;
    }

    /**
     * @param $zone
     * @param $foundExactIsoMatch
     * @return bool
     */
    protected function tryFindExactZoneMatch($zone, $foundExactIsoMatch)
    {
        while (!$zone->EOF && !$foundExactIsoMatch) {
            if (strtoupper($zone->fields['zone_code']) == strtoupper($this->request->readPost('state'))) {
                $foundExactIsoMatch = true;
                continue;
            }
            $zone->MoveNext();
        }
        return $foundExactIsoMatch;
    }

    /**
     * @return bool
     */
    protected function validateZoneCountryId($fieldName)
    {
        if ((is_numeric($this->request->readPost('zone_country_id')) == false) || ($this->request->readPost('zone_country_id') < 1)) {
            $this->addressEntries[$fieldName]['errorMsg'] = ENTRY_COUNTRY_ERROR;
            return true;
        }
        return false;
    }

    /**
     * @return bool
     */
    protected function validateTelephone($fieldName)
    {
        if (strlen($this->request->readPost('telephone')) < ENTRY_TELEPHONE_MIN_LENGTH) {
            $this->addressEntries[$fieldName]['errorMsg'] = ENTRY_TELEPHONE_NUMBER_ERROR;
            return true;
        }
        return false;
    }

    /**
     * @return bool
     */
    protected function validateFax()
    {
        return false;
    }

    /**
     * @return bool
     */
    protected function validateEmailAddress($fieldName)
    {
        if (strlen($this->request->readPost('email-address')) < ENTRY_EMAIL_ADDRESS_MIN_LENGTH) {
            $this->addressEntries[$fieldName]['errorMsg'] = ENTRY_EMAIL_ADDRESS_ERROR;
            return true;
        }
        if (zen_validate_email($this->request->readPost('email-address')) == false) {
            $this->addressEntries[$fieldName]['errorMsg'] = ENTRY_EMAIL_ADDRESS_CHECK_ERROR;
            return true;
        }
        $check_email_query = "SELECT count(*) AS total
                            FROM " . TABLE_CUSTOMERS . "
                            WHERE customers_email_address = '" . zen_db_input($this->request->readPost('email-address')) . "'
                            AND COWOA_account != 1";
        $check_email = $this->dbConn->Execute($check_email_query);

        if ($check_email->fields['total'] > 0) {
            $this->addressEntries[$fieldName]['errorMsg'] = ENTRY_EMAIL_ADDRESS_ERROR_EXISTS;
            return true;
        }
        return false;
    }

    /**
     * @param $stackTarget
     */
    protected function processMessageStackErrors($stackTarget)
    {
        foreach ($this->addressEntries as $addressEntry) {
            if ($addressEntry['show'] == false) {
                continue;
            }
            $this->view->getMessageStack()->add($stackTarget, $addressEntry['errorMsg']);
        }
    }

    protected function testEntryMinLength($fieldName, $minLengthValue, $errorText)
    {
        if ((int)$minLengthValue > 0 && strlen($this->request->readPost($fieldName)) < $minLengthValue) {
            $this->addressEntries[$fieldName]['errorMsg'] = $errorText;
            return true;
        }
        return false;
    }
}
