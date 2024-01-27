<?php

namespace Database\Seeders\zencart;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CountProductViewsTableSeeder extends Seeder
{

    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {


        DB::table('count_product_views')->truncate();

        DB::table('count_product_views')->insert(array(
            0 =>
                array(
                    'date_viewed' => '2023-06-29',
                    'language_id' => 1,
                    'product_id' => 160,
                    'views' => 3,
                ),
            1 =>
                array(
                    'date_viewed' => '2023-06-26',
                    'language_id' => 1,
                    'product_id' => 168,
                    'views' => 9,
                ),
            2 =>
                array(
                    'date_viewed' => '2023-06-27',
                    'language_id' => 1,
                    'product_id' => 168,
                    'views' => 3,
                ),
            3 =>
                array(
                    'date_viewed' => '2023-06-28',
                    'language_id' => 1,
                    'product_id' => 168,
                    'views' => 8,
                ),
            4 =>
                array(
                    'date_viewed' => '2023-06-29',
                    'language_id' => 1,
                    'product_id' => 168,
                    'views' => 15,
                ),
            5 =>
                array(
                    'date_viewed' => '2023-06-28',
                    'language_id' => 1,
                    'product_id' => 169,
                    'views' => 4,
                ),
            6 =>
                array(
                    'date_viewed' => '2023-06-29',
                    'language_id' => 1,
                    'product_id' => 169,
                    'views' => 10,
                ),
            7 =>
                array(
                    'date_viewed' => '2023-06-29',
                    'language_id' => 1,
                    'product_id' => 171,
                    'views' => 18,
                ),
            8 =>
                array(
                    'date_viewed' => '2023-06-29',
                    'language_id' => 1,
                    'product_id' => 172,
                    'views' => 7,
                ),
        ));


    }
}
