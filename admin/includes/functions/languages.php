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
//  $Id: languages.php 1969 2005-09-13 06:57:21Z drbyte $
//

  function zen_get_languages_directory($code) {
    global $db;
    $language = $db->Execute("select languages_id, directory 
                              from " . TABLE_LANGUAGES . " 
                              where code = '" . zen_db_input($code) . "'");

    if ($language->RecordCount() > 0) {
      $_SESSION['languages_id'] = $language->fields['languages_id'];
      return $language->fields['directory'];
    } else {
      return false;
    }
  }
?>