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

use HtmlSanitizer\Extension\Basic\Sanitizer\AHrefSanitizer;
use PHPUnit\Framework\TestCase;

class AHrefSanitizerTest extends TestCase
{
    public function provideUrls()
    {
        // Simple cases
        yield [
            'allowedSchemes' => ['http', 'https'],
            'allowedHosts' => null,
            'allowMailTo' => false,
            'allowRelativeLinks' => false,
            'forceHttps' => false,
            'input' => 'https://trusted.com/link.php',
            'output' => 'https://trusted.com/link.php',
        ];

        yield [
            'allowedSchemes' => ['http', 'https'],
            'allowedHosts' => ['trusted.com'],
            'allowMailTo' => false,
            'allowRelativeLinks' => false,
            'forceHttps' => false,
            'input' => 'https://trusted.com/link.php',
            'output' => 'https://trusted.com/link.php',
        ];

        yield [
            'allowedSchemes' => ['http', 'https'],
            'allowedHosts' => ['trusted.com'],
            'allowMailTo' => false,
            'allowRelativeLinks' => false,
            'forceHttps' => false,
            'input' => 'https://untrusted.com/link.php',
            'output' => null,
        ];

        yield [
            'allowedSchemes' => ['http', 'https'],
            'allowedHosts' => null,
            'allowMailTo' => false,
            'allowRelativeLinks' => false,
            'forceHttps' => false,
            'input' => '/link.php',
            'output' => null,
        ];

        yield [
            'allowedSchemes' => ['http', 'https'],
            'allowedHosts' => null,
            'allowMailTo' => true,
            'allowRelativeLinks' => false,
            'forceHttps' => false,
            'input' => '/link.php',
            'output' => null,
        ];

        yield [
            'allowedSchemes' => ['http', 'https'],
            'allowedHosts' => null,
            'allowMailTo' => true,
            'allowRelativeLinks' => true,
            'forceHttps' => false,
            'input' => '/link.php',
            'output' => '/link.php',
        ];

        // Force HTTPS
        yield [
            'allowedSchemes' => ['http', 'https'],
            'allowedHosts' => ['trusted.com'],
            'allowMailTo' => false,
            'allowRelativeLinks' => false,
            'forceHttps' => true,
            'input' => 'http://trusted.com/link.php',
            'output' => 'https://trusted.com/link.php',
        ];

        // Data-URI not allowed
        yield [
            'allowedSchemes' => ['http', 'https'],
            'allowedHosts' => null,
            'allowMailTo' => true,
            'allowRelativeLinks' => false,
            'forceHttps' => false,
            'input' => 'data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7',
            'output' => null,
        ];

        yield [
            'allowedSchemes' => ['http', 'https'],
            'allowedHosts' => null,
            'allowMailTo' => true,
            'allowRelativeLinks' => false,
            'forceHttps' => true,
            'input' => 'data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7',
            'output' => null,
        ];

        // MailTo
        yield [
            'allowedSchemes' => ['http', 'https'],
            'allowedHosts' => null,
            'allowMailTo' => false,
            'allowRelativeLinks' => false,
            'forceHttps' => false,
            'input' => 'mailto:test@gmail.com',
            'output' => null,
        ];

        yield [
            'allowedSchemes' => ['http', 'https'],
            'allowedHosts' => null,
            'allowMailTo' => true,
            'allowRelativeLinks' => false,
            'forceHttps' => false,
            'input' => 'mailto:test@gmail.com',
            'output' => 'mailto:test@gmail.com',
        ];

        yield [
            'allowedSchemes' => ['http', 'https'],
            'allowedHosts' => ['trusted.com'],
            'allowMailTo' => true,
            'allowRelativeLinks' => false,
            'forceHttps' => false,
            'input' => 'mailto:test@gmail.com',
            'output' => 'mailto:test@gmail.com',
        ];

        yield [
            'allowedSchemes' => ['http', 'https'],
            'allowedHosts' => ['trusted.com'],
            'allowMailTo' => true,
            'allowRelativeLinks' => false,
            'forceHttps' => true,
            'input' => 'mailto:test@gmail.com',
            'output' => 'mailto:test@gmail.com',
        ];

        yield [
            'allowedSchemes' => ['http', 'https'],
            'allowedHosts' => null,
            'allowMailTo' => true,
            'allowRelativeLinks' => false,
            'forceHttps' => false,
            'input' => 'mailto:invalid',
            'output' => null,
        ];

        yield [
            'allowedSchemes' => ['http', 'https'],
            'allowedHosts' => null,
            'allowMailTo' => true,
            'allowRelativeLinks' => false,
            'forceHttps' => false,
            'input' => 'mailto:',
            'output' => null,
        ];

        yield [
            'allowedSchemes' => ['https'],
            'allowedHosts' => null,
            'allowMailTo' => true,
            'allowRelativeLinks' => false,
            'forceHttps' => false,
            'input' => 'http://trusted.com/link.php',
            'output' => null,
        ];
    }

    /**
     * @dataProvider provideUrls
     */
    public function testSanitize($allowedSchemes, $allowedHosts, $allowMailTo, $allowRelativeLinks, $forceHttps, $input, $expected)
    {
        $this->assertSame($expected, (new AHrefSanitizer($allowedSchemes, $allowedHosts, $allowMailTo, $allowRelativeLinks, $forceHttps))->sanitize($input));
    }
}
