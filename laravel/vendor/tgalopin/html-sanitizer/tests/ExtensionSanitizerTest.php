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
use HtmlSanitizer\SanitizerInterface;
use Tests\HtmlSanitizer\Extension\CustomExtension;

class ExtensionSanitizerTest extends EmptySanitizerTest
{
    public function createSanitizer(): SanitizerInterface
    {
        $builder = new SanitizerBuilder();
        $builder->registerExtension(new CustomExtension());

        return $builder->build([
            'extensions' => ['custom'],
            'tags' => [
                'custom' => [
                    'custom_data' => 'foo',
                ],
            ],
        ]);
    }

    public function provideFixtures(): array
    {
        return array_merge(parent::provideFixtures(), [
            [
                '<custom>Lorem ipsum</custom>',
                '<custom data-custom="foo">Lorem ipsum</custom>',
            ],
        ]);
    }
}
