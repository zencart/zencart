<?php

/**
 * TestUtil.php
 *
 * @since     2020-12-19
 * @category  Library
 * @package   Barcode
 * @author    Nicola Asuni <info@tecnick.com>
 * @copyright 2015-2024 Nicola Asuni - Tecnick.com LTD
 * @license   http://www.gnu.org/copyleft/lesser.html GNU-LGPL v3 (see LICENSE.TXT)
 * @link      https://github.com/tecnickcom/tc-lib-barcode
 *
 * This file is part of tc-lib-color software library.
 */

namespace Test;

use PHPUnit\Framework\TestCase;

/**
 * Web Color class test
 *
 * @since     2020-12-19
 * @category  Library
 * @package   Barcode
 * @author    Nicola Asuni <info@tecnick.com>
 * @copyright 2015-2024 Nicola Asuni - Tecnick.com LTD
 * @license   http://www.gnu.org/copyleft/lesser.html GNU-LGPL v3 (see LICENSE.TXT)
 * @link      https://github.com/tecnickcom/tc-lib-barcode
 */
class TestUtil extends TestCase
{
    public function bcAssertEqualsWithDelta(
        mixed $expected,
        mixed $actual,
        float $delta = 0.01,
        string $message = ''
    ): void {
        parent::assertEqualsWithDelta($expected, $actual, $delta, $message);
    }

    /**
     * @param class-string<\Throwable> $exception
     */
    public function bcExpectException($exception): void
    {
        parent::expectException($exception);
    }
}
