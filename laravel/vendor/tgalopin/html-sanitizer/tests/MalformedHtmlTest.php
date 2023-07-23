<?php

/*
 * This file is part of the HTML sanitizer project.
 *
 * (c) Titouan Galopin <galopintitouan@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Tests\HtmlSanitizer;

use HtmlSanitizer\Sanitizer;
use HtmlSanitizer\SanitizerInterface;
use PHPUnit\Framework\TestCase;

class MalformedHtmlTest extends TestCase
{
    public function testSanitizeMalformedUrl()
    {
        // Use rtrim to remove end line should be able to change without consequence on the validity of the test
        $input = rtrim(file_get_contents(__DIR__.'/Fixtures/malformed/input.html'));
        $expectedOutput = rtrim(file_get_contents(__DIR__.'/Fixtures/malformed/output.html'));

        $this->assertEquals($expectedOutput, $this->createSanitizer()->sanitize($input));
    }

    private function createSanitizer(): SanitizerInterface
    {
        return Sanitizer::create(['extensions' => ['basic', 'code', 'image', 'list', 'table', 'iframe', 'extra']]);
    }
}
