<?php
/**
 * @copyright Copyright 2003-2026 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 */

namespace Tests\Unit\testsSundry;

use PHPUnit\Framework\TestCase;
use Tests\Support\UnitTestBootstrap;

class UnitTestBootstrapTest extends TestCase
{
    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testInitializeDefinesExpectedStateWithoutEval(): void
    {
        UnitTestBootstrap::initialize();

        $this->assertTrue(defined('ZENCART_TESTFRAMEWORK_RUNNING'));
        $this->assertTrue(defined('DIR_FS_CATALOG'));
        $this->assertTrue(function_exists('zen_session_name'));
        $this->assertTrue(function_exists('zen_session_id'));
        $this->assertSame('zenid', \zen_session_name());
        $this->assertSame('1234567890', \zen_session_id());
        $this->assertArrayHasKey('zco_notifier', $GLOBALS);
        $this->assertInstanceOf(\notifier::class, $GLOBALS['zco_notifier']);
    }
}
