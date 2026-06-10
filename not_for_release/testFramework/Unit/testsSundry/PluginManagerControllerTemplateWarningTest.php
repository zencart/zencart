<?php
/**
 * @copyright Copyright 2003-2026 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 */

namespace Zencart\ViewBuilders {
    function zen_href_link(string $filename, string $parameters = ''): string
    {
        return $filename . ($parameters === '' ? '' : '?' . $parameters);
    }

    function zen_lookup_admin_menu_language_override(string $key, string $uniqueKey, string $default): string
    {
        return $default;
    }
}

namespace Tests\Unit\testsSundry {

use Tests\Support\zcUnitTestCase;
use Zencart\PluginSupport\PluginStatus;
use Zencart\ViewBuilders\PluginManagerController;
use Zencart\Request\Request;
use Zencart\ViewBuilders\TableViewDefinition;
use Zencart\PluginManager\PluginManager;
use Zencart\PluginSupport\InstallerFactory;

class PluginManagerControllerTemplateWarningTest extends zcUnitTestCase
{
    protected $runTestInSeparateProcess = true;
    protected $preserveGlobalState = false;

    public function testTemplatePluginAssignedToOtherLanguageShowsUninstallWarning(): void
    {
        $this->defineControllerConstants();
        $_SESSION['languages_id'] = 0;
        require_once DIR_FS_CATALOG . 'includes/classes/db/mysql/query_factory.php';

        $pluginRows = [
            [
                'unique_key' => 'ResponsiveClassicPlugin',
                'name' => 'Responsive Classic Plugin',
                'description' => 'Template plugin',
                'type' => 'template',
                'status' => PluginStatus::ENABLED,
                'author' => 'Zen Cart',
                'version' => 'v1.0.0',
            ],
        ];
        $templateRows = [
            [
                'template_id' => '1',
                'template_dir' => 'template_default',
                'template_language' => '0',
                'template_settings' => null,
            ],
            [
                'template_id' => '2',
                'template_dir' => 'responsive_classic_plugin',
                'template_language' => '999',
                'template_settings' => null,
            ],
        ];

        $GLOBALS['db'] = $this->getMockBuilder('queryFactory')
            ->disableOriginalConstructor()
            ->getMock();
        $GLOBALS['db']->method('Execute')->willReturnCallback(
            function (string $sql) use ($pluginRows, $templateRows): \queryFactoryResult {
                $result = $this->getMockBuilder('queryFactoryResult')
                    ->disableOriginalConstructor()
                    ->getMock();

                if (str_contains($sql, TABLE_PLUGIN_CONTROL)) {
                    $result->fields = $pluginRows[0];
                    return $this->mockIterator($result, $pluginRows);
                }

                if (str_contains($sql, TABLE_TEMPLATE_SELECT)) {
                    $result->fields = $templateRows[0];
                    return $this->mockIterator($result, $templateRows);
                }

                $result->fields = [];
                return $this->mockIterator($result, []);
            }
        );

        $request = $this->createMock(Request::class);
        $request->method('input')->willReturnCallback(
            static function (string $key, mixed $default = null): mixed {
                return match ($key) {
                    'action' => '',
                    'page' => 1,
                    default => $default,
                };
            }
        );

        $tableDefinition = $this->createMock(TableViewDefinition::class);
        $tableDefinition->method('getParameter')->willReturnCallback(
            static function (string $key): string {
                return match ($key) {
                    'pagerVariable' => 'page',
                    'colKey' => 'unique_key',
                    default => '',
                };
            }
        );
        $tableDefinition->method('colKeyName')->willReturn('unique_key');

        $formatter = new class {
            public function currentRowFromRequest(): object
            {
                return (object) [
                    'unique_key' => 'ResponsiveClassicPlugin',
                    'version' => 'v1.0.0',
                    'status' => PluginStatus::ENABLED,
                    'name' => 'Responsive Classic Plugin',
                    'description' => 'Template plugin',
                    'author' => 'Zen Cart',
                    'zc_contrib_id' => 0,
                ];
            }
        };

        $pluginManager = $this->createMock(PluginManager::class);
        $pluginManager->method('isNewDownloadAvailable')->willReturn(false);
        $pluginManager->method('isUpgradeAvailable')->willReturn(false);
        $pluginManager->method('hasPluginVersionsToClean')->willReturn(0);

        $installerFactory = $this->createMock(InstallerFactory::class);
        $messageStack = new class {
            public function add_session(string $message, string $type): void
            {
            }
        };

        $controller = new PluginManagerController($request, $messageStack, $tableDefinition, $formatter);
        $controller->init($pluginManager, $installerFactory);
        $controller->processRequest();

        $content = implode(
            "\n",
            array_map(
                static fn(array $entry): string => (string) ($entry['text'] ?? ''),
                $controller->getBoxContent()
            )
        );

        $this->assertStringContainsString('Assigned via <a href="template_select">Template Selection</a>', $content);
    }

    private function defineControllerConstants(): void
    {
        defined('DIR_FS_CATALOG') || define('DIR_FS_CATALOG', ROOTCWD);
        defined('DIR_WS_CATALOG') || define('DIR_WS_CATALOG', '/');
        defined('TABLE_PLUGIN_CONTROL') || define('TABLE_PLUGIN_CONTROL', 'plugin_control');
        defined('TABLE_TEMPLATE_SELECT') || define('TABLE_TEMPLATE_SELECT', 'template_select');
        defined('FILENAME_PLUGIN_MANAGER') || define('FILENAME_PLUGIN_MANAGER', 'plugin_manager');
        defined('FILENAME_TEMPLATE_SELECT') || define('FILENAME_TEMPLATE_SELECT', 'template_select');
        defined('BOX_TOOLS_TEMPLATE_SELECT') || define('BOX_TOOLS_TEMPLATE_SELECT', 'Template Selection');
        defined('TEXT_VERSION_INSTALLED') || define('TEXT_VERSION_INSTALLED', 'Installed %s');
        defined('TEXT_INFO_DESCRIPTION') || define('TEXT_INFO_DESCRIPTION', 'Description');
        defined('TEXT_PLUGIN_AUTHOR') || define('TEXT_PLUGIN_AUTHOR', 'Author %s');
        defined('TEXT_DISABLE') || define('TEXT_DISABLE', 'Disable');
        defined('TEXT_ENABLE') || define('TEXT_ENABLE', 'Enable');
        defined('TEXT_UNINSTALL') || define('TEXT_UNINSTALL', 'Uninstall');
        defined('WARNING_TEMPLATE_IS_ACTIVE') || define('WARNING_TEMPLATE_IS_ACTIVE', 'Assigned via <a href="%1$s">%2$s</a>');
    }
}
}
