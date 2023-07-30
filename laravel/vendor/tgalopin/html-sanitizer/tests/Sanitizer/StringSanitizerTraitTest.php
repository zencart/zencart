<?php

/*
 * This file is part of the HTML sanitizer project.
 *
 * (c) Titouan Galopin <galopintitouan@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Tests\HtmlSanitizer\Sanitizer;

use HtmlSanitizer\Sanitizer\StringSanitizerTrait;
use PHPUnit\Framework\TestCase;

class StringSanitizerTraitTest extends TestCase
{
    use StringSanitizerTrait;

    public function provideEncodeHtmlEntites()
    {
        $entities = [
            '' => '',
            '"' => '&#34;',
            '\'' => '&#039;',
            '&' => '&amp;',
            '<' => '&lt;',
            '>' => '&gt;',
            '&lt;' => '&amp;lt;',
            '&gt;' => '&amp;gt;',
            '+' => '&#43;',
            '=' => '&#61;',
            '@' => '&#64;',
            '`' => '&#96;',
        ];

        foreach ($entities as $input => $expected) {
            yield $input => [$input, $expected];
        }
    }

    /**
     * @dataProvider provideEncodeHtmlEntites
     */
    public function testEncodeHtmlEntites($input, $expected)
    {
        $this->assertEquals($expected, $this->encodeHtmlEntities($input));
    }
}
