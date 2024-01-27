<?php

namespace Database\Seeders\zencart;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ConfigurationGroupTableSeeder extends Seeder
{

    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {


        DB::table('configuration_group')->truncate();

        DB::table('configuration_group')->insert(array(
            0 =>
                array(
                    'configuration_group_description' => 'General information about my store',
                    'configuration_group_id' => 1,
                    'configuration_group_title' => 'My Store',
                    'sort_order' => 1,
                    'visible' => 1,
                ),
            1 =>
                array(
                    'configuration_group_description' => 'The minimum values for functions / data',
                    'configuration_group_id' => 2,
                    'configuration_group_title' => 'Minimum Values',
                    'sort_order' => 2,
                    'visible' => 1,
                ),
            2 =>
                array(
                    'configuration_group_description' => 'The maximum values for functions / data',
                    'configuration_group_id' => 3,
                    'configuration_group_title' => 'Maximum Values',
                    'sort_order' => 3,
                    'visible' => 1,
                ),
            3 =>
                array(
                    'configuration_group_description' => 'Image parameters',
                    'configuration_group_id' => 4,
                    'configuration_group_title' => 'Images',
                    'sort_order' => 4,
                    'visible' => 1,
                ),
            4 =>
                array(
                    'configuration_group_description' => 'Customer account configuration',
                    'configuration_group_id' => 5,
                    'configuration_group_title' => 'Customer Details',
                    'sort_order' => 5,
                    'visible' => 1,
                ),
            5 =>
                array(
                    'configuration_group_description' => 'Hidden from configuration',
                    'configuration_group_id' => 6,
                    'configuration_group_title' => 'Module Options',
                    'sort_order' => 6,
                    'visible' => 0,
                ),
            6 =>
                array(
                    'configuration_group_description' => 'Shipping options available at my store',
                    'configuration_group_id' => 7,
                    'configuration_group_title' => 'Shipping/Packaging',
                    'sort_order' => 7,
                    'visible' => 1,
                ),
            7 =>
                array(
                    'configuration_group_description' => 'Product Listing configuration options',
                    'configuration_group_id' => 8,
                    'configuration_group_title' => 'Product Listing',
                    'sort_order' => 8,
                    'visible' => 1,
                ),
            8 =>
                array(
                    'configuration_group_description' => 'Stock configuration options',
                    'configuration_group_id' => 9,
                    'configuration_group_title' => 'Stock',
                    'sort_order' => 9,
                    'visible' => 1,
                ),
            9 =>
                array(
                    'configuration_group_description' => 'Logging configuration options',
                    'configuration_group_id' => 10,
                    'configuration_group_title' => 'Logging',
                    'sort_order' => 10,
                    'visible' => 1,
                ),
            10 =>
                array(
                    'configuration_group_description' => 'Regulation options',
                    'configuration_group_id' => 11,
                    'configuration_group_title' => 'Regulations',
                    'sort_order' => 16,
                    'visible' => 1,
                ),
            11 =>
                array(
                    'configuration_group_description' => 'Email-related settings',
                    'configuration_group_id' => 12,
                    'configuration_group_title' => 'Email',
                    'sort_order' => 12,
                    'visible' => 1,
                ),
            12 =>
                array(
                    'configuration_group_description' => 'Configure products attributes settings',
                    'configuration_group_id' => 13,
                    'configuration_group_title' => 'Attribute Settings',
                    'sort_order' => 13,
                    'visible' => 1,
                ),
            13 =>
                array(
                    'configuration_group_description' => 'GZip compression options',
                    'configuration_group_id' => 14,
                    'configuration_group_title' => 'GZip Compression',
                    'sort_order' => 14,
                    'visible' => 1,
                ),
            14 =>
                array(
                    'configuration_group_description' => 'Session options',
                    'configuration_group_id' => 15,
                    'configuration_group_title' => 'Sessions',
                    'sort_order' => 15,
                    'visible' => 1,
                ),
            15 =>
                array(
                    'configuration_group_description' => 'Gift Vouchers and Coupons',
                    'configuration_group_id' => 16,
                    'configuration_group_title' => 'GV Coupons',
                    'sort_order' => 16,
                    'visible' => 1,
                ),
            16 =>
                array(
                    'configuration_group_description' => 'Credit Cards Accepted',
                    'configuration_group_id' => 17,
                    'configuration_group_title' => 'Credit Cards',
                    'sort_order' => 17,
                    'visible' => 1,
                ),
            17 =>
                array(
                    'configuration_group_description' => 'Product Info Display Options',
                    'configuration_group_id' => 18,
                    'configuration_group_title' => 'Product Info',
                    'sort_order' => 18,
                    'visible' => 1,
                ),
            18 =>
                array(
                    'configuration_group_description' => 'Layout Options',
                    'configuration_group_id' => 19,
                    'configuration_group_title' => 'Layout Settings',
                    'sort_order' => 19,
                    'visible' => 1,
                ),
            19 =>
                array(
                    'configuration_group_description' => 'Website Maintenance Options',
                    'configuration_group_id' => 20,
                    'configuration_group_title' => 'Website Maintenance',
                    'sort_order' => 20,
                    'visible' => 1,
                ),
            20 =>
                array(
                    'configuration_group_description' => 'New Products Listing',
                    'configuration_group_id' => 21,
                    'configuration_group_title' => 'New Listing',
                    'sort_order' => 21,
                    'visible' => 1,
                ),
            21 =>
                array(
                    'configuration_group_description' => 'Featured Products Listing',
                    'configuration_group_id' => 22,
                    'configuration_group_title' => 'Featured Listing',
                    'sort_order' => 22,
                    'visible' => 1,
                ),
            22 =>
                array(
                    'configuration_group_description' => 'All Products Listing',
                    'configuration_group_id' => 23,
                    'configuration_group_title' => 'All Listing',
                    'sort_order' => 23,
                    'visible' => 1,
                ),
            23 =>
                array(
                    'configuration_group_description' => 'Index Products Listing',
                    'configuration_group_id' => 24,
                    'configuration_group_title' => 'Index Listing',
                    'sort_order' => 24,
                    'visible' => 1,
                ),
            24 =>
                array(
                    'configuration_group_description' => 'Define Pages Options Settings',
                    'configuration_group_id' => 25,
                    'configuration_group_title' => 'Define Page Status',
                    'sort_order' => 25,
                    'visible' => 1,
                ),
            25 =>
                array(
                    'configuration_group_description' => 'EZ-Pages Settings',
                    'configuration_group_id' => 30,
                    'configuration_group_title' => 'EZ-Pages Settings',
                    'sort_order' => 30,
                    'visible' => 1,
                ),
        ));


    }
}
