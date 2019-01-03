<?php
/**
 * @package admin
 * @copyright Copyright 2003-2017 Zen Cart Development Team
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
        $di->set('zencart_widgetmanager', $di->lazyNew('ZenCart\DashboardWidget\WidgetManager'));
        $di->set('zencart_configsettings', $di->lazyNew('ZenCart\ConfigSettings\ConfigSettingsFactory'));

        $di->params['ZenCart\AdminNotifications\AdminNotifications'] = array(
            'session' => $di->lazyGet('zencart_session'),
            'db' => $di->lazyGet('zencart_db'),
        );

        $di->params['App\Model\ModelFactory'] = array(
            'db' => $di->lazyGet('zencart_db'),
            'capsule' => $di->lazyGet('zencart_eloquent'),
        );

        $di->set('zencart_model', $di->lazyNew('App\Model\ModelFactory'));

        $di->params['ZenCart\AdminUser\AdminUser'] = array(
            'session' => $di->lazyGet('zencart_session'),
            'modelFactory' => $di->lazyGet('zencart_model'),
            'notifications' => $di->lazyGet('zencart_notifications'),
        );


        $di->params['ZenCart\Request\Request'] = array(
            'webRequest' => $di->lazyNew('Aura\Web\Request'),
            'session' => $di->lazyGet('zencart_session'),
        );

        $di->params['App\Controllers\AbstractAdminController'] = array(
            'request' => $di->lazyGet('zencart_request'),
            'modelFactory' => $di->lazyGet('zencart_model'),
            'user' => $di->lazyGet('zencart_adminuser'),
            'view' => $di->lazyGet('zencart_view'),
        );

        $di->params['ZenCart\Paginator\Paginator'] = array(
            'request' => $di->lazyGet('zencart_request'),
        );

        $di->params['App\Controllers\AbstractListingController'] = array(
            'request' => $di->lazyGet('zencart_request'),
            'modelFactory' => $di->lazyGet('zencart_model'),
            'user' => $di->lazyGet('zencart_adminuser'),
            'view' => $di->lazyGet('zencart_view'),
            'paginator' => $di->lazyGet('zencart_paginator'),
        );

        $di->params['ZenCart\DashboardWidget\WidgetManager'] = array(
            'modelFactory' => $di->lazyGet('zencart_model'),
            'adminUser' => $di->lazyGet('zencart_adminuser'),
            'configSettingsFactory' => $di->lazyGet('zencart_configsettings'),
        );

        $di->params['App\Controllers\admin\Index'] = array(
            'request' => $di->lazyGet('zencart_request'),
            'modelFactory' => $di->lazyGet('zencart_model'),
            'user' => $di->lazyGet('zencart_adminuser'),
            'view' => $di->lazyGet('zencart_view'),
            'widgetManager' => $di->lazyGet('zencart_widgetmanager'),
        );

        $di->params['App\Controllers\AjaxDashboardWidget'] = array(
            'request' => $di->lazyGet('zencart_request'),
            'widgetManager' => $di->lazyGet('zencart_widgetmanager'),
        );

//        $di->params['ZenCart\Controllers\AbstractLeadController'] = array(
//            'request' => $di->lazyGet('zencart_request'),
//            'modelFactory' => $di->lazyGet('zencart_model'),
//            'user' => $di->lazyGet('zencart_adminuser'),
//            'paginator' => $di->lazyGet('zencart_paginator'),
//        );
    }
}
