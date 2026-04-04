<?php
/**
 * @copyright Copyright 2003-2026 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 */

namespace Tests\Unit\testsSundry;

use PHPUnit\Framework\TestCase;
use Tests\Support\InProcess\AdminFeatureRunner;
use Tests\Support\InProcess\ApplicationStateResetter;
use Tests\Support\InProcess\InProcessFeatureException;
use Tests\Support\InProcess\FeatureRequest;
use Tests\Support\InProcess\FeatureResponse;
use Tests\Support\InProcess\StorefrontFeatureRunner;

class InProcessFeatureRunnerTest extends TestCase
{
    protected $runTestInSeparateProcess = true;
    protected $preserveGlobalState = false;
    private string $fixture;

    protected function setUp(): void
    {
        StorefrontFeatureRunner::resetDispatchState();
        $this->fixture = dirname(__DIR__) . '/fixtures/InProcess/storefront_fixture.php';
    }

    public function testFeatureRequestCanMergeServerValues(): void
    {
        $request = new FeatureRequest('/index.php', 'GET', server: ['HTTP_HOST' => 'example.test']);
        $updated = $request->withServer(['REQUEST_METHOD' => 'GET']);

        $this->assertSame('example.test', $updated->server['HTTP_HOST']);
        $this->assertSame('GET', $updated->server['REQUEST_METHOD']);
    }

    public function testFeatureRequestCanMergeQueryStringWithExplicitQueryValues(): void
    {
        $request = new FeatureRequest('/index.php?main_page=products_all', query: ['foo' => 'bar']);

        $this->assertSame('/index.php', $request->requestPath());
        $this->assertSame([
            'main_page' => 'products_all',
            'foo' => 'bar',
        ], $request->queryParameters());
    }

    public function testFeatureResponseCanReadHeadersCaseInsensitively(): void
    {
        $response = new FeatureResponse(200, 'ok', ['X-Test' => 'value'], ['zenid' => 'cookie-value']);

        $this->assertSame('value', $response->header('x-test'));
        $this->assertNull($response->header('missing'));
        $this->assertSame('cookie-value', $response->cookie('zenid'));
    }

    public function testFeatureResponseProvidesAssertionHelpers(): void
    {
        $response = new FeatureResponse(200, 'Zen Cart!', ['X-Test' => 'value']);

        $result = $response
            ->assertOk()
            ->assertStatus(200)
            ->assertSee('Zen Cart!')
            ->assertHeader('X-Test', 'value');

        $this->assertSame($response, $result);
    }

    public function testFeatureResponseCanExtractSecurityToken(): void
    {
        $response = new FeatureResponse(200, "<script>securityToken: 'abc123token'</script>");

        $this->assertSame('abc123token', $response->securityToken());
    }

    public function testFeatureResponseCanExtractHiddenSecurityTokenAndHiddenFields(): void
    {
        $response = new FeatureResponse(200, <<<'HTML'
<form id="reset_form">
  <input type="hidden" name="securityToken" value="hidden-token">
  <input name="reset_token" type="hidden" value="reset-token-123">
</form>
HTML);

        $this->assertSame('hidden-token', $response->securityToken());
        $this->assertSame('reset-token-123', $response->hiddenFieldValue('reset_token'));
    }

    public function testFeatureResponseCanExtractAntiSpamFieldName(): void
    {
        $response = new FeatureResponse(200, <<<'HTML'
<form id="create_account">
  <input type="text" id="CAAS" name="contact_me_by_fax_only" value="">
</form>
HTML);

        $this->assertSame('contact_me_by_fax_only', $response->antiSpamFieldName());
    }

    public function testFeatureResponseCanReadFormDefaultsAndActionByFormName(): void
    {
        $response = new FeatureResponse(200, <<<'HTML'
<form name="checkout_address" action="https://example.test/index.php?main_page=checkout_shipping" method="post">
  <input type="hidden" name="securityToken" value="abc123">
  <input type="hidden" name="action" value="process">
  <input type="radio" name="shipping" value="flat_flat">
  <input type="radio" name="shipping" value="item_item" checked="checked">
  <textarea name="comments">Keep dry</textarea>
</form>
HTML);

        $this->assertSame(
            'https://example.test/index.php?main_page=checkout_shipping',
            $response->formAction('checkout_address')
        );
        $this->assertSame(
            [
                'securityToken' => 'abc123',
                'action' => 'process',
                'shipping' => 'item_item',
                'comments' => 'Keep dry',
            ],
            $response->formDefaults('checkout_address')
        );
    }

    public function testFeatureResponseCanReadFormDefaultsByFormIdAndDecodeEntities(): void
    {
        $response = new FeatureResponse(200, <<<'HTML'
<form id="account_password" action="https://example.test/index.php?main_page=password_reset" method="post">
  <input type="hidden" name="securityToken" value="abc&amp;123">
  <input type="hidden" name="action" value="process">
  <input type="checkbox" name="newsletter" value="1" checked="checked">
  <input type="checkbox" name="ignored_checkbox" value="0">
  <input type="text" name="disabled_field" value="skip-me" disabled>
  <textarea name="comments">Tom &amp; Jerry</textarea>
  <select name="country_id">
    <option value="14">Canada</option>
    <option value="223" selected="selected">United States</option>
  </select>
</form>
HTML);

        $this->assertSame(
            'https://example.test/index.php?main_page=password_reset',
            $response->formAction('account_password')
        );
        $this->assertSame(
            [
                'securityToken' => 'abc&123',
                'action' => 'process',
                'newsletter' => '1',
                'comments' => 'Tom & Jerry',
                'country_id' => '223',
            ],
            $response->formDefaults('account_password')
        );
    }

    public function testFeatureResponseCanAssertRedirects(): void
    {
        $response = new FeatureResponse(302, '', ['Location' => 'https://example.test/index.php?main_page=contact_us']);

        $result = $response->assertRedirect('main_page=contact_us');

        $this->assertSame($response, $result);
    }

    public function testFeatureResponseCanReportRedirectMetadata(): void
    {
        $response = new FeatureResponse(302, '', ['Location' => 'https://example.test/index.php?main_page=contact_us']);

        $this->assertTrue($response->isRedirect());
        $this->assertSame('https://example.test/index.php?main_page=contact_us', $response->redirectLocation());
    }

    public function testFeatureResponseDoesNotTreatRedirectStatusWithoutLocationAsRedirect(): void
    {
        $response = new FeatureResponse(302, 'Redirect without location');

        $this->assertFalse($response->isRedirect());
        $this->assertNull($response->redirectLocation());
    }

    public function testStorefrontRunnerExecutesFixtureEntrypointAndCapturesResponse(): void
    {
        $runner = new StorefrontFeatureRunner(entrypoint: $this->fixture, documentRoot: dirname($this->fixture));
        $response = $runner->handle(new FeatureRequest(
            '/index.php?main_page=products_all',
            'POST',
            request: ['products_id' => '2'],
            cookies: ['zenid' => 'cookie-value'],
            server: ['HTTP_HOST' => 'storefront.test']
        ));
        $payload = json_decode($response->content, true, 512, JSON_THROW_ON_ERROR);

        $this->assertSame(200, $response->statusCode);
        $this->assertSame('storefront', $response->header('X-ZC-InProcess-Runner'));
        $this->assertSame('products_all', $payload['get']['main_page']);
        $this->assertSame('2', $payload['post']['products_id']);
        $this->assertSame('cookie-value', $payload['cookies']['zenid']);
        $this->assertSame('POST', $payload['server']['REQUEST_METHOD']);
        $this->assertSame('/index.php?main_page=products_all', $payload['server']['REQUEST_URI']);
        $this->assertSame('storefront.test', $payload['server']['HTTP_HOST']);
    }

    public function testStorefrontRunnerCanHandleMultipleRequestsInSameProcess(): void
    {
        $runner = new StorefrontFeatureRunner(entrypoint: $this->fixture, documentRoot: dirname($this->fixture));

        $first = $runner->handle(new FeatureRequest('/index.php'));
        $second = $runner->handle(new FeatureRequest('/index.php?main_page=products_all'));

        $this->assertSame(200, $first->statusCode);
        $this->assertSame(200, $second->statusCode);
    }

    public function testStorefrontRunnerRejectsUnsupportedStorefrontPaths(): void
    {
        $runner = new StorefrontFeatureRunner();

        $this->expectException(InProcessFeatureException::class);
        $this->expectExceptionMessage('Unsupported storefront path');

        $runner->handle(new FeatureRequest('/contact_us.php'));
    }

    public function testAdminRunnerReturnsAdminResponse(): void
    {
        $response = (new AdminFeatureRunner())->handle(new FeatureRequest('/admin/index.php'));

        $this->assertContains($response->statusCode, [200, 302]);
        $this->assertSame('admin', $response->header('X-ZC-InProcess-Runner'));
    }

    public function testRunnerResetsStateBeforeDispatch(): void
    {
        $counter = new \stdClass();
        $counter->calls = 0;

        $resetter = new class($counter) implements ApplicationStateResetter {
            public function __construct(private \stdClass $counter)
            {
            }

            public function reset(): void
            {
                $this->counter->calls++;
            }
        };

        $fixture = $this->fixture;
        $runner = new class($resetter, $fixture) extends StorefrontFeatureRunner {
            public function __construct(ApplicationStateResetter $resetter, string $fixture)
            {
                parent::__construct($resetter, $fixture, dirname($fixture));
            }
        };

        $runner->handle(new FeatureRequest('/index.php'));

        $this->assertSame(1, $counter->calls);
    }
}
