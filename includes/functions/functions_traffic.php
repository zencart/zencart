<?php

declare(strict_types=1);

/**
 * @copyright Copyright 2003-2026 Zen Cart Development Team
 * @license https://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id:  Modified in v3.0.0-alpha $
 */

/**
 * Determine visitor's IP address, resolving any proxies where possible.
 *
 * @since ZC v1.0.3
 */
function zen_get_ip_address(): string
{
    $ip = '';
    /**
     * Resolve any proxies, but only honor the client-suppliable forwarded headers when the genuine
     * TCP peer is a configured trusted reverse proxy. The trust decision is made against the
     * captured original peer address (Request::getOriginalRemoteAddr()), so it is correct and
     * consistent no matter how many times this function is called in a request or whether
     * init_sessions.php has already overwritten $_SERVER['REMOTE_ADDR'].
     * When the peer is not a trusted proxy, the original peer address is returned directly.
     *
     * A trusted proxy conventionally APPENDS its own observed source address to an existing
     * forwarded-header value rather than replacing it, so the header can read
     * "client-claimed-value, hop1, hop2" — the leftmost entry is whatever the original,
     * potentially untrusted, client supplied, and is not authoritative.
     * Request::resolveClientFromForwardedChain() walks the chain from the right (the trusted side) and
     * returns the first entry that isn't itself a trusted proxy, instead of naively trusting
     * whichever value happens to be first.
     */
    if (isset($_SERVER)) {
        if (\Zencart\Request\Request::isFromTrustedProxy()) {
            $forwarded = $_SERVER['HTTP_X_FORWARDED_FOR'] ??
                $_SERVER['HTTP_CLIENT_IP'] ??
                    $_SERVER['HTTP_X_FORWARDED'] ??
                        $_SERVER['HTTP_X_CLUSTER_CLIENT_IP'] ??
                            $_SERVER['HTTP_FORWARDED_FOR'] ??
                                $_SERVER['HTTP_FORWARDED'] ??
                                    null;
            $ip = ($forwarded !== null)
                ? \Zencart\Request\Request::resolveClientFromForwardedChain((string) $forwarded)
                : \Zencart\Request\Request::getOriginalRemoteAddr();
        } else {
            $ip = \Zencart\Request\Request::getOriginalRemoteAddr();
        }
    }
    if (trim($ip) === '') {
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
    $ip = explode(',', (string)$ip);
    $ip = trim($ip[0]);
    $ip = filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6 | FILTER_FLAG_IPV4);

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

    return (string)$ip;
}


/**
 * Stop execution completely, but close the session beforehand to preserve data-integrity.
 *
 * @since ZC v1.0.3
 */
function zen_exit(): void
{
    session_write_close();
    exit();
}


/**
 * Lookup whether the browser client's user-agent (HTTP_USER_AGENT) contains a particular pattern.
 * Returns the matched string if found, or false if not found or if the user-agent is not set.
 *
 * @since ZC v1.0.3
 */
function zen_browser_detect(string $lookup_pattern): string|false
{
    if (!isset($_SERVER['HTTP_USER_AGENT'])) return false;
    return stristr($_SERVER['HTTP_USER_AGENT'], $lookup_pattern);
}

