<?php

namespace Database\Seeders\zencart;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MediaClipsTableSeeder extends Seeder
{

    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {


        DB::table('media_clips')->truncate();

        DB::table('media_clips')->insert(array(
            0 =>
                array(
                    'clip_filename' => 'thehunter.mp3',
                    'clip_id' => 1,
                    'clip_type' => 1,
                    'date_added' => '2004-06-01 20:57:43',
                    'last_modified' => '0001-01-01 00:00:00',
                    'media_id' => 1,
                ),
            1 =>
                array(
                    'clip_filename' => 'thehunter.mp3',
                    'clip_id' => 6,
                    'clip_type' => 1,
                    'date_added' => '2004-07-13 00:45:09',
                    'last_modified' => '0001-01-01 00:00:00',
                    'media_id' => 2,
                ),
        ));


    }
}
