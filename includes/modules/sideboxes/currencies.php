<?php
/**
 * currencies sidebox - allows customer to select from available currencies
 *
 * @package templateSystem
 * @copyright Copyright 2003-2018 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: Drbyte Sun Jan 7 21:28:50 2018 -0500 Modified in v1.5.6 $
 */

// test if box should display
  $show_currencies= false;

  // don't display on checkout page:
  if (substr($current_page, 0, 8) != 'checkout') {
    $show_currencies= true;
  }

  if ($show_currencies == true) {
    if (isset($currencies) && is_object($currencies)) {

      $currencies_array = array();
      foreach($currencies->currencies as $key => $value) {
        $currencies_array[] = array('id' => $key, 'text' => $value['title']);
      }

      $hidden_get_variables = zen_post_all_get_params('currency');

      require($template->get_template_dir('tpl_currencies.php',DIR_WS_TEMPLATE, $current_page_base,'sideboxes'). '/tpl_currencies.php');
      $title =  '<label>' . BOX_HEADING_CURRENCIES . '</label>';
      $title_link = false;
      require($template->get_template_dir($column_box_default, DIR_WS_TEMPLATE, $current_page_base,'common') . '/' . $column_box_default);
    }
  }
