<?php

/*
 * This file is part of the HTML sanitizer project.
 *
 * (c) Titouan Galopin <galopintitouan@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace HtmlSanitizer\Visitor;

use HtmlSanitizer\Model\Cursor;
use HtmlSanitizer\Node\StyleNode;

/**
 * @author Titouan Galopin <galopintitouan@gmail.com>
 *
 * @internal
 */
class StyleNodeVisitor extends AbstractNodeVisitor implements NamedNodeVisitorInterface
{
    public function getSupportedNodeNames(): array
    {
        return ['style'];
    }

    public function supports(\DOMNode $domNode, Cursor $cursor): bool
    {
        return 'style' === $domNode->nodeName;
    }

    public function enterNode(\DOMNode $domNode, Cursor $cursor)
    {
        $node = new StyleNode($cursor->node);

        $cursor->node->addChild($node);
        $cursor->node = $node;
    }

    public function leaveNode(\DOMNode $domNode, Cursor $cursor)
    {
        $cursor->node = $cursor->node->getParent();
    }
}
