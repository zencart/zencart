<?php

/*
 * This file is part of the HTML sanitizer project.
 *
 * (c) Titouan Galopin <galopintitouan@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace HtmlSanitizer\Extension\Image;

use HtmlSanitizer\Extension\ExtensionInterface;

/**
 * @author Titouan Galopin <galopintitouan@gmail.com>
 *
 * @final
 */
class ImageExtension implements ExtensionInterface
{
    public function getName(): string
    {
        return 'image';
    }

    public function createNodeVisitors(array $config = []): array
    {
        return [
            'img' => new NodeVisitor\ImgNodeVisitor($config['tags']['img'] ?? []),
        ];
    }
}
