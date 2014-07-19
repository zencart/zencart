<?php
/**
 * @package languageDefines
 * @copyright Copyright 2003-2006 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: freeoptions.php 3830 2006-06-21 23:47:16Z ajeh $
 */

define('MODULE_SHIPPING_FREEOPTIONS_TEXT_TITLE', 'Free Shipping Options');
define('MODULE_SHIPPING_FREEOPTIONS_TEXT_DESCRIPTION', '
Free Options is used to display a Free Shipping option when other Shipping Modules are displayed.
It can be based on: Always show, Order Total, Order Weight or Order Item Count.
The Free Options module does not show when Free Shipper is displayed.<br /><br />
Setting Total to >= 0.00 and <= nothing (leave blank) will activate this module to show with all shipping modules, except for Free Shipping - freeshipper.<br /><br />
NOTE: Leaving all settings for Total, Weight and Item count blank will deactivate this module.<br /><br />
NOTE: Free Shipping Options does not display if Free Shipping is used based on 0 weight is Free Shipping.
See: freeshipper
');
define('MODULE_SHIPPING_FREEOPTIONS_TEXT_WAY', 'Free Shipping');

?>