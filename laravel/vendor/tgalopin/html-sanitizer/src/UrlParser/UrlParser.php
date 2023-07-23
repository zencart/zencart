<?php

/*
 * This file is part of the HTML sanitizer project.
 *
 * (c) Titouan Galopin <galopintitouan@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace HtmlSanitizer\UrlParser;

use function League\Uri\parse;
use League\Uri\Exception;

/**
 * @author Titouan Galopin <galopintitouan@gmail.com>
 *
 * @internal
 */
class UrlParser
{
    public function parse(string $url): ?array
    {
        if (!$url) {
            return null;
        }

        try {
            $parsed = parse($url);
        } catch (Exception $e) {
            return null;
        }

        return $parsed;
    }
}
