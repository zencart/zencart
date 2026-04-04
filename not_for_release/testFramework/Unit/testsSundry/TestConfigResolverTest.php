<?php
/**
 * @copyright Copyright 2003-2026 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 */

namespace Tests\Unit\testsSundry;

use PHPUnit\Framework\TestCase;
use RuntimeException;
use Tests\Support\TestConfigResolver;

class TestConfigResolverTest extends TestCase
{
    private string $configDirectory;

    protected function setUp(): void
    {
        parent::setUp();

        $this->configDirectory = sys_get_temp_dir() . '/zc-test-configs-' . uniqid('', true);
        mkdir($this->configDirectory, 0777, true);
    }

    protected function tearDown(): void
    {
        foreach (glob($this->configDirectory . '/*') ?: [] as $file) {
            unlink($file);
        }

        if (is_dir($this->configDirectory)) {
            rmdir($this->configDirectory);
        }

        putenv('IS_DDEV_PROJECT');

        parent::tearDown();
    }

    public function testResolveConfigPathFallsBackToRunnerWhenUserSpecificFileIsMissing(): void
    {
        $runnerConfig = $this->configDirectory . '/runner.store.configure.php';
        file_put_contents($runnerConfig, "<?php\nreturn ['context' => 'runner-store'];\n");

        $resolvedPath = TestConfigResolver::resolveConfigPath('store', $this->configDirectory, ['USER' => 'unknown-user']);

        $this->assertSame($runnerConfig, $resolvedPath);
    }

    public function testDetectUserPrefersDdevEnvironment(): void
    {
        $this->assertSame('ddev', TestConfigResolver::detectUser(['IS_DDEV_PROJECT' => '1', 'USER' => 'runner']));
    }

    public function testLoadConfigReturnsRequiredData(): void
    {
        file_put_contents(
            $this->configDirectory . '/runner.main.configure.php',
            "<?php\nreturn ['mailserver-host' => 'localhost'];\n"
        );

        $config = TestConfigResolver::loadConfig('main', $this->configDirectory, ['USER' => 'unknown-user']);

        $this->assertSame(['mailserver-host' => 'localhost'], $config);
    }

    public function testResolveConfigPathThrowsHelpfulExceptionWhenNoFilesExist(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Unable to locate a test config for context "admin"');

        TestConfigResolver::resolveConfigPath('admin', $this->configDirectory, ['USER' => 'unknown-user']);
    }
}
