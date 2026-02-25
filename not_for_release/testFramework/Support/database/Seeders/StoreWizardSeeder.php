<?php

namespace Seeders;

use Tests\Support\Database\TestDb;

class StoreWizardSeeder
{

    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {
        TestDb::update(
            'configuration',
            ['configuration_value' => 'Zencart Store Name'],
            'configuration_key = :config_key',
            [':config_key' => 'STORE_NAME']
        );
        TestDb::update(
            'configuration',
            ['configuration_value' => 'Zencart Store Owner'],
            'configuration_key = :config_key',
            [':config_key' => 'STORE_OWNER']
        );
    }
}
