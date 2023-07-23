<?php

/*
 * This file is part of the HTML sanitizer project.
 *
 * (c) Titouan Galopin <galopintitouan@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace HtmlSanitizer\Extension\Iframe\Sanitizer;

use HtmlSanitizer\Sanitizer\UrlSanitizerTrait;

/**
 * @internal
 */
class IframeSrcSanitizer
{
    use UrlSanitizerTrait;

    private $allowedSchemes;
    private $allowedHosts;
    private $allowRelativeLinks;
    private $forceHttps;

    public function __construct(array $allowedSchemes, ?array $allowedHosts, bool $allowRelativeLinks, bool $forceHttps)
    {
        $this->allowedSchemes = $allowedSchemes;
        $this->allowedHosts = $allowedHosts;
        $this->allowRelativeLinks = $allowRelativeLinks;
        $this->forceHttps = $forceHttps;
    }

    public function sanitize(?string $input): ?string
    {
        $allowedSchemes = $this->allowedSchemes;
        $allowedHosts = $this->allowedHosts;

        if ($this->allowRelativeLinks) {
            $allowedSchemes[] = null;

            if (\is_array($this->allowedHosts)) {
                $allowedHosts[] = null;
            }
        }

        return $this->sanitizeUrl($input, $allowedSchemes, $allowedHosts, $this->forceHttps);
    }
}
