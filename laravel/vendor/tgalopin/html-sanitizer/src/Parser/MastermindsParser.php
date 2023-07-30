<?php

/*
 * This file is part of the HTML sanitizer project.
 *
 * (c) Titouan Galopin <galopintitouan@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace HtmlSanitizer\Parser;

use HtmlSanitizer\Exception\ParsingFailedException;
use Masterminds\HTML5;

/**
 * @author Titouan Galopin <galopintitouan@gmail.com>
 *
 * @final
 */
class MastermindsParser implements ParserInterface
{
    public function parse(string $html): \DOMNode
    {
        try {
            return (new HTML5())->loadHTMLFragment($html);
        } catch (\Throwable $t) {
            throw new ParsingFailedException($this, $t);
        }
    }
}
