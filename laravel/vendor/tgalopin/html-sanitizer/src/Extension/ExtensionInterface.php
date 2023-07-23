<?php

/*
 * This file is part of the HTML sanitizer project.
 *
 * (c) Titouan Galopin <galopintitouan@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace HtmlSanitizer\Extension;

use HtmlSanitizer\Visitor\NodeVisitorInterface;

/**
 * A sanitizer extension allows to easily add features to the sanitizer to handle specific tags.
 *
 * @author Titouan Galopin <galopintitouan@gmail.com>
 */
interface ExtensionInterface
{
    /**
     * Return this extension name, which will be used in the sanitizer configuration.
     */
    public function getName(): string;

    /**
     * Return a list of node visitors to register in the sanitizer following the format tagName => visitor.
     * For instance: 'strong' => new StrongVisitor($config).
     *
     * @param array $config The configuration given by the user of the library.
     *
     * @return NodeVisitorInterface[]
     */
    public function createNodeVisitors(array $config = []): array;
}
