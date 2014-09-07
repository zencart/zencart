<?php
/**
 * File contains just the request class
 *
 * @package classes
 * @copyright Copyright 2003-2014 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: $
 */
/**
 * class request
 *
 * @package classes
 */

use Aura\Web\WebFactory;
use Aura\Web\Request as WebRequest;

class Request extends \base
{
    /**
     * @var Aura\Web\Request
     */
    protected $request;
    /**
     * @var Aura\Web\WebFactory
     */
    protected $webFactory;
    /**
     * @var array
     */
    protected $parameterBag;

    /**
     * constructor
     */
    public function __construct(WebRequest $webRequest = null)
    {
        $this->request = $webRequest ?: (new WebFactory($GLOBALS))->newRequest();
        $this->initParameterBag();
    }

    /**
     * create the parameter bags for get/post
     *
     */
    protected function initParameterBag()
    {
        $this->parameterBag ['post'] = $this->request->post->get();
        $this->parameterBag ['get'] = $this->request->query->get();
    }

    /**
     * get a parameter value from the parameter bag
     *
     * @param $paramName
     * @param null $paramDefault
     * @param string $source
     * @return null
     * @throws Exception
     */
    public function get($paramName, $paramDefault = null, $source = 'get')
    {
        if (isset($this->parameterBag [$source] [$paramName])) {
            return $this->parameterBag [$source] [$paramName];
        }
        if (isset($paramDefault)) {
            return $paramDefault;
        }
        throw new \InvalidArgumentException('Exception: Could not Request::get paramName = ' . $paramName);
    }

    /**
     * alias to get a parameter bag value from GET
     * @param $paramName
     * @param $paramDefault
     * @return null
     */
    public function readGet($paramName, $paramDefault = null)
    {
        return $this->get($paramName, $paramDefault, 'get');
    }

    /**
     * alias to get a parameter bag value from GET
     *
     * @param $paramName
     * @param $paramDefault
     * @return null
     */
    public function readPost($paramName, $paramDefault = null)
    {
        return $this->get($paramName, $paramDefault, 'post');
    }

    /**
     * test whether parameter bag has a specific key
     *
     * @param $paramName
     * @param string $source
     * @return bool
     * @throws Exception
     */
    public function has($paramName, $source = 'get')
    {
        if (!isset($this->parameterBag[$source])) {
            throw new \InvalidArgumentException('Exception: invalid source for has operation');
        }
        return ((isset($this->parameterBag[$source][$paramName])) ? true : false);
    }

    /**
     * get all values from a parameter bag
     *
     * @param string $source
     * @return mixed
     * @throws Exception
     */
    public function all($source = 'get')
    {
        if (!isset($this->parameterBag[$source])) {
            throw new \InvalidArgumentException('Exception: invalid source for all operation');
        }
        return $this->parameterBag[$source];
    }

    /**
     * @return object
     */
    public function getWebFactoryRequest()
    {
        return $this->request;
    }
}
