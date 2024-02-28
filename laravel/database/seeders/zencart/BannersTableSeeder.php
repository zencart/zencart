<?php

namespace Database\Seeders\zencart;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BannersTableSeeder extends Seeder
{

    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {


        DB::table('banners')->truncate();

        DB::table('banners')->insert(array(
            0 =>
                array(
                    'banners_group' => 'Wide-Banners',
                    'banners_html_text' => '',
                    'banners_id' => 1,
                    'banners_image' => 'banners/zencart_468_60_02.gif',
                    'banners_on_ssl' => 1,
                    'banners_open_new_windows' => 1,
                    'banners_sort_order' => 0,
                    'banners_title' => 'Zen Cart',
                    'banners_url' => 'https://www.zen-cart.com',
                    'date_added' => '2004-01-11 20:59:12',
                    'date_scheduled' => NULL,
                    'date_status_change' => NULL,
                    'expires_date' => NULL,
                    'expires_impressions' => 0,
                    'status' => 1,
                ),
            1 =>
                array(
                    'banners_group' => 'SideBox-Banners',
                    'banners_html_text' => '',
                    'banners_id' => 2,
                    'banners_image' => 'banners/125zen_logo.gif',
                    'banners_on_ssl' => 1,
                    'banners_open_new_windows' => 1,
                    'banners_sort_order' => 0,
                    'banners_title' => 'Zen Cart the art of e-commerce',
                    'banners_url' => 'https://www.zen-cart.com',
                    'date_added' => '2004-01-11 20:59:12',
                    'date_scheduled' => NULL,
                    'date_status_change' => NULL,
                    'expires_date' => NULL,
                    'expires_impressions' => 0,
                    'status' => 1,
                ),
            2 =>
                array(
                    'banners_group' => 'SideBox-Banners',
                    'banners_html_text' => '',
                    'banners_id' => 3,
                    'banners_image' => 'banners/125x125_zen_logo.gif',
                    'banners_on_ssl' => 1,
                    'banners_open_new_windows' => 1,
                    'banners_sort_order' => 0,
                    'banners_title' => 'Zen Cart the art of e-commerce',
                    'banners_url' => 'https://www.zen-cart.com',
                    'date_added' => '2004-01-11 20:59:12',
                    'date_scheduled' => NULL,
                    'date_status_change' => NULL,
                    'expires_date' => NULL,
                    'expires_impressions' => 0,
                    'status' => 1,
                ),
            3 =>
                array(
                    'banners_group' => 'Wide-Banners',
                    'banners_html_text' => '',
                    'banners_id' => 4,
                    'banners_image' => 'banners/think_anim.gif',
                    'banners_on_ssl' => 1,
                    'banners_open_new_windows' => 1,
                    'banners_sort_order' => 0,
                    'banners_title' => 'if you have to think ... you haven\'t been Zenned!',
                    'banners_url' => 'https://www.zen-cart.com',
                    'date_added' => '2004-01-12 20:53:18',
                    'date_scheduled' => NULL,
                    'date_status_change' => NULL,
                    'expires_date' => NULL,
                    'expires_impressions' => 0,
                    'status' => 1,
                ),
            4 =>
                array(
                    'banners_group' => 'BannersAll',
                    'banners_html_text' => '',
                    'banners_id' => 5,
                    'banners_image' => 'banners/bw_zen_88wide.gif',
                    'banners_on_ssl' => 1,
                    'banners_open_new_windows' => 1,
                    'banners_sort_order' => 10,
                    'banners_title' => 'Zen Cart the art of e-commerce',
                    'banners_url' => 'https://www.zen-cart.com',
                    'date_added' => '2005-05-13 10:54:38',
                    'date_scheduled' => NULL,
                    'date_status_change' => NULL,
                    'expires_date' => NULL,
                    'expires_impressions' => 0,
                    'status' => 1,
                ),
            5 =>
                array(
                    'banners_group' => 'Wide-Banners',
                    'banners_html_text' => '<script><!--//<![CDATA[
var loc = \'//pan.zen-cart.com/display/group/1/\';
var rd = Math.floor(Math.random()*99999999999);
document.write ("<scr"+"ipt src=\'"+loc);
document.write (\'?rd=\' + rd);
document.write ("\'></scr"+"ipt>");
//]]>--></script>',
                    'banners_id' => 6,
                    'banners_image' => '',
                    'banners_on_ssl' => 1,
                    'banners_open_new_windows' => 1,
                    'banners_sort_order' => 0,
                    'banners_title' => 'Zen Cart Certified Services',
                    'banners_url' => 'https://www.zen-cart.com',
                    'date_added' => '2004-01-11 20:59:12',
                    'date_scheduled' => NULL,
                    'date_status_change' => NULL,
                    'expires_date' => NULL,
                    'expires_impressions' => 0,
                    'status' => 1,
                ),
            6 =>
                array(
                    'banners_group' => 'Wide-Banners',
                    'banners_html_text' => '',
                    'banners_id' => 7,
                    'banners_image' => 'banners/cardsvcs_468x60.gif',
                    'banners_on_ssl' => 1,
                    'banners_open_new_windows' => 1,
                    'banners_sort_order' => 0,
                    'banners_title' => 'Credit Card Processing',
                    'banners_url' => 'https://www.zen-cart.com/partners/square_promo',
                    'date_added' => '2005-05-13 10:54:38',
                    'date_scheduled' => NULL,
                    'date_status_change' => NULL,
                    'expires_date' => NULL,
                    'expires_impressions' => 0,
                    'status' => 1,
                ),
        ));


    }
}
