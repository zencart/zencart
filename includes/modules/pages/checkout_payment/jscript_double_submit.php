<?php
/**
 * @copyright Copyright 2003-2024 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: DrByte 2024 Apr 10 Modified in v2.0.1 $
 */

// For payment modules using AJAX for security, confirmation page javascript
// is not loaded.  So load the relevant file here.
if (PADSS_AJAX_CHECKOUT == '1') {
    require(DIR_WS_MODULES . '/pages/checkout_confirmation/jscript_double_submit.php');
}
