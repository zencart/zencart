<?php

/*
 * This file is part of the HTML sanitizer project.
 *
 * (c) Titouan Galopin <galopintitouan@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace HtmlSanitizer\Extension\Iframe;

use HtmlSanitizer\Extension\ExtensionInterface;

/**
 * @author Titouan Galopin <galopintitouan@gmail.com>
 *
 * @final
 */
class IframeExtension implements ExtensionInterface
{
    public function getName(): string
    {
        return 'iframe';
    }

    public function createNodeVisitors(array $config = []): array
    {
        return [
            'iframe' => new NodeVisitor\IframeNodeVisitor($config['tags']['iframe'] ?? []),
        ];
    }
}
