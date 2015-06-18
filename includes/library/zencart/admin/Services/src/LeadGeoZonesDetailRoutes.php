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

    /**
     * @return array
     */
    public function autocompleteGeoZoneExecute()
    {
        $dataTable = $this->request->readGet('dataTable');
        $dataSearchField = $this->request->readGet('dataSearchField');
        $dataResponse = $this->request->readGet('dataResponse');
        $valueResponse = $this->request->readGet('valueResponse');
        $search = $this->request->readGet('term');
        $sql = "SELECT :dataResponse:, :valueResponse: FROM :dataTable: WHERE :dataSearchField: LIKE ':search:%'";
        if ($this->request->has('extraWhere') && isset($this->outputLayout ['fields'] [$this->request->readGet('extraWhere')])) {
            $sql .= ' AND :extraWhereField: = :extraWhereValue:';
            $sql = $this->dbConn->bindVars($sql, ':extraWhereField:', $this->request->readGet('extraWhere'), 'noquotestring');
            $bindVarsType = $this->outputLayout ['fields'] [$this->request->readGet('extraWhere')] ['bindVarsType'];
            $sql = $this->dbConn->bindVars($sql, ':extraWhereValue:', $this->request->readGet('extraWhereVal'), $bindVarsType);
        }
        $sql = $this->dbConn->bindVars($sql, ':dataResponse:', $dataResponse, 'noquotestring');
        $sql = $this->dbConn->bindVars($sql, ':valueResponse:', $valueResponse, 'noquotestring');
        $sql = $this->dbConn->bindVars($sql, ':dataSearchField:', $dataSearchField, 'noquotestring');
        $sql = $this->dbConn->bindVars($sql, ':dataTable:', $dataTable, 'noquotestring');
        $sql = $this->dbConn->bindVars($sql, ':search:', $search, 'noquotestring');
        $results = $this->dbConn->execute($sql);
        $retVal = array('results' => array());
        if ($this->request->readGet('addAllResponse') == true) {
            $retVal ['results'] [] = array(
                'text' => $this->request->readGet('addAllResponseText'),
                'id' => $this->request->readGet('addAllResponseValue')
            );
        }
        foreach ($results as $result) {
            $retVal ['results'] [] = array(
                'text' => $result[$valueResponse],
                'id' => $result[$dataResponse]
            );
        }
        return $retVal;
    }
}
