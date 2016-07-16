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
//  $Id: product_piece_functions.php 1105 2005-04-04 22:05:35Z birdbrain $
//
 
////
// Return the artists URL in the needed language
  function zen_get_artists_url($artists_id, $language_id) {
    global $db;
    $artist = $db->Execute("select artists_url
                                  from " . TABLE_ARTISTS_INFO . "
                                  where artists_id = '" . (int)$artists_id . "'
                                  and languages_id = '" . (int)$language_id . "'");

    return $artist->fields['artists_url'];
  }
////
// Return the Agency URL in the needed language
  function zen_get_agency_url($agency_id, $language_id) {
    global $db;
    $agency = $db->Execute("select agency_url
                                  from " . TABLE_AGENCY_INFO . "
                                  where agency_id = '" . (int)$agency_id . "'
                                  and languages_id = '" . (int)$language_id . "'");

    return $agency->fields['agency_url'];
  }

////
// Return the Piece Style URL in the needed language
  function zen_get_piece_style_url($piece_style_id, $language_id) {
    global $db;
    $piece_style = $db->Execute("select piece_style_url
                                  from " . TABLE_AGENCY_INFO . "
                                  where piece_style_id = '" . (int)$piece_style_id . "'
                                  and languages_id = '" . (int)$language_id . "'");

    return $piece_style->fields['piece_style_url'];
  }
?>
