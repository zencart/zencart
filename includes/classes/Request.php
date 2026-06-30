<?php

declare(strict_types=1);
/**
 * @copyright Copyright 2003-2025 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: DrByte 2025 Sep 18 Modified in v2.2.0 $
 */

namespace Zencart\Request;

use Zencart\Traits\Singleton;

/**
 * @since ZC v1.5.8
 */
class Request
{
    use Singleton;

    protected array $paramBag;

    /**
     * The genuine TCP peer address ($_SERVER['REMOTE_ADDR']) as it was at the very start of the
     * request, captured once before any init code (notably init_sessions.php, which overwrites
     * $_SERVER['REMOTE_ADDR'] with the result of zen_get_ip_address()) can mutate it.
     *
     * Trust decisions about client-suppliable forwarded headers (isSecure(), zen_get_ip_address())
     * must be made against this immutable original peer address, never against the live
     * $_SERVER['REMOTE_ADDR'] which may already have been resolved/overwritten earlier in the
     * request. A static property (rather than a define() constant) is used deliberately so it can
     * be reset between PHPUnit test cases running in the same process.
     */
    private static ?string $originalRemoteAddr = null;

    /**
     * @since ZC v1.5.8
     */
    public static function capture(): self
    {
        $self = self::getInstance();
        $self->paramBag = $_REQUEST;
        return self::getInstance();
    }

    /**
     * @since ZC v1.5.8
     */
    public function input($key, $default = null): mixed
    {
        return $this->paramBag[$key] ?? $default;
    }

    /**
     * @since ZC v1.5.8
     */
    public function has($key): bool
    {
        return isset($this->paramBag[$key]);
    }

    /**
     * Capture the genuine TCP peer address once, as early as possible in the request.
     *
     * Uses null-coalescing assignment so that the first capture sticks: calling this more than
     * once during a single request is harmless and idempotent — the originally-captured value is
     * never overwritten by a later (possibly already-mutated) $_SERVER['REMOTE_ADDR'].
     */
    public static function captureOriginalRemoteAddr(): void
    {
        self::$originalRemoteAddr ??= ($_SERVER['REMOTE_ADDR'] ?? '');
    }

    /**
     * Return the genuine TCP peer address captured at the start of the request.
     *
     * Lazily captures on first read as a safety net in case captureOriginalRemoteAddr() was not
     * already called during bootstrap; the ??= semantics still guarantee a single, stable value.
     */
    public static function getOriginalRemoteAddr(): string
    {
        self::captureOriginalRemoteAddr();
        return self::$originalRemoteAddr ?? '';
    }

    /**
     * Test-only: clear the captured peer address so each PHPUnit test case can start from a clean
     * state and exercise both the trusted-proxy and not-trusted scenarios in the same process.
     * Not for use in application code.
     */
    public static function resetOriginalRemoteAddrForTesting(): void
    {
        self::$originalRemoteAddr = null;
    }

    /**
     * Parse the TRUSTED_PROXIES configuration constant into a normalized list of proxy addresses.
     *
     * Accepts either an array or a comma-separated string; trims and drops empty entries.
     * Shared by isSecure() and zen_get_ip_address() so the trust-list parsing lives in one place.
     */
    public static function getTrustedProxies(): array
    {
        $trustedProxiesConfig = defined('TRUSTED_PROXIES') ? constant('TRUSTED_PROXIES') : '';
        if (is_array($trustedProxiesConfig)) {
            return array_values(array_filter(array_map(static fn($proxy): string => trim((string) $proxy), $trustedProxiesConfig)));
        }
        return array_values(array_filter(array_map('trim', explode(',', (string) $trustedProxiesConfig))));
    }

    /**
     * Determine whether the genuine TCP peer is a configured trusted reverse proxy.
     *
     * The check is made against the captured original peer address, so it returns the same answer
     * regardless of where in the request it is called and regardless of whether init_sessions.php
     * has already overwritten $_SERVER['REMOTE_ADDR'].
     */
    public static function isFromTrustedProxy(): bool
    {
        $trustedProxies = self::getTrustedProxies();
        if ($trustedProxies === []) {
            return false;
        }
        return in_array(self::getOriginalRemoteAddr(), $trustedProxies, true);
    }

    public static function isSecure(): bool
    {
        /**
         * Detect the type of request received (secure or not)
         *
         * NOTE: there are some intentional loose-comparisons here for numeric strings.
         */
        $nativelySecure = (isset($_SERVER['HTTPS']) && (strtolower((string)$_SERVER['HTTPS']) !== 'off' || $_SERVER['HTTPS'] == '1'))
            || (isset($_SERVER['SCRIPT_URI']) && stripos((string)$_SERVER['SCRIPT_URI'], 'https:') === 0)
            || (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == '443');

        if ($nativelySecure) {
            return true;
        }

        /**
         * The X-Forwarded-* headers (and HTTP_SSLSESSIONID) are client-suppliable and only
         * trustworthy when they are known to be set/overwritten by a trusted reverse proxy.
         * The trust check is made against the captured original peer address (not the live
         * $_SERVER['REMOTE_ADDR'], which may already have been overwritten by init_sessions.php).
         */
        if (!self::isFromTrustedProxy()) {
            return false;
        }

        return (isset($_SERVER['HTTP_X_FORWARDED_BY']) && str_contains(strtoupper((string)$_SERVER['HTTP_X_FORWARDED_BY']), 'SSL'))
            || (isset($_SERVER['HTTP_X_FORWARDED_HOST']) && (str_contains(strtoupper((string)$_SERVER['HTTP_X_FORWARDED_HOST']), 'SSL') || str_contains(strtolower((string)$_SERVER['HTTP_X_FORWARDED_HOST']), str_replace('https://', '', HTTP_SERVER))))
            || (isset($_SERVER['HTTP_X_FORWARDED_SERVER']) && str_contains(strtolower((string)$_SERVER['HTTP_X_FORWARDED_SERVER']), str_replace('https://', '', HTTP_SERVER)))
            || (isset($_SERVER['HTTP_X_FORWARDED_SSL']) && ($_SERVER['HTTP_X_FORWARDED_SSL'] == '1' || strtolower((string)$_SERVER['HTTP_X_FORWARDED_SSL']) === 'on'))
            || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && (strtolower((string)$_SERVER['HTTP_X_FORWARDED_PROTO']) === 'ssl' || strtolower((string)$_SERVER['HTTP_X_FORWARDED_PROTO']) === 'https'))
            || (isset($_SERVER['HTTP_SSLSESSIONID']) && $_SERVER['HTTP_SSLSESSIONID'] !== '')
            || (isset($_SERVER['HTTP_X_FORWARDED_PORT']) && $_SERVER['HTTP_X_FORWARDED_PORT'] == '443');
    }
}
