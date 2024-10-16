<?php
/**
 * @copyright Copyright 2003-2024 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: DrByte 2024 Sep 21 Modified in v2.1.0-beta1 $
 */

use Zencart\Filters\FilterFactory;
use Zencart\Filters\FilterManager;
use Zencart\PluginManager\PluginManager;
use Zencart\PluginSupport\Installer;
use Zencart\PluginSupport\InstallerFactory;
use Zencart\PluginSupport\PluginErrorContainer;
use Zencart\PluginSupport\ScriptedInstallerFactory;
use Zencart\PluginSupport\SqlPatchInstaller;
use Zencart\ViewBuilders\DerivedItemsManager;
use Zencart\ViewBuilders\PluginManagerController;
use Zencart\ViewBuilders\PluginManagerDataSource;
use Zencart\ViewBuilders\SimpleDataFormatter;

/* @var PluginManager $pluginManager */
/* @var queryFactory $db */
/* @var messageStack $messageStack */

require('includes/application_top.php');
$pluginManager->inspectAndUpdate();

// These next few classes are only needed by the plugin manager
$errorContainer = new PluginErrorContainer();
$pluginInstaller = new Installer(new SqlPatchInstaller($db, $errorContainer), new ScriptedInstallerFactory($db, $errorContainer), $errorContainer);
$installerFactory = new InstallerFactory($db, $pluginInstaller, $errorContainer);

// define the table definition. Just using an array here, but could have used the fluent interface
$tableDefinition = [
    'colKey' => 'unique_key',
    'maxRowCount' => 20,
    'defaultRowAction' => '',
    'columns' => [
        'name' => [
            'title' => TABLE_HEADING_NAME,
            'derivedItem' => [
                'type' => 'local',
                'method' => 'getLanguageTranslationForName',
            ],
        ],
        'version' => ['title' => TABLE_HEADING_VERSION_INSTALLED],
        'filespace' => [
            'title' => TABLE_HEADING_FILE_SPACE,
            'derivedItem' => [
                'type' => 'local',
                'method' => 'getPluginFileSize',
            ],
        ],
        'unique_key' => ['title' => TABLE_HEADING_KEY],
        'status' => [
            'title' => TABLE_HEADING_STATUS,
            'derivedItem' => [
                'type' => 'local',
                'method' => 'arrayReplace',
                'params' => ['0' => TEXT_NOT_INSTALLED, '1' => TEXT_INSTALLED_ENABLED, '2' => TEXT_INSTALLED_DISABLED],
            ],
        ],
    ],
];

// Instantiate the table definition DTO
$table = new \Zencart\ViewBuilders\TableViewDefinition($tableDefinition);

// the datasource for building the initial query
$dataSource = new PluginManagerDataSource($table);
$query = $dataSource->processRequest($sanitizedRequest);

// Define filters for this view. If there were no filters we could skip this and the other calls to filterManager
$filterDefinitions = [
    [
        'type' => 'selectWhere',
        'field' => 'status',
        'label' => TEXT_LABEL_STATUS,
        'source' => 'options',
        'selectName' => 'plugin_status',
        'auto' => true,
        'options' => ['*' => TEXT_ALL_STATUSES, '0' => TEXT_NOT_INSTALLED, '1' => TEXT_INSTALLED_ENABLED, '2' => TEXT_INSTALLED_DISABLED],
    ],
];

// filter manager changes the query based on defined filters
$filterManager = new FilterManager($filterDefinitions, new FilterFactory());
$filterManager->build();
$query = $filterManager->processRequest($sanitizedRequest, $query);

// process the query now, and get the query results to pass to the formatter
$queryResults = $dataSource->processQuery($query);

//the simple formatter returns data that can be used for a simple html page
$formatter = new SimpleDataFormatter($sanitizedRequest, $table, $queryResults, new DerivedItemsManager());

// finally get a controller to respond to requests and build infoboxes etc
$tableController = new PluginManagerController($sanitizedRequest, $messageStack, $table, $formatter);
$tableController->init($pluginManager, $installerFactory);
$tableController->processRequest();

?>
<!doctype html>
<html <?= HTML_PARAMS ?>>
<head>
<?php require DIR_WS_INCLUDES . 'admin_html_head.php'; ?>
</head>
<body>
<!-- header //-->
<?php require DIR_WS_INCLUDES . 'header.php'; ?>
<!-- header_eof //-->

<!-- body //-->
<?php require 'includes/templates/table_view.php'; ?>
<!-- body_eof //-->

<!-- footer //-->
<?php require DIR_WS_INCLUDES . 'footer.php'; ?>
<!-- footer_eof //-->
</body>
</html>
<?php require DIR_WS_INCLUDES . 'application_bottom.php'; ?>
