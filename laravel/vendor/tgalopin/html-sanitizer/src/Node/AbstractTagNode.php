<?php

/*
 * This file is part of the HTML sanitizer project.
 *
 * (c) Titouan Galopin <galopintitouan@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace HtmlSanitizer\Node;

use HtmlSanitizer\Sanitizer\StringSanitizerTrait;

/**
 * Abstract base class for tags.
 *
 * @author Titouan Galopin <galopintitouan@gmail.com>
 */
abstract class AbstractTagNode extends AbstractNode implements TagNodeInterface
{
    use StringSanitizerTrait;

    private $attributes = [];

    /**
     * Return this tag name (used to render it).
     *
     * @return string
     */
    abstract public function getTagName(): string;

    public function getAttribute(string $name): ?string
    {
        return $this->attributes[$name] ?? null;
    }

    public function setAttribute(string $name, ?string $value)
    {
        // Always use only the first declaration (ease sanitization)
        if (!array_key_exists($name, $this->attributes)) {
            $this->attributes[$name] = $value;
        }
    }

    public function render(): string
    {
        $tag = $this->getTagName();

        if (method_exists($this, 'renderChildren')) {
            return '<'.$tag.$this->renderAttributes().'>'.$this->renderChildren().'</'.$tag.'>';
        }

        return '<'.$tag.$this->renderAttributes().' />';
    }

    protected function renderAttributes(): string
    {
        $rendered = [];
        foreach ($this->attributes as $name => $value) {
            if (null === $value) {
                // Tag should be removed as a sanitizer found suspect data inside
                continue;
            }

            $attr = $this->encodeHtmlEntities($name);
            if ('' !== $value) {
                // In quirks mode, IE8 does a poor job producing innerHTML values.
                // If JavaScript does:
                //      nodeA.innerHTML = nodeB.innerHTML;
                // and nodeB contains (or even if ` was encoded properly):
                //      <div attr="``foo=bar">
                // then IE8 will produce:
                //      <div attr=``foo=bar>
                // as the value of nodeB.innerHTML and assign it to nodeA.
                // IE8's HTML parser treats `` as a blank attribute value and foo=bar becomes a separate attribute.
                // Adding a space at the end of the attribute prevents this by forcing IE8 to put double
                // quotes around the attribute when computing nodeB.innerHTML.
                if (false !== mb_strpos($value, '`')) {
                    $value .= ' ';
                }

                $attr .= '="'.$this->encodeHtmlEntities($value).'"';
            }

            $rendered[] = $attr;
        }

        return $rendered ? ' '.implode(' ', $rendered) : '';
    }
}
