<?php
/**
 * @package languageDefines
 * @copyright Copyright 2003-2013 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: storepickup.php $
 */

define('MODULE_SHIPPING_STOREPICKUP_TEXT_TITLE', 'Store Pickup');
define('MODULE_SHIPPING_STOREPICKUP_TEXT_DESCRIPTION', 'Customer In Store Pick-up');
define('MODULE_SHIPPING_STOREPICKUP_TEXT_WAY', 'Walk In');


// The following is for defining multiple locations/methods on a per-language basis. It is only used if the shopper has selected a language other than the store's default.
// The content of the MODULE_SHIPPING_STOREPICKUP_MULTIPLE_WAYS definition should be the same as the multiple locations in the shipping module's settings in your admin, just with names changed.
// Advanced use: you can leave out the prices if you want them to be inherited from the prices already defined in the admin setting for multiple locations. Thus this becomes only a language translation, regardless of price. Indeed this is the ideal way.
// Formats are:
// "Location One, 5.00; Location Two, 3.50; Location Three, 0.00"
// or
// "Location One, Location Two, Location Three"
// or leave it blank to simply use the same text as defined in the Admin, regardless of language
// TIP: This should really be left blank for the default language, otherwise the Admin settings field is never used.
define('MODULE_SHIPPING_STOREPICKUP_MULTIPLE_WAYS', "");
