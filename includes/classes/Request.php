<?php
/**
 * @copyright Copyright 2003-2025 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: DrByte 2025 Sep 18 Modified in v2.2.0 $
 */

declare(strict_types=1);

namespace Zencart\Request;

use Zencart\Traits\Singleton;

/**
 * @since ZC v1.5.8
 */
class Request
{
    use Singleton;

    protected $paramBag;

    /**
     * The genuine TCP peer address ($_SERVER['REMOTE_ADDR']) as it was at the very start of the
     * request, captured once before any init code (notably init_sessions.php, which overwrites
     * $_SERVER['REMOTE_ADDR'] with the result of zen_get_ip_address()) can mutate it.
     *
     * Trust decisions about client-suppliable forwarded headers (isSecure(), getRequestHost(),
     * zen_get_ip_address())
     * must be made against this immutable original peer address, never against the live
     * $_SERVER['REMOTE_ADDR'] which may already have been resolved/overwritten earlier in the request.
     * A static property (rather than a define() constant) is used deliberately so
     * it can be reset between PHPUnit test cases running in the same process.
     */
    private static $originalRemoteAddr = null;

    /**
     * Per-request cache of the parsed TRUSTED_PROXIES list. TRUSTED_PROXIES is a define()'d
     * constant and cannot change during a request, so re-parsing it (explode/array_map/array_filter)
     * on every call, potentially once per zen_get_ip_address() call plus once per forwarded-header hop
     * while resolving a chain, would be pure repeated work for an unchanging result.
     * Nullable so ??= can distinguish "not yet computed" from "computed to an empty list"
     * (an absent/empty TRUSTED_PROXIES is itself a valid, cacheable result).
     */
    private static $trustedProxiesCache = null;

    /**
     * @return mixed|Request
     * @since ZC v1.5.8
     */
    static function capture()
    {
        $self = self::getInstance();
        $self->paramBag = $_REQUEST;
        return self::getInstance();
    }

    /**
     * @param $key
     * @param null $default
     * @return mixed|null
     * @since ZC v1.5.8
     */
    public function input($key, $default = null)
    {
        return (isset($this->paramBag[$key]) ? $this->paramBag[$key] : $default);
    }

    /**
     * @param $key
     * @return bool
     * @since ZC v1.5.8
     */
    public function has($key)
    {
        return (isset($this->paramBag[$key]));
    }

    /**
     * Determine whether the current request was made over HTTPS.
     *
     * Forwarded protocol headers are only authoritative when the genuine TCP peer is a
     * configured trusted proxy. Native web-server HTTPS indicators remain authoritative
     * for direct requests.
     *
     * NOTE: there are some intentional loose-comparisons here for numeric strings.
     *
     * @since ZC v2.3.0
     */
    public static function isSecure(): bool
    {
        $nativelySecure = (isset($_SERVER['HTTPS']) && (strtolower((string) $_SERVER['HTTPS']) === 'on' || $_SERVER['HTTPS'] == '1'))
            || (isset($_SERVER['SCRIPT_URI']) && stripos((string) $_SERVER['SCRIPT_URI'], 'https:') === 0)
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

        $httpsServer = defined('HTTPS_SERVER') ? strtolower(str_replace('https://', '', (string) HTTPS_SERVER)) : '';
        $forwardedHost = strtolower((string) ($_SERVER['HTTP_X_FORWARDED_HOST'] ?? ''));
        $forwardedServer = strtolower((string) ($_SERVER['HTTP_X_FORWARDED_SERVER'] ?? ''));

        return (isset($_SERVER['HTTP_X_FORWARDED_BY']) && str_contains(strtoupper((string) $_SERVER['HTTP_X_FORWARDED_BY']), 'SSL'))
            || ($forwardedHost !== '' && (str_contains(strtoupper($forwardedHost), 'SSL') || ($httpsServer !== '' && str_contains($forwardedHost, $httpsServer))))
            || ($forwardedServer !== '' && $httpsServer !== '' && str_contains($forwardedServer, $httpsServer))
            || (isset($_SERVER['HTTP_X_FORWARDED_SSL']) && ($_SERVER['HTTP_X_FORWARDED_SSL'] == '1' || strtolower((string) $_SERVER['HTTP_X_FORWARDED_SSL']) === 'on'))
            || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && in_array(strtolower((string) $_SERVER['HTTP_X_FORWARDED_PROTO']), ['ssl', 'https'], true))
            || !empty($_SERVER['HTTP_SSLSESSIONID'])
            || (isset($_SERVER['HTTP_X_FORWARDED_PORT']) && $_SERVER['HTTP_X_FORWARDED_PORT'] == '443');
    }

    /**
     * Return the request host without a port.
     *
     * A forwarded host is used only when the genuine TCP peer is trusted. If a proxy chain
     * supplies multiple hosts, the rightmost non-empty value is the one added by the proxy
     * closest to the application, so it is the only entry a client cannot forge.
     *
     * @since ZC v2.3.0
     */
    public static function getRequestHost(): string
    {
        $host = (string) ($_SERVER['HTTP_HOST'] ?? '');
        if (self::isFromTrustedProxy() && !empty($_SERVER['HTTP_X_FORWARDED_HOST'])) {
            $forwardedHosts = array_values(array_filter(
                array_map('trim', explode(',', (string) $_SERVER['HTTP_X_FORWARDED_HOST'])),
                static fn(string $forwardedHost): bool => $forwardedHost !== ''
            ));
            if ($forwardedHosts !== []) {
                $host = $forwardedHosts[array_key_last($forwardedHosts)];
            }
        }
        return self::normalizeHost($host);
    }

    /**
     * Determine whether an absolute referer URL belongs to the current request host.
     *
     * Ports are deliberately ignored. This allows Buy Now links to work when HTTP_HOST
     * includes a non-standard port while parse_url() returns only the referer hostname.
     *
     * @since ZC v2.3.0
     */
    public static function isInternalReferer(string $referer): bool
    {
        if ($referer === '') {
            return false;
        }
        $refererHost = parse_url($referer, PHP_URL_HOST);
        if (!is_string($refererHost) || $refererHost === '') {
            return false;
        }
        return self::normalizeHost($refererHost) === self::getRequestHost();
    }

    /**
     * Normalize a hostname for comparison, including bracketed IPv6 addresses.
     *
     * parse_url() returns an IPv6 host still wrapped in brackets ("[::1]") while HTTP_HOST
     * may carry both brackets and a port, so both sides of a host comparison must pass
     * through here or an IPv6 literal host can never match itself.
     *
     * @since ZC v2.3.0
     */
    private static function normalizeHost(string $host): string
    {
        $host = strtolower(trim($host));
        if (preg_match('/^\[(.*)\](?::\d+)?$/D', $host, $matches)) {
            return $matches[1];
        }
        return preg_replace('/:\d+$/D', '', $host) ?? '';
    }

    /**
     * Return the genuine TCP peer address captured at the start of the request.
     *
     * Lazily captures on first read as a safety net in case captureOriginalRemoteAddr() was not
     * already called during bootstrap; the ??= semantics still guarantee a single, stable value.
     *
     * @since ZC v2.3.0
     */
    public static function getOriginalRemoteAddr(): string
    {
        self::captureOriginalRemoteAddr();
        return self::$originalRemoteAddr ?? '';
    }

    /**
     * Capture the genuine TCP peer address once, as early as possible in the request.
     *
     * Uses null-coalescing assignment so that the first capture sticks: calling this more than
     * once during a single request is harmless and idempotent. The originally-captured value is
     * never overwritten by a later (possibly already-mutated) $_SERVER['REMOTE_ADDR'].
     *
     * @since ZC v2.3.0
     */
    public static function captureOriginalRemoteAddr(): void
    {
        self::$originalRemoteAddr ??= ($_SERVER['REMOTE_ADDR'] ?? '');
    }

    /**
     * Determine whether the genuine TCP peer is a configured trusted reverse proxy.
     *
     * The check is made against the captured original peer address, so it returns the same answer
     * regardless of where in the request it is called and regardless of whether init_sessions.php
     * has already overwritten $_SERVER['REMOTE_ADDR'].
     *
     * @since ZC v2.3.0
     */
    public static function isFromTrustedProxy(): bool
    {
        return self::isTrustedProxyAddress(self::getOriginalRemoteAddr());
    }

    /**
     * Determine whether the given address matches a configured trusted reverse proxy.
     *
     * Each TRUSTED_PROXIES entry may be a single IP (exact match) or a CIDR range
     * (e.g. 173.245.48.0/20). Providers such as Cloudflare publish their edge ranges as CIDR blocks
     * (see the TRUSTED_PROXIES doc comment in includes/dist-configure.php), so an exact-match-only
     * check could never match a real peer against the documented configuration.
     *
     * Unlike isFromTrustedProxy() (which always checks the captured original peer), this accepts
     * an arbitrary address so callers can also test intermediate hops from a forwarded-header
     * chain. See resolveClientFromForwardedChain().
     *
     * @since ZC v2.3.0
     */
    public static function isTrustedProxyAddress(string $address): bool
    {
        if ($address === '') {
            return false;
        }
        $trustedProxies = self::getTrustedProxies();
        if ($trustedProxies === []) {
            return false;
        }
        /**
         * inet_pton() on the candidate address is the same for every entry checked below (exact-IP or CIDR),
         * so it's computed once here rather than once per entry (think CF's ~20 published ranges).
         * If the address isn't a valid IP at all, no entry can match it (every match is now a binary comparison), so bail out early.
         */
        $addressBin = @inet_pton($address);
        if ($addressBin === false) {
            return false;
        }
        foreach ($trustedProxies as $proxyEntry) {
            if (self::proxyEntryMatchesPeer($addressBin, $proxyEntry)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Return the parsed TRUSTED_PROXIES list, computed once per request and cached thereafter.
     *
     * Accepts either an array or a comma-separated string; trims and drops empty entries.
     * Shared by isSecure(), getRequestHost() and zen_get_ip_address() so the trust-list parsing
     * lives in one place.
     *
     * @since ZC v2.3.0
     */
    public static function getTrustedProxies(): array
    {
        self::$trustedProxiesCache ??= self::parseTrustedProxiesConfig();
        return self::$trustedProxiesCache;
    }

    /**
     * Parse the raw TRUSTED_PROXIES configuration constant into a normalized list of proxy
     * addresses. Split out from getTrustedProxies() so the parsing itself stays simple to read;
     * only getTrustedProxies() knows about caching.
     *
     * @since ZC v2.3.0
     */
    private static function parseTrustedProxiesConfig(): array
    {
        $trustedProxiesConfig = defined('TRUSTED_PROXIES') ? constant('TRUSTED_PROXIES') : '';
        if (is_array($trustedProxiesConfig)) {
            return array_values(array_filter(array_map(static fn($proxy): string => trim((string) $proxy), $trustedProxiesConfig)));
        }
        return array_values(array_filter(array_map('trim', explode(',', (string) $trustedProxiesConfig))));
    }

    /**
     * Resolve the real client address from a forwarded-header chain (e.g. X-Forwarded-For),
     * which each hop conventionally APPENDS its observed source address to rather than
     * overwriting, so the chain reads left-to-right as [client-claimed value, hop1, hop2, ...].
     *
     * A client can freely forge the leftmost entries before the request ever reaches a trusted
     * proxy, so naively taking the first (leftmost) entry trusts attacker-controlled input even
     * when the connection genuinely came through a trusted proxy. Instead, walk the chain from
     * the right (the trusted-peer side): each trusted proxy's own append is authoritative for
     * "who connected to me", so skip entries that are themselves trusted proxies and return the
     * first (rightmost-to-leftmost) entry that isn't. That is the address the nearest trusted hop
     * actually observed. If every entry in the chain is a trusted proxy (or the chain is empty
     * after trimming), fall back to the captured original peer address.
     *
     * @since ZC v2.3.0
     */
    public static function resolveClientFromForwardedChain(string $forwardedChain): string
    {
        $hops = array_reverse(array_values(array_filter(array_map('trim', explode(',', $forwardedChain)), static fn(string $hop): bool => $hop !== '')));
        foreach ($hops as $hop) {
            if (!self::isTrustedProxyAddress($hop)) {
                return $hop;
            }
        }
        return self::getOriginalRemoteAddr();
    }

    /**
     * Match a single TRUSTED_PROXIES entry (exact IP or CIDR range) against a given address.
     *
     * $peerBin is the caller's already-computed, known-valid inet_pton($peer), passed in so it
     * isn't recomputed for every entry in a multi-entry proxy list.
     *
     * Exact-IP entries are compared as inet_pton() binary, not as raw text: IPv6 addresses have
     * multiple valid textual representations for the same address (e.g. a fully- or partially-expanded form
     * vs the "::" zero-run shorthand; "2001:db8:0:0::1" and "2001:db8::1" are the same address),
     * so a plain string comparison could silently fail to trust a genuinely configured proxy
     * whenever REMOTE_ADDR happens to be reported in a different (but equivalent)
     * textual form than what an operator typed into TRUSTED_PROXIES.
     *
     * @since ZC v2.3.0
     */
    private static function proxyEntryMatchesPeer(string $peerBin, string $proxyEntry): bool
    {
        if (!str_contains($proxyEntry, '/')) {
            $entryBin = @inet_pton($proxyEntry);
            return $entryBin !== false && $entryBin === $peerBin;
        }
        return self::ipInCidrRange($peerBin, $proxyEntry);
    }

    /**
     * Determine whether $ipBin (an already-inet_pton()'d address) falls within the given CIDR
     * range. Supports both IPv4 and IPv6; returns false on any malformed input or family mismatch
     * (e.g. an IPv4 peer against an IPv6 CIDR entry) rather than throwing,
     * since TRUSTED_PROXIES is operator-supplied configuration.
     *
     * @since ZC v2.3.0
     */
    private static function ipInCidrRange(string $ipBin, string $cidr): bool
    {
        $parts = explode('/', $cidr, 2);
        if (count($parts) !== 2 || !ctype_digit($parts[1])) {
            return false;
        }
        [$subnet, $maskBits] = $parts;
        $maskBits = (int) $maskBits;

        $subnetBin = @inet_pton($subnet);
        if ($subnetBin === false || strlen($ipBin) !== strlen($subnetBin)) {
            return false;
        }

        $totalBits = strlen($ipBin) * 8;
        if ($maskBits < 0 || $maskBits > $totalBits) {
            return false;
        }

        $fullBytes = intdiv($maskBits, 8);
        if ($fullBytes > 0 && substr($ipBin, 0, $fullBytes) !== substr($subnetBin, 0, $fullBytes)) {
            return false;
        }

        $remainderBits = $maskBits % 8;
        if ($remainderBits === 0) {
            return true;
        }

        $mask = chr((0xFF << (8 - $remainderBits)) & 0xFF);
        return (ord($ipBin[$fullBytes]) & ord($mask)) === (ord($subnetBin[$fullBytes]) & ord($mask));
    }

    /**
     * Test-only: clear the captured peer address so each PHPUnit test case can start from a clean
     * state and exercise both the trusted-proxy and not-trusted scenarios in the same process.
     *
     * Not for use in application code.
     * @internal
     *
     * @since ZC v2.3.0
     */
    public static function resetOriginalRemoteAddrForTesting(): void
    {
        self::$originalRemoteAddr = null;
    }

    /**
     * Test-only: clear the cached parsed TRUSTED_PROXIES list so each PHPUnit test case can
     * exercise a different TRUSTED_PROXIES configuration without a stale cached parse leaking in
     * from an earlier test in the same process. Not for use in application code.
     *
     * @internal
     *
     * @since ZC v2.3.0
     */
    public static function resetTrustedProxiesCacheForTesting(): void
    {
        self::$trustedProxiesCache = null;
    }
}
