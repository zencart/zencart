<?php
/**
 * main_template_vars_product_type.php  product_music-specific template vars
 * This file contains all the logic to prepare $vars for use in the product-type-specific template (in this case product_music)
 * It pulls data from all the related tables which collectively store the info related only to this product type.
 *
 * @copyright Copyright 2003-2020 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: John 2020 May 22 Modified in v1.5.7 $
 */
/*
 * This file contains all the logic to prepare $vars for use in the product-type-specific template (in this case product_music)
 * It pulls data from all the related tables which collectively store the info related only to this product type.
 */

  // This should be first line of the script:
  $zco_notifier->notify('NOTIFY_PRODUCT_TYPE_VARS_START_PRODUCT_MUSIC_INFO');

/**
 * Retrieve relevant data from relational tables, for the current products_id:
 */
    $tpl_page_body = '/tpl_product_music_info_display.php';

    $sql = "select * from " . TABLE_PRODUCT_MUSIC_EXTRA . "
            where products_id = '" . (int)$_GET['products_id'] . "'";

    $music_extras = $db->Execute($sql);

    $sql = "select * from " . TABLE_RECORD_ARTISTS . "
            where artists_id = '" . $music_extras->fields['artists_id'] . "'";

    $artist = $db->Execute($sql);

    $sql = "select * from " . TABLE_RECORD_ARTISTS_INFO . "
            where artists_id = '" . $music_extras->fields['artists_id'] . "'
            and languages_id = '" . (int)$_SESSION['languages_id'] . "'";

    $artist_info = $db->Execute($sql);

    $sql = "select * from " . TABLE_RECORD_COMPANY . "
            where record_company_id = '" . $music_extras->fields['record_company_id'] . "'";

    $record_company = $db->Execute($sql);

    $sql = "select * from " . TABLE_RECORD_COMPANY_INFO . "
            where record_company_id = '" . $music_extras->fields['record_company_id'] . "'
            and languages_id = '" . (int)$_SESSION['languages_id'] . "'";

    $record_company_info = $db->Execute($sql);


    $sql = "select * from " . TABLE_MUSIC_GENRE . "
            where music_genre_id = '" . $music_extras->fields['music_genre_id'] . "'";

    $music_genre = $db->Execute($sql);


/*
 * extract info from queries for use as template-variables:
 */
  $products_artist_name = !empty($artist->fields['artists_name']) ? $artist->fields['artists_name'] : '';
  $products_artist_url = !empty($artist_info->fields['artists_url']) ? $artist_info->fields['artists_url'] : '';
  $products_record_company_name = !empty($record_company->fields['record_company_name']) ? $record_company->fields['record_company_name'] : '';
  $products_record_company_url = !empty($record_company_info->fields['record_company_url']) ? $record_company_info->fields['record_company_url'] : '';
  $products_music_genre_name = !empty($music_genre->fields['music_genre_name']) ? $music_genre->fields['music_genre_name'] : '';
  if (!empty($products_artist_url)) $products_artist_name = '<a href="' . zen_href_link(FILENAME_REDIRECT, 'action=music_arist&artists_id=' . zen_output_string_protected($music_extras->fields['artists_id']), 'NONSSL', true, false) . '" rel="noopener noreferrer" target="_blank">'.$products_artist_name.'</a>';
  if (!empty($products_record_company_url)) $products_record_company_name = '<a href="' . zen_href_link(FILENAME_REDIRECT, 'action=music_record_company&record_company_id=' . zen_output_string_protected($music_extras->fields['record_company_id']), 'NONSSL', true, false) . '" rel="noopener noreferrer" target="_blank">'.$products_record_company_name.'</a>';

  // This should be last line of the script:
  $zco_notifier->notify('NOTIFY_PRODUCT_TYPE_VARS_END_PRODUCT_MUSIC_INFO');
