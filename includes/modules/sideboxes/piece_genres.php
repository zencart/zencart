<?php
/**
 * piece_genres sidebox - displays list of available piece genres to filter on
 *
 * @package templateSystem
 * @copyright Copyright 2003-2015 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: piece_genres.php  Modified in v1.6.0 $
 */

  $piece_genres_query = "select piece_genre_id, piece_genre_name
                          from " . TABLE_PIECE_GENRE . "
                          order by piece_genre_name";

  $piece_genres = $db->Execute($piece_genres_query);

  if ($piece_genres->RecordCount()>0) {
    $number_of_rows = $piece_genres->RecordCount()+1;

// Display a list
    $piece_genres_array = array();
    if (!isset($_GET['piece_genre_id']) || $_GET['piece_genre_id'] == '' ) {
      $piece_genres_array[] = array('id' => '', 'text' => PULL_DOWN_ALL);
    } else {
      $piece_genres_array[] = array('id' => '', 'text' => PULL_DOWN_PIECE_GENRES);
    }

    foreach($piece_genres as $result) {
      $elipsis = (strlen($result['piece_genre_name']) > (int)MAX_DISPLAY_PIECE_GENRES_NAME_LEN) ? '..' : '';
      $piece_genre_name = substr($result['piece_genre_name'], 0, (int)MAX_DISPLAY_PIECE_GENRES_NAME_LEN) . $elipsis;

      $piece_genres_array[] = array('id' => $result['piece_genre_id'],
                                    'text' => $piece_genre_name);
    }

    require($template->get_template_dir('tpl_piece_genres_select.php',DIR_WS_TEMPLATE, $current_page_base,'sideboxes'). '/tpl_piece_genres_select.php');

    $title = '<label>' . BOX_HEADING_PIECE_GENRES . '</label>';
    $title_link = false;
    require($template->get_template_dir($column_box_default, DIR_WS_TEMPLATE, $current_page_base,'common') . '/' . $column_box_default);
  }
