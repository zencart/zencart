<?php
/**
 * @copyright Copyright 2003-2026 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 */

namespace Tests\FeatureStore\StoreEndpoints;

use Tests\Support\Traits\CustomerAccountConcerns;
use Tests\Support\zcInProcessFeatureTestCaseStore;

/**
 * @group parallel-candidate
 * @group customer-account-write
 */
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

    protected function orderStatusSpamFieldName(string $content): string
    {
        if (preg_match('/<input[^>]*name="([^"]+)"[^>]*id="CUAS"/i', $content, $matches) === 1) {
            return $matches[1];
        }

        $this->fail('Unable to locate order-status honeypot field.');
    }
}
