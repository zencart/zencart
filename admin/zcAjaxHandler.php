<?php
/**
 * main ajax handler front controller.
 *
 * @package classes
 * @copyright Copyright 2003-2012 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version GIT: $Id: Author: Ian Wilson  Fri Aug 17 17:42:37 2012 +0100 New in v1.5.1 $
 */
/**
 * main ajax handler front controller.
 *
 * @package admin
 */
$loaderPrefix = 'ajax';
require('includes/application_top.php');
require (DIR_FS_ADMIN . DIR_WS_CLASSES . 'class.zcAjaxDispatcher.php');
if (isset($_GET['act']))
//if (false)
{
  zcAjaxDispatcher::run($_GET['act']);
} else 
{
  header("Status: 403 Forbidden", TRUE, 403);
  echo json_encode(array('error'=>TRUE, 'errorType'=>"MISSING_DISPATCHER_ACTION"));
  exit(1);
}