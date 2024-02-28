<?php

namespace Database\Seeders\zencart;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MediaTypesTableSeeder extends Seeder
{

    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {


        DB::table('media_types')->truncate();

        DB::table('media_types')->insert(array(
            0 =>
                array(
                    'type_ext' => '.mp3',
                    'type_id' => 1,
                    'type_name' => 'MP3',
                ),
        ));


    }
}
