<?php

/*
 * This file is part of the HTML sanitizer project.
 *
 * (c) Titouan Galopin <galopintitouan@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace HtmlSanitizer\Extension\Details\NodeVisitor;

use HtmlSanitizer\Extension\Details\Node\DetailsNode;
use HtmlSanitizer\Model\Cursor;
use HtmlSanitizer\Node\NodeInterface;
use HtmlSanitizer\Visitor\AbstractNodeVisitor;
use HtmlSanitizer\Visitor\HasChildrenNodeVisitorTrait;
use HtmlSanitizer\Visitor\NamedNodeVisitorInterface;

/**
 * @author Titouan Galopin <galopintitouan@gmail.com>
 *
 * @final
 */
class DetailsNodeVisitor extends AbstractNodeVisitor implements NamedNodeVisitorInterface
{
    use HasChildrenNodeVisitorTrait;

    protected function getDomNodeName(): string
    {
        return 'details';
    }

    public function getDefaultAllowedAttributes(): array
    {
        return ['open'];
    }

    protected function createNode(\DOMNode $domNode, Cursor $cursor): NodeInterface
    {
        $node = new DetailsNode($cursor->node);

        if (null !== $this->getAttribute($domNode, 'open')) {
            $node->setAttribute('open', 'open');
        }

        return $node;
    }
}
