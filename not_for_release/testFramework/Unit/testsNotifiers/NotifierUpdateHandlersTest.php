<?php
/**
 * @copyright Copyright 2003-2020 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 */

namespace Tests\Unit\testsNotifiers;

use Tests\Support\zcNotifierTestObserver;
use Tests\Support\zcUnitTestCase;


class NotifierUpdateHandlersTest extends zcUnitTestCase
{
    protected $preserveGlobalState = FALSE;
    protected $runTestInSeparateProcess = TRUE;

    protected $base;

    public function setUp(): void
    {
        parent::setUp();
        require_once TESTCWD . 'Support/zcNotifierTestObserver.php';
        $this->base = new \base;
    }

    public function testObserverUpdateHandlers()
    {
        $observer = new zcNotifierTestObserver;
        $testVar = 'foo';
        $this->base->notify('NOTIFY_TEST_SNAKE_CASE', null, $testVar);
        $this->assertEquals('snake', $testVar);

        $testVar = 'foo';
        $this->base->notify('NOTIFY_TEST_CAMEL_CASE', null, $testVar);
        $this->assertEquals('camel', $testVar);

        $testVar = 'foo';
        $this->base->notify('NOTIFY_TEST_UPDATE', null, $testVar);
        $this->assertEquals('update', $testVar);
    }
}
