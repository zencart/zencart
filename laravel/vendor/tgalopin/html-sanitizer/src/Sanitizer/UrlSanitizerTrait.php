<?php

/*
 * This file is part of the HTML sanitizer project.
 *
 * (c) Titouan Galopin <galopintitouan@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace HtmlSanitizer\Sanitizer;

use HtmlSanitizer\UrlParser\UrlParser;
use function League\Uri\build;

/**
 * @internal
 */
trait UrlSanitizerTrait
{
    /**
     * @var UrlParser|null
     */
    private $parser;

    private function sanitizeUrl(?string $input, array $allowedSchemes, ?array $allowedHosts, bool $forceHttps = false): ?string
    {
        if (!$input) {
            return null;
        }

        if (!$this->parser) {
            $this->parser = new UrlParser();
        }

        $url = $this->parser->parse($input);

        // Malformed URL
        if (!\is_array($url) || !$url) {
            return null;
        }

        // Invalid scheme
        if (!\in_array($url['scheme'], $allowedSchemes, true)) {
            return null;
        }

        // Invalid host
        if (null !== $allowedHosts && !$this->isAllowedHost($url['host'], $allowedHosts)) {
            return null;
        }

        // Force HTTPS
        if ($forceHttps && 'http' === $url['scheme']) {
            $url['scheme'] = 'https';
        }

        return build($url);
    }

    private function isAllowedHost(?string $host, array $allowedHosts): bool
    {
        if (null === $host) {
            return \in_array(null, $allowedHosts, true);
        }

        $parts = array_reverse(explode('.', $host));

        foreach ($allowedHosts as $allowedHost) {
            if ($this->matchAllowedHostParts($parts, array_reverse(explode('.', $allowedHost)))) {
                return true;
            }
        }

        return false;
    }

    private function matchAllowedHostParts(array $uriParts, array $trustedParts): bool
    {
        // Check each chunk of the domain is valid
        foreach ($trustedParts as $key => $trustedPart) {
            if ($uriParts[$key] !== $trustedPart) {
                return false;
            }
        }

        return true;
    }
}
