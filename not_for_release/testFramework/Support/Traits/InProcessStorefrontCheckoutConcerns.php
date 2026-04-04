<?php
/**
 * @copyright Copyright 2003-2026 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 */

namespace Tests\Support\Traits;

use Tests\Support\Database\TestDb;
use Tests\Support\InProcess\FeatureRequest;
use Tests\Support\InProcess\FeatureResponse;
use Tests\Support\InProcess\StorefrontFeatureRunner;

trait InProcessStorefrontCheckoutConcerns
{
    protected array $storeCookies = [];
    protected array $storeSslServer = ['HTTPS' => 'on', 'SERVER_PORT' => '443'];

    protected function resetStorefrontSession(): void
    {
        StorefrontFeatureRunner::resetDispatchState();
        $this->storeCookies = [];
    }

    protected function storefrontGet(string $uri, array $server = [], array $cookies = []): FeatureResponse
    {
        $response = (new StorefrontFeatureRunner())->handle(
            new FeatureRequest($uri, 'GET', server: $server, cookies: $this->mergeStorefrontCookies($cookies))
        );

        $this->storeResponseCookies($response);

        return $response;
    }

    protected function storefrontPost(string $uri, array $data = [], array $server = [], array $cookies = []): FeatureResponse
    {
        $response = (new StorefrontFeatureRunner())->handle(
            new FeatureRequest($uri, 'POST', request: $data, server: $server, cookies: $this->mergeStorefrontCookies($cookies))
        );

        $this->storeResponseCookies($response);

        return $response;
    }

    protected function visitCreateAccount(array $server = [], array $cookies = []): FeatureResponse
    {
        return $this->storefrontGetSslMainPage('create_account', $server, $cookies);
    }

    protected function visitLogin(array $server = [], array $cookies = []): FeatureResponse
    {
        return $this->storefrontGetSslMainPage('login', $server, $cookies);
    }

    protected function visitLogoff(array $server = [], array $cookies = []): FeatureResponse
    {
        return $this->storefrontGetMainPage('logoff', $server, $cookies);
    }

    protected function submitCreateAccountForm(array $data = [], array $server = [], array $cookies = []): FeatureResponse
    {
        $page = $this->visitCreateAccount($server, $cookies)
            ->assertOk()
            ->assertSee('My Account Information');

        $antiSpamFieldName = $page->antiSpamFieldName() ?? 'should_be_empty';
        $securityToken = $page->securityToken();
        $this->assertNotNull($securityToken);

        return $this->storefrontPostSsl(
            '/index.php?main_page=create_account',
            array_merge(
                [
                    'action' => 'process',
                    'company' => '',
                    'suburb' => '',
                    'nick' => '',
                    'fax' => '',
                    'customers_referral' => '',
                    'email_pref_html' => 'email_format',
                    'newsletter' => '1',
                    'email_format' => 'TEXT',
                    'securityToken' => $securityToken,
                    $antiSpamFieldName => '',
                ],
                $data
            ),
            $server,
            $cookies
        );
    }

    protected function submitLoginForm(array $data = [], array $server = [], array $cookies = []): FeatureResponse
    {
        $page = $this->visitLogin($server, $cookies)
            ->assertOk()
            ->assertSee('Welcome, Please Sign In');

        $securityToken = $page->securityToken();
        $this->assertNotNull($securityToken);

        return $this->storefrontPostSsl(
            '/index.php?main_page=login&action=process',
            array_merge(
                [
                    'email_address' => '',
                    'password' => '',
                    'securityToken' => $securityToken,
                ],
                $data
            ),
            $server,
            $cookies
        );
    }

    protected function addProductToCart(
        int $productsId,
        ?string $categoryPath = null,
        string $page = 'product_info',
        array $server = [],
        array $cookies = []
    ): FeatureResponse {
        $query = [
            'main_page' => $page,
            'products_id' => $productsId,
            'action' => 'buy_now',
        ];

        if ($categoryPath !== null) {
            $query['cPath'] = $categoryPath;
        }

        $requestUri = '/index.php?' . http_build_query($query);
        $internalServer = array_merge(
            [
                'HTTP_REFERER' => 'http://localhost/index.php?main_page=' . $page
                    . ($categoryPath !== null ? '&cPath=' . $categoryPath : '')
                    . '&products_id=' . $productsId,
                'HTTP_USER_AGENT' => 'Mozilla/5.0 (compatible; ZC InProcess Test Client)',
            ],
            $server
        );

        return $this->storefrontGet($requestUri, $internalServer, $cookies);
    }

    protected function continueCheckoutShipping(array $data = [], array $server = [], array $cookies = []): FeatureResponse
    {
        $page = $this->storefrontGetSslMainPage('checkout_shipping', $server, $cookies)
            ->assertOk()
            ->assertSee('Delivery Information');

        $formAction = $page->formAction('checkout_address') ?? '/index.php?main_page=checkout_shipping';
        $defaults = $page->formDefaults('checkout_address');
        $response = $this->storefrontPostSsl(
            $this->normalizeStorefrontRelativeUri($formAction),
            array_merge($defaults, $data),
            $server,
            $cookies
        );

        return $response->isRedirect() ? $this->followRedirect($response) : $response;
    }

    protected function continueCheckoutPayment(array $data = [], array $server = [], array $cookies = []): FeatureResponse
    {
        $page = $this->storefrontGetSslMainPage('checkout_payment', $server, $cookies)
            ->assertOk()
            ->assertSee('Payment Information');

        $formAction = $page->formAction('checkout_payment') ?? '/index.php?main_page=checkout_confirmation';
        $defaults = $page->formDefaults('checkout_payment');
        $response = $this->storefrontPostSsl(
            $this->normalizeStorefrontRelativeUri($formAction),
            array_merge($defaults, $data),
            $server,
            $cookies
        );

        return $response->isRedirect() ? $this->followRedirect($response) : $response;
    }

    protected function confirmCheckoutOrder(array $data = [], array $server = [], array $cookies = []): FeatureResponse
    {
        $page = $this->storefrontGetSslMainPage('checkout_confirmation', $server, $cookies)
            ->assertOk()
            ->assertSee('Order Confirmation');

        $formAction = $page->formAction('checkout_confirmation');
        $this->assertNotNull($formAction);

        return $this->storefrontPostSsl(
            $this->normalizeStorefrontRelativeUri($formAction),
            array_merge($page->formDefaults('checkout_confirmation'), ['btn_submit_x' => '1'], $data),
            $server,
            $cookies
        );
    }

    protected function followRedirect(FeatureResponse $response, array $server = []): FeatureResponse
    {
        $response->assertRedirect();
        $location = $response->redirectLocation();
        $this->assertNotNull($location);

        $parts = parse_url($location);
        $path = $parts['path'] ?? '/';
        if (!empty($parts['query'])) {
            $path .= '?' . $parts['query'];
        }

        return $this->storefrontGet($path, array_merge($server, $this->storefrontServerFromLocation($location)));
    }

    protected function completeSimpleStorefrontCheckout(string $profileName, int $productId = 25): array
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

    private function storefrontGetMainPage(string $page, array $server = [], array $cookies = []): FeatureResponse
    {
        return $this->storefrontGet('/index.php?main_page=' . $page, $server, $cookies);
    }

    private function storefrontGetSslMainPage(string $page, array $server = [], array $cookies = []): FeatureResponse
    {
        return $this->storefrontGetMainPage($page, array_merge($this->storeSslServer, $server), $cookies);
    }

    private function storefrontPostSsl(string $uri, array $data = [], array $server = [], array $cookies = []): FeatureResponse
    {
        return $this->storefrontPost($uri, $data, array_merge($this->storeSslServer, $server), $cookies);
    }

    private function mergeStorefrontCookies(array $cookies): array
    {
        return array_merge($this->storeCookies, $cookies);
    }

    private function storeResponseCookies(FeatureResponse $response): void
    {
        $this->storeCookies = array_merge($this->storeCookies, $response->cookies);
    }

    private function normalizeStorefrontRelativeUri(string $uri): string
    {
        $parts = parse_url($uri);
        if ($parts === false) {
            return $uri;
        }

        $path = $parts['path'] ?? '/';
        if (!empty($parts['query'])) {
            $path .= '?' . $parts['query'];
        }

        return $path;
    }

    private function storefrontServerFromLocation(string $location): array
    {
        $parts = parse_url($location);
        if (!is_array($parts)) {
            return [];
        }

        $server = [];
        $scheme = strtolower($parts['scheme'] ?? '');
        if ($scheme === 'https') {
            $server['HTTPS'] = 'on';
            $server['SERVER_PORT'] = '443';
        } elseif ($scheme === 'http') {
            $server['HTTPS'] = 'off';
            $server['SERVER_PORT'] = '80';
        }

        if (!empty($parts['host'])) {
            $server['HTTP_HOST'] = $parts['host'];
            $server['SERVER_NAME'] = $parts['host'];
        }

        return $server;
    }
}
