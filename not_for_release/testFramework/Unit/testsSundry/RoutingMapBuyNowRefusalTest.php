<?php
/**
 * @copyright Copyright 2003-2026 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 *
 * application_top.php refuses (406) a 'Buy Now' request that did not come from one of
 * the store's own pages. The check relies on the Referer header, which a browser may
 * withhold, so a real shopper can be refused too. zen_buy_now_refusal_html()
 * (includes/routing_map.php) builds the body that response carries for a non-crawler:
 * a link to the product, in place of a blank page.
 *
 * Like the rest of routing_map.php it is self-contained (no framework, no constants,
 * no DB), because it runs before the application is initialized.
 */

namespace Tests\Unit\testsSundry;

use Tests\Support\zcUnitTestCase;

class RoutingMapBuyNowRefusalTest extends zcUnitTestCase
{
    public function setUp(): void
    {
        parent::setUp();

        require_once DIR_FS_CATALOG . 'includes/routing_map.php';
    }

    public function testLinksToTheProductPage(): void
    {
        $html = \zen_buy_now_refusal_html(['action' => 'buy_now', 'products_id' => '27']);

        $this->assertStringContainsString('href="index.php?main_page=product_info&amp;products_id=27"', $html);
        $this->assertStringStartsWith('<!DOCTYPE html>', $html);
    }

    /**
     * The page must not offer the refused action again, or a crawler that reads the
     * body could loop on it.
     */
    public function testDoesNotLinkBackToBuyNow(): void
    {
        $html = \zen_buy_now_refusal_html(['action' => 'buy_now', 'products_id' => '27']);

        $this->assertStringNotContainsString('buy_now', $html);
    }

    public function testAsksSearchEnginesNotToIndexIt(): void
    {
        $html = \zen_buy_now_refusal_html(['products_id' => '27']);

        $this->assertStringContainsString('<meta name="robots" content="noindex, nofollow">', $html);
    }

    /**
     * A cart-style id carries an attribute hash after a colon; only the product's
     * number belongs in the link.
     */
    public function testUsesOnlyTheNumericPartOfAnAttributeId(): void
    {
        $html = \zen_buy_now_refusal_html(['products_id' => '27:0a1b2c3d4e5f60718293a4b5c6d7e8f9']);

        $this->assertStringContainsString('products_id=27"', $html);
        $this->assertStringNotContainsString('0a1b2c3d', $html);
    }

    /**
     * Nothing from the request reaches the page except an integer.
     */
    public function testRequestTextNeverReachesThePage(): void
    {
        $html = \zen_buy_now_refusal_html(['products_id' => '12"><script>alert(1)</script>']);

        $this->assertStringContainsString('products_id=12"', $html);
        $this->assertStringNotContainsString('<script', $html);
        $this->assertStringNotContainsString('alert', $html);
    }

    /**
     * With no usable product there is nothing to link to, so the caller sends the
     * empty 406 it always has.
     */
    public function testReturnsNothingWithoutAUsableProductId(): void
    {
        $unusable = [
            'no products_id' => ['action' => 'buy_now'],
            'empty' => ['products_id' => ''],
            'zero' => ['products_id' => '0'],
            'negative' => ['products_id' => '-5'],
            'not a number' => ['products_id' => 'abc'],
            'array (parameter pollution)' => ['products_id' => ['27']],
        ];

        foreach ($unusable as $case => $get) {
            $this->assertSame('', \zen_buy_now_refusal_html($get), "Expected no page for: $case.");
        }
    }
}
