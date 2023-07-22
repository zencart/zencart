<?php
/**
 * @copyright Copyright 2003-2020 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version GIT: $Id: $
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model as Eloquent;

class CustomerInfo extends Eloquent
{
    protected $table = TABLE_CUSTOMERS_INFO;
    protected $primaryKey = 'customers_info_id';
    public $timestamps = false;

    public function rules()
    {
        return [
            'customers_info_date_of_last_logon' => 'sometimes',
            'customers_info_number_of_logons' => 'sometimes',
            'customers_info_date_account_created' => 'sometimes',
            'customers_info_date_account_last_modified' => 'sometimes',
            'global_product_notifications' => 'sometimes',
        ];
    }
}
