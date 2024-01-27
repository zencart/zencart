<?php

namespace Database\Seeders\zencart;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CurrenciesTableSeeder extends Seeder
{

    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {


        DB::table('currencies')->truncate();

        DB::table('currencies')->insert(array(
            0 =>
                array(
                    'code' => 'USD',
                    'currencies_id' => 1,
                    'decimal_places' => '2',
                    'decimal_point' => '.',
                    'last_updated' => '2023-06-29 12:14:04',
                    'symbol_left' => '$',
                    'symbol_right' => '',
                    'thousands_point' => ',',
                    'title' => 'US Dollar',
                    'value' => '1.000000',
                ),
            1 =>
                array(
                    'code' => 'EUR',
                    'currencies_id' => 2,
                    'decimal_places' => '2',
                    'decimal_point' => '.',
                    'last_updated' => '2023-06-29 12:14:04',
                    'symbol_left' => '&euro;',
                    'symbol_right' => '',
                    'thousands_point' => ',',
                    'title' => 'Euro',
                    'value' => '0.773000',
                ),
            2 =>
                array(
                    'code' => 'GBP',
                    'currencies_id' => 3,
                    'decimal_places' => '2',
                    'decimal_point' => '.',
                    'last_updated' => '2023-06-29 12:14:04',
                    'symbol_left' => '&pound;',
                    'symbol_right' => '',
                    'thousands_point' => ',',
                    'title' => 'GB Pound',
                    'value' => '0.672600',
                ),
            3 =>
                array(
                    'code' => 'CAD',
                    'currencies_id' => 4,
                    'decimal_places' => '2',
                    'decimal_point' => '.',
                    'last_updated' => '2023-06-29 12:14:04',
                    'symbol_left' => '$',
                    'symbol_right' => '',
                    'thousands_point' => ',',
                    'title' => 'Canadian Dollar',
                    'value' => '1.104200',
                ),
            4 =>
                array(
                    'code' => 'AUD',
                    'currencies_id' => 5,
                    'decimal_places' => '2',
                    'decimal_point' => '.',
                    'last_updated' => '2023-06-29 12:14:04',
                    'symbol_left' => '$',
                    'symbol_right' => '',
                    'thousands_point' => ',',
                    'title' => 'Australian Dollar',
                    'value' => '1.178900',
                ),
        ));


    }
}
