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
 * Class LeadGeoZonesDetailRoutes
 * @package ZenCart\Services
 */
class LeadGeoZonesDetailRoutes extends LeadRoutes
{
    /**
     *
     */
    public function insertExecute()
    {
        $sql = "INSERT INTO " . TABLE_ZONES_TO_GEO_ZONES . " (zone_country_id, zone_id, geo_zone_id, date_added) VALUES (:zone_country_id:, :zone_id:, :geo_zone_id:, now())";
        $sql = $this->dbConn->bindVars($sql, ':zone_country_id:', $this->request->readPost('entry_field_zone_country_id'),
            'integer');
        $sql = $this->dbConn->bindVars($sql, ':zone_id:', $this->request->readPost('entry_field_zone_id'), 'integer');
        $sql = $this->dbConn->bindVars($sql, ':geo_zone_id:', $this->request->readPost('entry_field_geo_zone_id'), 'integer');
        $this->dbConn->execute($sql);
    }
}
