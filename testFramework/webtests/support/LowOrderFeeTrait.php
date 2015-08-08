<?php
/**
 * File contains common unit/web test resources
 *
 * @package tests
 * @copyright Copyright 2003-2015 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id:  $
 */

/**
 * Class LowOrderFeeTrait
 */
trait LowOrderFeeTrait
{

    public function switchLowOrderFee($mode)
    {
        $sql = "UPDATE " . DB_PREFIX . "configuration set configuration_value = '" . ($mode == 'on' ? 'true' : 'false') . "' WHERE configuration_key = 'MODULE_ORDER_TOTAL_LOWORDERFEE_LOW_ORDER_FEE'";
        $this->doDbQuery($sql);
    }
}
