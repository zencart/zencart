<?php
/**
 * @copyright Copyright 2003-2026 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 */

namespace Tests\Unit\testsSundry;

use Tests\Support\zcUnitTestCase;

class RenderedJscriptFrameworkTest extends zcUnitTestCase
{
    public function testRenderedJscriptFrameworkUsesCsrfHeaderForObjectPayloads(): void
    {
        $_SESSION = ['securityToken' => 'header-token-123'];

        ob_start();
        require dirname(__DIR__) . '/fixtures/render_jscript_framework.php';
        $output = (string) ob_get_clean();

        $this->assertStringContainsString("const csrfToken = 'header-token-123';", $output);
        $this->assertStringContainsString("'X-CSRF-Token': csrfToken", $output);
        $this->assertStringContainsString("data: options.data,", $output);
        $this->assertStringContainsString("options.data += '&securityToken=' + encodeURIComponent(csrfToken);", $output);
        $this->assertStringNotContainsString("data: jQuery.extend(true, {}, options.data, {securityToken:", $output);
    }
}
