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
use HtmlSanitizer\Node\TagNodeInterface;
use HtmlSanitizer\Node\NodeInterface;

/**
 * @author Titouan Galopin <galopintitouan@gmail.com>
 */
trait HasChildrenNodeVisitorTrait
{
    use TagVisitorTrait;

    abstract protected function createNode(\DOMNode $domNode, Cursor $cursor): NodeInterface;

    public function enterNode(\DOMNode $domNode, Cursor $cursor)
    {
        $node = $this->createNode($domNode, $cursor);
        if ($node instanceof TagNodeInterface && isset($this->config['allowed_attributes'])) {
            $this->setAttributes($domNode, $node, $this->config['allowed_attributes']);
        }

        $cursor->node->addChild($node);
        $cursor->node = $node;
    }

    public function leaveNode(\DOMNode $domNode, Cursor $cursor)
    {
        $cursor->node = $cursor->node->getParent();
    }
}
