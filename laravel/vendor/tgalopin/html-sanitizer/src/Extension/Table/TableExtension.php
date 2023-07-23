<?php

/*
 * This file is part of the HTML sanitizer project.
 *
 * (c) Titouan Galopin <galopintitouan@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace HtmlSanitizer\Extension\Table;

use HtmlSanitizer\Extension\ExtensionInterface;

/**
 * @author Titouan Galopin <galopintitouan@gmail.com>
 *
 * @final
 */
class TableExtension implements ExtensionInterface
{
    public function getName(): string
    {
        return 'table';
    }

    public function createNodeVisitors(array $config = []): array
    {
        return [
            'table' => new NodeVisitor\TableNodeVisitor($config['tags']['table'] ?? []),
            'tbody' => new NodeVisitor\TbodyNodeVisitor($config['tags']['tbody'] ?? []),
            'td' => new NodeVisitor\TdNodeVisitor($config['tags']['td'] ?? []),
            'tfoot' => new NodeVisitor\TfootNodeVisitor($config['tags']['tfoot'] ?? []),
            'thead' => new NodeVisitor\TheadNodeVisitor($config['tags']['thead'] ?? []),
            'th' => new NodeVisitor\ThNodeVisitor($config['tags']['th'] ?? []),
            'tr' => new NodeVisitor\TrNodeVisitor($config['tags']['tr'] ?? []),
        ];
    }
}
