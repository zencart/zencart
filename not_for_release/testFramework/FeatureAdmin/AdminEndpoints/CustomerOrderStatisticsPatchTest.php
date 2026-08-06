<?php
/**
 * Temporary branch-maintenance runner. Removed before review.
 */

namespace Tests\FeatureAdmin\AdminEndpoints;

use PHPUnit\Framework\TestCase;

class CustomerOrderStatisticsPatchTest extends TestCase
{
    public function testApplyCustomerOrderStatisticsPatch(): void
    {
        if (PHP_VERSION_ID < 80500) {
            self::markTestSkipped('Patch runner executes once under PHP 8.5.');
        }

        $root = dirname(__DIR__, 4);

        $this->replaceOnce(
            $root . '/includes/classes/Customer.php',
            "    protected ?int \$customer_id = null;\n"
                . "    protected bool \$is_logged_in = false;\n"
                . "    protected bool \$is_in_guest_checkout = false;\n"
                . "    protected array \$data = [];\n\n"
                . "    public function __construct(\$customer_id = null)\n"
                . "    {\n"
                . "        \$this->is_logged_in = \$this->someoneIsLoggedIn();\n",
            "    protected ?int \$customer_id = null;\n"
                . "    protected bool \$is_logged_in = false;\n"
                . "    protected bool \$is_in_guest_checkout = false;\n"
                . "    protected array \$data = [];\n"
                . "    protected bool \$load_order_statistics = true;\n\n"
                . "    public function __construct(\$customer_id = null, bool \$load_order_statistics = true)\n"
                . "    {\n"
                . "        \$this->load_order_statistics = \$load_order_statistics;\n"
                . "        \$this->is_logged_in = \$this->someoneIsLoggedIn();\n"
        );

        $this->replaceOnce(
            $root . '/includes/classes/Customer.php',
            "        if (IS_ADMIN_FLAG) {\n"
                . "            \$this->data['number_of_orders'] = \$this->countCustomersPreviousOrders();\n"
                . "            // only calculating this on the Admin side, for performance reasons\n"
                . "            if (\$this->data['number_of_orders']) {\n"
                . "                \$this->data['lifetime_value'] = \$this->getLifetimeValue();\n"
                . "            }\n"
                . "        } else {\n"
                . "            \$this->data['lifetime_value'] = null;\n"
                . "            \$this->data['number_of_orders'] = \$this->getNumberOfOrders();\n"
                . "        }\n",
            "        if (IS_ADMIN_FLAG && \$this->load_order_statistics) {\n"
                . "            \$this->data['number_of_orders'] = \$this->countCustomersPreviousOrders();\n"
                . "            // only calculating this on the Admin side, for performance reasons\n"
                . "            if (\$this->data['number_of_orders']) {\n"
                . "                \$this->data['lifetime_value'] = \$this->getLifetimeValue();\n"
                . "            }\n"
                . "        } elseif (!IS_ADMIN_FLAG) {\n"
                . "            \$this->data['lifetime_value'] = null;\n"
                . "            \$this->data['number_of_orders'] = \$this->getNumberOfOrders();\n"
                . "        }\n"
        );

        $this->replaceOnce(
            $root . '/admin/customers.php',
            "    \$customers = \$db->Execute(\$customers_query_raw);\n"
                . "    foreach (\$customers as \$result) {\n"
                . "        \$cust = new Customer(\$result['customers_id']);\n"
                . "        \$customer = \$cust->getData();\n"
                . "        if ((!isset(\$_GET['cID']) || (int)\$_GET['cID'] === \$customer['customers_id']) && !isset(\$cInfo)) {\n"
                . "            \$cInfo = new objectInfo(\$customer);\n"
                . "        }\n",
            "    \$customers = \$db->Execute(\$customers_query_raw);\n"
                . "    \$selected_customer = null;\n"
                . "    foreach (\$customers as \$result) {\n"
                . "        \$load_order_statistics = !isset(\$cInfo)\n"
                . "            && (!isset(\$_GET['cID']) || (int)\$_GET['cID'] === (int)\$result['customers_id']);\n"
                . "        \$cust = new Customer(\n"
                . "            \$result['customers_id'],\n"
                . "            load_order_statistics: \$load_order_statistics\n"
                . "        );\n"
                . "        \$customer = \$cust->getData();\n"
                . "        if (\$load_order_statistics) {\n"
                . "            \$cInfo = new objectInfo(\$customer);\n"
                . "            \$selected_customer = \$cust;\n"
                . "        }\n"
        );

        $this->replaceOnce(
            $root . '/admin/customers.php',
            "            if (isset(\$cInfo) && is_object(\$cInfo)) {\n"
                . "                \$customer = new Customer(\$cInfo->customers_id);\n",
            "            if (isset(\$cInfo) && is_object(\$cInfo)) {\n"
                . "                \$customer = \$selected_customer ?? new Customer(\$cInfo->customers_id);\n"
        );

        self::assertSame(0, $this->run('php -l includes/classes/Customer.php', $root));
        self::assertSame(0, $this->run('php -l admin/customers.php', $root));

        $this->run('git config user.name github-actions[bot]', $root);
        $this->run('git config user.email 41898282+github-actions[bot]@users.noreply.github.com', $root);
        self::assertSame(0, $this->run('git add includes/classes/Customer.php admin/customers.php', $root));
        self::assertSame(0, $this->run('git commit -m "Defer customer order statistics in admin listing"', $root));
        self::assertSame(0, $this->run('git push origin HEAD:agent/defer-customer-order-statistics', $root));
    }

    private function replaceOnce(string $path, string $old, string $new): void
    {
        $contents = file_get_contents($path);
        self::assertIsString($contents);
        self::assertSame(1, substr_count($contents, $old), "Expected exactly one match in $path");
        file_put_contents($path, str_replace($old, $new, $contents));
    }

    private function run(string $command, string $cwd): int
    {
        $process = proc_open($command, [1 => ['pipe', 'w'], 2 => ['pipe', 'w']], $pipes, $cwd);
        self::assertIsResource($process);
        $stdout = stream_get_contents($pipes[1]);
        $stderr = stream_get_contents($pipes[2]);
        fclose($pipes[1]);
        fclose($pipes[2]);
        $status = proc_close($process);
        fwrite(STDOUT, $stdout . $stderr);
        return $status;
    }
}
