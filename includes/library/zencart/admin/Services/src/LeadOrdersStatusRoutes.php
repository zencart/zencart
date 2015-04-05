<?php
/**
 * Class LeadCurrenciesRoutes
 *
 * @copyright Copyright 2003-2015 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version GIT: $Id: Author: Ian Wilson  Fri Aug 17 17:42:37 2012 +0100 New in v1.5.1 $
 */
namespace ZenCart\Admin\Services;

/**
 * Class LeadCurrenciesRoutes
 * @package ZenCart\Admin\Services
 */
class LeadOrdersStatusRoutes extends LeadRoutes
{
    /**
     * @return mixed
     */
    public function insertExecute()
    {
        $sql = "SELECT max(orders_status_id) AS orders_status_id FROM " . TABLE_ORDERS_STATUS;
        $nextId = $this->dbConn->Execute($sql);
        $ordersStatusId = $nextId->fields['orders_status_id'] + 1;
        foreach ($this->request->readPost('entry_field_orders_status_name') as $language => $value) {
            $sql = "INSERT INTO " . TABLE_ORDERS_STATUS . " (orders_status_id, language_id, orders_status_name) VALUES (:id:, :languageId:, :NAME:)";
            $sql = $this->dbConn->bindVars($sql, ':id:', $ordersStatusId, 'integer');
            $sql = $this->dbConn->bindVars($sql, ':languageId:', $language, 'integer');
            $sql = $this->dbConn->bindVars($sql, ':name:', $value, 'string');
            $this->dbConn->execute($sql);
        }
        return $ordersStatusId;
    }

    /**
     * @return array
     */
    public function deleteCheck()
    {
        $retVal = array(
            true,
            ''
        );
        $sql = "SELECT count(*) AS count FROM " . TABLE_ORDERS . " WHERE orders_status = :id:";
        $sql = $this->dbConn->bindVars($sql, ':id:', $this->request->readPost('id'), 'integer');
        $orderResult = $this->dbConn->execute($sql);
        $sql = "SELECT count(*) AS count FROM " . TABLE_ORDERS_STATUS_HISTORY . " WHERE orders_status_id = :id:";
        $sql = $this->dbConn->bindVars($sql, ':id:', $this->request->readPost('id'), 'integer');
        $historyResult = $this->dbConn->execute($sql);
        if ($this->request->readPost('id') == DEFAULT_ORDERS_STATUS_ID) {
            $retVal = array(
                false,
                ERROR_REMOVE_DEFAULT_ORDER_STATUS
            );

        } elseif ($orderResult->fields['count'] > 0) {
            $retVal = array(
                false,
                ERROR_STATUS_USED_IN_ORDERS
            );
        } elseif ($historyResult->fields['count'] > 0) {
            $retVal = array(
                false,
                ERROR_STATUS_USED_IN_HISTORY
            );
        }
        return $retVal;
    }

    /**
     * @return array
     */
    public function multiDeleteCheck()
    {
        $retVal = array(
            true,
            ''
        );
        $sql = "SELECT orders_status_id  FROM " . TABLE_ORDERS_STATUS . " WHERE orders_status_id = :code:";
        $sql = $this->dbConn->bindVars($sql, ':code:', DEFAULT_ORDERS_STATUS_ID, 'string');
        $result = $this->dbConn->execute($sql);
        if (in_array($result->fields ['orders_status_id'], $this->request->readPost('selected'))) {
            $retVal = array(
                false,
                ERROR_REMOVE_DEFAULT_ORDER_STATUS
            );

            return $retVal;
        }
        $sql = "SELECT count(*) AS count FROM " . TABLE_ORDERS . " WHERE orders_status = :id:";
        $sql = $this->dbConn->bindVars($sql, ':id:', DEFAULT_ORDERS_STATUS_ID, 'integer');
        $result = $this->dbConn->execute($sql);
        if (in_array($result->fields ['orders_status_id'], $this->request->readPost('selected'))) {
            $retVal = array(
                false,
                ERROR_STATUS_USED_IN_ORDERS
            );

            return $retVal;
        }
        $sql = "SELECT count(*) AS count FROM " . TABLE_ORDERS_STATUS_HISTORY . " WHERE orders_status_id = :id:";
        $sql = $this->dbConn->bindVars($sql, ':id:', DEFAULT_ORDERS_STATUS_ID, 'integer');
        $result = $this->dbConn->execute($sql);
        if (in_array($result->fields ['orders_status_id'], $this->request->readPost('selected'))) {
            $retVal = array(
                false,
                ERROR_STATUS_USED_IN_HISTORY
            );
            return $retVal;
        }
        return $retVal;
    }
}
