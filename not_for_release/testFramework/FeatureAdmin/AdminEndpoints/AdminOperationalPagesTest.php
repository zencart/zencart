<?php
/**
 * @copyright Copyright 2003-2026 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 */

namespace Tests\FeatureAdmin\AdminEndpoints;

use Tests\Support\zcInProcessFeatureTestCaseAdmin;

/**
 * @group parallel-candidate
 */
class AdminOperationalPagesTest extends zcInProcessFeatureTestCaseAdmin
{
    protected $runTestInSeparateProcess = true;
    protected $preserveGlobalState = false;

    protected array $operationalPageMap = [
        'customers' => ['strings' => ['Customers', 'Account Created']],
        'customer_groups' => ['strings' => ['Customer Groups', 'Group Name']],
        'coupon_admin' => ['strings' => ['Discount Coupons', 'Coupon Name']],
        'currencies' => ['strings' => ['Currencies', 'Currency']],
        'countries' => ['strings' => ['Countries', 'ISO Codes']],
        'tax_rates' => ['strings' => ['Tax Rates', 'Tax Rate']],
        'orders' => ['strings' => ['Orders', 'Order ID:']],
    ];

    /**
     * @dataProvider operationalPageProvider
     */
    public function testOperationalPagesAreReachableAfterInitialSetup(string $page, array $contentTest): void
    {
        $this->completeInitialAdminSetup();

        $response = $this->visitAdminCommand($page)->assertOk();

        foreach ($contentTest['strings'] as $contentString) {
            $response->assertSee($contentString);
        }
    }

    public function operationalPageProvider(): array
    {
        $pages = [];

        foreach ($this->operationalPageMap as $page => $contentTest) {
            $pages[$page] = [$page, $contentTest];
        }

        return $pages;
    }

    protected function completeInitialAdminSetup(): void
    {
        $this->visitAdminHome()
            ->assertOk()
            ->assertSee('Admin Login');

        $this->submitAdminLogin([
            'admin_name' => 'Admin',
            'admin_pass' => 'password',
        ])->assertOk()
            ->assertSee('Initial Setup Wizard');

        $this->submitAdminSetupWizard([
            'store_name' => 'Zencart Store',
        ])->assertOk()
            ->assertSee('Initial Setup Wizard');

        $this->submitAdminSetupWizard([
            'store_name' => 'Zencart Store',
            'store_owner' => 'Store Owner',
        ])->assertOk()
            ->assertSee('Admin Home');
    }
}
