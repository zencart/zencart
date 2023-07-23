<?php

/*
 * This file is part of the HTML sanitizer project.
 *
 * (c) Titouan Galopin <galopintitouan@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Tests\HtmlSanitizer\Sanitizer;

use HtmlSanitizer\Sanitizer\UrlSanitizerTrait;
use PHPUnit\Framework\TestCase;

class UrlSanitizerTraitTest extends TestCase
{
    use UrlSanitizerTrait;

    public function provideSanitizeUrls()
    {
        // Simple accepted cases
        yield [
            'input' => 'https://trusted.com/link.php',
            'allowedSchemes' => ['https'],
            'allowedHosts' => null,
            'forceHttps' => false,
            'output' => 'https://trusted.com/link.php',
        ];

        yield [
            'input' => 'https://trusted.com/link.php',
            'allowedSchemes' => ['https'],
            'allowedHosts' => ['trusted.com'],
            'forceHttps' => false,
            'output' => 'https://trusted.com/link.php',
        ];

        yield [
            'input' => 'http://trusted.com/link.php',
            'allowedSchemes' => ['http'],
            'allowedHosts' => ['trusted.com'],
            'forceHttps' => false,
            'output' => 'http://trusted.com/link.php',
        ];

        yield [
            'input' => 'data:text/plain;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7',
            'allowedSchemes' => ['data'],
            'allowedHosts' => null,
            'forceHttps' => false,
            'output' => 'data:text/plain;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7',
        ];

        // Simple filtered cases
        yield [
            'input' => 'ws://trusted.com/link.php',
            'allowedSchemes' => ['http'],
            'allowedHosts' => null,
            'forceHttps' => false,
            'output' => null,
        ];

        yield [
            'input' => 'ws://trusted.com/link.php',
            'allowedSchemes' => ['http'],
            'allowedHosts' => ['trusted.com'],
            'forceHttps' => false,
            'output' => null,
        ];

        yield [
            'input' => 'https://trusted.com/link.php',
            'allowedSchemes' => ['http'],
            'allowedHosts' => null,
            'forceHttps' => false,
            'output' => null,
        ];

        yield [
            'input' => 'https://untrusted.com/link.php',
            'allowedSchemes' => ['https'],
            'allowedHosts' => ['trusted.com'],
            'forceHttps' => false,
            'output' => null,
        ];

        yield [
            'input' => 'http://untrusted.com/link.php',
            'allowedSchemes' => ['http'],
            'allowedHosts' => ['trusted.com'],
            'forceHttps' => false,
            'output' => null,
        ];

        yield [
            'input' => 'data:text/plain;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7',
            'allowedSchemes' => ['http'],
            'allowedHosts' => null,
            'forceHttps' => false,
            'output' => null,
        ];

        yield [
            'input' => 'data:text/plain;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7',
            'allowedSchemes' => ['http'],
            'allowedHosts' => ['trusted.com'],
            'forceHttps' => false,
            'output' => null,
        ];

        // Allow null host (data scheme for instance)
        yield [
            'input' => 'data:text/plain;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7',
            'allowedSchemes' => ['http', 'https', 'data'],
            'allowedHosts' => ['trusted.com', null],
            'forceHttps' => false,
            'output' => 'data:text/plain;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7',
        ];

        // Force HTTPS
        yield [
            'input' => 'http://trusted.com/link.php',
            'allowedSchemes' => ['http', 'https'],
            'allowedHosts' => ['trusted.com'],
            'forceHttps' => true,
            'output' => 'https://trusted.com/link.php',
        ];

        yield [
            'input' => 'https://trusted.com/link.php',
            'allowedSchemes' => ['http', 'https'],
            'allowedHosts' => ['trusted.com'],
            'forceHttps' => true,
            'output' => 'https://trusted.com/link.php',
        ];

        yield [
            'input' => 'data:text/plain;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7',
            'allowedSchemes' => ['http', 'https', 'data'],
            'allowedHosts' => null,
            'forceHttps' => true,
            'output' => 'data:text/plain;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7',
        ];

        yield [
            'input' => 'data:text/plain;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7',
            'allowedSchemes' => ['http', 'https', 'data'],
            'allowedHosts' => ['trusted.com', null],
            'forceHttps' => true,
            'output' => 'data:text/plain;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7',
        ];

        // Domain matching
        yield [
            'input' => 'https://subdomain.trusted.com/link.php',
            'allowedSchemes' => ['http', 'https'],
            'allowedHosts' => ['trusted.com'],
            'forceHttps' => false,
            'output' => 'https://subdomain.trusted.com/link.php',
        ];

        yield [
            'input' => 'https://subdomain.trusted.com.untrusted.com/link.php',
            'allowedSchemes' => ['http', 'https'],
            'allowedHosts' => ['trusted.com'],
            'forceHttps' => false,
            'output' => null,
        ];

        yield [
            'input' => 'https://deep.subdomain.trusted.com/link.php',
            'allowedSchemes' => ['http', 'https'],
            'allowedHosts' => ['trusted.com'],
            'forceHttps' => false,
            'output' => 'https://deep.subdomain.trusted.com/link.php',
        ];

        yield [
            'input' => 'https://deep.subdomain.trusted.com.untrusted.com/link.php',
            'allowedSchemes' => ['http', 'https'],
            'allowedHosts' => ['trusted.com'],
            'forceHttps' => false,
            'output' => null,
        ];
    }

    /**
     * @dataProvider provideSanitizeUrls
     */
    public function testSanitizeUrl($input, $allowedSchemes, $allowedHosts, $forceHttps, $expected)
    {
        $this->assertEquals($expected, $this->sanitizeUrl($input, $allowedSchemes, $allowedHosts, $forceHttps));
    }
}
