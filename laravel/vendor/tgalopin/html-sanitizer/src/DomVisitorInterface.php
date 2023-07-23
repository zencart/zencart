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

use HtmlSanitizer\Node\DocumentNode;

/**
 * Visit a parsed DOM node to create the equivalent sanitized DocumentNode.
 *
 * @author Titouan Galopin <galopintitouan@gmail.com>
 */
interface DomVisitorInterface
{
    public function visit(\DOMNode $node): DocumentNode;
}
