<?php

/*
 * This file is part of the HTML sanitizer project.
 *
 * (c) Titouan Galopin <galopintitouan@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace HtmlSanitizer\Node;

/**
 * Special node to ignore styles and all their content.
 *
 * @author Titouan Galopin <galopintitouan@gmail.com>
 *
 * @internal
 */
class StyleNode extends AbstractTagNode
{
    use IsChildlessTrait;

    public function getTagName(): string
    {
        return 'style';
    }

    public function render(): string
    {
        return '';
    }
}
