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

/**
 * @author Titouan Galopin <galopintitouan@gmail.com>
 */
abstract class AbstractNodeVisitor implements NodeVisitorInterface
{
    /**
     * @var array
     */
    protected $config;

    public function __construct(array $config = [])
    {
        $default = $this->getDefaultConfiguration();
        $default['allowed_attributes'] = $this->getDefaultAllowedAttributes();

        $this->config = array_merge($default, $config);
    }

    /**
     * Return this visitor default allowed attributes and their filters.
     *
     * @return array
     */
    public function getDefaultAllowedAttributes(): array
    {
        return [];
    }

    /**
     * Return this visitor additional default configuration.
     * Can only be of depth 1 as it will be merged with the one provided by the user.
     *
     * @return array
     */
    public function getDefaultConfiguration(): array
    {
        return [];
    }

    public function enterNode(\DOMNode $domNode, Cursor $cursor)
    {
    }

    public function leaveNode(\DOMNode $domNode, Cursor $cursor)
    {
    }
}
