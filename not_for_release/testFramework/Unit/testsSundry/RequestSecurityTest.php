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
    /**
     * TRUSTED_PROXIES is a PHP constant and can't be redefined between test methods running in
     * the same process, so every test in this class that needs a trusted-proxy configuration
     * defines it (guarded by !defined()) with this same combined value: an exact IP, an IPv4 and
     * an IPv6 CIDR range (mirroring how a real deployment lists a provider's published ranges,
     * e.g. Cloudflare's, alongside an exact address), plus three deliberately malformed entries
     * (non-numeric mask, out-of-range mask, invalid subnet) used by
     * testMalformedCidrEntriesInTrustedProxiesListAreIgnoredSafely() to confirm a typo in one
     * entry neither matches everything nor breaks parsing of the rest of the list.
     */
    private const TEST_TRUSTED_PROXIES = '10.0.0.1,173.245.48.0/20,2400:cb00::/32,10.0.0.0/abc,10.0.0.0/40,not-an-ip/20';

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
            define('TRUSTED_PROXIES', self::TEST_TRUSTED_PROXIES);
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
            define('TRUSTED_PROXIES', self::TEST_TRUSTED_PROXIES);
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
            define('TRUSTED_PROXIES', self::TEST_TRUSTED_PROXIES);
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

    /**
     * TRUSTED_PROXIES entries are commonly published as CIDR ranges rather than individual IPs
     * (e.g. Cloudflare's documented edge ranges — see includes/dist-configure.php).
     * An exact-string-match-only check could never match a real peer against such a range,
     * so the trusted-proxy check must support CIDR membership.
     * 173.245.48.0/20 covers 173.245.48.0–173.245.63.255.
     */
    public function testCidrTrustedProxyMatchesPeerInsideIpv4Range(): void
    {
        if (!defined('TRUSTED_PROXIES')) {
            define('TRUSTED_PROXIES', self::TEST_TRUSTED_PROXIES);
        }

        $_SERVER['REMOTE_ADDR'] = '173.245.50.10';
        $_SERVER['HTTP_X_FORWARDED_FOR'] = '203.0.113.5';
        Request::captureOriginalRemoteAddr();

        $this->assertTrue(Request::isFromTrustedProxy());
        $this->assertSame('203.0.113.5', zen_get_ip_address());
    }

    /**
     * A peer just outside the configured CIDR range (173.245.64.1 is one address past the
     * 173.245.48.0/20 boundary at 173.245.63.255) must not be trusted.
     */
    public function testCidrTrustedProxyDoesNotMatchPeerOutsideIpv4Range(): void
    {
        if (!defined('TRUSTED_PROXIES')) {
            define('TRUSTED_PROXIES', self::TEST_TRUSTED_PROXIES);
        }

        $_SERVER['REMOTE_ADDR'] = '173.245.64.1';
        $_SERVER['HTTP_X_FORWARDED_FOR'] = '203.0.113.5';
        Request::captureOriginalRemoteAddr();

        $this->assertFalse(Request::isFromTrustedProxy());
        $this->assertSame('173.245.64.1', zen_get_ip_address());
    }

    /**
     * IPv6 CIDR ranges must also be supported (e.g. Cloudflare also publishes IPv6 ranges).
     * 2400:cb00::/32 covers any address whose first 32 bits are 2400:cb00.
     */
    public function testCidrTrustedProxyMatchesPeerInsideIpv6Range(): void
    {
        if (!defined('TRUSTED_PROXIES')) {
            define('TRUSTED_PROXIES', self::TEST_TRUSTED_PROXIES);
        }

        $_SERVER['REMOTE_ADDR'] = '2400:cb00:1234:5678::1';
        $_SERVER['HTTP_X_FORWARDED_FOR'] = '203.0.113.5';
        Request::captureOriginalRemoteAddr();

        $this->assertTrue(Request::isFromTrustedProxy());
        $this->assertSame('203.0.113.5', zen_get_ip_address());
    }

    /**
     * The malformed entries baked into TEST_TRUSTED_PROXIES (non-numeric mask, out-of-range mask,
     * invalid subnet) must be ignored safely: a peer that doesn't match any of the *valid* entries
     * stays untrusted (a parsing bug that defaulted a bad mask to 0 would wrongly match everyone),
     * and parsing them must not throw or otherwise prevent the valid entries later in the same
     * list from still matching correctly.
     */
    public function testMalformedCidrEntriesInTrustedProxiesListAreIgnoredSafely(): void
    {
        if (!defined('TRUSTED_PROXIES')) {
            define('TRUSTED_PROXIES', self::TEST_TRUSTED_PROXIES);
        }

        $_SERVER['REMOTE_ADDR'] = '198.51.100.50';
        Request::captureOriginalRemoteAddr();
        $this->assertFalse(Request::isFromTrustedProxy());

        Request::resetOriginalRemoteAddrForTesting();
        $_SERVER['REMOTE_ADDR'] = '10.0.0.1';
        Request::captureOriginalRemoteAddr();
        $this->assertTrue(Request::isFromTrustedProxy());
    }
}
