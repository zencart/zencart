<?php
/**
 * @copyright Copyright 2003-2026 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 */

namespace Tests\Unit\testsSundry;

use PHPUnit\Framework\TestCase;
use Tests\Support\Traits\GeneralConcerns;

class GeneralConcernsTest extends TestCase
{
    protected $runTestInSeparateProcess = true;
    protected $preserveGlobalState = false;

    public function testLoadConfigureFileStillLoadsContextWhenHttpServerExistsButDbConstantsDoNot(): void
    {
        $basePath = sys_get_temp_dir() . '/zc-general-concerns-' . uniqid('', true);
        mkdir($basePath . '/Support/configs', 0777, true);
        file_put_contents(
            $basePath . '/Support/configs/runner.store.configure.php',
            <<<'PHP'
<?php
if (!defined('DB_TYPE')) {
    define('DB_TYPE', 'mysql');
}
return ['loaded' => true];
PHP
        );

        if (!defined('TESTCWD')) {
            define('TESTCWD', $basePath . '/');
        }

        define('HTTP_SERVER', 'https://already-defined.test');
        $this->assertFalse(defined('DB_TYPE'));

        $loader = new class {
            use GeneralConcerns;
        };

        $loader::loadConfigureFile('store');

        $this->assertTrue(defined('DB_TYPE'));
        $this->assertSame('mysql', DB_TYPE);

        unlink($basePath . '/Support/configs/runner.store.configure.php');
        rmdir($basePath . '/Support/configs');
        rmdir($basePath . '/Support');
        rmdir($basePath);
    }
}
