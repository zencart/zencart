<?php

/*
 * This file is part of the HTML sanitizer project.
 *
 * (c) Titouan Galopin <galopintitouan@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace HtmlSanitizer\Extension\Image\Sanitizer;

use HtmlSanitizer\Sanitizer\UrlSanitizerTrait;

/**
 * @internal
 */
class ImgSrcSanitizer
{
    use UrlSanitizerTrait;

    private $allowedSchemes;
    private $allowedHosts;
    private $allowDataUri;
    private $allowRelativeLinks;
    private $forceHttps;

    public function __construct(array $allowedSchemes, ?array $allowedHosts, bool $allowDataUri, bool $allowRelativeLinks, bool $forceHttps)
    {
        $this->allowedSchemes = $allowedSchemes;
        $this->allowedHosts = $allowedHosts;
        $this->allowDataUri = $allowDataUri;
        $this->allowRelativeLinks = $allowRelativeLinks;
        $this->forceHttps = $forceHttps;
    }

    public function sanitize(?string $input): ?string
    {
        $allowedSchemes = $this->allowedSchemes;
        $allowedHosts = $this->allowedHosts;

        if ($this->allowDataUri && !$this->allowRelativeLinks) {
            $allowedSchemes[] = 'data';
            if (null !== $allowedHosts) {
                $allowedHosts[] = null;
            }
        }

        if ($this->allowRelativeLinks) {
            $allowedSchemes[] = null;
            if (null !== $allowedHosts) {
                $allowedHosts[] = null;
            }
        }

        if (!$sanitized = $this->sanitizeUrl($input, $allowedSchemes, $allowedHosts, $this->forceHttps)) {
            return null;
        }

        // Allow only images in data URIs
        if (0 === strpos($sanitized, 'data:') && 0 !== strpos($sanitized, 'data:image/')) {
            return null;
        }

        return $sanitized;
    }
}
