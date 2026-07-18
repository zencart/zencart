<?php
/**
 * @copyright Copyright 2003-2026 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 */

namespace Tests\Unit\testsTemplateResolver;

use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tests\Support\zcTemplateResolverTest;
use Zencart\LanguageLoader\ArraysLanguageLoader;

/**
 * Regression coverage for a real-world crash reported against the DBIO plugin
 * (lat9/dbio#269): ArraysLanguageLoader.php has `declare(strict_types=1)`, and
 * makeConstants() passed a language-constant's value directly as the $subject
 * argument to preg_match_all(), which is internally typed `string`. A plugin (or
 * core) language file defining a non-string constant -- e.g. `'SOME_COUNT' => 167`
 * -- triggered a fatal TypeError on every request that loaded it. Fixed by casting
 * the value to (string) only for the regex call, leaving the value passed to the
 * later define() call untouched so the constant's original type is preserved.
 */
#[RunTestsInSeparateProcesses]
class ArraysLanguageLoaderNonStringConstantTest extends zcTemplateResolverTest
{
    public function testMakeConstantsAcceptsAnIntegerValuedLanguageConstantWithoutFatalError(): void
    {
        require_once DIR_FS_CATALOG . 'includes/classes/ResourceLoaders/BaseLanguageLoader.php';
        require_once DIR_FS_CATALOG . 'includes/classes/ResourceLoaders/ArraysLanguageLoader.php';

        $loader = new ArraysLanguageLoader([], 'unit_test_page', 'template_default');

        $result = $loader->makeConstants(['DBIO_UNIT_TEST_INT_CONSTANT' => 167]);

        $this->assertTrue($result, 'Expected makeConstants() to report that a constant was made.');
        $this->assertTrue(defined('DBIO_UNIT_TEST_INT_CONSTANT'));
        $this->assertSame(
            167,
            \DBIO_UNIT_TEST_INT_CONSTANT,
            'Expected the constant to retain its original int type, not be stringified by the preg_match_all() fix.'
        );
    }

    public function testMakeConstantsAcceptsABooleanValuedLanguageConstantWithoutFatalError(): void
    {
        require_once DIR_FS_CATALOG . 'includes/classes/ResourceLoaders/BaseLanguageLoader.php';
        require_once DIR_FS_CATALOG . 'includes/classes/ResourceLoaders/ArraysLanguageLoader.php';

        $loader = new ArraysLanguageLoader([], 'unit_test_page', 'template_default');

        $loader->makeConstants(['DBIO_UNIT_TEST_BOOL_CONSTANT' => false]);

        $this->assertTrue(defined('DBIO_UNIT_TEST_BOOL_CONSTANT'));
        $this->assertFalse(\DBIO_UNIT_TEST_BOOL_CONSTANT);
    }
}
