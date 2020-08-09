<?php
/**
 * @copyright Copyright 2003-2020 Zen Cart Development Team
 * @license https://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id:  New in v1.5.8 $
 */


/**
 * Determine visitor's IP address, resolving any proxies where possible.
 *
 * @return string
 */
function zen_get_ip_address() {
    $ip = '';
    /**
     * resolve any proxies
     */
    if (isset($_SERVER)) {
        if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } elseif (isset($_SERVER['HTTP_CLIENT_IP'])) {
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        } elseif (isset($_SERVER['HTTP_X_FORWARDED'])) {
            $ip = $_SERVER['HTTP_X_FORWARDED'];
        } elseif (isset($_SERVER['HTTP_X_CLUSTER_CLIENT_IP'])) {
            $ip = $_SERVER['HTTP_X_CLUSTER_CLIENT_IP'];
        } elseif (isset($_SERVER['HTTP_FORWARDED_FOR'])) {
            $ip = $_SERVER['HTTP_FORWARDED_FOR'];
        } elseif (isset($_SERVER['HTTP_FORWARDED'])) {
            $ip = $_SERVER['HTTP_FORWARDED'];
        } else {
            $ip = $_SERVER['REMOTE_ADDR'];
        }
    }
    if (trim($ip) == '') {
        if (getenv('HTTP_X_FORWARDED_FOR')) {
            $ip = getenv('HTTP_X_FORWARDED_FOR');
        } elseif (getenv('HTTP_CLIENT_IP')) {
            $ip = getenv('HTTP_CLIENT_IP');
        } else {
            $ip = getenv('REMOTE_ADDR');
        }
    }

    /**
     * sanitize for validity as an IPv4 or IPv6 address
     */
    $original_ip = $ip;
    $ip = filter_var((string)$ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6 | FILTER_FLAG_IPV4);

    /**
     *  If it's an invalid IP, set the value to a single dot and issue a notification.
     */
    if ($ip === false) {
        $ip = '.';
        if (IS_ADMIN_FLAG) {
            $GLOBALS['zco_notifier']->notify('NOTIFY_ZEN_ADMIN_INVALID_IP_DETECTED', $original_ip);
        } else {
            $GLOBALS['zco_notifier']->notify('NOTIFY_ZEN_INVALID_IP_DETECTED', $original_ip);
        }
    }

    return $ip;
}


/**
 * Stop execution completely
 */
function zen_exit() {
    session_write_close();
    exit();
}


/**
 * Return whether the browser client is of a certain type
 * by checking whether the user-agent contains a particular pattern
 * @param string $lookup_pattern string to search for
 * @return false|string
 */
function zen_browser_detect($lookup_pattern) {
    if (!isset($_SERVER['HTTP_USER_AGENT'])) return false;
    return stristr($_SERVER['HTTP_USER_AGENT'], $lookup_pattern);
}

