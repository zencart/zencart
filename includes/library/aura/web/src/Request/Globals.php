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
 * A collection of objects representing superglobals.
 *
 * @package Aura.Web
 *
 * @property-read Values $cookies Object representing $_COOKIES values.
 *
 * @property-read Values $env Object representing $_ENV values.
 *
 * @property-read Files $files Object representing $_FILES values.
 *
 * @property-read Values $post Object representing $_POST values.
 *
 * @property-read Values $query Object representing $_GET values.
 *
 * @property-read Values $server Object representing $_SERVER values.
 *
 */
class Globals
{
    /**
     *
     * Object representing $_COOKIES values.
     *
     * @var Values
     *
     */
    protected $cookies;

    /**
     *
     * Object representing $_ENV values.
     *
     * @var Values
     *
     */
    protected $env;

    /**
     *
     * Object representing $_FILES values.
     *
     * @var Files
     *
     */
    protected $files;

    /**
     *
     * Object representing $_POST values.
     *
     * @var Values
     *
     */
    protected $post;

    /**
     *
     * Object representing $_GET values.
     *
     * @var Values
     *
     */
    protected $query;

    /**
     *
     * Object representing $_SERVER values.
     *
     * @var Values
     *
     */
    protected $server;

    /**
     *
     * Constructor.
     *
     * @param Values $cookies A $_COOKIES representation.
     *
     * @param Values $env A $_ENV representation.
     *
     * @param Files $files A $_FILES representation.
     *
     * @param Values $post A $_POST representation.
     *
     * @param Values $query A $_GET representation.
     *
     * @param Values $server A $_SERVER representation.
     *
     */
    public function __construct(
        Values $cookies,
        Values $env,
        Files  $files,
        Values $post,
        Values $query,
        Values $server
    ) {
        $this->cookies = $cookies;
        $this->env = $env;
        $this->files = $files;
        $this->post = $post;
        $this->query = $query;
        $this->server = $server;
    }

    /**
     *
     * Returns a property object.
     *
     * @param string $key The property object to return.
     *
     * @return Values|Files The property object.
     *
     */
    public function __get($key)
    {
        return $this->$key;
    }
}
