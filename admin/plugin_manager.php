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
    'maxRowCount' => 999,
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
                'params' => ['0' => zen_icon('status-green'), '1' => zen_icon('status-yellow'), '2' => zen_icon('status-red')],
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
        'options' => ['*' => TEXT_ALL_STATUSES, '0' => TEXT_INSTALLED_ENABLED, '1' => TEXT_INSTALLED_DISABLED, '2' => TEXT_NOT_INSTALLED],
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
      <style>
          .w-20 {width: 20%}
          .w-15 {width: 15%}
          .w-10 {width: 10%}
          .w-5 {width: 5%}
      </style>
</head>
<body>
<!-- header //-->
<?php require DIR_WS_INCLUDES . 'header.php'; ?>
<!-- header_eof //-->

<!-- body //-->
<div class="container-fluid">
    <h1><?php echo HEADING_TITLE; ?></h1>
    <?php if ($filterManager->hasFilters()) { ?>
    <div class="row noprint">
        <div class="form-inline">
            <div class="form-group col-xs-4 col-sm-3 col-md-3 col-lg-3">
                <form method="post" action="<?php echo zen_href_link($PHP_SELF); ?>">
                    <input type="hidden" name="securityToken" value="<?php echo $_SESSION['securityToken']; ?>">
                    <?php foreach ($filterManager->getFilters() as $filter) { ?>
                        <?php echo $filter->output(); ?>
                    <?php } ?>
                </form>
            </div>
        </div>
    </div>
    <?php } ?>

    <div class="row">
        <div class="col-xs-12 col-sm-12 col-md-9 col-lg-9 configurationColumnLeft">
        <?php
            for ($i = 0; $i < 3; $i++) {
                $firstheader = 0;
                $skip = 1;
                foreach ($formatter->getTableData() as $tableData) {
                    if ($tableData ["status"] ["original"] === $i) {
                        $skip = 0;
                        break;
                    }
                }
                if ($skip === 0) {
        ?>
            <table class="table table-hover">
                <thead>
                <tr class="dataTableHeadingRow">
                    <?php $firstheader = 0;
                        $colnumb = 0;
                        foreach ($formatter->getTableHeaders() as $colHeader) {
                        $colwidth = match(true) {
                            $colnumb === 0 => '',
                            $colnumb <= 1 => ' w-10',
                            $colnumb <= 2 => ' w-15',
                            $colnumb <= 3 => ' w-20',
                            default => ' w-10',
                        };
                    ?>
                        <th class="<?php echo $colHeader['headerClass'] . $colwidth; ?>">
                        <?php if ($firstheader === 0) {
                            $tabletitle = match($i) {
                                0 => TEXT_INSTALLED_ENABLED,
                                1 => TEXT_INSTALLED_DISABLED,
                                2 => TEXT_NOT_INSTALLED,
                            };
                            echo $tabletitle;
                            $firstheader = 1;
                            } else {
                                echo $colHeader['title'];
                            }
                            ?></th>
                    <?php $colnumb += 1;
                        } ?>
                    <th class="dataTableHeadingContent w-5 text-right"><?php echo TABLE_HEADING_ACTION; ?></th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($formatter->getTableData() as $tableData) { ?>
                    <?php if ($tableData ["status"] ["original"] === $i) {
                              if ($formatter->isRowSelected($tableData)) { ?>
                        <tr id="defaultSelected" class="dataTableRowSelected" onclick="document.location.href='<?php echo $formatter->getSelectedRowLink(
                            $tableData); ?>'" role="button">
                        <?php } else { ?>
                        <tr class="dataTableRow" onclick="document.location.href='<?php echo
                        $formatter->getNotSelectedRowLink($tableData); ?>'"
                        role="button">
                        <?php } ?>
                        <?php foreach ($tableData as $column) { ?>
                        <td class="<?php echo $column['class']; ?>">
                            <?php echo $column['value']; ?>
                        </td>
                        <?php } ?>
                        <?php require DIR_WS_TEMPLATES . 'partials/tableview_rowactions.php'; ?>
                        </tr>
                    <?php }
                } ?>
                </tbody>
            </table>
        <?php   }
            } ?>
        </div>

        <div class="col-xs-12 col-sm-12 col-md-3 col-lg-3 configurationColumnRight">
            <?php
            if (!empty($tableController->getBoxHeader()) && !empty($tableController->getBoxContent())) {
                $box = new box;
                echo $box->infoBox($tableController->getBoxHeader(), $tableController->getBoxContent());
            }
            ?>
        </div>
    </div>
<!-- body_eof //-->

<!-- footer //-->
<?php require DIR_WS_INCLUDES . 'footer.php'; ?>
<!-- footer_eof //-->
</body>
</html>
<?php require DIR_WS_INCLUDES . 'application_bottom.php'; ?>
