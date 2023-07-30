<?php

namespace Database\Seeders;

use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Database\Seeder;

class ProductTypesTableSeeder extends Seeder
{

    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {


        Capsule::table('product_types')->truncate();

        Capsule::table('product_types')->insert(array(
            0 =>
                array(
                    'allow_add_to_cart' => 'Y',
                    'date_added' => '2023-06-29 12:14:04',
                    'default_image' => '',
                    'last_modified' => '2023-06-29 12:14:04',
                    'type_handler' => 'product',
                    'type_id' => 1,
                    'type_master_type' => 1,
                    'type_name' => 'Product - General',
                ),
            1 =>
                array(
                    'allow_add_to_cart' => 'Y',
                    'date_added' => '2023-06-29 12:14:04',
                    'default_image' => '',
                    'last_modified' => '2023-06-29 12:14:04',
                    'type_handler' => 'product_music',
                    'type_id' => 2,
                    'type_master_type' => 1,
                    'type_name' => 'Product - Music',
                ),
            2 =>
                array(
                    'allow_add_to_cart' => 'N',
                    'date_added' => '2023-06-29 12:14:04',
                    'default_image' => '',
                    'last_modified' => '2023-06-29 12:14:04',
                    'type_handler' => 'document_general',
                    'type_id' => 3,
                    'type_master_type' => 3,
                    'type_name' => 'Document - General',
                ),
            3 =>
                array(
                    'allow_add_to_cart' => 'Y',
                    'date_added' => '2023-06-29 12:14:04',
                    'default_image' => '',
                    'last_modified' => '2023-06-29 12:14:04',
                    'type_handler' => 'document_product',
                    'type_id' => 4,
                    'type_master_type' => 3,
                    'type_name' => 'Document - Product',
                ),
            4 =>
                array(
                    'allow_add_to_cart' => 'Y',
                    'date_added' => '2023-06-29 12:14:04',
                    'default_image' => '',
                    'last_modified' => '2023-06-29 12:14:04',
                    'type_handler' => 'product_free_shipping',
                    'type_id' => 5,
                    'type_master_type' => 1,
                    'type_name' => 'Product - Free Shipping',
                ),
        ));


    }
}
