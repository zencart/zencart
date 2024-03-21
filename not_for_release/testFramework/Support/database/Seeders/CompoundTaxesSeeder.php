<?php

namespace Seeders;

use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Database\Seeder;

class CompoundTaxesSeeder extends Seeder
{

    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {
        Capsule::table('geo_zones')->insert(['geo_zone_name' => 'Canada', 'geo_zone_description' => 'Canada Compound']);
        $geoZone = capsule::getPdo()->lastInsertId();
        Capsule::table('zones_to_geo_zones')->insert(['zone_country_id' => 38, 'zone_id' => 0, 'geo_zone_id' => $geoZone]);
        Capsule::table('tax_rates')->insert(['tax_zone_id' => $geoZone, 'tax_class_id' => 1, 'tax_priority' => 1, 'tax_rate' => '3.000', 'tax_description' > 'CAD Compound 1']);
        Capsule::table('tax_rates')->insert(['tax_zone_id' => $geoZone, 'tax_class_id' => 1, 'tax_priority' => 2, 'tax_rate' => '8.000', 'tax_description' > 'CAD Compound 2']);
    }
}
