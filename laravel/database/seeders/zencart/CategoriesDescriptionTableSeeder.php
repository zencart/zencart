<?php

namespace Database\Seeders\zencart;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CategoriesDescriptionTableSeeder extends Seeder
{

    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {


        DB::table('categories_description')->truncate();

        DB::table('categories_description')->insert(array(
            0 =>
                array(
                    'categories_description' => 'We offer a variety of Hardware from printers to graphics cards and mice to keyboards.',
                    'categories_id' => 1,
                    'categories_name' => 'Hardware',
                    'language_id' => 1,
                ),
            1 =>
                array(
                    'categories_description' => 'Select from an exciting list of software titles. <br /><br />Not seeing a title that you are looking for?',
                    'categories_id' => 2,
                    'categories_name' => 'Software',
                    'language_id' => 1,
                ),
            2 =>
                array(
                    'categories_description' => 'We offer a variety of DVD movies enjoyable for the whole family.<br /><br />Please browse the various categories to find your favorite movie today!',
                    'categories_id' => 3,
                    'categories_name' => 'DVD Movies',
                    'language_id' => 1,
                ),
            3 =>
                array(
                    'categories_description' => '',
                    'categories_id' => 4,
                    'categories_name' => 'Graphics Cards',
                    'language_id' => 1,
                ),
            4 =>
                array(
                    'categories_description' => '',
                    'categories_id' => 5,
                    'categories_name' => 'Printers',
                    'language_id' => 1,
                ),
            5 =>
                array(
                    'categories_description' => '',
                    'categories_id' => 6,
                    'categories_name' => 'Monitors',
                    'language_id' => 1,
                ),
            6 =>
                array(
                    'categories_description' => '',
                    'categories_id' => 7,
                    'categories_name' => 'Speakers',
                    'language_id' => 1,
                ),
            7 =>
                array(
                    'categories_description' => '',
                    'categories_id' => 8,
                    'categories_name' => 'Keyboards',
                    'language_id' => 1,
                ),
            8 =>
                array(
                    'categories_description' => 'Pick the right mouse for your individual computer needs!<br /><br />Contact Us if you are looking for a particular mouse that we do not currently have in stock.',
                    'categories_id' => 9,
                    'categories_name' => 'Mice',
                    'language_id' => 1,
                ),
            9 =>
                array(
                    'categories_description' => '<p>Get into the action with our Action collection of DVD movies!<br /><br />Don\'t miss the excitement and order your\'s today!<br /><br /></p>',
                    'categories_id' => 10,
                    'categories_name' => 'Action',
                    'language_id' => 1,
                ),
            10 =>
                array(
                    'categories_description' => '',
                    'categories_id' => 11,
                    'categories_name' => 'Science Fiction',
                    'language_id' => 1,
                ),
            11 =>
                array(
                    'categories_description' => '',
                    'categories_id' => 12,
                    'categories_name' => 'Comedy',
                    'language_id' => 1,
                ),
            12 =>
                array(
                    'categories_description' => 'Something you can enjoy with children of all ages!',
                    'categories_id' => 13,
                    'categories_name' => 'Cartoons',
                    'language_id' => 1,
                ),
            13 =>
                array(
                    'categories_description' => '',
                    'categories_id' => 14,
                    'categories_name' => 'Thriller',
                    'language_id' => 1,
                ),
            14 =>
                array(
                    'categories_description' => '',
                    'categories_id' => 15,
                    'categories_name' => 'Drama',
                    'language_id' => 1,
                ),
            15 =>
                array(
                    'categories_description' => '',
                    'categories_id' => 16,
                    'categories_name' => 'Memory',
                    'language_id' => 1,
                ),
            16 =>
                array(
                    'categories_description' => '',
                    'categories_id' => 17,
                    'categories_name' => 'CDROM Drives',
                    'language_id' => 1,
                ),
            17 =>
                array(
                    'categories_description' => '',
                    'categories_id' => 18,
                    'categories_name' => 'Simulation',
                    'language_id' => 1,
                ),
            18 =>
                array(
                    'categories_description' => '',
                    'categories_id' => 19,
                    'categories_name' => 'Action',
                    'language_id' => 1,
                ),
            19 =>
                array(
                    'categories_description' => '',
                    'categories_id' => 20,
                    'categories_name' => 'Strategy',
                    'language_id' => 1,
                ),
            20 =>
                array(
                    'categories_description' => 'Send a Gift Certificate today!<br /><br />Gift Certificates are good for anything in the store.',
                    'categories_id' => 21,
                    'categories_name' => 'Gift Certificates',
                    'language_id' => 1,
                ),
            21 =>
                array(
                    'categories_description' => 'All of these products are &quot;Linked Products&quot;.<br /><br />This means that they appear in more than one Category.<br /><br />However, you only have to maintain the product in one place.<br /><br />The Master Product is used for pricing purposes.',
                    'categories_id' => 22,
                    'categories_name' => 'Big Linked',
                    'language_id' => 1,
                ),
            22 =>
                array(
                    'categories_description' => '',
                    'categories_id' => 23,
                    'categories_name' => 'Test Examples',
                    'language_id' => 1,
                ),
            23 =>
                array(
                    'categories_description' => '',
                    'categories_id' => 24,
                    'categories_name' => 'Free Call Stuff',
                    'language_id' => 1,
                ),
            24 =>
                array(
                    'categories_description' => '',
                    'categories_id' => 25,
                    'categories_name' => 'Test 10% by Attrib',
                    'language_id' => 1,
                ),
            25 =>
                array(
                    'categories_description' => '',
                    'categories_id' => 27,
                    'categories_name' => '$5.00 off',
                    'language_id' => 1,
                ),
            26 =>
                array(
                    'categories_description' => '',
                    'categories_id' => 28,
                    'categories_name' => 'Test 10%',
                    'language_id' => 1,
                ),
            27 =>
                array(
                    'categories_description' => '',
                    'categories_id' => 31,
                    'categories_name' => '10% off Skip',
                    'language_id' => 1,
                ),
            28 =>
                array(
                    'categories_description' => '',
                    'categories_id' => 32,
                    'categories_name' => '10% off Price',
                    'language_id' => 1,
                ),
            29 =>
                array(
                    'categories_description' => '<p>This is a top level category description.</p>',
                    'categories_id' => 33,
                    'categories_name' => 'A Top Level Cat',
                    'language_id' => 1,
                ),
            30 =>
                array(
                    'categories_description' => 'This is a sublevel category description.',
                    'categories_id' => 34,
                    'categories_name' => 'SubLevel 2 A',
                    'language_id' => 1,
                ),
            31 =>
                array(
                    'categories_description' => '',
                    'categories_id' => 35,
                    'categories_name' => 'SubLevel 2 B',
                    'language_id' => 1,
                ),
            32 =>
                array(
                    'categories_description' => '',
                    'categories_id' => 36,
                    'categories_name' => 'SubLevel 2 C',
                    'language_id' => 1,
                ),
            33 =>
                array(
                    'categories_description' => '',
                    'categories_id' => 37,
                    'categories_name' => 'Sub Sub Cat 2B1',
                    'language_id' => 1,
                ),
            34 =>
                array(
                    'categories_description' => '',
                    'categories_id' => 38,
                    'categories_name' => 'Sub Sub Cat 2B2',
                    'language_id' => 1,
                ),
            35 =>
                array(
                    'categories_description' => '',
                    'categories_id' => 39,
                    'categories_name' => 'Sub Sub Cat 2B3',
                    'language_id' => 1,
                ),
            36 =>
                array(
                    'categories_description' => 'This is a sub-sub level category description.',
                    'categories_id' => 40,
                    'categories_name' => 'Sub Sub Cat 2A1',
                    'language_id' => 1,
                ),
            37 =>
                array(
                    'categories_description' => '',
                    'categories_id' => 41,
                    'categories_name' => 'Sub Sub Cat 2C1',
                    'language_id' => 1,
                ),
            38 =>
                array(
                    'categories_description' => '',
                    'categories_id' => 42,
                    'categories_name' => 'Sub Sub Cat 2C3',
                    'language_id' => 1,
                ),
            39 =>
                array(
                    'categories_description' => '',
                    'categories_id' => 43,
                    'categories_name' => 'Sub Sub Cat 2A2',
                    'language_id' => 1,
                ),
            40 =>
                array(
                    'categories_description' => '',
                    'categories_id' => 44,
                    'categories_name' => 'Sub Sub Cat 2C2',
                    'language_id' => 1,
                ),
            41 =>
                array(
                    'categories_description' => '',
                    'categories_id' => 45,
                    'categories_name' => '10% off',
                    'language_id' => 1,
                ),
            42 =>
                array(
                    'categories_description' => '',
                    'categories_id' => 46,
                    'categories_name' => 'Set $100',
                    'language_id' => 1,
                ),
            43 =>
                array(
                    'categories_description' => '',
                    'categories_id' => 47,
                    'categories_name' => '10% off Attrib',
                    'language_id' => 1,
                ),
            44 =>
                array(
                    'categories_description' => '',
                    'categories_id' => 48,
                    'categories_name' => 'Sale Percentage',
                    'language_id' => 1,
                ),
            45 =>
                array(
                    'categories_description' => '',
                    'categories_id' => 49,
                    'categories_name' => 'Sale Deduction',
                    'language_id' => 1,
                ),
            46 =>
                array(
                    'categories_description' => '',
                    'categories_id' => 50,
                    'categories_name' => 'Sale New Price',
                    'language_id' => 1,
                ),
            47 =>
                array(
                    'categories_description' => '',
                    'categories_id' => 51,
                    'categories_name' => 'Set $100 Skip',
                    'language_id' => 1,
                ),
            48 =>
                array(
                    'categories_description' => '',
                    'categories_id' => 52,
                    'categories_name' => '$5.00 off Skip',
                    'language_id' => 1,
                ),
            49 =>
                array(
                    'categories_description' => '',
                    'categories_id' => 53,
                    'categories_name' => 'Big Unlinked',
                    'language_id' => 1,
                ),
            50 =>
                array(
                    'categories_description' => '<p>The New Products show many of the newest features that have been added to Zen Cart.<br /><br />Take the time to review these and the other Demo Products to better understand all the options and features that Zen Cart has to offer.</p>',
                    'categories_id' => 54,
                    'categories_name' => 'New v1.2',
                    'language_id' => 1,
                ),
            51 =>
                array(
                    'categories_description' => '<p>Discount Quantities can be set for Products or on the individual attributes.<br /><br />Discounts on the Product do NOT reflect on the attributes price.<br /><br />Only discounts based on Special and Sale Prices are applied to attribute prices.</p>',
                    'categories_id' => 55,
                    'categories_name' => 'Discount Qty',
                    'language_id' => 1,
                ),
            52 =>
                array(
                    'categories_description' => '',
                    'categories_id' => 56,
                    'categories_name' => 'Attributes',
                    'language_id' => 1,
                ),
            53 =>
                array(
                    'categories_description' => '',
                    'categories_id' => 57,
                    'categories_name' => 'Text Pricing',
                    'language_id' => 1,
                ),
            54 =>
                array(
                    'categories_description' => '',
                    'categories_id' => 58,
                    'categories_name' => 'Real Sale',
                    'language_id' => 1,
                ),
            55 =>
                array(
                    'categories_description' => '',
                    'categories_id' => 60,
                    'categories_name' => 'Downloads',
                    'language_id' => 1,
                ),
            56 =>
                array(
                    'categories_description' => '',
                    'categories_id' => 61,
                    'categories_name' => 'Real',
                    'language_id' => 1,
                ),
            57 =>
                array(
                    'categories_description' => '',
                    'categories_id' => 62,
                    'categories_name' => 'Music',
                    'language_id' => 1,
                ),
            58 =>
                array(
                    'categories_description' => 'Documents can now be added to the category tree. For example you may want to add servicing/Technical documents. Or use Documents as an integrated FAQ system on your site. The implemetation here is fairly spartan, but could be expanded to offer PDF downloads, links to purchaseable download files. The possibilities are endless and left to your imagination.',
                    'categories_id' => 63,
                    'categories_name' => 'Documents',
                    'language_id' => 1,
                ),
            59 =>
                array(
                    'categories_description' => 'This is a category with mixed product types.

This includes both products and documents. There are two types of documents - Documents that are for reading and Documents that are for reading and purchasing.',
                    'categories_id' => 64,
                    'categories_name' => 'Mixed Product Types',
                    'language_id' => 1,
                ),
        ));


    }
}
