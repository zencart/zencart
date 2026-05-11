<?php
/**
 * @copyright Copyright 2003-2026 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 */

namespace Tests\FeatureAdmin\AdminEndpoints;

use Tests\Support\zcInProcessFeatureTestCaseAdmin;

#[\PHPUnit\Framework\Attributes\Group('parallel-candidate')]
class AdminOperationalPagesTest extends zcInProcessFeatureTestCaseAdmin
{
    protected $runTestInSeparateProcess = true;
    protected $preserveGlobalState = false;

    private const OPERATIONAL_PAGE_MAP = [
        'customers' => ['strings' => ['Customers', 'Account Created']],
        'customer_groups' => ['strings' => ['Customer Groups', 'Group Name']],
        'coupon_admin' => ['strings' => ['Discount Coupons', 'Coupon Name']],
        'currencies' => ['strings' => ['Currencies', 'Currency']],
        'countries' => ['strings' => ['Countries', 'ISO Codes']],
        'tax_rates' => ['strings' => ['Tax Rates', 'Tax Rate']],
        'orders' => ['strings' => ['Orders', 'Order ID:']],
    ];

    #[\PHPUnit\Framework\Attributes\DataProvider('operationalPageProvider')]
    public function testOperationalPagesAreReachableAfterInitialSetup(string $page, array $contentTest): void
    {
        $this->completeInitialAdminSetup();

        $response = $this->visitAdminCommand($page)->assertOk();

        foreach ($contentTest['strings'] as $contentString) {
            $response->assertSee($contentString);
        }
    }

    public static function operationalPageProvider(): array
    {
        $pages = [];

        foreach (self::OPERATIONAL_PAGE_MAP as $page => $contentTest) {
            $pages[$page] = [$page, $contentTest];
        }

        return $pages;
    }
}
