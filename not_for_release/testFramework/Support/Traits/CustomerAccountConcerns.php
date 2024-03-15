<?php

namespace Tests\Support\Traits;

use App\Models\CouponGvCustomer;
use App\Models\Customer;
use App\Models\GroupPricing;
use Tests\Support\helpers\ProfileManager;

trait CustomerAccountConcerns
{

    public function createCustomerAccount($profileName)
    {
        //echo 'Creating account for ' . $profileName . PHP_EOL;
        $this->browser->request('GET', HTTP_SERVER . '/index.php?main_page=create_account');
        $response = $this->browser->getResponse();
        $this->assertEquals(200, $response->getStatusCode());
        $profile = ProfileManager::getProfile($profileName);
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
        $gv = CouponGvCustomer::where('customer_id', $customerId)->first();
        if (!$gv) {
            return 0;
        }
        return $gv['amount'];
    }

    public function getCustomerIdFromEmail($customerEmail)
    {
        $customer = Customer::where('customers_email_address', $customerEmail)->first();
        return $customer['customers_id'];
    }

    public function addGiftVoucherBalance($customerEmail, $value)
    {
        $customerId = $this->getCustomerIdFromEmail($customerEmail);
        $gv = CouponGvCustomer::where('customer_id', $customerId)->first();
        if (!$gv) {
            CouponGvCustomer::query()->create(['customer_id' => $customerId, 'amount' => $value]);
        } else {
            $gv->amount = $value;
            $gv->save();
        }
    }

    public function setCustomerGroupDiscount($customerEmail, $value)
    {
        $customerId = $this->getCustomerIdFromEmail($customerEmail);
        $gp = Customer::where('customers_id', $customerId)->first();
        if (!$gp) {
            return;
        }
        $gp->customers_group_pricing = $value;
        $gp->save();
    }
}
