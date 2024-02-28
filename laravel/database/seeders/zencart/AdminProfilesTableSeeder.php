<?php

namespace Database\Seeders\zencart;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AdminProfilesTableSeeder extends Seeder
{

    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {


        DB::table('admin_profiles')->truncate();

        DB::table('admin_profiles')->insert(array(
            0 =>
                array(
                    'profile_id' => 2,
                    'profile_name' => 'Order Processing',
                ),
            1 =>
                array(
                    'profile_id' => 1,
                    'profile_name' => 'Superuser',
                ),
        ));


    }
}
