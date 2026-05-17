<?php
/**
 * @copyright Copyright 2003-2026 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 */

namespace Tests\FeatureStore\StoreEndpoints;

use Tests\Support\Database\TestDb;
use Tests\Support\Traits\CustomerAccountConcerns;
use Tests\Support\zcInProcessFeatureTestCaseStore;

#[\PHPUnit\Framework\Attributes\Group('parallel-candidate')]
#[\PHPUnit\Framework\Attributes\Group('customer-account-write')]
class OrderStatusInProcessTest extends zcInProcessFeatureTestCaseStore
{
    use CustomerAccountConcerns;

    protected $runTestInSeparateProcess = true;
    protected $preserveGlobalState = false;

    public function testGuestCanViewOrderStatusLookupForm(): void
    {
        $this->getMainPage('order_status')
            ->assertOk()
            ->assertHeader('X-ZC-InProcess-Runner', 'storefront')
            ->assertSee('Lookup Order Information')
            ->assertSee('Order Number:')
            ->assertSee('Email Address:');
    }

    public function testLoggedInCustomerIsRedirectedToAccountHistory(): void
    {
        $this->createCustomerAccountOrLogin('florida-basic1');

        $this->getSslMainPage('order_status')
            ->assertRedirect('main_page=account_history');
    }

    public function testInvalidOrderNumberShowsValidationMessage(): void
    {
        $page = $this->getSslMainPage('order_status')->assertOk();
        $spamField = $this->orderStatusSpamFieldName($page->content);
        $defaults = $page->formDefaults('order_status');

        $this->postSsl('/index.php?main_page=order_status&action=status', [
            ...$defaults,
            'order_id' => '0',
            'query_email_address' => 'dirk@example.com',
            $spamField => '',
        ])->assertOk()
            ->assertSee('You have entered an invalid order number.');
    }

    public function testNoMatchingOrderShowsLookupFailureMessage(): void
    {
        $page = $this->getSslMainPage('order_status')->assertOk();
        $spamField = $this->orderStatusSpamFieldName($page->content);
        $defaults = $page->formDefaults('order_status');

        $this->postSsl('/index.php?main_page=order_status&action=status', [
            ...$defaults,
            'order_id' => '999999',
            'query_email_address' => 'dirk@example.com',
            $spamField => '',
        ])->assertOk()
            ->assertSee('No match found for your entry.');
    }

    public function testGuestOrderLookupDoesNotReflectRawOrderIdPayload(): void
    {
        $order = $this->createGuestLookupOrder('florida-basic1');
        $this->cookies = [];

        $page = $this->getSslMainPage('order_status')->assertOk();
        $spamField = $this->orderStatusSpamFieldName($page->content);
        $defaults = $page->formDefaults('order_status');
        $payload = $order['order_id'] . '</h2><svg/onload=alert(1)>';

        $response = $this->postSsl('/index.php?main_page=order_status&action=status', [
            ...$defaults,
            'order_id' => $payload,
            'query_email_address' => $order['email_address'],
            $spamField => '',
        ])->assertOk()
            ->assertSee('Order Information')
            ->assertSee((string) $order['order_id']);

        $this->assertStringNotContainsString($payload, $response->content);
        $this->assertStringNotContainsString('<svg/onload=alert(1)>', $response->content);
    }

    protected function orderStatusSpamFieldName(string $content): string
    {
        if (preg_match('/<input[^>]*name="([^"]+)"[^>]*id="CUAS"/i', $content, $matches) === 1) {
            return $matches[1];
        }

        $this->fail('Unable to locate order-status honeypot field.');
    }

    protected function createGuestLookupOrder(string $profileName, int $productId = 25): array
    {
        $profile = $this->createCustomerAccountOrLogin($profileName);
        $customerId = $this->getCustomerIdFromEmail($profile['email_address']);

        $this->assertNotNull($customerId);

        $this->addProductToCart($productId)
            ->assertRedirect('main_page=shopping_cart');

        $this->continueCheckoutShipping()
            ->assertOk()
            ->assertSee('Payment Information');

        $this->continueCheckoutPayment()
            ->assertOk()
            ->assertSee('Order Confirmation');

        $successResponse = $this->confirmCheckoutOrder()
            ->assertRedirect('main_page=checkout_success');

        $this->followRedirect($successResponse)
            ->assertOk()
            ->assertSee('Your Order Number is:');

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
            'order_id' => (int) $order['orders_id'],
            'customer_name' => $order['customers_name'],
            'product_name' => $order['products_name'],
            'email_address' => $profile['email_address'],
        ];
    }
}
