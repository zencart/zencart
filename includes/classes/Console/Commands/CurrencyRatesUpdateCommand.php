<?php
/**
 * @copyright Copyright 2003-2026 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 */

declare(strict_types=1);

namespace Zencart\Console\Commands;

use notifier;
use Zencart\Console\ConsoleCommand;
use Zencart\Console\ConsoleInput;
use Zencart\Console\ConsoleOutput;
use Zencart\Console\LegacyAdminFunctionLoader;
use Zencart\Console\TrustedPluginClassLoader;

class CurrencyRatesUpdateCommand extends ConsoleCommand
{
    /**
     * @since ZC v3.0.0
     *
     * @param null|callable(string): ?array<string, mixed> $configurationProvider
     * @param null|callable(): array<string, string> $trustedPluginResolver
     */
    public function __construct(
        private $configurationProvider = null,
        private ?LegacyAdminFunctionLoader $legacyAdminFunctionLoader = null,
        private $trustedPluginResolver = null,
        private ?TrustedPluginClassLoader $trustedPluginClassLoader = null
    ) {
    }

    /**
     * @since ZC v3.0.0
     */
    public function getName(): string
    {
        return 'currency-rates:update';
    }

    /**
     * @since ZC v3.0.0
     */
    public function getDescription(): string
    {
        return 'Update currency exchange rates from configured sources.';
    }

    /**
     * @since ZC v3.0.0
     */
    public function getUsageLines(): array
    {
        return [
            'php zc_cli.php currency-rates:update',
        ];
//        echo 'Zen Cart(tm) Currency Updater cron script.' . "\n\n";
//        echo "- Recommend running *infrequently*, as running too often is usually unnecessary.\n  Suggest once or twice per week, or maybe once or twice per day.\n  Hourly is fine, but is rarely necessary.\n";
    }

    /**
     * @since ZC v3.0.0
     */
    public function handle(ConsoleInput $input, ConsoleOutput $output): int
    {
        if (!\function_exists('zenDoCurlRequest')) {
            require_once \DIR_FS_CATALOG . 'includes/functions/functions_communications.php';
        }
        if (!\function_exists('zen_update_currencies')) {
            require_once \DIR_FS_CATALOG . 'includes/functions/functions_exchange_rates.php';
        }
        if (!\function_exists('zen_update_currencies')) {
            $output->errorln('Unable to find zen_update_currencies() function.');
            return 1;
        }
        if ($this->configurationProvider === null) {
            $output->errorln('Configuration lookup unavailable in the current CLI runtime.');
            return 1;
        }

        $context = zc_cli_get_db_context();
        if (empty($context['db'])) {
            $output->errorln('Database connection unavailable in the current CLI runtime.');
            return 1;
        }
        // make $db available globally since the currency update functions depend on it.
        global $db;
        $db = $context['db'];

        // Define dependent configuration constants if not already defined
        if (!defined('DEFAULT_CURRENCY')) {
            $row = ($this->configurationProvider)('DEFAULT_CURRENCY');
            if ($row !== null) {
                define('DEFAULT_CURRENCY', $row['configuration_value']);
            } else {
                $output->errorln('Unable to determine configuration settings.');
                return 1;
            }
        }
        if (!defined('CURRENCY_SERVER_PRIMARY')) {
            $row = ($this->configurationProvider)('CURRENCY_SERVER_PRIMARY');
            if ($row !== null) {
                define('CURRENCY_SERVER_PRIMARY', $row['configuration_value']);
            } else {
                $output->errorln('Unable to determine configuration settings.');
                return 1;
            }
        }
        if (!defined('CURRENCY_SERVER_BACKUP')) {
            $row = ($this->configurationProvider)('CURRENCY_SERVER_BACKUP');
            if ($row !== null) {
                define('CURRENCY_SERVER_BACKUP', $row['configuration_value']);
            } else {
                $output->errorln('Unable to determine configuration settings.');
                return 1;
            }
        }
        if (!defined('CURRENCY_UPLIFT_RATIO')) {
            $row = ($this->configurationProvider)('CURRENCY_UPLIFT_RATIO');
            if ($row !== null) {
                define('CURRENCY_UPLIFT_RATIO', $row['configuration_value']);
            } else {
                $output->errorln('Unable to determine configuration settings.');
                return 1;
            }
        }

        $trustedPlugins = [];
        if ($this->trustedPluginResolver !== null) {
            $trustedPlugins = ($this->trustedPluginResolver)();
        } elseif (\function_exists('zc_cli_resolve_trusted_plugin_versions')) {
            $trustedPluginContext = zc_cli_resolve_trusted_plugin_versions();
            $trustedPlugins = $trustedPluginContext['plugins'] ?? [];
        }

        ($this->trustedPluginClassLoader ?? new TrustedPluginClassLoader())->registerPluginClassNamespaces($trustedPlugins);
        ($this->legacyAdminFunctionLoader ?? new LegacyAdminFunctionLoader())->loadExtraFunctions($trustedPlugins);

        // NOTE: This isn't necessarily going to work since it's not running in a normal Admin context the way the legacy implementation did.
        global $zco_notifier;
        $zco_notifier = new notifier;

        if ($input->isVerboseRequested()) {
            $output->writeln('Starting currency rates update...');
        }

        defined('TEXT_INFO_CURRENCY_UPDATED') || define('TEXT_INFO_CURRENCY_UPDATED', 'The exchange rate for %1$s (%2$s) was updated successfully to %3$s via %4$s.');
        defined('ERROR_CURRENCY_INVALID') || define('ERROR_CURRENCY_INVALID', 'Error: The exchange rate for %1$s (%2$s) was not updated via %3$s. Is it a valid currency code?');
        defined('WARNING_PRIMARY_SERVER_FAILED') || define('WARNING_PRIMARY_SERVER_FAILED', 'Warning: Primary exchange-rate server %1$s failed for %2$s (%3$s).');

        zen_update_currencies($input->isVerboseRequested());

        if ($input->isVerboseRequested()) {
            $output->writeln('Done.');
        }

        return 0;
    }
}
