<?php
/**
 *
 * This file is part of Aura for PHP.
 *
 * @license http://opensource.org/licenses/bsd-license.php BSD
 *
 */
namespace Aura\Web;

/**
 *
 * Descriptors for building an HTTP response; note that this is not itself an
 * HTTP response.
 *
 * @package Aura.Web
 *
 * @property-read Response\Cache $cache A Cache object.
 *
 * @property-read Response\Content $content A Content object.
 *
 * @property-read Response\Cookies $cookies A Cookies object.
 *
 * @property-read Response\Headers $headers A Headers object.
 *
 * @property-read Response\Redirect $redirect A Redirect object.
 *
 * @property-read Response\Status $status A Status object.
 *
 */
class Response
{
    /**
     *
     * A Cache object.
     *
     * @var Response\Cache
     *
     */
    protected $cache;

    /**
     *
     * A Content object.
     *
     * @var Response\Content
     *
     */
    protected $content;

    /**
     *
     * A Cookies object.
     *
     * @var Response\Cookies
     *
     */
    protected $cookies;

    /**
     *
     * A Headers object.
     *
     * @var Response\Headers
     *
     */
    protected $headers;

    /**
     *
     * A Redirect object.
     *
     * @var Response\Redirect
     *
     */
    protected $redirect;

    /**
     *
     * A Status object.
     *
     * @var Response\Status
     *
     */
    protected $status;

    /**
     *
     * Constructor.
     *
     * @param Response\Status $status A status object.
     *
     * @param Response\Headers $headers A headers object.
     *
     * @param Response\Cookies $cookies A cookies object.
     *
     * @param Response\Content $content A content object.
     *
     * @param Response\Cache $cache A cache object.
     *
     * @param Response\Redirect $redirect A redirect object.
     *
     */
    public function __construct(
        Response\Status   $status,
        Response\Headers  $headers,
        Response\Cookies  $cookies,
        Response\Content  $content,
        Response\Cache    $cache,
        Response\Redirect $redirect
    ) {
        $this->status   = $status;
        $this->headers  = $headers;
        $this->cookies  = $cookies;
        $this->content  = $content;
        $this->cache    = $cache;
        $this->redirect = $redirect;
    }

    /**
     *
     * Read-only access to property objects.
     *
     * @param string $key The name of the property object to read.
     *
     * @return mixed The property object.
     *
     */
    public function __get($key)
    {
        return $this->$key;
    }
}
