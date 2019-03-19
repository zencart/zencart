<?php
/**
 * Default autoloader namespace configuration
 *
 * @package initSystem
 * @copyright Copyright 2003-2017 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version  $Id: New in v1.6.0 $
 */
define('NAMESPACE_LISTINGQUERYANDOUTPUT', 'ZenCart\ListingQueryAndOutput');
define('NAMESPACE_PAGINATOR', 'ZenCart\Paginator');
define('NAMESPACE_QUERYBUILDER', 'ZenCart\QueryBuilder');
define('NAMESPACE_REQUEST', 'ZenCart\Request');
define('NAMESPACE_DASHBOARDWIDGETS', 'ZenCart\DashboardWidget');
define('NAMESPACE_CONTROLLERS', 'App\Controllers');
define('NAMESPACE_SERVICES', 'ZenCart\Services');
define('NAMESPACE_AJAXDISPATCH', 'ZenCart\AjaxDispatch');
define('NAMESPACE_PAGE', 'ZenCart\Page');
define('NAMESPACE_CHECKOUTFLOW', 'ZenCart\CheckoutFlow');
define('NAMESPACE_VIEW', 'ZenCart\View');
define('NAMESPACE_ADMINUSER', 'ZenCart\AdminUser');
define('NAMESPACE_PHPMAILER', 'PHPMailer');
define('NAMESPACE_FORMVALIDATION', 'ZenCart\FormValidation');
define('NAMESPACE_ADMINNOTIFICATIONS', 'ZenCart\AdminNotifications');
define('NAMESPACE_MODEL', 'App\Model');
define('NAMESPACE_CONFIGLOADER', 'ZenCart\ConfigLoader');
define('NAMESPACE_CONFIGSETTINGS', 'ZenCart\ConfigSettings');
define('NAMESPACE_PLUGINMANAGER', 'ZenCart\PluginManager');
define('NAMESPACE_TRANSLATOR', 'ZenCart\Translator');


define('URL_SERVICES', 'zencart/Services/src/');
define('URL_CONTROLLERS', 'app/Controllers/');
define('URL_AJAXDISPATCH', 'zencart/AjaxDispatch/src/');
define('URL_DASHBOARDWIDGETS', 'zencart/DashboardWidget/src/');
define('URL_LISTINGQUERYANDOUTPUT', 'zencart/ListingQueryAndOutput/src/');
define('URL_PAGINATOR', 'zencart/Paginator/src/');
define('URL_QUERYBUILDER', 'zencart/QueryBuilder/src/');
define('URL_REQUEST', 'zencart/Request/src/');
define('URL_PAGE', 'zencart/Page/src/');
define('URL_CHECKOUTFLOW', 'zencart/CheckoutFlow/src/');
define('URL_VIEW', 'zencart/View/src/');
define('URL_ADMINUSER', 'zencart/AdminUser/src/');
define('URL_PHPMAILER', 'PHPMailer/src/');
define('URL_FORMVALIDATION', 'zencart/FormValidation/src/');
define('URL_ADMINNOTIFICATIONS', 'zencart/AdminNotifications/src/');
define('URL_MODEL', '/app/Model/');
define('URL_CONFIGLOADER', 'zencart/ConfigLoader/src/');
define('URL_CONFIGSETTINGS', 'zencart/ConfigSettings/src/');
define('URL_PLUGINMANAGER', 'zencart/PluginManager/src/');
define('URL_TRANSLATOR', 'zencart/Translator/src/');


/**
 * An array of namespace => basedir configurations
 */
return array(
    NAMESPACE_SERVICES => DIR_APP_LIBRARY . URL_SERVICES,
    NAMESPACE_CONTROLLERS => DIR_FS_CATALOG . URL_CONTROLLERS,
    NAMESPACE_AJAXDISPATCH => DIR_APP_LIBRARY . URL_AJAXDISPATCH,
    NAMESPACE_DASHBOARDWIDGETS => DIR_APP_LIBRARY . URL_DASHBOARDWIDGETS,
    NAMESPACE_LISTINGQUERYANDOUTPUT => DIR_APP_LIBRARY. URL_LISTINGQUERYANDOUTPUT,
    NAMESPACE_PAGINATOR => DIR_APP_LIBRARY. URL_PAGINATOR,
    NAMESPACE_QUERYBUILDER => DIR_APP_LIBRARY. URL_QUERYBUILDER,
    NAMESPACE_REQUEST => DIR_APP_LIBRARY. URL_REQUEST,
    NAMESPACE_PAGE => DIR_APP_LIBRARY. URL_PAGE,
    NAMESPACE_CHECKOUTFLOW => DIR_APP_LIBRARY. URL_CHECKOUTFLOW,
    NAMESPACE_VIEW => DIR_APP_LIBRARY. URL_VIEW,
    NAMESPACE_ADMINUSER => DIR_APP_LIBRARY. URL_ADMINUSER,
    NAMESPACE_PHPMAILER => DIR_APP_LIBRARY. URL_PHPMAILER,
    NAMESPACE_FORMVALIDATION => DIR_APP_LIBRARY. URL_FORMVALIDATION,
    NAMESPACE_ADMINNOTIFICATIONS => DIR_APP_LIBRARY. URL_ADMINNOTIFICATIONS,
    NAMESPACE_MODEL => DIR_FS_CATALOG . URL_MODEL,
    NAMESPACE_CONFIGLOADER => DIR_APP_LIBRARY. URL_CONFIGLOADER,
    NAMESPACE_CONFIGSETTINGS => DIR_APP_LIBRARY. URL_CONFIGSETTINGS,
    NAMESPACE_PLUGINMANAGER => DIR_APP_LIBRARY. URL_PLUGINMANAGER,
    NAMESPACE_TRANSLATOR => DIR_APP_LIBRARY. URL_TRANSLATOR,
);
