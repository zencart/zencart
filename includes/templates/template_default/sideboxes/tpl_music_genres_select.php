<?php
/**
 * Side Box Template
 *
 * @package templateSystem
 * @copyright Copyright 2003-2010 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: tpl_music_genres_select.php 15882 2010-04-11 16:37:54Z wilt $
 */
  $content = "";
  $content .= '<div id="' . str_replace('_', '-', $box_id . 'Content') . '" class="sideBoxContent centeredContent">';
  $content .= zen_draw_form('music_genres_form', zen_href_link(FILENAME_DEFAULT, '', $request_type, false), 'get');
  $content .= zen_draw_hidden_field('main_page', FILENAME_DEFAULT) . zen_hide_session_id() . zen_draw_hidden_field('typefilter', 'music_genre');
  $content .= zen_draw_pull_down_menu('music_genre_id', $music_genres_array, (isset($_GET['music_genre_id']) ? $_GET['music_genre_id'] : ''), 'onchange="this.form.submit();" size="' . MAX_MUSIC_GENRES_LIST . '" style="width: 90%; margin: auto;"');
  $content .= '</form>';
  $content .= '</div>';
?>