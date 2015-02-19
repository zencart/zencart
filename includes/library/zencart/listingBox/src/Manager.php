<?php
/**
 * Class Manager
 *
 * @copyright Copyright 2003-20145 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: $
 */
namespace ZenCart\ListingBox;
/**
 * Class Manager
 * @package ZenCart\ListingBox
 */
class Manager extends \base
{
    /**
     * @param $location
     * @param $zcDiContainer
     * @return array
     */
    public function buildListingBoxes($location, $zcDiContainer)
    {
        $listingBoxes = array();
        $listingBoxesEnabled = $this->getListingBoxesEnabled($location, $zcDiContainer);
        $this->notify('NOTIFY_LISTING_BOX_MANAGER_BUILDLISTINGBOXES_START');
        foreach ($listingBoxesEnabled as $listingBox => $entry) {
            $boxClass = '\\ZenCart\\ListingBox\\Box\\' . $listingBox;
            $box = new \ZenCart\ListingBox\Build ($zcDiContainer, new $boxClass);
            if ($box->getFormattedItemsCount() > 0) {
                $listingBoxes [] = $box->getTemplateVariables();
            }
        }
        $this->notify('NOTIFY_LISTING_BOX_MANAGER_BUILDLISTINGBOXES_END');
        return $listingBoxes;
    }

    /**
     * @param $location
     * @param $zcDiContainer
     * @return array
     */
    private function getListingBoxesEnabled($location, $zcDiContainer)
    {
        $listingBoxesEnabled = array();
        $sql = "SELECT * FROM " . TABLE_LISTINGBOXES_TO_LISTINGBOXGROUPS . " AS l2c
            LEFT JOIN " . TABLE_LISTINGBOXGROUPS . " AS c ON  l2c.group_id = c.group_id
            LEFT JOIN " . TABLE_LISTINGBOXGROUPS_TO_LOCATIONS . " AS c2l ON c2l.group_id = c.group_id
            WHERE c2l.location_key = :locationKey: ORDER BY c2l.sort_order, l2c.sort_order";
        $sql = $zcDiContainer->get('dbConn')->bindVars($sql, ':locationKey:', $location, 'string');
        $results = $zcDiContainer->get('dbConn')->execute($sql);
        foreach ($results as $result) {
            $listingBoxesEnabled [$result ['listingbox']] = $result;
        }
        return $listingBoxesEnabled;
    }
}
