<?php

namespace Database\Seeders\zencart;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProductsOptionsTypesTableSeeder extends Seeder
{

    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {


        DB::table('products_options_types')->truncate();

        DB::table('products_options_types')->insert(array(
            0 =>
                array(
                    'products_options_types_id' => 0,
                    'products_options_types_name' => 'Dropdown',
                ),
            1 =>
                array(
                    'products_options_types_id' => 1,
                    'products_options_types_name' => 'Text',
                ),
            2 =>
                array(
                    'products_options_types_id' => 2,
                    'products_options_types_name' => 'Radio',
                ),
            3 =>
                array(
                    'products_options_types_id' => 3,
                    'products_options_types_name' => 'Checkbox',
                ),
            4 =>
                array(
                    'products_options_types_id' => 4,
                    'products_options_types_name' => 'File',
                ),
            5 =>
                array(
                    'products_options_types_id' => 5,
                    'products_options_types_name' => 'Read Only',
                ),
        ));


    }
}
