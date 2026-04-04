<?php
/**
 * @copyright Copyright 2003-2026 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 */

namespace Tests\Unit\testsSundry;

use PHPUnit\Framework\TestCase;
use Tests\Support\Traits\DatabaseConcerns;

final class DatabaseConcernsRuntimeHarness
{
    use DatabaseConcerns;

    public static function defineRuntimeConstants(): void
    {
        self::defineDatabaseRuntimeConstants();
    }
}

class DatabaseConcernsRuntimeTest extends TestCase
{
    protected $runTestInSeparateProcess = true;
    protected $preserveGlobalState = false;

    public function testDatabaseRuntimeConstantsUseWorkerScopedLogDirectoryWhenConfigured(): void
    {
        putenv('ZC_TEST_WORKER=2');

        if (!defined('ROOTCWD')) {
            define('ROOTCWD', '/tmp/zencart/');
        }

        DatabaseConcernsRuntimeHarness::defineRuntimeConstants();

        $this->assertSame('/tmp/zencart/logs/2', DIR_FS_LOGS);
        $this->assertSame('/tmp/zencart/logs/2', DEBUG_LOG_FOLDER);
        $this->assertDirectoryExists('/tmp/zencart/logs/2');
    }
}
