<?php
/**
 * Callback module for Square payment module
 */
header('Access-Control-Allow-Origin: *');

$mode = 'cli';
if (isset($_GET) && isset($_GET['response_type']) && $_GET['response_type'] === 'code') {
    $mode = 'web';
}
$verbose = false;
require 'includes/application_top.php';
require DIR_WS_CLASSES . 'payment.php';
if ($mode === 'web' && (!isset($_GET['response_type']) || $_GET['response_type'] !== 'code')) {
    if ($verbose) echo 'INVALID PARAMS';
    exit(1);
}
$module = new payment('square');
$square = new square;
if ($mode === 'web') {
    if ($verbose) error_log('SQUARE TOKEN REQUEST - auth code for exchange: ' . $_GET['code'] . "\n\n" . print_r($_GET, true));
    $square->exchangeForToken($_GET['code']);
    exit(0);
}
if ($mode === 'cli') {
    if (!defined('MODULE_PAYMENT_SQUARE_STATUS') || MODULE_PAYMENT_SQUARE_STATUS !== 'True') {
        if ($verbose) echo 'MODULE DISABLED';
        http_response_code(417);
        exit(1);
    }
    $is_browser = (isset($_SERVER['HTTP_HOST']) || PHP_SAPI !== 'cli');
    $result = $square->token_refresh_check();
    if ($verbose) echo $result;
    if ($result === 'failure') {
        if (!$is_browser) echo 'Square Token Refresh Failure. See logs.';
        http_response_code(417);
        exit(1);
    }
}
exit(0);
