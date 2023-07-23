<?php

namespace Database\Seeders\zencart;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProductTypesToCategoryTableSeeder extends Seeder
{

    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {


        DB::table('product_types_to_category')->truncate();

        DB::table('product_types_to_category')->insert(array(
            0 =>
                array(
                    'category_id' => 63,
                    'product_type_id' => 3,
                ),
            1 =>
                array(
                    'category_id' => 63,
                    'product_type_id' => 4,
                ),
            2 =>
                array(
                    'category_id' => 62,
                    'product_type_id' => 2,
                ),
        ));


    }
}
