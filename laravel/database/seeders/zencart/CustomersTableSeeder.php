<?php

namespace Database\Seeders\zencart;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CustomersTableSeeder extends Seeder
{

    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {


        DB::table('customers')->truncate();

        DB::table('customers')->insert(array(
            0 =>
                array(
                    'customers_authorization' => 0,
                    'customers_default_address_id' => 1,
                    'customers_dob' => '2001-01-01 00:00:00',
                    'customers_email_address' => 'root@localhost.com',
                    'customers_email_format' => 'TEXT',
                    'customers_fax' => '',
                    'customers_firstname' => 'Bill',
                    'customers_gender' => 'm',
                    'customers_group_pricing' => 0,
                    'customers_id' => 1,
                    'customers_lastname' => 'Smith',
                    'customers_newsletter' => '0',
                    'customers_nick' => '',
                    'customers_password' => 'd95e8fa7f20a009372eb3477473fcd34:1c',
                    'customers_paypal_ec' => 0,
                    'customers_paypal_payerid' => '',
                    'customers_referral' => '',
                    'customers_secret' => '',
                    'customers_telephone' => '12345',
                    'last_login_ip' => '',
                    'registration_ip' => '',
                ),
        ));


    }
}
