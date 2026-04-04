<?php
/**
 * @copyright Copyright 2003-2026 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 */

namespace Tests\FeatureStore\StoreEndpoints;

use Tests\Support\zcInProcessFeatureTestCaseStore;

/**
 * @group parallel-candidate
 */
class CartInProcessTest extends zcInProcessFeatureTestCaseStore
{
    protected $runTestInSeparateProcess = true;
    protected $preserveGlobalState = false;

    public function testShoppingCartPageCanBeRenderedInProcess(): void
    {
        $this->visitCart()
            ->assertOk()
            ->assertHeader('X-ZC-InProcess-Runner', 'storefront')
            ->assertSee('Zen Cart! : The Shopping Cart');
    }

    public function testProductCanBeAddedToCartInProcess(): void
    {
        $this->emptyCart();

        $response = $this->addProductToCart(3, '1_9')
            ->assertRedirect('main_page=shopping_cart');

        $this->followRedirect($response)
            ->assertOk()
            ->assertHeader('X-ZC-InProcess-Runner', 'storefront')
            ->assertSee('Your Shopping Cart Contents')
            ->assertSee('Microsoft IntelliMouse Pro');
    }
}
