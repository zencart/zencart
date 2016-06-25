<?php
/**
 *
 * This file is part of Aura for PHP.
 *
 * @license http://opensource.org/licenses/bsd-license.php BSD
 *
 */
namespace Aura\Web\Request;

/**
 *
 * Information about the client.
 *
 * @package Aura.Web
 *
 */
class Client
{
    /**
     *
     * The list of 'X-Forwarded-For' values.
     *
     * @var array
     *
     */
    protected $forwarded_for = array();

    /**
     *
     * Is the 'User-Agent' recognized as a mobile agent?
     *
     * @var bool
     *
     */
    protected $mobile = null;

    /**
     *
     * Is the 'User-Agent' recognizes as a crawler robot?
     *
     * @var bool
     *
     */
    protected $crawler;

    /**
     *
     * The client IP address.
     *
     * @var string
     *
     */
    protected $ip;

    /**
     *
     * The 'Referer' value.
     *
     * @var string
     *
     */
    protected $referer;

    /**
     *
     * The 'User-Agent' string.
     *
     * @var string
     *
     */
    protected $user_agent;

    /**
     *
     * User-Agent strings used in matching mobile clients.
     *
     * @see isMobile()
     *
     * @var array
     *
     */
    protected $mobile_agents = array(
        'Android',
        'BlackBerry',
        'Blazer',
        'Brew',
        'Fennec',
        'IEMobile',
        'iPad',
        'iPhone',
        'iPod',
        'KDDI',
        'Kindle',
        'Maemo',
        'MOT-', // Motorola Internet Browser
        'NetFront',
        'Nokia',
        'Playstation',
        'Polaris',
        'PS2',
        'SEMC',
        'SymbianOS',
        'UP.Browser', // Openwave Mobile Browser
        'UP.Link',
        'Opera Mobi',
        'Opera Mini',
        'webOS', // Palm devices
        'Windows CE',
    );

    /**
     *
     * User-Agent strings used in matching crawler robot clients.
     *
     * @see isCrawler()
     *
     * @var array
     *
     */
    protected $crawler_agents = array(
        'Ask',
        'Baidu',
        'Google',
        'AdsBot',
        'gsa-crawler',
        'adidxbot',
        'librabot',
        'llssbot',
        'bingbot',
        'Danger hiptop',
        'MSMOBOT',
        'MSNBot',
        'MSR-ISRCCrawler',
        'MSRBOT',
        'Vancouver',
        'Y!J',
        'Yahoo',
        'mp3Spider',
        'Mp3Bot',
        'Scooter',
        'slurp',
        'Y!OASIS',
        'YRL_ODP_CRAWLER',
        'Yandex',
        'Fast',
        'Lycos',
        'heritrix',
        'ia_archiver',
        'InternetArchive',
        'archive.org_bot',
        'Nutch',
        'WordPress',
        'Wget'
    );

    /**
     *
     * IP addresses of trusted proxies, if any.
     *
     * @var array
     *
     */
    protected $proxies;

    /**
     *
     * The $_SERVER['PHP_AUTH_DIGEST'] value.
     *
     * @var string
     *
     */
    protected $auth_digest;

    /**
     *
     * The $_SERVER['PHP_AUTH_PW'] value.
     *
     * @var string
     *
     */
    protected $auth_pw;

    /**
     *
     * The $_SERVER['AUTH_TYPE'] value.
     *
     * @var string
     *
     */
    protected $auth_type;

    /**
     *
     * The $_SERVER['PHP_AUTH_USER'] value.
     *
     * @var string
     *
     */
    protected $auth_user;

    /**
     *
     * Constructor.
     *
     * @param array $server An array of $_SERVER values.
     *
     * @param array $mobile_agents Additional mobile agent strings.
     *
     * @param array $crawler_agents Additional crawler agent strings.
     *
     * @param array $proxies IP addresses of trusted proxies, if any.
     *
     */
    public function __construct(
        array $server,
        array $mobile_agents = array(),
        array $crawler_agents = array(),
        array $proxies = array()
    ) {
        $this->mobile_agents = array_merge($this->mobile_agents, $mobile_agents);
        $this->crawler_agents = array_merge($this->crawler_agents, $crawler_agents);
        $this->proxies = $proxies;
        $this->setAuthDigest($server);
        $this->setAuthPw($server);
        $this->setAuthType($server);
        $this->setAuthUser($server);
        $this->setReferer($server);
        $this->setUserAgent($server);
        $this->setForwardedFor($server);
        $this->setIp($server);
    }

    /**
     *
     * Sets the $auth_digest property.
     *
     * @param array $server A copy of $_SERVER.
     *
     * @return null
     *
     */
    protected function setAuthDigest(array $server)
    {
        $this->auth_digest = isset($server['PHP_AUTH_DIGEST'])
                           ? $server['PHP_AUTH_DIGEST']
                           : null;
    }

    /**
     *
     * Sets the $auth_pw property.
     *
     * @param array $server A copy of $_SERVER.
     *
     * @return null
     *
     */
    protected function setAuthPw(array $server)
    {
        $this->auth_pw = isset($server['PHP_AUTH_PW'])
                       ? $server['PHP_AUTH_PW']
                       : null;
    }

    /**
     *
     * Sets the $auth_type property.
     *
     * @param array $server A copy of $_SERVER.
     *
     * @return null
     *
     */
    protected function setAuthType(array $server)
    {
        $this->auth_type = isset($server['AUTH_TYPE'])
                         ? $server['AUTH_TYPE']
                         : null;
    }

    /**
     *
     * Sets the $auth_user property.
     *
     * @param array $server A copy of $_SERVER.
     *
     * @return null
     *
     */
    protected function setAuthUser(array $server)
    {
        $this->auth_user = isset($server['PHP_AUTH_USER'])
                         ? $server['PHP_AUTH_USER']
                         : null;
    }

    /**
     *
     * Sets the $auth_referer property.
     *
     * @param array $server A copy of $_SERVER.
     *
     * @return null
     *
     */
    protected function setReferer(array $server)
    {
        $this->referer = isset($server['HTTP_REFERER'])
                       ? $server['HTTP_REFERER']
                       : null;
    }

    /**
     *
     * Sets the $user_agent property.
     *
     * @param array $server A copy of $_SERVER.
     *
     * @return null
     *
     */
    protected function setUserAgent(array $server)
    {
        $this->user_agent = isset($server['HTTP_USER_AGENT'])
                          ? $server['HTTP_USER_AGENT']
                          : null;
    }

    /**
     *
     * Sets the $forwarded_for property.
     *
     * @param array $server A copy of $_SERVER.
     *
     * @return null
     *
     */
    protected function setForwardedFor(array $server)
    {
        if (isset($server['HTTP_X_FORWARDED_FOR'])) {
            $ips = explode(',', $server['HTTP_X_FORWARDED_FOR']);
            foreach ($ips as $ip) {
                $this->forwarded_for[] = trim($ip);
            }
        }
    }

    /**
     *
     * Sets the $ip property.
     *
     * @param array $server A copy of $_SERVER.
     *
     * @return null
     *
     */
    protected function setIp(array $server)
    {
        $ips = $this->getIps($server);
        foreach ($ips as $ip) {
            // is the IP a trusted proxy?
            if (! in_array($ip, $this->proxies)) {
                // no; treat it as the origin IP. technically we don't know
                // if this is a proxy server, the real client IP, or a spoof.
                // this is because this is the first point in the chain that
                // we know we can't trust.
                $this->ip = $ip;
                return;
            }
        }

        // still don't have an IP, use the reported remote address
        $this->ip = $ips[0];
    }

    /**
     *
     * Returns the origin IP, honoring the forwarded-for IPs.
     *
     * @param array $server A copy of $_SERVER.
     *
     * @return array
     *
     */
    protected function getIps($server)
    {
        // get the list of forwarded-for IPs, if any, and append the reported
        // remote address (in a proxy situation, it is the last proxy)
        $ips   = $this->forwarded_for;
        $ips[] = isset($server['REMOTE_ADDR'])
               ? $server['REMOTE_ADDR']
               : null;

        // set the origin IP by working through the IPs from right to left
        // (i.e., in reverse, from most to least reliable)
        return array_reverse($ips);
    }

    /**
     *
     * Returns the server `PHP_AUTH_DIGEST` value, if any.
     *
     * @return string
     *
     */
    public function getAuthDigest()
    {
        return $this->auth_digest;
    }

    /**
     *
     * Returns the server `PHP_AUTH_PW` value, if any.
     *
     * @return string
     *
     */
    public function getAuthPw()
    {
        return $this->auth_pw;
    }

    /**
     *
     * Returns the server `PHP_AUTH_USER` value, if any.
     *
     * @return string
     *
     */
    public function getAuthUser()
    {
        return $this->auth_user;
    }

    /**
     *
     * Returns the server `AUTH_TYPE` value, if any.
     *
     * @return string
     *
     */
    public function getAuthType()
    {
        return $this->auth_type;
    }

    /**
     *
     * Returns the values of the `X-Forwarded-For` headers as an array.
     *
     * @return array
     *
     */
    public function getForwardedFor()
    {
        return $this->forwarded_for;
    }

    /**
     *
     * Returns the apparent origin IP address; note that this should not be
     * treated as a trusted value in any case.
     *
     * @return string
     *
     */
    public function getIp()
    {
        return $this->ip;
    }

    /**
     *
     * Returns the value of the `Referer` header.
     *
     * @return string
     *
     */
    public function getReferer()
    {
        return $this->referer;
    }

    /**
     *
     * Returns the value of the `User-Agent` header.
     *
     * @return string
     *
     */
    public function getUserAgent()
    {
        return $this->user_agent;
    }

    /**
     *
     * Is the client a crawler?
     *
     * @return bool
     *
     */
    public function isCrawler()
    {
        if ($this->crawler === null) {
            $this->crawler = false;
            foreach ($this->crawler_agents as $regex) {
                $regex = preg_quote($regex);
                if (preg_match("/$regex/i", $this->user_agent)) {
                    $this->crawler = true;
                    break;
                }
            }
        }
        return $this->crawler;
    }

    /**
     *
     * Is the client a mobile device?
     *
     * @return bool
     *
     */
    public function isMobile()
    {
        if ($this->mobile === null) {
            $this->mobile = false;
            foreach ($this->mobile_agents as $regex) {
                $regex = preg_quote($regex);
                if (preg_match("/$regex/i", $this->user_agent)) {
                    $this->mobile = true;
                    break;
                }
            }
        }
        return $this->mobile;
    }
}
