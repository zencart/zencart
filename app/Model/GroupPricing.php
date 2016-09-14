<?php
/**
 * @copyright Copyright 2003-2016 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version GIT: $Id: $
 */

namespace ZenCart\Model;

use Illuminate\Database\Eloquent\Model as Eloquent;

/**
 * Class GroupPricing
 * @package ZenCart\Model
 */
class GroupPricing extends Eloquent
{
    protected $table = TABLE_GROUP_PRICING;
    protected $primaryKey = 'group_id';

    public function customers()
    {
        return $this->hasMany('ZenCart\Model\Customers', 'customers_group_pricing', 'group_id');
    }
}
