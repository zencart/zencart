<?php

namespace Seeders;

use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Database\Seeder;

class StoreWizardSeeder extends Seeder
{

    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {
        Capsule::table('configuration')->where('configuration_key', 'STORE_NAME')->update(['configuration_value' => 'Zencart Store Name']);
        Capsule::table('configuration')->where('configuration_key', 'STORE_OWNER')->update(['configuration_value' => 'Zencart Store Owner']);
    }
}
