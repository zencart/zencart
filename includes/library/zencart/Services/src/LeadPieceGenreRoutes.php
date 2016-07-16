<?php
/**
 * @copyright Copyright 2003-2015 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version GIT: $Id: Author: Ian Wilson  Fri Aug 17 17:42:37 2012 +0100 New in v1.5.1 $
 */
namespace ZenCart\Services;

/**
 * Class LeadPieceGenreRoutes
 * @package ZenCart\Services
 */
class LeadPieceGenreRoutes extends LeadRoutes
{
    /**
     * @return bool
     */
    public function deleteExecute()
    {
        $mainTableFkeyField = $this->listingQuery['mainTable']['fkeyFieldLeft'];

        if ($this->request->readPost('delete_linked') === 'true') {
            $sql = "SELECT products_id FROM " . TABLE_PRODUCT_PIECE_EXTRA . " WHERE piece_genre_id = :id:";
            $sql = $this->dbConn->bindVars($sql, ':id:', $this->request->readPost('id'),
                $this->outputLayout ['fields'] [$mainTableFkeyField] ['bindVarsType']);
            $results = $this->dbConn->execute($sql);
            foreach ($results as $result) {
                zen_remove_product($result['products_id']);
            }
        } else {
            $sql = "UPDATE " . TABLE_PRODUCT_PIECE_EXTRA . " SET piece_genre_id = '' WHERE piece_genre_id = :id:";
            $sql = $this->dbConn->bindVars($sql, ':id:', $this->request->readPost('id'),
                $this->outputLayout ['fields'] [$mainTableFkeyField] ['bindVarsType']);
            $this->dbConn->execute($sql);
        }
        $this->deleteTableEntry();
        return true;
    }
}
