<?php
/**
 * @copyright Copyright 2003-2016 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version GIT: $Id: $
 */
namespace App\Model;

use Illuminate\Database\Eloquent\Model as Eloquent;

/**
 * Class OrdersStatus
 * @package ZenCart\Model
 */
class OrdersStatus extends TranslatedModel
{
    protected $table = TABLE_ORDERS_STATUS;
    protected $primaryKey = 'orders_status_id';
    protected $translationTable = TABLE_ORDERS_STATUS;
    public $translatedAttributes = ['orders_status_name'];

}
