<?php
/**
 * music_genres sidebox - displays list of available music genres to filter on
 *
 * @package templateSystem
 * @copyright Copyright 2003-2015 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: music_genres.php  Modified in v1.6.0 $
 */

  $music_genres_query = "select music_genre_id, music_genre_name
                          from " . TABLE_MUSIC_GENRE . "
                          order by music_genre_name";

  $music_genres = $db->Execute($music_genres_query);

  if ($music_genres->RecordCount()>0) {
    $number_of_rows = $music_genres->RecordCount()+1;

// Display a list
    $music_genres_array = array();
    if (!isset($_GET['music_genre_id']) || $_GET['music_genre_id'] == '' ) {
      $music_genres_array[] = array('id' => '', 'text' => PULL_DOWN_ALL);
    } else {
      $music_genres_array[] = array('id' => '', 'text' => PULL_DOWN_MUSIC_GENRES);
    }

    foreach($music_genres as $result) {
      $elipsis = (strlen($result['music_genre_name']) > (int)MAX_DISPLAY_MUSIC_GENRES_NAME_LEN) ? '..' : '';
      $music_genre_name = substr($result['music_genre_name'], 0, (int)MAX_DISPLAY_MUSIC_GENRES_NAME_LEN) . $elipsis;

      $music_genres_array[] = array('id' => $result['music_genre_id'],
                                    'text' => $music_genre_name);
    }

    require($template->get_template_dir('tpl_music_genres_select.php',DIR_WS_TEMPLATE, $current_page_base,'sideboxes'). '/tpl_music_genres_select.php');

    $title = '<label>' . BOX_HEADING_MUSIC_GENRES . '</label>';
    $title_link = false;
    require($template->get_template_dir($column_box_default, DIR_WS_TEMPLATE, $current_page_base,'common') . '/' . $column_box_default);
  }
