<?php

/*
 * This file is part of the HTML sanitizer project.
 *
 * (c) Titouan Galopin <galopintitouan@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace HtmlSanitizer\Extension\Listing\Node;

use HtmlSanitizer\Node\AbstractTagNode;
use HtmlSanitizer\Node\HasChildrenTrait;

/**
 * @author Titouan Galopin <galopintitouan@gmail.com>
 *
 * @final
 */
class OlNode extends AbstractTagNode
{
    use HasChildrenTrait;

    public function getTagName(): string
    {
        return 'ol';
    }
}
