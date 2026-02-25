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
        $this->browser->request('GET', HTTP_SERVER . '/index.php?main_page=create_account');
        $response = $this->browser->getResponse();
        $this->assertEquals(200, $response->getStatusCode());
        $this->browser->submitForm('Submit the Information', $profile);
        $response = $this->browser->getResponse();
        $this->assertStringContainsString('Your Account Has Been Created', (string)$response->getContent());
        return $profile;
    }

    public function logoutCustomer()
    {
        //echo 'Logging out customer' . PHP_EOL;
        $this->browser->request('GET', HTTP_SERVER . '/index.php?main_page=logoff');
        $response = $this->browser->getResponse();
        $this->assertStringContainsString('Log Off', (string)$response->getContent());
    }

    public function loginCustomer($profileName)
    {
        //echo 'Logging in customer ' . $profileName . PHP_EOL;
        $this->logoutCustomer();
        $this->browser->request('GET', HTTP_SERVER . '/index.php?main_page=login');
        $response = $this->browser->getResponse();
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertStringContainsString('Welcome, Please Sign In', (string)$response->getContent());

        $profile = ProfileManager::getProfileForLogin($profileName);
        $this->browser->submitForm('Sign In', $profile);
        return $profile;
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
