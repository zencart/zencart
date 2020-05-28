<?php
/**
 * @copyright Copyright 2003-2020 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: DrByte 2020 May 19 Modified in v1.5.7 $
 *
 * Designed for v1.5.7
 */

class products_viewed_counter extends base
{
    protected $exclude_spiders = true;
    protected $exclude_maintenance_ips = true; // admins

    function __construct()
    {
        if ($this->should_be_excluded()) {
            return;
        }
        $this->attach($this, array('NOTIFY_PRODUCT_VIEWS_HIT_INCREMENTOR'));
    }

    function updateNotifyProductViewsHitIncrementor(&$class, $eventID, $product_id)
    {
        global $db;

        $sql = "INSERT INTO " . TABLE_COUNT_PRODUCT_VIEWS . "
                (product_id, language_id, date_viewed, views)
                VALUES (" . (int)$product_id . ", " . (int)$_SESSION['languages_id'] . ", now(), 1)
                ON DUPLICATE KEY UPDATE views = views + 1";
        $db->Execute($sql);
    }

    protected function should_be_excluded()
    {
        // exclude search-engine spiders
        if ($this->exclude_spiders && $GLOBALS['spider_flag'] === true) {
            return true;
        }

        // exclude hits from Admin users
        if ($this->exclude_maintenance_ips && zen_is_whitelisted_admin_ip()) { // admins
            return true;
        }
    }
}
