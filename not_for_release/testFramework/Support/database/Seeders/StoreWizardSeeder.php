<?php

namespace Seeders;

use Tests\Services\Contracts\TestSeederInterface;
use Tests\Support\Database\TestDb;

class StoreWizardSeeder implements TestSeederInterface
{

    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run(array $parameters = []): void
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
