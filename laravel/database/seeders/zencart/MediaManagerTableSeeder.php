<?php

namespace Database\Seeders\zencart;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MediaManagerTableSeeder extends Seeder
{

    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {


        DB::table('media_manager')->truncate();

        DB::table('media_manager')->insert(array(
            0 =>
                array(
                    'date_added' => '2004-06-01 20:42:53',
                    'last_modified' => '2004-06-01 20:57:43',
                    'media_id' => 1,
                    'media_name' => 'Russ Tippins - The Hunter',
                ),
            1 =>
                array(
                    'date_added' => '2004-07-12 17:57:45',
                    'last_modified' => '2004-07-13 01:01:14',
                    'media_id' => 2,
                    'media_name' => 'Help!',
                ),
        ));


    }
}
