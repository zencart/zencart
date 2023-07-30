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

use HtmlSanitizer\Sanitizer\StringSanitizerTrait;

/**
 * @author Titouan Galopin <galopintitouan@gmail.com>
 *
 * @internal
 */
class TextNode extends AbstractNode
{
    use IsChildlessTrait;
    use StringSanitizerTrait;

    private $text;

    public function __construct(NodeInterface $parent, string $text)
    {
        parent::__construct($parent);

        $this->text = $text;
    }

    public function render(): string
    {
        return $this->encodeHtmlEntities($this->text);
    }
}
