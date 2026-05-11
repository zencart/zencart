<?php

namespace Tests\Support;

use RuntimeException;

class TestConfigResolver
{
    public static function detectShellUser(?array $server = null): string
    {
        $server ??= $_SERVER;

        return $server['USER'] ?? $server['MY_USER'] ?? getenv('USER') ?: getenv('MY_USER') ?: 'runner';
    }

    public static function detectUser(?array $server = null): string
    {
        $server ??= $_SERVER;

        if (self::isTruthy($server['IS_DDEV_PROJECT'] ?? null) || self::isTruthy(getenv('IS_DDEV_PROJECT') ?: null)) {
            return 'ddev';
        }

        return self::detectShellUser($server);
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

    public static function resolveConfigProfile(string $context, ?string $basePath = null, ?array $server = null): string
    {
        $configPath = self::resolveConfigPath($context, $basePath, $server);
        $filename = basename($configPath);
        $suffix = '.' . $context . '.configure.php';

        if (str_ends_with($filename, $suffix)) {
            return substr($filename, 0, -strlen($suffix));
        }

        return $filename;
    }

    public static function loadConfig(string $context, ?string $basePath = null, ?array $server = null): mixed
    {
        return require self::resolveConfigPath($context, $basePath, $server);
    }

    private static function defaultBasePath(): string
    {
        return __DIR__ . '/configs';
    }

    private static function isTruthy(mixed $value): bool
    {
        if (is_bool($value)) {
            return $value;
        }

        if ($value === null) {
            return false;
        }

        $normalized = strtolower(trim((string)$value));
        return in_array($normalized, ['1', 'true', 'yes', 'on'], true);
    }
}
