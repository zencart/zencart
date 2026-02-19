<?php
/**
 * @copyright Copyright 2003-2026 Zen Cart Development Team
 * @license https://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 */

namespace Zencart\DbRepositories;

use queryFactory;

/**
 * Native queryFactory-backed accessor for TABLE_PRODUCT_TYPE_LAYOUT.
 */
class ProductTypeLayoutRepository
{
    public function __construct(private queryFactory $db)
    {
    }

    public function loadConfigSettings(): void
    {
        $configs = $this->db->Execute(
            'SELECT configuration_key, configuration_value FROM ' . TABLE_PRODUCT_TYPE_LAYOUT
        );

        while (!$configs->EOF) {
            $key = strtoupper((string)$configs->fields['configuration_key']);
            if (!defined($key)) {
                define($key, $configs->fields['configuration_value']);
            }
            $configs->MoveNext();
        }
    }
}
