<?php
/**
 * @copyright Copyright 2003-2026 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 */

namespace Tests\Unit\testsSundry;

use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use PHPUnit\Framework\TestCase;
use Tests\Support\Traits\GeneralConcerns;

#[RunTestsInSeparateProcesses]
class GeneralConcernsTest extends TestCase
{

    public function testLoadConfigureFileStillLoadsContextWhenHttpServerExistsButDbConstantsDoNot(): void
    {
        $basePath = sys_get_temp_dir() . '/zc-general-concerns-' . uniqid('', true);
        mkdir($basePath . '/Support/configs', 0777, true);
        file_put_contents(
            $basePath . '/Support/configs/runner.configure.php',
            <<<'PHP'
<?php
if (!defined('DB_TYPE')) {
    define('DB_TYPE', 'mysql');
}
return ['loaded' => true];
PHP
        );

        $rootPath = realpath(__DIR__ . '/../../../..');
        $command = sprintf(
            'php -r %s',
            escapeshellarg(<<<PHP
require {$this->exportString($rootPath . '/vendor/autoload.php')};
define('TESTCWD', {$this->exportString($basePath . '/')});
define('HTTP_SERVER', 'https://already-defined.test');
\$loader = new class {
    use \Tests\Support\Traits\GeneralConcerns;
};
\$loader::loadConfigureFile('store');
echo defined('DB_TYPE') ? DB_TYPE : 'missing';
PHP)
        );

        exec($command . ' 2>&1', $output, $exitCode);

        $this->assertSame(0, $exitCode, implode(PHP_EOL, $output));
        $this->assertSame(['mysql'], $output);

        unlink($basePath . '/Support/configs/runner.configure.php');
        rmdir($basePath . '/Support/configs');
        rmdir($basePath . '/Support');
        rmdir($basePath);
    }

    private function exportString(string $value): string
    {
        return var_export($value, true);
    }
}
