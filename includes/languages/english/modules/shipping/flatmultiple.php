<?php
/**
 * @package languageDefines
 * @copyright Copyright 2003-2013 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: flatmultiple.php $
 */

define('MODULE_SHIPPING_FLATMULTIPLE_TEXT_TITLE', 'Flat Multiple');
define('MODULE_SHIPPING_FLATMULTIPLE_TEXT_DESCRIPTION', 'Flat Rate Multiple');
define('MODULE_SHIPPING_FLATMULTIPLE_TEXT_WAY', 'Best Way');


// The following is for defining multiple locations/methods on a per-language basis. It is only used if the shopper has selected a language other than the store's default.
// The content of the MODULE_SHIPPING_FLATMULTIPLE_MULTIPLE_WAYS definition should be the same as the multiple flat ways in the shipping module's settings in your admin, just with names changed.
// Typical formats are:
// "Ground;Two Day,2.00; Next Day,3.00; Express,4.00;Over Night,10.00"
// "Ground, Two Day, Next Day"
// or leave it blank to simply use the same text as defined in the Admin, regardless of language
// TIP: This should really be left blank for the default language, otherwise the Admin settings field is never used.
define('MODULE_SHIPPING_FLATMULTIPLE_MULTIPLE_WAYS', "");
