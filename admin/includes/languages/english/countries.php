<?php
/**
 * @package admin
 * @copyright Copyright 2003-2013 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: countries.php 19312 2011-07-30 09:43:12Z kuroi $
 */

define('HEADING_TITLE', 'Countries');

define('TABLE_HEADING_COUNTRY_NAME', 'Country');
define('TABLE_HEADING_COUNTRY_CODES', 'ISO Codes');
define('TABLE_HEADING_COUNTRY_STATUS', 'Status');
define('TABLE_HEADING_ACTION', 'Action');

define('TEXT_INFO_EDIT_INTRO', 'Please make any necessary changes');
define('TEXT_INFO_COUNTRY_NAME', 'Name:');
define('TEXT_INFO_COUNTRY_CODE_2', 'ISO Code (2):');
define('TEXT_INFO_COUNTRY_CODE_3', 'ISO Code (3):');
define('TEXT_INFO_ADDRESS_FORMAT', 'Address Format:');
define('TEXT_INFO_COUNTRY_STATUS', 'Status (Active for shipping?):');
define('TEXT_INFO_INSERT_INTRO', 'Please enter the new country with its related data');
define('TEXT_INFO_DELETE_INTRO', 'Are you sure you want to delete this country?');
define('TEXT_INFO_HEADING_NEW_COUNTRY', 'New Country');
define('TEXT_INFO_HEADING_EDIT_COUNTRY', 'Edit Country');
define('TEXT_INFO_HEADING_DELETE_COUNTRY', 'Delete Country');
define('ERROR_COUNTRY_IN_USE', 'ERROR: Cannot delete selected country because it is connected to customer records.');
define('ISO_COUNTRY_CODES_LINK', '<a href="http://www.iso.org/iso/country_codes/iso_3166_code_lists.htm" target="_blank">ISO 3166 Country Codes Reference</a>');
