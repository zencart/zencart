<?php

namespace Seeders;

use Tests\Support\Database\TestDb;

class CompoundTaxesSeeder
{

    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {
        $now = date('Y-m-d H:i:s');
        $geoZoneId = TestDb::insert('geo_zones', ['geo_zone_name' => 'Canada', 'geo_zone_description' => 'Canada Compound']);
        TestDb::insert('zones_to_geo_zones', ['zone_country_id' => 38, 'zone_id' => 0, 'geo_zone_id' => $geoZoneId]);
        $taxRateOneId = TestDb::insert('tax_rates', [
            'tax_zone_id' => $geoZoneId,
            'tax_class_id' => 1,
            'tax_priority' => 1,
            'tax_rate' => '3.000',
            'last_modified' => $now,
            'date_added' => $now,
        ]);
        $taxRateTwoId = TestDb::insert('tax_rates', [
            'tax_zone_id' => $geoZoneId,
            'tax_class_id' => 1,
            'tax_priority' => 2,
            'tax_rate' => '8.000',
            'last_modified' => $now,
            'date_added' => $now,
        ]);

        TestDb::insert('tax_rates_description', [
            'tax_rates_id' => $taxRateOneId,
            'language_id' => 1,
            'tax_description' => 'CAD Compound 1',
        ]);
        TestDb::insert('tax_rates_description', [
            'tax_rates_id' => $taxRateTwoId,
            'language_id' => 1,
            'tax_description' => 'CAD Compound 2',
        ]);
    }
}
