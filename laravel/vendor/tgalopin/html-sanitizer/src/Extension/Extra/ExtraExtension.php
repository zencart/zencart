<?php

/*
 * This file is part of the HTML sanitizer project.
 *
 * (c) Titouan Galopin <galopintitouan@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace HtmlSanitizer\Extension\Extra;

use HtmlSanitizer\Extension\ExtensionInterface;

/**
 * @author Titouan Galopin <galopintitouan@gmail.com>
 *
 * @final
 */
class ExtraExtension implements ExtensionInterface
{
    public function getName(): string
    {
        return 'extra';
    }

    public function createNodeVisitors(array $config = []): array
    {
        return [
            'abbr' => new NodeVisitor\AbbrNodeVisitor($config['tags']['abbr'] ?? []),
            'caption' => new NodeVisitor\CaptionNodeVisitor($config['tags']['caption'] ?? []),
            'hr' => new NodeVisitor\HrNodeVisitor($config['tags']['hr'] ?? []),
            'mark' => new NodeVisitor\MarkNodeVisitor($config['tags']['mark'] ?? []),
            'rp' => new NodeVisitor\RpNodeVisitor($config['tags']['rp'] ?? []),
            'rt' => new NodeVisitor\RtNodeVisitor($config['tags']['rt'] ?? []),
            'ruby' => new NodeVisitor\RubyNodeVisitor($config['tags']['ruby'] ?? []),
            'time' => new NodeVisitor\TimeNodeVisitor($config['tags']['time'] ?? []),
        ];
    }
}
