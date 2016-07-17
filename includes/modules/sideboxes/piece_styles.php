<?php
/**
 * piece_styles sidebox - displays list of available piece styles to filter on
 *
 * @package templateSystem
 * @copyright Copyright 2003-2015 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: piece_styles.php  Modified in v1.6.0 $
 */

  $piece_styles_query = "select piece_style_id, piece_style_name
                          from " . TABLE_PIECE_STYLE . "
                          order by piece_style_name";

  $piece_styles = $db->Execute($piece_styles_query);

  if ($piece_styles->RecordCount()>0) {
    $number_of_rows = $piece_styles->RecordCount()+1;

// Display a list
    $piece_styles_array = array();
    if (!isset($_GET['piece_style_id']) || $_GET['piece_style_id'] == '' ) {
      $piece_styles_array[] = array('id' => '', 'text' => PULL_DOWN_ALL);
    } else {
      $piece_styles_array[] = array('id' => '', 'text' => PULL_DOWN_PIECE_STYLES);
    }

    foreach($piece_styles as $result) {
      $elipsis = (strlen($result['piece_style_name']) > (int)MAX_DISPLAY_PIECE_STYLES_NAME_LEN) ? '..' : '';
      $piece_style_name = substr($result['piece_style_name'], 0, (int)MAX_DISPLAY_PIECE_STYLES_NAME_LEN) . $elipsis;

      $piece_styles_array[] = array('id' => $result['piece_style_id'],
                                    'text' => $piece_style_name);
    }

    require($template->get_template_dir('tpl_piece_styles_select.php',DIR_WS_TEMPLATE, $current_page_base,'sideboxes'). '/tpl_piece_styles_select.php');

    $title = '<label>' . BOX_HEADING_PIECE_STYLES . '</label>';
    $title_link = false;
    require($template->get_template_dir($column_box_default, DIR_WS_TEMPLATE, $current_page_base,'common') . '/' . $column_box_default);
  }
