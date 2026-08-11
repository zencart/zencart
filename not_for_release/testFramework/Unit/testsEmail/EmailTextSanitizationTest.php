<?php
declare(strict_types=1);

/**
 * @copyright Copyright 2003-2026 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 */

namespace Tests\Unit\testsEmail;

use Tests\Support\zcUnitTestCase;

/**
 * Characterization tests for Email::sanitizeTextContent(), which builds the text/plain part
 * of every outgoing email.
 *
 * Everything here asserts intended behaviour, and is expected to keep passing when the
 * tag-stripping branch is rewritten. Cases where that branch is currently arbitrary - handling
 * that depends on a tag's letter case, and closing tags that come back with the slash dropped -
 * are deliberately left unasserted rather than pinned, so that nothing in this file has to be
 * unlearned later.
 *
 * The disclaimer branch is deliberately not covered in this class: it depends on
 * EMAIL_DISCLAIMER constants, and constants cannot be redefined between tests without process isolation.
 * All tests here pass a transactional $module, which skips that branch entirely.
 */
class EmailTextSanitizationTest extends zcUnitTestCase
{
    /**
     * Any module NOT listed in Email::isNonTransactional(), so no disclaimers are appended.
     */
    private const TRANSACTIONAL_MODULE = 'checkout';

    /**
     * The default value shipped in mysql_utf8.sql for CURRENCIES_TRANSLATIONS.
     */
    private const SHIPPED_DEFAULT = '&pound;,£:&euro;,€:&reg;,®:&trade;,™';

    public function setUp(): void
    {
        parent::setUp();
        require_once DIR_FS_CATALOG . 'includes/classes/Email.php';

        // CHARSET is always the same value here, so defining it needs no process isolation.
        defined('CHARSET') || define('CHARSET', 'utf-8');
        // The html-fallback branch calls zen_output_string_protected(), which the unit
        // bootstrap does not load, and which needs CHARSET for htmlspecialchars().
        require_once DIR_FS_CATALOG . 'includes/functions/functions_strings.php';
    }

    public function tearDown(): void
    {
        unset($GLOBALS['configurationRepository'], $GLOBALS['productTypeLayoutRepository']);
        parent::tearDown();
    }

    /**
     * Run the sanitizer with CURRENCIES_TRANSLATIONS stubbed to $config.
     *
     * zen_config() reads $configurationRepository->get() and only falls back to constants when
     * that global is unset, so stubbing it here avoids define() and therefore avoids needing
     * process isolation.
     */
    private function sanitize(
        string $text,
        string $config = '',
        string $module = self::TRANSACTIONAL_MODULE,
        string $htmlFallback = ''
    ): string {
        $GLOBALS['configurationRepository'] = new class ($config) {
            public function __construct(private string $config) {}

            public function get(string $key): mixed
            {
                return $key === 'CURRENCIES_TRANSLATIONS' ? $this->config : null;
            }
        };
        $GLOBALS['productTypeLayoutRepository'] = new class {
            public function get(string $key): mixed
            {
                return null;
            }
        };

        // Anonymous subclass so the protected sanitizer is reachable.
        // It must be declared here rather than at file scope, because the parent class is only loaded in setUp().
        $sanitizer = new class extends \Email {
            public function sanitize(string $text, string $htmlFallback, string $module): string
            {
                return $this->sanitizeTextContent($text, $htmlFallback, $module, 'customer@example.com');
            }
        };

        return $sanitizer->sanitize($text, $htmlFallback, $module);
    }

    public function testShippedDefaultConfigConvertsAllFourEntities(): void
    {
        $actual = $this->sanitize('Total &pound;10 and &euro;20 for ZenCart&trade; &reg;', self::SHIPPED_DEFAULT);

        self::assertSame('Total £10 and €20 for ZenCart™ ®', $actual);
    }

    /**
     * Regression test for the infinite loop fixed in PR #7950.
     *
     * NOTE: if that fix is reverted, this test HANGS the suite rather than failing cleanly -
     * PHPUnit has no portable per-test timeout. A suite that stops here is this defect, not
     * flaky infrastructure.
     */
    public function testSelfReplacingTranslationReturnsInsteadOfHanging(): void
    {
        $actual = $this->sanitize('Pay USD now', 'USD,USD');

        self::assertSame('Pay USD now', $actual);
    }

    public function testTranslationAtStartOfBodyIsApplied(): void
    {
        $actual = $this->sanitize('&pound;10 is due', self::SHIPPED_DEFAULT);

        self::assertSame('£10 is due', $actual);
    }

    /**
     * Current behaviour, deliberately retained: a pair with an empty replacement stops the
     * loop, discarding that pair AND every pair after it. Changing this would turn a config
     * typo into silent deletion of email content, so it is pinned as-is.
     */
    public function testMalformedPairDiscardsRemainingTranslations(): void
    {
        $actual = $this->sanitize('&pound;10 &reg; &euro;5', '&pound;,£:&reg;,:&euro;,€');

        self::assertSame('£10 &reg; &euro;5', $actual);
    }

    public function testTrailingSearchTermWithoutReplacementIsIgnored(): void
    {
        $actual = $this->sanitize('&pound;10 &reg;', '&pound;,£:&reg;');

        self::assertSame('£10 &reg;', $actual);
    }

    public function testEmptySearchTermIsSkipped(): void
    {
        $actual = $this->sanitize('&euro;5 due', ',£:&euro;,€');

        self::assertSame('€5 due', $actual);
    }

    public function testEmptyConfigStillAppliesSpecialCharacterCleanup(): void
    {
        $actual = $this->sanitize('He said &quot;hi&quot;', '');

        self::assertSame('He said "hi"', $actual);
    }

    public function testDecodesSpecialCharacterEntities(): void
    {
        $actual = $this->sanitize('&quot;q&quot; &lt;x&gt; a&nbsp;b c&#8209;d');

        self::assertSame('"q" <x> a b c-d', $actual);
    }

    public function testCollapsesRunsOfAmpersands(): void
    {
        $actual = $this->sanitize('A &amp;&amp; B & C');

        self::assertSame('A & B & C', $actual);
    }

    /**
     * The replacement set maps "\x00" to a space, but on every normal module that mapping is
     * unreachable: the tag-stripping branch runs first and strip_tags() deletes NUL outright,
     * leaving nothing to map. Nulls are therefore removed, not spaced.
     */
    public function testNullBytesAreRemovedBeforeTheNullMappingCanApply(): void
    {
        $actual = $this->sanitize("before\x00after");

        self::assertSame('beforeafter', $actual);
    }

    /**
     * ...and 'xml_record' is the one module that skips the tag-stripping branch, so it is the
     * only path on which the "\x00" => ' ' mapping actually fires.
     */
    public function testNullBytesBecomeSpacesForXmlRecordModule(): void
    {
        $actual = $this->sanitize("before\x00after", '', 'xml_record');

        self::assertSame('before after', $actual);
    }

    /**
     * Currency pairs and the fixed entity set share one str_replace() call, so ordering within
     * that call is load-bearing: a replacement that produces '&lt;' must still be decoded to
     * '<' afterwards.
     */
    public function testCurrencyTranslationsAreAppliedBeforeEntityDecoding(): void
    {
        $actual = $this->sanitize('&pound;', '&pound;,&lt;');

        self::assertSame('<', $actual);
    }

    // ------------------------------------------------------------------------------------
    // The html-fallback branch: taken when no text part was supplied, so the text/plain part
    // is derived from the HTML body. Unlike the branch below, this one strips ALL tags.
    // ------------------------------------------------------------------------------------

    public function testHtmlFallbackTurnsBreaksAndParagraphEndsIntoNewlines(): void
    {
        $actual = $this->sanitize('', '', self::TRANSACTIONAL_MODULE, '<p>One</p><p>Two</p>');

        self::assertSame("One\nTwo\n", $actual);
    }

    public function testHtmlFallbackStripsEveryTag(): void
    {
        $actual = $this->sanitize('', '', self::TRANSACTIONAL_MODULE, '<div><strong>bold</strong> and <em>italic</em></div>');

        self::assertSame('bold and italic', $actual);
    }

    /**
     * Current behaviour worth noting: this branch runs the text through
     * zen_output_string_protected() (htmlspecialchars, ENT_COMPAT), but the entity-decoding
     * step further down converts &quot; &lt; &gt; straight back again. The protective
     * encoding is therefore undone before the text is sent. This intentional, and deduplicates.
     */
    public function testHtmlFallbackProtectiveEncodingIsUndoneByLaterEntityDecoding(): void
    {
        $actual = $this->sanitize('', '', self::TRANSACTIONAL_MODULE, '<p>Say "hi"</p>');

        self::assertSame("Say \"hi\"\n", $actual);
    }

    /**
     * 'xml_record' skips the strip_tags/encode step, keeping the markup intact.
     */
    public function testHtmlFallbackForXmlRecordKeepsMarkup(): void
    {
        $actual = $this->sanitize('', '', 'xml_record', '<p>One</p>');

        self::assertSame("<p>One</p>\n", $actual);
    }

    public function testEmptyTextAndEmptyHtmlFallbackYieldsEmptyString(): void
    {
        self::assertSame('', $this->sanitize('', '', self::TRANSACTIONAL_MODULE, ''));
    }

    // ------------------------------------------------------------------------------------
    // Tag handling in the text/plain part.
    //
    // These assert the contract that holds regardless of how the tag-stripping branch is
    // implemented: formatting tags and script tags come out, their readable content stays.
    // Cases where today's regex behaves arbitrarily (letter-case dependence, mangled closing
    // tags) are deliberately NOT asserted here - see the tag-stripping fix for those.
    // ------------------------------------------------------------------------------------

    public function testStripsFormattingTagsFromThePlainTextPart(): void
    {
        self::assertSame('Hello bold world', $this->sanitize('Hello <strong>bold</strong> world'));
        self::assertSame('Link x', $this->sanitize('Link <a href="http://x">x</a>'));
        self::assertSame('Linebreak', $this->sanitize('Line<br />break'));
    }

    /**
     * Script tags are removed, but their contents survive as plain text.
     */
    public function testStripsScriptTagsButKeepsTheirBodyText(): void
    {
        self::assertSame('alert(1)', $this->sanitize('<script>alert(1)</script>'));
    }

    public function testRemovesIframeTagsEntirely(): void
    {
        self::assertSame('', $this->sanitize('<iframe src="evil"></iframe>'));
    }

    /**
     * A literal '<' that is not part of a tag must survive - a product name such as
     * "Widget <Large>" was truncated before CHANGE-417 (2013) added the placeholder step.
     */
    public function testLiteralAngleBracketsInContentSurvive(): void
    {
        self::assertSame('Widget <Large> shirt', $this->sanitize('Widget <Large> shirt'));
        self::assertSame('Qty 5 < 6 units', $this->sanitize('Qty 5 < 6 units'));
    }
}
