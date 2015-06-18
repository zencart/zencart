<?php
/**
 * @copyright Copyright 2003-2015 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id:  New in v1.6.0 $
 */
namespace ZenCart\Admin\Controllers;

/**
 * Class GeoZonesDetail
 * @package ZenCart\Admin\Controllers
 */
class GeoZonesDetail extends AbstractLeadController
{
    /**
     *
     */
    public function addExecute()
    {
        parent::addExecute();
        $this->tplVars ['leadDefinition'] ['fields'] ['geo_zone_id'] ['value'] = $this->request->readGet('geo_zone_id');
    }

    /**
     *
     */
    public function insertExecute()
    {
        $this->service->insertExecute();
        $this->response['redirect'] = zen_href_link($this->request->readGet('cmd'), 'geo_zone_id=' . (int)$this->request->readPost('entry_field_geo_zone_id'));
    }

    /**
     *
     */
    public function autocompleteGeoZoneExecute()
    {
        $this->useView = false;
        $this->response = $this->service->autocompleteGeoZoneExecute();
    }
}
