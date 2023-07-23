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

use HtmlSanitizer\Extension\Iframe\Sanitizer\IframeSrcSanitizer;
use PHPUnit\Framework\TestCase;

class IframeSrcSanitizerTest extends TestCase
{
    public function provideUrls()
    {
        // Simple cases
        yield [
            'allowedSchemes' => ['http', 'https'],
            'allowedHosts' => null,
            'allowRelativeLinks' => false,
            'forceHttps' => false,
            'input' => 'https://trusted.com/iframe.php',
            'output' => 'https://trusted.com/iframe.php',
        ];

        yield [
            'allowedSchemes' => ['http', 'https'],
            'allowedHosts' => ['trusted.com'],
            'allowRelativeLinks' => false,
            'forceHttps' => false,
            'input' => 'https://trusted.com/iframe.php',
            'output' => 'https://trusted.com/iframe.php',
        ];

        yield [
            'allowedSchemes' => ['http', 'https'],
            'allowedHosts' => ['trusted.com'],
            'allowRelativeLinks' => false,
            'forceHttps' => false,
            'input' => 'https://untrusted.com/iframe.php',
            'output' => null,
        ];

        yield [
            'allowedSchemes' => ['http', 'https'],
            'allowedHosts' => null,
            'allowRelativeLinks' => false,
            'forceHttps' => false,
            'input' => '/iframe.php',
            'output' => null,
        ];

        yield [
            'allowedSchemes' => ['http', 'https'],
            'allowedHosts' => null,
            'allowRelativeLinks' => true,
            'forceHttps' => false,
            'input' => '/iframe.php',
            'output' => '/iframe.php',
        ];

        // Force HTTPS
        yield [
            'allowedSchemes' => ['http', 'https'],
            'allowedHosts' => ['trusted.com'],
            'allowRelativeLinks' => false,
            'forceHttps' => true,
            'input' => 'http://trusted.com/iframe.php',
            'output' => 'https://trusted.com/iframe.php',
        ];

        // Data-URI not allowed
        yield [
            'allowedSchemes' => ['http', 'https'],
            'allowedHosts' => null,
            'allowRelativeLinks' => false,
            'forceHttps' => false,
            'input' => 'data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7',
            'output' => null,
        ];

        yield [
            'allowedSchemes' => ['http', 'https'],
            'allowedHosts' => null,
            'allowRelativeLinks' => false,
            'forceHttps' => true,
            'input' => 'data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7',
            'output' => null,
        ];

        yield [
            'allowedSchemes' => ['https'],
            'allowedHosts' => null,
            'allowRelativeLinks' => false,
            'forceHttps' => false,
            'input' => 'http://trusted.com/iframe.php',
            'output' => null,
        ];
    }

    /**
     * @dataProvider provideUrls
     */
    public function testSanitize($allowedSchemes, $allowedHosts, $allowRelativeLinks, $forceHttps, $input, $expected)
    {
        $this->assertSame($expected, (new IframeSrcSanitizer($allowedSchemes, $allowedHosts, $allowRelativeLinks, $forceHttps))->sanitize($input));
    }
}
