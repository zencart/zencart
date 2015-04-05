<?php
/**
 * Class LeadTaxClassesRoutes
 *
 * @copyright Copyright 2003-2015 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version GIT: $Id: Author: Ian Wilson  Fri Aug 17 17:42:37 2012 +0100 New in v1.5.1 $
 */
namespace ZenCart\Admin\Services;

/**
 * Class LeadTaxClassesRoutes
 * @package ZenCart\Admin\Services
 */
class LeadTaxClassesRoutes extends LeadRoutes
{
    /**
     * @return array
     */
    public function deleteCheck()
    {

        $passed = true;
        $errorMessage = "";
        $sql = "SELECT tax_class_id FROM " . TABLE_TAX_RATES . " WHERE tax_class_id= :id:";
        $sql = $this->dbConn->bindVars($sql, ':id:', $this->request->readPost('id'),
            $this->outputLayout ['fields'] [$this->listingQuery ['mainTable']['fkeyFieldLeft']] ['bindVarsType']);
        $result = $this->dbConn->Execute($sql);
        if ($result->RecordCount() > 0) {
            $passed = false;
            $errorMessage = ERROR_TAX_RATE_EXISTS_FOR_CLASS;
        }
        $sql = "SELECT count(*) AS count FROM " . TABLE_PRODUCTS . " WHERE products_tax_class_id= :id:";
        $sql = $this->dbConn->bindVars($sql, ':id:', $this->request->readPost('id'),
            $this->outputLayout ['fields'] [$this->listingQuery ['mainTable']['fkeyFieldLeft']] ['bindVarsType']);
        $result = $this->dbConn->Execute($sql);
        if ($result->fields ['count'] > 0) {
            $passed = false;
            $errorMessage = sprintf(ERROR_TAX_RATE_EXISTS_FOR_PRODUCTS, $result->fields ['count']);
        }
        return array(
            $passed,
            $errorMessage
        );
    }

    /**
     * @return array
     */
    public function multiDeleteCheck()
    {
        $passed = true;
        $errorMessage = "";
        $mainTableFkeyField = $this->listingQuery['mainTable']['fkeyFieldLeft'];
        $idList = implode(',', $this->request->readPost('selected'));
        $sql = "SELECT tax_class_id FROM " . TABLE_TAX_RATES . " WHERE tax_class_id IN (:idList:)";
        $bindVarType = 'inConstruct' . ucfirst($this->outputLayout ['fields'] [$mainTableFkeyField]['bindVarsType']);
        $sql = $this->dbConn->bindVars($sql, ':idList:', $idList, $bindVarType);
        $result = $this->dbConn->Execute($sql);
        if ($result->RecordCount() > 0) {
            $passed = false;
            $errorMessage = ERROR_TAX_RATE_EXISTS_FOR_CLASS_MULTI;
        }
        $sql = "SELECT count(*) AS count FROM " . TABLE_PRODUCTS . " WHERE products_tax_class_id IN (:idList:)";
        $sql = $this->dbConn->bindVars($sql, ':idList:', $idList, $bindVarType);
        $result = $this->dbConn->Execute($sql);
        if ($result->fields ['count'] > 0) {
            $passed = false;
            $errorMessage = sprintf(ERROR_TAX_RATE_EXISTS_FOR_PRODUCTS_MULTI, $result->fields ['count']);
        }
        return array(
            $passed,
            $errorMessage
        );
    }
}
