<?php
/**
 * Legacy Admin html Head Template
 *
 * @package templateSystem
 * @copyright Copyright 2003-2016 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: $
 */

$adminNotifications = $di->get('zencart_notifications');
$session = $zcRequest->getSession();
$currentUser = new ZenCart\AdminUser\AdminUser($session, $db, $adminNotifications);
$cssList [] = array(
    'href' => 'includes/template/css/bootstrap.min.css',
    'id' => 'bootstrapCSS'
);
$cssList [] = array(
    'href' => 'includes/template/AdminLTE2/dist/css/AdminLTE.css',
    'id' => 'adminlteCSS'
);
$cssList [] = array(
    'href' => 'includes/template/css/stylesheet.css',
    'id' => 'stylesheetCSS'
);
$tplVars ['cssList'] = $cssList;
$tplVars['cmd'] = $zcRequest->readGet('cmd');
$tplVars['hide_languages'] = $GLOBALS['hide_languages'];
$tplVars['languages'] = $GLOBALS['languages'];
$tplVars['languages_array'] = $GLOBALS['languages_array'];
$tplVars['languages_selected'] = $GLOBALS['languages_selected'];
$tplVars['user'] = $currentUser->getCurrentUser();
$tplVars['messageStack'] = $GLOBALS['messageStack'];
$tplVars['notifications'] = $currentUser->getNotifications()->getNotificationList();

$tplVars['menuTitles'] = zen_get_menu_titles();
$tplVars['adminMenuForUser'] = zen_get_admin_menu_for_user();
?>
<script src="includes/general.js"></script>
