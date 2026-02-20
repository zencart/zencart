<?php
/**
 * @copyright Copyright 2003-2020 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version GIT: $Id: $
 */

namespace Tests\Models;

use Illuminate\Database\Eloquent\Model as Eloquent;

/**
 * @since ZC v1.5.8
 */
class Customer extends Eloquent
{
    protected $table = TABLE_CUSTOMERS;
    protected $primaryKey = 'customers_id';
    public $timestamps = false;

    /**
     * @since ZC v1.5.8
     */
    public function addressBooks()
    {
        return $this->hasMany(AddressBook::class, 'customers_id');
    }

    /**
     * @since ZC v1.5.8
     */
    public function CustomerInfo()
    {
        return $this->hasOne(CustomerInfo::class, 'customers_info_id');
    }

}
