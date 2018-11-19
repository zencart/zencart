<?php
namespace Aura\Web\_Config;

use Aura\Di\Config;
use Aura\Di\Container;

class Common extends Config
{
    public function define(Container $di)
    {
        /**
         * Services.
         */
        $di->set('aura/web:response_headers', $di->lazyNew('Aura\Web\Response\Headers'));
        $di->set('aura/web:response_status', $di->lazyNew('Aura\Web\Response\Status'));
        $di->set('aura/web:response_cache', $di->lazyNew('Aura\Web\Response\Cache'));

        /**
         * Aura\Web\Request
         */
        $di->params['Aura\Web\Request'] = array(
            'client'  => $di->lazyNew('Aura\Web\Request\Client'),
            'content' => $di->lazyNew('Aura\Web\Request\Content'),
            'globals' => $di->lazyNew('Aura\Web\Request\Globals'),
            'headers' => $di->lazyNew('Aura\Web\Request\Headers'),
            'method'  => $di->lazyNew('Aura\Web\Request\Method'),
            'params'  => $di->lazyNew('Aura\Web\Request\Params'),
            'url'     => $di->lazyNew('Aura\Web\Request\Url'),
        );

        /**
         * Aura\Web\Request\Client
         */
        $di->params['Aura\Web\Request\Client'] = array(
            'server' => $_SERVER,
        );

        /**
         * Aura\Web\Request\Content
         */
        $di->params['Aura\Web\Request\Content']['server'] = $_SERVER;

        /**
         * Aura\Web\Request\Globals
         */
        $di->params['Aura\Web\Request\Globals'] = array(
            'cookies' => $di->lazyNew('Aura\Web\Request\Values', array('input' => $_COOKIE)),
            'env'     => $di->lazyNew('Aura\Web\Request\Values', array('input' => $_ENV)),
            'files'   => $di->lazyNew('Aura\Web\Request\Files',  array('input' => $_FILES)),
            'post'    => $di->lazyNew('Aura\Web\Request\Values', array('input' => $_POST)),
            'query'   => $di->lazyNew('Aura\Web\Request\Values', array('input' => $_GET)),
            'server'  => $di->lazyNew('Aura\Web\Request\Values', array('input' => $_SERVER)),
        );

        /**
         * Aura\Web\Request\Headers
         */
        $di->params['Aura\Web\Request\Headers'] = array(
            'server' => $_SERVER,
        );

        /**
         * Aura\Web\Request\Method
         */
        $di->params['Aura\Web\Request\Method'] = array(
            'server' => $_SERVER,
            'post' => $_POST,
        );

        /**
         * Aura\Web\Request\Url
         */
        $di->params['Aura\Web\Request\Url'] = array(
            'server' => $_SERVER
        );

        /**
         * Aura\Web\Response
         */
        $di->params['Aura\Web\Response'] = array(
            'status'   => $di->lazyGet('aura/web:response_status'),
            'headers'  => $di->lazyGet('aura/web:response_headers'),
            'cookies'  => $di->lazyNew('Aura\Web\Response\Cookies'),
            'content'  => $di->lazyNew('Aura\Web\Response\Content'),
            'cache'    => $di->lazyGet('aura/web:response_cache'),
            'redirect' => $di->lazyNew('Aura\Web\Response\Redirect'),
        );

        /**
         * Aura\Web\Response\Headers
         */
        $di->params['Aura\Web\Response\Headers'] = array(
            'server' => $_SERVER
        );

        /**
         * Aura\Web\Response\Content
         */
        $di->params['Aura\Web\Response\Content'] = array(
            'headers' => $di->lazyGet('aura/web:response_headers'),
        );

        /**
         * Aura\Web\Response\Cache
         */
        $di->params['Aura\Web\Response\Cache'] = array(
            'headers' => $di->lazyGet('aura/web:response_headers'),
        );

        /**
         * Aura\Web\Response\Redirect
         */
        $di->params['Aura\Web\Response\Redirect'] = array(
            'status' => $di->lazyGet('aura/web:response_status'),
            'headers' => $di->lazyGet('aura/web:response_headers'),
            'cache' => $di->lazyGet('aura/web:response_cache'),
        );
    }
}