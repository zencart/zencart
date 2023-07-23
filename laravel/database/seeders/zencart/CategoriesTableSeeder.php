<?php

namespace Database\Seeders\zencart;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CategoriesTableSeeder extends Seeder
{

    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {


        DB::table('categories')->truncate();

        DB::table('categories')->insert(array(
            0 =>
                array(
                    'categories_id' => 1,
                    'categories_image' => 'categories/category_hardware.gif',
                    'categories_status' => 1,
                    'date_added' => '2003-12-23 03:18:19',
                    'last_modified' => '2004-05-21 00:32:17',
                    'parent_id' => 0,
                    'sort_order' => 1,
                ),
            1 =>
                array(
                    'categories_id' => 2,
                    'categories_image' => 'categories/category_software.gif',
                    'categories_status' => 1,
                    'date_added' => '2003-12-23 03:18:19',
                    'last_modified' => '2004-05-22 21:14:57',
                    'parent_id' => 0,
                    'sort_order' => 2,
                ),
            2 =>
                array(
                    'categories_id' => 3,
                    'categories_image' => 'categories/category_dvd_movies.gif',
                    'categories_status' => 1,
                    'date_added' => '2003-12-23 03:18:19',
                    'last_modified' => '2004-05-21 00:22:39',
                    'parent_id' => 0,
                    'sort_order' => 3,
                ),
            3 =>
                array(
                    'categories_id' => 4,
                    'categories_image' => 'categories/subcategory_graphic_cards.gif',
                    'categories_status' => 1,
                    'date_added' => '2003-12-23 03:18:19',
                    'last_modified' => NULL,
                    'parent_id' => 1,
                    'sort_order' => 0,
                ),
            4 =>
                array(
                    'categories_id' => 5,
                    'categories_image' => 'categories/subcategory_printers.gif',
                    'categories_status' => 1,
                    'date_added' => '2003-12-23 03:18:19',
                    'last_modified' => NULL,
                    'parent_id' => 1,
                    'sort_order' => 0,
                ),
            5 =>
                array(
                    'categories_id' => 6,
                    'categories_image' => 'categories/subcategory_monitors.gif',
                    'categories_status' => 1,
                    'date_added' => '2003-12-23 03:18:19',
                    'last_modified' => NULL,
                    'parent_id' => 1,
                    'sort_order' => 0,
                ),
            6 =>
                array(
                    'categories_id' => 7,
                    'categories_image' => 'categories/subcategory_speakers.gif',
                    'categories_status' => 1,
                    'date_added' => '2003-12-23 03:18:19',
                    'last_modified' => NULL,
                    'parent_id' => 1,
                    'sort_order' => 0,
                ),
            7 =>
                array(
                    'categories_id' => 8,
                    'categories_image' => 'categories/subcategory_keyboards.gif',
                    'categories_status' => 1,
                    'date_added' => '2003-12-23 03:18:19',
                    'last_modified' => NULL,
                    'parent_id' => 1,
                    'sort_order' => 0,
                ),
            8 =>
                array(
                    'categories_id' => 9,
                    'categories_image' => 'categories/subcategory_mice.gif',
                    'categories_status' => 1,
                    'date_added' => '2003-12-23 03:18:19',
                    'last_modified' => '2004-05-21 00:34:10',
                    'parent_id' => 1,
                    'sort_order' => 0,
                ),
            9 =>
                array(
                    'categories_id' => 10,
                    'categories_image' => 'categories/subcategory_action.gif',
                    'categories_status' => 1,
                    'date_added' => '2003-12-23 03:18:19',
                    'last_modified' => '2004-05-21 00:39:17',
                    'parent_id' => 3,
                    'sort_order' => 0,
                ),
            10 =>
                array(
                    'categories_id' => 11,
                    'categories_image' => 'categories/subcategory_science_fiction.gif',
                    'categories_status' => 1,
                    'date_added' => '2003-12-23 03:18:19',
                    'last_modified' => NULL,
                    'parent_id' => 3,
                    'sort_order' => 0,
                ),
            11 =>
                array(
                    'categories_id' => 12,
                    'categories_image' => 'categories/subcategory_comedy.gif',
                    'categories_status' => 1,
                    'date_added' => '2003-12-23 03:18:19',
                    'last_modified' => NULL,
                    'parent_id' => 3,
                    'sort_order' => 0,
                ),
            12 =>
                array(
                    'categories_id' => 13,
                    'categories_image' => 'categories/subcategory_cartoons.gif',
                    'categories_status' => 1,
                    'date_added' => '2003-12-23 03:18:19',
                    'last_modified' => '2004-05-21 00:23:13',
                    'parent_id' => 3,
                    'sort_order' => 0,
                ),
            13 =>
                array(
                    'categories_id' => 14,
                    'categories_image' => 'categories/subcategory_thriller.gif',
                    'categories_status' => 1,
                    'date_added' => '2003-12-23 03:18:19',
                    'last_modified' => NULL,
                    'parent_id' => 3,
                    'sort_order' => 0,
                ),
            14 =>
                array(
                    'categories_id' => 15,
                    'categories_image' => 'categories/subcategory_drama.gif',
                    'categories_status' => 1,
                    'date_added' => '2003-12-23 03:18:19',
                    'last_modified' => NULL,
                    'parent_id' => 3,
                    'sort_order' => 0,
                ),
            15 =>
                array(
                    'categories_id' => 16,
                    'categories_image' => 'categories/subcategory_memory.gif',
                    'categories_status' => 1,
                    'date_added' => '2003-12-23 03:18:19',
                    'last_modified' => NULL,
                    'parent_id' => 1,
                    'sort_order' => 0,
                ),
            16 =>
                array(
                    'categories_id' => 17,
                    'categories_image' => 'categories/subcategory_cdrom_drives.gif',
                    'categories_status' => 1,
                    'date_added' => '2003-12-23 03:18:19',
                    'last_modified' => NULL,
                    'parent_id' => 1,
                    'sort_order' => 0,
                ),
            17 =>
                array(
                    'categories_id' => 18,
                    'categories_image' => 'categories/subcategory_simulation.gif',
                    'categories_status' => 1,
                    'date_added' => '2003-12-23 03:18:19',
                    'last_modified' => NULL,
                    'parent_id' => 2,
                    'sort_order' => 0,
                ),
            18 =>
                array(
                    'categories_id' => 19,
                    'categories_image' => 'categories/subcategory_action_games.gif',
                    'categories_status' => 1,
                    'date_added' => '2003-12-23 03:18:19',
                    'last_modified' => NULL,
                    'parent_id' => 2,
                    'sort_order' => 0,
                ),
            19 =>
                array(
                    'categories_id' => 20,
                    'categories_image' => 'categories/subcategory_strategy.gif',
                    'categories_status' => 1,
                    'date_added' => '2003-12-23 03:18:19',
                    'last_modified' => NULL,
                    'parent_id' => 2,
                    'sort_order' => 0,
                ),
            20 =>
                array(
                    'categories_id' => 21,
                    'categories_image' => 'categories/gv_25.gif',
                    'categories_status' => 1,
                    'date_added' => '2003-12-23 03:18:19',
                    'last_modified' => '2004-05-21 00:26:06',
                    'parent_id' => 0,
                    'sort_order' => 4,
                ),
            21 =>
                array(
                    'categories_id' => 22,
                    'categories_image' => 'categories/box_of_color.gif',
                    'categories_status' => 1,
                    'date_added' => '2003-12-23 03:18:19',
                    'last_modified' => '2004-05-21 00:28:43',
                    'parent_id' => 0,
                    'sort_order' => 5,
                ),
            22 =>
                array(
                    'categories_id' => 23,
                    'categories_image' => 'waybkgnd.gif',
                    'categories_status' => 1,
                    'date_added' => '2003-12-28 02:26:19',
                    'last_modified' => '2003-12-29 23:21:35',
                    'parent_id' => 0,
                    'sort_order' => 500,
                ),
            23 =>
                array(
                    'categories_id' => 24,
                    'categories_image' => 'categories/category_free.gif',
                    'categories_status' => 1,
                    'date_added' => '2003-12-28 11:48:46',
                    'last_modified' => '2004-01-02 19:13:45',
                    'parent_id' => 0,
                    'sort_order' => 600,
                ),
            24 =>
                array(
                    'categories_id' => 25,
                    'categories_image' => 'sample_image.gif',
                    'categories_status' => 1,
                    'date_added' => '2003-12-31 02:39:17',
                    'last_modified' => '2004-01-24 01:49:12',
                    'parent_id' => 0,
                    'sort_order' => 515,
                ),
            25 =>
                array(
                    'categories_id' => 27,
                    'categories_image' => 'sample_image.gif',
                    'categories_status' => 1,
                    'date_added' => '2004-01-04 14:13:08',
                    'last_modified' => '2004-01-24 16:16:23',
                    'parent_id' => 49,
                    'sort_order' => 10,
                ),
            26 =>
                array(
                    'categories_id' => 28,
                    'categories_image' => 'sample_image.gif',
                    'categories_status' => 1,
                    'date_added' => '2004-01-04 17:13:47',
                    'last_modified' => '2004-01-05 23:54:23',
                    'parent_id' => 0,
                    'sort_order' => 510,
                ),
            27 =>
                array(
                    'categories_id' => 31,
                    'categories_image' => 'sample_image.gif',
                    'categories_status' => 1,
                    'date_added' => '2004-01-04 23:16:46',
                    'last_modified' => '2004-01-24 01:48:29',
                    'parent_id' => 48,
                    'sort_order' => 30,
                ),
            28 =>
                array(
                    'categories_id' => 32,
                    'categories_image' => 'sample_image.gif',
                    'categories_status' => 1,
                    'date_added' => '2004-01-05 01:34:56',
                    'last_modified' => '2004-01-24 01:48:36',
                    'parent_id' => 48,
                    'sort_order' => 40,
                ),
            29 =>
                array(
                    'categories_id' => 33,
                    'categories_image' => 'categories/subcategory.gif',
                    'categories_status' => 1,
                    'date_added' => '2004-01-05 02:08:31',
                    'last_modified' => '2004-05-20 10:35:31',
                    'parent_id' => 0,
                    'sort_order' => 700,
                ),
            30 =>
                array(
                    'categories_id' => 34,
                    'categories_image' => 'categories/subcategory.gif',
                    'categories_status' => 1,
                    'date_added' => '2004-01-05 02:08:50',
                    'last_modified' => '2004-05-20 10:35:57',
                    'parent_id' => 33,
                    'sort_order' => 10,
                ),
            31 =>
                array(
                    'categories_id' => 35,
                    'categories_image' => 'categories/subcategory.gif',
                    'categories_status' => 1,
                    'date_added' => '2004-01-05 02:09:01',
                    'last_modified' => '2004-01-24 00:07:33',
                    'parent_id' => 33,
                    'sort_order' => 20,
                ),
            32 =>
                array(
                    'categories_id' => 36,
                    'categories_image' => 'categories/subcategory.gif',
                    'categories_status' => 1,
                    'date_added' => '2004-01-05 02:09:12',
                    'last_modified' => '2004-01-24 00:07:41',
                    'parent_id' => 33,
                    'sort_order' => 30,
                ),
            33 =>
                array(
                    'categories_id' => 37,
                    'categories_image' => 'categories/subcategory.gif',
                    'categories_status' => 1,
                    'date_added' => '2004-01-05 02:09:28',
                    'last_modified' => '2004-01-24 00:22:39',
                    'parent_id' => 35,
                    'sort_order' => 10,
                ),
            34 =>
                array(
                    'categories_id' => 38,
                    'categories_image' => 'categories/subcategory.gif',
                    'categories_status' => 1,
                    'date_added' => '2004-01-05 02:09:39',
                    'last_modified' => '2004-01-24 00:22:46',
                    'parent_id' => 35,
                    'sort_order' => 20,
                ),
            35 =>
                array(
                    'categories_id' => 39,
                    'categories_image' => 'categories/subcategory.gif',
                    'categories_status' => 1,
                    'date_added' => '2004-01-05 02:09:49',
                    'last_modified' => '2004-01-24 00:22:53',
                    'parent_id' => 35,
                    'sort_order' => 30,
                ),
            36 =>
                array(
                    'categories_id' => 40,
                    'categories_image' => 'categories/subcategory.gif',
                    'categories_status' => 1,
                    'date_added' => '2004-01-05 02:17:27',
                    'last_modified' => '2004-05-20 10:36:19',
                    'parent_id' => 34,
                    'sort_order' => 10,
                ),
            37 =>
                array(
                    'categories_id' => 41,
                    'categories_image' => 'categories/subcategory.gif',
                    'categories_status' => 1,
                    'date_added' => '2004-01-05 02:21:02',
                    'last_modified' => '2004-01-24 00:23:04',
                    'parent_id' => 36,
                    'sort_order' => 10,
                ),
            38 =>
                array(
                    'categories_id' => 42,
                    'categories_image' => 'categories/subcategory.gif',
                    'categories_status' => 1,
                    'date_added' => '2004-01-05 02:21:14',
                    'last_modified' => '2004-01-24 00:23:18',
                    'parent_id' => 36,
                    'sort_order' => 30,
                ),
            39 =>
                array(
                    'categories_id' => 43,
                    'categories_image' => 'categories/subcategory.gif',
                    'categories_status' => 1,
                    'date_added' => '2004-01-05 02:21:29',
                    'last_modified' => '2004-01-24 00:21:37',
                    'parent_id' => 34,
                    'sort_order' => 20,
                ),
            40 =>
                array(
                    'categories_id' => 44,
                    'categories_image' => 'categories/subcategory.gif',
                    'categories_status' => 1,
                    'date_added' => '2004-01-05 02:21:47',
                    'last_modified' => '2004-01-24 00:23:11',
                    'parent_id' => 36,
                    'sort_order' => 20,
                ),
            41 =>
                array(
                    'categories_id' => 45,
                    'categories_image' => 'sample_image.gif',
                    'categories_status' => 1,
                    'date_added' => '2004-01-05 23:54:56',
                    'last_modified' => '2004-01-24 01:48:22',
                    'parent_id' => 48,
                    'sort_order' => 10,
                ),
            42 =>
                array(
                    'categories_id' => 46,
                    'categories_image' => 'sample_image.gif',
                    'categories_status' => 1,
                    'date_added' => '2004-01-06 00:01:48',
                    'last_modified' => '2004-01-24 01:39:56',
                    'parent_id' => 50,
                    'sort_order' => 10,
                ),
            43 =>
                array(
                    'categories_id' => 47,
                    'categories_image' => 'sample_image.gif',
                    'categories_status' => 1,
                    'date_added' => '2004-01-06 03:09:57',
                    'last_modified' => '2004-01-24 01:48:05',
                    'parent_id' => 48,
                    'sort_order' => 20,
                ),
            44 =>
                array(
                    'categories_id' => 48,
                    'categories_image' => 'sample_image.gif',
                    'categories_status' => 1,
                    'date_added' => '2004-01-07 02:24:07',
                    'last_modified' => '2004-01-07 02:44:26',
                    'parent_id' => 0,
                    'sort_order' => 1000,
                ),
            45 =>
                array(
                    'categories_id' => 49,
                    'categories_image' => 'sample_image.gif',
                    'categories_status' => 1,
                    'date_added' => '2004-01-07 02:27:31',
                    'last_modified' => '2004-01-07 02:44:34',
                    'parent_id' => 0,
                    'sort_order' => 1100,
                ),
            46 =>
                array(
                    'categories_id' => 50,
                    'categories_image' => 'sample_image.gif',
                    'categories_status' => 1,
                    'date_added' => '2004-01-07 02:28:18',
                    'last_modified' => '2004-01-07 02:47:19',
                    'parent_id' => 0,
                    'sort_order' => 1200,
                ),
            47 =>
                array(
                    'categories_id' => 51,
                    'categories_image' => 'sample_image.gif',
                    'categories_status' => 1,
                    'date_added' => '2004-01-07 02:33:55',
                    'last_modified' => '2004-01-24 01:40:05',
                    'parent_id' => 50,
                    'sort_order' => 20,
                ),
            48 =>
                array(
                    'categories_id' => 52,
                    'categories_image' => 'sample_image.gif',
                    'categories_status' => 1,
                    'date_added' => '2004-01-24 16:09:35',
                    'last_modified' => '2004-01-24 16:16:33',
                    'parent_id' => 49,
                    'sort_order' => 20,
                ),
            49 =>
                array(
                    'categories_id' => 53,
                    'categories_image' => 'categories/subcategory.gif',
                    'categories_status' => 1,
                    'date_added' => '2004-04-25 23:07:41',
                    'last_modified' => NULL,
                    'parent_id' => 0,
                    'sort_order' => 1500,
                ),
            50 =>
                array(
                    'categories_id' => 54,
                    'categories_image' => 'categories/subcategory.gif',
                    'categories_status' => 1,
                    'date_added' => '2004-04-26 12:02:35',
                    'last_modified' => '2004-05-20 11:45:20',
                    'parent_id' => 0,
                    'sort_order' => 1510,
                ),
            51 =>
                array(
                    'categories_id' => 55,
                    'categories_image' => 'categories/subcategory.gif',
                    'categories_status' => 1,
                    'date_added' => '2004-04-28 01:48:47',
                    'last_modified' => '2004-05-20 11:45:51',
                    'parent_id' => 54,
                    'sort_order' => 0,
                ),
            52 =>
                array(
                    'categories_id' => 56,
                    'categories_image' => 'categories/subcategory.gif',
                    'categories_status' => 1,
                    'date_added' => '2004-04-28 01:49:16',
                    'last_modified' => '2004-04-28 01:53:14',
                    'parent_id' => 54,
                    'sort_order' => 0,
                ),
            53 =>
                array(
                    'categories_id' => 57,
                    'categories_image' => 'categories/subcategory.gif',
                    'categories_status' => 1,
                    'date_added' => '2004-05-01 01:29:13',
                    'last_modified' => NULL,
                    'parent_id' => 54,
                    'sort_order' => 0,
                ),
            54 =>
                array(
                    'categories_id' => 58,
                    'categories_image' => 'categories/subcategory.gif',
                    'categories_status' => 1,
                    'date_added' => '2004-05-02 12:35:02',
                    'last_modified' => '2004-05-18 10:46:13',
                    'parent_id' => 54,
                    'sort_order' => 110,
                ),
            55 =>
                array(
                    'categories_id' => 60,
                    'categories_image' => 'categories/subcategory.gif',
                    'categories_status' => 1,
                    'date_added' => '2004-05-02 23:45:21',
                    'last_modified' => NULL,
                    'parent_id' => 54,
                    'sort_order' => 0,
                ),
            56 =>
                array(
                    'categories_id' => 61,
                    'categories_image' => 'categories/subcategory.gif',
                    'categories_status' => 1,
                    'date_added' => '2004-05-18 10:13:46',
                    'last_modified' => '2004-05-18 10:46:02',
                    'parent_id' => 54,
                    'sort_order' => 100,
                ),
            57 =>
                array(
                    'categories_id' => 62,
                    'categories_image' => 'sample_image.gif',
                    'categories_status' => 1,
                    'date_added' => '2003-12-23 03:18:19',
                    'last_modified' => '2004-05-22 21:14:57',
                    'parent_id' => 0,
                    'sort_order' => 1520,
                ),
            58 =>
                array(
                    'categories_id' => 63,
                    'categories_image' => 'categories/subcategory.gif',
                    'categories_status' => 1,
                    'date_added' => '2003-12-23 03:18:19',
                    'last_modified' => '2004-07-12 17:45:24',
                    'parent_id' => 0,
                    'sort_order' => 1530,
                ),
            59 =>
                array(
                    'categories_id' => 64,
                    'categories_image' => 'categories/subcategory.gif',
                    'categories_status' => 1,
                    'date_added' => '2004-07-12 15:22:27',
                    'last_modified' => NULL,
                    'parent_id' => 0,
                    'sort_order' => 1550,
                ),
        ));


    }
}
