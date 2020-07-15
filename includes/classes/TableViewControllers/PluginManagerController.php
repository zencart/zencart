<?php

namespace Zencart\TableViewControllers;

use Symfony\Component\EventDispatcher\LegacyEventProxy;
use Zencart\FileSystem\FileSystem;

class PluginManagerController extends BaseController
{
    public function init($pluginManager, $installerFactory)
    {
        $this->pluginManager = $pluginManager;
        $this->installerFactory = $installerFactory;
        return $this;
    }

    protected function buildInitialQuery()
    {
        return $this->pluginManager->getPluginControl();
    }

    protected function processDefaultAction()
    {
        if (!isset($this->currentRow)) {
            return;
        }
        $this->setBoxHeader('<h4>' . zen_output_string_protected($this->currentRow->name) . '</h4>');
        if ($this->currentRow->status == 1) {
            $this->setBoxContent('<br>' . sprintf(TEXT_VERSION_INSTALLED, $this->currentRow->version) . '<br>');
        }
        $this->setBoxContent('<br>' . TEXT_INFO_DESCRIPTION . '<br>' . $this->currentRow->description);
        if ($this->currentRow->status == 0) {
            $this->setBoxContent(
                '<a href="' . zen_href_link(
                    FILENAME_PLUGIN_MANAGER,
                    $this->pageLink() . '&' . $this->colKeylink() . '&action=install'
                ) . '" class="btn btn-primary" role="button">' . TEXT_INSTALL . '</a>'
            );
        }
        if ($this->pluginManager->isUpgradeAvailable($this->currentRow->unique_key, $this->currentRow->version)) {
            $this->setBoxContent(
                '<a href="' . zen_href_link(
                    FILENAME_PLUGIN_MANAGER,
                    $this->pageLink() . '&' . $this->colKeylink() . '&action=upgrade'
                ) . '" class="btn btn-primary" role="button">' . TEXT_UPGRADE_AVAILABLE . '</a>'
            );
        }
        if ($this->currentRow->status == 1) {
            $this->setBoxContent(
                '<a href="' . zen_href_link(
                    FILENAME_PLUGIN_MANAGER,
                    $this->pageLink() . '&' . $this->colKeylink() . '&action=disable'
                ) . '" class="btn btn-primary" role="button">' . TEXT_DISABLE . '</a>'
            );
            $this->setBoxContent(
                '<a href="' . zen_href_link(
                    FILENAME_PLUGIN_MANAGER,
                    $this->pageLink() . '&' . $this->colKeylink() . '&action=uninstall'
                ) . '" class="btn btn-primary" role="button">' . TEXT_UNINSTALL . '</a>'
            );
        }
        if ($this->currentRow->status == 2) {
            $this->setBoxContent(
                '<a href="' . zen_href_link(
                    FILENAME_PLUGIN_MANAGER,
                    $this->pageLink() . '&' . $this->colKeylink() . '&action=enable'
                ) . '" class="btn btn-primary" role="button">' . TEXT_ENABLE . '</a>'
            );
            $this->setBoxContent(
                '<a href="' . zen_href_link(
                    FILENAME_PLUGIN_MANAGER,
                    $this->pageLink() . '&' . $this->colKeylink() . '&action=uninstall'
                ) . '" class="btn btn-primary" role="button">' . TEXT_UNINSTALL . '</a>'
            );
        }
        if ($this->pluginManager->hasPluginVersionsToClean($this->currentRow->unique_key, $this->currentRow->version)) {
            $this->setBoxContent('<br>' . TEXT_INFO_CLEANUP);
            $this->setBoxContent(
                '<a href="' . zen_href_link(
                    FILENAME_PLUGIN_MANAGER,
                    $this->pageLink() . '&' . $this->colKeylink() . '&action=cleanup'
                ) . '" class="btn btn-primary" role="button">' . TEXT_CLEANUP . '</a>'
            );
        }
    }

    protected function processActionInstall()
    {
        $this->setBoxHeader('<h4>' . zen_output_string_protected($this->currentRow->name) . '</h4>');
        $this->setBoxForm(
            zen_draw_form('plugininstall', FILENAME_PLUGIN_MANAGER, $this->pageLink() . '&' . $this->colKeylink() . '&action=doInstall', 'post', 'class="form-horizontal"')
        );
        $this->setBoxContent('<br>' . TEXT_INFO_DESCRIPTION . '<br>' . $this->currentRow->description);
        $versions = $this->pluginManager->getPluginVersionsForPlugin($this->currentRow->unique_key);
        $hasMultiple = (count($versions) > 1);
        $firstKey = key($versions);
        if ($hasMultiple) {
            foreach ($versions as $version) {
                $checked = ($version['version'] == $firstKey) ? true : false;
                $this->setBoxContent('<br>' . zen_draw_label($version['version'], 'version', 'class="control-label"') . zen_draw_radio_field('version', $version['version'], $checked));
            }
        }
        if (!$hasMultiple) {
            $this->setBoxContent(zen_draw_hidden_field('version', $firstKey));
        }
        $this->setBoxContent(
            '<br><button type="submit" class="btn btn-primary">'
            . TEXT_INSTALL . '</button> <a href="' . zen_href_link(
                FILENAME_PLUGIN_MANAGER,
                $this->pageLink() . '&' . $this->colKeylink()
            ) . '" class="btn btn-default" role="button">' . IMAGE_CANCEL . '</a>'
        );
    }

    protected function processActionDoInstall()
    {
        if (!$this->request->has('version')) {
            zen_redirect(
                zen_href_link(
                    FILENAME_PLUGIN_MANAGER,
                    $this->pageLink() . '&' . $this->colKeylink()
                )
            );
        }
        $installer = $this->installerFactory->make($this->currentRow->unique_key, $this->request->input('version'));
        $installed = $installer->processInstall($this->currentRow->unique_key, $this->request->input('version'));
        if (!$installed) {
            $this->outputMessageList($installer->errorContainer->getFriendlyErrors(), 'error');
            zen_redirect(
                zen_href_link(
                    FILENAME_PLUGIN_MANAGER,
                    $this->pageLink() . '&' . $this->colKeylink()
                )
            );
        }
        $this->messageStack->add_session(TEXT_INSTALL_SUCCESS, 'success');
        zen_redirect(
            zen_href_link(
                FILENAME_PLUGIN_MANAGER,
                $this->pageLink() . '&' . $this->colKeylink()
            )
        );
    }

    protected function processActionUninstall()
    {
        $this->setBoxHeader('<h4>' . zen_output_string_protected($this->currentRow->name) . '</h4>');
        $this->setBoxForm(
            zen_draw_form(
                'pluginuninstall',
                FILENAME_PLUGIN_MANAGER,
                $this->pageLink() . '&' . $this->colKeylink() . '&action=doUninstall',
                'post',
                'class="form-horizontal"'
            ) . zen_draw_hidden_field('version', $this->currentRow->version)
        );
        $this->setBoxContent('<br>' . TEXT_CONFIRM_UNINSTALL . '<br>');
        $this->setBoxContent(
            '<br><button type="submit" class="btn btn-primary">'
            . TEXT_UNINSTALL . '</button> <a href="' . zen_href_link(
                FILENAME_PLUGIN_MANAGER,
                $this->pageLink() . '&' . $this->colKeylink()
            ) . '" class="btn btn-default" role="button">' . IMAGE_CANCEL . '</a>'
        );
    }

    protected function processActionDoUninstall()
    {
        if (!$this->request->has('version')) {
            zen_redirect(
                zen_href_link(
                    FILENAME_PLUGIN_MANAGER,
                    $this->pageLink() . '&' . $this->colKeylink()
                )
            );
        }
        $installer = $this->installerFactory->make($this->currentRow->unique_key, $this->request->input('version'));
        $uninstalled = $installer->processUninstall($this->currentRow->unique_key, $this->request->input('version'));
        if (!$uninstalled) {
            $this->outputMessageList($installer->errorContainer->getFriendlyErrors(), 'error');
            zen_redirect(
                zen_href_link(
                    FILENAME_PLUGIN_MANAGER,
                    $this->pageLink() . '&' . $this->colKeylink()
                )
            );
        }
        $this->messageStack->add_session(TEXT_UNINSTALL_SUCCESS, 'success');
        zen_redirect(
            zen_href_link(
                FILENAME_PLUGIN_MANAGER,
                $this->pageLink() . '&' . $this->colKeylink()
            )
        );
    }

    protected function processActionUpgrade()
    {
        if (!$this->pluginManager->isUpgradeAvailable($this->currentRow->unique_key, $this->currentRow->version)) {
            zen_redirect(
                zen_href_link(
                    FILENAME_PLUGIN_MANAGER,
                    $this->pageLink() . '&' . $this->colKeylink()
                )
            );
        }
        $versions = $this->pluginManager->getVersionsForUpgrade($this->currentRow->unique_key, $this->currentRow->version);
        $this->setBoxHeader('<h4>' . zen_output_string_protected($this->currentRow->name) . '</h4>');
        $this->setBoxForm(zen_draw_form('pluginupgrade', FILENAME_PLUGIN_MANAGER, $this->pageLink() . '&' . $this->colKeylink() . '&action=confirmUpgrade', 'post', 'class="form-horizontal"'));
        $this->setBoxContent('<br>' . TEXT_INFO_UPGRADE . '<br>');
        $firstKey = key($versions);
        foreach ($versions as $version) {
            $checked = ($version == $firstKey);
            $this->tableDefinition['content'][] = array(
                'text' => '<br>' . zen_draw_label
                    (
                        $version,
                        'version',
                        'class="control-label"'
                    ) . zen_draw_radio_field(
                        'version',
                        $version,
                        $checked
                    )
            );
        }
        $this->setBoxContent(
            '<br><button type="submit" class="btn btn-primary">'
            . TEXT_UPGRADE . '</button> <a href="' . zen_href_link(
                FILENAME_PLUGIN_MANAGER,
                $this->pageLink() . '&' . $this->colKeylink()
            ) . '" class="btn btn-default" role="button">' . IMAGE_CANCEL . '</a>'
        );
    }

    protected function processActionConfirmUpgrade()
    {
        $error = false;
        $versions = $this->pluginManager->getVersionsForUpgrade($this->currentRow->unique_key, $this->currentRow->version);
        if (!$this->pluginManager->isUpgradeAvailable($this->currentRow->unique_key, $this->currentRow->version)) {
            $error = true;
        }
        if ((!$this->request->has('version'))) {
            $error = true;
        }
        if (!in_array($this->request->input('version'), $versions)) {
            $error = true;
        }
        if ($error) {
            zen_redirect(
                zen_href_link(
                    FILENAME_PLUGIN_MANAGER,
                    $this->pageLink() . '&' . $this->colKeylink()
                )
            );
        }
        $this->setBoxHeader('<h4>' . zen_output_string_protected($this->currentRow->name) . '</h4>');
        $this->setBoxForm(zen_draw_form(
                'pluginupgrade',
                FILENAME_PLUGIN_MANAGER,
                $this->pageLink() . '&' . $this->colKeylink() . '&action=doUpgrade',
                'post',
                'class="form-horizontal"'
            ) . zen_draw_hidden_field('version', $this->request->input('version')));
        $this->setBoxContent(
            '<br>' . TEXT_CONFIRM_UPGRADE . '<br>' . sprintf(TEXT_INFO_UPGRADE_CONFIRM, $this->request->input('version')) . '<br><br>' . TEXT_INFO_UPGRADE_WARNING
        );
        $this->setBoxContent(
            '<br><button type="submit" class="btn btn-primary">'
            . TEXT_UPGRADE . '</button> <a href="' . zen_href_link(
                FILENAME_PLUGIN_MANAGER,
                $this->pageLink() . '&' . $this->colKeylink()
            ) . '" class="btn btn-default" role="button">' . IMAGE_CANCEL . '</a>'
        );
    }

    protected function processActionDoUpgrade()
    {
        $error = false;
        $versions = $this->pluginManager->getVersionsForUpgrade($this->currentRow->unique_key, $this->currentRow->version);
        if (!$this->pluginManager->isUpgradeAvailable($this->currentRow->unique_key, $this->currentRow->version)) {
            $error = true;
        }
        if ((!$this->request->has('version'))) {
            $error = true;
        }
        if (!in_array($this->request->input('version'), $versions)) {
            $error = true;
        }
        if ($error) {
            zen_redirect(
                zen_href_link(
                    FILENAME_PLUGIN_MANAGER,
                    $this->pageLink() . '&' . $this->colKeylink()
                )
            );
        }
        $installer = $this->installerFactory->make($this->currentRow->unique_key, $this->request->input('version'));
        $upgraded = $installer->processUpgrade($this->currentRow->unique_key, $this->request->input('version'), $this->currentRow->version);
        if (!$upgraded) {
            $this->outputMessageList($installer->errorContainer->getFriendlyErrors(), 'error');
            zen_redirect(
                zen_href_link(
                    FILENAME_PLUGIN_MANAGER,
                    $this->pageLink() . '&' . $this->colKeylink()
                )
            );
        }
        $this->messageStack->add_session(TEXT_UPGRADE_SUCCESS, 'success');
        zen_redirect(
            zen_href_link(
                FILENAME_PLUGIN_MANAGER,
                $this->pageLink() . '&' . $this->colKeylink()
            )
        );
    }

    protected function processActionCleanUp()
    {
        $versions = $this->pluginManager->getPluginVersionsToClean($this->currentRow->unique_key, $this->currentRow->version);
        $this->setBoxHeader(
            '<h4>' . zen_output_string_protected
            (
                $this->currentRow->name
            ) . '</h4>'
        );
        $this->setBoxForm(
            zen_draw_form(
                'pluginupgrade',
                FILENAME_PLUGIN_MANAGER,
                $this->pageLink() . '&' . $this->colKeylink() . '&action=confirmCleanUp',
                'post',
                'class="form-horizontal"'
            )
        );
        $this->setBoxContent('<br>' . TEXT_INFO_SELECT_CLEAN . '<br>');
        foreach ($versions as $version) {
            $this->tableDefinition['content'][] = array(
                'text' => '<br>' . zen_draw_label
                    (
                        $version['version'],
                        'version',
                        'class="control-label"'
                    ) . zen_draw_checkbox_field(
                        'version[]',
                        $version['version']
                    )
            );
        }
        $this->setBoxContent(
            '<br><button type="submit" class="btn btn-primary">'
            . TEXT_CONFIRM . '</button> <a href="' . zen_href_link(
                FILENAME_PLUGIN_MANAGER,
                $this->pageLink() . '&' . $this->colKeylink()
            ) . '" class="btn btn-default" role="button">' . IMAGE_CANCEL . '</a>'
        );
    }

    protected function processActionConfirmCleanUp()
    {
        if (!$this->request->has('version') || !is_array($this->request->input('version'))) {
            zen_redirect(
                zen_href_link(
                    FILENAME_PLUGIN_MANAGER,
                    $this->pageLink() . '&' . $this->colKeylink() . '&action=cleanup'
                )
            );
        }
        $this->setBoxHeader('<h4>' . zen_output_string_protected($this->currentRow->name) . '</h4>');
        $this->setBoxForm(zen_draw_form(
            'pluginupgrade',
            FILENAME_PLUGIN_MANAGER,
            $this->pageLink() . '&' . $this->colKeylink() . '&action=doCleanUp',
            'post',
            'class="form-horizontal"'
        ));
        $this->setBoxContent('<br>' . TEXT_INFO_CONFIRM_CLEAN . '<br>');
        foreach ($this->request->input('version') as $version) {
            $this->setBoxContent('<br>' . $version . zen_draw_hidden_field('version[]', $version));
        }
        $this->setBoxContent(
            '<br><button type="submit" class="btn btn-primary">'
            . TEXT_CONFIRM . '</button> <a href="' . zen_href_link(
                FILENAME_PLUGIN_MANAGER,
                $this->pageLink() . '&' . $this->colKeylink()
            ) . '" class="btn btn-default" role="button">' . IMAGE_CANCEL . '</a>'
        );
    }

    protected function processActionDoCleanup()
    {
        if (!$this->request->has('version') || !is_array($this->request->input('version'))) {
            zen_redirect(
                zen_href_link(
                    FILENAME_PLUGIN_MANAGER,
                    $this->pageLink() . '&' . $this->colKeylink() . '&action=cleanup'
                )
            );
        }
        foreach ($this->request->input('version') as $version) {
            $path = DIR_FS_CATALOG . 'zc_plugins/' . $this->currentRow->unique_key . '/' . $version;
            (new FileSystem)->deleteDirectory($path);
        }
        $this->messageStack->add_session(TEXT_CLEANUP_SUCCESS, 'success');
        zen_redirect(zen_href_link(FILENAME_PLUGIN_MANAGER, $this->pageLink()));
    }

    protected function processActionEnable()
    {
        $this->setBoxHeader('<h4>' . zen_output_string_protected($this->currentRow->name) . '</h4>');
        $this->setBoxForm(zen_draw_form(
                'pluginuninstall',
                FILENAME_PLUGIN_MANAGER,
                $this->pageLink() . '&' . $this->colKeylink() . '&action=doEnable',
                'post',
                'class="form-horizontal"'
            ) . zen_draw_hidden_field('version', $this->currentRow->version));
        $this->setBoxContent('<br>' . TEXT_CONFIRM_ENABLE . '<br>');
        $this->setBoxContent(
            '<br><button type="submit" class="btn btn-primary">'
            . TEXT_ENABLE . '</button> <a href="' . zen_href_link(
                FILENAME_PLUGIN_MANAGER,
                $this->pageLink() . '&' . $this->colKeylink()
            ) . '" class="btn btn-default" role="button">' . IMAGE_CANCEL . '</a>'
        );
    }

    protected function processActionDoEnable()
    {
        if (!$this->request->has('version')) {
            zen_redirect(
                zen_href_link(
                    FILENAME_PLUGIN_MANAGER,
                    $this->pageLink() . '&' . $this->colKeylink()
                )
            );
        }
        $installer = $this->installerFactory->make($this->currentRow->unique_key, $this->request->input('version'));
        $installer->processEnable($this->currentRow->unique_key, $this->request->input('version'));
        $this->messageStack->add_session(TEXT_ENABLE_SUCCESS, 'success');
        zen_redirect(
            zen_href_link(
                FILENAME_PLUGIN_MANAGER,
                $this->pageLink() . '&' . $this->colKeylink()
            )
        );
    }

    protected function processActionDisable()
    {
        $this->setBoxHeader(
            '<h4>' . zen_output_string_protected
            (
                $this->currentRow->name
            ) . '</h4>'
        );
        $this->setBoxForm(zen_draw_form(
                'pluginuninstall',
                FILENAME_PLUGIN_MANAGER,
                $this->pageLink() . '&' . $this->colKeylink() . '&action=doDisable',
                'post',
                'class="form-horizontal"'
            ) . zen_draw_hidden_field('version', $this->currentRow->version));
        $this->setBoxContent('<br>' . TEXT_CONFIRM_DISABLE . '<br>');
        $this->setBoxContent(
            '<br><button type="submit" class="btn btn-primary">'
            . TEXT_DISABLE . '</button> <a href="' . zen_href_link(
                FILENAME_PLUGIN_MANAGER,
                $this->pageLink() . '&' . $this->colKeylink()
            ) . '" class="btn btn-default" role="button">' . IMAGE_CANCEL . '</a>'
        );
    }

    protected function processActionDoDisable()
    {
        if (!$this->request->has('version')) {
            zen_redirect(zen_href_link(FILENAME_PLUGIN_MANAGER, $this->pageLink() . '&' . $this->colKeylink()));
        }
        $installer = $this->installerFactory->make($this->currentRow->unique_key, $this->request->input('version'));
        $installer->processDisable($this->currentRow->unique_key, $this->request->input('version'));
        $this->messageStack->add_session(TEXT_DISABLE_SUCCESS, 'success');
        zen_redirect(zen_href_link(FILENAME_PLUGIN_MANAGER, $this->pageLink() . '&' . $this->colKeylink()));
    }

    protected function getPluginFileSize($listResult, $colName, $columnInfo)
    {
        $filePath = DIR_FS_CATALOG . 'zc_plugins/' . $listResult['unique_key'] . '/';
        $fs = new FileSystem;
        $dirSize = $fs->getDirectorySize($filePath);
        return $dirSize;
    }

}
