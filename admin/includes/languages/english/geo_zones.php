<?php
/**
 * @package admin
 * @copyright Copyright 2003-2006 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: geo_zones.php 4736 2006-10-13 07:11:44Z drbyte $
 */

define('HEADING_TITLE', 'Zone Definitions - Taxes, Payment and Shipping');

define('TABLE_HEADING_COUNTRY', 'Country');
define('TABLE_HEADING_COUNTRY_ZONE', 'Zone');
define('TABLE_HEADING_TAX_ZONES', 'Zone Name');
define('TABLE_HEADING_TAX_ZONES_DESCRIPTION', 'Zone Description');
define('TABLE_HEADING_STATUS', 'Status');
define('TABLE_HEADING_ACTION', 'Action');
//define('TEXT_LEGEND', 'LEGEND: ');
define('TEXT_LEGEND_TAX_AND_ZONES', ': Taxes &amp; Zones Defined');
define('TEXT_LEGEND_ONLY_ZONES', ': Zones Defined but not Taxes ');
define('TEXT_LEGEND_NOT_CONF', ': Not Configured ');

define('TEXT_INFO_HEADING_NEW_ZONE', 'New Zone');
define('TEXT_INFO_NEW_ZONE_INTRO', 'Please enter the new zone information');

define('TEXT_INFO_HEADING_EDIT_ZONE', 'Edit Zone');
define('TEXT_INFO_EDIT_ZONE_INTRO', 'Please make any necessary changes');

define('TEXT_INFO_HEADING_DELETE_ZONE', 'Delete Zone');
define('TEXT_INFO_DELETE_ZONE_INTRO', 'Are you sure you want to delete this zone?');

define('TEXT_INFO_HEADING_NEW_SUB_ZONE', 'New Sub Zone');
define('TEXT_INFO_NEW_SUB_ZONE_INTRO', 'Please enter the new sub zone information');

define('TEXT_INFO_HEADING_EDIT_SUB_ZONE', 'Edit Sub Zone');
define('TEXT_INFO_EDIT_SUB_ZONE_INTRO', 'Please make any necessary changes');

define('TEXT_INFO_HEADING_DELETE_SUB_ZONE', 'Delete Sub Zone');
define('TEXT_INFO_DELETE_SUB_ZONE_INTRO', 'Are you sure you want to delete this sub zone?');

define('TEXT_INFO_DATE_ADDED', 'Date Added:');
define('TEXT_INFO_LAST_MODIFIED', 'Last Modified:');
define('TEXT_INFO_ZONE_NAME', 'Zone Name:');
define('TEXT_INFO_NUMBER_ZONES', 'Number of Zones:');
define('TEXT_INFO_ZONE_DESCRIPTION', 'Description:');
define('TEXT_INFO_COUNTRY', 'Country:');
define('TEXT_INFO_COUNTRY_ZONE', 'Zone:');
define('TYPE_BELOW', 'All Zones');
define('PLEASE_SELECT', 'All Zones');
define('TEXT_ALL_COUNTRIES', 'All Countries');

define('TEXT_INFO_NUMBER_TAX_RATES','Number of Tax Rates:');
define('ERROR_TAX_RATE_EXISTS','WARNING: Tax Rate(s) are defined for this zone. Please delete the Tax Rate(s) before removing this zone');
?>