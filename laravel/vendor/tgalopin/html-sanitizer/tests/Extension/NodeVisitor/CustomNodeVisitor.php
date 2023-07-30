<?php

/*
 * This file is part of the HTML sanitizer project.
 *
 * (c) Titouan Galopin <galopintitouan@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Tests\HtmlSanitizer\Extension\NodeVisitor;

use HtmlSanitizer\Model\Cursor;
use HtmlSanitizer\Node\NodeInterface;
use HtmlSanitizer\Visitor\AbstractNodeVisitor;
use HtmlSanitizer\Visitor\HasChildrenNodeVisitorTrait;
use Tests\HtmlSanitizer\Extension\Node\CustomNode;

/**
 * @author Titouan Galopin <galopintitouan@gmail.com>
 */
class CustomNodeVisitor extends AbstractNodeVisitor
{
    use HasChildrenNodeVisitorTrait;

    protected function getDomNodeName(): string
    {
        return 'custom';
    }

    public function getDefaultAllowedAttributes(): array
    {
        return [
            'class', 'width', 'height',
        ];
    }

    public function getDefaultConfiguration(): array
    {
        return [
            'custom_data' => null,
        ];
    }

    protected function createNode(\DOMNode $domNode, Cursor $cursor): NodeInterface
    {
        $node = new CustomNode($cursor->node);
        $node->setAttribute('data-custom', $this->config['custom_data']);

        return $node;
    }
}
