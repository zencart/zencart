<?php

/*
 * This file is part of the HTML sanitizer project.
 *
 * (c) Titouan Galopin <galopintitouan@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace HtmlSanitizer\Extension\Listing;

use HtmlSanitizer\Extension\ExtensionInterface;

/**
 * @author Titouan Galopin <galopintitouan@gmail.com>
 *
 * @final
 */
class ListExtension implements ExtensionInterface
{
    public function getName(): string
    {
        return 'list';
    }

    public function createNodeVisitors(array $config = []): array
    {
        return [
            'dd' => new NodeVisitor\DdNodeVisitor($config['tags']['dd'] ?? []),
            'dl' => new NodeVisitor\DlNodeVisitor($config['tags']['dl'] ?? []),
            'dt' => new NodeVisitor\DtNodeVisitor($config['tags']['dt'] ?? []),
            'li' => new NodeVisitor\LiNodeVisitor($config['tags']['li'] ?? []),
            'ol' => new NodeVisitor\OlNodeVisitor($config['tags']['ol'] ?? []),
            'ul' => new NodeVisitor\UlNodeVisitor($config['tags']['ul'] ?? []),
        ];
    }
}
