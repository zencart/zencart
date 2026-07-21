<?php
/**
 * @copyright Copyright 2003-2026 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 *
 * Trusted-proxy hardening coverage for zen_get_ip_address(): forwarded IP headers
 * (X-Forwarded-For et al.) are only honored when the genuine TCP peer is a configured
 * TRUSTED_PROXIES entry, and forwarded-header chains are resolved from the trusted (right) side
 * rather than trusting the client-suppliable leftmost entry.
 */

namespace Tests\Unit\testsSundry;

use PHPUnit\Framework\Attributes\RunInSeparateProcess;
use Tests\Support\zcUnitTestCase;
use Zencart\Request\Request;

/**
 * Several tests in this class call define() on the global TRUSTED_PROXIES constant, which PHP
 * cannot undefine or redefine within a process. Per AGENTS.md's test-suite guidance, each such
 * test carries the method-level #[RunInSeparateProcess] attribute rather than isolating the whole
 * class with #[RunTestsInSeparateProcesses] — only those specific methods get their own process,
 * so a TRUSTED_PROXIES value defined in one can never leak into another, while the majority of
 * tests in this file keep running fast, in-process.
 */
class RequestSecurityTest extends zcUnitTestCase
{
    /**
     * Shared combined TRUSTED_PROXIES value used by every test that needs a trusted-proxy
     * configuration: an exact IP, an IPv4 and an IPv6 CIDR range (mirroring how a real deployment
     * lists a provider's published ranges, e.g. Cloudflare's, alongside an exact address), plus
     * three deliberately malformed entries (non-numeric mask, out-of-range mask, invalid subnet)
     * used by testMalformedCidrEntriesInTrustedProxiesListAreIgnoredSafely() to confirm a typo in
     * one entry neither matches everything nor breaks parsing of the rest of the list. Since each
     * test that defines TRUSTED_PROXIES runs in its own process, this constant no longer needs to
     * be identical across tests for correctness — it's kept as one shared value purely to avoid
     * repeating the same six-entry string in every test method.
     */
    private const TEST_TRUSTED_PROXIES = '10.0.0.1,173.245.48.0/20,2400:cb00::/32,10.0.0.0/abc,10.0.0.0/40,not-an-ip/20';

    public function setUp(): void
    {
        parent::setUp();
        require_once DIR_FS_CATALOG . 'includes/classes/Request.php';
        require_once DIR_FS_CATALOG . 'includes/functions/functions_traffic.php';

        $_SERVER = [];

        /**
         * zen_get_ip_address() bases its trust decision on the peer address that Request captures
         * once per request. Clear that captured value so each test starts clean and can
         * independently exercise the trusted-proxy and not-trusted scenarios.
         */
        Request::resetOriginalRemoteAddrForTesting();

        /**
         * Request::getTrustedProxies() caches its parsed result per request. Clear it too, so a
         * test that doesn't define TRUSTED_PROXIES isn't affected by a parse cached from an earlier
         * test in the same (non-isolated) process, and so tests remain independent of execution
         * order.
         */
        Request::resetTrustedProxiesCacheForTesting();
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
    #[RunInSeparateProcess]
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
     * even after $_SERVER['REMOTE_ADDR'] is overwritten. This is the core defense against the
     * visitor-count inflation / IP-block evasion reported by forged forwarded headers.
     */
    #[RunInSeparateProcess]
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
     * With TRUSTED_PROXIES empty/undefined (the default for the overwhelming majority of stores,
     * which sit behind no proxy), a forged X-Forwarded-For must be ignored outright and the real
     * peer returned. This is the default-safe upgrade behavior: no configuration change is needed
     * for forged-header spoofing to stop working. TRUSTED_PROXIES is left undefined here, so this
     * runs in the shared, non-isolated process.
     */
    public function testForwardedForIgnoredByDefaultWhenNoTrustedProxiesConfigured(): void
    {
        $_SERVER['REMOTE_ADDR'] = '198.51.100.20';
        $_SERVER['HTTP_X_FORWARDED_FOR'] = '203.0.113.5';

        Request::captureOriginalRemoteAddr();

        $this->assertFalse(Request::isFromTrustedProxy());
        $this->assertSame('198.51.100.20', zen_get_ip_address());
    }

    /**
     * TRUSTED_PROXIES entries are commonly published as CIDR ranges rather than individual IPs
     * (e.g. Cloudflare's documented edge ranges — see includes/dist-configure.php).
     * An exact-string-match-only check could never match a real peer against such a range,
     * so the trusted-proxy check must support CIDR membership.
     * 173.245.48.0/20 covers 173.245.48.0–173.245.63.255.
     */
    #[RunInSeparateProcess]
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
    #[RunInSeparateProcess]
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
    #[RunInSeparateProcess]
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
    #[RunInSeparateProcess]
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

    /**
     * Request::getTrustedProxies() explicitly supports TRUSTED_PROXIES as a PHP array (not just a
     * comma-delimited string) — every other test in this file uses the string form, so the array
     * form has never actually been exercised. This intentionally defines TRUSTED_PROXIES with a
     * different shape than TEST_TRUSTED_PROXIES, so it must run in its own process.
     */
    #[RunInSeparateProcess]
    public function testTrustedProxiesConfiguredAsArrayIsHonored(): void
    {
        if (!defined('TRUSTED_PROXIES')) {
            define('TRUSTED_PROXIES', ['10.0.0.1', '173.245.48.0/20']);
        }

        $_SERVER['REMOTE_ADDR'] = '173.245.50.5';
        $_SERVER['HTTP_X_FORWARDED_FOR'] = '203.0.113.9';
        Request::captureOriginalRemoteAddr();

        $this->assertTrue(Request::isFromTrustedProxy());
        $this->assertSame('203.0.113.9', zen_get_ip_address());
    }

    /**
     * A trusted proxy conventionally APPENDS its own observed source address to an existing
     * X-Forwarded-For value rather than replacing it — a client talking directly to the proxy can
     * freely set an arbitrary leftmost value before the proxy appends its own (authoritative)
     * observation. Naively taking the first (leftmost) entry would trust that attacker-controlled
     * value even though the connection genuinely came through a trusted proxy. This is the core
     * regression test for that spoofing vector: the forged leftmost entry must be ignored, and the
     * rightmost entry (what the trusted proxy actually appended) must be used instead.
     */
    #[RunInSeparateProcess]
    public function testAttackerSpoofedLeadingForwardedForEntryIsIgnored(): void
    {
        if (!defined('TRUSTED_PROXIES')) {
            define('TRUSTED_PROXIES', self::TEST_TRUSTED_PROXIES);
        }

        $_SERVER['REMOTE_ADDR'] = '10.0.0.1';
        $_SERVER['HTTP_X_FORWARDED_FOR'] = '6.6.6.6, 203.0.113.9';
        Request::captureOriginalRemoteAddr();

        $this->assertSame('203.0.113.9', zen_get_ip_address());
    }

    /**
     * With no trusted proxies in the chain besides REMOTE_ADDR itself, a multi-entry
     * X-Forwarded-For value should resolve to its rightmost entry — the one appended by the
     * nearest (trusted) hop — not the leftmost, client-suppliable one.
     */
    #[RunInSeparateProcess]
    public function testForwardedForChainTakesRightmostEntryWhenFromTrustedProxy(): void
    {
        if (!defined('TRUSTED_PROXIES')) {
            define('TRUSTED_PROXIES', self::TEST_TRUSTED_PROXIES);
        }

        $_SERVER['REMOTE_ADDR'] = '10.0.0.1';
        $_SERVER['HTTP_X_FORWARDED_FOR'] = '203.0.113.7, 10.0.0.2, 10.0.0.3';
        Request::captureOriginalRemoteAddr();

        $this->assertSame('10.0.0.3', zen_get_ip_address());
    }

    /**
     * A chain can pass through more than one trusted hop (e.g. a CDN edge behind a second internal
     * proxy, both listed in TRUSTED_PROXIES). Each trusted hop's own append must be skipped in
     * turn, walking right-to-left, until the first non-trusted entry — the real client — is found.
     */
    #[RunInSeparateProcess]
    public function testForwardedForChainSkipsMultipleTrustedHopsToFindRealClient(): void
    {
        if (!defined('TRUSTED_PROXIES')) {
            define('TRUSTED_PROXIES', self::TEST_TRUSTED_PROXIES);
        }

        $_SERVER['REMOTE_ADDR'] = '10.0.0.1';
        $_SERVER['HTTP_X_FORWARDED_FOR'] = '203.0.113.9, 173.245.50.5, 10.0.0.1';
        Request::captureOriginalRemoteAddr();

        $this->assertSame('203.0.113.9', zen_get_ip_address());
    }

    /**
     * If every entry in the forwarded chain is itself a trusted proxy (a malformed or unusual
     * chain with no genuine client entry), resolution must fall back to the captured original
     * peer address rather than returning a trusted-proxy's own address as if it were the client,
     * or an empty/invalid value.
     */
    #[RunInSeparateProcess]
    public function testForwardedForChainFallsBackToPeerWhenEveryHopIsTrusted(): void
    {
        if (!defined('TRUSTED_PROXIES')) {
            define('TRUSTED_PROXIES', self::TEST_TRUSTED_PROXIES);
        }

        $_SERVER['REMOTE_ADDR'] = '10.0.0.1';
        $_SERVER['HTTP_X_FORWARDED_FOR'] = '10.0.0.1, 173.245.50.5';
        Request::captureOriginalRemoteAddr();

        $this->assertSame('10.0.0.1', zen_get_ip_address());
    }

    /**
     * A trusted proxy that simply doesn't send any forwarded header at all must still resolve
     * cleanly to the genuine peer address — the null-coalescing fallback chain in
     * zen_get_ip_address() must not be skipped or produce an empty/invalid result.
     */
    #[RunInSeparateProcess]
    public function testTrustedProxyWithNoForwardedHeaderFallsBackToPeerAddress(): void
    {
        if (!defined('TRUSTED_PROXIES')) {
            define('TRUSTED_PROXIES', self::TEST_TRUSTED_PROXIES);
        }

        $_SERVER['REMOTE_ADDR'] = '10.0.0.1';
        Request::captureOriginalRemoteAddr();

        $this->assertTrue(Request::isFromTrustedProxy());
        $this->assertSame('10.0.0.1', zen_get_ip_address());
    }

    /**
     * A CIDR entry for one address family must never match a peer from the other family.
     * TEST_TRUSTED_PROXIES' IPv4 CIDR entry (173.245.48.0/20) must not match an IPv6 peer, and the
     * IPv6 CIDR entry (2400:cb00::/32) is for a different range entirely, so this peer matches
     * nothing in the list and the spoofed X-Forwarded-For must be ignored.
     */
    #[RunInSeparateProcess]
    public function testCidrTrustedProxyDoesNotMatchAcrossAddressFamilies(): void
    {
        if (!defined('TRUSTED_PROXIES')) {
            define('TRUSTED_PROXIES', self::TEST_TRUSTED_PROXIES);
        }

        $_SERVER['REMOTE_ADDR'] = 'fe80::1';
        $_SERVER['HTTP_X_FORWARDED_FOR'] = '203.0.113.5';
        Request::captureOriginalRemoteAddr();

        $this->assertFalse(Request::isFromTrustedProxy());
        $this->assertSame('fe80::1', zen_get_ip_address());
    }

    /**
     * When the resolved address fails IP validation (filter_var(..., FILTER_VALIDATE_IP)),
     * zen_get_ip_address() must return the '.' sentinel rather than the raw invalid value or an
     * empty string — callers throughout the codebase (last-login-IP storage, coupon-redemption
     * logging, etc.) rely on always getting a non-empty, filterable result. This peer is untrusted
     * (TRUSTED_PROXIES is undefined in this shared, non-isolated process), so it resolves straight
     * to the captured original peer address, which here is deliberately not a valid IP. The
     * invalid-IP branch also calls $GLOBALS['zco_notifier']->notify(), which the unit test
     * bootstrap already wires up to a real (observerless, so side-effect-free) notifier instance.
     */
    public function testInvalidIpReturnsDotPlaceholder(): void
    {
        $_SERVER['REMOTE_ADDR'] = 'not-a-valid-ip-address';
        Request::captureOriginalRemoteAddr();

        $this->assertSame('.', zen_get_ip_address());
    }

    /**
     * An exact-IP TRUSTED_PROXIES entry must match on IP-address equivalence, not raw text.
     * IPv6 has multiple valid textual representations of the same address — "2001:db8:0:0::1" and
     * "2001:db8::1" both expand to the same 16-byte address (confirmed: inet_pton() on both
     * produces identical binary). A naive string comparison would treat them as different entries
     * and silently refuse to trust a genuinely configured proxy whenever REMOTE_ADDR happens to be
     * reported in a different (but equivalent) form than what was typed into TRUSTED_PROXIES. This
     * uses its own TRUSTED_PROXIES value (not the shared TEST_TRUSTED_PROXIES) since it needs a
     * specific IPv6 exact-match entry not present in the shared config.
     */
    #[RunInSeparateProcess]
    public function testExactMatchTrustedProxyEntryMatchesEquivalentIpv6Representation(): void
    {
        if (!defined('TRUSTED_PROXIES')) {
            define('TRUSTED_PROXIES', '2001:db8:0:0::1');
        }

        $_SERVER['REMOTE_ADDR'] = '2001:db8::1';
        $_SERVER['HTTP_X_FORWARDED_FOR'] = '203.0.113.9';
        Request::captureOriginalRemoteAddr();

        $this->assertTrue(Request::isFromTrustedProxy());
        $this->assertSame('203.0.113.9', zen_get_ip_address());
    }

    /**
     * A malformed exact-match entry (not a valid IP at all) must be ignored safely rather than
     * matching via a stray string comparison — TRUSTED_PROXIES is operator-supplied configuration
     * that may contain a typo. The peer here is a genuinely valid IP (so the check reaches the
     * per-entry comparison rather than short-circuiting on an invalid peer), it simply doesn't
     * match the malformed entry.
     */
    #[RunInSeparateProcess]
    public function testMalformedExactMatchEntryIsIgnoredSafely(): void
    {
        if (!defined('TRUSTED_PROXIES')) {
            define('TRUSTED_PROXIES', 'not-an-ip-at-all');
        }

        $_SERVER['REMOTE_ADDR'] = '10.0.0.1';
        Request::captureOriginalRemoteAddr();

        $this->assertFalse(Request::isFromTrustedProxy());
    }
}
