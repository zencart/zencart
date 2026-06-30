<?php
/**
 * @copyright Copyright 2003-2026 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 */

use Tests\Support\zcUnitTestCase;

if (!defined('CHARSET')) {
    define('CHARSET', 'utf-8');
}

/**
 * @see admin/includes/classes/message_stack.php
 */
class AdminOutputEscapingTest extends zcUnitTestCase
{
    public function setUp(): void
    {
        if (!defined('IS_ADMIN_FLAG')) {
            define('IS_ADMIN_FLAG', true);
        }
        parent::setUp();
        require_once DIR_FS_CATALOG . DIR_WS_FUNCTIONS . 'functions_strings.php';
    }

    public function testZenOutputStringProtectedNeutralizesUnquotedEventHandlerBypass(): void
    {
        $escaped = zen_output_string_protected('<input autofocus onfocus=alert(document.cookie)>');

        $this->assertStringNotContainsString('<', $escaped, 'A raw "<" surviving means a new HTML element could still be formed.');
        $this->assertStringNotContainsString('>', $escaped, 'A raw ">" surviving means a new HTML element could still be formed.');
    }

    public function testZenOutputStringProtectedNeutralizesScriptTag(): void
    {
        $escaped = zen_output_string_protected('<script>alert(document.cookie)</script>');

        $this->assertStringNotContainsString('<', $escaped);
        $this->assertStringNotContainsString('>', $escaped);
    }

    public function testZenOutputStringProtectedNeutralizesSvgOnload(): void
    {
        $escaped = zen_output_string_protected('<svg onload=alert(1)>');

        $this->assertStringNotContainsString('<', $escaped);
        $this->assertStringNotContainsString('>', $escaped);
    }

    public function testZenOutputStringProtectedLeavesOrdinaryTextUnchanged(): void
    {
        $this->assertSame('SUMMER2026', zen_output_string_protected('SUMMER2026'));
        $this->assertSame("O'Brien &amp; Sons", zen_output_string_protected("O'Brien & Sons"));
    }

    /**
     * In admin context zen_output_string_protected() must not double-encode a value
     */
    public function testZenOutputStringProtectedDoesNotDoubleEncodeAlreadyEscapedValues(): void
    {
        $alreadyEncoded = htmlspecialchars("O'Brien & Sons \"Cafe\"", ENT_COMPAT, 'UTF-8', true);
        $this->assertSame($alreadyEncoded, zen_output_string_protected($alreadyEncoded));
    }
}
