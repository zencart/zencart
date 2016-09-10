<?php
/**
 * Class Manager
 *
 * @copyright Copyright 2003-2015 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: $
 */

namespace ZenCart\ListingQueryAndOutput;

use ZenCart\Paginator\Paginator as Paginator;
use ZenCart\QueryBuilder\QueryBuilder as QueryBuilder;
use ZenCart\QueryBuilder\DerivedItemManager;

/**
 * Class Manager
 * @package ZenCart\ListingQueryAndOutput
 */
class Manager extends \base
{
    /**
     * @param $location
     * @param $db
     * @param $request
     */
    public function __construct($location, $modelFactory, $request)
    {
        $this->listingBoxes = array();
        $dbConn = $modelFactory->getConnection();
        $listingBoxesEnabled = $this->findListingBoxesEnabled($location, $dbConn);
        $this->notify('NOTIFY_LISTING_BOX_MANAGER_BUILDLISTINGBOXES_START');
        foreach ($listingBoxesEnabled as $listingBox => $entry) {
            $paginator = new Paginator($request);
            $derivedIemManager = new DerivedItemManager();
            $qb = new QueryBuilder($dbConn);
            $boxClass = '\\ZenCart\\ListingQueryAndOutput\\definitions\\' . $listingBox;
            $box = new $boxClass($request, $modelFactory);
            $builder = new \ZenCart\QueryBuilder\PaginatorBuilder($request, $box->getListingQuery(), $paginator);
            $box->buildResults($qb, $dbConn, $derivedIemManager, $builder->getPaginator());
            if ($box->getFormattedItemsCount() > 0) {
                $this->listingBoxes [] = $box->getTplVars();
            }
        }
        $this->notify('NOTIFY_LISTING_BOX_MANAGER_BUILDLISTINGBOXES_END');
    }

    /**
     * @param $location
     * @param $db
     * @return array
     */
    protected function findListingBoxesEnabled($location, $db)
    {
        $listingBoxesEnabled = array();
        $sql = "SELECT * FROM " . TABLE_LISTINGBOXES_TO_LISTINGBOXGROUPS . " AS l2c
            LEFT JOIN " . TABLE_LISTINGBOXGROUPS . " AS c ON  l2c.group_id = c.group_id
            LEFT JOIN " . TABLE_LISTINGBOXGROUPS_TO_LOCATIONS . " AS c2l ON c2l.group_id = c.group_id
            WHERE c2l.location_key = :locationKey: ORDER BY c2l.sort_order, l2c.sort_order";
        $sql = $db->bindVars($sql, ':locationKey:', $location, 'string');
        $results = $db->execute($sql);
        foreach ($results as $result) {
            $listingBoxesEnabled [$result ['listingbox']] = $result;
        }
        return $listingBoxesEnabled;
    }

    /**
     * @return array
     */
    public function getListingBoxes()
    {
        return $this->listingBoxes;
    }
}
