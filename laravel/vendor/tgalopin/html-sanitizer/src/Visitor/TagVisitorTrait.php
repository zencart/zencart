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

/**
 * @author Titouan Galopin <galopintitouan@gmail.com>
 *
 * @internal
 */
trait TagVisitorTrait
{
    abstract protected function getDomNodeName(): string;

    public function getSupportedNodeNames(): array
    {
        return [$this->getDomNodeName()];
    }

    public function supports(\DOMNode $domNode, Cursor $cursor): bool
    {
        return $this->getDomNodeName() === $domNode->nodeName;
    }

    /**
     * Set attributes from a DOM node to a sanitized node.
     *
     * @param \DOMNode         $domNode
     * @param TagNodeInterface $node
     * @param array            $allowedAttributes
     */
    private function setAttributes(\DOMNode $domNode, TagNodeInterface $node, array $allowedAttributes = [])
    {
        if (!\count($domNode->attributes)) {
            return;
        }

        /** @var \DOMAttr $attribute */
        foreach ($domNode->attributes as $attribute) {
            $name = strtolower($attribute->name);

            if (\in_array($name, $allowedAttributes, true)) {
                $node->setAttribute($name, $attribute->value);
            }
        }
    }

    /**
     * Read the value of a DOMNode attribute.
     *
     * @param \DOMNode $domNode
     * @param string   $name
     *
     * @return null|string
     */
    private function getAttribute(\DOMNode $domNode, string $name): ?string
    {
        if (!\count($domNode->attributes)) {
            return null;
        }

        /** @var \DOMAttr $attribute */
        foreach ($domNode->attributes as $attribute) {
            if ($attribute->name === $name) {
                return $attribute->value;
            }
        }

        return null;
    }
}
