<?php
/**
 * @package admin
 * @copyright Copyright 2003-2007 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: tax_classes.php 7167 2007-10-03 23:02:17Z drbyte $
 */

define('HEADING_TITLE', 'Tax Classes');

define('TABLE_HEADING_TAX_CLASS_ID', 'ID');
define('TABLE_HEADING_TAX_CLASSES', 'Tax Classes');
define('TABLE_HEADING_ACTION', 'Action');

define('TEXT_INFO_EDIT_INTRO', 'Please make any necessary changes');
define('TEXT_INFO_CLASS_TITLE', 'Tax Class Title:');
define('TEXT_INFO_CLASS_DESCRIPTION', 'Description:');
define('TEXT_INFO_DATE_ADDED', 'Date Added:');
define('TEXT_INFO_LAST_MODIFIED', 'Last Modified:');
define('TEXT_INFO_INSERT_INTRO', 'Please enter the new tax class with its related data');
define('TEXT_INFO_DELETE_INTRO', 'Are you sure you want to delete this tax class?');
define('TEXT_INFO_HEADING_NEW_TAX_CLASS', 'New Tax Class');
define('TEXT_INFO_HEADING_EDIT_TAX_CLASS', 'Edit Tax Class');
define('TEXT_INFO_HEADING_DELETE_TAX_CLASS', 'Delete Tax Class');
define('ERROR_TAX_RATE_EXISTS_FOR_CLASS', 'ERROR: Cannot delete this Tax Class -- Tax Rates are currently linked to this Tax Class.');
define('ERROR_TAX_RATE_EXISTS_FOR_PRODUCTS', 'ERROR: Cannot delete this Tax Class -- There are %s products linked to this Tax Class.');
?>