<?php

namespace Seeders\InitialSeeders;

use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Database\Seeder;

class ProductTypesToCategoryTableSeeder extends Seeder
{

    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {


        Capsule::table('product_types_to_category')->truncate();

        Capsule::table('product_types_to_category')->insert(array(
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
