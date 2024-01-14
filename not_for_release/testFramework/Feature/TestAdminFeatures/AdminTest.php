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

        'modules&set=payment' => 'Authorize.net',
        'modules&set=shipping' => 'Free Shipping Options',
        'modules&set=ordertotal' => 'Low Order Fee',
        'plugin_manager' => 'Display logs',

        'customers' => 'Account Created',
        'orders' => 'Date Purchased',
        'group_pricing' => 'Group Name',
        'customer_groups' => '# of Customers',

        'countries' => 'Address Format',
        'zones' => 'Zones Name:',
        'geo_zones' => 'Number of Zones:',
        'tax_classes' => 'Tax Classes',
        'tax_rates' => 'Priority',

        'currencies' => 'Symbol Left:',
        'languages' => 'Code:',
        'orders_status' => 'Orders Status ID',

        'stats_customers' => 'Best Customer Orders-Total',
        'stats_customers_referrals' => 'Customers Referral Report',
        'stats_products_lowstock' => 'Product Stock Report',
        'stats_products_purchased' => 'Best Products Purchased',
        'stats_products_viewed' => 'Most-Viewed Products',
        'stats_sales_report_graphs' => 'Monthly Sales Reports',

        'banner_manager' => 'Banner Manager',
        'define_pages_editor' => 'Define Pages Editor for:',
        'developers_tool_kit' => 'Look-up CONSTANT or Language File defines',
        'ezpages' => 'EZ-Pages Select a page',
        'sqlpatch' => 'SQL Query Executor',
        'layout_controller' => 'Editing Sideboxes for template:',
        'newsletters' => 'Newsletter and Product Notifications Manager',
        'mail' => 'Send Email To Customers',
        'server_info' => 'Database Engine:',
        'store_manager' => 'Update ALL Products Price Sorter',
        'template_select' => 'Template Directory',
        'whos_online' => 'Updating Manually',

        'coupon_admin' => 'Coupon Code',
        'gv_queue' => 'Gift Certificate Release Queue',
        'gv_mail' => 'Send a Gift Certificate To Customers',
        'gv_sent' => 'Gift Certificate Value',

        'profiles' => 'User Profiles',
        'users' => 'Reset Password',
        'admin_page_registration' => 'Page Parameters',
        'admin_activity' => 'Review or Export Logs',

        'record_artists' => 'Recording Artists',
        'record_company' => 'Record Companies',
        'music_genre' => 'Music Genres',
        'media_manager' => 'Assign to Product',
        'media_types' => 'Extension',



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
