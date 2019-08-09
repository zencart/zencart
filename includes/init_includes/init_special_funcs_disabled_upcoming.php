<?php
/**
 * @copyright 2018
 * @license http://www.zen-cart.com/License/2_0.txt GNU Public License V2.0
 * @author mc12345678
 **/

if (empty($_SESSION['today_is']) || $_SESSION['today_is'] != date('Y-m-d') || empty($_SESSION['updateExpirations_upcoming']) || $_SESSION['updateExpirations_upcoming'] !== true) {
    /**
     * require the disabled upcoming products functions, auto-enable disabled product.
     */
    require DIR_WS_FUNCTIONS . 'disabled_upcoming.php';
    zen_enable_disabled_upcoming();

    // Need to set the session variable so that will not execute on every load if other default Zen Cart code sets the today_is session variable.
    $_SESSION['updateExpirations_upcoming'] = true;
}
