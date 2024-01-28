<?php

namespace Database\Seeders\zencart;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MediaToProductsTableSeeder extends Seeder
{

    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {


        DB::table('media_to_products')->truncate();

        DB::table('media_to_products')->insert(array(
            0 =>
                array(
                    'media_id' => 1,
                    'product_id' => 166,
                ),
            1 =>
                array(
                    'media_id' => 2,
                    'product_id' => 169,
                ),
        ));


    }
}
