<?php

namespace Tests\Support;

use RuntimeException;
use Symfony\Component\Filesystem\Filesystem;

require_once __DIR__ . '/configs/runtime_config.php';

class TestFrameworkFilesystem
{
    public function __construct(
        private readonly Filesystem $filesystem = new Filesystem(),
    ) {
    }

    public function listDebugLogFiles(string $catalogPath): array
    {
        return glob(zc_test_config_log_directory($catalogPath) . '/myDEBUG*') ?: [];
    }

    public function installPlugin(string $pluginName, string $catalogPath, string $fixturesRoot): void
    {
        $sourceDirectory = rtrim($fixturesRoot, '/') . '/not_for_release/testFramework/Support/plugins/' . $pluginName;
        if (!is_dir($sourceDirectory)) {
            throw new RuntimeException(sprintf('Plugin fixture directory not found: %s', $sourceDirectory));
        }

        $destinationDirectory = zc_test_config_plugin_directory($catalogPath, $pluginName);
        $this->filesystem->mkdir($destinationDirectory);
        $this->filesystem->mirror($sourceDirectory, $destinationDirectory);
    }

    public function removePlugin(string $pluginName, string $version, string $catalogPath): void
    {
        $pluginDirectory = zc_test_config_plugin_directory($catalogPath, $pluginName);
        $versionDirectory = $pluginDirectory . '/' . $version;

        if (!is_dir($versionDirectory)) {
            return;
        }

        $this->filesystem->remove($versionDirectory);

        $remainingEntries = glob($pluginDirectory . '/*') ?: [];
        if ($remainingEntries === []) {
            $this->filesystem->remove($pluginDirectory);
        }
    }
}
