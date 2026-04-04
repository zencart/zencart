<?php
/**
 * @copyright Copyright 2003-2026 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 */

namespace Tests\FeatureAdmin\AdminEndpoints;

use Tests\Support\Database\TestDb;
use Tests\Support\Traits\CustomerAccountConcerns;
use Tests\Support\Traits\InProcessStorefrontCheckoutConcerns;
use Tests\Support\zcInProcessFeatureTestCaseAdmin;

/**
 * @group parallel-candidate
 */
class AdminOrderStatusUpdateTest extends zcInProcessFeatureTestCaseAdmin
{
    use CustomerAccountConcerns;
    use InProcessStorefrontCheckoutConcerns;

    protected $runTestInSeparateProcess = true;
    protected $preserveGlobalState = false;

    public function setUp(): void
    {
        parent::setUp();
        $this->resetStorefrontSession();
    }

    public function testAdminCanUpdateOrderStatusAndRecordHistory(): void
    {
        $order = $this->completeSimpleStorefrontCheckout('florida-basic1');

        $this->completeInitialAdminSetup();

        $editPage = $this->getAdmin('/admin/index.php?cmd=orders&oID=' . $order['order_id'] . '&action=edit')
            ->assertOk()
            ->assertHeader('X-ZC-InProcess-Runner', 'admin')
            ->assertSee($order['product_name'])
            ->assertSee((string) $order['order_id']);

        $response = $this->submitAdminForm($editPage, 'statusUpdateForm', [
            'statusUpdateSelect' => '2',
            'comments' => 'Updated by admin feature test',
            'notify' => '0',
            'notify_comments' => 'on',
            'camefrom' => 'orderEdit',
        ]);

        $response->assertOk()
            ->assertHeader('X-ZC-InProcess-Runner', 'admin')
            ->assertSee('Orders')
            ->assertSee($order['product_name'])
            ->assertSee('Updated by admin feature test')
            ->assertSee('Processing [2]');

        $updatedOrder = TestDb::selectOne(
            'SELECT orders_status FROM orders WHERE orders_id = :order_id LIMIT 1',
            [':order_id' => $order['order_id']]
        );

        $historyRow = TestDb::selectOne(
            'SELECT orders_status_id, customer_notified, comments
               FROM orders_status_history
              WHERE orders_id = :order_id
              ORDER BY orders_status_history_id DESC
              LIMIT 1',
            [':order_id' => $order['order_id']]
        );

        $this->assertNotNull($updatedOrder);
        $this->assertSame('2', (string) $updatedOrder['orders_status']);

        $this->assertNotNull($historyRow);
        $this->assertSame('2', (string) $historyRow['orders_status_id']);
        $this->assertSame('0', (string) $historyRow['customer_notified']);
        $this->assertSame('Updated by admin feature test', $historyRow['comments']);
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
