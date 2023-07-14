<?php
/**
 * @copyright Copyright 2003-2020 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 */

namespace Tests\Features\TestAdminFeatures;

use Tests\Support\zcFeatureTestCaseAdmin;

class AdminTest extends zcFeatureTestCaseAdmin
{

    protected array $quickTestMap = [
        'configuration&gID=1' => 'Admin Session Time Out in Seconds',
        'category_product_listing' => 'Admin Category Product Listing',
        'product_types' => 'Admin Product Types',
        'products_price_manager' => 'Admin Products Price Manager',
        'options_name_manager' => 'Admin Options Name Manager',
        'options_values_manager' => 'Admin Options Values Manager',
        'attributes_controller' => 'Please select a Category to display the Product Attributes of',
        'downloads_manager' => 'Admin Downloads Manager',
        'option_name' => 'Option Name Sort Order',
        'option_values' => 'Option Values Default Sort Order',
        'manufacturers' => 'Admin Manufacturers',
        'reviews' => "Admin Reviews",
        'specials' => "Admin Specials",
        'featured' => "Sample of Document Product Type",
        'salemaker' => "Admin Salemaker",
        'products_expected' => "Admin Products Expected",
        'products_to_categories' => "Admin Products To Categories",
    ];

    public function testSimpleAdmin()
    {
        $this->browser->request('GET', HTTP_SERVER . '/admin');
        $response = $this->browser->getResponse();
        $this->assertEquals(200, $response->getStatusCode());
        $this->browser->request('GET', HTTP_SERVER . '/admin');
        $response = $this->browser->getResponse();
        $this->assertStringContainsString('Admin Login', (string)$response->getContent() );
        $this->browser->submitForm('Submit', [
            'admin_name' => 'Admin',
            'admin_pass' => 'password',
        ]);
        $response = $this->browser->getResponse();
        $this->assertStringContainsString('Initial Setup Wizard', (string)$response->getContent() );
    }

    public function testInitialLogin()
    {
        $this->browser->request('GET', HTTP_SERVER . '/admin');
        $response = $this->browser->getResponse();
        $this->assertEquals(200, $response->getStatusCode());
        $this->browser->request('GET', HTTP_SERVER . '/admin');
        $response = $this->browser->getResponse();
        $this->assertEquals(200, $response->getStatusCode());
        $this->browser->submitForm('Submit', [
            'admin_name' => 'Admin',
            'admin_pass' => 'password',
        ]);
        $this->browser->submitForm('Update', [
            'store_name' => 'Zencart Store',
        ]);
        $response = $this->browser->getResponse();
        $this->assertStringContainsString('Initial Setup Wizard', (string)$response->getContent() );
        $this->browser->submitForm('Update', [
            'store_name' => 'Zencart Store',
            'store_owner' => 'Store Owner',
        ]);
        $response = $this->browser->getResponse();
        $this->assertStringContainsString('Admin Home', (string)$response->getContent() );
        $this->quickLinksTest();
    }

    public function QuickLinksTest()
    {
        foreach ($this->quickTestMap as $page => $contentTest) {
            $pageURI = $this->buildAdminLink($page);
            $this->browser->request('GET', $pageURI);
            $response = $this->browser->getResponse();
            $this->assertEquals(200, $response->getStatusCode());
            $this->assertStringContainsString($contentTest, (string)$response->getContent() );
        }
    }

}
