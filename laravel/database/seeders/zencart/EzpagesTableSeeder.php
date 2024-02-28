<?php

namespace Database\Seeders\zencart;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class EzpagesTableSeeder extends Seeder
{

    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {


        DB::table('ezpages')->truncate();

        DB::table('ezpages')->insert(array(
            0 =>
                array(
                    'alt_url' => '',
                    'alt_url_external' => '',
                    'footer_sort_order' => 0,
                    'header_sort_order' => 10,
                    'page_is_ssl' => 0,
                    'page_open_new_window' => 0,
                    'pages_id' => 1,
                    'sidebox_sort_order' => 0,
                    'status_footer' => 0,
                    'status_header' => 1,
                    'status_sidebox' => 0,
                    'status_toc' => 1,
                    'status_visible' => 0,
                    'toc_chapter' => 10,
                    'toc_sort_order' => 10,
                ),
            1 =>
                array(
                    'alt_url' => '',
                    'alt_url_external' => '',
                    'footer_sort_order' => 0,
                    'header_sort_order' => 0,
                    'page_is_ssl' => 0,
                    'page_open_new_window' => 0,
                    'pages_id' => 2,
                    'sidebox_sort_order' => 0,
                    'status_footer' => 0,
                    'status_header' => 0,
                    'status_sidebox' => 0,
                    'status_toc' => 1,
                    'status_visible' => 0,
                    'toc_chapter' => 10,
                    'toc_sort_order' => 30,
                ),
            2 =>
                array(
                    'alt_url' => '',
                    'alt_url_external' => '',
                    'footer_sort_order' => 0,
                    'header_sort_order' => 0,
                    'page_is_ssl' => 0,
                    'page_open_new_window' => 0,
                    'pages_id' => 3,
                    'sidebox_sort_order' => 10,
                    'status_footer' => 0,
                    'status_header' => 0,
                    'status_sidebox' => 1,
                    'status_toc' => 0,
                    'status_visible' => 0,
                    'toc_chapter' => 0,
                    'toc_sort_order' => 0,
                ),
            3 =>
                array(
                    'alt_url' => 'index.php?main_page=brands',
                    'alt_url_external' => '',
                    'footer_sort_order' => 0,
                    'header_sort_order' => 0,
                    'page_is_ssl' => 1,
                    'page_open_new_window' => 0,
                    'pages_id' => 4,
                    'sidebox_sort_order' => 5,
                    'status_footer' => 0,
                    'status_header' => 1,
                    'status_sidebox' => 0,
                    'status_toc' => 0,
                    'status_visible' => 0,
                    'toc_chapter' => 0,
                    'toc_sort_order' => 0,
                ),
            4 =>
                array(
                    'alt_url' => '',
                    'alt_url_external' => '',
                    'footer_sort_order' => 0,
                    'header_sort_order' => 0,
                    'page_is_ssl' => 0,
                    'page_open_new_window' => 0,
                    'pages_id' => 5,
                    'sidebox_sort_order' => 20,
                    'status_footer' => 0,
                    'status_header' => 0,
                    'status_sidebox' => 1,
                    'status_toc' => 0,
                    'status_visible' => 0,
                    'toc_chapter' => 0,
                    'toc_sort_order' => 0,
                ),
            5 =>
                array(
                    'alt_url' => '',
                    'alt_url_external' => '',
                    'footer_sort_order' => 50,
                    'header_sort_order' => 50,
                    'page_is_ssl' => 0,
                    'page_open_new_window' => 0,
                    'pages_id' => 6,
                    'sidebox_sort_order' => 50,
                    'status_footer' => 1,
                    'status_header' => 1,
                    'status_sidebox' => 1,
                    'status_toc' => 0,
                    'status_visible' => 0,
                    'toc_chapter' => 0,
                    'toc_sort_order' => 0,
                ),
            6 =>
                array(
                    'alt_url' => 'index.php?main_page=account',
                    'alt_url_external' => '',
                    'footer_sort_order' => 10,
                    'header_sort_order' => 0,
                    'page_is_ssl' => 1,
                    'page_open_new_window' => 0,
                    'pages_id' => 7,
                    'sidebox_sort_order' => 0,
                    'status_footer' => 1,
                    'status_header' => 0,
                    'status_sidebox' => 0,
                    'status_toc' => 0,
                    'status_visible' => 0,
                    'toc_chapter' => 0,
                    'toc_sort_order' => 0,
                ),
            7 =>
                array(
                    'alt_url' => 'index.php?main_page=site_map',
                    'alt_url_external' => '',
                    'footer_sort_order' => 20,
                    'header_sort_order' => 0,
                    'page_is_ssl' => 0,
                    'page_open_new_window' => 0,
                    'pages_id' => 8,
                    'sidebox_sort_order' => 40,
                    'status_footer' => 1,
                    'status_header' => 0,
                    'status_sidebox' => 1,
                    'status_toc' => 0,
                    'status_visible' => 0,
                    'toc_chapter' => 0,
                    'toc_sort_order' => 0,
                ),
            8 =>
                array(
                    'alt_url' => 'index.php?main_page=privacy',
                    'alt_url_external' => '',
                    'footer_sort_order' => 40,
                    'header_sort_order' => 30,
                    'page_is_ssl' => 0,
                    'page_open_new_window' => 0,
                    'pages_id' => 9,
                    'sidebox_sort_order' => 0,
                    'status_footer' => 1,
                    'status_header' => 1,
                    'status_sidebox' => 0,
                    'status_toc' => 0,
                    'status_visible' => 0,
                    'toc_chapter' => 0,
                    'toc_sort_order' => 0,
                ),
            9 =>
                array(
                    'alt_url' => '',
                    'alt_url_external' => 'https://www.zen-cart.com',
                    'footer_sort_order' => 0,
                    'header_sort_order' => 60,
                    'page_is_ssl' => 0,
                    'page_open_new_window' => 1,
                    'pages_id' => 10,
                    'sidebox_sort_order' => 0,
                    'status_footer' => 0,
                    'status_header' => 1,
                    'status_sidebox' => 0,
                    'status_toc' => 0,
                    'status_visible' => 0,
                    'toc_chapter' => 0,
                    'toc_sort_order' => 0,
                ),
            10 =>
                array(
                    'alt_url' => 'index.php?main_page=index&cPath=21',
                    'alt_url_external' => '',
                    'footer_sort_order' => 0,
                    'header_sort_order' => 0,
                    'page_is_ssl' => 0,
                    'page_open_new_window' => 0,
                    'pages_id' => 11,
                    'sidebox_sort_order' => 60,
                    'status_footer' => 0,
                    'status_header' => 0,
                    'status_sidebox' => 1,
                    'status_toc' => 0,
                    'status_visible' => 0,
                    'toc_chapter' => 0,
                    'toc_sort_order' => 0,
                ),
            11 =>
                array(
                    'alt_url' => 'index.php?main_page=index&cPath=3_10',
                    'alt_url_external' => '',
                    'footer_sort_order' => 60,
                    'header_sort_order' => 0,
                    'page_is_ssl' => 0,
                    'page_open_new_window' => 0,
                    'pages_id' => 12,
                    'sidebox_sort_order' => 0,
                    'status_footer' => 1,
                    'status_header' => 0,
                    'status_sidebox' => 0,
                    'status_toc' => 0,
                    'status_visible' => 0,
                    'toc_chapter' => 0,
                    'toc_sort_order' => 0,
                ),
            12 =>
                array(
                    'alt_url' => '',
                    'alt_url_external' => 'https://www.google.com',
                    'footer_sort_order' => 0,
                    'header_sort_order' => 0,
                    'page_is_ssl' => 0,
                    'page_open_new_window' => 1,
                    'pages_id' => 13,
                    'sidebox_sort_order' => 70,
                    'status_footer' => 0,
                    'status_header' => 0,
                    'status_sidebox' => 1,
                    'status_toc' => 0,
                    'status_visible' => 0,
                    'toc_chapter' => 0,
                    'toc_sort_order' => 0,
                ),
            13 =>
                array(
                    'alt_url' => '',
                    'alt_url_external' => '',
                    'footer_sort_order' => 0,
                    'header_sort_order' => 0,
                    'page_is_ssl' => 0,
                    'page_open_new_window' => 0,
                    'pages_id' => 14,
                    'sidebox_sort_order' => 0,
                    'status_footer' => 0,
                    'status_header' => 0,
                    'status_sidebox' => 0,
                    'status_toc' => 1,
                    'status_visible' => 0,
                    'toc_chapter' => 10,
                    'toc_sort_order' => 20,
                ),
        ));


    }
}
