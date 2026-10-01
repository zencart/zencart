<?php

declare(strict_types=1);
/**
 * @copyright Copyright 2003-2026 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 */

namespace Tests\Unit\testsSundry;

use Tests\Support\zcUnitTestCase;
use Zencart\PluginSupport\PluginManifest;

/**
 * PluginManifest consolidates the "find, require and interpret a plugin's manifest.php"
 * handling that the Plugin Manager, InstallerFactory, TemplateResolver and the
 * InteractsWithPlugins trait each used to do for themselves.
 *
 * Every test works against a throw-away plugins root created under the system temp
 * directory, so nothing here depends on the repository's real zc_plugins/ contents.
 */
class PluginManifestTest extends zcUnitTestCase
{
    private string $pluginsRoot;

    public function setUp(): void
    {
        parent::setUp();

        $this->pluginsRoot = sys_get_temp_dir() . '/zc_plugin_manifest_test_' . bin2hex(random_bytes(4));
        mkdir($this->pluginsRoot, 0777, true);
    }

    public function tearDown(): void
    {
        $this->removeDirectory($this->pluginsRoot);

        parent::tearDown();
    }

    public function testExistsReturnsNullWhenNoManifestIsPresent(): void
    {
        mkdir($this->pluginsRoot . '/NoManifest/v1.0.0', 0777, true);

        $manifest = new PluginManifest($this->pluginsRoot);

        $this->assertNull($manifest->exists('NoManifest', 'v1.0.0'));
        $this->assertNull($manifest->exists('NotAPlugin', 'v1.0.0'));
        $this->assertNull($manifest->get('NoManifest', 'v1.0.0'));
    }

    public function testExistsReturnsTheManifestPath(): void
    {
        $this->writeManifest('Sample', 'v1.0.0', "<?php\nreturn ['pluginVersion' => 'v1.0.0'];\n");

        $manifest = new PluginManifest($this->pluginsRoot);

        $this->assertSame(
            $this->pluginsRoot . '/Sample/v1.0.0/manifest.php',
            $manifest->exists('Sample', 'v1.0.0')
        );
    }

    public function testTrailingDirectorySeparatorsOnThePluginsRootAreIgnored(): void
    {
        $this->writeManifest('Sample', 'v1.0.0', "<?php\nreturn ['pluginVersion' => 'v1.0.0'];\n");

        $manifest = new PluginManifest($this->pluginsRoot . '/');

        $this->assertSame(
            $this->pluginsRoot . '/Sample/v1.0.0/manifest.php',
            $manifest->exists('Sample', 'v1.0.0')
        );
    }

    public function testGetReturnsTheManifestArray(): void
    {
        $this->writeManifest(
            'Sample',
            'v1.0.0',
            "<?php\nreturn ['pluginVersion' => 'v1.0.0', 'pluginName' => 'Sample Plugin'];\n"
        );

        $manifest = new PluginManifest($this->pluginsRoot);

        $this->assertSame(
            ['pluginVersion' => 'v1.0.0', 'pluginName' => 'Sample Plugin'],
            $manifest->get('Sample', 'v1.0.0')
        );
    }

    public function testGetReturnsNullWhenTheManifestDoesNotReturnAnArray(): void
    {
        $this->writeManifest('Broken', 'v1.0.0', "<?php\nreturn 'not an array';\n");

        $manifest = new PluginManifest($this->pluginsRoot);

        $this->assertNull($manifest->get('Broken', 'v1.0.0'));
        $this->assertFalse($manifest->isSelectableTemplate('Broken', 'v1.0.0'));
        $this->assertNull($manifest->getSelectableTemplateKey('Broken', 'v1.0.0'));
        $this->assertFalse($manifest->removesUnencapsulatedVersion('Broken', 'v1.0.0'));
    }

    /**
     * A manifest is required from disk once per instance; later calls are served from the
     * cache, so the file's removal after the first read must not change the answers.
     */
    public function testAManifestIsReadOnceAndThenServedFromTheCache(): void
    {
        $this->writeManifest('Sample', 'v1.0.0', "<?php\nreturn ['pluginVersion' => 'v1.0.0'];\n");

        $manifest = new PluginManifest($this->pluginsRoot);
        $firstRead = $manifest->get('Sample', 'v1.0.0');

        unlink($this->pluginsRoot . '/Sample/v1.0.0/manifest.php');

        $this->assertSame($firstRead, $manifest->get('Sample', 'v1.0.0'));
        $this->assertSame(
            $this->pluginsRoot . '/Sample/v1.0.0/manifest.php',
            $manifest->exists('Sample', 'v1.0.0'),
            'exists() reports a cached manifest without re-checking the filesystem.'
        );
    }

    public function testATemplatePackageIsRecognisedByItsTemplateKey(): void
    {
        $this->writeManifest(
            'TemplatePlugin',
            'v2.0.0',
            "<?php\nreturn ['pluginVersion' => 'v2.0.0', 'template' => ['key' => 'my_template']];\n"
        );

        $manifest = new PluginManifest($this->pluginsRoot);

        $this->assertTrue($manifest->isSelectableTemplate('TemplatePlugin', 'v2.0.0'));
        $this->assertSame('my_template', $manifest->getSelectableTemplateKey('TemplatePlugin', 'v2.0.0'));
    }

    public function testAPluginWithoutATemplateArrayIsNotASelectableTemplate(): void
    {
        $this->writeManifest('Plain', 'v1.0.0', "<?php\nreturn ['pluginVersion' => 'v1.0.0'];\n");

        $manifest = new PluginManifest($this->pluginsRoot);

        $this->assertFalse($manifest->isSelectableTemplate('Plain', 'v1.0.0'));
        $this->assertNull($manifest->getSelectableTemplateKey('Plain', 'v1.0.0'));
    }

    /**
     * Only a non-empty string counts as a template key: an empty string, a missing
     * key, or a 'template' element that isn't an array all mean "not a template package".
     */
    public function testATemplateArrayWithoutAUsableKeyIsNotASelectableTemplate(): void
    {
        $this->writeManifest('EmptyKey', 'v1.0.0', "<?php\nreturn ['template' => ['key' => '']];\n");
        $this->writeManifest('NoKey', 'v1.0.0', "<?php\nreturn ['template' => ['baseTemplate' => 'x']];\n");
        $this->writeManifest('NotArray', 'v1.0.0', "<?php\nreturn ['template' => 'my_template'];\n");

        $manifest = new PluginManifest($this->pluginsRoot);

        foreach (['EmptyKey', 'NoKey', 'NotArray'] as $pluginKey) {
            $this->assertFalse($manifest->isSelectableTemplate($pluginKey, 'v1.0.0'), $pluginKey);
            $this->assertNull($manifest->getSelectableTemplateKey($pluginKey, 'v1.0.0'), $pluginKey);
        }
    }

    public function testRemovesUnencapsulatedVersionReflectsTheManifestFlag(): void
    {
        $this->writeManifest('Removes', 'v1.0.0', "<?php\nreturn ['removesUnencapsulatedVersion' => true];\n");
        $this->writeManifest('Keeps', 'v1.0.0', "<?php\nreturn ['removesUnencapsulatedVersion' => false];\n");
        $this->writeManifest('Silent', 'v1.0.0', "<?php\nreturn ['pluginVersion' => 'v1.0.0'];\n");

        $manifest = new PluginManifest($this->pluginsRoot);

        $this->assertTrue($manifest->removesUnencapsulatedVersion('Removes', 'v1.0.0'));
        $this->assertFalse($manifest->removesUnencapsulatedVersion('Keeps', 'v1.0.0'));
        $this->assertFalse($manifest->removesUnencapsulatedVersion('Silent', 'v1.0.0'));
    }

    private function writeManifest(string $pluginKey, string $version, string $contents): void
    {
        $dir = $this->pluginsRoot . '/' . $pluginKey . '/' . $version;
        if (!is_dir($dir)) {
            mkdir($dir, 0777, true);
        }
        file_put_contents($dir . '/manifest.php', $contents);
    }

    private function removeDirectory(string $path): void
    {
        if (!is_dir($path)) {
            return;
        }
        foreach (scandir($path) as $entry) {
            if ($entry === '.' || $entry === '..') {
                continue;
            }
            $child = $path . '/' . $entry;
            if (is_dir($child)) {
                $this->removeDirectory($child);
            } else {
                unlink($child);
            }
        }
        rmdir($path);
    }
}
