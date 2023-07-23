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

/**
 * A parser transforms a HTML string into a tree of DOMNode objects.
 */
interface ParserInterface
{
    /**
     * Parse a given string and returns a DOMNode tree.
     * This method must throw a ParsingFailedException if parsing failed in order for
     * the sanitizer to catch it and return an empty string.
     *
     * @param string $html
     *
     * @return \DOMNode
     *
     * @throws ParsingFailedException When the parsing fails.
     */
    public function parse(string $html): \DOMNode;
}
