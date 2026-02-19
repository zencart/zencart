<?php
/**
 * @copyright Copyright 2003-2020 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version GIT: $Id: $
 */

namespace Tests\Models;

use Illuminate\Database\Eloquent\Model as Eloquent;

/**
 * @since ZC v2.0.0
 */
class Coupon extends Eloquent
{
    protected $table = TABLE_COUPONS;
    protected $primaryKey = 'coupon_id';
    public $timestamps = false;
    protected $guarded = [];
}
