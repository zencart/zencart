<?php
namespace Tests\Feature;

use Tests\Support\zcFeatureTestCaseStore;

class LowOrderFeeTest extends zcFeatureTestCaseStore
{
    /** @test **/
    public function test_it_tests_a_simple_loworderfee_()
    {

        $this->switchLowOrderFee('on');
        $this->createCustomerAccount('florida-basic1');
        $this->browser->request('GET', HTTP_SERVER  . '/index.php?main_page=shopping_cart&action=empty_cart');
        $this->browser->request('GET', HTTP_SERVER  . '/index.php?main_page=product_info&cPath=1_9&products_id=3&action=buy_now');
        $browser = $this->browser->request('GET', HTTP_SERVER  . '/index.php?main_page=checkout_shipping');
        $response = $this->browser->getResponse();
        $this->assertEquals(200, $response->getStatusCode());
        $form = $browser->selectButton('Continue')->form();
        //var_dump($form);
var_dump($form['shipping'][1]);
//$form['shipping_cod'] = 'foo';
        $this->assertTrue(true);
    }
}
