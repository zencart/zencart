<?php
/**
 * @copyright Copyright 2003-2026 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 */

namespace Tests\Unit\testsSundry;

use Tests\Support\zcUnitTestCase;

class CsrfTokenRequestTest extends zcUnitTestCase
{
    public function setUp(): void
    {
        parent::setUp();
        require_once DIR_FS_CATALOG . 'includes/functions/functions_general_shared.php';

        $_SESSION = ['securityToken' => 'session-token'];
        $_POST = [];
        unset($_SERVER['HTTP_X_CSRF_TOKEN']);
    }

    public function testHeaderTokenIsPreferredWhenItMatchesPostToken(): void
    {
        $_SERVER['HTTP_X_CSRF_TOKEN'] = 'session-token';
        $_POST['securityToken'] = 'session-token';

        $this->assertSame('session-token', zen_get_csrf_token_from_request());
        $this->assertTrue(zen_request_has_valid_csrf_token());
    }

    public function testHeaderOnlyTokenIsAccepted(): void
    {
        $_SERVER['HTTP_X_CSRF_TOKEN'] = 'session-token';

        $this->assertSame('session-token', zen_get_csrf_token_from_request());
        $this->assertTrue(zen_request_has_valid_csrf_token());
    }

    public function testPostTokenRemainsSupported(): void
    {
        $_POST['securityToken'] = 'session-token';

        $this->assertSame('session-token', zen_get_csrf_token_from_request());
        $this->assertTrue(zen_request_has_valid_csrf_token());
    }

    public function testMismatchedHeaderAndPostTokensFailClosed(): void
    {
        $_SERVER['HTTP_X_CSRF_TOKEN'] = 'session-token';
        $_POST['securityToken'] = 'different-token';

        $this->assertNull(zen_get_csrf_token_from_request());
        $this->assertFalse(zen_request_has_valid_csrf_token());
    }
}
