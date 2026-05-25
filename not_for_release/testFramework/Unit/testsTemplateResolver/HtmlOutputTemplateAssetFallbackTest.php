<?php
/**
 * @copyright Copyright 2003-2026 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 */

use Tests\Support\zcTemplateResolverTest;

class HtmlOutputTemplateAssetFallbackTest extends zcTemplateResolverTest
{
    private const BASE_THEME_PLUGIN = 'UnitTestHtmlOutputBaseTheme';
    private const CHILD_THEME_PLUGIN = 'UnitTestHtmlOutputChildTheme';
    private const BASE_TEMPLATE_KEY = 'unit_test_html_output_base_theme';
    private const CHILD_TEMPLATE_KEY = 'html_output_child_theme';

    private string $baseThemePluginRoot;
    private string $pluginRoot;
    private string $baseImage;
    private string $baseThemePluginImage;
    private string $templateDogfoodLanguageImage;

    public function setUp(): void
    {
        parent::setUp();
        require_once DIR_FS_CATALOG . 'includes/classes/TemplateDto.php';
        require_once DIR_FS_CATALOG . 'includes/classes/TemplateSelect.php';
        require_once DIR_FS_CATALOG . 'includes/classes/ResourceLoaders/TemplateResolver.php';
        require_once DIR_FS_CATALOG . 'includes/classes/db/mysql/query_factory.php';
        require_once DIR_FS_CATALOG . 'includes/functions/html_output.php';

        $this->baseThemePluginRoot = DIR_FS_CATALOG . 'zc_plugins/' . self::BASE_THEME_PLUGIN . '/v1.0.0/';
        $this->pluginRoot = DIR_FS_CATALOG . 'zc_plugins/' . self::CHILD_THEME_PLUGIN . '/v1.0.0/';
        $this->baseImage = DIR_FS_CATALOG . 'includes/templates/responsive_classic/images/zz_unit_image.png';
        $this->baseThemePluginImage = $this->baseThemePluginRoot . 'catalog/includes/templates/' . self::BASE_TEMPLATE_KEY . '/images/zz_unit_image.png';
        $this->templateDogfoodLanguageImage = DIR_FS_CATALOG . 'includes/languages/english/' . self::BASE_TEMPLATE_KEY . '/zz_unit_lang.png';

        $this->removeDirectory($this->baseThemePluginRoot);
        $this->removeDirectory($this->pluginRoot);
        @mkdir($this->baseThemePluginRoot . 'catalog/includes/templates/' . self::BASE_TEMPLATE_KEY . '/images', 0777, true);
        @mkdir($this->pluginRoot . 'catalog/includes/templates/' . self::CHILD_TEMPLATE_KEY, 0777, true);
        @mkdir(dirname($this->baseImage), 0777, true);
        @mkdir(dirname($this->templateDogfoodLanguageImage), 0777, true);

        file_put_contents(
            $this->baseThemePluginRoot . 'manifest.php',
            sprintf(<<<'PHP'
<?php
return [
    'pluginVersion' => 'v1.0.0',
    'pluginName' => 'Unit Test Base Theme',
    'template' => [
        'key' => '%s',
        'baseTemplate' => 'template_default',
        'infoFile' => 'catalog/includes/templates/%s/template_info.php',
    ],
];
PHP
            ,
            self::BASE_TEMPLATE_KEY,
            self::BASE_TEMPLATE_KEY
        ));
        file_put_contents(
            $this->baseThemePluginRoot . 'catalog/includes/templates/' . self::BASE_TEMPLATE_KEY . '/template_info.php',
            <<<'PHP'
<?php
$template_name = 'Unit Test Base Theme';
$template_version = '1.0.0';
$template_author = 'Zen Cart';
$template_description = 'Unit test plugin-backed base theme';
$template_screenshot = 'screenshot.png';
PHP
        );
        file_put_contents(
            $this->pluginRoot . 'manifest.php',
            sprintf(<<<'PHP'
<?php
return [
    'pluginVersion' => 'v1.0.0',
    'pluginName' => 'Unit Test Child Theme',
    'template' => [
        'key' => '%s',
        'baseTemplate' => '%s',
        'infoFile' => 'catalog/includes/templates/%s/template_info.php',
    ],
];
PHP
            ,
            self::CHILD_TEMPLATE_KEY,
            self::BASE_TEMPLATE_KEY,
            self::CHILD_TEMPLATE_KEY
        ));
        file_put_contents(
            $this->pluginRoot . 'catalog/includes/templates/' . self::CHILD_TEMPLATE_KEY . '/template_info.php',
            <<<'PHP'
<?php
$template_name = 'Child Theme';
$template_version = '1.0.0';
$template_author = 'Zen Cart';
$template_description = 'Unit test child theme';
$template_screenshot = 'screenshot.png';
PHP
        );

        file_put_contents($this->baseImage, 'base');
        file_put_contents($this->baseThemePluginImage, 'base-plugin');
        file_put_contents($this->templateDogfoodLanguageImage, 'lang');

        $_SESSION['language'] = 'english';
        $GLOBALS['template_dir'] = self::CHILD_TEMPLATE_KEY;

        $this->instantiateQfr([
            'template_id' => '1',
            'template_dir' => self::CHILD_TEMPLATE_KEY,
            'template_language' => 0,
            'template_settings' => null,
        ]);

        $GLOBALS['installedPlugins'] = [
            [
                'unique_key' => self::BASE_THEME_PLUGIN,
                'version' => 'v1.0.0',
            ],
            [
                'unique_key' => self::CHILD_THEME_PLUGIN,
                'version' => 'v1.0.0',
            ],
        ];
    }

    public function tearDown(): void
    {
        @unlink($this->baseImage);
        @unlink($this->templateDogfoodLanguageImage);
        $this->removeDirectory($this->baseThemePluginRoot);
        $this->removeDirectory($this->pluginRoot);
        unset($GLOBALS['installedPlugins']);
        parent::tearDown();
    }

    public function testTemplateAssetFallbackUsesBaseTemplatePath(): void
    {
        $missingChildPath = 'zc_plugins/' . self::CHILD_THEME_PLUGIN . '/v1.0.0/catalog/includes/templates/' . self::CHILD_TEMPLATE_KEY . '/images/zz_unit_image.png';

        $this->assertSame(
            'zc_plugins/' . self::BASE_THEME_PLUGIN . '/v1.0.0/catalog/includes/templates/' . self::BASE_TEMPLATE_KEY . '/images/zz_unit_image.png',
            zen_resolve_template_fallback_asset_path($missingChildPath, self::CHILD_TEMPLATE_KEY)
        );
    }

    public function testTemplateLanguageAssetFallbackUsesBaseTemplatePath(): void
    {
        $missingChildLanguagePath = 'includes/languages/english/' . self::CHILD_TEMPLATE_KEY . '/zz_unit_lang.png';

        $this->assertSame(
            'includes/languages/english/' . self::BASE_TEMPLATE_KEY . '/zz_unit_lang.png',
            zen_resolve_template_fallback_asset_path($missingChildLanguagePath, self::CHILD_TEMPLATE_KEY)
        );
    }

    private function removeDirectory(string $directory): void
    {
        if (!is_dir($directory)) {
            return;
        }

        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($directory, \FilesystemIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::CHILD_FIRST
        );

        foreach ($iterator as $item) {
            if ($item->isDir()) {
                rmdir($item->getPathname());
                continue;
            }

            unlink($item->getPathname());
        }

        rmdir($directory);
    }
}
