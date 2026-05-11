<?php
/**
 * @copyright Copyright 2003-2026 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 */

namespace Tests\FeatureAdmin\AdminEndpoints;

use Tests\Support\Traits\CustomerAccountConcerns;
use Tests\Support\Traits\InProcessStorefrontCheckoutConcerns;
use Tests\Support\zcInProcessFeatureTestCaseAdmin;

#[\PHPUnit\Framework\Attributes\Group('parallel-candidate')]
class AdminOrderDocumentsTest extends zcInProcessFeatureTestCaseAdmin
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

    public function testAdminCanViewOrdersListDetailInvoiceAndPackingslip(): void
    {
        $order = $this->completeSimpleStorefrontCheckout('florida-basic1');

        $this->completeInitialAdminSetup();

        $this->visitAdminCommand('orders')
            ->assertOk()
            ->assertHeader('X-ZC-InProcess-Runner', 'admin')
            ->assertSee('Orders')
            ->assertSee((string) $order['order_id'])
            ->assertSee($order['customer_name']);

        $this->getAdmin('/admin/index.php?cmd=orders&oID=' . $order['order_id'] . '&action=edit')
            ->assertOk()
            ->assertHeader('X-ZC-InProcess-Runner', 'admin')
            ->assertSee('Invoice')
            ->assertSee('Packing Slip')
            ->assertSee((string) $order['order_id'])
            ->assertSee($order['customer_name'])
            ->assertSee($order['product_name']);

        $this->getAdmin('/admin/index.php?cmd=invoice&oID=' . $order['order_id'])
            ->assertOk()
            ->assertHeader('X-ZC-InProcess-Runner', 'admin')
            ->assertSee('Payment Method:')
            ->assertSee((string) $order['order_id'])
            ->assertSee($order['customer_name'])
            ->assertSee($order['product_name'])
            ->assertSee($order['email_address']);

        $this->getAdmin('/admin/index.php?cmd=packingslip&oID=' . $order['order_id'])
            ->assertOk()
            ->assertHeader('X-ZC-InProcess-Runner', 'admin')
            ->assertSee('Payment Method:')
            ->assertSee((string) $order['order_id'])
            ->assertSee($order['customer_name'])
            ->assertSee($order['product_name'])
            ->assertSee($order['email_address']);
    }
}
