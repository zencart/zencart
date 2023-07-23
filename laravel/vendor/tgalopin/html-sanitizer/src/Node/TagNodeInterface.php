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
 * Represents a tag node, which has attributes.
 *
 * @author Titouan Galopin <galopintitouan@gmail.com>
 */
interface TagNodeInterface extends NodeInterface
{
    /**
     * Return the value of this node given attribute.
     * Return null if the attribute does not exist.
     *
     * @param string $name
     *
     * @return null|string
     */
    public function getAttribute(string $name): ?string;

    /**
     * Set the value of this node given attribute.
     *
     * @param string $name
     * @param string $value
     */
    public function setAttribute(string $name, string $value);
}
