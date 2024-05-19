<?php
/**
 * Part of the Report All Errors plugin, provided by lat9@vinosdefrutastropicales.com
 *
 * @copyright Copyright 2003-2024 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: DrByte 2024 Apr 10 Modified in v2.0.1 $
 */

/*-----
** If the configuration value is set to report all errors during the store's operation, enable it!
*/
if (defined('REPORT_ALL_ERRORS_STORE') && REPORT_ALL_ERRORS_STORE != 'No') {
    @ini_set('error_reporting', E_ALL );
    set_error_handler('zen_debug_error_handler', E_ALL);
}
