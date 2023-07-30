<?php

/*
 * This file is part of the HTML sanitizer project.
 *
 * (c) Titouan Galopin <galopintitouan@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace HtmlSanitizer\Sanitizer;

/**
 * @internal
 */
trait StringSanitizerTrait
{
    private static $replacements = [
        // "&#34;" is shorter than "&quot;"
        '&quot;' => '&#34;',

        // Fix several potential issues in how browsers intepret attributes values
        '+' => '&#43;',
        '=' => '&#61;',
        '@' => '&#64;',
        '`' => '&#96;',

        // Some DB engines will transform UTF8 full-width characters their classical version
        // if the data is saved in a non-UTF8 field
        '＜' => '&#xFF1C;',
        '＞' => '&#xFF1E;',
        '＋' => '&#xFF0B;',
        '＝' => '&#xFF1D;',
        '＠' => '&#xFF20;',
        '｀' => '&#xFF40;',
    ];

    public function encodeHtmlEntities(string $string): string
    {
        $string = htmlspecialchars($string, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $string = str_replace(array_keys(self::$replacements), array_values(self::$replacements), $string);

        return $string;
    }
}
