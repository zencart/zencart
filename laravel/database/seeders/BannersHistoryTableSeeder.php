<?php

namespace Database\Seeders;

use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Database\Seeder;

class BannersHistoryTableSeeder extends Seeder
{

    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {


        Capsule::table('banners_history')->truncate();

        Capsule::table('banners_history')->insert(array(
            0 =>
                array(
                    'banners_clicked' => 0,
                    'banners_history_date' => '2023-06-29 12:14:41',
                    'banners_history_id' => 1,
                    'banners_id' => 3,
                    'banners_shown' => 1,
                ),
            1 =>
                array(
                    'banners_clicked' => 0,
                    'banners_history_date' => '2023-06-29 12:14:41',
                    'banners_history_id' => 2,
                    'banners_id' => 5,
                    'banners_shown' => 1,
                ),
            2 =>
                array(
                    'banners_clicked' => 0,
                    'banners_history_date' => '2023-06-29 12:14:41',
                    'banners_history_id' => 3,
                    'banners_id' => 3,
                    'banners_shown' => 1,
                ),
            3 =>
                array(
                    'banners_clicked' => 0,
                    'banners_history_date' => '2023-06-29 12:14:41',
                    'banners_history_id' => 4,
                    'banners_id' => 1,
                    'banners_shown' => 1,
                ),
        ));


    }
}
