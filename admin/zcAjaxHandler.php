<?php
/**
 * main ajax handler front controller.
 *
 * @package classes
 * @copyright Copyright 2003-2017 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version GIT: $Id: Author: Ian Wilson  $
 */
use ZenCart\AjaxDispatch\AjaxDispatch;
/**
 * main ajax handler front controller.
 *
 * @package admin
 */
require('includes/application_top.php');
$getAct = $zcRequest->get('act');
$postAct = $zcRequest->get('act', null, 'post');
$act = isset($getAct) ? $getAct : $postAct;
if (isset($act)) {
    AjaxDispatch::run($act, $zcRequest);
} else {
    header("Status: 403 Forbidden", TRUE, 403);
    echo json_encode(array('error'=>TRUE, 'errorType'=>"MISSING_DISPATCHER_ACTION"));
    require DIR_WS_INCLUDES . 'application_bottom.php';
    exit(1);
}
