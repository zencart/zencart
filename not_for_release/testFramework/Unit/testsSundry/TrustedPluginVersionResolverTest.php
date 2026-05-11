<?php
/**
 * @copyright Copyright 2003-2026 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 */

namespace Tests\Unit\testsSundry;

use PHPUnit\Framework\TestCase;
use Tests\Support\UnitTestBootstrap;
use Zencart\Console\TrustedPluginVersionResolver;
use Zencart\DbRepositories\PluginControlRepository;
use Zencart\PluginSupport\PluginStatus;

class TrustedPluginVersionResolverTest extends TestCase
{
    protected $preserveGlobalState = false;

    public static function setUpBeforeClass(): void
    {
        UnitTestBootstrap::initialize();
        require_once DIR_FS_CATALOG . 'includes/classes/vendors/AuraAutoload/src/Loader.php';
        $psr4Autoloader = new \Aura\Autoload\Loader();
        $psr4Autoloader->register();
        require DIR_FS_CATALOG . 'includes/psr4Autoload.php';
    }

    public function testResolvesOnlyEnabledPluginVersions(): void
    {
        $repository = $this->createMock(PluginControlRepository::class);
        $repository->expects($this->once())
            ->method('getInstalledPlugins')
            ->with(PluginStatus::ENABLED)
            ->willReturn([
                'enabledPlugin' => [
                    'unique_key' => 'enabledPlugin',
                    'version' => 'v1.2.3',
                    'status' => PluginStatus::ENABLED,
                ],
                'disabledPlugin' => [
                    'unique_key' => 'disabledPlugin',
                    'version' => 'v9.9.9',
                    'status' => PluginStatus::DISABLED,
                ],
            ]);

        $resolver = new TrustedPluginVersionResolver($repository);

        $this->assertSame(
            [
                'enabledPlugin' => 'v1.2.3',
                'disabledPlugin' => 'v9.9.9',
            ],
            $resolver->resolveEnabledPluginVersions()
        );
    }

    public function testIgnoresRowsMissingKeyOrVersion(): void
    {
        $repository = $this->createMock(PluginControlRepository::class);
        $repository->expects($this->once())
            ->method('getInstalledPlugins')
            ->with(PluginStatus::ENABLED)
            ->willReturn([
                'missingVersion' => [
                    'unique_key' => 'missingVersion',
                    'status' => PluginStatus::ENABLED,
                ],
                'missingKey' => [
                    'version' => 'v2.0.0',
                    'status' => PluginStatus::ENABLED,
                ],
                'validPlugin' => [
                    'unique_key' => 'validPlugin',
                    'version' => 'v3.0.0',
                    'status' => PluginStatus::ENABLED,
                ],
            ]);

        $resolver = new TrustedPluginVersionResolver($repository);

        $this->assertSame(
            [
                'validPlugin' => 'v3.0.0',
            ],
            $resolver->resolveEnabledPluginVersions()
        );
    }
}
