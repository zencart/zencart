<?php

namespace Seeders\InitialSeeders;

use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Database\Seeder;

class MusicGenreTableSeeder extends Seeder
{

    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {


        Capsule::table('music_genre')->truncate();

        Capsule::table('music_genre')->insert(array(
            0 =>
                array(
                    'date_added' => '2004-06-01 20:53:26',
                    'last_modified' => NULL,
                    'music_genre_id' => 1,
                    'music_genre_name' => 'Rock',
                ),
            1 =>
                array(
                    'date_added' => '2004-06-01 20:53:45',
                    'last_modified' => NULL,
                    'music_genre_id' => 2,
                    'music_genre_name' => 'Jazz',
                ),
        ));


    }
}
