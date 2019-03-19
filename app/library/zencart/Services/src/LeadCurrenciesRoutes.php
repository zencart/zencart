<?php
/**
 * Class LeadCurrenciesRoutes
 *
 * @copyright Copyright 2003-2015 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version GIT: $Id: Author: Ian Wilson  Fri Aug 17 17:42:37 2012 +0100 New in v1.5.1 $
 */
namespace ZenCart\Services;

/**
 * Class LeadCurrenciesRoutes
 * @package ZenCart\Services
 */
class LeadCurrenciesRoutes extends LeadRoutes
{
    /**
     *
     */
    public function updateExecute()
    {
        if ($this->request->has('entry_field_setAsDefault', 'post') && $this->request->has('entry_field_code', 'post')) {
            $this->updateDefaultConfigurationSetting('DEFAULT_CURRENCY', $this->request->readPost('entry_field_code'));
        }
        parent::updateExecute();
    }

    /**
     *
     */
    public function insertExecute()
    {
        if ($this->request->has('entry_field_setAsDefault', 'post') && $this->request->has('entry_field_code', 'post')) {
            $this->updateDefaultConfigurationSetting('DEFAULT_CURRENCY', $this->request->readPost('entry_field_code'));
        }
        parent::insertExecute();
    }

    /**
     * @return array
     */
    public function deleteCheck()
    {
        $retVal = array(true, '');
        $sql = "SELECT code FROM " . TABLE_CURRENCIES . " WHERE currencies_id = :id:";
        $sql = $this->dbConn->bindVars($sql, ':id:', $this->request->readPost('id'), 'integer');
        $result = $this->dbConn->execute($sql);
        if ($result->fields ['code'] == DEFAULT_CURRENCY) {
            $retVal = array(false, ERROR_REMOVE_DEFAULT_CURRENCY);
        }
        return $retVal;
    }

    /**
     * @return array
     */
    public function multiDeleteCheck()
    {
        $retVal = array(true, '');
        $sql = "SELECT currencies_id FROM " . TABLE_CURRENCIES . " WHERE code = :code:";
        $sql = $this->dbConn->bindVars($sql, ':code:', DEFAULT_CURRENCY, 'integer');
        $result = $this->dbConn->execute($sql);
        if (in_array($result->fields ['currencies_id'], $this->request->readPost('selected'))) {
            $retVal = array(false, ERROR_REMOVE_DEFAULT_CURRENCY_MULTI);
        }
        return $retVal;
    }
}
