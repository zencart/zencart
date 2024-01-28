<?php

namespace Database\Seeders\zencart;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ManufacturersInfoTableSeeder extends Seeder
{

    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {


        DB::table('manufacturers_info')->truncate();

        DB::table('manufacturers_info')->insert(array(
            0 =>
                array(
                    'date_last_click' => NULL,
                    'languages_id' => 1,
                    'manufacturers_id' => 1,
                    'manufacturers_url' => 'http://www.matrox.com',
                    'url_clicked' => 0,
                ),
            1 =>
                array(
                    'date_last_click' => NULL,
                    'languages_id' => 1,
                    'manufacturers_id' => 2,
                    'manufacturers_url' => 'http://www.microsoft.com',
                    'url_clicked' => 0,
                ),
            2 =>
                array(
                    'date_last_click' => NULL,
                    'languages_id' => 1,
                    'manufacturers_id' => 3,
                    'manufacturers_url' => 'http://www.warner.com',
                    'url_clicked' => 0,
                ),
            3 =>
                array(
                    'date_last_click' => NULL,
                    'languages_id' => 1,
                    'manufacturers_id' => 4,
                    'manufacturers_url' => 'http://www.fox.com',
                    'url_clicked' => 0,
                ),
            4 =>
                array(
                    'date_last_click' => NULL,
                    'languages_id' => 1,
                    'manufacturers_id' => 5,
                    'manufacturers_url' => 'http://www.logitech.com',
                    'url_clicked' => 0,
                ),
            5 =>
                array(
                    'date_last_click' => NULL,
                    'languages_id' => 1,
                    'manufacturers_id' => 6,
                    'manufacturers_url' => 'http://www.canon.com',
                    'url_clicked' => 0,
                ),
            6 =>
                array(
                    'date_last_click' => NULL,
                    'languages_id' => 1,
                    'manufacturers_id' => 7,
                    'manufacturers_url' => 'http://www.sierra.com',
                    'url_clicked' => 0,
                ),
            7 =>
                array(
                    'date_last_click' => NULL,
                    'languages_id' => 1,
                    'manufacturers_id' => 8,
                    'manufacturers_url' => 'http://www.infogrames.com',
                    'url_clicked' => 0,
                ),
            8 =>
                array(
                    'date_last_click' => NULL,
                    'languages_id' => 1,
                    'manufacturers_id' => 9,
                    'manufacturers_url' => 'http://www.hewlettpackard.com',
                    'url_clicked' => 0,
                ),
        ));


    }
}
