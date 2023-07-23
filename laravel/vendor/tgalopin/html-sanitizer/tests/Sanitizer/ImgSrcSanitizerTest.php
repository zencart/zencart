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

use HtmlSanitizer\Extension\Image\Sanitizer\ImgSrcSanitizer;
use PHPUnit\Framework\TestCase;

class ImgSrcSanitizerTest extends TestCase
{
    public function provideUrls()
    {
        // Simple cases
        yield [
            'allowedSchemes' => ['http', 'https'],
            'allowedHosts' => null,
            'allowDataUri' => false,
            'allowRelativeLinks' => false,
            'forceHttps' => false,
            'input' => 'https://trusted.com/image.php',
            'output' => 'https://trusted.com/image.php',
        ];

        yield [
            'allowedSchemes' => ['http', 'https'],
            'allowedHosts' => ['trusted.com'],
            'allowDataUri' => false,
            'allowRelativeLinks' => false,
            'forceHttps' => false,
            'input' => 'https://trusted.com/image.php',
            'output' => 'https://trusted.com/image.php',
        ];

        yield [
            'allowedSchemes' => ['http', 'https'],
            'allowedHosts' => ['trusted.com'],
            'allowDataUri' => false,
            'allowRelativeLinks' => false,
            'forceHttps' => false,
            'input' => 'https://untrusted.com/image.php',
            'output' => null,
        ];

        yield [
            'allowedSchemes' => ['http', 'https'],
            'allowedHosts' => null,
            'allowDataUri' => false,
            'allowRelativeLinks' => false,
            'forceHttps' => false,
            'input' => '/image.php',
            'output' => null,
        ];

        yield [
            'allowedSchemes' => ['http', 'https'],
            'allowedHosts' => null,
            'allowDataUri' => true,
            'allowRelativeLinks' => false,
            'forceHttps' => false,
            'input' => '/image.php',
            'output' => null,
        ];

        yield [
            'allowedSchemes' => ['http', 'https'],
            'allowedHosts' => null,
            'allowDataUri' => false,
            'allowRelativeLinks' => true,
            'forceHttps' => false,
            'input' => '/image.php',
            'output' => '/image.php',
        ];

        // Force HTTPS
        yield [
            'allowedSchemes' => ['http', 'https'],
            'allowedHosts' => ['trusted.com'],
            'allowDataUri' => false,
            'allowRelativeLinks' => false,
            'forceHttps' => true,
            'input' => 'http://trusted.com/image.php',
            'output' => 'https://trusted.com/image.php',
        ];

        // Data-URI
        yield [
            'allowedSchemes' => ['http', 'https'],
            'allowedHosts' => null,
            'allowDataUri' => false,
            'allowRelativeLinks' => false,
            'forceHttps' => false,
            'input' => 'data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7',
            'output' => null,
        ];

        yield [
            'allowedSchemes' => ['http', 'https'],
            'allowedHosts' => null,
            'allowDataUri' => true,
            'allowRelativeLinks' => false,
            'forceHttps' => false,
            'input' => 'data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7',
            'output' => 'data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7',
        ];

        yield [
            'allowedSchemes' => ['http', 'https'],
            'allowedHosts' => ['trusted.com'],
            'allowDataUri' => true,
            'allowRelativeLinks' => false,
            'forceHttps' => false,
            'input' => 'data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7',
            'output' => 'data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7',
        ];

        yield [
            'allowedSchemes' => ['http', 'https'],
            'allowedHosts' => null,
            'allowDataUri' => true,
            'allowRelativeLinks' => false,
            'forceHttps' => false,
            'input' => 'data://image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7',
            'output' => null,
        ];

        yield [
            'allowedSchemes' => ['http', 'https'],
            'allowedHosts' => null,
            'allowDataUri' => true,
            'allowRelativeLinks' => false,
            'forceHttps' => false,
            'input' => 'data:',
            'output' => null,
        ];

        yield [
            'allowedSchemes' => ['http', 'https'],
            'allowedHosts' => null,
            'allowDataUri' => true,
            'allowRelativeLinks' => false,
            'forceHttps' => false,
            'input' => 'data:text/plain;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7',
            'output' => null,
        ];

        yield [
            'allowedSchemes' => ['https'],
            'allowedHosts' => null,
            'allowDataUri' => false,
            'allowRelativeLinks' => false,
            'forceHttps' => false,
            'input' => 'http://trusted.com/image.php',
            'output' => null,
        ];
    }

    /**
     * @dataProvider provideUrls
     */
    public function testSanitize($allowedSchemes, $allowedHosts, $allowDataUri, $allowRelativeLinks, $forceHttps, $input, $expected)
    {
        $this->assertSame($expected, (new ImgSrcSanitizer($allowedSchemes, $allowedHosts, $allowDataUri, $allowRelativeLinks, $forceHttps))->sanitize($input));
    }
}
