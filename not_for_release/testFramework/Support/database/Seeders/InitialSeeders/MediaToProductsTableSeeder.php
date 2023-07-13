<?php

namespace Seeders\InitialSeeders;

use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Database\Seeder;

class MediaToProductsTableSeeder extends Seeder
{

    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {


        Capsule::table('media_to_products')->truncate();

        Capsule::table('media_to_products')->insert(array(
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
