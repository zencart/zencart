<?php
/**
 * @copyright Copyright 2003-2026 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 */

namespace Tests\Unit\testsTemplateResolver;

use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tests\Support\zcUnitTestCase;

/**
 * Regression coverage for Settings::setFromArray()'s precedence between an explicit
 * override and a same-named global constant, as consumed by TemplateSettings
 * (the $tplSetting object set up by includes/init_includes/init_templates.php).
 */
#[RunTestsInSeparateProcesses]
class SettingsTemplateOverrideTest extends zcUnitTestCase
{
    public function setUp(): void
    {
        parent::setUp();

        require_once DIR_FS_CATALOG . 'includes/classes/Settings.php';
        require_once DIR_FS_CATALOG . 'includes/classes/TemplateSettings.php';
    }

    /**
     * This is the bug fixed alongside this test: setFromArray() previously used
     * offsetExists() to decide whether a key should be skipped when $overwrite is
     * false. Because TemplateSettings enables constant fallback, offsetExists()
     * also returns true for any key that merely has a same-named global constant
     * defined - so a real override (e.g. from a template's template_settings.php,
     * or a per-template DB override) was silently discarded whenever a config
     * constant of the same name already existed. That defeated the override
     * support the $tplSetting conversion exists to provide.
     */
    public function testSetFromArrayOverrideWinsOverSameNamedGlobalConstant(): void
    {
        if (!defined('COLUMN_LEFT_STATUS')) {
            define('COLUMN_LEFT_STATUS', '1');
        }

        $tplSetting = new \TemplateSettings();
        $tplSetting->setFromArray(['COLUMN_LEFT_STATUS' => '0']);

        $this->assertSame(
            '0',
            $tplSetting->COLUMN_LEFT_STATUS,
            'A template_settings.php-style override should take precedence over a same-named global constant.'
        );
    }

    /**
     * Guards the fallback path that must keep working: when no override is supplied
     * for a key, reading it should still resolve to the global constant.
     */
    public function testSetFromArrayFallsBackToGlobalConstantWhenNoOverrideProvided(): void
    {
        if (!defined('BOX_WIDTH_LEFT')) {
            define('BOX_WIDTH_LEFT', '150px');
        }

        $tplSetting = new \TemplateSettings();
        $tplSetting->setFromArray([]);

        $this->assertSame(
            '150px',
            $tplSetting->BOX_WIDTH_LEFT,
            'With no override supplied, the global constant should still be used.'
        );
    }

    /**
     * Guards the $overwrite=true contract: an already-set value must still be
     * replaceable on demand, independent of whether a constant exists.
     */
    public function testSetFromArrayWithOverwriteTrueReplacesAnExistingValue(): void
    {
        $tplSetting = new \TemplateSettings();
        $tplSetting->setFromArray(['MAX_DISPLAY_PAGE_LINKS' => '5']);
        $tplSetting->setFromArray(['MAX_DISPLAY_PAGE_LINKS' => '7'], true);

        $this->assertSame(
            '7',
            $tplSetting->MAX_DISPLAY_PAGE_LINKS,
            'setFromArray() with $overwrite=true should replace a previously-set value.'
        );
    }

    /**
     * Guards the $overwrite=false contract in the other direction: a value already
     * explicitly set must NOT be replaced by a later setFromArray() call.
     */
    public function testSetFromArrayWithOverwriteFalseKeepsFirstExplicitValue(): void
    {
        $tplSetting = new \TemplateSettings();
        $tplSetting->setFromArray(['MAX_DISPLAY_PAGE_LINKS' => '5']);
        $tplSetting->setFromArray(['MAX_DISPLAY_PAGE_LINKS' => '7']);

        $this->assertSame(
            '5',
            $tplSetting->MAX_DISPLAY_PAGE_LINKS,
            'setFromArray() with $overwrite=false should leave an already-set value alone.'
        );
    }
}
