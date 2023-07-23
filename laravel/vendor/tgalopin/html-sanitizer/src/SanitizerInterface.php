<?php

/*
 * This file is part of the HTML sanitizer project.
 *
 * (c) Titouan Galopin <galopintitouan@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace HtmlSanitizer;

/**
 * Purify a given untrusted HTML source string to return a trustable one.
 * This usually includes removal of all the sources of XSS, and may also includes
 * additional protections like images sources and links targets filters.
 *
 * @author Titouan Galopin <galopintitouan@gmail.com>
 */
interface SanitizerInterface
{
    public function sanitize(string $html): string;
}
