<?php
/**
 * Created by PhpStorm.
 * User: wilt
 * Date: 10/09/16
 * Time: 10:22
 */

namespace ZenCart\Model;

use Illuminate\Database\Eloquent\Model as Eloquent;

class GroupPricing extends Eloquent
{
    protected $table = TABLE_GROUP_PRICING;
    protected $primaryKey = 'group_id';

    public function customers()
    {
        return $this->hasMany('ZenCart\Model\Customers', 'customers_group_pricing', 'group_id');
    }
}
