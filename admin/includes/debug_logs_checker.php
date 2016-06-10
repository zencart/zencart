<?php
/**
 * debug_logs_checker.php
 *
 * checks for debug logs in /logs/ and /cache/ folders
 *
 * @package admin
 * @copyright Copyright 2003-2014 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: debug_logs_checker.php Ajeh $
 */

  $cnt_logs = get_logs_data('count');
  if ($cnt_logs > 0){

    $adminNotifications = $di->get('zencart_notifications');
    $notification = array('type' => 'bell', 'text' => DEBUG_LOGS_DISCOVERED . $cnt_logs, 'class' => 'fa fa-warning text-red');
    $adminNotifications->addNotification($notification);
  }
