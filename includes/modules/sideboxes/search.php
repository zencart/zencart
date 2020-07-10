<?php
/**
 * search sidebox - displays keyword-search field for customer to initiate a search
 *
 * @package templateSystem
 * @copyright Copyright 2003-2005 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: search.php 2834 2006-01-11 22:16:37Z birdbrain $
 */

  require($template->get_template_dir('tpl_search.php',DIR_WS_TEMPLATE, $current_page_base,'sideboxes'). '/tpl_search.php');

  $title = '<label>' . BOX_HEADING_SEARCH . '</label>';
  $title_link = false;
  require($template->get_template_dir($column_box_default, DIR_WS_TEMPLATE, $current_page_base,'common') . '/' . $column_box_default);
?>