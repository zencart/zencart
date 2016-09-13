<?php
/**
 * Created by PhpStorm.
 * User: wilt
 * Date: 10/09/16
 * Time: 10:22
 */

namespace ZenCart\Model;

use Illuminate\Database\Eloquent\Model as Eloquent;

class OrdersStatus extends Eloquent
{
    protected $table = TABLE_ORDERS_STATUS;
    protected $primaryKey = 'orders_status_id';

}
