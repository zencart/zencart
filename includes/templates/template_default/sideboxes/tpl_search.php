<?php
/**
 * Side Box Template
 *
 * @package templateSystem
 * @copyright Copyright 2003-2010 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: tpl_search.php 15881 2010-04-11 16:32:39Z wilt $
 */
  $content = "";
  $content .= '<div id="' . str_replace('_', '-', $box_id . 'Content') . '" class="sideBoxContent centeredContent">';
  $content .= zen_draw_form('quick_find', zen_href_link(FILENAME_ADVANCED_SEARCH_RESULT, '', $request_type, false), 'get');
  $content .= zen_draw_hidden_field('main_page',FILENAME_ADVANCED_SEARCH_RESULT);
  $content .= zen_draw_hidden_field('search_in_description', '1') . zen_hide_session_id();

  if (strtolower(IMAGE_USE_CSS_BUTTONS) == 'yes') {
    $content .= zen_draw_input_field('keyword', '', 'size="18" maxlength="100" style="width: ' . ($column_width-30) . 'px"') . '<br />' . zen_image_submit (BUTTON_IMAGE_SEARCH,HEADER_SEARCH_BUTTON);
    $content .= '<br /><a href="' . zen_href_link(FILENAME_ADVANCED_SEARCH) . '">' . BOX_SEARCH_ADVANCED_SEARCH . '</a>';
  } else {
    $content .= zen_draw_input_field('keyword', '', 'size="18" maxlength="100" style="width: ' . ($column_width-30) . 'px" value="' . HEADER_SEARCH_DEFAULT_TEXT . '" onfocus="if (this.value == \'' . HEADER_SEARCH_DEFAULT_TEXT . '\') this.value = \'\';" onblur="if (this.value == \'\') this.value = \'' . HEADER_SEARCH_DEFAULT_TEXT . '\';"') . '<br /><input type="submit" value="' . HEADER_SEARCH_BUTTON . '" style="width: 50px" />';
    $content .= '<br /><a href="' . zen_href_link(FILENAME_ADVANCED_SEARCH) . '">' . BOX_SEARCH_ADVANCED_SEARCH . '</a>';
  }

  $content .= "</form>";
  $content .= '</div>';
?>