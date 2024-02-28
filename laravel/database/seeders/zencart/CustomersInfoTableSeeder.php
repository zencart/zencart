<?php

namespace Database\Seeders\zencart;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CustomersInfoTableSeeder extends Seeder
{

    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {


        DB::table('customers_info')->truncate();

        DB::table('customers_info')->insert(array(
            0 =>
                array(
                    'customers_info_date_account_created' => '2004-01-21 01:35:28',
                    'customers_info_date_account_last_modified' => '0001-01-01 00:00:00',
                    'customers_info_date_of_last_logon' => '0001-01-01 00:00:00',
                    'customers_info_id' => 1,
                    'customers_info_number_of_logons' => 0,
                    'global_product_notifications' => 0,
                ),
        ));


    }
}
