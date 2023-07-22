<?php
/**
 * @copyright Copyright 2003-2020 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version GIT: $Id: $
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model as Eloquent;

class AddressBook extends Eloquent
{
    protected $table = TABLE_ADDRESS_BOOK;
    protected $primaryKey = 'address_book_id';
    public $timestamps = false;

    public function customer()
    {
        return $this->hasOne(Customer::class, 'customers_id');
    }

    public function rules()
    {
        return [
            'customers_id' => 'required',
            'entry_gender' => 'sometimes',
            'entry_company' => 'sometimes',
            'entry_firstname' => 'sometimes',
            'entry_lastname' => 'sometimes',
            'entry_street_address' => 'sometimes',
            'entry_suburb' => 'sometimes',
            'entry_postcode' => 'sometimes',
            'entry_city' => 'sometimes',
            'entry_state' => 'sometimes',
            'entry_country_id' => 'sometimes',
            'entry_zone_id' => 'sometimes',
        ];
    }
}
