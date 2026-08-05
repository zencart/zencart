<?php
/**
 * @copyright Copyright 2003-2026 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 */

declare(strict_types=1);

namespace Zencart\Console;

class LegacyAdminFunctionLoader
{
    /**
     * @param array<string, string> $trustedPlugins
     */
    public function loadExtraFunctions(array $trustedPlugins): void
    {
        $this->requireProviderFiles(DIR_FS_ADMIN . 'includes/functions/extra_functions/*.php');

        foreach ($trustedPlugins as $uniqueKey => $version) {
            $this->requireProviderFiles(
                DIR_FS_CATALOG . 'zc_plugins/' . $uniqueKey . '/' . $version . '/admin/includes/functions/extra_functions/*.php'
            );
        }
    }

    private function requireProviderFiles(string $pattern): void
    {
        foreach (glob($pattern) ?: [] as $file) {
            if (!$this->definesQuoteFunction($file)) {
                continue;
            }

            require_once $file;
        }
    }

    private function definesQuoteFunction(string $file): bool
    {
        $contents = @file_get_contents($file);
        if ($contents === false) {
            return false;
        }

        return preg_match('/function\s+quote_[a-z0-9_]+_currency\s*\(/i', $contents) === 1;
    }
}
