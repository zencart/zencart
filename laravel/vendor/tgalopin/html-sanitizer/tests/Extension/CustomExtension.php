<?php

/*
 * This file is part of the HTML sanitizer project.
 *
 * (c) Titouan Galopin <galopintitouan@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Tests\HtmlSanitizer\Extension;

use HtmlSanitizer\Extension\ExtensionInterface;

/**
 * @author Titouan Galopin <galopintitouan@gmail.com>
 */
class CustomExtension implements ExtensionInterface
{
    public function getName(): string
    {
        return 'custom';
    }

    public function createNodeVisitors(array $config = []): array
    {
        return [
            'custom' => new NodeVisitor\CustomNodeVisitor($config['tags']['custom'] ?? []),
        ];
    }
}
