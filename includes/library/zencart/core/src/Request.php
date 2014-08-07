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
     *
     * @var $instance object
     */
    protected static $instance = null;

    /**
     * constructor
     */
    public function __construct()
    {
        $this->webFactory = new WebFactory($GLOBALS);
        $this->request = $this->webFactory->newRequest();
    }
    /**
     * get singleton instance
     *
     * @return object
     */
    public static function getInstance()
    {
        $class = __CLASS__;
        if (!self::$instance) {
            self::$instance = new  $class;
        }
        return self::$instance;
    }

    /**
     * initialize request
     */
    public static function init()
    {
        $class = self::getInstance();

        $class->notify('NOTIFY_REQUEST_SET_CONTEXT');
        $class->initParameterBag();
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
    public static function get($paramName, $paramDefault = null, $source = 'get')
    {
        $class = self::getInstance();
        if (isset($class->parameterBag [$source] [$paramName])) {
            return $class->parameterBag [$source] [$paramName];
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
    public static function readGet($paramName, $paramDefault = null)
    {
        return self::get($paramName, $paramDefault, 'get');
    }

    /**
     * alias to get a parameter bag value from GET
     *
     * @param $paramName
     * @param $paramDefault
     * @return null
     */
    public static function readPost($paramName, $paramDefault = null)
    {
        return self::get($paramName, $paramDefault, 'post');
    }

    /**
     * test whether parameter bag has a specific key
     *
     * @param $paramName
     * @param string $source
     * @return bool
     * @throws Exception
     */
    public static function has($paramName, $source = 'get')
    {
        $class = self::getInstance();
        if (!isset($class->parameterBag[$source])) {
            throw new \InvalidArgumentException('Exception: invalid source for has operation');
        }
        return ((isset($class->parameterBag[$source][$paramName])) ? true : false);
    }

    /**
     * get all values from a parameter bag
     *
     * @param string $source
     * @return mixed
     * @throws Exception
     */
    public static function all($source = 'get')
    {
        $class = self::getInstance();
        if (!isset($class->parameterBag[$source])) {
            throw new \InvalidArgumentException('Exception: invalid source for all operation');
        }
        return $class->parameterBag[$source];
    }

    /**
     * @return object
     */
    public static function getWebFactoryRequest()
    {
        $class = self::getInstance();
        return $class->request;
    }
}