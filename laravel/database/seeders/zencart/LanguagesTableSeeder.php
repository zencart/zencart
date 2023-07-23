<?php

namespace Database\Seeders\zencart;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class LanguagesTableSeeder extends Seeder
{

    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {


        DB::table('languages')->truncate();

        DB::table('languages')->insert(array(
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
