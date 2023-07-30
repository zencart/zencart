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

use HtmlSanitizer\Model\Cursor;
use HtmlSanitizer\Node\DocumentNode;
use HtmlSanitizer\Node\TextNode;
use HtmlSanitizer\Visitor\NamedNodeVisitorInterface;
use HtmlSanitizer\Visitor\NodeVisitorInterface;

/**
 * The DomVisitor iterate over the parsed DOM tree and visit nodes using NodeVisitorInterface objects.
 * For performance reasons, these objects are split in 2 groups: generic ones and node-specific ones.
 *
 * @author Titouan Galopin <galopintitouan@gmail.com>
 *
 * @final
 */
class DomVisitor implements DomVisitorInterface
{
    /**
     * @var NodeVisitorInterface[]
     */
    private $genericNodeVisitors = [];

    /**
     * @var NamedNodeVisitorInterface[]
     */
    private $namedNodeVisitors = [];

    /**
     * @param NodeVisitorInterface[] $visitors
     */
    public function __construct(array $visitors = [])
    {
        foreach ($visitors as $visitor) {
            if ($visitor instanceof NamedNodeVisitorInterface) {
                foreach ($visitor->getSupportedNodeNames() as $nodeName) {
                    $this->namedNodeVisitors[$nodeName][] = $visitor;
                }

                continue;
            }

            $this->genericNodeVisitors[] = $visitor;
        }
    }

    public function visit(\DOMNode $node): DocumentNode
    {
        $cursor = new Cursor();
        $cursor->node = new DocumentNode();

        $this->visitNode($node, $cursor);

        return $cursor->node;
    }

    private function visitNode(\DOMNode $node, Cursor $cursor)
    {
        /** @var NodeVisitorInterface[] $supportedVisitors */
        $supportedVisitors = array_merge($this->namedNodeVisitors[$node->nodeName] ?? [], $this->genericNodeVisitors);

        foreach ($supportedVisitors as $visitor) {
            if ($visitor->supports($node, $cursor)) {
                $visitor->enterNode($node, $cursor);
            }
        }

        /** @var \DOMNode $child */
        foreach ($node->childNodes ?? [] as $k => $child) {
            if ('#text' === $child->nodeName) {
                // Add text in the safe tree without a visitor for performance
                $cursor->node->addChild(new TextNode($cursor->node, $child->nodeValue));
            } elseif (!$child instanceof \DOMText) {
                // Ignore comments for security reasons (interpreted differently by browsers)
                $this->visitNode($child, $cursor);
            }
        }

        foreach (array_reverse($supportedVisitors) as $visitor) {
            if ($visitor->supports($node, $cursor)) {
                $visitor->leaveNode($node, $cursor);
            }
        }
    }
}
