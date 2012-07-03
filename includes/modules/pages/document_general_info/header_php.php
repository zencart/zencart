<?php
/**
 * document_general header_php.php 
 *
 * @package page
 * @copyright Copyright 2003-2006 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: header_php.php 2978 2006-02-07 00:52:01Z drbyte $
 */

  // This should be first line of the script:
  $zco_notifier->notify('NOTIFY_HEADER_START_DOCUMENT_GENERAL_INFO');

  require(DIR_WS_MODULES . zen_get_module_directory('require_languages.php'));

  // This should be last line of the script:
  $zco_notifier->notify('NOTIFY_HEADER_END_DOCUMENT_GENERAL_INFO');
?>