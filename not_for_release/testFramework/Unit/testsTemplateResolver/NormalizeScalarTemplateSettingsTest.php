<?php
/**
 * @copyright Copyright 2003-2026 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 *
 * @since ZC v3.0.0
 */

namespace Tests\Unit\testsTemplateResolver;

use Tests\Support\zcUnitTestCase;

/**
 * Regression coverage for zen_normalize_scalar_template_settings() (includes/functions/functions_templates.php),
 * used by includes/init_includes/init_templates.php to fix up json_decode()'s native-typed
 * output for a per-template DB settings override before it's merged into $tpl_settings and
 * handed to TemplateSettings::setFromArray().
 */
class NormalizeScalarTemplateSettingsTest extends zcUnitTestCase
{
    public function setUp(): void
    {
        parent::setUp();

        require_once DIR_FS_CATALOG . 'includes/functions/functions_templates.php';
    }

    /**
     * A per-template override stored as a bare JSON number (e.g. {"PREV_NEXT_BAR_LOCATION":3})
     * decodes via json_decode() to a PHP int.
     * Every other source of a $tplSetting value has always been a string, and downstream code
     * throughout the codebase often strictly compares $tplSetting values against string literals
     * (e.g. === '3') - so an un-normalized int silently fails every such comparison.
     */
    public function testScalarValuesAreCastToStrings(): void
    {
        $result = zen_normalize_scalar_template_settings([
            'PREV_NEXT_BAR_LOCATION' => 3,
            'MAX_PREVIEW' => 12.5,
            'BREAD_CRUMBS_SEPARATOR' => 'already a string',
        ]);

        $this->assertSame('3', $result['PREV_NEXT_BAR_LOCATION']);
        $this->assertSame('12.5', $result['MAX_PREVIEW']);
        $this->assertSame('already a string', $result['BREAD_CRUMBS_SEPARATOR']);
    }

    /**
     * A bare JSON true/false override decodes to a PHP bool, and PHP's native (string) cast
     * turns true into "1" and false into "" (empty string) - neither of which matches our
     * usual boolean-string convention ('true'/'false', such as the one which
     * Settings::returnCastValue() special-cases). A naive (string) cast on a bool would
     * silently fail any downstream === 'false' check (since "" !== 'false') even though
     * this function's whole purpose is to keep such checks working.
     */
    public function testBooleanValuesAreCastToTrueFalseStringsNotOneOrEmptyString(): void
    {
        $result = zen_normalize_scalar_template_settings([
            'FLAG_TRUE' => true,
            'FLAG_FALSE' => false,
        ]);

        $this->assertSame('true', $result['FLAG_TRUE']);
        $this->assertSame('false', $result['FLAG_FALSE']);
    }

    /**
     * Guards the explicit ['value' => ..., 'type' => ...] override convention: those decode to
     * arrays, not scalars, and must be left completely untouched so Settings::offsetSet()'s own
     * type-casting still applies (rather than this function stringifying the array itself, or
     * mangling its 'value' entry before offsetSet() sees it).
     */
    public function testArrayValuesAreLeftUntouched(): void
    {
        $override = ['value' => 3, 'type' => 'int'];

        $result = zen_normalize_scalar_template_settings([
            'SOME_KEY' => $override,
        ]);

        $this->assertSame($override, $result['SOME_KEY']);
    }

    /**
     * Guards a deliberately-null override: null is not a scalar, so it must pass through
     * unchanged rather than becoming the string "" or being dropped.
     */
    public function testNullValueIsLeftUntouched(): void
    {
        $result = zen_normalize_scalar_template_settings(['SOME_KEY' => null]);

        $this->assertNull($result['SOME_KEY']);
    }

    /**
     * End-to-end check that the normalized value actually fixes the strict-comparison bug:
     * once run through TemplateSettings::setFromArray(), the value reads back identically to
     * how it would if it had come from zen_config()/a constant instead of a JSON override.
     */
    public function testNormalizedValueSurvivesStrictComparisonAfterTemplateSettingsRoundTrip(): void
    {
        require_once DIR_FS_CATALOG . 'includes/classes/Settings.php';
        require_once DIR_FS_CATALOG . 'includes/classes/TemplateSettings.php';

        $decoded = ['PREV_NEXT_BAR_LOCATION' => 3]; // simulates json_decode('{"PREV_NEXT_BAR_LOCATION":3}', true)
        $normalized = zen_normalize_scalar_template_settings($decoded);

        $tplSetting = new \TemplateSettings();
        $tplSetting->setFromArray($normalized);

        $this->assertTrue(
            $tplSetting->PREV_NEXT_BAR_LOCATION === '3',
            'A per-template override supplied as a bare JSON number must still satisfy the strict string comparisons used throughout the codebase.'
        );
    }

    /**
     * Same end-to-end check as above, for the boolean case specifically - this is the exact
     * failure mode flagged in review: a bare JSON `false` override must still satisfy
     * === 'false' downstream, not silently become an empty string.
     */
    public function testNormalizedBooleanFalseSurvivesStrictComparisonAfterTemplateSettingsRoundTrip(): void
    {
        require_once DIR_FS_CATALOG . 'includes/classes/Settings.php';
        require_once DIR_FS_CATALOG . 'includes/classes/TemplateSettings.php';

        $decoded = ['BEST_SELLERS_TRUNCATE_MORE' => false]; // simulates json_decode('{"BEST_SELLERS_TRUNCATE_MORE":false}', true)
        $normalized = zen_normalize_scalar_template_settings($decoded);

        $tplSetting = new \TemplateSettings();
        $tplSetting->setFromArray($normalized);

        $this->assertTrue(
            $tplSetting->BEST_SELLERS_TRUNCATE_MORE === 'false',
            'A per-template override supplied as a bare JSON false must still satisfy the strict string comparisons used throughout the codebase.'
        );
    }
}
