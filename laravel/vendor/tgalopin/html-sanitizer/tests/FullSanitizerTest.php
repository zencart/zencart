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

class FullSanitizerTest extends AbstractSanitizerTest
{
    public function createSanitizer(): SanitizerInterface
    {
        return Sanitizer::create([
            'extensions' => ['basic', 'code', 'details', 'image', 'list', 'table', 'iframe', 'extra'],
            'tags' => [
                'abbr' => [
                    'allowed_attributes' => ['data-attr'],
                ],
                'a' => [
                    'allowed_attributes' => ['href', 'title', 'data-attr'],
                    'allowed_hosts' => ['trusted.com', 'external.com'],
                    'allow_mailto' => true,
                    'force_https' => false,
                    'rel' => 'noopener',
                ],
                'blockquote' => [
                    'allowed_attributes' => ['data-attr'],
                ],
                'br' => [
                    'allowed_attributes' => ['data-attr'],
                ],
                'caption' => [
                    'allowed_attributes' => ['data-attr'],
                ],
                'code' => [
                    'allowed_attributes' => ['data-attr'],
                ],
                'dd' => [
                    'allowed_attributes' => ['data-attr'],
                ],
                'del' => [
                    'allowed_attributes' => ['data-attr'],
                ],
                'details' => [
                    'allowed_attributes' => ['open', 'data-attr'],
                ],
                'div' => [
                    'allowed_attributes' => ['data-attr'],
                ],
                'dl' => [
                    'allowed_attributes' => ['data-attr'],
                ],
                'dt' => [
                    'allowed_attributes' => ['data-attr'],
                ],
                'em' => [
                    'allowed_attributes' => ['data-attr'],
                ],
                'figcaption' => [
                    'allowed_attributes' => ['data-attr'],
                ],
                'figure' => [
                    'allowed_attributes' => ['data-attr'],
                ],
                'h1' => [
                    'allowed_attributes' => ['data-attr'],
                ],
                'h2' => [
                    'allowed_attributes' => ['data-attr'],
                ],
                'h3' => [
                    'allowed_attributes' => ['data-attr'],
                ],
                'h4' => [
                    'allowed_attributes' => ['data-attr'],
                ],
                'h5' => [
                    'allowed_attributes' => ['data-attr'],
                ],
                'h6' => [
                    'allowed_attributes' => ['data-attr'],
                ],
                'hr' => [
                    'allowed_attributes' => ['data-attr'],
                ],
                'iframe' => [
                    'allowed_attributes' => ['src', 'width', 'height', 'frameborder', 'title', 'allow', 'allowfullscreen', 'data-attr'],
                    'allowed_hosts' => ['trusted.com'],
                    'force_https' => true,
                ],
                'img' => [
                    'allowed_attributes' => ['src', 'alt', 'title', 'data-attr'],
                    'allowed_hosts' => ['trusted.com'],
                    'allow_data_uri' => false,
                    'force_https' => true,
                ],
                'i' => [
                    'allowed_attributes' => ['data-attr'],
                ],
                'li' => [
                    'allowed_attributes' => ['data-attr'],
                ],
                'mark' => [
                    'allowed_attributes' => ['data-attr'],
                ],
                'ol' => [
                    'allowed_attributes' => ['data-attr'],
                ],
                'pre' => [
                    'allowed_attributes' => ['data-attr'],
                ],
                'p' => [
                    'allowed_attributes' => ['data-attr'],
                ],
                'q' => [
                    'allowed_attributes' => ['data-attr'],
                ],
                'rp' => [
                    'allowed_attributes' => ['data-attr'],
                ],
                'rt' => [
                    'allowed_attributes' => ['data-attr'],
                ],
                'ruby' => [
                    'allowed_attributes' => ['data-attr'],
                ],
                'small' => [
                    'allowed_attributes' => ['data-attr'],
                ],
                'span' => [
                    'allowed_attributes' => ['data-attr'],
                ],
                'strong' => [
                    'allowed_attributes' => ['data-attr'],
                ],
                'sub' => [
                    'allowed_attributes' => ['data-attr'],
                ],
                'summary' => [
                    'allowed_attributes' => ['data-attr'],
                ],
                'sup' => [
                    'allowed_attributes' => ['data-attr'],
                ],
                'table' => [
                    'allowed_attributes' => ['data-attr'],
                ],
                'tbody' => [
                    'allowed_attributes' => ['data-attr'],
                ],
                'td' => [
                    'allowed_attributes' => ['data-attr'],
                ],
                'tfoot' => [
                    'allowed_attributes' => ['data-attr'],
                ],
                'thead' => [
                    'allowed_attributes' => ['data-attr'],
                ],
                'th' => [
                    'allowed_attributes' => ['data-attr'],
                ],
                'time' => [
                    'allowed_attributes' => ['data-attr', 'datetime'],
                ],
                'tr' => [
                    'allowed_attributes' => ['data-attr'],
                ],
                'u' => [
                    'allowed_attributes' => ['data-attr'],
                ],
                'ul' => [
                    'allowed_attributes' => ['data-attr'],
                ],
            ],
        ]);
    }

    public function provideFixtures(): array
    {
        return array_merge(parent::provideFixtures(), [
            /*
             * Normal tags
             */

            [
                '<abbr class="foo" data-attr="foo">Lorem ipsum</abbr>',
                '<abbr data-attr="foo">Lorem ipsum</abbr>',
            ],
            [
                '<a href="https://trusted.com" title="Link title" class="foo" data-attr="foo">Lorem ipsum</a>',
                '<a href="https://trusted.com" rel="noopener" title="Link title" data-attr="foo">Lorem ipsum</a>',
            ],
            [
                '<a href="https://untrusted.com" title="Link title" class="foo" data-attr="foo">Lorem ipsum</a>',
                '<a rel="noopener" title="Link title" data-attr="foo">Lorem ipsum</a>',
            ],
            [
                '<a href="https://external.com" title="Link title" class="foo" data-attr="foo">Lorem ipsum</a>',
                '<a href="https://external.com" rel="noopener" title="Link title" data-attr="foo">Lorem ipsum</a>',
            ],
            [
                '<a href="mailto:test&#64;gmail.com" title="Link title" class="foo" data-attr="foo">Lorem ipsum</a>',
                '<a href="mailto:test&#64;gmail.com" rel="noopener" title="Link title" data-attr="foo">Lorem ipsum</a>',
            ],
            [
                '<blockquote class="foo" data-attr="foo">Lorem ipsum</blockquote>',
                '<blockquote data-attr="foo">Lorem ipsum</blockquote>',
            ],
            [
                'Lorem ipsum <br class="foo" data-attr="foo">dolor sit amet <br class="foo" data-attr="foo" />consectetur adipisicing.',
                'Lorem ipsum <br data-attr="foo" />dolor sit amet <br data-attr="foo" />consectetur adipisicing.',
            ],
            [
                '<caption class="foo" data-attr="foo">Lorem ipsum</caption>',
                '<caption data-attr="foo">Lorem ipsum</caption>',
            ],
            [
                '<code class="foo" data-attr="foo">Lorem ipsum</code>',
                '<code data-attr="foo">Lorem ipsum</code>',
            ],
            [
                '<dd class="foo" data-attr="foo">Lorem ipsum</dd>',
                '<dd data-attr="foo">Lorem ipsum</dd>',
            ],
            [
                '<del class="foo" data-attr="foo">Lorem ipsum</del>',
                '<del data-attr="foo">Lorem ipsum</del>',
            ],
            [
                '<details class="foo" data-attr="foo">Lorem ipsum</details>',
                '<details data-attr="foo">Lorem ipsum</details>',
            ],
            [
                '<details class="foo" data-attr="foo" open>Lorem ipsum</details>',
                '<details open="open" data-attr="foo">Lorem ipsum</details>',
            ],
            [
                '<details class="foo" data-attr="foo" open="foo">Lorem ipsum</details>',
                '<details open="open" data-attr="foo">Lorem ipsum</details>',
            ],
            [
                '<div class="foo" data-attr="foo">Lorem ipsum dolor sit amet, consectetur adipisicing elit.</div>',
                '<div data-attr="foo">Lorem ipsum dolor sit amet, consectetur adipisicing elit.</div>',
            ],
            [
                '<dl class="foo" data-attr="foo">Lorem ipsum</dl>',
                '<dl data-attr="foo">Lorem ipsum</dl>',
            ],
            [
                '<dt class="foo" data-attr="foo">Lorem ipsum</dt>',
                '<dt data-attr="foo">Lorem ipsum</dt>',
            ],
            [
                '<em class="foo" data-attr="foo">Lorem ipsum</em>',
                '<em data-attr="foo">Lorem ipsum</em>',
            ],
            [
                '<figcaption class="foo" data-attr="foo">Lorem ipsum</figcaption>',
                '<figcaption data-attr="foo">Lorem ipsum</figcaption>',
            ],
            [
                '<figure class="foo" data-attr="foo">Lorem ipsum</figure>',
                '<figure data-attr="foo">Lorem ipsum</figure>',
            ],
            [
                '<h1 class="foo" data-attr="foo">Lorem ipsum</h1>',
                '<h1 data-attr="foo">Lorem ipsum</h1>',
            ],
            [
                '<h2 class="foo" data-attr="foo">Lorem ipsum</h2>',
                '<h2 data-attr="foo">Lorem ipsum</h2>',
            ],
            [
                '<h3 class="foo" data-attr="foo">Lorem ipsum</h3>',
                '<h3 data-attr="foo">Lorem ipsum</h3>',
            ],
            [
                '<h4 class="foo" data-attr="foo">Lorem ipsum</h4>',
                '<h4 data-attr="foo">Lorem ipsum</h4>',
            ],
            [
                '<h5 class="foo" data-attr="foo">Lorem ipsum</h5>',
                '<h5 data-attr="foo">Lorem ipsum</h5>',
            ],
            [
                '<h6 class="foo" data-attr="foo">Lorem ipsum</h6>',
                '<h6 data-attr="foo">Lorem ipsum</h6>',
            ],
            [
                '<hr class="foo" data-attr="foo" />',
                '<hr data-attr="foo" />',
            ],
            [
                '<iframe src="/frame/example" width="300" height="300" frameborder="0" data-attr="foo">Lorem ipsum</iframe>',
                '<iframe width="300" height="300" frameborder="0" data-attr="foo">Lorem ipsum</iframe>',
            ],
            [
                '<iframe src="http://trusted.com/frame/example" width="300" height="300" frameborder="0">Lorem ipsum</iframe>',
                '<iframe src="https://trusted.com/frame/example" width="300" height="300" frameborder="0">Lorem ipsum</iframe>',
            ],
            [
                '<iframe src="http://untrusted.com/frame/example" width="300" height="300" frameborder="0">Lorem ipsum</iframe>',
                '<iframe width="300" height="300" frameborder="0">Lorem ipsum</iframe>',
            ],
            [
                '<iframe>Lorem ipsum</iframe>',
                '<iframe>Lorem ipsum</iframe>',
            ],
            [
                '<img src="/img/example.jpg" alt="Image alternative text" title="Image title" class="foo" data-attr="foo">',
                '<img alt="Image alternative text" title="Image title" data-attr="foo" />',
            ],
            [
                '<img src="http://trusted.com/img/example.jpg" alt="Image alternative text" title="Image title" class="foo" data-attr="foo" />',
                '<img src="https://trusted.com/img/example.jpg" alt="Image alternative text" title="Image title" data-attr="foo" />',
            ],
            [
                '<img src="http://untrusted.com/img/example.jpg" alt="Image alternative text" title="Image title" class="foo" data-attr="foo" />',
                '<img alt="Image alternative text" title="Image title" data-attr="foo" />',
            ],
            [
                '<img />',
                '<img />',
            ],
            [
                '<img title="" />',
                '<img title />',
            ],
            [
                '<i class="foo" data-attr="foo">Lorem ipsum</i>',
                '<i data-attr="foo">Lorem ipsum</i>',
            ],
            [
                '<li class="foo" data-attr="foo">Lorem ipsum</li>',
                '<li data-attr="foo">Lorem ipsum</li>',
            ],
            [
                '<mark class="foo" data-attr="foo">Lorem ipsum</mark>',
                '<mark data-attr="foo">Lorem ipsum</mark>',
            ],
            [
                '<ol class="foo" data-attr="foo">Lorem ipsum</ol>',
                '<ol data-attr="foo">Lorem ipsum</ol>',
            ],
            [
                '<p class="foo" data-attr="foo">Lorem ipsum</p>',
                '<p data-attr="foo">Lorem ipsum</p>',
            ],
            [
                '<pre class="foo" data-attr="foo">Lorem ipsum</pre>',
                '<pre data-attr="foo">Lorem ipsum</pre>',
            ],
            [
                '<q class="foo" data-attr="foo">Lorem ipsum</q>',
                '<q data-attr="foo">Lorem ipsum</q>',
            ],
            [
                '<rp class="foo" data-attr="foo">Lorem ipsum</rp>',
                '<rp data-attr="foo">Lorem ipsum</rp>',
            ],
            [
                '<rt class="foo" data-attr="foo">Lorem ipsum</rt>',
                '<rt data-attr="foo">Lorem ipsum</rt>',
            ],
            [
                '<ruby class="foo" data-attr="foo">Lorem ipsum</ruby>',
                '<ruby data-attr="foo">Lorem ipsum</ruby>',
            ],
            [
                '<small class="foo" data-attr="foo">Lorem ipsum</small>',
                '<small data-attr="foo">Lorem ipsum</small>',
            ],
            [
                '<span class="foo" data-attr="foo">Lorem ipsum</span>',
                '<span data-attr="foo">Lorem ipsum</span>',
            ],
            [
                '<strong class="foo" data-attr="foo">Lorem ipsum</strong>',
                '<strong data-attr="foo">Lorem ipsum</strong>',
            ],
            [
                '<summary class="foo" data-attr="foo">Lorem ipsum</summary>',
                '<summary data-attr="foo">Lorem ipsum</summary>',
            ],
            [
                '<time class="foo" datetime="2018-12-25 00:00" data-attr="foo">Lorem ipsum</time>',
                '<time datetime="2018-12-25 00:00" data-attr="foo">Lorem ipsum</time>',
            ],
            [
                '<b class="foo" data-attr="foo">Lorem ipsum</b>',
                '<strong data-attr="foo">Lorem ipsum</strong>',
            ],
            [
                '<sub class="foo" data-attr="foo">Lorem ipsum</sub>',
                '<sub data-attr="foo">Lorem ipsum</sub>',
            ],
            [
                '<sup class="foo" data-attr="foo">Lorem ipsum</sup>',
                '<sup data-attr="foo">Lorem ipsum</sup>',
            ],
            [
                '<table class="foo" data-attr="foo">Lorem ipsum</table>',
                '<table data-attr="foo">Lorem ipsum</table>',
            ],
            [
                '<tbody class="foo" data-attr="foo">Lorem ipsum</tbody>',
                '<tbody data-attr="foo">Lorem ipsum</tbody>',
            ],
            [
                '<td class="foo" data-attr="foo">Lorem ipsum</td>',
                '<td data-attr="foo">Lorem ipsum</td>',
            ],
            [
                '<tfoot class="foo" data-attr="foo">Lorem ipsum</tfoot>',
                '<tfoot data-attr="foo">Lorem ipsum</tfoot>',
            ],
            [
                '<thead class="foo" data-attr="foo">Lorem ipsum</thead>',
                '<thead data-attr="foo">Lorem ipsum</thead>',
            ],
            [
                '<th class="foo" data-attr="foo">Lorem ipsum</th>',
                '<th data-attr="foo">Lorem ipsum</th>',
            ],
            [
                '<tr class="foo" data-attr="foo">Lorem ipsum</tr>',
                '<tr data-attr="foo">Lorem ipsum</tr>',
            ],
            [
                '<ul class="foo" data-attr="foo">Lorem ipsum</ul>',
                '<ul data-attr="foo">Lorem ipsum</ul>',
            ],

            /*
             * Links
             */

            [
                '<a href="mailto:test&#64;gmail.com">Test</a>',
                '<a href="mailto:test&#64;gmail.com" rel="noopener">Test</a>',
            ],
            [
                '<a href="mailto:alert(\'ok\')">Test</a>',
                '<a rel="noopener">Test</a>',
            ],
            [
                '<a href="javascript:alert(\'ok\')">Test</a>',
                '<a rel="noopener">Test</a>',
            ],
            [
                '<a href="javascript://%0Aalert(document.cookie)">Test</a>',
                '<a rel="noopener">Test</a>',
            ],
            [
                '<a href="http://untrusted.com" onclick="alert(\'ok\')">Test</a>',
                '<a rel="noopener">Test</a>',
            ],
            [
                '<a href="https://trusted.com">Test</a>',
                '<a href="https://trusted.com" rel="noopener">Test</a>',
            ],
            [
                '<a>Lorem ipsum</a>',
                '<a rel="noopener">Lorem ipsum</a>',
            ],
            [
                '<a href="&#106;&#97;&#118;&#97;&#115;&#99;&#114;&#105;&#112;&#116;&#58;&#97;&#108;&#101;&#114;&#116;&#40;&#39;&#88;&#83;&#83;&#39;&#41;">Lorem ipsum</a>',
                '<a rel="noopener">Lorem ipsum</a>',
            ],
            [
                '<a href="http://trusted.com/index.html#this:stuff">Lorem ipsum</a>',
                '<a href="http://trusted.com/index.html#this:stuff" rel="noopener">Lorem ipsum</a>',
            ],
            [
                '<a href="java\0&#14;\t\r\n script:alert(\\\'foo\\\')">Lorem ipsum</a>',
                '<a rel="noopener">Lorem ipsum</a>',
            ],
            [
                '<a href= onmouseover="alert(\\\'XSS\\\');">Lorem ipsum</a>',
                '<a rel="noopener">Lorem ipsum</a>',
            ],

            // Inspired by https://twitter.com/brutelogic/status/1066333383276593152?s=19
            [
                '"><svg/onload=confirm(1)>"@x.y',
                '&#34;&gt;&#34;&#64;x.y',
            ],

            // Inspired by https://html5sec.org
            [
                '<a href="javascript:&apos;<svg onload&equals;alert&lpar;1&rpar;&nvgt;&apos;">Lorem ipsum</a>',
                '<a rel="noopener">Lorem ipsum</a>',
            ],

            /*
             * Scripts
             */

            [
                '<div>Lorem ipsum dolor sit amet, consectetur adipisicing elit.<script>alert(\'ok\');</script></div>',
                '<div>Lorem ipsum dolor sit amet, consectetur adipisicing elit.</div>',
            ],
            [
                '<figure><img src="https://trusted.com/img/example.jpg" onclick="alert(\'ok\')" /></figure>',
                '<figure><img src="https://trusted.com/img/example.jpg" /></figure>',
            ],
            [
                '<a href="javascript:alert(\'ok\')">Lorem ipsum dolor sit amet, consectetur adipisicing elit.</a>',
                '<a rel="noopener">Lorem ipsum dolor sit amet, consectetur adipisicing elit.</a>',
            ],
            [
                '<img src= onmouseover="alert(\'XSS\');" />',
                '<img />',
            ],
            [
                '<<img src="javascript:evil"/>img src="javascript:evil"/>',
                '<img />img src&#61;&#34;javascript:evil&#34;/&gt;',
            ],
            [
                '<<a href="javascript:evil"/>a href="javascript:evil"/>',
                '<a rel="noopener">a href&#61;&#34;javascript:evil&#34;/&gt;</a>',
            ],
            [
                '!<textarea>&lt;/textarea&gt;&lt;svg/onload=prompt`xs`&gt;</textarea>!',
                '!&lt;/textarea&gt;&lt;svg/onload&#61;prompt&#96;xs&#96;&gt;!',
            ],
            [
                '<iframe src= onmouseover="alert(\'XSS\');" />',
                '<iframe></iframe>',
            ],
            [
                '<<iframe src="javascript:evil"/>iframe src="javascript:evil"/>',
                '<iframe>iframe src&#61;&#34;javascript:evil&#34;/&gt;</iframe>',
            ],
            [
                '<scr<script>ipt>alert(1)</script>',
                '',
            ],
            [
                '<scr<a>ipt>alert(1)</script>',
                '<a rel="noopener">ipt&gt;alert(1)</a>',
            ],

            /*
             * Styles
             */

            [
                '<div>Lorem ipsum dolor sit amet, consectetur.<style>body { background: red; }</style></div>',
                '<div>Lorem ipsum dolor sit amet, consectetur.</div>',
            ],
            [
                '<img src="https://trusted.com/img/example.jpg" style="position:absolute;top:0;left:0;width:9000px;height:9000px;" />',
                '<img src="https://trusted.com/img/example.jpg" />',
            ],
            [
                '<a style="font-size: 40px; color: red;">Lorem ipsum dolor sit amet, consectetur.</a>',
                '<a rel="noopener">Lorem ipsum dolor sit amet, consectetur.</a>',
            ],
        ]);
    }
}
