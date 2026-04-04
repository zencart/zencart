<?php
/**
 * @copyright Copyright 2003-2026 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 */

namespace Tests\FeatureStore\StoreEndpoints;

use Tests\Support\Database\TestDb;
use Tests\Support\Traits\CustomerAccountConcerns;
use Tests\Support\zcInProcessFeatureTestCaseStore;

/**
 * @group parallel-candidate
 */
class CheckoutSuccessAndHistoryDetailTest extends zcInProcessFeatureTestCaseStore
{
    use CustomerAccountConcerns;

    protected $runTestInSeparateProcess = true;
    protected $preserveGlobalState = false;

    public function testCustomerCanPlaceOrderAndSeeCheckoutSuccessDetails(): void
    {
        $checkout = $this->completeSimpleCheckout('florida-basic1');

        $checkout['success_page']
            ->assertOk()
            ->assertHeader('X-ZC-InProcess-Runner', 'storefront')
            ->assertSee('Thank You! We Appreciate your Business!')
            ->assertSee('Your Order Number is:')
            ->assertSee((string) $checkout['order_id'])
            ->assertSee($checkout['product_name']);
    }

    public function testPlacedOrderAppearsInAccountHistoryAndHistoryDetail(): void
    {
        $checkout = $this->completeSimpleCheckout('florida-basic2');

        $this->getSslMainPage('account_history')
            ->assertOk()
            ->assertHeader('X-ZC-InProcess-Runner', 'storefront')
            ->assertSee('Order History')
            ->assertSee((string) $checkout['order_id'])
            ->assertSee($checkout['product_name']);

        $this->getSsl('/index.php?main_page=account_history_info&order_id=' . $checkout['order_id'])
            ->assertOk()
            ->assertHeader('X-ZC-InProcess-Runner', 'storefront')
            ->assertSee('Order Information')
            ->assertSee((string) $checkout['order_id'])
            ->assertSee($checkout['product_name'])
            ->assertSee($checkout['customer_name']);
    }

    protected function completeSimpleCheckout(string $profileName): array
    {
        $profile = $this->createCustomerAccountOrLogin($profileName);
        $customerId = $this->getCustomerIdFromEmail($profile['email_address']);

        $this->assertNotNull($customerId);

        $this->addProductToCart(25)
            ->assertRedirect('main_page=shopping_cart');

        $this->continueCheckoutShipping()
            ->assertOk()
            ->assertSee('Payment Information');

        $this->continueCheckoutPayment()
            ->assertOk()
            ->assertSee('Order Confirmation');

        $successResponse = $this->confirmCheckoutOrder()
            ->assertRedirect('main_page=checkout_success');

        $successPage = $this->followRedirect($successResponse)
            ->assertOk();

        $order = TestDb::selectOne(
            'SELECT o.orders_id, o.customers_name, op.products_name
               FROM orders o
               INNER JOIN orders_products op ON op.orders_id = o.orders_id
              WHERE o.customers_id = :customer_id
              ORDER BY o.orders_id DESC, op.orders_products_id ASC
              LIMIT 1',
            [':customer_id' => $customerId]
        );

        $this->assertNotNull($order, 'Expected checkout flow to create an order.');

        return [
            'success_page' => $successPage,
            'order_id' => (int) $order['orders_id'],
            'customer_name' => $order['customers_name'],
            'product_name' => $order['products_name'],
        ];
    }
}
