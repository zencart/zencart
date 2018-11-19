<?php
/**
 *
 * This file is part of Aura for PHP.
 *
 * @license http://opensource.org/licenses/bsd-license.php BSD
 *
 */
namespace Aura\Web\Response;

use DateTime;
use DateTimeZone;
use Exception;

/**
 *
 * Convenience methods related to HTTP caching.
 *
 * @package Aura.Web
 *
 * @todo Move (Age, Etag, Expires, Last-Modified) to Content object?
 *
 */
class Cache
{
    /**
     *
     * Cache-Control directives and values.
     *
     * @var array
     *
     */
    protected $control = array(
        'public' => false,
        'private' => false,
        'max-age' => null,
        's-maxage' => null,
        'no-cache' => false,
        'no-store' => false,
        'must-revalidate' => false,
        'proxy-revalidate' => false,
    );

    /**
     *
     * The response headers.
     *
     * @var Headers
     *
     */
    protected $headers;

    /**
     *
     * Constructor.
     *
     * @param Headers $headers The response headers.
     *
     */
    public function __construct(Headers $headers)
    {
        $this->headers = $headers;
    }

    /**
     *
     * Disables HTTP caching.
     *
     * @return null
     *
     */
    public function disable()
    {
        $this->setAge(null);
        $this->setControl(array(
            'public' => false,
            'private' => false,
            'max-age' => 0,
            's-maxage' => null,
            'no-cache' => true,
            'no-store' => true,
            'must-revalidate' => true,
            'proxy-revalidate' => true,
        ));
        $this->setEtag(null);
        $this->setExpires('Mon, 01 Jan 0001 00:00:00 GMT');
        $this->setLastModified(null);
        $this->setVary(null);
    }

    /**
     *
     * Resets caching headers to their original state (i.e., no caching
     * headers).
     *
     * @return null
     *
     */
    public function reset()
    {
        $this->setAge(null);
        $this->setControl(array(
            'public' => false,
            'private' => false,
            'max-age' => null,
            's-maxage' => null,
            'no-cache' => false,
            'no-store' => false,
            'must-revalidate' => false,
            'proxy-revalidate' => false,
        ));
        $this->setEtag(null);
        $this->setExpires(null);
        $this->setLastModified(null);
        $this->setVary(null);
    }

    /**
     *
     * Sets the content age in seconds.
     *
     * @param int $age The content age, in seconds.
     *
     * @return null
     *
     */
    public function setAge($age)
    {
        $age = trim($age);
        if ($age === '') {
            $this->headers->set('Age', null);
        } else {
            $this->headers->set('Age', (int) $age);
        }
    }

    /**
     *
     * Sets multiple Cache-Control directives all at once.
     *
     * @param array $control An array of key-value pairs where the key is the
     * directive label and the value is the directive value.
     *
     * @return null
     *
     */
    public function setControl(array $control)
    {
        // prepare the cache-control directives
        $this->control = array_merge($this->control, $control);

        // turn off public/private if no caching
        if ($this->control['no-cache']) {
            $this->control['public'] = false;
            $this->control['private'] = false;
        }

        // shared max-age indicates public
        if ($this->control['s-maxage']) {
            $this->control['public'] = true;
            $this->control['private'] = false;
        }

        // collect the control directives
        $control = array();
        foreach ($this->control as $key => $val) {
            if ($val === true) {
                // flag
                $control[] = $key;
            } elseif ($val !== null && $val !== false) {
                // value
                $control[] = "$key=$val";
            }
        }

        // set the header; clears cache-control if empty
        $this->headers->set('Cache-Control', implode(', ', $control));

        // if we have no-cache, also send pragma
        if ($this->control['no-cache']) {
            $this->headers->set('Pragma', 'no-cache');
        } else {
            $this->headers->set('Pragma', null);
        }
    }

    /**
     *
     * Sets a strong ETag header value.
     *
     * @param string $etag The ETag header value.
     *
     * @return null
     *
     */
    public function setEtag($etag)
    {
        $etag = trim($etag);
        if ($etag) {
            $etag = '"' . $etag . '"';
        }
        $this->headers->set('Etag', $etag);
    }

    /**
     *
     * Sets the Expires header; converts to the appropriate date format.
     *
     * @param mixed $expires The Expires value; this will be converted to an
     * HTTP date format from any recognizable format (including a DateTime
     * object).
     *
     * @return null
     *
     */
    public function setExpires($expires)
    {
        $this->headers->set('Expires', $this->httpDate($expires));
    }

    /**
     *
     * Sets the Last-Modified header; converts to the appropriate date format.
     *
     * @param mixed $last_modified The Last-Modified value; this will be
     * converted to an HTTP date format from any recognizable format
     * (including a DateTime object).
     *
     * @return null
     *
     */
    public function setLastModified($last_modified)
    {
        $this->headers->set('Last-Modified', $this->httpDate($last_modified));
    }

    /**
     *
     * Sets the "max-age" cache control directive.
     *
     * @param int $max_age The maximum allowed age for the content in seconds.
     *
     * @return null
     *
     */
    public function setMaxAge($max_age)
    {
        $this->setControl(array(
            'max-age' => (int) $max_age,
        ));
    }

    /**
     *
     * Sets the "no-cache" cache control directive.
     *
     * @param bool $flag True to add the "no-cache" directive, false to remove
     * it.
     *
     * @return null
     *
     */
    public function setNoCache($flag = true)
    {
        $this->setControl(array(
            'no-cache' => (bool) $flag
        ));
    }

    /**
     *
     * Sets the "no-store" cache control directive.
     *
     * @param bool $flag True to add the "no-store" directive, false to remove
     * it.
     *
     * @return null
     *
     */
    public function setNoStore($flag = true)
    {
        $this->setControl(array(
            'no-store' => (bool) $flag
        ));
    }

    /**
     *
     * Enables the "private" cache control directive, and disables "public".
     *
     * @return null
     *
     */
    public function setPrivate()
    {
        $this->setControl(array(
            'public' => false,
            'private' => true,
        ));
    }

    /**
     *
     * Enables the "public" cache control directive, and disables "private".
     *
     * @return null
     *
     */
    public function setPublic()
    {
        $this->setControl(array(
            'public' => true,
            'private' => false,
        ));
    }

    /**
     *
     * Sets the "s-maxage" (share max age) cache control directive.
     *
     * @param int $s_maxage The maximum allowed age for the content in
     * seconds.
     *
     * @return null
     *
     */
    public function setSharedMaxAge($s_maxage)
    {
        $this->setControl(array(
            's-maxage' => (int) $s_maxage
        ));
    }

    /**
     *
     * Sets the Vary header.
     *
     * @param mixed $vary The list of Vary values.
     *
     * @return null
     *
     */
    public function setVary($vary)
    {
        $this->headers->set('Vary', implode(', ', (array) $vary));
    }

    /**
     *
     * Sets a weak ETag header value.
     *
     * @param string $etag The ETag header value.
     *
     * @return null
     *
     */
    public function setWeakEtag($etag)
    {
        $etag = trim($etag);
        if ($etag) {
            $etag = 'W/"' . $etag . '"';
        }
        $this->headers->set('Etag', $etag);
    }

    /**
     *
     * Converts any recognizable date format to an HTTP date.
     *
     * @param mixed $date The incoming date value.
     *
     * @return string A formatted date.
     *
     */
    protected function httpDate($date)
    {
        if ($date instanceof DateTime) {
            $date = clone $date;
            $date->setTimeZone(new DateTimeZone('UTC'));
            return $date->format('D, d M Y H:i:s') . ' GMT';
        }

        if (trim($date) === '') {
            return null;
        }

        try {
            // create the date in the current time zone ...
            $date = new DateTime($date);
            // ... then convert to UTC
            $date->setTimeZone(new DateTimeZone('UTC'));
        } catch (Exception $e) {
            // treat bad dates as being in the past
            $date = new DateTime('0001-01-01', new DateTimeZone('UTC'));
        }

        // return the http formatted date
        return $date->format('D, d M Y H:i:s') . ' GMT';
    }
}
