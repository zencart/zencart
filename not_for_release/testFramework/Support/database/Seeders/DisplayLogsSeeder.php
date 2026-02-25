<?php

namespace Seeders;

use Tests\Support\Database\TestDb;

class DisplayLogsSeeder
{

    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {
        TestDb::insert('configuration', ['configuration_key' => 'DISPLAY_LOGS_MAX_DISPLAY', 'configuration_value' => '20', 'configuration_group_id' => 10, 'configuration_title' => 'foo', 'configuration_description' => 'foo']);
        TestDb::insert('configuration', ['configuration_key' => 'DISPLAY_LOGS_MAX_FILE_SIZE', 'configuration_value' => '80000', 'configuration_group_id' => 10, 'configuration_title' => 'foo', 'configuration_description' => 'foo']);
        TestDb::insert('configuration', ['configuration_key' => 'DISPLAY_LOGS_INCLUDED_FILES', 'configuration_value' => '', 'configuration_group_id' => 10, 'configuration_title' => 'foo', 'configuration_description' => 'foo']);
        TestDb::insert('configuration', ['configuration_key' => 'DISPLAY_LOGS_EXCLUDED_FILES', 'configuration_value' => '', 'configuration_group_id' => 10, 'configuration_title' => 'foo', 'configuration_description' => 'foo']);
    }
}
