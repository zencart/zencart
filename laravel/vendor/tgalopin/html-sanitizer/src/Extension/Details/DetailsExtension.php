<?php

/*
 * This file is part of the HTML sanitizer project.
 *
 * (c) Titouan Galopin <galopintitouan@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace HtmlSanitizer\Extension\Details;

use HtmlSanitizer\Extension\ExtensionInterface;

/**
 * @author Titouan Galopin <galopintitouan@gmail.com>
 *
 * @final
 */
class DetailsExtension implements ExtensionInterface
{
    public function getName(): string
    {
        return 'details';
    }

    public function createNodeVisitors(array $config = []): array
    {
        return [
            'details' => new NodeVisitor\DetailsNodeVisitor($config['tags']['details'] ?? []),
            'summary' => new NodeVisitor\SummaryNodeVisitor($config['tags']['summary'] ?? []),
        ];
    }
}
