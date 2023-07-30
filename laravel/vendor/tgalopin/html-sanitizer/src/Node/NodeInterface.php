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
 * Represents a node of the sanitized tree.
 *
 * @author Titouan Galopin <galopintitouan@gmail.com>
 */
interface NodeInterface
{
    /**
     * Return this node's parent node if it has one.
     *
     * @return NodeInterface|null
     */
    public function getParent(): ?NodeInterface;

    /**
     * Add a child to this node.
     *
     * @param NodeInterface $node
     */
    public function addChild(NodeInterface $node);

    /**
     * Render this node as a string.
     *
     * @return string
     */
    public function render(): string;
}
