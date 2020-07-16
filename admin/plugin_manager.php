<?php
/**
 * @copyright Copyright 2003-2020 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: Zcwilt 2020 Jun 10 Modified in v1.5.7 $
 */

use Zencart\PluginSupport\SqlPatchInstaller;
use Zencart\PluginSupport\ScriptedInstallerFactory;
use Zencart\PluginSupport\InstallerFactory;
use Zencart\PluginSupport\Installer;
use Zencart\PluginSupport\PluginErrorContainer;
use Zencart\TableViewControllers\PluginManagerController;
use Zencart\Filters\FilterFactory;

require('includes/application_top.php');

$pluginManager->inspectAndUpdate();

$errorContainer = new PluginErrorContainer();
$pluginInstaller = new Installer(new SqlPatchInstaller($db, $errorContainer), new ScriptedInstallerFactory($db, $errorContainer), $errorContainer);

$installerFactory = new InstallerFactory($db, $pluginInstaller, $errorContainer);


$tableDefinition = [
    'colKey'           => 'unique_key',
    'maxRowCount'      => 5,
    'actions'          => [
        [
            'action'                => 'new',
            'text'                  => 'new',
            'getParams'             => [],
            'showOnlyOnEmptyAction' => true
        ],
    ],
    'defaultRowAction' => '',
    'columns'          => [
        'name'       => ['title' => TABLE_HEADING_NAME],
        'unique_key' => ['title' => TABLE_HEADING_KEY],
        'filespace'  => [
            'title' => TABLE_HEADING_FILE_SPACE,
            'derivedItem'
                    => [
                'type'   => 'local',
                'method' => 'getPluginFileSize'
            ]
        ],
        'status'     => [
            'title'       => TABLE_HEADING_STATUS,
            'derivedItem' => [
                'type'   => 'local',
                'method' => 'arrayReplace',
                'params' => ['0' => TEXT_NOT_INSTALLED, '1' => TEXT_INSTALLED_ENABLED, '2' => TEXT_INSTALLED_DISABLED]
            ]
        ],
        'version'    => ['title' => TABLE_HEADING_VERSION_INSTALLED],
    ],
    'filters'          => [
        'statusFilter' => [
            'type'       => 'selectWhere',
            'field'      => 'status',
            'label'     => TEXT_LABEL_STATUS,
            'source'     => 'options',
            'selectName' => 'plugin_status',
            'auto'       => true,
            'options'    => ['*' => TEXT_ALL_STATUSES, '0' => TEXT_NOT_INSTALLED, '1' => TEXT_INSTALLED_ENABLED, '2' => TEXT_INSTALLED_DISABLED]
        ]
    ]
];

$tableController = (new PluginManagerController($sanitizedRequest, $messageStack, $tableDefinition, new FilterFactory))->init($pluginManager, $installerFactory)->processRequest();

?>
<!doctype html>
<html <?php echo HTML_PARAMS; ?>>
<head>
    <?php require DIR_WS_INCLUDES . 'admin_html_head.php'; ?>
</head>
<body>
<!-- header //-->
<?php require(DIR_WS_INCLUDES . 'header.php'); ?>
<!-- header_eof //-->

<!-- body //-->

<?php require "includes/templates/table_view.php"; ?>

<!-- body_eof //-->

<!-- footer //-->
<?php require(DIR_WS_INCLUDES . 'footer.php'); ?>
<!-- footer_eof //-->
</body>
</html>
<?php require(DIR_WS_INCLUDES . 'application_bottom.php'); ?>
