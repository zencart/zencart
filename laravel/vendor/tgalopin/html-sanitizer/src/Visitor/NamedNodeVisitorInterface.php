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

/**
 * A named node visitor is a node visitor targeted at specific node types.
 * Named node visitors are useful to improve the sanitizer performance by being able to resolve
 * visitors quickly for a given DOM node.
 *
 * @author Titouan Galopin <galopintitouan@gmail.com>
 */
interface NamedNodeVisitorInterface extends NodeVisitorInterface
{
    /**
     * Return the list of DOM nodes names supported by this visitor.
     * It will be called only on these specific DOM nodes.
     *
     * This method will be called before the `support` method of NodeVisitorInterface.
     *
     * @return array
     */
    public function getSupportedNodeNames(): array;
}
