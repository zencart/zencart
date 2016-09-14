<?php
/**
 * @package admin
 * @copyright Copyright 2003-2016 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id:$
 */

use Aura\Di\ContainerConfig as Config;
use Aura\Di\Container;

class ZenCartCommon extends Config
{
    public function define(Container $di)
    {
        $di->set('zencart_db', $GLOBALS['db']);
        $di->set('zencart_adminuser', $di->lazyNew('ZenCart\AdminUser\AdminUser'));
        $di->set('zencart_session', $di->lazyNew('ZenCart\Request\Session'));
        $di->set('zencart_request', $di->lazyNew('ZenCart\Request\Request'));
        $di->set('zencart_paginator', $di->lazyNew('ZenCart\Paginator\Paginator'));
        $di->set('zencart_notifications', $di->lazyNew('ZenCart\AdminNotifications\AdminNotifications'));
        $di->set('zencart_view', $di->lazyNew('ZenCart\View\ViewFactory'));
        $di->set('zencart_eloquent', $GLOBALS['capsule']);

        $di->params['ZenCart\AdminNotifications\AdminNotifications'] = array(
            'session' => $di->lazyGet('zencart_session'),
            'db' => $di->lazyGet('zencart_db'),
        );

        $di->params['ZenCart\Model\ModelFactory'] = array(
            'db' => $di->lazyGet('zencart_db'),
            'capsule' => $di->lazyGet('zencart_eloquent'),
        );

        $di->set('zencart_model', $di->lazyNew('ZenCart\Model\ModelFactory'));

        $di->params['ZenCart\AdminUser\AdminUser'] = array(
            'session' => $di->lazyGet('zencart_session'),
            'modelFactory' => $di->lazyGet('zencart_model'),
            'notifications' => $di->lazyGet('zencart_notifications'),
        );


        $di->params['ZenCart\Request\Request'] = array(
            'webRequest' => $di->lazyNew('Aura\Web\Request'),
            'session' => $di->lazyGet('zencart_session'),
        );
        $di->params['ZenCart\Controllers\AbstractAdminController'] = array(
            'request' => $di->lazyGet('zencart_request'),
            'modelFactory' => $di->lazyGet('zencart_model'),
            'user' => $di->lazyGet('zencart_adminuser'),
            'view' => $di->lazyGet('zencart_view'),
        );

        $di->params['ZenCart\Paginator\Paginator'] = array(
            'request' => $di->lazyGet('zencart_request'),
        );

        $di->params['ZenCart\Controllers\AbstractListingController'] = array(
            'request' => $di->lazyGet('zencart_request'),
            'modelFactory' => $di->lazyGet('zencart_model'),
            'user' => $di->lazyGet('zencart_adminuser'),
            'view' => $di->lazyGet('zencart_view'),
            'paginator' => $di->lazyGet('zencart_paginator'),
        );

//        $di->params['ZenCart\Controllers\AbstractLeadController'] = array(
//            'request' => $di->lazyGet('zencart_request'),
//            'modelFactory' => $di->lazyGet('zencart_model'),
//            'user' => $di->lazyGet('zencart_adminuser'),
//            'paginator' => $di->lazyGet('zencart_paginator'),
//        );
    }
}
