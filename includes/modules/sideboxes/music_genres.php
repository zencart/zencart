<?php
/**
 * music_genres sidebox - displays list of available music genres to filter on
 *
 * @copyright Copyright 2003-2022 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: lat9 2022 Jul 05 Modified in v1.5.8-alpha $
 */
$music_genres = $db->Execute(
    "SELECT music_genre_id, music_genre_name
       FROM " . TABLE_MUSIC_GENRE . "
      ORDER BY music_genre_name"
);

if (!$music_genres->EOF) {
// Display a list
    $music_genres_array = [];
    $default_selection = (isset($_GET['music_genre_id'])) ? (int)$_GET['music_genre_id'] : '';
    if (!isset($_GET['music_genre_id']) || $_GET['music_genre_id'] === '' ) {
        $required = ' required';
        $music_genres_array[] = ['id' => '', 'text' => PULL_DOWN_ALL];
    } else {
        $required = '';
        $music_genres_array[] = ['id' => '', 'text' => PULL_DOWN_MUSIC_GENRES];
    }

    foreach ($music_genres as $next_genre) {
        $music_genre_name = $next_genre['music_genre_name'];
        if (strlen($music_genre_name) > (int)MAX_DISPLAY_MUSIC_GENRES_NAME_LEN) {
            $music_genre_name = substr($music_genre_name, 0, (int)MAX_DISPLAY_MUSIC_GENRES_NAME_LEN) . '..';
        }
        $music_genres_array[] = [
            'id' => $next_genre['music_genre_id'],
             'text' => zen_output_string($music_genre_name, false, true),
        ];
    }
    require $template->get_template_dir('tpl_music_genres_select.php', DIR_WS_TEMPLATE, $current_page_base, 'sideboxes') . '/tpl_music_genres_select.php';

    $title = BOX_HEADING_MUSIC_GENRES;
    $title_link = false;
    require $template->get_template_dir($column_box_default, DIR_WS_TEMPLATE, $current_page_base, 'common') . '/' . $column_box_default;
}
