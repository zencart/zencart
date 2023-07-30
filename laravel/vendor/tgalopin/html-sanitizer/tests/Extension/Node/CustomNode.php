<?php

/*
 * This file is part of the HTML sanitizer project.
 *
 * (c) Titouan Galopin <galopintitouan@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Tests\HtmlSanitizer\Extension\Node;

use HtmlSanitizer\Node\AbstractTagNode;
use HtmlSanitizer\Node\HasChildrenTrait;

/**
 * @author Titouan Galopin <galopintitouan@gmail.com>
 */
class CustomNode extends AbstractTagNode
{
    use HasChildrenTrait;

    public function getTagName(): string
    {
        return 'custom';
    }
}
