<?php declare(strict_types=1);
/**
 * @copyright Copyright 2003-2025 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: DrByte 2025 Sep 29 Modified in v2.2.0 $
 */

namespace Zencart\ViewBuilders;

use Zencart\FileSystem\FileSystem;
use Zencart\PluginManager\PluginManager;
use Zencart\PluginSupport\InstallerFactory;
use Zencart\PluginSupport\PluginStatus;

/**
 * @since ZC v1.5.8
 */
class PluginManagerController extends BaseController
{

    protected PluginManager $pluginManager;
    protected InstallerFactory $installerFactory;

    /**
     * @since ZC v1.5.8
     */
    public function init(PluginManager $pluginManager, InstallerFactory $installerFactory)
    {
        $this->pluginManager = $pluginManager;
        $this->installerFactory = $installerFactory;
    }

    /**
     * @since ZC v1.5.8
     */
    protected function processDefaultAction()
    {
        $this->setBoxHeader('<h4>' . zen_lookup_admin_menu_language_override('plugin_name', $this->currentFieldValue('unique_key'), $this->currentFieldValue('name')) . '</h4>');
        if ($this->currentFieldValue('status') == 1) {
            $this->setBoxContent('<br>' . sprintf(TEXT_VERSION_INSTALLED, $this->currentFieldValue('version')) . '<br>');
        }
        $this->setBoxContent('<br>' . TEXT_INFO_DESCRIPTION . '<br>' . zen_lookup_admin_menu_language_override('plugin_description', $this->currentFieldValue('unique_key'), $this->currentFieldValue('description')));

        if (!empty($this->currentFieldValue('author'))) {
            $this->setBoxContent(
                sprintf(TEXT_PLUGIN_AUTHOR, $this->currentFieldValue('author'))
            );
        }

        if ((int)$this->currentFieldValue('status') === PluginStatus::NOT_INSTALLED) {
            $this->setBoxContent(
                '<a href="' . zen_href_link(
                    FILENAME_PLUGIN_MANAGER,
                    $this->pageLink() . '&' . $this->colKeylink() . '&action=install'
                ) . '" class="btn btn-primary" role="button">' . TEXT_INSTALL . '</a>'
            );
        }

        if ($available = $this->pluginManager->isNewDownloadAvailable($this->currentFieldValue('zc_contrib_id'), $this->currentFieldValue('version'))) {
            $this->setBoxContent(
                sprintf(TEXT_NEW_PLUGIN_DOWNLOAD_AVAILABLE, $available['latest_plugin_version'], $available['id'])
            );
        } elseif (!empty($this->currentFieldValue('zc_contrib_id'))) {
            $this->setBoxContent(
                sprintf(TEXT_PLUGIN_DOWNLOAD_PAGE, $this->currentFieldValue('zc_contrib_id'))
            );
        }

        if ($this->pluginManager->isUpgradeAvailable($this->currentFieldValue('unique_key'), $this->currentFieldValue('version'))) {
            $this->setBoxContent(
                '<a href="' . zen_href_link(
                    FILENAME_PLUGIN_MANAGER,
                    $this->pageLink() . '&' . $this->colKeylink() . '&action=upgrade'
                ) . '" class="btn btn-primary" role="button">' . TEXT_UPGRADE_AVAILABLE . '</a>'
            );
        }
        if ((int)$this->currentFieldValue('status') === PluginStatus::ENABLED) {
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
        } elseif ((int)$this->currentFieldValue('status') === PluginStatus::DISABLED) {
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
        if ($this->pluginManager->hasPluginVersionsToClean($this->currentFieldValue('unique_key'), $this->currentFieldValue('version')) && !empty($this->currentFieldValue('version'))) {
            $this->setBoxContent('<br>' . TEXT_INFO_CLEANUP);
            $this->setBoxContent(
                '<a href="' . zen_href_link(
                    FILENAME_PLUGIN_MANAGER,
                    $this->pageLink() . '&' . $this->colKeylink() . '&action=cleanup'
                ) . '" class="btn btn-primary" role="button">' . TEXT_CLEANUP . '</a>'
            );
        }
    }

    /**
     * @since ZC v1.5.8
     */
    protected function processActionInstall()
    {
        $this->setBoxHeader('<h4>' . zen_lookup_admin_menu_language_override('plugin_name', $this->currentFieldValue('unique_key'), $this->currentFieldValue('name')) . '</h4>');
        $this->setBoxForm(
            zen_draw_form('plugininstall', FILENAME_PLUGIN_MANAGER, $this->pageLink() . '&' . $this->colKeylink() . '&action=doInstall', 'post', 'class="form-horizontal"')
        );
        $this->setBoxContent('<br>' . TEXT_INFO_DESCRIPTION . '<br>' . zen_lookup_admin_menu_language_override('plugin_description', $this->currentFieldValue('unique_key'), $this->currentFieldValue('description')));
        $versions = $this->pluginManager->getPluginVersionsForPlugin($this->currentFieldValue('unique_key'));
        $hasMultiple = (count($versions) > 1);
        $firstKey = key($versions);
        if ($hasMultiple) {
            foreach ($versions as $version) {
                $checked = ($version['version'] == $firstKey);
                $this->setBoxContent('<br><label class="radio-inline">' . zen_draw_radio_field('version', $version['version'], $checked) . $version['version']);
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

    /**
     * @since ZC v1.5.8
     */
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
        $installer = $this->installerFactory->make($this->currentFieldValue('unique_key'), $this->request->input('version'));
        $installed = $installer->processInstall($this->currentFieldValue('unique_key'), $this->request->input('version'));
        if (!$installed) {
            $this->outputMessageList($installer->getErrorContainer()->getFriendlyErrors(), 'error');
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

    /**
     * @since ZC v1.5.8
     */
    protected function processActionUninstall()
    {
        $this->setBoxHeader('<h4>' . zen_lookup_admin_menu_language_override('plugin_name', $this->currentFieldValue('unique_key'), $this->currentFieldValue('name')) . '</h4>');
        $this->setBoxForm(
            zen_draw_form(
                'pluginuninstall',
                FILENAME_PLUGIN_MANAGER,
                $this->pageLink() . '&' . $this->colKeylink() . '&action=doUninstall',
                'post',
                'class="form-horizontal"'
            ) . zen_draw_hidden_field('version', $this->currentFieldValue('version'))
        );
        $this->setBoxContent('<br>' . TEXT_CONFIRM_UNINSTALL . '<br>');
        $this->setBoxContent(
            '<br><button type="submit" class="btn btn-danger">'
            . TEXT_UNINSTALL . '</button> <a href="' . zen_href_link(
                FILENAME_PLUGIN_MANAGER,
                $this->pageLink() . '&' . $this->colKeylink()
            ) . '" class="btn btn-default" role="button">' . IMAGE_CANCEL . '</a>'
        );
    }

    /**
     * @since ZC v1.5.8
     */
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
        $installer = $this->installerFactory->make($this->currentFieldValue('unique_key'), $this->request->input('version'));
        $uninstalled = $installer->processUninstall($this->currentFieldValue('unique_key'), $this->request->input('version'));
        if (!$uninstalled) {
            $this->outputMessageList($installer->getErrorContainer()->getFriendlyErrors(), 'error');
            zen_redirect(
                zen_href_link(
                    FILENAME_PLUGIN_MANAGER,
                    $this->pageLink() . '&' . $this->colKeylink()
                )
            );
        }
        $this->notify('NOTIFY_PLUGINMANAGER_DO_UNINSTALL', ['plugin_key' => $this->currentFieldValue('unique_key'), 'version' => $this->request->input('version')]);

        $this->messageStack->add_session(TEXT_UNINSTALL_SUCCESS, 'success');
        zen_redirect(
            zen_href_link(
                FILENAME_PLUGIN_MANAGER,
                $this->pageLink() . '&' . $this->colKeylink()
            )
        );
    }

    /**
     * @since ZC v1.5.8
     */
    protected function processActionUpgrade()
    {
        if (!$this->pluginManager->isUpgradeAvailable($this->currentFieldValue('unique_key'), $this->currentFieldValue('version'))) {
            zen_redirect(
                zen_href_link(
                    FILENAME_PLUGIN_MANAGER,
                    $this->pageLink() . '&' . $this->colKeylink()
                )
            );
        }
        $versions = $this->pluginManager->getVersionsForUpgrade($this->currentFieldValue('unique_key'), $this->currentFieldValue('version'));
        $this->setBoxHeader('<h4>' . zen_lookup_admin_menu_language_override('plugin_name', $this->currentFieldValue('unique_key'), $this->currentFieldValue('name')) . '</h4>');
        $this->setBoxForm(zen_draw_form('pluginupgrade', FILENAME_PLUGIN_MANAGER, $this->pageLink() . '&' . $this->colKeylink() . '&action=confirmUpgrade', 'post', 'class="form-horizontal"'));
        $this->setBoxContent('<br>' . TEXT_INFO_UPGRADE . '<br>');
        $firstKey = key($versions);
        foreach ($versions as $version) {
            $checked = ($version == $firstKey);
            $this->setBoxContent('<br><label class="radio-inline">' . zen_draw_radio_field('version', $version, $checked) . $version);
        }
        $this->setBoxContent(
            '<br><button type="submit" class="btn btn-primary">'
            . TEXT_UPGRADE . '</button> <a href="' . zen_href_link(
                FILENAME_PLUGIN_MANAGER,
                $this->pageLink() . '&' . $this->colKeylink()
            ) . '" class="btn btn-default" role="button">' . IMAGE_CANCEL . '</a>'
        );
    }

    /**
     * @since ZC v1.5.8
     */
    protected function processActionConfirmUpgrade()
    {
        $error = false;
        $versions = $this->pluginManager->getVersionsForUpgrade($this->currentFieldValue('unique_key'), $this->currentFieldValue('version'));
        if (!$this->pluginManager->isUpgradeAvailable($this->currentFieldValue('unique_key'), $this->currentFieldValue('version'))) {
            $error = true;
        }
        if (!$this->request->has('version')) {
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
        $this->setBoxHeader('<h4>' . zen_lookup_admin_menu_language_override('plugin_name', $this->currentFieldValue('unique_key'), $this->currentFieldValue('name')) . '</h4>');
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

    /**
     * @since ZC v1.5.8
     */
    protected function processActionDoUpgrade()
    {
        $error = false;
        $versions = $this->pluginManager->getVersionsForUpgrade($this->currentFieldValue('unique_key'), $this->currentFieldValue('version'));
        if (!$this->pluginManager->isUpgradeAvailable($this->currentFieldValue('unique_key'), $this->currentFieldValue('version'))) {
            $error = true;
        }
        if ((!$this->request->has('version'))) {
            $error = true;
        }
        if (!in_array($this->request->input('version'), $versions)) {
            $error = true;
        }
        if ($error) {
            zen_redirect(zen_href_link(FILENAME_PLUGIN_MANAGER, $this->pageLink() . '&' . $this->colKeylink()));
        }
        $installer = $this->installerFactory->make($this->currentFieldValue('unique_key'), $this->request->input('version'));
        $upgraded = $installer->processUpgrade($this->currentFieldValue('unique_key'), $this->request->input('version'), $this->currentFieldValue('version'));
        if (!$upgraded) {
            $this->outputMessageList($installer->getErrorContainer()->getFriendlyErrors(), 'error');
            zen_redirect(
                zen_href_link(
                    FILENAME_PLUGIN_MANAGER,
                    $this->pageLink() . '&' . $this->colKeylink()
                )
            );
        }
        $this->notify('NOTIFY_PLUGINMANAGER_DO_UPGRADE', ['plugin_key' => $this->currentFieldValue('unique_key'), 'version' => $this->request->input('version'), 'old_version' => $this->currentFieldValue('version')]);

        $this->messageStack->add_session(TEXT_UPGRADE_SUCCESS, 'success');
        zen_redirect(
            zen_href_link(
                FILENAME_PLUGIN_MANAGER,
                $this->pageLink() . '&' . $this->colKeylink()
            )
        );
    }

    /**
     * @since ZC v1.5.8
     */
    protected function processActionCleanUp()
    {
        $versions = $this->pluginManager->getPluginVersionsToClean($this->currentFieldValue('unique_key'), $this->currentFieldValue('version'));
        $this->setBoxHeader('<h4>' . zen_output_string_protected(zen_lookup_admin_menu_language_override('plugin_name', $this->currentFieldValue('unique_key'), $this->currentFieldValue('name'))) . '</h4>');
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
            $this->setBoxContent('<br>' . zen_draw_checkbox_field('version[]', $version['version']) . ' ' . $version['version']);
        }
        $this->setBoxContent(
            '<br><button type="submit" class="btn btn-danger">'
            . TEXT_CONFIRM . '</button> <a href="' . zen_href_link(
                FILENAME_PLUGIN_MANAGER,
                $this->pageLink() . '&' . $this->colKeylink()
            ) . '" class="btn btn-default" role="button">' . IMAGE_CANCEL . '</a>'
        );
    }

    /**
     * @since ZC v1.5.8
     */
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
        $this->setBoxHeader('<h4>' . zen_lookup_admin_menu_language_override('plugin_name', $this->currentFieldValue('unique_key'), $this->currentFieldValue('name')) . '</h4>');
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
            '<br><button type="submit" class="btn btn-danger">'
            . TEXT_CONFIRM . '</button> <a href="' . zen_href_link(
                FILENAME_PLUGIN_MANAGER,
                $this->pageLink() . '&' . $this->colKeylink()
            ) . '" class="btn btn-default" role="button">' . IMAGE_CANCEL . '</a>'
        );
    }

    /**
     * @since ZC v1.5.8
     */
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
        $error = "";
        foreach ($this->request->input('version') as $version) {
            $path = DIR_FS_CATALOG . 'zc_plugins/' . $this->currentFieldValue('unique_key') . '/' . $version;
            (new FileSystem)->deleteDirectory($path);
            if (is_dir($path)) {
                $error .= " :" . $path;
            }
        }
        if ($error === "") {
            $this->messageStack->add_session(TEXT_CLEANUP_SUCCESS, 'success');
        } else {
            $this->messageStack->add_session(TEXT_CLEANUP_ERROR . $error, 'error');
        }
        $this->notify('NOTIFY_PLUGINMANAGER_DO_CLEANUP', ['plugin_key' => $this->currentFieldValue('unique_key'), 'version' => $this->request->input('version')]);

        zen_redirect(zen_href_link(FILENAME_PLUGIN_MANAGER, $this->pageLink()));
    }

    /**
     * @since ZC v1.5.8
     */
    protected function processActionEnable()
    {
        $this->setBoxHeader('<h4>' . zen_lookup_admin_menu_language_override('plugin_name', $this->currentFieldValue('unique_key'), $this->currentFieldValue('name')) . '</h4>');
        $this->setBoxForm(zen_draw_form(
                'pluginuninstall',
                FILENAME_PLUGIN_MANAGER,
                $this->pageLink() . '&' . $this->colKeylink() . '&action=doEnable',
                'post',
                'class="form-horizontal"'
            ) . zen_draw_hidden_field('version', $this->currentFieldValue('version')));
        $this->setBoxContent('<br>' . TEXT_CONFIRM_ENABLE . '<br>');
        $this->setBoxContent(
            '<br><button type="submit" class="btn btn-primary">'
            . TEXT_ENABLE . '</button> <a href="' . zen_href_link(
                FILENAME_PLUGIN_MANAGER,
                $this->pageLink() . '&' . $this->colKeylink()
            ) . '" class="btn btn-default" role="button">' . IMAGE_CANCEL . '</a>'
        );
    }

    /**
     * @since ZC v1.5.8
     */
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
        $installer = $this->installerFactory->make($this->currentFieldValue('unique_key'), $this->request->input('version'));
        $installer->processEnable($this->currentFieldValue('unique_key'), $this->request->input('version'));
        $this->notify('NOTIFY_PLUGINMANAGER_DO_ENABLE', ['plugin_key' => $this->currentFieldValue('unique_key'), 'version' => $this->request->input('version')]);

        $this->messageStack->add_session(TEXT_ENABLE_SUCCESS, 'success');
        zen_redirect(
            zen_href_link(
                FILENAME_PLUGIN_MANAGER,
                $this->pageLink() . '&' . $this->colKeylink()
            )
        );
    }

    /**
     * @since ZC v1.5.8
     */
    protected function processActionDisable()
    {
        $this->setBoxHeader('<h4>' . zen_lookup_admin_menu_language_override('plugin_name', $this->currentFieldValue('unique_key'), $this->currentFieldValue('name')) . '</h4>');
        $this->setBoxForm(zen_draw_form(
                'pluginuninstall',
                FILENAME_PLUGIN_MANAGER,
                $this->pageLink() . '&' . $this->colKeylink() . '&action=doDisable',
                'post',
                'class="form-horizontal"'
            ) . zen_draw_hidden_field('version', $this->currentFieldValue('version')));
        $this->setBoxContent('<br>' . TEXT_CONFIRM_DISABLE . '<br>');
        $this->setBoxContent(
            '<br><button type="submit" class="btn btn-danger">'
            . TEXT_DISABLE . '</button> <a href="' . zen_href_link(
                FILENAME_PLUGIN_MANAGER,
                $this->pageLink() . '&' . $this->colKeylink()
            ) . '" class="btn btn-default" role="button">' . IMAGE_CANCEL . '</a>'
        );
    }

    /**
     * @since ZC v1.5.8
     */
    protected function processActionDoDisable()
    {
        if (!$this->request->has('version')) {
            zen_redirect(zen_href_link(FILENAME_PLUGIN_MANAGER, $this->pageLink() . '&' . $this->colKeylink()));
        }
        $installer = $this->installerFactory->make($this->currentFieldValue('unique_key'), $this->request->input('version'));
        $installer->processDisable($this->currentFieldValue('unique_key'), $this->request->input('version'));
        $this->notify('NOTIFY_PLUGINMANAGER_DO_DISABLE', ['plugin_key' => $this->currentFieldValue('unique_key'), 'version' => $this->request->input('version')]);

        $this->messageStack->add_session(TEXT_DISABLE_SUCCESS, 'success');
        zen_redirect(zen_href_link(FILENAME_PLUGIN_MANAGER, $this->pageLink() . '&' . $this->colKeylink()));
    }
}
