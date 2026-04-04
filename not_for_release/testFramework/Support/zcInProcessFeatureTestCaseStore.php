<?php
/**
 * @copyright Copyright 2003-2026 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 */

namespace Tests\Support;

use Tests\Support\InProcess\FeatureRequest;
use Tests\Support\InProcess\FeatureResponse;
use Tests\Support\InProcess\StorefrontFeatureRunner;

abstract class zcInProcessFeatureTestCaseStore extends zcInProcessFeatureTestCase
{
    protected array $cookies = [];
    protected array $sslServer = ['HTTPS' => 'on', 'SERVER_PORT' => '443'];

    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();
        self::setUpInProcessFeatureContext('store');
    }

    public function setUp(): void
    {
        StorefrontFeatureRunner::resetDispatchState();
        $this->cookies = [];
    }

    protected function get(string $uri, array $server = [], array $cookies = []): FeatureResponse
    {
        $response = (new StorefrontFeatureRunner())->handle(
            new FeatureRequest($uri, 'GET', server: $server, cookies: $this->mergeCookies($cookies))
        );

        $this->storeResponseCookies($response);

        return $response;
    }

    protected function post(string $uri, array $data = [], array $server = [], array $cookies = []): FeatureResponse
    {
        $response = (new StorefrontFeatureRunner())->handle(
            new FeatureRequest($uri, 'POST', request: $data, server: $server, cookies: $this->mergeCookies($cookies))
        );

        $this->storeResponseCookies($response);

        return $response;
    }

    protected function getMainPage(string $page, array $server = [], array $cookies = []): FeatureResponse
    {
        return $this->get('/index.php?main_page=' . $page, $server, $cookies);
    }

    protected function getSsl(string $uri, array $server = [], array $cookies = []): FeatureResponse
    {
        return $this->get($uri, array_merge($this->sslServer, $server), $cookies);
    }

    protected function getSslMainPage(string $page, array $server = [], array $cookies = []): FeatureResponse
    {
        return $this->getMainPage($page, array_merge($this->sslServer, $server), $cookies);
    }

    protected function visitContactUs(array $server = [], array $cookies = []): FeatureResponse
    {
        return $this->getSslMainPage('contact_us', $server, $cookies);
    }

    protected function visitPasswordForgotten(array $server = [], array $cookies = []): FeatureResponse
    {
        return $this->getSslMainPage('password_forgotten', $server, $cookies);
    }

    protected function visitGiftVoucherRedeem(?string $voucherNumber = null, array $server = [], array $cookies = []): FeatureResponse
    {
        $uri = '/index.php?main_page=gv_redeem';
        if ($voucherNumber !== null) {
            $uri .= '&gv_no=' . urlencode($voucherNumber);
        }

        return $this->get($uri, $server, $cookies);
    }

    protected function visitGiftVoucherSend(array $server = [], array $cookies = []): FeatureResponse
    {
        return $this->getSslMainPage('gv_send', $server, $cookies);
    }

    protected function visitCreateAccount(array $server = [], array $cookies = []): FeatureResponse
    {
        return $this->getSslMainPage('create_account', $server, $cookies);
    }

    protected function visitLogin(array $server = [], array $cookies = []): FeatureResponse
    {
        return $this->getSslMainPage('login', $server, $cookies);
    }

    protected function visitLogoff(array $server = [], array $cookies = []): FeatureResponse
    {
        return $this->getMainPage('logoff', $server, $cookies);
    }

    protected function visitSearchResults(array $query = [], array $server = [], array $cookies = []): FeatureResponse
    {
        $uri = '/index.php?main_page=search_result';
        if ($query !== []) {
            $uri .= '&' . http_build_query($query);
        }

        return $this->get($uri, $server, $cookies);
    }

    protected function visitSearch(array $query = [], array $server = [], array $cookies = []): FeatureResponse
    {
        $uri = '/index.php?main_page=search';
        if ($query !== []) {
            $uri .= '&' . http_build_query($query);
        }

        return $this->get($uri, $server, $cookies);
    }

    protected function visitCart(array $query = [], array $server = [], array $cookies = []): FeatureResponse
    {
        $uri = '/index.php?main_page=shopping_cart';
        if ($query !== []) {
            $uri .= '&' . http_build_query($query);
        }

        return $this->get($uri, $server, $cookies);
    }

    protected function visitProduct(int $productsId, array $query = [], array $server = [], array $cookies = []): FeatureResponse
    {
        $uri = '/index.php?main_page=product_info&products_id=' . $productsId;
        if ($query !== []) {
            $uri .= '&' . http_build_query($query);
        }

        return $this->get($uri, $server, $cookies);
    }

    protected function visitCheckoutShipping(array $server = [], array $cookies = []): FeatureResponse
    {
        return $this->getSslMainPage('checkout_shipping', $server, $cookies);
    }

    protected function visitCheckoutPayment(array $server = [], array $cookies = []): FeatureResponse
    {
        return $this->getSslMainPage('checkout_payment', $server, $cookies);
    }

    protected function visitCheckoutConfirmation(array $server = [], array $cookies = []): FeatureResponse
    {
        return $this->getSslMainPage('checkout_confirmation', $server, $cookies);
    }

    protected function searchFor(string $keyword, array $query = [], array $server = [], array $cookies = []): FeatureResponse
    {
        return $this->visitSearchResults(array_merge(['keyword' => $keyword], $query), $server, $cookies);
    }

    protected function postMainPage(string $page, array $data = [], array $server = [], array $cookies = []): FeatureResponse
    {
        return $this->post('/index.php?main_page=' . $page, $data, $server, $cookies);
    }

    protected function postSsl(string $uri, array $data = [], array $server = [], array $cookies = []): FeatureResponse
    {
        return $this->post($uri, $data, array_merge($this->sslServer, $server), $cookies);
    }

    protected function postSslMainPage(string $page, array $data = [], array $server = [], array $cookies = []): FeatureResponse
    {
        return $this->postMainPage($page, $data, array_merge($this->sslServer, $server), $cookies);
    }

    protected function submitContactUsForm(array $data = [], array $server = [], array $cookies = []): FeatureResponse
    {
        $page = $this->visitContactUs($server, $cookies)
            ->assertOk()
            ->assertSee('Zen Cart! : Contact Us');

        $securityToken = $page->securityToken();
        $this->assertNotNull($securityToken);

        return $this->postSsl(
            '/index.php?main_page=contact_us&action=send',
            array_merge(
                [
                    'contactname' => '',
                    'email' => '',
                    'telephone' => '',
                    'enquiry' => '',
                    'securityToken' => $securityToken,
                ],
                $data
            ),
            $server,
            $cookies
        );
    }

    protected function submitPasswordForgottenForm(array $data = [], array $server = [], array $cookies = []): FeatureResponse
    {
        $page = $this->visitPasswordForgotten($server, $cookies)
            ->assertOk()
            ->assertSee('Forgotten Password');

        $securityToken = $page->securityToken();
        $this->assertNotNull($securityToken);

        return $this->postSsl(
            '/index.php?main_page=password_forgotten&action=process',
            array_merge(
                [
                    'email_address' => '',
                    'securityToken' => $securityToken,
                ],
                $data
            ),
            $server,
            $cookies
        );
    }

    protected function submitGiftVoucherSendForm(array $data = [], array $server = [], array $cookies = []): FeatureResponse
    {
        $page = $this->visitGiftVoucherSend($server, $cookies)
            ->assertOk();

        $formAction = $page->formAction('gv_send_send') ?? '/index.php?main_page=gv_send&action=send';

        return $this->postSsl(
            $this->normalizeRelativeUri($formAction),
            array_merge($page->formDefaults('gv_send_send'), $data),
            $server,
            $cookies
        );
    }

    protected function confirmGiftVoucherSend(FeatureResponse $page, array $data = [], array $server = [], array $cookies = []): FeatureResponse
    {
        $page->assertOk();

        $formAction = $page->formAction('gv_send_process') ?? '/index.php?main_page=gv_send&action=process';
        $response = $this->postSsl(
            $this->normalizeRelativeUri($formAction),
            array_merge($page->formDefaults('gv_send_process'), $data),
            $server,
            $cookies
        );

        return $response->isRedirect() ? $this->followRedirect($response) : $response;
    }

    protected function submitCreateAccountForm(array $data = [], array $server = [], array $cookies = []): FeatureResponse
    {
        $page = $this->visitCreateAccount($server, $cookies)
            ->assertOk()
            ->assertSee('My Account Information');

        $antiSpamFieldName = $page->antiSpamFieldName() ?? 'should_be_empty';
        $securityToken = $page->securityToken();
        $this->assertNotNull($securityToken);

        return $this->postSsl(
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

        return $this->postSsl(
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

    protected function emptyCart(array $server = [], array $cookies = []): FeatureResponse
    {
        return $this->visitCart(['action' => 'empty_cart'], $server, $cookies);
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

        return $this->get($requestUri, $internalServer, $cookies);
    }

    protected function continueCheckoutShipping(array $data = [], array $server = [], array $cookies = []): FeatureResponse
    {
        $page = $this->visitCheckoutShipping($server, $cookies)
            ->assertOk()
            ->assertSee('Delivery Information');

        $formAction = $page->formAction('checkout_address') ?? '/index.php?main_page=checkout_shipping';
        $defaults = $page->formDefaults('checkout_address');
        $response = $this->postSsl(
            $this->normalizeRelativeUri($formAction),
            array_merge($defaults, $data),
            $server,
            $cookies
        );

        return $response->isRedirect() ? $this->followRedirect($response) : $response;
    }

    protected function continueCheckoutPayment(array $data = [], array $server = [], array $cookies = []): FeatureResponse
    {
        $page = $this->visitCheckoutPayment($server, $cookies)
            ->assertOk()
            ->assertSee('Payment Information');

        $formAction = $page->formAction('checkout_payment') ?? '/index.php?main_page=checkout_confirmation';
        $defaults = $page->formDefaults('checkout_payment');
        $response = $this->postSsl(
            $this->normalizeRelativeUri($formAction),
            array_merge($defaults, $data),
            $server,
            $cookies
        );

        return $response->isRedirect() ? $this->followRedirect($response) : $response;
    }

    protected function confirmCheckoutOrder(array $data = [], array $server = [], array $cookies = []): FeatureResponse
    {
        $page = $this->visitCheckoutConfirmation($server, $cookies)
            ->assertOk()
            ->assertSee('Order Confirmation');

        $formAction = $page->formAction('checkout_confirmation');
        $this->assertNotNull($formAction);

        return $this->postSsl(
            $this->normalizeRelativeUri($formAction),
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

        $redirectServer = array_merge($server, $this->serverFromLocation($location));

        return $this->get($path, $redirectServer);
    }

    private function mergeCookies(array $cookies): array
    {
        return array_merge($this->cookies, $cookies);
    }

    protected function normalizeRelativeUri(string $uri): string
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

    private function storeResponseCookies(FeatureResponse $response): void
    {
        $this->cookies = array_merge($this->cookies, $response->cookies);
    }

    private function serverFromLocation(string $location): array
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
