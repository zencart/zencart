<?php
/**
 * @copyright Copyright 2003-2026 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 */

namespace Tests\Unit\testsSundry;

use Tests\Support\zcUnitTestCase;
use Zencart\Request\Request;

class RequestSecurityTest extends zcUnitTestCase
{
    public function setUp(): void
    {
        parent::setUp();
        require_once DIR_FS_CATALOG . 'includes/classes/Request.php';
        require_once DIR_FS_CATALOG . 'includes/functions/functions_traffic.php';

        $_SERVER = [];

        /**
         * isSecure()/zen_get_ip_address() now base their trust decision on the peer address that
         * Request captures once per request. Clear that captured value so each test starts clean
         * and can independently exercise the trusted-proxy and not-trusted scenarios.
         */
        Request::resetOriginalRemoteAddrForTesting();
    }

    public function testPlainHttpRequestIsNotSecure(): void
    {
        $_SERVER['HTTPS'] = 'off';
        $_SERVER['SERVER_PORT'] = '80';

        $this->assertFalse(Request::isSecure());
    }

    public function testNativeHttpsRequestIsSecure(): void
    {
        $_SERVER['HTTPS'] = 'on';
        $_SERVER['SERVER_PORT'] = '443';

        $this->assertTrue(Request::isSecure());
    }

    public function testForwardedHttpsRequestIsNotSecureWithoutTrustedProxy(): void
    {
        $_SERVER['HTTPS'] = 'off';
        $_SERVER['SERVER_PORT'] = '80';
        $_SERVER['HTTP_X_FORWARDED_PROTO'] = 'https';

        $this->assertFalse(Request::isSecure());
    }

    public function testForwardedSslHeaderRequestIsNotSecureWithoutTrustedProxy(): void
    {
        $_SERVER['HTTPS'] = 'off';
        $_SERVER['SERVER_PORT'] = '80';
        $_SERVER['HTTP_X_FORWARDED_SSL'] = 'on';

        $this->assertFalse(Request::isSecure());
    }

    public function testForwardedPortRequestIsNotSecureWithoutTrustedProxy(): void
    {
        $_SERVER['HTTPS'] = 'off';
        $_SERVER['SERVER_PORT'] = '80';
        $_SERVER['HTTP_X_FORWARDED_PORT'] = '443';

        $this->assertFalse(Request::isSecure());
    }

    public function testForwardedHostContainingSslIsNotSecureWithoutTrustedProxy(): void
    {
        $_SERVER['HTTPS'] = 'off';
        $_SERVER['SERVER_PORT'] = '80';
        $_SERVER['HTTP_X_FORWARDED_HOST'] = 'ssl-proxy.example.test';

        $this->assertFalse(Request::isSecure());
    }

    public function testForwardedServerDoesNotMatchHttpServerByAccident(): void
    {
        $_SERVER['HTTPS'] = 'off';
        $_SERVER['SERVER_PORT'] = '80';
        $_SERVER['HTTP_X_FORWARDED_SERVER'] = 'different-host.local';

        $this->assertFalse(Request::isSecure());
    }

    public function testForwardedHttpsRequestIsSecureWhenFromTrustedProxy(): void
    {
        if (!defined('TRUSTED_PROXIES')) {
            define('TRUSTED_PROXIES', '10.0.0.1');
        }

        $_SERVER['HTTPS'] = 'off';
        $_SERVER['SERVER_PORT'] = '80';
        $_SERVER['HTTP_X_FORWARDED_PROTO'] = 'https';
        $_SERVER['REMOTE_ADDR'] = '10.0.0.1';

        /**
         * isSecure() now reads the captured original peer address rather than $_SERVER['REMOTE_ADDR']
         * directly, so capture it after the peer address has been set up.
         */
        Request::captureOriginalRemoteAddr();

        $this->assertTrue(Request::isSecure());
    }

    /**
     * Regression test for the bootstrap chicken-and-egg problem this fix addresses.
     *
     * A request arrives from a trusted reverse proxy: the genuine TCP peer is the proxy
     * (10.0.0.1) and the proxy reports the real client via X-Forwarded-For (203.0.113.5).
     * During a real request, init_sessions.php overwrites $_SERVER['REMOTE_ADDR'] with the
     * resolved client IP partway through bootstrap, and zen_get_ip_address() is called from
     * roughly ten sites both before and after that overwrite.
     *
     * The trust decision must be identical on every call — not correct on the first call (when
     * $_SERVER['REMOTE_ADDR'] still holds the proxy) and wrong on a later call (after it has been
     * overwritten with the client IP, which is not a trusted proxy). Because the decision is based
     * on the captured original peer address, it stays consistent across the overwrite.
     */
    public function testTrustDecisionIsConsistentAcrossRemoteAddrOverwrite(): void
    {
        if (!defined('TRUSTED_PROXIES')) {
            define('TRUSTED_PROXIES', '10.0.0.1');
        }

        $_SERVER['REMOTE_ADDR'] = '10.0.0.1';
        $_SERVER['HTTP_X_FORWARDED_FOR'] = '203.0.113.5';

        /**
         * Capture the genuine peer address once, exactly as the bootstrap does before any init
         * code can run.
         */
        Request::captureOriginalRemoteAddr();

        /**
         * First call — the equivalent of the very first zen_get_ip_address() use in the request,
         * before init_sessions.php has overwritten $_SERVER['REMOTE_ADDR'].
         */
        $this->assertTrue(Request::isFromTrustedProxy());
        $this->assertSame('203.0.113.5', zen_get_ip_address());

        /**
         * Simulate the init_sessions.php overwrite that happens partway through bootstrap.
         */
        $_SERVER['REMOTE_ADDR'] = zen_get_ip_address();
        $this->assertSame('203.0.113.5', $_SERVER['REMOTE_ADDR']);

        /**
         * Second call — a later zen_get_ip_address() site, after the overwrite. A naive check
         * against the now-overwritten $_SERVER['REMOTE_ADDR'] (203.0.113.5, not a trusted proxy)
         * would flip the trust decision and stop honoring the forwarded header. The captured
         * original peer address keeps it consistent.
         */
        $this->assertTrue(Request::isFromTrustedProxy());
        $this->assertSame('203.0.113.5', zen_get_ip_address());
    }

    /**
     * Conversely, when the genuine peer is NOT a trusted proxy, a spoofed X-Forwarded-For header
     * must be ignored and the real peer address returned — and that must also remain consistent
     * even after $_SERVER['REMOTE_ADDR'] is overwritten.
     */
    public function testForwardedForIgnoredWhenPeerNotTrusted(): void
    {
        if (!defined('TRUSTED_PROXIES')) {
            define('TRUSTED_PROXIES', '10.0.0.1');
        }

        $_SERVER['REMOTE_ADDR'] = '198.51.100.9';
        $_SERVER['HTTP_X_FORWARDED_FOR'] = '203.0.113.5';

        Request::captureOriginalRemoteAddr();

        $this->assertFalse(Request::isFromTrustedProxy());
        $this->assertSame('198.51.100.9', zen_get_ip_address());

        $_SERVER['REMOTE_ADDR'] = zen_get_ip_address();

        $this->assertFalse(Request::isFromTrustedProxy());
        $this->assertSame('198.51.100.9', zen_get_ip_address());
    }
}
