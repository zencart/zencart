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

class SimpleSanitizerTest extends AbstractSanitizerTest
{
    public function createSanitizer(): SanitizerInterface
    {
        return Sanitizer::create(['extensions' => ['basic']]);
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
                '<a href="https://trusted.com" title="Link title">Lorem ipsum</a>',
            ],
            [
                '<a href="https://untrusted.com" title="Link title" class="foo">Lorem ipsum</a>',
                '<a href="https://untrusted.com" title="Link title">Lorem ipsum</a>',
            ],
            [
                '<a href="https://external.com" title="Link title" class="foo">Lorem ipsum</a>',
                '<a href="https://external.com" title="Link title">Lorem ipsum</a>',
            ],
            [
                '<a href="mailto:test&#64;gmail.com" title="Link title" class="foo">Lorem ipsum</a>',
                '<a href="mailto:test&#64;gmail.com" title="Link title">Lorem ipsum</a>',
            ],
            [
                '<blockquote class="foo">Lorem ipsum</blockquote>',
                '<blockquote>Lorem ipsum</blockquote>',
            ],
            [
                'Lorem ipsum <br class="foo">dolor sit amet <br class="foo" />consectetur adipisicing.',
                'Lorem ipsum <br />dolor sit amet <br />consectetur adipisicing.',
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
                '<del>Lorem ipsum</del>',
            ],
            [
                '<div class="foo">Lorem ipsum dolor sit amet, consectetur adipisicing elit.</div>',
                '<div>Lorem ipsum dolor sit amet, consectetur adipisicing elit.</div>',
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
                '<em>Lorem ipsum</em>',
            ],
            [
                '<figcaption class="foo">Lorem ipsum</figcaption>',
                '<figcaption>Lorem ipsum</figcaption>',
            ],
            [
                '<figure class="foo">Lorem ipsum</figure>',
                '<figure>Lorem ipsum</figure>',
            ],
            [
                '<h1 class="foo">Lorem ipsum</h1>',
                '<h1>Lorem ipsum</h1>',
            ],
            [
                '<h2 class="foo">Lorem ipsum</h2>',
                '<h2>Lorem ipsum</h2>',
            ],
            [
                '<h3 class="foo">Lorem ipsum</h3>',
                '<h3>Lorem ipsum</h3>',
            ],
            [
                '<h4 class="foo">Lorem ipsum</h4>',
                '<h4>Lorem ipsum</h4>',
            ],
            [
                '<h5 class="foo">Lorem ipsum</h5>',
                '<h5>Lorem ipsum</h5>',
            ],
            [
                '<h6 class="foo">Lorem ipsum</h6>',
                '<h6>Lorem ipsum</h6>',
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
                '<iframe srcdoc=\'&lt;meta http-equiv="refresh" content="5;url=(link: https://www.google.com/) google.com " /&gt;&lt;script&gt;alert(document.domain + "\n\n" + document.cookie);</script>\'/>',
                '',
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
                '<i>Lorem ipsum</i>',
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
                '<p>Lorem ipsum</p>',
            ],
            [
                '<pre class="foo">Lorem ipsum</pre>',
                'Lorem ipsum',
            ],
            [
                '<q class="foo">Lorem ipsum</q>',
                '<q>Lorem ipsum</q>',
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
                '<small>Lorem ipsum</small>',
            ],
            [
                '<span class="foo">Lorem ipsum</span>',
                '<span>Lorem ipsum</span>',
            ],
            [
                '<strong class="foo">Lorem ipsum</strong>',
                '<strong>Lorem ipsum</strong>',
            ],
            [
                '<b class="foo">Lorem ipsum</b>',
                '<strong>Lorem ipsum</strong>',
            ],
            [
                '<sub class="foo">Lorem ipsum</sub>',
                '<sub>Lorem ipsum</sub>',
            ],
            [
                '<sup class="foo">Lorem ipsum</sup>',
                '<sup>Lorem ipsum</sup>',
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
                '<thead class="foo">Lorem ipsum</thead>',
                'Lorem ipsum',
            ],
            [
                '<th class="foo">Lorem ipsum</th>',
                'Lorem ipsum',
            ],
            [
                '<tr class="foo">Lorem ipsum</tr>',
                'Lorem ipsum',
            ],
            [
                '<u class="foo">Lorem ipsum</u>',
                '<u>Lorem ipsum</u>',
            ],
            [
                '<ul class="foo">Lorem ipsum</ul>',
                'Lorem ipsum',
            ],

            /*
             * Links
             */

            [
                '<a href="mailto:test&#64;gmail.com">Test</a>',
                '<a href="mailto:test&#64;gmail.com">Test</a>',
            ],
            [
                '<a href="mailto:alert(\'ok\')">Test</a>',
                '<a>Test</a>',
            ],
            [
                '<a href="javascript:alert(\'ok\')">Test</a>',
                '<a>Test</a>',
            ],
            [
                '<a href="javascript://%0Aalert(document.cookie)">Test</a>',
                '<a>Test</a>',
            ],
            [
                '<a href="http://untrusted.com" onclick="alert(\'ok\')">Test</a>',
                '<a href="http://untrusted.com">Test</a>',
            ],
            [
                '<a href="https://trusted.com">Test</a>',
                '<a href="https://trusted.com">Test</a>',
            ],
            [
                '<a>Lorem ipsum</a>',
                '<a>Lorem ipsum</a>',
            ],
            [
                '<a href="&#106;&#97;&#118;&#97;&#115;&#99;&#114;&#105;&#112;&#116;&#58;&#97;&#108;&#101;&#114;&#116;&#40;&#39;&#88;&#83;&#83;&#39;&#41;">Lorem ipsum</a>',
                '<a>Lorem ipsum</a>',
            ],
            [
                '<a href="http://trusted.com/index.html#this:stuff">Lorem ipsum</a>',
                '<a href="http://trusted.com/index.html#this:stuff">Lorem ipsum</a>',
            ],
            [
                '<a href="java\0&#14;\t\r\n script:alert(\\\'foo\\\')">Lorem ipsum</a>',
                '<a>Lorem ipsum</a>',
            ],
            [
                '<a href= onmouseover="alert(\\\'XSS\\\');">Lorem ipsum</a>',
                '<a>Lorem ipsum</a>',
            ],

            /*
             * Scripts
             */

            [
                '<div>Lorem ipsum dolor sit amet, consectetur adipisicing elit.<script>alert(\'ok\');</script></div>',
                '<div>Lorem ipsum dolor sit amet, consectetur adipisicing elit.</div>',
            ],
            [
                '<figure><img src="/img/example.jpg" onclick="alert(\'ok\')" /></figure>',
                '<figure></figure>',
            ],
            [
                '<a href="javascript:alert(\'ok\')">Lorem ipsum dolor sit amet, consectetur adipisicing elit.</a>',
                '<a>Lorem ipsum dolor sit amet, consectetur adipisicing elit.</a>',
            ],
            [
                '"><script>...</script><input value="',
                '&#34;&gt;',
            ],
            [
                '<scr<script>ipt>alert(1)</script>',
                '',
            ],
            [
                '<TABLE BACKGROUND="javascript:alert(\'XSS\')">',
                '',
            ],
            [
                '<title onpropertychange=alert(1)></title><title title=></title>',
                '',
            ],
            [
                '<img src=x:alert(alt) onerror=eval(src) alt=0>',
                '',
            ],
            [
                "\n><!-\n<b\n<c d=\"'e><iframe onload=alert(1) src=x>\n<a HREF=\"\">\n",
                "\n&gt;\n&lt;a HREF&#61;&#34;&#34;&gt;\n",
            ],
            [
                '<embed src="data:text/html;base64,PHNjcmlwdD5hbGVydCgxKTwvc2NyaXB0Pg=="></embed>',
                '',
            ],
            [
                '<div><embed allowscriptaccess=always src=/xss.swf><base href=//l0.cm/</div>',
                '<div></div>',
            ],
            [
                '"><svg><script>/<@/>alert(1337)</script>',
                '&#34;&gt;',
            ],
            [
                '<scr<a>ipt>alert(1)</script>',
                '<a>ipt&gt;alert(1)</a>',
            ],
            [
                '<IMG SRC=&#x6A&#x61&#x76&#x61&#x73&#x63&#x72&#x69&#x70&#x74&#x3A&#x61&#x6C&#x65&#x72&#x74&#x28&#x27&#x58&#x53&#x53&#x27&#x29>',
                '',
            ],
            [
                '<div onClick="&#38&#35&#49&#48&#54&#38&#35&#57&#55&#38&#35&#49&#49&#56&#38&#35&#57&#55&#38&#35&#49&#49&#53&#38&#35&#57&#57&#38&#35&#49&#49&#52&#38&#35&#49&#48&#53&#38&#35&#49&#49&#50&#38&#35&#49&#49&#54&#38&#35&#53&#56&#38&#35&#57&#57&#38&#35&#49&#49&#49&#38&#35&#49&#49&#48&#38&#35&#49&#48&#50&#38&#35&#49&#48&#53&#38&#35&#49&#49&#52&#38&#35&#49&#48&#57&#38&#35&#52&#48&#38&#35&#52&#57&#38&#35&#52&#49">Clickhere</div>',
                '<div>Clickhere</div>',
            ],
            [
                '<svg><![CDATA[><image xlink:href="]]><img src=xx:x onerror=alert(2)//"></svg>',
                '',
            ],
            [
                '<b a=<=" onmouseover="alert(1),1>1">',
                '<strong></strong>',
            ],
            [
                '<BGSOUND SRC="javascript:alert(\'XSS\');">',
                '',
            ],
            [
                '<BR SIZE="&{alert(\'XSS\')}">',
                '<br />',
            ],
            [
                '<META HTTP-EQUIV="Set-Cookie" Content="USERID=&lt;SCRIPT&gt;alert(\'XSS\')&lt;/SCRIPT&gt;">',
                '',
            ],
            // UTF-7 encoding
            [
                '<HEAD><META HTTP-EQUIV="CONTENT-TYPE" CONTENT="text/html; charset=UTF-7"> </HEAD>+ADw-SCRIPT+AD4-alert(\'XSS\');+ADw-/SCRIPT+AD4-',
                ' &#43;ADw-SCRIPT&#43;AD4-alert(&#039;XSS&#039;);&#43;ADw-/SCRIPT&#43;AD4-',
            ],
            // US-ASCII encoding
            [
                '¼script¾alert(¢XSS¢)¼/script¾',
                '¼script¾alert(¢XSS¢)¼/script¾',
            ],

            /*
             * Styles
             */

            [
                "<DIV STYLE=\"background-image:\\0075\\0072\\006C\\0028\'\\006a\\0061\\0076\\0061\\0073\\0063\\0072\\0069\\0070\\0074\\003a\\0061\\006c\\0065\\0072\\0074\\0028\\0027\\0058\\0053\\0053\\0027\\0029\'\\0029\">",
                '<div></div>',
            ],
            [
                "<STYLE>.XSS{background-image:url(\"javascript:alert(\'XSS\')\");}</STYLE><A CLASS=XSS></A>",
                '<a></a>',
            ],
            [
                '<style>*{x:ｅｘｐｒｅｓｓｉｏｎ(write(1))}</style>',
                '',
            ],
            [
                '<STYLE>@import\'http://ha.ckers.org/xss.css\';</STYLE>',
                '',
            ],
            [
                '<div>Lorem ipsum dolor sit amet, consectetur.<style>body { background: red; }</style></div>',
                '<div>Lorem ipsum dolor sit amet, consectetur.</div>',
            ],
            [
                '<img src="/img/example.jpg" style="position:absolute;top:0;left:0;width:9000px;height:9000px;" />',
                '',
            ],
            [
                '<a style="font-size: 40px; color: red;">Lorem ipsum dolor sit amet, consectetur.</a>',
                '<a>Lorem ipsum dolor sit amet, consectetur.</a>',
            ],

            /*
             * Ideas extracted from https://github.com/OWASP/java-html-sanitizer
             */

            [
                '<p/b onload=""/',
                '<p></p>',
            ],
            [
                '<p onload=""/b/',
                '<p></p>',
            ],
            [
                '<p onload=""<a href="https://trusted.com/" onload="">first part of the text</> second part',
                '<p><a href="https://trusted.com/">first part of the text second part</a></p>',
            ],
            [
                '<p onload=""<b onload="">Hello',
                '<p><strong>Hello</strong></p>',
            ],
            [
                '<p>Hello world</b style="width:expression(alert(1))">',
                '<p>Hello world</p>',
            ],
            [
                "<A TITLE=\"harmless\0  SCRIPT = javascript:alert(1) ignored = ignored\">",
                '<a title="harmless  SCRIPT &#61; javascript:alert(1) ignored &#61; ignored"></a>',
            ],
            [
                '<div style1="expression(\'alert(1)">Hello</div>',
                '<div>Hello</div>',
            ],
            [
                '<a title="``onmouseover=alert(1337)">Hello</a>',
                '<a title="&#96;&#96;onmouseover&#61;alert(1337) ">Hello</a>',
            ],
            // Tag stripping, different ways to work around removal of HTML tags.
            [
                '<script>alert(0)</script>',
                '',
            ],
            [
                '<script src="http://www.example.com" />',
                '',
            ],
            [
                '<ScRipt sRc=http://www.example.com/>',
                '',
            ],
            [
                "<script\nsrc\n=\nhttp://www.example.com/\n>",
                '',
            ],
            [
                '<script/a src=http://www.example.com/a.js></script>',
                '',
            ],
            [
                '<script/src=http://www.example.com/a.js></script>',
                '',
            ],
            // Null between < and tag name works at least with IE6.
            [
                "<\0scr\0ipt>alert(0)</script>",
                '',
            ],
            [
                '<scrscriptipt src=http://www.example.com/a.js>',
                '',
            ],
            [
                '<<script>alert(0);//<</script>',
                '',
            ],
            [
                '<script src=http://www.example.com/a.js?<b>',
                '',
            ],
            // DRUPAL-SA-2008-047: This doesn't seem exploitable, but the filter should
            // work consistently.
            [
                '<script>>',
                '',
            ],
            [
                '<script src=//www.example.com/.a>',
                '',
            ],
            [
                '<script src=http://www.example.com/.a',
                '',
            ],
            [
                '<script src=http://www.example.com/ <',
                '',
            ],
            [
                '<nosuchtag attribute="newScriptInjectionVector">',
                '',
            ],
            [
                '<t:set attributeName="innerHTML" to="&lt;script defer&gt;alert(0)&lt;/script&gt;">',
                '',
            ],
            [
                '<img """><script>alert(0)</script>',
                '',
            ],
            [
                '<blockquote><script>alert(0)</script></blockquote>',
                '<blockquote></blockquote>',
            ],
            [
                '<!--[if true]><script>alert(0)</script><![endif]-->',
                '',
            ],
            // Dangerous attributes removal.
            [
                '<p onmouseover="http://www.example.com/">',
                '<p></p>',
            ],
            [
                '<li style="list-style-image: url(javascript:alert(0))">',
                '',
            ],
            [
                '<img onerror   =alert(0)>',
                '',
            ],
            [
                '<img onabort!#$%&()*~+-_.,:;?@[/|\]^`=alert(0)>',
                '',
            ],
            [
                '<img oNmediAError=alert(0)>',
                '',
            ],
            // Works at least with IE
            [
                "<img o\0nfocus\0=alert(0)>",
                '',
            ],
            // Only whitelisted scheme names allowed in attributes.
            [
                '<img src="javascript:alert(0)">',
                '',
            ],
            [
                '<img src=javascript:alert(0)>',
                '',
            ],
            // A bit like CVE-2006-0070.
            [
                '<img src="javascript:confirm(0)">',
                '',
            ],
            [
                '<img src=`javascript:alert(0)`>',
                '',
            ],
            [
                '<img dynsrc="javascript:alert(0)">',
                '',
            ],
            [
                '<table background="javascript:alert(0)">',
                '',
            ],
            [
                '<base href="javascript:alert(0);//">',
                '',
            ],
            [
                '<img src="jaVaSCriPt:alert(0)">',
                '',
            ],
            [
                '<img src=&#106;&#97;&#118;&#97;&#115;&#99;&#114;&#105;&#112;&#116;&#58;&#97;&#108;&#101;&#114;&#116;&#40;&#48;&#41;>',
                '',
            ],
            [
                '<img src=&#00000106&#0000097&#00000118&#0000097&#00000115&#0000099&#00000114&#00000105&#00000112&#00000116&#0000058&#0000097&#00000108&#00000101&#00000114&#00000116&#0000040&#0000048&#0000041>',
                '',
            ],
            [
                '<img src=&#x6A&#x61&#x76&#x61&#x73&#x63&#x72&#x69&#x70&#x74&#x3A&#x61&#x6C&#x65&#x72&#x74&#x28&#x30&#x29>',
                '',
            ],
            [
                "<img src=\"jav\tascript:alert(0)\">",
                '',
            ],
            [
                '<img src="jav&#x09;ascript:alert(0)">',
                '',
            ],
            [
                '<img src="jav&#x000000A;ascript:alert(0)">',
                '',
            ],
            [
                "<img src=\"\n\n\nj\na\nva\ns\ncript:alert(0)\">",
                '',
            ],
            [
                "<img src=\"jav\0a\0\0cript:alert(0)\">",
                '',
            ],
            [
                '<img src="vbscript:msgbox(0)">',
                '',
            ],
            [
                '<img src="nosuchscheme:notice(0)">',
                '',
            ],
            // Netscape 4.x javascript entities.
            [
                '<br size="&{alert(0)}">',
                '<br />',
            ],
            // DRUPAL-SA-2008-006: Invalid UTF-8, these only work as reflected XSS with
            // Internet Explorer 6.
            [
                "<p arg=\"\xe0\">\" style=\"background-image: url(j\xe0avas\xc2\xa0cript:alert(0));\"\xe0<p>",
                '',
            ],
            [
                '<img src=" &#14;  javascript:alert(0)">',
                '',
            ],
        ]);
    }
}
