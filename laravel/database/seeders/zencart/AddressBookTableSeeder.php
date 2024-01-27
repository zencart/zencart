<?php

namespace Database\Seeders\zencart;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AddressBookTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('address_book')->truncate();
        DB::table('address_book')->insert(array(
            0 =>
                array(
                    'address_book_id' => 1,
                    'customers_id' => 1,
                    'entry_city' => 'Here',
                    'entry_company' => 'JustaDemo',
                    'entry_country_id' => 223,
                    'entry_firstname' => 'Bill',
                    'entry_gender' => 'm',
                    'entry_lastname' => 'Smith',
                    'entry_postcode' => '12345',
                    'entry_state' => '',
                    'entry_street_address' => '123 Any Avenue',
                    'entry_suburb' => '',
                    'entry_zone_id' => 12,
                ),
        ));

    }
}
