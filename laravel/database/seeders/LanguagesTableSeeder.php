<?php

namespace Database\Seeders;

use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Database\Seeder;

class LanguagesTableSeeder extends Seeder
{

    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {


        Capsule::table('languages')->truncate();

        Capsule::table('languages')->insert(array(
            0 =>
                array(
                    'code' => 'en',
                    'directory' => 'english',
                    'image' => 'icon.gif',
                    'languages_id' => 1,
                    'name' => 'English',
                    'sort_order' => 1,
                ),
        ));


    }
}
