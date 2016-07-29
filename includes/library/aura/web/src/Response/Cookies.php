<?php
/**
 *
 * This file is part of Aura for PHP.
 *
 * @license http://opensource.org/licenses/bsd-license.php BSD
 *
 */
namespace Aura\Web\Response;

/**
 *
 * Cookies to send with the response.
 *
 * @package Aura.Web
 *
 */
class Cookies
{
    /**
     *
     * The response cookies.
     *
     * @var array
     *
     */
    protected $cookies = array();

    /**
     *
     * The default cookie values.
     *
     * @var array
     *
     */
    protected $default = array(
        'value' => null,
        'expire' => 0,
        'path' => '',
        'domain' => '',
        'secure' => false,
        'httponly' => true,
    );

    /**
     *
     * Sets the default expire value.
     *
     * @param mixed $expire The default expire value.
     *
     * @return null
     *
     */
    public function setExpire($expire)
    {
        $this->default['expire'] = $expire;
    }

    /**
     *
     * Sets the default path value.
     *
     * @param string $path The default path value.
     *
     * @return null
     *
     */
    public function setPath($path)
    {
        $this->default['path'] = $path;
    }

    /**
     *
     * Sets the default domain value.
     *
     * @param string $domain The default domain value.
     *
     * @return null
     *
     */
    public function setDomain($domain)
    {
        $this->default['domain'] = $domain;
    }

    /**
     *
     * Sets the default secure value.
     *
     * @param bool $secure True to default to secure, false not.
     *
     * @return null
     *
     */
    public function setSecure($secure)
    {
        $this->default['secure'] = (bool) $secure;
    }


    /**
     *
     * Sets the default "HTTP Only" value.
     *
     * @param bool $flag True to send by HTTP only, false to send by any
     * method.
     *
     * @return null
     *
     */
    public function setHttponly($flag)
    {
        $this->default['httponly'] = (bool) $flag;
    }

    /**
     *
     * Returns the default meta-descriptors for cookies.
     *
     * @return array
     *
     */
    public function getDefault()
    {
        return $this->default;
    }

    /**
     *
     * Sets a cookie value in `$cookies`.
     *
     * @param string $name The name of the cookie.
     *
     * @param string $value The value of the cookie.
     *
     * @param int|string $expire The Unix timestamp after which the cookie
     * expires.  If non-numeric, the method uses strtotime() on the value.
     *
     * @param string $path The path on the server in which the cookie will be
     * available on.
     *
     * @param string $domain The domain that the cookie is available on.
     *
     * @param bool $secure Indicates that the cookie should only be
     * transmitted over a secure HTTPS connection.
     *
     * @param bool $httponly When true, the cookie will be made accessible
     * only through the HTTP protocol. This means that the cookie won't be
     * accessible by scripting languages, such as JavaScript.
     *
     * @return null
     *
     */
    public function set(
        $name,
        $value,
        $expire = null,
        $path = null,
        $domain = null,
        $secure = null,
        $httponly = null
    ) {
        $this->cookies[$name] = array(
            'value'    => $value,
        );

        $vars = array('expire', 'path', 'domain', 'secure', 'httponly');
        foreach ($vars as $var) {
            if ($$var !== null) {
                $this->cookies[$name][$var] = $$var;
            }
        }
    }

    /**
     *
     * Gets one cookie for the response.
     *
     * @param string $name The cookie name.
     *
     * @return array A cookie descriptor.
     *
     */
    public function get($name = null)
    {
        if (! $name) {
            $cookies = array();
            foreach ($this->cookies as $name => $cookie) {
                $cookies[$name] = $this->get($name);
            }
            return $cookies;
        }

        // merge with defaults
        $cookie = array_merge($this->default, $this->cookies[$name]);

        // try to allow for times not in unix-timestamp format
        if (! is_numeric($cookie['expire'])) {
            $cookie['expire'] = strtotime($cookie['expire']);
        }

        // force to certain types
        $cookie['expire'] = (int) $cookie['expire'];
        $cookie['secure']  = (bool) $cookie['secure'];

        // done
        return $cookie;
    }
}
