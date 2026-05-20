<?php
/**
 * @copyright Copyright 2003-2026 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 */

namespace Zencart\Console\Commands;

use notifier;
use queryFactory;
use Zencart\Console\ConsoleCommand;
use Zencart\Console\ConsoleInput;
use Zencart\Console\ConsoleOutput;

class CurrencyRatesUpdateCommand extends ConsoleCommand
{
    /**
     * @since ZC v3.0.0
     *
     * @param null|callable(string): ?array<string, mixed> $configurationProvider
     */
    public function __construct(private $configurationProvider = null)
    {
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
        if (!\function_exists('zen_update_currencies')) {
            require_once \DIR_FS_CATALOG . 'includes/functions/functions_exchange_rates.php';
        }
        if (!\function_exists('zen_update_currencies')) {
            $output->errorln('Unable to find zen_update_currencies() function.');
            return 1;
        }

        // Instantiate $db connection
        require_once \DIR_FS_CATALOG . 'includes/classes/db/' .DB_TYPE . '/query_factory.php';
        global $db;
        $db = new queryFactory();
        if (!$db->connect(DB_SERVER, DB_SERVER_USERNAME, DB_SERVER_PASSWORD, DB_DATABASE, 'unused', true)) {
            $output->errorln('Unable to connect to database. Please check your database configuration and try again.');
            return 1;
        }

        // Define dependent configuration constants if not already defined
        if (!defined('DEFAULT_CURRENCY')) {
            $row = ($this->configurationProvider)('DEFAULT_CURRENCY');
            if ($row !== null) {
                define('DEFAULT_CURRENCY', $row['configuration_value']);
            }
        }
        if (!defined('CURRENCY_SERVER_PRIMARY')) {
            $row = ($this->configurationProvider)('CURRENCY_SERVER_PRIMARY');
            if ($row !== null) {
                define('CURRENCY_SERVER_PRIMARY', $row['configuration_value']);
            }
        }
        if (!defined('CURRENCY_SERVER_BACKUP')) {
            $row = ($this->configurationProvider)('CURRENCY_SERVER_BACKUP');
            if ($row !== null) {
                define('CURRENCY_SERVER_BACKUP', $row['configuration_value']);
            }
        }
        if (!defined('CURRENCY_UPLIFT_RATIO')) {
            $row = ($this->configurationProvider)('CURRENCY_UPLIFT_RATIO');
            if ($row !== null) {
                define('CURRENCY_UPLIFT_RATIO', $row['configuration_value']);
            }
        }

        // NOTE: This isn't necessarily going to work since it's not running in a normal Admin context the way the legacy implementation did.
        global $zco_notifier;
        $zco_notifier = new notifier;

        if ($input->isVerboseRequested()) {
            $output->writeln('Starting currency rates update...');
        }

        defined('TEXT_INFO_CURRENCY_UPDATED') || define('TEXT_INFO_CURRENCY_UPDATED', 'The exchange rate for %1$s (%2$s) was updated successfully to %3$s via %4$s.');
        defined('ERROR_CURRENCY_INVALID') || define('ERROR_CURRENCY_INVALID', 'Error: The exchange rate for %1$s (%2$s) was not updated via %3$s. Is it a valid currency code?');

        zen_update_currencies($input->isVerboseRequested());

        if ($input->isVerboseRequested()) {
            $output->writeln('Done.');
        }

        return 0;
    }
}
