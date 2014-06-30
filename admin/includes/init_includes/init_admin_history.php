<?php
/**
 * @package admin
 * @copyright Copyright 2003-2014 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version GIT: $Id: Author: DrByte Fri Jun 13 2014  Modified in v1.5.3 $
 */
if (!defined('IS_ADMIN_FLAG')) {
  die('Illegal Access');
}

// log page visit into admin activity history
$zco_notifier->notify('NOTIFY_ADMIN_ACTIVITY_LOG_EVENT', 'POST');
