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
//  $Id: payment_module_info.php 1969 2005-09-13 06:57:21Z drbyte $
//


  class paymentModuleInfo {
    var $payment_code, $keys;

// class constructor
    function paymentModuleInfo($pmInfo_array) {
      global $db;
      $this->payment_code = $pmInfo_array['payment_code'];

      for ($i = 0, $n = sizeof($pmInfo_array) - 1; $i < $n; $i++) {
        $key_value = $db->Execute("select configuration_title, configuration_value,
                                          configuration_description
                                   from " . TABLE_CONFIGURATION . "
                                   where configuration_key = '" . $pmInfo_array[$i] . "'");

        $this->keys[$pmInfo_array[$i]]['title'] = $key_value->fields['configuration_title'];
        $this->keys[$pmInfo_array[$i]]['value'] = $key_value->fields['configuration_value'];
        $this->keys[$pmInfo_array[$i]]['description'] = $key_value->fields['configuration_description'];
      }
    }
  }
?>