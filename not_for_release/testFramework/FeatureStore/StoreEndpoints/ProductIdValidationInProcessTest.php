<?php
/**
 * @copyright Copyright 2003-2026 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 */

namespace Tests\FeatureStore\StoreEndpoints;

use Tests\Support\zcInProcessFeatureTestCaseStore;

/**
 * Covers the products_id validation block in includes/init_includes/init_sanitize.php:
 * a bare products_id is routed to its type's info page, a products_id that does not exist
 * is sent to product_info for a 404, and a request already on that page gets the 404
 * directly rather than a redirect to itself (zencart/zencart#7993).
 */
#[\PHPUnit\Framework\Attributes\Group('parallel-candidate')]
class ProductIdValidationInProcessTest extends zcInProcessFeatureTestCaseStore
{
    protected $runTestInSeparateProcess = true;
    protected $preserveGlobalState = false;

    private const MISSING_PRODUCT_ID = 999999;

    /** Listed in includes/spiders.txt, so no session is started for the request. */
    private const SPIDER_UA = ['HTTP_USER_AGENT' => 'Mozilla/5.0 (compatible; Googlebot/2.1; +http://www.google.com/bot.html)'];

    private const BROWSER_UA = ['HTTP_USER_AGENT' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/128.0 Safari/537.36'];

    public function testMissingProductOnItsInfoPageIsA404ForARequestWithoutASession(): void
    {
        $response = $this->visitProduct(self::MISSING_PRODUCT_ID, [], self::SPIDER_UA);

        $this->assertNull($response->cookie('zenid'), 'Spider request unexpectedly started a session');
        $this->assertFalse($response->isRedirect(), 'Missing product on product_info must not redirect');
        $response->assertStatus(404);
    }

    public function testMissingProductOnItsInfoPageIsA404OnEveryRequestWithASession(): void
    {
        $first = $this->visitProduct(self::MISSING_PRODUCT_ID, [], self::BROWSER_UA);
        $this->assertNotNull($first->cookie('zenid'), 'Browser request should have started a session');
        $this->assertFalse($first->isRedirect());
        $first->assertStatus(404);

        // The session cookie is carried into the next request by the base class.
        $second = $this->visitProduct(self::MISSING_PRODUCT_ID, [], self::BROWSER_UA);
        $this->assertFalse($second->isRedirect());
        $second->assertStatus(404);
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('bareProductsIdProvider')]
    public function testBareProductsIdIsRoutedToItsInfoPage(int $productsId, string $expectedPage): void
    {
        $this->get('/index.php?products_id=' . $productsId, self::SPIDER_UA)
            ->assertRedirect('main_page=' . $expectedPage . '&products_id=' . $productsId);
    }

    public static function bareProductsIdProvider(): array
    {
        return [
            'product' => [25, 'product_info'],
            'music product' => [166, 'product_music_info'],
        ];
    }

    public function testMissingProductOnAnotherPageIsSentToProductInfo(): void
    {
        $this->get('/index.php?main_page=index&products_id=' . self::MISSING_PRODUCT_ID, self::SPIDER_UA)
            ->assertRedirect('main_page=product_info&products_id=' . self::MISSING_PRODUCT_ID);
    }
}
