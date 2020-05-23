<?php

namespace Zencart\TableViewControllers;

use Zencart\FileSystem\FileSystem;

class PluginManagerController extends BaseController
{
    public function __construct(
        $dbConn, $messageStack, $queryBuilder, $tableDefinition, $installerFactory,
        $pluginManager
    ) {
        $this->installerFactory = $installerFactory;
        $this->pluginManager = $pluginManager;
        parent::__construct($dbConn, $messageStack, $queryBuilder, $tableDefinition);
    }

    protected function buildListQuery()
    {
        $queryParts['mainTable']['table'] = TABLE_PLUGIN_CONTROL;
        $queryParts['mainTable']['countField'] = 'id';
        return $queryParts;
    }

    protected function processDefaultAction()
    {
        if (!isset($this->tableObjInfo)) {
            return;
        }
        $this->tableDefinition['header'][] = array(
            'text' => '<h4>' . zen_output_string_protected
                (
                    $this->tableObjInfo->name) . '</h4>'
        );
        if ($this->tableObjInfo->status == 1) {
            $this->tableDefinition['content'][] = [
                'text' => '<br>' . sprintf(TEXT_VERSION_INSTALLED, $this->tableObjInfo->version) . '<br>'
            ];
        }
        $this->tableDefinition['content'][] = [
            'text' => '<br>' . TEXT_INFO_DESCRIPTION . '<br>' .
                $this->tableObjInfo->description
        ];
        if ($this->tableObjInfo->status == 0) {
            $this->tableDefinition['content'][] = array(
                'align' => 'text-center', 'text' => '<a href="' . zen_href_link
                    (
                        FILENAME_PLUGIN_MANAGER,
                        'page=' . $this->page . '&colKey=' . $this->tableObjInfo->unique_key . '&action=install') . '" class="btn btn-primary" role="button">' . TEXT_INSTALL . '</a>'
            );
        }
        if ($this->pluginManager->isUpgradeAvailable($this->tableObjInfo->unique_key, $this->tableObjInfo->version)) {
            $this->tableDefinition['content'][] = array(
                'align' => 'text-center', 'text' => '<a href="' . zen_href_link
                    (
                        FILENAME_PLUGIN_MANAGER,
                        'page=' . $this->page . '&colKey=' . $this->tableObjInfo->unique_key . '&action=upgrade') . '" class="btn btn-primary" role="button">' . TEXT_UPGRADE_AVAILABLE . '</a>'
            );

        }
        if ($this->tableObjInfo->status == 1) {
            $this->tableDefinition['content'][] = array(
                'align' => 'text-center', 'text' => '<a href="' . zen_href_link
                    (
                        FILENAME_PLUGIN_MANAGER,
                        'page=' . $this->page . '&colKey=' . $this->tableObjInfo->unique_key . '&action=disable') . '" class="btn btn-primary" role="button">' . TEXT_DISABLE . '</a>'
            );
            $this->tableDefinition['content'][] = array(
                'align' => 'text-center', 'text' => '<a href="' . zen_href_link
                    (
                        FILENAME_PLUGIN_MANAGER,
                        'page=' . $this->page . '&colKey=' . $this->tableObjInfo->unique_key . '&action=uninstall') . '" class="btn btn-primary" role="button">' . TEXT_UNINSTALL . '</a>'
            );
        }
        if ($this->tableObjInfo->status == 2) {
            $this->tableDefinition['content'][] = array(
                'align' => 'text-center', 'text' => '<a href="' . zen_href_link
                    (
                        FILENAME_PLUGIN_MANAGER,
                        'page=' . $this->page . '&colKey=' . $this->tableObjInfo->unique_key . '&action=enable') . '" class="btn btn-primary" role="button">' . TEXT_ENABLE . '</a>'
            );
        }
        if ($this->pluginManager->hasPluginVersionsToClean($this->tableObjInfo->unique_key, $this->tableObjInfo->version)) {
            $this->tableDefinition['content'][] = ['text' => '<br>' . TEXT_INFO_CLEANUP];
            $this->tableDefinition['content'][] = array(
                'align' => 'text-center', 'text' => '<a href="' . zen_href_link
                    (
                        FILENAME_PLUGIN_MANAGER,
                        'page=' . $this->page . '&colKey=' . $this->tableObjInfo->unique_key . '&action=cleanup') . '" class="btn btn-primary" role="button">' . TEXT_CLEANUP . '</a>'
            );
        }
    }

    protected function processActionInstall()
    {
        $this->tableDefinition['header'][] = array(
            'text' => '<h4>' . zen_output_string_protected
                (
                    $this->tableObjInfo->name) . '</h4>'
        );

        $this->tableDefinition['content']['form'] = zen_draw_form(
            'plugininstall', FILENAME_PLUGIN_MANAGER, 'page=' . $_GET['page'] . '&colKey=' .
                       $this->tableObjInfo->unique_key . '&action=doInstall', 'post', 'class="form-horizontal"');
        $this->tableDefinition['content'][] = [
            'text' => '<br>' . TEXT_INFO_DESCRIPTION . '<br>' .
                $this->tableObjInfo->description
        ];
        $versions = $this->pluginManager->getPluginVersionsForPlugin($this->tableObjInfo->unique_key);
        $hasMultiple = (count($versions) > 1);
        $firstKey = key($versions);
        if ($hasMultiple) {
            foreach ($versions as $version) {
                $checked = ($version['version'] == $firstKey) ? true : false;
                $this->tableDefinition['content'][] = array(
                    'text' => '<br>' . zen_draw_label
                        (
                            $version['version'], 'version', 'class="control-label"') . zen_draw_radio_field(
                            'version',
                            $version['version'],
                            $checked
                        )
                );
            }
        }
        if (!$hasMultiple) {
            $this->tableDefinition['content'][] = array('text' => zen_draw_hidden_field('version', $firstKey));
        }
        $this->tableDefinition['content'][] = array(
            'align' => 'text-center',
            'text'  => '<br><button type="submit" class="btn btn-primary">'
                . TEXT_INSTALL . '</button> <a href="' . zen_href_link(
                    FILENAME_PLUGIN_MANAGER,
                    'page=' . $this->page . '&colKey=' . $this->tableObjInfo->unique_key) . '" class="btn btn-default" role="button">' . IMAGE_CANCEL . '</a>'
        );
    }

    protected function processActionDoInstall()
    {
        if (!isset($_POST['version'])) {
            zen_redirect(
                zen_href_link(
                    FILENAME_PLUGIN_MANAGER, 'page=' . $this->page . '&colKey=' .
                                           $_GET['colKey']));
        }
        $installer = $this->installerFactory->make($_GET['colKey'], $_POST['version']);
        $installed = $installer->processInstall($_GET['colKey'], $_POST['version']);
        if (!$installed) {
            $this->outputMessageList($installer->errorContainer->getFriendlyErrors(), 'error');
            zen_redirect(
                zen_href_link(
                    FILENAME_PLUGIN_MANAGER, 'page=' . $this->page . '&colKey=' .
                                           $_GET['colKey']));

        }
        $this->messageStack->add_session(TEXT_INSTALL_SUCCESS, 'success');
        zen_redirect(
            zen_href_link(
                FILENAME_PLUGIN_MANAGER, 'page=' . $this->page . '&colKey=' .
                                       $_GET['colKey']));

    }

    protected function processActionUninstall()
    {
        $this->tableDefinition['header'][] = array(
            'text' => '<h4>' . zen_output_string_protected
                (
                    $this->tableObjInfo->name) . '</h4>'
        );
        $this->tableDefinition['content']['form'] = zen_draw_form(
            'pluginuninstall', FILENAME_PLUGIN_MANAGER, 'page=' . $this->page . '&colKey=' .
                       $this->tableObjInfo->unique_key . '&action=doUninstall', 'post', 'class="form-horizontal"') . zen_draw_hidden_field('version', $this->tableObjInfo->version);
        $this->tableDefinition['content'][] = [
            'text' => '<br>' . TEXT_CONFIRM_UNINSTALL . '<br>'
        ];
        $this->tableDefinition['content'][] = array(
            'align' => 'text-center',
            'text'  => '<br><button type="submit" class="btn btn-primary">'
                . TEXT_UNINSTALL . '</button> <a href="' . zen_href_link(
                    FILENAME_PLUGIN_MANAGER,
                    'page=' . $this->page . '&colKey=' . $this->tableObjInfo->unique_key) . '" class="btn btn-default" role="button">' . IMAGE_CANCEL . '</a>'
        );
    }

    protected function processActionDoUninstall()
    {
        if (!isset($_POST['version'])) {
            zen_redirect(
                zen_href_link(
                    FILENAME_PLUGIN_MANAGER, 'page=' . $this->page . '&colKey=' .
                                           $_GET['colKey']));
        }
        $installer = $this->installerFactory->make($_GET['colKey'], $_POST['version']);
        $uninstalled = $installer->processUninstall($_GET['colKey'], $_POST['version']);
        if (!$uninstalled) {
            $this->outputMessageList($installer->errorContainer->getFriendlyErrors(), 'error');
            zen_redirect(
                zen_href_link(
                    FILENAME_PLUGIN_MANAGER, 'page=' . $this->page . '&colKey=' .
                                           $_GET['colKey']));

        }
        $this->messageStack->add_session(TEXT_UNINSTALL_SUCCESS, 'success');
        zen_redirect(
            zen_href_link(
                FILENAME_PLUGIN_MANAGER, 'page=' . $this->page . '&colKey=' .
                                       $_GET['colKey']));

    }

    protected function processActionUpgrade()
    {
        if (!$this->pluginManager->isUpgradeAvailable($this->tableObjInfo->unique_key, $this->tableObjInfo->version)) {
            zen_redirect(
                zen_href_link(
                    FILENAME_PLUGIN_MANAGER, 'page=' . $this->page . '&colKey=' .
                                           $_GET['colKey']));
        }
        $versions = $this->pluginManager->getVersionsForUpgrade($this->tableObjInfo->unique_key, $this->tableObjInfo->version);
        $this->tableDefinition['header'][] = array(
            'text' => '<h4>' . zen_output_string_protected
                (
                    $this->tableObjInfo->name) . '</h4>'
        );
        $this->tableDefinition['content']['form'] = zen_draw_form(
                'pluginupgrade', FILENAME_PLUGIN_MANAGER, 'page=' . $this->page . '&colKey=' .
                                 $this->tableObjInfo->unique_key . '&action=confirmUpgrade', 'post', 'class="form-horizontal"');
        $this->tableDefinition['content'][] = [
            'text' => '<br>' . TEXT_INFO_UPGRADE . '<br>'
        ];
        $firstKey = key($versions);
        foreach ($versions as $version) {
            $checked = ($version == $firstKey) ? true : false;
            $this->tableDefinition['content'][] = array(
                'text' => '<br>' . zen_draw_label
                    (
                        $version, 'version', 'class="control-label"') . zen_draw_radio_field(
                        'version',
                        $version,
                        $checked
                    )
            );
        }
        $this->tableDefinition['content'][] = array(
            'align' => 'text-center',
            'text'  => '<br><button type="submit" class="btn btn-primary">'
                . TEXT_UPGRADE . '</button> <a href="' . zen_href_link(
                    FILENAME_PLUGIN_MANAGER,
                    'page=' . $this->page . '&colKey=' . $this->tableObjInfo->unique_key) . '" class="btn btn-default" role="button">' . IMAGE_CANCEL . '</a>'
        );
    }

    protected function processActionConfirmUpgrade()
    {
        $error = false;
        $versions = $this->pluginManager->getVersionsForUpgrade($this->tableObjInfo->unique_key, $this->tableObjInfo->version);
        if (!$this->pluginManager->isUpgradeAvailable($this->tableObjInfo->unique_key, $this->tableObjInfo->version)) {
            $error = true;
        }
        if ((!isset($_POST['version']))) {
            $error = true;
        }
        if (!in_array($_POST['version'], $versions)) {
            $error = true;
        }
        if ($error) {
            zen_redirect(
                zen_href_link(
                    FILENAME_PLUGIN_MANAGER, 'page=' . $this->page . '&colKey=' .
                                           $_GET['colKey']));

        }
        $this->tableDefinition['header'][] = array(
            'text' => '<h4>' . zen_output_string_protected
                (
                    $this->tableObjInfo->name) . '</h4>'
        );
        $this->tableDefinition['content']['form'] = zen_draw_form(
                'pluginupgrade', FILENAME_PLUGIN_MANAGER, 'page=' . $this->page . '&colKey=' .
                               $this->tableObjInfo->unique_key . '&action=doUpgrade', 'post', 'class="form-horizontal"') . zen_draw_hidden_field('version', $_POST['version']);
        $this->tableDefinition['content'][] = [
            'text' => '<br>' . TEXT_CONFIRM_UPGRADE . '<br>' . sprintf(TEXT_INFO_UPGRADE_CONFIRM, $_POST['version']) . '<br><br>' . TEXT_INFO_UPGRADE_WARNING
        ];
        $this->tableDefinition['content'][] = array(
            'align' => 'text-center',
            'text'  => '<br><button type="submit" class="btn btn-primary">'
                . TEXT_UPGRADE . '</button> <a href="' . zen_href_link(
                    FILENAME_PLUGIN_MANAGER,
                    'page=' . $this->page . '&colKey=' . $this->tableObjInfo->unique_key) . '" class="btn btn-default" role="button">' . IMAGE_CANCEL . '</a>'
        );
    }

    protected function processActionDoUpgrade()
    {
        $error = false;
        $versions = $this->pluginManager->getVersionsForUpgrade($this->tableObjInfo->unique_key, $this->tableObjInfo->version);
        if (!$this->pluginManager->isUpgradeAvailable($this->tableObjInfo->unique_key, $this->tableObjInfo->version)) {
            $error = true;
        }
        if ((!isset($_POST['version']))) {
            $error = true;
        }
        if (!in_array($_POST['version'], $versions)) {
            $error = true;
        }
        if ($error) {
            zen_redirect(
                zen_href_link(
                    FILENAME_PLUGIN_MANAGER, 'page=' . $this->page . '&colKey=' .
                                           $_GET['colKey']));

        }

    }

    protected function processActionCleanUp()
    {
        $versions = $this->pluginManager->getPluginVersionsToClean($this->tableObjInfo->unique_key, $this->tableObjInfo->version);
        $this->tableDefinition['header'][] = array(
            'text' => '<h4>' . zen_output_string_protected
                (
                    $this->tableObjInfo->name) . '</h4>'
        );
        $this->tableDefinition['content']['form'] = zen_draw_form(
            'pluginupgrade', FILENAME_PLUGIN_MANAGER, 'page=' . $this->page . '&colKey=' .
                           $this->tableObjInfo->unique_key . '&action=confirmCleanUp', 'post', 'class="form-horizontal"');
        $this->tableDefinition['content'][] = [
            'text' => '<br>' . TEXT_INFO_SELECT_CLEAN . '<br>'
        ];
        foreach ($versions as $version) {
            $this->tableDefinition['content'][] = array(
                'text' => '<br>' . zen_draw_label
                    (
                        $version['version'], 'version', 'class="control-label"') . zen_draw_checkbox_field(
                        'version[]',
                        $version['version']
                    )
            );
        }
        $this->tableDefinition['content'][] = array(
            'align' => 'text-center',
            'text'  => '<br><button type="submit" class="btn btn-primary">'
                . TEXT_CONFIRM . '</button> <a href="' . zen_href_link(
                    FILENAME_PLUGIN_MANAGER,
                    'page=' . $this->page . '&colKey=' . $this->tableObjInfo->unique_key) . '" class="btn btn-default" role="button">' . IMAGE_CANCEL . '</a>'
        );
    }

    protected function processActionConfirmCleanUp()
    {
        if (!isset($_POST['version']) || !is_array($_POST['version'])) {
            zen_redirect(
                zen_href_link(
                    FILENAME_PLUGIN_MANAGER, 'page=' . $this->page . '&colKey=' . $_GET['colKey'] . '&action=cleanup'
                )
            );
        }
        $this->tableDefinition['header'][] = array(
            'text' => '<h4>' . zen_output_string_protected
                (
                    $this->tableObjInfo->name) . '</h4>'
        );
        $this->tableDefinition['content']['form'] = zen_draw_form(
            'pluginupgrade', FILENAME_PLUGIN_MANAGER, 'page=' . $this->page . '&colKey=' .
                           $this->tableObjInfo->unique_key . '&action=doCleanUp', 'post', 'class="form-horizontal"');
        $this->tableDefinition['content'][] = [
            'text' => '<br>' . TEXT_INFO_CONFIRM_CLEAN . '<br>'
        ];
        foreach ($_POST['version'] as $version) {
            $this->tableDefinition['content'][] = array(
                'text' => '<br>' . $version . zen_draw_hidden_field('version[]', $version)
            );
        }
        $this->tableDefinition['content'][] = array(
            'align' => 'text-center',
            'text'  => '<br><button type="submit" class="btn btn-primary">'
                . TEXT_CONFIRM . '</button> <a href="' . zen_href_link(
                    FILENAME_PLUGIN_MANAGER,
                    'page=' . $this->page . '&colKey=' . $this->tableObjInfo->unique_key) . '" class="btn btn-default" role="button">' . IMAGE_CANCEL . '</a>'
        );

    }

    protected function processActionDoCleanup()
    {

    }

    protected function processActionEnable()
    {
        $this->tableDefinition['header'][] = array(
            'text' => '<h4>' . zen_output_string_protected
                (
                    $this->tableObjInfo->name) . '</h4>'
        );
        $this->tableDefinition['content']['form'] = zen_draw_form(
                'pluginuninstall', FILENAME_PLUGIN_MANAGER, 'page=' . $this->page . '&colKey=' .
                                 $this->tableObjInfo->unique_key . '&action=doEnable', 'post', 'class="form-horizontal"') . zen_draw_hidden_field('version', $this->tableObjInfo->version);
        $this->tableDefinition['content'][] = [
            'text' => '<br>' . TEXT_CONFIRM_ENABLE . '<br>'
        ];
        $this->tableDefinition['content'][] = array(
            'align' => 'text-center',
            'text'  => '<br><button type="submit" class="btn btn-primary">'
                . TEXT_ENABLE . '</button> <a href="' . zen_href_link(
                    FILENAME_PLUGIN_MANAGER,
                    'page=' . $this->page . '&colKey=' . $this->tableObjInfo->unique_key) . '" class="btn btn-default" role="button">' . IMAGE_CANCEL . '</a>'
        );
    }

    protected function processActionDoEnable()
    {
        if (!isset($_POST['version'])) {
            zen_redirect(
                zen_href_link(
                    FILENAME_PLUGIN_MANAGER, 'page=' . $this->page . '&colKey=' .
                                           $_GET['colKey']));
        }
        $installer = $this->installerFactory->make($_GET['colKey'], $_POST['version']);
        $installer->processEnable($_GET['colKey'], $_POST['version']);
        $this->messageStack->add_session(TEXT_ENABLE_SUCCESS, 'success');
        zen_redirect(
            zen_href_link(
                FILENAME_PLUGIN_MANAGER, 'page=' . $this->page . '&colKey=' .
                                       $_GET['colKey']));
    }

    protected function processActionDisable()
    {
        $this->tableDefinition['header'][] = array(
            'text' => '<h4>' . zen_output_string_protected
                (
                    $this->tableObjInfo->name) . '</h4>'
        );
        $this->tableDefinition['content']['form'] = zen_draw_form(
                'pluginuninstall', FILENAME_PLUGIN_MANAGER, 'page=' . $this->page . '&colKey=' .
                                 $this->tableObjInfo->unique_key . '&action=doDisable', 'post', 'class="form-horizontal"') . zen_draw_hidden_field('version', $this->tableObjInfo->version);
        $this->tableDefinition['content'][] = [
            'text' => '<br>' . TEXT_CONFIRM_DISABLE . '<br>'
        ];
        $this->tableDefinition['content'][] = array(
            'align' => 'text-center',
            'text'  => '<br><button type="submit" class="btn btn-primary">'
                . TEXT_DISABLE . '</button> <a href="' . zen_href_link(
                    FILENAME_PLUGIN_MANAGER,
                    'page=' . $this->page . '&colKey=' . $this->tableObjInfo->unique_key) . '" class="btn btn-default" role="button">' . IMAGE_CANCEL . '</a>'
        );
    }

    protected function processActionDoDisable()
    {
        if (!isset($_POST['version'])) {
            zen_redirect(
                zen_href_link(
                    FILENAME_PLUGIN_MANAGER, 'page=' . $this->page . '&colKey=' .
                                           $_GET['colKey']));
        }
        $installer = $this->installerFactory->make($_GET['colKey'], $_POST['version']);
        $installer->processDisable($_GET['colKey'], $_POST['version']);
        $this->messageStack->add_session(TEXT_DISABLE_SUCCESS, 'success');
        zen_redirect(
            zen_href_link(
                FILENAME_PLUGIN_MANAGER, 'page=' . $this->page . '&colKey=' .
                                       $_GET['colKey']));

    }
    protected function getPluginFileSize($listResult, $colName, $columnInfo)
    {
        $filePath = DIR_FS_CATALOG . 'zc_plugins/' . $listResult['unique_key'] . '/';
        $fs = FileSystem::getInstance();
        $dirSize = $fs->getDirectorySize($filePath);
        return $dirSize;
    }

}