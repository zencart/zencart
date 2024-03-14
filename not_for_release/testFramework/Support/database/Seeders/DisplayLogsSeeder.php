<?php

namespace Seeders;

use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Database\Seeder;

class DisplayLogsSeeder extends Seeder
{

    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {
        Capsule::table('configuration')->insert(['configuration_key' => 'DISPLAY_LOGS_MAX_DISPLAY', 'configuration_value' => '20', 'configuration_group_id' => 10, 'configuration_title' => 'foo', 'configuration_description' => 'foo']);
        Capsule::table('configuration')->insert(['configuration_key' => 'DISPLAY_LOGS_MAX_FILE_SIZE', 'configuration_value' => '80000', 'configuration_group_id' => 10, 'configuration_title' => 'foo', 'configuration_description' => 'foo']);
        Capsule::table('configuration')->insert(['configuration_key' => 'DISPLAY_LOGS_INCLUDED_FILES', 'configuration_value' => '', 'configuration_group_id' => 10, 'configuration_title' => 'foo', 'configuration_description' => 'foo']);
        Capsule::table('configuration')->insert(['configuration_key' => 'DISPLAY_LOGS_EXCLUDED_FILES', 'configuration_value' => '', 'configuration_group_id' => 10, 'configuration_title' => 'foo', 'configuration_description' => 'foo']);
    }
}
