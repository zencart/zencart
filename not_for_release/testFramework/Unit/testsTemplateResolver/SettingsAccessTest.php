<?php
/**
 * @copyright Copyright 2003-2026 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 */

namespace Tests\Unit\testsTemplateResolver;

use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tests\Support\zcUnitTestCase;

/**
 * Covers Settings' constructor, casting rules, constant lookups and TemplateSettings' import of
 * the global $tpl_settings array.
 */
#[RunTestsInSeparateProcesses]
class SettingsAccessTest extends zcUnitTestCase
{
    public function setUp(): void
    {
        parent::setUp();

        require_once DIR_FS_CATALOG . 'includes/classes/Settings.php';
        require_once DIR_FS_CATALOG . 'includes/classes/TemplateSettings.php';
    }

    public function testConstructorStoresTheSettingsItIsGiven(): void
    {
        $settings = $this->makeSettings(['FIRST' => 'one', 'SECOND' => ['value' => 2]]);

        $this->assertSame('one', $settings->FIRST);
        $this->assertSame(2, $settings['SECOND'], 'a value/type array stores the value');
        $this->assertCount(2, $settings);
    }

    public function testGlobalTemplateSettingsAreImportedByTemplateSettings(): void
    {
        $GLOBALS['tpl_settings'] = ['FROM_GLOBAL' => 'yes'];

        $imported = new \TemplateSettings();

        $this->assertSame('yes', $imported->FROM_GLOBAL);
        $this->assertCount(1, $imported);
        unset($GLOBALS['tpl_settings']);
        $this->assertCount(0, new \TemplateSettings());
    }

    public function testExistingSettingsAreOnlyReplacedWhenOverwriteIsRequested(): void
    {
        $settings = $this->makeSettings(['KEEP' => 'original']);

        $settings->setFromArray(['KEEP' => 'ignored', 'NEW' => 'added']);
        $this->assertSame('original', $settings->KEEP);
        $this->assertSame('added', $settings->NEW);

        $settings->setFromArray(['KEEP' => 'replaced'], true);
        $this->assertSame('replaced', $settings->KEEP);
        $settings->setFromArray(null);
        $settings->setFromArray([]);
        $this->assertCount(2, $settings);
    }

    public function testBooleanStringHandlingOnlyAppliesToBooleanCasts(): void
    {
        $settings = new \TemplateSettings();

        $this->assertSame(0, $this->callProtected($settings, 'returnCastValue', 'true', 'int'));
        $this->assertSame('false', $this->callProtected($settings, 'returnCastValue', 'false', 'string'));
        $this->assertTrue($this->callProtected($settings, 'returnCastValue', 'yes', 'bool'), 'other non-empty strings use the native cast');
        $this->assertFalse($this->callProtected($settings, 'returnCastValue', 'FALSE', 'boolean'));
        $this->assertTrue($this->callProtected($settings, 'returnCastValue', 'TRUE', 'bool'));
        $this->assertTrue($this->callProtected($settings, 'returnCastValue', 1, 'bool'));
        $this->assertSame('x', $this->callProtected($settings, 'returnCastValue', 'x', null));
        $this->assertSame([5], $this->callProtected($settings, 'returnCastValue', 5, 'array'));
        $this->assertSame(['a'], $this->callProtected($settings, 'returnCastValue', ['a'], 'array'));
        $this->assertSame(1.5, $this->callProtected($settings, 'returnCastValue', '1.5', 'float'));
        $this->assertSame('same', $this->callProtected($settings, 'returnCastValue', 'same', 'unknown'));
    }

    public function testGlobalConstantLookups(): void
    {
        define('ZZ_SETTINGS_ACCESS_TEST_CONSTANT', 'constant value');
        $settings = new \TemplateSettings();

        $this->assertTrue($this->callProtected($settings, 'globalConstantExists', 'ZZ_SETTINGS_ACCESS_TEST_CONSTANT'));
        $this->assertFalse($this->callProtected($settings, 'globalConstantExists', 'ZZ_SETTINGS_ACCESS_TEST_MISSING'));
        $this->assertSame('constant value', $this->callProtected($settings, 'getGlobalConstant', 'ZZ_SETTINGS_ACCESS_TEST_CONSTANT'));
        $this->assertNull($this->callProtected($settings, 'getGlobalConstant', 'ZZ_SETTINGS_ACCESS_TEST_MISSING'));
        $this->assertSame('constant value', $settings->ZZ_SETTINGS_ACCESS_TEST_CONSTANT, 'template settings fall back to constants');
    }

    private function makeSettings(array $settings): \Settings
    {
        return new class ($settings) extends \Settings {
        };
    }

    private function callProtected(object $object, string $method, mixed ...$arguments): mixed
    {
        $call = \Closure::bind(fn() => $this->$method(...$arguments), $object, \TemplateSettings::class);

        return $call();
    }
}
