<?php
/*
 * Security Patch notify 20190707
 * 
 * @package initSystem
 * @copyright Copyright 2003-2019 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: security_patch_notify_20190707.php 2019-05-07 wilt $
 */
/**
 * Security Patch
 *
 * Required for versions of Zen Cart prior to v1.5.7
 *
 * SQL Injection - $_POST['notify']
 *
 * Please Note : This file should be placed in includes/extra_configures and will automatically load.
 *  
 */

if (isset($_POST['notify'])) {
    if (!is_array($_POST['notify'])) {
        $_POST['notify'] = array($_POST['notify']);
    }

    foreach ($_POST['notify'] as $nKey => $nValue) {
        $_POST['notify'][$nKey] = (int)$nValue;
    }
}

if (isset($_GET['notify'])) {
    if (!is_array($_GET['notify'])) {
        $_GET['notify'] = array($_GET['notify']);
    }

    foreach ($_GET['notify'] as $nKey => $nValue) {
        $_GET['notify'][$nKey] = (int)$nValue;
    }
}
