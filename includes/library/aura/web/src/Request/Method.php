<?php
/**
 *
 * This file is part of Aura for PHP.
 *
 * @license http://opensource.org/licenses/bsd-license.php BSD
 *
 */
namespace Aura\Web\Request;

use BadMethodCallException;

/**
 *
 * A representation of the request method.
 *
 * @package Aura.Web
 *
 */
class Method
{
    /**
     *
     * The request method value.
     *
     * @var string
     *
     */
    protected $value;

    /**
     *
     * Constructor.
     *
     * @param array $server $_SERVER values.
     *
     * @param array $post $_POST values.
     *
     * @param string $method_field A special field to indicate a custom HTTP
     * method in place of 'POST'.
     *
     */
    public function __construct(
        array $server,
        array $post,
        $method_field = null
    ) {
        // set the original value
        if (isset($server['REQUEST_METHOD'])) {
            $this->value = strtoupper($server['REQUEST_METHOD']);
        }

        // must be a POST to do an override
        if ($this->value == 'POST') {

            // look for this method field in the post data
            if (! $method_field) {
                $method_field = '_method';
            }

            // look for override in post data
            $override = isset($post[$method_field])
                      ? $post[$method_field]
                      : false;
            if ($override) {
                $this->value = strtoupper($override);
            }

            // look for override in headers
            $override = isset($server['HTTP_X_HTTP_METHOD_OVERRIDE'])
                      ? $server['HTTP_X_HTTP_METHOD_OVERRIDE']
                      : false;
            if ($override) {
                $this->value = strtoupper($override);
            }
        }
    }

    /**
     *
     * Magic call to allow for custom is*() HTTP methods.
     *
     * @param string $func The called function.
     *
     * @param array $args The passed arguments.
     *
     * @return mixed
     *
     */
    public function __call($func, $args)
    {
        if (substr($func, 0, 2) == 'is') {
            return $this->value == strtoupper(substr($func, 2));
        }

        throw new BadMethodCallException($func);
    }

    /**
     *
     * Returns the request method value
     *
     * @return string request method value
     *
     */
    public function get()
    {
        return $this->value;
    }

    /**
     *
     * Did the request use a DELETE method?
     *
     * @return bool True|False
     *
     */
    public function isDelete()
    {
        return $this->value == 'DELETE';
    }

    /**
     *
     * Did the request use a GET method?
     *
     * @return bool True|False
     *
     */
    public function isGet()
    {
        return $this->value == 'GET';
    }

    /**
     *
     * Did the request use a HEAD method?
     *
     * @return bool True|False
     *
     */
    public function isHead()
    {
        return $this->value == 'HEAD';
    }

    /**
     *
     * Did the request use an OPTIONS method?
     *
     * @return bool True|False
     *
     */
    public function isOptions()
    {
        return $this->value == 'OPTIONS';
    }

    /**
     *
     * Did the request use a PATCH method?
     *
     * @return bool True|False
     *
     */
    public function isPatch()
    {
        return $this->value == 'PATCH';
    }

    /**
     *
     * Did the request use a PUT method?
     *
     * @return bool True|False
     *
     */
    public function isPut()
    {
        return $this->value == 'PUT';
    }

    /**
     *
     * Did the request use a POST method?
     *
     * @return bool True|False
     *
     */
    public function isPost()
    {
        return $this->value == 'POST';
    }
}
