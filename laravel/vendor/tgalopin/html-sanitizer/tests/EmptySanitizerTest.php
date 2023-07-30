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

use HtmlSanitizer\Sanitizer;
use HtmlSanitizer\SanitizerInterface;

class EmptySanitizerTest extends AbstractSanitizerTest
{
    public function createSanitizer(): SanitizerInterface
    {
        return Sanitizer::create([]);
    }

    public function provideFixtures(): array
    {
        return array_merge(parent::provideFixtures(), [
            /*
             * Normal tags
             */

            [
                '<abbr class="foo">Lorem ipsum</abbr>',
                'Lorem ipsum',
            ],
            [
                '<a href="https://trusted.com" title="Link title" class="foo">Lorem ipsum</a>',
                'Lorem ipsum',
            ],
            [
                '<blockquote class="foo">Lorem ipsum</blockquote>',
                'Lorem ipsum',
            ],
            [
                'Lorem ipsum <br class="foo">dolor sit amet <br class="foo" />consectetur adipisicing.',
                'Lorem ipsum dolor sit amet consectetur adipisicing.',
            ],
            [
                '<caption class="foo">Lorem ipsum</caption>',
                'Lorem ipsum',
            ],
            [
                '<code class="foo">Lorem ipsum</code>',
                'Lorem ipsum',
            ],
            [
                '<dd class="foo">Lorem ipsum</dd>',
                'Lorem ipsum',
            ],
            [
                '<del class="foo">Lorem ipsum</del>',
                'Lorem ipsum',
            ],
            [
                '<details class="foo">Lorem ipsum</details>',
                'Lorem ipsum',
            ],
            [
                '<details class="foo" open>Lorem ipsum</details>',
                'Lorem ipsum',
            ],
            [
                '<div class="foo">Lorem ipsum dolor sit amet, consectetur adipisicing elit.</div>',
                'Lorem ipsum dolor sit amet, consectetur adipisicing elit.',
            ],
            [
                '<dl class="foo">Lorem ipsum</dl>',
                'Lorem ipsum',
            ],
            [
                '<dt class="foo">Lorem ipsum</dt>',
                'Lorem ipsum',
            ],
            [
                '<em class="foo">Lorem ipsum</em>',
                'Lorem ipsum',
            ],
            [
                '<figcaption class="foo">Lorem ipsum</figcaption>',
                'Lorem ipsum',
            ],
            [
                '<figure class="foo">Lorem ipsum</figure>',
                'Lorem ipsum',
            ],
            [
                '<h1 class="foo">Lorem ipsum</h1>',
                'Lorem ipsum',
            ],
            [
                '<h2 class="foo">Lorem ipsum</h2>',
                'Lorem ipsum',
            ],
            [
                '<h3 class="foo">Lorem ipsum</h3>',
                'Lorem ipsum',
            ],
            [
                '<h4 class="foo">Lorem ipsum</h4>',
                'Lorem ipsum',
            ],
            [
                '<h5 class="foo">Lorem ipsum</h5>',
                'Lorem ipsum',
            ],
            [
                '<h6 class="foo">Lorem ipsum</h6>',
                'Lorem ipsum',
            ],
            [
                '<hr class="foo" />',
                '',
            ],
            [
                '<iframe src="/frame/example" width="300" height="300" frameborder="0">Lorem ipsum</iframe>',
                'Lorem ipsum',
            ],
            [
                '<iframe>Lorem ipsum</iframe>',
                'Lorem ipsum',
            ],
            [
                '<img src="/img/example.jpg" alt="Image alternative text" title="Image title" class="foo" />',
                '',
            ],
            [
                '<img src="http://trusted.com/img/example.jpg" alt="Image alternative text" title="Image title" class="foo" />',
                '',
            ],
            [
                '<img />',
                '',
            ],
            [
                '<i class="foo">Lorem ipsum</i>',
                'Lorem ipsum',
            ],
            [
                '<li class="foo">Lorem ipsum</li>',
                'Lorem ipsum',
            ],
            [
                '<ol class="foo">Lorem ipsum</ol>',
                'Lorem ipsum',
            ],
            [
                '<p class="foo">Lorem ipsum</p>',
                'Lorem ipsum',
            ],
            [
                '<pre class="foo">Lorem ipsum</pre>',
                'Lorem ipsum',
            ],
            [
                '<q class="foo">Lorem ipsum</q>',
                'Lorem ipsum',
            ],
            [
                '<rp class="foo">Lorem ipsum</rp>',
                'Lorem ipsum',
            ],
            [
                '<rt class="foo">Lorem ipsum</rt>',
                'Lorem ipsum',
            ],
            [
                '<ruby class="foo">Lorem ipsum</ruby>',
                'Lorem ipsum',
            ],
            [
                '<small class="foo">Lorem ipsum</small>',
                'Lorem ipsum',
            ],
            [
                '<span class="foo">Lorem ipsum</span>',
                'Lorem ipsum',
            ],
            [
                '<strong class="foo">Lorem ipsum</strong>',
                'Lorem ipsum',
            ],
            [
                '<b class="foo">Lorem ipsum</b>',
                'Lorem ipsum',
            ],
            [
                '<sub class="foo">Lorem ipsum</sub>',
                'Lorem ipsum',
            ],
            [
                '<summary class="foo">Lorem ipsum</summary>',
                'Lorem ipsum',
            ],
            [
                '<sup class="foo">Lorem ipsum</sup>',
                'Lorem ipsum',
            ],
            [
                '<table class="foo">Lorem ipsum</table>',
                'Lorem ipsum',
            ],
            [
                '<tbody class="foo">Lorem ipsum</tbody>',
                'Lorem ipsum',
            ],
            [
                '<td class="foo">Lorem ipsum</td>',
                'Lorem ipsum',
            ],
            [
                '<tfoot class="foo">Lorem ipsum</tfoot>',
                'Lorem ipsum',
            ],
            [
                '<u class="foo">Lorem ipsum</u>',
                'Lorem ipsum',
            ],

            /*
             * Scripts
             */

            [
                '<div>Lorem ipsum dolor sit amet, consectetur adipisicing elit.<script>alert(\'ok\');</script></div>',
                'Lorem ipsum dolor sit amet, consectetur adipisicing elit.',
            ],
            [
                '<figure><img src="/img/example.jpg" onclick="alert(\'ok\')" /></figure>',
                '',
            ],
            [
                '<a href="javascript:alert(\'ok\')">Lorem ipsum dolor sit amet, consectetur adipisicing elit.</a>',
                'Lorem ipsum dolor sit amet, consectetur adipisicing elit.',
            ],

            /*
             * Styles
             */

            [
                '<div>Lorem ipsum dolor sit amet, consectetur.<style>body { background: red; }</style></div>',
                'Lorem ipsum dolor sit amet, consectetur.',
            ],
            [
                '<img src="/img/example.jpg" style="position:absolute;top:0;left:0;width:9000px;height:9000px;" />',
                '',
            ],
            [
                '<a style="font-size: 40px; color: red;">Lorem ipsum dolor sit amet, consectetur.</a>',
                'Lorem ipsum dolor sit amet, consectetur.',
            ],
        ]);
    }
}
