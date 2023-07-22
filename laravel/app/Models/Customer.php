<?php
/**
 * @copyright Copyright 2003-2020 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version GIT: $Id: $
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model as Eloquent;

class Customer extends Eloquent
{
    protected $table = TABLE_CUSTOMERS;
    protected $primaryKey = 'customers_id';
    public $timestamps = false;

    public function addressBooks()
    {
        return $this->hasMany(AddressBook::class, 'customers_id');
    }

    public function customerInfo()
    {
        return $this->hasOne(CustomerInfo::class, 'customers_info_id');
    }

    public function rules() : array
    {
        return [
            'customers_gender' => 'sometimes',
            'customers_firstname' => 'required',
            'customers_lastname' => 'required',
            'customers_dob' => 'sometimes',
            'customers_email_address' => 'required',
            'customers_nick' => 'sometimes',
            'customers_default_address_id' => 'required',
            'customers_telephone' => 'sometimes',
            'customers_fax' => 'required',
            'customers_password' => 'required',
            'customers_secret' => 'sometimes',
            'customers_newsletter' => 'required',
            'customers_group_pricing' => 'sometimes',
            'customers_email_format' => 'sometimes',
            'customers_authorization' => 'sometimes',
            'customers_referral' => 'sometimes',
            'registration_ip' => 'sometimes',
            'last_login_ip' => 'sometimes',
            'customers_paypal_payerid' => 'sometimes',
            'customers_paypal_ec' => 'sometimes',
        ];
    }

}
