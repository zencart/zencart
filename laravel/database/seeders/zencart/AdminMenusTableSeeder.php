<?php

namespace Database\Seeders\zencart;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AdminMenusTableSeeder extends Seeder
{

    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {


        DB::table('admin_menus')->truncate();

        DB::table('admin_menus')->insert(array(
            0 =>
                array(
                    'language_key' => 'BOX_HEADING_ADMIN_ACCESS',
                    'menu_key' => 'access',
                    'sort_order' => 10,
                ),
            1 =>
                array(
                    'language_key' => 'BOX_HEADING_CATALOG',
                    'menu_key' => 'catalog',
                    'sort_order' => 2,
                ),
            2 =>
                array(
                    'language_key' => 'BOX_HEADING_CONFIGURATION',
                    'menu_key' => 'configuration',
                    'sort_order' => 1,
                ),
            3 =>
                array(
                    'language_key' => 'BOX_HEADING_CUSTOMERS',
                    'menu_key' => 'customers',
                    'sort_order' => 4,
                ),
            4 =>
                array(
                    'language_key' => 'BOX_HEADING_EXTRAS',
                    'menu_key' => 'extras',
                    'sort_order' => 11,
                ),
            5 =>
                array(
                    'language_key' => 'BOX_HEADING_GV_ADMIN',
                    'menu_key' => 'gv',
                    'sort_order' => 9,
                ),
            6 =>
                array(
                    'language_key' => 'BOX_HEADING_LOCALIZATION',
                    'menu_key' => 'localization',
                    'sort_order' => 6,
                ),
            7 =>
                array(
                    'language_key' => 'BOX_HEADING_MODULES',
                    'menu_key' => 'modules',
                    'sort_order' => 3,
                ),
            8 =>
                array(
                    'language_key' => 'BOX_HEADING_REPORTS',
                    'menu_key' => 'reports',
                    'sort_order' => 7,
                ),
            9 =>
                array(
                    'language_key' => 'BOX_HEADING_LOCATION_AND_TAXES',
                    'menu_key' => 'taxes',
                    'sort_order' => 5,
                ),
            10 =>
                array(
                    'language_key' => 'BOX_HEADING_TOOLS',
                    'menu_key' => 'tools',
                    'sort_order' => 8,
                ),
        ));


    }
}
