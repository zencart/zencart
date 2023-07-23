<?php

/*
 * This file is part of the HTML sanitizer project.
 *
 * (c) Titouan Galopin <galopintitouan@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Tests\HtmlSanitizer;

use HtmlSanitizer\SanitizerBuilder;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class LoggedSanitizerTest extends TestCase
{
    public function testLoggedSanitizer()
    {
        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects($this->once())
            ->method('debug')
            ->with('Sanitized given input to "{output}".', ['output' => 'Hello']);

        $builder = new SanitizerBuilder();
        $builder->setLogger($logger);
        $builder->build([])->sanitize('<div>Hello</div>');
    }
}
