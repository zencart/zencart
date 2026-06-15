<?php
/**
 * @copyright Copyright 2003-2026 Zen Cart Development Team
 * @license https://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 */

declare(strict_types=1);

namespace Zencart\Console;

use Zencart\DbRepositories\ConfigurationRepository;
use Zencart\DbRepositories\ProductTypeLayoutRepository;

class CliConfigurationLoader
{
    public function __construct(
        private ?ConfigurationRepository $configurationRepository = null,
        private ?ProductTypeLayoutRepository $productTypeLayoutRepository = null
    ) {
    }

    public function bootstrap(mixed $database): void
    {
        global $configurationRepository;
        global $productTypeLayoutRepository;

        if (!$database instanceof \queryFactory) {
            return;
        }

        $GLOBALS['db'] = $database;
        $configurationRepository = $this->configurationRepository ?? new ConfigurationRepository($database);
        $productTypeLayoutRepository = $this->productTypeLayoutRepository ?? new ProductTypeLayoutRepository($database);

        $configurationRepository->loadConfigSettings();
        $productTypeLayoutRepository->loadConfigSettings();

        if (!function_exists('zen_config')) {
            require_once DIR_FS_CATALOG . 'includes/functions/zen_config.php';
        }
    }
}
