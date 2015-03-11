<?php
/**
 * @package admin
 * @copyright Copyright 2015 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version 
 */

  $gID=(int) zen_db_prepare_input($_GET['gID']);
  $cID=(int) zen_db_prepare_input($_GET['cID']);

  if ($gID == 2 && $cID == 59) { 
    // Admin Usernames.  Must be >= 4
    if ($configuration_value < 4) { 
          $_GET['action']= '';
          $messageStack->add_session(TEXT_ADMIN_USERNAME_LENGTH, 'caution');
          zen_redirect(zen_href_link(FILENAME_CONFIGURATION, 'gID=' . $_GET['gID'] . '&cID=' . (int)$cID));
    }
  }
