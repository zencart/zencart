<?php

namespace Seeders\InitialSeeders;

use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Database\Seeder;

class GeoZonesTableSeeder extends Seeder
{

    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {


        Capsule::table('geo_zones')->truncate();

        Capsule::table('geo_zones')->insert(array(
            0 =>
                array(
                    'date_added' => '2023-06-29 12:14:04',
                    'geo_zone_description' => 'Florida local sales tax zone',
                    'geo_zone_id' => 1,
                    'geo_zone_name' => 'Florida',
                    'last_modified' => NULL,
                ),
        ));


    }
}
