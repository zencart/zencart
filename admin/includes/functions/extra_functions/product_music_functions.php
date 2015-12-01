<?php
//
// +----------------------------------------------------------------------+
// |zen-cart Open Source E-commerce                                       |
// +----------------------------------------------------------------------+
// | Copyright (c) 2003 The zen-cart developers                           |
// |                                                                      |
// | http://www.zen-cart.com/index.php                                    |
// |                                                                      |
// | Portions Copyright (c) 2003 osCommerce                               |
// +----------------------------------------------------------------------+
// | This source file is subject to version 2.0 of the GPL license,       |
// | that is bundled with this package in the file LICENSE, and is        |
// | available through the world-wide-web at the following url:           |
// | http://www.zen-cart.com/license/2_0.txt.                             |
// | If you did not receive a copy of the zen-cart license and are unable |
// | to obtain it through the world-wide-web, please send a note to       |
// | license@zen-cart.com so we can mail you a copy immediately.          |
// +----------------------------------------------------------------------+
//  $Id: product_music_functions.php 1105 2005-04-04 22:05:35Z birdbrain $
//
 
////
// Return the artists URL in the needed language
  function zen_get_artists_url($artists_id, $language_id) {
    global $db;
    $artist = $db->Execute("select artists_url
                                  from " . TABLE_RECORD_ARTISTS_INFO . "
                                  where artists_id = '" . (int)$artists_id . "'
                                  and languages_id = '" . (int)$language_id . "'");

    return $artist->fields['artists_url'];
  }
////
// Return the Record Company URL in the needed language
  function zen_get_record_company_url($record_company_id, $language_id) {
    global $db;
    $record_company = $db->Execute("select record_company_url
                                  from " . TABLE_RECORD_COMPANY_INFO . "
                                  where record_company_id = '" . (int)$record_company_id . "'
                                  and languages_id = '" . (int)$language_id . "'");

    return $record_company->fields['record_company_url'];
  }

////
// Return the Music Genre URL in the needed language
  function zen_get_music_genre_url($music_genre_id, $language_id) {
    global $db;
    $music_genre = $db->Execute("select music_genre_url
                                  from " . TABLE_RECORD_COMPANY_INFO . "
                                  where music_genre_id = '" . (int)$music_genre_id . "'
                                  and languages_id = '" . (int)$language_id . "'");

    return $music_genre->fields['music_genre_url'];
  }
?>
