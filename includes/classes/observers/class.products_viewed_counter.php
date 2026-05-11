<?php

use Zencart\Traits\ObserverManager;

/**
 * @copyright Copyright 2003-2025 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: DrByte 2025 Sep 18 Modified in v2.2.0 $
 *
 * @since ZC v1.5.1
 */

class products_viewed_counter
{
    use ObserverManager;

    protected bool $exclude_spiders = true;
    protected bool $exclude_maintenance_ips = true; // admins

    public function __construct()
    {
        if ($this->should_be_excluded()) {
            return;
        }
        $this->attach($this, ['NOTIFY_PRODUCT_VIEWS_HIT_INCREMENTOR']);
    }

    /**
     * @since ZC v1.5.7
     */
    public function updateNotifyProductViewsHitIncrementor(&$class, $eventID, $product_id): void
    {
        global $db;

        $sql = "INSERT INTO " . TABLE_COUNT_PRODUCT_VIEWS . "
                (product_id, language_id, date_viewed, views)
                VALUES (" . (int)$product_id . ", " . (int)$_SESSION['languages_id'] . ", now(), 1)
                ON DUPLICATE KEY UPDATE views = views + 1";
        $db->Execute($sql);
    }

    /**
     * @since ZC v1.5.7
     */
    protected function should_be_excluded(): ?bool
    {
        global $spider_flag;

        // exclude search-engine spiders
        if ($this->exclude_spiders && $spider_flag === true) {
            return true;
        }

        // exclude hits from Admin users
        if ($this->exclude_maintenance_ips && zen_is_whitelisted_admin_ip()) { // admins
            return true;
        }

        return false;
    }
}
