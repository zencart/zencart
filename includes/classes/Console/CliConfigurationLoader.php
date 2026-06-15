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

    public function bootstrap(mixed $db): void
    {
        global $configurationRepository;
        global $productTypeLayoutRepository;

        if (!$db instanceof \queryFactory) {
            return;
        }

        $configurationRepository = $this->configurationRepository ?? new ConfigurationRepository($db);
        $productTypeLayoutRepository = $this->productTypeLayoutRepository ?? new ProductTypeLayoutRepository($db);

        $configurationRepository->loadConfigSettings();
        $productTypeLayoutRepository->loadConfigSettings();

        if (!function_exists('zen_config')) {
            require_once DIR_FS_CATALOG . 'includes/functions/zen_config.php';
        }
    }
}
