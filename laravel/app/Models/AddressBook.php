<?php
/**
 * @copyright Copyright 2003-2020 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version GIT: $Id: $
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model as Eloquent;

/**
 * @since ZC v1.5.8
 */
class AddressBook extends Eloquent
{
    protected $table = TABLE_ADDRESS_BOOK;
    protected $primaryKey = 'address_book_id';
    public $timestamps = false;

    /**
     * @since ZC v1.5.8
     */
    public function customer()
    {
        return $this->hasOne(Customer::class, 'customers_id');
    }
}
