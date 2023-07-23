<?php

namespace Database\Seeders\zencart;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProductsOptionsValuesTableSeeder extends Seeder
{

    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {


        DB::table('products_options_values')->truncate();

        DB::table('products_options_values')->insert(array(
            0 =>
                array(
                    'language_id' => 1,
                    'products_options_values_id' => 0,
                    'products_options_values_name' => 'TEXT',
                    'products_options_values_sort_order' => 0,
                ),
            1 =>
                array(
                    'language_id' => 1,
                    'products_options_values_id' => 1,
                    'products_options_values_name' => '4 mb',
                    'products_options_values_sort_order' => 10,
                ),
            2 =>
                array(
                    'language_id' => 1,
                    'products_options_values_id' => 2,
                    'products_options_values_name' => '8 mb',
                    'products_options_values_sort_order' => 20,
                ),
            3 =>
                array(
                    'language_id' => 1,
                    'products_options_values_id' => 3,
                    'products_options_values_name' => '16 mb',
                    'products_options_values_sort_order' => 30,
                ),
            4 =>
                array(
                    'language_id' => 1,
                    'products_options_values_id' => 4,
                    'products_options_values_name' => '32 mb',
                    'products_options_values_sort_order' => 40,
                ),
            5 =>
                array(
                    'language_id' => 1,
                    'products_options_values_id' => 5,
                    'products_options_values_name' => 'Value',
                    'products_options_values_sort_order' => 10,
                ),
            6 =>
                array(
                    'language_id' => 1,
                    'products_options_values_id' => 6,
                    'products_options_values_name' => 'Premium',
                    'products_options_values_sort_order' => 20,
                ),
            7 =>
                array(
                    'language_id' => 1,
                    'products_options_values_id' => 7,
                    'products_options_values_name' => 'Deluxe',
                    'products_options_values_sort_order' => 30,
                ),
            8 =>
                array(
                    'language_id' => 1,
                    'products_options_values_id' => 8,
                    'products_options_values_name' => 'PS/2',
                    'products_options_values_sort_order' => 20,
                ),
            9 =>
                array(
                    'language_id' => 1,
                    'products_options_values_id' => 9,
                    'products_options_values_name' => 'USB',
                    'products_options_values_sort_order' => 10,
                ),
            10 =>
                array(
                    'language_id' => 1,
                    'products_options_values_id' => 10,
                    'products_options_values_name' => 'Download: Windows - English',
                    'products_options_values_sort_order' => 10,
                ),
            11 =>
                array(
                    'language_id' => 1,
                    'products_options_values_id' => 13,
                    'products_options_values_name' => 'Box: Windows - English',
                    'products_options_values_sort_order' => 1000,
                ),
            12 =>
                array(
                    'language_id' => 1,
                    'products_options_values_id' => 14,
                    'products_options_values_name' => 'DVD/VHS Combo Pak',
                    'products_options_values_sort_order' => 30,
                ),
            13 =>
                array(
                    'language_id' => 1,
                    'products_options_values_id' => 15,
                    'products_options_values_name' => 'Blue',
                    'products_options_values_sort_order' => 50,
                ),
            14 =>
                array(
                    'language_id' => 1,
                    'products_options_values_id' => 16,
                    'products_options_values_name' => 'Red',
                    'products_options_values_sort_order' => 10,
                ),
            15 =>
                array(
                    'language_id' => 1,
                    'products_options_values_id' => 17,
                    'products_options_values_name' => 'Yellow',
                    'products_options_values_sort_order' => 30,
                ),
            16 =>
                array(
                    'language_id' => 1,
                    'products_options_values_id' => 18,
                    'products_options_values_name' => 'Medium',
                    'products_options_values_sort_order' => 30,
                ),
            17 =>
                array(
                    'language_id' => 1,
                    'products_options_values_id' => 19,
                    'products_options_values_name' => 'X-Small',
                    'products_options_values_sort_order' => 10,
                ),
            18 =>
                array(
                    'language_id' => 1,
                    'products_options_values_id' => 20,
                    'products_options_values_name' => 'Large',
                    'products_options_values_sort_order' => 40,
                ),
            19 =>
                array(
                    'language_id' => 1,
                    'products_options_values_id' => 21,
                    'products_options_values_name' => 'Small',
                    'products_options_values_sort_order' => 20,
                ),
            20 =>
                array(
                    'language_id' => 1,
                    'products_options_values_id' => 22,
                    'products_options_values_name' => 'VHS',
                    'products_options_values_sort_order' => 20,
                ),
            21 =>
                array(
                    'language_id' => 1,
                    'products_options_values_id' => 23,
                    'products_options_values_name' => 'DVD',
                    'products_options_values_sort_order' => 10,
                ),
            22 =>
                array(
                    'language_id' => 1,
                    'products_options_values_id' => 24,
                    'products_options_values_name' => '20th Century',
                    'products_options_values_sort_order' => 10,
                ),
            23 =>
                array(
                    'language_id' => 1,
                    'products_options_values_id' => 25,
                    'products_options_values_name' => 'Orange',
                    'products_options_values_sort_order' => 20,
                ),
            24 =>
                array(
                    'language_id' => 1,
                    'products_options_values_id' => 26,
                    'products_options_values_name' => 'Green',
                    'products_options_values_sort_order' => 40,
                ),
            25 =>
                array(
                    'language_id' => 1,
                    'products_options_values_id' => 27,
                    'products_options_values_name' => 'Purple',
                    'products_options_values_sort_order' => 60,
                ),
            26 =>
                array(
                    'language_id' => 1,
                    'products_options_values_id' => 28,
                    'products_options_values_name' => 'Brown',
                    'products_options_values_sort_order' => 70,
                ),
            27 =>
                array(
                    'language_id' => 1,
                    'products_options_values_id' => 29,
                    'products_options_values_name' => 'Black',
                    'products_options_values_sort_order' => 80,
                ),
            28 =>
                array(
                    'language_id' => 1,
                    'products_options_values_id' => 30,
                    'products_options_values_name' => 'White',
                    'products_options_values_sort_order' => 90,
                ),
            29 =>
                array(
                    'language_id' => 1,
                    'products_options_values_id' => 31,
                    'products_options_values_name' => 'Silver',
                    'products_options_values_sort_order' => 100,
                ),
            30 =>
                array(
                    'language_id' => 1,
                    'products_options_values_id' => 32,
                    'products_options_values_name' => 'Gold',
                    'products_options_values_sort_order' => 110,
                ),
            31 =>
                array(
                    'language_id' => 1,
                    'products_options_values_id' => 34,
                    'products_options_values_name' => 'Wrapping',
                    'products_options_values_sort_order' => 40,
                ),
            32 =>
                array(
                    'language_id' => 1,
                    'products_options_values_id' => 35,
                    'products_options_values_name' => 'Autographed Memorabilia Card',
                    'products_options_values_sort_order' => 30,
                ),
            33 =>
                array(
                    'language_id' => 1,
                    'products_options_values_id' => 36,
                    'products_options_values_name' => 'Collector\'s Tin',
                    'products_options_values_sort_order' => 20,
                ),
            34 =>
                array(
                    'language_id' => 1,
                    'products_options_values_id' => 37,
                    'products_options_values_name' => 'Select from below ...',
                    'products_options_values_sort_order' => 5,
                ),
            35 =>
                array(
                    'language_id' => 1,
                    'products_options_values_id' => 38,
                    'products_options_values_name' => '$5.00',
                    'products_options_values_sort_order' => 5,
                ),
            36 =>
                array(
                    'language_id' => 1,
                    'products_options_values_id' => 39,
                    'products_options_values_name' => '$10.00',
                    'products_options_values_sort_order' => 10,
                ),
            37 =>
                array(
                    'language_id' => 1,
                    'products_options_values_id' => 40,
                    'products_options_values_name' => '$25.00',
                    'products_options_values_sort_order' => 25,
                ),
            38 =>
                array(
                    'language_id' => 1,
                    'products_options_values_id' => 41,
                    'products_options_values_name' => '$15.00',
                    'products_options_values_sort_order' => 15,
                ),
            39 =>
                array(
                    'language_id' => 1,
                    'products_options_values_id' => 42,
                    'products_options_values_name' => '$50.00',
                    'products_options_values_sort_order' => 50,
                ),
            40 =>
                array(
                    'language_id' => 1,
                    'products_options_values_id' => 43,
                    'products_options_values_name' => '$100.00',
                    'products_options_values_sort_order' => 100,
                ),
            41 =>
                array(
                    'language_id' => 1,
                    'products_options_values_id' => 44,
                    'products_options_values_name' => 'Select from below ...',
                    'products_options_values_sort_order' => 5,
                ),
            42 =>
                array(
                    'language_id' => 1,
                    'products_options_values_id' => 45,
                    'products_options_values_name' => 'NONE',
                    'products_options_values_sort_order' => 5,
                ),
            43 =>
                array(
                    'language_id' => 1,
                    'products_options_values_id' => 46,
                    'products_options_values_name' => 'None',
                    'products_options_values_sort_order' => 5,
                ),
            44 =>
                array(
                    'language_id' => 1,
                    'products_options_values_id' => 47,
                    'products_options_values_name' => 'Embossed Collector\'s Tin',
                    'products_options_values_sort_order' => 10,
                ),
            45 =>
                array(
                    'language_id' => 1,
                    'products_options_values_id' => 48,
                    'products_options_values_name' => 'None',
                    'products_options_values_sort_order' => 5,
                ),
            46 =>
                array(
                    'language_id' => 1,
                    'products_options_values_id' => 49,
                    'products_options_values_name' => 'Custom Handling',
                    'products_options_values_sort_order' => 20,
                ),
            47 =>
                array(
                    'language_id' => 1,
                    'products_options_values_id' => 50,
                    'products_options_values_name' => 'Same Day Shipping',
                    'products_options_values_sort_order' => 30,
                ),
            48 =>
                array(
                    'language_id' => 1,
                    'products_options_values_id' => 51,
                    'products_options_values_name' => 'Quality Design',
                    'products_options_values_sort_order' => 10,
                ),
            49 =>
                array(
                    'language_id' => 1,
                    'products_options_values_id' => 52,
                    'products_options_values_name' => 'Download: Windows - Spanish',
                    'products_options_values_sort_order' => 20,
                ),
            50 =>
                array(
                    'language_id' => 1,
                    'products_options_values_id' => 53,
                    'products_options_values_name' => '3 Iron',
                    'products_options_values_sort_order' => 30,
                ),
            51 =>
                array(
                    'language_id' => 1,
                    'products_options_values_id' => 54,
                    'products_options_values_name' => '4 Iron',
                    'products_options_values_sort_order' => 40,
                ),
            52 =>
                array(
                    'language_id' => 1,
                    'products_options_values_id' => 55,
                    'products_options_values_name' => '5 Iron',
                    'products_options_values_sort_order' => 50,
                ),
            53 =>
                array(
                    'language_id' => 1,
                    'products_options_values_id' => 56,
                    'products_options_values_name' => '6 Iron',
                    'products_options_values_sort_order' => 60,
                ),
            54 =>
                array(
                    'language_id' => 1,
                    'products_options_values_id' => 57,
                    'products_options_values_name' => '9 Iron',
                    'products_options_values_sort_order' => 90,
                ),
            55 =>
                array(
                    'language_id' => 1,
                    'products_options_values_id' => 58,
                    'products_options_values_name' => 'Wedge',
                    'products_options_values_sort_order' => 200,
                ),
            56 =>
                array(
                    'language_id' => 1,
                    'products_options_values_id' => 59,
                    'products_options_values_name' => '7 Iron',
                    'products_options_values_sort_order' => 70,
                ),
            57 =>
                array(
                    'language_id' => 1,
                    'products_options_values_id' => 60,
                    'products_options_values_name' => '8 Iron',
                    'products_options_values_sort_order' => 80,
                ),
            58 =>
                array(
                    'language_id' => 1,
                    'products_options_values_id' => 61,
                    'products_options_values_name' => '2 Iron',
                    'products_options_values_sort_order' => 20,
                ),
            59 =>
                array(
                    'language_id' => 1,
                    'products_options_values_id' => 62,
                    'products_options_values_name' => 'PDF - English',
                    'products_options_values_sort_order' => 10,
                ),
            60 =>
                array(
                    'language_id' => 1,
                    'products_options_values_id' => 63,
                    'products_options_values_name' => 'MS Word - English',
                    'products_options_values_sort_order' => 20,
                ),
            61 =>
                array(
                    'language_id' => 1,
                    'products_options_values_id' => 64,
                    'products_options_values_name' => 'Download: MAC - English',
                    'products_options_values_sort_order' => 100,
                ),
            62 =>
                array(
                    'language_id' => 1,
                    'products_options_values_id' => 65,
                    'products_options_values_name' => 'per Foot',
                    'products_options_values_sort_order' => 10,
                ),
            63 =>
                array(
                    'language_id' => 1,
                    'products_options_values_id' => 66,
                    'products_options_values_name' => 'per Yard',
                    'products_options_values_sort_order' => 20,
                ),
            64 =>
                array(
                    'language_id' => 1,
                    'products_options_values_id' => 67,
                    'products_options_values_name' => 'Free Shipping Included!',
                    'products_options_values_sort_order' => 10,
                ),
            65 =>
                array(
                    'language_id' => 1,
                    'products_options_values_id' => 68,
                    'products_options_values_name' => 'Book Hard Cover',
                    'products_options_values_sort_order' => 5,
                ),
        ));


    }
}
