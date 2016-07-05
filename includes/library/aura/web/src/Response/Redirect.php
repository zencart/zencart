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
 * Convenience methods for redirection. Note that the "201 Created" status
 * is provided even though it is not in the 3XX family of redirect statuses.
 *
 * @package Aura.Web
 *
 */
class Redirect
{
    /**
     *
     * A shared status object.
     *
     * @param Status
     *
     */
    protected $status;

    /**
     *
     * A shared headers object.
     *
     * @param Headers
     *
     */
    protected $headers;

    /**
     *
     * A shared cache object.
     *
     * @param Cache
     *
     */
    protected $cache;

    /**
     *
     * Constructor.
     *
     * @param Status $status The response status.
     *
     * @param Headers $headers The response headers.
     *
     * @param Cache $cache The response caching headers.
     *
     */
    public function __construct(
        Status $status,
        Headers $headers,
        Cache $cache
    ) {
        $this->status = $status;
        $this->headers = $headers;
        $this->cache = $cache;
    }

    /**
     *
     * Redirect to an arbitrary location with a status code and phrase.
     *
     * @param string $location The URL to redirect to.
     *
     * @param int $code Use this status code.
     *
     * @param string $phrase Use this status phrase.
     *
     * @return null
     *
     */
    public function to($location, $code = 302, $phrase = null)
    {
        $this->headers->set('Location', $location);
        $this->status->setCode($code);
        if ($phrase) {
            $this->status->setPhrase($phrase);
        }
    }

    /**
     *
     * Alias for `seeOther()`; redirects with status "303 See Other" and
     * disables caching.
     *
     * @param string $location The URL to redirect to.
     *
     * @return null
     *
     */
    public function afterPost($location)
    {
        $this->seeOther($location);
    }

    /**
     *
     * Redirects with status "201 Created".
     *
     * @param string $location The URL to redirect to.
     *
     * @return null
     *
     */
    public function created($location)
    {
        $this->to($location, 201);
    }

    /**
     *
     * Redirects with status "301 Moved Permanently".
     *
     * @param string $location The URL to redirect to.
     *
     * @return null
     *
     */
    public function movedPermanently($location)
    {
        $this->to($location, 301);
    }

    /**
     *
     * Redirects with status "302 Found".
     *
     * @param string $location The URL to redirect to.
     *
     * @return null
     *
     */
    public function found($location)
    {
        $this->to($location, 302);
    }

    /**
     *
     * Redirects with status "303 See Other" and disables caching.
     *
     * @param string $location The URL to redirect to.
     *
     * @return null
     *
     */
    public function seeOther($location)
    {
        $this->to($location, 303);
        $this->cache->disable();
    }

    /**
     *
     * Redirects with status "307 Temporary Redirect".
     *
     * @param string $location The URL to redirect to.
     *
     * @return null
     *
     */
    public function temporaryRedirect($location)
    {
        $this->to($location, 307);
    }

    /**
     *
     * Redirects with status "308 Permanent Redirect".
     *
     * @param string $location The URL to redirect to.
     *
     * @return null
     *
     */
    public function permanentRedirect($location)
    {
        $this->to($location, 308);
    }
}
