<?php

use Aura\Di\ContainerConfig as Config;
use Aura\Di\Container;

class ZenCartCommon extends Config
{
    public function define(Container $di)
    {


        $di->set('zencart_db', $GLOBALS['db']);
        $di->set('zencart_session', $di->lazyNew('ZenCart\Request\Session'));
        $di->set('zencart_request', $di->lazyNew('ZenCart\Request\Request'));
        $di->set('zencart_paginator', $di->lazyNew('ZenCart\Paginator\Paginator'));

        $di->params['ZenCart\Request\Request'] = array(
            'webRequest' => $di->lazyNew('Aura\Web\Request'),
            'session' => $di->lazyGet('zencart_session'),
        );

        $di->params['ZenCart\Paginator\Paginator'] = array(
            'request' => $di->lazyGet('zencart_request'),
        );

    }
}
