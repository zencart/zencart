<?php

/*
 * This file is part of the HTML sanitizer project.
 *
 * (c) Titouan Galopin <galopintitouan@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace HtmlSanitizer\Util;

/**
 * @author Titouan Galopin <galopintitouan@gmail.com>
 *
 * @final
 */
class Dumper
{
    private static $id;

    public static function dumpDomTree(\DOMNode $tree)
    {
        echo "\ndigraph G {\n";

        self::$id = 0;
        self::dumpDomNode($tree);

        echo "}\n";
    }

    private static function dumpDomNode(\DOMNode $node): string
    {
        ++self::$id;

        $name = self::$id.'-'.$node->nodeName;
        echo '    "'.$name."\";\n";

        /** @var \DOMNode $child */
        foreach ($node->childNodes ?: [] as $child) {
            $childName = self::dumpDomNode($child);
            echo '    "'.$name.'" -> "'.$childName."\";\n";
        }

        return $name;
    }
}
