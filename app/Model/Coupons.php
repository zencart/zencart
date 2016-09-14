<?php
/**
 * @copyright Copyright 2003-2016 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version GIT: $Id: $
 */

namespace ZenCart\Model;

use Illuminate\Database\Eloquent\Model as Eloquent;

/**
 * Class Coupons
 * @package ZenCart\Model
 */
class Coupons extends Eloquent
{
    protected $table = TABLE_COUPONS;
    protected $primaryKey = 'coupon_id';

}
