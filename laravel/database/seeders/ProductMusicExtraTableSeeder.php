<?php

namespace Database\Seeders;

use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Database\Seeder;

class ProductMusicExtraTableSeeder extends Seeder
{

    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {


        Capsule::table('product_music_extra')->truncate();

        Capsule::table('product_music_extra')->insert(array(
            0 =>
                array(
                    'artists_id' => 1,
                    'music_genre_id' => 1,
                    'products_id' => 166,
                    'record_company_id' => 0,
                ),
            1 =>
                array(
                    'artists_id' => 1,
                    'music_genre_id' => 2,
                    'products_id' => 169,
                    'record_company_id' => 1,
                ),
        ));


    }
}
