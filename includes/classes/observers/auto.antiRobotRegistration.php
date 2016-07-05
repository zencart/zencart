<?php
/**
 * @package plugins
 * @copyright Copyright 2003-2016 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: Designed for v1.6.0  $
 */

class zcObserverAntiRobotRegistration extends base {

  function __construct() {
    $this->attach($this, array('NOTIFY_CREATE_ACCOUNT_VALIDATION_CHECK'));
  }

  function updateNotifyCreateAccountValidationCheck(&$class, $eventID, $null = null, &$error, &$send_welcome_email)
  {
    if (ACCOUNT_VALIDATION != 'true' || CREATE_ACCOUNT_VALIDATION != 'true') return;

    $sql = "SELECT * FROM " . TABLE_ANTI_ROBOT_REGISTRATION . " WHERE session_id = '" . zen_session_id() . "' LIMIT 1";
    if( !$result = $db->Execute($sql) ) {
      $error = true;
      $entry_antirobotreg_error = true;
      $text_antirobotreg_error = ERROR_VALIDATION_1;
      $messageStack->add('create_account', ERROR_VALIDATION_1);
      return;
    }
    $entry_antirobotreg_error = false;
    $result = $db->Execute($sql);
    if (( strtolower($_POST['antirobotreg']) != $result->fields['reg_key'] ) or ($result->fields['reg_key'] =='')) {
      $error = true;
      $entry_antirobotreg_error = true;
      $text_antirobotreg_error = ERROR_VALIDATION_2;
      $messageStack->add('create_account', ERROR_VALIDATION_2);
    } else {
      $sql = "DELETE FROM " . TABLE_ANTI_ROBOT_REGISTRATION . " WHERE session_id = '" . zen_session_id() . "'";
      if( !$result = $db->Execute($sql) )
      {
        $error = true;
        $entry_antirobotreg_error = true;
        $text_antirobotreg_error = ERROR_VALIDATION_3;
        $messageStack->add('create_account', ERROR_VALIDATION_3);
      } else {
        $sql = "OPTIMIZE TABLE " . TABLE_ANTI_ROBOT_REGISTRATION . "";
        if( !$result = $db->Execute($sql) )
        {
          $error = true;
          $entry_antirobotreg_error = true;
          $text_antirobotreg_error = ERROR_VALIDATION_4;
          $messageStack->add('create_account', ERROR_VALIDATION_4);
        } else {
          $entry_antirobotreg_error = false;
        }
      }
    }

    if (strlen($antirobotreg) <> ENTRY_VALIDATION_LENGTH) {
      $error = true;
      $entry_antirobotreg_error = true;
    } else {
      $entry_antirobotreg_error = false;
    }

  }
}
