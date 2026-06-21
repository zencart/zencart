<?php
/**
 * @copyright Copyright 2003-2026 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 */

namespace Tests\Unit\testsTemplateResolver;

use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tests\Support\zcUnitTestCase;

/**
 * Regression coverage for two casting/access edge cases in Settings:
 * - boolean string casts must recognize mixed-case 'True'/'False', not just all-lower/all-upper.
 * - an explicitly-stored null value must not be masked by a same-named global constant.
 */
#[RunTestsInSeparateProcesses]
class SettingsValueCastingTest extends zcUnitTestCase
{
    public function setUp(): void
    {
        parent::setUp();

        require_once DIR_FS_CATALOG . 'includes/classes/Settings.php';
        require_once DIR_FS_CATALOG . 'includes/classes/TemplateSettings.php';
    }

    /**
     * This is the bug fixed alongside this test: the boolean-string special case in
     * returnCastValue() only recognized 'true'/'TRUE'/'false'/'FALSE'. Title-case
     * 'True'/'False' - the dominant convention used for boolean-ish config defaults
     * throughout this codebase - fell through to PHP's native (bool) cast, where any
     * non-empty string (including the string 'False') is truthy.
     */
    public function testBoolCastRecognizesTitleCaseTrueAndFalseStrings(): void
    {
        $tplSetting = new \TemplateSettings();
        $tplSetting->setFromArray(['FLAG_TRUE' => 'True', 'FLAG_FALSE' => 'False']);
        $tplSetting->setType('FLAG_TRUE', 'bool');
        $tplSetting->setType('FLAG_FALSE', 'bool');

        $this->assertTrue($tplSetting->FLAG_TRUE, "A 'True' string cast to bool should be true.");
        $this->assertFalse($tplSetting->FLAG_FALSE, "A 'False' string cast to bool should be false, not PHP's native truthy non-empty-string result.");
    }

    /**
     * Guards the casts that were already correct before this fix: all-lowercase and
     * all-uppercase boolean strings must keep working.
     */
    public function testBoolCastStillRecognizesLowerAndUpperCaseTrueAndFalseStrings(): void
    {
        $tplSetting = new \TemplateSettings();
        $tplSetting->setFromArray([
            'FLAG_LOWER_TRUE' => 'true',
            'FLAG_UPPER_TRUE' => 'TRUE',
            'FLAG_LOWER_FALSE' => 'false',
            'FLAG_UPPER_FALSE' => 'FALSE',
        ]);
        foreach (['FLAG_LOWER_TRUE', 'FLAG_UPPER_TRUE', 'FLAG_LOWER_FALSE', 'FLAG_UPPER_FALSE'] as $key) {
            $tplSetting->setType($key, 'bool');
        }

        $this->assertTrue($tplSetting->FLAG_LOWER_TRUE);
        $this->assertTrue($tplSetting->FLAG_UPPER_TRUE);
        $this->assertFalse($tplSetting->FLAG_LOWER_FALSE);
        $this->assertFalse($tplSetting->FLAG_UPPER_FALSE);
    }

    /**
     * This is the other bug fixed alongside this test: __get() used `??` to decide
     * between an explicit value and the constant fallback, which also falls through
     * whenever the explicit value is itself null - masking a deliberate null override
     * with a same-named constant's value.
     */
    public function testExplicitNullValueIsNotMaskedBySameNamedGlobalConstant(): void
    {
        if (!defined('EXPLICIT_NULL_OVERRIDE_KEY')) {
            define('EXPLICIT_NULL_OVERRIDE_KEY', 'constant-value');
        }

        $tplSetting = new \TemplateSettings();
        $tplSetting->setFromArray(['EXPLICIT_NULL_OVERRIDE_KEY' => null]);

        $this->assertNull(
            $tplSetting->EXPLICIT_NULL_OVERRIDE_KEY,
            'An explicitly-set null value should be returned as-is, not replaced by a same-named constant.'
        );
    }

    /**
     * Guards the fallback path that must keep working: with no explicit value set at
     * all (not even null), reading the key should still resolve to the global constant.
     */
    public function testGlobalConstantFallbackStillWorksWhenNoExplicitValueIsSet(): void
    {
        if (!defined('NO_OVERRIDE_CONSTANT_KEY')) {
            define('NO_OVERRIDE_CONSTANT_KEY', 'constant-value');
        }

        $tplSetting = new \TemplateSettings();

        $this->assertSame('constant-value', $tplSetting->NO_OVERRIDE_CONSTANT_KEY);
    }
}
