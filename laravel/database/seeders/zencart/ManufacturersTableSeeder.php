<?php

namespace Database\Seeders\zencart;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ManufacturersTableSeeder extends Seeder
{

    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {


        DB::table('manufacturers')->truncate();

        DB::table('manufacturers')->insert(array(
            0 =>
                array(
                    'date_added' => '2003-12-23 03:18:19',
                    'featured' => 0,
                    'last_modified' => NULL,
                    'manufacturers_id' => 1,
                    'manufacturers_image' => 'manufacturers/manufacturer_matrox.gif',
                    'manufacturers_name' => 'Matrox',
                ),
            1 =>
                array(
                    'date_added' => '2003-12-23 03:18:19',
                    'featured' => 0,
                    'last_modified' => NULL,
                    'manufacturers_id' => 2,
                    'manufacturers_image' => 'manufacturers/manufacturer_microsoft.gif',
                    'manufacturers_name' => 'Microsoft',
                ),
            2 =>
                array(
                    'date_added' => '2003-12-23 03:18:19',
                    'featured' => 0,
                    'last_modified' => NULL,
                    'manufacturers_id' => 3,
                    'manufacturers_image' => 'manufacturers/manufacturer_warner.gif',
                    'manufacturers_name' => 'Warner',
                ),
            3 =>
                array(
                    'date_added' => '2003-12-23 03:18:19',
                    'featured' => 0,
                    'last_modified' => NULL,
                    'manufacturers_id' => 4,
                    'manufacturers_image' => 'manufacturers/manufacturer_fox.gif',
                    'manufacturers_name' => 'Fox',
                ),
            4 =>
                array(
                    'date_added' => '2003-12-23 03:18:19',
                    'featured' => 0,
                    'last_modified' => NULL,
                    'manufacturers_id' => 5,
                    'manufacturers_image' => 'manufacturers/manufacturer_logitech.gif',
                    'manufacturers_name' => 'Logitech',
                ),
            5 =>
                array(
                    'date_added' => '2003-12-23 03:18:19',
                    'featured' => 0,
                    'last_modified' => NULL,
                    'manufacturers_id' => 6,
                    'manufacturers_image' => 'manufacturers/manufacturer_canon.gif',
                    'manufacturers_name' => 'Canon',
                ),
            6 =>
                array(
                    'date_added' => '2003-12-23 03:18:19',
                    'featured' => 0,
                    'last_modified' => NULL,
                    'manufacturers_id' => 7,
                    'manufacturers_image' => 'manufacturers/manufacturer_sierra.gif',
                    'manufacturers_name' => 'Sierra',
                ),
            7 =>
                array(
                    'date_added' => '2003-12-23 03:18:19',
                    'featured' => 0,
                    'last_modified' => NULL,
                    'manufacturers_id' => 8,
                    'manufacturers_image' => 'manufacturers/manufacturer_gt_interactive.gif',
                    'manufacturers_name' => 'GT Interactive',
                ),
            8 =>
                array(
                    'date_added' => '2003-12-23 03:18:19',
                    'featured' => 0,
                    'last_modified' => NULL,
                    'manufacturers_id' => 9,
                    'manufacturers_image' => 'manufacturers/manufacturer_hewlett_packard.gif',
                    'manufacturers_name' => 'Hewlett Packard',
                ),
        ));


    }
}
