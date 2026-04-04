<?php

namespace Tests\Support\Traits;

use Tests\Support\Database\TestDb;
use Tests\Support\helpers\ProfileManager;

trait CustomerAccountConcerns
{
    public function createCustomerAccountOrLogin($profileName)
    {
        $profile = ProfileManager::getProfile($profileName);
        if ($this->getCustomerIdFromEmail($profile['email_address']) !== null) {
            $this->loginCustomer($profileName);
            return $profile;
        }

        if (method_exists($this, 'submitCreateAccountForm')) {
            $response = $this->submitCreateAccountForm($profile)
                ->assertRedirect('main_page=create_account_success');

            $this->followRedirect($response)
                ->assertOk()
                ->assertSee('Your Account Has Been Created!');

            return $profile;
        }

        throw new \LogicException('Customer account helpers require in-process storefront form helpers.');
    }

    public function logoutCustomer()
    {
        if (method_exists($this, 'visitLogoff')) {
            $this->visitLogoff()
                ->assertOk()
                ->assertSee('Log Off');
            return;
        }

        throw new \LogicException('Customer logout helper requires in-process storefront navigation helpers.');
    }

    public function loginCustomer($profileName)
    {
        $this->logoutCustomer();

        $profile = ProfileManager::getProfileForLogin($profileName);

        if (method_exists($this, 'submitLoginForm')) {
            $response = $this->submitLoginForm($profile)
                ->assertRedirect();

            $this->followRedirect($response)
                ->assertOk();

            return $profile;
        }

        throw new \LogicException('Customer login helper requires in-process storefront form helpers.');
    }

    public function getCouponBalanceCustomer($customerEmail)
    {
        $customerId = $this->getCustomerIdFromEmail($customerEmail);
        if ($customerId === null) {
            return 0;
        }
        $amount = TestDb::selectValue(
            'SELECT amount FROM coupon_gv_customer WHERE customer_id = :customer_id LIMIT 1',
            [':customer_id' => $customerId]
        );

        return $amount === null ? 0 : (float) $amount;
    }

    public function getCustomerIdFromEmail($customerEmail)
    {
        $customerId = TestDb::selectValue(
            'SELECT customers_id FROM customers WHERE customers_email_address = :email LIMIT 1',
            [':email' => $customerEmail]
        );

        return $customerId === null ? null : (int) $customerId;
    }

    public function addGiftVoucherBalance($customerEmail, $value)
    {
        $customerId = $this->getCustomerIdFromEmail($customerEmail);
        if ($customerId === null) {
            return;
        }

        $exists = TestDb::selectValue(
            'SELECT customer_id FROM coupon_gv_customer WHERE customer_id = :customer_id LIMIT 1',
            [':customer_id' => $customerId]
        );

        if ($exists === null) {
            TestDb::insert('coupon_gv_customer', ['customer_id' => $customerId, 'amount' => $value]);
        } else {
            TestDb::update(
                'coupon_gv_customer',
                ['amount' => $value],
                'customer_id = :customer_id',
                [':customer_id' => $customerId]
            );
        }
    }

    public function setCustomerGroupDiscount($customerEmail, $value)
    {
        $customerId = $this->getCustomerIdFromEmail($customerEmail);
        if ($customerId === null) {
            return;
        }

        TestDb::update(
            'customers',
            ['customers_group_pricing' => $value],
            'customers_id = :customer_id',
            [':customer_id' => $customerId]
        );
    }
    public function updateGVBalance($profile)
    {
        if ($this->getCouponBalanceCustomer($profile['email_address']) < 300) {
            $this->addGiftVoucherBalance($profile['email_address'], 1000);
        }
    }

}
