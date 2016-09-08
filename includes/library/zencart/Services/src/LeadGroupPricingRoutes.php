<?php
/**
 * Class LeadGroupPricingRoutes
 *
 * @copyright Copyright 2003-2016 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version GIT: $Id: Author: Ian Wilson  Fri Aug 17 17:42:37 2012 +0100 New in v1.5.1 $
 */
namespace ZenCart\Services;

/**
 * Class LeadGroupPricingRoutes
 * @package ZenCart\Services
 */
class LeadGroupPricingRoutes extends LeadRoutes
{
    /**
     * @return array
     */
    public function deleteCheck()
    {
        $retVal = array(true, '');
        $sql = "SELECT count(*) as total FROM " . TABLE_CUSTOMERS . " WHERE customers_group_pricing = :id:";
        $sql = $this->dbConn->bindVars($sql, ':id:', $this->request->readPost('id'), 'integer');
        $result = $this->dbConn->execute($sql);
        if ($result->fields ['count'] !== 0) {
            $retVal = array(false, ERROR_REMOVE_LINKED_GROUP_PRICING);
        }
        return $retVal;
    }

    /**
     * @return array
     */
    /**
     * @return array
     */
    public function multiDeleteCheck()
    {
        $passed = true;
        $errorMessage = "";
        $mainTableFkeyField = $this->listingQuery['mainTable']['fkeyFieldLeft'];
        $idList = implode(',', $this->request->readPost('selected'));
        $bindVarType = 'inConstruct' . ucfirst($this->outputLayout ['fields'] [$mainTableFkeyField]['bindVarsType']);
        $sql = "SELECT count(*) AS count FROM " . TABLE_CUSTOMERS . " WHERE customers_group_pricing IN (:idList:)";
        $sql = $this->dbConn->bindVars($sql, ':idList:', $idList, $bindVarType);
        $result = $this->dbConn->Execute($sql);
        if ($result->fields ['count'] > 0) {
            $passed = false;
            $errorMessage = sprintf(ERROR_REMOVE_LINKED_GROUP_PRICING, $result->fields ['count']);
        }
        return array(
            $passed,
            $errorMessage
        );
    }
}
