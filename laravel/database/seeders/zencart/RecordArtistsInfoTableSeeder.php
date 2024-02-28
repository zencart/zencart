<?php

namespace Database\Seeders\zencart;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RecordArtistsInfoTableSeeder extends Seeder
{

    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {


        DB::table('record_artists_info')->truncate();

        DB::table('record_artists_info')->insert(array(
            0 =>
                array(
                    'artists_id' => 1,
                    'artists_url' => 'www.russtippins.com/',
                    'date_last_click' => NULL,
                    'languages_id' => 1,
                    'url_clicked' => 0,
                ),
        ));


    }
}
