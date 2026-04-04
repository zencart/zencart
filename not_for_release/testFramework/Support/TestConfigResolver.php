<?php

namespace Tests\Support;

use RuntimeException;

class TestConfigResolver
{
    public static function detectUser(?array $server = null): string
    {
        $server ??= $_SERVER;

        if (!empty($server['IS_DDEV_PROJECT']) || getenv('IS_DDEV_PROJECT')) {
            return 'ddev';
        }

        return $server['USER'] ?? $server['MY_USER'] ?? 'runner';
    }

    public static function resolveConfigPath(string $context, ?string $basePath = null, ?array $server = null): string
    {
        $basePath = rtrim($basePath ?? self::defaultBasePath(), '/') . '/';
        $user = self::detectUser($server);
        $candidates = array_unique([$user, 'ddev', 'runner']);
        $pathsTried = [];

        foreach ($candidates as $candidate) {
            $candidatePath = $basePath . $candidate . '.' . $context . '.configure.php';
            $pathsTried[] = $candidatePath;

            if (file_exists($candidatePath)) {
                return $candidatePath;
            }
        }

        throw new RuntimeException(
            sprintf(
                'Unable to locate a test config for context "%s". Tried: %s',
                $context,
                implode(', ', $pathsTried)
            )
        );
    }

    public static function loadConfig(string $context, ?string $basePath = null, ?array $server = null): mixed
    {
        return require self::resolveConfigPath($context, $basePath, $server);
    }

    private static function defaultBasePath(): string
    {
        return __DIR__ . '/configs';
    }
}
