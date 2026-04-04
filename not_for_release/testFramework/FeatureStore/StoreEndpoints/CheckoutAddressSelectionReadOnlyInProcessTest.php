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
class CheckoutAddressSelectionReadOnlyInProcessTest extends zcInProcessFeatureTestCaseStore
{
    use CustomerAccountConcerns;

    protected $runTestInSeparateProcess = true;
    protected $preserveGlobalState = false;

    public function testCustomerCanViewAlternateShippingAddressSelectionDuringCheckout(): void
    {
        $profile = $this->createCustomerAccountOrLogin('florida-basic1');
        $customerId = $this->getCustomerIdFromEmail($profile['email_address']);

        $this->assertNotNull($customerId);

        $shippingAddressId = $this->insertAddressBookEntry(
            $customerId,
            'Ship',
            'Alternate',
            '742 Evergreen Terrace',
            'Orlando',
            18,
            '32801'
        );

        $this->addProductToCart(25)
            ->assertRedirect('main_page=shopping_cart');

        $this->getSslMainPage('checkout_shipping_address')
            ->assertOk()
            ->assertHeader('X-ZC-InProcess-Runner', 'storefront')
            ->assertSee('Change the Shipping Address')
            ->assertSee((string) $shippingAddressId)
            ->assertSee('Ship Alternate');
    }

    public function testCustomerCanViewAlternateBillingAddressSelectionDuringCheckout(): void
    {
        $profile = $this->createCustomerAccountOrLogin('florida-basic2');
        $customerId = $this->getCustomerIdFromEmail($profile['email_address']);

        $this->assertNotNull($customerId);

        $billingAddressId = $this->insertAddressBookEntry(
            $customerId,
            'Bill',
            'Alternate',
            '150 Biscayne Blvd',
            'Miami',
            18,
            '33132'
        );

        $this->addProductToCart(25)
            ->assertRedirect('main_page=shopping_cart');

        $this->continueCheckoutShipping()
            ->assertOk()
            ->assertSee('Payment Information');

        $this->getSslMainPage('checkout_payment_address')
            ->assertOk()
            ->assertHeader('X-ZC-InProcess-Runner', 'storefront')
            ->assertSee('Change the Billing Information')
            ->assertSee((string) $billingAddressId)
            ->assertSee('Bill Alternate');
    }

    protected function insertAddressBookEntry(
        int $customerId,
        string $firstname,
        string $lastname,
        string $streetAddress,
        string $city,
        int $zoneId,
        string $postcode
    ): int {
        return (int) TestDb::insert('address_book', [
            'customers_id' => $customerId,
            'entry_firstname' => $firstname,
            'entry_lastname' => $lastname,
            'entry_street_address' => $streetAddress,
            'entry_postcode' => $postcode,
            'entry_city' => $city,
            'entry_country_id' => 223,
            'entry_zone_id' => $zoneId,
            'entry_state' => '',
            'entry_gender' => 'm',
            'entry_company' => '',
            'entry_suburb' => '',
        ]);
    }
}
