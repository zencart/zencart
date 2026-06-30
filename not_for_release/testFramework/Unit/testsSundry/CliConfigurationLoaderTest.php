<?php
/**
 * @copyright Copyright 2003-2026 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 */

namespace Tests\Unit\testsSundry;

use PHPUnit\Framework\TestCase;
use Tests\Support\UnitTestBootstrap;
use Zencart\Console\CliConfigurationLoader;
use Zencart\DbRepositories\ConfigurationRepository;
use Zencart\DbRepositories\ProductTypeLayoutRepository;

class CliConfigurationLoaderTest extends TestCase
{

    public static function setUpBeforeClass(): void
    {
        UnitTestBootstrap::initialize();
        require_once DIR_FS_CATALOG . 'includes/classes/Console/CliConfigurationLoader.php';
    }

    /**
     * @runInSeparateProcess
     */
    public function testBootstrapLoadsRepositoriesIntoZenConfig(): void
    {
        $db = new \queryFactory();
        $configurationRepository = new class ($db) extends ConfigurationRepository {
            public bool $loaded = false;

            public function loadConfigSettings(): void
            {
                $this->loaded = true;
            }

            public function get(string $configurationKey): mixed
            {
                return $configurationKey === 'CURL_PROXY_REQUIRED' ? 'True' : null;
            }
        };

        $productTypeLayoutRepository = new class ($db) extends ProductTypeLayoutRepository {
            public bool $loaded = false;

            public function loadConfigSettings(): void
            {
                $this->loaded = true;
            }

            public function get(string $configurationKey): mixed
            {
                return null;
            }
        };

        $loader = new CliConfigurationLoader($configurationRepository, $productTypeLayoutRepository);

        $loader->bootstrap($db);

        $this->assertSame($db, $GLOBALS['db']);
        $this->assertTrue($configurationRepository->loaded);
        $this->assertTrue($productTypeLayoutRepository->loaded);
        $this->assertTrue(function_exists('zen_config'));
        $this->assertSame('True', \zen_config('CURL_PROXY_REQUIRED'));
    }
}
