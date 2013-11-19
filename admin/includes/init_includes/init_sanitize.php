<?php
/**
 * init_sanitize
 *
 * @package initSystem
 * @copyright Copyright 2003-2013 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: init_sanitize.php 18698 2011-05-04 14:50:06Z wilt $
 */
$saniGroup1 = array('action', 'add_products_id', 'attribute_id', 'attribute_page', 'attributes_id', 'banner', 'bID', 'box_name', 'build_cat', 'came_from', 'categories_update_id', 'cID', 'cid', 'configuration_key_lookup', 'copy_attributes',
'cpage', 'cPath', 'current_category_id', 'current', 'customer', 'debug', 'debug2', 'debug3', 'define_it', 'download_reset_off', 'download_reset_on', 'end_date', 'ezID', 'fID', 'filename', 'flag',
'flagbanners_on_ssl', 'flagbanners_open_new_windows', 'gID', 'gid', 'global', 'go_back', 'id', 'info', 'ipnID', 'keepslashes', 'layout_box_name', 'lID', 'list_order', 'language', 'lng_id', 'lngdir', 'mail_sent_to', 'manual',
'master_category', 'mID', 'mode', 'module', 'month', 'na', 'nID', 'nogrants', 'ns', 'number_of_uploads', 'oID', 'oldaction', 'option_id', 'option_order_by', 'option_page', 'options_id_from', 'options_id', 'order_by',
'order', 'origin', 'p', 'padID', 'page', 'pages_id', 'payment_status', 'paypal_ipn_sort_order', 'pID', 'ppage', 'product_type', 'products_filter_name_model', 'products_filter', 'products_id',
'products_options_id_all', 'products_update_id', 'profile', 'ptID', 'q', 'read', 'recip_count', 'referral_code', 'reports_page', 'reset_categories_products_sort_order', 'reset_editor', 'reset_ez_sort_order',
'reset_option_names_values_copier', 'rID', 's', 'saction', 'selected_box', 'set', 'set_display_categories_dropdown', 'sID', 'spage', 'start_date', 'status', 't', 'tID', 'type', 'uid', 'update_action', 'update_to', 'user',
'value_id', 'value_page', 'vcheck', 'year', 'za_lookup', 'zID', 'zone', 'zpage', 'coupon_copy_to');

foreach ($saniGroup1 as $key)
{
  if (isset($_GET[$key]))
  {
    $_GET[$key] = preg_replace('/[^\/0-9a-zA-Z_:@.-]/', '', $_GET[$key]);
  }
}