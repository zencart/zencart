<?php
/**
 * search sidebox - displays keyword-search field for customer to initiate a search
 *
 * @copyright Copyright 2003-2022 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: DrByte 2020 Jul 10 Modified in v1.5.8-alpha $
 */

  require($template->get_template_dir('tpl_search.php',DIR_WS_TEMPLATE, $current_page_base,'sideboxes'). '/tpl_search.php');

  $title = '<label>' . BOX_HEADING_SEARCH . '</label>';
  $title_link = false;
  require($template->get_template_dir($column_box_default, DIR_WS_TEMPLATE, $current_page_base,'common') . '/' . $column_box_default);
?>