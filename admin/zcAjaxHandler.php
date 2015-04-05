<?php
/**
 * main ajax handler front controller.
 *
 * @package classes
 * @copyright Copyright 2003-2015 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version GIT: $Id: Author: Ian Wilson  Fri Aug 17 17:42:37 2012 +0100 New in v1.5.1 $
 */
use ZenCart\Admin\AjaxDispatch\AjaxDispatch;
/**
 * main ajax handler front controller.
 *
 * @package admin
 */
$loaderPrefix = 'ajax';
require('includes/application_top.php');
$getAct = $zcRequest->get('act');
$postAct = $zcRequest->get('act', null, 'post');
$act = isset($getAct) ? $getAct : $postAct;
if (isset($act)) {
    AjaxDispatch::run($act, $zcRequest);
} else {
    header("Status: 403 Forbidden", TRUE, 403);
    echo json_encode(array('error'=>TRUE, 'errorType'=>"MISSING_DISPATCHER_ACTION"));
    exit(1);
}
