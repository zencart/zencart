<?php
/**
 * Class LeadRecordCompanyRoutes
 *
 * @copyright Copyright 2003-2015 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version GIT: $Id: Author: Ian Wilson  Fri Aug 17 17:42:37 2012 +0100 New in v1.5.1 $
 */
namespace ZenCart\Services;

/**
 * Class LeadRecordCompanyRoutes
 * @package ZenCart\Services
 */
class LeadRecordCompanyRoutes extends LeadRoutes
{
    /**
     * @return bool
     */
    public function deleteExecute()
    {
        $mainTableFkeyField = $this->listingQuery['mainTable']['fkeyFieldLeft'];
        if ($this->request->readPost('delete_image') === 'true') {
            $sql = "SELECT record_company_image FROM " . TABLE_RECORD_COMPANY . " WHERE record_company_id = :id:";
            $sql = $this->dbConn->bindVars($sql, ':id:', $this->request->readPost('id'),
                $this->outputLayout ['fields'] [$mainTableFkeyField] ['bindVarsType']);
            $result = $this->dbConn->execute($sql);
            $imageLocation = DIR_FS_CATALOG_IMAGES . $result->fields ['record_company_image'];
            if (file_exists($imageLocation)) {
                @unlink($imageLocation);
            }
        }
        if ($this->request->readPost('delete_linked') === 'true') {
            $sql = "SELECT products_id FROM " . TABLE_PRODUCT_PIECE_EXTRA . " WHERE record_company_id = :id:";
            $sql = $this->dbConn->bindVars($sql, ':id:', $this->request->readPost('id'),
                $this->outputLayout ['fields'] [$mainTableFkeyField] ['bindVarsType']);
            $results = $this->dbConn->execute($sql);
            foreach ($results as $result) {
                zen_remove_product($result['products_id']);
            }
        } else {
            $sql = "UPDATE " . TABLE_PRODUCT_PIECE_EXTRA . " SET record_company_id = '' WHERE record_company_id = :id:";
            $sql = $this->dbConn->bindVars($sql, ':id:', $this->request->readPost('id'),
                $this->outputLayout['fields'] [$mainTableFkeyField] ['bindVarsType']);
            $this->dbConn->execute($sql);
        }
        $this->deleteTableEntry();
        return true;
    }
}
